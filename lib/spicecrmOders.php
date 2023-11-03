<?php



include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once 'l-custAjax.php';


$method = $_SERVER['REQUEST_METHOD'];
$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
$input = safe_json_decode(file_get_contents('php://input'), true);

$fun = $input['fun'];

if ($fun == "saveOrder") {
    $res = insOrderDetails($input);
    return $res;
}
if ($fun == "verifyOrder") {
    $count = verifyDuplicateOrderNum($input['orderId']);
    return $count;
}

if ($fun == "createOrder") {
    $count = createOrderAndSignUp($input);
    return $count;
}
function insOrderDetails($data)
{
    $pdo = pdo_connect();

    if ($data['sku_name_c'] == 'trial') {
        $sqlsku = $pdo->prepare("select skuRef from ".$GLOBALS['PREFIX']."agent.skuMaster where skuName = 'Trial Plan'");
        $sqlsku->execute();
        $numDays = 30;
        $trial = '1';
        $sku_name_c = "Tria sku";
    } else {
        $sqlsku = $pdo->prepare("select skuRef from ".$GLOBALS['PREFIX']."agent.skuMaster where skuName = 'Annual Plan'");
        $sqlsku->execute();
        $numDays = 367;
        $trial = '0';
        $sku_name_c = "Annual Plan";
    }
    $skures = $sqlsku->fetch();
    $skuNum = $skures['skuRef'];

    $trialStartDate = strtotime($data['order_start_date_c']);
    $trialEndDate = strtotime($data['order_end_date_c']);

    $sql = $pdo->prepare("insert into ".$GLOBALS['PREFIX']."agent.orderDetails(`chnl_id`,`orderNum`,`skuNum`,`skuDesc`,`licenseCnt`,`installCnt`,`purchaseDate`,`orderDate`,`contractEndDate`,`noofDays`,`payRefNum`,`transRefNum`,`trial`,`nh_lic`,`amount`,`aviraOtc`,`status`,`crmOrderId`) values"
        . "('" . $data['channelid'] . "','" . $data['orderId'] . "',?,?,'" . $data['quantity_c'] . "',' ',?,?,?,?,' ',' ','1','1','" . $data['total_amount_c'] . "',' ','1','" . $data['crmOderId'] . "')");
    $sql->execute([$skuNum, $sku_name_c, $trialStartDate, $trialStartDate, $trialEndDate, $numDays]);
    $res = $pdo->lastInsertId();

    if ($res) {
        echo 'success';
    } else {
        echo "failed to insert";
    }
}


function verifyDuplicateOrderNum($orderNum)
{
    $pdo = pdo_connect();
    $sql = $pdo->prepare("select orderNum from ".$GLOBALS['PREFIX']."agent.orderDetails where orderNum = '$orderNum'");
    $sql->execute();
    $res = $sql->fetchAll();
    $count = safe_count($res);
    return $count;
}

function createOrderAndSignUp($input)
{

    $key = '';
    $conn = db_connect();
    $fullname = $input['fullname'];
    $lname = $input['lname'];
    $companyname = $input['companyname'];
    $emailid = $input['emailid'];
    $returnid = $input['returnid'];
    $planid = $input['planid'];
    $_SESSION['user']['webplanid'] = $planid;
    $_SESSION['user']['contactId'] = $returnid;
    $language = "en";     $retVal = RSLR_AddSignupCustomer($key, $conn, $fullname, $lname, $companyname, $emailid, 'website', $language);
    print_json_data($retVal);

}

?>