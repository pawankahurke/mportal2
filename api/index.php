<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";


header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, X-API-KEY, X-sUserToken');

$_SESSION['internalcurl'] = true;
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../include/common_functions.php';
include_once '../lib/l-provision.php';
include_once '../lib/l-customer.php';
include_once '../lib/l-reseller.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-user.php';
include_once '../lib/l-util.php';
include_once 'JWT.php';
include_once 'auth.php';
include_once 'login.php';
include_once 'Rest.inc.php';
include_once '../include/NH-Config_API.php';
include_once 'notification/notificationV8.php';

$inputJSON = file_get_contents('php://input');
$input = safe_json_decode($inputJSON, true);

logs::log('Api_POST', $_POST);
logs::log('Api_input', $input);

$arr_json = null;

$isSort = false;
$islimit = false;

if (isset($input['mgroupuniq']) || isset($input['parent_mgroupuniq'])) {
    $route = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];

    // $route = substr($route, 1);
    // $route = explode("?", $route);
    // $route = explode("/", $route[0]);
    // $route = array_values($route);

    $route = $input['function'];

    $mgroupuniq = $input['mgroupuniq'];
    $parent_mgroupuniq = $input['parent_mgroupuniq'];
    if (isset($input['limit'])) {
        $limit = $input['limit'];
        $islimit = true;
    }

    if (isset($input['sort'])) {
        $sort = $input['sort'];
        $isSort = true;
    }

    if (safe_count($route) > 0) {

        // switch ($route[3]) {
        switch ($route) {
            case 'getServiceLogMaster':

                $parseobjArr = array();
                $parseobjArr["name"] = "S00306_ServiceLogMaster";
                $parseobjArr["dart"] = 306;
                $parseobjArr["group"] = $mgroupuniq;

                $response = MAKE_CURL_CALL($parseobjArr);
                $resObj = safe_json_decode(trim($response));

                if (isset($resObj->value) && !empty(trim($resObj->value))) {
                    $serviceLogMaster = $resObj->value;
                    $serviceLogMasterStr = preg_replace('~[\r\n]+~', '##nl##', $serviceLogMaster);
                    $colName = array("mid", "dart", "tileName", "varValues", "successDesc", "terminateDesc", "Tile");
                    if ($isSort) {

                        $arr_json = createJSONFormat("ServiceLog_Master", $serviceLogMasterStr, $colName, "##nl##", "#NXT#", $sort, $limit);
                    } else {
                        $arr_json = createJSONFormat("ServiceLog_Master", $serviceLogMasterStr, $colName, "##nl##", "#NXT#", 0, $limit);
                    }

                    response($arr_json, 200);
                } else {

                    if ($parseobjArr_parent != $mgroupuniq) {
                        $parseobjArr_parent = array();
                        $parseobjArr_parent["name"] = "S00306_ServiceLogMaster";
                        $parseobjArr_parent["dart"] = 306;
                        $parseobjArr_parent["group"] = $parent_mgroupuniq;
                        $response1 = MAKE_CURL_CALL($parseobjArr_parent);

                        $resObj_parent = safe_json_decode(trim($response1));

                        if (isset($resObj_parent->value)) {
                            $mainStr = preg_replace('~[\r\n]+~', '##nl##', $resObj_parent->value);
                            $colName_base_profile = array("Enable/Disable", "mid", "menuItem", "type", "parentId", "profile", "dart", "variable", "varValue", "shortDesc", "description", "tileDesc", "OS", "page", "status", "authFalg", "usageType");

                            if ($isSort) {
                                $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", $sort, $limit);
                            } else {
                                $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", 0, $limit);
                            }

                            response($arr_json, 200);
                        } else {
                            $response = array("status" => "failed", "message" => 'getServiceLogMaster value not found/set');
                            response(json_encode($response), 404);
                        }
                    } else {
                        $response = array("status" => "failed", "message" => 'getServiceLogMaster value not found/set');
                        response(json_encode($response), 404);
                    }
                }
                break;
            case 'getStatusMaster':
                $parseobjArr = array();
                $parseobjArr["name"] = "S00306_StatusMaster";
                $parseobjArr["dart"] = 306;
                $parseobjArr["group"] = $mgroupuniq;

                $response = MAKE_CURL_CALL($parseobjArr);
                $resObj = safe_json_decode(trim($response));

                if (isset($resObj->value) && !empty(trim($resObj->value))) {
                    $status_masterStr = preg_replace('~[\r\n]+~', '##nl##', $resObj->value);
                    $colName_sm = array("id", "dart", "statusName", "isEnabled");

                    if ($isSort) {

                        $arr_json = createJSONFormat("Status_Master", $status_masterStr, $colName_sm, "##nl##", "#NXT#", $sort, $limit);
                    } else {
                        $arr_json = createJSONFormat("Status_Master", $status_masterStr, $colName_sm, "##nl##", "#NXT#", 0, $limit);
                    }

                    response($arr_json, 200);
                } else {
                    if ($parseobjArr_parent != $mgroupuniq) {
                        $parseobjArr_parent = array();
                        $parseobjArr_parent["name"] = "S00306_StatusMaster";
                        $parseobjArr_parent["dart"] = 306;
                        $parseobjArr_parent["group"] = $parent_mgroupuniq;
                        $response1 = MAKE_CURL_CALL($parseobjArr_parent);

                        $resObj_parent = safe_json_decode(trim($response1));

                        if (isset($resObj_parent->value)) {
                            $mainStr = preg_replace('~[\r\n]+~', '##nl##', $resObj_parent->value);
                            $colName_base_profile = array("Enable/Disable", "mid", "menuItem", "type", "parentId", "profile", "dart", "variable", "varValue", "shortDesc", "description", "tileDesc", "OS", "page", "status", "authFalg", "usageType");

                            if ($isSort) {
                                $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", $sort, $limit);
                            } else {
                                $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", 0, $limit);
                            }

                            response($arr_json, 200);
                        } else {
                            $response = array("status" => "failed", "message" => 'getStatusMaster value not found/set');
                            response(json_encode($response), 404);
                        }
                    } else {
                        $response = array("status" => "failed", "message" => 'getStatusMaster value not found/set');
                        response(json_encode($response), 404);
                    }
                }
                break;
            case 'getStatusDetails':
                $parseobjArr = array();
                $parseobjArr["name"] = "S00306_StatusDetails";
                $parseobjArr["dart"] = 306;
                $parseobjArr["group"] = $mgroupuniq;

                $response = MAKE_CURL_CALL($parseobjArr);
                $resObj = safe_json_decode(trim($response));

                if (isset($resObj->value) && !empty(trim($resObj->value))) {
                    $status_detailsStr = preg_replace('~[\r\n]+~', '##nl##', $resObj->value);
                    $colName_sd = array("id", "page", "profile", "varValues", "variable", "dartfrom", "dartToExecute", "description", "logicType", "logicPara", "dispBtn", "url", "status", "title", "parent", "UISection", "GUIType", "addCss", "functionToCall", "ImageFileName", "usageType", "Numberofdays", "isRD");

                    if ($isSort) {
                        $arr_json = createJSONFormat("Status_Details", $status_detailsStr, $colName_sd, "##nl##", "#NXT#", $sort, $limit);
                    } else {
                        $arr_json = createJSONFormat("Status_Details", $status_detailsStr, $colName_sd, "##nl##", "#NXT#", 0, $limit);
                    }

                    response($arr_json, 200);
                } else {
                    if ($parseobjArr_parent != $mgroupuniq) {
                        $parseobjArr_parent = array();
                        $parseobjArr_parent["name"] = "S00306_StatusDetails";
                        $parseobjArr_parent["dart"] = 306;
                        $parseobjArr_parent["group"] = $parent_mgroupuniq;
                        $response1 = MAKE_CURL_CALL($parseobjArr_parent);

                        $resObj_parent = safe_json_decode(trim($response1));

                        if (isset($resObj_parent->value)) {
                            $mainStr = preg_replace('~[\r\n]+~', '##nl##', $resObj_parent->value);
                            $colName_base_profile = array("Enable/Disable", "mid", "menuItem", "type", "parentId", "profile", "dart", "variable", "varValue", "shortDesc", "description", "tileDesc", "OS", "page", "status", "authFalg", "usageType");

                            if ($isSort) {
                                $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", $sort, $limit);
                            } else {
                                $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", 0, $limit);
                            }

                            response($arr_json, 200);
                        } else {
                            $response = array("status" => "failed", "message" => 'getStatusDetails value not found/set');
                            response(json_encode($response), 404);
                        }
                    } else {
                        $response = array("status" => "failed", "message" => 'getStatusDetails value not found/set');
                        response(json_encode($response), 404);
                    }
                }
                break;
            case 'getBaseProfileDetails':
                $parseobjArr = array();
                $parseobjArr["name"] = "S00304_BaseProfiles";
                $parseobjArr["dart"] = 304;
                $parseobjArr["group"] = $mgroupuniq;

                $newarray = array();
                array_push($newarray, $parseobjArr);
                $response = NH_Config_API_GET($newarray);
                // logs::log(__FILE__, __LINE__, "API: NH_Config_API_GET", $response);


                $resObjData = safe_json_decode(trim($response));
                $resObj = $resObjData[0];

                // logs::log(__FILE__, __LINE__, "API: getBaseProfileDetails", $resObj);

                if (isset($resObj->value) && !empty(trim($resObj->value))) {
                    $mainStr = preg_replace('~[\r\n]+~', '##nl##', $resObj->value);
                    $colName_base_profile = [
                        "Enable/Disable",
                        "mid",
                        "menuItem",
                        "type",
                        "parentId",
                        "profile",
                        "dart",
                        "variable",
                        "varValue",
                        "shortDesc",
                        "description",
                        "tileDesc",
                        "OS",
                        "page",
                        "status",
                        "authFalg",
                        "usageType",
                        "dynamic"
                    ];

                    if ($isSort) {
                        $arr_json = createJSONFormat(
                            "Main",
                            $mainStr,
                            $colName_base_profile,
                            "##nl##",
                            "#NXT#",
                            $sort,
                            $limit
                        );
                    } else {
                        $arr_json = createJSONFormat(
                            "Main",
                            $mainStr,
                            $colName_base_profile,
                            "##nl##",
                            "#NXT#",
                            0,
                            $limit
                        );
                    }

                    // logs::log(__FILE__, __LINE__, "API: getBaseProfileDetails:arr_json", $arr_json);

                    response($arr_json, 200);
                } else {
                    if ($parseobjArr_parent != $mgroupuniq) {
                        $parseobjArr_parent = array();
                        $parseobjArr_parent["name"] = "S00304_BaseProfiles";
                        $parseobjArr_parent["dart"] = 304;
                        $parseobjArr_parent["group"] = $parent_mgroupuniq;
                        $newparray1 = array();
                        array_push($newparray1, $parseobjArr_parent);
                        $response1 = NH_Config_API_GET($newparray1);
                        $resObjData1 = safe_json_decode(trim($response1));
                        $resObj_parent = $resObjData1[0];

                        $parseobjArr_parent1 = array();
                        $parseobjArr_parent1["name"] = "S00304DynamicProfileConf";
                        $parseobjArr_parent1["dart"] = 304;
                        $parseobjArr_parent1["group"] = $parent_mgroupuniq;
                        $newparray2 = array();
                        array_push($newparray2, $parseobjArr_parent1);
                        $response2 = NH_Config_API_GET($newparray2);
                        $resObjData2 = safe_json_decode(trim($response2));
                        $resObj_parent1 = $resObjData2[0];

                        if (isset($resObj_parent->value) || isset($resObj_parent1->value)) {
                            $mainStr = preg_replace('~[\r\n]+~', '##nl##', $resObj_parent->value);
                            $seq = stripslashes($resObj_parent1->value);
                            $seq = stripslashes($seq);
                            $colName_base_profile = array("Enable/Disable", "mid", "menuItem", "type", "parentId", "profile", "dart", "variable", "varValue", "shortDesc", "description", "tileDesc", "OS", "page", "status", "authFalg", "usageType", "dynamic");

                            if ($isSort) {
                                $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", $sort, $limit, "dynamiConfig", $seq);
                            } else {
                                $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", 0, $limit, "dynamiConfig", $seq);
                            }

                            response($arr_json, 200);
                        } else {
                            $response = array("status" => "failed", "message" => 'getBaseProfileDetails value not found/set (1)');
                            response(json_encode($response), 404);
                        }
                    } else {
                        $response = array("status" => "failed", "message" => 'getBaseProfileDetails value not found/set (2)');
                        response(json_encode($response), 404);
                    }
                }
                break;
            case 'getprofileSequence':
                $parseobjArr = array();
                $parseobjArr["name"] = "S00304_ProfileSequence";
                $parseobjArr["dart"] = 304;
                $parseobjArr["group"] = $mgroupuniq;

                $response = MAKE_CURL_CALL($parseobjArr);
                $resObj = safe_json_decode(trim($response));

                if (isset($resObj->value) && !empty(trim($resObj->value))) {
                    $profile_sequenceStr = preg_replace('~[\r\n]+~', '##nl##', $resObj->value);
                    $colName_profileseq = array("profile_name", "sequence_id");

                    if ($isSort) {

                        $arr_json = createJSONFormat("ProfileSequence", $profile_sequenceStr, $colName_profileseq, "##nl##", ":", $sort, $limit);
                    } else {
                        $arr_json = createJSONFormat("ProfileSequence", $profile_sequenceStr, $colName_profileseq, "##nl##", ":", 0, $limit);
                    }

                    response($arr_json, 200);
                } else {
                    if ($parseobjArr_parent != $mgroupuniq) {
                        $parseobjArr_parent = array();
                        $parseobjArr_parent["name"] = "S00304_ProfileSequence";
                        $parseobjArr_parent["dart"] = 304;
                        $parseobjArr_parent["group"] = $parent_mgroupuniq;
                        $response1 = MAKE_CURL_CALL($parseobjArr_parent);

                        $resObj_parent = safe_json_decode(trim($response1));

                        if (isset($resObj_parent->value)) {
                            $mainStr = preg_replace('~[\r\n]+~', '##nl##', $resObj_parent->value);
                            $colName_base_profile = array("Enable/Disable", "mid", "menuItem", "type", "parentId", "profile", "dart", "variable", "varValue", "shortDesc", "description", "tileDesc", "OS", "page", "status", "authFalg", "usageType");

                            if ($isSort) {
                                $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", $sort, $limit);
                            } else {
                                $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", 0, $limit);
                            }

                            response($arr_json, 200);
                        } else {
                            $response = array("status" => "failed", "message" => 'getprofileSequence value not found/set');
                            response(json_encode($response), 404);
                        }
                    } else {
                        $response = array("status" => "failed", "message" => 'getprofileSequence value not found/set');
                        response(json_encode($response), 404);
                    }
                }
                break;

            case 'getSequenceIdDetails':
                $parseobjArr = array();
                $parseobjArr["name"] = "S00304_SequenceDetails";
                $parseobjArr["dart"] = 304;
                $parseobjArr["group"] = $mgroupuniq;

                $response = MAKE_CURL_CALL($parseobjArr);
                $resObj = safe_json_decode(trim($response));

                if (isset($resObj->value) && !empty(trim($resObj->value))) {
                    $Sequence_id_detailsStr = preg_replace('~[\r\n]+~', '##nl##', $resObj->value);
                    $colName_seq_details = array("id", "dart", "seq_name", "variable_ids", "");

                    if ($isSort) {

                        $arr_json = createJSONFormat("SequenceIdDetails", $Sequence_id_detailsStr, $colName_seq_details, "##nl##", ",", $sort, $limit);
                    } else {
                        $arr_json = createJSONFormat("SequenceIdDetails", $Sequence_id_detailsStr, $colName_seq_details, "##nl##", ",", 0, $limit);
                    }

                    response($arr_json, 200);
                } else {
                    if ($parseobjArr_parent != $mgroupuniq) {
                        $parseobjArr_parent = array();
                        $parseobjArr_parent["name"] = "S00304_SequenceDetails";
                        $parseobjArr_parent["dart"] = 304;
                        $parseobjArr_parent["group"] = $parent_mgroupuniq;
                        $response1 = MAKE_CURL_CALL($parseobjArr_parent);

                        $resObj_parent = safe_json_decode(trim($response1));

                        if (isset($resObj_parent->value)) {
                            $mainStr = preg_replace('~[\r\n]+~', '##nl##', $resObj_parent->value);
                            $colName_base_profile = array("Enable/Disable", "mid", "menuItem", "type", "parentId", "profile", "dart", "variable", "varValue", "shortDesc", "description", "tileDesc", "OS", "page", "status", "authFalg", "usageType");

                            if ($isSort) {
                                $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", $sort, $limit);
                            } else {
                                $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", 0, $limit);
                            }

                            response($arr_json, 200);
                        } else {
                            $response = array("status" => "failed", "message" => 'getSequenceIdDetails value not found/set');
                            response(json_encode($response), 404);
                        }
                    } else {
                        $response = array("status" => "failed", "message" => 'getSequenceIdDetails value not found/set');
                        response(json_encode($response), 404);
                    }
                }
                break;
            case 'getVariableDetails':
                $parseobjArr = array();
                $parseobjArr["name"] = "S00304_VariableIdConfig";
                $parseobjArr["dart"] = 304;
                $parseobjArr["group"] = $mgroupuniq;

                $response = MAKE_CURL_CALL($parseobjArr);
                $resObj = safe_json_decode(trim($response));

                if (isset($resObj->value) && !empty(trim($resObj->value))) {
                    $var_config = $resObj->value;
                    $var_config = str_replace('VID:', '', $var_config);
                    $var_config = str_replace('VN:', ',', $var_config);
                    $var_config = str_replace('VALUE:', ',', $var_config);
                    $var_config = preg_replace('~[\r\n]+~', '', $var_config);

                    $variable_detailsStr = $var_config;
                    $colName_var_details = array("vid", "vn", "value");

                    if ($isSort) {

                        $arr_json = createJSONFormat("VariableDetails", $variable_detailsStr, $colName_var_details, "#NXTVAR#", ",", $sort, $limit);
                    } else {
                        $arr_json = createJSONFormat("VariableDetails", $variable_detailsStr, $colName_var_details, "#NXTVAR#", ",", 0, $limit);
                    }

                    response($arr_json, 200);
                } else {
                    if ($parseobjArr_parent != $mgroupuniq) {
                        $parseobjArr_parent = array();
                        $parseobjArr_parent["name"] = "S00304_VariableIdConfig";
                        $parseobjArr_parent["dart"] = 304;
                        $parseobjArr_parent["group"] = $parent_mgroupuniq;
                        $response1 = MAKE_CURL_CALL($parseobjArr_parent);

                        $resObj_parent = safe_json_decode(trim($response1));

                        if (isset($resObj_parent->value)) {
                            $mainStr = preg_replace('~[\r\n]+~', '##nl##', $resObj_parent->value);
                            $colName_base_profile = array("Enable/Disable", "mid", "menuItem", "type", "parentId", "profile", "dart", "variable", "varValue", "shortDesc", "description", "tileDesc", "OS", "page", "status", "authFalg", "usageType");

                            if ($isSort) {
                                $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", $sort, $limit);
                            } else {
                                $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", 0, $limit);
                            }

                            response($arr_json, 200);
                        } else {
                            $response = array("status" => "failed", "message" => 'getVariableDetails value not found/set');
                            response(json_encode($response), 404);
                        }
                    } else {
                        $response = array("status" => "failed", "message" => 'getVariableDetails value not found/set');
                        response(json_encode($response), 404);
                    }
                }
                break;
            case 'deleteBaseProfileDetails':
                if ($route[4] != "") {
                    $parseobjArr = array();
                    $parseobjArr["name"] = "S00304_BaseProfiles";
                    $parseobjArr["dart"] = 304;
                    $parseobjArr["group"] = $mgroupuniq;

                    $response = MAKE_CURL_CALL($parseobjArr);
                    $resObj = safe_json_decode(trim($response));

                    if (isset($resObj->value) && !empty(trim($resObj->value))) {
                        $mainStr = preg_replace('~[\r\n]+~', '##nl##', $resObj->value);
                        $colName_base_profile = array("Enable/Disable", "mid", "menuItem", "type", "parentId", "profile", "dart", "variable", "varValue", "shortDesc", "description", "tileDesc", "OS", "page", "status", "authFalg", "usageType");

                        if ($isSort) {
                            $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", $sort, $limit);
                        } else {
                            $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", 0, $limit);
                        }
                        $arr_json = safe_json_decode($arr_json);

                        $main_data = $arr_json->Main;
                        array_push($main_data, (object) $input['postdata']);

                        $var_json = deleteArray("mid", $route[4], $main_data);
                        $encoded_str = encodeVariableFormat($var_json, "\n", "#NXT#");

                        $setobjArr = array();
                        $setobjArr["name"] = "S00304_BaseProfiles";
                        $setobjArr["dart"] = 304;
                        $setobjArr["group"] = $mgroupuniq;
                        $setobjArr["value"] = $encoded_str;
                        $set_var_response = MAKE_CURL_CALL($setobjArr, "");

                        if (strpos($set_var_response, "error") == false) {
                            $set_response_obj = array("status" => "success", "message" => $set_var_response);
                            response(json_encode($set_response_obj), 200);
                        } else {
                            $set_response_obj = array("status" => "failed", "message" => 'Failed to update : ' . $set_var_response);
                            response(json_encode($set_response_obj), 404);
                        }
                    } else {
                        $response = array("status" => "failed", "message" => 'deleteBaseProfileDetails value not found/set');
                        response(json_encode($response), 404);
                    }
                } else {
                    $response = array("status" => "failed", "message" => 'query param delete Id is required');
                    response(json_encode($response), 404);
                }

                break;
            case 'modifyBaseProfileDetails':
                if (isset($input['postdata'])) {
                    $response = "";
                    $parseobjArr = array();
                    $parseobjArr["name"] = "S00304_BaseProfiles";
                    $parseobjArr["dart"] = 304;
                    $parseobjArr["group"] = $mgroupuniq;

                    $response = MAKE_CURL_CALL($parseobjArr);
                    if (trim($response) === "") {
                        $parseobjArr = array();
                        $parseobjArr["name"] = "S00304_BaseProfiles";
                        $parseobjArr["dart"] = 304;
                        $parseobjArr["group"] = $parent_mgroupuniq;
                        $response = MAKE_CURL_CALL($parseobjArr);
                    }
                    $resObj = safe_json_decode(trim($response));

                    if (isset($resObj->value) && !empty(trim($resObj->value))) {
                        $mainStr = preg_replace('~[\r\n]+~', '##nl##', $resObj->value);
                        $colName_base_profile = array("Enable/Disable", "mid", "menuItem", "type", "parentId", "profile", "dart", "variable", "varValue", "shortDesc", "description", "tileDesc", "OS", "page", "status", "authFalg", "usageType");

                        if ($isSort) {
                            $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", $sort, $limit);
                        } else {
                            $arr_json = createJSONFormat("Main", $mainStr, $colName_base_profile, "##nl##", "#NXT#", 0, $limit);
                        }
                        $arr_json = safe_json_decode($arr_json);

                        $main_data = $arr_json->Main;
                        array_push($main_data, (object) $input['postdata']);

                        $var_json = updateArray("mid", $main_data);
                        $encoded_str = encodeVariableFormat($var_json, "\n", "#NXT#");

                        $setobjArr = array();
                        $setobjArr["name"] = "S00304_BaseProfiles";
                        $setobjArr["dart"] = 304;
                        $setobjArr["group"] = $mgroupuniq;
                        $setobjArr["value"] = $encoded_str;
                        $set_var_response = MAKE_CURL_CALL($setobjArr, "");

                        if (strpos($set_var_response, "error") == false) {
                            $set_response_obj = array("status" => "success", "message" => $set_var_response);
                            response(json_encode($set_response_obj), 200);
                        } else {
                            $set_response_obj = array("status" => "failed", "message" => 'Failed to update : ' . $set_var_response);
                            response(json_encode($set_response_obj), 404);
                        }
                    } else {
                        $response = array("status" => "failed", "message" => 'getBaseProfileDetails value not found/set (3)');
                        response(json_encode($response), 404);
                    }
                } else {
                    $response = array("status" => "failed", "message" => 'postdata is required to update');
                    response(json_encode($response), 404);
                }
                break;
            default:
                $response = array("status" => "failed", "message" => "API Not found");
                response($response, 404);
                break;
        }
    } else {
        $response = array("status" => "failed", "message" => "Not valid Url");
        response($response, 404);
    }
} else {
    if (($input == '') && (!url::issetInRequest('rquest'))) {
        foreach ($_REQUEST as $key => $value) {
            $input[$key] = $value;
        }
        $function = (trim($input['scope']));
    } else {
        if (url::issetInRequest('rquest')) {
            $function = (trim(str_replace("/", "", url::requestToAny('rquest'))));
        } else {
            $function = (trim($input['scope']));
        }
    }

    if ($function == '') {
        $jsondata = '[{"status":"error", "msg":"No method has been selected."}]';
        echo $jsondata;
    } else {
        if ($function != 'validateuser') {
            $scope_range = [
                'getProfileTiles',
                'getSolutions',
                'pushSolutionsNew',
                'validateCRMDetails',
                'machineHistory',
                'UserAuthenticate',
                'machineOnlineStatus',
                'machineBasicInfo',
                'machineAssetID',
                'actionInteractive',
                'getMachineDetails',
                'getMachinesList',
                'getNotificationSummary',
                'actionNotifications',
                'getProfileData',
            ];

            if (!in_array($function, $scope_range)) {


                if (
                    in_array("mgroupuniq", array_keys($input))
                    || in_array("parent_mgroupuniq", array_keys($input))
                ) {
                    $jsondata = array("status" => "error", "msg" => "Invalid scope. (3) for function $function (due mgroupuniq and parent_mgroupuniq is null)");
                } else {

                    $jsondata = array("status" => "error", "msg" => "Invalid scope. (2) for function $function");
                }

                response(json_encode($jsondata), 200);
            } else {
                if ($function == 'validateCRMDetails') {
                    $datares = $function($input);
                    echo $datares;
                } else {
                    global $HFN_ENCRYPT_JWT_KEY;
                    $auth_key = $HFN_ENCRYPT_JWT_KEY;
                    $db = db_connect();
                    db_change($GLOBALS['PREFIX'] . 'core', $db);
                    $validate = basicauth_validate($auth_key, $db);
                    if ($validate == 'Success') {
                        $datares = $function($input);
                        echo $datares;
                    } else {
                        echo $validate;
                    }
                }
            }
        } else {
            $datares = $function($input);
            echo $datares;
        }
    }

    $_SESSION['internalcurl'] = false;
}

function encodeVariableFormat($arrdata, $newline_separator, $sub_delimiter)
{
    $Newarray = [];
    foreach ($arrdata as $key => $value) {
        $lineStr = implode($sub_delimiter, (array) $value);
        array_push($Newarray, $lineStr);
    }

    $encodeVariableFormat = implode($newline_separator, $Newarray);

    return $encodeVariableFormat;
}

function deleteArray($obj_name, $obj_val, $data)
{
    $_data = array();
    foreach ($data as $elementKey => $element) {
        foreach ($element as $valueKey => $value) {
            if ($valueKey == $obj_name && $value == $obj_val) {
                unset($data[$elementKey]);
            }
        }
    }
    $data = array_values($data);
    return $data;
}

function updateArray($key, $data)
{
    $_data = array();
    $cnt = 0;
    foreach ($data as $v) {
        if (isset($_data[$v->$key])) {
            $cnt = $cnt + 1;
            if ($cnt > 1) {
                continue;
            }
        }
        $_data[$v->$key] = $v;
    }
    $data = array_values($_data);
    return $data;
}
