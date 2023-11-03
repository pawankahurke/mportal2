<?php






define('constAuditSourceUndefined', 0);
define('constAuditSourceClient',    1);
define('constAuditSourceServer',    2);


define('constAuditLevelVarName',    'AUDTAuditLevel');
define('constAuditMaxLogsVarName',  'AUDTMaximumAudits');


define('constVARSUserChangeLevel',  4);
define('constVARSDsynChangeLevel',  5);
define('constCOREDBNLevel',         9);


define('constAuditGroupVARSUserChange',     3);
define('constAuditGroupVARSSyncChange',     4);
define('constAuditGroupCOREDBNPrebuild',    14);


function AUDT_LogLocalAudit(
    $detailLevel,
    $module,
    $classID,
    $grpAudit,
    $userName,
    $detailStr,
    $machine,
    $site,
    $db
) {
    $hostname = gethostname();
    if (!$hostname) {
        $hostname = '';
    }
    AUDT_LogMachineAudit(
        $detailLevel,
        $module,
        $classID,
        $grpAudit,
        $userName,
        $detailStr,
        $site,
        $machine,
        '',
        $db
    );
}


function AUDT_LogMachineAudit(
    $detailLevel,
    $module,
    $classID,
    $grpAudit,
    $userName,
    $detailStr,
    $site,
    $machine,
    $uuid,
    $db
) {
    $timestamp = time();
    $source = constAuditSourceServer;
    $newDetailStr = str_replace('\'', '', $detailStr);
    $version = '#VERSION#';

    AUDT_Audit(
        $detailLevel,
        $module,
        $classID,
        $grpAudit,
        $userName,
        $newDetailStr,
        $site,
        $machine,
        $uuid,
        $timestamp,
        $source,
        $version,
        $db
    );
}


function AUDT_Audit(
    $detailLevel,
    $module,
    $classID,
    $grpAudit,
    $userName,
    $detailStr,
    $site,
    $hostname,
    $uuid,
    $timestamp,
    $source,
    $version,
    $db
) {

    $finalDetail = $detailStr;

    $qsite = $site;
    $qhost = $hostname;
    $quuid = $uuid;
    $quser = $userName;
    if (!$quser) {
        $quser  = '';
    }

    $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "audit.Audit (source, time, type, module, class, grpaudit, "
        . "site, machine, uuid, version, user, detail) VALUES ("
        . "?, ?, ?, ?, ?, ?,?, ?,?,?, ?,?)";
    NanoDB::query($sql, [
        $source, $timestamp, $detailLevel, $module, $classID, $grpAudit, $qsite,
        $qhost, $quuid, $version, $quser, $finalDetail
    ]);
}


function AUDT_GetNumberOfEntries($db)
{
    $sql = 'SELECT COUNT(*) AS cnt FROM ' . $GLOBALS['PREFIX'] . 'audit.Audit';
    $row = NanoDB::find_one($sql);
    if (!$row) {
        return -1;
    }

    return intval($row['cnt']);
}


function AUDT_DeleteOldestEntries($num, $db)
{
    $sql = "DELETE FROM " . $GLOBALS['PREFIX'] . "audit.Audit ORDER BY auditid LIMIT $num";
    $res = command($sql, $db);
    if (!$res) {
    }
}
