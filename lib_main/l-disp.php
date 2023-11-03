<?php





function flatten_array($main_array, $index_array, $db)
{
    $array_flat = array();
    if ($main_array) {
        reset($main_array);
        reset($index_array);
        foreach ($index_array as $k => $v) {
            $subarray = $main_array[$k];
            reset($subarray);
            foreach ($subarray as $k2 => $v2) {
                $array_flat[] = $k2;
            }
        }

        $array_flat = array_unique($array_flat);
    }
    return $array_flat;
}


function bold_html($selected, $itemtype, $itemid)
{
    $bold = "";
    if (($selected['itemtype'] == $itemtype) && ($selected['itemid'] == $itemid)) {
        $bold = " class='bold' ";
    }
    return $bold;
}


function rgb2hex($rgb)
{
    if (safe_count($rgb) != 3) {
        echo "Argument must be an array with 3 integer elements";
        return false;
    }
    for ($i = 0; $i < safe_count($rgb); $i++) {
        if (strlen($hex[$i] = dechex($rgb[$i])) == 1) {
            $hex[$i] = "0" . $hex[$i];
        }
    }
    return $hex;
}



function border_format($status, $statuscfg)
{
    if (array_key_exists($status, $statuscfg)) {
        $px  = $status ? $status : 1;
        $rgb = array($statuscfg[$status]['red'], $statuscfg[$status]['green'], $statuscfg[$status]['blue']);
    } else {
        $px  = 1;
        $rgb = array($statuscfg[0]['red'], $statuscfg[0]['green'], $statuscfg[0]['blue']);
    }
    $hex = join('', rgb2hex($rgb));

    $border_fmt = array();
    $border_fmt['width']    = $px . "px";
    $border_fmt['style']    = "solid";
    $border_fmt['color']    = $hex;
    return $border_fmt;
}

function get_statuscfg($userid, $db)
{
    $statuscfg = array();

    $statuscfg[0]['name']       = "n/a";
    $statuscfg[0]['highlight']  = 1;
    $statuscfg[0]['red']        = 128;
    $statuscfg[0]['green']      = 128;
    $statuscfg[0]['blue']       = 128;

    $statuscfg[1]['name']       = "OK";
    $statuscfg[1]['highlight']  = 1;
    $statuscfg[1]['red']        = 0;
    $statuscfg[1]['green']      = 165;
    $statuscfg[1]['blue']       = 107;
    $statuscfg[2]['name']       = "warning";
    $statuscfg[2]['highlight']  = 1;
    $statuscfg[2]['red']        = 247;
    $statuscfg[2]['green']      = 214;
    $statuscfg[2]['blue']       = 0;
    $statuscfg[3]['name']       = "alert";
    $statuscfg[3]['highlight']  = 1;
    $statuscfg[3]['red']        = 240;
    $statuscfg[3]['green']      = 57;
    $statuscfg[3]['blue']       = 66;

    $sql  = "SELECT statusval, name, highlight, red, green, blue\n"
        . "FROM StatusConfig\n"
        . "WHERE userid = " . $userid . "\n"
        . "ORDER BY statusval";
    $set = find_many($sql, $db);
    if (safe_count($set)) {
        reset($set);
        foreach ($set as $key => $row) {
            $val       = $row['statusval'];
            $name      = $row['name'];
            $highlight = $row['highlight'];
            $red       = $row['red'];
            $green     = $row['green'];
            $blue      = $row['blue'];

            $statuscfg[$val]['name']        = $name;
            $statuscfg[$val]['highlight']   = $highlight;
            $statuscfg[$val]['red']         = $red;
            $statuscfg[$val]['green']       = $green;
            $statuscfg[$val]['blue']        = $blue;
        }
    }
    return $statuscfg;
}







function return_tableids($selected)
{
    $itemtype   = $selected['itemtype'];
    $tableids   = array();

    switch ($itemtype) {
        case constDisplayItemDisplay:
            $tableids['DisplayMachineDisplay']  = constTableIDDisplayMachineDisplay;
            $tableids['DisplayMonitorDisplay']  = constTableIDDisplayMonitorDisplay;
            break;
        case constDisplayItemMachineGroup:
            $tableids['MachineGroupDisplay']    = constTableIDMachineGroupDisplay;
            break;
        case constDisplayItemMonItemGroup:
            $tableids['MonItemGroupDisplay']    = constTableIDMonItemGroupDisplay;
            break;
        case constDisplayItemMachine:
            $tableids['MachineDisplay']         = constTableIDMachineDisplay;
            break;
        case constDisplayItemMonItem:
            $tableids['MonitorDisplay']         = constTableIDMonitorDisplay;
            break;
        case constDisplayItemProfile:
            $tableids['ProfileDisplay']         = constTableIDProfileDisplay;
            break;
        case constDisplayItemSecurity:
            $tableids['SecurityDisplay']        = constTableIDSecurityDisplay;
            break;
        case constDisplayItemResources:
            $tableids['ResourceDisplay']        = constTableIDResourceDisplay;
            break;
        case constDisplayItemEvents:
            $tableids['EventDisplay']           = constTableIDEventDisplay;
            break;
        case constDisplayItemMaintenance:
            $tableids['MaintenanceDisplay']     = constTableIDMaintenanceDisplay;
            break;
    }
    return $tableids;
}


function return_seltbl_args($tableid)
{
    switch ($tableid) {
        case constTableIDDisplayMachineDisplay:
            return array(
                'curPage'       => '0',
                'postPage'      => '',
                'uniqueIndex'   => 'displaymachdispid',
                'partialLink'   => 'display.php?test&detail=',
                'postPage'      => 'display.php?test&set=1',
                'sortLink'      => 'display.php?sort=',
                'rootLink'      => 'display.php?test',
                'columns'       =>
                array(
                    'name' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Name',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingAsc,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 0,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'status' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Status',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 1,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'count' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Number of Items',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 2,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'updated' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Last Update',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataTimestamp,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 3,
                        'pixelWidth'        => 150
                    )
                )
            );

        case constTableIDDisplayMonitorDisplay:
            return array(
                'curPage'       => '0',
                'postPage'      => '',
                'uniqueIndex'   => 'displaymondispid',
                'partialLink'   => 'display.php?test&detail=',
                'postPage'      => 'display.php?test&set=1',
                'sortLink'      => 'display.php?sort=',
                'rootLink'      => 'display.php?test',
                'columns'       =>
                array(
                    'name' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Name',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingAsc,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 0,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'status' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Status',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 1,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'count' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Number of Items',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 2,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'updated' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Last Update',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataTimestamp,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 3,
                        'pixelWidth'        => 150
                    )
                )
            );

        case constTableIDMachineGroupDisplay:
            return array(
                'curPage'       => '0',
                'postPage'      => '',
                'uniqueIndex'   => 'mgroupdispid',
                'partialLink'   => 'display.php?test&detail=',
                'postPage'      => 'display.php?test&set=1',
                'sortLink'      => 'display.php?sort=',
                'rootLink'      => 'display.php?test',
                'columns'       =>
                array(
                    'site' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Site Name',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingAsc,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 0,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'host' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'     => 'Machine Name',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingAsc,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 1,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'status' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Status',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 2,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'updated' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Last Update',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataTimestamp,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 3,
                        'pixelWidth'        => 150
                    )
                )
            );

        case constTableIDMonItemGroupDisplay:
            return array(
                'curPage'       => '0',
                'postPage'      => '',
                'uniqueIndex'   => 'mongroupdispid',
                'partialLink'   => 'display.php?test&detail=',
                'postPage'      => 'display.php?test&set=1',
                'sortLink'      => 'display.php?sort=',
                'rootLink'      => 'display.php?test',
                'columns'       =>
                array(
                    'resourcename' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Resouce Name',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingAsc,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 0,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'resourceloc' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Resouce Location',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 1,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'resourcetype' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Resouce Type',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 2,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'count' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Number of Monitoring Machines',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 3,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'status' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Status',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 4,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'updated' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Last Update',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataTimestamp,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 5,
                        'pixelWidth'        => 150
                    )
                )
            );

        case constTableIDMachineDisplay:
            return array(
                'curPage'       => '0',
                'postPage'      => '',
                'uniqueIndex'   => 'mdispid',
                'partialLink'   => 'display.php?test&detail=',
                'postPage'      => 'display.php?test&set=1',
                'sortLink'      => 'display.php?sort=',
                'rootLink'      => 'display.php?test',
                'columns'       =>
                array(
                    'category' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Category',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingAsc,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 0,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'status' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Status',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 1,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'updated' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Last Update',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataTimestamp,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 2,
                        'pixelWidth'        => 150
                    )
                )
            );

        case constTableIDMonitorDisplay:
            return array(
                'curPage'       => '0',
                'postPage'      => '',
                'uniqueIndex'   => 'monitordispid',
                'partialLink'   => 'display.php?test&detail=',
                'postPage'      => 'display.php?test&set=1',
                'sortLink'      => 'display.php?sort=',
                'rootLink'      => 'display.php?test',
                'columns'       =>
                array(
                    'site' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Site Name',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingAsc,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 0,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'host' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'     => 'Machine Name',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingAsc,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 1,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'status' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Status',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 2,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'updated' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Last Update',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataTimestamp,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 3,
                        'pixelWidth'        => 150
                    )
                )
            );

        case constTableIDSecurityDisplay:
            return array(
                'curPage'       => '0',
                'postPage'      => '',
                'uniqueIndex'   => 'secdispid',
                'partialLink'   => 'display.php?test&detail=',
                'postPage'      => 'display.php?test&set=1',
                'sortLink'      => 'display.php?sort=',
                'rootLink'      => 'display.php?test',
                'columns'       =>
                array(
                    'name' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Security Item Name',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingAsc,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 0,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'host' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Machine Name',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingAsc,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 1,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'sectype' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Security Item Type',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 2,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'val' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Value',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 3,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'status' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Status',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 4,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'updated' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Last Update',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataTimestamp,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 5,
                        'pixelWidth'        => 150
                    )
                )
            );

        case constTableIDResourceDisplay:
            return array(
                'curPage'       => '0',
                'postPage'      => '',
                'uniqueIndex'   => 'resdispid',
                'partialLink'   => 'display.php?test&detail=',
                'postPage'      => 'display.php?test&set=1',
                'sortLink'      => 'display.php?sort=',
                'rootLink'      => 'display.php?test',
                'columns'       =>
                array(
                    'name' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Resource Name',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingAsc,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 0,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'restype' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Resource Type',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 1,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'interval' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Measurement Interval',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 2,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'val' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Value',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 3,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'status' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Status',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 4,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'updated' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Last Update',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataTimestamp,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 5,
                        'pixelWidth'        => 150
                    )
                )
            );

        case constTableIDEventDisplay:
            return array(
                'curPage'       => '0',
                'postPage'      => '',
                'uniqueIndex'   => 'eventdispid',
                'partialLink'   => 'display.php?test&detail=',
                'postPage'      => 'display.php?test&set=1',
                'sortLink'      => 'display.php?sort=',
                'rootLink'      => 'display.php?test',
                'columns'       =>
                array(
                    'name' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Name',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingAsc,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 0,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'status' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Status',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 1,
                        'pixelWidth'        => 150
                    )
                )
            );

        case constTableIDMaintenanceDisplay:
            return array(
                'curPage'       => '0',
                'postPage'      => '',
                'uniqueIndex'   => 'maintdispid',
                'partialLink'   => 'display.php?test&detail=',
                'postPage'      => 'display.php?test&set=1',
                'sortLink'      => 'display.php?sort=',
                'rootLink'      => 'display.php?test',
                'columns'       =>
                array(
                    'name' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Name',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingAsc,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 0,
                        'pixelWidth'        => 150
                    )
                ),
                array(
                    'status' =>
                    array(
                        'display'           => TRUE,
                        'friendlyName'      => 'Status',
                        'inSearch'          => TRUE,
                        'sortOptions'       => constSortOptionBoth,
                        'sortSetting'       => constSortSettingNone,
                        'dispData'          => constDispDataNone,
                        'searchOption'      => constSelSearchBasic,
                        'searchQuery'       => NULL,
                        'searchSetting'     => 1,
                        'subSearch'         => FALSE,
                        'searchStr'         => NULL,
                        'maxChars'          => 200,
                        'dispOrder'         => 1,
                        'pixelWidth'        => 150
                    )
                )
            );

        default:
            return array();
    }
}

function log_error($err)
{
    if ($err != constAppNoErr) {
        echo "<html><head><title>Selection Page Failure</title></head>"
            . "<body>";
        echo "\nAn error has occurred processing this page.  See ";
        echo "<a href=\"../acct/csrv.php?error\">errlog.txt</a>.\n";
        echo "</body>\n";
    }
}

function gen_seltables($env, $selected, $db)
{
    $itemtype   = $selected['itemtype'];
    $itemid     = $selected['itemid'];

    $tableids = return_tableids($itemtype);
    reset($tableids);
    foreach ($tableids as $tablename => $tableid) {

        $sql = "INSERT IGNORE INTO  " . $GLOBALS['PREFIX'] . "display.TableIdentifiers (tableid, dbname, tblname)"
            . " VALUES (" . $tableid . ", 'dashboard',  '" . $tablename . "')";
        redcommand($sql, $db);


        $sql = "INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "display.SelectionOptions \n"
            . "    (tableid, opt, defvalint, defvalstr)\n"
            . "VALUES\n"
            . "    (" . $tableid . ", 1, 10, ''),\n"
            . "    (" . $tableid . ", 2, 1, ''),\n"
            . "    (" . $tableid . ", 3, 1, '')";
        redcommand($sql, $db);

        $seltbl_args = return_seltbl_args($tableid);
        reset($seltbl_args);
        foreach ($seltbl_args['columns'] as $colname => $args) {

            $err = PHP_HTML_SetDefaultColumnOption(
                CUR,
                $tableid,
                $colname,
                $args['display'],
                $args['friendlyName'],
                $args['inSearch'],
                $args['sortOptions'],
                $args['sortSetting'],
                $args['dispData'],
                $args['searchOption'],
                $args['searchQuery'],
                $args['searchSetting'],
                $args['subSearch'],
                $args['searchStr'],
                $args['maxChars'],
                $args['dispOrder'],
                $args['pixelWidth']
            );
        }


        $err = PHP_HTML_BuildUserOptions(CUR);
        log_error($err);

        $detail = 1;
        $set = 1;
        $sort = 1;
        if (server_var('QUERY_STRING')) {
            if (strpos(server_var('QUERY_STRING'), "detail") === false) {
                $detail = 0;
            } else if (strpos(server_var('QUERY_STRING'), "set") === false) {
                $set = 0;
            } else if (strpos(server_var('QUERY_STRING'), "sort") === false) {
                $sort = 0;
            }
        } else {
            $detail = 0;
            $set = 0;
            $sort = 0;
        }

        $displayFull = 1;

        if ($detail) {
            $page = 0;
            $queryStr = server_var('QUERY_STRING');
            $err = PHP_HTML_GetTableSelect(
                CUR,
                $queryStr2,
                $schPtr,
                $pageSize,
                $format,
                $whereStr,
                $table,
                $env['username'],
                $page,
                $seltbl_args['postPage']
            );
            log_error($err);

            $err = PHP_CSRV_GetRowDetail(
                CUR,
                $html,
                $queryStr,
                "dashboard",
                $tablename,
                $seltbl_args['uniqueIndex'],
                $format
            );
            log_error($err);

            $displayFull = 0;
        } elseif (($set) || ($sort)) {
            $err = PHP_HTML_StoreSearchOptions(
                CUR,
                isset($GLOBALS["HTTP_RAW_POST_DATA"]) ?
                    $GLOBALS["HTTP_RAW_POST_DATA"] : 0,
                $tableid,
                $env['username'],
                server_var('QUERY_STRING')
            );
            log_error($err);
        }

        if ($displayFull) {
            $err = PHP_CSRV_GetTable(
                CUR,
                $html,
                server_var('QUERY_STRING'),
                $tableid,
                $seltbl_args['partialLink'],
                $env['username'],
                $seltbl_args['postPage'],
                $seltbl_args['sortLink'],
                $seltbl_args['rootLink']
            );
            log_error($err);
        }
    }
}


function gen_heading($itemtype, $itemid, $db)
{
    $heading = "";

    if (!$itemtype) {
        $heading = "Please select an item to the left.";
    } else {
        switch ($itemtype) {
            case constDisplayItemDisplay:
                $sql = "SELECT name\nFROM Displays\nWHERE dispid = " . $itemid;
                break;
            case constDisplayItemMachineGroup:
                $sql = "SELECT name\nFROM " . $GLOBALS['PREFIX'] . "core.MachineGroups\nWHERE mgroupid = " . $itemid;
                break;
            case constDisplayItemMonItemGroup:
                $sql = "SELECT name\nFROM MonitorGroups\nWHERE mongroupid = " . $itemid;
                break;
            case constDisplayItemMachine:
                $sql = "SELECT CONCAT(site,':',host) AS name\nFROM " . $GLOBALS['PREFIX'] . "core.Census\nWHERE id = " . $itemid;
                break;
            case constDisplayItemMonItem:
                $sql = "SELECT name\nFROM MonitorItems\nWHERE monitemid = " . $itemid;
                break;
            case constDisplayItemProfile:
                $sql = "SELECT CONCAT(site,':',host) AS name\nFROM " . $GLOBALS['PREFIX'] . "core.Census\nWHERE id = " . $itemid;
                break;
            case constDisplayItemSecurity:
                $sql = "SELECT CONCAT(site,':',host) AS name\nFROM " . $GLOBALS['PREFIX'] . "core.Census\nWHERE id = " . $itemid;
                break;
            case constDisplayItemResources:
                $sql = "SELECT CONCAT(site,':',host) AS name\nFROM " . $GLOBALS['PREFIX'] . "core.Census\nWHERE id = " . $itemid;
                break;
            case constDisplayItemEvents:
                $sql = "SELECT CONCAT(site,':',host) AS name\nFROM " . $GLOBALS['PREFIX'] . "core.Census\nWHERE id = " . $itemid;
                break;
            case constDisplayItemMaintenance:
                $sql = "SELECT CONCAT(site,':',host) AS name\nFROM " . $GLOBALS['PREFIX'] . "core.Census\nWHERE id = " . $itemid;
                break;
        }

        $row = find_one($sql, $db);
        if ($row) {
            $heading  = $row['name'];
        }
    }
    return $heading;
}


function create_snapshot_link($itemtype, $itemid, $parent, $db)
{
    $snapshot_link = "";

    if ($itemtype == constDisplayItemMachineGroup) {
        $act        = 201;
        $mgroupid   = $itemid;
        $sql = "SELECT t1.itemid AS dispid FROM Expansions AS t1, Expansions as t2\n"
            . "WHERE t1.itemtype = " . constDisplayItemDisplay . "\n"
            . "AND t1.expandid = " . $parent . "\n";
        $row = find_one($sql, $db);
        if ($row) {
            $dispid    = $row['dispid'];
        }

        $snapshot_url = "../config/syst.php?act=" . $act
            . "&display=" . $dispid
            . "&mgroupid=" . $mgroupid;

        $str = "these machines";
        $snapshot_link = "<a href=\"" . $snapshot_url . "\">Generate snapshot for " . $str . "</a>";
    } elseif ($itemtype == constDisplayItemMachine) {
        $act        = 301;
        $censusid   = $itemid;
        $sql = "SELECT t1.itemid AS dispid, t2.itemid AS mgroupid\n"
            . "FROM Expansions AS t1, Expansions as t2\n"
            . "WHERE t1.itemtype = " . constDisplayItemDisplay . "\n"
            . "AND t1.expandid = t2.parent\n"
            . "AND t2.expandid = " . $parent . "\n";
        $row = find_one($sql, $db);
        if ($row) {
            $dispid    = $row['dispid'];
            $mgroupid  = $row['mgroupid'];
        }

        $snapshot_url = "../config/syst.php?act=" . $act
            . "&display=" . $dispid
            . "&mgroupid=" . $mgroupid
            . "&censusid=" . $censusid;

        $str = "this machine";
        $snapshot_link = "<a href=\"" . $snapshot_url . "\">Generate snapshot for " . $str . "</a>";
    }
    return $snapshot_link;
}


function create_restart_link($itemtype, $itemid, $db)
{
    $restart_link = "";
    if ($itemtype == constDisplayItemDisplay) {
        $restart_url = "disp-act.php?act=restart&itemtype=" . $itemtype . "&itemid=" . $itemid;
        $restart_link = "<a href=\"" . $restart_url . "\">Restart monitoring for this display</a>";
    }
    return $restart_link;
}
