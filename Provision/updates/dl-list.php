<?php

/*
Revision history:

Date        Who     What
----        ---     ----
06-Nov-02   NL      create file
22-Nov-02   NL      filter sitename to authuser access
22-Nov-02   NL      alter_priv (required to Add, Edit or Delete)
22-Nov-02   NL      move redcommand to common.php
22-Nov-02   NL      remove fontspeak
22-Nov-02   NL      change display() arguments to a cols array
22-Nov-02   NL      mask($password)
 3-Dec-02   NL      change title
 3-Dec-02   NL      add &nbsp; to empty $url
 5-Dec-02   NL      "There are no versions listed" else block was in wrong place
16-Dec-02   EWB     Fixed short open tag
31-Dec-02   EWB     Single quote for strings non-evaluated strings.
31-Dec-02   EWB     deglobalized.
10-Feb-03   EWB     new database.
11-Feb-03   EWB     Removed common.php
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
12-Mar-03   EWB     changed database name to 'swupdate'.
25-Apr-03   EWB     Site filters.
29-Apr-03   EWB     l-cust not needed.
30-Apr-03   EWB     user filter sites.
30-Apr-03   NL      Fix $filter and $priv_downloads (1:0 not 0:1)
29-Mar-04   EWB     Allow user to set/view cmdline value.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title   = 'Available Versions';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('header.php');
include('../lib/l-head.php');
include('../lib/l-jump.php');

function again($priv, $dpriv)
{
    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    if ($priv) {
        $href = "dl-alter.php?action=add";
        $a[] = html_link($href, 'add');
    }
    $a[] = html_link('#versions', 'versions');
    if ($dpriv) {
        $self = server_var('PHP_SELF');
        $args = server_var('QUERY_STRING');
        $href = ($args) ? "$self?$args" : $self;
        $home = '../acct/index.php';
        $a[] = html_link('dnload.php', 'debug');
        $a[] = html_link($href, 'again');
        $a[] = html_link($home, 'home');
    }
    return jumplist($a);
}

function table_data($rows, $head)
{
    $td = ($head) ? 'th' : 'td';
    if ($rows) {
        echo "<tr>\n";
        reset($rows);
        foreach ($rows as $key => $data) {
            echo "<$td>$data</$td>\n";
        }
        echo "</tr>\n";
    }
}


function table_header($args)
{
    echo "<table border='2' align='left' cellspacing='2' cellpadding='2' width='100%'>\n";
    table_data($args, 1);
}


function table_end($db)
{
?>
    </table>
    <br clear="all">
    <br>
<?php
}

function mask($password)
{
    $password = preg_replace('/./', '*', $password);
    return $password;
}

function disp($row, $name)
{
    $text = $row[$name];
    return ($text == '') ? '<br>' : $text;
}

/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();
$user = user_data($authuser, $db);
$dbg  = get_integer('debug', 1);
$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, $local_nav, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$priv      = @($user['priv_downloads']) ? 1 : 0;
$dpriv     = @($user['priv_debug']) ?     1 : 0;
$filter    = @($user['filtersites']) ?    1 : 0;
$debug     = ($dpriv) ? $dbg : 0;


$site_list = find_sites($authuser, $filter, $db);
db_change($GLOBALS['PREFIX'] . 'swupdate', $db);

echo mark('versions');
echo again($priv, $dpriv);


// Get download files from database

$qu  = safe_addslashes($authuser);
$sql = "select * from Downloads\n"
    . " where global = 1\n"
    . " or owner in ('$qu','')\n"
    . " order by name, version";
$res = redcommand($sql, $db);

if ($res && mysqli_num_rows($res)) {
    $head = array();
    if ($priv)  $head[]  = 'Action';
    $head[] = 'Name';
    $head[] = 'Version';
    $head[] = 'Global';
    $head[] = 'Download URL';
    $head[] = 'Username';
    $head[] = 'Password';
    //  $head[] = 'Filename';
    //  $head[] = 'Target';
    $head[] = 'Command Line';

    table_header($head);

    while ($row = mysqli_fetch_array($res)) {
        $id       = $row['id'];
        //  $revl     = $row['revision'];
        $global   = $row['global'];
        $owner    = $row['owner'];
        $name     = disp($row, 'name');
        $version  = disp($row, 'version');
        //  $sitename = disp($row,'sitename');
        $url      = disp($row, 'url');
        $username = disp($row, 'username');
        //  $filename = disp($row,'filename');
        //  $target   = disp($row,'target');
        $cmdline  = disp($row, 'cmdline');
        $pass     = $row['password'];
        $password = ($pass == '') ? '<br>' : mask($pass);

        $args = array();
        if ($priv) {
            $acts = '<br>';
            $eref = "dl-alter.php?id=$id&action=edit";
            $cref = "dl-alter.php?id=$id&action=copy";
            $dref = "dl-act.php?id=$id&action=conf";
            $copy = html_link($cref, '[copy]');
            if (($owner == $authuser) || ($owner == '')) {
                $edit = html_link($eref, '[edit]');
                $del  = html_link($dref, '[delete]');
                $acts = "$edit<br>$del<br>$copy";
            } else {
                $acts = $copy;
            }
            $args[] = $acts;
        }

        $args[] = $name;
        $args[] = $version;
        $args[] = ($global) ? 'Yes' : 'No';
        $args[] = $url;
        $args[] = $username;
        $args[] = $password;
        //  $args[] = $filename;
        //  $args[] = $target;
        $args[] = $cmdline;
        table_data($args, 0);
    }
    table_end($db);
} else {
    echo "There are no versions available for user <b>$authuser</b>.";
}

echo again($priv, $dpriv);

echo head_standard_html_footer($authuser, $db);
?>