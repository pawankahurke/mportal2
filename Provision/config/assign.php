<?php

/* assign.php - Scrip Configuration Action page */

/*
Revision history:

Date        Who     What
----        ---     ----
27-Apr-06   BTE     Original creation.
01-May-06   BTE     Bug 3355: Scrip Config Status Page: Various Changes.
20-Sep-06   BTE     Added l-tiny.php.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

$title = 'Scrip Configuration Change';

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
include('../lib/l-gcfg.php');
include('../lib/l-grpw.php');
include('../lib/l-dsyn.php');
include('../lib/l-grps.php');
include('../lib/l-gdrt.php');
include('../lib/l-tiny.php');


/* assign_getscoperadio

        Generates part of an html form suitable for selecting the scope
        of an action.  Pass in the action text in $text, the applicable
        ValueMap.valmapid in $valmapid.  If the source page is the basic
        Scrip config status page, $isGroup should be 1 (otherwise 0).
    */
function assign_getscoperadio($text, $valmapid, $isGroup, $db)
{
    /* Gather all the information we need for this specific context. */
    $sql = "SELECT Census.site, Census.host, Variables.scop, "
        . "VarVersions.descval, MachineGroups.name, ValueMap.mgroupuniq, "
        . "ValueMap.last FROM "
        . "ValueMap LEFT JOIN Census ON (ValueMap.censusuniq="
        . "Census.censusuniq) LEFT JOIN Variables ON (ValueMap.varuniq="
        . "Variables.varuniq) LEFT JOIN "
        . "MachineGroups ON (ValueMap.mgroupuniq=MachineGroups.mgroupuniq)"
        . " LEFT JOIN Revisions ON (Census.id=Revisions.censusid) LEFT "
        . "JOIN VarVersions ON (Revisions.vers=VarVersions.vers AND "
        . "ValueMap.varuniq=VarVersions.varuniq) WHERE ValueMap.valmapid="
        . "$valmapid";
    $row = find_one($sql, $db);
    if ($row) {
        /* Now, if we came from the standard status page, we need to
                get the full variable and machine lists. */
        $sql = "SELECT Census.site, Census.host, Variables.scop, "
            . "VarVersions.descval, MachineGroups.name, "
            . "ValueMap.mgroupuniq, ValueMap.last, "
            . "ValueMap.varuniq FROM "
            . "ValueMap LEFT JOIN Census ON (ValueMap.censusuniq="
            . "Census.censusuniq) LEFT JOIN Variables ON ("
            . "ValueMap.varuniq="
            . "Variables.varuniq) LEFT JOIN "
            . "MachineGroups ON (ValueMap.mgroupuniq="
            . "MachineGroups.mgroupuniq)"
            . " LEFT JOIN Revisions ON (Census.id=Revisions.censusid) "
            . "LEFT"
            . " JOIN VarVersions ON (Revisions.vers=VarVersions.vers "
            . "AND "
            . "ValueMap.varuniq=VarVersions.varuniq) WHERE "
            . "ValueMap.mgroupuniq='"
            . $row['mgroupuniq'] . "'";
        $set = find_many($sql, $db);
        if ($set) {
            $vars = "variable(s) <b> ";
            $scrips = "Scrip(s) <b> ";
            $machines = "machine(s) <b> ";
            $vmachines = "machine(s) <b> ";
            $addvars = 0;
            $addscrips = 0;
            $addmachines = 0;
            $vaddmachines = 0;
            foreach ($set as $key => $row2) {
                if (!($isGroup)) {
                    $str = $row2['site'] . ":" . $row2['host'];
                    if (strpos($vmachines, $str) === false) {
                        if ($vaddmachines) {
                            $vmachines .= ", ";
                        }
                        $vmachines .= $str . " (configured "
                            . date('M j, Y H:i:s', $row2['last']) . ")";
                        $vaddmachines = 1;
                    }
                }

                if (strcmp($row['last'], $row2['last']) == 0) {
                    if (strpos($vars, $row2['descval']) === false) {
                        if ($addvars) {
                            $vars .= ", ";
                        }
                        $vars .= $row2['descval'];
                        $addvars = 1;
                    }
                    $str = " " . $row2['scop'];
                    if (strpos($scrips, $str) === false) {
                        if ($addscrips) {
                            $scrips .= ", ";
                        }
                        $scrips .= $row2['scop'];
                        $addscrips = 1;
                    }
                    $str = $row2['site'] . ":" . $row2['host'];
                    if (strpos($machines, $str) === false) {
                        if ($addmachines) {
                            $machines .= ", ";
                        }
                        $machines .= $str;
                        $addmachines = 1;
                    }
                }
            }
            $vars .= "</b>";
            $scrips .= "</b>";
            $machines .= "</b>";
            $vmachines .= "</b>";
        }
        $singlevars = "variable <b>" . $row['descval'] . "</b>";
        $singlescrips = "Scrip <b>" . $row['scop'] . "</b>";
        $singlemachines = "machine <b>" . $row['host'] . "</b> in site <b>"
            . $row['site'] . "</b>";

        if ($isGroup) {
            $individual = "Individual change.  This action will occur"
                . " on $machines only, and only for the $vars defined for "
                . "$scrips.";
        } else {
            $individual = "Individual change.  This action will occur"
                . " on $singlemachines only, and only for the $singlevars "
                . "defined for $singlescrips.";
            $variable = "Variable change.  This action will occur "
                . " on $vmachines, but only for the "
                . "$singlevars defined for $singlescrips.  Essentially, "
                . "this action will occur only on machines "
                . "currently taking the value for this variable from the "
                . "group <b>" . $row['name'] . "</b>.";
        }
        $group = "Group change.  This action will occur on "
            . "potentially multiple machines, for all Scrip configuration "
            . "variables whose value is taken from group <b>"
            . $row['name'] . "</b>.";
        $val = $isGroup ? constAssignScopIndVar :
            constAssignScopIndividual;
        $msg = "<p>Where would you like to " . $text . "</p>"
            . "<p><input type=\"radio\" name=\"scope\" value=\""
            . $val . "\"> $individual<br>";

        if (!($isGroup)) {
            $msg .= "<input type=\"radio\" name=\"scope\" value=\""
                . constAssignScopVariable . "\"> $variable<br>";
        }
        $msg .= "<input type=\"radio\" name=\"scope\" value=\""
            . constAssignScopGroup . "\"> $group<br></p>";
        return $msg;
    } else {
        return "ValueMap $valmapid no longer exists, try again.";
    }
}


/* assign_modify

        Provides html to handle an expiration set request.  Pass in the
        applicable ValueMap.valmapid as $valmapid, and if the source page
        is the basic Scrip config status page, pass in 1 for $isGroup
        (otherwise 0).
    */
function assign_modify($valmapid, $isGroup, $db)
{
    echo "<form method=\"post\" name=\"myform\" action=\"assign.php\">";
    echo "<input type=\"hidden\" name=\"setaction\" value=\""
        . constAssignActionModifyExpire . "\">";
    echo "<input type=\"hidden\" name=\"valmapid\" value=\""
        . $valmapid . "\">";
    echo "<p>When should the change expire?</p><p> Days:";
    echo "<input type=\"text\" name=\"expdays\" value=\"\"> Hours: ";
    echo "<input type=\"text\" name=\"exphours\" value=\"\"> Minutes: ";
    echo "<input type=\"text\" name=\"expminutes\" value=\"\"></p>";
    echo assign_getscoperadio(
        'set the expiration date?',
        $valmapid,
        $isGroup,
        $db
    );
    echo "<p><input type=\"submit\" name=\"setexpire\" value=\"Set "
        . "Expiration\"></p></form>";
}


/* assign_revert

        Provides html to handle a revert request.  Pass in the
        applicable ValueMap.valmapid as $valmapid, and if the source page
        is the basic Scrip config status page, pass in 1 for $isGroup
        (otherwise 0).
    */
function assign_revert($valmapid, $isGroup, $db)
{
    echo "<form method=\"post\" name=\"myform\" action=\"assign.php\">";
    echo "<input type=\"hidden\" name=\"setaction\" value=\""
        . constAssignActionRevert . "\">";
    echo "<input type=\"hidden\" name=\"valmapid\" value=\""
        . $valmapid . "\">";
    echo assign_getscoperadio(
        'revert the change?',
        $valmapid,
        $isGroup,
        $db
    );
    echo "<p><input type=\"submit\" name=\"rever\" value=\"Revert "
        . "Change\"></p></form>";
}


/* assign_delete

        Provides html to handle a group delete request.  Pass in the
        applicable ValueMap.valmapid as $valmapid, and if the source page
        is the basic Scrip config status page, pass in 1 for $isGroup
        (otherwise 0).
    */
function assign_delete($valmapid, $db)
{
    echo "<form method=\"post\" name=\"myform\" action=\"assign.php\">";
    echo "<input type=\"hidden\" name=\"setaction\" value=\""
        . constAssignActionDelete . "\">";
    echo "<input type=\"hidden\" name=\"valmapid\" value=\""
        . $valmapid . "\">";
    echo "<input type=\"hidden\" name=\"scope\" value=\""
        . constAssignScopGroup . "\">";
    echo "Please confirm that you want to revert all changes attached "
        . "to the group and delete the group.";
    echo "<p><input type=\"submit\" name=\"delete\" value=\"Revert "
        . "Changes and Delete Group\"></p></form>";
}

/* Perform authentication */
$db       = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$user     = user_data($authuser, $db);

$dir = $comp['odir'];
$a   = array();
$a[] = html_link("index.php?act=wiz", 'wizards');
$a[] = html_link("status.php", 'status');
$a[] = html_link("status.php?level=1", 'advanced');
$m   = join(' | ', $a);
$nav = "<b>configuration:</b> $m\n<br><br>\n";

$action = get_string('action', '');
$valmapid = get_integer('valmapid', 0);
$isGroup = get_integer('isgroup', 0);
$scope = get_integer('scope', 0);
$setaction = get_integer('setaction', 0);

switch ($action) {
    case 'modify':
        $title .= ' - Modify Expiration Date';
        break;
    case 'revert':
        $title .= ' - Revert Change';
        break;
    case 'delete':
        $title .= ' - Revert Change and Delete Group';
        break;
}

echo standard_html_header($title, $comp, $authuser, $nav, 0, 0, $db);

switch ($action) {
    case 'modify':
        assign_modify($valmapid, $isGroup, $db);
        break;
    case 'revert':
        assign_revert($valmapid, $isGroup, $db);
        break;
    case 'delete':
        assign_delete($valmapid, $db);
        break;
    default:
        switch ($setaction) {
            case constAssignActionModifyExpire:
                $days = get_integer("expdays", 0);
                $hours = get_integer("exphours", 0);
                $minutes = get_integer("expminutes", 0);
                $expire = ($days * 86400) + ($hours * 3600)
                    + ($minutes * 60);
                $now = time() + $expire;
                $num = GCFG_HandleValueMapAction(
                    $setaction,
                    $scope,
                    $valmapid,
                    $now,
                    $db
                );
                echo "Updated the expiration date for $num variable and "
                    . "machine combination(s) to " . date("r", $now);
                break;
            case constAssignActionRevert:
            case constAssignActionDelete:
                /* WARNING - you MUST compute which group is to be deleted
                        BEFORE the revert operation otherwise the WRONG group
                        will be deleted. */
                $row = array();
                if ($setaction == constAssignActionDelete) {
                    $sql = "SELECT mgroupid, MachineGroups.name FROM "
                        . "ValueMap LEFT JOIN "
                        . "MachineGroups ON (ValueMap.mgroupuniq=MachineGroups"
                        . ".mgroupuniq) WHERE valmapid=$valmapid";

                    $row = find_one($sql, $db);
                }
                $num = GCFG_HandleValueMapAction(
                    $setaction,
                    $scope,
                    $valmapid,
                    0,
                    $db
                );
                echo "Updated the assignments and/or attachments for $num "
                    . "variable and machine combination(s).";

                if ($row && $num) {
                    $env = array();
                    $env['auth'] = $authuser;
                    $env['gid'] = $row['mgroupid'];
                    debug_del_mgrp($env, 0, $db);
                    echo "Deleted the group " . $row['name'] . ".";
                }
                break;
            default:
                echo "Invalid action.";
                break;
        }
        break;
}

echo head_standard_html_footer($authuser, $db);
