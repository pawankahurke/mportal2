<?php

/* 
Revision history:

Date        Who     What
----        ---     ----
14-Jan-03   EWB     Created
 7-Apr-03   EWB     Returns number of records removed.
*/

/*
    |  The AssetSearchCriteria table doesn't really directly
    |  specify any owners, it just exists as an adjunct to the 
    |  AssetSearches table.
    |
    |  The problem is that sometimes the criteria don't get
    |  removed when the searches do ... so we have this
    |  procedure to prevent waxy yellow buildup.
    */

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
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
    }

    $sql = 'select id from AssetSearches';
    $res = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $sid = $row['id'];
            $used[$sid] = true;
        }
        ((mysqli_free_result($res) || (is_object($res) && (get_class($res) == "mysqli_result"))) ? true : false);
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
