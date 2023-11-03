<?php



include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-util.php';
include_once '../lib/l-profilewizard.php';
include_once '../lib/l-customer.php';
include_once '../lib/l-reseller.php';
include_once '../lib/l-user.php';
include_once '../lib/l-sqlitedb.php';
include_once '../lib/l-crmdetls.php';
include_once '../lib/l-provElastic.php';
include_once '../lib/l-dashboardAPI.php';


nhRole::dieIfnoRoles(['agentworkspace']); // roles: agentworkspace

if (url::issetInRequest('function')) { // roles: agentworkspace
    $function = url::requestToText('function'); // roles: agentworkspace
    $apikey   = UTIL_GetString('apikey', "");
    $isValid  = DASH_ValidateKey($apikey);

    if ($isValid) {
        $result = $function();
        echo $result;
    } else {
        echo "Your key has been expired";
    }
}



function MSP_GetCustomerGrid()
{
    $conn = db_connect();
    $draw = UTIL_GetInteger('draw', 1);
    $recordList = [];
    $totalCount = 0;
    $loggedEid = $_SESSION["user"]["cId"];
    $ctype = $_SESSION["user"]["cd_ctype"];
    $eid = UTIL_GetString('id', '');
    $key = '';

    $result = MSP_GetMSPCustomers($key, $conn, $loggedEid);

    if (safe_count($result) > 0) {
        $totalCount = safe_count($result);
        $recordList = MSP_FormatCustomerGrid($result);
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $totalCount, "recordsFiltered" => $totalCount, "data" => $recordList);
    return json_encode($jsonData);
}


function MSP_GetMSPCustomers($key, $db, $loggedEid)
{
    $sql = "select C.eid,C.companyName,C.firstName,C.lastName,C.emailId,C.status, C.createdTime from " . $GLOBALS['PREFIX'] . "agent.channel C where C.entityId='$loggedEid' order by createdtime desc";
    $res = find_many($sql, $db);

    if (safe_count($res)) {
        return $res;
    } else {
        return array();
    }
}

function MSP_FormatCustomerGrid($resultArray)
{
    $array = [];
    foreach ($resultArray as $key => $value) {

        $companyName = explode('_', $value['companyName']);

        $customer = MSP_CreatPTag($companyName[0]);
        $firstName = MSP_CreatPTag($value['firstName']);
        $lastName = MSP_CreatPTag($value['lastName']);
        $email = MSP_CreatPTag($value['emailId']);
        $status = MSP_GetCustomerStatus($value['status']);
        $status = MSP_CreatPTag($status);

        $rowId = $value['status'] . '---' . $value['eid'];
        $status = $value['status'];
        $staVal = '';
        if ($status == 1 || $status == '1') {
            $staVal = '<p style="color:green;">Active</p>';
        } elseif ($status == 0 || $status == '0') {
            $staVal = '<p style="color:red;">InActive</p>';
        }

        $array[] = array(
            "DT_RowId" => $rowId,
            'customer' => utf8_encode($customer),
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'status' => utf8_encode($staVal)
        );
    }

    return $array;
}


function MSP_CreatPTag($ptag_val)
{
    if ($ptag_val == "" || $ptag_val == "NULL" || $ptag_val == NULL || $ptag_val == null) {
        $ptagStr = '<p class="ellipsis">-</p>';
    } else {
        $ptagStr = '<p class="ellipsis" title="' . $ptag_val . '">' . $ptag_val . '</p>';
    }
    return $ptagStr;
}


function MSP_GetCustomerStatus($status)
{
    if ($status == 1 || $status == '1') {
        $str = 'Enabled';
    } else {
        $str = 'Disabled';
    }
    return $str;
}
