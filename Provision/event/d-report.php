<?php

/*
Revision history:

Date        Who     What
----        ---     ----
17-Feb-03   EWB     Created.
24-Feb-03   EWB     Color for enable/disable.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
14-Apr-03   NL      Move debug_note line below $debug.
30-Apr-03   NL      Delete RptSiteFilters when deleting report.
22-Oct-03   EWB     Show next/last report times.
22-Oct-03   EWB     Sort by next run time.
23-Oct-03   EWB     establish a default limit
26-Oct-03   EWB     command to repair wedged report
27-Oct-03   EWB     count total number of reports and filters.
28-Oct-03   EWB     enable, disable, postpone
19-Nov-03   EWB     touch, later, skip
20-Nov-03   EWB     redo
12-Apr-04   EWB     test retry.
16-Apr-04   EWB     wait column.
19-Aug-04   EWB     past and future.
28-Oct-04   EWB     Manage Queue
15-Nov-04   EWB     Queue first works on one of an initial gang.
09-Nov-05   BJS     Removed RptSiteFilters.
19-Sep-06   WOH     Bug 3657: Changed name html_footer.  Added username arg.

*/


ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('local.php');
include('../lib/l-rprt.php');
include('../lib/l-user.php');
include('../lib/l-jump.php');
include('../lib/l-cmth.php');
include('../lib/l-slav.php');
include('../lib/l-gsql.php');
include('../lib/l-tabs.php');
include('../lib/l-head.php');


function showtime($when)
{
    $text = '<br>';
    if ($when > 0) {
        $text = date('m/d/y H:i:s', $when);
    }
    if ($when < 0) {
        $text = 'in process';
    }
    return $text;
}

function again($env)
{
    $self = $env['self'];
    $args = $env['args'];
    $href = ($args) ? "$self?$args" : $self;
    $ord  = "$self?ord";
    $act  = "$self?act";

    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link('debug.php', 'home');
    $a[] = html_link("$act=next", 'future');
    $a[] = html_link("$act=past", 'past');
    $a[] = html_link("$act=queu", 'queue');
    $a[] = html_link("$act=menu", 'menu');
    $a[] = html_link($href, 'again');
    return jumplist($a);
}

function nanotime($when)
{
    $text = '<br>';
    if ($when > 0) {
        $that = date('m/d/y', time());
        $date = date('m/d/y', $when);
        $time = date('H:i:s', $when);
        $text = ($date == $that) ? $time : "$date $time";
    }
    if ($when < 0) {
        $text = "running";
    }
    return $text;
}

function color_data($args, $color, $head)
{
    $td = ($head) ? 'th' : 'td';
    if ($args) {
        echo "<tr bgcolor='$color'>\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo "<$td>$data</$td>\n";
        }
        echo "</tr>\n";
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
        $txt = sprintf('%d:%02d', $mm, $ss);
    if ((3600 <= $secs) && ($secs < 86400))
        $txt = sprintf('%d:%02d:%02d', $hh, $mm, $ss);
    if ((86400 <= $secs) && ($dd <= 7))
        $txt = sprintf('%d %02d:%02d:%02d', $dd, $hh, $mm, $ss);
    if (8 <= $dd) {
        $dd  = intval(round($secs / 86400));
        $txt = "$dd days";
    }

    return $txt;
}


function find_report($id, $db)
{
    $row = array();
    if ($id > 0) {
        $sql = "select * from Reports where id = $id";
        $row = find_one($sql, $db);
    }
    return $row;
}


function delete_report($id, $db)
{
    if ($id > 0) {
        $sql = "delete from Reports where id = $id";
        $res = redcommand($sql, $db);
    }
}


function enable_report($id, $db)
{
    if ($id > 0) {
        $sql = "update Reports set\n"
            . " enabled = 1,\n"
            . " next_run = 0,\n"
            . " this_run = 0\n"
            . " where id = $id";
        $res = redcommand($sql, $db);
    }
}

function disable_report($id, $db)
{
    if ($id > 0) {
        $sql = "update Reports set\n"
            . " enabled = 0,\n"
            . " this_run = 0,\n"
            . " next_run = 0\n"
            . " where id = $id";
        $res = redcommand($sql, $db);
    }
}

function touch_report($id, $db)
{
    if ($id > 0) {
        $now = time();
        $sql = "update Reports set\n"
            . " last_run = $now\n"
            . " where id = $id";
        $res = redcommand($sql, $db);
    }
}

function skip_report($id, $db)
{
    if ($id > 0) {
        $sql = "update Reports set\n"
            . " next_run = 0\n"
            . " where id = $id\n"
            . " and enabled = 1";
        $res = redcommand($sql, $db);
    }
}

function redo_report($id, $db)
{
    if ($id > 0) {
        $when = time() - 86400;
        $sql = "update Reports set\n"
            . " next_run = $when\n"
            . " where id = $id\n"
            . " and enabled = 1";
        $res = redcommand($sql, $db);
    }
}


function later_report($id, $db)
{
    $row = find_report($id, $db);
    if ($row) {
        $next = $row['next_run'];
        if ($next > 0) {
            $now = time();
            if ($next > $now)
                $then = $next + 3600;
            else
                $then = $now + 3600;
            $sql = "update Reports set\n"
                . " next_run = $then\n"
                . " where id = $id and\n"
                . " enabled = 1 and\n"
                . " next_run > 0";
            $res = redcommand($sql, $db);
        }
    }
}


function postpone_reports($db)
{
    $now  = time();
    $then = $now + 3600;
    $sql  = "update Reports set\n"
        . " next_run = $then\n"
        . " where enabled = 1\n"
        . " and next_run > 0\n"
        . " and next_run < $then";
    $res  = redcommand($sql, $db);
    $num  = affected($res, $db);
    $date = date('m/d/y H:i:s', $then);
    if ($num)
        $msg = "$num reports postponed to $date.";
    else
        $msg = "nothing has changed.";
    $msg = fontspeak($msg);
    echo "$msg<br>\n";
}



function one_shot($db)
{
    $name = 'Stuck Report';
    $user = 'admin';
    $qn  = safe_addslashes($name);
    $qu  = safe_addslashes($user);
    $sql = "select * from Reports\n"
        . " where name = '$qn'\n"
        . " and username = '$qu'\n"
        . " and global = 0";
    $row = find_one($sql, $db);
    if ($row) {
        $rid  = $row['id'];
        $timo = server_int('report_timeout', 7200, $db);
        if ($timo > 300) {
            $now  = time();
            $tmin = $now - $timo + 30;
            $tmax = $tmin + $timo;
            $sql = "update Reports set\n"
                . " enabled  = 1,\n"
                . " next_run = -1,\n"
                . " this_run = $tmin,\n"
                . " last_run = $tmin,\n"
                . " retries  = 5\n"
                . " where id = $rid";
            $res = redcommand($sql, $db);
        }
    }
}



function repair_bias($id, $bias, $db)
{
    $row = find_report($id, $db);
    if ($row) {
        $now  = time();
        $next = $row['next_run'];
        $last = $row['last_run'];
        if ($next < 0) {
            $prev = last_cycle($row, $now);
            $when = $prev + $bias;
            $sql  = "update Reports set\n"
                . " next_run = $when,\n"
                . " this_run = 0\n"
                . " where id = $id";
            redcommand($sql, $db);
            $now  = date('m/d/y H:i:s', $now);
            $when = date('m/d/y H:i:s', $when);
            $last = date('m/d/y H:i:s', $last);
            debug_note("when: $when, last:$last, now:$now");
        }
    }
}


function repair_report($id, $db)
{
    $bias = server_int('cron_bias', 120, $db);
    repair_bias($id, $bias, $db);
}


function repair_all($db)
{
    $sql = "select * from Reports where next_run < 0";
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            $bias = server_int('cron_bias', 120, $db);
            while ($row = mysqli_fetch_array($res)) {
                $id = $row['id'];
                repair_bias($id, $bias, $db);
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
}


function jumpdetail($row, $id)
{
    $a = array();
    $self = server_var('PHP_SELF');
    $act  = "$self?id=$id&act";
    $enab = html_link("$act=enab", 'enable');
    $disb = html_link("$act=disb", 'disable');
    $a[] = ($row['enabled']) ? $disb : $enab;
    $a[] = html_link("$act=dlet", 'delete');
    $a[] = html_link("$act=tuch", 'touch');
    if ($row['enabled']) {
        $a[] = html_link("$act=lats", 'later');
        $a[] = html_link("$act=redo", 'redo');
    }
    if ($row['next_run'] < 0) {
        $a[] = html_link("$act=reps", 'repair');
        $a[] = html_link("$act=skip", 'skip');
    }
    return jumplist($a);
}


function fix_time(&$row, $name)
{
    if ($row[$name]) {
        $time = $row[$name];
        $row[$name] = showtime($time) . " ($time)";
    }
}


function check_queue($env, $db)
{
    $lock = array();
    $lpid = array();
    $priv = $env['priv'];
    $now  = $env['now'];
    if ($priv) {
        $lock = find_opt('report_lock', $db);
        $lpid = find_opt('report_pid', $db);
    }
    if (($lock) && ($lpid)) {
        if ($lock['value']) {
            $when = '';
            $age  = 'unknown';
            $time = $lpid['modified'];
            $ownr = $lpid['value'];
            if ($now > $time) {
                $when = nanotime($time);
                $age  = age($now - $time);
            }

            $text = "Report Queue Locked by $ownr since $when ($age)";
            echo "\n\n<br><h2>$text</h2><br>\n\n";
        }
    }
}

function claim_lock(&$env, $db)
{
    $pid  = $env['pid'];
    $lock = server_int('report_lock', 0, $db);
    $lpid = server_int('report_pid', 0, $db);
    $timo = server_int('report_timeout', 0, $db);
    if (($lock) && ($lpid)) {
        echo "<p>Report Lock owned by <b>$lpid</b>.</p>\n";
    } else {
        if (($timo > 0) && ($pid)) {
            $now  = time();
            $when = ($now - $timo) + 60;
            echo "<p>Timeout is <b>$timo</b> seconds.</p>\n";
            if (update_opt('report_lock', '1', $db)) {
                $sql = "update " . $GLOBALS['PREFIX'] . "core.Options set\n"
                    . " value = $pid,\n"
                    . " modified = $when\n"
                    . " where name = 'report_pid'";
                redcommand($sql, $db);
                $xxx = nanotime($when);
                $txt = "report: fake lock by process $pid at $xxx";
                echo "<p>Report Lock claimed by process <b>$pid</b>.</p>\n";
                logs::log(__FILE__, __LINE__, $txt, 0);
                debug_note($txt);
            }
        } else {
            echo "<p>Timeout is zero.</p>\n";
        }
    }
}


function pick_lock(&$env, $db)
{
    $now  = $env['now'];
    $lock = find_opt('report_lock', $db);
    $lpid = find_opt('report_pid', $db);
    if (($lock) && ($lpid)) {
        $age  = 0;
        $ownr = $lpid['value'];
        $when = $lock['modified'];
        $age  = ($now - $when);
        echo "<p>Report Lock owned by <b>$ownr</b>. ($age seconds)</p>\n";
        opt_update('report_pid', 0, 0, $db);
        opt_update('report_lock', 0, 0, $db);
    }
}


function detail_report($id, $db)
{
    $row = find_report($id, $db);
    if ($row) {
        $tmp = $row;
        fix_time($row, 'next_run');
        fix_time($row, 'last_run');
        fix_time($row, 'this_run');
        fix_time($row, 'modified');
        fix_time($row, 'created');
        fix_time($row, 'umin');
        fix_time($row, 'umax');

        $num = 0;
        $str = $row['search_list'];
        $foo = explode(',', $str);
        reset($foo);
        foreach ($foo as $key => $sid) {
            if ($sid > 0) $num++;
        }

        $txt = "$num searches<br>$str";
        $row['search_list'] = $txt;

        echo jumpdetail($tmp, $id);
        echo table_header();
        reset($row);
        foreach ($row as $key => $data) {
            $valu = ($data == '') ? '<br>' : $data;
            $args = array($key, $valu);
            echo table_data($args, 0);
        }
        echo table_footer();
        echo jumpdetail($tmp, $id);
    }
}


function order($ord)
{
    switch ($ord) {
        case  0:
            return 'enabled desc, next_run, this_run desc, id';
        case  1:
            return 'enabled desc, next_run desc, id';
        case  2:
            return 'name, username';       // name
        case  3:
            return 'name desc, username';
        case  4:
            return 'username, name';           // owner
        case  5:
            return 'username desc, name';
        case  6:
            return 'id';                           // id
        case  7:
            return 'id desc';
        case  8:
            return 'enabled desc, hour, minute, cycle, mday, wday, name, username';
        case  9:
            return 'hour desc, minute desc, cycle, mday, wday, name, username';
        case 10:
            return 'enabled desc, cycle, mday, wday, hour, minute, name, username';
        case 11:
            return 'last_run desc, name, username';
        case 12:
            return 'last_run, name, username';
        case 13:
            return 'modified, name, username';
        case 14:
            return 'modified desc, name, username';
        case 15:
            return 'created, name, username';
        case 16:
            return 'created desc, name, username';
        default:
            return order(0);
    }
}


function count_reports($db)
{
    $sql = "select count(*) from Reports";
    return find_scalar($sql, $db);
}


function bold($text)
{
    return "<b>[$text]</b>";
}


function shed(&$env, &$row)
{
    $cycl = $row['cycle'];
    $mint = $row['minute'];
    $hour = $row['hour'];
    $type = $env['cycl'][$cycl];
    $hhmm = sprintf('%02d:%02d', $hour, $mint);
    switch ($cycl) {
        case 0:
            return "$hhmm $type";
        case 1:
            $wday = $row['wday'];
            $text = $env['days'][$wday];
            return "$hhmm $text";
        case 2:
            $mday = $row['mday'];
            return "$hhmm $type $mday";
        case 3:
            return "$hhmm $type";
        default:
            return $type;
    }
}



function queue_manage(&$env, $db)
{
    $now = $env['now'];
    $max = $now + 86400;
    $sql = "select * from Reports\n"
        . " where enabled = 1\n"
        . " and next_run < $max\n"
        . " order by next_run, global, id\n"
        . " limit 30";
    $set = find_many($sql, $db);
    if ($set) {
        $self = $env['self'];
        $head = explode('|', 'Wait|Name|Owner|Id|Schedule|Next|Last|Action');
        $cols = safe_count($head);
        $text = datestring($now);

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($head, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $rid  = $row['id'];
            $name = $row['name'];
            $user = $row['username'];
            $glob = $row['global'];
            $last = $row['last_run'];
            $next = $row['next_run'];
            $that = $row['this_run'];

            if ($next > 0) {
                if ($next > $now) {
                    $secs = $next - $now;
                    $wait = age($secs);
                } else {
                    $secs = $now - $next;
                    $late = age($secs);
                    $wait = bold($late);
                }
            } else if ($next < 0) {
                if ($that > 0) {
                    $name = bold($name);
                    $wait = '(run)';
                }
            }

            $tlst = nanotime($last);
            $tnxt = nanotime($next);
            $scop = ($glob) ? 'g' : 'l';
            $ownr = "$user($scop)";

            $cmd  = "$self?id=$rid&act";
            $dbg  = "$self?debug=1&id=$rid&act";
            $ax   = array();
            $ax[] = html_link("$cmd=view", 'detail');
            $ax[] = html_link("$cmd=frst", 'first');
            $ax[] = html_link("$cmd=last", 'now');
            $ax[] = html_link("$cmd=post", 'post');

            $text = 'white';
            $acts = join(' ', $ax);
            $shed = shed($env, $row);
            $args = array($wait, $name, $ownr, $rid, $shed, $tnxt, $tlst, $acts);
            echo color_data($args, $text, 0);
        }
        echo table_footer();
    }
}



function queue_post(&$env, $db)
{
    $new = array();
    $rid = $env['id'];
    $old = find_report($rid, $db);
    $msg = 'No change';
    if ($old) {
        $nxt = $old['next_run'] - 1;
        $enb = $old['enabled'];
        if (($enb) && ($nxt > 28)) {
            $sql = "select * from Reports\n"
                . " where enabled = 1\n"
                . " and next_run > $nxt\n"
                . " and id != $rid\n"
                . " order by next_run\n"
                . " limit 1";
            $new = find_one($sql, $db);
        }
    }
    if ($new) {
        $nxt = $new['next_run'] + 1;
        $sql = "update Reports set\n"
            . " next_run = $nxt\n"
            . " where id = $rid\n"
            . " and next_run > 0\n"
            . " and next_run < $nxt\n"
            . " and enabled = 1";
        $res = redcommand($sql, $db);
        if (affected($res, $db)) {
            $name = $old['name'];
            $otim = $old['next_run'];
            $secs = age($nxt - $otim);
            $otxt = nanotime($otim);
            $ntxt = nanotime($nxt);
            $msg  = "Report <b>$name</b> postponed by <b>$secs</b>,"
                . " from <b>$otxt</b> to <b>$ntxt</b>.";
        }
    }
    echo "<p>$msg</p>\n";
    queue_manage($env, $db);
}


function queue_last(&$env, $db)
{
    $new = array();
    $rid = $env['id'];
    $now = $env['now'];
    $old = find_report($rid, $db);
    $msg = 'No change';
    if ($old) {
        $ntim = $now - 1;
        $otim = $old['next_run'];
        $sql  = "update Reports set\n"
            . " next_run = $ntim\n"
            . " where id = $rid\n"
            . " and next_run > 0\n"
            . " and this_run = 0\n"
            . " and enabled = 1";
        $res  = redcommand($sql, $db);
        if (affected($res, $db)) {
            $name = $old['name'];
            $otim = $old['next_run'];
            if ($otim < $ntim) {
                $secs = age($ntim - $otim);
                $what = 'postponed';
            } else {
                $secs = age($otim - $ntim);
                $what = 'advanced';
            }
            $otxt = nanotime($otim);
            $ntxt = nanotime($ntim);
            $msg  = "Report <b>$name</b> $what by <b>$secs</b>,"
                . " from <b>$otxt</b> to <b>$ntxt</b>.";
        }
    }
    echo "<p>$msg</p>\n";
    queue_manage($env, $db);
}


function queue_frst(&$env, $db)
{
    $new = array();
    $rid = $env['id'];
    $old = find_report($rid, $db);
    $msg = 'No change';
    if ($old) {
        $enb = $old['enabled'];
        $nxt = $old['next_run'];
        if (($enb) && ($nxt > 28)) {
            // note this should be ordered exactly
            // the same way that c-report chooses
            // the next report to process.

            $sql = "select * from Reports\n"
                . " where enabled = 1\n"
                . " and next_run > 86400\n"
                . " and next_run <= $nxt\n"
                . " and id != $rid\n"
                . " order by next_run, global, cycle, id\n"
                . " limit 1";
            $new = find_one($sql, $db);
        }
    }
    if ($new) {
        $nxt = $new['next_run'] - 1;
        $sql = "update Reports set\n"
            . " next_run = $nxt\n"
            . " where id = $rid\n"
            . " and next_run > $nxt\n"
            . " and enabled = 1";
        $res = redcommand($sql, $db);
        if (affected($res, $db)) {
            $name = $old['name'];
            $otim = $old['next_run'];
            $secs = age($otim - $nxt);
            $otxt = nanotime($otim);
            $ntxt = nanotime($nxt);
            $msg  = "Report <b>$name</b> advanced by <b>$secs</b>,"
                . " from <b>$otxt</b> to <b>$ntxt</b>.";
        }
    }
    echo "<p>$msg</p>\n";
    queue_manage($env, $db);
}


function debug_report(&$env, &$set, $act, $future, $db)
{
    $ord  = $env['ord'];
    $now  = $env['now'];
    $self = $env['self'];
    $o    = "$self?act=$act&ord";

    $next = ($ord ==  0) ? "$o=1"  : "$o=0";
    $name = ($ord ==  2) ? "$o=3"  : "$o=2";
    $user = ($ord ==  4) ? "$o=5"  : "$o=4";
    $iref = ($ord ==  6) ? "$o=7"  : "$o=6";
    $last = ($ord == 11) ? "$o=12" : "$o=11";
    $mtim = ($ord == 14) ? "$o=13" : "$o=14";
    $ctim = ($ord == 16) ? "$o=15" : "$o=16";

    switch ($ord) {
        case  8:
            $wref = "$o=9";
            break;
        case  9:
            $wref = "$o=10";
            break;
        default:
            $wref = "$o=8";
            break;
    }

    $wait = html_link($next, 'Wait');
    $age  = html_link($last, 'Age');
    $next = html_link($next, 'Next');
    $name = html_link($name, 'Name');
    $user = html_link($user, 'Owner');
    $mtim = html_link($mtim, 'Modify');
    $ctim = html_link($ctim, 'Create');
    $last = html_link($last, 'Last');
    $id   = html_link($iref, 'Id');
    $when = html_link($wref, 'When');

    $time = ($future) ? $wait : $age;
    $head = array($time, $name, $user, $ctim, $mtim, $id, $when, $last, $next, 'action');
    $cols = safe_count($head);

    echo table_header();
    echo pretty_header('Reports', $cols);
    echo table_data($head, 1);

    reset($set);
    foreach ($set as $key => $row) {
        $rid  = $row['id'];
        $glob = $row['global'];
        $enab = $row['enabled'];
        $ctim = $row['created'];
        $last = $row['last_run'];
        $next = $row['next_run'];
        $that = $row['this_run'];
        $mtim = $row['modified'];

        $name = disp($row, 'name');
        $user = disp($row, 'username');
        $scop = ($glob) ? 'g' : 'l';
        $wait = '<br>';
        if ($enab) {
            $code = '';
            if (($next < 0) && ($that > 0)) {
                $color = 'lightpink';
                $wait = '(run)';
                $name = bold($name);
            } else if ($next < 0) {
                $color = 'lemonchiffon';
            } else if ($next == 0) {
                $color = 'aquamarine';
            } else if ($next < $now) {
                $color = 'lightgreen';
                $age  = age($now - $next);
                $wait = bold($age);
            } else {
                $wait  = age($next - $now);
                $color = 'aquamarine';
            }
        } else {
            $code = '(d)';
            $color = 'lightskyblue';
            if ((0 < $last) && ($last < $now)) {
                $wait = age($now - $last);
            }
        }

        $when = shed($env, $row);
        $mod  = showtime($mtim);
        $crt  = showtime($ctim);
        $lst  = showtime($last);
        $nxt  = showtime($next);
        $act  = "$self?id=$rid&act";
        $ax   = array();
        $ax[] = html_link("$act=view", 'detail');
        if (($next < 0) && ($last + 1800 < $now)) {
            $ax[] = html_link("$act=reps", 'repair');
        }
        $eeee = html_link("$act=enab", 'enable');
        $dddd = html_link("$act=disb", 'disable');
        $ax[] = ($enab) ? $dddd : $eeee;
        $acts = join("&nbsp;\n", $ax);
        $ownr = "$user($scop)$code";
        if (!$future) {
            if ($last)
                $wait = age($now - $last);
            else
                $wait = '<br>';
        }
        $args = array($wait, $name, $ownr, $crt, $mod, $rid, $when, $lst, $nxt, $acts);
        color_data($args, $color, 0);
    }

    echo table_footer();
}


function future_report(&$env, $db)
{
    $ord = $env['ord'];
    $lim = $env['limt'];

    if ($ord < 0) $ord = 0;
    $wrd = order($ord);
    $sql = "select * from Reports\n"
        . " where enabled = 1\n"
        . " order by $wrd\n"
        . " limit $lim";
    $set = find_many($sql, $db);
    if ($set) {
        debug_report($env, $set, 'next', 1, $db);
    }
}


function past_report(&$env, $db)
{
    $ord = $env['ord'];
    $lim = $env['limt'];
    if ($ord < 0) $ord = 11;

    $wrd = order($ord);
    $sql = "select * from Reports\n"
        . " where last_run > 0\n"
        . " order by $wrd\n"
        . " limit $lim";
    $set = find_many($sql, $db);
    if ($set) {
        debug_report($env, $set, 'past', 0, $db);
    }
}

function disabled_report(&$env, $db)
{
    $ord = $env['ord'];
    $lim = $env['limt'];

    if ($ord < 0) $ord = 11;

    $wrd = order($ord);
    $sql = "select * from Reports\n"
        . " where enabled != 1\n"
        . " order by $wrd\n"
        . " limit $lim";
    $set = find_many($sql, $db);
    if ($set) {
        debug_report($env, $set, 'dead', 0, $db);
    }
}

function command_list(&$act, &$txt)
{
    echo "<p>What do you want to do?</p>\n\n\n<ol>\n";

    reset($txt);
    foreach ($txt as $key => $doc) {
        $cmd = html_link($act[$key], $doc);
        echo "<li>$cmd</li>\n";
    }
    echo "</ol>\n";
}


function debug_menu(&$env, $db)
{
    $self = $env['self'];
    $cmd  = "$self?act";
    $dbg  = "$self?debug=1&act";

    $act = array();
    $txt = array();

    $act[] = "$cmd=menu";
    $txt[] = 'Debug Menu';

    $act[] = "$dbg=next&l=50&o=24";
    $txt[] = 'Upcoming';

    $act[] = "$dbg=last&l=50&o=20";
    $txt[] = 'Recent Past';

    $act[] = "$cmd=dead";
    $txt[] = 'Disabled';

    $act[] = "$cmd=stat";
    $txt[] = 'Statistics';

    //    $act[] = "$dbg=rset";
    //    $txt[] = 'Reset Report Queue Only';

    $act[] = "$dbg=fixs";
    $txt[] = 'Fix All';

    $act[] = "$dbg=queu";
    $txt[] = 'Queue Control';

    $act[] = "$dbg=lock";
    $txt[] = 'Claim Lock';

    $act[] = "$dbg=pick";
    $txt[] = 'Release Lock';

    $act[] = "$dbg=pall";
    $txt[] = 'Postpone All';

    $act[] = '../acct/index.php';
    $txt[] = 'Debug Home';

    $act[] = 'notify.php?act=queu';
    $txt[] = 'Notify Queue';

    command_list($act, $txt);
}

function no_access(&$env, $db)
{
    $msg = "This page requires administrative access.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
}

/*
    |  Main program
    */

$now  = time();
$db   = db_connect();
$auth = process_login($db);
$comp = component_installed();
$head = 'Debug Event Report';
$msg  = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($head, $comp, $auth, 0, 0, 0, $db);

$date = datestring(time());

echo "<h2>$date</h2>";

$ord   = get_integer('ord', -1);
$id    = get_integer('id', 0);
$priv  = get_integer('priv', 1);
$dbg   = get_integer('debug', 0);
$enbl  = get_integer('enabled', 0);
$limit = get_integer('limit', 50);
$act   = get_string('act', 'next');
$user  = user_data($auth, $db);
$dprv  = @($user['priv_debug']) ?   1   : 0;
$debug = @($user['priv_debug']) ? $dbg  : 0;
$admin = @($user['priv_admin']) ? $priv : 0;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$env = array();
$env['id']   = $id;
$env['ord']  = $ord;
$env['now']  = $now;
$env['pid']  = getmypid();
$env['days'] = $daynames;
$env['cycl'] = $cyclenames;
$env['enab'] = $enbl;
$env['limt'] = $limit;
$env['dbug'] = $debug;
$env['admn'] = $admin;
$env['priv'] = $dprv;
$env['acts'] = $act;
$env['self'] = server_var('PHP_SELF');
$env['args'] = server_var('QUERY_STRING');
if ($admin) {
    check_queue($env, $db);
} else {
    $act = 'priv';
}

echo again($env);
db_change($GLOBALS['PREFIX'] . 'event', $db);
switch ($act) {
    case 'next':
        future_report($env, $db);
        break;
    case 'past':
        past_report($env, $db);
        break;
    case 'dead':
        disabled_report($env, $db);
        break;
    case 'dlet':
        delete_report($id, $db);
        break;
    case 'view':
        detail_report($id, $db);
        break;
    case 'enab':
        enable_report($id, $db);
        break;
    case 'tuch':
        touch_report($id, $db);
        break;
    case 'lats':
        later_report($id, $db);
        break;
    case 'skip':
        skip_report($id, $db);
        break;
    case 'redo':
        redo_report($id, $db);
        break;
    case 'queu':
        queue_manage($env, $db);
        break;
    case 'post':
        queue_post($env, $db);
        break;
    case 'last':
        queue_last($env, $db);
        break;
    case 'frst':
        queue_frst($env, $db);
        break;
    case 'disb':
        disable_report($id, $db);
        break;
    case 'reps':
        repair_report($id, $db);
        break;
    case 'pall':
        postpone_reports($db);
        break;
    case 'menu':
        debug_menu($env, $db);
        break;
    case 'lock':
        claim_lock($env, $db);
        break;
    case 'pick':
        pick_lock($env, $db);
        break;
    case 'xxxx':
        one_shot($db);
        break;
    case 'priv':
        no_access($env, $db);
        break;
    case 'fixs':
        repair_all($db);
        break;
    default:
        future_report($env, $db);
        break;
}
echo again($env);
db_change($GLOBALS['PREFIX'] . 'core', $db);
echo head_standard_html_footer($auth, $db);
