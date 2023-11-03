<?php

function Get_CRMConfigurations()
{

    $pdo = pdo_connect();
    db_change($GLOBALS['PREFIX'] . "event", $pdo);
    $sql = $pdo->prepare("select chid,siteNames from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure");
    $sql->execute();
    $res = $sql->fetchAll() or die("error here");
    $countRecords = safe_count($res);
    if ($countRecords > 0) {
        foreach ($res as $value) {
            $chid = "";
            $siteNames = "";
            $chid .= "'" . $value['chid'] . "',";
            $siteNames .= $value['siteNames'] . ",";
        }
        $siteList = rtrim($siteNames, ",");
        $logData = "Configured Customers(from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure) : " . $siteList . "\n\n";
        ITSM_log($logData);
        $chidList = rtrim($chid, ",");
    } else {
        $chidList = "";
    }
    return $chidList;
}

function Get_CRMAPICredentials($configLists)
{
    $pdo = pdo_connect();
    db_change($GLOBALS['PREFIX'] . "agent", $pdo);
    $sql = $pdo->prepare("select A.eid,A.companyName,A.crmType,A.sitelist,A.firstName,A.crmIP,A.crmKey,A.crmUsername,A.crmPassword from " . $GLOBALS['PREFIX'] . "agent.channel A where 
       A.crmType != '0' and A.syncAssetData='compucom' and eid in(" . $configLists . ")");
    $sql->execute();
    $result = $sql->fetchAll();
    _channel($result, $pdo);
    return $result;
}

function Get_EnabledCustomers($chid, $pdo)
{
    $eventSql = $pdo->prepare("select * from  " . $GLOBALS['PREFIX'] . "event.crmSnowConfigure c where c.chid=? and tcktcreation='enabled'");
    $eventSql->execute([$chid]);
    $snowData = $eventSql->fetch();
    return $snowData;
}

function Get_AutohealNotifEvents($siteNames, $pdo)
{
    $tday = date('Y-m-d');
    $startDate = strtotime('-7 day', strtotime($tday));
    $endDate = strtotime('+1 day', strtotime($tday));
    $eventDtl = $pdo->prepare("select * from  " . $GLOBALS['PREFIX'] . "event.ticketEvents T where T.siteName=? and syncStatus in('0','4','2') and status in('open','Create failed','Close failed') and T.eventDate BETWEEN " . $startDate . " and " . $endDate . " ORDER BY T.teid DESC");
    $eventDtl->execute([$siteNames]);
    $result = $eventDtl->fetchAll($eventDtl, $pdo);
    return $result;
}



function DB_PushAutoheal_CreateResponse($ticketID, $payloadData, $result, $pdo, $site, $machine, $id, $statusCode, $succ_Status, $retryCreate)
{
    $updatedtime = time();
    $date = date("m-d-Y h:i:s");
    $ticketclose = $date;
    if (($succ_Status == '1') || ($succ_Status == 1)) {
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set syncStatus=2,ticketId=?,status='Created',ccSentPayload=?, ccResppayload=?,crontime=?,ccStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$ticketID, $payloadData, $result, $updatedtime, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Successfully Created Autoheal Ticket(s): " . $id . "<br>";
        }
    } else if (($succ_Status == '0') || ($succ_Status == 0)) {
        $retryCreate = $retryCreate + 1;
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set syncStatus=0,status='Create failed',retryCreate=?,ticketId=?,ccSentPayload=?, ccResppayload=?,crontime=?,ccStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$retryCreate, $ticketID, $payloadData, $result, $updatedtime, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Failed to Create Autoheal Ticket(s): " . $id . "<br>";
        }
    }
}

function DB_PushSchedule_CreateResponse($ticketID, $payloadData, $result, $pdo, $site, $machine, $id, $statusCode, $succ_Status, $retryCreate)
{
    $updatedtime = time();
    $date = date("m-d-Y h:i:s");
    $ticketclose = $date;
    if (($succ_Status == '1') || ($succ_Status == 1)) {
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set syncStatus=2,ticketId=?,status='Created',ccSentPayload=?, ccResppayload=?,crontime=?,ccStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$ticketID, $payloadData, $result, $updatedtime, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Successfully Created Schedule Ticket(s): " . $id . "<br>";
        }
    } else if (($succ_Status == '0') || ($succ_Status == 0)) {
        $retryCreate = $retryCreate + 1;
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set syncStatus=0,status='Create failed',retryCreate=?,ticketId=?,ccSentPayload=?, ccResppayload=?,crontime=?,ccStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$retryCreate, $ticketID, $payloadData, $result, $updatedtime, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Failed to Create Schedule Ticket(s): " . $id . "<br>";
        }
    }
}

function DB_PushAutoheal_CloseResponse($ticketID, $jsonCloseData, $closeres, $pdo, $site, $machine, $id, $statusCode, $succ_Status, $retryClose, $ticketType)
{

    $updatedtime = time();
    $date = date("m-d-Y h:i:s");
    $ticketclose = $date;
    if (($ticketType == 1) || ($ticketType == '1')) {
        $typeName = "Autoheal";
    } else if (($ticketType == 3) || ($ticketType == '3')) {
        $typeName = "Selfhelp";
    } else if (($ticketType == 4) || ($ticketType == '4')) {
        $typeName = "Schedule";
    }

    if (($succ_Status == '1') || ($succ_Status == 1)) {
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set syncStatus=3,status='Closed',ticketId=?,closeSentPayload=?,closeRespPayload=?,crontime=?,ticketClose=?,closeStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$ticketID, $jsonCloseData, $closeres, $updatedtime, $ticketclose, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Successfully Closed " . $typeName . " Ticket(s): " . $id . "<br>";
        }
    } else if (($succ_Status == '0') || ($succ_Status == 0)) {
        $retryClose = $retryClose + 1;
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set syncStatus=4,status='Close failed',retryClose=?,ticketId=?,closeSentPayload=?,closeRespPayload=?,crontime=?,ticketClose=?,closeStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$retryClose, $ticketID, $jsonCloseData, $closeres, $updatedtime, $ticketclose, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Failed to Close " . $typeName . " Ticket(s): " . $id . "<br>";
        }
    }
}


function DB_PushSchedule_CloseResponse($ticketID, $jsonCloseData, $closeres, $pdo, $site, $machine, $id, $statusCode, $succ_Status, $retryClose, $ticketType)
{

    $updatedtime = time();
    $date = date("m-d-Y h:i:s");
    $ticketclose = $date;
    if (($ticketType == 1) || ($ticketType == '1')) {
        $typeName = "Autoheal";
    } else if (($ticketType == 3) || ($ticketType == '3')) {
        $typeName = "Selfhelp";
    } else if (($ticketType == 4) || ($ticketType == '4')) {
        $typeName = "Schedule";
    }
    if (($succ_Status == '1') || ($succ_Status == 1)) {
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set syncStatus=3,status='Closed',ticketId=?,closeSentPayload=?,closeRespPayload=?,crontime=?,ticketClose=?,closeStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$ticketID, $jsonCloseData, $closeres, $updatedtime, $ticketclose, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Successfully Closed " . $typeName . " Ticket(s): " . $id . "<br>";
        }
    } else if (($succ_Status == '0') || ($succ_Status == 0)) {
        $retryClose = $retryClose + 1;
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set syncStatus=4,status='Close failed',retryClose=?,ticketId=?,closeSentPayload=?,closeRespPayload=?,crontime=?,ticketClose=?,closeStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$retryClose, $ticketID, $jsonCloseData, $closeres, $updatedtime, $ticketclose, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Failed to Close " . $typeName . " Ticket(s): " . $id . "<br>";
        }
    }
}

function DB_PushSelfhelp_CreateResponse($ticketID, $payloadData, $result, $pdo, $site, $machine, $id, $statusCode, $succ_Status, $retryCreate)
{
    $updatedtime = time();
    $date = date("m-d-Y h:i:s");
    $ticketclose = $date;
    if (($succ_Status == '1') || ($succ_Status == 1)) {
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set syncStatus=2,ticketId=?,status='Created',ccSentPayload=?, ccResppayload=?,crontime=?,ccStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$ticketID, $payloadData, $result, $updatedtime, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Successfully Created Selfhelp Ticket(s): " . $id . "<br>";
        }
    } else if (($succ_Status == '0') || ($succ_Status == 0)) {
        $retryCreate = $retryCreate + 1;
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set syncStatus=0,status='Create failed',retryCreate=?,ticketId=?,ccSentPayload=?, ccResppayload=?,crontime=?,ccStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$retryCreate, $ticketID, $payloadData, $result, $updatedtime, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Failed to Create Selfhelp Ticket(s): " . $id . "<br>";
        }
    }
}

function DB_PushSelfhelp_CloseResponse($ticketID, $jsonCloseData, $closeres, $pdo, $site, $machine, $id, $statusCode, $succ_Status, $retryClose)
{

    $updatedtime = time();
    $date = date("m-d-Y h:i:s");
    $ticketclose = $date;
    if (($succ_Status == '1') || ($succ_Status == 1)) {
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set syncStatus=3,status='Closed',ticketId=?,closeSentPayload=?,closeRespPayload=?,crontime=?,ticketClose=?,closeStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$ticketID, $jsonCloseData, $closeres, $updatedtime, $ticketclose, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Successfully Closed Selfhelp Ticket(s): " . $id . "<br>";
        }
    } else if (($succ_Status == '0') || ($succ_Status == 0)) {
        $retryClose = $retryClose + 1;
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set syncStatus=4,status='Close failed',retryClose=?,ticketId=?,closeSentPayload=?,closeRespPayload=?,crontime=?,ticketClose=?,closeStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$retryClose, $ticketID, $jsonCloseData, $closeres, $updatedtime, $ticketclose, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Failed to Close Selfhelp Ticket(s): " . $id . "<br>";
        }
    }
}

function DB_PushNotification_CloseResponse($ticketID, $jsonCloseData, $closeres, $pdo, $site, $machine, $id, $statusCode, $succ_Status, $retryClose, $audit_Id, $DartExecutionProof)
{
    $updatedtime = time();
    $date = date("m-d-Y h:i:s");
    $ticketclose = $date;
    if (($succ_Status == '1') || ($succ_Status == 1)) {
        $profileName = get_ProfiLeName($pdo, $audit_Id);
        $comments = "Action: " . $profileName;
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set audit_Id=?,DartExecutionProof=?,comments=?,syncStatus=3,status='Closed',closeSentPayload=?,closeRespPayload=?,crontime=?,ticketClose=?,closeStatusCode=? Where ticketId=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$audit_Id, $DartExecutionProof, $comments, $jsonCloseData, $closeres, $updatedtime, $ticketclose, $statusCode, $ticketID, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Successfully Closed Notification Ticket(s): " . $id . "<br>";
        }
    } else if (($succ_Status == '0') || ($succ_Status == 0)) {
        $retryClose = $retryClose + 1;
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set audit_Id=?,DartExecutionProof=?,syncStatus=2,status='Close failed',retryClose=?,ticketId=?,closeSentPayload=?,closeRespPayload=?,crontime=?,ticketClose=?,closeStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$audit_Id, $DartExecutionProof, $retryClose, $ticketID, $jsonCloseData, $closeres, $updatedtime, $ticketclose, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Failed to Close Notification Ticket(s): " . $id . "<br>";
        }
    }
}

function get_ProfiLeName($pdo, $audit_Id)
{
    $sql = $pdo->prepare("select ProfileName from " . $GLOBALS['PREFIX'] . "communication.Audit where AID=?");
    $sql->execute([$audit_Id]);
    $result = $sql->fetch();
    $pname = $result['ProfileName'];
    return $pname;
}

function check_EventCounts($nid, $eventDate, $siteName, $machineName, $pdo)
{
    $pdo = pdo_connect();
    db_change($GLOBALS['PREFIX'] . "event", $pdo);
    $eventDateConv = date("Y-m-d", $eventDate);
    $sql = $pdo->prepare("select * from  " . $GLOBALS['PREFIX'] . "event.ticketEvents T where ticketType='2' and nid=? and machineName=? and FROM_UNIXTIME(eventDate, '%Y-%m-%d')=? and siteName=? and ticketId IS NOT NULL and syncStatus='2' order by teid desc limit 1");
    $sql->execute([$nid, $machineName, $eventDateConv, $siteName]);
    $result = $sql->fetch($sql, $pdo);
    return $result;
}

function check_PendingClose($nid, $eventDate, $siteName, $machineName, $pdo)
{
    $pdo = pdo_connect();
    db_change($GLOBALS['PREFIX'] . "event", $pdo);
    $eventDateConv = date("Y-m-d", $eventDate);
    $sql = $pdo->prepare("select * from  " . $GLOBALS['PREFIX'] . "event.ticketEvents T where ticketType='2' and syncStatus='2' and nid=? and machineName=? and FROM_UNIXTIME(eventDate, '%Y-%m-%d')=? and siteName=? and ticketId IS NOT NULL and audit_Id IS NOT NULL AND DartExecutionProof IS NOT NULL order by teid desc limit 1");
    $sql->execute([$nid, $machineName, $eventDateConv, $siteName]);
    $result = $sql->fetch();
    return $result;
}

function DB_PushNotification_CreateResponse($ticketID, $data, $result, $pdo, $site, $machine, $id, $statusCode, $succ_Status, $retryCreate)
{

    $updatedtime = time();
    $date = date("m-d-Y h:i:s");
    $ticketclose = $date;
    if (($succ_Status == '1') || ($succ_Status == 1)) {
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set syncStatus=2,status='open',ticketId=?,ccSentPayload=?,ccResppayload=?,crontime=?,ticketClose=?,ccStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$ticketID, $data, $result, $updatedtime, $ticketclose, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Successfully Created Notification Ticket(s): " . $id . "<br>";
        }
    } else if (($succ_Status == '0') || ($succ_Status == 0)) {
        $retryCreate = $retryCreate + 1;
        $updateStatusSql = $pdo->prepare("update IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set syncStatus=0,status='Create failed',retryCreate=?,ticketId=?,ccSentPayload=?, ccResppayload=?,crontime='',ticketClose='',ccStatusCode=? Where teid=? and siteName=? and machineName=?");
        $updateStatusSql->execute([$retryCreate, $ticketID, $data, $result, $statusCode, $id, $site, $machine]);
        $updateStatusRes = $pdo->lastInsertId();
        if ($updateStatusRes) {
            echo "Failed to Created Notification Ticket(s): " . $id . "<br>";
        }
    }
}



function ITSM_log($data)
{
    return true;
}

function _channel($result, $pdo)
{
    $logDataS = "";
    foreach ($result as $value) {
        $companyName = $value['companyName'];
        $sitelist = $value['sitelist'];
        $logDataS .= ": Company Name:" . $companyName . " SiteName: " . $sitelist . ",";
    }
    $logData = "Channel: Customers for which credentials has been configured " . $logDataS . "\n";
    ITSM_log($logData);
    return $logData;
}

function _TicketEnabledCustomers($snowData, $pdo)
{
    $siteNames = $snowData['siteNames'];
    $notification = $snowData['notification'];
    $autoheal = $snowData['autoheal'];
    $logData .= "\nTicket Creation checking Condition Started for the Customer : " . $siteNames . "\n"
        . "Ticket Type Configuration:  \nAutoheal: " . $autoheal . " \nNotification: " . $notification . "\n";
    ITSM_log($logData);
}



function compCreateData($jsonData, $datafields, $actionObj)
{

    if ($actionObj == "autoheal") {
        $contactType = "Auto-Resolution";
    } else if ($actionObj == "selfhelp") {
        $contactType = "Self-Service";
    } else if ($actionObj == "event") {
        $contactType = "Event";
    } else if ($actionObj == "schedule") {
        $contactType = "Self-Resolution";
    }

    $today = time();
    $transDt = date(DATE_W3C, $today);
    $transationid = md5(mt_rand());
    $jsonArray = safe_json_decode($jsonData, true);
    $equipement['model'] = "WINDOWS";
    $equipement['description'] = "DESKTOP-OCPSBGI";
    foreach ($jsonArray as $key => $value) {
        if ($value == "%%") {
            unset($key);
        } else {
            $res[$key] = $value;
        }
    }
    unset($res['equipment']);

    $string = str_replace("\r\n", PHP_EOL, $datafields['ticketDescription']);
    $string2 = str_replace("\r\n", " ", $datafields['ticketDescription']);
    $string1 = str_replace("\n", " ", $string2);

    $arrayReplacements = array(
        "transactionId" => "$transationid",
        "timeStamp" => "$transDt",
        "refCaseNumber" => "NH_ID",
        "openedDateStamp" => "$transDt",
        "transDateStamp" => "$transDt",
        "problemDescription" => "" . $string . "",
        "shortDescription" => "" . $datafields['ticketSub'] . "",
        "priorityCode" => 3,
        "supportGroup" => "CMPC - Service Desk Self-Heal",
        "notes" => "" . $string . "",
        "contactType" => "$contactType",
        "internalNotes" => "" . $string . "",

        "equipment" => $equipement
    );


    $arrayReplacementsDB = array(
        "transactionId" => "$transationid",
        "timeStamp" => "$transDt",
        "refCaseNumber" => "NH_ID",
        "openedDateStamp" => "$transDt",
        "transDateStamp" => "$transDt",
        "problemDescription" => "" . $string1 . "",
        "shortDescription" => "" . $datafields['ticketSub'] . "",
        "priorityCode" => 3,
        "supportGroup" => "CMPC - Service Desk Self-Heal",
        "notes" => "" . $string1 . "",
        "contactType" => "$contactType",
        "internalNotes" => "" . $string1 . "",

        "equipment" => $equipement
    );

    $r = array_merge($res, $arrayReplacements);
    $data = json_encode($r);
    $rdb = array_merge($res, $arrayReplacementsDB);
    $datadb = json_encode($rdb);
    $response = array($data, $datadb);

    return $response;
}

function compCreateData__Notification($jsonData, $datafields, $actionObj, $machineName, $siteName)
{

    if ($actionObj == "autoheal") {
        $contactType = "Auto-Resolution";
    } else if ($actionObj == "selfhelp") {
        $contactType = "Self-Service";
    } else if ($actionObj == "event") {
        $contactType = "Event";
    } else if ($actionObj == "schedule") {
        $contactType = "Self-Resolution";
    }

    $today = time();
    $transDt = date(DATE_W3C, $today);
    $transationid = md5(mt_rand());
    $jsonArray = safe_json_decode($jsonData, true);
    $equipement['model'] = "WINDOWS";
    $equipement['description'] = "DESKTOP-OCPSBGI";
    foreach ($jsonArray as $key => $value) {
        if ($value == "%%") {
            unset($key);
        } else {
            $res[$key] = $value;
        }
    }
    unset($res['equipment']);
    if (strpos($siteName, '__') !== false) {
        $rs = explode("__", $siteName);
        $SiteNameVal = $rs[0];
    }
    $datafields['ticketDescription'] = "Machine: " . $machineName . " Site: " . $SiteNameVal . " Description: " . $datafields['ticketDescription'];
    $string = str_replace("\r\n", PHP_EOL, $datafields['ticketDescription']);
    $string2 = str_replace("\r\n", " ", $datafields['ticketDescription']);
    $string1 = str_replace("\n", " ", $string2);

    $arrayReplacements = array(
        "transactionId" => "$transationid",
        "timeStamp" => "$transDt",
        "refCaseNumber" => "NH_ID",
        "openedDateStamp" => "$transDt",
        "transDateStamp" => "$transDt",
        "problemDescription" => "" . $string . "",
        "shortDescription" => "" . $datafields['ticketSub'] . "",
        "priorityCode" => 3,
        "supportGroup" => "CMPC - Service Desk Self-Heal",
        "notes" => "" . $string . "",
        "contactType" => "$contactType",
        "internalNotes" => "" . $string . "",

        "equipment" => $equipement
    );


    $arrayReplacementsDB = array(
        "transactionId" => "$transationid",
        "timeStamp" => "$transDt",
        "refCaseNumber" => "NH_ID",
        "openedDateStamp" => "$transDt",
        "transDateStamp" => "$transDt",
        "problemDescription" => "" . $string1 . "",
        "shortDescription" => "" . $datafields['ticketSub'] . "",
        "priorityCode" => 3,
        "supportGroup" => "CMPC - Service Desk Self-Heal",
        "notes" => "" . $string1 . "",
        "contactType" => "$contactType",
        "internalNotes" => "" . $string1 . "",

        "equipment" => $equipement
    );

    $r = array_merge($res, $arrayReplacements);
    $data = json_encode($r);
    $rdb = array_merge($res, $arrayReplacementsDB);
    $datadb = json_encode($rdb);
    $response = array($data, $datadb);

    return $response;
}

function closeDataCompcom($jsonCreateData, $datafields, $ticketID)
{

    $today = time();
    $transDt = date(DATE_W3C, $today);
    $transationid = md5(mt_rand());
    $jsonArray = safe_json_decode($jsonCreateData, true);
    $string = str_replace("\r\n", PHP_EOL, $datafields['ticketDescription']);
    $arrayclose['docType'] = $jsonArray['docType'];
    $arrayclose['client'] = $jsonArray['client'];
    $arrayclose['transactionId'] = $transationid;
    $arrayclose['timeStamp'] = $transDt;
    $arrayclose['sender'] = $jsonArray['sender'];
    $arrayclose['caseNumber'] = "$ticketID";
    $arrayclose['refCaseNumber'] = "$ticketID";
    $arrayclose['transDateStamp'] = $transDt;
    $resol = $datafields['ticketSub'];
    $resolution = array(
        "text" => $resol,
        "code" => "Resolved - Full Restoration",
        "timeStamp" => $transDt,
    );
    $arrayclose['resolution'] = $resolution;
    $arrayclose['statusCode'] = $jsonArray['statusCode'];

    $descr = trim($string);
    $arrayclose['problemDescription'] = str_replace(PHP_EOL, '', $descr);

    $r = json_encode($arrayclose);



    return $r;
}



function createSNOWticket($data, $instanceAPIUrl, $crmUsername, $crmPassword)
{
    try {

        $cred = $crmUsername . ":" . $crmPassword;
        if (!empty($data)) {
            $headers = array();
            $headers[] = "Content-Type: application/json";
            $headers[] = "Accept: application/json";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $instanceAPIUrl);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $cred);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            curl_error($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $result;
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        echo "Exception : " . $ex;
    }
}

function closeTicketCompcom($data, $instanceAPIUrl, $crmUsername, $crmPassword)
{
    try {
        $cred = $crmUsername . ":" . $crmPassword;
        if (!empty($data)) {
            $headers = array();
            $headers[] = "Content-Type: application/json";
            $headers[] = "Accept: application/json";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $instanceAPIUrl);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $cred);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $result;
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        echo "Exception : " . $ex;
    }
}
