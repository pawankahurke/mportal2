<?php

/*
  Revision history:

  Date        Who     What
  ----        ---     ----
  16-Oct-19   SVG      Creation.

 */

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-cust.php');
include('../lib/l-head.php');
include('header.php');
include('../lib/l-errs.php');
include('../lib/l-cnst.php');

function newlines($n)
{
    for ($i = 0; $i < $n; $i++) {
        echo "<br>\n";
    }
}

function bold($msg)
{
    return "<b>$msg</b>";
}

// this is gross .. makes the entire contents into
// a table.  oh well.

function silly_table()
{
    return '<table width="100%" border="0"><tr><td>';
}

function mark($name)
{
    echo "<a name='$name'></a>\n";
}

function marklink($link, $text)
{
    return "<a href='$link'>$text</a>\n";
}

function brace()
{
    return "&nbsp;|&nbsp;";
}

/*
  |  Note that the mark for "top" is now included in header.inc.
  |  The mark for "bottom" is included in footer.inc.
 */

function jumptable($tags)
{
    $args = explode(',', $tags);
    $n = safe_count($args);
    if ($n > 0) {
        $msg = '';
        for ($i = 0; $i < $n; $i++) {
            $name = $args[$i];
            $link = marklink("#$name", $name);
            if ($i)
                $msg .= brace();
            $msg .= $link;
        }
        //$msg .= brace();
        //$msg .= "<a href='custdata.php?action=add'>add customer</a>";
        echo "<p>[ $msg ]</p>";
    }
}

function table_header()
{
    echo "\n<table border='2' align='left' cellspacing='2' cellpadding='2'>\n";
}

function table_data($args, $head, $align = 'left')
{
    $td = ($head) ? "th" : "td";
    if (safe_count($args)) {
        echo "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            $s = fontspeak($data);
            echo "<$td align=$align>$s</$td>\n";
        }
        echo "</tr>\n";
    }
}

function span_data($n, $msg, $xtra = '')
{
    $msg = "<tr><td colspan='$n' $xtra>$msg</td></tr>\n";
    return $msg;
}

function table_footer()
{
    echo "\n</table>\n";
    echo "<br clear='all'>\n";
}

function get_all_customer_data($db)
{
    $usersdata = array();
    $sql = "SELECT * FROM Customers order by customer_name";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $id = $row['cid'];
                $usersdata[$id]['customer_name'] = $row['customer_name'];
                $usersdata[$id]['tenant_id'] = ($row['tenant_id']) ? $row['tenant_id'] : '';
                $usersdata[$id]['sku_list'] = ($row['sku_list']) ? $row['sku_list'] : '';
                $created_time = '-';
                $last_update = '-';
                if ($row['created_time'] !== '0') {
                    $created_time = gmdate("Y-m-d H:i:s", $row['created_time']);
                }
                if ($row['last_update'] !== '0') {
                    $last_update = gmdate("Y-m-d H:i:s", $row['last_update']);
                }
                $usersdata[$id]['created_time'] = $created_time;
                $usersdata[$id]['last_update'] = $last_update;
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $usersdata;
}

function show_customer($id, $row, $db)
{
    $name = $row['customer_name'];
    $tenant_id = get_UserName($row['tenant_id'], $db);
    $sku_list = get_SkuNames($row['sku_list'], $db);
    $created_time = $row['created_time'];
    $last_update = $row['last_update'];

    $args = array($name, $tenant_id, $sku_list, $created_time, $last_update);
    $edit = "<a href='custdata.php?id=$id&action=edit'>[edit]</a>";
    $del = "<a href='cust-act.php?id=$id&action=delete'>[delete]</a>";
    $exp    = "<a href='cust-act.php?id=$id&action=expCust'>[export customer]</a>";
    //$args[] = "$edit<br>$del<br>$exp";

    table_data($args, 0);
}

/*
  |  Main program
 */

$db = db_connect();
db_change($GLOBALS['PREFIX'] . 'install', $db);

$authuser = install_login($db);
$authuserdata = install_user($authuser, $db);
$priv_admin = @($authuserdata['priv_admin']) ? 1 : 0;
$priv_servers = @($authuserdata['priv_servers']) ? 1 : 0;

if (!$priv_admin)
    header("Location: custdata.php");

$comp = component_installed();

$title = 'Customer';

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo install_html_header($title, $comp, $authuser, $priv_admin, $priv_servers, $db);
if (trim($msg))
    debug_note($msg);   // ...display any errors to debug Customer

newlines(2);

echo silly_table();

mark('users');
jumptable('top,bottom,customers');
table_header();
$args[] = 'Customer Name';
$args[] = 'User';
$args[] = 'Sku List';
$args[] = 'Created Time';
$args[] = 'Last Update Time';
//$args[] = 'Action';

table_data($args, 1);

$usersdata = get_all_customer_data($db);
if (safe_count($usersdata)) {
    reset($usersdata);
    foreach ($usersdata as $key => $data) {
        show_customer($key, $data, $db);
    }
} else {
    echo span_data(safe_count($args), "<br>No customers currently exist.<br><br>");
}

table_footer();

newlines(1);
jumptable('top,bottom,customers');

table_footer();

/* Hardwired to pass in hfn for the user. */
$user = 'hfn';
echo head_standard_html_footer($user, $db);
