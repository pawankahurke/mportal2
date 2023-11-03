<?php

/*
Revision history:

Date        Who     What
----        ---     ----
13-Aug-02   EWB     Show if a host has been changed, and if so when
15-Aug-02   EWB     Log all mysql errors
15-Aug-02   EWB     Advance preparations for client version support
15-Aug-02   EWB     Name changed to match modern conventions.
22-Aug-02   EWB     Changed "hosts" to machines.
28-Aug-02   EWB     "Last contact" --> "Last Accessed"
28-Aug-02   EWB     "Revision" --> "PS Revision"
28-Aug-02   EWB     "Last accessed" --> "Last Contact"
 3-Sep-02   EWB     "Systems" --> "Machines"
 9-Sep-02   EWB     Link to "select machine" page.
20-Sep-02   EWB     Giant refactoring
 2-Jan-03   EWB     Sort machine table by host name.
15-Jan-03   EWB     Implement "configui" spec
17-Jan-03   EWB     Access to $_SERVER variables.
17-Jan-03   EWB     Added jumptable, special title.
20-Jan-03   AAM     Removed unused $revl.
23-Jan-03   EWB     More debugging information.
 7-Feb-03   EWB     New database name
10-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change()
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
 6-Mar-03   NL      Add calls to config_navigate() & config_info()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
 2-Apr-03   EWB     Show number of machines for each site.
15-Apr-03   EWB     Factored out the jumptable.
24-Apr-03   EWB     echo jumptable.
24-Apr-03   EWB     do the site filter thing.
30-Apr-03   EWB     User site filter.
22-May-03   EWB     Quote Crusade.
 4-Jun-03   EWB     Export Site Globals
17-Jun-03   EWB     Slave Database.
20-Jun-03   EWB     No Slave database.
23-Jun-03   EWB     Don't show sites with no machines.
 5-Apr-04   EWB     Update of page titles.
 7-Apr-04   EWB     sort by sites/number of machines
18-Jan-05   EWB     use a join to find site id codes
25-Jan-05   EWB     configuration "wizard" page
 1-Feb-05   AAM     Updated wizard_menu to desired look, and added export.
11-Feb-05   EWB     Don't remap wizard request
15-Feb-05   BJS     added ms update wizard link.
16-Feb-05   EWB     a bit of documentation.
 4-Mar-05   BJS     added frequency wizard link.
 5-Jul-05   EWB     fixed a sitefilter bug.
27-Jun-05   BJS     label change for Alex.
 5-Aug-05   EWB     Configure Machine wizard link.
 7-Sep-05   BJS     Fixed bug in echo para($err)
23-Sep-05   BJS     Added customURL(), l-grps.php.
12-Oct-05   BTE     Changed reference from gconfig to core.
10-Nov-05   BTE     Added some includes.
18-Nov-05   BJS     Added scrpconf.php link.
08-Dec-05   BJS     Added scrpconf.php?custom=constPageEntryScrpConf.
29-Dec-05   BJS     Group wizard link change.
14-Apr-06   BTE     Added link to Advanced Scrip Configurator wizard.
19-Apr-06   BTE     Bug 3204: Assorted text changes for group management.
20-Apr-06   BTE     Bug 3285: User interface group management issues from
                    emails.
21-Jun-06   BTE     Bug 3466: The primary census server page is no longer in
                    order.
20-Sep-06   BTE     Added l-tiny.php.
19-Sep-06	WOH		Changed name of standard_footer.  Also added username arg.
25-Oct-06   BTE     Made some text-only changes.
31-Oct-06   BTE     Bug 3794: Finish server for first customer release of MUM
                    changes.
13-Oct-06   JRN     Added Registry management link.
03-Jan-06   BTE     Temporarily removed registry management link.
14-Feb-06   AAM     Put back registry management link.  I think it is there for
                    real now.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-cnst.php');
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-rlib.php');
include('../lib/l-user.php');
include('../lib/l-jump.php');
include('../lib/l-gsql.php');
include('../lib/l-gcfg.php');
include('../lib/l-cprg.php');
//  include ( '../lib/l-slav.php' );
include('local.php');
include('../lib/l-head.php');
include('../lib/l-tabs.php');
include('../lib/l-grps.php');
include('../lib/l-errs.php');
include('../lib/l-dsyn.php');
include('../lib/l-grpw.php');
include('../lib/l-tiny.php');


function CONF_Title($act, $site, $cid)
{
    $cfg = 'Configuration';
    switch ($act) {
        case 'wiz':
            return "$cfg Wizard";
        case 'site':
            return 'Select a Site';
        case 'host':
            return "$cfg - $site Machines";
        case 'csit':
            return 'Confirm Delete Site';
        case 'chst':
            return 'Confirm Delete Machine';
        case 'dhst':
            return 'Machine Deleted';
        case 'dsit':
            return 'Site Removed';
        default:
            return "Unknown Action ($act)";
    }
}

function worder($wrd)
{
    switch ($wrd) {
        case  0:
            return 'CONVERT(X.site USING latin1)';
        case  1:
            return 'CONVERT(X.site USING latin1) desc';
        case  2:
            return 'number desc, CONVERT(X.site USING latin1)';
        case  3:
            return 'number, CONVERT(X.site USING latin1)';
        default:
            return worder(0);
    }
}

function CONF_Order($ord)
{
    switch ($ord) {
        case  0:
            return 'host asc';
        case  1:
            return 'host desc';
        case  2:
            return 'ctime asc, host asc';
        case  3:
            return 'ctime desc, host asc';
        default:
            return CONF_Order(0);
    }
}

function CONF_Again(&$env)
{
    $self = $env['self'];
    $cust = $env['cust'];
    $priv = $env['priv'];

    $act = $env['act'];
    $cmd = "$self?act";
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    if ($act != 'wiz') {
        $href = "$cmd=wiz";
        $a[] = html_link($href, 'wizard');
    }
    if ($cmd != 'site') {
        $a[] = html_link($self, 'sites');
    }
    if ($priv) {
        $home = '../acct/index.php';
        $self = server_var('PHP_SELF');
        $args = server_var('QUERY_STRING');
        $href = ($args) ? "$self?$args" : $self;
        $a[]  = html_link('census.php', 'census');
        $a[]  = html_link($href, 'again');
        $a[]  = html_link($home, 'home');
    }
    return jumplist($a);
}


function shortdate($utime)
{
    return date('M d H:i:s', $utime);
}

function newlines($n)
{
    for ($i = 0; $i < $n; $i++) {
        echo "<br clear=\"all\">\n";
    }
}

function site_totals($sites, $hosts)
{
    echo table_header();
    echo pretty_header('Total', 2);
    echo double('Sites:', $sites);
    echo double('Machines:', $hosts);
    echo table_footer();
}


function choose_cust(&$env, $db)
{
    $wrd  = $env['wrd'];
    $cid  = $env['cid'];
    $auth = $env['auth'];
    $self = $env['self'];

    $set = array();
    $num = 0;
    $err = "No sites found for user $auth.<br>\n";
    if ($auth) {
        $filt = '';
        if ($env['user']['filtersites']) {
            $filt = " and C.sitefilter = 1\n";
        }
        $wrds = worder($wrd);
        $qu  = safe_addslashes($auth);
        $sql = "select X.site as cust,\n"
            . " count(R.censusid) as number,\n"
            . " C.id as cid,\n"
            . " G.mgroupid as mgroupid,\n"
            . " G.name as name from\n"
            . " Revisions as R,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as X,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as B\n"
            . " where C.username = '$qu'\n"
            . $filt
            . " and C.customer = X.site\n"
            . " and X.id = R.censusid\n"
            . " and M.censusuniq = X.censusuniq\n"
            . " and G.mgroupuniq = M.mgroupuniq\n"
            . " and G.mcatuniq = B.mcatuniq\n"
            . " and B.category='Site'\n"
            . " group by C.id\n"
            . " order by $wrds";
        $set = find_many($sql, $db);
    }
    if ($set) {
        $num = safe_count($set);
        $err = '';
    }
    if ($num > 0) {
        $n = 0;
        reset($set);
        foreach ($set as $key => $row) {
            $n += $row['number'];
        }

        site_totals($num, $n);

        $w    = "$self?wrd";
        $sref = ($wrd == 0) ? "$w=1" : "$w=0";
        $nref = ($wrd == 2) ? "$w=3" : "$w=2";
        $sdef = html_link($sref, 'Site Name');
        $ndef = html_link($nref, 'Number of Machines');
        $head = array('Action', $sdef, $ndef);
        echo table_header();
        echo table_data($head, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $site = $row['cust'];
            $num  = $row['number'];
            $cid  = $row['cid'];
            $cmd  = "$self?cid=$cid&act";
            $mgroupid = $row['mgroupid'];
            $name = $row['name'];

            $a   = array();
            $a[] = html_link("$cmd=host", '[view machines]');
            $a[] = html_link("export.php?cid=$cid", '[export]');
            $a[] = html_link("$cmd=csit", '[delete]');
            $a[] = html_link(
                "scrpconf.php?act=scop&cid=$cid&custom=8"
                    . "&scop=3&mgroupid=$mgroupid&group_name=$name&hid=0"
                    . "&pscop=3&site=&censusid=&snum=&level=1",
                '[configure site]'
            );
            $act = join("<br>\n", $a);
            $args = array($act, $site, $num);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
    if ($auth) {
        newlines(2);
        /* Now, display each group this user owns */
        $sql = "SELECT MachineGroups.name, MachineGroups.mgroupid, "
            . "count(MachineGroupMap.mgmapid) as number FROM MachineGroups"
            . " LEFT JOIN MachineGroupMap ON (MachineGroups.mgroupuniq="
            . "MachineGroupMap.mgroupuniq) WHERE username='"
            . $qu . "' GROUP BY MachineGroups.name ORDER BY "
            . "CONVERT(MachineGroups.name USING latin1)";
        $set = find_many($sql, $db);
        if ($set) {
            echo "User-defined groups:<p>";
            $head = array('Action', 'Group');
            echo table_header();
            echo table_data($head, 1);

            foreach ($set as $key => $row) {
                $mgroupid = $row['mgroupid'];
                $link = GRPW_GetGroupScripLink($mgroupid);
                if ($row['number'] > 0) {
                    $confLink = html_link($link, '[configure group]');
                } else {
                    $confLink = "&nbsp;";
                }
                $args = array($confLink, $row['name']);
                echo table_data($args, 0);
            }
            echo table_footer();
        }
    }
    newlines(2);
    return $err;
}


function choose_host(&$env, $db)
{
    $self = $env['self'];
    $cid  = $env['cid'];
    $ord  = $env['ord'];
    $cust = $env['cust'];
    $auth = $env['auth'];
    $filt = '';
    $err  = "No machines found for site $cust<br>";
    $order = CONF_Order($ord);

    $qa  = safe_addslashes($auth);
    $sql = "select X.host, R.* from\n"
        . " Revisions as R,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as X,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers as C\n"
        . " where C.id = $cid\n"
        . " and C.username = '$qa'\n"
        . " and X.site = C.customer\n"
        . " and X.id = R.censusid\n"
        . $filt
        . " order by $order";
    $set = find_many($sql, $db);
    if ($set) {
        $num = safe_count($set);

        site_totals(1, $num);

        $err  = '';

        $act  = 'Action';
        $host = 'Machine';
        $when = 'Last Contact';
        $pend = '<i>&nbsp;Changed&nbsp;</i>';
        $o    = "$self?act=host&cid=$cid&ord";

        $href = ($ord == 0) ? "$o=1" : "$o=0";
        $wref = ($ord == 2) ? "$o=3" : "$o=2";

        $hlink = html_link($href, $host);
        $wlink = html_link($wref, $when);

        newlines(2);
        $args = array('Action', $hlink, $wlink, $pend);
        $cols = safe_count($args);
        $rows = safe_count($set);
        $text = "$cust &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $hid   = $row['censusid'];
            $ctime = $row['ctime'];
            $stime = $row['stime'];
            if ($stime > $ctime) {
                $pend  = 'Yes -- ';
                $pend .= shortdate($stime);
            } else {
                $pend = 'No';
            }
            $del  = "$self?act=chst&hid=$hid";
            $a    = array();
            $a[]  = html_link("config.php?hid=$hid", '[configure Scrips]');
            $a[]  = html_link("export.php?hid=$hid", '[export]');
            $a[]  = html_link($del, '[delete]');
            $act  = join("<br>\n", $a);
            $when = date('m/d H:i:s', $ctime);
            $host = $row['host'];
            $args = array($act, $host, $when, $pend);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
    return $err;
}


function select_site(&$env, $db)
{
    echo mark('machines');
    echo CONF_Again($env);
    $err = choose_cust($env, $db);
    echo mark('end');
    echo CONF_Again($env);
    $env['err'] = $err;
}

function find_site_cid($cid, $auth, $db)
{
    $row = array();
    if (($cid > 0) && ($auth != '')) {
        $qu  = safe_addslashes($auth);
        $sql = "select * from \n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers\n"
            . " where id = $cid\n"
            . " and username = '$qu'";
        $row = find_one($sql, $db);
    }
    return $row;
}


function confirm_site(&$env, $db)
{
    echo CONF_Again($env);
    $cid  = $env['cid'];
    $auth = $env['auth'];

    $row = find_site_cid($cid, $auth, $db);
    if ($row) {
        $self = $env['self'];
        $site = $row['customer'];
        $yref = "$self?cid=$cid&act=dsit";
        $ylnk = html_link($yref, 'Yes, delete it.');
        $nlnk = html_link($self, "No, don't do anything");
        /*
 |  This will remove all the site specific values from the site
 |  administration database including all of its associated
 |  machines and all of their local and global variables, as
 |  well as any pending updates.
 */
        echo <<< SITE

            This will remove all the site specific values from
            the site<br>administration database, including all
            of its associated<br>machines and all of their local
            and global variables, as<br>well as any pending
            updates.<br>
            <p>Delete site <b>$site</b>?</p>
            <p>$ylnk</p>
            <p>$nlnk</p>
SITE;
    }
    echo CONF_Again($env);
}


function confirm_host(&$env, $db)
{
    echo CONF_Again($env);
    $auth = $env['auth'];
    $hid  = $env['hid'];
    debug_note("confirm_host hid:$hid, auth:$auth");
    $row  = full_revl($hid, $auth, $db);
    if ($row) {
        $self = $env['self'];
        $site = $row['site'];
        $host = $row['host'];
        $name = ucwords($host);
        $yref = "$self?act=dhst&hid=$hid";
        $ylnk = html_link($yref, "Yes, delete $host.");
        $nlnk = html_link($self, "No, don't do anything.");

        /*
 |  This will remove all the machine specific Scrip configuration values
 |  from the site administration database for the machine dupont,
 |  including all of its local variables, local overrides, and pending
 |  updates.  However this will not affect the global variables for
 |  the HFN Development site.
 */
        echo <<< HOST

            This will remove all the machine specific Scrip
            configuration values<br>from the site administration
            database for the machine <b>$name</b>,<br>including
            all of its local variables, local overrides, and
            pending<br>updates.&nbsp;&nbsp;However this will
            not affect the global variables for<br>
            the <b>$site</b> site.<br>

            <p>Delete records for machine <b>$name</b>?</p>
            <p>$ylnk</p>
            <p>$nlnk</p>
HOST;
    }
    echo CONF_Again($env);
}


/*
    |  This removes all the configuration for the
    |  entire site ... however the machines and
    |  machine groups themselves are left intact.
    */

function delete_site(&$env, $db)
{
    echo CONF_Again($env);
    $num  = 0;
    $cid  = $env['cid'];
    $auth = $env['auth'];
    $row = find_site_cid($cid, $auth, $db);
    if ($row) {
        $site = $row['customer'];
        debug_note("cid:$cid site:$site auth:$auth");
        $num = purge_config_site($site, $db);
        if ($num) {
            $txt = "Site <b>$site</b> has been removed from the database.";
            echo para($txt);
            $txt = "config: site '$site' removed (n:$num) by $auth.";
            debug_note($txt);
            logs::log(__FILE__, __LINE__, $txt, 0);
        }
    }

    if (!$num) {
        echo para('Nothing has changed.');
    }
    echo CONF_Again($env);
}


/*
    |  This removes all the configuration information for
    |  the specified machine.
    */

function delete_host(&$env, $db)
{
    echo CONF_Again($env);
    $auth = $env['auth'];
    $hid  = $env['hid'];
    $revl = full_revl($hid, $auth, $db);
    $num  = 0;
    if ($revl) {
        $host = $revl['host'];
        $site = $revl['site'];
        $hid  = $revl['censusid'];
        debug_note("delete_host $host at $site ($hid)");
        $num  = purge_config_host($hid, $site, $host, $db);
        if ($num) {
            $txt  = "Records for machine <b>$host</b> h"
                . "ave been removed from the database.";
            echo para($txt);
            $txt  = "config: $host removed (n:$num) from $site by $auth.";
            debug_note($txt);
            logs::log(__FILE__, __LINE__, $txt, 0);
        }
    }

    if (!$num) {
        echo para('Nothing has changed.');
    }
    echo CONF_Again($env);
}


function select_host(&$env, $db)
{
    echo mark('sites');
    echo CONF_Again($env);
    $err = choose_host($env, $db);
    echo mark('end');
    echo CONF_Again($env);
    $env['err'] = $err;
}


function CONF_SimpleList(&$act, &$txt, &$doc)
{
    echo "\n\n<ol>\n";
    reset($txt);
    foreach ($txt as $key => $msg) {
        $cmd = html_link($act[$key], $msg) . $doc[$key];
        echo "<li>$cmd</li>\n";
    }
    echo "</ol>\n";
}


function CONF_WizardMenu(&$env, $db)
{
    $act = array();
    $txt = array();
    $doc = array();
    $self = $env['self'];

    $act[] = 'remote.php';
    $txt[] = 'Configure remote control';
    $doc[] = '';

    $act[] = '../acct/export.php';
    $txt[] = 'Export data';
    $doc[] = '';

    $act[] = 'intruder.php';
    $txt[] = 'Enable-disable malware protection';
    $doc[] = ' - <i>This action enables-disables Scrips 27 and 232</i>';

    $act[] = 'patch.php';
    $txt[] = 'Enable-disable Microsoft updates';
    $doc[] = ' - <i>This action enables-disables Scrip 237</i>';

    $act[] = 'cfgschd.php';
    $txt[] = 'Scrip execution frequency';
    $doc[] = ' - <i>This action changes Scrip 177 frequency</i>';

    $cst   = customURL(constPageEntrySites);
    $act[] = "groups.php?$cst";
    $txt[] = 'Group management';
    $doc[] = '';

    $cst   = customURL(constPageEntryScrpConf);
    $act[] = "scrpconf.php?$cst";
    $txt[] = 'Scrip configuration';
    $doc[] = ' - <i>Configure Scrips for all sites, a single site, a single machine or a group</i>';

    $cst   = customURL(constPageEntryScrpConf);
    $act[] = "scrpconf.php?$cst&level=1";
    $txt[] = 'Advanced Scrip configuration';
    $doc[] = ' - <i>Configure Scrips for all sites, a single site, a single machine or a group</i>';

    $act[] = "../regmgmt/rmgt.php";
    $txt[] = 'Registry management';
    $doc[] = ' - <i>Configure the Registry management wizard for All sites</i>';

    //        $act[] = '../patch/insw.php';
    //        $txt[] = 'Microsoft update management status analysis';
    //        $doc[] = '';

    echo CONF_Again($env);
    echo para('What do you want to do?');
    CONF_SimpleList($act, $txt, $doc);
    echo CONF_Again($env);
}


/*
    |  Main program
    */

$db = db_connect();
$auth = process_login($db);
$comp = component_installed();

$cid = get_integer('cid', 0);
$ord = get_integer('ord', 0);
$hid = get_integer('hid', 0);
$wrd = get_integer('wrd', 0);
$dbg = get_integer('debug', 1);
$act = get_string('act', 'site');
$sid = 0;

$user   = user_data($auth, $db);
$filter = @($user['filtersites']) ? 1 : 0;
$priv   = @($user['priv_debug']) ?  1 : 0;
$debug  = ($priv) ? $dbg : 0;

$cust = '';
$host = '';
$vers = '';
$carr = array();
$sarr = site_array($auth, $filter, $db);

if ($sarr) {
    reset($sarr);
    foreach ($sarr as $key => $site) {
        $carr[$site] = $key;
    }
}

if ($carr) {
    if (safe_count($carr) == 1) {
        reset($carr);
        foreach ($carr as $key => $data) {
            $cust = $key;
            $cid  = $data;
        }
    }
    if (empty($cust) && ($cid > 0)) {
        reset($carr);
        foreach ($carr as $key => $data) {
            if ($cid == $data) {
                $cust = $key;
            }
        }
    }
}


$self = server_var('PHP_SELF');

$name = CONF_Title($act, $cust, $cid);

$local_nav = config_navigate($cid, $hid, $sid);
$local_inf = config_info($auth, $vers, $host);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($name, $comp, $auth, $local_nav, $local_inf, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

debug_note("debug:$debug, cid:$cid, act:$act ord:$ord auth:$auth self:$self");

if (!mysqli_select_db($db, core)) {
    $act = 'fail';
}

$env = array();
$env['ord']  = $ord;
$env['wrd']  = $wrd;
$env['cid']  = $cid;
$env['hid']  = $hid;
$env['act']  = $act;
$env['err']  = '';
$env['auth'] = $auth;
$env['self'] = $self;
$env['cust'] = $cust;
$env['priv'] = $priv;
$env['user'] = $user;

switch ($act) {
    case 'site':
        select_site($env, $db);
        break;
    case 'host':
        select_host($env, $db);
        break;
    case 'wiz':
        CONF_WizardMenu($env, $db);
        break;
    case 'fail':
        dbase_error($env, $db);
        break;
    case 'chst':
        confirm_host($env, $db);
        break;
    case 'csit':
        confirm_site($env, $db);
        break;
    case 'dhst':
        delete_host($env, $db);
        break;
    case 'dsit':
        delete_site($env, $db);
        break;
}

if ($env['err']) {
    $err = $env['err'];
    echo para($err);
}

echo head_standard_html_footer($auth, $db);
