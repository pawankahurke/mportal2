<?php

/*
Revision history:

Date        Who     What
----        ---     ----
29-Oct-03   EWB     Created.
30-Jan-04   EWB     Sorting options
 2-Feb-04   EWB     Paging options
11-Feb-04   EWB     Control Box.
12-Feb-04   EWB     Audit Product Owner.
12-Feb-04   EWB     Better paging control.
16-Feb-04   EWB     Page logic, audit_page variable.
18-Feb-04   EWB     Range checking.
 8-Mar-04   EWB     Changed tag to 'current settings'
 9-Mar-04   EWB     Server records have site name of ''
25-Mar-04   EWB     Documentation for date formats.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title  = 'Audit Trail';

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
include('../lib/l-date.php');
include('../lib/l-cmth.php');
include('../lib/l-slav.php');
include('../lib/l-tabs.php');
include('../lib/l-head.php');
include('local.php');

function simple_time($u)
{
    $date = date('m/d/Y', $u);
    $time = date('H:i:s', $u);
    $text = ($time == '00:00:00') ? $date : "$date $time";
    return $text;
}


function again($env)
{
    $priv = $env['priv'];
    $actn = $env['action'];
    $self = server_var('PHP_SELF');
    $args = server_var('QUERY_STRING');
    $href = ($args) ? "$self?$args" : $self;
    $act  = "$self?action";

    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    if ($actn == 'list') {
        $a[] = html_link('#current', 'current settings');
        $a[] = html_link('#list', 'list');
        $a[] = html_link('#control', 'control');
    }
    if ($priv) {
        if ($actn == 'debug')
            $a[] = html_link("$act=list", 'back');
        else
            $a[] = html_link("$act=debug", 'debug');
        $a[] = html_link($href, 'again');
        $a[] = html_link('../acct/index.php', 'home');
    }
    return jumplist($a);
}


function nothing_found(&$env)
{
    $now  = $env['now'];
    $umin = $env['umin'];
    $umax = $env['umax'];
    $filt = $env['user']['filtersites'];
    $secs = $umax - $umin;
    $days = intval(round($secs / 86400));
    $dmin = date('m/d', $umin);
    $dmax = date('m/d', $umax);
    $time = ($days == 1) ? "1 day" : "$days days";

    $txt = "<br>\n"
        . "There were no audit records found for the specified time<br>\n"
        . "period of $dmin to $dmax, an elapsed period of $time.<br>\n"
        . "We recommend setting an earlier start date.<br>\n";
    if ($umax < $now) {
        $txt .= "Or perhaps you could try a later end date.<br>\n";
    }
    if ($filt) {
        $txt .= "<br>\n"
            .  "Also, site filtering is currently enabled,<br>\n"
            .  "you might consider turning it off.<br>\n"
            .  "<br>\n";
    }
    $txt .= "<br>\n";
    return $txt;
}


function wide_header()
{
    echo "<br>\n<table border='2' align='left' cellspacing='2' cellpadding='2' width='100%'>\n";
}

function order($ord)
{
    $order = 'servertime desc, auditid';
    switch ($ord) {
        case  0:;
            break;
        case  1:
            $order = 'servertime, auditid';
            break;
        case  2:
            $order = 'product, servertime desc, auditid';
            break;
        case  3:
            $order = 'product desc, servertime desc, auditid';
            break;
        case  4:
            $order = 'machine, sitename, clienttime desc, auditid';
            break;
        case  5:
            $order = 'machine desc, sitename, clienttime desc, auditid';
            break;
        case  6:
            $order = 'action, servertime desc, auditid';
            break;
        case  7:
            $order = 'action desc, servertime desc, auditid';
            break;
        case  8:
            $order = 'sitename, servertime desc, auditid';
            break;
        case  9:
            $order = 'sitename desc, servertime desc, auditid';
            break;
        case 10:
            $order = 'username, servertime desc, auditid';
            break;
        case 11:
            $order = 'username desc, servertime desc, auditid';
            break;
        case 12:
            $order = 'who, servertime desc, auditid';
            break;
        case 13:
            $order = 'who desc, servertime desc, auditid';
            break;
        case 14:
            $order = 'clienttime desc, auditid';
            break;
        case 15:
            $order = 'clienttime, auditid';
            break;
        case 16:
            $order = 'auditid';
            break;
        case 17:
            $order = 'auditid desc';
            break;
        case 18:
            $order = 'uuid, clienttime desc, auditid';
            break;
        case 19:
            $order = 'uuid desc, clienttime desc, auditid';
            break;
        default:
            break;
    }
    return $order;
}

function ords()
{
    $asc   = 'ascending';
    $dsc   = 'descending';
    $a     = array();
    $a[0] = "Server Time (latest first)";
    $a[1] = "Server Time (oldest first)";
    $a[2] = "Product ($asc) / Server Time";
    $a[3] = "Product ($dsc) / Server Time";
    $a[4] = "Machine ($asc) / Site / Client Time";
    $a[5] = "Machine ($dsc) / Site / Client Time";
    $a[6] = "Action ($asc) / Server Time";
    $a[7] = "Action ($dsc) / Server Time";
    $a[8] = "Site ($asc) / Server Time";
    $a[9] = "Site ($dsc) / Server Time";
    $a[10] = "User ($asc) / Server Time";
    $a[11] = "User ($dsc) / Server Time";
    $a[12] = "Location ($asc) / Server Time";
    $a[13] = "Location ($dsc) / Server Time";
    $a[14] = "Client Time (latest first)";
    $a[15] = "Client Time (oldest first)";
    $a[16] = "Id ($asc)";
    $a[17] = "Id ($dsc)";
    $a[18] = "UUID ($asc) / Client Time";
    $a[19] = "UUID ($dsc) / Client Time";
    return $a;
}


/*
    |  Calculate the mapping of pages and events.
    |  For example, if we have 223 events, and a page size
    |  of 50, then we will want five pages.  The pages
    |  are internally numbered 0..4, but we call them
    |  1..5 when we show them to the user.
    |
    |     0: page 1 (1..50)
    |     1: page 2 (51..100)
    |     2: page 3 (101..150)
    |     3: page 4 (151..200)
    |     4: page 5 (201..223)
    |
    |  The pages are internally numbered as 0..4, but
    |  we will show the user 1..5 instead.
    */

function page_logic($page, $limit, $total)
{
    $prev = $limit - 1;
    $last = intval(($total + $prev) / $limit);
    $fake = $page + 1;
    $pmin = 0;
    $pmax = 0;
    if ($fake > $last) $fake = $last;
    if ($total) {
        $pmin = ($page * $limit) + 1;
        $pmax = ($fake * $limit);
        if ($pmax > $total) $pmax = $total;
    }
    return "page $fake of $last ($pmin..$pmax)";
}

function span($cols, $text)
{
    return "<tr><td colspan=\"$cols\">$text</td></tr>\n";
}



function audit_control($env, $total, $db)
{
    $ord  = $env['ord'];
    $page = $env['page'];
    $umin = $env['umin'];
    $umax = $env['umax'];
    $limt = $env['limt'];
    $midn = $env['midn'];
    $priv = $env['priv'];

    echo post_self();
    echo hidden('action', 'list');

    $tmin = array();
    $tmax = array();

    $days = array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 21, 28, 60, 90, 120, 150, 180, 360);
    $lims = array(5, 10, 20, 25, 50, 100, 250, 500, 1000);

    $next = days_ago($midn, -1);

    $tmax[$next] = 'Midnight Tonight';
    $tmax[$midn] = 'Midnight Last Night';
    $tmin[$midn] = $tmax[$midn];
    $time = $midn;

    reset($days);
    foreach ($days as $key => $day) {
        $time = days_ago($next, $day);
        $text = date('m/d/Y', $time) . " ($day days ago)";
        $tmin[$time] = $text;
        $tmax[$time] = $text;
    }

    if ($umin) {
        if (!isset($tmin[$umin])) {
            $tmin[$umin] = simple_time($umin);
            ksort($tmin, SORT_NUMERIC);
        }
    }

    if ($umin) {
        if (!isset($tmax[$umax])) {
            $tmax[$umax] = simple_time($umax);
            ksort($tmax, SORT_NUMERIC);
        }
    }

    $gmin = ($umin) ? $umin : $env['last'];
    $gmax = ($umax) ? $umax : $env['next'];

    $pags = array();
    $i    = 0;
    $pmax = 0;
    while ($pmax < $total) {
        $pmax += $limt;
        $pags[$i] = page_logic($i, $limt, $total);
        $i++;
    }
    if (!in_array($limt, $lims)) {
        $lims[] = $limt;
        sort($lims, SORT_NUMERIC);
    }

    $ords = ords();
    $sel_dmin = textbox('dmin', 20, '');
    $sel_dmax = textbox('dmax', 20, '');
    $sel_umin = html_select('n', $tmin, $gmin, 1);
    $sel_umax = html_select('x', $tmax, $gmax, 1);
    $sel_limt = html_select('l', $lims, $limt, 0);
    $sel_ord  = html_select('o', $ords, $ord, 1);

    $submit = button('Go');

    echo table_header();
    echo pretty_header('Audit Control', 2);
    echo two_col('Select start date:',  $sel_umin);
    echo two_col('Enter start date:',   $sel_dmin);
    echo two_col('Select end date:',    $sel_umax);
    echo two_col('Enter end date:',     $sel_dmax);
    echo two_col('Sort by:',            $sel_ord);
    echo two_col('Page size:',          $sel_limt);

    if ($total > $limt) {
        $sel_page = html_select('p', $pags, $page, 1);
        echo two_col('Find page:', $sel_page);
    }
    echo two_col('Submit', $submit);
    if ($priv) {
        $debug = $env['debug'];
        $yesno = array('No', 'Yes');
        $dbg   = html_select('debug', $yesno, $debug, 1);
        $text  = green('$debug');
        echo two_col($text, $dbg);
    }
    echo table_footer();
    $txt = date_doc();
    $txt = fontspeak($txt);
    echo "<p>$txt</p>\n";
    echo form_footer();
}


function audit_current($env, $total, $db)
{
    $ord  = $env['ord'];
    $page = $env['page'];
    $umin = $env['umin'];
    $umax = $env['umax'];
    $limt = $env['limt'];
    $ords = ords();
    $sort = $ords[$ord];

    $dmin = datestring($umin);
    $dmax = datestring($umax);
    $secs = $umax - $umin;
    $days = intval(round($secs / 86400));
    $hour = intval(round($secs / 3600));
    $time = (48 <= $hour) ? "$days days" : "$hour hours";

    echo table_header();
    echo pretty_header('Current Settings', 2);
    echo two_col('Start Date:',     $dmin);
    echo two_col('End Date:',       $dmax);
    echo two_col('Elapsed Time:',   $time);
    echo two_col('Sort By:',        $sort);
    echo two_col('Page Size:',      $limt);
    if ($total > $limt) {
        $text = page_logic($page, $limt, $total);
        echo two_col('Page:', $text);
    }
    if ($total > 0) {
        echo two_col('Total Found:', $total);
    }
    echo table_footer();
}


function debug_audit($env, $db)
{
    debug_note("debug_audit");
    $self = $env['self'];
    $priv = $env['priv'];
    $limt = $env['limt'];
    $list = array();
    if ($priv) {
        $order = order($env['ord']);
        $sql   = "select * from Audit\n"
            . " order by $order\n"
            . " limit $limt";
        $list  = find_many($sql, $db);
    }

    if ($list) {
        //         $jump = debug_jump($env);

        $jump = again($env);
        echo $jump;

        $count = safe_count($list);

        debug_note("$count records found");

        wide_header();
        $head = explode('|', 'name|owner|id|who|when|site|host|uuid|user|action');
        echo table_data($head, 1);

        reset($list);
        foreach ($list as $key => $row) {
            $id   = $row['auditid'];
            $who  = $row['who'];
            $stim = $row['servertime'];
            $ctim = $row['clienttime'];

            $ownr = disp($row, 'owner');
            $host = disp($row, 'machine');
            $site = disp($row, 'sitename');
            $name = disp($row, 'product');
            $uuid = disp($row, 'uuid');
            $user = disp($row, 'username');
            $act  = disp($row, 'action');

            $ct   = fulldate($ctim);
            $st   = fulldate($stim);

            $args = array($name, $ownr, $id, $who, $st, $site, $host, $uuid, $user, $act);
            echo table_data($args, 0);
        }
        echo table_footer();

        echo $jump;
    } else {
        echo again($env);
        echo "There are no audits.";
        echo again($env);
    }
}


function prevnext(&$env, $total)
{
    $self = $env['self'];
    $page = $env['page'];
    $limt = $env['limt'];
    $umin = $env['umin'];
    $umax = $env['umax'];
    $ord  = $env['ord'];

    $img  = '<img border="0"';
    $pwid = 'width="68" height="22">';
    $nwid = 'width="47" height="22">';
    $next = $page + 1;
    $prev = $page - 1;
    $pmin = ($page > 0) ? $limt * $page : 0;
    $pmax = $pmin + $limt;

    if ($page <= 0) {
        $psrc = ' src="../pub/previous-gray.gif" ';
        $pimg = $img . $psrc . $pwid;
        $ptxt = $pimg;
    } else {
        $psrc = ' src="../pub/previous.gif" ';
        $pimg = $img . $psrc . $pwid;
        $a    = array("$self?p=$prev");
        $a[]  = "o=$ord";
        $a[]  = "l=$limt";
        $a[]  = "n=$umin";
        $a[]  = "x=$umax";
        $pref = join('&', $a);
        $ptxt = html_link($pref, $pimg);
    }

    //      debug_note("prevnext page:$page, total:$total, limit:$limt next:$next, prev:$prev");

    if ($pmax < $total) {
        $nsrc = ' src="../pub/next.gif" ';
        $nimg = $img . $nsrc . $nwid;
        $a    = array("$self?p=$next");
        $a[]  = "o=$ord";
        $a[]  = "l=$limt";
        $a[]  = "n=$umin";
        $a[]  = "x=$umax";
        $nref = join('&', $a);
        $ntxt = html_link($nref, $nimg);
    } else {
        $nsrc = ' src="../pub/next-gray.gif" ';
        $nimg = $img . $nsrc . $nwid;
        $ntxt = $nimg;
    }

    echo <<< HERE

<br clear="all">
<table width="100%">
<tr>
  <td align="left" valign="top">
    $ptxt
  </td>

  <td>
    <br>
  </td>

  <td align="right" valign="top">
    $ntxt
  </td>
</tr>
</table>

HERE;
}


function find_audit_count(&$env, $db)
{
    $val  = 0;
    $carr = $env['carr'];
    $umin = $env['umin'];
    $umax = $env['umax'];
    if ($carr) {
        $access = db_access($carr);
        $sql = "select count(*) from Audit\n"
            . " where sitename in ('',$access)\n"
            . " and servertime between $umin and $umax";
    } else {
        $sql = "select count(*) from Audit\n"
            . " where sitename = ''\n"
            . " and servertime between $umin and $umax";
    }
    $res = redcommand($sql, $db);
    if ($res) {
        $val = mysqli_result($res, 0);
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $val;
}


function find_audit_list(&$env, $total, $db)
{
    $carr = $env['carr'];
    $ord   = $env['ord'];
    $page  = $env['page'];
    $limt  = $env['limt'];
    $umin  = $env['umin'];
    $umax  = $env['umax'];
    $order = order($ord);
    $pmin = ($page > 0) ? $limt * $page : 0;
    if (($total <= $limt) || ($total <= $pmin)) {
        $pmin = 0;
        $page = 0;
    }
    if ($carr) {
        $access = db_access($carr);

        $sql  = "select * from Audit\n"
            . " where sitename in ('',$access)\n"
            . " and servertime between $umin and $umax\n"
            . " order by $order\n"
            . " limit $pmin, $limt";
    } else {
        $sql  = "select * from Audit\n"
            . " where sitename = ''\n"
            . " and servertime between $umin and $umax\n"
            . " order by $order\n"
            . " limit $pmin, $limt";
    }
    return find_many($sql, $db);
}

function list_audit(&$env, $db)
{
    echo mark('current');

    echo again($env);

    $total = find_audit_count($env, $db);

    audit_current($env, $total, $db);

    $list = find_audit_list($env, $total, $db);
    if ($list) {
        $count = safe_count($list);

        $self = $env['self'];
        $limt = $env['limt'];
        $umin = $env['umin'];
        $umax = $env['umax'];
        $ord  = $env['ord'];

        // all of these reset to page zero.

        $o    = "$self?l=$limt&n=$umin&x=$umax&o";
        $tref = ($ord ==  0) ? "$o=1"  : "$o=0";     // servertime 0, 1
        $nref = ($ord ==  2) ? "$o=3"  : "$o=2";     // product 2, 3
        $href = ($ord ==  4) ? "$o=5"  : "$o=4";     // machine 4, 5
        $aref = ($ord ==  6) ? "$o=7"  : "$o=6";     // action 6, 7
        $sref = ($ord ==  8) ? "$o=9"  : "$o=8";     // sitename 8, 9
        $uref = ($ord == 10) ? "$o=11" : "$o=10";    // username 10, 11
        $wref = ($ord == 12) ? "$o=13" : "$o=12";    // who  12, 13
        $cref = ($ord == 14) ? "$o=15" : "$o=14";    // clienttime 14, 15

        $who  = html_link($wref, 'Location');
        $name = html_link($nref, 'Product');
        $time = html_link($tref, 'Server time');
        $ctim = html_link($cref, 'Client time');
        $site = html_link($sref, 'Site name');
        $host = html_link($href, 'Machine name');
        $user = html_link($uref, 'User name');
        $act  = html_link($aref, 'Action');

        echo mark('list');
        echo again($env);

        prevnext($env, $total);

        wide_header();

        $head  = array($who, $name, $time, $ctim, $site, $host, $user, $act);
        echo table_data($head, 1);

        reset($list);
        foreach ($list as $key => $row) {
            $id   = $row['auditid'];
            $who  = $row['who'];
            $stim = $row['servertime'];
            $ctim = $row['clienttime'];

            $host = disp($row, 'machine');
            $site = disp($row, 'sitename');
            $name = disp($row, 'product');
            $user = disp($row, 'username');
            $act  = disp($row, 'action');

            $ct   = fulldate($ctim);
            $st   = fulldate($stim);
            $what = ($who) ? 'Server' : 'Client';
            $args = array($what, $name, $st, $ct, $site, $host, $user, $act);
            echo table_data($args, 0);
        }
        echo table_footer();

        prevnext($env, $total);
    } else {
        echo nothing_found($env);
    }
    echo again($env);
    echo mark('control');
    audit_control($env, $total, $db);
    echo again($env);
}

function unknown_action($env, $db)
{
    debug_note("unknown action");
}

function single_shot($env, $db)
{
    /* -------------------------
        $sql = "update Audit set\n"
             . " owner = 'admin'\n"
             . " where owner = ''";
        redcommand($sql,$db);
        ------------------------- */
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
$nav = provis_navigate();
echo standard_html_header($title, $comp, $authuser, $nav, 0, 0, $db);

$date = datestring(time());

$action = get_string('action', 'list');
$dbg    = get_integer('debug', 0);
$user   = user_data($authuser, $db);
$priv   = @($user['priv_debug']) ?     1 : 0;
$flt    = @($user['filtersites']) ?    1 : 0;
$debug  = @($user['priv_debug']) ?  $dbg : 0;
$carr   = site_array($authuser, $flt, $db);

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($debug) echo "<h2>$date</h2>";

debug_array($debug, $_POST);

/*
    |  calculate the time range for this report.
    |  if the user enters something in the text box, try to use that.
    |  if that doesn't work, fall back to the pulldown values.
    |
    |  If those don't exist, fall back on just today.
    |
    |  If happens to be just after midnight or early in the
    |  morning, we'll include yesterday as well.
    */

$midn = midnight($now);
$last = $midn;
$next = days_ago($last, -1);
$secs = ($last <= $now) ? $now - $last : 0;
if (round($secs / 3600) <= 6) {
    $last = yesterday($midn);
}

$dmin = get_string('dmin', '');
$dmax = get_string('dmax', '');
$tmin = ($dmin == '') ? 0 : parsedate($dmin, $now);
$tmax = ($dmax == '') ? 0 : parsedate($dmax, $now);
$umin = ($tmin) ? $tmin : get_integer('n', 0);
$umax = ($tmax) ? $tmax : get_integer('x', 0);

if (!$umin) $umin = $last;
if (!$umax) $umax = $next;

/*
    |  if the user gets them backwards, it's probably
    |  easier to just fix it here.
    */

if ($umin > $umax) {
    $temp = $umin;
    $umin = $umax;
    $umax = $temp;
}

$adef = server_int('audit_page', 50, $db);
$adef = value_range(5, 1000, $adef);
$limt = get_integer('l', $adef);
$ord  = get_integer('o', 0);
$env  = array();
$env['db']     = $db;
$env['now']    = $now;
$env['ord']    = value_range(0, 19, $ord);
$env['page']   = get_integer('p', 0);
$env['limt']   = value_range(5, 1000, $limt);
$env['umin']   = $umin;
$env['umax']   = $umax;
$env['midn']   = $midn;
$env['last']   = $last;
$env['next']   = $next;
$env['priv']   = $priv;
$env['auth']   = $authuser;
$env['self']   = server_var('PHP_SELF');
$env['user']   = $user;
$env['carr']   = $carr;
$env['debug']  = $debug;
$env['action'] = $action;

db_change($GLOBALS['PREFIX'] . 'provision', $db);
switch ($action) {
    case 'debug':
        debug_audit($env, $db);
        break;
    case 'list':
        list_audit($env, $db);
        break;
    case 'xxx':
        single_shot($env, $db);
        break;
    default:
        unknown_action($env);
        break;
}
db_change($GLOBALS['PREFIX'] . 'core', $db);
echo head_standard_html_footer($authuser, $db);
