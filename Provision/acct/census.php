<?php

/*
Revision history:

Date        Who     What
----        ---     ----
25-Nov-02   EWB     Created.
27-Nov-02   EWB     Sort by timestamp
 5-Dec-02   EWB     Reorginization Day
17-Dec-02   EWB     Fixed short php tags
 3-Feb-03   EWB     Sorting options.
17-Feb-03   EWB     Uses sandbox libraries.
24-Feb-03   EWB     Migration of debug pages.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
17-Mar-03   EWB     Moved census count to start of page.
19-Mar-03   NL      Move $debug initialization and surrounding lines above ob_ lines
26-Mar-03   EWB     Made accessible to mere mortals.
27-Mar-03   EWB     Categorized output.
27-Mar-03   EWB     Census control.
28-Mar-03   EWB     Sort from form.
31-Mar-03   EWB     Alex suggestions.
 1-Apr-03   EWB     Reorder census columns.
 1-Apr-03   EWB     Handle case where action is empty.
 1-Apr-03   EWB     Clear debug controls.
 3-Apr-03   EWB     Fixed a spelling error.
 9-Apr-03   EWB     Direct access to pager timestamps.
24-Apr-03   EWB     Echo jumptable.
24-Apr-03   EWB     Do the sitefilter thing.
25-Apr-03   EWB     Handle empty access list.
29-Apr-03   EWB     l-cust now obsolete.
30-Apr-03   EWB     User site filters.
20-May-03   EWB     standard server ident
22-May-03   EWB     Quote Crusade
13-Jun-03   EWB     Delete Site Command
31-Oct-03   EWB     Added jumptable.
26-Nov-03   EWB     Report timestamps for machine expunge.
30-Dec-03   EWB     normal user can remove machine from census.
31-Dec-03   EWB     report age always as integer.
12-Feb-04   EWB     purge provision database
18-Feb-04   EWB     delete site for normal users.
19-Feb-04   EWB     delete site / host removes cryptkeys.
12-Mar-04   EWB     simple view.
 2-Apr-04   EWB     Removed merge/class view.
 5-Apr-04   EWB     Actions lined up vertically.
 7-Apr-04   EWB     sort by sites/number of machines
29-Apr-04   EWB     expunge of last machine clears config site.
 4-May-04   EWB     normal users can expunge site/host.
 4-Jun-04   EWB     Expunge site/host sets census dirty flag.
14-Jul-04   EWB     purge from census clears patches and machine groups.
26-Jul-04   EWB     option to show contact info names.
 2-Sep-04   EWB     fixed wrong param order for value_range.
10-Jan-05   EWB     remote control buttons
11-Jan-05   EWB     remote control buttons in column zero
17-Jan-05   EWB     Uses gif image for connect button.
17-Jan-05   EWB     Connect column conditional upon config priv
 8-Feb-05   EWB     Page loads faster without images.
18-Mar-05   EWB     Delete from debug page.
11-May-05   EWB     Census "expanded" display uses new database
27-May-05   EWB     "Delete" must expunge for config/provis/patch
20-Jul-05   EWB     Fixed the case-sensitive sitename bug.
12-Sep-05   BTE     Added checksum invalidation code.
12-Oct-05   BTE     Changed references from gconfig to core.
05-Nov-05   BTE     Use the permanent delete option when removing a record.
10-Nov-05   BTE     Some delete operations should not be permanent.
15-Dec-05   BTE     Updated to properly use VarValues.def.
24-Feb-06   BTE     Bug 3079: Make expunge/server deletions permanent and make
                    client preserve self.
15-Mar-06   BTE     Bug 3186: Event logging appears to be completely broken on
                    4.3 server.
23-Mar-06   JRN     Added the manage link.
05-Apr-06   AAM     Added link to UltraVNC connection ID page (bug 3237).
06-May-06   BTE     Bug 3209: 4.2 to 4.3 server upgrade does not work
                    correctly.
21-Jun-06   BTE     Bug 3466: The primary census server page is no longer in
                    order.
19-Jul-06   BTE     Bug 3239: 913 errors on CI server.
13-Sep-06   AAM     Updated the elink function to use the new sel_recent
                    parameter for pager.php.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()                    
21-Mar-07   AAM     Updated expanded census to include Company and Service Level.
19-Feb-08   BTE     Bug 4416: Move the "last event log" timestamp into shared
                    memory.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-rlib.php');
include('../lib/l-cnst.php');
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-rcmd.php');
include('../lib/l-serv.php');
include('../lib/l-user.php');
include('../lib/l-slct.php');
include('../lib/l-gsql.php');
include('../lib/l-jump.php');
include('../lib/l-gcfg.php');
include('../lib/l-cprg.php');
include('../lib/l-tabs.php');
include('../lib/l-form.php');
include('../lib/l-head.php');
include('../lib/l-errs.php');
include('../lib/l-dsyn.php');
include('../lib/l-gdrt.php');
include('../lib/l-grps.php');
include('../lib/l-core.php');

define('constButtonCon', 'Connect');
define('constContactInfoScop', '241');
define('constFirstName', 'FirstName');
define('constLastName', 'LastName');
define('constCompany', 'Company');
define('constServiceLevel', 'ServiceLevel');


function title($act, $site)
{
    $c = 'Census -';
    switch ($act) {
        case 'list':
            return "$c Sites";
        case 'site':;
        case 'name':
            return "$c $site Machines";
        case 'del':
            return "$c Machine Deleted";
        case 'exp':
            return "$c Machine Expunged";
        case 'chost':
            return "$c Delete Machine";
        case 'csite':
            return "$c Delete $site";
        case 'dsite':
            return "$c $site Deleted";
        case 'esite':
            return "$c $site Expunged";
        case 'debug':
            return "$c Debug";
        default:
            return "$c Unknown";
    }
}


function ident($now)
{
    $date = datestring($now);
    $info = asi_info();
    $version = $info['svvers'];
    return "\n<h2>$date ($version)</h2>\n";
}

function unknown_option($action, $db)
{
    debug_note("unknown_option($action,db)");
}


function count_census(&$env, $db)
{
    $access = $env['access'];
    $num = 0;
    if ($access) {
        $sql = "select count(*) from Census\n"
            . " where site in ($access)";
        $res = redcommand($sql, $db);
        if ($res) {
            $num = mysqli_result($res, 0);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }
    return $num;
}


function again(&$env)
{
    $dbg  = $env['priv_debug'];
    $self = $env['self'];
    $cid  = $env['cid'];
    $act  = $env['action'];
    $cmd  = "$self?action";
    $a[]  = html_link('#top', 'top');
    $a[]  = html_link('#bottom', 'bottom');
    if (($cid) && ($act == 'site')) {
        $name = "$cmd=name&cid=$cid";
        $a[]  = html_link($name, 'expand');
    }
    if (($cid) && ($act == 'name')) {
        $site = "$cmd=site&cid=$cid";
        $a[]  = html_link($site, 'collapse');
    }
    $a[]  = html_link($self, 'sites');
    $a[]  = html_link('../config/listuvnc.php', 'connection ids');
    if ($dbg) {
        $dref = "$cmd=debug&ord=0";
        $home = 'index.php';
        $args = server_var('QUERY_STRING');
        $href = ($args) ? "$self?$args" : $self;
        $a[]  = html_link($dref, 'debug');
        $a[]  = html_link($href, 'again');
        $a[]  = html_link($home, 'home');
    }
    return jumplist($a);
}


function find_site($cid, $auth, $db)
{
    $qu  = safe_addslashes($auth);
    $sql = "select * from Customers\n"
        . " where id = $cid\n"
        . " and username = '$qu'";
    return find_one($sql, $db);
}


function asset_tree(&$env, $db)
{
    $list   = array();
    $asset  = array();
    $comp   = $env['comp'];
    $access = $env['access'];
    if (($comp['asst']) && ($access)) {
        $sql  = "select * from Machine\n"
            . " where cust in ($access)\n"
            . " order by machineid";
        if (mysqli_select_db($db, asset)) {
            $list = find_many($sql, $db);
            mysqli_select_db($db, core);
        }
    }

    if ($list) {
        foreach ($list as $key => $row) {
            $mid  = $row['machineid'];
            $host = $row['host'];
            $site = $row['cust'];
            $asset[$site][$host] = $mid;
        }
    }
    return $asset;
}



function order($ord)
{
    switch ($ord) {
        case  0:
            return 'last desc, id';
        case  1:
            return 'last, id';
        case  2:
            return 'host asc, site asc, last desc';
        case  3:
            return 'host desc, site asc, last desc';
        case  4:
            return 'site asc, last desc';
        case  5:
            return 'site asc, last asc';
        case  6:
            return 'site asc, host';
        case  7:
            return 'site asc, host desc';
        case  8:
            return 'id asc';
        case  9:
            return 'id desc';
        case 10:
            return 'uuid, last desc, id';
        case 11:
            return 'uuid desc, last, id';
        case 12:
            return 'lname, fname, host, id';
        case 13:
            return 'lname desc, fname desc, host desc, id';
        case 14:
            return 'fname, lname, host, id';
        case 15:
            return 'fname desc, lname desc, host desc, id';
        default:
            return order(0);
    }
}


function worder($wrd)
{
    switch ($wrd) {
        case  0:
            return 'CONVERT(site USING latin1)';
        case  1:
            return 'CONVERT(site USING latin1) desc';
        case  2:
            return 'number desc, CONVERT(site USING latin1)';
        case  3:
            return 'number, CONVERT(site USING latin1)';
        default:
            return worder(0);
    }
}


/* Return an HTML link that will go to a display of recent events for
        the machine with censusid = $mid. */
function elink($mid)
{
    $eref = '../event/pager.php?sel_recent=' . $mid;
    return html_link($eref, '[event]');
}


function alink($mid)
{
    $href = "../asset/detail.php?mid=$mid";
    return html_link($href, '[asset]');
}

function dlink($self, $mid)
{
    $href = "$self?action=chost&mid=$mid";
    return html_link($href, '[delete]');
}

function dsite($self, $cid)
{
    $href = "$self?action=csite&cid=$cid";
    return html_link($href, '[delete]');
}

function mlink($mid)
{
    $href = "../config/config.php?hid=$mid";
    return html_link($href, '[manage]');
}

function plural($count, $name)
{
    $text = "$count $name";
    if ($count != 1) {
        $text .= 's';
    }
    return $text;
}


//  function site_header($name,$count)
//  {
//      $text = plural($count,'machine');
//      echo "<p>$name has $text.</p>\n";
//  }


function site_totals($sites, $hosts)
{
    echo table_header();
    echo pretty_header('Total', 2);
    echo double('Sites:', $sites);
    echo double('Machines:', $hosts);
    echo table_footer();
    echo "<br>\n";
}



/*
    |  It has been demanded that actions be lined up
    |  vertically in the first column of the table.
    |
    |  This makes the table much taller than it needs
    |  to be, and when the number of rows becomes large
    |  it forces the user do a lots of extra vertical
    |  scrolling.
    |
    |  In my view, this is not only stupid, and irritating,
    |  it's also ugly as well.  But that's just my opinion.
    */

function list_sites(&$env, $db)
{
    $ord    = $env['ord'];
    $wrd    = $env['wrd'];
    $now    = $env['now'];
    $self   = $env['self'];
    $user   = $env['user'];
    $carr   = $env['carr'];
    $comp   = $env['comp'];
    $admin  = $env['admin'];
    $access = $env['access'];
    $filter = $env['filter'];

    $sites  = array();

    echo again($env);

    debug_note("list_sites: user:$user, admin:$admin, ord:$ord");

    $set   = array();
    $sites = array();
    if ($user) {
        $filt = ($filter) ? " and C.sitefilter = 1\n" : '';
        $wrds = worder($wrd);
        $qa   = safe_addslashes($user);
        $sql  = "select C.id as cid,\n"
            . " C.customer as site,\n"
            . " count(H.id) as number from\n"
            . " Census as H,\n"
            . " Customers as C\n"
            . " where H.site = C.customer\n"
            . " and C.username = '$qa'\n"
            . $filt
            . " group by site\n"
            . " order by $wrds";
        $set = find_many($sql, $db);
    }
    $total = safe_count($set);

    if ($total <= 0) {
        $msg = "No census data found.";
        $msg = fontspeak($msg);
        echo "<p>$msg</p><br>\n";
    } else {
        $n = 0;
        reset($set);
        foreach ($set as $key => $row) {
            $n += $row['number'];
        }
        site_totals($total, $n);

        $w    = "$self?wrd";
        $sref = ($wrd == 0) ? "$w=1" : "$w=0";
        $nref = ($wrd == 2) ? "$w=3" : "$w=2";
        $sdef = html_link($sref, 'Site Name');
        $ndef = html_link($nref, 'Number of Machines');
        $head = array('Action', $sdef, $ndef);

        echo table_header();
        //      echo pretty_header('Site Census',3);
        echo table_data($head, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $site = $row['site'];
            $num  = $row['number'];
            $cid  = $row['cid'];
            $act  = "$self?action=site&cid";
            $view = html_link("$act=$cid", '[machines]');
            $text = plural($num, 'machine');
            $del  = ($cid) ? dsite($self, $cid) : '<br>';
            $acts = "$view<br>$del";
            $args = array($acts, $site, $text);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
    echo "<br>\n";
    echo again($env);
}


function connect_link($mid)
{
    $href = "../config/remote.php?act=host&mid=$mid";
    $conn = "window.open('$href','_self');";
    return click(constButtonCon, $conn);
}


function single_census(&$env, $db)
{
    echo again($env);

    $cid  = $env['cid'];
    $now  = $env['now'];
    $row  = $env['row'];
    $ord  = value_range(0, 7, $env['ord']);
    $comp = $env['comp'];
    $self = $env['self'];
    $admn = $env['admin'];
    $cnfg = $env['priv_config'];
    debug_note("single_census: cid:$cid, admin:$admn");

    //    $sql = "select * from Customers where id = $cid";
    //    $row = find_one($sql,$db);
    $hosts = array();
    if ($row) {
        $site  = $row['customer'];
        $qs    = safe_addslashes($site);
        $order = order($ord);
        $sql   = "select * from TempCensusCache\n"
            . " where site = '$qs'\n"
            . " order by $order";
        $hosts = CORE_GetAllTempCensusCache($sql, $db);
    }

    $n = safe_count($hosts);
    if ($hosts) {
        site_totals(1, $n);

        $act  = "$self?action=site&cid=$cid";
        $wref = ($ord == 0) ? "$act&ord=1" : "$act&ord=0";
        $href = ($ord == 6) ? "$act&ord=7" : "$act&ord=6";

        $host = html_link($href, 'Machine');
        $when = html_link($wref, 'Latest Event Log');
        $act  = 'Action';

        if ($cnfg) {
            $conn = constButtonCon;
            $head = array($conn, $act, $host, $when);
        } else {
            $head = array($act, $host, $when);
        }
        $cols = safe_count($head);
        $asset = asset_tree($env, $db);

        //      site_header($site,count($hosts));

        echo table_header();
        echo pretty_header($site, $cols);
        echo table_data($head, 1);

        reset($hosts);
        foreach ($hosts as $key => $row) {
            $action = '<br>';
            $mid  = $row['id'];
            $host = $row['host'];
            $last = $row['last'];
            $when = date("m/d H:i:s", $last);
            $aid  = @intval($asset[$site][$host]);
            $a = array();
            if ($comp['evnt']) $a[] = elink($mid);
            if ($aid > 0)      $a[] = alink($aid);
            $a[] = dlink($self, $mid);
            $a[] = mlink($mid);
            $act = ($a) ? join("<br>\n", $a) : '<br>';
            if ($cnfg) {
                $conn = connect_link($mid);
                $args = array($conn, $act, $host, $when);
            } else {
                $args = array($act, $host, $when);
            }
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        $msg = "No census information found.";
        $msg = fontspeak($msg);
        echo "$msg<br>\n";
    }
    echo again($env);
}


function create_temp_table($name, $db)
{
    $sql = "create temporary table $name (\n"
        . " id    int(11)     default 0  not null,\n"
        . " site  varchar(50) default '' not null,\n"
        . " host  varchar(64) default '' not null,\n"
        . " last  int(11)     default 0  not null,\n"
        . " fname text        default '' not null,\n"
        . " lname text        default '' not null,\n"
        . " comp  text        default '' not null,\n"
        . " slev  text        default '' not null,\n"
        . " primary key (id))";
    $res = redcommand($sql, $db);
    return $res;
}

function delete_temp_table($name, $db)
{
    $sql = "drop table $name";
    $res = redcommand($sql, $db);
}


function build_temp_table($name, $db)
{
    $res = create_temp_table($name, $db);
    if (!$res) {
        delete_temp_table($name, $db);
        $res = create_temp_table($name, $db);
    }
    return $res;
}


function name_census(&$env, $db)
{
    echo again($env);

    $cid    = $env['cid'];
    $now    = $env['now'];
    $row    = $env['row'];
    $ord    = $env['ord'];
    $comp   = $env['comp'];
    $cnfg   = $env['priv_config'];
    $self   = $env['self'];
    $admin  = $env['admin'];
    $debug  = $env['debug'];
    debug_note("name_census: cid:$cid, admin:$admin");

    $name = 'TempCensus';
    $set = array();
    $res = build_temp_table($name, $db);
    if (($res) && ($row)) {
        $site = $row['customer'];
        $qs   = safe_addslashes($site);
        $sql  = "insert ignore into $name\n"
            . " select id, site, host, last,'','','',''\n"
            . " from " . $GLOBALS['PREFIX'] . "core.Census\n"
            . " where site = '$qs'";
        $res  = redcommand($sql, $db);
        CORE_UpdateCensusCacheTable($name, 'id', $db);
    }

    if (($res) && ($row)) {
        $scop = constContactInfoScop;
        $selSQL = "VarValues.valu, VarValues.def, Census.host, "
            . "Variables.name";
        $firstRow = GCFG_GetVariableInfo(
            $site,
            0,
            0,
            0,
            constFirstName,
            $scop,
            $selSQL,
            '',
            0,
            1,
            0,
            $db
        );
        $lastRow = GCFG_GetVariableInfo(
            $site,
            0,
            0,
            0,
            constLastName,
            $scop,
            $selSQL,
            '',
            0,
            1,
            0,
            $db
        );
        $compRow = GCFG_GetVariableInfo(
            $site,
            0,
            0,
            0,
            constCompany,
            $scop,
            $selSQL,
            '',
            0,
            1,
            0,
            $db
        );
        $slevRow = GCFG_GetVariableInfo(
            $site,
            0,
            0,
            0,
            constServiceLevel,
            $scop,
            $selSQL,
            '',
            0,
            1,
            0,
            $db
        );

        $counter = 0;
        reset($firstRow);
        reset($lastRow);
        reset($compRow);
        reset($slevRow);
        foreach ($firstRow as $key => $iter) {
            $set[$counter] = $iter;
            $counter++;
        }
        foreach ($lastRow as $key => $iter) {
            $set[$counter] = $iter;
            $counter++;
        }
        foreach ($compRow as $key => $iter) {
            $set[$counter] = $iter;
            $counter++;
        }
        foreach ($slevRow as $key => $iter) {
            $set[$counter] = $iter;
            $counter++;
        }
    }
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $qv = safe_addslashes($row['valu']);
            $qn = safe_addslashes($row['name']);
            $qh = safe_addslashes($row['host']);
            switch ($qn) {
                case constFirstName:
                    $qt = 'fname';
                    break;
                case constLastName:
                    $qt = 'lname';
                    break;
                case constCompany:
                    $qt = 'comp';
                    break;
                case constServiceLevel:
                    $qt = 'slev';
                    break;
            }
            $sql = "update $name set\n"
                . " $qt = '$qv'\n"
                . " where host = '$qh'";
            $res = redcommand($sql, $db);
        }
    }
    $wrd = order($ord);
    $sql = "select * from $name\n"
        . " order by $wrd";
    $set = find_many($sql, $db);
    $n   = safe_count($set);
    if ($set) {
        site_totals(1, $n);

        $o    = "$self?action=name&cid=$cid&debug=$debug&ord";
        $wref = ($ord == 0) ?  "$o=1"  : "$o=0";
        $href = ($ord == 6) ?  "$o=7"  : "$o=6";
        $fref = ($ord == 14) ? "$o=15" : "$o=14";
        $lref = ($ord == 12) ? "$o=13" : "$o=12";

        $host = html_link($href, 'Machine');
        $when = html_link($wref, 'Latest Event Log');
        $fusr = html_link($fref, 'First Name');
        $lusr = html_link($lref, 'Last Name');
        $cusr = html_link($lref, 'Company');
        $susr = html_link($lref, 'Service Level');
        $act  = 'Action';
        if ($cnfg) {
            $conn = 'Connect';
            $head = array($conn, $act, $host, $when, $fusr, $lusr, $cusr, $susr);
        } else {
            $head = array($act, $host, $when, $fusr, $lusr, $cusr, $susr);
        }
        $cols = safe_count($head);
        $asset = asset_tree($env, $db);

        //      site_header($site,count($hosts));
        echo table_header();
        echo pretty_header($site, $cols);
        echo table_data($head, 1);
        reset($set);
        foreach ($set as $key => $row) {
            $action = '<br>';
            $mid  = $row['id'];
            $last = $row['last'];
            $host = disp($row, 'host');
            $fusr = disp($row, 'fname');
            $lusr = disp($row, 'lname');
            $cusr = disp($row, 'comp');
            $susr = disp($row, 'slev');
            $when = date("m/d H:i:s", $last);
            $aid  = @intval($asset[$site][$host]);
            $a = array();
            if ($comp['evnt']) $a[] = elink($mid);
            if ($aid > 0)      $a[] = alink($aid);
            $a[] = dlink($self, $mid);
            $a[] = mlink($mid);
            $act = ($a) ? join("<br>\n", $a) : '<br>';
            if ($cnfg) {
                $conn = connect_link($mid);
                $args = array($conn, $act, $host, $when, $fusr, $lusr, $cusr, $susr);
            } else {
                $args = array($act, $host, $when, $fusr, $lusr, $cusr, $susr);
            }
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        $msg = "No census information found.";
        $msg = fontspeak($msg);
        echo "$msg<br>\n";
    }
    delete_temp_table($name, $db);
    echo again($env);
}


function anounce_removal($host, $dbase, $num)
{
    if ($num > 0) {
        $count = plural($num, 'record');
        $msg = "census: $host: $count removed from $dbase.";
        logs::log(__FILE__, __LINE__, $msg, 0);
        $msg = "$count removed from database $dbase.";
        $msg = fontspeak($msg);
        echo "<p>$msg</p>\n";
    }
}


function count_config_machines($site, $db)
{
    $set = config_list($site, $db);
    return safe_count($set);
}


function expunge_site($site, $db)
{
    $asset  = purge_asset_site($site, $db);
    $provis = purge_provis_site($site, $db);
    $crypt  = purge_crypt_site($site, $db);
    $config = purge_config_site($site, $db);
    $update = purge_update_site($site, $db);
    $event  = purge_event_site($site, $db);
    $provis += $crypt;

    anounce_removal($site, 'asset', $asset);
    anounce_removal($site, 'core', $config);
    anounce_removal($site, 'swupdate', $update);
    anounce_removal($site, 'event', $event);
    anounce_removal($site, 'provision', $provis);
}


/*
    |  The config database is special in that
    |  removing the last machine from the site
    |  should also clear the Globals and the
    |  GlobalCache for that site.
    */

function expunge_host($mid, $site, $host, $db)
{
    $asset  = purge_asset_host($site, $host, $db);
    $crypt  = purge_crypt_host($site, $host, $db);
    $provis = purge_provis_host($site, $host, $db);
    $config = purge_config_host($mid, $site, $host, $db);
    $update = purge_update_host($site, $host, $db);
    $event  = purge_event_host($site, $host, $db);
    $provis += $crypt;
    if (count_config_machines($site, $db) == 0) {
        $provis += purge_provis_site($site, $db);
        $config += purge_config_site($site, $db);
    }
    anounce_removal($host, 'asset', $asset);
    anounce_removal($host, 'core', $config);
    anounce_removal($host, 'swupdate', $update);
    anounce_removal($host, 'event', $event);
    anounce_removal($host, 'provision', $provis);
}


function delete_host(&$env, $full, $db)
{
    $row    = array();
    $mid    = $env['mid'];
    $user   = $env['user'];
    $self   = $env['self'];
    $admin  = $env['admin'];
    $access = $env['access'];

    echo again($env);

    if ($mid > 0) {
        if ($admin) {
            $sql  = "select * from Census\n"
                . " where id = $mid";
            $row  = find_one($sql, $db);
        } else if ($access) {
            $sql = "select * from Census\n"
                . " where id = $mid and\n"
                . " site in ($access)";
            $row = find_one($sql, $db);
        }
    }
    if ($row) {
        $host = $row['host'];
        $site = $row['site'];
        debug_note("delete_host: mid:$mid, admin:$admin, host:$host");

        $usec = microtime();
        if ($full) {
            expunge_host($mid, $site, $host, $db);
            $act = 'expunged';
        } else {
            $crypt  = purge_crypt_host($site, $host, $db);
            $provis = purge_provis_host($site, $host, $db);
            $config = purge_config_host($mid, $site, $host, $db);
            $provis += $crypt;
            if (count_config_machines($site, $db) == 0) {
                $provis += purge_provis_site($site, $db);
                $config += purge_config_site($site, $db);
            }
            anounce_removal($host, 'core', $config);
            anounce_removal($host, 'provision', $provis);
            $act = 'removed';
        }

        purge_patch_host($mid, $db);
        purge_groups_host($mid, $db);

        $err = PHP_DSYN_InvalidateRow(
            CUR,
            (int)$mid,
            "id",
            constDataSetCoreCensus,
            constOperationPermanentDelete
        );
        if ($err != constAppNoErr) {
            logs::log(__FILE__, __LINE__, "delete_host: PHP_DSYN_InvalidateRow returned "
                . $err, 0);
        }
        $num = 0;
        if ($err == constAppNoErr) {
            $sql  = "delete from Census\n where id = $mid";
            $res  = redcommand($sql, $db);
            $num  = affected($res, $db);
        }
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        if ($num == 1) {
            groups_init($db, constGroupsInitFull);
            $txt = "census: $host $act from $site by $user in $secs.";
            $msg = "Machine <b>$host</b> has been removed from <b>$site</b>.";
        } else {
            $txt = "census: mysql error removing $host from $site by $user in $secs.";
            $msg = "Machine <b>$host</b> was not removed from <b>$site</b>.";
        }
        logs::log(__FILE__, __LINE__, $txt, 0);
    } else {
        $msg = "Machine <b>$mid</b> seems to have vanished.";
    }

    $census = html_link($self, 'census');
    $msg .= "<br><br>Back to $census.<br>";
    $msg  = fontspeak($msg);
    echo "$msg<br>\n";
    echo again($env);
}


/*
    |  The much requested delete site command.
    |
    |  What an appropriate thing for friday the 13th ...
    */

function delete_site(&$env, $full, $db)
{
    echo again($env);

    $admin = $env['admin'];
    $cid   = $env['cid'];
    $user  = $env['user'];
    $self  = $env['self'];
    $carr  = $env['carr'];
    $site  = @trim($carr[$cid]);
    if ($site) {
        debug_note("delete_site: cid:$cid, admin:$admin");

        $list = site_list($site, $db);
        purge_patch_list($list, $db);
        purge_groups_list($list, $db);

        if ($full) {
            expunge_site($site, $db);
            $msg = "census: $user expunged $site.";
        } else {
            $provis = purge_provis_site($site, $db);
            $crypt  = purge_crypt_site($site, $db);
            $config = purge_config_site($site, $db);
            $provis += $crypt;

            anounce_removal($site, 'core', $config);
            anounce_removal($site, 'provision', $provis);
            $msg = "census: $user removed $site.";
        }
        logs::log(__FILE__, __LINE__, $msg, 0);
        $qs  = safe_addslashes($site);

        $sql = "select id from Census where site='$qs'";
        $set = DSYN_DeleteSet(
            $sql,
            constDataSetCoreCensus,
            "id",
            "delete_site",
            0,
            1,
            constOperationPermanentDelete,
            $db
        );

        if ($set) {
            $sql = "delete from Census\n where site = '$qs'";
            $res = redcommand($sql, $db);
            if (affected($res, $db)) {
                groups_init($db, constGroupsInitFull);
            }
            $msg = "Site <b>$site</b> been removed.<br>";
        }
    } else {
        $msg = "Site $cid has vanished.";
    }
    $census = html_link($self, 'census');
    $msg .= "<br><br>Back to $census.<br>";
    $msg  = fontspeak($msg);
    echo "$msg<br>\n";
    echo again($env);
}


function confirm_site(&$env, $db)
{
    $admin = $env['admin'];
    $cid   = $env['cid'];
    $self  = $env['self'];
    $carr  = $env['carr'];
    $site  = @trim($carr[$cid]);
    debug_note("confirm_site: admin:$admin, cid:$cid");

    if ($site) {
        $msg     = "Please confirm removal of site <b>$site</b>.<br><br>\n";
        $act     = "$self?cid=$cid&action";
        $stop    = html_link($self, 'Stop');
        $delete  = html_link("$act=dsite", 'Delete');
        $expunge = html_link("$act=esite", 'Expunge');

        $txt = "These are your choices:<br><br>\n"
            . "$stop: This will return to the "
            . "census page without changing anything.<br>\nProbably a "
            . "good idea unless you are sure.<br><br>\n"
            . "$delete: This will remove the site from "
            . "the census table only.<br>\nThis has no effect on any "
            . "other tables.<br><br>\n"
            . "$expunge: This will remove the site from the census "
            . "and also<br>from all other tables.&nbsp;&nbsp;This is "
            . "irreversible.&nbsp;&nbsp;Not recommended<br>\n"
            . "for casual use.";
        $msg = $msg . $txt;
    } else {
        $msg = "Site $cid has vanished.";
    }

    $msg  = fontspeak($msg);
    echo "$msg<br>\n";
}


/*
    |  A normal account can now delete a machine
    |  but only an admin account can expunge it.
    |
    |  4-May-04:  Not any more ... anyone can expunge.
    */

function confirm_host(&$env, $db)
{
    $row    = array();
    $admin  = $env['admin'];
    $access = $env['access'];
    $mid    = $env['mid'];
    $self   = $env['self'];
    debug_note("confirm_host: admin:$admin, mid:$mid");
    if (($mid > 0) && ($admin)) {
        $sql = "select * from Census\n"
            . " where id = $mid";
        $row = find_one($sql, $db);
    }
    if ((!$row) && ($access) && ($mid > 0)) {
        $sql = "select * from Census\n"
            . " where id = $mid and\n"
            . " site in ($access)";
        $row = find_one($sql, $db);
    }
    if ($row) {
        $host    = $row['host'];
        $site    = $row['site'];
        $act     = "$self?mid=$mid&action";
        $stop    = html_link($self, 'Stop');
        $delete  = html_link("$act=del", 'Delete');
        $expunge = html_link("$act=exp", 'Expunge');

        /*
 |  Please confirm removal of machine $host from $site.
 |
 |  These are your choices:
 |
 |  Stop: This will return to the census page without changing anything.
 |  Probably a good idea unless you are sure.
 |
 |  Delete: This will remove the current machine from the census table
 |  only.  This has no effect on any other tables.
 |
 |  Expunge: This will remove the machine from the census, and also
 |  from all other tables.  This is irreversible.  Not recommended
 |  for casual use.
 |
 */


        $msg = "Please confirm removal of machine "
            . "<b>$host</b> from <b>$site</b>.<br><br>\n"
            . "These are your choices:<br><br>\n"
            . "$stop: This will return to the "
            . "census page without changing anything.<br>\nProbably a "
            . "good idea unless you are sure.<br><br>\n"
            . "$delete: This will remove the current machine from "
            . "the census table only.<br>\nThis has no effect on any "
            . "other tables.<br><br>\n"
            . "$expunge: This will remove the machine from the census "
            . "and also<br>from all other tables.&nbsp;&nbsp;This is "
            . "irreversible.&nbsp;&nbsp;Not recommended<br>\n"
            . "for casual use.";
    } else {
        $msg = "Machine $mid has vanished.";
    }
    $msg  = fontspeak($msg);
    echo "$msg<br>\n";
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



function debug_census(&$env, $db)
{
    $ord  = $env['ord'];
    $now  = $env['now'];
    $self = $env['self'];

    echo again($env);

    debug_note("debug_census: ord:$ord");

    $list  = array();
    $order = order($ord);
    $sql   = "select * from TempCensusCache\n"
        . " order by $order";
    $num   = 0;
    if ($env['priv_debug']) {
        $list = CORE_GetAllTempCensusCache($sql, $db);
        $num  = safe_count($list);
    }
    if ($list) {
        $o    = "$self?action=debug&ord";
        $wref = ($ord ==  0) ? "$o=1"  : "$o=0";
        $href = ($ord ==  2) ? "$o=3"  : "$o=2";
        $sref = ($ord ==  6) ? "$o=7"  : "$o=6";
        $iref = ($ord ==  8) ? "$o=9"  : "$o=8";
        $uref = ($ord == 10) ? "$o=11" : "$o=10";

        $id   = html_link($iref, 'Id');
        $age  = html_link($wref, 'Age');
        $host = html_link($href, 'Machine');
        $when = html_link($wref, 'When');
        $site = html_link($sref, 'Site');
        $uuid = html_link($uref, 'UUID');

        $head = array($age, $host, $site, $when, $id, $uuid);
        $rows = safe_count($list);
        $cols = safe_count($head);
        $text = "Machine Census &nbsp; ($rows found)";
        $kill = "$self?action=chost&mid";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($head, 1);

        reset($list);
        foreach ($list as $key => $row) {
            $id   = $row['id'];
            $last = $row['last'];
            CORE_GetCachedTime($last, $row['site'], $row['host']);
            $site = disp($row, 'site');
            $host = disp($row, 'host');
            $uuid = disp($row, 'uuid');
            $when = date("m/d H:i:s", $last);
            $age  = age($now - $last);
            $host = html_link("$kill=$id", $host);
            $args = array($age, $host, $site, $when, $id, $uuid);
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        $msg = "No census data found.";
        $msg = fontspeak($msg);
        echo "<p>$msg</p><br>\n";
    }

    echo again($env);
}




/*
    |  Main program
    */

$now      = time();
$db       = db_connect();
$authuser = process_login($db);
$comp     = component_installed();

$self   = server_var('PHP_SELF');
$user   = user_data($authuser, $db);

$filter      = @($user['filtersites']) ?    1 : 0;
$priv_debug  = @($user['priv_debug']) ?     1 : 0;
$priv_admin  = @($user['priv_admin']) ?     1 : 0;
$priv_asset  = @($user['priv_asset']) ?     1 : 0;
$priv_config = @($user['priv_config']) ?    1 : 0;
$priv_update = @($user['priv_updates']) ?   1 : 0;
$priv_dload  = @($user['priv_downloads']) ? 1 : 0;

$adm    = get_integer('admin', 1);
$dbg    = get_integer('debug', 0);

$admin  = ($priv_admin) ? $adm : 0;
$debug  = ($priv_debug) ? $dbg : 0;

$ord    = get_integer('ord', 6);
$wrd    = get_integer('wrd', 0);
$mid    = get_integer('mid', 0);
$cid    = get_integer('cid', 0);
$action = get_string('action', 'list');

/*
    |  For extreme commands, make debug on by default.
    |
    */

if (($priv_debug) && ($debug == 0)) {
    $def = get_integer('debug', 1) ? 1 : 0;
    switch ($action) {
        case 'del':
            $debug = $def;
            break;
        case 'exp':
            $debug = $def;
            break;
        case 'dsite':
            $debug = $def;
            break;
        case 'esite':
            $debug = $def;
            break;
        default:
            break;
    }
}


$row    = array();
$site   = '';
$carr   = site_array($authuser, $filter, $db);
$access = db_access($carr);


if ($cid > 0) {
    $site = @strval($carr[$cid]);
    $row  = find_site($cid, $authuser, $db);
}

$title = title($action, $site);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($debug) {
    echo ident(time());
}

$env = array();
$env['priv_debug']  = $priv_debug;
$env['priv_admin']  = $priv_admin;
$env['priv_asset']  = $priv_asset;
$env['priv_dload']  = $priv_dload;
$env['priv_config'] = $priv_config;
$env['priv_update'] = $priv_update;
$env['action'] = $action;
$env['access'] = $access;
$env['filter'] = $filter;
$env['admin']  = $admin;
$env['debug']  = $debug;
$env['comp']   = $comp;
$env['carr']   = $carr;
$env['self']   = $self;
$env['user']   = $authuser;
$env['now']    = $now;
$env['row']    = $row;
$env['mid']    = $mid;
$env['cid']    = $cid;
$env['ord']    = $ord;
$env['wrd']    = $wrd;

switch ($action) {
    case 'list':
        list_sites($env, $db);
        break;
    case 'del':
        delete_host($env, 0, $db);
        break;
    case 'exp':
        delete_host($env, 1, $db);
        break;
    case 'site':
        single_census($env, $db);
        break;
    case 'name':
        name_census($env, $db);
        break;
    case 'chost':
        confirm_host($env, $db);
        break;
    case 'csite':
        confirm_site($env, $db);
        break;
    case 'dsite':
        delete_site($env, 0, $db);
        break;
    case 'esite':
        delete_site($env, 1, $db);
        break;
    case 'debug':
        debug_census($env, $db);
        break;
    default:
        unknown_option($action, $db);
        break;
}
echo head_standard_html_footer($authuser, $db);
