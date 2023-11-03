<?php

/*
Revision history:

Date        Who     What
----        ---     ----
11-Jun-04   EWB     Created.
29-Jun-04   EWB     Shows PatchStatus Records.
30-Jun-04   EWB     Column sorting.
 6-Jul-04   EWB     Added "needs reboot" patch status.
 6-Jul-04   EWB     Added "explanatory text"
13-Jul-04   EWB     Filter for site / host
14-Jul-04   EWB     Removed nextaction / lastuninstall columns
14-Jul-04   EWB     Display and sort by PatchStatus.detected.
16-Jul-04   EWB     status control.
21-Jul-04   EWB     Paging.
22-Jul-04   EWB     Table Alignment.
27-Jul-04   EWB     Column order detect / download / install
28-Jul-04   EWB     Wizard page for all / site errors.
 6-Aug-04   EWB     encode / decode conditional column display
27-Aug-04   EWB     patch_navigate();
28-Sep-04   EWB     new search method
29-Sep-04   EWB     optional machine column.
29-Sep-04   EWB     select / display / sort by size.
30-Sep-04   EWB     select by mgrp / pgrp.
30-Sep-04   EWB     teeny-tiny control panel.
 5-Oct-04   EWB     more choices for page sizes.
11-Oct-04   EWB     any column can have it's own never name.
25-Oct-04   EWB     added "potential installation failure".
27-Oct-04   EWB     title change for alex.
18-Nov-04   EWB     searching and sorting links scroll to the table.
23-Nov-04   EWB     display and sorting by msname.
24-Nov-04   EWB     selection by msname.
29-Nov-04   EWB     constPatchStatusSuperceded
 3-Dec-04   EWB     constPatchStatusSuperseded
20-Dec-04   EWB     fixed a subtle form posting problem.
28-Jan-05   EWB     Added reset and help buttons.
 3-Feb-05   EWB     Alex help text
10-Feb-05   BJS     Export Button.
14-Feb-05   EWB     Database Consistancy Check.
12-Sep-05   BTE     Added checksum invalidation code.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
24-Feb-06   BTE     Bug 3079: Make expunge/server deletions permanent and make
                    client preserve self.
15-Mar-06   BTE     Bug 3186: Event logging appears to be completely broken on
                    4.3 server.
06-May-06   BTE     Bug 3209: 4.2 to 4.3 server upgrade does not work
                    correctly.
21-Jun-06   BTE     Bug 3466: The primary census server page is no longer in
                    order.
26-Aug-06   BTE     Bug 3601: Superseded status can be misleading in MUM.
02-Oct-06   BTE     Updated to handle new status code.
09-Oct-06   BTE     Bug 3710: Extend the patch status page.
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer() 
31-Oct-06   BTE     Bug 3794: Finish server for first customer release of MUM
                    changes.
03-Nov-06   BTE     Text change from Alex.
16-Nov-06   BTE     Bug 3611: Finish MS update status report that BJ started.
14-Mar-07   BTE     Removed report generate section.

*/

ob_start();
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-slct.php');
include('../lib/l-rlib.php');
include('../lib/l-user.php');
include('../lib/l-jump.php');
include('../lib/l-slav.php');
include('../lib/l-tabs.php');
include('../lib/l-cmth.php');
include('../lib/l-form.php');
include('../lib/l-tiny.php');
include('../lib/l-head.php');
include('../lib/l-cnst.php');
include('../lib/l-errs.php');
include('../lib/l-dsyn.php');
include('../lib/l-gdrt.php');
include('../lib/l-grps.php');
include('../lib/l-core.php');
include('../lib/l-ptch.php');
include('../lib/l-rept.php');
include('../lib/l-mime.php');
include('../lib/l-date.php');
include('local.php');

define('constButtonHlp',   'Help');
define('constButtonRst',   'Reset');
define('constButtonSub',   'Search');
define('constButtonExport', 'Export');
define('constButtonGen',   'Create Report');
define('constButtonCancel', 'Cancel');

define('constNoneStr',      'None');

/* status_control $format options */
define('constFormatTable',  0);
define('constFormatForm',   1);

define('constSepBlankLine', '<tr><td colspan="0">&nbsp;</td></tr>');
define('constSepSmallLine', '<tr><td colspan="0" height="6">'
    . '</td></tr>');

function unknown_action(&$env, $db)
{
    debug_note("unknown action");
}

function title($act, $site, $host)
{
    $mu = 'Microsoft Update';
    switch ($act) {
        case 'ptch':;
        case 'list':
            return "$mu - Status";
        case 'site':
            return "Status for $mu at $site";
        case 'host':
            return "Status for $mu for $host at $site";
        case 'dbug':
            return "Debug Menu for $mu";
        case 'dlst':
            return "Debug Status for $mu";
        case 'fake':
            return "Generate Fake Status for $mu";
        case 'sane':
            return 'Consistancy Check';
            //     case 'aerr': return "Error Status for $mu";
            //     case 'serr': return "Site Error Status for $mu";
        case 'cfgr':
            return "$mu Management - Report Configuration";
        case 'genr':
            return 'MUM Update Status Report';
        default:
            return "Unknown Action $act";
    }
}

function again(&$env)
{
    $self = $env['self'];
    $dbg  = $env['priv'];
    $act  = $env['act'];
    $a    = array();
    $a[]  = html_link('#top', 'top');
    $a[]  = html_link('#bottom', 'bottom');
    if ($act != 'help') {
        $a[]  = html_link('#control', 'search');
        $a[]  = html_link('#table', 'table');
    }
    $a[]  = html_link('index.php', 'wizard');
    if ($dbg) {
        $dbug = "$self?act=dbug";
        $args = $env['args'];
        $href = ($args) ? "$self?$args" : $self;
        $a[] = html_link('../acct/index.php', 'home');
        $a[] = html_link($dbug, 'debug');
        $a[] = html_link($href, 'again');
        $a[] = html_link($self, 'status');
    }
    return jumplist($a);
}


function nevername($name)
{
    switch ($name) {
        case 'lastinstall':
            return 'not installed';
        case 'lastdownload':
            return 'not downloaded';
        case 'detected':
            return 'not detected';
        case 'nextaction':
            return 'unknown';
        case 'lastchange':
            return 'unchanged';
        case 'lastuninstall':
            return 'not removed';
        case 'lasterrordate':
            return 'no errors';
        default:
            return 'never';
    }
}


function timestamp($row, $name)
{
    $time = $row[$name];
    $text = date('m/d/y H:i:s', $time);
    return ($time) ? $text : nevername($name);
}

function status($stat, $patchstatusid)
{
    switch ($stat) {
        case                   constPatchStatusInvalid:
            return 'undefined';
        case        constPatchStatusNotHandledOnServer:
            return 'automatic';
        case   constPatchStatusPendingImmediateInstall:
            return 'pending install';
        case constPatchStatusPendingImmediateUninstall:
            return 'pending uninstall';
        case   constPatchStatusPendingScheduledInstall:
            return 'scheduled install';
        case constPatchStatusPendingScheduledUninstall:
            return 'scheduled uninstall';
        case           constPatchStatusInstallDisabled:
            return 'disabled';
        case             constPatchStatusInstallFailed:
            return 'install error';
        case                 constPatchStatusInstalled:
            return 'installed';
        case               constPatchStatusUninstalled:
            return 'uninstalled';
        case                constPatchStatusDownloaded:
            return 'downloaded';
        case                  constPatchStatusDetected:
            return 'detected';
        case           constPatchStatusPendingDownload:
            return 'pending download';
        case             constPatchStatusPendingReboot:
            return 'needs reboot';
        case          constPatchStatusPotentialFailure:
            return 'potential installation failure';
        case constPatchStatusSuperseded:
            return "<a target=\"_blank\" href=\"chkstat.php?status=super&"
                . "patchstatusid=$patchstatusid\">superseded</a>";
        case constPatchStatusWaiting:
            return "<a target=\"_blank\" href=\"chkstat.php?status=wait&"
                . "patchstatusid=$patchstatusid\">waiting</a>";
        case constPatchStatusDownloadFailed:
            return 'download error';
        default:
            return status(constPatchStatusInvalid);
    }
}

function order($ord, &$env)
{
    $env['order1'] = constNoneStr;
    $env['order2'] = constNoneStr;
    $env['order3'] = constNoneStr;
    $env['order4'] = '';
    switch ($ord) {
        case  0:
            $env['order1'] = 'CONVERT(C.site USING latin1) ASC';
            $env['order2'] = 'CONVERT(C.host USING latin1) ASC';
            $env['order3'] = 'CONVERT(P.name USING latin1) ASC';
            $env['order4'] = 'patchstatusid';
            return "CONVERT(site USING latin1), CONVERT(host USING "
                . "latin1), CONVERT(name USING latin1), patchstatusid";
        case  1:
            $env['order1'] = 'CONVERT(C.site USING latin1) DESC';
            $env['order2'] = 'CONVERT(C.host USING latin1) DESC';
            $env['order3'] = 'CONVERT(P.name USING latin1) DESC';
            $env['order4'] = 'patchstatusid';
            return "CONVERT(site USING latin1) desc, CONVERT(host"
                . " USING latin1) desc, CONVERT(name USING latin1) desc, "
                . "patchstatusid";
        case  2:
            $env['order1'] = 'CONVERT(C.host USING latin1) ASC';
            $env['order2'] = 'CONVERT(P.name USING latin1) ASC';
            $env['order3'] = 'CONVERT(C.site USING latin1) ASC';
            $env['order4'] = 'patchstatusid';
            return "CONVERT(host USING latin1), CONVERT(name USING "
                . "latin1), CONVERT(site USING latin1), patchstatusid";
        case  3:
            $env['order1'] = 'CONVERT(C.host USING latin1) DESC';
            $env['order2'] = 'CONVERT(C.site USING latin1) DESC';
            $env['order3'] = 'CONVERT(P.name USING latin1) DESC';
            $env['order4'] = 'patchstatusid';
            return "CONVERT(host USING latin1) desc, CONVERT(site "
                . "USING latin1) desc, CONVERT(name USING latin1) desc, "
                . "patchstatusid";
        case  4:
            $env['order1'] = 'CONVERT(P.name USING latin1) ASC';
            $env['order2'] = 'CONVERT(C.site USING latin1) ASC';
            $env['order3'] = 'CONVERT(C.host USING latin1) ASC';
            $env['order4'] = 'patchstatusid';
            return "CONVERT(name USING latin1), CONVERT(site USING "
                . "latin1), CONVERT(host USING latin1), patchstatusid";
        case  5:
            $env['order1'] = 'CONVERT(P.name USING latin1) DESC';
            $env['order2'] = 'CONVERT(C.site USING latin1) DESC';
            $env['order3'] = 'CONVERT(C.host USING latin1) DESC';
            $env['order4'] = 'patchstatusid';
            return "CONVERT(name USING latin1) desc, CONVERT(site "
                . "USING latin1), CONVERT(host USING latin1), patchstatusid";
        case  6:
            $env['order1'] = 'CONVERT(S.status USING latin1) ASC';
            $env['order2'] = 'CONVERT(P.name USING latin1) ASC';
            $env['order4'] = 'patchstatusid desc';
            return 'status, name, patchstatusid desc';
        case  7:
            $env['order1'] = 'CONVERT(S.status USING latin1) DESC';
            $env['order2'] = 'CONVERT(P.name USING latin1) ASC';
            $env['order4'] = 'patchstatusid';
            return 'status desc, name, patchstatusid';
        case  8:
            $env['order1'] = 'CONVERT(S.lastinstall USING latin1) DESC';
            $env['order4'] = 'patchstatusid';
            return 'lastinstall desc, patchstatusid';
        case  9:
            $env['order1'] = 'CONVERT(S.lastinstall USING latin1) ASC';
            $env['order4'] = 'patchstatusid desc';
            return 'lastinstall, patchstatusid desc';
        case 10:
            $env['order1'] = 'CONVERT(S.lastuninstall USING latin1) DESC';
            $env['order4'] = 'patchstatusid';
            return 'lastuninstall desc, patchstatusid';
        case 11:
            $env['order1'] = 'CONVERT(S.lastuninstall USING latin1) ASC';
            $env['order4'] = 'patchstatusid desc';
            return 'lastuninstall, patchstatusid desc';
        case 12:
            $env['order1'] = 'CONVERT(S.nextaction USING latin1) DESC';
            $env['order4'] = 'patchstatusid';
            return 'nextaction desc, patchstatusid';
        case 13:
            $env['order1'] = 'CONVERT(S.nextaction USING latin1) ASC';
            $env['order4'] = 'patchstatusid desc';
            return 'nextaction, patchstatusid desc';
        case 14:
            $env['order1'] = 'CONVERT(S.lastdownload USING latin1) DESC';
            $env['order4'] = 'patchstatusid';
            return 'lastdownload desc, patchstatusid';
        case 15:
            $env['order1'] = 'CONVERT(S.lastdownload USING latin1) ASC';
            $env['order4'] = 'patchstatusid desc';
            return 'lastdownload, patchstatusid desc';
        case 16:
            $env['order1'] = 'CONVERT(S.lastinstall USING latin1) DESC';
            $env['order4'] = 'patchstatusid desc';
            return 'downloadsource, patchstatusid desc';
        case 17:
            $env['order1'] = 'CONVERT(S.downloadsource USING latin1) DESC';
            $env['order4'] = 'patchstatusid';
            return 'downloadsource desc, patchstatusid';
        case 18:
            $env['order1'] = 'CONVERT(S.lasterror USING latin1) ASC';
            $env['order2'] = 'CONVERT(C.site USING latin1) ASC';
            $env['order3'] = 'CONVERT(C.host USING latin1) ASC';
            $env['order4'] = 'patchstatusid';
            return 'lasterror, site, host, patchstatusid';
        case 19:
            $env['order1'] = 'CONVERT(S.lasterror USING latin1) DESC';
            $env['order2'] = 'CONVERT(C.site USING latin1) DESC';
            $env['order3'] = 'CONVERT(C.host USING latin1) DESC';
            $env['order4'] = 'patchstatusid desc';
            return 'lasterror desc, site desc, host desc, patchstatusid '
                . 'desc';
        case 20:
            $env['order1'] = 'CONVERT(S.detected USING latin1) DESC';
            $env['order2'] = 'CONVERT(P.name USING latin1) ASC';
            $env['order4'] = 'patchstatusid';
            return 'detected desc, name, patchstatusid';
        case 21:
            $env['order1'] = 'CONVERT(S.detected USING latin1) ASC';
            $env['order2'] = 'CONVERT(P.name USING latin1) DESC';
            $env['order4'] = 'patchstatusid';
            return 'detected, name desc, patchstatusid';
        case 22:
            $env['order4'] = 'patchstatusid';
            return 'patchstatusid';
        case 23:
            $env['order4'] = 'patchstatusid desc';
            return 'patchstatusid desc';
        case 24:
            $env['order1'] = 'CONVERT(S.lastchange USING latin1) DESC';
            $env['order2'] = 'CONVERT(S.detected USING latin1) DESC';
            $env['order4'] = 'patchstatusid';
            return 'lastchange desc, detected desc, patchstatusid';
        case 25:
            $env['order1'] = 'CONVERT(S.lastchange USING latin1) ASC';
            $env['order2'] = 'CONVERT(S.detected USING latin1) ASC';
            $env['order4'] = 'patchstatusid desc';
            return 'lastchange, detected, patchstatusid desc';
        case 26:
            $env['order1'] = 'CONVERT(S.lasterrordate USING latin1) DESC';
            $env['order4'] = 'patchstatusid';
            return 'lasterrordate desc, patchstatusid';
        case 27:
            $env['order1'] = 'CONVERT(S.lasterrordate USING latin1) ASC';
            $env['order4'] = 'patchstatusid desc';
            return 'lasterrordate, patchstatusid desc';
        case 28:
            $env['order1'] = 'CONVERT(P.size USING latin1) DESC';
            $env['order4'] = 'patchstatusid';
            return 'size desc, patchstatusid';
        case 29:
            $env['order1'] = 'CONVERT(P.size USING latin1) ASC';
            $env['order4'] = 'patchstatusid desc';
            return 'size, patchstatusid desc';
        case 30:
            $env['order1'] = 'CONVERT(P.msname USING latin1) ASC';
            $env['order4'] = 'patchstatusid desc';
            return 'msname, patchstatusid desc';
        case 31:
            $env['order1'] = 'CONVERT(P.msname USING latin1) DESC';
            $env['order4'] = 'patchstatusid';
            return 'msname desc, patchstatusid';
        case 32:
            $env['order1'] = 'CONVERT(P.type USING latin1) ASC';
            $env['order4'] = 'patchstatusid desc';
            return 'type, patchstatusid desc';
        case 33:
            $env['order1'] = 'CONVERT(P.type USING latin1) DESC';
            $env['order4'] = 'patchstatusid';
            return 'type desc, patchstatusid';
        default:
            return order(0, $env);
    }
}


function tag_defaults()
{
    return array(
        'name' => 1,
        'cid'  => 0,
        'hid'  => 0,
        'mid'  => 0,
        'gid'  => 0,
        'jid'  => 0,
        'stat' => 0,
        'inst' => 0,
        'dnld' => 0,
        'dtec' => 0,
        'unin' => -1,
        'next' => -1,
        'chng' => -1,
        'psid' => -1,
        'dsrc' => -1,
        'lerr' => -1,
        'etim' => -1,
        'mtag' => -1,
        'size' => -1,
        'limt' => 20,
        'type' => -1
    );
}


/*
    |   p page
    |   l limt
    |   s stat
    |   o ord
    |   d dtec
    |   c chng
    |   n next
    |   r unin
    |   i inst
    |   m mtag
    |   z size
    */

function tag_shorts()
{
    return array(
        'limt' => 'l',
        'stat' => 's',
        'cid'  => 'cid',
        'hid'  => 'hid',
        'mid'  => 'mid',
        'gid'  => 'gid',
        'jid'  => 'jid',
        'inst' => 'i',
        'dnld' => 'dl',
        'dtec' => 'd',
        'unin' => 'r',
        'next' => 'n',
        'chng' => 'c',
        'psid' => 'psid',
        'name' => 'name',
        'dsrc' => 'ds',
        'lerr' => 'e',
        'etim' => 'et',
        'mtag' => 'm',
        'size' => 'z',
        'type' => 't'
    );
}




function ords()
{
    $as = 'ascending';
    $ds = 'descending';
    $u = 'Update';
    $s = 'Site';
    $d = 'Date';
    $m = 'Machine';
    return array(
        0 => "$s / $m / $u ($as)",
        1 => "$s / $m / $u ($ds)",
        2 => "$m / $s / $u ($as)",
        3 => "$m / $s / $u ($ds)",
        4 => "$u / $s / $m ($as)",
        5 => "$u / $s / $m ($ds)",
        6 => "Status / $u ($as)",
        7 => "Status / $u ($ds)",
        8 => "Install $d ($ds)",
        9 => "Install $d ($as)",
        10 => "Removal $d ($ds)",
        11 => "Removal $d ($as)",
        12 => "Next $d ($ds)",
        13 => "Next $d ($as)",
        14 => "Download $d ($ds)",
        15 => "Download $d ($as)",
        16 => "Download Source ($as)",
        17 => "Download Source ($ds)",
        18 => "Error ($as)",
        19 => "Error ($ds)",
        20 => "Detect $d ($ds)",
        21 => "Detect $d ($as)",
        22 => "Id ($as)",
        23 => "Id ($ds)",
        24 => "Change $d ($ds)",
        25 => "Change $d ($as)",
        26 => "Error $d ($ds)",
        27 => "Error $d ($as)",
        28 => "$u Size ($ds)",
        29 => "$u Size ($as)",
        30 => "MS Name ($as)",
        31 => "MS Name ($ds)",
        32 => "Type ($as)",
        33 => "Type ($ds)"
    );
}


function add_column(&$env, $name, &$args, $data)
{
    if (@$env[$name]) {
        if ($name == 'd_lerr') {
            if (is_numeric($data)) {
                $args[] = "<a target=\"_blank\" href=\"chkerror.php?"
                    . "deccode=$data\">$data</a>";
            } else {
                $args[] = $data;
            }
        } else {
            $args[] = $data;
        }
    }
}



function stats_table(&$env, &$set, $total)
{
    $ord  = $env['ord'];
    $lim  = $env['limt'];
    $self = $env['self'];
    $code = $env['code'];
    $tags = $env['tags'];
    $jump = $env['jump'];
    $types = PTCH_GetAllTypes();

    /*
        |  create a list of everthing
        |  which has deviated from its
        |  default value.
        */

    $a = array("$self?l=$lim");
    reset($tags);
    foreach ($tags as $name => $tag) {
        $valu = $env[$name];
        $what = $code[$name];
        if ($valu != $tag) {
            $a[] = "$what=$valu";
        }
    }

    $o = join('&', $a) . '&o';
    $site = ($ord ==  0) ? "$o=1"  : "$o=0";     // site   0, 1
    $host = ($ord ==  2) ? "$o=3"  : "$o=2";     // host   2, 3
    $name = ($ord ==  4) ? "$o=5"  : "$o=4";     // name   4, 5
    $stat = ($ord ==  6) ? "$o=7"  : "$o=6";     // stat   6, 7
    $inst = ($ord ==  8) ? "$o=9"  : "$o=8";     // inst   8, 9
    $unin = ($ord == 10) ? "$o=11" : "$o=10";    // unin  10, 11
    $next = ($ord == 12) ? "$o=13" : "$o=12";    // next  12, 13
    $dnld = ($ord == 14) ? "$o=15" : "$o=14";    // dnld  14, 15
    $dsrc = ($ord == 16) ? "$o=17" : "$o=16";    // dsrc  16, 17
    $lerr = ($ord == 18) ? "$o=19" : "$o=18";    // lerr  18, 19
    $dtec = ($ord == 20) ? "$o=21" : "$o=20";    // dtec  20, 21
    $psid = ($ord == 22) ? "$o=23" : "$o=22";    // psid  22, 23
    $chng = ($ord == 24) ? "$o=25" : "$o=24";    // chng  24, 25
    $etim = ($ord == 26) ? "$o=27" : "$o=26";    // etim  26, 27
    $size = ($ord == 28) ? "$o=29" : "$o=28";    // size  28, 29
    $mtag = ($ord == 30) ? "$o=31" : "$o=30";    // mtag  30, 31
    $type = ($ord == 32) ? "$o=33" : "$o=32";    // type  32, 33

    $a   = array();
    add_column($env, 'd_cid', $a, html_jump($site, $jump, 'Site'));
    add_column($env, 'd_hid', $a, html_jump($host, $jump, 'Machine'));
    add_column($env, 'd_mtag', $a, html_jump($mtag, $jump, 'MS Name'));
    add_column($env, 'd_type', $a, html_jump($type, $jump, 'Type'));
    add_column($env, 'd_name', $a, html_jump($name, $jump, 'Update'));
    add_column($env, 'd_stat', $a, html_jump($stat, $jump, 'Status'));
    add_column($env, 'd_size', $a, html_jump($size, $jump, 'Size'));
    add_column($env, 'd_dtec', $a, html_jump($dtec, $jump, 'Detect Date'));
    add_column($env, 'd_dnld', $a, html_jump($dnld, $jump, 'Download Date'));
    add_column($env, 'd_inst', $a, html_jump($inst, $jump, 'Install Date'));
    add_column($env, 'd_unin', $a, html_jump($unin, $jump, 'Removal Date'));
    add_column($env, 'd_chng', $a, html_jump($chng, $jump, 'Date of Last Change'));
    add_column($env, 'd_next', $a, html_jump($next, $jump, 'Date of Next Action'));
    add_column($env, 'd_dsrc', $a, html_jump($dsrc, $jump, 'Download Source'));
    add_column($env, 'd_lerr', $a, html_jump($lerr, $jump, 'Error'));
    add_column($env, 'd_etim', $a, html_jump($etim, $jump, 'Error Date'));
    add_column($env, 'd_psid', $a, html_jump($psid, $jump, 'Record Id'));

    $cmd = 'wu-patch.php?act=edet&mid';

    if (($set) && ($a)) {
        $cols = safe_count($a);
        $text = "Status Records &nbsp; ($total found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($a, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $psid = $row['patchstatusid'];
            $mid  = $row['patchid'];
            $size = $row['size'];
            $msn  = $row['msname'];
            $site = disp($row, 'site');
            $host = disp($row, 'host');
            $name = disp($row, 'name');
            $lerr = disp($row, 'lasterror');
            $dsrc = disp($row, 'downloadsource');
            $stat = status($row['status'], $psid);
            $mtag = ($msn) ? $msn : '(none)';
            $inst = timestamp($row, 'lastinstall');
            $chng = timestamp($row, 'lastchange');
            $unin = timestamp($row, 'lastuninstall');
            $next = timestamp($row, 'nextaction');
            $dtec = timestamp($row, 'detected');
            $dnld = timestamp($row, 'lastdownload');
            $etim = timestamp($row, 'lasterrordate');
            $name = html_page("$cmd=$mid", $name);
            $mtag = html_page("$cmd=$mid", $mtag);
            $args = array();
            add_column($env, 'd_cid', $args, $site);
            add_column($env, 'd_hid', $args, $host);
            add_column($env, 'd_mtag', $args, $mtag);
            add_column($env, 'd_type', $args, $types[$row['type']]);
            add_column($env, 'd_name', $args, $name);
            add_column($env, 'd_stat', $args, $stat);
            add_column($env, 'd_size', $args, $size);
            add_column($env, 'd_dtec', $args, $dtec);
            add_column($env, 'd_dnld', $args, $dnld);
            add_column($env, 'd_inst', $args, $inst);
            add_column($env, 'd_unin', $args, $unin);
            add_column($env, 'd_chng', $args, $chng);
            add_column($env, 'd_next', $args, $next);
            add_column($env, 'd_dsrc', $args, $dsrc);
            add_column($env, 'd_lerr', $args, $lerr);
            add_column($env, 'd_etim', $args, $etim);
            add_column($env, 'd_psid', $args, $psid);
            echo table_data($args, 0);
        }
        echo table_footer();
        echo prevnext($env, $total);
    } else {
        echo para('There were no matching status records ...');
    }
}


function stats_blurb(&$env)
{

    $self = $env['self'];
    $href = 'wu-stat2.htm';
    $help = html_page($href, 'help');

    echo <<< HERE

        <p>
          On this page, you can view the status of updates for machines
          at your sites. Each row in the table represents the status of
          one software update on one machine. You can view the status
          of a subset of the updates by using the <b>Search Options</b>
          immediately below.  You can also control the number of
          updates displayed on each page, and sort the updates either
          by clicking on a column header, or by selecting a multi-column
          sort option from the <b>Display Options</b> box below. Click
          on this $help link to find out more about software update
          status conditions, and other information about the software
          update process.
        </p>

        <p>
          When checking the information on this page, it is very
          important that you take into account any time lag in the
          communication between your sites and the ASI server. You
          should check on the status of an action three to six hours
          after an actions scheduled execution time making sure that
          there are no connectivity issues between the sites and
          the ASI server.
        </p>

        <p>
          Please note that "superseded" and "waiting" status entries are
          hyperlinks. Clicking on a "superseded" status entry will display in a
          new window a listing of all software updates which caused the
          software update with superseded status. Clicking on a "waiting"
          status entry will display in a new window a listing of all software
          updates that need to be installed before the software update with
          "waiting" status will be.
        </p>

HERE;
}



function find_site($cid, $auth, $db)
{
    $row  = array();
    $site = '';
    if (($cid > 0) && ($auth)) {
        $qu  = safe_addslashes($auth);
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers\n"
            . " where id = $cid\n"
            . " and username = '$qu'";
        $row = find_one($sql, $db);
    }
    if ($row) {
        $site = $row['customer'];
    }
    return $site;
}

function find_host($hid, $auth, $db)
{
    $row  = array();
    if (($hid > 0) && ($auth)) {
        $qu  = safe_addslashes($auth);
        $sql = "select C.* from " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U\n"
            . " where C.id = $hid\n"
            . " and C.site = U.customer\n"
            . " and U.username = '$qu'";
        $row = find_one($sql, $db);
    }
    return $row;
}


function site_options($auth, $db)
{
    $list = array();
    $list[-1] = constTagNone;
    $list[0] = constTagAny;
    $qu   = safe_addslashes($auth);
    $sql  = "select U.customer, U.id from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers as U,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as X,\n"
        . " " . $GLOBALS['PREFIX'] . "softinst.Machine as M\n"
        . " where X.id = M.id\n"
        . " and X.site = U.customer\n"
        . " and U.username = '$qu'\n"
        . " group by U.customer\n"
        . " order by CONVERT(U.customer USING latin1)";
    $set = find_many($sql, $db);
    foreach ($set as $key => $row) {
        $cid  = $row['id'];
        $site = $row['customer'];
        $list[$cid] = $site;
        //        debug_note("cid $cid site $site");
    }
    return $list;
}


function ptch_options($db)
{
    $list = array();
    $list[-1] = constTagNone;
    $list[0] = constTagAny;
    $sql  = "select patchid, name from\n"
        . " " . $GLOBALS['PREFIX'] . "softinst.Patches\n"
        . " order by name";
    $set = find_many($sql, $db);
    foreach ($set as $key => $row) {
        $mid  = $row['patchid'];
        $name = $row['name'];
        $list[$mid] = $name;
        //         debug_note("mid $mid name $name");
    }
    return $list;
}

function mtag_options($db)
{
    $out = array();
    $out[-1] = constTagNone;
    $out[0] = constTagAny;
    $sql = "select patchid, msname from\n"
        . " " . $GLOBALS['PREFIX'] . "softinst.Patches\n"
        . " where msname != ''\n"
        . " order by msname";
    $set = find_many($sql, $db);
    reset($set);
    foreach ($set as $key => $row) {
        $mid = $row['patchid'];
        $out[$mid] = $row['msname'];
    }
    return $out;
}

function stat_mgrp_options($auth, $db)
{
    $qu  = safe_addslashes($auth);
    $out = array(constTagAny);
    $sql = "select G.mgroupid, G.name from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers as U,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M\n"
        . " where U.username = '$qu'\n"
        . " and C.site = U.customer\n"
        . " and M.censusuniq = C.censusuniq\n"
        . " and M.mgroupuniq = G.mgroupuniq\n"
        . " and G.human = 1\n"
        . " group by G.mgroupid\n"
        . " order by name";
    $set = find_many($sql, $db);
    foreach ($set as $key => $row) {
        $gid  = $row['mgroupid'];
        $out[$gid] = $row['name'];
    }
    return $out;
}

function WUST_PgrpOptions($db)
{
    $out = array(constTagAny);
    $sql = "select pgroupid, name from\n"
        . " " . $GLOBALS['PREFIX'] . "softinst.PatchGroups\n"
        . " where human = 1\n"
        . " order by name";
    $set = find_many($sql, $db);
    foreach ($set as $key => $row) {
        $jid  = $row['pgroupid'];
        $out[$jid] = $row['name'];
    }
    return $out;
}



function host_options($site, $db)
{
    $list = array();
    $list[-1] = constTagNone;
    $list[0] = constTagAny;
    if ($site) {
        $qs  = safe_addslashes($site);
        $sql = "select X.id, X.host from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as X,\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.Machine as M\n"
            . " where X.id = M.id\n"
            . " and X.site = '$qs'\n"
            . " order by host";
        $set = find_many($sql, $db);
        foreach ($set as $key => $row) {
            $id   = $row['id'];
            $host = $row['host'];
            $list[$id] = $host;
            //              debug_note("id $id host $host");
        }
    }
    return $list;
}


function size_options()
{
    return array(
        -2 => '(unknown)',
        -1 => constTagNone,
        0 => constTagAny,
        1 => '0 - 100 KB',
        2 => '100 KB - 500 KB',
        3 => '500 KB - 1 MB',
        4 => '1 MB - 10MB',
        5 => '10 MB - 50 MB',
        6 => 'over 50 MB'
    );
}


function page_href(&$env, $page, $ord)
{
    $self = $env['self'];
    $limt = $env['limt'];
    $tags = $env['tags'];
    $code = $env['code'];
    $priv = $env['priv'];
    $dbug = $env['debug'];

    $a    = array("$self?p=$page");
    $a[]  = "o=$ord";
    $a[]  = "l=$limt";

    if (($priv) && ($dbug)) {
        $a[] = "debug=1";
    }

    reset($tags);
    foreach ($tags as $name => $tag) {
        $valu = $env[$name];
        $what = $code[$name];
        if ($valu != $tag) {
            $a[] = "$what=$valu";
        }
    }


    return join('&', $a);
}


function simple_time($u)
{
    $date = date('m/d/Y', $u);
    $time = date('H:i:s', $u);
    $text = ($time == '00:00:00') ? $date : "$date $time";
    return $text;
}


/*
    |  In general, D signifies the number of days of data we
    |  want to see, including today, except that we use
    |  signify that the field should not be displayed and
    |  zero to mean that any date is valid.
    |
    |   -1 --> not dispayed
    |    0 --> any date
    |    1 --> today since midnight
    |    2 --> yesterday
    |    3 --> day before yesterday
    */

function date_code($env, $d)
{
    $when = $env['midn'];
    if ($d > 1) {
        $when = days_ago($when, $d - 1);
    }
    return $when;
}


function status_control(&$env, $format, $db)
{
    $auth = $env['auth'];
    $midn = $env['midn'];
    $limt = $env['limt'];
    $page = $env['page'];
    $priv = $env['priv'];
    $stat = $env['stat'];
    $site = $env['site'];
    $jump = $env['jump'];
    $self = $env['self'];
    $ord  = $env['ord'];
    $cid  = $env['cid'];
    $hid  = $env['hid'];
    $mid  = $env['mid'];
    $form = $self . $jump;

    $sites = site_options($auth, $db);
    $names = host_options($site, $db);
    $patch = ptch_options($db);
    $sizes = size_options();
    $mgrps = stat_mgrp_options($auth, $db);
    $pgrps = WUST_PgrpOptions($db);
    $mtags = mtag_options($db);
    $types = PTCH_GetAllTypes();

    if ($format == constFormatTable) {
        echo post_other('myform', $form);
    }

    echo hidden('act', 'list');
    echo hidden('ctl', '1');
    if ((safe_count($sites) <= 2) && ($cid) && ($site)) {
        echo hidden('cid', $cid);
    }

    $now  = time();
    $opts = array();
    $opts[-2] = constTagNever;
    $opts[-1] = constTagNone;
    $opts[0] = constTagAny;
    $opts[1] = constTagToday;

    $days = array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 21, 28, 60, 90, 120, 150, 180, 360);
    $lims = array(5, 10, 20, 25, 50, 75, 100, 150, 200, 250, 500, 1000);

    reset($days);
    foreach ($days as $key => $day) {
        $time = date_code($env, $day);
        $text = date('D m/d', $time) . " ($day days)";
        $opts[$day] = $text;
    }

    if (!in_array($limt, $lims)) {
        $lims[] = $limt;
        sort($lims, SORT_NUMERIC);
    }

    $grps[0] = constTagAny;
    $disp[-1] = constTagNone;
    $disp[0] = constTagAny;
    $disp[1] = constTagEmpty;
    $disp[2] = constTagLaden;

    $nx = 50;
    $px = 150;
    $wx = 3 * ($px + 4);

    $ords = ords();
    $sopt = status_opt();

    if ($format == constFormatTable) {
        $sel_limt = tiny_select('l',   $lims, $limt, 0, $nx);
        $sel_sort = tiny_select('o',   $ords, $ord, 1, $px);
        $sel_stat = tiny_select('s',   $sopt, $stat, 1, $px);
        $sel_lerr = tiny_select('e',   $disp, $env['lerr'], 1, $px);
        $sel_dtec = tiny_select('d',   $opts, $env['dtec'], 1, $px);
        $sel_inst = tiny_select('i',   $opts, $env['inst'], 1, $px);
        $sel_chng = tiny_select('c',   $opts, $env['chng'], 1, $px);
        $sel_unin = tiny_select('r',   $opts, $env['unin'], 1, $px);
        $sel_next = tiny_select('n',   $opts, $env['next'], 1, $px);
        $sel_size = tiny_select('z',   $sizes, $env['size'], 1, $px);
        $sel_etim = tiny_select('et',  $opts, $env['etim'], 1, $px);
        $sel_dnld = tiny_select('dl',  $opts, $env['dnld'], 1, $px);
        $sel_dsrc = tiny_select('ds',  $disp, $env['dsrc'], 1, $px);
        $sel_mtag = tiny_select('m',   $mtags, $env['mtag'], 1, $px);
        $sel_type = tiny_select('t',   $types, $env['type'], 1, $px);
        $sel_site = tiny_select('cid', $sites, $cid, 1, $px);
        $sel_host = tiny_select('hid', $names, $hid, 1, $px);
        $sel_ptch = tiny_select('mid', $patch, $mid, 1, $wx);
        $sel_gid  = tiny_select('gid', $mgrps, $env['gid'], 1, $px);
        $sel_jid  = tiny_select('jid', $pgrps, $env['jid'], 1, $px);
    } else if ($format == constFormatForm) {
        $sel_limt = html_select('l',   $lims, $limt, 0);
        $sel_sort = html_select('o',   $ords, $ord, 1);
        $sel_stat = html_select('s',   $sopt, $stat, 1);
        $sel_lerr = html_select('e',   $disp, $env['lerr'], 1);
        $sel_dtec = html_select('d',   $opts, $env['dtec'], 1);
        $sel_inst = html_select('i',   $opts, $env['inst'], 1);
        $sel_chng = html_select('c',   $opts, $env['chng'], 1);
        $sel_unin = html_select('r',   $opts, $env['unin'], 1);
        $sel_next = html_select('n',   $opts, $env['next'], 1);
        $sel_size = html_select('z',   $sizes, $env['size'], 1);
        $sel_etim = html_select('et',  $opts, $env['etim'], 1);
        $sel_dnld = html_select('dl',  $opts, $env['dnld'], 1);
        $sel_dsrc = html_select('ds',  $disp, $env['dsrc'], 1);
        $sel_mtag = html_select('m',   $mtags, $env['mtag'], 1);
        $sel_type = html_select('t',   $types, $env['type'], 1, $px);
        $sel_site = html_select('cid', $sites, $cid, 1);
        $sel_host = html_select('hid', $names, $hid, 1);
        $sel_ptch = html_select('mid', $patch, $mid, 1);
        $sel_gid  = html_select('gid', $mgrps, $env['gid'], 1);
        $sel_jid  = html_select('jid', $pgrps, $env['jid'], 1);
    }

    $td   = 'td style="font-size: xx-small"';
    $sel_psid = '';
    $pprompt  = '';
    $dcol     = '';
    $privbr   = '';
    if ($priv && ($format == constFormatTable)) {
        $popt     = array(-1 => constTagNone, 0 => constTagAny);
        $psid     = $env['psid'];
        $dbug     = ($env['debug']) ? 1 : 0;
        $dopt     = array('No', 'Yes');
        $sel_dbug = tiny_select('debug', $dopt, $dbug, 1, $nx);
        $sel_psid = tiny_select('psid', $popt, $psid, 1, $px);
        $pprompt  = green('Record Id');
        $dprompt  = green('Debug');
        $dcol     = "\n<$td>$dprompt<br>$sel_dbug</td>\n";
        $privbr   = '<br>';
    }

    if ($format == constFormatTable) {
        $self = $env['self'];
        $href = 'wu-stats.htm';
        $open = "window.open('$href','help');";
        $xopen = "window.open('../acct/export.php?act=ps','xprt');";
        $hlp  = click(constButtonHlp, $open);
        $xprt = click(constButtonExport, $xopen);
        $sub  = button(constButtonSub);
        $rst  = button(constButtonRst);
        $head = table_header();
        $srch = pretty_header('Search Options', 1);
        $disp = pretty_header('Display Options', 1);
        $xn   = indent(4);

        echo <<< XXXX

            <table>
            <tr valign="top">
            <td rowspan="3">
    
                $head
    
                $srch
    
                <tr><td>
                <table border="0" width="100%">
                <tr>
                    <$td>Site     <br>$sel_site </td>
                    <$td>Machine  <br>$sel_host </td>
                    <$td>Update   <br>$sel_ptch </td>
                </tr>
                </table>
    
                <table border="0" width="100%">
                <tr>
                    <$td>Status              <br>$sel_stat </td>
                    <$td>Detect Date         <br>$sel_dtec </td>
                    <$td>Download Date       <br>$sel_dnld </td>
                    <$td>Install Date        <br>$sel_inst </td>
                    <$td>Removal Date        <br>$sel_unin </td>
                </tr>
    
                <tr>
                    <$td>Error               <br>$sel_lerr </td>
                    <$td>Error Date          <br>$sel_etim </td>
                    <$td>Date of Last Change <br>$sel_chng </td>
                    <$td>Date of Next Action <br>$sel_next </td>
                    <$td>Download Source     <br>$sel_dsrc </td>
                </tr>
                <tr>
                    <$td>Update Size         <br>$sel_size </td>
                    <$td>Machine Group       <br>$sel_gid  </td>
                    <$td>Update Group        <br>$sel_jid  </td>
                    <$td>MS Name             <br>$sel_mtag </td>
                    <$td>Type                <br>$sel_type </td>
                <tr>
                    <$td>$pprompt            $privbr $sel_psid </td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                </table>
                </td></tr>
                </table>
    
            </td>
    
            <td rowspan="3">
                $xn
            </td>
    
            <td>
                <table border="2" align="left" cellspacing="2" 
                cellpadding="2" width="100%">\n
                $disp
    
                <tr><td>
                <table border="0" width="100%">
                <tr>
                    <$td>Page Size  <br>$sel_limt  </td>
                    <$td>Sort By    <br>$sel_sort  </td>$dcol
                </tr>
                </table>
                </td></tr>
                </table>
            </td>
            </tr>
            <tr><td>
                <table width="100%">
                <tr><td align="left" valign="bottom">
    
                ${sub}${xn}${hlp}${xn}${rst}${xn}${xprt}
    
                </td></tr>
                </table>
            <td>
            </tr>
            </table>
    
            <br clear="all">
    
XXXX;

        echo form_footer();
    } else if ($format == constFormatForm) {
        echo <<< XXXX

                <tr><td>Site:</td><td colspan="2">$sel_site</td></tr>
                <tr><td>Machine:</td><td colspan="2">$sel_host</td></tr>
                <tr><td>Update:</td><td colspan="2">$sel_ptch</td></tr>
                <tr><td>Status:</td><td colspan="2">$sel_stat</td></tr>
                <tr><td>Detect Date:</td><td colspan="2">$sel_dtec</td></tr>
                <tr><td>Download Date:</td><td colspan="2">$sel_dnld</td></tr>
                <tr><td>Install Date:</td><td colspan="2">$sel_inst</td></tr>
                <tr><td>Removal Date:</td><td colspan="2">$sel_unin</td></tr>
                <tr><td>Error:</td><td colspan="2">$sel_lerr</td></tr>
                <tr><td>Error Date:</td><td colspan="2">$sel_etim</td></tr>
                <tr><td>Date of Last Change:</td><td colspan="2">$sel_chng</td>
</tr>
                <tr><td>Date of Next Action:</td><td colspan="2">$sel_next</td>
</tr>
                <tr><td>Download Source:</td><td colspan="2">$sel_dsrc</td>
</tr>
                <tr><td>Update Size:</td><td colspan="2">$sel_size</td></tr>
                <tr><td>Machine Group:</td><td colspan="2">$sel_gid</td></tr>
                <tr><td>Update Group:</td><td colspan="2">$sel_jid</td></tr>
                <tr><td>MS Name:</td><td colspan="2">$sel_mtag</td></tr>
                <tr><td>Type:</td><td colspan="2">$sel_type</td></tr>
XXXX;
    }


    //     $txt = date_doc();
    //     $txt = fontspeak($txt);
    //     echo para($txt);
}


function restrict_time(&$env, &$trm, $code, $field)
{
    $valu = $env[$code];
    if ($valu > 0) {
        $time  = date_code($env, $valu);
        $trm[] = "$field > $time";
    }
    if ($valu == -2) {
        $trm[] = "$field = 0";
    }
}

function restrict_valu(&$env, &$trm, $code, $field)
{
    $valu = $env[$code];
    if ($valu == 1) {
        $trm[] = "$field = ''";
    }
    if ($valu == 2) {
        $trm[] = "$field != ''";
    }
}


function restrict_size(&$env, &$trm, $code, $field)
{
    $valu = $env[$code];
    $kb    = 1024;
    $kb100 = $kb * 100;
    $kb500 = $kb * 500;
    $mb    = $kb * $kb;
    $mb10  = $mb * 10;
    $mb50  = $mb * 50;
    $bet   = "$field between";
    switch ($valu) {
        case -2:
            $trm[] = "$field = 0";
            break;
        case  1:
            $trm[] = "$bet 0 and $kb100";
            break;
        case  2:
            $trm[] = "$bet $kb100 and $kb500";
            break;
        case  3:
            $trm[] = "$bet $kb500 and $mb";
            break;
        case  4:
            $trm[] = "$bet $mb and $mb10";
            break;
        case  5:
            $trm[] = "$bet $mb10 and $mb50";
            break;
        case  6:
            $trm[] = "$field > $mb50";
            break;
        default:;
    }
}


/*
    |  We want to use the same procedure to generate sql for both
    |  the counting and the selection of records.
    */

function gen_query(&$env, $count, $num, &$tables, &$where)
{
    $bug = $env['bug'];
    $gid = $env['gid'];
    $jid = $env['jid'];
    $cid = $env['cid'];
    $hid = $env['hid'];
    $mid = $env['mid'];
    $msn = $env['mtag'];
    $stt = $env['stat'];
    $type = $env['type'];

    $site = $env['site'];
    $auth = $env['auth'];
    $qu   = safe_addslashes($auth);
    if ($count) {
        $sel = "select count(S.patchstatusid) from";
    } else {
        $sel = WUST_GetSelectCols($list);
        $sel .= ' from';
    }
    $tab = array(
        'PatchStatus as S',
        $GLOBALS['PREFIX'] . 'core.Census as C',
        $GLOBALS['PREFIX'] . 'core.Customers as U',
        'Patches as P'
    );
    $trm = array(
        'S.id = C.id',
        'C.site = U.customer',
        "U.username = '$qu'",
        'S.patchid = P.patchid',
    );

    if (($cid > 0) && ($hid <= 0) && ($site)) {
        $trm[] = "U.id = $cid";
    }
    if ($hid > 0) $trm[] = "S.id = $hid";
    if ($stt > 0) $trm[] = "S.status = $stt";
    if ($mid > 0) $trm[] = "S.patchid = $mid";  // see msn
    if ($msn > 0) $trm[] = "S.patchid = $msn";  // see mid
    if ($type >= 0) $trm[] = "P.type = $type";

    if (($gid > 0) || (WUST_NeedsMachineGroup($env))) {
        $tab[] = $GLOBALS['PREFIX'] . 'core.MachineGroupMap as G';
        $tab[] = $GLOBALS['PREFIX'] . 'core.MachineGroups as Q';
        $trm[] = 'G.mgroupuniq = Q.mgroupuniq';
        $trm[] = 'G.censusuniq = C.censusuniq';
    }

    if ($gid > 0) {
        $trm[] = "Q.mgroupid = $gid";
    }
    if ($jid > 0) {
        $tab[] = $GLOBALS['PREFIX'] . 'softinst.PatchGroupMap as J';
        $trm[] = "J.pgroupid = $jid";
        $trm[] = 'J.patchid = S.patchid';
    }

    /*
        |  Microsoft has some bug where occasionally they detect a
        |  patch, and then stop mentioning it.  The client wrongly
        |  interprets this to mean the patch is installed, but
        |  with the install time set the same as the download time.
        |
        |  So ... we have this strange option that prevents
        |  normal users from seeing these invalid records.
        */

    if ($bug) {
        $stats = constPatchStatusInstalled;
        $xxxxx = '(S.lastdownload != S.lastinstall)';
        $yyyyy = "(S.status != $stats)";
        $trm[] = "($xxxxx or $yyyyy)";
    }
    restrict_time($env, $trm, 'dtec', 'detected');
    restrict_time($env, $trm, 'inst', 'lastinstall');
    restrict_time($env, $trm, 'unin', 'lastuninstall');
    restrict_time($env, $trm, 'chng', 'lastchange');
    restrict_time($env, $trm, 'dnld', 'lastdownload');
    restrict_time($env, $trm, 'etim', 'lasterrordate');
    restrict_time($env, $trm, 'next', 'nextaction');
    restrict_valu($env, $trm, 'lerr', 'lasterror');
    restrict_valu($env, $trm, 'dsrc', 'downloadsource');
    restrict_size($env, $trm, 'size', 'size');

    $tabs = join(",\n ", $tab);
    $trms = join("\n and ", $trm);

    $where = $trms;
    $tables = $tabs;

    if ($count) {
        $sql = "$sel\n $tabs\n where $trms";
    } else {
        $ord  = $env['ord'];
        $page = $env['page'];
        $limt = $env['limt'];
        $ords = order($ord, $env);
        $pmin = ($page > 0) ? $limt * $page : 0;
        if (($num <= $limt) || ($num <= $pmin)) {
            $pmin = 0;
        }
        $sql = "$sel\n $tabs\n where $trms\n"
            . " order by $ords\n"
            . " limit $pmin, $limt";
    }
    return $sql;
}



function find_status_count(&$env, $db)
{
    $num = 0;
    $sql = gen_query($env, 1, 0, $tables, $where);
    $res = redcommand($sql, $db);
    if ($res) {
        $num = mysqli_result($res, 0);
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    debug_note("There are $num total matching records.");
    return $num;
}


function find_host_name($host, $site, $db)
{
    $qs  = safe_addslashes($site);
    $qh  = safe_addslashes($host);
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census\n"
        . " where host = '$qh'\n"
        . " and site = '$qs'";
    return find_one($sql, $db);
}


/* create_host

        Adds an entry to the census table.  Note that this primitive has not
        been updated to use the new "uniq" fields, since this primitive is only
        used to create "fake" machines.
    */
function create_host($host, $site, $db)
{
    $qs  = safe_addslashes($site);
    $qh  = safe_addslashes($host);
    $now = time();
    $sql = "insert into\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census set\n"
        . " site = '$qs',\n"
        . " host = '$qh',\n"
        . " last = $now";
    $res = redcommand($sql, $db);
    $num = affected($res, $db);
    $has = ($num) ? 'has' : 'has not';
    if ($num) {
        $lastid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
        $err = PHP_DSYN_InvalidateRow(
            CUR,
            (int)$lastid,
            "id",
            constDataSetCoreCensus,
            constOperationInsert
        );
        if ($err != constAppNoErr) {
            logs::log(__FILE__, __LINE__, "create_host: PHP_DSYN_InvalidateRow returned "
                . $err, 0);
        }
    }
    debug_note("machine $host at $site $has been created.");
    return $num;
}


function find_status($hid, $mid, $db)
{
    $row = array();
    if (($hid > 0) && ($mid > 0)) {
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.PatchStatus\n"
            . " where id = $hid\n"
            . " and patchid = $mid";
        $row = find_one($sql, $db);
    }
    return $row;
}


function fake_host_records($hid, $db)
{
    $now = time();
    $sql = 'select patchid from Patches';
    $set = find_many($sql, $db);
    $day = 86400;
    $del = 10 * $day;
    reset($set);
    foreach ($set as $key => $row) {
        $mid = $row['patchid'];
        $row = find_status($hid, $mid, $db);
        $stt = mt_rand(1, constPatchStatusSuperseded);
        if ($row) {
            $ldd = $now - mt_rand(0, $del);
            $lch = $now - mt_rand(0, $day);
            $nxt = $now + mt_rand(0, $del);
            $lin = $now - mt_rand(0, $del);
            $lun = $now - mt_rand(0, $del);
            $sql = "update PatchStatus set\n"
                . " status = $stt,\n"
                . " lastdownload = $ldd,\n"
                . " lastuninstall = $lun,\n"
                . " lastinstall = $lin,\n"
                . " nextaction = $nxt,\n"
                . " lastchange = $lch\n"
                . " where id = $hid\n"
                . " and patchid = $mid";
        } else {
            $dtt = $now - mt_rand(0, $day);
            $sql = "insert into PatchStatus set\n"
                . " id = $hid,\n"
                . " patchid = $mid,\n"
                . " patchconfigid = 0,\n"
                . " status = $stt,\n"
                . " detected = $dtt,\n"
                . " downloadsource = 'MacroSoft'";
        }
        $res = redcommand($sql, $db);
    }
}


function build_fake_machine($host, $site, $db)
{
    $num = 0;
    $hid = 0;
    $row = find_host_name($host, $site, $db);
    if (!$row) {
        $num = create_host($host, $site, $db);
        $row = find_host_name($host, $site, $db);
    }

    if ($row) {
        $hid = $row['id'];
        $sql = "select * from Machine\n"
            . " where id = $hid";
        $row = find_one($sql, $db);
        if (!$row) {
            $now = time();
            $sql = "insert into Machine set\n"
                . " id = $hid,\n"
                . " lastcontact = $now,\n"
                . " lastchange = $now";
            $res = redcommand($sql, $db);
        }
    }

    if ($hid) {
        fake_host_records($hid, $db);
    }
    return $num;
}



function build_fake_site($site, $auth, $db)
{
    $qs  = safe_addslashes($site);
    $qu  = safe_addslashes($auth);
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers\n"
        . " where customer = '$qs'\n"
        . " and username = ''";
    $row = find_one($sql, $db);
    if (!$row) {
        $sql = "insert into\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers set\n"
            . " customer = '$qs',\n"
            . " username = ''";
        $res = redcommand($sql, $db);
    }
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers\n"
        . " where customer = '$qs'\n"
        . " and username = '$qu'";
    $row = find_one($sql, $db);
    if (!$row) {
        $sql = "insert into\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers set\n"
            . " customer = '$qs',\n"
            . " username = '$qu',\n"
            . " owner = 1";
        $res = redcommand($sql, $db);
    }
}


function fake_stats(&$env, $db)
{
    echo again($env);
    $auth = $env['auth'];
    $site = 'Another Fake Site';
    build_fake_site($site, $auth, $db);
    $x  = 0;
    $x += build_fake_machine('nanospork', $site, $db);
    $x += build_fake_machine('fuzzball', $site, $db);
    $x += build_fake_machine('gastropod', $site, $db);
    $x += build_fake_machine('stimpy', $site, $db);
    $x += build_fake_machine('cthulhu', $site, $db);
    if ($x) {
        groups_init($db, constGroupsInitFull);
    }
    echo again($env);
}


function sane_stats(&$env, $db)
{
    echo again($env);

    $sql = "select M.id from\n"
        . " " . $GLOBALS['PREFIX'] . "softinst.Machine as M\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.Census as C\n"
        . " on C.id = M.id\n"
        . " where C.id is NULL\n"
        . " group by M.id";
    $set = find_many($sql, $db);
    if ($set) {
        $num = safe_count($set);
        echo para("There are $num missing machines.");
        reset($set);
        foreach ($set as $key => $row) {
            $mid = $row['id'];
            $sql = "delete from\n"
                . " " . $GLOBALS['PREFIX'] . "softinst.Machine\n"
                . " where id = $mid";
            $res = redcommand($sql, $db);
            $num = affected($res, $db);
            debug_note("Machine $mid does not exist, $num records removed.");
        }
    } else {
        echo para($GLOBALS['PREFIX'] . 'oftinst.Machine.id: OK');
    }
    $sql = "select S.id from\n"
        . " " . $GLOBALS['PREFIX'] . "softinst.PatchStatus as S\n"
        . " left join " . $GLOBALS['PREFIX'] . "softinst.Machine as M\n"
        . " on M.id = S.id\n"
        . " where M.id is NULL\n"
        . " group by S.id";
    $set = find_many($sql, $db);
    if ($set) {
        $num = safe_count($set);
        echo para("There are $num missing machines.");
        reset($set);
        foreach ($set as $key => $row) {
            $mid = $row['id'];
            $sql = "delete from\n"
                . " " . $GLOBALS['PREFIX'] . "softinst.PatchStatus\n"
                . " where id = $mid";
            $res = redcommand($sql, $db);
            $num = affected($res, $db);
            debug_note("Machine $mid does not exist, $num records removed.");
        }
    } else {
        echo para($GLOBALS['PREFIX'] . 'softinst.PatchStatus.id: OK');
    }
    $sql = "select S.patchid from\n"
        . " " . $GLOBALS['PREFIX'] . "softinst.PatchStatus as S\n"
        . " left join " . $GLOBALS['PREFIX'] . "softinst.Patches as P\n"
        . " on P.patchid = S.patchid\n"
        . " where P.patchid is NULL\n"
        . " group by S.patchid";
    $set = find_many($sql, $db);
    if ($set) {
        $num = safe_count($set);
        echo para("There are $num missing patches.");
        reset($set);
        foreach ($set as $key => $row) {
            $pid = $row['patchid'];
            $sql = "delete from\n"
                . " " . $GLOBALS['PREFIX'] . "softinst.PatchStatus\n"
                . " where patchid = $pid";
            $res = redcommand($sql, $db);
            $num = affected($res, $db);
            debug_note("Patch $pid does not exist, $num records removed.");
        }
    } else {
        echo para($GLOBALS['PREFIX'] . 'softinst.PatchStatus.patchid: OK');
    }
    echo again($env);
}


function list_stats(&$env, $db)
{
    $num = find_status_count($env, $db);
    $sql = gen_query($env, 0, $num, $tables, $where);
    $set = find_many($sql, $db);
    echo mark('control');
    echo again($env, $db);
    stats_blurb($env);
    status_control($env, constFormatTable, $db);
    echo mark('table');
    echo again($env);
    if ($set) {
        $tmp = safe_count($set);
        debug_note("There were $tmp records loaded.");
        stats_table($env, $set, $num);
    } else {
        echo para('There were no matching status records ...');
    }
    echo again($env);
}


function debug_stats(&$env, $db)
{
    echo again($env);
    $lim = $env['limt'];
    $wrd = order(24, $env);
    $sql = "select C.host, S.* from\n"
        . " PatchStatus as S,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
        . " where C.id = S.id\n"
        . " order by $wrd\n"
        . " limit $lim";
    $set = find_many($sql, $db);
    if ($set) {
        $rows = safe_count($set);
        $head = explode('|', 'host|psid|pid|id|pcfg|lcfg|stat|inst|next|chng|unin|dtec|dnld|err|etim');
        $cols = safe_count($head);
        $text = "$rows records found";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($head, 1);
        reset($set);
        foreach ($set as $key => $row) {
            $host = $row['host'];
            $psid = $row['patchstatusid'];
            $pid  = $row['patchid'];
            $pcfg = $row['patchconfigid'];
            $lcfg = $row['lastconfigid'];
            $id   = $row['id'];
            $dsrc = disp($row, 'downloadsource');
            $lerr = disp($row, 'lasterror');
            $stat = status($row['status'], $psid);
            $inst = timestamp($row, 'lastinstall');
            $unin = timestamp($row, 'lastuninstall');
            $next = timestamp($row, 'nextaction');
            $dtec = timestamp($row, 'detected');
            $dnld = timestamp($row, 'lastdownload');
            $chng = timestamp($row, 'lastchange');
            $etim = timestamp($row, 'lasterrordate');
            $args = array($host, $psid, $pid, $id, $pcfg, $lcfg, $stat, $inst, $next, $chng, $unin, $dtec, $dnld, $lerr, $etim);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
    echo again($env);
}


function debug_menu($env, $db)
{
    echo again($env);
    $self = $env['self'];
    $cmd  = "$self?act";

    $act = array();
    $txt = array();

    $act[] = "$self?debug=0";
    $txt[] = 'Normal Status Page. (no debug)';

    $act[] = "$self?debug=1";
    $txt[] = 'Normal Status Page. (debug view)';

    $act[] = "$cmd=dbug";
    $txt[] = 'This very list.';

    $act[] = "$cmd=sane&debug=1";
    $txt[] = 'Consistancy Check.';

    $act[] = "$cmd=dlst&l=200&debug=1";
    $txt[] = 'Debug Patch Status';

    $act[] = "$cmd=fake&debug=1";
    $txt[] = 'Build Fake Machines';
    if ($txt) {
        echo "<ol>\n";
        reset($txt);
        foreach ($txt as $key => $doc) {
            $cmd = html_link($act[$key], $doc);
            echo "<li>$cmd</li>\n";
        }
        echo "</ol>\n";
    }
    echo again($env);
}


function tag_int($name, $min, $max, $def)
{
    $valu = get_integer($name, $def);
    return value_range($min, $max, $valu);
}


/* WUST_ReportConfig

        Prints out the configuration form for the status report.
    */
function WUST_ReportConfig($env, $db)
{
    $dateHelp = 'date format is mm/dd or mm/dd/yy or mm/dd hh:mm.  Only '
        . 'needed if no dates are selected below.';
    echo post_other('myform', $env['self'] . $env['jump']);
    echo "<table border=\"0\" cellspacing=\"2\" cellpadding=\"2\">\n";
    echo pretty_header('Create Update Report', 3);
    echo WUST_PrintControl(
        'Report name:',
        textbox('rname', 50, ''),
        'Enter the name of this Microsoft Update Management report.  '
            . 'The name can be up to 50 characters long.'
    );
    echo WUST_PrintControl('Destination:', 'Display Immediately '
        . checkbox('dispimd', 1) . indent(10) . 'Send e-mail '
        . checkbox('sende', 0), 'You can select more than one option');
    echo WUST_PrintControl(
        'E-mail recipients:',
        textbox('email', 50, ''),
        'Comma separated list of email addresses with no spaces.'
    );
    echo WUST_PrintControl('Default e-mail recipients:', checkbox(
        'demail',
        0
    ), 'Add default email list members to the list of e-mail '
        . 'recipients.');
    echo WUST_PrintControl(
        'Start Date:',
        textbox('sdate', 20, ''),
        $dateHelp
    );
    echo WUST_PrintControl(
        'End Date:',
        textbox('edate', 20, ''),
        $dateHelp
    );
    echo WUST_PrintControl('&nbsp;', '<font size="-2"><i>Use the pull-down'
        . ' lists below to:</i></font><br><font size="-2"><i>'
        . '1) Choose software update selection criteria.  Any software '
        . 'update parameter you use as a selection criterion will also be '
        . 'included in the detail section of your report.</i></font>'
        . '<br><font size="-2"><i>'
        . '2) Select software update related parameters to be included in '
        . 'the detail section of your report.</i></font></td></tr>', '');
    status_control($env, constFormatForm, $db);
    echo '<tr><td>Show details:</td><td>' . checkbox('details', 1)
        . '</td></tr>';
    $orders = WUST_GetAllOrderColumns(false, true);
    echo '<tr><td>Group and sort by:</td><td>' . html_select(
        'group1',
        $orders,
        constNoneStr,
        1
    ) . '</td></tr>';
    echo '<tr><td>Within that, group and sort by:</td><td>'
        . html_select('group2', $orders, constNoneStr, 1) . '</td></tr>';
    echo '<tr><td>Within that, group and sort by:</td><td>'
        . html_select('group3', $orders, constNoneStr, 1) . '</td></tr>';

    /* Get the correct ORDER BY settings */
    $orders = WUST_GetAllOrderColumns(false, false);
    order($env['ord'], $env);
    echo '<tr><td>Within that, sort by:</td><td>' . html_select(
        'order1',
        $orders,
        $env['order1'],
        1
    ) . '</td></tr>';
    echo '<tr><td>Within that, sort by:</td><td>' . html_select(
        'order2',
        $orders,
        $env['order2'],
        1
    ) . '</td></tr>';

    /* order3 is disabled, but can easily be put back in if needed */
    //echo '<tr><td>Within that, order by:</td><td>' . html_select('order3',
    //    $orders, $env['order3'], 1) . '</td></tr>';

    echo '<tr><td>' . button(constButtonGen) . '</td><td>'
        . button(constButtonCancel) . '</td></tr>';
    echo "</table>\n";

    echo form_footer();
}


/* WUST_GetAllGroupColumns

        Returns all columns that can be grouped (or ordered) for the update
        status report.
    */
function WUST_GetAllGroupColumns()
{
    return array(
        constNoneStr => constNoneStr,
        'S.lastchange' => 'Date of Last Change',
        'S.nextaction' => 'Date of Next Action',
        'S.detected' => 'Detect Date',
        'S.lastdownload' => 'Download Date',
        'S.downloadsource' => 'Download Source',
        'S.lasterror' => 'Error',
        'S.lasterrordate' => 'Error Date',
        'S.lastinstall' => 'Install Date',
        'C.host' => 'Machine',
        'Q.name' => 'Machine Group',
        'P.msname' => 'MS Name',
        'S.lastuninstall' => 'Removal Date',
        'C.site' => 'Site',
        'P.size' => 'Size',
        'S.status' => 'Status',
        'P.type' => 'Type',
        'P.name' => 'Update'
    );
}


/* WUST_GetAllOrderColumns

        Returns an array of ORDER BY columns, with "friendly" names and
        database clauses when $full is true, otherwise simply returns the
        database clauses.
    */
function WUST_GetAllOrderColumns($full, $getgroup = true)
{
    $orders = WUST_GetAllGroupColumns();
    $retlist = array();
    foreach ($orders as $key => $row) {
        if (strcmp($key, constNoneStr) != 0) {
            $key1 = 'CONVERT(' . $key . ' USING latin1) ASC';
            $key2 = '`CONVERT(' . $key . ' USING latin1)` ASC';
            $select = 'CONVERT(' . $key . ' USING latin1)';
            $row2 = $row . ' (ascending)';
            if ($full) {
                $retlist[$key2] = array();
                $retlist[$key2]['dbname'] = $key;
                $retlist[$key2]['sname'] = $row;
                $retlist[$key2]['fname'] = $row2;
                $retlist[$key2]['selname'] = $select;
                $retlist[$key1] = array();
                $retlist[$key1]['dbname'] = $key;
                $retlist[$key1]['sname'] = $row;
                $retlist[$key1]['fname'] = $row2;
                $retlist[$key1]['selname'] = $select;
            } else {
                if ($getgroup) {
                    $retlist[$key2] = $row2;
                } else {
                    $retlist[$key1] = $row2;
                }
            }
            $key1 = 'CONVERT(' . $key . ' USING latin1) DESC';
            $key2 = '`CONVERT(' . $key . ' USING latin1)` DESC';
            $select = 'CONVERT(' . $key . ' USING latin1)';
            $row2 = $row . ' (descending)';
            if ($full) {
                $retlist[$key2] = array();
                $retlist[$key2]['dbname'] = $key;
                $retlist[$key2]['sname'] = $row;
                $retlist[$key2]['fname'] = $row2;
                $retlist[$key2]['selname'] = $select;
                $retlist[$key1] = array();
                $retlist[$key1]['dbname'] = $key;
                $retlist[$key1]['sname'] = $row;
                $retlist[$key1]['fname'] = $row2;
                $retlist[$key1]['selname'] = $select;
            } else {
                if ($getgroup) {
                    $retlist[$key2] = $row2;
                } else {
                    $retlist[$key1] = $row2;
                }
            }
        } else {
            if ($full) {
                $retlist[constNoneStr] = array();
                $retlist[constNoneStr]['dbname'] = constNoneStr;
                $retlist[constNoneStr]['sname'] = constNoneStr;
                $retlist[constNoneStr]['fname'] = constNoneStr;
                $retlist[constNoneStr]['selname'] = constNoneStr;
            } else {
                $retlist[constNoneStr] = constNoneStr;
            }
        }
    }
    return $retlist;
}


/* WUST_GetInitializedString

        Gets the posted variable named $strname.  If the value is non-existent
        or empty, will return 'None'.
    */
function WUST_GetInitializedString($strname)
{
    $str = get_string($strname, '');
    if (strcmp('', $str) == 0) {
        $str = constNoneStr;
    }
    return $str;
}


/* WUST_MakeReport

        Generates the update status report itself.
    */
function WUST_MakeReport($env, $db)
{
    gen_query($env, 1, 0, $tables, $where);
    /* First, we need to create the primary report structure */
    $thisReport = WUST_CreateReportParam($env, $db);
    if ($thisReport == 0) {
        return;
    }

    $restrictTime = false;
    $operands = array();

    $timeStr = '((lastchange<operand><date> AND lastchange!=0) OR '
        . '(detected<operand><date> AND detected!=0) OR '
        . '(lastinstall<operand><date> AND lastinstall!=0) OR '
        . '(lastuninstall<operand><date> AND lastuninstall!=0) OR '
        . '(nextaction<operand><date> AND nextaction!=0) OR '
        . '(lastdownload<operand><date> AND lastdownload!=0) OR '
        . '(lasterrordate<operand><date> AND lasterrordate!=0))';
    $timeStr2 = '((lastchange<operand><date> AND '
        .       'lastchange<operand2><date2>) OR '
        . '(detected<operand><date> AND detected<operand2><date2>) OR '
        . '(lastinstall<operand><date> AND '
        .       'lastinstall<operand2><date2>) OR '
        . '(lastuninstall<operand><date> AND '
        .       'lastuninstall<operand2><date2>) OR '
        . '(nextaction<operand><date> AND nextaction<operand2><date2>) OR '
        . '(lastdownload<operand><date> AND '
        .       'lastdownload<operand2><date2>!=0) OR '
        . '(lasterrordate<operand><date> AND '
        .       'lasterrordate<operand2><date2>))';
    $addStr1 = '';
    $addStr2 = '';
    $now = time();

    /* Append additional time restrictions to the where clause */
    if (strcmp($env['sdate'], '') != 0) {
        /* Barrier for starting activity, at least one date must be at
                least the value of 'sdate' */
        $restrictTime = true;
        $addStr1 = str_replace('<operand>', '>=', $timeStr);
        $addStr2 = str_replace('<operand>', '>=', $timeStr2);

        $umin = parsedate($env['sdate'], $now);
        if ($umin == 0) {
            echo 'Did not understand the format of the start time';
            return;
        } else {
            $addStr1 = str_replace('<date>', $umin, $addStr1);
            $addStr2 = str_replace('<date>', $umin, $addStr2);
        }
    }
    if (strcmp($env['edate'], '') != 0) {
        /* Barrier for ending activity, at least one date must be at
                most the value of 'edate' */
        $restrictTime = true;
        if (strcmp($addStr2, '') != 0) {
            $addStr2 = str_replace('<operand2>', '<=', $addStr2);
        }
        $addStr1 = str_replace('<operand>', '>=', $timeStr);
        $umin = parsedate($env['edate'], $now);
        if ($umin == 0) {
            echo 'Did not understand the format of the end time';
            return;
        } else {
            $addStr1 = str_replace('<date>', $umin, $addStr1);
            if (strcmp($addStr2, '') != 0) {
                $addStr2 = str_replace('<date2>', $umin, $addStr2);
            }
        }
    } else {
        $addStr2 = '';
    }

    if ($restrictTime) {
        if (strcmp($where, '') != 0) {
            $where .= ' AND ';
        }
        if (strcmp($addStr2, '') != 0) {
            $where .= $addStr2;
        } else {
            $where .= $addStr1;
        }
    }

    $group = '';
    $order = '';
    $numGroup = 0;
    $numOrder = 0;

    WUST_AppendToString($group, $numGroup, $env['group1']);
    WUST_AppendToString($group, $numGroup, $env['group2']);
    WUST_AppendToString($group, $numGroup, $env['group3']);

    WUST_AppendToString($order, $numOrder, $env['order1']);
    WUST_AppendToString($order, $numOrder, $env['order2']);
    WUST_AppendToString($order, $numOrder, $env['order3']);
    WUST_AppendToString($order, $numOrder, $env['order4']);

    /* Create the summary component:
                Values          location 0
        */
    $sumGroup = $numGroup;
    if ($numGroup == 0) {
        $sumGroup = 2;
        /* Force site, host grouping for summary */
        $compIdx = REPT_AddComponent(
            $thisReport,
            'softinst',
            $tables,
            '<h3>Summary</h3>',
            'border="0"',
            $where,
            '',
            'C.site, C.host',
            '',
            1,
            true
        );
        if ($compIdx == -1) {
            return;
        }

        if (WUST_AddSummaryGroupCol(
            'CONVERT(C.site USING latin1) ASC',
            0,
            $thisReport,
            $compIdx,
            0,
            1,
            0,
            $sumGroup
        ) == -1) {
            return;
        }

        if (WUST_AddSummaryGroupCol(
            'CONVERT(C.host USING latin1) ASC',
            2,
            $thisReport,
            $compIdx,
            1,
            0,
            -1,
            $sumGroup
        ) == -1) {
            return;
        }
    } else {
        /* Use custom grouping */
        $compIdx = REPT_AddComponent(
            $thisReport,
            'softinst',
            $tables,
            '<h3>Summary</h3>',
            'border="0"',
            $where,
            '',
            $group,
            '',
            $numGroup - 1,
            true
        );
        if ($compIdx == -1) {
            return;
        }
        if ($numGroup > 1) {
            $grpIdx = 0;
            $rank = 0;
        } else {
            $grpIdx = -1;
            $rank = 2;
        }
        /* Group 1 */
        if (WUST_AddSummaryGroupCol(
            $env['group1'],
            $rank,
            $thisReport,
            $compIdx,
            0,
            $numGroup - 1,
            $grpIdx,
            $numGroup
        ) == -1) {
            return;
        }

        /* Group 2 */
        if ($numGroup <= 2) {
            $grpIdx = -1;
            $rank = 2;
        } else {
            $grpIdx = 1;
            $rank = 1;
        }
        if (WUST_AddSummaryGroupCol(
            $env['group2'],
            $rank,
            $thisReport,
            $compIdx,
            1,
            $numGroup - 2,
            $grpIdx,
            $numGroup
        ) == -1) {
            return;
        }

        /* Group 3 */
        if (WUST_AddSummaryGroupCol(
            $env['group3'],
            2,
            $thisReport,
            $compIdx,
            2,
            0,
            -1,
            $numGroup
        ) == -1) {
            return;
        }
    }

    /* Number of updates installed */
    if (WUST_AddSummaryColumn($thisReport, $compIdx, 'count(S.status='
        . constPatchStatusInstalled . ' OR NULL)', 'Updates '
        . 'Installed', $sumGroup) == -1) {
        return;
    }

    /* % installed */
    if (WUST_AddSummaryColumn(
        $thisReport,
        $compIdx,
        'ROUND((count(S.status'
            . '=' . constPatchStatusInstalled . ' OR NULL)/count(*))*100)',
        '% Installed',
        $sumGroup + 1
    ) == -1) {
        return;
    }

    /* Number of updates downloaded */
    if (WUST_AddSummaryColumn($thisReport, $compIdx, 'count(S.status='
        . constPatchStatusDownloaded . ' OR NULL)', 'Updates '
        . 'Downloaded', $sumGroup + 2) == -1) {
        return;
    }

    /* % downloaded */
    if (WUST_AddSummaryColumn(
        $thisReport,
        $compIdx,
        'ROUND((count(S.status'
            . '=' . constPatchStatusDownloaded . ' OR NULL)/count(*))*100)',
        '% Downloaded',
        $sumGroup + 3
    ) == -1) {
        return;
    }

    /* Number of updates detected */
    if (WUST_AddSummaryColumn($thisReport, $compIdx, 'count(S.status='
        . constPatchStatusDetected . ' OR NULL)', 'Updates '
        . 'Detected', $sumGroup + 4) == -1) {
        return;
    }

    /* % detected */
    if (WUST_AddSummaryColumn(
        $thisReport,
        $compIdx,
        'ROUND((count(S.status'
            . '=' . constPatchStatusDetected . ' OR NULL)/count(*))*100)',
        '% Detected',
        $sumGroup + 5
    ) == -1) {
        return;
    }

    /* Number of updates superseded */
    if (WUST_AddSummaryColumn($thisReport, $compIdx, 'count(S.status='
        . constPatchStatusSuperseded . ' OR NULL)', 'Updates '
        . 'Superseded', $sumGroup + 6) == -1) {
        return;
    }

    /* % superseded */
    if (WUST_AddSummaryColumn(
        $thisReport,
        $compIdx,
        'ROUND((count(S.status'
            . '=' . constPatchStatusSuperseded . ' OR NULL)/count(*))*100)',
        '% Superseded',
        $sumGroup + 7
    ) == -1) {
        return;
    }

    /* Number of "other" updates */
    if (WUST_AddSummaryColumn(
        $thisReport,
        $compIdx,
        'count(*) - ('
            . 'count(S.status=' . constPatchStatusDetected . ' OR NULL) + '
            . 'count(S.status=' . constPatchStatusDownloaded . ' OR NULL) + '
            . 'count(S.status=' . constPatchStatusInstalled . ' OR NULL) + '
            . 'count(S.status=' . constPatchStatusSuperseded . ' OR NULL))',
        'Other Updates ',
        $sumGroup + 8
    ) == -1) {
        return;
    }

    /* % "other" */
    if (WUST_AddSummaryColumn($thisReport, $compIdx, 'ROUND(((count(*) - ('
        . 'count(S.status=' . constPatchStatusDetected . ' OR NULL) + '
        . 'count(S.status=' . constPatchStatusDownloaded . ' OR NULL) + '
        . 'count(S.status=' . constPatchStatusInstalled . ' OR NULL) + '
        . 'count(S.status=' . constPatchStatusSuperseded . ' OR NULL)))/'
        . 'count(*))*100)', '% Other', $sumGroup + 9) == -1) {
        return;
    }

    /* Total No. of updates */
    if (WUST_AddSummaryColumn(
        $thisReport,
        $compIdx,
        'count(*)',
        'Updates Total',
        $sumGroup + 10
    ) == -1) {
        return;
    }

    if ($env['details'] == 1) {
        $colText = WUST_GetAllOrderColumns(true)
            + WUST_GetAllGroupColumns();

        /* Details section:

                Details (component header)
                Group1 Name             (location 3)
                    Group2 Name         (location 2)
                        Group3 Name     (location 1)
                            Values      (location 0)
            */
        $fullOrder = $group;
        if ((strcmp($fullOrder, '') != 0) && (strcmp($order, '') != 0)) {
            $fullOrder .= ',';
        }
        $fullOrder .= $order;

        /* Now, although the GROUP BY clause requires quoted identifiers
                to function properly (when combined with ROLLUP GROUP BY loses
                the ability to run CONVERT) the ORDER BY must not use quoted
                identifiers.  This is not the cleanest way to do this, but it
                is the simplest: remove all identifiers. */
        $fullOrder = str_replace('`', '', $fullOrder);
        $compIdx = REPT_AddComponent(
            $thisReport,
            'softinst',
            $tables,
            '<h3>Details</h3>',
            'border="0"',
            $where,
            '',
            '',
            $fullOrder,
            $numGroup,
            false
        );
        if ($compIdx == -1) {
            return;
        }

        $startLocation = 0;
        $groupIndex = -1;

        if ((strcmp($env['group3'], constNoneStr) != 0) &&
            (strcmp($env['group3'], '') != 0)
        ) {
            $startLocation = 3;
            $groupIndex++;
            $colDB = WUST_TranslateDB($colText[$env['group3']]['dbname']);
            $colFormat = WUST_GetSpecialColFormat(
                $colText[$env['group3']]['dbname'],
                constReptColFormatBoldIndent
            );
            if (REPT_AddComponentCol(
                $thisReport,
                $compIdx,
                $colDB,
                $groupIndex,
                '',
                $colFormat,
                constReptColFormatNone,
                $startLocation - 2,
                constReptLocFormatDbValue,
                $groupIndex,
                'colspan="2"',
                '',
                constSepBlankLine,
                0
            ) == -1) {
                return;
            }
        }

        if ((strcmp($env['group2'], constNoneStr) != 0) &&
            (strcmp($env['group2'], '') != 0)
        ) {
            if ($startLocation == 0) {
                $startLocation = 2;
            }
            $groupIndex++;
            $colDB = WUST_TranslateDB($colText[$env['group2']]['dbname']);
            $colFormat = WUST_GetSpecialColFormat(
                $colText[$env['group2']]['dbname'],
                constReptColFormatBoldIndent
            );
            if (REPT_AddComponentCol(
                $thisReport,
                $compIdx,
                $colDB,
                $groupIndex,
                '',
                $colFormat,
                constReptColFormatNone,
                $startLocation - 1,
                constReptLocFormatDbValue,
                $groupIndex,
                'colspan="2"',
                '',
                constSepBlankLine,
                0
            ) == -1) {
                return;
            }
        }

        if ((strcmp($env['group1'], constNoneStr) != 0) &&
            (strcmp($env['group1'], '') != 0)
        ) {
            if ($startLocation == 0) {
                $startLocation = 1;
            }
            $groupIndex++;
            $colDB = WUST_TranslateDB($colText[$env['group1']]['dbname']);
            $colFormat = WUST_GetSpecialColFormat(
                $colText[$env['group1']]['dbname'],
                constReptColFormatHeader
            );
            if (REPT_AddComponentCol(
                $thisReport,
                $compIdx,
                $colDB,
                $groupIndex,
                '',
                $colFormat,
                constReptColFormatNone,
                $startLocation,
                constReptLocFormatDbValue,
                $groupIndex,
                'colspan="2"',
                '',
                constSepBlankLine,
                0
            ) == -1) {
                return;
            }
        }

        if (
            WUST_AddDetailCols($thisReport, $compIdx, $startLocation, $env)
            == -1
        ) {
            return;
        }
    }

    if (PHP_REPT_GenerateReport(
        CUR,
        $html,
        $thisReport,
        $env['user']['username']
    ) != constAppNoErr) {
        REPT_PrintError();
        return;
    }

    if (PHP_REPT_FreeReport(CUR, $thisReport) != constAppNoErr) {
        REPT_PrintError();
        return;
    }

    if ($env['dispimd']) {
        echo WUST_GetReportHeader($env, true);
    }

    echo $html;
}


/* WUST_AppendToString

        Appends $cmpStr to $str if it is non-empty and not 'None'.  If $str
        already has text, appends ', ' to $str first.  Increments $num by 1
        if it appends $cmpStr.
    */
function WUST_AppendToString(&$str, &$num, $cmpStr)
{
    if ((strcmp($cmpStr, '') != 0) &&
        (strcmp($cmpStr, constNoneStr) != 0)
    ) {
        $num++;
        if (strcmp($str, '') != 0) {
            $str .= ', ';
        }
        $str .= $cmpStr;
    }
}


/* WUST_GetSelectCols

        Returns in $list an array of all the columns that could be used in the
        update report.  Maintains "backwards compatibility" with the search/
        sort table by also returning a string of the select columns.
    */
function WUST_GetSelectCols(&$list)
{
    $list = array(
        'C.site', 'C.host', 'P.name', 'P.msname', 'P.size', 'P.type',
        'S.patchstatusid', 'S.id', 'S.patchid', 'S.patchconfigid',
        'S.lastconfigid', 'S.lastchange', 'S.detected', 'S.status',
        'S.lastinstall', 'S.lastuninstall', 'S.nextaction', 'S.lastdownload',
        'S.downloadsource', 'S.lasterror', 'S.lasterrordate'
    );
    return "select C.site, C.host,\n"
        . " P.name, P.msname, P.size, P.type, S.*";
}


/* WUST_CreateReportParam

        Builds the report structure for REPT.
    */
function WUST_CreateReportParam($env, $db)
{
    $thisReport = 0;
    if (PHP_ALST_MakeAList(CUR, $params) != constAppNoErr) {
        REPT_PrintError();
        return 0;
    }
    if (PHP_ALST_SetNamedItemString(
        CUR,
        $params,
        constReptNameParam,
        $env['rname']
    ) != constAppNoErr) {
        REPT_PrintError();
        return 0;
    }
    if (PHP_ALST_SetNamedItemUInt32(
        CUR,
        $params,
        constReptDestEmailParam,
        $env['sende']
    ) != constAppNoErr) {
        REPT_PrintError();
        return 0;
    }
    if (PHP_ALST_SetNamedItemUInt32(
        CUR,
        $params,
        constReptDestDisplayParam,
        $env['dispimd']
    ) != constAppNoErr) {
        REPT_PrintError();
        return 0;
    }
    if (PHP_ALST_SetNamedItemUInt32(
        CUR,
        $params,
        constReptDestInfoPortalParam,
        0
    ) != constAppNoErr) {
        REPT_PrintError();
        return 0;
    }
    if (PHP_ALST_SetNamedItemString(
        CUR,
        $params,
        constReptEmailListParam,
        $env['email']
    ) != constAppNoErr) {
        REPT_PrintError();
        return 0;
    }
    if (PHP_ALST_SetNamedItemUInt32(
        CUR,
        $params,
        constReptEmailDefaultParam,
        $env['demail']
    ) != constAppNoErr) {
        REPT_PrintError();
        return 0;
    }
    if (
        PHP_ALST_SetNamedItemUInt32(
            CUR,
            $params,
            constReptOutputFormatParam,
            constReptOutputFormatHTML
        )
        != constAppNoErr
    ) {
        REPT_PrintError();
        return 0;
    }
    $now = time();
    $date = datestring($now);
    if (strcmp($env['rname'], '') == 0) {
        $name = 'MUM Update Status Report_User_';
    } else {
        $name = $env['rname'] . '_User_';
    }

    $name .= $env['user']['username'];
    $name = date('Y-m-d', $now) . "_$name.html";
    $fileName = mime_filename($name);
    $bodyText = '<html><body>Please find the attached report.<br><br>'
        . 'Server: ' . server_name($db) . '<br>'
        . 'Creation Date: ' . $date . '<br>'
        . 'File Name: ' . $fileName . '</body></html>';
    if (strcmp($env['rname'], '') == 0) {
        $subject = 'Report: MUM Update Status Report for User '
            . $env['user']['username'];
    } else {
        $subject = 'Report: ' . $env['rname'] . ' for User '
            . $env['user']['username'];
    }
    $from = 'report@' . server_name($db);
    if (PHP_ALST_SetNamedItemString(
        CUR,
        $params,
        constReptEmailBodyTextParam,
        $bodyText
    ) != constAppNoErr) {
        REPT_PrintError();
        return 0;
    }
    if (PHP_ALST_SetNamedItemString(
        CUR,
        $params,
        constReptEmailSubjectParam,
        $subject
    ) != constAppNoErr) {
        REPT_PrintError();
        return 0;
    }
    if (PHP_ALST_SetNamedItemString(
        CUR,
        $params,
        constReptEmailAttachFileNameParam,
        $fileName
    ) != constAppNoErr) {
        REPT_PrintError();
        return 0;
    }
    if (PHP_ALST_SetNamedItemString(
        CUR,
        $params,
        constReptEmailFromParam,
        $from
    ) != constAppNoErr) {
        REPT_PrintError();
        return 0;
    }
    $header = WUST_GetReportHeader($env, true);
    if (PHP_ALST_SetNamedItemString(
        CUR,
        $params,
        constReptEmailAttachBegin,
        $header
    ) != constAppNoErr) {
        REPT_PrintError();
        return 0;
    }
    if (
        PHP_REPT_CreateReportParam(CUR, $thisReport, $params)
        != constAppNoErr
    ) {
        REPT_PrintError();
        return 0;
    }
    if (PHP_ALST_FreeEntireAList(CUR, $params) != constAppNoErr) {
        PHP_REPT_FreeReport(CUR, $thisReport);
        REPT_PrintError();
        return 0;
    }

    return $thisReport;
}


/* WUST_AddSummaryColumn

        Adds a summary column for the summary component of the report.
    */
function WUST_AddSummaryColumn(
    $thisReport,
    $compIdx,
    $dbName,
    $colName,
    $colIdx,
    $location = 0,
    $grpIndex = -1
) {
    $myArray = array();
    $myArray[2]['value'] = '(empty)';
    $myArray[2]['dheader'] = 1;
    $myArray[2]['sep'] = '';
    $myArray[3]['value'] = '(empty)';
    $myArray[3]['dheader'] = 1;
    $myArray[3]['sep'] = '';
    $nullHandle = REPT_CreateNullHandleList($myArray);
    if ($nullHandle == 0) {
        return -1;
    }

    $colDB = WUST_TranslateDB($dbName);
    $colFormat = WUST_GetSpecialColFormat($dbName, constReptColFormatNone);
    return REPT_AddComponentCol(
        $thisReport,
        $compIdx,
        $colDB,
        $colIdx,
        $colName,
        $colFormat,
        constReptColFormatBold,
        $location,
        constReptLocFormatWide,
        $grpIndex,
        'align="right"',
        'align="center"',
        '',
        $nullHandle
    );
}


/* WUST_TranslateDB

        Translates the special status and type columns to user-readable
        strings.
    */
function WUST_TranslateDB($dbName)
{
    $translate = $dbName;
    $useIF = 0;
    /* Special text handling gets added here */
    if ((strcmp($dbName, 'S.status') == 0) ||
        (strcmp($dbName, 'CONVERT(S.status USING latin1)') == 0)
    ) {
        $opts = status_opt();
        $useIF = 1;
    } else if ((strcmp($dbName, 'P.type') == 0) ||
        (strcmp($dbName, 'CONVERT(P.type USING latin1)') == 0)
    ) {
        $opts = PTCH_GetAllTypes();
        $useIF = 1;
    }

    if ($useIF) {
        $translate = '';
        $count = 0;
        foreach ($opts as $key => $value) {
            $translate .= "IF($dbName=$key,'$value',";
            $count++;
        }
        $translate .= '\'\'';
        $translate .= str_repeat(')', $count);
        $translate .= ' AS `' . $dbName . '`';
    }

    return $translate;
}


/* WUST_AddDetailCols

        Adds columns to the detail section of the report if they are not
        already one of the three group by columns.
    */
function WUST_AddDetailCols($thisReport, $compIdx, $startLocation, $env)
{
    $cols = WUST_GetAllGroupColumns();
    $all = WUST_GetAllOrderColumns(true);
    $count = 0;

    foreach ($cols as $dbName => $friendlyName) {
        /* Add the column if it is not part of a group by */
        if ((strcmp($all[$env['group1']]['dbname'], $dbName) != 0) &&
            (strcmp($all[$env['group2']]['dbname'], $dbName) != 0) &&
            (strcmp($all[$env['group3']]['dbname'], $dbName) != 0)
        ) {
            if ((strcmp(constNoneStr, $dbName) != 0) &&
                (strcmp('', $dbName) != 0)
            ) {
                $canDisplay = WUST_CanDisplay($dbName, $env);
                if ($canDisplay) {
                    $colDB = WUST_TranslateDB($dbName);
                    $colFormat = WUST_GetSpecialColFormat(
                        $dbName,
                        constReptColFormatNoneIndent
                    );
                    if (REPT_AddComponentCol(
                        $thisReport,
                        $compIdx,
                        $colDB,
                        $startLocation + $count,
                        $friendlyName,
                        $colFormat,
                        constReptColFormatNoneIndent,
                        0,
                        constReptLocFormatHigh,
                        -1,
                        '',
                        '',
                        constSepBlankLine,
                        0
                    ) == -1) {
                        return -1;
                    }
                    $count++;
                }
            }
        }
    }

    return 0;
}


/* WUST_CanDisplay

        Determines if the column $dbName can be displayed in the detail
        section or not.
    */
function WUST_CanDisplay($dbName, $env)
{
    $envvar = '';
    if (strcmp($dbName, 'C.site') == 0) {
        $envvar = 'd_cid';
    } else if (strcmp($dbName, 'C.host') == 0) {
        $envvar = 'd_hid';
    } else if (strcmp($dbName, 'P.msname') == 0) {
        $envvar = 'd_mtag';
    } else if (strcmp($dbName, 'P.type') == 0) {
        $envvar = 'd_type';
    } else if (strcmp($dbName, 'P.name') == 0) {
        $envvar = 'd_name';
    } else if (strcmp($dbName, 'S.status') == 0) {
        $envvar = 'd_stat';
    } else if (strcmp($dbName, 'P.size') == 0) {
        $envvar = 'd_size';
    } else if (strcmp($dbName, 'S.detected') == 0) {
        $envvar = 'd_dtec';
    } else if (strcmp($dbName, 'S.lastdownload') == 0) {
        $envvar = 'd_dnld';
    } else if (strcmp($dbName, 'S.lastinstall') == 0) {
        $envvar = 'd_inst';
    } else if (strcmp($dbName, 'S.lastuninstall') == 0) {
        $envvar = 'd_unin';
    } else if (strcmp($dbName, 'S.lastchange') == 0) {
        $envvar = 'd_chng';
    } else if (strcmp($dbName, 'S.nextaction') == 0) {
        $envvar = 'd_next';
    } else if (strcmp($dbName, 'S.downloadsource') == 0) {
        $envvar = 'd_dsrc';
    } else if (strcmp($dbName, 'S.lasterror') == 0) {
        $envvar = 'd_lerr';
    } else if (strcmp($dbName, 'S.lasterrordate') == 0) {
        $envvar = 'd_etim';
    } else if (strcmp($dbName, 'Q.name') == 0) {
        $envvar = 'gid';
    } else {
        logs::log(__FILE__, __LINE__, "wu-stats.php: add env handling for $dbName", 0);
    }

    if ($envvar) {
        if (@($env[$envvar])) {
            return true;
        }
    }
    return false;
}


/* WUST_GetSpecialColFormat

        Translates $curFormat into a special format (such as the timestamp
        version of $curFormat) if $dbName is a column that needs translation.
    */
function WUST_GetSpecialColFormat($dbName, $curFormat)
{
    $isTime = 0;
    if (strcmp($dbName, 'S.detected') == 0) {
        $isTime = 1;
    } else if (strcmp($dbName, 'S.lastdownload') == 0) {
        $isTime = 1;
    } else if (strcmp($dbName, 'S.lastinstall') == 0) {
        $isTime = 1;
    } else if (strcmp($dbName, 'S.lastuninstall') == 0) {
        $isTime = 1;
    } else if (strcmp($dbName, 'S.lastchange') == 0) {
        $isTime = 1;
    } else if (strcmp($dbName, 'S.nextaction') == 0) {
        $isTime = 1;
    } else if (strcmp($dbName, 'S.lasterrordate') == 0) {
        $isTime = 1;
    }

    if ($isTime == 1) {
        switch ($curFormat) {
            case constReptColFormatNone:
                return constReptColFormatNoneT;
                break;
            case constReptColFormatBold:
                return constReptColFormatBoldT;
                break;
            case constReptColFormatBoldIndent:
                return constReptColFormatBoldIndentT;
                break;
            case constReptColFormatNoneIndent:
                return constReptColFormatNoneIndentT;
                break;
            case constReptColFormatHeader:
                return constReptColFormatHeaderT;
                break;
            default:
                logs::log(
                    __FILE__,
                    __LINE__,
                    "wu-stats.php: add format handling for $curFormat",
                    0
                );
                break;
        }
    }

    return $curFormat;
}


/* WUST_GetReportHeader

        Generates HTML for the report header (both e-mail and display).
    */
function WUST_GetReportHeader($env, $addHTML)
{
    $ret = '';
    $title = '';
    if (strcmp($env['rname'], '') == 0) {
        $title .= 'MUM Update Status Report';
    } else {
        $title .= $env['rname'];
    }
    if ($addHTML) {
        $ret .= "<html><head><title>$title</title>" . standard_style()
            . "</head><body>";
    }

    $user = $env['user']['username'];

    $list = '';
    if ($env['demail']) {
        $list .= $env['user']['report_mail'];
    }
    if (strcmp($env['email'], '') != 0) {
        if (strcmp($list, '') != 0) {
            $list .= ',';
        }
        $list .= $env['email'];
    }

    $ret .= "<table border=\"0\">";

    $font = "<font face=\"Verdana,helvetica\" size=\"3\" "
        . "color=\"333399\">";
    $font2 = "<font face=\"Verdana,helvetica\" size=\"3\">";

    $now = time();
    $umin = parsedate($env['sdate'], $now);
    $umax = parsedate($env['edate'], $now);
    $tnow = datestring(time());
    $hours = 0;
    if (($umin != 0) && ($umax != 0)) {
        $seconds = $umax - $umin;
        $hours   = intval($seconds / 3600);
    }

    $ret .= "<tr><td>$font Report Title:</font></td><td>"
        . "$font2 $title</font></td></tr>";
    $ret .= "<tr><td>$font Creator:</font></td><td>"
        . "$font2 $user</font></td></tr>";
    $ret .= "<tr><td>$font Recipients:</font></td><td>"
        . "$font2 $list</font></td></tr>";
    $ret .= '<td><td>&nbsp;</td><td>&nbsp;</td></tr>';
    $ret .= "<tr><td>$font Start Date:</font></td><td>"
        . "$font2 " . $env['sdate'] . "</font></td></tr>";
    $ret .= "<tr><td>$font End Date:</font></td><td>"
        . "$font2 " . $env['edate'] . "</font></td></tr>";
    $ret .= "<tr><td>$font Report Date:</font></td><td>"
        . "$font2 $tnow</font></td></tr>";
    if ($hours != 0) {
        $ret .= "<tr><td>$font Elapsed Time:</font></td><td>"
            . "$font2 $hours hours</font></td></tr>";
    } else {
        $ret .= "<tr><td>$font Elapsed Time:</font></td><td>"
            . "$font2 &nbsp;</font></td></tr>";
    }
    $ret .= '<td><td>&nbsp;</td><td>&nbsp;</td></tr>';

    $colText = WUST_GetAllOrderColumns(true, true);
    order($env['ord'], $env);

    $numGroup = 0;
    $group = '';
    WUST_AppendToString($group, $numGroup, $env['group1']);
    WUST_AppendToString($group, $numGroup, $env['group2']);
    WUST_AppendToString($group, $numGroup, $env['group3']);

    if ($numGroup == 0) {
        $ret .= "<tr><td>$font Group and sort by first:</font></td><td>"
            . "$font2 Site (ascending) [default summary] </td></tr>";
        $ret .= "<tr><td>$font Group and sort by second:</font></td><td>"
            . "$font2 Machine (ascending) [default summary]</td></tr>";
    } else {
        $ret .= "<tr><td>$font Group and sort by first:</font></td><td>"
            . "$font2 " . $colText[$env['group1']]['fname'] . "</td></tr>";
        $ret .= "<tr><td>$font Group and sort by second:</font></td><td>"
            . "$font2 " . $colText[$env['group2']]['fname'] . "</td></tr>";
    }
    $ret .= "<tr><td>$font Group and sort by third:</font></td><td>"
        . "$font2 " . $colText[$env['group3']]['fname'] . "</td></tr>";

    $colText = WUST_GetAllOrderColumns(true, true);
    $ret .= "<tr><td>$font Within that, sort by:</font></td><td>"
        . "$font2 " . $colText[$env['order1']]['fname'] . "</td></tr>";
    $ret .= "<tr><td>$font Within that, sort by:</font></td><td>"
        . "$font2 " . $colText[$env['order2']]['fname'] . "</td></tr>";

    $ret .= "</table>";

    return $ret;
}


/* WUST_AddSummaryGroupCol

        Adds a special grouping column to the summary component.
    */
function WUST_AddSummaryGroupCol(
    $groupStr,
    $rank,
    $thisReport,
    $compIdx,
    $dbIdx,
    $location,
    $grpIdx,
    $numGroup
) {
    $myArray = array();
    $valueFormat = constReptColFormatNone;
    $headerFormat = constReptColFormatNone;
    $locationFormat = constReptLocFormatDbValue;
    $ranktd = '';
    $sep = '';
    $nullHandleList = 0;

    /* The $rank variable tells which location */
    switch ($rank) {
        case 0:
            /* Primary grouping field */
            $valueFormat = constReptColFormatHeader;
            $headerFormat = constReptColFormatHeader;
            if ($numGroup == 3) {
                $myArray[$numGroup - 2]['sep'] = '';
                $myArray[$numGroup - 2]['value'] = ' ';
                $myArray[$numGroup - 2]['dheader'] = 1;
            }
            $myArray[$numGroup - 1]['sep'] = '';
            $myArray[$numGroup - 1]['value'] = ' ';
            $myArray[$numGroup - 1]['dheader'] = 1;
            $ranktd = 'colspan="12"';
            break;
        case 1:
            /* Secondary grouping field */
            $valueFormat = constReptColFormatBold;
            $headerFormat = constReptColFormatBold;
            $sep = constSepBlankLine;
            $ranktd = 'colspan="12"';
            break;
        case 2:
            /* Final (lowest level) grouping field */
            $headerFormat = constReptColFormatBold;
            $locationFormat = constReptLocFormatWide;

            if ($numGroup > 1) {
                $myArray[1]['sep'] = constSepSmallLine;
                $myArray[1]['value'] = '<nobr><b>Sub-total</b></nobr>';
                $myArray[1]['dheader'] = 1;
                $myArray[2]['sep'] = '';
                $myArray[2]['value'] = '<b>Total</b>';
                $myArray[2]['dheader'] = 1;
                $sep = constSepBlankLine;
                if ($numGroup == 3) {
                    $myArray[3]['sep'] = '';
                    $myArray[3]['value'] = '<b>Grand&nbsp;Total</b>';
                    $myArray[3]['dheader'] = 1;
                }
            } else {
                $myArray[1]['sep'] = constSepBlankLine;
                $myArray[1]['value'] = '<b>Total</b>';
                $myArray[1]['dheader'] = 1;
                $sep = constSepBlankLine;
            }
            break;
    }

    if ($myArray) {
        $nullHandleList = REPT_CreateNullHandleList($myArray);
        if ($nullHandleList == 0) {
            return -1;
        }
    }
    $colText = WUST_GetAllOrderColumns(true) + WUST_GetAllGroupColumns();
    if ((strcmp($groupStr, constNoneStr) != 0) && (strcmp($groupStr, '') != 0)) {
        $colDB = WUST_TranslateDB($colText[$groupStr]['selname']);
        $colFormat = WUST_GetSpecialColFormat(
            $colText[$groupStr]['dbname'],
            $valueFormat
        );
        if (REPT_AddComponentCol(
            $thisReport,
            $compIdx,
            $colDB,
            $dbIdx,
            $colText[$groupStr]['sname'],
            $colFormat,
            $headerFormat,
            $location,
            $locationFormat,
            $grpIdx,
            $ranktd,
            '',
            $sep,
            $nullHandleList
        ) == -1) {
            return -1;
        }
    }
}


/* WUST_NeedsMachineGroup

        Determines if the machine group name (Q.name) needs to be included in
        the report's query or not.
    */
function WUST_NeedsMachineGroup($env)
{
    $myArray = array(
        'Q.name',
        'CONVERT(Q.name USING latin1) ASC',
        '`CONVERT(Q.name USING latin1)` ASC',
        'CONVERT(Q.name USING latin1) DESC',
        '`CONVERT(Q.name USING latin1)` DESC'
    );
    $myArray2 = array(
        $env['group1'],
        $env['group2'],
        $env['group3'],
        $env['order1'],
        $env['order2'],
        $env['order3'],
        $env['order4']
    );
    $intersect = array_intersect($myArray, $myArray2);
    if ($intersect) {
        return true;
    }
    return false;
}


/* WUST_PrintControl

        Prints the control $controlText with descriptive text $controlDesc on
        the left and help text $helpText on the right.  If $helpText is empty,
        will span two columns for $controlText.
    */
function WUST_PrintControl($controlDesc, $controlText, $helpText)
{
    $ret = '';
    $printHelp = false;

    $ret .= '<tr><td>';
    $ret .= $controlDesc;
    $ret .= '</td>';
    if (strcmp($helpText, '') == 0) {
        $ret .= '<td colspan="2">';
    } else {
        $ret .= '<td>';
        $printHelp = true;
    }

    $ret .= $controlText;
    $ret .= '</td>';
    if ($printHelp) {
        $ret .= '<td><font size="-2"><i>';
        $ret .= $helpText;
        $ret .= '</i></font></td>';
    }
    $ret .= '</tr>';
    return $ret;
}


/*
    |  Main program
    */

$now  = time();
$db   = db_connect();
$auth = process_login($db);
$comp = component_installed();
$nav  = patch_navigate($comp);
$cid  = get_integer('cid', 0);
$hid  = get_integer('hid', 0);
$mid  = get_integer('mid', 0);
$ctl  = get_integer('ctl', 0);
$act  = get_string('act', 'list');
$post = get_string('button', '');

/*
    |  The user can specify both the host and the site
    |  as long as the combination makes sense.  If
    |  we get a nonsense combination we'll ignore
    |  the host and go with the site.
    */

$host  = '';
$site  = find_site($cid, $auth, $db);
$cen   = find_host($hid, $auth, $db);
if ($cen) {
    if ($site) {
        if ($cen['site'] == $site) {
            $host = $cen['host'];
            $site = $cen['site'];
        } else {
            $hid = 0;
        }
    } else {
        $host = $cen['host'];
        $site = $cen['site'];
    }
}

if (($hid > 0) && ($host)) {
    $act = 'host';
}
if (($cid) && ($site) && ($hid <= 0)) {
    $act = 'site';
}

if ($post == constButtonHlp) {
    $act = 'help';
}

if ($post == constButtonCfgRep) {
    $act = 'cfgr';
}

if ($post == constButtonGen) {
    $act = 'genr';
}

$title = title($act, $site, $host);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $auth, $nav, 0, 0, $db);

$dbg   = get_integer('debug', 0);
$xxx   = get_integer('bug', 0);
$date  = datestring(time());
$user  = user_data($auth, $db);
$priv  = @($user['priv_debug']) ?   1  : 0;
$debug = @($user['priv_debug']) ? $dbg : 0;
$bug   = @($user['priv_debug']) ? $xxx : 1;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($debug) echo "<h2>$date</h2>";

debug_array($debug, $_POST);

if (!$priv) {
    $tmp = "|$act|";
    $txt = '|||dbug|dlst|fake|';
    if (strpos($txt, $tmp)) {
        $act = 'list';
    }
}

$midn = midnight($now);
$env = array();

/*
    |  Always Show:
    |    host, name
    |  Default Show:
    |    site, stat, inst, dtec, dnld
    |  Default Hide:
    |    unin, next, chng, psid, dsrc, lerr, etim, mtag
    */

$stat = get_integer('s', constPatchStatusInvalid);
$dtec = get_integer('d', 0);
$dnld = get_integer('dl', 0);
$inst = get_integer('i', 0);
$lerr = get_integer('e', 0);
$unin = get_integer('r', -1);
$next = get_integer('n', -1);
$chng = get_integer('c', -1);
$mtag = get_integer('m', -1);
$type = get_integer('t', -1);
$size = get_integer('z', -1);
$dsrc = get_integer('ds', -1);
$etim = get_integer('et', -1);
$psid = get_integer('psid', -1);
$gid  = get_integer('gid', 0);
$jid  = get_integer('jid', 0);
$ord  = tag_int('o', 0, 33, 20);
$pag  = tag_int('p', 0, 9999, 0);
$lim  = tag_int('l', 5, 1000, 20);

if ($post == constButtonRst) {
    $stat = constPatchStatusInvalid;
    $dtec = 0;
    $lerr = -1;
    $dsrc = -1;
    $dnld = 0;
    $unin = -1;
    $etim = -1;
    $inst = 0;
    $next = -1;
    $psid = -1;
    $gid  = 0;
    $chng = -1;
    $lim  = 20;
    $jid  = 0;
    $mtag = -1;
    $ord  = 20;
    $pag  = 0;
    $size = -1;
    $mid  =  0;
    $type = -1;
}

$env['d_name'] = (0 <= $mid);
$env['d_hid'] = (0 <= $hid);
$env['d_cid'] = (0 <= $cid);
$env['d_stat'] = (0 <= $stat);
$env['d_inst'] = (0 <= $inst);
$env['d_unin'] = (0 <= $unin);
$env['d_next'] = (0 <= $next);
$env['d_chng'] = (0 <= $chng);
$env['d_psid'] = (0 <= $psid);
$env['d_dnld'] = (0 <= $dnld);
$env['d_dsrc'] = (0 <= $dsrc);
$env['d_dtec'] = (0 <= $dtec);
$env['d_lerr'] = (0 <= $lerr);
$env['d_etim'] = (0 <= $etim);
$env['d_mtag'] = (0 <= $mtag);
if ($type != -1) {
    $env['d_type'] = $type;
}
$env['d_size'] = (0 <= $size);

$env['db']   = $db;
$env['ord']  = $ord;
$env['cid']  = $cid;
$env['hid']  = $hid;
$env['mid']  = $mid;
$env['gid']  = $gid;
$env['jid']  = $jid;
$env['act']  = $act;
$env['bug']  = $bug;

$env['href'] = 'page_href';
$env['auth'] = $auth;
$env['site'] = $site;
$env['host'] = $host;
$env['priv'] = $priv;
$env['midn'] = $midn;
$env['code'] = tag_shorts();
$env['tags'] = tag_defaults();

$env['name'] = 1;
$env['next'] = $next;
$env['stat'] = $stat;
$env['inst'] = $inst;
$env['dnld'] = $dnld;
$env['etim'] = $etim;
$env['chng'] = $chng;
$env['dsrc'] = $dsrc;
$env['lerr'] = $lerr;
$env['psid'] = $psid;
$env['size'] = $size;
$env['dtec'] = $dtec;
$env['unin'] = $unin;
$env['mtag'] = $mtag;
$env['type'] = $type;
$env['jump'] = '#table';

$env['page'] = $pag;
$env['limt'] = $lim;
$env['self'] = server_var('PHP_SELF');
$env['args'] = server_var('QUERY_STRING');
$env['debug']  = $debug;

$env['rname'] = get_string('rname', '');
$env['dispimd'] = get_integer('dispimd', 0);
$env['sende'] = get_integer('sende', 0);
$env['email'] = get_string('email', '');
$env['demail'] = get_integer('demail', 0);
$env['details'] = get_integer('details', 0);
$env['group1'] = WUST_GetInitializedString('group1');
$env['group2'] = WUST_GetInitializedString('group2');
$env['group3'] = WUST_GetInitializedString('group3');
$env['order1'] = WUST_GetInitializedString('order1');
$env['order2'] = WUST_GetInitializedString('order2');
$env['order3'] = WUST_GetInitializedString('order3');
$env['order4'] = WUST_GetInitializedString('order4');
$env['sdate'] = get_string('sdate', '');
$env['edate'] = get_string('edate', '');

$env['user'] = $user;

db_change($GLOBALS['PREFIX'] . 'softinst', $db);
switch ($act) {
    case 'list':
        list_stats($env, $db);
        break;
    case 'site':
        list_stats($env, $db);
        break;
    case 'host':
        list_stats($env, $db);
        break;
    case 'ptch':
        list_stats($env, $db);
        break;
    case 'sane':
        sane_stats($env, $db);
        break;
    case 'fake':
        fake_stats($env, $db);
        break;
    case 'dbug':
        debug_menu($env, $db);
        break;
    case 'dlst':
        debug_stats($env, $db);
        break;
    case 'cfgr':
        WUST_ReportConfig($env, $db);
        break;
    case 'genr':
        WUST_MakeReport($env, $db);
        break;
    default:
        unknown_action($env, $db);
        break;
}
echo head_standard_html_footer($auth, $db);
