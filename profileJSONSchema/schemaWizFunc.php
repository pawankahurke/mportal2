<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-profileAPI.php';
include_once '../lib/l-elastic.php';
include_once '../include/NH-Config_API.php';
include_once '../communication/common_communication.php';

nhRole::dieIfnoRoles(['profilewizard']); //roles: profilewizard
$db = pdo_connect();
$function = '';
if (url::issetInRequest('function')) { //roles: profilewizard
    $function = url::requestToText('function'); //roles: profilewizard
    $function();
}

$result = '';

function get_dartJsonSchema()
{
    $db = pdo_connect();
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
        foreach ($dartObj as $key => $value) {
            if ($value != 0) {
                array_push($varids, $key);
            }
        }

        $varlist = "'" . implode("','", $varids) . "'";
        $mainschemaObj = getVarJson($varlist, $dartObj);
        echo json_encode($mainschemaObj, JSON_PRETTY_PRINT);
    }
}

// function getVarJsonDataValue($jid)
// {
//     $db = pdo_connect();
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
    $db = pdo_connect();
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
    $dartno = url::requestToText('dartno');
    $dartTileToken = url::requestToText('dartTileToken');
    $dartindx = url::requestToText('dartindx');
    $dartseqn = url::requestToText('dartseqn');
    $varids = str_replace("'", "", $varids);

    $db = pdo_connect();
    $arr = explode(",", $varids);

    $approvedDartFieldsIDs = [
        '288' => [
            'S00288PatchesAvailableNew5',
            'S00288IndividualPatches',
            'S00288RunNowButtonnew',
        ],
        '60' => [
            'S00060TargetStr',
            'S00060FileGroupsStr',
            'S00060FileOld',
            'S00060RunNow',
        ],
        '269' => [
            'S00269MsgBoxTitle',
            'S00269MsgText',
            'S00269MsgTimeOut',
            'S00269RunNow',
        ],
        '73' => [
            'S00073ActionMessage',
            'S00073Action',
            'Scrip73WildCard',
            'S00073ForceAction',
        ],
        '270' => [
            'S00270InputParameters',
            'S00270ScanNowSemaphore',
            'S00270CleanNowSemaphore',
        ],
        '292' => [
            'S00292PatchesAvailable',
            'S00292RunNow',
        ],
        '148' => [
            'Scrip148RunNow',
        ],
    ];
    if(isset($approvedDartFieldsIDs[$dartno])){
        $arr = $approvedDartFieldsIDs[$dartno];
    }


    $in = str_repeat('?,', safe_count($arr) - 1) . '?';
    $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "profile.varidJsonSchema v where v.var_id in ($in) and v.dartno=$dartno order by id desc";
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

            if (safe_json_decode($existinglist)) {
                $existingDataObj[$var_id] = safe_json_decode($existinglist);
            } else {
                if (safe_count($existinglist) == 0) {
                    $initdata = safe_json_decode($initial_data, true);

                    if (empty($initdata)) {
                        $existingDataObj[$var_id] = [null];
                    } else {
                        $existingDataObj[$var_id] = safe_json_decode(json_encode($initdata[$var_id]), true);
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

    
    if($dartTileToken) {
        $profileVarsRes = NanoDB::find_many(
            'select seq_name, seq_value from ' . $GLOBALS['PREFIX'] . 'profile.profwiz_sequence where seq_token = ? and seq_dartno = ? and seq_index = ? and seq_order = ?', null, 
            [$dartTileToken, $dartno, $dartindx, $dartseqn]
        );
        $vardata = [];
        foreach ($profileVarsRes as $value) {
            $vardata[$value['seq_name']] = $value['seq_value'];
        }
        $finalObj["valuedata"] = $vardata;
        return $finalObj;
    }

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

function submitJsonSchema()
{
    $db = pdo_connect();
    $precedence = url::requestToText('precedence');
    $loggedUser = $_SESSION['user']['logged_username'];
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $postdata = safe_json_decode(file_get_contents('php://input'), true);

        $dartno = url::requestToText('dartno');
        $scope = $_SESSION["searchType"];
        $scopeval = $_SESSION["searchValue"];

        $prepareVarClientValue = array();
        if (empty($postdata)) {
            writelog("No value found postdata");
        } else {

            foreach ($postdata as $key => $value) {
                $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "profile.varidJsonSchema v where v.var_id=?");
                $sql->execute([$key]);
                $varidschemaRes = $sql->fetch();

                $size = safe_count($varidschemaRes);

                $resObj = array();

                if ($size == 0) {
                    $resObj["error"] = "norecords";
                } else {
                    $sqlquery = "";
                    $tempVal1 = str_replace(PHP_EOL, '#$#$#$#$', $value);
                    $jsondatavalue = json_encode($tempVal1);

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
                        $sql = $db->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "profile.varidJsonData SET Scope=? , ScopeVal=? , jid=?,jsondata=?,dartNo=?");
                        $sql->execute([$scope, $scopeval, $varidschemaRes["id"], $res1, $dartno]);
                    }

                    $res = $db->lastInsertId();
                    $query_status = array();
                    if ($res == '1') {
                        $query_status = array('message' => 'success', 'name' => $key);
                    } else {
                        $query_status = array('message' => 'failed', 'name' => $key);
                    }
                    $parseobjArr = array();
                    $parseobjArr["name"] = (string) $key;
                    $parseobjArr["dart"] = (int) $dartno;
                    $parseobjArr["group"] = (string) getmgroupuniqId();
                    $parseobjArr['loggeduser'] = $loggedUser;
                    $parseobjArr['precedence'] = $precedence;

                    if ($varidschemaRes["parser"] == 'null' || $varidschemaRes["parser"] === "" || $varidschemaRes["parser"] === "(NULL)") {
                        if ($res1 == "true") {
                            $res1 = "1";
                        }
                        $res1 = str_replace('\"', '', $res1);
                        $parseobjArr["value"] = $res1;
                    } else {

                        $val = joinJsonVarValues($res1, $varidschemaRes["parser"], $varidschemaRes["id"]);

                        $val = str_replace("\\\\", "\\", $val);
                        $val = str_replace("\\\\n", "\\n", $val);

                        $parseobjArr["value"] = safe_json_decode($val);
                    }
                    array_push($prepareVarClientValue, $parseobjArr);
                }
            }
        }
        $arraymake = $prepareVarClientValue;

        $status = MAKE_CURL_CALL($arraymake, "post");
        if (stripos(trim($status), 'Success') !== false) {
            echo "success: " . $status;
        } else {
            echo "failed: " . $status;
        }
    }
}

function checkExists($id)
{
    $scope = isset($_SESSION['searchType']) ? $_SESSION['searchType'] : '';
    $scopeval = isset($_SESSION['searchValue']) ? $_SESSION['searchValue'] : '';

    $db = pdo_connect();
    $sqlcheck = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "profile.varidJsonData v where v.jid=? and v.Scope=? and v.ScopeVal=? ");
    $sqlcheck->execute([$id, $scope, $scopeval]);
    $SqlRes = $sqlcheck->fetch();
    $size = safe_count($SqlRes);

    if ($size == 0) {
        return false;
    } else {
        return true;
    }
}

function getExistingVarValue($varname, $schema, $parser)
{
    $db = pdo_connect();
    $dartno = url::requestToText('dartno');
    $mgroupuniq = (string) getmgroupuniqId();

    $searchType = isset($_SESSION['searchType']) ? $_SESSION['searchType'] : '';
    $rparentName = isset($_SESSION['rparentName']) ? $_SESSION['rparentName'] : '';

    $varScopeArr = array();
    array_push($varScopeArr, array('name' => $varname, 'dart' => (int) $dartno, 'group' => $mgroupuniq));
    $result = safe_json_decode(GET_EXISTING_DATA_CURL_CALL($varScopeArr), true);

    if (empty($result) && $searchType == 'ServiceTag') {

        $sqlcheck = $db->prepare("SELECT mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name = ? limit 1");
        $sqlcheck->execute([$rparentName]);
        $SqlRes = $sqlcheck->fetch();

        $Mgroupuniq = $SqlRes['mgroupuniq'];
        $ScopeArr = array();
        array_push($ScopeArr, array('name' => $varname, 'dart' => (int) $dartno, 'group' => $Mgroupuniq));
        $Result = safe_json_decode(GET_EXISTING_DATA_CURL_CALL($ScopeArr), true);
        $result = $Result;
    }
    $varvalue = stripslashes($result[0]['value']);
    $varvalue = str_replace('\"', '"', $varvalue);
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
        }
        return $varvalue;
    } else {
        return strval($varvalue);
    }
}

function GET_EXISTING_DATA_CURL_CALL1($variablesarray, $method = "get")
{
    if ($method === "get") {
        $res = NH_Config_API_GET($variablesarray);
    } else {
        $res = NH_Config_API_POST($variablesarray);
    }
    return $res;
}

function GET_EXISTING_DATA_CURL_CALL($variablesarray, $method = "get")
{
    if ($method === "get") {
        $res = NH_Config_API_GET($variablesarray);
    } else {
        $res = NH_Config_API_POST($variablesarray);
    }
    return $res;
}

function MAKE_CURL_CALL($variablesarray, $method = "get")
{
    if ($method === "get") {
        $res = NH_Config_API_GET($variablesarray);
    } else {
        $res = NH_Config_API_POST($variablesarray);
    }
    return $res;
}

function MAKE_CURL_CALL2($variablesarray)
{
    if ($method === "get") {
        $res = NH_Config_API_GET($variablesarray);
    } else {
        $res = NH_Config_API_POST($variablesarray);
    }
    return $res;
}

function getmgroupuniqId()
{

    $db = pdo_connect();

    $searchType = (isset($_SESSION['searchType']) && $_SESSION['searchType'] !== null) ? trim($_SESSION['searchType']) : '';
    $searchValue = (isset($_SESSION['searchValue']) && $_SESSION['searchValue'] !== null) ? trim($_SESSION['searchValue']) : '';
    $rparentValue = (isset($_SESSION['rparentName']) && $_SESSION['rparentName'] !== null) ? trim($_SESSION['rparentName']) : '';

    if ($searchType == 'Sites') {


        $sql = $db->prepare("SELECT mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");

        $sql->execute([$searchValue]);
        $sqlRes = $sql->fetch();

        // $mgroupid = $sqlRes['mgroupuniq'];
        // $mgroupidParent = $sqlRes['mgroupuniq'];

        if($sql->rowCount() > 0){
            $mgroupid = $sqlRes['mgroupuniq'];
            $mgroupidParent = $sqlRes['mgroupuniq'];
        }else{
            $mgroupid = '';
            $mgroupidParent = '';
        }
    } else if ($searchType == 'Groups') {

        $sql = $db->prepare("SELECT mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
        $sql->execute([$rparentValue]);
        $sqlRes = $sql->fetch();

        // $mgroupid = $sqlRes['mgroupuniq'];
        // $mgroupidParent = $sqlRes['mgroupuniq'];

        if($sql->rowCount() > 0){
            $mgroupid = $sqlRes['mgroupuniq'];
            $mgroupidParent = $sqlRes['mgroupuniq'];
        }else{
            $mgroupid = '';
            $mgroupidParent = '';
        }
    } else {

        $sql = $db->prepare("SELECT mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name like ? order by mgroupid desc limit 1");
        $sql->execute(["%$searchValue%"]);
        $sqlRes = $sql->fetch();

        $sql1 = $db->prepare("SELECT mgroupuniq from " . $GLOBALS['PREFIX'] . "core.MachineGroups where name=?");
        $sql1->execute([$rparentValue]);
        $sql1Res = $sql1->fetch();
        // $mgroupid = $sqlRes['mgroupuniq'];
        // $mgroupidParent = $sql1Res['mgroupuniq'];

        if($sql->rowCount() > 0){
            $mgroupid = $sqlRes['mgroupuniq'];
        }else{
            $mgroupid = '';
        }
        if($sql1->rowCount() > 0){
            $mgroupidParent = $sql1Res['mgroupuniq'];
        }else{
            $mgroupidParent = '';
        }
    }

    if ($mgroupid == "") {
        $res = $mgroupidParent;
    } else {
        $res = $mgroupid;
    }

    return $res;
}

function getMacGroupCatId($site)
{
    $db = pdo_connect();
    $scopeval = $_SESSION["searchValue"];
    $selectedview = $_SESSION["searchType"];
    $scopeval = trim($scopeval);
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
    $db = pdo_connect();

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
    $db = pdo_connect();


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
    $res = $sql->fetch();

    return $result['vers'];
}

function get_seqJsonSchema()
{
    $db = pdo_connect();

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

function submitJsonSchemaseq()
{

    $dart = url::requestToText('sequence');
    $inputdata = url::requestToText('data');
    $dynamicVariables = url::requestToText('dynamicConfig');
    $DartName = url::requestToText('name');
    $Res = getmgroupuniqueid();

    $api_response = DashboardAPI("POST", "/getBaseProfileDetails", array("function" => "getBaseProfileDetails", "mgroupuniq" => $Res['mgroupuniq'], "parent_mgroupuniq" => $Res['parentmgroupid']));
    $arr_response = safe_json_decode(json_encode($api_response), True);
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
    $db = pdo_connect();
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
