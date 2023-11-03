<?php

/* addsect.php - add a new summary section component

    This page does not share the common server header or footers, this is
    intentional.
*/

/*
Revision history:

Date        Who     What
----        ---     ----
31-Jul-07   BTE     Original creation.
17-Aug-07   BTE     Changes for summary sections phase 1.
01-Sep-07   BTE     Changes for summary sections phase 2.
04-Sep-07   BTE     Validation for input fields, minor changes.
09-Sep-07   BTE     Text changes.
28-Sep-07   BTE     Changes for summary sections phase 4.

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
include('../lib/l-arpt.php');

define('constJavaScriptContentAreaDiv', 'contarea');
define('constAssetSummaryGroupOne', 'assetgrpone');
define('constAssetSummaryGroupTwo', 'assetgrptwo');
define('constAssetSummaryDataOne', 'assetdataone');
define('constAssetSummaryExcFake', 'assetexcfake');

/* reportconfiguniq for MUM sections group one, update repd.h (same
        constant name) if this changes */
define('constMUMGroupOneReptConfig', 'da48b01a350f9bf1f496530a2d02ba19');

/* WriteAssetOptions

        Yes, this is copied from l-arpt.php (write_JS_order_options), but the
        version there did not allow for easy reuse and modification.
    */
function WriteAssetOptions($searchids, $ctr1, $ctr2, $asrchuniq, $db)
{
    $super = "\nif(!super_init){super_init=1;";
    $super .= "\nsuper_displayfields = new Array();\n";
    $i = 1;
    reset($searchids);
    foreach ($searchids as $key => $row) {
        $super .= ARPT_WriteSingleSearchArray($row, '', $db);
        $i++;
    }
    $super .= '}';
    $nothing = asset_null();
    echo <<< HERE

<script language="JavaScript">

function get_displayfields_for_search(ctr1,asrchuniqs,isMath)
{
    $super

    var mergedArray = new Array;
    var i = 0;
    var j = 0;
    var k = 0;
    var newElement = 1;

    /* This is expensive, I would like a better way to do this */
    for(i=0; i<asrchuniqs.length; i++)
    {
        for(j=0; j<super_displayfields[asrchuniqs[i]].length; j++)
        {
            newElement = 1;
            for(k=0; k<mergedArray.length; k++)
            {
                if(mergedArray[k][1]==super_displayfields[asrchuniqs[i]][j][1])
                {
                    newElement = 0;
                    break;
                }
            }
            if(isMath)
            {
                /* The Site and Machine special options do not apply */
                if(super_displayfields[asrchuniqs[i]][j][0]=='Site Name')
                {
                    newElement = 0;
                }
                if(super_displayfields[asrchuniqs[i]][j][0]=='Machine Name')
                {
                    newElement = 0;
                }
            }
            if(newElement)
            {
                mergedArray[k] = new Array;
                mergedArray[k][0] = super_displayfields[asrchuniqs[i]][j][0];
                mergedArray[k][1] = super_displayfields[asrchuniqs[i]][j][1];
            }
        }
    }

    if(document.getElementById(ctr1))
    {
        fillSelectFromArray(document.getElementById(ctr1),
            mergedArray,
            document.getElementById(ctr1).value, true);
    }
}

</script>

HERE;
}

$db       = db_connect();
$authuser = process_login($db);
$user     = user_data($authuser, $db);
$idx = get_integer('idx', -1);
$act = get_string('act', '');

if ($act == 'new') {
    $title = 'Add Summary Line Item';
} else {
    $title = 'Edit Summary Line Item';
}

echo '<html>';
echo '<head>';
echo "<title>$title</title>";
echo standard_style();
echo '<LINK href="control.css" rel="stylesheet" type="text/css">';

/* control.js will need the functions generated here, so do this now: */
$db = db_select($GLOBALS['PREFIX'] . 'asset');
$qu   = safe_addslashes($authuser);
$sql  = "select * from AssetSearches\n";
$sql .= " where global = 1 or\n";
$sql .= " username = '$qu'\n";
$sql .= " order by name, global";
$searches = find_many($sql, $db);
$controldisposition = get_integer('disposition', 0);

//outputJavascriptShowElement($allf,$chks,$rite,$date);
WriteAssetOptions(
    $searches,
    constAssetSummaryGroupOne,
    constAssetSummaryGroupTwo,
    'A',
    $db
);

echo '<script type="text/javascript" language="JavaScript" src="'
    . 'control.js"></script>';

/* mumSectionArray for name lookups */
REPT_MakeMUMSectionArray($db, $authuser);

/* mumGroupsArray for grouping options */
echo '<script type="text/javascript" language="JavaScript">';
echo 'mumGroupsArray = new Array();';
$db = db_select($GLOBALS['PREFIX'] . 'report');
/* Select only the (ascending) options, and strip off the "(ascending)"
        text. */
$sql = 'SELECT value, TRIM(TRAILING \' (ascending)\' FROM friendlytext) '
    . 'AS friendlytext FROM MultipleValueMapDef WHERE '
    . 'reportconfiguniq=\'' . constMUMGroupOneReptConfig . '\' '
    . ' AND (value LIKE \'%_' . constReptColMapGroupAscStr
    . '\' OR value=\'' . constSpecialOptNoneGroup . '\') ORDER BY orderint';
$set = find_many($sql, $db);
foreach ($set as $key => $row) {
    echo 'mumGroupsArray.push(Array(\'' . safe_addslashes($row['friendlytext'])
        . '\',\'' . $row['value'] . '\'));';
}
echo '</script>';

echo '</head>';
echo '<body>';
echo '<script type="text/javascript" language="JavaScript">'
    . "controlDisposition = $controldisposition;</script>";
echo "<span class=\"heading\">$title</span><p>";
echo '<form method="post">';
echo 'Item name:&nbsp;<input type="hidden" id="' . constRepfFormPrefix
    . constReportConfigSumContAsset . '" value="">';

echo '<input type="text" id="sumname" size="50" '
    . 'value="New Summary Item"><p>';

/* Draw the content area buttons */
echo '<table border="0" cellpadding="0" cellspacing="0"><tbody><tr>'
    . '<td class="tdsel" id="tdassetq"><input id="assetq" name="assetq"'
    . ' value="'
    . 'Asset Queries" class="selbutton" onclick="handleSummaryTabs(\''
    . 'assetq\');" size="21" type="button"></td>'
    . '<td class="tdnosel" id="tdeventf"><input id="eventf" name='
    . '"eventf" value="'
    . 'Event Filters" class="regbutton" onclick="handleSummaryTabs(\''
    . 'eventf\');" size="21" type="button"></td>'
    . '<td class="tdnosel" id="tdmumf"><input id="mumf" name="mumf" '
    . 'value="'
    . 'MUM Filters" class="regbutton" onclick="handleSummaryTabs(\''
    . 'mumf\');" size="21" type="button" style="border-right:1px solid '
    . '#333399;"></td><td><font size="-2">Click on the tab corresponding '
    . 'to the type of summary line item you want to add</font>'
    . '</td></tr></tbody></table>';

/* Draw the content definition area */
echo '<div id="' . constJavaScriptContentAreaDiv . '"></div>';

/* Run the javascript to populate the content area */
echo '<script type="text/javascript" language="JavaScript">'
    . 'handleSummaryTabs(\'assetq\');';
if ($idx != -1) {
    /* We're editing an existing component, get the variables from the
            parent. */
    echo 'idx=' . $idx . ';window.setTimeout(\'updateVars()\',0)';
}
echo '</script>';
echo '<p>';

/* Draw the update and cancel buttons */
echo '<input type="button" value="Update" onclick="if(validateAddSum())'
    . '{addSummaryContent(' . $idx . ');window.close();}">'
    . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" value="Cancel" '
    . 'onclick="window.close();">';

echo '</form>';
if ($controldisposition == constJavaListDispositionView) {
    echo '<script type="text/javascript" language="JavaScript">'
        . 'window.setTimeout(\'makeViewOnly()\', 0);</script>';
}
echo '</body>';
echo '</html>';
