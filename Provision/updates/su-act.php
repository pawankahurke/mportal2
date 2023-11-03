<?php

/*
Revision history:

Date        Who     What
----        ---     ----
11-Nov-02   NL      create file
22-Nov-02   NL      move redcommand to common.php
22-Nov-02   NL      add error_dup_record()
25-Nov-02   NL      whenever site is deleted, delete its machines
 3-Dec-02   NL      change titles & return page link
 3-Dec-02   NL      change titles & return page link
 5-Dec-02   NL      whenever site is deleted, delete its versions
 6-Dec-02   EWB     Reorginization Day
16-Jan-03   EWB     Don't require register_globals
10-Feb-03   EWB     Use sandbox libraries
10-Feb-03   EWB     Uses new database.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
12-Mar-03   EWB     changed database name to 'swupdate'.
27-May-03   EWB     Show sql to debug users.
28-May-03   EWB     Added missing priv check.
28-May-03   EWB     Quoting issues.
23-Jun-03   EWB     Fixed a logic error in sitename check.
31-Mar-04   EWB     Gang Assign.
21-Apr-04   EWB     Allow clearing of site version.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('../lib/l-head.php');
include('../lib/l-gsql.php');
include('header.php');


function action_title($act)
{
    switch ($act) {
        case 'add':
            return 'Site Update Record Added';
        case 'edit':
            return 'Site Update Record Updated';
        case 'conf':
            return 'Delete A Site Update Record';
        case 'del':
            return 'Site Update Record Deleted';
        case 'grp':
            return 'Site Update Group Updated';
        default:
            return 'Site Update Record Updated';
    }
}


/*
    |   Error functions
    */

function error_generic($db)
{
?>
    <br><span class="red">
        There was a problem with your submission. Please try again.
    </span>
<?php
}

function error_dup_record($sitename, $version)
{
    $error = "<br>There is already a record with sitename <b>$sitename</b>.<br>";
    return $error;
}

function error_version_unavailable($sitename, $version)
{
    $error = "<br>Unfortunately, version <b>$version</b> is not" .
        " currently available for site <b>$sitename</b>. " .
        " Please select a different version.<br>";
    return $error;
}

function unknown($env)
{
    $acts = $env['acts'];
    echo "<p>unknown action <b>$acts</b>.</p>\n\n";
}



/*
    |   Success functions
    */

function add_success($sitename)
{
    echo <<< HERE

        <br>
        You have added a record for site <b>$sitename</b>.
        <br><br>
HERE;
}

function edit_success($sitename)
{
    echo <<< HERE

        <br>
        You have updated the record for site <b>$sitename</b>.
        <br><br>
HERE;
}

function delete_success($site)
{
    echo <<< HERE

        <br>
        You have deleted the record for site <b>$site</b>,
        as well as the records for its machines.
        <br><br>
HERE;
}

function go_back($db)
{
    $href = 'su-list.php';
    $link = html_link($href, 'Site Version List');
    echo "<p>Return to the $link</p>\n";
}


function find_site($env, $db)
{
    $row  = array();
    $id   = $env['id'];
    $carr = $env['carr'];
    if (($id > 0) && ($carr)) {
        $txt = db_access($carr);
        $sql = "select * from UpdateSites\n"
            . " where id = $id\n"
            . " and sitename in ($txt)";
        $row = find_one($sql, $db);
    }
    return $row;
}


function find_dnload($name, $auth, $db)
{
    $row  = array();
    if (($name) && ($auth)) {
        $qn  = safe_addslashes($name);
        $qa  = safe_addslashes($auth);
        $sql = "select * from Downloads\n"
            . " where name = '$qn'\n"
            . " and (owner in ('','$qa')\n"
            . " or global = 1)";
        $row = find_one($sql, $db);
    }
    return $row;
}


/*
    |   Action functions
    */

function confirm($env, $db)
{
    $row = find_site($env, $db);
    if ($row) {
        $id   = $row['id'];
        $site = $row['sitename'];
        $self = server_var('PHP_SELF');
        $href = "$self?action=del&id=$id";
        $yes  = html_link($href, '[Yes]');
        $no   = html_link('su-list.php', '[No]');
        $msg  = "<p>Do you really want to delete the"
            . " records for site <b>$site</b>"
            . " and all of it's machines?</p>"
            . "<br>\n"
            . "<p>$yes &nbsp;&nbsp; $no</p>\n";
    } else {
        $id  = $env['id'];
        $msg = "<p>We did not find any site records.</p>\n\n";
        debug_note("site $id not found");
    }
    echo $msg;
}

function delete(&$env, $db)
{
    $site = '';
    $num = 0;
    $row = find_site($env, $db);
    if ($row) {
        $site = $row['sitename'];
        $id   = $row['id'];
        $sql  = "delete from UpdateSites\n where id = $id";
        $res  = redcommand($sql, $db);
        $num  = affected($res, $db);
    }
    if (($num) && ($site)) {
        $good = true;
        $qs  = safe_addslashes($site);
        $sql = "delete from UpdateMachines\n where sitename = '$qs'";
        $res = redcommand($sql, $db);
        $num = affected($sql, $db);
        debug_note("removed $num machines");
    }

    if ($good) {
        delete_success($site);
        go_back(0);
    }
}

function add($env, $db)
{
    $site = $env['site'];
    $name = $env['name'];
    $auth = $env['auth'];
    $qs  = safe_addslashes($site);
    $qn  = safe_addslashes($name);
    $qa  = safe_addslashes($auth);
    $sql = "select * from " . $GLOBALS['PREFIX'] . "core.Customers\n"
        . " where customer = '$qs' and\n"
        . " username = '$qa'";
    $dup = array();
    $num = 0;
    $row = find_one($sql, $db);
    if ($row) {
        $sql = "select * from UpdateSites\n"
            . " where sitename = '$qs'";
        $dup = find_one($sql, $db);
    }
    if (($row) && (!$dup)) {
        $sql = "insert into UpdateSites set\n"
            . " sitename='$qs',\n"
            . " version='$qn'";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    if ($num) {
        add_success($site);
        go_back($db);
    }
}


function report_error($error)
{
    echo "<span class=red>$error</span><br>";
}


/*
    |  To change the target download, the download
    |  record has to already exist.  However, we do
    |  allow it in the special case where we are
    |  clearing the name.
    */

function edit(&$env, $db)
{
    $good = false;
    $num = 0;
    $row = find_site($env, $db);
    $dn  = array();
    if ($row) {
        $id   = $row['id'];
        $site = $row['sitename'];
        $name = $env['name'];
        $auth = $env['auth'];
        $dn   = find_dnload($name, $auth, $db);
        if ($dn) {
            $good = true;
        } else {
            $good = ($name == '') ? true : false;
        }
        if ($good) {
            $qn  = safe_addslashes($name);
            $sql = "update UpdateSites set\n"
                . " version='$qn'\n"
                . " where id = $id";
            $res = redcommand($sql, $db);
            $num = affected($res, $db);
        }
    }

    if (($good) && ($site)) {
        if ($num == 1) {
            edit_success($site);
        } else {
            echo "<p>Nothing has changed ...</p>\n";
        }
    } else {
        error_generic($db);
    }
    go_back($db);
}


/*
    |  To change the target download, the download
    |  record has to already exist.  However, we do
    |  allow it in the special case where we are
    |  clearing the name.
    */

function group(&$env, $db)
{
    $sites = array();
    $total = 0;
    $good = false;
    $name = $env['name'];
    $auth = $env['auth'];
    $carr = $env['carr'];
    $dn   = find_dnload($name, $auth, $db);
    if ($carr) {
        $txt = db_access($carr);
        $sql = "select * from UpdateSites\n"
            . " where sitename in ($txt)";
        $sites = find_many($sql, $db);
    }

    if ($dn) {
        $good = true;
    } else {
        $good = ($name == '') ? true : false;
    }


    if (($sites) && ($good)) {
        $qn = safe_addslashes($name);
        reset($sites);
        foreach ($sites as $key => $row) {
            $id   = $row['id'];
            $site = $row['sitename'];
            $set  = get_integer("site_$id", 0);
            if ($set) {
                debug_note("site:$site, name:$name");
                $sql = "update UpdateSites set\n"
                    . " version = '$qn'\n"
                    . " where id = $id";
                $res = redcommand($sql, $db);
                $num = affected($res, $db);
                if ($num) {
                    if ($name == '')
                        $msg = 'cleared';
                    else
                        $msg = "updated to <b>$name</b>";
                } else
                    $msg = 'unchanged';
                echo "<p>Site <b>$site</b> is $msg.</p>\n";
                $total += $num;
            }
        }
    }
    if ($good) {
        if ($total > 0) {
            echo "<p>$total sites updated</p>\n";
        } else {
            echo "<p>Nothing has changed ...</p>\n";
        }
    } else {
        error_generic($db);
    }
    go_back($db);
}


/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();


$action = get_string('action', 'none');
$title  = action_title($action);
$id     = get_integer('id', 0);
$dbg    = get_integer('debug', 1);
$msg    = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, $local_nav, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$user  = user_data($authuser, $db);
$debug = @($user['priv_debug']) ? $dbg : 0;
$alter = @($user['priv_updates']) ? 1 : 0;
$site  = get_string('site', '');
$name  = get_string('name', '');
$carr  = site_array($authuser, 0, $db);

db_change($GLOBALS['PREFIX'] . 'swupdate', $db);
$error = '';
echo "<table><tr><td>\n";

/*
    |   Perform error checking
    */

if (($id <= 0) && ($action != 'add') && ($action != 'grp')) {
    $error .= "<br>Error -- no id specified.";
}

if ($alter == 0) {
    $error .= "<br>Error -- You do not have privileges to add or edit records.";
}

if ($action == 'none') {
    $error .= "<br>Error -- no action specified.";
}

if ($error) {
    $action = 'err';
}

$env = array();
$env['id']   = $id;
$env['site'] = $site;
$env['name'] = $name;
$env['carr'] = $carr;
$env['auth'] = $authuser;
$env['acts'] = $action;
switch ($action) {
    case 'add':
        add($env, $db);
        break;
    case 'edit':
        edit($env, $db);
        break;
    case 'conf':
        confirm($env, $db);
        break;
    case 'del':
        delete($env, $db);
        break;
    case 'grp':
        group($env, $db);
        break;
    case 'err':
        error($env, $error);
        break;
    case 'none':
        error($env, $error);
        break;
    default:
        unknown($env);
        break;
}
echo "</td></tr></table>\n";
echo head_standard_html_footer($authuser, $db);
?>