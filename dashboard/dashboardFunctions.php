<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../include/common_functions.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-elastic.php';
require_once '../lib/l-vizualization.php';
include_once '../lib/l-setTimeZone.php';

nhRole::dieIfnoRoles(['dashboardview']);

//Replace $routes['post'] with if else
if (url::postToText('function') === 'Add_Dashboard') { //roles: dashboardview
    // Add_Dashboard();
} else if (url::postToText('function') === 'Save_UsersDashboard') { //roles: dashboardview
    // Save_UsersDashboard();
} else if (url::postToText('function') === 'UpdateUsersDashboard') { //roles: dashboardview
    UpdateUsersDashboard();
} else if (url::postToText('function') === 'UpdateUsersViz') { //roles: dashboardview
    UpdateUsersViz();
} else if (url::postToText('function') === 'Save_UsersViz') { //roles: dashboardview
    // Save_UsersViz();
}


//Replace $routes['get'] with if else
if (url::postToText('function') === 'getUsersList') { //roles: dashboardview
    getUsersList();
} else if (url::postToText('function') === 'getAllDashboards') { //roles: dashboardview
    getAllDashboards();
} else if (url::postToText('function') === 'load_CubePage') { //roles: dashboardview
    load_CubePage();
} else if (url::postToText('function') === 'getUsersStatus') { //roles: dashboardview
    getUsersStatus();
} else if (url::postToText('function') === 'getOrgDashList') { //roles: dashboardview
    // getOrgDashList();
} else if (url::postToText('function') === 'fetchDashboardDetails') { //roles: dashboardview
    fetchDashboardDetails();
} else if (url::postToText('function') === 'getDefaultDashboard') { //roles: dashboardview
    getDefaultDashboard();
} else if (url::postToText('function') === 'getDefaultViz') { //roles: dashboardview
    getDefaultViz();
} else if (url::postToText('function') === 'getOrgVizList') { //roles: dashboardview
    // getOrgVizList();
}



function getUsersList()
{

    $pdo = pdo_connect();
    $userid = $_SESSION['user']['userid'];

    $sql = $pdo->prepare("Select userid,username from " . $GLOBALS['PREFIX'] . "core.Users where userid != ? ");
    $sql->execute([$userid]);
    $sqlRes = $sql->fetchAll();

    $options = '';
    if (safe_count($sqlRes) > 0) {
        foreach ($sqlRes as $key => $val) {
            $id = $val['userid'];
            $name = rtrim(preg_replace('#\d.*$#', '', $val['username']), '_');
            $options .= '<option value="' . $id . '">' . $name . '</option>';
        }
    } else {
        $options = '<option>No User Available</option>';
    }
    echo $options;
}

function getAllDashboards()
{
    $db = pdo_connect();
    $recordList1 = array();
    $recordList2 = array();
    $type = url::requestToAny('type');
    $allDashboardArray = array();

    $sql1 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Dashboards WHERE visualization=0");
    $sql1->execute();
    $sql1Res = $sql1->fetchAll(PDO::FETCH_ASSOC);
    if (safe_count($sql1Res) > 0) {
        foreach ($sql1Res as $key => $val) {
            $id = $val['id'];
            $dashName = $val['name'];
            $createdBy = $val['createdby'];
            $createdOn = $val['createdon'];
            $type = "Original Dashboard";
            $action = "<p class='ellipsis'>"
                . "<a href='javascript:' onclick='ViewEditCharts($id,\"$dashName\")' style='text-color:#ffedsw;color: #fa0f4b;text-decoration: underline'>Edit Charts</a></p>";
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $createdOn = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $createdOn, "m/d/Y g:i:s A");
            } else {
                $createdOn = date("m/d/Y g:i:s A", $createdOn);
            }
            $recordList1[] = array($dashName, $createdBy, $createdOn, $type, $action, $dashName, $id);
        }
    } else {
        $recordList1[] = array();
    }

    $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.UserDashboards WHERE visualization=0");
    $sql2->execute();
    $sql2Res = $sql2->fetchAll(PDO::FETCH_ASSOC);

    if (safe_count($sql2Res) > 0) {
        foreach ($sql2Res as $key => $val) {
            $id = $val['id'];
            $dashName = $val['name'];
            $createdBy = $val['createdby'];
            $createdOn = $val['createdon'];
            $type = "Custom Dashboard";
            $action = "<p class='ellipsis'>"
                . "<a href='javascript:' onclick='ViewEditCharts($id,\"$dashName\")' style='text-color:#ffedsw;color: #fa0f4b;text-decoration: underline'>Edit Charts</a></p>";
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $createdOn = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $createdOn, "m/d/Y g:i:s A");
            } else {
                $createdOn = date("m/d/Y g:i:s A", $createdOn);
            }
            $recordList2[] = array($dashName, $createdBy, $createdOn, $type, $action, $dashName, $id);
        }
    } else {
        $recordList2 = array();
    }
    $allDashboardArray = array_merge($recordList1, $recordList2);
    echo json_encode($allDashboardArray);
}

function getAllVizs()
{
    $db = pdo_connect();
    $recordList1 = array();
    $recordList2 = array();
    $type = url::requestToAny('type');
    $allDashboardArray = array();

    $sql1 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Dashboards WHERE visualization=1");
    $sql1->execute();
    $sql1Res = $sql1->fetchAll(PDO::FETCH_ASSOC);
    if (safe_count($sql1Res) > 0) {
        foreach ($sql1Res as $key => $val) {
            $id = $val['id'];
            $dashName = $val['name'];
            $createdBy = $val['createdby'];
            $createdOn = $val['createdon'];
            $type = "Original Dashboard";
            $action = "<p class='ellipsis'>"
                . "<a href='javascript:' onclick='ViewEditVizCharts($id,\"$dashName\")' style='text-color:#ffedsw;color: #fa0f4b;text-decoration: underline'>Edit Charts</a></p>";
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $createdOn = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $createdOn, "m/d/Y g:i:s A");
            } else {
                $createdOn = date("m/d/Y g:i:s A", $createdOn);
            }
            $recordList1[] = array($dashName, $createdBy, $createdOn, $type, $action, $dashName, $id);
        }
    } else {
        $recordList1[] = array();
    }

    $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.UserDashboards WHERE visualization=1");
    $sql2->execute();
    $sql2Res = $sql2->fetchAll(PDO::FETCH_ASSOC);

    if (safe_count($sql2Res) > 0) {
        foreach ($sql2Res as $key => $val) {
            $id = $val['id'];
            $dashName = $val['name'];
            $createdBy = $val['createdby'];
            $createdOn = $val['createdon'];
            $type = "Custom Dashboard";
            $action = "<p class='ellipsis'>"
                . "<a href='javascript:' onclick='ViewEditVizCharts($id,\"$dashName\")' style='text-color:#ffedsw;color: #fa0f4b;text-decoration: underline'>Edit Charts</a></p>";
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $createdOn = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $createdOn, "m/d/Y g:i:s A");
            } else {
                $createdOn = date("m/d/Y g:i:s A", $createdOn);
            }
            $recordList2[] = array($dashName, $createdBy, $createdOn, $type, $action, $dashName, $id);
        }
    } else {
        $recordList2 = array();
    }
    $allDashboardArray = array_merge($recordList1, $recordList2);
    echo json_encode($allDashboardArray);
}

function load_CubePage()
{
    $dashId = url::requestToText('dashid');
    $cubeDateRange = url::requestToText('label');

    if ($dashId == '') {
        $val = getUserDefaultDashboard();
        $dashId =  $val['id'];
        $_REQUEST['type'] = 'dash';
    }
    $type = url::requestToText('type');

    $DID = $dashId ? $dashId : 907;
    $sqlFRes = NanoDB::find_one("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.UserDashboards where id = ?", NULL, [$DID]);


    $username = $_SESSION['user']['username'];
    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];

    $allowedit = "false";
    // $machineArray = [];
    if ($searchType == 'Sites') {
        $searchType = 'site';
    } else if ($searchType == 'ServiceTag') {
        $searchType = 'machine';
    } else {
        $searchType = 'group';
        // $machineArray = MachineGroups::getMachinesByGroupName($searchValue);
    }

    if ($sqlFRes['did'] && getenv('METABASE_SITE_URL') && getenv('METABASE_SECRET_KEY')) {

        $charts = [];
        if (isset($sqlFRes['charts'])) {
            $charts1 = json_decode($sqlFRes['charts'], true);
            if ($charts1) {
                $charts = $charts1;
            }
        }


        $tokenData = [
            "resource" => ['dashboard' => $sqlFRes['did']],
            'params' => $charts,
            'exp' => date('U') + (3600 * 24)
        ];

        if ($sqlFRes['charts'] === null || !$tokenData['params']) {
            $tokenData['params']['site'] = $searchValue;
        }

        foreach ($tokenData['params'] as $key => $value) {
            if ($value === 'session') {
                $tokenData['params'][$key] = $searchValue;
            }
            if ($value === '[session]') {
                $tokenData['params'][$key] = [$searchValue];
            }
        }

        //        if ($searchType = 'site') {
        //            $tokenData['params']['site'] = $searchValue;
        //            $tokenData['params']['group'] = 'All';
        //        } else {
        //            $tokenData['params']['site'] = 'All';
        //            $tokenData['params']['group'] = $searchValue;
        //        }


        $token  = JWT::getJWT($tokenData, getenv('METABASE_SECRET_KEY'));

        $data = [
            "chartType" => 'metabase',
            "appId" => getenv('APP_CUBEJS_ID') ?: '',
            "appShema" => getenv('APP_CUBEJS_SCHEMA') ?: '',
            "type" => $searchType,
            "name" => $sqlFRes['name'],
            "dateFilter" => $cubeDateRange,
            "username" => $username,
            "tokenData" => $tokenData,
            "url" => getenv('METABASE_SITE_URL') . "/embed/dashboard/" . $token . "#bordered=false&titled=false&theme=transparent",
        ];
        echo json_encode($data);
        return;
    }


    $cubeUrl = getenv('VISUALISATION_SERVICE_DASH_URL');
    if (!$cubeUrl) {
        $confsql = NanoDB::connect()->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Options where name = 'cubejs_config' limit 1");
        $confsql->execute();
        $confres = $confsql->fetch(PDO::FETCH_ASSOC);
        $confdata = safe_json_decode($confres['value'], true);
        $cubeUrl = $confdata['cubeurl'];
    }


    $schemaId = "";
    if ($type == 'viz') {
        $cubejsUrl = $cubeUrl . "/#/explore/?" . "dashid=" . $dashId . "&username=" . $username . "&combined=true" . "&schemaId=" . $schemaId;
    } else {
        if ($dashId == '') {
            $cubejsUrl = $cubeUrl . "/#/userdashboard?" . "dashid=907" . "&username=" . $username . "&combined=false" . "&schemaId=3" . "&type=" . $searchType . "&filter=" . $searchValue . "&header=false";
        } elseif ($dashId > 900) {
            $schemaId = "3";
            $cubejsUrl = $cubeUrl . "/#/userdashboard?" . "dashid=" . $dashId . "&username=" . $username . "&combined=false" . "&schemaId=" . $schemaId . "" . "&type=" . $searchType . "&filter=" . $searchValue . "&header=false";
        } else {
            $cubejsUrl = $cubeUrl . "/#/?" . "dashid=" . $dashId . "&username=" . $username . "&combined=false" . "&schemaId=" . $schemaId . "&type=" . $searchType . "&filter=" . $searchValue . "&header=false";
        }
    }

    $temp = array(
        "chartType" => 'cubejs',
        "allowEdit" => $allowedit,
        "name" => $sqlFRes['name'],
        "url" => $cubejsUrl . "&dateFilter=" . $cubeDateRange,
        "dashId" => $DID,
        "token" => JWT::getJWT([
            "username" => $username,
            "schema" => $schemaId,
            "appId" => getenv('APP_CUBEJS_ID') ?: '',
            "appShema" => getenv('APP_CUBEJS_SCHEMA') ?: '',
            "type" => $searchType,
            "name" => $searchValue,
            "dateFilter" => $cubeDateRange,
            // 'machines' => $machineArray,
        ], getenv('CUBEJS_API_SECRET'))
    );

    echo json_encode($temp);
}
/*

function Add_Dashboard()
{
    $db = pdo_connect();
    $dashname = url::requestToAny('dashboardname');
    $usersList = url::requestToAny('users');
    $createdby = $_SESSION['user']['username'];
    $loggedUserId = $_SESSION['user']['userid'];
    $createdon = time();
    $type = url::requestToAny('type');
    $global = url::requestToAny('global');
    $default = url::requestToAny('home');

    if ($type == 'dash') {
        $viztype = 0;
    } else {
        $viztype = 1;
    }


    $allUsers = array();
    $sql = $db->prepare("Select userid from " . $GLOBALS['PREFIX'] . "core.Users where userid != ?");
    $sql->execute([$loggedUserId]);
    $sqlRes = $sql->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sqlRes as $k => $v) {
        array_push($allUsers, $v['userid']);
    }
    $DiffArr = array_diff($allUsers, $usersList);
    array_push($usersList, $loggedUserId);
    $usersList = implode(',', $usersList);
    $DiffArr = implode(',', $DiffArr);

    $insert_sql = $db->prepare("INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.Dashboards (name,visualization,createdby,createdon,global,home) VALUES (?,?,?,?,?,?)");
    $insert_sql->execute([$dashname, $viztype, $createdby, $createdon, $global, $home]);
    $insert_res = $db->lastInsertId();

    if ($type == 'dash') {
        $insert2_sql = $db->prepare("INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DashboardUsers (id,user,allowedit) VALUES (?,?,?)");
        $insert2_sql->execute([$insert_res, $usersList, 1]);
        $insert2_res = $db->lastInsertId();

        $insert3_sql = $db->prepare("INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DashboardUsers (id,user,allowedit) VALUES (?,?,?)");
        $insert3_sql->execute([$insert_res, $DiffArr, 0]);
        $insert3_res = $db->lastInsertId();
    } else {
        $insert2_sql = $db->prepare("INSERT  IGNORE INTO " . $GLOBALS['PREFIX'] . "core.VisualizationUsers (id,user,allowedit) VALUES (?,?,?)");
        $insert2_sql->execute([$insert_res, $usersList, 1]);
        $insert2_res = $db->lastInsertId();

        $insert3_sql = $db->prepare("INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.VisualizationUsers (id,user,allowedit) VALUES (?,?,?)");
        $insert3_sql->execute([$insert_res, $DiffArr, 0]);
        $insert3_res = $db->lastInsertId();
    }

    if ($insert_res) {
        $msg = "success";
    } else {
        $msg = "failed";
    }

    echo $msg;
}

function getOrgDashList()
{
    $db = pdo_connect();

    $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Dashboards where visualization = 0");
    $sql->execute();
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);

    $options = '';
    if (safe_count($res) > 0) {
        foreach ($res as $key => $val) {
            $id = $val['id'];
            $name = $val['name'];
            $options .= '<option value="' . $id . '" id = "' . $name . '">' . $name . '</option>';
        }
    } else {
        $options = '<option>No User Available</option>';
    }
    echo $options;
}

function getOrgVizList()
{
    $db = pdo_connect();

    $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Dashboards where visualization = 1");
    $sql->execute();
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);

    $options = '';
    if (safe_count($res) > 0) {
        foreach ($res as $key => $val) {
            $id = $val['id'];
            $name = $val['name'];
            $options .= '<option value="' . $id . '" id = "' . $name . '">' . $name . '</option>';
        }
    } else {
        $options = '<option>No User Available</option>';
    }
    echo $options;
}

function Save_UsersDashboard()
{
    $db = pdo_connect();
    $users = url::requestToAny('users');
    $loggedUserId = $_SESSION['user']['userid'];
    $replicate = url::requestToAny('replicate');
    $deftype = url::requestToAny('defType');
    $UsersArr = array();
    $homePage = url::requestToAny('home');
    $dashname = url::requestToText('dashName');
    $allUsers = array();
    $sql = $db->prepare("Select userid from " . $GLOBALS['PREFIX'] . "core.Users");
    $sql->execute();
    $sqlRes = $sql->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sqlRes as $k => $v) {
        array_push($allUsers, $v['userid']);
    }

    $createdTime = time();
    if ($users == 'All') {
        $global = 1;
    } else {
        $global = 0;
    }

    if ($deftype == '') {
        $sql2 = $db->prepare("select type from " . $GLOBALS['PREFIX'] . "core.Dashboards where id = ?");
        $sql2->execute([$replicate]);
        $res2 = $sql2->fetch();
        $type = $res2['type'];
    } else {
        $type = $deftype;
    }

    $loggedUserName = $_SESSION['user']['logged_username'];
    $sqlInsert1 = $db->prepare("Insert into " . $GLOBALS['PREFIX'] . "core.UserDashboards (did,name,type,visualization,createdby,createdon,global,home) VALUES (?,?,?,?,?,?,?,?)");
    $sqlInsert1->execute([$replicate, $dashname, $type, '0', $loggedUserName, $createdTime, $global, $homePage]);
    $resInsert1 = $db->lastInsertId();

    if ($users == 'All') {
        $usersList = implode(',', $allUsers);
        $insert2_sql = $db->prepare("INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DashboardUsers (dashid,user,allowedit,type) VALUES (?,?,?,?)");
        $insert2_sql->execute([$resInsert1, $usersList, 1, 'userDef']);
        $insert2_res = $db->lastInsertId();
    } else {
        $DiffArr = array_diff($allUsers, $users);
        array_push($users, $loggedUserId);
        $usersList = implode(',', $users);
        $DiffArr = implode(',', $DiffArr);
        $insert2_sql = $db->prepare("INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DashboardUsers (dashid,user,allowedit,type) VALUES (?,?,?,?)");
        $insert2_sql->execute([$resInsert1, $usersList, 1, 'userDef']);
        $insert2_res = $db->lastInsertId();

        $insert3_sql = $db->prepare("INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.DashboardUsers (dashid,user,allowedit,type) VALUES (?,?,?,?)");
        $insert3_sql->execute([$resInsert1, $DiffArr, 0, 'userDef']);
        $insert3_res = $db->lastInsertId();
    }


    if ($replicate != '') {
        $sql = $db->prepare("select layout,vizstate from " . $GLOBALS['PREFIX'] . "core.DashboardItem where dashid = ?");
        $sql->execute([$replicate]);
        $res = $sql->fetchAll();



        foreach ($res as $key => $val) {
            $vizstate = $val['vizstate'];
            $layout = $val['layout'];

            $sqlInsert2 = $db->prepare("Insert into " . $GLOBALS['PREFIX'] . "core.UserDashboardItem (layout,vizstate,name,dashid,username) VALUES (?,?,?,?,?)");
            $sqlInsert2->execute([$layout, $vizstate, $dashname, $resInsert1, $loggedUserName]);
        }
    }

    $res = array("DashboardName" => $dashname, 'DashId' => $resInsert1);
    echo json_encode($res);
}

function Save_UsersViz()
{
    $db = pdo_connect();
    $users = url::requestToAny('users');
    $loggedUserId = $_SESSION['user']['userid'];
    $replicate = url::requestToAny('replicate');
    $UsersArr = array();
    $homePage = url::requestToAny('home');
    $dashname = url::requestToAny('dashName');
    $deftype = url::requestToAny('defType');

    $allUsers = array();
    $sql = $db->prepare("Select userid from " . $GLOBALS['PREFIX'] . "core.Users");
    $sql->execute();
    $sqlRes = $sql->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sqlRes as $k => $v) {
        array_push($allUsers, $v['userid']);
    }

    $createdTime = time();
    if ($users == 'All') {
        $global = 1;
    } else {
        $global = 0;
    }

    if ($deftype == '') {
        $sql2 = $db->prepare("select type from " . $GLOBALS['PREFIX'] . "core.Dashboards where id = ?");
        $sql2->execute([$replicate]);
        $res2 = $sql2->fetch();
        $type = $res2['type'];
    } else {
        $type = $deftype;
    }

    $loggedUserName = $_SESSION['user']['logged_username'];
    $sqlInsert1 = $db->prepare("Insert into " . $GLOBALS['PREFIX'] . "core.UserDashboards (did,name,type,visualization,createdby,createdon,global,home) VALUES (?,?,?,?,?,?,?,?)");
    $sqlInsert1->execute([$replicate, $dashname, $type, '1', $loggedUserName, $createdTime, $global, $homePage]);
    $resInsert1 = $db->lastInsertId();

    if ($users == 'All') {
        $usersList = implode(',', $allUsers);
        $insert2_sql = $db->prepare("INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.VisualizationUsers (dashid,user,allowedit,type) VALUES (?,?,?,?)");
        $insert2_sql->execute([$resInsert1, $usersList, 1, 'userDef']);
        $insert2_res = $db->lastInsertId();
    } else {
        $DiffArr = array_diff($allUsers, $users);
        array_push($users, $loggedUserId);
        $usersList = implode(',', $users);
        $DiffArr = implode(',', $DiffArr);
        $insert2_sql = $db->prepare("INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.VisualizationUsers (dashid,user,allowedit,type) VALUES (?,?,?,?)");
        $insert2_sql->execute([$resInsert1, $usersList, 1, 'userDef']);
        $insert2_res = $db->lastInsertId();

        $insert3_sql = $db->prepare("INSERT IGNORE INTO " . $GLOBALS['PREFIX'] . "core.VisualizationUsers (dashid,user,allowedit,type) VALUES (?,?,?,?)");
        $insert3_sql->execute([$resInsert1, $DiffArr, 0, 'userDef']);
        $insert3_res = $db->lastInsertId();
    }


    if ($replicate != '') {
        $sql = $db->prepare("select layout,vizstate from " . $GLOBALS['PREFIX'] . "core.DashboardItem where dashid = ?");
        $sql->execute([$replicate]);
        $res = $sql->fetchAll();

        foreach ($res as $key => $val) {
            $vizstate = $val['vizstate'];
            $layout = $val['layout'];

            $sqlInsert2 = $db->prepare("Insert into " . $GLOBALS['PREFIX'] . "core.UserDashboardItem (layout,vizstate,name,dashid,username) VALUES (?,?,?,?,?)");
            $sqlInsert2->execute([$layout, $vizstate, $dashname, $resInsert1, $loggedUserName]);
        }
    }

    $res = array("VizName" => $dashname, 'VizId' => $resInsert1);
    echo json_encode($res);
}

function Delete_DashboardFn()
{
    $dashid = url::requestToAny('did');
    $Type = url::requestToAny('type');
    $dashType = url::requestToAny('dashType');

    $db = pdo_connect();
    if ($Type == 'Custom Dashboard') {
        $sql1 = $db->prepare("delete from " . $GLOBALS['PREFIX'] . "core.UserDashboards where id = ?");
        $sql1->execute([$dashid]);
    } else {

        $sql1 = $db->prepare("delete from " . $GLOBALS['PREFIX'] . "core.Dashboards where id = ?");
        $sql1->execute([$dashid]);
    }

    if ($dashType == 'dash') {
        $sql2 = $db->prepare("delete from " . $GLOBALS['PREFIX'] . "core.DashboardUsers where dashid = ?");
        $sql2->execute([$dashid]);
        $res2 = $sql2->rowCount();
    } else {
        $sql2 = $db->prepare("delete from " . $GLOBALS['PREFIX'] . "core.VisualizationUsers where dashid = ?");
        $sql2->execute([$dashid]);
        $res2 = $sql2->rowCount();
    }

    if ($res2) {
        $msg = array("msg" => 'success');
    } else {
        $msg = array("msg" => 'failed');
    }

    echo json_encode($msg);
}
 */


function getUsersStatus()
{
    $db = pdo_connect();
    $loggedUserid = $_SESSION['user']['userid'];
    $loggedUserEmail = $_SESSION['userloginfo']['email'];

    $sql1 = $db->prepare("SELECT id FROM " . $GLOBALS['PREFIX'] . "core.Options WHERE name = 'AdminRole'");
    $sql1->execute([]);
    $sql1Res = $sql1->fetch(PDO::FETCH_ASSOC);
    $adminRoleid = $sql1Res['id'];

    $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Users where userid = ?");
    $sql->execute([$loggedUserid]);
    $res = $sql->fetch(PDO::FETCH_ASSOC);
    $loggedUserRole = $res['role_id'];

    if ($loggedUserRole === $adminRoleid) {
        $msg = array('Email' => $loggedUserEmail, 'IsAdmin' => "true");
    } else {
        $msg = array('Email' => $loggedUserEmail, 'IsAdmin' => "false");
    }

    echo json_encode($msg);
}

function fetchDashboardDetails()
{
    $id = url::requestToAny('did');
    $type = url::requestToAny('dashtype');
    $dashboardType = url::requestToAny('dashboardType');
    $recordList = array();
    $db = pdo_connect();

    if ($type == 'Custom Dashboard') {
        $sql1 = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.UserDashboards where id = ?");
        $sql1->execute([$id]);
        $res1 = $sql1->fetch(PDO::FETCH_ASSOC);
        $home = $res1['home'];
        $dashname = $res1['name'];

        if ($dashboardType == 'dash') {
            $sql2 = $db->prepare("select count(*) as count  from " . $GLOBALS['PREFIX'] . "core.DashboardUsers where dashid = ?");
            $sql2->execute([$id]);
            $res2 = $sql2->fetch(PDO::FETCH_ASSOC);
            $count = $res2['count'];

            if ($count == 2) {
                $global = 0;
                $sql2 = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.DashboardUsers where dashid = ?");
                $sql2->execute([$id]);
                $res2 = $sql2->fetchAll(PDO::FETCH_ASSOC);
                foreach ($res2 as $k => $v) {
                    $value = $v['allowedit'];
                    if ($value == 1) {
                        $id = $v['user'];
                        $user = explode(',', $id);
                        foreach ($user as $K => $V) {
                            $sql2 = $db->prepare("select username from " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
                            $sql2->execute([$V]);
                            $res2 = $sql2->fetch(PDO::FETCH_ASSOC);
                            $name = rtrim(preg_replace('#\d.*$#', '', $res2['username']), '_');
                            $usersList .= '<option selected value="' . $V . '">' . $name . '</option>';
                        }
                    } else {
                        $id = $v['user'];
                        $user = explode(',', $id);
                        foreach ($user as $K => $V) {
                            $sql2 = $db->prepare("select username from " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
                            $sql2->execute([$V]);
                            $res2 = $sql2->fetch(PDO::FETCH_ASSOC);
                            $name = rtrim(preg_replace('#\d.*$#', '', $res2['username']), '_');
                            $usersList .= '<option value="' . $V . '">' . $name . '</option>';
                        }
                    }
                }
            } else {
                $global = 1;
                $usersList = getFetchUsersList();
            }
        } else {
            $sql2 = $db->prepare("select count(*) as count  from " . $GLOBALS['PREFIX'] . "core.VisualizationUsers where dashid = ?");
            $sql2->execute([$id]);
            $res2 = $sql2->fetch(PDO::FETCH_ASSOC);
            $count = $res2['count'];

            if ($count == 2) {
                $global = 0;
                $sql2 = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.VisualizationUsers where dashid = ?");
                $sql2->execute([$id]);
                $res2 = $sql2->fetchAll(PDO::FETCH_ASSOC);
                foreach ($res2 as $k => $v) {
                    $value = $v['allowedit'];
                    if ($value == 1) {
                        $id = $v['user'];
                        $user = explode(',', $id);
                        foreach ($user as $K => $V) {
                            $sql2 = $db->prepare("select username from " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
                            $sql2->execute([$V]);
                            $res2 = $sql2->fetch(PDO::FETCH_ASSOC);
                            $name = rtrim(preg_replace('#\d.*$#', '', $res2['username']), '_');
                            $usersList .= '<option selected value="' . $V . '">' . $name . '</option>';
                        }
                    } else {
                        $id = $v['user'];
                        $user = explode(',', $id);
                        foreach ($user as $K => $V) {
                            $sql2 = $db->prepare("select username from " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
                            $sql2->execute([$V]);
                            $res2 = $sql2->fetch(PDO::FETCH_ASSOC);
                            $name = rtrim(preg_replace('#\d.*$#', '', $res2['username']), '_');
                            $usersList .= '<option value="' . $V . '">' . $name . '</option>';
                        }
                    }
                }
            } else {
                $global = 1;
                $usersList = getFetchUsersList();
            }
        }

        $recordList = array('dashname' => $dashname, 'homepage' => $home, 'global' => $global, 'usersList' => $usersList);
    } else {
        if ($dashboardType == 'dash') {
            $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Dashboards where id = ?");
            $sql->execute([$id]);
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $dashname = $res['name'];
            $global = $res['global'];
            $home = $res['home'];

            $sql2 = $db->prepare("select count(*) as count  from " . $GLOBALS['PREFIX'] . "core.DashboardUsers where dashid = ?");
            $sql2->execute([$id]);
            $res2 = $sql2->fetch(PDO::FETCH_ASSOC);
            $count = $res2['count'];

            if ($count == 2) {
                $global = 0;
                $sql2 = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.DashboardUsers where dashid = ?");
                $sql2->execute([$id]);
                $res2 = $sql2->fetchAll(PDO::FETCH_ASSOC);
                foreach ($res2 as $k => $v) {
                    $value = $v['allowedit'];
                    if ($value == 1) {
                        $id = $v['user'];
                        $user = explode(',', $id);
                        foreach ($user as $K => $V) {
                            $sql2 = $db->prepare("select username from " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
                            $sql2->execute([$V]);
                            $res2 = $sql2->fetch(PDO::FETCH_ASSOC);
                            $name = rtrim(preg_replace('#\d.*$#', '', $res2['username']), '_');
                            $usersList .= '<option selected value="' . $V . '">' . $name . '</option>';
                        }
                    } else {
                        $id = $v['user'];
                        $user = explode(',', $id);
                        foreach ($user as $K => $V) {
                            $sql2 = $db->prepare("select username from " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
                            $sql2->execute([$V]);
                            $res2 = $sql2->fetch(PDO::FETCH_ASSOC);
                            $name = rtrim(preg_replace('#\d.*$#', '', $res2['username']), '_');
                            $usersList .= '<option value="' . $V . '">' . $name . '</option>';
                        }
                    }
                }
            } else {
                $global = 1;
                $usersList = getFetchUsersList();
            }
        } else {
            $sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Dashboards where id = ?");
            $sql->execute([$id]);
            $res = $sql->fetch(PDO::FETCH_ASSOC);
            $dashname = $res['name'];
            $global = $res['global'];
            $home = $res['home'];

            $sql2 = $db->prepare("select count(*) as count  from " . $GLOBALS['PREFIX'] . "core.VisualizationUsers where dashid = ?");
            $sql2->execute([$id]);
            $res2 = $sql2->fetch(PDO::FETCH_ASSOC);
            $count = $res2['count'];

            if ($count == 2) {
                $global = 0;
                $sql2 = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.VisualizationUsers where dashid = ?");
                $sql2->execute([$id]);
                $res2 = $sql2->fetchAll(PDO::FETCH_ASSOC);
                foreach ($res2 as $k => $v) {
                    $value = $v['allowedit'];
                    if ($value == 1) {
                        $id = $v['user'];
                        $user = explode(',', $id);
                        foreach ($user as $K => $V) {
                            $sql2 = $db->prepare("select username from " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
                            $sql2->execute([$V]);
                            $res2 = $sql2->fetch(PDO::FETCH_ASSOC);
                            $name = rtrim(preg_replace('#\d.*$#', '', $res2['username']), '_');
                            $usersList .= '<option selected value="' . $V . '">' . $name . '</option>';
                        }
                    } else {
                        $id = $v['user'];
                        $user = explode(',', $id);
                        foreach ($user as $K => $V) {
                            $sql2 = $db->prepare("select username from " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
                            $sql2->execute([$V]);
                            $res2 = $sql2->fetch(PDO::FETCH_ASSOC);
                            $name = rtrim(preg_replace('#\d.*$#', '', $res2['username']), '_');
                            $usersList .= '<option value="' . $V . '">' . $name . '</option>';
                        }
                    }
                }
            } else {
                $global = 1;
                $usersList = getFetchUsersList();
            }
        }

        $recordList = array('dashname' => $dashname, 'homepage' => $home, 'global' => $global, 'usersList' => $usersList);
    }

    echo json_encode($recordList);
}

function getFetchUsersList()
{
    $db = pdo_connect();
    $userid = $_SESSION['user']['userid'];

    $sql = $db->prepare("Select userid,username from " . $GLOBALS['PREFIX'] . "core.Users where userid != ? ");
    $sql->execute([$userid]);
    $sqlRes = $sql->fetchAll();

    $options = '';
    if (safe_count($sqlRes) > 0) {
        foreach ($sqlRes as $key => $val) {
            $id = $val['userid'];
            $name = rtrim(preg_replace('#\d.*$#', '', $val['username']), '_');
            $options .= '<option value="' . $id . '">' . $name . '</option>';
        }
    } else {
        $options = '<option>No User Available</option>';
    }
    return $options;
}

function getDefaultDashboard()
{
    $db = pdo_connect();

    $sql2 = $db->prepare("select dashType,description from " . $GLOBALS['PREFIX'] . "core.DashboardTypes ");
    $sql2->execute();
    $res2 = $sql2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($res2 as $key => $val) {
        $id = $val['dashType'];
        $desc = $val['description'];
        $usersList .= '<option value="' . $id . '">' . $desc . '</option>';
    }

    echo $usersList;
}

function getDefaultViz()
{
    $db = pdo_connect();

    $sql2 = $db->prepare("select vizType,description from " . $GLOBALS['PREFIX'] . "core.VisualTypes ");
    $sql2->execute();
    $res2 = $sql2->fetchAll(PDO::FETCH_ASSOC);

    foreach ($res2 as $key => $val) {
        $id = $val['vizType'];
        $desc = $val['description'];
        $usersList .= '<option value="' . $id . '">' . $desc . '</option>';
    }

    echo $usersList;
}

function UpdateUsersDashboard()
{
    $db = pdo_connect();
    $dashid = url::requestToAny('dashid');
    $dashname = url::requestToText('dashName');
    $type = url::requestToAny('type');
    $global = url::requestToAny('global');
    $home = url::requestToAny('home');
    $usersList = url::requestToAny('users');
    $createdby = $_SESSION['user']['username'];
    $loggedUserId = $_SESSION['user']['userid'];

    $allUsers = array();
    $sql = $db->prepare("Select userid from " . $GLOBALS['PREFIX'] . "core.Users where userid != ?");
    $sql->execute([$loggedUserId]);
    $sqlRes = $sql->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sqlRes as $k => $v) {
        array_push($allUsers, $v['userid']);
    }
    $DiffArr = array_diff($allUsers, $usersList);
    array_push($usersList, $loggedUserId);
    $usersList = implode(',', $usersList);
    $DiffArr = implode(',', $DiffArr);

    if ($type == 'Original Dashboard') {
        $insert_sql = $db->prepare("Update " . $GLOBALS['PREFIX'] . "core.Dashboards set name = ?,global = ?,home = ? where id = ?");
        $insert_sql->execute([$dashname, $global, $home, $dashid]);
    } else {
        $params = array_merge([$dashname, $global, $home, $dashid]);
        $insert_sql = $db->prepare("Update " . $GLOBALS['PREFIX'] . "core.UserDashboards set name = ?,global = ?,home = ? where id = ?");
        $insert_sql->execute($params);
    }
    $insert2_sql = $db->prepare("Update " . $GLOBALS['PREFIX'] . "core.DashboardUsers set user = ? where allowedit = ? and dashid = ?");
    $insert2_sql->execute([$usersList, 1, $dashid]);
    $insert2_res = $insert2_sql->rowCount();

    $insert3_sql = $db->prepare("Update " . $GLOBALS['PREFIX'] . "core.DashboardUsers set user = ? where allowedit = ? and  dashid = ?");
    $insert3_sql->execute([$DiffArr, 0, $dashid]);
    $insert3_res = $insert3_sql->rowCount();

    if ($insert3_res || $insert2_res) {
        $msg = "success";
    } else {
        $msg = "failed";
    }

    echo $msg;
}

function UpdateUsersViz()
{
    $db = pdo_connect();
    $dashid = url::requestToAny('dashid');
    $dashname = url::requestToAny('dashName');
    $type = url::requestToAny('type');
    $global = url::requestToAny('global');
    $home = url::requestToAny('home');
    $usersList = url::requestToAny('users');
    $createdby = $_SESSION['user']['username'];
    $loggedUserId = $_SESSION['user']['userid'];

    $allUsers = array();
    $sql = $db->prepare("Select userid from " . $GLOBALS['PREFIX'] . "core.Users where userid != ?");
    $sql->execute([$loggedUserId]);
    $sqlRes = $sql->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sqlRes as $k => $v) {
        array_push($allUsers, $v['userid']);
    }
    $DiffArr = array_diff($allUsers, $usersList);
    array_push($usersList, $loggedUserId);
    $usersList = implode(',', $usersList);
    $DiffArr = implode(',', $DiffArr);

    if ($type == 'Original Dashboard') {
        $insert_sql = $db->prepare("Update " . $GLOBALS['PREFIX'] . "core.Dashboards set name = ?,global = ?,home = ? where id = ?");
        $insert_sql->execute([$dashname, $global, $home, $dashid]);
    } else {
        $params = array_merge([$dashname, $global, $home, $dashid]);
        $insert_sql = $db->prepare("Update " . $GLOBALS['PREFIX'] . "core.UserDashboards set name = ?,global = ?,home = ? where id = ?");
        $insert_sql->execute($params);
    }
    $insert2_sql = $db->prepare("Update " . $GLOBALS['PREFIX'] . "core.VisualizationUsers set user = ? where allowedit = ? and dashid = ?");
    $insert2_sql->execute([$usersList, 1, $dashid]);
    $insert2_res = $insert2_sql->rowCount();

    $insert3_sql = $db->prepare("Update " . $GLOBALS['PREFIX'] . "core.VisualizationUsers set user = ? where allowedit = ? and  dashid = ?");
    $insert3_sql->execute([$DiffArr, 0, $dashid]);
    $insert3_res = $insert3_sql->rowCount();

    if ($insert3_res || $insert2_res) {
        $msg = "success";
    } else {
        $msg = "failed";
    }

    echo $msg;
}
