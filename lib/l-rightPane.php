<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-dashboard.php';

function Pane_GetJsonFormat($userName, $customerType)
{
    $key = '';
    $pdo = pdo_connect();
    $json = [];
    $key = true;

    $user = $userName;
    $json['userType'] = $customerType;
    $sites = DASH_GetSites($key, $pdo, $user);

    $views = array('Sites', 'Groups');
    $groups = DASH_GetGroups($key, $pdo, $user);

    $_SESSION['user']['group_list'] = $groups;
    $searchType = $_SESSION['searchType'];
    $selectedSiteName = $_SESSION['searchValue'];
    if ($searchType == 'ServiceTag') {
        $selectedSiteName = $_SESSION['rparentName'];
    }

    foreach ($views as $viewvalue) {
        if ($viewvalue == 'Sites') {
            foreach ($sites as $key1 => $site) {
                $gatewayStatus = 0;
                $site1 = $site . ' ';
                $json['views'][$viewvalue][$site1]['id'] = utf8_encode($site) . "_" . $viewvalue . '@@@' . $gatewayStatus;
            }
        } else if ($viewvalue == 'Groups') {
            $json['views'][$viewvalue] = [];
            $json['views'][$viewvalue] = [];

            $a = 0;
            $gatewayStatus = 0;
            $stylesel = $pdo->query('select * from group_styles');
            $styleres = $stylesel->fetchAll();

            foreach ($styleres as $style) {
                $stylename = $style['style_name'];
                $temp = [];
                $stylename = $stylename . ' ';
                $json['views'][$viewvalue][$stylename]['id'] = utf8_encode($style['style_number']) . "_" . $viewvalue;
            }
        }
    }
    return $json;
}

function Pane_UpdateSessionRgtPane($key, $paneObject)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $_SESSION['searchType'] = $paneObject['searchType'];
        $_SESSION['searchValue'] = $paneObject['searchValue'];
        $_SESSION['rparentName'] = $paneObject['rparentName'];
        $_SESSION['passlevel'] = $paneObject['searchType'];
        if ($paneObject['searchType'] == 'ServiceTag') {
            Pane_MachLevelSession($key, $paneObject);
        }
    }
}

function Pane_MachLevelSession($key, $paneObject)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $_SESSION['rcensusId'] = $paneObject['censusId'];
        $_SESSION['rparentName'] = $paneObject['rparentName'];
        $_SESSION['passlevel'] = $paneObject['passlevel'];
    }
}

function Pane_UpdateSession()
{
    $paneObject['searchType'] = UTIL_GetString('searchType', '');
    $paneObject['searchValue'] = UTIL_GetString('searchValue', '');
    $paneObject['rparentName'] = UTIL_GetString('rparentName', '');
    $paneObject['passlevel'] = UTIL_GetString('passlevel', '');
    $paneObject['censusId'] = UTIL_GetString('rcensusId', '');

    $key = '';
    $key = DASH_ValidateKey($key);
    if ($key) {
        Pane_UpdateSessionRgtPane($key, $paneObject);
    }
}

function Pane_GetSearchMenu($key, $userName)
{

    $db = pdo_connect();
    $json = [];
    $key = DASH_ValidateKey($key);

    $sites = DASH_GetSites($key, $db, $userName);

    foreach ($sites as $site) {
        $machines = DASH_GetMachinesSites($key, $db, $site);

        $machineStatus = DASH_GetAllMachineStatusNOs($key, $db, $machines);

        $temp = [];
        foreach ($machines as $id => $host) {
            $temp[] = array(
                "machine_name" => $host,
                "machine_status" => $machineStatus[$host][0],
                "operating_system" => $machineStatus[$host][1],
                "machine_unique_id" => $id,
            );
        }
        $json[$site] = $temp;
    }
    echo json_encode($json);
}

function Pane_REST_API_GetRightpane($userName, $customerType)
{

    $db = pdo_connect();
    $json = [];
    $key = DASH_ValidateKey($key);

    $user = $userName;
    $json['userType'] = $customerType;
    $sites = DASH_GetSites($key, $db, $user);

    $views = array('Sites', 'Groups');
    $groups = DASH_GetGroups($key, $db, $user);

    foreach ($views as $viewvalue) {
        if ($customerType != 5) {
            $json['views'][$viewvalue]['All']['machines'] = [];
            $json['views'][$viewvalue]['All']['id'] = 'All_' . $viewvalue;
        }
        if ($viewvalue == 'Sites') {
            foreach ($sites as $site) {
                $machines = DASH_GetMachinesSites($key, $db, $site);

                $machineStatus = DASH_GetAllMachineStatusNOs($key, $db, $machines);

                $temp = [];
                foreach ($machines as $id => $host) {
                    $temp[$host]['machineStatus'] = isset($machineStatus[$host][0]) ? $machineStatus[$host][0] : 'offline';
                    $temp[$host]['operatingSystem'] = isset($machineStatus[$host][1]) ? $machineStatus[$host][1] : '1';
                    $temp[$host]['machineUniqueId'] = $id;
                    if ($customerType != 5) {
                        $json['views'][$viewvalue]['All']['machines'][$host]['machineStatus'] = $machineStatus[$host][0];
                        $json['views'][$viewvalue]['All']['machines'][$host]['operatingSystem'] = $machineStatus[$host][1];
                        $json['views'][$viewvalue]['All']['machines'][$host]['machineUniqueId'] = $id;
                    }
                }
                $json['views'][$viewvalue][$site]['machines'] = $temp;
                $json['views'][$viewvalue][$site]['id'] = $site . "_" . $viewvalue;
            }
        } else if ($viewvalue == 'Groups') {
            foreach ($groups as $group => $groupID) {
                $machines = DASH_GetGroupsMachines($key, $db, $groupID);

                $machineStatus = DASH_GetAllMachineStatusNOs($key, $db, $machines);

                $temp = [];
                foreach ($machines as $id => $host) {
                    $temp[$host]['machineStatus'] = isset($machineStatus[$host][0]) ? $machineStatus[$host][0] : 'offline';
                    $temp[$host]['operatingSystem'] = isset($machineStatus[$host][1]) ? $machineStatus[$host][1] : '1';
                    $temp[$host]['machineUniqueId'] = $id;
                    if ($customerType != 5) {
                        $json['views'][$viewvalue]['All']['machines'][$host]['machineStatus'] = $machineStatus[$host][0];
                        $json['views'][$viewvalue]['All']['machines'][$host]['operatingSystem'] = $machineStatus[$host][1];
                        $json['views'][$viewvalue]['All']['machines'][$host]['machineUniqueId'] = $id;
                    }
                }
                $json['views'][$viewvalue][$group]['machines'] = $temp;
                $json['views'][$viewvalue][$group]['id'] = $groupID . "_" . $viewvalue;
            }
        }
    }
    return $json;
}

function Pane_GetSelectedSitesMachines($db, $parentName, $type, $username, $limit)
{
    $key = "";
    $temp = [];
    $json = [];
    $macOnStatusArr = array(
        "1" => 'windows-green',
        "2" => 'android-green',
        "3" => 'apple-green',
        "4" => 'linux-green',
        "5" => 'ios-green',
    );

    $macOffStatusArr = array(
        "1" => 'windows-grey',
        "2" => 'android-grey',
        "3" => 'apple-grey',
        "4" => 'linux-grey',
        "5" => 'ios-grey',
    );

    // $searchType = $_SESSION['searchType'];
    // $searchValue = trim($_SESSION['searchValue']);

    if ($type == "Sites") {
        if ($parentName == 'All') {
            $dataScope = UTIL_GetSiteScope_PDO($db, $parentName, $type);
            $machines = DASH_GetMachinesSites($key, $db, $dataScope, $limit);
        } else {
            $machines = DASH_GetMachinesSites($key, $db, $parentName, $limit);
        }
    } else if ($type == "Groups") {
        if ($parentName == 'All') {
            $parentNameAll = Pane_GetGroupStyleAll($db, $username);
            $groups = $parentNameAll;
            $machines = [];
        } else {
            $groups = Pane_GetGroupSepStyleAll($db, $parentName, $username);
            $machines = [];
        }
    }

    $machineStatus = DASH_GetAllMachineStatus($key, $db, $machines);

    $str = '';

    $headers = getallheaders();

    if (safe_count($machines) > 0) {
        $str .= '<ul class="nav">';
        foreach ($machines as $id => $host1) {
            $host = utf8_encode($host1);
            $temp[$host]['status'] = isset($machineStatus[$host][0]) ? $machineStatus[$host][0] : 'offline';
            $temp[$host]['os'] = isset($machineStatus[$host][1]) ? $machineStatus[$host][1] : '1';
            $temp[$host]['censusId'] = $id;

            $status = strtolower(isset($machineStatus[$host][0]) ? $machineStatus[$host][0] : 'offline');
            $os = isset($machineStatus[$host][1]) ? $machineStatus[$host][1] : '1';
            $img = 'windows-grey.png';
            if ($status == 'offline') {
                $img = $macOffStatusArr[$os];
            } else if ($status == 'online') {
                $img = $macOnStatusArr[$os];
            } else {
                $img = $macOffStatusArr[$os];
            }
            // $class = '';
            // if ($searchType == 'ServiceTag' && $searchValue == trim($host)) {
            //     $class = 'active';
            // }
            $value = 'ServiceTag##' . $host . '##' . $parentName . '##' . $type . '##';
            $str .= '<li><a href="#" data-qa="machClick" class="machine_' . $id . '" onclick="machClick(\'' . $value . '\')" os="'.$os.'" title="' . $host . '"><span class="sidebar-normal">' . $host . '</span> <i><img src="../assets/img/' . $img . '.png" alt=""></i></a></li>';
        }
        $str .= '</ul>';
        $limit += 100;
        if (safe_count($machines) == 100) {
            $str .= '<div class="right-menu-more limit' . $limit . '" onclick="loadMoreMachines(\'' . $parentName . '\',\'' . $type . '\', \'' . $limit . '\')">Load More...</div>';
        }
    } else {

        if ($type == 'Groups') {

            $str = '';
            if (is_array($groups)) {
                $str .= '<ul class="nav">';
                foreach ($groups as $group) {
                    $value = 'Groups##' . $group['name'] . '##' . $parentName . '##' . $type . '##';
                    $str .= '<li><a href="#" data-qa="machClick" onclick="machClick(\'' . $value . '\')"  title="' . $group['name'] . '"><span class="sidebar-normal">' . $group['name'] . '</span></a></li>';
                }

                $str .= '</ul>';
            } else {
                $str .= '<ul class="nav">';
                $str .= '<li>' . $groups . '</li>';

                $str .= '</ul>';
            }
        }
    }

    if ($groups && $headers['Accept'] == 'application/json'){
      $str =  $groups;
    }

    if ($machines && $headers['Accept'] == 'application/json'){
      $str = $machines;
    }

    return $str;
}

function Pane_GetGroupNameById($db, $groupId)
{

    $sql = $db->prepare("SELECT name FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE mgroupid=? LIMIT 1");
    $sql->execute([$groupId]);
    $res = $sql->fetch();
    return $res['name'];
}

function Pane_GetGroupSepStyleAll($db, $styleId, $username)
{

    $sql = $db->prepare("SELECT mg.name FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups mg,core.GroupMappings gm WHERE mg.style=? and mg.mgroupid = gm.groupid and gm.username = ? ");
    $sql->execute([$styleId, $username]);
    $res = $sql->fetchAll();
    return $res;
}

function Pane_GetGroupNameAll($db, $username)
{
    $mgroupid = [];

    $sql = $db->prepare("SELECT mgroupid,name FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE username =?");
    $sql->execute([$username]);
    $res = $sql->fetchAll();

    foreach ($res as $key => $value) {
        $mgroupid[] .= $value['mgroupid'];
    }
    return $mgroupid;
}
function Pane_GetGroupStyleAll($db, $username)
{
    $mgroup = [];

    $sql = $db->prepare("SELECT mgroupid,name FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups WHERE username =?");
    $sql->execute([$username]);
    $res = $sql->fetchAll();

    foreach ($res as $key => $value) {
        $mgroup[] .= $value['name'];
    }
    return $mgroup;
}

function Pane_GetSelectedMachines($pdo, $hostVal)
{

    $username = $_SESSION['user']['username'];
    $type = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $parentName = $_SESSION['rparentName'];
    $siteName = '';
    $key = "";
    $temp = [];
    $json = [];
    $macOnStatusArr = array("1" => 'windows-green', "2" => 'android-green', "3" => 'apple-green', "4" => 'linux-green', "5" => 'ios-green');
    $macOffStatusArr = array("1" => 'windows-grey', "2" => 'android-grey', "3" => 'apple-grey', "4" => 'linux-grey', "5" => 'ios-grey');
    $status = 0;
    if ($type == 'Sites') {
        if ($parentName == 'All') {
            $dataScope = UTIL_GetSiteScope_PDO($pdo, $parentName, $type);
            $machines = DASH_GetMachinesSites_new($key, $pdo, $dataScope, $hostVal);
        } else {
            $machines = DASH_GetMachinesSites_new($key, $pdo, $parentName, $hostVal);
        }
    } else if ($type == 'Groups') {
        if ($parentName == 'All') {
            $parentNameAll = Pane_GetGroupNameAll($pdo, $username);
            $machines = DASH_GetGroupsMachines_new($key, $pdo, $parentNameAll, $hostVal);
        } else {
            $siteName = $parentName;
            $machines = DASH_GetGroupsMachines_new($key, $pdo, $parentName, $hostVal);
        }
    }

    $machineStatus = DASH_GetAllMachineStatus($key, $pdo, $machines);

    $str = '';
    if (safe_count($machines) > 0) {
        // $str .= '<ul class="nav">';
        foreach ($machines as $id => $host1) {
            $host = utf8_encode($host1);
            $temp[$host]['status'] = isset($machineStatus[$host][0]) ? $machineStatus[$host][0] : 'offline';
            $temp[$host]['os'] = isset($machineStatus[$host][1]) ? $machineStatus[$host][1] : '1';
            $temp[$host]['censusId'] = $id;

            $status = isset($machineStatus[$host][0]) ? $machineStatus[$host][0] : 'offline';
            $os = isset($machineStatus[$host][1]) ? $machineStatus[$host][1] : '1';
            $censusId = $id;
            $img = 'windows-grey.png';
            if ($status == 'offline' || $status == 'Offline') {
                $img = $macOffStatusArr[$os];
            } else if ($status == 'online' || $status == 'Online') {
                $img = $macOnStatusArr[$os];
            } else {
                $img = $macOffStatusArr[$os];
            }
            $class = '';
            if ($type == 'ServiceTag' && $searchValue == trim($hostVal)) {
                $status = 1;
                $class = 'active';
            }
            $value = 'ServiceTag##' . $host . '##' . $parentName . '##' . $type . '##';
            $str .= '<li><a href="#" data-qa="machClick" style="margin-right: 30px;" class="machine_' . $id . '" onclick="machClick(\'' . $value . '\')" title="' . $host . '"><span class="sidebar-normal">' . $host . '</span> <i><img src="../assets/img/' . $img . '.png" alt=""></i></a></li>';

            // $str .= '<li><a href="#" data-qa="machClick" id="' . $id . '_' . $type . '_' . $parentName . '" class="' . $type . '_machine ' . $class . '" onclick="machClick(this.className)" title="' . $host . '" style="margin-right: 30px;"><span>' . $host . '</span><i><img src="../assets/img/' . $img . '.png" alt=""></i></a></li>';
        }
        // $str .= '</ul>';
    }
    if ($type == 'ServiceTag' && $status == 0) {
        // $str .= '<ul class="nav">';
        $result = DASH_getNewLiForSearchMachine($pdo, $parentName, $hostVal);
        $mac = array($result['id'] => $result['host']);
        $id = $result['id'];
        $host = utf8_encode($result['host']);

        $machineStatus = DASH_GetAllMachineStatus($key, $pdo, $mac);

        $status = isset($machineStatus[$host][0]) ? $machineStatus[$host][0] : 'offline';
        $os = isset($machineStatus[$host][1]) ? $machineStatus[$host][1] : '1';
        $censusId = $id;
        $img = 'windows-grey.png';
        if ($status == 'offline' || $status == 'Offline') {
            $img = $macOffStatusArr[$os];
        } else if ($status == 'online' || $status == 'Online') {
            $img = $macOnStatusArr[$os];
        } else {
            $img = $macOffStatusArr[$os];
        }
        // $value = 'Groups##' .$group['name'].'##' . $parentName  .'##'.$type.'##';
        // $str .= '<li><a href="#" data-qa="machClick" onclick="machClick(\'' . $value.'\')"  title="'.$group['name'].'"><span class="sidebar-normal">'.$group['name'].'</span></a></li>';
        $str .= '<li><a data-qa="machClick" href="#" id="' . $id . '_' . $type . '_' . $parentName . '" style="margin-right: 30px;" class="' . $type . '_machine ' . $class . '" onclick="machClick(this.className)" title="' . $host . '" style="margin-right: 30px;"><span>' . $host . '</span><i><img src="../assets/img/' . $img . '.png" alt=""></i></a></li>';
        // $str .= '</ul>';
    }

    if ($str == '') {
        $str = 'Notfound';
    }
    return $str;
}
