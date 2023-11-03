<?php

/*
Revision history:

Date        Who     What
----        ---     ----
15-Aug-02   EWB     Update filenames for php4
19-Sep-02   EWB     Don't include common.php3
20-Sep-02   EWB     More factoring ... lib-db.php3
27-Sep-02   EWB     Time function.
07-Oct-02   NL      exec_QUERYUPDATE (exec.php)
13-Nov-02   EWB     added errs.php
25-Nov-02   EWB     Added census logging.
20-Jan-03   AAM     Added consts.php. (l-cnst.php)
21-Jan-03   EWB     Disable display_errors.
10-Feb-03   EWB     Use the sandbox libraries.
28-May-03   MMK     Added include l-rpcs.php
30-Nov-03   AAM     Added performance logging entries.
10-Dec-03   AAM     Changed call to build_args to include procedure name.
31-May-04   BTE     Added inst.php.
 9-Jul-04   EWB     l-rlib.php
03-Sep-05   BTE     Replaced errs.php with l-errs.php.
13-Sep-05   BTE     Added include for l-dsyn.php.
10-Nov-05   BTE     Added an include.
14-Dec-05   AAM     Added include of l-pcnt.php.
26-Jan-06   BTE     Added include for l-core.php.
06-May-06   BTE     Added includes to support groups_init.
20-Sep-06   BTE     Added include for l-pdrt.php.
26-Jan-07   BTE     Bug 4015: Add TX performance counters.



*/

/*
    |  We want this to be a tiny file that just includes other files
    |  to do all the work.  That's so that we can debug the functions
    |  in the include files by running them interactively.
    */

/* Turn on output buffering so that we can track the outgoing data. */
ob_start();

$usec = microtime();

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

include_once('../lib/l-perf.php');
include_once('../lib/l-cnst.php');
include_once('../lib/l-db.php');
include_once('../lib/l-rpcs.php');
include_once('../lib/l-rlib.php');
include_once('../lib/l-errs.php');
include_once('../lib/l-dsyn.php');
include_once('../lib/l-gcfg.php');
include_once('../lib/l-pcnt.php');
include_once('../lib/l-core.php');
include_once('../lib/l-gdrt.php');
include_once('../lib/l-grps.php');
include_once('../lib/l-pdrt.php');
include_once('../lib/l-svbt.php');
include_once('server.php');
include_once('census.php');
include_once('event.php');
include_once('publish.php');
include_once('asset.php');
include_once('time.php');
include_once('exec.php');
include_once('install.php');
include_once('provis.php');
include_once('inst.php');

logs::tag("RPC_POST", ["request" => $_REQUEST, "post" => $_POST, "get" => $_GET]);

if (function_exists('ini_set')) {
    ini_set('display_errors', '0');
}

$procName = posted('ProcName');
$logRPCTimes = false;
if (pfTimeRPCs) {
    if ($procName != 'ELOG_LogEntry') {
        $logRPCTimes = true;
        $ipAddr = $_SERVER['REMOTE_ADDR'];
        logs::log(__FILE__, __LINE__, "timing: rpc $ipAddr $procName started", 0);
    }
}

$dbg = posted('debug_rpc');
$sql = posted('debug_sql');
$prs = posted('debug_prs');
$debug       = ($dbg == 'yes') ? 1 : 0;
$debug_parse = ($prs == 'yes') ? 1 : 0;
$debug_sql   = ($sql == 'yes') ? 1 : 0;

$args = build_args($usec, $procName);

if ($logRPCTimes) {
    $secs = microtime_show(microtime_diff($usec, microtime()));
    logs::log(__FILE__, __LINE__, "timing: rpc $ipAddr $procName build_args after $secs", 0);
}

dispatch($args);

if ($args['oxml']) {
    $args['xml'] = hpc_return($args);
    echo $args['xml'];
}

if ($logRPCTimes) {
    $secs = microtime_show(microtime_diff($usec, microtime()));
    logs::log(__FILE__, __LINE__, "timing: rpc $ipAddr $procName finished after $secs", 0);
}


/* We have to wait until all output is echoed to stdout before counting
        transmit bytes. */
finish_tx_bytes($procName);

$res = ob_get_contents();

logs::tag("RPC_END", ["request" => $_REQUEST,  "get" => $_GET, "res" => $res]);

ob_end_flush();

/* Don't delete the newline from the end of this file */
echo "\n\n"; // I do not know why.  :-(
