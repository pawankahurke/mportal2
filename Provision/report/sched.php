<?php

/* sched.php - scheduling UI handler */

/*
Revision history:

Date        Who     What
----        ---     ----
24-Feb-07   BTE     Original creation.
14-Mar-07   BTE     Lots of new functionality.
19-Mar-07   BTE     Minor text changes from Alex.
03-Apr-07   BTE     Many bugfixes.
15-Apr-07   BTE     A few bugfixes.
03-May-07   BTE     Added global rights logic.
04-May-07   BTE     Added support for read-only forms.
09-May-07   BTE     Added view-only links.
04-Jun-07   BTE     Fixed an undefined index.
22-Jun-07   BTE     Bug 4156: Reports small changes and questions - #2 (minor
                    text things).  Bug 4154: Resolve issues from "Re: Global,
                    admin etc." email.
27-Jun-07   BTE     Fixed a bug in the title generation.
08-Jul-07   BTE     Bug 4226: Deleting management items need to verify they are
                    not in use.
04-Sep-07   BTE     Fixed name constraint messages.
04-Oct-07   BTE     Increased the size of the click here text.

*/

include_once ( '../lib/l-cnst.php'  );
include_once ( '../lib/l-db.php'    );
include_once ( '../lib/l-errs.php'  );
include_once ( '../lib/l-util.php'  );
include_once ( '../lib/l-head2.php' );
include_once ( '../lib/l-serv.php'  );
include_once ( '../lib/l-sql.php'   );
include_once ( '../lib/l-user.php'  );
include_once ( '../lib/l-repf.php'  );
include_once ( '../lib/l-rept.php'  );
include_once ( '../lib/l-jump.php'  );
include_once ( '../lib/l-rcmd.php'  );
include_once ( '../lib/l-gsql.php'  );
include_once ( '../patch/local.php' );

    /* act constants for this page */
    /* define('constActListSchedules',     0); - defined in l-repf.php */
    define('constActCreateSchedule',    1);
    define('constActEditSchedule',      2);
    define('constActDeleteSchedule',    3);
    define('constActSaveSchedule',      4);
    define('constActCopySchedule',      5);
    define('constActCloneSchedule',     6);     /* Not an actual config page */
    define('constActViewSchedule',      7);


    /* SCED_GetTitle

        Computes the title given the current action $act.
    */
    function SCED_GetTitle($act, $oldact, $scheduniq, $db)
    {
        $name = '';
        if($scheduniq)
        {
            $sql = "SELECT name FROM Schedules WHERE scheduniq='$scheduniq'";
            $row = find_one($sql, $db);
            if($row)
            {
                $name = $row['name'];
            }
        }
        if(!($name))
        {
            $name = get_string(constSchedNameConfigUniq, '');
        }
        switch($act)
        {
        case constActCreateSchedule:
            $title = "Add a Schedule";
            break;
        case constActEditSchedule:
            $title = "Edit Schedule \"$name\"";
            break;
        case constActSaveSchedule:
            $title = "Schedule \"$name\"";
            if($oldact==constActCreateSchedule)
            {
                $title .= ' Added - Confirmation';
            }
            else
            {
                $title .= ' Edited - Confirmation';
            }
            break;
        case constActDeleteSchedule:
            $title = "Delete Schedule \"$name\"";
            break;
        case constActCopySchedule:
            $title = 'Copy a Schedule';
            break;
        case constActViewSchedule:
            $title = "View Schedule \"$name\"";
            break;
        case constActListSchedules:
        default:
            $title = 'Schedules';
            break;
        }

        return $title;
    }


    /* SCED_PrintLinks

        Prints navigation links for the scheduling interface.
    */
    function SCED_PrintLinks()
    {
        $cmd = 'sched.php?act=';
        $a = array();
        $a[] = html_link($cmd . constActListSchedules, 'schedules');
        $a[] = html_link($cmd . constActCreateSchedule, 'add schedule');

        echo jumplist($a);
    }

    function SCED_HandleParent($isparent, $scheduniq)
    {
        $db = db_select($GLOBALS['PREFIX'].'schedule');
        $sql = "SELECT QUOTE(name) AS name FROM Schedules WHERE "
            . "scheduniq='$scheduniq'";
        $row = find_one($sql, $db);
        switch($isparent)
        {
        case 1:
            if($row)
            {
                $name = $row['name'];
                echo "<p>" . constFontSizeClickHere
                    . "Click <a href=\"#\" onclick=\""
                    . "addScheduleButton('$scheduniq',$name);"
                    . "window.close();\">here</a>"
                    . " to add this new schedule to the report you are "
                    . "creating.</font>";
            }
            break;
        case 2:
            if($row)
            {
                $name = $row['name'];
                echo '<p>' . constFontSizeClickHere
                    . 'Click <a href="#" onclick="renameSchedule(\''
                    . "$scheduniq',$name);window.close();\">here</a> "
                    . 'to return to the report definition page.</font>';
            }
            break;
        }
    }

    function SCED_HandleCreateOrCopy($act, &$scheduniq, &$copyuniq, $user, $db)
    {
        if($act==constActCreateSchedule)
        {
            $name = 'Schedule_' . date('n/d/y_H:i:s', time());
            if(PHP_SCED_CreateSchedule(CUR, $scheduniq, $name, TRUE,
                $user['username'], constScheduleBlank)!=constAppNoErr)
            {
                REPT_PrintError();
                $scheduniq = '';
            }
            $copyuniq = $scheduniq;
        }
        else
        {
            $sql = "SELECT name FROM Schedules WHERE scheduniq='$scheduniq'";
            $row = find_one($sql, $db);
            if($row)
            {
                switch($act)
                {
                case constActCopySchedule:
                    $name = 'Copy of ' . $row['name'] . ' '
                        . date('n/d/y_H:i:s', time());
                    break;
                case constActCloneSchedule:
                    $name = $row['name'];
                    break;
                }
                if(PHP_SCED_CopySchedule(CUR, $copyuniq, $name,
                    $user['username'], $scheduniq)!=constAppNoErr)
                {
                    REPT_PrintError();
                    $copyuniq = '';
                }
            }
            else
            {
                echo "Schedule $scheduniq not found.";
                $copyuniq = '';
            }
        }
    }

    function SCED_IsScheduleGlobal($scheduniq, $db)
    {
        $db = db_select($GLOBALS['PREFIX'].'schedule');
        $sql = "SELECT global FROM Schedules WHERE scheduniq='$scheduniq'";
        $row = find_one($sql, $db);
        if($row)
        {
            return $row['global'];
        }
        return false;
    }

    function SCED_GetScheduleOwner($scheduniq, $db)
    {
        $db = db_select($GLOBALS['PREFIX'].'schedule');
        $sql = "SELECT username FROM Schedules WHERE scheduniq='$scheduniq'";
        $row = find_one($sql, $db);
        if($row)
        {
            return $row['username'];
        }
        return 'hfn';
    }

    /* Perform authentication */
    $db       = db_connect();
    $authuser = process_login($db);
    $comp = component_installed();

    $user     = user_data($authuser,$db);
    $admin = @ ($user['priv_admin'])?   1  : 0;

    $act = get_integer('act', constActListSchedules);
    $oldact = get_integer('oldact', constActEditSchedule);
    $scheduniq = get_string('scheduniq', '');
    $copyuniq = get_string('copyuniq', '');
    $isparent = get_integer('isparent', 0);
    $confirm = get_integer('confirm', 0);

    $db = db_select($GLOBALS['PREFIX'].'schedule');
    $title = SCED_GetTitle($act, $oldact, $scheduniq, $db);

    echo custom_html_header($title,$comp,$authuser,'',0,0,0,
        '<LINK href="control.css" rel="stylesheet" type="text/css"> '
        . '<script type="text/javascript" language="JavaScript" src="'
        . 'control.js"></script>', $db);

    SCED_PrintLinks();

    if($act==constActViewSchedule)
    {
        $cmd = "sched.php?scheduniq=$scheduniq&act=";
        $a = array();
        $a[] = html_link($cmd . constActViewSchedule, 'details');
        $a[] = html_link($cmd . constActEditSchedule, 'edit');
        if(strcmp($user['username'], SCED_GetScheduleOwner($scheduniq, $db))
            ==0)
        {
            $a[] = html_link($cmd . constActDeleteSchedule, 'delete');
        }
        $a[] = html_link($cmd . constActCopySchedule, 'copy');
        echo jumplist($a);
    }

    if($act==constActEditSchedule)
    {
        $copyuniq = $scheduniq;
    }

    switch($act)
    {
    case constActCreateSchedule:
    case constActCopySchedule:
        SCED_HandleCreateOrCopy($act, $scheduniq, $copyuniq, $user, $db);
        /* fall through */
    case constActEditSchedule:
        if($act==constActEditSchedule)
        {
            if(strcmp($user['username'],
                SCED_GetScheduleOwner($scheduniq, $db))!=0)
            {
                /* User does not own the schedule - create a local copy */
                $act = constActCloneSchedule;
                SCED_HandleCreateOrCopy($act,
                    $scheduniq, $copyuniq, $user, $db);
            }
        }
        if(($scheduniq!='') && ($copyuniq!=''))
        {
            if(PHP_SCED_GenerateHTMLControl(CUR, $html, $copyuniq,
                'sched.php?act=' . constActSaveSchedule . '&scheduniq='
                . $scheduniq . '&oldact=' . $act . '&isparent=' . $isparent
                . '&copyuniq=' . $copyuniq, $act==constActEditSchedule ?
                constSchedFormEdit : constSchedFormCreate)!=constAppNoErr)
            {
                REPT_PrintError();
            }
            else
            {
                echo $html;
                /* The schedule is not really supposed to exist until someone
                    actually "saves" it, so delete it */
                if(($act==constActCreateSchedule)
                    || ($act==constActCopySchedule)
                    || ($act==constActCloneSchedule))
                {
                    PHP_SCED_DeleteSchedule(CUR, $copyuniq);
                }
            }
        }
        break;
    case constActSaveSchedule:
        if($scheduniq!='')
        {
            if(($oldact==constActCreateSchedule) ||
                ($oldact==constActCopySchedule) ||
                ($oldact==constActCloneSchedule))
            {
                SCED_HandleCreateOrCopy($oldact, $scheduniq, $copyuniq, $user,
                    $db);
            }
            if(($scheduniq!='') && ($copyuniq!=''))
            {
                $err = PHP_SCED_SaveScheduleConfig(CUR, $html, $copyuniq,
                    $GLOBALS['HTTP_RAW_POST_DATA'], $user['username'],
                    $oldact==constActEditSchedule ? FALSE : TRUE);
                switch($err)
                {
                case constAppNoErr:
                    echo $html;
                    SCED_HandleParent($isparent, $copyuniq);
                    break;
                case constErrUniqueName:
                    echo $html;
                    if(($oldact==constActCreateSchedule)
                        || ($oldact==constActCopySchedule)
                        || ($oldact==constActCloneSchedule))
                    {
                        PHP_SCED_DeleteSchedule(CUR, $copyuniq);
                    }
                    break;
                default:
                    REPT_PrintError();
                    break;
                }
            }
            else
            {
                REPT_PrintError();
            }
        }
        break;
    case constActDeleteSchedule:
        if($confirm)
        {
            if(PHP_SCED_DeleteSchedule(CUR, $scheduniq)!=constAppNoErr)
            {
                REPT_PrintError();
            }
            else
            {
                echo 'Schedule deleted.';
            }
        }
        else
        {
            /* Verify this schedule is not in use by anything else.  */
            if(PHP_SCED_CheckDeleteSchedule(CUR, $canDelete, $items,
                $scheduniq)!=constAppNoErr)
            {
                REPT_PrintError();
            }
            else
            {
                if($canDelete)
                {
                    echo "Delete schedule?<p>[<a href=\"sched.php?act=$act&"
                        . "confirm=1&scheduniq=$scheduniq\">Yes</a>]&nbsp;["
                        . '<a href="sched.php">No</a>]';
                }
                else
                {
                    echo 'You cannot delete this schedule because it is in '
                        . 'use by the following: <ul>' . $items . '</ul>';
                }
            }
        }
        break;
    case constActViewSchedule:
        if(PHP_SCED_GenerateHTMLControl(CUR, $html, $scheduniq,
            'sched.php?act=' . constActListSchedules, constSchedFormView)
            !=constAppNoErr)
        {
            REPT_PrintError();
        }
        else
        {
            echo $html;
        }
        break;
    case constActListSchedules:
    default:
        REPF_ListReports($user['username'], constSectionSchedule,
            constActListSchedules, 'sched.php?act=');
        break;
    }

    SCED_PrintLinks();

    echo head_standard_html_footer($authuser,$db);
?>
