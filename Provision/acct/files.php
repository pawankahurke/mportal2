<?php

/*
Revision history:

Date        Who     What
----        ---     ----
17-Jul-03   EWB     Created.
17-Jul-03   EWB     Sort Options.
17-Jul-03   EWB     Show file sizes.
21-Jul-03   EWB     Changed title.
24-Jul-03   EWB     Files menu reports counts.
28-Jul-03   EWB     Title pages.
29-Jul-03   EWB     Edit/Update file.
31-Jul-03   EWB     Restricted Users.
 6-Aug-03   EWB     Prompt changes
 7-Aug-03   EWB     Control Box
 7-Aug-03   EWB     Next/Previous
11-Aug-03   EWB     Minor prompt changes
21-Aug-03   EWB     Added sorting to control box.
29-Aug-03   EWB     Purge by file size.
 2-Sep-03   EWB     knows about file site records.
 3-Sep-03   EWB     Moved file delete into library code.
 3-Sep-03   EWB     started select by site.
 4-Sep-03   EWB     subset file selection
 5-Sep-03   EWB     only create/drop temp tables once per run.
 5-Sep-03   EWB     show event count in debug display.
 8-Oct-03   EWB     show site count for report in debug display.
 8-Oct-03   EWB     fixed a major bug in filtersite access.
21-Nov-03   EWB     debug sort/show counts in file list.
17-Mar-04   EWB     added meter reports, show counts always.
25-Jun-04   EWB     added event count for restricted users.
20-Jun-05   EWB     changed "insert" to "insert ignore"
21-Jun-05   EWB     select distinct.
16-Jun-06   BTE     Bug 3481: Fix user access for reports.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.
10-Apr-08   BTE     Bug 4613: Fix titles on Global Mentoring.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-rcmd.php');
include('../lib/l-serv.php');
include('../lib/l-user.php');
include('../lib/l-gsql.php');
include('../lib/l-jump.php');
include('../lib/l-slct.php');
include('../lib/l-sets.php');
include('../lib/l-date.php');
include('../lib/l-rest.php');
include('../lib/l-cmth.php');
include('../lib/l-file.php');
include('../lib/l-head.php');

function table_header()
{
    echo "<br clear=\"all\">\n";
    echo "<table border=\"2\" align=\"left\" cellspacing=\"2\" cellpadding=\"2\">\n";
}

function wide_table_header()
{
    echo <<< HERE

<br clear="all">
<table border="2" align="left" cellspacing="2" width="100%" cellpadding="2">

HERE;
}

function green($text)
{
    return "<font color=\"green\">$text</font>";
}

function bold($text)
{
    return "<b>$text</b>";
}

function table_footer()
{
    echo "</table>\n";
    echo "<br clear=\"all\">\n";
}

function find_scalar($sql, $db)
{
    $val = '';
    $res = redcommand($sql, $db);
    if ($res) {
        $val = mysqli_result($res, 0);
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }
    return $val;
}

function table_data($args, $head)
{
    $td = ($head) ? 'th' : 'td';
    if (safe_count($args)) {
        echo "<tr valign=\"top\">\n";
        reset($args);
        foreach ($args as $key => $data) {
            echo "<$td>$data</$td>\n";
        }
        echo "</tr>\n";
    }
}


function subheader($txt)
{
    $msg = ucwords($txt);
    echo "\n\n<h4>$msg</h4>\n\n";
}


function row($a, $b)
{
    $args = array($a, $b);
    table_data($args, 0);
}

function unknown_option($action, $db)
{
    debug_note("unknown_option($action,db)");
}


// keep in sync with order_file

function file_order($ord)
{
    $order = 'username, name, created, id';
    switch ($ord) {
        case  0:
            $order = 'created desc, id';
            break;
        case  1:
            $order = 'created, id';
            break;
        case  2:
            $order = 'name, username, created, id';
            break;
        case  3:
            $order = 'name desc, username, created, id';
            break;
        case  4:
            $order = 'username, name, created, id';
            break;
        case  5:
            $order = 'username desc, name, created, id';
            break;
        case  6:
            $order = 'id';
            break;
        case  7:
            $order = 'id desc';
            break;
        case  8:
            $order = 'expires desc, id';
            break;
        case  9:
            $order = 'expires, id';
            break;
        case 10:
            $order = 'counted desc, name, username, id';
            break;
        case 11:
            $order = 'counted, name, username, id';
            break;
        default:
            break;
    }

    return $order;
}

// keep in sync with file_order

function order_file(&$env)
{
    $ord = array(
        0 => 'time, newest to oldest',
        1 => 'time, oldest to newest',
        2 => 'name (increasing), owner, time',
        3 => 'name (decreasing), owner, time',
        4 => 'owner (increasing), name',
        5 => 'owner (decresing), name',
        6 => 'id (increasing)',
        7 => 'id (decreasing)',
        8 => 'expires, first to last',
        9 => 'expires, last to first',
        10 => 'events, most to least',
        11 => 'events, least to most'
    );
    if (!$env['admin']) {
        unset($ord[4]);
        unset($ord[5]);
    }
    if (!$env['debug']) {
        unset($ord[6]);
        unset($ord[7]);
    }
    return $ord;
}

function plural($count, $name)
{
    $text = "$count $name";
    if ($count != 1) {
        $text .= 's';
    }
    return $text;
}

function small_date($when)
{
    $text = 'Never';
    if ($when > 0) {
        $date = date('m/d/y', $when);
        $time = date('H:i:s', $when);
        $text = "$date<br>$time";
    }
    return $text;
}

function line_date($when)
{
    return ($when > 0) ? date('m/d/y H:i:s', $when) : 'Never';
}


function file_type($code)
{
    $type = '';
    switch ($code) {
        case 1:
            $type = 'Event Report';
            break;
        case 2:
            $type = 'Asset Report';
            break;
        case 3:
            $type = 'Change Report';
            break;
        case 4:
            $type = 'Meter Report';
            break;
        default:
            break;
    }
    return $type;
}


function start_list()
{
    return "<ul>\n";
}

function end_list()
{
    return "</ul>\n";
}


function create_temp_table($name, $db)
{
    $sql  = "create temporary table $name\n";
    $sql .= " (id int(11) not null primary key)";
    redcommand($sql, $db);
}

function drop_temp_table($name, $db)
{
    $sql = "drop table $name";
    redcommand($sql, $db);
}



function debug_temp($name, $db)
{
    $lst = array();
    $sql = "select id from $name order by id";
    $tmp = find_many($sql, $db);
    reset($tmp);
    foreach ($tmp as $key => $row) {
        $lst[] = $row['id'];
    }
    $num = safe_count($lst);
    $txt = implode(',', $lst);
    debug_note("$name: $num ($txt)");
}


/*
    |   Creates a list of files that the user
    |   is not allowed to see ... note this
    |   might be hundreds ...
    */

function create_unauth_table(&$env, $db)
{
    $user  = $env['auth'];
    $debug = $env['debug'];
    create_temp_table('unauth', $db);
    $qu  = safe_addslashes($user);
    $sql = "insert ignore into unauth\n"
        . " select distinct FileSites.fid from FileSites\n"
        . " left join Customers on\n"
        . " (HEX(FileSites.sitename) = HEX(Customers.customer)) and\n"
        . " (Customers.username = '$qu')\n"
        . " where (Customers.username is null)";
    $res = redcommand($sql, $db);
    if (($res) && ($debug)) {
        debug_temp('unauth', $db);
    }
}


function create_auth_table(&$env, $db)
{
    $debug = $env['debug'];

    create_temp_table('auth', $db);
    $sql = "insert ignore into auth\n"
        . " select distinct FileSites.fid from FileSites\n"
        . " left join unauth on\n"
        . " (FileSites.fid = unauth.id)\n"
        . " where (unauth.id is null)";
    $res = redcommand($sql, $db);
    if (($res) && ($debug)) {
        debug_temp('auth', $db);
    }
}

function create_filter_table(&$env, $db)
{
    $user  = $env['auth'];
    $debug = $env['debug'];
    $qu    = safe_addslashes($user);

    create_temp_table('included', $db);
    create_temp_table('excluded', $db);
    $sql  = "insert into excluded\n";
    $sql .= " select distinct FileSites.fid from FileSites\n";
    $sql .= " left join Customers on\n";
    $sql .= " (FileSites.sitename = Customers.customer) and\n";
    $sql .= " (Customers.username = '$qu') and\n";
    $sql .= " (Customers.sitefilter = 0)\n";
    $sql .= " where (Customers.username is not null)";
    $res  = redcommand($sql, $db);
    if (($res) && ($debug)) {
        debug_temp('excluded', $db);
    }
    $sql  = "insert into included\n";
    $sql .= " select FileSites.fid from FileSites\n";
    $sql .= " left join excluded on\n";
    $sql .= " (FileSites.fid = excluded.id)\n";
    $sql .= " where excluded.id is null";
    $res  = redcommand($sql, $db);
    if (($res) && ($debug)) {
        debug_temp('included', $db);
    }
}


/*
    |  See mysql manual 6.4.1.1 on join syntax,
    |  which as of 9/2003 is here:
    |
    |  http://www.mysql.com/doc/en/JOIN.html
    |
    */

function find_files_by_site(&$env, $db)
{
    $ord    = $env['ord'];
    $user   = $env['auth'];
    $code   = $env['code'];
    $limt   = $env['limt'];
    $rusr   = $env['rusr'];
    $tmin   = $env['tmin'];
    $tmax   = $env['tmax'];
    $pmin   = $env['pmin'];
    $debug  = $env['debug'];
    $admin  = $env['admin'];
    $filter = $env['filter'];

    $order  = file_order($ord);
    $type   = file_type($code);
    $qusr   = (($admin) && ($rusr)) ? safe_addslashes($rusr) : '';
    $list   = array();
    $sql    = "select Files.* from Files,";

    if ($filter) {
        $sql .= " auth, included where\n";
        $sql .= " (auth.id = included.id) and\n";
    } else {
        $sql .= " auth where\n";
    }

    $sql .= " (Files.id = auth.id)";
    $sql .= " and\n (Files.type = '$type')";
    if ($qusr) $sql .= " and\n (Files.username = '$qusr')";
    if ($tmin) $sql .= " and\n ($tmin <= Files.created)";
    if ($tmax) $sql .= " and\n (Files.created <= $tmax)";
    $sql .= "\n order by $order\n limit $pmin, $limt";
    $list = find_many($sql, $db);
    $tmp = safe_count($list);
    debug_note("$tmp files found");
    return $list;
}


function find_files_simple(&$env, $db)
{
    $ord    = $env['ord'];
    $code   = $env['code'];
    $rusr   = $env['rusr'];
    $limt   = $env['limt'];
    $tmin   = $env['tmin'];
    $tmax   = $env['tmax'];
    $pmin   = $env['pmin'];

    $order = file_order($ord);
    $type  = file_type($code);

    $qusr  = ($rusr) ? safe_addslashes($rusr) : '';
    $sql   = "select * from Files\n";
    $sql  .= " where type = '$type'";
    if ($qusr) $sql .= " and\n username = '$qusr'";
    if ($tmin) $sql .= " and\n $tmin <= created";
    if ($tmax) $sql .= " and\n created <= $tmax";
    $sql .= "\n order by $order\n limit $pmin, $limt";
    return find_many($sql, $db);
}


function count_files_simple(&$env, $code, $narrow, $db)
{
    $user   = $env['auth'];
    $rusr   = $env['rusr'];
    $tmin   = $env['tmin'];
    $tmax   = $env['tmax'];

    $type   = file_type($code);
    $qusr   = '';

    $sql  = "select count(*) from Files where\n";
    if (($rusr) && ($narrow)) {
        $qusr = safe_addslashes($rusr);
    }

    $sql .= " (Files.type = '$type')";
    if ($narrow) {
        if ($qusr) $sql .= " and\n (Files.username = '$qusr')";
        if ($tmin) $sql .= " and\n (Files.created > $tmin)";
        if ($tmax) $sql .= " and\n (Files.created < $tmax)";
    }
    $count = find_scalar($sql, $db);
    debug_note("count files: $count");
    return $count;
}


function create_temp_tables(&$env, $db)
{
    $filter = $env['filter'];
    create_unauth_table($env, $db);
    create_auth_table($env, $db);
    if ($filter) {
        create_filter_table($env, $db);
    }
}


function drop_temp_tables(&$env, $db)
{
    $filter = $env['filter'];
    if ($filter) {
        drop_temp_table('included', $db);
        drop_temp_table('excluded', $db);
    }
    drop_temp_table('auth', $db);
    drop_temp_table('unauth', $db);
}


function count_files_by_site(&$env, $code, $narrow, $db)
{
    $user   = $env['auth'];
    $rusr   = $env['rusr'];
    $tmin   = $env['tmin'];
    $tmax   = $env['tmax'];
    $filter = $env['filter'];
    $admin  = $env['admin'];

    $type = file_type($code);
    $qusr = (($rusr) && ($admin)) ? safe_addslashes($rusr) : '';
    $sql  = "select count(Files.username)";
    if ($filter) {
        $sql .= " from Files, auth, included where\n";
        $sql .= " (auth.id = included.id) and\n";
        $sql .= " (Files.id = auth.id) and\n";
    } else {
        $sql .= " from Files, auth where\n";
        $sql .= " (Files.id = auth.id) and\n";
    }

    $sql .= " (Files.type = '$type')";
    if ($narrow) {
        if ($qusr) $sql .= " and\n (Files.username = '$qusr')";
        if ($tmin) $sql .= " and\n (Files.created > $tmin)";
        if ($tmax) $sql .= " and\n (Files.created < $tmax)";
    }
    $count = find_scalar($sql, $db);
    debug_note("count files: $count");
    return $count;
}


function show_link($env, $count, $code)
{
    if ($count > 0) {
        $self = $env['self'];
        $href = "$self?c=$code";
        $type = file_type($code);
        $type = strtolower($type);
        $type = ucfirst($type);
        $text = plural($count, $type);
        $link = html_link($href, bold($text));
        echo "<li>$link</li>\n";
    }
}


function jumparound(&$env, $tags)
{
    $code = $env['code'];
    $self = $env['self'];
    $dbg  = $env['priv_debug'];
    $act  = $env['action'];
    $a    = array();

    $a[] = html_link($self, 'index');
    jumptags($a, $tags);
    if ($code > 0) {
        if (($act == 'confirm') || ($act == 'edit') || ($act == 'delete')) {
            $lnk = "$self?c=$code";
            $a[] = html_link($lnk, 'back');
        }
    }
    if ($dbg) {
        $args = $env['args'];
        $href = ($args) ? "$self?$args" : $self;
        $ac   = "$self?action";
        $ps   = "$ac=psize&size";
        $a[]  = html_link('index.php', 'home');
        $a[]  = html_link("$ac=debug", 'debug');
        $a[]  = html_link("$ac=pzero", 'zero');
        $a[]  = html_link("$ps=4096", '4K');
        $a[]  = html_link("$ps=8192", '8K');
        $a[]  = html_link($href, 'again');
    }
    return jumplist($a);
}


function menu_files(&$env, $db)
{
    echo jumparound($env, 'top,bottom');
    $simple = $env['simple'];
    if ($simple) {
        $rcount = count_files_simple($env, 1, 0, $db);
        $acount = count_files_simple($env, 2, 0, $db);
        $ccount = count_files_simple($env, 3, 0, $db);
        $mcount = count_files_simple($env, 4, 0, $db);
    } else {
        create_temp_tables($env, $db);
        $rcount = count_files_by_site($env, 1, 0, $db);
        $acount = count_files_by_site($env, 2, 0, $db);
        $ccount = count_files_by_site($env, 3, 0, $db);
        $mcount = count_files_by_site($env, 4, 0, $db);
        drop_temp_tables($env, $db);
    }
    $total  = $rcount + $acount + $ccount + $mcount;
    if ($total > 0) {
        $text = plural($total, 'report');
        $text = ucwords("$text published.");
        echo "<h4>$text</h4>\n";

        echo start_list();
        show_link($env, $rcount, 1);
        show_link($env, $acount, 2);
        show_link($env, $ccount, 3);
        show_link($env, $mcount, 4);
        echo end_list();
    } else {
        $msg  = "Sorry, you don't have any reports yet.<br>\n";
        $msg .= "Check back later.<br>\n";
        echo $msg;
    }
    echo jumparound($env, 'top,bottom');
}


function userlist($db)
{
    $list = array(' ');
    $sql  = "select username from Users\n";
    $sql .= " order by username";
    $tmp  = find_many($sql, $db);
    if ($tmp) {
        foreach ($tmp as $key => $row) {
            $list[] = $row['username'];
        }
    }
    return $list;
}


function control_files(&$env, $total, $num, $db)
{
    $now   = $env['now'];
    $ord   = $env['ord'];
    $code  = $env['code'];
    $self  = $env['self'];
    $rmax  = $env['rmax'];
    $rmin  = $env['rmin'];
    $midn  = $env['midn'];
    $limt  = $env['limt'];
    $pmin  = $env['pmin'];
    $admin  = $env['admin'];

    debug_note("control_files now:$now rmin:$rmin rmax:$rmax");
    $opt   = array();
    $time  = array();
    $popt  = array();
    $lopt  = array(5, 10, 20, 30, 40, 50, 60, 75, 100, 200, 400, 500);
    for ($i = 1; $i <= 4; $i++) {
        $opt[$i] = file_type($i);
    }

    if ($limt < 5) $limt = 5;

    $j = 0;
    $n = 1;
    while ($j < $total) {
        $min = $j + 1;
        $tmp = $j + $limt;
        $max = ($tmp > $total) ? $total : $tmp;
        $popt[$j] = "Page $n  ($min through $max)";
        $j += $limt;
        $n++;
    }

    $time[0] = '     ';
    $time[1] = 'Midnight';
    $time[2] = 'Yesterday';
    for ($i = 2; $i <= 13; $i++) {
        $when = days_ago($midn, $i);
        $time[$i] = date('m/d/y', $when);
    }
    for ($i = 2; $i <= 14; $i++) {
        $days = $i * 7;
        $when = days_ago($midn, $days);
        $time[$days] = date('m/d/y', $when);
    }
    $sopt   = order_file($env);
    $type   = html_select('c', $opt, $code, 1);
    $smax   = html_select('rmax', $time, $rmax, 1);
    $smin   = html_select('rmin', $time, $rmin, 1);
    $limit  = html_select('l', $lopt, $limt, 0);
    $sort   = html_select('o', $sopt, $ord, 1);

    $submit = '<input type="submit" value="submit">';
    $reset  = '<input type="reset" value="reset">';

    echo mark('control');
    echo jumparound($env, 'top,bottom,control,reports');
    echo <<< HERE

<form method="post" action="$self">
<input type="hidden" name="action" value="display">

HERE;
    table_header();
    row('Type', $type);
    if ($admin) {
        $rusr  = $env['rusr'];
        if ($rusr == '') $rusr = ' ';
        $users = userlist($db);
        $owner = html_select('rusr', $users, $rusr, 0);
        row('Owner', $owner);
    }
    //    row('Total',$total);
    //    row('Displayed',$num);
    row('Earliest publication date', $smin);
    row('Latest publication date', $smax);
    row('Sort', $sort);
    row('Page size', $limit);
    if ($total > $limt) {
        $next   = html_select('p', $popt, $pmin, 1);
        row('Page', $next);
    }
    row($submit, $reset);
    if ($env['priv_debug']) {
        $filter  = $env['filter'];
        $debug   = $env['debug'];
        $rest    = $env['rest'];
        $opt     = array('No', 'Yes');
        $dbg_select = html_select('debug',    $opt, $debug, 1);
        $adm_select = html_select('admin',    $opt, $admin, 1);
        $rst_select = html_select('restrict', $opt, $rest,  1);
        $flt_select = html_select('filter',   $opt, $filter, 1);

        row(green('$debug'), $dbg_select);
        row(green('$admin'), $adm_select);
        row(green('$rest'),  $rst_select);
        row(green('$filter'), $flt_select);
    }
    table_footer();
}


function prevnext(&$env, $total, $num)
{
    if ($num < $total) {
        $ord  = $env['ord'];
        $self = $env['self'];
        $pmin = $env['pmin'];
        $limt = $env['limt'];
        $code = $env['code'];
        $rmin = $env['rmin'];
        $rmax = $env['rmax'];

        $img  = '<img border="0"';
        $pwid = 'width="68" height="22">';
        $nwid = 'width="47" height="22">';

        if ($pmin <= 0) {
            $psrc = ' src="../pub/previous-gray.gif" ';
            $pimg = $img . $psrc . $pwid;
            $ptxt = $pimg;
        } else {
            $tmp  = $pmin - $limt;
            $prev = ($tmp <= 0) ? 0 : $tmp;
            $psrc = ' src="../pub/previous.gif" ';
            $pimg = $img . $psrc . $pwid;
            $a    = array("$self?c=$code");
            $a[]  = "p=$prev";
            $a[]  = "o=$ord";
            $a[]  = "l=$limt";
            if ($rmin) $a[]  = "rmin=$rmin";
            if ($rmin) $a[]  = "rmax=$rmax";
            $pref = join('&', $a);

            $ptxt = html_link($pref, $pimg);
        }


        $next = $pmin + $limt;
        if ($next < $total) {
            $nsrc = ' src="../pub/next.gif" ';
            $nimg = $img . $nsrc . $nwid;
            $a    = array("$self?c=$code");
            $a[]  = "p=$next";
            $a[]  = "o=$ord";
            $a[]  = "l=$limt";
            if ($rmin) $a[]  = "rmin=$rmin";
            if ($rmax) $a[]  = "rmax=$rmax";
            $nref = join('&', $a);
            $ntxt = html_link($nref, $nimg);
        } else {
            $nsrc = ' src="../pub/next-gray.gif" ';
            $nimg = $img . $nsrc . $nwid;
            $ntxt = $nimg;
        }

        echo <<< HERE

<br clear="all">
<table width="100%">
<tr>
  <td align="left" valign="top">
    $ptxt
  </td>

  <td>
    <br>
  </td>

  <td align="right" valign="top">
    $ntxt
  </td>
</tr>
</table>

HERE;
    }
}



/*
    |  This has three kinds of access methods.
    |
    |  1. An admin user with site filtering disabled
    |     can examine any file.
    |  2. A normal user with site filtering disabled
    |     can see only those files which refer only
    |     to sites he is allowed to access.
    |  3. A user with site filtering enabled can
    |     see only files that refer only to his
    |     enabled sites.
    */

function list_files(&$env, $type, $db)
{
    $ord    = $env['ord'];
    $now    = $env['now'];
    $self   = $env['self'];
    $auth   = $env['auth'];
    $code   = $env['code'];
    $rest   = $env['rest'];
    $rusr   = $env['rusr'];
    $limt   = $env['limt'];
    $tmin   = $env['tmin'];
    $tmax   = $env['tmax'];
    $pmin   = $env['pmin'];
    $admin  = $env['admin'];
    $debug  = $env['debug'];
    $filter = $env['filter'];
    $simple = $env['simple'];

    $list   = array();
    $num    = 0;
    $total  = 0;

    if ($simple)
        $total = count_files_simple($env, $code, 1, $db);
    else {
        create_temp_tables($env, $db);
        $total = count_files_by_site($env, $code, 1, $db);
    }

    if (($pmin != 0) && (($total < $pmin) || ($total < $limt))) {
        debug_note("reset to first page, pmin:$pmin total:$total limit:$limt");
        $env['pmin'] = 0;
        $pmin = 0;
    }

    if ($simple) {
        if ($total > 0) {
            $list = find_files_simple($env, $db);
        }
    } else {
        if ($total > 0) {
            $list = find_files_by_site($env, $db);
        }
        drop_temp_tables($env, $db);
    }

    $num = safe_count($list);
    control_files($env, $total, $num, $db);

    debug_note("$num files loaded");


    if ($num > 0) {
        $msg  = plural($total, $type);
        $msg  = strtolower($msg);
        subheader("$msg published, $num displayed.");
        echo mark('reports');
        echo jumparound($env, 'top,bottom,control,reports');

        $min = $pmin + 1;
        $max = $pmin + $num;
        echo "Reports $min through $max (of $total)<br>\n\n";

        $act  = "$self?c=$code&p=$pmin&l=$limt&o";
        $wref = ($ord ==  0) ? "$act=1"  : "$act=0";
        $sref = ($ord ==  4) ? "$act=5"  : "$act=4";
        $href = ($ord ==  2) ? "$act=3"  : "$act=2";
        $iref = ($ord ==  6) ? "$act=7"  : "$act=6";
        $xref = ($ord ==  8) ? "$act=9"  : "$act=8";
        $cref = ($ord == 10) ? "$act=11" : "$act=10";

        $id   = html_link($iref, 'Id');
        $name = html_link($href, 'Name');
        $when = html_link($wref, 'Publication Date');
        $user = html_link($sref, 'Owner');
        $exp  = html_link($xref, 'Expires');
        $evnt = html_link($cref, 'Count');
        $size = 'Size (Bytes)';
        $note = 'Notes';
        $act  = 'Action';

        if ($rest)
            $head = array($name, $when, $evnt, $size, $note, $act);
        else if (($debug) && ($admin))
            $head = array($name, $user, $when, $exp, $evnt, $id, $size, $note, $act);
        else if ($admin)
            $head = array($name, $user, $when, $exp, $evnt, $size, $note, $act);
        else
            $head = array($name, $when, $exp, $evnt, $size, $note, $act);

        clearstatcache();

        prevnext($env, $total, $num);
        wide_table_header();
        table_data($head, 1);
        reset($list);
        foreach ($list as $key => $row) {
            $a    = array();
            $id   = $row['id'];
            $name = $row['name'];
            $path = $row['path'];
            $link = $row['link'];
            $type = $row['type'];
            $note = $row['note'];
            $evnt = $row['counted'];
            $last = $row['expires'];
            $when = $row['created'];
            $user = $row['username'];
            $acct = "$self?i=$id&c=$code&action";
            $edit = html_link("$acct=edit", '[edit]');
            $del  = html_link("$acct=confirm", '[delete]');
            $when = small_date($when);
            $last = small_date($last);
            if (file_exists($path)) {
                $size = filesize($path);
                $view = html_page($link, '[view]');
                $a[] = $view;
                $a[] = $edit;
            } else {
                $size = '<i>missing</i>';
            }

            $text = $name;
            $note = ($note == '') ? '<br>' : nl2br($note);
            if (!$rest) {
                if (($admin) || ($user == $auth)) {
                    $a[] = $del;
                }
            }

            $act = ($a) ? join("<br>\n", $a) : '<br>';
            if ($rest)
                $args = array($text, $when, $evnt, $size, $note, $act);
            else {
                $args = array($text);
                if ($admin) $args[] = $user;
                $args[] = $when;
                $args[] = $last;
                $args[] = $evnt;
                if (($debug) && ($admin)) {
                    $args[] = $id;
                }
                $args[] = $size;
                $args[] = $note;
                $args[] = $act;
            }
            table_data($args, 0);
        }
        table_footer();
        prevnext($env, $total, $num);
        echo jumparound($env, 'top,bottom,control,reports');
    } else {
        echo jumparound($env, 'top,bottom');
        $msg = "No reports published.";
        $msg = fontspeak($msg);
        echo "<p>$msg</p><br>\n";
        echo jumparound($env, 'top,bottom');
    }
}


function display_files(&$env, $db)
{
    $type = file_type($env['code']);
    if ($type) {
        list_files($env, $type, $db);
    } else {
        menu_files($env, $db);
    }
}


function find_one_file(&$env, $db)
{
    $fid   = $env['fid'];
    $admin = $env['admin'];
    $row   = array();

    debug_note("find_one_file: fid:$fid, admin:$admin");

    if ($fid > 0) {
        if ($admin) {
            $good = true;
        } else {
            $carr = $env['carr'];
            $site = file_site_list($fid, $db);
            $good = subset($site, $carr);
            if (!$good) {
                $ccc = db_access($carr);
                $sss = db_access($site);
                debug_note("site: $ccc");
                debug_note("carr: $sss");
            }
        }
        if ($good) {
            $sql  = "select * from Files\n";
            $sql .= " where id = $fid";
            $row = find_one($sql, $db);
        } else {
            $user = $env['auth'];
            debug_note("user $user has no access to file $fid");
        }
    }
    return $row;
}


function backtolist(&$env)
{
    $self = $env['self'];
    $code = $env['code'];
    $href = "$self?c=$code";
    $back = html_link($href, 'back');
    $text = "Go $back to report page.<br>\n\n";
    return $text;
}


function delete_file(&$env, $db)
{
    $fid   = $env['fid'];
    $row   = $env['row'];
    $self  = $env['self'];
    $code  = $env['code'];
    $user  = $env['auth'];
    $admin = $env['admin'];

    debug_note("delete_file: fid:$fid, code:$code, user:$user admin:$admin");

    if ($row) {
        $name = $row['name'];
        $good = delete_file_record($row, $admin, $user, $db);
        if ($good) {
            $msg  = "Report <b>$name</b> has been removed.<br><br>\n\n";
        } else {
            $msg = "Report <b>$name</b> was not removed.<br><br>\n\n";
        }
    } else {
        $msg = "No file specified ...\n\n<br>";
    }
    $msg .= backtolist($env);
    $msg  = fontspeak($msg);
    echo "$msg<br>\n";
}


function select_expires($now, $expires)
{
    $days = array(2, 3, 4, 5, 6, 7, 14, 30, 60, 90, 120, 150, 180, 210, 240, 270, 300, 330, 360);
    $opt = array();
    $tomorrow = $now + 86400;
    if ($expires > 0) {
        $opt[$expires] = line_date($expires);
    }
    $opt[$now] = 'Today';
    $opt[$tomorrow] = 'Tomorrow';
    reset($days);
    foreach ($days as $key => $day) {
        $when = $now + ($day * 86400);
        $text = date('m/d/y', $when) . " -- $day days";
        $opt[$when] = $text;
    }
    $opt[0] = 'Never';
    return html_select('umax', $opt, $expires, 1);
}



function file_page(&$env, $row, $db)
{
    echo jumparound($env, 'top,bottom');

    $fid   = $env['fid'];
    $now   = $env['now'];
    $self  = $env['self'];
    $code  = $env['code'];
    $rest  = $env['rest'];
    $auth  = $env['auth'];
    $debug = $env['debug'];
    $admin = $env['admin'];

    $note   = $row['note'];
    $path   = $row['path'];
    $link   = $row['link'];
    $name   = $row['name'];
    $exp    = $row['expires'];
    $create = $row['created'];
    $user   = $row['username'];

    $size   = '<i>missing</i>';
    $view   = '';
    if (file_exists($path)) {
        $temp = filesize($path);
        if (($link) && ($temp)) {
            $size = $temp;
            $view = html_link($link, '[view]');
        }
    }


    echo <<< HERE

<form method="post" action="$self">
<input type="hidden" name="i" value="$fid">
<input type="hidden" name="c" value="$code">
<input type="hidden" name="action" value="update">

HERE;
    if ($rest)
        echo "<input type=\"hidden\" name=\"umax\" value=\"$exp\">\n";

    $dexp = line_date($exp);
    $date = line_date($create);
    $text   = '<input type="text" name="date">';
    $submit = '<input type="submit" value="submit">';
    $reset  = '<input type="reset" value="reset">';
    $thead  = '<textarea wrap="virtual" rows="5" cols="60" name="note">';
    $ttail  = '</textarea>';
    $note   = $thead . $note . $ttail;
    $sel    = select_expires($now, $exp);

    table_header();
    if ($debug) row('Id', $fid);
    row('Name', $name);
    row('Owner', $user);
    row('Size (bytes)', $size);
    row('Published', $date);

    /*
        |  Restricted users are not allowed to modify file
        |  expiration dates.
        |
        |  Normal users can modify the expiration date
        |  of files which they own.
        |
        |  Admin users can modify the expiration date
        |  of any file.
        */

    $mod = 0;
    if (!$rest) {
        if (($admin) || ($user == $auth)) {
            $mod = 1;
        }
    }

    $ed = 'expiration date';
    if ($mod) {
        row("Select $ed", $sel);
        row("Enter $ed", $text);
    } else {
        $msg = ucfirst($ed);
        row($msg, $dexp);
    }
    if ($view) row('View', $view);
    row('Note', $note);
    row($submit, $reset);
    if ($env['priv_debug']) {
        $debug   = $env['debug'];
        $rest    = $env['rest'];
        $opt     = array('No', 'Yes');
        $dbg_select = html_select('debug',    $opt, $debug, 1);
        $adm_select = html_select('admin',    $opt, $admin, 1);
        $rst_select = html_select('restrict', $opt, $rest,  1);

        row(green('$debug'), $dbg_select);
        row(green('$admin'), $adm_select);
        row(green('$rest'),  $rst_select);
    }

    table_footer();
    echo "\n</form>\n<br>\n";
    echo jumparound($env, 'top,bottom');
}


function edit_file(&$env, $db)
{
    $fid   = $env['fid'];
    $row   = $env['row'];
    $user  = $env['auth'];
    $admin = $env['admin'];
    $msg   = '';

    debug_note("edit_file: fid:$fid, admin:$admin");

    if ($row) {
        file_page($env, $row, $db);
    } else {
        $msg = "No file specified ...\n\n<br>";
    }
    if ($msg) {
        $msg  = fontspeak($msg);
        echo "$msg<br>\n";
    }
}




function update_file(&$env, $db)
{
    $fid   = $env['fid'];
    $now   = $env['now'];
    $row   = $env['row'];
    $user  = $env['auth'];
    $date  = $env['date'];
    $umax  = $env['umax'];
    $code  = $env['code'];
    $self  = $env['self'];
    $rest  = $env['rest'];
    $enote = $env['note'];
    $admin = $env['admin'];
    $good  = false;
    $msg   = '';

    debug_note("update_file: user:$user fid:$fid, admin:$admin");

    if ($row) {
        $note = $row['note'];
        $name = $row['name'];
        $exp  = $row['expires'];
        $user = $row['username'];
        $tmp  = 0;
        if ($date) {
            if (strcmp($date, 'never')) {
                $tmp = parsedate($date, $now);
                if ($tmp > 0) {
                    $exp = $tmp;
                    $good = true;
                }
            } else {
                $exp = 0;
                $good = true;
            }
        }
        if (($tmp == 0) && (0 <= $umax)) {
            $exp = $umax;
            $good = true;
        }
        if ($note != $enote) {
            $good = true;
        }

        $mod = 0;
        if (($good) && (!$rest)) {
            if (($admin) || ($auth == $user)) {
                $mod = 1;
            }
        }

        if ($good) {
            $qn   = safe_addslashes($enote);
            $sql  = "update Files set\n";
            if ($mod) $sql .= " expires=$exp,\n";
            $sql .= " note='$qn'\n";
            $sql .= " where id = $fid";
            $res  = redcommand($sql, $db);
            if ($res) {
                $msg = "Entry for <b>$name</b> has been updated.<br>\n";
                if (($mod) && ($row['expires'] != $exp)) {
                    $exp  = line_date($exp);
                    $msg .= "Expiration date: $exp<br>\n";
                }
            } else {
                $msg = "Update of <b>$name</b> failed.";
            }
        } else {
            $msg = "No changes noticed.";
        }
    } else {
        $msg = "No report specified ...\n\n<br>";
    }
    $txt  = backtolist($env);
    $msg .= "<br><br>\n\n$txt";
    if ($msg) {
        $msg  = fontspeak($msg);
        echo "$msg<br>\n";
    }
}



function confirm_file(&$env, $db)
{
    $fid   = $env['fid'];
    $row   = $env['row'];
    $user  = $env['auth'];
    $self  = $env['self'];
    $code  = $env['code'];
    $admin = $env['admin'];

    debug_note("confirm_file: admin:$admin, fid:$fid");

    if ($row) {
        $name = $row['name'];
        $act  = "$self?i=$fid&c=$code&action";
        $yes  = html_link("$act=delete", 'Yes');
        $no   = html_link("$act=display", 'No');

        $msg  = "Are you sure you want to delete report <b>$name</b>?<br>\n";
        $msg .= "<br><br>\n\n";
        $msg .= "&nbsp;&nbsp;&nbsp;$yes&nbsp;&nbsp;&nbsp;$no<br>\n";
        $msg .= "<br><br>\n\n";
    } else {
        $msg = "Report $fid has vanished.";
    }
    $msg  = fontspeak($msg);
    echo "$msg<br>\n";
}


function code_title(&$title, $code)
{
    switch ($code) {
        case 0:
            $title .= ' - Index';
            break;
        case 1:
            $title .= ' - Event Reports';
            break;
        case 2:
            $title .= ' - Asset Reports';
            break;
        case 3:
            $title .= ' - Asset Change Reports';
            break;
        case 4:
            $title .= ' - Metering Reports';
            break;
        default:
            break;
    }
}



function action_title(&$title, $action, $code)
{
    switch ($action) {
        case 'display':
            code_title($title, $code);
            break;
        case 'confirm':
            $title .= ' - Confirm Report Delete';
            break;
        case 'delete':
            $title .= ' - Deleting Report';
            break;
        case 'edit':
            $title .= ' - Edit Report';
            break;
        case 'update':
            $title .= ' - Updating Report';
            break;
        case 'debug':
            $title = 'Debug -- List All';
            break;
        default:
            break;
    }
}


function usersite($db)
{
    $users = array();
    $sql   = "select * from Customers";
    $cust  = find_many($sql, $db);
    if ($cust) {
        reset($cust);
        foreach ($cust as $key => $data) {
            $user = $data['username'];
            $site = $data['customer'];
            if (($user) && ($site)) {
                $users[$user][$site] = true;
            }
        }
    }
    return $users;
}


/*
    |  This should only need to happen once for any server.
    |  It associates files with sites based on which sites
    |  the owner of the file is allowed to access.
    |
    |  So, it allows us to start off with a reasonable
    |  default set of associations.
    |
    |  If you run it twice, it allows us to recalcuate
    |  them all ... good for debugging, but not needed
    |  in a production server.
    */

function create_sites($env, $db)
{
    $admin = $env['admin'];
    if ($admin) {
        $sql   = "select * from Files";
        $files = find_many($sql, $db);
        $users = usersite($db);
        $list  = array();
        if (($users) && ($files)) {
            $sql = "delete from FileSites";
            $res = redcommand($sql, $db);

            reset($files);
            foreach ($files as $key => $row) {
                $fid  = $row['id'];
                $user = $row['username'];
                if (isset($users[$user])) {
                    $sites = $users[$user];
                    foreach ($sites as $site => $data) {
                        create_filesite($fid, $site, $db);
                    }
                }
            }
        }
    }
}




function purge_size($env, $db)
{
    $admin = $env['admin'];
    $user  = $env['auth'];
    $msize = $env['size'];
    $files = array();
    $list  = array();
    if ($admin) {
        $sql   = "select * from Files";
        $files = find_many($sql, $db);
    }
    if ($files) {
        reset($files);
        foreach ($files as $key => $row) {
            $path  = $row['path'];
            $size  = 0;
            $exist = false;
            if (file_exists($path)) {
                $exist = true;
                $size  = filesize($path);
            }
            if ($size < $msize) {
                $good = delete_file_record($row, $admin, $user, $db);
                $tmp  = array();
                $tmp['name'] = $row['name'];
                $tmp['user'] = $row['username'];
                $tmp['size'] = $size;
                if ($good)
                    $tmp['act'] = 'removed successfully';
                else
                    $tmp['act'] = 'not removed';
                $list[] = $tmp;
            }
        }
    }
    if ($list) {
        table_header();

        reset($list);
        foreach ($list as $key => $row) {
            $user = $row['user'];
            $name = $row['name'];
            $size = $row['size'];
            $act  = $row['act'];
            $args = array($user, $name, $size, $act);
            table_data($args, 0);
        }
        table_footer();
    }
}

function purge_zero($env, $db)
{
    echo jumparound($env, 'top,bottom');
    $admin = $env['admin'];
    $user  = $env['auth'];
    $files = array();
    $list  = array();
    if ($admin) {
        $sql   = "select * from Files\n"
            . " where counted = 0";
        $files = find_many($sql, $db);
    }
    if ($files) {
        reset($files);
        foreach ($files as $key => $row) {
            $good = false;
            $good = delete_file_record($row, $admin, $user, $db);
            $fate = ($good) ? 'removed successfully' : 'not removed';
            $tmp  = array();
            $tmp['name'] = $row['name'];
            $tmp['user'] = $row['username'];
            $tmp['type'] = $row['type'];
            $tmp['act']  = $fate;
            $list[] = $tmp;
        }
    }
    if ($list) {
        $head = explode('|', 'owner|name|type|action');
        table_header();
        table_data($head, 1);

        reset($list);
        foreach ($list as $key => $row) {
            $user = $row['user'];
            $name = $row['name'];
            $type = $row['type'];
            $act  = $row['act'];
            $args = array($user, $name, $type, $act);
            table_data($args, 0);
        }
        table_footer();
    }
    echo jumparound($env, 'top,bottom');
}

function debug_files($env, $db)
{
    $priv = $env['priv_debug'];
    $user = $env['auth'];
    echo jumparound($env, 'top,bottom');
    if ($priv) {
        $qu   = safe_addslashes($user);
        $sql  = "select * from Customers\n";
        $sql .= " where username = '$qu'\n";
        $sql .= " order by customer";
        $list = find_many($sql, $db);
        if ($list) {
            $tmp = safe_count($list);
            $cnt = 0;
            table_header();
            $head = explode('|', 'id|user|site|filter');
            table_data($head, 1);
            reset($list);
            foreach ($list as $key => $row) {
                $id   = $row['id'];
                $name = $row['username'];
                $site = $row['customer'];
                $filt = $row['sitefilter'];
                if ($filt) $cnt++;
                $args = array($id, $name, $site, $filt);
                table_data($args, 0);
            }
            table_footer();
        }
        echo "\n\n\n<br>$user owns $tmp sites, $cnt of them enabled.<br>\n\n\n";

        echo jumparound($env, 'top,bottom');

        $sql  = "select * from Files order by id";
        $list = find_many($sql, $db);
        if ($list) {
            $sites = array();
            $sql  = "select * from FileSites\n";
            $sql .= " order by fid, sitename";
            $tmp  = find_many($sql, $db);

            reset($tmp);
            foreach ($tmp as $key => $row) {
                $fid  = $row['fid'];
                $sites[$fid][] = $row['sitename'];
            }
            table_header();
            $head = explode('|', 'id|name|events|sites|list|type|user|created|expires');
            table_data($head, 1);
            reset($list);
            foreach ($list as $key => $row) {
                $id   = $row['id'];
                $name = $row['name'];
                $user = $row['username'];
                $type = $row['type'];
                $num  = $row['counted'];
                $exp  = small_date($row['expires']);
                $crt  = small_date($row['created']);

                if (isset($sites[$id])) {
                    $txt = join('<br>', $sites[$id]);
                    $fsc = safe_count($sites[$id]);
                } else {
                    $txt = '<br>';
                    $fsc = 0;
                }

                $args = array($id, $name, $num, $fsc, $txt, $type, $user, $crt, $exp);
                table_data($args, 0);
            }
            table_footer();
            $num = safe_count($list);
            $aux = safe_count($tmp);
            echo "\n<br>$num files, $aux site records found.<br>\n\n";
        }
    } else {
        echo "missing debug priv ... <br><br>\n\n";
    }
    echo jumparound($env, 'top,bottom');
}




/*
    |  Main program
    */

$now      = time();
$db       = db_connect();
$authuser = restrict_login($db);
$comp     = component_installed();

$user   = user_data($authuser, $db);
$carr   = site_array($authuser, 0, $db);

$priv_debug  = @($user['priv_debug']) ?    1 : 0;
$priv_admin  = @($user['priv_admin']) ?    1 : 0;
$priv_rest   = @($user['priv_restrict']) ? 1 : 0;
$filtersites = @($user['filtersites']) ?   1 : 0;

if ($priv_admin) $priv_rest = 0;

$adm = get_integer('admin', 1);
$dbg = get_integer('debug', 0);
$rst = get_integer('restrict', 0);
$flt = get_integer('filter', -1);

// debug users are allowed to set certain options
// on the command line, for debug purposes.

$flt    = (0 <= $flt) ?   $flt : $filtersites;
$rst    = ($priv_debug) ? $rst : 0;
$debug  = ($priv_debug) ? $dbg : 0;
$filter = ($priv_debug) ? $flt : $filtersites;
$admin  = ($priv_admin) ? $adm : 0;
$rest   = ($priv_rest) ?  1    : $rst;

// we can simulate admin or restricted
// for debugging purposes ... but not
// both at once.

if ($rest)  $admin = 0;
if ($admin) $rest  = 0;

$ord    = get_integer('o', 0);
$fid    = get_integer('i', 0);
$code   = get_integer('c', 0);
$pmin   = get_integer('p', 0);
$limt   = get_integer('l', 50);
$size   = get_integer('size', 4000);

$umax   = get_integer('umax', -1);
$rmin   = get_integer('rmin', 0);
$rmax   = get_integer('rmax', 0);
$midn   = midnight($now);

$action = get_string('action', 'display');
$date   = get_string('date', '');
$note   = get_string('note', '');
$rusr   = get_string('rusr', '');

if (strcmp(server_opt('server_name', $db), 'asi.gmstechrx.com') == 0) {
    $title = 'EMS Information Portal';
} else {
    $title = 'ASI Information Portal';
}
action_title($title, $action, $code);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
if ($rest) {
    echo restricted_html_header($title, $comp, $authuser, $db);
} else {
    echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);
}
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$env = array();
$env['priv_debug']  = $priv_debug;
$env['priv_admin']  = $priv_admin;
$env['priv_rest']   = $priv_rest;
$env['action'] = $action;
$env['filter'] = $filter;
$env['simple'] = (($admin) && (!$filter));
$env['admin']  = $admin;
$env['debug']  = $debug;
$env['rest']   = $rest;
$env['self']   = server_var('PHP_SELF');
$env['args']   = server_var('QUERY_STRING');
$env['auth']   = $authuser;
$env['user']   = $authuser;
$env['date']   = strtolower($date);
$env['note']   = $note;
$env['code']   = $code;
$env['carr']   = $carr;
$env['umax']   = $umax;
$env['rmax']   = $rmax;
$env['rmin']   = $rmin;
$env['rusr']   = $rusr;
$env['midn']   = $midn;
$env['limt']   = $limt;
$env['pmin']   = $pmin;
$env['size']   = $size;
$env['tmin']   = ($rmin) ? days_ago($midn, $rmin) : 0;
$env['tmax']   = ($rmax) ? days_ago($midn, $rmax) : 0;
$env['now']    = $now;
$env['fid']    = $fid;
$env['ord']    = $ord;
$env['row']    = find_one_file($env, $db);

switch ($action) {
    case 'display':
        display_files($env, $db);
        break;
    case 'confirm':
        confirm_file($env, $db);
        break;
    case 'delete':
        delete_file($env, $db);
        break;
    case 'edit':
        edit_file($env, $db);
        break;
    case 'update':
        update_file($env, $db);
        break;
    case 'csite':
        create_sites($env, $db);
        break;
    case 'psize':
        purge_size($env, $db);
        break;
    case 'pzero':
        purge_zero($env, $db);
        break;
    case 'debug':
        debug_files($env, $db);
        break;
    default:
        unknown_option($action, $db);
        break;
}

echo head_standard_html_footer($authuser, $db);
