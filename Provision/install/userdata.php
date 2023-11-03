<?php

/*
  Revision history:

  Date        Who     What
  ----        ---     ----
  15-May-03   NL      Creation.
  28-May-03   NL      Increase messagetext field height to 10.
  29-May-03   NL      Typo: priviledges -> privileges.
  29-May-03   NL      JS_HideShow emailbounce,urldownload,messagetext only for admin user.
  29-May-03   NL      Increase form field size for emailbounce & urldownload.
  29-May-03   NL      install_html_header(): pass $priv_servers (to display servers link).
  29-May-03   NL      default action is edit, for when non-admin user clicks on user navbar link.
  29-May-03   NL      Oops. $userdata['priv_servers'] NOT $userdata['priv_serv'].
  01-Jun-03   NL      Change label: Name -> User Name
  02-Jun-03   NL      Call install_html_footer (has its own version).
  02-Jun-03   NL      Dynamic title.
  03-Jun-03   NL      Add default site-related fields, re-label fields.
  04-Jun-03   NL      Change "Instructional message" --> "Email distribution message".
  04-Jun-03   NL      Change all labels to sentence case.
  16-Jun-03   NL      Change include line: '../lib/l-head.php' --> 'header.php'.
  24-Jun-03   NL      Limit admin view.
  15-Jul-03   NL      Add subject, sender, extra headers fields.
  15-Jul-03   NL      Remove links: edit server, edit scrip configs.
  21-Jul-03   NL      Add maxlength='255' to email form fields; rearrange order.
  23-Jul-03   NL      Change footer to standard_html_footer().
  24-Jul-03   NL      Add action as hidden element in "Help" form.
  27-Jul-03   NL      rename Destination email -> Logging email address
  28-Jul-03   NL      Remove JS show/hide bug; only show email for non-admin user.
  28-Jul-03   NL      Create blue subheadings: CLIENT SETUP & EMAIL DISTRIBUTION.
  31-Jul-03   EWB     Uses install_login($db);
  8-Aug-03   NL      get_servers(): get global ("Available to all" servers).
  28-Aug-03   NL      Use htmlentities() around all email addresses to accomodate quotes.
  28-Aug-03   NL      Capitalize scrip --> Scrip.
  2-Sep-03   NL      Add Checkbox "Propagate changes in default values to existing sites".
  15-Sep-03   NL      Direct help buttons to corresponding help page.
  29-Sep-03   NL      Add "View" action/page; make "view" the default action.
  6-Oct-03   NL      Fix misspelling: Propogate --> Propagate.
  8-Oct-03   NL      Change action if/else statements to switch statements.
  8-Oct-03   NL      Add some function descriptions.
  23-Oct-03   NL      BACK OUT: Add "View" action/page; make "view" the default action.
  09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
  19-Jun-07   AAM     Handle new database columns for automation.
  27-Jun-07   AAM     Added user values for automation.
  03-Oct-08   BTE     Bug 4828: Change customization feature of server.
  25-Oct-08   AAM     Bug 4823: backed out "automation" related changes.

  07-Mar-19   JHN     Adding new fields for the Service Bot Integration.
 */

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-cnst.php');
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-slct.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('../lib/l-head.php');
include('header.php');
include('../lib/l-errs.php');
include('../lib/l-svbt.php');

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

function table_data($args, $head, $align = 'center', $DHTMLid = '', $nowrapcol = 0)
{
    $i = 1;
    $idstr = (strlen($DHTMLid)) ? "id = '$DHTMLid'" : "";
    $td = ($head) ? "th" : "td";
    if (safe_count($args)) {
        echo "<tr $idstr valign=top align=$align> \n";
        reset($args);
        foreach ($args as $key => $data) {
            $nowrap = ($i == $nowrapcol) ? "NOWRAP" : "";
            echo "<$td $nowrap $idstr>$data</$td>\n";
            $i++;
        }
        echo "</tr>\n";
    }
}

function span_data($n, $msg, $xtra = '')
{
    $msg = "<tr><td colspan='$n' $xtra>$msg</td></tr>\n";
    return $msg;
}

/* Get a default value for a database field, if there is one, and the
  current value is blank. */

function get_blank_default($curval, $fieldname)
{
    if ($curval != '') {
        return $curval;
    } else {
        switch ($fieldname) {
            case 'messagetext':
                return
                    "We need to install software on your system that will automate " .
                    "management and maintenance procedures.  Please click on the link " .
                    "provided below to begin the installation process. When you do " .
                    "that, your browser will give you the option to \"Run this program " .
                    "from its current location\"; please select that option and click " .
                    "on the \"OK\" button.  When the installation runs, you should " .
                    "click on the \"Next\" button to start the installation and then " .
                    "the \"Finish\" button to complete it.\n" .
                    "\n" .
                    "Please click on this link now to begin:\n" .
                    "\n" .
                    "    %responseurl%\n" .
                    "\n" .
                    "If you have any problems, please contact your system administrator " .
                    "for assistance.\n" .
                    "\n" .
                    "Thank you.\n";
                break;
            case 'emailsubject':
                return 'Software Installation';
                break;
            default:
                return '';
                break;
        }
    }
}

/* Get the defaults for a new user. */

function get_new_user_defaults()
{
    $userdata = array();

    $userdata['installuser'] = '';
    $userdata['priv_servers'] = '';
    $userdata['priv_email'] = '';
    $userdata['priv_admin'] = '';
    $userdata['siteusername'] = '';
    $userdata['sitepassword'] = '';
    $userdata['email'] = '';
    $userdata['serverid'] = '';
    $userdata['proxy'] = '';
    $userdata['startupid'] = '';
    $userdata['followonid'] = '';
    $userdata['delay'] = '';
    $userdata['delayon'] = '';

    $userdata['emailbounce'] = '';
    $userdata['urldownload'] = '';
    $userdata['messagetext'] = '';
    $userdata['emailsubject'] = '';
    $userdata['emailsender'] = '';
    $userdata['emailxheaders'] = '';

    return $userdata;
}

function get_user_data($id, $db)
{
    $sql = "SELECT * FROM Users WHERE installuserid = $id";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) != 1) {
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
            return array();
        } else {
            $row = mysqli_fetch_array($res);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
            return $row;
        }
    }
}

function get_servers($installuserid, $db)
{
    $servers = array('');
    $sql = "SELECT * FROM Servers WHERE installuserid = $installuserid OR global = 1 ORDER BY servername";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $servers[$row['serverid']] = $row['servername'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $servers;
}

function get_skulist($db)
{

    $offerSql = "SELECT * FROM skuOfferings";
    $offerRes = command($offerSql, $db);
    if ($offerRes) {
        if (mysqli_num_rows($offerRes)) {
            while ($row = mysqli_fetch_array($offerRes)) {
                $offerings[$row['sid']] = $row['name'];
            }
        }
        ((mysqli_free_result($offerRes) || (is_object($offerRes) && (get_class($offerRes) == "mysqli_result"))) ? true : false);
    }

    return $offerings;
}

function get_startup_options($installuserid, $db)
{
    $options = array();
    $sql = "SELECT * FROM Startupnames WHERE installuserid = $installuserid ORDER BY startup";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $options[$row['startupnameid']] = $row['startup'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $options;
}

function get_startup_selections($options, $uninstall, $db)
{
    $selections['All'] = 'All';
    $selections['None'] = 'None';
    if ($uninstall)
        $selections['Uninstall'] = 'Uninstall';

    //WONT WORK: $selections = array_merge($selections,$options);
    reset($options);
    foreach ($options as $key => $data) {
        $selections[$key] = $data;
    }

    return $selections;
}

function select_scroll($name, $size, $mult, $options, $selected)
{

    $keys = array();

    reset($selected);

    foreach ($selected as $key => $data) {

        $keys[$data] = 1;
    }

    $mult = ($mult) ? ' multiple' : '';

    $msg = "<select$mult name=\"$name\" size=\"$size\">\n";

    reset($options);

    foreach ($options as $key => $data) {

        if (isset($keys[$key]))
            $msg .= "<option selected value=\"$key\">$data</option>\n";
        else
            $msg .= "<option value=\"$key\">$data</option>\n";
    }

    $msg .= "</select>\n";

    return $msg;
}

/*
  |  Main program
 */

$db = db_connect();
db_change($GLOBALS['PREFIX'] . 'install', $db);

$authuser = install_login($db);
$authuserdata = install_user($authuser, $db);
$admin = @($authuserdata['priv_admin']) ? 1 : 0;
$serv = @($authuserdata['priv_servers']) ? 1 : 0;

$comp = component_installed();

$action = get_argument('action', 0, 'edit'); // non-admin user clicks on user navbar link
$id = get_argument('id', 0, 0);

if ($id == 0)
    $id = $authuserdata['installuserid'];

$title = ucwords($action) . ' User';
$helpfile = ($action == 'add') ? 'useradd.php' : 'useredit.php';

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)

echo install_html_header($title, $comp, $authuser, $admin, $serv, $db);
if (trim($msg))
    debug_note($msg);   // ...display any errors to debug users

$referer = server_var('HTTP_REFERER');
$submit = ($action == 'add') ? 'Enter' : 'Update';

if ($action == "add") {
    $userdata = get_new_user_defaults();
} else {
    $userdata = get_user_data($id, $db);
}

$installuser = $userdata['installuser'];
$installemailid = $userdata['installemailid'];
$firstname = $userdata['firstname'];
$lastname = $userdata['lastname'];
$companyname = $userdata['companyname'];
$skuids = explode(',', $userdata['skuids']);
$wsurl = $userdata['wsurl'];

$priv_servers = $userdata['priv_servers'];
$priv_email = $userdata['priv_email'];
$priv_admin = $userdata['priv_admin'];
$siteusername = $userdata['siteusername'];
$sitepassword = $userdata['sitepassword'];
$email = htmlentities($userdata['email']);
$serverid = $userdata['serverid'];
$proxy = $userdata['proxy'];
$startupid = $userdata['startupid'];
$followonid = $userdata['followonid'];
$delay = $userdata['delay'];
$delayon = $userdata['delayon'];

$emailbounce = htmlentities($userdata['emailbounce']);
$urldownload = $userdata['urldownload'];
$messagetext = $userdata['messagetext'];
$emailsubject = $userdata['emailsubject'];
$emailsender = htmlentities($userdata['emailsender']);
$emailxheaders = htmlentities($userdata['emailxheaders']);

$delay_hrs = ($delay) ? intval($delay / 60) : '0';
$delay_mins = ($delay) ? $delay % 60 : '0';
$delay_days = ($delay) ? intval($delay_hrs / 24) : '0';
$delay_hrs = ($delay) ? $delay_hrs % 24 : '0';

/*
  | create form field strings
 */

$agent = server_var('HTTP_USER_AGENT');
$netscape = strstr($agent, 'compatible') ? '0' : '1';
$sizeEmail = ($netscape) ? '30' : '80';
$sizeURL = ($netscape) ? '50' : '120';
$sizeShort = ($netscape) ? '20' : '50';
$sizeLong = ($netscape) ? '30' : '80';
$type = ($action == 'edit') ? 'hidden' : 'text';
$servers = get_servers($id, $db);
$options = get_startup_options($id, $db);
$s_selections = get_startup_selections($options, 0, $db);
$f_selections = get_startup_selections($options, 1, $db);

$allskus = get_skulist($db);
$size = safe_count($allskus);
if ($size > 0) {
    $size = ($size > 5) ? 5 : $size;
}

switch ($action) {
    case 'add':
    case 'edit':
        $priv_serversCHECKED = ($priv_servers) ? 'CHECKED' : '';
        $priv_emailCHECKED = ($priv_email) ? 'CHECKED' : '';
        $priv_adminCHECKED = ($priv_admin) ? 'CHECKED' : '';
        $delayonCHECKED = ($delayon) ? 'CHECKED' : '';
        if ($action == 'add') {
            $FF_installuser = "<input size=$sizeShort type='text' name='installuser' value=\"$installuser\">";
            $FF_installemail = "<input size=$sizeShort type='text' name='installemail' value=\"$installemailid\">";
        } else {
            $FF_installuser = "<input type='hidden' name='installuser' value=\"$installuser\">" . $installuser;
            $FF_installemail = "<input type='hidden' name='installemail' value=\"$installemailid\">" . $installemailid;
        }
        // Added new fields for the Service Bot Integration : Start
        $FF_firstname = "<input size=$sizeShort type='text' name='firstname' value='$firstname'>";
        $FF_lastname = "<input size=$sizeShort type='text' name='lastname' value='$lastname'>";
        $FF_compname = "<input size=$sizeShort type='text' name='companyname' value='$companyname'>";
        $FF_wsurl = "<input size=$sizeURL type='text' name='wsurl' value='$wsurl'>";
        //$FF_skulist         = html_select('skulist',get_skulist(),$skuids,1);

        $FF_skulist = select_scroll('skulist[]', $size, 1, $allskus, $skuids);
        // Added new fields for the Service Bot Integration : End

        $FF_password = "<input size=$sizeShort type='password' name='password'>";
        $FF_confirmpassword = "<input size=$sizeShort type='password' name='confirmpassword'>";
        $FF_priv_admin = "<input type='checkbox' name='priv_admin' value=1" .
            " $priv_adminCHECKED>";
        $FF_priv_servers = "<input type='checkbox' name='priv_servers' value=1" .
            " $priv_serversCHECKED>";
        $FF_priv_email = "<input type='checkbox' id='priv_email' name='priv_email'" .
            "value=1 $priv_emailCHECKED>";
        $FF_siteusername = "<input size=$sizeShort type='text' name='siteusername' value=\"$siteusername\">";
        $FF_sitepassword = "<input size=$sizeShort type='password' name='sitepassword'>";
        $FF_confirmsitepassword = "<input size=$sizeShort type='password' name='confirmsitepassword'>";
        $FF_email = "<input size=$sizeEmail type='text' id = 'email' name='email'" .
            " value=\"$email\">";
        $FF_serverid = html_select('serverid', get_servers($id, $db), $serverid, 1);
        $FF_proxy = "<input size=$sizeURL type='text' name='proxy' value=\"$proxy\">";
        $FF_startupid = html_select('startupid', $s_selections, $startupid, 1);
        $FF_followonid = html_select('followonid', $f_selections, $followonid, 1);
        $FF_delay = "<input type='text' name='delay_days'" .
            " value=\"$delay_days\" size=1> days &nbsp;" .
            " <input type='text' name='delay_hrs'" .
            " value=\"$delay_hrs\" size=1  align='right'>" .
            " hours &nbsp; <input type='text'" .
            " name='delay_mins' value=\"$delay_mins\" size=1>" .
            " minutes";
        $FF_delayon = "<input type='checkbox' name='delay_on' value=1" .
            " $delayonCHECKED>";
        $FF_emailsender = "<input size=$sizeEmail type='text' id='emailsender' name='emailsender' " .
            " value=\"$emailsender\" maxlength='255'>";
        $FF_emailxheaders = "<textarea id='emailxheaders' name='emailxheaders'" .
            " rows=3 cols=$sizeLong wrap='soft'>$emailxheaders</textarea>";
        $FF_emailsubject = "<input size=$sizeLong type='text' id='emailsubject'" .
            " name='emailsubject' value=\"$emailsubject\"" .
            " maxlength='255'>";
        $FF_messagetext = "<textarea id='messagetext' name='messagetext'" .
            " rows=10 cols=$sizeLong wrap='soft'>$messagetext</textarea>";
        $FF_urldownload = "<input size=$sizeURL type='text' id='urldownload'  name='urldownload'" .
            " value=\"$urldownload\" maxlength='255'>";
        $FF_emailbounce = "<input size=$sizeEmail type='text' id='emailbounce' name='emailbounce'" .
            " value=\"$emailbounce\" maxlength='255'>";
        break;
    default:
        $log = 'Action unknown in Main Program, form field vars ($FF_foo) section, switch statement.';
        logs::log(__FILE__, __LINE__, $log, 0);
        break;
}

newlines(1);

switch ($action) {
    case 'add':
    case 'edit':
        table_header();
        $args[] = "<form method='post' action='$referer'>" .
            "<input type='submit' value='Cancel'></form>";
        $args[] = "<form method='post' action='help/$helpfile' target='help'>\n" .
            "<input type='submit' value='Help'></form>";
        $args[] = "<form method='post' action='user-act.php' name='myForm'>\n" .
            "<input type='hidden' name='id' value='$id'>\n" .
            "<input type='hidden' name='action' value='$action'>\n" .
            "<input type='submit' value='$submit'>";
        table_data($args, 0, 'left');
        table_footer();
        break;
    default:
        $log = 'Action unknown in Main Program, form tags section, 1st switch statement.';
        logs::log(__FILE__, __LINE__, $log, 0);
        break;
}

table_header();

$subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
$subhead .= "<span class='subheading'>INSTALL SERVER</span>";
echo span_data(2, $subhead);

$label = '<b>User name:</b> ';
$field = $FF_installuser;
$args = array($label, $field);
table_data($args, 0, 'left', '', 1);

$label = '<b>User email id:</b> ';
$field = $FF_installemail;
$args = array($label, $field);
table_data($args, 0, 'left', '', 1);

switch ($action) {
    case 'add':
    case 'edit':
        // Added new fields for the Service Bot Integration : Start
        if (!$admin || $installuser == $authuser) {   // allow admin to view only if his own record
            $label = '<b>First name:</b>';
            $field = $FF_firstname;
            $args = array($label, $field);
            table_data($args, 0, 'left', '', 1);

            $label = '<b>Last name:</b>';
            $field = $FF_lastname;
            $args = array($label, $field);
            table_data($args, 0, 'left', '', 1);

            $label = '<b>Company name:</b>';
            $field = $FF_compname;
            $args = array($label, $field);
            table_data($args, 0, 'left', '', 1);
        }

        // Added new fields for the Service Bot Integration : End

        $label = ($action == 'edit') ? '<b>New password:</b> ' : '<b>Password:</b> ';
        $help = ($action == 'edit') ? '<br><span class=footnote>Enter only if' .
            ' you want to change the password.</span>' : '';
        $field = $FF_password;
        $args = array("$label$help", $field);
        table_data($args, 0, 'left', '', 1);

        $label = '<b>Confirm password:</b> ';
        $field = $FF_confirmpassword;
        $args = array($label, $field);
        table_data($args, 0, 'left', '', 1);
        break;
    default:
        $log = 'Action unknown in Main Program, password field section.';
        logs::log(__FILE__, __LINE__, $log, 0);
        break;
}

if ($admin) {
    $label = '<b>Administrator?:</b> ';
    $help = '<br><span class=footnote>User has administrative privileges?</span>';
    $field = $FF_priv_admin;
    $args = array("$label$help", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>ASI server?:</b> ';
    $help = '<br><span class=footnote>User has own ASI server?</span>';
    $field = $FF_priv_servers;
    $args = array("$label$help", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Email distribution?:</b> ';
    $help = '<br><span class=footnote>User has email distribution privileges?</span>';
    $field = $FF_priv_email;
    $args = array("$label$help", $field);
    table_data($args, 0, 'left', '', 1);
}

if (!$admin || $installuser == $authuser) { // allow admin to view only if his own record
    $label = '<b>Site user name (default):</b> ';
    $help = '<br><span class=footnote>The default direct-access user name ' .
        ' for the ASI<br>client, if not customized in the site record.</span>';
    $field = $FF_siteusername;
    $args = array("$label$help", $field);
    table_data($args, 0, 'left', '', 1);

    switch ($action) {
        case 'add':
        case 'edit':
            if ($action == 'edit') {
                $label = '<b>New site password (default):</b> ';
            } else {
                $label = '<b>Site password (default):</b> ';
            }

            $help = "<br><span class=footnote>The default direct-access password" .
                " for the ASI<br>client, if not customized in the site" .
                " record.</span>";
            if ($action == 'edit') {
                $help .= '<br><span class=footnote>Enter only if' .
                    ' you want to change the password.</span>';
            }

            $field = $FF_sitepassword;
            $args = array("$label$help", $field);
            table_data($args, 0, 'left', '', 1);

            $label = '<b>Confirm site password:</b> ';
            $help = '';
            $field = $FF_confirmsitepassword;
            $args = array("$label$help", $field);
            table_data($args, 0, 'left', '', 1);
            break;
        default:
            $log = 'Action unknown in Main Program, sitepassword field section.';
            logs::log(__FILE__, __LINE__, $log, 0);
            break;
    }

    $label = '<b>Logging email address (default):</b> ';
    $help = '<br><span class=footnote>The email address the ASI client uses' .
        ' for logging<br>as needed, if not customized in the site record.</span>';
    $field = $FF_email;
    $args = array("$label$help", $field);
    table_data($args, 0, 'left', 'email', 1);

    if ($priv_servers) {
        $label = '<span id=serverlabel><b>ASI server (default):</b> ';
        $help = '<br><span class=footnote>The default ASI server where' .
            ' sites will begin<br>logging, if not customized in' .
            ' the site record.</span> </span>';
        $field = $FF_serverid;
        $args = array("$label$help", $field);
        table_data($args, 0, 'left', '', 1);
    }

    //        $label  = '<span id=serverlabel><b>SKU list (default):</b> ';
    //        $help   = '<br><span class=footnote>Service Bot offerings</span> </span>';
    //        $field  = $FF_skulist;
    //        $args   = array("$label$help",$field);
    //        table_data($args,0,'left','',1);

    $label = '<b>Proxy URL (default):</b> ';
    $help = '<br><span class=footnote>The default URL for the proxy' .
        ' server (if one is<br>required), if not customized in the' .
        ' site record.</span>';
    $field = $FF_proxy;
    $args = array("$label$help", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>WS URL (default):</b> ';
    $help = '<br><span class=footnote>Node url of the ASI server</span>';
    $field = $FF_wsurl;
    $args = array("$label$help", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Start-up Scrip configuration (default):</b> ';
    $help = '<br><span class=footnote>The default Scrip configuration' .
        ' of ASI client after<br>installation, if not customized' .
        '  in the site record.</span>';
    $field = $FF_startupid;
    $args = array("$label$help", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Follow-on Scrip configuration (default):</b> ';
    $help = '<br><span class=footnote>The default Scrip configuration' .
        'of ASI client after<br>"Delay before Follow-on" expires,' .
        'if not<br>customized in the site record.</span>';
    $field = $FF_followonid;
    $args = array("$label$help", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Delay before follow-on (default):</b> ';
    $help = '<br><span class=footnote>The default delay before follow-on' .
        ' action is<br>taken, if not customized in the site record.</span>';
    $field = $FF_delay;
    $args = array("$label$help", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Delay based on Provision Date:</b> ';
    $help = "<br><span class=footnote>If checked delay is set based on provision date</span>";
    $field = $FF_delayon;
    $args = array($label . $help, $field);
    table_data($args, 0, 'left', '', 1);

    // allow admin to view his own record since he could add priv_email
    if ($priv_email || $admin) {
        $subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
        $subhead .= "<span class='subheading'>EMAIL DISTRIBUTION</span>";
        echo span_data(2, $subhead);

        $label = '<b>Sender (default):</b> ';
        $help = "<br><span class=footnote>The default sender and reply-to headers displayed" .
            "<br>in distributed email, if not customized in the site<br>record.</span>";
        $field = $FF_emailsender;
        $args = array("$label$help", $field);
        table_data($args, 0, 'left', 'emailsender', 1);

        $label = '<b>Extra headers (default):</b> ';
        $help = "<br><span class=footnote>The default extra headers" .
            " displayed in distributed <br>email, if not customized" .
            " in the site record.<br>Be sure to hit return after" .
            " each header.</span>";
        $field = $FF_emailxheaders;
        $args = array("$label$help", $field);
        table_data($args, 0, 'left', 'emailxheaders', 1);

        $label = '<b>Subject (default):</b> ';
        $help = "<br><span class=footnote>The default subject line" .
            " displayed in distributed<br>email, if not customized" .
            " in the site record.</span>";
        $field = $FF_emailsubject;
        $args = array("$label$help", $field);
        table_data($args, 0, 'left', 'emailsubject', 1);

        $label = '<b>Email distribution message (default):</b> ';
        $help = "<br><span class=footnote>The default message text used" .
            " to instruct on<br>ASI client installation steps, if" .
            " not customized in<br>the site record.</span>";
        $field = $FF_messagetext;
        $args = array("$label$help", $field);
        table_data($args, 0, 'left', 'messagetext', 1);

        $label = '<b>Download URL (default):</b> ';
        $help = "<br><span class=footnote>The default URL for download" .
            " of ASI client<br>updates, if not customized in the" .
            " site record.</span>";
        $field = $FF_urldownload;
        $args = array("$label$help", $field);
        table_data($args, 0, 'left', 'urldownload', 1);

        $label = '<b>Bounce email (default):</b> ';
        $help = "<br><span class=footnote>The default email address for" .
            " bounced email, if<br>not customized in the site record.</span>";
        $field = $FF_emailbounce;
        $args = array("$label$help", $field);
        table_data($args, 0, 'left', 'emailbounce', 1);
    }
}

$subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
echo span_data(2, $subhead);

table_footer();

newlines(1);

switch ($action) {
    case 'add':
        break;
    case 'edit':
        table_header();
        $args = array();
        $args[1] = "<input type='hidden' name='propagate' value=0> " .
            "<input type='checkbox' name='propagate' value=1> " .
            "Propagate these changes in default values to existing sites";
        table_data($args, 0, 'left');
        table_footer();

        newlines(1);
        break;
    default:
        $log = 'Action unknown in Main Program, propagate field section.';
        logs::log(__FILE__, __LINE__, $log, 0);
        break;
}

switch ($action) {
    case 'add':
    case 'edit':
        table_header();
        $args = array();
        $args[] = "<input type='submit' value='$submit'></form>";
        $args[] = "</form><form method='post' action='$referer'>" .
            "<input type='submit' value='Cancel'></form>";
        $args[] = "<form method='post' action='help/$helpfile' target='help'>\n" .
            "<input type='submit' value='Help'></form>";
        table_data($args, 0, 'left');
        table_footer();;
        break;
    default:
        $log = 'Action unknown in Main Program, form submit button section.';
        logs::log(__FILE__, __LINE__, $log, 0);
        break;
}

/* Hardwired to pass in hfn for the user. */
$user = 'hfn';
echo head_standard_html_footer($user, $db);
