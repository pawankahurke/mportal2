<?php

define('constInfoVarValue', 'value');
define('constInfoVarName', 'name');
define('constInfoVarDart', 'dart');
define('constInfoVarPwsc', 'pwsc');
define('constInfoVarGroup', 'mgroupuniq');
define('constInfoVarSource', 'source');
define('constInfoVarMap', 'map');

define('constScopeEngine', 100000);

define('constMapStateUnknown', 0);
define('constMapStateMapped', 1);
define('constMapStateUnmapped', 2);
define('constMapStateDefmap', 3);

ignore_user_abort(true);
set_time_limit(0);

function VARS_GetVariableValuesGroup($request)
{
    $vars = array();
    foreach ($request as $key => $var) {
        if (!VARS_GetValueGroup(
            $value,
            $var['mgroupuniq'],
            $var['name'],
            $var['dart'],
            NanoDB::connect()
        )) {

            continue;
        }

        $thisVar = array(
            'name' => $var['name'],
            'dart' => $var['dart'],
            'mgroupuniq' => $var['mgroupuniq'],
            'value' => $value,
        );
        $vars[] = $thisVar;
    }
    return $vars;
}

function VARS_SetVariableValuesGroup($vars, $db)
{
    foreach ($vars as $key => $var) {
        $value = $var['value'];
        $name = $var['name'];
        $dart = $var['dart'];
        $mgroupuniq = $var['group'];
        $pwsc = constPasswordSecVarDefault;
        if (isset($var['pwsc'])) {
            $pwsc = $var['pwsc'];
        }
        $source = '0';
        if (isset($var['source'])) {
            $source = $var['source'];
        }
        $map = 0;
        if (isset($var['map'])) {
            $map = 1;
        }

        if (!VARS_SetVariableValueGroup(
            $value,
            $name,
            $dart,
            $pwsc,
            $mgroupuniq,
            time(),
            $source,
            $map,
            $db
        )) {
            return constErrBadFormat;
        }
    }

    return constAppNoErr;
}

function VARS_GetLocalValue($varName, $varScope, $db)
{
    $sql = "SELECT defval, valu FROM " . $GLOBALS['PREFIX'] . "locals.LocalValues WHERE name=? "
        . " AND scop=?";
    $row = NanoDB::find_one($sql, null, [$varName, $varScope]);
    if (!$row) {
        return null;
    }
    if (strlen($row['valu']) > 0) {
        return $row['valu'];
    }
    return $row['defval'];
}

function VARS_HandleDeletedGroup($censusuniq, $mgroupuniq, $db)
{
    if ($censusuniq != '') {
        $qcensusuniq = $censusuniq;
        $sql = "SELECT " . $GLOBALS['PREFIX'] . "core.Variables.name, " . $GLOBALS['PREFIX'] . "core.Variables.scop, ValueMap.censusuniq FROM ValueMap LEFT JOIN "
            . "" . $GLOBALS['PREFIX'] . "core.Variables ON (ValueMap.varuniq=" . $GLOBALS['PREFIX'] . "core.Variables.varuniq)
             WHERE ValueMap.mgroupuniq=?  AND censusuniq=?";
        $rows = NanoDB::find_many($sql, null, [$mgroupuniq, $qcensusuniq]);
    } else {
        $sql = "SELECT " . $GLOBALS['PREFIX'] . "core.Variables.name, " . $GLOBALS['PREFIX'] . "core.Variables.scop, ValueMap.censusuniq FROM ValueMap LEFT JOIN "
            . "" . $GLOBALS['PREFIX'] . "core.Variables ON (ValueMap.varuniq=" . $GLOBALS['PREFIX'] . "core.Variables.varuniq)
             WHERE ValueMap.mgroupuniq=?";
        $rows = NanoDB::find_many($sql, null, [$mgroupuniq]);
    }
    foreach ($rows as $key => $row) {
        VARS_HandleDeletedVarValue($mgroupuniq, $row['name'], $row['scop'], $row['censusuniq'], $db);
    }
    return true;
}

function VARS_HandleDeletedVarValue($mgroupuniq, $varName, $scripNum, $censusuniq, $db)
{
    $qName = $varName;
    $qmgroupuniq = $mgroupuniq;
    $sql = "SELECT ValueMap.censusuniq, " . $GLOBALS['PREFIX'] . "core.Variables.itype, ValueMap.varuniq, ValueMap.valmapid "
        . "FROM ValueMap LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Variables ON (ValueMap.varuniq=" . $GLOBALS['PREFIX'] . "core.Variables.varuniq) WHERE "
        . "" . $GLOBALS['PREFIX'] . "core.Variables.name=? AND " . $GLOBALS['PREFIX'] . "core.Variables.scop=? AND ValueMap.mgroupuniq=?";
    $rows = NanoDB::find_many($sql, null, [$qName, $scripNum, $qmgroupuniq]);
    if ((!$rows) || (safe_count($rows) == 0)) {

        return;
    }
    foreach ($rows as $key => $row) {

        $sql = "SELECT MachineGroups.mgroupuniq FROM MachineGroupMap LEFT JOIN MachineGroups ON ("
            . "MachineGroupMap.mgroupuniq=MachineGroups.mgroupuniq) LEFT JOIN " . $GLOBALS['PREFIX'] . "core.MachineCategories ON ("
            . "MachineGroups.mcatuniq=MachineCategories.mcatuniq) LEFT JOIN " . $GLOBALS['PREFIX'] . "core.VarValues ON ("
            . "MachineGroupMap.mgroupuniq=" . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq) WHERE MachineGroupMap."
            . "censusuniq=? AND (MachineCategories.category='"
            . constCategorySite . "' OR MachineCategories.category='" . constCategoryMachine
            . "') AND " . $GLOBALS['PREFIX'] . "core.VarValues.varuniq=? ORDER BY MachineCategories.precedence";
        $rows2 = NanoDB::find_many($sql, null, [$row['censusuniq'], $row['varuniq']]);
        if ((!$rows2) || (safe_count($rows2) == 0)) {

            $sql = "DELETE FROM ValueMap WHERE valmapid=?";
            NanoDB::query($sql, [$rows['valmapid']]);
        } else {
            VARS_SetVariableGroup(
                $changedGroup,
                $rows2['mgroupuniq'],
                $varName,
                $scripNum,
                (int) $rows['itype'],
                true,
                $rows['censusuniq'],
                '0',
                null,
                null,
                '0',
                $db
            );
        }
    }
}

function VARS_SetVariableGroup(
    &$changedGroup,
    $mgroupuniq,
    $varName,
    $scope,
    $varType,
    $canClientChange,
    $censusuniq,
    $timeStr,
    $user,
    $fullMachineStr,
    $sourceStr,
    $db
) {
    $changedGroup = false;
    if ($censusuniq == null) {
        return false;
    }
    $changedGroup = false;
    $username = '';
    if (($user != null) && (strlen($user) > 0)) {
        $username = $user;
    }

    if (!CORE_GetSingleGroupInfoPrec($groupName, $category, $precedence, $mgroupuniq, $db)) {

        return false;
    }

    if (!VARS_GetVarGroupMachine($mgroupuniqCur, $mapState, $scope, $varName, $censusuniq, $db)) {

        return false;
    }

    if (!VARS_GetVaruniq($varuniq, $varName, $scope, $db)) {

        return false;
    }

    if (!VARS_GetGroupDetails(
        $censussiteuniq,
        $mcatuniq,
        $varscopuniq,
        $varnameuniq,
        $censusuniq,
        $mgroupuniq,
        $varuniq
    )) {

        return false;
    }

    if ($mgroupuniqCur == $mgroupuniq) {

        return true;
    }

    $changedGroup = true;

    VARS_AuditVariableChange(
        $mgroupuniq,
        $scope,
        $varName,
        null,
        $username,
        $fullMachineStr,
        null,
        constVARSUserChangeLevel,
        $db
    );

    $qmgroupuniq = $mgroupuniq;
    $qvaruniq = $varuniq;
    $sql = "SELECT valueid FROM " . $GLOBALS['PREFIX'] . "core.VarValues WHERE " . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq=? "
        . " AND " . $GLOBALS['PREFIX'] . "core.VarValues.varuniq=?";
    $row = NanoDB::find_one($sql, null, [$mgroupuniq, $varuniq]);
    if (!$row) {
        if (!VARS_AddVariableToGroupDetail(
            $mgroupuniq,
            $varuniq,
            $canClientChange,
            $mcatuniq,
            $varscopuniq,
            $varnameuniq,
            $varType,
            $db
        )) {

            return false;
        }
    }

    $sql = "SELECT valmapid FROM " . $GLOBALS['PREFIX'] . "core.ValueMap WHERE censusuniq=? AND varuniq=? ";
    $row = NanoDB::find_one($sql, null, [$censusuniq, $qvaruniq]);
    if ($row) {
        $sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.ValueMap SET oldmgroupuniq=mgroupuniq, last=?, expire=0, "
            . "mgroupuniq=?, mcatuniq=?, revl=revl+1, source=?, "
            . "oldvalu='', lastchange=UNIX_TIMESTAMP() WHERE valmapid=?";
        if (!NanoDB::query($sql, [$timeStr, $qmgroupuniq, $mcatuniq, $sourceStr, $row['valmapid']])) {
            return false;
        }
    } else {
        if (!VARS_AddValueMap($censusuniq, $mgroupuniq, $varuniq, true, $scope, $varName, $db)) {
            $changedGroup = false;
        }
    }

    return $changedGroup;
}

function VARS_GetVarGroupMachine(&$mgroupuniq, &$mapState, $scope, $varName, $censusuniq, $db)
{
    $mgroupuniq = '';
    $mapState = constMapStateUnknown;
    $qName = $varName;
    $qcensusuniq = $censusuniq;
    $maxPrecGroup = '';

    $sql = "SELECT vm.mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.ValueMap AS vm, " . $GLOBALS['PREFIX'] . "core.Variables AS v WHERE "
        . " vm.varuniq=v.varuniq AND vm.censusuniq=? AND v.name=? AND v.scop=?";
    $rows = NanoDB::find_many($sql, null, [$qcensusuniq, $qName, $scope]);

    if (($rows) && (safe_count($rows) > 0)) {
        $mapState = constMapStateMapped;
        if (safe_count($rows) != 1) {
            return false;
        }
        $mgroupuniq = $rows[0]['mgroupuniq'];
        return true;
    }

    $sql = "SELECT " . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq, "
        . "precedence FROM " . $GLOBALS['PREFIX'] . "core.VarValues LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Variables ON ("
        . "" . $GLOBALS['PREFIX'] . "core.VarValues.varuniq=" . $GLOBALS['PREFIX'] . "core.Variables.varuniq) LEFT JOIN MachineGroups ON ("
        . "" . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq=MachineGroups.mgroupuniq) LEFT JOIN "
        . "MachineCategories ON (MachineGroups.mcatuniq=MachineCategories."
        . "mcatuniq) LEFT JOIN MachineGroupMap ON (" . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq="
        . "MachineGroupMap.mgroupuniq) WHERE " . $GLOBALS['PREFIX'] . "core.Variables.name=?"
        . " AND " . $GLOBALS['PREFIX'] . "core.Variables.scop=? AND censusuniq=?"
        . " ORDER BY precedence DESC";
    $rows = NanoDB::find_many($sql, null, [$qName, $scope, $qcensusuniq]);
    if ((!$rows) && (safe_count($rows) > 0)) {

        $maxPrecGroup = $rows[0]['mgroupuniq'];
        $prec = $rows[0]['precedence'];
    }

    if (!CORE_GetClientVersion($vers, $censusuniq, $db)) {
        return false;
    }

    if (!VARS_GetVarVersionIDDet($varverid, $varName, $scope, $vers, $db)) {
        return false;
    }

    $sql = "SELECT defMap FROM " . $GLOBALS['PREFIX'] . "core.VarVersions WHERE varversid=?";
    $row = NanoDB::find_one($sql, null, [$varverid]);
    if (!$row) {
        return false;
    }

    $defMap = $row['defMap'];
    $defMapGroup = '';
    $found = false;
    switch ($defMap) {
        case constDefMapSite:
            if (!CORE_GetCensusInfo($site, $machine, $censusuniq, $db)) {
                return false;
            }
            if (!CORE_GetCategoryDetails($mcatuniq, $precedence, constCategorySite, $db)) {
                return false;
            }
            $sql = "SELECT mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE mcatuniq=? AND "
                . "name=?";
            $row = NanoDB::find_one($sql, null, [$mcatuniq, $site]);
            if (!$row) {
                return false;
            }
            $found = true;
            $defMapGroup = $row['mgroupuniq'];
            break;
        case constDefMapMachine:
            if (!CORE_GetCategoryDetails($mcatuniq, $precedence, constCategoryMachine, $db)) {
                return false;
            }
            $sql = "SELECT mgroupuniq FROM MachineGroupMap WHERE censusuniq=? AND mcatuniq=?";
            $row = NanoDB::find_one($sql, null, [$qcensusuniq, $mcatuniq]);
            if (!$row) {
                return false;
            }
            $found = true;
            $defMapGroup = $row['mgroupuniq'];
            break;
        default:
            return false;
    }

    if (!$found) {
        return false;
    }

    if (strlen($maxPrecGroup) > 0) {
        if (!CORE_GetSingleGroupInfoPrec($groupName, $category, $precDef, $defMapGroup)) {
            return false;
        }
        if ($prec > $precDef) {
            $mapState = constMapStateUnmapped;
            $mgroupuniq = $maxPrecGroup;
        }
    }

    if (strlen($mgroupuniq) == 0) {
        $mapState = constMapStateDefmap;
        $mgroupuniq = $defMapGroup;
    }

    return true;
}

function VARS_GetVaruniq(&$varuniq, $varName, $scope, $db)
{
    $sql = "SELECT varuniq FROM " . $GLOBALS['PREFIX'] . "core.Variables WHERE name=? AND scop=?";
    $row = NanoDB::find_one($sql, null, [$varName, $scope]);
    if (!$row) {
        return false;
    }
    $varuniq = $row['varuniq'];
    return true;
}

function VARS_GetGroupDetails(
    &$censussiteuniq,
    &$mcatuniq,
    &$varscopuniq,
    &$varnameuniq,
    $censusuniq,
    $mgroupuniq,
    $varuniq
) {

    logs::log("VARS_GetGroupDetails", [
        &$censussiteuniq,
        &$mcatuniq,
        &$varscopuniq,
        &$varnameuniq,
        $censusuniq,
        $mgroupuniq,
        $varuniq
    ]);

    if (strlen($censusuniq) > 0) {
        $sql = "SELECT censussiteuniq FROM Census WHERE censusuniq=?";
        $row = NanoDB::find_one($sql,  null, [$censusuniq]);
        if (!$row) {
            return false;
        }
        $censussiteuniq = $row['censussiteuniq'];
    }
    if (strlen($mgroupuniq) > 0) {
        $sql = "SELECT mcatuniq FROM MachineGroups WHERE mgroupuniq=?";
        $row = NanoDB::find_one($sql, null, [$mgroupuniq]);
        if (!$row) {
            return false;
        }
        $mcatuniq = $row['mcatuniq'];
    }

    if (strlen($varuniq) > 0) {
        $sql = "SELECT varscopuniq, varnameuniq FROM " . $GLOBALS['PREFIX'] . "core.Variables WHERE varuniq=?";
        $row = NanoDB::find_one($sql, null, [$varuniq]);
        if (!$row) {
            return false;
        }
        $varscopuniq = $row['varscopuniq'];
        $varnameuniq = $row['varnameuniq'];
    }

    return true;
}

function VARS_AuditVariableChange($mgroupuniq, $scopeStr, $varName, $value, $user, $machine, $groupuniq, $detail, $db, $site = '', $changedvarname = '', $changedvarvalue = '')
{
    //$typeSelected = $_SESSION['searchType'];
    $typeSelected = (isset($_SESSION['searchType'])) ? $_SESSION['searchType'] : '';
    
    //$grpname = $_SESSION['rparentName'];
    $grpname = (isset($_SESSION['rparentName'])) ? $_SESSION['rparentName'] : '';

    $machineStr = '</b>.';
    $grpStr = '</b>.';
    if (strlen($machine) > 0) {
        $machineStr = '</b> for the group machine <b>' . $machine . '</b>.';
    } else {
        if ($typeSelected == 'Groups') {
            $grpStr = '</b> for the group group <b>' . $grpname . '</b>.';
        } else {
            $grpStr = '</b> for the group site <b>' . $site . '</b>.';
        }
    }
    if (strlen($mgroupuniq) > 0) {
        if (!CORE_GetFriendlyGroupName($friendlyName, $mgroupuniq, $db)) {
            return;
        }
        $detailStr = 'Updated the state of <b>' . $changedvarname . '</b> in Scrip <b>'
            . $scopeStr . '</b> to the value <b>' . $changedvarvalue . $grpStr . $machineStr;
    } else if ((strlen($value) > 0) && (strlen($groupuniq) > 0)) {
        if (!CORE_GetFriendlyGroupName($friendlyName, $groupuniq, $db)) {
            return;
        }
        $detailStr = 'Updated the value of <b>' . $changedvarname . '</b> in Scrip <b>'
            . $scopeStr . '</b> to the value <b>' . $changedvarvalue
            . $grpStr . $machineStr;
    } else if (strlen($value) > 0) {
        $detailStr = 'Updated the value of <b>' . $changedvarname . '</b> in Scrip <b>'
            . $scopeStr . '</b> to the value <b>' . $changedvarvalue . $machineStr;
    } else {
    }
    switch ($detail) {
        case constVARSUserChangeLevel:
            $group = constAuditGroupVARSUserChange;
            break;
        case constVARSDsynChangeLevel:
            $group = constAuditGroupVARSSyncChange;
            break;
        default:
            $group = constAuditGroupVARSSyncChange;
            break;
    }
    AUDT_LogLocalAudit(
        $detail,
        constModuleConfig,
        constClassUser,
        $group,
        $user,
        $detailStr,
        $machine,
        $site,
        $db
    );
}

function VARS_AddVariableToGroupDetail(
    $mgroupuniq,
    $varuniq,
    $canClientChange,
    $mcatuniq,
    $varscopuniq,
    $varnameuniq,
    $varType
) {
    $pDefVal = '1';
    $pValue = '';
    if ($varType == constVblTypeSemaphore) {
        $pDefVal = '0';
        $pValue = '0';
    }
    $clientChange = '0';
    if ($canClientChange) {
        $clientChange = '1';
    }

    $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.VarValues (mgroupuniq, varuniq, clientconf, mcatuniq, varscopuniq, "
        . "varnameuniq, def, valu, lastchange) VALUES (?, ?, ?, "
        . "?, ?, ?, ?, ?, UNIX_TIMESTAMP())";
    logs::log('VARS_AddVariableToGroupDetail', $sql);
    $res = NanoDB::query($sql, [$mgroupuniq, $varuniq, $clientChange, $mcatuniq, $varscopuniq, $varnameuniq, $pDefVal, $pValue]);
    if (!$res) {
        return false;
    }

    return true;
}

/**
 *  Works almost the same as VARS_AddValueMap but faster.
 */
function VARS_AddValueMap_v2(
    $loggedUser,
    $mgroupuniq,
    $varuniq,
    $changeExisting,
    $scope,
    $varName,
    $db,
    $source = ''
) {
    if ($changeExisting == 'false') {
        $changeExisting = 0;
    } else {
        $changeExisting = 1;
    }
    
    $searchType = (isset($_SESSION['searchType'])) ? $_SESSION['searchType'] : '';
    $searchVal = (isset($_SESSION['searchValue'])) ? $_SESSION['searchValue'] : '';

    $rows = [];
    if ($loggedUser != '') {
        $sql = "SELECT censusuniq, valmapid, mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.ValueMap WHERE censusuniq in(select M.censusuniq as censusuniq from " . $GLOBALS['PREFIX'] . "core.Census as C, "
            . $GLOBALS['PREFIX'] . "core.Customers as U, " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M, " . $GLOBALS['PREFIX'] . "core.MachineGroups as G "
            . "where M.censusuniq = C.censusuniq and M.mgroupuniq = G.mgroupuniq and "
            . "G.mgroupid IN (SELECT mgroupid FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE mgroupuniq=? )
             and U.customer = C.site
             and U.username = ?) AND varuniq=? ;";
        $rows = NanoDB::find_many($sql, null, [$mgroupuniq, $loggedUser, $varuniq]);
    } else {
        $sql = "SELECT gm.censusuniq as censusuniq, vm.valmapid,  vm.mgroupuniq
        FROM " . $GLOBALS['PREFIX'] . "core.MachineGroupMap gm left join  " . $GLOBALS['PREFIX'] . "core.ValueMap vm on vm.censusuniq = gm.censusuniq
        WHERE gm.mgroupuniq= ? and vm.varuniq = ?     ";
        $rows = NanoDB::find_many($sql, null, [$mgroupuniq, $varuniq]);
    }

    foreach ($rows as $key => $rowG) {
        $censusUniq = $rowG['censusuniq'];

        if ($changeExisting) {
            // $sql = "SELECT valmapid, mgroupuniq FROM core.ValueMap WHERE censusuniq=? AND varuniq=?";
            // $row = NanoDB::find_one($sql, null, [$censusUniq, $varuniq]);
            $row = [
                "valmapid" => $rowG['valmapid'], // core.ValueMap.valmapid
                "mgroupuniq" => $rowG['mgroupuniq'] // core.ValueMap.mgroupuniq
            ];
            if ($row['mgroupuniq'] && $row['valmapid']) {
                if ($mgroupuniq != $row['mgroupuniq']) {

                    if (!VARS_GetGroupDetails(
                        $censussiteuniq,
                        $mcatuniq,
                        $varscopuniq,
                        $varnameuniq,
                        $censusUniq,
                        $mgroupuniq,
                        $varuniq
                    )) {
                        return false;
                    }

                    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.ValueMap SET oldmgroupuniq='', last=0, expire=0, "
                        . "mgroupuniq=?, mcatuniq=?, revl=revl+1, source=?, oldvalu='', lastchange=UNIX_TIMESTAMP() "
                        . " WHERE valmapid=?";

                    NanoDB::query($sql, [$mgroupuniq, $mcatuniq, $source, $row['valmapid']]);
                }
            } else if (!$row) {

                if (!CORE_IsOSYNClient($isOSYN, $censusUniq, $db)) {
                }
                $res = 0;
                if (!$isOSYN) {
                    if (!VARS_GetVarGroupMachine($oldmgroupuniq, $mapState, $scope, $varName, $censusUniq, $db)) {

                        $res = 0;
                    } else {
                        if ($oldmgroupuniq != $mgroupuniq) {
                            $res = 1;
                        }
                    }
                }

                if (($res != 0) || ($isOSYN)) {
                    if (!VARS_GetGroupDetails(
                        $censussiteuniq,
                        $mcatuniq,
                        $varscopuniq,
                        $varnameuniq,
                        $censusUniq,
                        $mgroupuniq,
                        $varuniq
                    )) {
                        return false;
                    }

                    $sql = "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.ValueMap (censusuniq, mgroupuniq, varuniq, "
                        . "censussiteuniq, mcatuniq, varscopuniq, varnameuniq, lastchange) "
                        . "VALUES (?,?,?, ?,?, ?, ?, UNIX_TIMESTAMP())";
                    // $res = command($sql, $db);
                    $res = NanoDB::query($sql, [$censusUniq, $mgroupuniq, $varuniq, $censussiteuniq, $mcatuniq, $varscopuniq, $varnameuniq]);
                    if (!$res) {
                        return false;
                    }
                }
            }
        } else {
            if ($searchType == 'Sites') {
                $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ?";
                $row = NanoDB::find_one($sql, null, [$searchVal]);

                $mcatuniq = $row['mcatuniq'];
                $mgroupuniq = $row['mgroupuniq'];

                $check = "Select * from " . $GLOBALS['PREFIX'] . "core.ValueMap WHERE censusuniq=? AND varuniq=?";
                $result = NanoDB::find_one($check, null, [$censusUniq, $varuniq]);

                if ($result) {
                    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.ValueMap SET mgroupuniq=?, mcatuniq=?, lastchange=UNIX_TIMESTAMP() 
                     WHERE censusuniq=? AND varuniq=? ";
                    $row = NanoDB::query($sql, [$mgroupuniq, $mcatuniq, $censusUniq, $varuniq]);
                } else {
                    $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.ValueMap (censusuniq, mgroupuniq, varuniq, "
                        . "censussiteuniq, mcatuniq, varscopuniq, varnameuniq, lastchange) "
                        . "VALUES (?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())";
                    // $row = command($sql, $db);
                    $res = NanoDB::query($sql, [$censusUniq, $mgroupuniq, $varuniq, $censussiteuniq, $mcatuniq, $varscopuniq, $varnameuniq]);
                }

                if (!$row) {
                    $msg = "This functionality is available only on Site Level";
                    return $msg;
                } else {
                    continue;
                }
            }
        }
    }

    return true;
}

/**
 * let use VARS_AddValueMap_v2
 * @deprecated
 */
function VARS_AddValueMap($censusUniq, $mgroupuniq, $varuniq, $changeExisting, $scope, $varName, $db, $source = '')
{
    $searchType = $_SESSION['searchType'];
    $searchVal = $_SESSION['searchValue'];
    if ($changeExisting == 'false') {
        $changeExisting = 0;
    } else {
        $changeExisting = 1;
    }

    if ($changeExisting) {
        $sql = "SELECT valmapid, mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.ValueMap WHERE censusuniq=? AND varuniq=?";
        $row = NanoDB::find_one($sql, null, [$censusUniq, $varuniq]);
        if ($row) {
            if ($mgroupuniq != $row['mgroupuniq']) {

                if (!VARS_GetGroupDetails(
                    $censussiteuniq,
                    $mcatuniq,
                    $varscopuniq,
                    $varnameuniq,
                    $censusUniq,
                    $mgroupuniq,
                    $varuniq
                )) {
                    return false;
                }

                $sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.ValueMap SET oldmgroupuniq='', last=0, expire=0, "
                    . "mgroupuniq=?, mcatuniq=?, revl=revl+1, source=?, oldvalu='', lastchange=UNIX_TIMESTAMP() "
                    . " WHERE valmapid=?";

                NanoDB::query($sql, [$mgroupuniq, $mcatuniq, $source, $row['valmapid']]);
            }
        } else if (!$row) {

            if (!CORE_IsOSYNClient($isOSYN, $censusUniq, $db)) {
            }
            $res = 0;
            if (!$isOSYN) {
                if (!VARS_GetVarGroupMachine($oldmgroupuniq, $mapState, $scope, $varName, $censusUniq, $db)) {

                    $res = 0;
                } else {
                    if ($oldmgroupuniq != $mgroupuniq) {
                        $res = 1;
                    }
                }
            }

            if (($res != 0) || ($isOSYN)) {
                if (!VARS_GetGroupDetails(
                    $censussiteuniq,
                    $mcatuniq,
                    $varscopuniq,
                    $varnameuniq,
                    $censusUniq,
                    $mgroupuniq,
                    $varuniq
                )) {
                    return false;
                }

                $sql = "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.ValueMap (censusuniq, mgroupuniq, varuniq, "
                    . "censussiteuniq, mcatuniq, varscopuniq, varnameuniq, lastchange) "
                    . "VALUES (?,?,?, ?,?, ?, ?, UNIX_TIMESTAMP())";
                // $res = command($sql, $db);
                $res = NanoDB::query($sql, [$censusUniq, $mgroupuniq, $varuniq, $censussiteuniq, $mcatuniq, $varscopuniq, $varnameuniq]);
                if (!$res) {
                    return false;
                }
            }
        }
    } else {
        if ($searchType == 'Sites') {
            $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE name = ?";
            $row = NanoDB::find_one($sql, null, [$searchVal]);

            $mcatuniq = $row['mcatuniq'];
            $mgroupuniq = $row['mgroupuniq'];

            $check = "Select * from " . $GLOBALS['PREFIX'] . "core.ValueMap WHERE censusuniq=? AND varuniq=?";
            $result = NanoDB::find_one($check, null, [$censusUniq, $varuniq]);

            if ($result) {
                $sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.ValueMap SET mgroupuniq=?, mcatuniq=?, lastchange=UNIX_TIMESTAMP() 
                 WHERE censusuniq=? AND varuniq=? ";
                $row = NanoDB::query($sql, [$mgroupuniq, $mcatuniq, $censusUniq, $varuniq]);
            } else {
                $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "core.ValueMap (censusuniq, mgroupuniq, varuniq, "
                    . "censussiteuniq, mcatuniq, varscopuniq, varnameuniq, lastchange) "
                    . "VALUES (?, ?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP())";
                // $row = command($sql, $db);
                $res = NanoDB::query($sql, [$censusUniq, $mgroupuniq, $varuniq, $censussiteuniq, $mcatuniq, $varscopuniq, $varnameuniq]);
            }

            if (!$row) {
                $msg = "This functionality is available only on Site Level";
                return $msg;
            } else {
                return true;
            }
        }
    }
    return true;
}

function VARS_GetVarVersionIDDet(&$varverID, $varName, $scope, $vers, $db)
{
    $sql = "SELECT varversid FROM " . $GLOBALS['PREFIX'] . "core.VarVersions LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Variables ON ("
        . "" . $GLOBALS['PREFIX'] . "core.VarVersions.varuniq=" . $GLOBALS['PREFIX'] . "core.Variables.varuniq) WHERE " . $GLOBALS['PREFIX'] . "core.Variables.name=? "
        . " AND " . $GLOBALS['PREFIX'] . "core.Variables.scop=? AND " . $GLOBALS['PREFIX'] . "core.VarVersions.vers=?";
    $row = NanoDB::find_one($sql, null, [$varName, $scope, $vers]);
    if (!$row) {
        return false;
    }
    $varverID = $row['varversid'];
    return true;
}

function VARS_GetValueGroup(&$value, $mgroupuniq, $varName, $scrip, $db)
{
    $sql = "SELECT valu, def, defval, defmap FROM " . $GLOBALS['PREFIX'] . "core.VarVersions LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Variables ON "
        . "(" . $GLOBALS['PREFIX'] . "core.VarVersions.varuniq=" . $GLOBALS['PREFIX'] . "core.Variables.varuniq) LEFT JOIN " . $GLOBALS['PREFIX'] . "core.VarValues ON "
        . "(" . $GLOBALS['PREFIX'] . "core.VarVersions.varuniq=" . $GLOBALS['PREFIX'] . "core.VarValues.varuniq AND " . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq=?) "
        . "WHERE " . $GLOBALS['PREFIX'] . "core.Variables.name=? AND " . $GLOBALS['PREFIX'] . "core.Variables.scop=$scrip ORDER BY " . $GLOBALS['PREFIX'] . "core.VarVersions.vers DESC LIMIT 1";

    $row = NanoDB::find_one($sql, null, [$mgroupuniq, $varName]);

    if (!$row) {
        logs::log('VARS_GetValueGroup: false [1]');
        return false;
    }

    if (!is_null($row['def'])) {
        $defInt = (int) $row['def'];
        if ($defInt == 1) {
            if ($row['defval'] != null) {
                $value = $row['defval'];
            }
        } else if ($row['valu'] != null) {
            $value = $row['valu'];
        }
    } else {
        if (!VARS_GetGroupDetails(
            $censussiteuniq,
            $mcatuniq,
            $varscopuniq,
            $varnameuniq,
            '',
            $mgroupuniq,
            ''
        )) {

            logs::log('VARS_GetValueGroup: false [2]');
            return false;
        }

        switch ((int) $row['defmap']) {
            case constDefMapSite:
                if (!CORE_GetCategoryDetails($tcatuniq, $precedence, constCategorySite, $db)) {
                    logs::log('VARS_GetValueGroup: false [3]');
                    return false;
                }
                break;
            case constDefMapMachine:
                if (!CORE_GetCategoryDetails($tcatuniq, $precedence, constCategoryMachine, $db)) {
                    logs::log('VARS_GetValueGroup: false [4]');
                    return false;
                }
                break;
            default:
                logs::log('VARS_GetValueGroup: false [5]');
                return false;
        }

        if ($mcatuniq != $tcatuniq) {

            logs::log('VARS_GetValueGroup: false [6]');
            return false;
        }

        $value = $row['defval'];
    }

    return true;
}

function VARS_SetVariableValueGroup(
    $valu,
    $varName,
    $scope,
    $pwsc,
    $mgroupuniq,
    $last,
    $sourceStr,
    $map,
    $db,
    $loggedUser = '',
    $changedvarname = '',
    $changedvarvalue = '',
    $precedence = ''
) {
    switch ($pwsc) {
        case constPasswordSecCleartext:
        case constPasswordSecVarDefault:
            $qValue = $valu;
            break;
        case constPasswordSecHashed:
            $qValue = md5($valu);
            break;
        default:
            logs::log('VARS_SetVariableValueGroup', ["pwsc" => $pwsc]);
            return false;
    }
    $qName = $varName;
    $sql = "SELECT valueid, " . $GLOBALS['PREFIX'] . "core.Variables.varuniq FROM " . $GLOBALS['PREFIX'] . "core.Variables LEFT JOIN " . $GLOBALS['PREFIX'] . "core.VarValues ON ("
        . "" . $GLOBALS['PREFIX'] . "core.Variables.varuniq=" . $GLOBALS['PREFIX'] . "core.VarValues.varuniq) WHERE " . $GLOBALS['PREFIX'] . "core.Variables.name=? AND "
        . "" . $GLOBALS['PREFIX'] . "core.Variables.scop=? AND mgroupuniq=?";

    $row = NanoDB::find_one($sql, null, [$qName, $scope, $mgroupuniq]);
    if (!$row) {
        if (!VARS_AddVariableToGroup($mgroupuniq, $varName, $scope, $db)) {
            $error = "Check Whether Core DBN is uploaded properly (ErrorCode=1)";
            logs::log('VARS_SetVariableValueGroup', $error);
            return $error;
        }

        $row = NanoDB::find_one($sql, null, [$qName, $scope, $mgroupuniq]);
        if (!$row) {
            $error = "Check Whether Core DBN is uploaded properly (ErrorCode=2)";
            logs::log('VARS_SetVariableValueGroup', $error);
            return $error;
        }
    }

    $valueid = $row['valueid'];
    if (!VARS_GetValueGroup($oldValu, $mgroupuniq, $varName, $scope, $db)) {
        $error = "Check Whether Core DBN is uploaded properly (ErrorCode=3)";
        logs::log('VARS_SetVariableValueGroup', $error);
        return $error;
    }

    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.VarValues SET valu=?, revldef=revldef+def, def=0, lastchange=UNIX_TIMESTAMP(), revl=revl+1 "
        . "WHERE valu!=? AND valueid=?";
    if (!NanoDB::query($sql, [$qValue, $qValue, $valueid])) {
        $error = "SQL Failure - Unable to update Values";
        logs::log('VARS_SetVariableValueGroup', ["error" => $error]);
        return $error;
    }

    if (!VARS_UpdateHistory($oldValu, $mgroupuniq, $row['varuniq'], $last, $sourceStr, $db)) {
        logs::log('VARS_SetVariableValueGroup', ["error" => "!VARS_UpdateHistory"]);
        return false;
    }

    if ($map) {
        $varuniq = $row['varuniq'];

        if (!VARS_AddValueMap_v2($loggedUser, $mgroupuniq, $varuniq, $precedence, $scope, $varName, $db, $sourceStr)) {
            $error = "Could Not Add Map";
            logs::log('VARS_SetVariableValueGroup', ["error" => $error]);
            return $error;
        }

        // if ($loggedUser != '') {
        //     $mgidSql = "SELECT mgroupid, name FROM core.MachineGroups WHERE mgroupuniq=? limit 1";
        //     $mgidRes = NanoDB::find_one($mgidSql,  null, [$mgroupuniq]);
        //     $mgroupid = $mgidRes['mgroupid'];

        //     $sql = "select distinct C.site,C.host,C.id, M.censusuniq from " . $GLOBALS['PREFIX'] . "core.Census as C, "
        //         . $GLOBALS['PREFIX'] . "core.Customers as U, " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M, " . $GLOBALS['PREFIX'] . "core.MachineGroups as G "
        //         . "where M.censusuniq = C.censusuniq and M.mgroupuniq = G.mgroupuniq and "
        //         . "G.mgroupid IN (?) and U.customer = C.site and U.username = ?;";
        //     $rows = NanoDB::find_many($sql, null, [$mgroupid, $loggedUser]);

        //     foreach ($rows as $key => $row) {
        //         if (!VARS_AddValueMap($row['censusuniq'], $mgroupuniq, $varuniq, $precedence, $scope, $varName, $db, $sourceStr)) {
        //             $error = "Could Not Add Map";
        //             logs::log('VARS_SetVariableValueGroup', ["error" => $error]);
        //             return $error;
        //         }
        //     }
        // } else {


        // Old V1 code. It should work the same but slower.
        // $sql = "SELECT censusuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineGroupMap WHERE mgroupuniq=?";
        // $rows = NanoDB::find_many($sql, null, [$mgroupuniq]); 
        // foreach ($rows as $key => $row) {
        //     if (!VARS_AddValueMap($row['censusuniq'], $mgroupuniq, $varuniq, $precedence, $scope, $varName, $db, $sourceStr)) {
        //         $error = "Could Not Add Map";
        //         logs::log('VARS_SetVariableValueGroup', ["error" => $error]);
        //         return $error;
        //     }
        // }
        // }
    }

    $sqlcheck = "SELECT " . $GLOBALS['PREFIX'] . "core.MachineGroups.name, " . $GLOBALS['PREFIX'] . "core.MachineCategories.category FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups LEFT JOIN " . $GLOBALS['PREFIX'] . "core.MachineCategories
                ON (" . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq=" . $GLOBALS['PREFIX'] . "core.MachineCategories.mcatuniq) WHERE " . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq=?";
    $resultcheck = NanoDB::find_one($sqlcheck, null, [$mgroupuniq]);
    $category = $resultcheck['category'];
    if ($category == 'Machine') {
        $nameVal = $resultcheck['name'];
        $nameData = explode(':', $nameVal);
        $site = $nameData[0];
        $machine = $nameData[1];
    } else {
        $machine = '';
        $site = $resultcheck['name'];
    }
    VARS_AuditVariableChange(
        $mgroupuniq,
        $scope,
        $varName,
        null,
        $loggedUser,
        $machine,
        null,
        constVARSUserChangeLevel,
        $db,
        $site,
        $changedvarname,
        $changedvarvalue
    );
    logs::log('VARS_SetVariableValueGroup:true');
    return true;
}

function VARS_AddVariableToGroup($mgroupuniq, $varName, $scripNum, $db)
{
    if (!VARS_GetGroupDetails(
        $censussiteuniq,
        $mcatuniq,
        $varscopuniq,
        $varnameuniq,
        '',
        $mgroupuniq,
        ''
    )) {
        logs::log('VARS_AddVariableToGroup: false [1] (VARS_GetGroupDetails is wrong)');
        return false;
    }

    $sql = "SELECT varuniq, varscopuniq, varnameuniq, itype FROM " . $GLOBALS['PREFIX'] . "core.Variables WHERE "
        . "name=? AND scop=?";
    $row = NanoDB::find_one($sql, null, [$varName, $scripNum]);
    if (!$row) {
        logs::log('VARS_AddVariableToGroup: false [2]');
        return false;
    }

    $varType = $row['itype'];
    // ?
    if (!CORE_GetSingleGroupInfoPrec($groupName, $category, $precedence, $mgroupuniq)) {
        logs::log('VARS_AddVariableToGroup: false [3]');
        return false;
    }

    $canClientChange = true;
    if ($category == constCategoryAll) {
        $canClientChange = false;
    }
    if ($category == constCategoryUser) {
        $canClientChange = false;
    }
    if (!VARS_AddVariableToGroupDetail(
        $mgroupuniq,
        $row['varuniq'],
        $canClientChange,
        $mcatuniq,
        $row['varscopuniq'],
        $row['varnameuniq'],
        $varType,
        $db
    )) {
        logs::log('VARS_AddVariableToGroup: false [4]');
        return false;
    }

    logs::log('VARS_AddVariableToGroup: true');
    return true;
}

function VARS_UpdateHistory($oldValu, $mgroupuniq, $varuniq, $last, $sourceStr, $db)
{
    return true;

    // old code :
    // $sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.ValueMap SET oldvalu='', oldmgroupuniq='', expire=0, last=0 "
    //     . "WHERE mgroupuniq=? AND varuniq=? AND last!=? ";

    // if (!NanoDB::query($sql, [$mgroupuniq, $varuniq, $last])) {
    //     logs::log('VARS_UpdateHistory:err for',  $sql);
    //     return false;
    // }

    // old code :
    // $sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.ValueMap SET oldvalu=?, last=?, source=? "
    //     . "WHERE mgroupuniq=? AND varuniq=? AND oldmgroupuniq=''";
    // if (!NanoDB::query($sql, [$oldValu, $last, $sourceStr, $mgroupuniq, $varuniq])) {
    //     logs::log('VARS_UpdateHistory:err for',  $sql);
    //     return false;
    // }

    /*
    $sql = "SELECT count(*) as total FROM " . $GLOBALS['PREFIX'] . "core.ValueMap   "
        . "WHERE mgroupuniq=? AND varuniq=? AND last!=? ";
    $row = NanoDB::find_one($sql, null, [$mgroupuniq, $varuniq, $last]);
    if (!$row) {
        return false;
    }

    logs::log("VARS_UpdateHistory: step=no to work " . $row['total'],  $sql);

    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "core.ValueMap SET oldmgroupuniq='', expire=0, oldvalu=?, last=?, source=? "
        . "WHERE mgroupuniq=? AND varuniq=? AND last!=? ";
    if (!NanoDB::query($sql, [$oldValu, $last, $sourceStr, $mgroupuniq, $varuniq, $last])) {
        logs::log('VARS_UpdateHistory:err for',  $sql);
        return false;
    }
*/
    return true;
}
