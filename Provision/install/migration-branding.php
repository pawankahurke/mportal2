<?php

/*
 * Temporary file created to update branding url changes
 */

include_once('../lib/l-cnst.php');
include_once('../lib/l-util.php');
include_once('../lib/l-db.php');
include_once('../lib/l-sql.php');
include_once('../lib/l-serv.php');
include_once('../lib/l-slct.php');
include_once('../lib/l-rcmd.php');
include_once('../lib/l-user.php');
include_once('../lib/l-head.php');
include_once('header.php');
include_once('../lib/l-errs.php');

$db = db_connect();
db_change($GLOBALS['PREFIX'] . 'install', $db);

$updtcnt = 0;

$sql = "select siteid, brandingurl from " . $GLOBALS['PREFIX'] . "install.Sites";
$res = redcommand($sql, $db);

if ($res) {
    if (mysqli_num_rows($res) > 0) {
        $totalCount = mysqli_num_rows($res);
        while ($row = mysqli_fetch_assoc($res)) {
            $siteid = $row['siteid'];
            $brandingdata = explode('/', $row['brandingurl']);
            $brandingfile = end($brandingdata);
            // insert into Sites table [1]
            // update branding data here 
            $updtsql = "update Sites SET brandingurl = '$brandingfile' where siteid = $siteid";
            $updtres = redcommand($updtsql, $db);

            if ($updtres) {
                $updtcnt++;
            }
        }
    }
    ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
}

echo '<pre>';
echo 'Total Records => ' . $totalCount . ' | Updated Records => ' . $updtcnt . '<br/>';
if ($totalCount == $updtcnt) {
    echo 'Branding file updated sucessfully!';
} else {
    echo 'Failed to update some Branding file';
}
