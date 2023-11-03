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


define('constDSYNRepairDefault',        3);
define('constDSYNRepairCheckOnly',        0);
define('constDSYNRepairAll',            1);
define('constDSYNRecomputeSet',         2);


define('constDataSetCoreCensusSingle',          35);
define('constDataSetCoreMachineGroupMapSingle', 36);
define('constDataSetGConfigVarVersionsDefMap',  37);
define('constDataSetGConfigVarValuesNoDefaults', 38);


define('constSyncStateUndefined',       0);
define('constSyncStateSynchronizing',   1);
define('constSyncStateFinished',        2);
define('constSyncStateClientError',     3);
define('constSyncStateServerError',     4);
define('constSyncStateStopped',         5);


define('constDBNTypeRegular',   0);
define('constDBNTypeOneWay',    1);


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

            $error = 1;
            break;
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
    $res = mysqli_query($db, $sql);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $set[] = $row;
        }
        mysqli_free_result($res);
    } else {

        $txt = mysqli_error($db);
        $num = mysqli_errno($db);
        $msg = "mysql: ($num) $txt";
        $cmd = "mysql: $sql";
    }
    return $set;
}

function DSYN_LogSyncRPC($rawin, $rawout, $rval, $site, $host, $stime)
{
    $startidx = strpos($rawout, '<procname>');
    $endidx = strpos($rawout, '</procname>');
    if (($startidx === false) || ($endidx === false)) {
        return;
    }
    $startname = $startidx + strlen('<procname>');
    $procname = substr($rawout, $startname, $endidx - $startname);

    $state = constSyncStateSynchronizing;
    if ($procname == 'SYNC_StopSync') {
        $state = constSyncStateStopped;
    }
    if ($procname == 'DSYN_Synchronize') {
        $datasetid = -1;
        $startidx = strpos($rawout, ' type="UINT32">');
        if ($startidx !== false) {
            $endidx = strpos($rawout, '</param>', $startidx);

            if ($endidx !== false) {
                $startset = $startidx + strlen(' type="UINT32">');
                $datasetid = (int)substr($rawout, $startset, $endidx = $startset);
            }
        }

        if ($datasetid != -1) {
            $procname .= '(' . $datasetid . ')';
        }
    }

    $startidx = strpos($rawout, '<returnval type="ERRSTAT">');
    $endidx = strpos($rawout, '</returnval>');
    if (($startidx === false) || ($endidx === false)) {
        return;
    }

    $starterr = $startidx + strlen('<returnval type="ERRSTAT">');
    $errstat = (int)substr($rawout, $starterr, $endidx - $starterr);
    if ($errstat != constAppNoErr) {
        if (strpos($procname, 'DSYN_Synchronize') !== false) {
            if ($errstat != constErrRetrySync) {
                $state = constSyncStateServerError;
            } else {
                $errstat = constAppNoErr;
            }
        } else if ($procname == 'DSYN_GetAggregateChecksum') {
            if ($errstat != constErrGetMergedCriteria) {
                $state = constSyncStateServerError;
            } else {
                $errstat = constAppNoErr;
            }
        } else {
            $state = constSyncStateServerError;
        }
    }

    $blacklist = 0;
    if ($errstat == constErrClientBlacklisted) {
        $blacklist = 1;
    }

    $db = db_connect();
    $now = time();
    DSYN_LogSyncStatus(
        $site,
        $host,
        $now,
        $state,
        -1,
        $errstat,
        $procname,
        $stime,
        strlen($rawin),
        strlen($rawout),
        $blacklist,
        $db
    );
}

function DSYN_LogSyncEvent($scrp, $sql, $now, $id, $site, $host, $db)
{
    if (($scrp == 0) && (stripos($sql, 's00177.c') === false)) {
        return;
    }
    if ($scrp == 0) {
        $errtext = 'Original error status: ';
        $pos = stripos($sql, $errtext);
        if ($pos !== false) {
            $pos += strlen($errtext);
            if (sscanf(substr($sql, $pos), "%D", $code) == 1) {
                DSYN_LogRealSyncEvent($scrp, $code, $now, $id, $site, $host, $db);
            }
        }
        return;
    }

    if (stripos($sql, 'Client is synchronized') !== false) {
        DSYN_LogRealSyncEvent($scrp, constAppNoErr, $now, $id, $site, $host, $db);
        return;
    }

    $errtext = 'Error code: ';
    $pos = stripos($sql, $errtext);
    if ($pos !== false) {
        $pos += strlen($errtext);
        if (sscanf(substr($sql, $pos), "%D", $code) == 1) {
            DSYN_LogRealSyncEvent($scrp, $code, $now, $id, $site, $host, $db);
            return;
        }
    }

    if (stripos($sql, 'failed to synchronize after multiple retries') !== false) {
        DSYN_LogRealSyncEvent($scrp, constErrBadChecksum, $now, $id, $site, $host, $db);
        return;
    }

    if (stripos($sql, 'Could not open connection with server') !== false) {
        DSYN_LogRealSyncEvent($scrp, constErrCantConnect, $now, $id, $site, $host, $db);
        return;
    }

    if (stripos($sql, 'Server closed SSL connection with the client') !== false) {
        DSYN_LogRealSyncEvent($scrp, constErrOpenSSL, $now, $id, $site, $host, $db);
        return;
    }

    if (stripos($sql, 'HTTP Request failed') !== false) {
        DSYN_LogRealSyncEvent($scrp, constErrHttpReqFailed, $now, $id, $site, $host, $db);
        return;
    }

    if (stripos($sql, 'No such host is known') !== false) {
        DSYN_LogRealSyncEvent($scrp, constErrCantGetHost, $now, $id, $site, $host, $db);
        return;
    }

    if (stripos($sql, 'Server is busy, client was instructed to retry in') !== false) {
        DSYN_LogRealSyncEvent($scrp, constErrServerTooBusy, $now, $id, $site, $host, $db);
        return;
    }

    DSYN_LogRealSyncEvent($scrp, constErrBadFormat, $now, $id, $site, $host, $db);
}

function DSYN_LogRealSyncEvent($scrp, $err, $now, $id, $site, $host, $db)
{
    $syncstate = constSyncStateUndefined;
    if ($scrp == 177) {
        if ($err == constAppNoErr) {
            $syncstate = constSyncStateFinished;
        } else {
            $syncstate = constSyncStateClientError;
        }
    } else {
        $syncstate = constSyncStateClientError;
    }

    DSYN_LogSyncStatus($site, $host, $now, $syncstate, $id, $err, '', -1, -1, -1, 0, $db);
}

function DSYN_LogSyncStatus(
    $site,
    $host,
    $now,
    $syncstate,
    $id,
    $err,
    $rpc,
    $stime,
    $rpcrx,
    $rpctx,
    $blacklist,
    $db
) {
    $enabled = server_def('sync_history', 0, $db);
    if (!$enabled) {
        return;
    }

    if (!mysqli_select_db('core', $db)) {
        return;
    }
    $qsite = safe_addslashes($site);
    $qhost = safe_addslashes($host);

    $sql = "UPDATE Census SET lastsync=$now, syncstate=$syncstate, syncerr=$err";


    if ($blacklist == 1) {
        $sql .= ", syncblock=1";
    }
    $sql .= " WHERE site='$qsite' AND host='$qhost'";
    command($sql, $db);

    if ($blacklist == 0) {
        $sql = "SELECT syncblock FROM Census WHERE site='$qsite' AND host='$qhost'";
        $row = find_one($sql, $db);
        if ($row) {
            $blacklist = (int)$row['syncblock'];
        }
    }

    $sql = "INSERT INTO SyncHistory (site, host, synctime, syncstate, synceventid, "
        . "syncblock, syncerr, syncrpc, rpctime, rpcrx, rpctx) VALUES ("
        . "'$qsite', '$qhost', $now, $syncstate, $id, $blacklist, $err, '" . safe_addslashes($rpc)
        . "', $stime, $rpcrx, $rpctx)";
    command($sql, $db);
}
