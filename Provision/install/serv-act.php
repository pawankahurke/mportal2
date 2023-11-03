<?php

/*
Revision history:

Date        Who     What
----        ---     ----
20-May-03   NL      Creation.
28-May-03   NL      insert_server(): insert installuserid, not installuser(name).
29-May-03   NL      message(): add stripslashes
29-May-03   NL      install_html_header(): pass $priv_servers (to display servers link).
29-May-03   NL      check_pwd_change(): pass $username not $servername
02-Jun-03   NL      Call install_html_footer (has its own version).
16-Jun-03   NL      Change include line: '../lib/l-head.php' --> 'header.php'.
23-Jul-03   NL      Change footer to standard_html_footer().
28-Jul-03   NL      Change page titles.
28-Jul-03   NL      Change page titles (create --> add).
31-Jul-03   EWB     Uses install_login($db);
 7-Aug-03   NL      Change all text messages: create --> add.
 8-Aug-03   NL      Change title (Editing --> Updating).
 8-Aug-03   NL      Change all text messages: Server --> ASI Server.
 8-Aug-03   NL      update_server(), insert_server(): Process form field "Available to all".
14-Aug-03   NL      insert_server(), update_server(): Check for dup entries using db (not PHP).
14-Aug-03   NL      message(): add another newline.
15-Aug-03   NL      insert_server(), update_server(): Use get_key_index for dup name.
28-Aug-03   NL      insert_server(), update_server(): Match on error code instead of error message.
29-Aug-03   NL      Include lib/l-dberr.php for get_key_index().
25-Sep-03   NL      Add "install:" and "by $authuser" to all error_log entries;
                    Create entries for all db actions.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
19-Jun-07   AAM     Updated index references to match database changes.
03-Oct-08   BTE     Bug 4828: Change customization feature of server.
30-Sep-19   SHG     Mac/iOS/Linux client upload option added.
                    
*/

//error_reporting(E_ALL);
//ini_set('display_errors', 'On');
/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */
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
include('../lib/l-config.php');

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

function uploadWithFtp($name)
{
    $fileName = $_FILES[$name]['name'];
    $fileInfo = pathinfo($fileName);
    $fileExtension = isset($fileInfo['extension']) ? $fileInfo['extension'] : false;

    if (!$fileExtension) {
        return ['status' => false, 'message' => "File extension not found"];
    }

    // $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/Provision/download';
    $uploadDir = '/home/nanoheal/setups/live';

    $newFileName = $fileName;
    $location = $uploadDir . "/" . $newFileName;

    if (!move_uploaded_file($_FILES[$name]['tmp_name'], $location))
        exit('file upload error.please check folder permission');


    /*  global $ftp_server;
        global $ftp_username;
        global $ftp_userpass;
        global $ftp_downloadpath;

        $ftp_conn = ftp_connect($ftp_server) or die("Could not connect to $ftp_server");
        $login = ftp_login($ftp_conn, $ftp_username, $ftp_userpass);
        $remoteLocation = '/home/nanoheal/setups/live/'.$newFileName;
        if (!ftp_put($ftp_conn, $remoteLocation, $location, FTP_BINARY))
        {
          return ['status' => false, 'message' => "Unable to upload file with ftp"];
        } else {
            @ftp_chmod($ftp_conn, 0777, $remoteLocation);
        }
        
        ftp_close($ftp_conn);
        @unlink($location); */

    return ['status' => true];
}

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

function update_server($id, $authuser, $db)
{
    $sql_pwd    = '';
    $msg        = '';
    $problem    = 0;
    $xtra_msg   = '';

    $servername = trim(get_argument('servername', 1, ''));
    $domainname = trim(get_argument('domainname', 1, ''));
    $global     = trim(get_argument('global', 0, 0));
    $serverurl     = trim(get_argument('serverurl', 0, 0));
    $notifyemail = trim(get_argument('notifyemail', 0, ''));
    $reportemail = trim(get_argument('reportemail', 0, ''));
    $url        = trim(get_argument('url', 0, ''));
    $strmngurl  = trim(get_argument('strmngurl', 0, ''));

    // check for blank server name
    if (!strlen($servername)) {
        $msg = "Server name cannot be blank.";
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
            $msg = "Android client upload error";
        }
        $clientApkUpload = true;
        $clientApkFileName = $_FILES[$clientApkName]['name'];
    }

    if (isset($_FILES[$clientMacName]) && isset($_FILES[$clientMacName]['name']) && !empty($_FILES[$clientMacName]['name'])) {
        if (!isset($_FILES[$clientMacName]['error']) || $_FILES[$clientMacName]['error'] != 0) {
            $msg = "mac client upload error";
        }
        $clientMacUpload = true;
        $clientMacFileName = $_FILES[$clientMacName]['name'];
    }

    if (isset($_FILES[$clientIosName]) && isset($_FILES[$clientIosName]['name']) && !empty($_FILES[$clientIosName]['name'])) {
        if (!isset($_FILES[$clientIosName]['error']) || $_FILES[$clientIosName]['error'] != 0) {
            $msg = "IOS client upload error";
        }
        $clientIosUpload = true;
        $clientIosFileName = $_FILES[$clientIosName]['name'];
    }

    if (isset($_FILES[$clientLinuxName]) && isset($_FILES[$clientLinuxName]['name']) && !empty($_FILES[$clientLinuxName]['name'])) {
        if (!isset($_FILES[$clientLinuxName]['error']) || $_FILES[$clientLinuxName]['error'] != 0) {
            $msg = "Linux client upload error";
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
        // update Servers table
        $sql  = "UPDATE Servers SET";
        $sql .= " servername='$servername',\n";
        $sql .= " serverurl='$serverurl',\n";
        $sql .= " global='$global',\n";
        $sql .= " notifyemail='$notifyemail',\n";
        $sql .= " reportemail='$reportemail',\n";
        $sql .= " url='$url',\n";
        $sql .= " streamingurl='$strmngurl'\n";

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

        $sql .= " WHERE serverid = $id";
        $res  = redcommand($sql, $db);

        $chksql = "select domainurl from apiConfig limit 1";
        $chkres = redcommand($chksql, $db);
        if ($chkres) {
            if (mysqli_num_rows($chkres) == 1) {
                $domaindata = mysqli_fetch_assoc($chkres);
            }
            ((mysqli_free_result($chkres) || (is_object($chkres) && (get_class($chkres) == "mysqli_result"))) ? true : false);
        }
        if ($domaindata['domainurl'] == '') {
            $dsql = "insert ignore into apiConfig (domainurl) values ('$domainname')";
        } else {
            $dsql = "update apiConfig set domainurl = '$domainname' where id > 0";
        }
        redcommand($dsql, $db);

        if (!$res) {
            $problem    = 1;
            $sql_error  = mysqli_error($GLOBALS["___mysqli_ston"]);
            $sql_errno  = mysqli_errno($GLOBALS["___mysqli_ston"]);

            // check for duplicate server name
            $key_index = get_key_index('install', 'Servers', 'uniq', $db);
            if ($sql_errno == 1062) // 1062 is the error code for dup entry
            {
                if (preg_match("/\b$key_index\b/", $sql_error)) {
                    $xtra_msg = "The server name <b>$servername</b> is a duplicate
                                    of an existing server name.";
                }
            }
        }

        if ($problem) {
            $msg = "Unable to update server <b>$servername</b>. $xtra_msg";
        } else {
            $msg  = "Server <b>$servername</b> updated.";
            $log = "install: Server '$servername' updated by $authuser.";
            logs::log(__FILE__, __LINE__, $log, 0);
        }
    }

    message($msg);
}

function find_server($id, $db)
{
    $server = array();
    $sql = "select * from Servers where serverid = $id";
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $server = mysqli_fetch_array($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $server;
}

function confirm_delete_server($id, $db)
{
    $server = find_server($id, $db);
    if ($server) {
        $self   = server_var('PHP_SELF');
        $referer = server_var('HTTP_REFERER');
        $href = "$self?action=reallydelete&id=$id";
        $yes  = "[<a href='$href'>Yes</a>]";
        $no   = "[<a href='$referer'>No</a>]";

        $servername = $server['servername'];
        $msg  = "Are you sure you want to delete server <b>$servername</b>?<br>";
        $msg .= "<br>";
        $msg .= "$yes&nbsp;&nbsp;&nbsp;$no";
        message($msg);
    }
}

function delete_server($id, $authuser, $db)
{
    $server = find_server($id, $db);
    if ($server) {
        $servername = $server['servername'];
        $sql = "DELETE FROM Servers WHERE serverid = $id";
        redcommand($sql, $db);
        $msg = "Server <b>$servername</b> has been removed.";
        $log = "install: Server '$servername' removed by $authuser.";
        logs::log(__FILE__, __LINE__, $log, 0);
    } else {
        $msg = "Server <b>$id</b> does not exist.";
    }

    message($msg);
}

function insert_server($authuser, $userid, $id, $db)
{
    $sql_pwd    = '';
    $msg        = '';
    $problem    = 0;
    $xtra_msg   = '';

    $servername = trim(get_argument('servername', 1, ''));
    $domainname = trim(get_argument('domainname', 1, ''));
    $serverurl = trim(get_argument('serverurl', 1, ''));
    $global     = trim(get_argument('global', 0, 0));
    $notifyemail = trim(get_argument('notifyemail', 0, ''));
    $reportemail = trim(get_argument('reportemail', 0, ''));
    $url        = trim(get_argument('url', 0, ''));
    $strmngurl  = trim(get_argument('strmngurl', 0, ''));

    // check for blank server name
    if (!strlen($servername)) {
        $msg = "Server name cannot be blank.";
    }

    if (!strlen($domainname)) {
        $msg = "Please enter the domain name.";
    }

    // upload enhancement start

    $client32Upload = $client64Upload = false;
    $client32Name = 'executable_client_32';
    $client64Name = 'executable_client_64';
    $clientApkName = 'executable_client_apk';
    $clientMacName = 'executable_client_mac';
    $clientIosName = 'executable_client_ios';
    $clientLinuxName = 'executable_client_linux';

    /*         if(
            (!isset($_FILES[$client32Name]) || !isset($_FILES[$client32Name]['name']) || empty($_FILES[$client32Name]['name']))
            &&
            (!isset($_FILES[$client64Name]) || !isset($_FILES[$client64Name]['name']) || empty($_FILES[$client64Name]['name']))
            &&
            (!isset($_FILES[$clientApkName]) || !isset($_FILES[$clientApkName]['name']) || empty($_FILES[$clientApkName]['name']))
            &&
            (!isset($_FILES[$clientMacName]) || !isset($_FILES[$clientMacName]['name']) || empty($_FILES[$clientMacName]['name']))
            &&
            (!isset($_FILES[$clientIosName]) || !isset($_FILES[$clientIosName]['name']) || empty($_FILES[$clientIosName]['name']))
            &&
            (!isset($_FILES[$clientLinuxName]) || !isset($_FILES[$clientLinuxName]['name']) || empty($_FILES[$clientLinuxName]['name']))
        ){
             $msg = "32 bit,64 bit Client, android client,mac client,ios client and linux client upload is mandatory";
        } 
        else if(!isset($_FILES[$client32Name]['error']) || $_FILES[$client32Name]['error']!=0){
             $msg = "Client 32 bit upload error";
        } else if(!isset($_FILES[$client64Name]['error']) || $_FILES[$client64Name]['error']!=0){
             $msg = "Client 64 bit upload error";
        } else if(!isset($_FILES[$clientApkName]['error']) || $_FILES[$clientApkName]['error']!=0){
             $msg = "Android client upload error";
        }else if(!isset($_FILES[$clientMacName]['error']) || $_FILES[$clientMacName]['error']!=0){
             $msg = "mac client upload error";
        }else if(!isset($_FILES[$clientIosName]['error']) || $_FILES[$clientIosName]['error']!=0){
             $msg = "iOS client upload error";
        }else if(!isset($_FILES[$clientLinuxName]['error']) || $_FILES[$clientLinuxName]['error']!=0){
             $msg = "Linux client upload error";
        }
         */
    if ($_FILES[$client32Name]['name'] != '') {
        $upoadFtpData = uploadWithFtp($client32Name);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($_FILES[$client64Name]['name'] != '') {
        $upoadFtpData = uploadWithFtp($client64Name);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($_FILES[$clientApkName]['name'] != '') {
        $upoadFtpData = uploadWithFtp($clientApkName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($_FILES[$clientMacName]['name'] != '') {
        $upoadFtpData = uploadWithFtp($clientMacName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($_FILES[$clientIosName]['name'] != '') {
        $upoadFtpData = uploadWithFtp($clientIosName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    if ($_FILES[$clientLinuxName]['name'] != '') {
        $upoadFtpData = uploadWithFtp($clientLinuxName);
        if (!$upoadFtpData['status']) {
            $msg = $upoadFtpData['message'];
        }
    }

    $client32FileName = $_FILES[$client32Name]['name'];
    $client64FileName = $_FILES[$client64Name]['name'];
    $clientApkFileName = $_FILES[$clientApkName]['name'];
    $clientMacFileName = $_FILES[$clientMacName]['name'];
    $clientIosFileName = $_FILES[$clientIosName]['name'];
    $clientLinuxFileName = $_FILES[$clientLinuxName]['name'];

    if ($msg == '') {
        // insert into Servers table
        $sql  = "INSERT INTO Servers SET\n";
        $sql .= " servername='$servername',\n";
        $sql .= " installuserid='$userid',\n";
        $sql .= " serverurl='$serverurl',\n";
        $sql .= " global='$global',\n";
        $sql .= " notifyemail='$notifyemail',\n";
        $sql .= " reportemail='$reportemail',\n";
        $sql .= " url='$url',\n";
        $sql .= " streamingurl='$strmngurl',\n";
        $sql .= " client_32_name='$client32FileName'\n";
        $sql .= " ,client_64_name='$client64FileName'\n";
        $sql .= " ,client_android_name='$clientApkFileName'\n";
        $sql .= " ,client_mac_name='$clientMacFileName'\n";
        $sql .= " ,client_ios_name='$clientIosFileName'\n";
        $sql .= " ,client_linux_name='$clientLinuxFileName'\n";


        // upload enhancement end
        $res  = redcommand($sql, $db);

        $dsql = "insert ignore into apiConfig (domainurl) values ('$domainname')";
        redcommand($dsql, $db);

        if (!$res) {
            $problem    = 1;
            $sql_error  = mysqli_error($GLOBALS["___mysqli_ston"]);
            $sql_errno  = mysqli_errno($GLOBALS["___mysqli_ston"]);

            // check for duplicate server name
            $key_index = get_key_index('install', 'Servers', 'uniq', $db);
            if ($sql_errno == 1062) // 1062 is the error code for dup entry
            {
                if (preg_match("/\b$key_index\b/", $sql_error)) {
                    $xtra_msg = "The server name <b>$servername</b> is a duplicate
                                    of an existing server name.";
                }
            }
        }

        if ($problem) {
            $msg = "Unable to add server <b>$servername</b>. $xtra_msg";
        } else {
            $msg  = "New server <b>$servername</b> added.";
            $log = "install: Server '$servername' added by $authuser.";
            logs::log(__FILE__, __LINE__, $log, 0);
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
$userid         = $authuserdata['installuserid'];
$priv_admin     = @($authuserdata['priv_admin'])  ? 1 : 0;
$priv_servers   = @($authuserdata['priv_servers']) ? 1 : 0;

$comp = component_installed();

$action = strval(get_argument('action', 0, 'none'));
$id     = get_argument('id', 0, 0);

switch ($action) {
    case 'add':
        $title = 'Adding ASI Server';
        break;
    case 'edit':
        $title = 'Updating ASI Server';
        break;
    case 'delete':
        $title = 'Confirm ASI Server Delete';
        break;
    case 'reallydelete':
        $title = 'Deleting ASI Server';
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
        insert_server($authuser, $userid, $id, $db);
        break;
    case 'edit':
        update_server($id, $authuser, $db);
        break;
    case 'delete':
        confirm_delete_server($id, $db);
        break;
    case 'reallydelete':
        delete_server($id, $authuser, $db);
        break;
    default:
        break;
}

/* Hardwired to pass in hfn for the user. */
$user = 'hfn';
echo head_standard_html_footer($user, $db);
