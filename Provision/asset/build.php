<?php

/*
Revision history:

Date        Who     What
----        ---     ----
19-Sep-02   EWB     Created
20-Sep-02   EWB     Giant Refactoring.
 4-Dec-02   EWB     Reorginization Day
10-Dec-02   EWB     Local Navigation
10-Dec-02   EWB     Factored build_asset_tables into library.
10-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change()
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
 6-May-03   EWB     Update user calculation.
15-Dec-05   BJS     Added l-cnst.php
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title = 'Rebuild Asset Database';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-head.php');
include('../lib/l-rcmd.php');
include('../lib/l-abld.php');
include('../lib/l-user.php');
include('../lib/l-cnst.php');
include('local.php');


function newline()
{
    echo "<br>\n";
}

function fontcolor($size, $color, $msg)
{
    $face = "verdana,helvetica";
    return "<font face='$face' color='$color' size='$size'>$msg</font>";
}


function table_exists($dbname, $table, $db)
{
    $exists = 0;
    $res = mysqli_query($db, "SHOW TABLES FROM `$dbname`");
    if ($res) {
        $n = mysqli_num_rows($res);
        for ($i = 0; $i < $n; $i++) {
            $name = ((mysqli_data_seek($res, $i) && (($___mysqli_tmp = mysqli_fetch_row($res)) !== NULL)) ? array_shift($___mysqli_tmp) : false);
            if ($name == $table) {
                $exists = 1;
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $exists;
}


function drop_table($dbname, $table, $db)
{
    if (table_exists($dbname, $table, $db)) {
        $sql = "drop table $table";
        redcommand($sql, $db);
    }
}

function rebuild_asset($dbname, $user, $db)
{
    db_change($dbname, $db);
    $tables = array('Machine', 'DataName', 'AssetData');
    reset($tables);
    foreach ($tables as $key => $data) {
        drop_table($dbname, $data, $db);
    }
    build_asset_tables(constTableTypeStatic,  constTableAssetData, $db);
    $real = @$GLOBALS['real_sql'];
    if ($real) {
        $msg = "asset database rebuilt by $user";
        logs::log(__FILE__, __LINE__, $msg, 0);
        debug_note($msg);
    }
}


/*
    |  Main program
    */

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$confirm  = intval(get_argument('confirm', 0, 0));

$user = user_data($authuser, $db);

$priv_admin = @($user['priv_admin']) ? 1 : 0;
$priv_debug = @($user['priv_debug']) ? 1 : 0;

/*
    |  The $real_sql flag is just so we can temporarily
    |  make a harmless copy of this file to experiment
    |  with, which will automatically become active when
    |  it is remaned to it's official name.
    */

$file = $comp['file'];
$self = $comp['self'];

$test_sql = ('build.php' == $file) ? 0 : 1;
$show_sql = $priv_debug;
$debug    = $priv_debug;
$real_sql = (!$test_sql);


$dbname = 'asset';
$dbname = (getenv('DB_PREFIX') ?: '') . 'core';

if ($priv_admin) {
    if ($confirm) {
        newline();
        newline();
        newline();

        rebuild_asset($dbname, $authuser, $db);

        $msg  = "The asset database has been rebuilt.<br>\n";
        $msg .= "<br>";
        $msg .= "<br>\n";
        $msg .= "<a href='index.php'>Home</a><br>\n";
    } else {
        $y = "$self?confirm=1";
        $n = "index.php";
        $p = "purge.php";

        $yes     = "<a href='$y'>Yes, go ahead.</a>";
        $no      = "<a href='$n'>No, don't do anything</a>";
        $purging = "<a href='$p'>purging</a>";
        /*
 |  This will remove the current machine asset database and
 |  rebuild all of the database tables.  All of the current
 |  database values would be lost.  If you don't need to rebuild
 |  the tables, you might consider purging the database instead.
 */
        $msg  = "This will remove the current machine asset ";
        $msg .= "database and<br>rebuild all of the database ";
        $msg .= "tables.&nbsp;&nbsp;All of the current<br>database ";
        $msg .= "values would be lost.&nbsp;&nbsp;If you don't need ";
        $msg .= "to rebuild<br>the tables, you might consider ";
        $msg .= "$purging the database instead.<br>";
        $msg .= "<br>";
        $msg .= "Would you like to rebuild the machine asset database?<br>";
        $msg .= "<br>";
        $msg .= "$yes<br>";
        $msg .= "<br>";
        $msg .= "$no<br>";
    }
} else {
    $msg  = "This operation requires administative access.<br>";
    $msg .= "<br>";
    $msg .= "Permission denied.";
    $msg .= "<br>";
    $msg .= "<a href='index.php'>Home</a><br>";
}

$msg = fontcolor(2, 'black', $msg);
echo "<p>$msg</p>\n";

echo head_standard_html_footer($authuser, $db);
