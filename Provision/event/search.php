<?php

/*
Revision history:

Date        Who     What
----        ---     ----
11-Sep-02   EWB     Merge with new asset code.
17-Sep-02   EWB     Better format for asset searchstring display.
19-Sep-02   EWB     Giant refactoring.
20-Sep-02   EWB     Back to events only version.
13-Nov-02   EWB     Log event of mysql failures.
 4-Dec-02   EWB     Reorginization Day
 6-Dec-02   EWB     Local Navagation
 9-Jan-03   EWB     Do not require register_globals.
 9-Jan-03   EWB     Debug users can see owner of search.
 7-Feb-03   EWB     New database format.
11-Feb-03   EWB     db_change()
21-Feb-03   EWB     Don't give option to delete stuff we don't own.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added lib path back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header().
14-Apr-03   NL      Move debug_note line below $debug.
22-May-03   EWB     Quote Crusade.
18-Jun-03   EWB     Slave Database
20-Jun-03   EWB     No Slave Database.
 7-Dec-04   EWB     Column Sort Table
 7-Dec-04   EWB     Paging for Search Table
14-Dec-04   EWB     Always provide "copy" link.
16-Dec-04   EWB     Name Contains / Filter Contains
16-Dec-04   EWB     Delete Search
19-Jan-05   EWB     Fixed a few sql bugs Allan found.
25-Jan-05   EWB     Help / Reset buttons.
 2-Feb-05   EWB     Help in new window.
 4-Feb-05   EWB     Select by owner still displays column
 7-Feb-05   EWB     New help page.
21-Nov-05   NL      Added some comments.
21-Jun-06   BTE     Bug 3466: The primary census server page is no longer in
                    order.
19-Sep-06   WOH     Bug 3657: Changed name html_footer.  Added username arg.
04-Jun-07   BTE     Added calls to PHP_REPF_UpdateDynamicList.
20-Jun-07   BTE     Bug 4152: Event sections: make sure all buttons work.
08-Jul-07   BTE     Bug 4226: Deleting management items need to verify they are
                    not in use.

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

define('constButtonCan',  'Cancel');
define('constButtonHlp',  'Help');
define('constButtonRst',  'Reset');
define('constButtonSub',  'Search');

function title($act)
{
    $e = 'Event';
    $q = 'Query';
    $f = 'Filter';
    switch ($act) {
        case 'list':
            return "$e $q ${f}s";
        case 'view':
            return "$e $q $f Details";
        case 'cdel':
            return "Confirm Delete $e $q $f";
        case 'rdel':
            return "Delete $e $q $f";
        default:
            return "$e $q ${f}s";
    }
}


function again($env)
{
    $priv = $env['priv'];
    $self = $env['self'];
    $act  = $env['act'];

    $a = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link('srch-add.php', 'add');
    if ($act == 'list') {
        $a[] = html_link('#control', 'control');
        $a[] = html_link('#searches', 'table');
    }
    if ($act == 'view') {
        $a[] = html_link($self, 'filter');
    }
    if ($priv) {
        $args = $env['args'];
        $jump = $env['jump'];
        $comp = "$self?act=list&dsp=1";
        $full = "$comp&gbl=3&flt=-1&usr=0&mod=0&crt=0$jump";
        $href = ($args) ? "$self?$args" : $self;
        $a[] = html_link($full, 'compact');
        $a[] = html_link('../acct/index.php', 'home');
        $a[] = html_link($href, 'again');
    }
    return jumplist($a);
}


function green($msg)
{
    return "<font color=\"green\">$msg</font>";
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
        4 => "Search ($a)",
        5 => "Search ($d)",
        6 => "Modify ($d)",
        7 => "Modify ($a)",
        8 => "Created ($d)",
        9 => "Created ($a)",
        10 => "Global ($a)",
        11 => "Global ($d)",
        12 => "Id ($a)",
        13 => "Id ($d)"
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
        default:
            return order(0);
    }
}

function query_state(&$env, &$set)
{
    $crt = $env['crt'];
    $mod = $env['mod'];
    $gbl = $env['gbl'];
    $dsp = $env['dsp'];
    $usr = $env['usr'];
    $flt = $env['flt'];
    $pat = $env['pat'];
    $txt = $env['txt'];
    $dbg = $env['dbug'];
    $prv = $env['priv'];

    if ($dsp != 0) $set[] = "dsp=$dsp";
    if ($flt != 0) $set[] = "flt=$flt";

    if ($usr != -1) $set[] = "usr=$usr";
    if ($gbl != -1) $set[] = "gbl=$gbl";
    if ($crt != -1) $set[] = "crt=$crt";
    if ($mod != -1) $set[] = "mod=$mod";
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


// returns array of none, any, and if adminuser, userid:username for all users.  
// Used for OWNER select box
/* --------------------------------------------------------\
    |  owns_options returns a 2D array of display options      |
    |  (currently none, any)                                   |
    |  and if adminuser, userid:username for all users.        |
    |  Used for Owner select box.                              |
    \*--------------------------------------------------------*/
function owns_options(&$env, $db)
{
    $set = array();
    $out = disp_options();
    if ($env['user']['priv_admin']) {
        $sql = "select userid, username\n"
            . " from " . $GLOBALS['PREFIX'] . "core.Users\n"
            . " order by CONVERT(username USING latin1)";
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


function find_search($sid, $db)
{
    $row = array();
    if ($sid) {
        $sql = "select * from SavedSearches\n"
            . " where id = $sid";
        $row = find_one($sql, $db);
    }
    return $row;
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


function show(&$env, &$args, $tag, $col)
{
    if ($env[$tag]) $args[] = $col;
}


function search_table(&$env, $set, $total)
{
    $ord  = $env['ord'];    // selected option for "Sort By"
    $dsp  = $env['dsp'];    // selected option for "Display"
    $lim  = $env['limt'];   // selected option for "Page Size"
    $self = $env['self'];
    $priv = $env['priv'];
    $auth = $env['auth'];
    $jump = $env['jump'];

    $args = array("$self?act=list&l=$lim");
    query_state($env, $args);
    $o = join('&', $args) . "&o";

    // unfortunately, these var names are misused for sort order
    $name = ($ord ==  0) ? "$o=1"  : "$o=0";     // name   0, 1
    $user = ($ord ==  2) ? "$o=3"  : "$o=2";     // user   2, 3
    $text = ($ord ==  4) ? "$o=5"  : "$o=4";     // text   4, 5
    $mtim = ($ord ==  6) ? "$o=7"  : "$o=6";     // mtim   6, 7
    $ctim = ($ord ==  8) ? "$o=9"  : "$o=8";     // ctim   8, 9
    $glob = ($ord == 10) ? "$o=11" : "$o=10";    // glob  10,11
    $ssid = ($ord == 12) ? "$o=13" : "$o=12";    // ssid  12,13

    $acts = 'Action';
    $ssid = html_jump($ssid, $jump, 'Id');    // html_jump() creates a hyperlink
    $name = html_jump($name, $jump, 'Name');
    $user = html_jump($user, $jump, 'Owner');
    $glob = html_jump($glob, $jump, 'Scope');
    $ctim = html_jump($ctim, $jump, 'Created');
    $mtim = html_jump($mtim, $jump, 'Modified');
    $text = html_jump($text, $jump, 'Filter');

    $head = array();
    show($env, $head, 'd_act', $acts); // show() creates a hyperlink col header...
    show($env, $head, 'd_nam', $name); // ...and adds it to the $head array
    show($env, $head, 'd_usr', $user);
    show($env, $head, 'd_gbl', $glob);
    show($env, $head, 'd_crt', $ctim);
    show($env, $head, 'd_mod', $mtim);
    show($env, $head, 'd_flt', $text);
    show($env, $head, 'd_sid', $ssid);

    $page = 'srch-act.php';
    $cols = safe_count($head);
    $rows = safe_count($set);
    $text = "Event Filters &nbsp; ($total found)";
    $acts = '<br>';
    $tiny = (!$dsp);

    echo table_header();
    echo pretty_header($text, $cols);
    echo table_data($head, 1);

    reset($set);
    foreach ($set as $key => $row) {
        // now these var names are used for their actual values
        $sid  = $row['id'];
        $name = $row['name'];
        $glob = $row['global'];
        $ctim = $row['created'];
        $mtim = $row['modified'];
        $user = $row['username'];
        $text = $row['searchstring'];
        $mine = ($user == $auth);

        $view = "$self?act=view&sid=$sid";
        $name = html_target($view, 'detail', $name);
        $mtim = nanotime($mtim);
        $ctim = nanotime($ctim);
        $scop = ($glob) ? 'Global' : 'Local';

        if ($tiny) {
            $a   = array();
            $act = "$page?id=$sid&action";
            $cmd = "$self?sid=$sid&act";
            if ($mine) {
                $a[] = html_link("$act=edit", '[edit]');
                $a[] = html_link("$cmd=cdel", '[delete]');
            }
            $a[] = html_link("$act=duplicate", '[copy]');
            if ((!$mine) && ($glob)) {
                $a[] = html_link("$act=edit", '[edit]');
            }
            $acts = implode("<br>\n", $a);
        }

        $args = array();
        show($env, $args, 'd_act', $acts);
        show($env, $args, 'd_nam', $name);
        show($env, $args, 'd_usr', $user);
        show($env, $args, 'd_gbl', $scop);
        show($env, $args, 'd_crt', $ctim);
        show($env, $args, 'd_mod', $mtim);
        show($env, $args, 'd_flt', $text);
        show($env, $args, 'd_sid', $sid);
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
    $usrs = owns_options($env, $db); // 2D array for Owner select box.
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
    $s_dbg = tiny_select('debug', $yn, $env['dbug'], 1, $norm);
    $s_pat = tinybox('pat', 40, $env['pat'], $norm);
    $s_txt = tinybox('txt', 40, $env['txt'], $norm);

    $href = 'search.htm';
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
                <$td>Name Contains <br>$s_pat </td>
                <$td>Scope         <br>$s_gbl </td>
                <$td>Filter        <br>$s_flt </td>
                <$td>Owner         <br>$s_usr </td>
              </tr>
              <tr>
                <$td>Filter Contains <br>$s_txt </td>
                <$td>Created         <br>$s_crt </td>
                <$td>Modified        <br>$s_mod </td>
                <$td>$dbug           <br>$s_dbg </td>
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
          Clicking on the <i>control</i> and <i>table</i> links will 
          take you to the beginning of the <i>Search Options</i> panel, 
          and the event filter list, respectively.
        </p>
ZORT;
    $num = find_search_count($env, $db);
    $sql = gen_query($env, 0, $num);
    $set = find_many($sql, $db);
    echo mark('control');
    echo again($env);
    search_control($env, $num, $db);
    echo mark('searches');
    echo again($env);

    if ($set) {
        $tmp = safe_count($set);
        debug_note("There were $tmp records loaded.");
        search_table($env, $set, $num);
    } else {
        echo "<p>There were no matching event filters...</p>\n";
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


function detail_links(&$env, &$row, &$reps, &$nots)
{
    $a    = array();
    $priv = $env['priv'];
    $auth = $env['auth'];
    $self = $env['self'];
    $sid  = $row['id'];
    $glob = $row['global'];
    $user = $row['username'];
    $mine = ($auth == $user);
    $page = 'srch-act.php';

    $act = "$page?id=$sid&action";
    $cmd = "$self?sid=$sid&act";
    $a[] = html_link("$cmd=view", 'details');
    $a[] = html_link("$act=duplicate", 'copy');
    if ($mine) {
        $a[] = html_link("$act=edit", 'edit');
    }
    if ((!$mine) && ($glob)) {
        $a[] = html_link("$act=edit", 'edit');
    }
    if ((!$reps) && (!$nots)) {
        if ($mine) {
            $a[] = html_link("$cmd=cdel", 'delete');
        }
        if (($priv) && (!$mine)) {
            $a[] = html_link("$cmd=cdel", 'p.delete');
        }
    }
    return $a;
}


function search_detail_table(&$env, $sid, $link, $msg, $db)
{
    $admn = $env['user']['priv_admin'];
    $auth = $env['auth'];
    $self = $env['self'];
    $priv = $env['priv'];
    $row  = find_search($sid, $db);
    $reps = find_report_sid($sid, $db);
    $nots = find_notify_sid($sid, $db);
    if ($row) {
        $user = $row['username'];
        $glob = $row['global'];
        $mine = ($auth == $user);
        $good = (($admn) || ($glob) || ($mine));
    }
    if ($good) {
        $now  = time();
        $sid  = $row['id'];
        $name = $row['name'];
        $ctim = $row['created'];
        $mtim = $row['modified'];
        $text = disp($row, 'searchstring');
        $scop = ($glob) ? 'Global' : 'Local';

        if ($link) {
            $a = detail_links($env, $row, $reps, $nots);
            echo jumplist($a);
        }

        echo table_header();
        echo pretty_header($name, 2);
        echo double('Owner', $user);
        echo double('Scope', $scop);
        echo double('Created', showtime($now, $ctim));
        echo double('Modified', showtime($now, $mtim));
        echo double('Record', $sid);
        echo double('Filter', $text);
        echo table_footer();

        if ($link) {
            echo jumplist($a);
        }
    } else {
        if ($row) {
            $txt = 'No access to this filter.';
        } else {
            $txt = "Filter <b>$sid</b> does not exist.";
        }
        echo para($txt);
    }

    if ($good) {
        echo $msg;
    }

    if (($good) && (!$priv)) {
        $reps = find_report_auth($sid, $auth, $db);
        $nots = find_notify_auth($sid, $auth, $db);
    }

    if (($good) && ($nots)) {
        $rows = safe_count($nots);
        $cols = ($priv) ? 3 : 1;
        $text = "Notifications &nbsp; ($rows found)";
        $cmd  = 'notify.php?act=view&nid';

        echo table_header();
        echo pretty_header($text, $cols);

        reset($nots);
        foreach ($nots as $key => $data) {
            $aa   = array();
            $nid  = $data['id'];
            $name = $data['name'];
            $aa[] = html_link("$cmd=$nid", $name);
            if ($priv) {
                $user = $data['username'];
                $glob = $data['global'];
                $scop = ($glob) ? 'Global' : 'Local';
                $aa[] = green($user);
                $aa[] = green($scop);
            }
            echo table_data($aa, 0);
        }
        echo table_footer();
    }

    if (($good) && ($reps)) {
        $rows = safe_count($reps);
        $cols = ($priv) ? 3 : 1;
        $text = "Reports &nbsp; ($rows found)";
        $cmd  = 'report.php?act=view&rid';

        echo table_header();
        echo pretty_header($text, $cols);

        reset($reps);
        foreach ($reps as $key => $data) {
            $rid  = $data['id'];
            $name = $data['name'];
            $link = html_link("$cmd=$rid", $name);
            $aa   = array($link);
            if ($priv) {
                $user = $data['username'];
                $glob = $data['global'];
                $scop = ($glob) ? 'Global' : 'Local';
                $aa[] = green($user);
                $aa[] = green($scop);
            }
            echo table_data($aa, 0);
        }
        echo table_footer();
    }
}


function view_search(&$env, $db)
{
    echo again($env);
    $sid = $env['sid'];
    search_detail_table($env, $sid, 1, '', $db);
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
    $pat  = $env['pat'];
    $usr  = $env['usr'];
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
        'SavedSearches as S'
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
        $trm[] = 'U.username = S.username';
        $trm[] = "U.userid = $usr";
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
        $lft[] = 'SavedSearches as X';
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
        $lft[] = 'SavedSearches as X';
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


function find_search_count(&$env, $db)
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


function find_report_sid($sid, $db)
{
    $set = array();
    if ($sid) {
        $sql = "select * from Reports\n"
            . " where search_list like '%,$sid,%'\n"
            . " order by name, username, global, id";
        $set = find_many($sql, $db);
    }
    return $set;
}


function find_report_auth($sid, $auth, $db)
{
    $set = array();
    if ($sid) {
        $qu  = safe_addslashes($auth);
        $sql = "select * from Reports\n"
            . " where search_list like '%,$sid,%'\n"
            . " and (global = 1 or\n"
            . " username = '$qu')\n"
            . " order by name, username, global, id";
        $set = find_many($sql, $db);
    }
    return $set;
}


function find_notify_sid($sid, $db)
{
    $set = array();
    if ($sid) {
        $sql = "select * from Notifications\n"
            . " where search_id = $sid\n"
            . " order by name, username, global, id";
        $set = find_many($sql, $db);
    }
    return $set;
}

function find_notify_auth($sid, $auth, $db)
{
    $set = array();
    if ($sid) {
        $qu  = safe_addslashes($auth);
        $sql = "select * from Notifications\n"
            . " where search_id = $sid\n"
            . " and (global = 1 or\n"
            . " username = '$qu')\n"
            . " order by name, username, global, id";
        $set = find_many($sql, $db);
    }
    return $set;
}

function delete_conf(&$env, $db)
{
    echo again($env);
    $good = false;
    $sid  = $env['sid'];
    $priv = $env['priv'];
    $admn = $env['user']['priv_admin'];
    $row  = find_search($sid, $db);
    $reps = find_report_sid($sid, $db);
    $nots = find_notify_sid($sid, $db);

    $canDelete = false;
    if ($row) {
        if (PHP_REPF_CheckDeleteSavedSearch(
            CUR,
            $canDelete,
            $usedItems,
            $row['searchuniq']
        ) != constAppNoErr) {
            echo 'An error occurred checking usage<br>';
            $canDelete = false;
        }
    }

    if ($row) {
        $text = '';
        if (($reps) || ($nots) || (!($canDelete))) {
            $text = para('The above event filter cannot be removed '
                . 'because it is being used by the following items:');
        }
        search_detail_table($env, $sid, 0, $text, $db);
    } else {
        $text = 'Query not found ...';
        echo para($text);
    }
    if (!($canDelete)) {
        echo 'Sections'
            . '<ul>' . $usedItems . '</ul><br>';
    }
    if (($row) && (!$reps) && (!$nots)) {
        $auth = $env['auth'];
        $user = $row['username'];
        $mine = ($user == $auth);
        $good = (($mine) || ($admn));
    }
    if ($good) {
        $name = $row['name'];
        $self = $env['self'];
        $href = "$self?act=rdel&sid=$sid";
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


function kill_search($sid, $db)
{
    $sql = "delete from SavedSearches\n"
        . " where id = $sid";
    $res = redcommand($sql, $db);
    PHP_REPF_UpdateDynamicList(CUR, constJavaListEventFilters);
    return affected($res, $db);
}


function delete_act(&$env, $db)
{
    echo again($env);
    $good = false;
    $sid  = $env['sid'];
    $admn = $env['user']['priv_admin'];
    $row  = find_search($sid, $db);
    $reps = find_report_sid($sid, $db);
    $nots = find_notify_sid($sid, $db);
    if (($row) && (!$reps) && (!$nots)) {
        $auth = $env['auth'];
        $user = $row['username'];
        $mine = ($user == $auth);
        $good = (($mine) || ($admn));
    }
    if ($good) {
        if (kill_search($sid, $db)) {
            $name = $row['name'];
            $text = "Event Filter <b>$name</b> has been removed.";
        } else {
            $text = 'Nothing has changed.';
        }
    } else {
        if ($row) {
            if (($reps) || ($nots)) {
                $text = 'This event filter cannot be removed because it is being used.';
            } else {
                $text = 'Authorization denied.';
            }
        } else {
            $text = 'Event filter not found.';
        }
    }
    echo para($text);
    echo again($env);
}



/*
    |  Main program
    */

$now  = time();
$db   = db_connect();
$auth = process_login($db);
$comp = component_installed();
$user = user_data($auth, $db);
$priv = @($user['priv_debug']) ? 1 : 0;
$admn = @($user['priv_admin']) ? 1 : 0;
$dbg  = get_integer('debug', 0);
$act  = get_string('act', 'list');
$post = get_string('button', '');
$name = title($act);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($name, $comp, $auth, 0, 0, 0, $db);
$debug = ($priv) ? $dbg : 0;
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

debug_array($debug, $_POST);

$dsp = tag_int('dsp', 0, 1, 0);       // display, expanded
$gbl = tag_int('gbl', -1, 3, -1);      // global, not displayed
$flt = tag_int('flt', -1, 0, 0);       // filter, not displayed
$crt = tag_int('crt', -2, 9999, -1);   // create, no display
$mod = tag_int('mod', -2, 9999, -1);   // modified, no display
$ord = tag_int('o', 0, 13, 0);
$pag = tag_int('p', 0, 9999, 0);
$lim = tag_int('l', 5, 5000, 20);
$usr = get_integer('usr', -1);
$pat = get_string('pat', '');
$txt = get_string('txt', '');

if (!$admn) {
    $usr = value_range(-1, 0, $usr);
    $gbl = value_range(-1, 2, $gbl);
}

if ($post == constButtonRst) {
    $gbl = -1;
    $dsp = 0;
    $pat = '';
    $usr = -1;
    $pag = 0;
    $txt = '';
    $crt = -1;
    $flt = 0;
    $lim = 20;
    $mod = -1;
    $ord = 0;
}

$sid = get_integer('sid', 0);
$uniq = get_string('uniq', '');
if (($sid == 0) && ($uniq != '')) {
    $sql = "SELECT id FROM event.SavedSearches WHERE searchuniq='$uniq'";
    $row = find_one($sql, $db);
    if ($row) {
        $sid = $row['id'];
    }
}

$env = array();
$env['href'] = 'page_href';
$env['midn'] = midnight($now);
$env['auth'] = $auth;
$env['user'] = $user;
$env['priv'] = $priv;
$env['page'] = $pag;
$env['limt'] = $lim;
$env['self'] = server_var('PHP_SELF');
$env['args'] = server_var('QUERY_STRING');
$env['dbug'] = $debug;
$env['jump'] = '#searches';

$env['d_nam'] = (true);
$env['d_act'] = (0 == $dsp);    // don't display actions col if display == compact
$env['d_crt'] = (0 <= $crt);
$env['d_mod'] = (0 <= $mod);
$env['d_flt'] = (0 <= $flt);
$env['d_sid'] = ($gbl == 3);
$env['d_usr'] = ((0 <= $usr) || ($gbl == 3));
$env['d_gbl'] = ((0 == $gbl) || ($gbl == 3));

$env['act'] = $act;
$env['crt'] = $crt;
$env['usr'] = $usr;
$env['mod'] = $mod;
$env['gbl'] = $gbl;
$env['dsp'] = $dsp;
$env['flt'] = $flt;
$env['sid'] = $sid;
$env['ord'] = $ord;
$env['pat'] = $pat;
$env['txt'] = $txt;

db_change($GLOBALS['PREFIX'] . 'event', $db);
switch ($act) {
    case 'list':
        list_search($env, $db);
        break;
    case 'view':
        view_search($env, $db);
        break;
    case 'cdel':
        delete_conf($env, $db);
        break;
    case 'rdel':
        delete_act($env, $db);
        break;
    default:
        list_search($env, $db);
        break;
}
echo head_standard_html_footer($auth, $db);
