<?php
/*
Revision history:

Date        Who     What
----        ---     ----
17-Oct-03   EWB     Creation
31-Oct-03   EWB     Display/Sort by Creation Date
23-Mar-04   EWB     Command to show root (parent = 0) items.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

// mysqldump -u weblog asset DataName > dataname.sql

$title = 'Asset Names';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('local.php');
include('../lib/l-user.php');
include('../lib/l-rcmd.php');
include('../lib/l-jump.php');
include('../lib/l-head.php');


function table_data($args)
{
    if ($args) {
        echo "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo "<td>$data</td>\n";
        }
        echo "</tr>\n";
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
    $a[] = html_link('debug.php', 'home');
    $a[] = html_link($href, 'again');
    $a[] = html_link("$o=0", 'id');
    $a[] = html_link("$o=1", 'name');
    $a[] = html_link("$o=2", 'parent');
    $a[] = html_link("$o=3", 'client');
    $a[] = html_link("$o=4", 'server');
    $a[] = html_link("$o=5", 'group');
    $a[] = html_link("$o=6", 'single');
    $a[] = html_link("$o=7", 'leaders');
    $a[] = html_link("$o=8", 'error');
    $a[] = html_link("$o=9", 'create');
    $a[] = html_link("$o=10", 'root');
    return jumplist($a);
}

function order($ord)
{
    switch ($ord) {
        case  1:
            return "select * from DataName\n"
                . " order by name, dataid";
        case  2:
            return "select * from DataName\n"
                . " order by parent, ordinal, dataid";
        case  3:
            return "select * from DataName\n"
                . " where setbyclient = 1\n"
                . " order by groups, name, dataid";
        case  4:
            return "select * from DataName\n"
                . " where setbyclient = 0\n"
                . " order by parent, ordinal, dataid";
        case  5:
            return "select * from DataName\n"
                . " where (groups != 0)\n"
                . " order by groups, ordinal, dataid";
        case  6:
            return "select * from DataName\n"
                . " where setbyclient = 1\n"
                . " and groups = 0\n"
                . " order by name, dataid";
        case  7:
            return "select * from DataName\n"
                . " where leader = 1\n"
                . " order by name, dataid";
        case  8:
            return "select * from DataName\n"
                . " where (groups != 0)\n"
                . " and (groups != parent)\n"
                . " order by name, dataid";
        case  9:
            return "select * from DataName\n"
                . " order by created, dataid";
        case 10:
            return "select * from DataName\n"
                . " where parent = 0\n"
                . " order by ordinal, dataid";
        default:
            return "select * from DataName\n"
                . " order by dataid";
    }
}


function draw_table($sql, $db)
{
    $res = redcommand($sql, $db);
    if ($res) {
        $num = mysqli_num_rows($res);
        if ($num > 0) {
            $msg = "Table contains $num entries.";
            $msg = fontspeak($msg);
            echo "$msg<br><br>\n\n\n";
            $head = explode('|', 'did|name|gid|pid|ord|ldr|inc|set|create');

            echo "<table bgcolor=\"wheat\" border=\"2\"
                      align=\"left\" cellspaceing=\"2\" cellpadding=\"2\">\n";
            table_data($head);
            while ($row = mysqli_fetch_array($res)) {
                $name = $row['name'];
                $did  = $row['dataid'];
                $pid  = $row['parent'];
                $ord  = $row['ordinal'];
                $gid  = $row['groups'];
                $crt  = $row['created'];
                $ldr  = $row['leader'];
                $inc  = $row['include'];
                $set  = $row['setbyclient'];

                $crt  = date('m/d/y', $crt);
                $list = array($did, $name, $gid, $pid, $ord, $ldr, $inc, $set, $crt);

                table_data($list);
            }
            echo "</table>";
            echo "<br clear=\"all\">\n\n\n";
        } else {
            $msg = "No results found.";
            $msg = fontspeak($msg);
            echo "$msg<br><br>\n\n\n";
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
}

/*
    |  Main program
    */

$now = time();
$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);


$dbg = get_integer('debug', 1);
$ord = get_integer('ord', 0);

$user = user_data($authuser, $db);

$priv_debug = @($user['priv_debug']) ? 1 : 0;

$debug = ($priv_debug) ? $dbg : 0;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

db_change($GLOBALS['PREFIX'] . 'asset', $db);

if ($priv_debug) {
    echo again();
    $sql = order($ord);
    draw_table($sql, $db);
    echo again();
} else {
    $msg = "You need debug access to use the page.";
    $msg = "<p>$msg</p><br>\n";
    echo $msg;
}

echo head_standard_html_footer($authuser, $db);
