<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../include/common_functions.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-setTimeZone.php';

nhRole::dieIfnoRoles(['role']); // roles: role

//Replace $routes['post'] with if else
if (url::postToText('function') === 'ROLEroleValueStored') { // roles: role
    ROLEroleValueStored();
} else if (url::postToText('function') === 'fetch_EditRoleData') { // roles: role, editrole, deleterole
    nhRole::dieIfnoRoles(['role', 'deleterole', 'editrole']); // roles: role, editrole, deleterole
    fetch_EditRoleData();
}

//Replace $routes['get'] with if else
if (url::postToText('function') === 'get_RoleList') { // roles: role
    get_RoleList();
} else if (url::postToText('function') === 'ROLEgetRoleValueData') { // roles: role
    ROLEgetRoleValueData();
} else if (url::postToText('function') === 'get_Userrole') { // roles: role
    get_Userrole();
} else if (url::postToText('function') === 'deleteRoleValue') { // roles: role, deleterole
    nhRole::dieIfnoRoles(['role', 'deleterole']); // roles: role, deleterole
    delete_RoleValue();
}



function get_RoleList()
{
    $db = pdo_connect();
    $query = "SELECT
                        r.assignedRole,r.displayName,r.global,o.editable,o.modified
                  FROM (
                            SELECT * FROM " . $GLOBALS['PREFIX'] . "core.RoleMapping where username=? UNION SELECT * FROM " . $GLOBALS['PREFIX'] . "core.RoleMapping where global='1'
                        ) r
                  LEFT JOIN " . $GLOBALS['PREFIX'] . "core.Options o ON (r.assignedRole=o.id)";
    $sql = $db->prepare($query);
    $sql->execute([$_SESSION['userloginfo']['email']]);
    $sqlRes = $sql->fetchAll();
    $recordList = array();
    if (safe_count($sqlRes) > 0) {
        foreach ($sqlRes as $key => $value) {
            $id = $value['assignedRole'];
            $modified = $value['modified'];
            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $logintime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $modified);
            } else {
                $logintime = date('m/d/Y H:i:s', $modified);
            }
            $isEditable = (isset($value['editable']) && is_numeric($value['editable']) && intval($value['editable']) == 1) ? 'true' : 'false';
            $displayName   = '<p class="ellipsis" data-is-editable="' . $isEditable . '" title="' . $value['displayName'] . '" id="' . $id . '">' . $value['displayName'] . '</p>';
            $recordList[]  = array($displayName, $logintime, $id);
        }
    }
    $auditRes = create_auditLog('Role Management', 'View', 'Success');
    echo json_encode($recordList);
}
function ROLEgetRoleValueData()
{
    /*   $res = checkModulePrivilege('addrole', 2);
        if(!$res) exit(json_encode(['message' => 'Permission denied for this user role' , 'error' => true])); */
    $db = pdo_connect();
    try {
        $query = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.RoleValue where moduleName != 'Insight'");
        $query->execute();
        $result = $query->fetchAll();
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        exit(json_encode(['message' => 'Something went wrong', 'error' => true]));
    }
    echo json_encode($result);
}
function ROLEroleValueStored()
{
    $rolesValue = url::postToAny('jsonRolesData');
    $formId     = url::postToText('formId');
    $isGlobal = (url::issetInPost('global') && url::isNumericInPost('global') && url::postToInt('global') == 1) ? '1' : '0';
    $db = pdo_connect();
    $time = time();
    $uname = $_SESSION['userloginfo']['email'];
    $roleStr = '';
    $globalRoleData = 0;
    if ($formId == 'rolesdataform') {
        foreach ($rolesValue as $key => $value) {
            if ($value['name'] == 'roleName') {
                $rolename = $value['value'];
            }
        }

        $query = $db->prepare("select name from " . $GLOBALS['PREFIX'] . "core.Options where name =?");
        $query->execute([$rolename]);
        $resQury = $query->fetchAll();
        if (isset($resQury) && safe_sizeof($resQury) > 0 && isset($resQury[0]['editable']) && is_numeric($resQury[0]['editable']) && intval($resQury[0]['editable']) == 0) {
            echo "un-editable";
            return;
        }
        if (safe_count($resQury)) {
            echo "exist";
        } else {
            //  $roleStr = "{";
            foreach ($rolesValue as $key => $value) {
                /*                 if ($value['name'] === 'csrfMagicToken')
                    continue;  */
                if ($value['name'] == 'roleName') {
                    $roleName = $value['value'];
                } else if ($value['name'] == 'globalRole') {
                    $globalRoleData = 1;
                } else {
                    $name = trim($value['name']);
                    $val = trim($value['value']);
                    //    $roleStr .= '"'.$name.'": '.$val.',';
                }
                $resArray[$name] = $val;
            }
            $roleStr = json_encode($resArray);
            // $roleStr = trim($roleStr, ',');
            //  $roleStr .= "}";
            $query = $db->prepare("select name from " . $GLOBALS['PREFIX'] . "core.Options where name =?");
            $query->execute([$roleName]);
            $resQury = $query->fetchAll();
            if (safe_count($resQury)) {
                echo "exist";
            } else {
                $sql = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.Options (name,value,modified,type,editable) VALUES(?,?,?,?,?)");
                $sql->execute([$roleName, $roleStr, $time, 10, 1]);
                $sqlRes = $db->lastInsertId();
                $id = $db->lastInsertId();
                // $sql1 = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.OptionsPerm (name,value,modified,type,editable) VALUES(?,?,?,?,?)");
                // $sql1->execute([$roleName, $roleStr, $time, 10, 1]);
                // $sqlRes = $db->lastInsertId();
                $sql2 = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "core.RoleMapping (assignedRole,displayName,global,statusVal,username) VALUES(?,?,?,?,?)");
                $sql2->execute([$id, $roleName, $isGlobal, 1, $uname]);
                $sqlRes2 = $db->lastInsertId();
                if ($sqlRes) {
                    if (isset($_SESSION["user"]["sso"])) {
                        if ($_SESSION["user"]["sso"]) {
                            sync_to_azureAd($db, 0);
                        }
                    }
                    $auditRes = create_auditLog('Role Management', 'Create', 'Success', $rolesValue);
                    echo "success";
                } else {
                    $auditRes = create_auditLog('Role Management', 'Create', 'Failed', $rolesValue);
                    echo "failed";
                }
            }
        }
    } else {
        foreach ($rolesValue as $key => $value) {
            if ($value['name'] == 'editRoleName') {
                $reg = '/[^a-zA-Z0-9\s_-]+/im';
                $roleName = preg_replace($reg, '', $value['value']);
            } else if ($value['name'] == 'editglobalRole') {
                $globalRoleData = 1;
            } else if ($value['name'] == 'roleId') {
                $id = $value['value'];
            } else {
                $name = trim($value['name']);
                $val = trim($value['value']);
                $roleStr .= '"' . $name . '": ' . $val . ',';
            }
            $resArray[$name] = $val;
        }
        $roleStr = json_encode($resArray);
        $q = $db->prepare("SELECT o.*,r.global FROM " . $GLOBALS['PREFIX'] . "core.Options o LEFT JOIN " . $GLOBALS['PREFIX'] . "core.RoleMapping r ON(o.id=r.assignedRole) WHERE o.id=?");
        $q->execute([$id]);
        $roleData = $q->fetch();
        if (isset($roleData) && isset($roleData['editable']) && is_numeric($roleData['editable']) && intval($roleData['editable']) == 0) {
            echo "un-editable";
            return;
        }
        if ($roleData && isset($roleData['global']) && is_numeric($roleData['global'])) {
            if (is_numeric($isGlobal) && intval($roleData['global']) != intval($isGlobal)) {
                $q = $db->prepare("SELECT count(role_id) as assigned_role_count FROM " . $GLOBALS['PREFIX'] . "core.Users where role_id=?");
                $q->execute([$id]);
                $countData = $q->fetch();
                if (isset($countData['assigned_role_count']) && intval($countData['assigned_role_count']) > 0) {
                    exit('no-global');
                }
            }
        }
        $query = $db->prepare("select name from " . $GLOBALS['PREFIX'] . "core.Options where name=? and id!=?");
        $query->execute([$roleName, $id]);
        $existingRoleData = $query->fetch(PDO::FETCH_ASSOC);
        if ($existingRoleData) {
            exit('exist');
        }
        $requesterRoleId = isset($_SESSION['user']['role_id']) ? $_SESSION['user']['role_id'] : null;
        if (!is_null($requesterRoleId) && is_numeric($requesterRoleId)) {
            $q = $db->prepare("SELECT o.*,r.global,r.username FROM " . $GLOBALS['PREFIX'] . "core.Options o LEFT JOIN " . $GLOBALS['PREFIX'] . "core.RoleMapping r ON(o.id=r.assignedRole) WHERE o.id=?");
            $q->execute([$requesterRoleId]);
            $requesterRoleData = $q->fetch();
            if ($requesterRoleData['username'] != $uname) {
                if (isset($id) && is_numeric($id) && intval($id) == intval($requesterRoleId)) {
                    exit('#33758');
                }
            }
        }
        $updateQuery = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.Options SET  value=?,modified=? where id = ?");
        $updateQuery->execute([$roleStr, $time, $id]);
        $updateProSqlRes = $updateQuery->rowCount();
        // $updateQuery1 = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.OptionsPerm SET  value=?,modified=? where id = ?");
        // $updateQuery1->execute([$roleStr, $time, $id]);
        // $updateProSqlRes1 = $updateQuery1->rowCount();
        $updateQuery2 = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "core.RoleMapping SET global=?, displayName=? where assignedRole = ?");
        $updateQuery2->execute([$isGlobal, $roleName, $id]);
        $updateProSqlRes2 = $updateQuery2->rowCount();
        if ($updateProSqlRes) {
            if (isset($_SESSION["user"]["sso"])) {
                if ($_SESSION["user"]["sso"]) {
                    sync_to_azureAd($db, 0);
                }
            }
            $auditRes = create_auditLog('Role Management', 'Modification', 'Success', $rolesValue);
            echo "update";
        } else {
            $auditRes = create_auditLog('Role Management', 'Modification', 'Failed', $rolesValue);
            echo "failed";
        }
    }
}
function fetch_EditRoleData()
{
    $id = url::requestToText('id');
    $db = pdo_connect();
    $sql = $db->prepare("SELECT t1.id,t1.name,t1.value, t2.global,t1.editable FROM " . $GLOBALS['PREFIX'] . "core.Options t1 inner join " . $GLOBALS['PREFIX'] . "core.RoleMapping t2 on t1.id = t2.assignedRole AND t2.assignedRole = ? where t1.id = ?");
    $sql->execute([$id, $id]);
    $sqlRes = $sql->fetchAll();
    $recordList = array();
    if (safe_count($sqlRes) > 0) {
        foreach ($sqlRes as $key => $value) {
            $id = $value['assignedRole'];
            $id        = $value['id'];
            $name      = $value['name'];
            $global    = $value['global'];
            $jsonValue = $value['value'];
            $recordList  = array('id' => $id, 'name' => $name, 'global' => $global, 'jsondata' => $jsonValue, 'editable' => $value['editable']);
        }
    }
    echo json_encode($recordList);
}
function delete_RoleValue()
{
    /*   $res = checkModulePrivilege('deleterole', 2);
        if(!$res) exit('Permission denied'); */
    $id = url::requestToText('id');
    $db = pdo_connect();
    $query = "SELECT
                        r.username,o.editable
                  FROM
                        " . $GLOBALS['PREFIX'] . "core.RoleMapping r
                  LEFT JOIN
                        " . $GLOBALS['PREFIX'] . "core.Options o ON (r.assignedRole=o.id)
                  WHERE
                        r.assignedRole=?";
    $sql = $db->prepare($query);
    $sql->execute([$id]);
    $res = $sql->fetch();
    if (isset($res['editable']) && is_numeric($res['editable']) && intval($res['editable']) == 0) {
        exit('Cannot delete this role');
    }
    $roleActualUser = $res['username'];
    $loginUser = $_SESSION['userloginfo']['email'];
    $sql = $db->prepare("select userid from " . $GLOBALS['PREFIX'] . "core.Users where role_id = ? and userStatus=1");
    $sql->execute([$id]);
    $check = $sql->fetch();
    if ($roleActualUser != $loginUser) {
        echo 'Only role owner can delete this role';
        return;
    }
    if ($check) {
        echo "This role has already been assigned to users.<br />Cannot delete role";
        return;
    }
    if ($roleActualUser == $loginUser && !$check) {
        $deleteSql = $db->prepare("delete from " . $GLOBALS['PREFIX'] . "core.Options where id =?");
        $deleteSql->execute([$id]);
        $deleteSqlres = $deleteSql->rowCount();
        // $deleteSql1 = $db->prepare("delete from " . $GLOBALS['PREFIX'] . "core.OptionsPerm where id =?");
        // $deleteSql1->execute([$id]);
        // $deleteSqlres = $deleteSql1->rowCount();
        $deleteSql2 = $db->prepare("delete from " . $GLOBALS['PREFIX'] . "core.RoleMapping where assignedRole =?");
        $deleteSql2->execute([$id]);
        $deleteSqlres = $deleteSql2->rowCount();
        if ($deleteSqlres) {
            if (isset($_SESSION["user"]["sso"])) {
                if ($_SESSION["user"]["sso"]) {
                    sync_to_azureAd($db, $id);
                }
            }
            $auditRes = create_auditLog('Role Management', 'Delete', 'Success');
            echo "success";
        } else {
            $auditRes = create_auditLog('Role Management', 'Delete', 'Failed');
            echo "failed";
        }
    }
}

function get_Userrole()
{
    echo nhRole::currentRoleName();
}

function sync_to_azureAd($db, $deleteId)
{
    $userId = $_SESSION['user']['userid'];
    $pdo = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Users where userid='" . $userId . "'");
    $pdo->execute();
    $userdet = $pdo->fetch(PDO::FETCH_ASSOC);
    $getTokenDet =     decode_JWT($userdet['access_token']);
    $appId = $getTokenDet['claims']['appid'];
    $getData = http_build_query(array('$filter' => 'appId eq \'' . $appId . '\''));
    $url = 'https://graph.microsoft.com/v1.0/applications?' . $getData;
    $options = array(
        'http' => array(
            'header'  => 'Authorization: Bearer ' . $userdet['access_token'],
            'method'  => 'GET',
        ),
    );
    $context  = stream_context_create($options);
    $server_output = file_get_contents($url, false, $context);
    $server_output = safe_json_decode($server_output);
    $appobjectId = $server_output->value[0]->id;
    $getRoleUrl = 'https://graph.microsoft.com/v1.0/applications/' . $appobjectId;
    $options3 = array(
        'http' => array(
            'header'  => array("Authorization:Bearer " . $userdet['access_token'], "Content-type: application/json"),
            'method'  => 'GET'
        ),
    );
    $context3  = stream_context_create($options3);
    $getData_output = file_get_contents($getRoleUrl, false, $context3);
    $approledet = safe_json_decode($getData_output, true);
    $appRoles = $approledet['appRoles'];
    $disableAppRole = array();
    $disableAppRole['appRoles'] = array();
    foreach ($appRoles as $approle) {
        $approle['isEnabled'] = false;
        array_push($disableAppRole['appRoles'], $approle);
    }
    $patchUrl3 = 'https://graph.microsoft.com/v1.0/applications/' . $appobjectId;
    $options4 = array(
        'http' => array(
            'header'  => array("Authorization:Bearer " . $userdet['access_token'], "Content-type: application/json"),
            'method'  => 'PATCH',
            'content' => json_encode($disableAppRole)
        ),
    );
    $context4  = stream_context_create($options4);
    $patch_output1 = file_get_contents($patchUrl3, false, $context4);
    $data = get_roleDetails($db, $deleteId);
    $patchUrl = 'https://graph.microsoft.com/v1.0/applications/' . $appobjectId;
    $options2 = array(
        'http' => array(
            'header'  => array("Authorization:Bearer " . $userdet['access_token'], "Content-type: application/json"),
            'method'  => 'PATCH',
            'content' => json_encode($data)
        ),
    );
    $context2  = stream_context_create($options2);
    $patch_output = file_get_contents($patchUrl, false, $context2);
}
function get_roleDetails($db, $deleteId)
{
    $pdo = $db->prepare('select displayName as name,assignedRole as id,azure_guid from ' . $GLOBALS['PREFIX'] . 'core.RoleMapping where statusVal = ?');
    $pdo->execute([1]);
    $roles = $pdo->fetchAll(PDO::FETCH_ASSOC);
    $roleDettemp = [];
    foreach ($roles as $role) {
        if (empty($role['azure_guid'])) {
            $randGuid = getGUID();
            $temp = array(
                "allowedMemberTypes" => ['User'],
                "description" => $role['name'],                      "displayName" => str_replace(" ", "", $role['name']),                      "id" => $randGuid,
                "isEnabled" => true,
                "value" =>  strval($role['id'])
            );
            $pdo = $db->prepare('update ' . $GLOBALS['PREFIX'] . 'core.RoleMapping set azure_guid = "' . $randGuid . '" where assignedRole = ? ');
            $pdo->execute([$role['id']]);
            array_push($roleDettemp, $temp);
        } else {
            if ($deleteId != 0 && $deleteId == $role['id']) {
                $temp = array(
                    "allowedMemberTypes" => ['User'],
                    "description" => $role['name'],                      "displayName" => str_replace(" ", "", $role['name']),                      "id" =>  $role['azure_guid'],
                    "isEnabled" => false,
                    "value" =>  strval($role['id'])
                );
                array_push($roleDettemp, $temp);
            } else {
                $temp = array(
                    "allowedMemberTypes" => ['User'],
                    "description" => $role['name'],                      "displayName" => str_replace(" ", "", $role['name']),                      "id" =>  $role['azure_guid'],
                    "isEnabled" => true,
                    "value" =>  strval($role['id'])
                );
                array_push($roleDettemp, $temp);
            }
        }
    }
    $rolesDet = [
        "appRoles" => $roleDettemp
    ];
    return $rolesDet;
}
function getGUID()
{
    if (function_exists('com_create_guid')) {
        return com_create_guid();
    } else {
        mt_srand((float)microtime() * 10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);
        $uuid =                 substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        return $uuid;
    }
}
function decode_JWT($access_token)
{
    if ($access_token) {
        $jwt = list($header, $claims, $signature) = explode('.', $access_token);
        $header = decodeFragment($header);
        $claims = decodeFragment($claims);
        $signature = (string) base64_decode($signature);
        return [
            'header' => $header,
            'claims' => $claims,
            'signature' => $signature
        ];
    }
    return false;
}
function decodeFragment($value)
{
    return (array) safe_json_decode(base64_decode($value));
}
