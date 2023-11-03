<?php

/*
Revision history:

Date        Who     What
----        ---     ----
06-Nov-02   NL      create file
22-Nov-02   NL      move redcommand to common.php
22-Nov-02   NL      add Site Name, Username, and Password fields
22-Nov-02   NL      add error_username_password()
22-Nov-02   NL      add error_dup_record()
22-Nov-02   NL      add error_empty_version()
22-Nov-02   NL      trim version, url, username & password
25-Nov-02   NL      when deleting a download, reset site version to ''
27-Nov-02   NL      when editing a download, reset site version to ''
 3-Dec-02   NL      change titles & return page link
 3-Dec-02   NL      reverse messages in error_username_password()
 4-Dec-02   EWB     Reorginization Day
16-Dec-02   EWB     Fixed php short tags
16-Jan-03   EWB     Don't require register_globals
10-Feb-03   EWB     Use sandbox libraries.
10-Feb-03   EWB     New database.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
17-Mar-03   EWB     Database swupdate.
27-May-03   EWB     Show sql to debug users.
27-May-03   EWB     Specify cmdline for new installer if version > 1.005
27-May-03   EWB     Quoting issues.
29-Mar-04   EWB     Allow user update of swupdate.Downloads.cmdline.
31-Mar-04   EWB     Site independant download records.
 1-Apr-04   EWB     Don't allow empty urls.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
 
*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once('../lib/l-util.php');
include_once('../lib/l-db.php');
include_once('../lib/l-sql.php');
include_once('../lib/l-serv.php');
include_once('../lib/l-rcmd.php');
include_once('../lib/l-user.php');
include_once('../lib/l-gsql.php');
include_once('../lib/l-head.php');
include_once('header.php');

function action_title($act)
{
    switch ($act) {
        case 'add':
            return 'Version Record Added';
        case 'edit':
            return 'Version Record Updated';
        case 'copy':
            return 'Version Record Copied';
        case 'conf':
            return 'Delete A Version Record';
        case 'del':
            return 'Version Record Deleted';
        default:
            return 'Version Record';
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

function error_empty_version()
{
    return '<br>The version cannot be empty.';
}

function error_empty_url()
{
    return '<br>The url cannot be empty.';
}

function error_username_password($username_or_password)
{
    if ($username_or_password == 'username') {
        $error = "<br>You entered a password but did not"
            .    " enter a corresponding username.";
    } else {
        $error = "<br>You entered a username but did not"
            .    " enter a corresponding password.";
    }

    return $error;
}

function changed($old, $new, $id)
{
    $href = "dl-alter.php?id=$id&action=edit";
    $edit = html_link($href, 'edit');
    return "<p>Note ... the name <b>$old</b> was not"
        .   " unique,<br>so this version is temporarily"
        .   " named <b>$new</b> instead.<br>You can"
        .   " $edit it if yow would like to choose"
        .   " another name.</p>\n";
}



function error_dup_record($name)
{
    return "<br>There is already a record"
        .  " named <b>$name</b>.";
}


/*
    |  To delete a download record, either you have to
    |  personally own it, or it must be unowned.
    */

function find_download($id, $authuser, $db)
{
    $row = array();
    if ($id > 0) {
        $qu  = safe_addslashes($authuser);
        $sql = "select * from Downloads\n"
            . " where id = $id\n"
            . " and owner in ('$qu','')";
        $row = find_one($sql, $db);
    }
    return $row;
}


/*
    |   Success functions
    */


function delete_success($version)
{
    echo "<br>"
        .  "You have deleted the record for version <b>$version</b>."
        .  "<br><br>";
}

function go_back($db)
{
    $versions = html_link('dl-list.php', 'Available Versions');
    echo "<p>Return to the $versions list.</p>";
}


/*
    |   Action functions
    */

function confirm_delete($id, $authuser, $db)
{
    $row  = find_download($id, $authuser, $db);
    $self = server_var('PHP_SELF');
    $lref = 'dl-list.php';
    if ($row) {
        $name = $row['name'];
        $vers = $row['version'];
        $href = "$self?action=del&id=$id";
        $no   = html_link($lref, '[No]');
        $yes  = html_link($href, '[Yes]');
        $msg  = "<p>"
            .  "Do you really want to delete"
            .  " <b>$name</b> for version <b>$vers</b>?</p>"
            .  "<p>$yes &nbsp;&nbsp; $no</p>"
            .  "\n";
    } else {
        $link = html_link($lref, 'Return to versions list');
        $msg = "<p>No such record.</p><p>$link.</p>";
    }
    echo $msg;
}


function delete($id, $authuser, $db)
{
    $good = 0;
    $num = 0;
    $row = find_download($id, $authuser, $db);
    if ($row) {
        $name = $row['name'];
        $id   = $row['id'];
        $sql  = "delete from Downloads where id = $id";
        $res  = redcommand($sql, $db);
        $num  = affected($res, $db);
    }
    if (($num) && ($row)) {
        // since des'd version no longer exists, reset to blank

        $good = 1;
        $qn   = safe_addslashes($row['name']);
        $sql  = "update UpdateSites set\n"
            . " version = ''\n"
            . " where version = '$qn'";
        $res  = redcommand($sql, $db);
        $num  = affected($res, $db);
        debug_note("$num sites updated");
    }

    if ($good)
        delete_success($name);
    else
        error_generic(3);
    go_back(0);
}


/*
    |  Just checking for existance, we don't care who the
    |  owner is.
    */

function exists($name, $id, $db)
{
    $qn  = safe_addslashes($name);
    $sql = "select id from Downloads\n"
        . " where name = '$qn'\n"
        . " and id != $id";
    $row = find_one($sql, $db);
    return ($row) ? true : false;
}

function unique($text, $id, $db)
{
    $uniq = 0;
    $name = $text;
    if (exists($name, $id, $db)) {
        do {
            $uniq++;
            $xxx  = sprintf('%03d', $uniq);
            $name = "$text $xxx";
        } while (exists($name, $id, $db));
    }
    return $name;
}



function add_download(&$env, $db)
{
    $cmdline  = $env['cmd'];
    $version  = $env['vers'];
    $name     = $env['name'];
    if ($cmdline == '') {
        $ver = substr($version, 0, 5);
        $cmp = strnatcmp('1.006', $ver);
        $cmdline = ($cmp <= 0) ? '/VERYSILENT' : '-s -a /s';
        debug_note("default cmdline version:$version ver:$ver cmp:$cmp cmd:$cmdline");
    }

    $text = ($name == '') ? $version : $name;
    $name = unique($text, 0, $db);

    $qn  = safe_addslashes($name);
    $qv  = safe_addslashes($version);
    $qc  = safe_addslashes($cmdline);
    $qo  = safe_addslashes($env['auth']);
    $qp  = safe_addslashes($env['pass']);
    $qus = safe_addslashes($env['user']);
    $qur = safe_addslashes($env['url']);
    $gbl = $env['glob'];
    $sql = "insert into Downloads set\n"
        . " name='$qn',\n"
        . " owner='$qo',\n"
        . " global=$gbl,\n"
        . " version='$qv',\n"
        . " url='$qur',\n"
        . " username='$qus',\n"
        . " password='$qp',\n"
        . " cmdline='$qc'";
    $res = redcommand($sql, $db);
    if (affected($res, $db)) {
        $id = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        debug_note("added $id for $name $version");
        if ($name != $text) {
            $msg = changed($text, $name, $id);
        } else {
            $msg = "<p>You have added a new record"
                .  "  named <b>$name</b> for version"
                .  " <b>$version</b>.</p>\n";
        }
    } else {
        $msg = "<p>There was a problem with your submission."
            .  " Please try again.</p>\n";
    }
    echo $msg;
    go_back($db);
}


function edit_download(&$env, $db)
{
    $good = false;
    $auth = $env['auth'];
    $num  = 0;
    $id   = $env['id'];
    $row  = find_download($id, $auth, $db);

    if ($row) {
        $id   = $row['id'];
        $old  = $row['name'];
        $name = $env['name'];
        $new  = unique($name, $id, $db);

        $gbl = $env['glob'];
        $qn  = safe_addslashes($new);
        $qp  = safe_addslashes($env['pass']);
        $qv  = safe_addslashes($env['vers']);
        $qc  = safe_addslashes($env['cmd']);
        $qo  = safe_addslashes($env['auth']);
        $qus = safe_addslashes($env['user']);
        $qur = safe_addslashes($env['url']);
        $sql = "update Downloads set\n"
            . " name='$qn',\n"
            . " version='$qv',\n"
            . " owner='$qo',\n"
            . " global=$gbl,\n"
            . " url='$qur',\n"
            . " username='$qus',\n"
            . " password='$qp',\n"
            . " cmdline='$qc'\n"
            . " where id = $id";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }

    if ($num == 1) {
        $good = 1;
        if ($new != $old) {
            $qo  = safe_addslashes($old);
            $qn  = safe_addslashes($new);
            $sql = "update UpdateSites set\n"
                . " version = '$qn'\n"
                . " where version = '$qo'";
            $res = redcommand($sql, $db);
            $num = affected($res, $db);
            debug_note("there are $num sites updated");
        }
        if ($new != $name) {
            echo changed($name, $new, $id);
        }
    }

    if ($good) {
        echo "<br>"
            .  "You have updated the record for <b>$old</b>."
            .  "<br><br>\n";
    } else {
        if ($res) {
            echo "Nothing changed";
        } else
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
$id     = get_integer('id', 0);
$dbg    = get_integer('debug', 1);
$title  = action_title($action);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, $local_nav, 0, 0, $db);

$user     = user_data($authuser, $db);
$debug    = @($user['priv_debug']) ? $dbg : 0;
$alter    = @($user['priv_downloads']) ? 1 : 0;

$global   = get_integer('global', 0);
$version  = get_string('version', '');
$name     = get_string('name', '');
$password = get_string('password', '');
$username = get_string('username', '');
$cmdline  = get_string('cmdline', '');
$url      = get_string('url', '');

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

db_change($GLOBALS['PREFIX'] . 'swupdate', $db);
$error = '';


debug_note("name:name ver:$version glob:$global user:$username cmd:$cmdline");

?>
<table>
    <tr>
        <td>
            <?php


            /*
    |   Perform error checking
    */

            if (($id <= 0) && ($action != 'add') && ($action != 'copy')) {
                $error .= "<br>Error -- no id specified.";
            }

            if ($alter == 0) {
                $error .= "<br>Error -- You do not have privileges to add or edit records.";
            }

            if ($action == 'none') {
                $error .= '<br>Error -- no action specified.';
            }

            if ($action == 'add' || $action == 'edit' || $action == 'copy') {
                // version cant be blank
                if ($version == '') {
                    $error .= error_empty_version();
                }
                if ($url == '') {
                    $error .= error_empty_url();
                }

                // username & password: both or neither
                if (($username != '') && ($password == '')) {
                    $error .= error_username_password('password');
                } elseif (($username == '') && ($password != '')) {
                    $error .= error_username_password('username');
                }
            }

            if ($error) {
                echo "<span class=\"red\">$error</span>";
                $action = 'err';
            }

            $env = array();
            $env['id']   = $id;
            $env['cmd']  = $cmdline;
            $env['url']  = $url;
            $env['name'] = $name;
            $env['vers'] = $version;
            $env['auth'] = $authuser;
            $env['glob'] = $global;
            $env['user'] = $username;
            $env['pass'] = $password;
            $env['acts'] = $action;

            switch ($action) {
                case 'add':
                    add_download($env, $db);
                    break;
                case 'edit':
                    edit_download($env, $db);
                    break;
                case 'copy':
                    add_download($env, $db);
                    break;
                case 'conf':
                    confirm_delete($id, $authuser, $db);
                    break;
                case 'del':
                    delete($id, $authuser, $db);
                    break;
                case 'err':
                    break;
                case 'none':
                    break;
                default:
                    break;
            }
            echo "</td></tr></table>\n";

            echo head_standard_html_footer($authuser, $db);
            ?>