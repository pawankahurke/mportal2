<?php

/*
Revision history:

Date        Who     What
----        ---     ----
 4-Jun-03   EWB     Created.
 5-Jun-03   EWB     Propogates globals
 5-Jun-03   EWB     Shows changed and Missing variables.
 5-Jun-03   EWB     Shows Donor Machine.
 6-Jun-03   EWB     Allow donor machine to be specified on command line.
 6-Jun-03   EWB     Report old/new for changes.
 6-Jun-03   EWB     Select All/Select None for sites.
 6-Jun-03   EWB     Show descriptions with variable names.
 9-Jun-03   EWB     Don't count crlf/nl as important changes.
 9-Jun-03   EWB     Scrips on left side, sites on right side.
 9-Jun-03   EWB     "Dangerous" scrips shown in red.
 9-Jun-03   EWB     Added jumptable.
12-Jun-03   EWB     More Alex suggestions.
 9-Jan-04   EWB     Server Name.
16-Feb-04   EWB     server_name variable.
21-Apr-04   EWB     clear the site checksum cache when exporting.
14-Jan-05   EWB     trivial change to error message.
20-May-05   EWB     works with gconfig database.
 1-Jun-05   EWB     legacy checksum cache
11-Jul-05   EWB     started full gconfig version.
13-Jul-05   EWB     create new configuration.
15-Jul-05   EWB     controlled partial update of any group.
19-Jul-05   EWB     copy any group over any other group.
21-Jul-05   EWB     group permissions checking.
12-Sep-05   BTE     Added checksum invalidation code.
12-Oct-05   BTE     Changed references from gconfig to core.
03-Nov-05   BTE     Changed VarValues.* statements to explicit columns.
05-Nov-05   BTE     Specify the operation when calling checksum invalidation
                    code.
10-Nov-05   BTE     Some delete operations should not be permanent.
07-Dec-05   BJS     drop_temp_table -> CWIZ_drop_temp_table().
13-Dec-05   BJS     find_site_mgrp -> GCFG_find_site_mgrp().
14-Dec-05   BTE     Added a comment.
26-Jan-06   BTE     Bug 3059: Redesign DSYN and tables to "remotable" internal
                    pointers.
24-Feb-06   BTE     Bug 3079: Make expunge/server deletions permanent and make
                    client preserve self.
13-Mar-06   BTE     Bug 3199: Remove unused core database columns.
20-Apr-06   BTE     Bug 3285: User interface group management issues from
                    emails.
19-May-06   BTE     Added $db parameter to GCFG_CreateVarValues.
23-May-06   BTE     Bug 3290: Config Module: Client syncing with server caused
                    variable changes.
19-Jul-06   BTE     Bug 3239: 913 errors on CI server.
21-Jul-06   BTE     Bug 3542: Config Module: Change export to not flag all
                    machines as changed.
24-Jul-06   BTE     Bug 3539: Minor text changes.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.

*/

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-cnst.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-slct.php');
include('../lib/l-rcmd.php');
include('../lib/l-jump.php');
include('../lib/l-head.php');
include('../lib/l-csum.php');
include('../lib/l-gsql.php');
include('../lib/l-tabs.php');
include('../lib/l-form.php');
include('../lib/l-rlib.php');
include('../lib/l-gcfg.php');
include('../lib/l-cwiz.php');
include('local.php');
include('../lib/l-user.php');
include('../lib/l-errs.php');
include('../lib/l-dsyn.php');

define('constCheckSites',  'Check All Sites');
define('constClearSites',  'Clear All Sites');
define('constCheckGroups', 'Check All Groups');
define('constClearGroups', 'Clear All Groups');
define('constCheckScrips', 'Check All Scrips');
define('constClearScrips', 'Clear All Scrips');
define('constCancel',      'Cancel');
define('constExport',      'Export');
define('constNext',        'Next &gt');


function color($color, $text)
{
    return "<font color=\"$color\">$text</font>";
}


function green($text)
{
    return color('green', $text);
}

function red($text)
{
    return color('red', $text);
}

function para($txt)
{
    return "<p>$txt</p>\n";
}

function debug_array($p)
{
    if ($p) {
        reset($p);
        foreach ($p as $key => $data) {
            debug_note("$key value:$data");
        }
    }
}


function again(&$env)
{
    $priv = $env['priv'];
    $self = $env['self'];
    $post = $env['post'];
    $act  = $env['act'];
    $hid  = $env['hid'];
    $cid  = $env['cid'];
    $cmd  = "$self?cid=$cid&hid=$hid&act";
    $aa   = array();
    $menu = "$cmd=menu";
    $aa[] = html_link('#top', 'top');
    $aa[] = html_link('#bottom', 'bottom');
    $aa[] = html_link($menu, 'menu');
    if (($act == 'site') || ($act == 'ecat')) {
        $aa[] = html_link('#control', 'control');
        $aa[] = html_link('#donor', 'donor');
        $aa[] = html_link('#select', 'select');
    }
    $aa[] = html_link('index.php', 'back');
    if ($priv) {
        $args = $env['args'];
        $href = ($args) ? "$self?$args" : $self;
        $home = '../acct/index.php';
        $aa[] = html_link('census.php', 'census');
        $aa[] = html_link($href, 'again');
        $aa[] = html_link($home, 'home');
    }
    return jumplist($aa);
}

function indent($n)
{
    $sp = '&nbsp;';
    return str_repeat($sp, $n);
}


function title(&$env)
{
    $act = $env['act'];
    switch ($act) {
        case 'done':
            return 'Scrip Configuration Export Completed';
        case 'cbld':
            return 'Select Category for New Configuration';
        case 'gbld':
            return 'Select Group for New Configuration';
        case 'gcfg':
            return 'Create Configuration For Machine Group';
        case 'none':
            return 'Cannot Export This Machine';
        case 'kill':
            return 'Remove Machine Group';
        case 'nbld':
            return 'Select New Donor Group';
        case 'ngrp':
            return 'Select Category for new Donor';
        case 'ecat':
            return 'Export to Specified Groups';
        case 'site':
            return 'Export to Specified Sites';
        case 'menu':;
        case 'mcat':;
        default:
            return 'Export Global Scrip Configurations';
    }
}

/*
    |  Tells how many machines are known
    |  to be at this site.
    */

function count_machines($site, $db)
{
    $qs  = safe_addslashes($site);
    $sql = "select C.id from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Revisions as R\n"
        . " where C.site = '$qs'\n"
        . " and C.id = R.censusid";
    $set = find_many($sql, $db);
    return safe_count($set);
}


function update_site($site, $now, $db)
{
    dirty_site($site, $db);
}


function return_to_sites()
{
    $link = html_link('index.php', 'Return to Sites');
    echo para($link);
}


function plural($n, $name)
{
    $word = ($n == 1) ? $name : $name . 's';
    return "$n $word";
}


function find_record($sql, $show, $db)
{
    if ($show)
        $row = find_one($sql, $db);
    else {
        $row = array();
        $res = command($sql, $db);
        if ($res) {
            if (mysqli_num_rows($res) == 1) {
                $row = mysqli_fetch_assoc($res);
            }
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    }
    return $row;
}


function onecommand($sql, $show, $db)
{
    return ($show) ? redcommand($sql, $db) :  command($sql, $db);
}



function get_description($name, $scop, $vers, $db)
{
    $qv  = safe_addslashes($vers);
    $qn  = safe_addslashes($name);
    $sql = "select X.descval as valu from\n"
        . " Variables as V,\n"
        . " VarVersions as X\n"
        . " where V.name = '$qn'\n"
        . " and V.scop = $scop\n"
        . " and X.vers = '$qv'\n"
        . " and V.varuniq = X.varuniq";
    $row = find_record($sql, 0, $db);
    return ($row) ? $row['valu'] : '';
}


/*
    |  Update a global, return a result code.
    |
    |    0 -- unchanged
    |    1 -- updated
    |    2 -- added
    |    3 -- error
    */

function update_global($gid, $self, $now, &$src, &$dst, $db)
{
    $code = 3;
    $vid  = $src['varid'];
    $nval = $src['valu'];
    $tnew = normalize($nval);
    $dvid = @intval($dst[$vid]['varid']);
    if ($dvid) {
        $oval = $dst[$vid]['valu'];
        $told = normalize($oval);
        if (strcmp($told, $tnew)) {
            $qv  = safe_addslashes($tnew);
            $qh  = safe_addslashes($self);

            $sql = "select valueid from VarValues left join Variables on ("
                . "VarValues.varuniq=Variables.varuniq) left join "
                . "MachineGroups on (VarValues.mgroupuniq=MachineGroups."
                . "mgroupuniq) where varid=$vid"
                . " and mgroupid=$gid and valu!='$qv'";
            $set = DSYN_DeleteSet(
                $sql,
                constDataSetGConfigVarValues,
                "valueid",
                "update_global",
                0,
                0,
                constOperationDelete,
                $db
            );
            if ($set) {
                $sql = "update VarValues left join Variables on ("
                    . "VarValues.varuniq=Variables.varuniq) left join "
                    . "MachineGroups on (VarValues.mgroupuniq="
                    . "MachineGroups.mgroupuniq) set\n"
                    . " valu = '$qv',\n"
                    . " host = '$qh',\n"
                    . " last = $now,\n"
                    . " revl = revl+1,\n"
                    . " revldef = revldef+def,\n"
                    . " def = 0\n"
                    . " where varid = $vid\n"
                    . " and mgroupid = $gid\n"
                    . " and valu != '$qv'";
                $res = onecommand($sql, 0, $db);
                $num = affected($res, $db);
                if ($num) {
                    $code = 1;
                }
                DSYN_UpdateSet(
                    $set,
                    constDataSetGConfigVarValues,
                    "valueid",
                    $db
                );
            }
        } else {
            $code = 0;
        }
    } else {
        // core.VarValues.def = 0;
        $qv  = safe_addslashes($tnew);
        $qh  = safe_addslashes($self);

        $sql = "insert into VarValues (valu,host,revl,last,revldef,scop,"
            . "name,varnameuniq,varscopuniq,varuniq,mcatuniq,"
            . "mgroupuniq)\n select '$qv','$qh',2,$now,1,Variables.scop,"
            . "Variables.name,Variables.varnameuniq,Variables.varscopuniq"
            . ",Variables.varuniq,MachineCategories.mcatuniq,"
            . "MachineGroups.mgroupuniq from Variables, MachineGroups, "
            . "MachineCategories where varid=$vid and mgroupid=$gid and "
            . "MachineGroups.mcatuniq=MachineCategories.mcatuniq";

        $res = onecommand($sql, 0, $db);
        if (affected($res, $db)) {
            $lastid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
            $err = PHP_DSYN_InvalidateRow(
                CUR,
                (int)$lastid,
                "valueid",
                constDataSetGConfigVarValues,
                constOperationInsert
            );
            if ($err != constAppNoErr) {
                logs::log(__FILE__, __LINE__, "update_global: PHP_DSYN_InvalidateRow2 "
                    . "returned " . $err, 0);
            }
            $code = 2;
            $name = $src['name'];
            $scop = $src['scop'];
            debug_note("variable $name:$scop did not exist");
        }
    }
    return $code;
}


function newline()
{
    return "<br clear=\"all\">\n";
}


// merge config.php

function find_names($hid, $db)
{
    $set = array();
    $tmp = array();
    if ($hid) {
        $sql = "select S.num, S.name\n"
            . " from Scrips as S,\n"
            . " Revisions as R\n"
            . " where R.censusid = $hid\n"
            . " and S.vers = R.vers\n"
            . " group by num\n"
            . " order by num";
        $tmp = find_many($sql, $db);
    }
    if ($tmp) {
        reset($tmp);
        foreach ($tmp as $key => $row) {
            $num = $row['num'];
            $set[$num] = $row['name'];
        }
    }
    return $set;
}


function namebox($name, $checked, $text)
{
    return checkbox($name, $checked) . $text;
}


function describe_donor(&$env)
{
    $txt = '';
    $xid = $env['xid'];
    if ($env['revl']) {
        $row = $env['revl'];
        $txt .= table_header();
        $txt .= pretty_header('Donor', 2);
        if ($xid) {
            $txt .= double('Name', $row['name']);
        } else {
            $txt .= double('Site', $row['site']);
            $txt .= double('Machine', $row['host']);
        }
        $txt .= double('Time', datestring($row['ctime']));
        $txt .= double('Version', $row['vers']);
        $txt .= table_footer();
    }
    return $txt;
}


function left_head($text)
{
    return <<< DONE

        <table>
        <th align="left">
            $text
        </th>
DONE;
}


/*
    |  Creates a list of legal target groups.
    |
    | The target groups should:
    |
    |   already have a configuration.
    |   be 100% owned by the specified user
    |   part of the specified catagory
    |   not the same as the donor.
    */

function find_active_groups($tid, $gid, $auth, $db)
{
    $set = array();
    $tmp = array();
    if (($tid) && ($gid) && ($auth)) {
        $gcfg = 'GroupConfig';
        $alen = 'GroupAliens';
        build_config_table($gcfg, $db);
        build_alien_table($alen, $auth, $db);
        $sql = "select G.mgroupid, G.name from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " $alen as A,\n"
            . " $gcfg as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as X,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as B\n"
            . " left join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M\n"
            . "  on M.censusuniq = X.censusuniq\n"
            . "  and M.mgroupuniq = G.mgroupuniq\n"
            . " where G.mcatuniq = B.mcatuniq\n"
            . " and B.mcatid = $tid\n"
            . " and X.id = A.id\n"
            . " and G.mgroupid != $gid\n"
            . " and G.mgroupid = C.id\n"
            . " and M.mgroupuniq is NULL\n"
            . " group by G.mgroupid\n"
            . " order by G.name";
        $tmp = find_many($sql, $db);
        CWIZ_drop_temp_table($gcfg, $db);
        CWIZ_drop_temp_table($alen, $db);
    }
    if ($tmp) {
        reset($tmp);
        foreach ($tmp as $key => $row) {
            $id = $row['mgroupid'];
            $set[$id] = $row['name'];
        }
    }
    return $set;
}


function find_site_groups($auth, $filt, $db)
{
    $set = array();
    $tmp = array();
    if ($auth) {
        $qa  = safe_addslashes($auth);
        $qc  = safe_addslashes(constCatSite);
        $flt = ($filt) ? " and U.sitefilter = 1\n" : '';
        $sql = "select G.mgroupid, G.name from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as H,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.VarValues as V\n"
            . " where U.username = '$qa'\n"
            .   $flt
            . " and G.mcatuniq = C.mcatuniq\n"
            . " and C.category = '$qc'\n"
            . " and U.customer = H.site\n"
            . " and M.censusuniq = H.censusuniq\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and M.mgroupuniq = V.mgroupuniq\n"
            . " group by G.mgroupid\n"
            . " order by G.name";
        $tmp = find_many($sql, $db);
    }
    if ($tmp) {
        reset($tmp);
        foreach ($tmp as $key => $row) {
            $gid = $row['mgroupid'];
            $set[$gid] = $row['name'];
        }
    }
    return $set;
}




function dangerous_scrips()
{
    return array(18, 43, 223, 229, 230);
}

function choose_target(&$env, $ecat, $db)
{
    $sgid = 0;
    $cid  = $env['cid'];
    $hid  = $env['hid'];
    $tid  = $env['tid'];
    $act  = $env['act'];
    $ctl  = $env['ctl'];
    $xid  = $env['xid'];
    $post = $env['post'];
    $priv = $env['priv'];
    $revl = $env['revl'];
    $auth = $env['auth'];
    $filt = $env['filt'];
    $jump = again($env);
    $debug  = $env['dbug'];
    $danger = dangerous_scrips();
    if (!$ctl) {
        $post = constCheckScrips;
    }

    if (($revl) && ($xid)) {
        $site = $revl['name'];
    }
    if (($revl) && (!$xid)) {
        $site = $revl['site'];
        $sgrp = GCFG_find_site_mgrp($site, $db);
        $xid  = ($sgrp) ? $sgrp['mgroupid'] : 0;
    }

    echo mark('control');
    echo $jump;
    $schk = constCheckScrips;
    $sclr = constClearScrips;
    if ($ecat) {
        $cchk = constCheckGroups;
        $cclr = constClearGroups;
        $objs = 'groups';
        $obj  = 'Group';
        $list = find_active_groups($tid, $xid, $auth, $db);
    } else {
        $cchk = constCheckSites;
        $cclr = constClearSites;
        $objs = 'sites';
        $obj  = 'Site';
        $list = find_site_groups($auth, $filt, $db);
    }
    $head = ucfirst($objs);

    $check_all  = ($post == $schk) ? 1 : 0;
    $clear_all  = ($post == $sclr) ? 1 : 0;
    $ccheck_all = ($post == $cchk) ? 1 : 0;
    $cclear_all = ($post == $cclr) ? 1 : 0;
    $donor_table = describe_donor($env);
    $names = find_names($hid, $db);

    unset($list[$xid]);

    if (($names) && ($list)) {
        $selected = array();
        $special  = array();
        reset($names);
        foreach ($names as $num => $name) {
            $var = "scp_$num";
            $chk = get_integer($var, 0);
            if ($check_all) $chk = 1;
            if ($clear_all) $chk = 0;
            $selected[$num] = $chk;
            $special[$num]  = 0;
        }
        reset($danger);
        foreach ($danger as $key => $num) {
            $special[$num] = 1;
            if ($check_all) {
                $selected[$num] = 0;
            }
        }
        $debug_option = '';
        if ($priv) {
            $opts = array('No', 'Yes');
            $txt  = green('$debug');
            $dbg  = html_select('debug', $opts, $debug, 1);
            $debug_option = double($txt, $dbg);
        }
        $donor_target   = mark('donor');
        $select_target  = mark('select');

        $scrips = safe_count($names);
        $sites  = safe_count($list);

        echo post_self('myform');
        echo hidden('ctl', 1);
        echo hidden('cid', $cid);
        echo hidden('hid', $hid);
        echo hidden('tid', $env['tid']);
        echo hidden('xid', $env['xid']);
        echo hidden('act', $act);
        echo hidden('ecat', $ecat);

        $xprt = button(constExport);
        $schk = button($schk);
        $sclr = button($sclr);
        $cchk = button($cchk);
        $cclr = button($cclr);
        $can  = button(constCancel);
        $in   = indent(5);

        $foot = "\n</table>\n";
        $msg  = left_head(ucfirst($objs));
        reset($list);
        foreach ($list as $gid => $name) {
            $var = "gid_$gid";
            $chk = get_integer($var, 0);
            if ($ccheck_all) $chk = 1;
            if ($cclear_all) $chk = 0;
            $args = array(namebox($var, $chk, $name));
            $msg .= table_data($args, 0);
        }
        $site_table = $msg . $foot;

        $msg = left_head('Scrips');
        reset($names);
        foreach ($names as $num => $name) {
            $txt = "$num - $name";
            if ($special[$num]) $txt = red($txt);
            $chk = $selected[$num];
            $var = "scp_$num";
            $args = array(namebox($var, $chk, $txt));
            $msg .= table_data($args, 0);
        }
        $scrp_table = $msg . $foot;

        echo <<< HERE

<table align="left" border="2" cellspacing="2" cellpadding="2">
<tr>
    <th colspan="2" bgcolor="#333399">
        <font color="white">
            Export $site Configuration
        </font>
    </th>
</tr>
<tr>
    <td>
       $schk
    </td>
    <td>
        Selects all Scrips except for # 18, 43, 223, 229, and 230.<br>
        You should not export the configuration of Scrips 18, 43,<br>
        223, 229, or 230 unless you are sure.
    </td>
</tr>
<tr>
    <td>
       $sclr
    </td>
    <td>
        De-selects all the Scrips.<br>
        An export operation requires the selection of at least one Scrip.
    </td>
</tr>
<tr>
    <td>
       $cchk
    </td>
    <td>
        Selects all the $objs.<br>
        This is generally not recommended.
    </td>
</tr>
<tr>
    <td>
       $cclr
    </td>
    <td>
        De-selects all $objs.<br>
        An export operation requires the selection of at least one $obj.
    </td>
</tr>
<tr>
    <td>
       $xprt
    </td>
    <td>
        Exports <b>$site</b> global Scrip configuration values.
    </td>
</tr>
<tr>
    <td>
       <input type="reset" value="Reset">
    </td>
    <td>
        Resets to initial settings.
    </td>
</tr>

$debug_option

</table>


<br clear="all">

<p>
   Found $scrips Scrips and $sites groups.
</p>

$donor_target

$jump

$donor_table

$select_target

$jump

<p>
  In the left-hand column, please select the Scrips whose global<br>
  configuration you want to export. In the right-hand column, please select<br>
  the $obj(s) you want to export the global Scrip configuration(s) to.
</p>

<p>
  <font color="red" size="3">
    Warning: if you are sure you want to copy Scrips 18, 43,<br>
    223, 229, or 230 then you must select them individually.
  </font>
</p>


<table align="left">
<tr>
    <td valign="top">
        $scrp_table
    </td>
    <td valign="top">
        $site_table
    </td>
</tr>
</table>

<br clear="all">

<p>
  ${in}${xprt}${in}${schk}${in}${sclr}${in}${cchk}${in}${cclr}${in}${can}
</p>

HERE;
        echo form_footer();
    } else {
        echo para('Nothing has changed.');
    }
    echo again($env);
}

/*
    |  Attempt to show a string in some reasonable printable form so
    |  we can examine it.
    */

function printable($text, $len)
{
    $none = '<i>(none)</i>';
    $text = normalize($text);
    $text = str_replace("\n", " ", $text);
    $text = htmlspecialchars($text);
    if (strlen($text) > $len) {
        if ($len > 3) $len = $len - 3;
        // this should be enough ...
        $text = substr($text, 0, $len) . '...';
    }
    return ($text == '') ? $none : addcslashes($text, "\0..\37");
}


function show_changes(&$dsc, &$vars, $sites, $names, $head, $db)
{
    $rows = 0;

    if ($vars) {
        reset($vars);
        foreach ($vars as $cid => $d1) {
            reset($d1);
            foreach ($d1 as $vid => $row) {
                $rows++;
            }
        }
    }

    if ($vars) {
        $text = 'Site|Scrip|Name|Variable|Value';
        $args = explode('|', $text);
        $cols = safe_count($args);
        $text = "$head &nbsp; ($rows found)";

        echo table_header();
        echo pretty_header($text, $cols);
        echo table_data($args, 1);

        reset($vars);
        foreach ($vars as $gid => $d1) {
            $site = $sites[$gid];
            reset($d1);
            foreach ($d1 as $vid => $row) {
                $scop = $row['scop'];
                $name = $row['name'];
                $old  = $row['old'];
                $new  = $row['new'];
                $scrp = printable($names[$scop], 40);

                $txt = @trim($dsc[$vid]['valu']);
                $txt = printable($txt, 40);
                $txt = "$txt<br><i>$name</i>";
                $old = printable($row['old'], 45);
                $new = printable($row['new'], 45);
                $val = "Old:&nbsp;&nbsp;$old<br>New:&nbsp;&nbsp;$new";
                $args = array($site, $scop, $scrp, $txt, $val);
                echo table_data($args, 0);
            }
        }

        echo table_footer();
    }
    return $rows;
}



function load_desc($hid, $db)
{
    $tmp = array();
    $set = array();
    if ($hid) {
        $sql = "select V.varid, V.name,\n"
            . " V.scop, X.descval as valu from\n"
            . " Revisions as R,\n"
            . " Variables as V,\n"
            . " VarVersions as X\n"
            . " where R.censusid = $hid\n"
            . " and R.vers = X.vers\n"
            . " and X.varuniq = V.varuniq\n"
            . " group by X.varuniq\n"
            . " order by V.scop, V.name";
        $set = find_many($sql, $db);
    }
    return sequence($set);
}


function load_vars($gid, $db)
{
    $set = array();
    if ($gid) {
        $sem = constVblTypeSemaphore;
        $sql = "select V.name, V.scop,\n"
            . " V.varid, X.valu from\n"
            . " VarValues as X,\n"
            . " Variables as V,\n"
            . " MachineGroups as G\n"
            . " where X.mgroupuniq=G.mgroupuniq\n"
            . " and G.mgroupid = $gid\n"
            . " and V.itype != $sem\n"
            . " and V.varuniq = X.varuniq\n"
            . " group by X.varuniq\n"
            . " order by V.scop, V.name";
        $set = find_many($sql, $db);
    }
    return sequence($set);
}



function export_done(&$env, $db)
{
    debug_note('export_done');
    echo again($env);
    $hid  = $env['hid'];
    $xid  = $env['xid'];
    $now  = $env['now'];
    $tid  = $env['tid'];
    $auth = $env['auth'];
    $filt = $env['filt'];
    $revl = $env['revl'];
    $ecat = $env['ecat'];
    debug_note("export source: $hid");
    echo describe_donor($env);
    if (($revl) && (!$xid)) {
        $site = $revl['site'];
        $sgrp = GCFG_find_site_mgrp($site, $db);
        $xid  = ($sgrp) ? $sgrp['mgroupid'] : 0;
    }
    $names = find_names($hid, $db);

    $gids = array();
    $scrp = array();
    $glob = array();
    $vars = array();
    $grps = array();

    if ($ecat) {
        $grps = find_active_groups($tid, $xid, $auth, $db);
    } else {
        $grps = find_site_groups($auth, $filt, $db);
    }

    reset($grps);
    foreach ($grps as $gid => $name) {
        if (get_integer("gid_$gid", 0)) {
            $gids[$gid] = $name;
        }
    }

    $dsc = load_desc($hid, $db);
    $src = load_vars($xid, $db);

    if ($src) {
        reset($src);
        foreach ($src as $vid => $row) {
            $scop = $row['scop'];
            $scrp[$scop] = false;
        }
    }

    $bb = 0;
    if ($names) {
        reset($names);
        foreach ($names as $scop => $name) {
            if (get_integer("scp_$scop", 0)) {
                $scrp[$scop] = true;
                $bb++;
            }
        }
    }

    $aa = safe_count($gids);
    debug_note("This will update as many as $bb scrips in $aa groups.");

    $vars = array();
    $miss = array();
    $self = server_name($db);
    $same = 0;
    $errs = 0;
    if (($gids) && ($scrp) && ($src) && ($dsc)) {
        reset($gids);
        foreach ($gids as $dgid => $site) {
            $changes = 0;
            $dst = load_vars($dgid, $db);
            if (($dst) && ($xid != $dgid)) {
                debug_note("process group $site");
                reset($src);
                foreach ($src as $vid => $row) {
                    $scop = $row['scop'];
                    $name = $row['name'];
                    if ($scrp[$scop]) {
                        $code = update_global($dgid, $self, $now, $row, $dst, $db);
                        if ($code == 0) {
                            $same++;
                        }
                        if ($code == 1) {
                            $new = $row['valu'];
                            $old = $dst[$vid]['valu'];
                            $vars[$dgid][$vid]['name'] = $name;
                            $vars[$dgid][$vid]['scop'] = $scop;
                            $vars[$dgid][$vid]['old'] = $old;
                            $vars[$dgid][$vid]['new'] = $new;
                            $changes++;
                        }
                        if ($code == 2) {
                            $new = $row['valu'];
                            $miss[$dgid][$vid]['name'] = $name;
                            $miss[$dgid][$vid]['scop'] = $scop;
                            $miss[$dgid][$vid]['old'] = '';
                            $miss[$dgid][$vid]['new'] = $new;
                            $changes++;
                        }
                        if ($code == 3) {
                            $errs++;
                        }
                    }
                }
            }
            if ($changes > 0) {
                if (!($ecat)) {
                    update_site($site, $now, $db);
                }
            }
        }
    }

    if (safe_count($vars) == 0) {
        echo para('No configuration values have been updated.');
    }

    if ($same > 0) {
        $text = plural($same, 'variable');
        $are  = ($same == 1) ? 'is' : 'are';
        echo para("There $are $text which $are already set to the specified value.");
    }

    if ($errs > 0) {
        $text = plural($errs, 'variable');
        $are  = ($errs == 1) ? 'is' : 'are';
        echo para("There $are $text which $are already set to the specified value.");
    }

    $cc = show_changes($dsc, $vars, $gids, $names, 'Changed Values', $db);
    if ($cc > 0) {
        $cnt = plural($cc, 'global variable');
        echo para("$cnt have been updated.");
    }
    $mm = show_changes($dsc, $miss, $gids, $names, 'Missing Values', $db);
    if ($mm > 0) {
        $cnt = plural($mm, 'configuration value');
        $was = ($mm == 1) ? 'was' : 'were';
        $has = ($mm == 1) ? 'has' : 'have';
        $msg = "There $was $cnt missing, which $has now been added.";
        echo para($msg);
    }
    echo again($env);
}


function site_revl($cid, $auth, $db)
{
    debug_note("site_revl($cid,$auth)");
    $row = array();
    if (($cid) && ($auth)) {
        $qa  = safe_addslashes($auth);
        $sql = "select R.*, H.site,\n"
            . " H.host, H.uuid,\n"
            . " U.id as cid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as H,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R\n"
            . " where U.id = $cid\n"
            . " and U.username = '$qa'\n"
            . " and H.site = U.customer\n"
            . " and R.censusid = H.id\n"
            . " group by R.censusid\n"
            . " order by R.vers desc, R.ctime desc\n"
            . " limit 1";
        $row = find_one($sql, $db);
    }
    return $row;
}


function mgrp_revl($xid, $auth, $db)
{
    debug_note("mgrp_revl($xid,$auth)");
    $row = array();
    if (($xid) && ($auth)) {
        $qa  = safe_addslashes($auth);
        $sql = "select R.*, H.site, H.host,\n"
            . " H.uuid, G.name,\n"
            . " U.id as cid from\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as H,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Customers as U,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Revisions as R\n"
            . " where G.mgroupid = $xid\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " and U.username = '$qa'\n"
            . " and H.site = U.customer\n"
            . " and R.censusid = H.id\n"
            . " and M.censusuniq = H.censusuniq\n"
            . " group by R.censusid\n"
            . " order by R.vers desc, R.ctime desc\n"
            . " limit 1";
        $row = find_one($sql, $db);
    }
    return $row;
}


function simple_list(&$act, &$txt)
{
    echo "\n\n<ol>\n";
    reset($txt);
    foreach ($txt as $key => $doc) {
        $cmd = html_link($act[$key], $doc);
        echo "<li>$cmd</li>\n";
    }
    echo "</ol>\n";
}


function command_list(&$act, &$txt)
{
    echo para('What do you want to do?');
    simple_list($act, $txt);
}


function export_menu(&$env, $db)
{
    echo again($env);
    $self = $env['self'];
    $priv = $env['priv'];
    $hid  = $env['hid'];
    $xid  = $env['xid'];
    $cid  = $env['cid'];
    $revl = $env['revl'];
    $act  = array();
    $txt  = array();
    echo describe_donor($env);
    $act[] = 'index.php?act=site';
    $txt[] = "Choose another site.";
    if ($hid) {
        $act[] = "$self?act=site&hid=$hid&xid=$xid";
        $txt[] = "Export to existing site(s)";
        $act[] = "$self?act=cbld&hid=$hid";
        $txt[] = "Create new machine configuration";
        $act[] = "config.php?hid=$hid";
        $txt[] = "Configure this machine";
    }
    $act[] = "$self?act=ngrp&hid=$hid";
    $txt[] = "Select a new donor group";
    if ($priv) {
        $act[] = 'census.php';
        $txt[] = 'Config Census';
        $act[] = '../acct/index.php';
        $txt[] = 'Debug Home';
    }
    command_list($act, $txt);
    echo again($env);
}


function export_none(&$env, $db)
{
    return_to_sites();
    echo para('No valid recipient destination discovered.');
    return_to_sites();
}

function export_site(&$env, $db)
{
    debug_note('export_site');
    return_to_sites();
    choose_target($env, 0, $db);
    return_to_sites();
}

function export_ecat(&$env, $db)
{
    debug_note('export_ecat');
    return_to_sites();
    choose_target($env, 1, $db);
    return_to_sites();
}

function find_all_cats($db)
{
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineCategories\n"
        . " order by precedence";
    return find_many($sql, $db);
}

function next_cancel()
{
    $nxt = button(constNext);
    $can = button(constCancel);
    return para("$nxt $can");
}

function choose_cat($env, $set, $act, $db)
{
    echo again($env);
    echo describe_donor($env);
    if ($set) {
        $tid = $env['tid'];
        $in  = indent(5);

        if (!$tid) {
            $tid = $set[0]['mcatid'];
        }
        echo post_self('myform');
        echo hidden('cid', $env['cid']);
        echo hidden('hid', $env['hid']);
        echo hidden('xid', $env['xid']);
        echo hidden('act', $act);

        echo para('Choose a machine group category:');

        reset($set);
        foreach ($set as $key => $row) {
            $id  = $row['mcatid'];
            $cat = $row['category'];
            $rad = radio('tid', $id, $tid);
            echo "${in}${rad}${cat}<br>\n";
        }
        echo next_cancel();
        echo form_footer();
    }
    echo again($env);
}


/*
    |  Create a new group configuration.
    |  Select amoung groups which we control
    |  100%, but do not have a configuration yet.
    */

function export_cbld(&$env, $db)
{
    debug_note('export_cbld');
    $auth = $env['auth'];
    $gcfg = 'GroupConfig';
    $alen = 'GroupAliens';
    build_unconfig_table($gcfg, $db);
    build_alien_table($alen, $auth, $db);
    $sql = "select C.* from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
        . " $gcfg as X,\n"
        . " $alen as A,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as B\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M\n"
        . " on M.censusuniq = B.censusuniq\n"
        . " and M.mgroupuniq = G.mgroupuniq\n"
        . " where G.mcatuniq = C.mcatuniq\n"
        . " and B.id = A.id\n"
        . " and G.mgroupid = X.id\n"
        . " and M.censusuniq is NULL\n"
        . " group by C.mcatid\n"
        . " order by C.precedence";
    $set = find_many($sql, $db);
    CWIZ_drop_temp_table($alen, $db);
    CWIZ_drop_temp_table($gcfg, $db);

    $num = safe_count($set);
    debug_note("final set $num");
    choose_cat($env, $set, 'gbld', $db);
}


/*
    |  Select a new donor group.
    |  Select amoung groups where we control
    |  at least one machine, and already have
    |  a configuration.
    */

function export_ngrp(&$env, $db)
{
    debug_note('export_ngrp');
    $auth = $env['auth'];
    $gcfg = 'GroupConfig';
    $accs = 'GroupAccess';
    build_config_table($gcfg, $db);
    build_access_table($accs, $auth, $db);

    $sql = "select C.* from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
        . " $gcfg as X,\n"
        . " $accs as A\n"
        . " where G.mcatuniq = C.mcatuniq\n"
        . " and G.mgroupid = X.id\n"
        . " and G.mgroupid = A.id\n"
        . " group by C.mcatid\n"
        . " order by C.precedence";
    $set = find_many($sql, $db);
    CWIZ_drop_temp_table($gcfg, $db);
    CWIZ_drop_temp_table($accs, $db);

    $num = safe_count($set);
    debug_note("final set $num");
    choose_cat($env, $set, 'nbld', $db);
}

/*
    |  Select a new target category.
    |
    |  Select amoung groups where we control
    |  at 100% and already have a configuration.
    */

function export_mgrp(&$env, $db)
{
    debug_note('export_mgrp');
    $auth = $env['auth'];
    $gcfg = 'GroupConfig';
    $alen = 'GroupAliens';
    build_config_table($gcfg, $db);
    build_alien_table($alen, $auth, $db);

    $sql = "select C.* from\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
        . " $gcfg as X,\n"
        . " $alen as A,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as B\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M\n"
        . " on M.censusuniq = B.censusuniq\n"
        . " and M.mgroupuniq = G.mgroupuniq\n"
        . " where G.mcatuniq = C.mcatuniq\n"
        . " and B.id = A.id\n"
        . " and G.mgroupid = X.id\n"
        . " and M.censusuniq is NULL\n"
        . " group by C.mcatid\n"
        . " order by C.precedence";
    $set = find_many($sql, $db);
    CWIZ_drop_temp_table($alen, $db);
    CWIZ_drop_temp_table($gcfg, $db);

    $num = safe_count($set);
    debug_note("final set $num");

    return_to_sites();
    choose_cat($env, $set, 'ecat', $db);
    return_to_sites();
}

function sequence(&$set)
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


/*
    |  Adds all the "missing" semaphore records
    |  after creating or updating a new group.
    |
    |  The new records are added for machines which
    |  are a member of the group, and already have
    |  appropriate ValueMap records for the variable
    |  in question.
    */

function add_semclears(&$env, $gid, $db)
{
    $ins = 0;
    $now = $env['now'];
    $set = array();
    if ($gid) {
        /* Since we are dealing with semaphores, we can use VarValues.valu
                directly. */
        $sem = constVblTypeSemaphore;
        $sql = "select C.id as censusid, V.varid, X.valu from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as G,\n"
            . " Variables as V,\n"
            . " VarValues as X,\n"
            . " ValueMap as M,\n"
            . " MachineGroups as G,\n"
            . " Census as C\n"
            . " left join SemClears as S\n"
            . " on S.varuniq = X.varuniq\n"
            . " and S.mgroupuniq = X.mgroupuniq\n"
            . " and S.censusuniq = M.censusuniq\n"
            . " where V.itype = $sem\n"
            . " and X.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid = $gid\n"
            . " and M.censusuniq = G.censusuniq\n"
            . " and M.varuniq = X.varuniq\n"
            . " and X.varuniq = V.varuniq\n"
            . " and M.censusuniq = C.censusuniq\n"
            . " and S.semid is NULL\n"
            . " group by X.varuniq, M.censusuniq\n"
            . " order by X.varuniq, M.censusuniq";
        $set = find_many($sql, $db);
    }
    if ($set) {
        $num = safe_count($set);
        debug_note("There seem to be $num missing records.");

        reset($set);
        foreach ($set as $key => $row) {
            $val = $row['valu'];
            $vid = $row['varid'];
            $hid = $row['censusid'];
            $sql = GCFG_CreateSemClears($hid, $gid, $vid, $val, 1, $now);
            $res = onecommand($sql, 0, $db);
            if (affected($res, $db)) {
                //                  debug_note("v:$vid,h:$hid,s:$val");
                $ins++;
                $lastid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
                $err = PHP_DSYN_InvalidateRow(
                    CUR,
                    (int)$lastid,
                    "semid",
                    constDataSetGConfigSemClears,
                    constOperationInsert
                );
                if ($err != constAppNoErr) {
                    logs::log(__FILE__, __LINE__, "add_semclears: PHP_DSYN_InvalidateRow "
                        . "returned " . $err, 0);
                }
            }
        }
        debug_note("Added $ins new semaphore records.");
    }
    return $ins;
}


/*
    |  Check for aliens ... these are machines which
    |  belong to this machine group, but we have no
    |  acess to and should not be allowed to modify.
    */

function mgrp_alien($gid, $auth, $db)
{
    $qu  = safe_addslashes($auth);
    $sql = "select X.* from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as X,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M,\n"
        . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
        . " left join " . $GLOBALS['PREFIX'] . "core.Customers as C\n"
        . " on C.username = '$qu'\n"
        . " and C.customer = X.site\n"
        . " where M.mgroupuniq = G.mgroupuniq\n"
        . " and G.mgroupid = $gid\n"
        . " and M.censusuniq = X.censusuniq\n"
        . " and C.id is NULL";
    return find_many($sql, $db);
}



function export_gcfg(&$env, $db)
{
    echo again($env);
    $ins = 0;
    $set = array();
    $src = array();
    $bad = array();
    $gid = $env['gid'];
    $hid = $env['hid'];
    $tid = $env['tid'];

    $auth = $env['auth'];
    $priv = $env['priv'];
    $self = $env['self'];
    $admn = $env['admn'];

    echo describe_donor($env);
    if ((!$admn) && ($gid)) {
        $bad = mgrp_alien($gid, $auth, $db);
    }
    $dst = load_vars($gid, $db);
    if ($dst) {
        $num = safe_count($dst);
        $href = "$self?act=ecat&hid=$hid&tid=$tid&gid_$gid=1";
        $edit = html_link($href, 'edit');
        echo para("This machine group already has a configuration, with <b>$num</b> values.");
        echo para("Would you like to $edit this configuration?");
    }
    if (($hid) && ($gid)) {
        $sql = "select V.name, V.scop, V.itype, X.valueid, V.varid,"
            . " G.mgroupid, X.valu, X.revl, X.def, X.revldef,"
            . " X.clientconf, X.revlclientconf, X.last, X.host from\n"
            . " " . $GLOBALS['PREFIX'] . "core.VarValues as X,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Variables as V,\n"
            . " " . $GLOBALS['PREFIX'] . "core.ValueMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
            . " where M.censusuniq = C.censusuniq\n"
            . " and C.id = $hid\n"
            . " and X.mgroupuniq = M.mgroupuniq\n"
            . " and X.mgroupuniq = G.mgroupuniq\n"
            . " and X.varuniq = M.varuniq\n"
            . " and X.varuniq = V.varuniq\n"
            . " group by X.varuniq";
        $set = find_many($sql, $db);
    }
    if ($set) {
        $num = safe_count($set);
        $src = sequence($set);
        $set = array();
        echo para("The donor machine countains <b>$num</b> values.");
    }
    if (($gid) && ($src) && (!$dst) && (!$bad)) {
        $sem = constVblTypeSemaphore;
        $now = $env['now'];

        reset($src);
        foreach ($src as $vid => $row) {
            $dvid = @intval($dst[$vid]['varid']);
            $valu = normalize($row['valu']);
            $type = $row['itype'];
            $host = $row['host'];
            $qh   = safe_addslashes($host);
            $qv   = ($type == $sem) ? '0' : safe_addslashes($valu);

            $sql = GCFG_CreateVarValues(
                $gid,
                $vid,
                $qv,
                $qh,
                2,
                $now,
                1,
                0,
                0,
                $db
            );
            $res = command($sql, $db);
            if (affected($res, $db)) {
                $lastid = ((is_null($___mysqli_res = mysqli_insert_id($db))) ? false : $___mysqli_res);
                $err = PHP_DSYN_InvalidateRow(
                    CUR,
                    (int)$lastid,
                    "valueid",
                    constDataSetGConfigVarValues,
                    constOperationInsert
                );
                if ($err != constAppNoErr) {
                    logs::log(__FILE__, __LINE__, "export_gcfg: PHP_DSYN_InvalidateRow "
                        . "returned " . $err, 0);
                }
                $ins++;
            }
        }
        $sems = add_semclears($env, $gid, $db);
        $text = "There were a total of <b>$ins</b> values and <b>$sems</b> semaphores.";
        echo para($text);
    }
    if ($bad) {
        echo para('This group contains machines you do not own.');
    }
    if (($priv) && ($gid) && ($hid)) {
        $self = $env['self'];
        $href = "$self?act=kill&gid=$gid&hid=$hid";
        $kill = html_link($href, 'Kill this group.');
        echo para($kill);
    }
    echo again($env);
}


function find_machines_using($gid, $db)
{
    $set = array();
    if ($gid) {
        $sql = "select H.id, H.host, H.site\n"
            . " from ValueMap as M,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as H,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G\n"
            . " where M.mgroupuniq = G.mgroupuniq\n"
            . " and G.mgroupid = $gid\n"
            . " and M.censusuniq = H.censusuniq\n"
            . " group by H.id\n"
            . " order by H.site, H.host";
        $set = find_many($sql, $db);
    }
    return $set;
}

function export_kill(&$env, $db)
{
    $gid  = $env['gid'];
    $priv = $env['priv'];
    $set  = find_machines_using($gid, $db);
    if (($gid) && ($priv) && (!$set)) {
        $sql = "select valueid from " . $GLOBALS['PREFIX'] . "core.VarValues left join "
            . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq="
            . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid=$gid";
        $set = DSYN_DeleteSet(
            $sql,
            constDataSetGConfigVarValues,
            "valueid",
            "export_kill",
            0,
            1,
            constOperationPermanentDelete,
            $db
        );

        $set2 = array();

        if ($set) {
            $sql = "select semid from " . $GLOBALS['PREFIX'] . "core.SemClears left join "
                . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.SemClears.mgroupuniq="
                . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid=$gid";
            $set2 = DSYN_DeleteSet(
                $sql,
                constDataSetGConfigSemClears,
                "semid",
                "export_kill",
                1,
                1,
                constOperationPermanentDelete,
                $db
            );
        }

        if ($set && $set2) {
            $sql = "delete from " . $GLOBALS['PREFIX'] . "core.VarValues using " . $GLOBALS['PREFIX'] . "core.VarValues left "
                . "join " . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.VarValues.mgroupuniq="
                . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid = $gid";
            $res = redcommand($sql, $db);
            $val = affected($res, $db);
            $sql = "delete from " . $GLOBALS['PREFIX'] . "core.SemClears using " . $GLOBALS['PREFIX'] . "core.SemClears left "
                . "join " . $GLOBALS['PREFIX'] . "core.MachineGroups on (" . $GLOBALS['PREFIX'] . "core.SemClears.mgroupuniq="
                . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq) where mgroupid = $gid";
            $res = redcommand($sql, $db);
            $sem = affected($res, $db);
            echo para("Removed $val values and $sem semaphores.");
        }
    }
    if (($gid) && ($priv) && ($set)) {
        $num = safe_count($set);
        echo para("This group is used by $num machines.");
    }
    export_menu($env, $db);
}


/*
    |  The group we are creating a configuration for
    |  should contain no alien machines, not already
    |  have a configuration, and be a member of the
    |  specified category.
    */

function export_gbld(&$env, $db)
{
    $set  = array();
    $tid  = $env['tid'];
    $hid  = $env['hid'];
    $auth = $env['auth'];
    $revl = $env['revl'];
    $gcfg = 'GroupConfig';
    $alen = 'GroupAliens';
    if ($revl) {
        echo describe_donor($env);
    }
    if (($tid) && ($hid) && ($auth)) {
        build_alien_table($alen, $auth, $db);
        build_unconfig_table($gcfg, $db);
        $sql = "select G.mgroupid, G.name from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " $alen as A,\n"
            . " $gcfg as C,\n"
            . " " . $GLOBALS['PREFIX'] . "core.Census as B,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as D\n"
            . " left join " . $GLOBALS['PREFIX'] . "core.MachineGroupMap as M\n"
            . " on M.censusuniq = B.censusuniq\n"
            . " and M.mgroupuniq = G.mgroupuniq\n"
            . " where G.mcatuniq = D.mcatuniq\n"
            . " and D.mcatid = $tid\n"
            . " and B.id = A.id\n"
            . " and G.mgroupid = C.id\n"
            . " and M.mgroupuniq is NULL\n"
            . " group by G.mgroupid\n"
            . " order by G.name";
        $set = find_many($sql, $db);
        CWIZ_drop_temp_table($gcfg, $db);
        CWIZ_drop_temp_table($alen, $db);
    }
    if ($set) {
        echo again($env);
        $cid = $env['cid'];
        $gid = $env['gid'];
        $in  = indent(5);

        if (!$gid) {
            $gid = $set[0]['mgroupid'];
        }

        echo post_self('myform');
        echo hidden('cid', $cid);
        echo hidden('hid', $hid);
        echo hidden('tid', $tid);
        echo hidden('act', 'gcfg');

        echo para('Create a configuration for which machine group:');

        reset($set);
        foreach ($set as $key => $row) {
            $id  = $row['mgroupid'];
            $grp = $row['name'];
            $rad = radio('gid', $id, $gid);
            echo "${in}${rad}${grp}<br>\n";
        }
        echo next_cancel();
        echo form_footer();
        echo again($env);
    } else {
        echo para('No appropriate machine groups found.');
        export_menu($env, $db);
    }
}


/*
    |  A valid donor group must contain at least
    |  one machine we own, and already have a
    |  configuration.
    */

function export_nbld(&$env, $db)
{
    echo again($env);
    $set  = array();
    $tid  = $env['tid'];
    $hid  = $env['hid'];
    $xid  = $env['xid'];
    $auth = $env['auth'];
    $revl = $env['revl'];
    if ($revl) {
        echo describe_donor($env);
    }
    if (($tid) && ($hid) && ($auth)) {
        $gcfg = 'GroupConfig';
        $accs = 'GroupAccess';
        build_config_table($gcfg, $db);
        build_access_table($accs, $auth, $db);
        $sql = "select G.* from\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineGroups as G,\n"
            . " $gcfg as X,\n"
            . " $accs as A,\n"
            . " " . $GLOBALS['PREFIX'] . "core.MachineCategories as C\n"
            . " where G.mcatuniq = C.mcatuniq\n"
            . " and C.mcatid = $tid\n"
            . " and G.mgroupid = X.id\n"
            . " and G.mgroupid = A.id\n"
            . " group by G.mgroupid\n"
            . " order by G.name";
        $set = find_many($sql, $db);
        CWIZ_drop_temp_table($gcfg, $db);
        CWIZ_drop_temp_table($accs, $db);
    }
    if ($set) {
        $cid = $env['cid'];
        $gid = $env['gid'];
        $in  = indent(5);

        if (!$gid) {
            $gid = $set[0]['mgroupid'];
        }

        echo post_self('myform');
        echo hidden('hid', $hid);
        echo hidden('act', 'nset');

        echo para('Choose a donor machine group:');

        reset($set);
        foreach ($set as $key => $row) {
            $id  = $row['mgroupid'];
            $grp = $row['name'];
            $rad = radio('xid', $id, $xid);
            echo "${in}${rad}${grp}<br>\n";
        }
        echo next_cancel();
        echo form_footer();
    } else {
        echo para('No appropriate machine groups found.');
    }
    echo again($env);
}




/*
    |  Main program
    */

$now = time();
$cid = get_integer('cid', 0);
$hid = get_integer('hid', 0);
$sid = get_integer('sid', 0);
$tid = get_integer('tid', 0);
$xid = get_integer('xid', 0);
$gid = get_integer('gid', 0);
$ctl = get_integer('ctl', 0);
$dbg = get_integer('debug', 1);
$act = get_string('act', 'menu');

$vers = '';
$host = '';
$site = '';

$db   = db_connect();
$auth = process_login($db);
$comp = component_installed();
$post = get_string('button', 'none');

$user = user_data($auth, $db);
$priv = @($user['priv_debug']) ?  1 : 0;
$admn = @($user['priv_admin']) ?  1 : 0;
$pcfg = @($user['priv_config']) ? 1 : 0;
$filt = @($user['filtersites']) ? 1 : 0;
$ecat = get_integer('ecat', 0);
$debug = ($priv) ? $dbg : 0;

$revl = array();
$good = mysqli_select_db($db, core);
if ($good) {
    if ($xid) {
        $revl = mgrp_revl($xid, $auth, $db);
    }
    if (($hid) && (!$revl)) {
        $xid  = 0;
        $revl = full_revl($hid, $auth, $db);
    }
    if (($cid) && (!$revl)) {
        $revl = site_revl($cid, $auth, $db);
    }
    if ($revl) {
        $host = $revl['host'];
        $vers = $revl['vers'];
        $site = $revl['site'];
        $cid  = $revl['cid'];
        $hid  = $revl['censusid'];
    }
}

if ((!$pcfg) || (!$revl)) {
    debug_note('no priv or no target');
    $good = false;
}

if ($post == constCancel) {
    $act = 'menu';
}

if ($post == constExport) {
    if (($act == 'site') || ($act == 'ecat')) {
        $act = 'done';
    }
}

if (($act == 'ecat') && (!$tid)) {
    $act = 'mgrp';
}
if (($act == 'gbld') && (!$tid)) {
    $act = 'cgrp';
}
if (($act == 'nset') && (!$xid)) {
    $act = 'ngrp';
}

if (!$good) {
    debug_note('not good');
    $act = 'none';
}

$env = array();
$env['act']  = $act;
$env['hid']  = $hid;
$env['cid']  = $cid;
$env['gid']  = $gid;
$env['tid']  = $tid;
$env['xid']  = $xid;
$env['ctl']  = $ctl;
$env['now']  = $now;
$env['ecat'] = $ecat;
$env['auth'] = $auth;
$env['revl'] = $revl;
$env['host'] = $host;
$env['site'] = $site;
$env['cnfg'] = $pcfg;
$env['priv'] = $priv;
$env['post'] = $post;
$env['filt'] = $filt;
$env['admn'] = $admn;
$env['dbug'] = $debug;
$env['self'] = server_var('PHP_SELF');
$env['args'] = server_var('QUERY_STRING');
$local_nav = config_navigate($cid, $hid, $sid);
$local_inf = config_info($auth, $vers, $host);

$name = title($env);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($name, $comp, $auth, $local_nav, $local_inf, 0, $db);

if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

debug_note(datestring($now));

if ($debug) {
    //      debug_array($_POST);
    //      debug_array($_GET);
}
switch ($act) {
    case 'nset':;
    case 'menu':
        export_menu($env, $db);
        break;
    case 'mcat':
        export_mcat($env, $db);
        break;
    case 'site':
        export_site($env, $db);
        break;
    case 'done':
        export_done($env, $db);
        break;
    case 'cbld':
        export_cbld($env, $db);
        break;
    case 'gbld':
        export_gbld($env, $db);
        break;
    case 'gcfg':
        export_gcfg($env, $db);
        break;
    case 'ecat':
        export_ecat($env, $db);
        break;
    case 'ngrp':
        export_ngrp($env, $db);
        break;
    case 'nbld':
        export_nbld($env, $db);
        break;
    case 'mgrp':
        export_mgrp($env, $db);
        break;
    case 'kill':
        export_kill($env, $db);
        break;
    case 'none':
        export_none($env, $db);
        break;
    default:
        export_none($env, $db);
        break;
}
echo head_standard_html_footer($auth, $db);
