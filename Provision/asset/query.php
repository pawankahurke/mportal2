<?php

/*
Revision history:

Date        Who     What
----        ---     ----
23-Sep-02   NL      start file from 19-Sep-02, EWB, Giant refactoring.
23-Sep-02   NL      remove event code --> asset only
 7-Oct-02   NL      change the "run" querystring
21-Oct-02   NL      only display non-temporary queries
23-Oct-02   NL      display &nbsp if searchstring empty
23-Oct-02   NL      change create to add
 4-Dec-02   EWB     Reorginization Day
 5-Dec-02   EWB     Do not require php short_open_tag
 6-Dec-02   EWB     Local Navigation
10-Jan-03   EWB     Minimal quotes.
10-Jan-03   EWB     Show owner to debug user.
 7-Feb-03   EWB     Moved to asset world.
11-Feb-03   EWB     db_change()
24-Feb-03   EWB     Don't offer to delete things we don't own anyway.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   NL      Move debug_note line below $debug.
30-Apr-04   EWB     Export by query.
13-Dec-04   EWB     Paging of Asset Queries
15-Dec-04   EWB     Manage Asset Queries
16-Dec-04   EWB     Name Contains / Display and Sort by displayfields
 4-Jan-05   EWB     Spelling counts ... "Critera" -> "Criteria"
 5-Jan-05   EWB     "Criteria Contains"
25-Jan-05   EWB     Help / Reset
 4-Feb-05   EWB     Select by owner still displays column
 7-Feb-05   EWB     New help pages
21-Dec-06   WOH     Made changes for bugzilla #3657 - head_standard_html_footer()
17-Aug-07   BTE     Changes for summary sections phase 1.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-gsql.php');
include('../lib/l-user.php');
include('../lib/l-cmth.php');
include('../lib/l-jump.php');
include('../lib/l-tabs.php');
include('../lib/l-form.php');
include('../lib/l-slct.php');
include('../lib/l-tiny.php');
include('local.php');
include('../lib/l-head.php');
include('../lib/l-cnst.php');
include('../lib/l-errs.php');

define('constButtonAll',  'Check all');
define('constButtonCan',  'Cancel');
define('constButtonHlp',  'Help');
define('constButtonNo',   'No');
define('constButtonNone', 'Uncheck all');
define('constButtonOk',   'OK');
define('constButtonRst',  'Reset');
define('constButtonSub',  'Search');
define('constButtonYes',  'Yes');

function title($act)
{
    $a = 'Asset';
    $q = 'Query';
    $s = 'Queries';
    switch ($act) {
        case 'cdel':
            return "Confirm Delete $a $q";
        case 'list':
            return "$a $s";
        case 'mnge':
            return "Manage $a $s";
        case 'rdel':
            return "Delete $a $q";
        case 'view':
            return "$a $q Details";
        default:
            return "$a $s";
    }
}


function again($env)
{
    $priv = $env['priv'];
    $self = $env['self'];
    $jump = $env['jump'];
    $act  = $env['act'];

    $ax = array();
    $ax[] = html_link('#top', 'top');
    $ax[] = html_link('#bottom', 'bottom');
    $ax[] = html_link('qury-add.php', 'add');
    if ($act == 'list') {
        $mnge = "$self?act=mnge";
        $ax[] = html_link('#control', 'control');
        $ax[] = html_link('#table', 'table');
        $ax[] = gang_link($env, 'mnge', 'manage');
    } else {
        $temp = $self . $jump;
        $ax[] = html_link($temp, 'queries');
    }
    if ($priv) {
        $args = $env['args'];
        $comp = "$self?act=list&dsp=1";
        $full = "$comp&gbl=3&flt=-1&usr=0&mod=0&crt=0$jump";
        $redo = ($args) ? "$self?$args" : $self;
        $ax[] = html_link($full, 'compact');
        $ax[] = html_link('../acct/index.php', 'home');
        $ax[] = html_link($redo, 'again');
    }
    return jumplist($ax);
}

function gang_href(&$env, $act)
{
    $self = $env['self'];
    $page = $env['page'];
    $limt = $env['limt'];
    $ord  = $env['ord'];
    $args = array("$self?act=$act&o=$ord&p=$page&l=$limt");
    query_state($env, $args);
    return join('&', $args);
}


function query_state(&$env, &$set)
{
    $crt = $env['crt'];
    $mod = $env['mod'];
    $gbl = $env['gbl'];
    $dsp = $env['dsp'];
    $usr = $env['usr'];
    $flt = $env['flt'];
    $fld = $env['fld'];
    $pat = $env['pat'];
    $txt = $env['txt'];
    $dbg = $env['dbug'];
    $prv = $env['priv'];

    if ($dsp != 0)  $set[] = "dsp=$dsp";
    if ($flt != 0)  $set[] = "flt=$flt";
    if ($usr != -1) $set[] = "usr=$usr";
    if ($gbl != -1) $set[] = "gbl=$gbl";
    if ($crt != -1) $set[] = "crt=$crt";
    if ($mod != -1) $set[] = "mod=$mod";
    if ($fld != -1) $set[] = "fld=$fld";
    if ($pat != '') {
        $value = urlencode($pat);
        $set[] = "pat=$value";
    }
    if ($txt != '') {
        $value = urlencode($txt);
        $set[] = "txt=$value";
    }
    if (($prv) && ($dbg)) $set[] = "debug=1";
}

function gang_link(&$env, $act, $text)
{
    $href = gang_href($env, $act);
    return html_link($href, $text);
}

function green($msg)
{
    return "<font color=\"green\">$msg</font>";
}

function dgreen($a, $b)
{
    $aa = green($a);
    $bb = green($b);
    return double($aa, $bb);
}

function debug_array($debug, $p)
{
    if ($debug) {
        reset($p);
        foreach ($p as $key => $data) {
            $msg = green("$key: $data");
            echo "$msg<br>\n";
        }
    }
}

function nanotime($when)
{
    $text = '<br>';
    if ($when > 0) {
        $that = date('m/d/y', time());
        $date = date('m/d/y', $when);
        $time = date('H:i:s', $when);
        $midn = ($time == '00:00:00');
        $tday = ($date == $that);
        if ($midn) {
            $text = $date;
        } else if ($tday) {
            $text = $time;
        } else {
            $text = "$date $time";
        }
    }
    if ($when < 0) {
        $text = "running";
    }
    return $text;
}

function ords()
{
    $a = 'ascending';
    $d = 'descending';
    return array(
        0 => "Name ($a)",
        1 => "Name ($d)",
        2 => "Owner ($a)",
        3 => "Owner ($d)",
        4 => "Criteria ($a)",
        5 => "Criteria ($d)",
        6 => "Modify ($d)",
        7 => "Modify ($a)",
        8 => "Created ($d)",
        9 => "Created ($a)",
        10 => "Global ($d)",
        11 => "Global ($a)",
        12 => "Id ($a)",
        13 => "Id ($d)",
        14 => "Fields ($a)",
        15 => "Fields ($d)"
    );
}

function criteria_ops()
{
    return array(
        'unknown',
        '=',
        '!=',
        'contains',
        'begins with',
        'ends with',
        '<',
        '>',
        '<=',
        '>=',
        'not contain'
    );
}


function order($ord)
{
    switch ($ord) {
        case  0:
            return 'name, username, id';
        case  1:
            return 'name desc, username, id';
        case  2:
            return 'username, name, id';
        case  3:
            return 'username desc, name desc, id';
        case  4:
            return 'searchstring, id';
        case  5:
            return 'searchstring desc, id';
        case  6:
            return 'modified desc, id';
        case  7:
            return 'modified, id';
        case  8:
            return 'created desc, id';
        case  9:
            return 'created, id';
        case 10:
            return 'global desc, id';
        case 11:
            return 'global, id';
        case 12:
            return 'id';
        case 13:
            return 'id desc';
        case 14:
            return 'displayfields, id desc';
        case 15:
            return 'displayfields desc, id';
        default:
            return order(0);
    }
}

function past_options($midn, $days)
{
    $opts = array(
        -2 => constTagNever,
        -1 => constTagNone,
        0 => constTagAny,
        1 => constTagToday
    );
    reset($days);
    foreach ($days as $key => $day) {
        $time = date_code($midn, $day);
        $text = date('D m/d', $time) . " ($day days)";
        $opts[$day] = $text;
    }
    return $opts;
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

function date_code($when, $d)
{
    if ($d > 1) {
        $when = days_ago($when, $d - 1);
    }
    return $when;
}


function glbl_options()
{
    return array(
        -1 => constTagNone,
        0 => constTagAny,
        1 => 'Local',
        2 => 'Global',
        3 => 'Debug'
    );
}


function find_query($qid, $db)
{
    $row = array();
    if ($qid) {
        $sql = "select * from AssetSearches\n"
            . " where id = $qid";
        $row = find_one($sql, $db);
    }
    return $row;
}

function find_report_qid($qid, $db)
{
    $set = array();
    if ($qid) {
        $sql = "select * from AssetReports\n"
            . " where searchid = $qid\n"
            . " order by name, username, global, id";
        $set = find_many($sql, $db);
    }
    return $set;
}


function find_reports($qid, $auth, $db)
{
    $set = array();
    if ($qid) {
        $qu  = safe_addslashes($auth);
        $sql = "select * from AssetReports\n"
            . " where searchid = $qid\n"
            . " and (global = 1 or\n"
            . " username = '$qu')\n"
            . " order by name, username, global, id";
        $set = find_many($sql, $db);
    }
    return $set;
}


function tiny_data($args, $tiny)
{
    $m  = '';
    $td = ($tiny) ? 'td style="font-size: x-small"' : 'td';
    if ($args) {
        $m .= "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            $m .= "<$td>$data</td>\n";
            $td = 'td';
        }
        $m .= "</tr>\n";
    }
    return $m;
}

function df($disp)
{
    $txt = '';
    if ($disp) {
        $set = explode(':', $disp);
        reset($set);
        foreach ($set as $key => $fld) {
            if (strlen($fld)) {
                $txt .= "$fld<br>\n";
            }
        }
    }
    return $txt;
}


function show(&$env, &$args, $tag, $col)
{
    if ($env[$tag]) $args[] = $col;
}

function search_table(&$env, $set, $total)
{
    $ord  = $env['ord'];
    $dsp  = $env['dsp'];
    $lim  = $env['limt'];
    $self = $env['self'];
    $priv = $env['priv'];
    $auth = $env['auth'];
    $jump = $env['jump'];

    $args = array("$self?act=list&l=$lim");
    query_state($env, $args);
    $o = join('&', $args) . "&o";

    $name = ($ord ==  0) ? "$o=1"  : "$o=0";     // name   0, 1
    $user = ($ord ==  2) ? "$o=3"  : "$o=2";     // user   2, 3
    $text = ($ord ==  4) ? "$o=5"  : "$o=4";     // text   4, 5
    $mtim = ($ord ==  6) ? "$o=7"  : "$o=6";     // mtim   6, 7
    $ctim = ($ord ==  8) ? "$o=9"  : "$o=8";     // ctim   8, 9
    $glob = ($ord == 10) ? "$o=11" : "$o=10";    // glob  10,11
    $sqid = ($ord == 12) ? "$o=13" : "$o=12";    // sqid  12,13
    $flds = ($ord == 14) ? "$o=15" : "$o=14";    // flds  14,15

    $acts = 'Action';
    $sqid = html_jump($sqid, $jump, 'Id');
    $name = html_jump($name, $jump, 'Name');
    $user = html_jump($user, $jump, 'Owner');
    $glob = html_jump($glob, $jump, 'Scope');
    $ctim = html_jump($ctim, $jump, 'Created');
    $mtim = html_jump($mtim, $jump, 'Modified');
    $flds = html_jump($flds, $jump, 'Display fields');
    $text = html_jump($text, $jump, 'Selection criteria');

    $head = array();
    show($env, $head, 'd_act', $acts);
    show($env, $head, 'd_nam', $name);
    show($env, $head, 'd_usr', $user);
    show($env, $head, 'd_gbl', $glob);
    show($env, $head, 'd_fld', $flds);
    show($env, $head, 'd_crt', $ctim);
    show($env, $head, 'd_mod', $mtim);
    show($env, $head, 'd_flt', $text);
    show($env, $head, 'd_qid', $sqid);

    $cols = safe_count($head);
    $rows = safe_count($set);
    $text = "Asset Queries &nbsp; ($total found)";
    $acts = '<br>';
    $flds = '<br>';
    $tiny = (!$dsp);
    $disp = ($env['d_fld']);

    echo table_header();
    echo pretty_header($text, $cols);
    echo table_data($head, 1);

    reset($set);
    foreach ($set as $key => $row) {
        $qid  = $row['id'];
        $name = $row['name'];
        $glob = $row['global'];
        $ctim = $row['created'];
        $mtim = $row['modified'];
        $user = $row['username'];
        $text = disp($row, 'searchstring');
        $scop = ($glob) ? 'Global' : 'Local';
        $cmd  = "$self?qid=$qid&act";
        $view = html_page("$cmd=view", $name);

        if ($tiny) {
            $ax   = array();
            $act  = "qury-act.php?id=$qid&action";
            $exec = "exec.php?qid=$qid";
            $expo = "export.php?qid=$qid";
            $ax[] = html_link($exec, '[run]');
            $mine = ($user == $auth);
            if ($mine) {
                $ax[] = html_link("$act=edit", '[edit]');
                $ax[] = html_link("$cmd=cdel", '[delete]');
            }
            if ((!$mine) && ($glob)) {
                $ax[] = html_link("$act=edit", '[edit]');
            }
            $ax[] = html_link("$act=duplicate", '[copy]');
            $ax[] = html_page($expo, '[export]');
            $acts = implode("<br>\n", $ax);
        }
        if ($disp) {
            $flds = df($row['displayfields']);
        }

        $args = array();
        show($env, $args, 'd_act', $acts);
        show($env, $args, 'd_nam', $view);
        show($env, $args, 'd_usr', $user);
        show($env, $args, 'd_gbl', $scop);
        show($env, $args, 'd_fld', $flds);
        show($env, $args, 'd_crt', nanotime($ctim));
        show($env, $args, 'd_mod', nanotime($mtim));
        show($env, $args, 'd_flt', $text);
        show($env, $args, 'd_qid', $qid);
        echo tiny_data($args, $tiny);
    }
    echo table_footer();
    echo prevnext($env, $total);
}


function disp_options()
{
    return array(
        -1 => constTagNone,
        0 => constTagAny
    );
}

function page_href(&$env, $page, $ord)
{
    $self = $env['self'];
    $limt = $env['limt'];

    $a   = array("$self?p=$page");
    $a[] = "o=$ord";
    $a[] = "l=$limt";
    query_state($env, $a);
    return join('&', $a);
}

function owns_options(&$env, $db)
{
    $set = array();
    $out = disp_options();
    if ($env['user']['priv_admin']) {
        $sql = "select userid, username\n"
            . " from " . $GLOBALS['PREFIX'] . "core.Users\n"
            . " order by username";
        $set = find_many($sql, $db);
    }
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $uid = $row['userid'];
            $out[$uid] = $row['username'];
        }
    }
    return $out;
}

function search_control(&$env, $total, $db)
{
    $auth = $env['auth'];
    $limt = $env['limt'];
    $page = $env['page'];
    $priv = $env['priv'];
    $self = $env['self'];
    $ord  = $env['ord'];
    $jump = $env['jump'];
    $form = $self . $jump;

    echo post_other('myform', $form);
    echo hidden('act', 'list');

    $days = array(2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 21, 28, 60, 90, 120, 150, 180, 360, 720, 3000);
    $lims = array(5, 10, 20, 25, 50, 75, 100, 150, 200, 250, 500, 1000);

    if (!in_array($limt, $lims)) {
        $lims[] = $limt;
        sort($lims, SORT_NUMERIC);
    }

    $midn = $env['midn'];
    $dsps = array('Expanded', 'Compact');
    $ords = ords();
    $glbs = glbl_options();
    $opts = past_options($midn, $days);
    $disp = disp_options();
    $usrs = owns_options($env, $db);
    $tiny = 50;
    $norm = 128;
    $yn = array('No', 'Yes');

    if (!$priv) {
        unset($glbs[3]);
    }

    $s_lim = tiny_select('l',   $lims, $env['limt'], 0, $tiny);
    $s_ord = tiny_select('o',   $ords, $env['ord'], 1, $norm);
    $s_crt = tiny_select('crt', $opts, $env['crt'], 1, $norm);
    $s_usr = tiny_select('usr', $usrs, $env['usr'], 1, $norm);
    $s_mod = tiny_select('mod', $opts, $env['mod'], 1, $norm);
    $s_dsp = tiny_select('dsp', $dsps, $env['dsp'], 1, $norm);
    $s_flt = tiny_select('flt', $disp, $env['flt'], 1, $norm);
    $s_gbl = tiny_select('gbl', $glbs, $env['gbl'], 1, $norm);
    $s_fld = tiny_select('fld', $disp, $env['fld'], 1, $norm);
    $s_dbg = tiny_select('debug', $yn, $env['dbug'], 1, $norm);
    $s_pat = tinybox('pat', 40, $env['pat'], $norm);
    $s_txt = tinybox('txt', 40, $env['txt'], $norm);

    $href = 'query.htm';
    $open = "window.open('$href','help');";
    $hlp  = click(constButtonHlp, $open);
    $sub  = button(constButtonSub);
    $rst  = button(constButtonRst);

    $head = table_header();
    $srch = pretty_header('Search Options', 1);
    $disp = pretty_header('Display Options', 1);
    $td   = 'td style="font-size: xx-small"';
    $xn   = indent(4);
    if ($priv) {
        $dbug = green('Debug');
    } else {
        $dbug  = '';
        $s_dbg = '';
    }


    echo <<< XXXX

        <table>
        <tr valign="top">
          <td rowspan="2">

            $head

            $srch

            <tr><td>
              <table border="0" width="100%">
              <tr>
                <$td>Name Contains      <br>\n$s_pat</td>
                <$td>Scope              <br>\n$s_gbl</td>
                <$td>Selection criteria <br>\n$s_flt</td>
              </tr>
              <tr>
                <$td>Criteria Contains  <br>\n$s_txt</td>
                <$td>Display fields     <br>\n$s_fld</td>
                <$td>Owner              <br>\n$s_usr</td>
              </tr>
              <tr>
                <$td>Created            <br>\n$s_crt</td>
                <$td>Modified           <br>\n$s_mod</td>
                <$td>$dbug              <br>\n$s_dbg</td>
              </tr>
              </table>
            </td></tr>
            </table>

          </td>

          <td rowspan="2">
            $xn
          </td>

          <td>
            $head
            $disp

            <tr><td>
              <table border="0" width="100%">
              <tr>
                <$td>Page Size  <br>$s_lim  </td>
                <$td>Sort By    <br>$s_ord  </td>
                <$td>Display    <br>$s_dsp  </td>
              </tr>
              </table>
            </td></tr>
            </table>
          </td>
        </tr>
        <tr>
          <td>
            <table width="100%">
            <tr><td align="left" valign="bottom">

                ${sub}${xn}${hlp}${xn}${rst}

            </td></tr>
            </table>
           <td>
        </tr>
        </table>

        <br clear="all">


XXXX;

    echo form_footer();
}


function restrict_time(&$env, &$trm, $code, $field)
{
    $valu = $env[$code];
    if ($valu > 0) {
        $midn  = $env['midn'];
        $time  = date_code($midn, $valu);
        $trm[] = "R.$field > $time";
    }
    if ($valu == -2) {
        $trm[] = "N.$field = 0";
    }
}

function list_search(&$env, $db)
{
    $p = 'p style="font-size:8pt"';
    echo <<< ZORT

        <$p>
          Click on the <i>manage</i> link below to perform 
          management actions (e.g. delete) on multiple 
          asset queries.
        </p>

        <$p>
          Clicking on the <i>control</i> and <i>table</i> links will 
          take you to the beginning of the <i>Search Options</i> panel, 
          and the query list, respectively.
        </p>
ZORT;
    $num = find_query_count($env, $db);
    $sql = gen_query($env, 0, $num);
    $set = find_many($sql, $db);
    echo mark('control');
    echo again($env);
    search_control($env, $num, $db);
    echo mark('table');
    echo again($env);

    if ($set) {
        $tmp = safe_count($set);
        debug_note("There were $tmp records loaded.");
        search_table($env, $set, $num);
    } else {
        echo "<p>There were no matching asset queries ...</p>\n";
    }
    echo again($env);
}

function age($secs)
{
    if ($secs <= 0) $secs = 0;

    $ss = intval($secs);
    $mm = intval($secs / 60);
    $hh = intval($secs / 3600);
    $dd = intval($secs / 86400);

    $ss = $ss % 60;
    $mm = $mm % 60;
    $hh = $hh % 24;

    if ($secs < 3600)
        $txt = sprintf('%d:%02d', $mm, $ss);
    if ((3600 <= $secs) && ($secs < 86400))
        $txt = sprintf('%d:%02d:%02d', $hh, $mm, $ss);
    if ((86400 <= $secs) && ($dd <= 7))
        $txt = sprintf('%d %02d:%02d:%02d', $dd, $hh, $mm, $ss);
    if (8 <= $dd) {
        $dd  = intval(round($secs / 86400));
        $txt = "$dd days";
    }

    return $txt;
}


function showtime($now, $then)
{
    if ($then <  0) return 'running';
    if ($then == 0) return 'never';
    if ($then <= $now) {
        $when = nanotime($then);
        $age  = age($now - $then);
        $text = "$when (age $age)";
    } else {
        $when = nanotime($then);
        $wait = age($then - $now);
        $text = "$when (wait $wait)";
    }
    return $text;
}






function find_criteria($qid, $db)
{
    $out = array();
    $tmp = array();
    $sql = "select * from\n"
        . " AssetSearchCriteria\n"
        . " where assetsearchid = $qid\n"
        . ' order by block, fieldname';
    $set = find_many($sql, $db);
    if ($set) {
        $ops = criteria_ops();
        reset($set);
        foreach ($set as $key => $row) {
            $blk = $row['block'];
            $val = $row['value'];
            $fld = $row['fieldname'];
            $op  = $row['comparison'];
            $cmp = $ops[$op];
            $txt = htmlspecialchars("'$fld' $cmp '$val'");
            $tmp[$blk][] = $txt;
        }
    }
    $in = indent(2);
    reset($tmp);
    foreach ($tmp as $blk => $trms) {
        $xxx = sprintf('[%02d]', $blk);
        reset($trms);
        foreach ($trms as $key => $txt) {
            $out[] = "$xxx$in$txt";
        }
    }
    return $out;
}


function query_detail_table(&$env, $qid, $links, $db)
{
    $admn = $env['user']['priv_admin'];
    $auth = $env['auth'];
    $self = $env['self'];
    $priv = $env['priv'];
    $row  = find_query($qid, $db);
    $reps = array();
    if ($row) {
        $user = $row['username'];
        $glob = $row['global'];
        $mine = ($auth == $user);
        $good = (($admn) || ($glob) || ($mine));
    }
    if ($good) {
        $a    = array();
        $now  = time();
        $qid  = $row['id'];
        $name = $row['name'];
        $ctim = $row['created'];
        $mtim = $row['modified'];
        $text = $row['searchstring'];
        $disp = $row['displayfields'];
        $reps = find_report_qid($qid, $db);

        $act = "qury-act.php?id=$qid&action";
        $cmd = "$self?qid=$qid&act";

        $a[] = html_link("$cmd=view", 'details');
        if (($mine) || ($glob)) {
            $run = "exec.php?qid=$qid";
            $exp = "export.php?qid=$qid";
            $a[] = html_link($run, 'run');
            $a[] = html_page($exp, 'export');
            $a[] = html_link("$act=duplicate", 'copy');
        }
        if ($mine) {
            $a[] = html_link("$act=edit", 'edit');
        }
        if ((!$mine) && ($glob)) {
            $a[] = html_link("$act=edit", 'edit');
        }

        if (!$reps) {
            if ($mine) {
                $a[] = html_link("$cmd=cdel", 'delete');
            }
            if ((!$mine) && ($priv)) {
                $a[] = html_link("$cmd=cdel", 'p.delete');
            }
        }

        if ($links) {
            echo jumplist($a);
        }

        $glob = ($glob) ? 'Global' : 'Local';
        $qury = ($text) ?  $text   : 'None';
        $crts = find_criteria($qid, $db);

        echo table_header();
        echo pretty_header($name, 2);
        echo double('Owner', $user);
        echo double('Scope', $glob);
        echo double('Created', showtime($now, $ctim));
        echo double('Modified', showtime($now, $mtim));
        echo double('Query', $qury);
        $text = df($disp);
        $disp = ($text) ? $text : 'None';

        echo double('Display', $disp);
        if ($priv) {
            $text = ($crts) ? join('<br>', $crts) : 'None';
            echo dgreen('Record', $qid);
            echo dgreen('Criteria', $text);
        }
        echo table_footer();

        if ($links) {
            echo jumplist($a);
        }
    } else {
        if ($row) {
            $txt = 'No access to this query.';
        } else {
            $txt = "Asset Query <b>$qid</b> does not exist.";
        }
        echo para($txt);
    }

    if (($good) && (!$priv)) {
        $reps = find_reports($qid, $auth, $db);
    }

    if (($good) && ($reps)) {
        $rows = safe_count($reps);
        $cols = ($priv) ? 3 : 1;
        $text = "Reports &nbsp; ($rows found)";
        $cmd  = 'report.php?act=view&rid';

        echo table_header();
        echo pretty_header($text, $cols);

        reset($reps);
        foreach ($reps as $key => $row) {
            $rid  = $row['id'];
            $name = html_link("$cmd=$rid", $row['name']);
            $ax   = array($name);
            if ($priv) {
                $scop = ($row['global']) ? 'Global' : 'Local';
                $ax[] = green($row['username']);
                $ax[] = green($scop);
            }
            echo table_data($ax, 0);
        }
        echo table_footer();
    }
}


function view_search(&$env, $db)
{
    echo again($env);
    $qid = $env['qid'];
    query_detail_table($env, $qid, 1, $db);
    $row  = find_query($qid, $db);
    if ($row) {
        if (PHP_REPF_CheckDeleteAssetQuery(
            CUR,
            $canDelete,
            $usedItems,
            $row['asrchuniq']
        ) != constAppNoErr) {
            echo 'An error occurred checking usage<br>';
        } else if (!($canDelete)) {
            echo 'Sections'
                . '<ul>' . $usedItems . '</ul><br>';
        }
    }
    echo again($env);
}


function tag_int($name, $min, $max, $def)
{
    $valu = get_integer($name, $def);
    return value_range($min, $max, $valu);
}

/*
    |  We want to use the same procedure to generate sql for both
    |  the counting and the selection of records.
    */

function gen_query(&$env, $count, $num)
{
    $auth = $env['auth'];
    $gbl  = $env['gbl'];
    $usr  = $env['usr'];
    $pat  = $env['pat'];
    $txt  = $env['txt'];
    $qu   = safe_addslashes($auth);
    if ($count) {
        $sel = "select count(S.id) from";
    } else {
        $sel = "select S.* from";
    }
    $lft = array();
    $ons = array();
    $trm = array();
    $tab = array(
        'AssetSearches as S'
    );

    if ($pat != '') {
        $value = str_replace('%', '\%', $pat);
        $value = str_replace('_', '\_', $value);
        $value = safe_addslashes($value);
        $trm[] = "S.name like '%$value%'";
    }

    if ($txt != '') {
        $value = str_replace('%', '\%', $txt);
        $value = str_replace('_', '\_', $value);
        $value = safe_addslashes($value);
        $trm[] = "S.searchstring like '%$value%'";
    }

    if ($usr > 0) {
        $tab[] = $GLOBALS['PREFIX'] . 'core.Users as U';
        $trm[] = "U.userid = $usr";
        $trm[] = 'U.username = S.username';
    }

    /*
        |  Global:
        |   -1: same as 0, but not displayed
        |    0: both, honor local override
        |    1: locals owned by current user
        |    2: globals, honor local override
        |    3: debug only, show all
        |
        |    glbl_options()
        */

    if (($gbl <= 0) && ($usr <= 0)) {
        $u = "username = '$qu'";
        $lft[] = 'AssetSearches as X';
        $ons[] = 'S.name = X.name';
        $ons[] = 'X.global = 0';
        $ons[] = "X.$u";
        $trm[] = "((S.$u) or (S.global = 1 and X.id is NULL))";
    }
    if (($gbl == 1) && ($usr <= 0)) {
        $trm[] = "S.username = '$qu'";
        $trm[] = 'S.global = 0';
    }
    if (($gbl == 1) && ($usr > 0)) {
        $trm[] = 'S.global = 0';
    }
    if (($gbl == 2) && ($usr <= 0)) {
        $lft[] = 'AssetSearches as X';
        $ons[] = 'X.name = S.name';
        $ons[] = 'X.global != S.global';
        $ons[] = "X.username = '$qu'";
        $trm[] = 'S.global = 1';
        $trm[] = 'X.id is NULL';
    }
    if (($gbl == 2) && ($usr > 0)) {
        $trm[] = 'S.global = 1';
    }

    restrict_time($env, $trm, 'mod', 'modified');
    restrict_time($env, $trm, 'crt', 'created');

    if (!$trm) {
        $trm[] = 'S.id > 0';  // need at least one
    }

    $onss = '';
    $lfts = '';
    $tabs = join(",\n ", $tab);
    $trms = join("\n and ", $trm);
    if ($lft) {
        $lj   = 'left join';
        $txt  = join("\n $lj ", $lft);
        $lfts = " $lj $txt\n";
    }
    if ($ons) {
        $txt  = join("\n and ", $ons);
        $onss = " on $txt\n";
    }

    if ($count) {
        $sql = "$sel\n $tabs\n${lfts}${onss} where $trms";
    } else {
        $ord  = $env['ord'];
        $page = $env['page'];
        $limt = $env['limt'];
        $ords = order($ord);
        debug_note("ord:$ord, page:$page, size:$limt");
        $pmin = ($page > 0) ? $limt * $page : 0;
        if (($num <= $limt) || ($num <= $pmin)) {
            $pmin = 0;
        }
        $sql = "$sel\n $tabs\n${lfts}${onss} where $trms\n"
            . " order by $ords\n"
            . " limit $pmin, $limt";
    }
    return $sql;
}


function find_query_count(&$env, $db)
{
    $num = 0;
    $sql = gen_query($env, 1, 0);
    $res = redcommand($sql, $db);
    if ($res) {
        $num = mysqli_result($res, 0);
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    debug_note("There are $num total matching records.");
    return $num;
}


function delete_conf(&$env, $db)
{
    echo again($env);
    $good = false;
    $qid  = $env['qid'];
    $priv = $env['priv'];
    $admn = $env['user']['priv_admin'];
    $row  = find_query($qid, $db);
    $set  = find_report_qid($qid, $db);
    $canDelete = false;

    if ($row) {
        if (PHP_REPF_CheckDeleteAssetQuery(
            CUR,
            $canDelete,
            $usedItems,
            $row['asrchuniq']
        ) != constAppNoErr) {
            echo 'An error occurred checking usage<br>';
            $canDelete = false;
        }
    }

    if (($row) && (($set) || (!$canDelete))) {
        $text = 'This query cannot be removed because it is being used.';
        echo para($text);
    }
    if ($row)
        query_detail_table($env, $qid, 0, $db);
    else {
        $text = 'Query not found ...';
        echo para($text);
    }
    if (($row) && (!$set) && ($canDelete)) {
        $auth = $env['auth'];
        $user = $row['username'];
        $mine = ($user == $auth);
        $good = (($mine) || ($admn));
    }
    if ($row && (!$canDelete)) {
        echo 'Sections'
            . '<ul>' . $usedItems . '</ul><br>';
    }
    if ($good) {
        $name = $row['name'];
        $self = $env['self'];
        $href = "$self?act=rdel&qid=$qid";
        $yes  = html_link($href, '[Yes]');
        $no   = html_link($self, '[No]');
        $in   = indent(5);

        echo <<< HERE

            <br>
            <p>Do you really want to delete <b>$name</b>?</p>
            <p>${yes}${in}${no}</p>

HERE;
    }
    echo again($env);
}


function kill_criteria($qid, $db)
{
    $sql = "delete from AssetSearchCriteria\n"
        . " where assetsearchid = $qid";
    $res = redcommand($sql, $db);
    return affected($res, $db);
}


function kill_query($qid, $db)
{
    $sql = "delete from AssetSearches\n"
        . " where id = $qid";
    $res = redcommand($sql, $db);
    return affected($res, $db);
}


/*
    |  check the username clause, just in case.
    */

function delete_act(&$env, $db)
{
    echo again($env);
    $good = false;
    $qid  = $env['qid'];
    $admn = $env['user']['priv_admin'];
    $row  = find_query($qid, $db);
    $set  = find_report_qid($qid, $db);
    if (($row) && (!$set)) {
        $auth = $env['auth'];
        $user = $row['username'];
        $mine = ($user == $auth);
        $good = (($mine) || ($admn));
    }
    if ($good) {
        $name = $row['name'];
        if (kill_query($qid, $db)) {
            $crit = kill_criteria($qid, $db);
            debug_note("$crit criteria removed");
            $text = "Asset Query <b>$name</b> has been removed.";
        } else {
            $text = 'Nothing has changed.';
        }
    } else {
        if (($row) && ($set)) {
            $text = 'This query cannot be removed because it is being used.';
        }
        if (($row) && (!$set)) {
            $text = 'Authorization denied.';
        }
        if (!$row) {
            $text = 'Query not found.';
        }
    }
    echo para($text);
    echo again($env);
}


function command_list(&$act, &$txt)
{
    echo "<p>What do you want to do?</p>\n\n\n<ol>\n";

    reset($txt);
    foreach ($txt as $key => $doc) {
        $cmd = html_link($act[$key], $doc);
        echo "<li>$cmd</li>\n";
    }
    echo "</ol>\n";
}


function manage_query(&$env, $db)
{
    echo again($env);
    $self = $env['self'];
    $jump = $env['jump'];
    $priv = $env['priv'];
    $glob = ($priv) ? 3 : 0;
    $cmd  = "$self?act";

    $comp = "$cmd=list&flt=-1&dsp=1&gbl=$glob";
    $last = "$comp&mod=0&usr=0&o=6$jump";
    $back = gang_href($env, 'list') . $jump;

    $act = array();
    $txt = array();

    $act[] = gang_href($env, 'gdel');
    $txt[] = 'Delete Multiple Queries';

    $act[] = $back;
    $txt[] = 'Back to List Queries Page';

    $act[] = 'qury-add.php';
    $txt[] = 'Create A New Query';

    $act[] = $last;
    $txt[] = 'Recently Modified Queries';

    $act[] = $self . $jump;
    $txt[] = 'Query Default View';

    command_list($act, $txt);
    echo again($env);
}


function gang_table(&$env, &$set, $frm, $head)
{
    $rows = safe_count($set);

    if ($rows <= 0) {
        return;
    }

    $cols = (12 <= $rows) ? 4 : 1;
    if ($frm) {
        $post = $env['post'];
        $aflg = ($post == constButtonAll);
        $nflg = ($post == constButtonNone);
    }

    $out = array();
    $tmp = array();

    reset($set);
    foreach ($set as $nnn => $data) {
        $nam = $data['name'];
        if ($frm) {
            $qid = $data['id'];
            $tag = "qid_$qid";
            $chk = get_integer($tag, 0);
            $chk = ($aflg) ? 1 : $chk;
            $chk = ($nflg) ? 0 : $chk;
            $box = checkbox($tag, $chk) . '&nbsp;';
            $txt = $box . $nam;
        } else {
            $txt = $nam;
        }
        $tmp[$nnn] = $txt;
    }

    if ($cols > 1) {
        $dec = $cols - 1;
        $max = intval(($rows + $dec) / $cols);
        for ($row = 0; $row < $max; $row++) {
            for ($col = 0; $col < $cols; $col++) {
                $out[$row][$col] = '<br>';
            }
        }
    } else {
        $max = $rows;
        $col = 0;
    }

    reset($tmp);
    foreach ($tmp as $nnn => $txt) {
        if ($cols > 1) {
            $row = intval($nnn % $max);
            $col = intval($nnn / $max);
        } else {
            $row = $nnn;
        }
        $out[$row][$col] = $txt;
    }

    $text = "$head &nbsp; ($rows found)";

    echo table_header();
    echo pretty_header($text, $cols);

    reset($out);
    foreach ($out as $key => $args) {
        echo table_data($args, 0);
    }
    echo table_footer();
}


function gdel_conf(&$env, $db)
{
    $num = find_query_count($env, $db);
    $set = array();
    $ids = array();
    if ($num) {
        $sql = gen_query($env, 0, $num);
        $set = find_many($sql, $db);
    }

    echo mark('table');
    echo again($env);
    if ($set) {
        reset($set);
        foreach ($set as $key => $data) {
            $qid = $data['id'];
            $tag = "qid_$qid";
            if (get_integer($tag, 0)) {
                $ids[] = $qid;
            }
        }
        $set = array();
    }

    if ($ids) {
        $txt = join(',', $ids);
        $sql = "select * from AssetSearches\n"
            . " where id in ($txt)";
        if (!$env['user']['priv_admin']) {
            $qu  = safe_addslashes($env['auth']);
            $sql = "$sql\n and username = '$qu'";
        }
        $set = find_many($sql, $db);
    }

    $next = ($set) ? 'gexp' : 'list';
    $self = $env['self'];
    $jump = $env['jump'];
    $form = $self . $jump;

    echo post_other('myform', $form);
    echo hidden('act', $next);
    echo hidden('p', $env['page']);
    echo hidden('o', $env['ord']);
    echo hidden('l', $env['limt']);
    echo hidden('debug', '1');
    preserve($env, 'dsp,flt,usr,gbl,crt,mod,pat');

    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $qid = $row['id'];
            $tag = "qid_$qid";
            echo hidden($tag, '1');
        }
        $txt = 'Asset Queries to be Deleted';
        gang_table($env, $set, 0, $txt);
        echo para('Delete these queries?');
        echo okcancel(5);
    } else {
        $cont = button('Continue');
        echo para('You are not allowed to delete any of those queries.');
        echo para($cont);
    }
    echo form_footer();
    echo again($env);
}


function okcancel($n)
{
    $in  = indent($n);
    $ok  = button(constButtonOk);
    $can = button(constButtonCan);
    $txt = "${in}${ok}${in}${can}";
    return para($txt);
}


function checkallnone($n)
{
    $in  = indent($n);
    $all = button(constButtonAll);
    $non = button(constButtonNone);
    $txt = "${in}${all}${in}${non}";
    return para($txt);
}


function preserve(&$env, $txt)
{
    $tags = explode(',', $txt);
    if ($tags) {
        reset($tags);
        foreach ($tags as $key => $tag) {
            echo hidden($tag, $env[$tag]);
        }
    }
}



function gdel_form(&$env, $db)
{
    $num = find_query_count($env, $db);
    $set = array();
    if ($num) {
        $sql = gen_query($env, 0, $num);
        $set = find_many($sql, $db);
    }

    echo mark('table');
    echo again($env);
    if ($set) {
        $self = $env['self'];
        $jump = $env['jump'];
        $form = $self . $jump;
        echo post_other('myform', $form);
        echo hidden('act', 'gdel');
        echo hidden('p', $env['page']);
        echo hidden('o', $env['ord']);
        echo hidden('l', $env['limt']);
        echo hidden('debug', '1');
        preserve($env, 'dsp,flt,usr,gbl,crt,mod,pat');

        echo okcancel(5);
        echo checkallnone(5);

        $txt = 'Delete Asset Queries';
        gang_table($env, $set, 1, $txt);

        echo okcancel(5);
        echo checkallnone(5);
        echo form_footer();
    }

    echo again($env);
}


function gdel_exec(&$env, $db)
{
    echo mark('table');
    echo again($env);

    $num = find_query_count($env, $db);
    $set = array();
    $ids = array();
    if ($num) {
        $sql = gen_query($env, 0, $num);
        $set = find_many($sql, $db);
        $num = 0;
    }

    if ($set) {
        reset($set);
        foreach ($set as $key => $data) {
            $qid = $data['id'];
            $tag = "qid_$qid";
            if (get_integer($tag, 0)) {
                $ids[] = $qid;
            }
        }
    }

    $set = array();
    $out = array();
    $num = 0;
    if ($ids) {
        $txt = join(',', $ids);
        $sql = "select * from AssetSearches\n"
            . " where id in ($txt)";
        if (!$env['user']['priv_admin']) {
            $qu  = safe_addslashes($env['auth']);
            $sql = "$sql\n and username = '$qu'";
        }
        $set = find_many($sql, $db);
    }

    if ($set) {
        $txt = 'Asset Queries to be Deleted';
        gang_table($env, $set, 0, $txt);
    }

    reset($set);
    foreach ($set as $key => $row) {
        $qid  = $row['id'];
        $name = $row['name'];
        $user = $row['username'];
        $rep  = find_report_qid($qid, $db);
        if ($rep) {
            $text = 'query is used';
        } else {
            if (kill_query($qid, $db)) {
                $num++;
                $crit = kill_criteria($qid, $db);
                $text = 'deleted';
                debug_note("$num, $qid: $name, $crit criteria");
            } else {
                $text = 'not deleted';
            }
        }
        $out[] = array($name, $text);
    }

    if ($out) {
        $cols = 2;
        $text = "$num Deleted";

        echo table_header();
        echo pretty_header($text, $cols);
        reset($out);
        foreach ($out as $key => $row) {
            $name = $row[0];
            $text = $row[1];
            echo double($name, $text);
        }
        echo table_footer();
    } else {
        echo para('Nothing has changed');
    }

    $self = $env['self'];
    $jump = $env['jump'];
    $form = $self . $jump;
    $cont = button('Continue');

    echo post_other('myform', $form);
    echo hidden('act', 'list');
    echo hidden('p', $env['page']);
    echo hidden('o', $env['ord']);
    echo hidden('l', $env['limt']);
    preserve($env, 'dsp,flt,usr,gbl,crt,mod,pat');
    echo para($cont);
    echo form_footer();
    echo again($env);
}


function gdel_disp(&$env, $db)
{
    $post = $env['post'];
    $done = ($post == constButtonOk);
    if ($done)
        gdel_conf($env, $db);
    else
        gdel_form($env, $db);
}


/*
    |  Main program
    */

$db   = db_connect();
$now  = time();
$auth = process_login($db);
$comp = component_installed();
$user = user_data($auth, $db);
$priv = @($user['priv_debug']) ? 1 : 0;
$admn = @($user['priv_admin']) ? 1 : 0;
$dbg  = get_integer('debug', 0);
$act  = get_string('act', 'list');
$post = get_string('button', '');
$asrchuniq = get_string('asrchuniq', '');
if ($post == constButtonCan) {
    $act = 'list';
}
$name = title($act);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($name, $comp, $auth, 0, 0, 0, $db);
$debug = ($priv) ? $dbg : 0;
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

debug_array($debug, $_POST);

$dsp = tag_int('dsp', 0, 1, 0);       // display, expanded
$flt = tag_int('flt', -1, 0, 0);       // filter, not displayed
$crt = tag_int('crt', -2, 9999, -1);   // create, no display
$mod = tag_int('mod', -2, 9999, -1);   // modified, no display
$gbl = tag_int('gbl', -1, 3, -1);      // global, not displayed
$pag = tag_int('p', 0, 9999, 0);;
$lim = tag_int('l', 5, 5000, 20);
$ord = tag_int('o', 0, 15, 0);
$fld = get_integer('fld', -1);       // show display fields
$usr = get_integer('usr', -1);
$pat = get_string('pat', '');
$txt = get_string('txt', '');

if (!$admn) {
    $usr = value_range(-1, 0, $usr);
    $gbl = value_range(-1, 2, $gbl);
}

if ($post == constButtonRst) {
    $dsp = 0;
    $gbl = -1;
    $lim = 20;
    $pag = 0;
    $usr = -1;
    $pat = '';
    $flt = 0;
    $crt = -1;
    $txt = '';
    $ord = 0;
    $mod = -1;
    $fld = -1;
}

$env = array();
$env['href'] = 'page_href';
$env['midn'] = midnight($now);
$env['post'] = $post;
$env['auth'] = $auth;
$env['user'] = $user;
$env['priv'] = $priv;
$env['page'] = $pag;
$env['limt'] = $lim;
$env['dbug'] = $debug;
$env['jump'] = '#table';
$env['self'] = server_var('PHP_SELF');
$env['args'] = server_var('QUERY_STRING');

$env['d_act'] = (0 == $dsp);
$env['d_nam'] = (true);
$env['d_crt'] = (0 <= $crt);
$env['d_mod'] = (0 <= $mod);
$env['d_flt'] = (0 <= $flt);
$env['d_fld'] = (0 <= $fld);
$env['d_qid'] = ($gbl == 3);
$env['d_usr'] = ((0 <= $usr) || ($gbl == 3));
$env['d_gbl'] = ((0 == $gbl) || ($gbl == 3));

$env['act'] = $act;
$env['crt'] = $crt;
$env['usr'] = $usr;
$env['mod'] = $mod;
$env['gbl'] = $gbl;
$env['dsp'] = $dsp;
$env['flt'] = $flt;
$env['fld'] = $fld;
$env['ord'] = $ord;
$env['pat'] = $pat;
$env['txt'] = $txt;
$env['qid'] = get_integer('qid', 0);

db_change($GLOBALS['PREFIX'] . 'asset', $db);

if (($env['qid'] == 0) && ($asrchuniq != '')) {
    $sql = 'SELECT id FROM AssetSearches WHERE asrchuniq=\'' . $asrchuniq
        . '\'';
    $row = find_one($sql, $db);
    if ($row) {
        $env['qid'] = $row['id'];
    }
}
switch ($act) {
    case 'list':
        list_search($env, $db);
        break;
    case 'view':
        view_search($env, $db);
        break;
    case 'menu':
        debug_menu($env, $db);
        break;
    case 'cdel':
        delete_conf($env, $db);
        break;
    case 'rdel':
        delete_act($env, $db);
        break;
    case 'gdel':
        gdel_disp($env, $db);
        break;
    case 'gexp':
        gdel_exec($env, $db);
        break;
    case 'mnge':
        manage_query($env, $db);
        break;
    default:
        list_search($env, $db);
        break;
}
echo head_standard_html_footer($auth, $db);
