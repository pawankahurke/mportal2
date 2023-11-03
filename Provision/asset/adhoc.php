<?php

/*
Revision history:

Date        Who     What
----        ---     ----
23-Oct-07   BTE     Original creation.
08-Nov-07   BTE     Text change from Alex.
23-Mar-08   BTE     Bug 4433: Add export function to ad-hoc asset queries.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-user.php');
include('../lib/l-alib.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-qtbl.php');
include('../lib/l-jump.php');
include('../lib/l-slct.php');
include('../lib/l-head2.php');
include('../lib/l-grps.php');
include('../lib/l-form.php');
include('../lib/l-js.php');
include('../lib/l-asst.php');
include('../lib/l-cnst.php');
include('../lib/l-errs.php');
include('../lib/l-qury.php');
include('local.php');

/* Action constants */
define('constActSelectFields',  0);
define('constActSelectCrit',    1);

define('constDivName',      'mytree');
define('constAdHocFormName',    'adhocform');
define('constDidsHidden',   'didshid');
define('constOutputFormName',   'adhoc_outform');
define('constOutputFormRun',    0);
define('constOutputFormExport', 1);

function clear_rows()
{
    echo "\n\n";
    echo '<br clear="all">';
    echo "\n\n";
}


/* ADHC_SelectFields

        Handles the field selection page.
    */
function ADHC_SelectFields($db)
{
    /* Draw the YUI tree - this makes heavy use of javascript */
    echo '<script type="text/javascript" language="JavaScript">'
        . 'var tree = new YAHOO.widget.TreeView("' . constDivName . '");'
        . 'var root = tree.getRoot();';
    $names = asset_names($db);
    $names[0]['name'] = 'SYSTEM SUMMARY';
    echo ALIB_DrawYUITree(0, 'root', $names, $db);
    echo 'tree.draw();'
        . 'function setupSelected()'
        . '{'
        . '     dids = "";'
        . '     root = tree.getRoot();'
        . '     getChildren(root);'
        . '     document.getElementById(\'' . constDidsHidden
        .           '\').value=dids;'
        . '}'
        . 'function getChildren(myNode)'
        . '{'
        . '     var i;'
        . '     for(i=0; i<myNode.children.length; i++)'
        . '     {'
        . '         getChildren(myNode.children[i]);'
        . '     }'
        . '     if((myNode.children.length==0) && (myNode.checkState==2))'
        . '     {'
        . '         dids = dids + myNode.data + ",";'
        . '     }'
        . '}</script>';
    echo '<input type="hidden" name="' . constDidsHidden . '" id="'
        . constDidsHidden . '">';
}


/* ADHC_SelectCrit

        Handles the criteria selection page.
    */
function ADHC_SelectCrit($auth, $owned, $db)
{
    /* First, machine groups to include */
    $allgrp = GRPS_ReturnAllMgroupid($db);
    $grps = build_group_list($auth, constQueryNoRestrict, $db);
    $mstr = prep_for_multiple_select($grps);
    $sel_include = saved_search($mstr, $allgrp, 7, constAdHocGroupInc
        . '[]', constMachineGroupMessage);
    echo '<h2>Select Groups of Machines to Include</h2>';
    echo $sel_include;

    /* Second, asset query criteria */
    echo '<h2>Select criteria to retrieve records by</h2><p>';
    echo '<i>Note that the criteria you choose here will be OR-ed '
        . 'together.</i><p>';
    $didstr = get_string(constDidsHidden, '');
    $ownstr = implode(',', $owned);
    $dids = explode(',', $didstr);
    reset($dids);
    foreach ($dids as $key => $val) {
        if ($val) {
            $sql = 'SELECT DISTINCT value,id FROM AssetData WHERE dataid='
                . "$val AND machineid IN ($ownstr) GROUP BY value "
                . "ORDER BY value";
            $vals = find_many($sql, $db);
            $sql = "SELECT name FROM DataName WHERE dataid=$val";
            $dn = find_one($sql, $db);
            echo $dn['name'] . ':&nbsp;&nbsp;&nbsp;&nbsp;<select '
                . 'name="' . constAdHocCritPrefix . $val . '">';
            echo '<option value="' . constAdHocCritNone
                . '">(all)</option>';
            foreach ($vals as $key2 => $row) {
                echo '<option value="' . $row['id'] . '">'
                    . htmlentities($row['value'])
                    . '</option>';
            }
            echo '</select><p>';
        }
    }

    /* Finally, date and display settings */
    /* This code is copied in three places:
                asset/qury-act.php
                asset/qury-add.php
        */
    echo '<h2>Date and display settings</h2>';
    echo '<table border="0" cellpadding="3"><tr><td><b>Select Date:</b>'
        . '</td><td>&nbsp;</td></tr><tr><td><input type="radio" '
        . 'name="DateType" value="RelDate" checked>Relative Date:'
        . '</td><td>';

    global $date_code;

    $date_codes[0] = ' - - - - - - - - - - - - - - -';
    $date_codes[1] = 'latest';
    $date_codes[2] = '1 day ago';
    $date_codes[3] = 'some days ago...'; /* if index changes, change in
                                                outputJavascriptDaysAgo() */
    $date_codes[4] = '1 week ago';
    $date_codes[5] = '1 month ago';
    $date_codes[6] = '3 months ago';
    $date_codes[7] = '6 months ago';
    $date_codes[8] = '1 year ago';

    $select  = html_select('date_code', $date_codes, $date_code, 1);
    $show    = "showElement('rel_days_ago,rel_days_ago_text',"
        . "document." . constAdHocFormName
        . ".date_code.selectedIndex,3,'')";
    $change  = "onChange=\"$show\"";
    $pattern = 'size="1"';
    $replace = "\n  $change $pattern";
    echo str_replace($pattern, $replace, $select);
    $rel_days_ago = ($date_code == 3) ? $date_value : '';
    $exact_checked = '';

    echo '</td><td nowrap><input type="text" size="2" '
        . 'name="rel_days_ago" id="rel_days_ago" '
        . "value=\"$rel_days_ago\"><span id=\"rel_days_ago_text\">"
        . 'days ago</span>';

    outputJavascriptShowElement(
        "rel_days_ago,rel_days_ago_text",
        "document." . constAdHocFormName . ".date_code.selectedIndex",
        "3",
        ""
    );

    echo '</td></tr><tr><td><input type="Radio" Name="DateType" '
        . "Value=\"ExactDate\" $exact_checked>Exact Date: </td>"
        . '<td colspan=2>';
    echo date_selector('', '', '');

    $outform = get_integer(constOutputFormName, constOutputFormRun);
    if ($outform == constOutputFormRun) {
        echo '</td></tr><tr><td colspan=2><br><b>Select Display Options:'
            . '</b></td><td>&nbsp;</td></tr><tr><td colspan=2>Number of '
            . 'Results per Page:</td><td>';
        $rowsizes = array('25', '50', '100');
        echo html_select('rowsize', $rowsizes, '50', 0);
        echo '</td></tr><tr><td colspan=2>Refresh Page Every (in minutes):'
            . '</td><td>';
        $refreshes = array('never', '5', '10', '15');
        echo html_select('refresh', $refreshes, 'never', 0);
    }
    echo '</td></tr></table>';
}


/* ADHC_PrintButtons

        Prints the next/run and cancel buttons, and some optional text
        depending on $first.
    */
function ADHC_PrintButtons($act, $first)
{
    $action = '';
    $nextText = '';
    $formaction = '';
    $outform = get_integer(constOutputFormName, constOutputFormRun);

    switch ($act) {
        case constActSelectFields:
            if ($first) {
                echo '<p>Click on the "+" icon to expand the asset tree, '
                    . 'select one or more data fields you want the query '
                    . 'to retrieve by clicking in the box to the left of '
                    . 'their name, and press \'Next\'.';
            } else {
                echo '<p><input name="' . constOutputFormName . '" type="'
                    . 'radio" checked value="' . constOutputFormRun
                    . '">&nbsp;Run '
                    . 'this query in the browser.<br><input name="'
                    . constOutputFormName . '" type="radio" value="'
                    . constOutputFormExport . '">&nbsp;Export this query to '
                    . 'a file.<p>';
            }
            $action .= 'setupSelected();';
            $nextText = 'Next';
            $formaction = 'adhoc.php';
            break;
        case constActSelectCrit:
            /* No special action here */
            switch ($outform) {
                default:
                case constOutputFormRun:
                    $nextText = 'Run';
                    $formaction = 'exec.php?qid=0&adhoc=1';
                    break;
                case constOutputFormExport:
                    $nextText = 'Export';
                    $formaction = 'export.php?qid=0&adhoc=1';
                    break;
            }
            break;
    }
    if ($first) {
        echo '<form method="post" name="' . constAdHocFormName . '" '
            . 'action="' . $formaction . '">';
    }
    $action .= constAdHocFormName . '.submit();';
    echo '<p><input type="button" value="' . $nextText
        . '" onclick="' . $action
        . '">&nbsp;&nbsp;&nbsp;&nbsp;'
        . '<input type="button" value="Cancel" onclick="window.location.'
        . 'href=\'../welcome.php\'"><p>';
}

/*
    |  Main program
    */

$now = time();
$db  = db_connect();

$authuser = process_login($db);
$comp = component_installed();
$user = user_data($authuser, $db);
$filter = @($user['filtersites']) ? 1 : 0;

$carr   = site_array($authuser, $filter, $db);
$access = db_access($carr);

$act = get_integer('act', constActSelectFields);
$title = 'Ad-hoc';
switch ($act) {
    case constActSelectFields:
        $title = 'Ad-hoc Asset Query - Select Display Fields';
        break;
    case constActSelectCrit:
        $title = 'Ad-hoc Asset Query - Select Retrieval Criteria';
        break;
}

db_change($GLOBALS['PREFIX'] . 'core', $db);
$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo custom_html_header(
    $title,
    $comp,
    $authuser,
    '',
    0,
    0,
    0,
    '<link type="text/css" rel="stylesheet" href="yui/fonts-min.css">'
        . '<link type="text/css" rel="stylesheet" href="yui/treeview.css">'
        . '<link type="text/css" rel="stylesheet" href="yui/tree.css">'
        . '<script src = "yui/yahoo-min.js" ></script>'
        . '<script src = "yui/event-min.js" ></script>'
        . '<script src = "yui/treeview-min.js" ></script>'
        . '<script src = "yui/TaskNode.js" ></script>',
    $db
);
db_change($GLOBALS['PREFIX'] . 'asset', $db);
$owned = asset_access($access, $db);

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users
clear_rows();

ADHC_PrintButtons($act, 1);

switch ($act) {
    case constActSelectFields:
        echo '<input type="hidden" name="act" value="' . constActSelectCrit
            . '">';
        echo '<div id="' . constDivName . '"></div>';
        ADHC_SelectFields($db);
        break;
    case constActSelectCrit:
        ADHC_SelectCrit($authuser, $owned, $db);
        break;
}

ADHC_PrintButtons($act, 0);

echo '</form>';

db_change($GLOBALS['PREFIX'] . 'core', $db);
echo head_standard_html_footer($authuser, $db);
