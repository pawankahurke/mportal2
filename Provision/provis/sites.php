<?php

/*
Revision history:

Date        Who     What
----        ---     ----
29-Oct-03   EWB     Created.
11-Nov-03   EWB     Calculate list of sites.
12-Nov-03   EWB     Implement site actions.
13-Nov-03   EWB     Local & Global Site variables
 5-Dec-03   EWB     Machines & Products
 9-Dec-03   EWB     Edit Site Products
15-Dec-03   EWB     Create Local / Remove Local
16-Dec-03   EWB     Edit Machine Products
17-Dec-03   EWB     Check Machine Products
19-Dec-03   EWB     Unified site/host checkbox code.
19-Dec-03   EWB     Unified site/host update code.
 9-Feb-04   EWB     New site/machine assigment tables.
10-Feb-04   EWB     mostly rewritten.
11-Feb-04   EWB     warn users about removing products they do not own.
19-Feb-04   EWB     create fake site command.
26-Feb-04   EWB     removed extra argument in meter product list.
26-Feb-04   EWB     don't update levels unless value has really changed.
 8-Mar-04   EWB     Titles change
 8-Mar-04   EWB     priv_provis
10-Mar-04   EWB     Add/Delete Site/Machine Product Complete
11-Mar-04   EWB     Manage Site Products Complete
11-Mar-04   EWB     Create/Revert Local Complete.
12-Mar-04   EWB     Add Override Product Complete
12-Mar-04   EWB     Delete Override Product Complete
15-Mar-04   EWB     Don't allow duplicate assignments.
15-Mar-04   EWB     Reports Success/Failure on checkbox changes.
15-Mar-04   EWB     List local machines on checkbox update.
22-Mar-04   EWB     Added provisional update time to config census.
23-Mar-04   EWB     Removed the 'Create Local Complete' page.
23-Mar-04   EWB     Special Message for no-change update.
 7-Apr-04   EWB     sort by sites/number of machines
21-Apr-04   EWB     Mark the checksum cache invalid when updating machine.
22-Apr-04   EWB     Always show machine product enabled checkbox.
18-Jan-05   AAM     Wording changes as per Alex.
10-May-05   EWB     Uses new database.
12-Sep-05   BTE     Added checksum invalidation code.
12-Oct-05   BTE     Changed references from gconfig to core.
24-Oct-05   BTE     Handle ValueMap.revl in set_state.
03-Nov-05   BTE     Changed VarValues.* statements to explicit columns.
05-Nov-05   BTE     Specify the operation when calling checksum invalidation
                    code.
13-Dec-05   BJS     find_site_mgrp -> GCFG_find_site_mgrp()
15-Dec-05   BTE     Removed unused local_product, removed unused columns from
                    some SQL statements.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
27-Apr-06   BTE     Bug 3292: Add group assignment reset function.
01-May-06   BTE     Bug 3355: Scrip Config Status Page: Various Changes.
19-Jul-06   BTE     Bug 3239: 913 errors on CI server.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title  = 'Sites';

ob_start();
include('../lib/l-cnst.php');
include('../lib/l-util.php');
include('../lib/l-rlib.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-slct.php');
include('../lib/l-user.php');
include('../lib/l-jump.php');
include('../lib/l-slav.php');
include('../lib/l-head.php');
include('../lib/l-csum.php');
include('../lib/l-tabs.php');
include('../lib/l-gcfg.php');
include('local.php');
include('../lib/l-prov.php');
include('../lib/l-errs.php');
include('../lib/l-dsyn.php');

function build_title($act, $site)
{
    switch ($act) {
        case 'amp':
            return 'Add Machine Product';
        case 'asp':
            return 'Add Site Product';
        case 'ccl':
            return 'Confirm Create Local';
        case 'cen':
            return 'Config Census';
        case 'cmp':
            return 'Manage Machine Products Complete';
        case 'crp':
            return 'Debug Crypt Keys';
        case 'csp':
            return 'Manage Site Products Complete';
        case 'dmp':
            return 'Delete Machine Product';
        case 'dsp':
            return 'Delete Site Product';
        case 'cl':; // return 'Create Local Complete';
        case 'emp':
            return 'Manage Machine Products';
        case 'esp':
            return 'Manage Site Products';
        case 'fak':
            return 'Fake Site';
        case 'hst':
            return 'Debug Host Assignments';
        case 'key':
            return 'Debug Host Keys';
        case 'lst':
            return 'Provisioning - Sites';
        case 'mch':
            return "$site Machines";
        case 'pmp':
            return 'Delete Machine Product Complete';
        case 'psp':
            return 'Delete Site Product Complete';
        case 'pop':
            return 'Delete Local Product Complete';
        case 'rl':
            return 'Revert Local Complete';
        case 'rsp':
            return 'Revert System Configuration to Site Settings';
        case 'sit':
            return 'Debug Site Assignments';
        case 'ump':
            return 'Add Machine Product Complete';
        case 'usp':
            return 'Add Site Product Complete';
        case 'uop':
            return 'Add Local Product Complete';
        case 'vmp':
            return 'View Machine Products';
        case 'vsp':
            return 'View Site Products';
        case 'xxx':
            return 'One Time Only';
        default:
            return 'Unknown Sites';
    }
}


function bool($x)
{
    return ($x > 0) ? 'Yes' : 'No';
}


function para($x)
{
    return "<p>$x</p>\n";
}


function audit_action(&$env, $host, $name, $act, $db)
{
    $auth = $env['auth'];
    $site = $env['revl']['site'];
    $qp  = safe_addslashes($name);
    $qh  = safe_addslashes($host);
    $qu  = safe_addslashes($auth);
    $qs  = safe_addslashes($site);
    $qa  = safe_addslashes($act);
    $now = time();
    $sql = "insert into " . $GLOBALS['PREFIX'] . "provision.Audit set\n"
        . " who = 1,\n"
        . " servertime = $now,\n"
        . " clienttime = $now,\n"
        . " sitename = '$qs',\n"
        . " product = '$qp',\n"
        . " machine = '$qh',\n"
        . " owner = '$qu',\n"
        . " username = '$qu',\n"
        . " action = '$qa'";
    $res = redcommand($sql, $db);
    return affected($res, $db);
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
    if ($env['act'] != 'lst') {
        $a[] = html_link($self, 'sites');
    }
    if ($priv) {
        $home = '../acct/index.php';
        $a[] = html_link($home, 'home');
        $a[] = html_link($href, 'again');
        $a[] = html_link("$act=cen", 'census');
        $a[] = html_link("$act=crp", 'crypt');
        $a[] = html_link("$act=fak", 'fake');
    }
    return jumplist($a);
}

function mark_site($site)
{
    echo "<h2>$site</h2>\n";
}


function mark_host($site, $host)
{
    $name = ucwords($host);
    echo "<h2>$name at $site</h2>\n";
}

function table_span($cols, $text)
{
    $text = "<td colspan=\"$cols\">$text</td>";
    echo "<tr>$text</tr>\n";
}

function red($txt)
{
    return "<font color=\"red\">$txt</font>";
}



function find_any_revl($site, $host, $db)
{
    $qs  = safe_addslashes($site);
    $qh  = safe_addslashes($host);
    $sql = "select C.host, C.site,\n"
        . " C.uuid, R.* from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Revisions as R,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
        . " where C.id = R.censusid\n"
        . " and C.site = '$qs'\n"
        . " and C.host = '$qh'";
    return find_one($sql, $db);
}

function find_any_machine($hid, $db)
{
    $rvl = array();
    if ($hid > 0) {
        $sql = "select C.site, C.host,\n"
            . " C.uuid, R.* from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R\n"
            . " where C.id = $hid\n"
            . " and C.id = R.censusid";
        $rvl = find_one($sql, $db);
    }
    return $rvl;
}


function find_machine($hid, $auth, $db)
{
    $row = array();
    if (($auth) && ($hid)) {
        $qa  = safe_addslashes($auth);
        $sql = "select C.site, C.host,\n"
            . " C.uuid, R.*, U.id as cid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R\n"
            . " where R.censusid = $hid\n"
            . " and C.id = R.censusid\n"
            . " and C.site = U.customer\n"
            . " and U.username = '$qa'";
        $row = find_one($sql, $db);
    }
    return $row;
}


function insert_host($pid, $qs, $qh, $qu, $prov, $enab, $metr, $db)
{
    $sql = "insert into\n"
        . " " . $GLOBALS['PREFIX'] . "provision.MachineAssignments set\n"
        . " productid=$pid,\n"
        . " sitename='$qs',\n"
        . " machine='$qh',\n"
        . " uuid='$qu',\n"
        . " enabled=$enab,\n"
        . " metered=$metr,\n"
        . " provisioned=$prov";
    $res = redcommand($sql, $db);
    return affected($res, $db);
}


function assign_host($revl, $prod, $prov, $db)
{
    $num = 0;
    if (($revl) && ($prod)) {
        $pid  = ($prod['productid']);
        $enab = ($prod['defaultenable']) ?  1 : 0;
        $metr = ($prod['defaultmonitor']) ? 1 : 0;
        $prov = ($prov) ? 1 : 0;

        $qs  = safe_addslashes($revl['site']);
        $qh  = safe_addslashes($revl['host']);
        $qu  = safe_addslashes($revl['uuid']);
        $num = insert_host($pid, $qs, $qh, $qu, $prov, $enab, $metr, $db);
    }
    return $num;
}


function remove_host($site, $host, $pid, $db)
{
    $num = 0;
    if (($site) && ($host) && ($pid > 0)) {
        $qs  = safe_addslashes($site);
        $qh  = safe_addslashes($host);
        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "provision.MachineAssignments\n"
            . " where productid = $pid\n"
            . " and machine = '$qh'\n"
            . " and sitename='$qs'";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}

function remove_site($revl, $pid, $db)
{
    $num = 0;
    if (($revl) && ($pid > 0)) {
        $qs  = safe_addslashes($revl['site']);
        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "provision.SiteAssignments\n"
            . " where sitename = '$qs'\n"
            . " and productid = $pid";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}


function assign_site($revl, $prod, $prov, $db)
{
    $num = 0;
    if (($revl) && ($prod)) {
        $qs   = safe_addslashes($revl['site']);
        $pid  = $prod['productid'];
        $enab = ($prod['defaultenable']) ?  1 : 0;
        $metr = ($prod['defaultmonitor']) ? 1 : 0;
        $prov = ($prov) ? 1 : 0;
        $sql  = "insert into\n"
            . " " . $GLOBALS['PREFIX'] . "provision.SiteAssignments set\n"
            . " productid=$pid,\n"
            . " sitename='$qs',\n"
            . " enabled=$enab,\n"
            . " metered=$metr,\n"
            . " provisioned=$prov";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}

function find_site_product($revl, $prod, $db)
{
    $res = array();
    if (($revl) && ($prod)) {
        $qs  = safe_addslashes($revl['site']);
        $pid = $prod['productid'];
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "provision.SiteAssignments\n"
            . " where productid = $pid\n"
            . " and sitename='$qs'";
        $res = find_many($sql, $db);
    }
    return $res;
}

function find_host_product($revl, $prod, $db)
{
    $res = array();
    if (($revl) && ($prod)) {
        $qh  = safe_addslashes($revl['host']);
        $qs  = safe_addslashes($revl['site']);
        $pid = $prod['productid'];
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "provision.MachineAssignments\n"
            . " where productid = $pid\n"
            . " and sitename = '$qs'\n"
            . " and machine = '$qh'";
        $res = find_many($sql, $db);
    }
    return $res;
}

function order($ord)
{
    switch ($ord) {
        case  0:
            return 'site, host, censusid desc';
        case  1:
            return 'site desc, host desc, censusid desc';
        case  2:
            return 'host, censusid';
        case  3:
            return 'host desc, censusid';
        case  4:
            return 'ctime desc, censusid';
        case  5:
            return 'ctime, censusid';
        case  6:
            return 'censusid';
        case  7:
            return 'censusid desc';
        default:
            return order(0);
    }
}



function find_config_sites($carr, $ord, $db)
{
    $sites = array();
    if ($carr) {
        $order  = order($ord);
        $access = db_access($carr);
        $sql = "select C.site, C.host,\n"
            . " C.uuid. R.* from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R\n"
            . " where C.id = $hid\n"
            . " and C.id = R.censusid\n"
            . " and C.site in ($access)\n"
            . " order by $order";
        $res = find_many($sql, $db);
        if ($res) {
            foreach ($res as $key => $row) {
                $id   = $row['censusid'];
                $site = $row['site'];
                $sites[$site] = $id;
            }
        }
        return $sites;
    }
}


function user_products($auth, $db)
{
    $qu   = safe_addslashes($auth);
    $sql  = "select * from " . $GLOBALS['PREFIX'] . "provision.Products\n"
        . " where global = 1\n"
        . " or username = '$qu'\n"
        . " order by prodname";
    return find_many($sql, $db);
}


function find_product_names($auth, $db)
{
    $list = array();
    $prds = user_products($auth, $db);

    if ($prds) {
        $list[0] = '     ';
        reset($prds);
        foreach ($prds as $key => $row) {
            $name = $row['prodname'];
            $id   = $row['productid'];
            $list[$id] = $name;
        }
    }
    return $list;
}


function find_product($pid, $auth, $db)
{
    $row = array();
    if ($pid > 0) {
        $qu  = safe_addslashes($auth);
        $sql = "select * from " . $GLOBALS['PREFIX'] . "provision.Products\n"
            . " where productid = $pid and\n"
            . " ((global = 1) or (username = '$qu'))";
        $row = find_one($sql, $db);
    }
    return $row;
}

function find_any_product($pid, $db)
{
    $row = array();
    if ($pid > 0) {
        $sql  = "select * from"
            . " " . $GLOBALS['PREFIX'] . "provision.Products\n"
            . " where productid = $pid";
        $row  = find_one($sql, $db);
    }
    return $row;
}


function distinct($many, $field)
{
    $list = array();
    if ($many) {
        reset($many);
        foreach ($many as $key => $row) {
            $list[] = $row[$field];
        }
    }
    return $list;
}


function legal_sites(&$env, $db)
{
    $legal = array();
    $list  = array();
    $carr  = $env['carr'];
    if ($carr) {
        reset($carr);
        foreach ($carr as $key => $site) {
            $legal[$site] = false;
        }
        $access = db_access($carr);
        $qp  = safe_addslashes(constProductList);
        $ps  = constProvisScope;
        $ms  = constMeterScope;
        $sql = "select distinct G.name from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Variables as V,\n"
            . " " . $GLOBALS['PREFIX'] . "core.VarValues as X,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
            . " where V.scop in ($ms,$ps)\n"
            . " and V.name = '$qp'\n"
            . " and X.varuniq = V.varuniq\n"
            . " and X.mgroupuniq = G.mgroupuniq\n"
            . " and G.name in ($access)\n"
            . " order by G.name";
        $set = find_many($sql, $db);
        $list = distinct($set, 'name');
    }
    if ($list) {
        reset($list);
        foreach ($list as $key => $site) {
            $legal[$site] = true;
        }
    }
    return $legal;
}


function worder($wrd)
{
    switch ($wrd) {
        case  0:
            return 'site';
        case  1:
            return 'site desc';
        case  2:
            return 'number desc, site';
        case  3:
            return 'number, site';
        default:
            return worder(0);
    }
}


function plural($n, $name)
{
    $word = ($n == 1) ? $name : $name . 's';
    return "$n $word";
}


function site_totals($sites, $hosts)
{
    echo table_header();
    echo pretty_header('Total', 2);
    echo two_col('Sites:', $sites);
    echo two_col('Machines:', $hosts);
    echo table_footer();
    echo "<br>\n";
}


function list_sites(&$env, $db)
{
    echo again($env);
    $ord  = $env['ord'];
    $wrd  = $env['wrd'];
    $pprv = $env['pprv'];
    $carr = $env['carr'];
    $list = array();
    if ($carr) {
        $wrds = worder($wrd);
        $txt  = db_access($carr);
        $sql  = "select C.site,\n"
            . " count(C.site) as number,\n"
            . " min(R.censusid) as mid from\n"
            . " Revisions as R,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
            . " where C.site in ($txt)\n"
            . " and R.censusid = C.id\n"
            . " group by site\n"
            . " order by $wrds";
        $list = find_many($sql, $db);
    }
    $num = safe_count($list);

    if ($num) {
        $self  = $env['self'];
        $legal = legal_sites($env, $db);
        $none  = "&nbsp;&nbsp;(not enabled)&nbsp;&nbsp;";

        $n = 0;
        reset($list);
        foreach ($list as $key => $row) {
            $n += $row['number'];
        }

        site_totals($num, $n);

        $w    = "$self?wrd";
        $sref = ($wrd == 0) ? "$w=1" : "$w=0";
        $nref = ($wrd == 2) ? "$w=3" : "$w=2";
        $sdef = html_link($sref, 'Site Name');
        $ndef = html_link($nref, 'Number of Machines');
        $head = array('Action', $sdef, $ndef);
        $cmd  = ($pprv) ? 'esp'  : 'vsp';

        echo table_header();
        //    echo pretty_header('Provision Sites',3);
        echo table_data($head, 1);

        reset($list);
        foreach ($list as $key => $row) {
            $id   = $row['mid'];
            $num  = $row['number'];
            $site = $row['site'];
            $act  = "$self?id=$id&act";
            $mach = html_link("$act=mch", '[machines]');
            $prod = html_link("$act=$cmd", '[manage products]');
            $acts = ($legal[$site]) ? "$mach<br>$prod" : $none;
            $text = plural($num, 'machine');
            $args = array($acts, $site, $text);
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        echo "There were no sites found.<br>\n";
    }
    echo again($env);
}


function local_host($revl, $db)
{
    $ps  = constProvisScope;
    $ms  = constMeterScope;
    $qs  = safe_addslashes($revl['site']);
    $qp  = safe_addslashes(constProductList);
    $sql = "select distinct host from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.ValueMap as M,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Variables as V,\n"
        . " where M.stat = 1\n"
        . " and M.censusuniq = C.censusuniq\n"
        . " and M.varuniq = V.varuniq\n"
        . " and C.site = '$qs'\n"
        . " and V.name = '$qp'\n"
        . " and V.scop in ($ms,$ps)\n"
        . " order by host";
    $set = find_many($sql, $db);
    return distinct($set, 'host');
}


function product_record($gid, $scop, $db)
{
    return find_valu($gid, constProductList, $scop, $db);
}


function site_globals($sgrp, $db)
{
    $set = array();
    if ($sgrp) {
        $gid = $sgrp['mgroupid'];
        $qp  = safe_addslashes(constProductList);
        $ps  = constProvisScope;
        $ms  = constMeterScope;
        $sql = "select 1 from\n"
            . " " . $GLOBALS['PREFIX'] . "core.VarValues as X,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Variables as V,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
            . " where V.name = '$qp'\n"
            . " and V.scop in ($ps,$ms)\n"
            . " and X.varuniq = V.varuniq\n"
            . " and X.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid = $gid";
        $set = find_many($sql, $db);
    }
    return $set;
}


function site_locals($site, $db)
{
    $qs  = safe_addslashes($site);
    $qp  = safe_addslashes(constProductList);
    $ps  = constProvisScope;
    $ms  = constMeterScope;
    $loc = constVarConfStateLocal;
    $sql = "select C.host from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.ValueMap as M,\n"
        . " " . $GLOBALS['PREFIX'] . "core.VarValues as X,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Variables as V\n"
        . " where M.stat = $loc\n"
        . " and V.scop in ($ps,$ms)\n"
        . " and V.name = '$qp'\n"
        . " and C.site = '$qs'\n"
        . " and M.censusuniq = C.censusuniq\n"
        . " and M.varuniq = V.varuniq\n"
        . " and M.varuniq = X.varuniq\n"
        . " and M.mgroupuniq = X.mgroupuniq";
    return find_many($sql, $db);
}


function host_locals($site, $host, $db)
{
    $qs  = safe_addslashes($site);
    $qh  = safe_addslashes($host);
    $qp  = safe_addslashes(constProductList);
    $ps  = constProvisScope;
    $ms  = constMeterScope;
    $loc = constVarConfStateLocal;
    $sql = "select 1 from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.ValueMap as M,\n"
        . " " . $GLOBALS['PREFIX'] . "core.VarValues as X,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Variables as V\n"
        . " where M.stat = $loc\n"
        . " and V.scop in ($ps,$ms)\n"
        . " and V.name = '$qp'\n"
        . " and C.site = '$qs'\n"
        . " and C.host = '$qh'\n"
        . " and M.censusuniq = C.censusuniq\n"
        . " and M.varuniq = V.varuniq\n"
        . " and M.varuniq = X.varuniq\n"
        . " and M.mgroupuniq = X.mgroupuniq";
    return find_many($sql, $db);
}




/*
    |  Create a list of machines at $site that
    |  have local overrides and assign product $pid.
    */

function over_product($site, $pid, $db)
{
    $qs  = safe_addslashes($site);
    $sql = "select distinct machine from\n"
        . " " . $GLOBALS['PREFIX'] . "provision.MachineAssignments\n"
        . " where productid = $pid\n"
        . " and sitename = '$qs'\n"
        . " order by machine";
    $res = find_many($sql, $db);
    return distinct($res, 'machine');
}

/*
    |  Create a list of machines at $site that
    |  have local overrides but do not assign
    |  product $pid.
    */

function over_missing($site, $pid, $db)
{
    $qs  = safe_addslashes($site);
    $res = over_product($site, $pid, $db);
    $sql = "select distinct machine from\n"
        . " " . $GLOBALS['PREFIX'] . "provision.MachineAssignments\n"
        . " where sitename = '$qs'";
    if ($res) {
        $txt  = db_access($res);
        $sql .= "\n and machine not in ($txt)";
    }
    $sql .= "\n order by machine";
    $res = find_many($sql, $db);
    return distinct($res, 'machine');
}


function list_table($pids, $prds, $db)
{
    $list = array();
    $prod = product_pids($pids, $db);
    reset($prds);
    foreach ($prds as $pid => $row) {
        $list[$pid]['name']  = $prod[$pid]['prodname'];
        $list[$pid]['user']  = $prod[$pid]['username'];
        $list[$pid]['glob']  = $prod[$pid]['global'];
        $list[$pid]['prod']  = $prod[$pid];
        $list[$pid]['mprod'] = $row['metered'];
        $list[$pid]['pprod'] = $row['provisioned'];
        $list[$pid]['penab'] = $row['enabled'];
    }
    return $list;
}



function site_table($env, $revl, $db)
{
    $site = $revl['site'];
    debug_note("site table: $site");
    $pids = array();
    $prds = array();
    site_pids($site, $pids, $prds, $db);
    return list_table($pids, $prds, $db);
}


function host_table($env, $revl, $db)
{
    $host = $revl['host'];
    $site = $revl['site'];
    debug_note("host table: <b>$host</b> at <b>$site</b>.");
    $pids = array();
    $prds = array();
    host_pids($site, $host, $pids, $prds, $db);
    return list_table($pids, $prds, $db);
}


// called indirectly from sort_table

function reverse($a, $b)
{
    return strcasecmp($b, $a);
}


function sort_table(&$table, $ord)
{
    $tmp   = $table;
    $names = array();

    reset($table);
    foreach ($table as $pid => $row) {
        $name = $row['name'];
        $names[$name] = $pid;
    }
    $cmp = ($ord) ? 'reverse' : 'strcasecmp';
    uksort($names, $cmp);

    $tmp = array();

    reset($names);
    foreach ($names as $name => $pid) {
        $tmp[$pid] = $table[$pid];
    }
    $table = $tmp;
}


function list_machines(&$env, $db)
{
    echo again($env);
    $id   = $env['hid'];
    $ord  = $env['ord'];
    $self = $env['self'];
    $pprv = $env['pprv'];
    $site = $env['revl']['site'];
    $list = array();
    if ($site) {
        debug_note("site: $site");

        $txt  = 'Manage Site Products';
        $act  = "$self?id=$id&act";
        $edit = ($pprv) ? 'esp' : 'vsp';
        $link = html_link("$act=$edit", $txt);
        echo  "<br><br>$link.<br><br>\n";
        $ords = order($ord);
        $qs   = safe_addslashes($site);
        $qa   = safe_addslashes($env['auth']);
        $sql  = "select C.host, C.site, R.* from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U\n"
            . " where C.site = '$qs'\n"
            . " and U.customer = C.site\n"
            . " and U.username = '$qa'\n"
            . " and C.id = R.censusid\n"
            . " order by $ords";
        $list = find_many($sql, $db);
    }
    if ($list) {
        $num = safe_count($list);
        site_totals(1, $num);
        $temp = array();
        $locl = site_locals($site, $db);

        reset($list);
        foreach ($list as $key => $row) {
            $host = $row['host'];
            $temp[$host] = false;
        }

        reset($locl);
        foreach ($locl as $key => $row) {
            $host = $row['host'];
            $temp[$host] = true;
        }
        $act  = "$self?act=mch&id=$id&ord";
        $href = ($ord) ? "$act=0" : "$act=1";
        $link = html_link($href, 'Machine name');
        $head = array('Action', $link, 'Local settings exist');

        echo table_header();
        echo pretty_header($site, 4);
        echo table_data($head, 1);
        reset($list);
        foreach ($list as $key => $row) {
            $mid  = $row['censusid'];
            $host = $row['host'];
            $site = $row['site'];
            $act  = "$self?id=$mid&act";
            $a    = array();
            if ($temp[$host]) {
                $exist = 'Yes';
                if ($pprv) {
                    $a[] = html_link("$act=emp", '[edit]');
                    $a[] = html_link("$act=rsp", '[delete]');
                } else {
                    $a[] = html_link("$act=vmp", '[view]');
                }
            } else {
                $exist = 'No';
                if ($pprv) {
                    $a[] = html_link("$act=ccl", '[manage]');
                } else {
                    $a[] = ' (none) ';
                }
            }
            $acts = join('<br>', $a);
            $args = array($acts, $host, $exist);
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        $text = ($site) ? "No machines at $site." : 'No site found.';
        $text = fontspeak($text);
        echo "$text<br>\n\n";
    }

    echo again($env);
}


function edit_site_products(&$env, $db)
{
    echo again($env);
    $list = array();
    $glob = array();
    $auth = $env['auth'];
    $self = $env['self'];
    $ord  = $env['ord'];
    $id   = $env['hid'];
    $revl = $env['revl'];

    $table = array();
    if ($revl) {
        $site = $env['revl']['site'];
        //        mark_site($site);
        $sgrp  = GCFG_find_site_mgrp($site, $db);
        $glob  = site_globals($sgrp, $db);
        $table = site_table($env, $revl, $db);
    }

    if ($glob) {
        $href = "$self?id=$id&act=asp";
        $add  = 'Add a new site product';
        $link = html_link($href, $add);
        echo "$link.<br><br>\n\n";

        $href = "$self?id=$id&act=mch";
        $mach = "$site Machines";
        $link = html_link($href, $mach);
        echo "$link.<br><br>\n\n";
    }

    if ($table) {
        sort_table($table, $ord);

        $update = button('Update');

        echo post_self();
        echo hidden('act', 'csp');
        echo hidden('id', $id);

        $rev  = ($ord) ? 0 : 1;
        $href = "$self?id=$id&ord=$rev&act=esp";
        $link = html_link($href, 'Product name');
        $head = explode('|', "$link|Provisioned|Enabled|Metered|Action");
        $cols = safe_count($head);

        echo para($update);

        echo table_header();
        echo pretty_header($site, $cols);
        echo table_data($head, 1);

        reset($table);
        foreach ($table as $pid => $row) {
            $name   = $row['name'];
            $pprod  = $row['pprod'];
            $mprod  = $row['mprod'];
            $penab  = $row['penab'];

            $pchk = checkbox("pprod_$pid", $pprod);
            $mchk = checkbox("mprod_$pid", $mprod);
            $echk = checkbox("penab_$pid", $penab);

            $href = "$self?id=$id&pid=$pid&act=dsp";
            $acts = html_link($href, '[delete]');
            $args = array($name, $pchk, $echk, $mchk, $acts);
            echo table_data($args, 0);
        }
        echo table_footer();

        echo para($update);

        echo form_footer();
    } else {
        if ($glob) {
            echo "<br>\n"
                . "There are no site-wide settings for<br>\n"
                . "provisioning or metering at this site.<br>\n";
        }
    }

    if (($site) && (!$glob)) {
        missing_site_variables($site);
    }
    echo again($env);
}



function view_site_products(&$env, $db)
{
    echo again($env);
    $list = array();
    $glob = array();
    $revl = $env['revl'];
    $site = $env['revl']['site'];
    $auth = $env['auth'];
    $self = $env['self'];
    $ord  = $env['ord'];
    $id   = $env['hid'];

    $table = array();
    if ($revl) {
        //        mark_site($site);
        $sgrp  = GCFG_find_site_mgrp($site, $db);
        $glob  = site_globals($sgrp, $db);
        $table = site_table($env, $revl, $db);
    }

    if ($table) {
        $href = "$self?id=$id&act=mch";
        $mach = "$site Machines";
        $link = html_link($href, $mach);
        echo "$link.<br><br>\n\n";

        sort_table($table, $ord);

        $done = button('Done');

        echo post_self();
        echo hidden('act', 'lst');

        $rev  = ($ord) ? 0 : 1;
        $href = "$self?id=$id&ord=$rev&act=vsp";
        $link = html_link($href, 'Product name');
        $head = explode('|', "$link|Provisioned|Enabled|Metered");

        echo para($done);

        echo table_header();
        echo pretty_header($site, 5);
        echo table_data($head, 1);

        reset($table);
        foreach ($table as $pid => $row) {
            $name  = $row['name'];
            $pprod = $row['pprod'];
            $mprod = $row['mprod'];
            $penab = $row['penab'];

            $enab = ($pprod) ? bool($penab) : '<br>';
            $prov = bool($pprod);
            $metr = bool($mprod);
            $args = array($name, $prov, $enab, $metr);
            echo table_data($args, 0);
        }
        echo table_footer();
        echo para($done);
        echo form_footer();
    } else {
        echo "<br>\n"
            . "There are no site-wide settings for<br>\n"
            . "provisioning or metering at this site.<br>\n";
    }

    if (($site) && (!$glob)) {
        missing_site_variables($site);
    }
    echo again($env);
}



function toggle_site_products(&$env, &$revl, &$table, $db)
{
    $changes = 0;
    if (($table) && ($revl)) {
        $qs = safe_addslashes($revl['site']);

        reset($table);
        foreach ($table as $pid => $row) {
            $metr  = $row['mprod'];
            $prov  = $row['pprod'];
            $enab  = $row['penab'];
            $mprod = get_integer("mprod_$pid", 0);
            $pprod = get_integer("pprod_$pid", 0);
            $penab = get_integer("penab_$pid", 0);
            if (($mprod != $metr) || ($pprod != $prov) || ($penab != $enab)) {
                $sql = "update " . $GLOBALS['PREFIX'] . "provision.SiteAssignments set\n"
                    . " provisioned = $pprod,\n"
                    . " metered = $mprod,\n"
                    . " enabled = $penab\n"
                    . " where productid = $pid and\n"
                    . " sitename = '$qs'";
                $res = redcommand($sql, $db);
                $num = affected($res, $db);

                if ($num) {
                    $table[$pid]['mprod'] = $mprod;
                    $table[$pid]['pprod'] = $pprod;
                    $table[$pid]['penab'] = $penab;
                    $changes += $num;
                }
            }
        }
    }
    return $changes;
}


function toggle_host_products(&$env, &$revl, &$table, $db)
{
    $changes = 0;
    if (($table) && ($revl)) {
        $qs = safe_addslashes($revl['site']);
        $qh = safe_addslashes($revl['host']);

        reset($table);
        foreach ($table as $pid => $row) {
            $metr  = $row['mprod'];
            $prov  = $row['pprod'];
            $enab  = $row['penab'];
            $mprod = get_integer("mprod_$pid", 0);
            $pprod = get_integer("pprod_$pid", 0);
            $penab = get_integer("penab_$pid", 0);
            if (($mprod != $metr) || ($pprod != $prov) || ($penab != $enab)) {
                $sql = "update " . $GLOBALS['PREFIX'] . "provision.MachineAssignments set\n"
                    . " provisioned = $pprod,\n"
                    . " metered = $mprod,\n"
                    . " enabled = $penab\n"
                    . " where productid = $pid and\n"
                    . " sitename = '$qs' and\n"
                    . " machine = '$qh'";
                $res = redcommand($sql, $db);
                $num = affected($res, $db);
                if ($num) {
                    $changes += $num;
                    $table[$pid]['mprod'] = $mprod;
                    $table[$pid]['pprod'] = $pprod;
                    $table[$pid]['penab'] = $penab;
                }
            }
        }
    }
    return $changes;
}


function list_local_host($revl, $db)
{
    $list = local_host($revl, $db);
    $site = $revl['site'];
    if (($list) && ($site)) {

        // Note that some machines at this site have a local
        // machine configuration, and are therefore unaffected
        // by any changes to the site wide configuration.

        echo "<p>Note that some machines at this site"
            .  " have a local<br>machine configuration,\n"
            .  " and are therefore unaffected<br> by any changes\n"
            .  " to the site wide configuration.</p>\n";
        reset($list);
        $head = explode('|', 'Site|Machine');
        echo table_header();
        echo pretty_header('Local Machines', 2);
        echo table_data($head, 1);
        reset($list);
        foreach ($list as $key => $host) {
            $args = array($site, $host);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}



function check_site_products(&$env, $db)
{
    echo again($env);
    $revl = $env['revl'];
    $good = false;
    if ($revl) {
        $site  = $revl['site'];
        $table = site_table($env, $revl, $db);
        $delta = 0;
        if ($table) {
            if (toggle_site_products($env, $revl, $table, $db)) {
                $delta = 1;
                if (publish_site($site, $db)) {
                    $good = true;
                }
            }
        }

        if ($delta) {
            $fate = ($good) ? 'saved' : 'not saved';
            $text = "Changes to <b>$site</b> $fate";
        } else {
            $text = "You did not make any changes to site <b>$site</b> products";
        }

        echo "<p>$text.</p>\n";
        if ($delta) {
            list_local_host($revl, $db);
        }

        $link = edit_site_link($env);
        echo "<p>$link.</p>\n";
    }
    echo again($env);
}



function debug_census(&$env, $db)
{
    echo again($env);

    $ord  = $env['ord'];
    $self = $env['self'];
    $priv = $env['priv'];
    $list = array();
    $ords = order($ord);

    if ($priv) {
        $sql  = "select * from Revisions\n";
        $sql .= " order by $ords";
        $list = find_many($sql, $db);
    } else {
        $txt  = "<br>\n";
        $txt .= "No access to this function.<br>\n";
        $txt .= "<br>\n";
        echo $txt;
    }

    if ($list) {
        $num  = safe_count($list);

        echo "<h2>$num machines found</h2>\n";

        echo table_header();
        $head = explode('|', 'id|host|site|uuid|vers|last|serv|prov|action');
        echo table_data($head, 1);
        reset($list);
        foreach ($list as $key => $row) {
            $a    = array();
            $last = $row['ctime'];
            $serv = $row['stime'];
            $prov = $row['provisional'];
            $id   = $row['id'];

            $host = disp($row, 'host');
            $site = disp($row, 'site');
            $uuid = disp($row, 'uuid');
            $vers = disp($row, 'vers');

            $last = fulldate($last);
            $serv = fulldate($serv);
            $prov = fulldate($prov);
            $act  = "$self?id=$id&act";
            $a[]  = html_link("$act=sit", '[site]');
            $a[]  = html_link("$act=hst", '[host]');
            $a[]  = html_link("$act=key", '[keys]');
            $a[]  = html_link("$act=gbl", '[global]');
            $a[]  = html_link("$act=lcl", '[local]');
            $acts = join(' ', $a);
            $args = array($id, $host, $site, $uuid, $vers, $last, $serv, $prov, $acts);
            echo table_data($args, 0);
        }
        echo table_footer();
    }

    echo again($env);
}

/*
    |   Makes a printable version of the
    |   string for display.
    */

function printable($s)
{
    $x = '';
    $n = strlen($s);
    for ($i = 0; $i < $n; $i++) {
        $ch = ord($s[$i]);
        if ($ch < 32) {
            switch ($ch) {
                case  7:
                    $x .= '\\a';
                    break;
                case  8:
                    $x .= '\\b';
                    break;
                case  9:
                    $x .= '\\t';
                    break;
                case 10:
                    $x .= '\\n';
                    break;
                case 11:
                    $x .= '\\v';
                    break;
                case 12:
                    $x .= '\\f';
                    break;
                case 13:
                    $x .= '\\r';
                    break;
                default:
                    $x .= sprintf('\\%03o', $ch);
            }
        } else {
            $x .= chr($ch);
        }
    }
    return $x;
}


function display_value($xx)
{
    if ($xx == '') $xx = '<br>';
    $xx = printable($xx);
    if (strlen($xx) > 40) {
        $xx = substr($xx, 0, 40) . '...';
    }
    return $xx;
}


function debug_pids($prds, $pids, $db)
{
    $prod = array();
    $keys = array();
    $mets = array();
    if (($pids) && ($prds)) {
        $prod = product_pids($pids, $db);
        $keys = provis_pids($pids, $db);
        $mets = meter_pids($pids, $db);
    }
    if (($prds) && ($keys) && ($mets)) {
        $head = explode('|', 'name|user|pid|prov|enab|meter|files');
        echo table_header();
        echo table_data($head, 1);

        reset($prds);
        foreach ($prds as $pid => $row) {
            $enab = $row['enabled'];
            $metr = $row['metered'];
            $prov = $row['provisioned'];
            $user = $prod[$pid]['username'];
            $name = $prod[$pid]['prodname'];
            $glob = $prod[$pid]['global'];
            $mlst = $mets[$pid];
            $plst = $keys[$pid];
            $scop = ($glob) ? 'g' : 'l';
            $user = "$user($scop)";
            $a    = array();

            reset($mlst);
            foreach ($mlst as $k => $filename) {
                $a[] = "$filename &nbsp; (meter)";
            }
            reset($plst);
            foreach ($plst as $k => $filename) {
                $a[] = "$filename &nbsp; (key)";
            }
            $file = ($a) ? join('<br>', $a) : '<br>';
            $args = array($name, $user, $pid, $prov, $enab, $metr, $file);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}



function debug_site(&$env, $db)
{
    echo again($env);

    $hid  = $env['hid'];
    $priv = $env['priv'];
    $revl = find_any_machine($hid, $db);
    if (($priv) && ($revl)) {
        $site = $revl['site'];
        $pids = array();
        $prds = array();
        mark_site($site);
        site_pids($site, $pids, $prds, $db);
        debug_pids($prds, $pids, $db);
    }
    echo again($env);
}

function debug_host(&$env, $db)
{
    echo again($env);
    $hid  = $env['hid'];
    $priv = $env['priv'];
    $revl = find_any_machine($hid, $db);
    if (($priv) && ($revl)) {
        $site = $revl['site'];
        $host = $revl['host'];
        $pids = array();
        $prds = array();
        host_pids($site, $host, $pids, $prds, $db);
        mark_host($site, $host);
        debug_pids($prds, $pids, $db);
    }
    echo again($env);
}


function crypt_table($keys, $db)
{
    if ($keys) {
        $pids = array();
        reset($keys);
        foreach ($keys as $key => $row) {
            $pids[] = $row['productid'];
        }
        $prod = product_pids($pids, $db);

        echo table_header();
        $head = explode('|', 'name|user|cid|pid|uuid|date|last|n|method|encode|decode');
        echo table_data($head, 1);
        reset($keys);
        foreach ($keys as $key => $row) {
            $cid  = $row['cryptid'];
            $pid  = $row['productid'];
            $n    = $row['access'];
            $date = fulldate($row['created']);
            $used = fulldate($row['lastuse']);
            $uuid = disp($row, 'uuid');
            $meth = disp($row, 'method');
            $ekey = $row['encryptkey'];
            $dkey = $row['decryptkey'];
            $name = @strval($prod[$pid]['prodname']);
            $user = @strval($prod[$pid]['username']);
            $glob = @intval($prod[$pid]['global']);
            $scop = ($glob) ? 'g' : 'l';
            $user = "$user($scop)";


            $dhex = ($dkey) ? bin2hex($dkey) : '<br>';
            $ehex = ($ekey) ? bin2hex($ekey) : '<br>';
            $args = array($name, $user, $cid, $pid, $uuid, $date, $used, $n, $meth, $ehex, $dhex);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}


function single_shot(&$env, $db)
{
    echo again($env);
    $priv = $env['priv'];
    if ($priv) {

        $sql = "delete from " . $GLOBALS['PREFIX'] . "provision.CryptKeys\n where productid = 28";
        redcommand($sql, $db);
        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "provision.CryptKeys\n"
            . " where method = ''";
        redcommand($sql, $db);
    }
    echo again($env);
}


/*
    |  Creates a somewhat realistic-looking fake site.
    |
    |  And why do we want to do that?
    |
    |  So we can test all the various delete site and
    |  host commands without risking any real data.
    |
    |  This also allows us to test the site and host
    |  commands without messing up a real machine.
    */

function fake_site(&$env, $db)
{
    echo again($env);
    /*

        this can wait ...

        $priv = $env['priv'];
        if ($priv)
        {
            $now  = time();
            $host = 'baked';
            $site = 'Alaska';
            $vers = '1.008.0745.BE';
            $uuid = '123457-123457-123457-123457-123457';
            $name = 'Silly';
            $auth = safe_addslashes($env['auth']);

            $sql = "select * from\n
                 . " core.Customers where\n"
                 . " customer = '$site'";
            $list = find_many($sql,$db);

            if (!$list)
            {
                $sql = "insert into\n"
                     . " core.Customers set\n"
                     . " customer='$site',\n"
                     . " username=''";
                redcommand($sql,$db);
                $sql = "insert into\n"
                     . " core.Customers set\n"
                     . " customer='$site',\n"
                     . " username='$auth',\n"
                     . " owner = 1";
                redcommand($sql,$db);
            }

            $sql = "select * from core.Census where\n"
                 . " site = '$site'";
            $list = find_many($sql,$db);
            if (!$list)
            {
                $sql = "insert into core.Census set\n"
                     . " site='$site',\n"
                     . " host='$host',\n"
                     . " last=$now";
                redcommand($sql,$db);
            }

            $revl = find_any_revl($site,$host,$db);
            if (!$revl)
            {
                $sql = "insert into Revisions set\n"
                     . " cust='$site',\n"
                     . " host='$host',\n"
                     . " vers='$vers',\n"
                     . " uuid='$uuid',\n"
                     . " stime=0,\n"
                     . " ctime=$now";
                redcommand($sql,$db);
                $serv = server_name($db);
                $qp = safe_addslashes(constProductList);
                $ms = constMeterScope;
                $ps = constProvisScope;
                $sql = "insert into Globals set\n"
                     . " cust='$site',\n"
                     . " host='$serv',\n"
                     . " name='$qp',\n"
                     . " scop=$ms,\n"
                     . " valu='',\n"
                     . " last=$now";
                redcommand($sql,$db);
                $sql = "insert into Globals set\n"
                     . " cust='$site',\n"
                     . " host='$serv',\n"
                     . " name='$qp',\n"
                     . " scop=$ps,\n"
                     . " valu='',\n"
                     . " last=$now";
                redcommand($sql,$db);
                $sql = "insert into Locals set\n"
                     . " cust='$site',\n"
                     . " host='$host',\n"
                     . " name='$qp',\n"
                     . " scop=$ms,\n"
                     . " valu='',\n"
                     . " last=$now";
                redcommand($sql,$db);
                $sql = "insert into Locals set\n"
                     . " cust='$site',\n"
                     . " host='$host',\n"
                     . " name='$qp',\n"
                     . " scop=$ps,\n"
                     . " valu='',\n"
                     . " last=$now";
                redcommand($sql,$db);
            }
            $sql = "select * from provision.Products\n"
                 . " where username = '$auth'\n"
                 . " and global = 0\n"
                 . " and prodname = '$name'";
            $prod = find_one($sql,$db);
            if (!$prod)
            {
                $ins = "insert into provision.Products set\n"
                     . " username = '$auth',\n"
                     . " created = $now,\n"
                     . " global = 0,\n"
                     . " prodname = '$name'";
                redcommand($ins,$db);
                $prod = find_one($sql,$db);
            }
            if ($prod)
            {
                $user = 'Philip J. Fry';
                $file = safe_addslashes('C:\calc.exe');
                $sql = "insert into provision.Meter set\n"
                     . " sitename = '$site',\n"
                     . " machine = '$host',\n"
                     . " uuid = '$uuid',\n"
                     . " exename = '$file',\n"
                     . " servertime = $now,\n"
                     . " clienttime = $now,\n"
                     . " product = '$name',\n"
                     . " username = '$user',\n"
                     . " owner = '$auth',\n"
                     . " processid = '12345',\n"
                     . " eventtype = 1";
                redcommand($sql,$db);
                $sql = "insert into provision.Audit set\n"
                     . " sitename = '$site',\n"
                     . " machine = '$host',\n"
                     . " uuid = '$uuid',\n"
                     . " servertime = $now,\n"
                     . " clienttime = $now,\n"
                     . " product = '$name',\n"
                     . " username = '$user',\n"
                     . " owner = '$auth',\n"
                     . " action = 'fake',\n"
                     . " who = 0";
                redcommand($sql,$db);
                $pid = $prod['productid'];
                $sql = "select * from provision.SiteAssignments\n"
                     . " where sitename = '$site'";
                $list = find_many($sql,$db);
                if (!$list)
                {
                    $sql = "insert into\n"
                         . " provision.SiteAssignments set\n"
                         . " productid = $pid,\n"
                         . " sitename = '$site'";
                    redcommand($sql,$db);
                }

                $sql = "select * from\n"
                     . " provision.MachineAssignments\n"
                     . " where machine = '$host'"
                     . " and sitename = '$site'";
                $list = find_many($sql,$db);
                if (!$list)
                {
                    $sql = "insert into\n"
                         . " provision.MachineAssignments set\n"
                         . " productid = $pid,\n"
                         . " machine = '$host',\n"
                         . " sitename = '$site'";
                    redcommand($sql,$db);
                }


                $sql = "select * from provision.CryptKeys\n"
                     . " where uuid = '$uuid'";
                $list = find_many($sql,$db);
                if (!$list)
                {
                    $sql = "insert into provision.CryptKeys set\n"
                         . " created = $now,\n"
                         . " productid = $pid,\n"
                         . " uuid = '$uuid',\n"
                         . " encryptkey = 'deadbeef',\n"
                         . " decryptkey = 'deadbeef',\n"
                         . " method = 'EVP_rc4()'";
                    redcommand($sql,$db);
                }
            }
        }
*/
    echo again($env);
}


function list_host_link(&$env)
{
    $self = $env['self'];
    $hid  = $env['hid'];
    $site = $env['revl']['site'];
    $href = "$self?id=$hid&act=mch";
    $list = "$site Machines";
    return html_link($href, $list);
}

function edit_site_link(&$env)
{
    $self = $env['self'];
    $hid  = $env['hid'];
    $href = "$self?id=$hid&act=esp";
    $edit = 'Manage Site Products';
    return html_link($href, $edit);
}

function edit_host_link(&$env)
{
    $self = $env['self'];
    $hid  = $env['hid'];
    $href = "$self?id=$hid&act=emp";
    $edit = 'Manage Machine Products';
    return html_link($href, $edit);
}


function host_crypt(&$env, $db)
{
    echo again($env);

    $priv = $env['priv'];
    $revl = $env['revl'];

    if (($priv) && ($revl)) {
        $pids = array();
        $prds = array();
        $site = $env['revl']['site'];
        $host = $env['revl']['host'];
        host_pids($site, $host, $pids, $prds, $db);
        mark_host($site, $host);
        $qu  = safe_addslashes($env['revl']['uuid']);
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "provision.CryptKeys\n"
            . " where uuid = '$qu'\n"
            . " limit 50";
        $keys = find_many($sql, $db);
        crypt_table($keys, $db);
    }
    echo again($env);
}


function debug_crypt(&$env, $db)
{
    echo again($env);

    $priv = $env['priv'];
    if ($priv) {
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "provision.CryptKeys\n"
            . " order by created desc\n"
            . " limit 200";
        $keys = find_many($sql, $db);
        crypt_table($keys, $db);
    }
    echo again($env);
}

function warn_delete($name)
{
    return "<br>\n"
        . "You do not own <b>$name</b>.<br>\n"
        . "<br>\n"
        . "You may still remove it if you would like to, however<br>"
        . "if you do, you will not be able to replace it later.<br>"
        . "<br>\n";
}



/*
    |  You can remove a product from a site even
    |  if you do not own it.  However, if you do,
    |  you will be unable to put it back.
    */

function delete_site_products(&$env, $db)
{
    $id   = $env['hid'];
    $pid  = $env['pid'];
    $auth = $env['auth'];
    $self = $env['self'];
    $revl = $env['revl'];
    $site = $env['revl']['site'];
    $prod = find_any_product($pid, $db);
    $txt  = "Machine $id or product $pid has vanished.";

    echo again($env);
    if (($revl) && ($prod)) {
        $name = $prod['prodname'];
        $user = $prod['username'];
        $glob = $prod['global'];
        mark_site($site);
        $table = site_table($env, $revl, $db);
        if (isset($table[$pid])) {
            if ((!$glob) && ($user != $auth)) {
                echo warn_delete($name);
            }
            $act = "$self?id=$id&pid=$pid&act";
            $yes = html_link("$act=psp", '[Yes]');
            $no  = html_link("$act=esp", '[No]');
            $txt = "Are you sure you want to delete product"
                . " <b>$name</b> from site <b>$site</b>?\n"
                . "<br><br>\n\n"
                . "$yes &nbsp;&nbsp; $no<br>\n";
        } else {
            $txt = "Product <b>$name</b> has vanished.<br>\n\n";
        }
    }
    echo $txt;
    echo again($env);
}


function describe_over(&$env, $list, $prod)
{
    $site = $env['revl']['site'];

    if (($site) && ($list) && ($prod)) {
        $name = $prod['prodname'];
        $head = explode('|', 'Site|Machine');
        echo table_header();
        echo pretty_header($name, 2);
        echo table_data($head, 1);
        reset($list);
        foreach ($list as $key => $host) {
            $args = array($site, $host);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}



function delete_over(&$env, $prod, $db)
{
    $list = array();
    if ($prod) {
        $pid  = $prod['productid'];
        $site = $env['revl']['site'];
        $name = $prod['prodname'];
        $list = over_product($site, $pid, $db);
    }
    if ($list) {
        describe_over($env, $list, $prod);
        $self = $env['self'];
        $hid  = $env['hid'];
        $act  = "$self?id=$hid&pid=$pid&act";
        $yes  = html_link("$act=pop", '[Yes]');
        $no   = html_link("$act=esp", '[No]');

        // The machines listed above have local product configurations.
        //
        // Would you like to remove NAME from their product configurations?
        //
        // [Yes]   [No]

        echo  "<p>The machines listed above have"
            .   " local product configurations.</p>\n"
            .   "<p>Would you like to remove <b>$name</b>"
            .   " from their product configurations?</p>\n"
            .   "\n"
            .   "<p>$yes &nbsp;&nbsp; $no</p>\n<br>\n";
    }
}

function insert_over(&$env, $prod, $db)
{
    $list = array();
    if ($prod) {
        $pid  = $prod['productid'];
        $site = $env['revl']['site'];
        $name = $prod['prodname'];
        $list = over_missing($site, $pid, $db);
    }
    if ($list) {
        describe_over($env, $list, $prod);
        $self = $env['self'];
        $prov = $env['prov'];
        $hid  = $env['hid'];
        $act  = "$self?id=$hid&pid=$pid&prov=$prov&act";
        $yes  = html_link("$act=uop", '[Yes]');
        $no   = html_link("$act=esp", '[No]');

        // The machines listed above have local product configurations.
        //
        // Would you like to add NAME to their product configurations?
        //
        // [Yes]   [No]

        echo  "<p>The machines listed above"
            .   " have local product configurations.</p>"
            .   "<p>Would you like to add <b>$name</b>"
            .   " to their product configurations?</p>\n"
            .   "<p>\n"
            .   "$yes &nbsp;&nbsp; $no"
            .   "</p><br>\n";
    }
}



/*
    |  Note we call find_any_product instead
    |  of find_product ... remember, the user
    |  is allowed to remove products even if
    |  he doesn't own them.
    */

function purge_site_product(&$env, $db)
{
    echo again($env);
    $pid  = $env['pid'];
    $revl = $env['revl'];
    $prod = find_any_product($pid, $db);
    if (($revl) && ($prod)) {
        $good = false;
        $site = $env['revl']['site'];
        $name = $prod['prodname'];
        debug_note("purge $name from $site");

        if (remove_site($revl, $pid, $db)) {
            if (publish_site($site, $db)) {
                $good = true;
                audit_action($env, '', $name, 'remove', $db);
            }
        }

        $fate = ($good) ? 'removed' : 'not removed';
        echo "<p>Product <b>$name</b> $fate from site <b>$site</b>.</p>\n";

        $link = edit_site_link($env);
        echo  "<p>$link.</p>\n";

        if ($good) {
            delete_over($env, $prod, $db);
        }
    } else {
        if ($revl) {
            $txt = "Product <b>$pid</b> does not exist.";
        } else {
            $txt = "Site not found.";
        }
        $txt = "<p>$txt</p>\n";
        echo $txt;
    }
    echo again($env);
}

function purge_over_product(&$env, $db)
{
    echo again($env);
    $pid  = $env['pid'];
    $auth = $env['auth'];
    $prov = $env['prov'];
    $site = $env['revl']['site'];
    $prod = find_any_product($pid, $db);
    $list = over_product($site, $pid, $db);

    if (($list) && ($prod)) {
        $name = $prod['prodname'];

        reset($list);
        foreach ($list as $key => $host) {
            $good = false;
            if (remove_host($site, $host, $pid, $db)) {
                if (publish_host($site, $host, $db)) {
                    $good = true;
                    audit_action($env, $host, $name, 'remove', $db);
                }
            }

            $text = ucwords($host);
            $fate = ($good) ? 'Removed' : 'Did not remove';
            echo "<p>$fate <b>$name</b>"
                .  " from <b>$text</b> at"
                .  " <b>$site</b>.</p>\n";
        }
        $link = edit_site_link($env);
        echo "<p>$link.</p>\n";
    }
    echo again($env);
}



function empty_products($env)
{
    $href = 'product.php?act=ap';
    $here = html_link($href, 'here');

    echo "<br>\n"
        . "Currently, you do not own any products.<br>\n"
        . "Click $here if you would like to create one.<br>\n"
        . "<br>\n";
}

function add_site_product(&$env, $db)
{
    echo again($env);
    $auth  = $env['auth'];
    $site  = $env['revl']['site'];
    $names = find_product_names($auth, $db);
    if (($site) && ($names)) {
        mark_site($site);
        $id   = $env['hid'];
        $name = html_select('pid', $names, 0, 1);
        $prov = checkbox('prov', 1);

        $submit = button('Add');
        $cancel = button('Cancel');

        echo post_self();
        echo hidden('act', 'usp');
        echo hidden('id', $id);

        echo table_header();
        echo pretty_header('Add Site Product', 2);
        echo two_col('Site:', $site);
        echo two_col('Product Name:', $name);
        echo two_col('Provisioned:', $prov);
        echo two_col($submit, $cancel);
        echo table_footer();
        echo form_footer();
    } else {
        if (safe_count($names) == 0) {
            empty_products($env);
        }
    }
    echo again($env);
}


function edit_host_products(&$env, $db)
{
    echo again($env);
    $table = array();
    $locl = array();
    $self = $env['self'];
    $auth = $env['auth'];
    $ord  = $env['ord'];
    $id   = $env['hid'];
    $revl = $env['revl'];
    $site = $env['revl']['site'];
    $host = $env['revl']['host'];

    if (($site) && ($host)) {
        $locl = host_locals($site, $host, $db);
        $table = host_table($env, $revl, $db);

        debug_note("edit machine products host:$host, site:$site id:$id");
        //       mark_host($site,$host);
    }

    if (($site) && ($host)) {
        if ($locl) {
            $a   = array();
            $edt = 'Manage Site Products';
            $new = 'Add new machine product';
            $del = 'Delete all these settings and revert to site-wide settings';
            $act = "$self?id=$id&act";
            $a[] = html_link("$act=esp", $edt);
            $a[] = html_link("$act=amp", $new);
            $a[] = html_link("$act=rsp", $del);
            $txt = join(".<br><br>\n", $a) . "<br><br>\n\n";
            echo $txt;
        } else {
            missing_host_variables($site, $host);
        }
    }

    if ($table) {
        sort_table($table, $ord);

        echo post_self();
        echo hidden('act', 'cmp');
        echo hidden('id', $id);

        $update = button('Update');

        echo para($update);

        $rev  = ($ord) ? 0 : 1;
        $href = "$self?id=$id&ord=$rev&act=emp";
        $link = html_link($href, 'Product name');
        $head = explode('|', "$link|Provisiond|Enabled|Metered|Action");
        $name = ucwords($host);
        $text = "$name at $site";

        echo table_header();
        echo pretty_header($text, 5);
        echo table_data($head, 1);

        reset($table);
        foreach ($table as $pid => $row) {
            $name  = $row['name'];
            $pprod = $row['pprod'];
            $mprod = $row['mprod'];
            $penab = $row['penab'];

            $pchk = checkbox("pprod_$pid", $pprod);
            $mchk = checkbox("mprod_$pid", $mprod);
            $echk = checkbox("penab_$pid", $penab);
            $enab = ($pprod) ? $echk : '<br>';

            $href = "$self?id=$id&pid=$pid&act=dmp";
            $acts = html_link($href, '[delete]');
            $args = array($name, $pchk, $enab, $mchk, $acts);
            echo table_data($args, 0);
        }
        echo table_footer();

        echo para($update);

        echo form_footer();
    } else {
        if (($site) && ($host) && ($locl)) {
            echo   "<br>\n"
                .    "There are no machine-specific settings that<br>\n"
                .    "override the site wide settings for<br>\n"
                .    "provisioning and metering on this machine.<br>\n"
                .    "<br>\n";
        }
    }

    echo again($env);
}

function view_host_products(&$env, $db)
{
    $table = array();
    $locl  = array();
    $revl  = $env['revl'];
    $site  = $env['revl']['site'];
    $host  = $env['revl']['host'];
    $self  = $env['self'];
    $auth  = $env['auth'];
    $pprv  = $env['pprv'];
    $ord   = $env['ord'];
    $id    = $env['hid'];

    echo again($env);
    if (($site) && ($host)) {
        $locl = host_locals($site, $host, $db);
        $table = host_table($env, $revl, $db);

        debug_note("view machine products host:$host, site:$site id:$id");
        //      mark_host($site,$host);

        $verb = ($pprv) ? 'Manage' : 'View';
        $cmd  = ($pprv) ? 'esp' : 'vsp';
        $txt  = "$verb Site Products";
        $act  = "$self?id=$id&act";
        $link = html_link("$act=$cmd", $txt);
        echo  "<br><p>$link.</p><br>\n";
    }

    if ($table) {
        sort_table($table, $ord);

        echo post_self();
        echo hidden('act', 'mch');
        echo hidden('id', $id);

        $done = button('Done');

        echo para($done);

        $rev  = ($ord) ? 0 : 1;
        $href = "$self?id=$id&ord=$rev&act=vmp";
        $link = html_link($href, 'Product name');
        $head = explode('|', "$link|Provisiond|Enabled|Metered");
        $name = ucwords($host);
        $text = "$name at $site";

        echo table_header();
        echo pretty_header($text, 4);
        echo table_data($head, 1);

        reset($table);
        foreach ($table as $pid => $row) {
            $name  = $row['name'];
            $pprod = $row['pprod'];
            $mprod = $row['mprod'];
            $penab = $row['penab'];

            $pchk = bool($pprod);
            $mchk = bool($mprod);
            $enab = ($pprod) ? bool($penab) : '<br>';
            $args = array($name, $pchk, $enab, $mchk);
            echo table_data($args, 0);
        }
        echo table_footer();

        echo para($done);

        echo form_footer();
    } else {
        if (($site) && ($host) && ($locl)) {
            echo    "<br>\n"
                .     "There are no machine-specific settings that<br>\n"
                .     "override the site wide settings for<br>\n"
                .     "provisioning and metering on this machine.<br>\n"
                .     "<br>\n";
        }
    }

    echo again($env);
}


function delete_host_product(&$env, $db)
{
    echo again($env);
    $id   = $env['hid'];
    $pid  = $env['pid'];
    $auth = $env['auth'];
    $txt  = "Machine <b>$id</b> or product <b>$pid</b> has vanished<br>\n";
    $prod = find_any_product($pid, $db);
    $revl = $env['revl'];
    $site = $env['revl']['site'];
    $host = $env['revl']['host'];
    if (($site) && ($host) && ($prod)) {
        $name  = $prod['prodname'];
        $user  = $prod['username'];
        $glob  = $prod['global'];
        $table = host_table($env, $revl, $db);
        if (isset($table[$pid])) {
            if ((!$glob) && ($user != $auth)) {
                echo warn_delete($name);
            }
            $self = $env['self'];
            $act  = "$self?id=$id&pid=$pid&act";
            $yes  = html_link("$act=pmp", '[Yes]');
            $no   = html_link("$act=emp", '[No]');
            $msg  = ucwords($host);
            $txt  = "Are you sure you want to delete product"
                . " <b>$name</b> from machine <b>$msg</b>"
                . " at site <b>$site</b>?\n"
                . "<br><br>\n\n"
                . "$yes &nbsp;&nbsp; $no<br>\n";
        } else {
            $txt  = "Product <b>$name</b> has vanished.<br>\n\n";
        }
    }
    echo $txt;
    echo again($env);
}



/*
    |  A user must have access to the product in order to
    |  assign it to a site.  This means that either
    |  he owns the product, or it is a global product.
    */

function add_site_complete(&$env, $db)
{
    echo again($env);

    $id   = $env['hid'];
    $pid  = $env['pid'];
    $auth = $env['auth'];
    $revl = $env['revl'];
    $prod = find_product($pid, $auth, $db);

    if (($revl) && ($prod)) {
        $good = false;
        $prov = $env['prov'];
        $site = $revl['site'];
        $name = $prod['prodname'];
        $list = find_site_product($revl, $prod, $db);

        if ($list) {
            echo "<p>Site <b>$site</b> already"
                .  " uses product <b>$name</b>.</p>\n";
        } else {
            if (assign_site($revl, $prod, $prov, $db)) {
                if (publish_site($site, $db)) {
                    $good = true;
                    audit_action($env, '', $name, 'assign', $db);
                }
            }
        }
        $fate = ($good) ? 'added' : 'not added';
        $link = edit_site_link($env);
        echo "<p>Product <b>$name</b> $fate"
            .  " to <b>$site</b>.</p>\n"
            .  "<p>$link.</p>\n";

        insert_over($env, $prod, $db);
    } else {
        if ($prod) {
            echo "<p>No site found ... </p>\n";
        } else {
            echo "<p>No product found ... </p>\n";
        }
    }

    echo again($env);
}


function add_host_complete(&$env, $db)
{
    echo again($env);
    $id   = $env['hid'];
    $pid  = $env['pid'];
    $prov = $env['prov'];
    $auth = $env['auth'];
    $revl = $env['revl'];
    $prod = find_product($pid, $auth, $db);

    $table = array();
    if (($revl) && ($prod)) {
        $good = false;
        $name = $prod['prodname'];
        $site = $env['revl']['site'];
        $host = $env['revl']['host'];
        $text = ucwords($host);
        $list = find_host_product($revl, $prod, $db);
        if ($list) {
            echo "<p><b>$text</b> at <b>$site</b>"
                .  " already uses <b>$name</b>.</p>\n";
        } else {
            if (assign_host($revl, $prod, $prov, $db)) {
                if (publish_host($site, $host, $db)) {
                    audit_action($env, $host, $name, 'assign', $db);
                    $good = true;
                }
            }
        }
        $link = edit_host_link($env);
        $fate = ($good) ? 'added' : 'not added';
        echo "<p>Product <b>$name</b> $fate"
            .  " to <b>$text</b> at <b>$site</b>.</p>\n"
            .  "<p>$link.</p>\n";
    }
    echo again($env);
}

function add_over_complete(&$env, $db)
{
    echo again($env);
    $pid  = $env['pid'];
    $auth = $env['auth'];
    $prov = $env['prov'];
    $site = $env['revl']['site'];
    $prod = find_product($pid, $auth, $db);
    $list = over_missing($site, $pid, $db);

    if (($list) && ($prod)) {
        $name = $prod['prodname'];

        reset($list);
        foreach ($list as $key => $host) {
            $good = false;
            $revl = find_any_revl($site, $host, $db);
            if (assign_host($revl, $prod, $prov, $db)) {
                if (publish_host($site, $host, $db)) {
                    $good = true;
                    audit_action($env, $host, $name, 'assign', $db);
                }
            }
            $text = ucwords($host);
            $fate = ($good) ? 'Added' : 'Did not add';
            echo "<p>$fate product <b>$name</b>"
                .  " to <b>$text</b> at"
                .  " <b>$site</b>.</p>\n";
        }
        $link = edit_site_link($env);
        echo "<p>$link.</p>\n";
    }
    echo again($env);
}


function add_host_product(&$env, $db)
{
    echo again($env);
    $auth  = $env['auth'];
    $site  = $env['revl']['site'];
    $host  = $env['revl']['host'];
    $names = find_product_names($auth, $db);
    if (($site) && ($host) && ($names)) {
        $id   = $env['hid'];
        $name = html_select('pid', $names, 0, 1);
        $prov = checkbox('prov', 1);

        $submit = button('Add');
        $cancel = button('Cancel');

        echo post_self();
        echo hidden('act', 'ump');
        echo hidden('id', $id);

        echo table_header();
        echo pretty_header('Add Machine Product', 2);
        echo two_col('Site:', $site);
        echo two_col('Machine:', $host);
        echo two_col('Product Name:', $name);
        echo two_col('Provisioned:', $prov);
        echo two_col($submit, $cancel);
        echo table_footer();
        echo form_footer();
    }
    echo again($env);
}


/* -------------------------------------------------------------

Are you sure you want to remove the local product
settings for machine Dupont at site Eric's House
and revert to the site-wide settings?


[Yes]  [No]

 ------------------------------------------------ */

function revert_site_products(&$env, $db)
{
    echo again($env);
    $id   = $env['hid'];
    $site = $env['revl']['site'];
    $host = $env['revl']['host'];
    debug_note("revert site products host:$host, id:$id");
    if (($site) && ($host)) {
        $self = $env['self'];
        $name = ucwords($host);
        $act  = "$self?id=$id&act";
        $yes  = html_link("$act=rl", '[Yes]');
        $no   = html_link("$act=mch", '[No]');
        $txt  = "Are you sure you want to remove the local product<br>"
            . "settings for machine <b>$name</b> at site"
            . " <b>$site</b><br> and revert to the site-wide"
            . " settings?<br>\n"
            . "<br><br>\n\n"
            . "$yes &nbsp;&nbsp; $no<br>\n";
    }
    echo $txt;
    echo again($env);
}


function set_state($hid, $vid, $sgid, $hgid, $stat, $db)
{
    $gid = ($stat) ? $hgid : $sgid;

    $sql = "select valmapid from ValueMap left join Census on (ValueMap."
        . "censusuniq=Census.censusuniq) left join Variables on ("
        . "ValueMap.varuniq=Variables.varuniq) left join MachineGroups "
        . "on (ValueMap.mgroupuniq=MachineGroups.mgroupuniq) where "
        . "id=$hid and varid=$vid and mgroupid!=$gid";
    $set = DSYN_DeleteSet(
        $sql,
        constDataSetGConfigValueMap,
        "valmapid",
        "set_state",
        0,
        1,
        constOperationDelete,
        $db
    );

    $sql = "select mgroupuniq, mcatuniq from MachineGroups where mgroupid "
        . "= $gid";
    $res = find_one($sql, $db);
    if ($res && $set) {
        $last = time();
        $sql = "update ValueMap left join Census on (ValueMap."
            . "censusuniq=Census.censusuniq) left join Variables on ("
            . "ValueMap.varuniq=Variables.varuniq) left join MachineGroups"
            . " on (ValueMap.mgroupuniq=MachineGroups.mgroupuniq) set\n"
            . " stat = $stat,\n"
            . " srev = srev+1,\n"
            . " ValueMap.oldmgroupuniq = ValueMap.mgroupuniq,\n"
            . " ValueMap.oldvalu = '',\n"
            . " ValueMap.last = $last,\n"
            . " ValueMap.expire = 0,\n"
            . " ValueMap.mgroupuniq = '" . $res['mgroupuniq'] . "',\n"
            . " ValueMap.mcatuniq = '" . $res['mcatuniq'] . "',\n"
            . " revl = revl+1\n"
            . " where id = $hid\n"
            . " and varid = $vid\n"
            . " and mgroupid != $gid";
        $res = redcommand($sql, $db);
    }

    DSYN_UpdateSet($set, constDataSetGConfigValueMap, "valmapid", $db);

    return affected($res, $db);
}


/*
    |  Sets a machines local values to be the same as
    |  the sites global values.
    */

function become_local(&$env, $hgrp, $sgrp, $stat, $db)
{
    if (($env['revl']) && ($sgrp) && ($hgrp)) {
        $now  = $env['now'];
        $serv = $env['serv'];
        $site = $env['revl']['site'];
        $host = $env['revl']['host'];
        $hid  = $env['revl']['censusid'];
        $sgid = $sgrp['mgroupid'];
        $hgid = $hgrp['mgroupid'];
        debug_note("$host at $site (hgid:$hgid,sgid:$sgid,hid:$hid,stat:$stat)");

        $ps   = constProvisScope;
        $ms   = constMeterScope;
        $pgbl = product_record($sgid, $ps, $db);
        $mgbl = product_record($sgid, $ms, $db);

        if (($pgbl) || ($mgbl)) {
            if ($pgbl) {
                /* This needs to handle potentially multiple values */
                $vid = $pgbl[0]['varid'];
                $val = $pgbl[0]['valu'];
                set_value(
                    $vid,
                    $hgid,
                    $serv,
                    $val,
                    constSourceScripConfig,
                    $now,
                    $db
                );
                set_state($hid, $vid, $sgid, $hgid, $stat, $db);
            }

            if ($mgbl) {
                /* This needs to handle potentially multiple values */
                $vid = $mgbl[0]['varid'];
                $val = $mgbl[0]['valu'];
                set_value(
                    $vid,
                    $hgid,
                    $serv,
                    $val,
                    constSourceScripConfig,
                    $now,
                    $db
                );
                set_state($hid, $vid, $sgid, $hgid, $stat, $db);
            }
            dirty_hid($hid, $db);
            hid_revision($hid, $now, $db);
        } else {
            missing_site_variables($site);
        }
    }
}



function confirm_create_local(&$env, $db)
{
    echo again($env);
    $txt  = '';
    $id   = $env['hid'];
    $site = $env['revl']['site'];
    $host = $env['revl']['host'];

    if (($site) && ($host)) {
        $self = $env['self'];
        $act  = "$self?id=$id&act";
        $yes  = html_link("$act=cl", '[Yes]');
        $no   = html_link("$act=mch", '[No]');
        $name = ucwords($host);

        //  Are you sure you want to create machine-specific settings
        //  that will override the site-wide settings for provisioning
        //  and metering for machine Dupont at site Eric's House?
        //
        //  [Yes]  [No]

        $txt  = "Are you sure you want to create"
            . " machine-specific settings<br>that"
            . " will override the site-wide"
            . " settings for provisioning<br>and"
            . " metering for machine <b>$name</b>"
            . " at site <b>$site</b>?\n"
            . " <br><br>\n\n"
            . "$yes &nbsp;&nbsp; $no<br>\n";
    }
    echo $txt;
    echo again($env);
}


function kill_host($revl, $db)
{
    if ($revl) {
        $qu  = safe_addslashes($revl['uuid']);
        $qs  = safe_addslashes($revl['site']);
        $qh  = safe_addslashes($revl['host']);
        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "provision.MachineAssignments where\n"
            . " uuid = '$qu'";
        $res = redcommand($sql, $db);
        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "provision.MachineAssignments\n"
            . " where machine = '$qh'\n"
            . " and sitename = '$qs'";
        $res = redcommand($sql, $db);
    }
}


function create_local(&$env, $db)
{
    echo again($env);

    $revl = $env['revl'];
    $hid  = $env['hid'];
    $hgrp = find_mgrp_hid($hid, $db);
    $text = "<p>unexpected error</p>";
    $good = false;
    if (($revl) && ($hgrp)) {
        $site = $env['revl']['site'];
        $host = $env['revl']['host'];
        $uuid = $env['revl']['uuid'];
        $sgrp = GCFG_find_site_mgrp($site, $db);

        kill_host($revl, $db);
        become_local($env, $hgrp, $sgrp, 1, $db);
        $table = site_table($env, $revl, $db);

        $bad = false;
        $qs  = safe_addslashes($site);
        $qh  = safe_addslashes($host);
        $qu  = safe_addslashes($uuid);

        reset($table);
        foreach ($table as $pid => $row) {
            $metr = $row['mprod'];
            $enab = $row['penab'];
            $prov = $row['pprod'];
            $num  = insert_host($pid, $qs, $qh, $qu, $prov, $enab, $metr, $db);
            if ($num != 1) $bad = true;
        }
        if ($bad) {
            $text = "<p>Could not create local machine configuration</p>";
        } else {
            audit_action($env, $host, '', 'local', $db);
            $good = true;
        }
    }
    if ($good) {
        $name = ucwords($host);
        echo "<p>Created local machine configuration"
            .  " for <b>$name</b> at <b>$site</b>.</p>\n"
            .  "<p>The values are the same as the site-wide values.</p>\n";
        edit_host_products($env, $db);
    } else {
        echo $text;
        echo again($env);
    }
}


function revert_local(&$env, $db)
{
    echo again($env);
    $revl = $env['revl'];
    $hid  = $env['hid'];
    $hgrp = find_mgrp_hid($hid, $db);
    if (($revl) && ($hgrp)) {
        $site = $env['revl']['site'];
        $host = $env['revl']['host'];
        $sgrp = GCFG_find_site_mgrp($site, $db);

        become_local($env, $hgrp, $sgrp, 0, $db);
        kill_host($revl, $db);

        audit_action($env, $host, '', 'revert', $db);
        $edit = edit_site_link($env);
        $list = list_host_link($env);
        $name = ucwords($host);
        echo "<p>Removed local machine configuration"
            .  " for <b>$name</b> at <b>$site</b>.</p>\n"
            .  "<p>$edit</p>\n"
            .  "<p>$list</p>\n";
    }
    echo again($env);
}

function unknown_action(&$env, $db)
{
    echo again($env);
    $act = $env['act'];
    debug_note("unknown action act:$act");
    echo again($env);
}


function check_machine_products(&$env, $db)
{
    echo again($env);
    $good = false;
    $revl = $env['revl'];
    if ($revl) {
        $site = $revl['site'];
        $host = $revl['host'];
        $table = host_table($env, $revl, $db);
        if ($table) {
            if (toggle_host_products($env, $revl, $table, $db)) {
                if (publish_host($site, $host, $db)) {
                    $good = true;
                }
            }
        }
        $fate = ($good) ? 'saved' : 'not saved';
        $text = ucwords($host);
        $link = edit_host_link($env);

        echo "<p>Changes to <b>$text</b> at <b>$site</b> $fate.</p>\n";

        echo "<p>$link</p>\n";
    }
    echo again($env);
}


/*
    |  Note we call find_any_product instead
    |  of find_product ... remember, the user
    |  is allowed to remove products even if
    |  he doesn't own them.
    */

function purge_host_product(&$env, $db)
{
    echo again($env);
    $pid  = $env['pid'];
    $revl = $env['revl'];
    $good = false;
    $prod = find_any_product($pid, $db);
    if (($revl) && ($prod)) {
        $site = $env['revl']['site'];
        $host = $env['revl']['host'];
        $name = $prod['prodname'];
        debug_note("purge $name from $host at $site");
        if (remove_host($site, $host, $pid, $db)) {
            if (publish_host($site, $host, $db)) {
                audit_action($env, $host, $name, 'remove', $db);
                $good = true;
            }
        }

        $fate = ($good) ? 'removed' : 'not removed';
        $link = edit_host_link($env);
        $text = ucwords($host);
        echo  "<p>Product <b>$name</b>"
            .   " $fate from <b>$text</b>"
            .   " at <b>$site</b>.</p>\n"
            .   "<p>$link.</p>\n";
    }
    echo again($env);
}

/*
    |  Cancel out of edit product page goes to product list page.
    |  instead of the normal edit product complete page.
    |
    |  Cancel out of update keyfile goes to back to edit products.
    */

function redirect_action($act, $post)
{
    if ($post == 'Cancel') {
        if ($act == 'usp') $act = 'lst';
        if ($act == 'ump') $act = 'lst';
    }
    return $act;
}



/*
    |  Main program
    */

$now = time();
$db   = db_connect();
$auth = process_login($db);
$comp = component_installed();

$act  = get_string('act', 'lst');
$post = get_string('submit', '');
$dbg  = get_integer('debug', 1);

$id   = get_integer('id',  0);
$ord  = get_integer('ord', 0);
$wrd  = get_integer('wrd', 0);
$prov = get_integer('prov', 0);
$enab = get_integer('enab', 0);
$metr = get_integer('metr', 0);

$user   = user_data($auth, $db);
$priv   = @($user['priv_debug']) ?  1 : 0;
$pprv   = @($user['priv_provis']) ? 1 : 0;
$filter = @($user['filtersites']) ? 1 : 0;
$debug  = @($priv) ? $dbg  : 0;

/*
    |  Users who do not have priv_provis can
    |  examine products but not change them.
    |
    |  This means that all they get to do is
    |  list or view ... and that's the only
    |  links we give them.
    |
    |  But remember, anyone can type anything they
    |  please into their browser command line.
    */

if (!$pprv) {
    $tmp = "|$act|";
    $txt = '||||cen|crp|lst|gbl|hst|key|lcl|mch|sit|vmp|vsp|';
    $pos = strpos($txt, $tmp);
    if ($pos <= 0) $act = 'lst';
}

if (!$priv) {
    $tmp = "|$act|";
    $txt = '||||cen|crp|fak|gbl|hst|key|lcl|sit|xxx|';
    $pos = strpos($txt, $tmp);
    if ($pos > 1) $act = 'lst';
}

$carr = site_array($auth, $filter, $db);
$revl = find_machine($id, $auth, $db);
if ($revl) {
    $site = $revl['site'];
} else {
    $site = '';
    $tmp  = "|$act|";
    $txt  = '|||amp|asp|ccl|cl|dmp|dsp|emp|esp|key|lcl|mch|rl|rsp|ump|usp|uop|vmp|vsp|';
    $pos  = strpos($txt, $tmp);
    if ($pos > 1) $act = 'lst';
}
$act   = redirect_action($act, $post);
$title = build_title($act, $site);
$msg   = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
$nav = provis_navigate();
echo standard_html_header($title, $comp, $auth, $nav, 0, 0, $db);

$date = datestring(time());

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($debug) echo "<h2>$date</h2>";

debug_array($debug, $_POST);

db_change($GLOBALS['PREFIX'] . 'core', $db);

$env  = array();
$env['hid']   = $id;
$env['pid']   = get_integer('pid', 0);
$env['ord']   = value_range(0, 7, $ord);
$env['wrd']   = value_range(0, 3, $wrd);
$env['now']   = $now;
$env['act']   = $act;
$env['carr']  = $carr;
$env['priv']  = $priv;
$env['pprv']  = $pprv;
$env['revl']  = $revl;
$env['prov']  = value_range(0, 1, $prov);
$env['enab']  = value_range(0, 1, $enab);
$env['metr']  = value_range(0, 1, $metr);
$env['name']  = get_string('name', '');
$env['self']  = server_var('PHP_SELF');
$env['args']  = server_var('QUERY_STRING');
$env['serv']  = server_name($db);
$env['auth']  = $auth;
$env['debug'] = $debug;

switch ($act) {
    case 'amp':
        add_host_product($env, $db);
        break;
    case 'asp':
        add_site_product($env, $db);
        break;
    case 'ccl':
        confirm_create_local($env, $db);
        break;
    case 'cen':
        debug_census($env, $db);
        break;
    case 'cl':
        create_local($env, $db);
        break;
    case 'cmp':
        check_machine_products($env, $db);
        break;
    case 'crp':
        debug_crypt($env, $db);
        break;
    case 'csp':
        check_site_products($env, $db);
        break;
    case 'dmp':
        delete_host_product($env, $db);
        break;
    case 'dsp':
        delete_site_products($env, $db);
        break;
    case 'emp':
        edit_host_products($env, $db);
        break;
    case 'esp':
        edit_site_products($env, $db);
        break;
    case 'fak':
        fake_site($env, $db);
        break;
    case 'hst':
        debug_host($env, $db);
        break;
    case 'key':
        host_crypt($env, $db);
        break;
    case 'lst':
        list_sites($env, $db);
        break;
    case 'mch':
        list_machines($env, $db);
        break;
    case 'pmp':
        purge_host_product($env, $db);
        break;
    case 'pop':
        purge_over_product($env, $db);
        break;
    case 'psp':
        purge_site_product($env, $db);
        break;
    case 'rl':
        revert_local($env, $db);
        break;
    case 'rsp':
        revert_site_products($env, $db);
        break;
    case 'sit':
        debug_site($env, $db);
        break;
    case 'ump':
        add_host_complete($env, $db);
        break;
    case 'usp':
        add_site_complete($env, $db);
        break;
    case 'uop':
        add_over_complete($env, $db);
        break;
    case 'vmp':
        view_host_products($env, $db);
        break;
    case 'vsp':
        view_site_products($env, $db);
        break;
    case 'xxx':
        single_shot($env, $db);
        break;
    default:
        unknown_action($env, $db);
        break;
}
echo head_standard_html_footer($auth, $db);
