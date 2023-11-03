<?php

/*
Revision history:

Date        Who     What
----        ---     ----
11-Jun-03   NL      Created by combining maillist.php & mail-act.php
11-Jun-03   NL      Add links to "Action Unknown page".
11-Jun-03   NL      Move detection of %responseurl% in messagetext up
                        to confirm_send_email() and confirm_send_emails().
15-Jun-03   NL      Correct label on delete emails option.
16-Jun-03   NL      Change include line: '../lib/l-head.php' --> 'header.php'.
24-Jun-03   NL      Move delete cboxes to status page
24-Jun-03   NL      Move/dup resend cboxes to status page
24-Jun-03   NL      clear_response(): Fix SQL query
13-July-03  NL      add_addresses(): fix bug: assigning $punct requires a '=' !
13-July-03  NL      manage_emails(): fix title case on form button.
15-Jul-03   NL      Add & process subject, sender, extra headers fields.
21-Jul-03   NL      Add maxlength='255' to email form fields; rearrange order.
23-Jul-03   NL      Change footer to standard_html_footer().
28-Jul-03   NL      Create blue subheadings.
28-Jul-03   NL      Change page titles.
31-Jul-03   EWB     Uses install_login($db);
 7-Aug-03   NL      Change email from plaintext to HTML.
 8-Aug-03   NL      Create email in both HTML and plain text.
11-Aug-03   NL      Back to plain text, just change URL to make it shorter.
12-Aug-03   NL      Oops.  New URL shouldn't have any directories in path.
14-Aug-03   NL      message(): add another newline.
15-Aug-03   NL      add_addresses(): Use get_key_index for dup name.
15-Aug-03   NL      add_addresses(): Don't enter blank lines.
15-Aug-03   NL      Encode email addresses (htmlentities) to allow for quotes & brackets.
25-Aug-03   NL      send_emails(): Populate Errors-to header with $bounceemail.
                    send_emails(): Error message if download URL empty.
28-Aug-03   NL      Use htmlentities() around all email addresses to accomodate quotes.
28-Aug-03   NL      Add help buttons to all email pages.
28-Aug-03   NL      add_addresses(): Match on error code instead of error message.
29-Aug-03   NL      Include lib/l-dberr.php for get_key_index().
29-Aug-03   NL      send_emails(): check for unfully qual'd URLS.
29-Aug-03   NL      Combine confirm_send_emails() into confirm_send_email().
 2-Sep-03   NL      manage_addresses(); manage_emails(): formatting (add a blue line at bottom).
15-Sep-03   NL      Direct help buttons to corresponding help page.
25-Sep-03   NL      Add "install:" and "by $authuser" to all error_log entries;
                    Create entries for all db actions.
30-Sep-03   NL      Add numresponses, numinstalls to email status page (manage_status()).
 3-Oct-03   NL      Clean up long $args; Add some function descriptions.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
19-Jun-07   AAM     Updated index references to match database changes.
03-Oct-08   BTE     Bug 4828: Change customization feature of server.

*/


ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-dberr.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('../lib/l-head.php');
include('header.php');
include('../lib/l-errs.php');
include('../lib/l-cnst.php');
include('../lib/l-smtp.php');

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
    return '&nbsp;|&nbsp;';
}

function jumptable($tags)
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
        echo "[ $msg ]";
    }
}

function table_header($border = 0, $cellspacing = 0, $cellpadding = 0, $align = 'left')
{
    echo "\n<table border='$border' cellspacing='$cellspacing' cellpadding='$cellpadding' align='$align'>\n";
}

function table_data($args, $head, $align = 'center', $valign = 'top', $nowrapcol = 0, $DHTMLid = '')
{
    $i = 1;
    $idstr = (strlen($DHTMLid)) ? "id = '$DHTMLid'" : "";
    $td = ($head) ? "th" : "td";
    if (safe_count($args)) {
        echo "<tr $idstr align='$align' valign='$valign'> \n";
        reset($args);
        foreach ($args as $key => $data) {
            $nowrap = ($i == $nowrapcol) ? "NOWRAP" : "";
            echo "<$td $nowrap $idstr>$data</$td>\n";
            $i++;
        }
        echo "</tr>\n";
    }
}

function table_footer()
{
    echo "\n</table>\n";
    echo "<br clear='all'>\n";
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
    |  Display / List Functions (from erstwhile maillist.php)
    */


function display_welcome($siteid)
{
    if ($siteid) {
        $m .= "To continue, choose a link below:<br>\n ";
        $m .= "<ul>\n ";
        $m .= "<li><a href='emails.php?action=addresses&siteid=$siteid'>manage addresses</a><br>\n";
        $m .= "<li><a href='emails.php?action=emaildistr&siteid=$siteid'>manage distribution</a><br>\n";
        $m .= "<li><a href='emails.php?action=status&siteid=$siteid'>review email status</a><br>\n";
        $m .= "</ul>\n ";
        $m .= "<br>\n";
    } else {
        $m = "[<a href='sites.php?action=list'>Select a site to manage email distribution for</a>]";
    }
    message($m);
}

function get_site_data($siteid, $db)
{
    $sitedata = array();
    $sql  = "SELECT * FROM Sites WHERE siteid = $siteid";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            $row = mysqli_fetch_array($res);
            $sitedata['sitename']       = $row['sitename'];
            $sitedata['installuserid']  = $row['installuserid'];
            $sitedata['email']          = htmlentities($row['email']);
            $sitedata['emailbounce']    = htmlentities($row['emailbounce']);
            $sitedata['urldownload']    = $row['urldownload'];
            $sitedata['messagetext']    = $row['messagetext'];
            $sitedata['emailsubject']   = $row['emailsubject'];
            $sitedata['emailsender']    = htmlentities($row['emailsender']);
            $sitedata['emailxheaders']  = htmlentities($row['emailxheaders']);
            $sitedata['regcode']        = $row['regcode'];
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $sitedata;
}

function get_all_emails_data($siteid, $db)
{
    $emailsdata = array();
    $sql  = "SELECT * FROM Siteemail WHERE siteid=$siteid ORDER BY email";
    $res  = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $id   = $row['siteemailid'];
                $emailsdata[$id]['siteid']          = $row['siteid'];
                $emailsdata[$id]['installuserid']   = $row['installuserid'];
                $emailsdata[$id]['email']           = htmlentities($row['email']);
                $emailsdata[$id]['sent']            = $row['sent'];
                $emailsdata[$id]['response']        = $row['response'];
                $emailsdata[$id]['numresponses']    = $row['numresponses'];
                $emailsdata[$id]['installed']       = $row['installed'];
                $emailsdata[$id]['numinstalls']     = $row['numinstalls'];
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $emailsdata;
}

function display_email_row($id, $row, $db)
{
    $siteid     = $row['siteid'];
    $email      = $row['email'];
    $sent       = ($row['sent'] > 0)       ? "Yes" : "No";
    $response   = ($row['response'] > 0)   ? "Yes (" . $row['numresponses'] . ")" : "No";
    $installed  = ($row['installed'] > 0)  ? "Yes (" . $row['numinstalls'] . ")"  : "No";

    $args[] = $email;
    $args[] = $sent;
    $args[] = $response;
    $args[] = $installed;

    $del    = "<a href='emails.php?action=delete&siteid=$siteid&id=$id'>[delete]</a>";
    $clear  = "<a href='emails.php?action=clear&siteid=$siteid&id=$id'>[clear response confirmation]</a>";
    $send  = "<a href='emails.php?&action=send&siteid=$siteid&id=$id'>[send email to this address]</a>";

    $args[] = "$del<br>$clear<br>$send";

    table_data($args, 0, 'left', 'top', 1);
}

function manage_addresses($siteid)
{
    // form variabless
    $self   = preg_replace('/\?.+/', '', server_var('PHP_SELF'));
    $agent    = server_var('HTTP_USER_AGENT');
    $netscape = strstr($agent, 'compatible') ? '0' : '1';
    $size50 = ($netscape) ? '20' : '50';
    $size40 = ($netscape) ? '20' : '40';

    echo "<form method='post' action='$self' enctype='multipart/form-data'>\n" .
        "<input type='hidden' name='action' value='act-addall'>\n" .
        "<input type='hidden' name='siteid' value='$siteid'>\n";

    table_header();

    $subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
    $subhead .= "<span class='subheading'>ADD ADDRESSES</span>";
    echo span_data(2, $subhead);

    $label = '<b>Type new addresses: </b>';
    $help  = "<br><span class=footnote>Enter one email address per line.</span>";
    $field = "<textarea name='emaillist' rows=5 cols=$size50></textarea>\n";
    $args  = array("$label$help", $field);
    table_data($args, 0, 'left');

    $label = '<b>Upload a file of addresses: &nbsp;</b>';
    $help  = "<br><span class=footnote>Upload a text file containing<br>" .
        " one email address per line.</span>'";
    $field = "<input type='file' name='emailfile' size=$size50></input>\n" .
        "<br><br>\n<table><tr>\n" .
        "<td><input type='submit' value='Add Addresses'>\n</form></td>\n" .
        "<td><form method='post' action='help/mailadd.php' target='help'>\n" .
        "&nbsp;&nbsp;<input type='submit' value='Help'></form></td>\n" .
        "</tr></table>\n";
    $args  = array("$label$help", $field);
    table_data($args, 0, 'left');

    $subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
    echo span_data(2, $subhead);

    table_footer();
    newlines(1);
}

function manage_emails($siteid, $sitedata, $action)
{
    if ($action == 'emaildistr') {
        $post = 'contentsendall';

        $unsent = get_argument('unsent', 0, 0);
        $unsent_CHECKED = ($unsent) ? 'CHECKED' : '';

        $hidden = '';
    } else {
        $post = 'act-contentonly';

        $unsent = get_argument('unsent', 0, 0);
        $resend = get_argument('resend', 0, 0);
        $id     = get_argument('id', 0, 0);

        $hidden = "<input type='hidden' name='unsent' value=$unsent>\n";
        $hidden .= "<input type='hidden' name='resend' value=$resend>\n";
        if ($id)
            $hidden .= "<input type='hidden' name='id' value=$id>\n";
    }

    // form variabless
    $self   = preg_replace('/\?.+/', '', server_var('PHP_SELF'));

    $sitename       = $sitedata['sitename'];
    $emailbounce    = $sitedata['emailbounce'];
    $urldownload    = $sitedata['urldownload'];
    $messagetext    = $sitedata['messagetext'];
    $emailsubject   = $sitedata['emailsubject'];
    $emailsender    = $sitedata['emailsender'];
    $emailxheaders  = $sitedata['emailxheaders'];

    $agent    = server_var('HTTP_USER_AGENT');
    $netscape = strstr($agent, 'compatible') ? '0' : '1';
    $size50 = ($netscape) ? '20' : '50';
    $size40 = ($netscape) ? '20' : '40';

    echo "<form method='post' action='$self'>\n" .
        "<input type='hidden' name='action' value='$post'>\n" .
        "<input type='hidden' name='siteid' value='$siteid'>\n" . $hidden;

    table_header(0, 0, 5, 'left');

    $subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
    $subhead .= "<span class='subheading'>CHANGE EMAIL CONTENT FOR $sitename</span>";
    if ($action == 'emaildistr')
        $subhead .= "<br><span class=footnote>(Any changes will be saved.)</span>";
    echo span_data(2, $subhead);

    $label = '<b>Sender: </b>';
    $help  = "<br><span class=footnote>The sender and reply-to headers" .
        " displayed in<br>distributed email.</span>";
    $field = "<input type='text' id='emailsender' name='emailsender'" .
        " value=\"$emailsender\" size=$size50 maxlength='255'>";
    $args  = array("<span id='emailsender'>$label$help</span>", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Extra headers: </b>';
    $help  = "<br><span class=footnote>Optional extra headers displayed in" .
        " distributed<br> email. Be sure to hit return after each header.</span>";
    $field = "<textarea id='emailxheaders' name='emailxheaders'" .
        " rows=3 cols=$size40 wrap='soft'>$emailxheaders</textarea>";
    $args  = array("<span id='emailxheaders'>$label$help</span>", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Subject: </b>';
    $help  = "<br><span class=footnote>The subject line displayed in" .
        " distributed email.</span>";
    $field = "<input type='text' id='emailsubject' name='emailsubject'" .
        " value=\"$emailsubject\" size=$size50 maxlength='255'>";
    $args  = array("<span id='emailsubject'>$label$help</span>", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Email distribution message: </b>';
    $help  = "<br><span class=footnote>The message text used to instruct on" .
        "<br>ASI client installation steps.</span>";
    $field = "<textarea id='messagetext' name='messagetext' rows=10" .
        " cols=$size40 wrap='soft'>$messagetext</textarea>";
    $args  = array("<span id='messagetext'>$label$help</span>", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Download URL: </b>';
    $help  = "<br><span class=footnote>The URL for download of ASI client<br>" .
        " updates.</span>";
    $field = "<input type='text' id='urldownload' name='urldownload'" .
        " value=\"$urldownload\" size=$size50 maxlength='255'>";
    $args  = array("<span id='urldownload'>$label$help</span>", $field);
    table_data($args, 0, 'left', '', 1);

    $label = '<b>Bounce email: </b>';
    $help  = "<br><span class=footnote>The email address for bounced email.</span>";
    $field = "<input type='text' id='emailbounce' name='emailbounce'" .
        " value=\"$emailbounce\" size=$size50 maxlength='255'>";
    $args  = array("<span id='emailbounce'>$label$help</span>", $field);
    table_data($args, 0, 'left', '', 1);

    if ($action == 'contentonly') {
        $label = '<b>Upload a file of addresses: &nbsp;</b>';
        $help  = "<br><span class=footnote>Upload a text file containing<br>" .
            "one email address per line.</span>";
        $field = "<input type='file' name='emailfile' size=$size50></input>\n";
        $args  = array("$label$help", $field);
        table_data($args, 0, 'left', 'bottom');

        $subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
        echo span_data(2, $subhead);

        $args = array();
        $args[] = "<input type='hidden' name='contentonly' value=1>";
        $args[] = "<table><tr><td>\n" .
            "<input type='submit' value='Update Content'>\n" .
            "</form></td>\n" .
            "<td><form method='post' action='help/mailsend.php' target='help'>" .
            "&nbsp;&nbsp;<input type='submit' value='Help'>\n" .
            "</form></td></tr></table>\n";
        table_data($args, 0, 'left');
    } else {
        $subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
        $subhead .= "<span class='subheading'>SEND EMAIL</span>";
        echo span_data(2, $subhead);

        $label = '<b>Send  all pending (unsent) email: </b>';
        $help  = '';
        $field = "<input type='checkbox' name='unsent' value=1 $unsent_CHECKED>";
        $args  = array("$label$help", $field);
        table_data($args, 0, 'left');

        $subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
        echo span_data(2, $subhead);

        $args = array();
        $args[] = "";
        $args[] = "<br><table><tr><td>\n" .
            "<input type='submit' value='Update Content / Send Emails'>\n" .
            "</form></td>\n" .
            "<td><form method='post' action='help/mailsend.php' target='help'>" .
            "&nbsp;&nbsp;<input type='submit' value='Help'>\n" .
            "</form></td></tr></table>\n";
        table_data($args, 0, 'left', 'bottom');
    }

    table_footer();
    newlines(1);
}

function manage_status($siteid, $sitename, $db)
{
    // form variabless
    $self   = preg_replace('/\?.+/', '', server_var('PHP_SELF'));

    echo "<form method='post' action='$self'>\n" .
        "<input type='hidden' name='action' value='clearall'>\n" .
        "<input type='hidden' name='siteid' value='$siteid'>\n";

    table_header(0, 0, 2, 'left');

    $subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
    $subhead .= "<span class='subheading'>CLEAR RESPONSE CONFIRMATIONS</span>";
    echo span_data(3, $subhead);

    $label = '<b>Clear all response confirmations: </b>&nbsp;';
    $help  = '';
    $field = "<input type='submit' value='Clear Confirmations'>";
    $args  = array("$label$help", '', $field);
    table_data($args, 0, 'left');

    echo "</form>\n";

    echo "<form method='post' action='$self'>\n" .
        "<input type='hidden' name='action' value='deleteall'>\n" .
        "<input type='hidden' name='siteid' value='$siteid'>\n";

    $subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
    $subhead .= "<span class='subheading'>DELETE ADDRESSES</span>";
    echo span_data(3, $subhead);

?>
    <tr>
        <td><b>Delete all addresses: </b></td>
        <td><input type='radio' name='which' value='all'>&nbsp;&nbsp;</td>
        <td rowspan=2><input type='submit' name='submit' value='&nbsp;Delete Addresses&nbsp;'></td>
    </tr>
    <tr>
        <td><b>Delete only those with confirmed installation: </b></td>
        <td><input type='radio' name='which' value='some' CHECKED>&nbsp;&nbsp;</td>
    </tr>
    </form>
    <?php

    echo "<form method='post' action='$self'>\n" .
        "<input type='hidden' name='action' value='sendall'>\n" .
        "<input type='hidden' name='siteid' value='$siteid'>\n";

    $subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
    $subhead .= "<span class='subheading'>SEND EMAIL</span>";
    echo span_data(3, $subhead);

    ?>
    <tr>
        <td><b>Send all pending (unsent) email: </b></td>
        <td><input type='checkbox' name='unsent' value=1>&nbsp;&nbsp;</td>
        <td rowspan=2><input type='submit' value="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Send Email&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"></td>
    </tr>
    <tr>
        <td valign='bottom'><b>Resend emails to addresses without confirmed responses: </b></td>
        <td valign='bottom'><input type='checkbox' name='resend' value=1>&nbsp;&nbsp;</td>
    </tr>
    </form>
<?php

    $subhead = "<hr color='#333399' align='left' noshade size='1'>\n";
    echo span_data(3, $subhead);
    table_footer();

    newlines(2);

    table_header(2, 2, 2);
    $args = array('Email Address', 'Email Sent', 'Response Confirmation', 'Installation Confirmation', 'Action');
    table_data($args, 'center', 'top', 1);

    $emailsdata = get_all_emails_data($siteid, $db);

    if (safe_count($emailsdata)) {
        reset($emailsdata);
        foreach ($emailsdata as $key => $data) {
            display_email_row($key, $data, $db);
        }
    } else {
        echo span_data(safe_count($args), "<br>No email addresses exist for $sitename.<br><br>");
    }
    table_footer();

    table_header(0, 0, 2, 'left');
    $args = array("<form method='post' action='help/mailstat.php' target='help'>" .
        "<br><input type='submit' value='Help'></form> ");
    table_data($args, 0, 'left');
    table_footer();
}


/*
    |  Action Functions (from erstwhile mail-act.php)
    */

function find_site($siteid, $db)
{
    $site = array();
    $sql = "SELECT * FROM Sites WHERE siteid = $siteid";
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $site = mysqli_fetch_array($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $site;
}

function get_file_data($name)
{
    $filedata = array();
    if (isset($_FILE)) {
        $filedata = $_FILE[$name];
    } elseif (isset($GLOBALS['HTTP_POST_FILES'])) {
        $filedata = $GLOBALS['HTTP_POST_FILES'][$name];
    }
    return $filedata;
}

/*
    |   add_addresses
    |
    |   Takes email addresses from two sources, a form text box and a text file,
    |    and inserts them into the install.Siteemail table.  One address
    |    per line is expected.
    |
    |   parameters:
    |       $siteid:    The site id for the site the user is working with.
    |       $userid:    The id for the user who created the site
    |                    (corresponds to install.Users.intalluserid).
    |       $authuser:  The id for the logged in user.
    |       $db:        The handle to the database connection.
    */
function add_addresses($siteid, $userid, $authuser, $db)
{
    $emaillist  = trim(get_argument('emaillist', 0, ''));
    $filedata   = get_file_data('emailfile');

    $emails_list = array();
    $emails_file = array();
    $problem = 0;

    $now = time();

    $site = find_site($siteid, $db);
    $sitename = $site['sitename'];

    if (strlen($emaillist)) {
        $emails_list  = explode("\n", $emaillist);
    }

    if ($filedata && $filedata['size']) {
        $filename = $filedata['tmp_name'];
        $handle = fopen($filename, "r");
        while (!feof($handle)) {
            $line = fgets($handle, 1024);
            $emails_file[] = $line;
        }
    }

    $emails = array_merge($emails_list, $emails_file);

    $msg      = '';
    $xtra_msg = '';

    // update Siteemails table
    $count = 0;
    reset($emails);
    foreach ($emails as $key => $data) {
        $email = trim($data);
        if (strlen($email)) {
            $count++;
            $sql  = "INSERT INTO Siteemail SET";
            $sql .= " siteid=$siteid,\n";
            $sql .= " installuserid=$userid,\n";
            $sql .= " email='$email',\n";
            $sql .= " createdtime='$now'\n";
            $res  = redcommand($sql, $db);

            if (!$res) {
                $problem    = 1;
                $sql_error  = mysqli_error($GLOBALS["___mysqli_ston"]);
                $sql_errno  = mysqli_errno($GLOBALS["___mysqli_ston"]);

                // check for duplicate email address
                $key_index = get_key_index('install', 'Siteemail', 'uniq', $db);
                if ($sql_errno == 1062) // 1062 is the error code for dup entry
                {
                    if (preg_match("/\b$key_index\b/", $sql_error)) {
                        $xtra_msg .= "<br>The email address <b>$email</b> is a
                                        duplicate of an existing email address.";
                    }
                }
            }
        }
    }

    $addresses = ($count == 0 || $count > 1) ? 'addresses' : 'address';

    if ($problem) {
        $punct  = (strlen($xtra_msg)) ? ':<br>' : '.';
        $msg    = "There was a problem entering the <b>$count</b> new email " .
            "$addresses for site <b>$sitename</b>$punct $xtra_msg";
    } else {
        $self = preg_replace('/\?.+/', '', server_var('PHP_SELF'));
        $url = $self . "?action=status&siteid=$siteid";
        $msg = "You have entered <b>$count</b> new email $addresses for site <b>$sitename</b>.";
        if ($count)
            $msg .= "<br><br>[<a href='$url'>View them in the Email Status page</a>]";
        $log = "install: $count email addresses added for site '$sitename' by $authuser.";
        logs::log(__FILE__, __LINE__, $log, 0);
    }

    message($msg);
}

function confirm_delete_addresses($siteid, $db)
{
    $which = trim(get_argument('which', 0, 'some'));
    $conf = ($which == 'some') ? 'with confirmed installation' : '';

    $site = find_site($siteid, $db);
    if ($site) {
        $self   = preg_replace('/\?.+/', '', server_var('PHP_SELF'));
        $referer = server_var('HTTP_REFERER');
        $href = "$self?action=act-deleteall&which=$which&siteid=$siteid";
        $yes  = "[<a href='$href'>Yes</a>]";
        $no   = "[<a href='$referer'>No</a>]";

        $sitename = $site['sitename'];
        $msg  = "Are you sure you want to delete all email addresses $conf for site <b>$sitename</b>?<br>";
        $msg .= "<br>";
        $msg .= "$yes&nbsp;&nbsp;&nbsp;$no";
    } else {
        $msg = "Site <b>$siteid</b> does not exist.";
    }
    message($msg);
}

function delete_addresses($siteid, $authuser, $db)
{
    $which      = trim(get_argument('which', 0, 'some'));
    $sql_conf   = ($which == 'some') ? 'AND installed > 0' : '';
    $conf       = ($which == 'some') ? 'with confirmed installation' : '';

    $site = find_site($siteid, $db);
    if ($site) {
        $sitename = $site['sitename'];
        $log = "install: Email addresses $conf for site '$sitename' removed by $authuser.";
        logs::log(__FILE__, __LINE__, $log, 0);
        $sql = "DELETE FROM Siteemail WHERE siteid = $siteid $sql_conf"; // SE fix skip 
        $res = redcommand($sql, $db);
        $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        $addresses = ($count == 0 || $count > 1) ? 'addresses' : 'address';
        $msg = "<b>$count</b> email $addresses $conf for site <b>$sitename</b> have been removed.";
    } else {
        $msg = "Site <b>$siteid</b> does not exist.";
    }
    message($msg);
}

/*
    |   updatecontent_sendemails
    |
    |   Routes to appropriate functions to update email content and send or resend
    |    emails depending on form input.
    |
    |   parameters:
    |       $siteid:    The site id for the site the user is working with.
    |       $authuser:  The id for the logged in user.
    |       $db:        The handle to the database connection.
    */
function updatecontent_sendemails($siteid, $authuser, $db)
{
    $contentonly = get_argument('contentonly', 0, 0);
    $unsent     = get_argument('unsent', 0, 0);
    $resend     = get_argument('resend', 0, 0);
    $id         = get_argument('id', 0, 0);
    $messagetext = get_argument('messagetext', 0, '');

    // update email content
    $changed = update_site($siteid, $authuser, $db);

    // send emails
    if ($unsent || $resend) {
        confirm_send_email($siteid, 0, $db);
    } elseif ($id) //sending 1 email
    {
        confirm_send_email(0, $id, $db);
    } else // content only
    {
        $self = preg_replace('/\?.+/', '', server_var('PHP_SELF'));
        if ($changed) {
            //$url = $self . "?action=status&siteid=$siteid";
            //$msg = "[<a href='$url'>Return to Email Status page</a>]";
            //message($msg);
        } else {
            $url = $self . "?action=contentonly&siteid=$siteid&unsent=$unsent&resend=$resend";
            $msg = "[<a href='$url'>Return to Content page to edit the email content.</a>]";
            message($msg);
        }
    }
}


function confirm_send_email($siteid, $id, $db)
{
    $problem        = 0;
    $msg            = '';
    $xtra_msg       = '';
    $provide_link   = 0;

    $self   = preg_replace('/\?.+/', '', server_var('PHP_SELF'));
    $referer = server_var('HTTP_REFERER');

    if ($id) // sending single email
    {
        $emaildata  = get_email_data($id, $db);
        if ($emaildata) {
            $siteid = $emaildata['siteid'];
        } else {
            $problem = 1;
            $xtra_msg .= "Email address <b>$emailid</b> does not exist.\n";
        }
    } elseif ($siteid) // sending multiple emails
    {
        $unsent = get_argument('unsent', 0, 0);
        $resend = get_argument('resend', 0, 0);

        $todo   = '';
        if ($unsent)
            $todo .= 'send all unsent emails ';
        if ($unsent && $resend)
            $todo .= 'and ';
        if ($resend)
            $todo .= 'resend emails to addresses without confirmed responses';

        if ($unsent == 0 && $resend == 0) {
            $problem = 1;
            $msg  = "You chose to send email, but didn't check either checkbox.<br>";
            $msg .= "<br>";
            $msg .= "[<a href='$referer'>Go back and select a checkbox</a>]\n";
        }
    } else {
        $problem = 1;
    }

    if (!$problem) {
        $sitedata = get_site_data($siteid, $db);
        if ($sitedata) {
            $sitename   = $sitedata['sitename'];
            $messagetext = $sitedata['messagetext'];
            $urldownload = $sitedata['urldownload'];

            // check for fully qualified download URL
            if (strlen($urldownload)) {
                // check that it starts w/ protocol specifier (eg "http://").
                if (preg_match('/^[A-Za-z]+:\/\//', $urldownload)) {
                    // good to go
                } else {
                    $problem = 1;
                    $xtra_msg .= "The Download URL must be fully qualified, starting " .
                        "with a protocol specifier such as <i>http://</i>.<br>\n";
                    $provide_link = 1;
                }
            } else {
                $problem = 1;
                $xtra_msg .= "No Download URL has been specified.<br>\n";
                $provide_link = 1;
            }

            // check for responseurl in messagetext
            if (!preg_match('/%(responseurl)%/', $messagetext)) {
                $problem = 1;
                $xtra_msg .= "The string %responseurl% was not found in the email " .
                    "message you set up.<br>\n";
                $provide_link = 1;
            }
        } else {
            $problem = 1;
            $xtra_msg .= "Site <b>$siteid</b> does not exist.\n";
        }
    }

    if (!$problem) {
        if ($id) // sending single email
        {
            $qs   = "action=act-send&siteid=$siteid&id=$id";
            $address = htmlentities($emaildata['email']);
            $msg  = "Are you sure you want to send an email to <b>$address</b>?<br>";
        } else // sending multiple emails
        {
            $qs   = "action=act-sendall&unsent=$unsent&resend=$resend&siteid=$siteid";
            $msg  = "Are you sure you want to $todo for site <b>$sitename</b>? " .
                "Note that for a large number of messages, this process " .
                "may take a while.<br>";
        }
        $href = "$self?$qs";
        $yes  = "[<a href='$href'>Yes</a>]";
        $no   = "[<a href='$referer'>No</a>]";
        $msg .= "<br>";
        $msg .= "$yes&nbsp;&nbsp;&nbsp;$no";
    } else {
        $xtra_msg = (strlen($xtra_msg)) ? ":<br>" . $xtra_msg : ".";
        if ($provide_link == 1) {
            if ($id) // sending single email
            {
                $url = $self . "?action=contentonly&siteid=$siteid&id=$id";
            } else // sending multiple emails
            {
                $url = $self . "?action=contentonly&siteid=$siteid&unsent=$unsent&resend=$resend";
            }
            $xtra_msg .= "<br>[<a href='$url'>Edit the email content for $sitename</a>]\n";
        }
        $msg = "A problem was encountered while trying to send email" . $xtra_msg;
    }
    message($msg);
}

function get_emails($id, $siteid, $unsent, $resend, $db)
{
    $emails = array();

    if ($id)  // came from Emails List page
    {
        $sql = "SELECT * FROM Siteemail WHERE siteemailid = $id";
    } else // came from Email Distr page
    {
        $sql_where = ($resend) ? 'response = 0' : 'sent = 0';
        $sql = "SELECT * FROM Siteemail WHERE siteid = $siteid AND $sql_where";
    }
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_array($res)) {
                $siteemailid = $row['siteemailid'];
                $email       = $row['email'];
                $emails[$siteemailid] = $email;
            }
        }
    }
    return $emails;
}


/*
    |   update_site
    |
    |   Updates the install.Sites with form data user edited.
    |
    |   parameters:
    |       $siteid:    The site id for the site the user is working with.
    |       $authuser:  The id for the logged in user.
    |       $db:        The handle to the database connection.
    */
function update_site($siteid, $authuser, $db)
{
    $site = find_site($siteid, $db);
    $sitename = $site['sitename'];

    $emailbounce    = trim(get_argument('emailbounce', 0, ''));
    $urldownload    = trim(get_argument('urldownload', 0, ''));
    $messagetext    = trim(get_argument('messagetext', 1, ''));
    $emailsubject   = trim(get_argument('emailsubject', 1, ''));
    $emailsender    = trim(get_argument('emailsender', 1, ''));
    $emailxheaders  = trim(get_argument('emailxheaders', 1, ''));

    // if urldownload starts w/ host (e.g. [//]www.cool-site-4-u.com), prepend w/ http:[//]
    if (preg_match('/^(\/\/)?[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+/', $urldownload, $matches)) {
        $prepend = 'http:';
        if (!isset($matches[1]) || $matches[1] != '//')
            $prepend .= "//";
        $urldownload = $prepend . $urldownload;
    }
    // insert into Sites table [1]
    // update Sites table
    $sql  = "update Sites set";
    $sql .= " emailbounce='$emailbounce',\n";
    $sql .= " urldownload='$urldownload',\n";
    $sql .= " messagetext='$messagetext',\n";
    $sql .= " emailsubject='$emailsubject',\n";
    $sql .= " emailsender='$emailsender',\n";
    $sql .= " emailxheaders='$emailxheaders'\n";
    $sql .= " WHERE siteid = $siteid";
    $res  = redcommand($sql, $db);
    TokenChecker::calcSites($siteid, 'siteid');
    if ($res) {
        if (mysqli_affected_rows($GLOBALS["___mysqli_ston"])) {
            $msg = "Email content updated for site <b>$sitename</b>.";
            $changed = 1;
            $log = "install: Email content updated for site '$sitename' by $authuser.";
            logs::log(__FILE__, __LINE__, $log, 0);
        } else {
            $msg = "Email content for site <b>$sitename</b> left unchanged.";
            $changed = 0;
        }
    } else {
        $msg = "There was a problem updating content for site <b>$sitename</b>.";
        $changed = 0;
    }
    message($msg);

    return $changed;
}

/*
    |   send_emails
    |
    |   Sends all unsent (or resends all) emails for a site,
    |    OR sends 1 email to an address specified by $id.
    |
    |   parameters:
    |       $siteid:    The site id for which emails should be sent/resent,
    |                    OR the site id corresponding to the ONE email address
    |                    (IF $id is provided).
    |       $id:        The id for the email address for which an email should
    |                     be sent, OR '' IF multiple emails should be sent/resent.
    |       $userid:    The id for the user who created the site
    |                    (corresponds to install.Users.intalluserid).
    |       $authuser:  The id for the logged in user.
    |       $db:        The handle to the database connection.
    */
function send_emails($siteid, $id, $userid, $authuser, $db)
{
    $unsent = get_argument('unsent', 0, 0);
    $resend = get_argument('resend', 0, 0);

    $problem    = 0;
    $msg        = '';
    $xtra_msg   = '';

    // just in case siteid wasn't passed in
    if ($siteid == 0 && $id) {
        $emaildata = get_email_data($id, $db);
        $siteid = $emaildata['siteid'];
    }

    $sitedata = get_site_data($siteid, $db);
    if ($sitedata) {
        $sitename       = $sitedata['sitename'];
        $regcode        = $sitedata['regcode'];
        $email          = $sitedata['email'];
        $emailbounce    = $sitedata['emailbounce'];
        $messagetext    = $sitedata['messagetext'];
        $emailsubject   = $sitedata['emailsubject'];
        $emailsender    = $sitedata['emailsender'];
        $emailxheaders  = $sitedata['emailxheaders'];
        $urldownload    = $sitedata['urldownload'];

        // check for fully qualified download URL
        if (strlen($urldownload)) {
            // check that it starts w/ protocol specifier (eg "http://").
            if (preg_match('/^[A-Za-z]+:\/\//', $urldownload)) {
                // good to go
            } else {
                $problem = 1;
                $xtra_msg .= ":<br>The Download URL must be fully qualified, starting " .
                    "with a protocol specifier such as <i>http://</i>.<br>\n";
            }
        } else {
            $problem = 1;
            $xtra_msg .= ":<br>No Download URL has been specified.<br>\n";
        }

        // check for responseurl in messagetext
        if (!preg_match('/%(responseurl)%/', $messagetext)) {
            $problem = 1;
            $xtra_msg .= ":<br>The string %responseurl% was not found in the email " .
                "message you set up.<br>\n";
        }

        if (!$problem) {
            $now  = time();

            $subject    = $emailsubject;
            $boundary   = 'MiMeBoUnDaRy';
            $headers    = "From: $emailsender\n";
            $headers   .= "Reply-To: $emailsender\n";
            $headers   .= "Errors-To: $emailbounce\n";
            if (strlen($emailxheaders))
                $headers .= "$emailxheaders";

            $mime_head  = "MIME-Version: 1.0\n";
            $mime_head .= "Content-Type: text/plain;\n";


            $ssl  = 1; //server_var('HTTPS') ? 1 : 0;
            $http = ($ssl) ? 'https' : 'http';
            $host = server_var('HTTP_HOST');
            $ftp_path_data = explode('/', $_SERVER['PHP_SELF'])[1];
            $page = $ftp_path_data . '/install/d.php';

            $url = "$http://$host/$page";

            $emails = get_emails($id, $siteid, $unsent, $resend, $db);
            $count = safe_count($emails);
            reset($emails);
            foreach ($emails as $siteemailid => $email) {
                $responseurl = "$url?r=$regcode&e=$siteemailid";
                $messagetext = preg_replace('/%(responseurl)%/', $responseurl, $messagetext);

                // mime_head must be part of headers, not body.
                //$good = mail($email, $subject, $messagetext, $headers . $mime_head);
                $good = SMTP_sendMail($db, $email, $subject, $messagetext, $headers, $mime_head);
                if ($good) {
                    $sql = "UPDATE Siteemail SET sent=$now WHERE siteemailid = $siteemailid"; // SE fix skip 
                    $res = redcommand($sql, $db);
                    if (!$res) $problem = 1;
                } else {
                    $problem = 1;
                }
            }
        }
    } else {
        $problem = 1;
        $xtra_msg .= ":<br>Site <b>$siteid</b> does not exist.";
    }


    if (!$problem) {
        $message = ($count == 0 || $count > 1) ? 'messages' : 'message';
        $msg = "$count email $message sent.";
        $log = "install: $count email $message sent by $authuser.";
        logs::log(__FILE__, __LINE__, $log, 0);
    } else {
        if (!strlen($xtra_msg)) $xtra_msg = ".";
        $msg = "A problem was encountered while trying to send email" . $xtra_msg;
    }
    message($msg);
}


function confirm_clear_responses($siteid, $db)
{
    $site = find_site($siteid, $db);
    if ($site) {
        $self   = preg_replace('/\?.+/', '', server_var('PHP_SELF'));
        $referer = server_var('HTTP_REFERER');
        $href = "$self?action=act-clearall&siteid=$siteid";
        $yes  = "[<a href='$href'>Yes</a>]";
        $no   = "[<a href='$referer'>No</a>]";

        $sitename = $site['sitename'];
        $msg  = "Are you sure you want to clear the response confirmations for all addresses for site <b>$sitename</b>?<br>";
        $msg .= "<br>";
        $msg .= "$yes&nbsp;&nbsp;&nbsp;$no";
    } else {
        $msg = "Site <b>$siteid</b> does not exist.";
    }

    message($msg);
}

function clear_responses($siteid, $authuser, $db)
{
    $site = find_site($siteid, $db);
    if ($site) {
        $sitename = $site['sitename'];
        $log = "install: Response confirmations cleared for site '$sitename' by $authuser.";
        logs::log(__FILE__, __LINE__, $log, 0);
        $sql = "UPDATE Siteemail SET response = 0 WHERE siteid = $siteid"; // SE fix skip 
        $res = redcommand($sql, $db);
        $count = mysqli_affected_rows($GLOBALS["___mysqli_ston"]);
        $confirmation = ($count == 0 || $count > 1) ? 'confirmations' : 'confirmation';
        $msg = "<b>$count</b> response $confirmation for site <b>$sitename</b> have been cleared.";
    } else {
        $msg = "Site <b>$siteid</b> does not exist.";
    }
    message($msg);
}

function get_email_data($id, $db)
{
    $emaildata = array();
    $sql = "SELECT * FROM Siteemail WHERE siteemailid = $id";
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $emaildata = mysqli_fetch_array($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $emaildata;
}

function confirm_delete_address($siteid, $id, $db)
{
    $emaildata = get_email_data($id, $db);
    if ($emaildata) {
        $self   = preg_replace('/\?.+/', '', server_var('PHP_SELF'));
        $referer = server_var('HTTP_REFERER');
        $href = "$self?action=act-delete&siteid=$siteid&id=$id";
        $yes  = "[<a href='$href'>Yes</a>]";
        $no   = "[<a href='$referer'>No</a>]";

        $address = htmlentities($emaildata['email']);
        $msg  = "Are you sure you want to delete email address <b>$address</b>?<br>";
        $msg .= "<br>";
        $msg .= "$yes&nbsp;&nbsp;&nbsp;$no";
    } else {
        $msg = "Email address <b>$id</b> does not exist.";
    }
    message($msg);
}

function delete_address($siteid, $id, $authuser, $db)
{
    $emaildata = get_email_data($id, $db);
    if ($emaildata) {
        $address = htmlentities($emaildata['email']);
        $log = "install: Email addresses '$address' removed by $authuser.";
        logs::log(__FILE__, __LINE__, $log, 0);
        $sql = "DELETE FROM Siteemail WHERE siteemailid = $id"; // SE fix skip 
        $res = redcommand($sql, $db);
        $msg = "Email address <b>$address</b> has been removed.";
    } else {
        $msg = "Email address <b>$id</b> does not exist.";
    }

    $self = preg_replace('/\?.+/', '', server_var('PHP_SELF'));
    $url  = $self . "?action=status&siteid=$siteid";

    $msg .= "<br><br>[<a href='$url'>Return to Email Status page</a>]";
    message($msg);
}

function confirm_clear_response($siteid, $id, $db)
{
    $emaildata = get_email_data($id, $db);
    if ($emaildata) {
        $self   = preg_replace('/\?.+/', '', server_var('PHP_SELF'));
        $referer = server_var('HTTP_REFERER');
        $href = "$self?action=act-clear&siteid=$siteid&id=$id";
        $yes  = "[<a href='$href'>Yes</a>]";
        $no   = "[<a href='$referer'>No</a>]";

        $address = htmlentities($emaildata['email']);
        $msg  = "Are you sure you want to clear the response confirmations" .
            " for email address <b>$address</b>?<br>";
        $msg .= "<br>";
        $msg .= "$yes&nbsp;&nbsp;&nbsp;$no";
    } else {
        $msg = "Email address <b>$id</b> does not exist.";
    }
    message($msg);
}

function clear_response($siteid, $id, $authuser, $db)
{
    $emaildata = get_email_data($id, $db);
    if ($emaildata) {
        $address = htmlentities($emaildata['email']);
        $log = "install: Response confirmation cleared for email address " .
            "'$address' by $authuser.";
        logs::log(__FILE__, __LINE__, $log, 0);
        $sql = "UPDATE Siteemail SET response = 0 WHERE siteemailid = $id"; // SE fix skip 
        $res = redcommand($sql, $db);
        $msg = "Response confirmation for email address <b>$address</b> has been cleared.";
    } else {
        $msg = "Email address <b>$id</b> does not exist.";
    }

    $self   = preg_replace('/\?.+/', '', server_var('PHP_SELF'));
    $url    = $self . "?action=status&siteid=$siteid";

    $msg .= "<br><br>[<a href='$url'>Return to Email Status page</a>]";
    message($msg);
}



/*
    |  Main program
    */

$db = db_connect();
db_change($GLOBALS['PREFIX'] . 'install', $db);

$authuser       = install_login($db);
$authuserdata   = install_user($authuser, $db);
$authid         = $authuserdata['installuserid'];
$auth_admin     = @($authuserdata['priv_admin'])  ? 1 : 0;
$auth_servers   = @($authuserdata['priv_servers']) ? 1 : 0;
$auth_email     = @($authuserdata['priv_email'])  ? 1 : 0;

if (!$auth_email)  header("Location: index.php");

$comp = component_installed();

$action = strval(get_argument('action', 0, 'none'));
$id     = get_argument('id', 0, 0);

$siteid     = get_argument('siteid', 0, 0);
$sitedata   = get_site_data($siteid, $db);
$sitename   = $sitedata['sitename'];
// because an admin viewer can view and edit s.o. else's email stuff
$userid     = $sitedata['installuserid'];

switch ($action) {
    case 'addresses':
        $title = "Add Email Addresses for $sitename";
        break;
    case 'act-addall':
        $title = "Adding Email Addresses for $sitename";
        break;
    case 'deleteall':
        $title = "Confirm Delete Email Addresses for $sitename";
        break;
    case 'act-deleteall':
        $title = "Deleting Email Addresses for $sitename";
        break;
    case 'emaildistr':
        $title = "Send Email for $sitename";
        break;
    case 'contentsendall':
        if (get_argument('unsent', 0, 0))
            $title = "Confirm Send Email for $sitename";
        else
            $title = "Updating Content for $sitename";
        break;
    case 'act-sendall':
        $title = "Sending Email for $sitename";
        break;
    case 'sendall':
        $title = "Confirm Send Email for $sitename";
        break;
    case 'contentonly':
        $title = "Update Content for $sitename";
        break;
    case 'act-contentonly':
        $title = "Updating Content for $sitename";
        break;
    case 'status':
        $title = "Review Email Status for $sitename";
        break;
    case 'clearall':
        $title = "Confirm Clear Response Confirmations";
        break;
    case 'act-clearall':
        $title = "Clearing Response Confirmations";
        break;
    case 'delete':
        $title = "Confirm Delete Email Address";
        break;
    case 'act-delete':
        $title = "Deleting Email Address";
        break;
    case 'clear':
        $title = "Confirm Clear Response Confirmations";
        break;
    case 'act-clear':
        $title = "Clearing Response Confirmations";
        break;
    case 'send':
        $title = "Confirm Send Email";
        break;
    case 'act-send':
        $title = "Sending Email";
        break;
    case 'none':
        $title = 'Action Unknown';
        break;
}

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo install_html_header($title, $comp, $authuser, $auth_admin, $auth_servers, $db, $siteid);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

switch ($action) {
    case 'addresses':
        manage_addresses($siteid);
        break;
    case 'act-addall':
        add_addresses($siteid, $userid, $authuser, $db);
        break;
    case 'deleteall':
        confirm_delete_addresses($siteid, $db);
        break;
    case 'act-deleteall':
        delete_addresses($siteid, $authuser, $db);
        break;
    case 'emaildistr':
        manage_emails($siteid, $sitedata, $action);
        break;
    case 'contentsendall':
        updatecontent_sendemails($siteid, $authuser, $db);
        break;
    case 'sendall':
        confirm_send_email($siteid, 0, $db);
        break;
    case 'act-sendall':
        send_emails($siteid, $id, $userid, $authuser, $db);
        break;
    case 'contentonly':
        manage_emails($siteid, $sitedata, $action);
        break;
    case 'act-contentonly':
        updatecontent_sendemails($siteid, $authuser, $db);
        break;
    case 'status':
        manage_status($siteid, $sitename, $db);
        break;
    case 'clearall':
        confirm_clear_responses($siteid, $db);
        break;
    case 'act-clearall':
        clear_responses($siteid, $authuser, $db);
        break;
    case 'delete':
        confirm_delete_address($siteid, $id, $db);
        break;
    case 'act-delete':
        delete_address($siteid, $id, $authuser, $db);
        break;
    case 'clear':
        confirm_clear_response($siteid, $id, $db);
        break;
    case 'act-clear':
        clear_response($siteid, $id, $authuser, $db);
        break;
    case 'send':
        confirm_send_email(0, $id, $db);
        break;
    case 'act-send':
        send_emails(0, $id, $userid, $authuser, $db);
        break;
    case 'none':
        display_welcome($siteid);
        break;
}

echo head_standard_html_footer($authuser, $db);

?>