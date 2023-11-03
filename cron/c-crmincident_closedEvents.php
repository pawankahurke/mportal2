<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-dbConnect.php';
ini_set('max_execution_time', '240'); // 4 minutes

$cronConfig = new CronFunction();
if ($cronConfig->startPermission('closed_events', 'CLOSED EVENTS') != 'true') {
    echo 'no launch rights';
    exit();
}
$cronConfig->updateMonitoring('CLOSED EVENTS');

function push_Tickets($instanceApiUrl, $crmUsername, $crmPassword, $crmtype, $companyName, $siteName)
{
    $timeNow = time();
    do {
        $done = false;

        $EventRes = NanoDB::find_one('select * from ' . $GLOBALS['PREFIX'] . 'event.ticketEvents where siteName = ? and scrip in (286, 69) and status != "Closed" and ticketDescription not like "%log:Type of run: Scheduled%"  ORDER BY teid DESC limit 1', null, [$siteName]);

        if (!$EventRes) {
            return true;
        }

        $teid = $EventRes['teid'];

        preg_match('/Text:  .*:(.*)\n/', $EventRes['ticketDescription'], $seq);
        preg_match('/log:Type of run: (.*)/', $EventRes['ticketDescription'], $log);
        $seq = ['seqName' => $seq[1] ? $seq[1] : ''];
        $text2 = ['log' => $log[1] ? $log[1] : ''];

        $event = [
            'idx' => 0,
            'name' => $EventRes['ticketSub'],
            'scrip' => $EventRes['scrip'],
            'entered' => 0,
            'customer' => $EventRes['siteName'],
            'machine' => $EventRes['machineName'],
            'username' => $EventRes['username'],
            'clientversion' => '',
            'clientsize' => '',
            'priority' => $EventRes['priority'],
            'description' => $EventRes['ticketDescription'],
            'type' => '',
            'path' => '',
            'executable' => '',
            'version' => '',
            'size' => '',
            'id' => '',
            'windowtitle' => '',
            'string1' => '',
            'string2' => '',
            'text1' => json_encode($seq),
            'text2' => json_encode($text2),
            'text3' => '',
            'text4' => '',
            'ctime' => '',
            'servertime' => $EventRes['servertime'],
            'uuid' => '',
        ];

        $machineName = $event['machine'];

        $instanceData = [
            'Events' => [
                $event
            ]
        ];

        createSnowTickets($teid, $instanceData, $instanceApiUrl, $crmUsername, $crmPassword, $machineName, $siteName);

        $done = true;
        if (url::getToInt('maxLimit1')) {
            return true;
        }
    } while ($done);

    return true;
}

function createSnowTickets($teid, $instanceData, $instanceApiUrl, $crmUsername, $crmPassword, $machineName, $siteName)
{

    echo __FUNCTION__ . ":" . __LINE__ . " - instanceApiUrl $instanceApiUrl\n";
    // logs::log("instanceApiUrl $instanceApiUrl\n");

    $instanceResp = createTicketApi($instanceData, $instanceApiUrl, $crmUsername, $crmPassword);

    try {
        $respData = safe_json_decode($instanceResp, true);
        // logs::log(__FUNCTION__ , __LINE__ , '', [ $respData ]);
    } catch (Exception $e) {
        $search_string = strpos($instanceResp, "<h1>Your instance is hibernating</h1>");
        if ($search_string) {
            // NanoDB::query('update  ' . $GLOBALS['PREFIX'] . 'event.crmSnowConfigure set tcktcreation = ? where crmUrl = ?', ['disabled', $instanceApiUrl]);
            exit();
        }
    }

    if ($respData && isset($respData['result']) && isset($respData['result']['message'])) {
        $sys_id = preg_replace("#Closed Event Created.Incident Number is #", "", $respData['result']['message']);
        updateTicketEventsDetails($teid, $instanceData, $respData, $machineName, $sys_id, $siteName);
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
    // echo "\n\n".$instanceDataJson."\n\n";
    logs::log(__FILE__, __LINE__, 'sending to SN payload - ', [$instanceDataJson]);

    try {
        $URL = $instanceApiUrl . "/api/x_807428_nanoheal/nanoheal_integration/closedevents";
        $userpwd = "$crmUsername:$crmPassword";

        echo __FUNCTION__ . ":" . __LINE__ . " - URL:$URL ($userpwd)\n";

        if (!empty($instanceDataJson)) {
            echo __FUNCTION__ . ":" . __LINE__ . " - send curl\n";
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

            echo __FUNCTION__ . ":" . __LINE__ . " - get curl result: $result\n";
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

function updateTicketEventsDetails($teid, $instanceData, $respData, $machineName, $sys_id, $siteName)
{
    logs::log("updateTicketEventsDetails - ticketId=$sys_id, teid=$teid");
    $updatedtime = time();
    $date = date("m-d-Y h:i:s");
    $ticketclose = $date;

    $updateStatusSql = NanoDB::query("UPDATE IGNORE  " . $GLOBALS['PREFIX'] . "event.ticketEvents set status='Closed', crontime=?, ticketClose=?, ticketId=? WHERE teid=?", [$updatedtime, $ticketclose, $sys_id, $teid]);

    if ($updateStatusSql) {
        logs::log('Ticket closed successfully for Service Tag#' . $machineName . ' ticketID - ' . $sys_id);
        echo 'Ticket closed successfully for Service Tag#' . $machineName . ' ticketID - ' . $sys_id . PHP_EOL;
    } else {
        logs::log('ERROR: Ticket closed for Service Tag#' . $machineName . ' ticketID - ' . $sys_id);
        echo 'ERROR: Ticket closed for Service Tag#' . $machineName . ' ticketID - ' . $sys_id . PHP_EOL;
    }
}



function initiateCrmProcess()
{
    echo "\n\n\n\n";
    echo "Start in initiateCrmProcess.\n";

    $crmData = NanoDB::find_many('select * from  ' . $GLOBALS['PREFIX'] . 'event.crmSnowConfigure where tcktcreation = ?  ', null, ['enabled']);

    if ($crmData) {

        foreach ($crmData as $value) {
            $instanceApiUrl = $value['crmUrl'];
            $crmUsername = $value['crmUsername'];
            $crmPassword = base64_decode($value['crmPassword']);
            $crmtype = $value['crmType'];
            $companyName = 'Nanoheal';
            $siteName = $value['siteName'];

            logs::log(__FUNCTION__ . ":" . __LINE__ . " - siteName $siteName\n");
            echo __FUNCTION__ . ":" . __LINE__ . " - siteName $siteName\n";

            echo "\n ----- closedevents -----\n";
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
