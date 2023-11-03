<?php

/*
Revision history:

Date        Who     What
----        ---     ----
12-Feb-03   EWB     Created.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
10-Mar-03   NL      Create local_nav & local_inf using config_navigate() & config_info()
19-Mar-03   NL      Move debug_note line below $debug.
12-Jun-03   EWB     Sorting options.
13-Nov-03   EWB     Global/Local variables.
14-Nov-03   EWB     Describe by Site or Name.
14-Nov-03   EWB     Delete Machine
21-Nov-03   EWB     Be prepared to deal with empty or weird variable names.
26-Nov-03   EWB     Report age of records.
31-Dec-03   EWB     Age is an integer number of days.
23-Feb-03   EWB     More precise age display.
22-Mar-04   EWB     Added provisional update time to config census.
 7-Apr-04   EWB     Sort by column header.
 8-Apr-04   EWB     Age is first.
21-Apr-04   EWB     Show Checksum Cache.
27-Apr-04   EWB     remove checksum cache when deleting machine.
27-Apr-04   EWB     command to invalidate site or host cache.
 4-Jun-04   EWB     sort by site goes site/vers/host ... easy to find mismatch.
17-Aug-04   EWB     Report which machines need syncronization.
12-Oct-04   EWB     Variable Summary
13-Jan-05   EWB     View Recent Changes
24-Feb-05   EWB     Database Consistancy Check
 9-Mar-05   EWB     Unconfigurable Variables
10-Mar-05   EWB     gconfig version
 1-Jun-05   EWB     checksum cache
30-Jun-05   EWB     debug_hmap
13-Jul-05   EWB     debug_hgrp / sgrp / gsem
 3-Aug-05   EWB     build fake site
 4-Aug-05   EWB     remove fake site
12-Sep-05   BTE     Added checksum invalidation code.
20-Sep-05   BTE     Properly specify tables in kill_vers.
07-Oct-05   BTE     Small bugfix in cleanup_scrips.
12-Oct-05   BTE     Changed references from gconfig to core.
03-Nov-05   BTE     Changed VarValues.* statements to explicit columns.
05-Nov-05   BTE     Specify the operation when calling checksum invalidation
                    code, fixed an undefined variable.
10-Nov-05   BTE     Some delete operations should not be permanent.
13-Nov-05   BJS     Added GCFG_ to find_site_mgrp & find_host_mgrp.
15-Dev-05   BTE     Updated to handle VarValues.def.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.  Removed the "fake site".
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.                    

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-cnst.php');
include('../lib/l-errs.php');
include('../lib/l-dsyn.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-rlib.php');
include('../lib/l-gsql.php');
include('../lib/l-jump.php');
include('../lib/l-user.php');
include('../lib/l-tabs.php');
include('../lib/l-cprg.php');
include('../lib/l-gcfg.php');
include('local.php');
include('../lib/l-head.php');


function fulldate($time)
{
    return ($time) ? date('m/d/y H:i:s', $time) : '<br>';
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
    if ((86400 <= $secs) && ($dd <= 3)) {
        $hh  = intval(round($secs / 3600));
        $txt = "$hh hours";
    }
    if (3 < $dd) {
        $dd  = intval(round($secs / 86400));
        $txt = "$dd days";
    }

    return $txt;
}

function title($act)
{
    switch ($act) {
        case 'list':
            return 'Site Manager Machine Census';
        case 'lcl':
            return 'Show Machine Locals';
        case 'new':
            return 'Recent Changes';
        case 'del':
            return 'Delete Machine';
        case 'sum':
            return 'Variable Summary';
        case 'map':
            return 'Mapping Summary';
        case 'site':
            return 'Site Groups';
        case 'host':
            return 'Host Groups';
        case 'vars':
            return 'Show Variables';
        case 'view':
            return 'Machine State';
        case 'sems':
            return 'Semaphores';
        case 'vers':
            return 'Show Versions';
        case 'name':
            return 'Show Name Eveywhere';
        case 'sync':
            return 'Need to be Syncronized';
        case 'dgbl':
            return 'Invalidate Site Checksum Cache';
        case 'dlcl':
            return 'Invalidate Machine Checksum Cache';
        case 'menu':
            return 'Debug Menu';
        case 'csum':
            return 'Legacy Checksum Cache';
        case 'none':
            return 'No Access';
        case 'ovar':
            return 'One Variable';
        case 'gsid':
            return 'Group / Scope';
        case 'grps':
            return 'All Groups';
        case 'mgrp':
            return 'Single Machine Group';
        case 'sing':
            return 'Single Value';
        case 'hmap':
            return 'ValueMap for Single Machine';
        case 'hgrp':
            return 'Host Machine Group';
        case 'sgrp':
            return 'Site Machine Group';
        case 'gsem':
            return 'Group / Host Semaphores';
        default:
            return "Unknown Action ($act)";
    }
}


function again(&$env)
{
    $priv = $env['priv'];
    $self = $env['self'];
    $cmd  = "$self?act";
    $aa   = array();
    $aa[] = html_link('#top', 'top');
    $aa[] = html_link('#bottom', 'bottom');
    $aa[] = html_link('index.php', 'config');
    $aa[] = html_link($self, 'census');
    $aa[] = html_link("$cmd=menu", 'menu');
    $aa[] = html_link("$cmd=new", 'new');
    if ($priv) {
        $args = $env['args'];
        $href = ($args) ? "$self?$args" : $self;
        $home = '../acct/index.php';
        $aa[] = html_link($href, 'again');
        $aa[] = html_link($home, 'home');
    }
    return jumplist($aa);
}

function order($ord)
{
    switch ($ord) {
        case  0:
            return 'provisional desc, ctime desc, censusid';
        case  1:
            return 'provisional desc, ctime, censusid';
        case  2:
            return 'site, vers desc, host, censusid';
        case  3:
            return 'site desc, vers, host desc, censusid';
        case  4:
            return 'host, site, censusid';
        case  5:
            return 'host desc, site, censusid';
        case  6:
            return 'vers desc, site, host, censusid';
        case  7:
            return 'vers, site, host, censusid';
        case  8:
            return 'stime desc, censusid';
        case  9:
            return 'stime, id';
        case 10:
            return 'provisional desc, ctime desc, censusid';
        case 11:
            return 'provisional, ctime, censusid';
        case 12:
            return 'censusid';
        case 13:
            return 'censusid desc';
        case 14:
            return 'uuid desc, ctime, censusid';
        case 15:
            return 'uuid, ctime desc, censusid';
        default:
            return order(0);
    }
}


function para($txt)
{
    return "<p>$txt</p>\n";
}


/*
    |   Makes a printable version of the
    |   string for display.
    */

function printable($s)
{
    $x = '';
    $n = strlen($s);
    for ($i = 0; $i < $n; $i++) {
        $ch = ord($s[$i]);
        if ($ch < 32) {
            switch ($ch) {
                case  7:
                    $x .= '\\a';
                    break;
                case  8:
                    $x .= '\\b';
                    break;
                case  9:
                    $x .= '\\t';
                    break;
                case 10:
                    $x .= '\\n';
                    break;
                case 11:
                    $x .= '\\v';
                    break;
                case 12:
                    $x .= '\\f';
                    break;
                case 13:
                    $x .= '\\r';
                    break;
                default:
                    $x .= sprintf('\\%03o', $ch);
            }
        } else {
            $x .= chr($ch);
        }
    }
    return $x;
}

function display_value($xx, $max)
{
    if ($xx == '') $xx = '<br>';
    $xx = printable($xx);
    if (strlen($xx) > $max) {
        $xx = substr($xx, 0, $max) . '...';
    }
    return $xx;
}


function debug_vers(&$env, $db)
{
    $sql = "select vers,\n"
        . " min(censusid) as hid,\n"
        . " count(censusid) as num\n"
        . " from Revisions\n"
        . " group by vers\n"
        . " order by vers";
    $set = find_many($sql, $db);
    if ($set) {
        $self = $env['self'];
        $args = explode('|', 'vers|number');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "Versions &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $hid  = $row['hid'];
            $vers = $row['vers'];
            $num  = $row['num'];
            $args = array($vers, $num);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}


function debug_vars(&$env, $db)
{
    $sql = "select * from Variables\n"
        . " order by scop, name, varuniq";
    $set = find_many($sql, $db);
    if ($set) {
        $self = $env['self'];
        $args = explode('|', 'scrip|name|type|vid');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "Variables &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $vid  = $row['varid'];
            $name = $row['name'];
            $scop = $row['scop'];
            $type = $row['itype'];

            $ovar = "$self?act=ovar&vid=$vid";
            $name = html_link($ovar, $name);
            $type = type_name($type);
            $args = array($scop, $name, $type, $vid);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}

function find_var_vid($vid, $db)
{
    $row = array();
    if ($vid) {
        $sql = "select * from Variables\n"
            . " where varid = $vid";
        $row = find_one($sql, $db);
    }
    return $row;
}


function sequence_varid($set)
{
    $out = array();
    if ($set) {
        reset($set);
        foreach ($set as $key => $row) {
            $vid = $row['varid'];
            $out[$vid] = $row;
        }
    }
    return $out;
}


function find_versions($hid, $db)
{
    $set = array();
    if ($hid) {
        $sql = "select V.name, V.scop, V.varid, X.* from\n"
            . " Revisions as R,\n"
            . " Variables as V,\n"
            . " VarVersions as X\n"
            . " where R.censusid = $hid\n"
            . " and V.varuniq = X.varuniq\n"
            . " and X.vers = R.vers\n"
            . " group by X.varuniq\n"
            . " order by X.varuniq";
        $set = find_many($sql, $db);
    }
    return sequence_varid($set);
}


function find_variable_gid($gid, $db)
{
    $set = array();
    if ($gid) {
        $selSQL = "Variables.name, Variables.scop, VarValues.valueid, "
            . "VarValues.varuniq, VarValues.mgroupuniq, VarValues.valu, "
            . "VarValues.revl, VarValues.def, VarValues.revldef, "
            . "VarValues.clientconf, VarValues.revlclientconf, "
            . "VarValues.last, VarValues.host, Variables.varid";
        $addSQL = "group by VarValues.varuniq, order by VarValues.varuniq";

        $set = GCFG_GetVariableInfo(
            '',
            0,
            0,
            $gid,
            '',
            0,
            $selSQL,
            $addSQL,
            1,
            0,
            0,
            $db
        );
    }
    return sequence_varid($set);
}


function find_vmap_hid($hid, $db)
{
    $set = array();
    if ($hid) {
        $sql = "select V.name, V.scop, V.varid, M.* from\n"
            . " Variables as V,\n"
            . " ValueMap as M,\n"
            . " Census as C\n"
            . " where M.censusuniq = C.censusuniq\n"
            . " and C.id = $hid\n"
            . " and M.varuniq = V.varuniq\n"
            . " group by M.varuniq\n"
            . " order by M.varuniq";
        $set = find_many($sql, $db);
    }
    return sequence_varid($set);
}


function minitime($time)
{
    return ($time) ? date('m/d H:i', $time) : $time;
}


/* debug_ovar

        Displays information on a single variable.
    */
function debug_ovar(&$env, $db)
{
    $set = array();
    $vid = $env['vid'];
    $var = find_var_vid($vid, $db);
    if ($var) {
        $selSQL = "MachineGroups.name, MachineCategories.category, "
            . "MachineCategories.precedence, VarValues.valueid, "
            . "VarValues.varuniq, VarValues.mgroupuniq, VarValues.valu, "
            . "VarValues.revl, VarValues.def, VarValues.revldef, "
            . "VarValues.clientconf, VarValues.revlclientconf, "
            . "VarValues.last, VarValues.host, MachineGroups.mgroupid";
        $addSQL = "order by MachineCategories.precedence, "
            . "MachineGroups.name";

        $set = GCFG_GetVariableInfo(
            '',
            0,
            $vid,
            0,
            '',
            0,
            $selSQL,
            $addSQL,
            1,
            0,
            1,
            $db
        );
    }
    if ($set) {
        $self = $env['self'];
        $name = $var['name'];
        $scop = $var['scop'];

        $args = explode('|', 'cat|name|gid|xid|revl|when|host|len|value');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "$name:$scop &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $xid  = $row['valueid'];
            $gid  = $row['mgroupid'];
            $pre  = $row['precedence'];
            $cat  = $row['category'];
            $name = $row['name'];
            $revl = $row['revl'];
            $last = $row['last'];
            $host = $row['host'];
            $len  = strlen($row['valu']);
            $last = minitime($last);
            $valu = disp($row, 'valu');
            $valu = display_value($valu, 50);
            $gsid = "$self?act=gsid&gid=$gid&sid=$scop";
            $sing = "$self?act=sing&xid=$xid";
            $name = html_link($gsid, $name);
            $xid  = html_link($sing, $xid);
            $args = array($cat, $name, $gid, $xid, $revl, $last, $host, $len, $valu);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}



function debug_sum(&$env, $db)
{
    debug_note("debug sum");
    $now = $env['now'];
    $sql = "select count(V.valueid) as num,\n"
        . " max(V.last) as time,\n"
        . " G.name, V.mgroupuniq, G.mgroupid from\n"
        . " VarValues as V,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
        . " where V.mgroupuniq = G.mgroupuniq\n"
        . " group by V.mgroupuniq\n"
        . " order by G.name";
    $set = find_many($sql, $db);
    if ($set) {
        $args = explode('|', 'Group|Variables|Gid|Age|When');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "Value Summary &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $num  = $row['num'];
            $last = $row['time'];
            $name = $row['name'];
            $gid  = $row['mgroupid'];
            $time = fulldate($last);
            $secs = age($now - $last);
            $args = array($name, $num, $gid, $secs, $time);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}


function debug_map(&$env, $db)
{
    debug_note("debug map");
    $now = $env['now'];
    $sql = "select C.*, R.ctime, R.vers,\n"
        . " count(M.valmapid) as maps from\n"
        . " " . $GLOBALS['PREFIX'] . "core.ValueMap as M,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Revisions as R,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
        . " where C.censusuniq = M.censusuniq\n"
        . " and C.id = R.censusid\n"
        . " group by C.id\n"
        . " order by C.site, C.host";
    $set = find_many($sql, $db);
    if ($set) {
        $self = $env['self'];
        $args = explode('|', 'Age|Site|Host|Vers|Maps|When');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "ValueMap Summary &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        $now = time();

        reset($set);
        foreach ($set as $key => $row) {
            $hid  = $row['id'];
            $maps = $row['maps'];
            $vers = $row['vers'];
            $last = $row['ctime'];
            $site = $row['site'];
            $host = $row['host'];
            $when = fulldate($last);
            $secs = age($now - $last);
            $hmap = "$self?act=hmap&hid=$hid";
            $host = html_link($hmap, $host);
            $args = array($secs, $site, $host, $vers, $maps, $when);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}


/* debug_new

        Displays those variables who have most recently changed.
    */
function debug_new(&$env, $db)
{
    debug_note("debug new");
    $self = $env['self'];
    $now  = $env['now'];
    $max  = $env['max'];

    $selSQL = "VarValues.valueid, VarValues.varuniq, VarValues.mgroupuniq,"
        . " VarValues.valu, VarValues.revl, VarValues.def, "
        . "VarValues.revldef, VarValues.clientconf, "
        . "VarValues.revlclientconf, VarValues.last, VarValues.host, "
        . "Variables.name, Variables.itype, Variables.scop, "
        . "MachineGroups.name AS mgrp, MachineGroups.mgroupid, "
        . "Variables.varid";
    $addSQL = "order by last desc limit 20";

    $set = GCFG_GetVariableInfo(
        '',
        0,
        0,
        0,
        '',
        0,
        $selSQL,
        $addSQL,
        1,
        0,
        0,
        $db
    );
    if ($set) {
        $text = "Recent Changes";
        $head = explode('|', 'name|group|scrip|host|last|revl|xid|type|value');
        $rows = safe_count($set);
        $cols = safe_count($head);

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($head, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $gid  = $row['mgroupid'];
            $xid  = $row['valueid'];
            $vid  = $row['varid'];
            $revl = $row['revl'];
            $last = $row['last'];
            $valu = $row['valu'];
            $scop = $row['scop'];
            $type = $row['itype'];
            $grp  = disp($row, 'mgrp');
            $name = disp($row, 'name');
            $host = disp($row, 'host');
            $valu = display_value($valu, $max);
            $last = fulldate($last);
            $type = type_name($type);
            $ovar = "$self?act=ovar&vid=$vid";
            $mgrp = "$self?act=mgrp&gid=$gid";
            $sing = "$self?act=sing&xid=$xid";
            $name = html_link($ovar, $name);
            $grp  = html_link($mgrp, $grp);
            $xid  = html_link($sing, $xid);
            $args = array($name, $grp, $scop, $host, $last, $revl, $xid, $type, $valu);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}


function find_mgrp($name, $db)
{
    $qn  = safe_addslashes($name);
    $tag = constStyleBuiltin;
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups\n"
        . " where name = '$qn'\n"
        . " and style = $tag\n"
        . " and human = 0\n"
        . " and global = 1";
    return find_one($sql, $db);
}

function site_code($site, $db)
{
    $qs  = safe_addslashes($site);
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers\n"
        . " where customer = '$qs'\n"
        . " and username = ''";
    $row = find_one($sql, $db);
    return ($row) ? $row['id'] : 0;
}


function delete_host(&$env, $db)
{
    $hid  = $env['hid'];
    $self = $env['self'];
    $priv = $env['priv'];
    $auth = $env['auth'];
    $revl = array();
    $host = '';
    $site = '';
    if (($priv) && ($hid > 0)) {
        $sql = "select R.*, C.host,\n"
            . " C.site, C.uuid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " Revisions as R\n"
            . " where C.id = R.censusid\n"
            . " and R.censusid = $hid";
        $revl = find_one($sql, $db);
    }

    if ($revl) {
        $host = $revl['host'];
        $site = $revl['site'];
        $hid  = $revl['censusid'];
        $num  = purge_config_host($hid, $site, $host, $db);
        if ($num) {
            $msg  = "config: $host removed (n:$num) from $site by $auth";
            logs::log(__FILE__, __LINE__, $msg, 0);
            debug_note($msg);
        }
    }
}


function show_list(&$env, &$set, $cmd)
{
    $now  = $env['now'];
    $ord  = $env['ord'];
    $self = $env['self'];
    if ($set) {
        $o    = "$self?act=$cmd&ord";
        $lref = ($ord ==  0) ? "$o=1"  : "$o=0";     // last 0,1
        $sref = ($ord ==  2) ? "$o=3"  : "$o=2";     // site 2,3
        $href = ($ord ==  4) ? "$o=5"  : "$o=4";     // host 4,5
        $vref = ($ord ==  6) ? "$o=7"  : "$o=6";     // vers 6,7
        $cref = ($ord ==  8) ? "$o=9"  : "$o=8";     // chng 8,9
        $pref = ($ord == 10) ? "$o=11" : "$o=10";    // prov 10,11
        $iref = ($ord == 12) ? "$o=13" : "$o=12";    // id   12,13
        $uref = ($ord == 14) ? "$o=15" : "$o=14";    // uuid 14,15

        $a   = array();
        $a[] = html_link($lref, 'Age');
        $a[] = html_link($href, 'Host');
        $a[] = html_link($sref, 'Site');
        $a[] = html_link($uref, 'UUID');
        $a[] = html_link($iref, 'Id');
        $a[] = html_link($vref, 'Version');
        $a[] = html_link($lref, 'Last');
        $a[] = html_link($cref, 'Change');
        $a[] = html_link($pref, 'Prov');

        $cols = safe_count($a);
        $rows = safe_count($set);
        $text = "Revisions ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($a, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $hid  = $row['censusid'];
            $host = disp($row, 'host');
            $site = disp($row, 'site');
            $vers = disp($row, 'vers');
            $uuid = disp($row, 'uuid');
            $last = $row['ctime'];
            $serv = $row['stime'];
            $prov = $row['provisional'];

            $hgrp = "$self?hid=$hid&act=hgrp";
            $sgrp = "$self?hid=$hid&act=sgrp";
            $view = "$self?hid=$hid&act=view";
            $host = html_link($hgrp, $host);
            $site = html_link($sgrp, $site);
            $hid  = html_link($view, $hid);
            $age  = age($now - $last);
            $age  = age($now - $last);
            $last = fulldate($last);
            $serv = fulldate($serv);
            $prov = fulldate($prov);
            $args = array($age, $host, $site, $uuid, $hid, $vers, $last, $serv, $prov);
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        echo para('Nothing found ...');
    }
}




function debug_census(&$env, $db)
{
    $now  = $env['now'];
    $ord  = $env['ord'];
    $self = $env['self'];
    $ords = order($ord);
    $sql  = "select R.*, C.host,\n"
        . " C.site, C.uuid, C.censusuniq from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
        . " Revisions as R\n"
        . " where C.id = R.censusid\n"
        . " order by $ords";
    $list = find_many($sql, $db);
    show_list($env, $list, 'list');
}

function debug_sync(&$env, $db)
{
    $now  = $env['now'];
    $ord  = $env['ord'];
    $self = $env['self'];
    $ords = order($ord);
    $sql  = "select R.*, censusuniq from Revisions as R join Census \n"
        . " where stime > ctime and R.censusid=Census.id\n"
        . " order by $ords";
    $list = find_many($sql, $db);
    show_list($env, $list, 'sync');
}

function debug_csum(&$env, $db)
{
    $now  = $env['now'];
    $ord  = $env['ord'];
    $self = $env['self'];
    $sql  = "select L.*,\n"
        . " C.customer as site from\n"
        . " " . $GLOBALS['PREFIX'] . "core.LegacyCache as L,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers as C\n"
        . " where L.siteid > 0\n"
        . " and L.siteid = C.id\n"
        . " order by last desc\n"
        . " limit 20";
    $set = find_many($sql, $db);
    if ($set) {
        $args = explode('|', 'Age|Site|Globals|When|Dirty');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "Site Checksums &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        $now = time();

        reset($set);
        foreach ($set as $key => $row) {
            $site = $row['site'];
            $drty = $row['drty'];
            $gsum = $row['gsum'];
            $last = $row['last'];
            $secs = age($now - $last);
            $when = fulldate($last);
            $text = ($drty) ? 'Yes' : 'No';
            $args = array($secs, $site, $gsum, $when, $text);
            echo table_data($args, 0);
        }
        echo table_footer();
    }

    $sql = "select C.site, C.host, L.* from\n"
        . " " . $GLOBALS['PREFIX'] . "core.LegacyCache as L,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
        . " where L.censusid > 0\n"
        . " and L.censusid = C.id\n"
        . " order by last desc\n"
        . " limit 20";
    $set = find_many($sql, $db);
    if ($set) {
        $args = explode('|', 'Age|Site|Machine|Local|State|When|Dirty');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "Machine Checksums &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        $now = time();

        reset($set);
        foreach ($set as $key => $row) {
            $host = $row['host'];
            $site = $row['site'];
            $drty = $row['drty'];
            $lsum = $row['lsum'];
            $ssum = $row['ssum'];
            $last = $row['last'];
            $text = ($drty) ? 'Yes' : 'No';
            $secs = age($now - $last);
            $when = fulldate($last);
            $args = array($secs, $site, $host, $lsum, $ssum, $when, $text);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
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


function debug_menu(&$env, $db)
{

    $self = $env['self'];
    $cmd  = "$self?act";

    $act[] = "$cmd=menu";
    $txt[] = 'Debug Menu';

    $act[] = "$cmd=sum";
    $txt[] = 'Summary';

    $act[] = "$cmd=grps";
    $txt[] = 'Groups';

    $act[] = "$cmd=new";
    $txt[] = 'Most Recently Changed';

    $act[] = "$cmd=map";
    $txt[] = 'ValueMap Summary';

    $act[] = "$cmd=csum";
    $txt[] = 'Legacy Checksum Cache';

    $act[] = "$cmd=vars";
    $txt[] = 'Known Variables';

    $act[] = "$cmd=site";
    $txt[] = 'Site Groups';

    $act[] = "$cmd=host";
    $txt[] = 'Host Groups';

    $act[] = "$cmd=vers";
    $txt[] = 'Known Versions';

    $act[] = $self;
    $txt[] = 'Begin Again';

    $act[] = "$cmd=stat";
    $txt[] = 'Statistics';

    command_list($act, $txt);
}


function debug_none($env)
{
    $msg = "This page requires administrative access.";
    $msg = fontspeak($msg);
    echo "<br><p>$msg</p><br>\n";
}


function single($name, $row)
{
    if (($name) && ($row)) {
        $args = explode('|', 'name|valu');
        $rows = safe_count($row);
        $cols = safe_count($args);
        $text = "$name &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($row);
        foreach ($row as $key => $data) {
            $text = ($data == '') ? '<br>' : $data;
            $args = array($key, $text);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}


function debug_view(&$env, $db)
{
    $hid = $env['hid'];
    $set = array();
    $rev = find_revl($hid, $db);
    if ($rev) {
        $hid = $rev['censusid'];
        $sql = "select G.*, C.category,\n"
            . " count(V.valueid) as num,\n"
            . " C.precedence from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.VarValues as V,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as H\n"
            . " where M.censusuniq = H.censusuniq\n"
            . " and H.id = $hid\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and M.mgroupuniq = V.mgroupuniq\n"
            . " and G.mcatuniq = C.mcatuniq\n"
            . " group by G.mgroupuniq\n"
            . " order by C.precedence, G.name";
        $set = find_many($sql, $db);
    }
    if ($rev) {
        $host = $rev['host'];
        $site = $rev['site'];
        $name = "$host at $site";
        $rev['ctime'] = datestring($rev['ctime']);
        $rev['stime'] = datestring($rev['stime']);
        single($name, $rev);

        $self = $env['self'];
        $del  = "$self?act=del&hid=$hid";
        $cfg  = "config.php?act=list&hid=$hid";
        $del  = html_link($del, '[delete]');
        $cfg  = html_link($cfg, '[config]');
        echo para("$cfg&nbsp;&nbsp;$del");
    }
    if ($set) {
        $host = $rev['host'];
        $site = $rev['site'];
        $self = $env['self'];

        $args = explode('|', 'precedence|category|group|gid|vars|sems');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "$host at $site &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $mpre = $row['precedence'];
            $mcat = $row['category'];
            $name = $row['name'];
            $gid  = $row['mgroupid'];
            $num  = $row['num'];
            $mgrp = "$self?act=mgrp&gid=$gid";
            $gsem = "$self?act=gsem&gid=$gid&hid=$hid";
            $name = html_link($mgrp, $name);
            $sems = html_link($gsem, '[sems]');
            $args = array($mpre, $mcat, $name, $gid, $num, $sems);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}


/* debug_sing

        Displays all the column data for a single entry in the VarValues table.
        Note that this has not been updated to respect the VarValues.def flag,
        so if this flag is 1 the valu column cannot be trusted.
    */
function debug_sing(&$env, $db)
{
    $xid = $env['xid'];
    $row = array();
    if ($xid) {
        $sql = "select V.* from\n"
            . " VarValues as V\n"
            . " where V.valueid = $xid";
        $row = find_one($sql, $db);
    }
    if ($row) {
        $name = "core.VarValues($xid)";
        single($name, $row);
    }
}


function debug_mgrp(&$env, $db)
{
    $gid = $env['gid'];
    $grp = find_mgrp_info($gid, $db);
    $row = find_mgrp_revl($gid, $db);
    $set = array();
    if (($grp) && ($row)) {
        $qv  = safe_addslashes($row['vers']);
        $sql = "select V.scop, S.name,\n"
            . " count(X.varuniq) as number from\n"
            . " Variables as V,\n"
            . " VarValues as X,\n"
            . " Scrips as S,\n"
            . " MachineGroups as G\n"
            . " where S.vers = '$qv'\n"
            . " and S.num = V.scop\n"
            . " and X.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid = $gid\n"
            . " and X.varuniq = V.varuniq\n"
            . " group by V.scop\n"
            . " order by V.scop";
        $set = find_many($sql, $db);
    }

    if (($set) && ($grp)) {
        $self = $env['self'];
        $name = $grp['name'];
        $args = explode('|', 'scop|name|number');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "$name &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $scop = $row['scop'];
            $name = $row['name'];
            $num  = $row['number'];
            $gsid = "$self?act=gsid&sid=$scop&gid=$gid";
            $name = html_link($gsid, $name);
            $args = array($scop, $name, $num);
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        if (!$set) echo para('Scrips Not Found');
        if (!$grp) echo para("Machine group <b>$gid</b> not found");
        if (!$row) echo para("Revision record not found");
    }
}

function find_revl($hid, $db)
{
    $row = array();
    if ($hid) {
        $sql = "select C.site, C.host,\n"
            . " C.uuid, R.*, U.id as cid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R\n"
            . " where R.censusid = $hid\n"
            . " and C.id = R.censusid\n"
            . " and C.site = U.customer\n"
            . " and U.username = ''";
        $row = find_one($sql, $db);
    }
    return $row;
}


function debug_hgrp(&$env, $db)
{
    $hgrp = array();
    $hid  = $env['hid'];
    $revl = find_revl($hid, $db);
    if ($revl) {
        $site = $revl['site'];
        $host = $revl['host'];
        $hgrp = GCFG_find_host_mgrp($hid, $site, $host, $db);
    }
    $gid = ($hgrp) ? $hgrp['mgroupid'] : 0;
    if ($gid) {
        $env['gid'] = $gid;
        debug_mgrp($env, $db);
    } else {
        echo para('group not found');
    }
}


function debug_sgrp(&$env, $db)
{
    $hid  = $env['hid'];
    $sgrp = array();
    $revl = find_revl($hid, $db);
    if ($revl) {
        $site = $revl['site'];
        $sgrp = GCFG_find_site_mgrp($site, $db);
    }
    $gid = ($sgrp) ? $sgrp['mgroupid'] : 0;
    if ($gid) {
        $env['gid'] = $gid;
        debug_mgrp($env, $db);
    } else {
        echo para('Site group not found.');
    }
}


function debug_gsem(&$env, $db)
{
    $set = array();
    $gid = $env['gid'];
    $hid = $env['hid'];
    if (($gid) && ($hid)) {
        $sql = "select V.scop, V.name, S.* from\n"
            . " Variables as V,\n"
            . " SemClears as S,\n"
            . " Census as C,\n"
            . " MachineGroups as G\n"
            . " where S.censusuniq = C.censusuniq\n"
            . " and C.id = $hid\n"
            . " and S.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid = $gid\n"
            . " and S.varuniq = V.varuniq\n"
            . " order by V.scop, V.name";
        $set = find_many($sql, $db);
        $grp = find_mgrp_info($gid, $db);
        $rev = find_revl($hid, $db);
    }
    if (($set) && ($grp) && ($rev)) {
        $host = $rev['host'];
        $name = $grp['name'];
        $self = $env['self'];
        $args = explode('|', 'scop|name|vid|last|revl|valu');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "$host / $name Semaphores &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $vid  = $row['varuniq'];
            $scop = $row['scop'];
            $name = $row['name'];
            $revl = $row['revl'];
            $last = $row['last'];
            $valu = $row['valu'];
            $last = minitime($last);
            $args = array($scop, $name, $vid, $last, $revl, $valu);
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        echo para("No semaphores found for this machine / group.");
    }
}


/* debug_gsid

        Show the values of variables for a specific scope and machine group.
    */
function debug_gsid(&$env, $db)
{
    $gid = $env['gid'];
    $sid = $env['sid'];
    $grp = find_mgrp_info($gid, $db);
    $set = array();
    if (($grp) && ($sid)) {
        $selSQL = "Variables.name, Variables.scop, VarValues.valueid, "
            . "VarValues.mgroupuniq, VarValues.valu, VarValues.revl, "
            . "VarValues.def, VarValues.revldef, VarValues.clientconf, "
            . "VarValues.revlclientconf, VarValues.last, VarValues.host, "
            . "VarValues.varuniq, Variables.varid";
        $addSQL = "order by name";

        $set = GCFG_GetVariableInfo(
            '',
            0,
            0,
            $gid,
            '',
            $sid,
            $selSQL,
            $addSQL,
            1,
            0,
            0,
            $db
        );
    }

    if (($set) && ($grp)) {
        $self = $env['self'];
        $name = $grp['name'];
        $args = explode('|', 'scop|name|last|revl|host|xid|cc|df|valu');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "$name &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $xid  = $row['valueid'];
            $vid  = $row['varid'];
            $def  = $row['def'];
            $scop = $row['scop'];
            $name = $row['name'];
            $revl = $row['revl'];
            $last = $row['last'];
            $host = $row['host'];
            $valu = $row['valu'];
            $ccnf = $row['clientconf'];
            $last = minitime($last);
            $valu = display_value($valu, 50);
            $ovar = "$self?act=ovar&vid=$vid";
            $sing = "$self?act=sing&xid=$xid";
            $name = html_link($ovar, $name);
            $xid  = html_link($sing, $xid);
            $args = array($scop, $name, $last, $revl, $host, $xid, $ccnf, $def, $valu);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}


function show_group_set(&$env, &$set, $name)
{
    if ($set) {
        $self = $env['self'];
        $args = explode('|', 'cat|name|gid|num');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "$name &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $gid  = $row['mgroupid'];
            $cats = $row['category'];
            $name = $row['name'];
            $num  = $row['num'];
            $mgrp = "$self?act=mgrp&gid=$gid";
            $name = html_link($mgrp, $name);
            $args = array($cats, $name, $gid, $num);
            echo table_data($args, 0);
        }
        echo table_footer();
    } else {
        echo para('Nothing found ...');
    }
}


function debug_grps(&$env, $db)
{
    $sql = "select G.*, C.category,\n"
        . " count(X.valueid) as num from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.VarValues as X\n"
        . " where G.mgroupuniq = X.mgroupuniq\n"
        . " and G.mcatuniq = C.mcatuniq\n"
        . " group by G.mgroupuniq\n"
        . " order by precedence, name";
    $set = find_many($sql, $db);

    show_group_set($env, $set, 'All Groups');
}



function debug_cat(&$env, $cat, $db)
{
    $qc  = safe_addslashes($cat);
    $sql = "select G.*, C.category,\n"
        . " count(M.valueid) as num from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.VarValues as M\n"
        . " where G.mgroupuniq = M.mgroupuniq\n"
        . " and G.mcatuniq = C.mcatuniq\n"
        . " and C.category = '$qc'\n"
        . " group by G.mgroupuniq\n"
        . " order by name";
    $set = find_many($sql, $db);
    $txt = "$cat Groups";
    show_group_set($env, $set, $txt);
}



function host_info($hid, $db)
{
    $row = array();
    if ($hid) {
        $sql = "select C.site, C.host,\n"
            . " C.uuid, R.* from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R\n"
            . " where R.censusid = $hid\n"
            . " and C.id = R.censusid";
        $row = find_one($sql, $db);
    }
    return $row;
}


function debug_hmap(&$env, $db)
{
    debug_note("debug hmap");
    $hid = $env['hid'];
    $row = host_info($hid, $db);
    $set = array();
    if ($row) {
        $sql = "select V.name, V.scop, M.* from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Variables as V,\n"
            . " " . $GLOBALS['PREFIX'] . "core.ValueMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C\n"
            . " where M.censusuniq = C.censusuniq"
            . " and C.id = $hid\n"
            . " and M.varuniq = V.varuniq\n"
            . " order by V.scop, V.name";
        $set = find_many($sql, $db);
    }

    if (($set) && ($row)) {
        $host = $row['host'];
        $site = $row['site'];
        $args = explode('|', 'scrip|vid|name|gid|stat|srev');
        $rows = safe_count($set);
        $cols = safe_count($args);
        $text = "$host ValueMap &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($set);
        foreach ($set as $key => $row) {
            $name = $row['name'];
            $scop = $row['scop'];
            $stat = $row['stat'];
            $srev = $row['srev'];
            $vid  = $row['varuniq'];
            $gid  = $row['mgroupuniq'];
            $args = array($scop, $vid, $name, $gid, $stat, $srev);
            echo table_data($args, 0);
        }
        echo table_footer();
    }
}

function debug_site(&$env, $db)
{
    debug_cat($env, constCatSite, $db);
}


function debug_host(&$env, $db)
{
    debug_cat($env, constCatMachine, $db);
}

function find_census_uuid($uuid, $db)
{
    $row = array();
    if ($uuid) {
        $qu  = safe_addslashes($uuid);
        $sql = "select * from"
            . " " . $GLOBALS['PREFIX'] . "core.Census where\n"
            . " uuid = '$qu'";
        $row = find_one($sql, $db);
    }
    return $row;
}


function find_census_site($site, $db)
{
    $set = array();
    if ($site) {
        $qs  = safe_addslashes($site);
        $sql = "select * from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census\n"
            . " where site = '$qs'\n"
            . " order by id";
        $set = find_many($sql, $db);
    }
    return $set;
}

/*
    |  Main program
    */

$now  = time();
$db   = db_connect();
$auth = process_login($db);
$comp = component_installed();

$user  = user_data($auth, $db);
$priv  = @($user['priv_debug']) ? 1 : 0;
$debug = @($user['priv_debug']) ? 1 : 0;
$admin = @($user['priv_admin']) ? 1 : 0;

$ord = get_integer('ord', 0);
$hid = get_integer('hid', 0);
$mid = get_integer('mid', 0);
$sid = get_integer('sid', 0);
$rid = get_integer('rid', 0);
$gid = get_integer('gid', 0);
$lid = get_integer('lid', 0);
$vid = get_integer('vid', 0);
$xid = get_integer('xid', 0);
$max = get_integer('max', 60);
$act = get_string('act', 'list');

if (!$admin) {
    $act = 'none';
}

$name = title($act);
$msg  = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($name, $comp, $auth, '', '', 0, $db);

$date = datestring($now);

echo "<h2>$date</h2>";

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

$env = array();
$env['hid'] = $hid;
$env['rid'] = $rid;
$env['mid'] = $mid;
$env['lid'] = $lid;
$env['gid'] = $gid;
$env['vid'] = $vid;
$env['xid'] = $xid;
$env['sid'] = $sid;
$env['ord'] = $ord;
$env['now'] = $now;
$env['max'] = $max;
$env['act'] = $act;
$env['priv'] = $priv;
$env['auth'] = $auth;
$env['self'] = server_var('PHP_SELF');
$env['args'] = server_var('QUERY_STRING');
$env['priv'] = $priv;

$good = mysqli_select_db($db, core);
if (!$good) {
    $act = 'none';
}
echo again($env);

switch ($act) {
    case 'list':
        debug_census($env, $db);
        break;
    case 'del':
        delete_host($env, $db);
        break;
    case 'sum':
        debug_sum($env, $db);
        break;
    case 'map':
        debug_map($env, $db);
        break;
    case 'new':
        debug_new($env, $db);
        break;
    case 'vars':
        debug_vars($env, $db);
        break;
    case 'vers':
        debug_vers($env, $db);
        break;
    case 'ovar':
        debug_ovar($env, $db);
        break;
    case 'none':
        debug_none($env, $db);
        break;
    case 'sync':
        debug_sync($env, $db);
        break;
    case 'csum':
        debug_csum($env, $db);
        break;
    case 'view':
        debug_view($env, $db);
        break;
    case 'menu':
        debug_menu($env, $db);
        break;
    case 'mgrp':
        debug_mgrp($env, $db);
        break;
    case 'gsid':
        debug_gsid($env, $db);
        break;
    case 'sing':
        debug_sing($env, $db);
        break;
    case 'grps':
        debug_grps($env, $db);
        break;
    case 'site':
        debug_site($env, $db);
        break;
    case 'hmap':
        debug_hmap($env, $db);
        break;
    case 'host':
        debug_host($env, $db);
        break;
    case 'hgrp':
        debug_hgrp($env, $db);
        break;
    case 'sgrp':
        debug_sgrp($env, $db);
        break;
    case 'gsem':
        debug_gsem($env, $db);
        break;
}
echo again($env);
echo head_standard_html_footer($auth, $db);
