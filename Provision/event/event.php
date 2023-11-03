<?php

/*
Revision history:

Date        Who     What
----        ---     ----
11-Sep-02   EWB     Merge with new asset code.
19-Sep-02   EWB     Giant refactoring
20-Sep-02   EWB     8.3 library names
28-Sep-02   EWB     No more get_dataset_passer();
 7-Oct-02   EWB     OutputJavascriptAssetTree()
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navagation
12-Dec-02   EWB     Fixed short open tags.
10-Feb-03   EWB     Get the machine list from the census.
11-Feb-03   EWB     db_change()
13-Feb-03   EWB     somewhat gratuitous date handling changes.
13-Feb-03   EWB     Loads scrip names from the scrip cache.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
14-Apr-03   NL      Comment out "if (trim($msg)) debug_note($msg)" cuz no $debug.
16-Apr-03   NL      Relabel Customer->Site; sites ($carr) and machines are filtered.
16-Apr-03   NL      Oops. Move db_change('event',$db) below $host.
16-Apr-03   NL      Use find_customers after all.  It metaquotes the sites.
29-Apr-03   EWB     Clean up for access lists.
29-Apr-03   NL      Correct label:  Event Queries --> Query Filters
30-Apr-03   EWB     Filter Sites bit.
29-May-03   EWB     Display SQL
29-May-03   EWB     Quote Crusade.
29-May-03   EWB     Unique Machine Names.
17-Jun-03   EWB     Slave Database.
20-Jun-03   EWB     No Slave Database.
20-Jun-03   EWB     More reasonable default times.
18-Jan-05   AAM     Added more options to results per page.
20-Jan-05   AAM     Changed max query results to 2000 as per Alex.
24-Jan-05   EWB     Search by ID
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
17-Apr-07   RWM     Bug 4081 - Change default results per page for ad-hoc query

*/

$title   = 'Ad-hoc Event Query Form';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('local.php');
include('../lib/l-page.php');
include('../lib/l-dslt.php');
include('../lib/l-cens.php');
include('../lib/l-user.php');
//  include ( '../lib/l-slav.php'  );
include('../lib/l-cmth.php');
include('../lib/l-head.php');

function find_searches($authuser, $db)
{
    $list = array();
    $sql  = "select id, name from SavedSearches\n";
    $sql .= " where global = 1 or\n";
    $sql .= " username = '$authuser'\n";
    $sql .= " order by name, global";
    $res  = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            $prev = '';
            while ($row = mysqli_fetch_array($res)) {
                $name = $row['name'];
                $id   = $row['id'];
                if ($name != $prev) {
                    $list[$id] = $name;
                }
                $prev = $name;
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $list;
}


function select_scrip($db)
{
    $list = array();
    $sql = "select * from EventScrips\n order by scrip";
    $res = redcommand($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_array($res)) {
            $tmp = array();
            $tmp['scrip'] = $row['scrip'];
            $tmp['desc']  = $row['description'];
            $list[] = $tmp;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    if ($list) {
        $m  = "\n<select name=\"sel_scrip\">\n";
        $m .= "<option value=\"\">all</option>\n";
        foreach ($list as $key => $row) {
            $scrip = $row['scrip'];
            $desc  = $row['desc'];
            $m .= "<option value=\"$scrip\">$desc</option>\n";
        }
        $m .= "</select>\n";
    } else {
        $m = "<b>no scrips found.</b>";
    }
    return $m;
}


function saved_search($searches)
{
    if ($searches) {
        $msg = "<select name=\"sel_searchstring[]\" multiple size=\"5\">\n";
        foreach ($searches as $id => $name) {
            $msg .= "<option value=\"$id\">$name</option>\n";
        }
        $msg .= "</select>\n";
    } else {
        $msg = "<b>no event filters.</b>";
    }
    return $msg;
}


function simple_select($name, $options)
{
    $m = '';
    if ($options) {
        $m .= "<select name=\"$name\" size=\"1\">\n";
        $m .= "<option value=\"\">all</option>\n";
        reset($options);
        foreach ($options as $key => $data) {
            $m .= "<option>$data</option>\n";
        }
        $m .= "</select>\n";
    }
    return $m;
}




/*
    |  Main program
    */

$now = time();
$db  = db_connect();

/************************
    $mdb = db_connect();
    $sdb = db_slave($mdb);
    if ($sdb)
    {
        db_change($GLOBALS['PREFIX'].'core',$sdb);
        $db = $sdb;
    }
    else
    {
        $db = $mdb;
    }
 ************************/

$authuser = process_login($db);
$comp = component_installed();
$user = user_data($authuser, $db);
$filter = @($user['filtersites']) ? 1 : 0;
$debug  = @($user['priv_debug']) ?  1 : 0;

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

/************************
    if ($sdb)
        debug_note("replicated database, mdb:$mdb, sdb:$sdb");
    else
        debug_note("normal database");
 ************************/

$carr   = site_array($authuser, $filter, $db);  // $carr is filtered site array
$access = db_access($carr);                  // $access is filtered metaquoted site list

$date = datestring($now);
debug_note("$date ($now)");
if (safe_count($carr) == 0) {
    echo "<p>No sites found for user <b>$authuser</b>.</p>";
} else {
    $host = census_machine($access, $db);

    db_change($GLOBALS['PREFIX'] . 'event', $db);
    $searches = find_searches($authuser, $db);
    $search_select = saved_search($searches);
    $scrip_select  = select_scrip($db);
    $select_host   = simple_select("sel_machine", $host);
    $select_site   = simple_select("sel_customer", $carr);

    echo <<< HERE

<form method="get" action="pager.php">

    <table cellpadding="3">
    <tr>
        <td valign="top" nowrap>
            <b>Query Filters:</b><br>
            <small>
            <i>
                To deselect, hold down<br>'ctrl'and click again.<br>
                (Mac: command key)
            </i>
            </small>
        </td>
        <td width="100%">
            <table>
            <tr>
                <td>
                    $search_select
                </td>
                <td valign="bottom">
                    <a href="search.php">[Edit a  Query Filter]</a><br>
                    <a href="srch-add.php">[Add a Query Filter]</a>
                    <p>
                        <input type="submit" value="Search" name="Search">
                    </p>
                </td>
            </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>Select Additional Search Criteria for Events:</b>
        </td>
    </tr>
    <tr>
        <td>
            Site:
        </td>
        <td>
            $select_site
        </td>
    </tr>
    <tr>
        <td>
            Date/Time from:
        </td>
        <td>

HERE;

    /*
        |  Try to set a somewhat reasonable default times
        |  Be careful of daylight savings time.
        */

    $tday = getdate($now);
    $hour = $tday['hours'];
    if ($hour <= 8) {
        // early yesterday morning to tonight
        $umin = midnight($now - (10 * 3600));
        $umax = midnight($now + (26 * 3600)) - 1;
    }
    if ((9 <= $hour) && ($hour <= 21)) {
        // this morning to tonight
        $umin = midnight($now);
        $umax = midnight($now + (24 * 3600)) - 1;
    }
    if (22 <= $hour) {
        // this morning to tomorrow night
        $umin = midnight($now);
        $umax = midnight($now + (28 * 3600)) - 1;
    }

    $dmin = getdate($umin);
    $dmax = getdate($umax);

    date_select('dmin', $dmin['mday'], $dmin['mon'], $dmin['year'], $dmin['hours'], $dmin['minutes']);
    echo <<< HERE

        </td>
    </tr>
    <tr>
        <td>
            Date/Time to:
        </td>
        <td>

HERE;

    date_select('dmax', $dmax['mday'], $dmax['mon'], $dmax['year'], $dmax['hours'], $dmax['minutes']);
    echo <<< HERE

        </td>
    </tr>
    <tr>
        <td>
            Machine:
        </td>
        <td>
            $select_host
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <b>Narrow Your Search (optional):</b>
        </td>
    </tr>
    <tr>
        <td>
            Scrip number:
        </td>
        <td>
            $scrip_select
        </td>
    </tr>
    <tr>
        <td>
            Executable:
        </td>
        <td>
            <input type="text" name="sel_executable" size="20">
        </td>
    </tr>
    <tr>
        <td>
            Window Title:
        </td>
        <td>
            <input type="text" name="sel_windowtitle" size="20">
        </td>
    </tr>
    <tr>
        <td>Text:</td>
        <td>
            <input type="text" name="sel_text" size="20">
        </td>
    </tr>
    <tr>
        <td>ID:</td>
        <td>
            <input type="text" name="sel_id" size="20">
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>Select Display Options:</b>
        </td>
    </tr>
    <tr>
        <td>Number of Results per Page:</td>
        <td>
            <select name="rowsize">
                <option>25</option>
                <option>50</option>
                <option>75</option>
                <option>100</option>
                <option>150</option>
                <option>200</option>
                <option selected="1">250</option>
                <option>500</option>
                <option>1000</option>
                <option>2000</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>
            Refresh Page Every (in minutes):
        </td>
        <td>
            <select name="refresh">
                    <option selected>never</option>
                    <option>1</option>
                    <option>5</option>
                    <option>10</option>
                    <option>15</option>
                    <option>20</option>
                    <option>25</option>
                    <option>30</option>
            </select>
        </td>
    </tr>
    <tr>
        <td>
            <br>
        </td>
        <td>
            <input type="submit" value="Search" name="Search">
        </td>
    </tr>
    </table>
</form>



HERE;
}
debug_note("$date ($now)");
echo head_standard_html_footer($authuser, $db);
