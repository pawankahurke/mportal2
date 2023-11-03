<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-profileAPI.php';
include_once '../lib/l-elastic.php';
include_once '../include/NH-Config_API.php';
include_once '../include/common_functions.php';
include_once '../communication/common_communication.php';

nhRole::dieIfnoRoles(['darts']);

//Replace $routes['post'] with if else
if (url::postToText('function') === 'get_dart_JsonSchema') { //roles: darts
    get_dart_JsonSchema();
} else if (url::postToText('function') === 'submit_JsonSchema') { //roles: darts
    submit_JsonSchema();
} else if (url::postToText('function') === 'get_seq_JsonSchema') { //roles: darts
    get_seq_JsonSchema();
} else if (url::postToText('function') === 'submit_JsonSchemaseq') { //roles: darts
    submit_JsonSchemaseq();
}

function get_dart_JsonSchema()
{
    $db = NanoDB::connect();
    $dartno = url::requestToText('dartno');

    $Sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "profile.WizardMasterRole");
    $Sql->execute();
    $sqlRes = $Sql->fetch();
    $size = safe_count($sqlRes);
    $resObj = array();
    if ($size == 0) {
        $resObj["error"] = "norecords";
    } else {
        $dartVar = $sqlRes["role_access_level"];

        $dartjsonObj = safe_json_decode($dartVar, true);
        $dartObj = $dartjsonObj["darts"][0][$dartno][0];
        
        $varids = array();
        if(!empty($dartObj)){
            foreach ($dartObj as $key => $value) {
                if ($value != 0) {
                    array_push($varids, $key);
                }
            }
        }

        $varlist = "'" . implode("','", $varids) . "'";
        $mainschemaObj = getVarJson($varlist, $dartObj);
        echo json_encode($mainschemaObj, JSON_PRETTY_PRINT);
    }
}

// function getVarJsonDataValue($jid)
// {
//     $db = NanoDB::connect();
//     $scope = $_SESSION["searchType"];
//     $scopeval = $_SESSION["searchValue"];
//     $Sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "profile.varidJsonData jd where jd.jid=? and Scope=? and ScopeVal=?");
//     $Sql->execute([$jid, $scope, $scopeval]);
//     $varidDataRes = $Sql->fetch();
//     $resObj = array();

//     if (!$varidDataRes) {
//         $resObj["error"] = "norecords";
//         return $resObj;
//     } else {
//         return $varidDataRes;
//     }
// }

function checkall()
{
    $db = NanoDB::connect();
    $varScopeArr = array();

    $dartno = url::requestToText('dartno');
    $mgroupuniq = (string) getmgroupuniqId();
    $var_id = "S00060TargetStr";

    $Sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.MachineGroups");
    $Sql->execute();
    $varidschemaRes = $Sql->fetchAll();

    foreach ($varidschemaRes as $key => $value) {
        extract($value);
        $dartno = url::requestToText('dartno');

        array_push($varScopeArr, array('name' => $var_id, 'dart' => (int) $dartno, 'group' => $mgroupuniq));
    }
    $result = safe_json_decode(GET_EXISTING_DATA_CURL_CALL($varScopeArr));
    print_r($result);
}

function getVarJson($varids, $varid_roleObj)
{
    logs::log(__FILE__, __LINE__, ["varids" => $varids, "varid_roleObj" => $varid_roleObj]);
    $varids = str_replace("'", "", $varids);

    $db = NanoDB::connect();
    $arr = explode(",", $varids);
    $in = str_repeat('?,', safe_count($arr) - 1) . '?';
    $dartno = url::requestToInt('dartno');
    $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "profile.varidJsonSchema v where v.var_id in ($in) and v.dartno=$dartno order by id desc";

    logs::log(__FILE__, __LINE__, ["sql" => $sql, "var" => $arr]);
    $Sql = $db->prepare($sql);
    $Sql->execute($arr);

    $varidschemaRes = $Sql->fetchAll();

    $size = safe_count($varidschemaRes);
    $resObj = array();
    $varschemaObj = array();
    $existingDataObj = array();
    if ($size == 0) {
        $resObj["error"] = "norecords";
    } else {

        $varScopeArr = array();
        $dartno = url::requestToText('dartno');
        $mgroupuniq = (string) getmgroupuniqId();
        foreach ($varidschemaRes as $key => $value) {
            extract($value);
            // $varidJsonDataObj = getVarJsonDataValue($id);
            array_push($varScopeArr, array('name' => $var_id, 'dart' => (int) $dartno, 'mgroupuniq' => $mgroupuniq));
            $existinglist = getExistingVarValue($var_id, $jsonschema, $parser);

            // logs::log("getExistingVarValue: res", $existinglist);

            if (safe_json_decode($existinglist)) {
                $existingDataObj[$var_id] = safe_json_decode($existinglist);
            } else {
                if (!safe_count($existinglist)) {
                    $initdata = safe_json_decode($initial_data, true);

                    if (!safe_count($initdata)) {
                        if($existinglist === "0" || $existinglist === 0){
                            $existingDataObj[$var_id] = 0; //to display 0 in input box(type=number) of dart's configuration side nav, if the value is stored as 0 in DB 
                        }else{
                            $existingDataObj[$var_id] = null;
                        }
                    } else {
                        $existingDataObj[$var_id] = safe_json_decode(json_encode($initial_data), true);
                    }
                } else {
                    $existingDataObj[$var_id] = $existinglist;
                }
            }

            $varschemaObj[$var_id] = safe_json_decode($jsonschema);
            if ($varid_roleObj[$var_id] == "1") {
                if (isset($varschemaObj[$var_id]->properties)) {
                    $propObj = $varschemaObj[$var_id]->properties;
                    foreach ($propObj as $subkey => $subvalue) {
                        $subvalue->readOnly = true;
                    }
                } else {
                    $varschemaObj[$var_id]->readOnly = true;
                }
            }
        }
    }

    $finalObj = array();
    $mainschemaObj = array();
    $mainschemaObj["\$schema"] = "http://json-schema.org/draft-03/schema#";
    $mainschemaObj["type"] = "object";
    $mainschemaObj["properties"] = $varschemaObj;

    $finalObj["schemadata"] = $mainschemaObj;
    $finalObj["valuedata"] = $existingDataObj;

    return $finalObj;
}

function validateSchemaParser()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $postdata = safe_json_decode(file_get_contents('php://input'), true);
        if (!empty($postdata)) {
            $schema = $postdata["schema"];
            $parser = $postdata["parser"];

            $jsondata = str_replace('\\"', '"', $schema);
            $schemaObj = safe_json_decode($jsondata);

            $parserObj = safe_json_decode($parser);
            foreach ($parserObj as $key => $value) {
                $schemajson = json_encode($schemaObj->$key);
                $mainValueList = validateParser($schemajson, $parser);
                echo json_encode(array("response" => $mainValueList));
            }
        }
    }
}

function validateParser($jsondata, $parser)
{

    $jsondata = str_replace('\\"', '"', $jsondata);

    $varidObj = safe_json_decode($jsondata);

    $parserObj = safe_json_decode($parser, true);

    $mainValueArr = array();
    $newlinestr = "\n";

    $subobjArr = array();
    $delimiter = "";
    foreach ($varidObj as $key => $value) {

        foreach ($parserObj as $pkey => $pjsons) {
            $delimiter = $pjsons["delimiter"];
            $ismultiline = $pjsons["multiline"];
        }
        $subarray = array_values((array) $value);
        $subarray_str = implode($delimiter, $subarray);

        if ($ismultiline) {
            array_push($subobjArr, $subarray_str . $newlinestr);
        } else {
            array_push($subobjArr, $subarray_str);
        }
    }
    $mainValueList = implode('', $subobjArr);

    return $mainValueList;
}

function joinJsonVarValues($jsondata, $parser, $id)
{

    $mainValueList = validateParser($jsondata, $parser);
    $mainValueList = rtrim($mainValueList, "\n");
    return json_encode($mainValueList);
}

function writelog($message)
{
    logs::log(__FILE__, __LINE__, $message, ['tag' => "schemaWizFunc"]);
}

function submit_JsonSchema($data = null, $dartno = null, $scope = null, $scopeval = null)
{
    if ($_SERVER['REQUEST_METHOD'] != 'POST') {
        return;
    }

    $reqFromProfiles = $data != null;
    $db = NanoDB::connect();

    $precedence = url::requestToText('precedence');
    $loggedUser = $_SESSION['user']['logged_username'];

    $headers = getallheaders();

    $data = $data ?: url::requestToAny('data');
    $dartno = $dartno ?: url::requestToInt('dartno');
    if ($headers['Accept'] == 'application/json'){
      $scope = url::requestToText('searchType');
      $scopeval = url::requestToText('searchValue');
      if ($scope == 'Sites'){
        $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] ."core.Census where site = ?");
        $sql->execute([$scopeval]);
        $countMachineSite = $sql->fetch();
        if (safe_count($countMachineSite) < 1){
          $result['status'] = "failed";
          $result['msg'] = "The selected site does not contain linked machines. The varvalues data has not been updated";
          echo json_encode($result);
          exit();
        }
      }
    }else{
      $searchType = isset($_SESSION["searchType"]) ? $_SESSION["searchType"] : '';  
      $scope = $scope ?: $searchType;

      $searchValue = isset($_SESSION["searchValue"]) ? $_SESSION["searchValue"] : '';  
      $scopeval = $scopeval ?: $searchValue;
    }
    $postdata = safe_json_decode($data, true);
   if (!is_array($postdata)) {
     $postdata = json_decode($postdata);
   }

    $fieldError = 0;
    foreach ($postdata as $key => $value) {
      $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "profile.varidJsonSchema v where v.var_id=? limit 1");
      $sql->execute([$key]);
      $varidschemaRes = $sql->fetch();

      $jsonschema = json_decode($varidschemaRes['jsonschema']);
      $varType = gettype($value);
      if ($jsonschema->type =! $varType){
        $fieldError = 1;
        $errorMassage = "Fields '".$key."' must be ".$jsonschema->type.".You have sent an ".$varType;
        break;
      }
      $size = safe_count($varidschemaRes);
      if ($size == 0){
        $fieldError = 1;
        $errorMassage = "Fields '".$key."' not found";
        break;
      }
    }

    if ($fieldError == 1){
      $result['status'] = "failed";
      $result['msg'] = $errorMassage;
      echo json_encode($result);
      exit();
    }

    $prepareVarClientValue = array();
    if (empty($postdata)) {
        writelog("No value found postdata");
    } else {
        foreach ($postdata as $key => $value) {
            $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "profile.varidJsonSchema v where v.var_id=? limit 1");
            $sql->execute([$key]);
            $varidschemaRes = $sql->fetch();

            $size = safe_count($varidschemaRes);

            $resObj = array();

            if ($size == 0) {
                $resObj["error"] = "norecords";
            } else {
              if(gettype($value) == 'string'){
                $value = str_replace(PHP_EOL, '#$#$#$#$', $value);
              }
                $jsondatavalue = json_encode($value);

                $jsondatavalue = str_replace('"', '\"', $jsondatavalue);
                $jsondatavalue = str_replace("'", "\'", $jsondatavalue);
                if ($jsondatavalue == "false") {
                    $res1 = str_replace('true', 'false', $jsondatavalue);
                } else {
                    $res1 = str_replace('false', 'true', $jsondatavalue);
                }
                if (checkExists($varidschemaRes["id"])) {
                    $sql = $db->prepare("UPDATE " . $GLOBALS['PREFIX'] . "profile.varidJsonData SET jsondata=?,dartNo=? where Scope=? and ScopeVal=? and jid=?");
                    $sql->execute([$res1, $dartno, $scope, $scopeval, $varidschemaRes["id"]]);
                } else {
                    $sql = $db->prepare("REPLACE INTO " . $GLOBALS['PREFIX'] . "profile.varidJsonData SET Scope=? , ScopeVal=? , jid=?,jsondata=?,dartNo=?");
                    $sql->execute([$scope, $scopeval, $varidschemaRes["id"], $res1, $dartno]);
                }

                $parseobjArr = array();
                $parseobjArr["name"] = (string) $key;
                $parseobjArr["dart"] = (int) $dartno;
                $parseobjArr["group"] = (string) getmgroupuniqId($scope,$scopeval); // here

                $parseobjArr['loggeduser'] = $loggedUser;
                $parseobjArr['precedence'] = $precedence;

                if ($varidschemaRes["parser"] == 'null' || $varidschemaRes["parser"] === "" || $varidschemaRes["parser"] === "(NULL)") {
                    if ($res1 == "true") {
                        $res1 = "1";
                    }
                    if ($dartno == 100 || $dartno == 69 || $dartno == 192 || $dartno == 304 || $dartno == 306 || $dartno == 288 || $dartno == 190 || $dartno == 60 || $dartno == 89 || $dartno == 270 || $dartno == 267) {
                        $res1 = str_replace('\"', '"', $res1);
                        $res1 = ltrim($res1, '"');
                        $res1 = rtrim($res1, '"');
                        $res1 = str_replace("\\r", "", $res1);
                    } else {
                        $res1 = str_replace('\"', '', $res1);
                        $res1 = str_replace("\\r", "", $res1);
                    }
                    $parseobjArr["value"] = $res1;
                } else {

                    $val = joinJsonVarValues($res1, $varidschemaRes["parser"], $varidschemaRes["id"]);

                    $val = str_replace("\\\\", "\\", $val);
                    $val = str_replace("\\\\n", "\\n", $val);
                    $val = str_replace("\\r", "", $val);

//                    $parseobjArr["value"] = safe_json_decode($val);
                    $parseobjArr["value"] = $val;
                }
                array_push($prepareVarClientValue, $parseobjArr);
            }
        }
    }
    $arraymake = $prepareVarClientValue;
    $status = MAKE_CURL_CALL($arraymake, "post");
    if($precedence == 'false'){
       $precedenceResult = NH_Config_API_PUT($arraymake,$scopeval);
    }
    $result = [];
    if($reqFromProfiles){
        return;
    }
    if (stripos(trim($status), 'Success') !== false) {
        $result['msg'] = $status;
        $action = 'Execute - DART' . $dartno;
        $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : null;
        create_auditLog('Services', $action, 'Success', $_REQUEST, $gpname);

        $result['status'] = "success";
        echo json_encode($result);
    } else {
        $result['msg'] = $status;
        $action = 'Execute - DART' . $dartno;
        $gpname = isset($_SESSION['searchValue']) ? trim($_SESSION['searchValue']) . " - " . trim($_SESSION['rparentName']) : null;
        create_auditLog('Services', $action, 'Failed', $_REQUEST, $gpname);

        $result['status'] = "failed";
        echo json_encode($result);
    }
}

function checkExists($id)
{
    $scope = isset($_SESSION["searchType"]) ? $_SESSION["searchType"] : '';  
    $scopeval = isset($_SESSION["searchValue"]) ? $_SESSION["searchValue"] : '';  

    $db = NanoDB::connect();
    $sqlcheck = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "profile.varidJsonData v where v.jid=? and v.Scope=? and v.ScopeVal=? limit 1");
    $sqlcheck->execute([$id, $scope, $scopeval]);
    //$SqlRes = $sqlcheck->fetch();
    if($sqlcheck->rowCount() > 0){
        //$size = $SqlRes['id'];
        return true;
    }else{
        return false;
    }

    // if (!$size) {
    //     return false;
    // } else {
    //     return true;
    // }
}

function getExistingVarValue($varname, $schema, $parser)
{
    $db = NanoDB::connect();
    $dartno = url::requestToInt('dartno');
    $mgroupuniq = (string) getmgroupuniqId();
    
    $searchType = (isset($_SESSION['searchType'])) ? $_SESSION['searchType'] : '';
    $rparentName = (isset($_SESSION['rparentName'])) ? $_SESSION['rparentName'] : '';

    logs::log("getExistingVarValue: args", [$varname, $schema, $parser]);

    $varScopeArr = array();
    array_push($varScopeArr, array('name' => $varname, 'dart' => (int) $dartno, 'group' => $mgroupuniq));

    $r1 = GET_EXISTING_DATA_CURL_CALL($varScopeArr);
    $result = safe_json_decode($r1, true);

    // logs::log("getExistingVarValue: result", [$r1, $result]);


    if (empty($result) && $searchType == 'ServiceTag') {

        $sqlcheck = $db->prepare("SELECT mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ? limit 1");
        $sqlcheck->execute([$rparentName]);
        $SqlRes = $sqlcheck->fetch();

        $Mgroupuniq = $SqlRes['mgroupuniq'];
        $ScopeArr = array();
        array_push($ScopeArr, array('name' => $varname, 'dart' => (int) $dartno, 'group' => $Mgroupuniq));
        $Result = safe_json_decode(GET_EXISTING_DATA_CURL_CALL($ScopeArr), true);
        // logs::log("getExistingVarValue: Result", $result);
        $result = $Result;
    }

    $varvalue = '';
    if(isset($result[0]['value']) && $result[0]['value'] !== null){
        $varvalue = stripslashes($result[0]['value']);
        $varvalue = str_replace('\"', '"', $varvalue);
    }

    $schparser = safe_json_decode($parser);
    $jsonschema = safe_json_decode($schema);
    $mainDataValList = array();

    if (($jsonschema->type) == "object") {
        if (isset($jsonschema->properties)) {
            $i = 0;
            foreach ($jsonschema->properties as $jkey => $jvalue) {
                $valuefound = "";
                $explodeArr = explode("\n", $varvalue);
                foreach ($explodeArr as $key => $value) {
                    if ($i == $key) {
                        $valuefound = $value;
                        break;
                    }
                }

                if (($jvalue->type) == "string") {
                    $mainDataValList[$jkey] = $valuefound;
                } else if (($jvalue->type) == "integer") {
                    $mainDataValList[$jkey] = intval($valuefound);
                } else if (($jvalue->type) == "array") {

                    $sKeyArr = array();
                    foreach ($jvalue->items->properties as $sjkey => $sjvalue) {
                        array_push($sKeyArr, $sjkey);
                    }

                    $cnt = 0;
                    $totalCnt = count((array) $jvalue->items->properties);
                    $sepValLinelist = array();

                    $parserValueArr = explode(PHP_EOL, $varvalue);
                    foreach ($parserValueArr as $plines) {
                        $sublimitercnt = substr_count($plines, $schparser->$jkey->delimiter);
                        $sublimitercnt = $sublimitercnt + 1;
                        if ($totalCnt == $sublimitercnt) {
                            array_push($sepValLinelist, $plines);
                        }
                    }

                    $sublist = array();

                    foreach ($sepValLinelist as $sval) {
                        $expVal = explode($schparser->$jkey->delimiter, $sval);
                        $eachlineArr = array();

                        foreach ($expVal as $sdata) {
                            array_push($eachlineArr, $sdata);
                        }

                        $combinedArr = array_combine($sKeyArr, $eachlineArr);
                        array_push($sublist, $combinedArr);
                    }
                    $mainDataValList[$jkey] = $sublist;
                }
                $i++;
            }
        }

        return json_encode($mainDataValList);
    }
    if (($jsonschema->type) == "array") {

        $jarry = [];
        $newarr = [];

        if ($jsonschema->items->type == "object") {
            $sKeyArr = array();
            $i = 0;
            $splitvalue = explode(PHP_EOL, $varvalue);

            if (safe_count($splitvalue) > 1) {
                foreach ($splitvalue as $key => $value2) {
                    $i = 0;
                    $myarr = array();
                    $splitvalue = explode(",", $value2);
                    $val = $splitvalue[$i];

                    if ($val) {
                        foreach ($jsonschema->items->properties as $sjkey => $sjvalue) {
                            if ($jsonschema->items->properties->$sjkey->type == "integer") {
                                $myarr[$sjkey] = intval($splitvalue[$i]);
                            } else {
                                $myarr[$sjkey] = strval($splitvalue[$i]);
                            }
                            $i++;
                        }
                        $newarr[$key] = $myarr;
                    }
                }
            } else {
                foreach ($jsonschema->items->properties as $sjkey => $sjvalue) {

                    $splitvalue = explode(",", $varvalue);
                    $val = $splitvalue[$i];
                    if ($val) {
                        $newarr[$i] = array($sjkey => $splitvalue[$i]);
                    }
                }
            }
            $i++;

            return ($newarr);
        }
    }
    if (($jsonschema->type) == "integer") {
        return intval($varvalue);
    }
    if (($jsonschema->format) == "togglebutton") {

        if (($jsonschema->trueValue) == $varvalue) {
            $varvalue = '1';
        } else if (($jsonschema->falseValue) == $varvalue) {
            $varvalue = '0';
        } else {
            $varvalue = $jsonschema->falseValue;
        }
        return $varvalue;
    } else if (($jsonschema->format) == "button") {
        $varvalue = '0';
        return $varvalue;
    } else {
        return strval($varvalue);
    }
}

function GET_EXISTING_DATA_CURL_CALL($variablesarray, $method = "get")
{
    logs::log("GET_EXISTING_DATA_CURL_CALL", [$variablesarray, $method]);
    if ($method === "get") {
        $res = NH_Config_API_GET($variablesarray);
    } else {
        $res = NH_Config_API_POST($variablesarray);
    }
    return $res;
}

function MAKE_CURL_CALL($variablesarray, $method = "get")
{
    return GET_EXISTING_DATA_CURL_CALL($variablesarray, $method);
}


function getmgroupuniqId($searchType = null, $searchValue = null, $rparentValue = null)
{
    return MachineGroups::getmgroupuniqId($searchType, $searchValue, $rparentValue);
}

function getMacGroupCatId($site)
{
    $db = NanoDB::connect();
    $scopeval = (isset($_SESSION["searchValue"])) ? $_SESSION["searchValue"] : '';
    $selectedview = isset($_SESSION["searchType"]) ? $_SESSION["searchType"] : '';  
    
    if($scopeval !== null){
        $scopeval = trim($scopeval);
    }else{
        $scopeval = '';
    }
    if ($selectedview == 'Site' || $selectedview == 'Sites') {
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
        $sql->execute([$scopeval]);
    } else if ($selectedview == 'Group' || $selectedview == 'Groups') {
        $sql = $db->prepare("SELECT mgroupid,mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where mgroupid=?");
        $sql->execute([$scopeval]);
    }
    $sqlArr = $sql->fetch();
    $data = $sqlArr['mgroupid'] . '-' . $sqlArr['mgroupuniq'];
    return $data;
}

function getMachineGroupCatId()
{
    $db = NanoDB::connect();

    $hostname = $_SESSION["searchValue"];
    $parentscope = $_SESSION["rparentName"];
    $parentscope = trim($parentscope);
    $hostname = trim($hostname);

    $sql = $db->prepare("SELECT mg.mgroupid,mc.mcatid,mg.mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups mg
            inner join " . $GLOBALS['PREFIX'] . "core.MachineCategories mc
            on mg.mcatuniq=mc.mcatuniq where mg.name=?");
    $sql->execute([$parentscope . ':' . $hostname]);
    $sqlArr = $sql->fetch();

    $data = $sqlArr['mgroupid'] . '-' . $sqlArr['mgroupuniq'] . '-' . $sqlArr['mcatid'];
    return $data;
}

function getClientVersion($mcatid, $mgroupid)
{
    $db = NanoDB::connect();

    $sql = $db->prepare("SELECT distinct vers from " . $GLOBALS['PREFIX'] . "core.Revisions
            where censusid in (SELECT id as censusid from " . $GLOBALS['PREFIX'] . "core.MachineGroupMap
            left join " . $GLOBALS['PREFIX'] . "core.MachineGroups on (
            " . $GLOBALS['PREFIX'] . "core.MachineGroupMap.mgroupuniq=" . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupuniq)
            left join " . $GLOBALS['PREFIX'] . "core.MachineCategories on (" . $GLOBALS['PREFIX'] . "core.MachineGroups.mcatuniq=" . $GLOBALS['PREFIX'] . "core.MachineCategories.mcatuniq)
            left join " . $GLOBALS['PREFIX'] . "core.Census on (" . $GLOBALS['PREFIX'] . "core.MachineGroupMap.censusuniq=" . $GLOBALS['PREFIX'] . "core.Census.censusuniq)
            where " . $GLOBALS['PREFIX'] . "core.MachineGroups.mgroupid =?
            and " . $GLOBALS['PREFIX'] . "core.MachineCategories.mcatid =?
            and id IS NOT NULL)
            order by vers desc;");
    $sql->execute([$mgroupid, $mcatid]);
    $result = $sql->fetch();

    return $result['vers'];
}

function get_seq_JsonSchema()
{
    $db = NanoDB::connect();

    $dartno = url::requestToText('sequence');

    $Sql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "profile.varidJsonSchema where dartno = ?");
    $Sql->execute([$dartno]);
    $sqlRes = $Sql->fetchAll();
    $newArray = array();
    foreach ($sqlRes as $key => $val) {

        $var_id = $val['var_id'];
        $var_id .= "_Val";
        $schema = $val['jsonschema'];
        $dartjsonObj = safe_json_decode($schema, true);
        $newArray[$var_id] = $dartjsonObj;
    }
    $mainschemaObj = getVarJsonseq($newArray);
    echo json_encode($mainschemaObj, JSON_PRETTY_PRINT);
    exit;
}

function getVarJsonseq($varids)
{
    $finalObj = array();
    $mainschemaObj = array();
    $mainschemaObj["\$schema"] = "http://json-schema.org/draft-03/schema#";
    $mainschemaObj["type"] = "object";
    $mainschemaObj["properties"] = $varids;
    $finalObj["schemadata"] = $mainschemaObj;
    $finalObj["valuedata"] = null;
    return $finalObj;
}

function submit_JsonSchemaseq()
{

    $dart = url::requestToText('sequence');
    $inputdata = url::requestToText('data');
    $dynamicVariables = url::requestToText('dynamicConfig');
    $DartName = url::requestToText('name');
    $Res = getmgroupuniqueid();

    $api_response = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arr_response = safe_json_decode(json_encode($api_response), true);
    $array = $arr_response["Main"];
    $main_arr = $arr_response["dynamiConfig"];
    $res = explode('],', $main_arr);
    $count = safe_count($res);
    $newArray = array();
    foreach ($res as $key => $value) {
        if (($key + 1) == $count) {
            $newdata = $res[$key];
            $res1 = explode('[{', $newdata);
            $newdata1 = '[{' . $res1[1];
            $data1 = safe_json_decode($newdata1, true);
            $valData = $data1[0]['tmp'];
            if (strpos($valData, $DartName) !== false) {
                array_push($newArray, $valData);
            }
        } else {
            $newdata = $res[$key] . ']';
            $res1 = explode('[{', $newdata);
            $newdata1 = '[{' . $res1[1];
            $data1 = safe_json_decode($newdata1, true);
            $valData = $data1[0]['tmp'];
            if (strpos($valData, $DartName) !== false) {
                array_push($newArray, $valData);
            }
        }
    }
    foreach ($newArray as $key => $val) {
        $newVal = explode(',', $val);
        $finalVal = implode(PHP_EOL, $newVal);
    }

    foreach ($inputdata as $key => $value) {
        $result = str_replace($key, $value, $finalVal);
    }

    echo $result;
}

function getmgroupuniqueid()
{
    $db = NanoDB::connect();
    $searchType = $_SESSION['searchType'];
    $searchValue = $_SESSION['searchValue'];
    $rparentValue = $_SESSION['rparentName'];
    $pass = $_SESSION['passlevel'];
    if ($searchType == 'Sites') {

        $sql = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
        $sql->execute([$searchValue]);
        $sqlRes = $sql->fetch();

        $mgroupid = $sqlRes['mgroupuniq'];
        $mgroupidParent = $sqlRes['mgroupuniq'];
    } else if ($searchType == 'Groups') {

        $sql = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
        $sql->execute([$rparentValue]);
        $sqlRes = $sql->fetch();

        $mgroupid = $sqlRes['mgroupuniq'];
        $mgroupidParent = $sqlRes['mgroupuniq'];
    } else {
        if ($pass == 'Groups' || $pass == '') {
            $sql = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name like ? order by mgroupid desc limit 1");
            $sql->execute(["%$searchValue%"]);
            $sqlRes = $sql->fetch();

            $sqltest = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Census where host = ? order by last desc limit 1");
            $sqltest->execute([$searchValue]);
            $sqlRestest = $sqltest->fetch();
            $value = $sqlRestest['site'];

            $sql1 = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
            $sql1->execute([$value]);
            $sql1Res = $sql1->fetch();
            $mgroupid = $sqlRes['mgroupuniq'];
            $mgroupidParent = $sql1Res['mgroupuniq'];
        } else {
            $sql = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name like ? order by mgroupid desc limit 1");
            $sql->execute(["%$searchValue%"]);
            $sqlRes = $sql->fetch();

            $sql1 = $db->prepare("select mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
            $sql1->execute([$rparentValue]);
            $sql1Res = $sql1->fetch();

            $mgroupid = $sqlRes['mgroupuniq'];
            $mgroupidParent = $sql1Res['mgroupuniq'];
        }
    }

    return array("mgroupuniq" => $mgroupid, "parentmgroupid" => $mgroupidParent);
}
