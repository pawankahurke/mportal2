<?php

/*
Revision History

Date       Who   What
----       ---   ---- 
19-Sep-05  BJS   Created.
13-Oct-05  BJS   Removed Census_sql(), only ever call
                 TempCensus_sql() here.
11-Nov-05  BJS   Do not use *, specify fields when selecting.
26-Jan-06  BTE   Bug 3059: Redesign DSYN and tables to "remotable" internal
                 pointers.
13-Mar-06  BTE   Bug 3199: Remove unused core database columns.
19-Feb-08  BTE   Bug 4416: Move the "last event log" timestamp into shared
                 memory.

*/

/*
    Following files depend on l-core:
    -------------
    /acct/update.php
    /cron/c-report.php

    */


define('constFieldID',      'id');
define('constFieldUUID',    'uuid');
define('constCensusTemp',   0);
define('constCensus',       1);


/* 
    |  $table_type = (true) create the real Census table,
    |                (false) create a temporary Census table.
    |  $db         = database handle.
    |
    */
function create_coreCensus($table_type, $db)
{
    switch ($table_type) {
        case 0:
            TempCensus_sql($db);
            break;
        default:
            logs::log(__FILE__, __LINE__, "l-core: unknown table type:$table_type");
    }
}


function TempCensus_sql($db)
{
    /*
        | any field added to core.Census in update.php
        | needs to be added to this temporary table - unless it is one of
        | the new unique fields that are not used in legacy code.
        |
        | We no longer select * into temp_Census, 
        | instead we specify the fields.
        */
    $def = 'not null default';
    $sql = "create temporary table temp_Census (\n"
        . "  temp_id    int(11) not null auto_increment,\n"
        . "  site  varchar(50)  $def '',\n"
        . "  host  varchar(64)  $def '',\n"
        . "  temp_uuid  varchar(50)  $def '',\n"
        . "  born  int(11)      $def  0,\n"     // 3/16/2005
        . "  last  int(11)      $def  0,\n"
        . "  code  int(11)      $def  0,\n"     // 3/16/2005
        . "  deleted tinyint(1) $def  0,\n"
        . "  primary key (temp_id),\n"
        . "  key site (site),\n"
        . "  key host (host),\n"
        . "  unique key uniq (site,host),\n"
        . "  unique index guid (temp_uuid)\n"       // 3/16/2005
        . ")";

    $res = redcommand($sql, $db);
    if ($res) {
        $sql = "insert ignore into temp_Census\n"
            . " select id,site,host,uuid,born,last,code,deleted from " . $GLOBALS['PREFIX'] . "core.Census";
        redcommand($sql, $db);
        CORE_UpdateCensusCacheTable('temp_Census', 'temp_id', $db);
    }
}


/* CORE_CreateMachineGroupMap

        Generates a mysql statement to add a new row to core.MachineGroupMap
        using $censusid (from core.Census.id), $mcatid (from
        core.MachineCategories.mcatid) and $mgroupid (from core.MachineGroups.
        mgroupid).
    */
function CORE_CreateMachineGroupMap($censusid, $mcatid, $mgroupid)
{
    return "INSERT INTO " . $GLOBALS['PREFIX'] . "core.MachineGroupMap (mgroupuniq,mcatuniq,"
        . "censusuniq,censussiteuniq) SELECT mgroupuniq,"
        . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq,censusuniq,censussiteuniq"
        . " FROM " . $GLOBALS['PREFIX'] . "core.Census, " . $GLOBALS['PREFIX'] . "core.MachineCategories, " . $GLOBALS['PREFIX'] . "core.MachineGroups"
        . " WHERE id=" . $censusid . " AND mcatid=" . $mcatid . " AND "
        . "mgroupid=" . $mgroupid;
}


/* CORE_GetCachedTime

        Gets the cached timestamp from shared memory.
    */
function CORE_GetCachedTime(&$last, $site, $machine)
{
    $newlast = 0;
    $err = PHP_CORE_GetTimestamp(CUR, $newlast, $site, $machine);
    if ($err != constAppNoErr) {
        logs::log(__FILE__, __LINE__, 'CORE_GetCachedTime: error for '
            . 'PHP_CORE_GetTimestamp ' . $err, 0);
        $newlast = 0;
    }

    if ($newlast != 0) {
        $last = $newlast;
    }
}


/* CORE_CreateTempCensusCache

        Clones the Census table into TempCensusCache, and updates the last
        timestamps to match the cache.  This is not a temporary table,
        remember to drop it when you are finished.
    */
function CORE_CreateTempCensusCache($db)
{
    /* Clone the Census table */
    $sql = 'CREATE TABLE TempCensusCache LIKE Census';
    command($sql, $db);
    $sql = 'INSERT INTO TempCensusCache SELECT * FROM Census';
    command($sql, $db);

    /* Update with latest timestamps */
    CORE_UpdateCensusCacheTable('TempCensusCache', 'id', $db);
}


/* CORE_GetAllTempCensusCache

        Returns all rows using a clone of the Census table called
        TempCensusCache with the latest timestamps.  Specify select,
        joins, and ordering in $sql.  This procedure handles dropping
        TempCensusCache internally and just returns a result set.
    */
function CORE_GetAllTempCensusCache($sql, $db)
{
    CORE_CreateTempCensusCache($db);
    $set = find_many($sql, $db);
    command('DROP TABLE TempCensusCache', $db);
    return $set;
}


/* CORE_GetOneTempCensusCache

        Similar to CORE_GetAllTempCensusCache, except the result is for
        find_one instead of find_many.
    */
function CORE_GetOneTempCensusCache($sql, $db)
{
    CORE_CreateTempCensusCache($db);
    $row = find_one($sql, $db);
    command('DROP TABLE TempCensusCache', $db);
    return $row;
}


/* CORE_UpdateCensusCacheTable

        Treats $table as a "Census" table with at least site, host, last
        and primary key column named $id.  Updates the last column in
        $table with the census cache timestamps.
    */
function CORE_UpdateCensusCacheTable($table, $id, $db)
{
    $sql = "SELECT $id, site, host, last FROM $table";
    $set = find_many($sql, $db);
    if ($set) {
        foreach ($set as $key => $row) {
            $last = $row['last'];
            CORE_GetCachedTime($last, $row['site'], $row['host']);
            $sql = "UPDATE $table SET last=$last WHERE $id="
                . $row[$id];
            command($sql, $db);
        }
    }
}
