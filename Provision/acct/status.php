<?php

/*
Revision history:

Date        Who     What
----        ---     ----
12-Apr-04   EWB     Created.
13-Apr-04   EWB     Show client version in most recent events table.
13-Apr-04   EWB     Show updates and meter logging.
14-Apr-04   EWB     Event status shows purge time.
14-Apr-04   EWB     Asset status shows purge time.
15-Apr-04   EWB     Config/Update/Meter Stats.
15-Apr-04   EWB     Postpone/Audit.
17-Apr-04   EWB     Postpone redraws queue.
19-Apr-04   EWB     Created Master Reset command.
20-Apr-04   EWB     Census uses census_days, not purge_days
20-Apr-04   EWB     Status Menu
21-Apr-04   EWB     Show number of sites in census statistics.
25-Apr-04   EWB     Disk space percentage in tenths.
20-May-04   EWB     Display event latency.
10-Jun-04   EWB     Hourly event summary.
12-Jun-04   EWB     Daily event summary.
16-Jul-04   EWB     Fix future clock notification problem.
 3-Sep-04   EWB     Categorize Events.
 7-Sep-04   EWB     Debug command to disable all reports / notifications.
 8-Oct-04   EWB     Skip pending notifications forward.
21-Mar-05   EWB     Show Active Users
25-Mar-05   EWB     Show both active sites and total sites for each user.
28-Mar-05   EWB     added event detail links to event info.
12-May-05   EWB     uses new gconfig database for table information.
 1-Aug-05   EWB     generate link to notify console details page
12-Oct-05   BTE     Changed references from gconfig to core.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.
19-Feb-08   BTE     Bug 4416: Move the "last event log" timestamp into shared
                    memory.

*/

    ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-jump.php'  );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-gsql.php'  );
include_once ( '../lib/l-cmth.php'  );
include_once ( '../lib/l-tabs.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-cnst.php'  );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-core.php'  );


    function title($act)
    {
        switch ($act)
        {
            case 'asst': return 'Asset Status';
            case 'audt': return 'Audit Status';
            case 'cats': return 'Categorize Events';
            case 'clok': return 'Clock Reset';
            case 'cnfg': return 'Config Status';
            case 'cnsl': return 'Console Status';
            case 'disk': return 'Disk Space';
            case 'eday': return 'Events Today';
            case 'evnt': return 'Event Status';
            case 'file': return 'File Status';
            case 'kill': return 'Disable All Report / Notify';
            case 'logs': return 'Recent Activity';
            case 'menu': return 'Status Menu';
            case 'user': return 'Active Users';
            case 'metr': return 'Meter Status';
            case 'mnth': return 'Daily Events Summary';
            case 'msrt': return 'Master Reset';
            case 'past': return 'Recent Queue Status';
            case 'post': return 'Postpone';
            case 'priv': return 'No Access';
            case 'queu': return 'Queue Status';
            case 'updt': return 'Update Status';
            default    : return 'Server Status';
        }
    }

    function field($fld)
    {
        switch ($fld)
        {
            case  0: return 'customer';
            case  1: return 'machine';
            case  2: return 'scrip';
            case  3: return 'description';
            case  4: return 'clientversion';
            case  5: return 'version';
            case  6: return 'executable';
            case  7: return 'windowtitle';
            case  8: return 'path';
            case  9: return 'string1';
            default: return field(0);
        }
    }


    function again(&$env)
    {
        $self = $env['self'];
        $dbg = $env['priv'];
        $act = $env['act'];
        $cmd = "$self?act";
        $mon = "$self?refresh=60&act";
        $a   = array( );
        $a[] = html_link('#top','top');
        $a[] = html_link('#bottom','bottom');
        $a[] = html_link("$cmd=evnt",'event');
        $a[] = html_link("$cmd=asst",'asset');
        $a[] = html_link("$cmd=cnfg",'config');
        $a[] = html_link("$cmd=updt",'update');
        $a[] = html_link("$mon=queu",'queue');
        $a[] = html_link("$cmd=menu",'menu');
        if ($dbg)
        {
            $args = $env['args'];
            $href = ($args)? "$self?$args" : $self;
            $a[] = html_link('index.php','home');
            $a[] = html_link($href,'again');
        }
        return jumplist($a);
    }


    function queue_tags(&$env)
    {
        $dbg = $env['priv'];
        $not = '../event/notify.php?act=queu';
        $rep = '../event/report.php?act=queu';
        $ass = '../asset/report.php?act=queu';
        $a   = array( );
        $a[] = html_link('#top','top');
        $a[] = html_link('#bottom','bottom');
        $a[] = html_link('#notify','notify');
        $a[] = html_link('#report','report');
        $a[] = html_link('#asset','asset');
        if ($dbg)
        {
            $a[] = html_link($not,'nq');
            $a[] = html_link($rep,'rq');
            $a[] = html_link($ass,'aq');
        }
        return jumplist($a);
    }


    function timetags(&$env)
    {
        $self = $env['self'];
        $day = $env['day'];
        $act = $env['act'];
        $fld = $env['fld'];
        $prv = $day + 1;
        $nxt = $day - 1;
        $cmd = "$self?act=$act&fld=$fld&day";
        $a   = array( );
        if ($day > 0)
        {
            $a[] = html_link("$cmd=0",'today');
            $a[] = html_link("$cmd=$nxt",'next');
        }

        $a[] = html_link("$cmd=$prv",'prev');

        $cmd = "$self?act=$act&day=$day&fld";
        $a[] = html_link("$cmd=0",'site');
        $a[] = html_link("$cmd=1",'machine');
        $a[] = html_link("$cmd=3",'description');
        $a[] = html_link("$cmd=4",'version');
        $a[] = html_link("$cmd=2",'scrip');
        return jumplist($a);
    }


    function value_range($min,$max,$val)
    {
        if ($val <= $min) $val = $min;
        if ($max <= $val) $val = $max;
        return $val;
    }

    function short_date($x)
    {
        $text = '<br>';
        if ($x > 0)
        {
    //      $date = date('m/d/y',$x);
    //      $time = date('H:i:s',$x);
    //      $text = "$date<br>$time";
            $text = date('m/d/y H:i:s',$x);
        }
        return $text;
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


    function bold($text)
    {
        return "<b>$text</b>";
    }

    function showtime($when)
    {
        $text = '<br>';
        if ($when > 0)
        {
            $now  = date('m/d/y',time());
            $mdy  = date('m/d/y',$when);
            $hms  = date('H:i:s',$when);
            $text = ($now == $mdy)? $hms : "$mdy $hms";
        }
        if ($when < 0)
        {
            $text = 'running';
        }
        return $text;
    }

    function fulltime($when)
    {
        $text = '<br>';
        if ($when > 0)
        {
            $text = date('m/d/y H:i:s',$when);
        }
        if ($when < 0)
        {
            $text = 'running';
        }
        return $text;
    }


    function state(&$env,$row)
    {
        $now  = $env['now'];
        $enab = $row['enabled'];
        $next = $row['next_run'];
        $last = $row['last_run'];
        $that = $row['this_run'];
        $stat = 'unknown';
        if ($enab == 1)
        {
            if ($next < 0)
            {
                if ($that > 0)
                {
                    $age  = age(abs($now - $that));
                    $text = "run ($age)";
                    $stat = bold($text);
                }
                else
                {
                    $stat = 'claimed';
                }
            }
            if ($next == 0)
            {
                $stat = 'inactive';
            }
            if (($next > 0) && ($next <= $now))
            {
                $stat = 'ready';
            }
            if ($now < $next)
            {
                $stat = 'future';
            }
        }
        if ($enab == 2) $stat = 'invalid';
        if ($enab == 0) $stat = 'disabled';
        return $stat;
    }


    function display_notify(&$env,$past,$db)
    {
        echo mark('notify');
        echo queue_tags($env);
        $now  = $env['now'];
        $limt = $env['limt'];
        if ($past)
        {
            $txt = 'Recent Notifications';
            $sql = "select * from Notifications\n"
                 . " where last_run > 0\n"
                 . " order by last_run desc, next_run, id\n"
                 . " limit $limt";
        }
        else
        {
            $txt = 'Notify Queue';
            $sql = "select * from Notifications\n"
                 . " where enabled = 1\n"
                 . " order by next_run, this_run desc, id\n"
                 . " limit $limt";
        }
        $set = find_many($sql,$db);
        if ($set)
        {
            $what = ($past)? 'Age' : 'Wait';
            $head = explode('|',"$what|State|Name|Owner|Id|Next|Last|Frequency");
            $cols = safe_count($head);
            $cmd  = '../event/notify.php?act=view&nid';

            echo table_header();
            echo pretty_header($txt,$cols);
            echo table_data($head,1);
            reset($set);
            foreach ($set as $key => $row)
            {
                $nid  = $row['id'];
                $name = $row['name'];
                $glob = $row['global'];
                $freq = $row['seconds'];
                $user = $row['username'];
                $last = $row['last_run'];
                $that = $row['this_run'];
                $next = $row['next_run'];
                $prio = $row['priority'];
                $wait = '<br>';

                if ($past)
                {
                    if ((0 < $last) && ($last < $now))
                    {
                        $secs = $now - $last;
                        $wait = age($secs);
                    }
                }
                else
                {
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
                            $wait = bold($late);
                        }
                    }
                    if (($next < 0) && ($that > 0))
                    {
                        $wait = '(run)';
                        $name = bold($name);
                    }
                }
                $last = showtime($last);
                $next = showtime($next);
                $scop = ($glob)? 'g' : 'l';
                $ownr = "$user($scop)";
                $stat = state($env,$row);
                $secs = age($freq);
                $link = html_link("$cmd=$nid",$name);
                $args = array($wait,$stat,$link,$ownr,$nid,$next,$last,$secs);
                echo table_data($args,0);
            }
            echo table_footer();
        }
    }


    function display_events(&$env,$db)
    {
        $snow = $env['now'];
        $limt = $env['limt'];
        $sql  = "select * from Events\n"
              . " order by servertime desc\n"
              . " limit $limt";
        $set  = find_many($sql,$db);
        if ($set)
        {
            $head = explode('|','Age|Latency|Machine|Site|Version|When|Id|Scrip|Description');
            $cols = safe_count($head);
            $text = 'Recent Events';

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);
            reset($set);
            foreach ($set as $key => $row)
            {
                $eid  = $row['idx'];
                $scrp = $row['scrip'];
                $stim = $row['servertime'];
                $ctim = $row['entered'];
                $host = disp($row,'machine');
                $site = disp($row,'customer');
                $vers = disp($row,'clientversion');
                $desc = disp($row,'description');
                $href = "../event/detail.php?eid=$eid";
                $link = html_link($href,$eid);
                $age  = age(abs($snow - $stim));
                $late = age(abs($stim - $ctim));
                $last = showtime($stim);
                $args = array($age,$late,$host,$site,$vers,$last,$link,$scrp,$desc);
                echo table_data($args,0);
            }
            echo table_footer();
        }
    }

    function display_console(&$env,$db)
    {
        $now  = $env['now'];
        $limt = $env['limt'];
        $sql  = "select * from Console\n"
              . " order by servertime desc\n"
              . " limit $limt";
        $set  = find_many($sql,$db);
        if ($set)
        {

            $head = explode('|','Age|Name|Owner|Site|Count|Created|Expires|Id');
            $cols = safe_count($head);
            $text = 'Recent Notifications';

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $cid  = $row['id'];
                $last = $row['servertime'];
                $expr = $row['expire'];
                $recs = $row['count'];
                $user = disp($row,'username');
                $site = disp($row,'site');
                $name = disp($row,'name');
                $date = '<br>';
                $age  = age($now - $last);
                $last = showtime($last);
                if ((0 < $expr) && ($expr < 0x7fffffff))
                {
                    $date = showtime($expr);
                }
                $href = "../event/cnsl-det.php?id=$cid";
                $link = html_link($href,$cid);
                $args = array($age,$name,$user,$site,$recs,$last,$date,$link);
                echo table_data($args,0);
            }
            echo table_footer();
        }
    }


    function display_meter(&$env,$db)
    {
        $now  = $env['now'];
        $limt = $env['limt'];
        $sql  = "select * from Meter\n"
              . " order by servertime desc\n"
              . " limit $limt";
        $set  = find_many($sql,$db);
        if ($set)
        {

            $head = explode('|','Age|Machine|Site|Product|Owner|File|When|Id');
            $cols = safe_count($head);
            $text = 'Recent Meter';

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);
            reset($set);
            foreach ($set as $key => $row)
            {
                $mid  = $row['meterid'];
                $last = $row['servertime'];
                $host = disp($row,'machine');
                $site = disp($row,'sitename');
                $name = disp($row,'product');
                $ownr = disp($row,'owner');
                $file = disp($row,'exename');

                $age  = age($now - $last);
                $last = showtime($last);
                $args = array($age,$host,$site,$name,$ownr,$file,$last,$mid);
                echo table_data($args,0);
            }
            echo table_footer();
        }
    }


    function display_audit(&$env,$db)
    {
        $now  = $env['now'];
        $limt = $env['limt'];
        $sql  = "select * from Audit\n"
              . " order by servertime desc\n"
              . " limit $limt";
        $set  = find_many($sql,$db);
        if ($set)
        {

            $head = explode('|','Age|Machine|Site|Product|Owner|Action|When|Id');
            $cols = safe_count($head);
            $text = 'Recent Audit';

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);
            reset($set);
            foreach ($set as $key => $row)
            {
                $age  = '<br>';
                $mid  = $row['auditid'];
                $last = $row['servertime'];
                $host = disp($row,'machine');
                $site = disp($row,'sitename');
                $name = disp($row,'product');
                $ownr = disp($row,'owner');
                $acts = disp($row,'action');
                if ((0 < $last) && ($last < $now))
                {
                    $age  = age($now - $last);
                }
                $last = showtime($last);
                $args = array($age,$host,$site,$name,$ownr,$acts,$last,$mid);
                echo table_data($args,0);
            }
            echo table_footer();
        }
    }


    function report_info(&$env,$set,$txt,$past,$ass,$db)
    {
        $now = $env['now'];
        if ($set)
        {
            $what = ($past)? 'Age'  : 'Wait';
            $mods = ($ass)? 'asset' : 'event';

            $cmd  = "../$mods/report.php?act=view&rid";
            $head = explode('|',"$what|State|Name|Owner|Id|Next|Last");
            $cols = safe_count($head);

            echo table_header();
            echo pretty_header($txt,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $rid  = $row['id'];
                $glob = $row['global'];
                $last = $row['last_run'];
                $that = $row['this_run'];
                $next = $row['next_run'];
                $name = $row['name'];
                $user = disp($row,'username');
                $wait = '<br>';

                if ($past)
                {
                    if ((0 < $last) && ($last < $now))
                    {
                        $wait = age($now - $last);
                    }
                }
                else
                {
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
                            $wait = bold($late);
                        }
                    }
                    if (($next < 0) && ($that > 0))
                    {
                        $wait = '(now)';
                        $name = bold($name);
                    }
                }

                $last = showtime($last);
                $next = showtime($next);
                $scop = ($glob)? 'g' : 'l';
                $ownr = "$user($scop)";
                $stat = state($env,$row);
                $link = html_link("$cmd=$rid",$name);
                $args = array($wait,$stat,$link,$ownr,$rid,$next,$last);
                echo table_data($args,0);
            }
            echo table_footer();
        }
    }


    function asset_report(&$env,$past,$db)
    {
        echo mark('asset');
        echo queue_tags($env);
        $now = $env['now'];
        $limt = $env['limt'];
        if ($past)
        {
            $txt = 'Recent Asset Reports';
            $sql = "select * from AssetReports\n"
                 . " where last_run > 0\n"
                 . " order by last_run desc, id\n"
                 . " limit $limt";
        }
        else
        {
            $txt = 'Asset Report Queue';
            $sql = "select * from AssetReports\n"
                 . " where enabled = 1\n"
                 . " order by next_run, this_run desc, id\n"
                 . " limit $limt";
        }
        $set = find_many($sql,$db);
        report_info($env,$set,$txt,$past,1,$db);
    }



    function event_report(&$env,$past,$db)
    {
        echo mark('report');
        echo queue_tags($env);
        $now  = $env['now'];
        $limt = $env['limt'];
        if ($past)
        {
            $txt = 'Recent Event Reports';
            $sql = "select * from Reports\n"
                 . " where last_run > 0\n"
                 . " order by last_run desc, id\n"
                 . " limit $limt";
        }
        else
        {
            $txt = 'Event Report Queue';
            $sql = "select * from Reports\n"
                 . " where enabled = 1\n"
                 . " order by next_run, this_run desc, id\n"
                 . " limit $limt";
        }

        $set = find_many($sql,$db);
        report_info($env,$set,$txt,$past,0,$db);
    }


    function display_census(&$env,$db)
    {
        $now  = $env['now'];
        $limt = $env['limt'];

        $sql = "select * from TempCensusCache\n"
             . " order by last desc\n"
             . " limit $limt";
        $set = CORE_GetAllTempCensusCache($sql,$db);

        if ($set)
        {

            $head = explode('|','Age|Machine|Site|When|Id');

            echo table_header();
            echo pretty_header('Recent Census',6);
            echo table_data($head,1);
            reset($set);
            foreach ($set as $key => $row)
            {
                $id   = $row['id'];
                $site = disp($row,'site');
                $host = disp($row,'host');
                $last = $row['last'];
                $when = date("m/d H:i:s",$last);
                $age  = age($now - $last);
                $args = array($age,$host,$site,$when,$id);
                echo table_data($args,0);
            }
            echo table_footer();
        }
    }


    function display_config(&$env,$db)
    {
        $now  = $env['now'];
        $limt = $env['limt'];

        $sql = "select C.site, C.host,\n"
             . " C.uuid, R.* from\n"
             . " ".$GLOBALS['PREFIX']."core.Revisions as R,\n"
             . " ".$GLOBALS['PREFIX']."core.Census as C\n"
             . " where R.censusid = C.id\n"
             . " order by provisional desc, ctime desc, censusid\n"
             . " limit $limt";
        $set = find_many($sql,$db);

        if ($set)
        {
            $args = explode('|','Age|Machine|Site|Version|When|Id|Provisional');
            $cols = safe_count($args);
            $text = 'Recent Config Logs';

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($args,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $hid  = $row['censusid'];
                $host = disp($row,'host');
                $site = disp($row,'site');
                $vers = disp($row,'vers');
                $uuid = disp($row,'uuid');
                $last = $row['ctime'];
                $serv = $row['stime'];
                $prov = $row['provisional'];

                $when = showtime($last);
                $prov = showtime($prov);
                $age  = age($now - $last);
                $args = array($age,$host,$site,$vers,$when,$hid,$prov);
                echo table_data($args,0);
            }
            echo table_footer();
        }
    }



    function display_files(&$env,$db)
    {
        $now  = $env['now'];
        $limt = $env['limt'];

        $sql  = "select * from Files\n"
              . " order by created desc, name, id\n"
              . " limit $limt";
        $set  = find_many($sql,$db);

        if ($set)
        {

            $head = explode('|','Age|Name|Owner|Type|Count|Created|Expires|Id');
            $cols = safe_count($head);
            $text = 'Recent Files';
            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);
            reset($set);
            foreach ($set as $key => $row)
            {
                $fid  = $row['id'];
                $name = disp($row,'name');
                $user = disp($row,'username');
                $type = disp($row,'type');
                $recs = $row['counted'];
                $last = $row['created'];
                $expr = $row['expires'];

                $when = showtime($last);
                $expr = showtime($expr);
                $age  = age($now - $last);
                $args = array($age,$name,$user,$type,$recs,$when,$expr,$fid);
                echo table_data($args,0);
            }
            echo table_footer();
        }
    }



    function display_update(&$env,$db)
    {
        $now  = $env['now'];
        $limt = $env['limt'];
        $sql  = "select * from UpdateMachines\n"
              . " order by timecontact desc, id\n"
              . " limit $limt";
        $set  = find_many($sql,$db);

        if ($set)
        {
            $head = explode('|','Age|Machine|Site|When|Version|Old|New|Id');
            $cols = safe_count($head);
            $text = 'Recent Update Logs';

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $mid  = $row['id'];
                $last = $row['timecontact'];
                $host = disp($row,'machine');
                $site = disp($row,'sitename');
                $vers = disp($row,'lastversion');
                $old  = disp($row,'oldversion');
                $new  = disp($row,'newversion');

                $when = showtime($last);
                $age  = age($now - $last);
                $args = array($age,$host,$site,$when,$vers,$old,$new,$mid);
                echo table_data($args,0);
            }
            echo table_footer();
        }
    }



    function display_asset(&$env,$db)
    {
        $now  = $env['now'];
        $limt = $env['limt'];
        $sql  = "select * from Machine\n"
              . " order by provisional desc, slatest desc, machineid\n"
              . " limit $limt";
        $set  = find_many($sql,$db);

        if ($set)
        {
            $head = explode('|','Age|Machine|Site|When|Id|Provisional');
            $cols = safe_count($head);
            $text = 'Recent Asset Logs';
            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);
            reset($set);
            foreach ($set as $key => $row)
            {
                $mid  = $row['machineid'];
                $host = disp($row,'host');
                $site = disp($row,'cust');
                $last = $row['slatest'];
                $prov = $row['provisional'];

                $when = showtime($last);
                $prov = showtime($prov);
                $age  = age($now - $last);
                $args = array($age,$host,$site,$when,$mid,$prov);
                echo table_data($args,0);
            }
            echo table_footer();
        }
    }



    function display_status(&$env,$db)
    {
        echo again($env);
        display_census($env,$db);
        if (mysqli_select_db($db, event))
        {
            display_events($env,$db);
        }
        if (mysqli_select_db($db, core))
        {
            display_config($env,$db);
        }
        if (mysqli_select_db($db, asset))
        {
            display_asset($env,$db);
        }
        if (mysqli_select_db($db, swupdate))
        {
            display_update($env,$db);
        }
        if (mysqli_select_db($db, provision))
        {
            display_meter($env,$db);
            display_audit($env,$db);
        }
        echo again($env);
    }


    function event_stats(&$env,$db)
    {
        $now  = $env['now'];
        $min  = 'select min(idx) from Events';
        $max  = 'select max(idx) from Events';
        $cnt  = 'select count(*) from Events';
        $ecnt = find_scalar($cnt,$db);
        if ($ecnt)
        {
            $emin = find_scalar($min,$db);
            $emax = find_scalar($max,$db);
            $find = 'select * from Events where idx =';
            $min  = find_one("$find $emin",$db);
            $max  = find_one("$find $emax",$db);
            if (($min) && ($max))
            {
                $sp   = '&nbsp;';
                $days = server_def('purge_days',90,$db);
                $tmin = $min['servertime'];
                $tmax = $max['servertime'];
                $dmin = fulltime($tmin);
                $dmax = fulltime($tmax);
                $amin = age($now - $tmin);
                $amax = age($now - $tmax);
                $rnge = $emax - $emin;
                $rnge = "$rnge ($emin..$emax)";
                $time = $tmax - $tmin;

                echo table_header();
                echo pretty_header('Event Statistics',2);
                echo double( 'Total:', "$ecnt events");
                echo double( 'Range:', $rnge);
                echo double('Newest:', "$dmax $sp ($amax)");
                echo double('Oldest:', "$dmin $sp ($amin)");
                if ($time > 86400)
                {
                    $dtim = $time / 86400;
                    $htim = $time / 3600;
                    $mtim = $time / 60;

                    $davg = intval(round($ecnt / $dtim));
                    $havg = intval(round($ecnt / $htim));
                    $mavg = intval(round($ecnt / $mtim));
                    echo double('Averages:', "Day:$davg, Hour:$havg, Minute:$mavg");
                }
                $past = intval($now / 86400);
                if ((0 < $days) && ($days < $past))
                {
                    $expr = $now - ($days * 86400);
                    $time = abs($expr - $tmin);

                    $dexp = fulltime($expr);
                    $dage = age($time);
                    echo double('Expire:', "$dexp $sp ($dage)");
                    echo double( 'Purge:', "$days days $sp (purge_days)");
                }
                echo table_footer();
            }
        }
    }


    function meter_stats(&$env,$db)
    {
        $now  = $env['now'];
        $min  = 'select min(meterid) from Meter';
        $max  = 'select max(meterid) from Meter';
        $cnt  = 'select count(*) from Meter';
        $ecnt = find_scalar($cnt,$db);
        if ($ecnt)
        {
            $emin = find_scalar($min,$db);
            $emax = find_scalar($max,$db);
            $find = 'select * from Meter where meterid =';
            $min  = find_one("$find $emin",$db);
            $max  = find_one("$find $emax",$db);
            if (($min) && ($max))
            {
                $sp   = '&nbsp;';
                $days = server_def('meter_days',60,$db);
                $tmin = $min['servertime'];
                $tmax = $max['servertime'];
                $hmin = $min['machine'];
                $hmax = $max['machine'];
                $smin = $min['sitename'];
                $smax = $max['sitename'];
                $dmin = fulltime($tmin);
                $dmax = fulltime($tmax);
                $amin = age($now - $tmin);
                $amax = age($now - $tmax);
                $rnge = $emax - $emin;
                $rnge = "$rnge ($emin..$emax)";

                $xmin = "$hmin at $smin ($dmin)";
                $xmax = "$hmax at $smax ($dmax)";

                echo table_header();
                echo pretty_header('Meter Statistics',2);
                echo double( 'Total:', "$ecnt events");
                echo double( 'Range:', $rnge);
                echo double('Newest:', "$dmax $sp ($amax)");
                echo double('Oldest:', "$dmin $sp ($amin)");
                echo double('Newest:',$xmax);
                echo double('Oldest:',$xmin);
                $past = intval($now / 86400);
                if ((0 < $days) && ($days < $past))
                {
                    $expr = $now - ($days * 86400);
                    $time = abs($expr - $tmin);

                    $dexp = fulltime($expr);
                    $dage = age($time);
                    echo double('Expire:', "$dexp $sp ($dage)");
                    echo double( 'Purge:', "$days days $sp (meter_days)");
                }
                echo table_footer();
            }
        }
    }


    function audit_stats(&$env,$db)
    {
        $now  = $env['now'];
        $min  = 'select min(auditid) from Audit';
        $max  = 'select max(auditid) from Audit';
        $cnt  = 'select count(*) from Audit';
        $ecnt = find_scalar($cnt,$db);
        if ($ecnt)
        {
            $emin = find_scalar($min,$db);
            $emax = find_scalar($max,$db);
            $find = 'select * from Audit where auditid =';
            $min  = find_one("$find $emin",$db);
            $max  = find_one("$find $emax",$db);
            if (($min) && ($max))
            {
                $sp   = '&nbsp;';
                $days = server_def('audit_days',60,$db);
                $tmin = $min['servertime'];
                $tmax = $max['servertime'];
                $hmin = $min['machine'];
                $hmax = $max['machine'];
                $smin = $min['sitename'];
                $smax = $max['sitename'];
                $dmin = fulltime($tmin);
                $dmax = fulltime($tmax);
                $amin = age($now - $tmin);
                $amax = age($now - $tmax);
                $rnge = $emax - $emin;
                $rnge = "$rnge ($emin..$emax)";

                $xmin = "$hmin at $smin ($dmin)";
                $xmax = "$hmax at $smax ($dmax)";

                echo table_header();
                echo pretty_header('Audit Statistics',2);
                echo double( 'Total:', "$ecnt events");
                echo double('Newest:', "$dmax $sp ($amax)");
                echo double('Oldest:', "$dmin $sp ($amin)");
                echo double('Newest:',$xmax);
                echo double('Oldest:',$xmin);
                $past = intval($now / 86400);
                if ((0 < $days) && ($days < $past))
                {
                    $expr = $now - ($days * 86400);
                    $time = abs($expr - $tmin);

                    $dexp = fulltime($expr);
                    $dage = age($time);
                    echo double('Expire:', "$dexp $sp ($dage)");
                    echo double( 'Purge:', "$days days $sp (audit_days)");
                }
                echo table_footer();
            }
        }
    }


    function asset_stats(&$env,$db)
    {
        $now  = $env['now'];
        $cnt  = 'select count(*) from Machine';
        $acnt = find_scalar($cnt,$db);
        if ($acnt)
        {
            $find = 'select * from Machine order by';
            $min  = "$find slatest, machineid limit 1";
            $max  = "$find slatest desc, machineid limit 1";
            $min  = find_one($min,$db);
            $max  = find_one($max,$db);
            if (($min) && ($max))
            {
                $sp   = '&nbsp;';
                $tmin = $min['slatest'];
                $tmax = $max['slatest'];
                $hmin = $min['host'];
                $hmax = $max['host'];
                $smin = $min['cust'];
                $smax = $max['cust'];
                $dmin = fulltime($tmin);
                $dmax = fulltime($tmax);
                $amin = age($now - $tmin);
                $amax = age($now - $tmax);

                $xmin = "$hmin at $smin";
                $xmax = "$hmax at $smax";
                $days = server_def('asset_days',120,$db);

                echo table_header();
                echo pretty_header('Asset Statistics',2);
                echo double( 'Total:', "$acnt machines");
                echo double('Newest:',$xmax);
                echo double('Oldest:',$xmin);
                echo double('Newest:', "$dmax $sp ($amax)");
                echo double('Oldest:', "$dmin $sp ($amin)");
                $past = intval($now / 86400);
                if ((0 < $days) && ($days < $past))
                {
                    $expr = $now - ($days * 86400);
                    $time = abs($expr - $tmin);

                    $dexp = fulltime($expr);
                    $dage = age($time);
                    echo double('Expire:', "$dexp $sp ($dage)");
                    echo double( 'Purge:', "$days days $sp (asset_days)");
                }

                echo table_footer();
            }
        }
    }


    function config_stats(&$env,$db)
    {
        $now  = $env['now'];
        $cnt  = 'select count(*) from Revisions';
        $mcnt = find_scalar($cnt,$db);
        if ($mcnt)
        {
            $find = "select C.host, C.site, R.* from\n"
                  . " ".$GLOBALS['PREFIX']."core.Revisions as R,\n"
                  . " ".$GLOBALS['PREFIX']."core.Census as C\n"
                  . " where C.id = R.censusid\n"
                  . " order by";
            $min  = "$find ctime, censusid\n limit 1";
            $max  = "$find ctime desc, censusid\n limit 1";
            $min  = find_one($min,$db);
            $max  = find_one($max,$db);
            if (($min) && ($max))
            {
                $sp   = '&nbsp;';
                $days = server_def('config_days',40,$db);
                $tmin = $min['ctime'];
                $tmax = $max['ctime'];
                $hmin = $min['host'];
                $hmax = $max['host'];
                $smin = $min['site'];
                $smax = $max['site'];
                $vmax = $max['vers'];
                $vmin = $min['vers'];
                $dmin = fulltime($tmin);
                $dmax = fulltime($tmax);
                $amin = age($now - $tmin);
                $amax = age($now - $tmax);

                $xmin = "$hmin at $smin ($vmin)";
                $xmax = "$hmax at $smax ($vmax)";

                echo table_header();
                echo pretty_header('Config Statistics',2);
                echo double( 'Total:', "$mcnt machines");
                echo double('Newest:', $xmax);
                echo double('Oldest:', $xmin);
                echo double('Newest:', "$dmax $sp ($amax)");
                echo double('Oldest:', "$dmin $sp ($amin)");
                $past = intval($now / 86400);
                if ((0 < $days) && ($days < $past))
                {
                    $expr = $now - ($days * 86400);
                    $time = abs($expr - $tmin);

                    $dexp = fulltime($expr);
                    $dage = age($time);
                    echo double('Expire:', "$dexp $sp ($dage)");
                    echo double( 'Purge:', "$days days $sp (config_days)");
                }
                echo table_footer();
            }
        }
    }


    function file_stats(&$env,$db)
    {
        $now  = $env['now'];
        $cnt  = 'select count(*) from Files';
        $fcnt = find_scalar($cnt,$db);
        if ($fcnt)
        {
            $find = 'select * from Files order by';
            $min  = "$find created, id limit 1";
            $max  = "$find created desc, id limit 1";
            $min  = find_one($min,$db);
            $max  = find_one($max,$db);
            if (($min) && ($max))
            {
                $sp   = '&nbsp;';
                $tmin = $min['created'];
                $tmax = $max['created'];
                $umin = $min['username'];
                $umax = $max['username'];
                $nmin = $min['name'];
                $nmax = $max['name'];
                $dmin = fulltime($tmin);
                $dmax = fulltime($tmax);
                $amin = age($now - $tmin);
                $amax = age($now - $tmax);

                echo table_header();
                echo pretty_header('File Statistics',2);
                echo double( 'Total:', "$fcnt files");
                echo double('Newest:', "$dmax $sp ($amax)");
                echo double('Oldest:', "$dmin $sp ($amin)");
                echo table_footer();
            }
        }
    }

    function console_stats(&$env,$db)
    {
        $now  = $env['now'];
        $cnt  = 'select count(*) from Console';
        $fcnt = find_scalar($cnt,$db);
        if ($fcnt)
        {
            $find = 'select * from Console order by';
            $min  = "$find servertime, id limit 1";
            $max  = "$find servertime desc, id limit 1";
            $min  = find_one($min,$db);
            $max  = find_one($max,$db);
            if (($min) && ($max))
            {
                $sp   = '&nbsp;';
                $tmin = $min['servertime'];
                $tmax = $max['servertime'];
                $umin = $min['username'];
                $umax = $max['username'];
                $nmin = $min['name'];
                $nmax = $max['name'];
                $dmin = fulltime($tmin);
                $dmax = fulltime($tmax);
                $amin = age($now - $tmin);
                $amax = age($now - $tmax);

                echo table_header();
                echo pretty_header('Console Statistics',2);
                echo double( 'Total:', "$fcnt records");
                echo double('Newest:', "$dmax $sp ($amax)");
                echo double('Oldest:', "$dmin $sp ($amin)");
                echo table_footer();
            }
        }
    }

    function update_stats(&$env,$db)
    {
        $now  = $env['now'];
        $cnt  = 'select count(*) from UpdateMachines';
        $mcnt = find_scalar($cnt,$db);
        if ($mcnt)
        {
            $find = 'select * from UpdateMachines order by';
            $min  = "$find timecontact, id limit 1";
            $max  = "$find timecontact desc, id limit 1";
            $min  = find_one($min,$db);
            $max  = find_one($max,$db);
            if (($min) && ($max))
            {
                $sp   = '&nbsp;';
                $tmin = $min['timecontact'];
                $tmax = $max['timecontact'];
                $hmin = $min['machine'];
                $hmax = $max['machine'];
                $smin = $min['sitename'];
                $smax = $max['sitename'];
                $vmax = $max['lastversion'];
                $vmin = $min['lastversion'];
                $dmin = fulltime($tmin);
                $dmax = fulltime($tmax);
                $amin = age($now - $tmin);
                $amax = age($now - $tmax);
                $xmin = "$hmin at $smin ($vmin)";
                $xmax = "$hmax at $smax ($vmax)";
                $days = server_def('update_days',40,$db);

                echo table_header();
                echo pretty_header('Update Statistics',2);
                echo double( 'Total:', "$mcnt machines");
                echo double('Newest:', $xmax);
                echo double('Oldest:', $xmin);
                echo double('Newest:', "$dmax $sp ($amax)");
                echo double('Oldest:', "$dmin $sp ($amin)");
                $past = intval($now / 86400);
                if ((0 < $days) && ($days < $past))
                {
                    $expr = $now - ($days * 86400);
                    $time = abs($expr - $tmin);

                    $dexp = fulltime($expr);
                    $dage = age($time);
                    echo double('Expire:', "$dexp $sp ($dage)");
                    echo double( 'Purge:', "$days days $sp (update_days)");
                }
                echo table_footer();
            }
        }
    }


    function census_stats(&$env,$db)
    {
        $now  = $env['now'];
        $cnt  = 'select count(*) from Census';
        $mcnt = find_scalar($cnt,$db);
        $cnt  = 'select count(distinct site) from Census';
        $scnt = find_scalar($cnt,$db);
        if ($mcnt)
        {
            $find = 'select * from TempCensusCache order by';
            $min  = "$find last, id limit 1";
            $max  = "$find last desc, id limit 1";
            $min  = CORE_GetOneTempCensusCache($min,$db);
            $max  = CORE_GetOneTempCensusCache($max,$db);
            if (($min) && ($max))
            {
                $sp   = '&nbsp;';
                $tmin = $min['last'];
                $tmax = $max['last'];
                $hmin = $min['host'];
                $hmax = $max['host'];
                $smin = $min['site'];
                $smax = $max['site'];

                $dmin = fulltime($tmin);
                $dmax = fulltime($tmax);
                $amin = age($now - $tmin);
                $amax = age($now - $tmax);
                $xmin = "$hmin at $smin";
                $xmax = "$hmax at $smax";
                $past = intval($now / 86400);
                $days = server_def('census_days',100,$db);

                echo table_header();
                echo pretty_header('Census Statistics',2);
                echo double('Machines:', "$mcnt machines");
                echo double(   'Sites:', "$scnt sites");
                echo double(  'Newest:',  $xmax);
                echo double(  'Oldest:',  $xmin);
                echo double(  'Newest:', "$dmax $sp ($amax)");
                echo double(  'Oldest:', "$dmin $sp ($amin)");
                if ((0 < $days) && ($days < $past))
                {
                    $expr = $now - ($days * 86400);
                    $time = abs($expr - $tmin);

                    $dexp = fulltime($expr);
                    $dage = age($time);
                    echo double('Expire:', "$dexp $sp ($dage)");
                    echo double( 'Purge:', "$days days $sp (census_days)");
                }
                echo table_footer();
            }
        }
    }



    function event_range($tmin,$tmax,$db)
    {
        $sql = "select count(*) from Events\n"
             . " where servertime between $tmin and $tmax";
        return find_scalar($sql,$db);
    }


    function event_day(&$env,$db)
    {
        echo again($env);
        if (mysqli_select_db($db, event))
        {
            event_stats($env,$db);
            $hh   = 26;
            $now  = $env['now'];
            $time = array( );
            $evnt = array( );
            $when = $now - ($now % 3600);
            for ($i = 0; $i <= $hh; $i++)
            {
                $time[$i] = $when - ($i * 3600);
                $evnt[$i] = 0;
            }
            for ($i = 1; $i <= $hh; $i++)
            {
                $tmin = $time[$i];
                $tmax = $time[$i-1];
                $evnt[$i] = event_range($tmin,$tmax,$db);
            }
            echo table_header();
            echo pretty_header('Hourly Event Summary',2);
            if ($now > $when)
            {
                $new = event_range($when,$now,$db);
                if ($new > 0)
                {
                    $min = date('H:i',$when);
                    $max = date('H:i:s',$now);
                    $text = "$min .. $max";
                    echo double($text, $new);
                }
            }

            for ($i = 1; $i <= $hh; $i++)
            {
                $tmax = date('m/d/Y H:i',$time[$i]);
                $text = sprintf('%s (%02d)',$tmax,$i);
                echo double($text, $evnt[$i]);
            }
            echo table_footer();
        }
        echo again($env);
    }


    function event_month(&$env,$db)
    {
        echo again($env);
        if (mysqli_select_db($db, event))
        {
            event_stats($env,$db);
            $hh   = 32;
            $now  = $env['now'];
            $time = array( );
            $evnt = array( );
            $when = midnight($now);
            for ($i = 0; $i <= $hh; $i++)
            {
                $time[$i] = days_ago($when,$i);
                $evnt[$i] = 0;
            }
            for ($i = 1; $i <= $hh; $i++)
            {
                $tmin = $time[$i];
                $tmax = $time[$i-1];
                $evnt[$i] = event_range($tmin,$tmax,$db);
            }
            echo table_header();
            echo pretty_header('Daily Event Summary',2);
            if ($now > $when)
            {
                $new = event_range($when,$now,$db);
                if ($new > 0)
                {
                    $max = date('H:i:s',$now);
                    $text = "As of $max";
                    echo double($text, $new);
                }
            }

            for ($i = 1; $i <= $hh; $i++)
            {
                $date = date('l m/d',$time[$i]);
                echo double($date, $evnt[$i]);
            }
            echo table_footer();
        }
        echo again($env);
    }


    function event_info(&$env,$db)
    {
        echo again($env);
        if (mysqli_select_db($db, core))
        {
            census_stats($env,$db);
        }
        if (mysqli_select_db($db, event))
        {
            event_stats($env,$db);
            display_events($env,$db);
            display_console($env,$db);
        }
        echo again($env);
    }


    function categorize($field,$min,$max,$head,$db)
    {
        $sql = "select count(*) as num,\n"
             . " $field as data\n"
             . " from Events\n"
             . " where servertime between $min and $max\n"
             . " group by data\n"
             . " order by num desc, data";
        $set = find_many($sql,$db);
        if ($set)
        {
            echo table_header();
            echo pretty_header('Categorize Events',2);
            echo double('Min',showtime($min));
            echo double('Max',showtime($max));
            echo double('Categories',count($set));
            echo double('Field',$field);
            echo table_footer();

            echo table_header();
            echo pretty_header($head,2);
            foreach ($set as $key => $row)
            {
                $num  = $row['num'];
                $data = disp($row,'data');
                echo double($num,$data);
            }
            echo table_footer();
        }
    }



    function event_cats(&$env,$db)
    {
        echo again($env);
        if (mysqli_select_db($db, event))
        {
            echo timetags($env);
            $day  = $env['day'];
            $now  = $env['now'];
            $fld  = field($env['fld']);
            $when = days_ago($now,$day);
            $midn = midnight($when);
            if ($midn < $when)
            {
                $min = $midn;
                $tmp = $midn + (26 * 3600);
                $max = midnight($tmp);
            }
            else
            {
                $max = $midn;
                $min = midnight($max-1);
            }
            $head = date('D, F jS, Y',$min);
            categorize($fld,$min,$max,$head,$db);
            echo timetags($env);
        }
        echo again($env);
    }

    function asset_info(&$env,$db)
    {
        if (mysqli_select_db($db, asset))
        {
            echo again($env);
            asset_stats($env,$db);
            display_asset($env,$db);
            echo again($env);
        }
    }

    function meter_info(&$env,$db)
    {
        if (mysqli_select_db($db, provision))
        {
            echo again($env);
            meter_stats($env,$db);
            display_meter($env,$db);
            echo again($env);
        }
    }

    function console_info(&$env,$db)
    {
        if (mysqli_select_db($db, event))
        {
            echo again($env);
            console_stats($env,$db);
            display_console($env,$db);
            echo again($env);
        }
    }

    function audit_info(&$env,$db)
    {
        if (mysqli_select_db($db, provision))
        {
            echo again($env);
            audit_stats($env,$db);
            display_audit($env,$db);
            echo again($env);
        }
    }

    function config_info(&$env,$db)
    {
        if (mysqli_select_db($db, core))
        {
            echo again($env);
            config_stats($env,$db);
            display_config($env,$db);
            echo again($env);
        }
    }

    function file_info(&$env,$db)
    {
        echo again($env);
        file_stats($env,$db);
        display_files($env,$db);
        echo again($env);
    }

    function update_info(&$env,$db)
    {
        if (mysqli_select_db($db, swupdate))
        {
            echo again($env);
            update_stats($env,$db);
            display_update($env,$db);
            echo again($env);
        }
    }


    function queue_info(&$env,$past,$db)
    {
        echo again($env);
        if (mysqli_select_db($db, event))
        {
            display_notify($env,$past,$db);
            event_report($env,$past,$db);
        }
        if (mysqli_select_db($db, asset))
        {
            asset_report($env,$past,$db);
        }
        echo again($env);
    }



    function active_info(&$env,$db)
    {
        echo again($env);

        CORE_CreateTempCensusCache($db);
        $sql = "select U.username,\n"
             . " count(distinct H.id) as hnum,\n"
             . " count(distinct A.id) as anum,\n"
             . " count(distinct S.id) as snum,\n"
             . " max(H.last) as time from\n"
             . " Users as U,\n"
             . " Customers as S,\n"
             . " Customers as A,\n"
             . " TempCensusCache as H\n"
             . " where A.customer = H.site\n"
             . " and U.username = A.username\n"
             . " and U.username = S.username\n"
             . " group by U.username\n"
             . " order by U.username";
        $set = find_many($sql,$db);
        command('DROP TABLE TempCensusCache', $db);
        if ($set)
        {
            $head = explode('|','User|Total Sites|Active Sites|Machines|Last|Age');
            $cols = safe_count($head);
            $rows = safe_count($set);
            $text = "Active Users &nbsp; ($rows found)";
            $now  = time();

            echo table_header();
            echo pretty_header($text,$cols);
            echo table_data($head,1);

            reset($set);
            foreach ($set as $key => $row)
            {
                $user = $row['username'];
                $anum = $row['anum'];
                $snum = $row['snum'];
                $hnum = $row['hnum'];
                $time = $row['time'];
                $secs = ($time <= $now)? $now - $time : 0;
                $when = showtime($time);
                $args = array($user,$snum,$anum,$hnum,$when,age($secs));
                echo table_data($args,0);
            }
            echo table_footer();
        }
        else
        {
            echo "<p>No active users found</p>\n";
        }
        echo again($env);
    }


    function show_size($size)
    {
        $kb = 1024;
        $mb = $kb * $kb;
        $gb = $kb * $mb;
        $k  = round($size / $kb);
        $m  = round($size / $mb);

        if ($size <= 10240)
        {
            $t = "$size bytes";
        }
        if ((10240 < $size) && ($size <= $mb))
        {
            $t = $k . 'k';
        }
        if (($mb < $size) && ($size <= $gb))
        {
            $x = intval(round($size / ($mb / 10)));
            $m = intval($x / 10);
            $d = intval($x % 10);
            $t = sprintf('%d.%dM',$m,$d);
        }
        if ($size > $gb)
        {
            $x = intval(round($size / ($gb / 10)));
            $g = intval($x / 10);
            $d = intval($x % 10);
            $t = sprintf('%d.%dG',$g,$d);
        }
        return $t;
    }


    function percent($part,$whole)
    {
        $pm = round(($part*1000) / $whole);
        $pc = intval($pm / 10);
        $pd = intval($pm % 10);
        return sprintf('%d.%d%%',$pc,$pd);
    }


    function disk_info(&$env,$db)
    {
        echo again($env);
        $free = disk_free_space('/var/lib/mysql');
        $size = disk_total_space('/var/lib/mysql');
        if ((0 < $size) && ($free < $size))
        {
            $used = $size - $free;

            $ds = show_size($size);
            $df = show_size($free);
            $du = show_size($used);
            $pu = percent($used,$size);
            $pf = percent($free,$size);
            $sp = '&nbsp;';

            echo table_header();
            echo pretty_header('Disk Space',2);
            echo double( 'Total:', $ds);
            echo double(  'Used:', "$du $sp ($pu)");
            echo double(  'Free:', "$df $sp ($pf)");

            if ($env['priv'])
            {
                echo double( '<br>', $sp);
                echo double( 'Raw Total:', $size);
                echo double(  'Raw Used:', $used);
                echo double(  'Raw Free:', $free);
            }
            echo table_footer();
        }
        echo again($env);
    }


    function postpone(&$env,$db)
    {
        $now  = $env['now'];
        $secs = $env['secs'];
        echo again($env);
        if ($secs > 0)
        {
            $evr  = 0;
            $evn  = 0;
            $asr  = 0;
            $when = $now + $secs;
            $date = date('m/d H:i:s',$when);
            $age  = age($secs);
            echo "<p>Postpone queue for ($age) until $date.</p>\n";
            if (mysqli_select_db($db, event))
            {
                $sql = "update Reports set\n"
                     . " next_run = $when\n"
                     . " where next_run > 0\n"
                     . " and next_run < $when\n"
                     . " and enabled = 1";
                $res = redcommand($sql,$db);
                $evr = affected($res,$db);
                $sql = "update Notifications set\n"
                     . " next_run = $when\n"
                     . " where next_run > 0\n"
                     . " and next_run < $when\n"
                     . " and enabled = 1";
                $res = redcommand($sql,$db);
                $evn = affected($res,$db);
            }
            if (mysqli_select_db($db, asset))
            {
                $sql = "update AssetReports set\n"
                     . " next_run = $when\n"
                     . " where next_run > 0\n"
                     . " and next_run < $when\n"
                     . " and enabled = 1";
                $res = redcommand($sql,$db);
                $asr = affected($res,$db);
            }
            if ($evr)
            {
                echo "<p>Postponed $evr event reports until $date</p>\n";
            }
            if ($asr)
            {
                echo "<p>Postponed $asr asset reports until $date</p>\n";
            }
            if ($evn)
            {
                echo "<p>Postponed $evn event notifications until $date</p>\n";
            }
            if ($evr + $asr + $evn <= 0)
            {
                echo "<p>Nothing has changed.</p>\n";
            }
        }
        queue_info($env,0,$db);
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


    function reset_clock($tab,$db)
    {
        $now = time();
        $sql = "update $tab set\n"
             . " this_run = 0,\n"
             . " next_run = 0\n"
             . " where next_run < 0\n"
             . " or this_run > 0";
        $res = redcommand($sql,$db);
        $dis = affected($res,$db);
        $sql = "update $tab set\n"
             . " next_run = 0\n"
             . " where enabled = 1\n"
             . " and next_run > 0";
        $res = redcommand($sql,$db);
        $foo = affected($res,$db);
        $sql = "update $tab set\n"
             . " next_run = 0,\n"
             . " last_run = $now\n"
             . " where last_run > $now\n"
             . " and enabled = 1";
        $res = redcommand($sql,$db);
        $xxx = affected($res,$db);
        return $dis + $foo + $xxx;
    }



   /*
    |  This should only happen when you are sure that
    |  all running cron jobs are really dead, and the
    |  crontab itself is disabled.
    |
    |  For example, if something has gone wrong with
    |  mysql and you have just restarted the server.
    |
    |  This allows you to quickly restore the queue
    |  to a reasonable state.
    */

    function master_reset(&$env,$db)
    {
        echo again($env);
        $now  = $env['now'];
        $evr  = 0;
        $evn  = 0;
        $asr  = 0;
        $past = 600;
        $when = $now - $past;
        $date = date('m/d H:i:s',$when);
        $age  = age($past);
        echo "<p>Reset queue for $date, $age.</p>\n";
        if (mysqli_select_db($db, event))
        {
            $evr = reset_table('Reports',$when,$db);
            $evn = reset_table('Notifications',$when,$db);
        }
        if (mysqli_select_db($db, asset))
        {
            $asr = reset_table('AssetReports',$when,$db);
        }
        if ($evr)
        {
            echo "<p>Cleared $evr pending event reports.</p>\n";
        }
        if ($asr)
        {
            echo "<p>Cleared $asr pending asset reports.</p>\n";
        }
        if ($evn)
        {
            echo "<p>Cleared $evn pending notifications.</p>\n";
        }
        if ($evr + $asr + $evn <= 0)
        {
            echo "<p>Nothing has changed.</p>\n";
        }

        update_opt('notify_pid', 0,$db);
        update_opt('report_pid', 0,$db);
        update_opt('asset_pid',  0,$db);
        update_opt('purge_pid',  0,$db);
        update_opt('notify_lock',0,$db);
        update_opt('report_lock',0,$db);
        update_opt('asset_lock', 0,$db);
        update_opt('purge_lock', 0,$db);
        queue_info($env,0,$db);
    }



    function kill_queue(&$env,$db)
    {
        echo again($env);
        if (mysqli_select_db($db, event))
        {
            $sql = "update Reports set\n"
                 . " enabled = 0,\n"
                 . " next_run = 0,\n"
                 . " this_run = 0";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
            echo "<p>$num event reports cleared.</p>\n";
            $sql = "update Notifications set\n"
                 . " enabled = 0,\n"
                 . " next_run = 0,\n"
                 . " this_run = 0";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
            echo "<p>$num notifications cleared.</p>\n";
        }
        if (mysqli_select_db($db, asset))
        {
            $sql = "update AssetReports set\n"
                 . " enabled = 0,\n"
                 . " next_run = 0,\n"
                 . " this_run = 0";
            $res = redcommand($sql,$db);
            $num = affected($res,$db);
            echo "<p>$num asset reports cleared.</p>\n";
        }
        echo again($env);
    }



    function clock_reset(&$env,$db)
    {
        if (mysqli_select_db($db, event))
        {
            $evr = reset_clock('Reports',$db);
            $evn = reset_clock('Notifications',$db);
        }
        if (mysqli_select_db($db, asset))
        {
            $asr = reset_clock('AssetReports',$db);
        }
        if ($evr)
        {
            echo "<p>Reset $evr event reports.</p>\n";
        }
        if ($asr)
        {
            echo "<p>Reset $asr asset reports.</p>\n";
        }
        if ($evn)
        {
            echo "<p>Reset $evn notifications.</p>\n";
        }
        if ($evr + $asr + $evn <= 0)
        {
            echo "<p>Nothing has changed.</p>\n";
        }
        queue_info($env,0,$db);
    }

    function noprivs(&$env,$db)
    {
        $msg = "This page requires administrative access.";
        $msg = fontspeak($msg);
        echo "<br><p>$msg</p><br>\n";
    }


    function listcmd(&$env,$cmd,$dbug,$text)
    {
        $self = $env['self'];
        $href = "$self?act=$cmd&debug=$dbug";
        $link = html_link($href,$text);
        return "<li>$link</li>\n";
    }


    function status_menu(&$env,$db)
    {
        echo again($env);
        echo "<br><ul>\n";
        echo listcmd($env,'evnt',0,'event status');
        echo listcmd($env,'asst',0,'asset status');
        echo listcmd($env,'cnfg',0,'config status');
        echo listcmd($env,'updt',0,'update status');
        echo listcmd($env,'metr',0,'meter status');
        echo listcmd($env,'audt',0,'audit status');
        echo listcmd($env,'user',0,'active users');
        echo listcmd($env,'cnsl',0,'notify console status');
        echo listcmd($env,'file',0,'file status');
        echo listcmd($env,'logs',0,'all logging');
        echo listcmd($env,'disk',0,'disk space');
        echo listcmd($env,'queu',0,'run queue status');
        echo listcmd($env,'past',0,'run queue history');
        echo listcmd($env,'eday',0,'hourly event summary');
        echo listcmd($env,'mnth',0,'daily event summary');
        echo listcmd($env,'cats',0,'categorize events');
        if ($env['priv'])
        {
            echo listcmd($env,'lock',1,'fake purge lock');
            echo listcmd($env,'pick',1,'clear purge lock');
            echo listcmd($env,'post',1,'run queue: postpone impending');
            echo listcmd($env,'msrt',1,'run queue: total master reset');
            echo listcmd($env,'clok',1,'run queue: recalculate all future');
            echo listcmd($env,'kill',1,'run queue: disable all');
        }
        echo "</ul><br>\n";
        echo again($env);
    }


    function server_lock(&$env,$db)
    {
        echo again($env);
        $pid  = $env['pid'];
        $auth = $env['auth'];
        update_opt('purge_pid',$pid,$db);
        update_opt('purge_lock',1,$db);
        $text = "purge lock set by $auth for $pid";
        logs::log(__FILE__, __LINE__, $text,0);
        echo "<p>$text</p>\n";
        echo again($env);
    }


    function server_pick(&$env,$db)
    {
        echo again($env);
        $auth = $env['auth'];
        update_opt('purge_pid',0,$db);
        update_opt('purge_lock',0,$db);
        $text = "purge lock cleared by $auth";
        logs::log(__FILE__, __LINE__, $text,0);
        echo "<p>$text</p>\n";
        echo again($env);
    }


    function unknown($env,$db)
    {
        echo again($env);
        $act = $env['act'];
        $msg = "Unknown action ($act)";
        $msg = fontspeak($msg);
        echo "<br><p>$msg</p><br><br>\n";
        echo again($env);
    }

    function matchOld($act , $txt)
    {
        $tmp = "|$act|";
        return strpos($txt , $tmp);
    }


   /*
    |  Main program
    */

    $db = db_connect();
    $auth = process_login($db);
    $comp = component_installed();
    $act  = get_string('act','evnt');
    $title = title($act);

    $txt = '';
    $tmp = get_integer('refresh',0);
    if ($tmp > 9)
    {
        $txt = "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n"
             . "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"$tmp\">\n";
    }

    $refresh     = $tmp;
    $refreshtime = $txt;  // spooky global

    $msg = ob_get_contents();           // save the buffered output so we can...
    ob_end_clean();                     // (now dump the buffer)
    echo standard_html_header($title,$comp,$auth,'','',0,$db);

    $date = datestring(time());

    echo "<h2>$date</h2>";

    $ord  = get_integer('ord',0);
    $dbg  = get_integer('debug',0);
    $secs = get_integer('secs',300);
    $limt = get_integer('limit',10);
    $user = user_data($auth,$db);
    $priv  = @ ($user['priv_debug'])?   1  : 0;
    $debug = @ ($user['priv_debug'])? $dbg : 0;
    $admin = @ ($user['priv_admin'])?   1  : 0;

    if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

    $env = array();
    $env['now']  = time();
    $env['act']  = $act;
    $env['day']  = get_integer('day',0);
    $env['fld']  = get_integer('fld',0);
    $env['pid']  = getmypid();
    $env['self'] = server_var('PHP_SELF');
    $env['args'] = server_var('QUERY_STRING');
    $env['limt'] = value_range(3,500,$limt);
    $env['secs'] = value_range(1,86400,$secs);
    $env['priv'] = $priv;
    $env['auth'] = $auth;
    $env['user'] = $user;
    $env['refr'] = $refresh;
    if (!$admin)
    {
        $act = 'priv';
    }

    if (!$priv)
    {
        $txt = '|||msrt|post|clok|kill|pick|lock|';
        if (matchOld($act,$txt))
        {
            $act = 'menu';
        }
    }

    switch ($act)
    {
        case 'logs': display_status($env,$db); break;
        case 'priv': noprivs($env,$db);        break;
        case 'evnt': event_info($env,$db);     break;
        case 'asst': asset_info($env,$db);     break;
        case 'cnfg': config_info($env,$db);    break;
        case 'cnsl': console_info($env,$db);   break;
        case 'file': file_info($env,$db);      break;
        case 'eday': event_day($env,$db);      break;
        case 'updt': update_info($env,$db);    break;
        case 'metr': meter_info($env,$db);     break;
        case 'audt': audit_info($env,$db);     break;
        case 'queu': queue_info($env,0,$db);   break;
        case 'past': queue_info($env,1,$db);   break;
        case 'user': active_info($env,$db);    break;
        case 'disk': disk_info($env,$db);      break;
        case 'msrt': master_reset($env,$db);   break;
        case 'clok': clock_reset($env,$db);    break;
        case 'lock': server_lock($env,$db);    break;
        case 'pick': server_pick($env,$db);    break;
        case 'menu': status_menu($env,$db);    break;
        case 'post': postpone($env,$db);       break;
        case 'mnth': event_month($env,$db);    break;
        case 'cats': event_cats($env,$db);     break;
        case 'kill': kill_queue($env,$db);     break;
        default    : unknown($env,$db);        break;
    }
    echo head_standard_html_footer($auth,$db);
?>
