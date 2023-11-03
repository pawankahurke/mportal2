<?php

/*
Revision history:

Date        Who     What
----        ---     ----
19-Oct-04   EWB     Created.
20-Oct-04   EWB     Cron Shedule constants moved here.
21-Oct-04   EWB     cron_hour shouldn't return now, even if it's legal.
22-Oct-04   EWB     cron_next_run insists on a future time.
*/


/*
    |  Codes for
    |    events.Notifications.ntype
    */

define('constScheduleClassic',   '0');
define('constScheduleInvalid',   '1');
define('constScheduleCrontab',   '2');
define('constScheduleProximity', '3');
define('constScheduleOneShot',   '4');

/*
    |  Codes for
    |    events.NotifySchedule.type
    */

define('constCronInvalid', '0');
define('constCronMinute',  '1');   // 0-59,  minute in hour
define('constCronHour',    '2');   // 0-23,  hours in day
define('constCronMDay',    '3');   // 1-31,  days of month
define('constCronWDay',    '4');   // 0-7,   0:sunday
define('constCronMonth',   '5');   // 1-12,  month of year
define('constCronWeek',    '6');   // 1-5,   week of month
define('constCronYDay',    '7');   // 1-366, day of year

define('constCronUMin',   '10');   // posix min time
define('constCronUMax',   '11');   // posix max time

define('constCronProx',  '100');   // proximity
define('constCronFail',  '101');   // failure
define('constCronAny',    '-1');   // special any tag.


/*
    |   figure out wday for specified m/d/y
    |   as always, sunday is zero.
    */

function weekday($dd, $mm, $yy)
{
    $time = mktime(12, 0, 0, $mm, $dd, $yy, -1);
    $day  = getdate($time);
    return $day['wday'];
}

function cron_set(&$out, $type, $min, $max, $data)
{
    for ($xxx = $min; $xxx <= $max; $xxx++) {
        $out[$type][$xxx] = $data;
    }
}

function cron_empty_schedule()
{
    $out = array();
    $out[constCronInvalid] = 0;
    $out[constCronUMin] = 0;
    $out[constCronUMax] = 0;
    $out[constCronProx] = 0;
    $out[constCronFail] = 0;
    cron_set($out, constCronMonth,  1, 12, 0);
    cron_set($out, constCronMDay,   1, 31, 0);
    cron_set($out, constCronWDay,   0, 6, 0);
    cron_set($out, constCronMinute, 0, 59, 0);
    cron_set($out, constCronHour,   0, 23, 0);
    return $out;
}


/*
    |  Set the correct mday bits to specify the week number.
    |
    |  Thanksgiving is always the 4th Thursday in November.
    |  The fourth week is always the 22nd through the 28th.
    |
    |  So ..
    |     mnth: 11
    |     wday: 4
    |     mday: 22-28
    */

function cron_week(&$out, $data)
{
    debug_note("cron week $data");
    switch ($data) {
        case 1:
            cron_set($out, constCronMDay, 1, 7, 1);
            break;
        case 2:
            cron_set($out, constCronMDay, 8, 14, 1);
            break;
        case 3:
            cron_set($out, constCronMDay, 15, 21, 1);
            break;
        case 4:
            cron_set($out, constCronMDay, 22, 28, 1);
            break;
        case 5:
            cron_set($out, constCronMDay, 29, 31, 1);
            break;
        default:
            $out[constCronInvalid] = 1;
    }
}


/*
    |  hour, mint, mday, wday, mnth (1-5) have their own bitfields
    |
    |  The week param just sets 3 or 7 mday bits as appropriate.
    */

function cron_load_schedule($nid, $db)
{
    $out = cron_empty_schedule();
    $sql = "select * from\n"
        . "  " . $GLOBALS['PREFIX'] . "event.NotifySchedule\n"
        . " where nid = $nid";
    $set = find_many($sql, $db);
    foreach ($set as $key => $row) {
        $type = $row['type'];
        $data = $row['data'];
        if ((1 <= $type) && ($type <= 5)) {
            if ($data < 0) {
                $tmp = $out[$type];
                reset($tmp);
                foreach ($tmp as $key => $row) {
                    $out[$type][$key] = 1;
                }
            } else {
                $out[$type][$data] = 1;
            }
        }
        if ($type == constCronWeek) {
            cron_week($out, $data);
        }
        if ($type == constCronInvalid) {
            $out[constCronInvalid] = 1;
        }
        if (10 <= $type) {
            $out[$type] = $data;
        }
    }
    if (!$set) {
        $out[constCronInvalid] = 1;
    }
    return $out;
}


/*
    |   note funny argument order for mktime.
    |
    |      mktime(hh,mm,ss,m,d,y,dst);
    |
    |   Note that the time returned should be
    |   in the future, even if now is a legal
    |   time.
    |
    |   Also note that we need to worry about
    |   Daylight Savings Time.
    */

function cron_hour(&$sh, &$td, $sk, $yy, $mm, $dd, $hh)
{
    debug_note("cron hour $mm/$dd/$yy $hh ($sk)");
    $uu = $td[0];
    $xx = ($sk) ? $td['minutes'] + 1 : 0;
    while ($xx < 60) {
        if ($sh[constCronMinute][$xx]) {
            debug_note("cron minute $mm/$dd/$yy $hh:$xx");
            $tt = mktime($hh, $xx, 0, $mm, $dd, $yy, -1);
            if ($tt > $uu) {
                return $tt;
            }
        }
        $xx++;
    }
    return 0;
}


/*
    |  Throw out dates such as February 31, etc.
    |
    */

function cron_day(&$sh, &$td, $sk, $yy, $mm, $dd)
{
    debug_note("cron day $mm/$dd/$yy ($sk)");
    if (checkdate($mm, $dd, $yy)) {
        $hh = ($sk) ? $td['hours'] : 0;
        while ($hh < 24) {
            if ($sh[constCronHour][$hh]) {
                $when = cron_hour($sh, $td, $sk, $yy, $mm, $dd, $hh);
                if ($when > 0) {
                    return $when;
                }
            }
            $sk = 0;
            $hh = $hh + 1;
        }
    }
    return 0;
}


function cron_month(&$sh, &$td, $sk, $yy, $mm)
{
    debug_note("cron month $mm/$yy ($sk)");
    if ($sk) {
        $dd = $td['mday'];
        $ww = $td['wday'];
    } else {
        $dd = 1;
        $ww = weekday($dd, $mm, $yy);
    }
    while ($dd <= 31) {
        if (($sh[constCronMDay][$dd]) && ($sh[constCronWDay][$ww])) {
            $when = cron_day($sh, $td, $sk, $yy, $mm, $dd);

            if ($when > 0) {
                return $when;
            }
        }
        $sk = 0;
        $dd = $dd + 1;
        $ww = ($ww + 1) % 7;
    }
    return 0;
}


function cron_next_run(&$sh, $time)
{
    $td = getdate($time);
    $mm = $td['mon'];
    $yy = $td['year'];
    $xx = 1;
    $sk = 1;

    if ($sh[constCronInvalid]) {
        return 0;
    }
    while ($xx <= 12) {
        if ($sh[constCronMonth][$mm]) {
            $when = cron_month($sh, $td, $sk, $yy, $mm);

            if ($when > $time) {
                return $when;
            }
        }
        $sk = 0;
        $mm++;
        $xx++;
        if ($mm > 12) {
            $yy++;
            $mm = 1;
        }
    }
    return 0;
}

function cron_umax(&$sh)
{
    return ($sh[constCronInvalid]) ? 0 : $sh[constCronUMax];
}

function cron_next(&$ntfy, $time, $db)
{
    $nid  = $ntfy['id'];
    $type = $ntfy['ntype'];
    $next = 0;
    switch ($type) {
        case constScheduleClassic:
            $last = $ntfy['last_run'];
            $secs = $ntfy['seconds'];
            $next = $last + $secs;
            break;
        case constScheduleProximity:
        case constScheduleCrontab:
            $shed = cron_load_schedule($nid, $db);
            $next = cron_next_run($shed, $time);
            break;
        case constScheduleOneShot:
            $shed = cron_load_schedule($nid, $db);
            $next = cron_umax($shed);
            break;
        default:
            break;
    }
    return $next;
}
