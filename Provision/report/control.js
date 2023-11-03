<!--
/* HFN Control JavaScript

Contains the JavaScript code necessary for the newer listbox style and the new
scheduling control.

Revision history:

Date        Who     What
----        ---     ----
24-Feb-07   BTE     Original creation.
14-Mar-07   BTE     Cleaned up code, added lots of functionality.
19-Mar-07   BTE     Added special date functionality.
03-Apr-07   BTE     Added setrestrict, bugfixes.
15-Apr-07   BTE     Added all button handling, various bugfixes.
19-Apr-07   BTE     Ensure that immediate reports cannot be disabled.
03-May-07   BTE     Updated to use general checkboxes.
04-May-07   BTE     Added read-only special lists and functions.
09-May-07   BTE     Added makeViewOnly.
04-Jun-07   BTE     Added some support for dynamic JavaScript lists (not
                    finished yet).
20-Jun-07   BTE     Bug 4152: Event sections: make sure all buttons work.
                    Minor text changes.
31-Jul-07   BTE     Added new functions and constants to support summary
                    sections.
17-Aug-07   BTE     Changes for summary sections phase 1.
01-Sep-07   BTE     Changes for summary sections phase 2.
04-Sep-07   BTE     Validation for input fields, minor changes.
09-Sep-07   BTE     Text changes, added move controls for sections.
28-Sep-07   BTE     Changes for summary sections phase 4.
04-Oct-07   BTE     Text changes.
08-Oct-07   BTE     Text changes.
17-Mar-08   BTE     Make textarea read only in view only window.

*/

/* Uncomment the next line to debug this script - do not checkin uncommented */
/* debugger; */

/* Constants - the const keyword does not work in IE so just use variables */
var constScheduleFormName =         'ctrsched';
var constSchedControlName =         'ctrsched_name';
var constSchedControlGlobal =       'ctrsched_global';
var constSchedControlChkImmed =     'ctrsched_chkimmed';
var constSchedControlMonth =        'ctrsched_month';
var constSchedControlDay =          'ctrsched_day';
var constSchedControlYear =         'ctrsched_year';
var constSchedControlHour =         'ctrsched_hour';
var constSchedControlMinute =       'ctrsched_minute';
var constSchedControlStartTime =    'ctrsched_starttime';
var constSchedControlEndTime =      'ctrsched_endtime';
var constSchedControlNotify =       'ctrsched_notify';
var constSchedControlDelay =        'ctrsched_delay';
var constSchedControlWeekSel =      'ctrsched_weeksel';
var constSchedControlMDaySel =      'ctrsched_mdaysel';
var constSchedControlStartMin =     'ctrsched_startmin';
var constSchedControlIntMin =       'ctrsched_intmin';
var constSchedControlRandMin =      'ctrsched_randmin';
var constSchedControlProxyEnable =  'ctrsched_proxyenable';
var constSchedControlProxyMin =     'ctrsched_proxymin';
var constSchedControlStartDay =     'ctrsched_startday';
var constSchedControlDayInt =       'ctrsched_intday';
var constSchedControlCancel =       'ctrsched_cancel';
var constSchedControlType =         'ctrsched_type';
var constSchedControlRunTime =      'ctrsched_runtime';
var constSchedControlHourMask =     'ctrsched_hourmask';
var constSchedControlWeekDayMask =  'ctrsched_weekdaymask';
var constSchedControlMonthMask =    'ctrsched_monthmask';
var constActCheckboxAppend =        '_chkbox_append';
var constSchedTypeOneTime =         1;
var constSchedTypeImmediate =       3;
var constJavaScriptRunOnceName =    'runonce';
var constJavaScriptSchedulesName =  'schedules';
var constJavaScriptSchedHelpDiv =   'sched_help';
var constSchedHelpRunOnce =         '<font size="-2">If you click on the '
    + '"Immediate" box, this report will run once only covering the period of '
    + 'time you enter in the "Start time" and "End time" fields. After the '
    + 'report runs, it will not be listed in the "Reports" page any longer.  '
    + 'If you do not choose to make this report "Immediate", you will need to '
    + 'enter a date and time in the future when you want this one-time report '
    + 'to run, and the period of time you want the report to cover, in the '
    + '"Start time" and "End time" fields. Please note that even in this '
    + 'case, the report runs once, and will not be listed in the "Reports" '
    + 'page any longer after it runs.</font>';
var constSchedHelpSchedules =       '<font size="-2">You can assign '
    + 'multiple schedules to a report. If you do so, the report will run at '
    + 'the times specified in the schedules you select.<br>If you want to '
    + 'select an existing schedule, please click on the "Add existing" '
    + 'button. The "Available Schedules" page will be displayed in a new '
    + 'window. After you select a schedule, its entry will be automatically '
    + 'added to the list of schedules for this report.  You can change the '
    + 'settings of an existing schedule by clicking on "Edit..." after you '
    + 'select it.<br>If you want this report to run on a schedule not '
    + 'included in the "Available Schedules" page, you can add a new '
    + 'schedule by clicking on the "Add new" button. The "Add a Schedule" '
    + 'page will be displayed in a new window. After you add a new schedule, '
    + 'its entry will be automatically added to the list of schedules used '
    + 'for the report you are creating.</font>';
var constDateHelpText = 'date is mm/dd or mm/dd/yy or mm/dd hh:mm';
var constAllLabel = 'All';
var constNoneLabel = 'None';
var constAllMonths = 4095;
var constAllHours = 16777215;
var constAllWeekDays = 127;
var constReportEnabled = 2;
var constRepfFormPrefix = 'ctrform_';
var constJavaScriptContentAreaDiv = 'contarea';

/* Summary section asset query help text */
var constAssetQueryGroupHelp = '<hr>If you want to group information '
    + 'retrieved by '
    + 'this item, please select grouping parameters below. Please note that '
    + 'if you want the item to include percentages, you will need to select '
    + 'at least one grouping parameter.<p>'
    + '"Raw data type" is the unit of measurement of the grouping parameter '
    + 'you select.';
var constAssetQueryDataHelp = 'Select a data value for AVG, MIN, and MAX:';
var constAssetQueryGroupSetHelp = '<b>First and second grouping settings:</b>';
var constAssetQueryDataSetHelp = '<b>Math function settings (configures the '
    + 'value and data format for AVG, MIN, and MAX):</b>';

/* Summary section event filter help text */
var constEventFilterFilterHelp = '<font size="-2">'
    + 'You can assign multiple event filters to '
    + 'a summary line item. If you do so, the line item will include events '
    + 'from all the selected event filters unless you select the "Only '
    + 'events matching all filters will be included" option below in which '
    + 'case only events satisfying the filtering conditions of ALL event '
    + 'filters selected will be included.</font><p><font size="-2">'
    + 'If you want to select an existing event filter, please click on the '
    + '"Add existing" button. The "Available Event Query Filters" page will '
    + 'be displayed in a new window. After you select an event filter, its '
    + 'entry will be automatically added to the list of event filters used '
    + 'for this line item. You can change the settings or an existing event '
    + 'filter by clicking on "Edit..." button after you select it.</font>'
    + '<p><font size="-2">'
    + 'If you want this line item to include an event filter not included in '
    + 'the "Available Event Query Filters" page, you can add a new event '
    + 'filter by clicking on the "Add new ..." button. The "Add a Query '
    + 'Filter" page will be displayed in a new window. After you add a new '
    + 'event filter, in order to use it for this line item click on the '
    + '"here" link in the "Click here to add this new filter ..." phrase at '
    + 'the bottom of the "Query Filter Added" page.</font>'
    + '<p><font size="-2">'
    + 'If you want to select an existing event filter not included in the '
    + '"Event filters" list on this page, please click on the "Add existing" '
    + 'button. The "Available Event Query Filters" page will be displayed in '
    + 'a new window. After you select an event filter, its entry will be '
    + 'automatically added to the list of event filters used for this line '
    + 'item.  If you want this line item to include an event filter not '
    + 'included in the "Event filter" list on this page, or in the '
    + '"Available Event Query Filters" page, you can add a new event filter '
    + 'by clicking on the "Add new" button. The "Add a Query Filter" page '
    + 'will be displayed in a new window. After you add a new event filter, '
    + 'in order to use it for this line item click on the "here" link in the '
    + '"Click here to add this new filter ..." phrase at the bottom of the '
    + '"Query Filter Added" page.</font>';
var constEventFilterGroupHelp = '<hr>If you want to group information '
    + 'retrieved by '
    + 'this item, please select grouping parameters below. Please note that: '
    + '<br>a) If you want the item to include percentages, you will need to '
    + 'select at least one grouping parameter<br>'
    + 'b) The AVG, MAX, and MIN statistics are not meaningful, and should not '
    + 'be selected, for items where you select the "Limit the output to the '
    + 'top X entries" configuration option below.<p>'
    + '"Raw data type" is the unit of measurement of the grouping parameter '
    + 'you select.';
var constEventFilterClauseOrHelp = 'Events matching at least one filter, but '
    + 'not necessarily all filters, will be included.';
var constEventFilterClauseAndHelp = 'Only events matching all filters will be '
    + 'included.';
var constEventFilterMathHelp = '<hr>Statistics settings: From the "Data field"'
    + ' pull-down list select the type of data you want "Total", "AVG", "MIN",'
    + ' and "MAX" to be calculated for. The possible choices are:<br>'
    + '- Count -- This refers to event count, and can be used with any event '
    + 'filter<br>'
    + '- Machine -- can be used with any event filter<br>'
    + '- Files deleted -- can be used only with event filters for Scrips 60, '
    + '217-221 (Folder and file clean-up)<br>'
    + '- Services -- can be used only with event filters for Scrip 176 '
    + '(Services restart)<br>'
    + '- Size -- can be used only with event filters for Scrip 174 (User '
    + 'logon-logoff tracking)<br>'
    + '- ID -- can be used only with event filters for Scrip 77 (Windows '
    + 'event log changes detected)<br>'
    + '- String1 -- can be used only with event filters for Scrips that '
    + 'output information to String1<br>'
    + '- String2 -- can be used only with event filters for Scrips that '
    + 'output information to String2<p>'
    + '"Raw data type" is the unit of measurement of the data field you '
    + 'select.';
var constMUMFilterHelp = 'Choose a MUM section for selection criteria, or '
    + 'set to "None" to include all Microsoft update management history:';
var constMUMFilterMathHelp = 'Statistics settings: From the "Data field"'
    + ' pull-down list select the type of data you want "Total", "AVG", "MIN",'
    + ' and "MAX" to be calculated for. The possible choices are:<br>'
    + '- Count -- This refers to the number of updates retrieved using the '
    + 'selection criteria for this item.  AVG is the average number of '
    + 'updates per machine.<br>'
    + '- Machine -- This refers to machine count for Total and AVG.';

/* Summary section config names */
var constReportConfigSumContAsset = 'sumcontasset';
var constAssetSummaryGroupOne = 'assetgrpone';
var constAssetSummaryGroupOneRaw = 'assetgrponeraw';
var constAssetSummaryGroupOneDec = 'assetgrponedec';
var constAssetSummaryGroupTwo = 'assetgrptwo';
var constAssetSummaryGroupTwoRaw = 'assetgrptworaw';
var constAssetSummaryGroupTwoDec = 'assetgrptwodec';
var constAssetSummaryDataOne = 'assetdataone';
var constAssetSummaryDataOneRaw = 'assetdataoneraw';
var constAssetSummaryDataOneDec = 'assetdataonedec';
var constAssetSummaryExcFake = 'assetexcfake';
var constAssetQuerySetting = 'assetsetting';
var constEventFilterSetting = 'eventsetting';
var constEventFilterLimitTop = 'eventlimittop';
var constEventFilterLimitNum = 'eventlimitnum';
var constSummaryLineType = 'sumlinetype';
var constEventFilterLimitPer = 'eventlimitper';
var constEventFilterLimitPerNum = 'eventlimitpernum';
var constMUMFilterSelect = 'mumselect';
var constMUMFilterLimitCount = 'mumfiltercount';
var constMUMFilterLimitSel = 'mumfilterlimit';
var constMUMFilterLimitNum = 'mumfilterlimitnum';
var constMUMFilterLimitPerSel = 'mumfilterlimitpersel';

/* Summary section asset query config values */
var constAssetQueryGroupOnlyVal = 1;
var constAssetQueryDataOnlyVal = 2;

/* Summary section event filter config values */
var constEventFilterClauseValOr = 1;
var constEventFilterClauseValAnd = 2;

/* addChecks.type constants */
var constAddCheckTypeSection = 0;
var constAddCheckTypeSchedule = 1;
var constAddCheckTypeEventFilters = 2;
var constAddCheckTypeEventIncludeMgrp = 3;
var constAddCheckTypeEventExcludeMgrp = 4;
var constAddCheckTypeAssetQueries = 5;

/* special control dispostions */
var constJavaListDispositionEdit = 0;
var constJavaListDispositionView = 1;

/* Summary line item constants */
var constSummaryItemAsset = 0;
var constSummaryItemEvent = 1;
var constSummaryItemMUM = 2;

/* Summary section content control identifiers */
var constReptCtrSummaryUniq = 0;
var constReptCtrSummaryName = 1;
var constReptCtrSummaryType = 2;
var constReptCtrSummaryOrder = 3;
var constReptCtrSummaryData = 4;
var constReptCtrSummaryGroupOne = 5;
var constReptCtrSummaryGroupTwo = 6;
var constReptCtrSummaryDataOne = 7;
var constReptCtrSummaryDispTotal = 8;
var constReptCtrSummaryDispPer = 9;
var constReptCtrSummaryDispAvg = 10;
var constReptCtrSummaryDispMin = 11;
var constReptCtrSummaryDispMax = 12;
var constReptCtrSummaryExcFake = 13;
var constReptCtrSummaryGroupOneRaw = 14;
var constReptCtrSummaryGroupOneDec = 15;
var constReptCtrSummaryGroupTwoRaw = 16;
var constReptCtrSummaryGroupTwoDec = 17;
var constReptCtrSummaryDataOneRaw = 18;
var constReptCtrSummaryDataOneDec = 19;
var constReptCtrSummaryAssetSetting = 20;
var constReptCtrSummaryEventSetting = 21;
var constReptCtrSummaryEventLimitTop = 22;
var constReptCtrSummaryEventLimitNum = 23;
var constReptCtrSummaryEventLimitPer = 24;
var constReptCtrSummaryEventLimitPerNum = 25;
var constReptCtrSummaryMUMFilter = 26;
var constReptCtrSummaryMUMLimitCount = 27;
var constReptCtrSummaryMUMLimitSel = 28;
var constReptCtrSummaryMUMLimitNum = 29;
var constReptCtrSummaryMUMLimitPerSel = 30;

/* Validation text */
var constDecimalValidationFail = 'Validation failure - decimal precision '
    + 'must be a positive integer, 0, or an empty string.';
var constLimitNumValidationFail = 'Validation failure - limit the output to '
    + 'the top X entries must be a positive integer or an empty string.';
var constLimitPerNumValidationFail = 'Validation failure - only include '
    + 'machines where the number of records retrieved is greater than or '
    + 'equal to X% must be an integer between 0 and 100 or an empty string.';
var constLimitUpdatesValidationFail = 'Validation failure - limit the output '
    + 'to machines with at least/most X updates must be a positive integer '
    + 'or an empty string.';

/* report.php isparent settings */
var constIsParentNothing = 0;
var constIsParentNewSection = 1;
var constIsParentEditSection = 2;
var constIsParentNewMUMFilter = 3;
var constIsParentEditMUMFilter = 4;

/* l-repf shared constants */
var constSectionMUMReport = 9;

/* Create an empty array for schedules - both values and friendly names */
var scheduleArray = new Array;
var scheduleNameArray = new Array;
var scheduleUniq = '';

/* Create an empty array for sections - both values and friendly names */
var sectionArray = new Array;
var sectionNameArray = new Array;
var sectionUniq = '';

/* Adding multiple sections at once also requires arrays */
var addUniq = new Array;
var addName = new Array;

/* Strings for dynamic tab controls */
var oneTimeHTML = '';
var intervalHTML = '';
var weekHTML = '';
var monthHTML = '';
var runonceHTML = '';
var schedulesHTML = '';

/* Special date control arrays */
var dateDivArray = new Array;
var dateSchedArray = new Array;
var dateRestArray = new Array;
var dateStartArray = new Array;
var dateStartValArray = new Array;
var dateEndArray = new Array;
var dateEndValArray = new Array;
var dateSchedValueArray = new Array;

/* State control drop down on the report page */
var stateControl = '';

/* For special lists/controls, this value may be set at runtime if we are
    viewing a report instead of changing it. */
var controlDisposition = constJavaListDispositionEdit;

/* JavaScript controlled lists */
var dynamicLists = new Array;
var dynamicNames = new Array;
var dynamicUniqs = new Array;
var dynamicDivs = new Array;
var constJavaListEventFilters = 0;
var constJavaListEventMgrpInclude = 1;
var constJavaListEventMgrpExclude = 2;
var constJavaListAssetQueries = 3;
var constDynamicListUniqs = 0;
var constDynamicListNames = 1;
var constDynamicListGroupOne = 2;
var constDynamicListGroupTwo = 3;
var constDynamicListDataOne = 4;
var constDynamicListDispTotal = 5;
var constDynamicListDispPer = 6;
var constDynamicListDispAvg = 7;
var constDynamicListDispMin = 8;
var constDynamicListDispMax = 9;
var constDynamicListExcFake = 10;
var constDynamicListGroupOneRaw = 11;
var constDynamicListGroupOneDec = 12;
var constDynamicListGroupTwoRaw = 13;
var constDynamicListGroupTwoDec = 14;
var constDynamicListDataOneRaw = 15;
var constDynamicListDataOneDec = 16;
var constDynamicListAssetSetting = 17;
var constDynamicListEventSetting = 18;
var constDynamicListEventLimitTop = 19;
var constDynamicListEventLimitNum = 20;
var constDynamicListEventLimitPer = 21;
var constDynamicListEventLimitPerNum = 22;
var constDynamicListMUMFilter = 23;
var constDynamicListMUMLimitCount = 24;
var constDynamicListMUMLimitSel = 25;
var constDynamicListMUMLimitNum = 26;
var constDynamicListMUMLimitPerSel = 27;

/* deleteShiftArray.type constants */
var constLiteralType = 0;
var constArrayType = 1;

/* Data units */
var constDataUnitsNone = 1
var constDataUnitsSeconds = 2;
var constDataUnitsPercent = 3;
var constDataUnitsBytes = 4;
var constDataUnitsKBytes = 5;
var constDataUnitsPerSecond = 6;

/* Note: add new dynamic lists here before using! */
dynamicLists[constJavaListEventFilters] = new Array;
dynamicNames[constJavaListEventFilters] = new Array;
dynamicLists[constJavaListEventMgrpInclude] = new Array;
dynamicNames[constJavaListEventMgrpInclude] = new Array;
dynamicLists[constJavaListEventMgrpExclude] = new Array;
dynamicNames[constJavaListEventMgrpExclude] = new Array;
dynamicLists[constJavaListAssetQueries] = new Array;
dynamicNames[constJavaListAssetQueries] = new Array;

/* summary content controls - only one is supported per config page */
var summaryDivUniq = '';
var summaryNames = new Array;
var summaryUniqs = new Array;
var summaryTypes = new Array;
var summaryTypeData = new Array;

/* Index of component being edited within this script */
var idx = -1;

/* Special array for asset queries */
var super_displayfields = new Array;
var super_init = 0;

/* Special arrays for MUM filters */
var mumSectionsArray = new Array;
var mumGroupsArray = new Array;

/* display

    Displays an "element" on the page.
*/
function display(element)
{
    if (document.layers && document.layers[element] != null)
    {
        document.layers[element].visibility = 'visible';
    }
    else if (document.all)
    {
        document.all[element].style.visibility = 'visible';
    }
}


/* hide

    Hides an "element" on the page.
*/
function hide(element)
{
    if (document.layers && document.layers[element] != null)
    {
        document.layers[element].visibility = 'hidden';
    }
    else if (document.all)
    {
        document.all[element].style.visibility = 'hidden';
    }
}


/* addSection

    Opens up the "add an existing section" page.
*/
function addSection()
{
    window.open('addsect.php');
}


/* addSchedule

    Opens up the "add an existing schedule" page.
*/
function addSchedule()
{
    window.open('addsched.php');
}


/* updateSection

    Updates the div HTML for the control "reportconfiguniq" which is a dynamic
    list of sections.
*/
function updateSection(reportconfiguniq)
{
    if(reportconfiguniq!='')
    {
        sectionUniq = reportconfiguniq;
    }

    var str = '<table border="0"><tr><td>';

    if(sectionNameArray.length==0)
    {
        str += 'No sections have been added yet.<br><br>';
    }
    else
    {
        str += '<div style="height:150px;width:400px;overflow:auto;">'
            + '<table class="mytable" cellpadding="3" cellspacing="0" style="'
            + 'width:380px"><tr><th class="mycol" bgcolor="#333399"><font color="'
            + 'white"> Section Name</font></th><th class="mycol" bgcolor="'
            + '#333399"><font color="white">';
        if(controlDisposition==constJavaListDispositionEdit)
        {
            str += 'Edit</font></th><th class="mycol"'
                + ' bgcolor="#333399"><font color="white">Remove</font>'
                + '<th class="mycol" bgcolor="#333399"><font color="white">'
                + 'Move</th>';
        }
        else
        {
            str += 'View</font></th>';
        }
        str += '</tr>';
        for(i=0; i<sectionNameArray.length; i++)
        {
            str += '<tr><td class="mycol">' + sectionNameArray.slice(i, i+1)
                + '</td><td class="mycol" align="center"><input type="button"'
                + ' value="';
            if(controlDisposition==constJavaListDispositionEdit)
            {
                str += 'Edit..." onclick="editSection(\''
                    + sectionArray.slice(i, i+1) + '\','
                    + constIsParentEditSection + ');"></td><td class='
                    + '"mycol" align="center"><input type="button" value="Remove"'
                    + ' onclick="removeSection(' + i + ');"></td>';
                str += getMoveHtml(i, 'shiftSection', sectionNameArray.length);
                str += '</tr>';
            }
            else
            {
                str += 'View..." onclick="viewSection(\''
                    + sectionArray.slice(i, i+1) + '\');"></td></tr>';
            }
        }
        str += '</table></div>';
    }

    str += '</td>';
    if(controlDisposition==constJavaListDispositionEdit)
    {
        str += '<td>'
        + '<input type="button" value="Add New..." onclick="newSection();">'
        + '<br><br>'
        + '<input type="button" value="Add Existing..." onclick='
        + '"addSection();"></td>';
    }

    str += '</tr></table>';

    /* Update the hidden variable with the current values */
    value = '';
    first = true;
    for(i=0; i<sectionArray.length; i++)
    {
        if(!first)
        {
            value += ', ';
        }
        value += sectionArray.slice(i, i+1);
        first = false;
    }

    document.getElementById(constRepfFormPrefix + sectionUniq).value = value;

    sections.innerHTML = str;
}


/* addSectionButton

    Handles an onclick event for the "Add" button from addsect.php.  "value"
    is the underlying sectionuniq, and "text" is the friendly name of that
    section.
*/
function addSectionButton(value, text)
{
    window.opener.addSectionArray(value, text);
    window.opener.updateSection('');
}


/* addSectionArray

    Adds a new section to our section array.  "value" is sectionuniq, "text"
    is the friendly text of the section.
*/
function addSectionArray(value, text)
{
    sectionArray.push(value);
    sectionNameArray.push(text);
}


/* updateAddSectionButtons

    This reads the parent's section array variable and determines which
    buttons in addsect.php should be grayed out since they are already
    associated with the report.
*/
function updateAddSectionButtons()
{
    for(i=0; i<window.opener.sectionArray.length; i++)
    {
        if(document.getElementById(window.opener.sectionArray.slice(i, i+1)))
        {
            document.getElementById(window.opener.sectionArray.slice(i, i+1))
                .disabled = true;
            document.getElementById(window.opener.sectionArray.slice(i, i+1))
                .checked = true;
        }
    }
}


/* updateAddScheduleButtons

    This reads the parent's schedule array variable and determines which
    buttons in addsched.php should be grayed out since they are already
    associated with the report.
*/
function updateAddScheduleButtons()
{
    for(i=0; i<window.opener.scheduleArray.length; i++)
    {
        if(document.getElementById(window.opener.scheduleArray.slice(i, i+1)))
        {
            document.getElementById(window.opener.scheduleArray.slice(i, i+1))
                .disabled = true;
            document.getElementById(window.opener.scheduleArray.slice(i, i+1))
                .checked = true;
        }
    }
}


/* setimmediate

    Handles the immediate checkbox changing value for one-time schedules.
*/
function setimmediate()
{
    var ctrlName = constSchedControlChkImmed + constActCheckboxAppend;
    var ctrlEnable = document.getElementById(ctrlName).checked;

    document.getElementById(constSchedControlMonth).disabled = ctrlEnable;
    document.getElementById(constSchedControlDay).disabled = ctrlEnable;
    document.getElementById(constSchedControlYear).disabled = ctrlEnable;
    document.getElementById(constSchedControlHour).disabled = ctrlEnable;
    document.getElementById(constSchedControlMinute).disabled = ctrlEnable;

    /* Update the hidden schedule type to match */
    if(ctrlEnable)
    {
        document.getElementById(constSchedControlType).value
            = constSchedTypeImmediate;
    }
    else
    {
        document.getElementById(constSchedControlType).value
            =  constSchedTypeOneTime;
    }

    if(stateControl!='')
    {
        document.getElementById(stateControl).onchange = statechanged;
        statechanged();
    }
}


/* handleSchedTabs

    Updates the appropriate div inner html given a tabname "x".
*/
function handleSchedTabs(x)
{
    if(x=='onetime')
    {
        eval(constScheduleFormName).onetime.className = 'selbutton';
        eval(constScheduleFormName).interval.className = 'regbutton';
        tdonetimeb.className = 'tdsel';
        tdintervalb.className = 'tdnosel';
        allcontrols.innerHTML = oneTimeHTML;
    }
    if(x=='interval')
    {
        eval(constScheduleFormName).onetime.className = 'regbutton';
        eval(constScheduleFormName).interval.className = 'selbutton';
        tdonetimeb.className = 'tdnosel';
        tdintervalb.className = 'tdsel';
        allcontrols.innerHTML = intervalHTML;
    }
    if(x=='week')
    {
        daycont.innerHTML = weekHTML;
        tdweekb.className = 'tdsel';
        tdmdayb.className = 'tdnosel';
        ctrsched.ctrsched_weeksel.className = 'selbutton';
        ctrsched.ctrsched_mdaysel.className = 'regbutton';
    }
    if(x=='mday')
    {
        daycont.innerHTML = monthHTML;
        tdweekb.className = 'tdnosel';
        tdmdayb.className = 'tdsel';
        ctrsched.ctrsched_weeksel.className = 'regbutton';
        ctrsched.ctrsched_mdaysel.className = 'selbutton';
    }
}


/* switchClass

    Swaps the CSS class for button control "x".
*/
function switchClass(x)
{
    if(document.getElementById(x).className=='regbuttonsm')
    {
        document.getElementById(x).className = 'selbuttonsm';
    }
    else
    {
        document.getElementById(x).className = 'regbuttonsm';
    }
}


/* switchClassEnd

    Swaps the CSS class for button control "x" where "x" is the last control
    in the table row.
*/
function switchClassEnd(x)
{
    if(document.getElementById(x).className=='regbuttonsmend')
    {
        document.getElementById(x).className = 'selbuttonsm';
    }
    else
    {
        document.getElementById(x).className = 'regbuttonsmend';
    }
}


/* printBox

    Returns a HTML button with an id of "id", a value of "val" and a class name
    of "css".  Adds the appropriate onclick handler for switching classes.

    FIX ME: is this still used?  I think the C code generates this now.
*/
function printBox(id, val, css)
{
    func = 'switchClass';
    if(css=='regbuttonsmend')
    {
        func = 'switchClassEnd';
    }
    return '<input type="button" id=' + id + ' value="' + val + '" class="' + css
        + '" onclick="' + func + '(\'' + id + '\');">';
}


/* setDivHTML

    Stores the HTML for one-time schedules "oneTime", interval schedules
    "interval", weekday tab "week", and month days tab "month".
*/
function setDivHTML(oneTime, interval, week, month)
{
    oneTimeHTML = oneTime;
    intervalHTML = interval;
    weekHTML = week;
    monthHTML = month;
}


/* setMask

    Updates "hiddenVar" by exclusively or-ing it with "value".  This is used
    for the buttons.
*/
function setMask(hiddenVar, value)
{
    /* exclusive OR:
        1. If this bit is already set, this will unset it.
        2. If this bit is not set, it will be set.
    */
    document.getElementById(hiddenVar).value
        = document.getElementById(hiddenVar).value ^ value;
}


/*  handleSchedSpecTabs

    Updates the div html for the run-once/schedules control in reports.
*/
function handleSchedSpecTabs(x)
{
    if(x==constJavaScriptRunOnceName)
    {
        document.getElementById(constJavaScriptRunOnceName).className
            = 'selbutton';
        document.getElementById(constJavaScriptSchedulesName).className
            = 'regbutton';
        tdrunonceb.className = 'tdsel';
        tdschedulesb.className = 'tdnosel';
        schedspecdiv.innerHTML = runonceHTML;
        setimmediate();
        document.getElementById(constJavaScriptSchedHelpDiv).innerHTML
            = constSchedHelpRunOnce;
    }
    if(x==constJavaScriptSchedulesName)
    {
        document.getElementById(constJavaScriptRunOnceName).className
            = 'regbutton';
        document.getElementById(constJavaScriptSchedulesName).className
            = 'selbutton';
        tdrunonceb.className = 'tdnosel';
        tdschedulesb.className = 'tdsel';
        schedspecdiv.innerHTML = schedulesHTML;
        updateSchedule('');
        document.getElementById(constJavaScriptSchedHelpDiv).innerHTML
            = constSchedHelpSchedules;
    }
}


/* setSchedDiv

    Stores HTML for the run-once tab "runonce", schedules tab "schedules", and
    the unique identifier for the hidden run-once schedule "scheduniq".
*/
function setSchedDiv(runonce, schedules, scheduniq)
{
    runonceHTML = runonce;
    schedulesHTML = schedules;
    scheduleUniq = scheduniq;
}


/* updateSchedule

    Updates the schedules tab for the report control "reportconfiguniq".
*/
function updateSchedule(reportconfiguniq)
{
    if(reportconfiguniq!='')
    {
        scheduleUniq = reportconfiguniq;
    }

    var str = '<table border="0"><tr><td>';

    if(scheduleNameArray.length==0)
    {
        str += 'No schedules have been added yet.<br><br>';
    }
    else
    {
        str += '<div style="height:150px;width:400px;overflow:auto;">'
            + '<table class="mytable" cellpadding="3" cellspacing="0" style="'
            + 'width:380px"><tr><th class="mycol" bgcolor="#333399"><font color="'
            + 'white"> Schedule Name</font></th><th class="mycol" bgcolor="'
            + '#333399"><font color="white">';
        if(controlDisposition==constJavaListDispositionEdit)
        {
            str += 'Edit</font></th><th class="mycol"'
                + ' bgcolor="#333399"><font color="white">Remove</font>';
        }
        else
        {
            str += 'View</font>';
        }

        str += '</th></tr>';
        for(i=0; i<scheduleNameArray.length; i++)
        {
            str += '<tr><td class="mycol">' + scheduleNameArray.slice(i, i+1)
                + '</td><td class="mycol" align="center"><input type="button"'
                + ' value="';
            if(controlDisposition==constJavaListDispositionEdit)
            {
                str += 'Edit..." onclick="editSchedule(\''
                    + scheduleArray.slice(i, i+1) + '\');"></td><td class='
                    + '"mycol" align="center"><input type="button" value="Remove"'
                    + ' onclick="removeSchedule(' + i + ');"></td></tr>';
            }
            else
            {
                str += 'View..." onclick="viewSchedule(\''
                    + scheduleArray.slice(i, i+1) + '\');"></td></tr>';
            }
        }
        str += '</table></div>';
    }

    str += '</td>';
    if(controlDisposition==constJavaListDispositionEdit)
    {
        str += '<td>'
        + '<input type="button" value="Add New..." onclick="newSchedule();">'
        + '<br><br>'
        + '<input type="button" value="Add Existing..." onclick='
        + '"addSchedule();"></td>';
    }

    str += '</tr></table>';

    /* Update the hidden variable with the current values */
    value = '';
    first = true;
    for(i=0; i<scheduleArray.length; i++)
    {
        if(!first)
        {
            value += ', ';
        }
        value += scheduleArray.slice(i, i+1);
        first = false;
    }

    if(document.getElementById(constRepfFormPrefix + scheduleUniq))
    {
        document.getElementById(constRepfFormPrefix + scheduleUniq).value
            = value;
        scheds.innerHTML = str;
    }
}


/* addScheduleButton

    Handles the add button push from addsched.php.  Appends the scheduniq
    "value" and friendly text "text" to our schedule array.
*/
function addScheduleButton(value, text)
{
    window.opener.addScheduleArray(value, text);
    window.opener.updateSchedule('');
    window.close();
}


/* addScheduleArray

    Adds the scheduniq "value" and friendly text "text" to the schedule array.
*/
function addScheduleArray(value, text)
{
    scheduleArray.push(value);
    scheduleNameArray.push(text);
}


/* newSection

    Creates a new section and opens up the editing window.
*/
function newSection()
{
    /* FIX ME: constant */
    window.open('report.php?act=12&isparent=' + constIsParentNewSection);
}


/* newSchedule

    Creates a new schedule and opens up the editing window.
*/
function newSchedule()
{
    /* FIX ME: use constActCreateSchedule from sched.php instead */
    window.open('sched.php?act=1&isparent=1');
}


/* updateSchedText

    Generates dynamic text describing the schedule as the user configures it.
*/
function updateSchedText()
{
    /* For now, disable all dynamic help text for schedules */
    return;

    var text = '<b><ul>';
/* FIX ME: use constants */
    if(!(document.getElementById('schedtextdiv')))
    {
        return;
    }
    /* switch statements don't seem to work */
    if(document.getElementById(constSchedControlChkImmed).value==-1)
    {
        /* interval tab is active */
        text += getSchedTextInterval();
    }
    else if(document.getElementById(constSchedControlChkImmed).value==0)
    {
        /* one time tab is active */
        text += getSchedTextOneTime();
    }
    else
    {
        text += '<li>This schedule will run immediately.';
    }

    text += ' ' + getSchedTextAdvanced();

    schedtextdiv.innerHTML = text + '</ul><b>';
}


/* getSchedTextInterval

    Reads control values from the scheduling form and generates text describing
    the interval.
*/
function getSchedTextInterval()
{
    var text = '';
    var monthMask = document.getElementById(constSchedControlMonthMask).value;
    var weekMask = document.getElementById(constSchedControlWeekDayMask).value;
    var hourMask = document.getElementById(constSchedControlHourMask).value;
    var first;

    /* First, handle the simple interval case */
/* FIX ME - use constants */
    if((monthMask==4095) && (weekMask==127) && (hourMask==16777215) &&
        (document.getElementById(constSchedControlStartDay).value==1) &&
        (document.getElementById(constSchedControlDayInt).value==1) &&
        (document.getElementById(constSchedControlStartMin).value==0) &&
        (document.getElementById(constSchedControlIntMin).value==5))
    {
        return '<li>This schedule will run every five minutes.';
    }

    if(monthMask==4095)
    {
        text += '<li>This schedule can run during any month';
    }
    else
    {
        text += '<li>This schedule will run during the months of ';
        first = 1;
        for(i=0,mask=1; i<12; i++,mask<<=1)
        {
            if(monthMask & mask)
            {
                if(!first)
                {
                    text += ', ';
                }
                first = 0;
                switch(i)
                {
                case 0:
                    text += 'January';
                    break;
                case 1:
                    text += 'February';
                    break;
                case 2:
                    text += 'March';
                    break;
                case 3:
                    text += 'April';
                    break;
                case 4:
                    text += 'May';
                    break;
                case 5:
                    text += 'June';
                    break;
                case 6:
                    text += 'July';
                    break;
                case 7:
                    text += 'August';
                    break;
                case 8:
                    text += 'September';
                    break;
                case 9:
                    text += 'October';
                    break;
                case 10:
                    text += 'November';
                    break;
                case 11:
                    text += 'December';
                    break;
                }
            }
        }
    }
    text += '.<li>The schedule may run on ';
    if(weekMask==127)
    {
        text += 'any day of the week';
    }
    else
    {
        first = 1;
        for(i=0,mask=1; i<7; i++,mask<<=1)
        {
            if(weekMask & mask)
            {
                if(!first)
                {
                    text += ', ';
                }
                first = 0;
                switch(i)
                {
                case 0:
                    text += 'Sunday';
                    break;
                case 1:
                    text += 'Monday';
                    break;
                case 2:
                    text += 'Tuesday';
                    break;
                case 3:
                    text += 'Wednesday';
                    break;
                case 4:
                    text += 'Thursday';
                    break;
                case 5:
                    text += 'Friday';
                    break;
                case 6:
                    text += 'Saturday';
                    break;
                }
            }
        }
    }

    text += '.<li>Every month, the schedule starts on day '
        + document.getElementById(constSchedControlStartDay).value
        + ' and repeats every '
        + document.getElementById(constSchedControlDayInt).value
        + ' day(s).<li>Every day, the schedule can run at ';
    if(hourMask==16777215)
    {
        text += 'any hour';
    }
    else
    {
        first = 1;
        for(i=0,mask=1; i<24; i++,mask<<=1)
        {
            if(hourMask & mask)
            {
                if(!first)
                {
                    text += ', ';
                }
                first = 0;
                if(i==0)
                {
                    text += 'Midnight';
                }
                else if(i==12)
                {
                    text += 'Noon';
                }
                else if(i<12)
                {
                    text += i + ' AM';
                }
                else
                {
                    text += (i-12) + ' PM';
                }
            }
        }
    }
    text += '.<li>Every hour, the schedule starts at '
        + document.getElementById(constSchedControlStartMin).value
        + ' past the hour and repeats every '
        + document.getElementById(constSchedControlIntMin).value
        + ' minute(s).';

    return text;
}


/* getSchedTextOneTime

    Gets help text for a one-time schedule.
*/
function getSchedTextOneTime()
{
    return '<li>This schedule will run only once at the time specified.';
}


/* getSchedTextAdvanced

    Returns help text describing the advanced scheduling settings.
*/
function getSchedTextAdvanced()
{
    var text = '';

    if(document.getElementById(constSchedControlNotify).value!=0)
    {
        text += '<li>You will be notified by e-mail when this schedule fails '
            + 'to run '
            + document.getElementById(constSchedControlNotify).value
            + ' time(s).';
    }
    if(document.getElementById(constSchedControlDelay).value!=0)
    {
        text += '<li>This schedule will be delayed an additional '
            + document.getElementById(constSchedControlDelay).value
            + ' day(s).';
    }
    if(document.getElementById(constSchedControlRandMin).value!=0)
    {
        text += '<li>This schedule will be randomly delayed up to '
            + document.getElementById(constSchedControlDelay).value
            + ' minute(s).';
    }
/* FIX ME: this logic is wrong for some reason */
    if(document.getElementById(constSchedControlProxyEnable)==1)
    {
        text += '<li>This schedule can only run within '
            + document.getElementById(constSchedControlProxyMin).value
            + ' minute(s) of the scheduled time.';
    }

    return text;
}


/* editSection

    Opens up a section editing window for the sectionuniq "x".
*/
function editSection(x, isparent)
{
/* FIX ME: use real constant */
    window.open('report.php?act=6&sectionuniq=' + x + '&isparent='
        + isparent);
}


/* editSchedule

    Opens up a schedule editing window for the scheduniq "x".
*/
function editSchedule(x)
{
/* FIX ME: use real constant */
    window.open('sched.php?act=2&scheduniq=' + x + '&isparent=2');
}


/* removeSection

    Removes section index "i" from the internal JavaScript arrays.
*/
function removeSection(i)
{
    sectionArray.splice(i, 1);
    sectionNameArray.splice(i, 1);
    updateSection('');
}


/* removeSchedule

    Removes schedule index "i" from the internal JavaScript arrays.
*/
function removeSchedule(i)
{
    scheduleArray.splice(i, 1);
    scheduleNameArray.splice(i, 1);
    updateSchedule('');
}


/* renameSchedule

    Allows a child window to change the friendly name of the scheduniq "value"
    to "text".
*/
function renameSchedule(value, text)
{
    for(i=0; i<window.opener.scheduleArray.length; i++)
    {
        if(window.opener.scheduleArray[i]==value)
        {
            window.opener.scheduleNameArray[i] = text;
            window.opener.updateSchedule('');
            return;
        }
    }
}


/* renameSection

    Allows a child window to change the friendly name of the sectionuniq
    "value" to "text".
*/
function renameSection(value, text)
{
    for(i=0; i<window.opener.sectionArray.length; i++)
    {
        if(window.opener.sectionArray[i]==value)
        {
            window.opener.sectionNameArray[i] = text;
            window.opener.updateSection('');
            return;
        }
    }
}


/* checkAll

    Checks all checkboxes on the addsect.php page.
*/
function checkAll()
{
    checkThem(true);
}


/* uncheckAll

    Unchecks all checkboxes on the addsect.php page.
*/
function uncheckAll()
{
    checkThem(false);
}


/* checkThem

    Unchecks or checks all checkboxes on the addsect.php page.
*/
function checkThem(checked)
{
    var list = document.getElementsByTagName('input');
    for(i=0; i<list.length; i++)
    {
        if((list[i].type=='checkbox') &&
            (!list[i].disabled))
        {
            list[i].checked = checked;
        }
    }
}


/* addChecks

    Adds all the checked sections on the addsect.php page to the parent section
    control and closes the child window.
*/
function addChecks(type)
{
    var list = document.getElementsByTagName('input');
    for(i=0; i<list.length; i++)
    {
        if((list[i].type=='checkbox') &&
            (!list[i].disabled) && (list[i].checked))
        {
            for(j=0; j<addUniq.length; j++)
            {
                if(list[i].name==addUniq[j])
                {
                    switch(type)
                    {
                    case constAddCheckTypeSection:
                        addSectionButton(addUniq[j], addName[j]);
                        break;
                    case constAddCheckTypeSchedule:
                        addScheduleButton(addUniq[j], addName[j]);
                        break;
                    case constAddCheckTypeEventFilters:
                        addDynamicItemButton(constJavaListEventFilters,
                            addUniq[j], addName[j]);
                        break;
                    case constAddCheckTypeEventIncludeMgrp:
                        addDynamicItemButton(constJavaListEventMgrpInclude,
                            addUniq[j], addName[j]);
                        break;
                    case constAddCheckTypeEventExcludeMgrp:
                        addDynamicItemButton(constJavaListEventMgrpExclude,
                            addUniq[j], addName[j]);
                        break;
                    case constAddCheckTypeAssetQueries:
                        addDynamicItemButton(constJavaListAssetQueries,
                            addUniq[j], addName[j]);
                        break;
                    }
                }
            }
        }
    }
    window.close();
}


/* addSpecialDate

    Adds information about a single special date control to the internal
    javascript arrays.  Pass in the name of the div for start/end dates
    "divname", the reportconfiguniq of the Scheduler button "sched", the
    reportconfiguniq of the Restrict button "rest", the reportconfiguniq of
    the start date control "start", and the reportconfiguniq of the end
    date control "end".  Pass in the current values for start/end dates into
    "startVal" and "endVal", respectively.
*/
function addSpecialDate(divname, sched, rest, start, startVal, end, endVal,
    schedValue)
{
    dateDivArray.push(divname);
    dateSchedArray.push(sched);
    dateRestArray.push(rest);
    dateStartArray.push(start);
    dateStartValArray.push(startVal);
    dateEndArray.push(end);
    dateEndValArray.push(endVal);
    dateSchedValueArray.push(schedValue);
}


/* showDate

    Dynamically displays or hides the start/end date for the division
    "divname".
*/
function showDate(divname)
{
    for(i=0; i<dateDivArray.length; i++)
    {
        if(dateDivArray[i]==divname)
        {
            if(document.getElementById(dateSchedArray[i]).checked)
            {
                document.getElementById(divname).innerHTML =
                    '<input type="hidden" name="' + dateStartArray[i]
                    + '" id="' + dateStartArray[i] + '" value="'
                    + '"><input type="hidden" name="' + dateEndArray[i]
                    + '" id="' + dateEndArray[i] + '" value="'
                    + '">';
            }
            else
            {
                document.getElementById(divname).innerHTML =
                    '<table border="0" cellspacing="0"><tr><td>'
                    + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Start&nbsp;Date:'
                    + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                    + '<input type="text" name="' + dateStartArray[i]
                    + '" id="' + dateStartArray[i] + '" value="'
                    + dateStartValArray[i] + '"></td><td>'
                    + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                    + 'End&nbsp;Date:&nbsp;&nbsp;&nbsp;'
                    + '&nbsp;&nbsp;<input type="text" name="' + dateEndArray[i]
                    + '" id="' + dateEndArray[i] + '" value="'
                    + dateEndValArray[i] + '"></td></tr><tr><td>'
                    + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                    + '<font size="-2">' + constDateHelpText
                    + '</font></td><td>'
                    + '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font size="-2">'
                    + constDateHelpText + '</font></td></tr></table>';
            }
            return;
        }
    }
}


/* setrestrict

    Handles the restrict checkbox "ctrname" changing value.
*/
function setrestrict(ctrname)
{
    var disableControls = false;
    if(document.getElementById(ctrname).checked)
    {
        disableControls = true;
    }
    for(i=0; i<dateRestArray.length; i++)
    {
        if(dateRestArray[i]!=ctrname)
        {
            document.getElementById(dateSchedArray[i]).disabled =
                disableControls;
            document.getElementById(dateRestArray[i]).disabled =
                disableControls;
            document.getElementById(dateStartArray[i]).disabled =
                disableControls;
            document.getElementById(dateStartArray[i]).value = '';
            dateStartValArray[i] = '';
            document.getElementById(dateEndArray[i]).disabled =
                disableControls;
            document.getElementById(dateEndArray[i]).value = '';
            dateEndValArray[i] = '';
            if(disableControls)
            {
                /* Uncheck the "Scheduler" checkbox if we need to */
                if(document.getElementById(dateSchedArray[i]).checked)
                {
                    document.getElementById(dateSchedArray[i]).checked = false;
                    showDate(dateDivArray[i]);
                    document.getElementById(dateSchedValueArray[i]).value = 0;
                }
            }
        }
        else
        {
            document.getElementById(dateSchedArray[i]).disabled = false;
            document.getElementById(dateRestArray[i]).disabled = false;
            document.getElementById(dateStartArray[i]).disabled = false;
            document.getElementById(dateEndArray[i]).disabled = false;
        }
    }
}


/* setAllMonths

    Handles the "x" all button for months.
*/
function setAllMonths(x)
{
    if(document.getElementById(x).className=='regbuttonsmend')
    {
        document.getElementById(constSchedControlMonthMask).value
            = constAllMonths;
        document.getElementById(x).className = 'selbuttonsm';
        document.getElementById(x).value = constNoneLabel;
        document.getElementById('Jan').className = 'selbuttonsm';
        document.getElementById('Feb').className = 'selbuttonsm';
        document.getElementById('Mar').className = 'selbuttonsm';
        document.getElementById('Apr').className = 'selbuttonsm';
        document.getElementById('May').className = 'selbuttonsm';
        document.getElementById('Jun').className = 'selbuttonsm';
        document.getElementById('Jul').className = 'selbuttonsm';
        document.getElementById('Aug').className = 'selbuttonsm';
        document.getElementById('Sep').className = 'selbuttonsm';
        document.getElementById('Oct').className = 'selbuttonsm';
        document.getElementById('Nov').className = 'selbuttonsm';
        document.getElementById('Dec').className = 'selbuttonsm';
    }
    else
    {
        document.getElementById(constSchedControlMonthMask).value = 0;
        document.getElementById(x).className = 'regbuttonsmend';
        document.getElementById(x).value = constAllLabel;
        document.getElementById('Jan').className = 'regbuttonsm';
        document.getElementById('Feb').className = 'regbuttonsm';
        document.getElementById('Mar').className = 'regbuttonsm';
        document.getElementById('Apr').className = 'regbuttonsm';
        document.getElementById('May').className = 'regbuttonsm';
        document.getElementById('Jun').className = 'regbuttonsm';
        document.getElementById('Jul').className = 'regbuttonsm';
        document.getElementById('Aug').className = 'regbuttonsm';
        document.getElementById('Sep').className = 'regbuttonsm';
        document.getElementById('Oct').className = 'regbuttonsm';
        document.getElementById('Nov').className = 'regbuttonsm';
        document.getElementById('Dec').className = 'regbuttonsmend';
    }
}


/* setAllHours

    Handles the "x" all button for hours.
*/
function setAllHours(x)
{
    if(document.getElementById(x).className=='regbuttonsmend')
    {
        document.getElementById(constSchedControlHourMask).value
            = constAllHours;
        document.getElementById(x).className = 'selbuttonsm';
        document.getElementById(x).value = constNoneLabel;
        document.getElementById('AM_mid').className = 'selbuttonsm';
        document.getElementById('AM_1').className = 'selbuttonsm';
        document.getElementById('AM_2').className = 'selbuttonsm';
        document.getElementById('AM_3').className = 'selbuttonsm';
        document.getElementById('AM_4').className = 'selbuttonsm';
        document.getElementById('AM_5').className = 'selbuttonsm';
        document.getElementById('AM_6').className = 'selbuttonsm';
        document.getElementById('AM_7').className = 'selbuttonsm';
        document.getElementById('AM_8').className = 'selbuttonsm';
        document.getElementById('AM_9').className = 'selbuttonsm';
        document.getElementById('AM_10').className = 'selbuttonsm';
        document.getElementById('AM_11').className = 'selbuttonsm';
        document.getElementById('PM_noon').className = 'selbuttonsm';
        document.getElementById('PM_1').className = 'selbuttonsm';
        document.getElementById('PM_2').className = 'selbuttonsm';
        document.getElementById('PM_3').className = 'selbuttonsm';
        document.getElementById('PM_4').className = 'selbuttonsm';
        document.getElementById('PM_5').className = 'selbuttonsm';
        document.getElementById('PM_6').className = 'selbuttonsm';
        document.getElementById('PM_7').className = 'selbuttonsm';
        document.getElementById('PM_8').className = 'selbuttonsm';
        document.getElementById('PM_9').className = 'selbuttonsm';
        document.getElementById('PM_10').className = 'selbuttonsm';
        document.getElementById('PM_11').className = 'selbuttonsm';
    }
    else
    {
        document.getElementById(constSchedControlHourMask).value = 0;
        document.getElementById(x).className = 'regbuttonsmend';
        document.getElementById(x).value = constAllLabel;
        document.getElementById('AM_mid').className = 'regbuttonsm';
        document.getElementById('AM_1').className = 'regbuttonsm';
        document.getElementById('AM_2').className = 'regbuttonsm';
        document.getElementById('AM_3').className = 'regbuttonsm';
        document.getElementById('AM_4').className = 'regbuttonsm';
        document.getElementById('AM_5').className = 'regbuttonsm';
        document.getElementById('AM_6').className = 'regbuttonsm';
        document.getElementById('AM_7').className = 'regbuttonsm';
        document.getElementById('AM_8').className = 'regbuttonsm';
        document.getElementById('AM_9').className = 'regbuttonsm';
        document.getElementById('AM_10').className = 'regbuttonsm';
        document.getElementById('AM_11').className = 'regbuttonsmend';
        document.getElementById('PM_noon').className = 'regbuttonsm';
        document.getElementById('PM_1').className = 'regbuttonsm';
        document.getElementById('PM_2').className = 'regbuttonsm';
        document.getElementById('PM_3').className = 'regbuttonsm';
        document.getElementById('PM_4').className = 'regbuttonsm';
        document.getElementById('PM_5').className = 'regbuttonsm';
        document.getElementById('PM_6').className = 'regbuttonsm';
        document.getElementById('PM_7').className = 'regbuttonsm';
        document.getElementById('PM_8').className = 'regbuttonsm';
        document.getElementById('PM_9').className = 'regbuttonsm';
        document.getElementById('PM_10').className = 'regbuttonsm';
        document.getElementById('PM_11').className = 'regbuttonsmend';
    }
}


/* setAllWeeks

    Handles the "x" all button for weeks.
*/
function setAllWeeks(x)
{
    if(document.getElementById(x).className=='regbuttonsmend')
    {
        document.getElementById(constSchedControlWeekDayMask).value
            = constAllWeekDays;
        document.getElementById(x).className = 'selbuttonsm';
        document.getElementById(x).value = constNoneLabel;
        document.getElementById('Mon').className = 'selbuttonsm';
        document.getElementById('Tue').className = 'selbuttonsm';
        document.getElementById('Wed').className = 'selbuttonsm';
        document.getElementById('Thu').className = 'selbuttonsm';
        document.getElementById('Fri').className = 'selbuttonsm';
        document.getElementById('Sat').className = 'selbuttonsm';
        document.getElementById('Sun').className = 'selbuttonsm';
    }
    else
    {
        document.getElementById(constSchedControlWeekDayMask).value = 0;
        document.getElementById(x).className = 'regbuttonsmend';
        document.getElementById(x).value = constAllLabel;
        document.getElementById('Mon').className = 'regbuttonsm';
        document.getElementById('Tue').className = 'regbuttonsm';
        document.getElementById('Wed').className = 'regbuttonsm';
        document.getElementById('Thu').className = 'regbuttonsm';
        document.getElementById('Fri').className = 'regbuttonsm';
        document.getElementById('Sat').className = 'regbuttonsmend';
        document.getElementById('Sun').className = 'regbuttonsm';
    }
}


/* setstatecontrol

    Stores the name of the state SELECT control "x".
*/
function setstatecontrol(x)
{
    stateControl = x;
}


/* statechanged

    SELECT event handler for the state dropdown.
*/
function statechanged(x)
{
    var ctrlName = constSchedControlChkImmed + constActCheckboxAppend;
    if((document.getElementById(ctrlName)) &&
        (document.getElementById(ctrlName).checked))
    {
        for(var i=0; i<document.getElementById(stateControl).length; i++)
        {
            if(document.getElementById(stateControl).options[i].value
                ==constReportEnabled)
            {
                document.getElementById(stateControl).options[i].selected
                    = true;
            }
            else
            {
                document.getElementById(stateControl).options[i].selected
                    = false;
            }
        }
    }
}

/* viewSection

    Opens up a section editing window for the sectionuniq "x".
*/
function viewSection(x)
{
/* FIX ME: use real constant */
    window.open('report.php?act=22&sectionuniq=' + x);
}

/* viewSchedule

    Opens up a schedule viewing window for the scheduniq "x".
*/
function viewSchedule(x)
{
/* FIX ME: use real constant */
    window.open('sched.php?act=7&scheduniq=' + x);
}

/* makeViewOnly

    Disables all input and select options on the page, except View...,
    Help, and How to.
*/
function makeViewOnly()
{
    var list = document.getElementsByTagName('input');
    for(i=0; i<list.length; i++)
    {
        /* Do not disable the view or help buttons - FIX ME: these strings
            should be defined constants */
        if((list[i].value!='View...') && (list[i].value!='Help')
            && (list[i].value!='How to'))
        {
            list[i].disabled = true;
        }
    }
    list = document.getElementsByTagName('select');
    for(i=0; i<list.length; i++)
    {
        list[i].disabled = true;
    }
    list = document.getElementsByTagName('textarea');
    for(i=0; i<list.length; i++)
    {
        list[i].disabled = true;
    }
}


/* updateDynamicList

    Updates the div "divid" with HTML for the dynamic list "list" for the
    unique control "reportconfiguniq".
*/
function updateDynamicList(list, divid, reportconfiguniq)
{
    if(reportconfiguniq!='')
    {
        dynamicUniqs[list] = reportconfiguniq;
    }
    if(divid!='')
    {
        dynamicDivs[list] = divid;
    }

    var str = '<table border="0"><tr><td>';

    if(dynamicNames[list].length==0)
    {
        switch(list)
        {
        case constJavaListEventFilters:
            str += 'No event query filters have been added yet.<br><br>';
            break;
        case constJavaListEventMgrpInclude:
            str += 'No machine groups have been selected for inclusion.<br>'
                + '<br>';
            break;
        case constJavaListEventMgrpExclude:
            str += 'No machine groups have been selected for exclusion.<br>'
                + '<br>';
            break;
        case constJavaListAssetQueries:
            str += 'No asset queries have been added yet.<br><br>';
            updateSummaryAssetGroups();
            break;
        default:
            str += 'No items have been added yet.<br><br>';
            break;
        }
    }
    else
    {
        str += '<div style="height:150px;width:400px;overflow:auto;">'
            + '<table class="mytable" cellpadding="3" cellspacing="0" style="'
            + 'width:380px"><tr><th class="mycol" bgcolor="#333399"><font color="'
            + 'white"> ';
        switch(list)
        {
        case constJavaListEventFilters:
            str += 'Event Filter Name';
            break;
        case constJavaListEventMgrpInclude:
        case constJavaListEventMgrpExclude:
            str += 'Machine Group Name';
            break;
        case constJavaListAssetQueries:
            str += 'Asset Query Name';
            updateSummaryAssetGroups();
            break;
        default:
            str += 'Item Name';
            break;
        }
        str += '</font></th><th class="mycol" bgcolor="'
            + '#333399"><font color="white">';
        if(controlDisposition==constJavaListDispositionEdit)
        {
            str += 'Edit</font></th><th class="mycol"'
                + ' bgcolor="#333399"><font color="white">Remove</font></th>';
        }
        else
        {
            str += 'View</font></th>';
        }
        str += '</tr>';

        for(i=0; i<dynamicNames[list].length; i++)
        {
            str += '<tr><td class="mycol">' + dynamicNames[list].slice(i, i+1)
                + '</td><td class="mycol" align="center"><input type="button"'
                + ' value="';
            if(controlDisposition==constJavaListDispositionEdit)
            {
                str += 'Edit..." onclick="editDynamicItem(' + list + ',\''
                    + dynamicLists[list].slice(i, i+1) + '\');"></td><td class='
                    + '"mycol" align="center"><input type="button" value="Remove"'
                    + ' onclick="removeDynamicItem(' + list + ',' + i
                    + ');"></td></tr>';
            }
            else
            {
                str += 'View..." onclick="viewDynamicItem(' + list + ',\''
                    + dynamicLists[list].slice(i, i+1) + '\');"></td></tr>';
            }
        }
        str += '</table></div>';
    }

    str += '</td>';
    if(controlDisposition==constJavaListDispositionEdit)
    {
        str += '<td>'
        + '<input type="button" value="Add New..." onclick="newDynamicItem('
        + list + ');">'
        + '<br><br>'
        + '<input type="button" value="Add Existing..." onclick='
        + '"addDynamicItem(' + list + ');"></td>';
    }

    str += '</tr></table>';

    /* Update the hidden variable with the current values */
    value = '';
    first = true;
    for(i=0; i<dynamicLists[list].length; i++)
    {
        if(!first)
        {
            value += ', ';
        }
        value += dynamicLists[list].slice(i, i+1);
        first = false;
    }

    document.getElementById(constRepfFormPrefix + dynamicUniqs[list]).value
        = value;

    document.getElementById(dynamicDivs[list]).innerHTML = str;
}


/* addArrayDynamicItem

*/
function addArrayDynamicItem(list, value, text)
{
    dynamicLists[list].push(value);
    dynamicNames[list].push(text);
}


/* updateAddItemButtons

*/
function updateAddItemButtons(list)
{
    for(i=0; i<window.opener.dynamicLists[list].length; i++)
    {
        if(document.getElementById(window.opener.dynamicLists[list].slice(i,
            i+1)))
        {
            document.getElementById(window.opener.dynamicLists[list].slice(i,
                i+1)).disabled = true;
            document.getElementById(window.opener.dynamicLists[list].slice(i,
                i+1)).checked = true;
        }
    }
}


/* removeDynamicItem

*/
function removeDynamicItem(list, i)
{
    dynamicLists[list].splice(i, 1);
    dynamicNames[list].splice(i, 1);
    updateDynamicList(list, '', '');
}


/* renameDynamicItem

*/
function renameDynamicItem(list, value, text)
{
    for(i=0; i<window.opener.dynamicLists[list].length; i++)
    {
        if(window.opener.dynamicLists[list][i]==value)
        {
            window.opener.dynamicNames[list][i] = text;
            window.opener.updateDynamicList(list, '', '');
            return;
        }
    }
}


function editDynamicItem(list, x)
{
    switch(list)
    {
    case constJavaListEventFilters:
        window.open('../event/srch-act.php?uniq=' + x
            + '&action=edit&isparent=1');
        break;
    case constJavaListEventMgrpInclude:
    case constJavaListEventMgrpExclude:
        window.open('../config/groups.php?act=wmth&scop=5&dtc=-1&int=-1'
            + '&custom=3&notification_id=0&notification_act=&report_id=0'
            + '&report_act=&asset_id=0&asset_act=&mgroupuniq=' + x);
        break;
    case constJavaListAssetQueries:
        window.open('../asset/qury-act.php?asrchuniq=' + x + '&action=edit');
        break;
    }
}


function viewDynamicItem(list, x)
{
    switch(list)
    {
    case constJavaListEventFilters:
        window.open('../event/search.php?uniq=' + x + '&act=view');
        break;
    case constJavaListEventMgrpInclude:
    case constJavaListEventMgrpExclude:
        window.open('../config/groups.php?sub=wmth&act=gdet&custom=3&'
            + 'notfication_id=0&notfication_act=&mgroupuniq=' + x);
        break;
    case constJavaListAssetQueries:
        window.open('../asset/query.php?act=view&asrchuniq=' + x);
        break;
    }
}


function addDynamicItemButton(list, value, text)
{
    window.opener.addArrayDynamicItem(list, value, text);
    window.opener.updateDynamicList(list, '', '');
}

function newDynamicItem(list)
{
    switch(list)
    {
    case constJavaListEventFilters:
        window.open('../event/srch-add.php?isparent=1');
        break;
    case constJavaListEventMgrpInclude:
    case constJavaListEventMgrpExclude:
        window.open('../config/groups.php?act=wmth&frm=wmth&ctl=0&pcn=wiz'
            + '&tid=-1&gid=-1&scop=5&custom=3&notification_id=0&'
            + 'notification_act=&report_id=0&report_act=&asset_id=0&asset_act='
            + '&int=-1&dtc=-1&button=Add+new+group&isparent=' + list);
        break;
    case constJavaListAssetQueries:
        window.open('../asset/qury-add.php?isparent=' + list);
        break;
    default:
        break;


    }
}

function addDynamicItem(list)
{
    window.open('additem.php?listid=' + list);
}

function setcheckbox(list, value, single)
{
    var listArray = list.split(',');
    var setvalue = 0;
    if(single=='')
    {
        setvalue = value;
    }
    else
    {
        setvalue = document.getElementById(constRepfFormPrefix + single).value;
    }

    for(i=0; i<listArray.length; i++)
    {
        if(listArray[i]!='')
        {
            document.getElementById(constRepfFormPrefix
                + listArray[i]).value = setvalue;
            document.getElementById(constRepfFormPrefix + listArray[i]
                + constActCheckboxAppend).checked = setvalue==0 ? false : true;
        }
    }
}


function updateSummaryDiv(reportconfiguniq)
{
    var divtext = '<table border="0"><tr><td>';

    if(summaryDivUniq=='')
    {
        summaryDivUniq = reportconfiguniq;
    }

    if(summaryNames.length==0)
    {
        divtext += 'No content has been added yet.';
    }
    else
    {
        /* Header */
        divtext += '<table class="mytable" cellpadding="3" cellspacing="0">'
            + '<tr><th class="mycol" bgcolor="#333399"><font color="white">'
            + 'Item Name</th>'
            + '<th class="mycol" bgcolor="#333399"><font color="white">'
            + 'Item Details</th>'
            + '<th class="mycol" bgcolor="#333399"><font color="white">'
            + 'Edit</th>'
            + '<th class="mycol" bgcolor="#333399"><font color="white">'
            + 'Total</th>'
            + '<th class="mycol" bgcolor="#333399"><font color="white">'
            + '%</th>'
            + '<th class="mycol" bgcolor="#333399"><font color="white">'
            + 'Avg</th>'
            + '<th class="mycol" bgcolor="#333399"><font color="white">'
            + 'Min</th>'
            + '<th class="mycol" bgcolor="#333399"><font color="white">'
            + 'Max</th>'
            + '<th class="mycol" bgcolor="#333399"><font color="white">'
            + 'Move</th>'
            + '<th class="mycol" bgcolor="#333399"><font color="white">'
            + 'Delete</th></tr>';

        for(i=0; i<summaryNames.length; i++)
        {
            divtext += '<tr>';
            /* Item name */
            divtext += '<td class="mycol">' + summaryNames[i] + '<input '
                + 'type="hidden" name="' + constRepfFormPrefix + summaryDivUniq
                + '_' + i + '_'
                + constReptCtrSummaryName + '" value="' + summaryNames[i]
                + '"><input type="hidden" name="' + constRepfFormPrefix
                + summaryDivUniq + '_' + i
                + '_' + constReptCtrSummaryUniq + '" value="' + summaryUniqs[i]
                + '"><input type="hidden" name="' + constRepfFormPrefix
                + summaryDivUniq + '_' + i
                + '_' + constReptCtrSummaryOrder + '" value="' + (i+1)
                + '"></td>';
            /* Item details */
            divtext += '<td class="mycol">';
            switch(summaryTypes[i])
            {
            case constSummaryItemAsset:
                divtext += 'Asset Queries:<ul>';
                break;
            case constSummaryItemEvent:
                divtext += 'Event Filters:<ul>';
                break;
            case constSummaryItemMUM:
                divtext += 'MUM Filter: ';
                for(j=0; j<mumSectionsArray.length; j++)
                {
                    if(mumSectionsArray[j][1]
                        ==summaryTypeData[i][constDynamicListMUMFilter])
                    {
                        divtext += mumSectionsArray[j][0];
                        break;
                    }
                }
                break;
            }
            asrchuniqs = '';
            for(j=0; j<summaryTypeData[i][constDynamicListNames].length;
                j++)
            {
                switch(summaryTypes[i])
                {
                case constSummaryItemAsset:
                    divtext += '<li><a target="blank" href='
                        + '"../asset/query.php?act=view&asrchuniq=';
                    break;
                case constSummaryItemEvent:
                    divtext += '<li><a target="blank" href='
                        + '"../event/search.php?act=view&uniq=';
                    break;
                }
                divtext += summaryTypeData[i][constDynamicListUniqs][j] + '">'
                    + summaryTypeData[i][constDynamicListNames][j]
                    + '</a>';
                asrchuniqs += ','
                    + summaryTypeData[i][constDynamicListUniqs][j];
            }
            switch(summaryTypes[i])
            {
            case constSummaryItemAsset:
            case constSummaryItemEvent:
                divtext += '</ul>';
                break;
            case constSummaryItemMUM:
                break;
            }
            divtext += '<input type="hidden" name="' + constRepfFormPrefix
                + summaryDivUniq
                + '_' + i + '_' + constReptCtrSummaryType + '" value="'
                + summaryTypes[i] + '"><input type="hidden" name="'
                + constRepfFormPrefix + summaryDivUniq + '_' + i + '_'
                + constReptCtrSummaryData + '" value="' + asrchuniqs
                + '">'
                + addHiddenSummaryInput(i, constReptCtrSummaryGroupOne,
                    constDynamicListGroupOne)
                + addHiddenSummaryInput(i, constReptCtrSummaryGroupOneRaw,
                    constDynamicListGroupOneRaw)
                + addHiddenSummaryInput(i, constReptCtrSummaryGroupOneDec,
                    constDynamicListGroupOneDec)
                + addHiddenSummaryInput(i, constReptCtrSummaryGroupTwo,
                    constDynamicListGroupTwo)
                + addHiddenSummaryInput(i, constReptCtrSummaryGroupTwoRaw,
                    constDynamicListGroupTwoRaw)
                + addHiddenSummaryInput(i, constReptCtrSummaryGroupTwoDec,
                    constDynamicListGroupTwoDec)
                + addHiddenSummaryInput(i, constReptCtrSummaryDataOne,
                    constDynamicListDataOne)
                + addHiddenSummaryInput(i, constReptCtrSummaryDataOneRaw,
                    constDynamicListDataOneRaw)
                + addHiddenSummaryInput(i, constReptCtrSummaryDataOneDec,
                    constDynamicListDataOneDec)
                + addHiddenSummaryInput(i, constReptCtrSummaryExcFake,
                    constDynamicListExcFake);
            switch(summaryTypes[i])
            {
            case constSummaryItemAsset:
                divtext += addHiddenSummaryInput(i,
                    constReptCtrSummaryAssetSetting,
                    constDynamicListAssetSetting);
                break;
            case constSummaryItemEvent:
                divtext += addHiddenSummaryInput(i,
                        constReptCtrSummaryEventSetting,
                        constDynamicListEventSetting)
                    + addHiddenSummaryInput(i,
                        constReptCtrSummaryEventLimitTop,
                        constDynamicListEventLimitTop)
                    + addHiddenSummaryInput(i,
                        constReptCtrSummaryEventLimitNum,
                        constDynamicListEventLimitNum)
                    + addHiddenSummaryInput(i,
                        constReptCtrSummaryEventLimitPer,
                        constDynamicListEventLimitPer)
                    + addHiddenSummaryInput(i,
                        constReptCtrSummaryEventLimitPerNum,
                        constDynamicListEventLimitPerNum);
                break;
            case constSummaryItemMUM:
                divtext += addHiddenSummaryInput(i,
                    constReptCtrSummaryMUMFilter,
                    constDynamicListMUMFilter)
                    + addHiddenSummaryInput(i,
                        constReptCtrSummaryEventLimitTop,
                        constDynamicListEventLimitTop)
                    + addHiddenSummaryInput(i,
                        constReptCtrSummaryEventLimitNum,
                        constDynamicListEventLimitNum)
                    + addHiddenSummaryInput(i,
                        constReptCtrSummaryMUMLimitCount,
                        constDynamicListMUMLimitCount)
                    + addHiddenSummaryInput(i,
                        constReptCtrSummaryMUMLimitSel,
                        constDynamicListMUMLimitSel)
                    + addHiddenSummaryInput(i,
                        constReptCtrSummaryMUMLimitNum,
                        constDynamicListMUMLimitNum)
                    + addHiddenSummaryInput(i,
                        constReptCtrSummaryEventLimitPer,
                        constDynamicListEventLimitPer)
                    + addHiddenSummaryInput(i,
                        constReptCtrSummaryEventLimitPerNum,
                        constDynamicListEventLimitPerNum)
                    + addHiddenSummaryInput(i,
                        constReptCtrSummaryMUMLimitPerSel,
                        constDynamicListMUMLimitPerSel);
                break;
            }
            divtext += '</td>';

            /* Edit */
            divtext += '<td class="mycol"><input type="button" value="';
            if(controlDisposition==constJavaListDispositionEdit)
            {
                divtext += 'Edit...';
            }
            else
            {
                divtext += 'View...';
            }
            divtext += '" onclick="editSummaryContent(' + i + ');"></td>';

            /* Total */
            divtext += '<td class="mycol">' + buildCustomCheckBox(
                constRepfFormPrefix +
                summaryDivUniq + '_' + i + '_' + constReptCtrSummaryDispTotal,
                summaryTypeData[i][constDynamicListDispTotal]) + '</td>';

            /* % */
            divtext += '<td class="mycol">' + buildCustomCheckBox(
                constRepfFormPrefix +
                summaryDivUniq + '_' + i + '_' + constReptCtrSummaryDispPer,
                summaryTypeData[i][constDynamicListDispPer]) + '</td>';

            /* Avg */
            divtext += '<td class="mycol">' + buildCustomCheckBox(
                constRepfFormPrefix +
                summaryDivUniq + '_' + i + '_' + constReptCtrSummaryDispAvg,
                summaryTypeData[i][constDynamicListDispAvg]) + '</td>';

            /* Min */
            divtext += '<td class="mycol">' + buildCustomCheckBox(
                constRepfFormPrefix +
                summaryDivUniq + '_' + i + '_' + constReptCtrSummaryDispMin,
                summaryTypeData[i][constDynamicListDispMin]) + '</td>';

            /* Max */
            divtext += '<td class="mycol">' + buildCustomCheckBox(
                constRepfFormPrefix +
                summaryDivUniq + '_' + i + '_' + constReptCtrSummaryDispMax,
                summaryTypeData[i][constDynamicListDispMax]) + '</td>';

            /* Move */
            divtext += getMoveHtml(i, 'shiftSumComp', summaryNames.length);

            /* Delete */
            divtext += '<td class="mycol"><input type="button" value="Delete"'
                + ' onclick="delSummaryContent(' + i + ');"></td>';

            divtext += '</tr>';
        }

        divtext += '</table>';
    }

    divtext += '</td><td><input type="button" value="Add New..." '
        + 'onclick="newSummaryContent();"><br>Click on the "Add New..."'
        + ' button to define summary line items for this section.'
        + '</td></tr></table>';

    document.getElementById(summaryDivUniq).innerHTML = divtext;

    updateSummaryControls();
}

function newSummaryContent()
{
    window.open('addsum.php?act=new');
}

function handleSummaryTabs(area)
{
    var areatxt = '<table border="0" class="mytable" cellpadding="3" '
        + 'cellspacing="0"><tr><td class="mycol">';

    document.getElementById('assetq').className = (area=='assetq') ?
        'selbutton' : 'regbutton';
    document.getElementById('tdassetq').className = (area=='assetq') ?
        'tdsel' : 'tdnosel';
    document.getElementById('eventf').className = (area=='eventf') ?
        'selbutton' : 'regbutton';
    document.getElementById('tdeventf').className = (area=='eventf') ?
        'tdsel' : 'tdnosel';
    document.getElementById('mumf').className = (area=='mumf') ?
        'selbutton' : 'regbutton';
    document.getElementById('tdmumf').className = (area=='mumf') ?
        'tdsel' : 'tdnosel';

    if(area=='assetq')
    {
        areatxt += '<table border="0"><tr><td>Asset queries:</td><td><div id="'
            + 'assetquerydiv"></div></td></tr></table><p>'
            + constAssetQueryGroupHelp + '<p><input type="radio" id="'
            + constAssetQuerySetting + '" name="' + constAssetQuerySetting
            + '" value="' + constAssetQueryGroupOnlyVal + '" checked>&nbsp;'
            + constAssetQueryGroupSetHelp + '<br><table><tr><td>&nbsp;&nbsp;'
            + '&nbsp;&nbsp;</td><td>'
            + getGroupDataDec('Group by first:', constAssetSummaryGroupOne,
            constAssetSummaryGroupOneRaw, constAssetSummaryGroupOneDec) + '<p>'
            + getGroupDataDec('Group by second:', constAssetSummaryGroupTwo,
            constAssetSummaryGroupTwoRaw, constAssetSummaryGroupTwoDec)
            + '</td></tr>'
            + '</table><p>'
            + '<input type="radio" id="' + constAssetQuerySetting + '" name="'
            + constAssetQuerySetting + '" value="' + constAssetQueryDataOnlyVal
            + '">&nbsp;' + constAssetQueryDataSetHelp + '<br><table><tr><td>'
            + '&nbsp;&nbsp;&nbsp;&nbsp;</td><td>'
            + getGroupDataDec('Data field:', constAssetSummaryDataOne,
            constAssetSummaryDataOneRaw, constAssetSummaryDataOneDec)
            + '</td></tr>'
            + '</table><p>'
            + buildCustomCheckBox(constAssetSummaryExcFake, 0)
            + ' Exclude machines that do not have applicable asset '
            + 'information.<input type="hidden" id="' + constSummaryLineType
            + '" name="' + constSummaryLineType + '" value="'
            + constSummaryItemAsset + '">';
    }
    else if(area=='eventf')
    {
        areatxt += constEventFilterFilterHelp 
            + '<table border="0"><tr><td>Event filters:</td><td>'
            + '<div id="'
            + 'eventfilterdiv"></div></td></tr></table><p>'
            + '<input type="radio" id="'
            + constEventFilterSetting + '" name="' + constEventFilterSetting
            + '" value="' + constEventFilterClauseValOr + '" checked>&nbsp;'
            + constEventFilterClauseOrHelp + '<br><input type="radio" id="'
            + constEventFilterSetting + '" name="' + constEventFilterSetting
            + '" value="' + constEventFilterClauseValAnd + '">&nbsp;'
            + constEventFilterClauseAndHelp + '<hr>'
            + buildCustomCheckBox(constEventFilterLimitPer, 0)
            + '&nbsp;Only include machines where the number of events '
            + 'retrieved is greater than or equal to <input type="text" id="'
            + constEventFilterLimitPerNum + '" name="'
            + constEventFilterLimitPerNum + '" size="2">% of the total '
            + 'possible events for the specified filter(s) (e.g. machines '
            + 'where events reporting processor utilization greater than or '
            + 'equal to 75% is equal to or greater than 25% of the total '
            + 'number of possible processor utilization events)'
            + constEventFilterMathHelp + '<br>'
            + getGroupDataDec('Data field:', constAssetSummaryDataOne,
            constAssetSummaryDataOneRaw, constAssetSummaryDataOneDec) + '<p>'
            + '<input type="hidden" id="' + constSummaryLineType
            + '" name="' + constSummaryLineType + '" value="'
            + constSummaryItemEvent + '"><p>'
            + constEventFilterGroupHelp + '<p>'
            + getGroupDataDec('Group by first:', constAssetSummaryGroupOne,
            constAssetSummaryGroupOneRaw, constAssetSummaryGroupOneDec) + '<p>'
            + getGroupDataDec('Group by second:', constAssetSummaryGroupTwo,
            constAssetSummaryGroupTwoRaw, constAssetSummaryGroupTwoDec)
            + buildCustomCheckBox(constEventFilterLimitTop, 0)
            + '&nbsp;Limit the output to the top&nbsp;<input type="text" id="'
            + constEventFilterLimitNum + '" name="' + constEventFilterLimitNum
            + '" size="2">&nbsp;entries.  In order to use this configuration '
            + 'option, you need to select grouping parameters.';
    }
    else if(area=='mumf')
    {
        areatxt += constMUMFilterHelp + '<br><select id="'
            + constMUMFilterSelect + '" name="' + constMUMFilterSelect
            + '"></select>&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" '
            + ' onclick="editSection(document.getElementById(\''
            + constMUMFilterSelect + '\').value,' + constIsParentEditMUMFilter
            + ');" '
            + 'value="Edit...">&nbsp;&nbsp;'
            + '&nbsp;&nbsp;<input type="button" onclick="addMUMSection();" '
            + 'value="Add New..."><p><hr>' + constMUMFilterMathHelp + '<br>'
            + getGroupDataDec('Data field:', constAssetSummaryDataOne,
            constAssetSummaryDataOneRaw, constAssetSummaryDataOneDec) + '<p>'
            + '<input type="hidden" id="' + constSummaryLineType
            + '" name="' + constSummaryLineType + '" value="'
            + constSummaryItemMUM + '"><p>'
            + constEventFilterGroupHelp + '<p>'
            + getGroupDataDec('Group by first:', constAssetSummaryGroupOne,
            constAssetSummaryGroupOneRaw, constAssetSummaryGroupOneDec) + '<p>'
            + getGroupDataDec('Group by second:', constAssetSummaryGroupTwo,
            constAssetSummaryGroupTwoRaw, constAssetSummaryGroupTwoDec)
            + buildCustomCheckBox(constEventFilterLimitTop, 0)
            + '&nbsp;Limit the output to the top&nbsp;<input type="text" id="'
            + constEventFilterLimitNum + '" name="' + constEventFilterLimitNum
            + '" size="2">&nbsp;entries.  In order to use this configuration '
            + 'option, you need to select grouping parameters.<hr>'
            + buildCustomCheckBox(constMUMFilterLimitCount, 0)
            + '&nbsp;Limit the output to machines with at&nbsp;<select id="'
            + constMUMFilterLimitSel + '" name="' + constMUMFilterLimitSel
            + '"><option selected value="least">least</option>'
            + '<option value="most">most</option></select>&nbsp;<input '
            + 'type="text" id="' + constMUMFilterLimitNum + '" name="'
            + constMUMFilterLimitNum + '" size="2">&nbsp;updates that meet '
            + 'the selection criteria.<br>'
            + buildCustomCheckBox(constEventFilterLimitPer, 0)
            + '&nbsp;Limit the output to machines where the number of updates '
            + 'meeting the selection criteria is equal to at&nbsp;<select id="'
            + constMUMFilterLimitPerSel + '" name="'
            + constMUMFilterLimitPerSel
            + '"><option selected value="least">least</option>'
            + '<option value="most">most</option></select>&nbsp;<input '
            + 'type="text" id="' + constEventFilterLimitPerNum + '" name="'
            + constEventFilterLimitPerNum + '" size="2">&nbsp;% of all '
            + 'updates in any state on those machines.';
    }

    areatxt += '</td></tr></table>';
    document.getElementById(constJavaScriptContentAreaDiv).innerHTML = areatxt;

    if(area=='assetq')
    {
        updateDynamicList(constJavaListAssetQueries, 'assetquerydiv',
            constReportConfigSumContAsset);
        updateSummaryAssetGroups();
    }
    else if(area=='eventf')
    {
        var itemArray = new Array(
            Array('Site', 'customer'),
            Array('Machine', 'machine'),
            Array('ID', 'id'),
            Array('String 1', 'string1'),
            Array('String 2', 'string2'),
            Array('Username', 'username'));
        updateDynamicList(constJavaListEventFilters, 'eventfilterdiv',
            constReportConfigSumContAsset);
        fillSelectFromArray(document.getElementById(constAssetSummaryGroupOne),
            itemArray, 'Nothing', true);
        fillSelectFromArray(document.getElementById(constAssetSummaryGroupTwo),
            itemArray, 'Nothing', true);

        itemArray = new Array(
            Array('Count', '*'),
            Array('machine', 'machine'),
            Array('files deleted', 'files deleted'),
            Array('services', 'services'),
            Array('size', 'size'),
            Array('id', 'id'),
            Array('string1', 'string1'),
            Array('string2', 'string2'));
        fillSelectFromArray(document.getElementById(constAssetSummaryDataOne),
            itemArray, '*', false);
    }
    else if(area=='mumf')
    {
        fillSelectFromArray(document.getElementById(constMUMFilterSelect),
            mumSectionsArray, 'None', false);

        itemArray = new Array(
            Array('Count', '*'),
            Array('machine', 'machine'));
        fillSelectFromArray(document.getElementById(constAssetSummaryDataOne),
            itemArray, '*', false);

        fillSelectFromArray(document.getElementById(constAssetSummaryGroupOne),
            mumGroupsArray, 'None', false);
        fillSelectFromArray(document.getElementById(constAssetSummaryGroupTwo),
            mumGroupsArray, 'None', false);
    }
}

function addSummaryContent(idx)
{
    var valtype = Number(document.getElementById(constSummaryLineType).value);
    window.opener.mumSectionsArray = window.opener.copyArray(mumSectionsArray);

    switch(valtype)
    {
    case constSummaryItemAsset:
        window.opener.localAddSummaryContent(
            document.getElementById(constSummaryLineType).value,
            document.getElementById('sumname').value,
            dynamicLists[constJavaListAssetQueries],
            dynamicNames[constJavaListAssetQueries],
            document.getElementById(constAssetSummaryGroupOne).value,
            document.getElementById(constAssetSummaryGroupTwo).value,
            document.getElementById(constAssetSummaryDataOne).value,
            document.getElementById(constAssetSummaryExcFake).value,
            document.getElementById(constAssetSummaryGroupOneRaw).value,
            document.getElementById(constAssetSummaryGroupOneDec).value,
            document.getElementById(constAssetSummaryGroupTwoRaw).value,
            document.getElementById(constAssetSummaryGroupTwoDec).value,
            document.getElementById(constAssetSummaryDataOneRaw).value,
            document.getElementById(constAssetSummaryDataOneDec).value,
            getCheckedValue(
                document.getElementsByName(constAssetQuerySetting)), null,
            null, null, null, null, null, null, null, null, null, idx);
        break;
    case constSummaryItemEvent:
        window.opener.localAddSummaryContent(
            document.getElementById(constSummaryLineType).value,
            document.getElementById('sumname').value,
            dynamicLists[constJavaListEventFilters],
            dynamicNames[constJavaListEventFilters],
            document.getElementById(constAssetSummaryGroupOne).value,
            document.getElementById(constAssetSummaryGroupTwo).value,
            document.getElementById(constAssetSummaryDataOne).value,
            null,
            document.getElementById(constAssetSummaryGroupOneRaw).value,
            document.getElementById(constAssetSummaryGroupOneDec).value,
            document.getElementById(constAssetSummaryGroupTwoRaw).value,
            document.getElementById(constAssetSummaryGroupTwoDec).value,
            document.getElementById(constAssetSummaryDataOneRaw).value,
            document.getElementById(constAssetSummaryDataOneDec).value,
            null, getCheckedValue(
                document.getElementsByName(constEventFilterSetting)),
            document.getElementById(constEventFilterLimitTop).value,
            document.getElementById(constEventFilterLimitNum).value,
            document.getElementById(constEventFilterLimitPer).value,
            document.getElementById(constEventFilterLimitPerNum).value, null,
            null, null, null, null, idx);
        break;
    case constSummaryItemMUM:
        window.opener.localAddSummaryContent(
            document.getElementById(constSummaryLineType).value,
            document.getElementById('sumname').value,
            Array(),
            Array(),
            document.getElementById(constAssetSummaryGroupOne).value,
            document.getElementById(constAssetSummaryGroupTwo).value,
            document.getElementById(constAssetSummaryDataOne).value,
            null,
            document.getElementById(constAssetSummaryGroupOneRaw).value,
            document.getElementById(constAssetSummaryGroupOneDec).value,
            document.getElementById(constAssetSummaryGroupTwoRaw).value,
            document.getElementById(constAssetSummaryGroupTwoDec).value,
            document.getElementById(constAssetSummaryDataOneRaw).value,
            document.getElementById(constAssetSummaryDataOneDec).value,
            null, null,
            document.getElementById(constEventFilterLimitTop).value,
            document.getElementById(constEventFilterLimitNum).value,
            document.getElementById(constEventFilterLimitPer).value,
            document.getElementById(constEventFilterLimitPerNum).value,
            document.getElementById(constMUMFilterSelect).value,
            document.getElementById(constMUMFilterLimitCount).value,
            document.getElementById(constMUMFilterLimitSel).value,
            document.getElementById(constMUMFilterLimitNum).value,
            document.getElementById(constMUMFilterLimitPerSel).value, idx);
        break;
    }
}

function localAddSummaryContent(sumtype, sumname, uniqs, names, groupone,
    grouptwo,
    dataone, excfake, grouponeraw, grouponedec, grouptworaw, grouptwodec,
    dataoneraw, dataonedec, assetset, eventset, limittop, limitnum, limitper,
    limitpernum, mumfilter, mumlimit, mumlimitsel, mumlimitnum, mumlimitpersel,
    idx)
{
    newContent = 0;
    if(idx==-1)
    {
        newContent = 1;
        idx = summaryNames.length;
    }
    summaryNames[idx] = sumname;
    /* I would prefer the md5(timestamp) here, but I think computing the md5 is
        too intensive to do client side.  So, the server will see an empty
        string and compute an uniq on its own. */
    if(newContent)
    {
        summaryUniqs[idx] = '';
        summaryTypeData[idx] = new Array;
        summaryTypeData[idx][constDynamicListDispTotal] = 1;
        summaryTypeData[idx][constDynamicListDispPer] = 0;
        summaryTypeData[idx][constDynamicListDispAvg] = 0;
        summaryTypeData[idx][constDynamicListDispMin] = 0;
        summaryTypeData[idx][constDynamicListDispMax] = 0;
    }
    summaryTypeData[idx][constDynamicListUniqs] = copyArray(uniqs);
    summaryTypeData[idx][constDynamicListNames] = copyArray(names);
    summaryTypeData[idx][constDynamicListGroupOne] = groupone;
    summaryTypeData[idx][constDynamicListGroupTwo] = grouptwo;
    summaryTypeData[idx][constDynamicListDataOne] = dataone;
    summaryTypeData[idx][constDynamicListExcFake] = excfake;
    summaryTypeData[idx][constDynamicListGroupOneRaw] = grouponeraw;
    summaryTypeData[idx][constDynamicListGroupOneDec] = grouponedec;
    summaryTypeData[idx][constDynamicListGroupTwoRaw] = grouptworaw;
    summaryTypeData[idx][constDynamicListGroupTwoDec] = grouptwodec;
    summaryTypeData[idx][constDynamicListDataOneRaw] = dataoneraw;
    summaryTypeData[idx][constDynamicListDataOneDec] = dataonedec;

    summaryTypes[idx] = Number(sumtype);
    switch(Number(sumtype))
    {
    case constSummaryItemAsset:
        summaryTypeData[idx][constDynamicListAssetSetting] = assetset;
        break;
    case constSummaryItemEvent:
        summaryTypeData[idx][constDynamicListEventSetting] = eventset;
        summaryTypeData[idx][constDynamicListEventLimitTop] = limittop;
        summaryTypeData[idx][constDynamicListEventLimitNum] = limitnum;
        summaryTypeData[idx][constDynamicListEventLimitPer] = limitper;
        summaryTypeData[idx][constDynamicListEventLimitPerNum] = limitpernum;
        break;
    case constSummaryItemMUM:
        summaryTypeData[idx][constDynamicListMUMFilter] = mumfilter;
        summaryTypeData[idx][constDynamicListEventLimitTop] = limittop;
        summaryTypeData[idx][constDynamicListEventLimitNum] = limitnum;
        summaryTypeData[idx][constDynamicListMUMLimitCount] = mumlimit;
        summaryTypeData[idx][constDynamicListMUMLimitSel] = mumlimitsel;
        summaryTypeData[idx][constDynamicListMUMLimitNum] = mumlimitnum;
        summaryTypeData[idx][constDynamicListEventLimitPer] = limitper;
        summaryTypeData[idx][constDynamicListEventLimitPerNum] = limitpernum;
        summaryTypeData[idx][constDynamicListMUMLimitPerSel] = mumlimitpersel;
        break;
    }
    updateSummaryDiv('');
}

function copyArray(source)
{
    newarray = new Array;
    for(var i=0; i<source.length; i++)
    {
        newarray[i] = source[i];
    }
    return newarray;
}

function storeSummarySet(reportconfiguniq, idx, sectioncompuniq, name, type,
    disptotal, dispper, dispavg, dispmin, dispmax, groupOne, groupTwo, dataOne,
    excFake, grouponeraw, grouponedec, grouptworaw, grouptwodec,
    dataoneraw, dataonedec, assetset, eventset, limittop, limitnum, limitper,
    limitpernum, mumfilter, mumlimit, mumlimitsel, mumlimitnum, mumlimitpersel)
{
    summaryUniqs[idx] = sectioncompuniq;
    summaryNames[idx] = name;
    summaryTypes[idx] = Number(type);
    summaryTypeData[idx] = new Array;
    summaryTypeData[idx][constDynamicListUniqs] = new Array;
    summaryTypeData[idx][constDynamicListNames] = new Array;
    summaryTypeData[idx][constDynamicListDispTotal] = disptotal;
    summaryTypeData[idx][constDynamicListDispPer] = dispper;
    summaryTypeData[idx][constDynamicListDispAvg] = dispavg;
    summaryTypeData[idx][constDynamicListDispMin] = dispmin;
    summaryTypeData[idx][constDynamicListDispMax] = dispmax;
    summaryTypeData[idx][constDynamicListGroupOne] = groupOne;
    summaryTypeData[idx][constDynamicListGroupTwo] = groupTwo;
    summaryTypeData[idx][constDynamicListDataOne] = dataOne;
    summaryTypeData[idx][constDynamicListExcFake] = excFake;
    summaryTypeData[idx][constDynamicListGroupOneRaw] = grouponeraw;
    summaryTypeData[idx][constDynamicListGroupOneDec] = grouponedec;
    summaryTypeData[idx][constDynamicListGroupTwoRaw] = grouptworaw;
    summaryTypeData[idx][constDynamicListGroupTwoDec] = grouptwodec;
    summaryTypeData[idx][constDynamicListDataOneRaw] = dataoneraw;
    summaryTypeData[idx][constDynamicListDataOneDec] = dataonedec;
    summaryTypeData[idx][constDynamicListAssetSetting] = assetset;
    summaryTypeData[idx][constDynamicListEventSetting] = eventset;
    summaryTypeData[idx][constDynamicListEventLimitTop] = limittop;
    summaryTypeData[idx][constDynamicListEventLimitNum] = limitnum;
    summaryTypeData[idx][constDynamicListEventLimitPer] = limitper;
    summaryTypeData[idx][constDynamicListEventLimitPerNum] = limitpernum;
    summaryTypeData[idx][constDynamicListMUMFilter] = mumfilter;
    summaryTypeData[idx][constDynamicListMUMLimitCount] = mumlimit;
    summaryTypeData[idx][constDynamicListMUMLimitSel] = mumlimitsel;
    summaryTypeData[idx][constDynamicListMUMLimitNum] = mumlimitnum;
    summaryTypeData[idx][constDynamicListMUMLimitPerSel] = mumlimitpersel;
}

function addDataToSummarySet(idx, uniq, name)
{
    var length = summaryTypeData[idx][constDynamicListUniqs].length;
    summaryTypeData[idx][constDynamicListUniqs][length] = uniq;
    summaryTypeData[idx][constDynamicListNames][length] = name;
}

  // Original:  Jerome Caron (jerome.caron@globetrotter.net)
  // This script and many more are available free online at
  // The JavaScript Source!! http://javascript.internet.com
  function fillSelectFromArray(selectCtrl, itemArray, valueToSelect,
    addNothing)
  {
    var i, j;
    var prompt;
    // empty existing items
    for (i = selectCtrl.options.length; i >= 0; i--)
    {
      selectCtrl.options[i] = null;
    }

    j = 0;
    if(addNothing)
    {
        // add an entry for the first option
        selectCtrl.options[0] = new Option('Nothing');
        selectCtrl.options[0].value = "";
        j = 1;
    }

    if (itemArray != null)
    {
      // add new items
      for (i = 0; i < itemArray.length; i++)
      {
        selectCtrl.options[j] = new Option(itemArray[i][0]);
        if (itemArray[i][1] != null)
        {
            selectCtrl.options[j].value = itemArray[i][1];
            if(itemArray[i][1]==valueToSelect)
            {
                selectCtrl.options[j].selected = true;
            }
        }
        j++;
      }
    }
  }


function updateSummaryAssetGroups()
{
    get_displayfields_for_search(constAssetSummaryGroupOne,
        dynamicLists[constJavaListAssetQueries], 0);
    get_displayfields_for_search(constAssetSummaryGroupTwo,
        dynamicLists[constJavaListAssetQueries], 0);
    get_displayfields_for_search(constAssetSummaryDataOne,
        dynamicLists[constJavaListAssetQueries], 1);
}


function buildCustomCheckBox(name, value)
{
    var text = '<input type="hidden" name="' + name + '" id="' + name
        + '" value="' + value + '"><input type="checkbox" ';
    if(value)
    {
        text += 'checked';
    }
    text += ' name="' + name + constActCheckboxAppend + '" id="' + name
        + constActCheckboxAppend + '" value="1" onclick="'
        + 'if(document.getElementById(\'' + name + '\').value==1)'
        + '{'
        + '     document.getElementById(\'' + name + '\').value=0;'
        + '}'
        + 'else'
        + '{'
        + '     document.getElementById(\'' + name + '\').value=1;'
        + '}'
        + '">';

    return text;
}

function editSummaryContent(idx)
{
    var url = 'addsum.php?idx=' + idx + '&disposition=' + controlDisposition;
    window.open(url);
}

function updateVars()
{
    document.getElementById(constRepfFormPrefix
        + constReportConfigSumContAsset).value
        = window.opener.summaryNames[idx];
    document.getElementById('sumname').value
        = window.opener.summaryNames[idx];
    /* Note: call this just after setting up the arrays but before assigning
        the values to the dropdown boxes */
    switch(window.opener.summaryTypes[idx])
    {
    case constSummaryItemAsset:
        dynamicLists[constJavaListAssetQueries]
            = copyArray(
            window.opener.summaryTypeData[idx][constDynamicListUniqs]);
        dynamicNames[constJavaListAssetQueries]
            = copyArray(
            window.opener.summaryTypeData[idx][constDynamicListNames]);
        handleSummaryTabs('assetq');
        break;
    case constSummaryItemEvent:
        dynamicLists[constJavaListEventFilters]
            = copyArray(
            window.opener.summaryTypeData[idx][constDynamicListUniqs]);
        dynamicNames[constJavaListEventFilters]
            = copyArray(
            window.opener.summaryTypeData[idx][constDynamicListNames]);
        handleSummaryTabs('eventf');
        break;
    case constSummaryItemMUM:
        handleSummaryTabs('mumf');
        break;
    }

    /* Now set the current settings for the drop downs */
    selectString(document.getElementById(constAssetSummaryGroupOne),
        window.opener.summaryTypeData[idx][constDynamicListGroupOne]);
    selectString(document.getElementById(constAssetSummaryGroupTwo),
        window.opener.summaryTypeData[idx][constDynamicListGroupTwo]);
    selectString(document.getElementById(constAssetSummaryDataOne),
        window.opener.summaryTypeData[idx][constDynamicListDataOne]);

    /* Raw data type and decimal precision settings */
    document.getElementById(constAssetSummaryGroupOneRaw).value =
        window.opener.summaryTypeData[idx][constDynamicListGroupOneRaw];
    document.getElementById(constAssetSummaryGroupOneDec).value =
        window.opener.summaryTypeData[idx][constDynamicListGroupOneDec];
    document.getElementById(constAssetSummaryGroupTwoRaw).value =
        window.opener.summaryTypeData[idx][constDynamicListGroupTwoRaw];
    document.getElementById(constAssetSummaryGroupTwoDec).value =
        window.opener.summaryTypeData[idx][constDynamicListGroupTwoDec];
    document.getElementById(constAssetSummaryDataOneRaw).value =
        window.opener.summaryTypeData[idx][constDynamicListDataOneRaw];
    document.getElementById(constAssetSummaryDataOneDec).value =
        window.opener.summaryTypeData[idx][constDynamicListDataOneDec];

    switch(window.opener.summaryTypes[idx])
    {
    case constSummaryItemAsset:
        setCheckedValue(document.getElementsByName(constAssetQuerySetting),
            window.opener.summaryTypeData[idx][constDynamicListAssetSetting]);
        /* Set the checkbox for excluding fake */
        document.getElementById(constAssetSummaryExcFake).value =
            window.opener.summaryTypeData[idx][constDynamicListExcFake];
        document.getElementById(constAssetSummaryExcFake
            +constActCheckboxAppend).checked =
            window.opener.summaryTypeData[idx][constDynamicListExcFake]
            ==0 ? false : true;
        break;
    case constSummaryItemEvent:
        setCheckedValue(document.getElementsByName(constEventFilterSetting),
            window.opener.summaryTypeData[idx][constDynamicListEventSetting]);
        document.getElementById(constEventFilterLimitTop).value =
            window.opener.summaryTypeData[idx][constDynamicListEventLimitTop];
        document.getElementById(constEventFilterLimitTop
            +constActCheckboxAppend).checked =
            window.opener.summaryTypeData[idx][constDynamicListEventLimitTop]
            ==0 ? false : true;
        document.getElementById(constEventFilterLimitNum).value =
            window.opener.summaryTypeData[idx][constDynamicListEventLimitNum];
        document.getElementById(constEventFilterLimitPer).value =
            window.opener.summaryTypeData[idx][constDynamicListEventLimitPer];
        document.getElementById(constEventFilterLimitPer
            +constActCheckboxAppend).checked =
            window.opener.summaryTypeData[idx][constDynamicListEventLimitPer]
            ==0 ? false : true;
        document.getElementById(constEventFilterLimitPerNum).value =
            window.opener.summaryTypeData[idx]
                [constDynamicListEventLimitPerNum];
        break;
    case constSummaryItemMUM:
        document.getElementById(constMUMFilterSelect).value =
            window.opener.summaryTypeData[idx][constDynamicListMUMFilter];
        document.getElementById(constEventFilterLimitTop).value =
            window.opener.summaryTypeData[idx][constDynamicListEventLimitTop];
        document.getElementById(constEventFilterLimitTop
            +constActCheckboxAppend).checked =
            window.opener.summaryTypeData[idx][constDynamicListEventLimitTop]
            ==0 ? false : true;
        document.getElementById(constEventFilterLimitNum).value =
            window.opener.summaryTypeData[idx][constDynamicListEventLimitNum];
        document.getElementById(constMUMFilterLimitCount).value =
            window.opener.summaryTypeData[idx][constDynamicListMUMLimitCount];
        document.getElementById(constMUMFilterLimitCount
            +constActCheckboxAppend).checked =
            window.opener.summaryTypeData[idx][constDynamicListMUMLimitCount]
            ==0 ? false : true;
        document.getElementById(constMUMFilterLimitSel).value =
            window.opener.summaryTypeData[idx][constDynamicListMUMLimitSel];
        document.getElementById(constMUMFilterLimitNum).value =
            window.opener.summaryTypeData[idx][constDynamicListMUMLimitNum];
        document.getElementById(constEventFilterLimitPer).value =
            window.opener.summaryTypeData[idx][constDynamicListEventLimitPer];
        document.getElementById(constEventFilterLimitPer
            +constActCheckboxAppend).checked =
            window.opener.summaryTypeData[idx][constDynamicListEventLimitPer]
            ==0 ? false : true;
        document.getElementById(constEventFilterLimitPerNum).value =
            window.opener.summaryTypeData[idx]
                [constDynamicListEventLimitPerNum];
        document.getElementById(constMUMFilterLimitPerSel).value =
            window.opener.summaryTypeData[idx][constDynamicListMUMLimitPerSel];
        break;
    }

    window.opener.updateSummaryControls();
}


function selectString(control, string)
{
    for (i = 0; i < control.options.length; i++)
    {
        control.options[i].selected = false;
        if(control.options[i].value==string)
        {
            control.options[i].selected = true;
        }
    }
}

function delSummaryContent(idx)
{
    deleteShiftArray(summaryUniqs, idx, constLiteralType);
    deleteShiftArray(summaryNames, idx, constLiteralType);
    deleteShiftArray(summaryTypes, idx, constLiteralType);
    deleteShiftArray(summaryTypeData, idx, constArrayType);
    updateSummaryDiv('');
}


function deleteShiftArray(array, delIdx, type)
{
    var j = array.length;
    var shift = 0;
    for(i=0; i<j; i++)
    {
        if(shift)
        {
            switch(type)
            {
            case constLiteralType:
                array[i-1] = array[i];
                break;
            case constArrayType:
                array[i-1] = copyArray(array[i]);
                break;
            }
            array[i] = null;
        }
        if(i==delIdx)
        {
            array[i] = null;
            shift = 1;
        }
    }

    array.length = j-1;
}


function addRawDataOptions()
{
    return '<option value="' + constDataUnitsNone + '">None</option>'
        + '<option value="' + constDataUnitsSeconds + '">Seconds</option>'
        + '<option value="' + constDataUnitsPercent + '">Percent</option>'
        + '<option value="' + constDataUnitsBytes + '">Bytes</option>'
        + '<option value="' + constDataUnitsKBytes + '">KBytes</option>'
        + '<option value="' + constDataUnitsPerSecond + '">'
        + 'Per Second</option>';
}

function addHiddenSummaryInput(i, ctrname, dataname)
{
    return '<input type="hidden" name="' + constRepfFormPrefix
        + summaryDivUniq + '_' + i + '_'
        + ctrname + '" value="'
        + summaryTypeData[i][dataname] + '">';
}

/* Both getCheckedValue and setCheckedValue are public domain:
    http://www.somacon.com/p143.php
*/

// return the value of the radio button that is checked
// return an empty string if none are checked, or
// there are no radio buttons
function getCheckedValue(radioObj) {
    if(!radioObj)
        return "";
    var radioLength = radioObj.length;
    if(radioLength == undefined)
        if(radioObj.checked)
        {
            return radioObj.value;
        }
        else
        {
            return "";
        }
    for(var i = 0; i < radioLength; i++) {
        if(radioObj[i].checked) {
            return radioObj[i].value;
        }
    }
    return "";
}

// set the radio button with the given value as being checked
// do nothing if there are no radio buttons
// if the given value does not exist, all the radio buttons
// are reset to unchecked
function setCheckedValue(radioObj, newValue) {
    if(!radioObj)
        return;
    var radioLength = radioObj.length;
    if(radioLength == undefined) {
        radioObj.checked = (radioObj.value == newValue.toString());
        return;
    }
    for(var i = 0; i < radioLength; i++) {
        radioObj[i].checked = false;
        if(radioObj[i].value == newValue.toString()) {
            radioObj[i].checked = true;
        }
    }
}

function shiftSumComp(idx, shift)
{
    shiftArray(summaryNames, idx, shift);
    shiftArray(summaryUniqs, idx, shift);
    shiftArray(summaryTypes, idx, shift);
    shiftArray(summaryTypeData, idx, shift);
    updateSummaryDiv('');
}


/* shiftArray

    Moves the array element "idx" "shift" places.  "shift" is only
    supported for -1 and 1.
*/
function shiftArray(myArray, idx, shift)
{
    var newidx = idx+shift;
    var tempItem;

    tempItem = myArray[newidx];
    myArray[newidx] = myArray[idx];
    myArray[idx] = tempItem;
}


function getGroupDataDec(text, groupName, dataName, decName)
{
    var text = '<table class="mytable" cellpadding="10" '
        + 'cellspacing="0"><tr><td class="mycol">' + text + '&nbsp;<select '
        + 'id="' + groupName + '" name="'
        + groupName + '"></select>&nbsp;&nbsp;&nbsp;&nbsp;'
        + 'Raw data type:&nbsp;<select id="' + dataName
        + '" name="' + dataName + '">'
        + addRawDataOptions() + '</select>&nbsp;&nbsp;&nbsp;&nbsp;'
        + 'Decimal precision:&nbsp;<input type="text" size="2" id="'
        + decName + '" name="'
        + decName + '"></td></tr></table>';
    return text;
}

function updateSummaryControls()
{
    var disableControl;

    for(i=0; i<summaryNames.length; i++)
    {
        disableControl = false;
        if(((summaryTypeData[i][constDynamicListEventLimitTop]==1) &&
            (summaryTypes[i]!=constSummaryItemMUM)) ||
            ((summaryTypeData[i][constDynamicListGroupOne]=='') &&
            (summaryTypeData[i][constDynamicListGroupTwo]=='')))
        {
            summaryTypeData[i][constDynamicListDispPer] = 0;
            document.getElementById(constRepfFormPrefix +
                summaryDivUniq + '_' + i + '_' + constReptCtrSummaryDispPer
                + constActCheckboxAppend).checked = false;
            document.getElementById(constRepfFormPrefix +
                summaryDivUniq + '_' + i + '_' + constReptCtrSummaryDispPer)
                .value = 0;
            disableControl = true;
        }

        document.getElementById(constRepfFormPrefix +
            summaryDivUniq + '_' + i + '_' + constReptCtrSummaryDispPer
            + constActCheckboxAppend).disabled = disableControl;
    }
}


function validateAddSum()
{
    var valtype = Number(document.getElementById(constSummaryLineType).value);

    if(!validateNumber(
        document.getElementById(constAssetSummaryGroupOneDec).value, 0,
        null))
    {
        alert(constDecimalValidationFail);
        return false;
    }
    if(!validateNumber(
        document.getElementById(constAssetSummaryGroupTwoDec).value, 0,
        null))
    {
        alert(constDecimalValidationFail);
        return false;
    }
    if(!validateNumber(
        document.getElementById(constAssetSummaryDataOneDec).value, 0,
        null))
    {
        alert(constDecimalValidationFail);
        return false;
    }

    switch(valtype)
    {
    case constSummaryItemAsset:
        /* no additional validation items */
        break;
    case constSummaryItemEvent:
        if(!validateNumber(
            document.getElementById(constEventFilterLimitNum).value, 1,
            null))
        {
            alert(constLimitNumValidationFail);
            return false;
        }
        if(!validateNumber(
            document.getElementById(constEventFilterLimitPerNum).value, 0,
            100))
        {
            alert(constLimitPerNumValidationFail);
            return false;
        }
        break;
    case constSummaryItemMUM:
        if(!validateNumber(
            document.getElementById(constEventFilterLimitNum).value, 1,
            null))
        {
            alert(constLimitNumValidationFail);
            return false;
        }
        if(!validateNumber(
            document.getElementById(constMUMFilterLimitNum).value, 0,
            null))
        {
            alert(constLimitUpdatesValidationFail);
            return false;
        }
        if(!validateNumber(
            document.getElementById(constEventFilterLimitPerNum).value, 0,
            100))
        {
            alert(constLimitPerNumValidationFail);
            return false;
        }
        break;
    }

    return true;
}

function validateNumber(str, minValue, maxValue)
{
    /* No negative values */
    if((minValue!=null) && (minValue<0))
    {
        return false;
    }
    if((maxValue!=null) && (maxValue<0))
    {
        return false;
    }

    /* Empty string bypass */
    if(str=='')
    {
        return true;
    }

    /* First, verify everything is a digit - decimals are not allowed */
    if(!str.match(/^\d*$/))
    {
        return false;
    }

    /* Check the range */
    var value = new Number(str);
    if((minValue!=null) && (value<minValue))
    {
        return false;
    }
    if((maxValue!=null) && (value>maxValue))
    {
        return false;
    }

    return true;
}


function getMoveHtml(i, funcName, arrayLength)
{
    var divtext = '<td class="mycol">';
    /* Each location except the last one gets a down arrow */
    if(i!=(arrayLength-1))
    {
        divtext += '<img src="down.jpg" onclick="' + funcName + '(' + i
            + ',1)">';
        if(i!=0)
        {
            /* Add a space when there's two images */
            divtext += '&nbsp;';
        }
    }
    /* Each location except the first one get an up arrow */
    if(i!=0)
    {
        divtext += '<img src="up.jpg" onclick="' + funcName + '(' + i
            + ',-1)">';
    }

    /* Handle the case where neither arrow applies */
    if(arrayLength==1)
    {
        divtext += '&nbsp;';
    }

    divtext += '</td>';

    return divtext;
}


function shiftSection(idx, shift)
{
    shiftArray(sectionArray, idx, shift);
    shiftArray(sectionNameArray, idx, shift);
    updateSection('');
}


function moveGroupList(srcList, destList, moveAll, cfgList, cfgString)
{
    var src = document.getElementById(srcList);
    var dest = document.getElementById(destList);
    var i;
    var str = '\'';

    for(i=0; i<src.length; i++)
    {
        if((src.options[i].selected) || (moveAll))
        {
            dest.options[dest.length] = new Option(src.options[i].text,
                src.options[i].value, false, false);
            src.options[i] = null;
            i--;
        }
    }

    var cfg = document.getElementById(cfgList);
    for(i=0; i<cfg.length; i++)
    {
        if(str!='')
        {
            str += '\',\'';
        }
        str += cfg.options[i].value;
    }
    str += '\'';

    document.getElementById(cfgString).value = str;
}


/* addMUMSection

    Creates a new MUM section and opens up the editing window.
*/
function addMUMSection()
{
    /* FIX ME: constant */
    window.open('report.php?act=5&isparent=' + constIsParentNewMUMFilter
        + '&section=' + constSectionMUMReport);
}


function addMumFilter(uniq, name)
{
    window.opener.localMumFilter(uniq, name);
}


function localMumFilter(uniq, name)
{
    var filters = document.getElementById(constMUMFilterSelect);
    var idx = filters.length;

    filters.options[idx] = new Option(name);
    filters.options[idx].value = uniq;
    filters.options[idx].selected = true;

    mumSectionsArray.push(Array(name, uniq));
}

function renameMumFilter(uniq, name)
{
    window.opener.localRenameMumFilter(uniq, name);
}

function localRenameMumFilter(uniq, name)
{
    var i;
    var filters = document.getElementById(constMUMFilterSelect);
    for(i=0; i<filters.length; i++)
    {
        if(filters.options[i].value==uniq)
        {
            filters.options[i].text = name;
            break;
        }
    }
    for(i=0; i<mumSectionsArray.length; i++)
    {
        if(mumSectionsArray[i][1]==uniq)
        {
            mumSectionsArray[i][0] = name;
            break;
        }
    }
}


//-->