<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
20-May-03   NL      Creation.
28-May-03   NL      stripslashes before displaying startupname in message.
29-May-03   NL      message(): add stripslashes
29-May-03   NL      install_html_header(): pass $priv_servers (to display servers link).
02-Jun-03   NL      Call install_html_footer (has its own version).
02-Jun-03   NL      Change titles.
03-Jun-03   NL      Change reference to startup option in user message.
04-Jun-03   NL      Change titles.
16-Jun-03   NL      Change include line: '../lib/l-head.php' --> 'header.php'.
23-Jul-03   NL      Change footer to standard_html_footer().
28-Jul-03   NL      Change page titles. 
28-Jul-03   NL      Change page titles (create --> add).  
31-Jul-03   EWB     Uses install_login($db);
 7-Aug-03   NL      Change all text messages: create --> add.  
 8-Aug-03   NL      Change title (Editing --> Updating).  
15-Aug-03   NL      insert_option(), update_option(): Check for dup entries 
                        using db (get_key_index), not PHP. 
28-Aug-03   NL      insert_option(), update_option(): Match on error code instead of error message.
29-Aug-03   NL      Include lib/l-dberr.php for get_key_index().     
24-Sep-03   NL      Add "install: " to error_log entries; create entries for all db actions.
25-Sep-03   NL      Add "install:" and "by $authuser" to all error_log entries; 
                    Create entries for all db actions.                                          
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
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

function update_option($id, $authuser, $db)
{
    $msg        = '';
    $problem    = 0;
    $xtra_msg   = '';

    $startup    = trim(get_argument('startup', 1, ''));
    $scrips     = get_argument('scrips', 0, array());

    if (strlen($startup)) {
        // check ALL or NONE
        $lc_startup = strtolower($startup);
        if (preg_match('/^all|none$/', $lc_startup)) {
            $msg = "The Scrip configuration name cannot be <i>all</i> 
                        or <i>none</i>. Please choose another name";
        }
    } else {
        // check for blank name
        $msg = "The Scrip configuration name cannot be blank.";
    }

    if ($msg == '') {
        // update Startupnames table
        $sql  = "UPDATE Startupnames SET startup = '$startup' WHERE startupnameid = $id";
        $res  = redcommand($sql, $db);
        if (!$res) {
            $problem    = 1;
            $sql_error  = mysqli_error($GLOBALS["___mysqli_ston"]);
            $sql_errno  = mysqli_errno($GLOBALS["___mysqli_ston"]);

            // check for duplicate startupname
            $key_index = get_key_index('install', 'Startupnames', 'uniq', $db);
            if ($sql_errno == 1062) // 1062 is the error code for dup entry
            {
                if (preg_match("/\b$key_index\b/", $sql_error)) {
                    $xtra_msg = "The Scrip configuration name <b>$startup</b> 
                           is a duplicate of an existing Scrip configuration name.";
                }
            }
        } else {
            // update (delete, insert) Startupscrips table
            $sql  = "DELETE FROM Startupscrips WHERE startupnameid = $id";
            $res  = redcommand($sql, $db);
            if ($res) {
                reset($scrips);
                foreach ($scrips as $key => $scripid) {
                    $sql  = "INSERT INTO Startupscrips SET";
                    $sql .= " startupnameid=$id,\n";
                    $sql .= " scrip=$scripid\n";
                    $res  = redcommand($sql, $db);
                    if (!$res) {
                        $problem    = 1;
                        $sql_error  = mysqli_error($GLOBALS["___mysqli_ston"]);
                        $sql_errno  = mysqli_errno($GLOBALS["___mysqli_ston"]);

                        // check for duplicate scrips
                        $key_index = get_key_index('install', 'Startupscrips', 'uniq', $db);
                        if ($sql_errno == 1062) // 1062 is the error code for dup entry
                        {
                            if (preg_match("/\b$key_index\b/", $sql_error)) {
                                $xtra_msg .= "The Scrip <b>$scripid</b> is a duplicate 
                                                of an existing Scrip.";
                            }
                        }
                    }
                }
            } else {
                $problem = 1;
            }
        }
        if ($problem) {
            $msg = "Unable to update Scrip configuration <b>$startup</b>. $xtra_msg";
        } else {
            $msg  = "Scrip configuration <b>$startup</b> updated.";
            $log  = "install: Scrip configuration '$startup' updated by $authuser.";
            logs::log(__FILE__, __LINE__, $log, 0);
        }
    }

    message($msg);
}

function find_option($id, $db)
{
    $option = array();
    $sql = "select * from Startupnames where startupnameid = $id";
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $option = mysqli_fetch_array($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $option;
}


function confirm_delete_option($id, $db)
{
    $option = find_option($id, $db);
    if ($option) {
        $startup = $option['startup'];
        $self   = server_var('PHP_SELF');
        $referer = server_var('HTTP_REFERER');
        $href = "$self?action=reallydelete&id=$id";
        $yes  = "[<a href='$href'>Yes</a>]";
        $no   = "[<a href='$referer'>No</a>]";

        $msg  = "Are you sure you want to delete Scrip configuration <b>$startup</b>?<br>";
        $msg .= "<br>";
        $msg .= "$yes&nbsp;&nbsp;&nbsp;$no";
        message($msg);
    }
}

function delete_option($id, $authuser, $db)
{
    $problem = 0;

    $option = find_option($id, $db);
    if ($option) {
        $startup = $option['startup'];

        $sql = "DELETE FROM Startupnames WHERE startupnameid = $id";
        $res = redcommand($sql, $db);
        if ($res) {
            $sql = "DELETE FROM Startupscrips WHERE startupnameid = $id";
            $res = redcommand($sql, $db);
            if (!$res) $problem = 1;
        } else {
            $problem = 1;
        }

        if ($problem) {
            $msg = "Unable to delete Scrip configuration <b>$startup</b>.";
        } else {
            $msg  = "Scrip configuration <b>$startup</b> has been removed.";
            $log = "install: Scrip configuration '$startup' removed by $authuser.";
            logs::log(__FILE__, __LINE__, $log, 0);
        }
    } else {
        $msg = "Scrip configuration <b>$id</b> does not exist.";
    }

    message($msg);
}

function insert_option($authuser, $authuserid, $db)
{
    $msg        = '';
    $problem    = 0;
    $xtra_msg   = '';

    $startup    = trim(get_argument('startup', 1, ''));
    $scrips     = get_argument('scrips', 0, array());

    if (strlen($startup)) {
        // check ALL or NONE
        $lc_startup = strtolower($startup);
        if (preg_match('/^all|none$/', $lc_startup)) {
            $msg = "The Scrip configuration name cannot be <i>all</i> 
                or <i>none</i>. Please choose another name";
        }
    } else
    // check for blank name        
    {
        // check for blank name    
        $msg = "The Scrip configuration name cannot be blank.";
    }

    if ($msg == '') {
        // insert into Startupnames table 
        $sql  = "INSERT INTO Startupnames SET";
        $sql .= " startup='$startup',\n";
        $sql .= " installuserid = $authuserid";
        $res  = redcommand($sql, $db);
        if (!$res) {
            $problem    = 1;
            $sql_error  = mysqli_error($GLOBALS["___mysqli_ston"]);
            $sql_errno  = mysqli_errno($GLOBALS["___mysqli_ston"]);

            // check for duplicate startupname
            $key_index = get_key_index('install', 'Startupnames', 'uniq', $db);
            if ($sql_errno == 1062) // 1062 is the error code for dup entry
            {
                if (preg_match("/\b$key_index\b/", $sql_error)) {
                    $xtra_msg = "The Scrip configuration name <b>$startup</b> 
                           is a duplicate of an existing Scrip configuration name.";
                }
            }
        } else {
            $id = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);

            // insert into Startupscrips table
            reset($scrips);
            foreach ($scrips as $key => $scripid) {
                $sql  = "INSERT INTO Startupscrips SET";
                $sql .= " startupnameid=$id,\n";
                $sql .= " scrip=$scripid\n";
                $res  = redcommand($sql, $db);
                if (!$res) {
                    $problem    = 1;
                    $sql_error  = mysqli_error($GLOBALS["___mysqli_ston"]);
                    $sql_errno  = mysqli_errno($GLOBALS["___mysqli_ston"]);

                    // check for duplicate scrips
                    $key_index = get_key_index('install', 'Startupscrips', 'uniq', $db);
                    if ($sql_errno == 1062) // 1062 is the error code for dup entry
                    {
                        if (preg_match("/\b$key_index\b/", $sql_error)) {
                            $xtra_msg = "The Scrip <b>$scripid</b> is a duplicate 
                                            of an existing Scrip.";
                        }
                    }
                }
            }
        }

        if ($problem) {
            $msg = "Unable to add Scrip configuration <b>$startup</b>. $xtra_msg";
        } else {
            $log  = "install: Scrip configuration '$startup' added by $authuser.";
            logs::log(__FILE__, __LINE__, $log, 0);
            $msg  = "New Scrip configuration <b>$startup</b> added.";
        }
    }
    message($msg);
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

$action = strval(get_argument('action', 0, 'none'));
$id     = get_argument('id', 0, 0);

switch ($action) {
    case 'add':
        $title = 'Adding Scrip Configuration';
        break;
    case 'edit':
        $title = 'Updating Scrip Configurations';
        break;
    case 'delete':
        $title = 'Confirm Scrip Configuration Delete';
        break;
    case 'reallydelete':
        $title = 'Deleting Scrip Configuration';
        break;
    default:
        $title = 'Action Unknown';
        break;
}

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer) 
echo install_html_header($title, $comp, $authuser, $priv_admin, $priv_servers, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

switch ($action) {
    case 'add':
        insert_option($authuser, $authuserdata['installuserid'], $db);
        break;
    case 'edit':
        update_option($id, $authuser, $db);
        break;
    case 'delete':
        confirm_delete_option($id, $db);
        break;
    case 'reallydelete':
        delete_option($id, $authuser, $db);
        break;
    default:
        break;
}

/* Hardwired to pass in hfn for the user. */
$user = 'hfn';
echo head_standard_html_footer($user, $db);
