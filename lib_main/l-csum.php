<?php





function find_site_code($site, $db)
{
    $qs  = safe_addslashes($site);
    $sql = "select * from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers\n"
        . " where customer = '$qs'\n"
        . " and username = ''";
    $row = find_one($sql, $db);
    return ($row) ? $row['id'] : 0;
}


function find_host_code($site, $host, $db)
{
    $qs  = safe_addslashes($site);
    $qh  = safe_addslashes($host);
    $sql = "select H.id as hid,\n"
        . " C.id as cid from\n"
        . " " . $GLOBALS['PREFIX'] . "core.Customers as C,\n"
        . " " . $GLOBALS['PREFIX'] . "core.Census as H\n"
        . " where H.site = '$qs'\n"
        . " and H.host = '$qh'\n"
        . " and H.site = C.customer\n"
        . " and C.username = ''";
    return find_one($sql, $db);
}


function dirty_hid($hid, $db)
{
    $num = 0;
    if ($hid) {
        $now = time();
        $sql = "update " . $GLOBALS['PREFIX'] . "core.LegacyCache set\n"
            . " drty = 1,\n"
            . " last = $now\n"
            . " where censusid = $hid";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}


function dirty_cid($cid, $db)
{
    $num = 0;
    if ($cid) {
        $now = time();
        $sql = "update " . $GLOBALS['PREFIX'] . "core.LegacyCache set\n"
            . " drty = 1,\n"
            . " last = $now\n"
            . " where siteid = $cid";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}


function dirty_both($cid, $hid, $db)
{
    $num = 0;
    if (($hid) && ($cid)) {
        $now = time();
        $sql = "update " . $GLOBALS['PREFIX'] . "core.LegacyCache set\n"
            . " drty = 1,\n"
            . " last = $now\n"
            . " where censusid = $hid\n"
            . " or siteid = $cid";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);
    }
    return $num;
}




function dirty_host($site, $host, $db)
{
    $num = 0;
    $row = find_host_code($site, $host, $db);
    if ($row) {
        $hid = $row['hid'];
        $num = dirty_hid($hid, $db);
    }
    return $num;
}




function dirty_site($site, $db)
{
    $cid = find_site_code($site, $db);
    return dirty_cid($cid, $db);
}




function dirty_checksum($site, $host, $db)
{
    $num = 0;
    $row = find_host_code($site, $host, $db);
    if ($row) {
        $hid = $row['hid'];
        $cid = $row['cid'];
        $num = dirty_both($cid, $hid, $db);
    }
    return $num;
}



function dirty_group($gid, $db)
{
    $sql = "SELECT category, name, mgroupuniq FROM " . $GLOBALS['PREFIX'] . "core.MachineCategories "
        . "LEFT JOIN "
        . $GLOBALS['PREFIX'] . "core.MachineGroups ON (" . $GLOBALS['PREFIX'] . "core.MachineCategories.mcatuniq=" . $GLOBALS['PREFIX'] . "core."
        . "MachineGroups.mcatuniq) WHERE mgroupid=$gid";
    $row = find_one($sql, $db);
    if ($row) {
        if (strcmp($row['category'], 'Site') == 0) {

            dirty_site($row['name'], $db);
        }

        $sql = "SELECT id FROM " . $GLOBALS['PREFIX'] . "core.Census LEFT JOIN " . $GLOBALS['PREFIX'] . "core.MachineGroupMap"
            . " ON (" . $GLOBALS['PREFIX'] . "core.Census.censusuniq=" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq"
            . ") WHERE " . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq='"
            . $row['mgroupuniq'] . "'";
        $set = find_many($sql, $db);
        if ($set) {
            foreach ($set as $key => $row2) {
                dirty_hid($row2['id'], $db);
            }
        }
    }
}
