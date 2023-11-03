<?php

/*
Revision history:

Date        Who     What
----        ---     ----
14-Apr-03   NL      Initial creation.
14-Apr-03   NL      Add quotes around true and false (if $check)
15-Apr-03   NL      Close checkbox HTML tag.
15-Apr-03   NL      Add explanatory text.
15-Apr-03   NL      Oops. Correct explanatory text.
15-Apr-03   NL      Check strlen $sitesON & $siteOFF before running update_sitefilter() queries.
15-Apr-03   NL      $sites & $sitesCHECKED should be metaquoted
15-Apr-03   NL      Change HTML tag for $sites & sitesCHECKED to be double-quoted.
24-Apr-03   EWB     Library jumptable.
25-Apr-03   EWB     Show sql to debug users.
25-Apr-03   NL      Move many site filter functions to l-sitefl.php
25-Apr-03   NL      Rename l-sitefl.php to l-sitflt.php
29-Apr-03   NL      show_checkboxlist renamed show_sitefilterlist
                    site filter radio button, update_admin_filtersites
30-Apr-03   NL      Rename get_admin_filtersetting --> get_user_filtersetting;
                    Rename get_admin_sitefilter --> get_user_sitefilter;
                    Rename update_admin_filtersetting --> update_user_filtersetting;
                    Rename update_admin_sitefilter --> update_user_sitefilter;
 5-May-03   NL      Dont pass $filtersites to show_sitefilterlist.
 5-May-03   NL      Pass columns to show_sitefilterlist
 5-May-03   NL      Oops. Move newlines below radio button.
21-Aug-03   EWB     Added message for Russell.
 8-Oct-03   EWB     more readable debug messages.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
 
*/

$title = 'Site Filter';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-rcmd.php');
include('../lib/l-serv.php');
include('../lib/l-jump.php');
include('../lib/l-user.php');
include('../lib/l-head.php');
include('../lib/l-sitflt.php');


function bigbluetext($msg)
{
    return "<font face='verdana,helvetica' size='3' color='#333399'>$msg</font>\n";
}

function newlines($n)
{
    for ($i = 0; $i < $n; $i++) {
        echo "<br>\n";
    }
}

function table_data($args, $head)
{
    $td = ($head) ? "th" : "td";
    if (safe_count($args)) {
        echo "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo "<$td>$data</$td>\n";
        }
        echo "</tr>\n";
    }
}

function span_data($n, $msg)
{
    $msg = "<tr><td colspan='$n'>$msg</td></tr>\n";
    return $msg;
}

function table_header()
{
    echo "\n<table border='0' align='left' cellspacing='2' cellpadding='2'>\n";
}

function table_footer()
{
    echo "\n</table>\n";
    echo "<br clear='all'>\n";
}


function PHP_CheckUncheckAll($all_setting, $authuser, $db)
{
    if ($all_setting == 'check')
        $filter = 1;
    elseif ($all_setting == 'uncheck')
        $filter = 0;

    $sql = "UPDATE Customers SET\n sitefilter = $filter\n" .
        " WHERE username = '$authuser'";
    $res = redcommand($sql, $db);
}


function update_user_filtersetting($authuser, $db)
{
    $filtersites   = get_argument('filtersites', 0, 0);

    $sql = "UPDATE Users SET\n" .
        " filtersites = $filtersites\n" .
        " WHERE username = '$authuser'";
    $res  = redcommand($sql, $db);
}

function update_user_sitefilter($authuser, $db)
{
    $sitesCHECKED   = get_argument('sitesCHECKED', 1, array());

    // turn filter off for ALL sites
    $sql = "UPDATE Customers SET\n sitefilter = 0\n" .
        " WHERE username = '$authuser'";
    $res  = redcommand($sql, $db);

    // turn filter on for CHECKED sites
    if (safe_count($sitesCHECKED)) {
        // single quote each sitename
        reset($sitesCHECKED);
        foreach ($sitesCHECKED as $key => $data)
            $sitesCHECKED[$key] = "'" . $data . "'";
        // convert quoted sitenames to string
        $sitesON = implode(",", $sitesCHECKED);
        // update db
        if (strlen($sitesON)) {
            $sql = "UPDATE Customers SET\n sitefilter = 1\n " .
                " WHERE username = '$authuser' AND\n customer IN ($sitesON)";
            $res  = redcommand($sql, $db);
        }
    }
}

/*
    |  Main program
    */

$now = time();
$db  = db_connect();
$authuser = process_login($db);
$comp   = component_installed();
$user   = user_data($authuser, $db);
$dbg    = get_argument('debug', 0, 1);
$priv   = @($user['priv_debug']) ? 1 : 0;
$filter = @($user['filtersites']) ? 1 : 0;
$debug  = ($priv) ? $dbg : 0;
$date   = datestring($now);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$all_setting = get_argument('all_setting', 0, '');
$submit      = get_argument('submit', 0, '');

// if JS turned off, use PHP to check/uncheck all
if (strlen($all_setting)) {
    PHP_CheckUncheckAll($all_setting, $authuser, $db);
}

// update site filter
if (strlen($submit)) {
    update_user_sitefilter($authuser, $db);
    update_user_filtersetting($authuser, $db);
}

// it might have changed
$filtersites = get_user_filtersetting($authuser, $db);
$sitefilter  = get_user_sitefilter($authuser, $db);
debug_note("now: $date");
if (safe_count($sitefilter)) {
    echo mark('sites');
    echo jumptable('top,bottom,sites');
    echo "<form name='myForm' method='post' action='sitefltr.php'>\n";
    echo "<br><input type='submit' name='submit' value='Update Site Filter'>\n";
    echo "Site Filter: ";
    show_sitefilter_radio($filtersites, $db);
    newlines(2);
    echo "Display events for site:<br>\n\n";
    show_sitefilterlist('', $sitefilter, $all_setting, 2, $db);
    echo "<br><input type='submit' name='submit' value='Update Site Filter'>\n";
    echo "</form>\n";
    newlines(1);
    echo jumptable('top,bottom,sites');
} else {
    $msg = "There are currently no sites accessible by user <b>$authuser</b>.";
    $msg = bigbluetext($msg);
    echo "<p>$msg</p>";
}
debug_note("now: $date");
echo head_standard_html_footer($authuser, $db);
