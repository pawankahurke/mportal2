<?php

/*
Revision history:

Date        Who     What
----        ---     ----
14-Feb-03   EWB     Created.
24-Feb-03   EWB     Report the correct Asset Query.
24-Feb-03   EWB     Color shows enabled/disabled.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   NL      Move debug_note line below $debug.
30-Apr-03   NL      Delete RptSiteFilters when deleting report.
10-Sep-03   EWB     Show Site Filters with report details.
10-Sep-03   EWB     Report number of items removed when deleting report.
10-Sep-03   EWB     Asset Report Filters Page.
22-Oct-03   EWB     Show last/next report time.
23-Oct-03   EWB     Establish a default limit.
24-Oct-03   EWB     Display/Sort by Create/Modify
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title  = 'Debug Asset Report';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-head.php');
include('../lib/l-rcmd.php');
include('../lib/l-rprt.php');
include('../lib/l-jump.php');
include('../lib/l-gsql.php');
include('../lib/l-user.php');


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

function pretty_header($name, $width)
{
    return <<< HERE
<tr>
    <th colspan="$width" bgcolor="#333399">
        <font color="white">
            $name
        </font>
    </th>
</tr>

HERE;
}

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


function bold($text)
{
    return "<b>$text</b>";
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

function query($id, $db)
{
    $query = '';
    $sql  = "select * from AssetSearches where id = $id";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            while ($row = mysqli_fetch_array($res)) {
                $id    = $row['id'];
                $name  = $row['name'];
                $user  = $row['username'];
                $scop  = ($row['global']) ? 'g' : 'l';
                $query = "$user($scop) [$id] $name";
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $query;
}

function table_header()
{
    echo "<br>\n<table border='2' align='left' cellspacing='2' cellpadding='2'>\n";
}


function table_footer()
{
    echo "</table>\n<br clear='all'>\n<br>\n";
}

function delete_report($id, $db)
{
    $sql = "delete from AssetReports where id = $id";
    $res = redcommand($sql, $db);
    $rd  = affected($res, $db);

    $sql  = "delete from RptSiteFilters where assetreportid = $id";
    $res = redcommand($sql, $db);
    $fd  = affected($res, $db);
    echo "removed $rd reports and $fd filters<br>\n";
}


function fix_date(&$row, $name)
{
    $time = $row[$name];
    if ($time > 0) {
        $date = showtime($time);
        $row[$name] = "$date  ($time)";
    }
}


function detail_report($id, $db)
{
    $sql = "select * from AssetReports\n"
        . " where id = $id";
    $row = find_one($sql, $db);
    $sql = "select * from RptSiteFilters\n"
        . " where assetreportid = $id";
    $set = find_many($sql, $db);

    if ($row) {
        table_header();
        fix_date($row, 'this_run');
        fix_date($row, 'next_run');
        fix_date($row, 'last_run');
        fix_date($row, 'created');
        fix_date($row, 'modified');
        fix_date($row, 'umin');
        fix_date($row, 'umax');
        $qid = $row['searchid'];
        if ($qid > 0) {
            $row['searchid'] = query($qid, $db);
        }

        reset($row);
        foreach ($row as $key => $data) {
            $num = intval($key);

            if (($num == 0) && ($key != '0')) {
                $valu = ($data == '') ? '<br>' : $data;
                $args = array($key, $valu);
                color_data($args, 'white', 0);
            }
        }
        table_footer();
    }

    if ($set) {
        table_header();
        reset($set);
        foreach ($set as $key => $row) {
            $fid  = $row['id'];
            $rid  = $row['assetreportid'];
            $site = $row['site'];
            $filt = $row['filter'];
            $args = array($fid, $rid, $site, $filt);
            color_data($args, 'white', 0);
        }
        table_footer();
    }
}


function again($env)
{
    $self = $env['self'];
    $args = $env['args'];
    $href = ($args) ? "$self?$args" : $self;
    $act  = "$self?act";
    $ord  = "$self?ord";

    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link('debug.php', 'home');
    $a[] = html_link($href, 'again');
    $a[] = html_link("$act=filter", 'filter');
    $a[] = html_link("$act=enab", 'enabled');
    $a[] = html_link("$act=disb", 'disabled');
    $a[] = html_link("$act=last", 'last');
    $a[] = html_link("$ord=18", 'global');
    return jumplist($a);
}



function order($ord)
{
    switch ($ord) {
        case  0:
            return 'next_run, this_run desc, id';
        case  1:
            return 'next_run desc, id';
        case  2:
            return 'name, username, id';
        case  3:
            return 'name desc, username, id';
        case  4:
            return 'id';
        case  5:
            return 'id desc';
        case  6:
            return 'created desc, name, id';
        case  7:
            return 'created, name, id';
        case  8:
            return 'modified desc, name, id';
        case  9:
            return 'modified, name, id';
        case 10:
            return 'last_run desc, id';
        case 11:
            return 'last_run, id';
        case 12:
            return 'username, name, id';
        case 13:
            return 'username desc, name';
        case 14:
            return 'hour, minute, cycle, mday, wday, name, username, id';
        case 15:
            return 'cycle, mday, wday, name, username, id';
        case 16:
            return 'cycle, wday, mday, hour, minute, name, username, id';
        default:
            return order(0);
    }
}




function show_report($env, $past, $txt, $sql, $db)
{
    $num = 0;
    $gbl = 0;
    $lcl = 0;
    $now      = $env['now'];
    $ord      = $env['ord'];
    $act      = $env['act'];
    $limit    = $env['limt'];
    $self     = $env['self'];
    $daynames = $env['days'];
    $cyclenames = $env['cycl'];
    $res = redcommand($sql, $db);
    if ($res) {
        $num = mysqli_num_rows($res);
        if ($num > 0) {
            $o = "$self?act=$act&ord";
            $next = ($ord ==  0) ? "$o=1"  : "$o=0";   // next     0/1
            $nref = ($ord ==  2) ? "$o=3"  : "$o=2";   // name     2/3
            $iref = ($ord ==  4) ? "$o=5"  : "$o=4";   // id       4/5
            $cref = ($ord ==  6) ? "$o=7"  : "$o=6";   // create   6/7
            $mref = ($ord ==  8) ? "$o=9"  : "$o=8";   // modify   8/9
            $lref = ($ord == 10) ? "$o=11" : "$o=10";  // last     10/11
            $oref = ($ord == 12) ? "$o=13" : "$o=12";  // owner    12/13

            switch ($ord) {
                case 14:
                    $wref = "$o=15";
                    break;
                case 15:
                    $wref = "$o=16";
                    break;
                default:
                    $wref = "$o=14";
                    break;
            }
            $name = html_link($nref, 'Name');
            $own  = html_link($oref, 'Owner');
            $id   = html_link($iref, 'Id');
            $crt  = html_link($cref, 'Create');
            $mod  = html_link($mref, 'Modify');
            $last = html_link($lref, 'Last');
            $age  = html_link($lref, 'Age');
            $wait = html_link($next, 'Wait');
            $when = html_link($wref, 'When');
            $next = html_link($next, 'Next');

            $color = 'white';
            $head  = array(0, $name, $own, $id, $when, $crt, $mod, $last, $next, 'Action');
            $cols  = safe_count($head);
            $head[0] = ($past) ? $age : $wait;
            table_header();
            echo pretty_header($txt, $cols);
            color_data($head, 'white', 1);
            while ($row = mysqli_fetch_array($res)) {
                $id   = $row['id'];
                $name = $row['name'];
                $user = $row['username'];
                $glob = $row['global'];
                $hour = $row['hour'];
                $last = $row['last_run'];
                $that = $row['this_run'];
                $next = $row['next_run'];
                $crt  = $row['created'];
                $mod  = $row['modified'];
                $wday = $row['wday'];
                $mday = $row['mday'];
                $qid  = $row['searchid'];

                $enabled = $row['enabled'];
                $cycle   = $row['cycle'];
                $minute  = $row['minute'];


                $scop = ($glob) ? 'g' : 'l';
                $wait = '<br>';

                if ($enabled) {
                    $enab  = '';
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
                    $enab = '(d)';
                    $color = 'lightskyblue';
                    if ((0 < $last) && ($last < $now)) {
                        $wait = age($now - $last);
                    }
                }

                $act    = "$self?id=$id&act";
                $delete = html_link("$act=delete", 'delete');
                $detail = html_link("$act=detail", 'detail');
                $action = "$delete $detail";

                //            $qury   = query($qid,$db);

                $cname  = $cyclenames[$cycle];
                $when   = sprintf('%02d:%02d', $hour, $minute);
                switch ($cycle) {
                    case 0:
                        $when .= " $cname";
                        break;
                    case 1:
                        $when .= " " . $daynames[$wday];
                        break;
                    case 2:
                        $when .= " $cname $mday";
                        break;
                    case 3:
                        $when .= " $cname";
                        break;
                    case 4:
                        $when  = $cname;
                        break;
                }

                if ($glob)
                    $gbl++;
                else
                    $lcl++;
                $next = showtime($next);
                $last = showtime($last);
                $crt  = showtime($crt);
                $mod  = showtime($mod);
                $owner = "$user($scop)$enab";
                $args  = array($wait, $name, $owner, $id, $when, $crt, $mod, $last, $next, $action);
                color_data($args, $color, 0);
            }

            table_footer();
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
}


function debug_report($env, $db)
{
    $txt   = 'Asset Reports';
    $ord   = $env['ord'];
    $limit = $env['limt'];
    $order = order($ord);
    $sql   = "select * from AssetReports\n";
    $sql  .= " order by $order\n";
    $sql  .= " limit $limit";
    show_report($env, 0, $txt, $sql, $db);
}


function enab_report($env, $db)
{
    $txt   = 'Enabled Reports';
    $ord   = $env['ord'];
    $limit = $env['limt'];
    $order = order($ord);
    $sql = "select * from AssetReports\n"
        . " where enabled = 1\n"
        . " order by $order\n"
        . " limit $limit";
    show_report($env, 0, $txt, $sql, $db);
}

function disb_report($env, $db)
{
    $txt   = 'Disabled Reports';
    $ord   = $env['ord'];
    $limit = $env['limt'];
    $order = order($ord);
    $sql = "select * from AssetReports\n"
        . " where enabled = 0\n"
        . " order by $order\n"
        . " limit $limit";
    show_report($env, 1, $txt, $sql, $db);
}

function last_report($env, $db)
{
    $txt   = 'Recent Reports';
    $ord   = $env['ord'];
    $limit = $env['limt'];
    $order = order($ord);
    $sql = "select * from AssetReports\n"
        . " where last_run > 0\n"
        . " order by $order\n"
        . " limit $limit";
    show_report($env, 1, $txt, $sql, $db);
}


function filter_report($ord, $limit, $db)
{
    $list = array();
    $sql  = "select * from RptSiteFilters\n";
    $sql .= " where filter = 1\n";
    $sql .= " order by assetreportid, site";
    $rows = find_many($sql, $db);
    if ($rows) {
        reset($rows);
        foreach ($rows as $key => $data) {
            $id   = $data['assetreportid'];
            $site = $data['site'];
            $list[$id][] = $site;
        }
    }

    $crows = safe_count($rows);
    $clist = safe_count($list);
    debug_note("$crows filters, $clist reports");

    $num = 0;
    $gbl = 0;
    $lcl = 0;
    $order = order($ord);
    $sql = "select * from AssetReports order by $order limit $limit";
    $res = redcommand($sql, $db);
    if ($res) {
        $num = mysqli_num_rows($res);
        if ($num > 0) {
            $self = server_var('PHP_SELF');
            table_header();
            $color = 'white';
            $head  = array('Name', 'Owner', 'Id', 'Filter', 'Sites', 'Action');
            color_data($head, 'white', 1);
            while ($row = mysqli_fetch_array($res)) {
                $id       = $row['id'];
                $name     = $row['name'];
                $user     = $row['username'];
                $glob     = $row['global'];
                $enabled  = $row['enabled'];
                $filter   = $row['filtersites'];

                if (isset($list[$id])) {
                    $sites = join("<br>\n", $list[$id]);
                } else {
                    $sites = '<br>';
                }

                $scop = ($glob) ? 'g' : 'l';

                if ($enabled) {
                    $enab  = '';
                    $color = 'aquamarine';
                } else {
                    $enab = '(d)';
                    $color = 'lemonchiffon';
                }



                $act    = "$self?id=$id&action";
                $delete = html_link("$act=delete", 'delete');
                $detail = html_link("$act=detail", 'detail');
                $action = "$delete<br>$detail";

                if ($glob)
                    $gbl++;
                else
                    $lcl++;

                $owner = "$user($scop)$enab";
                $args  = array($name, $owner, $id, $filter, $sites, $action);
                color_data($args, $color, 0);
            }

            table_footer();
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    echo "<h2>$num reports found, $gbl global, $lcl local.</h2>";
}


function one_shot($db)
{
    $name = 'Stuck Report';
    $user = 'admin';
    $qn = safe_addslashes($name);
    $qu = safe_addslashes($user);
    $sql = "select * from AssetReports\n"
        . " where name = '$qn'\n"
        . " and username = '$qu'\n"
        . " and global = 0";
    $row = find_one($sql, $db);
    if ($row) {
        $rid  = $row['id'];
        $timo = server_int('report_timeout', 7200, $db);
        if ($timo > 300) {
            $now  = time();
            $tmin = $now - $timo + 60;
            $tmax = $tmin + $timo;
            $sql = "update AssetReports set\n"
                . " enabled  = 1,\n"
                . " next_run = -1,\n"
                . " this_run = $tmin,\n"
                . " last_run = $tmin,\n"
                //        . " retries  = retries+1\n"
                . " retries  = 4\n"
                . " where id = $rid";
            $res = redcommand($sql, $db);
        }
    }
}



function nothing($env)
{
    $msg = "This page requires administrative access.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
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
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);

$date = datestring(time());

echo "<h2>$date</h2>";

$ord    = get_integer('ord', 0);
$id     = get_integer('id', 0);
$priv   = get_integer('priv', 1);
$dbg    = get_integer('debug', 1);
$limit  = get_integer('limit', 50);
$act    = get_string('act', 'enab');

$user  = user_data($authuser, $db);
$debug = @($user['priv_debug']) ? $dbg  : 0;
$admin = @($user['priv_admin']) ? $priv : 0;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$env = array();
$env['id']    = $id;
$env['ord']   = $ord;
$env['now']   = $now;
$env['act']   = $act;
$env['limt']  = $limit;
$env['self']  = server_var('PHP_SELF');
$env['args']  = server_var('QUERY_STRING');
$env['days']  = $daynames;
$env['cycl']  = $cyclenames;
$env['admin'] = $admin;


if (!$admin) {
    $act = 'none';
}


echo again($env);
db_change($GLOBALS['PREFIX'] . 'asset', $db);
switch ($act) {
    case 'display':
        debug_report($env, $db);
        break;
    case 'enab':
        enab_report($env, $db);
        break;
    case 'disb':
        disb_report($env, $db);
        break;
    case 'last':
        last_report($env, $db);
        break;
    case 'delete':
        delete_report($id, $db);
        break;
    case 'detail':
        detail_report($id, $db);
        break;
    case 'filter':
        filter_report($ord, $limit, $db);
        break;
    case 'none':
        nothing($env, $db);
        break;
    case 'xxx':
        one_shot($db);
        break;
}
echo again($env);

echo head_standard_html_footer($authuser, $db);
