<?php

/*
Revision history:

Date        Who     What
----        ---     ----
11-Jun-04   EWB     Created.
17-Jun-04   EWB     Changed some prompts.
 6-Jul-04   EWB     Page titles and explanations.
15-Jul-04   EWB     Restrict display to patch machines only.
 6-Aug-04   EWB     Check the census / patch dirty bit.
 9-Aug-04   EWB     patch_navigate, title change.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
24-Feb-06   BTE     Removed check for census dirty.
21-Jun-06   BTE     Bug 3466: The primary census server page is no longer in
                    order.
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
include('../lib/l-tabs.php');
include('../lib/l-grps.php');
include('../lib/l-ptch.php');
include('../lib/l-rlib.php');
include('../lib/l-pdrt.php');
include('../lib/l-drty.php');
include('../lib/l-gdrt.php');
include('../lib/l-head.php');
include('local.php');



function title($act, $site)
{
    switch ($act) {
        case 'list':
            return 'Microsoft Update - Sites';
        case 'site':
            return "Microsoft Updates for Machines at Site $site";
        case 'host':
            return "Microsoft Updates for One Machine at Site $site";
        case 'dmac':
            return 'Debug Machine Table';
        default:
            return "Unknown Action $act";
    }
}

function order($ord)
{
    switch ($ord) {
        case  0:
            return 'CONVERT(site USING latin1)';
        case  1:
            return 'CONVERT(site USING latin1) desc';
        case  2:
            return 'number desc, CONVERT(site USING latin1)';
        case  3:
            return 'number, CONVERT(site USING latin1)';
        default:
            return order(0);
    }
}


function timestamp($time)
{
    return ($time) ? date('m/d/y H:i:s', $time) : '<br>';
}


function plural($count, $name)
{
    $text = "$count $name";
    if ($count != 1) {
        $text .= 's';
    }
    return $text;
}


function find_site($cid, $auth, $db)
{
    $row  = array();
    $site = '';
    if (($cid > 0) && ($auth)) {
        $qu  = safe_addslashes($auth);
        $sql = "select * from " . $GLOBALS['PREFIX'] . "core.Customers\n"
            . " where id = $cid\n"
            . " and username = '$qu'";
        $row = find_one($sql, $db);
    }
    if ($row) {
        $site = $row['customer'];
    }
    return $site;
}


function find_host($hid, $auth, $db)
{
    $row = array();
    if ($hid > 0) {
        $qu  = safe_addslashes($auth);
        $sql = "select M.*, X.host, X.site\n"
            . " from " . $GLOBALS['PREFIX'] . "softinst.Machine as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as X,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U\n"
            . " where M.id = $hid\n"
            . " and X.id = M.id\n"
            . " and X.site = U.customer\n"
            . " and U.username = '$qu'";
        $row = find_one($sql, $db);
    }
    return $row;
}

function again(&$env)
{
    $self = $env['self'];
    $dbg  = $env['priv'];
    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link('wu-confg.php', 'wizard');
    if ($dbg) {
        $cmd  = "$self?act";
        $args = $env['args'];
        $href = ($args) ? "$self?$args" : $self;
        $a[] = html_link("$cmd=dmac", 'debug');
        $a[] = html_link('../acct/index.php', 'home');
        $a[] = html_link($href, 'again');
        $a[] = html_link($self, 'sites');
    }

    return jumplist($a);
}


function show_machines(&$env, $db)
{
    //     recalc_wcfg_invalid($db);
    $site = $env['site'];
    $set  = array();
    echo again($env);

    echo <<< HERE

        <p>
        This is a list of all the machines at a single site that are running<br>
        the Windows Update process.  It allows you to see the updates on a<br>
        single machine, and change the configuration of how that machine<br>
        handles updates.  Each row in the table represents a single machine.
        </p>

HERE;
    if ($site) {
        $qs  = safe_addslashes($site);
        /* -----------------------
            $sql = "select M.id, C.host, G.name from"
                 . " core.Census as C,\n"
                 . " softinst.Machine as M,\n"
                 . " core.MachineGroups as G,\n"
                 . " softinst.WUConfig as W\n"
                 . " where M.id = C.id\n"
                 . " and C.site = '$qs'\n"
                 . " and M.wuconfigid = W.id\n"
                 . " and G.mgroupid = W.mgroupid\n"
                 . " order by host, name, id";
            --------------------- */
        $sql = "select C.* from"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.Machine as M\n"
            . " where M.id = C.id\n"
            . " and C.site = '$qs'\n"
            . " order by host";
        $set = find_many($sql, $db);
    }

    if ($set) {
        $self = $env['self'];
        //     $head = explode('|','Action|Machine Name|Group Name');
        $head = explode('|', 'Action|Machine Name');
        echo table_header();
        echo pretty_header($site, 3);
        echo table_data($head, 1);
        reset($set);
        foreach ($set as $key => $row) {
            $host = $row['host'];
            //         $name = $row['name'];
            $hid  = $row['id'];
            $x    = "php?hid=$hid&act=host";
            $cmd  = "wu-confg.php?hid=$hid&act";
            $a    = array();
            $a[]  = html_link("$cmd=host", '[configure machine]');
            $a[]  = html_link("wu-stats.$x", '[show updates]');
            $a[]  = html_link("$cmd=hpid", '[configure updates]');
            $a[]  = html_link("wu-sites.$x", '[details]');
            $acts = join('<br>', $a);
            //         $args = array($acts,$host,$name);
            $args = array($acts, $host);
            echo table_data($args, 0);
        }
        echo table_footer();
    }

    echo again($env);
}

function list_sites(&$env, $db)
{
    $ord    = $env['ord'];
    $now    = $env['now'];
    $self   = $env['self'];
    $auth   = $env['auth'];

    echo again($env);

    debug_note("list_sites: user:$auth, ord:$ord");

    echo <<< HERE

        </p>
        This is a list of all the sites with machines running the Windows<br>
        Update process.  It also allows you to see the machines that are at a<br>
        site, see the updates that are installed at a site, and change the<br>
        configuration of how those machines and updates are handled.  Each<br>
        row in the table represents a single site.</p>
HERE;

    $list  = array();
    if ($auth) {
        $qu   = safe_addslashes($auth);
        $wrds = order($ord);
        $sql  = "select X.site,\n"
            . " count(X.site) as number,\n"
            . " C.id as cid\n from"
            . " " . $GLOBALS['PREFIX'] . "core.Census as X,\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.Machine as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as C\n"
            . " where M.id = X.id\n"
            . " and X.site = C.customer\n"
            . " and C.username = '$qu'\n"
            . " group by site\n"
            . " order by $wrds";
        $list = find_many($sql, $db);
    }
    $total = safe_count($list);
    if ($total <= 0) {
        $msg = "No census data found.";
        $msg = fontspeak($msg);
        echo "<p>$msg</p><br>\n";
    } else {
        $w    = "$self?ord";
        $sref = ($ord == 0) ? "$w=1" : "$w=0";
        $nref = ($ord == 2) ? "$w=3" : "$w=2";
        $sdef = html_link($sref, 'Site Name');
        $ndef = html_link($nref, 'Number of Machines');
        $head = array('Action', $sdef, $ndef);

        echo table_header();
        echo table_data($head, 1);

        reset($list);
        foreach ($list as $key => $row) {
            $site = $row['site'];
            $num  = $row['number'];
            $cid  = $row['cid'];

            $cmd  = "wu-confg.php?cid=$cid&act";
            $x    = "php?cid=$cid&act=site";
            $a    = array();
            $a[]  = html_link("wu-sites.$x", '[show machines]');
            $a[]  = html_link("$cmd=site", '[configure machines]');
            $a[]  = html_link("wu-stats.$x", '[show updates]');
            $a[]  = html_link("$cmd=spid", '[configure updates]');
            $acts = join('<br>', $a);
            $text = plural($num, 'machine');
            $args = array($acts, $site, $text);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
    echo "<br>\n";
    echo again($env);
}



function one_machine(&$env, $db)
{
    echo again($env);
    $hid  = $env['hid'];
    $auth = $env['auth'];
    $mach = find_host($hid, $auth, $db);
    if ($mach) {
        $site = $mach['site'];
        $host = $mach['host'];
        $wid  = $mach['wuconfigid'];
        $row  = find_correct_wconfig($hid, $db);
        if ($row) {
            $old = $wid;
            $grp = $row['grp'];
            $cat = $row['cat'];
            $gid = $row['gid'];
            $new = $row['wid'];
            $vld = ($new == $old) ? 'Yes' : 'No';

            $last = timestamp($mach['lastchange']);
            $when = timestamp($mach['lastcontact']);
            $text = ucwords("$host at $site");

            echo table_header();
            echo pretty_header($text, 2);
            echo double('Group:', $grp);
            echo double('Category:', $cat);
            echo double('Cached:', $vld);
            echo double('Change:', $last);
            echo double('Contact:', $when);
            echo table_footer();
        }


        $sql = "select G.mgroupid as gid,\n"
            . " G.name as grp,\n"
            . " G.created as tim,\n"
            . " C.category as cat,\n"
            . " C.precedence as pre from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as A\n"
            . " where M.censusuniq = A.censusuniq\n"
            . " and A.id = $hid\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and G.mcatuniq = C.mcatuniq\n"
            . " order by pre desc, tim desc, grp";
        $set = find_many($sql, $db);

        if ($set) {
            $head = explode('|', 'Action|Priority|Date|Category|Group');
            $cols = safe_count($head);
            $rows = safe_count($set);
            $text = ucwords("$host at $site");
            $text = "$text &nbsp; ($rows groups)";

            echo table_header();
            echo pretty_header($text, $cols);
            echo table_data($head, 1);

            reset($set);
            foreach ($set as $key => $row) {
                $cat  = $row['cat'];
                $grp  = $row['grp'];
                $gid  = $row['gid'];
                $pre  = $row['pre'];
                $tim  = timestamp($row['tim']);
                $fwid = "wu-confg.php?act=fwid&gid=$gid";
                $acts = html_link($fwid, '[config]');
                $args = array($acts, $pre, $tim, $cat, $grp);
                echo table_data($args, 0);
            }
        }
        echo table_footer();
    }
    echo again($env);
}



function debug_machines(&$env, $db)
{
    echo again($env);
    $sql = "select C.host, C.site, M.* from\n"
        . " " . $GLOBALS['PREFIX'] . "softinst.Machine as M,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
        . " where C.id = M.id\n"
        . " order by site, host, id";
    $set = find_many($sql, $db);
    if ($set) {
        $head = explode('|', 'Site|Host|Id|Wid|Last|Change|def|cdef');
        $rows = safe_count($set);
        $cols = safe_count($head);
        $text = $GLOBALS['PREFIX'] . "softinst.Machine ($rows rows)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($head, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $site = disp($row, 'site');
            $host = disp($row, 'host');
            $id   = $row['id'];
            $wid  = $row['wuconfigid'];
            $pid  = $row['lastdefconfigid'];
            $chng = timestamp($row['lastchange']);
            $last = timestamp($row['lastcontact']);
            $lpid = timestamp($row['lastdefchange']);
            $args = array($site, $host, $id, $wid, $last, $chng, $pid, $lpid);
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        echo "<p>The Machine table seems to be empty.</p>\n";
    }
    echo again($env);
}




function unknown_action(&$env, $db)
{
    debug_note("unknown action");
}

/*
    |  Main program
    */

$now  = time();
$db   = db_connect();
$auth = process_login($db);
$comp = component_installed();
$nav  = patch_navigate($comp);
$dbg  = get_integer('debug', 1);
$cid  = get_integer('cid', 0);
$hid  = get_integer('hid', 0);
$user = user_data($auth, $db);
$site = find_site($cid, $auth, $db);
$mach = find_host($hid, $auth, $db);

$priv   = @($user['priv_debug']) ?   1  : 0;
$debug  = @($user['priv_debug']) ? $dbg : 0;
$filter = @($user['filtersites']) ?  1  : 0;
$act    = get_string('act', 'list');

if ((!$priv) && ($act == 'dmac')) {
    $act = 'list';
}

if ((!$site) && ($mach)) {
    $site = $mach['site'];
}


debug_note("site is $site");

$title = title($act, $site);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $auth, $nav, 0, 0, $db);

$date = datestring(time());

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($debug) echo "<h2>$date</h2>";

debug_array($debug, $_POST);

check_patch_dirty($db);

$env = array();
$env['db']   = $db;
$env['act']  = $act;
$env['now']  = time();
$env['ord']  = get_integer('ord', -1);
$env['hid']  = $hid;
$env['cid']  = $cid;
$env['auth'] = $auth;
$env['site'] = $site;
$env['carr'] = site_array($auth, $filter, $db);
$env['self'] = server_var('PHP_SELF');
$env['args'] = server_var('QUERY_STRING');
$env['priv']  = $priv;
$env['debug'] = $debug;
$env['limit'] = get_integer('limit', 50);

switch ($act) {
    case 'list':
        list_sites($env, $db);
        break;
    case 'site':
        show_machines($env, $db);
        break;
    case 'host':
        one_machine($env, $db);
        break;
    case 'dmac':
        debug_machines($env, $db);
        break;
    default:
        unknown_action($env, $db);
        break;
}
echo head_standard_html_footer($auth, $db);
