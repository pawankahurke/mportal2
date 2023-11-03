<?php

/* addsect.php - add an existing section helper page

    This page does not share the common server header or footers, this is
    intentional.
*/

/*
Revision history:

Date        Who     What
----        ---     ----
24-Feb-07   BTE     Original creation.
14-Mar-07   BTE     Fixed up the title, added checkbox controls.
15-Apr-07   BTE     Removed a form that was not needed.
03-May-07   BTE     Updated to support generalized checkbox controls.
22-Jun-07   BTE     Bug 4156: Reports small changes and questions - #2 (minor
                    text things).

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

define('constFormName',     'addsect');

function ADST_AddSectionButtons()
{
    echo '<input type="button" value="Add Checked" '
        . 'onclick="addChecks(' . constAddCheckTypeSection . ');">'
        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<input type="button" value="Check All" '
        . 'onclick="checkAll();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<input type="button" value="Uncheck All" '
        . 'onclick="uncheckAll();">';
}

$db       = db_connect();
$authuser = process_login($db);
$user     = user_data($authuser, $db);

$title = 'Available Sections';

echo '<html>';
echo '<head>';
echo "<title>$title</title>";
echo standard_style();
echo '<script type="text/javascript" language="JavaScript" src="'
    . 'control.js"></script>';
echo '</head>';
echo '<body>';
echo "<span class=\"heading\">$title</span><p>";

ADST_AddSectionButtons();

echo '<p>';

REPF_ListReports(
    $user['username'],
    constSectionAdd,
    constActListAddSections,
    'addsect.php?act='
);

ADST_AddSectionButtons();

echo '<script type="text/javascript" language="JavaScript">';
echo 'updateAddSectionButtons();';

$db = db_select($GLOBALS['PREFIX'] . 'report');
$sql = 'SELECT sectionuniq, sectionname FROM Section';
$set = find_many($sql, $db);
foreach ($set as $key => $row) {
    echo 'addUniq.push(\'' . $row['sectionuniq'] . '\');';
    echo 'addName.push(\'' . str_replace('\'', '\\\'', $row['sectionname'])
        . '\');';
}
echo '</script>';

echo '</body>';
echo '</html>';
