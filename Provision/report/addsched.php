<?php

/* addsched.php - add an existing schedule helper page

    This page does not share the common server header or footers, this is
    intentional.
*/

/*
Revision history:

Date        Who     What
----        ---     ----
24-Feb-07   BTE     Original creation.
14-Mar-07   BTE     Fixed up the title.
03-May-07   BTE     Updated to use checkboxes.

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

function ADSC_AddScheduleButtons()
{
    echo '<input type="button" value="Add Checked" '
        . 'onclick="addChecks(' . constAddCheckTypeSchedule . ');">'
        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    /* No check all button for schedules */
    echo '<input type="button" value="Uncheck All" '
        . 'onclick="uncheckAll();">';
}

$db       = db_connect();
$authuser = process_login($db);
$user     = user_data($authuser, $db);

$title = 'Available Schedules';

echo '<html>';
echo '<head>';
echo "<title>$title</title>";
echo standard_style();
echo '<script type="text/javascript" language="JavaScript" src="'
    . 'control.js"></script>';
echo '</head>';
echo '<body>';
echo "<span class=\"heading\">$title</span><p>";

ADSC_AddScheduleButtons();

echo '<p>';

REPF_ListReports(
    $user['username'],
    constSectionAddSched,
    constActListSchedules,
    'addsched.php?act='
);

ADSC_AddScheduleButtons();

echo '<script type="text/javascript" language="JavaScript">';
echo 'updateAddScheduleButtons();';

$db = db_select($GLOBALS['PREFIX'] . 'schedule');
$sql = 'SELECT scheduniq, name FROM Schedules';
$set = find_many($sql, $db);
foreach ($set as $key => $row) {
    echo 'addUniq.push(\'' . $row['scheduniq'] . '\');';
    echo 'addName.push(\'' . str_replace('\'', '\\\'', $row['name'])
        . '\');';
}
echo '</script>';

echo '</body>';
echo '</html>';
