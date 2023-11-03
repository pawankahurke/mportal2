<?php

/*
Revision history:

Date        Who     What
----        ---     ----
11-Jun-04   EWB     Created.
23-Jun-04   EWB     Shows a patch.
 1-Jul-04   EWB     Column sorting.
14-Jul-04   EWB     Patches.canuninstall
16-Jul-04   EWB     Canuninstall, zero is undefined.
23-Jul-04   EWB     Don't delete for 'No'
27-Jul-04   EWB     Patch detail page.
 6-Aug-04   EWB     Wizard link.
18-Aug-04   EWB     Don't update machine table.
27-Aug-04   EWB     Show group creation date.
 7-Sep-04   EWB     Wizard page does not show patch groups
29-Sep-04   EWB     check_patch_dirty
29-Sep-04   EWB     delete patch removes associated pgrp/pcfg
 4-Oct-04   EWB     debug patch table.
 4-Oct-04   EWB     paging for patch display.
14-Dec-04   EWB     uses new library paging routines.
20-Sep-06   BTE     Bug 2826: Make MUM approve/decline wizards a little easier
                    to use (not so large).
09-Oct-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()

*/

ob_start();
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-slct.php');
include('../lib/l-user.php');
include('../lib/l-jump.php');
include('../lib/l-slav.php');
include('../lib/l-tabs.php');
include('../lib/l-form.php');
include('../lib/l-tiny.php');
include('../lib/l-rlib.php');
include('../lib/l-drty.php');
include('../lib/l-grps.php');
include('../lib/l-ptch.php');
include('../lib/l-pdrt.php');
include('../lib/l-head.php');
include('../lib/l-cnst.php');
include('../lib/l-errs.php');
include('local.php');


function unknown_action(&$env, $db)
{
    debug_note("unknown action");
}

function find_patch($mid, $db)
{
    $row = array();
    if ($mid > 0) {
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.Patches\n"
            . " where patchid = $mid";
        $row = find_one($sql, $db);
    }
    return $row;
}

function canuninstall($can)
{
    switch ($can) {
        case constPatchCanUnknown:
            return 'Unknown';
        case     constPatchCanYes:
            return 'Yes';
        case      constPatchCanNo:
            return 'No';
        default:
            return canuninstall(constPatchCanUnknown);
    }
}

function again(&$env)
{
    $self = $env['self'];
    $dbg  = $env['priv'];
    $act  = $env['act'];
    $a    = array();
    $cmd  = "$self?act";
    $a[]  = html_link('#top', 'top');
    $a[]  = html_link('#bottom', 'bottom');
    if ($act == 'list') {
        $a[] = html_link('#control', 'control');
        $a[] = html_link('#table', 'table');
    } else {
        $a[] = html_link($self, 'updates');
    }
    $a[]  = html_link('index.php', 'wizard');
    if ($dbg) {
        $args = $env['args'];
        $href = ($args) ? "$self?$args" : $self;
        $dbug = "$cmd=dbug";
        $a[] = html_link('../acct/index.php', 'home');
        $a[] = html_link($href, 'again');
        $a[] = html_link($dbug, 'debug');
        $a[] = html_link($self, 'patch');
    }
    return jumplist($a);
}


function delete_patch($mid, $db)
{
    $num = 0;
    $row = find_patch($mid, $db);
    if ($row) {
        $name = $row['name'];
        $pgrp = find_pgrp_name($name, $db);
        $sql = "delete from\n"
            . " " . $GLOBALS['PREFIX'] . "softinst.Patches\n"
            . " where patchid = $mid";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
        if ($num) {
            debug_note("Patch <b>$name</b> removed.");
            $sql = "delete from\n"
                . " " . $GLOBALS['PREFIX'] . "softinst.PatchGroupMap\n"
                . " where patchid = $mid";
            $res = redcommand($sql, $db);
            $tmp = affected($res, $db);
            debug_note("PatchGroupMaps: $tmp removed");
            $sql = "delete from\n"
                . " " . $GLOBALS['PREFIX'] . "softinst.PatchStatus\n"
                . " where patchid = $mid";
            $res = redcommand($sql, $db);
            $tmp = affected($res, $db);
            debug_note("PatchStatus: $tmp removed");
        }
        if (($num) && ($pgrp)) {
            $jid = $pgrp['pgroupid'];
            $sql = "delete from\n"
                . " " . $GLOBALS['PREFIX'] . "softinst.PatchConfig\n"
                . " where pgroupid = $jid";
            $res = redcommand($sql, $db);
            $tmp = affected($res, $db);
            debug_note("PatchConfig: $tmp removed");
            $sql = "delete from\n"
                . " " . $GLOBALS['PREFIX'] . "softinst.PatchGroups\n"
                . " where pgroupid = $jid";
            $res = redcommand($sql, $db);
            $tmp = affected($res, $db);
            debug_note("PatchGroups: $tmp removed");
        }
    }
    return $num;
}


function confirm_delete(&$env, $db)
{
    echo again($env);
    $mid   = $env['mid'];
    $patch = find_patch($mid, $db);
    if ($patch) {
        $name = $patch['name'];
        $yes  = button('Yes');
        $no   = button('No');
        $sp   = '&nbsp;';
        echo post_self('myform');
        echo hidden('act', 'del');
        echo hidden('mid', $mid);
        echo "<p>Delete Update <b>$name</b>?</p>\n";
        echo "<p>$yes $sp $sp $no</p>\n";
        echo form_footer();
    }
    echo again($env);
}

function patch_delete(&$env, $db)
{
    $mid   = $env['mid'];
    $patch = find_patch($mid, $db);
    if ($patch) {
        $name = $patch['name'];
        $good = delete_patch($mid, $db);
        $has  = ($good) ? 'has' : 'has not';
        echo "<p>Patch <b>$name</b> $has been deleted.</p>\n";
    }
    list_patches($env, $db);
}

function order($ord)
{
    switch ($ord) {
        case  0:
            return 'name, patchid desc';
        case  1:
            return 'name desc, patchid';
        case  4:
            return 'size, name, patchid desc';
        case  5:
            return 'size desc, name desc, patchid';
        case  6:
            return 'date desc, name, patchid';
        case  7:
            return 'date, name desc, patchid';
        case  8:
            return 'clientfile, name, patchid desc';
        case  9:
            return 'clientfile desc, name desc, patchid';
        case 10:
            return 'canuninstall desc, name desc, patchid';
        case 11:
            return 'canuninstall, name, patchid';
        default:
            return order(0);
    }
}


function ords()
{
    $a = 'ascending';
    $d = 'descending';
    $u = 'Update';
    $s = 'Size';
    return array(
        0 => "$u ($a)",
        1 => "$u ($d)",
        4 => "$s / $u ($a)",
        5 => "$s / $u ($d)",
        6 => "Date / $u ($d)",
        7 => "Date / $u ($a)",
        8 => "Filename / $u ($a)",
        9 => "Filename / $u ($d)",
        10 => "Uninstallable / $u ($d)",
        11 => "Uninstallable / $u ($a)"
    );
}


function page_href(&$env, $page, $ord)
{
    $self = $env['self'];
    $limt = $env['lim'];
    $priv = $env['priv'];
    $dbug = $env['debug'];

    $a    = array("$self?p=$page");
    $a[]  = "o=$ord";
    $a[]  = "l=$limt";

    if (($priv) && ($dbug)) {
        $a[] = "debug=1";
    }

    return join('&', $a);
}


function list_patches(&$env, $db)
{
    echo mark('control');
    echo again($env);

    $tableID = constTableIDMUMUpdates;
    $set = 1;
    $sort = 1;
    if (server_var('QUERY_STRING')) {
        if (strpos(server_var('QUERY_STRING'), "set") === false) {
            $set = 0;
        } else if (strpos(server_var('QUERY_STRING'), "sort") === false) {
            $sort = 0;
        }
    } else {
        $set = 0;
        $sort = 0;
    }

    $username = $env['user']['username'];

    $displayFull = 1;
    if (($set) || ($sort)) {
        $err = PHP_HTML_StoreSearchOptions(
            CUR,
            isset($GLOBALS["HTTP_RAW_POST_DATA"]) ?
                $GLOBALS["HTTP_RAW_POST_DATA"] : NULL,
            $tableID,
            $username,
            server_var('QUERY_STRING')
        );
        if ($err != constAppNoErr) {
            echo "\nAn error has occurred processing this page.  See ";
            echo "<a href=\"../acct/csrv.php?error\">errlog.txt</a>.\n";
        }
    }

    if ($displayFull) {
        $err = PHP_CSRV_GetTable(
            CUR,
            $html,
            server_var('QUERY_STRING'),
            $tableID,
            NULL,
            $username,
            "wu-patch.php?act=list&set=1",
            "wu-patch.php?act=list&sort=",
            "wu-patch.php?act=list"
        );
        if ($err != constAppNoErr) {
            echo "\nAn error has occurred processing this page.  See ";
            echo "<a href=\"../acct/csrv.php?error\">errlog.txt</a>.\n";
        }
    }

    echo $html;

    echo again($env);
}


function timestamp($time)
{
    return ($time) ? date('m/d/y H:i:s', $time) : 'unknown';
}


function show_string($row, $name, $prompt)
{
    if (isset($row[$name])) {
        $valu = $row[$name];
        if ($valu != '') {
            echo double($prompt, $valu);
        }
    }
}


function g($txt)
{
    return "<font color=\"green\">$txt</font>";
}

function patch_groups($row, $db)
{
    check_patch_dirty($db);
    if ($row) {
        $mid = $row['patchid'];
        $sql = "select G.pgroupid as gid,\n"
            . " G.name as grp,\n"
            . " G.created as tim,\n"
            . " C.category as cat,\n"
            . " C.precedence as pri\n"
            . " from PatchCategories as C,\n"
            . " PatchGroups as G,\n"
            . " PatchGroupMap as M\n"
            . " where M.patchid = $mid\n"
            . " and G.pgroupid = M.pgroupid\n"
            . " and G.pcategoryid = C.pcategoryid\n"
            . " order by pri desc, tim desc, grp";
        $set = find_many($sql, $db);
        if ($set) {
            $name = $row['name'];
            $head = explode('|', 'Priority|Date|Category|Group');
            $cols = safe_count($head);
            $rows = safe_count($set);
            $text = "$name &nbsp; ($rows groups)";

            echo table_header();
            echo pretty_header($text, $cols);
            echo table_data($head, 1);

            reset($set);
            foreach ($set as $key => $row) {
                $gid = $row['gid'];
                $grp = $row['grp'];
                $cat = $row['cat'];
                $pri = $row['pri'];
                $tim = timestamp($row['tim']);
                $args = array($pri, $tim, $cat, $grp);
                echo table_data($args, 0);
            }
            echo table_footer();
            echo clear_all();
        }
    }
}


function patch_fields($row, $dbg)
{
    if ($row) {
        $name = $row['name'];
        $size = $row['size'];
        $date = $row['date'];
        $prio = $row['prio'];
        $mid  = $row['patchid'];
        $omaj = $row['osmajor'];
        $omin = $row['osminor'];
        $obld = $row['osbuild'];
        $smaj = $row['spmajor'];
        $smin = $row['spminor'];
        $desc = $row['patchdesc'];
        $phid = $row['priohidden'];
        $lupd = $row['lastupdate'];
        $canu = $row['canuninstall'];
        $lref = $row['lastreference'];

        /*
            |  Microsoft sometimes likes to stuff unicode characters
            |  into patch descriptions, and for reasons I'm not sure
            |  of yet, these end up getting mashed into utf8 in the
            |  database.
            |
            |  see mbstring.http_input / http_output
            |
            |  So for the (R) symbol (0xAE) we end up with 0xC2AE.
            |
            |  The right single quote is 0x92 in codepage 1252, but
            |  gets mapped to 0x2019 in unicode, and then to 0xE28099
            |  for utf-8.
            |
            |  However utf8_decode just turns 0xE28099 into a question
            |  mark ... so I'm doing it manually.
            */

        $desc = str_replace("\xE2\x80\x99", "'", $desc);
        $desc = utf8_decode($desc);
        $over = ($omaj) ? "$omaj.$omin.$obld" : 'unknown';
        $sver = ($smaj) ? "$smaj.$smin" : 'unknown';
        $unin = canuninstall($canu);

        echo table_header();
        echo pretty_header($name, 2);
        echo double('Description', $desc);
        show_string($row, 'msname', 'MS Name');
        show_string($row, 'title', 'Title');
        echo double('Size', $size);
        show_string($row, 'clientfile', 'Client File');
        show_string($row, 'serverfile', 'Server File');
        echo double('Date', timestamp($date));
        show_string($row, 'itemid', 'Item');
        show_string($row, 'processor', 'Processor');
        echo double('Can Uninstall', $unin);
        show_string($row, 'platform', 'Platform');
        show_string($row, 'component', 'Component');
        show_string($row, 'name', 'Name');
        echo double('OS Version', $over);
        echo double('SP Version', $sver);
        show_string($row, 'locale', 'Locale');
        show_string($row, 'crc', 'CRC');
        show_string($row, 'eula', 'EULA');
        echo double('Priority', $prio);
        echo double('Hidden Priority', $phid);
        if ($dbg) {
            show_string($row, 'params', g('Params'));
            echo double(g('LastUpdate'), timestamp($lupd));
            echo double(g('LastReference'), timestamp($lref));
            echo double(g('PatchId'), $mid);
        }
        echo table_footer();
        echo clear_all();
    }
}


function debug_patch(&$env, $db)
{
    echo again($env);
    $ord = $env['ord'];
    $wrd = order($ord);
    $sql = "select * from Patches\n"
        . " order by $wrd";
    $set = find_many($sql, $db);
    if ($set) {
        $head = explode('|', 'mid|name|size|comp|plat|over');
        $cols = safe_count($head);
        $rows = safe_count($set);
        $text = "Debug Patch &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($head, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $mid  = $row['patchid'];
            $size = $row['size'];
            $name = disp($row, 'name');
            $comp = disp($row, 'component');
            $plat = disp($row, 'platform');
            //      $item = disp($row,'itemid');
            $omaj = $row['osmajor'];
            $omin = $row['osminor'];
            $obld = $row['osbuild'];
            $over = ($omaj) ? "$omaj.$omin.$obld" : '<br>';
            $args = array($mid, $name, $size, $comp, $plat, $over);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
    echo again($env);
}



function patch_wizard(&$env, $db)
{
    echo again($env);
    $mid = $env['mid'];
    $dbg = $env['debug'];
    $row = find_patch($mid, $db);
    if ($row) {
        patch_fields($row, $dbg);
    }

    echo again($env);
}

function patch_detail(&$env, $db)
{
    echo again($env);
    $mid = $env['mid'];
    $dbg = $env['debug'];
    $row = find_patch($mid, $db);
    if ($row) {
        patch_fields($row, $dbg);
        patch_groups($row, $db);
    }

    echo again($env);
}




/*
    |  Main program
    */

$now  = time();
$db   = db_connect();
$auth = process_login($db);
$comp = component_installed();
$nav  = patch_navigate($comp);
$act  = get_string('act', 'list');
$post = get_string('button', '');
if (($act == 'del') && ($post == 'No')) {
    $act = 'list';
}


$title = 'Microsoft Update - Software Updates';
$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $auth, $nav, 0, 0, $db);

$date = datestring($now);

$dbg    = get_integer('debug', 1);
$user   = user_data($auth, $db);
$priv   = @($user['priv_debug']) ?   1  : 0;
$debug  = @($user['priv_debug']) ? $dbg : 0;

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

if ($debug) echo "<h2>$date</h2>";

debug_array($debug, $_POST);

$env = array();
$env['db']  = $db;
$env['act'] = $act;
$env['mid'] = get_integer('mid', 0);
$env['ord'] = get_integer('o', 0);
$env['lim'] = get_integer('l', 20);
$env['page'] = get_integer('p', 0);
$env['self'] = server_var('PHP_SELF');
$env['args'] = server_var('QUERY_STRING');
$env['priv'] = $priv;
$env['href'] = 'page_href';
$env['limt'] = $env['lim'];
$env['jump'] = '#table';
$env['debug'] = $debug;
$env['user'] = $user;

db_change($GLOBALS['PREFIX'] . 'softinst', $db);
switch ($act) {
    case 'list':
        list_patches($env, $db);
        break;
    case 'cdel':
        confirm_delete($env, $db);
        break;
    case 'dbug':
        debug_patch($env, $db);
        break;
    case 'del':
        patch_delete($env, $db);
        break;
    case 'dets':
        patch_wizard($env, $db);
        break;
    case 'edet':
        patch_detail($env, $db);
        break;
    default:
        unknown_action($env, $db);
        break;
}

echo head_standard_html_footer($auth, $db);
