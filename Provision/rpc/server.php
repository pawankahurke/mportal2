<?php

/*
Revision history:

Date        Who     What
----        ---     ----
13-Aug-02   EWB     Always log mysql failures
20-Aug-02   EWB     Made it simpler to add new procedures
20-Aug-02   EWB     Implementation of RUNT_GetRevision
20-Aug-02   EWB     Support for safe_array_keys in php3.
22-Aug-02   EWB     Don't need safe_array_keys after all.
22-Aug-02   EWB     Logs unknown procedure call
 9-Sep-02   EWB     Repent of magic quotes.
27-Sep-02   MMK     Added TIMR_GetNow
05-Nov-02   AAM     Fixed error return for non-existent server function so that
                    it works correctly.
11-Nov-02   EWB     Moved db_select to lib-db.php3
12-Nov-02   EWB     rpc_encode, rpc_decode, double_encode, double_decode
13-Nov-02   EWB     Return appropriate error codes.
13-Nov-02   EWB     Created db_code.
26-Nov-02   EWB     Need to double_encode all return values.
 2-Dec-02   EWB     RUNT_GetRevision returns constRevisionLevel
20-Dec-02   EWB     Removed extra assignment for parse_alist
26-Dec-02   AAM     Performance improvement: split -> explode
20-Jan-03   AAM     Added extract_alist, fully_parse_alist, and
                    fully_make_alist.  The latter is a masterpiece of recursive
                    programming, by the way.  Removed remote calls for
                    SCNF_PublishHost, VARS_PublishHost, and VARS_QueryRevl.
                    Added remote calls for VARS_CheckSync, VARS_ApplyPackage,
                    and SCNF_ApplyScrips.
30-Jan-03   EWB     Created server_var(), server_opt()
30-Jan-03   EWB     Don't log mysql error ER_DUP_ENTRY (1062)
 4-Feb-03   EWB     fully_parse_alist should *always* return an array.
17-Feb-03   AAM     Fixed big performance problem (exacerbated in PHP3) with
                    extract_alist.  Fixed a warning that was popping up a lot
                    in PHP3.
28-May-03   MMK     Moved the RPC string code to a library. Added name of
                    procedure for CONF_GetClientSettings.
30-Oct-03   EWB     Created server_def()
31-Oct-03   EWB     Pass large arrays by reference if possible.
10-Dec-03   AAM     Used $_POST instead of $GLOBALS['HTTP_POST_VARS']; we are
                    at a PHP version now (due to security patches) where that
                    is OK, and I think it will save some memory.  Also changed
                    build_args to special-case the 5th parameter of
                    ELOG_AssetDataALIST and the 4th parameter of
                    VARS_ApplyPackage, which can be large ALISTs, so that it
                    doesn't try to copy those.  Also added get_alist_param, to
                    provide a way to access those parameters.
23-Mar-04   EWB     created affected()
27-Apr-04   EWB     find_many arrives from provis.
28-Apr-04   EWB     create debug_time().
 4-Jun-04   EWB     created update_opt()
11-Jun-04   BTE     Added RPC calls for Patch Management to function dispatch.
16-Aug-04   BTE     Added RPC call INST_GetDefaultConfig.
 8-Dec-04   EWB     Improved mysql error logging.
16-Dec-04   EWB     Don't log giant mysql statements.
17-Mar-05   EWB     New RPC event logging.
 1-Apr-05   EWB     Gather RPC statistics in shared memory.
03-Oct-05   BTE     Added CSRV RPC calls (we are no longer using csrv.cgi
                    directly because of environment variable limitations).
24-Oct-05   BTE     Added AUDT_Audit as a CSRV RPC call.
31-Oct-05   BTE     Removed unused RPC calls, added CONF_GetSiteInfo.
10-Nov-05   BTE     Updated to use the new census_manage function.
15-Dec-05   AAM     Move common code into l-pcnt.php, and change RPC count to
                    more flexible start/stop performance counter implementation.
                    Add (currently commented-out) code to allow discarding
                    high-volume client operations.  Use new performance counters
                    for RPC, and also keep track of "server too busy" counts.
23-Dec-05   BTE     Added DSYN_IntersectCriteria.
16-Feb-06   BTE     Added DSYN_GetAggregateChecksum.
06-May-06   BTE     Fixed certain calls to work properly with server libraries.
15-May-06   BTE     Bug 2967: Handle duplicate UUIDs and remove migration for
                    pre-2.2 clients.
19-Jul-06   BTE     Bug 3013: Server should be able to tell client which other
                    clients need to sync.
24-Jul-06   BTE     Fixed incorrect log entries for unknown procedures that
                    really do exist.
13-Nov-06   BTE     Bug 3847: HTPC: needs to URL encode/decode protocol
                    parameters.
16-Nov-06   BTE     Added "fix" for 101 protocol.
17-Nov-06   JRN     Added TSYN_InsertData, TSYN_GetEngineVersion.
06-Jun-07   WOH     ELOG_AssetPartialDataALIST() and ELOG_CheckAsset()
18-Oct-07   WOH     Added SYNC_StartSync and SYNC_StopSync.
19-Feb-08   BTE     Bug 4416: Move the "last event log" timestamp into shared
                    memory.
23-Mar-15   BTE     Added TSYN_SynchronizeTable.
07-Apr-15   BTE     Added EXEC_QueryProfileUpdate.

*/


/*
    |  mysql Error codes:
    |
    |   /usr/include/mysql/mysqld_errno.h
    |
    */


define('ER_DUP_ENTRY', 1062);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
logs::log("RPC_START", ["request" => $_REQUEST, "post" => $_POST,  "get" => $_GET]);

function redcommand($sql, $db)
{
    debug_code('debug_sql', 'red', $sql);
    return command($sql, $db);
}

/* Special version to force RPC's to always work. */
function global_def($name, $def)
{
    if (strcmp($name, 'test_sql') == 0) {
        return 0;
    }
    if (strcmp($name, 'show_sql') == 0) {
        return 0;
    }
    if (strcmp($name, 'debug') == 0) {
        return 0;
    }
    if (strcmp($name, 'refreshtime') == 0) {
        return '';
    }
    return $def;
}


/*
    |  We have a few sites still running older versions of php.
    |  So ... we'll use the new superglobals if they exist,
    |  and fall back to the older method if that fails.
    |
    |  In any event, we want to continue to run
    |  whether register_globals is turned on or not.
    |
    |  http://www.php.net/manual/en/security.registerglobals.php
    |  http://www.php.net/manual/en/reserved.variables.php
    */

function server_var($name)
{
    if (isset($_SERVER)) {
        return $_SERVER[$name];
    }
    if (isset($GLOBALS['HTTP_SERVER_VARS'])) {
        return $GLOBALS['HTTP_SERVER_VARS'][$name];
    }
    return '';
}


/*
    |  Acts just the same as mysql_query, plus it
    |  logs errors to the php log file.  It also
    |  writes them to the screen, just in case
    |  we're debugging.
    |
    |  See /usr/include/mysql/mysqld_error.h
    */

function command($sql, $db)
{
    $aaa = microtime();
    $res = mysqli_query($db, $sql);
    $bbb = microtime();
    $xxx = microtime_diff($aaa, $bbb);
    $len = strlen($sql);
    $trace = debug_backtrace();
    logs::log($trace[0]["file"], $trace[0]["line"], $sql);
    if (($len > 256) && ((!$res) || ($xxx > 60))) {
        $sql = substr($sql, 0, 256) . '...';
    }
    if (!$res) {
        $msg = mysqli_error($db);
        $num = mysqli_errno($db);
        if ($num != ER_DUP_ENTRY)  // expected
        {
            $secs = microtime_show($xxx);
            $stat = "n:$num,l:$len";
            $text = "mysql: ($stat) $msg ($secs)";
            $cmds = "mysql: $sql";
            logs::log($trace[0]["file"], $trace[0]["line"], $cmds, $text);
        }
    }
    if (($res) && ($xxx > 60)) {
        $secs = microtime_show($xxx);
        $text = "mysql: slow (l:$len) ($secs) $sql";
        logs::log($trace[0]["file"], $trace[0]["line"], $text);
    }
    return $res;
}


/*
    |  We want one and only one result.
    */

function find_one($sql, $db)
{
    logs::tag("SQL", __FUNCTION__, ["sql" => $sql], 1);
    return NanoDB::find_one($sql, $db);
}


function find_many($sql, $db)
{
    logs::tag("SQL", __FUNCTION__, ["sql" => $sql], 1);
    return NanoDB::find_many($sql, $db);
}


function debug_time($msg, $asec)
{
    $bsec = microtime();
    $msec = microtime_diff($asec, $bsec);
    $secs = microtime_show($msec);
    $text = "timing: $msg in $secs";
    //   logs::log(__FILE__, __LINE__, $text,0);
    debug_note($text);
}



/*
    |  This returns the value of the server option, if it
    |  exists, or the default value if it does not.
    */

function server_def($name, $def, $db)
{
    $value = $def;
    $qname = safe_addslashes($name);
    $sql = "select * from " . $GLOBALS['PREFIX'] . "core.Options where name = '$qname'";
    $row = find_one($sql, $db);
    if ($row) {
        $value = $row['value'];
    }
    return $value;
}

/*
    |  This returns just a single server option,
    |  or the empty string.
    */

function server_opt($name, $db)
{
    return server_def($name, '', $db);
}


/*
    |  Updates the value of an already existing option.
    |  If the option doesn't exist yet, that's ok, just
    |  fail silently.  We don't want the rpc code to
    |  worry about creating the option initially.
    */

function update_opt($name, $valu, $db)
{
    $qn  = safe_addslashes($name);
    $qv  = safe_addslashes($valu);
    $sql = "update " . $GLOBALS['PREFIX'] . "core.Options\n"
        . " set value = '$qv'\n"
        . " where name = '$qn'";
    command($sql, $db);
}


/*
    |  We want our code to stop relying upon the
    |  evil of so-called "magic quotes".  We want to
    |  *KNOW* if the string is already quoted or not.
    */

function posted($name)
{
    $v = @$_POST[$name];
    // if (get_magic_quotes_gpc())
    // {
    //     $v = stripslashes($v);
    // }
    return rpc_decode($v);
}


/* build_args
        Put together the "$args" array from the parameters that are passed
        in from an RPC.  The RPCs that pass in large ALISTs use too much
        time and memory copying the strings around, so we special-case those,
        and the procedures involved get the parameters using get_alist_param
        below.
    */
function build_args($usec, $procName)
{
    /* Set things up to not process large ALIST parameters. */
    switch ($procName) {
        case 'ELOG_AssetPartialDataALIST':
        case 'ELOG_AssetDataALIST':
            $skipParam = 5;
            break;
        case 'VARS_ApplyPackage':
            $skipParam = 4;
            break;
        default:
            $skipParam = 0;
            break;
    }

    $args = array();
    $args['usec'] = $usec;
    $args['prot'] = posted('ProtocolVer');
    $args['proc'] = posted('ProcName');
    $args['pver'] = posted('ProcVer');
    $args['nump'] = posted('NumParams');
    $args['type'] = array();
    $args['valu'] = array();
    for ($i = 1; $i <= $args['nump']; $i++) {
        $tname = sprintf('Type%02d', $i);
        $vname = sprintf('Val%02d', $i);
        $args['type'][$i] = posted($tname);
        if ($i == $skipParam) {
            $args['valu'][$i] = '';
        } else {
            $args['valu'][$i] = posted($vname);
        }
    }

    switch ($args['prot']) {
        case constProtocolVer100:
            /* Legacy version, no site/host/uuid */
            break;
        case constProtocolVer101:
            /* New version */
            $args['site'] = posted('Site');
            $args['machine'] = posted('Machine');
            $args['uuid'] = posted('UUID');

            /* Kludge to allow & in the site name */
            $startPos = strpos($GLOBALS['HTTP_RAW_POST_DATA'], '&Site=');
            if ($startPos > 0) {
                $startPos = $startPos + strlen('&Site=');
            }
            $finalPos = strpos($GLOBALS['HTTP_RAW_POST_DATA'], '&Machine=');
            if (($finalPos > $startPos) && ($startPos > 0) && ($finalPos > 0)) {
                $args['site'] = substr(
                    $GLOBALS['HTTP_RAW_POST_DATA'],
                    $startPos,
                    $finalPos - $startPos
                );
            }
            break;
        case constProtocolVer102:
            /* RPC decode */
            $args['site'] = double_decode(posted('Site'));
            $args['machine'] = double_decode(posted('Machine'));
            $args['uuid'] = double_decode(posted('UUID'));
            break;
    }
    return $args;
}

/* get_alist_param
        This gets an RPC parameter with number $paramNum that may be a large
        ALIST.  Note that it is return by reference, so you have to assign
        by reference when you call it!
    */
function &get_alist_param($paramNum)
{
    $vname = sprintf('Val%02d', $paramNum);

    /* Try to avoid copying the data more than absolutely necessary. */
    // if (get_magic_quotes_gpc())
    // {
    //     $v = stripslashes(@ $_POST[$vname]);
    // }
    // else
    // {
    $v = &$_POST[$vname];
    // }

    /* Since rpc_decode does a copy, only do it if it looks likely. */
    if ((strlen($v) > 0) && ($v[0] == '\\')) {
        $v = rpc_decode($v);
    }

    return $v;
}

function hpc_return(&$args)
{
    $proc = $args['proc'];
    $prot = $args['prot'];
    $pver = $args['pver'];
    $nump = $args['nump'];
    $rval = $args['rval'];

    $xml = "<?xml version=\"1.0\"?>\n"
        . "<?xml:stylesheet type=\"text/xsl\" href=\"hpc_display.xsl\"?>\n"
        . "<hpcreturn>\n"
        . "<ProtocolVer>$prot</ProtocolVer>\n"
        . "<ProcName>$proc</ProcName>\n"
        . "<ProcVer>$pver</ProcVer>\n"
        . "<NumParams>$nump</NumParams>\n"
        . "<ReturnVal type=\"ERRSTAT\">$rval</ReturnVal>\n"
        . "<Parameters>\n";
    for ($i = 1; $i <= $nump; $i++) {
        $t    = double_encode($args['type'][$i]);
        $v    = double_encode($args['valu'][$i]);
        $xml .= "\t<Param num=\"$i\" type=\"$t\">$v</Param>\n";
    }
    $xml .= "</Parameters>\n";
    $xml .= "</hpcreturn>\n";
    return $xml;
}



/*
    |  This one is so simple we'll just stick it here.
    |  Note, function names are not case-sensitive.
    */

function RUNT_GetRevision(&$args)
{
    $valu[1] = constRevisionLevel;
    $args['valu'] = $valu;
    $args['rval'] = constAppNoErr;
    $args['oxml'] = 1;
}


/* Count "server too busy" occurrences. */
function count_busy_procs($proc)
{
    if (($proc == 'ELOG_AssetDataALIST') ||
        ($proc == 'VARS_CheckSync')      ||
        ($proc == 'ELOG_CheckAsset')
    ) {
        start_perf_count(
            $proc . '_too_busy',
            $wSec,
            $wUsec,
            $uSec,
            $uUsec,
            $sSec,
            $sUsec,
            $enabled
        );
        finish_perf_count(
            $proc . '_too_busy',
            $wSec,
            $wUsec,
            $uSec,
            $uUsec,
            $sSec,
            $sUsec,
            $enabled
        );
    }
}



/*
    |  Actually performs the remote procedure call
    */

function dispatch(&$args)
{
    /* This is the list of number of parameters.  Note that these numbers
            do NOT include the initial mID parameter. */
    static $pnums = array(
        'ELOG_AssetDataALIST' => 5,
        'ELOG_LogEntry' => 22,
        'RUNT_GetRevision' => 1,
        'TIMR_GetNow' => 1,
        'EXEC_QueryUpdate' => 8,
        'VARS_CheckSync' => 2,
        'VARS_ApplyPackage' => 4,
        'SCNF_ApplyScrips' => 2,
        'CONF_GetClientSettings' => 2,
        'EXEC_QueryProfileUpdate' => 5,

        'PROV_ReportAudit' => 4,
        'PROV_ReportMeter' => 4,
        'PROV_SetKeys' => 4,
        'PROV_GetKeys' => 5,

        'INST_CheckWUTimes' => 4,
        'INST_GetWUConfig' => 4,
        'INST_GetPatches' => 6,
        'INST_SendPatches' => 4,
        'INST_SetStatus' => 4,
        'INST_GetDefaultConfig' => 4,

        'CONF_GetSiteInfo' => 2,
        'VARS_GetMachinesNeedSync' => 2,
        'ELOG_CheckAsset' => 3,
        'ELOG_AssetPartialDataALIST' => 5
    );


    /* These need to be defined in dev/cust/opsys/linux/hpcp.c and
            iface/none/hpcp.h - This is a manual opertation. */
    static $csrv = array(
        'DSYN_Synchronize' => 5,
        'DSYN_GetEngineVersion' => 1,
        'DSYN_GetAggregationList' => 4,
        'AUDT_Audit' => 2,
        'DSYN_IntersectCriteria' => 4,
        'DSYN_GetAggregateChecksum' => 8,
        'TSYN_InsertData' => 3,
        'TSYN_GetEngineVersion' => 1,
        'SYNC_StartSync' => 4,
        'SYNC_StopSync' => 2,
        'TSYN_SynchronizeTable' => 7
    );

    $args['rval'] = constErrServerNoSupport;
    $args['olog'] = 1;  /* log by default */
    $args['oxml'] = 1;  /* xml by default */
    $args['updatecensus'] = 1;  /* update the census */

    $good = 0;
    $prot = $args['prot'];
    $pver = $args['pver'];
    $proc = $args['proc'];
    $nump = $args['nump'];

    //        /* Just respond positively for some calls but don't do anything.  We
    //            are trying to clear out client queues. */
    // Leave ELOG_LogEntry in
    //        if ($proc == 'ELOG_LogEntry')
    //        {
    //            start_perf_count('ELOG_LogEntry_skipped',
    //                $wSec, $wUsec, $uSec, $uUsec, $sSec, $sUsec, $enabled);
    //
    //            /* Traffic optimization */
    //            $args['valu'][17] = '';  // string1
    //            $args['valu'][18] = '';  // string2
    //            $args['valu'][19] = '';  // text1
    //            $args['valu'][20] = '';  // text2
    //            $args['valu'][21] = '';  // text3
    //            $args['valu'][22] = '';  // text4
    //
    //            /* Return value */
    //            $args['olog'] = 0;
    //            $args['oxml'] = 1;
    //            $args['rval'] = constAppNoErr;
    //
    //            /* Server thinks it's OK */
    //            $good = 1;
    //
    //            finish_perf_count('ELOG_LogEntry_skipped',
    //                $wSec, $wUsec, $uSec, $uUsec, $sSec, $sUsec, $enabled);
    //        }
    // Leave ELOG_AssetDataALIST
    //        else
    //        if ($proc == 'ELOG_AssetDataALIST')
    //        {
    //            start_perf_count('ELOG_AssetDataALIST_skipped',
    //                $wSec, $wUsec, $uSec, $uUsec, $sSec, $sUsec, $enabled);
    //
    //            /* Traffic optimization */
    //            $args['valu'][6] = '';
    //            $args['valu'][7] = 0;
    //
    //            /* Return value */
    //            $args['olog'] = 0;
    //            $args['oxml'] = 1;
    //            $args['rval'] = constAppNoErr;
    //
    //            /* Server thinks it's OK */
    //            $good = 1;
    //
    //            finish_perf_count('ELOG_AssetDataALIST_skipped',
    //                $wSec, $wUsec, $uSec, $uUsec, $sSec, $sUsec, $enabled);
    //        }
    // Leave VARS_CheckSync in for now
    //        else
    //        if ($proc == 'VARS_CheckSync')
    //        {
    //            start_perf_count('VARS_CheckSync_skipped',
    //                $wSec, $wUsec, $uSec, $uUsec, $sSec, $sUsec, $enabled);
    //
    //            /* Traffic optimization */
    //            $args['valu'][2] = '';
    //
    //            /* Return value */
    //            $args['valu'][1] = constCheckSyncDoNothing;
    //            $args['olog'] = 0;
    //            $args['oxml'] = 1;
    //            $args['rval'] = constAppNoErr;
    //
    //            /* Server thinks it's OK */
    //            $good = 1;
    //
    //            finish_perf_count('VARS_CheckSync_skipped',
    //                $wSec, $wUsec, $uSec, $uUsec, $sSec, $sUsec, $enabled);
    //        }
    //        else
    if (isset($pnums[$proc])) {
        $pnum = @$pnums[$proc];
        if (($prot == constProtocolVer100) && ($pver == 100) &&
            ($pnum == $nump)
        ) {
            start_perf_count(
                $proc,
                $wSec,
                $wUsec,
                $uSec,
                $uUsec,
                $sSec,
                $sUsec,
                $enabled
            );
            $proc($args);
            finish_perf_count(
                $proc,
                $wSec,
                $wUsec,
                $uSec,
                $uUsec,
                $sSec,
                $sUsec,
                $enabled
            );
            if ($enabled && ($args['rval'] == constErrServerTooBusy)) {
                count_busy_procs($proc);
            }
            $good = 1;
        } else if (($prot == constProtocolVer101) && ($pver == 100) &&
            ($pnum == $nump)
        ) {
            $isLegacy = 0;
            $site = $args['site'];
            $machine = $args['machine'];
            $uuid = $args['uuid'];
            $runUpdateCensus = 0;
            $args['updatecensus'] = 0; /* we will update here */

            /* Add any procedures that should not update the census to
                    this list */
            if ((strcmp($proc, "ELOG_LogEntry") != 0) &&
                (strcmp($proc, "RUNT_GetRevision") != 0) &&
                (strcmp($proc, "TIMR_GetNow") != 0) &&
                (strcmp($proc, "CONF_GetClientSettings") != 0) &&
                (strcmp($proc, "CONF_GetSiteInfo") != 0)
            ) {
                $runUpdateCensus = 1;
            }
            /* ELOG_LogEntry is a special case - only update the
                    timestamp in the census table and ignore errors */ else if (strcmp($proc, "ELOG_LogEntry") == 0) {
                $args['eloguuid'] = $uuid;
                $uuid = '';
                $isLegacy = 1;
                $runUpdateCensus = 1;
            }

            $err = constAppNoErr;

            if ($runUpdateCensus) {
                start_perf_count(
                    'census_manage',
                    $wSec,
                    $wUsec,
                    $uSec,
                    $uUsec,
                    $sSec,
                    $sUsec,
                    $enabled
                );
                $db = db_code('db_cor');
                $err = census_manage(
                    $site,
                    $machine,
                    $uuid,
                    $isLegacy,
                    $db
                );
                $args['rval'] = $err;
                finish_perf_count(
                    'census_manage',
                    $wSec,
                    $wUsec,
                    $uSec,
                    $uUsec,
                    $sSec,
                    $sUsec,
                    $enabled
                );
            }

            if ($err == constAppNoErr) {
                start_perf_count(
                    $proc,
                    $wSec,
                    $wUsec,
                    $uSec,
                    $uUsec,
                    $sSec,
                    $sUsec,
                    $enabled
                );
                $proc($args);
                finish_perf_count(
                    $proc,
                    $wSec,
                    $wUsec,
                    $uSec,
                    $uUsec,
                    $sSec,
                    $sUsec,
                    $enabled
                );
                if ($enabled && ($args['rval'] == constErrServerTooBusy)) {
                    count_busy_procs($proc);
                }
                $good = 1;
            } else {
                logs::log(__FILE__, __LINE__, "census_manage returned $err for $proc from "
                    . $site . ":" . $machine . " with " . $uuid, 0);
                /* No longer an unknown procedure */
                $good = 1;
            }
        } else if (($prot == constProtocolVer102) && ($pver == 100) &&
            ($pnum == $nump)
        ) {
            $isLegacy = 0;
            $site = $args['site'];
            $machine = $args['machine'];
            $uuid = $args['uuid'];
            $runUpdateCensus = 0;
            $args['updatecensus'] = 0; /* we will update here */

            /* Add any procedures that should not update the census to
                    this list */
            if ((strcmp($proc, "ELOG_LogEntry") != 0) &&
                (strcmp($proc, "RUNT_GetRevision") != 0) &&
                (strcmp($proc, "TIMR_GetNow") != 0) &&
                (strcmp($proc, "CONF_GetClientSettings") != 0) &&
                (strcmp($proc, "CONF_GetSiteInfo") != 0)
            ) {
                $runUpdateCensus = 1;
            }
            /* ELOG_LogEntry is a special case - only update the
                    timestamp in the census table and ignore errors */ else if (strcmp($proc, "ELOG_LogEntry") == 0) {
                $args['eloguuid'] = $uuid;
                $uuid = '';
                $isLegacy = 1;
                $runUpdateCensus = 1;
            }

            $err = constAppNoErr;

            if ($runUpdateCensus) {
                start_perf_count(
                    'census_manage',
                    $wSec,
                    $wUsec,
                    $uSec,
                    $uUsec,
                    $sSec,
                    $sUsec,
                    $enabled
                );
                $db = db_code('db_cor');
                $err = census_manage(
                    $site,
                    $machine,
                    $uuid,
                    $isLegacy,
                    $db
                );
                $args['rval'] = $err;
                finish_perf_count(
                    'census_manage',
                    $wSec,
                    $wUsec,
                    $uSec,
                    $uUsec,
                    $sSec,
                    $sUsec,
                    $enabled
                );
            }

            if ($err == constAppNoErr) {
                start_perf_count(
                    $proc,
                    $wSec,
                    $wUsec,
                    $uSec,
                    $uUsec,
                    $sSec,
                    $sUsec,
                    $enabled
                );
                $proc($args);
                finish_perf_count(
                    $proc,
                    $wSec,
                    $wUsec,
                    $uSec,
                    $uUsec,
                    $sSec,
                    $sUsec,
                    $enabled
                );
                if ($enabled && ($args['rval'] == constErrServerTooBusy)) {
                    count_busy_procs($proc);
                }
                $good = 1;
            } else {
                logs::log(__FILE__, __LINE__, "census_manage returned $err for $proc from "
                    . $site . ":" . $machine . " with " . $uuid, 0);
                /* No longer an unknown procedure */
                $good = 1;
            }
        }
    } else if (isset($csrv[$proc])) {
        start_perf_count(
            $proc,
            $wSec,
            $wUsec,
            $uSec,
            $uUsec,
            $sSec,
            $sUsec,
            $enabled
        );
        $args['rval'] = PHP_HTPC_ServerCall(
            CUR,
            $GLOBALS["HTTP_RAW_POST_DATA"],
            $str
        );
        finish_perf_count(
            $proc,
            $wSec,
            $wUsec,
            $uSec,
            $uUsec,
            $sSec,
            $sUsec,
            $enabled
        );
        echo $str;
        $args['oxml'] = 0;
        $args['olog'] = 0;
        $good = 1;
    }
    if (!$good) {
        $self = server_var('PHP_SELF');
        $text = "$self: unknown $proc($nump)";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }
}
