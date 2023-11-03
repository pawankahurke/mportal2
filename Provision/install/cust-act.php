<?php

/*
  Revision history:

  Date        Who     What
  ----        ---     ----
  04-Oct-19   SVG      Creation.
  16-Oct-19   SVG      Import Customer
 */

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-cnst.php');
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-dberr.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-cust.php');
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

function update_cust($admin, $id, $authuser, $db)
{
    //$sql_pwd = '';
    //$sql_site_pwd = '';
    $msg = '';
    $problem = 0;
    //$xtra_msg = '';
    //$skulist = trim(get_argument('skulist', 1, ''));
    $skulist = implode(',', get_argument('skulist', 0, array()));
    $installCustmer = trim(get_argument('installCustmer', 1, ''));

    if (($skulist == '') || ($id == '')) {
        $msg = 'Invalid update details.';
    }


    if ($msg == '') {

        $time = time();

        $sql = "update Customers set";
        $sql .= " sku_list='$skulist',\n";
        $sql .= " last_update='$time' \n";
        $sql .= " where cid = '$id'";
        //        echo $sql;
        //        die();
        $res = redcommand($sql, $db);
        if (!$res) {
            $problem = 1;
        }

        if ($problem) {
            $msg = "Unable to update customer <b>$installCustmer</b>.";
        } else {
            $msg = "Customer <b>$installCustmer</b> updated.";
            logs::log(__FILE__, __LINE__, $msg, 0);
        }
    }
    message($msg);
}

function find_customer($id, $db)
{
    $usr = array();
    $sql = "select * from Customers where cid = '$id'";
    $res = redcommand($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) == 1) {
            $usr = mysqli_fetch_array($res);
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $usr;
}

function confirm_delete_cust($id, $db)
{
    $cust = find_customer($id, $db);
    if ($cust) {
        $self = server_var('PHP_SELF');
        $referer = server_var('HTTP_REFERER');
        $href = "$self?action=reallydelete&id=$id";
        $yes = "[<a href='$href'>Yes</a>]";
        $no = "[<a href='$referer'>No</a>]";

        $custname = $cust['customer_name'];
        $msg = "Are you sure you want to delete customer <b>$custname</b>?<br>";
        $msg .= "<br>";
        $msg .= "$yes&nbsp;&nbsp;&nbsp;$no";
        message($msg);
    }
}

function delete_cust($id, $admin, $authuser, $db)
{
    if ($admin) {
        $cust = find_customer($id, $db);
        if ($cust) {
            $name = $cust['customer_name'];
            $sql = "DELETE FROM Customers WHERE cid = '$id'";
            $res = redcommand($sql, $db);
            if ($res) {
                /* $sql = "DELETE FROM Servers WHERE installuserid = $id";
                  redcommand($sql, $db);
                  $sql = "DELETE FROM Sites WHERE installuserid = $id";
                  redcommand($sql, $db);
                  $sql = "DELETE FROM Siteemail WHERE installuserid = $id";
                  redcommand($sql, $db);
                  $sql = "SELECT startupnameid FROM Startupnames WHERE installuserid = $id";
                  $res = redcommand($sql, $db);
                  $startupnameids = array();
                  if ($res)
                  while ($row = mysql_fetch_array($res))
                  $startupnameids[] = $row['startupnameid'];
                  $startid_string = implode(",", $startupnameids);
                  $sql = "DELETE FROM Startupnames WHERE installuserid = $id";
                  $res = redcommand($sql, $db);
                  if (strlen($startid_string)) {
                  $sql = "DELETE FROM Startupscrips WHERE startupnameid IN ($startid_string)";
                  $res = redcommand($sql, $db);
                  } */

                $msg = "Customer <b>$name</b> has been removed.";
                //$log = "install: User '$name' removed by $authuser.";
                //logs::log(__FILE__, __LINE__, $log, 0);
                logs::log(__FILE__, __LINE__, $msg, 0);
            } else {
                $msg = "There was a problem deleting customer <b>$name</b>. Please try again.";
            }
        } else {
            $msg = "Customer <b>$id</b> does not exist.";
        }
    } else {
        $msg = "Authorization denied.";
    }
    message($msg);
}

function insert_customer($authuser, $id, $db)
{
    //$sql_pwd = '';
    //$sql_site_pwd = '';
    $msg = '';
    $problem = 0;
    $xtra_msg = '';

    $installCustmer = trim(get_argument('installCustmer', 1, ''));
    //$skulist = trim(get_argument('skulist', 1, ''));
    $skulist = implode(',', get_argument('skulist', 0, array()));

    if ((!strlen($installCustmer)) || (strlen($installCustmer) === 0)) {
        $msg = "Customer name cannot be blank.";
    }

    $sql_exist = "select * from Customers where customer_name = '$installCustmer'";
    $res_exist = command($sql_exist, $db);
    if (mysqli_num_rows($res_exist) > 0) {
        $msg = "The customer name <b>$installCustmer</b> is a duplicate
                                     of an existing user name.";
    }

    if ($msg == '') {

        // insert into Customer table

        $time = time();

        $sql = "INSERT INTO Customers SET\n";
        $sql .= " customer_name='$installCustmer',\n";
        $sql .= " tenant_id='$id',\n";
        $sql .= " sku_list='$skulist',\n";
        $sql .= " created_time=$time\n";

        $res = command($sql, $db);
        if (!affected($res, $db)) {
            $problem = 1;
        }

        if ($problem) {
            $msg = "Unable to add customer <b>$installCustmer</b>. $xtra_msg";
        } else {
            $msg = "New customer <b>$installCustmer</b> added.";
            logs::log(__FILE__, __LINE__, $msg, 0);
        }
    }

    message($msg);
}

function export_cust($id, $db)
{

    $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "install.Customers WHERE cid = $id";
    $res = command($sql, $db);

    if ($res) {
        $row = mysqli_fetch_assoc($res);

        $exportArray = ['user' => $row];

        $name = uniqid() . '.' . time() . '.csv';
        $fullPath = 'csv/' . $name;

        $encData = encryptData($exportArray);
        $exportCSVArray = ['Export Key' => $encData];

        ob_clean();
        $fp = fopen($fullPath, 'w');
        fputcsv($fp, $exportCSVArray);
        fclose($fp);
        download_send_headers($fullPath, $name);
        exit();
    }
}

function import_tenant_key($db)
{

    $msg = '';
    $problem = 0;
    $now = time();

    $tenantActKey = trim(get_argument('tenantactkey', 1, ''));

    if ($tenantActKey == '') {
        $msg = "Tenant Import key field value cannot be blank.";
    }

    if ($msg == '') {
        $key = 'hnhj7vqj9n';
        $c = base64_decode($tenantActKey);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);

        if (hash_equals($hmac, $calcmac)) {     //PHP 5.6+ timing attack safe comparison
            $slaveTenantInfo = unserialize($original_plaintext);
            //secho '<pre>'; print_r($slaveTenantInfo); die();
            $userInfo = $slaveTenantInfo['user'];
            $serverInfo = $slaveTenantInfo['server'];

            $sql = "insert into " . $GLOBALS['PREFIX'] . "install.Servers set ";
            foreach ($serverInfo as $key => $value) {
                $sql .= $key . " = '$value',";
            }
            $servsql = rtrim($sql, ',');
            $servres = redcommand($servsql, $db);

            $sql = "insert into " . $GLOBALS['PREFIX'] . "install.Users set ";
            foreach ($userInfo as $key => $value) {
                $sql .= $key . " = '$value',";
            }
            $usersql = rtrim($sql, ',');
            $userres = redcommand($usersql, $db);

            if (!$servres || !$userres) {
                $problem = 1;
            }

            if ($problem) {
                $msg = "Tenant key import failed";
            } else {
                $msg = "Tenant key imported successfully";
                $log = "install: Tenant key created.";
                logs::log(__FILE__, __LINE__, $log, 0);
            }
        }
    }
    message($msg);
}

function export_license_key($id, $priv_admin, $authuser, $db)
{
    $sql = "select installuser, skuids, token from " . $GLOBALS['PREFIX'] . "install.Users where installuserid = $id";
    $res = command($sql, $db);
    if ($res) {
        $row = mysqli_fetch_assoc($res);
        if (isset($row['skuids']) && $row['skuids'] != '') {
            $skulist = $row['skuids'];
            $token = $row['token'];
            $skusql = "select * from " . $GLOBALS['PREFIX'] . "install.skuOfferings where sid in ($skulist)";
            $skures = command($skusql, $db);
            if ($skures) {
                while ($row = mysqli_fetch_assoc($skures)) {
                    $skudata[] = $row;
                }
            }
            $exportArray = ['token' => $token, 'data' => $skudata];
            $encSkuData = encryptData($exportArray);
            $csvContArr = ['License Key' => $encSkuData];
            $name = uniqid('LIC_') . '.' . time() . '.csv';
            $fullPath = 'csv/' . $name;

            ob_clean();
            $fp = fopen($fullPath, 'w');
            fputcsv($fp, $csvContArr);
            fclose($fp);
            download_send_headers($fullPath, $name);
            exit();
        } else {
            echo "Failed: No SKU found to export!";
        }
    } else {
        echo "No result found!";
    }
}

function encryptData($exportArray)
{
    $key = 'hnhj7vqj9n';
    $plaintext = serialize($exportArray);
    $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
    $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
    $ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);

    return $ciphertext;
}

function download_send_headers($filePath, $filename)
{
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download  
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");
    header('Content-Type: text/x-csv');

    // disposition / encoding on response body

    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");

    readfile($filePath);
}

function decryptKeyDetails()
{
    $encryptedData = trim(get_argument('encryptedkey', 1, ''));

    $key = 'hnhj7vqj9n';
    $c = base64_decode($encryptedData);
    $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
    $iv = substr($c, 0, $ivlen);
    $hmac = substr($c, $ivlen, $sha2len = 32);
    $ciphertext_raw = substr($c, $ivlen + $sha2len);
    $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
    $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);

    if (hash_equals($hmac, $calcmac)) {     //PHP 5.6+ timing attack safe comparison
        $decryptedData = unserialize($original_plaintext);
        echo '<pre>';
        echo json_encode($decryptedData, JSON_PRETTY_PRINT);
    }
}

function import_cust_license_key($authuser, $db)
{

    $msg = '';

    $CustActKey = trim(get_argument('custkey', 1, ''));

    if ($CustActKey == '') {
        $msg = "Customer Import key field value cannot be blank.";
    }

    if ($msg == '') {
        $key = 'hnhj7vqj9n';
        $c = base64_decode($CustActKey);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);

        if (hash_equals($hmac, $calcmac)) {     //PHP 5.6+ timing attack safe comparison
            $slaveTenantInfo = unserialize($original_plaintext);
            //echo '<pre>'; print_r($slaveTenantInfo); die();
            $userInfo = $slaveTenantInfo['user'];

            if (isset($userInfo)) {
                $usrsql = "insert into " . $GLOBALS['PREFIX'] . "install.Customers set ";
                foreach ($userInfo as $key => $value) {
                    if ($key == 'cid') {
                        $cid_key = $value;
                    }
                    $usrsql .= $key . " = '$value',";
                }
                $custsql = rtrim($usrsql, ',');

                $chksql = "select count(cid) as icnt from " . $GLOBALS['PREFIX'] . "install.Customers where cid = '$cid_key' limit 1";
                $chkres = command($chksql, $db);
                if ($chkres) {
                    $instcustdata = mysqli_fetch_assoc($chkres);
                    if ($instcustdata['icnt'] > 0) {
                        $usrupdtsql = "update " . $GLOBALS['PREFIX'] . "install.Customers set ";
                        foreach ($userInfo as $updtkey => $updtvalue) {
                            if ($updtkey == 'cid') {
                                $cid_key = $updtvalue;
                            }
                            $usrupdtsql .= $updtkey . " = '$updtvalue',";
                        }
                        $custupdtsql = rtrim($usrupdtsql, ',');
                        $custupdtsql .= " where cid = '$cid_key';";
                        $custupdtres = redcommand($custupdtsql, $db);
                        if ($custupdtres) {
                            $msg = "Customer data has been updated successfully.";
                        }
                    } else {
                        $custres = redcommand($custsql, $db);
                        if ($custres) {
                            $msg = "Customer key imported successfully.";
                        } else {
                            $msg = "Customer key import failed. Query failed to execute!";
                        }
                    }
                } else {
                    $msg = "Customer has been already created with this key.";
                }
            }
        } else {
            $msg = "Invalid customer key!";
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
        /* case 'add' : $title = 'Adding Customer';
      break;
      case 'edit' : $title = 'Updating Customer';
      break;
      case 'delete' : $title = 'Confirm Customer Delete';
      break;
      case 'reallydelete' : $title = 'Deleting Customer';
      break;
      case 'expCust' : $title = "Export Customer";
      break;
      case 'importtenantkey' : 'Import Tenant Key';
      break;
      case 'explicensekey' : 'Export License key';
      break; */
    case 'decryptdetails':
        'Decrypt key Details';
        break;
    case 'importcustlicense':
        'Import Customer License';
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
        /* case 'add' : insert_customer($authuser, $id, $db);
      break;
      case 'edit' : update_cust($priv_admin, $id, $authuser, $db);
      break;
      case 'delete' : confirm_delete_cust($id, $db);
      break;
      case 'reallydelete' : delete_cust($id, $priv_admin, $authuser, $db);
      break;
      case 'expCust' : export_cust($id, $db);
      break;
      case 'importtenantkey' : import_tenant_key($db);
      break;
      case 'explicensekey' : export_license_key($id, $priv_admin, $authuser, $db);
      break; */
    case 'decryptdetails':
        decryptKeyDetails();
        break;
    case 'importcustlicense':
        import_cust_license_key($authuser, $db);
        break;
    default:
        break;
}

/* Hardwired to pass in hfn for the user. */
$user = 'hfn';
echo head_standard_html_footer($user, $db);
