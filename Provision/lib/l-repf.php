<?php

/* Helpful functions and constants for the REPF C-interface.  This file
    contains copied constants from iface/repf.h.

Revision history:

Date        Who     What
----        ---     ----
24-Feb-07   BTE     Original creation.
15-Apr-07   BTE     Added some constants.
03-May-07   BTE     Added some constants.
04-May-07   BTE     Added constFormView.
04-Jun-07   BTE     Added some constants and cases.
20-Jun-07   BTE     Bug 4150: email: Event Reports Feedback #1.
28-Jun-07   BTE     Added constSectionTypeGeneric.
31-Jul-07   BTE     Added support for summary sections.
17-Aug-07   BTE     Changes for summary sections phase 1.
04-Oct-07   BTE     Changes for summary sections phase 5.

*/

    /* Section types */
    define('constSectionTypeEvent',      1);
    define('constSectionTypeAsset',      2);
    define('constSectionTypeMUM',        3);
    define('constSectionTypeReport',     4);
    define('constSectionTypeExecSummary',   5);
    define('constSectionTypeGeneric',    6);

    /* MUM report title default */
    define('constMUMSectionTitle',      'Microsoft Update Management Section');
    define('constEventSectionTitle',    'Event Section');
    define('constExecSumSectionTitle',  'Summary Section');
    define('constAssetSectionTitle',    'Asset Section');

    /* Config types */
    define('constRepfConfigTypeRept',       0);
    define('constRepfConfigTypeSection',    1);

    /* section constants for report.php and REPF_ListReports.$section */
    define('constSectionGeneric', 0);
    define('constSectionAdd',       1);
    define('constSectionSchedule',  2);
    define('constSectionAddSched',  3);
    define('constSectionExecSummary',   4);
    define('constSectionEvent',     5);
    define('constSectionAddEventFilters',   6);
    define('constSectionAddMgrpInclude',    7);
    define('constSectionAsset',     8);
    define('constSectionMUMReport', 9);
    define('constSectionAddAssetQueries',   10);

    /* List constants from other pages */
    define('constActListReports',       0);
    define('constActListSections',      4);
    define('constActListSchedules',     0);
    define('constActListAddSections',   0);

    /* REPF_RunInteractiveReport.runContext options */
    define('constRepfRunContextImmed',      0);
    define('constRepfRunContextSched',      1);
    define('constRepfRunContextPreview',    2);

    /* REPF_GenerateHTMLConfig.disposition constants */
    define('constFormCreate',           0);
    define('constFormEdit',             1);
    define('constFormView',             2);

    /* addChecks.type constants (report/control.js) */
    define('constAddCheckTypeSection',      0);
    define('constAddCheckTypeSchedule',     1);
    define('constAddCheckTypeEventFilters', 2);
    define('constAddCheckTypeEventIncludeMgrp', 3);
    define('constAddCheckTypeEventExcludeMgrp', 4);
    define('constAddCheckTypeAssetQueries', 5);

    /* Summary content controls */
    define('constReportConfigSumContAsset',     'sumcontasset');

    /* REPF_ListReports

        Search/sort/display page helper function.  Generates a page for the
        user $username, section $section (enumerated type).  The parent page
        $pageLink defines $listact for post action.
    */
    function REPF_ListReports($username, $section, $listact, $pageLink)
    {
        switch($section)
        {
        case constSectionGeneric:
            switch($listact)
            {
            case constActListReports:
                $tableid = constTableIDReports;
                break;
            case constActListSections:
                $tableid = constTableIDSections;
                break;
            default:
                logs::log(__FILE__, __LINE__, "REPF_ListReports: unknown listaction $listact", 0);
                return;
                break;
            }
            break;
        case constSectionEvent:
            switch($listact)
            {
            case constActListReports:
                $tableid = constTableIDReports;
                break;
            case constActListSections:
                $tableid = constTableIDEventSections;
                break;
            default:
                logs::log(__FILE__, __LINE__, "REPF_ListReports: unknown listaction $listact", 0);
                return;
                break;
            }
            break;
        case constSectionMUMReport:
            switch($listact)
            {
            case constActListReports:
                $tableid = constTableIDReports;
                break;
            case constActListSections:
                $tableid = constTableIDMUMSections;
                break;
            default:
                logs::log(__FILE__, __LINE__, "REPF_ListReports: unknown listaction $listact", 0);
                return;
                break;
            }
            break;
        case constSectionExecSummary:
            switch($listact)
            {
            case constActListReports:
                $tableid = constTableIDReports;
                break;
            case constActListSections:
                $tableid = constTableIDExecSumSections;
                $err = PHP_REPF_PrepareExecSummaryDisplay(CUR);
                if($err!=constAppNoErr)
                {
                    echo "\nAn error has occurred processing this page.  See ";
                    echo "<a href=\"../acct/csrv.php?error\">errlog.txt</a>\n";
                    return;
                }
                break;
            default:
                logs::log(__FILE__, __LINE__, "REPF_ListReports: unknown listaction $listact", 0);
                return;
                break;
            }
            break;
        case constSectionAdd:
            $tableid = constTableIDAddSections;
            break;
        case constSectionSchedule:
            $tableid = constTableIDSchedules;
            break;
        case constSectionAddSched:
            $tableid = constTableIDAddSchedules;
            break;
        case constSectionAddEventFilters:
            $tableid = constTableIDAddEventFilters;
            break;
        case constSectionAddMgrpInclude:
            $tableid = constTableIDAddMgrpInclude;
            break;
        case constSectionAddAssetQueries:
            $tableid = constTableIDAddAssetQueries;
            break;
        default:
            logs::log(__FILE__, __LINE__, "REPF_ListReports: unknown section $section", 0);
            return;
            break;
        }
        $set = 1;
        $sort = 1;
        if(server_var('QUERY_STRING'))
        {
            if(strpos(server_var('QUERY_STRING'), "set")===false)
            {
                $set = 0;
            }
            else if(strpos(server_var('QUERY_STRING'), "sort")===false)
            {
                $sort = 0;
            }
        }
        else
        {
            $set = 0;
            $sort = 0;
        }

        $displayFull = 1;
        if(($set) || ($sort))
        {
            $err = PHP_HTML_StoreSearchOptions(CUR,
                isset($GLOBALS["HTTP_RAW_POST_DATA"]) ?
                $GLOBALS["HTTP_RAW_POST_DATA"] : NULL, $tableid,
                $username, server_var('QUERY_STRING'));
            if($err!=constAppNoErr)
            {
                echo "\nAn error has occurred processing this page.  See ";
                echo "<a href=\"../acct/csrv.php?error\">errlog.txt</a>.\n";
                $displayFull = 0;
            }
        }

        if($displayFull)
        {
            $link = $pageLink . $listact;
            $err = PHP_CSRV_GetTable(CUR, $html, server_var('QUERY_STRING'),
                $tableid, NULL, $username, $link . '&set=1',
                $link . '&sort=', $link);
            if($err!=constAppNoErr)
            {
                echo "\nAn error has occurred processing this page.  See ";
                echo "<a href=\"../acct/csrv.php?error\">errlog.txt</a>.\n";
            }
        }

        echo $html;
    }
