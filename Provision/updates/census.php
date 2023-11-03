<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 5-Sep-03   EWB     Created.
 4-Nov-03   EWB     Time of zero doesn't count.
 1-Apr-04   EWB     Reports elapsed time since last contact.
21-Apr-04   EWB     Sort by column headers.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title = 'Updates Machine Census';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-jump.php');
include('../lib/l-user.php');
include('header.php');
include('../lib/l-head.php');


function disp($row, $name)
{
    $text = $row[$name];
    return ($text == '') ? '<br>' : $text;
}


function again($debug)
{
    $self = server_var('PHP_SELF');
    $act  = "$self?ord";
    $a    = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link('sites.php', 'sites');
    $a[] = html_link('dnload.php', 'downloads');
    if ($debug) {
        $args = server_var('QUERY_STRING');
        $href = ($args) ? "$self?$args" : $self;
        $home = '../acct/index.php';
        $a[] = html_link($home, 'home');
        $a[] = html_link($href, 'again');
    }
    return jumplist($a);
}


function table_data($args, $head)
{
    $td = ($head) ? 'th' : 'td';
    if (safe_count($args)) {
        echo "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo "<$td>$data</$td>\n";
        }
        echo "</tr>\n";
    }
}


function table_header()
{
    echo "<br>\n<table border='2' align='left' cellspacing='2' cellpadding='2'>\n";
}


function table_footer()
{
    echo "</table>\n<br clear='all'>\n<br>\n";
}


function short_date($x)
{
    $text = '<br>';
    if ($x > 0) {
        //      $date = date('m/d/y',$x);
        //      $time = date('H:i:s',$x);
        //      $text = "$date<br>$time";
        $text = date('m/d/y H:i:s', $x);
    }
    return $text;
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
            return 'timecontact desc, id';
        case  1:
            return 'timecontact, id';
        case  2:
            return 'machine, sitename, id';
        case  3:
            return 'machine desc, sitename desc, id';
        case  4:
            return 'sitename, machine, id';
        case  5:
            return 'sitename desc, machine desc, id';
        case  6:
            return 'lastversion, sitename, machine desc, id';
        case  7:
            return 'lastversion desc, sitename, machine, id';
        case  8:
            return 'id';
        case  9:
            return 'id desc';
        case 10:
            return 'oldversion, timecontact desc, id';
        case 11:
            return 'oldversion desc, timecontact, id';
        case 12:
            return 'newversion, timecontact desc, id';
        case 13:
            return 'newversion desc, timecontact, id';
        case 14:
            return 'doforce, timecontact desc, id';
        case 15:
            return 'doforce desc, timecontact, id';
        case 16:
            return 'wasforced, timecontact desc, id';
        case 17:
            return 'wasforced desc, timecontact, id';
        case 18:
            return 'timeupdate desc, timecontact, id';
        case 19:
            return 'timeupdate, timecontact desc, id';
        default:
            return order(0);
    }
}


function display_census($db, $ord)
{
    $order = order($ord);
    $num = 0;
    $self = server_var('PHP_SELF');
    $sql = "select * from UpdateMachines\n order by $order";
    $res = redcommand($sql, $db);
    if ($res) {
        $num  = mysqli_num_rows($res);
        if ($num > 0) {
            $o = "$self?ord";
            $aref = ($ord ==  0) ? "$o=1"  : "$o=0";   // age      0/1
            $mref = ($ord ==  2) ? "$o=3"  : "$o=2";   // machine  2/3
            $sref = ($ord ==  4) ? "$o=5"  : "$o=4";   // site     4/5
            $vref = ($ord ==  6) ? "$o=7"  : "$o=6";   // version  6/7
            $iref = ($ord ==  8) ? "$o=9"  : "$o=8";   // id       8/9
            $oref = ($ord == 10) ? "$o=11" : "$o=10";  // old      10/11
            $nref = ($ord == 12) ? "$o=13" : "$o=12";  // new      12/13
            $fref = ($ord == 14) ? "$o=15" : "$o=14";  // force    14/15
            $wref = ($ord == 16) ? "$o=17" : "$o=16";  // wf       16/17
            $uref = ($ord == 18) ? "$o=19" : "$o=18";  // update   18/19
            $now  = time();

            $age  = html_link($aref, 'Age');
            $host = html_link($mref, 'Machine');
            $site = html_link($sref, 'Site');
            $id   = html_link($iref, 'Id');
            $vers = html_link($vref, 'Version');
            $when = html_link($aref, 'When');
            $updt = html_link($uref, 'Update');
            $old  = html_link($oref, 'Old');
            $new  = html_link($nref, 'New');
            $frs  = html_link($fref, 'Force');
            $was  = html_link($wref, 'Was');

            $args = array($age, $host, $site, $when, $vers, $id, $updt, $old, $new, $frs, $was);

            table_header();
            table_data($args, 1);
            while ($row = mysqli_fetch_array($res)) {
                $site  = disp($row, 'sitename');
                $host  = disp($row, 'machine');
                $vers  = disp($row, 'lastversion');
                $old   = disp($row, 'oldversion');
                $new   = disp($row, 'newversion');
                $id    = $row['id'];
                $last  = $row['timecontact'];
                $updt  = $row['timeupdate'];
                $wf    = $row['wasforced'];
                $force = $row['doforce'];
                $age   = age($now - $last);
                $when  = short_date($last);
                $then  = short_date($updt);
                $args  = array($age, $host, $site, $when, $vers, $id, $then, $old, $new, $force, $wf);
                table_data($args, 0);
            }
            table_footer();
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    echo "<h2>$num machines found</h2>";
}


/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$ord = get_integer('ord', 0);
$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, $local_nav, '', 0, $db);


$date = datestring(time());

echo "<h2>$date</h2>";

$user  = user_data($authuser, $db);
$debug = @($user['priv_debug']) ? 1 : 0;
$admin = @($user['priv_admin']) ? 1 : 0;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($admin) {
    db_change($GLOBALS['PREFIX'] . 'swupdate', $db);
    echo again($debug);
    display_census($db, $ord);
    echo again($debug);
    db_change($GLOBALS['PREFIX'] . 'core', $db);
} else {
    $msg = "This page requires administrative access.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
}

echo head_standard_html_footer($authuser, $db);
