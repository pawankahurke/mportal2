<?php


$print_query = false;
$multiple = [];
$machineTableName = '';
$assetTableName = '';



function getTableName($db)
{
    global $machineTableName;
    global $assetTableName;

    $adv_Asset_Sql = "select value from " . $GLOBALS['PREFIX'] . "core.Options where name = 'advanced_asset'";
    $adv_Asset_Sql_Res = find_one($adv_Asset_Sql, $db);
    $adv_Asset_Val = $adv_Asset_Sql_Res['value'];

    if ($adv_Asset_Val == 1) {
        $machineTableName = 'MachineLatest';
        $assetTableName = 'AssetDataLatestTest';
    } else {
        $machineTableName = 'Machine';
        $assetTableName = 'AssetDataLatest';
    }
}


function asset_date_code($dc, $dv)
{
    $dc = intval($dc);
    $dv = intval($dv);
    $now = time();

    switch ($dc) {
        case 1:
            $when = 0;
            break;
        case 2:
            $when = $now - 86400;
            break;
        case 3:
            $when = $now - $dv;
            break;
        case 4:
            $when = $now - 604800;
            break;
        case 5:
            $when = $now - 2592000;
            break;
        case 6:
            $when = $now - 7776000;
            break;
        case 7:
            $when = $now - 15552000;
            break;
        case 8:
            $when = $now - 31104000;
            break;
        default:
            $when = $dv;
            break;
    }

    return $when;
}


function query_condition($term)
{

    $sql = '';
    $val = $term['value'];
    $did = $term['dataid'];
    $cmp = $term['comparison'];

    $dct = array(false, '=', '<>', 'LIKE', 'LIKE', 'LIKE', '<', '>', '<=', '>=', 'NOT LIKE');

    $op = $dct[$cmp];

    if ($op) {

        if (is_numeric($val) && $cmp != 3 && $cmp != 4 && $cmp != 5 && $cmp != 10) {
            $value = $val;
        } else {
            $val = safe_addslashes($val);

            $prefix = ($cmp == 3 || $cmp == 10 || $cmp == 5) ? '%' : '';
            $suffix = ($cmp == 3 || $cmp == 10 || $cmp == 4) ? '%' : '';

            $value = "'$prefix$val$suffix'";
        }

        $sql = " (a.dataid = $did AND a.value $op $value)";
    }

    return $sql;
}


function fetch_search_terms($db, $qid)
{
    $sql = "SELECT d.dataid, c.comparison, c.value, c.block, d.groups, d.ordinal FROM " . $GLOBALS['PREFIX'] . "asset.AssetSearchCriteria AS c INNER JOIN " . $GLOBALS['PREFIX'] . "asset.DataName AS d ON d.name = c.fieldname WHERE c.assetsearchid = $qid";
    $res = mysqli_query($sql, $db);

    $terms = array();

    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            if (isset($row['dataid'])) {
                $terms[] = $row;
            }
        }

        mysqli_free_result($res);
    }

    return $terms;
}


function find_group_pivot($group, $dataids, $terms)
{
    $min = 1000;
    $filter = array();

    foreach ($terms as $term) {
        if ($term['groups'] == $group) {
            if ($term['ordinal'] < $min) {

                if (isset($base)) {
                    $filter[] = $base;
                }

                $min = $term['ordinal'];

                $base = $term['dataid'];
                $vals = array_diff($dataids, [$term['dataid']]);
            } else {
                $filter[] = $term['dataid'];
            }
        }
    }

    return array('base' => $base, 'values' => $vals, 'filter' => $filter);
}


function distinct_machines_full_query($machines, $term, $when)
{
    global $machineTableName;
    $filter = implode(',', $machines);
    $query = "SELECT DISTINCT m.machineid FROM " . $GLOBALS['PREFIX'] . "asset." . $machineTableName . " AS m INNER JOIN " . $GLOBALS['PREFIX'] . "asset.AssetData AS a ON a.machineid = m.machineid";

    if ($when) {
        $query .= " AND ((a.cearliest <= $when AND $when <= a.clatest) OR ($when > m.clatest AND a.clatest = m.clatest))";
    } else {
        $query .= " AND a.clatest = m.clatest";
    }

    $query .= " AND a.machineid IN ($filter)";

    if ($term) {
        $query .= " WHERE $term";
    }

    return $query;
}


function distinct_machines_latest_query($machines, $term)
{
    global $assetTableName;
    $filter = implode(',', $machines);
    $query = "SELECT DISTINCT a.machineid FROM " . $GLOBALS['PREFIX'] . "asset." . $assetTableName . " AS a WHERE a.machineid IN ($filter)";

    if ($term) {
        $query .= " AND $term";
    }

    return $query;
}


function select_distinct_machines($db, $table, $when, $machines, $term)
{
    global $print_query;

    if ($table == "AssetData") {
        $query = distinct_machines_full_query($machines, $term, $when);
    } else {
        $query = distinct_machines_latest_query($machines, $term);
    }

    if ($print_query) {
        echo "<p><small>&bull; $query</small></p>";
    }

    $res = mysqli_query($db, $query);
    $mids = array();

    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $mids[] = $row[0];
        }

        mysqli_free_result($res);
    }

    return $mids;
}


function select_machines($db, $table, $when, $terms, $mids)
{

    $blocks = array();

    foreach ($terms as $term) {
        $blocks[$term['block']][] = query_condition($term);
    }

    if (safe_count($blocks) == 0) {
        return $mids;
    }

    $t1 = array();

    foreach ($blocks as $block) {
        $t2 = array();
        $t3 = $mids;

        foreach ($block as $term) {
            $t3 = select_distinct_machines($db, $table, $when, $t3, $term);
            $t2[] = $t3;
        }

        switch (safe_count($t2)) {
            case 0:
                $t1[] = array();
                break;
            case 1:
                $t1[] = array_pop($t2);
                break;
            default:
                $t1[] = call_user_func_array('array_intersect', $t2);
        }
    }

    return array_unique(call_user_func_array('array_merge', $t1));
}


function group_display_criteria($table, $terms, $groups)
{
    $temp = array();
    foreach ($groups as $group => $pivot) {
        $vals = implode(",", $pivot['values']);
        $temp[] = "(a.dataid = " . $pivot['base'] . " AND b.dataid IN (" . $vals . ") AND a.ordinal = b.ordinal)";
    }

    return " LEFT JOIN " . $GLOBALS['PREFIX'] . "asset.$table AS b ON a.machineid = b.machineid AND (" . implode(" OR ", $temp) . ")";
}


function asset_display_criteria($db, $fields, $terms, $machines)
{
    $list = array();

    $groups = array();

    $conditions = array();

    $ufilter = array();

    $cfilter = array();

    if ($fields) {
        $fields = str_replace(":", "','", substr($fields, 1, -1));

        $query = "SELECT dataid, clientname, groups FROM " . $GLOBALS['PREFIX'] . "asset.DataName WHERE name IN ('$fields') order by ordinal";
        $res = mysqli_query($db, $query);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $list[$row['clientname']] = $row['dataid'];

                if ($row['groups'] > 0) {
                    $groups[$row['groups']][] = $row['dataid'];
                }

                $parent[$row['groups']][] = $row['dataid'];
            }

            mysqli_free_result($res);
        }
    }

    foreach ($groups as $group => $dataids) {
        if (safe_count($dataids) < 2) {
            unset($groups[$group]);
        } else {
            $pivot = find_group_pivot($group, $dataids, $terms);

            if (!isset($pivot['base'])) {
                unset($groups[$group]);
            } else {
                $ufilter = array_merge($ufilter, $dataids);
                $cfilter = array_merge($cfilter, $pivot['filter']);
                $groups[$group] = $pivot;
            }
        }
    }
    foreach ($terms as $term) {
        if (in_array($term['dataid'], $list) && !in_array($term['dataid'], $cfilter)) {
            $conditions[] = query_condition($term);
            $ufilter[] = $term['dataid'];
        }
    }

    $includes = array_diff($list, $ufilter);
    $ccount = safe_count($conditions);
    $icount = safe_count($includes);

    $criteria = " ";

    if ($ccount + $icount > 1) {
        $criteria .= "(";
    }

    if ($ccount > 0) {
        $c = implode(" OR ", $conditions);
        $criteria .= "$c";
    }

    if ($icount > 0) {
        if ($ccount > 0) {
            $criteria .= " OR ";
        }
        $i = implode(",", $includes);
        $criteria .= "a.dataid IN ($i)";
    }

    if ($ccount + $icount > 1) {
        $criteria .= ")";
    }
    return array('columns' => $list, 'criteria' => $criteria, 'groups' => $groups, 'parent' => $parent);
}


function asset_display_query($db, $table, $when, $terms, $machines, $display)
{
    global $machineTableName;
    global $print_query;

    $query = "SELECT a.machineid, a.dataid, a.ordinal, a.value";

    if (safe_count($display['groups']) > 0) {
        $query .= ", b.dataid, b.value";
    }

    $query .= " FROM " . $GLOBALS['PREFIX'] . "asset.$table AS a";

    mysqli_query("set names 'utf8'");
    if ($table == "AssetData") {
        $query .= " INNER JOIN " . $GLOBALS['PREFIX'] . "asset." . $machineTableName . " AS m ON a.machineid = m.machineid";

        if ($when) {
            $query .= " AND ((a.cearliest <= $when AND $when <= a.clatest) OR ($when > m.clatest AND a.clatest = m.clatest))";
        } else {
            $query .= " AND a.clatest = m.clatest";
        }

        $query .= " AND a.machineid IN (" . implode(",", $machines) . ")";
    }

    if (safe_count($display['groups']) > 0) {
        $query .= group_display_criteria($table, $terms, $display['groups']);
    }

    if ($table != "AssetData") {
        $query .= " WHERE a.machineid IN (" . implode(",", $machines) . ")";
    }

    if (safe_count($display['columns']) > 0) {
        $query .= ($table != "AssetData") ? " AND" : " WHERE";
        $query .= $display['criteria'];
    }

    if ($print_query) {
        echo "<p><small>&bull; $query</small></p>";
    }

    $temp = array();
    $res = mysqli_query($query, $db);

    if ($res) {
        while ($row = mysqli_fetch_row($res)) {
            $temp[] = $row;
        }

        mysqli_free_result($res);
    }

    return $temp;
}

function asset_display_query_new($db, $table, $when, $terms, $machines, $display)
{
    global $print_query;
    $query = '';

    if (safe_count($display['groups']) > 0) {
        $query = multiple_search_criteria($db, $table, $when, $terms, $machines, $display);
    } else {
        $query = single_search_criteria($db, $table, $when, $terms, $machines, $display);
    }
    if ($print_query) {
        echo "<p><small>&bull; $query[0]</small></p>";
    }
    $temp = array();
    $res = array();
    mysqli_set_charset('utf8', $db);
    if (safe_count($query) > 1) {
        foreach ($query as $key => $val) {

            $temp1 = mysqli_query($val, $db);
            array_push($res, $temp1);
        }
    } else {
        mysqli_set_charset('utf8', $db);
        db_change($GLOBALS['PREFIX'] . 'asset', $db);
        $temp1 = mysqli_query($query[0], $db);
        array_push($res, $temp1);
    }
    $flag = 0;
    if (!empty($res)) {
        foreach ($res as $a => $b) {
            while ($row = mysqli_fetch_row($b)) {

                if (key(preg_grep('/\b##\b/i', $row))) {
                    $flag = 1;
                }
                $temp[] = $row;
            }
        }
        mysqli_free_result($res);
    }
    if ($flag == 1) {
        $result = transform_mul_result($temp, $display['parent']);
    } else {
        $result = transform_result($temp);
    }
    return $result;
}

function multiple_search_criteria($db, $table, $when, $terms, $machines, $display)
{

    $sql1 = '';
    $machine = implode(',', $machines);
    $dataIdMap = array();
    $dataValMap = array();
    $dataId = array();
    $Querres = array();
    $block = array();
    foreach ($terms as $dataid => $blocks) {
        $block[$blocks['dataid']] = $blocks['block'];
    }
    $block = array_unique($block);
    $key = safe_array_keys($display['groups']);
    $list = implode(',', $display['columns']);
    $list = explode(',', $list);
    foreach ($display['groups'] as $groupId => $groupVal) {

        $dataIds = array();
        $sql1 = '';
        $sql2 = '';

        $filter = array_unique($display['groups'][$groupId]['filter']);
        array_push($dataIds, $display['groups'][$groupId]['base']);
        $tempFilter = array_flip($filter);
        $disval = array_diff($list, $filter);

        foreach ($terms as $dataid => $value) {
            $dataIdMap[$value['dataid']][] = $value['value'];
            $dataValMap[$value['block']][] = $value['value'];
            $dataComp[$value['dataid']] = $value['comparison'];
            $dataCompMap[] = $value['comparison'];
        }

        $dataIdMap = array_intersect_key($dataIdMap, $tempFilter);

        $i = 0;
        foreach ($dataIdMap as $dataId => $vals) {
            $sql = '';
            $dataIds[] = $dataId;

            if (safe_count($block) == 1) {
                $flag = 0;
                $machineId = array();
                foreach ($machines as $id => $Machineid) {
                    if ($dataComp[$dataId] == 10) {
                        $cons = 'NOT LIKE ';
                    } else {
                        $cons = 'LIKE ';
                    }

                    foreach ($vals as $key => $val) {
                        if (($key == 0 && $flag == 0) || ($key > 0 && $flag > 0)) {
                            $newSql = "SELECT M.host,a.value FROM "
                                . "AssetDataLatest AS a,Machine M WHERE a.machineid IN ($Machineid) " .
                                "AND a.machineid = M.machineid AND (a.dataid = " . $dataId . ")  AND ((a.value $cons '%" . $val . "%'));";
                            $result = find_many($newSql, $db);
                            if (safe_count($result) > 0) {
                                $flag = 1;
                            } else {
                                $flag = 0;
                            }
                        }
                    }
                    if ($flag == 1) {
                        array_push($machineId, $Machineid);
                    }
                }
                $machine = implode(',', $machineId);
            }

            $sql .= "INSERT INTO AssetDataLatest_1 (machinename,sitename,machineid,dataid,ordinal,value) "
                . "SELECT M.host,M.cust,a.machineid, a.dataid, a.ordinal, a.value FROM "
                . "AssetDataLatest AS a,Machine M WHERE a.machineid IN ($machine) "
                . "AND a.machineid = M.machineid AND ";

            $sql .= "(a.dataid = " . $dataId . ")  AND (";

            mysqli_query("set names 'utf8'");

            if ($dataComp[$dataId] == 10) {
                $conds = 'NOT LIKE ';
            } else {
                $conds = 'LIKE ';
            }
            foreach ($vals as $key => $val) {
                $sql .= "(a.value $conds '%" . $val . "%') OR ";
            }
            $sql = rtrim($sql, "OR ");
            $sql .= ")";
            if ($i == 0) {
            } else {
                $sql .= "AND a.ordinal in (SELECT S.ordinal from AssetDataLatest_1 S group by S.ordinal)";
            }
            $res = redcommand($sql, $db);
            $i++;
        }

        if (!empty($display['criteria'])) {
            $dispSql = '';
            $criteria = $display['criteria'];
            $disval   = array_unique($disval);
            $critSelVal = array_diff($disval, array($display['groups'][$groupId]['base']));
            $dtval = implode(',', $critSelVal);
            $critSelRes = "a.dataid IN (" . $dtval . ")";
            $dtval = ",'" . $dtval . "' as dtlist";

            $dispSql = "INSERT INTO AssetDataLatest_1 (machinename,sitename,machineid,dataid,ordinal,value) "
                . "SELECT M.host,M.cust,a.machineid, a.dataid, a.ordinal, a.value FROM "
                . "AssetDataLatest AS a,Machine M WHERE a.machineid IN ($machine) "
                . "AND a.machineid = M.machineid AND $criteria";

            db_change($GLOBALS['PREFIX'] . 'asset', $db);
            $result = redcommand($dispSql, $db);

            $sql2 .= "SELECT *$dtval FROM (SELECT b.machinename,a.machineid,a.ordinal, " .
                "GROUP_CONCAT( DISTINCT(a.dataid) SEPARATOR '##') AS id, GROUP_CONCAT( DISTINCT(CONCAT(a.value,'@@@', a.dataid)) SEPARATOR '##') AS ops " .
                "FROM AssetDataLatest_1 AS a ,AssetDataLatest_1 AS b WHERE  a.machineid = b.machineid " .
                "AND a.machineid IN ($machine) AND  $critSelRes AND (a.ordinal=b.ordinal) " .
                "AND (a.dataid !=b.dataid) GROUP BY a.machineid,a.ordinal) AS t";
        }
        $dataIds = array_unique($dataIds);
        $dataIds = implode(',', $dataIds);
        $dtlist = "'" . $dataIds . "' as dtlist";

        if (safe_count($block) == 1) {
            $uniqueCriteria = "AND (a.dataid !=b.dataid)";

            $sql1 .= "SELECT *,$dtlist FROM (SELECT b.machinename,a.machineid,a.ordinal, "
                . "GROUP_CONCAT( DISTINCT(a.dataid) SEPARATOR '##') as dataid, GROUP_CONCAT( DISTINCT(CONCAT(a.value,'@@@', a.dataid)) SEPARATOR '##') "
                . "as ops  FROM AssetDataLatest_1 AS a ,AssetDataLatest_1 AS b WHERE  a.machineid = b.machineid AND a.machineid IN"
                . " ($machine) AND  a.dataid IN($dataIds) AND (a.ordinal=b.ordinal)  ";
            if (safe_count($dataIds) > 1) {
                $sql1 .= "AND (a.dataid !=b.dataid) ";
            }
            $sql1 .= "GROUP BY a.machineid,a.ordinal) as t ";
        } else {
            $sql1 .= "SELECT *,$dtlist FROM (SELECT b.machinename,a.machineid,a.ordinal, "
                . "GROUP_CONCAT( DISTINCT(a.dataid) SEPARATOR '##') as dataid, GROUP_CONCAT( DISTINCT(CONCAT(a.value,'@@@', a.dataid)) SEPARATOR '##') "
                . "as ops  FROM AssetDataLatest_1 AS a ,AssetDataLatest_1 AS b WHERE  a.machineid = b.machineid AND a.machineid IN"
                . " ($machine) AND  a.dataid IN($dataIds) AND (a.ordinal=b.ordinal) AND "
                . "(a.dataid !=b.dataid) GROUP BY a.machineid,a.ordinal) as t WHERE  (";

            mysqli_query("set names 'utf8'");

            foreach ($dataValMap as $id => $data) {
                $sql1 .= "(";
                if ($dataCompMap[$id] == 10) {
                    $condition = 'NOT LIKE ';
                } else {
                    $condition = 'LIKE ';
                }

                foreach ($data as $index => $cond) {
                    $sql1 .= "ops $condition '%" . $cond . "%' AND ";
                }
                $sql1 = rtrim($sql1, "AND ");
                $sql1 .= ") OR ";
            }
            $sql1 = rtrim($sql1, "OR ");
            $sql1 .= ")";
        }

        if ($sql1 != '') {
            array_push($Querres, $sql1);
        }
        if ($sql2 != '') {
            array_push($Querres, $sql2);
        }
    }

    return $Querres;
}

function single_search_criteria($db, $table, $when, $terms, $machines, $display)
{

    global $machineTableName;
    global $print_query;
    $query = '';

    $query = "SELECT a.machineid, a.dataid, a.ordinal, a.value FROM $table AS a";

    mysqli_query("set names 'utf8'");
    if ($table == "AssetData") {
        $query .= " INNER JOIN " . $machineTableName . " AS m ON a.machineid = m.machineid";

        if ($when) {
            $query .= " AND ((a.cearliest <= $when AND $when <= a.clatest) OR ($when > m.clatest AND a.clatest = m.clatest))";
        } else {
            $query .= " AND a.clatest = m.clatest";
        }

        $query .= " AND a.machineid IN (" . implode(",", $machines) . ")";
    }

    if ($table != "AssetData") {
        $query .= " WHERE a.machineid IN (" . implode(",", $machines) . ")";
    }

    if (safe_count($display['columns']) > 0) {
        $query .= ($table != "AssetData") ? " AND" : " WHERE";
        $query .= $display['criteria'];
    }
    return array($query);
}

function transform_result($assets)
{
    $result = array();
    $flag = [];
    global $multiple;
    foreach ($assets as $a) {
        $result[$a[0]][$a[1]][$a[2]] = $a[3];

        if (!isset($flag[$a[1]])) {
            $maxData[$a[1]] = safe_count($result[$a[0]][$a[1]]);
            $multiple[$a[1]] = False;
        }
        if ($maxData[$a[1]] > 1 && !isset($flag[$a[1]])) {
            $flag[$a[1]] = FALSE;
            $multiple[$a[1]] = TRUE;
        }

        if (isset($a[4])) {
            $result[$a[0]][$a[4]][$a[2]] = $a[5];
        }
    }
    return $result;
}

function transform_mul_result($asset, $parentArr)
{

    $result = array();

    $flag = [];
    global $multiple;

    foreach ($asset as $a) {
        $machineId = $a[1];
        $dataId = explode('##', $a[3]);
        $dataValue = explode('##', $a[4]);
        $ordinal = $a[2];
        if (isset($a[5])) {
            $dataIdArr = explode(',', $a[5]);
            $difference = array_diff($dataIdArr, $dataId);
            $dataList = array_merge($dataId, $difference);
        } else {
            $dataList = $dataId;
        }

        foreach ($dataList as $key => $dataid) {
            if (array_key_exists($key, $dataValue)) {
                $dataVals = explode('@@@', $dataValue[$key]);
                $result[$a[1]][$dataid][$ordinal] = $dataVals[0];
            } elseif (!in_array($dataid, $parentArr[0])) {
                $result[$a[1]][$dataid][$ordinal] = '';
            }

            if (!isset($flag[$dataid])) {
                $maxData[$dataid] = safe_count($result[$a[1]][$dataid]);
                $multiple[$dataid] = False;
            }
            if ($maxData[$dataid] > 1 && !isset($flag[$dataid])) {
                $flag[$dataid] = FALSE;
                $multiple[$dataid] = TRUE;
            }
        }
    }
    return $result;
}

function run_raw_query($db, $table, $when, $mids, $terms, $fields, $limit, $page, $info)
{
    $assets = array();
    $result = array();
    if (safe_count($terms) > 0) {
        $mids = select_machines($db, $table, $when, $terms, $mids);
    }

    $total = safe_count($mids);
    if ($total > 0) {
        if ($limit > 0) {
            $offset = ($page - 1) * $limit;
            $mids = array_slice($mids, $offset, $limit);
        }

        $display = asset_display_criteria($db, $fields, $terms, $mids);
        if ($info == 'info') {
            $assets = asset_display_query($db, $table, $when, $terms, $mids, $display);
        } else {
            $assets = asset_display_query_new($db, $table, $when, $terms, $mids, $display);
        }
    }

    foreach ($terms as $dataid => $blocks) {
        $block[$blocks['dataid']] = $blocks['block'];
    }
    $block = array_unique($block);
    if (safe_count($block) > 1) {
        $blockFlag = 2;
    } else {
        $blockFlag = 1;
    }


    if ($info == 'info') {

        $result['columns'] = $display['columns'];
        $result['rows']    = transform_result($assets);
        $result['mids'] = $mids;
        $result['total'] = $total;
    } else {
        $result['columns'] = $display['columns'];
        $result['parent'] = $display['parent'];
        $result['rows'] = $assets;
        $result['pages'] = ceil($total / $limit);
        $result['block'] = $blockFlag;
        $result['total'] = $total;
    }


    return $result;
}


function run_adhoc_query($db, $mids, $terms, $fields, $limit, $page, $info)
{
    global $assetTableName;
    getTableName($db);
    $table = $assetTableName;
    $when = 1;

    $result = run_raw_query($db, $table, $when, $mids, $terms, $fields, $limit, $page, $info);

    return $result;
}


function run_query($db, $qid, $mids, $page, $override, $limit)
{
    global $assetTableName;
    global $multiple;

    $clearTableSql = "TRUNCATE " . $GLOBALS['PREFIX'] . "asset.AssetDataLatest_1";
    $clearTablerRes = redcommand($clearTableSql, $db);

    $sql = "SELECT * FROM AssetSearches WHERE id = $qid";
    $res = mysqli_query($db, $sql);
    getTableName($db);
    $result = array();

    if ($res) {
        $row = mysqli_fetch_array($res);

        if (!$row) {
            $str = "<p>Query with id $qid not found.</p>";
            print_data($str);
            return;
        }

        $name = $row['name'];
        $fields = $row['displayfields'];
        $dc = $row['date_code'];
        $dv = $row['date_value'];
        $search = $row['searchstring'];

        $when = asset_date_code($dc, $dv);
        $table = "AssetData";

        if ($override != 1 && $dc == 1) {
            $table = $assetTableName;
        }

        $terms = fetch_search_terms($db, $qid);

        $result = run_raw_query($db, $table, $when, $mids, $terms, $fields, $limit, $page, '');

        $result['search'] = $search;
        $result['fields'] = $fields;
        $result['name'] = $name;
        $result['multiple'] = $multiple;
    }

    return $result;
}
