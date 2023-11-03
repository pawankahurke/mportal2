<?php

/*
Revision history:

Date        Who     What
----        ---     ----
20-Sep-02   EWB     Giant refactoring
25-Nov-02   EWB     Purge census records as well.
5-Dec-02   EWB     Reorginization Day
13-Jan-03   EWB     Don't requre register_globals
4-Feb-03   EWB     Moved interactive portion elsewhere.
10-Feb-03   EWB     Uses event database.
10-Feb-03   EWB     Uses sandbox libraries.
6-Mar-03   NL      Uses output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
15-Mar-03   EWB     Posix servertime
19-Mar-03   EWB     Uses server_def for ssl and port option.
4-Apr-03   EWB     uses server option census_days
7-Apr-03   EWB     Also purge unused criteria.
11-Jun-03   EWB     Show counts in logfile.
13-Jun-03   EWB     census_days, purge_days default to 90.
18-Jul-03   EWB     purge expired files.
2-Sep-03   EWB     moved file purging to library function.
5-Sep-03   EWB     asset purge.
5-Sep-03   EWB     config purge.
5-Sep-03   EWB     update purge.
6-Sep-03   EWB     Report the age of the oldest item.
8-Sep-03   EWB     Log when not purging for some reason.
8-Sep-03   EWB     Raised default for config/update to 40 days.
6-Oct-03   EWB     spelling counts: serous_messsage -> serious_message
11-Nov-03   EWB     Report age of eldest notify console record.
24-Nov-03   EWB     Elapsed time for purge.
31-Dec-03   EWB     Purge console events (finally!)
7-Jan-04   EWB     real server name for cron.
9-Feb-04   EWB     purge provision meter/audit records.
16-Feb-04   EWB     server_name variable.
23-Feb-04   EWB     config purge removes provision records as well.
2-Mar-04   EWB     purge event records last, optimize table afterwords.
8-Apr-04   EWB     Added server option to skip the optimize table (just in case)
15-Apr-04   EWB     Added server option for postpone seconds.
21-Apr-04   EWB     Show disk size statistics.
22-Apr-04   EWB     Purge config host/site clears host/site cache.
26-Apr-04   EWB     Purge host/site cache.
27-Apr-04   EWB     Report number of cache records removed.
7-May-04   EWB     Postpone queue before event purge, check for reports.
12-May-04   EWB     Analyze after optimize.
12-May-04   EWB     Analyze/Optimize Slave Database.
12-May-04   EWB     Test for syncronization failure.
13-May-04   EWB     select slave event database.
13-May-04   EWB     pause the slave before optimize/analyze
21-May-04   EWB     purge events by time only.
4-Jun-04   EWB     delete from census sets the dirty flag.
14-Jul-04   EWB     purge from census clears patches and machine groups.
14-Dec-04   EWB     sets purge_lock, purge_pid server variables.
17-Feb-05   EWB     Need to call purge_sleep even when osec is zero.
12-May-05   EWB     Purges new gconfig database.
1-Jun-05   EWB     Purges legacy checksum cache
12-Sep-05   BTE     Added checksum invalidation code.
12-Oct-05   BTE     Changed references from gconfig to core.
05-Nov-05   BTE     Specify the operation when calling checksum invalidation
code.
10-Nov-05   BTE     Some delete operations should not be permanent.
24-Feb-06   BTE     Bug 3079: Make expunge/server deletions permanent and make
client preserve self.
15-Mar-06   BTE     Bug 3186: Event logging appears to be completely broken on
4.3 server.
06-May-06   BTE     Bug 3209: 4.2 to 4.3 server upgrade does not work
correctly.
19-Jul-06   BTE     Bug 3239: 913 errors on CI server.
07-Aug-06   BTE     Bug 3569: Change old-style purge to optimize core database.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
17-Oct-06   AAM/RWM Bug 3745: Added purge of events from perf Scrips
23-Mar-08   BTE     Bug 4365: Group management traceback invalid machine
groups.

 */

//if (getenv('CLEAR_EVENTS') !== 'true') {
//    echo "Env var is not CLEAR_EVENTS===true";
//    exit;
//}

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once '../lib/l-util.php';
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-serv.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-slav.php';
include_once '../lib/l-aprg.php';
include_once '../lib/l-cprg.php';
include_once '../lib/l-gcfg.php';
include_once '../lib/l-rlib.php';
include_once '../lib/l-cnst.php';
include_once '../lib/l-cmth.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-file.php';
include_once '../lib/l-jump.php';
include_once '../lib/l-head.php';
include_once '../lib/l-errs.php';
include_once '../lib/l-dsyn.php';
include_once '../lib/l-gdrt.php';
include_once '../lib/l-grps.php';
include_once '../lib/l-core.php';
set_time_limit(0);
$cronConfig = new CronFunction();
if ($cronConfig->startPermission('c-purge', 'C-PURGE') != 'true') {
    echo 'no launch rights';
    exit();
}
$cronConfig->updateMonitoring('C-PURGE');

define('constReportLock', 'report_lock');
define('constNotifyLock', 'notify_lock');
define('constPurgeLock', 'purge_lock');
define('constPurgePid', 'purge_pid');


function purge($sql, $db)
{
    $res = redcommand($sql, $db);
    return affected($res, $db);
}

function message($msg)
{
    $text = fontspeak($msg);
    echo "<p>$text</p>\n";
}

function serious_message($txt)
{
    message($txt);
    logs::log(__FILE__, __LINE__, $txt, 0);
}

function count_table($table, $db)
{
    $sql = "select count(*) from $table";
    return find_scalar($sql, $db);
}

function optimize_events($dbid, $db)
{
    $time = 0;
    $sql = 'optimize table Events';
    $res = redcommand_time($sql, $time, $db);
    $secs = microtime_show($time);
    $msg = "purge: optimize $dbid events table ($secs)";
    serious_message($msg);
    $time = 0;
    $sql = 'analyze table Events';
    $res = redcommand_time($sql, $time, $db);
    $secs = microtime_show($time);
    $msg = "purge: analyze $dbid events table ($secs)";
    serious_message($msg);
}

function short_date($time)
{
    return date('m/d H:i', $time);
}

function percent($part, $whole)
{
    $pm = round(($part * 1000) / $whole);
    $pc = intval($pm / 10);
    $pd = intval($pm % 10);
    return sprintf('%d.%d%%', $pc, $pd);
}

function purge_files($now, $db)
{
    $num = 0;
    $sql = "select * from Files\n"
        . " where expires > 0\n"
        . " and expires < $now";
    $list = find_many($sql, $db);
    if ($list) {
        reset($list);
        foreach ($list as $key => $row) {
            $user = $row['username'];
            $good = delete_file_record($row, 1, $user, $db);
            if ($good) {
                $num++;
            }
        }
    }
    if ($num > 0) {
        $msg = "purge: $num files purged.";
        serious_message($msg);
    }
}

/*
|  Note that we do NOT currently purge old cryptkeys.
 */

function provision_host($site, $host, $db)
{
    $num = 0;
    if (($host) && ($site)) {
        $qh = safe_addslashes($host);
        $qs = safe_addslashes($site);
        $del = 'delete from';
        $txt = "where machine='$qh'\n"
            . " and sitename='$qs'";
        $ma = "$del " . $GLOBALS['PREFIX'] . "provision.MachineAssignments\n $txt";
        $mt = "$del " . $GLOBALS['PREFIX'] . "provision.Meter\n $txt";
        $au = "$del " . $GLOBALS['PREFIX'] . "provision.Audit\n $txt";

        $num += purge($ma, $db);
        $num += purge($mt, $db);
        $num += purge($au, $db);
    }
    return $num;
}

function provision_site($site, $db)
{
    $num = 0;
    if ($site) {
        $qs = safe_addslashes($site);
        $del = 'delete from';
        $txt = "where sitename='$qs'";
        $ma = "$del " . $GLOBALS['PREFIX'] . "provision.MachineAssignments\n $txt";
        $sa = "$del " . $GLOBALS['PREFIX'] . "provision.SiteAssignments\n $txt";
        $mt = "$del " . $GLOBALS['PREFIX'] . "provision.Meter\n $txt";
        $au = "$del " . $GLOBALS['PREFIX'] . "provision.Audit\n $txt";

        $num += purge($ma, $db);
        $num += purge($sa, $db);
        $num += purge($mt, $db);
        $num += purge($au, $db);
    }
    return $num;
}

function purge_asset($days, $now, $db)
{
    $exp = "where expires between 1 and $now";
    $del = "delete from";
    $ssql = "$del AssetSearches\n $exp";
    $csql = "$del AssetSearchCriteria\n $exp";
    $srch = purge($ssql, $db);
    $crit = purge($csql, $db);
    if (($srch) || ($crit)) {
        $msg = "assets: purge search:$srch, criteria:$crit.";
        serious_message($msg);
    }

//    $days = Options::getOption('asset_days');
//  $days = $days['value'];
  $days = CronFunction::getConfigJson('asset_days');
    if ($days <= 0) {
        $days = 120;
    }

    $when = days_ago($now, $days);
    $date = short_date($when);
    $mcnt = count_table('Machine', $db);
    $min = "select min(slatest) from Machine limit 1";
    $old = find_scalar($min, $db);
    debug_note("purge asset days:$days when:$when $mcnt records, date:$date");
    if (($old > 0) && ($old < $now)) {
        $adate = short_date($old);
        $asecs = $now - $old;
        $adays = intval($asecs / 86400);
        $msg = "oldest asset is $adays days ago, $adate";
        message($msg);
    }

    $sql = "select * from Machine\n"
        . " where slatest < $when\n"
        . " order by slatest";
    $list = find_many($sql, $db);
    $mids = array();
    if ($list) {
        reset($list);
        foreach ($list as $key => $row) {
            $mid = $row['machineid'];
            $host = $row['host'];
            $site = $row['cust'];
            $last = $row['slatest'];
            $date = date('m/d/y H:i:s', $last);
            $hsql = "delete from Machine where machineid = $mid";
            $hdel = purge($hsql, $db);
            if ($hdel > 0) {
                $asql = "delete from AssetData where machineid = $mid";
                $adel = purge($asql, $db);
                $msg = "assets: purge $host (d:$days,a:$adel) ($date) at $site";
                serious_message($msg);
            }
        }
    }
}

function purge_census($days, $now, $db)
{
    $when = days_ago($now, $days);
    $date = short_date($when);
    debug_note("purge census days:$days when:$when date:$date");
    $min = "select min(last) from Census limit 1";
    $old = find_scalar($min, $db);
    if (($old > 0) && ($old < $now)) {
        $adate = short_date($old);
        $asecs = $now - $old;
        $adays = intval($asecs / 86400);
        $msg = "oldest census machine is $adays days ago, $adate";
        message($msg);
    }

    $sql = "select * from Census\n"
        . " where last < $when\n"
        . " order by last";
    $list = find_many($sql, $db);
    $mids = array();
    if ($list) {
        reset($list);
        foreach ($list as $key => $row) {
            $cdel = 0;
            $host = $row['host'];
            $site = $row['site'];
            $last = $row['last'];
            $id = $row['id'];
            $date = date('m/d/y H:i:s', $last);
            $cdel = purge_config_host($id, $site, $host, $db);

            if ($cdel) {
                $mids[] = $id;
                $msg = "census: purge $host ($date) at $site";
                serious_message($msg);
            }
        }
        if ($mids) {
            purge_patch_list($mids, $db);
            groups_init($db, constGroupsInitFull);
        }
    }
}

function postpone_events($secs, $db)
{
    if ($secs > 0) {
        $when = intval(time() + $secs);
        $sql = "update Reports set\n"
            . " next_run = $when\n"
            . " where enabled = 1\n"
            . " and next_run > 0\n"
            . " and next_run < $when";
        //        redcommand($sql, $db);
        $sql = "update Notifications set\n"
            . " next_run = $when\n"
            . " where enabled = 1\n"
            . " and next_run > 0\n"
            . " and next_run < $when";
        redcommand($sql, $db);
    }
}

function queue_running($db)
{
    $rep = server_int(constReportLock, 0, $db);
    $not = server_int(constNotifyLock, 0, $db);
    return $rep + $not;
}

/*
|  We want to avoid running the event purge at the
|  same time as a notification or report, since that
|  often results in the insert lock deadlock.
|
|  So ... if we find that a notification is running
|  we'll wait around for a reasonable amount
|  of time (30 mins) for it to finish.
|
|  If that doesn't work, then we'll just have to
|  skip the event purge this time.
 */

function purge_sleep($osec, $db)
{
    $wait = 1800;
    $start = time();
    $count = queue_running($db);
    $delta = time() - $start;
    message("count:$count, delta: $delta");
    while (($count > 0) && ($delta < $wait)) {
        $msg = "purge: $count in queue, $delta seconds.";
        serious_message($msg);
        sleep(60);
        postpone_events($osec, $db);
        $count = queue_running($db);
        $delta = time() - $start;
        message("count:$count, delta: $delta");
    }
    if ($count <= 0) {
        $good = true;
    } else {
        $msg = "purge: skipping event purge due to run queue.";
        serious_message($msg);
        $good = false;
    }
    return $good;
}

/*
|  Purge the events database ... this included the
|  events themselves, plus the notification console.
 */

function purge_events(&$env, $days, $perfDays, $now, $optimize, $db)
{
    $osec = $env['osec'];
    $bias = $env['bias'];
    $sdb = $env['sdb'];

//    $days = Options::getOption('purge_days');
//  $days = $days['value'];
  $days = CronFunction::getConfigJson('purge_days');

  if ($days <= 0) {
        $days = 90;
    }
    $perfDays = $days;



    $when = days_ago($now, $days);
    $perfWhen = days_ago($now, $perfDays);
    $date = short_date($when);
    $pcnt = count_table('Console', $db);
    $min = "select min(servertime) from Console limit 1";
    $old = find_scalar($min, $db);
    if (($old > 0) && ($old < $now)) {
        $adate = short_date($old);
        $asecs = $now - $old;
        $adays = intval(round($asecs / 86400));
        $msg = "oldest notify record is $adays days ago, $adate";
        message($msg);
    }

    /*
    |  We purge the notification console records
    |  which have expired, or who have outlived
    |  the events which they are referencing.
    |
    |  So, we use the same $when we already
    |  calculated for the Event purge.
     */

    $sql = "delete from Console\n"
        . " where expire < $now or\n"
        . " servertime < $when";
    $pdel = purge($sql, $db);
    if ($pdel > 0) {
        $msg = "purge: $pcnt console, $days days ($date) $pdel removed.";
        serious_message($msg);
    }


    if (($optimize) && ($osec > 0)) {
        $msg = "purge: postpone for purge ($osec seconds)";
        serious_message($msg);
        postpone_events($osec, $db);
    }

    /*
    |  21-May-2004 EWB
    |
    |  The purge ought to remove events that have the deleted
    |  flag set ... however yesterday morning nanoheal had an
    |  unnacceptable freeze during optimization time.
    |
    |  I'm thinking now that even though it really ought to
    |  work to complete remove expunged events, it's probably
    |  safer to just leave them there.   This means that we
    |  are only removing the oldest events, and the optimizer
    |  will have less work to do.
     */

    $good = purge_sleep($osec, $db);
    $pdel = 0;
    $pcnt = count_table('Events', $db);
    debug_note("purge events days:$days when:$when events:$pcnt date:$date");
    if (($pcnt > 0) && ($good)) {
        $min = "select min(servertime) from Events limit 1";
        $old = find_scalar($min, $db);
        if (($old > 0) && ($old < $now)) {
            $adate = short_date($old);
            $asecs = $now - $old;
            $adays = intval(round($asecs / 86400));
            $msg = "oldest event is $adays days ago, $adate";
            message($msg);
        }

        // get scrip for delete

//        $scrip_delete = Options::getOption('delete_events_scrip');
//        $scrip_delete = $scrip_delete['value'];
        $scrip_delete = CronFunction::getConfigJson('delete_events_scrip');

        //Events Archive before delete // commented bcz "sended_to_sn" column not exists in EventsArchive"
        // $sql = "insert into EventsArchive SELECT * from Events where\n"
        //     . " ( (servertime < $when) or\n"
        //     . "   ( (scrip in ($scrip_delete)) and\n"
        //     . "     (servertime < $perfWhen) ) )";
        // error_log('==============='.$sql);
        // purge($sql, $db);

        $sql = "delete from Events where\n"
            . " ( (servertime < $when) or\n"
            . "   ( (scrip in ($scrip_delete)) and\n"
            . "     (servertime < $perfWhen) ) )";
        $pdel = purge($sql, $db);
    }

    if (($pdel > 0) && ($pcnt > 0) && ($good)) {
        $msg = "purge: $pcnt events, $days days ($date), $pdel removed.";
        serious_message($msg);
        if ($optimize) {
            if ($osec > 0) {
                $msg = "purge: postpone for optimize ($osec seconds)";
                serious_message($msg);
                postpone_events($osec, $db);
            }

            optimize_events('master', $db);
            if (($sdb) && (mysqli_select_db($sdb, 'event'))) {
                if ($osec > 0) {
                    postpone_events($osec, $db);
                }
                if ($bias > 0) {
                    sleep($bias);
                }
                redcommand('slave stop', $sdb);
                optimize_events('slave', $sdb);
                redcommand('slave start', $sdb);
            }
        } else {
            message("skipping optimize");
        }
    }
}

function purge_config($days, $now, $db)
{
    $when = days_ago($now, $days);
    $date = short_date($when);
    debug_note("purge config days:$days, when:$when, date:$date");
    $min = "select min(ctime) from Revisions limit 1";
    $old = find_scalar($min, $db);
    if (($old > 0) && ($old < $now)) {
        $adate = short_date($old);
        $asecs = $now - $old;
        $adays = intval($asecs / 86400);
        $msg = "oldest config is $adays days ago, $adate";
        message($msg);
    }

    $sql = "select C.host, C.site, R.* from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Revisions as R,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
        . " where R.censusid = C.id\n"
        . " and R.ctime < $when\n"
        . " order by ctime";
    $set = find_many($sql, $db);
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $hid = $row['censusid'];
            $host = $row['host'];
            $site = $row['site'];
            $last = $row['ctime'];
            $date = date('m/d/y H:i:s', $last);
            $num = purge_config_host($hid, $site, $host, $db);
            if ($num) {
                $pdel = provision_host($site, $host, $db);
                $msg = "config: purge $host (n:$num,p:$pdel,d:$days) ($date) at $site";
                serious_message($msg);
                // debug_note($msg);
            }
        }
    }

    /*
    |  We don't use the checksum cache unless the timestamp
    |  indicates that it's less than 16 hours old, so there's
    |  no real need to keep it much longer than that.  But
    |  the purge normally only happens once a day, and the
    |  week timeout means that a user can turn their
    |  computer off over a long weekend and we'll still end
    |  up doing an update instead of an insert.
     */

    $days = 7;
    $when = days_ago($now, $days);
    $date = date('m/d H:i', $when);
    $sql = "delete from LegacyCache\n"
        . " where last < $when";
    $del = purge($sql, $db);
    if ($del) {
        $msg = "config: purge checksum cache (c:$del,d:$days) $date";
        serious_message($msg);
    }
}

function purge_update($days, $now, $db)
{
    $when = days_ago($now, $days);
    $date = short_date($when);
    debug_note("purge update days:$days, when:$when, date:$date");

    $min = "select min(timecontact) from UpdateMachines limit 1";
    $old = find_scalar($min, $db);
    if (($old > 0) && ($old < $now)) {
        $adate = short_date($old);
        $asecs = $now - $old;
        $adays = intval($asecs / 86400);
        $msg = "oldest update is $adays days ago, $adate";
        message($msg);
    }

    $sql = "select * from UpdateMachines\n"
        . " where timecontact < $when\n"
        . " order by timecontact";
    $list = find_many($sql, $db);
    $sites = array();
    if ($list) {
        reset($list);
        foreach ($list as $key => $row) {
            $udel = 0;
            $host = $row['machine'];
            $site = $row['sitename'];
            $last = $row['timecontact'];
            $date = date('m/d/y H:i:s', $last);

            $qs = safe_addslashes($site);
            $qh = safe_addslashes($host);
            $sql = "delete from\n"
                . " UpdateMachines where\n"
                . " machine = '$qh' and\n"
                . " sitename = '$qs'";
            //       debug_note($sql);
            $sites[$site] = true;
            $udel = purge($sql, $db);
            $msg = "update: purge $host (d:$days) ($date) at $site";
            serious_message($msg);
            //       debug_note($msg);
        }
    }

    if ($sites) {
        reset($sites);
        foreach ($sites as $site => $data) {
            $qs = safe_addslashes($site);
            $sql = "select count(*)\n"
                . " from UpdateMachines\n"
                . " where sitename = '$qs'";
            $num = find_scalar($sql, $db);
            debug_note("test number:$num, site:$site");
            if ($num == 0) {
                $udel = 0;
                $ddel = 0;
                $usql = "delete from UpdateSites where sitename = '$qs'";
                $dsql = "delete from Downloads where sitename = '$qs'";
                $udel = purge($usql, $db);
                $ddel = purge($dsql, $db);
                $msg = "update: purge site (s:$udel,d:$ddel) $site";
                serious_message($msg);
                //          debug_note($msg);
            }
        }
    }
}

function nopurge($module, $days)
{
    $msg = "$module: not purged, $days days.";
    serious_message($msg);
}

function purge_meter($days, $now, $db)
{
    $when = days_ago($now, $days);
    $date = short_date($when);
    $mcnt = count_table('Meter', $db);
    debug_note("purge meter days:$days when:$when records:$mcnt date:$date");
    $min = "select min(servertime) from Meter limit 1";
    $old = find_scalar($min, $db);
    if (($old > 0) && ($old < $now)) {
        $adate = short_date($old);
        $asecs = $now - $old;
        $adays = intval(round($asecs / 86400));
        $msg = "oldest meter record is $adays days ago, $adate";
        message($msg);
    }

    $sql = "delete from Meter\n"
        . " where servertime < $when";
    $pdel = purge($sql, $db);
    if ($pdel > 0) {
        $msg = "purge: $mcnt meter, $days days ($date), $pdel removed.";
        serious_message($msg);
    }
}

function purge_audit($days, $now, $db)
{
    $when = days_ago($now, $days);
    $date = short_date($when);
    $acnt = count_table('Audit', $db);
    debug_note("purge audit days:$days when:$when records:$acnt date:$date");
    $min = "select min(servertime) from Audit limit 1";
    $old = find_scalar($min, $db);
    if (($old > 0) && ($old < $now)) {
        $adate = short_date($old);
        $asecs = $now - $old;
        $adays = intval(round($asecs / 86400));
        $msg = "oldest audit record is $adays days ago, $adate";
        message($msg);
    }

    $sql = "delete from Audit\n"
        . " where servertime < $when";
    $pdel = purge($sql, $db);
    if ($pdel > 0) {
        $msg = "purge: $acnt audit, $days days ($date), $pdel removed.";
        serious_message($msg);
    }
}

function show_size($size)
{
    $kb = 1024;
    $mb = $kb * $kb;
    $gb = $kb * $mb;
    $k = round($size / $kb);

    if ($size <= 10240) {
        $t = "$size bytes";
    }
    if ((10240 < $size) && ($size <= $mb)) {
        $t = $k . 'k';
    }
    if (($mb < $size) && ($size <= $gb)) {
        $x = intval(round($size / ($mb / 10)));
        $m = intval($x / 10);
        $d = intval($x % 10);
        $t = sprintf('%d.%dM', $m, $d);
    }
    if ($size > $gb) {
        $x = intval(round($size / ($gb / 10)));
        $g = intval($x / 10);
        $d = intval($x % 10);
        $t = sprintf('%d.%dG', $g, $d);
    }
    return $t;
}

function expire(&$env, $db)
{
    $pid = $env['pid'];
    $now = $env['now'];
    $usec = $env['usec'];
    $name = $env['server'];

    $ccnt = count_table('Census', $db);
    $info = asi_info();
    $vers = $info['svvers'];
    $date = $info['svdate'];
    $php = phpversion();
    $text = "purge: $name ($vers) [$php] $date, $ccnt machines ($pid)";
    serious_message($text);

    purge_files($now, $db);

    $past = intval($now / 86400) - 7;

    debug_note("past: $past days");

//    $days = $env['census'];
      $days = CronFunction::getConfigJson('census_days');
    if ((21 <= $days) && ($days < $past)) {
        purge_census($days, $now, $db);
    } else {
        nopurge('census', $days);
    }
    if (mysqli_select_db($db, 'asset')) {
        //        $adel = purge_unused_criteria($db);
        //        if ($adel > 0) {
        //            $msg = "assets: $adel unused criteria removed.";
        //            serious_message($msg);
        //        }
        $days = $env['asset'];
        if ((14 <= $days) && ($days < $past)) {
            purge_asset($days, $now, $db);
        } else {
            nopurge('assets', $days);
        }
    }
    if (mysqli_select_db($db, 'core')) {
        $days = $env['config'];
        if ((7 <= $days) && ($days < $past)) {
            purge_config($days, $now, $db);
        } else {
            nopurge('config', $days);
        }
    }
    if (mysqli_select_db($db, 'swupdate')) {
        $days = $env['update'];
        if ((7 <= $days) && ($days < $past)) {
            purge_update($days, $now, $db);
        } else {
            nopurge('update', $days);
        }
    }
    if (mysqli_select_db($db, 'provision')) {
        $days = $env['meter'];
        if ((7 <= $days) && ($days < $past)) {
            purge_meter($days, $now, $db);
        } else {
            nopurge('meter', $days);
        }
        $days = $env['audit'];
        if ((7 <= $days) && ($days < $past)) {
            purge_audit($days, $now, $db);
        } else {
            nopurge('audit', $days);
        }
    }
    if (mysqli_select_db($db, 'event')) {
        $opts = $env['opts'];
        $days = $env['event'];
        $perfDays = $env['perf'];
        if ((7 <= $days) && ($days < $past)) {
            purge_events($env, $days, $perfDays, $now, $opts, $db);
        } else {
            nopurge('events', $days);
        }
    }

    // $dir  = '/var/lib/mysql';
    // $free = disk_free_space($dir);
    // $size = disk_total_space($dir);
    // if (($size > 0) && ($free < $size)) {
    //     $used = $size - $free;
    //     $pu   = percent($used, $size);
    //     $pf   = percent($free, $size);
    //     $ds   = show_size($size);
    //     $df   = show_size($free);
    //     $du   = show_size($used);
    //     $text = "purge: disk total $ds, used $du ($pu), free $df ($pf).";
    //     serious_message($text);
    // }

    mysqli_select_db($db, 'core');

    /*
    |  There's really no pressing need for this, but I wanted
    |  to know how long it takes to do the purge ...
     */

    $msec = microtime_diff($usec, microtime());
    $secs = microtime_show($msec);
    $text = "purge: $name ($vers) complete ($pid) in $secs";
    serious_message($text);
}

function again()
{
    $self = server_var('PHP_SELF');
    $args = server_var('QUERY_STRING');
    $href = ($args) ? "$self?$args" : $self;

    $a = array();
    $a[] = html_link($href, 'again');
    $a[] = html_link('../acct/index.php', 'home');
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    return jumplist($a);
}

function checkup($env, $db)
{
    $bias = $env['bias'];
    $sdb = $env['sdb'];
    $now = time();
    $mcnt = 0;
    $scnt = 0;
    $smax = 0;
    $mmax = 0;

    // if (get_magic_quotes_gpc()) {
    //     serious_message("warning: magic_quotes_gpc is enabled");
    // }
    if (ini_get('register_globals')) {
        serious_message("warning: register_globals is enabled");
    }

    if ($sdb) {
        debug_note("slave database is running");
        $sql = "select count(*) from  " . $GLOBALS['PREFIX'] . "event.Events";
        $mcnt = find_scalar($sql, $db);
        $scnt = find_scalar($sql, $sdb);
        $sql = "select max(servertime) from  " . $GLOBALS['PREFIX'] . "event.Events";
        $mmax = find_scalar($sql, $db);
        $smax = find_scalar($sql, $sdb);
    }
    if (($scnt) && ($mcnt)) {
        debug_note("master count:$mcnt, slave count: $scnt");
        if (($smax) && ($mmax)) {
            $stim = date('m/d H:i:s', $smax);
            $mtim = date('m/d H:i:s', $mmax);
            debug_note("master time: $mtim, slave time: $stim");
        }
    }

    if (($mcnt != $scnt) && ($smax != $mmax)) {
        $mage = abs($now - $mmax);
        $sage = abs($now - $smax);
        if (($sage > $bias) && ($sage > $mage)) {
            $miss = abs($mcnt - $scnt);
            $delay = abs($sage - $mage);

            debug_note("master: $mage, slave: $sage, missing:$miss, delay:$delay");
            $stat = "m:$miss,d:$delay";
            serious_message("warning: possible ($stat) synchronization problem");
        }
    }
}

function optimize_db($dbname, $db)
{
    $tables = find_tables($dbname, $db);
    foreach ($tables as $table => $val) {
        $time = 0;
        $sql = "optimize table $dbname.$table";
        $res = redcommand_time($sql, $time, $db);
        $secs = microtime_show($time);
        $msg = "purge: optimize $dbname.$table ($secs)";
        serious_message($msg);
        $time = 0;
        $sql = "analyze table $dbname.$table";
        $res = redcommand_time($sql, $time, $db);
        $secs = microtime_show($time);
        $msg = "purge: analyze $dbname.$table ($secs)";
        serious_message($msg);
    }
}

/*
|  Main program
 */

$now = time();
$usec = microtime();
$pid = getmypid();
$db = db_connect();
$comp = component_installed();
$name = "Cron Purge ($pid)";
$msg = ob_get_contents(); // save the buffered output so we can...
ob_end_clean(); // (now dump the buffer)
echo standard_html_header($name, $comp, '', 0, 0, 0, $db);
if (trim($msg)) {
    debug_note($msg);
}
// ...display any errors to debug users

$debug = get_integer('debug', 0);
$force = get_integer('force', 0);
if ($force) {
    opt_update(constPurgePid, 0, 0, $db);
    opt_update(constPurgeLock, 0, 0, $db);
}

$sdb = db_cron($db);

$env = array();
$env['pid'] = $pid;
$env['sdb'] = $sdb;
$env['now'] = $now;
$env['usec'] = $usec;
$env['opts'] = server_int('optimize_events', 1, $db);
$env['osec'] = server_int('optimize_secs', 1200, $db);
$env['bias'] = server_int('cron_bias', 120, $db);
$env['event'] = server_int('purge_days', 90, $db);
$env['perf'] = server_int('perf_event_days', 3, $db);
$env['asset'] = server_int('asset_days', 120, $db);
$env['meter'] = server_int('meter_days', 60, $db);
$env['audit'] = server_int('audit_days', 60, $db);
$env['update'] = server_int('update_days', 40, $db);
$env['census'] = server_int('census_days', 100, $db);
$env['config'] = server_int('config_days', 40, $db);
$env['space'] = server_def('min_free_space', '15%', $db);
$env['server'] = server_name($db);
echo again();

$lock = new FileLock();

expire($env, $db);
checkup($env, $db);

/* Now optimize the core database */
if ($env['opts']) {
    optimize_db('core', $db);
}

echo again();

$user = 'hfn';
echo head_standard_html_footer($user, $db);
