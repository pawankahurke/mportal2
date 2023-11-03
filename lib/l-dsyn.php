<?php






define('constAggregateSetGConfig', 1);

define('constAggregateSetRegistryManagment', 4);
define('constAggregateDSYNSetServiceTables', 7);
define('constAggregateDSYNSetSrvcMgmtTables', 10);


define('constDataSetCoreCensus',            2);
define('constDataSetCoreMachineCategories', 3);
define('constDataSetCoreMachineGroups',     4);
define('constDataSetCoreMachineGroupMap',   5);
define('constDataSetGConfigVariables',      6);
define('constDataSetGConfigVarValues',      7);
define('constDataSetGConfigValueMap',       8);
define('constDataSetGConfigSemClears',      9);
define('constDataSetGConfigScrips',         10);
define('constDataSetGConfigVarVersions',    11);
define('constDataSetGConfigGroupSettings',  12);





define('constDataSetRegManFilters',         20);
define('constDataSetRegManBaseKeys',        21);
define('constDataSetRegManMacActions',      22);
define('constDataSetRegManActions',         23);
define('constDataSetCoreUsers',             24);


define('constOperationInsert',              0);
define('constOperationDelete',              1);
define('constOperationPermanentDelete',     2);


function DSYN_UpdateDependencies($dataSetID, $rowID, $db)
{
    $error = 0;
    switch ($dataSetID) {
        case constDataSetCoreCensus:

            $keyName = "id";
            break;

        case constDataSetCoreMachineCategories:

            $keyName = "mcatid";
            break;

        case constDataSetCoreMachineGroups:

            $keyName = "mgroupid";
            break;

        case constDataSetCoreMachineGroupMap:

            $keyName = "mgmapid";
            break;

        case constDataSetGConfigVariables:

            $keyName = "varid";
            break;

        case constDataSetGConfigVarValues:

            $keyName = "valueid";
            break;

        case constDataSetGConfigValueMap:

            $keyName = "valmapid";
            break;

        case constDataSetGConfigSemClears:

            $keyName = "semid";
            break;

        case constDataSetGConfigScrips:

            $keyName = "scripid";
            break;

        case constDataSetGConfigVarVersions:

            $keyName = "varversid";
            break;

        case constDataSetGConfigGroupSettings:

            $keyName = "setid";
            break;

        case constDataSetCoreUsers:

            $keyName = "userid";
            break;

        default:
            logs::log(__FILE__, __LINE__, "DSYN_UpdateDependencies, bad switch exit, "
                . $dataSetID, 0);
            $error = 1;
            break;
    }

    if (!($error)) {
        $err = 2;
        if ($err != constAppNoErr) {
            logs::log(__FILE__, __LINE__, "DSYN_UpdateDependencies: "
                . "PHP_DSYN_InvalidateRow20 returned " . $err, 0);
        }
    }
}


function DSYN_InsertEntries(
    $dataSetID,
    $keyName,
    $tableName,
    $parentKeyName,
    $rowID,
    $numID,
    $db
) {
    $set = 0;

    $sql = "select " . $keyName . " from " . $tableName . " where "
        . $parentKeyName . "=" . $rowID;


    if (!(isset($GLOBALS['test_sql']))) {
        $set = DSYN_FindMany($sql, $db);
        if ($set) {
            foreach ($set as $key => $row) {
                $err = PHP_DSYN_InvalidateRow(
                    CUR,
                    (int)$row[$keyName],
                    $keyName,
                    $dataSetID,
                    constOperationInsert
                );
                if ($err != constAppNoErr) {
                    logs::log(__FILE__, __LINE__, "DSYN_UpdateDependencies: "
                        . " PHP_DSYN_InvalidateRow "
                        . $numID . " returned " . $err, 0);
                }
            }
        }
    }
    return $set;
}


function DSYN_DeleteSet(
    $sql,
    $dataSetID,
    $keyName,
    $funcName,
    $idCode,
    $runTest,
    $opCode,
    $db
) {
    if ($runTest) {
        if (function_exists('global_def')) {
            $test = global_def('test_sql', 0);
        } else {
            $test = 0;
        }
    } else {
        $test = 0;
    }
    if (!($test)) {
        $set = DSYN_FindMany($sql, $db);

        if ($set) {
            foreach ($set as $key => $row) {
                $err = 2;
                if ($err != constAppNoErr) {
                    logs::log(__FILE__, __LINE__, $funcName . ": PHP_DSYN_InvalidateRow"
                        . $idCode . " returned " . $err, 0);
                    return array();
                }
            }
        }
    }
    return $set;
}


function DSYN_UpdateSet($set, $dataSetID, $keyName, $db)
{
    if ($set) {
        foreach ($set as $key => $row) {
            DSYN_UpdateDependencies($dataSetID, $row[$keyName], $db);
        }
    }
}

function DSYN_FindMany($sql, $db)
{
    $set = array();
    $res = mysql_query($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $set[] = $row;
        }
        mysqli_free_result($res);
    } else {

        $txt = mysql_error($db);
        $num = mysql_errno($db);
        $msg = "mysql: ($num) $txt";
        $cmd = "mysql: $sql";
        logs::log(__FILE__, __LINE__, $cmd, 0);
        logs::log(__FILE__, __LINE__, $msg, 0);
    }
    return $set;
}
