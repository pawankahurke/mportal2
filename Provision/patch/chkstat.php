<?php

/* csrv.php: A wrapper around the csrv.cgi program. */

/*
Revision history:

Date        Who     What
----        ---     ----
26-Aug-06   BTE     Original creation (bug 3601).
02-Oct-06   BTE     MUM - expand approve updates page to include priority
                    and type.
21-Oct-06   BTE     Use new footer code.
09-Dec-06   BTE     Bug 3842: Make mandatory an update attribute.

*/

$title = 'Microsoft Update Management - Status Check';

include('../lib/l-cnst.php');
include('../lib/l-db.php');
include('../lib/l-errs.php');
include('../lib/l-util.php');
include('../lib/l-head.php');
include('../lib/l-serv.php');
include('../lib/l-sql.php');
include('../lib/l-user.php');
include('../lib/l-gsql.php');
include('../lib/l-rcmd.php');
include('../lib/l-tiny.php');
include('../lib/l-rlib.php');
include('local.php');

function CSTA_GetPatchDetail($patchid, $db)
{
    $row = array();
    $sql = 'SELECT name, type, mandatory FROM Patches WHERE patchid='
        . "$patchid";
    $row = find_one($sql, $db);
    return $row;
}

function CSTA_GetPatchNameFromStatus($patchstatusid, $db)
{
    $sql = "SELECT patchid FROM PatchStatus WHERE "
        . "patchstatusid=$patchstatusid";
    $row = find_one($sql, $db);
    if ($row) {
        $row2 = CSTA_GetPatchDetail($row['patchid'], $db);
        if ($row2) {
            return $row2['name'];
        }
    }
    return '';
}

function CSTA_GetDetails($patchstatusid, $machineid, $db)
{
    $count = 0;
    $sql = "SELECT patchid FROM BlockedPatches WHERE patchstatusid="
        . $patchstatusid;
    $set = find_many($sql, $db);
    if ($set) {
        foreach ($set as $key => $row) {
            $row2 = CSTA_GetPatchDetail($row['patchid'], $db);
            if ($row2) {
                if ($count == 0) {
                    echo "<ul>";
                }
                $status = CSTA_GetStatus($row['patchid'], $machineid, $db);
                echo '<li>' . $row2['name'] . " <b>[$status]</b>";
                if ($row2['mandatory'] == constPatchMandatoryYes) {
                    echo " <b>[Mandatory]</b>";
                }
                $count = $count + 1;
            }
        }
    }

    if ($count == 0) {
        echo "No updates found.";
    } else {
        echo "</ul>";
    }
}

function CSTA_GetStatus($patchid, $machineid, $db)
{
    $states = status_opt();
    $sql = "SELECT status FROM PatchStatus WHERE id=" . $machineid
        . " AND patchid=$patchid";
    $row2 = find_one($sql, $db);
    if ($row2) {
        return $states[$row2['status']];
    }
    return 'no data';
}

function CSTA_GetMachineIDFromStatus($patchstatusid, $db)
{
    $sql = "SELECT id FROM PatchStatus WHERE patchstatusid=$patchstatusid";
    $row = find_one($sql, $db);
    if ($row) {
        return $row['id'];
    }
    return 0;
}

function CSTA_GetMachine($machineid, $db)
{
    $sql = "SELECT site, host FROM " . $GLOBALS['PREFIX'] . "core.Census WHERE id=$machineid";
    $row = find_one($sql, $db);
    if ($row) {
        return $row;
    }
    return '';
}

/* Perform authentication */
$db       = db_connect();
$authuser = process_login($db);
$comp = component_installed();
$nav  = patch_navigate($comp);

echo standard_html_header($title, $comp, $authuser, $nav, 0, 0, $db);

$status = get_string('status', '');
$patchstatusid = get_integer('patchstatusid', 0);

db_change($GLOBALS['PREFIX'] . 'softinst', $db);

$patch = CSTA_GetPatchNameFromStatus($patchstatusid, $db);
$machineid = CSTA_GetMachineIDFromStatus($patchstatusid, $db);
$mach = CSTA_GetMachine($machineid, $db);
$machine = $mach['site'] . ":" . $mach['host'];

switch ($status) {
    case 'super':
        echo "<p><b>Superseded Status Check for $patch on machine "
            . $machine . "</b><p>";
        echo "This update is currently superseded by the following:<p>";
        echo CSTA_GetDetails($patchstatusid, $machineid, $db);
        echo "<p>";
        echo "This means that $patch no longer needs to be installed on "
            . "machine " . $mach['host'] . ".<p>";
        break;
    case 'wait':
        echo "<p><b>Wait Status Check for $patch on machine $machine"
            . "</b><p>";
        echo "This update is currently blocked until the following updates are"
            . " handled:<p>";
        echo CSTA_GetDetails($patchstatusid, $machineid, $db);
        echo "<p>";
        echo "This means that $patch will not be installed on "
            . "machine " . $mach['host'] . " until the above updates are "
            . "either installed or declined (you may not decline mandatory "
            . "updates).<p>";
        break;
    default:
        break;
}

echo head_standard_html_footer($authuser, $db);
