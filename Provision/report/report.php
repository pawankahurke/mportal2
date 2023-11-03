<?php

/* report.php - main report UI handler */

/*
Revision history:

Date        Who     What
----        ---     ----
24-Feb-07   BTE     Original creation.
14-Mar-07   BTE     Lots of new functionality.
19-Mar-07   BTE     Reorganized the help text.
03-Apr-07   BTE     Many bugfixes.
15-Apr-07   BTE     Many bugfixes.
19-Apr-07   BTE     Ensure that immediate reports cannot be disabled.
03-May-07   BTE     Added global rights logic.
04-May-07   BTE     Added support for read-only forms.
09-May-07   BTE     Added view-only links, run now opens a new window.
04-Jun-07   BTE     Added cases for event sections.
20-Jun-07   BTE     Bug 4150: email: Event Reports Feedback #1.
22-Jun-07   BTE     Bug 4156: Reports small changes and questions - #2 (minor
                    text things).  Bug 4154: Resolve issues from "Re: Global,
                    admin etc." email.
24-Jun-07   BTE     Fixed some minor title issues, bug 4181: Change event
                    section defaults.
27-Jun-07   BTE     Bug 4189: Add classic report links and page.
28-Jun-07   BTE     Bug 4204: Fix default report titles.
08-Jul-07   BTE     Bug 4226: Deleting management items need to verify they are
                    not in use.
12-Jul-07   BTE     Bug 4234: Fix report.php to allow large interactive
                    reports.
31-Jul-07   BTE     Added support for summary sections.
17-Aug-07   BTE     Changes for summary sections phase 1.
01-Sep-07   BTE     Fixed bug preventing enabling/disabling global reports
                    owned by a different user.
04-Sep-07   BTE     Fixed name constraint messages.
09-Sep-07   BTE     Text change.
28-Sep-07   BTE     Changes for summary sections phase 4.
04-Oct-07   BTE     Increased the size of the click here text.

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
include('../patch/local.php');

/* act constants for this page */
/* defined in l-repf.php: define('constActListReports',   0); */
define('constActCreateReport',      1);
define('constActEditReport',        2);
define('constActSaveReport',        3);
/* defined in l-repf.php: define('constActListSections',  4); */
define('constActCreateSection',     5);
define('constActEditSection',       6);
define('constActSaveSection',       7);
define('constActRun',               8);
define('constActDeleteReport',      9);
define('constActDeleteSection',     10);
/* future development: define('constActManageQueue',       11); */
define('constActGenCreateSection',  12);
define('constActCopyReport',        13);
define('constActCopySection',       14);
define('constActEnableReport',      15);
define('constActDisableReport',     16);
define('constActPreviewReport',     17);
define('constActCloneReport',       18);   /* Not a separate config page */
define('constActCloneRunReport',    19);   /* Not a separate config page */
define('constActCloneSection',      20);   /* Not a separate config page */
define('constActViewReport',        21);
define('constActViewSection',       22);
define('constActLegacy',            23);

/* $isparent settings shared with report/control.js */
define('constIsParentNothing',      0);
define('constIsParentNewSection',   1);
define('constIsParentEditSection',  2);
define('constIsParentNewMUMFilter', 3);
define('constIsParentEditMUMFilter', 4);

/* REPH_GetTitle

        Computes the page title given the current action $act and section
        $section.
    */
function REPH_GetTitle(
    $act,
    $oldact,
    $section,
    $reportuniq,
    $sectionuniq,
    $db
) {
    $type = '';
    $reptname = '';
    $sectname = '';
    switch ($section) {
        case constSectionGeneric:
            /* $type should be empty */
            break;
        case constSectionEvent:
            $type = 'Event';
            break;
        case constSectionAsset:
            $type = 'Asset';
            break;
        case constSectionMUMReport:
            $type = 'Microsoft Update Management';
            break;
        case constSectionExecSummary:
            $type = 'Summary';
            break;
        default:
            logs::log(__FILE__, __LINE__, "report.php: unknown $section for REPH_GetTitle", 0);
            return 'unknown';
    }

    if ($reportuniq) {
        $sql = 'SELECT reportname FROM Report WHERE reportuniq=\''
            . "$reportuniq'";
        $row = find_one($sql, $db);
        if ($row) {
            $reptname = $row['reportname'];
        }
    }
    if ($sectionuniq) {
        $sql = 'SELECT sectionname FROM Section WHERE sectionuniq=\''
            . "$sectionuniq'";
        $row = find_one($sql, $db);
        if ($row) {
            $sectname = $row['sectionname'];
        }
    }

    switch ($act) {
        case constActCreateReport:
            $title = "Add a Report";
            break;
        case constActEditReport:
            $title = "Edit Report \"$reptname\"";
            break;
        case constActSaveReport:
            $reptname = get_string(constRepfFormPrefix
                . constReportNameConfigUniq, '');
            $title = "Report \"$reptname\"";
            if ($oldact == constActCreateReport) {
                $title .= ' Added';
            } else {
                $title .= ' Edited';
            }
            break;
        case constActListSections:
            if ($type == '') {
                $title = 'Sections';
            } else {
                $title = "$type - Sections";
            }
            break;
        case constActCreateSection:
            $title = "$type - Add a Section";
            break;
        case constActEditSection:
            $title = "Edit $type Section \"$sectname\"";
            break;
        case constActSaveSection:
            $sectname = get_string(constRepfFormPrefix
                . constSectionNameEventConfigUniq, '');
            if ($sectname == '') {
                $sectname = get_string(constRepfFormPrefix
                    . constSectionNameMUMConfigUniq, '');
            }
            if ($sectname == '') {
                $sectname = get_string(constRepfFormPrefix
                    . constSectionNameExecSumConfigUniq, '');
            }
            $title = "$type Section \"$sectname\"";
            if ($oldact == constActCreateSection) {
                $title .= ' Added';
            } else {
                $title .= ' Edited';
            }
            break;
        case constActRun:
            $title = "Run a Report";
            break;
        case constActDeleteReport:
            $title = "Delete Report \"$reptname\"";
            break;
        case constActDeleteSection:
            if ($type == '') {
                $title = "Delete Section \"$sectname\"";
            } else {
                $title = "$type - Delete Section \"$sectname\"";
            }
            break;
        case constActGenCreateSection:
            $title = 'Select a Section Type';
            break;
        case constActCopyReport:
            $title = "Copy a Report";
            break;
        case constActCopySection:
            if ($type == '') {
                $title = 'Copy a Section';
            } else {
                $title = "$type - Copy a Section";
            }
            break;
        case constActEnableReport:
            $title = "Enable Report";
            break;
        case constActDisableReport:
            $title = "Disable Report";
            break;
        case constActPreviewReport:
            $title = "Preview Report";
            break;
        case constActViewReport:
            $title = "View Report \"$reptname\"";
            break;
        case constActViewSection:
            if ($type == '') {
                $title = "View Section \"$sectname\"";
            } else {
                $title = "$type - View Section \"$sectname\"";
            }
            break;
        case constActLegacy:
            $title = 'Legacy Reports';
            break;
        case constActListReports:
        default:
            $title = "Reports";
            break;
    }

    return $title;
}


/* REPH_PrintLinks

        Prints standard report navigation links for the user $user and section
        $section.
    */
function REPH_PrintLinks($section)
{
    $cmd = "report.php?section=$section&act=";
    $a = array();
    $a[] = html_link($cmd . constActListReports, 'reports');
    $a[] = html_link($cmd . constActCreateReport, 'add report');
    $a[] = html_link($cmd . constActListSections, 'sections');
    switch ($section) {
        case constSectionGeneric:
            $a[] = html_link($cmd . constActGenCreateSection, 'add section');
            break;
        default:
            $a[] = html_link($cmd . constActCreateSection, 'add section');
            break;
    }
    $a[] = html_link('sched.php', 'manage schedules');

    echo jumplist($a);
}


function REPH_HandleParent($isparent, $sectionuniq)
{
    $db = db_select($GLOBALS['PREFIX'] . 'report');
    $sql = "SELECT QUOTE(sectionname) FROM Section WHERE sectionuniq='"
        . "$sectionuniq'";
    $row = find_one($sql, $db);
    if ($row) {
        $name = $row['QUOTE(sectionname)'];
        switch ($isparent) {
            case constIsParentNothing:
                /* Nothing */
                break;
            case constIsParentNewSection:
                if ($row) {
                    echo "<p>" . constFontSizeClickHere
                        . "Click <a href=\"#\" onclick=\""
                        . "addSectionButton('$sectionuniq',$name);"
                        . "window.close();\">here</a>"
                        . " to add this new section.</font>";
                }
                break;
            case constIsParentEditSection:
                if ($row) {
                    echo '<p>' . constFontSizeClickHere
                        . 'Click <a href="#" onclick="renameSection(\''
                        . "$sectionuniq',$name);window.close();\">here</a> "
                        . 'to return to the report definition page.</font>';
                }
                break;
            case constIsParentNewMUMFilter:
                if ($row) {
                    echo '<p>' . constFontSizeClickHere
                        . 'Click <a href="#" onclick="addMumFilter(\''
                        . "$sectionuniq',$name);window.close();\">here</a> "
                        . 'to return to the MUM filter definition page.'
                        . '</font>';
                }
                break;
            case constIsParentEditMUMFilter:
                if ($row) {
                    echo '<p>' . constFontSizeClickHere
                        . 'Click <a href="#" onclick="renameMumFilter(\''
                        . "$sectionuniq',$name);window.close();\">here</a> "
                        . 'to return to the MUM filter  definition page.'
                        . '</font>';
                }
                break;
        }
    }
}

function REPH_PrintHelpText($act)
{
    switch ($act) {
        case constActCreateReport:
            echo 'To add a new report, please perform the following steps:<br>'
                . '<ol><li>Enter a report title.<li>Enter one or more report '
                . 'destinations.<li>If you select e-mail as a destination, '
                . 'select the desired recipients and, if necessary enter their'
                . ' e-mail addresses.<li>Select a schedule you added '
                . 'previously or, add a new one, and then select it.  You can '
                . 'change the '
                . 'settings of an existing schedule by clicking on "Edit..."'
                . ' after you select it.<li>'
                . 'Reports are composed of one or more sections.  In a report '
                . 'section you specify the content you want retrieved, and its'
                . ' formatting.  Select a report section you added previously '
                . 'or, '
                . 'add a new one, and then select it.  You can access the '
                . 'settings of an existing report section by clicking on '
                . '"Edit..." after you select it.'
                . '<li>When you have completed the above steps, click on the '
                . '"Add" button.</ol>';
            break;
        case constActListReports:
            echo 'Reports consist of '
                . 'schedules and sections. Both schedules and sections are '
                . 'items defined independently of reports. This gives you '
                . 'great flexibility in preparing reports mixing and '
                . 'matching sections and schedules depending on your needs.<p>'
                . 'Schedules define the timing of report production. Sections '
                . 'define the content.<p>'
                . 'Schedules define the timing of actions ranging from report '
                . 'production to notification and Scrip execution. Initially, '
                . 'they are used only for report production.<p>'
                . 'By defining schedules independently of the actions they '
                . 'apply to, you gain flexibility and reduce the amount of '
                . 'time spent specifying when an action should occur. You can '
                . 'define a schedule, and assign it to multiple items. If you '
                . 'decide to change that schedule, the change you make will '
                . 'apply automatically to all actions you assigned that '
                . 'schedule to.<p>'
                . 'Note that you can assign multiple schedules to one report. '
                . 'This makes it possible to define reports which you '
                . 'want produced on multiple cycles (e.g. weekly and monthly, '
                . 'or on the first day of each quarter) with a single action.'
                . '<p>'
                . 'Sections specify the retrieval criteria, structure, and '
                . 'formatting of information you want included in reports. '
                . 'Individual sections can be used in multiple reports. When '
                . 'you change the definition of a section, the change will be '
                . 'applied to all reports that use that section.<p>'
                . 'You can assign multiple sections to a single report. This '
                . 'gives you the ability to define compelling customer '
                . 'deliverables with a single action.';
            break;
    }
}

function REPH_CreateOrCopyReport(
    $act,
    &$reportuniq,
    &$copyuniq,
    $user,
    $section,
    $db
) {
    $type = constSectionTypeGeneric;
    switch ($section) {
        case constSectionEvent:
            $type = constSectionTypeEvent;
            break;
        case constSectionAsset:
            $type = constSectionTypeAsset;
            break;
        case constSectionMUMReport:
            $type = constSectionTypeMUM;
            break;
        case constSectionExecSummary:
            $type = constSectionTypeExecSummary;
            break;
    }
    if ($act == constActCreateReport) {
        if (PHP_REPD_CreateSectionType(
            CUR,
            $reportuniq,
            $user['username'],
            $type
        ) != constAppNoErr) {
            REPT_PrintError();
            $reportuniq = '';
        }
        $copyuniq = $reportuniq;
    } else {
        $sql = "SELECT reportname FROM Report WHERE reportuniq='"
            . "$reportuniq'";
        $row = find_one($sql, $db);
        if (!($row)) {
            echo "Unable to find source report.";
        } else {
            $name = $row['reportname'];
            switch ($act) {
                case constActCopyReport:
                    $name = 'Copy of ' . $row['reportname'];
                    break;
                case constActCloneReport:
                    $name = $row['reportname'];
                    break;
                case constActCloneRunReport:
                    $name = $row['reportname'];
                    break;
            }
            if (
                PHP_REPF_CopyReport(
                    CUR,
                    $copyuniq,
                    $name,
                    $reportuniq,
                    $user['username'],
                    constReptOutputFormatHTML
                )
                != constAppNoErr
            ) {
                REPT_PrintError();
                $reportuniq = '';
            }
        }
    }
}

function REPH_CreateOrCopySection(
    $act,
    &$sectionuniq,
    &$copyuniq,
    $user,
    $section,
    $db
) {
    $type = constSectionTypeMUM;
    $sectionname = constMUMSectionTitle;
    switch ($section) {
        case constSectionEvent:
            $type = constSectionTypeEvent;
            $sectionname = constEventSectionTitle;
            break;
        case constSectionAsset:
            $type = constSectionTypeAsset;
            $sectionname = constAssetSectionTitle;
            break;
        case constSectionMUMReport:
            $type = constSectionTypeMUM;
            $sectionname = constMUMSectionTitle;
            break;
        case constSectionExecSummary:
            $type = constSectionTypeExecSummary;
            $sectionname = constExecSumSectionTitle;
            break;
    }
    if ($act == constActCreateSection) {
        $sectionuniq = NULL;
        if (PHP_REPF_CreateSection(
            CUR,
            $sectionuniq,
            $sectionname,
            $user['username'],
            TRUE,
            $type,
            FALSE,
            FALSE,
            FALSE,
            TRUE
        ) != constAppNoErr) {
            REPT_PrintError();
            $sectionuniq = '';
        }
        $copyuniq = $sectionuniq;
    } else {
        $sql = "SELECT sectionname FROM Section WHERE sectionuniq='"
            . "$sectionuniq'";
        $row = find_one($sql, $db);
        if (!($row)) {
            echo "Unable to find source section.";
        } else {
            switch ($act) {
                case constActCopySection:
                    $name = 'Copy of ' . $row['sectionname'];
                    break;
                case constActCloneSection:
                    $name = $row['sectionname'];
                    break;
            }
            if (PHP_REPF_CopySection(
                CUR,
                $copyuniq,
                $name,
                $sectionuniq,
                $user['username']
            ) != constAppNoErr) {
                REPT_PrintError();
                $sectionuniq = '';
            }
        }
    }
}

function REPH_EnableReport($reportuniq, $confirm, $user, $section, $db)
{
    if ($confirm) {
        REPH_SetReportState(
            $reportuniq,
            constReportEnabled,
            $user,
            $section,
            $db
        );
        echo 'Report enabled.';
    } else {
        echo 'Are you sure you want to enable this report?<br>';
        echo '<a href="report.php?act=' . constActEnableReport
            . '&confirm=1&reportuniq=' . $reportuniq
            . '">[Yes]</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
            . '<a href="report.php">[No]</a><br>';
    }
}

function REPH_DisableReport($reportuniq, $confirm, $user, $section, $db)
{
    if ($confirm) {
        REPH_SetReportState(
            $reportuniq,
            constReportDisabled,
            $user,
            $section,
            $db
        );
        echo 'Report disabled.';
    } else {
        $message = '';
        if (PHP_REPF_CanDisableReport(
            CUR,
            $disable,
            $message,
            $reportuniq
        ) != constAppNoErr) {
            REPT_PrintError();
            return;
        }
        if ($disable) {
            echo 'Are you sure you want to disable this report?<br>';
            echo '<a href="report.php?act=' . constActDisableReport
                . '&confirm=1&reportuniq=' . $reportuniq
                . '">[Yes]</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                . '<a href="report.php">[No]</a><br>';
        } else {
            echo $message;
        }
    }
}

function REPH_SetReportState($reportuniq, $state, $user, $section, $db)
{
    if (!$user['priv_report']) {
        if (REPH_IsReportGlobal($reportuniq, $db)) {
            /* This is a global report, but the user has no global rights -
                    create a local copy */
            $act = constActCloneReport;
            REPH_CreateOrCopyReport(
                $act,
                $reportuniq,
                $copyuniq,
                $user,
                $section,
                $db
            );
            $reportuniq = $copyuniq;
        }
    }

    $sql = "UPDATE Report SET state=$state, modified=" . time()
        . " WHERE reportuniq='$reportuniq'";
    redcommand($sql, $db);
}

function REPH_SelectSchedule($reportuniq, &$scheduniq)
{
    $db = db_select($GLOBALS['PREFIX'] . 'schedule');
    $msg = 'Please choose a schedule to use for this action:<ul>';
    $count = 0;
    $sql = 'SELECT Schedules.scheduniq, name FROM ScheduleMap LEFT JOIN '
        . 'Schedules '
        . 'ON (ScheduleMap.scheduniq=Schedules.scheduniq) WHERE '
        . 'objecttype=' . constObjectTypeReport . ' AND objectuniq=\''
        . $reportuniq . '\'';
    $set = find_many($sql, $db);
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $scheduniq = $row['scheduniq'];
            $msg .= '<li><a href="report.php?act=' . constActRun
                . "&reportuniq=$reportuniq&scheduniq=$scheduniq"
                . '&confirm=1" target="_blank">' . $row['name'] . '</a>';
            $count = $count + 1;
        }
    }

    $msg .= '</ul>';

    if ($count > 1) {
        echo $msg;
        return TRUE;
    }
    return FALSE;
}

function REPH_IsReportGlobal($reportuniq, $db)
{
    $db = db_select($GLOBALS['PREFIX'] . 'report');
    $sql = "SELECT global FROM Report WHERE reportuniq='$reportuniq'";
    $row = find_one($sql, $db);
    if ($row) {
        return $row['global'];
    }
    return false;
}

function REPH_GetReportState($reportuniq, $db)
{
    $db = db_select($GLOBALS['PREFIX'] . 'report');
    $sql = "SELECT state FROM Report WHERE reportuniq='$reportuniq'";
    $row = find_one($sql, $db);
    if ($row) {
        return $row['state'];
    }
    return constReportDisabled;
}

function REPH_GetReportOwner($reportuniq, $db)
{
    $db = db_select($GLOBALS['PREFIX'] . 'report');
    $sql = "SELECT username FROM Report WHERE reportuniq='$reportuniq'";
    $row = find_one($sql, $db);
    if ($row) {
        return $row['username'];
    }
    return 'hfn';
}

function REPH_GetSectionOwner($sectionuniq, $db)
{
    $db = db_select($GLOBALS['PREFIX'] . 'report');
    $sql = "SELECT username FROM Section WHERE sectionuniq='$sectionuniq'";
    $row = find_one($sql, $db);
    if ($row) {
        return $row['username'];
    }
    return 'hfn';
}

/* Perform authentication */
$db       = db_connect();
$authuser = process_login($db);
$comp = component_installed();

$user     = user_data($authuser, $db);
$admin = @($user['priv_admin']) ?   1  : 0;

$act = get_integer('act', constActListReports);
$oldact = get_integer('oldact', constActListReports);
$section = get_integer('section', constSectionGeneric);
$sectionuniq = get_string('sectionuniq', '');
$reportuniq = get_string('reportuniq', '');
$copyuniq = get_string('copyuniq', '');
$isparent = get_integer('isparent', 0);
$confirm = get_integer('confirm', 0);
$scheduniq = get_string('scheduniq', '');

$db = db_select($GLOBALS['PREFIX'] . 'report');

/* Increase available memory to interactively run large reports */
$mem = server_def('max_php_mem_mb', '256', $db);
ini_set('memory_limit', $mem . 'M');

$title = REPH_GetTitle(
    $act,
    $oldact,
    $section,
    $reportuniq,
    $sectionuniq,
    $db
);

$a[] = html_link('report.php?section=' . constSectionEvent . '&act='
    . constActListSections, 'event sections');
$a[] = html_link('report.php?section=' . constSectionExecSummary . '&act='
    . constActListSections, 'summary sections');
$a[] = html_link('report.php?section=' . constSectionMUMReport . '&act='
    . constActListSections, 'MUM sections');
$nav = join(' | ', $a) . '<br><br>';

echo custom_html_header(
    $title,
    $comp,
    $authuser,
    $nav,
    0,
    0,
    0,
    '<LINK href="control.css" rel="stylesheet" type="text/css"> '
        . '<script type="text/javascript" language="JavaScript" src="'
        . 'control.js"></script>',
    $db
);

REPT_MakeMUMSectionArray($db, $authuser);

REPH_PrintLinks($section);

switch ($act) {
    case constActViewReport:
        $cmd = "report.php?reportuniq=$reportuniq&act=";
        $a = array();
        $a[] = html_link($cmd . constActViewReport, 'details');
        $state = REPH_GetReportState($reportuniq, $db);
        if ($state == constReportEnabled) {
            $a[] = html_link($cmd . constActDisableReport, 'disable');
        } else {
            $a[] = html_link($cmd . constActEnableReport, 'enable');
        }
        $a[] = html_link($cmd . constActEditReport, 'edit');
        if (strcmp($user['username'], REPH_GetReportOwner($reportuniq, $db)) == 0) {
            $a[] = html_link($cmd . constActDeleteReport, 'delete');
        }
        $a[] = html_link($cmd . constActCopyReport, 'copy');
        $a[] = html_link($cmd . constActRun, 'run');
        echo jumplist($a);
        break;
    case constActViewSection:
        $cmd = "report.php?sectionuniq=$sectionuniq&act=";
        $a = array();
        $a[] = html_link($cmd . constActViewSection, 'details');
        $a[] = html_link($cmd . constActEditSection, 'edit');
        if (
            strcmp($user['username'], REPH_GetSectionOwner($reportuniq, $db))
            == 0
        ) {
            $a[] = html_link($cmd . constActDeleteSection, 'delete');
        }
        $a[] = html_link($cmd . constActCopySection, 'copy');
        echo jumplist($a);
        break;
}

switch ($section) {
    case constSectionGeneric:
    case constSectionEvent:
    case constSectionAsset:
    case constSectionMUMReport:
        REPH_PrintHelpText($act);
        break;
    case constSectionExecSummary:
        break;
    default:
        logs::log(__FILE__, __LINE__, "report.php: unknown section $section in main", 0);
        break;
}

if ($act == constActEditReport) {
    $copyuniq = $reportuniq;
}

switch ($act) {
    case constActCreateReport:
    case constActCopyReport:
        REPH_CreateOrCopyReport(
            $act,
            $reportuniq,
            $copyuniq,
            $user,
            $section,
            $db
        );
        /* fall through */
    case constActEditReport:
        if ($act == constActEditReport) {
            if (strcmp(
                $user['username'],
                REPH_GetReportOwner($reportuniq, $db)
            ) != 0) {
                /* User does not own the report - create a local copy */
                $act = constActCloneReport;
                REPH_CreateOrCopyReport(
                    $act,
                    $reportuniq,
                    $copyuniq,
                    $user,
                    $section,
                    $db
                );
            }
        }
        $html = '';
        if (strcmp($reportuniq, '') != 0) {
            if (PHP_REPF_GenerateHTMLConfig(
                CUR,
                $html,
                $copyuniq,
                $user['username'],
                'report.php?act=' . constActSaveReport
                    . '&section=' . $section . '&oldact=' . $act . '&reportuniq='
                    . $reportuniq . '&copyuniq=' . $copyuniq,
                constRepfConfigTypeRept,
                $act == constActEditReport ?
                    constFormEdit : constFormCreate
            ) != constAppNoErr) {
                REPT_PrintError();
            } else {
                echo $html;
                if (($act == constActCreateReport)
                    || ($act == constActCopyReport)
                    || ($act == constActCloneReport)
                ) {
                    PHP_REPF_DeleteReport(CUR, $copyuniq);
                }
            }
        }
        break;
    case constActSaveReport:
        if (($oldact == constActCreateReport) || ($oldact == constActCopyReport)
            || ($oldact == constActCloneReport)
        ) {
            REPH_CreateOrCopyReport(
                $oldact,
                $reportuniq,
                $copyuniq,
                $user,
                $section,
                $db
            );
        }
        $err = PHP_REPF_SaveReportConfig(
            CUR,
            $html,
            $GLOBALS['HTTP_RAW_POST_DATA'],
            server_var('QUERY_STRING'),
            $user['username'],
            $oldact == constActEditReport ? FALSE : TRUE,
            constRepfConfigTypeRept
        );
        switch ($err) {
            case constAppNoErr:
                echo $html;
                break;
            case constErrUniqueName:
                echo $html;
                if (($oldact == constActCreateReport)
                    || ($oldact == constActCopyReport)
                    || ($oldact == constActCloneReport)
                ) {
                    PHP_REPF_DeleteSection(CUR, $copyuniq);
                }
                break;
            default:
                REPT_PrintError();
                break;
        }
        break;
    case constActCreateSection:
    case constActCopySection:
        REPH_CreateOrCopySection(
            $act,
            $sectionuniq,
            $copyuniq,
            $user,
            $section,
            $db
        );
        /* fall through */
    case constActEditSection:
        if ($act == constActEditSection) {
            if (strcmp(
                $user['username'],
                REPH_GetSectionOwner($sectionuniq, $db)
            ) != 0) {
                /* The user does not own this section - create a local copy */
                $act = constActCloneSection;
                REPH_CreateOrCopySection(
                    $act,
                    $sectionuniq,
                    $copyuniq,
                    $user,
                    $section,
                    $db
                );
            }
        }
        $html = '';
        if (strcmp($sectionuniq, '') != 0) {
            if (strcmp($copyuniq, '') == 0) {
                $copyuniq = $sectionuniq;
            }
            if (
                PHP_REPF_GenerateHTMLConfig(
                    CUR,
                    $html,
                    $copyuniq,
                    $user['username'],
                    'report.php?act=' . constActSaveSection
                        . '&section=' . $section . '&isparent=' . $isparent
                        . '&sectionuniq=' . $sectionuniq . '&oldact=' . $act
                        . '&copyuniq=' . $copyuniq,
                    constRepfConfigTypeSection,
                    $act == constActEditSection ? constFormEdit : constFormCreate
                )
                != constAppNoErr
            ) {
                REPT_PrintError();
            } else {
                echo $html;
                if (($act == constActCreateSection)
                    || ($act == constActCopySection)
                    || ($act == constActCloneSection)
                ) {
                    PHP_REPF_DeleteSection(CUR, $copyuniq);
                }
            }
        }
        break;
    case constActSaveSection:
        if (($oldact == constActCreateSection) || ($oldact == constActCopySection)
            || ($oldact == constActCloneSection)
        ) {
            REPH_CreateOrCopySection(
                $oldact,
                $sectionuniq,
                $copyuniq,
                $user,
                $section,
                $db
            );
        }
        $err = PHP_REPF_SaveReportConfig(
            CUR,
            $html,
            $GLOBALS['HTTP_RAW_POST_DATA'],
            server_var('QUERY_STRING'),
            $user['username'],
            $oldact == constActEditSection ? FALSE : TRUE,
            constRepfConfigTypeSection
        );
        switch ($err) {
            case constAppNoErr:
                echo $html;
                REPH_HandleParent($isparent, $sectionuniq);
                break;
            case constErrUniqueName:
                echo $html;
                if (($oldact == constActCreateSection)
                    || ($oldact == constActCopySection)
                    || ($oldact == constActCloneSection)
                ) {
                    PHP_REPF_DeleteSection(CUR, $copyuniq);
                }
                break;
            default:
                REPT_PrintError();
                break;
        }
        break;
    case constActListSections:
        REPF_ListReports(
            $user['username'],
            $section,
            constActListSections,
            "report.php?section=$section&act="
        );
        break;
    case constActRun:
        $selectSchedule = TRUE;
        $oldact = constActRun;
        if (strcmp($scheduniq, '') == 0) {
            $selectSchedule = REPH_SelectSchedule($reportuniq, $scheduniq);
        }
        if ($confirm) {
            if (strcmp(
                $user['username'],
                REPH_GetReportOwner($reportuniq, $db)
            ) != 0) {
                $oldact = constActCloneRunReport;
                REPH_CreateOrCopyReport(
                    constActCloneRunReport,
                    $reportuniq,
                    $copyuniq,
                    $user,
                    $section,
                    $db
                );
                if (strcmp($reportuniq, '') != 0) {
                    $reportuniq = $copyuniq;
                }
            }
            if (strcmp($reportuniq, '') != 0) {
                if (
                    PHP_REPF_RunInteractiveReport(
                        CUR,
                        $html,
                        $reportuniq,
                        $user['username'],
                        constRepfRunContextImmed,
                        $scheduniq
                    )
                    != constAppNoErr
                ) {
                    REPT_PrintError();
                } else {
                    echo $html;
                }
                if ($oldact == constActCloneRunReport) {
                    PHP_REPF_DeleteReport(CUR, $reportuniq);
                }
            }
        } else {
            echo '<p>Depending on the time period, the report may take some '
                . 'time to run.<p>';
            if (!$selectSchedule) {
                echo 'Are you sure?<p>';
                echo '<a href="report.php?act=' . constActRun
                    . '&confirm=1&reportuniq=' . $reportuniq
                    . '" target="_blank">[Yes]</a>&nbsp;&nbsp;&nbsp;&nbsp;'
                    . '&nbsp;<a href="report.php">[No]</a><br>';
            }
        }
        break;
    case constActDeleteReport:
        if ($confirm) {
            if (PHP_REPF_DeleteReport(CUR, $reportuniq) != constAppNoErr) {
                REPT_PrintError();
            } else {
                echo 'Report deleted.';
            }
        } else {
            echo "Delete report?<p>[<a href=\"report.php?act=$act&confirm=1"
                . "&reportuniq=$reportuniq\">Yes</a>]&nbsp;[<a href=\""
                . "report.php\">No</a>]";
        }
        break;
    case constActDeleteSection:
        if ($confirm) {
            if (PHP_REPF_DeleteSection(CUR, $sectionuniq) != constAppNoErr) {
                REPT_PrintError();
            } else {
                echo 'Section deleted.';
            }
        } else {
            if (PHP_REPF_CheckDeleteSection(
                CUR,
                $canDelete,
                $items,
                $sectionuniq
            ) != constAppNoErr) {
                REPT_PrintError();
            } else {
                if ($canDelete) {
                    echo "Delete section?<p>[<a href=\"report.php?act=$act&"
                        . "confirm=1&sectionuniq=$sectionuniq\">Yes</a>]&nbsp;"
                        . '[<a href="report.php?act=' . constActListSections
                        . '">No</a>]';
                } else {
                    echo 'You cannot delete this section because it is in '
                        . 'use by the following: <ul>' . $items . '</ul>';
                }
            }
        }
        break;
    case constActGenCreateSection:
        echo '<input type="button" value="Event Section" '
            . 'onclick="window.location.href=\'report.php?act='
            . constActCreateSection . '&isparent=' . $isparent . '&section='
            . constSectionEvent . '\';">';
        echo '<br><br>';
        echo '<input type="button" value="Summary Section" '
            . 'onclick="window.location.href=\'report.php?act='
            . constActCreateSection . '&isparent=' . $isparent . '&section='
            . constSectionExecSummary . '\';">';
        echo '<br><br>';
        echo '<input type="button" value="Microsoft Update Management Section"'
            . ' onclick="window.location.href=\'report.php?act='
            . constActCreateSection . '&isparent=' . $isparent . '&section='
            . constSectionMUMReport . '\';">';
        break;
    case constActEnableReport:
        REPH_EnableReport($reportuniq, $confirm, $user, $section, $db);
        break;
    case constActDisableReport:
        REPH_DisableReport($reportuniq, $confirm, $user, $section, $db);
        break;
    case constActPreviewReport:
        if (PHP_REPF_RunInteractiveReport(
            CUR,
            $html,
            $reportuniq,
            $user['username'],
            constRepfRunContextPreview,
            ''
        ) != constAppNoErr) {
            REPT_PrintError();
        } else {
            echo $html;
        }
        break;
    case constActViewReport:
        /* Setup javascript to use the view disposition, since reports use the
            special lists */
        echo '<script type="text/javascript" language="JavaScript">'
            . 'controlDisposition=constJavaListDispositionView</script>';
        if (PHP_REPF_GenerateHTMLConfig(
            CUR,
            $html,
            $reportuniq,
            $user['username'],
            'report.php?act=' . constActListReports,
            constRepfConfigTypeRept,
            constFormView
        ) != constAppNoErr) {
            REPT_PrintError();
        } else {
            echo $html;
        }
        break;
    case constActViewSection:
        echo '<script type="text/javascript" language="JavaScript">'
            . 'controlDisposition=constJavaListDispositionView</script>';
        if (PHP_REPF_GenerateHTMLConfig(
            CUR,
            $html,
            $sectionuniq,
            $user['username'],
            'report.php?act=' . constActListReports,
            constRepfConfigTypeSection,
            constFormView
        ) != constAppNoErr) {
            REPT_PrintError();
        } else {
            echo $html;
        }
        break;
    case constActLegacy:
        echo 'In order to access legacy event or asset reports, please click '
            . 'on the corresponding link below<br><p>'
            . '- "<a href="../event/report.php">Legacy event reports'
            . '</a>"<br>'
            . '- "<a href="../asset/report.php">Legacy asset reports'
            . '</a>"<br><br>';
        break;
    case constActListReports:
    default:
        REPF_ListReports(
            $user['username'],
            $section,
            constActListReports,
            'report.php?act='
        );
        break;
}

REPH_PrintLinks($section);

echo head_standard_html_footer($authuser, $db);
