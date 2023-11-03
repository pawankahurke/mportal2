<?php




$title = 'Site Selection';

ob_start();
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-rprt.php');
include('../lib/l-user.php');
include('../lib/l-cust.php');
include('../lib/l-slct.php');
include('../lib/l-sitflt.php');
include('../lib/l-head.php');


function html_header($title, $db)
{
    $gmtime = gmdate('D, d M Y H:i:s', time());
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: $gmtime GMT");
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    $refreshtime = global_def('refreshtime', '');

    $agent    = server_var('HTTP_USER_AGENT');
    $netscape = !strstr($agent, 'compatible');
?>
    <!DOCTYPE html>
    <html lang="en" xml:lang="en">

    <head>
        <?php echo $refreshtime ?>

        <title><?php echo $title ?></title>

        <style>
            BODY,
            TD,
            TH {
                font-family: Verdana, Helvetica, sans-serif;
                font-size: smaller;
            }

            .blue {
                font-family: Verdana, Helvetica, sans-serif;
                color: 333399;
            }

            .red {
                font-family: Verdana, Helvetica, sans-serif;
                color: FF0000;
            }

            .heading {
                font-family: Verdana, Helvetica, sans-serif;
                font-size: <?php echo $netscape ? "larger" : "medium" ?>;
                color: 333399;
            }

            .footnote {
                font-family: Verdana, Helvetica, sans-serif;
                font-size: <?php echo $netscape ? "x-small" : "xx-small" ?>;
            }

            .faded {
                font-family: Verdana, Helvetica, sans-serif;
                color: #666666
            }

            .hide {
                position: absolute;
                top: 200;
                left: 250;
                visibility: hidden
            }
        </style>
    </head>

<?php
}

function JS_resize_window()
{
?>
    <script language="javascript">
        // <!--
        function resizeWindow(count) {
            if (count > 10) {
                var h = screen.height;
                window.resizeTo(300, h);
            }
        }
        // -->
    </script>
<?php
}



function bigbluetext($msg)
{
    return "<font face='verdana,helvetica' size='3' color='#333399'>$msg</font>\n";
}

function mark($name)
{
    echo "<a name='$name'></a>\n";
}

function marklink($link, $text)
{
    return "<a href='$link'>$text</a>\n";
}


function brace()
{
    return "&nbsp;|&nbsp;";
}



function jumptable($tags, $admin)
{
    $args = explode(',', $tags);
    $n = safe_count($args);
    if ($n > 0) {
        $msg = '';
        for ($i = 0; $i < $n; $i++) {
            $name = $args[$i];
            $link = marklink("#$name", $name);
            if ($i) $msg .= brace();
            $msg .= $link;
        }
        echo fontspeak("<p>[ $msg ]</p>");
    }
}

function newlines($n)
{
    for ($i = 0; $i < $n; $i++) {
        echo "<br>\n";
    }
}

function table_data($args, $head)
{
    $td = ($head) ? "th" : "td";
    if (safe_count($args)) {
        echo "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo "<$td>\n$data\n</$td>\n";
        }
        echo "</tr>\n";
    }
}

function span_data($n, $msg)
{
    $msg = "<tr><td colspan='$n'>$msg</td></tr>\n";
    return $msg;
}

function table_header()
{
    echo "\n<table border='0' align='left' cellspacing='2' cellpadding='2'>\n";
}

function table_footer()
{
    echo "\n</table>\n";
    echo "<br clear='all'>\n";
}

function JS_close_window()
{
?>
    <script language="javascript">
        // <!--
        window.close();
        // -->
    </script>
<?php
}

function PHP_CheckUncheckAll($all_setting, $objecttype, $objectid, $authuser, $db)
{
    $objectvars = get_obj_vars($objecttype);
    $idfield    = $objectvars['idfield'];
    $auxtable   = $objectvars['auxtable'];

    $sql = "DELETE FROM $auxtable WHERE\n";
    $sql .= " $idfield=$objectid\n";

    $res = redcommand($sql, $db);
    if ($res) {

        $sitefilter = get_obj_sitefilter($objecttype, $objectid, $authuser, $db);

        $newfilter = ($all_setting == "check") ? 1 : 0;

        reset($sitefilter);
        foreach ($sitefilter as $site => $oldfilter) {
            $sql = "INSERT INTO $auxtable SET\n";
            $sql .= " $idfield=$objectid,\n";
            $sql .= " site='" . safe_addslashes($site) . "',\n";
            $sql .= " filter=" . $newfilter . "\n";
            $res = redcommand($sql, $db);
        }
        if ($res) {
            $good = 1;
        } else {
            sqlerror($sql, $db);
        }
    } else {
        sqlerror($sql, $db);
    }
}




$db = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$msg = ob_get_contents();
ob_end_clean();
echo html_header($title, $db);

$all_setting    = get_argument('all_setting', 0, '');
$sites          = get_argument('sites', 0, array());
$sitesCHECKED   = get_argument('sitesCHECKED', 0, array());
$submit         = get_argument('submit', 0, '');
$objecttype     = get_argument('objecttype', 0, '');
$objectid       = get_argument('objectid', 0, 0);

$objectvars     = get_obj_vars($objecttype);
$dbname         = $objectvars['dbname'];
db_change($dbname, $db);


if (strlen($submit)) {
    $sitefilter = checkboxes2sitefilter($sites, $sitesCHECKED);
    update_obj_sitefilter($objecttype, $objectid, $sitefilter, $db);
    JS_close_window();
    echo "Your site selection has been updated.<br><br>";
    echo "You may close this window and return to the main window.";
} else {
    if (strlen($all_setting)) {
        PHP_CheckUncheckAll($all_setting, $objecttype, $objectid, $authuser, $db);
    }

    $sitefilter = get_obj_sitefilter($objecttype, $objectid, $authuser, $db);

    if ($count = safe_count($sitefilter)) {
        JS_resize_window();
        echo "<script language='javascript'>resizeWindow($count)</script>\n";
        echo "<body>\n";
        echo "<form name='myForm' method='post' action='l-sitpop.php'>\n";
        echo "<input type='hidden' name='objecttype' value='" . $objecttype . "'>\n";
        echo "<input type='hidden' name='objectid' value='" . $objectid . "'>\n";
        echo "<input type='submit' name='submit' value='Update'><br><br>\n";
        show_sitefilterlist($objecttype, $sitefilter, $all_setting, 1, $db);
        echo "<br><input type='submit' name='submit' value='Update'>\n";
        echo "</form>\n";
    } else {
        $msg = "There are currently no sites accessible by user <b>$authuser</b>.";
        $msg = bigbluetext($msg);
        echo "<p>$msg</p>";
    }
}

?>