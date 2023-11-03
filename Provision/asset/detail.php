<?php

/*
Revision history:

Date        Who     What
----        ---     ----
23-Sep-02   EWB     Creation
25-Sep-02   EWB     Don't show columns with no data.
26-Sep-02   EWB     Mixed tree and table view.
30-Sep-02   EWB     Fixed a problem with the links.
30-Sep-02   EWB     Show Group, Show Asset, Show Data
 1-Oct-02   EWB     Factored out lib-alib.
 2-Oct-02   EWB     Prototype query code
 3-Oct-02   EWB     Subtables in query display result.
 4-Oct-02   EWB     Real Asset Queries
 6-Oct-02   EWB     Asset Queries work.
 7-Oct-02   EWB     Better error checking.
 7-Oct-02   EWB     a-detail split into a-detail and a-query.
 9-Oct-02   EWB     Enable display of entire AssetData record
10-Oct-02   EWB     free from register_globals.
16-Oct-02   EWB     Fixed a bug where it was not reporting a value of 0.
24-Oct-02   EWB     Show group tables in correct order.
 4-Nov-02   EWB     Allow group/ord query.
 5-Nov-02   EWB     Allow gid/ord/when query.
 5-Nov-02   EWB     Allow did/ord and did/ord/when query.
 5-Nov-02   EWB     Added change link to machine description.
14-Nov-02   EWB     Handle empty value in show_asset()
15-Nov-02   EWB     Remove leading/trailing spaces from date field.
 2-Dec-02   EWB     Fixed missing table header.
 4-Dec-02   EWB     Reorginization Day
10-Dec-02   EWB     Local Navigation
07-Jan-03   AAM     Performance fix:  don't copy arrays.
 7-Feb-03   EWB     New database scheme.
10-Feb-03   EWB     Uses sandbox libraries.
11-Feb-03   EWB     db_change.
14-Feb-03   EWB     Fixed an access bug.
 6-Mar-03   NL      Uses PHP auth'n, output buffering, standard_html_header()
10-Mar-03   NL      Added "../lib" back in so code uses sandbox libraries.
10-Mar-03   NL      Passed 0 as $legend to standard_html_header()
19-Mar-03   NL      Move debug_note line below $debug.
 3-Apr-03   EWB     Further debug control.
24-Apr-03   EWB     Do that sitefilter thing.
29-Apr-03   EWB     Clean up access lists.
30-Apr-03   EWB     User site filter list.
 8-May-03   EWB     Debug ordinal selection.
 9-May-03   EWB     show_asset() reports number of matching records.
22-May-03   EWB     Quote Crusade.
30-May-03   EWB     Sort by value, etc.
30-May-03   EWB     Find siblings for group member.
30-May-03   EWB     Show mid, did, gid in verbose display.
17-Jun-03   EWB     Slave Database
20-Jun-03   EWB     No Slave Database.
 6-Oct-03   AAM     Fixed problem where linked items were getting indented
                    slightly too far.
 6-Oct-03   EWB     Ok, but allow newlines at the end.
 4-Mar-04   EWB     Raise memory limit for huge machines.
25-Mar-04   EWB     Navigation links.
12-Apr-04   EWB     renamed console link to sites.
24-Jan-06   AAM     Bug 3072: change memory_limit setting to use max_php_mem_mb.
                    (Note that this change was actually moved from 4.2 to 4.3
                    on 06-Mar-06.)
09-Mar-06   AAM     Bug 2924: move the work of the link from the event detail
                    page into here so that it only gets done when someone
                    actually clicks on the link.
19-Sep-06   WOH     Changed name of standard_footer.  Also added username arg.                    

*/

$title  = 'Asset Detail';

ob_start(); // avoid indavertantly sending output (before HTTP Headers sent)
include('../lib/l-util.php');
include('../lib/l-db.php');
include('../lib/l-sql.php');
include('../lib/l-serv.php');
include('../lib/l-rcmd.php');
include('../lib/l-head.php');
include('../lib/l-sets.php');
include('../lib/l-gsql.php');
include('../lib/l-user.php');
include('../lib/l-alib.php');
include('../lib/l-dids.php');
include('../lib/l-qtbl.php');
include('../lib/l-slct.php');
include('../lib/l-date.php');
include('../lib/l-jump.php');
//  include ( '../lib/l-slav.php'  );
include('local.php');



function again($env)
{
    $priv = $env['dpriv'];
    $a   = array();
    $a[] = html_link('#top', 'top');
    $a[] = html_link('#bottom', 'bottom');
    $a[] = html_link('console.php', 'sites');
    if ($priv) {
        $self = $env['self'];
        $args = $env['args'];
        $href = ($args) ? "$self?$args" : $self;
        $home = '../acct/index.php';
        $a[] = html_link($href, 'again');
        $a[] = html_link('census.php', 'census');
        $a[] = html_link($home, 'home');
    }
    return jumplist($a);
}

function pretty_header($name, $width)
{
    return <<< HERE
<tr>
    <th colspan="$width" bgcolor="#333399">
        <font color="white">
            $name
        </font>
    </th>
</tr>

HERE;
}



// http://www.php.net/manual/en/language.references.pass.php

function machine_link(&$env, $mid)
{
    $name = $env['hosts'][$mid]['host'];
    $self = $env['self'];
    $href = "$self?mid=$mid";
    return html_link($href, $name);
}


function did_link(&$env, $did)
{
    $name = $env['names'][$did]['name'];
    $self = $env['self'];
    $href = "$self?did=$did";
    return html_link($href, $name);
}


function table_back($n, $mid, $gid, $name)
{
    $self = server_var('PHP_SELF');
    $href = "$self?mid=$mid&gid=$gid";
    $link = html_link($href, $name);
    return "<tr><td colspan=\"$n\" align=\"center\">$link</td><tr>\n";
}


function table_span($value, $span)
{
    return "<tr><th colspan=\"$span\">$value</th></tr>\n";
}

function table_head($args)
{
    $m = '';
    if ($args) {
        $m .= "<tr>\n";
        reset($args);
        foreach ($args as $key => $data) {
            $m .= " <th>$data</th>\n";
        }
        $m .= "</tr>\n";
    }
    return $m;
}

function table_filter($s)
{
    $s = trim($s);
    return ($s == '') ? '<br>' : htmlspecialchars($s);
}


function comma($txt)
{
    return explode(',', $txt);
}


function indent($n)
{
    $s = '';
    for ($i = 0; $i < $n; $i++) {
        $s .= '&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    return $s;
}



function show_leaf(&$env, $did, $depth)
{
    $mid   = $env['mid'];
    $when  = $env['when'];
    $db    = $env['db'];

    $name  = $env['names'][$did]['name'];
    $smax  = $env['hosts'][$mid]['slatest'];

    $sql  = "select * from AssetData\n";
    $sql .= " where machineid = $mid and\n";
    if (($smax <= $when) || ($when == 0))
        $sql .= " $smax <= slatest and\n";
    else {
        $sql .= " searliest <= $when and\n";
        $sql .= " $when <= slatest and\n";
    }
    $sql .= " dataid = $did";
    $data = find_one($sql, $db);

    $valu = @strval($data['value']);

    if ($valu != '')    // 0 counts
    {
        $d   = indent($depth);
        $msg = fontspeak($valu);
        echo "$d<span class=\"blue\">$name: </span>$msg<br>\n";
    }
}


function draw_tree(&$env, $did, $depth)
{
    $db     = $env['db'];
    $child  = asset_children($did, $db);
    $table  = asset_members($did, $db);
    if ($child) {
        $d = indent($depth);
        $name = $env['names'][$did]['name'];
        if ($table) {
            $msg  = fontspeak($name);
            $mark = mark_nocrlf("link_$did");
            if (group_found($env, $did))
                $link = html_link("#table_$did", $msg);
            else
                $link = "<span class=\"faded\">$msg</span>";
            echo "$d$mark$link<br>\n";
        } else {
            echo "$d<span class=\"faded\">$name</span><br>\n";
            foreach ($child as $key => $data) {
                draw_tree($env, $data, $depth + 1);
            }
        }
    } else {
        show_leaf($env, $did, $depth);
    }
}


/*
    |  Note ... this is a debug table.
    |
    */

function show_names(&$names)
{
    if ($names) {
        echo table_start();

        $head = comma('did,name,ord,gid,pid,set');

        echo table_head($head);

        reset($names);
        foreach ($names as $key => $data) {
            $row   = array($data['dataid']);
            $row[] = $data['name'];
            $row[] = $data['ordinal'];
            $row[] = $data['groups'];
            $row[] = $data['parent'];
            $row[] = $data['setbyclient'];

            echo asset_data($row);
        }
        echo table_end();
    }
}


function table_mark($gid)
{
    $msg  = mark("table_$gid");
    $msg .= "\n<br clear=\"all\">\n\n";
    return $msg;
}


function group_data(&$env, $gid, $asset)
{
    $mid   = $env['mid'];
    $self  = $env['self'];
    $db    = $env['db'];

    $table = array();
    $kdid  = array();
    $dids  = array();
    $sibs  = asset_members($gid, $db);

    reset($sibs);
    foreach ($sibs as $k => $did) {
        $kdid[$did] = false;
    }
    $name = $env['names'][$gid]['name'];

    $msg = table_mark($gid);

    $href = "#link_$gid";
    $link = html_link($href, $name);
    $msg .= asset_header($link);

    if ($asset) {
        reset($asset);
        foreach ($asset as $key => $data) {
            $ord = @intval($data['ordinal']);
            $did = @intval($data['dataid']);
            $val = @strval($data['value']);
            if ($val != '') {
                $table[$ord][$did] = $val;
                $kdid[$did] = true;
            }
        }

        /*
            |  We want to show the columns in
            |  the same order they show up in
            |  in the tree.
            */

        reset($sibs);
        foreach ($sibs as $k => $did) {
            if ($kdid[$did])
                $dids[] = $did;
        }

        if ($dids) {
            $row = array('<br>');
            reset($dids);
            foreach ($dids as $key => $did) {
                $text  = $env['names'][$did]['name'];
                $href  = "$self?mid=$mid&did=$did";
                $row[] = html_link($href, $text);
            }
            $msg .= table_start();
            $n = safe_count($row);
            $msg .= table_back($n, $mid, $gid, $name);
            $msg .= asset_data($row);
            foreach ($table as $ord => $data) {
                $row = array($ord);
                reset($dids);
                foreach ($dids as $key => $did) {
                    $row[] = @table_filter($table[$ord][$did]);
                }
                $msg .= asset_data($row);
            }
            $msg .= table_end();
        }
    }
    return $msg;
}


function clear_rows()
{
    $clr = '<br clear="all">';
    echo "$clr\n$clr\n\n";
}


function group_found(&$env, $gid)
{
    $db    = $env['db'];
    $mid   = $env['mid'];
    $when  = $env['when'];
    $found = 0;

    $list  = asset_members($gid, $db);
    if ($list) {
        $smax = $env['hosts'][$mid]['slatest'];
        $name = $env['names'][$gid]['name'];
        $mlist = implode(',', $list);
        $sql  = "select count(*) from AssetData\n";
        $sql .= " where machineid = $mid and\n";
        if (($smax <= $when) || ($when == 0))
            $sql .= " $smax <= slatest and\n";
        else {
            $sql .= " searliest <= $when and\n";
            $sql .= " $when <= slatest and\n";
        }
        $sql .= " dataid in ($mlist)";
        $found = count_records($sql, $db);
        debug_note("group $name -- $found found");
    }
    return $found;
}

function show_group(&$env, $gid, $list)
{
    $db    = $env['db'];
    $mid   = $env['mid'];
    $when  = $env['when'];

    debug_note("show group $gid");

    if ($list) {
        $smax  = $env['hosts'][$mid]['slatest'];
        $mlist = implode(',', $list);
        $sql  = "select * from AssetData\n";
        $sql .= " where machineid = $mid and\n";
        if (($smax <= $when) || ($when == 0))
            $sql .= " $smax <= slatest and\n";
        else {
            $sql .= " searliest <= $when and\n";
            $sql .= " $when <= slatest and\n";
        }
        $sql .= " dataid in ($mlist)\n";
        $sql .= " order by ordinal,dataid";
        $data = find_many($sql, $db);

        if ($data) {
            echo group_data($env, $gid, $data);
        } else {
            debug_note("no data for group $gid ($mlist) at time $when");
        }
    }
}

function ldate($utime)
{
    return date('m/d/y H:i:s', $utime);
}

function show_asset(&$env, $res, $name)
{
    $num = mysqli_num_rows($res);
    if ($num > 0) {
        $msg = "There were $num matching records found.";
        $msg = fontspeak($msg);
        echo "<p>$msg</p>\n";

        $gif   = $env['gif'];
        $self  = $env['self'];
        $verb  = $env['verb'];
        $gif   = "\n$gif";

        if ($verb)
            $head = comma('<br>,ord,name,val,host,aid,did,gid,mid,min,obs,max,c-min,c-obs,c-max');
        else
            $head = comma('<br>,Ordinal,Name,Value,Machine,Earliest,Latest');
        $cols = safe_count($head);

        echo table_start();
        echo pretty_header($name, $cols);
        echo table_head($head);
        while ($row = mysqli_fetch_array($res)) {
            $list = array();
            $aid  = $row['id'];
            $did  = $row['dataid'];
            $mid  = $row['machineid'];
            $gid  = $env['names'][$did]['groups'];
            $href   = "$self?aid=$aid";
            $list[] = html_link($href, $gif);
            $list[] = $row['ordinal'];
            $list[] = $env['names'][$did]['name'];
            $list[] = table_filter($row['value']);
            $list[] = $env['hosts'][$mid]['host'];
            if ($verb) {
                $list[] = $aid;
                $list[] = $did;
                $list[] = $gid;
                $list[] = $mid;
                $list[] = tdate($row['searliest']);
                $list[] = tdate($row['sobserved']);
                $list[] = tdate($row['slatest']);
                $list[] = tdate($row['cearliest']);
                $list[] = tdate($row['cobserved']);
                $list[] = tdate($row['clatest']);
            } else {
                $list[] = ldate($row['searliest']);
                $list[] = ldate($row['slatest']);
            }
            echo asset_data($list);
        }
        echo table_end();
    } else {
        $msg = "There were no records found matching this query.";
        $msg = fontspeak($msg);
        echo "<p>$msg</p>\n";
    }
}



function show_aid(&$env)
{
    $db    = $env['db'];
    $mid   = $env['mid'];
    $aid   = $env['aid'];
    $cid   = $env['cid'];
    $user  = $env['user'];
    $carr  = $env['carr'];
    $owned = $env['owned'];
    $data  = array();

    if ($mid) {
        $mids = array($mid);
        $mids = intersect($mids, $owned);
    } else {
        $mids = $owned;
    }
    if ($mids) {
        $sml  = implode(",", $mids);
        $sql  = "select * from AssetData\n";
        $sql .= " where machineid in ($sml) and\n";
        $sql .= " id = $aid";
        $data = find_one($sql, $db);
    } else {
        echo empty_mids();
    }
    $dord = 0;
    $dgid = 0;
    $dmid = 0;
    $dobs = 0;
    if ($data) {
        $ddid = $data['dataid'];
        $dmid = $data['machineid'];
        $dval = $data['value'];
        $dord = $data['ordinal'];
        $dobs = $data['sobserved'];
        $name = $env['names'][$ddid]['name'];
        $dgid = $env['names'][$ddid]['groups'];
        if (($cid) || ($mid)) {
            $site = array();
            if ($cid) {
                $cust = @$carr[$cid];
                if ($cust) {
                    $site = asset_site($cust, $db);
                }
            }
            if ($mid) {
                $site = array($mid);
            }
            $site = intersect($site, $owned);
            if (($site) && ($ddid)) {
                $sml  = implode(",", $site);
                $name = $dval;
                $qval = safe_addslashes($dval);
                $sql  = "select * from AssetData\n";
                $sql .= " where machineid in ($sml) and\n";
                $sql .= " dataid = $ddid and\n";
                $sql .= " value = '$qval'";
            }
        }
        $res = redcommand($sql, $db);
        if ($res) {
            show_asset($env, $res, $name);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }

        debug_note("dmid:$dmid, ddid:$ddid, dgid:$dgid, dord:$dord, dobs:$dobs, dval:$dval");

        clear_rows();
        echo table_start();
        echo table_head(array($dval));
        $self = $env['self'];
        if (($ddid) && ($dmid) && ($dobs)) {
            $name = $env['names'][$ddid]['name'];
            $href = "$self?mid=$dmid&did=$ddid&when=$dobs";
            $link = html_link($href, $name);
            echo asset_data(array($link));
        }
        if (($dmid) && ($dgid) && ($dord) && ($dobs)) {
            $name = $env['names'][$dgid]['name'];
            $href = "$self?mid=$dmid&gid=$dgid&ord=$dord&when=$dobs";
            $link = html_link($href, $name);
            echo asset_data(array($link));
        }
        if ($dmid) {
            $host = $env['hosts'][$dmid]['host'];
            $href = "$self?aid=$aid&mid=$dmid";
            $link = html_link($href, $host);
            echo asset_data(array($link));
        }
        reset($carr);
        foreach ($carr as $cid => $site) {
            $href = "$self?aid=$aid&cid=$cid";
            $link = html_link($href, $site);
            echo asset_data(array($link));
        }
        echo table_end();
    } else {
        $msg = "No values found for this search.";
        $msg = fontspeak($msg);
        echo "<p>$msg</p><br>\n";
    }
}




/*
    |  see sort_index
    */

function sort_order($sort)
{
    switch ($sort) {
            //      case  0: default
        case  1:
            $order = 'machineid, ordinal, sobserved, dataid';
            break;
        case  2:
            $order = 'machineid, dataid, sobserved, ordinal';
            break;
        case  3:
            $order = 'dataid, value, sobserved, id';
            break;
        case  4:
            $order = 'id';
            break;
        case  5:
            $order = 'sobserved, ordinal, dataid';
            break;
        case  6:
            $order = 'dataid, ordinal, sobserved';
            break;
        case  7:
            $order = 'machineid, dataid, ordinal, sobserved';
            break;
        case  8:
            $order = 'machineid, dataid, value, sobserved';
            break;
        default:
            $order = 'machineid, sobserved, ordinal, dataid';
            break;
    }
    return $order;
}


/*
    |  see sort_order
    */

function sort_index()
{
    $list = array();
    $list[0] = 'machine, time, ordinal, field';
    $list[1] = 'machine, ordinal, time, field';
    $list[2] = 'machine, ordinal, field, time';
    $list[3] = 'field, value, time';
    $list[4] = 'id';
    $list[5] = 'time, ordinal, field';
    $list[6] = 'field, ordinal, time';
    $list[7] = 'machine, field, ordinal, time';
    $list[8] = 'machine, field, value, time';
    return $list;
}



function show_did(&$env)
{
    $db     = $env['db'];
    $mid    = $env['mid'];
    $did    = $env['did'];
    $ord    = $env['ord'];
    $when   = $env['when'];
    $sort   = $env['sort'];
    $owned  = $env['owned'];

    if ($mid) {
        $mids = array($mid);
        $mids = intersect($mids, $owned);
    } else {
        $mids = $owned;
    }
    if (($mids) && ($did)) {
        $ordr = sort_order($sort);
        $name = $env['names'][$did]['name'];
        $sml  = implode(",", $mids);
        $sql  = "select * from AssetData\n";
        $sql .= " where machineid in ($sml)\n";
        $sql .= " and dataid = $did\n";
        if ($ord)  $sql .= " and ordinal = $ord\n";
        if ($when) $sql .= " and searliest <= $when\n and ($when <= slatest)\n";
        $sql .= " order by $ordr";
        $res  = redcommand($sql, $db);
        if ($res) {
            show_asset($env, $res, $name);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    } else {
        echo empty_mids();
    }
}


function show_gid(&$env)
{
    $db    = $env['db'];
    $mid   = $env['mid'];
    $gid   = $env['gid'];
    $ord   = $env['ord'];
    $when  = $env['when'];
    $sort  = $env['sort'];
    $owned = $env['owned'];

    if ($mid) {
        $mids = array($mid);
        $mids = intersect($mids, $owned);
    } else {
        $mids = $owned;
    }
    $dids = asset_members($gid, $db);
    if (($mids) && ($dids)) {
        $name  = $env['names'][$gid]['name'];
        $ordr  = sort_order($sort);
        $d = implode(',', $dids);
        $m = implode(',', $mids);

        $sql  = "select * from AssetData";
        $sql .= " where machineid in ($m)\n";
        $sql .= " and dataid in ($d)\n";
        if ($ord)  $sql .= " and ordinal = $ord\n";
        if ($when) $sql .= " and searliest <= $when\n and $when <= slatest\n";
        $sql .= " order by $ordr";
        $res  = redcommand($sql, $db);
        if ($res) {
            show_asset($env, $res, $name);
            ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        }
    } else {
        if ($dids)
            echo empty_mids();
        else
            echo empty_dids();
    }
}



function table_entry(&$env, $mid, $did, $table, $gmax)
{
    $gid  = $env['names'][$did]['groups'];
    $msg  = '<br>';
    $elem = @$table[$mid][$did];
    if (($elem) && (is_array($elem))) {
        $n  = safe_count($elem);
        $gm = @$gmax[$mid][$gid];
        if (($n == 1) && ($gm == 1)) {
            foreach ($elem as $key => $data) {
                $msg = $data;
            }
        }

        if (($gm > 1) && ($n > 0)) {
            $msg = '<table border="1" cellspacing="1" cellpadding="3">';
            for ($i = 1; $i <= $gm; $i++) {
                $val = '<br>';
                if (isset($elem[$i]))
                    $val = $elem[$i];
                $msg .= "<tr><td>$i</td><td>$val</td></tr>";
            }
            $msg .= "</table>";
        }
    }
    return $msg;
}


function show_machine(&$env)
{
    $db = $env['db'];
    echo mark('tree');
    echo jumptable('top,bottom,tree,groups');
    draw_tree($env, 0, 0);
    echo mark('groups');
    echo jumptable('top,bottom,tree,groups');
    $groups = asset_groups($db);
    reset($groups);
    foreach ($groups as $key => $gid) {
        $members = asset_members($gid, $db);
        show_group($env, $gid, $members);
    }
    echo jumptable('top,bottom,tree,groups');
}


function count_records($sql, $db)
{
    $num = 0;
    $res = redcommand($sql, $db);
    if ($res) {
        $num = mysqli_result($res, 0);
    }
    return $num;
}

function sort_unique($a)
{
    if ($a) {
        $keys = array();
        reset($a);
        foreach ($a as $key => $data) {
            $keys[$data] = true;
        }
        ksort($keys);
        reset($keys);
        $a = array();
        foreach ($keys as $key => $data) {
            $a[] = $key;
        }
    }
    return $a;
}



function green($text)
{
    return "<font color=\"green\">$text</font>";
}

function row($a, $b)
{
    return asset_data(array($a, $b));
}


function machine_form(&$env)
{
    $db    = $env['db'];
    $mid   = $env['mid'];
    $mach  = $env['mach'];
    $when  = $env['when'];
    $self  = $env['self'];
    $now   = $env['now'];
    $dpriv = $env['dpriv'];
    $owned = $env['owned'];
    $did   = $env['did'];
    $gid   = $env['gid'];
    $ord   = $env['ord'];
    $verb  = $env['verb'];
    $sort  = $env['sort'];
    $debug = $env['debug'];

    $smax  = $mach['slatest'];
    $smin  = $mach['searliest'];
    $host  = $mach['host'];
    $site  = $mach['cust'];

    $sql     = "select count(*)\n from AssetData\n where machineid = $mid";
    $records = count_records($sql, $db);

    $href = "change.php?mid=$mid";
    $head = html_link($href, $host);

    $time = array($smin, $smax);
    $sql  = "select distinct sobserved,slatest\n from AssetData\n where machineid = $mid";
    $res  = redcommand($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_array($res)) {
            $time[] = $row['sobserved'];
            $time[] = $row['slatest'];
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    $times = sort_unique($time);
    $events = safe_count($times);

    $time[] = 0;
    $time[] = $when;
    $times  = sort_unique($time);

    reset($times);
    foreach ($times as $key => $data) {
        if ($data)
            $time_opt[$data] = date("m/d/Y H:i", $data);
        else
            $time_opt[$data] = "last log";
    }

    $machines = array('    ');
    reset($owned);
    foreach ($owned as $xxx => $omid) {
        $machines[$omid] = $env['hosts'][$omid]['host'];
    }
    asort($machines);
    $time_select = html_select('when', $time_opt, $when, 1);
    $mid_select  = html_select('mid', $machines, $mid, 1);


    echo "<form method=\"get\" action=\"$self\">\n";
    if (($mid > 0) && (safe_count($machines) == 1)) {
        echo "<input type='hidden' name='mid' value='$mid'>\n";
    }
    echo table_start();
    echo table_span($head, 2);
    if ($site) {
        echo row('Site', $site);
    }
    echo row('Machine',  $host);
    echo row('First Log', datestring($smin));
    echo row('Last Log', datestring($smax));

    if ($when) {
        echo row('Target', datestring($when));
    }

    if ($did) echo row('Field', $env['names'][$did]['name']);
    if ($gid) echo row('Group', $env['names'][$gid]['name']);

    echo row('Records', $records);
    echo row('Events', $events);
    if (safe_count($machines) > 1) {
        echo row('Select Machine', $mid_select);
    }
    echo row('Select Time', $time_select);

    $date = "&nbsp;&nbsp;<i>(mm/dd/yyyy)</i>";
    $emin = "<input type=\"text\" name=\"date\" size=\"20\">$date";
    echo row('Enter Time', $emin);
    $submit = '<input type="submit" value="submit">';
    $reset  = '<input type="reset" value="reset">';
    echo row($submit, $reset);
    if ($dpriv) {
        $opt      = array('No', 'Yes');
        $deb_select  = html_select('debug', $opt, $debug, 1);
        echo row(green('$debug'), $deb_select);
        $names = &$env['names'];
        $grps  = asset_groups($db);
        if (($names) && ($grps)) {
            $dids = array('    ');
            $gids = array('    ');

            reset($grps);
            foreach ($grps as $key => $xxx) {
                $gids[$xxx] = $names[$xxx]['name'];
            }
            reset($names);
            foreach ($names as $xxx => $data) {
                if (!isset($gids[$xxx]))
                    $dids[$xxx] = $data['name'];
            }
            asort($dids);
            asort($gids);

            $list = sort_index();
            $vrb_select = html_select('verb', $opt, $verb, 1);
            $srt_select = html_select('sort', $list, $sort, 1);
            $did_select = html_select('did', $dids, $did, 1);
            $gid_select = html_select('gid', $gids, $gid, 1);
            $oval = ($ord > 0) ? $ord : '';
            $ord_input  = "<input type='text' name='ord' size='10' value='$oval'>";
            echo row(green('$verb'), $vrb_select);
            echo row(green('$sort'), $srt_select);
            echo row(green('$did'), $did_select);
            echo row(green('$gid'), $gid_select);
            echo row(green('$ord'), $ord_input);
        }
    }
    echo table_end();
    echo "<form>\n";
    clear_rows();
    clear_rows();
}


/*
    |  Main program
    */

$now = time();
$db  = db_connect();

/************************
    $mdb = db_connect();
    $sdb = db_slave($mdb);
    if ($sdb)
    {
        db_change($GLOBALS['PREFIX'].'core',$sdb);
        $db = $sdb;
    }
    else
    {
        $db = $mdb;
    }
 ************************/

$authuser = process_login($db);
$comp = component_installed();
$user = user_data($authuser, $db);

$msg = ob_get_contents();           // save the buffered output so we can...
ob_end_clean();                     // (now dump the buffer)
echo standard_html_header($title, $comp, $authuser, 0, 0, 0, $db);

$mid   = intval(get_argument('mid', 0, 0));
$did   = intval(get_argument('did', 0, 0));
$aid   = intval(get_argument('aid', 0, 0));
$gid   = intval(get_argument('gid', 0, 0));
$cid   = intval(get_argument('cid', 0, 0));
$ord   = intval(get_argument('ord', 0, 0));
$sort  = intval(get_argument('sort', 0, 0));
$when  = intval(get_argument('when', 0, 0));
$date  = trim(get_argument('date', 0, ''));
$dbg   = intval(get_argument('debug', 0, 0));
$vrb   = intval(get_argument('verb', 0, 0));

$self = $comp['self'];
$file = $comp['file'];
$odir = $comp['odir'];
$priv   = @($user['priv_debug']) ?  1 : 0;
$filter = @($user['filtersites']) ? 1 : 0;
$debug = ($priv) ? $dbg : 0;
$verb  = ($priv) ? $vrb : 0;
if (trim($msg)) debug_note($msg);   // ...display any errors to debug users

/************************************************
    if ($sdb)
        debug_note("replicated database, mdb:$mdb, sdb:$sdb");
    else
        debug_note("normal database");
 ************************************************/

$carr   = site_array($authuser, $filter, $db);
$access = db_access($carr);
db_change($GLOBALS['PREFIX'] . 'asset', $db);

$name  = '';
$names = asset_names($db);
$hosts = asset_machines($db);
$owned = asset_access($access, $db);

debug_note("authuser: $authuser, access:$access, filter:$filter");
clear_rows();

/* "machine" and "site" are used for an outside link to asset details.  If
        they are present, we just translate them as if mid was passed in. */
$machine = trim(get_argument('machine', 0, ''));
$site = trim(get_argument('site', 0, ''));
if (($machine != '') && ($site != '')) {
    $qmachine = safe_addslashes($machine);
    $qsite = safe_addslashes($site);
    $sql = "select machineid from Machine where"
        . " cust = '$site' and host = '$machine'";
    $res = redcommand($sql, $db);
    if ($res && mysqli_num_rows($res)) {
        $row = mysqli_fetch_array($res);
        $mid = $row['machineid'];
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
        debug_note("Translated ($site,$machine) to mid=$mid");
    } else {
        echo message("The machine '$machine' at site '$site' has no asset data.");
    }
}

if ($cid) {
    $name = '';
    $name = @$carr[$cid];
    if ($name) {
        debug_note("Asking about customer $cid, '$name'");
        $temp  = asset_site($name, $db);
        $owned = intersect($temp, $owned);
    } else {
        echo message("Site $cid does not exist.");
        $cid = 0;
    }
}

if ($mid) {
    $name = '';
    $name = @$hosts[$mid]['host'];
    if ($name)
        debug_note("Asking about machine '$name'");
    else {
        echo message("Machine $mid does not exist.");
        $mid = 0;
    }
}

if ($did) {
    $name = '';
    $name = @strval($names[$did]['name']);
    if ($name)
        debug_note("Asking about field $did, '$name'");
    else {
        echo message("Field $did does not exist.");
        $did = 0;
    }
}

if ($gid) {
    $name = @strval($names[$gid]['name']);
    $grup = asset_members($gid, $db);
    if (($name) && ($grup))
        debug_note("Asking about group $gid, '$name'");
    else {
        echo message("Group $gid does not exist.");
        $gid = 0;
    }
}

if ($ord) {
    if (($gid) || ($did))
        debug_note("Asking about ordinal $ord");
    else {
        echo message("No group or dataid specified.");
        $ord = 0;
    }
}

if ($date) {
    $when = parsedate($date, $now);
}


$mach = array();
if (($mid) && ($owned) && ($access)) {
    $sql   = "select * from Machine where\n";
    $sql  .= " machineid = $mid and\n";
    $sql  .= " cust in ($access)";
    $mach  = find_one($sql, $db);
}

$msg = '';
if (!$mach) {
    if ($mid) {
        $msg  = "The specified machine was not found.  Either there is no<br>";
        $msg .= "record of this machine, or you do not own it.";
    }
}

if ($msg) {
    echo "<p>$msg</p>";
}

if (($names) && ($debug)) {
    //      show_names($names);
}


$env = array();
$env['db'] = $db;
$env['mid'] = $mid;
$env['did'] = $did;
$env['aid'] = $aid;
$env['gid'] = $gid;
$env['cid'] = $cid;
$env['ord'] = $ord;
$env['now'] = $now;
$env['gif'] = "<img src=\"/$odir/pub/detail.gif\" width=\"33\" height=\"14\" border=\"0\">";
$env['self'] = $self;
$env['args'] = server_var('QUERY_STRING');
$env['odir'] = $odir;
$env['mach'] = $mach;
$env['carr'] = $carr;
$env['when'] = $when;
$env['sort'] = $sort;
$env['verb'] = $verb;
$env['user'] = $authuser;
$env['dpriv'] = $priv;
$env['names'] = $names;
$env['hosts'] = $hosts;
$env['owned'] = $owned;
$env['debug'] = $debug;

echo again($env);

if ($mach) {
    machine_form($env);
}

if (($names) && ($mach) && ($mid)) {
    if ($aid) {
        show_aid($env);
    }

    if ($did) {
        show_did($env);
    }

    if ($gid) {
        show_gid($env);
    }

    if ((!$aid) && (!$gid) && (!$did)) {
        $names[0]['name'] = strtoupper($mach['host']);
        $env['names'] = $names;
        $mem = server_def('max_php_mem_mb', '256', $db);
        ini_set('memory_limit', $mem . 'M');
        show_machine($env);
    }
} else {
    if ($aid) {
        show_aid($env);
    }

    if ($did) {
        show_did($env);
    }

    if ($gid) {
        show_gid($env);
    }
}


clear_rows();
echo again($env);
echo head_standard_html_footer($authuser, $db);
