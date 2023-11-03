<?php

/*
Revision history:

Date        Who     What
----        ---     ----
11-Nov-02   NL      create file
22-Nov-02   NL      create then commented out Edit & Delete actions
22-Nov-02   NL      alter_priv (required for Force checkbox)
22-Nov-02   NL      changed Force checkbox forms from per row/machine to whole page
22-Nov-02   NL      move redcommand to common.php
22-Nov-02   NL      remove fontspeak
22-Nov-02   NL      change display() arguments to a cols array
25-Nov-02   NL      delete a machine
25-Nov-02   NL      global forced update (force_all)
27-Nov-02   NL      make site-specific page title; add function determine_site()
27-Nov-02   NL      make force_all work in "off" position
 3-Dec-02   NL      change title & message
 3-Dec-02   NL      HTML for displaying "force all" only if $alter_priv
 3-Dec-02   NL      Add valign='top' to table_data()
 4-Dec-02   EWB     Reorginization Day
31-Dec-02   EWB     Single quote for non-evaluated strings.
10-Feb-03   EWB     Uses new database
10-Feb-03   EWB     Uses sandbox libraries
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
12-Mar-03   EWB     changed database name to 'swupdate'.
17-Mar-03   EWB     Initialize $siteid, $action
18-Mar-03   EWB     Initialize $force_all
25-Apr-03   EWB     Site Filter / quoting issues.
29-Apr-03   EWB     l-cust not needed.
30-Apr-03   EWB     User site filter.
21-Jul-03   NL      update_force():  quote the sitename before entering into database.
21-Jul-03   NL      Bug Fix: change preg_match to  look for [0-9], not [1-9]
17-Sep-04   EWB     'force' is a reserved word in mysql 4.
*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('header.php');
include('../lib/l-slct.php');
include('../lib/l-user.php');
include('../lib/l-jump.php');
include('../lib/l-head.php');


/*
    |   returns sitename for title, but also sets global sitename & version for MAIN
    */

function determine_site($db, $siteid)
{
    global $sitename;
    global $desired_v;

    $sitename = '';
    $desired_v = '';

    if ($siteid > 0) {
        db_change($GLOBALS['PREFIX'] . 'swupdate', $db);
        $sql  = "SELECT * FROM UpdateSites ";
        $sql .= "WHERE id = " . $siteid;
        $res = command($sql, $db);

        if ($res && mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_array($res);
            $sitename       = $row['sitename'];
            $desired_v      = $row['version'];
            if (empty($desired_v))
                $desired_v  = "(none selected)";
        }
    }
    return $sitename;
}

function choose_site($site_list, $db)
{
    /*
        |   Get sitename options for select list
        */
    $sitenames = array();
    $sitename = '';

    if ($site_list) {
        $sql  = "SELECT id, sitename from UpdateSites\n";
        $sql .= " WHERE sitename IN ($site_list)\n";
        $sql .= " ORDER BY sitename";

        $result = redcommand($sql, $db);
        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                    $sitenames[$row['id']]  = $row['sitename'];
                }
            }
        }
    }
?>
    In order to view machine update data, you must first choose a site.
    <form method=post action="mu-list.php" name="form">
        <?php echo html_select("siteid", $sitenames, $sitename, 1) ?>
        <input type='submit' name='submit' value='View Machines'>
    </form>
<?php
    include('../lib/l-foot.php');
}


function again($dpriv)
{
    $a = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link('#machines', 'machines');
    if ($dpriv) {
        $self = server_var('PHP_SELF');
        $args = server_var('QUERY_STRING');
        $href = ($args) ? "$self?$args" : $self;
        $home = '../acct/index.php';
        $a[] = html_link('census.php', 'debug');
        $a[] = html_link($href, 'again');
        $a[] = html_link($home, 'home');
    }
    return jumplist($a);
}

function table_data($rows, $head)
{
    $td = ($head) ? "th" : "td";
    if ($rows) {
        echo "<tr>\n";
        reset($rows);
        // foreach ($rows as $key => $data)
        foreach ($rows as $key => $data) {
            echo "<$td valign='top'>$data</$td>\n";
        }
        echo "</tr>\n";
    }
}


function table_header($args)
{
    echo "<table border='2' align='left' cellspacing='2' cellpadding='2' width='100%'>\n";
    echo "<tr>\n";
    table_data($args, 1);
    echo "</tr>\n";
}

function display($id, $cols)
{
    global $alter_priv;
    $args  = array();

    if ($alter_priv) {
        //$edit = "<a href='mu-alter.php?id=$id&action=edit'>[edit]</a><br>";
        $del  = "<a href='mu-act.php?id=$id&action=confirmdelete'>[delete]</a>";
        //$action_string = $edit . $del;
        $args[] = $del;
    }

    reset($cols);
    foreach ($cols as $k => $v) {
        $args[] = $v;
    }

    table_data($args, 0);
}

function table_end($db)
{
?>
    </table>
    <br clear="all">
<?php
}

function update_force($id, $sitename, $force, $db)
{
    $sitename = safe_addslashes($sitename);

    # update force
    $sql  = "UPDATE UpdateMachines";
    $sql .= " SET doforce = " . $force;
    if (strlen($sitename))
        $sql .= " WHERE sitename = '$sitename' ";
    else
        $sql .= " WHERE id = " . $id;
    redcommand($sql, $db);
}


/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();
$user = user_data($authuser, $db);

$alter_priv = @($user['priv_updates']) ? 1 : 0;
$debug      = @($user['priv_debug']) ?   1 : 0;
$filter     = @($user['filtersites']) ?  1 : 0;
$site_list  = find_sites($authuser, $filter, $db);
$siteid     = get_integer('siteid', 0);
$force_all  = get_integer('force_all', 0);
$action     = get_string('action', 'none');
debug_note("siteid: $siteid, user:$authuser");

db_change($GLOBALS['PREFIX'] . 'swupdate', $db);
$sitename = determine_site($db, $siteid);
$title  = ($sitename == '') ? 'Select a Site' : "$sitename Machine Update Status";

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
db_change($GLOBALS['PREFIX'] . 'core', $db);
echo standard_html_header($title, $comp, $authuser, $local_nav, 0, 0, $db);
db_change($GLOBALS['PREFIX'] . 'swupdate', $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users


/*
    | Check for incoming w/o sitename
    */

if ($siteid <= 0) {
    choose_site($site_list, $db);
    exit;
}


/*
    | Check for incoming force checkbox form
    */

if (!empty($action) && $action == 'update') {
    if (!empty($force_all)) {
        update_force('', $sitename, $force_all, $db);
    } else {
        reset($_POST);
        foreach ($_POST as $name => $value) {
            # find all the force checkbox fields ('force_$id')
            $matches = array();
            if (preg_match('/^force_([0-9]+)$/', $name, $matches)) {
                # What machine id?
                $id = $matches[1];
                $force = $value;

                # To avoid duplicates (we had to send over default checkbox value=0)
                $forcedata[$id] = $value;
            }
        }

        reset($forcedata);
        foreach ($forcedata as $id => $force) {
            update_force($id, '', $force, $db);
        }
    }
}

/*
    | Display sitename & version at top of page
    */

?>
<table>
    <tr>
        <td align="right"><span class="blue">site name: </class>
        </td>
        <td><?php echo $sitename ?></td>
    </tr>
    <tr>
        <td align="right"><span class="blue">desired version: </class>
        </td>
        <td><?php echo $desired_v ?></td>
    </tr>
</table>

<?php

/*
    | Start the display table
    */

echo mark('machines');
echo again($debug);

if ($alter_priv) {
?>
    <form method="post" action="mu-list.php" name="form">
        <input type='hidden' name='siteid' value='<?php echo $siteid ?>'>
        <input type='hidden' name='action' value='update'>
        <input type='submit' value='Submit Changes'>
        <br><br>
    <?php
}


# Get machines from database
$qs   = safe_addslashes($sitename);
$sql  = "SELECT * FROM UpdateMachines\n";
$sql .= " WHERE sitename = '$qs'\n";
$sql .= " ORDER BY machine";
$res = redcommand($sql, $db);

if ($res) {
    if (mysqli_num_rows($res)) {
        $headers = array();
        if ($alter_priv)
            $headers[]  = 'Action';
        $headers[] = 'Machine Name';
        $headers[] = 'Last Contact<br><small>time of last contact</small>';
        $headers[] = 'Last Version<br><small>version reported at last contact</small>';
        $headers[] = 'Last Update<br><small>time of last update</small>';
        $headers[] = 'Old Version<br><small>version that was reported by machine</small>';
        $headers[] = 'New Version<br><small>version that was sent to machine</small>';
        $headers[] = 'Was Forced<br><small>whether the last update was forced</small>';
        $headers[] = 'Force?<br><small>whether to force the next update</small>';

        table_header($headers);

        while ($row = mysqli_fetch_array($res)) {
            $id         = $row['id'];
            $sitename   = !empty($row['sitename'])    ? $row['sitename']   : '&nbsp;';
            $machine    = !empty($row['machine'])     ? $row['machine']    : '&nbsp;';
            $timecontact = !empty($row['timecontact']) ? $row['timecontact'] : '0';
            $timeupdate = !empty($row['timeupdate'])  ? $row['timeupdate'] : '0';
            $lastversion = !empty($row['lastversion']) ? $row['lastversion'] : '&nbsp;';
            $oldversion = !empty($row['oldversion'])  ? $row['oldversion'] : '&nbsp;';
            $newversion = !empty($row['newversion'])  ? $row['newversion'] : '&nbsp;';
            $wasforced  = !empty($row['wasforced'])   ? $row['wasforced']  : '&nbsp;';
            $force      = !empty($row['doforce'])     ? $row['doforce']    : '0';

            $timecontact_str = ($timecontact > 0) ? '<small>' . datestring($timecontact) . '</small>' : '&nbsp;';
            $timeupdate_str  = ($timeupdate > 0) ?  '<small>' . datestring($timeupdate)  . '</small>' : '&nbsp;';
            if ($wasforced == 0)        $wasforced_str = 'not forced';
            if ($wasforced == 1)        $wasforced_str = 'forced';
            if ($wasforced == '&nbsp;') $wasforced_str = '&nbsp;';
            if ($alter_priv) {
                $force_checked  = (@$force == 1) ? 'CHECKED' : '';
                $force_str = "<input type='hidden' name='force_$id' value='0'>\n" .
                    "<input type='checkbox' name='force_$id' value='1' $force_checked>\n";
            } else {
                if ($force == 0) $force_str = "don't force";
                if ($force == 1) $force_str = "force update";
            }

            $datas = array();
            $datas[] = $machine;
            $datas[] = $timecontact_str;
            $datas[] = $lastversion;
            $datas[] = $timeupdate_str;
            $datas[] = $oldversion;
            $datas[] = $newversion;
            $datas[] = $wasforced_str;
            $datas[] = $force_str;
            display($id, $datas);
        }

        if ($alter_priv) {
            echo "<tr><td colspan=8></td><td>";
            echo "<input type='hidden' name='force_all' value='0'>\n";
            echo "<input type='checkbox' name='force_all' value='1'>\n";
            echo "<b>Force All</b></td></tr>\n";
        }

        table_end($db);

        if ($alter_priv) {
            echo "<br><input type='submit' value='Submit Changes'>\n</form>";
        }
    }
    ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
} else {
    ?>
        <tr>
            <td colspan=5><b>There are no machines listed for this site.</b></td>
        </tr>
    <?php
}


echo again($debug);
include('../lib/l-foot.php');
    ?>