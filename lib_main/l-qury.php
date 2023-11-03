<?php





define('constAssetExec',         0);
define('constCronCreateFileXML', 2);

function debug_array($prompt, $name, $data)
{
    $debug = @intval($GLOBALS['debug']);
    if ($debug) {
        echo "<font color=\"green\">\n";
        echo "<pre>\n$prompt:$name\n";
        if ($data)
            print_r($data);
        else
            echo '(empty)';
        echo "</pre>\n";
        echo "</font>\n";
    }
}





function server_link(&$env, $name, $command)
{
    $link = $env['link'];
    $href = $command;
    if ($env['cron']) {
        if ($env['file']) {
            $odir = $env['odir'];
            if ($odir) {
                $href = "/$odir/asset/$command";
            }
        } else {
            $base = $env['base'];
            if ($base) {
                $href = "$base/asset/$command";
            }
        }
    }

    $text = ($env['link']) ? html_page($href, $name) : $name;
    debug_note("server_link:$text");
    return $text;
}


function machine_link(&$env, $mid)
{
    $name = $env['hosts'][$mid]['host'];
    return server_link($env, $name, "detail.php?mid=$mid");
}

function did_link(&$env, $did)
{
    $name = $env['names'][$did]['name'];
    return server_link($env, $name, "detail.php?did=$did");
}

function sort_link(&$env, $did, $qid)
{
    $adhocgrp = '';
    if (array_key_exists('adhocgrp', $env)) {
        $grpinc = $env['adhocgrp'];
        if ($grpinc != '') {
            $adhocgrp = '&adhocgrp=' . $grpinc;
        }
    }
    $name = $env['names'][$did]['name'];
    return server_link($env, $name, "exec.php?qid=$qid&o1=$did$adhocgrp");
}

function show_description($text)
{
    return "\n<small><font color=\"gray\">\n<p>$text</p>\n</font></small>\n\n";
}

function show_when($when)
{
    $time = datestring($when);
    return "\n<small><font color=\"blue\">\n<p>$time</p>\n</font></small>\n\n";
}

function show_miss(&$env, $miss)
{
    $msg = '';
    if ($miss) {
        reset($miss);
        foreach ($miss as $k => $did) {
            $name = $env['names'][$did]['name'];
            $msg .= "No information found:$name<br>";
        }
        $msg = "\n<small><font color=\"blue\">\n<p>$msg</p></font></small>\n\n";
    }
    return $msg;
}



function is_number($s)
{
    if (!isset($s))
        return false;
    if (is_integer($s))
        return true;
    if (is_string($s)) {
        if (($s == '-0') || ($s == '-'))
            return false;
        $n = strlen($s);
        if ($n < 1)
            return false;
        $j = 0;
        if ($s[$j] == '-') $j++;
        for ($i = $j; $i < $n; $i++) {
            if (($s[$i] < '0') || ($s[$i] > '9')) {
                return false;
            }
        }
        return true;
    }
    return false;
}




function query_mids(&$env, $qid, $when, $terms, $qname, $own)
{
    $db   = $env['db'];
    $tbl  = $env['SelectedAssetDataTableName'];
    if (!$tbl) {
        $tbl = create_pidtable_name('SelectedAssetData', $db);
        $env['SelectedAssetDataTableName'] = $tbl;
    }
    $aux_temp_tables = array();

    $mids = $own;

    if (($terms) && ($own)) {
        $dids = array();
        $gids = array();
        $date = datestring($when);
        reset($terms);
        foreach ($terms as $k => $data) {
            $did = $data['did'];
            $gid = $data['gid'];
            $blk = $data['blk'];
            $dids[$did] = $did;
            $gids[$gid][$did] = $did;
        }

        $sql = "select distinct m.machineid from Machine as m";



        reset($dids);
        foreach ($dids as $did => $d) {
            $tmp_tbl = QURY_build_aux_temp_table($tbl, $did, $db);
            if ($tmp_tbl) {
                $sql .= ",\n $tmp_tbl as ad$did";
                $aux_temp_tables[] = $tmp_tbl;
            }
        }


        $sql .= " where\n";



        $mlist = implode(',', $own);
        $sql .= " m.machineid in ($mlist)\n";



        reset($dids);
        foreach ($dids as $did => $d) {
            if ($when) {
                $sql .= " and -- $date\n";
                $sql .= " ((ad$did.cearliest <= $when and\n";
                $sql .= " $when <= ad$did.clatest)\n";
                $sql .= " or\n";
                $sql .= " ($when > m.clatest and\n";
                $sql .= " ad$did.clatest = m.clatest))\n";
            } else {
                $sql .= " and ad$did.clatest = m.clatest\n";
            }
        }



        reset($dids);
        foreach ($dids as $did => $d) {
            $sql .= " and ad$did.machineid = m.machineid\n";
        }



        reset($dids);
        foreach ($dids as $did => $d) {
            $name = $env['names'][$did]['name'];
            $sql .= " and (ad$did.dataid = $did) -- $name\n";
        }



        reset($gids);
        foreach ($gids as $gid => $gdid) {
            if (($gid) && (safe_count($gdid) > 1)) {
                reset($gdid);
                $d = current($gdid);
                $did = next($gdid);
                foreach ($gdid as $k => $did) {
                    $sql .= " and ad$did.ordinal = ad$d.ordinal\n";
                }
            }
        }



        $query = query_tree($env, $terms);
        $sql  .= " and ($query)";
        $res   = redcommand($sql, $db);
        $mids  = array();
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $mids[] = $row['machineid'];
            }
            mysqli_free_result($res);
        }
        if ($aux_temp_tables) {
            QURY_remove_aux_temp_table($aux_temp_tables, $db);
        }
    } else {
        $adhoc = 0;
        if (array_key_exists('adhoc', $env)) {
            if ($env['adhoc'] == 1) {
                $adhoc = 1;
            }
        }
        if ($adhoc == 0) {
            echo empty_terms($qname);
        }
    }

    return $mids;
}


function query_tree(&$env, $terms)
{
    $query  = '';
    $blocks = array();
    foreach ($terms as $k => $data) {
        $blk = $data['blk'];
        $blocks[$blk][] = $data;
    }

    $o = array();
    foreach ($blocks as $blk => $list) {
        $a = array();
        reset($list);
        foreach ($list as $k => $data) {
            $a[] = query_term($env, $data);
        }

        $o[] = $a;
    }

    $tmp = array();
    reset($o);
    foreach ($o as $n => $a) {
        $query = $a[0];
        if (safe_count($a) > 1) {
            reset($a);
            foreach ($a as $key => $data) {
                $a[$key] = "($data)";
            }
            $query = implode(' and ', $a);
        }
        $tmp[] = $query;
    }

    if (safe_count($tmp) > 1) {
        reset($tmp);
        foreach ($tmp as $key => $data) {
            $tmp[$key] = "($data)";
        }
        $query = implode(' or ', $tmp);
    }

    return $query;
}


function query_entry(&$env, $elem, $gmx)
{
    $msg  = '<br>';
    if (($elem) && (is_array($elem))) {
        if ($gmx <= 1) {
            foreach ($elem as $key => $data) {
                $val = trim($data);
                $msg = ($val == '') ? '<br>' : $data;
            }
        } else {
            $tmp = '';
            for ($i = 1; $i <= $gmx; $i++) {
                $val = @trim(strval($elem[$i]));
                if ($val != '') {
                    $row  = array($i, $val);
                    $tmp .= asset_data($row);
                }
            }
            if ($tmp) {
                $msg  = table_header();
                $msg .= $tmp;
                $msg .= '</table>';
            }
        }
    }
    return $msg;
}


function asset_date_code($dc, $dv, $now)
{
    $when = 0;
    $dc = intval($dc);
    $dv = intval($dv);
    if ((0 <= $dc) && ($dc <= 8)) {
        switch ($dc) {
            case 0:
                $when = $dv;
                break;
            case 1:
                $when = 0;
                break;
            case 2:
                $when = days_ago($now, 1);
                break;
            case 3:
                $when = days_ago($now, $dv);
                break;
            case 4:
                $when = days_ago($now, 7);
                break;
            case 5:
                $when = months_ago($now, 1);
                break;
            case 6:
                $when = months_ago($now, 3);
                break;
            case 7:
                $when = months_ago($now, 6);
                break;
            case 8:
                $when = months_ago($now, 12);
                break;
        }
    }
    return $when;
}

function return_all_mids(&$env, $user, $db)
{
    if ($env['cron']) {
        if (isset($env['siteaccesstree'])) {
            $access = $env['siteaccesstree'][$user];
            $sites = db_access($access);
        } else {
            $sites = $env['access'][$user];
        }
    } else {
        $sites = $env['access'][$user];
    }

    $own  = asset_access($sites, $db);
    $site = $env['site'];
    if ($site) {
        $tmp  = asset_site($site, $db);
        $own  = intersect($tmp, $own);
    }
    return $own;
}




function query_query(&$env, $user, $qid, $ords)
{
    $db    = $env['db'];
    $now   = $env['now'];
    $title = @$env['report_title'];
    $mids  = array();
    $dids  = array();
    $crit  = array();
    $name  = '';
    $text  = '';
    $when  = 0;
    $exp   = 0;
    $adhoc = 0;
    if (array_key_exists('adhoc', $env)) {
        if ($env['adhoc'] == 1) {
            $adhoc = 1;
        }
    }

    $sql  = "select * from AssetSearches where id = $qid";
    $srch = find_one($sql, $db);
    if ($srch) {
        $name = @$srch['name'];
        $df   = @$srch['displayfields'];
        $dc   = @$srch['date_code'];
        $dv   = @$srch['date_value'];
        $text = @$srch['searchstring'];
        $exp  = @$srch['expires'];
        $when = asset_date_code($dc, $dv, $now);
        debug_note("displayfields: $df");
        $dids = query_dids($env, $df, $ords);
        $crit = query_criteria($env, $qid);

        $tbl = $env['SelectedAssetDataTableName'];
        if (!$tbl) {
            $tbl = create_pidtable_name('SelectedAssetData', $db);
            $env['SelectedAssetDataTableName'] = $tbl;
        }

        build_assetdata(constTableTypeTemporary, $tbl, $db);

        $cdid = calculate_crit_dids($crit);


        $own = return_all_mids($env, $user, $db);

        if ($env['cron']) {

            $g_include_mid = ($env['group_include']) ?
                GRPS_find_machineid_from_mgroupid(
                    $env['group_include'],
                    $db
                ) : false;

            $g_exclude_mid = ($env['group_exclude']) ?
                GRPS_find_machineid_from_mgroupid(
                    $env['group_exclude'],
                    $db
                ) : false;
            $ownGroup = GRPS_find_common_from_machineid(
                $g_include_mid,
                $g_exclude_mid
            );
            $own = array_intersect($own, $ownGroup);
        }
        if ($adhoc == 1) {

            $g_include_mid = $env['adhocgrp'] ?
                GRPS_find_machineid_from_mgroupid(
                    $env['adhocgrp'],
                    $db
                ) : false;
            $ownGroup = GRPS_find_common_from_machineid(
                $g_include_mid,
                false
            );
            $own = array_intersect($own, $ownGroup);
        }

        populate_SelectedAssetData_table(
            $user,
            $title,
            $tbl,
            $own,
            $dids,
            $cdid,
            $db
        );

        $mids = query_mids($env, $qid, $when, $crit, $name, $own);
    } else {
        echo message("Query $qid does not exist.");
    }

    $q = array();
    $q['qid']  = $qid;
    $q['exp']  = $exp;
    $q['ords'] = $ords;
    $q['dids'] = $dids;
    $q['mids'] = $mids;
    $q['text'] = $text;
    $q['name'] = $name;
    $q['when'] = $when;
    $q['crit'] = $crit;
    $q['cdid'] = $cdid;
    $q['gids'] = array();
    $q['miss'] = array();
    return $q;
}



function query_dids(&$env, $disp, $ords)
{
    $db   = $env['db'];
    $dids = array();
    $list = array();
    if ($ords) {
        reset($ords);
        foreach ($ords as $xxx => $ord) {
            if ($ord > 0) {
                $list[$ord] = true;
            }
        }
    }
    if ($disp) {
        $p = explode(':', $disp);
        reset($p);
        foreach ($p as $key => $field) {
            if ($field) {
                $did = find_did($field, $db);
                if ($did) {
                    debug_note("did:$did&nbsp;&nbsp;($field)");
                    $list[$did] = true;
                }
            }
        }
    }

    reset($list);
    foreach ($list as $did => $xxx) {
        $dids[] = $did;
    }
    return $dids;
}



function query_criteria(&$env, $qid)
{
    $db  = $env['db'];
    $sql = "select * from AssetSearchCriteria where assetsearchid = $qid";
    $res = command($sql, $db);
    $cmd = array();
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $fld = $row['fieldname'];
            $did = find_did($fld, $db);
            if ($did) {
                $tmp = array();
                $tmp['did'] = $did;
                $tmp['val'] = $row['value'];
                $tmp['blk'] = $row['block'];
                $tmp['cmp'] = $row['comparison'];
                $tmp['gid'] = $env['names'][$did]['groups'];
                $cmd[] = $tmp;
            }
        }
        mysqli_free_result($res);
    }
    $n = safe_count($cmd);
    debug_note("$n criteria found for $qid");
    return $cmd;
}




function query_term(&$env, $term)
{
    $compare = array(
        1 => '=',
        2 => '!=',
        3 => 'like',               4 => 'like',               5 => 'like',               6 => '<',
        7 => '>',
        8 => '<=',
        9 => '>=',
        10 => 'not like'
    );
    $did  = $term['did'];
    $val  = $term['val'];
    $cmp  = $term['cmp'];
    $sql  = '';

    if (($did) && (1 <= $cmp) && ($cmp <= 10)) {
        if ($val == '')
            $value = "''";
        else {
            $val = safe_addslashes($val);
            if ((3 <= $cmp) && ($cmp <= 5) || ($cmp == 10)) {
                $prefix = '';
                $suffix = '';

                if (($cmp == 3) || ($cmp == 10) || ($cmp == 5)) $prefix = '%';
                if (($cmp == 3) || ($cmp == 10) || ($cmp == 4)) $suffix = '%';
                $val = $prefix . $val . $suffix;
            }
            $value = (is_number($val)) ? $val : "'$val'";
        }
        $op  = $compare[$cmp];
        $sql = "ad$did.value $op $value";
    }
    return $sql;
}

function query_draw(&$env, $q, $tree)
{
    $name  = $q['name'];
    $text  = $q['text'];
    $when  = $q['when'];
    $gmax  = $q['gmax'];
    $mids  = $q['mids'];
    $dids  = $q['dids'];
    $miss  = $q['miss'];
    $anum  = $q['anum'];
    $qid   = $q['qid'];
    $tab   = $env['tab'];
    $msg   = '';
    $adhoc = 0;
    if (array_key_exists('adhoc', $env)) {
        if ($env['adhoc'] == 1) {
            $adhoc = 1;
        }
    }

    if (($tree) && ($dids) && ($mids)) {
        if ($adhoc == 0) {
            $msg = asset_header($name);
        }

        if ($text) {
            $msg .= show_description($text);
        }

        if ($when) {
            $msg .= show_when($when);
        }

        if ($miss) {
            $msg .= show_miss($env, $miss);
        }

        if ($anum) {
            $nms  = safe_count($mids);
            $msg .= show_description("Query found $anum records from $nms machines.");
        }

        if ($tab) {
            reset($tree);
            foreach ($tree as $index => $data) {
                $msg .= table_header();
                $mid  = $data[0];
                $host = $env['hosts'][$mid]['host'];
                $link = machine_link($env, $mid);
                $msg .= $link;
                $msg .= pretty_header($host, 2);

                reset($dids);
                foreach ($dids as $key => $did) {
                    $elem  = @$data[$did];
                    $gid   = @intval($env['names'][$did]['groups']);
                    $gmx   = @intval($gmax[$mid][$gid]);
                    $value = query_entry($env, $elem, $gmx);
                    $name  = $env['names'][$did]['name'];
                    if ($value != '<br>') {
                        $msg  .= double($name, $value);
                    }
                }
                $msg .= table_footer();
            }
        } else {
            $msg .= table_header();
            $row  = array('<br>');
            reset($dids);
            foreach ($dids as $key => $did) {
                $row[] = sort_link($env, $did, $qid);
            }

            $msg .= asset_data($row);
            reset($tree);
            foreach ($tree as $index => $data) {
                $mid  = $data[0];
                $link = machine_link($env, $mid);

                $row = array($link);
                reset($dids);
                foreach ($dids as $key => $did) {
                    $elem  = @$data[$did];
                    $gid   = @intval($env['names'][$did]['groups']);
                    $gmx   = @intval($gmax[$mid][$gid]);
                    $row[] = query_entry($env, $elem, $gmx);
                }
                $msg .= asset_data($row);
            }
            $msg .= table_footer();
        }
    }
    return $msg;
}



function compare_flat($a, $b)
{
    $ords = @$GLOBALS['ords'];
    if ((isset($ords)) && (is_array($ords))) {
        reset($ords);
        foreach ($ords as $key => $did) {
            if ($did > 0) {
                $aa = @strval($a[$did][1]);
                $bb = @strval($b[$did][1]);
                if ($aa != $bb) {
                    $res = strnatcasecmp($bb, $aa);
                    return $res;
                }
            }
        }
    }
    return 0;
}


function asset_ords(&$env, $ords, $dids)
{
    $temp = array();
    if ($ords) {
        debug_array('asset_ords 1', 'ords', $ords);
        reset($ords);
        foreach ($ords as $key => $did) {
            if ($did > 0) {
                $name = @$env['names'][$did]['name'];
                if ($name) {
                    $temp[] = $did;
                }
            }
        }
    }
    $ords = $temp;



    $kdid = array();
    $temp = array();

    if ($dids) {
        reset($ords);
        reset($dids);
        foreach ($ords as $k => $did) $kdid[$did] = false;
        foreach ($dids as $k => $did) $kdid[$did] = true;

        reset($ords);
        foreach ($ords as $key => $did) {
            if ($kdid[$did]) $temp[] = $did;
        }
    }
    while (safe_count($temp) < 4) {
        $temp[] = 0;
    }
    debug_array('asset_ords 2', 'ords', $temp);
    return $temp;
}






function asset_sort(&$env, $q, $ords)
{
    $dids = $q['dids'];
    if (($ords) && ($dids)) {
        debug_array('asset_sort 1', 'dids', $dids);



        $kdid = array();
        reset($ords);
        reset($dids);
        foreach ($ords as $key => $did) $kdid[$did] = true;
        foreach ($dids as $key => $did) $kdid[$did] = true;

        $dids = array();
        reset($kdid);
        foreach ($kdid as $did => $xxx) {
            if ($did > 0)
                $dids[] = $did;
        }

        debug_array('asset_sort 2', 'dids', $dids);



        $tree = $q['tree'];
        $GLOBALS['ords'] = $ords;
        usort($tree, 'compare_flat');
        $q['tree'] = $tree;
        $q['dids'] = $dids;
    }
    $q['ords'] = $ords;
    return $q;
}




function asset_flat(&$env, $q, $ords)
{
    $dids = $q['dids'];
    $mids = $q['mids'];
    $gids = $q['gids'];
    $gmax = $q['gmax'];
    $flat = $q['tree'];

    $kgid = array();
    $grps = array();
    $news = array();

    reset($ords);
    foreach ($ords as $key => $ord) {
        if ($ord > 0) {
            $gid = $env['names'][$ord]['groups'];
            if ($gid > 0) {
                $kgid[$gid] = true;
            }
        }
    }

    reset($kgid);
    foreach ($kgid as $gid => $xxx) {
        $grps[] = $gid;
    }



    reset($grps);
    foreach ($grps as $k => $grp) {
        $news = array();
        $kill = array();

        reset($flat);
        $i = 0;
        foreach ($flat as $ind => $row) {
            $mid = $row[0];
            $gmx = @$gmax[$mid][$grp];
            if ($gmx > 1) {
                $name = $env['names'][$grp]['name'];
                $host = $env['hosts'][$mid]['host'];
                debug_note("machine ($mid:$host) group ($grp:$name) adding $gmx rows");
                for ($ord = 1; $ord <= $gmx; $ord++) {
                    $new = $row;
                    $good = false;
                    reset($dids);
                    foreach ($dids as $d => $did) {
                        if ($gids[$did] == $grp) {
                            unset($new[$did]);
                            $val = @$row[$did][$ord];
                            if ($val != '') {
                                debug_note("machine ($mid:$host), ord:$ord, new[$did] = ($val)");
                                $new[$did][1] = $val;
                                $good = true;
                            }
                        }
                    }
                    if ($good) {
                        $news[] = $new;
                    }
                }
                $kill[] = $ind;
            }
        }

        reset($news);
        foreach ($news as $i => $row) {
            debug_note("adding new row $i");
            $flat[] = $row;
        }

        reset($kill);
        foreach ($kill as $k => $ind) {
            debug_note("removing old row $ind");
            unset($flat[$ind]);
        }
        reset($mids);
        foreach ($mids as $key => $mid) {
            $gmax[$mid][$grp] = 1;
        }
    }

    $q['ords'] = $ords;
    $q['tree'] = $flat;
    $q['gmax'] = $gmax;
    return $q;
}




function query_data(&$env, $q)
{
    $tree = $q['tree'];
    $dids = $q['dids'];
    $ords = $q['ords'];

    $ords = asset_ords($env, $ords, $dids);
    if ($ords[0] > 0) {
        $q = asset_flat($env, $q, $ords);
        $q = asset_sort($env, $q, $ords);
        $tree = $q['tree'];
    }

    $msg = query_draw($env, $q, $tree);

    $q['html'] = $msg;
    $q['tree'] = $tree;
    return $q;
}





function query_build(&$env, $q, $d1, $d2)
{
    $temp = array();
    $kmid = array();
    $kdid = array();
    $gmax = array();
    $mids = array();
    $gids = array();
    $miss = array();

    $dids = $q['dids'];
    $ords = $q['ords'];
    if ($dids) {
        reset($dids);
        foreach ($dids as $key => $did) {
            $gid = @intval($env['names'][$did]['groups']);
            $gids[$did] = $gid;
            $kdid[$did] = false;
        }
    }

    $anum = safe_count($d1) + safe_count($d2);
    $x = array($d1, $d2);

    reset($x);
    foreach ($x as $k => $asset) {
        reset($asset);
        foreach ($asset as $key => $data) {
            $did = @intval($data['dataid']);
            $mid = @intval($data['machineid']);
            $ord = @intval($data['ordinal']);
            $gid = @intval($gids[$did]);
            $val = @strval($data['value']);
            $gmx = @intval($gmax[$mid][$gid]);
            if ($val != '') {
                $temp[$mid][$did][$ord] = $val;
                $kdid[$did] = true;
                $kmid[$mid] = true;
                if ($ord > $gmx)
                    $gmax[$mid][$gid] = $ord;
            }
        }
    }

    $miss = array();
    $dids = array();
    $mids = array();

    if ($kdid) {
        reset($kdid);
        foreach ($kdid as $did => $found) {
            if ($found)
                $dids[] = $did;
            else
                $miss[] = $did;
        }
    }

    if ($kmid) {
        reset($kmid);
        foreach ($kmid as $mid => $xxx) {
            $mids[] = $mid;
        }
    }




    if (($temp) && ($mids)) {
        reset($mids);
        foreach ($mids as $key => $mid) {
            $row = $temp[$mid];
            $row[0] = $mid;
            $tree[] = $row;
        }
        unset($temp);
    }

    $ords = asset_ords($env, $ords, $dids);

    $q['ords'] = $ords;
    $q['tree'] = $tree;
    $q['mids'] = $mids;
    $q['dids'] = $dids;
    $q['gids'] = $gids;
    $q['gmax'] = $gmax;
    $q['miss'] = $miss;
    $q['anum'] = $anum;
    return $q;
}




function unconstrained($tbl, $mids, $dids, $when)
{
    $sql = '';
    if (($mids) && ($dids)) {
        $dlist = implode(',', $dids);
        $mlist = implode(',', $mids);
        $date  = datestring($when);

        $sql  = "select a.machineid, a.dataid, a.ordinal, a.value from\n";
        $sql .= " Machine as m,\n $tbl as a where\n";
        $sql .= " (m.machineid in ($mlist))\n";
        if ($when) {
            $sql .= " and -- $date\n";
            $sql .= " (($when <= a.clatest and\n";
            $sql .= " a.cearliest <= $when)\n";
            $sql .= " or\n";
            $sql .= " ($when > m.clatest and\n";
            $sql .= " a.clatest = m.clatest))\n";
        } else
            $sql .= " and (a.clatest = m.clatest)\n";
        $sql .= " and (a.machineid = m.machineid)\n";
        $sql .= " and (a.dataid in ($dlist))\n";
        $sql .= " order by m.host";
    }
    return $sql;
}



function QURY_build_aux_temp_table($tbl, $did, $db)
{

    $t_t_name = 'SelectedAssetData' . $did;
    $tmp_tbl  = create_pidtable_name($t_t_name, $db);

    $res = build_assetdata(constTableTypeTemporary, $tmp_tbl, $db);
    if ($res == -1) {
        $txt = 'l-qury.php: QURY_build_aux_temp_table failed to build'
            . " table($tmp_tbl)";
        return false;
    }
    $sql = "insert ignore into $tmp_tbl\n"
        . " select * from $tbl";
    redcommand($sql, $db);

    return $tmp_tbl;
}






function query_command(&$env, $q)
{
    debug_note("query_command()");
    $tbl    = $env['SelectedAssetDataTableName'];
    $db     = $env['db'];
    $dids   = $q['dids'];
    $mids   = $q['mids'];
    $when   = $q['when'];
    $terms  = $q['crit'];
    $sql    = '';
    $sql1   = '';
    $sql2   = '';


    if (($mids) && ($dids)) {
        $date  = datestring($when);
        $dlist = implode(',', $dids);
        $mlist = implode(',', $mids);

        $udid = array();
        $qdid = array();
        $mdid = array();
        $kdid = array();
        $kgid = array();
        $gids = array();
        $displayed = array();
        $involved  = array();
        $members   = array();
        $sibling   = array();
        $aux_temp_tables = array();

        reset($dids);
        foreach ($dids as $k => $did) {
            $gid = $env['names'][$did]['groups'];
            $involved[$did]  = false;
            $displayed[$did] = true;
            $members[$gid]   = false;
            $sibling[$gid]   = 0;
        }

        if ($terms) {
            reset($terms);
            foreach ($terms as $k => $data) {
                $did = $data['did'];
                $gid = $data['gid'];
                $gids[$gid][$did] = $did;
                $involved[$did]   = true;
                $members[$gid]    = true;
                $displayed[$did]  = false;
                $sibling[$gid]    = $did;
            }
        }

        $members[0] = false;
        $sibling[0] = 0;
        reset($dids);
        foreach ($dids as $k => $did) {
            $displayed[$did] = true;
        }

        if ($involved) {
            reset($involved);
            foreach ($involved as $did => $data) {
                if ($data)
                    $qdid[] = $did;
                else {
                    $gid = $env['names'][$did]['groups'];
                    if ($members[$gid])
                        $mdid[] = $did;
                    else
                        $udid[] = $did;
                }
            }
        }

        if ($qdid) {
            $count = 0;

            reset($qdid);
            foreach ($qdid as $k => $did) {
                if ($displayed[$did])
                    $count++;
            }
            reset($mdid);
            foreach ($mdid as $k => $did) {
                if ($displayed[$did])
                    $count++;
            }
            if ($count == 0) {
                $qdid = array();
                $mdid = array();
            }
        }




        if ($qdid) {
            $tab = array();
            $tab[] = "Machine as m";
            $tab[] = "$tbl as a";

            reset($qdid);
            foreach ($qdid as $k => $did) {
                $tmp_tbl = QURY_build_aux_temp_table($tbl, $did, $db);
                if ($tmp_tbl) {
                    $tab[] = "$tmp_tbl as ad$did";
                    $aux_temp_tables[] = $tmp_tbl;
                }
            }

            $tabs  = implode(",\n ", $tab);
            $sql   = "select\n a.value, a.dataid, a.machineid, a.ordinal"
                . " from\n $tabs\n";


            reset($mdid);
            foreach ($mdid as $k => $did) {
                $tmp_tbl = QURY_build_aux_temp_table($tbl, $did, $db);
                if ($tmp_tbl) {
                    $aux_temp_tables[] = $tmp_tbl;
                }
            }

            reset($mdid);
            foreach ($mdid as $k => $did) {
                $gid  = $env['names'][$did]['groups'];
                $nam  = $env['names'][$did]['name'];
                $sib  = $sibling[$gid];
                $sql .= "\n left join $tbl$did as ad$did\n";
                $sql .= " on  (ad$did.machineid = m.machineid)\n";
                $sql .= " and (ad$did.dataid = $did) -- $nam\n";
                if ($when) {
                    $sql .= " and ((ad$did.cearliest <= $when and\n";
                    $sql .= " $when <= ad$did.clatest)\n";
                    $sql .= " or -- $date\n";
                    $sql .= " ($when > m.clatest and\n";
                    $sql .= " ad$did.clatest = m.clatest))\n";
                } else {
                    $sql .= " and (ad$did.clatest = m.clatest)\n";
                }
                if (($sib > 0) && ($sib != $did)) {
                    $sql .= " and (ad$did.ordinal = ad$sib.ordinal)\n";
                }
            }

            $sql .= " where\n m.machineid in ($mlist)\n";

            reset($qdid);
            foreach ($qdid as $k => $did) {
                $nam = $env['names'][$did]['name'];
                if ($when) {
                    $sql .= " and -- $date\n";
                    $sql .= " ((ad$did.cearliest <= $when and\n";
                    $sql .= " $when <= ad$did.clatest)\n";
                    $sql .= " or\n";
                    $sql .= " ($when > m.clatest and\n";
                    $sql .= " ad$did.clatest = m.clatest))\n";
                } else {
                    $sql .= " and ad$did.clatest = m.clatest\n";
                }
                $sql .= " and ad$did.dataid = $did -- $nam\n";
                $sql .= " and ad$did.machineid = m.machineid\n";
            }




            reset($gids);
            foreach ($gids as $gid => $gdids) {
                if (($gid) && (safe_count($gdids) > 1)) {
                    reset($gdids);
                    $d = current($gdids);
                    $did = next($gdids);
                    foreach ($gdids as $did => $data) {
                        $sql .= " and ad$d.ordinal = ad$did.ordinal\n";
                    }
                }
            }

            $tmp = array();
            reset($qdid);
            foreach ($qdid as $k => $did) {
                if ($displayed[$did])
                    $tmp[] = "(a.id = ad$did.id)";
            }
            reset($mdid);
            foreach ($mdid as $k => $did) {
                if ($displayed[$did])
                    $tmp[] = "(a.id = ad$did.id)";
            }
            if ($tmp) {
                $display = implode(' or ', $tmp);
                $sql .= " and ($display)\n";

                $query = query_tree($env, $terms);
                $sql  .= " and ($query)";
            } else
                $sql = '';
        }

        $sql2 = $sql;

        if ($udid) {
            if ($qdid)
                $sql1 = unconstrained($tbl, $mids, $udid, $when);
            else
                $sql1 = unconstrained($tbl, $mids, $dids, $when);
        }
    }


    $temp = array();
    $temp['sql1'] = $sql1;
    $temp['sql2'] = $sql2;
    $temp['aux_temp_tables'] = $aux_temp_tables;
    return $temp;
}



function QURY_remove_aux_temp_table($aux_temp_tables, $db)
{
    reset($aux_temp_tables);
    foreach ($aux_temp_tables as $key => $t_n) {
        QURY_drop_temp_table($t_n, $db);
    }
}


function asset_time_query($sql, $slow, $dbid, $db)
{
    $msec = 0;
    $res  = redcommand_time($sql, $msec, $db);
    if ($msec > $slow) {
        $pid  = getmypid();
        $rows = ($res) ? mysqli_num_rows($res) : 0;
        $secs = microtime_show($msec);
        $stat = "p:$pid,n:$rows,d:$dbid";
        $text = "assets: slow ($stat) in $secs\n$sql";
        debug_note("slow ($stat,$slow) in $secs");
    }
    return $res;
}


function find_slow($sql, $slow, $dbid, $db)
{
    $temp = array();
    $res  = asset_time_query($sql, $slow, $dbid, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $temp[] = $row;
        }
        mysqli_free_result($res);
    }
    return $temp;
}

function asset_walk_arrange(&$env, $set)
{
    $txt    = '';
    $eol    = "\n";
    $data   = array();
    $header = array();
    $out    = array();
    $header['Site']    = 0;
    $header['Machine'] = 1;
    $next_header       = 2;
    $ord               = 0;

    if (isset($set['mids'])) {
        $mid_set = $set['mids'];
        if (!$mid_set) {
            $set = array();
        }
    }
    if ($set) {
        foreach ($set as $key => $row) {
            $did  = '';
            $mid  = '';
            $valu = '';
            $name = '';
            $host = '';
            $site = '';
            $ord  = '';

            if (isset($row['dataid']))
                $did  = $row['dataid'];

            if (isset($row['machineid']))
                $mid  = $row['machineid'];

            if (isset($row['value']))
                $valu = $row['value'];

            if (isset($env['names'][$did]['name']))
                $name = $env['names'][$did]['name'];

            if (isset($env['hosts'][$mid]['host']))
                $host = $env['hosts'][$mid]['host'];

            if (isset($env['hosts'][$mid]['cust']))
                $site = $env['hosts'][$mid]['cust'];

            if (isset($row['ordinal']))
                $ord  = $row['ordinal'];

            $txt .= $eol;
            if (isset($header[$name])) {
                $col = $header[$name];
            } else {
                $header[$name] = $next_header;
                $col = $next_header;
                $next_header++;
            }
            if (!isset($data[$mid][$ord])) {
                $data[$mid][$ord][0] = $site;
                $data[$mid][$ord][1] = $host;
            }
            if (!isset($data[$mid][$ord][$col])) {
                $data[$mid][$ord][$col] = $valu;
            }
        }
    }
    reset($header);
    reset($data);
    $out['header'] = $header;
    $out['data']   = $data;
    return $out;
}



function create_pidtable_name($tbl, $db)
{
    $tmp = 'temp_';
    $pid = getmypid() . '_';
    $tbl = $tmp . $pid . $tbl;
    QURY_drop_temp_table($tbl, $db);

    return $tbl;
}

function QURY_drop_temp_table($tbl, $db)
{
    $sql = "drop table if exists $tbl";
    redcommand($sql, $db);
}


function create_SelectedAssetData_table($tbl, $db)
{
    $sql = "CREATE TABLE $tbl (\n"
        . " id int(11) NOT NULL auto_increment,\n"
        . " machineid int(11) NOT NULL default '0',\n"
        . " dataid int(11) NOT NULL default '0',\n"
        . " value varchar(255) NOT NULL,\n"
        . " ordinal int(11) NOT NULL default '0',\n"
        . " cearliest int(11) NOT NULL default '0',\n"
        . " cobserved int(11) NOT NULL default '0',\n"
        . " clatest int(11) NOT NULL default '0',\n"
        . " searliest int(11) NOT NULL default '0',\n"
        . " sobserved int(11) NOT NULL default '0',\n"
        . " slatest int(11) NOT NULL default '0',\n"
        . " uuid varchar(50) NOT NULL default '',\n"
        . " PRIMARY KEY  (id),\n"
        . " KEY machineid (machineid),\n"
        . " KEY dataid (dataid),\n"
        . " KEY ordinal (ordinal),\n"
        . " KEY slatest (slatest),\n"
        . " KEY clatest (clatest)\n"
        . " )";
    redcommand($sql, $db);
}



function populate_SelectedAssetData_table(
    $user,
    $title,
    $tbl,
    $mids,
    $dids,
    $cdid,
    $db
) {
    reset($mids);
    reset($dids);
    reset($cdid);

    $dids_list = implode(",", $dids);
    $mids_list = implode(",", $mids);

    if ($cdid) {
        $cdid_list = implode(",", $cdid);
        $dids_list = $dids_list . ', ' . $cdid_list;
    }

    if ($mids_list) {
        $sql = "insert ignore into $tbl\n"
            . " select * from AssetData\n"
            . " where machineid in ($mids_list)\n"
            . " and dataid in ($dids_list)";
        redcommand($sql, $db);
    } else {

        $err = "assets: user($user) ran ($title) a zero machine"
            . " asset report";
    }
}



function calculate_crit_dids(&$crit)
{
    $tmp = array();
    reset($crit);
    if ($crit) {
        foreach ($crit as $key => $set) {
            $tmp[] = $set['did'];
        }
    }
    return $tmp;
}



function show_query(&$env, $user, $qid, $ords, $type)
{
    $db    = $env['db'];
    $slow  = $env['slow'];
    $dbid  = $env['dbid'];

    $msg   = '';

    debug_note("show_query: qid:$qid");

    $tbl = create_pidtable_name('SelectedAssetData', $db);
    $env['SelectedAssetDataTableName'] = $tbl;

    $q = query_query($env, $user, $qid, $ords);
    $q['ords'] = $ords;
    $q['tree'] = array();
    $q['gmax'] = array();
    $q['anum'] = 0;

    $dids   = $q['dids'];
    $mids   = $q['mids'];
    $exp    = $q['exp'];
    $cdid   = $q['cdid'];

    if (($mids) && ($dids)) {
        $x = query_command($env, $q);

        $s1 = $x['sql1'];
        $s2 = $x['sql2'];
        $aux_temp_tables = $x['aux_temp_tables'];

        $d1 = ($s1) ? find_slow($s1, $slow, $dbid, $db) : array();
        $d2 = ($s2) ? find_slow($s2, $slow, $dbid, $db) : array();

        if ($aux_temp_tables) {

            QURY_remove_aux_temp_table($aux_temp_tables, $db);
        }

        if ($type == constCronCreateFileXML) {
            return array_merge($d1, $d2);
        }
        if (($d1) || ($d2)) {
            $q = query_build($env, $q, $d1, $d2);
        } else {
            $msg = empty_data();
        }
    } else {
        if ($dids)
            $msg = empty_mids();
        else
            $msg = empty_dids();
    }

    $q['html'] = $msg;
    return $q;
}
