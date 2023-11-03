<?php



include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-ptsReportExport.php';


function PTS_ManagerInstallReport($startDate, $toDate, $instalType, $pdo)
{

    if ($instalType == 1) {
        $skuSql = $pdo->prepare("SELECT DISTINCT(skuRef) FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE planId = '$instalType' ");
    } else if ($instalType == 2) {
        $skuSql = $pdo->prepare("SELECT DISTINCT(skuRef) FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE planId = '$instalType' ");
    } else if ($instalType == 3) {
        $skuSql = $pdo->prepare("SELECT DISTINCT(skuRef) FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE planId = '$instalType' ");
    } else {
        $skuSql = $pdo->prepare("SELECT DISTINCT(skuRef) FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE planId IN (1,2,3)");
    }
    $skuSql->execute();
    $skuRef = $skuSql->fetchAll();
    $sku = '';
    foreach ($skuRef as $key => $val) {
        $temp = $val['skuRef'];
        $sku .= "'" . $temp . "',";
    }
    $sku = rtrim($sku, ',');

    $exportDataSql = $pdo->prepare("SELECT C.customerNum customerNum, C.orderNum orderNum, C.coustomerFirstName coustomerFirstName,C.emailId emailId,C.coustomerLastName coustomerLastName," .
        "C.SKUNum SKUNum, C.SKUDesc SKUDesc, C.orderDate orderDate,C.payRefNum,C.refundAmt,C.cancelDate,C.createdDate," .
        "C.contractEndDate contractEndDate,C.refCustomerNum refCustomerNum,C.transactionDate transactionDate,C.transationType transationType," .
        "S.serviceTag serviceTag,S.installationDate installationDate,S.downloadStatus downloadStatus, " .
        "S.revokeStatus as revokeStatus,S.uninsdormatStatus as uninsdormatStatus,S.uninsdormatDate as uninsdormatDate ," .
        "S.machineManufacture machineManufacture,S.machineModelNum machineModelNum," .
        "S.clientVersion,S.oldVersion,S.machineOS from " . $GLOBALS['PREFIX'] . "agent.customerOrder as C left join  " . $GLOBALS['PREFIX'] . "agent.serviceRequest as S on " .
        "C.orderNum =  S.orderNum AND C.customerNum = S.customerNum WHERE C.processId=S.processId " .
        "and S.downloadStatus = 'EXE' and S.installationDate >= '$startDate' AND S.installationDate <= '$toDate' AND C.SKUNum IN($sku) group by S.sessionid");
    $exportDataSql->execute();
    $exportData = $exportDataSql->fetchAll();
    $excelData = PTS_InstalExcelCreation($exportData);
    return $excelData;
}

function PTS_ManagerUsageReport($chkBox, $radVal, $employeeId, $serialNum, $startDate, $endDate, $processId, $pdo)
{
    $where = '';
    if ($chkBox == 'agentTrigger' && $radVal == 'employeeId') {
        $where = 'U.agentId ="' . $employeeId . '"';
    } elseif ($chkBox == 'agentTrigger' && $radVal == 'systemSerialNum') {
        $where = 'U.customerno ="' . $serialNum . '" and U.agentId IS NOT NULL and U.agentId != "" and U.agentId != "0"';
    } elseif ($chkBox == 'agentTrigger' && $radVal == 'allAgent') {
        $where = "U.agentId IS NOT NULL and U.agentId != '' and U.agentId != '0'";
    } elseif ($chkBox == 'CustomerTrigger' && $radVal == 'allCustomer') {
        $where = "";
    } elseif ($chkBox == 'CustomerTrigger' && $radVal == 'systemSerialNum') {
        $where = 'U.customerno ="' . $serialNum . '" and';
    }

    if ($chkBox == 'agentTrigger') {
        $reportSql = $pdo->prepare("SELECT U.customerno,U.orderno,U.agentId,U.serviceTag,U.sitename,U.executiontime,U.dartno,U.Text1,U.clientversion,U.brandname,U.machineModelNum,S.firstName FROM  " . $GLOBALS['PREFIX'] . "event.usageReport U,core.Users S WHERE $where and U.agentId= S.user_email and dartno IN (256,286) AND " .
            "(executiontime >= $startDate or executiontime <= $endDate) AND pid='$processId' AND " .
            "text2='Type of run: On-Demand:Agent' order by executiontime asc");
    } elseif ($chkBox == 'CustomerTrigger') {
        $reportSql = $pdo->prepare("SELECT U.customerno,U.orderno,U.agentId,U.serviceTag,U.sitename,U.executiontime,U.dartno,U.Text1,U.clientversion,U.brandname,U.machineModelNum,S.firstName FROM  " . $GLOBALS['PREFIX'] . "event.usageReport U,core.Users S WHERE $where dartno IN (256,286) AND (agentId = '' OR agentId = '0' OR agentId IS NULL) " .
            "AND executiontime >= $startDate AND (executiontime <= $endDate or pid='$processId') AND " .
            "text2='Type of run: On-Demand:Consumer' order by executiontime asc");
    } else {
        $reportSql = $pdo->prepare("SELECT U.customerno,U.orderno,U.agentId,U.serviceTag,U.sitename,U.executiontime,U.dartno,U.Text1,U.clientversion,U.brandname,U.machineModelNum,S.firstName FROM  " . $GLOBALS['PREFIX'] . "event.usageReport U,core.Users S WHERE dartno IN (256,286) AND (executiontime >= $startDate " .
            "or executiontime <= $endDate) AND pid=$processId order by executiontime asc");
    }
    $reportSql->execute();
    $reportRes = $reportSql->fetchAll();
    $resultData = PTS_UsageExcelCreation($reportRes);
    return $resultData;
}

function PTS_ManagerSalesReport($customerType, $cId, $pId, $sDate, $eDate, $instalType, $pdo)
{

    $sql = "";
    $processId = '';

    if ($customerType == 0) {
        $sql = $pdo->prepare("SELECT p.pId FROM " . $GLOBALS['PREFIX'] . "agent.processMaster p join channel c on c.eid=p.cId WHERE c.ctype =5");
    } else if ($customerType == 1) {
        $sql = $pdo->prepare("SELECT p.pId FROM " . $GLOBALS['PREFIX'] . "agent.processMaster p join channel c on c.eid=p.cId WHERE c.ctype =5 and c.entityId='$cId'");
    } else if ($customerType == 2) {
        $sql = $pdo->prepare("SELECT p.pId FROM " . $GLOBALS['PREFIX'] . "agent.processMaster p join channel c on c.eid=p.cId WHERE c.ctype =5 and c.channelId='$cId'");
    } else if ($customerType == 5) {
        $processId = $pId;
    }

    if (!empty($sql)) {
        $sql->execute();
        $sqlRes = $sql->fetchAll();
        if (!empty($sqlRes)) {
            foreach ($sqlRes as $key => $val) {
                $id = $val['pId'];
                $processId .=  $id . ",";
            }
            $processId = rtrim($processId, ',');
        }
    }

    if ($instalType == 1) {
        $skuSql = $pdo->prepare("SELECT DISTINCT(skuRef) FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE planId = '$instalType' ");
    } else if ($instalType == 2) {
        $skuSql = $pdo->prepare("SELECT DISTINCT(skuRef) FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE planId = '$instalType' ");
    } else if ($instalType == 3) {
        $skuSql = $pdo->prepare("SELECT DISTINCT(skuRef) FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE planId = '$instalType' ");
    } else {
        $skuSql = $pdo->prepare("SELECT DISTINCT(skuRef) FROM " . $GLOBALS['PREFIX'] . "agent.skuMaster WHERE planId IN (1,2,3)");
    }
    $skuSql->execute();
    $skuRef = $skuSql->fetchAll();

    foreach ($skuRef as $key => $val) {
        $temp = $val['skuRef'];
        $sku .= "'" . $temp . "',";
    }
    $sku = rtrim($sku, ',');

    $salesSql = $pdo->prepare("SELECT c.customerNum,c.orderNum,c.coustomerFirstName,c.coustomerLastName,c.coustomerCountry," .
        "c.emailId,c.orderDate,c.contractEndDate,c.validity,c.noOfPc,c.payRefNum,c.transactionDate,c.SKUDesc,c.paymentStatus FROM " .
        "" . $GLOBALS['PREFIX'] . "agent.customerOrder as c WHERE c.processId IN($processId) AND (c.transactionDate >= '$sDate'  OR c.transactionDate <= '$eDate') AND SKUNum IN($sku)");
    $salesSql->execute();
    $salesRes = $salesSql->fetchAll();
    $salesResultData = PTS_SalesExcelCreation($salesRes);
    return $salesResultData;
}

function PTS_getDropdownList($cid, $pid)
{

    $pdo = pdo_connect();

    $fieldSql = $pdo->prepare("SELECT F.displayName,F.provCode FROM " . $GLOBALS['PREFIX'] . "agent.fieldValues F," . $GLOBALS['PREFIX'] . "agent.custSkuMaster C WHERE C.cId ='$cid' AND C.pId='$pid' " .
        "AND C.skuId=F.id AND F.fid=3 AND C.status=1 order by F.noOfDays");
    $fieldSql->execute();
    $fieldRes = $fieldSql->fetchAll();

    return $fieldRes;
}

function PTS_refundData($searchType, $searchVal, $processId, $pdo)
{
    if ($searchType == 'custNum') {
        $colName = 'customerNum';
    }
    if ($searchType == 'orderNum') {
        $colName = 'orderNum';
    }
    if ($searchType == 'emailId') {
        $colName = 'emailId';
    }

    $OrderSql = $pdo->prepare("SELECT customerNum,orderNum,SKUNum,SKUDesc,mappCCNum,payRefNum,refund,refundAmt,paidAmount," .
        "FROM_UNIXTIME(orderDate,'%Y-%m-%d') as orderDate,FROM_UNIXTIME(contractEndDate,'%Y-%m-%d') as contractEndDate,refund,recursive," .
        "paymentStatus from " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE " . $colName . " = '$searchVal' and processId = $processId ORDER BY id DESC");
    $OrderSql->execute();
    $orderRes = $OrderSql->fetchAll();

    if (safe_count($orderRes) > 0) {
        foreach ($orderRes as $key => $value) {
            $amount = ($value['paidAmount'] != '') ? '$' . $value['paidAmount'] : '-';

            $custNum       = '<p class="ellipsis" title="' . $value['customerNum'] . '">' . $value['customerNum'] . '</p>';
            $orderNum      = '<p class="ellipsis" title="' . $value['orderNum'] . '">' . $value['orderNum'] . '</p>';
            $skuNum        = '<p class="ellipsis" title="' . $value['SKUDesc'] . '">' . $value['SKUDesc'] . '</p>';
            $paidAmount    = '<p class="ellipsis" title="' . $amount . '">' . $amount . '</p>';
            $orderDate     = '<p class="ellipsis" title="' . $value['orderDate'] . '">' . $value['orderDate'] . '</p>';
            $endDate       = '<p class="ellipsis" title="' . $value['contractEndDate'] . '">' . $value['contractEndDate'] . '</p>';
            if ($value['payRefNum'] != '' && $value['refund'] == 0) {
                $refNum = $value['payRefNum'];
                $refund        = "<p class='ellipsis' id='" . $refNum . "' title='" . $refNum . "'> <a href='javascript:'"
                    . "onclick='refund(&quot;$refNum&quot;);' style='text-color:#ffedsw;color: #2695ca;text-decoration: underline;' >Refund</a></p>";
            } else {
                $refunAmt = ($value['refundAmt'] != '') ? '$' . $value['refundAmt'] : '-';
                $refund        = '<p class="ellipsis" title="' . $refunAmt . '">' . $refunAmt . '</p>';
            }

            $recordlist[] = array($custNum, $orderNum, $skuNum, $paidAmount, $orderDate, $endDate, $refund);
        }
    } else {
        $recordlist = array();
    }
    return $recordlist;
}

function PTS_refund($referenceNum, $compId, $pId, $pdo)
{

    $sql = $pdo->prepare("SELECT customerNum,orderNum,agentId,SKUNum,paidAmount FROM " . $GLOBALS['PREFIX'] . "agent.customerOrder WHERE payRefNum = ? ");
    $sql->execute([$referenceNum]);
    $sqlRes = $sql->fetch();
    $amount = $sqlRes['paidAmount'];

    $refund_ref = refundPayment($referenceNum, $amount);

    if ($refund_ref['msg'] == 'success') {
        $custNum = $sqlRes['customerNum'];
        $orderNum = $sqlRes['orderNum'];
        $time = date('Y-m-d', $refund_ref['result']['created']);
        $agentId = $sqlRes['agentId'];
        $skuNum = $sqlRes['SKUNum'];
        $skuPrice = get_price($skuNum);
        $refundPrice = $refund_ref['result']['amount'];
        $refundId = $refund_ref['result']['id'];
        $payRefNum = $referenceNum;
        $transactionRef = $refund_ref['result']['balance_transaction'];



        $insertRefundSql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "agent.refundDetails SET cId = $compId, pId = $pId, customerNum = '$custNum', orderNum = '$orderNum', refundDate = '$time'," . "agent_email_id = '', agent= '$agentId',skuNum='$skuNum',skuPrice='$skuPrice',refundPrice = '$refundPrice',refundType = '1'," .
            "incidentId = '',agentId = '$agentId',payRefNum = '$payRefNum',transactionRef = '$transactionRef'");
        $insertRefundSql->execute();
        $refundRes = $pdo->lastInsertId();

        $refundDate = $refund_ref['result']['created'];
        $insertCustSql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "agent.customerOrder SET refund=1,refundAmt='$refundPrice',cancelDate='$refundDate' WHERE customerNum = '$custNum' AND orderNum = '$orderNum'");
        $insertCustSql->execute();
        $custRes = $pdo->lastInsertId();

        return array("msg" => 'success', "result" => "Refund successful");
    } else {
        return array("msg" => 'failed', "result" => $refund_ref['result']);
    }
}

function PTS_customerDetails($pdo)
{
    $custSql = $pdo->prepare("SELECT customerNum,orderNum,orderDate,contractEndDate,transationType,paymentStatus,SKUDesc,provCode,refund,cancelDate,payRefNum from " . $GLOBALS['PREFIX'] . "agent.customerOrder order by id desc");
    $custSql->execute();
    $custRes = $custSql->fetchAll();
    return $custRes;
}

function refundPayment($payRef, $amount)
{

    \Stripe\Stripe::setApiKey("sk_test_pfo7bAfdfSSkasZkBysq1gf7");
    try {
        $result = \Stripe\refund::create(array(
            "charge" => $payRef,
            "amount" => $amount
        ));
        $array = $result->__toArray(true);
        $return = array("msg" => 'success', "result" => $array);
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        $return = array("msg" => 'failed', "result" => 'Refund couldnot be processed.Try later');
    }
    return $return;
}

function PTS_ManagerBarGraphData($pdo, $pid, $cid)
{
    $limitDate = strtotime(date('d-m-Y', strtotime("-30 days")));
    $sql = $pdo->prepare("SELECT count(id),createdDate,FROM_UNIXTIME(createdDate,'%d %b') dt from " . $GLOBALS['PREFIX'] . "agent.customerOrder where compId='$cid' and processId='$pid' and (createdDate>$limitDate) group by FROM_UNIXTIME(createdDate,'%d-%m-%Y') order by createdDate asc");
    $sql->execute();
    $Res = $sql->fetchAll();

    if (safe_count($Res) > 0) {
        foreach ($Res as $key => $val) {
            $Totalcount[] = (int)$val['count(id)'];
            $dates[] = $val['dt'];
        }

        $result = array("TotalCounts" => $Totalcount, "Dates" => $dates);
    } else {
        $result = array("TotalCounts" => "failed", "Dates" => "failed");
    }
    return $result;
}



function formatGridData($data)
{

    foreach ($data as $key => $val) {
        $custNum = $val['customerNum'];
        $custName = $val['coustomerFirstName'];
        $orderDate = $val['orderDate'];
        $serviceTag = $val['serviceTag'];
        $installStatus = 'Installed';
        $chatId        = $val['chatroomid'];
        $os           = $val['machineOS'];
        $manuf        = $val['machineManufacture'];
        $orderEndDate  = $val['contractEndDate'];
        $orderNum     = $val['orderNum'];

        $recordList[] = array(
            "customerName" =>  '<p class="ellipsis" title="' . $custName . '">' . $custName . '</p>',
            "customerNumber" => '<p class="ellipsis" title="' . $custNum . '">' . $custNum . '</p>',
            "date" => '<p class="ellipsis" title="' . $orderDate . '">' . $orderDate . '</p>',
            "servicetag" => '<p class="ellipsis" title="' . $serviceTag . '">' . $serviceTag . '</p>',
            "status" => '<p class="ellipsis" title="' . $installStatus . '">' . $installStatus . '</p>',

            "chatroom" => "<p class='ellipsis' id='" . $chatId . "' title='" . $chatId . "'><button class='add-user-add-btn'"
                . "style='background: #48b2e4;font-size: 13px;font-weight: 600;color: #fff;border: 0;"
                . "height: 24px;margin-top: 0%;border-radius: 15px;padding-left: 15px;padding-right: 15px;' onclick='openChat(&quot;$chatId&quot;);'>Chat</button></p>",
            "orderNumber" => '<p class="ellipsis" title="' . $orderNum . '">' . $orderNum . '</p>',
            "enDate"     => '<p class="ellipsis" title="' . $orderEndDate . '">' . $orderEndDate . '</p>',
            "machineOs" => '<p class="ellipsis" title="' . $os . '">' . $os . '</p>',
            "manufacturer" => '<p class="ellipsis" title="' . $manuf . '">' . $manuf . '</p>',


        );
    }

    return $recordList;
}
