<?php





function purge_unused_criteria($db)
{
    $used = array();
    $sids = array();
    $num  = 0;
    $sql  = 'select assetsearchid from AssetSearchCriteria';
    $res  = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $sid = $row['assetsearchid'];
            $used[$sid] = false;
        }
        mysqli_free_result($res);
    }

    $sql = 'select id from AssetSearches';
    $res = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $sid = $row['id'];
            $used[$sid] = true;
        }
        mysqli_free_result($res);
    }
    if ($used) {
        reset($used);
        foreach ($used as $key => $data) {
            if (!$data) $sids[] = $key;
        }
    }

    if ($sids) {
        $list = implode(',', $sids);
        $sql  = "delete from AssetSearchCriteria\n"
            . " where assetsearchid in ($list)";
        $res  = redcommand($sql, $db);
        $num  = affected($res, $db);
    }
    return $num;
}
