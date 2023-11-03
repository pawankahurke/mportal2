<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'l-setTimeZone.php';



function UTIL_GetInteger($name, $def)
{

    $tmp = UTIL_GetArgument($name, 0, '');
    return ($tmp == '') ? intval($def) : intval($tmp);
}



function UTIL_GetString($name, $def)
{
    return trim(UTIL_GetArgument($name, 0, $def));
}



function UTIL_GetArgument($name, $quoted, $default)
{

    $valu = $default;

    if (isset($_REQUEST[$name])) {
        $valu = strip_tags($_REQUEST[$name]);
    }

    return UTIL_MagicQuote($quoted, $valu);
}



function UTIL_MagicQuote($quoted, $valu)
{

    if ($quoted) {
        $valu = safe_addslashes($valu);
    }
    return $valu;
}


function UTIL_GetSiteScope($db, $selectedItem, $selectedType)
{
    if ($selectedItem == 'All') {
        $user = $_SESSION['user']['username'];
        $scope = [];
        $key = '';

        switch ($selectedType) {
            case 'Sites':
                $scope = DASH_GetSites($key, $db, $user);
                break;
            case 'Groups':
                $scope = DASH_GetGroups($key, $db, $user);
                break;
            default:
                break;
        }
        return $scope;
    } else {
        return $selectedItem;
    }
}



function UTIL_FormatDataForHomePage($data, $type, $totalDevice)
{

    $formatData = [];
    switch ($type) {
        case 'chassistype':
            $staticArray = array('Laptop', 'Notebook', 'Portable', 'Desktop', 'All In One', 'Tower');
            $formatData = UTIL_CompareValues($staticArray, $data, $totalDevice);
            break;
        case 'chassismake':
            $staticArray = array('Hewlett-Packard', 'HP', 'Dell', 'LENOVO', 'Apple');
            $formatData = UTIL_CompareValues($staticArray, $data, $totalDevice);
            break;
        case 'osname':
            $staticArray = array('Windows', 'MAC OS', 'Android', 'Linux', 'iOS');
            $formatData = UTIL_CompareValues($staticArray, $data, $totalDevice);
            break;
        default:
            break;
    }
    return $formatData;
}



function UTIL_CompareValues($staticArray, $compareArray, $totalDevice)
{

    $returnData = [];

    foreach ($staticArray as $value) {
        $returnData[$value] = 0;
    }
    $returnData['others'] = 0;
    $returnData['total'] = 0;
    foreach ($compareArray as $key => $value) {
        $flag = true;
        foreach ($staticArray as $value1) {
            if (stripos($key, $value1) !== false) {
                $returnData[$value1] += intval($value);
                $flag = false;
            }
        }
        if ($flag) {
            $returnData['others'] += intval($value);
        }
        $returnData['total'] += intval($value);
    }
    if ($returnData['total'] < $totalDevice) {
        $returnData['others'] += $totalDevice - $returnData['total'];
        $returnData['total'] = $totalDevice;
    }
    return $returnData;
}

function UTIL_FormatCompListData($data)
{

    global $API_enable_comp;
    $dbusage = $_SESSION["user"]["usage"];
    if ($dbusage == 1 && $API_enable_comp == 1) {
        $lival = '';
        $tempArr = array();
        $i = 0;
        foreach ($data['leftData'] as $key => $value) {
            $tempArr[$i]['itemid'] = $value['itemid'];
            $tempArr[$i]['itemtype'] = $value['itemtype'];
            $tempArr[$i]['status'] = $value['status'];
            $i++;
            $lival .= '<li onclick="reloadComplianceData(' . $value['itemid'] . ', \'' . $value['itemtype'] . '\', \'' . $value['status'] . '\', this)"><a href="#" title="' . $value['itemname'] . '"> ' . $value['itemname'] . '</a></li>';
        }
        if (safe_count($value['itemid']) == 0) {
            $lival = '<span>No Data Found</span>';
        }
        return $lival . '####' . $tempArr[0]['itemid'] . '####' . $tempArr[0]['itemtype'] . '####' . $tempArr[0]['itemname'];
    } else {
        $lival = '';
        $firstid = $data[0]['itemid'];
        $firstItemType = $data[0]['itemtype'];
        foreach ($data as $key => $value) {
            $lival .= '<li onclick="reloadComplianceData(' . $value['itemid'] . ', \'' . $value['itemtype'] . '\', \'' . $value['status'] . '\', this)"><a href="#" title="' . $value['name'] . '"> ' . $value['name'] . '</a></li>';
        }
        if (safe_count($data) == 0) {
            $lival = '<span>No Data Found</span>';
        }
        return $lival . '####' . $firstid . '####' . $firstItemType . '####' . $value['name'];
    }
}

function UTIL_FormatComplianceCalendarMonthGraph($data)
{
    $html = '';
    $listdata = '';
    $title = "";
    $class = "";
    $yearnum = $data['year'];
    $monthnum = $data['month'];
    foreach ($data['list'] as $row1 => $value1) {

        switch ($row1) {
            case "Li5":
                $title = "Availability";
                $class = "app-error";
                break;
            case "Li7":
                $title = "Security";
                $class = "network-error";
                break;
            case "Li8":
                $title = "Resources";
                $class = "network-error";
                break;
            case "Li9":
                $title = "Events";
                $class = "network-error";
                break;
            case "Li10":
                $title = "Maintenance";
                $class = "network-error";
                break;
        }
        $listdata .= '<div class="' . $class . '">';
        $listdata .= '<h5>' . $title . ' <span>(NEW_COUNT)</span></h5>';
        $listdata .= '<ul>';
        $NEW_COUNT = 0;
        foreach ($value1 as $eventname) {
            $listdata .= '<li title="' . $eventname . '"><a href="javascript:;">' . $eventname . '</a></li>';
            $NEW_COUNT++;
        }
        $listdata = str_replace("NEW_COUNT", $NEW_COUNT, $listdata);
        $listdata .= '</ul>';
        $listdata .= '</div>';
    }
    $mon = 1;
    foreach ($data['html'] as $key1 => $val1) {
        $maketime = mktime(0, 0, 10, $mon, 1, $yearnum);
        $weeklist5 = '<ul class="clearfix">
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                        </ul>';
        $weeklist6 = '<ul class="clearfix">
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                        </ul>';
        if ($key1 == "October" || $key1 == "July") {
            $weekclass = " six-week";
            $liststyle = $weeklist6;
        } else {
            $weekclass = " five-week";
            $liststyle = $weeklist5;
        }
        $html .= '<div class="notification-slide">
                    <div class="date-line-box ' . $weekclass . '">
                        <span class="date">
                        <a href="javascript:;" onclick="Get_ComplianceCalendarWeekGraph(' . $maketime . ');">
                            <i class="icon-ic_keyboard_arrow_down_24px material-icons"></i>
                        </a>' . $key1 . '</span>
                        ' . $liststyle . '
                    </div>';
        foreach ($val1 as $key2 => $val2) {
            $html .= '<div class="notification-box ' . $weekclass . '">';
            foreach ($val2 as $key3 => $val3) {
                $html .= '<ul class="clearfix">';
                $count = safe_count($val3);
                foreach ($val3 as $key4 => $val4) {
                    $html .= '<li>' . $val4 . '</li>';
                }
                $html .= '</ul>';
            }

            $html .= '' . $liststyle . '</div>';
        }
        $html .= "</div>";
        $mon++;
    }
    return array($html, $listdata, $yearnum);
}

function UTIL_FormatComplianceCalendarWeekGraph($data)
{
    $html = '';
    $listdata = '';
    $iVal = 1;
    $title = "";
    $class = "";
    $monthN = $data['monthnumber'];
    $yearN = $data['year'];
    $monthyear = $data['month'];
    foreach ($data['list'] as $row1 => $value1) {

        switch ($row1) {
            case "Li5":
                $title = "Availability";
                $class = "app-error";
                break;
            case "Li7":
                $title = "Security";
                $class = "network-error";
                break;
            case "Li8":
                $title = "Resources";
                $class = "network-error";
                break;
            case "Li9":
                $title = "Events";
                $class = "network-error";
                break;
            case "Li10":
                $title = "Maintenance";
                $class = "network-error";
                break;
        }
        $listdata .= '<div class="' . $class . '">';
        $listdata .= '<h5>' . $title . ' <span>(NEW_COUNT)</span></h5>';
        $listdata .= '<ul>';
        $NEW_COUNT = 0;
        foreach ($value1 as $eventname) {
            $listdata .= '<li title="' . $eventname . '"><a href="javascript:;">' . $eventname . '</a></li>';
            $NEW_COUNT++;
        }
        $listdata = str_replace("NEW_COUNT", $NEW_COUNT, $listdata);
        $listdata .= '</ul>';
        $listdata .= '</div>';
    }

    $dayN = 1;
    foreach ($data['html'] as $key1 => $val1) {
        $maketime1 = mktime(0, 0, 10, $monthN, $dayN, $yearN);
        $html .= '<div class="notification-slide">
                    <div class="date-line-box week-view">
                        <span class="date">
                            <a href="javascript:;" onclick="Get_ComplianceCalendarDailyGraph(' . $maketime1 . ');">
                                <i class="icon-ic_keyboard_arrow_down_24px material-icons"></i>
                            </a>
                            Week ' . $iVal . '
                            <a href="javascript:;" onclick="Get_ComplianceCalendarMonthGraph();">
                                <i class="icon-ic_keyboard_arrow_up_24px material-icons"></i>
                            </a>
                        </span>
                        <ul class="clearfix">
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                        </ul>
                    </div>';
        foreach ($val1 as $key2 => $val2) {
            $html .= '<div class="notification-box week-view">';
            foreach ($val2 as $key3 => $val3) {
                $html .= '<ul class="clearfix">';
                $count = safe_count($val3);
                foreach ($val3 as $key4 => $val4) {
                    $html .= '<li>' . $val4 . '</li>';
                }
                $html .= '</ul>';
            }
            $html .= '<ul class="clearfix">
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                        </ul>';
            $html .= '</div>';
        }
        $html .= "</div>";
        $iVal++;
        $dayN = $dayN + 7;
    }
    return array($html, $listdata, $monthyear);
}

function UTIL_FormatComplianceCalendarDailyGraph($data)
{
    $html = '';
    $listdata = '';
    $firstday = $data['firstday'];
    $iVal = date("j", $firstday);
    $title = "";
    $class = "";
    $monthyear = $data['month'];
    $monthnum = $data['monthnum'];
    $yearnum = 2017;
    foreach ($data['list'] as $row1 => $value1) {

        switch ($row1) {
            case "Li5":
                $title = "Availability";
                $class = "app-error";
                break;
            case "Li7":
                $title = "Security";
                $class = "network-error";
                break;
            case "Li8":
                $title = "Resources";
                $class = "network-error";
                break;
            case "Li9":
                $title = "Events";
                $class = "network-error";
                break;
            case "Li10":
                $title = "Maintenance";
                $class = "network-error";
                break;
        }
        $listdata .= '<div class="' . $class . '">';
        $listdata .= '<h5>' . $title . ' <span>(NEW_COUNT)</span></h5>';
        $listdata .= '<ul>';
        $NEW_COUNT = 0;
        foreach ($value1 as $eventname) {
            $listdata .= '<li title="' . $eventname . '"><a href="javascript:;">' . $eventname . '</a></li>';
            $NEW_COUNT++;
        }
        $listdata = str_replace("NEW_COUNT", $NEW_COUNT, $listdata);
        $listdata .= '</ul>';
        $listdata .= '</div>';
    }

    foreach ($data['html'] as $key1 => $val1) {
        $maketime = mktime(0, 0, 10, $monthnum, 1, $yearnum);
        $html .= '<div class="notification-slide">
                    <div class="date-line-box">
                        <span class="date">' . $iVal . '-' . $monthnum . '-' . $yearnum . '<a href="javascript:;" onclick="Get_ComplianceCalendarWeekGraph(' . $maketime . ');">
                                <i class="icon-ic_keyboard_arrow_up_24px material-icons"></i>
                            </a>
                        </span>
                        <ul class="clearfix">
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                        </ul>
                    </div>';
        foreach ($val1 as $key2 => $val2) {
            $html .= '<div class="notification-box">';
            foreach ($val2 as $key3 => $val3) {
                $html .= '<ul class="clearfix">';
                $count = safe_count($val3);
                foreach ($val3 as $key4 => $val4) {
                    $html .= '<li>' . $val4 . '</li>';
                }
                $html .= '</ul>';
            }
            $html .= '<ul class="clearfix">
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                            <li></li>
                        </ul>';
            $html .= '</div>';
        }
        $html .= "</div>";
        $iVal++;
    }
    return array($html, $listdata, $monthyear);
}

function UTIL_FormatCompDetailData($data, $draw)
{
    $complianceDetails = [];
    $totalRecords = safe_count($data);
    foreach ($data as $value) {
        $checkBox = '<div class="form-group"><div class="checkbox"><label><input type="checkbox" class="user_check gridcheck" value="' . $value['itemtype'] . "_" . $value['itemid'] . "_" . $value['status'] . '" id="' . $value['id'] . "_" . $value['itemtype'] . "_" . $value['itemid'] . '_rc" name="' . $value['id'] . "_" . $value['itemtype'] . "_" . $value['itemid'] . '_rc" /><span class="checkbox-material"><span class="check"></span></span></label></div></div>';
        $time = date("m/d/Y h:i A", $value['clienttime']);
        $host = '<p class="ellipsis" title="' . $value['host'] . '">' . $value['host'] . '</p>';
        $complianceDetails[] = array("checkbox-btn" => $checkBox, "machine" => $host, "servertime" => $time, "eventcount" => $value['eventcount']);
    }
    $jsonData = array("draw" => $draw, "recordsTotal" => $totalRecords, "recordsFiltered" => $totalRecords, "data" => $complianceDetails);
    return $jsonData;
}

function UTIL_FormatAllCompDetailData($machWiseCompCount)
{

    $complianceDetails = [];

    foreach ($machWiseCompCount as $key => $value) {

        $time = date("m/d/Y h:i A", $value['last']);
        $radioButton = '<div class="radio-btn ' . $checked . '"><input type="radio" class="user_check gridcheck"  value="' . $value['host'] . '" id="' . $value['id'] . '_rc" name="rc1" onclick="loadComplianceItem(\'' . $key . '\',\'' . $value['id'] . '\',this);" /><label for="' . $value['id'] . '_rc" onclick></label></div>';
        if ($value['alert'] > 0) {
            $statusClass = '<span style="color:#c80c02">No</span>';
        } else if ($value['warning'] > 0 && $value['alert'] == 0) {
            $statusClass = '<span style="color:#c80c02">No</span>';
        } else if ($value['warning'] == 0 && $value['alert'] == 0) {
            $statusClass = '<span style="color:#13933b">Yes</span>';
        } else {
            $statusClass = '<span style="color:#13933b">Yes</span>';
        }

        $warning = $value['warning'];
        $alert = $value['alert'];
        $host = '<p class="text-overflow" title="' . $key . '">' . $key . '</p>';

        $complianceDetails[] = array($radioButton, $host, $warning, $alert, $time, $statusClass);
    }
    return $complianceDetails;
}

function UTIL_CountMachComplainceStat($data, $machRprtData)
{

    $machineWiseCompCount = [];
    foreach ($data as $key => $value) {
        if ($value['status'] == '3') {
            if (isset($machineWiseCompCount[$value['id']]['alert'])) {
                $machineWiseCompCount[$value['id']]['alert']++;
            } else {
                $machineWiseCompCount[$value['id']]['alert'] = 1;
            }
        } else if ($value['status'] == '2') {
            if (isset($machineWiseCompCount[$value['id']]['warning'])) {
                $machineWiseCompCount[$value['id']]['warning']++;
            } else {
                $machineWiseCompCount[$value['id']]['warning'] = 1;
            }
        }
    }
    return $machineWiseCompCount;
}

function UTIL_CountAllMachComplainceStat($data, $machRprtData)
{

    foreach ($data as $key => $value) {
        if ($value['status'] == '3') {
            $machRprtData[$value['host']]['alert']++;
        } else if ($value['status'] == '2') {
            $machRprtData[$value['host']]['warning']++;
        }
    }
    return $machRprtData;
}

function UTIL_GetItemId($db, $tableName, $name)
{

    $itemids = [];
    $nameList = '';
    foreach ($name as $value) {
        array_push($itemids, $value);
    }
    $in  = str_repeat('?,', safe_count($itemids) - 1) . '?';
    $sql = $db->prepare("select eventitemid,name from " . $GLOBALS['PREFIX'] . "dashboard.$tableName"
        . " where name in ($in)");
    $sql->execute($itemids);
    $itemIds = $sql->fetchAll();

    return $itemIds;
}

function UTIL_GetHomeTilesData($db, $complianceData)
{

    $tilesDataCount = [];
    $names = array('Hard Disk Failure', 'Processor Failure', 'CPU Failure', 'RAM Failure', 'Battery Failure', 'Battery Critical', 'Firewall Disabled', 'Multiple Antivirus Installed', 'No Anti-Spyware Installed', 'Anti Virus not up-to-date', 'Device Ram less than 10%', 'Device Battery less than 10%', 'Device Disk space less than 20%', 'Windows Update Disable', 'No Virus Protection', 'Mother Board Failure');
    $tableName = 'EventItems';
    $itemId = UTIL_GetItemId($db, $tableName, $names);

    foreach ($names as $value) {
        $tilesDataCount[$value] = 0;
    }

    foreach ($itemId as $value) {
        $itemIds[] = $value['eventitemid'];
    }
    foreach ($complianceData as $value) {
        if (in_array($value['itemid'], $itemIds)) {
            $tilesDataCount[$value['name']]++;
        }
    }
    return $tilesDataCount;
}

function UTIL_FormatSecurityData($db, $data, $map)
{

    $securityDetails = [];

    $names = array('Firewall Disabled', 'Multiple Antivirus Installed', 'Anti Virus not up-to-date', 'Windows Update Disable', 'No Virus Protection');
    $tableName = 'EventItems';
    $itemId = UTIL_GetItemId($db, $tableName, $names);
    $issueHosts = [];
    foreach ($itemId as $value) {
        $itemIds[] = $value['eventitemid'];
    }

    foreach ($data as $value) {
        if (in_array($value['itemid'], $itemIds)) {
            $issueHosts[] = $value['id'];
            $time = date("m/d/Y h:i A", $value['clienttime']);
            $host = '<p class="text-overflow" title="' . $value['host'] . '">' . $value['host'] . '</p>';
            $itemName = '<p class="text-overflow" title="' . $value['name'] . '">' . $value['name'] . '</p>';
            $hostName = '<p class="text-overflow" title="' . $map[$value['id']][1] . '">' . $map[$value['id']][1] . '</p>';
            $make = '<p class="text-overflow" title="' . $map[$value['id']][2] . '">' . $map[$value['id']][2] . '</p>';

            $securityDetails[] = array($host, $hostName, $make, $itemName, $time);
        }
    }

    foreach ($map as $key => $value) {
        if (!in_array($key, $issueHosts)) {
            $securityDetails[] = array('<p class="text-overflow" title="' . $value[0] . '">' . $value[0] . '</p>', '<p class="text-overflow" title="' . $value[1] . '">' . $value[1] . '</p>', '<p class="text-overflow" title="' . $value[2] . '">' . $value[2] . '</p>', "No Issue Reported", "-");
        }
    }
    return $securityDetails;
}

function UTIL_FormatHealthData($db, $data, $map)
{

    $healthDetails = [];

    $names = array('Hard Disk Failure', 'CPU Failure', 'RAM Failure', 'Battery Failure', 'Battery Critical', 'Mother Board Failure');
    $tableName = 'EventItems';
    $itemId = UTIL_GetItemId($db, $tableName, $names);

    foreach ($itemId as $value) {
        $itemIds[] = $value['eventitemid'];
    }

    $checkMid = 0;
    $hardDisk = 'Normal';
    $cpu = 'Normal';
    $ram = 'Normal';
    $battery = 'Normal';
    $motherB = 'Normal';
    $serialNo = '';
    $host = '';
    $make = '';
    $issueHosts = [];
    foreach ($data as $value) {
        if (in_array($value['itemid'], $itemIds)) {
            $issueHosts[] = $value['id'];
            if ($checkMid == 0) {
                $checkMid = $value['id'];
            } else {
                if ($value['id'] != $checkMid) {
                    $checkMid = $value['id'];
                    $healthDetails[] = array($host, $serialNo, $make, $hardDisk, $motherB, $cpu, $ram, $battery);
                    $hardDisk = 'Normal';
                    $cpu = 'Normal';
                    $ram = 'Normal';
                    $battery = 'Normal';
                    $motherB = 'Normal';
                    $serialNo = '';
                    $host = '';
                }
            }
            $host = '<p class="text-overflow" title="' . $value['host'] . '">' . $value['host'] . '</p>';
            $hostName = '<p class="text-overflow" title="' . $map[$value['id']][1] . '">' . $map[$value['id']][1] . '</p>';
            $make = '<p class="text-overflow" title="' . $map[$value['id']][2] . '">' . $map[$value['id']][2] . '</p>';

            switch ($value['name']) {
                case 'Hard Disk Failure':
                    $hardDisk = 'At risk';
                    break;
                case 'CPU Failure':
                    $cpu = 'At risk';
                    break;
                case 'RAM Failure':
                    $ram = 'At risk';
                    break;
                case 'Battery Failure':
                    $battery = 'Failed';
                    break;
                case 'Battery Critical':
                    $battery = 'At risk';
                    break;
                case 'Mother Board Failure':
                    $motherB = 'At risk';
                    break;
                default:
                    break;
            }
        }
    }
    if ($host != '') {
        $healthDetails[] = array($host, $hostName, $make, $hardDisk, $motherB, $cpu, $ram, $battery);
    }
    $hardDisk = 'Normal';
    $cpu = 'Normal';
    $ram = 'Normal';
    $battery = 'Normal';
    $motherB = 'Normal';
    foreach ($map as $key => $value) {
        if (!in_array($key, $issueHosts)) {
            $healthDetails[] = array('<p class="text-overflow" title="' . $value[0] . '">' . $value[0] . '</p>', '<p class="text-overflow" title="' . $value[1] . '">' . $value[1] . '</p>', '<p class="text-overflow" title="' . $value[2] . '">' . $value[2] . '</p>', $hardDisk, $motherB, $cpu, $ram, $battery);
        }
    }
    return $healthDetails;
}

function UTIL_GetReportDuration($reportDate)
{

    switch ($reportDate) {
        case 1:
            $start = strtotime("1 january " . date("Y"));
            $end = strtotime("30 january " . date("Y"));
            break;
        case 2:
            $start = strtotime("1 february " . date("Y"));
            $end = strtotime("28 february " . date("Y"));
            break;
        case 3:
            $start = strtotime("1 march " . date("Y"));
            $end = strtotime("30 march " . date("Y"));
            break;
        case 4:
            $start = strtotime("1 april " . date("Y"));
            $end = strtotime("30 april " . date("Y"));
            break;
        case 5:
            $start = strtotime("1 may " . date("Y"));
            $end = strtotime("30 may " . date("Y"));
            break;
        case 6:
            $start = strtotime("1 june " . date("Y"));
            $end = strtotime("30 june " . date("Y"));
            break;
        case 7:
            $start = strtotime("1 july " . date("Y"));
            $end = strtotime("30 july ");
            break;
        case 8:
            $start = strtotime("1 august " . date("Y"));
            $end = strtotime("30 august " . date("Y"));
            break;
        case 9:
            $start = strtotime("1 september " . date("Y"));
            $end = strtotime("30 september ");
            break;
        case 10:
            $start = strtotime("1 october " . date("Y"));
            $end = strtotime("30 october " . date("Y"));
            break;
        case 11:
            $start = strtotime("1 november " . date("Y"));
            $end = strtotime("30 november " . date("Y"));
            break;
        case 12:
            $start = strtotime("1 december " . date("Y"));
            $end = strtotime("30 december " . date("Y"));
            break;
        default:
            break;
    }
    return array($start, $end);
}

function UTIL_FormatSummaryData($summaryData)
{

    $summaryDetails = [];
    $datawiseCount = [];
    $summaryDetail = [];

    for ($i = 1; $i <= 31; $i++) {
        $datawiseCount[$i] = 0;
    }


    $type1 = array('System Error Events', 'BSOD Errors', 'Hard Disk Failure', 'CPU Failure', 'RAM Failure', 'Battery Failure');
    $type2 = array('AV Not Installed', 'Firewall OFF', 'More than 1 AV Installed', 'Antispyware OFF', 'More than 2 AS Installed', 'Firewall Disabled', 'Multiple Antivirus Installed', 'Anti Virus not up-to-date', 'Windows Update Disable', 'No Virus Protection');
    $type3 = array('Device Ram less than 10%', 'Device Disk space less than 20%');

    foreach ($summaryData as $value) {

        $time = date("m/d/Y", $value['servertime']);
        $day = date("j", $value['servertime']);

        if (in_array($value['name'], $type1)) {
            $summaryDetail[] = array($time, 'Device health', $value['host'], '<p class="text-overflow" title="' . $value['name'] . '">' . $value['name'] . '</p>');
            $datawiseCount[$day]++;
        } elseif (in_array($value['name'], $type2)) {
            $summaryDetail[] = array($time, 'Security', $value['host'], '<p class="text-overflow" title="' . $value['name'] . '">' . $value['name'] . '</p>');
            $datawiseCount[$day]++;
        } elseif (in_array($value['name'], $type3)) {
            $summaryDetail[] = array($time, 'Device capacity', $value['host'], '<p class="text-overflow" title="' . $value['name'] . '">' . $value['name'] . '</p>');
            $datawiseCount[$day]++;
        } else {
            $summaryDetail[] = array($time, 'others', $value['host'], '<p class="text-overflow" title="' . $value['name'] . '">' . $value['name'] . '</p>');
            $datawiseCount[$day]++;
        }
    }
    $summaryDetails['gridData'] = $summaryDetail;
    $summaryDetails['graphData'] = implode(",", $datawiseCount);
    return $summaryDetails;
}

function UTIL_FormatGraphData($entitleData)
{

    $totalRecords = safe_count($entitleData);

    $warrantyval = 0;
    $outwarranty = 0;
    $carepackval = 0;
    if (safe_count($totalRecords) > 0) {

        foreach ($entitleData as $key => $value) {
            $typeCode = $value['obligationTypeCode'];
            $obligationStatus = $value['obligationStatus'];
            if ($typeCode == 'W' && ($obligationStatus == 1 || $obligationStatus == '1')) {
                $warrantyval++;
            } elseif ($typeCode == 'P' && ($obligationStatus == 1 || $obligationStatus == '1')) {
                $carepackval++;
            } elseif ($typeCode == 'W' && ($obligationStatus == 0 || $obligationStatus == '0')) {
                $outwarranty++;
            }
        }
        $ret = array("carepack" => $carepackval, "warranty" => $warrantyval, "outWarranty" => $outwarranty);
    } else {
        $ret = array("carepack" => 0, "warranty" => 0, "outWarranty" => 0);
    }
    return $ret;
}

function UTIL_MakeCapacityCountArray($ramcount, $diskcount)
{
    $countArray = array('Device Ram less than 10%' => (int) $ramcount, 'Device Disk space less than 20%' => (int) $diskcount);
    return $countArray;
}

function UTIL_AppendPTag($value)
{
    return '<p class="text-overflow" data-placement="left" data-toggle="tooltip" title=\'' . $value . '\'>' . $value . '</p>';
}

function UTIL_GetdateLabelForMonth()
{

    $date = date("M d");
    $onemonth = strtotime($date) - (15 * 24 * 60 * 60);
    $datelabel = '';
    $total = 15;
    $i = 1;
    while ($i <= $total) {
        $datelabel[] = date("M d", $onemonth + ($i * 24 * 60 * 60));
        $i += 1;
    }
    return $datelabel;
}

function UTIL_FormatAssetDataJson($data, $datanames, $machineMap, $fp)
{

    $parsedata = [];
    $time = time();

    foreach ($datanames as $value) {
        $temp[$value] = 'NA';
    }


    foreach ($data as $key => $val) {
        if ($checkMid == 0) {
            $checkMid = $val['machineid'];
        } else {
            if ($val['machineid'] != $checkMid) {
                $parsedata[$machineMap[$checkMid][2]][$machineMap[$checkMid][0]]['last Sync Date'] = $machineMap[$checkMid][1];
                $parsedata[$machineMap[$checkMid][2]][$machineMap[$checkMid][0]]['details'] = $temp;
                $checkMid = $val['machineid'];
                foreach ($datanames as $value) {
                    $temp[$value] = 'NA';
                }
            }
        }
        $temp[$datanames[$val['dataid']]] = $val['value'];
    }

    UTIL_WrtJsonFile("{", $fp);
    UTIL_WrtJsonFile('"content" : "Basic Information",', $fp);
    UTIL_WrtJsonFile('"Last Collected Date" : "' . $time . '",', $fp);
    UTIL_WrtJsonFile('"details" :', $fp);
    UTIL_WrtJsonFile(json_encode($parsedata), $fp);
    UTIL_WrtJsonFile("}", $fp);
}

function UTIL_FormatAssetMulDataJson($data, $datanames, $machineMap, $assetType, $fp)
{

    $checkOrd = 0;
    $parsedata = [];
    $temp = [];
    $i = 0;
    $time = time();

    foreach ($data as $key => $val) {
        if ($checkMid == 0) {
            $checkMid = $val['machineid'];
        } else {
            if ($val['machineid'] != $checkMid) {
                $parsedata[$machineMap[$checkMid][2]][$machineMap[$checkMid][0]]['last Sync Date'] = $machineMap[$checkMid][1];
                $parsedata[$machineMap[$checkMid][2]][$machineMap[$checkMid][0]]['details'] = $temp;
                $checkMid = $val['machineid'];
                $temp = [];
                $i = 0;
                $checkOrd = 0;
            }
        }
        if ($checkOrd == 0) {
            $checkOrd = $val['ordinal'];
            $i = 0;
        } else {
            if ($val['ordinal'] != $checkOrd) {
                $i++;
                $checkOrd = $val['ordinal'];
            }
        }

        $temp[$i][$datanames[$val['dataid']]] = utf8_encode($val['value']);
    }

    UTIL_WrtJsonFile("{", $fp);
    UTIL_WrtJsonFile('"content" : "' . $assetType . ' Information",', $fp);
    UTIL_WrtJsonFile('"Last Collected Date" : "' . $time . '",', $fp);
    UTIL_WrtJsonFile('"' . $assetType . ' Details" :', $fp);
    UTIL_WrtJsonFile(json_encode($parsedata), $fp);
    UTIL_WrtJsonFile("}", $fp);
}

function UTIL_FormatAssetData($data, $datanames, $restrict, $machineMap)
{

    $checkMid = 0;
    $parsedata = [];
    $i = 0;

    if (!empty($data)) {
        foreach ($restrict as $value) {
            $temp[$value] = 'NA';
        }

        foreach ($data as $key => $val) {
            if ($checkMid == 0) {
                $checkMid = $val['machineid'];
            } else {
                if ($val['machineid'] != $checkMid) {
                    $parsedata[$i][] = $temp;
                    $i++;
                    $checkMid = $val['machineid'];
                    foreach ($restrict as $value) {
                        $temp[$value] = 'NA';
                    }
                }
            }
            if (in_array($datanames[$val['dataid']], $restrict)) {
                $temp['hostName'] = $machineMap[$checkMid][0];
                $temp[$datanames[$val['dataid']]] = utf8_encode($val['value']);
            }
        }
        $parsedata[$i][] = $temp;
    }
    return $parsedata;
}

function UTIL_FormatAssetMulData($data, $datanames, $machineMap)
{

    $checkOrd = 0;
    $checkMid = 0;
    $parsedata = [];
    $temp = [];
    $i = 0;

    foreach ($data as $key => $val) {
        if ($checkMid == 0) {
            $checkMid = $val['machineid'];
        } else {
            if ($val['machineid'] != $checkMid) {

                $parsedata[] = $temp;
                $checkMid = $val['machineid'];
                $temp = [];
                $i = 0;
                $checkOrd = 0;
            }
        }
        if ($checkOrd == 0) {
            $checkOrd = $val['ordinal'];
            $i = 0;
        } else {
            if ($val['ordinal'] != $checkOrd) {
                $i++;
                $checkOrd = $val['ordinal'];
            }
        }
        $temp[$i]['host'] = $machineMap[$checkMid][0];
        $temp[$i][$datanames[$val['dataid']]] = utf8_encode($val['value']);
    }

    $parsedata[] = $temp;
    return $parsedata;
}

function UTIL_FormatEventDataJson($data, $month, $fp)
{

    UTIL_WrtJsonFile("{", $fp);
    UTIL_WrtJsonFile('"content" : "event",', $fp);
    UTIL_WrtJsonFile('"month" : "' . $month . '",', $fp);


    UTIL_WrtJsonFile('"details" :', $fp);
    UTIL_WrtJsonFile(json_encode($data), $fp);

    UTIL_WrtJsonFile("}", $fp);
}

function UTIL_WrtJsonFile($data, $fp)
{
    fwrite($fp, $data);
}



function UTIL_CreateAssetInfoJson($parsedata)
{

    $i = 0;
    $tempArray = [];
    if (!empty($parsedata)) {
        foreach ($parsedata as $key => $value) {
            foreach ($value as $key1 => $value1) {
                $hostName = isset($value1['hostName']) ? $value1['hostName'] : 'NA';
                $userName = isset($value1["User Name"]) ? $value1["User Name"] : "NA";
                $chassType = isset($value1["Chassis Type"]) ? $value1["Chassis Type"] : "NA";
                $chassMan = isset($value1["Chassis Manufacturer"]) ? $value1["Chassis Manufacturer"] : "NA";
                $opeSystm = isset($value1["Operating System"]) ? $value1["Operating System"] : "NA";

                $tempArray[$i][] = '<p class="ellipsis" title="' . $hostName . '" >' . $hostName . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $userName . '" >' . $userName . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $chassType . '" >' . $chassType . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $chassMan . '" >' . $chassMan . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $opeSystm . '" >' . $opeSystm . '</p>';
                $i++;
            }
        }
    }

    return $tempArray;
}

function UTIL_CreateSoftInfoJson($parsedata)
{

    $hostName = '';
    $userName = '';
    $i = 0;
    foreach ($parsedata as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $key1 => $value1) {
                if ($value1["User Name"]) {
                    $userName = $value1["User Name"];
                }
                $hostName = isset($value1['host']) ? $value1['host'] : '';
                $softName = isset($value1["Installed Software Names"]) ? $value1["Installed Software Names"] : "NA";
                $instDate = isset($value1["Installation Date"]) ? $value1["Installation Date"] : "NA";
                $version = isset($value1["Version"]) ? $value1["Version"] : "NA";

                $tempArray[$i][] = '<p class="ellipsis" title="' . $hostName . '" >' . $hostName . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $userName . '" >' . $userName . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $softName . '" >' . $softName . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $instDate . '" >' . $instDate . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $version . '" >' . $version . '</p>';
                $i++;
            }
        } else {
            $hostName = $value;
        }
    }
    return $tempArray;
}

function UTIL_CreatePatchInfoJson($parsedata)
{

    $hostName = '';
    $userName = '';
    $i = 0;
    foreach ($parsedata as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $key1 => $value1) {
                if ($value1["User Name"]) {
                    $userName = $value1["User Name"];
                }
                $hostName = isset($value1['host']) ? $value1['host'] : '';
                $descName = isset($value1["Description Name"]) ? $value1["Description Name"] : "NA";
                $instDate = isset($value1["Installed On"]) ? $value1["Installed On"] : "NA";
                $kbid = isset($value1["KB ID"]) ? $value1["KB ID"] : "NA";

                $tempArray[$i][] = '<p class="ellipsis" title="' . $hostName . '" >' . $hostName . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $userName . '" >' . $userName . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $descName . '" >' . $descName . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $instDate . '" >' . $instDate . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $kbid . '" >' . $kbid . '</p>';
                $i++;
            }
        } else {
            $hostName = $value;
        }
    }

    return $tempArray;
}

function UTIL_CreateResourceInfoJson($parsedata)
{

    $hostName = '';
    $userName = '';
    $i = 0;

    foreach ($parsedata as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $key1 => $value1) {

                $diskTotal = intval(str_replace(',', '', explode(' ', $value1["Logical Disk KBytes Total"])[0]));
                $diskUsed  = intval(str_replace(',', '', explode(' ', $value1["Logical Disk KBytes Used"])[0]));

                $hostName = isset($value1['host']) ? $value1['host'] : '';
                $driveName = isset($value1["Logical Disk Name"]) ? $value1["Logical Disk Name"] : "NA";
                $driveTotal = isset($value1["Logical Disk KBytes Total"]) ? floor($diskTotal / 1048576) : "NA";
                $driveUsed = isset($value1["Logical Disk KBytes Used"]) ? floor($diskUsed / 1048576) : "NA";
                $freeSpace = ($driveTotal != 'NA') ? ($driveTotal - $driveUsed) : 0;

                $tempArray[$i][] = '<p class="ellipsis" title="' . $hostName . '" >' . $hostName . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $driveName . '" >' . $driveName . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $driveTotal . '" >' . $driveTotal . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $driveUsed . '" >' . $driveUsed . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $freeSpace . '" >' . $freeSpace . '</p>';
                $i++;
            }
        } else {
            $hostName = $value;
        }
    }
    return $tempArray;
}

function UTIL_CreateNetworkInfoJson($parsedata)
{
    $hostName = '';
    $userName = '';
    $i = 0;

    foreach ($parsedata as $key => $value) {
        if (is_array($value)) {
            foreach ($value as $key1 => $value1) {
                $userName = isset($value1["User Name"]) ? $value1["User Name"] : "NA";
                $hostName = isset($value1['host']) ? $value1['host'] : '';
                $domain = isset($value1["Domain"]) ? $value1["Domain"] : "NA";
                $ipaddr = isset($value1["IP address"]) ? $value1["IP address"] : "NA";
                $macaddr = isset($value1["MAC address"]) ? $value1["MAC address"] : "NA";

                $tempArray[$i][] = '<p class="ellipsis" title="' . $hostName . '" >' . $hostName . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $userName . '" >' . $userName . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $domain . '" >' . $domain . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $ipaddr . '" >' . $ipaddr . '</p>';
                $tempArray[$i][] = '<p class="ellipsis" title="' . $macaddr . '" >' . $macaddr . '</p>';
                $i++;
            }
        } else {
            $hostName = $value;
        }
    }
    return $tempArray;
}

function UTIL_FormatSummaryExprtData($summaryData)
{

    $summaryDetail = [];

    $type1 = array('Hard Disk Failure', 'CPU Failure', 'RAM Failure', 'Battery Failure');
    $type2 = array('Firewall Disabled', 'Multiple Antivirus Installed', 'Anti Virus not up-to-date', 'Windows Update Disable', 'No Virus Protection');
    $type3 = array('Device Ram less than 10%', 'Device Disk space less than 20%');

    foreach ($summaryData as $value) {

        $time = date("m/d/Y", $value['servertime']);

        if (in_array($value['name'], $type1)) {
            $summaryDetail[] = array($time, 'Device health', $value['host'], $value['name']);
        } elseif (in_array($value['name'], $type2)) {
            $summaryDetail[] = array($time, 'Security', $value['host'], $value['name']);
        } elseif (in_array($value['name'], $type3)) {
            $summaryDetail[] = array($time, 'Device capacity', $value['host'], $value['name']);
        } else {
            $summaryDetail[] = array($time, 'others', $value['host'], $value['name']);
        }
    }

    return $summaryDetail;
}


function UTIL_GetFilteredGroupName($rawGroupName, $customerNumber)
{

    $groupName = trim($rawGroupName) . '__' . trim($customerNumber);
    return $groupName;
}


function UTIL_GetTrimmedGroupName($rawGroupName)
{
    $groupName = "";
    if (preg_match('__', $rawGroupName)) {
        $tempArray = explode("__", $rawGroupName);
        $groupName = $tempArray[0];
    } else {
        $groupName = $rawGroupName;
    }
    return trim($groupName);
}

function UTIL_GetTrimmedSiteName($tempCompName)
{
    $companyName = '';
    if (strpos($tempCompName, "_") !== false) {
        $tempArray = explode("_", $tempCompName);
        if (is_numeric($tempArray[1])) {
            $companyName = $tempArray[0];
        } else {
            $companyName = $tempCompName;
        }
    } else {
        $companyName = $tempCompName;
    }
    return $companyName;
}



function server_var($name)
{
    if (isset($_SERVER)) {
        if (isset($_SERVER[$name]))
            return $_SERVER[$name];
        else
            return '';
    }
    if (isset($GLOBALS['HTTP_SERVER_VARS'])) {
        if (isset($GLOBALS['HTTP_SERVER_VARS'][$name]))
            return $GLOBALS['HTTP_SERVER_VARS'][$name];
        else
            return '';
    }
    if (isset($GLOBALS[$name])) {
        return $GLOBALS[$name];
    }
    return '';
}

function UTIL_GetUserSiteList($db, $userid)
{

    $sql = $db->prepare("select customerNum,orderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder where siteName in (select C.customer  from " . $GLOBALS['PREFIX'] . "core.Customers C, " . $GLOBALS['PREFIX'] . "core.Users U where C.username=U.username and U.userid = ?) ");
    $sql->execute([$userid]);
    $sqlRes = $sql->fetchAll();
    $custList = '';
    $ordList = '';
    foreach ($sqlRes as $res) {
        $custNo = $res['customerNum'];
        $ordNo = $res['orderNum'];

        $custList .= "'" . $custNo . "',";
        $ordList .= "'" . $ordNo . "',";
    }
    $custList = rtrim($custList, ',');
    $ordList = rtrim($ordList, ',');
    return array("custNo" => $custList, "ordNo" => $ordList);
}


function UTIL_GetExcelFormattedDate($objPHPExcel, $cellValue, $index, $excelRow, $dateFormat)
{
    if ($cellValue != '-' && $cellValue != "" && $cellValue !== 'NA' && $cellValue != 'NULL' && $cellValue != NULL && $cellValue != 'Not Available' && $cellValue != 0 && $cellValue != '0' && $cellValue != '--') {
        $objPHPExcel->getActiveSheet()->setCellValue($excelRow . $index, PHPExcel_Shared_Date::PHPToExcel($cellValue));
        $objPHPExcel->getActiveSheet()->getStyle($excelRow . $index)->getNumberFormat()->setFormatCode($dateFormat);
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue($excelRow . $index, $cellValue);
    }

    return $objPHPExcel;
}

function UTIL_GetTrimCompanyName($companyName)
{
    if (preg_match("/^[-a-zA-Z0-9_]*$/", $companyName)) {
        $split = explode('_', $companyName);
        $num = $split[1];
        if (preg_match("/^[0-9]*$/", $num)) {
            return $split[0];
        } else {
            return $companyName;
        }
    } else {

        return $companyName;
    }
}

function ELPROV_GET_Curl($url, $params)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


    $headers = array();
    $headers[] = "Content-Type: application/json";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorno = curl_errno($ch);

    $result = curl_exec($ch);
    if ($errorno) {
        logElasticOperations($errorno, $result);
        return array();
        exit();
    }
    curl_close($ch);
    return $result;
}


function ELPROV_GET_Curl_Temp($indexName, $params)
{
    logs::log(__FILE__, __LINE__, "Error:CodeRemoved");
}

function ELPROV_GET_RowsCount($url)
{
    $url = $url . "/_count";
    $params = '';
    $ch = curl_init();
    $count = 0;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


    $headers = array();
    $headers[] = "Content-Type: application/json";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorno = curl_errno($ch);

    $result = curl_exec($ch);
    $isValidJson = ELPROV_IsValidJson($result);
    if ($isValidJson) {
        $curlArray = safe_json_decode($result, TRUE);
        if (isset($curlArray['count']) && $curlArray['count'] > 0) {
            $count = $curlArray['count'];
        }
    }
    return $count;
}


function ELPROV_Scroll_Data($indexName, $params, $count)
{
    global $elastic_url;
    $url = $elastic_url . $indexName;

    $ch = curl_init();
    $count = ELPROV_GET_RowsCount($url);

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


    $headers = array();
    $headers[] = "Content-Type: application/json";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorno = curl_errno($ch);

    $result = curl_exec($ch);
    if ($errorno) {
        logElasticOperations($errorno, $result);
        return array();
        exit();
    }
    curl_close($ch);
    return $result;
}

function ELPROV_Get_ScrollId($indexUrl, $params)
{
    $url = $indexUrl . "_search?scroll=2m";
    $result = EL_Make_Curl($url, $params);
    $isValidJson = ELPROV_IsValidJson($result);
    if ($isValidJson) {
        $curlArray = safe_json_decode($result, TRUE);
        if (isset($curlArray['_scroll_id'])) {
            $count = $curlArray['_scroll_id'];
        }
    }

    return $result;
}

function EL_Make_Curl($url, $params)
{
    $ch = curl_init();
    $count = ELPROV_GET_RowsCount($url);

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");


    $headers = array();
    $headers[] = "Content-Type: application/json";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $errorno = curl_errno($ch);

    $result = curl_exec($ch);
    if ($errorno) {
        logElasticOperations($errorno, $result);
        return array();
        exit();
    }
    curl_close($ch);
    return $result;
}

function ELPROV_FORMAT_Curldata($curlResponse)
{
    $isValidJson = ELPROV_IsValidJson($curlResponse);
    $result = [];
    if ($isValidJson) {
        $curlArray = safe_json_decode($curlResponse, TRUE);
        if (isset($curlArray['hits']['total']) && $curlArray['hits']['total'] > 0) {
            return $curlArray['hits']['hits'];
        }
    }
    return $result;
}

function ELPROV_IsValidJson($string)
{
    $result = safe_json_decode($string);
    switch (json_last_error()) {
        case JSON_ERROR_NONE:
            $error = TRUE;
            break;
        case JSON_ERROR_DEPTH:
            $error = 'The maximum stack depth has been exceeded.';
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $error = 'Invalid or malformed JSON.';
            break;
        case JSON_ERROR_CTRL_CHAR:
            $error = 'Control character error, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_SYNTAX:
            $error = 'Syntax error, malformed JSON.';
            break;
        case JSON_ERROR_UTF8:
            $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
            break;
        case JSON_ERROR_RECURSION:
            $error = 'One or more recursive references in the value to be encoded.';
            break;
        case JSON_ERROR_INF_OR_NAN:
            $error = 'One or more NAN or INF values in the value to be encoded.';
            break;
        case JSON_ERROR_UNSUPPORTED_TYPE:
            $error = 'A value of a type that cannot be encoded was given.';
            break;
        default:
            $error = 'Unknown JSON error occured.';
            break;
    }

    if ($error) {
        return TRUE;
    } else {
        return FALSE;
    }
}


function UTIL_CreatPTag($ptag_val)
{
    if ($ptag_val == "" || $ptag_val == "NULL" || $ptag_val == "null" || $ptag_val == NULL || $ptag_val == null) {
        $ptagStr = '<p class="ellipsis">-</p>';
    } else {
        $ptagStr = '<p class="ellipsis" title="' . $ptag_val . '">' . $ptag_val . '</p>';
    }
    return $ptagStr;
}


function UTIL_GetUserSiteList_PDO($pdo, $userid)
{

    $sql_cust = "select customerNum,orderNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder where siteName in (select C.customer  from " . $GLOBALS['PREFIX'] . "core.Customers C, " . $GLOBALS['PREFIX'] . "core.Users U where C.username=U.username and U.userid=?) ";
    $stmt = $pdo->prepare($sql_cust);
    $stmt->execute([$userid]);
    $sqlRes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $custList = '';
    $ordList = '';
    foreach ($sqlRes as $res) {
        $custNo = $res['customerNum'];
        $ordNo = $res['orderNum'];

        $custList .= "'" . $custNo . "',";
        $ordList .= "'" . $ordNo . "',";
    }
    $custList = rtrim($custList, ',');
    $ordList = rtrim($ordList, ',');
    return array("custNo" => $custList, "ordNo" => $ordList);
}



function UTIL_GetSiteScope_PDO($db, $selectedItem, $selectedType)
{
    if ($selectedItem == 'All') {
        $user = $_SESSION['user']['username'];
        $scope = [];
        $key = '';

        switch ($selectedType) {
            case 'Sites':
                $scope = DASH_GetSites_PDO($key, null, $user);
                break;
            case 'Groups':
                $scope = DASH_GetGroups_PDO($key, $db, $user);
                break;
            default:
                break;
        }
        return $scope;
    } else {
        return $selectedItem;
    }
}



function completeSanitize($string)
{
    $payloadsType = array('GROUP BY', 'ORDER BY', 'UNION SELECT', '\'', '\"', 'information_schema.', 'AND FALSE', '/bin/');

    foreach ($payloadsType as $eachPayloadTypes) {
        if (stripos($string, $eachPayloadTypes)) {
            str_ireplace($eachPayloadTypes, '', $string);
        }
    }

    if (is_numeric($string)) $string = (int) $string;

    return $string;
}
