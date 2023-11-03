<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, X-API-KEY, X-UserToken');

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-util.php';
include_once '../lib/l-customer.php';
include_once '../lib/l-reseller.php';
include_once '../lib/l-user.php';
include_once '../lib/l-crm.php';
include_once '../lib/l-crmdetls.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';
include_once '../lib/passdata.php';
require_once '../include/common_functions.php';

require_once "./l-custAjax2.php";



function prepareSend($user_id)
{
    $user = nhUser::getUserById($user_id);
    $mail = $user['user_email'];
    $res = CUSTAJX_ResendUserMail($mail);
    $result = $res == false ? 2 : 1;
    return $result;
}

if (url::postToText('function') === 'CUSAJX_GetAllUser_Roles') { // roles: role, user

    nhRole::dieIfnoRoles(['role', 'user']); // roles: role, user
    CUSAJX_GetAllUser_Roles();
} elseif (url::postToText('function') === 'CUSAJX_GetAdmin_Role') { //roles: user
    nhRole::dieIfnoRoles(['role', 'user']); // roles: role, user
    CUSAJX_GetAdmin_Role();
} elseif (url::postToText('function') === 'CUSAJX_GetUserDetail') { //roles: user
    nhRole::dieIfnoRoles(['user']); // roles:   user
    CUSAJX_GetUserDetail();
} elseif (url::postToText('function') === 'CUSTAJX_ResellerCustomers') { //roles: user
    nhRole::dieIfnoRoles(['user']); // roles:   user
    CUSTAJX_ResellerCustomers();
} elseif (url::postToText('function') === 'CUSTAJX_ResendUserMail') { //roles: role
    nhRole::dieIfnoRoles(['user']); // roles:   user
    $result = prepareSend(url::postToInt('sel_userId'));
    echo $result;
} elseif (url::postToText('function') === 'CUSAJX_DeleteUser') { //roles: role
    nhRole::dieIfnoRoles(['user']); // roles:   user
    CUSAJX_DeleteUser();
} elseif (url::postToText('function') === 'CUST_TimeZones') { //roles: role
    nhRole::dieIfnoRoles(['user']); // roles:   user
    CUST_TimeZones();
} elseif (url::getToText('function') === 'CUSAJX_ExportUser') { //roles: role
    nhRole::dieIfnoRoles(['user']); // roles:   user
    CUSAJX_ExportUser();
} elseif (url::postToText('function') === 'getwsurl') { //roles: role
    nhRole::dieIfnoRoles(['user']); // roles:   user
    getwsurl();
} elseif (url::postToText('function') === 'CUSAJX_CreateUser') { //roles: role
    nhRole::dieIfnoRoles(['user']); // roles:   user
    CUSAJX_CreateUser();
} elseif (url::postToText('function') === 'CUSAJX_Update_User') { //roles: role
    nhRole::dieIfnoRoles(['user']); // roles:   user
    CUSAJX_Update_User();
} elseif (url::postToText('function') === 'CUSAJX_UserGridData') { //roles: any
    CUSAJX_UserGridData();
} elseif (url::postToText('function') === 'CUSTAJX_ResetPassword') { //roles:  any
    CUSTAJX_ResetPassword();
} else {
    echo 'missing function ' . url::postToText('function'); // roles: role, user
}

function CUSAJX_WhereClause($defaultOrder, $ordertype)
{
    $where = '';
    $start = url::requestToText('start');
    $length = url::requestToText('length');
    $draw = url::requestToText('draw');
    $limit = '';

    if ($length != -1 && !empty($length) && (isset($start) && !empty($start) && is_numeric($start)) && (isset($length) && !empty($length) && is_numeric($length))) {
        $limit = " limit $start , $length ";
    }

    // $searchVal = strip_tags(url::requestToAny('search')['value']);
    $orderval = strip_tags(url::requestToAny('order')[0]['column']);

    if ($orderval != '') {
        $orderColoumn = strip_tags(url::requestToAny('columns')[$orderval]['data']);
        $ordertype = strip_tags(url::requestToAny('order')[0]['dir']);
        $orderValues = " order by $orderColoumn $ordertype ";
    } else {
        $orderValues = " order by $defaultOrder $ordertype ";
    }

    $where = $orderValues . $limit;
    return $where;
}

function AJAX_GetEventitemsSearchid()
{
    $db = NanoDB::connect();
    $res = GetEventitemsSearchid_new($db);
    print_json_data($res);
}

function SubmitEventItems($data)
{
    $permission = checkModulePrivilege('eventItems', 2);
    if (!$permission) {
        exit('Permission denied');
    }

    $db = NanoDB::connect();
    $EventName = url::requestToText('EventName');
    $mID = url::requestToText('mID');
    $Master = url::requestToText('Master');
    $add_global = url::requestToText('add_global');
    $item_type = url::requestToText('item_type');
    $add_enabled = url::requestToText('add_enabled');
    $search_id = url::requestToText('search_id');
    $Cstatus = url::requestToText('Cstatus');
    $Ctype = url::requestToText('Ctype');
    $Cvalue = url::requestToText('Cvalue');
    $Pval = url::requestToText('Pval');
    $mon_Type = url::requestToText('mon_Type');

    $res = ValidEventitems($db, $EventName);
    if ($res !== "available") {
        $res = AddEventitems_admin($db, $EventName, $mID, $Master, $add_global, $add_enabled, $search_id, $item_type, $Cstatus, $Ctype, $Cvalue, $Pval, $mon_Type);
    }
    print_data($res);
}

function UpdateEventItems($data)
{
    $permission = checkModulePrivilege('eventItems', 2);
    if (!$permission) {
        exit('Permission denied');
    }

    $db = NanoDB::connect();
    $EventName = url::requestToText('EventName');
    $mID = url::requestToText('mID');
    $Master = url::requestToText('Master');
    $add_global = url::requestToText('add_global');
    $item_type = url::requestToText('item_type');
    $add_enabled = url::requestToText('add_enabled');
    $search_id = url::requestToText('search_id');
    $Cstatus = url::requestToText('Cstatus');
    $Ctype = url::requestToText('Ctype');
    $Cvalue = url::requestToText('Cvalue');
    $Pval = url::requestToText('Pval');
    $eventId = url::requestToText('eventId');
    $mon_Type = url::requestToText('mon_Type');

    $res = EditEventitems_admin($db, $EventName, $mID, $Master, $add_global, $add_enabled, $search_id, $item_type, $Cstatus, $Ctype, $Cvalue, $Pval, $eventId, $mon_Type);
    print_data($res);
}

function ValidEventitems($db, $EventName)
{
    $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "dashboard.EventItems WHERE name=?");
    $sql->execute([$EventName]);
    $res = $sql->fetchAll();

    if (is_array($res) && safe_count($res) > 0) {
        $msg = "available";
    } else {
        $msg = "continue";
    }
    return $msg;
}

function AddEventitems_admin($db, $EventName, $mID, $Master, $add_global, $add_enabled, $search_id, $item_type, $Cstatus, $Ctype, $Cvalue, $Pval, $mon_Type)
{
    $res = [];
    $userid = AddEventUser_admin($db);
    $mgroupid = machineUser_admin($db);

    $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "dashboard.EventItems(name,global,enabled,master,id,userid,mgroupid,monint,itemtype,montype) VALUES(?,?,?,?,?,?,?,?,?,?)";
    $sql = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "dashboard.EventItems(name,global,enabled,master,id,userid,mgroupid,monint,itemtype,montype) VALUES(?,?,?,?,?,?,?,?,?,?)");
    $sql->execute([$EventName, $add_global, $add_enabled, $Master, $search_id, $userid, $mgroupid, $mID, $item_type, $mon_Type]);
    $res = $db->lastInsertId();

    if ($res) {
        $id = $db->lastInsertId();
        Add_Criteria($db, $item_type, $Cstatus, $Ctype, $Cvalue, $Pval, $id);
        return true;
    } else {
        return false;
    }
}

function EditEventitems_admin($db, $EventName, $mID, $Master, $add_global, $add_enabled, $search_id, $item_type, $Cstatus, $Ctype, $Cvalue, $Pval, $eventId, $mon_Type)
{
    $res = [];
    $userid = AddEventUser_admin($db);
    $mgroupid = machineUser_admin($db);

    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "dashboard.EventItems SET name = ?,global = ?,enabled = ?,master = ?,id = ?,userid = ?,mgroupid = ?,monint = ?,itemtype = ?,montype = ? WHERE eventitemid = ? ";
    $pdo = $db->prepare($sql);
    $res = $pdo->execute([$EventName, $add_global, $add_enabled, $Master, $search_id, $userid, $mgroupid, $mID, $item_type, $mon_Type, $eventId]);

    $deleteStatus = delete_oldCriteria($db, $eventId);

    if ($deleteStatus) {
        Add_Criteria($db, $item_type, $Cstatus, $Ctype, $Cvalue, $Pval, $eventId);
        return true;
    } else {
        return false;
    }
}

function Add_Criteria($pdo, $item_type, $Cstatus, $Ctype, $Cvalue, $Pval, $id)
{
    $CstatusA = explode(",", $Cstatus);
    $CtypeA = explode(",", $Ctype);
    $CvalueA = explode(",", $Cvalue);
    $PvalA = explode(",", $Pval);

    $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "dashboard.Criteria (itemtype, itemid, statusval, crittype, countval, paramval) VALUES (?,?,?,?,?,?)";
    $stmt = $pdo->prepare($sql);

    foreach ($CstatusA as $key => $value) {
        if ($value !== "'',''") {
            $stmt->execute([$item_type, $id, $value, $CtypeA[$key], $CvalueA[$key], $PvalA[$key]]);
        }
    }

    return true;
}

function delete_oldCriteria($db, $eventId)
{
    $del_sql = "DELETE FROM " . $GLOBALS['PREFIX'] . "dashboard.Criteria WHERE itemid IN(?)";
    $pdo = $db->prepare($del_sql);
    $del_res = $pdo->execute([$eventId]);

    if ($del_res) {
        return true;
    } else {
        return false;
    }
}

function machineUser_admin($db)
{
    $sql = $db->prepare("SELECT mgroupid FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups M WHERE M.name=?  LIMIT 1");
    $sql->execute(['All']);
    $res = $sql->fetch(PDO::FETCH_ASSOC);

    if (is_array($res) && safe_count($res) > 0) {
        return $res['mgroupid'];
    } else {
        return false;
    }
}

function AddEventUser_admin($db)
{
    $res = [];

    $sql = $db->prepare("SELECT userid FROM " . $GLOBALS['PREFIX'] . "core.Users U WHERE U.username='admin' LIMIT 1 ");
    $sql->execute();
    $res = $sql->fetch(PDO::FETCH_ASSOC);

    if (is_array($res) && safe_count($res) > 0) {
        return $res['userid'];
    } else {
        return false;
    }
}

function CUSAJX_UserGridData()
{
    if (!checkModulePrivilege('user', 2)) {
        $jsonData = array("draw" => "", "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);
        print_json_data($jsonData);
        exit();
    }

    $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
    $curPage = url::postToInt('nextPage') - 1;
    $orderVal = url::postToStringAz09('order');
    $sortVal = url::postToStringAz09('sort');
    $limitStart = $limitCount * $curPage;
    $limitEnd = $limitStart + $limitCount;

    $key = '';
    // $searchStr = ' ';
    $conn = NanoDB::connect();
    // $draw = url::requestToText('draw');
    // $searchVal = strip_tags(url::requestTo/Any('search')['value']);

    $notifSearch = url::requestToStringAz09('notifSearch');
    if (!empty($notifSearch)) {
        $whereSearch = " WHERE (firstName like '%" . $notifSearch . "%' OR lastName like '%" . $notifSearch . "%' OR user_email like '%" . $notifSearch . "%')";
    } else {
        $whereSearch = '';
    }

    if (!empty($orderVal)) {
        $orderStr = 'order by ' . $orderVal . ' ' . $sortVal;
    } else {
        $orderStr = 'order by username asc';
    }
    if ($limitStart > 0) {
        $limitStr = " LIMIT " . $limitStart . "," . $limitCount;
    } else {
        $limitStr = " LIMIT " . $limitStart . "," . $limitEnd;
    }
    $whereSearch = $whereSearch . "  ";
    $whereClause = $whereSearch . $orderStr . $limitStr;
    
    $searchValue = '';
    if(isset($_SESSION['searchValue'])){
        $searchType = $_SESSION['searchValue'];
    }
    
    $searchType = '';
    if(isset($_SESSION['searchType'])){
        $searchType = $_SESSION['searchType'];
    }

    $dataScope = UTIL_GetSiteScope_PDO($conn, $searchValue, $searchType);
    if ($searchType == "Servicetag") {
        $dataScope = "'" . $_SESSION["leveltwo"] . "'";
    }

    $user_res = USER_SitesUsers_PDO($key, $conn, $dataScope, $whereClause);
    $data = $user_res['data'];
    $totCount = $user_res['totCount'];

    if (safe_sizeof($data) == 0) {
        $dataArr['largeDataPaginationHtml'] = '';
        $dataArr['html'] = '';
        echo json_encode($dataArr);
    } else {
        $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage);
        $dataArr['html'] = CUSAJX_CreateUserGridArray($data);
        echo json_encode($dataArr);
    }
    create_auditLog('User', 'View', 'Success');
}

function CUSAJX_eventItemsGridData()
{
    $permission = checkModulePrivilege('eventItems', 2);
    if (!$permission) {
        exit('Permission denied');
    }

    $key = '';
    $searchStr = ' ';
    $conn = NanoDB::connect();
    $itemtype = url::requestToText('itemtype');
    $draw = url::requestToText('draw');
    $searchVal = strip_tags(url::requestToAny('search')['value']);

    if ($searchVal != '') {
        $searchStr = "where E.name like '%$searchVal%' OR E.global like '%$searchVal%' OR E.enabled like '%$searchVal%'";
    }

    $searchStr = $searchStr . " GROUP BY E.name ";
    $whereClause = CUSAJX_WhereClause("eventitemid", "desc");
    $whereClause = $searchStr . $whereClause;

    $jsonData = array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => []);

    $tot_res = event_Items($key, $conn, " $searchStr ", $itemtype);
    $user_res = event_Items($key, $conn, $whereClause, $itemtype);
    $jsonData = array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());

    if (safe_count($tot_res) != 0 || safe_count($tot_res) != "0") {
        $recordList = CUSAJX_CreateEventItemsArray($user_res);
        $jsonData = array("draw" => $draw, "recordsTotal" => safe_count($tot_res), "recordsFiltered" => safe_count($tot_res), "data" => $recordList);
    }

    print_json_data($jsonData);
}

function getEditItemData()
{
    $res = checkModulePrivilege('eventItems', 2);

    if (!$res) {
        $res['status'] = 0;
        echo json_encode($res);
        exit();
    }

    $selid = url::requestToText('selid');
    $db = NanoDB::connect();

    $sql = $db->prepare("select E.name,E.`global`,E.enabled,E.monint,E.itemtype,E.id,E.montype from " . $GLOBALS['PREFIX'] . "dashboard.EventItems E Where E.eventitemid = ? ");
    $sql->execute([$selid]);
    $result = $sql->fetchAll(PDO::FETCH_ASSOC);

    $total_count = safe_count($result);

    if ($total_count > 0) {
        $res['status'] = 1;
        $res['result'] = $result;
    } else {
        $res['status'] = 0;
    }

    $sqlC = $db->prepare("select C.statusval,C.crittype,C.countval,C.paramval from " . $GLOBALS['PREFIX'] . "dashboard.Criteria C where C.itemid = ? ");
    $sqlC->execute([$selid]);
    $resultC = $sqlC->fetchAll(PDO::FETCH_ASSOC);
    $total_countC = safe_count($resultC);

    $res['total_countC'] = $total_countC;
    $res['resultC'] = $resultC;

    echo json_encode($res);
}

function CUSAJX_CreateUserGridArray($user_res)
{
    $key = '';
    $conn = NanoDB::connect();
    $userStatusArray = array("Active" => 'Active', "Disabled" => 'In Active', "In Active" => 'Pending');
    $i = 0;
    foreach ($user_res as $key => $value) {
        $userId = $value["userid"];
        $firstName = ($value['firstName'] != '') ? $value['firstName'] : $value['username'];
        $lastName = $value['lastName'];
        $userEmail = $value['user_email'];
        $roleName = USER_Role_Name($value['role_id'], $conn);
        $userStatus = $userStatusArray[USER_Status($value['userStatus'], $value['password'])];
        if ($value['password'] != '') {
            $passStatus = 1;
        } else {
            $passStatus = 0;
        }

        $userType = 'Local User';
        if ($value['userType'] == 'SSO') {
            $userType = 'SSO User';
            $userStatus = 'Active';
        }
        $recordList[$i][] = $firstName;
        $recordList[$i][] = $lastName;
        $recordList[$i][] = $userEmail;
        $recordList[$i][] = $roleName;
        $recordList[$i][] = $userType;
        $recordList[$i][] = $userStatus;
        $recordList[$i][] = $passStatus;
        $recordList[$i][] = $userType;
        $recordList[$i][] = $userId;
        $i++;
    }
    return $recordList;
}

function CUSAJX_CreateEventItemsArray($user_res)
{
    $key = '';
    $conn = NanoDB::connect();

    foreach ($user_res as $key => $value) {
        $name = $value["name"];
        $userid = $value["userid"];
        if ($value["global"] == 1) {
            $global = 'Yes';
        } else if ($value["global"] == 0) {
            $global = 'No';
        } else {
            $global = ' ';
        }
        if ($value["enabled"] == 1) {
            $enabled = 'Yes';
        } else if ($value["enabled"] == 0) {
            $enabled = 'No';
        } else {
            $enabled = ' ';
        }
        if ($value["itemtype"] == 5) {
            $itemtype = 'Availability';
        } else if ($value["itemtype"] == 7) {
            $itemtype = 'Security';
        } else if ($value["itemtype"] == 8) {
            $itemtype = 'Resources';
        } else if ($value["itemtype"] == 10) {
            $itemtype = 'Maintenance';
        } else if ($value["itemtype"] == 9) {
            $itemtype = 'Events';
        } else {
            $itemtype = ' ';
        }

        $filterId = $value["id"];
        $user_sql = $conn->prepare("select name from event.SavedSearches ES where ES.id =?");
        $user_sql->execute([$filterId]);
        $user_result = $user_sql->fetchAll(PDO::FETCH_ASSOC);
        $filterName = $user_result[0]['name'];
        $id = $value["eventitemid"];

        $recordList[] = array(
            "DT_RowId" => $userId,
            "name" => '<p class="ellipsis" onclick="" title="' . $name . '">' . $name . '</p>',
            "userid" => '<p class="ellipsis" onclick="" title="' . $userid . '">' . $userid . '</p>',
            "global" => '<p class="ellipsis" onclick="" title="' . $global . '">' . $global . '</p>',
            "enabled" => '<p class="ellipsis" onclick="" title="' . $enabled . '">' . $enabled . '</p>',
            "filterId" => '<p class="ellipsis" onclick="" title="' . $filterName . '">' . $filterName . '</p>',
            "itemtype" => '<p class="ellipsis" onclick="" title="' . $itemtype . '">' . $itemtype . '</p>',
            "id" => $id,
        );
    }
    return $recordList;
}

function CUSAJX_GetAllUser_Roles()
{
    $permission = checkModulePrivilege('user', 2);
    if (!$permission) {
        exit('Permission denied');
    }

    $conn = NanoDB::connect();
    $allRoles = USER_GetAllRoles($conn);
    foreach ($allRoles as $role) {
        $roleId = $role['assignedRole'];
        $str .= CUSTAJX_CreatSelectedOption('"' . $roleId . '"', $role['displayName'], "");
    }
    $str = trim($str);

    print_data($str);
}

function CUSAJX_GetAdmin_Role()
{
    $str = '';
    $conn = NanoDB::connect();
    $allRoles = USER_GetAdminRole($conn);

    $adminrole = $allRoles['assignedRole'];

    print_data($adminrole);
}

function CUSAJX_CreateUser()
{
    $permission = checkModulePrivilege('adduser', 2);
    if (!$permission) {
        exit('Permission denied');
    }

    $key = '';
    $conn = NanoDB::connect();
    $name = url::issetInPost('userName') ? url::postToText('userName') : '';
    $userEmail = url::issetInPost('userEmail') ? url::postToText('userEmail') : '';
    $userRole = url::issetInPost('userRole') ? url::postToText('userRole') : 'normal';
    $agentRoleName = url::issetInPost('agentRoleName') ? url::postToText('agentRoleName') : '';
    $timeZone = url::issetInPost('timezone') ? url::postToText('timezone') : '';

    $chksum = md5(mt_rand());
    //$resetId = USER_DownloadId_PDO($key, $conn);
    $resetId = USER_UserKey($conn);
    $entityId = $_SESSION["user"]["entityId"];
    if ($_SESSION['user']['busslevel'] == 'Commercial') {
        $mailType = 10;
        CUSAJX_CreateMSPUser($key, $conn, $name, $userEmail, $userRole, $chksum, $resetId, $mailType, $entityId, $agentRoleName, $timeZone);
    } else {
        $mailType = 101;
        CUSAJX_CreatePTSUser($key, $conn, $name, $userEmail, $userRole, $chksum, $resetId, $mailType, $entityId, $agentRoleName, $timeZone);
    }
}

function CUSAJX_CreateMSPUser($key, $conn, $name, $userEmail, $userRole, $chksum, $resetId, $mailType, $entityId, $agentRoleName, $timeZone)
{
    $userlevel = url::issetInRequest('userlevel') ? url::requestToText('userlevel') : '';
    $language = url::issetInRequest('language') ? url::requestToText('language') : 'en';

    try {
        $user_dtls = $_REQUEST;
        $user_dtls["cksum"] = $chksum;
        $user_dtls["resetid"] = $resetId;
        $user_dtls["userrole"] = $userRole;
        $user_dtls["userid"] = $_SESSION["user"]["userid"];
        $user_dtls["username"] = preg_replace('/\s+/', '_', $name);
        $username = $_SESSION["user"]["username"];
        $user_dtls["agentRoleName"] = $agentRoleName;
        $user_dtls["securityOpt"] = url::requestToAny('sectype');
        $user_dtls["timezone"] = $timeZone;
        if ($_SESSION["user"]["Avira_Inst"] == 1 || $_SESSION["user"]["Avira_Inst"] == '1') {
            if ($userlevel == 'reseller') {
                $user_dtls["eid"] = $_SESSION["user"]["cId"];
            } else {
                $userCustomer = url::issetInRequest('userCustomer') ? url::requestToAny('userCustomer') : '';
                $user_dtls["eid"] = $userCustomer;
            }
        } else {
            $user_dtls["eid"] = $_SESSION["user"]["cId"];
        }
//
//      function generateRandomString($length = 8) {
//        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
//        $charactersLength = strlen($characters);
//        $randomString = '';
//        for ($i = 0; $i < $length; $i++) {
//          $randomString .= $characters[random_int(0, $charactersLength - 1)];
//        }
//        return $randomString;
//      }


        $randomUserPass = getenv('DEFAULT_USER_PASSWORD');

        $insert_user_id = USER_AddUser('', $conn, $user_dtls, $randomUserPass);
        if ($insert_user_id == "DUPLICATE") {
            $msg = "DUPLICATE";
            $result = ['msg' => $msg];
            print_data(json_encode($result));
        } else if ($insert_user_id > 0) {
            if ($_SESSION["user"]["Avira_Inst"] == 1 || $_SESSION["user"]["Avira_Inst"] == '1') {
                if ($userlevel == 'reseller') {
                    $sitesArray = USER_GetSiteWithUsername($conn, $username);
                } else if ($userlevel == 'customer') {
                    $sitesArray = USER_GetSiteWithCompId($conn, $userCustomer);
                }
            } else {
                $userCustomer = url::issetInRequest('userCustomer') ? url::requestToAny('userCustomer') : '';
                $sitesArray = $userCustomer;
            }
            foreach ($sitesArray as $sitename) {
                $update = USER_InsertSite_PDO($conn, $user_dtls["username"], $sitename, $user_dtls["lastname"]);
            }
            USER_InsertCheckSum($conn, $username, $chksum);
          $resultSendEmail = User_SendEmail_PDO($conn, $name, $userEmail, $resetId, $mailType, $entityId, $language);

          $msg = 'DONE';
          if ($resultSendEmail == 0){
            $pass = 'default';
          }else{
            $pass = '';
          }
          $timestamp = strtotime("+1 month");
          $updSql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET passwordDate=?,userStatus='1' where user_email=?";
          NanoDB::query($updSql,[$timestamp,$userEmail]);
          $result = ['msg' => $msg, 'pass' => $pass];
          print_data(json_encode($result));
        } else {
            $msg = 'NOTDONE';
            $result = ['msg' => $msg];
            print_data(json_encode($result));
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function CUSAJX_CreatePTSUser($key, $conn, $name, $userEmail, $userRole, $chksum, $resetId, $mailType, $entityId, $agentRoleName, $timeZone)
{
    $userlevel = url::issetInRequest('userlevel') ? url::requestToText('userlevel') : '';
    $language = url::issetInRequest('language') ? url::requestToText('language') : 'en';

    try {

        $user_dtls = $_REQUEST;
        $user_dtls["cksum"] = $chksum;
        $user_dtls["resetid"] = $resetId;
        $user_dtls["userrole"] = $userRole;
        $user_dtls["username"] = preg_replace('/\s+/', '_', $name);
        $user_dtls["eid"] = $_SESSION["user"]["cId"];
        $user_dtls["userid"] = $_SESSION["user"]["userid"];
        $username = $_SESSION["user"]["username"];
        $user_dtls["agentRoleName"] = $agentRoleName;
        $user_dtls["timezone"] = $timeZone;

        $randomUserPass = getenv('DEFAULT_USER_PASSWORD');

        $insert_user_id = USER_AddUser($key, $conn, $user_dtls, $randomUserPass);

        if ($insert_user_id == "DUPLICATE") {

          $msg = "DUPLICATE";
          $result = ['msg' => $msg];
          print_data(json_encode($result));
        } else if ($insert_user_id > 0) {

            $sitesArray = USER_GetSiteWithUsername($conn, $username);

            foreach ($sitesArray as $sitename) {
                $update = USER_InsertSite($conn, $user_dtls["username"], $sitename, $user_dtls["lastname"]);
            }

            USER_InsertCheckSum($conn, $username, $chksum);
          $resultSendEmail = User_SendEmail_PDO($conn, $name, $userEmail, $resetId, $mailType, $entityId, $language);
          $msg = 'DONE';
          if ($resultSendEmail == 0){
            $pass = 'default';
          }else{
            $pass = '';
          }
          $timestamp = strtotime("+1 month");
          $updSql = "UPDATE " . $GLOBALS['PREFIX'] . "core.Users SET passwordDate=?,userStatus='1' where user_email=?";
          NanoDB::query($updSql,[$timestamp,$userEmail]);
          $result = ['msg' => $msg, 'pass' => $pass];
          print_data(json_encode($result));
        } else {
          $msg = 'NOTDONE';
          $result = ['msg' => $msg];
          print_data(json_encode($result));;
        }
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function CUSAJX_SiteList()
{
    $key = '';
    $conn = NanoDB::connect();
    $userid = url::issetInRequest('userid') ? url::requestToText('userid') : '';
    $site_res = USER_GetLoggedUserSite($key, $conn, $userid);

    if (is_array($site_res) && safe_count($site_res) > 0) {
        print_json_data($site_res);
    } else {
        print_json_data(array());
    }
}

function CUSAJX_GetUserDetail()
{
    $permission = checkModulePrivilege('user', 2);
    if (!$permission) {
        exit('Permission denied');
    }

    $key = '';
    $conn = NanoDB::connect();
    $seluid = url::requestToText('userid');
    $loggedUid = $_SESSION["user"]["userid"];
    $cId = isset($_SESSION["user"]["cId"]) ? $_SESSION["user"]["cId"] : '';
    $userRole = url::issetInRequest('userRole') ? url::requestToText('userRole') : 'normal';
    $loggedCtype = isset($_SESSION["user"]["customerType"]) ? $_SESSION["user"]["customerType"] : '';
    $customerStr = '';
    $userType = 'reseller';

    try {

        $details = USER_GetUserDetail($key, $conn, $seluid);
        $ch_id = $details['ch_id'];
        $roleName = USER_Role_Name($details['role_id'], $conn);
        $res = USER_GetLoggedUserSite($key, $conn, $seluid);

        $selUserSites = USER_GetSelectedUserSites($conn, $seluid);
        $loggedUserSites = USER_GetSelectedUserSites($conn, $loggedUid);
        $editSiteList = CUSAJX_CreateEditSiteList($loggedUserSites, $selUserSites);
        $securityOpt = CUSAJX_CreateEditSecList($details['securityType']);
        $details['customers'] = trim($customerStr);
        $details['rolename'] = $roleName;
        $details['userType'] = $userType;
        $details['rolenameval'] = $userRole;
        $details['selectedSites'] = $editSiteList;
        $details['secType'] = $securityOpt;

        unset($details['password']);
        unset($details['passwordHistory']);
        unset($details['apipass']);
        unset($details['cksum']);
        unset($details['access_token']);

        print_json_data($details);
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function CUSAJX_CreateEditSecList($selSec)
{

    global $securityArray;
    $seclistArr = explode(',', $selSec);
    $str = '';

    foreach ($securityArray as $key => $val) {
        if (in_array($val, $seclistArr)) {
            $str .= CUSTAJX_CreatSelectedOption('"' . $key . '"', $val, "selected");
        } else {
            $str .= CUSTAJX_CreatSelectedOption('"' . $key . '"', $val, "");
        }
    }

    return $str;
}

function CUSAJX_CreateEditSiteList($loggedUserSites, $selUserSites)
{
    $str = '';
    $temp = [];
    $selected = [];

    foreach ($selUserSites as $i => $value) {
        $temp[$i] = $value['customer'];
    }

    foreach ($loggedUserSites as $key => $value) {

        if (!in_array($value['customer'], $selected)) {
            $trimedCompName = UTIL_GetTrimmedGroupName($value['customer']);

            if (in_array($value['customer'], $temp)) {
                $str .= CUSTAJX_CreatSelectedOption('"' . $value['customer'] . '"', $trimedCompName, "selected");
            } else {
                $str .= CUSTAJX_CreatSelectedOption('"' . $value['customer'] . '"', $trimedCompName, "");
            }
        }

        $selected[] = $value['customer'];
    }

    return $str;
}

function CUSAJX_Update_User()
{
    $key = '';
    $conn = NanoDB::connect();
    $selectedUserid = url::requestToText('sel_userid');
//  $Site_name = url::requestToText('Sitename');
    $Site_name = url::arrayToString('Sitename');
    $fname = url::issetInRequest('userName') ? url::requestToText('userName') : '';
    $lname = url::issetInRequest('lastname') ? url::requestToText('lastname') : '';
    $userrole = url::issetInRequest('userrole') ? url::requestToText('userrole') : 'normal';
    $timezone = url::issetInRequest('timezone') ? url::requestToText('timezone') : '';
    $ch_id = $_SESSION["user"]["cId"];
    $sectype = url::requestToAny('sectype');

    try {

        $isLoggedInUser = 0;
        if ($isLoggedInUser == 1) {
            $arr = array("status" => 0, "message" => "Don't have enough rights to perform operation");
        } else {
            $update_user_id = USER_EditNewUser($key, $conn, $ch_id, $selectedUserid, $fname, $lname, $userrole, $Site_name, $sectype, $timezone);

            if ($update_user_id) {
                $arr = array("status" => 1, "message" => "Selected User updated successfully");
            } else {
                $arr = array("status" => 2, "message" => "Some error occurred while performing operation");
            }
        }
        print_json_data($arr);
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function CUSAJX_DeleteUser()
{
    $permission = checkModulePrivilege('deleteuser', 2);
    if (!$permission) {
        exit(json_encode(array("status" => 0, "message" => "Permission denied")));
    }

    $key = '';
    $conn = NanoDB::connect();
    $selectedUserid = url::requestToText('selectedUserid');

    $sql = $conn->prepare("select parent_id from " . $GLOBALS['PREFIX'] . "core.Users where userid = ?");
    $sql->execute([$selectedUserid]);
    $res = $sql->fetch();
    $parent_id = $res['parent_id'];
    try {
        $isLoggedInUser = USER_IsLoggedUser('', $selectedUserid);
        if ($isLoggedInUser == 0) {
            $arr = array("status" => 0, "message" => "<span>Don't have enough rights to perform operation</span>");
        } else {
            $des_res = USER_DeleteSite($key, $conn, $selectedUserid);
            $des_res = USER_DeleteUser($key, $conn, $selectedUserid);
            if ($des_res) {
                $arr = array("status" => 1, "message" => "<span>Selected User deleted successfully</span>");
            } else {
                $arr = array("status" => 0, "message" => "<span>Some error occurred while performing operation</span>");
            }
        }

        print_json_data($arr);
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function CUSAJX_GetExcelSheetObject($headers, $width)
{
    try {
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
        foreach ($headers as $key => $value) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($key)->setWidth($width);
            $objPHPExcel->getActiveSheet()->setCellValue($key . '1', $value);
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
    return $objPHPExcel;
}

function CUSAJX_CreateUserExcelSheet($objPHPExcel, $resultArray, $conn)
{
    $userStatusArray = array("Active" => 'Active', "Disabled" => 'In Active', "In Active" => 'Pending');
    try {
        $index = 2;
        foreach ($resultArray as $key => $value) {

            $roleName = USER_Role_Name($value['role_id'], $conn);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, ($value['firstName'] != '') ? $value['firstName'] : $value['username']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value['lastName']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $value['user_email']);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $roleName);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $userStatusArray[USER_Status($value['userStatus'], $value['password'])]);
            $index++;
        }
        return $objPHPExcel;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function CUSAJX_ExportUser()
{
    $permission = checkModulePrivilege('userexport', 2);
    if (!$permission) {
        exit('Permission denied');
    }

    $key = '';
    $conn = NanoDB::connect();
    $searchValue = $_SESSION["searchValue"];
    $searchType = $_SESSION["searchType"];

    $dataScope = UTIL_GetSiteScope_PDO($conn, $searchValue, $searchType);

    if ($searchType == "Servicetag") {
        $dataScope = "'" . $_SESSION["leveltwo"] . "'";
    }

    $headerArray = array("A" => "First Name", "B" => "Last Name", "C" => "User Email", "D" => "Role Name", "E" => "Userstatus");

    try {
        $objPHPExcel = CUSAJX_GetExcelSheetObject($headerArray, 30);
        $res = USER_SitesUsers_PDO($key, $conn, $dataScope, " GROUP BY C.username ", 'export');

        if (is_array($res) && safe_count($res) > 0) {
            $objPHPExcel = CUSAJX_CreateUserExcelSheet($objPHPExcel, $res['data'], $conn);
        } else {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . 2, 'No Data Avaliable');
        }
        $fn = "Users.xls";
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fn . '"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        ob_end_clean();
        $objWriter->save('php://output');
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
    }
}

function CUSAJX_GetLicenseCount()
{
    $key = '';
    $conn = db_connect();

    $jsonRes = array("totalLicenses" => 0, "unusedLicenses" => 0, "installedLicenses" => 0, "renewLicenses" => 0);
    $loggedEid = $_SESSION["user"]["cId"];
    $bussLevel = $_SESSION['user']['busslevel'];

    if ($bussLevel == 'Consumer') {
        $licenseCountRes = RSLR_GetPTSLicenseCount($key, $conn, $loggedEid);
    }

    if (is_array($licenseCountRes) && safe_count($licenseCountRes) > 0) {
        $jsonRes["totalLicenses"] = $licenseCountRes['lseCnt'];
        if ($bussLevel == 'Consumer') {
            $jsonRes["unusedLicenses"] = $licenseCountRes['insCnt'];
        } else {
            $jsonRes["unusedLicenses"] = $licenseCountRes['lseCnt'] - $licenseCountRes['insCnt'];
        }
        $jsonRes["renewLicenses"] = $licenseCountRes['renewLicensesCnt'];
    }
    print_json_data($jsonRes);
}

function CUSAJX_GetComercialLicenseCount()
{
    $key = '';
    $conn = NanoDB::connect();

    $jsonRes = array("totalLicenses" => 0, "unusedLicenses" => 0, "installedLicenses" => 0, "renewLicenses" => 0);
    $loggedEid = $_SESSION["user"]["cId"];
    $bussLevel = $_SESSION['user']['busslevel'];
    $NH_lic = url::requestToText('NHCode');

    if ($bussLevel == 'Commercial') {
        $licenseCountRes = RSLR_GetLicenseCount_PDO($key, $conn, $loggedEid, $NH_lic);
    }

    if (is_array($licenseCountRes) && safe_count($licenseCountRes) > 0) {
        $jsonRes["totalLicenses"] = $licenseCountRes['lseCnt'];
        if ($bussLevel == 'Consumer') {
            $jsonRes["unusedLicenses"] = $licenseCountRes['insCnt'];
        } else {
            $jsonRes["unusedLicenses"] = $licenseCountRes['lseCnt'] - $licenseCountRes['insCnt'];
        }
        $jsonRes["renewLicenses"] = $licenseCountRes['renewLicensesCnt'];
    }

    print_json_data($jsonRes);
}

function CUSAJX_GetOrderGridData()
{
    $key = '';
    $conn = NanoDB::connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $draw = url::requestToText('draw');

    $totalCount = RSLR_GetOrderDetailsGrid_PDO($key, $conn, $loggedEid, "");
    $jsonData = array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());

    if (safe_count($totalCount) != 0) {
        $resultArray = RSLR_GetOrderDetailsGrid_PDO($key, $conn, $loggedEid, "");
        foreach ($resultArray as $key => $value) {

            $licenseCnt = $value['licenseCnt'];
            $purchaseDate = ($value['purchaseDate'] != "") ? date("m/d/Y h:i A", $value['purchaseDate']) : "Not Available";
            $validity = $value['noofDays'] . ' days';
            $recordList[] = array(
                "DT_RowId" => $value['orderNum'],
                "orderNum" => $value['orderNum'],
                "licenseCnt" => $licenseCnt,
                "purchaseDate" => $purchaseDate,
                "validity" => $validity,
            );
        }
        $jsonData = array("draw" => $draw, "recordsTotal" => safe_count($totalCount), "recordsFiltered" => safe_count($totalCount), "data" => $recordList);
    }
    print_json_data($jsonData);
}

function CUSAJX_Avira_GetOrderGridData()
{
    $key = '';
    $conn = NanoDB::connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $draw = 1;

    $resultArray = RSLR_GetAviraOrderDetailsGrid($key, $conn, $loggedEid, "");
    $totalCount = safe_count($resultArray);
    $jsonData = array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());
    $recordList = [];

    if ($totalCount != 0) {
        if ($_SESSION["user"]["Avira_Inst"] == 1 || $_SESSION["user"]["Avira_Inst"] == '1') {
            foreach ($resultArray as $value) {

                $email = '<p class="ellipsis">-</p>';
                $compName = '<p class="ellipsis">-</p>';
                $aviraDetails = RSLR_GetAviraLicenses($conn, "otcCode", $value['aviraOtc']);
                if (is_array($aviraDetails) && safe_count($aviraDetails) > 0) {
                    $email = CUSTAJX_CreatPTag($aviraDetails['emailId']);
                    $tempCompName = CUSTAJX_GetTrimmedCompName($aviraDetails['companyname']);
                    $compName = CUSTAJX_CreatPTag($tempCompName);
                }

                $aviraOtc = CUSTAJX_CreatPTag($value['aviraOtc']);
                $skuDesc = CUSTAJX_CreatPTag($value['skuDesc']);
                $licenseCnt = CUSTAJX_CreatPTag($value['licenseCnt']);

                $recordList[] = array(
                    "DT_RowId" => $value['aviraOtc'],
                    "OTC_Code" => $aviraOtc,
                    "email" => utf8_encode($email),
                    "compname" => utf8_encode($compName),
                    "desc" => utf8_encode($skuDesc),
                    "licenses" => $licenseCnt,
                );
            }
        } else {
            $recordList = [];
        }
        $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    }
    print_json_data($jsonData);
}

function CUSTAJX_GetCustOTC()
{
    $key = '';
    $conn = NanoDB::connect();
    $compId = url::requestToText('compId');
    $proId = url::requestToText('proId');
    $customerNum = url::issetInRequest('customerNum') ? url::requestToText('customerNum') : '';
    $orderNum = url::issetInRequest('orderNum') ? url::requestToText('orderNum') : '';

    $res = RSLR_GetCustOTC($key, $conn, $compId, $proId, $customerNum, $orderNum);

    if (is_array($res) && safe_count($res) > 0) {
        print_json_data($res);
    } else {
        $array = array("status" => "error");
        print_json_data($array);
    }
}

function CUSTAJX_UpdateCustOTC()
{
    $key = '';
    $conn = NanoDB::connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $compId = url::requestToText('compId');
    $proId = url::requestToText('proId');
    $customerNum = url::issetInRequest('customerNum') ? url::requestToText('customerNum') : '';
    $oldOTC = url::issetInRequest('oldOTC') ? url::requestToText('oldOTC') : '';
    $newOTC = url::issetInRequest('newOTC') ? url::requestToText('newOTC') : '';
    $orderNum = url::issetInRequest('orderNum') ? url::requestToText('orderNum') : '';

    $res = RSLR_UpdateCustNewOTC($key, $conn, $loggedEid, $newOTC, $oldOTC, $customerNum, $orderNum, $compId, $proId);

    if (is_array($res) && safe_count($res) > 0) {
        print_json_data($res);
    } else {
        $array = array("status" => "error");
        print_json_data($array);
    }
}

function CUSTAJX_GetSelCustOTC()
{
    $key = '';
    $conn = NanoDB::connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $compId = url::requestToText('compId');
    $proId = url::requestToText('proId');
    $customerNum = url::issetInRequest('customerNum') ? url::requestToText('customerNum') : '';
    $orderNum = url::issetInRequest('orderNum') ? url::requestToText('orderNum') : '';
    $newOTC = url::issetInRequest('newOTC') ? url::requestToText('newOTC') : '';
    $oldOTC = url::issetInRequest('oldOTC') ? url::requestToText('oldOTC') : '';

    $res = RSLR_GetSelOTCCnt($key, $conn, $loggedEid, $newOTC, $oldOTC, $customerNum, $orderNum, $compId, $proId);

    if (is_array($res) && safe_count($res) > 0) {
        print_json_data($res);
    } else {
        $array = array("status" => "error");
        print_json_data($array);
    }
}

function revoke_aviraSubscription()
{
    $key = '';
    $conn = NanoDB::connect();

    $loggedEid = $_SESSION["user"]["cId"];
    $customerNum = url::issetInRequest('customerNum') ? url::requestToText('customerNum') : '';
    $orderNum = url::issetInRequest('orderNum') ? url::requestToText('orderNum') : '';
    $servicetag = url::issetInRequest('servicetag') ? url::requestToText('servicetag') : '';

    $res = RSLR_RevokeaviraSubscription($key, $conn, $customerNum, $orderNum, $servicetag);

    if (is_array($res) && safe_count($res) > 0) {
        print_json_data($res);
    } else {
        $array = array("status" => "error");
        print_json_data($array);
    }
}

function CUSAJX_GetSkuGridData()
{
    $key = '';
    $conn = NanoDB::connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $draw = url::requestToText('draw');

    $totalCount = RSLR_GetSkuDetailsGrid($key, $conn, $loggedEid, "");
    $jsonData = array("draw" => $draw, "recordsTotal" => 0, "recordsFiltered" => 0, "data" => array());

    if (safe_count($totalCount) != 0) {
        $resultArray = RSLR_GetSkuDetailsGrid($key, $conn, $loggedEid, "");
        foreach ($resultArray as $key => $value) {
            $validity = $value['noOfDays'] . ' days';

            $recordList[] = array(
                "DT_RowId" => $value['skuRef'] . '--' . $loggedEid,
                "skuName" => $value['skuName'],
                "licenseCnt" => $value['licenseCnt'],
                "validity" => $validity,
                "skuRef" => $value['skuRef'],
                "cid" => $loggedEid,
            );
        }
        $jsonData = array("draw" => $draw, "recordsTotal" => safe_count($totalCount), "recordsFiltered" => safe_count($totalCount), "data" => $recordList);
    }
    print_json_data($jsonData);
}

function CUSAJX_GetCustomerGrid()
{
    $key = '';
    $conn = NanoDB::connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $draw = url::requestToText('draw');
    $customerType = $_SESSION['user']['customerType'];
    $totalRecords = CUSAJX_GetCustomersArray($key, $conn, $loggedEid, $customerType);
    global $download_ClientUrl;

    if (is_array($totalRecords) && safe_count($totalRecords) > 0) {
        foreach ($totalRecords as $value) {
            $url = $download_ClientUrl . 'eula.php?id=' . $value['downloadId'];
            $rowId = $value['compId'] . '##' . $value['processId'] . '##' . $value['customerNum'] . '##' . $value['orderNum'];
            $custName = '<p class="ellipsis" title="' . $value['coustomerFirstName'] . '">' . $value['coustomerFirstName'] . '</p>';
            $pccount = '<p class="ellipsis" title="' . $value['installedCnt'] . '">' . $value['installedCnt'] . '</p>';
            $recordList[] = array('DT_RowId' => $rowId, 'customername' => $custName, 'pccount' => $pccount, 'sitename' => $value['siteName'], 'custNum' => $value['customerNum'], 'custId' => $value['downloadId'], 'url' => $url);
        }
    } else {
        $recordList = [];
    }
    $jsonData = array("draw" => $draw, "recordsTotal" => safe_count($totalRecords), "recordsFiltered" => safe_count($totalRecords), "data" => $recordList);
    print_json_data($jsonData);
}

function CUSAJX_GetSkuCustomerGrid()
{
    $key = '';
    $conn = NanoDB::connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $draw = url::requestToText('draw');
    $customerType = $_SESSION['user']['customerType'];
    $skuRef = url::requestToText('skuRef');
    $totalRecords = RSLR_GetSkuBasedCustomer($key, $conn, $loggedEid, $customerType, $skuRef);
    global $download_ClientUrl;

    if (is_array($totalRecords) && safe_count($totalRecords) > 0) {
        foreach ($totalRecords as $key => $value) {
            $url = $download_ClientUrl . 'eula.php?id=' . $value['downloadId'];
            $rowId = $value['compId'] . '##' . $value['processId'] . '##' . $value['customerNum'] . '##' . $value['orderNum'];
            $custName = '<p class="ellipsis" title="' . $value['coustomerFirstName'] . '">' . $value['coustomerFirstName'] . '</p>';
            $pccount = '<p class="ellipsis" title="' . $value['installedCnt'] . '">' . $value['installedCnt'] . '</p>';
            $recordList[] = array('DT_RowId' => $rowId, 'customername' => $custName, 'pccount' => $pccount, 'sitename' => $value['siteName'], 'custNum' => $value['customerNum'], 'custId' => $value['downloadId'], 'url' => $url);
        }
    } else {
        $recordList = [];
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => safe_count($totalRecords), "recordsFiltered" => safe_count($totalRecords), "data" => $recordList);
    print_json_data($jsonData);
}

function CUSAJX_GetCustomersArray($key, $conn, $loggedEid, $customerType)
{
    $array = [];
    switch ($customerType) {
        case 0:
            $array = RSLR_GetAllCustomers($key, $conn, $loggedEid);
            break;
        case 1:
            $array = RSLR_GetEntityCustomers($key, $conn, $loggedEid);
            break;
        case 2:
            $array = RSLR_GetResellerCustomers($key, $conn, $loggedEid);
            break;
        case 5:
            $array = RSLR_GetCustomer($key, $conn, $loggedEid);
            break;
        default:
            break;
    }

    return $array;
}

function CUSAJX_GetCustomerDevicesData()
{
    $key = '';
    $conn = NanoDB::connect();

    $loggedEid = $_SESSION["user"]["cId"];
    $compId = url::issetInRequest('compId') ? url::requestToText('compId') : '';
    $processId = url::issetInRequest('processId') ? url::requestToText('processId') : '';
    $customerNum = url::issetInRequest('customerNum') ? url::requestToText('customerNum') : '';
    $orderNum = url::issetInRequest('orderNum') ? url::requestToText('orderNum') : '';

    $whereClause = "";
    $totalCount = RSLR_GetCustomerDevicesGrid_PDO($key, $conn, $compId, $processId, $customerNum, $orderNum, "");
    $recordList = array();

    if (is_array($totalCount) && safe_count($totalCount) > 0) {
        $resultArray = RSLR_GetCustomerDevicesGrid_PDO($key, $conn, $compId, $processId, $customerNum, $orderNum, $whereClause);
        if ($_SESSION["user"]["Avira_Inst"] == 1 || $_SESSION["user"]["Avira_Inst"] == '1') {
            $recordList = CUSAJX_CreateAviraGridArray($resultArray, $key, $conn);
        } else {
            $recordList = CUSAJX_CreateRegularGridArray($resultArray);
        }
    }
    print_json_data($recordList);
}

function CUSAJX_CreateRegularGridArray($resultArray)
{
    $compId = url::issetInRequest('compId') ? url::requestToText('compId') : '';
    $processId = url::issetInRequest('processId') ? url::requestToText('processId') : '';
    $customerNum = url::issetInRequest('customerNum') ? url::requestToText('customerNum') : '';
    $orderNum = url::issetInRequest('orderNum') ? url::requestToText('orderNum') : '';

    foreach ($resultArray as $key => $value) {
        $chkValue = '';
        $deviceInfo = "Os:" . $value['machineOS'] . ",Model No:" . $value['machineModelNum'];

        if ($value['sid'] != '') {
            $chkValue = $compId . '---' . $processId . '---' . $customerNum . '---' . $orderNum . '---' . $value['sid'];
        } else {
            $chkValue = $compId . '---' . $processId . '---' . $customerNum . '---' . $orderNum;
        }

        $installationDate = ($value['installationDate'] != "") ? date("m/d/Y h:i", $value['installationDate']) : '-';
        $uninstallDate = ($value['uninstallDate'] != "") ? date("m/d/Y h:i", $value['uninstallDate']) : '-';
        $serviceTag = ($value['serviceTag'] != "") ? '<p class="ellipsis" title="' . $value['serviceTag'] . '">' . $value['serviceTag'] . '</p>' : '-';
        $machineOs = ($value['serviceTag'] != "") ? '<p class="ellipsis" title="' . $deviceInfo . '">' . $deviceInfo . '</p>' : '-';

        if ($value['downloadStatus'] == 'EXE' && $value['revokeStatus'] == 'I') {
            $deviceStatus = 'Installed';
        } else if ($value['downloadStatus'] == 'EXE' && $value['revokeStatus'] == 'R') {
            $deviceStatus = 'Revoked';
        } else if (($value['downloadStatus'] == 'D' || $value['downloadStatus'] == 'G') && $value['revokeStatus'] == 'I') {
            $deviceStatus = 'Not Installed';
        } else {
            $deviceStatus = $value['orderStatus'];
        }

        $recordList[] = array($orderNum, $serviceTag, $machineOs, $installationDate, $uninstallDate, $deviceStatus);
    }
    return $recordList;
}

function CUSAJX_CreateAviraGridArray($resultArray, $key, $conn)
{
    $compId = url::issetInRequest('compId') ? url::requestToText('compId') : '';
    $processId = url::issetInRequest('processId') ? url::requestToText('processId') : '';
    $customerNum = url::issetInRequest('customerNum') ? url::requestToText('customerNum') : '';
    $orderNum = url::issetInRequest('orderNum') ? url::requestToText('orderNum') : '';

    foreach ($resultArray as $key => $value) {
        $chkValue = '';
        $aviraInstall = "-";
        $aviraUninstall = "-";
        $gtwaySts = $value['gatewayMachine'];
        $gtImg = '';
        if ($gtwaySts === '1' || $gtwaySts === 1) {
            $gtImg = '<img src="../vendors/images/gateway.png" title"Gateway" style="height: 15px;">';
        }

        if ($value['sid'] != '') {
            $chkValue = $compId . '---' . $processId . '---' . $customerNum . '---' . $orderNum . '---' . $value['sid'];
        } else {
            $chkValue = $compId . '---' . $processId . '---' . $customerNum . '---' . $orderNum;
        }

        $NHInstall = ($value['installationDate'] != "") ? '<p class="ellipsis" title="' . date("l, F d, Y h:i:s", $value['installationDate']) . '">' . date("l, F d, Y h:i:s", $value['installationDate']) . '</p>' : '-';
        $NHUninstall = ($value['uninstallDate'] != "") ? '<p class="ellipsis" title="' . date("l, F d, Y h:i:s", $value['uninstallDate']) . '">' . date("l, F d, Y h:i:s", $value['uninstallDate']) . '</p>' : '-';
        $serviceTag = ($value['serviceTag'] != "") ? '<p class="ellipsis" title="' . $value['serviceTag'] . '">' . $gtImg . '&nbsp;' . $value['serviceTag'] . '</p>' : '-';
        $ordStatus = $value['orderStatus'];
        if ($ordStatus == 'Active') {
            $ordStatus = '<span style="color:green;">' . $ordStatus . '</span>';
        } else {
            $ordStatus = '<span style="color:red;">' . $ordStatus . '</span>';
        }
        if ($value['aviraId'] != '') {
            $deviceStatus = CUSAJX_GetAviraStatus($value['aviraId']) . '/' . $ordStatus;
            $aviraDetails = RSLR_GetAviraDetails($key, $conn, $value['sid']);
            $aviraInstall = '<p class="ellipsis" title="' . $aviraDetails[0]['productDate'] . '">' . $aviraDetails[0]['productDate'] . '</p>';
            $aviraUninstall = '<p class="ellipsis" title="' . $aviraDetails[0]['licenseExpiration'] . '">' . $aviraDetails[0]['licenseExpiration'] . '</p>';
        } else {

            $deviceStatus = CUSAJX_GetAviraStatus($value['aviraId']) . '/' . $ordStatus;
            $aviraInstall = '-';
            $aviraUninstall = '-';
        }
        $rowId = $value['customerNum'] . '---' . $value['orderNum'] . '---' . $value['serviceTag'] . '---' . $value['orderStatus'];
        $recordList[] = array("DT_RowId" => $rowId, $serviceTag, $aviraInstall, $aviraUninstall, $NHInstall, $NHUninstall, $deviceStatus);
    }
    return $recordList;
}

function CUSAJX_GetAviraStatus($aviraId)
{
    $deviceStatus = '';

    if ($aviraId == 'NULL' || $aviraId == '0' || $aviraId == 'null' || $aviraId == 0 || $aviraId == null) {
        $deviceStatus = '<span>Not Installed</span>';
    } else {
        $deviceStatus = '<span>Installed</span>';
    }
    return $deviceStatus;
}

function CUSAJX_CreateCustomerExcelSheet($objPHPExcel, $custRes, $conn)
{
    try {
        $index = 2;
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);

        $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->setTitle("Customer Detailed Devices");
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);

        if ($_SESSION["user"]["Avira_Inst"] == 1 || $_SESSION["user"]["Avira_Inst"] == '1') {
            $objPHPExcel = CUSAJX_GetAviraDevicesExcelSheet($objPHPExcel);
        } else {
            $objPHPExcel = CUSAJX_GetRegularDevicesExcelSheet($objPHPExcel);
        }

        foreach ($custRes as $key => $value) {
            $objPHPExcel->setActiveSheetIndex(0);
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['coustomerFirstName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value['installedCnt']);

            if ($_SESSION["user"]["Avira_Inst"] == 1 || $_SESSION["user"]["Avira_Inst"] == '1') {
                $licenseKey = $value['licenseKey'];
                $OTCDetails = RSLR_GetOTCDetails($conn, $cid, $licenseKey);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value['installedCnt']);
                $objPHPExcel = CUSAJX_CreateAviraDeviceExcelSheet($key, $conn, $value, $objPHPExcel);
            } else {

                $objPHPExcel = CUSAJX_CreateRegularDeviceExcelSheet($key, $conn, $value, $objPHPExcel);
            }

            $index++;
        }
        return $objPHPExcel;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function CUSAJX_GetAviraDevicesExcelSheet($objPHPExcel)
{
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "Customer Name");
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "Device ID");
    $objPHPExcel->getActiveSheet()->setCellValue('C1', "Operating System");
    $objPHPExcel->getActiveSheet()->setCellValue('D1', "Model No.");
    $objPHPExcel->getActiveSheet()->setCellValue('E1', "Avira Installed");
    $objPHPExcel->getActiveSheet()->setCellValue('F1', "Avira Uninstalled");
    $objPHPExcel->getActiveSheet()->setCellValue('G1', "NH Installed");
    $objPHPExcel->getActiveSheet()->setCellValue('H1', "NH Uninstalled");
    $objPHPExcel->getActiveSheet()->setCellValue('I1', "Avira Status");
    $objPHPExcel->getActiveSheet()->setCellValue('J1', "Product Name");
    return $objPHPExcel;
}

function CUSAJX_GetRegularDevicesExcelSheet($objPHPExcel)
{
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "Customer Name");
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "Order Number");
    $objPHPExcel->getActiveSheet()->setCellValue('C1', "Device ID");
    $objPHPExcel->getActiveSheet()->setCellValue('D1', "Operating System");
    $objPHPExcel->getActiveSheet()->setCellValue('E1', "Model No.");
    $objPHPExcel->getActiveSheet()->setCellValue('F1', "Installed On");
    $objPHPExcel->getActiveSheet()->setCellValue('G1', "Contract End Date");
    $objPHPExcel->getActiveSheet()->setCellValue('H1', "Status");
    return $objPHPExcel;
}

function CUSAJX_CreateRegularDeviceExcelSheet($key, $conn, $result, $objPHPExcel)
{
    $compId = $result['compId'];
    $processId = $result['processId'];
    $customerNum = $result['customerNum'];
    $orderNum = $result['orderNum'];

    $deviceRes = RSLR_GetCustomerDevicesGrid_PDO($key, $conn, $compId, $processId, $customerNum, $orderNum, "");

    $objPHPExcel->setActiveSheetIndex(1);
    $highestRow = $objPHPExcel->setActiveSheetIndex(1)->getHighestRow();

    if (is_array($deviceRes) && safe_count($deviceRes) > 0) {
        foreach ($deviceRes as $key => $value) {
            $highestRow++;
            $serviceTag = ($value['serviceTag'] != '') ? $value['serviceTag'] : "-";
            $os = ($value['machineOS'] != '') ? $value['machineOS'] : "-";
            $modalNo = ($value['machineModelNum'] != '') ? $value['machineModelNum'] : "-";
            $installDate = ($value['installationDate'] != '') ? date("m/d/Y h:i", $value['installationDate']) : "-";
            $conctractEndData = ($value['uninstallDate'] != '') ? date("m/d/Y h:i", $value['uninstallDate']) : "-";

            if ($value['downloadStatus'] == 'EXE' && $value['revokeStatus'] == 'I') {
                $deviceStatus = 'Installed';
            } else if ($value['downloadStatus'] == 'EXE' && $value['revokeStatus'] == 'R') {
                $deviceStatus = 'Revoked';
            } else if (($value['downloadStatus'] == 'D' || $value['downloadStatus'] == 'G') && $value['revokeStatus'] == 'I') {
                $deviceStatus = 'Not Installed';
            } else {
                $deviceStatus = '-';
            }

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, $result['coustomerFirstName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, $orderNum);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, $serviceTag);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, $os);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, $modalNo);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, $installDate);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, $conctractEndData);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, $deviceStatus);
        }
    }
    return $objPHPExcel;
}

function CUSAJX_CreateAviraDeviceExcelSheet($key, $conn, $result, $objPHPExcel)
{
    $compId = $result['compId'];
    $processId = $result['processId'];
    $customerNum = $result['customerNum'];
    $orderNum = $result['orderNum'];

    $deviceRes = RSLR_GetCustomerDevicesGrid_PDO($key, $conn, $compId, $processId, $customerNum, $orderNum, "");

    $objPHPExcel->setActiveSheetIndex(1);
    $highestRow = $objPHPExcel->setActiveSheetIndex(1)->getHighestRow();

    if (is_array($deviceRes) && safe_count($deviceRes) > 0) {
        foreach ($deviceRes as $key => $value) {
            $highestRow++;
            $serviceTag = ($value['serviceTag'] != '') ? $value['serviceTag'] : "-";
            $os = ($value['machineOS'] != '') ? $value['machineOS'] : "-";
            $modalNo = ($value['machineModelNum'] != '') ? $value['machineModelNum'] : "-";
            $NHInstall = ($value['installationDate'] != '') ? date("l, F d, Y h:i:s", $value['installationDate']) : "-";
            $NHUninstall = ($value['uninstallDate'] != '') ? date("l, F d, Y h:i:s", $value['uninstallDate']) : "-";

            if ($value['aviraId'] != '') {
                $deviceStatus = strip_tags(CUSAJX_GetAviraStatus($value['aviraId']));
                $aviraDetails = RSLR_GetAviraDetails($key, $conn, $value['sid']);
                $aviraInstall = $aviraDetails[0]['productDate'];
                $aviraUninstall = $aviraDetails[0]['licenseExpiration'];
                $productName = $aviraDetails[0]['productName'];
            } else {
                $deviceStatus = 'Not Installed';
                $aviraInstall = '-';
                $aviraUninstall = '-';
            }

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $highestRow, $result['coustomerFirstName']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $highestRow, $serviceTag);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $highestRow, $os);
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $highestRow, $modalNo);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $highestRow, $aviraInstall);
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $highestRow, $aviraUninstall);
            $objPHPExcel->getActiveSheet()->setCellValue('G' . $highestRow, $NHInstall);
            $objPHPExcel->getActiveSheet()->setCellValue('H' . $highestRow, $NHUninstall);
            $objPHPExcel->getActiveSheet()->setCellValue('I' . $highestRow, $deviceStatus);
            $objPHPExcel->getActiveSheet()->setCellValue('J' . $highestRow, $productName);
        }
    }
    return $objPHPExcel;
}

function CUSAJX_ExportCustomerData()
{
    $key = '';
    $conn = NanoDB::connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $customerType = $_SESSION['user']['customerType'];
    $custRes = [];

    switch ($customerType) {
        case 0:
            $custRes = RSLR_GetAllCustomers($key, $conn, $loggedEid);
            break;
        case 1:
            $custRes = RSLR_GetEntityCustomers($key, $conn, $loggedEid);
            break;
        case 2:
            $custRes = RSLR_GetResellerCustomers($key, $conn, $loggedEid);
            break;
        case 5:
            $custRes = RSLR_GetCustomer($key, $conn, $loggedEid);
            break;
        default:
            break;
    }

    if ($_SESSION["user"]["Avira_Inst"] == 1) {
        $fn = "Avira_Customer_Details.xls";
        $objPHPExcel = CUSAJX_Avira_GetExcelObject($custRes, $conn);
    } else {
        $headerArray = array("A" => "Customer Name", "B" => "Number of devices");
        $objPHPExcel = CUSAJX_GetExcelSheetObject($headerArray, 30);
        $fn = "Customer_Details.xls";
        $objPHPExcel = CUSAJX_CreateCustomerExcelSheet($objPHPExcel, $custRes, $conn);
    }

    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function CUSAJX_ExportPTSCustomerData()
{
    $key = '';
    $conn = NanoDB::connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $customerType = $_SESSION['user']['customerType'];
    $custRes = [];

    switch ($customerType) {
        case 0:
            $custRes = RSLR_GetAllCustomers($key, $conn, $loggedEid);
            break;
        case 1:
            $custRes = RSLR_GetEntityCustomers($key, $conn, $loggedEid);
            break;
        case 2:
            $custRes = RSLR_GetResellerCustomers($key, $conn, $loggedEid);
            break;
        case 5:
            $custRes = RSLR_GetCustomer($key, $conn, $loggedEid);
            break;
        default:
            break;
    }

    $headerArray = array("A" => "Customer Name", "B" => "Number of devices");
    $objPHPExcel = CUSAJX_GetExcelSheetObject($headerArray, 30);
    if (safe_count($custRes) != 0) {
        $objPHPExcel = CUSAJX_CreateCustomerExcelSheet($objPHPExcel, $custRes, $conn);
    }

    $fn = "Customer Details.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function CUSTAJX_GetRenewDevices()
{
    $key = '';
    $conn = db_connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $ctype = $_SESSION["user"]["customerType"];
    $customerNum = url::issetInRequest('customerNum') ? url::requestToText('customerNum') : '';
    $orderNum = url::issetInRequest('orderNum') ? url::requestToText('orderNum') : '';
    $compId = url::issetInRequest('compId') ? url::requestToText('compId') : '';
    $prcId = url::issetInRequest('prcId') ? url::requestToText('prcId') : '';
    $renewDevices = [];

    if ($ctype == 1) {
        $renewDevices = RSLR_GetEntity_RenewDevices($key, $conn, $loggedEid);
    } else if ($ctype == 2) {
        $renewDevices = RSLR_GetReseller_RenewDevices($key, $conn, $loggedEid, $customerNum, $orderNum, $compId, $prcId);
    } else if ($ctype == 5) {
        $renewDevices = RSLR_GetRenewDevices($key, $conn, $loggedEid, $compId, $prcId);
    }

    $recordList = [];
    if (is_array($renewDevices) && safe_count($renewDevices) > 0) {
        foreach ($renewDevices as $key => $value) {
            $rowcheck = '<div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="check renew_check"name="checkNoc" id="' . htmlentities($value['sid']) . '" value="' . htmlentities($value['sid']) . '" >
                                        <span class="checkbox-material">
                                            <span class="check">

                                            </span>
                                        </span>
                                    </label>
                                </div>
                              </div>';

            $recordList[] = array(
                $rowcheck, '<p class="ellipsis" title="' . htmlentities($value['companyName']) . '">' . htmlentities($value['companyName']) . '</p>',
                htmlentities($value['orderNum']), '<p class="ellipsis" title="' . htmlentities($value['serviceTag']) . '">' . htmlentities($value['serviceTag']) . '</p>',
                htmlentities(date("m/d/Y h:i A", $value['installationDate'])),
                htmlentities(date("m/d/Y h:i A", $value['uninstallDate'])),
            );
        }
    }
    print_json_data($recordList);
}

function CUSAJX_RenewSelectedLicense()
{
    $key = '';
    $conn = NanoDB::connect();
    $provResult = 0;
    $selNHLic = url::requestToAny('selNHLic');
    $selPcCnt = url::requestToAny('selPcCnt');
    $custNum = url::requestToAny('custNum');
    $ordNum = url::requestToAny('ordNum');
    $cId = url::requestToAny('cId');
    $pId = url::requestToAny('pId');
    $loggedCtype = $_SESSION["user"]["customerType"];
    if ($loggedCtype == '2' || $loggedCtype == 2) {
        $dt = time();
        $loggedEid = $_SESSION["user"]["cId"];
        $remainLinsencesRes = RSLR_GetAllRemainingLicenses($key, $conn, $loggedEid, $selNHLic);
        $licenseCnt = $remainLinsencesRes['licenseCnt'];
        $instLicCnt = $remainLinsencesRes['installCnt'];
        $pendingCnt = $licenseCnt - $instLicCnt;
        $selecDevices = url::requestToAny('selecDevices');
        $selecDevicesArr = explode(',', $selecDevices);

        if (safe_count($selecDevicesArr) <= $pendingCnt) {

            $custNumDtls = RSLR_GetUniqueCustomerNums($key, $conn, $custNum, $ordNum, $cId, $pId);

            $orderDetails = RSLR_GetOrderDetailsGrid($key, $conn, $loggedEid, $selNHLic);
            $uniDate = $orderDetails[0]['contractEndDate'];
            $liceOrderNum = $orderDetails[0]['orderNum'];
            $skuNum = $orderDetails[0]['skuNum'];

            $custNum = $custNumDtls['customerNum'];
            $siteName = $custNumDtls['siteName'];
            $oldOrdNum = $custNumDtls['orderNum'];
            $pId = $custNumDtls['processId'];
            $cId = $custNumDtls['compId'];
            $noOfPc = safe_count($selecDevicesArr);
            $newOrderNum = CUSTAJX_MakeNewProvision($custNum, $oldOrdNum, $siteName, $uniDate, $cId, $pId, $liceOrderNum, $skuNum, $noOfPc);

            if ($newOrderNum != 'Fail') {
                $provResult = CUSTAJX_MakeNewServiceTag($selecDevicesArr, $loggedEid, $uniDate, $custNum, $oldOrdNum, $newOrderNum, $liceOrderNum);
                print_json_data(array("status" => 1, "msg" => "$provResult devices renewed"));
            } else {
                print_json_data(array("status" => 0, "msg" => "Something went wrong"));
            }
        } else {
            print_json_data(array("status" => 0, "msg" => "You have selected more device than available licenses"));
        }
    } else {
        print_json_data(array("status" => 0, "msg" => "You don't have access to renew devices"));
    }
}

function CUSTAJX_MakeNewProvision($custNum, $oldOrdNum, $siteName, $uniDate, $cId, $pId, $liceOrderNum, $skuNum, $noOfPc)
{
    $conn = NanoDB::connect();
    $newOrderNum = CUST_AutoOrderNo_PDO($conn);
    $orderDate = time();

    $result = CUST_RenewProvision_PDO($custNum, $oldOrdNum, $newOrderNum, $siteName, $siteName, '', $skuNum, $cId, $siteName, '', $noOfPc, $orderDate, $uniDate, $liceOrderNum);

    if ($result) {
        return $newOrderNum;
    } else {
        return 'Fail';
    }
}

function CUSTAJX_MakeNewServiceTag($selecDevicesArr, $loggedEid, $uniDate, $custNum, $oldOrdNum, $cId, $pId, $newOrderNum, $licenseOrderNumber)
{
    $key = '';
    $conn = NanoDB::connect();
    $i = 0;

    foreach ($selecDevicesArr as $deviceSid) {

        $deviceDtls = RSLR_GetDeviceDtls_PDO($key, $conn, $deviceSid, $custNum, $oldOrdNum);
        $insertRes = RSLR_InsertServiceRequest($key, $conn, $deviceDtls, $newOrderNum, $uniDate, $cId, $pId, $custNum, $oldOrdNum);
        if ($insertRes) {
            $i++;
            if ($insertRes) {
                RSLR_UpdateLicenseCounts($key, $conn, $loggedEid, $licenseOrderNumber);
            }
        }
    }
    return $i;
}

function CUSTAJX_SkuList()
{
    $key = '';
    $conn = NanoDB::connect();
    $loggedEid = $_SESSION["user"]["cId"];
    $skutype = url::requestToText('skutype');
    $skiListArr = RSLR_GetSkuDetailsGrid($key, $conn, $loggedEid, $skutype);

    if (is_array($skiListArr) && safe_count($skiListArr) > 0) {
        $str = '<option >Select sku</option>';
        foreach ($skiListArr as $key => $value) {
            $str .= '<option value="' . $value['id'] . '" >' . $value['skuName'] . '</option>';
        }
    } else {
        $str = '<option >No SKUs Available</option>';
    }

    print_data($str);
}

function CUSTAJX_CreateNewCustomer()
{
    $key = '';
    $conn = NanoDB::connect();
    $result = CUST_VerifyAndCreateCustomer($key, $conn);
    print_json_data($result);
}

function CUSTAJX_CreateNewSubscription()
{
    $key = '';
    $conn = NanoDB::connect();
    $result = CUST_AddSubscription($key, $conn);
    print_json_data($result);
}

function CUSTAJX_RenewSubscription()
{
    $key = '';
    $conn = NanoDB::connect();
    $result = CUST_RenewSubscription($key, $conn);
    print_json_data($result);
}

function CUSTAJX_RevokeDevice()
{
    $key = '';
    $conn = NanoDB::connect();
    $selectedDevices = explode(',', url::requestToText('selecDevices'));

    foreach ($selectedDevices as $deviceArray) {
        $device = explode('---', $deviceArray);
        $cId = $device[0];
        $pId = $device[1];
        $customerNumber = $device[2];
        $orderNum = $device[3];
        $sid = $device[4];
        $downLoadUrl = CUST_RevokeOrder($key, $conn, $customerNumber, $orderNum, $cId, $pId, $sid);
        if ($downLoadUrl != '') {
            print_json_data(array("status" => 1, "link" => $downLoadUrl, "msg" => "Please click on Copy button to copy url"));
        } else {
            print_json_data(array("status" => 0, "link" => "", "msg" => "Revoke can not be done at this time"));
        }
    }
}

function CUSTAJX_RegenerateDevice()
{
    $key = '';
    $conn = NanoDB::connect();
    $deviceArray = url::requestToText('selecDevices');
    $device = explode('---', $deviceArray);
    $cId = $device[0];
    $pId = $device[1];
    $customerNumber = $device[2];
    $orderNum = $device[3];
    $sid = $device[4];
    $res = CUST_RegenerateOrder($key, $conn, $customerNumber, $orderNum, $cId, $pId, $sid);

    print_json_data($res);
}

function addsignUp()
{
    $key = '';
    $conn = NanoDB::connect();
    $fullname = url::requestToText('fullname');
    $lname = url::requestToText('lname');
    $companyname = url::requestToText('companyname');
    $emailid = url::requestToText('emailid');
    $planid = url::requestToText('planid');
    $_SESSION['user']['webplanid'] = $planid;
    $language = "en";

    $retVal = RSLR_AddSignupCustomer($key, $conn, $fullname, $lname, $companyname, $emailid, 'website', $language);
    print_json_data($retVal);
}

function addPaysignUp()
{
    $key = '';
    $conn = NanoDB::connect();
    $retVal = RSLR_AddPaySignupReseller($key, $conn, 'website');

    print_json_data($retVal);
}

function purchasesignUp()
{

    $key = '';
    $fullname = url::requestToText('fullname');
    $lname = url::requestToText('lname');
    $companyname = url::requestToText('companyname');
    $emailid = url::requestToText('emailid');
    $planid = url::requestToText('planid');

    $retVal = RSLR_payPurchase($key, $fullname, $lname, $companyname, $emailid, $planid);
    print_data($retVal);
}

function purchasePage()
{
    $fid = url::requestToText('checkrcode');
    $retVal = RSLR_getSignupDtl($fid);
    print_json_data($retVal);
}

function purchaseLoginPage()
{
    $fid = url::requestToText('checkrcode');
    $selSku = url::requestToText('selplan');
    $retVal = RSLR_getLoginDtl($fid, $selSku);
    print_json_data($retVal);
}

function purchaseLogin()
{

    $data = safe_json_decode(file_get_contents('php://input'), true);
    $username = strip_tags($data['userid']);
    $pwd = strip_tags($data['secretkey']);

    $retVal = RSLR_payLogin($username, $pwd);
    print_json_data($retVal);
}

function CUSTAJX_AddSignUpReseller()
{
    global $aviraEnabled;

    $key = '';
    $conn = NanoDB::connect();
    $fullname = url::requestToText('fullname');
    $lname = url::requestToText('lname');
    $companyname = url::requestToText('companyname');
    $emailid = url::requestToText('emailid');
    $language = url::requestToText('language');

    if ($aviraEnabled == 0) {
        $retVal = RSLR_AddSignupCustomer($key, $conn, $fullname, $lname, $companyname, $emailid, 'dashboard', $language);
    } else if ($aviraEnabled == 1) {
        $retVal = RSLR_AddSignupReseller($key, $conn, $fullname, $lname, $companyname, $emailid, 'dashboard', $language);
    }
    print_json_data($retVal);
}

function getCustDetails()
{

    $key = '';
    $conn = NanoDB::connect();
    $cid = url::requestToText('cid');
    $pid = url::requestToText('pid');
    $custNum = url::requestToText('custNum');
    $ordNum = url::requestToText('ordNum');

    $result = CUST_GetCustomerDetail($key, $conn, $cid, $pid, $custNum, $ordNum);
    print_json_data($result);
}

function login_sendresetPassLink()
{
    global $signupPassUrl;
    $db = NanoDB::connect();
    $user_email = url::requestToText('userid');

    $resetSql = $db->prepare("select userid,role_id,username,user_email,firstName from " . $GLOBALS['PREFIX'] . "core.Users where (user_email=? or user_phone_no = ?) limit 1");
    $resetSql->execute([$user_email, $user_email]);
    $resetRes = $resetSql->fetch(PDO::FETCH_ASSOC);

    if (is_array($resetRes) && safe_count($resetRes) > 0) {

        $passId = getUserPassId();
        $sql_change = $db->prepare("update " . $GLOBALS['PREFIX'] . "core.Users set userKey=? where (user_email=? or user_phone_no = ?)");
        $sql_change->execute([$passId, $user_email, $user_email]);
        $sql_change->rowCount();

        $mailStus = RSLR_PassSendMail($resetRes['firstName'], $user_email, $resetLink);

        if ($mailStus == 1) {
            $msg = 1;
            print_data($msg);
        } else {
            $msg = 0;
            print_data($msg);
        }
    } else {
        $msg = 2;
        print_data($msg);
    }
}

function getUserPassId()
{

    try {

        $character_set_array = array();
        $character_set_array[] = array('count' => 6, 'characters' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
        $character_set_array[] = array('count' => 2, 'characters' => '0123456789');
        $temp_array = array();
        foreach ($character_set_array as $character_set) {
            for ($i = 0; $i < $character_set['count']; $i++) {
                $temp_array[] = $character_set['characters'][rand(0, strlen($character_set['characters']) - 1)];
            }
        }
        shuffle($temp_array);
        $randomNo = implode('', $temp_array);
        return $randomNo;
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
    }
}

function isOrderExist()
{
    $key = '';
    $conn = NanoDB::connect();
    $cid = url::requestToText('cid');
    $pid = url::requestToText('pid');
    $custNum = url::requestToText('custNum');
    $ordNum = url::requestToText('ordNum');
    $isExist = CUST_IsOrderExist($key, $conn, $cid, $pid, $custNum, $ordNum);
    print_data($isExist);
}

function CUSTAJX_GetOrders()
{
    $key = '';
    $conn = NanoDB::connect();
    $cid = url::requestToText('cid');
    $pid = url::requestToText('pid');
    $custNumber = url::requestToText('custNumber');
    $result = CUST_GetAllOrders($key, $conn, $cid, $pid, $custNumber);
    $options = CUSTAJX_PrepareOptionsList('orderNum', 'orderNum', $result, 'Orders');
    print_json_data(array("list" => $options, "firstOrderDtls" => $custNumber . '---' . $result[0]['orderNum']));
}

function CUSTAJX_PrepareOptionsList($keyindex, $lableindex, $resultArray, $defaultLable)
{
    $str = '';

    if (is_array($resultArray) && safe_count($resultArray) > 0) {
        foreach ($resultArray as $key => $value) {
            $str .= '<option value="' . $value[$keyindex] . '" >' . $value[$lableindex] . '</option>';
        }
    } else {
        $str = '<option value="">No ' . $defaultLable . ' Available</option>';
    }

    return $str;
}
