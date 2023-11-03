<?php
/*
Revision History

Date        Who    What
----        ---    ----
14-Dec-05   BJS    Created.
15-Dec-05   BJS    Added comments.
21-Dec-05   BJS    Added constant to GRPS_return_group_from_mgroupid().
29-Dec-05   BJS    Added group name to title.
19-Sep-06   WOH    Changed name of standard_footer.  Also added username arg.

*/

include('../lib/l-util.php');
include('../lib/l-cnst.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-jump.php');
include('../lib/l-user.php');
include('../lib/l-form.php');
include('../lib/l-tabs.php');
include('../lib/l-head.php');
include('../lib/l-grps.php');
include('../lib/l-qtbl.php');


/*
    | $set = array of site, host and id.
    | Build a table with a row for each machine containing a link
    | to config/config.php (sites:configuration).
   */
function CHCK_build_table($set)
{
    $list = array(
        0 => '<center>Action</center>',
        1 => '<center>Site Name</center>',
        2 => '<center>Machine Name</center>'
    );

    $tbl  = table_start();
    $tbl .= QTBL_table_heading($list);

    foreach ($set as $index => $out) {
        $id  = $out['id'];
        $link = html_link(
            "../config/config.php?hid=$id",
            '[configure Scrip]'
        );

        $site = $out['site'];
        $host = $out['host'];

        $tbl .= <<< HERE
            <tr>
              <td>$link</td>
              <td>$site</td>
              <td>$host</td>
            </tr>
HERE;
    }
    $tbl .= table_end();

    return $tbl . '<br><br>';
}


/*
    | $env = global array
    | $db  = database handle
    | get the group information for the given mgroupid and 
    | build the table. On error write to error_log and return
    | false.
   */
function CHCK_site_machine($env, $db)
{
    $mgroupid = $env['mgroupid'];
    $user     = $env['user']['username'];

    /* an array of all the machines/sites in a given mgroupid */
    $set = GRPS_return_group_from_mgroupid(
        $mgroupid,
        $user,
        constQueryIncludeMgroupid,
        $db
    );
    if ($set) {
        /* build the table to display */
        $tbl = CHCK_build_table($set);
    } else {
        /* GRPS_return_group_from_mgroupid writes to error_log */
        $tbl = 'There was an error accessing the variables.';
    }
    echo $tbl;
}


/*
    | Main Program
   */

$now      = time();
$db       = db_connect();
$authuser = process_login($db);
$comp     = component_installed();

/* alex wants the group name in the title */
$mgroupid = get_integer('mgroupid', 0);
$sql = "SELECT name from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid = $mgroupid";
$set = find_one($sql, $db);
$grp = $set['name'];

$title = "Configuration - '$grp' Machines";

echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);

$dbg  = (get_argument('debug', 0, 1)) ?   1 : 0;

$user = user_data($authuser, $db);

$priv_debug = @($user['priv_debug']) ? 1 : 0;
$priv_admin = @($user['priv_admin']) ? 1 : 0;

$debug = ($priv_debug) ? $dbg : 0;

$act = get_string('act', '');
$env = array();
$env['user']     = $user;
$env['mgroupid'] = $mgroupid;



switch ($act) {
    default:
        CHCK_site_machine($env, $db);
        break;
}

echo head_standard_html_footer($authuser, $db);
