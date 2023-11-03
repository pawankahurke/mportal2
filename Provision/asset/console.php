<?php

/*
Revision history:

Date        Who     What
----        ---     ----
15-Aug-02   EWB     Only show most recent logging
27-Aug-02   EWB     Display earliest and latest times
11-Sep-02   EWB     Merge with new asset code.
19-Sep-02   EWB     Giant refactoring
30-Sep-02   EWB     Restrict to users own machines.
 1-Oct-02   EWB     Tree view of links.
 3-Oct-02   EWB     Correct spelling for "cellspacing"
 7-Oct-02   EWB     Separate table for each site, hosts in alphabetical order
 8-Oct-02   EWB     No more colored backgrounds.
10-Oct-02   EWB     Uses get_argument()
30-Oct-02   EWB     Change reports, sorting options
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Added local navigation
 7-Jan-03   AAM     Performance fix:  don't copy arrays.
10-Jan-03   EWB     Minimal quotes.
16-Jan-03   EWB     Consistant user interface.
10-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change()
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
13-Mar-03   EWB     Don't present delete option to non-admin user.
28-Mar-03   EWB     Added index table and system counts.
31-Mar-03   EWB     Events link.
 1-Apr-03   EWB     Asset Control Box
 2-Apr-03   EWB     Handle ambiguous machine names.
 2-Apr-03   EWB     Just one active site.
 9-Apr-03   EWB     Direct access to pager timestamps.
24-Apr-03   EWB     Do the sitefilter thing.
29-Apr-03   EWB     use new priv_asset.
30-Apr-03   EWB     User site filter bits.
12-Apr-03   EWB     Handle user without enabled sites.
22-May-03   EWB     Quote Crusade.
10-Jun-03   EWB     Added delete site.
10-Jun-03   EWB     Don't show delete site link to non-priv user.
17-Jun-03   EWB     Slave Database.
20-Jun-03   EWB     No Slave Database.
25-Jun-03   EWB     Ignore damaged machines.
24-Mar-04   EWB     Site/Machine view.
25-Mar-04   EWB     explicit asset hierarcy.
 2-Apr-04   EWB     Removed class view and associated support.
 5-Apr-04   EWB     Actions lined up vertically.
 6-Apr-04   EWB     Show site/host totals.
 7-Apr-04   EWB     Sort by site/number of machines.
12-Apr-04   EWB     Show machine name in title for its tree.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-user.php');
include('../lib/l-alib.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-qtbl.php');
include('../lib/l-jump.php');
include('../lib/l-slct.php');
//  include ( '../lib/l-slav.php'  );
include('../lib/l-head.php');
include('local.php');


function title($act, $site, $host)
{
    $host = ucwords($host);
    $text = 'Assets -';
    switch ($act) {
        case 'menu':
            return "$text Sites";
        case 'site':
            return "$text $site Machines";
        case 'tree':
            return "$text $host Tree";
        case 'stree':
            return "$text $site Tree";
        case 'world':
            return "$text Asset Tree";
        case 'empty':
            return "$text Empty";
        default:
            return "$text Console";
    }
}


function double($prompt, $action)
{
    return <<< HERE
<tr>
    <td align="right">
        $prompt
    </td>
    <td align="left">
        $action
    </td>
</tr>

HERE;
}


function site_totals($sites, $hosts)
{
    echo table_start();
    echo pretty_header('Total', 2);
    echo double('Sites:', $sites);
    echo double('Machines:', $hosts);
    echo table_end();
    echo "<br>\n";
}

function sdate($utime)
{
    return date('j-M-Y H:i:s', $utime);
}

function ldate($utime)
{
    return date('m/d/y H:i:s', $utime);
}

function clear_rows()
{
    echo "\n\n";
    echo '<br clear="all">';
    echo "\n\n";
}

function plural($n, $name)
{
    $word = ($n == 1) ? $name : $name . 's';
    return "$n $word";
}


function again($env)
{
    $priv = $env['priv'];
    $self = $env['self'];
    $args = $env['args'];
    $href = ($args) ? "$self?$args" : $self;
    $act  = "$self?act";

    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    if ($env['acts'] != 'menu') {
        $a[] = html_link($self, 'sites');
    }
    $a[] = html_link("$act=world", 'asset tree');
    if ($priv) {
        $home = '../acct/index.php';
        $a[] = html_link($href, 'again');
        $a[] = html_link('census.php', 'census');
        $a[] = html_link($home, 'home');
    }
    return jumplist($a);
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

function table_head($args)
{
    $m = '';
    if ($args) {
        $m .= "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            $m .= " <th>$data</th>\n";
        }
        $m .= "</tr>\n";
    }
    return $m;
}

function table_name($name, $width)
{
    return "<tr><th colspan=\"$width\" align=\"center\">$name</th></tr>\n";
}

function indent($n)
{
    $s = '';
    for ($i = 0; $i < $n; $i++) {
        $s .= '&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    return $s;
}


function show_leaf(&$env, $did, $depth)
{
    $name  = $env['names'][$did]['name'];
    $mid   = $env['mid'];
    $cid   = $env['cid'];
    $d     = indent($depth);
    $href  = "detail.php?did=$did";
    if ($cid) $href .= "&cid=$cid";
    if ($mid) $href .= "&mid=$mid";
    $link  = html_link($href, $name);
    echo "$d<span class=\"blue\">$link</span><br>\n";
}

function draw_tree(&$env, $did, $depth)
{
    $db     = $env['db'];
    $mid    = $env['mid'];
    $cid    = $env['cid'];
    $child  = asset_children($did, $db);
    $table  = asset_members($did, $db);
    if ($child) {
        $d = indent($depth);
        $name = $env['names'][$did]['name'];
        if ($table) {
            $href = "detail.php?gid=$did";
            if ($cid) $href .= "&cid=$cid";
            if ($mid) $href .= "&mid=$mid";
            $link = html_link($href, $name);
            echo "$d<span class=\"faded\">$link</span><br>\n";
            foreach ($table as $key => $did) {
                draw_tree($env, $did, $depth + 1);
            }
        } else {
            echo "$d<span class=\"faded\">$name</span><br>\n";
            foreach ($child as $key => $data) {
                draw_tree($env, $data, $depth + 1);
            }
        }
    } else {
        show_leaf($env, $did, $depth);
    }
}


function show_tree($names, $cid, $mid, $db)
{
    $names[0]['name'] = 'SYSTEM SUMMARY';
    $a = array();
    $a['db']    = $db;
    $a['cid']   = $cid;
    $a['mid']   = $mid;
    $a['names'] = $names;
    draw_tree($a, 0, 0);
}


function order($ord)
{
    switch ($ord) {
        case  0:
            return 'host';
        case  1:
            return 'host desc';
        case  2:
            return 'slatest';
        case  3:
            return 'slatest desc';
        case  4:
            return 'searliest';
        case  5:
            return 'searliest desc';
        case  6:
            return 'clatest';
        case  7:
            return 'clatest desc';
        case  8:
            return 'cearliest';
        case  9:
            return 'cearliest desc';
        default:
            return order(0);
    }
}


function asset_order($wrd)
{
    switch ($wrd) {
        case  0:
            return 'cust';
        case  1:
            return 'cust desc';
        case  2:
            return 'number desc, cust';
        case  3:
            return 'number';
        default:
            return asset_order(0);
    }
}


function empty_access(&$env)
{
    $msg = "You need access to at least one machine in order to use this page.";
    $msg = fontspeak($msg);
    echo "<p>$msg</p>\n";
}



function elink($site, $host, $umin, $umax)
{
    $us = urlencode($site);
    $uh = urlencode($host);
    $er = '../event/pager.php?'
        . "sel_machine=$uh&"
        . "sel_customer=$us&"
        . "umin=$umin&"
        . "umax=$umax";
    return html_link($er, '[event]');
}



function asset_census($access, $carr, $db)
{
    $used = array();
    $list = array();
    $nums = array();
    $cids = array();
    $mids = array();
    if ($carr) {
        reset($carr);
        foreach ($carr as $cid => $site) {
            $nums[$site] = 0;
            $cids[$site] = $cid;
        }
        ksort($cids);
    }
    if (($cids) && ($access)) {
        $sql = "select * from Machine\n"
            . " where provisional = 0 and\n"
            . " cust in ($access)";
        $list = find_many($sql, $db);
    }
    if (($list) && ($nums)) {
        foreach ($list as $key => $row) {
            $site = $row['cust'];
            $host = $row['host'];
            $mid  = $row['machineid'];
            $nums[$site]++;
            $used[$site] = $cids[$site];
            $mids[$mid] = $host;
        }
        ksort($used);
        asort($mids);
    }
    $temp['used'] = $used;
    $temp['nums'] = $nums;
    $temp['mids'] = $mids;
    return $temp;
}


function asset_unknown($action)
{
    debug_note("asset_unkown: action:$action");
}

function asset_menu(&$env, $db)
{
    $now   = $env['now'];
    $wrd   = $env['wrd'];
    $acts  = $env['acts'];
    $used  = $env['used'];
    $nums  = $env['nums'];
    $mids  = $env['mids'];
    $carr  = $env['carr'];
    $asset = $env['asset'];
    $list  = array();

    if (($carr) && ($used)) {
        $wrds = asset_order($wrd);
        $txt  = db_access($env['carr']);
        $sql  = "select cust, count(cust) as number\n"
            . " from Machine\n"
            . " where cust in ($txt)\n"
            . " and provisional = 0\n"
            . " group by cust\n"
            . " order by $wrds";
        $list = find_many($sql, $db);
    }
    if ($list) {
        $s = safe_count($used);
        $m = safe_count($mids);
        site_totals($s, $m);

        $self = $env['self'];

        $w = "$self?act=$acts&wrd";

        $sref = ($wrd == 0) ? "$w=1" : "$w=0";
        $nref = ($wrd == 2) ? "$w=3" : "$w=2";
        $sdef = html_link($sref, 'Site Name');
        $ndef = html_link($nref, 'Number of Machines');
        $head = array('Action', $sdef, $ndef);

        echo table_start();
        echo table_head($head);
        reset($list);
        foreach ($list as $key => $row) {
            $site = $row['cust'];
            $num  = $row['number'];
            $cid  = $used[$site];
            $text = plural($num, 'machine');
            $dref = "del-cust.php?cid=$cid";
            $act  = "$self?cid=$cid&act";
            $vref = "$act=site";
            $tref = "$act=stree";
            $a    = array();
            $a[] = html_link($tref, '[tree]');
            $a[] = html_link($vref, '[machines]');
            if ($asset) $a[] = html_link($dref, '[delete]');
            $acts = join('<br>', $a);
            $args = array($acts, $site, $text);
            echo asset_data($args);
        }
        echo table_end();
        clear_rows();
    }
}


function site_tree(&$env, $db)
{
    $cid   = $env['cid'];
    $carr  = $env['carr'];
    $site  = $carr[$cid];
    $names = asset_names($db);
    if (($cid) && ($site) && ($names)) {
        echo "<h2>$site</h2>\n";
        show_tree($names, $cid, 0, $db);
    }
}

function host_tree(&$env, $db)
{
    $cid  = $env['cid'];
    $mid  = $env['mid'];
    $carr = $env['carr'];
    $mids = $env['mids'];
    $good = false;
    $names = asset_names($db);
    if (($mid) && ($cid) && ($names)) {
        $site = @strval($carr[$cid]);
        $host = @strval($mids[$mid]);
        if (($site) && ($host)) {
            $text = ucwords($host);
            echo "<h2>$text at $site</h2>\n";
            show_tree($names, $cid, $mid, $db);
            $good = true;
        }
    }
    if (!$good) {
        $msg = "Failed to find the specified machine.";
        $msg = fontspeak($msg);
        echo "<p>$msg</p>\n";
    }
}

function world_tree(&$env, $db)
{
    $good = false;
    $names = asset_names($db);
    if ($names) {
        echo "<h2>Asset Hierarchy</h2>\n";
        show_tree($names, 0, 0, $db);
        $good = true;
    }
    if (!$good) {
        $msg = "Failed to draw asset hierarchy.";
        $msg = fontspeak($msg);
        echo "<p>$msg</p>\n";
    }
}



function asset_one(&$env, $db)
{
    $ord   = $env['ord'];
    $cid   = $env['cid'];
    $now   = $env['now'];
    $used  = $env['used'];
    $carr  = $env['carr'];
    $self  = $env['self'];
    $site  = $carr[$cid];

    $qs    = safe_addslashes($site);
    $order = order($ord);
    $sql   = "select * from Machine where\n"
        . " provisional = 0 and\n"
        . " cust = '$qs'\n"
        . " order by $order";
    $mach  = find_many($sql, $db);
    $tmin  = $now - 36000;  // 10 hours ago
    $tmax  = $now + 3600;   // 1 hour from now
    $n     = safe_count($mach);
    if ($mach) {
        site_totals(1, $n);

        $asset = $env['asset'];
        clear_rows();


        $o = "$self?cid=$cid&act=site&ord";

        $eref = ($ord == 5) ? "$o=4" : "$o=5";
        $lref = ($ord == 3) ? "$o=2" : "$o=3";
        $mref = ($ord == 1) ? "$o=0" : "$o=1";

        $mdef = html_link($mref, 'Machine');
        $edef = html_link($eref, 'Earliest');
        $ldef = html_link($lref, 'Latest');
        $head = array('Action', $mdef, $edef, $ldef);

        echo table_start();
        //      echo pretty_header($site,4);
        echo table_head($head);
        reset($mach);
        foreach ($mach as $key => $data) {
            $host = $data['host'];
            $dmid = $data['machineid'];
            $smin = $data['searliest'];
            $smax = $data['slatest'];

            $dmin = ldate($smin);
            $dmax = ldate($smax);

            $vref = "detail.php?mid=$dmid";
            $cref = "change.php?mid=$dmid";
            $dref = "del-host.php?mid=$dmid";
            $tref = "$self?cid=$cid&mid=$dmid&act=tree";
            $xref = "export.php?cid=$cid&mid=$dmid&act=xhost";

            $a    = array();
            $a[]  = html_link($vref, '[details]');
            $a[]  = html_link($tref, '[tree]');
            $a[]  = html_link($cref, '[changes]');
            $a[]  = html_page($xref, '[export]');
            $a[]  = elink($site, $host, $tmin, $tmax);
            if ($asset) {
                $a[]  = html_link($dref, '[delete]');
            }
            $act  = implode("<br>\n", $a);
            $list = array($act, $host, $dmin, $dmax);
            echo asset_data($list);
        }
        echo table_end();
        clear_rows();
    }
}


/*
    |  Main program
    */

$now = time();
$db  = db_connect();
$act = get_string('act', 'menu');

$authuser = process_login($db);
$comp = component_installed();

$dbg = (get_integer('debug', 0)) ? 1 : 0;
$ast = (get_integer('asset', 1)) ? 1 : 0;
$ord = get_integer('ord', 0);
$wrd = get_integer('wrd', 0);
$mid = get_integer('mid', 0);
$cid = get_integer('cid', 0);

$user  = user_data($authuser, $db);
$pd    = @($user['priv_debug']) ?  1 : 0;
$pa    = @($user['priv_asset']) ?  1 : 0;
$fs    = @($user['filtersites']) ? 1 : 0;
$debug = ($pd) ? $dbg : 0;
$asset = ($pa) ? $ast : 0;

$carr = site_array($authuser, $fs, $db);
$access = db_access($carr);
db_change($GLOBALS['PREFIX'] . 'asset', $db);

$temp = asset_census($access, $carr, $db);
$used = $temp['used'];
$mids = $temp['mids'];
$nums = $temp['nums'];

$site = '';
$host = '';
if ($cid > 0) {
    $site = @trim($carr[$cid]);
}
if ($mid > 0) {
    $host = @trim($mids[$mid]);
}

if (!$used) {
    $act = 'empty';
}


$env = array();
$env['now'] = $now;
$env['mid'] = $mid;
$env['ord'] = $ord;
$env['wrd'] = $wrd;
$env['cid'] = $cid;
$env['self'] = server_var('PHP_SELF');
$env['args'] = server_var('QUERY_STRING');
$env['carr'] = $carr;
$env['user'] = $user;
$env['used'] = $used;
$env['nums'] = $nums;
$env['mids'] = $mids;
$env['acts'] = $act;
$env['priv'] = $pd;

$env['debug']  = $debug;
$env['asset']  = $asset;
$env['access'] = $access;

db_change($GLOBALS['PREFIX'] . 'core', $db);
$title = title($act, $site, $host);
$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
db_change($GLOBALS['PREFIX'] . 'asset', $db);

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users
clear_rows();

debug_note("action:$act filter:$fs user:$authuser");

echo again($env);
switch ($act) {
    case 'menu':
        asset_menu($env, $db);
        break;
    case 'site':
        asset_one($env, $db);
        break;
    case 'tree':
        host_tree($env, $db);
        break;
    case 'stree':
        site_tree($env, $db);
        break;
    case 'world':
        world_tree($env, $db);
        break;
    case 'empty':
        empty_access($env);
        break;
    default:
        asset_unknown($action);
        break;
}
echo again($env);
db_change($GLOBALS['PREFIX'] . 'core', $db);
echo head_standard_html_footer($authuser, $db);
