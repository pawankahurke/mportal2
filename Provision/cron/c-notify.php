<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 1-Aug-02   EWB     Fixed a problem with non-standard ports
 2-Aug-02   EWB     Allows port to be specified on command line
 2-Aug-02   EWB     Better override checking.
13-Aug-02   EWB     mysql failures should always be logged
20-Sep-02   EWB     Giant refactoring
14-Oct-02   EWB     Using lib_base.
 5-Dec-02   EWB     Reorginization Day
13-Dec-02   EWB     Mail from $SERVER_NAME
13-Jan-03   EWB     Don't require register_globals.
13-Jan-03   EWB     Deal with quotes in notification names.
16-Jan-03   EWB     Access to $_SERVER variables.
31-Jan-03   EWB     Uses new server options.
10-Feb-03   EWB     Uses event database.
10-Feb-03   EWB     Uses sandbox libraries.
13-Feb-03   EWB     Need to calculate access tree before database change.
13-Feb-03   EWB     Cache user default email addresses before starting.
20-Feb-03   EWB     notify_query initializes nid before calling notify_disable.
 6-Mar-03   NL      Uses output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   EWB     Uses server_def for ssl and port option.
17-Apr-03   EWB     Console records classified by site.
23-Apr-03   EWB     Human-readable timestamps for entered, servertime.
30-Apr-03   NL      Impliment Site Filtering:
                    Incl l-sitflt.php.
                    Get siteaccesstree and sitefiltertree (arrays not lists).
30-Apr-03   NL      Fix "$siteaccess = $env['siteaccesstree'][$authuser]" --> [$username]
 2-May-03   NL      Correct var names. Proxy user array is called $users not $owners
                        and userdata array is now called $userdata not $users.
                    Optimization:
                     - Use $row['filtersites'] rather than get_obj_filtersetting.
                     - Only pass notn ids for notifications where sitefilter is ON.
 2-Jun-03   EWB     Better method of finding event field names.
10-Jun-03   EWB     Master & Slave database access.
                     - Slave database is read only, used only for select.
                     - Master database used for insert, update and delete.
13-Jun-03   EWB     Don't bother with site filtering unless we're really using it.
16-Jun-03   EWB     Uses slave library.
18-Jun-03   EWB     Use between instead of two comparisons.
19-Jun-03   EWB     Log Slow Queries.
19-Jun-03   EWB     Use master database to find saved searches.
15-Jul-03   EWB     Log Master/Slave for slow queries.
23-Jul-03   EWB     Implement Links.
 5-Sep-03   EWB     Use Slave database for 'Events' table only.
 3-Nov-03   EWB     Check for index existance in solo evaluation.
18-Nov-03   EWB     Report process id for significant log messages.
20-Nov-03   EWB     ignore_user_abort();
20-Nov-03   EWB     Slow query reports number of results returned.
24-Nov-03   EWB     Implement run single notify.
25-Nov-03   NL      Don't generate reports for inactive sites.
25-Nov-03   NL      If threshold is 0, generate "zero-event" notifications indicating event
                        DID NOT happen (requires l-cens).  To distinguish machines
                        from diff sites, changed $list to 2D array $eventcounts.
26-Nov-03   NL      Remove vestigial $env['fnames'],find_field_names(),periodic();
                    Move insert code into new fct insert_zero_event(); Small bug fixes.
30-Nov-03   AAM     Added performance logging entries.
10-Dec-03   EWB     Never insert empty console events for non-zero notifications.
10-Dec-03   EWB     Don't make log entry for user sans active site.
11-Dec-03   EWB     Wording change in notify mail.
11-Dec-03   EWB     zero event notify: email subject reports count of 0-event machines.
 7-Jan-04   EWB     case-insensitive site calculations.
13-Feb-03   EWB     Uses server_name();
16-Feb-03   EWB     server_name variable.
11-Mar-04   EWB     events.Events.deleted
25-Mar-04   EWB     New scheduling algorythm.
26-Mar-04   EWB     Abort if already running.
26-Mar-04   EWB     Stuck notification sends E-Mail.
 8-Apr-04   EWB     slow notifications log with human-readable timestamps.
 9-Apr-04   EWB     reset notify
12-Apr-04   EWB     Notify Timeout
15-Apr-04   EWB     Always schedule into the future, even when busy.
16-Apr-04   EWB     No, I changed my mind, past schedule is ok.
16-Apr-04   EWB     Always schedule the locals first.
20-Apr-04   EWB     Suicide upon certain kinds of failure.
18-May-04   EWB     Fixed small spelling error.
 8-Oct-04   EWB     Always measure time in seconds.
20-Oct-04   EWB     Process notifications just one at a time, better locking.
21-Oct-04   EWB     One Shot Notifications.
22-Oct-04   EWB     Tardy Proximity still needs to update next run.
 1-Nov-04   EWB     control timespan for oneshot notifications.
10-Dec-04   BJS     added skip_owner: option to not run report for owner.
14-Dec-04   EWB     purge_lock always highest priority.
31-Jan-05   EWB     proximity failure does not invalidate notification.
29-Jul-05   EWB     specifies target population by way of machine groups
 1-Aug-05   EWB     debug zero event notifications
 1-Sep-05   BJS     removed event.Events.deleted, replace with core.Census.deleted.
28-Sep-05   BJS     Added options for Notifications with;
                    Per-site email notifications, using the site sender from address
                    and adding an email footer (Autotask).
03-Oct-05   BJS     Added constAutoUser/Pass.
05-Oct-05   BJS     Generate zero-event reports correctly.
27-Oct-05   BJS     Added HEX() to notify_run when joining core with events
                    and comparing strings.
04-Nov-05   BJS     Fixed mgroupid queries to IN not =.
10-Nov-05   BJS     Removed filtersites reference.
23-Jan-06   AAM     Fixed bug 3036 -- put event count at end of email subject.
24-Jan-06   AAM     Update to last fix; added "events" at end of subject.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
18-Apr-06   BJS     Bug 3276: Ambiguous column name in SQL statement.
                    Bug 3277: Undefined variable in send_error_mail( ) & 
                    proximity_failure( ).
14-Jun-06   BTE     Part of bug 3468: Slow query locks event logging.  Bug
                    3476: Failed notification errors get sent to customers.
16-Jun-06   BTE     Part of bug 3468, bug 3482: Scrip Configurator too slow
                    on the server.
31-Jul-06   AAM     Run dashboard update along with notifications.
09-Aug-06   AAM     Added code to use max_php_mem_mb into main program for
                    calls to notify_cron and PHP_DNAV_PeriodicUpdate.  This
                    was kind of a major omission.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
24-Nov-06   AAM     Bug 3865: implemented consistent use of stripslashes with
                    contents of event filters.
05-Dec-06   BTE     Bug 3933: Force notifications and reports to use the
                    servertime index.
27-Dec-07   BTE     Added support for Autotask tickets/notes.
04-Feb-08   BTE     Bug 4402: Improve CodeNotify query in c-notify.php.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-cnst.php');
include('../lib/l-errs.php');
include('../lib/l-perf.php');
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-head.php');
include('../lib/l-base.php');
include('../lib/l-cron.php');
include('../lib/l-gsql.php');
include('../lib/l-slav.php');
include('../lib/l-jump.php');
//  include ( '../lib/l-rlib.php'  );
//  include ( '../lib/l-grps.php'  );
include('../lib/l-next.php');
include('../lib/l-cens.php');
include('../lib/l-auto.php');

define('constPurgeLock',    'purge_lock');
define('constNotifyLock',   'notify_lock');
define('constNotifyPid',    'notify_pid');
define('constNotifyTimo',   'notify_timeout');

define('constAutoName', '%name%');
define('constAutoSite', '%site%');
define('constAutoUser', '%ticketuser%');
define('constAutoPass', '%ticketpassword%');

/*
    |  Check to see if we still own the lock.
    |  If so, all is well ... update the mod time.
    |
    |  otherwise we have a problem
    */

function checkpid($pid, $db)
{
    $good = false;
    $lpid = server_int(constNotifyPid, 0, $db);
    if ($lpid == $pid) {
        opt_update(constNotifyPid, $pid, 0, $db);
        $good = true;
    } else {
        $acts = ($lpid) ? "stolen by process $lpid" : 'vanished';
        $stat = "p:$pid";
        $text = "notify: queue ($stat) lock $acts";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }
    return $good;
}



/*
    |   Returns the original array, except that
    |   the empty elements have been filtered out.
    */

function filter($p)
{
    $list = array();
    $n = safe_count($p);
    for ($i = 0; $i < $n; $i++) {
        $elem = $p[$i];
        if ($elem) $list[] = $elem;
    }
    return $list;
}

function again()
{
    $self = server_var('PHP_SELF');
    $args = server_var('QUERY_STRING');
    $href = ($args) ? "$self?$args" : $self;

    $a   = array();
    $a[] = html_link($href, 'again');
    $a[] = html_link('../acct/index.php', 'home');
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link('c-report.php', 'report');
    $a[] = html_link('c-asset.php', 'asset');
    return jumplist($a);
}

/*
    |  enabled = 0 -> disabled
    |  enabled = 1 -> enabled
    |  enabled = 2 -> invalidated
    */

function notify_disable($db, $id)
{
    logs::log(__FILE__, __LINE__, "disable notify $id", 0);
    $sql = "update Notifications\n set enabled = 2\n where id = $id";
    redcommand($sql, $db);
}


function notify_update($db, $now, $id)
{
    $sql = "update Notifications set\n"
        . " next_run = -1,\n"
        . " last_run = $now\n"
        . " where id = $id";
    $res = redcommand($sql, $db);
    return affected($res, $db);
}


function notify_query($db, $row)
{
    $search = array();

    $nid = $row['id'];
    $sid = $row['search_id'];
    $sql = "select * from SavedSearches\n where id = $sid";
    $search = find_one($sql, $db);
    if (!$search) {
        echo "[Notify] Error, could not find saved search $sid <br />\n";
        //      echo "[Notify] Notification disabled<br />\n";
        logs::log(__FILE__, __LINE__, "notify $nid, missing saved search $sid", 0);
        //      notify_disable($db, $nid);
    }
    return $search;
}


function send_error_mail(&$env, $row, $query)
{
    $server = $env['server'];
    $base = $env['base'];
    debug_note("send_error_mail(env,row,$query)");
    $id   = $row['id'];
    $name = $row['name'];
    $user = $row['username'];
    $to   = $row['emaillist'];
    $def  = $env['userdata'][$user]['notify_mail'];
    $to   = email_list($to, 1, $def);

    if ($to == '') {
        $to = $env['support'];
    }

    if ($to) {
        $sign = $env['company'];
        $url  = "$base/event/notify.php?act=edit&nid=$id";
        $sub  = "Notification Error";
        $msg  = "There is a problem running the notification ";
        $msg .= "you requested named $name. ";
        $msg .= "The most likely issue is that there is an error ";
        $msg .= "in your saved search:\n\n$query\n\n";
        $msg .= "You can edit your Notification here:\n\n";
        $msg .= "$url\n\n$sign.";

        $origin = $env['origin'];
        if ($origin == '') {
            $server = $env['server'];
            $origin = "notify@$server";
        }

        $from = "From: $origin";
        $to = $env['support'];
        mail($to, $sub, $msg, $from);
    }
}


/*
    |  This has to go into the master database.
    */

function log_notify($mdb, $servertime, $name, $site, $username, $count, $event_list, $config, $nid, $priority, $days)
{
    $qname = safe_addslashes($name);
    $qsite = safe_addslashes($site);
    $quser = safe_addslashes($username);
    if ($days) {
        $expire = $servertime + ($days * 86400);
    } else {
        $expire = 0x7fffffff;
    }
    $sql = "insert into Console set"
        . " name='$qname',"
        . " site='$qsite',"
        . " priority=$priority,"
        . " username='$quser',"
        . " config='$config',"
        . " count=$count,"
        . " event_list='$event_list',"
        . " nid=$nid,"
        . " expire=$expire,"
        . " servertime=$servertime";
    $res = command($sql, $mdb);
}


/*
    |   insert_zero_event
    |
    |   Inserts a "zero-event" record into event.Events table for each site/machine
    |     that returns no events on a "zero-event" notification (threshold == 0).
    |
    |   parameters:
    |       $site:      The site of the machine with no events returned.
    |       $machine:   The machine with no events returned.
    |       $username:  The owner of the zero-event notification.
    |       $name:      The name of the zero-event notification.
    |       $now:       Current time for use as "servertime".
    |       $mdb:       The handle to the database connection.
    */

function insert_zero_event($site, $machine, $username, $name, $now, $mdb)
{
    $zero_event_id = 0;

    $qsite    = safe_addslashes($site);
    $qmachine = safe_addslashes($machine);

    $sql = "insert into Events set\n"
        . " scrip=1000,\n"
        . " entered=$now,\n"
        . " customer='$qsite',\n"
        . " machine='$qmachine',\n"
        . " username='$username',\n"
        . " priority=1,\n"
        . " description='Missing Event Notification',\n"
        . " size=0,\n"
        . " id=0,\n"
        . " text1='$name',\n"
        . " servertime=$now";

    $res = command($sql, $mdb);
    $num = affected($res, $mdb);
    if ($num) {
        $zero_event_id = ((is_null($___mysqli_res = mysqli_insert_id($mdb))) ? false : $___mysqli_res);
    }
    return $zero_event_id;
}

// mattb at columbia dot edu

function seed_random()
{
    $hash = md5(microtime());
    $lo   = substr($hash, -8);
    $seed = hexdec($lo);
    $seed &= 0x7fffffff;
    mt_srand($seed);
}


/*
    |  for a specified x, returns a random
    |  number between -x and x.
    */

function fuzz_time($secs)
{
    $max  = 2 * $secs;
    $time = mt_rand(0, $max);
    return $time - $secs;
}

/*
    |  Finds the oldest running notification.
    |  Normally there aren't any.
    */

function find_stuck($db)
{
    $sql = "select * from Notifications\n"
        . " where enabled = 1\n"
        . " and next_run < 0\n"
        . " and this_run > 0\n"
        . " order by this_run desc, id\n"
        . " limit 1";
    return find_one($sql, $db);
}

function kill_notify($nid, $db)
{
    $sql = "delete from Notifications\n"
        . " where id = $nid";
    $res = redcommand($sql, $db);
    if (affected($res, $db)) {
        $sql = "delete from NotifySchedule\n"
            . " where nid = $nid";
        redcommand($sql, $db);
    }
}

/*
    |  Find a likely notification to run.
    |
    */

function find_next($now, $db)
{
    $ddd = date('m/d H:i:s', $now);
    $sql = "select * from Notifications -- $ddd\n"
        . " where next_run between 1 and $now\n"
        . " and enabled = 1\n"
        . " order by next_run, global, seconds, id\n"
        . " limit 1";
    return find_one($sql, $db);
}



/*
    |  Notifications have a tendency to start out perfectly syncronized
    |  and then stay that way.  We want to randomly distribute them.
    |  So the 5 minute things can sometimes be 4 or 6, and
    |  the 60 minute things can be 58..62.
    |
    |  If the server is extremely busy, next_run can be scheduled in
    |  the past.  This does not matter, because another notify process
    |  won't start until this one finished anyay.
    |
    |  So ... we allow next_run to reflect when the notification ought
    |  to be run, but it may not actually get to run until the current
    |  one finishes.
    */

function finished(&$env, &$row)
{
    $db   = $env['mdb'];
    $last = $env['xnow'];
    $nid  = $row['id'];
    $type = $row['ntype'];
    if ($type == constScheduleClassic) {
        $secs = $row['seconds'];
        $deci = intval($secs / 10);
        $rand = ($deci > 300) ? 300 : $deci;
        $fuzz = fuzz_time($rand);
        $time = $secs + $fuzz;
        $next = $last + $time;
        debug_note("secs:$secs deci:$deci rand:$rand fuzz:$fuzz time:$time");
    } else {
        $next = cron_next($row, $last, $db);
    }
    $sql = "update Notifications set\n"
        . " retries = 0,\n"
        . " this_run = 0,\n"
        . " next_run = $next,\n"
        . " last_run = $last\n"
        . " where id = $nid\n"
        . " and next_run < 0\n"
        . " and this_run > 0\n"
        . " and enabled = 1";
    $res = redcommand($sql, $db);
    return affected($res, $db);
}


/*
    |  We can only claim a notification
    |  that is already marked as running.
    |  This is important because if someone
    |  modifies the notification while it
    |  is marked will set next_run to zero.
    */

function claim(&$env, &$row)
{
    $db  = $env['mdb'];
    $nid = $row['id'];
    $now = time();
    $sql = "update Notifications set\n"
        . " next_run = -1,\n"
        . " this_run = $now,\n"
        . " retries = retries+1\n"
        . " where id = $nid\n"
        . " and enabled = 1\n"
        . " and next_run > 0";
    $res = redcommand($sql, $db);
    return affected($res, $db);
}


/*
    |  Proof of life.
    */

function touch_notify(&$env, &$row)
{
    $db  = $env['mdb'];
    $nid = $row['id'];
    $now = time();
    $sql = "update Notifications set\n"
        . " this_run = $now\n"
        . " where id = $nid\n"
        . " and this_run > 0\n"
        . " and next_run < 0\n"
        . " and enabled = 1";
    $res = redcommand($sql, $db);
    return affected($res, $db);
}


function reset_notify($db)
{
    $sql = "update Notifications set\n"
        . " enabled = 2,\n"
        . " next_run = 0,\n"
        . " this_run = 0\n"
        . " where enabled = 1\n"
        . " and this_run > 0\n"
        . " and next_run < 0";
    $res = redcommand($sql, $db);
    $inv = affected($res, $db);
    $sql = "update Notifications set\n"
        . " next_run = 0\n"
        . " where enabled = 1\n"
        . " and this_run = 0\n"
        . " and next_run < 0";
    $res = redcommand($sql, $db);
    $clr = affected($res, $db);
    logs::log(__FILE__, __LINE__, "notify: reset queue -- $inv invalid, $clr cleared");
}


function event_list($set)
{
    $str = '';
    if ($set) {
        $txt = join(',', $set);
        $str = ",$txt,";
    }
    return $str;
}


/*
    |  We only want to send a single email 
    |  per site, but we want to list all
    |  the machines in that site that
    |  reported no notifications.
    |
    |  $set is currently indexed my site id
    |  and contains an array for each site.
    |  We want to re-arrange set to contain a 
    |  single array for each site with the 
    |  machines listed in it.
    */
function index_by_site($set)
{
    $tmp_host = array();
    reset($set);
    foreach ($set as $sid => $site_set) {
        $site = $site_set['site'];
        $host = $site_set['host'];
        $solo = $site_set['solo'];
        $zcnt = $site_set['zcnt'];

        $tmp_host[$site][$host]['solo'] = $solo;
        $tmp_host[$site][$host]['zcnt'] = $zcnt;
    }
    return $tmp_host;
}


/*
    |  $email_footer = true or false
    |  $email_footer_txt = the autotask footer
    |  $name = name of the notification
    |  $site = current site
    |  $sdb  = database handle
    | 
    |  Returns the completed footer or an empty string.
    */
function create_parsed_footer($email_footer, $email_footer_txt, $name, $site, $sdb)
{
    $finished_msg = '';
    if ($email_footer) {
        $parsed_footer = parse_email_footer($email_footer_txt, $name, $site, $sdb);
        $finished_msg  = "\n\n" . $parsed_footer . "\n";
    }
    return $finished_msg;
}


/*
    |  $site = current site
    |  $host = current machine
    |
    |  Returns the completed message.
    */
function no_event_text($site, $host)
{
    $msg  = "No event logs were retrieved for\n";
    $msg .= "site: $site\n";
    $msg .= "machine: $host\n";
    $msg .= "\n\n-------------------------------------------------\n";
    return $msg;
}


/*
    |  $email_sender = server email address
    |  $origin       = default email address
    |  $site         = current site name
    |  $sdb          = database handle
    |   
    |  Returns the $from address used in the email.
    */
function create_from_address($email_sender, $origin, $site, $sdb)
{
    $from = '';
    if ($email_sender) {
        /* We are not sending an email per site.
            |  This is a basic notification.
            */
        $from = 'From :' . find_site_email($site, $sdb);
    } else {
        /* We are using the site's from address.
            |  This is a basic notificatin with site sender
            |  turned on.          
            */
        $from = "From: $origin";
    }
    return $from;
}


/*
    | $footer = Autotask footer
    | $name   = Notification name
    | $site   = The current site
    | $db     = Database handle
    | Replace any %name%, %site%, %ticketuser% or $ticketpassword%.
    |
    | Returns the parsed footer.
    */
function parse_email_footer($footer, $name, $site, $db)
{
    // server values
    $user = server_opt('ticket_user',    $db);
    $pass = server_opt('ticket_password', $db);

    $footer = str_replace(constAutoName, $name, $footer);
    $footer = str_replace(constAutoSite, $site, $footer);
    $footer = str_replace(constAutoUser, $user, $footer);
    $footer = str_replace(constAutoPass, $pass, $footer);
    return $footer;
}


/*
    | Builds the message stating the notification time frame.
    | This is appended to the end of the email before the 
    | footer.
    |
    | $umin = posix start time
    | $umax = posix end time
    */
function compute_notification_time($umin, $umax)
{
    $interval = $umax - $umin;
    $hours    = intval($interval / 3600);
    $minutes  = intval(($interval - ($hours * 60 * 60)) / 60);
    $seconds  = $interval - ($minutes * 60 + $hours * 3600);
    $msg      = "\n\nThe actual time interval over which ";
    $msg     .= "the events were searched for is $hours hours ";
    $msg     .= "$minutes minutes, and $seconds seconds.";
    return $msg;
}


/*
    |  There are four cases to consider:
    |
    |  (($threshold) && ($solo))
    |
    |    The notification should signal if the total number of events
    |    on any given machine is greater than or equal to the threshold.
    |
    |    notify if ($minx <= $maxcount)
    |
    |  (($threshold) && (!$solo))
    |
    |    The notification should signal if the total number of events
    |    found is greater than or equal to the threshold.
    |
    |    notify if ($minx <= $count)
    |
    |  ((!$threshold) && (!$solo))
    |
    |    The notification should signal only if the total number
    |    of events is zero.
    |
    |    notify if (!$count)
    |
    |  ((!$threshold) && ($solo))
    |
    |    The notification should signal if at least one machine did
    |    not generate an event
    |
    |    notify if ($zhost)
    |
    */

function notify_run(&$env, $not, $stab, $umin, $umax)
{
    $query     = '';
    $first_sss = '';
    $id     = $not['id'];
    $sdb    = $env['sdb'];
    $mdb    = $env['mdb'];
    $pid    = $env['pid'];
    $slow   = $env['slow'];
    $dbid   = $env['dbid'];
    $timo   = $env['timo'];
    $search = notify_query($mdb, $not);
    if ($search) {
        $searchname = $search['name'];
        $query      = stripslashes($search['searchstring']);
    }
    $username = $not['username'];
    $name     = $not['name'];

    if (pfTimeDnot) {
        logs::log(__FILE__, __LINE__, "timing: nt1 $pid $id start", 0);
    }

    $restriction = '';
    $separator = '@$#';
    $set = array();
    $sql = "select * from $stab";
    $tmp = find_many($sql, $sdb);
    if ($tmp) {
        reset($tmp);
        foreach ($tmp as $key => $row) {
            $site = $row['site'];
            $host = $row['host'];
            $hid  = $row['hid'];

            /* Add this machine to the list of machines included
                    in the query.  Note that we use a little trick to
include_once both the site name and machine name. */
            if ($restriction == '') {
                $restriction = '(';
            } else {
                $restriction .= ',';
            }
            $restriction .=
                "'" . safe_addslashes($site . $separator . $host) . "'";

            /* used when we are sending a
                |  single email notification
                |  with the autotask footer
                */
            if ($first_sss == '') {
                $first_sss = $site;
            }
            $set[$hid]['site'] = $site;
            $set[$hid]['host'] = $host;
            $set[$hid]['ecnt'] = 0;
            $set[$hid]['solo'] = 0;
            $set[$hid]['zcnt'] = 0;
            $set[$hid]['zids'] = 0;
            $set[$hid]['eids'] = array();
            $set[$hid]['sids'] = array();
            $hhhh = strtolower($host);
            $ssss = strtolower($site);
            $hids[$ssss][$hhhh] = $hid;
        }
        $tmp = array();
    }
    /* Finish off the restriction if there are any conditions. */
    if ($restriction != '') {
        $restriction .= ')';
    }
    if (!$set) {

        // At the moment nanoheal has more than 60 users sans sites.
        // I think this is a good idea, but currently it generates
        // far too much output to be useful.

        //       logs::log(__FILE__, __LINE__, "notify: no active sites for notification $id, user $username",0);
        echo "[Notify] Notification '$name' not being generated because none" .
            " of the selected sites are active.<br>";
        return 0;
    }

    $pop = safe_count($set);

    debug_note("process $name for $username");
    if (($query) && ($set)) {

        // get the events

        $xnow     = $env['xnow'];
        $name     = $not['name'];

        $last_run = $not['last_run'];
        $config   = $not['config'];
        $suspend  = $not['suspend'];
        $minx     = $not['threshold'];

        /*
            |  Query restricted to recent past.
            */

        $dmin = date('m/d H:i:s', $umin);
        $dmax = date('m/d H:i:s', $umax);
        $time = $umax - $umin;
        debug_note("from $dmin to $dmax, $time seconds");

        $sql = "select * from Events FORCE INDEX (servertime)\n"
            . " where servertime between $umin and $umax\n"
            . " and ($query)";
        /* Add on the restriction for machines if it exists. */
        if ($restriction != '') {
            $sql .= " and concat(customer, '$separator', machine)"
                .  " in $restriction";
        }

        $qtime  = 0;
        touch_notify($env, $not);
        $events = redcommand_time($sql, $qtime, $sdb);

        if (pfTimeDnot) {
            logs::log(__FILE__, __LINE__, "timing: nt1 $pid $id end sql", 0);
        }

        $count  = ($events) ? mysqli_num_rows($events) : 0;
        if ($qtime > $slow) {
            $secs = microtime_show($qtime);
            $stat = "p:$pid,n:$id,d:$dbid,e:$count,u:$username";
            $text = "notify: slow ($stat) in $secs\n$sql";
            logs::log(__FILE__, __LINE__, $text, 0);
        }

        /*
            |  If our select takes more than TIMO seconds,
            |  this probably means the database is locked.
            |
            |  In any event, this means that a later instance
            |  of c-notify has probably already assumed this
            |  process has crashed, and has taken responsibility
            |  for cleaning up the mess.
            |
            |  In this case the best thing to do is to just
            |  exit without attempting to change anything.
            */

        if (($qtime > $timo) && ($timo > 0)) {
            $secs = microtime_show($qtime);
            $stat = "p:$pid,n:$id,d:$dbid,t:$timo,u:$username";
            $text = "notify: suicide ($stat) in $secs";
            logs::log(__FILE__, __LINE__, $text, 0);
            return -1;
        }

        if (!$events) {
            // send error mail

            logs::log(__FILE__, __LINE__, "disable notify $name ($id) for $username.", 0);
            send_error_mail($env, $not, $query);
            //notify_disable($mdb, $id);
        } else {

            /*
                |  send out notifications if there are at least as many
                |  matching rows as the threshold.
                |
                |  [In the special case where threshold was set to 0,
                |   notifications should be sent if and only if
                |   0 events occurred overall (no machines have events)].
                */

            $numevents = $count;
            $email    = $not['email'];
            $links    = $not['links'];
            $defmail  = $not['defmail'];
            $priority = $not['priority'];
            $console  = $not['console'];
            $to       = $not['emaillist'];
            $days     = $not['days'];
            $solo     = $not['solo'];
            $email_footer     = $not['email_footer'];
            $email_per_site   = $not['email_per_site'];
            $email_footer_txt = $not['email_footer_txt'];
            $email_sender     = $not['email_sender'];
            $list      = array();
            $site_msg  = array();
            $maxcount  = 0;
            $zero_notn = 0;
            $zhost     = 0;

            // populate them with event data
            if ($numevents) {
                mysqli_data_seek($events, 0);
            }

            while ($event = mysqli_fetch_assoc($events)) {
                $idx = $event['idx'];
                $sss = strtolower($event['customer']);
                $hhh = strtolower($event['machine']);

                /*
                    |  Used for a notification not sending mail 
                    |  for every site. Save the first site from $events
                    |  and use it as the substitute for %site%
                    |  in parse_email_footer().               
                   */
                if (($first_sss == '') && (!$email_per_site)) {
                    $first_sss = $sss;
                }

                $hid = @intval($hids[$sss][$hhh]);
                if ($hid) {
                    $set[$hid]['ecnt']++;
                    $set[$hid]['eids'][] = $idx;
                } else {
                    debug_note("mapping error for $hhh at $sss event $idx");
                }
            }

            /*
                |  In the case of a zero event notification Alex wants
                |  the count on the email subject line to repesent the
                |  number of zero-event machines, instead of the normal
                |  event count.
                */

            reset($set);
            foreach ($set as $hid => $row) {
                $ecnt = $row['ecnt'];
                if (($ecnt) && ($minx <= $ecnt)) {
                    $set[$hid]['sids'] = $row['eids'];
                    $set[$hid]['solo'] = $row['ecnt'];
                }
                if ($ecnt > $maxcount) {
                    $maxcount = $ecnt;
                }
                if (!$ecnt) {
                    $set[$hid]['zcnt'] = 1;
                    $zhost++;
                }
            }
            debug_note("zhost:$zhost, pop:$pop, count:$count, maxcount:$maxcount");

            /*
                |  Ordinarily there have to be threshold events total,
                |  on all the machines, within the time interval in order
                |  to trigger the notification.
                |
                |  Solo means that there must be at least threshold
                |  events on ANY SINGLE MACHINE within the time interval
                |  before we trigger the notification.  This is a much
                |  more restrictive test.
                |
                |  [In the special case where threshold was set to 0,
                |   notification should be sent if and only if
                |   0 events occurred on at least one machine].
                */

            if ($solo && ((2 <= $minx) && ($minx <= $count) || ($minx == 0))) {
                /*
                    |  We're only going to report events from machines
                    |  that have more than $minx events.  So, we need to
                    |  recalculate the number of events to be reported.
                    |
                    |  [In the special case where threshold was set to 0,
                    |   notification should be sent only for machines
                    |   where 0 events occurred].
                    */

                $count = 0;
                if ($minx != 0) {
                    reset($set);
                    foreach ($set as $hid => $row) {
                        $count += $row['solo'];
                    }
                } elseif (($minx == 0) && ($zhost)) {
                    // zero-event notification; $count = 0
                    $zero_notn = 1;
                }
            } else {
                $maxcount = $count;
                $solo = 0;

                /*
                    |  [In the special case where threshold was set to 0,
                    |   notification should be sent if and only if only
                    |   0 events occurred overall (no machines have events)].
                    */

                if ((!$minx) && (!$numevents)) {
                    // zero-event notification; $count = numevents = 0
                    $zero_notn = 1;
                }
            }

            echo "[Notify] '$name' for '$username' -- $count, $maxcount events<br />";

            if (($maxcount < $minx) || ($minx == 0 && !$zero_notn)) {
                echo "[Notify] Not enough matching rows";
                if ($minx == 0) {
                    if ($solo)
                        echo " ($zhost == 0)<br />";
                    else
                        echo " ($numevents > 0)<br />";
                } else {
                    echo " ($maxcount < $minx)<br />";
                }

                ((mysqli_free_result($events) || (is_object($events) && (get_class($events) == "mysqli_result"))) ? true : false);
            } else {
                echo "[Notify] Starting Notification $name for user $username $count events<br />";

                $secs = $not['seconds'];
                $interval = round($secs / 60);
                $units    = "minutes";
                if ($interval > 4320) {
                    $interval = intval($interval / 1440);
                    $units    = "days";
                }
                if ($interval > 240) {
                    $interval = intval($interval / 60);
                    $units    = "hours";
                }

                $autotask = @$not['autotask'];

                if (($email) || ($autotask)) {
                    $server   = $env['server'];
                    $base     = $env['base'];
                    $url      = "$base/event/detail.php";
                    $number   = ($minx == 0) ? $zhost : $count;

                    $subject = "$name: $number events";

                    /*
                        |  normal:
                        |    You asked to be notified about at least $minx
                        |    occurence(s) of the following event within
                        |    $interval $units:
                        |
                        |  solo:
                        |    You asked to be notified about at least $minx
                        |    occurences(s) of the following event on any
                        |    single machine within interval $units:
                        |
                        |  zero:
                        |    You asked to be notified if the following event
                        |    did not take place on one or more machines
                        |    within $interval $units:
                        */

                    $msg = 'You asked to be notified';

                    if ($minx == 0) {
                        $msg .= " if the following event did not\ntake";
                        $msg .= ' place on one or more machines';
                    } else {
                        $msg .= " about at least $minx occurence(s)";
                        $msg .= " of the\nfollowing event";
                        if ($solo) {
                            $msg .= " on any single machine";
                        }
                    }
                    $msg .= " within $interval $units:\n";
                    $msg .= "Saved Search Name: $searchname\n";
                    $msg .= "Saved Search Query: $query";

                    // we will re-use this text when we are building the email
                    if ($email_per_site) {
                        $first_para = $msg;
                    }
                    if ($minx != 0) {
                        $msg .= "\n\nThe following $count event(s) occurred:\n\n";
                        $msg .= "-------------------------------------------------";
                    }

                    // for restricted 0-event notifications, list 0-event machine(s)
                    if (($zero_notn) && ($solo)) {
                        $msg .= "\n\n-------------------------------------------------\n";

                        reset($set);
                        foreach ($set as $hid => $row) {
                            if ($row['zcnt']) {
                                $site = $row['site'];
                                $host = $row['host'];
                                $msg .= no_event_text($site, $host);
                            }
                        }
                    } else {
                        // Erase the contents of msg, we only want the events
                        if ($email_per_site) {
                            $msg = '';
                        }

                        $cfg = filter(explode(':', $config));

                        if ($numevents) {
                            mysqli_data_seek($events, 0);
                        }
                        while ($event = mysqli_fetch_assoc($events)) {
                            $idx = $event['idx'];
                            $hhh = strtolower($event['machine']);
                            $sss = strtolower($event['customer']);
                            $hid = @intval($hids[$sss][$hhh]);
                            $num = @intval($set[$hid]['ecnt']);

                            if (($minx) && ((!$solo) || ($minx <= $num))) {
                                $msg .= "\n";

                                $event['entered']    = mysqltime($event['entered']);
                                $event['servertime'] = mysqltime($event['servertime']);

                                reset($cfg);
                                foreach ($cfg as $key => $data) {
                                    $valu = $event[$data];
                                    if (is_integer($valu)) {
                                        $valu = strval($valu);
                                    }
                                    if ($valu) {
                                        $msg .= "$data: $valu\n";
                                    }
                                }
                                if ($links) {
                                    $msg .= "\ndetail: $url?eid=$idx";
                                }
                                $msg .= "\n\n-------------------------------------------------\n";
                            }
                            if ($email_per_site) {
                                /*
                                    | $site = site where the current event is
                                    |
                                    | $site_msg[$site]['msg'] = we store the 
                                    |   event message here in a string.
                                    |
                                    | $site_msg[$site]['count'] = the number of
                                    |   events for a particular site.
                                   */

                                $site = $event['customer'];
                                if (!isset($site_msg[$site])) {
                                    // first entry for a site set count to 1
                                    $site_msg[$site]['msg']   = $msg;
                                    $site_msg[$site]['count'] = 1;
                                } else {
                                    // increment event count at $site
                                    $site_count               = $site_msg[$site]['count'];
                                    $site_msg[$site]['count'] = $site_count + 1;
                                    $site_msg[$site]['msg']  .= $msg;
                                }
                                // clear the message string
                                $msg = '';
                            }
                        }
                    }
                }
                ((mysqli_free_result($events) || (is_object($events) && (get_class($events) == "mysqli_result"))) ? true : false);

                $def = $env['userdata'][$username]['notify_mail'];
                $to  = email_list($to, $defmail, $def);

                if ((($email) && ($to)) || ($autotask)) {
                    $msg   .= compute_notification_time($umin, $umax);
                    $origin = $env['origin'];

                    if ($origin == '') {
                        $server = $env['server'];
                        $origin = "notify@$server";
                    }

                    // We are not sending an email per site
                    if (!$email_per_site) {
                        /*
                            | When parsing the email_footer_txt we use
                            |  $first_sss (the first site returned from $events).
                            |
                            |  This is a basic notification with the footer 
                            |  turned on.       
                           */
                        $msg .= create_parsed_footer(
                            $email_footer,
                            $email_footer_txt,
                            $name,
                            $first_sss,
                            $sdb
                        );

                        $from = create_from_address($email_sender, $origin, $first_sss, $sdb);

                        if (($email) && ($to)) {
                            debug_note("notify2: mail sent to:$to subject: $subject");
                            mail($to, $subject, $msg, $from);
                        }
                    } else {
                        /*
                            | We are sending 1 email per notification per site.
                            |
                            |  The array is indexed:
                            |  array => [site 1] =>
                            |                      [msg]
                            |                      [count]
                            |           [site 2] ...
                            |
                            | $first_para = contains the message section:
                            |  You asked to be notified ..
                            |  Saved Search Name:  ..
                            |  Saved Search Query: ..
                            |  
                            | $built_msg = contains $first_para, plus the count of events.
                            | 
                            | $msg = the finished message to email.
                            |  We may append the autotask footer onto this and if so
                            |  we parse if for any occurance of constAutoSite or constAutoName.
                            | 
                            | We must generate the time frame in which the events were 
                            |  searched for by calling compute_notification_time().
                            |
                            | If the user wants to use the site email as the from address
                            |  we must set that by calling find_site_email().
                            |  Otherwise we use the default from address.
                            |
                            | We mail the message and clear the contents of $msg.
                           */

                        //common values between 0 and > 0 notifications
                        $notify_time = compute_notification_time($umin, $umax);

                        if ($site_msg) {
                            reset($site_msg);
                            foreach ($site_msg as $site => $event_msg) {
                                $msg   = $event_msg['msg'];
                                $count = $event_msg['count'];

                                $built_msg  = $first_para;
                                $built_msg .= "\n\nThe following $count event(s) occurred:\n\n";
                                $built_msg .= "-------------------------------------------------\n";

                                $msg     = $built_msg . $msg;
                                $subject = "$name: $count events";
                                $msg    .= $notify_time;

                                if ($autotask) {
                                    AUTO_SendNotification(
                                        $subject,
                                        $msg,
                                        $not['username'],
                                        $site,
                                        $not,
                                        $mdb
                                    );
                                }

                                $msg .= create_parsed_footer(
                                    $email_footer,
                                    $email_footer_txt,
                                    $name,
                                    $site,
                                    $sdb
                                );

                                $from = create_from_address($email_sender, $origin, $site, $sdb);

                                if (($email) && ($to) && ($email_per_site)) {
                                    mail($to, $subject, $msg, $from);
                                    debug_note("notify3: mail sent to:$to subject:$subject");
                                }
                                $msg = '';
                            }
                        } else {
                            /*
                                |  We are sending an email per site
                                |  however this is a notification for
                                |  zero event reports.
                                |
                                |  We can also get here if we have a 
                                |  THRESHOLD = 0 and RESTRICTED = yes || no
                                |  and we are sending an email per site.
                               */

                            // True if the notifications has
                            // Restricted set to Yes.
                            $solo_notification = $not['solo'];

                            // create a new array indexed by the site name 
                            $tmp_set = index_by_site($set);

                            debug_note('<br> print_r(tmp_set) <br>');
                            print_r($tmp_set);

                            reset($tmp_set);
                            foreach ($tmp_set as $site_name => $site_set) {
                                $finished_msg = '';
                                $tmp_text     = "\n\n-------------------------------------------------\n";
                                $send_mail    = false;

                                reset($site_set);
                                foreach ($site_set as $host_name => $site_values) {
                                    $site = $site_name;
                                    $host = $host_name;

                                    if ($site_values['zcnt'] == 1) {
                                        /*
                                            |  Any site that generates no events will send an email
                                            |  from that site listing the machine that did not generate
                                            |  any events.
                                            */
                                        if ($solo_notification) {
                                            $send_mail = true;
                                            $tmp_text .= no_event_text($site, $host);
                                        } else {
                                            /*
                                                |  For any site to send an email we must generate
                                                |  no events on all machines. We can check the value
                                                |  of numevents to see how many events where generated
                                                |  for each user.
                                                */
                                            if ($numevents == 0) {
                                                $send_mail     = true;
                                                $finished_msg  = $first_para . $notify_time;
                                            }
                                        }
                                    }
                                }

                                $tmp_text      = $first_para . $tmp_text;
                                $tmp_text     .= $notify_time;
                                $finished_msg .= $tmp_text;

                                if ($autotask) {
                                    AUTO_SendNotification(
                                        $subject,
                                        $finished_msg,
                                        $not['username'],
                                        $site,
                                        $not,
                                        $mdb
                                    );
                                }

                                $finished_msg .= create_parsed_footer(
                                    $email_footer,
                                    $email_footer_txt,
                                    $name,
                                    $site,
                                    $sdb
                                );
                                $from = create_from_address($email_sender, $origin, $site, $sdb);

                                if (($send_mail) && (($email) && ($to))) {
                                    debug_note("<br> notify: mail sent to:$to "
                                        . "<br> from:$from "
                                        . "<br> subject:$subject"
                                        . "<br> finished_msg = $finished_msg");
                                    mail($to, $subject, $finished_msg, $from);
                                }
                            }
                        }
                    } // else: is an email per site
                }

                // for zero-event notifications, add entries to Events table
                // (especially needed for console detail)
                if ($zero_notn) {
                    reset($set);
                    foreach ($set as $hid => $row) {
                        if ($row['zcnt']) {
                            $site = $row['site'];
                            $host = $row['host'];
                            $zid  = insert_zero_event($site, $host, $username, $name, $xnow, $mdb);
                            if ($zid) {
                                $set[$hid]['zids'] = $zid;
                            }
                        }
                    }
                }

                // for the console, we group events by site
                if ($console) {
                    $sites = array();
                    reset($hids);
                    foreach ($hids as $site => $list) {
                        $uniq = strtolower($site);
                        $sites[$uniq]['solo'] = 0;
                        $sites[$uniq]['ecnt'] = 0;
                        $sites[$uniq]['zcnt'] = 0;
                        $sites[$uniq]['eids'] = array();
                        $sites[$uniq]['zids'] = array();
                        $sites[$uniq]['sids'] = array();
                    }

                    reset($set);
                    foreach ($set as $hid => $row) {
                        $zids = $row['zids'];
                        $eids = $row['eids'];
                        $sids = $row['sids'];
                        $site = $row['site'];
                        $uniq = strtolower($site);
                        $sites[$uniq]['name']   = $site;
                        $sites[$uniq]['ecnt']  += $row['ecnt'];
                        $sites[$uniq]['solo']  += $row['solo'];
                        $sites[$uniq]['zcnt']  += $row['zcnt'];
                        if ($zids) {
                            $sites[$uniq]['zids'][] = $zids;
                        }
                        reset($eids);
                        foreach ($eids as $key => $eid) {
                            $sites[$uniq]['eids'][] = $eid;
                        }
                        reset($sids);
                        foreach ($sids as $key => $sid) {
                            $sites[$uniq]['sids'][] = $sid;
                        }
                    }

                    reset($sites);
                    foreach ($sites as $sss => $row) {
                        $zcnt = $row['zcnt'];
                        if (($zero_notn) && ($zcnt)) {
                            $site = $row['name'];
                            $zids = $row['zids'];
                            $text = event_list($zids);
                            log_notify(
                                $mdb,
                                $xnow,
                                $name,
                                $site,
                                $username,
                                safe_count($zids),
                                $text,
                                $config,
                                $id,
                                $priority,
                                $days
                            );
                        }
                        if ($minx > 0) {
                            $sids = $row['sids'];
                            $eids = $row['eids'];
                            $list = ($solo) ? $sids : $eids;
                            if ($list) {
                                // only generate a console record for this site
                                // if there is at least one event from this site.
                                $text = event_list($list);
                                $site = $row['name'];

                                log_notify(
                                    $mdb,
                                    $xnow,
                                    $name,
                                    $site,
                                    $username,
                                    safe_count($list),
                                    $text,
                                    $config,
                                    $id,
                                    $priority,
                                    $days
                                );
                            }
                        }
                    }
                }
            }
        }
    } else {
        debug_note("query $query");
    }

    if (pfTimeDnot) {
        logs::log(__FILE__, __LINE__, "timing: nt1 $pid $id end", 0);
    }
    return 1;
}


function drop_temp_table($name, $db)
{
    if ($name) {
        $sql = "drop table if exists $name";
        redcommand($sql, $db);
    }
}

function create_temp_table($name, $db)
{
    drop_temp_table($name, $db);
    if ($name) {
        $sql = "create temporary table $name\n"
            . " (id int(11) not null primary key)";
        redcommand($sql, $db);
    }
}


function remove_temp($gid, $name, $db)
{
    $num = 0;
    $set = array();
    $tmp = array();
    if (($gid) && ($name)) {
        $sql = "select T.id from\n"
            . " $name as T,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
            . " where M.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid in ($gid)\n"
            . " and M.censusuniq = C.censusuniq\n"
            . " and C.id = T.id\n"
            . " group by T.id\n"
            . " order by T.id";
        $set = find_many($sql, $db);
    }
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $tmp[] = $row['id'];
        }
    }
    if ($tmp) {
        $txt = join(',', $tmp);
        $sql = "delete from $name\n"
            . " where id in ($txt)";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
        debug_note("$num machines removed from $name");
    }
    return $num;
}


/*
    |  These have to be different from the field names
    |  in the events table, so that the existing saved
    |  searches do not become ambiguous.
    */

function slave_table($name, $sdb)
{
    if ($name) {
        $def = 'not null default';
        $sql = "create temporary table $name (\n"
            . " hid int(11) not null,\n"
            . " site varchar(50) $def '',\n"
            . " host varchar(64) $def '',\n"
            . " primary key (hid),\n"
            . " key site (site),\n"
            . " key host (host)\n"
            . ")";
        drop_temp_table($name, $sdb);
        redcommand($sql, $sdb);
    }
}


/*
    |  We have to construct the list of included machines
    |  in the master database, but the actual query is
    |  going to happen in the slave database.  If we
    |  are doing not doing replication then it's simple
    |  to directly compute TempNotify ... however, it's
    |  more work with replication.
    */

function notify_once(&$env, $not, $umin, $umax)
{
    $pop  = 0;
    $num  = 0;
    $set  = array();
    $now  = $env['xnow'];
    $mdb  = $env['mdb'];
    $sdb  = $env['sdb'];
    $rep  = $env['rep'];
    $susp = $not['suspend'];
    $user = $not['username'];
    $igid = $not['group_include'];
    $egid = $not['group_exclude'];
    $sgid = $not['group_suspend'];
    $mtab = 'CodeNotify';   // master database
    $stab = 'TempNotify';   // slave database

    create_temp_table($mtab, $mdb);

    //  $rep = 1;  // just for testing ...

    $mgm = '';
    $grp = '';
    $mgroupuniqs = '';
    if ($igid) {
        /* $igid is a list of mgroupids seperated by commas.  Convert
                into mgroupuniqs to make this insert less expensive. */
        $sql = 'SELECT mgroupuniq FROM ' . $GLOBALS['PREFIX'] . 'core.MachineGroups WHERE mgroupid '
            . "IN ($igid)";
        $set = find_many($sql, $mdb);
        if ($set) {
            foreach ($set as $key => $row) {
                if ($mgroupuniqs != '') {
                    $mgroupuniqs .= ',';
                }
                $mgroupuniqs .= '\'' . $row['mgroupuniq'] . '\'';
            }
        }
    }
    $qu  = safe_addslashes($user);
    if ($mgroupuniqs != '') {
        $sql = "INSERT INTO $mtab SELECT DISTINCT Census.id FROM "
            . $GLOBALS['PREFIX'] . 'core.Census '
            . 'LEFT JOIN ' . $GLOBALS['PREFIX'] . 'core.MachineGroupMap ON (Census.censusuniq='
            . 'MachineGroupMap.censusuniq) LEFT JOIN ' . $GLOBALS['PREFIX'] . 'core.Customers '
            . 'ON (Census.site=Customers.customer) WHERE Customers.'
            . "username='$qu' AND MachineGroupMap.mgroupuniq IN ("
            . "$mgroupuniqs)";
    } else {
        $sql = "insert into $mtab\n"
            . " select H.id from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as H,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U\n"
            . " where   U.username  = '$qu'\n"
            . " and U.customer = H.site\n"
            . " group by H.id";
    }
    $res = redcommand($sql, $mdb);
    $pop = affected($res, $mdb);
    debug_note("$mtab population $pop machine(s) ...");
    if ($pop > 0) {
        $num = remove_temp($egid, $mtab, $mdb);
        $pop = $pop - $num;
    }
    if (($pop > 0) && ($now < $susp)) {
        $num = remove_temp($sgid, $mtab, $mdb);
        $pop = $pop - $num;
    }
    if ($pop > 0) {
        slave_table($stab, $sdb);
        if ($rep) {
            $sql = "select H.id, H.site, H.host\n"
                . " from " . $GLOBALS['PREFIX'] . "core.Census as H,\n"
                . " $mtab as T\n"
                . " where H.id = T.id\n"
                . " and H.deleted = 0\n"
                . " group by H.id\n"
                . " order by H.site, H.host";
            $set = find_many($sql, $mdb);
        }
        if (($rep) && ($set)) {
            reset($set);
            foreach ($set as $key => $row) {
                $hid  = $row['id'];
                $site = safe_addslashes($row['site']);
                $host = safe_addslashes($row['host']);
                $xx[] = "($hid,'$site','$host')";
            }
            $val = join(",\n ", $xx);
            $sql = "insert into $stab values\n $val";
            $res = redcommand($sql, $sdb);
            $pop = affected($res, $sdb);
        }
        if (($rep) && (!$set)) {
            $pop = 0;
        }
        if (!$rep) {
            $sql = "insert into $stab\n"
                . " select H.id, H.site, H.host from\n"
                . " " . $GLOBALS['PREFIX'] . "core.Census as H,\n"
                . " $mtab as T\n"
                . " where H.id = T.id\n"
                . " group by H.id\n"
                . " order by H.site, H.host";
            $res = redcommand($sql, $sdb);
            $pop = affected($res, $sdb);
        }
        debug_note("$stab population $pop machine(s) ...");
    }
    drop_temp_table($mtab, $mdb);
    if ($pop > 0) {
        $num = notify_run($env, $not, $stab, $umin, $umax);
    } else {
        $n_name = $not['name'];
        $u_name = $not['username'];
        logs::log(__FILE__, __LINE__, "c-notify: user($u_name) notification($n_name) contains zero machines");
    }
    drop_temp_table($stab, $sdb);
    return $num;
}


function proximity_failure(&$env, &$row, $db)
{
    $nid  = $row['id'];
    $name = $row['name'];
    $user = $row['username'];
    $dst  = $row['emaillist'];
    $rtry = $row['retries'];

    $pid  = $env['pid'];
    $src  = $env['origin'];
    $cmp  = $env['company'];
    $def  = $env['userdata'][$user]['notify_mail'];
    $serv = $env['server'];
    $base = $env['base'];
    $dst  = email_list($dst, 1, $def);

    $now = time();
    $sql = "update Notifications set\n"
        . " retries = 0\n"
        . " where id = $nid";
    redcommand($sql, $db);

    $date = datestring($now);
    $stat = "p:$pid,n:$nid,r:$rtry,u:$user";
    $text = "notify: proximity failure ($stat) $name";
    logs::log(__FILE__, __LINE__, $text, 0);
    debug_note($text);

    if ($dst == '') {
        $dst = $env['support'];
    }

    if ($src == '') {
        $src = "notify@$serv";
    }

    if ($dst) {
        $frm  = "From: $src";
        $url  = "$base/event/notify.php?act=edit&nid=$nid";
        $sub  = 'Notification Proximity Failure';
        $msg  = "There is a problem running the notification "
            . "you requested named $name.\n\n"
            . "The most likely issue is that the server is "
            . "too busy.\n\n"
            . "You can edit your Notification here:\n\n"
            . "\t$url\n\n$cmp.\n\n$date\n\n";
        $good = mail($dst, $sub, $msg, $frm);
        if ($good) {
            echo "<p>sent mail to $dst</p>\n";
        } else {
            $text = "notify: mail failure ($stat) $name";
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
    }
}


/*
    |  Global notifications are processed once for the real owner, and
    |  then once again for every other existing user.
    |
    |  We only do this if the user does not own a local notification with the
    |  same name as a global one.
    */

function notify(&$env, $row)
{
    $aaa  = microtime();
    $mdb  = $env['mdb'];
    $pid  = $env['pid'];
    $nid  = $row['id'];
    $name = $row['name'];
    $type = $row['ntype'];
    $user = $row['username'];
    $umin = $row['last_run'];
    $skip = $row['skip_owner'];
    $umax = $env['xnow'] - 1;
    $global = $row['global'];
    if ($type == constScheduleOneShot) {
        $shed = cron_load_schedule($nid, $mdb);
        if ($shed) {
            $umin = $shed[constCronUMin];
            $umax = $shed[constCronUMax];
        }
    }
    $good = true;
    $span = $umax - $umin;
    $stat = "p:$pid,n:$nid,u:$user,s:$span";
    if ($type == constScheduleProximity) {
        $time = time();
        $shed = cron_load_schedule($nid, $mdb);
        if ($shed) {
            $prox = $shed[constCronProx];
            $fail = $shed[constCronFail];
            if (($prox) && ($fail)) {
                $when = $time - $prox;
                $next = cron_next_run($shed, $when);
                $good = ($next <= $time);
            }
        }
        if (!$good) {
            $next = cron_next_run($shed, $time);
            $rtry = $row['retries'] + 1;
            $sql  = "update Notifications set\n"
                . " next_run = $next,\n"
                . " retries = $rtry\n"
                . " where id = $nid";
            redcommand($sql, $mdb);
            $stat = "$stat,r:$rtry,f:$fail,x:$prox";
            $text = "notify: tardy ($stat) $name";
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
            if ($rtry > $fail) {
                $row['retries'] = $rtry;
                proximity_failure($env, $row, $mdb);
            }
            return 0;
        }
    }

    $total = -1;
    if (claim($env, $row)) {
        // Run for owner
        if ((!$skip) || (!$global)) {
            $total = notify_once($env, $row, $umin, $umax);
            if ($total < 0) return $total;
        }
        $users = $env['users'];
        if (($global) && ($users)) {
            $mdb  = $env['mdb'];
            $name = $row['name'];
            $user = $row['username'];
            $qn   = safe_addslashes($name);
            reset($users);
            foreach ($users as $key => $data) {
                // not for the real owner, he's done already.
                if ($data != $user) {
                    $qu  = safe_addslashes($data);
                    $sql = "select * from Notifications\n"
                        . " where username = '$qu'\n"
                        . " and name = '$qn'";
                    $set = find_many($sql, $mdb);
                    if (!$set) {
                        // local with identical name overrides global
                        // even if the local is disabled.

                        $row['username'] = $data;
                        $row['global'] = 0;
                        $row['owner'] = 0;
                        $done = notify_once($env, $row, $umin, $umax);
                        if ($done < 0) return $done;
                        $total += $done;
                    }
                }
            }
        }

        if (finished($env, $row) <= 0) {
            $total = -1;
        }
    }

    $bbb  = microtime();
    $sec  = microtime_diff($aaa, $bbb);
    $time = microtime_show($sec);
    if ($type == constScheduleOneShot) {
        $text = "notify: one shot ($stat) in $time, $name";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
        kill_notify($nid, $mdb);
    }
    $text = "notify: done ($stat) in $time, $name";
    if (10 <= $sec) {
        logs::log(__FILE__, __LINE__, $text, 0);
    }
    debug_note($text);
    return $total;
}


function update_zero($db)
{
    $sql = "select * from Notifications\n"
        . " where next_run = 0\n"
        . " and enabled = 1";
    $now = time();
    $num = 0;
    $set = find_many($sql, $db);
    reset($set);
    foreach ($set as $key => $row) {
        $nid  = $row['id'];
        $last = $row['last_run'];
        $secs = $row['seconds'];
        $type = $row['ntype'];
        if ($type == constScheduleClassic) {
            if ($last + $secs < $now) {
                $last = $now - $secs;
            }
            $next = $last + $secs;
        } else {
            if ($last <= 0) {
                $last = $now;
            }
            $next = cron_next($row, $last, $db);
        }
        $sql  = "update Notifications set\n"
            . " next_run = $next,\n"
            . " last_run = $last\n"
            . " where id = $nid";
        $res  = redcommand($sql, $db);
        $num += affected($res, $db);
    }
    if ($set) {
        debug_note("$num updated");
    }
}


/*
    |  Master database should be used for insert, delete, and update
    |  Slave database should be used for select only.
    |
    |  Since there can be a slight delay updating the slave, we
    |  want to run the notifications slightly in the past..
    */

function build_env($mdb, $now, $pid, $comp, $debug)
{
    $sdb = db_cron($mdb);

    $xnow = $now;
    $bias = 0;
    if ($sdb) {
        $bias = server_int('cron_bias', 120, $mdb);
        if ($bias > 0) {
            $xnow = $now - $bias;
        }
        $replicated = 1;
        $dbid = 'slave';
        debug_note("slave database bias: $bias, mdb:$mdb, sdb:$sdb");
    } else {
        $replicated = 0;
        $dbid = 'master';
        $sdb = $mdb;
        debug_note("normal database.");
    }

    $ssl     = server_int('ssl', 1, $mdb);
    $def     = ($ssl) ? 443 : 80;
    $port    = server_int('port', $def, $mdb);
    $date    = datestring($xnow);
    $support = server_opt('support_email', $mdb);
    $slow    = (float) server_def('slow_query_notify', 20, $mdb);
    $company = server_opt('company_name', $mdb);
    $origin  = server_opt('event_notify_sender', $mdb);
    $timo    = server_int('notify_timeout', 1800, $mdb);

    $server  = server_name($mdb);
    $host    = server_href($mdb);
    $base    = base_directory($host, $comp);

    $users          = userlist($mdb);
    $userdata       = usertree($mdb);
    $siteaccesstree = siteaccesstree($users, $mdb);
    $active         = find_active_sites($mdb);

    $env = array();

    $env['sdb']      = $sdb;
    $env['mdb']      = $mdb;
    $env['now']      = $now;
    $env['pid']      = $pid;
    $env['rep']      = $replicated;
    $env['slow']     = $slow;
    $env['base']     = $base;
    $env['bias']     = $bias;
    $env['xnow']     = $xnow;
    $env['timo']     = $timo;
    $env['dbid']     = $dbid;
    $env['users']    = $users;
    $env['debug']    = $debug;
    $env['server']   = $server;
    $env['origin']   = $origin;
    $env['support']  = $support;
    $env['company']  = $company;
    $env['userdata'] = $userdata;

    $env['siteaccesstree'] = $siteaccesstree;
    $env['active']         = $active;
    if ($replicated) {
        db_change($GLOBALS['PREFIX'] . 'event', $sdb);
    }
    return $env;
}



/*
    |  Process Notifications
    |
    |  This is a very sensitive piece of code... It involves locking the
    |  notification table, so if anything were to go wrong during run-time,
    |  a frozen database is possible.
    |
    |  This process is invoked by cron every 60 seconds, 1440 times a day.
    |
    |  The problem with this is that sometimes it can take a long time to
    |  process a notification, it is normal and expected for another
    |  instance of this process to start before the first is finished.
    |
    |  The trick is to keep them from trying to process the same notification.
    |  We accomplish this trick by updating the last_run date before we do
    |  the work, and also by keeping the notifications table locked
    |  during the scheduling phase.
    |
    |  We do this in two passes.  The first pass runs with the notifications
    |  table locked, and does nothing but marking and scheduling.  This pass
    |  should always finish very quickly.  We update the last_run field of
    |  our scheduled items so that subsequent invocations of this process
    |  will leave them alone.
    |
    |  The second pass runs with all tables unlocked, and does all the actual work
    |  involved in processing the notifications.  This pass may take a long time
    |  to finish, but we don't mind because the tables are now unlocked, and future
    |  invocations of this process should leave our events alone, since they have
    |  already been marked as complete.
    */

function notify_cron($mdb, $now, $pid, $comp, $debug, $single)
{
    $total = 0;
    $env  = array();
    $ntfy = array();

    db_change($GLOBALS['PREFIX'] . 'event', $mdb);

    if ($single) {
        // very good for debugging ...

        $when = $now - 3600;
        $sql  = "update Notifications set\n"
            . " last_run = $when\n"
            . " where id = $single";
        $res  = redcommand($sql, $mdb);
        $sql  = "select * from Notifications\n"
            . " where id = $single";
        $ntfy = find_one($sql, $mdb);
        $more = false;
    } else {
        $more = true;
        $ntfy = find_next($now, $mdb);
    }

    if ($ntfy) {
        set_time_limit(0);
        ignore_user_abort(true);

        if (pfTimeDnot) {
            logs::log(__FILE__, __LINE__, "timing: ntf $pid end pick, start processing", 0);
        }

        $env  = build_env($mdb, $now, $pid, $comp, $debug);
        $xnow = $env['xnow'];
        if ($single) {
            $total = notify($env, $ntfy);
            $more  = false;
        } else {
            $ntfy = find_next($xnow, $mdb);
        }
    }

    if (!$ntfy) {
        $date = date('m/d H:i:s', $now);
        echo "<p>Nothing to be done $date</p>\n";
    }

    $done = 0;
    while (($more) && ($ntfy)) {
        if (checkpid($pid, $mdb)) {
            $done = notify($env, $ntfy);
            if ($done < 0) {
                $more = false;
            }
        } else {
            $more = false;
        }

        if ($more) {
            $total += $done;
            $ntfy = find_next($xnow, $mdb);
        }
    }

    if ($more) {
        update_zero($mdb);
    }
    return $total;
}


function notify_lock($pid, $db)
{
    $busy = server_int(constPurgeLock, 0, $db);
    if ($busy) {
        return false;
    }
    $good = false;
    $name = constNotifyLock;
    if (update_opt($name, 1, $db)) {
        // we have the lock, update mod time.
        opt_update($name, 1, 0, $db);
        $good = true;
    } else {
        $row = find_opt($name, $db);
        if (!$row) {
            if (opt_insert($name, 1, 0, $db)) {
                $good = true;
                $text = "notify: $name created";
            } else {
                $text = "notify: could not create $name";
            }
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
    }
    if ($good) {
        $name = constNotifyPid;
        $row  = find_opt($name, $db);
        if ($row) {
            opt_update($name, $pid, 0, $db);
        } else {
            if (opt_insert($name, $pid, 0, $db)) {
                $text = "notify: $name created";
            } else {
                $text = "notify: could not create $name";
            }
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
    }
    return $good;
}


/*
    |  We got the server lock to begin with, and we should still
    |  have it now.  But if someone else has stolen it away, we
    |  should just log the fact and leave WITHOUT clearing the
    |  lock flag.
    */

function notify_unlock($pid, $db)
{
    if (checkpid($pid, $db)) {
        opt_update(constNotifyPid, 0, 0, $db);
        opt_update(constNotifyLock, 0, 0, $db);
    }
}


/*
    |  If notify_timeout is zero, then we should be prepared
    |  to wait forever for the lock to be released.
    */

function notify_steal($pid, $db)
{
    $out  = false;
    $lock = server_int(constNotifyLock, 0, $db);
    $timo = server_int(constNotifyTimo, 0, $db);
    $busy = server_int(constPurgeLock, 0, $db);
    if (($lock) && ($timo) && (!$busy)) {
        $row = find_opt(constNotifyPid, $db);
        $now = time();
        if ($row) {
            $own = $row['value'];
            $mod = $row['modified'];
            $txt = date('H:i:s', $mod);
            $age = $now - $mod;
            echo "<p>Queue Locked by <b>$own</b> at <b>$txt</b> ($age seconds)</p>\n";
            if ($mod + $timo < $now) {
                $sql  = "update  " . $GLOBALS['PREFIX'] . "event.Notifications set\n"
                    . " next_run = 0,\n"
                    . " this_run = 0\n"
                    . " where enabled = 1\n"
                    . " and next_run < 0";
                redcommand($sql, $db);
                $val  = $row['value'];
                $age  = $now - $mod;
                $stat = "p:$pid,a:$age,t:$timo";
                $text = "notify: stealing lock ($stat) from process $own";
                logs::log(__FILE__, __LINE__, $text, 0);
                debug_note($text);
                $out = true;
            } else {
                $age = $now - $mod;
                debug_note("age $age < timeout $timo");
            }
        }
    }
    return $out;
}



/*
    |  Main program
    */


$mtstart = microtime();
$now     = time();
$pid     = getmypid();

if (pfTimeNotf) {
    logs::log(__FILE__, __LINE__, "timing: ntf $pid start run", 0);
}

$single = get_integer('id', 0);
$dbg    = get_integer('debug', 0);
$debug  = (($dbg) || ($single));
$mdb    = db_connect();
$title  = "Cron Notify ($pid)";
$auth   = getenv("REMOTE_USER"); // cron does not use PHP auth'n
$comp   = component_installed();

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $auth, 0, 0, 0, $mdb);

if (trim($msg)) debug_note($msg);

echo again();
if (notify_lock($pid, $mdb)) {
    /* Set the runtime limit to infinite, and the memory limit to the
            server setting.  I copied (yuck) this code from c-report.php. */
    $msize = get_integer('mem', 128);
    set_time_limit(0);
    ignore_user_abort(true);
    if (function_exists('ini_set')) {
        $msizeserv = server_def('max_php_mem_mb', '256', $mdb);
        if ($msizeserv > $msize) {
            $msize = $msizeserv;
        }
        $mem = $msize . 'M';
        debug_note("memory_limit: $mem");
        ini_set('memory_limit', $mem);
        ini_set('log_errors', '1');
        if ($debug) {
            ini_set('error_reporting', E_ALL);
            ini_set('display_errors', '1');
        }
    }

    $total = notify_cron($mdb, $now, $pid, $comp, $debug, $single);
    $err = PHP_DNAV_PeriodicUpdate(CUR);
    if ($err == constAppNoErr) {
        echo "<p>Dashboard update ran successfully.</p>\n";
    } else {
        $text = "Error $err occurred in dashboard periodic update.";
        echo "<p>$text</p>\n";
        logs::log(__FILE__, __LINE__, $text, 0);
    }
    notify_unlock($pid, $mdb);
} else {
    $total = 0;
    if (notify_steal($pid, $mdb)) {
        opt_update(constNotifyPid, 0, 0, $mdb);
        opt_update(constNotifyLock, 0, 0, $mdb);
        echo "<p>Lock broken.</p>\n";
    } else {
        echo "<p>Could not aquire notify lock</p>\n";
    }
}
echo again();

$usec = microtime_diff($mtstart, microtime());
$secs = microtime_show($usec);
$text = "timing: ntf $pid end run ($total) total time $secs";
echo "<p>$text</p>\n";
if (pfTimeNotf) {
    logs::log(__FILE__, __LINE__, $text, 0);
}

echo head_standard_html_footer($auth, $mdb);
