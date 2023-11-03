<?php

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once('../lib/l-util.php');
include_once('../lib/l-db.php');
include_once('../lib/l-sql.php');
include_once('../lib/l-serv.php');
include_once('../lib/l-rcmd.php');
include_once('../lib/l-user.php');
include_once('../lib/l-jump.php');
include_once('../lib/l-base.php');
include_once('../lib/l-tabs.php');
include_once('../lib/l-form.php');
include_once('../lib/l-gsql.php');
include_once('../lib/l-head.php');
include_once('../lib/l-errs.php');
include_once('../lib/l-cnst.php');

define('constDBNVarVersions',   0);
define('constDBNVarValues',     1);
define('constDBNValueMap',      2);
define('constDBNScrips',        3);
define('constDBNSemClears',     4);

function get_query($code, $row, $importvers)
{
    switch ($code) {
        case constDBNVarValues:
            return "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DBN_VarValues (mgroupuniq, mcatuniq, varuniq, varscopuniq, "
                . "varnameuniq, valu, revl, def, revldef, clientconf, revlclientconf, last, "
                . "host, scop, name, seminit, cksum) VALUES ("
                . "'" . safe_addslashes($row['mgroupuniq']) . "',"
                . "'" . safe_addslashes($row['mcatuniq']) . "',"
                . "'" . safe_addslashes($row['varuniq']) . "',"
                . "'" . safe_addslashes($row['varscopuniq']) . "',"
                . "'" . safe_addslashes($row['varnameuniq']) . "',"
                . "'" . safe_addslashes($row['valu']) . "',"
                . $row['revl'] . ","
                . $row['def'] . ","
                . $row['revldef'] .  ","
                . $row['clientconf'] . ","
                . $row['revlclientconf'] . ","
                . $row['last'] . ","
                . "'" . safe_addslashes($row['host']) . "',"
                . $row['scop'] . ","
                . "'" . safe_addslashes($row['name']) . "',"
                . $row['seminit'] . ","
                . "'" . safe_addslashes($importvers) . "')"; //copy everything except the cksum column
        case constDBNVarVersions:
            return "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DBN_VarVersions (vers, varuniq, varscopuniq, varnameuniq, "
                . "descval, pwsc, dngr, defval, config, configorder, grpany, grpall, grpuser, "
                . "grpsite, grpmach, varversuniq, cksum) VALUES ("
                . "'" . safe_addslashes($row['vers']) . "',"
                . "'" . safe_addslashes($row['varuniq']) . "',"
                . "'" . safe_addslashes($row['varscopuniq']) . "',"
                . "'" . safe_addslashes($row['varnameuniq']) . "',"
                . "'" . safe_addslashes($row['descval']) . "',"
                . $row['pwsc'] . ","
                . $row['dngr'] . ","
                . "'" . safe_addslashes($row['defval']) . "',"
                . $row['config'] . ","
                . $row['configorder'] .  ","
                . $row['grpany'] . ","
                . $row['grpall'] . ","
                . $row['grpuser'] . ","
                . $row['grpsite'] . ","
                . $row['grpmach'] . ","
                . "'" . safe_addslashes($row['varversuniq']) . "',"
                . "'" . safe_addslashes($importvers) . "')"; //copy everything except the cksum column
        case constDBNValueMap:
            return "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DBN_ValueMap (censusuniq, censussiteuniq, mgroupuniq, "
                . "mcatuniq, varuniq, varscopuniq, varnameuniq, stat, srev, revl, last, "
                . "expire, oldmgroupuniq, oldvalu, source, cksum) VALUES ("
                . "'" . safe_addslashes($row['censusuniq']) . "',"
                . "'" . safe_addslashes($row['censussiteuniq']) . "',"
                . "'" . safe_addslashes($row['mgroupuniq']) . "',"
                . "'" . safe_addslashes($row['mcatuniq']) . "',"
                . "'" . safe_addslashes($row['varuniq']) . "',"
                . "'" . safe_addslashes($row['varscopuniq']) . "',"
                . "'" . safe_addslashes($row['varnameuniq']) . "',"
                . $row['stat'] . ","
                . $row['srev'] . ","
                . $row['revl'] . ","
                . $row['last'] .  ","
                . $row['expire'] . ","
                . "'" . safe_addslashes($row['oldmgroupuniq']) . "',"
                . "'" . safe_addslashes($row['oldvalu']) . "',"
                . $row['source'] . ","
                . "'" . safe_addslashes($importvers) . "')"; //copy everything except the cksum column
        case constDBNScrips:
            return "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DBN_Scrips (num, name, vers, cksum) VALUES ("
                . $row['num'] . ","
                . "'" . safe_addslashes($row['name']) . "',"
                . "'" . safe_addslashes($row['vers']) . "',"
                . "'" . safe_addslashes($importvers) . "')"; //copy everything except the cksum column
        case constDBNSemClears:
            return "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DBN_SemClears (censusuniq, censussiteuniq, mgroupuniq, "
                . "mcatuniq, varuniq, varscopuniq, varnameuniq, valu, revl, last, cksum) VALUES ("
                . "'" . safe_addslashes($row['censusuniq']) . "',"
                . "'" . safe_addslashes($row['censussiteuniq']) . "',"
                . "'" . safe_addslashes($row['mgroupuniq']) . "',"
                . "'" . safe_addslashes($row['mcatuniq']) . "',"
                . "'" . safe_addslashes($row['varuniq']) . "',"
                . "'" . safe_addslashes($row['varscopuniq']) . "',"
                . "'" . safe_addslashes($row['varnameuniq']) . "',"
                . $row['valu'] . ","
                . $row['revl'] . ","
                . $row['last'] . ","
                . "'" . safe_addslashes($importvers) . "')";
    }
}

function do_cleanup($importvers, $verboseoutput, $db)
{
    /* The import failed in some way, delete anything that was added */
    redcommand("DELETE FROM " . $GLOBALS['PREFIX'] . "core.DBN_Versions WHERE vers='" . safe_addslashes($importvers) . "'", $db);

    redcommand("DELETE FROM " . $GLOBALS['PREFIX'] . "core.DBN_VarVersions WHERE cksum='" . safe_addslashes($importvers) . "'", $db);
    redcommand("DELETE FROM " . $GLOBALS['PREFIX'] . "core.DBN_VarValues WHERE cksum='" . safe_addslashes($importvers) . "'", $db);
    redcommand("DELETE FROM " . $GLOBALS['PREFIX'] . "core.DBN_ValueMap WHERE cksum='" . safe_addslashes($importvers) . "'", $db);
    redcommand("DELETE FROM " . $GLOBALS['PREFIX'] . "core.DBN_Scrips WHERE cksum='" . safe_addslashes($importvers) . "'", $db);
    redcommand("DELETE FROM " . $GLOBALS['PREFIX'] . "core.DBN_SemClears WHERE cksum='" . safe_addslashes($importvers) . "'", $db);

    echo "<br/>";
    foreach ($verboseoutput as $line) {
        echo $line;
    }
}

function process_table(
    $sqlitequery,
    $code,
    &$verboseoutput,
    $tblname,
    $db,
    $dbsqlite,
    $importvers
) {

    logs::log("process_table:", ["sqlitequery" => $sqlitequery, "code" => $code, "tblname" => $tblname]);
    $query = $dbsqlite->query($sqlitequery);
    if (!$query) {
        echo "Failed to select from $tblname: " . $dbsqlite->lastErrorMsg();
        return;
    }

    $countInserts = 0;
    $countFailed = 0;
    while ($row = $query->fetchArray()) {
        $sql = get_query($code, $row, $importvers);
        logs::log("process_table: " . $sql);
        $res = command($sql, $db);
        $count = affected($res, $db);
        $countInserts += $count;
        $vout = "<br/>" . $sql . "<br/>";
        if ($count == 0) {
            $vout = '<br/>Warning: sql failed to insert a row to var values: ' . $sql
                . '<br/>' . mysqli_error($GLOBALS["___mysqli_ston"]) . '<br/>';
            $countFailed++;
        }
        $verboseoutput[] = $vout;
    }

    echo "<br/>Inserted $countInserts into DBN_$tblname, $countFailed insert(s) failed...<br/>";
    return $countFailed;
}

function import_vars($env, $db)
{
    try {
        echo "Attempting to open " . $env['dbpath'] . "...<br/>";
        $dbsqlite = new SQLite3($env['dbpath'], SQLITE3_OPEN_READONLY);
        $importvers = get_string('vers', '');

        $sql = "select vers from " . $GLOBALS['PREFIX'] . "core.DBN_Versions WHERE vers='"
            . safe_addslashes($importvers) . "'";
        $set = find_one($sql, $db);
        if ($set) {
            echo "The version in core.dbn, " . $importvers . ", has already been imported.";
            return;
        }

        echo "Database opened, now importing $importvers<br/>";

        $verboseoutput = array();

        if (process_table(
            "SELECT * FROM VarVersions WHERE vers='" . $importvers . "'",
            constDBNVarVersions,
            $verboseoutput,
            'VarVersions',
            $db,
            $dbsqlite,
            $importvers
        ) > 0) {
            echo "<br/>Import failed, aborting.<br/>";
            do_cleanup($importvers, $verboseoutput, $db);
            return;
        }
        if (process_table(
            "SELECT * FROM VarValues",
            constDBNVarValues,
            $verboseoutput,
            'VarValues',
            $db,
            $dbsqlite,
            $importvers
        ) > 0) {
            echo "<br/>Import failed, aborting.<br/>";
            do_cleanup($importvers, $verboseoutput, $db);
            return;
        }
        if (process_table(
            "SELECT * FROM ValueMap",
            constDBNValueMap,
            $verboseoutput,
            'ValueMap',
            $db,
            $dbsqlite,
            $importvers
        ) > 0) {
            echo "<br/>Import failed, aborting.<br/>";
            do_cleanup($importvers, $verboseoutput, $db);
            return;
        }
        if (process_table(
            "SELECT * FROM Scrips",
            constDBNScrips,
            $verboseoutput,
            'Scrips',
            $db,
            $dbsqlite,
            $importvers
        ) > 0) {
            echo "<br/>Import failed, aborting.<br/>";
            do_cleanup($importvers, $verboseoutput, $db);
            return;
        }
        if (process_table(
            "SELECT * FROM SemClears",
            constDBNSemClears,
            $verboseoutput,
            'SemClears',
            $db,
            $dbsqlite,
            $importvers
        ) > 0) {
            echo "<br/>Import failed, aborting.<br/>";
            do_cleanup($importvers, $verboseoutput, $db);
            return;
        }

        echo "<br/>Import succeeded, making version available to new clients...";
        redcommand("INSERT INTO " . $GLOBALS['PREFIX'] . "core.DBN_Versions (vers) VALUES ('"
            . safe_addslashes($importvers) . "')", $db);

        echo "<br/>";
        foreach ($verboseoutput as $line) {
            echo $line;
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function inspect_vars($env, $db)
{
    echo "Inspecting core.dbn variables...<br/>";
    try {
        echo "Attempting to open " . $env['dbpath'] . "...<br/>";
        $dbsqlite = new SQLite3($env['dbpath'], SQLITE3_OPEN_READONLY);
        echo "Database opened.<br/>";
        $query = $dbsqlite->query("SELECT vers, count(*) as num FROM VarVersions GROUP BY vers");
        if (!$query) {
            echo "Failed to select from VarVersions: " . $dbsqlite->lastErrorMsg();
            return;
        }
        while ($row = $query->fetchArray()) {
            $tables = array('VarVersions', 'VarValues', 'ValueMap', 'Scrips', 'SemClears');

            foreach ($tables as $table) {
                $sql = "select count(*) as num from " . $GLOBALS['PREFIX'] . "core.DBN_$table where cksum='"
                    . safe_addslashes($row['vers']) . "'";
                $set = find_one($sql, $db);
                if ($set) {
                    echo "Found " . $set['num'] . " variable(s) in server DBN_$table table for " . $row['vers'] . "<br/>";
                }
            }

            $sql = "select vers from " . $GLOBALS['PREFIX'] . "core.DBN_Versions WHERE vers='"
                . safe_addslashes($row['vers']) . "'";
            $set = find_one($sql, $db);
            if ($set) {
                echo "The version " . $row['vers'] . " has already been imported.";
            } else {
                echo "<br/>Import <a href='dbn.php?action=import&vers="
                    . rawurlencode($row['vers'])
                    . "'>" . $row['vers'] . "</a> (" . $row['num']
                    . " variables)<br/>";
            }
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}


/*
    |  Main program
    */

$dbpath = '/dbn/core.dbn';

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$user   = user_data($authuser, $db);
$padm   = @($user['priv_admin']) ? 1 : 0;
$pdbg   = @($user['priv_debug']) ? 1 : 0;

$id     = get_integer('id', 0);
$dbg    = get_integer('debug', 1);
$priv   = get_integer('priv', 1);
$action = get_string('action', 'display');
$name   = get_string('name', '');
$value  = get_string('value', '');

$admin  = ($padm) ? $priv : 0;
$debug  = ($pdbg) ? $dbg  : 0;
$title  = 'Client Variable Import';

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$env = array();
$env['id'] = $id;
$env['valu'] = $value;
$env['name'] = $name;
$env['acts'] = $action;
$env['pdbg'] = $pdbg;
$env['padm'] = $pdbg;
$env['self'] = server_var('PHP_SELF');
$env['args'] = server_var('QUERY_STRING');
$env['dbpath'] = $dbpath;

if (!$admin) {
    $action = 'none';
}
switch ($action) {
    case 'import':
        import_vars($env, $db);
        break;
    case 'none':
        echo "You need to be an admin to use this page.";
    default:
        inspect_vars($env, $db);
        break;
}
echo head_standard_html_footer($authuser, $db);
