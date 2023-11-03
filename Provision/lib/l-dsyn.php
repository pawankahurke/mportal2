<?php

/*
Revision history:

Date        Who     What
----        ---     ----
12-Sep-05   BTE     Original creation.
14-Sep-05   BTE     DSYN_UpdateDependencies needs to update the row itself.
20-Sep-05   BTE     Fixed table specifications.
11-Oct-05   BTE     Fixed undefined global_def if l-head.php is not included.
12-Oct-05   BTE     Changed references from gconfig to core.
05-Nov-05   BTE     Expanded DSYN_DeleteSet to handle permanent deletions.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
19-Jul-06   BTE     Bug 3239: 913 errors on CI server.
31-Jul-06   BTE     Bug 3560: Sites: Configuration - Local change on 2.1 client
                    doesn't reflect on server.
15-Nov-06   JRN     Added constAggregateSetRegistryManagment set definitions.
12-Dec-06   BTE     Bug 3948: Tools: Admin: Edit user fails to update sites.
11-Jan-08   WOH     Added constAggregateDSYNSetServiceTables.
21-Feb-08   WOH     Added constAggregateDSYNSetSrvcMgmtTables.
11-Aug-13   BTE     Added repair constants.

*/

/* This file contains useful functions and definitions for handling 
        database synchronization.  */

/* copied from iface/dsyn.h - aggregate set codes */
define('constAggregateSetGConfig', 1);
/* Retired    define('constAggregateSetRegistryManagment', 2); */
define('constAggregateSetRegistryManagment', 4);
define('constAggregateDSYNSetServiceTables', 7);
define('constAggregateDSYNSetSrvcMgmtTables', 10);

/* copied from iface/dsyn.h - data set codes */
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
/* Retired define('constDataSetRegManFilters',         13); */
/* Retired define('constDataSetRegManBaseKeys',        14); */
/* Retired define('constDataSetRegManMacActions',      15); */
/* Retired define('constDataSetRegManActions',         16); */
/* Retired define('constDataSetCoreUsers',             17); */
define('constDataSetRegManFilters',         20);
define('constDataSetRegManBaseKeys',        21);
define('constDataSetRegManMacActions',      22);
define('constDataSetRegManActions',         23);
define('constDataSetCoreUsers',             24);

/* copied from iface/dsyn.h - DSYN_InvalidateRow codes */
define('constOperationInsert',              0);
define('constOperationDelete',              1);
define('constOperationPermanentDelete',     2);

/* copied from iface/dsyn.h - repair modes */
define('constDSYNRepairCheckOnly',        0);
define('constDSYNRepairAll',        1);

/* DSYN_UpdateDependencies

        Updates the dependencies for the data set $dataSetID using the data
        table row identifier $rowID and the connection to the database $db.
        Normal usage for this function is as follows:
        PHP_DSYN_InvalidateRow  $rowID, constOperationDelete
        UPDATE dataTable SET VALUE=X WHERE key=$rowID
        DSYN_UpdateDependencies($dataSetID, $rowID)

        PHP_DSYN_InvalidateRow deletes from the checksum cache tables anything
        referring to $rowID, so we have to add them back after the UPDATE
        operation completes.

        Note: it is not necessary to do checksum invalidation if the column
        that is changing in the primary table is not included in the checksum
        computation.  See the data set identifier definitions on the
        engineering website to determine if this is the case.

        After all dependencies are updated the data table's checksum cache
        table is updated.
    */
function DSYN_UpdateDependencies($dataSetID, $rowID, $db)
{
    $error = 0;
    switch ($dataSetID) {
        case constDataSetCoreCensus:
            /* Dependent tables: none */
            $keyName = "id";
            break;

        case constDataSetCoreMachineCategories:
            /* Dependent tables: none */
            $keyName = "mcatid";
            break;

        case constDataSetCoreMachineGroups:
            /* Dependent tables: none */
            $keyName = "mgroupid";
            break;

        case constDataSetCoreMachineGroupMap:
            /* Dependent tables: none */
            $keyName = "mgmapid";
            break;

        case constDataSetGConfigVariables:
            /* Dependent tables: none */
            $keyName = "varid";
            break;

        case constDataSetGConfigVarValues:
            /* Dependent tables: none */
            $keyName = "valueid";
            break;

        case constDataSetGConfigValueMap:
            /* Dependent tables: none */
            $keyName = "valmapid";
            break;

        case constDataSetGConfigSemClears:
            /* Dependent tables: none */
            $keyName = "semid";
            break;

        case constDataSetGConfigScrips:
            /* Dependent tables: none */
            $keyName = "scripid";
            break;

        case constDataSetGConfigVarVersions:
            /* Dependent tables: none */
            $keyName = "varversid";
            break;

        case constDataSetGConfigGroupSettings:
            /* Dependent tables: none */
            $keyName = "setid";
            break;

        case constDataSetCoreUsers:
            /* dependent tables: none */
            $keyName = "userid";
            break;

        default:
            logs::log(__FILE__, __LINE__, "DSYN_UpdateDependencies, bad switch exit, "
                . $dataSetID, 0);
            $error = 1;
            break;
    }

    if (!($error)) {
        $err = PHP_DSYN_InvalidateRow(
            CUR,
            (int)$rowID,
            $keyName,
            $dataSetID,
            constOperationInsert
        );
        if ($err != constAppNoErr) {
            logs::log(__FILE__, __LINE__, "DSYN_UpdateDependencies: "
                . "PHP_DSYN_InvalidateRow20 returned " . $err, 0);
        }
    }
}

/* DSYN_InsertEntries

        Inserts checksum cache table entries for the table $tableName for all
        rows identified by $parentKeyName=$rowID using the unique primary key
        $keyName for the data table defined in $dataSetID.  $numID should be an
        unique identifying number to aid in error reporting, and $db should be
        a connection to the database.

        This function returns an array of unique primary key identifiers from
        $tableName.$keyName.
    */
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

    /* Determine if this is a test or not. */
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

/* DSYN_DeleteSet

        Deletes the set returned by $sql in the checksum cache table for
        $dataSetID.  $keyName must be the primary key column name for the
        data table referred to in $sql.  $funcName and $idCode help to uniquely
        identify the function and position where DSYN_DeleteSet was called from
        for error information.  If $runTest is true, then 'test_sql' is checked
        before any modifications to $db begin.

        This function returns the result set for $sql (if we are not running in
        test mode).

        $opCode can be one of the following:
            constOperationInsert                use when adding/after updating
            constOperationDelete                use just before an update
            constOperationPermanentDelete       use just before a delete
    */
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
                $err = PHP_DSYN_InvalidateRow(
                    CUR,
                    (int)$row[$keyName],
                    $keyName,
                    $dataSetID,
                    $opCode
                );
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

/* DSYN_UpdateSet

        Updates the dependencies defined in $dataSetID for each primary key
        identifier $keyName within the array $set using the connection $db.
    */
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
    $res = mysqli_query($db, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $set[] = $row;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    } else {
        /* Duplicated from l-sql.php's sqlerror, since l-dsyn.php needs to
                be as self-contained as possible. */
        $txt = mysqli_error($db);
        $num = mysqli_errno($db);
        $msg = "mysql: ($num) $txt";
        $cmd = "mysql: $sql";
        logs::log(__FILE__, __LINE__, $cmd, 0);
        logs::log(__FILE__, __LINE__, $msg, 0);
    }
    return $set;
}
