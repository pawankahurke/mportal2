<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../include/common_functions.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-elastic.php';
require_once '../lib/l-vizualization.php';
include_once '../lib/l-setTimeZone.php';

if (!isset($_SESSION)) {
}
nhRole::dieIfnoRoles(['dashboardview']);

//Replace $routes['post'] with if else
if (url::postToText('function') === 'saveDashboard') { //roles: dashboardview
    saveDashboard();
} else if (url::postToText('function') === 'updateDashboard') { //roles: dashboardview
    updateDashboard();
} else if (url::postToText('function') === 'addKibanaDashboard') { //roles: dashboardview
    addKibanaDashboard();
}

//Replace $routes['get'] with if else
if (url::postToText('function') === 'getDashboardList') { //roles: dashboardview
    getDashboardList();
} else if (url::postToText('function') === 'getVisualisationList') { //roles: dashboardview
    getVisualisationList();
} else if (url::postToText('function') === 'getUserList') { //roles: dashboardview
    getUserList();
} else if (url::postToText('function') === 'getDashboardVisData') { //roles: dashboardview
    getDashboardVisData();
} else if (url::postToText('function') === 'dash_List') { //roles: dashboardview
    dash_List();
}

function getDashboardList()
{

    $res = checkModulePrivilege('visualisation', 2);
    if (!$res) {
        echo "Permission Denied";
        exit();
    }

    $userid = $_SESSION['user']['userid'];
    $pdo = pdo_connect();
    $vizualizations = getUserVizualizations();
    $defaultVizualizationData = getUserDefaultDashboardData($userid);
    $defDashId = $defaultVizualizationData ? $defaultVizualizationData['dashboardId'] : '';
    $recordList = [];

    if (!empty($vizualizations)) {
        foreach ($vizualizations as $key => $val) {
            $id = $val['dashboardId'];
            $name = $val['dashboardName'];
            $default = 'No';
            if ($id == $defDashId) {
                $default = 'Yes';
            }
            $global = $val['global'] == 1 ? 'Yes' : 'No';
            $type = $val['type'] == 1 ? 'Dashboard' : 'Insight';
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $time = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $val['createdtime'], "m/d/Y g:i:s A");
            } else {
                $time = date("m/d/Y g:i:s A", $val['createdtime']);
            }
            $createtime = $val['createdtime'] != 0 ? $time : '-';
            $username = getUserName($pdo, $val['uid']);
            $dashType = $val['userList'];
            $recordList[] = array($name, $default, $global, $type, $username, $createtime, $id, $dashType);
        }
    } else {
        $recordList = array();
    }
    $auditRes = create_auditLog('Create Dashbaord', 'View', 'Success');

    echo json_encode($recordList);
}

function getUserName($pdo, $uid)
{

    $sql = $pdo->prepare("Select username from " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
    $sql->execute([$uid]);
    $res = $sql->fetch();
    return $res['username'];
}

function getChildList($pdo, $userid)
{

    $sql = $pdo->prepare("select userid from " . $GLOBALS['PREFIX'] . "core.Users where parent_id=?");
    $sql->execute($userid);
    $res = $sql->fetchAll();
    return $res;
}

function getVisualisationList()
{
    $pdo = pdo_connect();
    $indexName = '.kibana*';
    $sql = $pdo->prepare("select value from " . $GLOBALS['PREFIX'] . "core.Options where name='kibana_namespace'");
    $sql->execute();
    $res = $sql->fetch();
    $namespace = $res['value'];
    if ($namespace == '') {
        $params = '{

                "query": {
                  "bool": {
                    "must": [
                        {
                            "term": {
                                "type": "visualization"
                            }
                        }
                    ]
                  }
                }
              }';
    } else {
        $params = '{

                "query": {
                  "bool": {
                    "must": [
                        {
                            "term": {
                                "namespace": "' . $namespace . '"
                            }
                        },
                        {
                            "term": {
                                "type": "visualization"
                            }
                        }
                    ]
                  }
                }
              }';
    }

    global $elastic_username;
    global $elastic_password;

    $requestHeaders = [
        "Content-Type: application/json",
        "Authorization: Basic " . base64_encode($elastic_username . ":" . $elastic_password),
    ];

    $res = EL_GetCurl($indexName, $params, $requestHeaders);
    $result = safe_json_decode($res, true);

    $result1 = $result['hits']['hits'];
    $option = '';
    $vizArray = [];

    if (safe_count($result1) > 0) {
        foreach ($result1 as $key => $val) {
            $id = explode('visualization:', $val['_id'])[1];
            $name = $val['_source']['visualization']['title'];
            if (!in_array($id, $vizArray)) {
                $vizArray[] = $id;
                $option .= '<option data-tokens="' . $name . '" value="' . $id . '">' . $name . '</option>';
            }
        }
    } else {
        $option = '<option>No Visualisation Available</option>';
    }

    echo $option;
}

function saveDashboard()
{
    $res = checkModulePrivilege('adddashboard', 2);
    if (!$res) {
        exit(json_encode(array('success' => false, 'message' => 'Permission Denied')));
    }

    $pdo = pdo_connect();

    global $elastic_url;
    global $kibana_ip_url;
    global $kibana_url;

    if (!url::issetInRequest('dname') || url::isEmptyInRequest('dname')) {
        exit(json_encode(array('success' => false, 'message' => 'The name field is required')));
    }

    if (!preg_match('/^[a-zA-Z0-9\_ ]+$/', url::requestToAny('dname'))) {
        exit(json_encode(array('success' => false, 'message' => 'The name can contain alpha numeric characters, space or underscores')));
    }

    if (strlen(url::requestToAny('dname')) > 48) {
        exit(json_encode(array('success' => false, 'message' => 'The name should not be more than 48 characters')));
    }

    $vid = url::requestToText('visid');
    $name = url::requestToText('dname');

    $checkSql = $pdo->prepare("select count(id) as dcount from " . $GLOBALS['PREFIX'] . "agent.dashboard where dashboardName=? limit 1");
    $checkSql->execute([$name]);
    $checkRes = $checkSql->fetch();
    if ($checkRes['dcount'] > 0) {
        exit(json_encode(array('success' => false, 'message' => 'The name you have entered already exists! Please try with another name.')));
    }

    $global = url::requestToText('global');
    $default = url::requestToText('default');
    $dashType = url::requestToText('dashType');
    $dash = url::requestToText('dash');
    $envglobal = url::requestToText('envglobal');

    $url = $kibana_url . '/api/kibana/dashboards/import?exclude=index-pattern';
    $embed = '';

    foreach ($vid as $key => $val) {
        $i = $key + 1;
        $embed .= '{\"embeddableConfig\":{},\"gridData\":{\"x\":0,\"y\":0,\"w\":28,\"h\":15,\"i\":\"' . $i . '\"}'
            . ',\"id\":\"' . $val . '\",\"panelIndex\":\"' . $i . '\",\"type\":\"visualization\",\"version\":\"6.5.1\"},';
    }
    $embed1 = rtrim($embed, ',');
    $panelJson = $embed1;

    $params = '{

	"objects": [{

            "type": "dashboard",
            "updated_at": "2019-01-10T12:07:52.103Z",
            "version": 4,
            "attributes": {
                    "title": "' . $name . '",
                    "hits": 0,
                    "description": "",
                    "panelsJSON": "[' . $panelJson . ']",
                    "optionsJSON": "{\"darkTheme\":false,\"hidePanelTitles\":false,\"useMargins\":true}",
                    "version": 1,
                    "timeRestore": true,
                    "timeTo": "now",
                    "timeFrom": "now-7d",
                    "kibanaSavedObjectMeta": {
                            "searchSourceJSON": "{\"query\":{\"language\":\"lucene\",\"query\":\"\"},\"filter\":[]}"
                    }
            }
        }]
    }';

    global $kibana_username;
    global $kibana_password;
    $headers = array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($params),
        "Authorization: Basic " . base64_encode($kibana_username . ":" . $kibana_password),
        'kbn-xsrf:true',
    );

    $res = curlToDashboard($url, $params, $headers);
    $result = safe_json_decode($res, true);
    $dashId = explode('dashboard:', $result['objects'][0]['id'])[1];

    if (isset($dashId) && !empty($dashId)) {
        $dashUrl = $kibana_url . "/app/kibana#/dashboard/" . $dashId . "?embed=true";
        $lastInsertId = saveDashboardDetails($dashId, $name, $global, $default, $dash, $envglobal, $dashType);
        saveDashboardVizualizations($lastInsertId, $vid);

        $auditRes = create_auditLog('Dashboard', 'Create', 'Success', $_REQUEST);
        exit(json_encode(array('success' => true, 'data' => $dashUrl)));
    }

    $auditRes = create_auditLog('Dashboard', 'Create', 'Failed', $_REQUEST);
    exit(json_encode(array('success' => false, 'message' => 'Something went wrong, unable to add visualisation')));
}

function saveDashboardVizualizations($dashboardId, $vizualizations)
{
    $dbo = NanoDB::connect();
    $valueArray = [];
    $bindings = [];

    foreach ($vizualizations as $eachVizs) {
        $valueArray[] = " (?, ?, '" . date('Y-m-d H:i:s') . "')";
        $bindings[] = $dashboardId;
        $bindings[] = $eachVizs;
    }

    if (safe_sizeof($valueArray) > 0) {
        $value = implode(", ", $valueArray);
        $query = 'INSERT INTO `' . $GLOBALS['PREFIX'] . 'agent`.`dashboard_vizualizations` (`dashboard_id`, `vizualization_id`, `created_at`) VALUES' . $value;
        $pdo = $dbo->prepare($query);
        $pdo->execute($bindings);

        return true;
    }

    return false;
}

function getUserList()
{

    $pdo = pdo_connect();
    $userid = $_SESSION['user']['userid'];

    $sql = $pdo->prepare("Select userid,username from " . $GLOBALS['PREFIX'] . "core.Users WHERE userid=?");
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

function saveDashboardDetails($dashId, $name, $global, $default, $dash, $envglobal, $dashType)
{

    $pdo = pdo_connect();

    $userid = $_SESSION['user']['userid'];
    $useremail = $_SESSION['user']['adminEmail'];
    $time = time();

    if ($useremail == 'admin@nanoheal.com') {

        $updatesql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.dashboard set defaultDashboard = ? WHERE defaultDashboard=?");
        $updatesql->execute([0, 1]);
    }

    if ($default == 1) {
        $sql1 = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.dashboard set defaultPage = ? WHERE uid=?");
        $sql1->execute([0, $userid]);

        $ddsql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET default_dashid = ? WHERE userid = ?");
        $ddsql->execute([$dashId, $userid]);
    }

    $sql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.dashboard (uid,dashboardId,dashboardName,global,defaultPage,type,createdtime,envGlobal,userList) VALUES (?,?,?,?,?,?,?,?,?)");
    $sql->execute([$userid, $dashId, $name, $global, $default, $dash, $time, $envglobal, $dashType]);

    return $pdo->lastInsertId();
}

function curlToDashboard($url, $params, $header = [])
{
    global $kibana_username;
    global $kibana_password;

    $defaultHeaders = array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($params),
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        "Authorization: Basic " . base64_encode($kibana_username . ":" . $kibana_password),
        'kbn-xsrf:true',
    );

    $header = (is_array($header) && safe_sizeof($header) > 0) ? $header : $defaultHeaders;

    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        $result = curl_exec($ch);
        $curl_errno = curl_errno($ch);
        curl_close($ch);
        return $result;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        echo $e;
    }
}

function deleteDashboard()
{

    $res = checkModulePrivilege('deletedashboard', 2);
    if (!$res) {
        echo "Permission Denied";
        exit();
    }

    $dID = url::requestToText('did');
    $pdo = pdo_connect();

    $res1 = checkVisualisationAccess($pdo, $dID);
    if (!$res1) {
        $auditRes = create_auditLog('Dashbaord', 'Delete', 'Failed', $_REQUEST);
        echo "Permission Denied";
        exit();
    }

    $delSql = $pdo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "agent.dashboard WHERE dashboardId =?");
    $delSql->execute([$dID]);

    $dashId = 'dashboard:' . $dID;

    $res = EL_DeleteIndexRow('.kibana_1', $dashId);

    if ($res) {
        $auditRes = create_auditLog('Dashbaord', 'Delete', 'Success', $_REQUEST);
        echo "success";
    }
}

function dash_List()
{
    $kid = null;

    $db = pdo_connect();

    $dashArray = [];

    $isActiveSet = false;
    $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.UserDashboards");
    $sql2->execute();
    $sql2Res = $sql2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($sql2Res as $key => $val) {
        $name = $val['name'];
        $id = $val['id'];
        $type = $val['type'];
        $dashname = $val['name'];
        $onclickval = "reloadview('" . $id . "','" . $dashname . "', " . $type . ")";
        $isActive = '';
        if ($id == $kid && !$isActiveSet) {
            $isActive = ' active';
            $isActiveSet = true;
        }
        if (!isset($dashArray[$type])) {
            $dashArray[$type] = "";
        }

        if ($name) {
            $html = "<li class=\"enableAnchorTag sidebar-dashboard-items$isActive\">
                        <a data-qa='menu-id-$id' href='javascript:' onclick=\"" . $onclickval . "\">
                            <span title='" . $name . "' class='sidebar-normal'>" . $name . "</span>
                        </a>
                    </li>";


            $dashArray[$type] .= $html;
        }
    }
    echo json_encode($dashArray);
}

function getDashboardVisData()
{

    $pdo = pdo_connect();
    $dashId = url::getToText('dashid');
    $query = "SELECT "
        . "ad.uid, ad.dashboardId, ad.userList,ad.global, ad.defaultPage, ad.type, ad.envGlobal, cu.default_dashid , GROUP_CONCAT(dv.vizualization_id) as vizualization_ids "
        . "FROM " . $GLOBALS['PREFIX'] . "agent.dashboard ad "
        . "LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Users cu ON (ad.uid=cu.userid) "
        . "LEFT JOIN " . $GLOBALS['PREFIX'] . "agent.dashboard_vizualizations dv ON (ad.id=dv.dashboard_id)"
        . "WHERE ad.dashboardId = ? GROUP BY ad.id LIMIT 1";

    $sql = $pdo->prepare($query);
    $sql->execute([$dashId]);
    $dashVizData = $sql->fetch();

    ob_clean();
    echo json_encode($dashVizData);
}

function updateDashboard()
{
    $res = checkModulePrivilege('adddashboard', 2);
    if (!$res) {
        exit(json_encode(array('success' => false, 'message' => 'Permission Denied')));
    }

    global $kibana_url;

    if (!url::issetInRequest('dname') || url::isEmptyInRequest('dname')) {
        exit(json_encode(array('success' => false, 'message' => 'The name field is required')));
    }

    if (!preg_match('/^[a-zA-Z0-9\_ ]+$/', url::requestToAny('dname'))) {
        exit(json_encode(array('success' => false, 'message' => 'The name can contain alpha numeric characters, space or underscores')));
    }

    if (strlen(url::requestToAny('dname')) > 48) {
        exit(json_encode(array('success' => false, 'message' => 'The name should not be more than 48 characters')));
    }

    $dashid = url::requestToText('dashid');
    $vid = url::requestToText('visid');
    $name = url::requestToText('dname');

    $global = url::requestToText('global');
    $default = url::requestToText('default');
    $dash = url::requestToText('dash');
    $envglobal = url::requestToText('envglobal');
    $dashType = url::requestToText('dashtype');
    $kibananewId = url::requestToText('kibanadashboardId');

    $dbo = NanoDB::connect();
    $pdo = $dbo->prepare("select * FROM " . $GLOBALS['PREFIX'] . "agent.dashboard where dashboardId=?");
    $pdo->execute([$dashid]);
    $checkData = $pdo->fetch(PDO::FETCH_ASSOC);

    if (!$checkData || !isset($checkData['uid']) || !is_numeric($checkData['uid'])) {
        exit(json_encode(array('success' => false, 'message' => 'Vizualization not found')));
    }

    if (intval($checkData['uid']) != intval($_SESSION['user']['userid'])) {
        exit(json_encode(array('success' => false, 'message' => 'This vizualization is not editable by this user')));
    }

    $dashboardPrimaryKey = $checkData['id'];

    $url = $kibana_url . '/api/kibana/dashboards/import?exclude=index-pattern';
    $embed = '';

    foreach ($vid as $key => $val) {
        $i = $key + 1;
        $embed .= '{\"embeddableConfig\":{},\"gridData\":{\"x\":0,\"y\":0,\"w\":28,\"h\":15,\"i\":\"' . $i . '\"}'
            . ',\"id\":\"' . $val . '\",\"panelIndex\":\"' . $i . '\",\"type\":\"visualization\",\"version\":\"6.5.1\"},';
    }
    $embed1 = rtrim($embed, ',');
    $panelJson = $embed1;

    $params = '{

	"objects": [{

            "type": "dashboard",
            "updated_at": "2019-01-10T12:07:52.103Z",
            "version": 4,
            "attributes": {
                    "title": "' . $name . '",
                    "hits": 0,
                    "description": "",
                    "panelsJSON": "[' . $panelJson . ']",
                    "optionsJSON": "{\"darkTheme\":false,\"hidePanelTitles\":false,\"useMargins\":true}",
                    "version": 1,
                    "timeRestore": true,
                    "timeTo": "now",
                    "timeFrom": "now-7d",
                    "kibanaSavedObjectMeta": {
                            "searchSourceJSON": "{\"query\":{\"language\":\"lucene\",\"query\":\"\"},\"filter\":[]}"
                    }
            }
        }]
    }';

    $res = curlToDashboard($url, $params);
    $result = safe_json_decode($res, true);
    $newDashId = explode('dashboard:', $result['objects'][0]['id'])[1];

    if ($dashType == 'Kibana Dashboard') {
        $newDashId = $kibananewId;
    }

    if (isset($newDashId) && !empty($newDashId)) {
        $dashUrl = $kibana_url . "/app/kibana#/dashboard/" . $newDashId . "?embed=true";
        $return = updateDashboardDetails($dashid, $name, $global, $default, $dash, $envglobal, $newDashId);

        if (deleteDashboardVizualizationByDashboardId($dashboardPrimaryKey)) {
            saveDashboardVizualizations($dashboardPrimaryKey, $vid);
        }

        if ($return) {
            $auditRes = create_auditLog('Dashboard', 'Modification', 'Success', $_REQUEST);

            exit(json_encode(array('success' => true)));
        }
    }
    $auditRes = create_auditLog('Dashboard', 'Modification', 'Failed', $_REQUEST);

    exit(json_encode(array('success' => false, 'message' => 'Something went wrong, unable to update visualisation')));
}

function deleteDashboardVizualizationByDashboardId($id)
{
    $dbo = NanoDB::connect();
    $pdo = $dbo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "agent.dashboard_vizualizations WHERE dashboard_id=?");

    if ($pdo->execute([$id])) {
        return true;
    }

    return false;
}

function updateDashboardDetails($dashId, $name, $global, $default, $dash, $envglobal, $newDashId)
{
    $pdo = pdo_connect();

    $userid = $_SESSION['user']['userid'];
    $useremail = $_SESSION['user']['adminEmail'];
    $time = time();

    if ($default == 1) {
        $sql1 = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.dashboard set defaultPage = ? WHERE uid=?");
        $sql1->execute([0, $userid]);

        $ddsql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET default_dashid = ? WHERE userid = ?");
        $ddsql->execute([$newDashId, $userid]);
    } else {
        $checkSql = $pdo->prepare("SELECT default_dashid from " . $GLOBALS['PREFIX'] . "core.Users WHERE userid = ?");
        $checkSql->execute([$userid]);
        $checkRes = $checkSql->fetch();
        if ($checkRes['default_dashid'] == $dashId) {
            $samesql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET default_dashid = ? WHERE userid = ?");
            $samesql->execute([$newDashId, $userid]);
        }
    }

    $sql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.dashboard SET dashboardName = ?, dashboardId = ?, global = ?, defaultPage = ?, type = ?, createdtime = ?, envGlobal = ? WHERE dashboardId = ?");
    $sql->execute([$name, $newDashId, $global, $default, $dash, $time, $envglobal, $dashId]);
    $count = $sql->rowCount();

    if ($count > 0) {
        $stmt = "Visulisation updated successfully!";
    } else {
        $stmt = "Nothing to update in Visualization!";
    }
    return $stmt;
}

function addKibanaDashboard()
{
    $pdo = pdo_connect();

    $kibname = url::requestToText('kibname');
    $kibdashid = url::requestToText('kibid');
    $userid = strip_tags($_SESSION['user']['userid']);
    $time = time();
    $global = url::requestToText('global');
    $default = url::requestToText('default');
    $type = url::requestToText('dash');
    $dashtype = url::requestToText('dashType');
    $checkstmt = $pdo->prepare("select count(id) as kdcnt from " . $GLOBALS['PREFIX'] . "agent.dashboard where dashboardName = ? ");
    $checkstmt->execute([$kibname]);
    $checkres = $checkstmt->fetch(PDO::FETCH_ASSOC);

    if ($checkres['kdcnt'] > 0) {
        $auditRes = create_auditLog('Kibana Dashboard', 'Create', 'Failed', $_REQUEST);
        $res = 'exist';
    } else {
        if ($default == 1) {
            $sql1 = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.dashboard set defaultPage = ? WHERE uid=?");
            $sql1->execute([0, $userid]);

            $ddsql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET default_dashid = ? WHERE userid = ?");
            $ddsql->execute([$kibdashid, $userid]);
        }
        $sql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.dashboard (uid, dashboardId, dashboardName, "
            . "global, defaultPage, type, createdtime, envGlobal,userList) VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)");
        $sql->execute([$userid, $kibdashid, $kibname, $global, $default, $type, $time, 0, $dashtype]);
        $auditRes = create_auditLog('Kibana Dashboard', 'Create', 'Success', $_REQUEST);
        $res = 'done';
    }
    ob_clean();
    echo $res;
}
