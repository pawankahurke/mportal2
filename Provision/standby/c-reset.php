<?php

/* 
Revision History:

Date        Who     What
----        ---     ----
28-Jun-05   BJS     created.
29-Jun-05   BJS     removed uneeded procedures.
30-Jun-05   BJS     removed more procedures.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-head.php'  );
include_once ( '../lib/l-cron.php'  );


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


    function master_reset($now,$db)
    {
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
    }

    /* --- main --- */

    $mtstart = microtime();
    $now     = time();
    $pid     = getmypid();

    $single = get_integer('id',0);
    $dbg    = get_integer('debug',0);
    $debug  = (($dbg) || ($single));
    $mdb    = db_connect();
    $title  = "Master Reset($pid)";
    $auth   = getenv("REMOTE_USER"); // cron does not use PHP auth'n
    $comp   = component_installed();

    echo standard_html_header($title,$comp,$auth,0,0,0,$mdb);

    logs::log(__FILE__, __LINE__, "master reset: p($pid) reports, notifications and assetreports");   

    master_reset($now,$mdb);

	echo head_standard_html_footer($mdb,$db);
