<?php

/*
Revision history:

Date        Who     What
----        ---     ----
13-Aug-02   EWB     Always log mysql errors.
15-Aug-02   EWB     Use the formal begin script tag.
 9-Sep-02   EWB     Repent from magic_quotes
19-Sep-02   EWB     event_cache_set moved here, since only logging uses it.
19-Sep-02   EWB     event_cache_set should not log an error, it is expected
                    to fail for duplicate entries.
13-Nov-02   EWB     return the appropriate error code if the database is unavailable.
25-Nov-02   EWB     Census logging.
30-Jan-03   EWB     Created event_execute
31-Jan-03   EWB     Renamed server option to 'event_code'.
 7-Feb-03   EWB     Use 3.1 databases, changes to events table.
13-Feb-03   EWB     Builds a cache of scrip names.
19-May-03   EWB     Imposed a size limit on event logging.
20-May-03   EWB     Leave the first 4K ... enough for a stack trace.
 4-Nov-03   EWB     Improved memory usage, performance timing, and error logging.
11-Mar-04   EWB     Allow for new event.Events.deleted
25-May-04   EWB     Everybody updates the census.
15-Dec-04   EWB     Event logging honors the purge_lock flag.
21-Dec-04   EWB     Truncate errlog uploads at 4K (cchs problem)
 7-Feb-05   EWB     Raise errlog limit to 64K
14-Mar-05   EWB     Record value of machine UUID, whenever possible.
16-Mar-05   EWB     ELOG_LogEvent
18-Mar-05   EWB     Don't clear scrip 96 text4 value.
 7-Apr-05   EWB     Attempt to reduce server traffic
16-Jun-05   BJS     Set $good in ELOG_LogEvent/LogEntry.
 7-Sep-05   BJS     We removed the deleted field from the Events table, 
                    and also had to change event_common to not insert that
                    field into the database. Only goes to uuid[24].
 3-Oct-05   BJS     Removed logs::log(__FILE__, __LINE__, $sql) in event_common().
31-Oct-05   BTE     Removed ELOG_LogEvent.
10-Nov-05   BTE     Updated to use the new census_manage function.
15-May-06   BTE     Bug 2967: Handle duplicate UUIDs and remove migration for
                    pre-2.2 clients.
16-Aug-07   RWM     Bug 4278: Increase truncation threshold for Scrip 100 events.
06-Nov-07   RWM     Increase truncation threshold for Scrip 111 events.
19-Feb-08   BTE     Bug 4416: Move the "last event log" timestamp into shared
                    memory.  Bug 4412: Remove check for purge lock from event
                    logging.  Bug 4413: Move check for second database while
                    event logging into shared memory.  Bug 4415: Cache the
                    Scrip title table in shared memory.

*/


/*
    |  This is to allow users to log to a parallel database
    |  if they need to.  We want to do this last, since if
    |  they have a bug in their code it will break logging for
    |  us as well.
    |
    |  This is enabled when the user sets the 'event_code'
    |  server option.   The value of event_execute is supposed
    |  to be a php string to be evaluated.
    |
    |  The user code gets access to the same data that we do,
    |  stored in an $args array.  The keys for the $args
    |  array are named the same as the columns in the events
    |  table.
    */

function event_execute(&$valu, $id, $db)
{
    $err = PHP_CORE_GetOptionCache(CUR, $code, constOptionEventCode);
    if ($err != constAppNoErr) {
        logs::log(__FILE__, __LINE__, 'event_execute: failed to get option cache', 0);
        return;
    }
    if ($code) {
        $cmd = server_opt('event_code', $db);
        if ($cmd) {
            $names = array(
                'idx', 'scrip', 'entered', 'customer', 'machine',
                'username', 'clientversion', 'clientsize', 'priority',
                'description', 'type', 'path', 'executable', 'version',
                'size', 'id', 'windowtitle', 'string1', 'string2',
                'text1', 'text2', 'text3', 'text4', 'servertime', 'uuid'
            );
            $args = array();
            reset($names);
            foreach ($names as $key => $data) {
                $args[$data] = $valu[$key];
            }
            $args['idx'] = $id;
            //      logs::log(__FILE__, __LINE__, "event_execute:$id,$cmd",0);
            eval($cmd);
        }
    }
}


/*
    |  This insert will nearly always fail, due to the unique keys.
    |  This builds up a cache of the scrip names for the events form.
    */

function cache(&$args, $db)
{
    $scrip = &$args['valu'][1];
    $desc  = &$args['valu'][9];

    $err = PHP_RUNT_AddScripCache(
        CUR,
        intval($args['valu'][1]),
        "$scrip - $desc"
    );
    if ($err != constAppNoErr) {
        logs::log(__FILE__, __LINE__, 'event.php: PHP_RUNT_AddScripCache failure', 0);
    }
}


/*
    |  Converts mysql format to posix time.
    |
    |  0000000000111111111
    |  0123456789012345678
    |  2003-01-10 09:14:22
    |
    |  This goes badly wrong in the event that apache and mysql are running
    |  in different time zones ... or if the client and server are in
    |  different time zones, or if they dissagree about when daylight
    |  savings time starts.
    */

function posix_time($s)
{
    $y  = intval(substr($s, 0, 4));
    $m  = intval(substr($s, 5, 2));
    $d  = intval(substr($s, 8, 2));
    $hh = intval(substr($s, 11, 2));
    $mm = intval(substr($s, 14, 2));
    $ss = intval(substr($s, 17, 2));
    return mktime($hh, $mm, $ss, $m, $d, $y);
}


/*
    |  This handles all the server event logging.
    |
    |  Note that $valu currently is and probably
    |  must continue to be different from $args['valu'];
    |
    |  This is to maintain compatibility with older clients, which
    |  send the client time in mysql format, and also because of
    |  text truncation and the event_code stuff.
    |
    |  If the purge_lock flag is set, we just tell the client that
    |  the database is unavailable, and it should try again later.
    */

function event_common(&$args, $uuid, $db)
{
    $now  = time();
    $rval = constErrDatabaseNotAvailable;
    $usec = $args['usec'];
    $scrp = $args['valu'][1];
    $text = $args['valu'][3];
    $host = $args['valu'][4];

    $site = ($text) ? $text : 'Invalid';
    $valu = $args['valu'];
    $valu[3] = $site;
    $blockscrp = array(249, 63, 177, 65, 0, 257, 248, 249, 61, 187, 243, 190, 240, 64, 38, 232);

    /*
        |  Just to reduce server traffic
        */

    $args['valu'][17] = '';  // string1
    $args['valu'][18] = '';  // string2
    $args['valu'][19] = '';  // text1
    $args['valu'][20] = '';  // text2
    $args['valu'][21] = '';  // text3
    $args['valu'][22] = '';  // text4

    if ($scrp == 65) {
        // scrip 65 is upload errlog.txt, value in text3
        // truncate to just first 64K of errlog.txt to
        // work around a problem at cchs
        $emax = 64 * 1024;
        $size = strlen($valu[21]);
        if ($size > $emax) {
            $valu[21] = substr($valu[21], 0, $emax);
            $text = "event: $host errlog ($size) truncated at $site";
            logs::log(__FILE__, __LINE__, $text, 0);
        }
    }
    $valu[0] = 0;
    $ctime = $valu[2];
    if (intval($ctime) <= 9999) {
        $valu[2] = posix_time($ctime);
    }

    /*
        |  Currently, the server does not use the Events.uuid field.
        |  However, we would like to be able to use it in some future
        |  version of the server, so we'll just start recording the
        |  uuid value whenever we can.
        */

    $valu[23] = $now;
    $valu[24] = $uuid;
    $sql = '0';
    for ($i = 1; $i <= 24; $i++) {
        /* Increase truncation threshold for Scrip 100 */
        /* Updated to do the same for Scrip 111 */
        $maxsize = 1048576;
        if (($scrp == 100) || ($scrp == 111)) {
            $maxsize = 4194304;
        }
        $size = strlen($valu[$i]);
        if ($size > $maxsize) {
            $size = $size - 4096;
            $text = "event: $size bytes truncated for $host at $site.";
            logs::log(__FILE__, __LINE__, $text, 0);

            /*
                |  Ok, we've left a log entry to identify the
                |  machine with the problem ... just in case
                |  we crash on the substr.
                */

            $val  = substr($valu[$i], 0, 4096);
            $val .= "\n ... ($size bytes truncated)\n";
        } else {
            $val = $valu[$i];
        }

        $tmp  = safe_addslashes($val);
        $sql .= ", '$tmp'";
    }
    if (!in_array($scrp, $blockscrp)) {
        $cmd = "insert into Events values ($sql)";
        $res = command($cmd, $db);
        if (affected($res, $db)) {
            $id   = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
            $rval = constAppNoErr;
            // cache($args,$db);
            if (mysqli_select_db($db, core)) {
                event_execute($valu, $id, $db);
            }
        }
    } else {
        $rval = constAppNoErr;
    }
    return $rval;
}


function event_error(&$args, $rval, $good)
{
    $usec = $args['usec'];
    if ($rval == constErrDatabaseNotAvailable) {
        $site = $args['valu'][3];
        $host = $args['valu'][4];
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $mode = ($good) ? 'failure' : 'unavailable';
        $text = "mysql: $host mysql $mode in $secs at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }
    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = $rval;
}


function ELOG_LogEntry(&$args)
{
    $good = false;
    $db   = db_code('db_log');
    if ($db) {
        $good = true;
    }
    $rval = constErrDatabaseNotAvailable;
    if ($good) {
        $text = $args['valu'][3];
        $host = $args['valu'][4];
        $site = ($text) ? $text : 'Invalid';
        $uuid = '';
        if (@$args['eloguuid']) {
            $uuid = $args['eloguuid'];
        }
        if ($uuid == '') {
            $row  = find_census_name($site, $host, $db);
            $uuid = ($row) ? $row['uuid'] : '';
        }
        if ($args['updatecensus'] == 1) {
            /* We don't send the UUID, since ELOG_LogEvent should
                    permit an event log entry even if the appropriate
                    entries for the machine do not exist in the Census. */
            $rval = census_manage($site, $host, '', 1, $db);
        }
        $rval = event_common($args, $uuid, $db);
    }
    event_error($args, $rval, $good);
}
