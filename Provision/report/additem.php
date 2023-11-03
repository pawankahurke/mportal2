<?php

/* additem.php - helper page for JavaScript list controls

    This page does not share the common server header or footers, this is
    intentional.
*/

/*
Revision history:

Date        Who     What
----        ---     ----
04-Jun-07   BTE     Original creation.
20-Jun-07   BTE     Bug 4152: Event sections: make sure all buttons work.
31-Jul-07   BTE     Added support for asset queries.
17-Aug-07   BTE     Fixed string quoting issues.

*/

include('../lib/l-cnst.php');
include('../lib/l-db.php');
include('../lib/l-errs.php');
include('../lib/l-util.php');
include('../lib/l-head2.php');
include('../lib/l-serv.php');
include('../lib/l-sql.php');
include('../lib/l-user.php');
include('../lib/l-repf.php');
include('../lib/l-rept.php');
include('../lib/l-jump.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');

function ADDI_AddChecks($listid)
{
    switch ($listid) {
        case constJavaListEventFilters:
            $checktype = constAddCheckTypeEventFilters;
            break;
        case constJavaListEventMgrpInclude:
            $checktype = constAddCheckTypeEventIncludeMgrp;
            break;
        case constJavaListEventMgrpExclude:
            $checktype = constAddCheckTypeEventExcludeMgrp;
            break;
        case constJavaListAssetQueries:
            $checktype = constAddCheckTypeAssetQueries;
            break;
    }
    echo '<input type="button" value="Add Checked" '
        . 'onclick="addChecks(' . $checktype . ');">'
        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<input type="button" value="Check All" '
        . 'onclick="checkAll();">';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<input type="button" value="Uncheck All" '
        . 'onclick="uncheckAll();">';
}

$db       = db_connect();
$authuser = process_login($db);
$user     = user_data($authuser, $db);
$listid   = get_integer('listid', constJavaListEventFilters);

switch ($listid) {
    case constJavaListEventFilters:
        $title = 'Available Event Query Filters';
        break;
    case constJavaListEventMgrpInclude:
    case constJavaListEventMgrpExclude:
        $title = 'Available Machine Groups';
        break;
    case constJavaListAssetQueries:
        $title = 'Available Asset Queries';
        break;
}

echo '<html>';
echo '<head>';
echo "<title>$title</title>";
echo standard_style();
echo '<script type="text/javascript" language="JavaScript" src="'
    . 'control.js"></script>';
echo '</head>';
echo '<body>';
echo "<span class=\"heading\">$title</span><p>";

ADDI_AddChecks($listid);

echo '<p>';

switch ($listid) {
    case constJavaListEventFilters:
        REPF_ListReports(
            $user['username'],
            constSectionAddEventFilters,
            0,
            "additem.php?listid=$listid&act="
        );
        break;
    case constJavaListEventMgrpInclude:
    case constJavaListEventMgrpExclude:
        REPF_ListReports(
            $user['username'],
            constSectionAddMgrpInclude,
            0,
            "additem.php?listid=$listid&act="
        );
        break;
    case constJavaListAssetQueries:
        REPF_ListReports(
            $user['username'],
            constSectionAddAssetQueries,
            0,
            "additem.php?listid=$listid&act="
        );
        break;
}

ADDI_AddChecks($listid);

echo '<script type="text/javascript" language="JavaScript">';
echo "updateAddItemButtons($listid);";

switch ($listid) {
    case constJavaListEventFilters:
        $db = db_select($GLOBALS['PREFIX'] . 'event');
        $sql = 'SELECT searchuniq as uniq, QUOTE(name) FROM SavedSearches';
        break;
    case constJavaListEventMgrpInclude:
    case constJavaListEventMgrpExclude:
        $db = db_select($GLOBALS['PREFIX'] . 'core');
        $sql = 'SELECT mgroupuniq as uniq, QUOTE(name) FROM MachineGroups';
        break;
    case constJavaListAssetQueries:
        $db = db_select($GLOBALS['PREFIX'] . 'asset');
        $sql = 'SELECT asrchuniq as uniq, QUOTE(name) FROM AssetSearches';
        break;
}
$set = find_many($sql, $db);
foreach ($set as $key => $row) {
    echo 'addUniq.push(\'' . $row['uniq'] . '\');';
    echo 'addName.push(' . $row['QUOTE(name)'] . ');';
}
echo '</script>';

echo '</body>';
echo '</html>';
