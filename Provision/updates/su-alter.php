<?php

/*
Revision history:

Date        Who     What
----        ---     ----
11-Nov-02   NL      create file
22-Nov-02   NL      filter sitename to authuser access
22-Nov-02   NL      alter_priv (required to continue)
22-Nov-02   NL      move redcommand to common.php3
22-Nov-02   NL      add error checking for action, id & alter_priv
25-Nov-02   NL      edit mode: removed select list of sitenames; no longer editable
25-Nov-02   NL      select list of version now site-specific
 5-Dec-02   NL      simplify getting sitename from Customers table; just use cust_array()
11-Feb-03   EWB     even simpler, use library functions.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
12-Mar-03   EWB     changed database name to 'swupdate'.
25-Apr-03   EWB     Site Filter
29-Apr-03   EWB     l-cust not needed.
30-Apr-03   EWB     User site filters.
26-May-03   AAM     Fixed spelling of privileges.
31-Mar-04   EWB     Update for non site specific versions.
31-Mar-04   EWB     Group Add.
 1-Apr-04   EWB     Check All/Check None.
 9-Apr-04   EWB     Second Copy Check All/Check None.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
 
*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-slct.php');
include('../lib/l-user.php');
include('../lib/l-gsql.php');
include('header.php');
include('../lib/l-head.php');

function title($act)
{
    switch ($act) {
        case 'add':
            return 'Add a Site Update Record';
        case 'edit':
            return 'Edit a Site Update Record';
        case 'grp':
            return 'Group Update Management';
        default:
            return 'Site Update Record Updated';
    }
}

function checkbox($name, $checked)
{
    $valu = ($checked) ? 'checked' : '';
    return "<input type=\"checkbox\" $valu name=\"$name\" value=\"1\">";
}

function hidden($name, $value)
{
    return "<input type=\"hidden\" name=\"$name\" value=\"$value\">\n\n";
}

function download_list($auth, $db)
{
    $list = array();
    $list[0] = '    ';
    $qu  = safe_addslashes($auth);
    $sql = "select * from Downloads\n"
        . " where global = 1\n"
        . " or owner in ('','$qu')\n"
        . " order by name";
    $res = find_many($sql, $db);
    if ($res) {
        reset($res);
        foreach ($res as $key => $row) {
            $id   = $row['id'];
            $name = $row['name'];
            $list[$id] = $name;
        }
    }
    return $list;
}

function button($valu)
{
    $type = 'type="submit"';
    $name = 'name="submit"';
    $valu = "value=\"$valu\"";
    return "<input $type $name $valu>";
}

function table_data($args, $head)
{
    $txt = '';
    $td  = ($head) ? 'th' : 'td';
    if ($args) {
        $txt .= "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            $txt .=  "<$td>$data</$td>\n";
        }
        $txt .= "</tr>\n";
    }
    return $txt;
}

function table_header()
{
    return "<br>\n<table border=\"2\" align=\"left\" cellspacing=\"2\" cellpadding=\"2\">\n";
}


function table_footer()
{
    return "</table>\n<br clear=\"all\">\n<br>\n";
}

function find_site($id, $db)
{
    $row = array();
    if ($id > 0) {
        $sql = "SELECT * from UpdateSites WHERE id = $id";
        $row = find_one($sql, $db);
    }
    return $row;
}

function post($page, $name)
{
    return "<form method=\"post\" action=\"$page\" name=\"$name\">\n\n";
}

function form_table($site, $name, $sub)
{
    echo <<< HERE

        <table border="0" cellpadding="3">
            <tr>
                <td>Site Name:</td>
                <td>
                    $site
                </td>
            </tr>
            <tr>
                <td>Version Name:</td>
                <td>$name</td>
            </tr>
        </table>
        <br>

        <table border="0" cellpadding="3">
            <tr>
                <td>&nbsp;</td>
                <td>
                    <font face="verdana,helvetica" size="2">
                        <input type="submit" value="$sub">
                        &nbsp;&nbsp;&nbsp;
                        <input type="reset" value="Reset">
                        <br>
                    </font>
                </td>
            </tr>
        </table>

        </form>
HERE;
}


function edit($env, $db)
{
    $auth = $env['auth'];
    $id   = $env['id'];
    $versions = download_list($auth, $db);
    $row = find_site($id, $db);
    if ($row) {
        $sitename = $row['sitename'];
        $version  = @$row['version'];
    }

    echo post('su-act.php', 'form');
    echo hidden('action', 'edit');
    echo hidden('id', $id);
    $select = html_select('name', $versions, $version, 0);
    form_table($sitename, $select, 'Update');
}


function add(&$env, $db)
{
    $auth = $env['auth'];
    $carr = $env['carr'];
    $versions = download_list($auth, $db);
    $sites = html_select('site', $carr, '', 0);
    $names = html_select('name', $versions, '', 0);
    echo post('su-act.php', 'form');
    echo hidden('action', 'add');
    form_table($sites, $names, 'Add');
}


function table_span($text, $cols)
{
    $row = "<td colspan=\"$cols\">$text</td>";
    return "<tr>$row</tr>\n";
}

function left_right($cols, $left, $right)
{
    return <<< HERE
<tr>
  <td colspan="$cols">
    <table width="100%">
    <tr>
      <td align="left">
        $left
      </td>
      <td align="right">
        $right
      </td>
    </tr>
    </table>
  </td>
</tr>

HERE;
}


function group($env, $db)
{
    $carr = $env['carr'];
    $auth = $env['auth'];
    $chck = ($env['chck'] == 'all') ? 1 : 0;
    $list = download_list($auth, $db);
    $us   = array();
    $dl   = array();
    $names = array();
    if (($carr) && ($list)) {
        $txt = db_access($carr);
        $sql = "select * from UpdateSites\n"
            . " where sitename in ($txt)\n"
            . " order by sitename";
        $us  = find_many($sql, $db);
    }

    if ($us) {
        reset($us);
        foreach ($us as $key => $row) {
            $name = $row['version'];
            if ($name != '') {
                $names[$name] = '';
            }
        }
    }

    /*
        |  Normally the user doen't get to inspect
        |  a download record that he doesn't own.
        |  However, if it's assigned to a site that
        |  he has access to, then he'll gets to see
        |  the name and version information.
        */

    if ($names) {
        $key = safe_array_keys($names);
        $txt = db_access($key);
        $sql = "select * from Downloads\n"
            . " where name in ($txt)";
        $dl  = find_many($sql, $db);
        $key = array();
    }
    if ($dl) {
        reset($dl);
        foreach ($dl as $key => $row) {
            $name = $row['name'];
            $names[$name] = $row['version'];
        }
        $dl = array();
    }

    $num = safe_count($list);
    if (($us) && ($num > 1)) {
        $update = button('Update');
        echo post('su-act.php', 'form');
        echo hidden('action', 'grp');

        $self = $env['self'];
        $act  = "$self?action=grp&check";
        $all  = html_link("$act=all", 'Assign to all sites');
        $none = html_link("$act=none", 'Assign to no sites');

        $name = html_select('name', $list, '', 0);
        $text = "Version name: $name $update";
        echo "<p>$text</p>\n";
        echo "<p>$all</p>\n";
        echo "<p>$none</p>\n";

        $head = explode('|', 'Site Name| Current Target|Current Version');

        echo table_header();
        echo table_data($head, 1);
        reset($us);
        foreach ($us as $key => $row) {
            $id   = $row['id'];
            $site = $row['sitename'];
            $text = $row['version'];
            $vers = ($text) ? $names[$text] : '<br>';
            $name = ($text) ? $text : '<br>';
            $box  = checkbox("site_$id", $chck);
            $site = "$box $site";
            $args = array($site, $name, $vers);
            echo table_data($args, 0);
        }
        $cols = safe_count($head);
        echo left_right($cols, $all, $none);
        echo table_footer();
        echo "<p>$update</p>\n";
        echo "</form>\n";
    } else {
        if ($us)
            echo "<p>You don't have any download records.</p>\n";
        else
            echo "<p>There aren't any site update records.</p>\n";
    }
}


/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp   = component_installed();
$dbg    = get_integer('debug', 1);
$action = get_string('action', 'none');
$title  = title($action);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, $local_nav, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$user   = user_data($authuser, $db);
$filter = @($user['filtersites']) ?   1  : 0;
$alter  = @($user['priv_updates']) ?  1  : 0;
$debug  = @($user['priv_debug']) ?  $dbg : 0;
$carr   = site_array($authuser, 0, $db);

db_change($GLOBALS['PREFIX'] . 'swupdate', $db);

$id = get_integer('id', 0);



/*
    |   Perform error checking
    */

$error = '';
// check for action & id

if (($id <= 0) && ($action == 'edit')) {
    $error .= "<br>Error -- no id specified.";
}

if ($action == 'none') {
    $error .= "<br>Error -- no action specified.";
}

// check for permissions to alter records
if (!$alter) {
    $error .= "<br>Error -- You do not have privileges to add or edit records.";
}

if ($error) {
    echo "<span class=red>$error</span>";
    $action = 'err';
}

$env = array();
$env['id']   = $id;
$env['auth'] = $authuser;
$env['carr'] = $carr;
$env['self'] = server_var('PHP_SELF');
$env['acts'] = $action;
$env['chck'] = get_string('check', 'none');
switch ($action) {
    case 'add':
        add($env, $db);
        break;
    case 'edit':
        edit($env, $db);
        break;
    case 'grp':
        group($env, $db);
        break;
    case 'err':;
    default:
        break;
}
echo head_standard_html_footer($authuser, $db);
