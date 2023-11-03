<?php



include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'l-util.php';
define('L1', 'L1');
define('L2', 'L2');
define('L3', 'L3');

function PRFL_GetProfileList($db, $key, $cid, $custType, $channel_id, $entity_id)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $tree = [];

        $sqladminid = "select eid from " . $GLOBALS['PREFIX'] . "agent.channel where emailId = 'admin@nanoheal.com' limit 1";
        $sqladminidres = find_one($sqladminid, $db);
        $adminid = $sqladminidres['eid'];

        if ($custType == 5 || $custType == '5') {

            $profilemapresult = PRFL_GetProfileMapData($db, $key, $cid);

            if ($profilemapresult == '' || $profilemapresult == 'null' || $profilemapresult == null) {

                $profilemapresult = PRFL_GetProfileMapData($db, $key, $channel_id);

                if ($profilemapresult == '' || $profilemapresult == 'null' || $profilemapresult == null) {

                    $profilemapresult = PRFL_GetProfileMapData($db, $key, $entity_id);
                }
            }

            $sqlenabled = "select profileid from " . $GLOBALS['PREFIX'] . "profile.profileMap where customerid = $adminid";
            $sqlenabledres = find_many($sqlenabled, $db);

            foreach ($sqlenabledres as $value) {
                $adminmid .= "" . $value['profileid'] . ",";
            }

            $mid = implode($profilemapresult, ",");
            $sql = "select type,parentId,profile,page,mid from " . $GLOBALS['PREFIX'] . "event.profile where mid in ($mid) or mid in (" .  rtrim($adminmid, ",") . ") order by type";
        } else if ($custType == 2 || $custType == '2') {

            $profilemapresult = PRFL_GetProfileMapData($db, $key, $cid);

            if ($profilemapresult == '' || $profilemapresult == 'null' || $profilemapresult == null) {

                $profilemapresult = PRFL_GetProfileMapData($db, $key, $entity_id);
            }

            $mid = implode($profilemapresult, ",");

            $sqlenabled = "select profileid from " . $GLOBALS['PREFIX'] . "profile.profileMap where customerid = $adminid";
            $sqlenabledres = find_many($sqlenabled, $db);

            foreach ($sqlenabledres as $value) {
                $adminmid .= "" . $value['profileid'] . ",";
            }

            $sql = "select type,parentId,profile,page,mid from " . $GLOBALS['PREFIX'] . "event.profile where mid in ($mid) or mid in (" .  rtrim($adminmid, ",") . ") order by type";
        } else if ($custType == 1 || $custType == '1') {

            $profilemapresult = PRFL_GetProfileMapData($db, $key, $cid);

            if ($profilemapresult == 'null' || $profilemapresult == null || $profilemapresult == '') {

                $profilemapresult = PRFL_GetProfileMapData($db, $key, "1");
                $sqlenabled = "select profileid from " . $GLOBALS['PREFIX'] . "profile.profileMap where customerid = $adminid";
                $sqlenabledres = find_many($sqlenabled, $db);

                foreach ($sqlenabledres as $value) {
                    $mid .= "" . $value['profileid'] . ",";
                }

                $sql = "select type,parentId,profile,page,mid from " . $GLOBALS['PREFIX'] . "event.profile where mid in (" .  rtrim($mid, ",") . ") order by type";
            } else {

                $midadmin = '';
                $mid = implode($profilemapresult, ",");
                $sqladmin = "select profileid from " . $GLOBALS['PREFIX'] . "profile.profileMap where customerid = $adminid";
                $sqladmindres = find_many($sqladmin, $db);

                foreach ($sqladmindres as $value) {
                    $midadmin .= "" . $value['profileid'] . ",";
                }

                $sql = "select type,parentId,profile,page,mid from " . $GLOBALS['PREFIX'] . "event.profile where mid in ($mid) or mid in (" .  rtrim($midadmin, ",") . ") order by type";
            }
        } else {

            $profilemapresult = PRFL_GetProfileMapData($db, $key, $cid);
            $sql = "select type,parentId,profile,page,mid from " . $GLOBALS['PREFIX'] . "event.profile order by type";
        }

        $res = find_many($sql, $db);

        foreach ($res as $key => $val) {

            if (in_array($val['mid'], $profilemapresult)) {

                if (($val['type'] == 'L1' && $val['parentId'] == '1') || ($val['type'] == L1 && $val['parentId'] == 1)) {
                    $tree[intval($val['page'])] = array('name' => safe_addslashes($val['profile']), 'icon' => '../vendors/images/l1.png', 'children' => [], 'checked' => 'true', 'profileid' => $val['mid']);
                } else if ($val['type'] == 'L2' || $val['type'] == L2) {

                    if (intval($val['page']) == intval($val['parentId'])) {
                        $executable = TRUE;
                    } else {
                        $executable = FALSE;
                    }
                    if (isset($tree[intval($val['page'])])) {
                        array_push($tree[intval($val['page'])]['children'], array('name' => safe_addslashes($val['profile']), 'icon' => '../vendors/images/l2.png', 'checked' => 'true', 'profileid' => $val['mid']));
                    }
                } else {
                    if (isset($tree[intval($val['page'])])) {
                        array_push($tree[intval($val['page'])]['children'], array('name' => safe_addslashes($val['profile']), 'icon' => '../vendors/images/l3.png', 'checked' => 'true', 'profileid' => $val['mid']));
                    }
                }
            } else {

                if (($val['type'] == 'L1' && $val['parentId'] == '1') || ($val['type'] == L1 && $val['parentId'] == 1)) {
                    $tree[intval($val['page'])] = array('name' => safe_addslashes($val['profile']), 'icon' => '../vendors/images/l1.png', 'children' => [], 'checked' => 'false', 'profileid' => $val['mid']);
                } else if ($val['type'] == 'L2' || $val['type'] == L2) {

                    if (intval($val['page']) == intval($val['parentId'])) {
                        $executable = TRUE;
                    } else {
                        $executable = FALSE;
                    }
                    if (isset($tree[intval($val['page'])])) {
                        array_push($tree[intval($val['page'])]['children'], array('name' => safe_addslashes($val['profile']), 'icon' => '../vendors/images/l2.png', 'checked' => 'false', 'profileid' => $val['mid']));
                    }
                } else {
                    if (isset($tree[intval($val['page'])])) {
                        array_push($tree[intval($val['page'])]['children'], array('name' => safe_addslashes($val['profile']), 'icon' => '../vendors/images/l3.png', 'checked' => 'false', 'profileid' => $val['mid']));
                    }
                }
            }
        }
        foreach ($tree as $key => $value) {
            $disply[] =  $value;
        }
        echo json_encode($disply);
    } else {
        echo "Your key has been expired";
    }
}

function PRFL_GetProfileMapData($db, $key, $id)
{
    $key = DASH_ValidateKey($key);
    if ($key) {
        $selectedProfileIds = [];

        $sqlprofile = "select profileid from " . $GLOBALS['PREFIX'] . "profile.profileMap where customerid = $id";
        $sqlprofileres = find_many($sqlprofile, $db);

        foreach ($sqlprofileres as $val) {
            $selectedProfileIds[] = $val['profileid'];
        }
    } else {
        echo "Your key has been expired";
    }

    return $selectedProfileIds;
}


function PRFL_UpdateProfileMap($key, $db, $customerId, $values)
{

    $selected = explode(',', $values);

    foreach ($selected as $key => $val) {
        $return .= '("' . $customerId . '" , "' . $val . '"),';
    }
    $return = rtrim($return, ",");

    $sqldata = "delete from " . $GLOBALS['PREFIX'] . "profile.profileMap where customerid = $customerId";
    redcommand($sqldata, $db);

    $sql = 'insert into ' . $GLOBALS['PREFIX'] . 'profile.profileMap (customerid, profileid) VALUES' . $return;
    $res = redcommand($sql, $db);

    if ($res) {
        $submit =  1;
    } else {

        $sql1 = "select profileid from " . $GLOBALS['PREFIX'] . "profile.profileMap where customerid = $customerId";
        $sql1res = find_many($sql1, $db);

        foreach ($sql1res as $value) {
            $profileid[] = $value['profileid'];
        }

        foreach ($selected as $key => $val) {
            if (in_array($val, $profileid)) {
                $temp = $val;
            } else {
                $temp1 .= '("' . $customerId . '" , "' . $val . '"),';
            }
        }

        $temp1 = rtrim($temp1, ",");
        $sqlupdate = 'insert into ' . $GLOBALS['PREFIX'] . 'profile.profileMap (customerid, profileid) VALUES' . $temp1;
        $res1 = redcommand($sqlupdate, $db);

        if ($res1) {
            $submit = 1;
        } else {
            $submit = 0;
        }
    }

    return $submit;
}

function PRFL_GetProfileDetails($db, $key, $mid, $profile)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select type,page,parentId,sequence from " . $GLOBALS['PREFIX'] . "event.profile where mid = $mid and profile = '$profile'";
        $sqlresult = find_one($sql, $db);
    } else {
        echo "Your key has been expired";
    }

    return $sqlresult;
}

function PRFL_GetParentTileName($db, $key, $page)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select profile from " . $GLOBALS['PREFIX'] . "event.profile where page = $page and parentId = 1 and type = 'L1'";
        $sqlresult = find_one($sql, $db);
    } else {
        echo "Your key has been expired";
    }

    return $sqlresult;
}


function PRFL_EditProfile($db, $key, $menuitem, $dart, $image, $op_sys, $profile, $varvalue, $description, $follow, $sequence, $auth, $mid)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        $desp = safe_addslashes($description);
        $sql = "update " . $GLOBALS['PREFIX'] . "event.profile set menuItem = '$menuitem' , dart = $dart , image = '$image' , OS = '$op_sys' ,"
            . "profile = '$profile' , varValue = '$varvalue' , tileDesc =  '$desp', follow = '$follow' ,"
            . "sequence = '$sequence' , authFalg = '$auth' where mid = $mid";
        $sqlresult = redcommand($sql, $db);
    } else {
        echo "Your key has been expired";
    }

    return $sqlresult;
}

function PRFL_GetProfilePageDetails($db, $key, $page)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select page,profile  from " . $GLOBALS['PREFIX'] . "event.profile where page != $page and page != '' and type = 'L1' group by page order by cast(page as unsigned) asc";
        $sqlresult = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }

    return $sqlresult;
}

function PRFL_GetAddProfilesubmit($key, $db, $menuitem, $dartitem, $image, $op_sys, $profile, $varvalue, $description, $follow, $sequence, $auth, $page, $parentid, $type, $variable)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "insert into " . $GLOBALS['PREFIX'] . "event.profile (menuItem,type,parentId,profile,dart,variable,varValue,shortDesc,image,tileDesc,iconPos,OS,page,lang,status,"
            . "theme,follow,addon,addonDart,authFalg,sequence) value ('$menuitem','$type','$parentid','$profile','$dartitem','$variable','$varvalue',"
            . "'$profile','$image','$description','center','$op_sys','$page','common','enable','1','$follow','null','null','$auth','$sequence')";

        $sqlresult = redcommand($sql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $sqlresult;
}

function PRFL_GetValidateProfile($db, $key, $mid, $page, $menuitem, $type, $profile)
{

    $key = DASH_ValidateKey($key);
    if ($key) {
        if ($mid != '') {

            $sql = "select mid,menuItem,type,parentId,page from " . $GLOBALS['PREFIX'] . "event.profile where page = $page and menuItem = '$menuitem' and profile = '$profile'";
        } else {
            $sql = "select mid,menuItem,type,parentId,page from " . $GLOBALS['PREFIX'] . "event.profile where menuItem = '$menuitem' and type = '$type'";
        }

        $sqlresult = find_one($sql, $db);
    } else {
        echo "Your Key been expired";
    }
    return $sqlresult;
}

function PRFL_GetDeleteProfile($key, $db, $mid, $cid)
{

    $key = DASH_ValidateKey($key);
    if ($key) {

        $sqlprofile = "delete from " . $GLOBALS['PREFIX'] . "event.profile where mid = $mid";
        $sqlresult = redcommand($sqlprofile, $db);

        $sqlprofilemap = "delete from " . $GLOBALS['PREFIX'] . "profile.profileMap where profileid = $mid and customerid = $cid";
        $sqlpmresult = redcommand($sqlprofilemap, $db);
    } else {
        echo "Your Key been expired";
    }

    return $sqlresult;
}

function PRFL_GetSequenceList($key, $db, $id)
{
    $sid = '';

    $key = DASH_ValidateKey($key);
    if ($key) {

        if ($id != '') {
            $sid = "where id in ($id)";
        }

        $sql = "select * from " . $GLOBALS['PREFIX'] . "profile.ConfigurationMaster $sid";
        $sqlresult = find_many($sql, $db);
    } else {
        echo "Your Key been expired";
    }

    return $sqlresult;
}

function PRFL_GetSequenceDetails($key, $db, $cid)
{
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select * from " . $GLOBALS['PREFIX'] . "profile.ConfigurationDetails where cid = $cid";
        $sqlresult = find_many($sql, $db);
    } else {
        echo "Your Key been expired";
    }

    return $sqlresult;
}

function PRFL_GetDBPresent($db)
{

    $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'event'";
    $sqlres = find_one($sql, $db);

    if ($sqlres) {
        $res = 1;
    } else {
        $res = 0;
    }

    return $res;
}

function PRFL_GetProfileExportList($key, $db, $mid)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        $sql = "select * from " . $GLOBALS['PREFIX'] . "event.profile where mid in ($mid) or (type = 'L0')";
        $sqlres = find_many($sql, $db);
    } else {
        echo "Your Key Been expired";
    }

    return $sqlres;
}

function PRFL_GetSequenceQuery($key, $db, $seq)
{
    $db = db_connect();
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sqlsequence = "select * from " . $GLOBALS['PREFIX'] . "profile.ConfigurationMaster where id in ($seq)";
        $sqlsequenceres = find_many($sqlsequence, $db);

        foreach ($sqlsequenceres as $key => $val) {
            $id      .= '' . $val['id'] . ',';
            $return1 .= '(';
            $return1 .= '"' . $val['id'] . '", ';
            $return1 .= '"' . $val['Version'] . '", ';
            $return1 .= '"' . $val['DART'] . '", ';
            $return1 .= '"' . $val['Description'] . '"';
            $return1 .= "),";
        }
        $id      = rtrim($id, ",");
        $return1 = rtrim($return1, ",");
    } else {
        echo "Your Key Been expired";
    }

    return $return1 . "#" . $id;
}

function PRFL_GetSequenceDetailQuery($key, $db, $cid)
{
    $db = db_connect();
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = "select * from " . $GLOBALS['PREFIX'] . "profile.ConfigurationDetails where cid in ($cid) order by cid asc";
        $sqlres = find_many($sql, $db);

        foreach ($sqlres as $key => $val) {
            $return2 .= '(';
            $return2 .= '"' . $val['Id'] . '",';
            $return2 .= '"' . $val['cid'] . '",';
            $return2 .= '"' . $val['Variable'] . '",';
            $return2 .= '"' . $val['VarType'] . '",';
            $return2 .= '"' . str_replace('"', "'", $val['VarValue']) . '",';
            $return2 .= '"' . $val['wn_id'] . '",';
            $return2 .= '"' . $val['def'] . '",';
            $return2 .= '"' . $val['scop'] . '",';
            $return2 .= '"' . $val['pwsc'] . '",';
            $return2 .= '"' . $val['descval'] . '"';
            $return2 .= "),";
        }
        $return2 = rtrim($return2, ",");
    } else {
        echo "Your Key Been expired";
    }

    return $return2;
}

function PRFL_GetServiceLogMasterQuery($key, $db)
{
    $db = db_connect();
    $key = DASH_ValidateKey($key);
    $return = "";
    if ($key) {

        $sql = "select * from " . $GLOBALS['PREFIX'] . "profile.ServiceLog_Master";
        $sqlres = find_many($sql, $db);

        foreach ($sqlres as $key => $val) {
            $return .= '(';
            $return .= '"' . $val['mid'] . '",';
            $return .= '"' . $val['dartNo'] . '",';
            $return .= '"' . $val['tileName'] . '",';
            $return .= '"' . $val['varValues'] . '",';
            $return .= '"' . $val['successDesc'] . '",';
            $return .= '"' . $val['terminateDesc'] . '",';
            $return .= '"' . $val['Type'] . '"';
            $return .= "),";
        }
        $return = rtrim($return, ",");
    } else {
        echo "Your Key Been expired";
    }

    return $return;
}

function PRFL_GetStatusMasterQuery($key, $db)
{
    $db = db_connect();
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = "select sm_id,dart,statusName,isEnabled from " . $GLOBALS['PREFIX'] . "profile.Status_Master";
        $sqlres = find_many($sql, $db);

        foreach ($sqlres as $key => $val) {
            $return .= '(';
            $return .= '"' . $val['sm_id'] . '",';
            $return .= '"' . $val['dart'] . '",';
            $return .= '"' . $val['statusName'] . '",';
            $return .= '"' . $val['isEnabled'] . '"';
            $return .= '),';
        }
        $return = rtrim($return, ",");
    } else {
        echo "Your Key Been expired";
    }
    return $return;
}

function PRFL_GetStatusDetailQuery($key, $db)
{
    $db = db_connect();
    $key = DASH_ValidateKey($key);
    $return  = "";
    if ($key) {

        $sql = "select * from " . $GLOBALS['PREFIX'] . "profile.Status_Details";
        $sqlres = find_many($sql, $db);

        foreach ($sqlres as $key => $val) {
            $return .= '(';
            $return .= '"' . $val['sd_id'] . '",';
            $return .= '"' . $val['page'] . '",';
            $return .= '"' . $val['profile'] . '",';
            $return .= '"' . $val['varValues'] . '",';
            $return .= '"' . $val['variable'] . '",';
            $return .= '"' . $val['dartfrom'] . '",';
            $return .= '"' . $val['dartToExecute'] . '",';
            $return .= '"' . $val['description'] . '",';
            $return .= '"' . $val['logicType'] . '",';
            $return .= '"' . $val['logicPara'] . '",';
            $return .= '"' . $val['dispBtn'] . '",';
            $return .= '"' . $val['url'] . '",';
            $return .= '"' . $val['status'] . '",';
            $return .= '"' . $val['title'] . '",';
            $return .= '"' . $val['parent'] . '",';
            $return .= '"' . $val['UISection'] . '",';
            $return .= '"' . $val['GUIType'] . '",';
            $return .= '"' . $val['addCss'] . '",';
            $return .= '"' . $val['functionToCall'] . '",';
            $return .= '"' . $val['ImageFileName'] . '",';
            $return .= '"' . $val['usageType'] . '"';
            $return .= '),';
        }
        $return = rtrim($return, ",");
    } else {
        echo "Your Key Been expired";
    }

    return $return;
}

function PRFL_GetConfigurationSubmit($db, $key, $version, $dart, $description)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        $sqlid = "select max(id) as id from " . $GLOBALS['PREFIX'] . "profile.ConfigurationMaster";
        $sqlresid = find_one($sqlid, $db);
        $id = $sqlresid['id'] + 1;

        $sql = "insert into " . $GLOBALS['PREFIX'] . "profile.ConfigurationMaster(id,Version,DART,Description) values ($id,'$version',$dart,'$description')";
        $sqlres = redcommand($sql, $db);
    } else {
        echo "Your Key Been expired";
    }

    return $sqlres;
}

function PRFL_GetConfigurationDetailSubmit($db, $key, $cid, $varibale, $vartype, $varvalue, $scope, $desval)
{
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sqlid = "select max(id) as id from " . $GLOBALS['PREFIX'] . "profile.ConfigurationDetails";
        $sqlresid = find_one($sqlid, $db);
        $id = $sqlresid['id'] + 1;

        $def = 0;
        $pwsc = 0;
        $sql = "insert into " . $GLOBALS['PREFIX'] . "profile.ConfigurationDetails(id,cid,Variable,VarType,VarValue,def,scop,pwsc,descval) values ($id,$cid,'$varibale','$vartype','$varvalue',$def,'$scope',$pwsc,'$desval')";

        $sqlres = redcommand($sql, $db);
    } else {
        echo "Your Key Been expired";
    }
    return $sqlres;
}

function PRFL_GetConfigurationMasterValues($db, $key, $version, $dart, $description)
{
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = "select id from " . $GLOBALS['PREFIX'] . "profile.ConfigurationMaster where Version = '$version' and DART = $dart and Description = '$description'";
        $sqlres = find_one($sql, $db);
    } else {
        echo "Your Key Been expired";
    }

    return $sqlres;
}

function PRFL_UpdateConfigMaster($key, $db, $id, $version, $dart, $descrip)
{
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = "update " . $GLOBALS['PREFIX'] . "profile.ConfigurationMaster set Version = '$version' , DART = '$dart' , Description = '$descrip' where id = $id";
        $sqlres = redcommand($sql, $db);
    } else {
        echo "Your Key Been expired";
    }

    return $sqlres;
}

function PRFL_GetSequenceDetailslist($key, $db, $id)
{
    $key = DASH_ValidateKey($key);

    if ($key) {
        $sql = "select Id,VarType,VarValue,descval from " . $GLOBALS['PREFIX'] . "profile.ConfigurationDetails where id = $id";
        $sqlres = find_one($sql, $db);
    } else {
        echo "Your Key Been expired";
    }
    return $sqlres;
}

function PRFL_GetUpdateSequence($key, $db, $id, $vartype, $descval, $varvalue)
{
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = "update " . $GLOBALS['PREFIX'] . "profile.ConfigurationDetails set VarType = $vartype, VarValue = '$varvalue', descval = '$descval' where id = $id";
        $sqlres = redcommand($sql, $db);
    } else {
        echo "Your Key Been expired";
    }

    return $sqlres;
}

function PRFL_GetServiceLogMasterValues($key, $db, $tilename, $sucssdesc, $termdesc, $dart, $type, $varValues)
{
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = "select max(CAST(mid AS SIGNED)) as mid from " . $GLOBALS['PREFIX'] . "profile.ServiceLog_Master limit 1";
        $sqlres = find_one($sql, $db);
        $mid = $sqlres['mid'] + 1;

        $sqlinsert = "insert into " . $GLOBALS['PREFIX'] . "profile.ServiceLog_Master(mid,dartNo,tileName,varValues,successDesc,terminateDesc,Type) values ('$mid', $dart,'$tilename','$varValues','$sucssdesc','$termdesc','$type')";
        redcommand($sqlinsert, $db);
    } else {
        echo "Your Key Been expired";
    }
}

function PRFL_GetvarValues($key, $db, $varvalue)
{
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = "select mid,tilename,successDesc,terminateDesc from " . $GLOBALS['PREFIX'] . "profile.ServiceLog_Master where varValues = '$varvalue' limit 1";
        $sqlres = find_one($sql, $db);
    } else {
        echo "Your Key has been expired";
    }

    return $sqlres;
}

function PRFL_GetserviceLogUpdate($key, $db, $mid, $tilename, $success, $terminate)
{
    $key = DASH_ValidateKey($key);

    if ($key) {

        $sql = "update " . $GLOBALS['PREFIX'] . "profile.ServiceLog_Master set tileName = '$tilename', successDesc = '$success', terminateDesc = '$terminate' where mid = $mid";
        $sqlres = redcommand($sql, $db);
    } else {
        echo "Your Key has been expired";
    }
    return $sqlres;
}
