<?php

/*
Revision history:

Date        Who     What
----        ---     ----
10-Jun-03   EWB     Created.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title  = 'Delete Site';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once('../lib/l-util.php');
include_once('../lib/l-db.php');
include_once('../lib/l-sql.php');
include_once('../lib/l-serv.php');
include_once('../lib/l-rcmd.php');
include_once('../lib/l-user.php');
include_once('../lib/l-alib.php');
include_once('../lib/l-head.php');
include_once('local.php');

function colorspeak($size, $color, $msg)
{
    $face = 'verdana,helvetica';
    return "<font face=\"$face\" color=\"$color\" size=\"$size\">$msg</font>";
}

function newline()
{
    echo "<br>\n";
}


function purge_site($site, $db)
{
    $mids = asset_site($site, $db);
    $qs   = safe_addslashes($site);
    $sql  = "delete from Machine\n";
    $sql .= " where cust='$qs'";
    $res  = redcommand($sql, $db);
    if (($res) && (mysqli_affected_rows($db)) && ($mids)) {
        $txt  = join(',', $mids);
        $sql  = "delete from AssetData\n";
        $sql .= " where machineid in ($txt)";
        $res  = redcommand($sql, $db);
    }
}


/*
    |  Main program
    */

$cid  = intval(get_argument('cid', 0, 0));
$yes  = intval(get_argument('yes', 0, 0));
$dbg  = intval(get_argument('debug', 0, 1));

$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$carr = site_array($authuser, 0, $db);
$user = user_data($authuser, $db);
$cust = '';
if ($cid > 0) {
    $cust = @$carr[$cid];
}

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, '', '', 0, $db);

db_change($GLOBALS['PREFIX'] . 'asset', $db);

$priv_dbg = @($user['priv_debug']) ?  1 : 0;
$priv_ast = @($user['priv_asset']) ? 1 : 0;
$debug    = ($priv_dbg) ? $dbg : 0;

$test_sql = 0;
//  $test_sql = 1;
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

debug_note("debug:$debug, cid:$cid, asset:$priv_ast, test_sql:$test_sql");

if (($cust) && ($priv_ast)) {
    if ($yes) {
        newline();
        newline();
        purge_site($cust, $db);
        newline();
        newline();
        $msg  = "Site $cust has been removed from the database.<br>";
        $msg .= "<br>";
        //          $msg .= "<a href='index.php'>Home</a><br>";
        logs::log(__FILE__, __LINE__, "asset: site $cust removed by $authuser", 0);
    } else {
        $self = server_var('PHP_SELF');
        $yes  = html_link("$self?cid=$cid&yes=1", "Yes delete it");
        $no   = html_link("console.php", "No, don't do anything");
        echo <<< HERE

  This will remove all of the asset information for
  the entire site from the asset database.<br>
  <br>
  Delete site $cust?<br>
  <br>
  $yes.<br>
  <br>
  $no<br>
  <br>

HERE;
    }
    $msg = colorspeak(2, 'black', $msg);
} else {
    if ($priv_ast)
        $msg = "No site specified to be deleted.";
    else
        $msg = "Authorization denied.";
    $msg = colorspeak(3, 'red', $msg);
}

echo "<p>$msg</p>\n";

newline();
newline();

echo head_standard_html_footer($authuser, $db);
