<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 5-Sep-03   EWB     Created.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer() 
 
*/

$title = 'Updates Sites Census';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-jump.php');
include('../lib/l-user.php');
include('header.php');
include('../lib/l-head.php');


function again($debug)
{
    $self = server_var('PHP_SELF');
    $args = server_var('QUERY_STRING');
    $href = ($args) ? "$self?$args" : $self;
    $act  = "$self?ord";
    $a   = array();
    if ($debug) {
        $a[] = html_link('../acct/index.php', 'home');
    }
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link($href, 'again');
    $a[] = html_link("$act=1", 'version');
    $a[] = html_link("$act=2", 'id');
    $a[] = html_link("$act=3", 'site');
    $a[] = html_link('census.php', 'machine');
    $a[] = html_link('dnload.php', 'downloads');

    return jumplist($a);
}


function table_data($args, $head)
{
    $td = ($head) ? 'th' : 'td';
    if (safe_count($args)) {
        echo "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo "<$td>$data</$td>\n";
        }
        echo "</tr>\n";
    }
}


function table_header()
{
    echo "<br>\n<table border='2' align='left' cellspacing='2' cellpadding='2'>\n";
}


function table_footer()
{
    echo "</table>\n<br clear='all'>\n<br>\n";
}

function display_census($db, $ord)
{
    $order = 'sitename, id';
    switch ($ord) {
        case  1:
            $order = 'version, id';
            break;
        case  2:
            $order = 'id';
            break;
        case  3:
            $order = 'sitename desc, id';
            break;
        case  4:
            $order = 'id desc';
            break;
        default:
            break;
    }
    $num = 0;
    $sql = "select * from UpdateSites\n order by $order";
    $res = redcommand($sql, $db);
    if ($res) {
        $num  = mysqli_num_rows($res);
        if ($num > 0) {
            $txt = 'id,site,version';
            table_header();
            $args  = explode(',', $txt);
            table_data($args, 1);
            while ($row = mysqli_fetch_array($res)) {
                $id    = $row['id'];
                $site  = $row['sitename'];
                $vers  = $row['version'];
                if (!$site) $site = '<br>';
                if (!$vers) $vers = '<br>';
                $args  = array($id, $site, $vers);
                table_data($args, 0);
            }
            table_footer();
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    echo "<h2>$num sites found</h2>";
}


/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$ord = get_integer('ord', 0);
$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, $local_nav, '', 0, $db);


$date = datestring(time());

echo "<h2>$date</h2>";

$user  = user_data($authuser, $db);
$debug = @($user['priv_debug']) ? 1 : 0;
$admin = @($user['priv_admin']) ? 1 : 0;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($admin) {
    db_change($GLOBALS['PREFIX'] . 'swupdate', $db);
    echo again($debug);
    display_census($db, $ord);
    echo again($debug);
    db_change($GLOBALS['PREFIX'] . 'core', $db);
} else {
    $msg = "This page requires administrative access.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
}

echo head_standard_html_footer($authuser, $db);
