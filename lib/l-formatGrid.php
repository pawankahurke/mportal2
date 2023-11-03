<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'l-setTimeZone.php';



function FORMAT_ADMN_NotifyGridData($res)
{
    $recordList = [];
    if (safe_count($res) > 0) {
        foreach ($res as $key => $value) {
            $id = $value['id'];
            $username = $value['username'];
            if (!empty($value['last_run'])) {
                $lastRun = '<p class="ellipsis" title="' . date("m/d/Y h:i A", $value['last_run']) . '">' . date("m/d/Y h:i A", $value['last_run']) . '</p>';
            } else {
                $lastRun = 'Never';
            }
            if ($value['console'] == 0 && $value['email'] == 0)
                $destination = '';
            if ($value['console'] == 0 && $value['email'] == 1)
                $destination = 'e-mail';
            if ($value['console'] == 1 && $value['email'] == 0)
                $destination = 'Console';
            if ($value['console'] == 1 && $value['email'] == 1)
                $destination = 'Console,e-mail';

            if ($value['email'] == 1) {
                if ($value['email_recipients'] != "") {
                    $email = '<p class="ellipsis" title="' . $value['email_recipients'] . '">' . $value['email_recipients'] . '</p>';
                } else {
                    $email = '<p> NA </p>';
                }
            } else {
                if ($value['email_recipients'] != "") {
                    $email = '<p class="ellipsis" title="' . $value['email_recipients'] . '">' . $value['email_recipients'] . '</p>';
                } else {
                    $email = '<p> NA </p>';
                }
            }

            if ($value['state'])
                $state = 'Enabled';
            else
                $state = 'Disabled';

            if ($value['auto_soln'] == 1) {
                $auto_soln = "Yes";
            } else {
                $auto_soln = "No";
            }

            if ($value['profile_name'] != "") {
                $profile_name = '<p class="ellipsis" title="' . $value['profile_name'] . '">' . $value['profile_name'] . '</p>';
            } else {
                $profile_name = '<p> NA </p>';
            }



            $prio = 'P' . $value['priority'] . '';
            $notifDetails = '<p class="ellipsis" title="' . $value['notification_name'] . '">' . $value['notification_name'] . '</p>' . '';
            $event_filter = '<p class="ellipsis" title="' . $value['event_filter'] . '">' . $value['event_filter'] . '</p>';
            $destination = '<p class="ellipsis" title="' . $destination . '">' . $destination . '</p>';
            if (empty($value['modified'])) {
                $modified = '<p> NA </p>';
            } else {
                $modified = '<p class="ellipsis" title="' . date("m/d/Y h:i A", $value['modified']) . '">' . date("m/d/Y h:i A", $value['modified']) . '</p>';
            }
            $recordList[] = array($notifDetails, $username, $lastRun, $modified, $prio, $state, $auto_soln, $profile_name, $id);
        }

        $jsonData = $recordList;
        echo json_encode($jsonData);
    } else {
        $jsonData = $recordList;
        echo json_encode($jsonData);
    }
}



function FORMAT_ADMN_GetEventfilterGridData($res)
{
    $recordList = [];
    if (safe_count($res) > 0) {
        foreach ($res as $key => $value) {
            $eventid = $value['id'];
            if ($value['created'] == 0) {
                $create = '<p class="ellipsis" title="-">-</p>';
            } else {
                $create = '<p class="ellipsis" title="' . date("m/d/Y h:i A", $value['created']) . '">' . date("m/d/Y h:i A", $value['created']) . '</p>';
            }
            if ($value['modified'] == 0) {
                $modified = '<p class="ellipsis" title="-">-</p>';
            } else {
                $modified = '<p class="ellipsis" title="' . date("m/d/Y h:i A", $value['modified']) . '">' . date("m/d/Y h:i A", $value['modified']) . '</p>';
            }

            $eventname = '<p class="ellipsis" id="' . $value['id'] . '" value="' . $value['id'] . '" title="' . $value['name'] . '">' . $value['name'] . '</p>';
            $recordList[] = array($eventname, $create, $modified, $eventid);
        }
    } else {
        $recordList[] = array();
    }

    return $recordList;
}

function FORMAT_DEPL_LeftGridData($res, $db)
{
    $recordList = [];
    if (safe_count($res) > 0) {
        foreach ($res as $key => $value) {
            $searchType = $_SESSION["searchType"];
            $searchVal = $_SESSION["searchValue"];
            $parent = $_SESSION["rparentName"];
            if ($searchType == "ServiceTag") {
                $site = $_SESSION["rparentName"];
                $host = $_SESSION["searchValue"];
            }
            $lastscanunx = $value['lastscan'];
            $subnetmask = '' . $value['subnetmask'] . '';
            $id = '' . $value['id'] . '';
            $lastscan = '';

            if ($value['lastscan'] == "never") {
                $lastscan = "never";
            } else {

                if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {

                    $lastscan = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $value['lastscan'], "m/d/Y g:i:s A");
                }
            }

            $scanFun = getScan_status($subnetmask, $db, $site, $host, $lastscanunx);


            $scantime = getlastScan($subnetmask, $db, $site, $host, $lastscanunx);

            $checkBox = '<div class="form-check"><label class="form-check-label"><input class="form-check-input subnetipid" type="checkbox" name="checkNoc" id="' . $id . '" value="' . $id . '" onclick="uniqueCheckBox(\'' . $id . '\');"><span class="form-check-sign"></span></label></div>';
            $actionStatus = "Scan";
            $subnetip = $value['subnetmask'];

            $subnetmask_format = str_replace(".", "_", $subnetmask);

            if ($searchType == "ServiceTag") {
                if ($scanFun == "scan") {
                    $actionStatus = "Scan";
                    $Action = '<a href="#" data-toggle="modal" style="color:#5882FA;" class="' . $subnetmask_format . 'scanlink" onclick="scanFunction(\'' . $site . '\',\'' . $host . '\',\'' . $subnetmask . '\')">' . $actionStatus . '</a><img src="../assets/img/loader-sm.gif" class="' . $subnetmask_format . 'scangif" style="display:none;" border="0" alt="scanning..."/>';
                } else if ($scanFun == "scanning") {
                    $actionStatus = "scanning";
                    $Action = "scanning...";
                } else if ($scanFun == "reset") {
                    $actionStatus = "resetScan";
                    $Action = '<a href="#" data-toggle="modal" style="color:#5882FA;" class="' . $subnetmask_format . 'scanlink" onclick="ResetscanFunction(\'' . $site . '\',\'' . $host . '\',\'' . $subnetmask . '\')">' . $actionStatus . '</a>';
                }
            } else {
                $Action = '';
            }

            $lastscan = '<p class="' . $subnetmask_format . 'lastscan" title="' . $lastscan . '">' . $lastscan . '</p><p class="' . $subnetmask_format . 'lastscan_offline"  style="display:none;" title="' . $lastscan . '">' . $lastscan . ' (offline) </p><p class="' . $subnetmask_format . 'lastscan_failed"  style="display:none;" title="' . $lastscan . '">' . $lastscan . ' (failed) </p><p class="' . $subnetmask_format . 'lastscan_scanning"  style="display:none;" title="' . $lastscan . '">Scanning</p>';
            $recordList[] = array("DT_RowId" => $value['subnetmask'], $subnetip, $lastscan, $Action);
        }

        $jsonData = $recordList;
        echo json_encode($jsonData);
    } else {
        $jsonData = $recordList;
        echo json_encode($jsonData);
    }
    exit;
}

function getScan_status($subnetmask, $db, $site, $host, $lastscanunx)
{

    $sql = "select * from " . $GLOBALS['PREFIX'] . "swupdate.DeploymentQueue where subnetmask='$subnetmask' and host='$host' and site='$site'";
    $res = find_one($sql, $db);
    $count = safe_count($res);

    if ($count > 0) {

        $status = $res['status'];
        $scantime = $res['scantime'];

        if (empty($scantime)) {
            $scanFun = "scan";
        } else if ($scantime != '') {

            if (($status = 1) || ($status = 0)) {

                $scanlimit = strtotime(date("m/d/y H:i", strtotime('10 minutes', $scantime)));
                $currentTime = time();

                if (($currentTime > $scanlimit) || ($status == 4)) {
                    $scanFun = "reset";
                } else {
                    $scanFun = "scanning";
                }
            }
        }
    } else {
        $scanFun = "scan";
    }
    return $scanFun;
}

function getlastScan($subnetmask, $db, $site, $host, $lastscanunx)
{
    $sql = "select * from " . $GLOBALS['PREFIX'] . "swupdate.DeploymentQueue where subnetmask='$subnetmask' and host='$host' and site='$site'";
    $res = find_one($sql, $db);
    $count = safe_count($res);
    if ($count > 0) {
        $scantime = date("m/d/Y h:i A", $res['scantime']);
    } else {
        $scantime = "never";
    }
    return $scantime;
}

function FORMAT_DEPL_RightGridData($res)
{
    $recordList = [];
    if (safe_count($res) > 0) {
        foreach ($res as $key => $value) {

            $checkBox = '<div class="form-check"><label class="form-check-label"><input class="form-check-input depdetailsview" type="checkbox" name="checkNoc" id="' . $value['host'] . '" value="' . $value['ipaddress'] . '" onclick="uniqueCheckBox(\'' . $value['host'] . '\');"><span class="form-check-sign"></span></label></div>';
            $macaddress = '' . $value['macaddress'] . '';
            $ip = '' . $value['ipaddress'] . '';
            $host = '' . $value['host'] . '';
            $client = '' . $value['isclientavl'] . '';
            $clientversion = '' . $value['clientversion'] . '';

            if ($macaddress != "") {
                $recordList[] = array($checkBox, $macaddress, $ip, $host, $client, $clientversion);
            }
        }
        $jsonData['status'] = 'griddata';
        $jsonData['griddata'] = $recordList;
        echo json_encode($jsonData);
    } else {
        $jsonData['status'] = 'griddata';
        $jsonData['griddata'] = $recordList;
        echo json_encode($jsonData);
    }
}

function FORMAT_ADMN_AssetQueryGridData($res)
{
    $recordList = [];
    if (safe_count($res) > 0) {
        foreach ($res as $key => $value) {
            $id = $value['id'];
            $name = '<p class="ellipsis" id="' . $value['id'] . '" title="' . $value['name'] . '">' . $value['name'] . '</p>' . '';
            $time = '<p class="ellipsis" title="' . date("m/d/Y h:i:s", $value['created']) . '">' . date("m/d/Y h:i:s", $value['created']) . '</p>';
            $modified = '<p class="ellipsis" title="' . date("m/d/Y h:i:s", $value['modified']) . '">' . date("m/d/Y h:i:s", $value['modified']) . '</p>';
            $valname = $value['name'];

            $recordList[] = array($name, $time, $modified, $id, $valname);
        }

        $jsonData = $recordList;
        echo json_encode($jsonData);
    } else {
        $jsonData = $recordList;
        echo json_encode($jsonData);
    }
}

function FORMAT_RESOL_ServicesListData($res)
{
    if (safe_count($res) > 0) {
        foreach ($res as $key => $value) {
            $list .= '<li><a href="javascript:;" onclick="GET_ServicesGridDetailData(&quot;' . $value["parent_name"] . '&quot;,this);" class="thislist" title="' . $value["parent_name"] . '">' . $value["parent_name"] . '</a></li>';
        }
        $jsonData = $list;
        echo json_encode($jsonData);
    } else {
        $jsonData = $list;
        echo json_encode($jsonData);
    }
}

function FORMAT_RESOL_ServicesGridData($res, $searchtype)
{
    $recordList = [];
    if (safe_count($res) > 0) {
        foreach ($res as $key => $row) {
            $AID = $row['AID'];
            $Time = date("m/d/Y h:i A", $row['JobCreatedTime']);
            $MachineTag = $row['MachineTag'];
            if ($searchtype == "ServiceTag") {
                $Scope = $row['SelectionType'];
            } else {
                $rawSelection = $row['SelectionType'];
                $rawSelectionexp = explode(" : ", $rawSelection);
                $finalExp = $rawSelectionexp[0] . " : " . UTIL_GetTrimmedGroupName($rawSelectionexp[1]);
                $FinScope = $finalExp . " : " . $MachineTag;
                $Scope = '<p class="ellipsis" title="' . $FinScope . '">' . $FinScope . '</p>' . '';
            }


            $DartExecProof = $row['DartExecutionProof'];
            $state = $row['JobStatus'];
            $Detail = 'AuditDetailStatusFn(1,' . $AID . ',\'' . $DartExecProof . '\')';
            switch ($state) {
                case "2":
                    $proof = '<a style="cursor:pointer;color: #008000;" onclick="' . $Detail . '">Completed</a>';
                    break;
                case "3":
                    $proof = '<a style="color: #FF0000;">Failed</a>';
                    break;
                case "0":
                    $proof = '<a style="cursor:pointer;color: #FFA500;" onclick="' . $Detail . '">Pending</a>';
                    break;
                default:
                    $proof = '<a style="color: #FFA500;">Pending</a>';
                    break;
            }

            $recordList[] = array($AID, $Time, $Scope, $proof);
        }
    } else {
        $recordList = array();
    }
    echo json_encode($recordList);
}

function FORMAT_RESOL_NHConfigListData($res)
{
    if (safe_count($res) > 0) {
        foreach ($res as $key => $value) {
            if ($value["parent_name"] == "System Management" || $value["parent_name"] == "Software Update") {
                $list .= "";
            } else {
                $list .= '<li><a href="javascript:;" onclick="GET_NHConfigGridDetailData(&quot;' . $value["parent_name"] . '&quot;,this);" class="thislist" title="' . $value["parent_name"] . '">' . $value["parent_name"] . '</a></li>';
            }
        }
        $jsonData = $list;
        echo json_encode($jsonData);
    } else {
        $jsonData = $list;
        echo json_encode($jsonData);
    }
}

function FORMAT_RESOL_AviraSchedulerData($res, $searchtype)
{
    $recordList = [];
    if (safe_count($res) > 0) {
        foreach ($res as $key => $row) {
            $Id = $row['Id'];
            $Name = '<p class="ellipsis" title="' . $row['Name'] . '">' . $row['Name'] . '</p>' . '';
            $Action = $row['Action'];
            $Frequency = $row['Frequency'];
            $DisplayMode = $row['DisplayMode'];
            $Enabled = $row['Enabled'];
            $Status = $row['Status'];

            $ActionType = "";
            $FrequencyType = "";
            $DisplayModeType = "";
            $EnabledType = "";
            $StatusType = "";

            switch ($Action) {
                case "0":
                    $ActionType = "Scan";
                    break;
                case "1":
                    $ActionType = "Update";
                    break;
                default:
                    break;
            }
            switch ($Frequency) {
                case "0":
                    $FrequencyType = "Immediately";
                    break;
                case "1":
                    $FrequencyType = "Daily";
                    break;
                case "2":
                    $FrequencyType = "Weekly";
                    break;
                case "3":
                    $FrequencyType = "Interval";
                    break;
                case "4":
                    $FrequencyType = "Single";
                    break;
                default:
                    break;
            }
            switch ($DisplayMode) {
                case "0":
                    $DisplayModeType = "Invisible";
                    break;
                case "1":
                    $DisplayModeType = "Minimize";
                    break;
                case "2":
                    $DisplayModeType = "Maximize";
                    break;
                default:
                    break;
            }
            switch ($Enabled) {
                case "0":
                    $EnabledType = "No";
                    break;
                case "1":
                    $EnabledType = "Yes";
                    break;
                default:
                    break;
            }
            switch ($Status) {
                case "0":
                    $StatusType = "Wait";
                    break;
                case "1":
                    $StatusType = "Ready";
                    break;
                default:
                    break;
            }

            $OtherActions = '<div class="table-row-dropdown">
                                <div class="burger-menu-dropdown">
                                    <div class="dropdown">
                                        <a class="icon" data-toggle="dropdown" aria-expanded="false"><i class="material-icons icon-ic_more_horiz_24px"></i></a>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li><a href="javascript:;" onclick="editSchedulerJob(&quot;' . $Id . '&quot;);"><i class="material-icons icon-ic_edit_24px"></i></a></li>
                                            <li><a href="javascript:;" onclick="deleteSchedulerJob(&quot;' . $Id . '&quot;);"><i class="material-icons icon-ic_delete_24px"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>';


            $recordList[] = array($Id, $Name, $ActionType, $FrequencyType, $DisplayModeType, $EnabledType, $StatusType, $OtherActions);
        }
    } else {
        $recordList = array();
    }
    echo json_encode($recordList);
}

function FORMAT_CreateCapacityGridArray($capacityResultArr)
{
    $recordList = [];
    if (safe_count($capacityResultArr) > 0) {
        foreach ($capacityResultArr as $key => $val) {

            if ($val['cpuUsage'] == "") {
                $cpuUsage = '--';
            } else {
                $cpuUsage = $val['cpuUsage'] . '%';
            }

            if ($val['ramUsage'] == "") {
                $ramUsage = '--';
            } else {
                $ramUsed = 100 - (int) $val['ramUsage'];
                $ramUsage = $ramUsed . '%';
            }

            if ($val['hardDiskUsage'] == "") {
                $hardDiskUsage = '--';
            } else {
                $hardDiskUsed = 100 - (int) $val['hardDiskUsage'];
                $hardDiskUsage = $hardDiskUsed . '%';
            }

            if ($val['bateryState'] == "NA") {
                $bateryState = 'No Battery';
            } else {
                $bateryState = ($val['bateryState'] != "") ? $val['bateryState'] : "--";
            }

            if ($_SESSION["user"]["usage"] == 1 || $_SESSION["user"]["usage"] == '1') {
                $machineName = $val['machine'];
                $hostName = $val['machine'];
            } else {
                $machineName = UTIL_AppendPTag($val['host']);
                $tempHostName = ($val['hostName'] != "") ? $val['hostName'] : "--";
                $hostName = UTIL_AppendPTag($tempHostName);
            }

            $recordList[] = array($machineName, $hostName, $cpuUsage, $ramUsage, $hardDiskUsage, $bateryState);
        }
    }
    return $recordList;
}

function FORMAT_DEPL_DeployAudit($resultArray)
{
    $recordlist = [];

    if (safe_count($resultArray) > 0) {
        foreach ($resultArray as $key => $val) {
            $id = $val['id'];
            $sites = '<p class="ellipsis" title="' . UTIL_GetTrimmedGroupName($val['site']) . '">' . UTIL_GetTrimmedGroupName($val['site']) . '</p>' . '';
            $machine = '<p class="ellipsis" title="' . $val['machine'] . '">' . $val['machine'] . '</p>' . '';
            $time = '<p class="ellipsis" title="' . $val['time'] . '">' . $val['time'] . '</p>' . '';
            $text1 = '<p class="ellipsis" title="' . $val['text1'] . '">' . $val['text1'] . '</p>' . '';
            $details = '<a href="#" onclick="getDeployAuditDetails(\'' . $val['idx'] . '\')" style="text-decoration: underline; color:#48b2e4">Details</a>';
            $recordlist[] = array("site" => $sites, "machine" => $machine, "time" => $time, "text1" => $text1, "details" => $details);
        }
    }
    echo json_encode($recordlist);
}

function FORMAT_DEPL_DeployAuditDetails($arrayDetails)
{
    $recordlist = '';
    if (safe_count($arrayDetails) > 0) {
        $recordlist = '<tr>';

        $recordlist .= '<td>' . $arrayDetails['text2'] . '</td><td class="customscroll">' . $arrayDetails['text3'] . '</td><td class="customscroll">' . $arrayDetails['text4'] . '</td>';
        $recordlist .= '</tr>';
    }
    echo $recordlist;
}

function FORMAT_GETManageJobsData($type, $data, $total, $draw)
{
    $recordList = [];
    if (safe_count($data) > 0) {
        if ($type == 4) {
            foreach ($data as $key => $val) {
                $sid = $val['sid'];
                $machineOS = $val['machineOS'];
                $MobileID = $val['MobileID'];
                $serviceTag = $val['serviceTag'];
                $action = '<p class="text-overflow" title="Edit" onclick="editGCM_ID(&quot;' . $sid . '&quot;);" style="text-decoration: underline;cursor:pointer;color:#48b2e4;">Edit</p>';

                $recordList[] = array(
                    "serviceTag" => $serviceTag,
                    "MobileID" => $MobileID,
                    "machineOS" => $machineOS,
                    "action" => $action
                );
            }
        } else {
            foreach ($data as $key => $val) {
                $AID = $val['AID'];
                $ProfileName = $val['ProfileName'];
                $SelectionType = UTIL_GetTrimmedGroupName($val['SelectionType']);
                $MachineTag = $val['MachineTag'];
                $AgentName = $val['AgentName'];
                $JobCreatedTime = date("m/d/Y h:i A", $val['JobCreatedTime']);

                $chkBox = '<div class="form-group">'
                    . '<div class="checkbox">'
                    . '<label>'
                    . '<input type="checkbox" class="check user_check" name="checkNoc" id="' . $AID . '" value="' . $AID . '" onclick="uniqueCheckBox();">'
                    . '<span class="checkbox-material">'
                    . '<span class="check">'
                    . '</span>'
                    . '</span>'
                    . '</label>'
                    . '</div>'
                    . '</div>';

                $recordList[] = array(
                    "check_data" => $chkBox,
                    "ProfileName" => $ProfileName,
                    "SelectionType" => $SelectionType,
                    "MachineTag" => $MachineTag,
                    "AgentName" => $AgentName,
                    "JobCreatedTime" => $JobCreatedTime
                );
            }
        }
    } else {
        $recordList = array();
    }

    $jsonData = array("draw" => $draw, "recordsTotal" => $total, "recordsFiltered" => $total, "data" => $recordList);
    return $jsonData;
}

function FORMAT_ADMN_AuditGrid($res, $pdo)
{
    $recordList = [];
    $dartArr = array();
    $i = 0;
    foreach ($res as $key => $value) {
        $valVal = getBetween($value['detail'], '<b>', '</b>');
        $sql = $pdo->prepare("select scop from " . $GLOBALS['PREFIX'] . "core.Variables where name = ?");
        $sql->execute([$valVal]);
        $query_p = $sql->fetch(PDO::FETCH_ASSOC);
        $dartno = $query_p['scop'];
        $id = $value['auditid'];
        if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {

            $userLoggedTime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $value['time']);

            $time = '<p class="ellipsis" title="' . $userLoggedTime . '">' . $userLoggedTime . '</p>';
        } else {
            $time = '<p class="ellipsis" title="' . date("m/d/Y h:i A", $value['time']) . '">' . date("m/d/Y h:i A", $value['time']) . '</p>';
        }
        $username = $value['user'];
        $detailrow = '<p class="ellipsis" id="detailaudit" style="white-space: pre-line" title="' . strip_tags($value['detail']) . '">' . strip_tags($value['detail']) . '</p>';


        $recordList[$i][] = $time;
        $recordList[$i][] = $dartno;
        $recordList[$i][] = $username;
        $recordList[$i][] = $detailrow;
        $recordList[$i][] = $id;

        $i++;
    }

    return $recordList;
}


function getBetween($string, $start = "", $end = "")
{
    if (strpos($string, $start)) {
        $startCharCount = strpos($string, $start) + strlen($start);
        $firstSubStr = substr($string, $startCharCount, strlen($string));
        $endCharCount = strpos($firstSubStr, $end);
        if ($endCharCount == 0) {
            $endCharCount = strlen($firstSubStr);
        }
        return substr($firstSubStr, 0, $endCharCount);
    } else {
        return '';
    }
}
