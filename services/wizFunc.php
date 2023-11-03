<?php

//error_reporting(-1);
//ini_set('display_errors', 'On');

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../lib/l-dbConnect.php';
require_once '../include/common_functions.php';
require_once '../lib/l-rcmd.php';
require_once '../lib/l-user.php';
require_once '../layout/rolesValues.php';

nhRole::dieIfnoRoles(['profilewizard']); // roles: profilewizard

//Replace $routes['post'] with if else
if (url::postToText('function') === 'parent_Description') { // roles: profilewizard
    parent_Description();
} else if (url::postToText('function') === 'child_Description') { // roles: profilewizard
    child_Description();
} else if (url::postToText('function') === 'subchild_Description') { // roles: profilewizard
    subchild_Description();
}

function handle_sql_errors($query, $error_message)
{
    echo '<pre>';
    echo $query;
    echo '</pre>';
    echo $error_message;
    die;
}

function parent_Description()
{

    $db = pdo_connect();
    $parent_role = url::issetInRequest('parent_role') ? url::requestToText('parent_role') : '';
    $parent_name = url::issetInRequest('parent_name') ? url::requestToText('parent_name') : '';
    $logo_class = url::issetInRequest('logo_class') ? url::requestToText('logo_class') : '';
    $Id = url::issetInRequest('Id') ? url::requestToText('Id') : '';
    $parent_description = url::issetInRequest('parent_description') ? url::requestToText('parent_description') : '';
    $q = "SELECT parent_role,parent_name,Id,logo_class,parent_description FROM " . $GLOBALS['PREFIX'] . "profile.WizardNameNH WHERE profile_type = 1 GROUP BY parent_name ORDER BY CASE parent_name
WHEN 'Problem Automation' THEN 1
WHEN 'Device Management' THEN 2
WHEN 'Device Policies' THEN 3
WHEN 'System Management' THEN 4
WHEN 'Software Update' THEN 5
WHEN 'Proactive Resolution' THEN 6
WHEN 'Others' THEN 7
END";
    try {
        // mysqli_query($db, "SET SESSION sql_mode = 'TRADITIONAL'");
        $sql = $db->prepare($q);
        $sql->execute();
        $sqlRes = $sql->fetchAll();
    } catch (PDOException $e) {
        handle_sql_errors($q, $e->getMessage());
    }

    $tileDesc = '';
    $cnt = safe_count($sqlRes);
    $html = '';
    //  print_r($sqlRes);
    foreach ($sqlRes as $key => $val) {
        $i = $key + 1;
        $parent_name = $val['parent_name'];
        $logo_class = $val['logo_class'];
        $parent_description = $val['parent_description'];
        $parent_role = $val['parent_role'];
        $class = setRoleForAnchorTag($parent_role, 2);
        $html .= '<li class="nav-item innTab" id="tab' . $i . '" ><input type="hidden" id="' . $parent_name . '">
        <a class="nav-link toolTip ' . $class . '" data-bs-toggle="tab" href="#" onclick="childDesc(\'' . $parent_name . '\')">
        <div class="tooltip">
        <img id="prnt_img" src="../assets/img/services/' . $logo_class . '.svg" /><span class="tooltext"> ' . $parent_name . '</span>
                <div class="middle">
                    <div class="text">' . $parent_description . '</div>
                </div>
        </div></a></li>';
    }

    $html .= '<li class="nav-item navItem" id="tab_1" style="display:none">
                <a class="nav-link toolTip" data-bs-toggle="tab" href="#" onclick="backoption()">
        <div class="tooltip">
        <img id="prnt_img" src="../assets/img/services/' . $logo_class . '.svg"/><span class="tooltext"></span>
                <div class="middle">
                    <div class="text">Click to go to Previous Section</div>
                </div>
        </div></a></li>';
    $auditRes = create_auditLog('Services', 'View', 'Success');
    echo $html;
}

//Commented [Donot Delete]
// function child_Description()
// {
//     $db = pdo_connect();
//     $parent_name = url::issetInRequest('name') ? url::requestToText('name') : '';

//     // $sql = $db->prepare("SELECT child_role,parent_name,Id,Name,description,logo_child_class FROM profile.WizardNameNH WHERE parent_name =?");
//     // $sql->execute([$parent_name]);
//     // $sqlRes = $sql->fetchAll();

//     $sql = $db->prepare("SELECT child_role,parent_name,Id,Name,description,logo_child_class FROM profile.WizardNameNH WHERE parent_name =? and child_role != 'otherConfigs' and child_role != 'messageConfiguration' order by Name");
//     $sql->execute([$parent_name]);
//     $sqlRes = $sql->fetchAll();

//     $cnt = safe_count($sqlRes);
//     $html = '';
//     foreach ($sqlRes as $key => $val) {
//         $j = $key + 1;
//         $parent_name = $val['parent_name'];
//         $logo_child_class = $val['logo_child_class'];
//         $description = $val['description'];
//         $Name = $val['Name'];
//         $Id = $val['Id'];
//         $parent_description = $val['parent_description'];
//         $childrole = $val['child_role'];
//         $class = setRoleForAnchorTag($childrole, 2);
//         if ($Name == 'Message Configuration') {
//             $html .= '<li class="nav-item"><input type="hidden" id="' . $Name . '">
//                 <a class="nav-link toolTip enableAnchorTag" data-bs-toggle="tab" href="../custom/config_browser.php" onclick=reDirectToMsgConfig()>
//                     <img id="prnt_img" src="../assets/img/services/' . $logo_child_class . '.svg" /><span class="tooltext"> ' . $Name . '</span>
//                     <div class="middle">
//                         <div class="text">' . $description . '</div>
//                     </div>
//                 </a>
//             </li>';
//         } else {
//             $html .= '<li class="nav-item"><input type="hidden" id="' . $Name . '">
//             <a class="nav-link toolTip ' . $class . '" data-bs-toggle="tab" href="#link" onclick="subchildDesc(\'' . $Id . '\')">
//                 <img id="prnt_img" src="../assets/img/services/' . $logo_child_class . '.svg" /><span class="tooltext"> ' . $Name . '</span>
//                 <div class="middle">
//                     <div class="text">' . $description . '</div>
//                 </div>
//             </a>
//         </li>';
//         }
//     }
//     echo $html;
// }

function child_Description()
{
    $db = pdo_connect();
    $parent_name = url::issetInRequest('name') ? url::requestToText('name') : '';

    $sql = $db->prepare("SELECT child_role,parent_name,Id,Name,description,logo_child_class,parent_description FROM " . $GLOBALS['PREFIX'] . "profile.WizardNameNH WHERE parent_name =? and child_role != 'otherConfigs' and child_role != 'messageConfiguration' order by Name");
    $sql->execute([$parent_name]);
    $sqlRes = $sql->fetchAll();

    $cnt = safe_count($sqlRes);
    $html = '';
    foreach ($sqlRes as $key => $val) {
        $j = $key + 1;
        $parent_name = $val['parent_name'];
        $logo_child_class = $val['logo_child_class'];
        $description = $val['description'];
        $Name = $val['Name'];
        $Id = $val['Id'];
        $parent_description = $val['parent_description'];
        $childrole = $val['child_role'];
        $class = setRoleForAnchorTag($childrole, 2);
        if ($Name == 'Message Configuration') {
            $html .= '<li class="nav-item"><input type="hidden" id="' . $Name . '">
                <a class="nav-link toolTip enableAnchorTag" data-bs-toggle="tab" href="../custom/config_browser.php" onclick=reDirectToMsgConfig()>
                    <img id="prnt_img" src="../assets/img/services/' . $logo_child_class . '.svg" /><span class="tooltext"> ' . $Name . '</span>
                </a>
            </li>';
        } else {
            $html .= '<li class="nav-item"><input type="hidden" id="' . $Name . '">
            <a class="nav-link toolTip ' . $class . '" data-bs-toggle="tab" href="#link" onclick="subchildDesc(\'' . $Id . '\')">
                <img id="prnt_img" src="../assets/img/services/' . $logo_child_class . '.svg" /><span class="tooltext"> ' . $Name . '</span>
            </a>
        </li>';
        }
    }
    echo $html;
}

function subchild_Description()
{
    $db = pdo_connect();
    $html = '';
    $Id = url::issetInRequest('id') ? url::requestToInt('id') : '';
    $search = url::requestToText('search');

    if(isset($_SESSION['searchType']) && $_SESSION['searchType'] !== null){
        $searchType = trim($_SESSION['searchType']);
    }else{
        $searchType = '';
    }

    if(isset($_SESSION['searchValue']) && $_SESSION['searchValue'] !== null){
        $searchValue = trim($_SESSION['searchValue']);
    }else{
        $searchValue = '';
    }

    if(isset($_SESSION['rparentName']) && $_SESSION['rparentName'] !== null){
        $rparentValue = trim($_SESSION['rparentName']);
    }else{
        $rparentValue = '';
    }

    $sqlRes = NanoDB::find_one("select profiles from " . $GLOBALS['PREFIX'] . "profile.WizardNameNH wn where  wn.Id=?", [$Id]);

    $profile = $sqlRes['profiles'];
    $arr = explode(',', $profile);
    $in = str_repeat('?,', safe_count($arr) - 1) . '?';
    $sql = "select DartNo,MasterId,VarID,Name,GUIType from " . $GLOBALS['PREFIX'] . "profile.WizardMasterNH where DartNo in ($in) and GUIType='checkbox' group by DartNo";

    $resWN = null;

    if ($search != '') {
        $profile = $sqlRes['profiles'];
        $arr = explode(',', $profile);
        $in = str_repeat('?,', safe_count($arr) - 1) . '?';
        $sql = "select DartNo,MasterId,VarID,Name,GUIType from " . $GLOBALS['PREFIX'] . "profile.WizardMasterNH where DartNo in ($in) and GUIType='checkbox' and Name like '%$search%' group by DartNo";
    }

    $sqlV2 = "select w.*, jid.id as Jid  from ($sql) as w left join (
        select id,dartno from " . $GLOBALS['PREFIX'] . "profile.varidJsonSchema where jsonschema like '%Enable this DART%'
    ) as jid on jid.dartno=w.DartNo";
    $resWN =  NanoDB::find_many($sqlV2, $arr);

    $jsonDarts=[];
    $headers = getallheaders();
    if ($resWN) {
        foreach ($resWN as $key => $val) {
            $DartNo = $val['DartNo'];
            $Name = $val['Name'];

            // $sqlResult = NanoDB::find_one("select id from " . $GLOBALS['PREFIX'] . "profile.varidJsonSchema where jsonschema like '%Enable this DART%' and dartno=? limit 1", [$DartNo]);
            // $Jid = $sqlResult['id'];
            $Jid = $val['Jid'];

            if ($Jid) {
                $SQLResult = NanoDB::find_one("select jsondata from " . $GLOBALS['PREFIX'] . "profile.varidJsonData where Scope=? and ScopeVal=? and dartNo=? and jid=? limit 1", [$searchType, $searchValue, $DartNo, $Jid]);

                if (!$SQLResult) {
                    $text = 'Not-Configured';
                } else {
                    $jsonData = $SQLResult['jsondata'];
                    $jsonData = str_replace('\"', '', $jsonData);
                    if ($jsonData == 0 || $jsonData == '0') {
                        $text = 'Not-Configured';
                    } else {
                        $text = 'Configured';
                    }
                }
            } else {
                $sqlq1  = "select * from " . $GLOBALS['PREFIX'] . "audit.Audit where site=?  and detail like ? limit 1";
                $sqlResq1 =  null;
                if ($searchType == 'Groups') {
                    $sqlResq1 =   NanoDB::find_one($sqlq1, [$rparentValue, "%$DartNo%"]);
                } else {
                    $sqlResq1 =   NanoDB::find_one($sqlq1, [$searchValue, "%$DartNo%"]);
                }

                $text = '';
                if (!!$sqlResq1) {
                    $text = 'Configured';
                } else {
                    $text = 'Not-Configured';
                }
            }

          if ($headers['Accept'] == 'application/json'){
            $stype = url::requestToText('type');
            $sname = url::requestToText('name');
            $searchConfigSQL = "select * from " . $GLOBALS['PREFIX'] . "core.DartsConfigJson where type=? and name=? and dartid=?";
            $searchResult = NanoDB::find_one($searchConfigSQL, [$stype,$sname,$DartNo]);
            if ($searchResult){
              $text = 'Configured';
            }else{
              $text = 'Not-Configured';
            }
            array_push($jsonDarts,['name'=>$Name,'DartNo'=>$DartNo,'status'=>$text]);
          }else{ 
            $tempName = "";
            if($Name !== null && $Name != ''){
                $tempName = trim($Name);
            }
            $html .= '<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12  Box" >
                    <div class="card" id="subchild_desc_box" >
                        <div class="card-header">
                            <h5><span class="cardHead">' . $Name . '</span></h5>
                        </div>
                        <div class="card-body field" id="desc_subchild">
                            <p>Status : <span data-qa="' . str_replace(" ", '', $Name) . '" class="config rightslide-container-hand" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="config-container" onclick="configDesc(\'' . $tempName . '\',\'' . $DartNo . '\');">' . $text . '</span></p>
                            <p><span class="txt">Description</span></p>
                        </div>
                    </div>
                </div>';
            }
        }
    }
  if ($headers['Accept'] == 'application/json'){
    header("Content-type: application/json; charset=utf-8");
    $jsonDarts = json_encode($jsonDarts);
    echo $jsonDarts;
  }else{
    echo $html;
  }
}
