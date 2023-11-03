<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 8-Oct-04   EWB     Created
13-Oct-04   EWB     details page, row color indicates priority
13-Oct-04   EWB     select by time.
14-Oct-04   EWB     show/sort by record id.
19-Oct-04   EWB     save / load / edit schedule.
21-Oct-04   EWB     pop-up site filter table
22-Oct-04   EWB     next run column
22-Oct-04   EWB     page titles.
22-Oct-04   EWB     select by next_run / destination
23-Oct-04   EWB     notify queue control
25-Oct-04   EWB     show default mail in italics
26-Oct-04   EWB     mail column empty unless email enabled
26-Oct-04   EWB     expand / compact display.
28-Oct-04   EWB     select / sort by restricted / expiration
29-Oct-04   EWB     post / sort jump to data table.
 1-Nov-04   EWB     oneshot dates go to +/- 45
 2-Dec-04   EWB     notify details can describe crontab schedule.
 6-Dec-04   EWB     gang assign can update email recepients.
10-Dec-04   EWB     gang delete.
13-Dec-04   EWB     Show site filters.
14-Dec-04   BJS     Added skip_owner.
15-Dec-04   EWB     Name Contains.
17-Dec-04   EWB     push_queue
21-Dec-04   EWB     Disable/Enable Override
 3-Jan-05   EWB     Enable/Disable Multiple Notifications
 7-Jan-05   EWB     new columns: defmail / links, recipient contains
18-Jan-05   AAM     Wording changes as per Alex.
21-Jan-05   EWB     Notification Help
24-Jan-05   EWB     select by owner, threshold range
25-Jan-05   EWB     allow admin gang delete of (!$mine) notifications.
 4-Feb-05   EWB     Select by owner still displays column
 7-Feb-05   EWB     New notification help pages.
14-Feb-05   EWB     database consistancy check
 6-Apr-05   EWB     special case for set / update threshold.
28-Jul-05   EWB     ginclude / gexclude / gsuspend
 2-Aug-05   EWB     gang update for ginclude / gexclude / gsuspend
26-Sep-05   BJS     Added autodesk constants and options, prep_for_display().
29-Sep-05   BJS     Added edit multiple & search on Autodesk fields.
                    UI fixes for Alex.
06-Oct-05   BJS     Spelling fix.
11-Oct-05   BJS     Added set_email_footer() to over_form().
13-Oct-05   BJS     UI fixes for Alex. Removed find_mgrp_gid().
14-Oct-05   BJS     UI fixes. Added configre group ability.
24-Oct-05   BJS     Edit multiple working with groups.
                    find_mgrp_gid() can call find_many() or find_one().
25-Oct-05   BJS     Added search options for group_include/exclude.
01-Nov-05   BJS     Compare groups against '', not zero.
03-Nov-05   BJS     Added GRPS_create_select_box().
11-Nov-05   BJS     Removed filtersites references.
18-Nov-05   BJS     Added constQueryNoRestrict to build_group_list().
30-Nov-05   BJS     Added argument to GRPS_create_select_box.
06-Jan-06   BJS     'Site Email' instruction clarifications.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
10-Sep-06   AAM     Added more explanatory text to link for "skip pending
                    notifications forward" because it is actually useful.
19-Sep-06   WOH     Bug 3657: Changed name html_footer.  Added username arg.
27-Dec-07   BTE     Added support for Autotask tickets/notes.
04-Jan-08   BTE     Bug 4379: Autotask text changes.

*/

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-srch.php'  );
include_once ( '../lib/l-slct.php'  );
include_once ( '../lib/l-cens.php'  );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-date.php'  );
include_once ( '../lib/l-rlib.php'  );
include_once ( '../lib/l-gsql.php'  );
include_once ( '../lib/l-form.php'  );
include_once ( '../lib/l-tabs.php'  );
include_once ( '../lib/l-cmth.php'  );
include_once ( '../lib/l-ntfy.php'  );
include_once ( '../lib/l-msql.php'  );
include_once ( '../lib/l-gbox.php'  );
include_once ( '../lib/l-jump.php'  );
include_once ( '../lib/l-next.php'  );
include_once ( '../lib/l-js.php'    );
//  include ( '../lib/l-slav.php'  );
include_once ( '../lib/l-upnt.php'  );
include_once ( 'local.php'   );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-tiny.php'  );
include_once ( '../lib/l-sitflt.php');
include_once ( '../lib/l-grps.php'  );
include_once ( '../lib/l-evnt.php'  );
include_once ( '../lib/l-auto.php'  );

    define('constButtonYes',  'Yes');
    define('constButtonNo',   'No');
    define('constButtonOk',   'OK');
    define('constButtonCan',  'Cancel');
    define('constButtonHlp',  'Help');
    define('constButtonRst',  'Reset');
    define('constButtonSub',  'Search');
    define('constButtonAll',  'Check all');
    define('constButtonNone', 'Uncheck all');
    define('constButtonLess', '<< Less');
    define('constButtonMore', 'More >>');

    define('constPostMonth',  'mnth');
    define('constPostHour',   'hour');
    define('constPostMinute', 'mint');
    define('constPostWeek',   'week');
    define('constPostMDay',   'mday');
    define('constPostWDay',   'wday');
    define('constPostYDay',   'yday');     //  just in case

    define('constAnyMonth',  'mnth_xx');
    define('constAnyHour',   'hour_xx');
    define('constAnyMDay',   'mday_xx');
    define('constAnyWDay',   'wday_xx');

    /* Autodesk constants */
    define('constEmailFooter',   'email_footer');
    define('constEmailPerSite',  'email_per_site');
    define('constEmailFooterTxt','email_footer_txt');
    define('constEmailSender',   'email_sender');

    define('constAutotaskIncomplete', '<p><b>Autotask was selected '
        . ' but this server does not have complete user or default '
        . 'settings configured yet.</b><p>');

    function title($act)
    {
        $e = 'Event';
        $n = 'Notification';
        $q = 'Queue';
        $m = 'Multiple';
        switch ($act)
        {
            case 'copy': return "Copy an $e $n";
            case 'edit': return "Edit a $n";
            case 'over': return 'Create a Local Report';
            case 'addn': return "Add an $e $n";
            case 'insn': return "$e $n Added";
            case 'stat': return "$n Statistics";
            case 'last': return "Recent ${n}s";
            case 'next': return "Upcoming ${n}s";
            case 'menu': return "$n Debug Menu";
            case 'gang': return "Edit $m $e ${n}s";
            case 'mnge': return "Manage $e ${n}s";
            case 'gexp': ;
            case 'gdel': return "Delete $m ${n}s";
            case 'genb': return "Enable/Disable $m ${n}s";
            case 'dovr': return 'Disable Local Report';
            case 'eovr': return 'Enable Local Report';
            case 'fail': return 'mysql Database Failure';
            case 'cdel': return "Confirm Delete";
            case 'view': return "$n Details";
            case 'sane': return "$n Database Consistancy Check";
            case 'lock': return "Lock $n $q";
            case 'pick': return "Unlock $n $q";
            case 'push': ;
            case 'time': ;
            case 'frst': ;
            case 'post': ;
            case 'queu': return "$n $q";
            default    : return "Event ${n}s";
        }
    }


    function gang_href(&$env,$act)
    {
        $self = $env['self'];
        $page = $env['page'];
        $limt = $env['limt'];
        $ord  = $env['ord'];
        $args = array("$self?act=$act&o=$ord&p=$page&l=$limt");
        query_state($env,$args);
        return join('&',$args);
    }

    function gang_link(&$env,$act,$text)
    {
        $href = gang_href($env,$act);
        return html_link($href,$text);
    }


    function again(&$env)
    {
        $self = $env['self'];
        $dbg = $env['priv'];
        $act = $env['act'];
        $cmd = "$self?act";
        $a   = array( );
        $a[] = html_link('#top','top');
        $a[] = html_link('#bottom','bottom');
        if ($act == 'list')
        {
            $a[] = html_link('#control','control');
            $a[] = html_link('#table','table');
            $a[] = gang_link($env,'mnge','manage');
        }
        else
        {
            $a[] = html_link($self,'notifications');
        }
        if (matchOld($act,'|||queu|post|frst|time|push|'))
        {
            $a[] = html_link('#table','table');
        }
        if (matchOld($act,'|||addn|over|copy|edit|'))
        {
            $a[] = html_link('#schedule','schedule');
        }
        else
        {
            $a[] = html_link("$cmd=addn",'add');
        }
        if ($dbg)
        {
            $args = $env['args'];
            $jump = $env['jump'];
            $time = (86400 * 14);
            $comp = "$cmd=list&mal=-1&dsp=1&gbl=3&adv=0";
            $next = "$comp&nxt=$time&o=24$jump";
            $last = "$comp&o=20$jump";
            $queu = "$cmd=queu$jump";
            $href = ($args)? "$self?$args" : $self;
            $a[] = html_link($next,'next');
            $a[] = html_link($last,'last');
            $a[] = html_link($queu,'queue');
            $a[] = html_link("$cmd=menu",'menu');
            $a[] = html_link('../acct/index.php','home');
            $a[] = html_link($href,'again');
        }
        $a[] = html_link('autotask.php', 'autotask');
        $user = $env['user'];
        if($user['priv_admin'])
        {
            $a[] = html_link('autotask.php?default=1',
                'autotask server defaults');
        }
        return jumplist($a);
    }

    function green($msg)
    {
        return "<font color=\"green\">$msg</font>";
    }


    function dgreen($a,$b)
    {
        $aa = green($a);
        $bb = green($b);
        return double($aa,$bb);
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

    function okcancel($n)
    {
        $in  = indent($n);
        $ok  = button(constButtonOk);
        $can = button(constButtonCan);
        return para("${in}${ok}${in}${can}");
    }

    function okcanhlp($n,$act)
    {
        $in  = indent($n);
        $ok  = button(constButtonOk);
        $can = button(constButtonCan);
        $hlp = click(constButtonHlp,$act);
        return para("${in}${ok}${in}${can}${in}${hlp}");
    }


    function checkallnone($n)
    {
        $in = indent($n);
        $ck = button(constButtonAll);
        $un = button(constButtonNone);
        return para("${in}${ck}${in}${un}");
    }


   /*
    |   Returns the original array, except that
    |   the empty elements have been filtered out.
    */

    function remove_empty($set)
    {
        $out = array( );
        reset($set);
        foreach ($set as $key => $data)
        {
            if ($data)
            {
                $out[] = $data;
            }
        }
        return $out;
    }


    function matchOld($act,$txt)
    {
        $tmp = "|$act|";
        return strpos($txt,$tmp);
    }


    function query_state(&$env,&$set)
    {
        $adv = $env['adv'];
        $crt = $env['crt'];
        $def = $env['def'];
        $dsp = $env['dsp'];
        $dst = $env['dst'];
        $enb = $env['enb'];
        $exp = $env['exp'];
        $flt = $env['flt'];
        $frq = $env['frq'];
        $gbl = $env['gbl'];
        $lnk = $env['lnk'];
        $lst = $env['lst'];
        $mal = $env['mal'];
        $mod = $env['mod'];
        $nxt = $env['nxt'];
        $pat = $env['pat'];
        $pnd = $env['pnd'];
        $gnc = $env['gnc'];
        $gxc = $env['gxc'];
        $pri = $env['pri'];
        $rst = $env['rst'];
        $skp = $env['skp'];
        $ftr = $env['ftr'];
        $eps = $env['eps'];
        $ems = $env['ems'];
        $tld = $env['tld'];
        $own = $env['own'];
        $txt = $env['txt'];
        $src = $env['src'];
        $dbg = $env['dbug'];
        $prv = $env['priv'];

        if ($adv != 1) $set[] = "adv=$adv";
        if ($dsp != 0) $set[] = "dsp=$dsp";
        if ($dst != 0) $set[] = "dst=$dst";
        if ($enb != 0) $set[] = "enb=$enb";
        if ($flt != 0) $set[] = "flt=$flt";
        if ($frq != 0) $set[] = "frq=$frq";
        if ($lst != 0) $set[] = "lst=$lst";
        if ($mal != 0) $set[] = "mal=$mal";

        if ($crt != -1) $set[] = "crt=$crt";
        if ($def != -1) $set[] = "def=$def";
        if ($exp != -1) $set[] = "exp=$exp";
        if ($gbl != -1) $set[] = "gbl=$gbl";
        if ($lnk != -1) $set[] = "lnk=$lnk";
        if ($mod != -1) $set[] = "mod=$mod";
        if ($nxt != -1) $set[] = "nxt=$nxt";
        if ($own != -1) $set[] = "own=$own";
        if ($pnd != -1) $set[] = "pnd=$pnd";
        if ($gnc != -1) $set[] = "gnc=$gnc";
        if ($gxc != -1) $set[] = "gxc=$gxc";
        if ($pri != -1) $set[] = "pri=$pri";
        if ($rst != -1) $set[] = "rst=$rst";
        if ($skp != -1) $set[] = "skp=$skp";
        if ($ftr != -1) $set[] = "ftr=$ftr";
        if ($eps != -1) $set[] = "eps=$eps";
        if ($ems != -1) $set[] = "ems=$ems";
        if ($tld != -1) $set[] = "tld=$tld";

        if ($pat != '')
        {
            $value = urlencode($pat);
            $set[] = "pat=$value";
        }
        if ($src != '')
        {
            $value = urlencode($src);
            $set[] = "src=$value";
        }
        if ($txt != '')
        {
            $value = urlencode($txt);
            $set[] = "txt=$value";
        }
        if (($prv) && ($dbg)) $set[] = "debug=1";
    }


    function page_href(&$env,$page,$ord)
    {
        $self = $env['self'];
        $limt = $env['limt'];
        $a    = array("$self?p=$page");
        $a[]  = "o=$ord";
        $a[]  = "l=$limt";
        query_state($env,$a);
        return join('&',$a);
    }

   /*
    |  In general, D signifies the number of days of data we
    |  want to see, including today, except that we use
    |  signify that the field should not be displayed and
    |  zero to mean that any date is valid.
    |
    |   -1 --> not dispayed
    |    0 --> any date
    |    1 --> today since midnight
    |    2 --> yesterday
    |    3 --> day before yesterday
    */

    function date_code($when,$d)
    {
        if ($d > 1)
        {
            $when = days_ago($when,$d-1);
        }
        return $when;
    }


    function prox_options()
    {
        $m = 60;
        $h = $m * 60;
        return array
        (
            $m*5  =>  '5 minutes',
            $m*10 => '10 minutes',
            $m*20 => '20 minutes',
            $m*40 => '40 minutes',
            $h*1  => '1 hour',
            $h*2  => '2 hours',
            $h*3  => '3 hours',
            $h*4  => '4 hours',
            $h*5  => '5 hours'
        );
    }


    function filter_options($auth,$db)
    {
        $qu  = safe_addslashes($auth);
        $sql = "select S.id, S.name from\n"
             . " event.SavedSearches as S\n"
             . " left join event.SavedSearches as X\n"
             . " on X.name = S.name\n"
             . " and X.global = 0\n"
             . " and X.username = '$qu'\n"
             . " where S.username = '$qu'\n"
             . " or (S.global = 1 and (X.id is NULL))\n"
             . " order by name, id";
        $set = find_many($sql,$db);
        $out = array();
        reset($set);
        foreach ($set as $key => $row)
        {
             $sid = $row['id'];
             $out[$sid] = $row['name'];
        }
        return $out;
    }


    function nanotime($when)
    {
        $text = '<br>';
        if ($when > 0)
        {
            $that = date('m/d/y',time());
            $date = date('m/d/y',$when);
            $time = date('H:i:s',$when);
            $text = ($date == $that)? $time : "$date $time";
        }
        if ($when < 0)
        {
            $text = "running";
        }
        return $text;
    }


    function check_queue($env,$db)
    {
        $lock = array();
        $lpid = array();
        $priv = $env['priv'];
        $now  = $env['now'];
        if ($priv)
        {
            $lock = find_opt('notify_lock',$db);
            $lpid = find_opt('notify_pid',$db);
        }
        if (($lock) && ($lpid))
        {
            if ($lock['value'])
            {
                $when = '';
                $age  = 'unknown';
                $time = $lpid['modified'];
                $ownr = $lpid['value'];
                if ($now > $time)
                {
                    $when = nanotime($time);
                    $age  = age($now - $time);
                }

                $text = "Notify Queue Locked by $ownr since $when ($age)";
                echo "\n\n<br><h2>$text</h2><br>\n\n";
            }
        }
    }


    function time_options($midn)
    {
        $days = array(45,35,28,21,14,7,6,5,4,3,2,1,0,-1,-2,-3,-4,-5,-6,-7,-14,-21,-28,-35,-45);
        reset($days);
        foreach ($days as $key => $day)
        {
            $when = days_ago($midn,$day);
            $text = date('m/d/y',$when);
            $out[$when] = $text;
        }
        return $out;
    }

    function disp_options()
    {
        return array
        (
             -1 => constTagNone,
              0 => constTagAny
        );
    }

    function secs_options()
    {
        $out = disp_options();
        $set = frequency_options();

        reset($set);
        foreach ($set as $secs => $txt)
        {
            $out[$secs] = $txt;
        }
        return $out;
    }

    function next_options()
    {
        $xxx = 14 * 86400;
        $out = secs_options();
        $out[$xxx] = '2 weeks';
        return $out;
    }


    function days_options()
    {
        return array
        (
            0  => 'Never',
            7  => '1 Week',
            14 => '2 Weeks',
            21 => '3 Weeks',
            28 => '4 Weeks'
        );
    }


    function show_days($d)
    {
        switch ($d)
        {
            case  0: return '  never';
            case  7: return ' 1 week';
            case 14: return '2 weeks';
            case 21: return '3 weeks';
            case 28: return '4 weeks';
            default: return "$d days";
        }
    }


    function expr_options()
    {
        $out = disp_options();
        $set = days_options();

        reset($set);
        foreach ($set as $d => $name)
        {
            $out[$d+1] = $name;
        }
        return $out;
    }


    function gids_options($auth,$db)
    {
        $out = array( );
        $out[-1] = 'No change';
        $set = group_list($auth,$db);
        if ($set)
        {
            reset($set);
            foreach ($set as $gid => $name)
            {
                $out[$gid] = $name;
            }
        }
        return $out;
    }


    function filt_options($auth,$db)
    {
        $out = disp_options();
        $set = filter_options($auth,$db);
        reset($set);
        foreach ($set as $sid => $name)
        {
             $out[$sid] = $name;
        }
        return $out;
    }


    function owns_options(&$env,$db)
    {
        $set = array();
        $out = disp_options();
        if ($env['user']['priv_admin'])
        {
            $sql = "select userid, username\n"
                 . " from ".$GLOBALS['PREFIX']."core.Users\n"
                 . " order by username";
            $set = find_many($sql,$db);
        }
        if ($set)
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $uid = $row['userid'];
                $out[$uid] = $row['username'];
            }
        }
        return $out;
    }


    function enab_options()
    {
        return array
        (
             -1 => constTagNone,
              0 => constTagAny,
              1 => 'Disabled',
              2 => 'Enabled',
              3 => 'Invalid'
        );
    }

    function dest_options()
    {
        return array
        (
             -1 => constTagNone,
              0 => constTagAny,
              1 => 'None',
              2 => 'Console',
              3 => 'Email',
              4 => 'Console, e-mail'
        );
    }


    function destination(&$row)
    {
        $cons = ($row['console'])? 1 : 0;
        $mail = ($row['email'])?   1 : 0;
        $dest = (($mail*2) + $cons) + 1;
        $opts = dest_options();
        return $opts[$dest];
    }


   /*
    |  what a pointless waste of time.
    */

    function tlds_options($range)
    {
        $out = disp_options();
        for ($i = 1; $i <= 30; $i++)
        {
            $out[$i] = $i-1;
        }
        if ($range)
        {
            $out[ 40] = '30-39';
            $out[ 50] = '40-49';
            $out[ 60] = '50-59';
            $out[ 70] = '60-69';
            $out[ 80] = '70-79';
            $out[ 90] = '80-89';
            $out[100] = '90-99';
            $out[110] = '100 or more';
        }
        return $out;
    }


    function tlds_compare($tld)
    {
        if ($tld <= 39)
        {
            $min = $tld - 1;
            $txt = "= $min";
        }
        if ((40 <= $tld) && ($tld <= 100))
        {
            $min = $tld - 10;
            $max = $tld - 1;
            $txt = "between $min and $max";
        }
        if ($tld > 100)
        {
            $txt = '> 100';
        }
        return $txt;
    }


    function glbl_options()
    {
        return array
        (
             -1 => constTagNone,
              0 => constTagAny,
              1 => 'Local',
              2 => 'Global',
              3 => 'Debug'
        );
    }

    function prio_options()
    {
        return array
        (
            -1 => constTagNone,
             0 => constTagAny,
             1 => 1,
             2 => 2,
             3 => 3,
             4 => 4,
             5 => 5
        );
    }

    function rsts_options()
    {
        return array
        (
            -1 => constTagNone,
             0 => constTagAny,
             1 => 'Not Restricted',
             2 => 'Restricted'
        );
    }

    function sits_options()
    {
        return array
        (
            -1 => constTagNone,
             0 => constTagAny,
             1 => 'Not Filtered',
             2 => 'Filtered'
        );
    }

    function defs_options()
    {
        return array
        (
            -1 => constTagNone,
             0 => constTagAny,
             1 => 'No',
             2 => 'Yes'
        );
    }


    function past_options($midn,$days)
    {
        $opts = array
        (
            -2 => constTagNever,
            -1 => constTagNone,
             0 => constTagAny,
             1 => constTagToday
        );
        reset($days);
        foreach ($days as $key => $day)
        {
            $time = date_code($midn,$day);
            $text = date('D m/d',$time) . " ($day days)";
            $opts[$day] = $text;
        }
        return $opts;
    }


    function notify_control(&$env,$total,$db)
    {
        $auth = $env['auth'];
        $limt = $env['limt'];
        $page = $env['page'];
        $priv = $env['priv'];
        $self = $env['self'];
        $jump = $env['jump'];
        $ord  = $env['ord'];
        $adv  = $env['adv'];
        $form = $self . $jump;

        echo post_other('myform',$form);
        echo hidden('act','list');
        echo hidden('adv',$adv);
        echo hidden('page',$page);

        $days = array(2,3,4,5,6,7,8,9,10,11,12,13,14,21,30,60,90,120,150,180,365,720,3000);
        $lims = array(5,10,20,25,50,75,100,150,200,250,500,750,1000);

        if (!in_array($limt,$lims))
        {
            $lims[] = $limt;
            sort($lims,SORT_NUMERIC);
        }

        $midn = $env['midn'];
        $dsps = array('Expanded','Compact');
        $ords = ords();
        $glbs = glbl_options();
        $enbs = enab_options();
        $tlds = tlds_options(true);
        $secs = secs_options();
        $dsts = dest_options();
        $disp = disp_options();
        $opts = past_options($midn,$days);
        $flts = filt_options($auth,$db);
        $tiny = 50;
        $norm = 128;
        $wide = $norm*2 + 6;
        $yn = array('No','Yes');

        if (!$priv)
        {
            unset($glbs[3]);
        }

        $sel_include = GRPS_create_select_box($auth, constGroupIncludeTempTable,
                                              's_g_include', $env['gnc'],
                                              constEventNotifications, $db);

        $sel_exclude = GRPS_create_select_box($auth, constGroupExcludeTempTable,
                                              's_g_exclude', $env['gxc'],
                                              constEventNotifications, $db);

        $sel_suspend = GRPS_create_select_box($auth, constGroupSuspendTempTable,
                                              's_g_suspend', $env['pnd'],
                                              constEventNotifications, $db);

        $s_dbg = tiny_select('debug', $yn, $env['dbug'],1, $tiny);
        $s_dsp = tiny_select('dsp', $dsps, $env['dsp'], 1, $norm);
        $s_dst = tiny_select('dst', $dsts, $env['dst'], 1, $norm);
        $s_enb = tiny_select('enb', $enbs, $env['enb'], 1, $norm);
        $s_flt = tiny_select('flt', $flts, $env['flt'], 1, $wide);
        $s_frq = tiny_select('frq', $secs, $env['frq'], 1, $norm);
        $s_gbl = tiny_select('gbl', $glbs, $env['gbl'], 1, $norm);
        $s_lim = tiny_select('l',   $lims, $env['limt'],0, $tiny);
        $s_mal = tiny_select('mal', $disp, $env['mal'], 1, $norm);
        $s_ord = tiny_select('o',   $ords, $env['ord'], 1, $norm);
        $s_tld = tiny_select('tld', $tlds, $env['tld'], 1, $norm);
        $s_lst = tiny_select('lst', $opts, $env['lst'], 1, $norm);
        if ($adv)
        {
            $defs = defs_options();
            $exps = expr_options();
            $nxts = next_options();
            $rsts = rsts_options();
            $popt = prio_options();
            $owns = owns_options($env,$db);

            $s_crt = tiny_select('crt', $opts, $env['crt'], 1, $norm);
            $s_def = tiny_select('def', $defs, $env['def'], 1, $norm);
            $s_exp = tiny_select('exp', $exps, $env['exp'], 1, $norm);
            $s_lnk = tiny_select('lnk', $defs, $env['lnk'], 1, $norm);
            $s_mod = tiny_select('mod', $opts, $env['mod'], 1, $norm);
            $s_nxt = tiny_select('nxt', $nxts, $env['nxt'], 1, $norm);
            $s_own = tiny_select('own', $owns, $env['own'], 1, $norm);
            $s_pri = tiny_select('pri', $popt, $env['pri'], 1, $norm);
            $s_rst = tiny_select('rst', $rsts, $env['rst'], 1, $norm);
            $s_skp = tiny_select('skp', $defs, $env['skp'], 1, $norm);
            $s_ftr = tiny_select('ftr', $defs, $env['ftr'], 1, $norm);
            $s_eps = tiny_select('eps', $defs, $env['eps'], 1, $norm);
            $s_ems = tiny_select('ems', $defs, $env['ems'], 1, $norm);
        }

        $s_pat = tinybox('pat',40,$env['pat'],$norm);
        $s_txt = tinybox('txt',40,$env['txt'],$norm);
        $s_src = tinybox('src',40,$env['src'],$norm);

        $href = 'notify.htm';
        $open = "window.open('$href','help');";
        $help = click(constButtonHlp,$open);

        $tag  = ($adv)? constButtonLess : constButtonMore;
        $sub  = button(constButtonSub);
        $rset = button(constButtonRst);
        $tag  = button($tag);
        $head = table_header();
        $srch = pretty_header('Search Options',1);
        $disp = pretty_header('Display Options',1);
        $td   = 'td style="font-size: xx-small"';
        $ts   = $td . ' colspan="2"';
        $xn   = indent(4);
        $ddst = 'Destination - E-mail, Console';
        $rec  = 'Recipient';
        $dbug = '';
        if ($priv)
        {

            $dbg  = green('Debug');
            $dbug = <<< GORK

            <$td>$dbg<br>$s_dbg</td>
GORK;

        }
        $advanced = '';
        if ($adv)
        {
            $advanced = <<< ADVANCED

              <tr>
                <$td>Owner              <br>\n$s_own</td>
                <$td>Expiration         <br>\n$s_exp</td>
                <$td>Default ${rec}s    <br>\n$s_def</td>
                <$td>Restricted         <br>\n$s_rst</td>
              </tr>
              <tr>
                <$td>Next Run           <br>\n$s_nxt</td>
                <$td>Priority           <br>\n$s_pri</td>
                <$td>Links              <br>\n$s_lnk</td>
                <$td>Modified           <br>\n$s_mod</td>
              </tr>
              <tr>
                <$td>Skip Owner         <br>\n$s_skp</td>
                <$td>Suspend            <br>\n$sel_suspend</td>
                <$td>Include            <br>\n$sel_include</td>
                <$td>Exclude            <br>\n$sel_exclude</td>
              </tr>
              <tr>
                <$td>E-mail footer         <br>\n$s_ftr</td>
                <$td>Per site e-mail       <br>\n$s_eps</td>
                <$td>Site e-mail as sender <br>\n$s_ems</td>
                <$td>Created               <br>\n$s_crt</td>
              </tr>

ADVANCED;
        }




        echo <<< XXXX

        <table>
        <tr valign="top">
          <td rowspan="2">

            $head

            $srch

            <tr><td>
              <table border="0" width="100%">
              <tr>
                <$td>Name Contains      <br>\n$s_pat</td>
                <$td>Scope              <br>\n$s_gbl</td>
                <$td>Frequency          <br>\n$s_frq</td>
                <$td>State              <br>\n$s_enb</td>
              </tr>
              <tr>
                <$td>$rec Contains      <br>\n$s_txt</td>
                <$td>E-mail ${rec}s     <br>\n$s_mal</td>
                <$td>$ddst              <br>\n$s_dst</td>
                <$td>Last Run           <br>\n$s_lst</td>
              </tr>
              <tr>
                <$td>Filter Contains    <br>\n$s_src</td>
                <$ts>Event Filter       <br>\n$s_flt</td>
                <$td>Threshold          <br>\n$s_tld</td>
              </tr> $advanced

              </table>
            </td></tr>
            </table>

          </td>

          <td rowspan="2">
            $xn
          </td>

          <td>
            $head
            $disp

            <tr><td>
              <table border="0" width="100%">
              <tr>
                <$td>Page Size  <br>\n$s_lim</td>
                <$td>Sort By    <br>\n$s_ord</td>
                <$td>Display    <br>\n$s_dsp</td>
                $dbug
              </tr>
              </table>
            </td></tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            <table width="100%">
            <tr><td align="left" valign="bottom">

              ${sub}${xn}${help}${xn}${tag}${xn}${rset}

            </td></tr>
            </table>
           <td>
        </tr>
        </table>

        <br clear="all">


XXXX;

        echo form_footer();
    }


    /*
    |  When adding new search options, the call to
    |   $ord = tag_int('o',0,$NUM,$xxx)
    |   must set $NUM to the highest integer index
    |   in the ords() array.
    */
    function ords()
    {
        $a = 'ascending';
        $d = 'descending';
        return array
        (
            0 => "Name ($a)",
            1 => "Name ($d)",
            2 => "Created ($d)",
            3 => "Created ($a)",
            4 => "Modify ($d)",
            5 => "Modify ($a)",
            6 => "Event Filter ($a)",
            7 => "Event Filter ($d)",
            8 => "Owner ($a)",
            9 => "Owner ($d)",
           10 => "Global ($a)",
           11 => "Global ($d)",
           12 => "Frequency ($a)",
           13 => "Frequency ($d)",
           14 => "Priority ($a)",
           15 => "Priority ($d)",
           16 => "Enabled ($a)",
           17 => "Enabled ($d)",
           18 => "Threshold ($a)",
           19 => "Threshold ($d)",
           20 => "Last Run ($d)",
           21 => "Last Run ($a)",
           22 => "Id ($a)",
           23 => "Id ($d)",
           24 => "Next Run ($a)",
           25 => "Next Run ($d)",
           26 => "E-mail Recipients ($a)",
           27 => "E-mail Recipients ($d)",
           28 => "Destination ($a)",
           29 => "Destination ($d)",
           30 => "Restricted ($a)",
           31 => "Restricted ($d)",
           32 => "Expiration ($a)",
           33 => "Expiration ($d)",
           34 => "Site Filter ($a)",
           35 => "Site Filter ($d)",
           38 => "Default Recipients ($a)",
           39 => "Default Recipients ($d)",
           40 => "Links ($a)",
           41 => "Links ($d)",
           42 => "Suspend ($a)",
           43 => "Suspend ($d)",
           44 => "Skip Owner ($d)",
           45 => "Skip Owner ($a)",
           46 => "E-mail Footer ($a)",
           47 => "E-mail Footer ($d)",
           48 => "E-mail Per Site ($a)",
           49 => "E-mail Per Site ($d)",
           50 => "Use Site E-mail ($a)",
           51 => "Use Site E-mail ($d)",
           52 => "Include ($a)",
           53 => "Include ($d)",
           54 => "Exclude ($a)",
           55 => "Exclude ($d)",
        );
    }


    /*
    |  When adding new search options, the call to
    |   $ord = tag_int('o',0,$NUM,$xxx)
    |   must set $NUM to the highest integer index
    |   in the order() array.
    */
    function order($ord)
    {
        switch ($ord)
        {
            case  0: return 'name, username, id';
            case  1: return 'name desc, username, id';
            case  2: return 'created desc, id';
            case  3: return 'created, id';
            case  4: return 'modified desc, id';
            case  5: return 'modified, id';
            case  6: return 'search, name, id';
            case  7: return 'search desc, name, id';
            case  8: return 'username, name, id';
            case  9: return 'username desc, name, id';
            case 10: return 'global, name, id';
            case 11: return 'global desc, name, id';
            case 12: return 'seconds, name, id';
            case 13: return 'seconds desc, name, id';
            case 14: return 'priority, name, id';
            case 15: return 'priority desc, name, id';
            case 16: return 'enabled, name, id';
            case 17: return 'enabled desc, name, id';
            case 18: return 'threshold, name, id';
            case 19: return 'threshold desc, name, id';
            case 20: return 'last_run desc, name, id';
            case 21: return 'last_run, name, id';
            case 22: return 'id';
            case 23: return 'id desc';
            case 24: return 'next_run, global, id';
            case 25: return 'next_run desc, global, id';
            case 26: return 'email, emaillist, name, id desc';
            case 27: return 'email desc, emaillist desc, name desc, id';
            case 28: return 'email desc, console, name, id';
            case 29: return 'email, console desc, name, id';
            case 30: return 'solo, name, id';
            case 31: return 'solo desc, name, id';
            case 32: return 'days, name, id';
            case 33: return 'days desc, name, id';
    //      case 36: return 'excluded, name, id desc';
    //      case 37: return 'excluded desc, name, id';
            case 38: return 'defmail, name desc, id';
            case 39: return 'defmail desc, name, id';
            case 40: return 'links, name desc, id';
            case 41: return 'links desc, name, id';
            case 42: return 'suspend desc, name desc, id';
            case 43: return 'suspend, name, id';
            case 44: return 'skip_owner desc, name, id';
            case 45: return 'skip_owner, name desc, id';
            case 46: return 'email_footer, name, id';
            case 47: return 'email_footer desc, name desc, id';
            case 48: return 'email_per_site, name, id';
            case 49: return 'email_per_site desc, name, id';
            case 50: return 'email_sender, name, id';
            case 51: return 'email_sender desc, name, id';
            case 52: return 'group_include desc, name , id desc';
            case 53: return 'group_include, name';
            case 54: return 'group_exclude desc, name, id desc';
            case 55: return 'group_exclude, name';
            default: return order(0);
        }
    }

    function restrict_time(&$env,&$trm,$code,$field)
    {
        $valu = $env[$code];
        if ($valu > 0)
        {
            $midn  = $env['midn'];
            $time  = date_code($midn,$valu);
            $trm[] = "N.$field > $time";
        }
        if ($valu == -2)
        {
            $trm[] = "N.$field = 0";
        }
    }

   /*
    |  We want to use the same procedure to generate sql for both
    |  the counting and the selection of records.
    */

    function gen_query(&$env,$count,$num)
    {
        $def = $env['def'];
        $dst = $env['dst'];
        $enb = $env['enb'];
        $exp = $env['exp'];
        $flt = $env['flt'];
        $frq = $env['frq'];
        $gbl = $env['gbl'];
        $lnk = $env['lnk'];
        $now = $env['now'];
        $nxt = $env['nxt'];
        $own = $env['own'];
        $pat = $env['pat'];
        $pnd = $env['pnd'];
        $gnc = $env['gnc'];
        $gxc = $env['gxc'];
        $pri = $env['pri'];
        $rst = $env['rst'];
        $skp = $env['skp'];
        $ftr = $env['ftr'];
        $eps = $env['eps'];
        $ems = $env['ems'];
        $src = $env['src'];
        $tld = $env['tld'];
        $txt = $env['txt'];

        $auth = $env['auth'];
        $qu   = safe_addslashes($auth);
        if ($count)
        {
            $sel = "select count(N.id) from";
        }
        else
        {
            $sel = "select N.*,\n"
                 . " S.name as search from";
        }
        $lft = array();
        $ons = array();
        $tab = array
        (
            'Notifications as N',
            'SavedSearches as S'
        );
        $trm = array
        (
            'N.search_id = S.id'
        );

        if ($pri > 0)   // prio_options()
        {
            $trm[] = "N.priority = $pri";
        }

        $c = 'N.console';
        $e = 'N.email';
        switch ($dst)      // dest_options()
        {
            case  1: $trm[] = "$c = 0"; $trm[] = "$e = 0"; break;
            case  2: $trm[] = "$c = 1"; $trm[] = "$e = 0"; break;
            case  3: $trm[] = "$c = 0"; $trm[] = "$e = 1"; break;
            case  4: $trm[] = "$c = 1"; $trm[] = "$e = 1"; break;
            default: break;
        }

        if ($pat != '')
        {
            $value = str_replace('%','\%',$pat);
            $value = str_replace('_','\_',$value);
            $value = safe_addslashes($value);
            $trm[] = "N.name like '%$value%'";
        }

        if ($src != '')
        {
            $value = str_replace('%','\%',$src);
            $value = str_replace('_','\_',$value);
            $value = safe_addslashes($value);
            $trm[] = "S.name like '%$value%'";
        }

        if ($txt != '')
        {
            $value = str_replace('%','\%',$txt);
            $value = str_replace('_','\_',$value);
            $value = safe_addslashes($value);
            $trm[] = "N.emaillist like '%$value%'";
            $trm[] = 'N.email = 1';
        }

        if ($nxt > 0)
        {
            $value = time() + $nxt;
            $trm[] = "N.next_run < $value";
            $trm[] = "N.next_run > 0";
        }
        if ($flt > 0)
        {
            $trm[] = "N.search_id = $flt";
        }
        if ($own > 0)
        {
            $tab[] = $GLOBALS['PREFIX'].'core.Users as U';
            $trm[] = 'U.username = N.username';
            $trm[] = "U.userid = $own";
        }
        if ($tld > 0)
        {
            $value = tlds_compare($tld);
            $trm[] = "N.threshold $value";
        }
        if ($lnk > 0)
        {
            $value = $lnk - 1;
            $trm[] = "N.links = $value";
        }
        if ($skp > 0)
        {
            $value = $skp - 1;
            $trm[] = "N.skip_owner = $value";
        }
        if ($ftr > 0)
        {
            $value = $ftr - 1;
            $trm[] = "N.email_footer = $value";
        }
        if ($eps > 0)
        {
            $value = $eps - 1;
            $trm[] = "N.email_per_site = $value";
        }
        if ($ems > 0)
        {
            $value = $ems - 1;
            $trm[] = "N.email_sender = $value";
        }
        if ($pnd > 0)
        {
            $trm[] = "N.suspend  > $now and N.group_suspend regexp '(^|,)$pnd(,|$)'";
        }
        if ($gnc > 0)
        {
            $trm[] = "N.group_include regexp '(^|,)$gnc(,|$)'";
        }
        if ($gxc > 0)
        {
            $trm[] = "N.group_exclude regexp '(^|,)$gxc(,|$)'";
        }
        if ($def > 0)
        {
            $value = $def - 1;
            $trm[] = "N.defmail = $value";
        }

        // expr_options(), days_options
        if ($exp > 0)
        {
            $value = $exp - 1;
            $trm[] = "N.days = $value";
        }
        if ($frq > 0)
        {
            $trm[] = "N.seconds = $frq";
        }

        // rsts_options()
        if ($rst > 0)
        {
            $value = $rst-1;
            $trm[] = "N.solo = $value";
        }

       /*
        |  Global:
        |   -1: same as 0, but not displayed
        |    0: both, honor local override
        |    1: locals owned by current user
        |    2: globals, honor local override
        |    3: debug only, show all
        |
        |    glbl_options()
        */

        if (($gbl <= 0) && ($own <= 0))
        {
            $u = "username = '$qu'";
            $lft[] = 'Notifications as X';
            $ons[] = 'N.name = X.name';
            $ons[] = 'X.global = 0';
            $ons[] = "X.$u";
            $trm[] = "((N.$u) or (N.global = 1 and X.id is NULL))";
        }
        if (($gbl == 1) && ($own <= 0))
        {
            $trm[] = "N.username = '$qu'";
            $trm[] = 'N.global = 0';
        }
        if (($gbl == 1) && ($own > 0))
        {
            $trm[] = 'N.global = 0';
        }
        if (($gbl == 2) && ($own > 0))
        {
            $trm[] = 'N.global = 1';
        }
        if (($gbl == 2) && ($own <= 0))
        {
            $lft[] = 'Notifications as X';
            $ons[] = 'X.name = N.name';
            $ons[] = 'X.global != N.global';
            $ons[] = "X.username = '$qu'";
            $trm[] = 'N.global = 1';
            $trm[] = 'X.id is NULL';
        }

        // enab_options()

        if ($enb == 1)
        {
            $trm[] = 'N.enabled = 0';
        }
        if ($enb == 2)
        {
            $trm[] = 'N.enabled = 1';
        }
        if ($enb > 2)
        {
            $trm[] = 'N.enabled > 1';
        }
        restrict_time($env,$trm,'mod','modified');
        restrict_time($env,$trm,'crt','created');
        restrict_time($env,$trm,'lst','last_run');

        $onss = '';
        $lfts = '';
        $tabs = join(",\n ",$tab);
        $trms = join("\n and ",$trm);
        if ($lft)
        {
            $lj   = 'left join';
            $txt  = join("\n $lj ",$lft);
            $lfts = " $lj $txt\n";
        }
        if ($ons)
        {
            $txt  = join("\n and ",$ons);
            $onss = " on $txt\n";
        }

        if ($count)
        {
            $sql = "$sel\n $tabs\n${lfts}${onss} where $trms";
        }
        else
        {
            $ord  = $env['ord'];
            $page = $env['page'];
            $limt = $env['limt'];
            $ords = order($ord);
            debug_note("ord:$ord, page:$page, size:$limt");
            $pmin = ($page > 0)? $limt * $page : 0;
            if (($num <= $limt) || ($num <= $pmin))
            {
                $pmin = 0;
            }
            $sql = "$sel\n $tabs\n${lfts}${onss} where $trms\n"
                 . " order by $ords\n"
                 . " limit $pmin, $limt";
        }
        return $sql;
    }


    function timestamp($time)
    {
        return ($time)? date('m/d/y H:i:s',$time) : 'never';
    }


    function enabled($code)
    {
        switch ($code)
        {
            case  0: return 'Disabled';
            case  1: return 'Enabled';
            default: return 'Invalid';
        }
    }

    function color_data($args,$color,$tiny)
    {
        $m  = '';
        $td = ($tiny)? 'td style="font-size: x-small"' : 'td';
        if ($args)
        {
            $m .= "<tr bgcolor=\"$color\">\n";
            reset($args);
            foreach ($args as $key => $data)
            {
                $m .= "<$td>$data</td>\n";
                $td = 'td';
            }
            $m .= "</tr>\n";
        }
        return $m;
    }


   /*
    |  http://www.htmlgoodies.com/tutors/colors.html
    |
    |  wheat         '#F5DEB3'
    |  lightsalmon   '#FFA07A'
    |  gold          '#FFD700'
    |  lightpink     '#FFB6CA'
    |  lemonchiffon  '#FFFACD'
    |  lightgreen    '#90EE90'
    |  lightskyblue  '#87CEFA'
    |  aquamarine    '#7FFFD4'
    */

    function prio_color($code)
    {
        switch ($code)
        {
            case  0: return 'wheat';
            case  1: return 'lightpink';
            case  2: return 'lemonchiffon';
            case  3: return 'lightgreen';
            case  4: return 'aquamarine';
            case  5: return 'lightskyblue';
            default: return 'grey';
        }
    }



    function age($secs)
    {
        if ($secs <= 0) $secs = 0;

        $ss = intval($secs);
        $mm = intval($secs / 60);
        $hh = intval($secs / 3600);
        $dd = intval($secs / 86400);

        $ss = $ss % 60;
        $mm = $mm % 60;
        $hh = $hh % 24;

        if ($secs < 3600)
            $txt = sprintf('%d:%02d',$mm,$ss);
        if ((3600 <= $secs) && ($secs < 86400))
            $txt = sprintf('%d:%02d:%02d',$hh,$mm,$ss);
        if ((86400 <= $secs) && ($dd <= 7))
            $txt = sprintf('%d %d:%02d:%02d',$dd,$hh,$mm,$ss);
        if (8 <= $dd)
        {
            $dd  = intval(round($secs / 86400));
            $txt = "$dd days";
        }

        return $txt;
    }


    function speak_freq($secs)
    {
        $mm = intval($secs / 60);
        $hh = intval($secs / 3600);
        $dd = intval($secs / 86400);
        $m  = $secs % 60;
        $h  = $secs % 3600;
        $d  = $secs % 86400;
        if (($mm < 120) && ($m == 0))
        {
            $txt = "$mm minutes";
        }
        else if (($hh <= 48) && ($h == 0))
        {
            $txt = "$hh hours";
        }
        else if ($d == 0)
        {
            $txt = "$dd days";
        }
        else
        {
            $txt = age($secs);
        }
        return $txt;
    }


    function notify_type($type)
    {
        switch ($type)
        {
            case constScheduleClassic  : return 'periodic';
            case constScheduleCrontab  : return 'crontab';
            case constScheduleProximity: return 'proximity';
            case constScheduleOneShot  : return 'oneshot';
            default                    : return 'invalid';
        }
    }


    function show(&$env,&$args,$tag,$col)
    {
        if ($env[$tag]) $args[] = $col;
    }


    function notify_table(&$env,$set,$total,$db)
    {
        debug_note("notify_table()");
        $ord  = $env['ord'];
        $lim  = $env['limt'];
        $self = $env['self'];
        $priv = $env['priv'];
        $auth = $env['auth'];
        $jump = $env['jump'];

        $dbg = $env['dbug'];
        $dsp = $env['dsp'];
        $now = $env['now'];

        $args = array("$self?act=list&l=$lim");
        query_state($env,$args);
        $o = join('&',$args) . "&o";

        $name = ($ord ==  0)? "$o=1"  : "$o=0";     // name   0, 1
        $ctim = ($ord ==  2)? "$o=3"  : "$o=2";     // cret   2, 3
        $mtim = ($ord ==  4)? "$o=5"  : "$o=4";     // modd   4, 5
        $srch = ($ord ==  6)? "$o=7"  : "$o=6";     // srch   6, 7
        $user = ($ord ==  8)? "$o=9"  : "$o=8";     // user   8, 9
        $glob = ($ord == 10)? "$o=11" : "$o=10";    // glob  10, 11
        $secs = ($ord == 12)? "$o=13" : "$o=12";    // secs  12, 13
        $prio = ($ord == 14)? "$o=15" : "$o=14";    // prio  14, 15
        $enab = ($ord == 16)? "$o=17" : "$o=16";    // enab  16, 17
        $thld = ($ord == 18)? "$o=19" : "$o=18";    // thld  18, 19
        $last = ($ord == 20)? "$o=21" : "$o=20";    // last  20, 21
        $ntid = ($ord == 22)? "$o=23" : "$o=22";    // ntid  22, 23
        $next = ($ord == 24)? "$o=25" : "$o=24";    // next  24, 25
        $mail = ($ord == 26)? "$o=27" : "$o=26";    // mail  26, 27
        $dest = ($ord == 28)? "$o=29" : "$o=28";    // dest  28, 29
        $solo = ($ord == 30)? "$o=31" : "$o=30";    // solo  30, 31
        $days = ($ord == 32)? "$o=33" : "$o=32";    // days  32, 33
    //  $xcld = ($ord == 36)? "$o=37" : "$o=36";    // xcld  36, 37
        $defm = ($ord == 38)? "$o=39" : "$o=38";    // defm  38, 39
        $lnks = ($ord == 40)? "$o=41" : "$o=40";    // lnks  40, 41
        $susp = ($ord == 42)? "$o=43" : "$o=42";    // susp  42, 43
        $skip = ($ord == 44)? "$o=45" : "$o=44";    // skip  44, 45
        $eftr = ($ord == 46)? "$o=47" : "$o=46";
        $eeps = ($ord == 48)? "$o=49" : "$o=48";
        $eems = ($ord == 50)? "$o=51" : "$o=50";
        $ginc = ($ord == 52)? "$o=53" : "$o=52";
        $gexc = ($ord == 54)? "$o=55" : "$o=54";

        $acts = 'Action';
        $ntid = html_jump($ntid,$jump,'Id');
        $lnks = html_jump($lnks,$jump,'Links');
        $enab = html_jump($enab,$jump,'State');
        $glob = html_jump($glob,$jump,'Scope');
        $user = html_jump($user,$jump,'Owner');
        $ctim = html_jump($ctim,$jump,'Create');
        $mtim = html_jump($mtim,$jump,'Modify');
    //  $xcld = html_jump($xcld,$jump,'Exclude');
        $susp = html_jump($susp,$jump,'Suspend');
        $ginc = html_jump($ginc,$jump,'Include');
        $gexc = html_jump($gexc,$jump,'Exclude');
        $prio = html_jump($prio,$jump,'Priority');
        $next = html_jump($next,$jump,'Next Run');
        $last = html_jump($last,$jump,'Last Run');
        $secs = html_jump($secs,$jump,'Frequency');
        $thld = html_jump($thld,$jump,'Threshold');
        $solo = html_jump($solo,$jump,'Restricted');
        $skip = html_jump($skip,$jump,'Skip Owner');
        $eftr = html_jump($eftr,$jump,'E-mail Footer');
        $eeps = html_jump($eeps,$jump,'E-mail Per Site');
        $eems = html_jump($eems,$jump,'Use Site E-mail');
        $days = html_jump($days,$jump,'Expiration');
        $dest = html_jump($dest,$jump,'Destination');
        $srch = html_jump($srch,$jump,'Event Filter');
        $name = html_jump($name,$jump,'Notification Name');
        $mail = html_jump($mail,$jump,'E-mail Recipients');
        $defm = html_jump($defm,$jump,'Default Recipients');

        $args = array( );
        show($env,$args,'d_act',$acts);
        show($env,$args,'d_nam',$name);
        show($env,$args,'d_flt',$srch);
        show($env,$args,'d_own',$user);
        show($env,$args,'d_pri',$prio);
        show($env,$args,'d_frq',$secs);
        show($env,$args,'d_crt',$ctim);
        show($env,$args,'d_mod',$mtim);
        show($env,$args,'d_nxt',$next);
        show($env,$args,'d_lst',$last);
        show($env,$args,'d_gbl',$glob);
        show($env,$args,'d_tld',$thld);
        show($env,$args,'d_nid',$ntid);
        show($env,$args,'d_dst',$dest);
        show($env,$args,'d_def',$defm);
        show($env,$args,'d_mal',$mail);
        show($env,$args,'d_enb',$enab);
        show($env,$args,'d_rst',$solo);
        show($env,$args,'d_skp',$skip);
        show($env,$args,'d_ftr',$eftr);
        show($env,$args,'d_eps',$eeps);
        show($env,$args,'d_ems',$eems);
        show($env,$args,'d_exp',$days);
    //  show($env,$args,'d_xcl',$xcld);
        show($env,$args,'d_pnd',$susp);
        show($env,$args,'d_gnc',$ginc);
        show($env,$args,'d_gxc',$gexc);
        show($env,$args,'d_lnk',$lnks);

        if (($set) && ($args))
        {
            $defs = $env['user']['notify_mail'];
            $defs = ($defs)? "<i>$defs</i>" : '';
            $cols = safe_count($args);
            $text = 'Notifications';
            $act  = 'search.php?act=view&sid';
            $tiny = ($dsp)? 0 : 1;
            $acts = '<br>';
            if (($total > $lim) || ($total > 40))
            {
                $text = "$text &nbsp; ($total found)";
            }

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($args,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $nid  = $row['id'];
                $solo = $row['solo'];
                $days = $row['days'];
                $mail = $row['email'];
                $type = $row['ntype'];
                $glob = $row['global'];
                $defm = $row['defmail'];
                $enab = $row['enabled'];
                $secs = $row['seconds'];
                $susp = $row['suspend'];
                $ginc = $row['group_include'];
                $gexc = $row['group_exclude'];
                $gsus = $row['group_suspend'];
                $prio = $row['priority'];
    //          $xcld = $row['excluded'];
                $sid  = $row['search_id'];
                $thld = $row['threshold'];
                $skip = $row['skip_owner'];
                $eftr = $row['email_footer'];
                $eeps = $row['email_per_site'];
                $eems = $row['email_sender'];

                $name = disp($row,'name');
                $srch = disp($row,'search');
                $user = disp($row,'username');

                $mine = ($user == $auth)? 1 : 0;
                $list = '';
                if ($mail)
                {
                    $list = $row['emaillist'];
                    if (($glob) || ($mine))
                    {
                        if (($defm) && ($defs))
                        {
                            $list = ($list)? "$defs,$list" : $defs;
                        }
                    }
                }
                $cmd  = "$self?nid=$nid&act";
                $norm = ($type == constScheduleClassic)? 1 : 0;
                $list = ($list)? str_replace(',','<br>',$list) : '<br>';
    //          $xcld = ($xcld)? str_replace(',','<br>',$xcld) : '<br>';
                $glbl = ($glob)? 'Global' : 'Local';
                $solo = ($solo)? 'Restricted' : 'Not Restricted';
                $defm = ($defm)? 'Yes' : 'No';
                $lnks = ($lnks)? 'Yes' : 'No';
                $skip = ($skip)? 'Yes' : 'No';
                $eftr = ($eftr)? 'Yes' : 'No';
                $eeps = ($eeps)? 'Yes' : 'No';
                $eems = ($eems)? 'Yes' : 'No';

                $ginc = find_mgrp_gid($ginc, constReturnGroupTypeMany, $db);
                $gexc = find_mgrp_gid($gexc, constReturnGroupTypeMany, $db);
                $gsus = find_mgrp_gid($gsus, constReturnGroupTypeMany, $db);

                $ginc = GRPS_edit_group_detail($ginc);
                $gexc = GRPS_edit_group_detail($gexc);
                $gsus = GRPS_edit_group_detail($gsus);

                $ginc = ($ginc)? ($ginc) : 'No';
                $gexc = ($gexc)? ($gexc) : 'No';
                $susp = ($now < $susp)? date('m/d/Y',$susp) : 'No';
                $susp = $gsus . $susp;

                $freq = ($norm)? speak_freq($secs) : notify_type($type);
                $dest = destination($row);
                $ctim = nanotime($row['created']);
                $mtim = nanotime($row['modified']);
                $next = nanotime($row['next_run']);
                $last = nanotime($row['last_run']);
                if ($tiny)
                {
                    $ax = array( );
                    if (($glob) || ($mine))
                    {
                        $ax[] = html_link("$cmd=copy",'[copy]');
                    }
                    if ($mine)
                    {
                        $ax[] = html_link("$cmd=edit",'[edit]');
                        $ax[] = html_link("$cmd=cdel",'[delete]');
                        if ($enab == 1)
                            $ax[] = html_link("$cmd=disb",'[disable]');
                        else
                            $ax[] = html_link("$cmd=enab",'[enable]');
                    }
                    if (($glob) && (!$mine))
                    {
                        $ax[] = html_link("$cmd=over",'[edit]');
                        if ($enab == 1)
                            $ax[] = html_link("$cmd=dovr",'[disable]');
                        else
                            $ax[] = html_link("$cmd=eovr",'[enable]');
                    }
                    if (!$ax)
                    {
                        $ax[] = html_link("$cmd=view",'[details]');
                    }
                    $acts = join("<br>\n",$ax);
                }

                $days = show_days($days);
                $enab = enabled($enab);
                $text = prio_color($prio);

               /*
                |  I'd prefer to let the user decide for himself
                |  if he wants a new window or not ... I find
                |  websites that open lots of windows to be
                |  annoying ... but I was overruled.
                */

                $srch = html_page("$act=$sid",$srch);
                $name = html_page("$cmd=view",$name);
                $args = array( );
                show($env,$args,'d_act',$acts);
                show($env,$args,'d_nam',$name);
                show($env,$args,'d_flt',$srch);
                show($env,$args,'d_own',$user);
                show($env,$args,'d_pri',$prio);
                show($env,$args,'d_frq',$freq);
                show($env,$args,'d_crt',$ctim);
                show($env,$args,'d_mod',$mtim);
                show($env,$args,'d_nxt',$next);
                show($env,$args,'d_lst',$last);
                show($env,$args,'d_gbl',$glbl);
                show($env,$args,'d_tld',$thld);
                show($env,$args,'d_nid',$nid);
                show($env,$args,'d_dst',$dest);
                show($env,$args,'d_def',$defm);
                show($env,$args,'d_mal',$list);
                show($env,$args,'d_enb',$enab);
                show($env,$args,'d_rst',$solo);
                show($env,$args,'d_skp',$skip);
                show($env,$args,'d_ftr',$eftr);
                show($env,$args,'d_eps',$eeps);
                show($env,$args,'d_ems',$eems);
                show($env,$args,'d_exp',$days);
    //          show($env,$args,'d_xcl',$xcld);
                show($env,$args,'d_pnd',$susp);
                show($env,$args,'d_gnc',$ginc);
                show($env,$args,'d_gxc',$gexc);
                show($env,$args,'d_lnk',$lnks);

                echo color_data($args,$text,$tiny);
            }

            echo table_footer();

            echo prevnext($env,$total);
        }
        else
        {
            $text = 'There were no matching status records ...';
            echo para($text);
        }
    }


    /*
    |  $nid = notification id
    |  $db  = database handle
    |
    |  Returns all values of the given Notification
    |  in an arrary indexed by field name.
    */
    function find_notify($nid,$db)
    {
        $row = array();
        if ($nid > 0)
        {
            $sql = "select * from Notifications\n"
                 . " where id = $nid";
            $row = find_one($sql,$db);
        }
        return $row;
    }

    function global_notify_exists($name,$nid,$db)
    {
        $qn  = safe_addslashes($name);
        $sql = "select * from Notifications\n"
             . " where id != $nid\n"
             . " and name = '$qn'\n"
             . " and global = 1";
        $set = find_many($sql,$db);
        return ($set)? true : false;
    }

    function owned_notify_exists($name,$auth,$nid,$db)
    {
        $qn  = safe_addslashes($name);
        $qa  = safe_addslashes($auth);
        $sql = "select * from Notifications\n"
             . " where id != $nid\n"
             . " and name = '$qn'\n"
             . " and username = '$qa'";
        $set = find_one($sql,$db);
        return ($set)? true : false;
    }

    function find_notify_name($name,$glob,$user,$db)
    {
        $row = array();
        if ($name)
        {
            $qn  = safe_addslashes($name);
            $sql = "select * from Notifications\n"
                 . " where name = '$qn'\n"
                 . " and global = $glob";
            if ($user)
            {
                $qu  = safe_addslashes($user);
                $sql = "$sql\n and username = '$qu'";
            }
            $row = find_one($sql,$db);
        }
        return $row;
    }


    function touch_notify($nid,$db)
    {
        if ($nid > 0)
        {
            $now = time();
            $sql = "update Notifications set\n"
                 . " modified = $now\n"
                 . " where id = $nid";
            redcommand($sql,$db);
        }
    }

    function find_search($sid,$db)
    {
        $row = array();
        if ($sid > 0)
        {
            $sql = "select * from SavedSearches\n"
                 . " where id = $sid";
            $row = find_one($sql,$db);
        }
        return $row;
    }


    function find_notify_count(&$env,$db)
    {
        $num = 0;
        $sql = gen_query($env,1,0);
        $res = redcommand($sql,$db);
        if ($res)
        {
            $num = mysqli_result($res, 0);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
        debug_note("There are $num total matching records.");
        return $num;
    }

    function delete_conf(&$env,$db)
    {
        echo again($env);
        $nid = $env['nid'];
        $row = find_notify($nid,$db);
        if ($row)
        {
            $admn = $env['user']['priv_admin'];
            $auth = $env['auth'];
            $user = $row['username'];
            $mine = ($user == $auth);
            if (($mine) || ($admn))
            {
                $name = $row['name'];
                $self = $env['self'];
                $href = "$self?act=rdel&nid=$nid";
                $yes  = html_link($href,'[Yes]');
                $no   = html_link($self,'[No]');
                $in   = indent(4);

                echo <<< HERE

                <p>Do you really want to delete <b>$name</b>?</p>
                <p>${yes}${in}${no}</p>
HERE;
            }
        }
        echo again($env);
    }


    function add_schedule($nid,$type,$valu,$db)
    {
        $sql = "insert into NotifySchedule set\n"
             . " nid = $nid,\n"
             . " type = $type,\n"
             . " data = $valu";
        $res = redcommand($sql,$db);
        return affected($res,$db);
    }

    function purge_records($sql,$db)
    {
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
        debug_note("$num records removed");
        return $num;
    }

    function kill_invalid($nid,$db)
    {
        $tag = constCronInvalid;
        $sql = "delete from NotifySchedule\n"
             . " where nid = $nid\n"
             . " and type = $tag";
        return purge_records($sql,$db);
    }

    function add_invalid($nid,$db)
    {
        $now = time();
        return add_schedule($nid,constCronInvalid,$now,$db);
    }

    function kill_valid($nid,$db)
    {
        $tag = constCronInvalid;
        $sql = "delete from NotifySchedule\n"
             . " where nid = $nid\n"
             . " and type != $tag";
        return purge_records($sql,$db);
    }

    function kill_notify($nid,$db)
    {
        $sql = "delete from Notifications\n"
             . " where id = $nid";
        return purge_records($sql,$db);
    }

    function kill_schedule($nid,$db)
    {
        $sql = "delete from NotifySchedule\n"
             . " where nid = $nid";
        return purge_records($sql,$db);
    }


    function kill_associates($nid, $db)
    {
        $sch = kill_schedule($nid, $db);
        return $sch;
    }


    function disable_act(&$env,$db)
    {
        echo again($env);
        $nid  = $env['nid'];
        $row  = find_notify($nid,$db);
        $num  = 0;
        $good = false;
        if ($row)
        {
            $auth = $env['auth'];
            $admn = $env['user']['priv_admin'];
            $name = $row['name'];
            $user = $row['username'];
            $enab = $row['enabled'];
            $mine = ($user == $auth);
            if (($mine) || ($admn))
            {
                $good = ($enab != 0);
            }
        }
        if ($good)
        {
            $now = time();
            $sql = "update Notifications set\n"
                 . " enabled = 0,\n"
                 . " this_run = 0,\n"
                 . " next_run = 0,\n"
                 . " modified = $now\n"
                 . " where id = $nid\n"
                 . " and 0 <= next_run\n"
                 . " and enabled != 0";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        if ($num)
        {
            $text = "Notification <b>$name</b> has been disabled.";
            echo para($text);
            notify_detail_table($env,$nid,$db);
        }
        else
        {
            echo para('No change ...');
        }
        echo again($env);
    }


    function copy_shed($src,$dst,$db)
    {
        $good = true;
        $set  = load_shed($src,$db);
        if ($set)
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                if ($good)
                {
                    $type = $row['type'];
                    $data = $row['data'];
                    $what = add_schedule($dst,$type,$data,$db);
                    $good = ($what > 0);
                }
            }
        }
        return $good;
    }



    function over_enab(&$env,$db)
    {
        debug_note('over_enab()');
        echo again($env);
        $xid  = 0;
        $cron = 0;
        $now  = $env['now'];
        $nid  = $env['nid'];
        $row  = find_notify($nid,$db);
        $locl = array( );
        $good = false;
        if ($row)
        {
            $auth = $env['auth'];
            $name = $row['name'];
            $type = $row['ntype'];
            $glob = $row['global'];
            $enab = $row['enabled'];
            $user = $row['username'];
            $locl = find_notify_name($name,0,$auth,$db);
            $mine = ($user == $auth);
            $cron = ($type != constScheduleClassic);
            $good = (($glob) && (!$mine) && (!$locl) && ($enab != 1));
        }
        if ($locl)
        {
            $lid  = $locl['id'];
            $self = $env['self'];
            $href = "$self?act=view&nid=$lid";
            $name = html_link($href,$name);
            $text = "You already own a notification named <b>$name</b>.";
            echo para($text);
        }
        if ($good)
        {
            $row['id']       = 0;
            $row['global']   = 0;
            $row['retries']  = 0;
            $row['enabled']  = ($cron)? 0 : 1;
            $row['created']  = $now;
            $row['next_run'] = 0;
            $row['this_run'] = 0;
            $row['last_run'] = $now;
            $row['modified'] = $now;
            $row['username'] = $auth;
            $xid = update_notify($row,$db);
        }
        if (($xid) && ($cron))
        {
            $good = copy_shed($nid,$xid,$db);
            if ($good)
            {
                $row['id'] = $xid;
                $row['enabled'] = 1;
                update_notify($row,$db);
            }
            else
            {
                kill_notify($xid,$db);
                kill_schedule($xid,$db);
                $xid = 0;
            }
        }
        if (($xid) && ($good))
        {
            $row['id'] = $xid;
            $text = "Enabled Local Report <b>$name</b> has been created.";
            echo para($text);
            notify_detail_table($env,$xid,$db);
        }
        else
        {
            echo para('Nothing has changed.');
        }
        echo again($env);
    }


    function over_disb(&$env,$db)
    {
        debug_note('over_disb()');
        echo again($env);
        $xid  = 0;
        $cron = 0;
        $now  = $env['now'];
        $nid  = $env['nid'];
        $row  = find_notify($nid,$db);
        $locl = array( );
        $good = false;
        if ($row)
        {
            $auth = $env['auth'];
            $name = $row['name'];
            $type = $row['ntype'];
            $glob = $row['global'];
            $enab = $row['enabled'];
            $user = $row['username'];
            $locl = find_notify_name($name,0,$auth,$db);
            $mine = ($user == $auth);
            $cron = ($type != constScheduleClassic);
            $good = (($glob) && (!$mine) && (!$locl) && ($enab == 1));
        }
        if ($locl)
        {
            $lid  = $locl['id'];
            $self = $env['self'];
            $href = "$self?act=view&nid=$lid";
            $name = html_link($href,$name);
            $text = "You already own a notification named <b>$name</b>.";
            echo para($text);
        }
        if ($good)
        {
            $row['id']       = 0;
            $row['global']   = 0;
            $row['retries']  = 0;
            $row['enabled']  = 0;
            $row['created']  = $now;
            $row['next_run'] = 0;
            $row['this_run'] = 0;
            $row['last_run'] = 0;
            $row['modified'] = $now;
            $row['username'] = $auth;
            $xid = update_notify($row,$db);
        }
        if (($xid) && ($cron))
        {
            $row['id'] = $xid;
            $good = copy_shed($nid,$xid,$db);
            if (!$good)
            {
                kill_notify($xid,$db);
                kill_schedule($xid,$db);
            }
        }
        if (($xid) && ($good))
        {
            $name = $row['name'];
            $text = "Disabled Local Report <b>$name</b> has been created.";
            echo para($text);
            notify_detail_table($env,$xid,$db);
        }
        else
        {
            echo para('Nothing has changed.');
        }
        echo again($env);
    }


    function enable_act(&$env,$db)
    {
        echo again($env);
        $nid  = $env['nid'];
        $row  = find_notify($nid,$db);
        $num  = 0;
        $good = false;
        if ($row)
        {
            $auth = $env['auth'];
            $admn = $env['user']['priv_admin'];
            $auth = $env['auth'];
            $name = $row['name'];
            $user = $row['username'];
            $mine = ($user == $auth);
            if (($mine) || ($admn))
            {
                $enab = $row['enabled'];
                $good = ($enab != 1);
            }
        }
        if ($good)
        {
            $now = time();
            $sql = "update Notifications set\n"
                 . " enabled = 1,\n"
                 . " retries = 0,\n"
                 . " this_run = 0,\n"
                 . " next_run = 0,\n"
                 . " last_run = $now,\n"
                 . " modified = $now\n"
                 . " where id = $nid";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }
        if ($num)
        {
            $text = "Notification <b>$name</b> has been enabled.";
            echo para($text);
        }
        else
        {
            echo para('No change ...');
        }
        if ($good)
        {
            notify_detail_table($env,$nid,$db);
        }
        echo again($env);
    }


   /*
    |  check the username clause, just in case.
    */

    function delete_act(&$env,$db)
    {
        echo again($env);
        $nid = $env['nid'];
        $row = find_notify($nid,$db);
        if ($row)
        {
            $auth = $env['auth'];
            $admn = $env['user']['priv_admin'];
            $name = $row['name'];
            $sql = "delete from Notifications\n"
                 . " where id = $nid";
            if (!$admn)
            {
                $qu  = safe_addslashes($auth);
                $sql = "$sql\n and username = '$qu'";
            }
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
            if ($num > 0)
             {
                debug_note("$num notifications removed");
                kill_associates($nid, $db);
                $text = "Notification <b>$name</b> has been deleted.";
                echo para($text);
             }
        }
        echo again($env);
    }


    function list_notify(&$env,$db)
    {
        $p = 'p style="font-size:8pt"';
        echo <<< ZORT

        <$p>
          Click on the <i>manage</i> link below to perform
          management actions (e.g. edit) on multiple
          notifications.
        </p>

        <$p>
          Clicking on the <i>control</i> and <i>table</i> links will
          take you to the beginning of the <i>Search Options</i> panel,
          and the notification list, respectively.
        </p>
ZORT;

        $num = find_notify_count($env,$db);
        $sql = gen_query($env,0,$num);
        $set = find_many($sql,$db);
        echo mark('control');
        echo again($env);
        notify_control($env,$num,$db);
        echo mark('table');
        echo again($env);
        if ($set)
        {
            $tmp = safe_count($set);
            debug_note("There were $tmp records loaded.");
            notify_table($env,$set,$num,$db);
        }
        else
        {
            $text = 'There were no matching notifications ...';
            echo para($text);
        }
        echo again($env);
    }





    function select_multiple($name,$size,$options,$selected,$pix)
    {
        reset($selected);
        $keys = array ( );
        foreach ($selected as $key => $data)
        {
             $keys[$data] = 1;
        }
        $st = '';
        if ($pix)
        {
            $st = " style=\"font-size:xx-small; width:${pix}px\"";
        }
        $msg = "<select name=\"$name\" multiple size=\"$size\"$st>\n";
        reset($options);
        foreach ($options as $key => $data)
        {
            $selected = @ $keys[$data];
            if ($selected)
                $msg .= "<option selected>$data</option>\n";
            else
                $msg .= "<option>$data</option>\n";
        }
        $msg .= "</select>\n";
        return $msg;
    }


    function machine_list($auth,$db)
    {
        $qu  = safe_addslashes($auth);
        $sql = "select distinct C.host from\n"
             . " ".$GLOBALS['PREFIX']."core.Census as C,\n"
             . " ".$GLOBALS['PREFIX']."core.Customers as U\n"
             . " where C.site = U.customer\n"
             . " and U.username = '$qu'\n"
             . " order by host";
        $set = find_many($sql,$db);
        $out = array();
        reset($set);
        foreach ($set as $key => $row)
        {
            $out[] = $row['host'];
        }
        return $out;
    }


   /*
    |  We only need the mgroupid and the
    |  the machine group name, so that is
    |  what we return. This will be used for
    |  the pulldown menu.
   */
    function single_machine_group_list($set)
    {
        $out = array();
        reset($set);
        foreach ($set as $index => $machine)
        {
            $c_mgrpid = $machine['mgroupid'];
            $c_name   = $machine['name'];
            $out[$c_mgrpid] = $c_name;
        }
        return $out;
    }

    function create_master_group_list($set1,$set2)
    {
        $out = array();
        reset($set1);
        reset($set2);
        foreach ($set1 as $index => $machine)
        {
            $out[$index] = $machine;
        }
        foreach ($set2 as $index => $machine)
        {
            $out[$index] = $machine;
        }
        return $out;
    }


   /*
    |  Attempts to create a list of "appropriate" groups.
    |
    |  It's not entirely clear what should be considered
    |  appropriate, but I think this will do in the
    |  meantime.
    |
    |  We want to restrict to human group so as not
    |  to interfere with purging.
    */

    function group_list($auth,$db)
    {
        $gids = array();
        if ($auth)
        {
            $qa  = safe_addslashes($auth);
            $sql = "select G.*, C.category from\n"
                 . " ".$GLOBALS['PREFIX']."core.Census as H,\n"
                 . " ".$GLOBALS['PREFIX']."core.Customers as U,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineCategories as C,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroups as G,\n"
                 . " ".$GLOBALS['PREFIX']."core.MachineGroupMap as M\n"
                 . " where U.username = '$qa'\n"
                 . " and G.mcatuniq = C.mcatuniq\n"
                 . " and G.human = 1\n"
                 . " and U.customer = H.site\n"
                 . " and M.censusuniq = H.censusuniq\n"
                 . " and M.mgroupuniq = G.mgroupuniq\n"
                 . " group by G.mgroupid\n"
                 . " order by G.name";
            $set = find_many($sql,$db);
        }
        if ($set)
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $glob = $row['global'];
                $user = $row['username'];
                $mine = ($auth == $user);
                if (($glob) || ($mine))
                {
                    $gid = $row['mgroupid'];
                    $gids[$gid] = $row['name'];
                }
            }
        }
        return $gids;
    }

    function textmax($name,$size,$max,$valu)
    {
        $disp = str_replace('"','&quot;',$valu);
        $disp = str_replace("'",'&#039;',$disp);
        return "<input type=\"text\" name=\"$name\" size=\"$size\" maxlength=\"$max\" value=\"$disp\">";
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

    function default_schedule()
    {
        $out = array
        (
            constAnyMonth => 1,
            constAnyMDay  => 1,
            constAnyWDay  => 1,
            constAnyHour  => 1
        );
        add_entries($out,constPostMonth,1,12);
        add_entries($out,constPostWeek,1,5);
        add_entries($out,constPostWDay,0,6);
        add_entries($out,constPostMDay,1,31);
        add_entries($out,constPostHour,0,23);
        add_entries($out,constPostMinute,0,60);
        $out['proximity'] = 600;
        $out['maxfail']   = 5;
        $out['invalid']   = 0;
        $out['umin']      = 0;
        $out['umax']      = 0;
        return $out;
    }

    function build_tag($name,$data)
    {
        if ($data < 0)
            $tag = $name . '_xx';
        else
            $tag = sprintf('%s_%02d',$name,$data);
        return $tag;
    }


    function add_special(&$out,$type,$data)
    {
        switch ($type)
        {
            case constCronMinute:
                $tag = build_tag(constPostMinute,$data);
                $out[$tag] = 1;
                break;
            case constCronHour:
                $tag = build_tag(constPostHour,$data);
                $out[$tag] = 1;
                break;
            case constCronMDay:
                $tag = build_tag(constPostMDay,$data);
                $out[$tag] = 1;
                break;
            case constCronWDay:
                $tag = build_tag(constPostWDay,$data);
                $out[$tag] = 1;
                break;
            case constCronWeek:
                $tag = build_tag(constPostWeek,$data);
                $out[$tag] = 1;
                break;
            case constCronMonth:
                $tag = build_tag(constPostMonth,$data);
                $out[$tag] = 1;
                break;
            case constCronUMin:
                $out['umin'] = $data;
                break;
            case constCronUMax:
                $out['umax'] = $data;
                break;
            case constCronProx:
                $out['proximity'] = $data;
                break;
            case constCronFail:
                $out['maxfail'] = $data;
                break;
            default:
                $out['invalid'] = 1;
                break;
        }
    }


    function load_shed($nid,$db)
    {
        $sql = "select * from NotifySchedule\n"
             . " where nid = $nid";
        return find_many($sql,$db);
    }

    function real_schedule($nid,$db)
    {
        $out = default_schedule();
        $out[constAnyMonth] = 0;
        $out[constAnyWDay]  = 0;
        $out[constAnyMDay]  = 0;
        $out[constAnyHour]  = 0;
        $set = load_shed($nid,$db);
        foreach ($set as $key => $row)
        {
            $type = $row['type'];
            $data = $row['data'];
            add_special($out,$type,$data);
        }
        return $out;
    }

    function load_schedule($nid,$db)
    {
        $out = real_schedule($nid,$db);
        if ($out['invalid'])
        {
            $out = default_schedule();
        }
        return $out;
    }

    function default_cfg()
    {
        return':machine:customer:username:description:';
    }

    function default_notify()
    {
        $cfg = default_cfg();
        return array
        (
            'id'               => 0,
            'global'           => 0,
            'ntype'            => 0,
            'priority'         => 3,
            'name'             => '',
            'username'         => '',
            'days'             => 14,    // two weeks
            'solo'             => 0,
            'console'          => 1,
            'email'            => 0,
            'emaillist'        => '',
            'defmail'          => 1,
            'search_id'        => 0,
            'seconds'          => 3600,  // 1 hour
            'threshold'        => 1,
            'last_run'         => 0,
            'next_run'         => 0,
            'this_run'         => 0,
            'suspend'          => 0,
            'retries'          => 0,
            'group_include'    => '',
            'group_exclude'    => '',
            'group_suspend'    => '',
            'config'           => $cfg,
            'enabled'          => 1,
            'links'            => 1,
            'created'          => 0,
            'modified'         => 0,
            'skip_owner'       => 0,
            'email_footer'     => 0,
            'email_per_site'   => 0,
            'email_footer_txt' => default_email_footer(),
            'email_sender'     => 0,
            'autotask'         => 0
        );
    }


    function notify_ints()
    {
        return array('global','ntype','priority','days',
                     'solo','console','email','defmail',
                     'search_id','seconds','suspend',
                     'group_include','group_exclude','group_suspend',
                     'g_suspend','g_exclude','g_include',
                     'enabled','links','skip_owner','email_footer',
                     'email_per_site','email_sender');
    }



    function save_oneshot($nid,$db)
    {
        $good = false;
        $umin = get_integer('umin',0);
        $umax = get_integer('umax',0);
        add_invalid($nid,$db);
        if (($umin) && ($umax) && ($umin < $umax))
        {
            kill_valid($nid,$db);
            $dmin = nanotime($umin);
            $dmax = nanotime($umax);
            debug_note("one shot schedule: $dmin ($umin) to $dmax ($umax)");
            if (add_schedule($nid,constCronUMin,$umin,$db))
            {
                if (add_schedule($nid,constCronUMax,$umax,$db))
                {
                    $ntfy = find_notify($nid,$db);
                    if ($ntfy)
                    {
                        $ntfy['last_run'] = $umin;
                        $ntfy['next_run'] = $umax;
                        $ntfy['this_run'] = 0;
                        if (update_notify($ntfy,$db))
                        {
                            $good = kill_invalid($nid,$db);
                        }
                    }
                }
            }
        }
        return $good;
    }



   /*
    |  Returns the number of records inserted, 0 through N,
    |  or -1 in the event of error.
    */


    function insert_tags($nid,$cat,$name,$min,$max,$db)
    {
        $set  = tag_gen($name,$min,$max);
        $num  = 0;
        reset($set);
        foreach ($set as $key => $tag)
        {
            if (0 <= $num)
            {
                if (get_integer($tag,0))
                {
                    debug_note("insert tag $tag");
                    if (add_schedule($nid,$cat,$key,$db))
                    {
                        $num++;
                    }
                    else
                    {
                        $num = -1;
                    }
                }
            }
        }
        return $num;
    }



    function cron_cat($nid,$any,$cat,$name,$min,$max,$db)
    {
        $num = 0;
        if (get_integer($any,0))
        {
            debug_note("allow any $name");
            if (add_schedule($nid,$cat,constCronAny,$db))
                $num++;
            else
                $num = -1;
        }
        else
        {
            $num = insert_tags($nid,$cat,$name,$min,$max,$db);
        }
        return $num;
    }


   /*
    |  There must be at least one legal month.
    |
    |  There must be at least one legal day of month,
    |  specified either by week or by mday.
    |
    |  There must be at least one legal hour.
    |  If the checkboxes don't specify one,
    |  we assume any hour.
    |
    |  There must be at least one legal minute.
    |  If the checkboxes don't specify one, we'll
    |  assign one at random.
    */


    function save_crontab($nid,$db)
    {
        $cron = constCronMonth;
        $mnth = cron_cat($nid,constAnyMonth,$cron,constPostMonth,1,12,$db);
        if ($mnth == 0)
        {
            debug_note("allow any month anyway");
            $mnth = add_schedule($nid,$cron,constCronAny,$db);
        }
        $good = (1 <= $mnth);
        if ($good)
        {
            $cron = constCronWDay;
            $wday = cron_cat($nid,constAnyWDay, $cron, constPostWDay, 0, 6,$db);
            if ($wday == 0)
            {
                debug_note("allow any wday anyway");
                $wday = add_schedule($nid,$cron,constCronAny,$db);
            }
            $good = (1 <= $wday);
        }
        if ($good)
        {
            $cron = constCronMDay;
            $mday = cron_cat($nid,constAnyMDay, $cron, constPostMDay, 1,31,$db);
            $good = (0 <= $mday);
        }
        if ($good)
        {
            $cron = constCronWeek;
            $week = insert_tags($nid,$cron,constPostWeek,1,5,$db);
            $good = (0 <= $week);
        }
        if ($good)
        {
            $good = ($week + $mday > 0);
        }
        if ($good)
        {
            $cron = constCronHour;
            $hour = cron_cat($nid,constAnyHour,$cron,constPostHour,0,23,$db);
            if ($hour == 0)
            {
                $rand = mt_rand() % 24;
                debug_note("random hour $rand");
                $hour = add_schedule($nid,$cron,$rand,$db);
            }
            $good = (1 <= $hour);
        }
        if ($good)
        {
            $cron = constCronMinute;
            $mint = insert_tags($nid,$cron,constPostMinute,0,59,$db);
            if ($mint == 0)
            {
                $rand = mt_rand() % 60;
                debug_note("random minute $rand");
                $mint = add_schedule($nid,$cron,$rand,$db);
            }
            $good = (1 <= $mint);
        }
        if (!$good)
        {
            debug_note('error saving crontab schedule');
        }
        return $good;
    }


    function save_proximity($nid,$db)
    {
        $good = false;
        if (add_invalid($nid,$db))
        {
            kill_valid($nid,$db);
            $prox = get_integer('proximity',0);
            $fail = get_integer('maxfail',0);
            $good = (($prox) && ($fail));
        }
        if ($good)
        {
            debug_note("proximity: $prox");
            $good = false;
            if (add_schedule($nid,constCronProx,$prox,$db))
            {
                debug_note("maxfail: $fail");
                if (add_schedule($nid,constCronFail,$fail,$db))
                {
                    $good = save_crontab($nid,$db);
                }
            }
        }
        if ($good)
        {
            kill_invalid($nid,$db);
        }
        return $good;
    }


    function save_schedule($type,$nid,$db)
    {
        $good  = false;
        switch ($type)
        {
            case constScheduleClassic:
                kill_schedule($nid,$db);
                break;
            case constScheduleCrontab:
                $good = add_invalid($nid,$db);
                kill_valid($nid,$db);
                if ($good) $good = save_crontab($nid,$db);
                if ($good) kill_invalid($nid,$db);
                break;
            case constScheduleProximity:
                $good = save_proximity($nid,$db);
                break;
            case constScheduleOneShot:
                $good = save_oneshot($nid,$db);
                break;
            default:
                kill_schedule($nid,$db);
                add_invalid($nid,$db);
        }
        return $good;
    }


    function validate(&$env,&$ntfy,&$errs,&$good,$db)
    {
        debug_note("validate()");
        $auth = $env['auth'];
        if (!$ntfy)
        {
            $errs[] = 'The notification does not exist.';
            $good = false;
        }
        $name = trim(get_string('name',''));
        if ($good)
        {
            $old  = $ntfy['enabled'];
            $ints = notify_ints();
            reset($ints);
            foreach ($ints as $key => $fld)
            {
                $ntfy[$fld] = get_integer($fld,0);
            }
            $new = $ntfy['enabled'];
            $ntfy['name']      = $name;

            $ntfy['next_run']  = 0;
            $ntfy['this_run']  = 0;
            $ntfy['config']    = genconfig($db,true,$_POST);
            $ntfy['emaillist'] = get_string('emaillist','');

            // Autotask values
            $ntfy['email_footer']     = $env['email_footer'];
            $ntfy['email_per_site']   = $env['email_per_site'];
            $ntfy['email_footer_txt'] = $env['email_footer_txt'];
            $ntfy['email_sender']     = $env['email_sender'];

            if (($old != $new) && ($new))
            {
                $ntfy['last_run'] = $env['now'];
            }

            $ntfy['autotask'] = get_integer('autotask', 0);
        }
        if ($name == '')
        {
            $errs[] = 'You must specify a name for the notification.';
            $good = false;
        }
        if ($good)
        {
            $tmp = get_string('threshold','1');
            $tld = intval($tmp);
            if (($tmp == '0') || ($tld > 0))
            {
                $ntfy['threshold'] = $tld;
            }
            else
            {
                $errs[] = 'Invalid threshold value.';
                $good = false;
            }
        }
        if ($good)
        {
            $nid = $ntfy['id'];
            if (owned_notify_exists($name,$auth,$nid,$db))
            {
                $errs[] = "You already own a notification named <b>$name</b>.";
                $good = false;
            }
        }
        if ($ntfy['ntype'] == constScheduleClassic)
        {
            if ($ntfy['seconds'] < 180)
            {
                $ntfy['seconds'] = 180;
            }
        }
        else
        {
            $ntfy['seconds'] = 86400;
        }
        if ($good)
        {
            $glob = $ntfy['global'];
            $gprv = $env['user']['priv_notify'];
            $ntfy['global'] = ($gprv)? $glob : 0;
            if ($ntfy['global'])
            {
                $nid = $ntfy['id'];
                if (global_notify_exists($name,$nid,$db))
                {
                    $errs[] = "There is already a global notification named <b>$name</b>.";
                    $good = false;
                }
            }
            else
            {
                $ntfy['skip_owner'] = 0;
            }
        }
        if ($good)
        {
            $sid  = $ntfy['search_id'];
            $srch = find_search($sid,$db);
            if ($srch)
            {
                if (!$srch['global'])
                {
                    if ($ntfy['global'])
                    {
                        $errs[] = "Can't use local search with global notification.";
                        $good = false;
                    }
                    if ($srch['username'] != $auth)
                    {
                        $errs[] = "Search authorization fault";
                        $good = false;
                    }
                }
            }
            else
            {
                $errs[] = 'Saved search not found.';
                $good = false;
            }
        }
        if ($good)
        {
            $susp = get_string('susp','');
            if ($susp)
            {
                $now = time();
                $gid = get_argument('g_suspend',0,0);

                $suspend = parsedate($susp,$now);
                if (($suspend > $now) && ($gid))
                {
                    $when = shortdate($suspend);
                    debug_note("suspend until $when");
                    $ntfy['suspend']  = $suspend;
                }
                if (($susp) && ($suspend == 0))
                {
                    $errs[] = "Don't understand suspend date '$susp'";
                    $good = false;
                }
            }
            /* group include, exclude & suspend */
            $ntfy['ginclude'] = GRPS_get_multiselect_values('g_include');
            $ntfy['gexclude'] = GRPS_get_multiselect_values('g_exclude');
            $ntfy['gsuspend'] = GRPS_get_multiselect_values('g_suspend');
        }
    }


    function add_exec(&$env,$db)
    {
        debug_note('add_exec()');
        echo again($env);
        $errs = array();
        $good = false;
        $dbug = $env['dbug'];
        $nid  = 0;
        $ntfy = default_notify();
        if ($ntfy)
        {
            $ntfy['id']       = 0;
            $ntfy['created']  = $env['now'];
            $ntfy['modified'] = $env['now'];
            $ntfy['last_run'] = $env['now'];
            $ntfy['username'] = $env['auth'];
            $good = true;
        }
        validate($env,$ntfy,$errs,$good,$db);
        if ($good)
        {
            $name = $ntfy['name'];
            $nid  = update_notify($ntfy,$db);
            if ($nid)
            {
                $text = "Notification <b>$name</b> has been created.";
                echo para($text);
                $settings = AUTO_ValidateSettings($ntfy['username'], false,
                    $db);
                if((!$settings) && ($ntfy['autotask']))
                {
                    echo constAutotaskIncomplete;
                    return;
                }
                $ntfy['id'] = $nid;
                $type = $ntfy['ntype'];
                $good = save_schedule($type,$nid,$db);
                if ($good)
                {
                    $time = time();
                    $next = cron_next($ntfy,$time,$db);
                    if ($next)
                    {
                        $date = nanotime($time);
                        $text = nanotime($next);
                        debug_note("now:$date next:$text");
                    }
                    else
                    {
                        $text = 'However, the schedule is invalid.';
                        debug_note("this is an invalid schedule");
                        add_invalid($nid,$db);
                        $ntfy['enabled'] = 0;
                        update_notify($ntfy,$db);
                        echo para($text);
                    }
                }
                notify_detail_table($env,$nid,$db);
            }
            else
            {
                $errs[] = 'Could not create new notification.';
            }
        }

        if ($errs)
        {
            $txt = join("<br>\n",$errs);
            echo para($txt);
        }
        echo again($env);
    }



    function upd_exec(&$env,$db)
    {
        debug_note('upd_exec()');
        echo again($env);
        $good = false;
        $disp = false;
        $errs = array();
        $nid  = $env['nid'];
        $auth = $env['auth'];
        $dbug = $env['dbug'];
        $ntfy = find_notify($nid,$db);
        if ($ntfy)
        {
            $user = $ntfy['username'];
            if ($user == $auth)
            {
                $good = true;
                $disp = true;
            }
            else
            {
                $errs[] = 'You do not own this notification.';
            }
        }
        else
        {
            $errs[] = 'The notification does not exist.';
        }

        validate($env,$ntfy,$errs,$good,$db);
        if ($good)
        {
            $num = update_notify($ntfy,$db);
            if ($num)
            {
                $errs[] = 'Notification Updated';
                touch_notify($nid,$db);
                $settings = AUTO_ValidateSettings($ntfy['username'], false,
                    $db);
                if((!$settings) && ($ntfy['autotask']))
                {
                    $errs[] = constAutotaskIncomplete;
                }
            }
            else
            {
                $errs[] = 'Notification Unchanged';
            }
            $type = $ntfy['ntype'];
            $good = save_schedule($type,$nid,$db);
            if ($good)
            {
                $time = time();
                $next = cron_next($ntfy,$time,$db);
                if ($next)
                {
                    $date = nanotime($time);
                    $text = nanotime($next);
                    debug_note("now:$date next:$text");
                }
                else
                {
                    debug_note("this is an invalid schedule");
                    add_invalid($nid,$db);
                    $ntfy['id'] = $nid;
                    $ntfy['enabled'] = 0;
                    update_notify($ntfy,$db);
                    echo para('The schedule is invalid.');
                }
            }
        }
        if ($errs)
        {
            $txt = join('<br>',$errs);
            echo para($txt);
        }
        if ($disp)
        {
            notify_detail_table($env,$nid,$db);
        }
        echo again($env);
    }

    function tag_int($name,$min,$max,$def)
    {
        $valu = get_integer($name,$def);
        return value_range($min,$max,$valu);
    }

    function command_list(&$act,&$txt)
    {
        echo "<p>What do you want to do?</p>\n\n\n<ol>\n";

        reset($txt);
        foreach ($txt as $key => $doc)
        {
            $cmd = html_link($act[$key],$doc);
            echo "<li>$cmd</li>\n";
        }
        echo "</ol>\n";
    }

    function debug_menu(&$env,$db)
    {
        echo again($env);
        $self = $env['self'];
        $cmd  = "$self?act";
        $dbg  = "$self?debug=1&act";

        $act = array( );
        $txt = array( );

        $act[] = $self;
        $txt[] = 'Basic Notification Page';

        $act[] = "$cmd=menu";
        $txt[] = 'Debug Menu';

        $act[] = "$dbg=next&l=50&o=24";
        $txt[] = 'Upcoming';

        $act[] = "$dbg=last&l=50&o=20";
        $txt[] = 'Recent Past';

        $act[] = "$cmd=push&min=0&inc=1";
        $txt[] = 'Postpone 0 minutes';

        $act[] = "$cmd=push&min=5&inc=1";
        $txt[] = 'Postpone 5 minutes';

        $act[] = "$cmd=push&min=10&inc=10";
        $txt[] = 'Postpone 10 Minutes';

        $act[] = "$cmd=push&min=60&inc=10";
        $txt[] = 'Postpone 60 Minutes';

        $act[] = "$cmd=push&min=240&inc=300";
        $txt[] = 'Postpone Four Hours';

        $act[] = "$dbg=sane";
        $txt[] = 'Database Consistancy Check';

        $act[] = "$cmd=stat";
        $txt[] = 'Statistics';

        $act[] = "$dbg=rset";
        $txt[] = 'Reset Notification Queue Only';

        $act[] = "$dbg=skip";
        $txt[] = 'Skip Pending Notifications Forward' .
                 ' (after server has been down a long time,' .
                 ' this makes notifications scheduled to run in' .
                 ' the past have a normal short time span)';

        $act[] = "$dbg=fix";
        $txt[] = 'Fix All Invalid';

        $act[] = "$dbg=queu";
        $txt[] = 'Queue Control';

        $act[] = "$dbg=lock";
        $txt[] = 'Claim Lock';

        $act[] = "$dbg=pick";
        $txt[] = 'Release Lock';

        $act[] = 'report.php?act=queu';
        $txt[] = 'Report Queue';

        $act[] = '../acct/index.php';
        $txt[] = 'Debug Home';

        command_list($act,$txt);
        echo again($env);
    }

    function showtime($now,$then)
    {
        if ($then <  0) return 'running';
        if ($then == 0) return 'never';
        if ($then <= $now)
        {
            $when = nanotime($then);
            $age  = age($now - $then);
            $text = "$when (age $age)";
        }
        else
        {
            $when = nanotime($then);
            $wait = age($then - $now);
            $text = "$when (wait $wait)";
        }
        return $text;
    }


    function mini_join($set)
    {
        return ($set)? join(', ',$set) : 'No';
    }


    function speak_shed($type,&$shed)
    {
        $text = 'Invalid';
        if ($shed['invalid'])
        {
            return $text;
        }

        if ($type == constScheduleOneShot)
        {
            $umin = $shed['umin'];
            $umax = $shed['umax'];
            if (($umin) && ($umax))
            {
                $dmin = timestamp($umin);
                $dmax = timestamp($umax);
                $text = "Start: <b>$dmin</b><br>\n"
                      . "Stop: <b>$dmax</b>";

            }
            return $text;
        }

        if ($shed[constAnyMonth])
        {
            $mnth = 'Any';
        }
        else
        {
            $out = array( );
            $tmp = month_array();
            $set = tag_gen(constPostMonth,1,12);
            reset($set);
            foreach ($set as $mm => $tag)
            {
                if ($shed[$tag])
                {
                    $out[] = $tmp[$mm];
                }
            }
            $mnth = mini_join($out);
        }


        if ($shed[constAnyMDay])
        {
            $mday = 'Any';
        }
        else
        {
            $out = array( );
            $set = tag_gen(constPostMDay,1,31);
            reset($set);
            foreach ($set as $dd => $tag)
            {
                if ($shed[$tag])
                {
                    $out[] = $dd;
                }
            }
            $mday = mini_join($out);
        }

        if ($shed[constAnyWDay])
        {
            $wday = 'Any';
        }
        else
        {
            $out = array( );
            $tmp = week_array();
            $set = tag_gen(constPostWDay,0,6);
            reset($set);
            foreach ($set as $ww => $tag)
            {
                if ($shed[$tag])
                {
                    $out[] = $tmp[$ww];
                }
            }
            $wday = mini_join($out);
        }


        if ($shed[constAnyHour])
        {
            $hour = 'Any';
        }
        else
        {
            $out = array( );
            $tmp = hour_array();
            $set = tag_gen(constPostHour,0,23);
            reset($set);
            foreach ($set as $hh => $tag)
            {
                if ($shed[$tag])
                {
                    $out[] = trim($tmp[$hh]);
                }
            }
            $hour = mini_join($out);
        }


        $out = array( );
        $set = tag_gen(constPostWeek,1,5);
        reset($set);
        foreach ($set as $ww => $tag)
        {
            if ($shed[$tag])
            {
                $out[] = $ww;
            }
        }
        $week = mini_join($out);

        $out  = array( );
        $set  = tag_gen(constPostMinute,0,59);
        reset($set);
        foreach ($set as $mm => $tag)
        {
            if ($shed[$tag])
            {
                $out[] = sprintf('%02d',$mm);
            }
        }
        $mint = mini_join($out);

        $text = '';
        if ($type == constScheduleProximity)
        {
            $prox = $shed['proximity'];
            $fail = $shed['maxfail'];
            if (($prox) && ($fail))
            {
                $prox = speak_freq($prox);
                $text = "<br>\n"
                      . "Proximity: <b>$prox</b><br>\n"
                      . "Max failures: <b>$fail</b><br>\n";

            }

        }

        return "Month of year: <b>$mnth</b><br>\n"
           .   "Day of month: <b>$mday</b><br>\n"
           .   "Day of week: <b>$wday</b><br>\n"
           .   "Week of month: <b>$week</b><br>\n"
           .   "Hour of day: <b>$hour</b><br>\n"
           .   "Minute of hour: <b>$mint</b>$text";
    }


    function notify_links(&$env,&$row,$db)
    {
        $self = $env['self'];
        $auth = $env['auth'];
        $priv = $env['priv'];

        $nid  = $row['id'];
        $name = $row['name'];
        $glob = $row['global'];
        $user = $row['username'];
        $mine = ($auth == $user);
        $enab = ($row['enabled'] == 1);

        $ax   = array( );
        $cmd  = "$self?nid=$nid&act";
        if (($mine) || ($glob) || ($priv))
        {
            $ax[] = html_link("$cmd=view",'details');
            $ax[] = html_link("$cmd=copy",'copy');
        }
        if ($mine)
        {
            if ($enab)
                $ax[] = html_link("$cmd=disb",'disable');
            else
                $ax[] = html_link("$cmd=enab",'enable');
            $ax[] = html_link("$cmd=edit",'edit');
            $ax[] = html_link("$cmd=cdel",'delete');
        }
        if (($glob) && (!$mine))
        {
            $locl = find_notify_name($name,0,$auth,$db);
            if (!$locl)
            {
                $ax[] = html_link("$cmd=over",'edit');
                if ($enab)
                    $ax[] = html_link("$cmd=dovr",'disable');
                else
                    $ax[] = html_link("$cmd=eovr",'enable');
            }
        }
        if (($priv) && (!$mine))
        {
            $ax[] = html_link("$cmd=cdel",'p.delete');
            if ($enab)
                $ax[] = html_link("$cmd=disb",'p.disable');
            else
                $ax[] = html_link("$cmd=enab",'p.enable');
        }
        return $ax;
    }


    function notify_detail_table(&$env,$nid,$db)
    {
        $auth = $env['auth'];
        $self = $env['self'];
        $priv = $env['priv'];
        $admn = $env['user']['priv_admin'];
        $defs = $env['user']['notify_mail'];
        $defs = ($defs)? "<i>$defs</i>" : '';
        $good = false;

        $row  = find_notify($nid,$db);
        if ($row)
        {
            $user = $row['username'];
            $glob = $row['global'];
            $mine = ($user == $auth);
            $good = (($mine) || ($glob) || ($admn));
        }
        if ($good)
        {
            $now  = time();
            $nid  = $row['id'];
            $days = $row['days'];
            $name = $row['name'];
            $mail = $row['email'];
            $type = $row['ntype'];
            $when = $row['suspend'];
            $secs = $row['seconds'];
            $enab = $row['enabled'];
            $cons = $row['console'];
            $sid  = $row['search_id'];
            $skip = $row['skip_owner'];
            $emfo = $row['email_footer'];
            $emps = $row['email_per_site'];
            $emft = $row['email_footer_txt'];
            $emsn = $row['email_sender'];

            if ($cons)
            {
                $time = ($days)? "$days days" : 'indefinite';
                $cons = "Yes, $time";
            }
            else
            {
                $cons = 'No';
            }

            $solo = ($row['solo'])? 'Yes' : 'No';
            $cfg  = explode(':',$row['config']);
            $cfg  = remove_empty($cfg);
            $scop = ($glob)? 'Global' : 'Local';
            $enab = enabled($row['enabled']);
            $srch = find_search($sid,$db);

            $igrp = $row['group_include'];
            $egrp = $row['group_exclude'];
            $sgrp = $row['group_suspend'];

            $igrp = find_mgrp_gid($igrp, constReturnGroupTypeMany, $db);
            $egrp = find_mgrp_gid($egrp, constReturnGroupTypeMany, $db);
            $sgrp = find_mgrp_gid($sgrp, constReturnGroupTypeMany, $db);

            if ($type == constScheduleClassic)
            {
                $freq = speak_freq($secs);
            }
            else
            {
                $shed = real_schedule($nid,$db);
                $freq = speak_shed($type,$shed);
            }

            $ax = notify_links($env,$row,$db);
            echo jumplist($ax);

            echo table_header();
            echo pretty_header($name,2);
            echo double('Owner',$row['username']);
            echo double('Scope',$scop);
            if ($glob)
            {
                $skip_t = ($skip)? 'Yes' : 'No';
                echo double('Skip Owner',$skip_t);
            }
            echo double('State',$enab);
            echo double('Type',notify_type($type));
            echo double('Console',$cons);

            $emfo = ($emfo)? 'Yes' : 'No';
            $emps = ($emps)? 'Yes' : 'No';
            $emsn = ($emsn)? 'Yes' : 'No';
            $emft = prep_for_display($emft);

            echo double('Include Footer',$emfo);
            echo double('E-mail Per-Site',$emps);
            echo double('Use Site E-mail',$emsn);
            echo double('Footer Text',   $emft);

            if ($srch)
            {
                $sid  = $srch['id'];
                $name = $srch['name'];
                $ssql = $srch['searchstring'];
                $href = "search.php?act=view&sid=$sid";
                $link = html_link($href,$name);
                echo double('Search',$link);
                echo double('Query',$ssql);
            }

            $itxt = ($igrp)? GRPS_edit_group_detail($igrp) : 'All groups included';
            $etxt = ($egrp)? GRPS_edit_group_detail($egrp) : 'Nothing excluded';

            echo double(   'Schedule',$freq);
            echo double(   'Priority',$row['priority']);
            echo double(  'Threshold',$row['threshold']);
            echo double( 'Restricted',$solo);
            echo double(    'Include',$itxt);
            echo double(    'Exclude',$etxt);

            if (($sgrp) && ($now < $when))
            {
                echo double('Suspend',         GRPS_edit_group_detail($sgrp));
                echo double(  'Suspend Until', shortdate($when));
            }

            if ($mail)
            {
                $lnk  = $row['links'];
                $dst  = $row['emaillist'];
                $defm = $row['defmail'];
                if (($glob) || ($mine))
                {
                    if (($defm) && ($defs))
                    {
                        $dst = ($dst)? "$defs,$dst" : $defs;
                    }
                }
                if ($dst)
                {
                    echo double('EMail',str_replace(',','<br>',$dst));
                }
                $lnk = ($lnk)? 'Yes' : 'No';
                echo double('Links',$lnk);
            }
            else
            {
                echo double('E-mail','No');
            }
            echo double(   'Config',join('<br>',$cfg));
            if ($row['retries'])
            {
                echo double('Retries',$row['retries']);
            }
            echo double(  'Created',showtime($now,$row['created']));
            echo double( 'Modified',showtime($now,$row['modified']));
            echo double( 'Last Run',showtime($now,$row['last_run']));
            echo double( 'Next Run',showtime($now,$row['next_run']));
            if ($priv)
            {
                if ($row['this_run'])
                {
                    echo dgreen('This Run',showtime($now,$row['this_run']));
                }
                echo dgreen('Record:',$nid);
                echo dgreen('Now:',datestring($now));
            }
            echo table_footer();
            echo jumplist($ax);
        }
        else
        {
            if ($row)
            {
                $txt = 'No access to this notification.';
            }
            else
            {
                $txt = "Notificaton <b>$nid</b> does not exist.";
            }
            echo para($txt);
        }
    }



    function notify_detail(&$env,$db)
    {
        echo again($env);
        $nid  = $env['nid'];
        notify_detail_table($env,$nid,$db);
        echo again($env);
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


    function any_row($name,$span,$txt,$val)
    {
        $tag = $name . '_xx';
        $box = checkbox($tag,$val);
        $row = "<td colspan=\"$span\">$box $txt</td>";
        return "<tr>$row</tr>\n";
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
        $cols = 8;
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
            $txt = $box . "Week $ww";
            $temp[$row][7] = $txt;
            $out[] = table_data($temp[$row],0);
        }
        $out[] = "</table>\n";
        $t2 = join("\n",$out);
        return array($t1,$t2);
    }



    /*
    |  $footer = email_footer_txt value
    |
    |  Encode before displaying to the
    |  user or the footer will be incorrect.
    |
    |  In the case where a report was created
    |  before the email_footer_txt field,
    |  we must present a default footer.
    */
    function prep_for_display($footer)
    {
        if ($footer == '')
        {
            $footer = default_email_footer();
        }
        $footer = htmlentities($footer, ENT_QUOTES);
        $footer = str_replace("\n",'<br>',$footer);
        return $footer;
    }


    /*
    |  $footer = the current value of 'email_footer_txt'.
    |
    |  The default database value for this field is '',
    |  so if we are editing a report created before the
    |  email_footer_txt field was added it will be blank.
    |  In that case we set it the default_email_footer()
    |  string.
    */
    function set_email_footer($footer)
    {
        if ($footer == '')
        {
            return default_email_footer();
        }
        else
        {
            return $footer;
        }
    }

    function notify_form(&$env,$row,$shed,$head,$db)
    {
        debug_note("notify_form()");
        $n_def    = preserve_notification_state($env['nid'], $env['act']);
        $auth     = $env['auth'];
        $priv     = $env['priv'];
        $midn     = $env['midn'];

        // Group include, exclude & suspend.
        // If its a new report, set include to be 'All'.
        $curr_ginc = ($row['group_include'])? $row['group_include'] : constMachineGroupDefaultALL;
        $curr_gexc = $row['group_exclude'];
        $curr_gsus = $row['group_suspend'];

        $gprv     = ($env['user']['priv_notify'])? 1 : 0;
        $opt_srch = filter_options($auth,$db);
        if ($opt_srch)
            $sel_srch = html_select('search_id',$opt_srch,$row['search_id'],1);
        else
            $sel_srch = 'No filters available.';
        $yesno    = array('No','Yes');
        $defmail  = html_select('defmail',$yesno,$row['defmail'],1);
        $opt_secs = frequency_options();
        $opt_prio = range(1,5);
        $opt_prox = prox_options();
        $opt_fail = range(1,10);
        $opt_days = days_options();
        $opt_time = time_options($midn);
        $sel_secs = html_select('seconds',$opt_secs,$row['seconds'],1);
        $sel_solo = html_select('solo',$yesno,$row['solo'],1);
        $sel_prio = html_select('priority',$opt_prio,$row['priority'],0);
        $sel_days = html_select('days',$opt_days,$row['days'],1);
        $sel_mail = html_select('email',$yesno,$row['email'],1);
        $sel_cons = html_select('console',$yesno,$row['console'],1);
        $sel_link = html_select('links',$yesno,$row['links'],1);
        $sel_enab = html_select('enabled',$yesno,$row['enabled'],1);
        $sel_prox = html_select('proximity',$opt_prox,$shed['proximity'],1);
        $sel_fail = html_select('maxfail',$opt_fail,$shed['maxfail'],0);
        $inp_name = textmax('name',50,50,$row['name']);
        $inp_mail = textbox('emaillist',30,$row['emaillist']);
        $inp_thld = textmax('threshold',3,3,$row['threshold']);

        $n0 = radio('ntype',constScheduleClassic,$row['ntype']);
        $n2 = radio('ntype',constScheduleCrontab,$row['ntype']);
        $n3 = radio('ntype',constScheduleProximity,$row['ntype']);
        $n4 = radio('ntype',constScheduleOneShot,$row['ntype']);

        $mm = checkbox(constAnyMonth,$shed[constAnyMonth]);
        $dd = checkbox(constAnyMDay,$shed[constAnyMDay]);
        $ww = checkbox(constAnyWDay,$shed[constAnyWDay]);
        $hh = checkbox(constAnyHour,$shed[constAnyHour]);

        $email_footer     = checkbox(constEmailFooter,   $row['email_footer']);
        $email_per_site   = checkbox(constEmailPerSite,  $row['email_per_site']);
        $email_sender     = checkbox(constEmailSender,   $row['email_sender']);
        $email_footer_txt = textareabox(constEmailFooterTxt,300,6,$row['email_footer_txt']);

        $umin     = $midn;
        $umax     = days_ago($umin,-1);
        $sel_umin = html_select('umin',$opt_time,$umin,1);
        $sel_umax = html_select('umax',$opt_time,$umax,1);
        $xn       = indent(4);

        $custom_URL = customURL(constPageEntryNotfy);
        $admin_link = html_link('../acct/admin.php#edit','tools:admin');
        $asi_link   = html_link('../acct/server.php?custom=ticket_user,ticket_password#ticket_user',
                                'ASI server configuration page');
        $group_link = html_link("../config/groups.php?$custom_URL&$n_def", '[configure groups]');

        if (!$gprv)
            $grow = '';
        else
        {
            $gbox = checkbox('global',$row['global']);
            $sbox = checkbox('skip_owner',$row['skip_owner']);
            $grow = <<< GLOB

            <tr>
              <td>
                Global:
              </td>
              <td>
                $gbox
              </td>
              <td colspan="2">
                <i>

                  Can this notification be seen and used by everyone?&nbsp;
                  Only privileged accounts can create or edit global
                  notifications.

                </i>
              </td>
            </tr>
            <tr>
              <td>
                Do not generate for owner:
              </td>
              <td>
                $sbox
              </td>
              <td colspan="2">
                <i>

                This option will generate a notification for everyone except the owner of the notification.
                <br>
                (Notification must be global)

                </i>
              </td>
            </tr>
GLOB;

        }

        $please_note = GRPS_please_note();
        $inc_ins     = GRPS_include_instructions();
        $exc_ins     = GRPS_exclude_instructions(constEventNotifications);

        $nid  = $row['id'];
        $user = $row['username'];
        $grps = array();

        $grps = build_group_list($auth, constQueryNoRestrict, $db);
        $mstr = prep_for_multiple_select($grps);

        $sel_suspend = saved_search($mstr, $curr_gsus, 7, 'g_suspend[]', constMachineGroupMessage);
        $sel_include = saved_search($mstr, $curr_ginc, 7, 'g_include[]', constMachineGroupMessage);
        $sel_exclude = saved_search($mstr, $curr_gexc, 7, 'g_exclude[]', constMachineGroupMessage);

        $autotask = '<script type="text/javascript" language="JavaScript">'
            . 'function autotaskClick()'
            . '{'
            .   'if(myform.autotask.checked)'
            .   '{'
            .   '   myform.email_per_site.checked=true;'
            .   '   myform.email_per_site.disabled=true;'
            .   '}'
            .   'else'
            .   '{'
            .   '   myform.email_per_site.disabled=false;'
            .   '}'
            . '}    window.setTimeout(\'autotaskClick()\',0);</script>'
            . checkbox_onclick('autotask',$row['autotask'],
            'autotaskClick();');

        $srow = '';
        $now  = time();
        $when = $row['suspend'];
        if (($row['id']) || ($when > $now))
        {
            $inp_suspend = format_suspend($when, $now);

            $srow = <<< SROW

            <tr>
              <td>
                Suspend:
              </td>
              <td colspan="3">
                <i>

                This parameter causes a notification to ignore events
                from one or more machines for a limited period of time.&nbsp;
                Suspension ends when the deadline specified in the "until"
                parameter, below, passes.&nbsp; The deadline should be later
                than the current time, and is expected to be in the format
                mm/dd/yy, mm/dd/yyyy, or mm/dd hh:mm.&nbsp; Deadlines fall
                at midnight unless otherwise specified.
                <br><br>
                Note -- you must specify a machine group and a future
                deadline in order for the suspension to have any effect.
                </i>
                <br>
                <br>
                $group_link
                $please_note
                <br>
                <br>
                $sel_suspend until $inp_suspend
              </td>
            </tr>
SROW;

        }

        $schds = draw_schedule($env,$shed,$db);
        $schd0 = $schds[0];
        $schd1 = $schds[1];

        $config = $row['config'];
        $fnames = event_fields($db);
        $cfgs = explode(':',$config);
        $defs = array( );
        foreach ($cfgs as $key => $data)
        {
            if ($data != '')
            {
                $defs[$data] = 1;
            }
        }
        $gen = genboxes($fnames,$defs);
        $genboxes = <<< XXXX

        <table cellpadding="3" cellspacing="0" bordercolor="COCOCO" border="1">
        $gen
        </table>
XXXX;

        $again  = again($env);
        $mark   = mark('schedule');
        if ($nid)
        {
            $act = 'updt';
            $sub = button('Update');
        }
        else
        {
            $act = 'insn';
            $sub = button('Add');
        }
        $rst    = '<input type="reset" value="reset">';
        $submit = para("${xn}${sub}${xn}${rst}");

        echo post_self('myform');
        echo hidden('act',$act);
        echo hidden('nid',$nid);
        if ($priv)
        {
            echo hidden('debug','1');
        }

        echo <<< ZZZZ

    $submit

    <table border="2" align="left" cellspacing="2" cellpadding="2" width="100%">
    <tr>
      <th colspan="4" bgcolor="#333399">
        <font color="white">
          $head
        </font>
      </th>
    </tr>

    <tr>
      <td>
        Name:
      </td>
      <td colspan="2">
        $inp_name
      </td>
      <td>
        <i>

          Enter the name of this notification.&nbsp;
          The name can be up to 50 characters
          long and must not already have been used.

        </i>
      </td>
    </tr>


    <tr>
      <td>
        Saved Search:
      </td>
      <td colspan="2">
        $sel_srch
      </td>
      <td>
        <i>

        This is the saved search used to retrieve the
        events you want to be notified about.&nbsp;
        If the saved search you would like
        to run has not yet been created, you can
        <a href="srch-add.php">add one</a>.

        </i>
      </td>
    </tr>
    <tr>
      <td nowrap>
        E-mail Recipients:
      </td>
      <td colspan="2">
        $inp_mail
      </td>
      <td>
        <i>

        This should be a comma separated list of email
        addresses.&nbsp; If the notification is
        not supposed to send records of events retrieved
        via email (see E-mail option below), then an email
        message will be sent only in the event of an error.

        </i>

      </td>
    </tr>

    <tr>
      <td>
        Default<br>E-mail Recipients:
      </td>
      <td>
        $defmail
      </td>
      <td colspan="2">

        <i>

        Add default email list members to the list of e-mail
        recipients.

        </i>

      </td>
    </tr>

    <tr>
      <td>
        Threshold:
      </td>
      <td>
        $inp_thld
      </td>
      <td>
        <i>

        This is the minimum number of event log records retrieved by the saved
        search from one or more machines, within the time interval specified by
        the Frequency parameter, needed to trigger a notification.

       </i>
      </td>
      <td>
        <i>

        Defaults to 1 if not specified.
        <br><br>
        A threshold of 0 will trigger a notification only if no event
        logs are retrieved.

        </i>
      </td>
    </tr>
    <tr>
      <td>
        Restricted:
      </td>
      <td>
        $sel_solo
      </td>
      <td colspan="2">
        <i>

        Enabling this option will trigger a notification whenever
        the number of events specified in the threshold parameter
        above, occurs on <b>one</b> machine.&nbsp; If set to zero,
        the notification will be triggered only if at least on one
        system no event is retrieved. &nbsp; If set to one, the
        notification will be no different from a non-restricted
        notification.


        </i>
      </td>
    </tr>

    <tr>
      <td>
        Enabled:
      </td>
      <td>
        $sel_enab
      </td>
      <td colspan="2">
        <i>

            Disabled notifications are ignored and never run.&nbsp; If you
            don't need a notification any more, but think you might want
            to use it again some day, you can just disable it, instead of
            deleting it.

         </i>
      </td>
    </tr>

    <tr>
      <td>
        Priority:
      </td>
      <td>
        $sel_prio
      </td>
      <td colspan="2">
        <i>

        Priorities range from 1, highest, to 5, lowest.&nbsp; By default,
        event log records on the notification console are sorted by priority.

        </i>
      </td>
    </tr>
    <tr>
      <td>
        Expires in:
      </td>
      <td>
        $sel_days
      </td>
      <td colspan="2">
        <i>

        This controls how long event log records retrieved by
        the notification should be displayed on the notification
        console.&nbsp; Notification records are automatically
        purged when they expire.&nbsp; Please note that notification
        records displayed on the console will lose their detail
        information if their associated log database event(s) are
        removed from the log database (e.g. when the log
        database is rolled over.)

        </i>
      </td>
    </tr>
    <tr>
      <td>
        Autotask:
      </td>
      <td>
        $autotask
      </td>
      <td colspan="2">
        <i>Note that the Autotask system limits notification content to 4000
        characters.  This means that notifications reporting many events, or a
        lot of detail information will be truncated to that size.</i>
      </td>
    </tr>
    <tr>
      <td>
        E-mail:
      </td>
      <td>
        $sel_mail
      </td>
      <td colspan="2">
        <i>

        This controls whether or not notification record(s) should be
        sent via email.&nbsp; This option should be used with care to avoid flooding
        a recipient with email messages.&nbsp; It can be useful to highlight
        critical events, or if you need a permanent record of the event(s).

        </i>

      </td>
    </tr>

    <tr>
      <td>
        Per-site e-mail notifications:
      </td>
      <td>
        $email_per_site
      </td>
      <td colspan="2">
        <i>
        Enabling this option will generate an e-mail notification message for
        each site event logs have been retrieved from.
        </i>
      </td>
    </tr>

    <tr>
      <td>
        Use site e-mail address as sender address:
      </td>
      <td>
        $email_sender
      </td>
      <td colspan="2">
        <i>
        This option is used in conjunction with the "Per site e-mail
        notifications" option above. The default site e-mail address
        configured for each site in its record on the admin module
        (click on $admin_link, then click on the edit link for the
        site(s) where you want to add a site e-mail address) will be
        used as the e-mail notification sender address.
        </i>
      </td>
    </tr>

    <tr>
      <td>
        Add e-mail footer:
      </td>
      <td>
        $email_footer
      </td>
      <td colspan="2">
        <i>
          When this option is enabled, the e-mail footer entered in "E-mail footer"
          is appended to the end of e-mail notifications.
        </i>
      </td>
    </tr>

    <tr>
      <td>
        E-mail footer:
      </td>
      <td>
        $email_footer_txt
      </td>
      <td colspan="2">
        <i>
          This text will be appended to the end of an e-mail notification when you
          enable the "Add e-mail footer" option. Note that the "Per-site e-mail notifications:"
          parameter needs to be enabled. The %site% variable corresponds to the site name as shown
          in the sites section of the admin page. The %name% variable corresponds to the
          notification name as entered on this page. The %ticketuser% and %ticketpassword%
          variables take on the values entered for the ticket_user and ticket_password entered
          in the $asi_link.
        </i>
      </td>
    </tr>

    <tr>
      <td>
        Console:
      </td>
      <td>
        $sel_cons
      </td>
      <td colspan="2">
        <i>

        This controls whether or not notification records should be posted on
        the notification console.&nbsp;  Please note that you can enable both the E-mail
        and Console options.

        </i>
      </td>
    </tr>
    <tr>
      <td>
        Links:
      </td>
      <td>
        $sel_link
      </td>
      <td colspan="2">
        <i>

        This option controls whether or not notifications sent via e-mail should
include_once links to event log details.&nbsp; Please note that it is applicable
        only to notifications sent via e-mail.

        </i>
      </td>
    </tr>
    <tr>
      <td>
        Include:
      </td>
      <td>
        $sel_include
        <br>
        <br>
        $group_link
        <br>
        <br>
        $please_note
      </td>
      <td colspan="2">
        <i>
          $inc_ins
        </i>
      </td>
    </tr>

    <tr>
      <td>
        Exclude:
      </td>
      <td>
        $sel_exclude
        <br>
        <br>
        $group_link
        <br>
        <br>
        $please_note
      </td>
      <td colspan="2">
        <i>
          $exc_ins
        </i>
      </td>
    </tr>

    $srow

    $grow

    <tr>
      <td>
        Details:
      </td>
      <td colspan="3">
        <i>

        Select the content of the notification's detail section.

        </i>

        $genboxes

      </td>
    </tr>

    </table>

    <br clear="all">
    <br>

    $mark

    $again


    <table border="2" align="left" cellspacing="2" cellpadding="2" width="100%">
    <tr>
      <th bgcolor="#333399">
        <font color="white">
          Schedule
        </font>
      </th>
    </tr>

    <tr>
      <td>

        <b>Notification Type:</b><br>
        ${xn}$n0 Periodic, run every $sel_secs<br>
        ${xn}$n2 Run at scheduled time or as soon as possible after its
                 scheduled time (this could be much later depending on
                 ASI server load)<br>
        ${xn}$n3 Run at scheduled time or no later than $sel_prox after
                 scheduled time.  Notify me if it fails to run more
                 than $sel_fail times.<br>
        ${xn}$n4 Run once covering period from $sel_umin to $sel_umax

        <br><br>
        <b>Schedule:</b><br>
        ${xn}$mm Any Month of Year<br>
        ${xn}$dd Any Day Of Month<br>
        ${xn}$ww Any Day Of Week<br>
        ${xn}$hh Any Hour Of Day<br>

        <br clear="all">

        <table width="100%">
        <tr valign="top">
          <td align="left" width="60%">
            $schd0
          </td>
          <td align="right" width="40%">
            $schd1
          </td>
        </tr>
        </table>

      </td>
    </tr>
    </table>

    <br clear="all">

    $submit

ZZZZ;
        echo form_footer();
    }


    function edit_form(&$env,$db)
    {
        debug_note('edit_form()');
        echo again($env);
        $nid  = $env['nid'];
        $ntfy = find_notify($nid,$db);
        $good = false;
        if ($ntfy)
        {
            $auth = $env['auth'];
            $user = $ntfy['username'];
            $good = ($auth == $user);
        }
        if ($good)
        {
            $shed = load_schedule($nid,$db);
            $head = $ntfy['name'];
            $ntfy['email_footer_txt'] = set_email_footer($ntfy['email_footer_txt']);
            notify_form($env,$ntfy,$shed,$head,$db);
        }
        else
        {
            echo para('No access ...');
        }
        echo again($env);
    }


    function add_form(&$env,$db)
    {
        echo again($env);
        $ntfy = default_notify();
        if ($ntfy)
        {
            $shed = default_schedule();
            $auth = $env['auth'];
            $glob = $ntfy['global'];
            $gprv = $env['user']['priv_notify'];
            $ntfy['id']       = 0;
            $ntfy['global']   = ($gprv)? $glob : 0;
            $ntfy['username'] = $auth;
            $head = 'Add a Notification';
            notify_form($env,$ntfy,$shed,$head,$db);
        }
        echo again($env);
    }

    function copy_form(&$env,$db)
    {
        echo again($env);
        $nid  = $env['nid'];
        $ntfy = find_notify($nid,$db);
        $good = false;
        if ($ntfy)
        {
            $shed = load_schedule($nid,$db);
            $auth = $env['auth'];
            $aprv = $env['user']['priv_admin'];
            $glob = $ntfy['global'];
            $user = $ntfy['username'];
            $mine = ($user == $auth);
            if (($glob) || ($mine) || ($aprv))
            {
                $good = true;
            }
        }
        if ($good)
        {
            $gprv = $env['user']['priv_notify'];
            $name = $ntfy['name'];
            $ntfy['id']       = 0;
            $ntfy['name']     = "Copy of $name";
            $ntfy['global']   = ($gprv)? $glob : 0;
            $ntfy['username'] = $auth;
            $ntfy['email_footer_txt'] = set_email_footer($ntfy['email_footer_txt']);
            $head = 'Duplicate a Notification';
            notify_form($env,$ntfy,$shed,$head,$db);
        }
        else
        {
            echo para('No access ...');
        }
        echo again($env);
    }


    function find_scalar($sql,$db)
    {
        $val = '';
        $res = command($sql,$db);
        if ($res)
        {
            $val = mysqli_result($res, 0);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
        return $val;
    }


    function reset_table($tab,$when,$db)
    {
        $sql = "update $tab set\n"
             . " this_run = 0,\n"
             . " next_run = $when\n"
             . " where next_run < 0\n"
             . " and enabled = 1";
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
        $sql = "update $tab set\n"
             . " this_run = 0,\n"
             . " next_run = 0\n"
             . " where next_run < 0\n"
             . " or this_run > 0";
        $res = redcommand($sql,$db);
        $dis = affected($res,$db);
        $now = time();
        $big = $now + (366 * 86400);
        $sql = "update $tab set\n"
             . " next_run = 0\n"
             . " where enabled = 1\n"
             . " and next_run > $big";
        $res = redcommand($sql,$db);
        $foo = affected($res,$db);
        $sql = "update $tab set\n"
             . " next_run = 0,\n"
             . " last_run = $now\n"
             . " where last_run > $now\n"
             . " and enabled = 1";
        $res = redcommand($sql,$db);
        $xxx = affected($res,$db);
        return $num + $dis + $foo + $xxx;
    }


    function reset_notify(&$env,$db)
    {
        echo again($env);
        $when = time() - 300;
        $date = timestamp($when);
        $age  = age(300);
        echo para("Reset queue for $date, $age.");
        $num  = reset_table('Notifications',$when,$db);
        if ($num)
        {
            $txt = "Cleared $num pending pending notifications.";
            echo para($txt);
        }
        update_opt('notify_pid',0,$db);
        update_opt('notify_lock',0,$db);
        $env['limt'] = 50;
        $env['ord']  = 24;
        schedule_next($env,$db);
    }


    function skip_notify(&$env,$db)
    {
        echo again($env);
        $now = $env['now'];
        $sql = "update Notifications set\n"
             . " next_run = $now + seconds,\n"
             . " last_run = $now\n"
             . " where enabled = 1\n"
             . " and next_run > 0\n"
             . " and next_run < $now\n"
             . " and this_run = 0";
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
        echo para("$num notifications moved forward.");
        $env['limt'] = 50;
        $env['ord']  = 24;
        schedule_next($env,$db);
    }



    function statistics($env,$db)
    {
        echo again($env);
        $now  = time();
        $num  = 'select count(*) from Notifications';
        $find = "$num\n where";
        $enab = "$find enabled = 1";
        $disb = "$find enabled = 0";
        $invl = "$find enabled = 2";
        $glob = "$find global = 1";
        $locl = "$find global = 0";
        $pend = "$find enabled = 1 and next_run < $now";
        $runn = "$find enabled = 1 and next_run < 0";

        $next = "select * from Notifications\n"
              . " where enabled = 1\n"
              . " order by next_run\n"
              . " limit 1";
        $row  = find_one($next,$db);

        echo table_header();
        echo pretty_header('Notification Statistics',2);
        echo double('Total:',   find_scalar($num,$db));
        echo double('Enabled:', find_scalar($enab,$db));
        echo double('Disabled:',find_scalar($disb,$db));
        echo double('Invalid:', find_scalar($invl,$db));
        echo double('Global:',  find_scalar($glob,$db));
        echo double('Local:',   find_scalar($locl,$db));
        echo double('Pending:', find_scalar($pend,$db));
        echo double('Running:', find_scalar($runn,$db));
        if ($row)
        {
            $name = $row['name'];
            $user = $row['username'];
            $glob = $row['global'];
            $next = $row['next_run'];
            $stat = ($glob)? 'g' : 'l';
            $when = '<br>';
            if ($next <  0) $when = 'running now';
            if ($next == 0) $when = 'frozen';
            if ((0 < $next) && ($next <= $now))
            {
                $date = timestamp($next);
                $secs = age($now - $next);
                $when = "pending ($secs) $date";
            }
            if ($now < $next)
            {
                $date = date('m/d/y H:i:s',$next);
                $secs = age($next - $now);
                $when = "future ($secs) $date";
            }
            echo double('Next scheduled:',$name);
            echo double('Owned by',"$user($stat)");
            echo double('When:',$when);
        }
        echo table_footer();
        echo again($env);
    }


    function draw_table(&$env,&$set,$text)
    {
        $now  = $env['now'];
        $ord  = $env['ord'];
        $self = $env['self'];
        $act  = $env['act'];
        $num  = safe_count($set);
        if ($num > 0)
        {
            $o    = "$self?act=$act&o";
            $name = ($ord ==  0)? "$o=1"  : "$o=0";   // name     0/1
            $ctim = ($ord ==  2)? "$o=3"  : "$o=2";   // create   2/3
            $mtim = ($ord ==  4)? "$o=5"  : "$o=4";   // modify   4/5
            $user = ($ord ==  8)? "$o=9"  : "$o=8";   // user     8/9
            $secs = ($ord == 12)? "$o=13" : "$o=12";  // secs     12/13
            $prio = ($ord == 14)? "$o=15" : "$o=14";  // prio     14/15
            $last = ($ord == 20)? "$o=21" : "$o=20";  // last     20/21
            $ntid = ($ord == 22)? "$o=23" : "$o=22";  // ntid     22/23
            $next = ($ord == 24)? "$o=25" : "$o=24";  // next     24/25

            $name = html_link($name,'Name');
            $user = html_link($user,'Owner');
            $prio = html_link($prio,'Priority');
            $ntid = html_link($ntid,'Id');
            $ctim = html_link($ctim,'Create');
            $mtim = html_link($mtim,'Modify');
            $last = html_link($last,'Last');
            $wait = html_link($next,'Wait');
            $next = html_link($next,'Next');
            $secs = html_link($secs,'Freq');
            $act  = 'Action';

            $head = array($wait,$name,$user,$ntid,$next,$last,$ctim,$mtim,$prio,$secs,$act);
            $cols = safe_count($head);

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $nid  = $row['id'];
                $name = $row['name'];
                $user = $row['username'];
                $glob = $row['global'];
                $prio = $row['priority'];
                $enab = $row['enabled'];
                $last = $row['last_run'];
                $next = $row['next_run'];
                $that = $row['this_run'];
                $freq = $row['seconds'];
                $sid  = $row['search_id'];
                $ctim = $row['created'];
                $mtim = $row['modified'];

                $wait  = '<br>';
                if ($enab == 1)
                {
                    $code  = '';
                    $color = prio_color($prio);
                    if ($next > 0)
                    {
                        if ($next > $now)
                        {
                            $secs = $next - $now;
                            $wait = age($secs);
                        }
                        else
                        {
                            $secs = $now - $next;
                            $late = age($secs);
                            $wait = page_bold($late);
                        }
                    }
                    else if ($next < 0)
                    {
                        if ($that > 0)
                        {
                            $name = page_bold($name);
                            $wait = '(run)';
                        }
                    }
                }
                else
                {
                    if ($enab)
                    {
                        $code  = '(i)';
                        $color = 'grey';
                    }
                    else
                    {
                        $code  = '(d)';
                        $color = 'white';
                    }
                    if ((0 < $last) && ($last < $now))
                    {
                        $secs = $now - $last;
                        $wait = age($secs);
                    }
                }

                $last = nanotime($last);
                $next = nanotime($next);
                $mod  = nanotime($mtim);
                $crt  = nanotime($ctim);
                $scop = ($glob)? 'g' : 'l';

                $a   = array( );
                $act = "$self?nid=$nid&act";
                $a[] = html_link("$act=cdel",'delete');
                $a[] = html_link("$act=view",'detail');
                if ($enab != 1)
                {
                    $a[] = html_link("$act=enab",'enable');
                }
                if ($row['next_run'] < 0)
                {
                    $a[] = html_link("$act=stck",'fix');
                }

                $acts = join(' ',$a);
                $ownr = "$user($scop)$code";
                $secs = speak_freq($freq);
                $args = array($wait,$name,$ownr,$nid,$next,$last,$crt,$mod,$prio,$secs,$acts);
                echo color_data($args,$color,0);
            }
            echo table_footer();
        }
    }


    function schedule_past(&$env,$db)
    {
        echo again($env);
        $limit = $env['limt'];
        $order = order($env['ord']);
        $sql = "select * from Notifications\n"
             . " where last_run > 0\n"
             . " order by $order\n"
             . " limit $limit";
        $set = find_many($sql,$db);
        if ($set)
        {
            $txt = 'Recent Notifications';
            draw_table($env,$set,$txt);
        }
        echo again($env);
    }


    function schedule_next(&$env,$db)
    {
        echo again($env);
        $limit = $env['limt'];
        $order = order($env['ord']);
        $sql = "select * from Notifications\n"
             . " where enabled = 1\n"
             . " order by $order\n"
             . " limit $limit";
        $set = find_many($sql,$db);
        if ($set)
        {
            $txt = 'Enabled Notifications';
            draw_table($env,$set,$txt);
        }
        echo again($env);
    }


    function queue_table(&$env,$db)
    {
        $now = $env['now'];
        $sql = "select * from Notifications\n"
             . " where enabled = 1\n"
             . " order by next_run, global, seconds, id\n"
             . " limit 30";
        $set = find_many($sql,$db);
        if ($set)
        {
            $self = $env['self'];
            $jump = $env['jump'];
            $head = explode('|','Wait|Name|Owner|Id|Next|Last|Frequency|Span|Action');
            $cols = safe_count($head);
            $rows = safe_count($set);
            $text = datestring($now);
            $cmd  = "$self?act=view&sid";

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $nid  = $row['id'];
                $name = $row['name'];
                $type = $row['ntype'];
                $glob = $row['global'];
                $secs = $row['seconds'];
                $user = $row['username'];
                $prio = $row['priority'];
                $last = $row['last_run'];
                $next = $row['next_run'];
                $that = $row['this_run'];
                $text = prio_color($prio);
                $wait = '<br>';
                $span = '<br>';

                if ($now <= $next)
                {
                    $time = $next - $now;
                    $wait = age($time);
                }
                if ((0 < $next) && ($next < $now))
                {
                    $time = $now - $next;
                    $late = age($time);
                    $wait = page_bold($late);
                }
                if (($next < 0) && (0 < $that) && ($that <= $now))
                {
                    $wait = age($now - $that);
                    $wait = "<b>run ($wait)</b>";
                }
                if (($next < 0) && ($that <= 0))
                {
                    $wait = page_bold('run');
                }
                if ((0 < $last) && ($last < $next))
                {
                    $span = age($next - $last);
                }

                $tlst = nanotime($last);
                $tnxt = nanotime($next);
                $scop = ($glob)? 'g' : 'l';
                $ownr = "$user($scop)";

                $cmd  = "$self?nid=$nid&act";
                $ax   = array( );
                if (0 < $next)
                {
                    $ax[] = html_jump("$cmd=post",$jump,'post');
                }
                if ((0 < $last) && ($last+$secs < $now))
                {
                    $ax[] = html_jump("$cmd=frst",$jump,'first');
                    $ax[] = html_jump("$cmd=time",$jump,'now');
                }
                $acts = ($ax)? join(' ',$ax) : '<br>';
                $norm = ($type == constScheduleClassic);
                $freq = ($norm)? speak_freq($secs) : notify_type($type);
                $link = html_link("$cmd=view",$name);
                $args = array($wait,$link,$ownr,$nid,$tnxt,$tlst,$freq,$span,$acts);
                echo color_data($args,$text,0);
            }
            echo table_footer();
        }
    }


   /*
    |  Note that the order of notifications
    |  should be exactly the same as used by
    |  notify cron.
    */

    function queue_manage(&$env,$db)
    {
        echo mark('table');
        echo again($env);
        queue_table($env,$db);
        echo again($env);
    }


   /*
    |  Move a pending notification to now.
    |  Really that just means setting next_run to be the
    |  current time.  We want to be somewhat careful since
    |  the cron job may have grabbed it in the meantime.
    */

    function queue_time(&$env,$db)
    {
        echo mark('table');
        echo again($env);
        $nid = $env['nid'];
        $now = $env['now'];
        $row = find_notify($nid,$db);
        $msg = 'No change';
        if ($row)
        {
            $sql = "update Notifications set\n"
                 . " next_run = $now\n"
                 . " where id = $nid\n"
                 . " and next_run > 0\n"
                 . " and enabled = 1";
            $res = redcommand($sql,$db);
            if (affected($res,$db))
            {
                $name = $row['name'];
                $otim = $row['next_run'];
                $ntim = $now;
                $otxt = nanotime($otim);
                $ntxt = nanotime($ntim);
                if ($ntim > $otim)
                {
                    $secs = age($ntim - $otim);
                    $what = 'postponed';
                }
                else
                {
                    $secs = age($otim - $ntim);
                    $what = 'advanced';
                }
                $msg  = "Notification <b>$name</b> $what by <b>$secs</b>,"
                      . " from <b>$otxt</b> to <b>$ntxt</b>.";
            }
        }
        echo para($msg);
        queue_table($env,$db);
        echo again($env);
    }



   /*
    |   Postpone this notification.  The idea is to set
    |   next run to be one second later than the next
    |   in line ... keeping in mind that the next in line
    |   might be scheduled for same time we are.
    |
    |   In any event, we should end up postponing this
    |   one by at least a second.  This also allows us
    |   to break up a clump where several are scheduled
    |   to run at the same time.
    */

    function queue_post(&$env,$db)
    {
        echo mark('table');
        echo again($env);
        $new = array( );
        $nid = $env['nid'];
        $old = find_notify($nid,$db);
        $msg = 'No change';
        if ($old)
        {
            $nxt = $old['next_run']-1;
            if ($nxt > 28)
            {
                $sql = "select * from Notifications\n"
                     . " where enabled = 1\n"
                     . " and next_run > $nxt\n"
                     . " and id != $nid\n"
                     . " order by next_run\n"
                     . " limit 1";
                $new = find_one($sql,$db);
            }
        }
        if ($new)
        {
            $nxt = $new['next_run'] + 1;
            $sql = "update Notifications set\n"
                 . " next_run = $nxt\n"
                 . " where id = $nid\n"
                 . " and next_run > 0\n"
                 . " and next_run < $nxt\n"
                 . " and enabled = 1";
            $res = redcommand($sql,$db);
            if (affected($res,$db))
            {
                $name = $old['name'];
                $otim = $old['next_run'];
                $secs = age($nxt - $otim);
                $otxt = nanotime($otim);
                $ntxt = nanotime($nxt);
                $msg  = "Notification <b>$name</b> postponed by <b>$secs</b>,"
                      . " from <b>$otxt</b> to <b>$ntxt</b>.";
            }
        }
        echo para($msg);
        queue_table($env,$db);
        echo again($env);
    }


   /*
    |   Move a pending notification to the
    |   start of the queue ... we just set
    |   our run time to one second before
    |   the current first in line.
    |
    |   This should have no effect if we
    |   are already first in line.
    */

    function queue_first(&$env,$db)
    {
        echo mark('table');
        echo again($env);
        $new = array( );
        $nid = $env['nid'];
        $now = $env['now'];
        $old = find_notify($nid,$db);
        $msg = 'No change';
        if ($old)
        {
            $nxt = $old['next_run'];
            $sql = "select * from Notifications\n"
                 . " where enabled = 1\n"
                 . " and id != $nid\n"
                 . " and next_run > 28\n"
                 . " and next_run < $nxt\n"
                 . " order by next_run\n"
                 . " limit 1";
            $new = find_one($sql,$db);
        }
        if ($new)
        {
            $nxt = $new['next_run'] - 1;
            $sql = "update Notifications set\n"
                 . " next_run = $nxt\n"
                 . " where id = $nid\n"
                 . " and next_run > $nxt\n"
                 . " and enabled = 1";
            $res = redcommand($sql,$db);
            if (affected($res,$db))
            {
                $name = $old['name'];
                $otim = $old['next_run'];
                $ntim = $nxt;
                $secs = age($otim - $ntim);
                $otxt = nanotime($otim);
                $ntxt = nanotime($ntim);
                $msg  = "Notification <b>$name</b> advanced by <b>$secs</b>,"
                      . " from <b>$otxt</b> to <b>$ntxt</b>.";
            }
        }
        echo para($msg);
        queue_table($env,$db);
        echo again($env);
    }



    function queue_push(&$env,$db)
    {
        echo mark('table');
        echo again($env);
        $nnn = 0;
        $now = $env['now'];
        $min = tag_int('min',0,1440,10);
        $inc = tag_int('inc',0,3600,60);
        $out = array( );

        $secs = $min * 60;
        $ntim = $now + $secs;
        $find = "select * from Notifications\n"
              . " where next_run < $ntim\n"
              . " and next_run > 0\n"
              . " and enabled = 1\n"
              . " order by next_run, global, seconds, id";
        $set  = find_many($find,$db);

        if ($set)
        {
            reset($set);
            foreach ($set as $key => $row)
            {
                $nid  = $row['id'];
                $name = $row['name'];
                $user = $row['username'];
                $otim = $row['next_run'];
                $cmds = "update Notifications set\n"
                      . " next_run = $ntim\n"
                      . " where id = $nid\n"
                      . " and enabled = 1\n"
                      . " and next_run = $otim";
                $res  = redcommand($cmds,$db);
                if (affected($res,$db))
                {
                    $out[$nnn][0] = $name;
                    $out[$nnn][1] = $user;
                    $out[$nnn][2] = $nid;
                    $out[$nnn][3] = nanotime($otim);
                    $out[$nnn][4] = nanotime($ntim);
                    $out[$nnn][5] = age($ntim - $otim);
                    $ntim = $ntim + $inc;
                    $nnn++;
                }
            }
        }
        if ($out)
        {
            $rows = safe_count($out);
            $args = explode('|','Name|Owner|Id|Old|New|Change');
            $cols = safe_count($args);
            $head = "Notifications &nbsp ($rows postponed)";

            echo table_header();
            echo pretty_header($head,$cols);
            echo table_data($args,1);

            reset($out);
            foreach ($out as $nnn => $args)
            {
                echo table_data($args,0);
            }
            echo table_footer();
            echo again($env);
        }
        else
        {
            echo para('Nothing appropriate ...');
        }
        queue_table($env,$db);
        echo again($env);
    }



    function preserve(&$env,$txt)
    {
        $tags = explode(',',$txt);
        if ($tags)
        {
            reset($tags);
            foreach ($tags as $key => $tag)
            {
                echo hidden($tag,$env[$tag]);
            }
        }
    }


    function gang_table(&$env,&$set,$frm,$head)
    {
        $rows = safe_count($set);

        if ($rows <= 0)
        {
            return;
        }

        $cols = (12 <= $rows)? 4 : 1;
        if ($frm)
        {
            $post = $env['post'];
            $aflg = ($post == constButtonAll);
            $nflg = ($post == constButtonNone);
        }

        $out = array( );
        $tmp = array( );

        reset($set);
        foreach ($set as $nnn => $data)
        {
            $nam = $data['name'];
            if ($frm)
            {
                $nid = $data['id'];
                $tag = "nid_$nid";
                $chk = get_integer($tag,0);
                $chk = ($aflg)? 1 : $chk;
                $chk = ($nflg)? 0 : $chk;
                $box = checkbox($tag,$chk) . '&nbsp;';
                $txt = $box . $nam;
            }
            else
            {
                $txt = $nam;
            }
            $tmp[$nnn] = $txt;
        }

        if ($cols > 1)
        {
            $dec = $cols - 1;
            $max = intval(($rows+$dec) / $cols);
            for ($row = 0; $row < $max; $row++)
            {
                for ($col = 0; $col < $cols; $col++)
                {
                    $out[$row][$col] = '<br>';
                }
            }
        }
        else
        {
            $max = $rows;
            $col = 0;
        }

        reset($tmp);
        foreach ($tmp as $nnn => $txt)
        {
            if ($cols > 1)
            {
                $row = intval($nnn % $max);
                $col = intval($nnn / $max);
            }
            else
            {
                $row = $nnn;
            }
            $out[$row][$col] = $txt;
        }

        $text = "$head &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text,$cols);

        reset($out);
        foreach ($out as $key => $args)
        {
            echo table_data($args,0);
        }
        echo table_footer();
    }


    function gang_preserve(&$env,$act)
    {
        $self = $env['self'];
        $jump = $env['jump'];
        $form = $self . $jump;
        echo post_other('myform',$form);
        echo hidden('act',$act);
        echo hidden('p',$env['page']);
        echo hidden('o',$env['ord']);
        echo hidden('l',$env['limt']);
        $set = 'adv,crt,def,dst,dsp,enb,exp,flt,frq,'
             . 'gbl,lnk,lst,mal,mod,nxt,pnd,src,skp,'
             . 'pri,tld,rst,pat,txt,own';
        preserve($env,$set);
    }


    function genb_form(&$env,$db)
    {
        $num = find_notify_count($env,$db);
        $set = array( );
        if ($num)
        {
            $sql = gen_query($env,0,$num);
            $set = find_many($sql,$db);
        }

        echo mark('table');
        echo again($env);
        if ($set)
        {
            gang_preserve($env,'genb');
            echo hidden('debug','1');

            echo okcancel(5);

            $norm = 128;
            $wide = $norm*2 + 6;

            $enbs = enab_options();
            $dont = 'No Change';

            $x_enb = tag_int('x_enb',0,3,0);
            unset($enbs[-1]); $enbs[0] = $dont;

            $s_enb = tiny_select('x_enb', $enbs, $x_enb, 1, $norm);

            $head = table_header();
            $disp = pretty_header('Edit Options',1);
            $td   = 'td style="font-size: xx-small"';
            $xn   = indent(4);
            echo <<< XXXX

            $head

            $disp

            <tr><td>

              <table border="0" width="100%">
              <tr>
                <$td>State        <br>$s_enb   </td>
              </tr>
              </table>

            </td></tr>
            </table>
            <br clear="all">
            <br>
XXXX;

            echo checkallnone(5);
            $txt = 'Select Notifications';
            gang_table($env,$set,1,$txt);
            echo checkallnone(5);

            echo okcancel(5);
            echo form_footer();
        }

        echo again($env);
    }


   /*
    |  This is the main menu presented when
    |  editing multiple notifications.
    |
    |  Oct-24-05:  Added multiple select boxes
    |  for group include, exclude and suspend.
    |
   */
    function gang_form(&$env,$db)
    {
        $num = find_notify_count($env,$db);
        $set = array( );
        if ($num)
        {
            $sql = gen_query($env,0,$num);
            $set = find_many($sql,$db);
        }

        echo mark('table');
        echo again($env);
        if ($set)
        {
            $auth = $env['auth'];
            $self = $env['self'];
            $href = 'ntfy-em.htm';
            $open = "window.open('$href','help');";

            gang_preserve($env,'gang');
            echo hidden('debug',1);
            echo hidden('ctl',1);

            echo okcanhlp(5,$open);

            $norm = 128;
            $wide = $norm*2 + 6;
            $tlds = tlds_options(false);
            $enbs = enab_options();
            $popt = prio_options();
            $secs = secs_options();
            $rsts = rsts_options();
            $exps = expr_options();
            $gids = gids_options($auth,$db);
            $dont = 'No Change';

            $defs = array($dont,'No','Yes');
            $mail = array($dont,'Update');

            $empty = array();

            $x_inc = get_integer('x_inc',-1);
            $x_xcl = get_integer('x_xcl',-1);
            $x_sus = get_integer('x_sus',-1);

            $x_det = tag_int('x_det',0,1,0);
            $x_set = tag_int('x_set',0,1,0);
            $x_rst = tag_int('x_rst',0,2,0);
            $x_def = tag_int('x_def',0,2,0);
            $x_con = tag_int('x_con',0,2,0);
            $x_eml = tag_int('x_eml',0,2,0);
            $x_skp = tag_int('x_skp',0,2,0);
            $x_ftr = tag_int('x_ftr',0,2,0);
            $x_eps = tag_int('x_eps',0,2,0);
            $x_ems = tag_int('x_ems',0,2,0);
            $x_lnk = tag_int('x_lnk',0,2,0);
            $x_enb = tag_int('x_enb',0,2,0);
            $x_pri = tag_int('x_pri',0,5,0);
            $x_exp = tag_int('x_exp',0,28,0);
            $x_tld = tag_int('x_tld',0,30,0);
            $x_frq = tag_int('x_frq',0,7*86400,0);
//          $x_hst = get_argument('x_hst',0,$empty);
//          $x_mac = get_argument('x_mac',0,$empty);

            $x_mal = get_string('x_mal','');
            $x_tim = get_string('x_tim','');

            unset($enbs[3]);
            unset($enbs[-1]); $enbs[0] = $dont;
            unset($tlds[-1]); $tlds[0] = $dont;
            unset($secs[-1]); $secs[0] = $dont;
            unset($rsts[-1]); $rsts[0] = $dont;
            unset($exps[-1]); $exps[0] = $dont;
            unset($popt[-1]); $popt[0] = $dont;

            // group include, exclude & suspend.
            $grps = build_group_list($auth, constQueryNoRestrict, $db);
            $mstr = prep_for_multiple_select($grps);

            $sel_include = saved_search($mstr, constMachineGroupDefaultALL, 7,
                                        'x_g_include[]', constMachineGroupMessage);
            $sel_exclude = saved_search($mstr, 0, 7,
                                        'x_g_exclude[]', constMachineGroupMessage);
            $sel_suspend = saved_search($mstr, 0, 7,
                                        'x_g_suspend[]', constMachineGroupMessage);

            $s_pri = tiny_select('x_pri', $popt, $x_pri, 1, $norm);
            $s_frq = tiny_select('x_frq', $secs, $x_frq, 1, $norm);
            $s_enb = tiny_select('x_enb', $enbs, $x_enb, 1, $norm);
            $s_tld = tiny_select('x_tld', $tlds, $x_tld, 1, $norm);
            $s_rst = tiny_select('x_rst', $rsts, $x_rst, 1, $norm);
            $s_exp = tiny_select('x_exp', $exps, $x_exp, 1, $norm);
            $s_def = tiny_select('x_def', $defs, $x_def, 1, $norm);
            $s_det = tiny_select('x_det', $mail, $x_det, 1, $norm);
            $s_con = tiny_select('x_con', $defs, $x_con, 1, $norm);
            $s_eml = tiny_select('x_eml', $defs, $x_eml, 1, $norm);
            $s_lnk = tiny_select('x_lnk', $defs, $x_lnk, 1, $norm);
            $s_set = tiny_select('x_set', $mail, $x_set, 1, $norm);
//          $s_sus = tiny_select('x_sus', $mail, $x_sus, 1, $norm);
//          $s_xcl = tiny_select('x_xcl', $mail, $x_xcl, 1, $norm);
            $s_skp = tiny_select('x_skp', $defs, $x_skp, 1, $norm);
            $s_ftr = tiny_select('x_ftr', $defs, $x_ftr, 1, $norm);
            $s_eps = tiny_select('x_eps', $defs, $x_eps, 1, $norm);
            $s_ems = tiny_select('x_ems', $defs, $x_ems, 1, $norm);
            $s_mal = tinybox('x_mal',50,$x_mal,$wide);
            $s_tim = tinybox('x_tim',50,$x_tim,$norm);
//          $s_hst = select_multiple('x_hst[]',5,$list,$x_hst,$norm);
//          $s_mac = select_multiple('x_mac[]',5,$list,$x_mac,$norm);
            $head = table_header();

            $disp = pretty_header('Edit Options',1);
            $td   = 'td style="font-size: xx-small"';
            $ts   = $td . ' colspan="2"';
            $xn   = indent(4);

            if (get_integer('ctl',0))
                $cnfg = genconfig($db,true,$_POST);
            else
                $cnfg = default_cfg();
            $flds = event_fields($db);
            $cfgs = explode(':',$cnfg);
            $defs = array( );
            foreach ($cfgs as $key => $data)
            {
                if ($data != '')
                {
                    $defs[$data] = 1;
                }
            }
            $gbox = genboxes($flds,$defs);
            $r    = 'Recipients';
            $e    = "E-mail $r";

            echo <<< XXXX

            $head

            $disp

            <tr><td>

              <table border="0" width="100%">
              <tr>
                <$td>$e (update)  <br>\n$s_set</td>
                <$ts>$e (value)   <br>\n$s_mal</td>
                <$td>Default $r   <br>\n$s_def</td>
                <$td>E-Mail       <br>\n$s_eml</td>
              </tr>
              <tr>
                <$td>State        <br>\n$s_enb</td>
                <$td>Console      <br>\n$s_con</td>
                <$td>Frequency    <br>\n$s_frq</td>
                <$td>Priority     <br>\n$s_pri</td>
                <$td>Links        <br>\n$s_lnk</td>
              </tr>
              <tr>
                <$td>Threshold    <br>\n$s_tld</td>
                <$td>Restricted   <br>\n$s_rst</td>
                <$td>Expiration   <br>\n$s_exp</td>
                <$td>Skip Owner   <br>\n$s_skp</td>
                <$td>Details      <br>\n$s_det</td>
              </tr>
              <tr>
                <$td>Include       <br>\n$sel_include</td>
                <$td>Exclude       <br>\n$sel_exclude</td>
                <$td>Suspend       <br>\n$sel_suspend</td>
                <$td>
                  <table>
                  <tr>
                    <$td>
                      Suspend Until <br>\n$s_tim<br>
                      E-mail Footer <br>\n$s_ftr
                    </td>
                  </tr>
                  </table>
                </td>
                <$td>
                  E-mail Per Site <br>\n$s_eps<br>
                  Use Site E-mail <br>\n$s_ems
                </td>
              </tr>
              <tr>
                <td colspan="4">
                  &nbsp;
                </td>
              </tr>
              <tr>
                <td colspan="4" align="center">
                  <table>
                    $gbox
                  </table>
                </td>
              </tr>
              </table>

            </td></tr>
            </table>
            <br clear="all">
            <br>

XXXX;

            echo checkallnone(5);
            $txt = 'Select Notifications';
            gang_table($env,$set,1,$txt);
            echo checkallnone(5);

            echo okcanhlp(5,$open);
            echo form_footer();
        }

        echo again($env);
    }


    function gdel_form(&$env,$db)
    {
        $num = find_notify_count($env,$db);
        $set = array( );
        if ($num)
        {
            $sql = gen_query($env,0,$num);
            $set = find_many($sql,$db);
        }

        echo mark('table');
        echo again($env);
        if ($set)
        {
            gang_preserve($env,'gdel');
            echo hidden('debug',1);

            echo okcancel(5);
            echo checkallnone(5);

            $txt = 'Delete Notifications';
            gang_table($env,$set,1,$txt);

            echo okcancel(5);
            echo checkallnone(5);
            echo form_footer();
        }

        echo again($env);
    }


    function find_selected(&$set)
    {
        $ids = array( );
        reset($set);
        foreach ($set as $key => $data)
        {
            $nid = $data['id'];
            $tag = "nid_$nid";
            if (get_integer($tag,0))
            {
                $ids[] = $nid;
            }
        }
        return $ids;
    }



    function gdel_conf(&$env,$db)
    {
        $num = find_notify_count($env,$db);
        $set = array( );
        $ids = array( );
        if ($num)
        {
            $sql = gen_query($env,0,$num);
            $set = find_many($sql,$db);
        }

        echo mark('table');
        echo again($env);
        $ids = find_selected($set);
        if ($ids)
        {
            $txt = join(',',$ids);
            $sql = "select * from Notifications\n"
                 . " where id in ($txt)";
            if (!$env['user']['priv_admin'])
            {
                $qu  = safe_addslashes($env['auth']);
                $sql = "$sql\n and username = '$qu'";
            }
            $set = find_many($sql,$db);
        }

        $next = ($set)? 'gexp' : 'list';
        gang_preserve($env,$next);
        echo hidden('debug','1');

        if ($set)
        {
            reset($set);
            foreach ($set as $key => $data)
            {
                $nid = $data['id'];
                $tag = "nid_$nid";
                echo hidden($tag,'1');
            }
            $txt = 'Notifications to be deleted';
            gang_table($env,$set,0,$txt);
            $txt = 'Delete these notifications?';
            echo para($txt);
            echo okcancel(5);
        }
        else
        {
            $cont = button('Continue');
            echo para('You are not allowed to delete any of those notifications.');
            echo para($cont);
        }
        echo form_footer();
        echo again($env);
    }


    function gname($row,$key)
    {
        return @ trim($row[$key]);
    }


    function gang_exec(&$env,$db)
    {
        debug_note("gang_exec");
        $num = find_notify_count($env,$db);
        $set = array( );
        $trm = array( );
        $cnd = array( );
        $ors = array( );
        $arg = array( );
        if ($num)
        {
            $sql = gen_query($env,0,$num);
            $set = find_many($sql,$db);
            $num = 0;
        }

        $initial = safe_count($set);
        $ids = find_selected($set);

        echo mark('table');
        echo again($env);

        $now  = $env['now'];
        $auth = $env['auth'];

        gang_preserve($env,'list');

        echo button('Continue');

        if (!$ids)
        {
            $txt = 'No notifications selected ...';
            echo para($txt);
        }
        else
        {
            $xx    = gids_options($auth,$db);
            $empty = array();

            $ginclude_set = get_argument('x_g_include', 0, -1);
            $gexclude_set = get_argument('x_g_exclude', 0, -1);
            $gsuspend_set = get_argument('x_g_suspend', 0, -1);

            $ginclude_str = ($ginclude_set != -1)? join(',', $ginclude_set) : $ginclude_set;
            $gexclude_str = ($gexclude_set != -1)? join(',', $gexclude_set) : $gexclude_set;
            $gsuspend_str = ($gsuspend_set != -1)? join(',', $gsuspend_set) : $gsuspend_set;

            debug_note("ginclude_str = $ginclude_str | gexclude_str = $gexclude_str | gsuspend_str = $gsuspend_str");

            $x_set = tag_int('x_set',0,1,0);
            $x_det = tag_int('x_det',0,1,0);
            $x_rst = tag_int('x_rst',0,2,0);
            $x_def = tag_int('x_def',0,2,0);
            $x_con = tag_int('x_con',0,2,0);
            $x_eml = tag_int('x_eml',0,2,0);
            $x_lnk = tag_int('x_lnk',0,2,0);
            $x_enb = tag_int('x_enb',0,2,0);
            $x_skp = tag_int('x_skp',0,2,0);
            $x_ftr = tag_int('x_ftr',0,2,0);
            $x_eps = tag_int('x_eps',0,2,0);
            $x_ems = tag_int('x_ems',0,2,0);
            $x_pri = tag_int('x_pri',0,5,0);
            $x_exp = tag_int('x_exp',0,28,0);
            $x_tld = tag_int('x_tld',0,30,0);
            $x_frq = tag_int('x_frq',0,7*86400,0);
//          $x_hst = get_argument('x_hst',0,$empty);
//          $x_mac = get_argument('x_mac',0,$empty);
            $x_mal = get_string('x_mal','');
            $x_tim = get_string('x_tim','');

            $qu  = safe_addslashes($env['auth']);
            if ($x_pri)
            {
                $value = $x_pri;
                $trm[] = "priority = $value";
                $ors[] = "priority != $value";
                $arg[] = array('Priority',$value);
            }
            if ($x_set)
            {
                $value = $x_mal;
                $arg[] = array('E-mail Recipients',$value);
                $value = safe_addslashes($value);
                $trm[] = "emaillist = '$value'";
                $ors[] = "emaillist != '$value'";
            }
            if ($x_det)
            {
                $value = genconfig($db,true,$_POST);
                $cfg   = explode(':',$value);
                $cfg   = remove_empty($cfg);
                $cfg   = join('<br>',$cfg);
                $arg[] = array('Details',$cfg);
                $value = safe_addslashes($value);
                $trm[] = "config = '$value'";
                $ors[] = "config != '$value'";
            }
            if ($x_enb)
            {
                $value = $x_enb - 1;
                $trm[] = "enabled = $value";
                $ors[] = "enabled != $value";
                $trm[] = 'next_run = 0';
                if ($value)
                {
                    $trm[] = 'retries = 0';
                    $trm[] = "last_run = $now";
                }
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Enabled',$value);
            }
            if ($x_exp)
            {
                $value = $x_exp - 1;
                $trm[] = "days = $value";
                $ors[] = "days != $value";
                $value = show_days($value);
                $arg[] = array('Expire',$value);
            }
            if ($ginclude_str != -1)
            {
                $trm[] = "group_include  = '$ginclude_str'";
                $ors[] = "group_include != '$ginclude_str'";
                $igrp  = find_mgrp_gid($ginclude_str, constReturnGroupTypeMany, $db);
                $arg[] = array('Include', group_detail($igrp));
            }
            if ($gexclude_str != -1)
            {
                $trm[] = "group_exclude  = '$gexclude_str'";
                $ors[] = "group_exclude != '$gexclude_str'";
                $egrp  = find_mgrp_gid($gexclude_str, constReturnGroupTypeMany, $db);
                $arg[] = array('Exclude', group_detail($egrp));
            }
            if ($gsuspend_str)
            {
                $limit = parsedate($x_tim, $now);
                if ($limit > $now)
                {
                    $later = nanotime($limit);
                    $trm[] = "suspend = $limit";
                    $trm[] = "group_suspend = '$gsuspend_str'";
                    $ors[] = "suspend != $limit";
                    $ors[] = "group_suspend != '$gsuspend_str'";
                    $sgrp  = find_mgrp_gid($gsuspend_str, constReturnGroupTypeMany, $db);
                    $arg[] = array('Suspend',       group_detail($sgrp));
                    $arg[] = array('Suspend Until', $x_tim);
                }
            }
            if ($x_def)
            {
                $value = $x_def - 1;
                $trm[] = "defmail = $value";
                $ors[] = "defmail != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Default Recipients',$value);
            }
            if ($x_eml)
            {
                $value = $x_eml - 1;
                $trm[] = "email = $value";
                $ors[] = "email != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('EMail',$value);
            }
            if ($x_con)
            {
                $value = $x_con - 1;
                $trm[] = "console = $value";
                $ors[] = "console != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Console',$value);
            }
            if ($x_lnk)
            {
                $value = $x_lnk - 1;
                $trm[] = "links = $value";
                $ors[] = "links != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Links',$value);
            }
            if ($x_rst)
            {
                $value = $x_rst - 1;
                $trm[] = "solo = $value";
                $ors[] = "solo != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Restricted',$value);
            }
            if ($x_tld)
            {
                $value = $x_tld - 1;
                $trm[] = "threshold = $value";
                $ors[] = "threshold != $value";
                $arg[] = array('Threshold',$value);
            }
            if ($x_frq)
            {
                $value = $x_frq;
                $ntype = constScheduleClassic;
                $trm[] = "seconds = $value";
                $cnd[] = "ntype = $ntype";
                $value = speak_freq($value);
                $arg[] = array('Frequency',$value);
            }
            if ($x_skp)
            {
                $value = $x_skp - 1;
                $trm[] = "skip_owner = $value";
                $ors[] = "skip_owner != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Skip owner',$value);
            }
            if ($x_ftr)
            {
                $value = $x_ftr - 1;
                $trm[] = "email_footer = $value";
                $ors[] = "email_footer != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('E-mail footer',$value);
            }
            if ($x_eps)
            {
                $value = $x_eps - 1;
                $trm[] = "email_per_site = $value";
                $ors[] = "email_per_site != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('E-mail per site',$value);
            }
            if ($x_ems)
            {
                $value = $x_ems - 1;
                $trm[] = "email_sender = $value";
                $ors[] = "email_sender != $value";
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Use Site E-mail',$value);
            }

        }

        if (($trm) && ($ids))
        {
            $txt   = join(',',$ids);
            $cnd[] = "username = '$qu'";
            $cnd[] = '0 <= next_run';
            $trm[] = "modified = $now";
            $cnd[] = "id in ($txt)";
            if ($ors)
            {
                $value = join("\n or ",$ors);
                $count = safe_count($ors);
                $cnd[] = ($count > 1)? "($value)" : $value;
            }
            $sets  = join(",\n ",$trm);
            $cond  = join("\n and ",$cnd);
            $sql   = "update Notifications set\n $sets\n"
                   . " where $cond";
            $res   = redcommand($sql,$db);
            $num   = affected($res,$db);
            debug_note("$num records updated");
        }
        $set = array();
        if (($num) && ($ids))
        {
            $txt = join(',',$ids);
            $wrd = order($env['ord']);
            $sql = "select N.*,\n"
                 . " S.name as search from\n"
                 . " Notifications as N,\n"
                 . " SavedSearches as S\n"
                 . " where N.search_id = S.id\n"
                 . " and N.modified = $now\n"
                 . " and N.id in ($txt)\n"
                 . " order by $wrd";
            $set = find_many($sql,$db);
        }

        if ($set)
        {
            $txt = 'Updated';
            gang_table($env,$set,0,$txt);
        }

        if (($set) && ($arg))
        {
            $date  = datestring($now);
            $arg[] = array('Available',$initial);
            $arg[] = array('Selected',count($ids));
            $arg[] = array('Updated',count($set));
            $arg[] = array('Modified',$date);

            echo table_header();
            echo pretty_header('Changes',2);
            reset($arg);
            foreach ($arg as $key => $row)
            {
                echo double($row[0],$row[1]);
            }
            echo table_footer();
        }

        if (($ids) && (!$set))
        {
            $text = 'No updates were applied.';
            echo para($text);
        }

        echo button('Continue');
        echo form_footer();
        echo again($env);
    }


    function genb_exec(&$env,$db)
    {
        $num = find_notify_count($env,$db);
        $set = array( );
        $ids = array( );
        $trm = array( );
        $cnd = array( );
        $ors = array( );
        $arg = array( );
        if ($num)
        {
            $sql = gen_query($env,0,$num);
            $set = find_many($sql,$db);
            $num = 0;
        }

        $initial = 0;
        if ($set)
        {
            $initial = safe_count($set);
            reset($set);
            foreach ($set as $key => $data)
            {
                $nid = $data['id'];
                $tag = "nid_$nid";
                if (get_integer($tag,0))
                {
                    $ids[] = $nid;
                }
            }
            $set = array( );
        }

        echo mark('table');
        echo again($env);

        $now  = $env['now'];
        gang_preserve($env,'list');

        echo para(button('Continue'));

        if (!$ids)
        {
            echo para('No notifications selected ...');
        }
        else
        {
            $x_enb = tag_int('x_enb',0,2,0);
            $qu  = safe_addslashes($env['auth']);
            if ($x_enb)
            {
                $value = $x_enb - 1;
                $trm[] = "enabled = $value";
                $ors[] = "enabled != $value";
                $trm[] = 'next_run = 0';
                if ($value)
                {
                    $trm[] = "last_run = $now";
                    $trm[] = 'retries = 0';
                }
                $value = ($value)? 'Yes' : 'No';
                $arg[] = array('Enabled',$value);
            }
        }

        if (($trm) && ($ids))
        {
            $txt   = join(',',$ids);
            $cnd[] = "username = '$qu'";
            $cnd[] = '0 <= next_run';
            $trm[] = "modified = $now";
            $cnd[] = "id in ($txt)";
            if ($ors)
            {
                $value = join("\n or ",$ors);
                $count = safe_count($ors);
                $cnd[] = ($count > 1)? "($value)" : $value;
            }
            $sets = join(",\n ",$trm);
            $cond = join("\n and ",$cnd);
            $sql  = "update Notifications set\n $sets\n"
                  . " where $cond";
            $res  = redcommand($sql,$db);
            $num  = affected($res,$db);
            debug_note("$num records updated");
        }

        if (($num) && ($ids))
        {
            $txt = join(',',$ids);
            $wrd = order($env['ord']);
            $sql = "select * from Notifications\n"
                 . " where modified = $now\n"
                 . " and id in ($txt)\n"
                 . " order by $wrd";
            $set = find_many($sql,$db);
        }

        if ($set)
        {
            $txt = 'Updated';
            gang_table($env,$set,0,$txt);
        }

        if (($set) && ($arg))
        {
            $date  = datestring($now);
            $arg[] = array('Available',$initial);
            $arg[] = array('Selected',count($ids));
            $arg[] = array('Updated',count($set));
            $arg[] = array('Modified',$date);

            echo table_header();
            echo pretty_header('Changes',2);
            reset($arg);
            foreach ($arg as $key => $row)
            {
                echo double($row[0],$row[1]);
            }
            echo table_footer();
        }

        if (($ids) && (!$set))
        {
            echo para('No notifications were enabled or disabled.');
        }

        echo button('Continue');
        echo form_footer();
        echo again($env);
    }

    function gang_disp(&$env,$db)
    {
        $post = $env['post'];
        $done = ($post == constButtonOk);
        if ($done)
            gang_exec($env,$db);
        else
            gang_form($env,$db);
    }


    function genb_disp(&$env,$db)
    {
        $post = $env['post'];
        $done = ($post == constButtonOk);
        if ($done)
            genb_exec($env,$db);
        else
            genb_form($env,$db);
    }


    function gdel_disp(&$env,$db)
    {
        $post = $env['post'];
        $done = ($post == constButtonOk);
        if ($done)
            gdel_conf($env,$db);
        else
            gdel_form($env,$db);
    }


    function gdel_exec(&$env,$db)
    {
        echo mark('table');
        echo again($env);

        $num = find_notify_count($env,$db);
        $set = array( );
        $ids = array( );
        if ($num)
        {
            $sql = gen_query($env,0,$num);
            $set = find_many($sql,$db);
            $num = 0;
        }

        if ($set)
        {
            reset($set);
            foreach ($set as $key => $data)
            {
                $nid = $data['id'];
                $tag = "nid_$nid";
                if (get_integer($tag,0))
                {
                    $ids[] = $nid;
                }
            }
            $set = array( );
        }

        $num = 0;
        if ($ids)
        {
            $txt = join(',',$ids);
            $sql = "delete from Notifications\n"
                 . " where id in ($txt)";
            if (!$env['user']['priv_admin'])
            {
                $qu  = safe_addslashes($env['auth']);
                $sql = "$sql\n and username = '$qu'";
            }
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
        }

        $cont = button('Continue');

        gang_preserve($env,'list');
        echo para("$num notifications deleted ...");
        echo para($cont);
        echo form_footer();
        echo again($env);
    }



    function manage_notify(&$env,$db)
    {
        echo again($env);
        $self = $env['self'];
        $jump = $env['jump'];
        $priv = $env['priv'];
        $glob = ($priv)? 3 : 0;
        $cmd  = "$self?act";

        $time = (86400 * 14);
        $cmp  = "$cmd=list&mal=-1&dsp=1&gbl=$glob&own=0";
        $next = "$cmp&nxt=$time&adv=1&o=24&enb=2$jump";
        $last = "$cmp&lst=14&adv=0&o=20$jump";
        $mods = "$cmp&mod=30&adv=1&dst=-1&o=4$jump";
        $back = gang_href($env,'list') . $jump;
        $n    = 'Notification';
        $m    = 'Multiple';

        $act = array( );
        $txt = array( );

        $act[] = gang_href($env,'gang');
        $txt[] = "Edit $m ${n}s";

        $act[] = gang_href($env,'genb');
        $txt[] = "Enable/Disable $m ${n}s";

        $act[] = gang_href($env,'gdel');
        $txt[] = "Delete $m ${n}s";

        $act[] = "$cmd=addn";
        $txt[] = "Create A New $n";

        $act[] = $last;
        $txt[] = "${n}s Run Within the Last Two Weeks";

        $act[] = $mods;
        $txt[] = "${n}s Modified Within the Last Month";

        $act[] = $next;
        $txt[] = "${n}s Scheduled to Run During the Next Two Weeks";

        $act[] = $back;
        $txt[] = "Back to Filtered ${n}s Page";

        $act[] = $self . $jump;
        $txt[] = "${n}s Default View";

        command_list($act,$txt);
        echo again($env);
    }



    function over_form(&$env,$db)
    {
        echo again($env);
        $nid  = $env['nid'];
        $ntfy = find_notify($nid,$db);
        $good = false;
        if ($ntfy)
        {
            $glob = $ntfy['global'];
            $user = $ntfy['username'];
            $auth = $env['auth'];
            $name = $ntfy['name'];
            $locl = find_notify_name($name,0,$auth,$db);
            $good = (($glob) && ($auth != $user) && (!$locl));
        }
        if ($good)
        {
            $shed = load_schedule($nid,$db);
            $ntfy['id']       = 0;
            $ntfy['global']   = 0;
            $ntfy['username'] = $auth;
            $ntfy['email_footer_txt'] = set_email_footer($ntfy['email_footer_txt']);
            $head = 'Create Local Report';
            notify_form($env,$ntfy,$shed,$head,$db);
        }
        echo again($env);
    }


    function claim_lock(&$env,$db)
    {
        echo again($env);
        $pid  = $env['pid'];
        $lock = server_int('notify_lock',0,$db);
        $lpid = server_int('notify_pid',0,$db);
        $timo = server_int('notify_timeout',0,$db);
        if (($lock) && ($lpid))
        {
            echo para("Notify Lock owned by <b>$lpid</b>.");
        }
        else
        {
            if (($timo > 0) && ($pid))
            {
                $now  = time();
                $when = ($now - $timo) + 60;
                echo para("Timeout is <b>$timo</b> seconds.");
                if (update_opt('notify_lock','1',$db))
                {
                    $sql = "update ".$GLOBALS['PREFIX']."core.Options set\n"
                         . " value = $pid,\n"
                         . " modified = $when\n"
                         . " where name = 'notify_pid'";
                    redcommand($sql,$db);
                    $xxx = nanotime($when);
                    $txt = "notify: fake lock by process $pid at $xxx";
                    echo para("Notify Lock claimed by process <b>$pid</b>.");
                    logs::log(__FILE__, __LINE__, $txt,0);
                    debug_note($txt);
                }
            }
            else
            {
                echo para('Timeout is zero.');
            }
        }

        echo again($env);
    }


    function pick_lock(&$env,$db)
    {
        echo again($env);
        $now  = $env['now'];
        $lock = find_opt('notify_lock',$db);
        $lpid = find_opt('notify_pid',$db);
        if (($lock) && ($lpid))
        {
            $age  = 0;
            $ownr = $lpid['value'];
            $when = $lock['modified'];
            $age  = ($now - $when);
            echo para("Notify Lock owned by <b>$ownr</b>. ($age seconds)");
            opt_update('notify_pid',0,0,$db);
            opt_update('notify_lock',0,0,$db);
        }
        echo again($env);
    }


    function fix_invalid(&$env,$db)
    {
        echo again($env);
        $now = time();
        $sql = "update Notifications set\n"
             . " enabled = 1,\n"
             . " last_run = $now,\n"
             . " this_run = 0,\n"
             . " next_run = 0\n"
             . " where enabled = 2";
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
        $msg = ($num)? "$num notifications changed." : 'No change.';
        echo para($msg);
        echo again($env);
    }


    function sanity(&$env,$db)
    {
        echo again($env);
        $sql = "select N.search_id from\n"
             . " Notifications as N\n"
             . " left join SavedSearches as S\n"
             . " on S.id = N.search_id\n"
             . " where S.id is NULL\n"
             . " group by N.search_id";
        $set = find_many($sql,$db);
        if ($set)
        {
            $num = safe_count($set);
            echo para("There are $num missing searches.");
            reset($set);
            foreach ($set as $key => $row)
            {
                $sid = $row['search_id'];
                $sql = "delete from\n"
                     . " Notifications\n"
                     . " where search_id = $sid";
                $res = redcommand($sql,$db);
                $num = affected($res,$db);
                debug_note("Search $sid does not exist, $num records removed.");
            }
        }
        else
        {
            echo para($GLOBALS['PREFIX'].'event.Notifications.search_id: OK');
        }
        $sql = "update Notifications set\n"
             . " next_run = 0,\n"
             . " this_run = 0\n"
             . " where enabled = 0";
        $res = redcommand($sql,$db);
        $num = affected($res,$db);
        debug_note("$num schedule problems.");
        echo again($env);
    }

    function warn_fail(&$env,$db)
    {
        echo again($env);
        echo para('mysql Database Failure ...');
        echo again($env);
    }


   /*
    |  Main program
    */

    $now  = time();
    $db   = db_connect();
    $auth = process_login($db);
    $comp = component_installed();
    $act  = get_string('act','list');
    $post = get_string('button','');
    if ($post == constButtonCan)
    {
        $act = 'list';
    }

    $txt  = '|||addn|view|cdel|menu|stat|rset|gang|help|skip|gdel|mnge|';
    $gif  = (matchOld($act,$txt))?  '' : '../pub/priority.gif';
    $name = title($act);
    $msg  = ob_get_contents();
    ob_end_clean();
    echo standard_html_header($name,$comp,$auth,0,0,$gif,$db);
    $user  = user_data($auth,$db);
    $priv  = @ ($user['priv_debug'])?  1 : 0;
    $admn  = @ ($user['priv_admin'])?  1 : 0;
    $dbg   = get_integer('debug',0);
    $debug = ($priv)? $dbg : 0;

    if (trim($msg)) debug_note($msg);

    debug_array($debug,$_POST);

   /*
    |  coordinate with
    |     notify_table()
    |     page_href()
    */

    $day = 86400;
    $dsp = tag_int('dsp', 0,1,0);         // display, expanded
    $adv = tag_int('adv', 0,1,1);         // interface, advanced
    $mal = tag_int('mal',-1,0,0);         // recipients, any
    $rst = tag_int('rst',-1,2,-1);        // restricted, not displayed
//  $xcl = tag_int('xcl',-1,2,-1);        // excluded, not displayed
    $skp = tag_int('skp',-1,2,-1);        // skip_owner, not displayed
    $ftr = tag_int('ftr',-1,2,-1);        // email_footer
    $eps = tag_int('eps',-1,2,-1);        // email_per_site
    $ems = tag_int('ems',-1,2,-1);        // email_sender
    $pnd = tag_int('pnd',-2,9999,-1);        // suspend, no display
    $gnc = tag_int('gnc',-2,9999,-1);        // group_include
    $gxc = tag_int('gxc',-2,9999,-1);        // group_exclude
    $gbl = tag_int('gbl',-1,3,-1);        // global, not displayed
    $def = tag_int('def',-1,3,-1);        // defmail, not displayed
    $lnk = tag_int('lnk',-1,3,-1);        // links, not displayed
    $enb = tag_int('enb',-1,3,0);         // enabled, any
    $dst = tag_int('dst',-1,4,0);         // destination, any
    $pri = tag_int('pri',-1,5,-1);        // priority, no display
    $exp = tag_int('exp',-1,29,-1);       // expires, no display
    $tld = tag_int('tld',-1,999,-1);      // threshhold, no display
    $crt = tag_int('crt',-2,9999,-1);     // create, no display
    $mod = tag_int('mod',-2,9999,-1);     // modified, no display
    $lst = tag_int('lst',-2,9999,0);      // last run, no display
    $frq = tag_int('frq',-1,$day*7,0);    // frequency, display
    $nxt = tag_int('nxt',-1,$day*14,-1);  // next run, no display
    $pag = tag_int('p',0,9999,0);
    $lim = tag_int('l',5,5000,20);
    $xxx = ($act == 'next')? 24 : 0;
    $xxx = ($act == 'last')? 20 : $xxx;
    /*
    |  When adding a new search option field, this value must
    |  be updated to equal the highest value in both ords()
    |  and order().
    */
    $ord = tag_int('o',0,55,$xxx);

    $s_g_include = get_integer('s_g_include', -2);
    $s_g_exclude = get_integer('s_g_exclude', -2);
    $s_g_suspend = get_integer('s_g_suspend', -2);

    if ($s_g_include > $gnc)
    {
        $gnc = $s_g_include;
    }
    if ($s_g_exclude > $gxc)
    {
        $gxc = $s_g_exclude;
    }
    if ($s_g_suspend > $pnd)
    {
        $pnd = $s_g_suspend;
    }

    $flt = get_integer('flt',0);          // filter, any
    $own = get_integer('own',-1);         // username, no display
    $nid = get_integer('nid', 0);
    $txt = get_string('txt','');          // N.emaillist like
    $pat = get_string('pat','');          // N.name like
    $src = get_string('src','');          // S.name like

    // Autodesk footer values
    $email_footer_txt = get_string('email_footer_txt', default_email_footer());
    $email_footer     = get_integer('email_footer',   0);
    $email_per_site   = get_integer('email_per_site', 0);
    $email_sender     = get_integer('email_sender',   0);

    if (!$admn)
    {
        $gbl = value_range(-1,2,$gbl);
    }

    if ($post == constButtonRst)
    {
        $dsp = 0;  $gbl = -1;  $pnd = -1;  $lim = 20;  $gnc = -1;
        $mal = 0;  $lst = -1;  $pri = -1;  $pag =  0;  $gxc = -1;
        $enb = 0;  $def = -1;  $exp = -1;  $txt = '';
        $dst = 0;  $lnk = -1;  $tld = -1;  $pat = '';
        $frq = 0;  $crt = -1;  $src = '';
        $nid = 0;  $rst = -1;  $mod = -1;  $ftr = -1;
        $flt = 0;  $nxt = -1;  $own = -1;  $eps = -1;
        $ord = 0;  $skp = -1;  $adv =  1;  $ems = -1;
    }

    if ($post == constButtonMore)
    {
        $adv = 1;
    }
    if ($post == constButtonLess)
    {
        $adv = 0;
    }


    $env = array( );
    $env['pid'] = getmypid();
    $env['ord'] = $ord;
    $env['act'] = $act;
    $env['now'] = $now;
    $env['dsp'] = $dsp;
    $env['adv'] = $adv;
    $env['dst'] = $dst;   // email / console
    $env['nid'] = $nid;   // Notifications.id
    $env['rst'] = $rst;   // Notifications.solo
    $env['exp'] = $exp;   // Notifications.days
    $env['lnk'] = $lnk;   // Notifications.links
    $env['gbl'] = $gbl;   // Notifications.global
    $env['crt'] = $crt;   // Notifications.created
    $env['def'] = $def;   // Notifications.defmail
    $env['enb'] = $enb;   // Notifications.enabled
    $env['frq'] = $frq;   // Notifications.seconds
    $env['pnd'] = $pnd;   // Notifications.suspend
    $env['gnc'] = $gnc;   // Notifications.group_include
    $env['gxc'] = $gxc;   // Notifications.group_exclude
    $env['own'] = $own;   // Notifications.username
    $env['pri'] = $pri;   // Notifications.priority
    $env['mod'] = $mod;   // Notifications.modified
    $env['lst'] = $lst;   // Notifications.last_run
    $env['nxt'] = $nxt;   // Notifications.next_run
//  $env['xcl'] = $xcl;   // Notifications.excluded
    $env['mal'] = $mal;   // Notifications.emaillist
    $env['tld'] = $tld;   // Notifications.threshold
    $env['flt'] = $flt;   // Notifications.search_id
    $env['skp'] = $skp;   // Notifications.skip_owner
    $env['ftr'] = $ftr;   // Notifications.email_footer
    $env['eps'] = $eps;   // Notifications.email_per_site
    $env['ems'] = $ems;   // Notifications.email_sender
    $env['pat'] = $pat;   // N.name like
    $env['src'] = $src;   // S.name like
    $env['txt'] = $txt;   // N.emaillist like
    $env['email_footer_txt'] = $email_footer_txt; // Autotask html footer value
    $env['email_footer']     = $email_footer;     // include the email footer
    $env['email_per_site']   = $email_per_site;   // generate 1 email per notification
    $env['email_sender']     = $email_sender;     // use the per site sender configured from address
    $env['href'] = 'page_href';
    $env['jump'] = '#table';
    $env['post'] = $post;
    $env['priv'] = $priv;
    $env['auth'] = $auth;
    $env['user'] = $user;
    $env['dbug'] = $debug;
    $env['midn'] = midnight($now);
    $env['page'] = $pag;
    $env['limt'] = $lim;
    $env['self'] = server_var('PHP_SELF');
    $env['args'] = server_var('QUERY_STRING');

    $env['d_nam'] = (  true   );
    $env['d_mod'] = (0 <= $mod);
    $env['d_crt'] = (0 <= $crt);
    $env['d_lst'] = (0 <= $lst);
    $env['d_nxt'] = (0 <= $nxt);
    $env['d_flt'] = (0 <= $flt);
    $env['d_gnc'] = (0 <= $gnc);
    $env['d_gxc'] = (0 <= $gxc);
    $env['d_pnd'] = (0 <= $pnd);

    $env['d_act'] = (0 == $dsp);
    $env['d_def'] = (0 == $def);
    $env['d_dst'] = (0 == $dst);
    $env['d_enb'] = (0 == $enb);
    $env['d_exp'] = (0 == $exp);
    $env['d_frq'] = (0 == $frq);
    $env['d_lnk'] = (0 == $lnk);
    $env['d_mal'] = (0 == $mal);
    $env['d_pri'] = (0 == $pri);
    $env['d_rst'] = (0 == $rst);
    $env['d_skp'] = (0 == $skp);
    $env['d_ftr'] = (0 == $ftr);
    $env['d_eps'] = (0 == $eps);
    $env['d_ems'] = (0 == $ems);
//  $env['d_xcl'] = (0 == $xcl);

    $env['d_nid'] = (3 == $gbl);
    $env['d_own'] = ((0 <= $own) || ($gbl == 3));
    $env['d_tld'] = ((0 == $tld) || ($tld > 30));
    $env['d_gbl'] = ((0 == $gbl) || (3 == $gbl));

    if (!$priv)
    {
        $txt = '|||next|past|stat|menu|fix|rset|sane'
             . '|||skip|queu|lock|pick|time|push||||';
        if (matchOld($act,$txt))
        {
            $act = 'list';
        }
    }

    check_queue($env,$db);
    if (!mysqli_select_db($db, event))
    {
        $act = 'fail';
    }
    switch ($act)
    {
        case 'menu': debug_menu($env,$db);            break;
        case 'next': schedule_next($env,$db);         break;
        case 'last': schedule_past($env,$db);         break;
        case 'rset': reset_notify($env,$db);          break;
        case 'skip': skip_notify($env,$db);           break;
        case 'stat': statistics($env,$db);            break;
        case 'queu': queue_manage($env,$db);          break;
        case 'post': queue_post($env,$db);            break;
        case 'time': queue_time($env,$db);            break;
        case 'frst': queue_first($env,$db);           break;
        case 'push': queue_push($env,$db);            break;
        case 'lock': claim_lock($env,$db);            break;
        case 'pick': pick_lock($env,$db);             break;
        case 'sane': sanity($env,$db);                break;
        case 'fix' : fix_invalid($env,$db);           break;
        /* ---------------------------------------------- */
        case 'list': list_notify($env,$db);           break;
        case 'mnge': manage_notify($env,$db);         break;
        case 'gang': gang_disp($env,$db);             break;
        case 'genb': genb_disp($env,$db);             break;
        case 'gdel': gdel_disp($env,$db);             break;
        case 'gexp': gdel_exec($env,$db);             break;
        case 'addn': add_form($env,$db);              break;
        case 'copy': copy_form($env,$db);             break;
        case 'over': over_form($env,$db);             break;
        case 'edit': edit_form($env,$db);             break;
        case 'insn': add_exec($env,$db);              break;
        case 'cdel': delete_conf($env,$db);           break;
        case 'rdel': delete_act($env,$db);            break;
        case 'disb': disable_act($env,$db);           break;
        case 'enab': enable_act($env,$db);            break;
        case 'dovr': over_disb($env,$db);             break;
        case 'eovr': over_enab($env,$db);             break;
        case 'updt': upd_exec($env,$db);              break;
        case 'view': notify_detail($env,$db);         break;
        case 'fail': warn_fail($env,$db);             break;
        default    : list_notify($env,$db);           break;
    }
    echo head_standard_html_footer($auth,$db);
?>
