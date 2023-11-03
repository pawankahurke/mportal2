<?php

require_once("../include/common_functions.php");
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-util.php';
include_once '../lib/l-patch.php';
include_once '../lib/l-dashboard.php';

$db = db_connect();


$mgroupidres = "select mgroupid from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = 'All'";
$mgroupidArray = find_one($mgroupidres, $db);
$mgroupid = $mgroupidArray['mgroupid'];

$rowres = "select * from " . $GLOBALS['PREFIX'] . "softinst.WUConfig where mgroupid = $mgroupid";
$queryres = find_one($rowres, $db);
$chain = $queryres['chainseconds'] / 3600;
$cache = $queryres['cacheseconds'] / 86400;
$install = $queryres['installday'];
$inshour = $queryres['installhour'];

$server = $queryres['serverurl'];

$manage = $queryres['management'];
$strmanage = '';
switch ($manage) {

    case 1:
        $strmanage = "Disable";
        break;
    case 2:
        $strmanage = "Manage from Server";
        break;
    case 3:
        $strmanage = "User controlled download and install";
        break;
    case 4:
        $strmanage = "Automated download, user controlled install";
        break;
    case 5:
        $strmanage = "Automated download and install";
        break;
    default:
        $strmanage = "Unknown ($manage)";
}


$newupdate = $queryres['newpatches'] == 1 ? 'Act based on last settings from server' : 'Wait to get current settings from server before taking action';

$propagate = $queryres['propagate'];
$downlaod = '';
switch ($propagate) {
    case 0:
        $downlaod = 'Only download from vendor';
        break;
    case 1:
        $downlaod = 'Only retrieve from local machines';
        break;
    case 2:
        $downlaod = 'Try to retrieve from local machines, then download from vendor if unsuccessful';
        break;
    default:
        $strmanage = "Unknown ($propagate)";
}


$retention = $queryres['updatecache'] == 1 ? 'Do not keep updates on this machine for other machines to use' : 'Keep updates on this machine' . $cache . ' days, for other machines to use';

$resartpolicy = $queryres['restart'] == 1 ? 'Do not automatically restart when a restart is necessary after an installation.' : 'Automatically restart when a restart is necessary after an installation';

$chaindata = $queryres['chain'];
$strchain = '';
switch ($chaindata) {
    case 1:
        $strchain = 'Repeat install cycle until machine is up to date, but stop after' . $chain . 'hours';
        break;
    case 2:
        $strchain = 'Repeat install cycle until machine is up to date.';
        break;
    case 3:
        $strchain = 'Only do one install cycle';
        break;
    default:
        $strchain = "Unknown ($chaindata)";
}
