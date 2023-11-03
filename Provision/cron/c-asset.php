<?php

/*
Revision history:

Date        Who     What
----        ---     ----
27-Sep-02   EWB     Created as duplicate of c-report.php
10-Oct-02   EWB     Runs asset Queries
11-Oct-02   EWB     Mails asset queries
11-Oct-02   EWB     Better base_name
17-Oct-02   EWB     Flattened tables
17-Oct-02   EWB     Sorted tables
24-Oct-02   EWB     Collect statistics before flattening table
24-Oct-02   EWB     Delete immediate reports after running them
25-Oct-02   EWB     Perhaps we want to flatten first after all.
28-Oct-02   EWB     Finally sorted out the statistics generation.
 5-Nov-02   EWB     Disable useless reports.
 5-Dec-02   EWB     Reorginization Day
13-Dec-02   EWB     Report mail from $SERVER_NAME
13-Jan-03   EWB     Handle quotes in report names
13-Jan-03   EWB     Minimal quotes.
16-Jan-03   EWB     Access to $_SERVER variables.
25-Jan-03   EWB     report_once doesn't need umin/umax.
27-Jan-03   EWB     Ask for 32M memory_limit
27-Jan-03   EWB     Reduce memory usage.
31-Jan-03   EWB     Uses new server options.
12-Feb-03   EWB     db_change();
12-Feb-03   EWB     Calculates list of owned machines on the fly.
21-Feb-03   EWB     standard_html_footer()
 6-Mar-03   NL      Uses output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   EWB     Uses server_def for ssl and port option.
21-Apr-03   EWB     OEM page footers.
30-Apr-03   NL      Implement Site Filtering:
                    Incl l-sitflt.php.
                    Delete RptSiteFilter records when deleting Report record.
                    Get siteaccesstree and sitefiltertree (arrays not lists).
                    In report_once, add $id to env[].
30-Apr-03   EWB     Don't calculate access for reports we aren't running.
 2-May-03   NL      Rather than setting $id to 0 for proxy reports, use row['owner']
                        = [0|1] to distinguish btw proxy and owner-run reports.
                    Correct var names. Proxy user array is called $users not $owners
                        and userdata array is now called $userdata not $users.
                    Optimization:
                     - Only pass report ids for reports where sitefilter is ON.
12-May-03   EWB     Mark cron job.
15-May-03   EWB     Asset Change Reports
16-May-03   EWB     machine_list() takes umin,umax,log as arguments.
21-May-03   EWB     Moved shared code into l-rtxt.php.
21-May-03   EWB     Better headers for asset change reports.
22-May-03   EWB     Asset change min/max times.
11-Jun-03   EWB     Support Replicated Database.
16-Jun-03   EWB     Uses slave library.
19-Jun-03   EWB     No, don't support replicated database.
24-Jun-03   EWB     Fix damaged machines.
25-Jun-03   EWB     Ignore damaged machines (until they are fixed)
17-Jul-03   EWB     File Output.
18-Jul-03   EWB     Fixed links in output file report.
18-Jul-03   EWB     Server Option for File Expiration Date.
23-Jul-03   EWB     New file type for Change Reports.
 5-Sep-03   EWB     Record machine count when saving file on server.
12-Sep-03   EWB     get image data, not just filename.
22-Sep-03   EWB     implement jpeg_quality server option.
22-Oct-03   EWB     Improved report scheduling.
27-Oct-03   EWB     allow setting memory limit from command line.
30-Oct-03   NL      Exclude reports on inactive sites.
30-Oct-03   NL      Moved find_active_sites() to l-cron.php.
 2-Nov-03   EWB     Always run local reports first.
 5-Oct-03   NL      Correct error message for inactive sites.
13-Nov-03   NL      html_reportsummary_data(): add new 4th argument.
20-Nov-03   EWB     ignore_user_abort()
29-Dec-03   EWB     Always run immediate reports at this cron time.
 7-Jan-04   EWB     case-insensitive site calculations.
13-Feb-03   EWB     Uses server_name();
16-Feb-03   EWB     server_name variable.
13-Apr-04   EWB     Killing a stuck report sends email.
14-Apr-04   EWB     Also notifies the intended recipients.
20-Oct-04   BJS     Added include_user, include_text & subject_text, into email subject.
16-Nov-04   EWB     content-free asset reports.
17-Nov-04   EWB     report runtime for long running reports.
 8-Dec-04   BJS     Added EWB's find_asset_sites, +1 argv to html_stats.
10-Dec-04   BJS     Added skip_owner: option to skip a global report for the owner.
17-Dec-04   BJS     Removed sites from report, added to email body.
25-Jan-05   BJS     Added l-tabs.php, and row[tabular] to env[tab] for query_draw.
31-Jan-05   BJS     row arg to file_links.
 2-Mar-05   EWB     update_stuck does not need bias argument.
17-Mar-05   EWB     fixed a problem killing a stuck report.
29-Mar-05   BJS     anchor/link fixes.
12-Jul-05   BJS     added l-fprc, save reports to temp file.
14-Jul-05   BJS     fwrite changed to my_write, improved error checks.
15-Jul-05   BJS     genreport returns integer.
18-Jul-05   BJS     a fatal error calls report_cleanup().
19-Jul-05   BJS     extracted make_env() from build_env().
17-Aug-05   BJS     added ftp_file and xml option.
18-Aug-05   BJS     improved ftp_file error checking, genreport() changes.
23-Aug-05   BJS     auto include .xml to all xml ftp filenames.
24-Aug-05   BJS     ftp_file fix for url with no path, close/unlink localfile.
25-Aug-05   BJS     xml ftp passive option.
 1-Aug-05   BJS     added l-abld.php
 6-Sep-05   BJS     added build_pidtable_name().
 7-Nov-05   BJS     added error supression to ['user_data'][$user]['report_mail']
 9-Nov-05   BJS     $file_type comparision on constant.
02-Dec-05   BJS     Added l-grps.php, group_include group_exclude.
05-Dec-05   BJS     Removed filtersites references.
16-Dec-05   BJS     added l-cnst.php
24-Jan-06   AAM     Bug 3072: change memory_limit setting to use max_php_mem_mb.
                    (Note that this change was actually moved from 4.2 to 4.3
                    on 06-Mar-06.)
04-Apr-06   AAM     Oops.  Typo in last change, was causing failure.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

/*
    |  Asset reports are produced in four stages.
    |
    |  1. Generate the initial table, done by l-qury.php
    |  2. Flatten table ... this means that when sorting on
    |     multivalued columns, generate duplicate rows, one
    |     for each value.
    |  3. sort the table.
    |  4. display the table.
    |
    */

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-foot.php');
include('../lib/l-head.php');
include('../lib/l-rprt.php');
include('../lib/l-cmth.php');
include('../lib/l-alib.php');
include('../lib/l-gsql.php');
include('../lib/l-base.php');
include('../lib/l-cust.php');
include('../lib/l-dids.php');
include('../lib/l-qtbl.php');
include('../lib/l-qury.php');
include('../lib/l-graf.php');
include('../lib/l-cron.php');
include('../lib/l-chng.php');
include('../lib/l-jump.php');
include('../lib/l-rtxt.php');
include('../lib/l-afix.php');
include('../lib/l-mime.php');
include('../lib/l-tabs.php');
include('../lib/l-fprc.php');
include('../lib/l-abld.php');
include('../lib/l-grps.php');
include('../lib/l-cnst.php');

define('constPurgeLock',    'purge_lock');
define('constReportLock',   'asset_lock');
define('constReportPid',    'asset_pid');
define('constReportTimo',   'asset_timeout');

/*------------------------------------------------------------------*\
 |                                                                  |
 |      UTILTY FUNCTIONS                                            |
 |                                                                  |
\*------------------------------------------------------------------*/

function serious_message($msg)
{
    logs::log(__FILE__, __LINE__, "assets: $msg", 0);
    echo "$msg<br>\n";
    flush();
}


function again()
{
    $self = server_var('PHP_SELF');
    $args = server_var('QUERY_STRING');
    $href = ($args) ? "$self?$args" : $self;
    $dbg  = 'php?debug=1';

    $a   = array();
    $a[] = html_link($href, 'again');
    $a[] = html_link('../acct/index.php', 'home');
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link("c-report.$dbg", 'report');
    $a[] = html_link("c-notify.$dbg", 'notify');
    $a[] = html_link('../asset/d-report.php', 'list');
    return jumplist($a);
}



/*-------------------------------------------------------------------*\
 |                                                                   |
 |  ACTION (And Supporting) FUNCTIONS                                |
 |                                                                   |
\*-------------------------------------------------------------------*/

function compare_numeric($a, $b)
{
    $aa = (int) $a;
    $bb = (int) $b;
    if ($aa == $bb) {
        return 0;
    }
    return ($aa > $bb) ? -1 : 1;
}


/*
    |  associative sort, first by value,
    |  and then within each value, sort
    |  by key.
    */

function aksort($a)
{
    $b = array();
    $c = array();

    if ($a) {
        uasort($a, 'compare_numeric');
        reset($a);
        foreach ($a as $k => $d) {
            $b[$d][] = $k;
        }

        reset($b);
        foreach ($b as $k1 => $d1) {
            $p = $d1;
            asort($p);
            reset($p);
            foreach ($p as $k2 => $d2) {
                $c[$d2] = $k1;
                //      debug_note("aksort $d2 $k1");
            }
        }
    }
    return $c;
}

function plural($n, $name)
{
    $word = ($n == 1) ? $name : $name . 's';
    return "$n $word";
}


function update_report(&$env, &$row, $db)
{
    $num = 0;
    $id  = $row['id'];
    if ($id > 0) {
        $bias = $env['bias'];
        $now  = $env['now'];
        $next = next_cycle($row, $now) + $bias;
        $last = time();
        $dn   = date('m/d H:i', $next);
        $dl   = date('m/d H:i', $last);
        $sql  = "update AssetReports set\n"
            . " last_run = $last, -- $dl\n"
            . " next_run = $next, -- $dn\n"
            . " this_run = 0,\n"
            . " retries  = 0\n"
            . " where id = $id\n"
            . " and next_run < 0\n"
            . " and this_run > 0\n"
            . " and enabled = 1";
        $res  = redcommand($sql, $db);
        $num  = affected($res, $db);
    }
    return $num;
}


/*
    |  Mark the report as executing.
    */

function claim_report($id, $db)
{
    $num = 0;
    if ($id > 0) {
        $now = time();
        $sql = "update AssetReports set\n"
            . " next_run = -1,\n"
            . " this_run = $now,\n"
            . " retries  = retries+1\n"
            . " where id = $id\n"
            . " and next_run > 0\n"
            . " and enabled = 1";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}

function disable_report(&$env, $id)
{
    $res = false;
    if ($id > 0) {
        $db  = $env['mdb'];
        $tbl = $env['table'];
        $sql = "update $tbl set enabled = 0 where id = $id";
        $res = command($sql, $db);
    }
    return $res;
}

function delete_report(&$env, $id)
{
    $res = false;
    if ($id > 0) {
        $db  = $env['mdb'];
        $tbl = $env['table'];

        $sql = "delete from $tbl where id = $id";
        $res = command($sql, $db);
    }
    return $res;
}


function find_stuck_report($db)
{
    $sql = "select * from AssetReports\n"
        . " where next_run < 0\n"
        . " and enabled = 1";
    return find_many($sql, $db);
}


function find_report_aid($id, $db)
{
    $sql = "select * from AssetReports\n"
        . " where id = $id";
    return find_one($sql, $db);
}




function html_assetsummary_subheader($name1, $key1, $data1, $name2, $id)
{
    $name2_uc = ucwords($name2);
    $anchor1  = s_anchor($id, $key1);
    return <<< HERE

<tr>
 <td colspan="2" valign="top">
  <a name="$anchor1"></a>
  <font face="verdana,helvetica" size="2">
  <b>
   <font color="333399">$name1:</font> $key1
   <font color="333399">(</font>$data1
   <font color="333399">items)</font>
  </b>
  </font>
 </td>
 <td valign="top" align="right" nowrap>
  <br>
 </td>
</tr>
<tr>
 <td valign="top" width="4%">
  <br>
 </td>
 <td valign="top" width="80%">
   <font face="verdana,helvetica" size="2" color="333399">
    <b>$name2_uc</b>
   </font>
 </td>
 <td valign="top" align="right" width="16%">
  <font face="verdana,helvetica" size="2" color="333399">
   <b>Counts</b>
  </font>
 </td>
</tr>

HERE;
}


function event_summaries(&$env, &$row, &$imgs, $count1, $index1, $name1, $name2)
{
    $odir   = $env['odir'];
    $jpgq   = $env['jpgq'];
    $server = $env['server'];
    $file   = $row['file'];
    $format = $row['format'];
    $id     = $row['id'];
    $base   = ($file) ? "/$odir" : $env['base'];
    $chart  = charting($row);
    $msg    = html_eventsummary_header('Item');

    $pagebreak = 'off';
    reset($count1);
    foreach ($count1 as $key1 => $data1) {
        if ($key1) {
            $count2 = $index1[$key1];

            # create and display the chart
            if (($chart) && (safe_count($count2))) {
                $iref = report_iref($row, $count2, $name2, $jpgq, $base, $imgs, $server);
                $msg .= html_pagebreak($pagebreak);
                $msg .= html_eventsummary_chart($key1, $iref, $format);
                $pagebreak = 'on';
            }

            # display subheadings

            $msg .= html_assetsummary_subheader($name1, $key1, $data1, $name2, $id);

            # display rows of data
            if (safe_count($count2)) {
                reset($count2);
                foreach ($count2 as $key => $data) {
                    $msg .= html_eventsummary_data($key, $data);
                }
                $msg .= html_separator(3, 'back to top');
            }
        }
    }
    return $msg;
}


function process_data(&$env, &$report, &$imgs, $q)
{
    $db     = $env['db'];
    $odir   = $env['odir'];
    $jpgq   = $env['jpgq'];
    $names  = $env['names'];
    $server = $env['server'];

    $file   = $report['file'];
    $format = $report['format'];
    $id     = $report['id'];

    $base  = ($file) ? "/$odir" : $env['base'];
    $tree  = $q['tree'];
    $dids  = $q['dids'];
    $ords  = $q['ords'];

    $o1 = $ords[0];
    $o2 = $ords[1];

    $msg  = '';
    $rows = 0;

    $n1 = 0;
    $n2 = 0;

    $distinct1 = '';
    $distinct2 = '';
    $unknown = '(unknown)';
    $name1  = $unknown;
    $name2  = $unknown;

    $count1 = array();
    $count2 = array();
    $index1 = array();

    $chart = charting($report);
    $name1 = ($o1) ? $names[$o1]['name'] : $unknown;
    $name2 = ($o2) ? $names[$o2]['name'] : $unknown;


    if ($o1) {
        foreach ($tree as $ind => $row) {
            $mid = $row[0];
            $tmp = @strval($row[$o1][1]);
            $v1  = ($tmp == '') ? $unknown : $tmp;
            $count1[$v1][$mid] = true;
            if ($o2) {
                $tmp = @strval($row[$o2][1]);
                $v2  = ($tmp == '') ? $unknown : $tmp;
                $count2[$v1][$v2][$mid] = true;
            }
        }

        /*
            |  Count up how many machines have
            |  each distinct v1.  Also, for
            |  each distint v1, how many machines
            |  have a distinct v2.
            */

        reset($count1);
        foreach ($count1 as $key => $data) {
            $tmp = safe_count($data);
            $count1[$key] = $tmp;
        }

        reset($count2);
        foreach ($count2 as $key1 => $data1) {
            reset($data1);
            foreach ($data1 as $key2 => $data2) {
                $index1[$key1][$key2] = safe_count($data2);
            }
        }

        unset($count2);

        $n1 = safe_count($count1);
        if ($n1 > 0) {
            $msg .= html_distinct($n1, $name1);
            $count1 = aksort($count1);

            # create and display the chart
            if ($chart) {
                $iref = report_iref($report, $count1, $name1, $jpgq, $base, $imgs, $server);
                $msg .= html_reportsummary_subheader($iref, $name1, 'Items', $format);
            }

            # display the rows of data
            $n = 0;
            reset($count1);
            foreach ($count1 as $key1 => $data1) {
                $n++;
                $msg .= html_reportsummary_data($n, $key1, $data1, '', $id);
            }

            if ($o2) {
                $msg .= event_summaries($env, $report, $imgs, $count1, $index1, $name1, $name2);
            }
        }
    }

    $msg .= html_close_table();
    return $msg;
}



function report_debug(&$env, $row, $email)
{
    if ($env['debug']) {
        $rnow     = time();
        $cron     = $env['now'];
        $asset    = $env['asset'];
        $event    = $env['event'];

        $id       = $row['id'];
        $owner    = $row['owner']; // whether owner or proxy user
        $cycle    = $row['cycle'];
        $name     = $row['name'];
        $username = $row['username'];
        $hour     = $row['hour'];
        $minute   = $row['minute'];
        $format   = $row['format'];
        $defmail  = $row['defmail'];
        $file     = $row['file'];
        $last     = $row['last_run'];
        $next     = $row['next_run'];
        $global   = $row['global'];
        $searchid = $row['searchid'];
        $umin     = $row['umin'];
        $umax     = $row['umax'];
        $log      = $row['log'];
        $change   = $row['change_rpt'];
        $tab      = $row['tabular'];
        $include_user = $row['include_user'];
        $include_text = $row['include_text'];
        $subject_text = $row['subject_text'];

        $r_wday  = @$row['wday'];   // only for weekly reports
        $r_mday  = @$row['mday'];   // only for monthly reports
        $o1      = @$row['order1'];
        $o2      = @$row['order2'];
        $o3      = @$row['order3'];
        $o4      = @$row['order4'];

        $drnow  = datestring($rnow);
        $dcron  = datestring($cron);
        $dnext  = datestring($next);
        $dlast  = datestring($last);
        $dumin  = datestring($umin);
        $dumax  = datestring($umax);

        echo "<p><b>\n";
        echo "cron: ($cron) $dcron<br>\n";
        echo "rnow: ($rnow) $drnow<br>\n";
        echo "next: ($next) $dnext<br>\n";
        echo "last: ($last) $dlast<br>\n";
        echo "umin: ($umin) $dumin<br>\n";
        echo "umax: ($umax) $dumax<br>\n";
        echo "name($name) email($email) format($format) username($username) owner($owner) id($id)<br>\n";
        echo "cycle($cycle) hour($hour) minute($minute) wday($r_wday) mday($r_mday)<br>\n";
        echo "o1($o1) o2($o2) o3($o3) o4($o4)<br>\n";
        echo "defmail($defmail) file($file)<br>\n";
        echo "searchid($searchid) global($global)<br>\n";
        echo "change($change) log($log)<br>\n";
        echo "include_user($include_user) include_text($include_text) subject_text($subject_text)<br>\n";
        echo "tabular($tab)<br>\n";
        echo "</b></p>\n";

        echo "<br clear=\"all\">\n";
        echo "<br clear=\"all\">\n";
    }
}


function html_tnow($tnow)
{
    $rows = array();
    html_append($rows, 'Report Date', $tnow);
    return html_data($rows);
}


// event reports just have rows

function html_reportsummary_header($rows, $cols)
{
    $msg  = html_new_pagetable();
    $msg .= <<< HERE

<tr>
 <td colspan="3" bgcolor="#333399">
  <a name="RS"></a>
  <font face="verdana,helvetica" color="white" size="2">
   <b>Report Summary</b>
  </font>
 </td>
</tr>

<tr>
 <td colspan="3">
  <font face="verdana,helvetica" size=2 color="333399">
   This report covers <font color="000000">$rows</font>
   machines and <font color="000000">$cols</font> fields.
  </font>
 </td>
</tr>

HERE;

    return $msg;
}

function find_asset_sites($mids, $db)
{
    $out = array();
    if ($mids) {
        $txt = join(',', $mids);
        $sql = "select distinct cust\n"
            . " from Machine\n"
            . " where machineid in ($txt)\n"
            . " order by cust";
        $set = find_many($sql, $db);
        foreach ($set as $key => $row) {
            $out[] = $row['cust'];
        }
    }
    return $out;
}


function html_rule_table_stats($title, $auth, $dest)
{
    $msg  = html_rule();
    $msg .= html_table(3, 0, 'C0C0C0', 0);
    $msg .= html_stats($title, $auth, $dest);
    return $msg;
}


function change_build_message($umin, $umax)
{
    $tmin  = ($umin) ? datestring($umin) : 'First Log';
    $tmax  = ($umax) ? datestring($umax) : 'Last Log';
    if (($umin) && ($umax) && ($umin < $umax)) {
        $hours = ($umax - $umin) / 3600;
    } else {
        $hours = 0;
    }
    $msg .= html_empty_row(2);
    $msg .= html_times($tmin, $tmax, $tnow, $hours);
    $msg .= html_close_table();
    $msg .= html_rule();

    return $msg;
}

function change_close_message($tnow, $grp1, $grp2, $grp3, $grp4, $rows, $cols)
{
    $msg  = html_tnow($tnow);
    $msg .= html_empty_row(2);
    $msg .= html_order_params($grp1, $grp2, $grp3, $grp4);
    $msg .= html_close_table();
    $msg .= html_rule();
    $msg .= html_reportsummary_header($rows, $cols);
    return $msg;
}


function change_desc_message($q, $mids)
{
    $name = $q['name'];
    $text = $q['text'];
    $when = $q['when'];

    $msg  = html_close_table();
    $msg .= asset_header($name);
    if ($text) {
        $msg .= show_description($text);
    }

    if ($when) {
        $msg .= show_when($when);
    }

    if ($mids) {
        $many  = plural(safe_count($mids), 'machine');
        $msg  .= "Query found $many.";
    }
    return $msg;
}


function machine_count($env, $umin, $umax, $log, $mids, $sites, $db)
{
    $set = array();
    if (($mids) && ($sites)) {
        $access = db_access($sites);
        $tmp = join(',', $mids);
        $sql = "select * from Machine\n"
            . " where provisional = 0\n"
            . " and machineid in ($tmp)\n"
            . " and cust in ($access)\n"
            . " order by cust, host";
        $set = find_many($sql, $db);
    }
    $count = safe_count($set);
    debug_note("$count machines found");
    return machine_list($env, $set, $umin, $umax, $log);
}


function create_message_table($env, $q, $row, $imgs, $content)
{
    $tree = $q['tree'];
    $ords = $q['ords'];
    if ($ords[0] > 0) {
        $q = asset_flat($env, $q, $ords);
        $q = asset_sort($env, $q, $ords);
        $tree = $q['tree'];
    }

    $msg  = process_data($env, $row, $imgs, $q);
    $msg .= mark('E') . "<br>\n";
    if ($content) {
        $msg .= query_draw($env, $q, $tree);
    } else {
        debug_note('content-free report');
    }
    return $msg;
}


/*
    |  // NL 5/2/03:  this comment appears to be obsolete
    |
    |  Note that $env is **NOT** passed by reference to genreport,
    |  since it tailors $env['owner'] (an arrary of mid's that
    |  this owner is allowed to access), and $env['carr'] (an
    |  array of sites that this owner can access) specifically
    |  for each owner.
    |
    |  This isn't so bad since genreport is only called once
    |  per report and never recurses.
    |  7/15/05: genreport returns -1, 0 or 1.
    */

function genreport(&$env, $row, $email, $umin, $umax)
{
    report_debug($env, $row, $email);

    $db       = $env['db'];
    $mdb      = $env['mdb'];
    $debug    = $env['debug'];
    $now      = $env['now'];

    $id      = $row['id'];
    $auth    = $row['username'];
    $title   = $row['name'];
    $format  = $row['format'];
    $content = $row['content'];
    $qid     = $row['searchid'];
    $links   = $row['links'];

    $change    = $row['change_rpt'];
    $file_type = $row['file'];
    $log       = $row['log'];

    $include_user = $row['include_user'];
    $include_text = $row['include_text'];
    $subject_text = $row['subject_text'];

    /* we want access to the mgroupids inside show_query()/query_query() */
    $env['group_include'] = (isset($row['group_include'])) ?
        $row['group_include'] : false;

    $env['group_exclude'] = (isset($row['group_exclude'])) ?
        $row['group_exclude'] : false;

    /*
        |  controls if library functions generate detail links
        */

    $env['link'] = $links;
    $env['file'] = $file_type;
    $env['tab']  = $row['tabular'];

    $order1 = @strval($row['order1']);
    $order2 = @strval($row['order2']);
    $order3 = @strval($row['order3']);
    $order4 = @strval($row['order4']);


    $o1 = ($order1) ? find_did($order1, $db) : 0;
    $o2 = ($order2) ? find_did($order2, $db) : 0;
    $o3 = ($order3) ? find_did($order3, $db) : 0;
    $o4 = ($order4) ? find_did($order4, $db) : 0;

    $sorting = ($o1 > 0);

    $ords = array($o1, $o2, $o3, $o4);

    $tnow = datestring($now);

    $message = '';

    $num = 0;
    $good = false;
    $imgs = array();

    /* Setup for logo if we have one defined */
    if ($env['img']) {
        /* Get the logo for the user */
        $imgs[] = $env['img'];
    }

    /*
        |  Intersect the user's authorized sites
        |  with the list of active ones.  We don't
        |  care about the inactive ones.
        */

    $sites = array();
    if (isset($env['siteaccesstree'][$auth])) {
        $temp   = $env['siteaccesstree'][$auth];
        $active = $env['active'];
        $sites  = site_intersect($temp, $active);
    }

    if (safe_count($sites) <= 0) {
        $name = $row['name'];
        $stat = "a:$id,u:$auth";
        $text = "assets: no active sites ($stat) $name";
        logs::log(__FILE__, __LINE__, $text, 0);
        echo "<p>Report '$title' not being generated because none" .
            " of the selected sites are active.</p><br>";
        return 0;
    }

    $env['report_title'] = $title;
    $dest = ($file_type) ? $auth : $email;
    echo "<p>Sending report '$title' to '$dest'.</p><br>";

    $env['link'] = $links;

    if ($file_type == constCronCreateFileXML) {
        //xml report
        $d3        = show_query($env, $auth, $qid, $ords, $file_type);
        $asset_set = asset_walk_arrange($env, $d3);
    } else {

        $q = show_query($env, $auth, $qid, $ords, $file_type);

        $tree = $q['tree'];
        $mids = $q['mids'];
        $dids = $q['dids'];
        $rows = safe_count($mids);
        $cols = safe_count($dids);

        $grp1 = ($o1) ? $order1 : '<br>';
        $grp2 = ($o2) ? $order2 : '<br>';
        $grp3 = ($o3) ? $order3 : '<br>';
        $grp4 = ($o4) ? $order4 : '<br>';

        if (safe_count($sites) >= 1) {
            $out = find_asset_sites($mids, $db);
            if (!$out) {
                $out[] = 'None';
            }
        } else {
            $out[] = 'None';
        }
    }

    // we need to drop the table created in show_query()
    // however we don't know the name.
    // create_pidtable_name() does a 'drop table if exists $tbl'.
    // This can be used to create the name and remove the table.
    $tbl = create_pidtable_name('SelectedAssetData', $db);

    /*
        // ftp the report as xml
        */
    if ($file_type == 2) {
        $data   = $asset_set['data'];
        $header = $asset_set['header'];
        $txt    = array_to_xml($header, $data);
        // txt is the complete xml file

        $tmp_dir  = '/tmp/';
        $tmp_ext  = '.tmp';
        $login    = $row['xmluser'];
        $pass     = $row['xmlpass'];
        $url      = $row['xmlurl'];
        $file     = $row['xmlfile'];
        $username = $row['username'];
        $passive  = $row['xmlpasv'];

        // do the filename substitutions
        $local_filename  = build_filename($file, $qid, $username, $db);

        /* the ftp_put proc cannot have the local and remote filenames
               be the same or it will produce an error. So append .tmp
               to the local, and place it in the /tmp directory.
            */
        $remote_filename = $local_filename;
        $local_filename  = $tmp_dir . $local_filename . $tmp_ext;

        // add the xml extension if not present
        $file_ext = substr($remote_filename, -4);
        if ($file_ext != '.xml') {
            $remote_filename = $remote_filename . '.xml';
        }

        $file_h = fopen($local_filename, 'w');
        if (!$file_h) {
            logs::log(__FILE__, __LINE__, "assets: fopen error file: $filename");
            return -1;
        }
        if ($file_h) {
            $good = my_write($file_h, $txt);
            fclose($file_h);
            if ($good) {
                $good = ftp_file($login, $pass, $url, $passive, $local_filename, $remote_filename);
            }
            unlink($local_filename);
        }
    }


    /*
        // report to publish to asi portal
        */
    if ($file_type == 1) {
        $message  = '';
        $message .= file_links($env, $row);
        $message .= html_rule_table_stats($title, $auth, $dest);
        if ($change) {
            $type = 'Change Report';
            $env['title'] = 'Asset Change';
            $message .= change_build_message($umin, $umax);
            $message .= change_desc_message($q, $mids);
            if ($content)
                $message .= machine_count($env, $umin, $umax, $log, $mids, $sites, $db);
            else
                debug_note('content-free report');
        } else {
            $type = 'Asset Report';
            $message .= change_close_message($tnow, $grp1, $grp2, $grp3, $grp4, $rows, $cols);
            $message .= create_message_table($env, $q, $row, $imgs, $content);
        }

        //the report is built, now write it

        $tmpfile_h = tmpfile();
        if (!$tmpfile_h) return -1;

        if (!my_write($tmpfile_h, $message)) return -1;

        $row['tmpfile_h'] = $tmpfile_h;
        $days = $env['days'];
        $good = write_file($env, $row, $type, $days, $rows, $sites);
        fclose($tmpfile_h);
    }


    /*
        // mail the report as an html attachment
        */
    if ($file_type == 0) {
        $message .= html_page_title($env, $row, $title);
        $message .= html_rule_table_stats($title, $auth, $dest);
        if ($change) {
            $env['title'] = 'Asset Change';
            $message .= change_build_message($umin, $umax);
            $message .= change_desc_message($q, $mids);

            if ($content)
                $message .= machine_count($env, $umin, $umax, $log, $mids, $sites, $db);
            else
                debug_note('content-free report');
        } else {
            $message .= change_close_message($tnow, $grp1, $grp2, $grp3, $grp4, $rows, $cols);
            $message .= create_message_table($env, $q, $row, $imgs, $content);
        }

        //report is built, now mail it

        $subject = "Report: $title";
        if ($include_user) {
            $subject = $subject . " for user " . $auth;
        }
        if ($include_text) {
            $subject = $subject . ": " . $subject_text;
        }

        $origin  = $env['origin'];
        $server  = $env['server'];
        $body  = html_header($title);
        $body .= $message;

        /* Now get the footer for this user */
        $body .= $env['foot'];

        $tmpfile_h = tmpfile();
        if (!$tmpfile_h) return -1;

        if (!my_write($tmpfile_h, $body)) return -1;

        $good = report_mail(
            $row,
            $email,
            $subject,
            $tmpfile_h,
            $origin,
            $imgs,
            $server,
            $out
        );
        fclose($tmpfile_h);
    }

    $body = '';
    $imgs = array();

    if ($good) {
        $num = 1;
    } else {
        echo '<p>Mail was *NOT* sent.</p>';
    }
    if ($debug) {
        echo $message;
    }
    return $num;
}


/* upload a file to the specified host in $url.
       seperate the $host and $path from the url at the first occurance of /
       Connect to the $host with the $pass and $login, turn passive mode on
       change to $path if set.
       If any file in the current directory matches the name of $remote_filename,
       delete the remote copy, and upload the $local_filename to the $host
       as $remote_filename.

       If any of these steps fail, we exit w/error.
    */
function ftp_file($login, $pass, $url, $passive, $local_filename, $remote_filename)
{
    $path = strstr($url, '/');
    $host = substr($url, 0, strpos($url, '/'));
    $ip   = ($host) ? gethostbyname($host) : gethostbyname($url);
    $host = ($host) ? $host : $url;

    //connect to host
    $ftp_h = ftp_connect($ip);
    if (!$ftp_h) {
        logs::log(__FILE__, __LINE__, "assets: ftp connect failed $host($ip)");
        ftp_close($ftp_h);
        return false;
    }
    logs::log(__FILE__, __LINE__, "assets: ftp connect at $host($ip)");

    //login
    @$good = ftp_login($ftp_h, $login, $pass);
    if (!$good) {
        logs::log(__FILE__, __LINE__, "assets: ftp login failed $login at $host($ip)");
        return false;
    }
    logs::log(__FILE__, __LINE__, "assets: ftp login (user:$login) at $host($ip)");

    //turn passive mode on
    if ($passive) {
        $good = ftp_pasv($ftp_h, true);
        if (!$good) {
            logs::log(__FILE__, __LINE__, "assets: ftp set passive mode failed $login at $host($ip)");
            ftp_close($ftp_h);
            return false;
        }
        logs::log(__FILE__, __LINE__, "assets: ftp passive mode set at $host($ip)");
    }

    //if a path is set, change dir to that path
    if ($path) {
        @$good = ftp_chdir($ftp_h, $path);
        if (!$good) {
            logs::log(__FILE__, __LINE__, "assets: ftp change directory failed (path:$path) at $host($ip)");
            ftp_close($ftp_h);
            return $good;
        }
        logs::log(__FILE__, __LINE__, "assets: ftp change directory (path:$path) at $host($ip)");
    }

    //get the contents of the directory
    $dir_contents = ftp_nlist($ftp_h, '.');
    if ($dir_contents) {
        reset($dir_contents);
        foreach ($dir_contents as $key => $dir_filename) {
            //if a file exists in the directory with the same name as
            //the one we are uploading, delete it.
            if ($dir_filename == $remote_filename) {
                $good = ftp_delete($ftp_h, $dir_filename);
                if (!$good) {
                    logs::log(__FILE__, __LINE__, "assets: ftp delete failed (file:$dir_filename) at $host($ip)");
                    ftp_close($ftp_h);
                    return false;
                }
                logs::log(__FILE__, __LINE__, "assets: ftp delete successful (file:$dir_filename) at $host($ip)");
            }
        }
    }

    //put the file on the server
    @$good = ftp_put($ftp_h, $remote_filename, $local_filename, FTP_BINARY);
    if (!$good) {
        logs::log(__FILE__, __LINE__, "assets: ftp upload failed $url : $remote_filename");
        ftp_close($ftp_h);
        return false;
    }

    logs::log(__FILE__, __LINE__, "assets: ftp upload successful $url : $remote_filename");
    ftp_close($ftp_h);
    return $good;
}


/*
    |  Here's the implentation of the much-requested
    |  report based on multiple saved searches.
    |
    |  The search_list is a comma separated list of
    |  id codes.  If there's only one, which I expect
    |  to be the usual case, then it will just return
    |  the one.  Otherwise, it returns a string of the
    |  form "(A) or (B) or (C)".
    |
    |  We don't put parens around the entire string
    |  because genreport does that anyway.
    */

function report_once(&$env, $row)
{
    $db     = $env['db'];
    $pid    = $env['pid'];
    $now    = $env['now'];
    $table  = $env['table'];
    $single = $env['single'];

    $id    = $row['id'];
    $owner = $row['owner'];  // whether this user owns the report
    $name  = $row['name'];
    $cycle = $row['cycle'];
    $email = $row['emaillist'];
    $defm  = $row['defmail'];
    $file  = $row['file'];
    $user  = $row['username'];
    $last  = $row['last_run'];

    $env['id'] = $id;  // is this really needed??

    $def   = @$env['userdata'][$user]['report_mail'];
    $email = email_list($email, $defm, $def);
    $num   = 0;

    if (($email) || ($file)) {
        if ($cycle <= 3) {
            $umax = last_cycle($row, $now);
        }

        // daily report

        if ($cycle == 0) {
            $umin = yesterday($umax);
            $num  = genreport($env, $row, $email, $umin, $umax);
        }

        // weekly report, sundays is zero

        if ($cycle == 1) {
            $umin = yesterweek($umax);
            $num  = genreport($env, $row, $email, $umin, $umax);
        }

        // monthly report

        if ($cycle == 2) {
            $umin = yestermonth($umax);
            $num  = genreport($env, $row, $email, $umin, $umax);
        }

        // weekdays ... sunday is 0, so weekdays are 1..5

        if ($cycle == 3) {
            $date = getdate($umax);
            $wday = $date['wday'];
            $days = ($wday == 1) ? 3 : 1;
            $umin = days_ago($umax, $days);
            $num  = genreport($env, $row, $email, $umin, $umax);
        }

        // immediate

        if ($cycle == 4) {
            $umin = $row['umin'];
            $umax = $row['umax'];
            $num  = genreport($env, $row, $email, $umin, $umax);
        }
    } else {
        if ($owner) {
            disable_report($env, $id);
        }
        $stat = "a:$id,p:$pid,u:$user";
        $text = "assets: no email ($stat) $name";
        logs::log(__FILE__, __LINE__, $text, 0);
        echo "<p>$text</p>\n";
    }
    return $num;
}


/*
    |  Global reports get run once for their owner,
    |  and then once again for every other user
    |  who does not have an override.
    */

function report(&$env, $row, &$users)
{
    $aaaa = microtime();
    $db   = $env['db'];
    $pid  = $env['pid'];
    $id   = $row['id'];
    $user = $row['username'];
    $name = $row['name'];
    $skip = $row['skip_owner'];
    $global = $row['global'];
    $done = 0;

    if (claim_report($id, $db)) {
        // Run for owner
        if ((!$skip) || (!$global)) {
            $row['owner'] = 1;
            $tmpuser = $row['username'];
            head_get_user_logo($tmpuser, $env, $db);
            foot_get_user_footer($tmpuser, $env, $db);
            //logs::log(__FILE__, __LINE__, "Report_Once - Global: $tmpuser",0);     
            $done = report_once($env, $row);
            if ($done < 0) return $done;
        }
        // Run for other users
        $cycle  = $row['cycle'];
        $table  = $env['table'];
        $single = $env['single'];
        if (($global) && ($users)) {
            $qn   = safe_addslashes($row['name']);
            reset($users);
            foreach ($users as $key => $data) {
                // not for the real owner,
                // we did him already.
                if ($data != $user) {
                    $qu  = safe_addslashes($data);
                    $sql = "select * from $table\n"
                        . " where username = '$qu'\n"
                        . " and name = '$qn'";
                    $set = find_many($sql, $db);
                    if (!$set) {
                        debug_note("proxy for $data");
                        // note override still applies even
                        // if matching report is disabled
                        // or even (forbidden) global.

                        $row['username'] = $data;
                        $row['owner']    = 0;
                        $row['global']   = 0;

                        $tmpuser = $row['username'];
                        head_get_user_logo($tmpuser, $env, $db);
                        foot_get_user_footer($tmpuser, $env, $db);
                        //logs::log(__FILE__, __LINE__, "Report_Once - User: $tmpuser",0);  
                        $tmpd = report_once($env, $row);
                        if ($tmpd < 0) return $tmpd;
                        $done += $tmpd;
                    }
                }
            }
        }
        update_report($env, $row, $db);
        if (($cycle == 4) && (!$single)) {
            delete_report($env, $id);
        }
    }

    $bbbb = microtime();
    $secs = microtime_diff($aaaa, $bbbb);
    $time = microtime_show($secs);
    $stat = "p:$pid,a:$id,u:$user,d:$done";
    $text = "assets: done ($stat) in $time, $name";
    if (10 <= $secs) {
        logs::log(__FILE__, __LINE__, $text, 0);
    }
    debug_note($text);
    return $done;
}


/*
    |  Clean up the mess ...
    |
    |  This can go away when report creation/editing is fixed.
    */

function fix_order($db)
{
    for ($i = 1; $i <= 4; $i++) {
        $field = "order$i";
        $sql  = "update AssetReports set";
        $sql .= " $field = '' where";
        $sql .= " ($field = 'Nothing') or";
        $sql .= " (isnull($field))";
        command($sql, $db);
    }
    $sql = "update AssetReports set format='html' where format='text'";
    command($sql, $db);
}


/*
    |  Check for failed uploads, fix them if possible.
    |
    |  Note that provisional > 0 is normal and expected,
    |  it happens during every update.
    |
    |  However, it shouldn't get stuck that way unless
    |  asset logging fails.
    */

function repair_upload($now, $db)
{
    $max  = $now - 7200;
    $sql  = "select * from Machine where\n";
    $sql .= " provisional between 1 and $max";
    $list = find_many($sql, $db);
    if ($list) {
        reset($list);
        foreach ($list as $key => $row) {
            $mid  = $row['machineid'];
            $smax = $row['slatest'];
            $cmax = $row['clatest'];
            $prov = $row['provisional'];
            $host = $row['host'];
            $site = $row['cust'];
            $when = date('m/d/Y H:i:s', $prov);
            logs::log(__FILE__, __LINE__, "assets: repair $host at $site, upload $when", 0);
            fix_machine($mid, $smax, $cmax, $prov, $db);
        }
    }
}


/*
    |  This handles the special case of a report that was
    |  cleared.  In general, we want the next run time
    |  to be the next cycle bounary, plus the appropriate
    |  cron bias.
    |
    |  However, we'll allow a little bit of flexibility
    |  in the special case where we have very recently
    |  (past few minutes) crossed the deadline.
    |
    |  This allows the user to schedule a report for the
    |  near future, and it will still work in a reasonable
    |  way even if his clock is a few minutes off.
    */

function update_future(&$row, $now, $bias, $db)
{
    $id   = $row['id'];
    $last = $row['last_run'];
    $next = next_cycle($row, $now);
    $prev = last_cycle($row, $now);
    if (($last < $prev) && ($now <= $prev + 1200))
        $when = $prev + $bias;
    else
        $when = $next + $bias;
    $dw   = date('m/d/y H:i:s', $when);
    $sql  = "update AssetReports set\n";
    $sql .= " next_run = $when -- $dw\n";
    $sql .= " where id = $id";
    redcommand($sql, $db);
    $next = date('m/d/y H:i', $next);
    $prev = date('m/d/y H:i', $prev);
    $last = date('m/d/y H:i', $last);
    $when = date('m/d/y H:i', $when);
    debug_note("time: $when, last:$last, next cycle: $next, previous cycle: $prev");
}

/*
    |  This handles the special case of a report that has
    |  become stuck.  This can happen if a server gets
    |  shut down while a report is in process.  Or it
    |  might mean that there was a fatal error.
    |
    |  In any event, we've already noted the stuck
    |  report in the log file, so it's worth trying
    |  again.
    */

function update_stuck(&$row, $now, $db)
{
    $id   = $row['id'];
    $when = $now - 1;
    $dw  = date('m/d/y H:i:s', $when);
    $sql = "update AssetReports set\n"
        . " next_run = $when, -- $dw\n"
        . " this_run = 0\n"
        . " where id = $id\n"
        . " and next_run < 0\n"
        . " and enabled = 1";
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    debug_note("unstick: id: $id, when:$dw");
}


/*
    |  Notify the intended recepients of the report that
    |  the report has some sort of problem and has been
    |  disabled.
    */

function dead_report(&$row, $src, $def)
{
    $file = $row['file'];
    $name = $row['name'];
    $user = $row['username'];
    $mail = $row['emaillist'];
    $defm = $row['defmail'];
    $dst  = email_list($mail, $defm, $def);
    if ($dst) {
        $now = date('m/d H:i', time());
        $sub = "assets: $name disabled $now.";
        $frm = "From: $src";
        $msg = "\n\nThere is a problem with report $name.\n"
            . "It has exceeded the maximum number of retries\n"
            . "and has now been disabled.\n\n\n";
        mail($dst, $sub, $msg, $frm);
        debug_note("user mail sent to $dst");
    } else {
        debug_note("no user mail sent");
    }
}



function find_next($now, $db)
{
    $ddd = date('m/d H:i', $now);
    $sql = "select * from AssetReports -- $ddd\n"
        . " where next_run between 1 and $now\n"
        . " and enabled = 1\n"
        . " order by next_run, global, cycle, id\n"
        . " limit 1";
    return find_one($sql, $db);
}


/* call this to return a finished env */
function make_env($db)
{
    $comp  = component_installed();
    $now   = time();
    $pid   = getmypid();
    $users = userlist($db);
    return build_env($pid, $now, $comp, $users, $db);
}


function build_env($pid, $now, $comp, $users, $db)
{
    $dbg    = get_integer('debug', 0);
    $single = get_integer('id', 0);
    $debug  = (($dbg > 0) || ($single > 0)) ? 1 : 0;
    $ssl  = server_int('ssl', 1, $db);
    $def  = ($ssl) ? 443 : 80;
    $qdef = 95; // good jpeg quality
    $port = server_int('port', $def, $db);
    $bias = server_int('cron_bias', 120, $db);
    $retr = server_int('report_max_retries', 3, $db);
    $timo = server_int(constReportTimo, 7200, $db);
    $days = server_int('file_expire_days', 120, $db);
    $jpgq = server_int('jpeg_quality', $qdef, $db);
    if (($jpgq < 0) || ($jpgq > 100)) {
        logs::log(__FILE__, __LINE__, "invalid jpeg_quality ($jpgq), expecting 0..100", 0);
        $jpgq = $qdef;
    }

    $slow = (float) server_def('slow_query_asset', 20, $db);

    $server = server_name($db);
    $def    = "assets@$server";
    $origin = server_def('asset_report_sender', $def, $db);

    /*
        |  We'll allow this particular script as much time as it needs,
        |  since some of the reports can take a very long time.  This
        |  effectively sets max_execution_time to infinite.
        |
        |  To set the memory limit, use the maximum of the size passed from
        |  the command line and the server setting.  The check for existence
        |  of ini_set is left over from older php3 compatibility.
        */

    set_time_limit(0);
    $msize  = get_integer('mem', 64);
    ignore_user_abort(true);
    if (function_exists('ini_set')) {
        $msizeserv = server_def('max_php_mem_mb', '256', $db);
        if ($msizeserv > $msize) {
            $msize = $msizeserv;
        }
        $mem = $msize . 'M';
        debug_note("memory_limit: $mem");
        ini_set('memory_limit', $mem);
        ini_set('log_errors', '1');
        if ($debug) {
            ini_set('error_reporting', E_ALL);
            ini_set('display_errors', '1');
        }
    }

    $table   = 'AssetReports';
    $dataset = 'asset';
    $asset   = 1;
    $event   = 0;

    $users  = userlist($db);
    $host   = server_href($db);
    $base   = base_directory($host, $comp);

    $env = array();
    $env['db']      = $db;
    $env['mdb']     = $db;
    $env['now']     = $now;
    $env['pid']     = getmypid();
    $env['tab']     = 0;
    $env['jpgq']    = $jpgq;
    $env['site']    = '';
    $env['dbid']    = 'master';
    $env['base']    = $base;
    $env['bias']    = $bias;
    $env['href']    = "$base/index.php";
    $env['cron']    = 1;
    $env['slow']    = $slow;
    $env['days']    = $days;
    $env['retr']    = $retr;
    $env['root']    = $comp['root'];
    $env['odir']    = $comp['odir'];
    $env['table']   = $table;
    $env['debug']   = $debug;
    $env['asset']   = $asset;
    $env['event']   = $event;
    $env['single']  = $single;
    $env['origin']  = $origin;
    $env['server']  = $server;
    $env['dataset'] = $dataset;
    $env['userdata'] = usertree($db);
    $env['siteaccesstree'] = siteaccesstree($users, $db);
    $env['active']  = find_active_sites($db);
    $env['names']  = asset_names($db);
    $env['hosts']  = asset_machines($db);
    return $env;
}



function kill_report(&$env, &$row, $db)
{
    $rid = $row['id'];
    $now = time();
    $sql = "update AssetReports set\n"
        . " enabled  = 0,\n"
        . " this_run = 0,\n"
        . " next_run = 0,\n"
        . " modified = $now\n"
        . " where id = $rid";
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    if ($num) {
        $pid  = $env['pid'];
        $name = $row['name'];
        $that = $row['this_run'];
        $last = $row['last_run'];
        $user = $row['username'];
        $retr = $row['retries'];
        $secs = ($now > $that) ? $now - $that : 0;
        $that = date('m/d/y H:i:s', $that);
        $last = date('m/d/y H:i:s', $last);
        $now  = date('m/d/y H:i:s', $now);
        $stat = "p:$pid,u:$user,a:$rid,r:$retr,s:$secs";
        $text = "assets: killed ($stat) $name";
        logs::log(__FILE__, __LINE__, $text, 0);

        $def = $env['userdata'][$user]['report_mail'];
        $pid = getmypid();
        $srv = server_name($db);
        $srd = "assets@$srv";
        $dsd = 'support@handsfreenetworks.com';
        $src = server_def('asset_report_sender', $srd, $db);
        $dst = server_def('support_email', $dsd, $db);
        $sub = "asset problem ($rid) at $srv";
        $msg = "\n\nage:$secs, last:$last, now:$now, this:$that\n"
            . "user:$user, pid:$pid, rid:$rid, name:$name\n"
            . "stuck report.\n\n";
        $frm = "From: $src";
        $good = mail($dst, $sub, $msg, $frm);
        if (!$good) {
            $text = "assets: mail failure ($pid)";
            logs::log(__FILE__, __LINE__, $text, 0);
        }
        echo "<p>sent mail to $dst</p>\n";
        dead_report($row, $src, $def);
    }
}


/*
    |  Make "immediate" reports run immediately,
    |  even if their specified time interval is
    |  not yet complete.
    */

function schedule_future($now, $db)
{
    $sql = "update AssetReports\n"
        . " set next_run = $now\n"
        . " where cycle = 4\n"
        . " and next_run = 0\n"
        . " and enabled = 1";
    redcommand($sql, $db);

    $sql = "select * from AssetReports\n"
        . " where cycle != 4\n"
        . " and next_run = 0\n"
        . " and enabled = 1";
    $set = find_many($sql, $db);
    if ($set) {
        $bias = server_int('cron_bias', 120, $db);

        reset($set);
        foreach ($set as $key => $row) {
            update_future($row, $now, $bias, $db);
        }
    }
}




/*
    |  The old queue used to claim, modify and process
    |  several reports at a time, but the new one just
    |  does them one at a time.  The old queue is perhaps
    |  slightly more efficient, but the new one has two
    |  major advantages.
    |
    |  1) If the queue gets stuck, at most one report
    |     needs to be repaired.
    |
    |  2) It allows us to reorder or modify pending
    |     reports while cron is running.
    */

function report_cron($pid, $now, $comp, $db)
{
    $aaaa   = microtime();
    $single = get_integer('id', 0);

    $env  = array();
    $more = true;
    $loop = 0;
    $num  = 0;

    schedule_future($now, $db);

    while ($more) {

        /*
            |  This is just for debugging.
            |  It allows us to run just one single report,
            |  without bothering anything else.  It does not
            |  check to see if the report is enabled or not.
            */

        if ($single > 0) {
            $more = false;
            $row = find_report_aid($single, $db);
        } else {
            $row = find_next($now, $db);
        }

        if (($row) && (!$env)) {
            $users = userlist($db);
            $env   = make_env($db);
            db_change($GLOBALS['PREFIX'] . 'asset', $db);
        }

        if (($row) && ($env) && ($users)) {
            $tmp = report($env, $row, $users);
            if ($tmp < 0) {
                // we've had a fatal error.
                report_cleanup($env, $db);
                $more = false;
            } else {
                $loop++;
                $num += $tmp;
            }
        } else {
            debug_note('nothing to be done');
            $more = false;
        }


        $elapsed = time() - $now;
        if ($elapsed > 0) {
            echo "$elapsed seconds so far ...<br>\n\n";
        }
    }

    if ($num > 0) {
        $bbbb = microtime();
        $time = microtime_diff($aaaa, $bbbb);
        $secs = microtime_show($time);
        $msg  = "Processed $num reports; ($secs).";
        logs::log(__FILE__, __LINE__, "assets: $num completed; ($secs)", 0);
    } else {
        $now  = time();
        $date = datestring($now);
        $msg  = "No reports to do right now ... $date";
    }
    echo "<p>$msg</p>\n";
}


function report_lock($pid, $db)
{
    $busy = server_int(constPurgeLock, 0, $db);
    if ($busy) {
        return false;
    }
    $good = false;
    $name = constReportLock;
    if (update_opt($name, 1, $db)) {
        // we have the lock, update mod time.
        opt_update($name, 1, 0, $db);
        $good = true;
    } else {
        $row = find_opt($name, $db);
        if (!$row) {
            if (opt_insert($name, 1, 0, $db)) {
                $good = true;
                $text = "assets: $name created";
            } else {
                $text = "assets: could not create $name";
            }
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
    }
    if ($good) {
        $name = constReportPid;
        $row  = find_opt($name, $db);
        if ($row) {
            opt_update($name, $pid, 0, $db);
        } else {
            if (opt_insert($name, $pid, 0, $db)) {
                $text = "assets: $name created";
            } else {
                $text = "assets: could not create $name";
            }
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
    }
    return $good;
}


/*
    |  Check to see if we still own the lock.
    |  If so, all is well ... update the mod time.
    |
    |  otherwise we have a problem
    */

function checkpid($pid, $db)
{
    $good = false;
    $lpid = server_int(constReportPid, 0, $db);
    if ($lpid == $pid) {
        opt_update(constReportPid, $pid, 0, $db);
        $good = true;
    } else {
        $acts = ($lpid) ? "stolen by process $lpid" : 'vanished';
        $stat = "p:$pid";
        $text = "assets: queue ($stat) lock $acts";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }
    return $good;
}


/*
    |  We got the server lock to begin with, and we should still
    |  have it now.  But if someone else has stolen it away, we
    |  should just log the fact and leave WITHOUT clearing the
    |  lock flag.
    */

function report_unlock($pid, $db)
{
    if (checkpid($pid, $db)) {
        opt_update(constReportPid, 0, 0, $db);
        opt_update(constReportLock, 0, 0, $db);
    }
}


function report_cleanup(&$env, $db)
{
    $try = $env['retr'];
    mysqli_select_db($db, asset);
    $set = array();
    if ($try > 0) {
        $sql = "select * from AssetReports\n"
            . " where next_run < 0\n"
            . " and enabled = 1\n"
            . " and retries > $try";
        $set = find_many($sql, $db);
        reset($set);
        foreach ($set as $key => $row) {
            kill_report($env, $row, $db);
        }
    }

    $set = find_stuck_report($db);
    reset($set);
    foreach ($set as $key => $row) {
        update_stuck($row, time(), $db);
    }
}

/*
    |  If report_timeout is zero, then we should be prepared
    |  to wait forever for the lock to be released.
    */

function report_steal($pid, $db)
{
    $out  = false;
    $busy = server_int(constPurgeLock, 0, $db);
    $lock = server_int(constReportLock, 0, $db);
    $timo = server_int(constReportTimo, 0, $db);
    if (($lock) && ($timo) && (!$busy)) {
        $row = find_opt(constReportPid, $db);
        $now = time();
        if ($row) {
            $own = $row['value'];
            $mod = $row['modified'];
            $txt = date('H:i:s', $mod);
            $age = $now - $mod;
            echo "<p>Queue Locked by <b>$own</b> at <b>$txt</b> ($age seconds)</p>\n";
            if ($mod + $timo < $now) {
                $env  = make_env($db);
                report_cleanup($env, $db);
                $age  = $now - $mod;
                $stat = "p:$pid,a:$age,t:$timo";
                $text = "assets: stealing lock ($stat) from process $own";
                logs::log(__FILE__, __LINE__, $text, 0);
                debug_note($text);
                $out = true;
            } else {
                $age = $now - $mod;
                debug_note("age $age < timeout $timo");
            }
        }
    }
    return $out;
}




/*
    |  Main program
    */

$aaaa = microtime();
$now  = time();
$pid  = getmypid();
$db   = db_connect();
$comp = component_installed();
$page = "Cron Asset ($pid)";

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($page, $comp, '', 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$dbg   = get_integer('debug', 0);
$id    = get_integer('id', 0);
$force = get_integer('force', 0);
$debug = (($dbg > 0) || ($id > 0));

echo again();

db_change($GLOBALS['PREFIX'] . 'asset', $db);
repair_upload($now, $db);
if ($force) {
    logs::log(__FILE__, __LINE__, 'assets: forced unlock', 0);
    opt_update(constReportPid, 0, 0, $db);
    opt_update(constReportLock, 0, 0, $db);
}

if (report_lock($pid, $db)) {
    report_cron($pid, $now, $comp, $db);
    report_unlock($pid, $db);
} else {
    if (report_steal($pid, $db)) {
        opt_update(constReportPid, 0, 0, $db);
        opt_update(constReportLock, 0, 0, $db);
        echo "<p>Asset Lock Broken.</p>\n";
    } else {
        echo "<p>Could not aquire asset lock</p>\n";
    }
}

echo again();

/* Just pass in hfn for this page since its a utility page */
$user = 'hfn';
echo head_standard_html_footer($user, $db);
