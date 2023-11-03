<?php

/*
Revision history:

Date        Who     What
----        ---     ----
19-May-03   EWB     Created.
19-May-03   EWB     Show server name for title.
20-May-03   EWB     Show server version as well.
22-May-03   EWB     Again.
23-May-03   EWB     Navigational Links
10-Jun-03   EWB     Next and Previous
31-Jul-03   EWB     Fixup links.
09-Jan-04   EWB     Server Name.
16-Feb-04   EWB     server_name variable.
17-Apr-04   EWB     handle large files.
23-Apr-04   EWB     allow increasing font size.
21-Aug-04   EWB     pattern match.
23-Aug-04   EWB     notify, audit, meter
20-Jan-05   EWB     census
22-Feb-05   EWB     groups, migrate
05-Mar-05   EWB     duplicate, uuid
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.                     

*/


ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-user.php');
include('../lib/l-jump.php');
include('../lib/l-tabs.php');
include('../lib/l-head.php');

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
        $txt = sprintf('%d %d:%02d:%02d', $dd, $hh, $mm, $ss);
    if (8 <= $dd) {
        $dd  = intval(round($secs / 86400));
        $txt = "$dd days";
    }

    return $txt;
}

function minmax($min, $max, $val)
{
    if ($val <= $min) $val = $min;
    if ($max <= $val) $val = $max;
    return $val;
}

function ident($now)
{
    $date = datestring($now);
    $info = asi_info();
    $version = $info['svvers'];
    return "\n<h2>$date ($version)</h2>\n";
}


function show_size($size)
{
    $kb = 1024;
    $mb = $kb * $kb;
    $gb = $kb * $mb;
    $k  = round($size / $kb);

    if ($size <= 10240) {
        $t = "$size bytes";
    }
    if ((10240 < $size) && ($size <= $mb)) {
        $t = $k . 'k';
    }
    if (($mb < $size) && ($size <= $gb)) {
        $x = intval(round($size / ($mb / 10)));
        $m = intval($x / 10);
        $d = intval($x % 10);
        $t = sprintf('%d.%dM', $m, $d);
    }
    if ($size > $gb) {
        $x = intval(round($size / ($gb / 10)));
        $g = intval($x / 10);
        $d = intval($x % 10);
        $t = sprintf('%d.%dG', $g, $d);
    }
    return $t;
}

function again($env)
{
    $log  = $env['log'];
    $act  = $env['act'];
    $self = $env['self'];
    $args = $env['args'];
    $page = $env['page'];
    $sect = $env['sect'];
    $kmax = $env['kmax'];
    $priv = $env['priv'];

    $nlog = $log - 1;
    $plog = $log + 1;
    $npag = $page - 1;
    $ppag = $page + 1;
    $p    = "$self?act=$act&l=$log&k=$kmax&p";
    $l    = "$self?act=$act&p=0&k=$kmax&l";
    $k    = "$self?act=$act&p=0&l=$log&k";
    $a    = array();
    $a[]  = html_link('#top', 'top');
    $a[]  = html_link('#bottom', 'bottom');
    $a[]  = html_link("$l=$plog", 'previous');
    if ($sect > 1) {
        $n   = $sect - 1;
        $a[] = html_link("$p=$n", 'first page');
        if ($ppag < $n) {
            $a[] = html_link("$p=$ppag", 'page back');
        }
        if ($npag > 0) {
            $a[] = html_link("$p=$npag", 'page next');
        }
        $a[] = html_link("$p=0", 'last page');
    }
    if ($nlog > 0) {
        $a[] = html_link("$l=$nlog", 'next');
    }
    $a[] = html_link("$l=0", 'last');
    if ($kmax <= 512) {
        $new = $kmax * 2;
        $a[] = html_link("$k=$new", 'double');
    }
    if (2 <= $kmax) {
        $new = intval(round($kmax / 2));
        $a[] = html_link("$k=$new", 'half');
    }

    if ($priv) {
        $args = $env['args'];
        $href = ($args) ? "$self?$args" : $self;
        $menu = "$self?act=menu";
        $a[] = html_link($menu, 'menu');
        $a[] = html_link($href, 'again');
        $a[] = html_link('index.php', 'home');
    }
    return jumplist($a);
}

function nothing($env)
{
    echo "<p>Permision denied</p>\n";
}

function missing($env)
{
    $file = $env['file'];
    echo "<p>file <b>$file</b> does not exist.</p>\n";
}

function unknown($env)
{
    $act = $env['act'];
    echo "<p>Action <b>$act</b> is not implemented.</p>\n";
}


function sections($w, $p)
{
    return intval(($w + ($p - 1)) / $p);
}



/*
    |  http://www.php.net/manual/en/function.fopen.php
    |  http://www.php.net/manual/en/function.fseek.php
    |  http://www.php.net/manual/en/function.fread.php
    |  http://www.php.net/manual/en/function.fclose.php
    */


function loadfile($env)
{
    $now  = $env['now'];
    $log  = $env['log'];
    $file = $env['file'];
    $size = $env['size'];
    $sect = $env['sect'];
    $time = $env['time'];
    $page = $env['page'];
    $bmax = $env['bmax'];
    $kmax = $env['kmax'];

    /*
        |  Suppose we have a file which we have divided into
        |  ten pages, which we will will lable 0 through 9.
        |
        |  We want page zero to always refer to the last page,
        |  the one we will look at by default, so we want
        |  to specify a negative seek from the end of the file.
        |
        |  Note that the file may have grown since we found out it
        |  its size ... this is fine, since we are seeking from the
        |  end.
        */

    $read = $size;
    $seek = 0;
    $buff = '';

    $f = @fopen($file, 'rb');
    if ($f) {
        if ($sect > 1) {
            $read = intval($size / $sect);
            if ($page + 1 < $sect) {
                $seek = 0 - (($page + 1) * $read);
                fseek($f, $seek, SEEK_END);
            }
        }
        $read = minmax(0, $bmax, $read);
        $buff = fread($f, $read);
        fclose($f);
    } else {
        echo "<p>file <b>$file</b> not found.</p>\n";
    }


    $sp   = '&nbsp;';
    $date = datestring($time);
    $age  = age($now - $time);
    $len  = strlen($buff);
    $hrs  = show_size($size);
    $disp = $sect - $page;

    echo table_header();
    echo pretty_header('Logfile', 2);
    echo double('File:', $file);
    echo double('Size:', "$size $sp ($hrs)");
    echo double('Date:', $date);
    echo double('Age:', $age);
    echo double('Buffer:', $kmax . 'K bytes');
    if ($sect > 1) {
        echo double('Page:', "$disp of $sect");
        echo double('Length:', "$len bytes");
    }
    echo table_footer();

    debug_note("page:$page, sect:$sect, log:$log, seek:$seek, read:$read");
    return $buff;
}


function logfile($env)
{
    $font = $env['font'];
    $buff = loadfile($env);
    $len  = strlen($buff);
    if ($len > 0) {
        if ($font > 0) echo "<font size=\"$font\">";
        echo "\n<pre>\n";
        echo htmlspecialchars($buff);
        echo "\n</pre>\n<br>\n";
        if ($font > 0) echo "</font>\n";
    }
}


function pattern($env, $pat)
{
    $font = $env['font'];
    $buf = loadfile($env);
    $max = strlen($buf) - 1;
    $min = 0;
    $num = 0;
    $pos = strpos($buf, "\n", $min);
    echo "\n<pre>\n";
    while (($min <= $pos) && ($pos < $max)) {
        $len = $pos - $min;
        if ($len > 0) {
            $sub = substr($buf, $min, $len);
            $new = strpos($sub, $pat);

            //         echo "(l:$len,p:$pos,n:$new) $sub\n";

            if ($new) {
                $num++;
                $msg = htmlspecialchars($sub);
                echo "$msg\n";
            }
        }
        $min = $pos + 1;
        $pos = strpos($buf, "\n", $min);
    }
    echo "\n</pre>\n";
    if ($num <= 0)
        echo "<p>Pattern \"<b>$pat</b>\" not found.</p>\n";
    else
        echo "<p>Pattern \"<b>$pat</b>\" had <b>$num</b> matches.</p>\n";
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

function menu($env)
{
    $self = $env['self'];
    $cmd  = "$self?act";
    $big  = "$self?k=1024&act";

    $act = array();
    $txt = array();

    $act[] = "$cmd=logs";
    $txt[] = 'Show Logfile';

    $act[] = "$cmd=reps";
    $txt[] = '"report:"';

    $act[] = "$cmd=rdon";
    $txt[] = '"report: done"';

    $act[] = "$cmd=rslo";
    $txt[] = '"report: slow"';

    $act[] = "$cmd=ntfy";
    $txt[] = '"notify:"';

    $act[] = "$cmd=ndon";
    $txt[] = '"notify: done"';

    $act[] = "$cmd=nslo";
    $txt[] = '"notify: slow"';

    $act[] = "$cmd=cfgs";
    $txt[] = 'Config';

    $act[] = "$big=prgs";
    $txt[] = 'Purge';

    $act[] = "$cmd=updt";
    $txt[] = 'Update';

    $act[] = "$cmd=asst";
    $txt[] = 'Assets';

    $act[] = "$cmd=ptch";
    $txt[] = 'Patch';

    $act[] = "$cmd=audt";
    $txt[] = 'Audit';

    $act[] = "$cmd=metr";
    $txt[] = 'Meter';

    $act[] = "$big=cens";
    $txt[] = 'Census';

    $act[] = "$big=grps";
    $txt[] = 'Groups';

    $act[] = "$big=mgrt";
    $txt[] = 'Migrate';

    $act[] = "$big=dupl";
    $txt[] = 'Duplicate';

    $act[] = "$big=uuid";
    $txt[] = 'UUID';

    $act[] = "$big=msql";
    $txt[] = 'mysql messages';

    $act[] = "$big=fatl";
    $txt[] = 'php fatal errors';

    $act[] = "$big=warn";
    $txt[] = 'php warnings';

    $act[] = "$big=msgs";
    $txt[] = 'all php messages';

    $act[] = 'index.php';
    $txt[] = 'Home';
    command_list($act, $txt);
}



/*
    |  Main program
    */

$now   = time();
$db    = db_connect();
$auth  = process_login($db);
$comp  = component_installed();
$user  = user_data($auth, $db);
$admin = @($user['priv_admin']) ? 1 : 0;
$priv  = @($user['priv_debug']) ? 1 : 0;
$dbg   = get_integer('debug', 0);
$log   = get_integer('l', 0);
$page  = get_integer('p', 0);
$font  = get_integer('f', 0);
$kmax  = get_integer('k', 128);
$act   = get_string('act', 'logs');
$title = 'Show Logfile';
$debug = ($priv) ? $dbg : 0;
if (($priv) && ($admin)) {
    $server = server_name($db);
    $title  = "$server (log)";
}
$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $auth, 0, 0, 0, $db);
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$path = '/var/log/php/php.log';
$file = ($log > 0) ? "$path.$log" : $path;
$size = 0;
$time = 0;

if (($priv) && ($admin)) {
    if (file_exists($file)) {
        clearstatcache();
        $size = @intval(filesize($file));
        $time = @intval(filemtime($file));
    }

    if (($size <= 1) || ($time <= 0)) {
        $act = 'miss';
    }
} else {
    $act = 'none';
}

$kmax = minmax(1, 1024, $kmax);
$font = minmax(0, 10, $font);
$bmax = $kmax * 1024;
$sect = sections($size, $bmax);
$page = minmax(0, $sect - 1, $page);

$env['now']   = $now;
$env['log']   = $log;
$env['act']   = $act;
$env['kmax']  = $kmax;
$env['bmax']  = $bmax;
$env['page']  = $page;
$env['path']  = $path;
$env['font']  = $font;
$env['file']  = $file;
$env['sect']  = $sect;
$env['size']  = $size;
$env['time']  = $time;
$env['self']  = server_var('PHP_SELF');
$env['args']  = server_var('QUERY_STRING');
$env['priv']  = $priv;
$env['admin'] = $admin;
$env['debug'] = $debug;

echo again($env);

echo ident($now);

switch ($act) {
    case 'logs':
        logfile($env);
        break;
    case 'none':
        nothing($env);
        break;
    case 'miss':
        missing($env);
        break;
    case 'reps':
        pattern($env, 'report:');
        break;
    case 'rdon':
        pattern($env, 'report: done');
        break;
    case 'rslo':
        pattern($env, 'report: slow');
        break;
    case 'cfgs':
        pattern($env, 'config:');
        break;
    case 'prgs':
        pattern($env, 'purge:');
        break;
    case 'updt':
        pattern($env, 'update:');
        break;
    case 'asst':
        pattern($env, 'assets:');
        break;
    case 'ptch':
        pattern($env, 'patch:');
        break;
    case 'audt':
        pattern($env, 'audit:');
        break;
    case 'metr':
        pattern($env, 'meter:');
        break;
    case 'ntfy':
        pattern($env, 'notify:');
        break;
    case 'ndon':
        pattern($env, 'notify: done');
        break;
    case 'nslo':
        pattern($env, 'notify: slow');
        break;
    case 'msql':
        pattern($env, 'mysql:');
        break;
    case 'cens':
        pattern($env, 'census:');
        break;
    case 'grps':
        pattern($env, 'groups:');
        break;
    case 'mgrt':
        pattern($env, 'migrate');
        break;
    case 'dupl':
        pattern($env, 'duplicate');
        break;
    case 'uuid':
        pattern($env, 'uuid');
        break;
    case 'fatl':
        pattern($env, '] PHP Fa');
        break;
    case 'warn':
        pattern($env, '] PHP Wa');
        break;
    case 'msgs':
        pattern($env, '] PHP ');
        break;
    case 'menu':
        menu($env);
        break;
    default:
        unknown($env);
        break;
}

echo ident($now);
echo again($env);

echo head_standard_html_footer($auth, $db);
