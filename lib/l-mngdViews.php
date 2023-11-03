<?php

$global = array();

function get_reportdata_json_db($filename, $reportType, $db)
{
    $sql = "SELECT json FROM " . $GLOBALS['PREFIX'] . "report.InfPortal WHERE filename = '$filename' and type = $reportType";
    $res = find_one($sql, $db);
    return $res['json'];
}

function getReportEditData($viaAjax)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);

    $sectionName = [];
    $reportName = url::requestToAny('name');
    $reportType = url::requestToAny('reportType');
    $id         = url::requestToAny('id');
    $sql        = "SELECT id,global,envglobal,status,infportal,emaillist,include FROM " . $GLOBALS['PREFIX'] . "report.ManagedReport WHERE id = $id and type = $reportType";

    $res        = find_one($sql, $db);
    if (safe_count($res)) {
        $report_Data['id']          = $res['id'];
        $report_Data['global']      = $res['global'];
        $report_Data['envglobal']   = $res['envglobal'];
        $report_Data['status']      = $res['status'];
        $report_Data['infportal']   = $res['infportal'];
        $report_Data['emaillist']   = $res['emaillist'];
        $report_Data['include']     = all_mach_grps($res['include'], $db);

        $sqlSec = "SELECT sectionid FROM " . $GLOBALS['PREFIX'] . "report.ManagedReportMap WHERE reportid = " . $res['id'];
        $result = find_many($sqlSec, $db);

        if (safe_count($result)) {
            foreach ($result as $key => $val) {
                $sectionIds[] = $val['sectionid'];
            }
        }

        $report_Data['sections'] =  get_sections($sectionIds, $db);

        $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.Schedule WHERE reportid =" . $res['id'];
        $sch = find_one($sql, $db);

        $report_Data['schedtype']   = $sch['schedtype'];
        $report_Data['mnthday']     = $sch['mnthday'];
        $report_Data['weekday']     = $sch['weekday'];
        $report_Data['hour']        = $sch['hour'];
        $report_Data['min']         = $sch['min'];
    }

    echo json_encode($report_Data);
}

function get_sections($includes, $db)
{
    $sql = "SELECT id,name FROM " . $GLOBALS['PREFIX'] . "report.ManagedSection";
    $result = find_many($sql, $db);
    foreach ($result as $key => $val) {
        if (in_array($val['id'], $includes)) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        $sectionOpt .= "<option value='" . $val['id'] . "' $selected >" . $val['name'] . "</option>";
    }
    return $result;
}

function createReportView($viewDetails, $viewName, $db)
{
    $userid = $_SESSION['user']['userid'];
    $username = $_SESSION['user']['username'];
    $envGlobal = "0";
    $global = "1";
    $now = time();
    $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "report.`ManagedReport` (`name`, `username`, `userid`, `global`, `envglobal`, "
        . "`status`, `include`, `created`, `lastrun`, `nextrun`, `emaillist`, `infportal`, `type`, "
        . "`savedConfig`) VALUES ('" . $viewName . "', '$username', '$userid', '$global', '$envGlobal', 1, '', '$now', "
        . "0, 0, '-', 0, 0, NULL)";
    $res = redcommand($sql, $db);
    $repId = mysql_insert_id($db);

    $sql1 = "INSERT INTO " . $GLOBALS['PREFIX'] . "report.`ManagedReportMap` (`reportid`, `sectionid`, `gridEnabled`, `chartEnabled`, `chartType`) VALUES ";
    foreach ($viewDetails as $key => $value) {
        $gridEnabled = ($value['grid'] != "") ? $value['grid'] : 0;
        $chartEnabled = ($value['chart'] != "") ? $value['chart'] : 0;
        if ($chartEnabled == 1 || $chartEnabled == "1") {
            $chartType = ($value['charttype'] != "") ? $value['charttype'] : 1;
        } else {
            $chartType = 0;
        }

        $sql1 .= "('$repId', '" . $value['secId'] . "', '" . $gridEnabled . "', '" . $chartEnabled . "', '" . $chartType . "'),";
    }

    $sql1 = rtrim($sql1, ",");
    $res1 = redcommand($sql1, $db);
}

function getAssetQueries($viaAjax)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'asset', $db);

    $sql = "SELECT id,name,displayfields FROM " . $GLOBALS['PREFIX'] . "asset.AssetSearches";

    $result = find_many($sql, $db);
    $i = 0;
    $return[0]['groupby'] = "{";
    foreach ($result as $key => $val) {
        $return[$i]['id'] = $val['id'];
        $return[$i]['name'] = $val['name'];
        $return[0]['groupby'] .= '"' . $val['id'] . '":"' . preg_replace('/[^A-Za-z0-9\. -:]/', '', $val['displayfields']) . '",';
        $i++;
    }
    if ($viaAjax) {
        $return[0]['groupby'] = rtrim($return[0]['groupby'], ",") . "}";
        echo json_encode($return);
    } else {
        $return[0]['groupby'] = rtrim($return[0]['groupby'], ",") . "}";
        return $return;
    }
}

function getEventFilters($viaAjax)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'event', $db);

    $sql = "SELECT id,name,eventtag FROM " . $GLOBALS['PREFIX'] . "event.SavedSearches where eventtag !=''";
    $result = find_many($sql, $db);
    $i = 0;

    foreach ($result as $key => $val) {
        $return[$i]['id'] = $val['id'];
        $return[$i]['name'] = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($val['name']));
        $return[$i]['etag'] = $val['eventtag'];
        $i++;
    }
    $return[0]['groupby'] = '{"val":"Machine:Site:User Name:Scrip:Executable:Window Title"}';
    if ($viaAjax) {
        echo json_encode($return);
    } else {
        return $return;
    }
}

function getPatchDetails($viaAjax)
{
    $return = [];
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'event', $db);
    global $global;
    if ($viaAjax) {
        $startDate = url::issetInRequest('startDate') ? url::requestToAny('startDate') : date('m/01/Y');;
        $endDate = url::issetInRequest('endDate') ? url::requestToAny('endDate') : date('m/d/Y');
    } else {
        $startDate = date('m/01/Y');
        $endDate = date('m/t/Y');
    }

    $where = " date BETWEEN '" . strtotime($startDate) . "' AND '" . strtotime($endDate) . "'";

    $sql = "SELECT patchid,name FROM " . $GLOBALS['PREFIX'] . "softinst.Patches WHERE $where";
    $result = find_many($sql, $db);
    $i = 0;
    foreach ($result as $key => $val) {
        $return[$i]['id'] = $val['patchid'];
        $return[$i]['name'] = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($val['name']));
        $i++;
    }
    if ($viaAjax) {
        echo json_encode($return);
    } else {
        return $return;
    }
}

function fetch_report_variables($reportId, $db)
{
    $sql = "SELECT global,envglobal,infportal,userid,type FROM " . $GLOBALS['PREFIX'] . "report.ManagedReport WHERE id = $reportId";
    $res = find_one($sql, $db);
    $report_variables['global'] = $res['global'];
    $report_variables['envglobal'] = $res['envglobal'];
    $report_variables['infportal'] = $res['infportal'];
    $report_variables['owner'] = $res['userid'];
    $report_variables['type'] = $res['type'];
    return $report_variables;
}



function fetch_users($global, $superGlobal, $infPortal, $owner, $db)
{

    if ($superGlobal && $infPortal) {
        $users = fetch_all_users($db);
    } else {
        if ($global) {
            $users = fetch_all_users_siblings_new($owner, $db);
        } else {
            $users = array($owner);
        }
    }
    return $users;
}

function fetch_all_users_siblings_new($owner, $db)
{
    $sql = "SELECT ch_id FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE userid = $owner";
    $result = find_one($sql, $db);
    $sql = "SELECT userid FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE ch_id =" . $result['ch_id'];
    $result = find_many($sql, $db);
    foreach ($result as $value) {
        $return[] = $value['userid'];
    }
    return $return;
}

function fetch_all_users($db)
{
    $sql = "SELECT userid FROM " . $GLOBALS['PREFIX'] . "core.Users";
    $result = find_many($sql, $db);
    foreach ($result as $value) {
        $return[] = $value['userid'];
    }
    return $return;
}

function checkAuth($viaAjax)
{
    $id = url::requestToAny('id');
    $db = db_connect();
    $sql = "SELECT userid FROM " . $GLOBALS['PREFIX'] . "report.ManagedReport WHERE id = $id";
    $res = find_one($sql, $db);
    if ($res['userid'] == $_SESSION['user']['adminid']) {
        $return['auth'] = 'yes';
    } else {
        $return['auth'] = 'no';
    }
    echo json_encode($return);
}

function deleteReport($viaAjax)
{
    $id     = url::requestToAny('id');
    $type   = url::requestToAny('type');
    $db     = db_connect();
    $sql    = "DELETE FROM " . $GLOBALS['PREFIX'] . "report.ManagedReport WHERE id = $id and type = $type";
    redcommand($sql, $db);
    $return['status'] = 'done';
    echo json_encode($return);
}

function deleteFiles($names)
{
    $db     = db_connect();
    $sql    = "DELETE FROM " . $GLOBALS['PREFIX'] . "report.InfPortal WHERE filename in (" . rtrim($names, ',') . ")";
    redcommand($sql, $db);
    $ids = explode(',', $names);
    foreach ($ids as $value) {
        if ($value != '')
            unlink("../insights/files/" . trim($value, '"') . "xls");
    }
}

function deleteReportFile($viaAjax)
{
    $id     = url::requestToAny('id');
    deleteFiles($id);
    $return['status'] = 'done';
    echo json_encode($return);
}

function runNow($viaAjax)
{
    $now = time();
    $db = db_connect();
    $reportName = url::requestToAny('name');
    $type = url::requestToAny('reportType');
    $id   = url::requestToAny('id');
    $sql = "SELECT id FROM " . $GLOBALS['PREFIX'] . "report.ManagedReport WHERE id = $id and type = $type";
    $res = find_one($sql, $db);
    $reportId = $res['id'];
    make_entry_in_queue($reportId, $now, 1, $db);
}

function all_mach_grps($include, $db)
{
    $sql = "SELECT customer FROM " . $GLOBALS['PREFIX'] . "core.Customers WHERE username ='" . $_SESSION['user']['username'] . "'";
    $result = find_many($sql, $db);
    $includes = explode(",", $include);
    foreach ($result as $key => $val) {
        if (in_array($val['customer'], $includes)) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        $machOpt .= "<option value='" . $val['customer'] . "' $selected >" . UTIL_GetTrimmedGroupName($val['customer']) . "</option>";
    }
    $selected = '';
    if (in_array('All', $includes)) {
        $selected = 'selected';
    }
    $machOpt .= "<option value='All' $selected >All</option>";
    return $machOpt;
}

function formatSlctOptns($data)
{
    foreach ($data as $value) {
        $opts .= '<option value="' . $value['id'] . '" title="' . safe_addslashes($value['name']) . '">' . safe_addslashes($value['name']) . '</option>';
    }
    return $opts;
}

function getReportPostValues()
{
    $data = safe_json_decode(file_get_contents('php://input'), true);
    return $data;
}

function check_existing($reportName, $type, $db)
{
    $username = $_SESSION['user']['username'];

    $sql = "SELECT id FROM " . $GLOBALS['PREFIX'] . "report.ManagedReport "
        . " WHERE (name = '$reportName' and type "
        . " = $type and username = '$username')"
        . " or (name = '$reportName' and type"
        . " = $type and global = 1)"
        . " or (name = '$reportName' and type"
        . " = $type and envglobal = 1)";

    $reprtnm = find_one($sql, $db);
    if ($reprtnm['id'] != '') {
        return $reprtnm['id'];
    } else {
        return 0;
    }
}



function next_cycle($schedData, $now)
{
    date_default_timezone_set('UTC');
    $tmp    = getdate($now);
    $cycle  = $schedData[0];
    $rhh    = $schedData[3];
    $rmm    = $schedData[4];
    $nhh    = $tmp['hours'];
    $nmm    = $tmp['minutes'];
    $yyy    = $tmp['year'];
    $mon    = $tmp['mon'];
    $day    = $tmp['mday'];
    $when   = mktime($rhh, $rmm, 0, $mon, $day, $yyy);
    if ($cycle == 2) {
        $next = ($now < $when) ? $when : $when + 86400;
    }
    if ($cycle == 3) {
        $nwday = $tmp['wday'];
        $rwday = $schedData[2];
        $when  = $when - ($nwday * 86400);
        $when  = $when + ($rwday * 86400);
        $next  = ($now < $when) ? $when : $when + (7 * 86400);
    }
    if ($cycle == 4) {
        $mday = $schedData[1];
        if ($mday > 28) $mday = 28;
        $when = mktime($rhh, $rmm, 0, $mon, $mday, $yyy);
        if ($when < $now) {
            if ($mon < 12) {
                $mon++;
            } else {
                $mon = 1;
                $yyy++;
            }
            $when = mktime($rhh, $rmm, 0, $mon, $mday, $yyy);
        }
        $next = $when;
    }
    return $next;
}

function make_entry_in_queue($reportId, $now, $priority, $db)
{

    $report_variables   = fetch_report_variables($reportId, $db);
    $global             = $report_variables['global'];
    $superGlobal        = $report_variables['envglobal'];
    $infPortal          = $report_variables['infportal'];
    $owner              = $report_variables['owner'];
    $type               = $report_variables['type'];


    $users = fetch_users($global, $superGlobal, $infPortal, $owner, $db);

    $sql = "INSERT into " . $GLOBALS['PREFIX'] . "report.Queue (reportid,intime,status,priority,userid,type) values ";
    foreach ($users as $value) {
        $values .= "(" . $reportId . ",$now,0,$priority,$value,$type),";
    }
    $sqlInsert = $sql . rtrim($values, ",");
    redcommand($sqlInsert, $db);
}

function update_next_run($reportId, $schedData, $db)
{
    $now = time();
    $type = $schedData[0];
    switch ($type) {
        case '1':
            make_entry_in_queue($reportId, $now, 1, $db);
            $next_run = 0;
            break;
        case '2':
        case '3':
        case '4':
            $next_run = next_cycle($schedData, $now);
            break;
        default:
            break;
    }
    $sql = "UPDATE " . $GLOBALS['PREFIX'] . "report.ManagedReport set nextrun = $next_run WHERE id = $reportId";
    redcommand($sql, $db);
}

function addReport($viaAjax)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);
    $postData   = getReportPostValues();
    $reportName = $postData['reportName'];
    $global     = $postData['reportGlobal'];
    $envGlobal  = $postData['envGlobal'];
    $dest       = explode(",", $postData['destination']);
    $infPortal  = $postData['infPortal'];
    $emailList  = $postData['emailList'];
    $include    = $postData['includeMachGrp'];
    $type       = $postData['type'];
    $enable     = $postData['enabled'];
    $sectionIds = $postData['sections'];
    $userName   = $_SESSION['user']['username'];
    $userId     = $_SESSION['user']['adminid'];
    $schedData  = $postData['schedData'];
    $created    = time();
    $exist      = check_existing($reportName, $type, $db);
    if ($exist == 0) {
        $sql    = "INSERT into " . $GLOBALS['PREFIX'] . "report.ManagedReport (name,username,userid,global,envglobal,status,include,created,lastrun,nextrun,emaillist,infportal,type) values ('" . safe_addslashes($reportName) . "','$userName',$userId,$global,$envGlobal,$enable,'$include',$created,0,0,'$emailList'," . $infPortal . ",$type)";
        $res1   = redcommand($sql, $db);
        $reportId = inserted_id($db);
        if ($res1) {
            set_Sched($reportId, $schedData, FALSE, $db);
            $res2 = set_report_map($sectionIds, $reportId, $db);
            update_next_run($reportId, $schedData, $db);
            if ($res2) {
                $res['status'] = "<span>Report Added Successfully</sapn>";
                echo json_encode($res);
            } else {
                $res['status'] = "<span>Report Addition Failed</span>";
                echo json_encode($res);
            }
        } else {
            $res['status'] = "<span>Report Addition Failed</span>";
            echo json_encode($res);
        }
    } else {
        $res['status'] = "<span>Report Already Exists</span>";
        echo json_encode($res);
    }
}

function editReport($viaAjax)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);
    $postData   = getReportPostValues();
    $reportName = $postData['reportName'];
    $global     = $postData['reportGlobal'];
    $envGlobal  = $postData['envGlobal'];
    $dest       = explode(",", $postData['destination']);
    $infPortal  = $postData['infPortal'];
    $emailList  = $postData['emailList'];
    $include    = $postData['includeMachGrp'];
    $enable     = $postData['enabled'];
    $schedData  = $postData['schedData'];
    $sectionIds = $postData['sections'];
    $type       = $postData['type'];
    $id         = url::requestToAny('id');
    $created    = time();
    $exist      = check_existing($reportName, $type, $db);
    if ($exist == 0 || $exist == $id) {
        $sql = "UPDATE  " . $GLOBALS['PREFIX'] . "report.ManagedReport"
            . " set "
            . " name = '$reportName',"
            . " global = $global,"
            . " envglobal = $envGlobal,"
            . " created = $created,"
            . " include = '$include',"
            . " emaillist = '$emailList',"
            . " infportal = $infPortal,"
            . " status = $enable"
            . " WHERE id = " . $id;
        $res1 = redcommand($sql, $db);
        unsetReportMapData($id, $db);
        set_Sched($id, $schedData, TRUE, $db);
        $res2 = set_report_map($sectionIds, $id, $db);
        update_next_run($id, $schedData, $db);
        if ($res2) {
            $res['status'] = "<span>Report Edited Successfully</span>";
            echo json_encode($res);
        } else {
            $res['status'] = "<span>Report Edition Failed</span>";
            echo json_encode($res);
        }
    } else {
        $res['status'] = "<span>Report Name Already Exists</span>";
        echo json_encode($res);
    }
}

function set_Sched($reportId, $schedData, $update, $db)
{
    if ($update) {
        $sql = "UPDATE  " . $GLOBALS['PREFIX'] . "report.Schedule"
            . " set "
            . " schedtype = $schedData[0],"
            . " mnthday = $schedData[1],"
            . " weekday = $schedData[2],"
            . " hour = $schedData[3],"
            . " min = $schedData[4]"
            . " WHERE reportid = " . $reportId;
    } else {
        $sql = "INSERT into " . $GLOBALS['PREFIX'] . "report.Schedule (reportid,schedtype,mnthday,weekday,hour,min) values ($reportId,$schedData[0],$schedData[1],$schedData[2],$schedData[3],$schedData[4])";
    }
    redcommand($sql, $db);
}

function set_report_map($sectionIds, $reportId, $db)
{
    $sectionIds = explode(",", $sectionIds);


    $sql = "INSERT into " . $GLOBALS['PREFIX'] . "report.ManagedReportMap (reportid,sectionid) values ";
    foreach ($sectionIds as $value) {
        $values .= "($reportId,$value),";
    }
    $sqlInsert  = $sql . rtrim($values, ",");
    $res        = redcommand($sqlInsert, $db);
    if ($res) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function unsetReportMapData($id, $db)
{

    $delsql1 = "DELETE FROM " . $GLOBALS['PREFIX'] . "report.ManagedReportMap WHERE reportid =$id";
    $res = redcommand($delsql1, $db);
}

function write_json_file($data, $fp)
{
    fwrite($fp, $data);
}

function wrtSumSecExcl($fEx, $data)
{
    $total = 0;
    $perc = 0;
    $j = 0;
    $excelRow = [];
    $style = (new StyleBuilder())->setFontBold()->build();
    $fEx->addRowWithStyle(array('Item', 'Count', 'Total', 'Percentage'), $style);
    foreach ($data as $key => $value) {
        if ($key != 'total') {
            $summaryName = $key;
            $excelRow[] = array($key, '', '', '');
            foreach ($value as $val) {
                $perc = round((($val['count'] / $data['total']) * 100), 2);
                $total += $perc;
                $excelRow[] = array('    ' . $val['name'], $val['count'], $data['total'], $perc);
                $j++;
            }
        }
    }
    $overAll = round(($total / ($j)), 2);
    $excelRow[] = array('Overall compliance', '', '', $overAll);
    $fEx->addRows($excelRow);
    $sheet = $fEx->getCurrentSheet();
    $sheet->setName(substr($summaryName, 0, 20));
    $fEx->addNewSheetAndMakeItCurrent();
}
function wrtExclContent($fEx, $secName, $groupby, $chartType, $data)
{


    if ($chartType != 3) {
        wrt_excl_Sum($fEx, $secName, $groupby, $data);
    }

    wrt_excl_details($fEx, $secName, $data);
}

function wrt_excl_details($fEx, $secName, $data)
{
    if ($data[0]['type'] == 'asset') {
        wrt_excl_details_asst($fEx, $secName, $data);
    } else if ($data[0]['type'] == 'event') {
        wrt_excl_details_evnt($fEx, $secName, $data);
    } else {
        wrt_excl_details_mum($fEx, $secName, $data);
    }
}

function wrt_excl_details_asst($fEx, $secName, $data)
{
    $Hdr = [];
    foreach ($data[0]['details']['columns'] as $name => $did) {
        $Hdr[] = $name;
    }
    $style = (new StyleBuilder())->setFontBold()->build();
    $fEx->addRowWithStyle($Hdr, $style);
    $sheet = $fEx->getCurrentSheet();
    $sheet->setName(substr($secName, 0, 20) . "_Details");
    foreach ($data as $value) {
        $j = 0;
        foreach ($value['details']['rows'] as $mid => $assets) {
            $table = [];
            $column = 0;
            foreach ($value['details']['columns'] as $name => $did) {
                $row    = 0;
                if (!isset($value['details']['multiple'][$did])) {
                    if (isset($assets[$did])) {
                        $singleVal = utf8_encode(array_pop($assets[$did]));
                        $table[$row][$column] = $singleVal;
                        $column++;
                        while (isset($table[$row + 1][$column - 2])) {
                            $row++;
                            $table[$row][$column - 1] = $singleVal;
                        }
                    }
                } else {
                    $table[$row][$column] = '';
                    if (isset($assets[$did])) {
                        foreach ($assets[$did] as $ord => $val) {
                            if (!isset($table[$row][$column - 1]) && $column && $row) {
                                for ($fill = 0; $fill < $column; $fill++) {
                                    if (!isset($table[$row][$column - ($fill + 1)]))
                                        $table[$row][$column - ($fill + 1)] = $table[$row - 1][$column - ($fill + 1)];
                                }
                            }
                            if ($row == $ord - 1) {
                                $table[$row][$column] = utf8_encode($val);
                                $row++;
                            } else {
                                for ($k = $row; $k < $ord; $k++) {
                                    for ($fill = 0; $fill < $column; $fill++) {
                                        if (!isset($table[$k][$column - ($fill + 1)]))
                                            $table[$k][$column - ($fill + 1)] = $table[$k - 1][$column - ($fill + 1)];
                                    }
                                    $table[$k][$column] = '';
                                    $row++;
                                }
                            }
                        }
                    }
                    $column++;
                }
            }
            $fEx->addRows($table);
        }
    }
    $fEx->addNewSheetAndMakeItCurrent();
}

function wrt_excl_details_evnt($fEx, $secName, $data)
{
    $style = (new StyleBuilder())->setFontBold()->build();
    $fEx->addRowWithStyle(array('Scrip', 'Machine', 'Site', 'Description', 'Text1'), $style);
    $sheet = $fEx->getCurrentSheet();
    $sheet->setName(substr($secName, 0, 20) . "_Details");
    foreach ($data as $value) {
        foreach ($value['details'] as $val) {
            $fEx->addRow(array($val['scrip'], $val['machine'], $val['customer'], $val['description'], $val['text1']));
        }
    }
    $fEx->addNewSheetAndMakeItCurrent();
}

function wrt_excl_details_mum($fEx, $secName, $data)
{

    $style = (new StyleBuilder())->setFontBold()->build();
    $fEx->addRowWithStyle(array('Machine', 'Patch', 'Status', 'Type', 'Detected'), $style);
    $sheet = $fEx->getCurrentSheet();
    $sheet->setName(substr($secName, 0, 20) . "_Details");

    $prevstatus = '';

    foreach ($data[0]['details'] as $value) {
        switch ($value['status']) {
            case 'Installed':
                $status = $value['status'];
                break;
            case 'Downloaded':
                $status = $value['status'];
                break;
            case 'Detected':
                $status = $value['status'];
                break;
            case 'Superseded':
                $status = $value['status'];
                break;
            case 'Waiting':
                $status = $value['status'];
                break;
            default:
                $status = $value['status'];
                break;
        }
        if ($status != $prevstatus) {
            $prevstatus = $status;
        }
        $fEx->addRow(array($value['host'], $data[0]['patchname'][$value['patchid']], $status, $data[0]['patchtype'][$value['patchid']], $value['detected']));
    }
    $fEx->addNewSheetAndMakeItCurrent();
}

function wrt_excl_Sum($fEx, $secName, $groupby, $data)
{

    $count = 0;
    $excelRow = [];

    $style = (new StyleBuilder())->setFontBold()->build();
    $fEx->addRowWithStyle(array('Item', 'count'), $style);

    if ($groupby == 1) {
        $graphData = $data[0]['groupedData'];
    } else {
        foreach ($data as $value) {
            $graphData[] = $value['groupedData'];
        }
    }
    foreach ($graphData as $value) {
        if (!empty($value)) {
            $count += $value['count'];
            $excelRow[] = array($value['name'], $value['count']);
        }
    }

    $excelRow[] = array('Grand Total', $count);
    $fEx->addRows($excelRow);
    $sheet = $fEx->getCurrentSheet();
    $sheet->setName(substr($secName, 0, 20) . "_Summary");
    $fEx->addNewSheetAndMakeItCurrent();
}


function sendMail($filename, $reportName, $email, $type)
{

    $reportType = 'Managed Report';
    $to = $email;
    $from = getenv('SMTP_USER_LOGIN');
    $message = "This is system generated mail, please don't reply.";
    $filepath = '../insights/files/' . $filename . '.xls';
    $subject  = "$reportType : $reportName";

    $fname = $filename . '.xls';
    $headers = "From: $from";
    $semi_rand = md5(time());
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";

    $message .= "This is a multi-part message in MIME format.\n\n" . "--{$mime_boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" . "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
    $message .= "--{$mime_boundary}\n";


    $file = fopen($filepath, "rb");
    $data = fread($file, filesize($filepath));
    fclose($file);
    $data = chunk_split(base64_encode($data));

    $message .= "Content-Type: {\"application/octet-stream\"};\n" . " name=\"$fname\"\n" .
        "Content-Disposition: attachment;\n" . " filename=\"$fname\"\n" .
        "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
    $message .= "--{$mime_boundary}\n";

//    $ok = @mail($to, $subject, $message, $headers, "-f " . $from);
    // send from visualisationService
    $arrayPost = array(
      'from' => getenv('SMTP_USER_LOGIN'),
      'to' => $to,
      'subject' => $subject,
      'text' =>'',
      'html' => $message,
      'token' => getenv('APP_SECRET_KEY'),
    );
    $url = getenv('VISUALISATION_SERVICE_API_URL')."/mailer/sendmassage";
    CURL::sendDataCurl($url, $arrayPost);

}

function set_basic_config_excel($fileName)
{
    $writer = WriterFactory::create(Type::XLSX);
    $fn = $fileName . ".xls";
    $csvPath = "../insights/files/$fn";
    $writer->openToFile($csvPath);
    return $writer;
}

function getSectionName($data)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);

    $name = url::requestToAny('name');
    $id   = url::requestToAny('id');
    $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.ManagedSection WHERE name = '$name'";

    $result = find_one($sql, $db);
    if (safe_count($result)) {
        $return['status'] = '<span>Section Name already exists</span>';
    } else {
        $return['status'] = 'No';
    }
    echo json_encode($return);
}

function addSection($viaAjax)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);

    $sectionData = getReportPostValues();
    $sectionName   = $sectionData['sectionName'];
    $headerType    = $sectionData['subHeaderType'];
    $chartType     = $sectionData['chartType'];
    $sectionType   = $sectionData['sectionType'];
    $pivotChartType = $sectionData['pivotChart'];
    $scorevalue     = implode(",", $sectionData['header']);

    $sectionExistsSql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.ManagedSection WHERE name = '$sectionName'";
    $sectionExistsRes = find_one($sectionExistsSql, $db);

    if (empty($sectionExistsRes)) {
        if ($sectionType == '5') {

            $res  = insert_sections_score($db, $sectionName, $sectionType, $scorevalue);
        } else {

            $res = insert_sections($db, $sectionName, $headerType, $chartType, $sectionType, $sectionData, $pivotChartType);
        }
        if ($res) {
            echo json_encode(array("msg" => '<span>Section added</span>'));
        } else {
            echo json_encode(array("msg" => '<span>Couldnot add section. Try again</span>'));
        }
    } else {
        echo json_encode(array("msg" => '<span>Section Name already exists</span>'));
    }
}

function insert_sections_score($db, $sectionName, $sectionType, $scorevalue)
{
    $sectionName = safe_addslashes($sectionName);
    $sql = 'INSERT into ' . $GLOBALS['PREFIX'] . 'report.ManagedSection (name,subheaders,charttype,sectiontype,pivotType,scorevalues) '
        . 'values("' . $sectionName . '",0,0,"' . $sectionType . '",0,"' . $scorevalue . '")';

    $result = redcommand($sql, $db);
    if ($result) {

        return TRUE;
    } else {

        return FALSE;
    }
}

function insert_sections($db, $sectionName, $headerType, $chartType, $sectionType, $headerData, $pivotChartType)
{
    $sectionName = safe_addslashes($sectionName);

    $sql = 'INSERT into ' . $GLOBALS['PREFIX'] . 'report.ManagedSection (name,subheaders,charttype,sectiontype,pivotType) '
        . 'values("' . $sectionName . '","' . $headerType . '","' . $chartType . '","' . $sectionType . '","' . $pivotChartType . '")';

    $result = redcommand($sql, $db);
    $sectionid = inserted_id($db);
    if ($result) {
        $id =  set_Subsec_Data($sectionid, $headerData, $db);

        if ($id) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

function set_Subsec_Data($sectionid, $headerData, $db)
{

    $groupVal       = $headerData['groupVal'];
    $updateType     = $headerData['updateType'];
    $updateSize     = $headerData['updateSize'];
    $month          = $headerData['month'];
    $year           = $headerData['year'];
    $os             = $headerData['osType'];

    unset($headerData['subSecData'][0]);

    foreach ($headerData['subSecData'] as $key => $val) {
        $sql = 'INSERT into ' . $GLOBALS['PREFIX'] . 'report.ManagedSubSection (sectionid, ' .
            'name, filtertype, filterid, groupVal, reportduration, ' .
            'updatetype, updatesize, mnth, year, ostype) values' .
            '("' . $sectionid . '", "' . $val['subheadername'] . '","' . $val['filterType'] . '", "' . $val['filterid'] . '", ' .
            '"' . $groupVal . '", "' . $val['eventduration'] . '","' . $updateType . '",' .
            '"' . $updateSize . '", "' . $month . '", "' . $year . '", "' . $os . '")';
        $id = redcommand($sql, $db);
    }
    return inserted_id($db);
}

function getSectionDetails()
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);
    $key = '';
    $start = url::requestToAny('start');
    $length = url::requestToAny('length');
    $limit = " limit $start, $length ";
    $sectionList = [];

    $sql = "SELECT * FROM " . $GLOBALS['PREFIX'] . "report.ManagedSection";
    $result = find_many($sql, $db);

    $totalRecords = safe_count($result);
    if ($totalRecords > 0) {
        foreach ($result as $key => $val) {
            $sectionId   = $val['id'];
            $sectionName = $val['name'];

            switch ($val['sectiontype']) {
                case 0:
                    $sectionType = 'No Section Selected';
                    break;
                case 1:
                    $sectionType = 'Event Section';
                    break;
                case 2:
                    $sectionType = 'Asset Section';
                    break;
                case 3:
                    $sectionType = 'MUM Section';
                    break;
                case 4:
                    $sectionType = 'Summary Section';
                    break;
                default:
                    break;
            }
            switch ($val['charttype']) {
                case 0:
                    $charType = "No chart selected";
                    break;
                case 1:
                    $chartType = 'Bar Chart';
                    break;
                case 2:
                    $chartType = 'Pie Chart';
                    break;
                case 3:
                    $chartType = 'Tabular Format';
                    break;
                case 4:
                    $chartType = 'Tabular/Summary';
                    break;
                default:
                    break;
            }

            $sectionList[] = array('id' => $sectionId, $sectionName, $sectionType, $chartType);
        }
    } else {
        $sectionList = array();
    }
    echo json_encode($sectionList);
}

function editSectionDetails($data)
{

    $id   = url::requestToAny('id');
    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);
    $sectionData = [];

    $sql = 'SELECT * FROM ' . $GLOBALS['PREFIX'] . 'report.ManagedSection WHERE id = ' . $id . ' ';
    $Result = find_one($sql, $db);

    $sectionData['chartType']   = $Result['charttype'];
    $sectionData['sectionType'] = $Result['sectiontype'];
    $sectionData['sectionName'] = $Result['name'];
    $sectionData['subHeaders']  = $Result['subheaders'];
    $sectionData['pivotType']   = $Result['pivotType'];

    $sql1 = 'SELECT * FROM ' . $GLOBALS['PREFIX'] . 'report.ManagedSubSection WHERE sectionid = ' . $id . ' ';
    $result = find_many($sql1, $db);

    foreach ($result as $key => $val) {
        $sectionData['subData'][$val['id']]['subHeaderName']   = $val['name'];
        $sectionData['subData'][$val['id']]['filterId']        = $val['filterid'];
        $sectionData['subData'][$val['id']]['filterType']      = $val['filtertype'];
        $sectionData['subData'][$val['id']]['groupName']       = rtrim($val['groupVal'], ',');
        $sectionData['subData'][$val['id']]['eventDuration']   = $val['reportduration'];
        $sectionData['subData'][$val['id']]['updateType']      = $val['updatetype'];
        $sectionData['subData'][$val['id']]['updateSize']      = $val['updatesize'];
        $sectionData['subData'][$val['id']]['month']           = $val['mnth'];
        $sectionData['subData'][$val['id']]['reportduration']  = $val['reportduration'];
        $sectionData['subData'][$val['id']]['year']            = $val['year'];
        $sectionData['subData'][$val['id']]['os']              = $val['ostype'];
        $sectionData['subData'][$val['id']]['text']            = ($val['text'] != '') ? $val['text'] : 'text1';

        if ($val['mnth'] !== "0") {
            $dateRange = explode("--", $sectionData['subData'][$val['id']]['month']);
            if ($Result['sectiontype'] == "3" || $Result['sectiontype'] == 3) {
                $sectionData['subData'][$val['id']]['startDate'] = date("m/d/Y", $dateRange[0]);
                $sectionData['subData'][$val['id']]['endDate'] = date("m/d/Y", $dateRange[1]);
            } else {
                $sectionData['subData'][$val['id']]['startDate'] = date("d/m/Y", $dateRange[0]);
                $sectionData['subData'][$val['id']]['endDate'] = date("d/m/Y", $dateRange[1]);
            }
        }
    }

    $sectionData['queryType']   = safe_count($result);
    echo json_encode($sectionData);
}

function editSection($viaAjax)
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);

    $editSectionData = getReportPostValues();
    $sectionId = $editSectionData['sectionId'];
    $sectionName = $editSectionData['editSectionName'];
    $headerType  = $editSectionData['editSubHeaderType'];
    $chartType   = $editSectionData['editChartType'];
    $sectionType = $editSectionData['editSectionType'];
    $pivotType   = $editSectionData['editpivotType'];

    $editSection = editsections($db, $sectionName, $headerType, $chartType, $sectionType, $editSectionData, $sectionId, $pivotType);

    if ($editSection) {
        echo json_encode(array("msg" => 'Section edited'));
    } else {
        echo json_encode(array("msg" => 'Couldnot edit section. Try again'));
    }
}

function editsections($db, $sectionName, $headerType, $chartType, $sectionType, $headerData, $sectionId)
{
    $sectionName = safe_addslashes($sectionName);

    $sql = 'UPDATE ' . $GLOBALS['PREFIX'] . 'report.ManagedSection SET  name = "' . $sectionName . '" , subheaders = "' . $headerType . '",' .
        'charttype = "' . $chartType . '", pivotType = "' . $pivotType . '" WHERE id = "' . $sectionId . '" ';
    $result = redcommand($sql, $db);

    if ($result) {
        $id =  edit_Subsec_Data($sectionId, $headerData, $db);

        if ($id) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
}

function edit_Subsec_Data($sectionid, $headerData, $db)
{

    $deleteSql = "DELETE FROM " . $GLOBALS['PREFIX'] . "report.ManagedSubSection WHERE sectionid = " . $sectionid;
    $deleteRes = redcommand($deleteSql, $db);

    $groupVal       = $headerData['editGroupVal'];
    $updateType     = $headerData['editUpdateType'];
    $updateSize     = $headerData['editUpdateSize'];
    $month          = $headerData['editMonth'];
    $year           = $headerData['editYear'];
    $os             = $headerData['editOsType'];

    unset($headerData['editSubSecData'][0]);

    foreach ($headerData['editSubSecData'] as $key => $val) {
        $sql = 'INSERT into ' . $GLOBALS['PREFIX'] . 'report.ManagedSubSection (sectionid, ' .
            'name, filtertype, filterid, groupVal, reportduration, ' .
            'updatetype, updatesize, mnth, year, ostype) values' .
            '("' . $sectionid . '", "' . $val['editSubheadername'] . '","' . $val['editFilterType'] . '", "' . $val['editFilterid'] . '", ' .
            '"' . $groupVal . '", "' . $val['editEventduration'] . '","' . $updateType . '",' .
            '"' . $updateSize . '", "' . $month . '", "' . $year . '", "' . $os . '")';
        $id = redcommand($sql, $db);
    }
    if ($id) {
        return TRUE;
    } else {
        return FALSE;
    }
}

function getSearchString()
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);
    $id   = url::requestToAny('id');

    $searchSql = "SELECT savedConfig from " . $GLOBALS['PREFIX'] . "report.ManagedReport WHERE id = $id";
    $searchRes = find_one($searchSql, $db);

    if ($searchRes) {
        $return['savedConfig'] = $searchRes['savedConfig'];
    } else {
        $return['savedConfig'] = '';
    }
    echo json_encode($return);
}

function updateConfig()
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);
    $id   = url::requestToAny('id');
    $string = url::requestToAny('string');

    $updateSql = "UPDATE " . $GLOBALS['PREFIX'] . "report.ManagedReport SET savedConfig = '$string' WHERE id = $id";
    $updateRes = redcommand($updateSql, $db);
    echo "success";
}

function getChartType()
{

    $db = db_connect();
    db_change($GLOBALS['PREFIX'] . 'report', $db);
    $id   = url::requestToAny('id');

    $searchSql = "SELECT pivotType FROM " . $GLOBALS['PREFIX'] . "report.ManagedSection s," . $GLOBALS['PREFIX'] . "report.ManagedReportMap r WHERE " .
        "s.id = r.sectionid AND r.reportid = $id";
    $searchRes = find_one($searchSql, $db);

    if ($searchRes) {
        $return['chartType'] = $searchRes['pivotType'];
    } else {
        $return['chartType'] = '';
    }
    echo json_encode($return);
}


function getReportDetails()
{
    $db = db_connect();

    $repid = url::issetInRequest('repid') ? url::requestToAny('repid') : '';

    $siteSql = "select name, username, include from " . $GLOBALS['PREFIX'] . "report.ManagedReport where id = $repid";
    $siteRes = find_one($siteSql, $db);

    $reptName = $siteRes['name'];
    $siteName = $siteRes['include'];
    $userName = $siteRes['username'];

    $key = '';
    $user = $_SESSION['user']['username'];
    if ($siteName == 'All') {
        $scope = DASH_GetSites($key, $db, $user);
        foreach ($scope as $value) {
            $siteName .= $value . ',';
        }
        $siteName = rtrim($siteName, ',');
    }

    $secNameSql = "select GROUP_CONCAT(ms.name order by ms.name) as sections, GROUP_CONCAT(ms.id order by ms.name) as sectionIds, GROUP_CONCAT(ms.sectiontype order by ms.name) as sectionType from " . $GLOBALS['PREFIX'] . "report.ManagedReportMap mr, " . $GLOBALS['PREFIX'] . "report.ManagedSection ms where mr.reportid = $repid and mr.sectionid = ms.id order by mapid";
    $secNameRes = find_one($secNameSql, $db);

    $sectionData = explode(',', $secNameRes['sections']);
    $sectionValu = explode(',', $secNameRes['sectionIds']);
    $sectionType = explode(',', $secNameRes['sectionType']);
    foreach ($sectionData as $key => $value) {
        $sectionOption .= '<li onclick="changeSectionData(' . $sectionValu[$key] . ',' . $sectionType[$key] . ',this)"><a href="#">' . $value . '</a></li>';
    }

    $tagSql = "select ms.name, ms.charttype, mss.sectionid, mss.filterid, GROUP_CONCAT(mss.filterid) as filterids, mss.reportduration, ms.sectiontype from " . $GLOBALS['PREFIX'] . "report.ManagedSection ms, " . $GLOBALS['PREFIX'] . "report.ManagedSubSection mss where mss.sectionid IN($sectId) and ms.id = mss.sectionid;";
    $tagRes = find_one($tagSql, $db);

    $chartTypeArr = array(1 => 'bar', 2 => 'pie', 3 => 'line');

    $eventTagSql = "select GROUP_CONCAT(eventtag) as tagfilters from " . $GLOBALS['PREFIX'] . "event.SavedSearches where id IN(" . $tagRes['filterids'] . ");";
    $eventTagRes = find_one($eventTagSql, $db);

    $tagNameListData = $eventTagRes['tagfilters'];

    $machOpts = all_mach_grps($siteName, $db);

    $sectionDet = array(
        'reportLevel' => 'site', 'reportname' => $reptName, 'sitename' => $siteName, 'username' => $userName, 'sectionData' => $sectionOption,
        'sectionName' => $tagRes['name'], 'sectionType' => $tagRes['sectiontype'], 'duration' => $tagRes['reportduration'],
        'chartType' => $chartTypeArr[$tagRes['charttype']], 'filterTags' => $tagNameListData, 'siteData' => $machOpts
    );
    echo json_encode($sectionDet);
}

function getReportDetailsNew()
{
    $db = db_connect();

    $resArr = '';
    $repid = url::issetInRequest('repid') ? url::requestToAny('repid') : '';

    $siteSql = "select name, username, include from " . $GLOBALS['PREFIX'] . "report.ManagedReport where id = $repid";
    $siteRes = find_one($siteSql, $db);

    $reptName = $siteRes['name'];
    $siteName = $siteRes['include'];

    $key = '';
    $user = $_SESSION['user']['username'];
    if ($siteName == 'All') {
        $scope = DASH_GetSites($key, $db, $user);
        foreach ($scope as $value) {
            $siteName .= $value . ',';
        }
        $siteName = rtrim($siteName, ',');
    }

    $secNameSql = "select GROUP_CONCAT(ms.name) as sections from " . $GLOBALS['PREFIX'] . "report.ManagedReportMap mr, " . $GLOBALS['PREFIX'] . "report.ManagedSection ms where mr.reportid = $repid and mr.sectionid = ms.id order by mapid";
    $secNameRes = find_one($secNameSql, $db);

    $secSql = "select sectionid from " . $GLOBALS['PREFIX'] . "report.ManagedReportMap where reportid = $repid order by mapid";
    $secRes = find_many($secSql, $db);

    foreach ($secRes as $key => $value) {
        $sectId .= $value['sectionid'] . ',';
    }
    $sectId = rtrim($sectId, ',');

    $tagSql = "select ms.name, ms.charttype, mss.sectionid, mss.filterid, GROUP_CONCAT(mss.filterid) as filterids, mss.reportduration, ms.sectiontype from " . $GLOBALS['PREFIX'] . "report.ManagedSection ms, " . $GLOBALS['PREFIX'] . "report.ManagedSubSection mss where mss.sectionid IN($sectId) and ms.id = mss.sectionid;";
    $tagRes = find_one($tagSql, $db);

    $chartTypeArr = array(1 => 'bar', 2 => 'pie', 3 => 'line');

    $eventTagSql = "select GROUP_CONCAT(eventtag) as tagfilters from " . $GLOBALS['PREFIX'] . "event.SavedSearches where id IN(" . $tagRes['filterids'] . ");";
    $eventTagRes = find_one($eventTagSql, $db);

    $tagNameListData = $eventTagRes['tagfilters'];

    $machOpts = all_mach_grps($siteName, $db);

    $sectionData = explode(',', $secNameRes['sections']);
    foreach ($sectionData as $key => $value) {
        $selected = '';
        if ($tagRes['name'] == $value) {
            $selected = 'selected';
        }
        $sectionOption .= "<option value='" . $secRes[$key]['sectionid'] . "' $selected>$value</option>";
    }

    $sectionDet = array('reportLevel' => 'site', 'reportname' => $reptName, 'sitename' => $siteName, 'sectionName' => $tagRes['name'], 'sectionType' => $tagRes['sectiontype'], 'duration' => $tagRes['reportduration'], 'chartType' => $chartTypeArr[$tagRes['charttype']], 'filterTags' => $tagNameListData, 'siteData' => $machOpts, 'sectionData' => $sectionOption);

    echo json_encode($sectionDet);
}




function getReportSectionDetails()
{
    $db = db_connect();

    $resArr = '';
    $sectId = url::issetInRequest('secid') ? url::requestToAny('secid') : '';

    $tagSql = "select ms.name, ms.charttype, mss.sectionid, mss.filterid, GROUP_CONCAT(mss.filterid) as filterids, mss.reportduration, ms.sectiontype from " . $GLOBALS['PREFIX'] . "report.ManagedSection ms, " . $GLOBALS['PREFIX'] . "report.ManagedSubSection mss where mss.sectionid IN($sectId) and ms.id = mss.sectionid;";
    $tagRes = find_one($tagSql, $db);

    $chartTypeArr = array(1 => 'bar', 2 => 'pie', 3 => 'line');

    $eventTagSql = "select GROUP_CONCAT(eventtag) as tagfilters from " . $GLOBALS['PREFIX'] . "event.SavedSearches where id IN(" . $tagRes['filterids'] . ");";
    $eventTagRes = find_one($eventTagSql, $db);

    $tagNameListData = $eventTagRes['tagfilters'];

    $machOpts = all_mach_grps($siteName, $db);

    $sectionDet = array('reportLevel' => 'site', 'sectionName' => $tagRes['name'], 'sectionType' => $tagRes['sectiontype'], 'duration' => $tagRes['reportduration'], 'chartType' => $chartTypeArr[$tagRes['charttype']], 'filterTags' => $tagNameListData, 'siteData' => $machOpts);

    echo json_encode($sectionDet);
}


function getEventsSectionDetails()
{
    $db = db_connect();

    $resArr = '';
    $sectId = url::issetInRequest('sectionId') ? url::requestToAny('sectionId') : '';
    $repid = url::issetInRequest('repid') ? url::requestToAny('repid') : '';

    $tagSql = "SELECT S.id, S.name, S.eventtag, M.reportduration FROM " . $GLOBALS['PREFIX'] . "event.SavedSearches S, " . $GLOBALS['PREFIX'] . "report.ManagedSubSection M WHERE S.id=M.filterid AND M.sectionid='$sectId' LIMIT 1";
    $tagRes = find_one($tagSql, $db);

    $includeSql = "SELECT include FROM " . $GLOBALS['PREFIX'] . "report.ManagedReport MR WHERE MR.id='$repid' LIMIT 1";
    $includeRes = find_one($includeSql, $db);

    if (stripos($includeRes['include'], 'All') !== FALSE) {
        $userid = $_SESSION['user']['userid'];
        $sql = "select customer from " . $GLOBALS['PREFIX'] . "core.Customers C join " . $GLOBALS['PREFIX'] . "core.Users U on U.username = C.username where userid = $userid";
        $result = find_many($sql, $db);
        $names = '';
        foreach ($result as $val) {
            $names .= $val['customer'] . ",";
        }
        $names = 'MSP_NH_Test,MSPNHTest2__201700010,MSPTest__201700010,abc__201700010,testins__201700032,LMITEST__201700010,dsbsbsdfbfbfdbdb__201700010,dzfhf__201700010,ELTESTING__201700095,network_test__201700010,veeratest2311__2017000234';
        $include = rtrim($names, ',');
    } else {
        $include = $includeRes['include'];
    }

    $sectionDet = array('reportLevel' => 'site', 'sectionName' => $tagRes['name'], 'eventtag' => $tagRes['eventtag'], 'duration' => $tagRes['reportduration'], "include" => $include);
    echo json_encode($sectionDet);
}
