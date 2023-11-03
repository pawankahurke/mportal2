<?php

/*
Revision history:

Date        Who     What
----        ---     ----
25-Jul-02   EWB     Allow infinite time for this process
31-Jul-02   EWB     Factored arguments into $row, $env
 1-Aug-02   EWB     Guarantee immediate reports never global
 1-Aug-02   EWB     Global reports are run for all users
 1-Aug-02   EWB     Better debug messages
 1-Aug-02   EWB     Fixed a problem with non-standard ports
 2-Aug-02   EWB     Allows port to be specified on command line
 2-Aug-02   EWB     Improved report override checking.
 2-Aug-02   EWB     At signs for expresions not statements
13-Aug-02   EWB     Always log mysql failures
17-Sep-02   EWB     search_list is not always defined.
20-Sep-02   EWB     8.3 library names.
25-Sep-02   EWB     Calender Math factored into library.
14-Oct-02   EWB     Base_name into library.
 5-Nov-02   EWB     Disables nonsense report.
13-Nov-02   EWB     Log even more mysql errors.
 5-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Migration Aware
13-Dec-02   EWB     Send mail from $SERVER_NAME
13-Jan-03   EWB     Handle quotes in report name.
13-Jan-03   EWB     Don't require register_globals
16-Jan-03   EWB     Access to $_SERVER variables.
27-Jan-03   EWB     Memory Limit 32M
27-Jan-03   EWB     Reduce memory usage.
31-Jan-03   EWB     Uses new server options.
10-Feb-03   EWB     Uses event database.
12-Feb-03   EWB     db_change();
12-Feb-03   EWB     Cache owners and access tree before switching to event database.
21-Feb-03   EWB     standard_html_footer()
 6-Mar-03   NL      Uses output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
15-Mar-03   EWB     Uses posix servertime.
19-Mar-03   EWB     Uses server_def for ssl and port option.
21-Apr-03   EWB     OEM Page Footers
23-Apr-03   EWB     Human-readable timestamps for entered, servertime.
27-Apr-03   NL      Delete RptSiteFilter records when deleting Report record.
27-Apr-03   NL      Use filtered sites ($sitelist) instead of $access.  Create
                      $sitelist (get_filtered_sitelist()) only for reports being gen'd
                      (not part of env array) cuz too taxing to get for all reports.
29-Apr-03   NL      In reportgen(), intersect site filter with access.
29-Apr-03   NL      Bugfixes to reportgen()
30-Apr-03   NL      Simplify reportgen(): use array_intersect().
30-Apr-03   EWB     Don't calculate irrelvent access.
 1-May-03   EWB     No sitefilter for proxy global report.
 2-May-03   NL      Turns out we do want sitefiltering for proxy global reports.
                    Rather than setting $id to 0 for proxy reports, use row['owner']
                        = [0|1] to distinguish btw proxy and owner-run reports.
                    Correct var names. Proxy user array is called $users not $owners
                        and userdata array is now called $userdata not $users.
                    Optimization:
                        - Use $row['filtersites'] rather than get_obj_filtersetting.
                        - Only pass report ids for reports where sitefilter is ON.
12-May-03   EWB     Mark cron job
21-May-03   EWB     Moved shared code into l-rtxt.php.
10-Jun-03   EWB     Master & Slave database access.
                     - Slave database is read only, used only for select.
                     - Master database used for insert, update and delete.
16-Jun-03   EWB     Uses slave library.
18-Jun-03   EWB     severtime between.
19-Jun-03   EWB     Log Slow Queries.
19-Jun-03   EWB     Use master database for saved searches.
15-Jul-03   EWB     Log Master/Slave for slow queries.
17-Jul-03   EWB     File Output.
18-Jul-03   EWB     Generates more appropriate links when writing to a file.
18-Jul-03   EWB     Server Option for File Expiration Date.
24-Jul-03   EWB     Suppress Server Links.
25-Aug-03   EWB     Close table for 0 events.
 5-Sep-03   EWB     Report event count when saving file on server.
 5-Sep-03   AAM     Use Slave database for 'Events' table only.
 8-Sep-03   EWB     ditto, this time for sure.
12-Sep-03   EWB     get image data, not just filename.
16-Sep-03   EWB     Factored MIME into new library l-mime.
22-Sep-03   EWB     implement jpeg_quality server option.
 1-Oct-03   EWB     sort by query works in master database.
 2-Oct-03   EWB     sort by query works in slave database.
22-Oct-03   EWB     Better scheduling method.
23-Oct-03   EWB     Unstick report 11 hour timeout
24-Oct-03   EWB     Run single report.
24-Oct-03   EWB     exclude reports on inactive sites
26-Oct-03   EWB     allow setting memory limit from command line.
27-Oct-03   EWB     run the retries *after* running the regular reports.
30-Oct-03   NL      Moved find_active_sites() to l-cron.php.
 2-Nov-03   EWB     Always run local reports first.
 5-Nov-03   NL      Correct error message for inactive sites.
13-Nov-03   NL      Add asset_href() to provide link to asset detail page.
14-Nov-03   NL      Add $alinks && $do_alinks (whether to link to asset detail page).
17-Nov-03   EWB     Changed insert to "insert ignore" for TempEvents.
18-Nov-03   EWB     Report process id for significant log messages.
19-Nov-03   EWB     ignore_user_abort();
20-Nov-03   EWB     Slow query reports number of results returned.
25-Nov-03   NL      Separate assetlinks & eventlinks controls.
28-Nov-03   EWB     fixed a typo: serious_messge -> serious_message()
30-Nov-03   AAM     Added performance logging entries.
29-Dec-03   EWB     Always run immediate reports at this cron time.
29-Dec-03   EWB     Remove call-time pass by reference.
 7-Jan-04   EWB     case-insensitive site calculations.
13-Feb-04   EWB     Uses server_name();
 6-Feb-04   EWB     server_name variable.
11-Mar-04   EWB     events.Events.deleted
 8-Apr-04   EWB     Implement Max Retries.
13-Apr-04   EWB     Killing a stuck report sends email.
14-Apr-04   EWB     Also notifies the intended recipients.
20-Apr-04   EWB     fixed database deadlock bug.
22-Apr-04   EWB     spellling counts, num should be $num.
18-May-04   EWB     disable report sets this_run to zero.
18-May-04   EWB     handle the case where some are claimed, but no one is running.
20-Jul-04   EWB     fixed a subtle logic problem in the scheduling.
18-Aug-04   EWB     always report statistics on long running reports.
27-Oct-04   EWB     automatically disable details on extremely large reports.
 1-Nov-04   BJS     added include_user, include_text & subject text to email options.
 9-Nov-04   EWB     improvements to report locking, scheduling.
 8-Dec-04   BJS     added EWB's fetch_sites, +1 argv to html_stats.
10-Dec-04   BJS     added skip_owner: option to not generate report for global owner.
14-Dec-04   EWB     purge_lock always highest priority.
18-Dec-04   EWB     process_data: mysql_data_seek($events,0);
20-Dec-04   BJS     added sites to email body.
 1-Jan-05   BJS     list sites when $site>0 && $rows>0 (for alex).
31-Jan-05   BJS     row arg to file_links().
24-Mar-05   BJS     aggregate reports.
25-Mar-05   BJS     link fixes per Alex.
28-Mar-05   BJS     fixed report layout.
29-Mar-05   BJS     more link fixes.
30-Mar-05   BJS     fixed report html missing </table>.
 7-Jun-05   BJS     option to omit 'zero event' aggregate and classic reports.
 8-Jun-05   BJS     added create_selectedevents() and drop_selectedevents().
 9-Jun-05   BJS     global reports now insert data into the SelectedEvents temp
                    table. When retrieving the report, a select from SelectedEvents
                    is done, instead of selecting from Events. Increase global report speed.
20-Jun-05   BJS     insert_selectedevents() code removed into seperate procedures.
22-Jun-05   BJS     query fixes, build_temp_table() additional argv.
23-Jun-05   BJS     added parens to searchstring in insert_selectedevents().
 8-Jul-05   BJS     build report in temp files instead of array.
13-Jul-05   BJS     reports generated correctly when details turned on.
14-Jul-05   BJS     fwrite changed to my_write, improved error checks.
18-Jul-05   BJS     a fatal error calls cleanup_suicide().
22-Jul-05   BJS     Option to create a report with links only (detaillinks).
09-Aug-05   BJS     fixed global reports w/multiple query filters not working.
 6-Sep-05   BJS     replaced Event.events.deleted with core.Census.deleted.
20-Sep-05   BJS     If the $query field is blank do not include in SQL statement.
07-Nov-05   BJS     Added support for group include/exclude in reports.
                    Added error supression to env['userdata'][$user]['report_email']
10-Nov-05   BJS     Removed RptSiteFilters references.
24-Jan-06   AAM     Bug 3072: change memory_limit setting to use max_php_mem_mb.
                    (Note that this change was actually moved from 4.2 to 4.3
                    on 06-Mar-06.)
24-Jul-06   BTE     Bug 3534: Event Reports: reports are too slow.
17-Aug-06   BTE     Bug 3616: Setup query interval for event reports.
23-Aug-06   BTE     Bug 3626: Event Reports: add query interval to local
                    reports.
16-Sep-06   BTE     Bug 3666: Remove optimization in c-report.php.
19-Sep-06   WOH     Bug 3657: Changed name html_footer.  Added username arg.                    
20-Oct-06   JRN     Bug 3761 & 3762: Changed show_details to build buffers on a per machine
                    basis, then write to tmpfile_h.
31-Oct-06   BTE     Bug 3789: Add back proper join to c-report.php.
24-Nov-06   AAM     Bug 3865: implemented consistent use of stripslashes with
                    contents of event filters.
05-Dec-06   BTE     Bug 3933: Force notifications and reports to use the
                    servertime index.
19-Feb-08   BTE     Added some includes.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-perf.php');
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-foot.php');
include('../lib/l-head.php');
include('../lib/l-rprt.php');
include('../lib/l-cmth.php');
include('../lib/l-base.php');
include('../lib/l-cron.php');
include('../lib/l-graf.php');
include('../lib/l-rtxt.php');
include('../lib/l-mime.php');
include('../lib/l-slav.php');
include('../lib/l-jump.php');
include('../lib/l-gsql.php');
include('../lib/l-msql.php');
include('../lib/l-dsql.php');
include('../lib/l-fprc.php');
include('../lib/l-cnst.php');
include('../lib/l-errs.php');
include('../lib/l-core.php');
include('../lib/l-grps.php');

define('constPurgeLock',    'purge_lock');
define('constReportLock',   'report_lock');
define('constReportPid',    'report_pid');
define('constReportTimo',   'report_timeout');

/*-------------------------------------------------------------------*\
 |                                                                   |
 |  UTILTY FUNCTIONS                                                 |
 |                                                                   |
\*-------------------------------------------------------------------*/


function serious_message($msg)
{
    logs::log(__FILE__, __LINE__, "report: $msg", 0);
    echo "$msg<br>\n";
    flush();
}


function report_error($sql, $db)
{
    $error = mysqli_error($db);
    $errno = mysqli_errno($db);
    $msg   = "$errno:$error";

    logs::log(__FILE__, __LINE__, $sql, 0);
    logs::log(__FILE__, __LINE__, $msg, 0);

    echo "<br><b>$sql<br>\n$msg</b><br>\n";

    $msg   = "       Query: $sql\n";
    $msg  .= "Error Number: $errno\n";
    $msg  .= "  Error Text: $error\n";
    return $msg;
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
    $a[] = html_link("c-asset.$dbg", 'asset');
    $a[] = html_link("c-notify.$dbg", 'notify');
    $a[] = html_link('../event/d-report.php', 'list');
    return jumplist($a);
}


function memstat($pid)
{
    $psm = `ps --pid $pid --no-headers -o rss,vsz`;
    return "mem (res KB, tot KB) = $psm";
}



function ident($now)
{
    $date = datestring($now);
    $info = asi_info();
    $version = $info['svvers'];
    return "\n<h2>$date ($version)</h2>\n";
}

// asset reports have rows and columns

function html_reportsummary_header($rows, $name)
{
    $msg  = html_new_pagetable();
    $msg .= <<< HERE

<tr>
 <td colspan="3" bgcolor="#333399">
  <a name="RS"></a>
  <font face="verdana,helvetica" color="white" size="2">
   <b>Report $name Summary</b>
  </font>
 </td>
</tr>

<tr>
 <td colspan="3">
  <font face="verdana,helvetica" size="2" color="333399">
    This report covers <font color="000000">$rows</font> events.
  </font>
 </td>
</tr>

HERE;

    return $msg;
}


function html_eventsummary_subheader($name1, $key1, $data1, $name2, $assetlink1, $id)
{
    $name2_uc = ucwords($name2);
    $anchor1 = s_anchor($id, $key1);
    $anchor2 = e_anchor($id, $key1);

    return <<< HERE

<tr>
 <td colspan="2" valign="top">
  <a name="$anchor1"></a>
  <font face="verdana,helvetica" size="2">
  <b>
   <font color="333399">$name1:</font> $assetlink1
   <font color="333399">(</font>$data1
   <font color="333399">items)</font>
  </b>
  </font>
 </td>
 <td valign="top" align="right" nowrap>
  <font face="verdana,helvetica" size="2">
   <a href="#$anchor2">view events</a>
  </font>
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
   <b>Events</b>
  </font>
 </td>
</tr>

HERE;
}



function html_events_header()
{
    $m  = html_pagebreak('on');
    $m .= <<< HERE

<tr>
 <td colspan="2" bgcolor="#333399">
  <a name="E"></a>
  <font face="verdana,helvetica" color="white" size="2">
   <b>Events</b>
  </font>
 </td>
</tr>

HERE;

    return $m;
}


function html_events_subheader($prev_group, $order1, $ctr, $id)
{
    $prev_group = ($prev_group == '') ? '(unknown)' : $prev_group;
    $anchor = e_anchor($id, $prev_group);
    $label = ucwords($order1);
    return <<< HERE

<tr>
 <td colspan="2" valign="top">
  <a name="$anchor"></a>
  <font face="verdana,helvetica" size="2">
   <b>
    <font color="333399">$label:</font> $prev_group
    <font color="333399">(</font>$ctr
    <font color="333399">items)</font>
   </b>
  </font>
 </td>
</tr>

HERE;
}


function html_events_data($name, $data)
{
    return <<< HERE

<tr>
 <td valign="top" align="right" width="18%">
  <font face="verdana,helvetica" size="2" color="333399">
   $name:
  </font>
 </td>
 <td valign="top" width="82%">
  <font face="verdana,helvetica" size="2">
   $data
  </font>
 </td>
</tr>

HERE;
}


function html_events_data_string($name, $data)
{
    $str = ($data) ? nl2br($data) : '&nbsp;';
    return html_events_data($name, $str);
}


function html_top_member($span, $text, $id)
{
    return <<< HERE

<tr>
 <td colspan="$span" bgcolor="C0C0C0" align="right">
  <font face="verdana,helvetica" size="1">
   <a href="#$id">$text</a>
   </font>
 </td>
</tr>

HERE;
}


function html_membersummary_header(&$row)
{
    $span = 3;
    $id = $row['id'];
    $m  = '';
    $m .= html_top_member(3, 'top of report', 'report_' . $id);
    $m .= html_pagebreak('on');
    $m .= <<< HERE

    <tr>
     <td colspan="$span" bgcolor="#333399">
      <a name="ES_$id"></a>
       <font face="verdana,helvetica" color="white" size="2">
        <b>Event Summary</b>
       </font>
     </td>
    </tr>

HERE;

    return $m;
}


/* -----------------------------------------------------------------
|                                                                   |
|  ACTION (And Supporting) FUNCTIONS                                |
|                                                                   |
-------------------------------------------------------------------*/

function compare_numeric($a, $b)
{
    $aa = (int) $a;
    $bb = (int) $b;
    if ($aa == $bb) {
        return 0;
    }
    return ($aa > $bb) ? -1 : 1;
}

function plural($number, $word)
{
    if ($number == 1)
        $words = $word;
    else
        $words = sprintf('%ss', $word);
    return strtolower($words);
}

// function affected($res,$db)
// {
//     return ($res)? (mysql_affected_rows($db)) : 0;
// }

/*
    |  Mark the report as complete.
    */

function update_report(&$env, &$row, $db)
{
    $num = 0;
    $rid = $row['id'];
    if ($rid > 0) {
        $bias = $env['bias'];
        $now  = $env['now'];
        $next = next_cycle($row, $now) + $bias;
        $last = time();
        $dn   = date('m/d H:i', $next);
        $dl   = date('m/d H:i', $last);
        $sql  = "update Reports set\n"
            . " last_run = $last, -- $dl\n"
            . " next_run = $next, -- $dn\n"
            . " this_run = 0,\n"
            . " retries  = 0\n"
            . " where id = $rid\n"
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
        $sql = "update Reports set\n"
            . " this_run = $now,\n"
            . " next_run = -1,\n"
            . " retries  = retries+1\n"
            . " where id = $id\n"
            . " and next_run > 0\n"
            . " and enabled = 1";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}


/*
    |  Proof of life.
    |
    |  We don't give up until this_run is more than
    |  report_timeout seconds old.
    */

function touch_report($id, $db)
{
    $num = 0;
    if ($id > 0) {
        $now = time();
        $sql = "update Reports set\n"
            . " this_run = $now\n"
            . " where id = $id\n"
            . " and next_run < 0\n"
            . " and this_run > 0\n"
            . " and enabled = 1";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}


function disable_report($db, $id)
{
    $num = 0;
    if ($id > 0) {
        $now = time();
        $sql = "update Reports set\n"
            . " enabled = 0,\n"
            . " next_run = 0,\n"
            . " this_run = 0,\n"
            . " modified = $now\n"
            . " where id = $id";
        $res = command($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}



function find_stuck_report($db)
{
    $sql = "select * from Reports\n"
        . " where next_run < 0\n"
        . " and enabled = 1";
    return find_many($sql, $db);
}



function delete_report($db, $id)
{
    $res = false;
    $num = 0;
    if ($id > 0) {
        $sql = "delete from Reports\n where id = $id";
        $res = command($sql, $db);
        $num = affected($res, $db);
        if ($num) {
            $sql = "delete from ReportGroups\n where owner = $id or member = $id";
            $res = command($sql, $db);
        }
    }
    return $num;
}


/*
    | $env   = global variables
    | $row   = current report
    | $txt   = details
    | $time  = servertime between xxx and yyy 
    | $query = query filter
    | $db    = database handle
    |
    | Selects from the events table into the temporary SelecteEvents table
    | with all restrictions and filters.
   */
function populate_selectedevents(&$env, &$row, $txt, $umin, $umax, $query, $db)
{
    $exception = '';
    $inc_list  = array();
    $auth      = safe_addslashes($row['username']);
    $newmin = 0;
    $newmax = 0;

    /* we want to add mgroupid restrictions here if there are any  */
    $exception = GRPS_build_host_list(
        $row['group_include'],
        $row['group_exclude'],
        $auth,
        $db
    );

    $interval = server_def('report_qinterval', 86400, $db);
    $fake  = fake_event_field();
    $query = ($query) ? "and ($query)" : '';

    $newmin = $umin;
    $newmax = $umax;
    while (($newmin + $interval) < $umax) {
        $newmax = $newmin + $interval;
        $time = timeinterval($newmin, $newmax, 'servertime');
        $sql   = "insert ignore into SelectedEvents\n"
            . " select $txt,\n"
            . " 'None' as $fake\n"
            . " from Events as E FORCE INDEX (servertime)\n"
            . " left join temp_Census as C\n"
            . " on (E.customer = C.site and E.machine = C.host)\n"
            . " where C.deleted = 0\n"
            . " and $time\n"
            . $exception
            . " $query";
        slow_command($env, $row, $sql, $db);

        $newmin = $newmin + $interval;
    }

    if ($newmin < $umax) {
        $time = timeinterval($newmin, $umax, 'servertime');
        $sql   = "insert ignore into SelectedEvents\n"
            . " select $txt,\n"
            . " 'None' as $fake\n"
            . " from Events as E FORCE INDEX (servertime)\n"
            . " left join temp_Census as C\n"
            . " on (E.customer = C.site and E.machine = C.host)\n"
            . " where C.deleted = 0\n"
            . " and $time\n"
            . $exception
            . " $query";
        slow_command($env, $row, $sql, $db);
    }
}


/* inserts the event report into the selectedevents temp table. */
function insert_selectedevents(&$env, $list, $row, $db)
{
    $query     = '';
    $cycle     =   $row['cycle'];
    $umin      = @$row['umin'];   // only for immediate reports
    $umax      = @$row['umax'];   // only for immediate reports

    if ($cycle <= 3) {
        $umax = last_cycle($row, time());
        $umin = calc_umin($umax, $cycle);
    }

    $txt  = join(",", $env['fld']);

    /* set the queryfilter */
    $set  = array();
    reset($list);
    foreach ($list as $key => $data) {
        if ($data['searchstring']) {
            $tmp   = stripslashes($data['searchstring']);
            $set[] = "($tmp)";
        }
    }

    if ($set)
        $query = join(" or\n ", $set);

    /* select from Events into SelectedEvents.
           gentext() will select from SelectedEvents instead of
           the Events table. This increases the speed of global
           reports on servers with many users and large event tables */
    populate_selectedevents($env, $row, $txt, $umin, $umax, $query, $db);
}


function drop_temp_table($flag, $db)
{
    $tab = ($flag) ? 'SelectedEvents' : 'TempEvents';
    $sql = "drop table $tab";
    redcommand($sql, $db);
}


function drop_coreCensus_temptable($db)
{
    $sql = "drop table if exists temp_Census";
    redcommand($sql, $db);
}

/*
    |  creates a temporary database table.
    |
    |  This will be mostly the same as the Events table,
    |  except that it will have a new field at the end
    |  named 'queryname', which will contain the name
    |  of the query which found this event.
    |
    |  Normally we require that events are unique ...
    |  so, if multiple queries find the same event, it
    |  will be entered only by the first query that
    |  matches.
    |
    |  However, in the special case where the query
    |  is one of our sorting options, then we do not
    |  use the idx primary key.  Instead, we generate
    |  a unique key for (idx,queryname) ... this
    |  means that in a query sorting report of N
    |  searches, we may find as many as N instances
    |  of the same event.
    |
    |  6/22/05: set table name & key
    |           flag = 0; TempEvents
    |           flag = 1; SelectedEvents
    */


function build_temp_table(&$env, &$row, $flag, $db)
{
    $good = false;
    $ev   = $env['evnt'];
    $o1   = $row['order1'];
    $o2   = $row['order2'];
    $o3   = $row['order3'];
    $o4   = $row['order4'];

    $tab = ($flag) ? 'SelectedEvents' : 'TempEvents';
    $key = ($flag) ? 'primary key (idx), key customer (customer)' : '';

    $q   = fake_event_field();
    $dup = (($o1 == $q) || ($o2 == $q) || ($o3 == $q) || ($o4 == $q));
    if ($ev) {
        $ev['name'][] = $q;
        $ev['type'][$q] = 'varchar(50)';
        $ev['null'][$q] = '';
        $ev['defs'][$q] = '';
        $ev['keys'][$q] = '';
        $name = $ev['name'];
        $sql = "create temporary table $tab(\n";
        $pri = '';
        reset($name);
        foreach ($name as $xxx => $fld) {
            $type = $ev['type'][$fld];
            $defs = $ev['defs'][$fld];
            $keys = $ev['keys'][$fld];
            $null = $ev['null'][$fld];
            if ($keys == 'PRI') {
                $pri = $fld;
            }
            $sql .= "  $fld $type";
            if ($defs != '') {
                $qdf  = safe_addslashes($defs);
                $sql .= " default '$qdf'";
            }
            if (($null == '') || ($keys != '')) {
                $sql .= ' not null';
            }
            $sql .= ",\n";
        }
        if (!$key)
            $key = ($dup) ? "unique key uniq ($pri,$q)" : "primary key ($pri), key customer (customer)";
        $sql .= "  $key\n)";
        $good = (redcommand($sql, $db)) ? true : false;
        if (!$good) {
            drop_temp_table($flag, $db);
            $good = (redcommand($sql, $db)) ? true : false;
        }
    }
    return $good;
}


function empty_temp_table($db)
{
    $sql = "delete from TempEvents";
    redcommand($sql, $db);
}


function show_details(&$env, &$row, $events, $name1, $order1)
{
    $format      = $row['format'];
    $config      = $row['config'];
    $file        = $row['file'];
    $links       = $row['links'];
    $id          = $row['id'];
    $detaillinks = $row['detaillinks'];
    $odir        = $env['odir'];

    $html = ($format != 'text');
    $base = ($file) ? "/$odir" : $env['base'];

    $prf = "$base/event/detail.php?eid=";
    $msg = '';
    //   $msg .= "debug: format:$format, config:$config\n";
    $cfg  = explode(':', $config);
    $num  = mysqli_num_rows($events);
    $good = mysqli_data_seek($events, 0);

    /* handle to the temporary file */
    $tmpfile_h = $row['tmpfile_h'];

    if ((1 <= $num) && ($good)) {
        if ($html) {
            $msg .= html_events_header();
        }

        $ctr = 0;
        $buffer = '';
        $pagebreak = 'off';
        $prev_group = '';
        while ($event = mysqli_fetch_assoc($events)) {
            $idx = $event['idx'];
            $event['entered']    = mysqltime($event['entered']);
            $event['servertime'] = mysqltime($event['servertime']);

            $url = "$prf$idx";
            if ($html) {
                if ($order1) {
                    if ($event[$order1] != $prev_group) {
                        if ($buffer) {
                            $msg .= html_pagebreak($pagebreak);
                            $msg .= html_events_subheader($prev_group, $name1, $ctr, $id);
                            $msg .= $buffer;
                            $pagebreak = 'on';
                        }
                        $ctr = 0;
                        $buffer = '';
                    }
                }

                if (!my_write($tmpfile_h, $msg)) return false;
                $msg = '';

                /* the user only wants links to events in
                       the details section of the report   */
                if (!$detaillinks) {
                    /* create the html for each report detail */
                    $n = safe_count($cfg);
                    for ($i = 0; $i < $n; $i++) {
                        $name = $cfg[$i];
                        if ($name) {
                            $data = $event[$name];

                            if (is_string($data) && ($data))
                                $buffer .= html_events_data_string($name, $data);
                            if (is_integer($data))
                                $buffer .= html_events_data($name, $data);
                        }
                    }
                }
                if ($links) {
                    $buffer .= html_rlink($url, 'view event detail');
                }
                if ($row['member']) {
                    $buffer .= html_top_member(2, 'top of report', 'report_' . $row['id']);
                } else
                    $buffer .= html_separator(2, 'back to top');

                $ctr++;
                if ($order1) {
                    $prev_group = $event[$order1];
                }
            } else {
                $msg .= "\n";
                $msg .= "-----------------------------------------------------\n\n";
                if ($links) {
                    $msg .= "$url\n";
                }
                $n  = safe_count($cfg);
                for ($i = 0; $i < $n; $i++) {
                    $name = $cfg[$i];
                    if ($name) {
                        $data = $event[$name];
                        if (is_string($data) && ($data))
                            $msg .= "$name: $data\n";
                        if (is_integer($data))
                            $msg .= "$name: $data\n";
                    }
                }
                if (!my_write($tmpfile_h, $msg)) return false;
                $msg = '';
            }
            if (!my_write($tmpfile_h, $msg)) return false;
            /* a single event detail is finished here */
        }
        if ($html) {
            $msg .= html_pagebreak($pagebreak);
            if ($order1) {
                $msg .= html_events_subheader($prev_group, $name1, $ctr, $id);
            }
            $msg .= $buffer;
            $msg .= html_close_table();

            if (!my_write($tmpfile_h, $msg)) return false;
        }
    }
    return $tmpfile_h;
}


function event_summaries(&$env, &$row, &$imgs, $count1, $asset_hrefs, $index1, $name1, $name2)
{
    $msg = '';

    $mdb    = $env['mdb'];
    $sdb    = $env['sdb'];
    $odir   = $env['odir'];
    $jpgq   = $env['jpgq'];
    $server = $env['server'];

    $format = $row['format'];
    $config = $row['config'];
    $file   = $row['file'];
    $order1 = $row['order1'];
    $order2 = $row['order2'];
    $id     = $row['id'];

    // $do_alinks: whether to provide a link to the asset detail page
    if (safe_count($asset_hrefs))
        $do_alinks = 1;
    else
        $do_alinks = 0;

    $html  = ($format != 'text');
    $base  = ($file) ? "/$odir" : $env['base'];
    $chart = charting($row);

    if ($html) {
        if ($row['member']) {
            $msg .= html_membersummary_header($row);
        } else
            $msg .= html_eventsummary_header('Event');
    } else
        $msg .= "\n\n\n\n";

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
            if ($html) {
                // turn machine name into a link to asset detail page
                $assetlink1 = $key1;
                if ($do_alinks && $order1 == 'machine') // $key1 is machinename
                {
                    $asset_href1 = $asset_hrefs[$key1];
                    if (strlen($asset_href1)) {
                        $assetlink1  = html_link($asset_href1, $key1);
                    }
                }

                $msg .= html_eventsummary_subheader($name1, $key1, $data1, $name2, $assetlink1, $id);
            } else {
                $msg .= "\n\n";
                $msg .= "$name1: $key1 $data1 items\n";
            }


            # display rows of data
            if (safe_count($count2)) {
                reset($count2);
                foreach ($count2 as $key => $data) {
                    if ($html) {
                        // turn machine name into a link to asset detail page
                        $assetlink = $key;
                        if ($do_alinks && $order2 == 'machine') // $key is machinename
                        {
                            $asset_href = $asset_hrefs[$key];
                            if (strlen($asset_href)) {
                                $assetlink  = html_link($asset_href, $key);
                            }
                        }

                        $msg .= html_eventsummary_data($assetlink, $data);
                    } else {
                        $msg .= "    $name2: $key $data events\n";
                    }
                }
                if ($html) {
                    if ($row['member']) {
                        $msg .= html_top_member(3, 'top of report', 'report_' . $row['id']);
                    } else
                        $msg .= html_separator(3, 'back to top');
                }
            }
        }
    }
    return $msg;
}


function process_data(&$env, &$row, &$imgs, $events, $rows, $order1, $order2, $name1, $name2, $access)
{
    $msg = '';
    $n1 = 0;
    $n2 = 0;

    $distinct1 = '';
    $distinct2 = '';
    $count1 = array();
    $count2 = array();
    $index1 = array();
    $asset_hrefs = array();

    $mdb     = $env['mdb'];
    $sdb     = $env['sdb'];
    $odir    = $env['odir'];
    $jpgq    = $env['jpgq'];
    $maxd    = $env['maxd'];
    $server  = $env['server'];

    $asset_exists = $env['asset_exists'];
    $tmpfile_h    = $row['tmpfile_h'];

    $file    = $row['file'];
    $details = $row['details'];
    $config  = $row['config'];
    $format  = $row['format'];
    $links   = $row['links'];
    $alinks  = $row['assetlinks'];
    $id      = $row['id'];
    $chart   = charting($row);
    $html    = ($format != 'text');
    $base    = ($file) ? "/$odir" : $env['base'];

    /*
        |  Handle the case where the user has asked for event
        |  details, but we find an unexpectedly large number
        |  of events.   Getting details for 80,000 event
        |  records is not helpful.
        |
        |  This feature is controlled by the server variable
        |  report_max_details.  Setting it to zero disables
        |  it, in which case we'll produce the report or
        |  die trying.
        */

    if (($details) && (0 < $maxd) && ($maxd < $rows)) {
        $details = 0;

        $pid  = $env['pid'];
        $rid  = $row['id'];
        $name = $row['name'];
        $user = $row['username'];
        $stat = "p:$pid,r:$rid,e:$rows,m:$maxd,u:$user";
        $text = "report: suppress details ($stat) $name";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }

    // $do_alinks: whether to provide a link to the asset detail page
    if ($asset_exists && $alinks && ($order1 == 'machine' || $order2 == 'machine'))
        $do_alinks = 1;
    else
        $do_alinks = 0;

    $good = mysqli_data_seek($events, 0);
    if (($order1) && ($good)) {
        while ($event = mysqli_fetch_assoc($events)) {
            $test1 = $event[$order1];
            if (empty($test1)) $test1 = '(unknown)';
            if ($test1 != $distinct1) {
                if ($distinct1) {
                    $index1[$distinct1] = $count2;
                }
                $distinct1 = $test1;
                $count1[$distinct1] = 0;
                $count2 = array();
            }

            if ($distinct1) {
                if (isset($count1[$distinct1]))
                    $count1[$distinct1]++;
                else
                    $count1[$distinct1] = 1;

                // populate array of asset hrefs keyed by machine
                if ($do_alinks && $order1 == 'machine') {
                    if (!isset($asset_hrefs[$distinct1])) {
                        $asset_href = asset_href($env, $row, $access, $distinct1, $mdb);
                        $asset_hrefs[$distinct1] = $asset_href;
                    }
                }
            }

            if ($order2) {
                $test2 = $event[$order2];
                if (empty($test2)) $test2 = '(unknown)';
                if ($test2 != $distinct2) {
                    $distinct2 = $test2;
                    $count2[$distinct2] = 0;
                }
                if ($distinct2) {
                    if (isset($count2[$distinct2]))
                        $count2[$distinct2]++;
                    else
                        $count2[$distinct2] = 1;

                    // populuate array of asset hrefs keyed by machine
                    if ($do_alinks && $order2 == 'machine') {
                        if (!isset($asset_hrefs[$distinct2])) {
                            $asset_href = asset_href($env, $row, $access, $distinct2, $mdb);
                            $asset_hrefs[$distinct2] = $asset_href;
                        }
                    }
                }
            }
        }
        if ($order2) {
            if ($distinct1) {
                $index1[$distinct1] = $count2;
            }
        }

        $n1 = safe_count($count1);
        if ($n1 > 0) {
            $names = plural($n1, $name1);

            if ($html)
                $msg .= html_distinct($n1, $names);
            else
                $msg .= "Events are reported for $n1 distinct $names.\n";

            uasort($count1, 'compare_numeric');


            # create and display the chart
            if ($chart) {
                $iref = report_iref($row, $count1, $name1, $jpgq, $base, $imgs, $server);
                $msg .= html_reportsummary_subheader($iref, $name1, 'Events', $format);
            }

            if (!my_write($tmpfile_h, $msg)) return false;

            # display the rows of data
            $n = 0;
            reset($count1);
            foreach ($count1 as $key1 => $data1) {
                if ($do_alinks && $order1 == 'machine') // $key1 is the machinename
                {
                    $asset_href1 = $asset_hrefs[$key1];
                } else {
                    $asset_href1 = '';
                }
                $n++;
                if ($html) {
                    // turn machine name into a link to asset detail page
                    $assetlink1 = $key1;
                    if ($do_alinks && $order1 == 'machine') // $key1 is machinename
                    {
                        $asset_href1 = $asset_hrefs[$key1];
                        if (strlen($asset_href1)) {
                            $assetlink1 = html_link($asset_href1, $key1);
                        }
                    }
                    if (!my_write($tmpfile_h, html_reportsummary_data($n, $key1, $data1, $assetlink1, $id)))
                        return false;
                } else {
                    if (!my_write($tmpfile_h, sprintf("%4d %s -- %d events\n", $n, $key1, $asset_href1, $data1)))
                        return false;
                }
            }

            if ($order2) {
                if (!my_write($tmpfile_h, event_summaries($env, $row, $imgs, $count1, $asset_hrefs, $index1, $name1, $name2)))
                    return false;
            }
        }
    }

    if (!$html) {
        if (!my_write($tmpfile_h, "-----------------------------------------------------\n\n")) return false;
    }

    if ($details) {
        $tmpfile_h = show_details($env, $row, $events, $name1, $order1);
    } else {
        if ($html) {
            if (!my_write($tmpfile_h, html_close_table())) return false;
        }
    }
    return $tmpfile_h;
}

/*
    |   get_asset_mid
    |
    |   Gets the machineid(s) for a given machine name and list of sites/customers.
    |   Only returns a machineid if one exists (meaning that asset data exists
    |   for that machine) and only ONE exists.
    |
    |   This is a kludge to get around the possibility of having machines with
    |   the same name on more than one site.
    |
    |   parameters:
    |       $access:    String comprised of a comma-separated list of quoted
    |                   sites that we are reporting on.
    |       $machine:   The machine name.
    |       $db:        The handle to the database connection.
    */

function get_asset_mid($access, $machine, $db)
{
    $mid = 0;
    if (strlen($access) && strlen($machine)) {
        // Make sure there is some asset data
        $qm  = safe_addslashes($machine);
        $sql = "select machineid\n"
            . " from " . $GLOBALS['PREFIX'] . "asset.Machine\n"
            . " where host = '$qm'\n"
            . " and cust in ($access)";
        $row = find_one($sql, $db);

        if ($row) {
            $mid = $row['machineid'];
        }
    }
    return $mid;
}


/*
    |   asset_href
    |
    |   Displays a row in an HTML table listing user data.
    |
    |   parameters:
    |       $env:       Compendium of various necessary variables.
    |                   sites that we are reporting on.
    |       $row:       Row from Reports table, i.e. report definition info.
    |       $access:    String comprised of a comma-separated list of quoted
    |                   sites that we are reporting on.
    |       $machine:   The machine name.
    |       $db:        The handle to the database connection.
    */

function asset_href(&$env, &$row, $access, $machine, $db)
{
    $href = '';
    $file = $row['file'];
    $odir = $env['odir'];
    $mid  = get_asset_mid($access, $machine, $db);
    if ($mid) {
        $base = ($file) ? "/$odir" : $env['base'];
        $href = "$base/asset/detail.php?mid=$mid";
    }
    return $href;
}


/*
    |  I don't want to use the mysql "BETWEEN" operator here because
    |  a SELECT BETWEEN includes both endpoints.  In the most frequent
    |  case I expect to select from midnight to midnight, and an event
    |  that happens exactly at midnight should only show up in one of
    |  the reports.
    |
    |  17-Jun-03 ... I've changed my mind, I think the between is
    |  probably more efficient.  Just use $umax - 1 as the upper
    |  endpoint.
    */

function timeinterval($umin, $umax, $what)
{
    $max  = $umax - 1;
    return "$what between $umin and $max";
}

function orderby($order1, $order2, $order3, $order4)
{
    $order = '';
    if ($order1) {
        $order = "order by $order1";
        if ($order2) {
            $order .= ", $order2";
            if ($order3) {
                $order .= ", $order3";
                if ($order4) {
                    $order .= ", $order4";
                }
            }
        }
    }
    return $order;
}


function report_debug(&$env, $row, $email, $umin, $umax, $list)
{
    if ($env['debug']) {
        $rnow         = time();
        $cron         = $env['now'];
        $id           = $row['id'];
        $owner        = $row['owner'];  // whether owner or proxy user
        $cycle        = $row['cycle'];
        $name         = $row['name'];
        $username     = $row['username'];
        $hour         = $row['hour'];
        $file         = $row['file'];
        $minute       = $row['minute'];
        $format       = $row['format'];
        $defmail      = $row['defmail'];
        $details      = $row['details'];
        $last         = $row['last_run'];
        $next         = $row['next_run'];
        $search_list  = $row['search_list'];
        $global       = $row['global'];
        $include_user = $row['include_user'];
        $include_text = $row['include_text'];
        $subject_text = $row['subject_text'];
        $omit         = $row['omit'];
        $detaillinks  = $row['detaillinks'];

        $r_wday  = @$row['wday'];   // only for weekly reports
        $r_mday  = @$row['mday'];   // only for monthly reports
        $config  = @$row['config']; // optional
        $o1      = @$row['order1'];
        $o2      = @$row['order2'];
        $o3      = @$row['order3'];
        $o4      = @$row['order4'];

        $drnow  = datestring($rnow);
        $dcron  = datestring($cron);
        $dumin  = datestring($umin);
        $dumax  = datestring($umax);
        $dlast  = datestring($last);
        $dnext  = datestring($next);

        echo "<p><b>\n";
        echo "cron: ($cron) $dcron<br>\n";
        echo "rnow: ($rnow) $drnow<br>\n";
        echo "umin: ($umin) $dumin<br>\n";
        echo "umax: ($umax) $dumax<br>\n";
        echo "last: ($last) $dlast<br>\n";
        echo "next: ($next) $dnext<br>\n";
        echo "name($name) email($email) format($format) username($username) owner($owner) id($id)<br>\n";
        echo "include_user($include_user) include_text($include_text) subject_text($subject_text) omit($omit)";
        echo "cycle($cycle) hour($hour) minute($minute) wday($r_wday) mday($r_mday)<br>\n";
        echo "o1($o1) o2($o2) o3($o3) o4($o4)<br>\n";
        echo "details($details) detaillinks($detaillinks) defmail($defmail) file($file)<br>\n";
        echo "config($config) search_list($search_list) global($global)<br>\n";
        echo "</b></p>\n";
        $n = safe_count($list);
        echo "queries: $n<br>\n";

        if ($n > 0) {
            reset($list);
            foreach ($list as $qid => $data) {
                $name  = $data['name'];
                $query = $data['searchstring'];
                echo "<p><b>id:$qid,name:$name<br>Query:<i>$query</i></b></p>\n";
            }
        }
        echo "<br clear=\"all\">\n";
        echo "<br clear=\"all\">\n";
    }
}


function slow_command(&$env, &$row, $sql, $db)
{
    $env['suicide'] = 0;
    $slow = $env['slow'];
    $timo = $env['timo'];
    $dbid = $env['dbid'];
    $pid  = $env['pid'];
    $rid  = $row['id'];
    $name = $row['name'];
    $user = $row['username'];
    $msec = 0;
    $rows = 0;
    //     serious_message("process $pid, $rid before\n $sql");
    $res  = redcommand_time($sql, $msec, $db);
    $rows = affected($res, $db);
    //     serious_message("process $pid, $rid after\n $sql");


    $secs = microtime_show($msec);
    $stat = "p:$pid,r:$rid,d:$dbid,u:$user,e:$rows";

    if ($msec > $slow) {
        $text = "slow ($stat) in $secs\n$sql";
        serious_message($text);
    }
    debug_note("query ($stat) $name in $secs");

    /*
        |  If our select takes more than TIMO seconds,
        |  this probably means the database is locked.
        |
        |  In any event, this means that a later instance
        |  of c-report has probably ALREADY assumed this
        |  process has crashed, and has taken responsibility
        |  for cleaning up the mess.
        |
        |  In this case the best thing to do is to just
        |  exit without attempting to change anything.
        */

    if (($msec > $timo) && ($timo > 0)) {
        $dbid = $env['dbid'];
        $user = $row['username'];
        $secs = microtime_show($msec);
        $stat = "p:$pid,r:$rid,t:$timo,d:$dbid";
        $text = "suicide ($stat) in $secs";
        serious_message($text);
        $env['suicide'] = 1;
    }
    return $res;
}


function fetch_sites($events)
{
    $out = array();
    $set = array();
    mysqli_data_seek($events, 0);
    while ($row = mysqli_fetch_assoc($events)) {
        $site = $row['customer'];
        $set[$site] = true;
    }
    reset($set);
    foreach ($set as $site => $data) {
        $out[] = $site;
    }
    reset($out);
    natcasesort($out);
    return $out;
}


function html_member_rule()
{
    return <<< HERE

        <hr color="#333399" noshade size="2" width="610" align="left">

HERE;
}


function return_nbsp($grp)
{
    if ($grp == '')
        $grp = '&nbsp;';

    return $grp;
}


function html_member_params($grp1, $grp2, $grp3, $grp4, $name)
{
    $grp1 = return_nbsp($grp1);
    $grp2 = return_nbsp($grp2);
    $grp3 = return_nbsp($grp3);
    $grp4 = return_nbsp($grp4);

    $link     = marklink('#report_list', 'back to report list');
    $message  = html_new_pagetable();
    $message .= <<< HERE
        <tr>
          <td colspan="3" bgcolor="#333399">
            <font face="verdana,helvetica" color="white" size="2">
              <b>$name</b>
            </font>
          </td>
        </tr>

        <tr>
          <td colspan="3" bgcolor="C0C0C0" align="right">
            <font face="verdana,helvetica" size="1">
              $link
            </font>
          </td>
        </tr>

        <tr>
          <td colspan="2">
            <font face="verdana,helvetica" size="2" color="000000">
              <b>Summary Information Groupings</b>
            </font>
          </td>
          <td>
            &nbsp;
          </td>
        </tr>

        <tr>
          <td colspan="2">
            <font face="verdana,helvetica" size="2" color="000000">
              Group by first:
            </font>
          </td>
          <td>
            $grp1
          </td>
        </tr>
        <tr>
          <td colspan="2">
            <font face="verdana,helvetica" size="2" color="000000">
              Group by second:
            </font>
          </td>
          <td>
            $grp2
          </td>
        </tr>

        <tr>
          <td colspan="2">
            <font face="verdana,helvetica" size="2" color="000000">
              <b>Detailed Information Sorting</b>
            </font>
          </td>
          <td>
            &nbsp;
          </td>
        </tr>

        <tr>
          <td colspan="2">
            <font face="verdana,helvetica" size="2" color="000000">
              Sort by:
            </font>
          </td>
          <td>
           $grp3
          </td>
        </tr>

        <tr>
          <td colspan="2">
            <font face="verdana,helvetica" size="2" color="000000">
              Sort by:
            </font>
          </td>
          <td>
            $grp4
          </td>
        </tr>

HERE;

    return $message;
}


function build_owner_header(&$env, &$row, $umin, $umax, $num, $rprt_name, $sites)
{
    $message = '';
    $set = array();
    $authuser     = $row['username'];
    $format       = $row['format'];
    $file         = $row['file'];
    $email        = $row['emaillist'];
    $title        = $row['name'];
    $include_user = $row['include_user'];
    $include_text = $row['include_text'];
    $subject_text = $row['subject_text'];

    $tmin  = intval($umin);
    $tmax  = intval($umax);
    $tnow  = intval(time());

    $seconds = $umax - $umin;
    $hours   = intval($seconds / 3600);

    $html = ($format != 'text');
    $dest = ($file) ? $authuser : $email;

    $title_plus = $title . " ($num events)";
    if ($include_user) {
        $title_plus = $title_plus . ' for user ' . $authuser;
    }
    if ($include_text) {
        $title_plus = $title_plus . ': ' . $subject_text;
    }

    $tmin = datestring($tmin);
    $tmax = datestring($tmax);
    $tnow = datestring($tnow);

    if (!$html) {
        $msites   = implode("\n", $sites);

        $message .= "   Report Title: $title_plus\n";
        $message .= "        Creator: $authuser\n";
        $message .= "     Recipients: $dest\n";
        $message .= "          Sites: \n$msites";
        $message .= "\n";

        $message .= "     Start Date: $tmin\n";
        $message .= "       End Date: $tmax\n";
        $message .= "    Report Date: $tnow\n";
        $message .= "   Elapsed Time: $hours hours\n";
        $message .= "\n";
    } else {
        if ($file)
            $message .= file_links($env, $row);
        else
            $message .= html_page_title($env, $row, $title);
        $message .= html_rule();
        $message .= html_table(3, 0, 'C0C0C0', 0);
        $message .= html_stats($title, $authuser, $dest);
        $message .= html_empty_row(2);
        $message .= html_times($tmin, $tmax, $tnow, $hours);
        if ($rprt_name) {
            $rprt_rows = array();
            $message  .= mark('report_list');
            $message  .= html_close_table();
            $message  .= html_new_pagetable();
            $message  .= <<< HERE
                <br>
                <tr>
                  <td colspan="3" bgcolor="#333399">
                    <font face="verdana,helvetica" color="white" size="2">
                      <b>Reports</b>
                    </font>
                  </td>
                </tr>
HERE;

            reset($rprt_name);
            foreach ($rprt_name as $key => $value) {
                $link     = marklink("#report_$key", $value);
                $ES_link  = marklink("#ES_$key", 'Event Summary');
                $message .= <<< HERE
                     <tr>
                       <td colspan="2">
                         <font face="verdana,helvetica" size="2" color="000000">
                           $link
                         </font>
                       </td>
                       <td align="right">
                         $ES_link
                       </td>
                     </tr>
HERE;
            }
            $message .= html_close_table();
        }
    }

    echo "<p>Sending report '$title' to '$dest'.</p><br>";

    $subject = "Report: $title_plus";
    $set[0]  = $subject;
    $set[1]  = $message;

    return $set;
}

/*
    |  process one single report for one single user.
    |
    |  return 1 for success or 0 for failure, -1 for FATAL error.
    */


function gentext(&$env, &$row, $email, $umin, $umax, $list, $report_id, &$imgs, $sites)
{
    $set = array();

    report_debug($env, $row, $email, $umin, $umax, $list);

    $num       = 0;
    $sdb       = $env['sdb'];
    $mdb       = $env['mdb'];
    $pid       = $env['pid'];
    $slow      = $env['slow'];
    $dbid      = $env['dbid'];
    $debug     = $env['debug'];
    $single    = $env['single'];
    $enames    = $env['enames'];
    $server    = $env['server'];

    $tmpfile_h = $row['tmpfile_h'];

    $id       = $row['id'];
    $name     = $row['name'];
    $file     = $row['file'];
    $links    = $row['links'];
    $format   = $row['format'];
    $details  = $row['details'];
    $authuser = $row['username'];
    $global   = $row['global_report'];

    $env['link'] = $links;
    $env['file'] = $file;

    $member = ($id != $report_id);
    $row['member']  = $member;

    $config = @$row['config'];
    $order1 = @$row['order1'];
    $order2 = @$row['order2'];
    $order3 = @$row['order3'];
    $order4 = @$row['order4'];

    $include_user = $row['include_user'];
    $include_text = $row['include_text'];
    $subject_text = $row['subject_text'];

    $now  = time();
    $tmin = datestring($umin);
    $tmax = datestring($umax);
    $tnow = datestring($now);

    $message = '';
    $cust    = '';

    empty_temp_table($sdb);

    $access = db_access($sites);  // turns array into quoted comma-delimited list
    $cust   = ($access) ? "customer in ($access)" : '(idx < 0)';
    $time   = timeinterval($umin, $umax, 'servertime');
    $txt    = join(',', $env['fld']);
    $fake   = fake_event_field();
    if ($list) {
        reset($list);
        foreach ($list as $qid => $data) {
            checkpid($pid, $mdb);
            touch_report($report_id, $mdb);
            $qname = safe_addslashes($data['name']);
            $query = stripslashes($data['searchstring']);
            $query = ($query) ? "and ($query)" : '';
            if ($global) {
                /* retrieve global report data from the SelectedEvents table */
                $sql = "insert ignore into TempEvents select\n"
                    . " $txt,\n"
                    . " '$qname' as $fake\n"
                    . " from SelectedEvents\n"
                    . " where $cust\n"
                    . " $query";
                $res = slow_command($env, $row, $sql, $sdb);
            } else {
                $exception = GRPS_build_host_list(
                    $row['group_include'],
                    $row['group_exclude'],
                    safe_addslashes($authuser),
                    $mdb
                );

                /* This has the same logic of populate_selectedevents but
                        there are too many differences to commonize them. */
                $interval = server_def('report_qinterval', 86400, $mdb);
                $newmin = $umin;
                $newmax = $umax;
                while (($newmin + $interval) < $umax) {
                    $newmax = $newmin + $interval;
                    $time = timeinterval($newmin, $newmax, 'servertime');
                    $sql = "insert ignore into TempEvents select\n"
                        . " $txt,\n"
                        . " '$qname' as $fake\n"
                        . " from Events as E FORCE INDEX (servertime)\n"
                        . " left join temp_Census as C\n"
                        . " on (E.customer=C.site and E.machine=C.host)\n"
                        . " where C.deleted = 0\n"
                        . " and $cust\n"
                        . " and $time\n"
                        . $exception
                        . " $query";
                    $res = slow_command($env, $row, $sql, $sdb);

                    $newmin = $newmin + $interval;
                }

                if ($newmin < $umax) {
                    $time = timeinterval($newmin, $umax, 'servertime');
                    $sql = "insert ignore into TempEvents select\n"
                        . " $txt,\n"
                        . " '$qname' as $fake\n"
                        . " from Events as E FORCE INDEX (servertime)\n"
                        . " left join temp_Census as C\n"
                        . " on (E.customer=C.site and E.machine=C.host)\n"
                        . " where C.deleted = 0\n"
                        . " and $cust\n"
                        . " and $time\n"
                        . $exception
                        . " $query";
                    $res = slow_command($env, $row, $sql, $sdb);
                }
            }
            if ($env['suicide']) return array();
        }
    } else {
        /* 
            | This section of code will probably never get reached.
            | When creating or editing a report you must specify an
            | event filter.
            | 11/07/05 Removed.
           */
        logs::log(__FILE__, __LINE__, "c-report: query filter is not set");
    }

    if (($order1 == 'servertime') || ($order1 == 'entered') ||
        ($order2 == 'servertime') || ($order2 == 'entered')
    ) {
        $message .= sprintf(" Group by first: %s\n",    $enames[$order1]);
        $message .= sprintf("Group by second: %s\n",    $enames[$order2]);
        $message .= "Error -- grouping by time currently unsupported.\n\n";
        $order1 = '';
        $order2 = '';
        $order3 = '';
        $order4 = '';
    }

    $html  = ($format != 'text');
    $order = orderby($order1, $order2, $order3, $order4);

    checkpid($pid, $mdb);
    touch_report($report_id, $mdb);

    $sql = "select * from TempEvents $order";
    //logs::log(__FILE__, __LINE__, "REPORT GENERATED: $title SQL: $sql",0);
    //$timer  = time();

    $events = slow_command($env, $row, $sql, $sdb);
    if ($env['suicide']) return array();

    //$timer = time() - $timer;
    //echo "<p>query: $sql, elapsed: $timer</p>";
    $rows = affected($events, $sdb);

    debug_note("rows:$rows, owner:$authuser, name: $name");

    if (pfTimeDrep) {
        $mem = memstat($pid);
        logs::log(__FILE__, __LINE__, "timing: rp1 $pid $id endsql $mem", 0);
    }

    $out = array();
    if (($sites) && ($rows)) {
        $out = fetch_sites($events);
    }
    if (!$out) {
        $out[] = 'None';
    }

    if ($events) {
        $grp1 = @$enames[$order1];
        $grp2 = @$enames[$order2];
        $grp3 = @$enames[$order3];
        $grp4 = @$enames[$order4];

        if ($html) {
            $message .= ($member) ? '<br>' : '';
            $message .= ($member) ? html_member_rule() : '';
            $message .= '<br>';
            $message .= mark("report_$id");
            $message .= ($member) ? html_member_params($grp1, $grp2, $grp3, $grp4, $name)
                : html_order_params($grp1, $grp2, $grp3, $grp4);
            $message .= html_empty_row(2);
            $message .= html_close_table();
            $message .= html_reportsummary_header($rows, $name);
        } else {
            $message .= sprintf(" Group by first: %s\n",    $grp1);
            $message .= sprintf("Group by second: %s\n",    $grp2);
            $message .= sprintf("        Sort by: %s\n",    $grp3);
            $message .= sprintf("        Sort by: %s\n\n\n", $grp4);

            $message .= "This report covers $rows events.\n";
            $message .= "-----------------------------------------------------\n\n";
        }

        if (!my_write($tmpfile_h, $message)) return array();

        if ($rows > 0) {
            $tmpfile_h = process_data(
                $env,
                $row,
                $imgs,
                $events,
                $rows,
                $order1,
                $order2,
                $grp1,
                $grp2,
                $access
            );

            /* process data failed during I/O */
            if (!$tmpfile_h) return array();
        } else {
            if (!my_write($tmpfile_h, html_close_table())) return array();
        }
        ((mysqli_free_result($events) || (is_object($events) && (get_class($events) == "mysqli_result"))) ? true : false);
    } else {
        if (!my_write($tmpfile_h, report_error($sql, $sdb))) return array();
    }

    $set['rows'] = $rows;
    return $set;
}


function find_members($report_id, $db)
{
    $sql = "select R.* from ReportGroups as G,\n"
        . " Reports as R\n"
        . " where G.owner = $report_id\n"
        . " and R.id = G.member\n"
        . " and R.aggregate = 0\n"
        . " order by name";
    return find_many($sql, $db);
}

function genreport(&$env, $row, $email, $umin, $umax, $list)
{
    $num   = 0;
    $rows  = 0;
    $set       = array();
    $rprt      = array();
    $out       = array();
    $imgs      = array();
    $sites     = array();
    $rprt_name = array();
    $message        = '';
    $member_message = '';

    $report_id     = $row['id'];
    $authuser      = $row['username'];
    $format        = $row['format'];
    $title         = $row['name'];
    $omit          = $row['omit'];
    $global_report = $row['global_report'];

    $origin = $env['origin'];
    $server = $env['server'];
    $sdb    = $env['sdb'];
    $pid    = $env['pid'];
    $mdb    = $env['mdb'];
    $id     = $report_id;

    $html   = ($format != 'text');

    /* create a tmp file */
    $tmpfile_h = tmpfile();
    if (!$tmpfile_h) return -1;

    $row['tmpfile_h'] = $tmpfile_h;

    if (isset($env['siteaccesstree'][$authuser])) {
        $auth   = $env['siteaccesstree'][$authuser];
        $active = $env['active'];
        $sites  = site_intersect($auth, $active);
    }

    /*
        |  If the report has site filtering enabled
        |  then we need to intersect the users sites
        |  with the ones specified with the report.
        |
        |  Then procede to calculate access as usual.
        */

    /*
        |  Intersect the user's authorized sites
        |  with the list of active ones.  We don't
        |  care about the inactive ones.
        */

    if (safe_count($sites) <= 0) {
        $stat = "p:$pid,r:$id,u:$authuser";
        $text = "no active sites ($stat)";
        serious_message($text);
        return 0;
    }

    if ($row['aggregate']) {
        $db  = $env['mdb'];
        $res = find_members($report_id, $db);
        reset($res);
        foreach ($res as $key => $data) {
            $data['owner']         = 0;
            $data['file']          = $row['file'];
            $data['format']        = $row['format'];
            $data['tmpfile_h']     = $row['tmpfile_h'];
            $data['global_report'] = $global_report;

            $list = build_queries($data, $db);
            $set  = gentext($env, $data, $email, $umin, $umax, $list, $report_id, $imgs, $sites);
            if (!$set) {
                return -1;
            }
            $rows = $set['rows'];
            if (!$rows && $omit) {   /* omit zero event reports & log */
                $stat = "report: empty (p:$pid,r:$id,u:$authuser)";
                logs::log(__FILE__, __LINE__, $stat, 0);
            } else {
                $mid             = $data['id'];
                $rprt_name[$mid] = $data['name'];
                $rprt[] = $set;
            }
        }
        if (!$rprt && $omit) /* all reports in aggregate are zero event */ {
            $stat = "report: empty (p:$pid,r:$id,u:$authuser)";
            logs::log(__FILE__, __LINE__, $stat, 0);
            return 0;
        }
    } else /* classic report, runs once. */ {
        $set = gentext($env, $row, $email, $umin, $umax, $list, $report_id, $imgs, $sites);
        if (!$set) {
            return -1;
        }
        $rows = $set['rows'];
        if (!$rows && $omit) {   /* omit zero event reports, log, return gracefully */
            $stat = "report: empty (p:$pid,r:$id,u:$authuser)";
            logs::log(__FILE__, __LINE__, $stat, 0);
            return 0;
        } else {
            $rprt[] = $set;
        }
    }
    reset($rprt);
    foreach ($rprt as $tmp) {
        $rows = $tmp['rows'];
        $num  = $num + $rows; /* total number of events */
    }

    $out = build_owner_header($env, $row, $umin, $umax, $num, $rprt_name, $sites);
    $subject = $out[0];
    $message = $out[1];

    /* tmpfile_h holds the current report, almost nearly complete at this point.
           we create a new tmpfile, and append $message to the begining, followed
           by the contents of tmpfile_h. Then return a handle to the new file.  */
    if (!$row['tmpfile_h'] = append_tmp_file($tmpfile_h, $message)) return -1;

    $file   = $row['file'];
    $debug  = $env['debug'];
    $single = $env['single'];
    $days   = $env['days'];

    $good = false;

    if ($file) {
        $type = 'Event Report';
        $good = write_file($env, $row, $type, $days, $num, $sites);
    } else {
        if ($html) {
            /* Add on the footer to the report */
            if (!my_write($row['tmpfile_h'], $env['foot'])) return -1;
        }
        $good = report_mail($row, $email, $subject, $row['tmpfile_h'], $origin, $imgs, $server, $sites);
        debug_note("c-report: report_mail good:($good)");
    }

    fclose($tmpfile_h);
    fclose($row['tmpfile_h']);

    if (pfTimeDrep) {
        $mem = memstat($pid);
        logs::log(__FILE__, __LINE__, "timing: rp1 $pid $id endgen $mem", 0);
    }

    if ($good) {
        $num = 1;
    } else {
        echo '<p>Mail was *NOT* sent.</p>';
    }

    if ($debug) {
        if (!$html) {
            $message = nl2br($message);
            $message = "\n<pre>\n$message\n</pre>\n\n";
        }
        $len = strlen($message);
        if (($len > 400000) || ($single)) {
            $message = "omitting $len bytes ... <br>\n";
        }
        echo $message;
    }
    return $num;
}


/*
    |  Construct an array containing all the searches
    |  for a single report.
    */

function build_queries($row, $db)
{
    $list = array();
    $text = trim($row['search_list']);
    $qids = explode(',', $text);
    $temp = array();
    if ($qids) {
        reset($qids);
        foreach ($qids as $xxx => $qid) {
            if ($qid > 0) {
                $temp[] = $qid;
            }
        }
        if ($temp) {
            $text = join(',', $temp);
            $sql  = "select * from SavedSearches\n where id in ($text)";
            $temp = find_many($sql, $db);
            reset($temp);
            foreach ($temp as $xxx => $data) {
                $id = $data['id'];
                $list[$id] = $data;
            }
        }
    }
    return $list;
}


function calc_umin($umax, $cycle)
{
    // daily report
    if ($cycle == 0)
        return yesterday($umax);

    // weekly report, sundays is zero
    if ($cycle == 1)
        return yesterweek($umax);

    // monthly report
    if ($cycle == 2)
        return yestermonth($umax);

    // weekdays ... sunday is 0, so weekdays are 1..5
    if ($cycle == 3) {
        $date = getdate($umax);
        $wday = $date['wday'];
        $days = ($wday == 1) ? 3 : 1;
        return days_ago($umax, $days);
    }
}


/*
    |  process one single report for one single user.
    */

function report_once(&$env, $row, &$list)
{
    $usec   = microtime();
    $sdb    = $env['sdb'];
    $mdb    = $env['mdb'];
    $now    = $env['now'];
    $pid    = $env['pid'];
    $single = $env['single'];

    if ($env['suicide']) return -1;

    $id       = $row['id'];
    $owner    = $row['owner'];  // whether this user owns the report
    $name     = $row['name'];
    $file     = $row['file'];
    $cycle    = $row['cycle'];
    $email    = $row['emaillist'];
    $defmail  = $row['defmail'];
    $username = $row['username'];
    $last     = $row['last_run'];

    $umin     = @$row['umin'];   // only for immediate reports
    $umax     = @$row['umax'];   // only for immediate reports

    if ($single) {
        $stat = "p:$pid,r:$id,u:$username";
        $text = "start ($stat) -- $name";
        serious_message($text);
    }

    $def   = @$env['userdata'][$username]['report_mail'];
    debug_note($def, $username);
    $email = email_list($email, $defmail, $def);
    $num   = 0;

    if (($email) || ($file)) {
        if ($cycle <= 3) {
            $umax = last_cycle($row, $now);
            $umin = calc_umin($umax, $cycle);
        }

        // daily report
        if ($cycle == 0) {
            $num = genreport($env, $row, $email, $umin, $umax, $list);
        }

        // weekly report, sundays is zero
        if ($cycle == 1) {
            $num = genreport($env, $row, $email, $umin, $umax, $list);
        }

        // monthly report
        if ($cycle == 2) {
            $num = genreport($env, $row, $email, $umin, $umax, $list);
        }

        // weekdays ... sunday is 0, so weekdays are 1..5
        if ($cycle == 3) {
            $num = genreport($env, $row, $email, $umin, $umax, $list);
        }

        // immediate
        if ($cycle == 4) {
            $num = genreport($env, $row, $email, $umin, $umax, $list);
        }
    } else {
        if ($owner) {
            //         disable_report($mdb,$id);
        }
        $stat = "p:$pid,r:$id,u:$username";
        $text = "no email ($stat)";
        serious_message($text);
    }

    if ($single) {
        $stat = "p:$pid,r:$id,u:$username";
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "finish ($stat) in $secs";
        serious_message($text);
    }

    return $num;
}


/*
    |  Global reports get run once for their owner,
    |  and then once again for every other user
    |  who does not have an override.
    |
    |  One single report for 1 to N users.
    */


function report(&$env, $row, &$users)
{
    $aaaa   = microtime();
    $sdb    = $env['sdb'];
    $mdb    = $env['mdb'];
    $pid    = $env['pid'];
    $id     = $row['id'];
    $user   = $row['username'];
    $name   = $row['name'];
    $skip   = $row['skip_owner'];
    $global = $row['global'];

    /* gentext needs to know which table to select from */
    $row['global_report'] = $global;

    $done = -1;
    $flag = 0; /* TempEvents */
    $good = build_temp_table($env, $row, $flag, $sdb);


    // If temp_Census exists remove it
    drop_coreCensus_temptable($sdb);

    // Create the temp_Census table and copy
    // the existing Census data into it
    create_coreCensus(constCensusTemp, $sdb);

    if (!$good) {
        serious_message("pid:$pid, cannot create temporary table");
        return -1;
    }

    $single = $env['single'];
    if (claim_report($id, $mdb)) {
        $done = 0;
        // Run for owner
        $list = build_queries($row, $mdb);

        /* if its a global report, we create the SelectedEvents
               tables, that we will insert into, and select from */
        if ($global) {
            /* Create SelectedEvents table */
            $flag = 1;
            $good = build_temp_table($env, $row, $flag, $sdb);
            if (!$good) {
                serious_message("pid:$pid, cannot create temporary table");
                return -1;
            }
            /* Populate SelectedEvents table */
            insert_selectedevents($env, $list, $row, $sdb);
            if ($env['suicide']) return -1;
        }
        if ((!$skip) || (!$global)) {
            $row['owner'] = 1;
            $tmpuser = $row['username'];
            head_get_user_logo($tmpuser, $env, $mdb);
            foot_get_user_footer($tmpuser, $env, $mdb);
            //logs::log(__FILE__, __LINE__, "Report_Once - Local for: $tmpuser",0);                			
            $done = report_once($env, $row, $list);
            if ($done < 0) {
                drop_temp_table(0, $sdb);
                if ($flag)
                    drop_tmp_table(1, $sdb);

                return $done;
            }
        }
        // Run for other users
        $cycle = $row['cycle'];

        if (($global) && ($users)) {
            $user = $row['username'];
            $qn   = safe_addslashes($row['name']);

            reset($users);
            foreach ($users as $key => $data) {
                // not for the real owner,
                // we did him already.
                if ($data != $user) {
                    $qu  = safe_addslashes($data);
                    $sql = "select * from Reports\n"
                        . " where username = '$qu'\n"
                        . " and name = '$qn'";
                    $res = find_many($sql, $mdb);
                    if (!$res) {
                        // note override still applies even
                        // if matching report is disabled
                        // or even (forbidden) global.

                        $row['username'] = $data;
                        $row['owner']    = 0;
                        $row['global']   = 0;
                        /* Using the username get the footer and logo */
                        $tmpuser = $row['username'];
                        head_get_user_logo($tmpuser, $env, $mdb);
                        foot_get_user_footer($tmpuser, $env, $mdb);
                        //logs::log(__FILE__, __LINE__, "Report_Once - Global for: $tmpuser",0);                    	    			
                        $num = report_once($env, $row, $list);
                        if ($num < 0) return $num;
                        $done += $num;
                    }
                }
            }
        }

        drop_temp_table(0, $sdb);
        drop_coreCensus_temptable($sdb);

        if ($flag) {
            drop_temp_table(1, $sdb);
        }
        if (update_report($env, $row, $mdb) <= 0) {
            return -1;
        }
        if (($cycle == 4) && (!$single)) {
            delete_report($mdb, $id);
        }
    }

    $bbbb = microtime();
    $secs = microtime_diff($aaaa, $bbbb);
    $time = microtime_show($secs);
    $stat = "p:$pid,r:$id,u:$user";
    $text = "report: done ($stat) in $time, $name";
    if (60 <= $secs) {
        logs::log(__FILE__, __LINE__, $text, 0);
    }
    debug_note($text);

    return $done;
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
    $sql = "update Reports set\n"
        . " next_run = $when -- $dw\n"
        . " where id = $id";
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    $next = date('m/d/y H:i', $next);
    $prev = date('m/d/y H:i', $prev);
    $when = date('m/d/y H:i', $when);
    $last = date('m/d/y H:i', $last);
    debug_note("time: $when, last:$last, next cycle: $next, previous cycle: $prev");
    return $num;
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
    |
    |  note that update_stuck won't bother the report
    |  we just disabled.
    */

function update_stuck(&$row, $now, $db)
{
    $id   = $row['id'];
    $name = $row['name'];
    $user = $row['username'];
    $when = $now - 1;
    $dw  = date('m/d/y H:i:s', $when);
    $sql = "update Reports set\n"
        . " next_run = $when, -- $dw\n"
        . " this_run = 0\n"
        . " where id = $id\n"
        . " and next_run < 0\n"
        . " and enabled = 1";
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    $pid = getmypid();
    debug_note("unstick: id:$id when:$dw");
    logs::log(__FILE__, __LINE__, "report: unstick (p:$pid,r:$id,u:$user) $name", 0);
}


/*
    |  Notify the intended recepients of the report that
    |  the report has some sort of problem and has been
    |  disabled.
    */

function dead_report($row, $src, $def)
{
    $file = $row['file'];
    $name = $row['name'];
    $user = $row['username'];
    $mail = $row['emaillist'];
    $defm = $row['defmail'];
    $dst  = email_list($mail, $defm, $def);
    if ($dst) {
        $now = date('m/d H:i', time());
        $sub = "report: $name disabled $now.";
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


function kill_report(&$env, &$row, $db)
{
    $rid = $row['id'];
    $now = time();
    $sql = "update Reports set\n"
        . " enabled  = 0,\n"
        . " this_run = 0,\n"
        . " next_run = 0,\n"
        . " modified = $now\n"
        . " where id = $rid";
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    if ($num) {
        $name = $row['name'];
        $that = $row['this_run'];
        $last = $row['last_run'];
        $user = $row['username'];
        $secs = ($now > $that) ? $now - $that : 0;
        $that = date('m/d/y H:i:s', $that);
        $last = date('m/d/y H:i:s', $last);
        $now  = date('m/d/y H:i:s', $now);

        $def = $env['userdata'][$user]['report_mail'];
        $pid = getmypid();
        $srv = server_name($db);
        $srd = "reports@$srv";
        $dsd = 'support@handsfreenetworks.com';
        $src = server_def('event_report_sender', $srd, $db);
        $dst = server_def('support_email', $dsd, $db);
        $sub = "report problem ($rid) at $srv";
        $msg = "\n\nage:$secs, last:$last, now:$now, this:$that\n"
            . "user:$user, pid:$pid, rid:$rid, name:$name\n"
            . "stuck report.\n\n";
        $frm = "From: $src";
        $good = mail($dst, $sub, $msg, $frm);
        if (!$good) {
            $text = "report: mail failure ($pid)";
            logs::log(__FILE__, __LINE__, $text, 0);
        }
        echo "<p>sent mail to $dst</p>\n";
        dead_report($row, $src, $def);
    }
}


function find_next($now, $db)
{
    $ddd = date('m/d H:i', $now);
    $sql = "select * from Reports -- $ddd\n"
        . " where next_run between 1 and $now\n"
        . " and enabled = 1\n"
        . " order by next_run, global, cycle, id\n"
        . " limit 1";
    return find_one($sql, $db);
}


function build_env($pid, $now, $comp, $users, $mdb)
{
    $dbg    = get_integer('debug', 0);
    $single = get_integer('id', 0);
    $debug  = (($dbg > 0) || ($single > 0)) ? 1 : 0;

    // only link to asset detail page if asset db exists
    $db_names = find_database_names($mdb);
    $asset_exists = (in_array('asset', $db_names)) ? 1 : 0;
    $date = date('m/d H:i:s', $now);
    $sdb  = db_cron($mdb);
    if ($sdb) {
        $replicated = 1;
        $dbid = 'slave';
        debug_note("replicated database, pid:$pid, now:$date ($now)");
    } else {
        $replicated = 0;
        $sdb  = $mdb;
        $dbid = 'master';
        debug_note("normal database, pid:$pid, now:$date ($now)");
    }

    if ($replicated) {
        db_change($GLOBALS['PREFIX'] . 'event', $sdb);
    }

    /*
        |  We'll allow this particular script as much time as it needs,
        |  since some of the reports can take a very long time.  This
        |  effectively sets max_execution_time to infinite.
        |
        |  We also set the memory limit from the server setting.  The check
        |  for ini_set is left over for compatibility with php3.
        |
        |  Finally, the memory size can be set from the command line, so
        |  we actually use the maximum of the two.
        */

    $msize = get_integer('mem', 128);
    set_time_limit(0);
    ignore_user_abort(true);
    if (function_exists('ini_set')) {
        $msizeserv = server_def('max_php_mem_mb', '256', $mdb);
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

    $server = server_name($mdb);
    $host = server_href($mdb);
    $base = base_directory($host, $comp);
    $def  = "reports@$server";
    $ssl  = server_int('ssl', 1, $mdb);
    $def  = ($ssl) ? 443 : 80;
    $qdef = 95; // good jpeg quality
    $port = server_int('port', $def, $mdb);
    $jpgq = server_int('jpeg_quality', $qdef, $mdb);

    if (($jpgq < 0) || ($jpgq > 100)) {
        serious_message("invalid jpeg_quality ($jpgq), expecting 0..100");
        $jpgq = $qdef;
    }

    $env['sdb']    = $sdb;
    $env['mdb']    = $mdb;
    $env['now']    = $now;
    $env['pid']    = $pid;
    $env['fld']    = find_field_names('event', 'Events', $mdb);
    $env['evnt']   = describe_table('event', 'Events', $mdb);
    $env['jpgq']   = $jpgq;
    $env['href']   = "$base/index.php";
    $env['base']   = $base;
    $env['bias']   = server_int('cron_bias', 120, $mdb);
    $env['slow']   = (float) server_def('slow_query_report', 20, $mdb);
    $env['timo']   = server_int('report_timeout', 7200, $mdb);
    $env['dbid']   = $dbid;
    $env['days']   = server_int('file_expire_days', 120, $mdb);
    $env['retr']   = server_int('report_max_retries', 3, $mdb);
    $env['maxd']   = server_int('report_max_details', 80000, $mdb);
    $env['odir']   = $comp['odir'];
    $env['root']   = $comp['root'];
    $env['cron']   = 1;
    $env['event']  = 1;
    $env['debug']  = $debug;
    $env['single'] = $single;
    $env['active'] = find_active_sites($mdb);
    $env['enames'] = user_event_names();
    $env['origin'] = server_def('event_report_sender', $def, $mdb);
    $env['server'] = $server;
    $env['suicide'] = 0;
    $env['userdata']  = usertree($mdb);
    $env['siteaccesstree'] = siteaccesstree($users, $mdb);
    $env['asset_exists'] = $asset_exists;
    return $env;
}


/*
    |  Make "immediate" reports run immediately,
    |  even if their specified time interval is
    |  not yet complete.
    */

function future_report($now, $mdb)
{
    $ddd = date('m/d H:i', $now);
    $sql = "update Reports -- $ddd\n"
        . " set next_run = $now\n"
        . " where cycle = 4\n"
        . " and next_run = 0\n"
        . " and enabled = 1";
    redcommand($sql, $mdb);

    $sql = "select * from Reports\n"
        . " where next_run = 0\n"
        . " and cycle != 4\n"
        . " and enabled = 1";
    $set = find_many($sql, $mdb);
    if ($set) {
        $bias = server_int('cron_bias', 120, $mdb);
        reset($set);
        foreach ($set as $key => $row) {
            update_future($row, $now, $bias, $mdb);
        }
    }
}



/*
    |  The suicide code just exits without changing
    |  anything, assuming another cron has probably
    |  already come along and claimed the queue.
    |
    |  However, this morning JMK had a timeout of 2
    |  hours, and a query that ran for 2:00:24 ...
    |
    |  So the old cron just went away on its own
    |  before another one came along to steal the
    |  queue ... and left the report stuck.
    */


function cleanup_suicide(&$env, $db)
{
    $set = array();
    $now = time();
    $try = $env['retr'];
    if ($try > 0) {
        $sql = "select * from Reports\n"
            . " where next_run < 0\n"
            . " and enabled = 1\n"
            . " and retries > $try";
        $set = find_many($sql, $db);
        reset($set);
        foreach ($set as $key => $row) {
            kill_report($env, $row, $db);
            $pid  = $env['pid'];
            $rid  = $row['id'];
            $name = $row['name'];
            $user = $row['username'];
            $stat = "p:$pid,r:$rid,u:$user";
            $text = "report: killed ($stat) $name";
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
    }
    $set = find_stuck_report($db);
    reset($set);
    foreach ($set as $key => $row) {
        update_stuck($row, $now, $db);
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

function report_cron($pid, $now, $comp, $mdb)
{
    $aaaa   = microtime();
    $single = get_integer('id', 0);
    $users  = userlist($mdb);
    $env    = build_env($pid, $now, $comp, $users, $mdb);

    $more = true;
    $loop = 0;
    $num  = 0;

    db_change($GLOBALS['PREFIX'] . 'event', $mdb);
    cleanup_suicide($env, $mdb);
    while ($more) {
        $now = time();

        future_report($now, $mdb);

        /*
            |  This is just for debugging.
            |  It allows us to run just one single report,
            |  without bothering anything else.  It does not
            |  check to see if the report is enabled or not.
            */

        if ($single > 0) {
            $more = false;
            $sql  = "select * from Reports\n"
                . " where id = $single";
            $row  = find_one($sql, $mdb);
        } else {
            $row = find_next($now, $mdb);
        }

        if (!$row) {
            debug_note('nothing to be done');
            $more = false;
        }

        if (($row) && ($env)) {
            $env['now'] = $now;
        }

        if ($row) {
            $tmp = report($env, $row, $users);
            if ($tmp < 0) {
                // we've had a fatal error.
                cleanup_suicide($env, $db);
                $more = false;
            } else {
                $loop++;
                $num += $tmp;
            }
        }

        $elapsed = time() - $now;
        if ($elapsed > 0) {
            echo "$elapsed seconds so far ...<br>\n\n";
        }
    }

    if ($loop > 0) {
        $bbbb = microtime();
        $time = microtime_diff($aaaa, $bbbb);
        $secs = microtime_show($time);
        $msg  = "Processed $num reports; ($secs).";
        logs::log(__FILE__, __LINE__, "report: pid:$pid, $num completed; ($secs)", 0);
    } else {
        $now  = time();
        $date = datestring($now);
        $msg  = "No reports to do right now ... $date";
    }
    echo "<p>$msg</p>\n\n";
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
        $text = "report: queue ($stat) lock $acts";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }
    return $good;
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
                $text = "report: $name created";
            } else {
                $text = "report: could not create $name";
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
                $text = "report: $name created";
            } else {
                $text = "report: could not create $name";
            }
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
    }
    return $good;
}


/*
    |  We got the server lock to begin with, and we should still
    |  have it now. But if someone else has stolen it away, we
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


function report_cleanup($db)
{
    $users = userlist($db);
    $comp  = component_installed();
    $now   = time();
    $pid   = getmypid();
    $env   = build_env($pid, $now, $comp, $users, $db);
    cleanup_suicide($env, $db);
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
                report_cleanup($db);
                $age  = $now - $mod;
                $stat = "p:$pid,a:$age,t:$timo";
                $text = "report: stealing lock ($stat) from process $own";
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

if (pfTimeRept) {
    logs::log(__FILE__, __LINE__, "timing: rpt $pid start run", 0);
}

$mdb  = db_connect();
$name = "Cron Report ($pid)";
$comp = component_installed();

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($name, $comp, '', 0, 0, 0, $mdb);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$dbg   = get_integer('debug', 0);
$id    = get_integer('id', 0);
$debug = (($dbg > 0) || ($id > 0));

echo ident($now);

echo again();

if (report_lock($pid, $mdb)) {
    report_cron($pid, $now, $comp, $mdb);
    report_unlock($pid, $mdb);
} else {
    if (report_steal($pid, $mdb)) {
        opt_update(constReportPid, 0, 0, $mdb);
        opt_update(constReportLock, 0, 0, $mdb);
        echo "<p>Lock broken.</p>\n";
    } else {
        echo "<p>Could not aquire report lock</p>\n";
    }
}

$msec = microtime_diff($aaaa, microtime());
$secs = microtime_show($msec);
$text = "timing: rpt $pid end run total time $secs";

debug_note($text);
if (pfTimeRept) {
    logs::log(__FILE__, __LINE__, $text, 0);
}
echo again();

/* We don't care about user here so just use hfn */
$authusr = 'hfn';
echo head_standard_html_footer($authusr, $mdb);
