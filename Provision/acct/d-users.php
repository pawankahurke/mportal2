<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
15-Aug-02   EWB     Added priv_debug
15-Aug-02   EWB     Always log mysql failures
20-Sep-02   EWB     Giant refactoring
 5-Dec-02   EWB     Reorginization Day
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header() 
19-Mar-03   NL      Move $debug initialization and surrounding lines above ob_ lines
11-Sep-03   EWB     Show account filter attributes
 8-Oct-03   EWB     Show which sites are are enabled for display.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
 
*/

$title  = "List of Users";

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)  
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-head.php');
include('../lib/l-user.php');
include('../lib/l-jump.php');
include('../lib/l-rcmd.php');


function customers($username, $db)
{
    $tmp  = '';
    $qu   = safe_addslashes($username);
    $sql  = "select * from Customers\n";
    $sql .= " where username = '$qu'\n";
    $sql .= " order by sitefilter desc,customer";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $site = $row['customer'];
                $filt = $row['sitefilter'];
                $enab = ($filt) ? 'e' : 'd';
                $tmp .= "($enab)$site<br>";
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $tmp;
}

function table_header($rows)
{
    echo "<table border='2' align='left' cellspacing='2' cellpadding='2'>\n";
    if (safe_count($rows)) {
        echo "<tr>\n";
        reset($rows);
        foreach ($rows as $key => $data) {
            $s = fontspeak($data);
            echo "<th>$s</th>\n";
        }
        echo "</tr>\n";
    }
}

function table_data($rows)
{
    if (safe_count($rows)) {
        echo "<tr valign=\"top\">\n";
        reset($rows);
        foreach ($rows as $key => $data) {
            $s = fontspeak($data);
            echo "<td>$s</td>\n";
        }
        echo "</tr>\n";
    }
}

function newline($n)
{
    echo "<br clear='all'>";
    if ($n > 0) {
        for ($i = 0; $i < $n; $i++) {
            echo "<br>\n";
        }
    }
}

function display_users($ord, $db)
{
    switch ($ord) {
        case  0:
            $order = 'username asc';
            break; // name
        case  1:
            $order = 'username desc';
            break;
        case  2:
            $order = 'userid asc';
            break;          // owner
        case  3:
            $order = 'userid desc';
            break;
        default:
            $order = 'username';
            break;
    }

    $sql = "select * from Users order by $order";
    $res = redcommand($sql, $db);

    if ($res) {
        $count = mysqli_num_rows($res);
    }
    if ($count) {
        newline(3);
        $header = array('Name', 'Id', 'Priv', 'Notify', 'Report', 'Sites', 'Filter');
        table_header($header);
        while ($row = mysqli_fetch_array($res)) {
            $userid    = $row['userid'];
            $username  = $row['username'];
            $notify    = $row['notify_mail'];
            $report    = $row['report_mail'];

            $ad        = $row['priv_admin'];
            $dg        = $row['priv_debug'];
            $cf        = $row['priv_config'];
            $up        = $row['priv_updates'];
            $dl        = $row['priv_downloads'];

            $gs        = $row['priv_search'];
            $gn        = $row['priv_notify'];
            $gr        = $row['priv_report'];
            $gq        = $row['priv_aquery'];
            $ga        = $row['priv_areport'];
            $filter    = ($row['filtersites']) ? 'Yes' : 'No';

            $report    = str_replace(',', '<br>', $report);
            $notify    = str_replace(',', '<br>', $notify);
            $customer  = customers($username, $db);
            if (empty($customer)) $customer = '<br>';
            if (empty($notify))   $notify   = '<br>';
            if (empty($report))   $report   = '<br>';

            $priv = '';
            if ($ad) $priv .= 'admin<br>';
            if ($dg) $priv .= 'debug<br>';
            if ($cf) $priv .= 'config<br>';
            if ($up) $priv .= 'update<br>';
            if ($dl) $priv .= 'dload<br>';
            if ($gs) $priv .= 'search<br>';
            if ($gn) $priv .= 'notify<br>';
            if ($gr) $priv .= 'report<br>';
            if ($gq) $priv .= 'query<br>';
            if ($ga) $priv .= 'asset<br>';
            $priv .= '<br>';
            $args = array($username, $userid, $priv, $notify, $report, $customer, $filter);
            table_data($args);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        echo "</table>\n";
        echo "<br clear='all'><br>\n";
        $msg = fontspeak("$count accounts total.");
        echo "<br><p>$msg</p>";
    }
}


function again()
{
    $self = server_var('PHP_SELF');
    $args = server_var('QUERY_STRING');
    $href = ($args) ? "$self?$args" : $self;
    $o    = "$self?ord";

    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link('index.php', 'home');
    $a[] = html_link($href, 'again');
    $a[] = html_link("$o=0", 'name');
    $a[] = html_link("$o=2", 'id');
    $a[] = html_link("$o=1", 'name*');
    $a[] = html_link("$o=3", 'id*');
    return jumplist($a);
}



/*
    |  Main program
    */

$db         = db_connect();
$authuser   = process_login($db);
$comp       = component_installed();

$ord    = intval(get_argument('ord', 0, 0));
$priv   = intval(get_argument('priv', 0, 1));
$dbg    = intval(get_argument('debug', 0, 1));
$user   = user_data($authuser, $db);
$admin  = @($user['priv_admin']) ? $priv : 0;
$debug  = @($user['priv_debug']) ? $dbg  : 0;

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer) 
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$now   = time();
$date  = datestring($now);
$count = 0;

echo "<h2>$date</h2>";

if ($admin) {
    echo again();
    display_users($ord, $db);
    echo again();
} else {
    $msg  = 'You need administrative access in order to use this';
    $msg .= ' page.  Authorization denied.';
    $msg  = "<p>$msg</p>";
    echo fontspeak($msg);
}

echo head_standard_html_footer($authuser, $db);
