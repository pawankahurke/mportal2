<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-dbConnect.php';
ini_set('max_execution_time', '240'); // 4 minutes 
$cronConfig = new CronFunction();
if ($cronConfig->startPermission('c-crmincident', 'C-CRMINCIDENT') != 'true') {
    echo 'no launch rights';
    exit();
}
$cronConfig->updateMonitoring('C-CRMINCIDENT');

function getCRMdetailsTcktcreation()
{
    return  NanoDB::find_many('select * from  ' . $GLOBALS['PREFIX'] . 'event.crmSnowConfigure where tcktcreation = ?  ', null, ['enabled']);
}

/**
 * push Tickets to servicenow.
 *
 */
function push_Tickets($instanceApiUrl, $crmUsername, $crmPassword, $crmtype, $companyName, $siteName)
{
    $timeNow = time();
    do {
        $done = false;
        $closedTicketTypes = [1, 3, 4];
        $ticketState = 1;
        // specified a limit of 10 so as not to iterate over 5000+ records
        $ticEveRes = NanoDB::find_many('select * from  ' . $GLOBALS['PREFIX'] . 'event.ticketEvents where siteName = ? and status in ("4", "open") and crontime is null and scrip = 0 ORDER BY teid DESC limit 1', null, [$siteName]);
        if (!$ticEveRes) {
            $ticEveRes = NanoDB::find_many('select * from  ' . $GLOBALS['PREFIX'] . 'event.ticketEvents where siteName = ? and status in ("4", "open") and ticketId is null and scrip = 0 ORDER BY teid DESC limit 1', null, [$siteName]);
        }
        if (!$ticEveRes) {
            return true;
        }

        foreach ($ticEveRes as $value) {
            // @todo Here Mark tikets as in work (update in database)
            $timeNow = time();
            NanoDB::query('update  ' . $GLOBALS['PREFIX'] . 'event.ticketEvents set crontime = ? where teid = ?', [$timeNow, $value['teid']]);


            if (in_array($value['ticketType'], $closedTicketTypes)) {
                $ticketState = 7;
            }

            $teid = $value['teid'];
            $machineName = $value['machineName'];
            $ticketSub = $value['ticketSub'];
            $ticketDescription = $value['ticketDescription'];

            //  ticketType = autoheal - 1, selfhelp - 3, schedule - 4 , notification - 2
            $instanceData = [];
            // $instanceData['assignment_group'] = 'Service Desk';
            // $instanceData['companyName'] = $companyName;
            // $instanceData['company'] = $companyName;
            // $instanceData['category'] = 'Software';
            // $instanceData['subcategory'] = 'Operating System';
            // $instanceData['service_tag'] = $machineName;
            // $instanceData['state'] = $ticketState;
            // $instanceData['impact'] = 1;
            // $instanceData['urgency'] = 1;
            // $instanceData['priority'] = ($ticketState == 7) ? 3 : 1;
            // $instanceData['contact_type'] = 'email';
            // $instanceData['correlation_id'] = 'Nano Heal Integrator';
            // $instanceData['opened_by'] = 'Nano Heal Integrator';
            // $instanceData['machineName'] = $machineName;
            // $instanceData['short_description'] = $ticketSub . " " . "Machine: " . $machineName;
            // $instanceData['description'] = $ticketDescription;
            // $instanceData['comments'] = $ticketDescription;
            // if ($ticketState == 7) {
            //     $instanceData['close_code'] = 'Closed/Resolved by Caller';
            //     $instanceData['close_notes'] = 'Closed Ticket';
            // }

            $instanceData['id'] =  $teid; // 222
            $instanceData['ntype'] = $value['ticketType']; // 3
            $instanceData['priority'] = ($ticketState == 7) ? 3 : 1; // 5

            $instanceData['name'] = $ticketSub . " " . "Machine: " . $machineName; // "Test"
            $instanceData['username'] = $machineName; // "DESKTOP-KIEQH54";
            $instanceData['site'] = $value['siteName']; // "BCBSA_UAT";
            $instanceData['machine'] = $machineName; // "DESKTOP-KIEQH54"
            $instanceData['nid'] =  $value['nid']; // 35;


            // $instanceData['expire'] = 2147483647;     //event.Console
            $instanceData['count'] = 1;    //event.Console
            $instanceData['event_list'] = json_encode(json_encode([
                ["event_value" => $ticketSub, "client_time" => (int)$value['eventDate']]
            ])); // "\"[{\\\"event_value\\\":\\\"PercentUtilization:99\\\",\\\"client_time\\\":1616651914}]\"";   //event.Console
            //      $instanceData['lastrun'] = 1616651955;  //event.Console
            //      $instanceData['this_run'] = 1616651400;  //event.Console
            //      $instanceData['oldstatus'] = 0; //event.Console
            //      $instanceData['status'] = 4; //event.Console
            //      $instanceData['activeStatus'] = 1; //event.Console

            createSnowTickets($instanceData, $instanceApiUrl, $crmUsername, $crmPassword, $machineName, $teid);
            $done = true;
            if (url::getToInt('maxLimit1')) {
                return true;
            }
        }
    } while ($done);

    return true;
}

function createSnowTickets($instanceData, $instanceApiUrl, $crmUsername, $crmPassword, $machineName, $teid)
{

    echo __FUNCTION__ . ":" . __LINE__ . "instanceApiUrl $instanceApiUrl\n";
    // logs::log("instanceApiUrl $instanceApiUrl\n");

    $instanceResp = createTicketApi($instanceData, $instanceApiUrl, $crmUsername, $crmPassword);

    try {
        $respData = safe_json_decode($instanceResp, true);
    } catch (Exception $e) {
        $search_string = strpos($instanceResp, "<h1>Your instance is hibernating</h1>");
        if ($search_string) {
            NanoDB::query('update  ' . $GLOBALS['PREFIX'] . 'event.crmSnowConfigure set tcktcreation = ? where crmUrl = ?', ['disabled', $instanceApiUrl]);
            exit();
        }
    }

    if ($respData && isset($respData['result']) && isset($respData['result']['message'])) {
        $sys_id = preg_replace("#Incident Created #", "", $respData['result']['message']);
        updateTicketEventsDetails($instanceData, $respData, $machineName, $sys_id, $teid);
        return true;
    } else {
        //  tickets as in error.
        // And check what happens? Maybe servicenow config is not valid or servicenow account was deleted and domain is not available?
        exit();
    }

    return false;
}

function createTicketApi($instanceData, $instanceApiUrl, $crmUsername, $crmPassword)
{

    $instanceDataJson = json_encode($instanceData);

    logs::log(__FILE__, __LINE__, 'sending to SN payload - ', [$instanceDataJson]);

    try {
        $URL = $instanceApiUrl . "/api/x_807428_nanoheal/nanoheal_integration/notifications";
        $userpwd = "$crmUsername:$crmPassword";

        echo __FUNCTION__ . ":" . __LINE__ . "URL:$URL ($userpwd)\n";

        if (!empty($instanceDataJson)) {
            echo __FUNCTION__ . ":" . __LINE__ . "send curl\n";
            $basicAuth = base64_encode($userpwd);
            $headers = array();
            $headers[] = "Content-Type: application/json";
            $headers[] = "Accept: application/json";
            $headers[] = "Authorization: Basic " . $basicAuth;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_URL, $URL);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $instanceDataJson);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $userpwd);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);

            curl_close($ch);

            echo __FUNCTION__ . ":" . __LINE__ . "get curl result: $result\n";
            // logs::log(__FUNCTION__ . ":" . __LINE__ . "get curl result: $result\n");
            return $result;
        } else {
            echo __FUNCTION__ . ":" . __LINE__ . "WARN instanceDataJson is empty\n";
            logs::log(__FUNCTION__ . ":" . __LINE__ . "WARN instanceDataJson is empty\n");
        }
    } catch (Exception $ex) {
        logs::log($ex, 0);
        echo "Exception : " . $ex;
    }
}

function updateTicketEventsDetails($instanceData, $respData, $machineName, $sys_id, $teid)
{
    $timenow = time();

    $statuscode = 1; // $respData['result']['state'];

    $updTicEveRes = NanoDB::query('update  ' . $GLOBALS['PREFIX'] . 'event.ticketEvents set ticketId = ?, ccSentPayload = ?, '
        . 'ccResppayload = ?, ccStatusCode = ?, syncStatus = ?, crontime = ?, actiontime = ? where teid = ?', [
        $sys_id, json_encode($instanceData), json_encode($respData), $statuscode,
        2, $timenow, $timenow, $teid
    ]);

    if ($updTicEveRes) {
        logs::log('Ticket created successfully for Service Tag# ' . $machineName);
        echo 'Ticket created successfully for Service Tag# ' . $machineName . PHP_EOL;
    }
}



function initiateCrmProcess()
{
    echo "\n\n\n\n";
    echo 'Start in initiateCrmProcess.';

    $crmData = getCRMdetailsTcktcreation();

    if ($crmData) {

        foreach ($crmData as $value) {
            $instanceApiUrl = $value['crmUrl'];
            $crmUsername = $value['crmUsername'];
            $crmPassword = base64_decode($value['crmPassword']);
            $crmtype = $value['crmType'];
            $companyName = 'Nanoheal';
            $siteName = $value['siteName'];

            logs::log(__FUNCTION__ . ":" . __LINE__ . "siteName $siteName\n");
            echo __FUNCTION__ . ":" . __LINE__ . "siteName $siteName\n";

            push_Tickets($instanceApiUrl, $crmUsername, $crmPassword, $crmtype, $companyName, $siteName);
        }
    } else {
        logs::log('Nothing to Run');
        echo 'Nothing to Run';
    }
}

initiateCrmProcess();

echo "\n\n\n\n";
echo 'All work was done.';
