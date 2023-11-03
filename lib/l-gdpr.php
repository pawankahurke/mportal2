<?php




include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
include_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';



//Replace $routes['post'] with if else
if (url::postToText('function') === 'get_UserData') { // roles: user 
    get_UserData(); // roles: user 
}

//Replace $routes['get'] with if else
if (url::postToText('function') === 'download_ClientData') { // roles: user 
    download_ClientData(); // roles: user 
}




function get_UserData()
{
    nhRole::dieIfnoRoles(['user']); // roles: user 
    $type = url::requestToText('type');
    $typeVal = url::requestToText('typeVal');

    switch ($type) {
        case 'user':
            $data = getData_User($typeVal);
            break;
        case 'device':
            $data = getData_Device($typeVal);
            break;
        default:
            $data = 'Type not defined! Please try again.';
            break;
    }
    return $data;
}

function getData_User($username)
{
    nhRole::dieIfnoRoles(['user', 'deleteuser']); // roles: user, deleteuser
    try {
        $db = pdo_connect();
        $sql_user = $db->prepare("select user_email, firstName, lastName from " . $GLOBALS['PREFIX'] . "core.Users where username = ?");
        $sql_user->execute([$username]);
        $res_user = $sql_user->fetchAll();
        if (safe_count($res_user) > 0) {
            echo 'success';
        } else {
            echo 'norecord';
        }
    } catch (Exception $exc) {
        logs::log(__FILE__, __LINE__, $exc, 0);
        echo "Error : " . $exc->getTraceAsString();
    }
}

function delete_UserData($data)
{
    nhRole::dieIfnoRoles(['user', 'deleteuser']); // roles: user, deleteuser
    if (!isset($data['username']) || empty($data['username'])) {
        exit(json_encode(['success' => false, 'message' => 'The username is required']));
    }

    $pdo = NanoDB::connect();
    $pdo->beginTransaction();
    $totalOpt = 2;
    $optCount = 0;

    try {

        $sql = $pdo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "core.Users where username=?");
        if ($sql->execute([$data['username']])) $optCount++;

        $sql = $pdo->prepare("DELETE FROM " . $GLOBALS['PREFIX'] . "core.Customers where username=?");
        if ($sql->execute([$data['username']])) $optCount++;
    } catch (Exception $e) {
        logs::log(__FILE__, __LINE__, $e, 0);
        $pdo->rollback();
        exit(json_encode(['success' => false, 'message' => 'Error : Something went wrong']));
    }

    if ($optCount == $totalOpt) {
        $pdo->commit();
    } else {
        $pdo->rollback();
    }

    echo (json_encode(['success' => true, 'message' => 'Successfully deleted data']));
}

function download_ClientData($data)
{
    if (isset($data['type'])) {
        if ($data['type'] == 'user' || $data['type'] == 'device') {
            $data = callES($data['type'], $data['type_value'], 'get');
            $data = $data['hits']['hits'];
            $data = array_column($data, "_source");
            return exportClientData($data, "gdpr_client");
        }
    }
}

function callES($type, $value, $action, $index = '*')
{
    global $elastic_username;
    global $elastic_password;
    global $elastic_url;

    $targetIndex = ($index == '*') ? 'assets*,events*' : $index;

    if ($action == 'get') {
        $url = $elastic_url . $targetIndex . '/_search?size=10000';
    } else if ($action == 'delete') {
        $url = $elastic_url . $targetIndex . '/_delete_by_query';
    }

    if (isset($url)) {
        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Authorization: Basic " .  base64_encode($elastic_username . ":" . $elastic_password);
        $key = ('user' == $type) ? 'user' : 'machine';

        $query = '{
                "query" : {
                        "bool" : {
                                "must" : {
                                        "match" : {
                                                "' . $key . '.keyword" : "' . $value . '"
                                        }
                                }
                        }
                }
        }';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        $errorno = curl_errno($ch);

        $response = safe_json_decode($result, true);

        return $response;
    }

    return false;
}

function exportClientData($data, $name)
{
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $name . '.json');
    echo json_encode($data);
}

function delete_UserClientData($data)
{
    nhRole::dieIfnoRoles(['user', 'deleteuser']); // roles: user, deleteuser
    if (!isset($data['username']) || empty($data['username'])) {
        exit(json_encode(['success' => false, 'message' => 'The username is required']));
    }

    $response = callES("user", $data['username'], 'delete');

    echo (json_encode(['success' => true, 'message' => 'Successfully deleted data']));
}

function delete_MachineClientData($data)
{
    nhRole::dieIfnoRoles(['user', 'deleteuser']); // roles: user, deleteuser
    if (!isset($data['devicename']) || empty($data['devicename'])) {
        exit(json_encode(['success' => false, 'message' => 'The device name is required']));
    }

    $response = callES("machine", $data['devicename'], 'delete');

    echo (json_encode(['success' => true, 'message' => 'Successfully deleted data']));
}
