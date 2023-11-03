<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
require_once '../include/common_functions.php';

$permission = checkModulePrivilege('alertnotification', 2);
if (!$permission) exit(json_encode(array('status' => false, 'message' => 'Permission denied')));


global $elast_alert;
global $elast_path;

if (!isset($_SESSION)) {
}

$path = $elast_path;

if (isset($_GET) && (url::getToText('action') === 'create' || url::getToText('action') === 'update')) {

    if (!isset($_SESSION['user']['username']) || !isset($_SESSION['user']['userid'])) {
        $return = array('status' => false, 'message' => 'loggedout');
        echo json_encode($return);
        return;
    }

    if (!url::issetInPost('yamlname') || url::isEmptyInPost('yamlname')) {
        $return = array('status' => false, 'message' => 'please mention a name for the yaml configuration file');
        echo json_encode($return);
        return;
    }

    if (!preg_match('/^[a-zA-Z0-9\_ ]+$/', url::postToAny('yamlname'))) {
        $return = array('status' => false, 'message' => 'The configuration name should contain alpha numeric characters, space or underscores');
        exit(json_encode($return));
    }

    if (!url::issetInPost('nhtype')) {
        $return = array('status' => false, 'message' => 'The type field is required.<br />Please make sure your role has type compliance or type notification enabled');
        exit(json_encode($return));
    }

    $userId = $_SESSION['user']['userid'];
    $yamlName = url::issetInPost('yamlname') ? url::postToText('yamlname') : '';
    $cornMinute = url::postToText('cornminute');
    $cornHour = url::postToText('cornhour');
    $cornDays = url::postToText('corndays');
    $cornWeekly = url::postToText('cornweekly');
    $cornMonth = url::postToText('cornmonth');
    $numberOfEvens = url::postToText('number-of-events');
    $timeFrameType = url::postToText('time-frame-type');
    $timeFrameValue = url::postToText('time-frame-value');
    $queryString = url::postToText('query-string');
    $complaianceCategory = url::postToText('compliance-category');
    $complaianceItem = url::postToText('compliance-item');
    $complianceName = url::postToText('compliance-name');
    $complianceId = url::postToText('compliance-id');
    $scope = url::issetInPost('scope') ? url::postToText('scope') : '';
    $nhtype = url::postToText('nhtype');
    $indexName = url::postToText('index-name');
    $username = $_SESSION['user']['username'];
    $channelId = $_SESSION['user']['cd_eid'];
    $currentTimestamp = date('Y-m-d H:i:s');

    $praecoQuery = '';

    if (isset($queryString) && !empty($queryString)) {
        $queryStringArray = $stringArray = preg_split("/AND/i", $queryString);
        $rulesArray = [];
        foreach ($queryStringArray as $eachPatterns) {
            $eachPatterns = trim($eachPatterns);
            if (strpos($eachPatterns, ":")) {
                $eachPatternsArray = explode(":", $eachPatterns);
                $ruleName = trim($eachPatternsArray[0]);
                $ruleValue = trim($eachPatternsArray[1]);
            } else {
                $ruleName = $eachPatterns;
                $ruleValue = '';
            }

            if (isset($ruleName)) {
                $rulesArray[] = '{"type": "query-builder-rule","query": {"rule": "' . $ruleName . '","selectedOperator": "contains","selectedOperand": "' . $ruleName . '","value": "' . $ruleValue . '"}}';
            }
        }

        $praecoQuery = implode(",", $rulesArray);
    }

    $yamlString = <<<STAT
#__praeco_query_builder: '{"query":{"logicalOperator":"all","children":[]}}'
_praeco_query_builder: '{"query":{"logicalOperator":"all","children":[$praecoQuery]}}'
aggregation:
  schedule: '$cornMinute $cornHour $cornDays $cornWeekly $cornMonth'
aggregation_key: machine
alert:
  - post
doc_type: events
filter:
  - query:
      query_string:
        query: '$queryString'
http_post_static_payload:
  "ComplianceCategory": "$complaianceCategory"
  "ComplianceItem": "$complaianceItem"
  "ComplianceName": "$complianceName"
  "ComplianceID": "$complianceId"
  "ComplianceType": "$scope"
  "username": "$username"
  "agent": "default"
  "solution": "default"
  "reset": "0"
STAT;

    if ($nhtype == 'Notification') {
        $yamlString .= "\n";
        $yamlString .= <<<STAT
  "NotificationName": "$yamlName"
  "Status" : "default"
  "actionNote" : "default"
STAT;
    }
    $yamlString .= "\n";
    $yamlString .= <<<STAT
  "nhtype" : "$nhtype"
http_post_url: '$elast_alert'
import: BaseRule.config

add_metadata_alert: true
index: $indexName
is_enabled: true
name: $yamlName
num_events: $numberOfEvens
timeframe:
  $timeFrameType: $timeFrameValue
timestamp_field: '@timestamp'
timestamp_type: iso
type: frequency
username: $username
channel_id: $channelId
agent: "default"
solution: "default"
STAT;


    $db = pdo_connect();
    $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "dashboard.yaml_configurations WHERE file_name=?");
    $sql->execute([htmlentities($_POST['yamlname']) . '.yaml']);
    $isExist = $sql->fetch(PDO::FETCH_ASSOC);
    $scope = (isset($scope) && $scope == 'global') ? '1' : '0';
    $target = $path . "/" . $yamlName . '.yaml';
    if (url::getToText('action') == 'update' && file_exists($target) && $isExist) unlink($target);


    if (url::getToText('action') == 'create' && $isExist) {
        $return = array('status' => false, 'message' => 'A configuration already exist with this name,<br />please enter a different name');
        exit(json_encode($return));
    }

    $fp = fopen($target, "wb+");
    fwrite($fp, $yamlString);
    fclose($fp);



    if (!$isExist) {
        try {

            $sql = "INSERT INTO `" . $GLOBALS['PREFIX'] . "dashboard`.`yaml_configurations` ( `user_id`, `file_name`, `scope` ,`configuration`, `created_at`, `updated_at`) VALUES (? , ?, ?, ?, ?, ?)";
            $bindings = array($userId, htmlentities($yamlName) . '.yaml', $scope, json_encode($_POST), $currentTimestamp, $currentTimestamp);
            $pdo = $db->prepare($sql);
            $pdo->execute($bindings);
        } catch (Exception $e) {
            logs::log(__FILE__, __LINE__, $e, 0);
            unlink($target);
            $return = array('status' => false, 'message' => 'Something went wrong');
            echo json_encode($return);
            return;
        }
    } else {
        try {
            $sql = "UPDATE `" . $GLOBALS['PREFIX'] . "dashboard`.`yaml_configurations` SET modified_by=?,scope=?,configuration=?,updated_at=? WHERE file_name=?";
            $bindings = array($userId, $scope, json_encode($_POST), $currentTimestamp, htmlentities($yamlName) . '.yaml');
            $pdo = $db->prepare($sql);
            $pdo->execute($bindings);
        } catch (Exception $e) {
            logs::log(__FILE__, __LINE__, $e, 0);
            $return = array('status' => false, 'message' => 'Something went wrong');
            echo json_encode($return);
            return;
        }
    }

    $return = array('status' => true);

    echo json_encode($return);
}
