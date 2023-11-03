<?php






define('constFieldID',      'id');
define('constFieldUUID',    'uuid');
define('constCensusTemp',   0);
define('constCensus',       1);


define('constLogDBNBegin',      'Beginning DBN import for machine ');
define('constLogDBNBeginMid',   ' version ');
define('constLogDBNInsert',     'Inserted ');
define('constLogDBNInsertMid',  ' row(s) into ');
define('constLogDBNInsertFail', ', some rows failed to insert.');


define('constCategoryAll',      'All');
define('constCategorySite',     'Site');
define('constCategoryUser',     'User');
define('constCategoryMachine',  'Machine');
define('constCategoryWizard',   'Wiz_SCOP_MC');
define('constCategoryOS',       'OS');
define('constCategoryLocal',    'Local');
define('constCategoryOS_Site',  'OS_Site');


function create_coreCensus($table_type, $db)
{
    switch ($table_type) {
        case 0:
            TempCensus_sql($db);
            break;
        default:
    }
}


function TempCensus_sql($db)
{

    $def = 'not null default';
    $sql = "create temporary table temp_Census (\n"
        . "  temp_id    int(11) not null auto_increment,\n"
        . "  site  varchar(50)  $def '',\n"
        . "  host  varchar(64)  $def '',\n"
        . "  temp_uuid  varchar(50)  $def '',\n"
        . "  born  int(11)      $def  0,\n"                  . "  last  int(11)      $def  0,\n"
        . "  code  int(11)      $def  0,\n"                  . "  deleted tinyint(1) $def  0,\n"
        . "  primary key (temp_id),\n"
        . "  key site (site),\n"
        . "  key host (host),\n"
        . "  unique key uniq (site,host),\n"
        . "  unique index guid (temp_uuid)\n"                    . ")";

    $res = redcommand($sql, $db);
    if ($res) {
        $sql = "insert ignore into temp_Census\n"
            . " select id,site,host,uuid,born,last,code,deleted from " . $GLOBALS['PREFIX'] . "core.Census";
        redcommand($sql, $db);
    }
}



function CORE_CreateMachineGroupMap($censusid, $mcatid, $mgroupid)
{
    return "INSERT INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,"
        . "censusuniq,censussiteuniq, updated) SELECT mgroupuniq,"
        . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq,censusuniq,censussiteuniq, UNIX_TIMESTAMP()"
        . " FROM " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories, " . $GLOBALS['PREFIX'] . "core.MachineGroups"
        . " WHERE id=" . $censusid . " AND mcatid=" . $mcatid . " AND "
        . "mgroupid=" . $mgroupid;
}



function CORE_GetCachedTime(&$last, $site, $machine)
{


    $db = db_code('db_cor');
    $newlast = 0;
    $sql = "SELECT id,last FROM " . $GLOBALS['PREFIX'] . "core.Census WHERE site='$site' and host='$machine'";
    $row = redcommand($sql, $db);
    if (!$row) {

        $newlast = 0;
    } else {

        $resVal = mysqli_fetch_assoc($row);
        $newlast = $resVal['last'];
    }

    if ($newlast != 0) {
        $last = $newlast;
    }
}

function CORE_AuditDBNInsert(&$auditBuf, $tableName, $rowCount, $failed)
{
    $auditBuf .= constLogDBNInsert;
    $auditBuf .= (string)$rowCount;
    $auditBuf .= constLogDBNInsertMid;
    $auditBuf .= $tableName;
    if ($failed) {
        $auditBuf .= constLogDBNInsertFail;
    }
    $auditBuf .= "\r\n";
}

function CORE_GetSingleGroupInfoPrec(&$groupName, &$category, &$precedence, $mgroupuniq, $db = null)
{
    $sql = "SELECT name, category, precedence FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups LEFT JOIN " . $GLOBALS['PREFIX'] . "core.MachineCategories ON ("
        . "MachineGroups.mcatuniq=MachineCategories.mcatuniq) WHERE mgroupuniq=?";
    $row = NanoDB::find_one($sql, null, [$mgroupuniq]);
    if (!$row) {
        return false;
    }
    $groupName = $row['name'];
    $category = $row['category'];
    $precedence = $row['precedence'];
    return true;
}

function CORE_GetClientVersion(&$vers, $censusuniq, $db)
{
    // $qcensusuniq = safe_addslashes($censusuniq);
    // $sql = "SELECT id FROM " . $GLOBALS['PREFIX'] . "core.Census WHERE censusuniq='$qcensusuniq'";
    // $row = find_one($sql, $db);
    $row = NanoDB::find_one("SELECT id FROM " . $GLOBALS['PREFIX'] . "core.Census WHERE censusuniq=?", null, [$censusuniq]);
    if (!$row) {
        return false;
    }

    // $sql = 'SELECT vers FROM Revisions WHERE censusid=' . $row['id'];
    // $row = find_one($sql, $db);
    $row = NanoDB::find_one("SELECT vers  FROM " . $GLOBALS['PREFIX'] . "core.Revisions WHERE censusid=?", null, [$row['id']]);
    if (!$row) {
        return false;
    }

    $vers = $row['vers'];
    return true;
}

function CORE_GetCensusInfo(&$site, &$machine, $censusuniq, $db)
{
    $qcensusuniq = safe_addslashes($censusuniq);
    $sql = "SELECT site, host FROM " . $GLOBALS['PREFIX'] . "core.Census WHERE censusuniq='$qcensusuniq'";
    $row = find_one($sql, $db);
    if (!$row) {
        return false;
    }

    $site = $row['site'];
    $machine = $row['host'];
    return true;
}

function CORE_GetCategoryDetails(&$mcatuniq, &$precedence, $name, $db)
{
    $qName = safe_addslashes($name);
    $sql = "SELECT mcatuniq, precedence FROM " . $GLOBALS['PREFIX'] . "core.MachineCategories WHERE category='$qName'";

    $row = find_one($sql, $db);
    if (!$row) {
        return false;
    }
    $mcatuniq = $row['mcatuniq'];
    $precedence = $row['precedence'];
    return true;
}


function CORE_GetFriendlyGroupName(&$friendlyName, $mgroupuniq, $db)
{
    $qmgroupuniq = safe_addslashes($mgroupuniq);
    $sql = "SELECT MachineGroups.name, MachineCategories.category FROM MachineGroups LEFT JOIN "
        . "MachineCategories ON (MachineGroups.mcatuniq=MachineCategories.mcatuniq) "
        . "WHERE MachineGroups.mgroupuniq='$qmgroupuniq'";
    $row = find_one($sql, $db);
    if (!$row) {
        return false;
    }
    $censusuniq = '';
    if ($row['category'] == 'Machine') {
        $sql = "SELECT censusuniq FROM MachineGroupMap WHERE mgroupuniq='$mgroupuniq'";
        $row = find_one($sql, $db);
        $censusuniq = $row['censusuniq'];
    }
    return CORE_GetFriendlyCategoryName($friendlyName, $row['name'], $row['category'], $censusuniq, $db);
}


function CORE_GetFriendlyCategoryName(&$friendlyName, $groupName, $category, $censusuniq, $db)
{
    if ($category == constCategoryAll) {
        $friendlyName = 'All machines';
        return true;
    }
    if ($category == constCategoryUser) {
        $pos = strpos($groupName, constUserName);
        if ($pos === false) {
            $friendlyName = 'user "' . $groupName . '"';
        } else {
            $friendlyName = 'user "' . substr($groupName, $pos + strlen(constUserName)) . '"';
        }
        return true;
    }
    if ($category == constCategorySite) {
        $friendlyName = 'site "' . $groupName . '"';
        return true;
    }
    if ($category == constCategoryMachine) {
        if (!CORE_GetCensusInfo($site, $machine, $censusuniq, $db)) {
            return false;
        }
        $friendlyName = 'machine "' . $machine . '"';
        return true;
    }

    return true;
}


function CORE_IsOSYNClient(&$isOSYN, $censusuniq, $db)
{
    $isOSYN = false;

    if (!CORE_GetClientVersion($vers, $censusuniq, $db)) {
        return false;
    }
    $qvers = safe_addslashes($vers);
    $sql = "SELECT type FROM " . $GLOBALS['PREFIX'] . "core.DBN_Versions WHERE vers='$qvers'";
    $row = find_one($sql, $db);
    if ($row) {
        if ($row['type'] == constDBNTypeOneWay) {
            $isOSYN = true;
        }
    }
    return true;
}
