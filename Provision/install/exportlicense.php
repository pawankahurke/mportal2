<?php
/*
  Revision history:

  Date        Who     What
  ----        ---     ----
  15-Apr-19   JHN     Creation.

 */

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-slct.php');
include('../lib/l-user.php');
include('../lib/l-head.php');
include('header.php');
include('../lib/l-errs.php');
include('../lib/l-cnst.php');

function newlines($n)
{
    for ($i = 0; $i < $n; $i++) {
        echo "<br>\n";
    }
}

function table_header()
{
    echo "\n<table border='0' align='left' cellspacing='0' cellpadding='6'>\n";
}

function table_footer()
{
    echo "\n</table>\n";
    echo "<br clear='all'>\n";
}

function table_data($args, $head, $align = 'center')
{
    $td = ($head) ? "th" : "td";
    if (safe_count($args)) {
        echo "<tr valign=top>\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo "<$td align=$align>$data</$td>\n";
        }
        echo "</tr>\n";
    }
}


/*
  |  Main program
 */

$db = db_connect();
db_change($GLOBALS['PREFIX'] . 'install', $db);

$authuser = install_login($db);
$authuserdata = install_user($authuser, $db);
$priv_admin = @($authuserdata['priv_admin']) ? 1 : 0;
$priv_servers = @($authuserdata['priv_servers']) ? 1 : 0;

$comp = component_installed();

$title = 'Import User License Key';

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo install_html_header($title, $comp, $authuser, $priv_admin, $priv_servers, $db);
if (trim($msg))
    debug_note($msg);   // ...display any errors to debug users

$referer = server_var('HTTP_REFERER');

newlines(1);

table_header();
$args = array();
$args[] = "<form method='post' action='$referer'>" .
    "<input type='submit' value='Cancel'></form>";
$args[] = "<form method='post' action='user-act.php' name='myForm'>\n" .
    "<input type='hidden' name='action' value='importuserlicense'>\n" .
    "<input type='submit' value='Import'>";
table_data($args, 0, 'left');
table_footer();

table_header();
$args = array('<b>License Key:</b> ', "<textarea id='licensekey' name='licensekey' style='width:500px; height:150px; resize:none;'></textarea>");
table_data($args, 0, 'left');

table_footer();

newlines(1);

table_header();
$args = array();
$args[] = "<input type='submit' value='Import'></form>";
$args[] = "</form><form method='post' action='$referer'>" .
    "<input type='submit' value='Cancel'></form>";
table_data($args, 0, 'left');
table_footer();

echo head_standard_html_footer($authuser, $db);
