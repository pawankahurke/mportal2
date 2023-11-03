<?php

/*
Revision history:

Date        Who     What
----        ---     ----
02-Aug-02   EWB     Client revision level is crevl, not revv
09-Aug-02   EWB     Fixed a problem in client update calculation
13-Aug-02   EWB     Always log mysql failures
14-Aug-02   EWB     Adding client version to Revisions table
22-Aug-02   EWB     VARS_QueryRevl checks for browser interface
22-Aug-02   EWB     SCNF_PublishHost created
23-Aug-02   EWB     Changed definitions of config database.
04-Sep-02   EWB     Added variable scoping.
05-Sep-02   EWB     Scrip variables define scoping.
05-Sep-02   EWB     Fixed state change bug.
05-Sep-02   EWB     Major change ... allow for non-unique variable names.
05-Sep-02   EWB     Turned off output log for the moment.
06-Sep-02   EWB     Non-unique variable names for SCNF_PublishHost
09-Sep-02   EWB     Repent from the evil of magic quotes.
09-Sep-02   EWB     Store the real values for variables.
12-Nov-02   EWB     double_decode on alist values.
12-Nov-02   EWB     Make a log entry for client publish.
13-Nov-02   EWB     Appropriate error code for unavailable database.
22-Nov-02   EWB     Update update module to learn of new host or site.
02-Dec-02   EWB     Don't modify update database when introducing new machine.
19-Dec-02   EWB     set_time_limit(0) for config logging.
20-Dec-02   EWB     Don't log errors for Scrips, Variables
20-Jan-03   AAM     Brobdignagian change for new remote configuration update
                    scheme.  This was essentially a rewrite.
21-Jan-03   EWB     Always initialize checksum.
22-Jan-03   AAM     Fixed problem where states were not getting set up right
                    on first client contact (thanks to EWB for finding this
                    problem).
06-Feb-03   MMK     Made ApplyPackage put into the package those variables
                    not in the incoming package from the client.
30-Jan-03   EWB     It's Ok to call command() again.
30-Jan-03   EWB     Minimal quotes.
30-Jan-03   EWB     Record config logging (uload,written,dload)
31-Jan-03   EWB     Record machine checkins as well.
03-Feb-03   EWB     Checkins are a bit too often ...
08-Feb-03   MMK (by AAM) Extended VARS_ApplyPackage to send back variables that
                    are on the server but not on the client, also to send back
                    the list of machines needing local variable updates if that
                    list came up from the client.
12-Feb-03   MMK     In CheckSync, check if there are machines (not including
                            the one updating) that need to be synchronized. In
                            ApplyPackage, only send back variables in the package if
                            there were variables in the incoming one.
14-Feb-03   MMK     Commented in error logging in ApplyPackage.
17-Feb-03   AAM     Although it shouldn't happen on customer servers, there were
                    some cases at Amherst and Newton where the "itype" field
                    wasn't getting set in Globals and Locals.  This was if some
                    data had been logged into the old database and then the
                    update.php3 script was used to add the "itype" fields.  They
                    just got set to the default value of zero.  This was causing
                    the checksum to always be computed differently for the
                    server and client, forcing a non-functional update.  So now
                    if the types are different, the server just inherits them
                    from the client.
24-Jun-03   EWB     Log machine checkins, at least when somethings changed.
05-Sep-03   MMK     Added two new fields to define_var to handle different kinds of
                    passwords and dangerous variables.
 8-Sep-03   EWB     Don't reference undefined fields.
15-Sep-03   MMK     Fixed unquoted value when inserting into the Locals table in
                    AddVarsNotOnClient.
31-Oct-03   EWB     Pass large objects by reference whenever feasable.
 5-Nov-03   EWB     Debug logging.
13-Nov-03   EWB     Commented out the checksum debug messages.  It makes the
                    logfile too big ... but we'll keep them around for later.
21-Nov-03   MMK     Added kludge to fix update of variable that has only
                    spaces in its name.
30-Nov-03   AAM     Added configurable config call disable.
10-Dec-03   AAM     Added provisional config logging, mimicing that of asset
                    logging.  This involved adding config_timed_out, adding
                    count_config_updates, adding claim_config_machine, adding
                    unclaim_config_machine, factoring config_check_sync out
                    of VARS_CheckSync, updating database access in
                    config_check_sync to manage the "provisional" field, and
                    making VARS_CheckSync handle the "too many active" case.
                    I also improved the logging in config_check_sync a bit and
                    made it mimic the asset case.  In parallel, factored
                    config_data out of VARS_ApplyPackage.  Made
                    VARS_ApplyPackage handle the "too many active" case, and
                    use get_alist_param to get its potentially large parameter.
                    In addition, made GetChecksums know about the client bug
                    in versions prior to 1.006.0836, and adapt the sort order
                    accordingly to prevent the "never-ending-update" problem.
26-Dec-03   AAM     Put in a workaround for a bug in mysql that was using the
                    wrong ordering for variables in the checksum where the
                    variable name contained a grave accent (`).  This variable
                    name shouldn't have existed but somehow got into the globals
                    at CCHS (bug in the client?) so it was causing endless
                    updates from that site.
14-Jan-04   EWB     A few debug messages I wanted to keep around just in case.
                    Just uncomment them whenever needed.
10-Feb-04   EWB     build_scrips logs failure.
11-Feb-04   EWB     sets the value of uuid if it is not yet known.
18-Mar-04   EWB     add debug option to log no action calls.
18-Mar-04   EWB     works again:  grep 'config: host' /var/log/php/php.log
19-Mar-04   EWB     record query as query, stop logging no action calls.
22-Mar-04   EWB     config action is query, logging or log/query
22-Mar-04   EWB     migrating machine problem.
 7-Apr-04   EWB     reports number of concurrent connections.
21-Apr-04   EWB     populate site/host checksum cache.
22-Apr-04   EWB     config logging invalidates checksum cache.
23-Apr-04   EWB     more effecient cache update.
26-Apr-04   EWB     handles unusual case of uuid mismatch.
26-Apr-04   EWB     using checksum cache.
27-Apr-04   EWB     config_data independantly tracks global/local writes.
27-Apr-04   EWB     AddVarsNotOnClient correctly tracks writes/downloads.
27-Apr-04   EWB     dirty_cache independantly invalidates global/local cache.
23-May-04   EWB     comment out the GetMachinesNeedSync query.
24-May-04   EWB     GetMachinesNeedSync controlled by a server option.
25-May-04   EWB     Everybody updates the census.
 3-Jun-04   EWB     Fixed a problem with adding a uuid to an existing revisions record.
 8-Jun-04   EWB     "legacy" machines should ignore the checksum cache.
 9-Jun-04   EWB     Tracking changes.
 2-Aug-04   EWB     count_config_updates: always be prepared.
16-Aug-04   EWB     report pid of config logging process.
25-Mar-05   EWB     new census logging methods
25-May-05   EWB     builds new site / host machine groups.
 1-Jun-05   EWB     legacy csum cache
20-Jun-05   EWB     allows changing state of invalid variables
10-Aug-05   AAM     Fixed bug 2805.
12-Sep-05   BTE     Added checksum invalidation code.
20-Sep-05   BTE     Added proper table specification in kill_config_host.
12-Oct-05   BTE     Changed references from gconfig to core.
13-Oct-05   BTE     Bug fix from prior checkin: make sure $db points to core.
24-Oct-05   BTE     Handle ValueMap.revl when changing mgroupid.
03-Nov-05   BTE     Fixed the sort order of variables by specifying a COLLATION
                    to apply to the SELECT, don't normalize strings before
                    storing them in the database, changed VarValues.*
                    statements to actual column names, minor bugfixes in
                    AddVarsNotOnClient.
05-Nov-05   BTE     Specify the operation when calling checksum invalidation
                    code.
10-Nov-05   BTE     Updated to use the new census_manage function, cleaned up
                    duplicated code.
09-Nov-05   AAM     Added debug output to /tmp directory to facilitate debugging
                    checksum problems.
11-Nov-05   AAM     Implemented mirror of sorting function for checksums to
                    finally completely eliminate order mismatch problems.
14-Dec-05   BTE     Removed the unused function find_value.
21-Dec-05   AAM     Fixed bug 2987:  machines were failing to sync with the
                    variable in Scrip 225 having a name with just a space.  Also
                    added some additional helpful output in the PHP log.
23-Dec-05   AAM     More sync issues: changed CompareValues to do the equality
                    comparison ignoring newline differences.  Without this
                    change, the server was always sending back values that
                    would never actually get changed on the client.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
27-Jan-06   BTE     Bug 3080: Compute mgroupuniq using UUID instead of mcatuniq
                    and name.
24-Feb-06   BTE     Fixed an incorrect SQL statement.
13-Mar-06   BTE     Bug 3199: Remove unused core database columns.
15-Mar-06   BTE     Bug 3206: Publish.php is getting mysql errors.
18-Mar-06   AAM     Bug 3214: default override_sites=1, config_search=0.
19-Mar-06   AAM     Bug 3214, continued: put default config_search back to 1.
19-Mar-06   AAM     Bug 3214, still more: default config_search=0.
20-Mar-06   AAM     Fixed the TempValues table to have mediumtext for variable
                    values.  Also, moved the config_debug code to the right
                    place (which was needed to find that first problem).  There
                    was no bug entry for this.
22-Mar-06   AAM     Clean up synchronization and update of state values and
                    revision levels for legacy client.  This fixes many, if not
                    all, of the sync problems of 2.1 clients and 4.3 servers.
06-May-06   BTE     Bug 2913: The "backdesk" system at pvmc is not correctly
                    synchronizing.
06-May-06   BTE     Minor change for another sync issue (part of bug 2913).
15-May-06   BTE     Another minor change for another sync issue (part of bug
                    2913).
15-May-06   BTE     Bug 2967: Handle duplicate UUIDs and remove migration for 
                    pre-2.2 clients.
16-May-06   BTE     More bug 2913.
19-May-06   BTE     Fixed part of bug 2967: start with a local revision level
                    of 2 to override the client's local revision of 1 for the
                    UUID timestamp.  Bug 3244: 439 errors in PHP log.
23-May-06   BTE     Bug 3290: Config Module: Client syncing with server caused
                    variable changes.
26-May-06   BTE     Bug 3386: Group management wizard change. More of bug 3290.
11-Jun-06   BTE     Bug 3465: InvalidVars can contain real variables for
                    clients.
06-Jul-06   BTE     Bug 3517: Variable sync issues with InvalidVars.
07-Jul-06   BTE     Bug 3525: Add search_config timeout option.
19-Jul-06   BTE     Bug 3239: 913 errors on CI server.  Bug 3013: Server should
                    be able to tell client which other clients need to sync.
31-Jul-06   BTE     Bug 3560: Sites: Configuration - Local change on 2.1 client
                    doesn't reflect on server.
23-Sep-06   BTE     Bug 3674: Change Scrip 43 new UUID semaphore to be local-
                    only.
24-Nov-06   AAM     Bug 3896: added banned_vars.  The way this works is that it
                    is a colon-separated list of variable names that will not be
                    added to InvalidVars.  So, you set up the illegal variable
                    names here and also in the "banned variables" in Scrip 43.
                    Then, however, you still have to use mysql to delete the
                    variables from InvalidVars on the server the first time.
04-Jun-07   BTE     Added calls to PHP_REPF_UpdateDynamicList.

*/


/* Module constants */

define('constUpdateNone',       0);
define('constUpdateToHere',     1);
define('constUpdateToThere',    2);

define('constSrvVarVal',        'v');
define('constSrvVarRev',        'r');
define('constSrvVarSRev',       'e');
define('constSrvVarState',      's');
define('constSrvVarType',       't');
define('constSrvVarVid',        'i');

define('constGlobalChecksum',   'global');
define('constLocalChecksum',    'local');
define('constStateChecksum',    'state');
define('constGlobal',           0);
define('constLocal',            1);
define('constState',            2);

define('constGenUUIDTimeoutVar',    'Scrip43GenUUIDVar');

define('constMissingSiteVar',   'site');
define('constMissingHostVar',   'host');

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";


/*
    |  We do not log errors for Scrips because of
    |  the unique index.
    */

function define_scrip($vers, $scop, $name, $db)
{
    $qn  = safe_addslashes($name);
    $qv  = safe_addslashes($vers);
    $sql = "insert into Scrips set\n"
        . " name = '$qn',\n"
        . " vers = '$qv',\n"
        . " num = $scop";
    command($sql, $db);
    $lastid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
    $err = PHP_DSYN_InvalidateRow(
        CUR,
        (int)$lastid,
        "scripid",
        constDataSetGConfigScrips,
        constOperationInsert
    );
    if ($err != constAppNoErr) {
        logs::log(
            __FILE__,
            __LINE__,
            "define_scrip: PHP_DSYN_InvalidateRow returned " . $err,
            0
        );
    }
}


function find_variables($db)
{
    $set = array();
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Variables";
    $tmp = find_many($sql, $db);
    reset($tmp);
    foreach ($tmp as $key => $row) {
        $scop = $row['scop'];
        $name = $row['name'];
        $set[$scop][$name] = $row['varid'];
    }
    return $set;
}


function find_semaphores($db)
{
    $set = array();
    $sem = constVblTypeSemaphore;
    $sql = "select varid, scop,\n"
        . " concat(name,'Local') as name from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Variables\n"
        . " where itype = $sem";
    $tmp = find_many($sql, $db);
    reset($tmp);
    foreach ($tmp as $key => $row) {
        $scop = $row['scop'];
        $name = $row['name'];
        $set[$scop][$name] = $row['varid'];
    }
    return $set;
}


function legal_name($name)
{
    if (!is_string($name)) {
        debug_note('not a string type');
        return false;
    }
    $len = strlen($name);
    if (($len < 1) || ($len > 50)) {
        debug_note("name length ($len) invalid.");
        return false;
    }
    $ch = $name[0];
    $good = ((('a' <= $ch) && ($ch <= 'z')) ||
        (('A' <= $ch) && ($ch <= 'Z')));
    if (!$good) {
        debug_note("$name does not begin correctly");
        return false;
    }
    for ($i = 0; $i < $len; $i++) {
        $ch = $name[$i];
        $good = ((('a' <= $ch) && ($ch <= 'z')) ||
            (('A' <= $ch) && ($ch <= 'Z')) ||
            (('0' <= $ch) && ($ch <= '9')) ||
            (($ch  == '_')));
        if (!$good) {
            debug_note("weird character $ch at position $i");
            return false;
        }
    }
    return true;
}


/*
    |  Both nanoheal and hollis have a single variable
    |  of type 1 (constVblTypeDateTime) however neither
    |  of them supply any method to configure it.
    |
    |  S00202DateTime:202
    */

function proc_map($proc)
{
    switch ($proc) {
        case 'text':
            return constVblTypeInteger;
        case 'checkbox':
            return constVblTypeBoolean;
        case 'execute':
            return constVblTypeSemaphore;
        case 'password':
            return constVblTypeString;
        case 'textarea':
            return constVblTypeString;
        default:
            return -1;
    }
}

function config_map($proc)
{
    switch ($proc) {
        case 'text':
            return constConfigNormal;
        case 'checkbox':
            return constConfigNormal;
        case 'execute':
            return constConfigNormal;
        case 'password':
            return constConfigPassword;
        case 'textarea':
            return constConfigNormal;
        default:
            return constConfigIllegal;
    }
}


/*
    |  We do not log errors for Variables because of
    |  the unique index.
    |
    |     core.Variables.cksum = '';
    |     core.Variables.deleted = 0;
    */

function define_var($vers, $name, $desc, $stat, $proc, $scop, $pwsc, $dngr, $db)
{
    $var = find_var($name, $scop, $db);
    if (!$var) {
        $type = proc_map($proc);
        if ((0 <= $type) && (legal_name($name))) {
            $sql = "insert into Variables set\n"
                . " itype = $type,\n"
                . " name = '$name',\n"
                . " scop = $scop,\n"
                . " varuniq = md5(concat('$name',',',$scop)),\n"
                . " varscopuniq = md5($scop),\n"
                . " varnameuniq = md5('$name')";
            command($sql, $db);
            $lastid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
            $err = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$lastid,
                "varid",
                constDataSetGConfigVariables,
                constOperationInsert
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "define_var: PHP_DSYN_InvalidateRow returned "
                    . $err, 0);
            }
            $var = find_var($name, $scop, $db);
        }
    }
    if ($var) {
        $sok = ($stat == constVarConfStateLocalOnly) ? 0 : 1;
        $vid = $var['varid'];
        $cfg = config_map($proc);

        $sql = "select md5(concat('" . $vers . "',varuniq)) AS "
            . "varversuniq,varnameuniq,varscopuniq,varuniq from Variables "
            . "where varid=$vid";
        $row = find_one($sql, $db);
        if ($row) {
            $sql = "insert into VarVersions (vers,pwsc,dngr,config,"
                . "grpsite,grpmach,varversuniq,descval,varnameuniq,"
                . "varscopuniq,varuniq) VALUES ('" . $vers . "', $pwsc, "
                . "$dngr, $cfg, $sok, 1, '" . $row['varversuniq'] . "', '"
                . $desc . "', '" . $row['varnameuniq'] . "', '"
                . $row['varscopuniq'] . "', '" . $row['varuniq'] . "')";

            $res = command($sql, $db);
            if (affected($res, $db)) {
                $lastid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
                $err = PHP_DSYN_InvalidateRow(
                    CUR,
                    (int)$lastid,
                    "varversid",
                    constDataSetGConfigVarVersions,
                    constOperationInsert
                );
                if ($err != constAppNoErr) {
                    logs::log(
                        __FILE__,
                        __LINE__,
                        "define_var: PHP_DSYN_InvalidateRow2 "
                            . "returned " . $err . " for varversid $lastid",
                        0
                    );
                }
            }
        } else {
            logs::log(__FILE__, __LINE__, "define_var: no such variable $vid", 0);
        }
    }
}


/*
    |  This is used by both config & provisioning.
    */

function find_revl_uuid($uuid, $db)
{
    $row = array();
    if ($uuid) {
        $qu  = safe_addslashes($uuid);
        $sql = "select R.* from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
            . " where R.censusid = C.id\n"
            . " and C.uuid = '$qu'";
        $row = find_one($sql, $db);
    }
    return $row;
}


function find_revl_host($site, $host, $db)
{
    $row = array();
    if (($site) && ($host)) {
        $qs  = safe_addslashes($site);
        $qh  = safe_addslashes($host);
        $sql = "select R.* from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
            . " where R.censusid = C.id\n"
            . " and C.host = '$qh'\n"
            . " and C.site = '$qs'";
        $row = find_one($sql, $db);
    }
    return $row;
}


function find_revl_mid($mid, $db)
{
    $row = array();
    if ($mid) {
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions\n"
            . " where censusid = $mid";
        $row = find_one($sql, $db);
    }
    return $row;
}


function identity(&$site, &$host, &$uuid, &$rval, &$logText, &$more)
{
    if (!$site) {
        $rval = constErrServerTooBusy;
        $logText = "empty site name (machine:$host)";
        $more = false;
    }
    if (!$host) {
        $rval = constErrServerTooBusy;
        $logText = "empty machine name (site:$site)";
        $more = false;
    }

    if (!$uuid) {
        $name = "$site:$host";
        $uuid = md5($name);
    }
}


function find_mgrp_gid_publish($gid, $db)
{
    $row = array();
    if ($gid) {
        $sql = "select G.*, C.mcatid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C\n"
            . " where mgroupid = $gid\n"
            . " and G.mcatuniq = C.mcatuniq";
        $row = find_one($sql, $db);
    }
    return $row;
}


/*
    |  Adds a new machine to an existing group
    */

function build_gmap($hid, $tid, $gid, $db)
{
    $xid = 0;
    if (($gid) && ($tid) && ($hid)) {
        $sql = CORE_CreateMachineGroupMap($hid, $tid, $gid);
        $res = command($sql, $db);
        if (affected($res, $db)) {
            $xid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
            $err = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$xid,
                "mgmapid",
                constDataSetCoreMachineGroupMap,
                constOperationInsert
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "build_gmap: PHP_DSYN_InvalidateRow "
                    . "returned " . $err, 0);
            }
        }
        if (!$xid) {
            $stat = "h:$hid,t:$tid,g:$gid";
            $text = "config: build_gmap failure ($stat)";
            debug_note($text);
            logs::log(__FILE__, __LINE__, $text, 0);
        }
    }
    return $xid;
}


/*
    |  Builds a site or host machine group for config.
    |  We leave many fields default:
    |
    |     core.MachineGroups.human = 0;
    |     core.MachineGroups.username = '';
    |     core.MachineGroups.eventspan = 0;
    |     core.MachineGroups.eventquery = 0;
    |     core.MachineGroups.assetquery = 0;
    */

function build_mgrp($hid, $tid, $name, $uuid, $db)
{
    $gid = 0;
    $xid = 0;
    $row = array();
    if (($hid) && ($tid)) {
        $now = time();
        $typ = constStyleBuiltin;
        $qt  = safe_addslashes('Built-In');
        $qn  = safe_addslashes($name);
        if ($uuid) {
            $mgroupuniq = "md5('$uuid')";
        } else {
            $mgroupuniq = "md5(concat(mcatuniq,',','$qn'))";
        }
        $sql = "insert into\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups (name,global,style,created,boolstring,"
            . "mgroupuniq,mcatuniq) select '$qn',1,$typ,$now,'$qt',"
            . "$mgroupuniq,mcatuniq from "
            . $GLOBALS['PREFIX'] . "core.MachineCategories where mcatid=$tid";
        $res = command($sql, $db);
        if (affected($res, $db)) {
            $gid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
            $err = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$gid,
                "mgroupid",
                constDataSetCoreMachineGroups,
                constOperationInsert
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "build_mgrp: PHP_DSYN_InvalidateRow " .
                    "returned " . $err, 0);
            }
            PHP_REPF_UpdateDynamicList(CUR, constJavaListEventMgrpInclude);
            PHP_REPF_UpdateDynamicList(CUR, constJavaListEventMgrpExclude);
        }
        if (!$gid) {
            $stat = "h:$hid,t:$tid";
            $text = "config: build_mgrp failure ($stat) $name";
            logs::log(__FILE__, __LINE__, $text, 0);
            debug_note($text);
        }
    }
    if ($gid) {
        $row = find_mgrp_gid_publish($gid, $db);
    }
    return $row;
}


function build_sgrp($hid, $site, $db)
{
    $row = array();
    $cat = find_mcat_name(constCatSite, $db);
    if ($cat) {
        $tid = $cat['mcatid'];
        $row = build_mgrp($hid, $tid, $site, NULL, $db);
    }
    if ($row) {
        $gid = $row['mgroupid'];
        $stat = "t:$tid,g:$gid,h:$hid";
        $text = "groups: $site ($stat) created";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }
    return $row;
}


/*
    |  Create a new machine group for this new machine.
    |
    |  Add this new machine to both the site group and
    |  the new group we just created.
    */

function build_hgrp($hid, $site, $host, $uuid, $db)
{
    $hxid = 0;
    $sxid = 0;
    $mgrp = array();
    $hgrp = array();
    $sgrp = array();
    $hcat = find_mcat_name(constCatMachine, $db);
    if ($hcat) {
        $htid = $hcat['mcatid'];
        $name = "$site:$host";
        $hgrp = build_mgrp($hid, $htid, $name, $uuid, $db);
        $sgrp = find_build_site_mgrp($hid, $site, $db);
    }
    if (($hgrp) && ($sgrp)) {
        $htid = $hgrp['mcatid'];
        $stid = $sgrp['mcatid'];
        $hgid = $hgrp['mgroupid'];
        $sgid = $sgrp['mgroupid'];
        $hxid = build_gmap($hid, $htid, $hgid, $db);
        $sxid = build_gmap($hid, $stid, $sgid, $db);
    }
    if (($sxid) && ($hxid)) {
        $mgrp = $hgrp;
        $stat = "t:$htid,g:$hgid,h:$hid";
        $text = "groups: $host at $site ($stat) created";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }
    return $mgrp;
}


function find_build_site_mgrp($hid, $site, $db)
{
    $row = array();
    $tag = constStyleBuiltin;
    $cat = safe_addslashes(constCatSite);
    $qs  = safe_addslashes($site);
    $sql = "select G.*, C.mcatid from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C\n"
        . " where C.category = '$cat'\n"
        . " and G.mcatuniq = C.mcatuniq\n"
        . " and G.global = 1\n"
        . " and G.human = 0\n"
        . " and G.style = $tag\n"
        . " and G.name = '$qs'";
    $row = find_one($sql, $db);
    if (!$row) {
        $row = build_sgrp($hid, $site, $db);
    }
    return $row;
}


function find_host_build_mgrp($mid, $site, $host, $uuid, $db)
{
    $tag = constStyleBuiltin;
    $cat = safe_addslashes(constCatMachine);
    $qn  = safe_addslashes("$site:$host");
    $sql = "select G.*, C.mcatid from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C\n"
        . " where G.mcatuniq = C.mcatuniq\n"
        . " and C.category = '$cat'\n"
        . " and G.name = '$qn'\n"
        . " and G.style = $tag\n"
        . " and G.human = 0\n"
        . " and G.global = 1";
    $row = find_one($sql, $db);

    /*
        |  The automatic build process does not handle renaming a machine
        |  group if the machine decides to change it's name ... the
        |  problem is that on a large server, detecting this kind of
        |  name change would be very expensive.
        |
        |  So, instead of that, we'll just take care of the problem here.
        |  Note that this will work even if the machine has moved to
        |  a different site.
        */

    if (!$row) {
        $xxx = "select G.mgroupid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as A\n"
            . " where M.censusuniq = A.censusuniq\n"
            . " and A.id = $mid\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and M.mcatuniq = C.mcatuniq\n"
            . " and M.mcatuniq = G.mcatuniq\n"
            . " and C.category = '$cat'";
        $grp = find_one($xxx, $db);
        if ($grp) {
            $gid = $grp['mgroupid'];
            $xxx = "update " . $GLOBALS['PREFIX'] . "core.MachineGroups\n"
                . " set name = '$qn',\n"
                . " revlname = revlname + 1\n"
                . " where mgroupid = $gid";
            $row = find_mgrp_gid_publish($gid, $db);
        }
    }
    if (!$row) {
        $row = build_hgrp($mid, $site, $host, $uuid, $db);
    }
    if ($row) {
        $row['site'] = $site;
        $row['host'] = $host;
    }
    return $row;
}


function find_site_cid($site, $db)
{
    $cid = 0;
    if ($site) {
        $qs  = safe_addslashes($site);
        $sql = "select id from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers\n"
            . " where customer = '$qs'\n"
            . " and username = ''";
        $row = find_one($sql, $db);
        $cid = ($row) ? $row['id'] : 0;
    }
    return $cid;
}


/*
    |  Note we search for the site group first.
    |  This is important, because it is the
    |  host search that populates the group.
    */

function find_groups($row, &$mid, &$cid, &$sgid, &$hgid, $db)
{
    if ($row) {
        $mid  = $row['id'];
        $site = $row['site'];
        $host = $row['host'];
        $uuid = $row['uuid'];
        $sgrp = find_build_site_mgrp($mid, $site, $db);
        $hgrp = find_host_build_mgrp($mid, $site, $host, $uuid, $db);
        $cid  = find_site_cid($site, $db);
        $sgid = ($sgrp) ? $sgrp['mgroupid'] : 0;
        $hgid = ($hgrp) ? $hgrp['mgroupid'] : 0;
    }
}



function create_revision($mid, $site, $host, $vers, $time, $db)
{
    $qv  = safe_addslashes($vers);
    $sql = "insert into Revisions set\n"
        . " censusid = $mid,\n"
        . " vers = '$qv',\n"
        . " ctime = $time,\n"
        . " stime = $time,\n"
        . " provisional = $time";
    $res = command($sql, $db);
    $num = affected($res, $db);
    if ($num) {
        $txt = "config: $host introduction ($vers) at $site.";
        logs::log(__FILE__, __LINE__, $txt, 0);
        debug_note($txt);
    }
    return $num;
}


/*
    |  touch_revision
    |
    |  There are several possible cases here, and we'll address them in
    |  decreasing order of probability.
    |
    |  1. We're updating a revisions record which already exists.  This
    |     is the normal case and should be handled as fast as possible,
    |     preferably as a same-size update.
    |
    |  2. The record doesn't exist yet.  Either it's a totally new machine,
    |     or someone has expunged it.  This will happen from time to time,
    |     but not very often.
    */

function touch_revision($mid, $site, $host, $vers, $time, $db)
{
    $row = array();
    $qv  = safe_addslashes($vers);
    $sql = "update Revisions set\n"
        . " vers = '$qv',\n"
        . " ctime = $time\n"
        . " where censusid = $mid";
    $res = command($sql, $db);
    $num = affected($res, $db);
    if ($num) {
        debug_note("existing revision record updated ...");
    }
    $row = find_revl_mid($mid, $db);
    if (!$row) {
        debug_note("looks like a new machine ...");
        if (create_revision($mid, $site, $host, $vers, $time, $db)) {
            $row = find_revl_mid($mid, $db);
        }
    }
    return $row;
}


function update_order($scop, $vers, $vars, $db)
{
    $num = 0;
    $set = explode(',', $vars);
    if ($set) {
        $qv = safe_addslashes($vers);
        reset($set);
        foreach ($set as $key => $code) {
            $pos = strpos($code, ':', $code);
            if ($pos > 0) {
                $name = substr($code, 0, $pos);
                $row  = find_var($name, $scop, $db);
                if ($row) {
                    $ord = $key + 1;
                    $vid = $row['varid'];

                    $sql = "select varversid from VarVersions left join "
                        . "Variables on (VarVersions.varuniq="
                        . "Variables.varuniq) where"
                        . " vers='$qv' and varid=$vid and configorder=0";
                    $set2 = DSYN_DeleteSet(
                        $sql,
                        constDataSetGConfigVarVersions,
                        "varversid",
                        "update_order",
                        0,
                        0,
                        constOperationDelete,
                        $db
                    );

                    if ($set2) {
                        $sql = "update VarVersions left join Variables on "
                            . "(VarVersions.varuniq=Variables.varuniq) set"
                            . "\n configorder = $ord\n"
                            . " where vers = '$qv'\n"
                            . " and varid = $vid\n"
                            . " and configorder = 0";
                        $res = command($sql, $db);
                        $xxx = affected($res, $db);

                        DSYN_UpdateSet(
                            $set2,
                            constDataSetGConfigVarVersions,
                            "varversid",
                            $db
                        );
                        $num = $num + $xxx;
                    }
                }
            }
        }
    }
}


/*
    |  Decodes the browser interface information prepared on the
    |  client by SCNF_DescribeList.
    |
    |   i -- scrip number
    |   n -- scrip name
    |   v -- scrip variables
    |
    |   a -- variable name
    |   o -- variable scope
    |   d -- variable description
    |   s -- variable status (0: global, 1: local, 2: local only)
    |   p -- variable display procedure
    |   c -- variable password security (0: none, 1: cleartext, 2: hashed)
    |   g -- variable dangerous attribute
    */

function build_scrips($vers, $list, $db)
{
    $set = array();
    if ($list) {
        reset($list);
        foreach ($list as $key => $data) {
            if (isset($data['vi'])) {
                $scop = $data['vi'];
                $name = $data['vn'];
                $vars = $data['vv'];

                $name = double_decode($name);
                $vars = double_decode($vars);
                define_scrip($vers, $scop, $name, $db);
                $set[$scop] = $vars;
            } else {
                $name = $data['va'];
                $desc = $data['vd'];
                $stat = $data['vs'];
                $proc = $data['vp'];
                $scop = $data['vo'];

                // older clients don't send these
                $pwsc = (isset($data['vc'])) ? $data['vc'] : constPasswordSecVarDefault;
                $dngr = (isset($data['vg'])) ? $data['vg'] : 0;

                $desc = double_decode($desc);
                $name = double_decode($name);
                define_var($vers, $name, $desc, $stat, $proc, $scop, $pwsc, $dngr, $db);
            }
        }
    } else {
        $text = "config: cannot build scrip/variables for $vers";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }

    if ($set) {
        reset($set);
        foreach ($set as $scop => $vars) {
            update_order($scop, $vers, $vars, $db);
        }
    }
}



/*
    |  We want to find out if we have the browser database
    |  for this client version.  We just check the Scrips
    |  table, which should be ok since we construct them
    |  all at the same time.
    */

function have_vers($db, $vers)
{
    $good = 0;
    $sql = "select num from Scrips\n where vers='$vers'";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            $good = 1;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $good;
}


/* FixNameIfEmpty
    If the incoming name is an empty string, output a space, otherwise output
    the incoming name. This is to deal with a variable name that only has
    spaces. On the client there is no problem but mysql removes trailing spaces
    when inserting into VARCHAR fields. During a SELECT the result will be an
    empty string, but that will cause a traceback on the client because it
    can't handle empty names. So we kludge around by returning a single space.
    Note that this will cause checksums to mismatch if multiple variables are
    created that have only spaces in their names.
*/
function FixNameIfEmpty($sourceName)
{
    $l = strlen($sourceName);
    if ($l == 0) {
        $ret = " ";
        //     logs::log(__FILE__, __LINE__, 'config: blank name found, converting to space',0);
    } else {
        $ret = $sourceName;
    }
    return $ret;
}


/* config_timed_out
        Check to see if we have timed out doing a config update.  Return
        true if we have, and false if we haven't.  $time is when the operation
        started, $site and $machine identify the machine doing the operation,
        and $usec is the micro-time when the operation started.

        We hard-wire the timeout to 50 minutes.  It just needs to be
        somewhat less than the hard-wired 60 minutes in
        ount_config_updates.
    */

function config_timed_out(&$env)
{
    $time = $env['time'];
    $secs = time() - $time;
    $fail = ($secs > 3000);
    if ($fail) {
        $usec = $env['usec'];
        $host = $env['host'];
        $site = $env['site'];
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "config: $host -- timeout in $secs at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }
    return $fail;
}

/* ---------------------------------------------

    function debug_pid_text($text)
    {
        $name = sprintf('/tmp/%05d.log',getmypid());
        $file = @ fopen($name,'a');
        if ($file)
        {
            fwrite($file,$text);
            fclose($file);
        }
    }


    function debug_pid_csum(&$row,$show)
    {
        $name = $row[0];
        $type = $row[1];
        $revl = $row[2];
        $stat = $row[3];
        if ($show)
            $text = sprintf('%d %8d %d %s',$type,$revl,$stat,$name);
        else
            $text = sprintf('%d %8d %s',$type,$revl,$name);
        debug_pid_text("$text\n");
    }


    function debug_pid_init(&$env)
    {
        $site = $env['site'];
        $host = $env['host'];
        $vers = $env['vers'];
        $date = date('m/d/y H:i:s',$env['time']);
        debug_pid_text("[$date]: $host at $site ($vers)\n");
    }

------------------------------------------- */


/* Checksum sorting functions.  The row has the variable name in $row[0]
        and the Scrip number in $row[4].  The Scrip number comparison always
        counts unless they are equal, in which case the names are compared
        in a version-dependent way. */

/* This is the checksum sorting function for a client prior to 1.006.0836.
        The client does this function by calling stricmp, which simply
        lowercases both strings and compares them directly. */
function ChecksumCompareV1($row1, $row2)
{
    $scrip1 = intval($row1[4]);
    $scrip2 = intval($row2[4]);

    if ($scrip1 < $scrip2) {
        return -1;
    } else if ($scrip1 > $scrip2) {
        return 1;
    } else {
        return strcmp(
            bin2hex(strtolower($row1[0])),
            bin2hex(strtolower($row2[0]))
        );
    }
}


/* This is the checksum sorting function for a client 1.006.0836 or later.
        The client does this function by comparing character by character:  if
        both characters are alphabetic, it compares the lowercase verison to
        make them case insensitive.  Otherwise, it compares them directly.  It
        uses a signed comparison, so if the high bit is set in one, it comes
        out as being less than the other. */
function ChecksumCompareV2($row1, $row2)
{
    $scrip1 = intval($row1[4]);
    $scrip2 = intval($row2[4]);

    if ($scrip1 < $scrip2) {
        return -1;
    } else if ($scrip1 > $scrip2) {
        return 1;
    } else {
        $l1 = strlen($row1[0]);
        $l2 = strlen($row2[0]);

        /* These are arrays with one character for each char in the
                string.  In PHP5 you can use str_split instead. */
        $a1 = explode("\r\n", chunk_split($row1[0], 1));
        $a2 = explode("\r\n", chunk_split($row2[0], 1));

        /* Go through until we hit an end of one string or a difference. */
        $r = 2;
        for ($i = 0; $r == 2; $i = $i + 1) {
            if ($i == $l1) {
                /* End of first string. */
                if ($i == $l2) {
                    $r = 0;
                } else {
                    $r = -1;
                }
            } else if ($i == $l2) {
                /* End of second string. */
                $r = 1;
            } else {
                /* Compare the characters the same way the client does. */
                $c1 = $a1[$i];
                $c2 = $a2[$i];

                if (((($c1 >= 'a') && ($c1 <= 'z')) ||
                        (($c1 >= 'A') && ($c1 <= 'Z'))) &&
                    ((($c2 >= 'a') && ($c2 <= 'z')) ||
                        (($c2 >= 'A') && ($c2 <= 'Z')))
                ) {
                    /* If they are both alphabetic, compare them as
                            lowercase. */
                    $l1 = strtolower($c1);
                    $l2 = strtolower($c2);
                    if ($l1 < $l2) {
                        $r = -1;
                    } else if ($l1 > $l2) {
                        $r = 1;
                    }
                } else {
                    /* If one is not alphabetic, compare them directly. */
                    $o1 = ord($c1);
                    $o2 = ord($c2);
                    /* Convert them to 8-bit signed numbers. */
                    if ($o1 > 127) {
                        $o1 = $o1 - 256;
                    }
                    if ($o2 > 127) {
                        $o2 = $o2 - 256;
                    }
                    if ($o1 < $o2) {
                        $r = -1;
                    } else if ($o1 > $o2) {
                        $r = 1;
                    }
                }
            }
        }
        return $r;
    }
}




/* GetOneChecksum
    Calculate one of the checksums, given a query that will return the
    variable name, variable type, revision level, and value to be used in the
    checksum.  "$sql" is the query to run, and "$db" is the database on which
    to run it.  "$alwaysInt" means that the third (value) query result is
    always an integer, otherwise its type is inferred from the second (type)
    result.  If "$dbg" is set, write out the string used to form the checksum
    into "$filename"; we use this to debug checksum sync problems.  "$vers"
    is the version of the client checking in; this can affect the way that
    the checksum is generated.
*/
function GetOneChecksum($sql, $db, $alwaysInt, $filename, $dbg, $vers)
{
    /* Try to create the debug file, if needed. */
    if ($dbg) {
        $fhan = fopen('/tmp/' . $filename, 'w');
        if (!$fhan) {
            logs::log(__FILE__, __LINE__, 'config: failed to write /tmp/' . $filename, 0);
            $dbg = false;
        }
    }

    $checkSum = '';
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res) > 0) {
            /* We used to use mysql to order the result, and collect the
                    rows up in that order.  But, we had so many problems with
                    mismatches between the client order and server order that
                    we are now doing the sort with PHP and exactly copying
                    what the client code does.

                    The comparison method used for the sort on the client
                    changed going to version 1.006.0836, which is why there
                    are two different sort functions.
                */

            /* Get all the data. */
            $sorted = array();
            while ($row = mysqli_fetch_array($res)) {
                $sorted[] = $row;
            }

            /* Sort it. */
            if ($vers < '1.006.0836') {
                usort($sorted, 'ChecksumCompareV1');
            } else {
                usort($sorted, 'ChecksumCompareV2');
            }

            $str = '';
            reset($sorted);
            foreach ($sorted as $key => $row) {
                //                 debug_pid_csum($row,$alwaysInt);

                /* Add the canonical string: */
                /*  name. Here is a kludge to fix the case of a variable
                        whose name is just a single space. mysql will convert
                        it to an empty string during an insert because it
                        removes trailing spaces for VARCHAR fields. So if we
                        see an empty string, convert it to a space. */
                $canon = FixNameIfEmpty($row[0]);
                /*  type */
                $canon .= bin2hex(pack('N', intval($row[1])));
                /*  revision level */
                $canon .= bin2hex(pack('N', intval($row[2])));
                /*  value */
                if ($alwaysInt || ($row[1] != constVblTypeString)) {
                    $canon .= bin2hex(pack('N', intval($row[3])));
                } else {
                    $noCR = str_replace("\r\n", "\n", $row[3]);
                    $canon .= bin2hex($noCR);
                }

                $str .= $canon;

                if ($dbg) {
                    fwrite($fhan, $canon . "\n");
                }
            }

            /* The checksum is the MD5 hash of the concatenation of all of
                    the canonical strings. */
            $checkSum = md5($str);

            if ($dbg) {
                fclose($fhan);
            }
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $checkSum;
}

function debug_csum($txt)
{
    debug_note($txt);
    //      logs::log(__FILE__, __LINE__, $txt,0);
    //      debug_pid_text("$txt\n");
}


/* SameChecksums
    Compare the server checksums "sCheck" with the client checksums
    "cCheck".  There are 3 checksums in each array.  Return 1 if they
    all match, otherwise 0.
*/
function SameChecksums($machine, $site, $sCheck, $cCheck)
{
    $sGlobal = $sCheck[constGlobalChecksum];
    $cGlobal = $cCheck[constGlobalChecksum];
    $sLocal  = $sCheck[constLocalChecksum];
    $cLocal  = $cCheck[constLocalChecksum];
    $sState  = $sCheck[constStateChecksum];
    $cState  = $cCheck[constStateChecksum];

    $g = strcmp($sGlobal, $cGlobal);
    $l = strcmp($sLocal, $cLocal);
    $s = strcmp($sState, $cState);

    $msg = "config: $machine at $site";
    if (($g == 0) && ($l == 0) && ($s == 0)) {
        debug_csum("$msg: all checksums match");
        $result = 1;
    } else {
        if ($g) {
            debug_csum("$msg: (sg) $sGlobal");
            debug_csum("$msg: (cg) $cGlobal");
        }
        if ($l) {
            debug_csum("$msg: (sl) $sLocal");
            debug_csum("$msg: (cl) $cLocal");
        }
        if ($s) {
            debug_csum("$msg: (ss) $sState");
            debug_csum("$msg: (cs) $cState");
        }
        $result = 0;
    }
    return $result;
}


function make_temp($db)
{
    $def = 'not null default';
    $i11 = "int(11) $def 0";
    $v50 = "varchar(50) $def ''";
    $txt = "mediumtext binary $def ''";
    $sql = "create temporary table TempValues (\n"
        . " scop $i11,\n"
        . " name $v50,\n"
        . " type $i11,\n"
        . " revl $i11,\n"
        . " valu $txt,\n"
        . " primary key (scop,name)\n"
        . ")";
    command($sql, $db);
}

function drop_temp($db)
{
    $sql = 'drop temporary table TempValues';
    command($sql, $db);
}

function kill_temp($db)
{
    $sql = "delete from TempValues";
    command($sql, $db);
}

function temp_valu($gid, $db)
{
    $sql = "insert ignore into TempValues select\n"
        . " V.scop  as scop,\n"
        . " V.name  as name,\n"
        . " V.itype as type,\n"
        . " X.revl  as revl,\n"
        . " X.valu  as valu\n"
        . ' from '
        . " VarValues as X,\n"
        . " Variables as V,\n"
        . " MachineGroups as G\n"
        . " where X.varuniq = V.varuniq\n"
        . " and X.mgroupuniq = G.mgroupuniq\n"
        . " and G.mgroupid = $gid";
    $res = command($sql, $db);
    return affected($res, $db);
}


function temp_sema($gid, $mid, $db)
{
    $int = constVblTypeInteger;
    $sem = constVblTypeSemaphore;
    $sql = "insert ignore into TempValues select\n"
        . " V.scop as scop,\n"
        . " concat(V.name,'Local') as name,\n"
        . " $int   as type,\n"
        . " S.revl as revl,\n"
        . " S.valu as valu\n"
        . ' from '
        . " Variables as V,\n"
        . " SemClears as S,\n"
        . " Census as C,\n"
        . " MachineGroups as G\n"
        . " where S.censusuniq = C.censusuniq\n"
        . " and C.id = $mid\n"
        . " and S.mgroupuniq = G.mgroupuniq\n"
        . " and G.mgroupid = $gid\n"
        . " and S.varuniq = V.varuniq";
    $res = command($sql, $db);
    return affected($res, $db);
}


function temp_invd($mid, $cid, $db)
{
    $sql = "insert ignore into TempValues select\n"
        . " scop  as scop,\n"
        . " name  as name,\n"
        . " itype as type,\n"
        . " revl  as revl,\n"
        . " valu  as valu\n"
        . " from InvalidVars\n"
        . " where siteid = $cid\n"
        . " and censusid = $mid";
    $res = command($sql, $db);
    return affected($res, $db);
}


function legacy_site_csum(&$env, $db, $dbg, $vers)
{
    kill_temp($db);
    $mid = $env['mid'];
    $cid = $env['cid'];
    $gid = $env['sgid'];

    $rrr = temp_valu($gid, $db);
    $sss = temp_sema($gid, $mid, $db);
    $fff = temp_invd(0, $cid, $db);
    $num = $rrr + $sss + $fff;
    $txt = "    global: (real:$rrr,sem:$sss,fake:$fff,total:$num)";

    debug_csum($txt);

    $sql = "select name,type,revl,valu,scop\n"
        . " from TempValues";
    debug_note('global');
    return GetOneChecksum($sql, $db, 0, "sgbl.txt", $dbg, $vers);
}



function legacy_host_csum(&$env, $db, $dbg, $vers)
{
    kill_temp($db);
    $mid = $env['mid'];
    $gid = $env['hgid'];
    $rrr = temp_valu($gid, $db);
    $sss = temp_sema($gid, $mid, $db);
    $fff = temp_invd($mid, 0, $db);
    $num = $rrr + $sss + $fff;

    $txt = "     local: (real:$rrr,sem:$sss,fake:$fff,total:$num)";
    debug_csum($txt);

    $sql = "select name,type,revl,valu,scop\n"
        . " from TempValues";
    debug_note('local');
    return GetOneChecksum($sql, $db, 0, "slcl.txt", $dbg, $vers);
}


/* get_state_settings_query
        Return the SQL for getting the values that correspond to the older
        state and state revision settings.  Derive the state settings for
        variables using the ValueMap table.  The way we do this is that by
        looking at the category for a group in which the variable has a value.  If it is the "site" category
        then it is a global variable.  If it is the "machine" category
        then it is either local or local-only.  Those two cases are
        distinguished by looking at the "grpsite" and "grpany" flags in
        the VarVersions table.  If either one is set, then the variable
        can take a value outside the machine group, so it is local,
        otherwise it is local-only.

        The query returns five columns:
            scop (Scrip number)
            name (variable name)
            itype (variable type)
            srev (state revision level)
            stat (state value)
    */
function get_state_settings_query(&$env, $db)
{
    /* Get a few fixed values that we need:  the mcatuniq values for
            "site" and "machine" categories, the censusuniq value for
            this machine, and the client version. */
    $qs  = safe_addslashes(constCatSite);
    $sql = "select mcatuniq from MachineCategories where category='$qs'";
    $row = find_one($sql, $db);
    $siteCatUniq = $row['mcatuniq'];

    $qm  = safe_addslashes(constCatMachine);
    $sql = "select mcatuniq from MachineCategories where category='$qm'";
    $row = find_one($sql, $db);
    $machineCatUniq = $row['mcatuniq'];

    $mid = $env['mid'];
    $sql = "select censusuniq from Census where id = $mid";
    $row = find_one($sql, $db);
    $censusUniq = $row['censusuniq'];

    $vers = $env['vers'];

    $sql = "select V.scop, V.name, V.itype,\n"
        . " M.revl as srev,\n"
        . " if(M.mcatuniq = '$siteCatUniq',\n"
        . "     " . constVarConfStateGlobal . ",\n"
        . "     if(M.mcatuniq = '$machineCatUniq',\n"
        . "         if(VV.grpsite=1 or VV.grpany=1,\n"
        . "             " . constVarConfStateLocal . ",\n"
        . "             " . constVarConfStateLocalOnly . "),\n"
        . "         " . constVarConfStateGlobal . ")) as stat from\n"
        . " ValueMap as M,\n"
        . " Variables as V,\n"
        . " VarVersions as VV\n"
        . " where VV.vers = '$vers'\n"
        . " and VV.varuniq = V.varuniq\n"
        . " and M.varuniq = V.varuniq\n"
        . " and M.censusuniq = '$censusUniq'";

    return $sql;
}



/*
    |  As it turns out, every single local only variable
    |  on nanoheal has srev = 1 ... so we'll just always
    |  report srev of 1 for all SemClears.
    */

function legacy_stat_csum(&$env, $db, $dbg, $vers)
{
    kill_temp($db);
    $mid = $env['mid'];
    $cid = $env['cid'];
    $gid = $env['hgid'];
    $lon = constVarConfStateLocalOnly;
    $loc = constVarConfStateLocal;
    $gbl = constVarConfStateGlobal;
    $qv  = safe_addslashes($env['vers']);

    /* Note that we are maintaining stat and srev for now but we
            really want to get rid of them, since they represent
            duplicate data.  As soon as we verify that they are unused,
            they are gone.  */
    $sql = "insert ignore into TempValues "
        . get_state_settings_query($env, $db);
    $res = command($sql, $db);
    $rrr = affected($res, $db);
    $int = constVblTypeInteger;
    $sem = constVblTypeSemaphore;
    $sql = "insert ignore into TempValues select\n"
        . " V.scop as scop,\n"
        . " concat(V.name,'Local') as name,\n"
        . " $int as type,\n"
        . "    1 as revl,\n"
        . " $lon as valu\n"
        . " from Variables as V,\n"
        . " SemClears as S,\n"
        . " Census as C,\n"
        . " MachineGroups as G\n"
        . " where S.varuniq = V.varuniq\n"
        . " and S.censusuniq = C.censusuniq\n"
        . " and C.id = $mid\n"
        . " and S.mgroupuniq = G.mgroupuniq\n"
        . " and G.mgroupid = $gid\n"
        . " and V.itype = $sem";
    $res = command($sql, $db);
    $sss = affected($res, $db);
    $sql = "insert ignore into TempValues select\n"
        . " scop  as scop,\n"
        . " name  as name,\n"
        . " itype as type,\n"
        . " srev  as revl,\n"
        . " stat  as valu\n"
        . " from InvalidVars\n"
        . " where isglobal = 0\n"
        . " and censusid = $mid";
    $res = command($sql, $db);
    $fff = affected($res, $db);

    $num = $rrr + $sss + $fff;
    $txt = "     state: (real:$rrr,sem:$sss,fake:$fff,total:$num)";

    debug_csum($txt);

    $sql = "select name,type,revl,valu,scop\n"
        . " from TempValues";
    debug_note('state');
    return GetOneChecksum($sql, $db, 1, "scfg.txt", $dbg, $vers);
}


function find_csum($cid, $mid, $min, $db)
{
    $sql = "select * from LegacyCache\n"
        . " where censusid = $mid\n"
        . " and siteid = $cid\n"
        . " and drty = 0\n"
        . " and last > $min";
    return find_one($sql, $db);
}


/*
    |  The checksum calculation changed in version 1.006.0836 ... if we
    |  find a "legacy" machine logging, then we should ignore the checksum
    |  cache and revert to the old way of doing it.
    |
    |  We don't examine the cached value, since it won't match anyway, and
    |  we don't store the result, since newer clients can't use it anyway.
    */

function find_cache_csum(&$env, $db)
{
    $old = '1.006.0836';
    $csum = array();
    $vers = $env['vers'];
    if (strnatcmp($vers, $old) > 0) {
        $min = time() - (16 * 3600);
        $mid = $env['mid'];
        $cid = $env['cid'];
        $svar = find_csum($cid, 0, $min, $db);
        $hvar = find_csum(0, $mid, $min, $db);
        if (($svar) && ($hvar)) {
            $csum[constGlobalChecksum] = $svar['gsum'];
            $csum[constLocalChecksum]  = $hvar['lsum'];
            $csum[constStateChecksum]  = $hvar['ssum'];

            $site = $env['site'];
            $host = $env['host'];
            $stat = "m:$mid,c:$cid";
            $text = "config: $host using cached csum ($stat) at $site";
            debug_csum($text);
        }
    }
    return $csum;
}


function calculate_csum(&$env, $dbg_config, $vers, $db)
{
    //      debug_pid_init($env);
    make_temp($db);
    $glob = legacy_site_csum($env, $db, $dbg_config, $vers);
    $locl = legacy_host_csum($env, $db, $dbg_config, $vers);
    $stat = legacy_stat_csum($env, $db, $dbg_config, $vers);
    drop_temp($db);

    $s[constGlobalChecksum] = $glob;
    $s[constLocalChecksum]  = $locl;
    $s[constStateChecksum]  = $stat;
    return $s;
}


function site_cache(&$env, &$csum, $db)
{
    $num = 0;
    $cid = $env['cid'];
    $now = $env['time'];
    $qv  = safe_addslashes($csum[constGlobalChecksum]);
    $sql = "insert into LegacyCache set\n"
        . " siteid = $cid,\n"
        . " gsum = '$qv',\n"
        . " last = $now";
    $res = command($sql, $db);
    $num = affected($res, $db);
    if (!$num) {
        $sql = "update LegacyCache set\n"
            . " gsum = '$qv',\n"
            . " drty = 0,\n"
            . " last = $now\n"
            . " where censusid = 0\n"
            . " and siteid = $cid";
        $res = command($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}


function host_cache(&$env, &$csum, $db)
{
    $num = 0;
    $mid = $env['mid'];
    $now = $env['time'];
    $ql  = safe_addslashes($csum[constLocalChecksum]);
    $qs  = safe_addslashes($csum[constStateChecksum]);
    $sql = "insert into LegacyCache set\n"
        . " censusid = $mid,\n"
        . " lsum = '$ql',\n"
        . " ssum = '$qs',\n"
        . " last = $now";
    $res = command($sql, $db);
    $num = affected($res, $db);
    if (!$num) {
        $sql = "update LegacyCache set\n"
            . " lsum = '$ql',\n"
            . " ssum = '$qs',\n"
            . " drty = 0,\n"
            . " last = $now\n"
            . " where censusid = $mid\n"
            . " and siteid = 0";
        $res = command($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}


/*
    |  The checksum calculation changed in version 1.006.0836 ... if we
    |  find a "legacy" machine logging, then we should ignore the checksum
    |  cache and revert to the old way of doing it.
    |
    |  We don't examine the cached value, since it won't match anyway, and
    |  we don't store the result, since newer clients can't use it anyway.
    */

function store_cache_csum(&$env, &$csum, $db)
{
    $site = $env['site'];
    $host = $env['host'];
    $vers = $env['vers'];
    $gsum = $csum[constGlobalChecksum];
    $lsum = $csum[constLocalChecksum];
    $ssum = $csum[constStateChecksum];
    if (($gsum) && ($lsum) && ($ssum) && ($vers)) {
        $old = '1.006.0836';
        if (strnatcmp($vers, $old) > 0) {
            site_cache($env, $csum, $db);
            host_cache($env, $csum, $db);

            $text = "config: $host store cache at $site";
        } else {
            $text = "config: $host ($vers) too old at $site";
        }
        debug_csum($text);
    }
}


/*
    |  We've just logged some variables.
    |  Invalidate the appropriate checksum
    |  cache records.
    */

function update_cache(&$env, $db)
{
    $now = $env['time'];
    $lcl = $env['lclw'];
    $gbl = $env['gblw'];
    $cid = $env['cid'];
    $mid = $env['mid'];
    if (($lcl) && ($gbl) && ($cid) && ($mid)) {
        $sql = "update LegacyCache set\n"
            . " drty = 1,\n"
            . " last = $now\n"
            . " where siteid = $cid\n"
            . " or censusid = $mid";
        command($sql, $db);
    }

    if ((!$gbl) && ($lcl) && ($mid)) {
        $sql = "update LegacyCache set\n"
            . " drty = 1,\n"
            . " last = $now\n"
            . " where censusid = $mid";
        command($sql, $db);
    }

    if ((!$lcl) && ($gbl) && ($cid)) {
        $sql = "update LegacyCache set\n"
            . " drty = 1,\n"
            . " last = $now\n"
            . " where siteid = $cid";
        command($sql, $db);
    }
}


function legacy_csum(&$env, $db)
{
    /* Get the control variables we need. */
    $vers = substr($env['vers'], 0, 10);
    $host = $env['host'];
    $site = $env['site'];
    $dbg_site = server_opt('config_debug_site', $db);
    $dbg_machine = server_opt('config_debug_machine', $db);
    $dbg_config = (($site == $dbg_site) && ($host == $dbg_machine));
    if ($dbg_config) {
        logs::log(__FILE__, __LINE__, "config: debug log for $host at $site", 0);
    }

    $csum = find_cache_csum($env, $db);
    /* Always calculate the checksum if we are debugging. */
    if ((!$csum) || $dbg_config) {
        $csum = calculate_csum($env, $dbg_config, $vers, $db);
        store_cache_csum($env, $csum, $db);
    }
    return $csum;
}

/* count_config_updates
        Find out how many config updates are currently in progress.  Also,
        purge any old ones.  Note that this is a little different from the
        asset logging, where the cron job is in charge of purging old ones,
        and it also has to clean stuff up while purging.  Return the number
        of current updates after purging the old ones.  $db is the config
        database, and $time is the time to use for expiring.
    */

function count_config_updates($db, $time)
{
    $deleted = true;
    while ($deleted) {
        $sql = "select count(*) as num,\n"
            . " min(provisional) as min\n"
            . " from Revisions\n"
            . " where provisional > 0";
        $res = command($sql, $db);
        /* Note that this should not normally return an empty result; if there
               are no items it seems to return $num = 0 and $min = NULL so we have
               to treat that case specially.  If it does return an empty result
               this usually means something very weird is going on.
            */
        if (($res) && (mysqli_num_rows($res))) {
            $row = mysqli_fetch_array($res);
            $num = $row['num'];
            $min = $row['min'];
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
            /* Note that we hard-wire this to expire items older than
                    ONE HOUR. */
            if (($num == 0) || (($min + 3600) > $time)) {
                $deleted = false;
            } else {
                $sql = "update Revisions set\n"
                    . " provisional = 0\n"
                    . " where provisional = $min";
                /* Note that this may fail if another thread is already
                        doing it, so we want to ignore any errors. */
                mysqli_query($db, $sql);
                $deleted = true;
            }
        } else {
            $num = 0;
            $deleted = false;
        }
    }

    return $num;
}



/*
    | claim_config_machine
    |
    |  Mark the fact that the machine $mid at $site with the name $host
    |  started an update at $time.  $db is the config database.
    |  If the machine is already doing an update, then return the time
    |  when that update started, and don't do any claiming.  Otherwise,
    |  return an empty value.
    */

function claim_config_machine(&$env, $db)
{
    $mid  = $env['mid'];
    $time = $env['time'];
    $retVal = 0;

    $row = find_revl_mid($mid, $db);

    if ($row) {
        $prov = $row['provisional'];
        if ($prov != 0) {
            $retVal = $prov;
        } else {
            $sql = "update Revisions set\n"
                . " provisional = $time\n"
                . " where censusid = $mid";
            command($sql, $db);
        }
    } else {
        $site = $env['site'];
        $host = $env['host'];
        $vers = $env['vers'];
        create_revision($mid, $site, $host, $vers, $time, $db);
    }
    return $retVal;
}


/* unclaim_config_machine
        Mark the fact that machine $mid has finished
        an update.  $db is the config database.
    */

function unclaim_config_machine($mid, $db)
{
    if ($mid) {
        $sql = "update Revisions set\n"
            . " provisional = 0\n"
            . " where censusid = $mid";
        command($sql, $db);
    }
}


/* config_check_sync
        Do the work of VARS_CheckSync, which checks to see whether a client
        is in sync with the server.
        Return $msg as a message to log, or empty for no logging.  Return
        $clientAction as the integer action code to send back to the client.
        $mList is the ALIST that was sent by the client.  $site and $machine
        identify the client.  $version is the client version.  $time is when
        this operation started, and $db is the configuration database.
        Return the status code for the operation as the function value.
    */

function config_check_sync(&$env, &$msg, &$clientAction, &$mList, $db)
{
    $clientChecks = array();
    $clientAction = constCheckSyncDoNothing;
    $msg = '';
    $rval = constAppNoErr;

    $clientChecks[constGlobalChecksum] = $mList[constInfoGlobalChecksum];
    $clientChecks[constLocalChecksum]  = $mList[constInfoLocalChecksum];
    $clientChecks[constStateChecksum]  = $mList[constInfoConfigChecksum];

    /* If this machine doesn't exist in the Revisions table, add it.
            Note that the "uniq" index prevents us from adding the same
            machine more than once, so we just ignore a failed insert.

            We can't add a unique index to the uuid yet, since most
            of them are empty.  Eventually we'll drop support for
            empty uuid's and then we can make them unique.
        */

    $mid  = $env['mid'];
    $site = $env['site'];
    $host = $env['host'];
    $vers = $env['vers'];
    $time = $env['time'];
    $revl = touch_revision($mid, $site, $host, $vers, $time, $db);

    /* If we don't have the browser database, then we need the
            client to register and synchronize. */
    if (!have_vers($db, $vers)) {
        /* Note that it is certainly possible to need a registration
                without also needing a synchronization.  For example, if
                the client is upgraded to a new version but all the
                variables are the same and nothing has changed in persistent
                state, then this would be the case.  However, this is not
                too common and the extra synchronization doesn't really
                hurt anything. */
        $clientAction = constCheckSyncDoRegisterAndSync;
        $msg .= 'register/synch';
    } else {
        $serverChecks = legacy_csum($env, $db);
        /* Determine whether or not synchronization is needed. */
        /* If we timed out, just return. */
        if (!$serverChecks) {
            return $rval;
        }

        if (SameChecksums($host, $site, $serverChecks, $clientChecks)) {
            $qs = safe_addslashes($site);
            $qm = safe_addslashes($host);

            $machines = GetMachinesNeedSync($qs, $qm, $db);
            if ($machines) {
                $clientAction = constCheckSyncMachineList;
                $num = safe_count($machines);
                $msg .= "sync $num machines";
            } else {
                $clientAction = constCheckSyncDoNothing;

                /*
                    |  This is for debugging, normal released servers
                    |  should not be logging no action calls.
                    */

                //              $msg .= 'no action';
            }
        } else {
            $clientAction = constCheckSyncDoSync;
            $msg .= 'synchronize';
        }
    }

    return $rval;
}


/* VARS_CheckSync
    Get the PS revision level "psRev" from the client, identified by
    "machineID".  Figure out what to do and respond with the correct action
    code in "action".  If no action is required, go ahead and update the
    timestamp to indicate that the client contacted us.
extern ERRSTAT VARS_CheckSync(MACHINE mID, PUINT32 action, PALIST machineID);
*/


function VARS_CheckSync(&$args)
{
    $usec = $args['usec'];
    debug_time('>> VARS_CheckSync', $usec);
    $time = time();

    $rval = constErrDatabaseNotAvailable;
    $clientAction = constCheckSyncDoNothing;
    $logText = '';

    /* Get the parts of the machine ID. */
    $alst  = &$args['valu'][2];
    $usec  = &$args['usec'];
    $alen  =  strlen($alst);
    $pid   =  getmypid();
    $mList =  fully_parse_alist($alst);

    $row  = array();
    $num  = 0;
    $mid  = 0;
    $cid  = 0;
    $more = true;
    $code = 0;
    $sgid = 0;
    $hgid = 0;
    $uuid = '';
    $host = $mList[constInfoMachine];
    $vers = $mList[constInfoVersion];
    $site = $mList[constInfoSite];
    if (isset($mList[constInfoUUID])) {
        $uuid = $mList[constInfoUUID];
    }

    if (isset($mList[constInfoToken])) {
        $code = $mList[constInfoToken];
    }

    if (pfDisableConf) {
        $logText = 'skipping VARS_CheckSync';
        $rval = constAppNoErr;
        $more = false;
    }

    if ($more) {
        $db = db_code('db_cor');
        if (!$db) {
            $more = false;
            $rval = constErrDatabaseNotAvailable;
            $logText = 'mysql unavailable';
        }
    }

    if ($more) {
        $temp = constAppNoErr;
        identity($site, $host, $uuid, $rval, $logText, $more);
    }

    if ($more) {
        $temp = census_manage($site, $host, $uuid, 0, $db);
        switch ($temp) {
            case constAppNoErr:
                break;
            case constErrServChangeUUID:
            case constErrServChangeName:
                $clientAction = constCheckSyncDoSync;
                $more = false;
                $rval = constAppNoErr;
                break;
            default:
                $rval = $temp;
                $more = false;
                break;
        }
    }

    if ($more) {
        /* Check to see if we are too busy. */
        $max = server_def('max_config_logs', 10, $db);
        $num = count_config_updates($db, $time);
        if ($max < $num + 1) {
            /* Note that the special case of max being zero is
                   explicitly allowed and expected. */
            $rval = constErrServerTooBusy;
            $logText = "server too busy (n:$num,m:$max)";
            $more = false;
        }
    }

    if ($more) {
        $row = find_census_uuid($uuid, $db);
        if ($row) {
            find_groups($row, $mid, $cid, $sgid, $hgid, $db);
        } else {
            $rval = constErrDatabaseNotAvailable;
            $logText = 'census error';
            $more = false;
        }
    }
    if ($more) {
        $more = (($mid) && ($cid) && ($sgid) && ($hgid));
        if ($more) {
            debug_note("groups (m:$mid,c:$cid,s:$sgid,h:$hgid)");
        } else {
            $rval = constErrDatabaseNotAvailable;
            $logText = 'group error';
        }
    }

    if ($more) {
        $env = array();
        $env['mid']  = $mid;
        $env['cid']  = $cid;
        $env['site'] = $site;
        $env['host'] = $host;
        $env['uuid'] = $uuid;
        $env['vers'] = $vers;
        $env['hgid'] = $hgid;
        $env['sgid'] = $sgid;
        $env['time'] = $time;
        $env['usec'] = $usec;
        $prov = claim_config_machine($env, $db);
        if ($prov) {
            /* Not exactly right but it will do for now. */
            $more = false;
            $rval = constErrServerTooBusy;
            $date = date('m/d H:i:s', $prov);
            $logText = "pending update ($date)";
        }
    }

    if ($more) {
        $msg = '';
        $rval = config_check_sync($env, $msg, $clientAction, $mList, $db);
        if ($msg) {
            $logText = $msg;
        }
        /* Note that unlike asset logging, we just always free
                up the machine, even if the operation timed out,
                because there is no "cleanup" to do. */
        unclaim_config_machine($mid, $db);
    }

    if ($logText) {
        $stat = "n:$num,p:$pid,l:$alen";
        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        $text = "config: $host $logText ($stat) at $site in $secs";
        logs::log(__FILE__, __LINE__, $text, 0);
        debug_note($text);
    }

    /* Don't bother returning the ALIST (slight traffic optimization). */
    $args['valu'][1] = $clientAction;
    $args['valu'][2] = '';
    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = $rval;

    // $args returned to caller by reference
    debug_time('<< VARS_CheckSync', $usec);
}




/* SCNF_ApplyScrips
    Apply the Scrip information in "scripData" to generate the browser database
    for editing Scrip configuration on the server for the machine identified by
    "machineID" (which is the same format as the parameter for VARS_CheckSync
    above).
extern ERRSTAT SCNF_ApplyScrips(MACHINE mID, PALIST machineID,
    PALIST scripData);
*/
function SCNF_ApplyScrips(&$args)
{
    $rval = constErrDatabaseNotAvailable;

    /* Get the parts of the machine ID. */
    $mList = fully_parse_alist($args['valu'][1]);
    $version = $mList[constInfoVersion];

    $db = db_code('db_cor');
    if ($db) {
        /* Only make the entry if we don't already have it. */
        /* Note that we'd like to change this to use
                fully_parse_alist and rewrite build_scrips. */
        if (!have_vers($db, $version)) {
            $list = parse_alist($args['valu'][2], 14); /* nothing like a magic number */
            build_scrips($version, $list, $db);
        }
        $rval = constAppNoErr;
    }

    /* Don't bother returning the ALISTs (slight traffic optimization). */
    $args['valu'][1] = '';
    $args['valu'][2] = '';

    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = $rval;

    // $args returned by reference
}



/* CompareValues
    Return one of three update directions depending on the comparison of
    $clientVal and $serverVal. $varType is the type of the variable.
    This is only used when the revision levels for the two variables are
    the same and a "tie-breaker" is needed to prevent endless updating.

    The "tie-breaker" is always the "smaller" value:  the lesser string, the
    smaller integer, or FALSE for booleans.  Note that it is important that
    the server and client pick the same value.  Because of the way PHP works
    this is pretty simple here.

    Note that the comparison for equality is done with CRLFs changed to LFs,
    so that this matches the checksum generation.  However, the comparison
    of values for the tie breaker has to be done with the original strings,
    because this is the way the client does it.  This is kind of a small
    bug, but we want everything to remain consistent.
*/

function CompareValues($clientVal, $serverVal, $varType)
{
    $clientNoCR = str_replace("\r\n", "\n", $clientVal);
    $serverNoCR = str_replace("\r\n", "\n", $serverVal);

    if ($serverNoCR == $clientNoCR) {
        $result = constUpdateNone;
    } else if ($serverVal < $clientVal) {
        $result = constUpdateToThere;
    } else /* $serverVal > $clientVal */ {
        $result = constUpdateToHere;
    }
    return $result;
}


/*
    |  Record a single value for variable $vid in
    |  machine group $gid ... returns a positive
    |  record index for success or zero for failure.
    |
    |    ".$GLOBALS['PREFIX']."core.VarValues.def = 0;
    |    ".$GLOBALS['PREFIX']."core.VarValues.cksum = '';
    |    ".$GLOBALS['PREFIX']."core.VarValues.deleted = 0;
    */

function create_value(&$env, $vid, $gid, $rev, $val, $db)
{
    $xid = 0;
    if (($vid) && ($gid)) {
        $qh  = safe_addslashes($env['host']);
        $qv  = safe_addslashes($val);
        $now = $env['time'];
        $sql = GCFG_CreateVarValues(
            $gid,
            $vid,
            $qv,
            $qh,
            $rev,
            $now,
            1,
            1,
            1,
            $db
        );
        $res = command($sql, $db);
        if (affected($res, $db)) {
            $xid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
            $err = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$xid,
                "valueid",
                constDataSetGConfigVarValues,
                constOperationInsert
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "create_value: PHP_DSYN_InvalidateRow "
                    . "returned " . $err, 0);
            }
        }
    }
    return $xid;
}


/*
    |  Record a single value for variable $vid in
    |  machine group $gid ... returns a positive
    |  record index for success or zero for failure.
    */

function update_value_publish(&$env, &$pkg, $vid, $glob, $db)
{
    $xid = 0;
    if ($glob) {
        $valu = $pkg[constVarPackageGlobal];
        $revl = $pkg[constVarPackageGlobalRev];
        $gid  = $env['sgid'];
    } else {
        $valu = $pkg[constVarPackageLocal];
        $revl = $pkg[constVarPackageLocalRev];
        $gid  = $env['hgid'];
    }
    if (($vid) && ($gid)) {
        $qh  = safe_addslashes($env['host']);
        $qv  = safe_addslashes($valu);
        $now = $env['time'];

        $sql = "select valueid from VarValues left join Variables on ("
            . "VarValues.varuniq=Variables.varuniq) left join "
            . "MachineGroups on (VarValues.mgroupuniq=MachineGroups."
            . "mgroupuniq) where varid=$vid and mgroupid=$gid";
        $set = DSYN_DeleteSet(
            $sql,
            constDataSetGConfigVarValues,
            "valueid",
            "update_value_publish",
            0,
            0,
            constOperationDelete,
            $db
        );

        if ($set) {
            $sql = "update VarValues left join Variables on ("
                . "VarValues.varuniq=Variables.varuniq) left join "
                . "MachineGroups on (VarValues.mgroupuniq=MachineGroups."
                . "mgroupuniq) set\n"
                . " valu = '$qv',\n"
                . " revl = $revl,\n"
                . " host = '$qh',\n"
                . " last = $now,\n"
                . " revldef = def+revldef,\n"
                . " def = 0\n"
                . " where varid = $vid\n"
                . " and mgroupid = $gid";
            $res = command($sql, $db);
            $num = affected($res, $db);

            DSYN_UpdateSet(
                $set,
                constDataSetGConfigVarValues,
                "valueid",
                $db
            );
        }
    }
    return $num;
}


function create_invalid(&$env, &$pkg, $scop, $name, $glob, $db)
{
    $site = $env['site'];
    $host = $env['host'];

    /* Don't add a banned variable to InvalidVars. */
    $banned = server_opt('banned_vars', $db);
    if (strpos($banned, ':' . $name . ':') !== false) {
        $text = "config: rejecting banned variable $scop:$name"
            . " from $host at $site";
        logs::log(__FILE__, __LINE__, $text, 0);
        return 0;
    }

    $stat = 0;
    $srev = 0;
    $type = $pkg[constVarPackageType];
    if ($glob) {
        $valu = $pkg[constVarPackageGlobal];
        $revl = $pkg[constVarPackageGlobalRev];
        $mid  = 0;
        $cid  = $env['cid'];
    } else {
        $valu = $pkg[constVarPackageLocal];
        $revl = $pkg[constVarPackageLocalRev];
        $cid  = 0;
        $mid  = $env['mid'];
        if (isset($pkg[constVarPackageState]))
            $stat = $pkg[constVarPackageState];
        if (isset($pkg[constVarPackageStateRev]))
            $srev = $pkg[constVarPackageStateRev];
    }

    $iid = 0;
    $qn  = safe_addslashes($name);
    $qv  = safe_addslashes($valu);
    $qh  = safe_addslashes($host);
    $now = $env['time'];
    $sql = "insert into InvalidVars set\n"
        . " censusid = $mid,\n"
        . " isglobal = $glob,\n"
        . " siteid = $cid,\n"
        . " itype = $type,\n"
        . " name = '$qn',\n"
        . " host = '$qh',\n"
        . " valu = '$qv',\n"
        . " scop = $scop,\n"
        . " stat = $stat,\n"
        . " srev = $srev,\n"
        . " revl = $revl,\n"
        . " last = $now";
    $res = command($sql, $db);
    if (affected($res, $db)) {
        $iid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
    } else {
        $text = "config: InvalidVars insert failure $scop:$name"
            . " by $host at $site";
        //            logs::log(__FILE__, __LINE__, $text, 0);
    }
    return $iid;
}

function update_invalid(&$env, &$pkg, $scop, $name, $glob, $db)
{
    $stat = 0;
    $srev = 0;
    $type = $pkg[constVarPackageType];
    if ($glob) {
        $valu = $pkg[constVarPackageGlobal];
        $revl = $pkg[constVarPackageGlobalRev];
        $mid  = 0;
        $cid  = $env['cid'];
    } else {
        $valu = $pkg[constVarPackageLocal];
        $revl = $pkg[constVarPackageLocalRev];
        $cid  = 0;
        $mid  = $env['mid'];
        if (isset($pkg[constVarPackageState]))
            $stat = $pkg[constVarPackageState];
        if (isset($pkg[constVarPackageState]))
            $srev = $pkg[constVarPackageState];
    }

    $qn  = safe_addslashes($name);
    $qv  = safe_addslashes($valu);
    $qh  = safe_addslashes($env['host']);
    $now = $env['time'];
    $sql = "update InvalidVars set\n"
        . " host = '$qh',\n"
        . " valu = '$qv',\n"
        . " stat = $stat,\n"
        . " srev = $srev,\n"
        . " revl = $revl,\n"
        . " last = $now\n"
        . " where scop = $scop\n"
        . " and name = '$qn'\n"
        . " and ((isglobal = 1 and siteid = $cid)\n"
        . "   or (isglobal = 0 and censusid = $mid))";
    $res = command($sql, $db);
    return affected($res, $db);
}

/*
    |  Record a single value for semaphore $sem in
    |  machine group $gid ... returns a positive
    |  record index for success or zero for failure.
    */

function create_semaphore(&$env, $vid, $gid, $revl, $valu, $db)
{
    $sid = 0;
    if (($vid) && ($gid)) {
        $mid = $env['mid'];
        $now = $env['time'];
        $sql = GCFG_CreateSemClears($mid, $gid, $vid, $valu, $revl, $now);
        $res = command($sql, $db);
        if (affected($res, $db)) {
            $sid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
            $err = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$sid,
                "semid",
                constDataSetGConfigSemClears,
                constOperationInsert
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "create_semaphore: PHP_DSYN_InvalidateRow "
                    . "returned " . $err, 0);
            }
        }
    }
    return $sid;
}



/*
    |  Record a single value for semaphore $sem in
    |  machine group $gid ... returns a positive
    |  record index for success or zero for failure.
    */

function update_semaphore(&$env, $vid, $gid, $val, $rev, $db)
{
    $num = 0;
    if (($vid) && ($gid)) {
        $mid = $env['mid'];
        $now = $env['time'];

        $sql = "select semid from SemClears left join Census on ("
            . "SemClears.censusuniq=Census.censusuniq) left join Variables"
            . " on (SemClears.varuniq=Variables.varuniq) left join "
            . "MachineGroups on (SemClears.mgroupuniq=MachineGroups."
            . "mgroupuniq) where id=$mid and varid=$vid and "
            . "mgroupid=$gid";
        $set = DSYN_DeleteSet(
            $sql,
            constDataSetGConfigSemClears,
            "semid",
            "update_semaphore",
            0,
            0,
            constOperationDelete,
            $db
        );

        if ($set) {
            $sql = "update SemClears left join Census on ("
                . "SemClears.censusuniq=Census.censusuniq) left join "
                . "Variables on (SemClears.varuniq=Variables.varuniq) left"
                . " join MachineGroups on (SemClears.mgroupuniq="
                . "MachineGroups.mgroupuniq) set\n"
                . " valu = $val,\n"
                . " revl = $rev,\n"
                . " SemClears.last = $now\n"
                . " where id = $mid\n"
                . " and mgroupid = $gid\n"
                . " and varid = $vid";
            $res = command($sql, $db);
            $num = affected($res, $db);

            DSYN_UpdateSet(
                $set,
                constDataSetGConfigSemClears,
                "semid",
                $db
            );
        }
    }
    if (!$num) {
        $stat = "s:$vid,g:$gid,m:$mid,v:$val,r:$rev";
        $text = "config: semapore update ($stat) failure";
        debug_note($text);
        logs::log(__FILE__, __LINE__, $text, 0);
    }
    return $num;
}



/*
    |  Update the state for variable $vid on machine $mid.
    |  returns one for success or zero for failure.
    */

function update_state(&$env, $vid, $stat, $srev, $db)
{
    $num = 0;
    $mid = $env['mid'];

    /* Figure out in which group the variable belongs. */
    switch ($stat) {
        case constVarConfStateLocal:
        case constVarConfStateLocalOnly:
            $gid = $env['hgid'];
            break;
        case constVarConfStateGlobal:
        default:
            $gid = $env['sgid'];
            break;
    }

    if (($vid) && ($gid) && ($mid)) {
        /* Remove the checksums for this ValueMap entry. */
        $sql = "select valmapid from ValueMap left join "
            . "Census on (ValueMap.censusuniq=Census.censusuniq) left join"
            . " Variables on (ValueMap.varuniq=Variables.varuniq) where "
            . "id=$mid and varid=$vid";
        $set = DSYN_DeleteSet(
            $sql,
            constDataSetGConfigValueMap,
            "valmapid",
            "update_state",
            0,
            1,
            constOperationDelete,
            $db
        );
        if ($set) {
            /* Note that we are maintaining stat and srev for now but we
                    really want to get rid of them, since they represent
                    duplicate data.  As soon as we verify that they are unused,
                    they are gone.  */
            $sql = "update ValueMap left join "
                . "Census on (ValueMap.censusuniq=Census.censusuniq) left "
                . "join Variables on (ValueMap.varuniq=Variables.varuniq) "
                . "join MachineGroups "
                . "set\n"
                . " ValueMap.mgroupuniq = MachineGroups.mgroupuniq,\n"
                . " ValueMap.mcatuniq = MachineGroups.mcatuniq,\n"
                . " stat = $stat,\n"
                . " srev = $srev,\n"
                . " revl = $srev\n"
                . " where id = $mid\n"
                . " and varid = $vid\n"
                . " and mgroupid = $gid";
            $res = command($sql, $db);
            $num = affected($res, $db);

            /* Restore the checksums for this ValueMap entry. */
            DSYN_UpdateSet(
                $set,
                constDataSetGConfigValueMap,
                "valmapid",
                $db
            );
        }
    }
    if (!$num) {
        $temp = "v:$vid,g:$gid,m:$mid,s:$stat,r:$srev";
        $text = "config: state update ($temp) failure";
        debug_note($text);
        logs::log(__FILE__, __LINE__, $text, 0);
    }
    return $num;
}



/*
    |  Update the state for invalid variable $name:$scop on
    |  machine $mid.   I didn't think this could ever happen,
    |  however we've now seen it shortly after client upgrade
    |  time.  In any event, we need to support it.
    |
    |  Returns one for success or zero for failure.
    */

function inv_state(&$env, $name, $scop, $stat, $srev, $db)
{
    $num = 0;
    $mid = $env['mid'];
    $now = $env['time'];
    if (($mid) && ($name) && ($scop)) {
        $qn  = safe_addslashes($name);
        $sql = "update InvalidVars set\n"
            . " last = $now,\n"
            . " stat = $stat,\n"
            . " srev = $srev\n"
            . " where isglobal = 0\n"
            . " and name = '$qn'\n"
            . " and scop = $scop\n"
            . " and censusid = $mid";
        $res = command($sql, $db);
        $num = affected($res, $db);
    }
    if (!$num) {
        $temp = "n:$name:$scop,m:$mid,s:$stat,r:$srev";
        $text = "config: state update ($temp) failure";
        debug_note($text);
        logs::log(__FILE__, __LINE__, $text, 0);
    }
    return $num;
}


/*
    |  Record the value mapping for a single value
    |  for machine $mid ... returns a positive
    |  record index for success or zero for failure.
    |
    |    ".$GLOBALS['PREFIX']."core.ValueMap.cksum = '';
    */

/* create_vmap
        Create a new ValueMap entry indicating that the machine in $mid (from
        $env) should use the value of variable $vid from group $gid.  Set the
        stat and srev columns, which are only used by the compatibility code,
        from $stat and $srev.
    */
function create_vmap(&$env, $vid, $gid, $stat, $srev, $db)
{
    $mid = $env['mid'];
    $nid = 0;
    if (($vid) && ($gid)) {
        /* Note that we are maintaining stat and srev for now but we
                really want to get rid of them, since they represent
                duplicate data.  As soon as we verify that they are unused,
                they are gone.  */
        $sql = GCFG_CreateValueMap(
            $mid,
            $gid,
            $vid,
            $stat,
            $srev,
            $srev,
            $db
        );
        $res = command($sql, $db);
        if (affected($res, $db)) {
            $nid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
            $err = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$nid,
                "valmapid",
                constDataSetGConfigValueMap,
                constOperationInsert
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "create_vmap: PHP_DSYN_InvalidateRow "
                    . "returned " . $err . " for valmapid $valmapid", 0);
            }
        }
    }
    return $nid;
}



/* SetOutgoingDescComponents
    Set parts of the variable package "desc" for sending back to the client.
    "whatComp" specifies which components. "type" is the variable type. "rev"
    and "val" depend on the value of "whatComp". For the global components,
    they are the global revision and value, for the local components they are
    the local rev and value, and for the state they are the state revision and
    the state itself.
*/
function SetOutgoingDescComponents(&$desc, $type, $rev, $whatComp, $val)
{
    $desc[constVarPackageType] = $type;
    settype($desc[constVarPackageType], 'integer');
    if ($whatComp == constGlobal) {
        $desc[constVarPackageGlobalRev] = $rev;
        $desc[constVarPackageGlobal] = $val;
        /* Get the PHP types correct so that the ALIST
            will have the correct type also. */
        settype($desc[constVarPackageGlobalRev], 'integer');
        if ($type == constVblTypeString) {
            settype($desc[constVarPackageGlobal], 'string');
        } else {
            settype($desc[constVarPackageGlobal], 'integer');
        }
    } else if ($whatComp == constLocal) {
        $desc[constVarPackageLocalRev] = $rev;
        $desc[constVarPackageLocal] = $val;
        /* Get the PHP types correct so that the ALIST
                will have the correct type also. */
        settype($desc[constVarPackageLocalRev], 'integer');
        if ($type == constVblTypeString) {
            settype($desc[constVarPackageLocal], 'string');
        } else {
            settype($desc[constVarPackageLocal], 'integer');
        }
    } else {
        $desc[constVarPackageState] = $val;
        $desc[constVarPackageStateRev] = $rev;
        /* Get the PHP types correct so that the ALIST
               will have the correct type also. */
        settype($desc[constVarPackageState], 'integer');
        settype($desc[constVarPackageStateRev], 'integer');
    }
}


/* AddVarsNotOnClient
    Adds to the package "retVars" all those variables found in the globals
    table "globals" but not in "retVars". "locals" is the locals table, "cust",
    "host", and "db" identify the customer, machine, and database. "time" is
    the current synchronization time. This procedure will add variables to the
    locals table if they are in the globals but not in the locals for the
    specified customer and machine.
*/

function AddVarsNotOnClient(&$env, &$out, &$set, &$vars, $db)
{
    $time = $env['time'];
    $hgid = $env['hgid'];
    $sgid = $env['sgid'];
    $need = array();

    reset($vars);
    foreach ($vars as $scop => $d1) {
        if (!isset($set[$scop])) {
            /* adding a scrip not on the client (all its variables) */
            $out[$scop] = array();
        }

        foreach ($d1 as $name => $d2) {
            foreach ($d2 as $gid => $d3) {
                if ($gid == $sgid) {
                    if ((!isset($set[$scop])) || (!isset($set[$scop][$name]))) {
                        $need[$scop][$name][constMissingSiteVar] = true;
                    }
                }
                if ($gid == $hgid) {
                    if ((!isset($set[$scop])) ||
                        (!isset($set[$scop][$name]))
                    ) {
                        $need[$scop][$name][constMissingHostVar] = true;
                    }
                }
            }
        }
    }

    reset($need);
    foreach ($need as $scop => $rows) {
        foreach ($rows as $name => $d) {
            /* Add the variable to the client - but do not add the local
                    version if there is already a global version. */
            if (isset($need[$scop][$name][constMissingSiteVar])) {
                $gid = $sgid;
            } else if (isset($need[$scop][$name][constMissingHostVar])) {
                $gid = $hgid;
            }

            $vid  = $vars[$scop][$name][$gid]['vid'];
            $type = $vars[$scop][$name][$gid]['type'];
            $grev = $vars[$scop][$name][$gid]['revl'];
            $gval = $vars[$scop][$name][$gid]['valu'];

            if (isset($vars[$scop][$name]['stat'])) {
                $stat = $vars[$scop][$name]['stat'];
                $srev = $vars[$scop][$name]['srev'];
            } else if ($gid == $sgid) {
                $stat = constVarConfStateGlobal;
            } else {
                $stat = constVarConfStateLocal;
            }

            /* ValueMap.revl starts at 1 */
            $srev = 1;

            if ((isset($vars[$scop][$name][$hgid]['valu'])) &&
                ($gid == $sgid)
            ) {
                $lval = $vars[$scop][$name][$sgid]['valu'];
                $lrev = $vars[$scop][$name][$sgid]['revl'];
            } else {
                /* add it to the locals table for this machine. We have
                    to do this here because when other clients check in
                    they add all their variables to the globals and the
                    locals for THEIR machines, but not for the others.  */

                /* VarValues.revl starts at 2 */
                $lrev = 2;
                $lval = $gval;
                $xid  = create_value($env, $vid, $hgid, $lrev, $lval, $db);
                if ($xid) {
                    $env['lclw']++;
                    $env['chng'][] = "insert l:$name:$scop";
                }
            }
            SetOutgoingDescComponents($newDesc, $type, $grev, constGlobal, $gval);
            SetOutgoingDescComponents($newDesc, $type, $lrev, constLocal, $lval);
            SetOutgoingDescComponents($newDesc, $type, $srev, constState, $stat);

            /* Kludge to fix case of a variable name made of only spaces.
                       mysql will remove trailing spaces during an INSERT for
                       VARCHAR fields, so an empty string will be returned during
                       a SELECT. The client cannot handle an empty var name, so
                       replace it with a space if we find it. */
            $correctVarName = FixNameIfEmpty($name);

            $out[$scop][$correctVarName] = $newDesc;
            $env['dnld']++;
            $env['chng'][] = "dnload a:$correctVarName:$scop";
        }
    }
}



/*
 |  GetMachinesNeedSync
 |
 |  Gets a list of all the machines that have variables on the server that have
 |  been changed. The result is returned by the procedure, "qSite" is the
 |  site name, "qMachine" is the machine which is doing the updating right
 |  now, and "db" is the database.
 |
 |  23-May-2004 EWB
 |
 |    It turns out this doesn't work very well for large sites.
 |    The problem is that when there are dozens of machines,
 |    there's almost always some that are out of date and
 |    unreachable.
 |
 |  24-May-2004 EWB
 |
 |    Added a switch to turn this feature on or off as appropriate.
 |    It defaults to on, since that's what people are expecting.
 */

function GetMachinesNeedSync($qs, $qh, $db)
{
    $machines = array();
    $set = array();
    $chk = server_def('config_search', 0, $db);
    if ($chk) {
        $timeout = server_def('config_schtimeout', 10800, $db);
        $now = time();
        $sql = "select C.host from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R\n"
            . " where C.id = R.censusid\n"
            . " and C.site = '$qs'\n"
            . " and C.host != '$qh'\n"
            . " and R.stime > R.ctime\n"
            . " and (R.ctime+$timeout) > $now";
        $set = find_many($sql, $db);
    }
    if ($set) {
        foreach ($set as $key => $row) {
            /* we don't care about the item value, just the name */
            $host = $row['host'];
            $machines[$host] = 0;
        }
    }
    return $machines;
}


/*
    |  Loads the current state of this machine into
    |  memory ... returns constAppNoErr if successful
    |  or constErrServerTooBusy on failure.
    */


function load_vars(&$env, &$vars, $db)
{
    $aaaa = microtime();
    $mid  = $env['mid'];
    $cid  = $env['cid'];
    $hgid = $env['hgid'];
    $sgid = $env['sgid'];
    $rval = constErrServerTooBusy;

    $norm = 0;
    $sems = 0;
    $maps = 0;
    $olds = 0;

    $sql = "select V.name, V.scop, V.itype, V.varid,"
        . " G.mgroupid, X.valu, X.revl\n"
        . " from Variables as V,\n"
        . " VarValues as X,\n"
        . " MachineGroups as G\n"
        . " where X.varuniq = V.varuniq\n"
        . " and X.mgroupuniq = G.mgroupuniq\n"
        . " and G.mgroupid in ($sgid,$hgid)";

    $set = find_many($sql, $db);
    if (config_timed_out($env)) return $rval;

    foreach ($set as $key => $row) {
        $norm++;

        $name = $row['name'];
        $scop = $row['scop'];
        $gid  = $row['mgroupid'];
        $vars[$scop][$name][$gid]['sem'] = 0;
        $vars[$scop][$name][$gid]['old'] = 0;
        $vars[$scop][$name][$gid]['vid'] = $row['varid'];
        $vars[$scop][$name][$gid]['valu'] = $row['valu'];
        $vars[$scop][$name][$gid]['revl'] = $row['revl'];
        $vars[$scop][$name][$gid]['type'] = $row['itype'];
    }
    $sql = "select V.scop, V.itype, V.varid,\n"
        . " S.valu, S.revl, G.mgroupid,\n"
        . " concat(V.name,'Local') as name from\n"
        . " Variables as V,\n"
        . " SemClears as S,\n"
        . " Census as C,\n"
        . " MachineGroups as G\n"
        . " where S.censusuniq = C.censusuniq\n"
        . " and C.id = $mid\n"
        . " and S.varuniq = V.varuniq"
        . " and S.mgroupuniq = G.mgroupuniq";
    $set = find_many($sql, $db);
    if (config_timed_out($env)) return $rval;

    foreach ($set as $key => $row) {
        $sems++;

        $name = $row['name'];
        $scop = $row['scop'];
        $gid  = $row['mgroupid'];
        $vars[$scop][$name][$gid]['old'] = 0;
        $vars[$scop][$name][$gid]['sem'] = $row['varid'];
        $vars[$scop][$name][$gid]['vid'] = 0;
        $vars[$scop][$name][$gid]['valu'] = $row['valu'];
        $vars[$scop][$name][$gid]['revl'] = $row['revl'];
        $vars[$scop][$name][$gid]['type'] = constVblTypeInteger;
        $vars[$scop][$name]['srev'] = 1;
        $vars[$scop][$name]['stat'] = constVarConfStateLocalOnly;
    }

    $sql = get_state_settings_query($env, $db);
    $set = find_many($sql, $db);
    if (config_timed_out($env)) return $rval;

    foreach ($set as $key => $row) {
        $maps++;

        $name = $row['name'];
        $scop = $row['scop'];
        $vars[$scop][$name]['stat'] = $row['stat'];
        $vars[$scop][$name]['srev'] = $row['srev'];
    }

    /* Note that we would have to use a temporary table to filter out any
            InvalidVars entries that are properly defined in VarVersions for
            this specific version (vers=$vers AND vers.id IS NULL) so we will
            check one by one later. */
    $sql = "select * from InvalidVars\n"
        . " where censusid = $mid\n"
        . " or siteid = $cid";
    $set = find_many($sql, $db);
    if (config_timed_out($env))
        return constErrServerTooBusy;

    foreach ($set as $key => $row) {
        $olds++;

        /* Since this result will be compared with data coming from
                the client in an ALIST, we need to put it into the client
                canonical form. */
        $name = FixNameIfEmpty($row['name']);
        $scop = $row['scop'];
        $glob = $row['isglobal'];
        $invd = $row['invid'];
        $gid  = ($glob) ? $sgid : $hgid;

        /* Prevent overwriting with invalid vars values if there are already
                real values. */
        if ((!(isset($vars[$scop]))) || (!(isset($vars[$scop][$name]))) ||
            (!(isset($vars[$scop][$name][$gid])))
        ) {
            /* There is no real value for this variable, just an invalid
                    one.  However, we cannot just use the invalid value if this
                    client claims the variable actually exists. */
            $qn = safe_addslashes($name);
            $sql = "SELECT VarVersions.varversid FROM VarVersions LEFT "
                . "JOIN Variables ON (VarVersions.varuniq="
                . "Variables.varuniq) WHERE VarVersions.vers='"
                . $env['vers'] . "' AND Variables.name='" . $qn
                . "' AND Variables.scop=$scop";
            $thisRow = find_one($sql, $db);
            if (!$thisRow) {
                /* Variable is truly invalid for this particular client. */
                $vars[$scop][$name][$gid]['sem'] = 0;
                $vars[$scop][$name][$gid]['vid'] = 0;
                $vars[$scop][$name][$gid]['old'] = $invd;
                $vars[$scop][$name][$gid]['valu'] = $row['valu'];
                $vars[$scop][$name][$gid]['revl'] = $row['revl'];
                $vars[$scop][$name][$gid]['type'] = $row['itype'];
                if (!$glob) {
                    $vars[$scop][$name]['stat'] = $row['stat'];
                    $vars[$scop][$name]['srev'] = $row['srev'];
                }
            } else {
                logs::log(__FILE__, __LINE__, "config: filtering out variable $name:$scop "
                    . "for " . $env['site'] . ":" . $env['host'] . " ("
                    . $env['vers'] . ")", 0);
            }
        }
    }

    $host = $env['host'];
    $site = $env['site'];
    $msec = microtime_diff($aaaa, microtime());
    $secs = microtime_show($msec);
    $stat = "n:$norm,s:$sems,m:$maps,o:$olds";
    $text = "config: $host loaded ($stat) at $site in $secs";

    debug_note($text);
    logs::log(__FILE__, __LINE__, $text, 0);

    return constAppNoErr;
}



function process_varlist(&$env, &$set, &$out, $db)
{
    $aaaa = microtime();
    $mid  = $env['mid'];
    $cid  = $env['cid'];
    $hgid = $env['hgid'];
    $sgid = $env['sgid'];
    $host = $env['host'];
    $site = $env['site'];
    $time = $env['time'];
    $usec = $env['usec'];
    $vars = array();

    $rval = load_vars($env, $vars, $db);
    if ($rval != constAppNoErr) {
        return $rval;
    }

    $vmap = find_variables($db);
    $smap = find_semaphores($db);

    $norm = 0;
    $sems = 0;
    $olds = 0;

    $sql = "SELECT Variables.name, Variables.scop FROM VarValues"
        . " LEFT JOIN Variables ON (VarValues.varuniq=Variables.varuniq) "
        . "LEFT JOIN VarVersions ON (VarValues.varuniq=VarVersions.varuniq"
        . " AND VarVersions.vers='" . $env['vers'] . "') LEFT JOIN "
        . " MachineGroups ON (VarValues.mgroupuniq="
        . "MachineGroups.mgroupuniq) WHERE VarVersions.varversid IS NULL "
        . "AND MachineGroups.mgroupid IN (" . $env['hgid'] . ", "
        . $env['sgid'] . ")";
    $missingVars = find_many($sql, $db);

    reset($set);
    foreach ($set as $scop => $varList) {
        reset($varList);

        /* Go through each variable ... */
        foreach ($varList as $name => $varVal) {
            $createInvalid = 0;
            reset($missingVars);
            foreach ($missingVars as $key => $row) {
                if (($row['scop'] == $scop)
                    && (strcmp($row['name'], $name) == 0)
                ) {
                    $createInvalid = 1;
                }
            }
            $env['upld']++;

            $vid = @intval($vmap[$scop][$name]);
            $sem = @intval($smap[$scop][$name]);

            if ($vid) $norm++;
            if ($sem) $sems++;
            if ((!$vid) && (!$sem)) $olds++;

            debug_note("upload $name:$scop (v:$vid,s:$sem)");

            /* Get variable info in a convenient form. */

            $varType   = $varVal[constVarPackageType];
            $globalRev = $varVal[constVarPackageGlobalRev];
            $localRev  = $varVal[constVarPackageLocalRev];
            $stateRev  = $varVal[constVarPackageStateRev];
            $state     = $varVal[constVarPackageState];

            $haveUpdateGlobal = isset($varVal[constVarPackageGlobal]);
            $haveUpdateLocal  = isset($varVal[constVarPackageLocal]);
            $haveUpdateState  = isset($varVal[constVarPackageState]);
            $haveServerGlobal = isset($vars[$scop][$name][$sgid]);
            $haveServerLocal  = isset($vars[$scop][$name][$hgid]);
            $haveServerState  = isset($vars[$scop][$name]['stat']);

            $haveUpdateInfoForThisVar = 0;
            $upText = '';

            if ($createInvalid) {
                if ($haveUpdateGlobal) {
                    $xid = create_invalid($env, $varVal, $scop, $name, 1, $db);
                    if ($xid) {
                        $env['gblw']++;
                        $env['chng'][] = "create_invalid g:$name:$scop";
                    }
                }
                if ($haveUpdateLocal) {
                    $xid = create_invalid($env, $varVal, $scop, $name, 0, $db);
                    if ($xid) {
                        $env['lclw']++;
                        $env['chng'][] = "create_invalid l:$name:$scop";
                    }
                }
            }

            /* if there is a global value */
            if ($haveUpdateGlobal) {
                /* if server global doesn't exist */
                if (!$haveServerGlobal) {
                    /* create server global */
                    $xid = 0;
                    $rev = $varVal[constVarPackageGlobalRev];
                    $val = $varVal[constVarPackageGlobal];
                    $txt = "config: create g:$name:$scop '$val' for $host at $site.";
                    debug_note($txt);
                    //  logs::log(__FILE__, __LINE__, $txt,0);
                    if ($vid) {
                        $xid = create_value($env, $vid, $sgid, $rev, $val, $db);
                    }
                    if ($sem) {
                        $xid = create_semaphore($env, $sem, $sgid, $rev, $val, $db);
                    }
                    if ((!$vid) && (!$sem)) {
                        $xid = create_invalid($env, $varVal, $scop, $name, 1, $db);
                    }
                    if ($xid) {
                        $env['gblw']++;
                        $env['chng'][] = "insert g:$name:$scop";
                    }
                } else {
                    /* decide which direction to do the update */
                    $serverGlobalRev = $vars[$scop][$name][$sgid]['revl'];
                    /* if client rev > server global rev */
                    if ($globalRev > $serverGlobalRev) {
                        $direction = constUpdateToHere;
                    } else if ($globalRev < $serverGlobalRev) {
                        $direction = constUpdateToThere;
                    } else {
                        /* check if values are different */
                        $updateGlbVal = $varVal[constVarPackageGlobal];
                        $serverGlbVal = $vars[$scop][$name][$sgid]['valu'];
                        $direction = CompareValues(
                            $updateGlbVal,
                            $serverGlbVal,
                            $varType
                        );
                    }
                    debug_note("upload $name:$scop (v:$vid,d:$direction)");

                    if ($direction == constUpdateToHere) {
                        /* update server global */
                        $num = 0;
                        $val = $varVal[constVarPackageGlobal];
                        $rev = $varVal[constVarPackageGlobalRev];

                        //     $txt = "config: update g:$name:$scop '$val'"
                        //          . " rev $rev for $host at $site.";
                        //     debug_note($txt,0);
                        //     logs::log(__FILE__, __LINE__, $txt,0);
                        $tryInvalid = 0;
                        if ($vid) {
                            $num = update_value_publish(
                                $env,
                                $varVal,
                                $vid,
                                1,
                                $db
                            );
                            if ($num == 0) {
                                /* It's possible this is an invalid
                                        variable for this machine but the
                                        variable is actually valid for other
                                        machines. */
                                $tryInvalid = 1;
                            }
                        }
                        if ($sem) {
                            $num = update_semaphore($env, $sem, $sgid, $val, $rev, $db);
                        }
                        if (((!$vid) && (!$sem)) || (($tryInvalid) && (!$sem))) {
                            $num = update_invalid($env, $varVal, $scop, $name, 1, $db);
                        }
                        if ($num) {
                            $env['gblw']++;
                            $env['chng'][] = "update g:$name:$scop";
                        }
                    } else if ($direction == constUpdateToThere) {
                        /* reinitialize the variable package */
                        if (!$haveUpdateInfoForThisVar) {
                            $newVal = array();
                        }
                        //                  $txt = "config: update client g:$name:$scop"
                        //                       . " for $host at $site.";
                        //                  logs::log(__FILE__, __LINE__, $txt,0);
                        /* put server global in client update list */
                        SetOutgoingDescComponents(
                            $newVal,
                            $varType,
                            $serverGlobalRev,
                            constGlobal,
                            $vars[$scop][$name][$sgid]['valu']
                        );
                        $haveUpdateInfoForThisVar = 1;
                        $upText .= 'g';
                    } /* else if direction */
                } /* else if haveServerGlobal */
            } /* if haveUpdateGlobal */


            /* take care of the local value */
            /* Note that we are currently assuming that the local update
                    info and the state update info come as a pair.  We'll
                    check this for now and generate an error if it isn't
                    true. */
            if ($haveUpdateLocal != $haveUpdateState) {
                $txt = "Local and state info mismatch,"
                    . " machine=$host, scrip=$scop,"
                    . " varName=$name";
                logs::log(__FILE__, __LINE__, $txt, 0);
            }
            if ($haveUpdateLocal) {
                /* if server local doesn't exist */
                if (!$haveServerLocal) {
                    /* create server local */
                    $xid = 0;
                    $rev = $varVal[constVarPackageLocalRev];
                    $val = $varVal[constVarPackageLocal];
                    $txt = "config: create l:$name:$scop '$val' for $host at $site";
                    debug_note($txt);
                    //   logs::log(__FILE__, __LINE__, $txt,0);
                    if ($vid) {
                        $xid = create_value($env, $vid, $hgid, $rev, $val, $db);
                    }
                    if ($sem) {
                        $xid = create_semaphore($env, $sem, $hgid, $rev, $val, $db);
                    }
                    if ((!$vid) && (!$sem)) {
                        $xid = create_invalid($env, $varVal, $scop, $name, 0, $db);
                    }
                    if ($xid) {
                        $env['lclw']++;
                        $env['chng'][] = "insert l:$name:$scop";
                    }
                    /* Set up the $locals entry because we use it
                            below, and it isn't initialized if the variable
                            wasn't on the server. */
                    $vars[$scop][$name][$hgid]['valu'] = $val;
                    $vars[$scop][$name][$hgid]['revl'] = $rev;
                    $vars[$scop][$name]['stat'] = $state;
                    $vars[$scop][$name]['srev'] = $stateRev;
                } else {

                    /* decide which direction to do the update */
                    $serverLocalRev =
                        $vars[$scop][$name][$hgid]['revl'];
                    /* decide which way the update needs to happen */
                    if ($localRev > $serverLocalRev) {
                        $direction = constUpdateToHere;
                    } else if ($localRev < $serverLocalRev) {
                        $direction = constUpdateToThere;
                    } else {
                        /* compare values */
                        $serverLocalVal =
                            $vars[$scop][$name][$hgid]['valu'];
                        $updateLocalVal =
                            $varVal[constVarPackageLocal];
                        $direction = CompareValues(
                            $updateLocalVal,
                            $serverLocalVal,
                            $varType
                        );
                    }
                    if ($direction == constUpdateToHere) {
                        /* update server local */
                        $num = 0;
                        $rev = $varVal[constVarPackageLocalRev];
                        $val = $varVal[constVarPackageLocal];
                        $txt = "config: update l:$name:$scop '$val'"
                            . " rev $rev for $host at $site.";
                        debug_note($txt);
                        //  logs::log(__FILE__, __LINE__, $txt,0);
                        $tryInvalid = 0;
                        if ($vid) {
                            $num = update_value_publish(
                                $env,
                                $varVal,
                                $vid,
                                0,
                                $db
                            );
                            if ($num == 0) {
                                $tryInvalid = 1;
                            }
                        }
                        if ($sem) {
                            $num = update_semaphore($env, $sem, $hgid, $val, $rev, $db);
                        }
                        if (((!$vid) && (!$sem)) || (($tryInvalid) && (!$sem))) {
                            $num = update_invalid($env, $varVal, $scop, $name, 0, $db);
                        }
                        if ($num) {
                            $env['lclw']++;
                            $env['chng'][] = "update l:$name:$scop";
                        }
                    } else if ($direction == constUpdateToThere) {
                        /* reinitialize the variable package */
                        if (!$haveUpdateInfoForThisVar) {
                            $newVal = array();
                        }
                        $txt = "config: update client l:$name:$scop for"
                            . " $host at $site.";
                        debug_note($txt);
                        //  logs::log(__FILE__, __LINE__, $txt,0);
                        $rev = $vars[$scop][$name][$hgid]['revl'];
                        $val = $vars[$scop][$name][$hgid]['valu'];
                        SetOutgoingDescComponents(
                            $newVal,
                            $varType,
                            $rev,
                            constLocal,
                            $val
                        );
                        $haveUpdateInfoForThisVar = 1;
                        $upText .= 'l';
                    } /* else if direction */
                } /* else if haveServerLocal */
            } /* if haveUpdateLocal */

            /* take care of the state last */
            if ($haveUpdateState) {
                /* Figure out which group the variable should be in. */
                switch ($state) {
                    default:
                    case constVarConfStateGlobal:
                        $stateGroup = $sgid;
                        break;
                    case constVarConfStateLocal:
                    case constVarConfStateLocalOnly:
                        $stateGroup = $hgid;
                        break;
                }

                /* If the server state needs to be created, do that. */
                if (!$haveServerState) {
                    /* Don't create a ValueMap entry if there is no
                            variable.  For example, this is true for semaphore
                            clears or invalid variables. */
                    if ($vid) {
                        $newID = create_vmap(
                            $env,
                            $vid,
                            $stateGroup,
                            $state,
                            $stateRev,
                            $db
                        );
                        if (!($newID)) {
                            FixInvalidState(
                                $env,
                                $name,
                                $scop,
                                $vid,
                                $state,
                                $stateRev,
                                $db
                            );
                        }
                    }
                } else {
                    /* decide which way the update needs to happen */
                    $serverState = $vars[$scop][$name]['stat'];
                    $serverStateRev = $vars[$scop][$name]['srev'];
                    if ($stateRev > $serverStateRev) {
                        $direction = constUpdateToHere;
                    } else if ($stateRev < $serverStateRev) {
                        $direction = constUpdateToThere;
                    } else {
                        /* tie breaker. choose the larger value */
                        if ($serverState == $state) {
                            $direction = constUpdateNone;
                        } else if ($state > $serverState) {
                            $direction = constUpdateToHere;
                        } else /* $state < $serverState */ {
                            $direction = constUpdateToThere;
                        }
                    }

                    /* do the actual update */
                    if ($direction == constUpdateToHere) {
                        $num = 0;
                        $tryInvalid = 0;
                        if ($vid) {
                            $num = update_state($env, $vid, $state, $stateRev, $db);
                            if ($num == 0) {
                                /* Could be invalid, try inv_state next */
                                $tryInvalid = 1;
                            }
                        } else {
                            $tryInvalid = 1;
                        }
                        if ($tryInvalid) {
                            $num = inv_state($env, $name, $scop, $state, $stateRev, $db);
                        }
                        if ($num) {
                            $env['lclw']++;
                            $env['chng'][] = "state ($state) l:$name:$scop";
                        }
                    } else if ($direction == constUpdateToThere) {
                        /* reinitialize the variable package */
                        if (!$haveUpdateInfoForThisVar) {
                            $newVal = array();
                        }
                        SetOutgoingDescComponents(
                            $newVal,
                            $varType,
                            $serverStateRev,
                            constState,
                            $serverState
                        );
                        $haveUpdateInfoForThisVar = 1;
                        $upText .= 's';
                    } /* else if direction */
                }
            } /* if haveUpdateState */

            /* If we have some update information, add it to the list
                    that we will return. */
            if ($haveUpdateInfoForThisVar) {
                /* Kludge to fix case of a name that only has spaces in it.
                       mysql will remove all trailing spaces during an INSERT
                       when handling VARCHAR fields. So if we find an empty
                       string for a name, convert it to a space, because the
                       client won't handle an empty string for a name. */
                $correctVarName = FixNameIfEmpty($name);

                $out[$scop][$correctVarName] = $newVal;
                $env['dnld']++;
                $env['chng'][] = "dnload $upText:$correctVarName:$scop";
            }
        } /* variable loop */

        if (config_timed_out($env)) {
            return constErrServerTooBusy;
        }
    } /* scrip loop */

    $stat = "n:$norm,s:$sems,o:$olds";
    $text = "config: $host statistics ($stat) at $site";
    debug_note($text);
    logs::log(__FILE__, __LINE__, $text, 0);

    debug_time("$host update loop", $aaaa);
    /* Finally, check for those scrips and variables that were not in the
           package, which means we have to add them to make the checksums match
           on both client and server. */
    $asec = microtime();
    AddVarsNotOnClient($env, $out, $set, $vars, $db);
    debug_time('AddVarsNotOnClient', $asec);
    return $rval;
}


/* config_data
        Handle a configuration logging operation.  Return $usize as the number
        of items uploaded, $wsize as the number of items written to the
        database, and $dsize as the number of items returned to the client.
        Return $retList as the ALIST array that should be returned to the
        client.  $list is the ALIST data for the logging.  $site and $machine
        define the machine doing the logging.  $time is the POSIX time when the
        operation started.  $db is the config database.
    */

function config_data(&$env, &$retList, $db)
{
    /* Get the update to apply. */

    $xxxx = microtime();
    $host = $env['host'];
    $vList = fully_parse_alist($env['list']);
    debug_time("$host fully_parse_alist", $xxxx);
    $rval = constAppNoErr;

    /* Get the server variables into arrays.  Eric had done it this
            way rather than doing a query for each variable, so I'll
            continue ... */

    /* $retVars accumulates the variables we return to the client. */
    $retVars = array();

    /* If there was a variable package from the client, handle it. */
    if (isset($vList[constVarPackageVars])) {
        $rval = process_varlist($env, $vList[constVarPackageVars], $retVars, $db);
    }

    /* Set up the return value. */
    $retList = array();

    /* If there are any variables to return, add those. */
    if ($retVars) {
        $retList[constVarPackageVars] = $retVars;
    }

    /* Now check if there are any machines that need to synchronize
            with the server, and send them back as part of the ALIST.  */
    if (isset($vList[constVarPackageMachines])) {
        $qh = safe_addslashes($env['host']);
        $qs = safe_addslashes($env['site']);
        $machines = GetMachinesNeedSync($qs, $qh, $db);
        if ($machines) {
            $retList[constVarPackageMachines] = $machines;
        }
    }

    return $rval;
}



/* VARS_ApplyPackage
    Apply the changes in "applyVal".  These will be for the machine in
    "machineID".  Return the new changes that need to be applied at the other
    end in "newVal".  Return the list of changes for logging in "logList".

    "logList" is not currently implemented, so it's just returned NULL
    (untouched).

extern ERRSTAT VARS_ApplyPackage(MACHINE mID, PPALIST newVal, PPALIST logList,
    PALIST machineID, PALIST applyVal)
*/

/* Note that the "applyVal" parameter is NOT passed through the "$args" array
    because it can be very large.  See the note in build_args in server.php
    for more detail. */

function VARS_ApplyPackage(&$args)
{
    $usec = $args['usec'];
    $time = time();
    debug_time('>> VARS_ApplyPackage', $usec);
    $rval = constErrDatabaseNotAvailable;
    $num  = 0;
    $uuid = '';
    $site = '';
    $host = '';
    $vers = 'Unknown';
    $code = 0;
    $mid  = 0;
    $cid  = 0;
    $more = true;
    $env  = array();

    /* Get the parts of the machine ID. */
    $mach = fully_parse_alist($args['valu'][3]);
    if ($mach) {
        $host = $mach[constInfoMachine];
        $site = $mach[constInfoSite];
        if (isset($mach[constInfoVersion])) {
            $vers = $mach[constInfoVersion];
        }
        if (isset($mach[constInfoUUID])) {
            $uuid = $mach[constInfoUUID];
        }
        if (isset($mach[constInfoToken])) {
            $code = $mach[constInfoToken];
        }
    } else {
        $ip  = $_SERVER['REMOTE_ADDR'];
        $txt = "config: invalid alist from unknown machine at $ip.";
        logs::log(__FILE__, __LINE__, $txt, 0);
        debug_note($txt);
        $args['valu'][1] = '0#';
        $args['valu'][2] = '0#';
        $args['valu'][3] = '';
        $args['valu'][4] = '';

        // try again later
        $args['olog'] = 0;
        $args['oxml'] = 1;
        $args['rval'] = constErrServerTooBusy;

        return;
    }


    $logText = '';
    $retList = array();

    if (pfDisableConf) {
        $logText = "ignoring upload";
        $args['valu'][1] = '0#';
        $rval = constErrServerNoSupport;
        $more = false;
    }

    if ($more) {
        $db = db_code('db_cor');
        if (!$db) {
            $more = false;
            $rval = constErrDatabaseNotAvailable;
            $logText = 'mysql unavailable';
        }
    }

    if ($more) {
        $temp = constAppNoErr;
        identity($site, $host, $uuid, $rval, $logText, $more);
    }

    if ($more) {
        $returnNow = 0;
        $temp = census_manage($site, $host, $uuid, 0, $db);
        switch ($temp) {
            case constAppNoErr:
                break;
            case constErrServChangeUUID:
                /* We need to change the UUID, but track when we last told
                    the client to change it's UUID.  We track this to prevent
                    the client from generating more than one UUID (which would
                    force a name change).  If we told the client to generate a
                    new UUID less than 10 minutes ago, do not tell it again. */
                $retList = fully_parse_alist(get_alist_param(4));
                $changeVar = 1;
                if (isset($retList['vars']['43'][constGenUUIDTimeoutVar])) {
                    $changeVar = 0;
                    $now = time();
                    if ($retList['vars']['43'][constGenUUIDTimeoutVar][constVarPackageLocal] < ($now - 600)) {
                        logs::log(__FILE__, __LINE__, "config: updating UUID button click at "
                            . "$now, was clicked " . $retList['vars']['43'][constGenUUIDTimeoutVar][constVarPackageLocal]
                            . " for $site:$host", 0);
                        $changeVar = 1;
                        $retList['vars']['43'][constGenUUIDTimeoutVar][constVarPackageLocal] = $now;
                        $retList['vars']['43'][constGenUUIDTimeoutVar][constVarPackageLocalRev] = $retList['vars']['43'][constGenUUIDTimeoutVar][constVarPackageLocalRev]
                            + 1;
                    } else {
                        logs::log(__FILE__, __LINE__, "config: suspending UUID button click at "
                            . "$now, was clicked " . $retList['vars']['43'][constGenUUIDTimeoutVar][constVarPackageLocal]
                            . " for $site:$host", 0);
                    }
                } else {
                    $now = time();
                    $retList['vars']['43'][constGenUUIDTimeoutVar] = array();
                    $retList['vars']['43'][constGenUUIDTimeoutVar][constVarPackageType] = constVblTypeInteger;
                    $retList['vars']['43'][constGenUUIDTimeoutVar][constVarPackageLocal] = $now;
                    /* Note that the local revision must be 2 to override the
                        client. */
                    $retList['vars']['43'][constGenUUIDTimeoutVar][constVarPackageLocalRev] = 2;
                    logs::log(__FILE__, __LINE__, "config: initial UUID button click at "
                        . "$now for $site:$host", 0);
                }
                if ($changeVar == 1) {
                    if (isset($retList['vars']['43']['S00043ResetClientUUIDa'])) {
                        if (isset($retList['vars']['43']['S00043ResetClientUUIDa'][constVarPackageStateRev])) {
                            $retList['vars']['43']['S00043ResetClientUUIDa'][constVarPackageStateRev] =  $retList['vars']['43']['S00043ResetClientUUIDa'][constVarPackageStateRev] + 1;
                            $retList['vars']['43']['S00043ResetClientUUIDa'][constVarPackageState] =
                                constVarConfStateLocal;
                        }
                    }
                    if (isset($retList['vars']['43']['S00043ResetClientUUIDLocal'])) {
                        $retList['vars']['43']['S00043ResetClientUUIDLocal'][constVarPackageLocal] = -1;
                        $retList['vars']['43']['S00043ResetClientUUIDLocal'][constVarPackageLocalRev] = $retList['vars']['43']['S00043ResetClientUUIDLocal'][constVarPackageLocalRev] + 1;
                    }
                    if (isset($retList['vars']['43']['S00043ResetClientUUIDaLocal'])) {
                        $retList['vars']['43']['S00043ResetClientUUIDaLocal'][constVarPackageLocal] = -1;
                        $retList['vars']['43']['S00043ResetClientUUIDaLocal'][constVarPackageLocalRev] = $retList['vars']['43']['S00043ResetClientUUIDaLocal'][constVarPackageLocalRev] + 1;
                    }
                    if (isset($retList['vars']['43']['S00043ResetClientUUIDbLocal'])) {
                        $retList['vars']['43']['S00043ResetClientUUIDbLocal'][constVarPackageLocal] = -1;
                        $retList['vars']['43']['S00043ResetClientUUIDbLocal'][constVarPackageLocalRev] = $retList['vars']['43']['S00043ResetClientUUIDbLocal'][constVarPackageLocalRev] + 1;
                    }
                }
                $returnNow = 1;

                if ($changeVar == 0) {
                    $rval = constErrServerTooBusy;
                    $more = false;
                    $returnNow = 0;
                    $retList = array();
                }
                break;
            case constErrServChangeName:
                /* Return the applicable ALIST to change the friendly name */
                $retList = fully_parse_alist(get_alist_param(4));
                if (isset($retList['vars']['43']['MachineID'])) {
                    $retList['vars']['43']['MachineID'][constVarPackageStateRev] =  $retList['vars']['43']['MachineID'][constVarPackageStateRev] + 1;
                    $retList['vars']['43']['MachineID'][constVarPackageState]
                        = constVarConfStateLocal;
                    $retList['vars']['43']['MachineID'][constVarPackageLocalRev] =  $retList['vars']['43']['MachineID'][constVarPackageLocalRev] + 1;
                    $retList['vars']['43']['MachineID'][constVarPackageLocal]
                        = 2;
                }
                if (isset($retList['vars']['43']['FriendlyName'])) {
                    $retList['vars']['43']['FriendlyName'][constVarPackageStateRev] =  $retList['vars']['43']['FriendlyName'][constVarPackageStateRev] + 1;
                    $retList['vars']['43']['FriendlyName'][constVarPackageState] = constVarConfStateLocal;
                    $retList['vars']['43']['FriendlyName'][constVarPackageLocalRev] =  $retList['vars']['43']['FriendlyName'][constVarPackageLocalRev] + 1;
                    if (isset($uuid)) {
                        $retList['vars']['43']['FriendlyName'][constVarPackageLocal] = $uuid;
                    } else {
                        $retList['vars']['43']['FriendlyName'][constVarPackageLocal] = md5(uniqid(''));
                    }
                }
                $returnNow = 1;
                break;
            default:
                $rval = $temp;
                $more = false;
                break;
        }

        if ($returnNow) {
            $args['valu'][1] = fully_make_alist($retList);
            $args['valu'][2] = '1#name#PSTRING#value#';
            $args['valu'][3] = '';
            $args['valu'][4] = '';
            $args['olog'] = 0;
            $args['oxml'] = 1;
            $args['rval'] = constAppNoErr;
            return;
        }
    }

    if ($more) {
        $row = find_census_uuid($uuid, $db);
        if ($row) {
            find_groups($row, $mid, $cid, $sgid, $hgid, $db);
        } else {
            $more = false;
            $rval = constErrDatabaseNotAvailable;
            $logText = 'census error';
        }
    }
    if ($more) {
        $more = (($mid) && ($cid) && ($sgid) && ($hgid));
        if ($more) {
            debug_note("groups (m:$mid,c:$cid,s:$sgid,h:$hgid)");
        } else {
            $rval = constErrDatabaseNotAvailable;
            $logText = 'group error';
        }
    }

    if ($more) {
        /* Check to see if we are too busy. */
        $max = server_def('max_config_logs', 10, $db);
        $num = count_config_updates($db, $time);
        if ($max < $num + 1) {
            /* Note that the special case of max being zero is
                    explicitly allowed and expected. */
            $more = false;
            $rval = constErrServerTooBusy;
            $logText = "server too busy (n:$num,m:$max)";
        }
    }
    if ($more) {
        $env['mid']  = $mid;
        $env['cid']  = $cid;
        $env['site'] = $site;
        $env['host'] = $host;
        $env['vers'] = $vers;
        $env['uuid'] = $uuid;
        $env['time'] = $time;
        $env['usec'] = $usec;
        $env['sgid'] = $sgid;
        $env['hgid'] = $hgid;
        $xxxx = microtime();
        $prov = claim_config_machine($env, $db);
        debug_time("$host claim_config_machine", $xxxx);
        if ($prov) {
            /* Not exactly right but it will do for now. */
            $more = false;
            $rval = constErrServerTooBusy;
            $date = date('m/d H:i:s', $prov);
            $logText = "pending update ($date)";
        }
    }
    if ($more) {
        $xxxx = microtime();
        $list = &get_alist_param(4);
        debug_time("$host get_alist_param", $xxxx);
        $set = array();
        $env['list'] = &$list;
        $env['upld'] = 0;
        $env['dnld'] = 0;
        $env['gblw'] = 0;
        $env['lclw'] = 0;
        $env['chng'] = $set;

        $pid  = getmypid();
        $alen = strlen($env['list']);
        $acts = 'unknown';
        $stat = "n:$num,p:$pid,l:$alen";
        $rval = config_data($env, $retList, $db);
        $set  = $env['chng'];

        if (($set) && (safe_count($set) <= 50)) {
            reset($set);
            foreach ($set as $key => $msg) {
                $txt = "config: $host $msg at $site";
                debug_note($txt);
                logs::log(__FILE__, __LINE__, $txt, 0);
            }
        }

        $usize = $env['upld'];
        $dsize = $env['dnld'];
        $wsize = $env['gblw'] + $env['lclw'];
        update_cache($env, $db);
        $logs  = (($usize) || ($dsize));
        if ($logs) {
            $acts = 'logging';
            $stat = "u:$usize,w:$wsize,d:$dsize,n:$num,p:$pid,l:$alen";
        }
        if (isset($retList[constVarPackageMachines])) {
            $mach = safe_count($retList[constVarPackageMachines]);
            if ($logs) {
                $acts = "log/query";
                $stat = "m:$mach,$stat";
            } else {
                $acts = 'query';
                $stat = "m:$mach,n:$num,p:$pid,l:$alen";
            }
        }
        $logText = "$acts ($stat)";
        /* Note that unlike asset logging, we just always free
                up the machine, even if the operation timed out,
                because there is no "cleanup" to do. */
        unclaim_config_machine($mid, $db);
    }

    $msec = microtime_diff($usec, microtime());
    $secs = microtime_show($msec);
    $text = "config: $host $logText at $site in $secs";
    logs::log(__FILE__, __LINE__, $text, 0);
    debug_note($text);
    $args['valu'][1] = fully_make_alist($retList);

    /* Return short ALIST for logList for now (need to implement this)
            (problem with returning NULL ALISTs) */
    $args['valu'][2] = '1#name#PSTRING#value#';

    /* Don't bother returning the ALISTs (slight traffic optimization). */
    $args['valu'][3] = '';
    $args['valu'][4] = '';

    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = $rval;
    // $args returned to caller by reference
    debug_time('<< VARS_ApplyPackage', $usec);
}

function FixInvalidState(&$env, $name, $scop, $vid, $state, $stateRev, $db)
{
    /* It is possible that the state of the invalid variable actually
            did change, and InvalidVars no longer has accurate information.
            In this case, force InvalidVars to match the correct state. */
    $qn = safe_addslashes($name);
    $sql = "UPDATE InvalidVars SET stat=$state, srev=$stateRev WHERE "
        . "name='$qn' AND scop=$scop AND (stat!=$state OR srev!=$stateRev)"
        . " AND censusid=" . $env['mid'];
    $res = command($sql, $db);
    $num = affected($res, $db);
    if ($num > 0) {
        $env['lclw'] += $num;
        $env['chng'][] = "fixinvstate ($state,$num) l:$name:$scop";
    }
}


/* VARS_GetMachinesNeedSync

        This is a fairly special case, which is why it is written in PHP
        instead of C.  Server definitions and options are all defined in the
        PHP code only, and this primitive makes heavy use of these options.
        Therefore, it makes more sense to add it to the PHP code rather than
        have the same thing defined in two different places.

    extern ERRSTAT VARS_GetMachinesNeedSync(MACHINE mID, PPALIST machines,
        PALIST machineID);
    */
function VARS_GetMachinesNeedSync(&$args)
{
    $rval = constErrDatabaseNotAvailable;
    $site = '';
    $host = '';
    $more = true;

    /* Get the parts of the machine ID. */
    $mach = fully_parse_alist($args['valu'][2]);
    if ($mach) {
        $host = $mach[constInfoMachine];
        $site = $mach[constInfoSite];
    } else {
        $ip  = $_SERVER['REMOTE_ADDR'];
        $txt = "config: invalid alist from unknown machine at $ip.";
        logs::log(__FILE__, __LINE__, $txt, 0);
        debug_note($txt);
        $args['valu'][1] = '0#';

        // try again later
        $args['olog'] = 0;
        $args['oxml'] = 1;
        $args['rval'] = constErrServerTooBusy;

        return;
    }

    $retList = array();

    $db = db_code('db_cor');
    if (!$db) {
        $more = false;
        $rval = constErrDatabaseNotAvailable;
    }

    if ($more) {
        $qh = safe_addslashes($host);
        $qs = safe_addslashes($site);
        $retList = GetMachinesNeedSync($qs, $qh, $db);
        $rval = constAppNoErr;
    }

    $args['valu'][1] = fully_make_alist($retList);
    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = $rval;
}
