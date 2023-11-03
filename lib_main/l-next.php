<?php






define('constScheduleClassic',   '0');
define('constScheduleInvalid',   '1');
define('constScheduleCrontab',   '2');
define('constScheduleProximity', '3');
define('constScheduleOneShot',   '4');



define('constCronInvalid', '0');
define('constCronMinute',  '1');
define('constCronHour',    '2');
define('constCronMDay',    '3');
define('constCronWDay',    '4');
define('constCronMonth',   '5');
define('constCronWeek',    '6');
define('constCronYDay',    '7');
define('constCronUMin',   '10');
define('constCronUMax',   '11');
define('constCronProx',  '100');
define('constCronFail',  '101');
define('constCronAny',    '-1');



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
