<?php

/*
Revision history:

Date        Who     What
----        ---     ----
24-May-04   BTE     Initial implementation.
25-Jun-04   BTE     Bugfixes to work correctly with client.
28-Jun-04   BTE     To handle errors with patches, a new patch action is no
                    longer required for INST_SetStatus.
29-Jun-04   BTE     Enhanced INST_SetStatus to handle 6-digit codes.
 5-Jul-04   BTE     Removed lots of checking before inserting or updating
                    existing table rows.
 6-Jul-04   BTE     Fixed a problem in INST_SetStatus where prior status data
                    would be erased.
 7-Jul-04   EWB     Set patch_dirty when insert new patch.
 8-Jul-04   BTE     Added lasterrordate to INST_SetStatus, set patch_dirty to
                    occur when a new patch is inserted from any machine.
 8-Jul-04   BTE     Added update_census calls to all RPC's, added database
                    code, fixed lots of faulty error returns.
 9-Jul-04   EWB     Moved some constants and routines into l-rlib.php.
15-Jul-04   EWB     Uses new database, update softinst.Machine.lastconact
15-Jul-04   EWB     error_log doesn't want/need a trailing newline.
16-Jul-04   BTE     Handle new fields, removed old fields, update PatchStatus
                    if necessary in INST_SendPatches.
16-Jul-04   BTE     Added in constants for INST_GetPatches, made
                    INST_GetPatches always return the default patch config in
                    addition to any other patches.
22-Jul-04   BTE     Removed some unused constants.
26-Jul-04   BTE     Updated to use new scheduling mechanism.
 2-Aug-04   BTE     Fixed bug in INST_CheckWUTimes where the wuconfig time
                    would always return 0.
 3-Aug-04   EWB     added more debug information to log files.
 5-Aug-04   BTE     Corrected a bug in INST_CheckWUTimes so that it always
                    will return UINT32s for times.
 5-Aug-04   BTE     Each RPC call now updates the softinst.Machine table.
 5-Aug-04   BTE     Replaced find_correct_wconfig and find_correct_pconfig
                    with search_wconfig and search_pconfig.
 5-Aug-04   BTE     INST_SendPatches now sets the lastchange field in
                    softinst.PatchStatus to the current server time.
 5-Aug-04   BTE     Removed unnecessary addslashes.
12-Aug-04   BTE     Enhanced INST_GetWUConfig to return the default patch
                    configuration also.
12-Aug-04   BTE     Fixed INST_CheckWUTimes to work without PatchStatus
                    records.
16-Aug-04   BTE     Renamed find_wuconfigcache_row to find_machine_row, added
                    error condition to INST_CheckWUTimes, added special time
                    constant to be returned when the machine groups are not set
                    up yet.
16-Aug-04   BTE     Added rpc call INST_GetDefaultConfig, added extra
                    computation to INST_CheckWUTimes.
18-Aug-04   BTE     INST_CheckWUTimes now computes all three configuration
                    timestamps everytime it runs.
19-Aug-04   BTE     INST_GetWUConfig now uses the Machine table.
19-Aug-04   BTE     Minor fix to ensure INST_CheckWUTimes returns integer
                    values.
 1-Sep-04   BTE     Added recompute_patches_by_machine.
 6-Sep-04   BTE     Handle uninstallable patches.
10-Sep-04   BTE     Return update cache settings to the client in
                    INST_GetWUConfig.
13-Sep-04   BTE     Removed references to PatchConfig.schedfail.
24-Sep-04   BTE     Added machine group and patch group strings to
                    INST_GetDefaultConfig, INST_GetWUConfig, and
                    INST_GetPatches.
 7-Oct-04   EWB     grep 'patch: host' /var/log/php/php.log now works.
18-Oct-04   BTE     Report patch: added message no longer applies, there is
                    already a created message available.  Fix for bug 2349:
                    server needs to handle non-unique titles.
27-Oct-04   BTE     If the status has not changed, do not update last install
                    field in PatchStatus.
03-Nov-04   BTE     Additional logging info for default pconfigid.
02-Dec-04   BTE     Small bugfix in INST_GetPatches, additional logging
                    information for some RPC calls.
28-Dec-04   BTE     Added patch install chaining and machine restart policy.
29-Dec-04   BTE     Moved install chaining to the machine configuration.
05-Jan-05   BTE     INST_SetStatus now sets the download date equal to the
                    install date if the downloaded date is 0 and the install
                    date is non-zero.
17-Jan-05   BTE     INST_SetStatus was not inserting the uninstall date to the
                    database.
18-Jan-05   BTE     Small bugfix in recompute_patches_by_machine where a newer
                    PatchStatus.lastchange could be overwritten with an older
                    time.
09-Feb-05   BTE     Fix for bug 2563: incorrect patch configuration can be sent
                    to the client.
24-Mar-05   EWB     New census logging code for automatic detection of 
                    duplicate uuids.
31-Oct-05   BTE     Removed unused RPC calls.
10-Nov-05   BTE     Updated to use the new census_manage function.
15-May-06   BTE     Bug 2967: Handle duplicate UUIDs and remove migration for
                    pre-2.2 clients.
26-Aug-06   BTE     Bug 3601: Superseded status can be misleading in MUM.
20-Sep-06   BTE     Bug 2826: Make MUM approve/decline wizards a little easier
                    to use (not so large).
09-Sep-06   BTE     Prevent older clients from eliminating type information.
09-Dec-06   BTE     Bug 3842: Make mandatory an update attribute.
15-Dec-06   BTE     Bug 3957: Fix inconsistencies in PatchStatus table.

*/

/* For ease of implementation these match the definitions in inst.h on the
        client. */

/* Constant data types */
define('constWUTimeConfig',                 'configtime');
define('constWUTimePatch',                  'patchtime');
define('constWUTimeDefault',                'deftime');

define('constWUConfigManagement',           'management');
define('constWUConfigInstallDay',           'installday');
define('constWUConfigInstallHour',          'installhour');
define('constWUConfigNewPatches',           'newpatches');
define('constWUConfigPatchSource',          'patchsource');
define('constWUConfigServerURL',            'serverurl');
define('constWUConfigPropagate',            'propagate');
define('constWUConfigCache',                'updatecache');
define('constWUConfigCacheSeconds',         'cacheseconds');
define('constWUConfigMachineGroup',         'machinegroup');
define('constWUConfigRestart',              'restart');
define('constWUConfigChain',                'chain');
define('constWUConfigChainSeconds',         'chainseconds');

define('constWUPatchItemID',                'itemid');
define('constWUPatchAction',                'patchaction');
define('constWUPatchStatus',                'patchstatus');

define('constWUNewPatchItemID',             'itemid');
define('constWUNewPatchName',               'name');
define('constWUNewPatchDate',               'date');
define('constWUNewPatchSize',               'size');
define('constWUNewPatchDesc',               'patchdesc');
define('constWUNewPatchParams',             'params');
define('constWUNewPatchClient',             'clientfile');
define('constWUNewPatchServer',             'serverfile');
define('constWUNewPatchCRC',                'crc');
define('constWUNewPatchComponent',          'component');
define('constWUNewPatchPlatform',           'platform');
define('constWUNewPatchProcessor',          'processor');
define('constWUNewPatchOSMajor',            'osmajor');
define('constWUNewPatchOSMinor',            'osminor');
define('constWUNewPatchOSBuild',            'osbuild');
define('constWUNewPatchSPMajor',            'spmajor');
define('constWUNewPatchSPMinor',            'spminor');
define('constWUNewPatchPriority',           'prio');
define('constWUNewPatchPrioH',              'priohidden');
define('constWUNewPatchEULA',               'eula');
define('constWUNewPatchStatus',             'status');
define('constWUNewPatchClientTime',         'clienttime');
define('constWUNewPatchMSName',             'msname');
define('constWUNewPatchTitle',              'title');
define('constWUNewPatchLocale',             'locale');
define('constWUNewPatch237Prio',            '237prio');
define('constWUNewPatchType',               'type');
define('constWUNewPatchMandatory',          'mandatory');

define('constWUStatPatchItemID',            'itemid');
define('constWUStatPatchStatus',            'patchstatus');
define('constWUStatPatchDate',              'patchdate');
define('constWUStatPatchLastInst',          'lastinstall');
define('constWUStatPatchLastDown',          'lastdownload');
define('constWUStatPatchDownSource',        'downloadsource');
define('constWUStatPatchLastError',         'lasterror');
define('constWUStatPatchLastErrorDate',     'lasterrordate');
define('constWUStatPatchCanUninstall',      'canuninst');
define('constWUStatPatchLastUInst',         'lastuninstall');
define('constWUStatPatchSuperBy',           'superby');

define('constWUGetPatchItemID',             'itemid');
define('constWUGetPatchAction',             'action');
define('constWUGetPatchScheduleDelay',      'scheddelay');
define('constWUGetPatchScheduleMinute',     'schedminute');
define('constWUGetPatchScheduleHour',       'schedhour');
define('constWUGetPatchScheduleDay',        'schedday');
define('constWUGetPatchScheduleMonth',      'schedmonth');
define('constWUGetPatchScheduleWeek',       'schedweek');
define('constWUGetPatchScheduleRandom',     'schedrandom');
define('constWUGetPatchScheduleType',       'schedtype');
define('constWUGetPatchNotifyA',            'notifyadvance');
define('constWUGetPatchNotifyAT',           'notifyadvancetime');
define('constWUGetPatchRemindUser',         'reminduser');
define('constWUGetPatchPreventShut',        'preventshutdown');
define('constWUGetPatchNotifySchedule',     'notifyschedule');
define('constWUGetPatchNotifyDelay',        'notifydelay');
define('constWUGetPatchNotifyMinute',       'notifyminute');
define('constWUGetPatchNotifyHour',         'notifyhour');
define('constWUGetPatchNotifyDay',          'notifyday');
define('constWUGetPatchNotifyMonth',        'notifymonth');
define('constWUGetPatchNotifyWeek',         'notifyweek');
define('constWUGetPatchNotifyRandom',       'notifyrandom');
define('constWUGetPatchNotifyType',         'notifytype');
define('constWUGetPatchNotifyFail',         'notifyfail');
define('constWUGetPatchNotifyT',            'notifytext');
define('constWUGetPatchMachineGroup',       'machinegroup');
define('constWUGetPatchPatchGroup',         'patchgroup');

define('constWUGetPatchDefaultID',          'default');

define('constWUTimesNoData',                1);

define('constVersionBegin',                 ' (v');
define('constVersionEnd',                   ')');

/* find_patch_id

        Locates "patchid" within the patches table.  Note that "patchid"
        should be unique in the table, so we expect to find either 0 or 1.
        Returns true or false depending on success.
    */

function find_patch_id($patchid, $db)
{
    $res  = false;
    if ($patchid) {
        $sql = "select name from\n"
            . " Patches where\n"
            . " itemid = $patchid";
        $row = find_one($sql, $db);
        $res = ($row) ? true : false;
    }

    return $res;
}

/* get_patch_identifier

        Converts a patchid number into the itemid field.
    */
function get_patch_identifier($patchid, $db)
{
    if ($patchid) {
        $sql = "select itemid from\n"
            . " Patches where\n"
            . " patchid = '$patchid'";
        $row = find_one($sql, $db);
        if ($row) {
            return $row['itemid'];
        }
    }
    return null;
}

/* find_patch_num

        Locates "patchid" within the Patches table and links it to the
        PatchStatus table.  Note that "patchid" should be unique in the
        Patches table, so we expect to find either 0 or 1.
        Returns 0 or the patchid.  If "check" is 1, then it will look to
        see if the patchid exists in PatchStatus.
        A further enhancement is the ability to just use the 6 digit code
        and the machineid to locate the patch in PatchStatus.  This function
        will try this if all else fails.
    */
function find_patch_num($patchid, $machineid, $db, $check)
{
    $res  = 0;
    if ($patchid) {
        $sql = "select * from\n"
            . " Patches where\n"
            . " itemid = $patchid";
        $row = find_one($sql, $db);
    }
    if (($row) && ($machineid)) {
        /* patch exists */
        $patchnum = $row['patchid'];
        $res = $patchnum;
        if (($patchnum) && ($check == 1)) {
            $sql = "select * from\n"
                . " PatchStatus where\n"
                . " patchid = $patchnum\n"
                . " and id = $machineid";
            $row2 = find_one($sql, $db);
            if ($row2) {
                $res = $patchnum;
            } else {
                $res = 0;
            }
        }
    } else if ($patchid && $machineid) {
        /* this indicates it never found it at all - let's try the 6 digit
                code */
        $sql = "select * from Patches";
        $row = find_many($sql, $db);
        if ($row) {
            reset($row);
            foreach ($row as $key => $row2) {
                $thisitem = $row2['itemid'];
                $thisid = $row2['patchid'];
                /* use === to avoid strpos returning a 0 as evaluating to
                        false */
                if (!(strpos($thisitem, $patchid) === 0)) {
                    /* for this to work the entry MUST already exist in
                            PatchStatus to avoid creating an invalid row */
                    $sql = "select * from\n"
                        . " PatchStatus where\n"
                        . " patchid = $thisid\n"
                        . " and id = $machineid";
                    $row3 = find_one($sql, $db);
                    if ($row3) {
                        $res = $thisid;
                    }
                }
            }
        }
    }
    return $res;
}

/* find_patch_statid

        Locates "patchid" within the Patches table and links it to the
        PatchStatus table.  Note that "patchid" should be unique in the
        Patches table, so we expect to find either 0 or 1.
        Returns 0 or the patchstatusid from the PatchStatus table.
    */
function find_patch_statid($patchid, $machineid, $db)
{
    $res  = 0;
    if ($patchid) {
        $sql = "select * from\n"
            . " Patches where\n"
            . " itemid = $patchid";
        $row = find_one($sql, $db);
    }
    if (($row) && ($machineid)) {
        /* patch exists */
        $patchnum = $row['patchid'];
        if ($patchnum) {
            $sql = "select * from\n"
                . " PatchStatus where\n"
                . " patchid = $patchnum\n"
                . " and id = $machineid";
            $row2 = find_one($sql, $db);
            if ($row2) {
                $res = $row2['patchstatusid'];
            }
        }
    }

    return $res;
}

function find_machine_row($machineid, $db)
{
    $row = array();
    if ($machineid) {
        $sql = "select * from\n"
            . " Machine where\n"
            . " id = $machineid";
        $row = find_one($sql, $db);
    }

    return $row;
}

function timestamp($x)
{
    return ($x) ? date('m/d H:i', $x) : 'never';
}

function find_lastpatchtime_by_machine($machineid, $db)
{
    $set = array();
    $highesttime = constWUTimesNoData;

    if ($machineid) {
        $sql = "select * from\n"
            . " PatchStatus where\n"
            . " id = $machineid order by\n"
            . " lastchange desc\n"
            . " limit 1";
        $set = find_one($sql, $db);
        if ($set) {
            $highesttime = $set['lastchange'];
        }
    }

    return $highesttime;
}

function recompute_patches_by_machine($machineid, $db)
{
    $names = array();

    /* recompute any rows that need to be recomputed */
    $set = array();
    $set = retrieve_patches_by_machine($machineid, $db);

    if ($set) {
        foreach ($set as $key => $row) {
            /* we have to recompute this row */
            $now = time();
            $psid = $row['patchstatusid'];
            $patchid = $row['patchid'];
            $oldpcfgid = $row['patchconfigid'];
            $oldtime = $row['lastchange'];

            $row2 = search_pconfig($machineid, $patchid, $db);

            if ($row2) {
                $pconfigid = $row2['pconfigid'];
                $name = array();
                $name['mgrp'] = $row2['mgrp'];
                $name['pgrp'] = $row2['pgrp'];
                $names[$pconfigid] = $name;
                if ($pconfigid != $oldpcfgid) {
                    $sql = "update PatchStatus set\n"
                        . " patchconfigid = $pconfigid,\n"
                        . " lastchange = $now\n"
                        . " where patchstatusid = $psid";
                    $res = command($sql, $db);
                } else {
                    $curupdate = $row2['lastupdate'];
                    if ($curupdate > $oldtime) {
                        $sql = "update PatchStatus set\n"
                            . " patchconfigid = $pconfigid,\n"
                            . " lastchange = $curupdate\n"
                            . " where patchstatusid = $psid";
                        $res = command($sql, $db);
                    }
                }
            } else {
                $text = "patch: missing pcfg for patch $patchid on machine $machineid";
                logs::log(__FILE__, __LINE__, $text, 0);
            }
        }
    }
    return $names;
}

function retrieve_patches_by_machine($machineid, $db)
{
    $set = array();
    if ($machineid) {
        $sql = "select * from\n"
            . " PatchStatus where\n"
            . " id = $machineid";
        $set = find_many($sql, $db);
    }
    return $set;
}

/* get_patch_action

        Retrieves much of the patchconfig table for this entry,
        if necessary will compute the patchconfigid.
    */

function get_patch_action(
    $pid,
    $patchid,
    $names,
    $machineid,
    $site,
    $host,
    $db
) {
    $action = array();
    $res = array();
    if ($pid && $patchid) {
        if ($pid == 0) {
            /* recompute the patchconfigid */
            $psid = find_patch_statid($patchid, $machineid, $db);
            $row = search_pconfig($machineid, $patchid, $db);
            if ($row) {
                $pid = $row['pconfigid'];
                $now = time();
                $sql = "update PatchStatus set\n"
                    . " patchconfigid = $pconfigid,\n"
                    . " lastchange = $now\n"
                    . " where patchstatusid = $psid";
                $res = command($sql, $db);
            }
        }
        if ($pid != 0) {
            /* return the configuration for this patch */
            $sql = "select * from PatchConfig\n"
                . " where pconfigid = $pid";
            $action = find_one($sql, $db);
            if ($action) {
                $item = @strval(get_patch_identifier($patchid, $db));
                $res[constWUGetPatchItemID] = $item;
                if ($res[constWUGetPatchItemID]) {
                    $res[constWUGetPatchAction] =
                        @intval($action['installation']);
                    $res[constWUGetPatchScheduleDelay] =
                        @intval($action['scheddelay']);
                    $res[constWUGetPatchScheduleMinute] =
                        @intval($action['schedminute']);
                    $res[constWUGetPatchScheduleHour] =
                        @intval($action['schedhour']);
                    $res[constWUGetPatchScheduleDay] =
                        @intval($action['schedday']);
                    $res[constWUGetPatchScheduleMonth] =
                        @intval($action['schedmonth']);
                    $res[constWUGetPatchScheduleWeek] =
                        @intval($action['schedweek']);
                    $res[constWUGetPatchScheduleRandom] =
                        @intval($action['schedrandom']);
                    $res[constWUGetPatchScheduleType] =
                        @intval($action['schedtype']);

                    $res[constWUGetPatchNotifyA] =
                        @intval($action['notifyadvance']);
                    $res[constWUGetPatchNotifyAT] =
                        @intval($action['notifyadvancetime']);
                    $res[constWUGetPatchRemindUser] =
                        @intval($action['reminduser']);
                    $res[constWUGetPatchPreventShut] =
                        @intval($action['preventshutdown']);
                    $res[constWUGetPatchNotifySchedule] =
                        @intval($action['notifyschedule']);
                    $res[constWUGetPatchNotifyDelay] =
                        @intval($action['notifydelay']);
                    $res[constWUGetPatchNotifyMinute] =
                        @intval($action['notifyminute']);
                    $res[constWUGetPatchNotifyHour] =
                        @intval($action['notifyhour']);
                    $res[constWUGetPatchNotifyDay] =
                        @intval($action['notifyday']);
                    $res[constWUGetPatchNotifyMonth] =
                        @intval($action['notifymonth']);
                    $res[constWUGetPatchNotifyWeek] =
                        @intval($action['notifyweek']);
                    $res[constWUGetPatchNotifyRandom] =
                        @intval($action['notifyrandom']);
                    $res[constWUGetPatchNotifyType] =
                        @intval($action['notifytype']);
                    $res[constWUGetPatchNotifyFail] =
                        @intval($action['notifyfail']);
                    $res[constWUGetPatchNotifyT] =
                        @strval($action['notifytext']);

                    $res[constWUGetPatchMachineGroup] =
                        @strval($names[$pid]['mgrp']);
                    $res[constWUGetPatchPatchGroup] =
                        @strval($names[$pid]['pgrp']);
                } else {
                    $res = array();
                    $text = "patch: unique id does not exist for patch"
                        . " $patchid on machine $machineid.";
                    logs::log(__FILE__, __LINE__, $text, 0);
                }
            }
        } else {
            $text = "patch: missing pcfg for patch"
                . " $patchid on machine $machineid.";
            logs::log(__FILE__, __LINE__, $text, 0);
        }
    }
    return $res;
}

/* uses itemid to get the equivalent patchid out of softinst.Patches */
function get_patchid_by_itemid($itemid, $db)
{
    $row = array();
    $patchid = 0;
    if ($itemid) {
        $sql = "select patchid from\n"
            . " Patches where\n"
            . " itemid = $itemid";
        $row = find_one($sql, $db);
        $patchid = ($row) ? $row['patchid'] : 0;
    }
    return $patchid;
}

function update_machine_patch($machineid, $db)
{
    if ($machineid) {
        $now = time();
        $sql = "update Machine set\n"
            . " lastcontact = $now\n"
            . " where id = $machineid";
        $res = command($sql, $db);

        if (!affected($res, $db)) {
            /* we need to add this machine */
            $sql = "insert into Machine set\n"
                . " id = $machineid,\n"
                . " lastcontact = $now";
            $res = command($sql, $db);
        }
    }
}

/* get_patch_config returns the patch configuration for id $pconfigid,
        converts the patch configuration to the client representation using
        the default identifier */
function get_patch_config($machineid, $pconfigid, $db)
{
    $sql = "select * from PatchConfig\n"
        . " where pconfigid = $pconfigid";
    $getdef = find_one($sql, $db);

    return convert_patch_config($getdef);
}

/* converts a patch config record to the client version */
function convert_patch_config($getdef)
{
    $defaultpatch = array();

    $defaultpatch[constWUGetPatchItemID] = constWUGetPatchDefaultID;
    $defaultpatch[constWUGetPatchAction]  =
        @intval($getdef['installation']);
    $defaultpatch[constWUGetPatchNotifyA] =
        @intval($getdef['notifyadvance']);
    $defaultpatch[constWUGetPatchNotifyAT] =
        @intval($getdef['notifyadvancetime']);
    $defaultpatch[constWUGetPatchRemindUser] =
        @intval($getdef['reminduser']);
    $defaultpatch[constWUGetPatchPreventShut] =
        @intval($getdef['preventshutdown']);
    $defaultpatch[constWUGetPatchNotifySchedule] =
        @intval($getdef['notifyschedule']);
    $defaultpatch[constWUGetPatchNotifyT] =
        @strval($getdef['notifytext']);

    $defaultpatch[constWUGetPatchScheduleDelay] =
        @intval($getdef['scheddelay']);
    $defaultpatch[constWUGetPatchScheduleMinute] =
        @intval($getdef['schedminute']);
    $defaultpatch[constWUGetPatchScheduleHour] =
        @intval($getdef['schedhour']);
    $defaultpatch[constWUGetPatchScheduleDay] =
        @intval($getdef['schedday']);
    $defaultpatch[constWUGetPatchScheduleMonth] =
        @intval($getdef['schedmonth']);
    $defaultpatch[constWUGetPatchScheduleWeek] =
        @intval($getdef['schedweek']);
    $defaultpatch[constWUGetPatchScheduleRandom] =
        @intval($getdef['schedrandom']);
    $defaultpatch[constWUGetPatchScheduleType] =
        @intval($getdef['schedtype']);

    $defaultpatch[constWUGetPatchNotifyDelay] =
        @intval($getdef['notifydelay']);
    $defaultpatch[constWUGetPatchNotifyMinute] =
        @intval($getdef['notifyminute']);
    $defaultpatch[constWUGetPatchNotifyHour] =
        @intval($getdef['notifyhour']);
    $defaultpatch[constWUGetPatchNotifyDay] =
        @intval($getdef['notifyday']);
    $defaultpatch[constWUGetPatchNotifyMonth] =
        @intval($getdef['notifymonth']);
    $defaultpatch[constWUGetPatchNotifyWeek] =
        @intval($getdef['notifyweek']);
    $defaultpatch[constWUGetPatchNotifyRandom] =
        @intval($getdef['notifyrandom']);
    $defaultpatch[constWUGetPatchNotifyType] =
        @intval($getdef['notifytype']);
    $defaultpatch[constWUGetPatchNotifyFail] =
        @intval($getdef['notifyfail']);

    $defaultpatch[constWUGetPatchMachineGroup] =
        @strval($getdef['mgrp']);
    $defaultpatch[constWUGetPatchPatchGroup] =
        @strval($getdef['pgrp']);

    return $defaultpatch;
}

/* update a title */
function update_title($itemid, $newtitle, $db)
{
    if ($itemid && $newtitle) {
        $sql = "update Patches set\n"
            . " name = $newtitle\n"
            . " where itemid = $itemid";
        $res = command($sql, $db);
    }
}



function find_times(&$args)
{
    $rval = constAppNoErr;
    $alst = &$args['valu'][1];
    $host = &$args['valu'][2];
    $site = &$args['valu'][3];
    $uuid = &$args['valu'][4];
    $code = &$args['valu'][5];

    $args['olog'] = 0;
    $args['oxml'] = 1;

    $db = db_code('db_pat');
    if (!$db) {
        $text = "patch: $host wutimes, mysql error at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        $args['rval'] = constErrDatabaseNotAvailable;
        return;
    }

    $times = array();
    $machine = array();
    $wucfgcache = array();

    if ($args['updatecensus'] == 1) {
        $rval = census_manage($site, $host, $uuid, 1, $db);
        if ($rval != constAppNoErr) {
            $args['rval'] = $rval;
            return;
        }
    }

    /* lookup this machine and retrieve its id */
    $machine = find_census_uuid($uuid, $db);
    if (!$machine) {
        $text = "patch: $host wutimes, census error on $uuid at $site";
        logs::log(__FILE__, __LINE__, $text, 0);
        $args['rval'] = constErrDatabaseNotAvailable;
        return;
    }
    $machineid = @intval($machine['id']);

    update_machine_patch($machineid, $db);

    /* match up this id in the wuconfigcache table and retrieve
            this machine's configuration */
    $wucfgcache = find_machine_row($machineid, $db);
    if (!$wucfgcache) {
        $text = "patch: $host wutimes, missing machine on $uuid at $site";
        logs::log(__FILE__, __LINE__, $text, 0);
        $args['rval'] = constErrDatabaseNotAvailable;
        return;
    }

    $row = array();
    $wconfigid = 0;

    $row = search_wconfig($machineid, $site, $db);
    $ctime = time();
    if ($row) {
        $wconfigid = $row['id'];
        if ($wconfigid == 0) {
            $ctime = @intval(constWUTimesNoData);
        } else if ($wconfigid != $wucfgcache['wuconfigid']) {
            $sql = "update Machine set\n"
                . " wuconfigid = $wconfigid,\n"
                . " lastchange = $ctime\n"
                . " where id = $machineid";
            $res = command($sql, $db);
        } else {
            /* use either the previous time or the lastupdate time,
                    whichever is greater */
            if ($row['lastupdate'] > $wucfgcache['lastchange']) {
                $ctime = @intval($row['lastupdate']);
            } else {
                $ctime = @intval($wucfgcache['lastchange']);
            }
        }
    }

    recompute_patches_by_machine($machineid, $db);

    $ptime = @intval(find_lastpatchtime_by_machine($machineid, $db));

    $defpatch = search_pconfig($machineid, 0, $db);
    if ($ptime == 0) {

        if ($defpatch['lastupdate'] == 0) {
            $ptime = time();
        } else {
            $ptime = @intval($defpatch['lastupdate']);
        }
    }

    /* now compute the default patch config time - update the default patch
            config id as necessary */
    if ($wucfgcache['lastdefconfigid'] == $defpatch['pconfigid']) {
        if ($wucfgcache['lastdefchange'] < $defpatch['lastupdate']) {
            $dtime = @intval($defpatch['lastupdate']);
            $sql = "update Machine set\n"
                . " lastdefchange = $dtime\n"
                . " where id = $machineid";
            $res = command($sql, $db);
        } else {
            $dtime = @intval($wucfgcache['lastdefchange']);
        }
    } else {
        $dtime = time();
        $pid = $defpatch['pconfigid'];

        $sql = "update Machine set\n"
            . " lastdefchange = $dtime,\n"
            . " lastdefconfigid = $pid\n"
            . " where id = $machineid";
        $res = command($sql, $db);
    }


    /* send back the lastchange value from wuconfigcache */
    $times[constWUTimeConfig] = $ctime;
    $times[constWUTimePatch]  = $ptime;
    $times[constWUTimeDefault] = $dtime;

    /* returns "times" to the caller by reference */
    $alst = fully_make_alist($times);

    if ($ctime == constWUTimesNoData) {
        $cdate = "no data";
    } else {
        $cdate = timestamp($ctime);
    }
    if ($ptime == constWUTimesNoData) {
        $pdate = "no data";
    } else {
        $pdate = timestamp($ptime);
    }
    $ddate = timestamp($dtime);

    $stat = "c:$cdate,p:$pdate,d:$ddate";
    $text = "patch: $host wutimes ($stat) at $site.";
    debug_note($text);
    logs::log(__FILE__, __LINE__, $text, 0);

    $args['rval'] = $rval;
}



function find_wcfg(&$args)
{
    $rval = constAppNoErr;
    $host = &$args['valu'][2];
    $site = &$args['valu'][3];
    $uuid = &$args['valu'][4];
    $code = &$args['valu'][5];

    $args['olog'] = 0;
    $args['oxml'] = 1;

    $db = db_code('db_pat');
    if (!$db) {
        $text = "patch: $host getwcfg, mysql error at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        $args['rval'] = constErrDatabaseNotAvailable;
        return;
    }

    if ($args['updatecensus'] == 1) {
        $rval = census_manage($site, $host, $uuid, 1, $db);
        if ($rval != constAppNoErr) {
            $args['rval'] = $rval;
            return;
        }
    }

    /* lookup this machine and retrieve its id */
    $machine = find_census_uuid($uuid, $db);
    if (!$machine) {
        $text = "patch: $host getwcfg, census error on $uuid at $site";
        logs::log(__FILE__, __LINE__, $text, 0);
        $args['rval'] = constErrDatabaseNotAvailable;
        return;
    }
    $machineid  = $machine['id'];
    update_machine_patch($machineid, $db);

    $thisConfig = search_wconfig($machineid, $site, $db);

    if (!($thisConfig)) {
        $text = "patch: $host missing wcfg for $machineid at $site";
        logs::log(__FILE__, __LINE__, $text, 0);
        $args['rval'] = constErrDatabaseNotAvailable;
        return;
    }

    $man = @intval($thisConfig[constWUConfigManagement]);
    $day = @intval($thisConfig[constWUConfigInstallDay]);
    $hor = @intval($thisConfig[constWUConfigInstallHour]);
    $new = @intval($thisConfig[constWUConfigNewPatches]);
    $src = @intval($thisConfig[constWUConfigPatchSource]);
    $url = @strval($thisConfig[constWUConfigServerURL]);
    $prp = @intval($thisConfig[constWUConfigPropagate]);
    $cac = @intval($thisConfig[constWUConfigCache]);
    $cas = @intval($thisConfig[constWUConfigCacheSeconds]);
    $mgp = @strval($thisConfig['mgrp']);
    $rest = @intval($thisConfig[constWUConfigRestart]);
    $chan = @intval($thisConfig[constWUConfigChain]);
    $chas = @intval($thisConfig[constWUConfigChainSeconds]);

    $cfg = array();

    $cfg[constWUConfigManagement]   = $man;
    $cfg[constWUConfigInstallDay]   = $day;
    $cfg[constWUConfigInstallHour]  = $hor;
    $cfg[constWUConfigNewPatches]   = $new;
    $cfg[constWUConfigPatchSource]  = $src;
    $cfg[constWUConfigServerURL]    = $url;
    $cfg[constWUConfigPropagate]    = $prp;
    $cfg[constWUConfigCache]        = $cac;
    $cfg[constWUConfigCacheSeconds] = $cas;
    $cfg[constWUConfigMachineGroup] = $mgp;
    $cfg[constWUConfigRestart]      = $rest;
    $cfg[constWUConfigChain]        = $chan;
    $cfg[constWUConfigChainSeconds] = $chas;

    $text = "patch: $host getwcfg (m:$man,d:$day,h:$hor,n:$new,p:$prp) at $site.";
    debug_note($text);
    logs::log(__FILE__, __LINE__, $text, 0);

    /* returns "wucfg" to the caller by reference */

    /* debug code */
    //ob_start();
    //print_r($cfg);
    //$text = ob_get_contents();
    //logs::log(__FILE__, __LINE__, $text,0);

    $args['valu'][1] = fully_make_alist($cfg);
    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = $rval;
}


function find_patches(&$args)
{
    $rval = constAppNoErr;
    $alst         = &$args['valu'][1];
    $newtimestamp = &$args['valu'][2];
    $host         = &$args['valu'][3];
    $site         = &$args['valu'][4];
    $uuid         = &$args['valu'][5];
    $oldtimestamp = &$args['valu'][6];
    $code         = &$args['valu'][7];
    $count = 0;

    $args['olog'] = 0;
    $args['oxml'] = 1;

    $db = db_code('db_pat');
    if (!$db) {
        $args['rval'] = constErrDatabaseNotAvailable;
        $text = "patch: $host getpatch, mysql failure at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        return;
    }

    $newtimestamp = 0;
    $machine = array();
    $allpatches = array();

    if ($args['updatecensus'] == 1) {
        $rval = census_manage($site, $host, $uuid, 1, $db);
        if ($rval != constAppNoErr) {
            $args['rval'] = $rval;
            return;
        }
    }

    /* lookup this machine and retrieve its id */
    $machine = find_census_uuid($uuid, $db);
    if (!$machine) {
        $args['rval'] = constErrDatabaseNotAvailable;
        $text = "patch: $host getpatch, census error on $uuid at $site";
        logs::log(__FILE__, __LINE__, $text, 0);
        return;
    }
    $machineid = @intval($machine['id']);
    update_machine_patch($machineid, $db);

    /* recompute everything first */
    $names = recompute_patches_by_machine($machineid, $db);

    find_lastpatchtime_by_machine($machineid, $db);

    $allpatches = retrieve_patches_by_machine($machineid, $db);

    /* anything in allpatches that is newer than oldtimestamp gets
            returned */
    $patches = array();
    if ($allpatches) {
        reset($allpatches);
        foreach ($allpatches as $key => $row) {
            if ($newtimestamp < $row['lastchange']) {
                $newtimestamp = $row['lastchange'];
            }

            if ($row['lastchange'] > $oldtimestamp) {
                $pid = $row['patchconfigid'];
                $mid = $row['patchid'];
                $patches[] = get_patch_action(
                    $pid,
                    $mid,
                    $names,
                    $machineid,
                    $site,
                    $host,
                    $db
                );
                $count = $count + 1;
            }
        }
    }

    if ($count == 0) {
        $newtimestamp = $oldtimestamp;
    }

    $alst = fully_make_alist($patches);
    $alen = strlen($alst);
    $odate = timestamp($oldtimestamp);
    $ndate = timestamp($newtimestamp);

    $stat = "c:$count,o:$odate,n:$ndate,l:$alen";
    $text = "patch: $host getpatch ($stat) at $site.";
    logs::log(__FILE__, __LINE__, $text, 0);
    debug_note($text);

    /* returns "alst" and "newTimeStamp" to the caller by reference */
    $args['rval'] = constAppNoErr;
}





function upload_patch(&$args)
{
    $rval    = constAppNoErr;
    $host    = &$args['valu'][1];
    $site    = &$args['valu'][2];
    $uuid    = &$args['valu'][3];
    $patches = &$args['valu'][4];
    $code    = &$args['valu'][5];
    $jid     = 0;

    $args['olog'] = 0;
    $args['oxml'] = 1;

    $db = db_code('db_pat');
    if (!$db) {
        $args['rval'] = constErrDatabaseNotAvailable;
        $text = "patch: $host sendpatch, mysql failure at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        return;
    }

    if ($args['updatecensus'] == 1) {
        $rval = census_manage($site, $host, $uuid, 1, $db);
        if ($rval != constAppNoErr) {
            $args['rval'] = $rval;
            return;
        }
    }

    /* lookup this machine and retrieve its id */
    $machine = find_census_uuid($uuid, $db);
    if (!$machine) {
        $text = "patch: $host sendpatch, census error on $uuid at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        $args['rval'] = constErrDatabaseNotAvailable;
        return;
    }
    $machineid = @intval($machine['id']);
    update_machine_patch($machineid, $db);

    $patchList = fully_parse_alist($patches);
    $uload = safe_count($patchList);
    $write = 0;

    /* retrieve all relevant named items out of patchList */
    if ($patchList) {
        $gpconfig = find_glbl_pcfg($db);
        if ($gpconfig) {
            $jid = $gpconfig['pgroupid'];
        }

        foreach ($patchList as $k => $row) {
            unset(
                $itemid,
                $name,
                $date,
                $size,
                $patchdesc,
                $params,
                $clientfile,
                $serverfile,
                $crc,
                $component,
                $platform,
                $processor,
                $osmajor,
                $osminor,
                $osbuild,
                $spmajor,
                $spminor,
                $prio,
                $priohidden,
                $eula,
                $status,
                $clienttime,
                $type,
                $prio237,
                $mandatory
            );

            $itemid = quote(strval($row[constWUNewPatchItemID]));
            $name = quote(strval($row[constWUNewPatchName]));
            $uqname = $row[constWUNewPatchName];
            $date = intval($row[constWUNewPatchDate]);
            $size = intval($row[constWUNewPatchSize]);
            $patchdesc = quote(strval($row[constWUNewPatchDesc]));
            $params = quote(strval($row[constWUNewPatchParams]));
            $clientfile = quote(strval($row[constWUNewPatchClient]));
            $serverfile = quote(strval($row[constWUNewPatchServer]));
            $crc = quote(strval($row[constWUNewPatchCRC]));
            $component = quote(strval($row[constWUNewPatchComponent]));
            $platform = quote(strval($row[constWUNewPatchPlatform]));
            $processor = quote(strval($row[constWUNewPatchProcessor]));
            $osmajor = intval($row[constWUNewPatchOSMajor]);
            $osminor = intval($row[constWUNewPatchOSMinor]);
            $osbuild = intval($row[constWUNewPatchOSBuild]);
            $spmajor = intval($row[constWUNewPatchSPMajor]);
            $spminor = intval($row[constWUNewPatchSPMinor]);
            $prio = intval($row[constWUNewPatchPriority]);
            $priohidden = intval($row[constWUNewPatchPrioH]);
            $eula = quote(strval($row[constWUNewPatchEULA]));
            $status = intval($row[constWUNewPatchStatus]);
            $clienttime = intval($row[constWUNewPatchClientTime]);
            $msname = quote(strval($row[constWUNewPatchMSName]));
            $title = quote(strval($row[constWUNewPatchTitle]));
            $locale = quote(strval($row[constWUNewPatchLocale]));
            $type = @intval($row[constWUNewPatchType]);
            $prio237 = @intval($row[constWUNewPatch237Prio]);
            $mandatory = @intval($row[constWUNewPatchMandatory]);

            if (!(isset(
                $itemid,
                $name,
                $date,
                $size,
                $patchdesc,
                $params,
                $clientfile,
                $serverfile,
                $crc,
                $component,
                $platform,
                $processor,
                $osmajor,
                $osminor,
                $osbuild,
                $spmajor,
                $spminor,
                $prio,
                $priohidden,
                $eula,
                $status,
                $clienttime,
                $msname,
                $title,
                $locale
            ))) {
                $rval = constErrAssertFail;
                $args['rval'] = $rval;
                return;
            } else {
                /* add this patch to softinst.Patches */
                $sql = "insert into Patches set\n"
                    . " itemid = $itemid,\n"
                    . " name = $name,\n"
                    . " date = $date,\n"
                    . " size = $size,\n"
                    . " patchdesc = $patchdesc,\n"
                    . " params = $params,\n"
                    . " clientfile = $clientfile,\n"
                    . " serverfile = $serverfile,\n"
                    . " crc = $crc,\n"
                    . " component = $component,\n"
                    . " platform = $platform,\n"
                    . " processor = $processor,\n"
                    . " osmajor = $osmajor,\n"
                    . " osminor = $osminor,\n"
                    . " osbuild = $osbuild,\n"
                    . " spmajor = $spmajor,\n"
                    . " spminor = $spminor,\n"
                    . " prio = $prio,\n"
                    . " priohidden = $priohidden,\n"
                    . " eula = $eula,\n"
                    . " msname = $msname,\n"
                    . " title = $title,\n"
                    . " locale = $locale";
                if (isset($type)) {
                    if ($type == constPatchTypeMandatory) {
                        /* This is an older client with a critical
                                mandatory update - force it to critical and
                                use the mandatory flag. */
                        $type = constPatchTypeCritical;
                        $mandatory = constPatchMandatoryYes;
                    }
                    $sql .= ",\ntype = $type";
                }
                if (isset($prio237)) {
                    $sql .= ",\npriority = $prio237";
                }
                if (isset($mandatory)) {
                    $sql .= ",\nmandatory = $mandatory";
                }
                $res = command($sql, $db);
                if (!(affected($res, $db))) {
                    /* Update the existing patch entry if applicable */
                    if ((isset($type) || isset($prio237)) &&
                        (($type != 0) || ($prio237 != 0))
                    ) {
                        $sql = "update Patches set\n";
                        if (isset($type)) {
                            if ($type != 0) {
                                $sql .= "type = $type";
                            }
                            if (isset($prio237)) {
                                if ($prio237 != 0) {
                                    $sql .= ",\n";
                                }
                            }
                        }
                        if (isset($prio237)) {
                            if ($prio237 != 0) {
                                $sql .= "priority = $prio237\n";
                            }
                        }
                        $sql .= "where itemid = $itemid and (type != $type"
                            . " or priority != $prio237)";
                        $res = command($sql, $db);
                        if (affected($res, $db)) {
                            $write++;
                            $item = $row[constWUNewPatchItemID];
                            $text = "patch: $host updated $item at $site.";
                            debug_note($text);
                            logs::log(__FILE__, __LINE__, $text, 0);

                            /* Now, add this patch to the correct patch
                                    type group. */
                            PDRT_AddNewPatchToType($itemid, $type, $db);
                        }
                    }
                    if (
                        isset($mandatory) &&
                        ($mandatory != constPatchMandatoryUnknown)
                    ) {
                        $sql = 'UPDATE Patches SET mandatory=' . $mandatory
                            . " WHERE itemid = $itemid AND mandatory!="
                            . $mandatory;
                        $res = command($sql, $db);
                        if (affected($res, $db)) {
                            $write++;
                            $item = $row[constWUNewPatchItemID];
                            $text = "patch: $host updated $item at $site.";
                            debug_note($text);
                            logs::log(__FILE__, __LINE__, $text, 0);

                            if ($mandatory == constPatchMandatoryYes) {
                                PDRT_AddMandatoryPatch(
                                    $itemid,
                                    $type,
                                    $db
                                );
                            }
                        }
                    }
                } else {
                    $mid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
                    if (($jid) && ($mid)) {
                        $sql = "insert into\n"
                            . " " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap set\n"
                            . " pgroupid = $jid,\n"
                            . " patchid = $mid";
                        $res = command($sql, $db);
                        if (!affected($res, $db)) {
                            $text = "patch: failed to insert $name";
                            $text .= " into pgrpmap";
                            debug_note($text);
                            logs::log(__FILE__, __LINE__, $text, 0);
                        }
                    }

                    $write++;
                    $item = $row[constWUNewPatchItemID];
                    $text = "patch: $host created $item at $site.";
                    debug_note($text);
                    logs::log(__FILE__, __LINE__, $text, 0);
                    update_opt(constPatchDirty, constDirtySet, $db);

                    /* Now, add this patch to the correct patch type group.
                        */
                    PDRT_AddNewPatchToType($itemid, $type, $db);

                    /* ensure titles are unique */
                    $changetitle = 0;
                    $vernum = 2;
                    $usedate = 0;
                    $sql = "select * from\n"
                        . " Patches where\n"
                        . " title = $title and\n"
                        . " patchid != $mid";
                    $row = find_many($sql, $db);
                    if ($row) {
                        reset($row);
                        foreach ($row as $key => $row2) {
                            if ((strcmp(
                                    quote($row2['itemid']),
                                    $itemid
                                ) != 0) &&
                                (strcmp(
                                    quote($row2['locale']),
                                    $locale
                                ) == 0) &&
                                (strcmp(
                                    quote($row2['processor']),
                                    $processor
                                ) == 0) &&
                                ($row2['osmajor'] == $osmajor) &&
                                ($row2['osminor'] == $osminor) &&
                                ($row2['osbuild'] == $osbuild) &&
                                ($row2['spmajor'] == $spmajor) &&
                                ($row2['spminor'] == $spminor)
                            ) {
                                $changetitle = 1;

                                /* this patch would have the same
                                        constructed title, see if the date is
                                        already here */

                                if ($row2['date'] != 0) {
                                    $dateStr = date(
                                        'm/d/y',
                                        $row2['date']
                                    );
                                    if (
                                        strpos($row2['name'], $dateStr)
                                        !== false
                                    ) {
                                        /* this one already has a date */
                                    } else {
                                        update_title(
                                            quote($row2['itemid']),
                                            quote($row2['name'] .
                                                ' (' . $dateStr . ')'),
                                            $db
                                        );
                                    }
                                    if ($row2['date'] != $date) {
                                        $usedate = 1;
                                    }
                                }
                                $verpos = strpos(
                                    $row2['name'],
                                    constVersionBegin,
                                    strlen($row2['title'])
                                );
                                if (($usedate == 0) &&
                                    ($verpos !== false)
                                ) {
                                    $ver = substr(
                                        $row2['name'],
                                        $verpos,
                                        strlen($row2['name'])
                                    );
                                    $num = sscanf(
                                        $ver,
                                        constVersionBegin . "%d" .
                                            constVersionEnd
                                    );
                                    if ($num[0] >= $vernum) {
                                        $vernum = $num[0] + 1;
                                    }
                                } else if ($usedate == 0) {
                                    /* if we wanted to say v1 for the
                                            original patch then we need to
                                            handle this case */
                                }
                            }
                        } /* patch while loop */
                    }
                    if ($changetitle == 1 && $usedate == 1) {
                        if ($date != 0) {
                            $dateStr = date('m/d/y', $date);
                            update_title($itemid, quote($uqname .
                                ' (' . $dateStr . ')'), $db);
                        } else {
                            $newname = $uqname . constVersionBegin .
                                $vernum . constVersionEnd;
                            update_title($itemid, quote($newname), $db);
                        }
                    } else if ($changetitle == 1) {
                        $newname = $uqname . constVersionBegin . $vernum .
                            constVersionEnd;
                        update_title($itemid, quote($newname), $db);
                    }
                }

                /* add an entry to the PatchStatus table if necessary */
                $patchid = get_patchid_by_itemid($itemid, $db);
                if ($patchid) {
                    $now = time();
                    $sql = "insert into PatchStatus set\n"
                        . " id = $machineid,\n"
                        . " patchid = $patchid,\n"
                        . " patchconfigid = 0,\n"
                        . " status = $status,\n"
                        . " detected = $clienttime,\n"
                        . " downloadsource = $serverfile,\n"
                        . " lastchange = $now";
                    $res = command($sql, $db);
                    if (!affected($res, $db)) {
                        /* ensure that if download source needs to change it does */
                        $sql = "update PatchStatus set\n"
                            . " status = $status,\n"
                            . " downloadsource = $serverfile\n"
                            . " where id = $machineid\n"
                            . " and patchid = $patchid";
                        $res = command($sql, $db);
                    }
                } else {
                    $item = $row[constWUNewPatchItemID];
                    $text = "patch: $host missing $item at $site.";
                    logs::log(__FILE__, __LINE__, $text, 0);
                }
            }
        }
    }
    $stat = "u:$uload,w:$write";
    $text = "patch: $host sendpatch ($stat) at $site.";
    logs::log(__FILE__, __LINE__, $text, 0);
    debug_note($text);

    $args['olog'] = 0;
    $args['oxml'] = 1;
    $args['rval'] = $rval;
}


function set_status(&$args)
{
    $rval = constAppNoErr;
    $host = &$args['valu'][1];
    $site = &$args['valu'][2];
    $uuid = &$args['valu'][3];
    $alst = &$args['valu'][4];
    $code = &$args['valu'][5];
    $total = 0;
    $used = 0;

    $args['olog'] = 0;
    $args['oxml'] = 1;

    $db = db_code('db_pat');
    if (!$db) {
        $args['rval'] = constErrDatabaseNotAvailable;
        $text = "patch: $host setstatus, mysql failure at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        return;
    }

    if ($args['updatecensus'] == 1) {
        $rval = census_manage($site, $host, $uuid, 1, $db);
        if ($rval != constAppNoErr) {
            $args['rval'] = $rval;
            return;
        }
    }

    /* lookup this machine and retrieve its id */
    $machine = find_census_uuid($uuid, $db);
    $machineid = @intval($machine['id']);

    update_machine_patch($machineid, $db);

    $patchList = fully_parse_alist($alst);

    /* retrieve all relevant named items out of patchList */
    if ($patchList) {
        foreach ($patchList as $k => $row) {
            $total = $total + 1;

            unset(
                $itemid,
                $status,
                $date,
                $lastinst,
                $lastdown,
                $downsource,
                $lasterror,
                $lastuinst
            );

            $itemid =     quote(strval($row[constWUStatPatchItemID]));
            $status =     @intval($row[constWUStatPatchStatus]);
            $date =       @intval($row[constWUStatPatchDate]);
            $lastinst =   @intval($row[constWUStatPatchLastInst]);
            $lastdown =   @intval($row[constWUStatPatchLastDown]);
            $downsource = @quote(strval($row[constWUStatPatchDownSource]));
            $lasterror =  @quote(strval($row[constWUStatPatchLastError]));
            $lasterrordate = @intval($row[constWUStatPatchLastErrorDate]);
            $canuinst =   @intval($row[constWUStatPatchCanUninstall]);
            $lastuinst =  @intval($row[constWUStatPatchLastUInst]);

            /* Do not allow "Software update failed to install." for
                    $lasterror */
            if (isset($lasterror)) {
                if (strcmp(
                    $lasterror,
                    '\'Software update failed to install.\''
                ) == 0) {
                    $lasterror = '';
                    unset($lasterror);
                }
            }

            if (!(isset($itemid))) {
                $rval = constErrAssertFail;
                $args['rval'] = $rval;
                return;
            } else {
                $patchnum = find_patch_num($itemid, $machineid, $db, 1);

                /* Do not permit the status to change to superseded if the
                        install date is non-zero. */
                if ((isset($status)) &&
                    ($status == constPatchStatusSuperseded)
                ) {
                    if ((isset($lastinst)) && ($lastinst != 0)) {
                        $status = constPatchStatusInstalled;
                    } else {
                        if ($patchnum != 0) {
                            $sql = 'SELECT lastinstall FROM PatchStatus '
                                . "WHERE patchid = $patchnum and id = "
                                . "$machineid";
                            $patchRes = find_one($sql, $db);
                            if ($patchRes) {
                                if ($patchRes['lastinstall'] != 0) {
                                    $status = constPatchStatusInstalled;
                                }
                            }
                        }
                    }
                }
                /* we have the full patch entry, now see if patchid already
                        exists in softinst.PatchStatus by checking the
                        itemid */
                if ($patchnum == 0) {
                    $patchnum = find_patch_num(
                        $itemid,
                        $machineid,
                        $db,
                        0
                    );
                    if ($patchnum != 0) {
                        /* add this patch to softinst.PatchStatus */
                        $sql = "insert into PatchStatus set\n"
                            . " patchid = $patchnum,\n"
                            . " id = $machineid";

                        /* set the download date if necessary */
                        if ((!(isset($lastdown) && ($lastdown != 0))) &&
                            (isset($lastinst) && ($lastinst != 0))
                        ) {
                            $lastdown = $lastinst;
                        }

                        if ((isset($status)) && ($status != 0)) {
                            $sql .= ",\n status = $status";
                        }
                        if ((isset($lastinst)) && ($lastinst != 0)) {
                            $sql .= ",\n lastinstall = $lastinst";
                        }
                        if ((isset($lastdown)) && ($lastdown != 0)) {
                            $sql .= ",\n lastdownload = $lastdown";
                        }
                        if ((isset($downsource)) && (strlen($downsource) >
                            4)) {
                            $sql .= ",\n downloadsource = $downsource";
                        }
                        if ((isset($lasterror)) && (strlen($lasterror) > 4)) {
                            $sql .= ",\n lasterror = $lasterror";
                        }
                        if ((isset($lasterrordate)) && ($lasterrordate != 0)) {
                            $sql .= ",\n lasterrordate = $lasterrordate";
                        }
                        if ((isset($lastuinst)) && ($lastuinst != 0)) {
                            $sql .= ",\n lastuninstall = $lastuinst";
                        }
                        $res = command($sql, $db);
                        if (affected($res, $db)) {
                            $used = $used + 1;
                            $rval = constAppNoErr;
                        } else {
                            $text = "patch: $host setstatus failure at $site.";
                            logs::log(__FILE__, __LINE__, $text, 0);
                            $rval = constErrDatabaseNotAvailable;
                            $args['rval'] = $rval;
                            return;
                        }
                    } else {
                        $text = "patch: $host setstatus, $itemid missing at $site.";
                        logs::log(__FILE__, __LINE__, $text, 0);
                        $rval = constErrDatabaseNotAvailable;
                        $args['rval'] = $rval;
                        return;
                    }
                } else {
                    /* update this patch in PatchStatus */
                    $first = 1;
                    $sql = "update PatchStatus set\n";
                    if ((isset($status)) && ($status != 0)) {
                        if ($first == 1) {
                            $sql .= " status = $status";
                            $first = 0;
                        } else {
                            $sql .= ",\n status = $status";
                        }
                    }
                    if ((isset($lastinst)) && ($lastinst != 0)) {
                        /* install time is only reported if the status has
                                changed */
                        $sql2 = "select status from PatchStatus\n"
                            .  " where patchid = $patchnum and\n"
                            .  " id = $machineid";
                        $row = find_one($sql2, $db);
                        if ($row) {
                            if ($row['status'] != $status) {
                                $sql2 = "select lastdownload\n"
                                    .  " from PatchStatus\n"
                                    .  " where patchid = $patchnum and\n"
                                    .  " id = $machineid";
                                $row = find_one($sql2, $db);
                                if ($row) {
                                    if (($row['lastdownload'] == 0) &&
                                        (!(isset($lastdown) &&
                                            ($lastdown != 0)))
                                    ) {
                                        $lastdown = $lastinst;
                                    }
                                }

                                if ($first == 1) {
                                    $sql .= " lastinstall = $lastinst";
                                    $first = 0;
                                } else {
                                    $sql .= ",\n lastinstall = $lastinst";
                                }
                            }
                        }
                    }
                    if ((isset($lastdown)) && ($lastdown != 0)) {
                        if ($first == 1) {
                            $sql .= " lastdownload = $lastdown";
                            $first = 0;
                        } else {
                            $sql .= ",\n lastdownload = $lastdown";
                        }
                    }
                    if ((isset($downsource)) && (strlen($downsource) > 4)) {
                        if ($first == 1) {
                            $sql .= " downloadsource = $downsource";
                            $first = 0;
                        } else {
                            $sql .= ",\n downloadsource = $downsource";
                        }
                    }
                    if ((isset($lasterror)) && (strlen($lasterror) > 4)) {
                        if ($first == 1) {
                            $sql .= " lasterror = $lasterror";
                            $first = 0;
                        } else {
                            $sql .= ",\n lasterror = $lasterror";
                        }
                    }
                    if ((isset($lasterrordate)) && ($lasterrordate != 0)) {
                        if ($first == 1) {
                            $sql .= " lasterrordate = $lasterrordate";
                            $first = 0;
                        } else {
                            $sql .= ",\n lasterrordate = $lasterrordate";
                        }
                    }
                    if ((isset($lastuinst)) && ($lastuinst != 0)) {
                        if ($first == 1) {
                            $sql .= " lastuninstall = $lastuinst";
                            $first = 0;
                        } else {
                            $sql .= ", \n lastuninstall = $lastuinst";
                        }
                    }

                    $sql .= "\n where patchid = $patchnum and\n"
                        .  " id = $machineid";

                    $res = command($sql, $db);
                    if (affected($res, $db)) {
                        $used = $used + 1;
                        $rval = constAppNoErr;
                    } else {
                        // command already logged the error.
                    }
                }
                if ($date) {
                    $sql = "select date from Patches where\n"
                        . " patchid = $patchnum";
                    $row = find_one($sql, $db);
                    if ($row) {
                        if ($row['date'] == 0) {
                            /* update the date in Patches */
                            $sql = "update Patches set\n"
                                . " date = $date\n"
                                . " where patchid = $patchnum";
                            $res = command($sql, $db);
                            if (affected($res, $db)) {
                                $rval = constAppNoErr;
                            } else {
                                $when = timestamp($date);
                                $stat = "d:$when,t:$date,n:$patchnum";
                                $text = "patch: $host date failure ($stat) at $site.";
                                logs::log(__FILE__, __LINE__, $text, 0);
                                $rval = constErrDatabaseNotAvailable;
                            }
                        }
                    }
                }
                if ($canuinst) {
                    $sql = "update Patches set\n"
                        . " canuninstall = $canuinst\n"
                        . " where patchid = $patchnum";
                    $res = command($sql, $db);
                }
            }

            /* Now, process the superseded list */
            $superList = @$row[constWUStatPatchSuperBy];
            if (($superList) && ($patchnum != 0)) {
                $sql = "select patchstatusid FROM PatchStatus WHERE "
                    . "id=$machineid AND patchid=$patchnum";
                $row = find_one($sql, $db);
                if ($row) {
                    $sql = "delete from BlockedPatches WHERE "
                        . "patchstatusid=" . $row['patchstatusid'];
                    $res = command($sql, $db);
                    foreach ($superList as $key2 => $row2) {
                        $sql = "SELECT patchid FROM Patches WHERE itemid='"
                            . $key2 . "'";
                        $row3 = find_one($sql, $db);
                        if ($row3) {
                            $sql = "INSERT INTO BlockedPatches ("
                                . "patchstatusid, patchid) VALUES ("
                                . $row['patchstatusid'] . ", "
                                . $row3['patchid'] . ")";
                            $res = command($sql, $db);
                        }
                    }
                }
            }
        }
    }

    $stat = "u:$total,w:$used";
    $text = "patch: $host setstatus ($stat) at $site.";
    logs::log(__FILE__, __LINE__, $text, 0);
    $args['rval'] = $rval;
}



function get_default(&$args)
{
    $rval = constAppNoErr;
    $cfg  = &$args['valu'][1];
    $host = &$args['valu'][2];
    $site = &$args['valu'][3];
    $uuid = &$args['valu'][4];
    $code = &$args['valu'][5];

    $args['olog'] = 0;
    $args['oxml'] = 1;

    $db = db_code('db_pat');
    if (!$db) {
        $args['rval'] = constErrDatabaseNotAvailable;
        $text = "patch: $host getdef, mysql failure at $site.";
        logs::log(__FILE__, __LINE__, $text, 0);
        return;
    }

    if ($args['updatecensus'] == 1) {
        $rval = census_manage($site, $host, $uuid, 1, $db);
        if ($rval != constAppNoErr) {
            $args['rval'] = $rval;
            return;
        }
    }

    /* lookup this machine and retrieve its id */
    $machine = find_census_uuid($uuid, $db);
    $machineid = @intval($machine['id']);

    update_machine_patch($machineid, $db);

    /* get the default patch configuration */
    $machine_row = find_machine_row($machineid, $db);
    if (!$machine_row) {
        $args['rval'] = constErrDatabaseNotAvailable;
        $text = "patch: $host getdef, missing machine on $uuid at $site";
        logs::log(__FILE__, __LINE__, $text, 0);
        return;
    }

    $pcfgarr = search_pconfig($machineid, 0, $db);
    $pconfigid = $pcfgarr['pconfigid'];

    if ($pconfigid == 0) {
        $defcfg = convert_patch_config($pcfgarr);
    } else {
        $defcfg = get_patch_config($machineid, $pconfigid, $db);
        $defcfg[constWUGetPatchMachineGroup] = $pcfgarr['mgrp'];
        $defcfg[constWUGetPatchPatchGroup] = $pcfgarr['pgrp'];
    }

    $cfg = fully_make_alist($defcfg);

    /* default configuration passed back by reference */

    $gid = $pcfgarr['mgroupid'];
    $jid = $pcfgarr['pgroupid'];

    $stat = "p:$pconfigid,g:$gid,j:$jid";
    $text = "patch: $host getdef ($stat) at $site.";
    logs::log(__FILE__, __LINE__, $text, 0);
    debug_note($text);

    $args['rval'] = $rval;
}




function INST_CheckWUTimes(&$args)
{
    $args['valu'][5] = 0;
    find_times($args);
}

function INST_GetWUConfig(&$args)
{
    $args['valu'][5] = 0;
    find_wcfg($args);
}

function INST_SendPatches(&$args)
{
    $args['valu'][5] = 0;
    upload_patch($args);
}

function INST_GetPatches(&$args)
{
    $args['valu'][7] = 0;
    find_patches($args);
}

function INST_SetStatus(&$args)
{
    $args['valu'][5] = 0;
    set_status($args);
}

function INST_GetDefaultConfig(&$args)
{
    $args['valu'][5] = 0;
    get_default($args);
}
