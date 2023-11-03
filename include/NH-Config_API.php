<?php



include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-serv.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-ebld.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-abld.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-core.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-rlib.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-jump.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-cbld.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-cnst.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-errs.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-dsyn.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-gcfg.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-grps.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-tabs.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-gdrt.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-pdrt.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-ptch.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-user.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-repf.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-vars.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-audt.php';
include_once $_SERVER["DOCUMENT_ROOT"] . '/Dashboard/lib_main/l-cprg.php';

function NH_Config_API_GET($inputdata)
{
    $name = "";
    $dartnum = "";
    $mgroupuniq = "";

    $i = 0;

    $dataList = array();
    foreach ($inputdata as $key => $val) {
        $name = $val["name"];
        $dartnum = intval($val["dart"]);
        $mgroupuniq = $val["group"];
        if ($i == 0) {
            $valueList = array('name' => $name, 'dart' => $dartnum, 'mgroupuniq' => $mgroupuniq, 'map' => 1);
        } else {
            $valueList = array('name' => $name, 'dart' => $dartnum, 'mgroupuniq' => $mgroupuniq);
        }
        array_push($dataList, $valueList);
    }

    // logs::log(__FILE__, __LINE__, "NH_Config_API_GET: inputdata", $inputdata);
    logs::log(__FILE__, __LINE__, "NH_Config_API_GET: dataList", $dataList);

    $res = VARS_GetVariableValuesGroup($dataList);
    return json_encode($res);
}

function NH_Config_API_POST($inputdata)
{
    $value = "";
    $name = "";
    $dartnum = "";
    $mgroupuniq = "";

    $err = '';
    $db = db_connect();
    foreach ($inputdata as $key => $val) {
        $tempVal1 = str_replace('#$#$#$#$', PHP_EOL, $val["value"]);
        $tempVal = str_replace('\/', '/', $tempVal1);
        $value = $tempVal;
        $name = $val["name"];
        $dartnum = intval($val["dart"]);
        $mgroupuniq = $val["group"];
        $time = time();
        $err = constAppNoErr;
        $loggedUser = $val["loggeduser"];
        $precedence = $val["precedence"];
        $result = VARS_SetVariableValueGroup($value, $name, $dartnum, 0, $mgroupuniq, $time, constSourceScripConfig, TRUE, $db, $loggedUser, $name, $value, $precedence);
        if ($result != 'true') {
            $err = $result;
        }
    }
    $res = return_validate_func($err);
    return json_encode($res);
}

function NH_Config_API_PUT($inputdata,$scopeval = null){
    
    $res = [];
    $err = '';
    $value = "";
    $mgroupuniqValues = [];
    $pdo = pdo_connect();
    //get All the machines associated with this site to override the precendence
    $census_stmt = $pdo->prepare("Select DISTINCT mgroupuniq from core.MachineGroupMap mgm left join core.Census c on mgm.censussiteuniq = c.censussiteuniq where c.site = ? and mgm.mcatuniq = ?");
    $census_stmt->execute([$scopeval,'47c954121886a5e5aaca7eece461b56f']);  
    if($census_stmt->rowCount() > 0){
        $siteMachineIds = $census_stmt->fetchAll(PDO::FETCH_ASSOC);
        if(!empty($siteMachineIds)){   
            foreach($siteMachineIds as $siteMachineId){
                if(isset($siteMachineId['mgroupuniq'])){
                    array_push($mgroupuniqValues,$siteMachineId['mgroupuniq']);
                } else{
                    //echo "Invalid data";
                    $res['err'] = 'Invalid data';
                }
            }
        } else{
            //echo "No Machines Found Under this Site";
            $res['err'] = 'No Machines Found Under this Site';
        }
    } else{
        $res['err'] = 'Error row count is zero';
       // echo "No Machines Found Under this Site";
    }
  
    $in = str_repeat('?,', safe_count($mgroupuniqValues) - 1) . '?';
// filtering data from inputdata to override the dart configuration data
    foreach($inputdata as $key => $val){
        $tempVal1 = str_replace('#$#$#$#$', PHP_EOL, $val["value"]);
        $tempVal = str_replace('\/', '/', $tempVal1);
        $value = $tempVal;
        $dartno = intval($val["dart"]);
        $value_stmt = $pdo->prepare("select varuniq from core.Variables  where scop = ? and name = ? ");
        $value_stmt->execute([$dartno,$val["name"]]);
        $varuniqvalue = $value_stmt->fetch(PDO::FETCH_ASSOC);
        $err = constAppNoErr;
        //update the varvalues of all machines under this site
        $varValue_stmt = $pdo->prepare("update core.VarValues set valu = '$value',revldef=revldef+def, def=0, lastchange=UNIX_TIMESTAMP(), revl=revl+1 where varuniq = ? and mcatuniq = ? and  mgroupuniq  IN ($in)");
        $result = $varValue_stmt->execute(array_merge([$varuniqvalue['varuniq'],'47c954121886a5e5aaca7eece461b56f'], $mgroupuniqValues));
        if ($result != 'true') {
            $err = $result;
        }
    } 
   
    $res = return_validate_func($err);
    return json_encode($res);
}

function return_validate_func($err)
{
    if ($err != constAppNoErr) {
        LOGS::trace(1, "return_validate_func:ERR", $err);
        $error = "Error occurred : $err ";
        return $error;
    } else {
        return "Success";
    }
}

function NH_Config_API_exp($mid, $action,$index = null)
{
    $db = db_connect();
    $row = [];
    $admin = 'admin';
    $full = 0;

    if ($action === "exp") {
        $full = 1;
    }

    if ($mid > 0) {
        if ($admin) {
            $sql = "select * from Census\n"
                . " where id = $mid";
            $row = find_one($sql, $db);
        }
    }
    if ($row) {
        $host = $row['host'];
        $site = $row['site'];

        $usec = microtime();
        if ($full) {
            expunge_host($mid, $site, $host, $db);
            $act = 'expunged';
        } else {
            $crypt = purge_crypt_host($site, $host, $db);
            $provis = purge_provis_host($site, $host, $db);
            $config = purge_config_host($mid, $site, $host, $db);
            $provis += $crypt;
            if (count_config_machines($site, $db) == 0) {
                $provis += purge_provis_site($site, $db);
                $config += purge_config_site($site, $db);
            }
            $act = 'removed';
        }

        purge_patch_host($mid, $db);
        purge_groups_host($mid, $db);


        $num = 0;

        $sql = "delete from Census\n where id = $mid";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);

        $sql = "delete from CensusPerm\n where id = $mid";
        $res = redcommand($sql, $db);
        $num = affected($res, $db);

        $msec = microtime_diff($usec, microtime());
        $secs = microtime_show($msec);
        if ($num == 1) {
            groups_init($db, constGroupsInitFull);
            $msg = "Machine <b>$host</b> has been removed from <b>$site</b>.";
        } else {
            $msg = "Machine <b>$host</b> was not removed from <b>$site</b>.";
        }
    } else {
        $msg = "Machine <b>$mid</b> seems to have vanished.";
    }

    //deleteing events, assests, patches from db after expungue
    if($index !== null){
        $pdo = pdo_connect();
        $indexArray = explode(",", $index);
        foreach ($indexArray as $value) {
            if($value == 'assets*'){
                 //deleting assets of expungued machine
                 $asset_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'asset.AssetData where machineid = ?');
                 $data = $asset_stmt->execute([$mid]);
            }
            elseif($value == 'events*'){
                //deleting events of  expungue machines
                $event_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'event.Events where machine = ?');
                $data_event = $event_stmt->execute([$host]);
            }
            elseif($value == 'patches*'){
                //deleting patches of expungue machine
                $pdo = pdo_connect();
                $asset_stmt = $pdo->prepare('select  * from ' . $GLOBALS['PREFIX'] . 'softinst.PatchStatus where id = ?');
                $asset_stmt->execute([$mid]);
                $data = $asset_stmt->fetchAll(PDO::FETCH_ASSOC);
                if($data){
                    foreach($data as $pid){
                         $patch_stmt = $pdo->prepare('delete from ' . $GLOBALS['PREFIX'] . 'softinst.Patches where patchid = ?');
                         $data = $patch_stmt->execute([$pid['patchid']]);
                        }
                }
            }
        }

    }
    // $sqlevent = "'delete from  ' . $GLOBALS['PREFIX'] . 'event.Events where machine = '$host";
    // $resevent = redcommand($sqlevent, $db);
    return $msg;
}

function expunge_host($mid, $site, $host, $db)
{
    purge_asset_host($site, $host, $db);
    $crypt = purge_crypt_host($site, $host, $db);
    $provis = purge_provis_host($site, $host, $db);
    $config = purge_config_host($mid, $site, $host, $db);
    purge_update_host($site, $host, $db);
    purge_event_host($site, $host, $db);
    $provis += $crypt;
    if (count_config_machines($site, $db) == 0) {
        $provis += purge_provis_site($site, $db);
        $config += purge_config_site($site, $db);
    }
}

function count_config_machines($site, $db)
{
    $set = config_list($site, $db);
    return safe_count($set);
}
