<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-dbConnect.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-dashboard.php';

global $db;
$db = pdo_connect();
$function = '';
if (url::issetInRequest('func')) {
    $function = url::requestToAny('func');
    $function();
}

function getDartVarIds(){
    $db = pdo_connect();
    $dartno = url::requestToText('dartno');

    $Sql = $db->prepare("SELECT role_access_level FROM ".$GLOBALS['PREFIX']."profile.WizardMasterRole where role_id_desc='ASE' limit 1");
    $Sql->execute();
    $sqlRes = $Sql->fetchAll();

    $size = safe_count($sqlRes);
    $resObj=array();
    $dartlist=array();
    $varidlist="";
    if ($size == 0) {
        return $resObj["error"]="norecords";
     } else {
        foreach ($sqlRes as $key => $value) {
            extract($value);
            $json=safe_json_decode($role_access_level);
            $darts=$json->darts[0];
                        foreach ($darts as $k => $v) {
                array_push($dartlist,$k);
                if($k==$dartno){
                                        $e_dart=$v[0];

                    $varlist=array();
                    foreach ($e_dart as $ek => $ev) {
                        array_push($varlist,$ek);
                    }
                    $varidlist = implode("','", $varlist);


                }
            }

        }
     }
     return $varidlist;
}

function getDartList() {

    $db = pdo_connect();
    $Sql = $db->prepare('SELECT role_access_level FROM '.$GLOBALS['PREFIX'].'profile.WizardMasterRole where role_id_desc="ASE" limit 1');
    $Sql->execute();
    $sqlRes = $Sql->fetchAll();

    $size = safe_count($sqlRes);
    $resObj=array();
    $dartlist=array();
    if ($size == 0) {
        return $resObj["error"]="norecords";
     } else {
        foreach ($sqlRes as $key => $value) {
            extract($value);
            $json=safe_json_decode($role_access_level);
            $darts=$json->darts[0];
                        foreach ($darts as $k => $v) {
                array_push($dartlist,$k);


                            }

        }
     }
     echo json_encode($dartlist);

}

function getAllJsonSchema() {

    $db = pdo_connect();
    $Sql = $db->prepare("SELECT * FROM ".$GLOBALS['PREFIX']."profile.varidJsonSchema");
    $Sql->execute();
    $sqlRes = $Sql->fetchAll();

    $size = safe_count($sqlRes);
    $resObj=array();

    $schemaArr=array();
    $addnewSchema=array();
    array_push($addnewSchema,"Add new schema","",null,null,"Description");
    array_push($schemaArr,$addnewSchema);

    if ($size == 0) {
       return $resObj["error"]="norecords";
    } else {


        foreach ($sqlRes as $key => $value) {
            extract($value);
            $schemaObj=array();

                                 $jsonschema=str_replace("\\","\\\\",$jsonschema);
            $schemaStructure=safe_json_decode($jsonschema);
                       $jobj=array();
            $jobj["\$schema"]="http://json-schema.org/draft-07/schema#";
            $jobj["type"]="object";
            $jobj["properties"]=array($var_id=>$schemaStructure) ;


                        array_push($schemaObj,trim($var_id),$jobj,safe_json_decode($parser),null,"Description");
            array_push($schemaArr,$schemaObj);
        }
                       echo json_encode($schemaArr);
    }

}

function getAllJsonSchemaNew() {
    $db = pdo_connect();
    $varidlist=getDartVarIds();

    $Sql = $db->prepare("SELECT * FROM ".$GLOBALS['PREFIX']."profile.varidJsonSchema");
    $Sql->execute();
    $sqlRes = $Sql->fetchAll();

    $size = safe_count($sqlRes);
    $resObj=array();

    $schemaArr=array();
    $addnewSchema=array();

    $jobj_n=array();
    $jobj_n["\$schema"]="http://json-schema.org/draft-07/schema#";
    $jobj_n["type"]="object";
    $jobj_n["properties"]=safe_json_decode("{}");
    array_push($addnewSchema,"Add new schema",$jobj_n,null,null,"Description");
    array_push($schemaArr,$addnewSchema);

    if ($size == 0) {
        echo json_encode($schemaArr);
    } else {

        foreach ($sqlRes as $key => $value) {
            extract($value);
            $schemaObj=array();
                                 $jsonschema=str_replace("\\","\\\\",$jsonschema);
            $schemaStructure=safe_json_decode($jsonschema);
                       $jobj=array();
            $jobj["\$schema"]="http://json-schema.org/draft-07/schema#";
            $jobj["type"]="object";
            $jobj["properties"]=array($var_id=>$schemaStructure) ;


                        array_push($schemaObj,trim($var_id),$jobj,safe_json_decode($parser),safe_json_decode($initial_data),"Description");
            array_push($schemaArr,$schemaObj);
        }
                       echo json_encode($schemaArr);
    }

}

function validateParser() {


    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
     $postdata = safe_json_decode(file_get_contents('php://input'), true);

     $schema=$postdata["schema"];
     $parser=$postdata["parser"];

                  $varidObj = safe_json_decode($schema);

    $parserObj = safe_json_decode($parser);
            $mainValueArr = array();
    $newlinestr = "\n";

    foreach ($varidObj as $key => $value) {


        $delimiter = $parserObj->$key->delimiter;
              $ismultiline = $parserObj->$key->multiline;
        $keyObj = $value;
        $subArrlist = array();
        foreach ($keyObj as $subkeyarr => $subvaluearr) {

            $subobj = array();
            $subObjList = "";
                       if(gettype($subvaluearr)=="object"){
                foreach ($subvaluearr as $subkeyObj => $subvalueObj) {

                        array_push($subobj, $subvalueObj);
                }
                print_r($subobj);
                $subObjList = implode($delimiter, $subobj);
                array_push($subArrlist, $subObjList);
            }
        }
        $mainList = "";
        if ($ismultiline) {
            $mainList = implode($newlinestr, $subArrlist);
        }
        array_push($mainValueArr, $mainList);
    }

    $mainValueList = implode($newlinestr, $mainValueArr);
    echo json_encode($mainValueList);
}
}

function submitSchema() {
    $db = pdo_connect();
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
     $postdata = safe_json_decode(file_get_contents('php://input'), true);
     $varid=$postdata["varid"];
     $schema=$postdata["schema"];
     $parser=$postdata["parser"];
     $inidata=$postdata["inidata"];

          $schema=str_replace('"','\"',$schema);

          $parser=str_replace('"','\"',$parser);
     $inidata=str_replace('"','\"',$inidata);


      $Sql = 'SELECT * FROM '.$GLOBALS['PREFIX'].'profile.varidJsonSchema v where v.var_id="'.$varid.'"';
      $varidschemaRes = find_one($Sql, $GLOBALS['db']);
      $size = safe_count($varidschemaRes);
      $resObj=array();

      $sqlquery="";
      if ($size == 0) {
        $sqlquery = $db->prepare("INSERT INTO ".$GLOBALS['PREFIX']."profile.varidJsonSchema SET var_id=? , jsonschema=? , parser=? , initial_data=?");
        $sqlquery->execute([$varid,$schema,$parser,$inidata]);

      } else {
        $sqlquery = $db->prepare("UPDATE ".$GLOBALS['PREFIX']."profile.varidJsonSchema SET jsonschema=?, parser=?  , initial_data=? where var_id=?");
        $sqlquery->execute([$schema,$parser,$inidata,$varid]);
      }

              $res = $db->lastInsertId();
        $query_status=array();
        if ($res=='1') {
            $query_status = array('message' => 'success');
        } else {
            $query_status = array('message' => 'failed');
        }
        echo json_encode($query_status);
    }
}
?>
