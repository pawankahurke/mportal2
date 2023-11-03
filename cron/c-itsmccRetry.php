<?php




include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-util.php';
include_once '../lib/l-ticketFunc.php';




ticket_Functions();

function ticket_Functions()
{

    $configLists = Get_CRMConfigurations();
    $CredList = Get_CRMAPICredentials($configLists);
    $countRocrds = safe_count($CredList);

    writelog("RECORDS found :" . $countRocrds);

    if ($countRocrds > 0) {

        $cnt = 0;

        foreach ($CredList as $value) {

            $cnt++;
            $companyName = $value['companyName'];
            $sitelist = $value['sitelist'];
            $crmtype = $value['crmType'];
            $crmkey = $value['crmKey'];
            $instanceAPIUrl = $value['crmIP'];
            $chid = $value['eid'];
            $crmUsername = $value['crmUsername'];
            $crmPassword = $value['crmPassword'];
            $firstName = $value['firstName'];
            writelog("***************************PROCESSING SITE " . $cnt . ": " . $sitelist . "***************************");
            Push_ticketInfo($instanceAPIUrl, $chid, $crmtype, $companyName, $sitelist, $crmUsername, $crmPassword, $firstName);
            writelog("***************************END OF SITE : " . $cnt . ": " . $sitelist . "***************************");
        }
    } else {
        echo "No customers are configured...";
    }
}



function Push_ticketInfo($instanceAPIUrl, $chid, $crmtype, $companyName, $sitelist, $crmUsername, $crmPassword, $firstName)
{

    try {
        $db = db_connect();
        db_change($GLOBALS['PREFIX'] . "event", $db);

        $snowData = Get_EnabledCustomers($chid, $db);
        $isSnowConfig = (safe_count($snowData) > 0) ? "TRUE" : "FALSE";
        writelog("Is SNOW-Configure Enabled : " . $isSnowConfig);
        if (safe_count($snowData) > 0) {

            $chid = $snowData['chid'];
            $siteNames = $snowData['siteNames'];
            $TickenblStatus = $snowData['tcktcreation'];
            $jsonCreateData = $snowData['jsonData'];
            $jsonCloseData = $snowData['jsonCloseData'];
            $notification = $snowData['notification'];
            $autoheal = $snowData['autoheal'];
            $selfhelp = $snowData['selfhelp'];
            $schedule = $snowData['schedule'];

            writelog("AUTOHEAL :" . (intval($autoheal) == 1 ? "Enabled" : "Disabled"));
            writelog("NOTIFICATION :" . (intval($notification) == 1 ? "Enabled" : "Disabled"));
            writelog("SELFHELP :" . (intval($selfhelp) == 1 ? "Enabled" : "Disabled"));
            writelog("SCHEDULE :" . (intval($schedule) == 1 ? "Enabled" : "Disabled"));

            $eventDtls = Get_AutohealNotifEvents($siteNames, $db);
            $countEvents = safe_count($eventDtls);
            writelog("Total number of ticket events found : " . $countEvents);

            if ($countEvents > 0) {

                for ($i = 0; $i < $countEvents; $i++) {


                    $ticketId = $eventDtls[$i]['ticketId'];
                    $status = $eventDtls[$i]['status'];
                    $syncStatus = $eventDtls[$i]['syncStatus'];
                    $eventDate = $eventDtls[$i]['eventDate'];
                    $teid = $eventDtls[$i]['teid'];
                    $datafields['ticketType'] = $eventDtls[$i]['ticketType'];
                    $nid = $eventDtls[$i]['nid'];
                    $datafields['siteName'] = $eventDtls[$i]['siteName'];
                    $datafields['machineName'] = $eventDtls[$i]['machineName'];
                    $datafields['eventDate'] = $eventDtls[$i]['eventDate'];
                    $datafields['ticketSub'] = $eventDtls[$i]['ticketSub'];
                    $datafields['ticketDescription'] = str_replace("'", "", $eventDtls[$i]['ticketDescription']);
                    $datafields['crmType'] = $eventDtls[$i]['crmType'];
                    $ticketType = $eventDtls[$i]['ticketType'];
                    $retryCreate = $eventDtls[$i]['retryCreate'];
                    $retryClose = $eventDtls[$i]['retryClose'];
                    $siteName = $datafields['siteName'];
                    $machineName = $datafields['machineName'];
                    $logData = "TicketEventId(teid) [" . $teid . "]-Ticketing process STARTED Site/Cusomer [" . $siteName . "]" .
                        ",machine:[" . $machineName . "],eventDate:[" . $datafields['eventDate'] . "],ticketType:[" . $datafields['ticketType'] . "],syncStatus:[" . $syncStatus . "]";
                    writelog($logData);



                    if (((intval($syncStatus) == 0)) && ((intval($retryCreate) < 3))) {
                        $logData = "[SyncStatus is '0' and retry is < 3] ";

                        writelog("NEW TICKET : " . $logData);
                        if ((($autoheal == '1') || ($autoheal == 1))) {
                            if ((($ticketType == '1') || ($ticketType == 1)) && (($ticketType != '2') || ($ticketType != 2)) && (($ticketType != '3') || ($ticketType != 3)) && (($ticketType != '4') || ($ticketType != 4))) {
                                $actionObj = "autoheal";
                                $dataResult = compCreateData($jsonCreateData, $datafields, $actionObj);
                                writelog("JSON data: " . json_encode($dataResult));
                                $data = $dataResult[0];
                                $payloadData = $dataResult[1];
                                $AutohealTime = date("m-d-Y h:i:s");
                                $logData = "Autoheal ticketing process started Site/Cusomer[" . $siteName . "] At [" . $AutohealTime . "]\n";
                                writelog($logData);

                                writelog("POST DATA:" . $data . ", instanceAPIUrl" . $instanceAPIUrl . ",crmUsername: " . $crmUsername . ",crmPassword: " . $crmPassword);
                                $result = createSNOWticket($data, $instanceAPIUrl, $crmUsername, $crmPassword);
                                writelog("result->" . $result);
                                $rsponse = safe_json_decode($result);

                                $statusCode = $rsponse->statusCode;
                                writelog("<****>statusCode : " . $statusCode . "<****>" . "\r\n");
                                $result = json_encode($rsponse);
                                if ((intval($statusCode) >= 200) && (intval($statusCode) < 300)) {
                                    $ticketID = $rsponse->caseNumber;

                                    $logData = "\nAutoheal Ticket Creation Success Site/Cusomer" . $siteName . " with status code" . $statusCode . " and ticket ID :" . $ticketID . "\n";
                                    writelog($logData);

                                    $succ_Status = "1";
                                    DB_PushAutoheal_CreateResponse($ticketID, $payloadData, $result, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryCreate);

                                    $closeData = closeDataCompcom($jsonCloseData, $datafields, $ticketID);
                                    $AutohealTime = date("m-d-Y h:i:s");
                                    $logData = "\nAutoheal Ticket Close Started Site/Cusomer" . $siteName . " At " . $AutohealTime . "\n";
                                    writelog($logData);
                                    $closeres = closeTicketCompcom($closeData, $instanceAPIUrl, $crmUsername, $crmPassword);
                                    $Closersponse = safe_json_decode($closeres);
                                    $statusCode = $Closersponse->statusCode;
                                    writelog(" statusCode: " . $statusCode);
                                    if ((intval($statusCode) == 200)) {
                                        $succ_Status = "1";
                                        $logData = "\nAutoheal Close Success for Site/Cusomer" . $siteName . "\n with code" . $statusCode;
                                        writelog($logData);
                                        DB_PushAutoheal_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose, $ticketType);
                                    } else if ($Closersponse->errorCode) {
                                        $statusCode = "";
                                        $succ_Status = "0";
                                        $logData = "\nAutoheal Close Failed for Site/Cusomer" . $siteName . "\n with error code" . $Closersponse->errorCode;
                                        writelog($logData);
                                        DB_PushAutoheal_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose, $ticketType);
                                    } else {
                                        $logData = "\nAutoheal Close Failed for Site/Cusomer" . $siteName . "\n (Not mactching Status code 200 / No error code found) but found status code is :" . $statusCode;
                                        writelog($logData);
                                        $statusCode = "";
                                        $succ_Status = "0";

                                        DB_PushAutoheal_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose, $ticketType);
                                    }
                                } else if ($rsponse->errorCode) {
                                    $statusCode = "";
                                    $logData = "\nAutoheal Ticket Creation Failed for Site/Cusomer" . $siteName . "\n with error code" . $rsponse->errorCode;
                                    writelog($logData);
                                    $ticketID = "";
                                    $succ_Status = "0";
                                    DB_PushAutoheal_CreateResponse($ticketID, $payloadData, $result, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryCreate);
                                } else {
                                    $logData = "\nAutoheal Ticket Creation Failed for Site/Cusomer" . $siteName . "\n(Not mactching Status code 200 / No error code found)but found status code is :" . $rsponse->statusCode;
                                    writelog($logData);
                                    $ticketID = "";
                                    $succ_Status = "0";
                                    DB_PushAutoheal_CreateResponse($ticketID, $payloadData, $result, $db, $siteName, $machineName, $teid, $rsponse->statusCode, $succ_Status, $retryCreate);
                                }
                            }
                        }
                        if ((($schedule == '1') || ($schedule == 1))) {
                            if ((($ticketType == '4') || ($ticketType == 4)) && (($ticketType != '2') || ($ticketType != 2)) && (($ticketType != '1') || ($ticketType != 1)) && (($ticketType != '3') || ($ticketType != 3))) {
                                $actionObj = "schedule";
                                $dataResult = compCreateData($jsonCreateData, $datafields, $actionObj);

                                $data = $dataResult[0];
                                $payloadData = $dataResult[1];
                                writelog("TYPE : " . $actionObj);
                                writelog("JSONPayload-API : " . $data);
                                writelog("JSONPayload-StoreInDB : " . $payloadData);

                                $AutohealTime = date("m-d-Y h:i:s");
                                $logData = "\Schedule ticketing process Started Site/Cusomer[" . $siteName . "] At " . $AutohealTime . "\n";
                                writelog($logData);

                                $result = createSNOWticket($data, $instanceAPIUrl, $crmUsername, $crmPassword);

                                $rsponse = safe_json_decode($result);
                                $statusCode = $rsponse->statusCode;
                                if (($statusCode == '200') || ($statusCode == 200)) {
                                    $ticketID = $rsponse->caseNumber;

                                    $logData = "\nSchedule Ticket Creation Success Site/Cusomer" . $siteName . " with status code" . $statusCode . " and ticket ID :" . $ticketID . "\n";
                                    writelog($logData);

                                    $succ_Status = "1";
                                    DB_PushSchedule_CreateResponse($ticketID, $payloadData, $result, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryCreate);

                                    $closeData = closeDataCompcom($jsonCloseData, $datafields, $ticketID);
                                    $AutohealTime = date("m-d-Y h:i:s");
                                    $logData = "\nSchedule Ticket Close Started Site/Cusomer" . $siteName . " At " . $AutohealTime . "\n";
                                    writelog($logData);
                                    $closeres = closeTicketCompcom($closeData, $instanceAPIUrl, $crmUsername, $crmPassword);
                                    $Closersponse = safe_json_decode($closeres);
                                    $statusCode = $Closersponse->statusCode;
                                    if (($statusCode == '200') || ($statusCode == 200)) {
                                        $succ_Status = "1";
                                        DB_PushSchedule_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose, $ticketType);
                                    } else if ($Closersponse->errorCode) {
                                        $statusCode = "";
                                        $succ_Status = "0";
                                        DB_PushSchedule_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose, $ticketType);
                                    } else {
                                        $statusCode = "";
                                        $succ_Status = "0";
                                        DB_PushSchedule_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose, $ticketType);
                                    }
                                } else if ($rsponse->errorCode) {
                                    $statusCode = "";
                                    $logData = "\nAutoheal Ticket Creation Failed for Site/Cusomer" . $siteName . "\n";
                                    writelog($logData);
                                    $ticketID = "";
                                    $succ_Status = "0";
                                    DB_PushSchedule_CreateResponse($ticketID, $payloadData, $result, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryCreate);
                                } else {
                                    $statusCode = "";
                                    $logData = "\nAutoheal Ticket Creation Failed for Site/Cusomer" . $siteName . "\n";
                                    writelog($logData);
                                    $ticketID = "";
                                    $succ_Status = "0";
                                    DB_PushSchedule_CreateResponse($ticketID, $payloadData, $result, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryCreate);
                                }
                            }
                        }
                        if ((($selfhelp == '1') || ($selfhelp == 1))) {

                            if ((($ticketType == '3') || ($ticketType == 3)) && (($ticketType != '2') || ($ticketType != 2)) && (($ticketType != '1') || ($ticketType != 1)) && (($ticketType != '4') || ($ticketType != 4))) {
                                $actionObj = "selfhelp";
                                $dataResult = compCreateData($jsonCreateData, $datafields, $actionObj);
                                $data = $dataResult[0];
                                $payloadData = $dataResult[1];
                                writelog("TYPE : " . $actionObj);
                                writelog("JSONPayload-API : " . $data);
                                writelog("JSONPayload-StoreInDB : " . $payloadData);

                                $AutohealTime = date("m-d-Y h:i:s");
                                $logData = "\nSelfhelp ticketing process Started Site/Cusomer" . $siteName . " At " . $AutohealTime . "\n";
                                writelog($logData);
                                $result = createSNOWticket($data, $instanceAPIUrl, $crmUsername, $crmPassword);
                                writelog("createSNOWticket : RESULT: " . $result);
                                writelog("createSNOWticket : RESULT: " . json_encode($result));
                                $rsponse = safe_json_decode($result);
                                $statusCode = $rsponse->statusCode;
                                writelog("createSNOWticket : statusCode:" . $statusCode);
                                if (($statusCode == '200') || ($statusCode == 200)) {
                                    $ticketID = $rsponse->caseNumber;

                                    $logData = "\nSelfhelp Ticket Creation Success Site/Cusomer" . $siteName . " with status code" . $statusCode . " and ticket ID :" . $ticketID . "\n";
                                    writelog($logData);

                                    $succ_Status = "1";
                                    DB_PushSelfhelp_CreateResponse($ticketID, $payloadData, $result, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryCreate);

                                    $closeData = closeDataCompcom($jsonCloseData, $datafields, $ticketID);
                                    $AutohealTime = date("m-d-Y h:i:s");
                                    $logData = "\nAutoheal Ticket Close Started Site/Cusomer" . $siteName . " At " . $AutohealTime . "\n";
                                    writelog($logData);
                                    $closeres = closeTicketCompcom($closeData, $instanceAPIUrl, $crmUsername, $crmPassword);
                                    $Closersponse = safe_json_decode($closeres);
                                    $statusCode = $Closersponse->statusCode;
                                    if (($statusCode == '200') || ($statusCode == 200)) {
                                        $succ_Status = "1";
                                        DB_PushSelfhelp_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose);
                                    } else if ($Closersponse->errorCode) {
                                        $statusCode = "";
                                        $succ_Status = "0";
                                        DB_PushSelfhelp_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose);
                                    } else {
                                        $statusCode = "";
                                        $succ_Status = "0";
                                        DB_PushSelfhelp_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose);
                                    }
                                } else if ($rsponse->errorCode) {
                                    $statusCode = "";
                                    $logData = "\nAutoheal Ticket Creation Failed for Site/Cusomer" . $siteName . "\n";
                                    writelog($logData);
                                    $ticketID = "";
                                    $succ_Status = "0";
                                    DB_PushSelfhelp_CreateResponse($ticketID, $payloadData, $result, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryCreate);
                                } else {
                                    $statusCode = "";
                                    $logData = "\nAutoheal Ticket Creation Failed for Site/Cusomer" . $siteName . "\n";
                                    writelog($logData);
                                    $ticketID = "";
                                    $succ_Status = "0";
                                    DB_PushSelfhelp_CreateResponse($ticketID, $payloadData, $result, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryCreate);
                                }
                            }
                        }

                        if (((intval($notification) == 1))) {

                            if ((intval($ticketType) == 2)) {
                                $actionObj = "event";
                                $dataResult = compCreateData__Notification($jsonCreateData, $datafields, $actionObj, $machineName, $siteName);
                                writelog("COUNT - compCreateData__Notification : " . safe_count($dataResult));
                                $data = $dataResult[0];
                                $payloadData = $dataResult[1];
                                $Event_Checks = check_EventCounts($nid, $eventDate, $siteName, $machineName, $db);
                                writelog("check_EventCounts : " . json_encode($Event_Checks));
                                $ticketCreatingStatus = safe_count($Event_Checks) == 0 ? "CREATE NEW TICKET" : "UPDATE EXISTING TICKET";

                                writelog($ticketCreatingStatus . "-->" . safe_count($Event_Checks));
                                if (safe_count($Event_Checks) > 0) {
                                    writelog("Updating.......with existing  ticketId : " . $Event_Checks['ticketId']);
                                    echo "'SUCCESS' - UPDATING with existing Ticket : " . $Event_Checks['ticketId'];
                                    $ticketID = $Event_Checks['ticketId'];
                                    $statusCode = $Event_Checks['ccStatusCode'];
                                    $result = $Event_Checks['ccResppayload'];
                                    $succ_Status = "1";
                                    DB_PushNotification_CreateResponse($ticketID, $payloadData, $result, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryCreate);
                                } else {

                                    writelog("Creating new ticket.......");
                                    $NotifTime = date("m-d-Y h:i:s");
                                    $logData = "Notification ticketing process Started Site/Cusomer[" . $siteName . "] At " . $NotifTime;
                                    writelog($logData);

                                    $result = createSNOWticket($data, $instanceAPIUrl, $crmUsername, $crmPassword);
                                    $rsponse = safe_json_decode($result);
                                    $notification_statusCode = $rsponse->statusCode;
                                    writelog("[TICKET_CREATION] - RESPONSE statusCode : " . $notification_statusCode);
                                    $result = json_encode($rsponse);

                                    if ((intval($notification_statusCode) >= 200) && (intval($notification_statusCode) < 300)) {

                                        $ticketID = $rsponse->caseNumber;
                                        $succ_Status = "1";
                                        echo "'SUCCESS' - CREATED new ticketID: : " . $ticketID;
                                        DB_PushNotification_CreateResponse($ticketID, $payloadData, $result, $db, $siteName, $machineName, $teid, $notification_statusCode, $succ_Status, $retryCreate);
                                    } else if ($rsponse->errorCode) {
                                        echo "'FAILED' - Couldnot CREATE new ticket error code:" . $rsponse->errorCode;
                                        $statusCode = "";
                                        $ticketID = "";
                                        $succ_Status = "0";
                                        $NotifTime = date("m-d-Y h:i:s");
                                        $logData = "\nNotification Ticket Creation Failed Site/Cusomer" . $siteName . " At " . $NotifTime . "\n With error code : " . $rsponse->errorCode;
                                        writelog($logData);
                                        DB_PushNotification_CreateResponse($ticketID, $payloadData, $result, $db, $siteName, $machineName, $teid, $notification_statusCode, $succ_Status, $retryCreate);
                                    } else {
                                        $statusCode = "";
                                        $ticketID = "";
                                        $succ_Status = "0";
                                        $NotifTime = date("m-d-Y h:i:s");
                                        $logData = "\nNotification Ticket Creation Failed Site/Cusomer" . $siteName . " At " . $NotifTime . "\n";
                                        writelog($logData);
                                        DB_PushNotification_CreateResponse($ticketID, $payloadData, $result, $db, $siteName, $machineName, $teid, $notification_statusCode, $succ_Status, $retryCreate);
                                    }
                                }
                            }
                        }
                    } else if (((intval($syncStatus) == 4)) && ((intval($retryClose) < 3))) {
                        $teid = $eventDtls[$i]['teid'];
                        $ticketID = $eventDtls[$i]['ticketId'];
                        $closeData = closeDataCompcom($jsonCloseData, $datafields, $ticketID);
                        $closeres = closeTicketCompcom($closeData, $instanceAPIUrl, $crmUsername, $crmPassword);
                        $Closersponse = safe_json_decode($closeres);
                        $statusCode = $Closersponse->statusCode;
                        if (($statusCode == '200') || ($statusCode == 200)) {
                            $succ_Status = "1";
                            DB_PushAutoheal_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose, $ticketType);
                        } else if ($Closersponse->errorCode) {
                            $statusCode = "";
                            $succ_Status = "0";
                            DB_PushAutoheal_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose, $ticketType);
                        } else {
                            $statusCode = "";
                            $succ_Status = "0";
                            DB_PushAutoheal_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose, $ticketType);
                        }
                    } else if ((($syncStatus == 2) || ($syncStatus == '2')) && (($retryClose < 4) || ($retryClose < '4')) && (($retryClose != 4) || ($retryClose != '4')) && (($retryClose != '3') || ($retryClose != 3))) {

                        $Event_Close = check_PendingClose($nid, $eventDate, $siteName, $machineName, $db);

                        if ($Event_Close) {
                            $ticketID = $Event_Close['ticketId'];
                            $statusCode = $Event_Close['closeStatusCode'];

                            $audit_Id = $Event_Close['audit_Id'];
                            $DartExecutionProof = $Event_Close['DartExecutionProof'];
                            $ticketID = $Event_Close['ticketId'];
                            $succ_Status = "1";
                            $closeData = closeDataCompcom($jsonCloseData, $datafields, $ticketID);
                            $closeres = closeTicketCompcom($closeData, $instanceAPIUrl, $crmUsername, $crmPassword);
                            $Closersponse = safe_json_decode($closeres);
                            $statusCode = $Closersponse->statusCode;
                            if (($statusCode == '200') || ($statusCode == 200)) {
                                $succ_Status = "1";
                                DB_PushNotification_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose, $audit_Id, $DartExecutionProof);
                            } else if ($Closersponse->errorCode) {
                                $statusCode = "";
                                $succ_Status = "0";
                                DB_PushNotification_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose, $audit_Id, $DartExecutionProof);
                            } else {
                                $statusCode = "";
                                $succ_Status = "0";
                                DB_PushNotification_CloseResponse($ticketID, $closeData, $closeres, $db, $siteName, $machineName, $teid, $statusCode, $succ_Status, $retryClose, $audit_Id, $DartExecutionProof);
                            }
                        }
                    }
                }
            } else {
                echo "No new Events For the " . $siteNames;
            }
        } else {
            writelog("Push_ticketInfo snowData : ITSM is disabled ");
        }
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
    }
}

function writelog($message)
{
    logs::log(__FILE__, __LINE__, $message, ['tag' => "c-itsmccRetry"]);
}
