<?php

/*
Revision history:

Date        Who     What
----        ---     ----
27-Dec-07   BTE     Original creation.
04-Jan-08   BTE     Bug 4379: Autotask text changes.
07-Jan-08   BTE     Text changes from Alex.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-head.php');
include('../lib/l-user.php');
include('../lib/l-auto.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-form.php');
include('../lib/l-cnst.php');
include('../lib/l-errs.php');

/* Constants */
define('constActNone',      0);
define('constActForm',      1);
define('constActForm2',     2);
define('constActFormSave',  3);

define('constFormServiceWSDL',      'formwsdl');
define('constFormUsername',         'formuser');
define('constFormPassword',         'formpass');
define('constFormAction',           'formaction');
define('constFormAccountSel',       'formacctsel');
define('constFormPrioritySel',      'formpriosel');
define('constFormQueueSel',         'formqueuesel');
define('constFormWeeks',            'formweeks');
define('constFormDays',             'formdays');
define('constFormHours',            'formhours');
define('constFormMinutes',          'formminutes');
define('constFormCreateDisp',       'formcreatedisp');
define('constFormCreateClosed',     'formcreateclosed');
define('constFormSitePrefix',       'sitesel');
define('constFormStatNew',          'formstatnew');
define('constFormStatComp',         'formstatcomp');
define('constFormNoteTypeSel',      'formnotetype');
define('constFormPublishSel',       'formpublish');


function DisplayForm($settings, $default, $db)
{
    echo '<form method="post" action="autotask.php?act=' . constActForm2;
    if ($default) {
        echo '&default=1';
    }
    echo '">';
    echo '<i>Enter your Autotask server URL and login credentials '
        . 'below, then click on the "Connect" button to proceed to '
        . 'the "Autotask Settings" page.</i><p>';

    echo '<table border="0">';
    echo '<tr><td>Service domain name:</td>';
    echo '<td><input type="text" size="30" name="' . constFormServiceWSDL
        . '" value="' . htmlentities($settings['middleurl'])
        . '">&nbsp;<i>Example: www.autotask.net</i></td></tr>';

    echo '<tr><td>Username:</td>';
    echo '<td><input type="text" size="30" name="' . constFormUsername
        . '" value="' . htmlentities($settings['autouser'])
        . '"></td></tr>';

    $passhelp = '';
    if (strlen($settings['autopass']) > 0) {
        $passhelp = '&nbsp;<i>Leave blank to use stored password.</i>';
    }
    echo '<tr><td>Password:</td>';
    echo '<td><input type="password" size="30" name="' . constFormPassword
        . '">' . $passhelp . '</td></tr></table><p>';

    echo '<input type="submit" name="' . constFormAction
        . '" value="Connect"><p>';
}

function DisplaySettingsForm($settings, $default, $db)
{
    $quser = safe_addslashes($settings['username']);
    $accounts = array();
    $picks = array();
    $notes = array();
    try {
        $client = AUTO_OpenSoap(
            $settings['beginurl']
                . $settings['middleurl'] .  $settings['endurl'],
            $settings['autouser'],
            $settings['autopass']
        );
        if ($client) {
            $accounts = AUTO_GetAutotaskAccounts($client);
            $picks = AUTO_GetTicketPicklists($client);
            $notes = AUTO_GetTicketNotePicklists($client);
        }
    } catch (SoapFault $e) {
        echo "Error contacting Autotask server: (faultcode: "
            . "{$e->faultcode}, faultstring: {$e->faultstring})";
        return;
    }
    if (!($client)) {
        echo 'Error contacting Autotask server, check your settings.';
        return;
    }
    if (!($picks)) {
        echo 'Error retrieving values from Autotask server.';
        return;
    }

    if (($picks[constPicksPriorities])
        && ($picks[constPicksQueues])
        && ($picks[constPicksUseNew])
        && ($picks[constPicksUseComplete])
        && ($notes[constPicksNoteType])
        && ($notes[constPicksPublish])
    ) {
        /* We have all the values we need, proceed */
    } else {
        echo 'Error retrieving some values from Autotask server.';
        return;
    }

    echo '<form method="post" action="autotask.php?act=' . constActFormSave;
    if ($default) {
        echo '&default=1';
    }
    echo '">';
    echo '<b>Ticket Settings</b><p>';

    if ($default) {
        $sql = 'SELECT descr FROM  ' . $GLOBALS['PREFIX'] . 'autotask.AutotaskAccount WHERE '
            . 'AutotaskAccount.linktype=' . constAutoSiteTypeDefault;
        $row = find_one($sql, $db);
        echo 'Default Autotask account for all ASI sites:&nbsp;';
        echo AUTO_MakeSelect(
            $accounts,
            constFormSitePrefix,
            htmlentities('---Select a Value---'),
            $row ? $row['descr'] : null,
            'No Autotask accounts found.',
            false,
            null
        );
        echo '<p>';
    } else {
        /* The user may assign Autotask account maps to all sites he/she
                has access to */
        $sql = 'SELECT customer, descr, id FROM ' . $GLOBALS['PREFIX'] . 'core.Customers '
            . 'LEFT JOIN  ' . $GLOBALS['PREFIX'] . 'autotask.'
            . ' AutotaskAccount ON (Customers.customer='
            . 'AutotaskAccount.site '
            . 'AND AutotaskAccount.linktype=' . constAutoSiteTypeLink
            . ') '
            . " WHERE Customers.username='$quser' ORDER BY customer";
        $sites = find_many($sql, $db);

        if (!($sites)) {
            echo 'You do not have access to any sites.';
        } else {
            echo '<table border="0"><tr><th>ASI Site</th><th>Autotask '
                . 'Account</th></tr>';
            reset($sites);
            foreach ($sites as $key => $row) {
                $qsite = htmlentities($row['customer']);
                echo '<tr><td>' . $qsite . '</td><td>';
                echo AUTO_MakeSelect(
                    $accounts,
                    constFormSitePrefix
                        . $row['id'],
                    htmlentities('---Use Default---'),
                    $row['descr'] == null ? null :
                        htmlentities($row['descr']),
                    'No Autotask accounts found.',
                    false,
                    null
                );
                echo '</td></tr>';
            }
            echo '</table>';
        }
    }

    echo '<table border="0" cellpadding="2" cellspacing="2">';

    echo '<tr><td><b>Priority:</b></td><td>';
    echo AUTO_MakeSelect(
        $picks[constPicksPriorities],
        constFormPrioritySel,
        htmlentities('---Select a value---'),
        $settings['confstate'] == constAutoConfStateComplete ?
            $settings['priorityid'] : null,
        'No priority options are available.',
        true,
        $picks[constPicksPrioritiesL]
    );
    echo '</td></tr>';

    echo '<tr><td><b>Queue:</b></td><td>';
    echo AUTO_MakeSelect(
        $picks[constPicksQueues],
        constFormQueueSel,
        htmlentities('---Select a value---'),
        $settings['confstate'] == constAutoConfStateComplete ?
            $settings['queueid'] : null,
        'No queue options are available.',
        true,
        $picks[constPicksQueuesL]
    );
    echo '</td></tr>';

    /* Add some hidden form controls for the status ids */
    echo hidden(constFormStatNew, $picks[constPicksNew]);
    echo hidden(constFormStatComp, $picks[constPicksComplete]);

    /* Ticket option controls */
    echo '</table><p><b>Ticket Settings - Production options</b><p>Due '
        . 'date for new tickets '
        . 'will be&nbsp;';
    $weeks = (int)($settings['duedateadd'] / 604800);
    $sec = $settings['duedateadd'] % 604800;
    $days = (int)($sec / 86400);
    $sec = $sec % 86400;
    $hours = (int)($sec / 3600);
    $sec = $sec % 3600;
    $minutes = (int)($sec / 60);
    echo '<input type="text" size="2" name="' . constFormWeeks
        . '" value="' . $weeks . '">&nbsp;weeks&nbsp;&nbsp;<input '
        . 'type="text" size="2" name="' . constFormDays . '" value="'
        . $days . '">&nbsp;days&nbsp;&nbsp;<input type="text" size="2" '
        . 'name="' . constFormHours . '" value="' . $hours . '">&nbsp;'
        . 'hours&nbsp;&nbsp;<input type="text" size="2" name="'
        . constFormMinutes . '" value="' . $minutes . '">&nbsp;minutes '
        . 'after the ticket was created.<p>';

    $radioval = $settings['createdisp'];
    if ($radioval == constAutoDispCompleteNew) {
        $radioval = constAutoDispKeepNew;
    }
    $state = '';
    if ($radioval != constAutoDispAppendNew) {
        $state = ' checked';
    }
    echo '<input type="radio" name="' . constFormCreateDisp . '" value="'
        . constAutoDispKeepNew . '"' . $state
        . '>&nbsp;Always create new tickets.&nbsp;&nbsp;';

    $state = '';
    if ($settings['createdisp'] == constAutoDispCompleteNew) {
        $state = ' checked';
    }
    echo '<input type="checkbox" name="' . constFormCreateClosed
        . '" value="1"' . $state . '>&nbsp;Tickets should be created '
        . 'in a closed state.<br>';

    $state = '';
    if ($radioval == constAutoDispAppendNew) {
        $state = ' checked';
    }
    echo '<input type="radio" name="' . constFormCreateDisp . '" value="'
        . constAutoDispAppendNew . '"' . $state
        . '>&nbsp;The first time a notification is produced create a new '
        . 'ticket.  From the second occurrence of the notification on, '
        . 'append additional instances of the notification to the ticket '
        . 'created the first '
        . 'time the notification was produced.  <i>If the Autotask ticket '
        . 'has been '
        . 'closed (or was created closed) a new ticket is created '
        . 'instead.</i><p>';

    echo '<b>Ticket Note Settings</b><p>';
    echo '<table border="0" cellpadding="2" cellspacing="2">';

    echo '<tr><td><b>Note Type:</b></td><td>';
    echo AUTO_MakeSelect(
        $notes[constPicksNoteType],
        constFormNoteTypeSel,
        htmlentities('---Select a value---'),
        $settings['confstate'] == constAutoConfStateComplete ?
            $settings['notetypeid'] : null,
        'No note type options are available.',
        true,
        $notes[constPicksNoteTypeL]
    );
    echo '</td></tr>';

    echo '<tr><td><b>Publish options:</b></td><td>';
    echo AUTO_MakeSelect(
        $notes[constPicksPublish],
        constFormPublishSel,
        htmlentities('---Select a value---'),
        $settings['confstate'] == constAutoConfStateComplete ?
            $settings['publishid'] : null,
        'No publish options are available.',
        true,
        $notes[constPicksPublishL]
    );
    echo '</td></tr></table>';

    echo '<input type="submit" name="' . constFormAction
        . '" value="Save"><p>';
}

function SaveSettings($username, $settings, $default, $db)
{
    $quser = safe_addslashes($username);

    $priorityid = get_string(constFormPrioritySel, '');
    $queueid = get_string(constFormQueueSel, '');
    $notetypeid = get_string(constFormNoteTypeSel, '');
    $publishid = get_string(constFormPublishSel, '');
    /* Require the user to select a priorityid and queueid */
    if (($priorityid == '') || ($queueid == '') || ($notetypeid == '')
        || ($publishid == '')
    ) {
        echo 'Please choose a priority, queue, note type, and/or publish '
            . 'option for tickets and/or notes.<p>';
        return array();
    }

    $secs = get_integer(constFormWeeks, 0) * 604800;
    $secs += get_integer(constFormDays, 0) * 86400;
    $secs += get_integer(constFormHours, 0) * 3600;
    $secs += get_integer(constFormMinutes, 0) * 60;

    $createdisp = get_integer(constFormCreateDisp, constAutoDispKeepNew);
    if ((get_integer(constFormCreateClosed, 0))
        && ($createdisp == constAutoDispKeepNew)
    ) {
        $createdisp = constAutoDispCompleteNew;
    }
    $statusid = get_integer(constFormStatNew, 0);
    $statcloseid = get_integer(constFormStatComp, 0);

    $sql = "UPDATE UserSettings SET priorityid=$priorityid, queueid="
        . "$queueid, duedateadd=$secs, createdisp=$createdisp, "
        . "statusid=$statusid, statcloseid=$statcloseid, confstate="
        . constAutoConfStateComplete . ", notetypeid=$notetypeid, "
        . "publishid=$publishid"
        . ' WHERE settingid=' . $settings['settingid'];
    $res = command($sql, $db);

    if ($default) {
        $newaccount = html_entity_decode(get_string(
            constFormSitePrefix,
            ''
        ));
        $sql = 'SELECT accountid FROM  ' . $GLOBALS['PREFIX'] . 'autotask.AutotaskAccount '
            . 'WHERE linktype=' . constAutoSiteTypeDefault;
        $row = find_one($sql, $db);
        if ($row) {
            $sql = 'UPDATE  ' . $GLOBALS['PREFIX'] . 'autotask.AutotaskAccount SET descr=\''
                . safe_addslashes($newaccount) . '\' WHERE accountid='
                . $row['accountid'];
        } else {
            $sql = 'INSERT INTO  ' . $GLOBALS['PREFIX'] . 'autotask.AutotaskAccount '
                . '(descr, linktype) VALUES (\'' . safe_addslashes($newaccount)
                . '\', ' . constAutoSiteTypeDefault . ')';
        }
        command($sql, $db);
    } else {
        $sql = 'SELECT customer, descr, id FROM ' . $GLOBALS['PREFIX'] . 'core.Customers '
            . 'LEFT JOIN  ' . $GLOBALS['PREFIX'] . 'autotask.'
            . ' AutotaskAccount ON (Customers.customer='
            . 'AutotaskAccount.site '
            . 'AND AutotaskAccount.linktype=' . constAutoSiteTypeLink
            . ') '
            . " WHERE Customers.username='$quser' ORDER BY customer";
        $sites = find_many($sql, $db);
        $usedefault = false;
        foreach ($sites as $key => $site) {
            $newaccount = html_entity_decode(get_string(constFormSitePrefix
                . $site['id'], ''));
            if ($newaccount == '') {
                $usedefault = true;
            }

            /* Handle all cases:
                    1. Current map does not exist and user selected a value.
                    2. Current map exists and user did not select a value.
                    3. Current map exists and user selected a different value.
    
                    All other cases do not require any action here.
                */
            $sql = '';
            if (($site['descr'] == null) && ($newaccount != '')) {
                /* This is case 1.  Create the map. */
                $sql = 'INSERT INTO AutotaskAccount (site, descr, '
                    . 'linktype) '
                    . 'VALUES (\'' . safe_addslashes($site['customer'])
                    . '\',\''
                    . safe_addslashes($newaccount) . '\','
                    . constAutoSiteTypeLink
                    . ')';
            } else if (($site['descr'] != null) && ($newaccount == '')) {
                /* This is case 2.  Delete the map. */
                $sql = 'DELETE FROM AutotaskAccount WHERE site=\''
                    . safe_addslashes($site['customer']) . '\' AND linktype='
                    . constAutoSiteTypeLink;
            } else if (($site['descr'] != null)
                && ($newaccount != $site['descr'])
            ) {
                /* This is case 3.  Update the existing map. */
                $sql = 'UPDATE AutotaskAccount SET descr=\''
                    . safe_addslashes($newaccount) . '\' WHERE site=\''
                    . safe_addslashes($site['customer']) . '\' AND linktype='
                    . constAutoSiteTypeLink;
            }
            if ($sql != '') {
                command($sql, $db);
            }
        }

        if ($usedefault) {
            /* Use default was chosen for at least some of the sites.
                    Provide
                    a warning message to the user if the default settings are
                    not
                    available. */
            $sql = 'SELECT count(*) FROM AutotaskAccount WHERE linktype='
                . constAutoSiteTypeDefault;
            $row = find_one($sql, $db);
            if ($row['count(*)'] == 0) {
                echo 'You have chosen <i>---Use Default---</i> for at '
                    . 'least '
                    . 'one of '
                    . 'your sites.  The server administrator has not yet '
                    . 'assigned server default Autotask settings.  '
                    . 'Autotask '
                    . 'tickets for sites set to <i>---Use Default---</i> '
                    . 'will '
                    . 'not '
                    . 'be created or modified until a specific Autotask '
                    . 'account is chosen for the site or the server '
                    . 'default '
                    . 'is assigned.<p>';
            }
        }
    }

    return AUTO_GetUserSettings($username, $db);
}

function SaveConnectInfo($settings, $default, $db)
{
    $url = get_string(constFormServiceWSDL, '');
    $user = get_string(constFormUsername, '');
    $pass = get_string(constFormPassword, '');
    $sql = 'UPDATE UserSettings SET middleurl=\'' . safe_addslashes($url)
        . '\', autouser=\'' . safe_addslashes($user) . '\'';
    if ($pass != '') {
        $sql .= ', autopass=\'' . safe_addslashes($pass) . '\'';
    }
    $sql .= ', confstate=IF(confstate=' . constAutoConfStateComplete
        . ',' . constAutoConfStateComplete . ',' . constAutoConfStateNew
        . ')';
    $sql .= ' WHERE settingid=' . $settings['settingid'];
    $res = command($sql, $db);

    if ($default) {
        return AUTO_GetDefaultSettings($db);
    }

    return AUTO_GetUserSettings($settings['username'], $db);
}

/*
    |  Main program
    */

$now  = time();
$db   = db_connect();
$auth = process_login($db);
$comp = component_installed();
$user  = user_data($auth, $db);
$db = db_select($GLOBALS['PREFIX'] . 'autotask');

$act = get_integer('act', constActForm);
$title = '';
$msg2 = '';
$default = get_integer('default', 0);
if ($default) {
    $title = 'Default ';
}

/* Get this user's settings */
if ($default) {
    $settings = AUTO_GetDefaultSettings($db);
} else {
    $settings = AUTO_GetUserSettings($user['username'], $db);
}

if (!$settings) {
    $msg2 = 'Failed to retrieve settings.';
    $act = constActNone;
}

if ($act == constActForm2) {
    $url = get_string(constFormServiceWSDL, '');
    $user = get_string(constFormUsername, '');
    if (($url == '') || ($user == '')) {
        $msg2 = 'Server name and/or username cannot be empty.';
        $act = constActForm;
    }
}

switch ($act) {
    case constActForm:
        $title .= 'Autotask Server Login';
        break;
    case constActForm2:
    case constActFormSave:
    default:
        $title .= 'Autotask Settings';
        break;
}

$msg  = ob_get_contents();
ob_end_clean();
echo standard_html_header($title, $comp, $auth, 0, 0, '', $db);

if ($msg2) {
    echo '<p>' . $msg2 . '<p>';
}

switch ($act) {
    case constActNone:
        break;
    case constActForm:
        DisplayForm($settings, $default, $db);
        break;
    case constActForm2:
        $settings = SaveConnectInfo($settings, $default, $db);
        if ($settings) {
            DisplaySettingsForm($settings, $default, $db);
        }
        break;
    case constActFormSave:
        $settings = SaveSettings(
            $user['username'],
            $settings,
            $default,
            $db
        );
        if ($settings) {
            echo 'Your settings have been saved.';
        } else {
            echo 'Error saving your settings.';
        }
        break;
}

echo head_standard_html_footer($auth, $db);
