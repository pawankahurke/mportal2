<?php

/*
Revision history:

Date        Who     What
----        ---     ----
15-Aug-02   EWB     Added priv_debug
15-Aug-02   EWB     Always log mysql failures
19-Sep-02   EWB     Giant refactoring.
 5-Nov-02   EWB     New Asset Privs priv_aquery, priv_areport
19-Nov-02   EWB     Global Owner command.
25-Nov-02   EWB     Config Machine priv.
 5-Dec-02   EWB     priv_downloads, priv_updates
 6-Dec-02   EWB     priv_debug, global user command vanishes
14-Jan-03   EWB     Don't require register_globals.
 7-Feb-03   EWB     3.1 Database
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
 8-Mar-03   NL      Added Change Password row under Update User
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
17-Mar-03   NL      Allow change password only if $admin or 'user_password' server option is on.
19-Mar-03   NL      Move $debug initialization and surrounding lines above ob_ lines
19-Mar-03   NL      Include l-rcmd.php
 8-Apr-03   EWB     More quote issues.
29-Apr-03   EWB     Added priv_asset.
23-Apr-03   EWB     Quote Crusade.
23-Jul-03   EWB     Show Restricted User
24-Jul-03   EWB     Show debug if debug.
31-Jul-03   EWB     Fixup jumptable.
 8-Oct-03   EWB     Alphabetize users list of sites.
29-Sep-05   BJS     Added assign anchor.
03-Oct-05   BJS     Added site email, action is first column.
09-Jan-06   BTE     Added auditing and CSRV rights.
21-Jun-06   BTE     Bug 3466: The primary census server page is no longer in
                    order.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title = 'Admin';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-rcmd.php');
include('../lib/l-serv.php');
include('../lib/l-jump.php');
include('../lib/l-head.php');
include('../lib/l-gsql.php');


function bigbluetext($msg)
{
    return "<font face=\"verdana,helvetica\" size=\"3\" color=\"#333399\">$msg</font>\n";
}


/*
    |  Note that the mark for "top" is now included in header.inc.
    |  The mark for "bottom" is included in footer.inc.
    */

function jumparound($tags, $admin, $debug)
{
    $a = array();
    if ($debug) {
        $a[] = html_link('index.php', 'home');
    }
    jumptags($a, $tags);
    if ($admin) {
        $act = "adm-act.php?action";
        $a[] = html_link("$act=ccu", 'new user');
        $a[] = html_link("$act=ccc", 'new site');
    }
    return jumplist($a);
}

function newlines($n)
{
    for ($i = 0; $i < $n; $i++) {
        echo "<br>\n";
    }
}


function find_cust($db, $username)
{
    $cust = array();
    $qu   = safe_addslashes($username);
    $sql  = "select customer from Customers\n";
    $sql .= " where username = '$qu'\n";
    $sql .= " order by CONVERT(customer USING latin1)";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_assoc($res)) {
                $cust[] = $row['customer'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $cust;
}


function customer_id($site, $db)
{
    $id   = 0;
    $qst  = safe_addslashes($site);
    $sql  = "select id from Customers where customer = '$qst'";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            $row = mysqli_fetch_assoc($res);
            $id  = $row['id'];
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $id;
}


function special_header($msg, $span)
{
    return <<< HERE

<tr>
  <th colspan="$span" bgcolor="#333399">
    <font color="white">
       $msg
    </font>
  </th>
</tr>

HERE;
}


function span_data($n, $msg)
{
    return <<< HERE

<tr>
   <td colspan="$n">
       $msg
   </td>
</tr>

HERE;
}

function find_all_customers($db)
{
    $cust = array();
    $sql  = "select distinct customer from Customers order by CONVERT("
        . "customer USING latin1)";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_assoc($res)) {
                $cust[] = $row['customer'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $cust;
}

function table_data($args, $head)
{
    $td = ($head) ? "th" : "td";
    if (safe_count($args)) {
        echo "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo " <$td>$data</$td>\n";
        }
        echo "</tr>\n";
    }
}

function mail_list($list)
{
    if (empty($list))
        $result = "<br>";
    else
        $result = str_replace(",", "<br>", $list);
    return $result;
}

function cust_list($name, $db)
{
    $msg  = '';
    $cust = find_cust($db, $name);
    $msg  = join("<br>\n", $cust);
    if ($msg == '') $msg = "(none)";
    return $msg;
}


function priv_list($row, $debug)
{
    $a = array();
    if ($row['priv_admin'])     $a[] = 'Administrate';
    if ($row['priv_search'])    $a[] = 'Global&nbsp;Event&nbsp;Searches';
    if ($row['priv_notify'])    $a[] = 'Global&nbsp;Notifications';
    if ($row['priv_report'])    $a[] = 'Global&nbsp;Event&nbsp;Reports';
    if ($row['priv_aquery'])    $a[] = 'Global&nbsp;Asset&nbsp;Queries';
    if ($row['priv_areport'])   $a[] = 'Global&nbsp;Asset&nbsp;Reports';
    if ($row['priv_config'])    $a[] = 'Configure&nbsp;Machines';
    if ($row['priv_provis'])    $a[] = 'Control&nbsp;Provision';
    if ($row['priv_asset'])     $a[] = 'Remove&nbsp;Assets';
    if ($row['priv_downloads']) $a[] = 'Control&nbsp;Downloads';
    if ($row['priv_updates'])   $a[] = 'Control&nbsp;Updates';
    if ($row['priv_restrict'])  $a[] = 'Restricted';
    if ($row['priv_audit'])     $a[] = 'Use&nbsp;Audits';
    if ($row['priv_csrv'])      $a[] = 'CSRV&nbsp;Right';
    if (($debug) && ($row['priv_debug']))
        $a[] = 'Debug';
    return ($a) ? join("<br>\n", $a) : '(none)';
}

function table_header()
{
    echo "\n\n\n";
    echo '<table border="2" align="left" cellspacing="2" cellpadding="2">';
    echo "\n";
}

function table_footer()
{
    echo "\n</table>\n\n\n";
    echo "<br clear=\"all\">\n\n";
}


function cust_cache($db, $site)
{
    $cnt  = 0;
    $qsite = safe_addslashes($site);
    $sql  = "select * from Census where";
    $sql .= " site = '$qsite'";
    $res  = command($sql, $db);
    if ($res) {
        $cnt = mysqli_num_rows($res);
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    $cached = ($cnt) ? 'Yes' : 'No';
    return $cached;
}


function site_owner($site, $db)
{
    $user = '';
    $qs   = safe_addslashes($site);
    $sql  = "select username\n";
    $sql .= " from Customers\n";
    $sql .= " where owner = 1 and\n";
    $sql .= " customer = '$qs'";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row  = mysqli_fetch_assoc($res);
            $user = $row['username'];
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    $owner = ($user == '') ? '<br>' : $user;
    return $owner;
}



function show_cust($cust, $authuser, $admin, $db)
{
    $args = array();
    if ($admin) {
        $args[] = 'Action';
        $args[] = 'Owner';
    }
    $args[] = 'Site Name';
    $args[] = 'Site Email';
    $args[] = 'Active';
    table_header();
    table_data($args, 1);
    if (safe_count($cust)) {
        reset($cust);
        foreach ($cust as $key => $site) {
            $site_email = find_site_email($site, $db);
            $args       = array();
            if ($admin) {
                $id     = customer_id($site, $db);
                $act    = "adm-act.php?id=$id&action";
                $delete = html_link("$act=cdc", '[delete]');
                $edit   = html_link("$act=sso", '[edit]');
                $args[] = "$delete <br> $edit";
                $args[] = site_owner($site, $db);
            }
            $args[] = $site;
            $args[] = ($site_email) ? $site_email : '<br>';
            $args[] = cust_cache($db, $site);
            table_data($args, 0);
        }
    }
    table_footer();
}


function find_user($username, $db)
{
    $row = array();
    $sql = "select * from Users where username = '$username'";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $row;
}


function find_users($db)
{
    $usr = array();
    $sql = "select * from Users order by CONVERT(username USING latin1)";
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_assoc($res)) {
                $usr[] = $row;
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $usr;
}



function show_user($row, $admin, $debug, $db)
{
    $id     = $row['userid'];
    $name   = $row['username'];
    $notify = $row['notify_mail'];
    $report = $row['report_mail'];

    $plist  = priv_list($row, $debug);
    $nlist  = mail_list($notify);
    $rlist  = mail_list($report);
    $clist  = cust_list($name, $db);

    if ($admin) {
        $act    = "adm-act.php?id=$id&action";
        $edit   = html_link("$act=eu", '[edit]');
        $del    = html_link("$act=cdu", '[delete]');
        $own    = html_link("$act=gu", '[global]');
        //      $args[] = "$edit<br>\n$del<br>\n$own";
        $args[] = "$edit<br>\n$del";
    }

    $args[] = $name;
    $args[] = $clist;
    $args[] = $plist;
    $args[] = $nlist;
    $args[] = $rlist;

    table_data($args, 0);
}


/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$now   = time();
$date  = datestring($now);
$user  = find_user($authuser, $db);

$apriv = ($user['priv_admin']) ? 1 : 0;
$dpriv = ($user['priv_debug']) ? 1 : 0;
$dbg   = intval(get_argument('debug', 0, 0));
$adm   = intval(get_argument('admin', 0, 1));
$admin = ($apriv) ? $adm : 0;
$debug = ($dpriv) ? $dbg : 0;

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$users = array();

if (safe_count($user))
    $users = array($user);
if ($admin)
    $users = find_users($db);

if (safe_count($users)) {
    newlines(2);
    echo mark('users');
    echo jumparound('top,bottom,users,sites,update', $admin, $dpriv);
    if (!$admin) {
        $msg = "$authuser user profile";
        $msg = bigbluetext($msg);
        echo "<p>$msg</p>";
    }
    table_header();
    if ($admin) $args[] = 'Action';
    $args[] = 'Name';
    $args[] = 'Sites';
    $args[] = 'Privileges';
    $args[] = 'Notifications Default Email Recipients';
    $args[] = 'Reports Default Email Recipients';

    table_data($args, 1);
    if (safe_count($users)) {
        reset($users);
        foreach ($users as $key => $data) {
            show_user($data, $admin, $debug, $db);
        }
    }
    table_footer();
} else {
    echo mark('users');
}


if ($admin)
    $cust = find_all_customers($db);
else
    $cust = find_cust($db, $authuser);

echo "<a name=\"#edit\">";
if (safe_count($cust)) {
    echo mark('sites');
    echo jumparound('top,bottom,users,sites,update', $admin, $dpriv);
    if (!$admin) {
        $msg = "Sites accessible by user $authuser";
        $msg = bigbluetext($msg);
        echo "<p>$msg</p>\n\n";
    }
    show_cust($cust, $authuser, $admin, $db);
}

if (safe_count($user)) {
    newlines(2);
    echo mark('update');
    echo jumparound('top,bottom,users,sites,update', $admin, $dpriv);

    echo "\n\n\n";
    echo "<form method=\"post\" action=\"adm-act.php\">\n";
    echo "<input type=\"hidden\" name=\"action\" value=\"uu\">\n";

    $args = array();
    table_header();

    echo special_header('Update User', 3);

    if ($admin || intval(server_opt('user_password', $db))) {
        $args[0] = 'Change Password: ';
        /*  I was going to simply put these 3 form fields in one row if
                I could have added the NOWRAP attribute to the TD tag, as follows:
                <td nowrap>Old: <input type=\"password\" name=\"old_pwd\" value=\"\" size=\"25\">
                            New: <input type=\"password\" name=\"new_pwd\" value=\"\" size=\"25\">
                            Confirm: <input type=\"password\" name=\"confirm_pwd\" value=\"\" size=\"25\">
                </td>
                But since I can't, I'll display then vertically
                (using an inner table so they line up nicely)

                Also, I will add <span class='footnote'> because I cant do
                <td class='footnote'>
             */
        $args[1] = "<table>";
        $args[1] .= "<tr><td>Old Password:</td>\n";
        $args[1] .= "<td><input type=\"password\" name=\"old_pwd\" value=\"\" size=\"25\"></td></tr>\n";
        $args[1] .= "<tr><td>New Password:</td>\n";
        $args[1] .= "<td><input type=\"password\" name=\"new_pwd\" value=\"\" size=\"25\"></td></tr>\n";
        $args[1] .= "<tr><td>Confirm New Password:</td>\n";
        $args[1] .= "<td><input type=\"password\" name=\"confirm_pwd\" value=\"\" size=\"25\"></td></tr>\n";
        $args[1] .= "</table>";
        table_data($args, 0);
    }


    $notify = $user['notify_mail'];
    $report = $user['report_mail'];

    $msg = "Enter all the recipients' e-mail addresses separated by commas.";
    $doc = fontspeak("<i>$msg</i>");

    $args[0] = 'Notifications default<br>email recipients: ';
    $args[1] = "<input type='text' name='notify_mail' value='$notify' size='70'><br>\n";
    $args[1] .= "<span class='footnote'>$msg</span>";
    table_data($args, 0);

    $args[0] = 'Reports default<br>email recipients: ';
    $args[1] = "<input type=\"text\" name=\"report_mail\" value=\"$report\" size=\"70\"><br>\n";
    $args[1] .= "<span class=\"footnote\">$msg</span>";
    table_data($args, 0);

    $submit = "<input type=\"submit\" name=\"submit\" value=\"update\">";
    $reset  = "<input type=\"reset\" value=\"reset\">";
    $action = "$submit&nbsp;&nbsp;&nbsp;$reset";
    echo span_data(3, $action);
    table_footer();
    echo "</form>\n\n\n";
}
echo jumparound('top,bottom,users,sites,update', $admin, $dpriv);

echo head_standard_html_footer($authuser, $db);
