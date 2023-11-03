<?php

//error_reporting(-1);
//ini_set('display_errors', 'On');
/*
  Revision history:

  Date        Who     What
  ----        ---     ----
  09-Jun-03   NL      Create by combining sitedata.php, sitelist.php, site-act.php
  09-Jun-03   NL      "Reset to default values" button.
  09-Jun-03   NL      Create admin "summary" view for sites page (includes numconnects subTL).
  09-Jun-03   NL      Change subtotal row color: #CCCCCCC -> #EEEEEE
  10-Jun-03   NL      Run code related to admin summary view only if $summary turned on.
  10-Jun-03   NL      If "Uninstall" followon set uninstall field in DB.
  10-Jun-03   NL      gen_regcode(): pad number if less than 9 digits.
  11-Jun-03   NL      Change maillist.php -> emails.php
  15-Jun-03   NL      title change: Editing Site >>> Updating Site.
  15-Jun-03   NL      gen_regcode(): regen increment by 16 x 0-99.
  16-Jun-03   NL      Change include line: '../lib/l-head.php' --> 'header.php'.
  15-Jul-03   NL      Add & process subject, sender, extra headers fields.
  15-Jul-03   NL      Add Count cols to sites page.
  21-Jul-03   NL      Add maxlength='255' to email form fields; rearrange order.
  23-Jul-03   NL      Change footer to standard_html_footer().
  27-Jul-03   NL      display_site_row(): display '[legacy installation]' if username blank.
  27-Jul-03   NL      rename Destination email -> Logging email address
  28-Jul-03   NL      display_list(): fix bug re column header ($args[]).
  28-Jul-03   NL      insert_site(): use default sitepassword if it matches the (non-blank) siteusername.
  28-Jul-03   NL      Create blue subheadings: CLIENT SETUP & EMAIL DISTRIBUTION.
  28-Jul-03   NL      Change page titles.
  28-Jul-03   NL      Change page titles (create --> add).
  31-Jul-03   EWB     Uses install_login($db);
  7-Aug-03   NL      Label changes.
  7-Aug-03   NL      change messagetext from softwrap to no wrap (for HTML email).
  7-Aug-03   NL      Change all text messages: create --> add.
  7-Aug-03   NL      Show real instructions.
  8-Aug-03   NL      get_servers(): get global ("Available to all" servers).
  9-Aug-03   NL      Add more text to instructions (display_summary()).
  11-Aug-03   NL      Add more text to instructions (display_summary()).
  14-Aug-03   NL      insert_site(), update_site(): Check for dup entries using db (not PHP).
  14-Aug-03   NL      Minor formating changes.
  15-Aug-03   NL      Create get_key_index() to check sql error for dupes in a
  specific index; Use get_key_index for dup name and regcode.
  15-Aug-03   NL      update_site(): Use get_key_index for dup name and regcode.
  28-Aug-03   NL      Use htmlentities() around all email addresses to accomodate quotes.
  28-Aug-03   NL      Capitalize scrip --> Scrip.
  28-Aug-03   NL      insert_site(), update_site(): Match on error code instead of error message.
  29-Aug-03   NL      Include lib/l-dberr.php for get_key_index().
  29-Aug-03   NL      insert_site(), update_site(): prepend downloadurl w/ http://.
  2-Sep-03   NL      update_site(): Wording change.
  2-Sep-03   NL      display_site(): formatting (add a blue line at bottom).
  3-Sep-03   NL      display_site_row(): Allow non-admin users to delete thier sites.
  3-Sep-03   NL      display_list(): display friendly message if no sites exist.
  15-Sep-03   NL      Direct help buttons to corresponding help page.
  25-Sep-03   NL      Add "install:" and "by $authuser" to all error_log entries;
  Create entries for all db actions.
  05-Nov-03   NL      Add new paragraph to summary; add stripslashes to sitename.
  13-Jul-04   WOH     Changed help text per Alex.
  13-Oct-05   AAM     Corrected determination of legacy site codes.
  07-Jul-06   BTE     Bug 3505: Install server: disallow case-sensitive only
  changes.
  09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
  19-Jun-07   AAM     Moved and added some includes.  Added code to handle new
  startuptype and followontype database columns, and removal
  of uninstall column.  Updated references to indices.  Moved
  make_seed and gen_regcode to common.php.
  27-Jun-07   AAM     Added user values for automation.
  03-Oct-08   BTE     Bug 4828: Change customization feature of server.
  25-Oct-08   AAM     Bug 4823: backed out all "automation" changes.  This
  involved removing common.php. Did a little cleanup at
  the same time.
  13-Mar-19   JHN     Creating default site email entry for the logged user.
  30-Sep-19   SHG     Mac,iOS and Linux client upload functionality.

 */

$summary = 0;

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-cnst.php');
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-dberr.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-slct.php');
include('../lib/l-user.php');
include('../lib/l-head.php');
include('header.php');
include('../lib/l-errs.php');
include('../lib/l-svbt.php');
include('../lib/l-config.php');

global $global_cid;


/*
  |  HTML Functions
 */

function newlines($n)
{
    for ($i = 0; $i < $n; $i++) {
        echo "<br>\n";
    }
}

// this is gross .. makes the entire contents into
// a table.  oh well.
function silly_table()
{
    return '<table width="100%" border="0"><tr><td>';
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

/*
  |  Note that the mark for "top" is now included in header.inc.
  |  The mark for "bottom" is included in footer.inc.
 */

function jumptable($tags)
{
    $args = explode(',', $tags);
    $n = safe_count($args);
    if ($n > 0) {
        $msg = '';
        for ($i = 0; $i < $n; $i++) {
            $name = $args[$i];
            $link = marklink("#$name", $name);
            if ($i)
                $msg .= brace();
            $msg .= $link;
        }
        $msg .= brace();
        $msg .= "<a href='sites.php?action=add'>add site</a>";
        echo "<p>[ $msg ]</p>";
    }
}

function table_header($border = 0, $cellspacing = 0, $cellpadding = 0, $align = 'left')
{
    echo "\n<table border='$border' cellspacing='$cellspacing' cellpadding='$cellpadding' align='$align'>\n";
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

function message($s)
{
    $msg = stripslashes($s);
    echo "<br>\n$msg<br>\n<br>\n";
}

/*
  |  Display Data Functions (from erstwhile sitedata.php)
 */

function get_site_data($id, $db)
{
    $sitedata = array();
    $sql = "SELECT * FROM Sites WHERE siteid = $id";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $sitedata['siteid'] = $row['siteid'];
                $sitedata['sitename'] = $row['sitename'];
                $sitedata['domain'] = $row['domain'];
                $sitedata['installuserid'] = $row['installuserid'];
                $sitedata['username'] = $row['username'];
                $sitedata['password'] = $row['password'];
                $sitedata['email'] = htmlentities($row['email']);
                $sitedata['serverid'] = $row['serverid'];
                $sitedata['skuids'] = $row['skuids'];
                $sitedata['wsurl'] = $row['wsurl'];
                $sitedata['cid'] = $row['cid'];
                $sitedata['proxy'] = $row['proxy'];
                $sitedata['startupid'] = $row['startupid'];
                $sitedata['followonid'] = $row['followonid'];
                $sitedata['delay'] = $row['delay'];
                $sitedata['delayon'] = $row['delayon'];
                $sitedata['regcode'] = $row['regcode'];
                $sitedata['deploypath32'] = $row['deploypath32'];
                $sitedata['deploypath64'] = $row['deploypath64'];
                $sitedata['fcmUrl'] = $row['fcmUrl'];
                $sitedata['emailbounce'] = htmlentities($row['emailbounce']);
                $sitedata['urldownload'] = $row['urldownload'];
                $sitedata['messagetext'] = $row['messagetext'];
                $sitedata['emailsubject'] = $row['emailsubject'];
                $sitedata['emailsender'] = htmlentities($row['emailsender']);
                $sitedata['emailxheaders'] = htmlentities($row['emailxheaders']);
                $sitedata['firstcontact'] = $row['firstcontact'];
                $sitedata['lastcontact'] = $row['lastcontact'];
                $sitedata['numconnects'] = $row['numconnects'];
                $sitedata['numedits'] = $row['numedits'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $sitedata;
}

function get_servers($installuserid, $db)
{
    $servers = [];
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

function get_configuredSku($selectedCustId, $db)
{
    global $global_cid;
    $skulist = [];
    $offerings = [];

    if ($selectedCustId == '') {
        $selectedCustId = $global_cid;
    }

    $sql = "SELECT sku_list FROM Customers WHERE cid = '$selectedCustId'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $skulist[] = $row['sku_list'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    $offerids = implode(',', $skulist);
    $offerSql = "SELECT * FROM skuOfferings where sid IN ($offerids)";
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

function get_configuredCustomer($installuserid, $db)
{
    global $global_cid;
    $couter = 0;
    $offerings = [];
    //$offerids = implode(',', $skulist);
    $offerSql = "SELECT * FROM Customers where tenant_id = '$installuserid' order by customer_name";
    $offerRes = command($offerSql, $db);
    if ($offerRes) {
        if (mysqli_num_rows($offerRes)) {
            while ($row = mysqli_fetch_array($offerRes)) {
                $offerings[$row['cid']] = $row['customer_name'];
                if ($couter === 0) {
                    $global_cid = $row['cid'];
                }
                $couter++;
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

function get_user_data($id, $db)
{
    $userdata = array();
    $sql = "SELECT * FROM Users WHERE installuserid = $id";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $userdata['installuserid'] = $row['installuserid'];
                $userdata['installuser'] = $row['installuser'];
                $userdata['password'] = $row['password'];
                $userdata['priv_servers'] = $row['priv_servers'];
                $userdata['priv_email'] = $row['priv_email'];
                $userdata['priv_admin'] = $row['priv_admin'];
                $userdata['siteusername'] = $row['siteusername'];
                $userdata['sitepassword'] = $row['sitepassword'];
                $userdata['email'] = htmlentities($row['email']);
                $userdata['serverid'] = $row['serverid'];
                $userdata['skuids'] = $row['skuids'];
                $userdata['wsurl'] = $row['wsurl'];
                $userdata['proxy'] = $row['proxy'];
                $userdata['startupid'] = $row['startupid'];
                $userdata['followonid'] = $row['followonid'];
                $userdata['delay'] = $row['delay'];
                $userdata['messagetext'] = $row['messagetext'];
                $userdata['emailsubject'] = $row['emailsubject'];
                $userdata['emailsender'] = htmlentities($row['emailsender']);
                $userdata['emailxheaders'] = htmlentities($row['emailxheaders']);
                $userdata['emailbounce'] = htmlentities($row['emailbounce']);
                $userdata['urldownload'] = $row['urldownload'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $userdata;
}

function display_site($action, $reset, $id, $authid, $db)
{
    $sitename = get_argument('sitename', 0, '');
    $domain = get_argument('domain', 0, '');


    // form variabless
    $referer = server_var('HTTP_REFERER');
    $self = server_var('PHP_SELF');
    $self = preg_replace('/\?.+/', '', $self);
    $submit = ($action == 'add') ? 'Enter' : 'Update';
    $helpfile = ($action == 'add') ? 'siteadd.php' : 'siteedit.php';

    if ($action != "add")
        $sitedata = get_site_data($id, $db);

    // because an admin viewer can view and edit s.o. else's site
    $installuserid = ($action == 'add') ? $authid : $sitedata['installuserid'];

    $userdata = get_user_data($installuserid, $db);
    $priv_email = $userdata['priv_email'];
    $priv_servers = $userdata['priv_servers'];

    $sitename = ($action == 'add' || $reset) ? $sitename : $sitedata['sitename'];
    $domain = ($action == 'add' || $reset) ? $domain : $sitedata['domain'];
    $regcode = ($action == 'add') ? '' : $sitedata['regcode'];
    $username = ($action == 'add' || $reset) ? $userdata['siteusername'] : $sitedata['username'];
    $email = ($action == 'add' || $reset) ? $userdata['email'] : $sitedata['email'];
    $serverid = ($action == 'add' || $reset) ? $userdata['serverid'] : $sitedata['serverid'];
    $skuids = ($action == 'add' || $reset) ? '' : $sitedata['skuids'];
    //    In case of an error, check the line below
    $wsurl = (($action == 'add' || $reset) ? $userdata['wsurl'] : ($sitedata['wsurl'] == '')) ? $userdata['wsurl'] : $sitedata['wsurl'];

    $cid_val = ($action == 'add' || $reset) ? '' : $sitedata['cid'];
    $proxy = ($action == 'add' || $reset) ? $userdata['proxy'] : $sitedata['proxy'];
    $startupid = ($action == 'add' || $reset) ? $userdata['startupid'] : $sitedata['startupid'];
    $followonid = ($action == 'add' || $reset) ? $userdata['followonid'] : $sitedata['followonid'];
    $delay = ($action == 'add' || $reset) ? $userdata['delay'] : $sitedata['delay'];
    $delayon = ($action == 'add' || $reset) ? $userdata['delayon'] : $sitedata['delayon'];
    $emailbounce = ($action == 'add' || $reset) ? $userdata['emailbounce'] : $sitedata['emailbounce'];
    $emailsubject = ($action == 'add' || $reset) ? $userdata['emailsubject'] : $sitedata['emailsubject'];
    $emailsender = ($action == 'add' || $reset) ? $userdata['emailsender'] : $sitedata['emailsender'];
    $emailxheaders = ($action == 'add' || $reset) ? $userdata['emailxheaders'] : $sitedata['emailxheaders'];
    $messagetext = ($action == 'add' || $reset) ? $userdata['messagetext'] : $sitedata['messagetext'];
    $urldownload = ($action == 'add' || $reset) ? $userdata['urldownload'] : $sitedata['urldownload'];

    $delay_hrs = ($delay) ? intval($delay / 60) : '0';
    $delay_mins = ($delay) ? $delay % 60 : '0';
    $delay_days = ($delay) ? intval($delay_hrs / 24) : '0';
    $delay_hrs = ($delay) ? $delay_hrs % 24 : '0';

    $delayonchecked = ($delayon) ? 'checked' : '';

    $deploypath32 = ($sitedata['deploypath32'] == '') ? '' : $sitedata['deploypath32'];
    $deploypath64 = ($sitedata['deploypath64'] == '') ? '' : $sitedata['deploypath64'];
    $fcmUrl = ($sitedata['fcmUrl'] == '') ? '' : $sitedata['fcmUrl'];

    /*
      | Display sitename & version at top of page
     */
    if ($action != 'add') {
        newlines(1);

        table_header(0, 0, 6);
        $args = array("<span class = 'blue'><b>Site name:</b> </span>", "<b>$sitename</b>");
        table_data($args, 0, 'left');
        $args = array("<span class = 'blue'><b>Registration code:</b> </span>", "<b>$regcode</b>");
        table_data($args, 0, 'left');
        table_footer();
    }

    $agent = server_var('HTTP_USER_AGENT');
    $netscape = strstr($agent, 'compatible') ? '0' : '1';
    $size50 = ($netscape) ? '20' : '50';
    $size40 = ($netscape) ? '20' : '40';

    newlines(1);

    table_header(0, 0, 6);
    $args = array();
    $args[0] = "<form method='post' action='$referer'>\n" .
        "<input type='submit' value='Cancel'></form>";
    $args[1] = "<form method='post' action='help/$helpfile' target='help'>\n" .
        "<input type='submit' value='Help'></form>";
    if ($action != 'add') {
        $args[2] = "<form enctype=\"multipart/form-data\" method='post' action='$self'>\n" .
            "<input type='hidden' name='action' value='act-$action'>\n" .
            "<input type='hidden' name='id' value='$id'>\n" .
            "<input type='hidden' name='regcode' value='$regcode'>\n" .
            "<input type='file' id=\"executable_client_32_id\" name='executable_client_32' value=\"\" style=\"display:none\"><input type='file' id=\"executable_client_64_id\" name='executable_client_64' value=\"\" style=\"display:none\">"
            . "<input type='file' id=\"executable_client_apk_id\" name='executable_client_apk' value=\"\" style=\"display:none\">"
            . "<input type='file' id=\"executable_client_mac_id\" name='executable_client_mac' value=\"\" style=\"display:none\">"
            . "<input type='file' id=\"executable_client_ios_id\" name='executable_client_ios' value=\"\" style=\"display:none\">"
            . "<input type='file' id=\"executable_client_linux_id\" name='executable_client_linux' value=\"\" style=\"display:none\"><input type='submit' name='reset' value='Reset to default values'>\n";
        $args[3] = "<input type='submit' value='$submit'>";
    } else {
        $args[2] = "<form enctype=\"multipart/form-data\" method='post' action='$self'>\n" .
            "<input type='file' id=\"executable_client_32_id\" name='executable_client_32' value=\"\" style=\"display:none\"><input type='file' id=\"executable_client_64_id\" name='executable_client_64' value=\"\" style=\"display:none\"><input type='file' id=\"executable_client_apk_id\" name='executable_client_apk' value=\"\" style=\"display:none\">"
            . "<input type='file' id=\"executable_client_64_id\" name='executable_client_64' value=\"\" style=\"display:none\"><input type='file' id=\"executable_client_mac_id\" name='executable_client_mac' value=\"\" style=\"display:none\">"
            . "<input type='file' id=\"executable_client_64_id\" name='executable_client_64' value=\"\" style=\"display:none\"><input type='file' id=\"executable_client_ios_id\" name='executable_client_ios' value=\"\" style=\"display:none\">"
            . "<input type='file' id=\"executable_client_64_id\" name='executable_client_64' value=\"\" style=\"display:none\"><input type='file' id=\"executable_client_linux_id\" name='executable_client_linux' value=\"\" style=\"display:none\">"
            . "<input type='hidden' name='action' value='act-$action'>\n" .
            "<input type='submit' value='$submit'>";
    }
    table_data($args, 0, 'left');
    table_footer();


    table_header(0, 0, 6);

    $subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
    $subhead .= "<span class='subheading'>CLIENT SETUP</span>";
    echo span_data(2, $subhead);

    $label = '<b>Site name:</b> ';
    $field = "<input type='text' name='sitename' value=\"$sitename\">";
    $args = array($label, $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Site email domain:</b> ';
    $field = "<input type='text' name='domain' value=\"$domain\">";
    $args = array($label, $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Site user name:</b> ';
    $help = "<br><span class=footnote>The user name for ASI client direct access.</span>";
    $field = "<input type='text' name='username' value=\"$username\">";
    $args = array($label . $help, $field);
    table_data($args, 0, 'left', '', 1);

    $help = '<br><span class=footnote>The direct-access password for the ASI client.<br>';
    if ($action == 'add') {
        $label = '<b>Site password:</b> ';
        $help .= 'Enter only if you want to change from user default.</span>';
    } else {
        $label = '<b>New password:</b> ';
        $help .= 'Enter only if you want to change the password.</span>';
    }
    $field = "<input type='password' name='password'>";
    $args = array($label . $help, $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Confirm password:</b> ';
    $field = "<input type='password' name='confirmpassword'>";
    $args = array($label, $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Logging email address:</b> ';
    $help = "<br><span class=footnote>The email address the ASI client " .
        "uses for logging<br>as needed.</span>";
    $field = "<input type='text' id='email' name='email' value=\"$email\" size=$size50>";
    $args = array("<span id='email'>$label$help</span>", $field);
    table_data($args, 0, 'left', '', 1);

    if ($priv_servers) {
        $label = '<b>ASI server:</b> ';
        $help .= '<br><span class=footnote>The ASI server where sites will begin logging.</span>';
        $servers = get_servers($installuserid, $db);
        $field = html_select('serverid', $servers, $serverid, 1);
        $args = array($label . $help, $field);
        table_data($args, 0, 'left', '', 1);
    }

    $label = '<span id=serverlabel><b>Customer list:</b> ';
    $help = '<br><span class=footnote>Get customer offering SKU list</span> </span>';
    $field = html_select_onchange('custlist', get_configuredCustomer($installuserid, $db), $cid_val, 1, 'OnSelectionCust');
    $args = array("$label$help", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<span id=serverlabel><b>SKU list:</b> ';
    $help = '<br><span class=footnote>Service Bot offerings</span> </span>';
    $field = html_select('skulist', get_configuredSku($cid_val, $db), $skuids, 1);
    $args = array("$label$help", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>WS URL (default):</b> ';
    $help = '<br><span class=footnote>Node url of the ASI server</span>';
    $field = "<input size=$size50 type='text' name='wsurl' value='$wsurl'>";
    $args = array("$label$help", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Proxy URL:</b> ';
    $help = "<br><span class=footnote>The URL for the proxy server, " .
        "if one is<br>required.</span>";
    $field = "<input type='text' name='proxy' value=\"$proxy\" size=$size50>";
    $args = array($label . $help, $field);
    table_data($args, 0, 'left', '', 1);

    $options = get_startup_options($installuserid, $db);
    $s_selections = get_startup_selections($options, 0, $db);
    $f_selections = get_startup_selections($options, 1, $db);

    $label = '<b>Start-up Scrip configuration:</b> ';
    $help = "<br><span class=footnote>Scrip configuration of ASI client" .
        "<br>after installation.</span>";
    $field = html_select('startupid', $s_selections, $startupid, 1);
    $args = array($label . $help, $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Follow-on Scrip configuration:</b> ';
    $help = "<br><span class=footnote>Scrip configuration of ASI client after" .
        " <br>\"Delay before Follow-on\" expires.</span>";
    $field = html_select('followonid', $f_selections, $followonid, 1);
    $args = array($label . $help, $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Delay before follow-on:</b> ';
    $help = "<br><span class=footnote>The delay before follow-on action is taken.</span>";
    $field = "<input type='text' name='delay_days' value=\"$delay_days\" size=1>" .
        " days &nbsp; " .
        " <input type='text' name='delay_hrs' value=\"$delay_hrs\" size=1 >" .
        " hours &nbsp; " .
        "<input type='text' name='delay_mins' value=\"$delay_mins\" size=1>" .
        " minutes";
    $args = array($label . $help, $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Delay based on Provision Date:</b> ';
    $help = "<br><span class=footnote>If checked delay is set based on provision date</span>";
    $field = "<input type='checkbox' name='delay_on' value=\"$delayon\" $delayonchecked>";
    $args = array($label . $help, $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Deploy 32 path:</b> ';
    $help = "";
    $field = "<input type='text' name='deploypath32' value=\"$deploypath32\" size=$size50>";
    $args = array($label . $help, $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Deploy 64 path:</b> ';
    $help = "";
    $field = "<input type='text' name='deploypath64' value=\"$deploypath64\" size=$size50>";
    $args = array($label . $help, $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Android FCM Url:</b> ';
    $help = "<br/><span class=footnote>For Android client</span>";
    $field = "<input type='text' name='fcmUrl' value=\"$fcmUrl\" size=$size50>";
    $args = array($label . $help, $field);
    table_data($args, 0, 'left', '', 1);

    if ($priv_email) {
        $subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
        $subhead .= "<span class='subheading'>EMAIL DISTRIBUTION</span>";
        echo span_data(2, $subhead);

        $label = '<b>Sender:</b> ';
        $help = "<br><span class=footnote>The sender and reply-to headers displayed in" .
            "<br>distributed email.</span>";
        $field = "<input type='text' id='emailsender' name='emailsender' value=\"$emailsender\" size=$size50 maxlength='255'>";
        $args = array("<span id='emailsender'>$label$help</span>", $field);
        table_data($args, 0, 'left', '', 1);

        $label = '<b>Extra headers:</b> ';
        $help = "<br><span class=footnote>Optional extra headers displayed in distributed" .
            "<br> email. Be sure to hit return after each header.</span>";
        $field = "<textarea id='emailxheaders' name='emailxheaders' rows=3 cols=$size40 wrap='soft'>$emailxheaders</textarea>";
        $args = array("<span id='emailxheaders'>$label$help</span>", $field);
        table_data($args, 0, 'left', '', 1);

        $label = '<b>Subject:</b> ';
        $help = "<br><span class=footnote>The subject line displayed in distributed email.</span>";
        $field = "<input type='text' id='emailsubject' name='emailsubject' value=\"$emailsubject\" size=$size50 maxlength='255'>";
        $args = array("<span id='emailsubject'>$label$help</span>", $field);
        table_data($args, 0, 'left', '', 1);

        $label = '<b>Email distribution message:</b> ';
        $help = "<br><span class=footnote>The message text used to instruct on" .
            "<br>ASI client installation steps.</span>";
        $field = "<textarea id='messagetext' name='messagetext' rows=10 cols=$size40 wrap='none'>$messagetext</textarea>";
        $args = array("<span id='messagetext'>$label$help</span>", $field);
        table_data($args, 0, 'left', '', 1);

        $label = '<b>Download URL:</b> ';
        $help = "<br><span class=footnote>The URL for download of ASI client updates.</span>";
        $field = "<input type='text' id='urldownload' name='urldownload' value=\"$urldownload\" size=$size50 maxlength='255'>";
        $args = array("<span id='urldownload'>$label$help</span>", $field);
        table_data($args, 0, 'left', '', 1);

        $label = '<b>Bounce email:</b> ';
        $help = "<br><span class=footnote>The email address for bounced email.</span>";
        $field = "<input type='text' id='emailbounce' name='emailbounce' value=\"$emailbounce\" size=$size50 maxlength='255'>";
        $args = array("<span id='emailbounce'>$label$help</span>", $field);
        table_data($args, 0, 'left', '', 1);

        $label = '<b>Upload Client 32 Bit:</b> ';
        $help = "<br><span class=footnote>The 32 bit client to be downloaded(not madatory)</span>";
        $field = "<img src=\"../download/up-manual-icon.png\" style=\"height: 34px;cursor: pointer;\" onclick=\"document.getElementById('executable_client_32_id').click();\">";
        $args = array("<span id='emailbounce'>$label$help</span>", $field);
        table_data($args, 0, 'left', '', 1);

        $label = '<b>Upload Client 64 Bit:</b> ';
        $help = "<br><span class=footnote>The 64 bit client to be downloaded(not madatory)</span>";
        $field = "<img src=\"../download/up-manual-icon.png\" style=\"height: 34px;cursor: pointer;\" onclick=\"document.getElementById('executable_client_64_id').click();\">";
        $args = array("<span id='emailbounce'>$label$help</span>", $field);
        table_data($args, 0, 'left', '', 1);

        $label = '<b>Upload Android Client:</b> ';
        $help = "<br><span class=footnote>The Android client to be downloaded(not madatory)</span>";
        $field = "<img src=\"../download/up-manual-icon.png\" style=\"height: 34px;cursor: pointer;\" onclick=\"document.getElementById('executable_client_apk_id').click();\">";
        $args = array("<span id='emailbounce'>$label$help</span>", $field);
        table_data($args, 0, 'left', '', 1);

        $label = '<b>Upload Mac Client:</b> ';
        $help = "<br><span class=footnote>The Mac client to be downloaded(not madatory)</span>";
        $field = "<img src=\"../download/up-manual-icon.png\" style=\"height: 34px;cursor: pointer;\" onclick=\"document.getElementById('executable_client_mac_id').click();\">";
        $args = array("<span id='emailbounce'>$label$help</span>", $field);
        table_data($args, 0, 'left', '', 1);

        $label = '<b>Upload Ios Client:</b> ';
        $help = "<br><span class=footnote>The Ios client to be downloaded(not madatory)</span>";
        $field = "<img src=\"../download/up-manual-icon.png\" style=\"height: 34px;cursor: pointer;\" onclick=\"document.getElementById('executable_client_ios_id').click();\">";
        $args = array("<span id='emailbounce'>$label$help</span>", $field);
        table_data($args, 0, 'left', '', 1);

        $label = '<b>Upload Linux Client:</b> ';
        $help = "<br><span class=footnote>The Linux client to be downloaded(not madatory)</span>";
        $field = "<img src=\"../download/up-manual-icon.png\" style=\"height: 34px;cursor: pointer;\" onclick=\"document.getElementById('executable_client_linux_id').click();\">";
        $args = array("<span id='emailbounce'>$label$help</span>", $field);
        table_data($args, 0, 'left', '', 1);
    }

    $subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
    echo span_data(2, $subhead);

    table_footer();

    newlines(1);

    table_header(0, 0, 6);
    $args = array();
    $args[] = "<input type='submit' value='$submit'>";
    if ($action != 'add')
        $args[] = "<input type='submit' name='reset' value='Reset to default values'>\n" .
            "</form>";
    $args[] = "<form method='post' action='$referer'>\n" .
        "<input type='submit' value='Cancel'>\n" .
        "</form>";
    $args[] = "<form method='post' action='help/$helpfile' target='help'>\n" .
        "<input type='submit' value='Help'></form>";
    table_data($args, 0, 'left');
    table_footer();
}

/*
  |  Site List Functions (from erstwhile sitelist.php)
 */

function get_all_sites_data($admin, $userid, $db)
{
    $sitesdata = array();
    $sql_where = ($admin) ? "" : "AND U.installuserid = $userid";
    $sql = "SELECT * FROM Sites AS S,Users AS U " .
        " WHERE S.installuserid = U.installuserid " .
        $sql_where .
        " ORDER BY U.installuser, S.sitename";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $id = $row['siteid'];
                $sitesdata[$id] = $row;
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $sitesdata;
}

/* IsRegCodeClientGen
  Check whether a site registration code was generated by the client.
  This is indicated by the LSB of the code (without the check digit).
  Return true or false indicating whether client-generated.

  Note that this code is duplicated in dev/server/rpc/install.php.
 */

function IsRegCodeClientGen($siteID)
{
    /* take the first 9 digits and convert them to a number */
    $siteIDNumberStr = substr($siteID, 0, 9);
    $siteIDNumber = intval($siteIDNumberStr);

    /* now just look at the last bit */
    $lastBit = $siteIDNumber & 1;

    return $lastBit;
}

function display_site_row($id, $row, $summary, $priv_admin, $priv_email, $db)
{
    $self = server_var('PHP_SELF');
    $self = preg_replace('/\?.+/', '', $self);

    $installuserid = $row['installuserid'];
    $userdata = get_user_data($installuserid, $db);
    $installuser = $userdata['installuser'];

    $sitename = $row['sitename'];
    $username = $row['username'];
    $regcode = $row['regcode'];
    if (IsRegCodeClientGen($regcode)) {
        $username = '[legacy installation]';
    }
    /* If the username is blank, make sure it doesn't mess up the table. */
    if (!strlen($username)) {
        $username = '&nbsp;';
    }
    $numconnects = $row['numconnects'];

    if ($summary & $priv_admin)
        $args[] = $installuser;
    $args[] = $sitename;

    $args[] = $username;
    $args[] = $regcode;
    if ($priv_email) {
        $num_sent = 0;
        $response_conf = 0;
        $install_conf = 0;

        $sql = "SELECT * FROM Siteemail WHERE siteid = $id";
        $res = command($sql, $db);
        if ($res) {
            if ($total = mysqli_num_rows($res)) {
                while ($row = mysqli_fetch_array($res)) {
                    if ($row['sent'] > 0)
                        $num_sent++;
                    if ($row['sent'] < $row['response'])
                        $response_conf++;
                    if ($row['sent'] < $row['installed'])
                        $install_conf++;
                }
            }
        } else {
            $num_sent = 'unknown';
            $response_conf = 'unknown';
            $install_conf = 'unknown';
        }
    }
    if ($summary & $priv_admin)
        $args[] = $numconnects;

    $edit = "<a href='$self?id=$id&action=edit'>[edit]</a>";
    $del = "<a href='$self?id=$id&action=delete'>[delete]</a>";
    $manage = "<a href='emails.php?action=addresses&siteid=$id'>[manage email distribution]</a>";

    $actions = "$edit<br>$del";
    if ($priv_email)
        $actions .= "<br>$manage";
    $args[] = $actions;

    $actcol = ($summary & $priv_admin) ? 7 : 5;
    table_data($args, 0, 'left', '', $actcol);
}

function display_subtl_row($subtotal)
{
    echo "<tr bgcolor='#EEEEEE' >\n<td colspan=5 align='right'><b>Subtotal:</b></td>\n" .
        "<td><b>$subtotal</b></td>\n<td>&nbsp;</td>\n";
}

/*
  $summary refers to an admin summary created by Allan but not
  wanted by Alex, so not currently turned on.
 */

function display_list($summary, $auth_email, $auth_admin, $authid, $db)
{
    newlines(2);

    echo silly_table();

    mark('sites');
    jumptable('top,bottom,sites');
    table_header(2, 2, 2);
    if ($summary & $auth_admin)
        $args[] = 'Install User';
    $args[] = 'Site Name';
    $args[] = 'Local User Name';
    $args[] = 'Registration Code';
    if ($summary & $auth_admin)
        $args[] = 'Number of Client Contacts';
    $args[] = 'Action';
    table_data($args, 1);

    $sitesdata = get_all_sites_data($auth_admin, $authid, $db);
    if (safe_count($sitesdata)) {
        $numconnects = 0;
        $prev_installuserid = '';
        reset($sitesdata);
        foreach ($sitesdata as $key => $data) {
            // display subtotal of numconnects
            if ($summary & $auth_admin) {
                $installuserid = $data['installuserid'];
                if (strlen($prev_installuserid) && $installuserid != $prev_installuserid) {
                    display_subtl_row($numconnects);
                    $numconnects = 0;
                }

                $numconnects += $data['numconnects'];
                $prev_installuserid = $installuserid;
            }
            display_site_row($key, $data, $summary, $auth_admin, $auth_email, $db);
        }
        if ($summary & $auth_admin)
            display_subtl_row($numconnects);
    } else {
        echo span_data(safe_count($args), "<br>No sites currently exist.<br><br>");
    }

    table_footer();

    newlines(1);
    jumptable('top,bottom,sites');

    table_footer();
}

/*
  |  Action Functions (from erstwhile site-act.php)
 */

/*
  check_pwd_change
  If $req_old_pwd is 1, checks that old password entered by user is correct.
  Then checks that new password and confirm password exist and match.
  Returns $response of "success" or an error message to display to user.
  ARGS:
  $username:    user of password to change.
  $req_old_pwd: boolean whether to check that $old_pwd matches database password
  $old_pwd:     old password. If $req_old_pwd==0, just use empty string.
  $new_pwd:     new password.
  $confirm_pwd: re-typed new password.
 */

function check_pwd_change($db, $username, $req_old_pwd, $old_pwd, $new_pwd, $confirm_pwd)
{
    $response = '';

    if ($req_old_pwd) {
        // check old password entered and correct
        if (!strlen($old_pwd)) {
            $response = "You must enter the old password for user <b>$username</b>.";
            return $response;
        } else {
            if (!compare_passwords($db, $username, $old_pwd)) {
                $response = "You have entered an incorrect password for user " .
                    "<b>$username</b>.  Please try again.";
                return $response;
            }
        }
    }

    if (!(strlen($new_pwd)) || !(strlen($confirm_pwd))) {
        if (!strlen($new_pwd))
            $response .= "You must enter a new password for user " .
                "<b>$username</b>.<br>";
        if (!strlen($confirm_pwd))
            $response .= "You must confirm the new password for " .
                "user <b>$username</b>.<br>";
    } else {
        if ($new_pwd != $confirm_pwd)
            $response = "The <b>New Password</b> and <b>Confirm New Password</b> " .
                "entries do not match. Please try again.<br>";
        else {
            // Good to go
            $response = "success";
        }
    }

    return $response;
}

function display_summary($msg, $sitename, $regcode, $db)
{
    $sitename = stripslashes($sitename);
    newlines(1);

    table_header(0, 0, 6);
    $args = array("<span class = 'blue'><b>Site name:</b> </span>", "<b>$sitename</b>");
    table_data($args, 0, 'left');
    $args = array("<span class = 'blue'><b>Registration code:</b> </span>", "<b>$regcode</b>");
    table_data($args, 0, 'left');
    table_footer();

    newlines(1);

    table_header(0, 0, 6);
    $msg .= "<br><br>\n";
    $msg .= "<hr color='#333399' align='left' noshade size='1'><br>\n";
    $msg .= <<< HERE

<p class=MsoNormal style='margin-bottom:12.0pt'><span style='font-size:10.0pt;font-family:Verdana'>The instructions that you will
find below should give you sufficient information for installing the ASI client
at a brand new site. Please refer to the Automated Support Infrastructure
client installation user guide for additional information on the ASI client
installation process.<br>
<br>
ASI client installation instructions</span></p>

<ul type=square>
 <li class=MsoNormal style='margin-bottom:12.0pt;'><span style='font-size:10.0pt;font-family:Verdana'>You must remember the site
     registration code listed above, although you can come back to the ASI
     installation management facility to get it in case you forget. Otherwise,
     you won't be able to complete deployment of the ASI client at the site
     listed above, if it is new.</span></li>
 <li class=MsoNormal style='margin-bottom:12.0pt;'><span style='font-size:10.0pt;font-family:Verdana'>In order to install the ASI
     client at the site listed above, you should run the ASI client
     installation executable on every system at the site where the ASI client
     is not installed. You can download the ASI client installation executable
     from <a href="ftp://ftp.handsfreenetworks.com/">ftp.handsfreenetworks.com</a>
     using your usual user id and password.<br>
     <br>
     Please note that as the ASI client runs as a service, first-time
     installation on systems running Microsoft Windows NT4, 2000, XP, or Server
     2003 operating systems has to take place with a user with full
     administrative rights logged in. Otherwise, the ASI service cannot be
     installed and the ASI client will not run correctly.</span></li>
 <li class=MsoNormal style='margin-bottom:12.0pt;'><span style='font-size:10.0pt;font-family:Verdana'>After execution of the ASI
     client installation executable on a system is completed, and the ASI
     client starts running, it is in a <i>dormant</i> configuration with a
     minimum number of Scrips enabled.</span></li>
</ul>

<p class=MsoBodyTextIndent style='margin-left:.5in'><span style='font-family:
Verdana'>One of the Scrips that is enabled is the ASI client installation and
deployment Scrip (#223). It runs as soon as the ASI client installation on a
system is completed, and checks whether or not the ASI client has been
configured. If it hasn't (as is the case when the ASI client is installed on a
new system), the Scrip generates a dialog box asking for the ten-digit site
registration code.</span></p>

<p class=MsoBodyTextIndent style='margin-left:.5in'><span style='font-family:
Verdana;'>If the ASI client is installed on
a system connected to a sub-net where the ASI client is already installed and
running on other systems, it performs a series of tasks to facilitate the site
registration code entry process:</span></p>

<p class=MsoBodyTextIndent style='margin-left:.75in;text-indent:-.25in;'><span
style='font-family:Wingdings;'><span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</span></span><span style='font-family:Verdana;'>If the ASI client sees responses to the broadcast it sends after
the with execution of the installation executable is completed with more than
one site code, then it will leave the site registration code box in the dialog
box empty, and the dialog box includes the message:  &quot;No default is provided because multiple codes are currently
accessible on this sub-net&quot;.</span></p>

<p class=MsoBodyTextIndent style='margin-left:.75in;text-indent:-.25in;'><span
style='font-family:Wingdings;'><span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</span></span><span style='font-family:Verdana;'>If the ASI client doesn't see any responses to its broadcast, it
doesn't enter any suggested site code, and the dialog box includes the message:
&quot;No default code is provided because no codes are currently accessible on
this sub-net&quot;.</span></p>

<p class=MsoBodyTextIndent style='margin-left:.75in;text-indent:-.25in;'><span
style='font-family:Wingdings;'><span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</span></span><span style='font-family:Verdana;'>If the ASI client sees responses to its broadcast with only one
site registration code, it uses that site registration code as a suggested
value, and the dialog box includes the message: &quot;The default code provided
is the only one currently accessible on this sub-net&quot;. Please note that
the person performing the installation needs to explicitly accept the suggested
site registration code value.</span></p>

<p class=MsoBodyTextIndent style='margin-left:.75in;text-indent:-.25in;'><span
style='font-family:Wingdings;'><span
style='font:7.0pt "Times New Roman"'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
</span></span><span style='font-family:Verdana;'>If the site registration code suggested by the ASI client (as
described in the step above) is one generated by the ASI client, as in the case
of versions of the client that are installed using a custom installation
executable (one that has a cust.ini file), then it will be used as the
suggested site registration code. However, if the person performing the
installation enters a legacy code, </span><span style='font-family:Verdana'>the
ASI client installation and deployment Scrip will reject it</span><span
style='font-family:Verdana;'>, and the
rejection is accompanied by a dialog box that says: &quot;Site registration
codes for sites where the ASI client was originally installed with a custom
installation executable are not valid&quot;.</span></p>

<p class=MsoNormal style='margin-left:.5in'><span style='font-size:10.0pt;font-family:Verdana'>The ASI client installation and
deployment Scrip checks the internal consistency of the site registration code,
using the check digit, and keeps asking until it gets one that is internally
consistent. <br>
<br>
Please note that as many as 15-30 seconds elapse from execution of the ASI
client installation executable until the dialog box asking for the ten-digit
site registration code is displayed.<br>
<br>
If you run the ASI client installation executable on multiple systems at the
same time (e.g. via a login script), the dialog box will be generated on all
the systems. This dialog box has a timeout. It automatically goes away after 60
minutes. Then, after 15 minutes, the systems on which it went away, will retry
to obtain a valid site registration code, bringing up the dialog box again.
This cycle is repeated until a valid site registration code is either </span><span
style='font-size:10.0pt;font-family:Verdana;'>entered by the person performing the installation,</span><span
style='font-size:10.0pt;font-family:Verdana'> or
obtained from a neighboring system and accepted by the person performing the
installation.<br>
<br>
In order to automate the entry of the site registration code, you can run the
ASI client installation executable using the </span><span style='font-size:
10.0pt;font-family:Verdana;'>/ID=XXXXXXXXXX command line parameter.</span><span
style='font-size:10.0pt;font-family:Verdana'> <br>
<br>
</span><span style='font-size:10.0pt;font-family:
Verdana;'>It lets you enter the registration
code for a site in the ASI client installation executable command line
eliminating the need for any user interaction. The letters in ID are not case
sensitive. You can have iD, Id, id etc... There should not be any spaces in the
string. There should only be a space that separates the name of the ASI client
installation executable file from the command line argument. </span><span
style='font-size:10.0pt;font-family:Verdana'><br>
<br>
</span><span style='font-size:10.0pt;font-family:
Verdana;'>For example:</span><span
style='font-size:10.0pt;font-family:Verdana'>  </span><span style='font-size:10.0pt;font-family:Verdana;'>hfn-inst-160665.exe
/ID=XXXXXXXXXX</span></p>

<ul type=square>
 <li class=MsoNormal style='margin-bottom:12.0pt;'><span style='font-size:10.0pt;font-family:Verdana'>Once the ASI client installation
     and deployment Scrip has the ten-digit site registration code, it contacts
     the ASI installation management facility (the facility your are logged
     onto now) asking for the site installation and deployment information.
     This is the information that you just entered for the site. This
     information allows the ASI client to be deployed for operational use. <br>
     <br>
     Please note that first-time ASI client installation always requires the
     entry of a site registration code, even at sites where the ASI client is
     already installed and the system where the ASI client is being installed
     is connected to a sub-net with systems where the ASI client is already
     installed and running.</span></li>
</ul>
HERE;

    $args = array($msg);
    table_data($args, 0, 'left');
    table_footer();
}

function get_numconnects($id, $db)
{
    $numconnects = '';
    $sql = "select numconnects from " . $GLOBALS['PREFIX'] . "install.Sites where siteid = $id";
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_array($res);
            $numconnects = $row['numconnects'];
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $numconnects;
}

function update_site($id, $authuser, $db)
{
    $sql_pwd = '';
    $msg = '';
    $problem = 0;
    $xtra_msg = '';

    $sitename = trim(get_argument('sitename', 1, ''));
    $domain = trim(get_argument('domain', 1, ''));
    $username = trim(get_argument('username', 1, ''));
    $password = trim(get_argument('password', 0, ''));
    $confirm_pwd = trim(get_argument('confirmpassword', 0, ''));
    $email = trim(get_argument('email', 1, ''));
    $serverid = intval(get_argument('serverid', 1, 0));
    $skuids = intval(get_argument('skulist', 1, 0));
    $wsurl = trim(get_argument('wsurl', 0, ''));
    $cid = intval(get_argument('custlist', 1, 0));
    $proxy = trim(get_argument('proxy', 0, ''));
    $startupid = trim(get_argument('startupid', 1, 'All'));
    $followonid = trim(get_argument('followonid', 1, 'All'));
    $delay_days = intval(get_argument('delay_days', 0, 0));
    $delay_hrs = intval(get_argument('delay_hrs', 0, 0));
    $delay_mins = intval(get_argument('delay_mins', 0, 0));
    $regcode = trim(get_argument('regcode', 0, ''));
    $deployPath32 = trim(get_argument('deploypath32', 0, ''));
    $deployPath64 = trim(get_argument('deploypath64', 0, ''));
    $fcmUrl = trim(get_argument('fcmUrl', 0, ''));
    $emailbounce = trim(get_argument('emailbounce', 0, ''));
    $urldownload = trim(get_argument('urldownload', 0, ''));
    $messagetext = trim(get_argument('messagetext', 1, ''));
    $emailsubject = trim(get_argument('emailsubject', 1, ''));
    $emailsender = trim(get_argument('emailsender', 1, ''));
    $emailxheaders = trim(get_argument('emailxheaders', 1, ''));

    $uninstall = ($followonid == 'Uninstall') ? 1 : 0;

    // if urldownload starts w/ host (e.g. [//]www.cool-site-4-u.com), prepend w/ http:[//]
    if (preg_match('/^(\/\/)?[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+/', $urldownload, $matches)) {
        $prepend = 'http:';
        if (!isset($matches[1]) || $matches[1] != '//')
            $prepend .= "//";
        $urldownload = $prepend . $urldownload;
    }

    // check for blank site name
    if (!strlen($sitename)) {
        $msg = "Site name cannot be blank.";
    }

    /* Check for case-sensitive only change to the site name */
    $sql = "SELECT sitename FROM Sites WHERE siteid=$id";
    $thisSite = find_site($id, $db);
    if ($thisSite) {
        if ((strcmp($thisSite['sitename'], $sitename) != 0) &&
            (strcasecmp($thisSite['sitename'], $sitename) == 0)
        ) {
            $msg = "Cannot adjust the case-sensitivity of a site.";
        }
    }

    // check for password
    if ($msg == '') {
        if (strlen($password) || strlen($confirm_pwd)) {
            $response = check_pwd_change($db, $username, 0, '', $password, $confirm_pwd);
            if ($response == "success")
                $sql_pwd = " password='" . md5($password) . "',\n";
            elseif (strlen(trim($response)))
                $msg = $response;
            else
                $msg = "There was a problem with this update. Please try again.";
        }
    }

    // upload enhancement start

    $client32Upload = $client64Upload = false;
    $client32Name = 'executable_client_32';
    $client64Name = 'executable_client_64';
    $clientApkName = 'executable_client_apk';
    $clientMacName = 'executable_client_mac';
    $clientIosName = 'executable_client_ios';
    $clientLinuxName = 'executable_client_linux';

    if (isset($_FILES[$client32Name]) && isset($_FILES[$client32Name]['name']) && !empty($_FILES[$client32Name]['name'])) {
        if (!isset($_FILES[$client32Name]['error']) || $_FILES[$client32Name]['error'] != 0) {
            $msg = "Client 32 bit upload error";
        }
        $client32Upload = true;
        $client32FileName = $_FILES[$client32Name]['name'];
    }

    if (isset($_FILES[$client64Name]) && isset($_FILES[$client64Name]['name']) && !empty($_FILES[$client64Name]['name'])) {
        if (!isset($_FILES[$client64Name]['error']) || $_FILES[$client64Name]['error'] != 0) {
            $msg = "Client 64 bit upload error";
        }
        $client64Upload = true;
        $client64FileName = $_FILES[$client64Name]['name'];
    }

    if (isset($_FILES[$clientApkName]) && isset($_FILES[$clientApkName]['name']) && !empty($_FILES[$clientApkName]['name'])) {
        if (!isset($_FILES[$clientApkName]['error']) || $_FILES[$clientApkName]['error'] != 0) {
            $msg = "Android Client upload error";
        }
        $clientApkUpload = true;
        $clientApkFileName = $_FILES[$clientApkName]['name'];
    }

    if (isset($_FILES[$clientMacName]) && isset($_FILES[$clientMacName]['name']) && !empty($_FILES[$clientMacName]['name'])) {
        if (!isset($_FILES[$clientMacName]['error']) || $_FILES[$clientMacName]['error'] != 0) {
            $msg = "Mac Client upload error";
        }

        $clientMacUpload = true;
        $clientMacFileName = $_FILES[$clientMacName]['name'];
    }
    if (isset($_FILES[$clientIosName]) && isset($_FILES[$clientIosName]['name']) && !empty($_FILES[$clientIosName]['name'])) {
        if (!isset($_FILES[$clientIosName]['error']) || $_FILES[$clientIosName]['error'] != 0) {
            $msg = "Ios Client upload error";
        }

        $clientIosUpload = true;
        $clientIosFileName = $_FILES[$clientIosName]['name'];
    }

    if (isset($_FILES[$clientLinuxName]) && isset($_FILES[$clientLinuxName]['name']) && !empty($_FILES[$clientLinuxName]['name'])) {
        if (!isset($_FILES[$clientLinuxName]['error']) || $_FILES[$clientLinuxName]['error'] != 0) {
            $msg = "Linux Client upload error";
        }

        $clientLinuxUpload = true;
        $clientLinuxFileName = $_FILES[$clientLinuxName]['name'];
    }

    if ($client32Upload) {
        $upoadFtpData = uploadWithFtp($client32Name);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($client64Upload) {
        $upoadFtpData = uploadWithFtp($client64Name);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientApkUpload) {
        $upoadFtpData = uploadWithFtp($clientApkName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientMacUpload) {
        $upoadFtpData = uploadWithFtp($clientMacName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientIosUpload) {
        $upoadFtpData = uploadWithFtp($clientIosName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientLinuxUpload) {
        $upoadFtpData = uploadWithFtp($clientLinuxName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }


    if ($msg == '') {
        // calculate delay
        $delay = ($delay_days * 1440) + ($delay_hrs * 60) + $delay_mins;
        // insert into Sites table [1]
        // update Sites table
        $sql = "update Sites set";
        $sql .= " sitename='$sitename',\n";
        $sql .= " domain='$domain',\n";
        $sql .= " username='$username',\n";
        if (strlen(trim($sql_pwd)))
            $sql .= $sql_pwd;
        $sql .= " email='$email',\n";
        $sql .= " serverid=$serverid,\n";
        $sql .= " skuids=$skuids,\n";
        $sql .= " wsurl='$wsurl',\n";
        $sql .= " cid=$cid,\n";
        $sql .= " proxy='$proxy',\n";
        $sql .= " startupid='$startupid',\n";
        $sql .= " followonid='$followonid',\n";
        $sql .= " uninstall='$uninstall',\n";
        $sql .= " delay=$delay,\n";
        $sql .= " deploypath32='$deployPath32',\n";
        $sql .= " deploypath64='$deployPath64',\n";
        $sql .= " fcmUrl='$fcmUrl',\n";
        $sql .= " emailbounce='$emailbounce',\n";
        $sql .= " urldownload='$urldownload',\n";
        $sql .= " messagetext='$messagetext',\n";
        $sql .= " emailsubject='$emailsubject',\n";
        $sql .= " emailsender='$emailsender',\n";
        $sql .= " emailxheaders='$emailxheaders'\n";

        if ($client32Upload) {
            $sql .= ", client_32_name='$client32FileName'\n";
        }

        if ($client64Upload) {
            $sql .= ", client_64_name='$client64FileName'\n";
        }

        if ($clientApkUpload) {
            $sql .= ", client_android_name='$clientApkFileName'\n";
        }

        if ($clientMacUpload) {
            $sql .= ", client_mac_name='$clientMacFileName'\n";
        }

        if ($clientIosUpload) {
            $sql .= ", client_ios_name='$clientIosFileName'\n";
        }

        if ($clientLinuxUpload) {
            $sql .= ", client_linux_name='$clientLinuxFileName'\n";
        }

        $sql .= " where siteid = $id";
        $res = redcommand($sql, $db);
        TokenChecker::calcSites($id);

        $sitename = stripslashes($sitename);

        if (!$res) {
            $problem = 1;
            $sql_error = mysqli_error($GLOBALS["___mysqli_ston"]);
            $sql_errno = mysqli_errno($GLOBALS["___mysqli_ston"]);

            // check for duplicate site name
            $key_index = get_key_index('install', 'Sites', 'uniq', $db);
            if ($sql_errno == 1062) { // 1062 is the error code for dup entry
                if (preg_match("/\b$key_index\b/", $sql_error)) {
                    $xtra_msg = "The site name <b>$sitename</b> is a duplicate
                                 of an existing site name.";
                }
            }
        }

        if ($problem) {
            $msg = "Unable to update site <b>$sitename</b>. $xtra_msg";
        } else {
            $message = "Site <b>$sitename</b> updated.";
            $log = "install: Site '$sitename' updated by $authuser.";
            logs::log(__FILE__, __LINE__, $log, 0);

            // Make sure clients haven't already gotten old settings
            if (mysqli_affected_rows($GLOBALS["___mysqli_ston"])) {
                // insert into Sites table [1]
                // update numedits
                $sql = "update Sites set";
                $sql .= " numedits= numedits + 1\n";
                $sql .= " where siteid = $id";
                $res = redcommand($sql, $db);
                TokenChecker::calcSites($id);

                $numconnects = get_numconnects($id, $db);
                if ($numconnects) {
                    $are = ($numconnects > 1) ? 'are' : 'is';
                    $clients = ($numconnects > 1) ? 'clients' : 'client';
                    $These = ($numconnects > 1) ? 'These' : 'This';
                    $message .= "<br><br>Note that there $are already $numconnects" .
                        " $clients using the data you changed." .
                        " $These $clients will not be updated with the " .
                        " changes you made until the next time a" .
                        " client is installed at site <b>$sitename</b>.";
                }
            }
            display_summary($message, $sitename, $regcode, $db);
        }
    }

    message($msg);
}

function find_site($id, $db)
{
    $site = array();
    $sql = "select * from " . $GLOBALS['PREFIX'] . "install.Sites where siteid = $id";
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $site = mysqli_fetch_array($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $site;
}

function confirm_delete_site($id, $db)
{
    $site = find_site($id, $db);
    if ($site) {
        $self = server_var('PHP_SELF');
        $self = preg_replace('/\?.+/', '', $self);
        $referer = server_var('HTTP_REFERER');
        $href = "$self?action=act-delete&id=$id";
        $yes = "[<a href='$href'>Yes</a>]";
        $no = "[<a href='$referer'>No</a>]";

        $sitename = $site['sitename'];
        $msg = "Are you sure you want to delete site <b>$sitename</b>?<br>";
        $msg .= "<br>";
        $msg .= "$yes&nbsp;&nbsp;&nbsp;$no";
        message($msg);
    }
}

function delete_site($id, $authuser, $db)
{
    $site = find_site($id, $db);
    if ($site) {
        $sitename = $site['sitename'];
        $log = "install: Site '$sitename' removed by $authuser.";
        logs::log(__FILE__, __LINE__, $log, 0);
        $sql = "DELETE FROM Sites WHERE siteid = $id";
        $res = redcommand($sql, $db);
        if ($res) {
            $sql = "DELETE FROM Siteemail WHERE siteid = $id"; // SE fix skip 
            redcommand($sql, $db);
            $msg = "Site <b>$sitename</b> has been removed.";
        } else {
            $msg = "There was a problem deleting site <b>$sitename</b>. Please try again.";
        }
    } else {
        $msg = "Site <b>$id</b> does not exist.";
    }

    message($msg);
}

function make_seed()
{
    list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}

/*
  The regcode is 9 digits plus a 10 check digit.

  The first nine digits are created as follow:
  1)  MD5 the site name.
  2)  Take the first 8 digits and convert them from a hexadecimal string
  to a number (for example "FFFFFFFF" will become 4294967295).
  3)  Because the resulting hex when represented as a signed 32-bit integer
  can appear negative, AND it with the number 7FFFFFFF.
  4)  Take the remainder of that mod 1 billion.
  If the result already exists in the database, add 1 and take
  mod 1 billion until a unique code is found
  5)  Indicate that this regcode was generated by the server (not the client)
  in the least significant bit by "ANDing" the result with 0xFFFFFFF0.
  (Actually reserve 4 LSBs for future use).
  LSB:  1 = generated by client; 0 = generated by server.
  (The other 3 bits will always be zero for now.)

  To create the checkdigit, each of the first 9 digits
  is given a weight (the first digit has a weight of 10, the second 9, and so
  on down to 2). Then each digit is multiplied by the weight and the products
  are added together.

  The check digit is generated by starting with the remainder of the sum of
  the weighted-digit products divided by 11.  That remainder is subtracted from 11
  and the result is divided by 11.  The remainder of that operation will be
  the check digit.

  The regcode is valid if the sum of the weighted-digit products PLUS checkdigit
  is evenly divisable by 11:
  (($dig1 * 10) + ($dig2 * 9) + ($dig3 * 8) + ($dig4 * 7) + ($dig5 * 6) +
  ($dig6 * 5) + ($dig7 * 4) + ($dig8 * 3) + ($dig9 * 2) + $checkdig) % 11 == 0
 */

function gen_regcode($sitename, $regen = 0)
{
    $step1 = md5($sitename);
    $step2 = hexdec(substr($step1, 0, 8));
    $positive = hexdec("7FFFFFFF");
    $step3 = $step2 & $positive;
    $step4 = $step3 % 1000000000;
    $servergen = hexdec("FFFFFFF0");
    $step5 = $step4 & $servergen;

    if ($regen) { // came back b/c previously generated reg code was a dup of existing.
        $log = "install: The regcode generated for site '$sitename' is a duplicate" .
            " of existing; generating new regcode.";
        logs::log(__FILE__, __LINE__, $log, 0);

        // seed with microseconds
        srand(make_seed());
        $rand = rand(1, 99);
        $incr = $rand * 16;   // use 16, not 1, since lowest 4 bits are reserved
        $step5 += $incr;
        $step5 = $step5 % 1000000000;  // in case it got too long
    }

    // pad number if less than 9 digits
    $number = sprintf("%09d", $step5);

    // get first 9 digits
    $dig1 = substr($number, 0, 1);
    $dig2 = substr($number, 1, 1);
    $dig3 = substr($number, 2, 1);
    $dig4 = substr($number, 3, 1);
    $dig5 = substr($number, 4, 1);
    $dig6 = substr($number, 5, 1);
    $dig7 = substr($number, 6, 1);
    $dig8 = substr($number, 7, 1);
    $dig9 = substr($number, 8, 1);

    // generate check digit
    $intermediate = ($dig1 * 10) + ($dig2 * 9) + ($dig3 * 8) + ($dig4 * 7) + ($dig5 * 6) + ($dig6 * 5) + ($dig7 * 4) + ($dig8 * 3) + ($dig9 * 2);
    $remainder = $intermediate % 11;
    $checkdig = (11 - $remainder) % 11;
    if ($checkdig == 10)
        $checkdig = 'X';

    $regcode = $dig1 . $dig2 . $dig3 . $dig4 . $dig5 . $dig6 . $dig7 . $dig8 . $dig9 . $checkdig;

    return $regcode;
}

function uploadWithFtp($name)
{
    $fileName = $_FILES[$name]['name'];
    $fileInfo = pathinfo($fileName);
    $fileExtension = isset($fileInfo['extension']) ? $fileInfo['extension'] : false;

    if (!$fileExtension) {
        return ['status' => false, 'message' => "File extension not found"];
    }

    $uploadDir = '/home/nanoheal/setups/live';
    $newFileName = $fileName;
    $location = $uploadDir . "/" . $newFileName;
    @move_uploaded_file($_FILES[$name]['tmp_name'], $location);

    /*  global $ftp_server;
    global $ftp_username;
    global $ftp_userpass;
    global $ftp_downloadpath;

    $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
    $login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);
    $remoteLocation = '/home/nanoheal/setups/live/' . $newFileName;
    if (!ftp_put($ftp_conn, $remoteLocation, $location, FTP_BINARY)) {
        return ['status' => false, 'message' => "Unable to upload file with ftp"];
    } else {
        @ftp_chmod($ftp_conn, 0777, $remoteLocation);
    }

    ftp_close($ftp_conn);
    @unlink($location); */

    return ['status' => true];
}

function insert_site($authuser, $userid, $id, $db, $regen = 0)
{
    $sql_pwd = '';
    $msg = '';
    $problem = 0;
    $xtra_msg = '';

    $sitename = trim(get_argument('sitename', 1, ''));
    $domain = trim(get_argument('domain', 1, ''));
    $username = trim(get_argument('username', 1, ''));
    $password = trim(get_argument('password', 0, ''));
    $confirm_pwd = trim(get_argument('confirmpassword', 0, ''));
    $email = trim(get_argument('email', 1, ''));
    $serverid = intval(get_argument('serverid', 1, 0));
    $cid = intval(get_argument('custlist', 1, 0));
    $proxy = trim(get_argument('proxy', 0, ''));
    $startupid = trim(get_argument('startupid', 1, 'All'));
    $followonid = trim(get_argument('followonid', 1, 'All'));
    $delay_days = intval(get_argument('delay_days', 0, 0));
    $delay_hrs = intval(get_argument('delay_hrs', 0, 0));
    $delay_mins = intval(get_argument('delay_mins', 0, 0));
    $delay_on = intval(get_argument('delay_on', 0, 0));
    $deployPath32 = trim(get_argument('deploypath32', 0, ''));
    $deployPath64 = trim(get_argument('deploypath64', 0, ''));
    $fcmUrl = trim(get_argument('fcmUrl', 0, ''));
    $emailbounce = trim(get_argument('emailbounce', 0, ''));
    $urldownload = trim(get_argument('urldownload', 0, ''));
    $messagetext = trim(get_argument('messagetext', 1, ''));
    $emailsubject = trim(get_argument('emailsubject', 1, ''));
    $emailsender = trim(get_argument('emailsender', 1, ''));
    $emailxheaders = trim(get_argument('emailxheaders', 1, ''));
    $uninstall = ($followonid == 'Uninstall') ? 1 : 0;
    $skuids = intval(get_argument('skulist', 1, 0));
    $wsurl = trim(get_argument('wsurl', 0, ''));

    // if urldownload starts w/ host (e.g. [//]www.cool-site-4-u.com), prepend w/ http:[//]
    if (preg_match('/^(\/\/)?[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+/', $urldownload, $matches)) {
        $prepend = 'http:';
        if (!isset($matches[1]) || $matches[1] != '//')
            $prepend .= "//";
        $urldownload = $prepend . $urldownload;
    }

    // check for blank site name
    if (!strlen($sitename)) {
        $msg = "Site name cannot be blank.";
    } else if (strpos($sitename, " ") !== false) {
        $msg = "Site name cannot contain space";
    }

    // check for password
    if ($msg == '') {
        if (strlen($password) || strlen($confirm_pwd)) {
            $response = check_pwd_change($db, $username, 0, '', $password, $confirm_pwd);
            if ($response == "success")
                $sql_pwd = " password='" . md5($password) . "',\n";
            elseif (strlen(trim($response)))
                $msg = $response;
            else
                $msg = "There was a problem with this update. Please try again.";
        } else { // use default sitepassword if it matches the (non-blank) siteusername.
            if (strlen($username)) {
                $userdata = get_user_data($userid, $db);
                $siteusername = $userdata['siteusername'];
                $sitepassword = $userdata['sitepassword'];
                if ($username == $siteusername) {
                    $password = $userdata['password'];
                    $sql_pwd = " password='$password',\n";
                }
            }
        }
    }

    // upload enhancement start

    $client32Upload = $client64Upload = $clientApkUpload = $clientMacUpload = $clientIosUpload = $clientLinuxUpload = false;
    $client32Name = 'executable_client_32';
    $client64Name = 'executable_client_64';
    $clientApkName = 'executable_client_apk';
    $clientMacName = 'executable_client_mac';
    $clientIosName = 'executable_client_ios';
    $clientLinuxName = 'executable_client_linux';

    if (isset($_FILES[$client32Name]) && isset($_FILES[$client32Name]['name']) && !empty($_FILES[$client32Name]['name'])) {
        if (!isset($_FILES[$client32Name]['error']) || $_FILES[$client32Name]['error'] != 0) {
            $msg = "Client 32 bit upload error";
        }
        $client32Upload = true;
        $client32FileName = $_FILES[$client32Name]['name'];
    }

    if (isset($_FILES[$client64Name]) && isset($_FILES[$client64Name]['name']) && !empty($_FILES[$client64Name]['name'])) {
        if (!isset($_FILES[$client64Name]['error']) || $_FILES[$client64Name]['error'] != 0) {
            $msg = "Client 64 bit upload error";
        }
        $client64Upload = true;
        $client64FileName = $_FILES[$client64Name]['name'];
    }

    if (isset($_FILES[$clientApkName]) && isset($_FILES[$clientApkName]['name']) && !empty($_FILES[$clientApkName]['name'])) {
        if (!isset($_FILES[$clientApkName]['error']) || $_FILES[$clientApkName]['error'] != 0) {
            $msg = "Android Client upload error";
        }

        $clientApkUpload = true;
        $clientApkFileName = $_FILES[$clientApkName]['name'];
    }

    if (isset($_FILES[$clientMacName]) && isset($_FILES[$clientMacName]['name']) && !empty($_FILES[$clientMacName]['name'])) {
        if (!isset($_FILES[$clientMacName]['error']) || $_FILES[$clientMacName]['error'] != 0) {
            $msg = "Mac Client upload error";
        }

        $clientMacUpload = true;
        $clientMacFileName = $_FILES[$clientMacName]['name'];
    }
    if (isset($_FILES[$clientIosName]) && isset($_FILES[$clientIosName]['name']) && !empty($_FILES[$clientIosName]['name'])) {
        if (!isset($_FILES[$clientIosName]['error']) || $_FILES[$clientIosName]['error'] != 0) {
            $msg = "Ios Client upload error";
        }

        $clientIosUpload = true;
        $clientIosFileName = $_FILES[$clientIosName]['name'];
    }

    if (isset($_FILES[$clientLinuxName]) && isset($_FILES[$clientLinuxName]['name']) && !empty($_FILES[$clientLinuxName]['name'])) {
        if (!isset($_FILES[$clientLinuxName]['error']) || $_FILES[$clientLinuxName]['error'] != 0) {
            $msg = "Linux Client upload error";
        }

        $clientLinuxUpload = true;
        $clientLinuxFileName = $_FILES[$clientLinuxName]['name'];
    }

    if ($client32Upload) {
        $upoadFtpData = uploadWithFtp($client32Name);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($client64Upload) {
        $upoadFtpData = uploadWithFtp($client64Name);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientApkUpload) {
        $upoadFtpData = uploadWithFtp($clientApkName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientMacUpload) {
        $upoadFtpData = uploadWithFtp($clientMacName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientIosUpload) {
        $upoadFtpData = uploadWithFtp($clientIosName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($clientLinuxUpload) {
        $upoadFtpData = uploadWithFtp($clientLinuxName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($msg == '') {
        //calculate delay
        $delay = ($delay_days * 1440) + ($delay_hrs * 60) + $delay_mins;

        /// generate reg code
        $regcode = gen_regcode($sitename, $regen);

        $ssl = (isset($_SERVER['HTTPS'])) ? 1 : 0;
        $host = $_SERVER['HTTP_HOST'];
        $http = ($ssl) ? 'https' : 'http';

        $defBrandingUrl = $http . '://' . $host . '/Provision/install/cust_Default_Branding/cust_Default_Branding.zip';
        // insert into Sites table [1]
        // insert into Sites table
        $sql = "INSERT INTO Sites SET\n";
        $sql .= " sitename='$sitename',\n";
        $sql .= " domain='$domain',\n";
        $sql .= " installuserid='$userid',\n";
        $sql .= " username='$username',\n";
        if (strlen(trim($sql_pwd)))
            $sql .= $sql_pwd;
        $sql .= " email='$email',\n";
        $sql .= " skuids=$skuids,\n";
        $sql .= " wsurl='$wsurl',\n";
        $sql .= " serverid=$serverid,\n";
        $sql .= " cid=$cid,\n";
        $sql .= " proxy='$proxy',\n";
        $sql .= " startupid='$startupid',\n";
        $sql .= " followonid='$followonid',\n";
        $sql .= " uninstall='$uninstall',\n";
        $sql .= " delay=$delay,\n";
        $sql .= " delayon=$delay_on,\n";
        $sql .= " deploypath32='$deployPath32',\n";
        $sql .= " deploypath64='$deployPath64',\n";
        $sql .= " fcmUrl='$fcmUrl',\n";
        $sql .= " emailbounce='$emailbounce',\n";
        $sql .= " urldownload='$urldownload',\n";
        $sql .= " messagetext='$messagetext',\n";
        $sql .= " emailsubject='$emailsubject',\n";
        $sql .= " emailsender='$emailsender',\n";
        $sql .= " emailxheaders='$emailxheaders',\n";
        $sql .= " regcode='$regcode',\n";
        $sql .= " brandingurl='$defBrandingUrl'\n";
        TokenChecker::calcSites($sitename, 'sitename');

        if ($client32Upload) {
            $sql .= ", client_32_name='$client32FileName'\n";
        }

        if ($client64Upload) {
            $sql .= ", client_64_name='$client64FileName'\n";
        }

        if ($clientApkUpload) {
            $sql .= ", client_android_name='$clientApkFileName'\n";
        }

        if ($clientMacUpload) {
            $sql .= ", client_mac_name='$clientMacFileName'\n";
        }

        if ($clientIosUpload) {
            $sql .= ", client_ios_name='$clientIosFileName'\n";
        }

        if ($clientLinuxUpload) {
            $sql .= ", client_linux_name='$clientLinuxFileName'\n";
        }

        $res = redcommand($sql, $db);

        $sitename = stripslashes($sitename);

        if (!$res) {
            $problem = 1;
            $sql_error = mysqli_error($GLOBALS["___mysqli_ston"]);
            $sql_errno = mysqli_errno($GLOBALS["___mysqli_ston"]);
            echo $sql_error;
            // check for duplicate site name or regcode
            $key_index1 = get_key_index('install', 'Sites', 'uniq', $db);
            $key_index2 = get_key_index('install', 'Sites', 'uniq2', $db);
            if ($sql_errno == 1062) { // 1062 is the error code for dup entry
                // check for duplicate site name
                if (preg_match("/\b$key_index1\b/", $sql_error)) {
                    $xtra_msg = "The site name <b>$sitename</b> is a duplicate
                                    of an existing site name.";
                }
                // check for duplicate regcode
                elseif (preg_match("/\b$key_index2\b/", $sql_error)) {
                    // try again, with regen set to 1
                    insert_site($authuser, $userid, $id, $db, 1);
                }
            }
        }


        if ($problem) {
            $msg = "Unable to add site <b>$sitename</b>. $xtra_msg";
        } else {
            // Creating default site email entry for current user
            $message = "New site <b>$sitename</b> added.";
            $siteid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
            $siteemailid = insertDefaultSiteEmailEntry($userid, $siteid, $db);
            if ($siteemailid != 'EXIST') {
                $message .= "<br/>You have entered <b>1</b> new email $email for site <b>$sitename</b>.";
            }

            $userdata = get_user_data($userid, $db);
            $installuser = $userdata['installuser'];
            /* $asicon = SVBT_coreServerConnect($installuser, $db);
              if($asicon['status'] == 1) {
              SVBT_createASICoreCustomers($sitename, $email, $db, $asicon['dbcon']);
              } */

            $apisql = "select serverurl from Servers where serverid = '$serverid'";
            $apires = command($apisql, $db);

            if (mysqli_num_rows($apires) > 0) {
                $data = mysqli_fetch_array($apires);
                $apiurl = $data['serverurl'];
            }

            $cdata['function'] = 'createcustomer';
            $cdata['data']['sitename'] = $sitename;
            $cdata['data']['emailid'] = $email;
            MAKE_CURL_CALL($apiurl, $cdata);

            $sdata['function'] = 'createsite';
            $sdata['data']['sitename'] = $sitename;
            $sdata['data']['domain'] = $domain;
            $sdata['data']['userid'] = $userid;
            $sdata['data']['username'] = $username;
            $sdata['data']['password'] = $password;
            $sdata['data']['email'] = $email;
            $sdata['data']['serverid'] = $serverid;
            $sdata['data']['proxy'] = $proxy;
            $sdata['data']['startupid'] = $startupid;
            $sdata['data']['followonid'] = $followonid;
            $sdata['data']['uninstall'] = $uninstall;
            $sdata['data']['delay'] = $delay;
            $sdata['data']['delayon'] = $delay_on;
            $sdata['data']['deploypath32'] = $deployPath32;
            $sdata['data']['deploypath64'] = $deployPath64;
            $sdata['data']['emailbounce'] = $emailbounce;
            $sdata['data']['urldownload'] = $urldownload;
            $sdata['data']['messagetext'] = $messagetext;
            $sdata['data']['emailsubject'] = $emailsubject;
            $sdata['data']['emailsender'] = $emailsender;
            $sdata['data']['emailxheaders'] = $emailxheaders;
            $sdata['data']['regcode'] = $regcode;
            $sdata['data']['brandingurl'] = $defBrandingUrl;
            MAKE_CURL_CALL($apiurl, $sdata);

            $log = "install: Site '$sitename' added by $authuser.";
            logs::log(__FILE__, __LINE__, $log, 0);
            display_summary($message, $sitename, $regcode, $db);
        }
    }

    message($msg);
}

function insertDefaultSiteEmailEntry($userid, $siteid, $db)
{

    $userSql = "select installuser, installemailid from Users where installuserid = $userid limit 1";
    $userRes = command($userSql, $db);
    $userData = mysqli_fetch_array($userRes);
    $useremail = $userData['installemailid'];

    $sql = "select * from Siteemail where email = '$useremail' and siteid = $siteid and installuserid = $userid limit 1";
    $res = command($sql, $db);

    if (mysqli_num_rows($res) > 0) {
        $retval = 'EXIST';
    } else {
        $now = time();
        $sql = "INSERT INTO Siteemail SET";
        $sql .= " siteid=$siteid,\n";
        $sql .= " installuserid=$userid,\n";
        $sql .= " email='$useremail',\n";
        $sql .= " createdtime='$now'\n";
        $res = redcommand($sql, $db);

        $siteemailid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        $retval = $siteemailid;
    }
    return $retval;
}

/*
  |  Main program
 */

$db = db_connect();
db_change($GLOBALS['PREFIX'] . 'install', $db);

$authuser = install_login($db);
$authuserdata = install_user($authuser, $db);
$authid = $authuserdata['installuserid'];
$auth_admin = @($authuserdata['priv_admin']) ? 1 : 0;
$auth_servers = @($authuserdata['priv_servers']) ? 1 : 0;
$auth_email = @($authuserdata['priv_email']) ? 1 : 0;

$comp = component_installed();

$action = get_argument('action', 0, 'add');
$reset = get_argument('reset', 0, 0);
if ($reset)
    $action = 'edit';
$id = get_argument('id', 0, 0);

switch ($action) {
    case 'list':
        $title = 'Sites';
        break;
    case 'add':
        $title = 'Add Site Installation and Deployment Information';
        break;
    case 'edit':
        $title = 'Edit Site Installation and Deployment Information';
        break;
    case 'delete':
        $title = 'Confirm Site Delete';
        break;
    case 'act-add':
        $title = 'Adding Site';
        break;
    case 'act-edit':
        $title = 'Updating Site';
        break;
    case 'act-delete':
        $title = 'Deleting Site';
        break;
    default:
        $title = 'Action Unknown';
        break;
}

// Output page header
$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo install_html_header($title, $comp, $authuser, $auth_admin, $auth_servers, $db);
if (trim($msg))
    debug_note($msg);   // ...display any errors to debug users

switch ($action) {
    case 'list':
        display_list($summary, $auth_email, $auth_admin, $authid, $db);
        break;
    case 'add':
        display_site($action, $reset, $id, $authid, $db);
        break;
    case 'edit':
        display_site($action, $reset, $id, $authid, $db);
        break;
    case 'delete':
        confirm_delete_site($id, $db);
        break;
    case 'act-add':
        insert_site($authuser, $authid, $id, $db);
        break;
    case 'act-edit':
        update_site($id, $authuser, $db);
        break;
    case 'act-delete':
        delete_site($id, $authuser, $db);
        break;
    default:
        break;
}

/* Hardwired to pass in hfn for the user. */
$user = 'hfn';
echo head_standard_html_footer($user, $db);
