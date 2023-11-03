<?php

/*
Revision history:

Date        Who     What
----        ---     ----
12-Feb-03   EWB     Created.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   NL      Move $debug initialization and surrounding lines above ob_ lines
20-Jun-03   EWB     View provisional field, added sorting options.
24-Jun-03   EWB     Damage and/or delete
25-Jun-03   EWB     Repair.
26-Jun-03   EWB     Unpeel.
15-Oct-03   EWB     Raised memory limit for unpeel host.
31-Dec-03   EWB     Reports age of asset records (for reasonable purge)
20-Aug-04   EWB     Added Column Sorting.
24-Feb-05   EWB     Database Consistancy Check.
24-Jan-06   AAM     Bug 3072: change memory_limit setting to use max_php_mem_mb.
                    (Note that this change was actually moved from 4.2 to 4.3
                    on 06-Mar-06.)
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()                    

*/

$title = 'Asset Machine Census';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-user.php');
include('../lib/l-afix.php');
include('../lib/l-jump.php');
include('../lib/l-tabs.php');
include('../lib/l-head.php');


function showtime($when)
{
    $text = '<br>';
    if (0 < $when) {
        $text = date('m/d/y H:i:s', $when);
    }
    return $text;
}

function again()
{
    $self = server_var('PHP_SELF');
    $args = server_var('QUERY_STRING');
    $href = ($args) ? "$self?$args" : $self;
    $ord  = "$self?ord";
    $sane = "$self?action=sane";

    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link('debug.php', 'home');
    $a[] = html_link($sane, 'sane');
    $a[] = html_link($href, 'again');
    return jumplist($a);
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
        $txt = sprintf('%d %d:%02d:%02d', $dd, $hh, $mm, $ss);
    if (8 <= $dd) {
        $dd  = intval(round($secs / 86400));
        $txt = "$dd days";
    }

    return $txt;
}


function order($ord)
{
    switch ($ord) {
        case  0:
            return 'provisional desc, slatest desc, machineid';
        case  1:
            return 'provisional, slatest, machineid';
        case  2:
            return 'cust, host';
        case  3:
            return 'cust desc, host';
        case  4:
            return 'machineid';
        case  5:
            return 'machineid desc';
        case  6:
            return 'clatest desc, machineid';
        case  7:
            return 'clatest, machineid';
        case  8:
            return 'cearliest desc, machineid';
        case  9:
            return 'cearliest, machineid';
        case 10:
            return 'searliest desc, machineid';
        case 11:
            return 'searliest, machineid';
        case 12:
            return 'host, cust';
        case 13:
            return 'host desc, cust desc';
        case 14:
            return 'slatest desc, machineid';
        case 15:
            return 'slatest, machineid';
        default:
            return order(0);
    }
}


function display_census($db, $ord)
{
    $order = order($ord);
    $num = 0;
    $now = time();
    $sql = "select * from Machine\n"
        . " order by $order";
    $res = redcommand($sql, $db);
    if ($res) {
        $num = mysqli_num_rows($res);
    }
    if (($res) && ($num > 0)) {
        $self = server_var('PHP_SELF');
        $o    = "$self?ord";
        $prov = ($ord ==  0) ? "$o=1"  : "$o=0";
        $site = ($ord ==  2) ? "$o=3"  : "$o=2";
        $id   = ($ord ==  4) ? "$o=5"  : "$o=4";
        $cmax = ($ord ==  6) ? "$o=7"  : "$o=6";
        $cmin = ($ord ==  8) ? "$o=9"  : "$o=8";
        $smin = ($ord == 10) ? "$o=9"  : "$o=10";
        $host = ($ord == 12) ? "$o=13" : "$o=12";
        $smax = ($ord == 14) ? "$o=15" : "$o=14";

        $id   = html_link($id, 'Id');
        $age  = html_link($prov, 'Age');
        $host = html_link($host, 'Machine');
        $site = html_link($site, 'Site');
        $smax = html_link($smax, 'Server Max');
        $smin = html_link($smin, 'Server Min');
        $cmin = html_link($cmin, 'Client Min');
        $cmax = html_link($cmax, 'Client Max');
        $prov = html_link($prov, 'Provisional');
        $acts = 'Action';
        $head = array($age, $host, $site, $id, $smin, $smax, $cmin, $cmax, $prov, $acts);
        $cols = safe_count($head);
        $text = "Asset Census &nbsp; ($num found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($head, 1);
        while ($row = mysqli_fetch_array($res)) {
            $id    = $row['machineid'];
            $site  = $row['cust'];
            $host  = $row['host'];
            $prov  = $row['provisional'];
            $smax  = $row['slatest'];
            $smin  = $row['searliest'];
            $age   = age($now - $smax);
            $act   = "$self?mid=$id&action";

            $a     = array();
            $a[]   = html_link("$act=delete", '[delete]');
            if ($prov)
                $a[] = html_link("$act=repair", '[repair]');
            else
                $a[] = html_link("$act=damage", '[damage]');
            if ($smax > $smin) {
                $a[]  = html_link("$act=unpeel", '[unpeel]');
            }
            $act   = join(' ', $a);
            $smax  = showtime($row['slatest']);
            $smin  = showtime($row['searliest']);
            $cmax  = showtime($row['clatest']);
            $cmin  = showtime($row['cearliest']);
            $prov  = showtime($row['provisional']);
            $args  = array($age, $host, $site, $id, $smin, $smax, $cmin, $cmax, $prov, $act);
            echo table_data($args, 0);
        }
        echo table_footer();
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
}


/*
    |  Create a realistic-looking damaged upload.
    |
    */

function damage_host($db, $mid)
{
    debug_note("damage_host $mid");
    $row  = array();
    $list = array();
    if ($mid > 0) {
        $sql  = "select * from Machine\n";
        $sql .= " where machineid = $mid";
        $row  = find_one($sql, $db);
    }
    if ($row) {
        $host = $row['host'];
        $site = $row['cust'];
        $prov = $row['provisional'];
        debug_note("host:$host, site:$site");

        $rcmax = $row['clatest'];
        $rcmin = $row['cearliest'];
        $rsmax = $row['slatest'];
        $rsmin = $row['searliest'];
        $lsmax = 0;
        $lcmax = 0;

        if ($rsmin < $rsmax) {
            $sql  = "select * from AssetData\n";
            $sql .= " where machineid = $mid";
            $list = find_many($sql, $db);

            /*
                |  Find the timestamps for the most
                |  recent prior update.
                */

            if ($list) {
                foreach ($list as $key => $data) {
                    $smax = $data['slatest'];
                    $cmax = $data['clatest'];
                    $sobs = $data['sobserved'];
                    $cobs = $data['cobserved'];
                    if (($lcmax < $cmax) && ($cmax < $rcmax)) $lcmax = $cmax;
                    if (($lcmax < $cobs) && ($cobs < $rcmax)) $lcmax = $cobs;
                    if (($lsmax < $smax) && ($smax < $rsmax)) $lsmax = $smax;
                    if (($lsmax < $sobs) && ($sobs < $rsmax)) $lsmax = $sobs;
                }
            }
        }

        if ($prov > $rsmax) {
            $when = date('m/d H:i', $prov);
            echo "Machine $host damaged already, provisional update $when<br>\n";
        } else {
            if ((0 < $lcmax) && ($lcmax < $rcmax) &&
                (0 < $lsmax) && ($lsmax < $rsmax)
            ) {
                $when = date('m/d H:i:s', $lsmax);
                echo "host $host, using prior update $when<br>\n";
                $sql  = "update Machine set\n";
                $sql .= " provisional = $rsmax,\n";
                $sql .= " slatest = $lsmax,\n";
                $sql .= " clatest = $lcmax\n";
                $sql .= " where machineid = $mid";
            } else {
                echo "host $host, no prior update<br>\n";
                $sql  = "update Machine set\n";
                $sql .= " provisional = $rsmax,\n";
                $sql .= " slatest = 0,\n";
                $sql .= " clatest = 0\n";
                $sql .= " where machineid = $mid";
            }
            redcommand($sql, $db);
        }
    }
}


function delete_host($db, $mid)
{
    if ($mid > 0) {
        $del  = 0;
        $sql  = "select * from Machine\n";
        $sql .= " where machineid = $mid";
        $row  = find_one($sql, $db);

        $sql  = "delete from AssetData\n";
        $sql .= " where machineid = $mid";
        $res  = redcommand($sql, $db);

        if ($res) {
            $del  = mysqli_affected_rows($db);
            $sql  = "delete from Machine\n";
            $sql .= " where machineid = $mid";
            $res  = redcommand($sql, $db);
        }
        if (($res) && ($row)) {
            $host = $row['host'];
            $site = $row['cust'];
            logs::log(__FILE__, __LINE__, "assets: $host (d:$del) removed from $site.", 0);
            echo "$host removed, $del records.<br>\n";
        }
    }
}


/*
    |  Trys to undo the most recent update.
    |
    |    1. Calculate the first and last log times directly
    |       from the AssetData itself.
    |    2. Find the previous log time, also directly from
    |       the AssetData.
    |    3. Remove all the final log new entries.
    |    4. Revert all the final log end times to previous end time.
    |    5. Update the Machine table to match reality.
    */

function unpeel_host($db, $mid)
{
    debug_note("unpeel_host $mid");
    if (function_exists('ini_set')) {
        $mem = server_def('max_php_mem_mb', '256', $db);
        ini_set('memory_limit', $mem . 'M');
    }
    $row  = array();
    $list = array();
    if ($mid > 0) {
        $sql  = "select * from Machine\n";
        $sql .= " where machineid = $mid";
        $row  = find_one($sql, $db);
        $sql  = "select clatest, slatest, cobserved, sobserved\n";
        $sql .= " from AssetData\n";
        $sql .= " where machineid = $mid";
        $list = find_many($sql, $db);
    }
    $lcmax = 0;
    $lsmax = 0;
    $rsmin = 0;
    $rsmax = 0;
    $rcmin = 0;
    $rcmax = 0;
    if (($row) && ($list)) {
        $host = $row['host'];
        $site = $row['cust'];

        debug_note("host:$host, site:$site");

        reset($list);
        foreach ($list as $key => $data) {
            $smax = $data['slatest'];
            $cmax = $data['clatest'];
            $sobs = $data['sobserved'];
            $cobs = $data['cobserved'];
            if ($rcmax < $cmax) $rcmax = $cmax;
            if ($rsmax < $smax) $rsmax = $smax;
            if (($rsmin == 0) || ($sobs < $rsmin)) $rsmin = $sobs;
            if (($rcmin == 0) || ($cobs < $rcmin)) $rcmin = $cobs;
        }

        reset($list);
        foreach ($list as $key => $data) {
            $smax = $data['slatest'];
            $cmax = $data['clatest'];
            $sobs = $data['sobserved'];
            $cobs = $data['cobserved'];
            if (($lcmax < $cmax) && ($cmax < $rcmax)) $lcmax = $cmax;
            if (($lcmax < $cobs) && ($cobs < $rcmax)) $lcmax = $cobs;
            if (($lsmax < $smax) && ($smax < $rsmax)) $lsmax = $smax;
            if (($lsmax < $sobs) && ($sobs < $rsmax)) $lsmax = $sobs;
        }
    }

    $drsmax = date('m/d/y H:i:s', $rsmax);
    $drcmax = date('m/d/y H:i:s', $rcmax);
    $dlsmax = date('m/d/y H:i:s', $lsmax);
    $dlcmax = date('m/d/y H:i:s', $lcmax);

    debug_note("rsmax: $rsmax $drsmax");
    debug_note("rcmax: $rcmax $drcmax");
    debug_note("lsmax: $lsmax $dlsmax");
    debug_note("lcmax: $lcmax $dlcmax");


    if ((0 < $rsmin) && ($rsmin <= $lsmax) && ($lsmax < $rsmax) && ($mid > 0)) {
        echo "smax: $drsmax --> $dlsmax<br>\n";
        echo "cmax: $drcmax --> $dlcmax<br>\n";
        $del = 0;
        $upd = 0;

        $sql  = "delete from AssetData\n";
        $sql .= " where machineid = $mid and\n";
        $sql .= " sobserved = $rsmax";
        $res  = redcommand($sql, $db);

        if ($res) {
            $del  = mysqli_affected_rows($db);
            $sql  = "update AssetData set\n";
            $sql .= " slatest = $lsmax,\n";
            $sql .= " clatest = $lcmax\n";
            $sql .= " where slatest = $rsmax and\n";
            $sql .= " machineid = $mid";
            $res  = redcommand($sql, $db);
        }

        if ($res) {
            $upd  = mysqli_affected_rows($db);
            $sql  = "update Machine set\n";
            $sql .= " searliest = $rsmin,\n";
            $sql .= " cearliest = $rcmin,\n";
            $sql .= " slatest = $lsmax,\n";
            $sql .= " clatest = $lcmax,\n";
            $sql .= " provisional = 0\n";
            $sql .= " where machineid = $mid";
            $res  = redcommand($sql, $db);
        }
        if ($res) {
            logs::log(__FILE__, __LINE__, "assets: $host unpeel (u:$upd,d:$del) $drsmax for $site.", 0);
        }

        echo "$del asset records removed.<br>\n";
        echo "$upd asset records modified.<br>\n";
    }

    if ((0 < $rsmin) && ($rsmin == $rsmax) && ($mid > 0)) {
        delete_host($db, $mid);
    }
}

function repair_host($db, $mid)
{
    if ($mid > 0) {
        $sql  = "select * from Machine\n";
        $sql .= " where machineid = $mid";
        $row  = find_one($sql, $db);
        if ($row) {
            $mid  = $row['machineid'];
            $smax = $row['slatest'];
            $cmax = $row['clatest'];
            $prov = $row['provisional'];
            $host = $row['host'];
            $site = $row['cust'];
            $when = date('m/d/y H:i:s', $prov);
            logs::log(__FILE__, __LINE__, "assets: $host repair $when for $site.", 0);
            fix_machine($mid, $smax, $cmax, $prov, $db);
        }
    }
}

function unknown_option($action, $db)
{
    debug_note("unknown option $action");
}


function sane_asset($db)
{
    $sql = "select A.machineid from\n"
        . " " . $GLOBALS['PREFIX'] . "asset.AssetData as A\n"
        . " left join " . $GLOBALS['PREFIX'] . "asset.Machine as M\n"
        . " on A.machineid = M.machineid\n"
        . " where M.machineid is NULL\n"
        . " group by A.machineid\n"
        . " order by A.machineid";
    $set = find_many($sql, $db);
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $mid = $row['machineid'];
            $sql = "delete from " . $GLOBALS['PREFIX'] . "asset.AssetData\n"
                . " where machineid = $mid";
            $res = redcommand($sql, $db);
            $num = affected($res, $db);
            debug_note("Machine $mid, Delete $num");
        }
    } else {
        debug_note('asset.AssetData.machineid: OK');
    }
}


/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$user  = user_data($authuser, $db);
$debug = @($user['priv_debug']) ? 1 : 0;
$admin = @($user['priv_admin']) ? 1 : 0;

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$date = datestring(time());
$ord  = get_integer('ord', 0);
$mid  = get_integer('mid', 0);
$act  = get_string('action', 'display');

echo "<h2>$date</h2>";

if ($admin) {
    db_change($GLOBALS['PREFIX'] . 'asset', $db);
    echo again();
    switch ($act) {
        case 'display':
            display_census($db, $ord);
            break;
        case 'damage':
            damage_host($db, $mid);
            break;
        case 'delete':
            delete_host($db, $mid);
            break;
        case 'repair':
            repair_host($db, $mid);
            break;
        case 'unpeel':
            unpeel_host($db, $mid);
            break;
        case 'sane':
            sane_asset($db);
            break;
        default:
            unknown_option($action, $db);
            break;
    }
    echo again();
} else {
    $msg = "This page requires administrative access.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
}

echo head_standard_html_footer($authuser, $db);
