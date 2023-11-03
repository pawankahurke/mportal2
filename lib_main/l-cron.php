<?php





function userlist($db)
{
    $set = array();
    $sql = "select distinct U.username\n"
        . " from " . $GLOBALS['PREFIX'] . "core.Users as U,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers as S\n"
        . " where U.username = S.username\n"
        . " and S.customer = C.site\n"
        . " order by U.username";
    $res = command($sql, $db);
    if ($res) {
        if (mysqli_num_rows($res)) {
            while ($row = mysqli_fetch_assoc($res)) {
                $set[] = $row['username'];
            }
        }
        mysqli_free_result($res);
    }
    return $set;
}



function siteaccesstree($users, $db)
{
    $siteaccesstree = array();
    if ($users) {
        reset($users);
        foreach ($users as $key => $user) {
            $siteaccesstree[$user] = site_array($user, 0, $db);
        }
    }
    return $siteaccesstree;
}



function sitefiltertree($objecttype, $objectids, $db)
{
    $filtertree = array();

    switch ($objecttype) {
        case 'assetreport':
            $auxtable = 'RptSiteFilters';
            $idfield  = 'assetreportid';
            break;
        default:
            $auxtable = 'TheWrongTable';
            $idfield  = 'TheWrongId';
    }

    if ($objectids) {
        $idlist = implode(',', $objectids);

        $sql  = "SELECT site,$idfield FROM $auxtable\n";
        $sql .= " WHERE $idfield IN ($idlist)\n";
        $sql .= " AND filter = 1\n";
        $sql .= " ORDER BY site";
        $res  = redcommand($sql, $db);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $id = $row[$idfield];
                $filtertree[$id][] = $row['site'];
            }
            mysqli_free_result($res);
        }
    }

    return $filtertree;
}



function find_active_sites($db)
{
    $active = array();
    $sql = "select distinct site from " . $GLOBALS['PREFIX'] . "core.Census order by site";
    $res = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $active[] = $row['site'];
        }
        mysqli_free_result($res);
    }
    return $active;
}



function usertree($db)
{
    $tree = array();
    $sql  = "select * from " . $GLOBALS['PREFIX'] . "core.Users order by username";
    $res  = command($sql, $db);
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $name = $row['username'];
            $tree[$name] = $row;
        }
        mysqli_free_result($res);
    }
    return $tree;
}


function email_list($to, $defmail, $def)
{
    if ($defmail) {
        if ($def) {
            if ($to)
                $to = "$to,$def";
            else
                $to = $def;
        }
    }
    return $to;
}




function site_intersect($a, $b)
{
    $tmp = array();
    $res = array();
    reset($a);
    foreach ($a as $key => $data) {
        $name = strtolower($data);
        $tmp[$name] = false;
    }
    reset($b);
    foreach ($b as $key => $data) {
        $name = strtolower($data);
        $tmp[$name] = true;
    }
    reset($a);
    foreach ($a as $key => $data) {
        $name = strtolower($data);
        if ($tmp[$name])
            $res[] = $data;
    }
    return $res;
}
