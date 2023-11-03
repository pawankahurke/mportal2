<?php

/*
Revision history:

Date        Who     What
----        ---     ----
29-Oct-03   EWB     Created.
 4-Feb-04   EWB     Coagulate events into process lifespans.
 5-Feb-04   EWB     reinstate clienttime, remove clientmin, servermin
 5-Feb-04   EWB     perform grouping functions.
 9-Feb-04   EWB     meter report time ranges.
10-Feb-04   EWB     meter reports restrict by site.
11-Feb-04   EWB     sort by product rather than exename.
13-Feb-04   EWB     restore executable.
17-Feb-04   EWB     Suppress Sort Columns.
17-Feb-04   EWB     Exceptions Report.
26-Feb-04   EWB     Changed default title to 'Metering'.
17-Mar-04   EWB     file output.
18-Mar-04   EWB     Default type is process.
18-Mar-04   EWB     E-Mail Output.
23-Mar-04   EWB     Added Logo.
24-Mar-04   EWB     Subtotal/Summary Code
25-Mar-04   EWB     Added documentation for date formats.
12-Jul-05   BJS     Added l-fprc.php, save to temp files.
14-Jul-05   BJS     fwrite change to my_write & improved error checks.
15-Jul-05   BJS     report_mail_report() & create_file_report() return t || f.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

ob_start();
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-slct.php');
include('../lib/l-user.php');
include('../lib/l-jump.php');
include('../lib/l-slav.php');
include('../lib/l-cmth.php');
include('../lib/l-date.php');
include('../lib/l-head.php');
include('../lib/l-rtxt.php');
include('../lib/l-base.php');
include('../lib/l-tabs.php');
include('../lib/l-mime.php');
include('../lib/l-fprc.php');
include('local.php');

define('constProcessCompletion', 0);
define('constProcessCreation',   1);
define('constProcessLife',       2);

function again($env)
{
    $priv = $env['priv'];
    $actn = $env['action'];
    $self = $env['self'];

    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    if ($actn == 'mr') {
        $a[] = html_link($self, 'back');
    }

    if ($priv) {
        $args = $env['args'];
        $href = ($args) ? "$self?$args" : $self;
        $act  = "$self?action";
        if (($actn == 'vrb') || ($actn == 'dbg')) {
            $a[] = html_link($self, 'back');
        }
        $a[] = html_link("$act=dbg", 'debug');
        $a[] = html_link("$act=vrb", 'verbose');
        $a[] = html_link($href, 'again');
        $a[] = html_link('../acct/index.php', 'home');
    }
    return jumplist($a);
}

function post_new()
{
    $self  = server_var('PHP_SELF');
    return "<form method=\"post\" target=\"_blank\" action=\"$self\">\n\n";
}

function time_string($time)
{
    if ($time < 3600) {
        $ss  = $time % 60;
        $mm  = $time / 60;
        $txt = sprintf('%d:%02d', $mm, $ss);
    } else {
        $ss  = $time % 60;
        $mm  = $time / 60;
        $hh  = $mm / 60;
        $mm  = $mm % 60;
        $txt = sprintf('%d:%02d:%02d', $hh, $mm, $ss);
    }
    return $txt;
}


function html_tags($spec, $tags)
{
    $txt = "<$spec";
    reset($tags);
    foreach ($tags as $name => $value) {
        $txt .= " $name=\"$value\"";
    }
    $txt .= ">\n";
    return $txt;
}


function order($ord)
{
    $order = 'clienttime desc, meterid';
    switch ($ord) {
        case  0:;
            break;
        case  1:
            $order = 'sitename, machine, servermax desc, meterid';
            break;
        case  2:
            $order = 'uuid, username, meterid';
            break;
        case  3:
            $order = 'machine, sitename, servermax desc, meterid';
            break;
        case  4:
            $order = 'meterid';
            break;
        case  5:
            $order = 'meterid desc';
            break;
        case  6:
            $order = 'product, meterid';
            break;
        case  7:
            $order = 'who, action, meterid';
            break;
        case  8:
            $order = 'action, servermax desc, meterid';
            break;
        case  9:
            $order = 'clientmax desc, meterid';
            break;
        case 10:
            $order = 'username, meterid';
            break;
        default:
            break;
    }
    return $order;
}


function create_meter_report(&$env, $db)
{
    echo again($env);

    $name = textbox('name', 60, '');
    $mail = textbox('mail', 60, '');
    $dmin = textbox('dmin', 20, '');
    $dmax = textbox('dmax', 20, '');

    $dtt  = checkbox('dtt', 1);
    $dct  = checkbox('dct', 1);
    $fip  = checkbox('fip', 0);

    $opt  = $env['sort'];
    $cpt  = array('Client time', 'Server time');
    $ttu  = html_select('ttu', $cpt, 0, 1);
    $ttu  = html_select('ttu', $cpt, 0, 1);

    $o1   = html_select('o1', $opt, 0, 1);
    $o2   = html_select('o2', $opt, 0, 1);
    $o3   = html_select('o3', $opt, 0, 1);

    $tmin = array();
    $tmax = array();

    $days = array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 21, 28, 60, 90, 120, 150, 180, 360, 730);
    $now  = time();
    $last = midnight($now);
    $next = days_ago($last, -1);
    $tmax[$next] = 'Midnight Tonight';
    $tmax[$last] = 'Midnight Last Night';
    $tmin[$last] = $tmax[$last];
    $time = $last;

    reset($days);
    foreach ($days as $key => $day) {
        $time = days_ago($next, $day);
        $text = date('m/d/y', $time) . " ($day days ago)";
        $tmin[$time] = $text;
        $tmax[$time] = $text;
    }

    $umin = html_select('umin', $tmin, $last, 1);
    $umax = html_select('umax', $tmax, $next, 1);
    $type   = html_select('type', $env['kind'], 0, 1);
    $submit = button('Create Report');
    $cancel = button('Cancel');

    echo post_new();
    //  echo post_self();
    echo hidden('action', 'mr');

    echo table_header();
    echo pretty_header('Create Meter Report', 2);
    echo two_col('Report name:', $name);
    echo two_col('E-mail recipients:', $mail);
    echo two_col('Information portal:', $fip);
    echo two_col('Enter start date:', $dmin);
    echo two_col('Select start date:', $umin);
    echo two_col('Enter end date:', $dmax);
    echo two_col('Select end date:', $umax);
    echo two_col('Time to use:', $ttu);
    echo two_col('Display time totals:', $dtt);
    echo two_col('Display count totals:', $dct);
    echo two_col('Report Type:', $type);
    echo two_col('Total by:', $o1);
    echo two_col('Within that, total by:', $o2);
    echo two_col('Within that, total by:', $o3);
    echo two_col($submit, $cancel);
    echo table_footer();

    $txt = date_doc();
    $txt = fontspeak($txt);
    echo "<p>$txt</p>\n";

    echo form_footer();
    echo again($env);
}

/*
    |  Level 0:  7 columns, 5 data, 2 time
    |  Level 1:  6 columns, 4 data, 2 time
    |  Level 2:  5 columns, 3 data, 2 time
    |  Level 3:  4 columns, 2 data, 2 time
    */

function table_count($env, $time, $count)
{
    $dct = $env['dct'];
    $dtt = $env['dtt'];
    $lvl = $env['level'];
    $txt = time_string($time);
    $spn = 5 - $lvl;
    $t1  = ($dct) ? "$count items total." : '<br>';
    $t2  = ($dtt) ? "total $txt" : '<br>';
    $row = "<td colspan=\"$spn\">$t1</td><td colspan=\"2\">$t2</td>";
    $col = "<tr>$row</tr>\n";
    return $col;
}


function dumphead($env, $hh)
{
    $lv = $env['level'];
    $o1 = $env['o1'];
    $o2 = $env['o2'];
    $o3 = $env['o3'];
    $tx = '';

    if ($lv == 1) {
        $tx = pretty_header($hh[0], 6);
    }
    if ($lv == 2) {
        $n0 = $env['sort'][$o1];
        $n1 = $env['sort'][$o2];
        $h0 = $hh[0];
        $h1 = $hh[1];
        $tx = <<< HERE

<tr>
  <td colspan="5">
    <table width="100%">
    <tr>
      <th align="center">$n0</th>
      <th align="center">$n1</th>
    </tr>
    <tr>
      <td align="center">$h0</td>
      <td align="center">$h1</td>
    </tr>
    </table>
  </td>
</tr>

HERE;
    }
    if ($lv == 3) {
        $n0 = $env['sort'][$o1];
        $n1 = $env['sort'][$o2];
        $n2 = $env['sort'][$o3];

        $h0 = $hh[0];
        $h1 = $hh[1];
        $h2 = $hh[2];
        $tx = <<< HERE

<tr>
  <td colspan="4">
    <table width="100%">
    <tr>
      <th align="center">$n0</th>
      <th align="center">$n1</th>
      <th align="center">$n2</th>
    <tr>
    </tr>
      <td align="center">$h0</td>
      <td align="center">$h1</td>
      <td align="center">$h2</td>
    </tr>
    </table>
  </td>
</tr>

HERE;
    }
    return $tx;
}


function dumplist($env, $hh, $list, $time, $count)
{
    $txt = '';
    $dct = $env['dct'];
    $dtt = $env['dtt'];
    if ($list) {
        $txt .= table_header();
        $txt .= dumphead($env, $hh);
        $txt .= table_data($env['head'], 1);

        reset($list);
        foreach ($list as $key => $args) {
            $txt .= table_data($args, 0);
        }
        if (($dtt) || ($dct)) {
            $txt .= table_count($env, $time, $count);
        }
        $txt .= table_footer();
    }
    return $txt;
}


function eventname($type)
{
    switch ($type) {
        case constProcessCreation:
            return 'start';
        case constProcessCompletion:
            return 'stop';
        case constProcessLife:
            return 'process';
        default:
            return "unknown $type";
    }
}


function orderby(&$env)
{
    $s  = $env['flds'];
    $o1 = $env['o1'];
    $o2 = $env['o2'];
    $o3 = $env['o3'];
    $a  = array();
    if ($o1) $a[] = $s[$o1];
    if (($o2) && ($o2 != $o1))
        $a[] = $s[$o2];
    if (($o3) && ($o3 != $o1) && ($o3 != $o2))
        $a[] = $s[$o3];
    $a[] = 'clienttime desc';
    $a[] = 'meterid';
    return join(',', $a);
}


function times(&$time, $level, $d1, $d2, $d3, $secs)
{
    switch ($level) {
        case 0:
            if (isset($time[0]))
                $time[0] += $secs;
            else
                $time[0] = $secs;
            break;
        case 1:
            if (isset($time[$d1]))
                $time[$d1] += $secs;
            else
                $time[$d1] = $secs;
            break;
        case 2:
            if (isset($time[$d1][$d2]))
                $time[$d1][$d2] += $secs;
            else
                $time[$d1][$d2] = $secs;
            break;
        case 3:
            if (isset($time[$d1][$d2][$d3]))
                $time[$d1][$d2][$d3] += $secs;
            else
                $time[$d1][$d2][$d3] = $secs;
            break;
    }
}

function counts(&$inst, $level, $d1, $d2, $d3)
{
    switch ($level) {
        case 0:
            if (isset($inst[0]))
                $inst[0]++;
            else
                $inst[0] = 1;
            break;
        case 1:
            if (isset($inst[$d1]))
                $inst[$d1]++;
            else
                $inst[$d1] = 1;
            break;
        case 2:
            if (isset($inst[$d1][$d2]))
                $inst[$d1][$d2]++;
            else
                $inst[$d1][$d2] = 1;
            break;
        case 3:
            if (isset($inst[$d1][$d2][$d3]))
                $inst[$d1][$d2][$d3]++;
            else
                $inst[$d1][$d2][$d3] = 1;
            break;
    }
}

function level0(&$env, &$out, $time, $inst)
{
    $dct = $env['dct'];
    $dtt = $env['dtt'];
    $t0  = ($dtt) ? $time[0] : 0;
    $c0  = ($dct) ? $inst[0] : 0;
    $h0  = array();
    $ts  = time_string($t0);
    $cs  = "Grand Total $c0 events";
    $ts  = "$ts elapsed.";
    $m   = '';
    if (($dct) && ($dtt)) {
        $m .= "<h2>$cs, $ts</h2>\n";
    } else {
        if (($dct) || ($dtt)) {
            $tx = ($dct) ? $cs : $ts;
            $m .= "<h2>$tx</h2>\n";
        }
    }
    $m .= dumplist($env, $h0, $out, $t0, $c0);
    return $m;
}



function level1(&$env, &$out, $time, $inst)
{
    $dct  = $env['dct'];
    $dtt  = $env['dtt'];
    $o1   = $env['o1'];

    $m  = '';
    $gt = '<b>Grand Total</b>';
    $br = '<br>';
    $h1 = $env['sort'][$o1];
    $head = array($h1, 'Counts', 'Elapsed Time');
    $m .= table_header();
    $m .= pretty_header('Subtotals', 3);
    $m .= table_data($head, 1);

    $t0  = 0;
    $c0  = 0;
    reset($out);
    foreach ($out as $d1 => $a1) {
        $t1 = ($dtt) ? $time[$d1] : 0;
        $c1 = ($dct) ? $inst[$d1] : 0;
        $c0 += $c1;
        $t0 += $t1;
        $ts = time_string($t1);
        $args = array($d1, $c1, $ts);
        $m .= table_data($args, 0);
    }
    $ts = time_string($t0);
    $args = array($gt, $c0, $ts);
    $m .= table_data($args, 0);
    $m .= table_footer();

    reset($out);
    foreach ($out as $d1 => $a1) {
        $h1 = array($d1);
        $t1 = ($dtt) ? $time[$d1] : 0;
        $c1 = ($dct) ? $inst[$d1] : 0;
        $m .= dumplist($env, $h1, $a1, $t1, $c1);
    }
    return $m;
}


function level2(&$env, &$out, $time, $inst)
{
    $dct = $env['dct'];
    $dtt = $env['dtt'];
    $o1  = $env['o1'];
    $o2  = $env['o2'];
    $l1  = array();
    $l2  = array();

    $m  = '';
    $t0 = 0;
    $c0 = 0;
    reset($out);
    foreach ($out as $d1 => $a1) {
        $t1 = 0;
        $c1 = 0;
        foreach ($a1 as $d2 => $a2) {
            $t2 = ($dtt) ? $time[$d1][$d2] : 0;
            $c2 = ($dct) ? $inst[$d1][$d2] : 0;
            $l2[$d1][$d2]['evnt'] = $c2;
            $l2[$d1][$d2]['time'] = $t2;
            $t1 += $t2;
            $c1 += $c2;
        }
        $l1[$d1]['evnt'] = $c1;
        $l1[$d1]['time'] = $t1;
        $t0 += $t1;
        $c0 += $c1;
    }

    $gt = '<b>Grand Total</b>';
    $br = '<br>';
    $h1 = $env['sort'][$o1];
    $h2 = $env['sort'][$o2];

    $head = array($h1, $h2, 'Counts', 'Elapsed Time');
    $m .= table_header();
    $m .= pretty_header('Subtotals', 4);
    $m .= table_data($head, 1);

    reset($l2);
    foreach ($l2 as $d1 => $a1) {
        $p1 = $d1;
        reset($a1);
        foreach ($a1 as $d2 => $a2) {
            $c2 = $a2['evnt'];
            $t2 = $a2['time'];
            $ts = time_string($t2);
            $args = array($p1, $d2, $c2, $ts);
            $m .= table_data($args, 0);
            $p1 = $br;
        }
        $c1 = $l1[$d1]['evnt'];
        $t1 = $l1[$d1]['time'];
        $ts = time_string($t1);
        $args = array($p1, $br, $c1, $ts);
        $m .= table_data($args, 0);
    }
    $ts = time_string($t0);
    $args = array($gt, $br, $c0, $ts);
    $m .= table_data($args, 0);
    $m .= table_footer();

    reset($out);
    foreach ($out as $d1 => $a1) {
        foreach ($a1 as $d2 => $a2) {
            $h2 = array($d1, $d2);
            $t2 = ($dtt) ? $time[$d1][$d2] : 0;
            $c2 = ($dct) ? $inst[$d1][$d2] : 0;
            $m .= dumplist($env, $h2, $a2, $t2, $c2);
        }
    }
    return $m;
}



function level3(&$env, &$out, $time, $inst)
{
    $dct = $env['dct'];
    $dtt = $env['dtt'];
    $o1  = $env['o1'];
    $o2  = $env['o2'];
    $o3  = $env['o3'];
    $l1  = array();
    $l2  = array();
    $l3  = array();
    $m   = '';

    $t0 = 0;
    $c0 = 0;
    reset($out);
    foreach ($out as $d1 => $a1) {
        $t1 = 0;
        $c1 = 0;
        reset($a1);
        foreach ($a1 as $d2 => $a2) {
            $t2 = 0;
            $c2 = 0;
            reset($a2);
            foreach ($a2 as $d3 => $a3) {
                $t3 = ($dtt) ? $time[$d1][$d2][$d3] : 0;
                $c3 = ($dtt) ? $inst[$d1][$d2][$d3] : 0;
                $l3[$d1][$d2][$d3]['time'] = $t3;
                $l3[$d1][$d2][$d3]['evnt'] = $c3;
                $t2 += $t3;
                $c2 += $c3;
            }
            $l2[$d1][$d2]['time'] = $t2;
            $l2[$d1][$d2]['evnt'] = $c2;
            $c1 += $c2;
            $t1 += $t2;
        }
        $l1[$d1]['evnt'] = $c1;
        $l1[$d1]['time'] = $t1;
        $t0 += $t1;
        $c0 += $c1;
    }

    $st = '&nbsp; &nbsp;<i>(subtotal)</i> &nbsp; &nbsp; ';
    $gt = '<b>Grand Total</b>';
    $br = '<br>';
    $h1 = $env['sort'][$o1];
    $h2 = $env['sort'][$o2];
    $h3 = $env['sort'][$o3];

    $head = array($h1, $h2, $h3, 'Counts', 'Elapsed Time');
    $m .= table_header();
    $m .= pretty_header('Subtotals', 5);
    $m .= table_data($head, 1);

    reset($l3);
    foreach ($l3 as $d1 => $a1) {
        $p1 = $d1;
        reset($a1);
        foreach ($a1 as $d2 => $a2) {
            $p2 = $d2;
            reset($a2);
            foreach ($a2 as $d3 => $a3) {
                $c3 = $a3['evnt'];
                $t3 = $a3['time'];
                $ts = time_string($t3);
                $args = array($p1, $p2, $d3, $c3, $ts);
                $m .= table_data($args, 0);
                $p1 = $br;
                $p2 = $br;
            }

            $c2 = $l2[$d1][$d2]['evnt'];
            $t2 = $l2[$d1][$d2]['time'];
            $ts = time_string($t2);

            $args = array($p1, $p2, $br, $c2, $ts);
            $m .= table_data($args, 0);
        }

        $c1 = $l1[$d1]['evnt'];
        $t1 = $l1[$d1]['time'];
        $ts = time_string($t1);

        $args = array($p1, $br, $br, $c1, $ts);
        $m .= table_data($args, 0);
    }
    $ts = time_string($t0);
    $args = array($gt, $br, $br, $c0, $ts);
    $m .= table_data($args, 0);
    $m .= table_footer();

    reset($out);
    foreach ($out as $d1 => $a1) {
        reset($a1);
        foreach ($a1 as $d2 => $a2) {
            reset($a2);
            foreach ($a2 as $d3 => $a3) {
                $h3 = array($d1, $d2, $d3);
                $t3 = ($dtt) ? $time[$d1][$d2][$d3] : 0;
                $c3 = ($dtt) ? $inst[$d1][$d2][$d3] : 0;
                $m .= dumplist($env, $h3, $a3, $t3, $c3);
            }
        }
    }
    return $m;
}


function create_file_report(&$env, $head, $num, $sites, &$body)
{
    echo "<p>Attempting to save report <b>$head</b> ...<p>\n";
    $db   = $env['mdb'];
    $good = false;

    $days = server_int('file_expire_days', 120, $db);
    $type = 'Meter Report';
    $row  = array();
    $row['name']      = $head;
    $row['username']  = $env['auth'];

    $tmpfile_h = tmpfile();
    if (!$tmpfile_h) return false;

    if (my_write($tmpfile_h, $body)) {
        $row['tmpfile_h'] = $tmpfile_h;
        $good = write_file($env, $row, $type, $days, $num, $sites);
        fclose($tmpfile_h);
    }
    return $good;
}


function create_mail_report(&$env, $head, $num, &$body)
{
    echo "<p>Attempting to mail report <b>$head</b> ...<p>\n";

    $db   = $env['mdb'];
    $comp = $env['comp'];
    $odir = $env['comp']['odir'];
    $user = $env['auth']; // Extract the user.
    $host = server_href($db);
    $logo = logo_state($user, $comp, $db);
    $iref = logo_iref($logo, $host);

    $href = "$host/$odir/index.php";
    $link = "<a href=\"$href\">$iref</a>";
    $logo = <<< HERE

<table>
<tr>
  <td align="left" valign="bottom">
    $link
  </td>
  <td align="right">
    <br>
  </td>
</tr>
</table>

<p>
 <font face="verdana,helvetica" color="#333399" size="4">
   $head
 </font>
</p>

HERE;
    $m  = '';
    $m .= html_header($head);
    $m .= $logo;
    $m .= $body;
    $m .= standard_html_footer($db);
    $good = false;

    $tmpfile_h = tmpfile();
    if (!$tmpfile_h) return false;

    if (my_write($tmpfile_h, $m)) {
        $empty = array();
        $row   = array();
        $sites = array();
        $row['format']   = 'html';
        $row['name']     = $head;
        $row['username'] = $env['auth'];

        $serv = server_name($db);
        $def  = "meter@$serv";
        $dst  = $env['mail'];
        $sub  = "meter: $head ($num records)";
        $src  = server_def('meter_report_sender', $def, $db);
        $good = report_mail($row, $dst, $sub, $tmpfile_h, $src, $empty, $serv, $sites);
        fclose($tmpfile_h);
    }
    return $good;
}



function meter_report(&$env, $db)
{
    echo again($env);
    $aaa   = microtime();
    $o1    = $env['o1'];
    $o2    = $env['o2'];
    $o3    = $env['o3'];
    $ttu   = $env['ttu'];
    $dct   = $env['dct'];   // display count totals
    $dtt   = $env['dtt'];   // display time totals
    $fip   = $env['fip'];   // file "information portal"
    $carr  = $env['carr'];
    $enab  = $env['enab'];
    $mail  = $env['mail'];
    $name  = $env['name'];
    $type  = $env['type'];
    $umax  = $env['umax'];
    $umin  = $env['umin'];
    $debug = $env['debug'];
    $level = $env['level'];
    $out   = array();
    $inst  = array();
    $time  = array();
    $keys  = array();
    $order = orderby($env);

    $head  = ($name) ? $name : 'Unnamed Report';
    $fate  = 'completed';

    $show = (($debug) && ($mail == '') && ($fip == 0));
    $now  = time();
    $num  = 0;
    $txt  = '';
    $msg  = '';
    $dmin = datestring($umin);
    $dmax = datestring($umax);
    $dnow = datestring($now);
    $access = ($carr) ? db_access($carr) : "''";

    debug_note("o1:$o1 o2:$o2 o3:$o3 ttu:$ttu dct:$dct dtt:$dtt fip:$fip");

    switch ($env['type']) {
        case  0:
            $et = "and eventtype = 2\n";
            break;
        case  1:
            $et = "and eventtype != 2\n";
            break;
        case  2:
            $et = '';
            break;
        default:
            $et = "and eventtype = 2\n";
            break;
    }
    $tfld = ($ttu) ? 'servertime' : 'clienttime';
    $ttag = ($ttu) ? 'Server Time' : 'Client Time';
    $sql  = "select * from Meter\n"
        . " where $tfld between $umin and $umax\n"
        . " and sitename in ($access)\n"
        . $et
        . " order by $order";
    $list = find_many($sql, $db);
    $num  = safe_count($list);

    $rows = array();
    html_append($rows, 'Report Title', $head);
    html_append($rows, 'Creator', $env['auth']);
    if ($mail != '') {
        html_append($rows, 'Recipients', $mail);
    }
    $txt .= html_rule();
    $txt .= html_table(3, 0, 'C0C0C0', 0);
    $txt .= html_data($rows);
    $txt .= html_empty_row(2);
    $rows = array();
    html_append($rows, 'Report Type', $env['kind'][$type]);
    if ($o1) {
        html_append($rows, 'Total By', $env['sort'][$o1]);
    }
    if ($o2) {
        html_append($rows, 'Total By', $env['sort'][$o2]);
    }
    if ($o3) {
        html_append($rows, 'Total By', $env['sort'][$o3]);
    }
    html_append($rows, 'Using', $ttag);
    html_append($rows, 'Records', $num);
    $txt .= html_data($rows);
    $txt .= html_empty_row(2);
    $rows = array();
    html_append($rows, 'Start Date', $dmin);
    html_append($rows, 'End Date', $dmax);
    html_append($rows, 'Report Date', $dnow);
    if ($umax > $umin) {
        $secs = $umax - $umin;
        $hour = intval(round($secs / 3600));
        $days = intval(round($secs / 86400));
        $disp = ($days > 3) ? "$days days" : "$hour hours";
        html_append($rows, 'Elapsed Time', $disp);
    }
    $txt .= html_data($rows);
    $txt .= html_close_table();
    $txt .= html_rule();
    if ($list) {
        $name1 = ($o1) ? $env['flds'][$o1] : '';
        $name2 = ($o2) ? $env['flds'][$o2] : '';
        $name3 = ($o3) ? $env['flds'][$o3] : '';

        reset($list);
        foreach ($list as $key => $row) {
            $smin = $row['servertime'];
            $cmin = $row['clienttime'];
            $smax = $row['servermax'];
            $cmax = $row['clientmax'];
            $type = $row['eventtype'];
            $name = $row['product'];
            $file = $row['exename'];
            $site = $row['sitename'];
            $host = $row['machine'];
            $user = $row['username'];

            $d1 = ($o1) ? $row[$name1] : '';
            $d2 = ($o2) ? $row[$name2] : '';
            $d3 = ($o3) ? $row[$name3] : '';

            $tmin = 0;
            $tmax = 0;
            $secs = 0;

            switch ($type) {
                case constProcessCreation:
                    $tmin = ($ttu) ? $smin : $cmin;
                    break;
                case constProcessCompletion:
                    $tmax = ($ttu) ? $smax : $cmax;
                    break;
                case constProcessLife:
                    $tmin = ($ttu) ? $smin : $cmin;
                    $tmax = ($ttu) ? $smax : $cmax;
                    $secs = $tmax - $tmin;
                    break;
                default:
                    break;
            }

            if ($dtt) {
                times($time, $level, $d1, $d2, $d3, $secs);
            }

            if ($dct) {
                counts($inst, $level, $d1, $d2, $d3);
            }

            $tmin = fulldate($tmin);
            $tmax = fulldate($tmax);
            if (($show) && ($secs)) {
                $text = time_string($secs);
                $text = green("($text)");
                $tmax = "$tmax &nbsp; $text";
            }

            $temp = array('zero', $site, $host, $user, $name, $file, $tmin, $tmax);
            $args = array();
            for ($i = 0; $i <= 7; $i++) {
                if ($enab[$i]) {
                    $args[] = $temp[$i];
                }
            }
            switch ($level) {
                case 0:
                    $out[]                = $args;
                    break;
                case 1:
                    $out[$d1][]           = $args;
                    break;
                case 2:
                    $out[$d1][$d2][]      = $args;
                    break;
                case 3:
                    $out[$d1][$d2][$d3][] = $args;
                    break;
            }
            $keys[$site] = true;
        }

        unset($list);

        switch ($level) {
            case 0:
                $msg = level0($env, $out, $time, $inst);
                break;
            case 1:
                $msg = level1($env, $out, $time, $inst);
                break;
            case 2:
                $msg = level2($env, $out, $time, $inst);
                break;
            case 3:
                $msg = level3($env, $out, $time, $inst);
                break;
        }
    }
    $body = $txt . $msg;
    $txt  = '';
    $msg  = '';
    $len  = strlen($body);
    $bbb  = microtime();
    $usec = microtime_diff($aaa, $bbb);
    $secs = microtime_show($usec);
    debug_note("body: $len bytes, $num records ($secs)");

    echo $body;

    debug_note("body: $len bytes, $num records ($secs)");

    if ($fip) {
        $sites = safe_array_keys($keys);
        $good  = create_file_report($env, $head, $num, $sites, $body);
        $fate  = ($good) ? 'has been saved' : 'was not created';
    }
    if ($mail) {
        $good = create_mail_report($env, $head, $num, $body);
        $fate = ($good) ? 'has been mailed' : 'was not mailed';
    }
    echo "<p>Report $fate.</p>\n";
    echo again($env);
}


function count_meter($db)
{
    $sql = 'select count(*) from Meter';
    return find_scalar($sql, $db);
}

function debug_meter($env, $verb, $db)
{
    debug_note("debug_meter");

    $min  = constProcessCreation;
    $max  = constProcessCompletion;
    $life = constProcessLife;

    $list  = array();
    $self  = $env['self'];
    $priv  = $env['priv'];
    if ($priv) {
        $limt  = $env['limt'];
        $mcnt  = count_meter($db);
        $order = order($env['ord']);
        $sql   = "select * from Meter\n"
            . " order by clienttime desc\n"
            . " limit $limt";
        $list = find_many($sql, $db);
    }
    if ($list) {
        $jump = again($env);
        echo $jump;

        $num = safe_count($list);
        $txt = fontspeak("Showing $num of $mcnt total records.");
        echo "<br>$txt<br>\n";

        $head = explode('|', 'product|owner|mid|user|file|pid|type|cmin|cmax|server|host|site');
        if ($verb) {
            $head[] = 'smax';
            $head[] = 'uuid';
        }
        $cols = safe_count($head);
        $name = ($verb) ? 'Verbose Meter' : 'Debug Meter';

        echo table_header();
        echo pretty_header($name, $cols);
        echo table_data($head, 1);

        reset($list);
        foreach ($list as $key => $row) {
            $mid  = $row['meterid'];
            $smin = $row['servertime'];
            $cmin = $row['clienttime'];
            $cmax = $row['clientmax'];
            $smax = $row['servermax'];
            $type = $row['eventtype'];

            $pid  = disp($row, 'processid');
            $file = disp($row, 'exename');
            $site = disp($row, 'sitename');
            $host = disp($row, 'machine');
            $uuid = disp($row, 'uuid');
            $user = disp($row, 'username');
            $name = disp($row, 'product');
            $ownr = disp($row, 'owner');

            $smin = fulldate($smin);
            $cmin = fulldate($cmin);
            $cmax = fulldate($cmax);
            $smax = fulldate($smax);

            $text = eventname($type);
            $args = array($name, $ownr, $mid, $user, $file, $pid, $text, $cmin, $cmax, $smin, $host, $site);
            if ($verb) {
                $args[] = $smax;
                $args[] = $uuid;
            }
            echo table_data($args, 0);
        }
        echo table_footer();

        echo $jump;
    } else {
        echo again($env);
        echo "There are no meter records.";
        echo again($env);
    }
}

function unknown_action($env, $db)
{
    debug_note("unknown action");
}

/*
    |  This function takes a process completion event
    |  and attempts to find a matching process creation
    |  event.
    |
    |  The matching creation event must of course be
    |  on the same machine as the completion event, and
    |  share the same exename, processid, and username.
    |
    |  Note that windows can re-use the same processid
    |  if a program gets restarted.  In that case we want
    |  to be sure to use the most recent process creation
    |  event not later than our completion event.
    |
    |  this is mostly the same as the rpc function.
    */

function meter_seek(&$row, $db)
{
    $type = constProcessCreation;
    $life = constProcessLife;
    $smax = $row['servermax'];
    $cmax = $row['clientmax'];
    $uuid = safe_addslashes($row['uuid']);
    $name = safe_addslashes($row['exename']);
    $user = safe_addslashes($row['username']);
    $pid  = safe_addslashes($row['processid']);
    $sql  = "select * from Meter where\n"
        . " eventtype = $type and\n"
        . " uuid = '$uuid' and\n"
        . " exename = '$name' and\n"
        . " processid = '$pid' and\n"
        . " username = '$user' and\n"
        . " clienttime <= $cmax\n"
        . " order by clienttime desc\n"
        . " limit 1";
    $old  = $row['meterid'];
    $met  = find_one($sql, $db);
    if ($met) {
        $new  = $met['meterid'];
        $cmin = $met['clienttime'];
        $sql  = "update Meter set\n"
            . " eventtype = $life,\n"
            . " clientmax = $cmax,\n"
            . " servermax = $smax\n"
            . " where meterid = $new";
        $res  = command($sql, $db);
        if (($res) && (mysqli_affected_rows($db) == 1)) {
            // success ... we don't really need the
            // debug information, we should remove
            // this later.

            $host = $row['machine'];
            $name = $row['exename'];
            $site = $row['sitename'];
            $secs = $cmax - $cmin;
            $cmin = date('H:i:s', $cmin);
            $cmax = date('H:i:s', $cmax);
            $txt  = "meter: $host at $site, $name (m:$new) $secs ($cmin to $cmax)";
            logs::log(__FILE__, __LINE__, $txt, 0);
            echo "$txt<br>\n";

            $sql  = "delete from Meter\n"
                . " where meterid = $old";
            command($sql, $db);
        }
    } else {
        // a process has completed, but we
        // have no record of it starting.

        $host = $row['machine'];
        $name = $row['exename'];
        $site = $row['sitename'];
        $pid  = $row['processid'];
        $cmax = date('m/d H:i:s', $cmax);
        $txt  = "meter: $host at $site, $name (m:$old) $cmax never started";
        logs::log(__FILE__, __LINE__, $txt, 0);
        echo "$txt<br>\n";
    }
}


function coagulate(&$env, $db)
{
    $min  = constProcessCreation;
    $max  = constProcessCompletion;
    $life = constProcessLife;

    $sql  = "select * from Meter\n"
        . " where eventtype = $max\n"
        . " order by clienttime desc\n"
        . " limit 1000";
    $list = find_many($sql, $db);
    reset($list);
    foreach ($list as $key => $row) {
        meter_seek($row, $db);
    }
}

function single_shot(&$env, $db)
{
    $sql  = "delete from Meter\n"
        . " where product=''\n"
        . " and exename = ''";
    $res  = redcommand($sql, $db);

    $sql  = "delete from Meter\n"
        . " where eventtype = 6";
    $res  = redcommand($sql, $db);

    /* --------------------------------

        // update records from before
        // product and owner was logged

        $eps  = "Epsilon Programmer's Editor";
        $qp   = safe_addslashes($eps);
        $sql  = "update Meter set\n"
              . " product='$qp',\n"
              . " owner='admin'\n"
              . " where product = '' and\n"
              . " exename like '%epsilon.exe'";
        $res  = redcommand($sql,$db);

        $sql  = "update Meter set\n"
              . " product='blah',\n"
              . " owner='admin'\n"
              . " where product = '' and\n"
              . " exename like '%calc.exe'";
        $res  = redcommand($sql,$db);

        $sql  = "update Meter set\n"
              . " product='blah',\n"
              . " owner='admin'\n"
              . " where product = '' and\n"
              . " exename like '%calc.exe'";
        $res  = redcommand($sql,$db);

        $sql  = "update Meter set\n"
              . " product='PrcView',\n"
              . " owner='admin'\n"
              . " where product = '' and\n"
              . " exename like '%PrcView.exe'";
        $res  = redcommand($sql,$db);

        $sql  = "update Meter set\n"
              . " product='Process Explorer',\n"
              . " owner='admin'\n"
              . " where product = '' and\n"
              . " exename like '%procexp.exe'";
        $res  = redcommand($sql,$db);

        $sql  = "update Meter set\n"
              . " product='TCP View',\n"
              . " owner='admin'\n"
              . " where product = '' and\n"
              . " exename like '%TCPVIEW.EXE'";
        $res  = redcommand($sql,$db);

        $sql  = "update Meter set\n"
              . " product='Word For Windows',\n"
              . " owner='admin'\n"
              . " where product = '' and\n"
              . " exename like '%winword.exe'";
        $res  = redcommand($sql,$db);

        $sql  = "update Meter set\n"
              . " product='Secure CRT',\n"
              . " owner='admin'\n"
              . " where product = '' and\n"
              . " exename like '%SecureCRT.EXE'";
        $res  = redcommand($sql,$db);

        $sql  = "update Meter set\n"
              . " product='Windows Explorer',\n"
              . " owner='admin'\n"
              . " where product = '' and\n"
              . " exename like '%explorer.exe'";
        $res  = redcommand($sql,$db);

        $sql  = "update Meter set\n"
              . " product='Provision',\n"
              . " owner='admin'\n"
              . " where product = '' and\n"
              . " exename like '%provision.exe'";
        $res  = redcommand($sql,$db);
        -------------------------------- */
}

/*
    |  Main program
    */

$now = time();
$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
$nav  = provis_navigate();
$name = get_string('name', '');
$title = ($name) ? $name : 'Metering';
echo standard_html_header($title, $comp, $authuser, $nav, 0, 0, $db);

$date = datestring(time());

$action = get_string('action', 'cmr');
$dbg    = get_integer('debug', 1);
$user   = user_data($authuser, $db);
$priv   = @($user['priv_debug']) ?    1  : 0;
$debug  = @($user['priv_debug']) ?  $dbg : 0;
$filter = @($user['filtersites']) ?   1  : 0;
$limit  = get_integer('limit', 100);

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($debug) echo "<h2>$date</h2>";

$carr = site_array($authuser, $filter, $db);

$o1   = get_integer('o1', 0);
$o2   = get_integer('o2', 0);
$o3   = get_integer('o3', 0);

$sort = explode('|', 'None|Product|Site Name|Machine Name|User Name');
$flds = explode('|', 'invalid|product|sitename|machine|username');
$smax = safe_count($sort) - 1;
$o1   = value_range(0, $smax, $o1);
$o2   = value_range(0, $smax, $o2);
$o3   = value_range(0, $smax, $o3);

$txt  = '';

if (($o2 == 0) && ($o3 > 0)) {
    debug_note("promoting o3 to o2");
    $o2 = $o3;
    $o3 = 0;
}

if (($o1 == 0) && ($o2 > 0)) {
    debug_note("promoting o2 to o1");
    $o1 = $o2;
    $o2 = $o3;
    $o3 = 0;
}

while (($o2 > 0) && ($o2 == $o1)) {
    debug_note("error: o1 == o2");
    $name = $sort[$o2];
    $txt .= "error: cannot sort by $name twice.<br>\n";
    $o2 = $o3;
    $o3 = 0;
}

if (($o3 > 0) && ($o3 == $o1)) {
    debug_note("error: o3 == o1");
    $name = $sort[$o3];
    $txt .= "error: cannot sort by $name twice.<br>\n";
    $o3 = 0;
}
if (($o3 > 0) && ($o3 == $o2)) {
    debug_note("error: o3 == o2");
    $name = $sort[$o3];
    $txt .= "error: cannot sort by $name twice.<br>\n";
    $o3   = 0;
}

if ($txt) {
    echo $txt;
}

$level = 0;
if ($o1) $level++;
if ($o2) $level++;
if ($o3) $level++;

/*
    |  calculate the time range for this report.
    |  if the user enters something in the text box, try to use that.
    |  if that doesn't work, fall back to the pulldown values.
    */

$dmin = get_string('dmin', '');
$dmax = get_string('dmax', '');
$tmin = ($dmin == '') ? 0 : parsedate($dmin, $now);
$tmax = ($dmax == '') ? 0 : parsedate($dmax, $now);
$umin = ($tmin) ?  $tmin  : get_integer('umin', 0);
$umax = ($tmax) ?  $tmax  : get_integer('umax', 0);

/*
    |  if the user gets them backwards, it's probably
    |  easier to just fix it here.
    */

if ($umin > $umax) {
    $temp = $umin;
    $umin = $umax;
    $umax = $temp;
}

/*
    |  Level 0:  7 columns
    |  Level 1:  6 columns
    |  Level 2:  5 columns
    |  Level 3:  4 columns
    */

$text = '0|Site name|Machine name|User name|Product|Executable|Start Time|End Time';
$temp = explode('|', $text);
$maps = explode('|', '0|4|1|2|3|0|0|0');
$enab = explode('|', '0|1|1|1|1|1|1|1');

$enab[$maps[$o1]] = 0;
$enab[$maps[$o2]] = 0;
$enab[$maps[$o3]] = 0;

$head = array();
for ($i = 0; $i <= 7; $i++) {
    if ($enab[$i]) {
        $head[] = $temp[$i];
    }
}

$limt = get_integer('limt', 50);
$type = get_integer('type', 0);
$ord  = get_integer('ord', 0);
$ttu  = get_integer('ttu', 0);
$dct  = get_integer('dct', 0);
$dtt  = get_integer('dtt', 0);
$fip  = get_integer('fip', 0);
$post = get_string('submit', '');
$env  = array();
$env['db']     = $db;
$env['o1']     = $o1;
$env['o2']     = $o2;
$env['o3']     = $o3;
$env['mdb']    = $db;
$env['ord']    = value_range(0, 10, $ord);
$env['ttu']    = value_range(0, 1, $ttu);
$env['dct']    = value_range(0, 1, $dct);
$env['dtt']    = value_range(0, 1, $dtt);
$env['fip']    = value_range(0, 1, $fip);
$env['type']   = value_range(0, 2, $type);
$env['limt']   = value_range(10, 2000, $limt);
$env['kind']   = array('Process', 'Exception', 'Combined');
$env['head']   = $head;
$env['carr']   = $carr;
$env['enab']   = $enab;
$env['comp']   = $comp;
$env['mail']   = get_string('mail', '');
$env['root']   = $comp['root'];
$env['umin']   = $umin;
$env['umax']   = $umax;
$env['post']   = $post;
$env['name']   = $name;
$env['priv']   = $priv;
$env['flds']   = $flds;
$env['sort']   = $sort;
$env['auth']   = $authuser;
$env['self']   = server_var('PHP_SELF');
$env['args']   = server_var('QUERY_STRING');
$env['user']   = $user;
$env['debug']  = $debug;
$env['level']  = $level;
$env['limit']  = $limit;
$env['action'] = $action;

if ($post == 'Cancel') {
    $action = 'cmr';
}

db_change($GLOBALS['PREFIX'] . 'provision', $db);
switch ($action) {
    case 'dbg':
        debug_meter($env, 0, $db);
        break;
    case 'vrb':
        debug_meter($env, 1, $db);
        break;
    case 'cmr':
        create_meter_report($env, $db);
        break;
    case 'mr':
        meter_report($env, $db);
        break;
    case 'xxx':
        single_shot($env, $db);
        break;
    case 'coag':
        coagulate($env, $db);
        break;
    default:
        unknown_action($env, $db);
        break;
}
db_change($GLOBALS['PREFIX'] . 'core', $db);
echo head_standard_html_footer($authuser, $db);
