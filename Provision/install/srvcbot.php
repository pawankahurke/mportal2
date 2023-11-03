<?php

/*
  Revision history:

  Date        Who     What
  ----        ---     ----
  05-Mar-19   JHN     File Created for Service Bot Webhook events information
 * 
 * 
 */

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include_once('../lib/l-cnst.php');
include_once('../lib/l-util.php');
include_once('../lib/l-db.php');
include_once('../lib/l-sql.php');
include_once('../lib/l-dberr.php');
include_once('../lib/l-serv.php');
include_once('../lib/l-rcmd.php');
include_once('../lib/l-slct.php');
include_once('../lib/l-user.php');
include_once('../lib/l-head.php');
include_once('../lib/l-errs.php');

$content = file_get_contents('php://input');

logs::log("srvcbot.log", 'Raw JSON Data : ' . $content);

$data = safe_json_decode($content, true);

//print_r($data);

if ($data['event_name'] === 'post_provision') {
    foreach ($data['event_data'] as $key => $value) {
        if ($key === 'request') {
            $emailid = $value['email'];
        }
        if ($key === 'instance') {
            if (array_key_exists('references', $value)) {
                foreach ($value['references'] as $refkey => $refval) {
                    if ($refkey === 'charge_items') {
                        $data_id = $refval[0]['id'];
                        $user_id = $refval[0]['user_id'];
                        $subscrp_id = $refval[0]['service_instance_id'];
                    }
                }
            }
        }
    }


    $db = db_code('db_ins');
    if ($db) {
        // $fp = fopen('serverinfo.log', 'a');
        $updateSql = "update Siteemail set userid = '$user_id', subscriptionid = '$subscrp_id', maxinstall = 1 "
            . "where email = '$emailid'";
        $updateRes = command($updateSql, $db);

        // fwrite($fp, 'Update Query : ' . $updateSql . PHP_EOL);

        $getRepSql = "select serverid from " . $GLOBALS['PREFIX'] . "install.Sites where email = '$emailid'";
        $getRepRes = command($getRepSql, $db);
        if (mysqli_num_rows($getRepRes) > 0) {
            $repoData = mysqli_fetch_array($getRepRes);
            // fwrite($fp, 'Server Info : ' . $repoData['serverid']);
            // fclose($fp);
        }
    }
}
