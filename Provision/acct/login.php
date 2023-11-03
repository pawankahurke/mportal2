<?php

/*
Revision history:

Date        Who     What
----        ---     ----
19-Mar-03   NL      Initial creation, based on acct/admin.php.
19-Mar-03   NL      Move $debug initialization and surrounding lines above ob_ lines
19-Mar-03   NL      Include l-rcmd.php
11-Apr-03   EWB     Uses library jumptable.
11-Apr-03   EWB     Resolved some quoting issues.
24-Apr-03   EWB     echo jumptable.
14-Dec-04   EWB     give priv users link to debug home.
21-Jun-06   BTE     Bug 3466: The primary census server page is no longer in
                    order.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.                                        

*/

$title = 'Login';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-rcmd.php');
include('../lib/l-serv.php');
include('../lib/l-jump.php');
include('../lib/l-tabs.php');
include('../lib/l-head.php');


function bigbluetext($msg)
{
    return "<font face='verdana,helvetica' size='3' color='#333399'>$msg</font>\n";
}

function find_cust($db, $username)
{
    $cust = array();
    $user = safe_addslashes($username);
    $sql  = "SELECT * FROM Customers where username = '$user'";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $cust[] = $row['customer'];
            }
            sort($cust);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $cust;
}


function again($debug)
{
    $ax   = array();
    $ax[] = html_link('#top', 'top');
    $ax[] = html_link('#bottom', 'bottom');
    $ax[] = html_link('#sites', 'sites');
    if ($debug) {
        $self = server_var('PHP_SELF');
        $ax[] = html_link($self, 'again');
        $ax[] = html_link('index.php', 'home');
    }
    return jumplist($ax);
}

function special_header($msg, $span)
{
    $msg = "<font color='white'>$msg</font>";
    $msg = fontspeak($msg);
    $msg = "<tr><th colspan='$span' bgcolor='#333399'>$msg</th></tr>\n";
    return $msg;
}


function find_all_customers($db)
{
    $cust = array();
    $sql  = "SELECT distinct customer FROM Customers order by "
        . "CONVERT(customer USING latin1)";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $cust[] = $row['customer'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $cust;
}

function cust_list($name, $db)
{
    $msg = '';
    $cust = find_cust($db, $name);
    foreach ($cust as $key => $data) {
        $msg .= "$data<br>";
    }
    if (empty($msg)) $msg = "(none)";
    return $msg;
}

function cust_cache($db, $site)
{
    $name = safe_addslashes($site);
    $cnt  = 0;
    $sql  = "select * from Census where";
    $sql .= " site = '$name'";
    $res  = command($sql, $db);
    if ($res) {
        $cnt = mysqli_num_rows($res);
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    $cached = ($cnt) ? 'Yes' : 'No';
    return $cached;
}


function show_cust($set, $db)
{
    if ($set) {
        $args = array('Site Name', 'Active');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "Sites &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $site) {
            $acts = cust_cache($db, $site);
            $args = array($site, $acts);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}


function find_user($user, $db)
{
    $row = array();
    $qu  = safe_addslashes($user);
    $sql = "select * from Users where username = '$qu'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_array($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $row;
}





/*
    |  Main program
    */

$db   = db_connect();
$auth = process_login($db);
$comp = component_installed();

$user  = find_user($auth, $db);
if ($user) {
    $admin = ($user['priv_admin']) ? 1 : 0;
    $debug = ($user['priv_debug']) ? 1 : 0;
} else {
    $admin = 0;
    $debug = 0;
}

$nopriv = get_integer('nopriv', 0);
if ($nopriv) $admin = 0;
$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $auth, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users


if ($admin)
    $cust = find_all_customers($db);
else
    $cust = find_cust($db, $auth);

$msg = "You are logged in as user <b>$auth</b>.<br><br>";
$msg = bigbluetext($msg);
echo "<p>$msg</p>";
if (safe_count($cust)) {
    $msg = "Sites accessible by user <b>$auth</b>:";
    $msg = bigbluetext($msg);
    echo "<p>$msg</p>";
    echo mark('sites');
    echo again($debug);
    show_cust($cust, $db);
    echo again($debug);
} else {
    $msg = "There are currently no sites accessible by user <b>$auth</b>.";
    $msg = bigbluetext($msg);
    echo "<p>$msg</p>";
}
echo head_standard_html_footer($auth, $db);
