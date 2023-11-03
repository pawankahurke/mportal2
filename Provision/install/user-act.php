<?php

/*
  Revision history:

  Date        Who     What
  ----        ---     ----
  15-May-03   NL      Creation.
  29-May-03   NL      message(): add stripslashes
  29-May-03   NL      install_html_header(): pass $priv_servers (to display servers link).
  02-Jun-03   NL      Delete servers, startups & siteemails when a user is deleted.
  02-Jun-03   NL      Call install_html_footer (has its own version).
  03-Jun-03   NL      Process default site-related fields.
  04-Jun-03   NL      delete_user(): only delete startupscrips if some exist.
  04-Jun-03   NL      insert_user(): initialize $sql_site_pwd;
  update_user(): only update privs if admin
  06-Jun-03   NL      update_user(): set priv defaults to 0 (checkboxes dont get sent if unchecked).
  16-Jun-03   NL      Change include line: '../lib/l-head.php' --> 'header.php'.
  24-Jun-03   NL      Change page title: Edit User -> Updating User.
  15-Jul-03   NL      Process subject, sender, extra headers fields.
  23-Jul-03   NL      Change footer to standard_html_footer().
  28-Jul-03   NL      Change page titles.
  28-Jul-03   NL      Change page titles (create --> add).
  31-Jul-03   EWB     Uses install_login($db);
  7-Aug-03   NL      Change all text messages: create --> add.
  15-Aug-03   NL      insert_user(), update_user(): Check for dup entries
  using db (get_key_index), not PHP.
  28-Aug-03   NL      update_user(): Pass in $authuser.
  28-Aug-03   NL      insert_user(), update_user(): Match on error code instead of error message.
  29-Aug-03   NL      Include lib/l-dberr.php for get_key_index().
  29-Aug-03   NL      insert_user(), update_user(): prepend downloadurl w/ http://.
  2-Sep-03   NL      update_user(): Process "Propogate changes..." checkbox.
  25-Sep-03   NL      Add "install:" and "by $authuser" to all error_log entries;
  Create entries for all db actions.
  6-Oct-03   NL      Fix misspelling: Propogate --> Propagate
  23-Oct-03   NL      update_user(): Only include privs in error_log for admin user.
  09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
  19-Jun-07   AAM     Handled new database columns for automation.  Updated for
  new indices.
  03-Oct-08   BTE     Bug 4828: Change customization feature of server.
  25-Oct-08   AAM     Bug 4823: backed out "automation" related changes.

 */

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-cnst.php');
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
include('../lib/l-svbt.php');

function special_header($msg, $span)
{
    $msg = "<font color='white'>$msg</font>";
    $msg = fontspeak($msg);
    $msg = "<tr><th colspan='$span' bgcolor='#333399'>$msg</th></tr>\n";
    return $msg;
}

function span_data($n, $msg)
{
    $msg = fontspeak($msg);
    $msg = "<tr><td colspan='$n'>$msg</td></tr>\n";
    return $msg;
}

function message($s)
{
    $msg = stripslashes($s);
    echo "<br>\n$msg<br>\n<br>\n";
}

function table_header()
{
    echo "\n<table border='2' align='left' cellspacing='2' cellpadding='2'>\n";
}

function table_footer()
{
    echo "\n</table>\n";
    echo "<br clear='all'>\n";
}

function table_data($args, $head)
{
    $td = ($head) ? 'th' : 'td';
    if (safe_count($args)) {
        echo "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            $s = fontspeak($data);
            echo "<$td>$s</$td>\n";
        }
        echo "</tr>\n";
    }
}

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

function update_user($admin, $id, $authuser, $db)
{
    $sql_pwd = '';
    $sql_site_pwd = '';
    $msg = '';
    $problem = 0;
    $xtra_msg = '';

    $propagate = trim(get_argument('propagate', 0, 0));
    $installuser = trim(get_argument('installuser', 1, ''));
    $installemail = trim(get_argument('installemail', 1, ''));

    $firstname = trim(get_argument('firstname', 1, ''));
    $lastname = trim(get_argument('lastname', 1, ''));
    $companyname = trim(get_argument('companyname', 1, ''));
    $wsurl = trim(get_argument('wsurl', 0, ''));

    $password = trim(get_argument('password', 0, ''));
    $confirm_pwd = trim(get_argument('confirmpassword', 0, ''));

    if ($admin) {
        $priv_servers = trim(get_argument('priv_servers', 0, 0));
        $priv_email = trim(get_argument('priv_email', 0, 0));
        $priv_admin = trim(get_argument('priv_admin', 0, 0));
    }

    $siteusername = trim(get_argument('siteusername', 1, ''));
    $sitepassword = trim(get_argument('sitepassword', 0, ''));
    $confirm_sitepwd = trim(get_argument('confirmsitepassword', 0, ''));
    $serverid = intval(get_argument('serverid', 1, 0));
    //$skulist = implode(',', get_argument('skulist', 0, array()));
    $proxy = trim(get_argument('proxy', 0, ''));
    $startupid = trim(get_argument('startupid', 1, 'All'));
    $followonid = trim(get_argument('followonid', 1, 'All'));
    $delay_days = intval(get_argument('delay_days', 0, 0));
    $delay_hrs = intval(get_argument('delay_hrs', 0, 0));
    $delay_mins = intval(get_argument('delay_mins', 0, 0));
    $delay_on = intval(get_argument('delay_on', 0, 0));
    $email = trim(get_argument('email', 0, ''));
    $emailbounce = trim(get_argument('emailbounce', 0, ''));
    $urldownload = trim(get_argument('urldownload', 0, ''));
    $messagetext = trim(get_argument('messagetext', 1, ''));
    $emailsubject = trim(get_argument('emailsubject', 1, ''));
    $emailsender = trim(get_argument('emailsender', 1, ''));
    $emailxheaders = trim(get_argument('emailxheaders', 1, ''));

    // if urldownload starts w/ host (e.g. [//]www.cool-site-4-u.com), prepend w/ http:[//]
    if (preg_match('/^(\/\/)?[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+/', $urldownload, $matches)) {
        $prepend = 'http:';
        if (!isset($matches[1]) || $matches[1] != '//')
            $prepend .= "//";
        $urldownload = $prepend . $urldownload;
    }

    if (!strlen($installuser)) {
        $msg = "User name cannot be blank.";
    }

    // check for password change
    if (strlen($password) || strlen($confirm_pwd)) {
        $response = check_pwd_change($db, $installuser, 0, '', $password, $confirm_pwd);
        if ($response == "success")
            $sql_pwd = " password='" . md5($password) . "',\n";
        elseif (strlen(trim($response)))
            $msg = $response;
        else
            $msg = "There was a problem with this update. Please try again.";
    }

    // check for site password change
    if (strlen($sitepassword) || strlen($confirm_sitepwd)) {
        $response = check_pwd_change($db, $siteusername, 0, '', $sitepassword, $confirm_sitepwd);
        if ($response == "success")
            $sql_site_pwd = " sitepassword='" . md5($sitepassword) . "',\n";
        elseif (strlen(trim($response)))
            $msg = $response;
        else
            $msg = "There was a problem with this update. Please try again.";
    }

    if ($msg == '') {
        // calculate delay
        $delay = ($delay_days * 1440) + ($delay_hrs * 60) + $delay_mins;

        // update Users table
        $sql = "update Users set";
        if (strlen(trim($sql_pwd)))
            $sql .= $sql_pwd;
        $sql .= " installuser='$installuser',\n";
        $sql .= " installemailid='$installemail',\n";
        $sql .= " firstname='$firstname',\n";
        $sql .= " lastname='$lastname',\n";
        $sql .= " companyname='$companyname',\n";
        if ($admin) {
            $sql .= " priv_servers=$priv_servers,\n";
            $sql .= " priv_email=$priv_email,\n";
            $sql .= " priv_admin=$priv_admin,\n";
        }
        $sql .= " siteusername='$siteusername',\n";
        if (strlen(trim($sql_site_pwd)))
            $sql .= $sql_site_pwd;
        $sql .= " serverid=$serverid,\n";
        //$sql .= " skuids='$skulist',\n";
        $sql .= " skuids='',\n";
        $sql .= " proxy='$proxy',\n";
        $sql .= " wsurl='$wsurl',\n";
        $sql .= " startupid='$startupid',\n";
        $sql .= " followonid='$followonid',\n";
        $sql .= " delay=$delay,\n";
        $sql .= " delayon=$delay_on,\n";
        $sql .= " email='$email',\n";
        $sql .= " emailbounce='$emailbounce',\n";
        $sql .= " urldownload='$urldownload',\n";
        $sql .= " messagetext='$messagetext',\n";
        $sql .= " emailsubject='$emailsubject',\n";
        $sql .= " emailsender='$emailsender',\n";
        $sql .= " emailxheaders='$emailxheaders'\n";
        $sql .= " where installuserid = $id";
        $res = redcommand($sql, $db);
        if (!$res) {
            $problem = 1;
            $sql_error = mysqli_error($GLOBALS["___mysqli_ston"]);
            $sql_errno = mysqli_errno($GLOBALS["___mysqli_ston"]);

            // check for duplicate user name
            $key_index = get_key_index('install', 'Users', 'uniq', $db);
            if ($sql_errno == 1062) { // 1062 is the error code for dup entry
                if (preg_match("/\b$key_index\b/", $sql_error)) {
                    $xtra_msg = "The user name <b>$installuser</b> is a duplicate
                                     of an existing user name.";
                }
            }
            // check for duplicate user emailid
            $email_index = get_key_index('install', 'Users', 'uniq_email', $db);
            if ($sql_errno == 1062) { // 1062 is the error code for dup entry
                if (preg_match("/\b$email_index\b/", $sql_error)) {
                    $xtra_msg .= "<br/>The user email id <b>$installemail</b> is a duplicate
                                     of an existing user name.";
                }
            }
        }

        if ($problem) {
            $msg = "Unable to update user <b>$installuser</b>. $xtra_msg";
        } else {
            $msg = "User <b>$installuser</b> updated.";
            $log = "install: User '$installuser' updated by $authuser;";
            if ($admin) {
                $log .= " priv_servers: $priv_servers;";
                $log .= " priv_email: $priv_email;";
                $log .= " priv_admin: $priv_admin.";
            }
            logs::log(__FILE__, __LINE__, $log, 0);

            //$asicon = SVBT_coreServerConnect($installuser, $db);  // creates user in respective ASI server.
            //if($asicon['status'] == 1) {
            //SVBT_createASICoreUser($installuser, $db, $asicon['dbcon']);
            SVBT_createASICoreUser_Dev($installuser, $serverid, $db);
            //}
            // Propagate changes in default values to existing sites
            if ($propagate) {
                // determine $uninstall
                $uninstall = ($followonid == 'Uninstall') ? 1 : 0;
                // calculate $delay
                $delay = ($delay_days * 1440) + ($delay_hrs * 60) + $delay_mins;

                $sql_site_pwd = str_replace("sitepassword", "password", $sql_site_pwd);
                // insert into Sites table [1]
                // update Sites table
                $sql = "update Sites set";
                $sql .= " username='$siteusername',\n";
                if (strlen(trim($sql_site_pwd)))
                    $sql .= $sql_site_pwd;
                $sql .= " email='$email',\n";
                $sql .= " serverid=$serverid,\n";
                $sql .= " proxy='$proxy',\n";
                $sql .= " startupid='$startupid',\n";
                $sql .= " followonid='$followonid',\n";
                $sql .= " uninstall='$uninstall',\n";
                $sql .= " delay=$delay,\n";
                $sql .= " delayon=$delay_on,\n";
                $sql .= " emailbounce='$emailbounce',\n";
                $sql .= " urldownload='$urldownload',\n";
                $sql .= " messagetext='$messagetext',\n";
                $sql .= " emailsubject='$emailsubject',\n";
                $sql .= " emailsender='$emailsender',\n";
                $sql .= " emailxheaders='$emailxheaders'\n";
                $sql .= " where installuserid = $id";
                $res = redcommand($sql, $db);
                if (!$res) {
                    $problem = 1;
                }

                if ($problem) {
                    $msg .= "<br><br>Unable to update exisiting sites.";
                } else {
                    $msg .= "<br><br>Existing sites updated.";

                    // Make sure clients haven't already gotten old settings
                    if (mysqli_affected_rows($GLOBALS["___mysqli_ston"])) {
                        // insert into Sites table [1]
                        // update numedits
                        $sql = "update Sites set";
                        $sql .= " numedits= numedits + 1\n";
                        $sql .= " where installuserid = $id";
                        $res = redcommand($sql, $db);
                        TokenChecker::calcSites($id, 'installuserid');

                        $numconnects = get_numconnects($id, $db);
                        if ($numconnects) {
                            $are = ($numconnects > 1) ? 'are' : 'is';
                            $clients = ($numconnects > 1) ? 'clients' : 'client';
                            $These = ($numconnects > 1) ? 'These' : 'This';
                            $msg .= "<br><br>Note that there $are already $numconnects" .
                                " $clients using the data you changed." .
                                " $These $clients will not be updated with the " .
                                " changes you made until the next time a" .
                                " client is installed at the site.";
                        }
                    }
                }
            }
        }
    }
    message($msg);
}

function find_user($id, $db)
{
    $usr = array();
    $sql = "select * from Users where installuserid = $id";
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $usr = mysqli_fetch_array($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $usr;
}

function confirm_delete_user($id, $db)
{
    $user = find_user($id, $db);
    if ($user) {
        $self = server_var('PHP_SELF');
        $referer = server_var('HTTP_REFERER');
        $href = "$self?action=reallydelete&id=$id";
        $yes = "[<a href='$href'>Yes</a>]";
        $no = "[<a href='$referer'>No</a>]";

        $username = $user['installuser'];
        $msg = "Are you sure you want to delete user <b>$username</b>?<br>";
        $msg .= "<br>";
        $msg .= "$yes&nbsp;&nbsp;&nbsp;$no";
        message($msg);
    }
}

function delete_user($id, $admin, $authuser, $db)
{
    if ($admin) {
        $user = find_user($id, $db);
        if ($user) {
            $name = $user['installuser'];
            $sql = "DELETE FROM Users WHERE installuserid = $id";
            $res = redcommand($sql, $db);
            if ($res) {
                $sql = "DELETE FROM Servers WHERE installuserid = $id";
                redcommand($sql, $db);
                $sql = "DELETE FROM Sites WHERE installuserid = $id";
                redcommand($sql, $db);
                $sql = "DELETE FROM Siteemail WHERE installuserid = $id"; // SE fix skip 
                redcommand($sql, $db);
                $sql = "SELECT startupnameid FROM Startupnames WHERE installuserid = $id";
                $res = redcommand($sql, $db);
                $startupnameids = array();
                if ($res)
                    while ($row = mysqli_fetch_array($res))
                        $startupnameids[] = $row['startupnameid'];
                $startid_string = implode(",", $startupnameids);
                $sql = "DELETE FROM Startupnames WHERE installuserid = $id";
                $res = redcommand($sql, $db);
                if (strlen($startid_string)) {
                    $sql = "DELETE FROM Startupscrips WHERE startupnameid IN ($startid_string)";
                    $res = redcommand($sql, $db);
                }

                $msg = "User <b>$name</b> has been removed.";
                $log = "install: User '$name' removed by $authuser.";
                logs::log(__FILE__, __LINE__, $log, 0);
            } else {
                $msg = "There was a problem deleting user <b>$name</b>. Please try again.";
            }
        } else {
            $msg = "User <b>$id</b> does not exist.";
        }
    } else {
        $msg = "Authorization denied.";
    }
    message($msg);
}

function insert_user($authuser, $id, $db)
{
    $sql_pwd = '';
    $sql_site_pwd = '';
    $msg = '';
    $problem = 0;
    $xtra_msg = '';

    $installuser = trim(get_argument('installuser', 1, ''));
    $installemail = trim(get_argument('installemail', 1, ''));
    $password = trim(get_argument('password', 0, ''));
    $confirm_pwd = trim(get_argument('confirmpassword', 0, ''));
    $priv_servers = trim(get_argument('priv_servers', 0, 0));
    $priv_email = trim(get_argument('priv_email', 0, 0));
    $priv_admin = trim(get_argument('priv_admin', 0, 0));
    $siteusername = trim(get_argument('siteusername', 1, ''));
    $sitepassword = trim(get_argument('sitepassword', 0, ''));
    $confirm_sitepwd = trim(get_argument('confirmsitepassword', 0, ''));
    $serverid = intval(get_argument('serverid', 1, 0));
    $proxy = trim(get_argument('proxy', 0, ''));
    $startupid = trim(get_argument('startupid', 1, 'All'));
    $followonid = trim(get_argument('followonid', 1, 'All'));
    $delay_days = intval(get_argument('delay_days', 0, 0));
    $delay_hrs = intval(get_argument('delay_hrs', 0, 0));
    $delay_mins = intval(get_argument('delay_mins', 0, 0));
    $delay_on = intval(get_argument('delay_on', 0, 0));
    $email = trim(get_argument('email', 0, ''));
    $emailbounce = trim(get_argument('emailbounce', 0, ''));
    $urldownload = trim(get_argument('urldownload', 0, ''));
    $messagetext = trim(get_argument('messagetext', 1, ''));
    $emailsubject = trim(get_argument('emailsubject', 1, ''));
    $emailsender = trim(get_argument('emailsender', 1, ''));
    $emailxheaders = trim(get_argument('emailxheaders', 1, ''));

    // if urldownload starts w/ host (e.g. [//]www.cool-site-4-u.com), prepend w/ http:[//]
    if (preg_match('/^(\/\/)?[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+/', $urldownload, $matches)) {
        $prepend = 'http:';
        if (!isset($matches[1]) || $matches[1] != '//')
            $prepend .= "//";
        $urldownload = $prepend . $urldownload;
    }

    if (!strlen($installuser)) {
        $msg = "User name cannot be blank.";
    }

    // check for password
    if ($msg == '') {
        if (strlen($password) || strlen($confirm_pwd)) {
            $response = check_pwd_change($db, $installuser, 0, '', $password, $confirm_pwd);
            if ($response == "success")
                $sql_pwd = " password='" . md5($password) . "',\n";
            elseif (strlen(trim($response)))
                $msg = $response;
            else
                $msg = "There was a problem with this update. Please try again.";
        }
    }

    // check for site password change
    if (strlen($sitepassword) || strlen($confirm_sitepwd)) {
        $response = check_pwd_change($db, $siteusername, 0, '', $sitepassword, $confirm_sitepwd);
        if ($response == "success")
            $sql_site_pwd = " sitepassword='" . md5($sitepassword) . "',\n";
        elseif (strlen(trim($response)))
            $msg = $response;
        else
            $msg = "There was a problem with this update. Please try again.";
    }

    if ($msg == '') {
        // calculate delay
        $delay = ($delay_days * 1440) + ($delay_hrs * 60) + $delay_mins;

        // insert into Users table
        $sql = "INSERT INTO Users SET\n";
        $sql .= " installuser='$installuser',\n";
        $sql .= " installemailid='$installemail',\n";
        if (strlen(trim($sql_pwd)))
            $sql .= $sql_pwd;
        $sql .= " priv_servers=$priv_servers,\n";
        $sql .= " priv_email=$priv_email,\n";
        $sql .= " priv_admin=$priv_admin,\n";
        $sql .= " siteusername='$siteusername',\n";
        if (strlen(trim($sql_site_pwd)))
            $sql .= $sql_site_pwd;
        $sql .= " serverid=$serverid,\n";
        $sql .= " proxy='$proxy',\n";
        $sql .= " startupid='$startupid',\n";
        $sql .= " followonid='$followonid',\n";
        $sql .= " delay=$delay,\n";
        $sql .= " delayon=$delay_on,\n";
        $sql .= " email='$email',\n";
        $sql .= " emailbounce='$emailbounce',\n";
        $sql .= " urldownload='$urldownload',\n";
        $sql .= " messagetext='$messagetext',\n";
        $sql .= " emailsubject='$emailsubject',\n";
        $sql .= " emailsender='$emailsender',\n";
        $sql .= " emailxheaders='$emailxheaders'\n";
        $res = command($sql, $db);
        if (!affected($res, $db)) {
            $problem = 1;
            $sql_error = mysqli_error($GLOBALS["___mysqli_ston"]);
            $sql_errno = mysqli_errno($GLOBALS["___mysqli_ston"]);

            // check for duplicate user name
            $key_index = get_key_index('install', 'Users', 'uniq', $db);
            if ($sql_errno == 1062) { // 1062 is the error code for dup entry
                if (preg_match("/\b$key_index\b/", $sql_error)) {
                    $xtra_msg = "The user name <b>$installuser</b> is a duplicate
                                     of an existing user name.";
                }
            }
            // check for duplicate user emailid
            $email_index = get_key_index('install', 'Users', 'uniq_email', $db);
            if ($sql_errno == 1062) { // 1062 is the error code for dup entry
                if (preg_match("/\b$email_index\b/", $sql_error)) {
                    $xtra_msg .= "<br/>The user email id <b>$installemail</b> is a duplicate
                                     of an existing user name.";
                }
            }
        }

        if ($problem) {
            $msg = "Unable to add user <b>$installuser</b>. $xtra_msg";
        } else {
            // $installuserid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);

            $msg = "New user <b>$installuser</b> added.";
            $log = "install: User '$installuser' added by $authuser;";
            $log .= " priv_servers: $priv_servers;";
            $log .= " priv_email: $priv_email;";
            $log .= " priv_admin: $priv_admin.";
            logs::log(__FILE__, __LINE__, $log, 0);
        }
    }

    message($msg);
}

function import_user_license_key($authuser, $db)
{
    $licenseKey = trim(get_argument('licensekey', 1, ''));
    if ($licenseKey == '') {
        $msg = "License key field value cannot be blank.";
    }

    if ($msg == '') {
        $key = 'hnhj7vqj9n';
        $c = base64_decode($licenseKey);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);

        if (hash_equals($hmac, $calcmac)) {     //PHP 5.6+ timing attack safe comparison
            $licenseInfo = unserialize($original_plaintext);
            //echo '<pre>'; print_r($licenseInfo); exit;
            $token = $licenseInfo['token'];
            $ldata = $licenseInfo['data'];

            $tksql = "select tokenval from " . $GLOBALS['PREFIX'] . "install.tokenManager limit 1";
            $tkres = command($tksql, $db);
            if ($tkres) {
                $tokenData = mysqli_fetch_assoc($tkres);
                if (isset($tokenData['tokenval']) && $tokenData['tokenval'] == $token) {
                    $error = 0;
                    foreach ($ldata as $key => $value) {
                        foreach ($value as $nskey => $nsvalue) {
                            // skuOfferings insert
                            $skusql = "insert ignore into " . $GLOBALS['PREFIX'] . "install.skuOfferings set ";
                            foreach ($nsvalue as $sdkey => $sdvalue) {
                                $skusql .= "$sdkey = '$sdvalue',";
                            }
                            $skusqlqry = rtrim($skusql, ',') . ';';
                            $skusqlres = redcommand($skusqlqry, $db);
                        }
                        if (!$skusqlres) {
                            $error = 1;
                        }
                    }
                    if (!$error) {
                        $msg = "License has been imported successfully!";
                    } else {
                        $msg = "License key import failed. Error updating record!";
                    }
                } else {
                    $msg = "License key import failed. Invalid token!";
                }
            } else {
                $msg = "License key import failed. Please try again!";
            }
        } else {
        }
    }

    message($msg);
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

$action = strval(get_argument('action', 0, 'none'));
$id = get_argument('id', 0, 0);

switch ($action) {
    case 'add':
        $title = 'Adding User';
        break;
    case 'edit':
        $title = 'Updating User';
        break;
    case 'delete':
        $title = 'Confirm User Delete';
        break;
    case 'reallydelete':
        $title = 'Deleting User';
        break;
        /* New Changes */
    case 'importuserlicense':
        $title = 'Import User License';
        break;
    default:
        $title = 'Action Unknown';
        break;
}

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo install_html_header($title, $comp, $authuser, $priv_admin, $priv_servers, $db);
if (trim($msg))
    debug_note($msg);   // ...display any errors to debug users

switch ($action) {
    case 'add':
        insert_user($authuser, $id, $db);
        break;
    case 'edit':
        update_user($priv_admin, $id, $authuser, $db);
        break;
    case 'delete':
        confirm_delete_user($id, $db);
        break;
    case 'reallydelete':
        delete_user($id, $priv_admin, $authuser, $db);
        break;
        /* New Changes */
    case 'importuserlicense':
        import_user_license_key($authuser, $db);
        break;
    default:
        break;
}

/* Hardwired to pass in hfn for the user. */
$user = 'hfn';
echo head_standard_html_footer($user, $db);
