<?php

/*
Revision History

25-Feb-05    EWB     Created.
 1-Mar-05    EWB     Populate Revisions
 2-Mar-05    EWB     Start Variables
 3-Mar-05    EWB     Populate Variables.
 4-Mar-05    EWB     Variables and Values version independant
 7-Mar-05    EWB     Host Globals / Locals
 9-Mar-05    EWB     Value Ordinals
14-Mar-05    EWB     Site Specific Variables
27-May-05    EWB     Cannot call mysql_create_db on fedora core 4
31-May-05    EWB     Legacy Checksum Cache
12-Sep-05    BTE     Added checksum invalidation code.
12-Oct-05    BTE     Lots of changes to support gconfig tables in core.
13-Oct-05    BTE     Moved several functions to l-gcfg.php.
03-Nov-05    BTE     Removed debug_vals (unused).
05-Nov-05    BTE     Specify the operation when calling DSYN_DeleteSet.
26-Jan-06    BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                     pointers.  Bug 2969: Remove the siteman to gconfig
                     conversion.
22-Jun-06    BTE     Bug 3489: Variable Sync Issues.
19-Jul-06    BTE     Bug 3239: 913 errors on CI server.
19-Sep-06    WOH     Changed name of standard_footer.  Also added username arg.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-head.php');
include('../lib/l-rcmd.php');
include('../lib/l-rlib.php');
include('../lib/l-gsql.php');
include('../lib/l-drty.php');
include('../lib/l-tabs.php');
include('../lib/l-grps.php');
include('../lib/l-gdrt.php');
include('../lib/l-cnst.php');
include('../lib/l-cbld.php');
include('../lib/l-jump.php');
include('../lib/l-errs.php');
include('../lib/l-dsyn.php');
include('../lib/l-gcfg.php');
include('../lib/l-user.php');

define('constSiteName', 'custName');
define('constSiteScop', 43);

function again()
{
    $self = server_var('PHP_SELF');
    $args = server_var('QUERY_STRING');
    $href = ($args) ? "$self?$args" : $self;
    $cmd  = "$self?act";
    $aa   = array();
    $aa[] = html_link('#top', 'top');
    $aa[] = html_link('#bottom', 'bottom');
    $aa[] = html_link('index.php', 'home');
    $aa[] = html_link("$cmd=menu", 'menu');
    $aa[] = html_link("$cmd=list", 'list');
    $aa[] = html_link($href, 'again');
    return jumplist($aa);
}



/*
    |  This is a non-debug redcommand
    */

function onecommand($sql, $dbg, $db)
{
    return ($dbg) ? redcommand($sql, $db) : command($sql, $db);
}


function find_databases($db)
{
    $dbs  = array();
    $res  = (($___mysqli_tmp = mysqli_query($db, "SHOW DATABASES")) ? $___mysqli_tmp : false);
    if ($res) {
        $n = mysqli_num_rows($res);
        for ($i = 0; $i < $n; $i++) {
            $name = ((mysqli_data_seek($res, $i) && (($___mysqli_tmp = mysqli_fetch_row($res)) !== NULL)) ? array_shift($___mysqli_tmp) : false);
            $dbs[$name] = 1;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $dbs;
}


function create_dbase($name, $db)
{
    $res = false;
    $sql = "create database if not exists $name";
    $res = redcommand($sql, $db);
    if (!$res) {
        debug_note("could not create $name");
    }
    return $res;
}




function find_single($sql, $db)
{
    $row = array();
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $row;
}


function update_order($scop, $vers, $vars, $db)
{
    $num = 0;
    $set = explode(',', $vars);
    if ($set) {
        $qv = safe_addslashes($vers);
        reset($set);
        foreach ($set as $key => $code) {
            $pos = strpos($code, ':', $code);
            if ($pos > 0) {
                $name = substr($code, 0, $pos);
                $row  = find_var($scop, $name, $db);
                if ($row) {
                    $ord = $key + 1;
                    $vid = $row['varuniq'];

                    $sql = "select varversid from VarVersions where"
                        . " vers='$qv' and varuniq='$vid' and"
                        . " configorder=0";
                    $set2 = DSYN_DeleteSet(
                        $sql,
                        constDataSetGConfigVarVersions,
                        "varversid",
                        "update_order",
                        0,
                        0,
                        constOperationDelete,
                        $db
                    );

                    /* This update is not changing any "uniq" fields in
                            VarVersions and is fine as-is. */
                    $sql = "update VarVersions set\n"
                        . " configorder = $ord\n"
                        . " where vers = '$qv'\n"
                        . " and varuniq = '$vid'\n"
                        . " and configorder = 0";

                    if ($set2) {
                        $res = command($sql, $db);
                        $xxx = affected($res, $db);

                        DSYN_UpdateSet(
                            $set2,
                            constDataSetGConfigVarVersions,
                            "varversid",
                            $db
                        );
                        $num = $num + $xxx;
                    }
                }
            }
        }
    }
    return $num;
}


function fulldate($time)
{
    return ($time) ? date('m/d/y H:i:s', $time) : '<br>';
}


function para($text)
{
    return "<p>$text</p>\n";
}

function minidate($time)
{
    return ($time) ? date('m/d H:i', $time) : $time;
}

function census($db)
{
    $set = array();
    if (mysqli_select_db($db, core)) {
        $sql = "select C.host, C.site, C.uuid,\n"
            . " R.* from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
            . " where C.id = R.censusid\n"
            . " order by site, host";
        $set = find_many($sql, $db);
    }
    if ($set) {
        $args = explode('|', 'site|host|uuid|vers|ctim|stim|hid|prov');
        $cols = safe_count($args);
        $rows = safe_count($set);
        $text = "Machines &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $hid  = $row['censusid'];
            $ctim = $row['ctime'];
            $stim = $row['stime'];
            $host = $row['host'];
            $site = $row['site'];
            $uuid = $row['uuid'];
            $vers = $row['vers'];
            $prov = $row['provisional'];
            $ctim = minidate($ctim);
            $stim = minidate($stim);
            $prov = minidate($prov);
            $args = array($site, $host, $uuid, $vers, $ctim, $stim, $hid, $prov);
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        echo para('Nothing found');
    }
}

function command_list(&$act, &$txt)
{
    echo "<p>What do you want to do?</p>\n\n\n<ol>\n";

    reset($txt);
    foreach ($txt as $key => $doc) {
        $cmd = html_link($act[$key], $doc);
        echo "<li>$cmd</li>\n";
    }
    echo "</ol>\n";
}


function menu($db)
{
    $self = server_var('PHP_SELF');
    $cmd  = "$self?act";

    $act[] = "$cmd=full";
    $txt[] = 'Full Build';

    $act[] = "$cmd=menu";
    $txt[] = 'Debug Menu';

    $act[] = "$cmd=list";
    $txt[] = 'List current';

    $act[] = $self;
    $txt[] = 'Begin Again';

    $act[] = "$cmd=semc";
    $txt[] = 'Fix Global SemClears';

    command_list($act, $txt);
}


function unknown($act)
{
    echo para("unknown $act");
}


function rebuild($db)
{
    GCFG_BuildGConfig($db);
}

/* FixGlobalSemClears

        Forces all machines at the same site to have the same global revl and
        value for the clear side of a semaphore.
    */
function FixGlobalSemClears($db)
{
    $mgroupuniq = '';
    $sql = "SELECT mgroupuniq FROM MachineGroups LEFT JOIN "
        . "MachineCategories ON (MachineGroups.mcatuniq=MachineCategories."
        . "mcatuniq) WHERE MachineCategories.category='Site'";
    $groups = find_many($sql, $db);

    if ($groups) {
        foreach ($groups as $key3 => $thisGroup) {
            $mgroupuniq = $thisGroup['mgroupuniq'];
            echo "Checking " . $thisGroup['mgroupuniq'] . "...";
            $num = 0;
            $sql = "SELECT MAX(revl) AS revl, MAX(valu) AS valu, varuniq "
                . "FROM SemClears WHERE mgroupuniq='" . $mgroupuniq
                . "' GROUP BY varuniq";
            $set = find_many($sql, $db);
            foreach ($set as $key => $row) {
                $revl = $row['revl'];
                $valu = $row['valu'];
                $sql = "UPDATE SemClears SET revl=$revl, valu=$valu "
                    . "WHERE (revl!=$revl OR valu!=$valu) AND mgroupuniq='"
                    . $mgroupuniq . "' AND varuniq='" . $row['varuniq']
                    . "'";
                $sql2 = "SELECT semid FROM SemClears WHERE (revl!="
                    . "$revl OR valu!=$valu) AND mgroupuniq='"
                    . $mgroupuniq . "' AND varuniq='" . $row['varuniq']
                    . "'";

                $set2 = DSYN_DeleteSet(
                    $sql2,
                    constDataSetGConfigSemClears,
                    "semid",
                    "FixGlobalSemClears",
                    0,
                    0,
                    constOperationDelete,
                    $db
                );

                if ($set2) {
                    $res = command($sql, $db);
                    $num = affected($res, $db);
                    if ($num) {
                        $msg = "config: update gsc:" . $row['varuniq']
                            . " $num times in group "
                            . $thisGroup['mgroupuniq'];
                        logs::log(__FILE__, __LINE__, $msg, 0);
                        echo $msg . "<br/>";
                    }
                    DSYN_UpdateSet(
                        $set2,
                        constDataSetGConfigVarValues,
                        "semid",
                        $db
                    );
                }
            }
        }
    }
}

$now  = time();
$db   = db_connect();
$auth = process_login($db);
$comp = component_installed();
$name = 'GConfig Update';
$act  = get_string('act', 'list');

$user  = user_data($auth, $db);
$priv  = @($user['priv_debug']) ? 1 : 0;
$debug = @($user['priv_debug']) ? 1 : 0;
$admin = @($user['priv_admin']) ? 1 : 0;

if (!$admin) {
    $act = 'none';
}

$msg = ob_get_contents();
ob_end_clean();
echo standard_html_header($name, $comp, $auth, '', '', 0, $db);

echo again();

switch ($act) {
    case 'list':
        census($db);
        break;
    case 'menu':
        menu($db);
        break;
    case 'full':
        rebuild($db);
        break;
    case 'none':
        echo "This page requires administrative access.";
        break;
    case 'semc':
        FixGlobalSemClears($db);
        break;
    default:
        unknown($act);
        break;
}

echo again();
echo head_standard_html_footer($auth, $db);
