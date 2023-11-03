<?php

/*
Revision history:

Date        Who     What
----        ---     ----
23-Sep-02   EWB     Creation
25-Sep-02   EWB     Don't show columns with no data.
26-Sep-02   EWB     Mixed tree and table view.
30-Sep-02   EWB     Fixed a problem with the links.
30-Sep-02   EWB     Show Group, Show Asset, Show Data
 1-Oct-02   EWB     Factored out lib-alib.
 2-Oct-02   EWB     Prototype query code
 3-Oct-02   EWB     Subtables in query display result.
 4-Oct-02   EWB     Real Asset Queries
 6-Oct-02   EWB     Asset Queries work.
 7-Oct-02   EWB     Better error checking.
 7-Oct-02   EWB     Added <= and >= to sql operators
 7-Oct-02   EWB     Better error messages.
 8-Oct-02   EWB     Implemented time restrictions for queries.
 8-Oct-02   EWB     Moved new calender math functions off to lib-cmth.
 8-Oct-02   EWB     List of host machines in alphabetical order.
 8-Oct-02   EWB     Opens a new browser window for details.
 9-Oct-02   EWB     Show description of query as well as name.
 9-Oct-02   EWB     Implement Nina's new asset date code
 9-Oct-02   EWB     Fixed a quoting problem in asset_term.
10-Oct-02   EWB     Fixed an important bug the time constraints.
10-Oct-02   EWB     Factored into several smaller library files.
18-Oct-02   EWB     Sorting.
21-Oct-02   EWB     Added options for temporary queries.
 4-Dec-02   EWB     Reorginization Day
10-Dec-02   EWB     Cleaned up some position-dependant code.
10-Dec-02   EWB     Local Navigation.
16-Jan-03   EWB     Minimal quotes.
10-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change()
12-Feb-03   EWB     Increase memory limit for running queries.
12-Feb-03   EWB     Optimized for factored databases.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   NL      Move debug_note line below $debug.
25-Apr-03   EWB     Filter available sites.
29-Apr-03   EWB     Clean up access lists
30-Apr-03   EWB     User site filters.
12-May-03   EWB     Mark not cron job.
22-May-03   EWB     Quote Crusade.
17-Jun-03   EWB     Slave Database
19-Jun-03   EWB     Log Slow Queries.
20-Jun-03   EWB     No Slave Database.
23-Jul-03   EWB     Set env['link'] as appropriate.
 7-Jan-04   EWB     l-qury requires l-cron (site_intersect).
25-Mar-04   EWB     navigational links.
24-Jan-05   BJS     added: l-tabs.php.
25-Jan-05   BJS     added: tabular output option.
19-Aug-05   BJS     added: 5th arg to show_query().
30-Aug-05   BJS     added: include 'l-abld.php'
09-Nov-05   BJS     show_query() 5th arg changed to constant.
12-Dec-05   BJS     added: include 'l-grps.php'
15-Dec-05   BJs     added: include 'l-cnst.php'
24-Jan-06   AAM     Bug 3072: change memory_limit setting to use max_php_mem_mb.
                    (Note that this change was actually moved from 4.2 to 4.3
                    on 06-Mar-06.)
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.
23-Oct-07   BTE     Made changes to support ad-hoc asset queries.

*/

$title  = 'Asset Query Results';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-user.php');
include('../lib/l-alib.php');
include('../lib/l-cmth.php');
include('../lib/l-cron.php');
include('../lib/l-qtbl.php');
include('../lib/l-dids.php');
include('../lib/l-rcmd.php');
include('../lib/l-sets.php');
include('../lib/l-gsql.php');
include('../lib/l-qury.php');
//  include ( '../lib/l-slav.php'  );
include('../lib/l-head.php');
include('../lib/l-jump.php');
include('../lib/l-tabs.php');
include('../lib/l-abld.php');
include('../lib/l-grps.php');
include('../lib/l-cnst.php');
include('../lib/l-errs.php');
include('local.php');


function again_blurb(&$env)
{
    $p = 'p style="font-size:8pt"';
    return <<< XXXX

        <$p>
          Clicking on the <i>tab on</i> link will display query results in 
          tabular form with the information for each system contained in 
          a separate table.  Clicking on the <i>tab off</i> link will 
          disable this feature.
        </p>
XXXX;
}

function again($env, $grpinc)
{
    $priv = $env['priv'];
    $qid  = $env['qid'];
    $tab  = ($env['tab']) ? 'tab off' : 'tab on';
    $t    = ($env['tab']) ? 0 : 1;
    $self = $env['self'];
    $addurl = '';
    if ($grpinc) {
        $addurl = "&adhocgrp=$grpinc";
    }

    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link('query.php', 'query');
    $a[] = html_link('console.php', 'console');
    $a[] = html_link("$self?qid=$qid&tab=$t$addurl", $tab);
    if ($priv) {
        $args = $env['args'];
        $href = ($args) ? "$self?$args" : $self;
        $home = '../acct/index.php';
        $a[] = html_link($href . $addurl, 'again');
        $a[] = html_link('census.php', 'census');
        $a[] = html_link($home, 'home');
    }
    return jumplist($a);
}

/*
    |  Note ... this is a debug table.
    |
    */

function show_names($names)
{
    if ($names) {
        echo table_start();

        $head = array('did', 'name', 'ord', 'gid', 'pid', 'set');

        echo asset_data($head);

        reset($names);
        foreach ($names as $key => $data) {
            $row   = array($data['dataid']);
            $row[] = $data['name'];
            $row[] = $data['ordinal'];
            $row[] = $data['groups'];
            $row[] = $data['parent'];
            $row[] = $data['setbyclient'];

            echo asset_data($row);
        }
        echo table_end();
    }
}


function clear_rows()
{
    echo "<br clear='all'>\n";
    echo "<br clear='all'>\n";
}



function debug_clock($debug, $t)
{
    if ($debug) {
        for ($i = 0; $i < 30; $i++) {
            $d = days_ago($t, $i);
            $m = months_ago($t, $i);
            $td = datestring($d);
            $tm = datestring($m);
            echo "$i day:$td  mon:$tm<br>";
        }
    }
}


/*
    |  Main program
    */

$now = time();
$db  = db_connect();

$authuser = process_login($db);
$comp = component_installed();

$qid  = get_integer('qid', 0);
$grpinc = '';
$adhoc = get_integer('adhoc', 0);

db_change($GLOBALS['PREFIX'] . 'asset', $db);
if (($qid == 0) && ($adhoc == 1)) {
    /* Create the query first */
    $qid = ALIB_BuildAdHocQuery($db);
    /* To support sorting, the comma separated list must be made
            available of the included machine groups */
    $grpinc = GRPS_get_multiselect_values(constAdHocGroupInc);
} else if ($adhoc == 0) {
    $sql = "SELECT querytype FROM AssetSearches WHERE id=$qid";
    $row = find_one($sql, $db);
    if ($row['querytype'] == constAssetQueryTypeAdHoc) {
        $adhoc = 1;
        $grpinc = get_string('adhocgrp', '');
    }
}

if ($adhoc == 1) {
    $title = 'Ad-hoc Asset Query Results';
}

db_change($GLOBALS['PREFIX'] . 'core', $db);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);

$cid  = get_integer('cid', 0);
$o1   = get_integer('o1', 0);
$o2   = get_integer('o2', 0);
$o3   = get_integer('o3', 0);
$o4   = get_integer('o4', 0);
$dbg  = get_integer('debug', 0);
$link = get_integer('link', 1);

$self = $comp['self'];
$file = $comp['file'];
$odir = $comp['odir'];
$user = user_data($authuser, $db);
$priv   = @($user['priv_debug']) ?    1  : 0;
$debug  = @($user['priv_debug']) ?  $dbg : 0;
$filter = @($user['filtersites']) ?   1  : 0;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users
$site = '';
$carr = site_array($authuser, $filter, $db);
if ($cid > 0) {
    $site = @strval($carr[$cid]);
}

$access = array();
$access[$authuser] = db_access($carr);
$slow  = (float) server_def('slow_query_asset', 20, $db);

debug_note("slow_query_asset: $slow");
db_change($GLOBALS['PREFIX'] . 'asset', $db);
$names = asset_names($db);
$hosts = asset_machines($db);

if (($names) && ($debug)) {
    show_names($names);
}

if ($debug) {
    //      debug_clock($debug,time());
    //      debug_clock($debug,mktime(2,22,22,4,15,2001));
    //      debug_clock($debug,mktime(1,11,11,10,31,2002));
}

$env = array();
$env['db']   = $db;
$env['qid']  = $qid;
$env['now']  = $now;
$env['self'] = $self;
$env['args'] = server_var('QUERY_STRING');
$env['cron'] = 0;
$env['priv'] = $priv;
$env['file'] = $file;
$env['link'] = $link;
$env['user'] = $authuser;
$env['base'] = '';
$env['odir'] = $odir;
$env['site'] = $site;
$env['slow'] = $slow;
$env['dbid'] = 'master';
$env['names'] = $names;
$env['hosts'] = $hosts;
$env['access'] = $access;
$env['table'] = 'exec.php';
$env['tab'] = get_integer('tab', 0);
$env['adhoc'] = $adhoc;
$env['adhocgrp'] = $grpinc;

echo again_blurb($env);
echo again($env, $grpinc);

$ords = array($o1, $o2, $o3, $o4);

foreach ($ords as $k => $o) {
    $good = false;
    if (0 <= $o) {
        if ($o) {
            $name = @$names[$o]['name'];
            if ($name) {
                debug_note("sorting by $name");
                $good = true;
            }
        } else {
            $good = true;
        }
    }
    if (!$good) {
        debug_note("invalid sort $o");
        $ords[$k] = 0;
    }
}

if ($qid) {
    if (function_exists('ini_set')) {
        $mem = server_def('max_php_mem_mb', '256', $db);
        ini_set('memory_limit', $mem . 'M');
    }

    $q = show_query($env, $authuser, $qid, $ords, constAssetExec);
    $exp  = $q['exp'];
    $tree = $q['tree'];
    $ords = $q['ords'];
    if ($ords[0] > 0) {
        $q = asset_flat($env, $q, $ords);
        $q = asset_sort($env, $q, $ords);
        $tree = $q['tree'];
    }

    $msg = query_draw($env, $q, $tree);
    $qa  = "qury-act.php?id=$qid&action";
    $er  = "$qa=edit";
    $em  = '[Edit This Query]';
    $el  = html_link($er, $em) . '&nbsp;&nbsp;';

    if ($adhoc == 0) {
        $msg .= "<br clear=\"all\"><br>\n";
        if ($exp > 0) {
            $when = datestring($exp);
            debug_note("expires $exp ($when)");

            $sr = "$qa=save";
            $cr = "$qa=cancel";
            $sm = '[Save This Query]';
            $cm = '[Cancel This Query]';

            $sl = html_link($sr, $sm) . '&nbsp;&nbsp;';
            $cl = html_link($cr, $cm);
            $msg .= $sl . $el . $cl;
        } else {
            $msg .= $el;
            debug_note("never expires");
        }
        $msg .= "<br><br>\n";
    }
} else {
    $msg = missing_query();
}

echo $msg;

clear_rows();

echo again($env, $grpinc);
echo head_standard_html_footer($authuser, $db);
