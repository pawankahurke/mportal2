<?php

/*
Revision history:

Date        Who     What
----        ---     ----
11-Sep-02   EWB     Merge with new asset code.
16-Sep-02   EWB     Only show query to debug users.
16-Sep-02   EWB     Renamed detail_asset to detail-asset.
19-Sep-02   EWB     Giant refactoring.
23-Sep-02   EWB     8.3 file names.
28-Sep-02   EWB     No more get_dataset_passer();
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navigation
13-Dec-02   EWB     Fixed short php tags
30-Dec-02   EWB     Single quotes for unevaluated string literals
 9-Jan-03   EWB     Don't require register_globals.
16-Jan-03   EWB     Access to $_SERVER variables
10-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change()
13-Feb-03   EWB     somewhat gratuitous date handling change.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
20-Mar-03   EWB     Filter for scrip 0 correctly.
 9-Apr-03   EWB     Allow direct access to timestamp (umin/umax).
14-Apr-03   NL      Comment out "if (trim($msg)) debug_note($msg)" cuz no $debug.
24-Apr-03   EWB     Do the sitefilter thing.
30-Apr-03   EWB     User site filter bit.
28-May-03   EWB     Small fixes to html generation and formatting.
12-Jun-03   EWB     Supports replicated database.
17-Jun-03   EWB     Start with slave database.
19-Jun-03   EWB     slow_query_event
15-Jul-03   EWB     Log Master/Slave for slow queries.
20-Nov-03   EWB     Report number of results returned from slow queries.
30-Nov-03   AAM     Added performance logging entries.
11-Mar-04   EWB     event.Events.deleted.
22-Mar-04   EWB     fixed bug 1997 (improper $rowsize default).
23-Mar-04   EWB     query from slave database.
29-Mar-04   EWB     show dates in query.
20-Jan-05   AAM     Changed max rowsize to 1000 to match change in event.php.
20-Jan-05   AAM     Changed max rowsize to 2000 as per Alex.
24-Jan-05   EWB     select by Events.id
18-Jul-05   BJS     removed priority & type columns, added clienttime.
31-Aug-05   BJS     added /lib/l-ebld.php
 1-Sep-05   BJS     Queries write to temp table, join on core.Census.
                    Only display results when core.Census.deleted = 0
                    instead of event.Events.deleted.
 6-Sep-05   BJS     added drop_TempEvents_table.
13-Oct-05   BJS     added HEX() when joining core & events to compare strings.
06-Jul-06   BTE     Bug 3515: Fix ad-hoc queries to no longer use *.
13-Sep-06   AAM     Added the sel_recent parameter to make it easier to get
                    a list of recent events for a particular system.
13-Sep-06   AAM     Added the sel_recent parameter to make it easier to get
                    a list of recent events for a particular system.
19-Sep-06   WOH     Bug 3657: Changed name html_footer.  Added username arg.
24-Nov-06   AAM     Bug 3865: implemented consistent use of stripslashes with
                    contents of event filters.
19-Feb-08   BTE     Bug 4416: Move the "last event log" timestamp into shared
                    memory.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-perf.php');
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-head.php');
include('../lib/l-user.php');
include('../lib/l-page.php');
include('../lib/l-slav.php');
include('../lib/l-ebld.php');
include('../lib/l-cnst.php');
include('../lib/l-errs.php');
include('../lib/l-core.php');
include('local.php');

function value_range($min, $max, $val)
{
    if ($val <= $min) $val = $min;
    if ($max <= $val) $val = $max;
    return $val;
}

function prevnext()
{
    global $page, $rowsize;
    $self  = server_var('PHP_SELF');
    $query = server_var('QUERY_STRING');

    $link = "$self?$query";
    $link = preg_replace('/rowstart=-*[0-9]*[&]*/', '', $link);
    $link = preg_replace('/rowsize=-*[0-9]*[&]*/',  '', $link);
    $link = preg_replace('/&$/', '', $link);
    echo <<< HERE

<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tr>

HERE;

    if ($page['previous']['exists']) {
        $prevstart = $page['previous']['start'];
        $prevlink  = "$link&rowstart=$prevstart&rowsize=$rowsize";
        echo <<< HERE

  <td align="left" valign="top">
    <a href="$prevlink">
       <img border="0" src="../pub/previous.gif" width="68" height="22">
    </a>
  </td>

HERE;
    } else {
        echo <<< HERE

  <td align="left" valign="top">
    <img border="0" src="../pub/previous-gray.gif" width="68" height="22">
  </td>

HERE;
    }
    echo <<< HERE

  <td valign="top">
    <p align="center">
  </td>

HERE;

    if ($page['next']['exists']) {
        $nextstart = $page['next']['start'];
        $nextlink = "$link&rowstart=$nextstart&rowsize=$rowsize";
        echo <<< HERE

  <td align="right" valign="top">
    <a href="$nextlink">
      <img border="0" src="../pub/next.gif" width="47" height="22">
    </a>
  </td>

HERE;
    } else {
        echo <<< HERE

  <td align="right" valign="top">
    <img border="0" src="../pub/next-gray.gif" width="47" height="22">
  </td>

HERE;
    }
    echo "\n</tr>\n</table>\n\n";
}

function report_error($sql, $db)
{
    $error = mysqli_error($db);
    $errno = mysqli_errno($db);
    $msg   = "       Query: " . nl2br($sql) . "\n";
    $msg  .= "Error Number: $errno\n";
    $msg  .= "  Error Text: $error\n";
    return $msg;
}

function querystring($db, $id)
{
    $data = '';
    $sql  = "select * from SavedSearches where id = $id";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            $row = mysqli_fetch_array($res);
            $data = stripslashes($row['searchstring']);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    } else {
        $msg = report_error($sql, $db);
        echo "<p><b>querystring:$msg</b><p>";
    }
    return $data;
}

function tablecol($data)
{
    echo <<< HERE

  <td>
    $data
  </td>

HERE;
}


/*
   | $tbl = table name
   | $db  = database handle
   */
function drop_TempEvents_table($tbl, $db)
{
    $sql = "drop table if exists $tbl";
    redcommand($sql, $db);
}

function memsize($pid)
{
    $mem = `ps --pid $pid --no-headers -o rss,vsz`;
    return "mem (res KB, tot KB) = $mem";
}

function detail($id)
{
    $href = "detail.php?eid=$id";
    echo <<< HERE

  <td>
    <a href="$href" target="_blank">
      <img src="../pub/detail.gif" width="33" height="14" border="0">
    </a>
  </td>

HERE;
}


/*
    |  Main program
    */

$now   = time();
$today = getdate($now);

$dmin_MON = get_integer('dmin_MON', $today['mon']);
$dmin_DAY = get_integer('dmin_DAY', $today['mday']);
$dmin_YR  = get_integer('dmin_YR',  $today['year']);
$dmin_HR  = get_integer('dmin_HR',  0);
$dmin_MIN = get_integer('dmin_MIN', 0);
$dmax_MON = get_integer('dmax_MON', $today['mon']);
$dmax_DAY = get_integer('dmax_DAY', $today['mday']);
$dmax_YR  = get_integer('dmax_YR',  $today['year']);
$dmax_HR  = get_integer('dmax_HR',  23);
$dmax_MIN = get_integer('dmax_MIN', 59);
$umin     = get_integer('umin',     0);
$umax     = get_integer('umax',     0);

$dbg      = get_integer('debug',    1);
$rowstart = get_integer('rowstart', 1);
$rowsize  = get_integer('rowsize',  50);

$sort     = get_string('sort', 'servertime');
$ord      = get_string('ord', 'asc');
$refresh  = get_string('refresh', 'never');

$sel_id          = get_string('sel_id', '');
$sel_text        = get_string('sel_text', '');
$sel_scrip       = strval(get_argument('sel_scrip',      1, ''));
$sel_machine     = strval(get_argument('sel_machine',    1, ''));
$sel_customer    = strval(get_argument('sel_customer',   1, ''));
$sel_executable  = strval(get_argument('sel_executable', 1, ''));
$sel_windowtitle = strval(get_argument('sel_windowtitle', 1, ''));

$sel_searchstring = get_argument('sel_searchstring', 1, array());

/* sel_recent is used to display recent events for a single machine.  This
        function is needed from several places so the option is added here. */
$sel_recent = get_integer('sel_recent', 0);

$title = 'Event Query Results';

$refreshtime = '';

$secs = 0;
if (($refresh != 'never') && ($refresh > 0)) {
    $secs = 60 * $refresh;
    $text = "<META HTTP-EQUIV=\"Pragma\" CONTENT=\"no-cache\">\n"
        . "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"$secs\">\n";
    $refreshtime = $text;
}

$mdb = db_connect();
$sdb = db_slave($mdb);
if ($sdb) {
    $db = $sdb;
    $dbid = 'slave';
} else {
    $db = $mdb;
    $dbid = 'master';
}

$authuser = process_login($mdb);
$comp = component_installed();
$user = user_data($authuser, $mdb);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $mdb);
//  echo $refreshtime;


$filter = @($user['filtersites']) ? 1 : 0;
$debug  = @($user['priv_debug']) ? $dbg : 0;
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($sdb)
    debug_note("replicated database, mdb:$mdb, sdb:$sdb");
else
    debug_note("normal database");

/* Handle the sel_recent parameter.  If it is present, then find out
        what we need in order to do the proper query and set those parameters
        up.  They are sel_machine, sel_customer, umin, and umax. */
if ($sel_recent > 0) {
    $sql = "select host, site, last from " . $GLOBALS['PREFIX'] . "core.Census"
        . " where id = $sel_recent";
    $res  = redcommand($sql, $db);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        $sel_machine = $row['host'];
        $sel_customer = $row['site'];
        $last = $row['last'];
        CORE_GetCachedTime($last, $row['site'], $row['host']);
        $umin = $last - 14400;
        $umax = $now + 14400;
        debug_note("sel_recent: $sel_recent sel_machine: $sel_machine" .
            " umin: $umin umax: $umax");
    }
}

if ($secs > 0) {
    $date = datestring($now);
    $len  = strlen($refreshtime);
    debug_note("refresh time:$secs, len:$len, now:$date");
}
$self  = server_var('PHP_SELF');
$query = server_var('QUERY_STRING');

$sortfields = array('customer', 'clienttime', 'servertime', 'machine', 'description', 'executable');
//  $sortfields = array ('customer', 'servertime', 'priority', 'machine', 'description', 'executable');

$srtclr['customer']    = 'white';
$srtclr['entered']     = 'white';
$srtclr['servertime']  = 'white';
$srtclr['machine']     = 'white';
$srtclr['description'] = 'white';
//  $srtclr['type']        = 'white';
$srtclr['executable']  = 'white';

$srtdir['customer']    = "asc";
$srtdir['entered']     = "acs";
$srtdir['servertime']  = "asc";
$srtdir['machine']     = "asc";
$srtdir['description'] = "asc";
//  $srtdir['type']        = "asc";
$srtdir['executable']  = "asc";

if ($ord == 'asc')
    $ord = 'desc';
else
    $ord = 'asc';

// setup
$srtclr[$sort] = '#99CCFF';
$srtdir[$sort] = $ord;

$access = find_sites($authuser, $filter, $mdb);
$slow   = (float) server_def('slow_query_event', 20, $mdb);

db_change($GLOBALS['PREFIX'] . 'event', $mdb);
if ($sdb) {
    db_change($GLOBALS['PREFIX'] . 'event', $sdb);
}

// always restrict by date
if (!$umin) {
    $umin = mktime($dmin_HR, $dmin_MIN, 0, $dmin_MON, $dmin_DAY, $dmin_YR);
    $smin = datestring($umin);
    debug_note("dmin: $dmin_MON/$dmin_DAY/$dmin_YR $dmin_HR:$dmin_MIN:00 --> $smin");
}
if (!$umax) {
    $umax = mktime($dmax_HR, $dmax_MIN, 0, $dmax_MON, $dmax_DAY, $dmax_YR);
    $smax = datestring($umax);
    debug_note("dmax: $dmax_MON/$dmax_DAY/$dmax_YR $dmax_HR:$dmax_MIN:00 --> $smax");
}

if ($umax < $umin) {
    $temp = $umax;
    $umax = $umin;
    $umin = $temp;
}
$list = ($access) ? $access : "''";
$smin = date('m/d H:i', $umin);
$smax = date('m/d H:i', $umax);

$temp_events = 'TempEvents';
drop_TempEvents_table($temp_events, $db);

build_events_table("CREATE TEMPORARY TABLE $temp_events", "BINARY", $db);

$sql = "insert ignore into $temp_events\n"
    . " (idx, entered, customer, machine, description,"
    . " executable, text1, servertime)\n"
    . " select idx, entered, customer, machine, description,"
    . " executable, text1, servertime from Events\n"
    . " where servertime between $umin and $umax\n";

/* if a customer is selected, there is no need to include the list
       of customers in the query. However, if no customer is selected
       we limit the query to all customers the user is allowed to access.
    */
if ($sel_customer) {
    $sql .= " and customer = '$sel_customer'\n";
} else {
    $sql .= " and customer in ($list)\n";
}
if ($sel_scrip != '') // '0' counts!
{
    $scp  = intval($sel_scrip);
    $sql .= " and scrip = $scp\n";
}
if (strlen($sel_machine)) {
    $sql .= " and machine = '$sel_machine'\n";
}
if ($sel_executable) {
    $sql .= " and executable like '%$sel_executable%'\n";
}
if ($sel_windowtitle) {
    $sql .= " and windowtitle like '%$sel_windowtitle%'\n";
}
if ($sel_id != '') {
    $id   = intval($sel_id);
    $sql .= " and id = $id\n";
}
if ($sel_text != '') {
    $qs  = safe_addslashes($sel_text);
    $txt = " and (\n"
        . "  (text1 like '%$qs%') or \n"
        . "  (text2 like '%$qs%') or \n"
        . "  (text3 like '%$qs%') or \n"
        . "  (text4 like '%$qs%') or \n"
        . "  (string1 like '%$qs%') or \n"
        . "  (string2 like '%$qs%')\n"
        . ")\n";
    $sql .= $txt;
}

// build the multiple select saved search string

$savedsearch = '';
if ($sel_searchstring) {
    $n = safe_count($sel_searchstring);
    for ($i = 0; $i < $n; $i++) {
        $item = querystring($mdb, $sel_searchstring[$i]);
        if (strlen($item)) {
            if ($i)
                $savedsearch .= " OR ($item)";
            else
                $savedsearch .= "($item)";
        }
    }
    if (strlen($savedsearch)) {
        if ($n > 1) {
            $savedsearch = "($savedsearch)";
        }
        $sql .= " and $savedsearch\n";
    }
}

// Execute the query to populate the TempEvents Table
redcommand($sql, $db);

// build the query to select from the temp table
$sql = "select * from $temp_events as TE\n"
    . " left join " . $GLOBALS['PREFIX'] . "core.Census as C\n"
    . " on (TE.customer = C.site\n"
    . " and TE.machine  = C.host)\n"
    . " where C.deleted = 0\n";

/* Since TempEvents is binary collation, any text fields must be sorted
        with the Latin1 character set */
$sort2 = $sort;
if (($sort == 'customer') || ($sort == 'machine') || ($sort == 'description')
    || ($sort == 'executable')
) {
    $sort2 = "CONVERT($sort USING latin1)";
}

// set up the sort order
$sql .= " order by $sort2 $ord";

?>
<table width="100%" border="0">
    <tr>
        <td align="left" valign="top">
            <?php

            // run the query

            if (pfTimeIntQ) {
                $ipAddr = $_SERVER['REMOTE_ADDR'];
                $pid = getmypid();
                $mem = memsize($pid);
                $txt = "timing: iev $ipAddr $authuser start $mem\n $sql";
                logs::log(__FILE__, __LINE__, $txt, 0);
            }
            $time = 0;
            $res  = redcommand_time($sql, $time, $db);
            if (pfTimeIntQ) {
                $mem = memsize($pid);
                $txt = "timing: iev $ipAddr $authuser finish $mem\n $sql";
                logs::log(__FILE__, __LINE__, $txt, 0);
            }
            $resultsize = ($res) ? mysqli_num_rows($res) : 0;
            if ($time > $slow) {
                $secs = microtime_show($time);
                $pid  = getmypid();
                $stat = "p:$pid,d:$dbid,e:$resultsize,u:$authuser";
                $text = "events: slow ($stat) in $secs\n$sql";
                logs::log(__FILE__, __LINE__, $text, 0);
                debug_note("slow query ($stat) in $secs");
            }

            if (!$res) {
                $msg = report_error($sql, $db);
                echo "<p><b>$msg</b></p><br>";
            }

            if ($resultsize <= 0) {
                echo "
            <b>No Results Found</b>
            <p></p>
            Try a <a href=\"event.php\">New Query</a>
            ";
                drop_TempEvents_table($temp_events, $db);
            } else {
                $rowsize = value_range(5, 2000, $rowsize);
                $page = paginate($rowstart, $rowsize, $resultsize);

                // If it is a saved search, insert the search string
                if (isset($savedsearch) && ($savedsearch)) {
                    echo "
                    <small>
                        Running saved search: $savedsearch
                    </small>
                <br>";
                }

                // Instructions
                echo "
            <p>
                Click on 'detail' button for complete information.<br>
                Click on column header to sort by column.<br>
                Or try a <a href=\"event.php\">New Query</a>
                or run another <a href=\"search.php\">Search</a>.
            </p>";

                $emin = $page['current']['start'];
                $emax = $page['current']['end'];

                //    debug_paginate($page);

                echo "
            <p>
               Events $emin through $emax (of $resultsize)
            </p>";

                // Previous and Next buttons
                prevnext();

                echo <<< HERE

<table border="1" bordercolor="#COCOCO" cellpadding="3" width="100%" cellspacing="0">
<tr>
  <th>
    <br>
  </th>

HERE;

                $link = "$self?$query";
                $link = preg_replace('/\bsort=[a-zA-Z]*[&]*/', '', $link);
                $link = preg_replace('/\bord=[a-zA-Z]*[&]*/',  '', $link);
                $link = preg_replace('/&$/', '', $link);

                $n = safe_count($sortfields);
                for ($i = 0; $i < $n; $i++) {
                    $sort = $sortfields[$i];
                    $text = strtolower($sort);
                    if ($sort == 'clienttime') {
                        /* 'entered' is the database field name.
                    Alex wants the column name displayed to the
                    user to be 'clienttime'. We can't use
                    clienttime in an sql query, so we change it
                    to 'entered' in the url.
                */
                        $sort = 'entered';
                    }
                    $color = $srtclr[$sort];
                    $dir   = $srtdir[$sort];
                    $href  = "$link&sort=$sort&ord=$dir";

                    echo <<< HERE

  <th bgcolor="$color">
    <a href="$href">$text</a>
  </th>

HERE;
                }

                echo <<< HERE

  <th>
    text
  </th>
</tr>

HERE;

                /*
        |  I'd much prefer to do a select limit $rowstart, $rowsize.
        |
        |  That works, but then mysqli_num_rows always returns
        |  $resultsize == $rowsize, so we can't paginate the
        |  result.  When we upgrade to mysql 4.0, we can use
        |  SQL_CALC_FOUND_ROWS and FOUND_ROWS();
        */

                $pmin = $page['current']['start'] - 1;
                $size = $page['current']['size'];
                mysqli_data_seek($res,  $pmin);

                $i = 0;
                while (($row = mysqli_fetch_array($res)) && ($i < $size)) {
                    echo "\n<tr>";
                    $time  = mysqltime($row['servertime']);
                    $ctime = mysqltime($row['entered']);
                    detail($row['idx'], '');
                    tablecol($row['customer']);
                    tablecol($ctime);
                    tablecol($time);
                    tablecol($row['machine']);
                    tablecol($row['description']);
                    //tablecol($row['type']);
                    tablecol($row['executable']);
                    tablecol($row['text1']);
                    echo '</tr>';
                    $i = $i + 1;
                }

                ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
                drop_TempEvents_table($temp_events, $db);

                echo "</table>\n\n";

                // Previous and Next buttons
                prevnext();

                echo <<< HERE

  <p align="center">
    <a href="#top">[top of page]</a>
  </p>

HERE;
            }

            echo <<< HERE

    </td>
  </tr>
</table>

HERE;

            echo head_standard_html_footer($authuser, $mdb);
            ?>