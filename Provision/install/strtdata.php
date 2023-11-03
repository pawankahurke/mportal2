<?php

/*
Revision history:

Date        Who     What
----        ---     ----
18-May-03   NL      Creation.
29-May-03   NL      install_html_header(): pass $priv_servers (to display servers link).
02-Jun-03   NL      Turn delay into days, hours, and minutes.
02-Jun-03   NL      Include the Scrip numbers in the checkbox list.
02-Jun-03   NL      Call install_html_footer (has its own version).
02-Jun-03   NL      Dynamic title.
03-Jun-03   NL      Check All \ Uncheck All.
03-Jun-03   NL      Bold Labels.
04-Jun-03   NL      Change title.
06-Jun-03   NL      Oops. get_argument('action','add',0) --> get_argument('action',0,'add').
16-Jun-03   NL      Change include line: '../lib/l-head.php' --> 'header.php'.
23-Jul-03   NL      Change footer to standard_html_footer().
31-Jul-03   EWB     Uses install_login($db);
15-Sep-03   NL      Direct help buttons to corresponding help page.
 8-Oct-03   NL      Cleaned up long lines.
29-Mar-04   EWB     Added special CBE hack.
 6-May-05   EWB     Uses gconfig database.
17-Mar-06   BTE     Bug 3212: Install server cannot configure Scrip
                    configurations with 4.3 database.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.
03-Oct-08   BTE     Bug 4828: Change customization feature of server.

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

function get_option_data($id, $db)
{
    $optiondata = array();
    $sql  = "SELECT * FROM Startupnames WHERE startupnameid = $id";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $optiondata['startup'] = $row['startup'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $optiondata;
}

function JS_CheckUncheckAll()
{
?>
    <SCRIPT LANGUAGE="JavaScript">
        function CheckUncheckAll(all_setting) {
            var check;
            if (all_setting == "check")
                check = true;
            else if (all_setting == "uncheck")
                check = false;

            var checkboxes = window.document.myForm.scrips;
            for (i = 0; i < checkboxes.length; i++)
                checkboxes[i].checked = check;
            return true;
        }
    </script>
<?php
}

function HTML_CheckUncheckAll($cols)
{
    $self = server_var('PHP_SELF');

    $querystring = server_var('QUERY_STRING');
    $querystring = preg_replace("/all_setting=(check|uncheck)&?/", "", $querystring);
    if (strlen($querystring))
        $querystring = '&' . $querystring;
    $colspan = $cols * 3;

    $html = "    [<a href=\"$self?all_setting=check$querystring\"\n" .
        "        onClick=\"CheckUncheckAll('check');return false;\">check all</a> |\n" .
        "     <a href=\"$self?all_setting=uncheck$querystring\"\n" .
        "        onClick=\"CheckUncheckAll('uncheck');return false;\">uncheck all</a>]<br>\n";

    return $html;
}


function box($name, $id, $checked)
{
    $state = ($checked) ? ' checked' : '';
    $box = "<input type=\"checkbox\" name=\"scrips[]\" id=\"scrips\" value=\"$id\"$state>";
    return $box . "Scrip $id: $name<br>\n";
}


function gen_scrip_cboxes($action, $id, $all_setting, $auth, $db)
{
    $CHECKEDscrips  = array();
    $scrips         = array();
    $cboxes         = '';

    // Get currently selected scrips
    if (($action != 'add') && ($id > 0)) {
        $sql = "SELECT * FROM Startupscrips WHERE startupnameid=$id";
        $res  = redcommand($sql, $db);
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $CHECKEDscrips[] = $row['scrip'];
            }
        }
    }

    // Get all the scrips from the core database

    $sql = "SELECT MAX(vers) AS version FROM " . $GLOBALS['PREFIX'] . "core.Scrips";
    $res  = redcommand($sql, $db);
    if ($res) {
        $qv = 'Invalid';
        if (mysqli_num_rows($res)) {
            $row = mysqli_fetch_array($res);
            $qv  = safe_addslashes($row['version']);
        }
        $sql = "SELECT num,name FROM " . $GLOBALS['PREFIX'] . "core.Scrips WHERE vers = '$qv' ORDER BY num";
        $res = command($sql, $db);
        if ($res) {
            if (mysqli_num_rows($res)) {
                $cboxes .= HTML_CheckUncheckAll(1);
                while ($row = mysqli_fetch_array($res)) {
                    $scripid    = $row['num'];
                    $scripname  = $row['name'];
                    $scrips[$scripid] = $scripname;
                }
                reset($scrips);
                foreach ($scrips as $id => $name) {
                    if ($all_setting == 'check')
                        $checked = true;
                    elseif ($all_setting == 'uncheck')
                        $checked = false;
                    else
                        $checked = (in_array($id, $CHECKEDscrips)) ? true : false;

                    $cboxes .= box($name, $id, $checked);
                }

                /*
                    |  Scrip 235 is only for or used by CBE.
                    */

                if (($auth == 'hfn') || ($auth == 'cbe')) {
                    $checked = true;
                    if ($all_setting == 'check')
                        $checked = true;
                    elseif ($all_setting == 'uncheck')
                        $checked = false;
                    $name    = 'Software Update Management';
                    $cboxes .= box($name, 235, $checked);
                }
                $cboxes .= HTML_CheckUncheckAll(1);
            }
        }
    }
    return $cboxes;
}



/*
    |  Main program
    */

$db = db_connect();
db_change($GLOBALS['PREFIX'] . 'install', $db);

$authuser       = install_login($db);
$authuserdata   = install_user($authuser, $db);
$priv_admin     = @($authuserdata['priv_admin'])  ? 1 : 0;
$priv_servers   = @($authuserdata['priv_servers']) ? 1 : 0;

$comp = component_installed();

$action = get_string('action', 'add');
$id     = get_integer('id', 0);
$title   = ucwords($action) . ' Scrip Configuration';
$all_setting = get_argument('all_setting', 0, '');
$helpfile   = ($action == 'add') ? 'strtadd.php' : 'strtedit.php';

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo install_html_header($title, $comp, $authuser, $priv_admin, $priv_servers, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users


$referer = server_var('HTTP_REFERER');
$submit  = ($action == 'add') ? 'Enter' : 'Update';

if ($action != 'add')
    $optiondata = get_option_data($id, $db);
$startup    = ($action == 'add') ? '' : $optiondata['startup'];

JS_CheckUncheckAll();
newlines(1);

table_header();
$args = array();
$args[] = "<form method='post' action='$referer'>" .
    "<input type='submit' value='Cancel'></form>";
$args[] = "<form method='post' action='help/$helpfile' target='help'>" .
    "<input type='submit' value='Help'></form>";
$args[] = "<form method='post' action='strt-act.php' name='myForm'>\n" .
    "<input type='hidden' name='action' value='$action'>\n" .
    "<input type='hidden' name='id' value='$id'>\n" .
    "<input type='submit' value='$submit'>";
table_data($args, 0, 'left');
table_footer();

table_header();
$args = array('<b>Name:</b> ', "<input type='text' name='startup' value=\"$startup\">");
table_data($args, 0, 'left');
$box  = gen_scrip_cboxes($action, $id, $all_setting, $authuser, $db);
$args = array('<b>Scrips:</b> ', $box);
table_data($args, 0, 'left');
table_footer();

newlines(1);

table_header();
$args   = array();
$args[] = "<input type='submit' value='$submit'></form>";
$args[] = "</form><form method='post' action='$referer'>" .
    "<input type='submit' value='Cancel'></form>";
$args[] = "<form method='post' action='help/$helpfile' target='help'>" .
    "<input type='submit' value='Help'></form>";
table_data($args, 0, 'left');
table_footer();

echo head_standard_html_footer($authuser, $db);
?>