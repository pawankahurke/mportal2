<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';

nhRole::dieIfnoRoles(['site']); // roles: site 

if (url::requestToAny('function') != '') { // roles: site 
    $function  = url::requestToAny('function'); // roles: site 
    $function();
}

function getDartDetails()
{

    $db = pdo_connect();
    $machineArr = array();
    $searchVal = $_SESSION['searchValue'];
    $searchType = $_SESSION['searchType'];

    if ($searchType == 'Sites') {
        $searchname = $searchVal;

        $siteMacSql = $db->prepare("SELECT host FROM  " . $GLOBALS['PREFIX'] . "core.Census WHERE site =? ");
        $siteMacSql->execute([$searchname]);
        $siteMacRes = $siteMacSql->fetchAll();
        array_push($machineArr, $siteMacRes['host']);
        $in  = str_repeat('?,', safe_count($machineArr) - 1) . '?';
        $sql = "SELECT UDID, Device_Token, Push_magic FROM " . $GLOBALS['PREFIX'] . "iosprofile.device WHERE machine IN ($in)";
        $stm = $db->prepare($sql);
        $stm->execute($machineArr);
    } else if ($searchType == 'ServiceTag') {
        $searchname = $searchVal;
        $sql = "SELECT UDID, Device_Token, Push_magic FROM " . $GLOBALS['PREFIX'] . "iosprofile.device WHERE machine = ?";
        $stm = $db->prepare($sql);
        $stm->execute([$searchname]);
    } else if ($searchType == 'Groups') {
        $grpMachines = array();
        $groupName = $searchVal;

        $group = $db->prepare("SELECT mgroupid FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ? ");
        $group->execute([$groupName]);
        $groupRes = $group->fetch();

        $groupId = $groupRes['mgroupid'];

        $groupMac = $db->prepare("SELECT site,host FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups as mg JOIN " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as mgm "
            . "on mg.mgroupuniq = mgm.mgroupuniq join " . $GLOBALS['PREFIX'] . "core.Census as c on mgm.censusuniq = c.censusuniq WHERE mg.mgroupid = ?");
        $groupMac->execute([$groupId]);
        $groupMacRes = $groupMac->fetchAll();
        array_push($grpMachines, $groupMacRes['host']);

        $in  = str_repeat('?,', safe_count($grpMachines) - 1) . '?';
        $sql = "SELECT UDID, Device_Token, Push_magic FROM " . $GLOBALS['PREFIX'] . "iosprofile.device WHERE machine IN ($in)";
        $stm = $db->prepare($sql);
        $stm->execute($grpMachines);
    }
    $result = $stm->fetch();
    $resArray = array();
    foreach ($result as $key => $val) {
        if (!empty($val)) {
            $val['Command'] = 'blankpush';
            array_push($resArray, $val);
        }
    }
    $resArray = json_encode($resArray);
    echo $resArray;
}
