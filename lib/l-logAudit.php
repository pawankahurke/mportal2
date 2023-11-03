<?php

include_once $absDocRoot . "config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'l-setTimeZone.php';

function login_audit($db, $email, $type, $timeZone, $status)
{
    $db = NanoDB::connect();
    $time = time();
    $ip = $_SERVER['REMOTE_ADDR'];

    $userSql = "SELECT userid,username,firstName,lastName FROM " . $GLOBALS['PREFIX'] . "core.Users where user_email=?";
    $pdo = $db->prepare($userSql);
    $pdo->execute([$email]);
    $userRes = $pdo->fetch(PDO::FETCH_ASSOC);

    $userName = $userRes['username'];
    $firstName = $userRes['firstName'];
    $lastName = $userRes['lastName'];
    $chid = $userRes['userid'];

    if ($userName != '' || $firstName != '' || $lastName != '') {
        $sql = "INSERT INTO " . $GLOBALS['PREFIX'] . "audit.loginAudit (username,firstname,lastname,email,user_password,loginIP,loginTime,timezone,status) VALUES(?,?,?,?,?,?,?,?,?)";
        $bindings = array($userName, $firstName, $lastName, $email, '', $ip, $time, $timeZone, $status);

        $pdo = $db->prepare($sql);
        $sqlRes = $pdo->execute($bindings);
        $str = array(
            "username" => $userName, "firstname" => $firstName, "lastname" => $lastName, "email" => $email,
            "ip" => $ip, "time" => $time, "timezone" => $timeZone, "status" => $status, "cid" => $chid, "type" => $type
        );
    }

    return $sqlRes;
}

function sendDataToNode($data, $db)
{

    $time = time();
    global $nodeurl;
    $url = $nodeurl;

    $json_array = json_encode($data);

    $sql1 = $db->prepare("Insert into " . $GLOBALS['PREFIX'] . "agent.siemAudit (url,type,json,time) values(?,?,?,?)");
    $sql1->execute([$url, 'log', $json_array, $time]);
    $id = $db->lastInsertId();

    $data_string = '{"jsondata":' . $json_array . ',"type":"log"}';
    $username = 'admin';
    $password = 'Nanoheal@123';

    $header = array(
        'Content-Type: application/json',
        "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
        'Content-Length: ' . strlen($data_string)
    );
    try {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        fwrite($fp, $result);
        if ($result == 'success') {
            $sql = $db->prepare("update " . $GLOBALS['PREFIX'] . "agent.siemAudit set response='success' where id=?");
        } else {
            $sql = $db->prepare("update " . $GLOBALS['PREFIX'] . "agent.siemAudit set response='fail'  where id=?");
        }
        $sql->execute([$id]);
        return $result;
        curl_close($ch);
    } catch (Exception $ex) {
        logs::log(__FILE__, __LINE__, $ex, 0);
        fwrite($fp, $ex);
        return "Exception : " . $ex;
    }
}

function LOG_getLoginDetails($db, $limitCount, $curPage, $limitStart, $limitEnd, $notifSearch, $orderStr)
{
    $res = checkModulePrivilege('loginaudit', 2);
    $recordList = [];
    if (!$res) {
        $jsonData = array();
        echo json_encode($jsonData);
        exit();
    }
    $result = LOG_getData($db, $limitCount, $curPage, $limitStart, $limitEnd, $notifSearch, $orderStr);
    $result = safe_json_decode($result, true);
    $data = $result['result'];
    $totCount = $result['count'];

    if (safe_sizeof($data) == 0) {
        $dataArr['largeDataPaginationHtml'] =  '';
        $dataArr['html'] =   '';
        echo json_encode($dataArr);
    } else {
        $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage, $nocName);
        $dataArr['html'] =  Format_LogAuditDataMysql($data);
        echo json_encode($dataArr);
    }
}

function Format_LogAuditDataMysql($result)
{
    $i = 0;
    foreach ($result as $key => $row) {
        $uName = $row['username'];
        $fName = $row['firstname'];
        $lName = $row['lastname'];
        $mail = $row['email'];
        $ip = $row['loginIP'];
        $time = $logintime = date('m/d/Y H:i:s', $row['loginTime']);

        if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
            $userTimeZone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : 'UTC';
            $servertimezone = date_default_timezone_get();
            $date = new DateTime($time, new DateTimeZone($servertimezone));
            $date->setTimezone(new DateTimeZone($userTimeZone));
            $logintime = $date->format('m/d/Y H:i:s');
        }

        $stat = $row['status'];
        $id = $row['id'];

        $recordList[$i][] = '<p id="' . $uName . '" class="ellipsis" title="' . $uName . '">' . $uName . '</p>';
        $recordList[$i][] = '<p id="' . $mail . '" class="ellipsis" title="' . $mail . '">' . $mail . '</p>';
        $recordList[$i][] = '<p id="' . $logintime . '" class="ellipsis" title="' . $logintime . '">' . $logintime . '</p>';
        $recordList[$i][] = '<p id="' . $stat . '" class="ellipsis" title="' . $stat . '">' . $stat . '</p>';
        $recordList[$i][] = $id;
        $i++;
    }
    return $recordList;
}

function LOG_exportLoginDetails($db)
{
    $res = checkModulePrivilege('loginexport', 2);
    if (!$res)
        exit('Permission denied');

    $index = 2;
    $result = LOG_getData($db);
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);

    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(55);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'User Name');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Email Id');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Login Time');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Login IP');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Login Status');
    $objPHPExcel->getActiveSheet()->setTitle("Login Details");

    if (safe_count($result) > 0) {
        foreach ($result as $key => $value1) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value1['username']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value1['email']);
            UTIL_GetExcelFormattedDate($objPHPExcel, $value1['loginTime'], $index, 'C', 'm/d/Y H:mm:s');
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $value1['loginIP']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $value1['status']);
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }

    $fn = "loginDetails.xlsx";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    ob_end_clean();
    $objWriter->save('php://output');
}

function LOG_getLoginRangeDetails($db, $fromDate, $toDate, $level)
{
    $res = checkModulePrivilege('loginRange', 2);
    if (!$res) {
        $jsonData = array();
        echo json_encode($jsonData);
        exit();
    }
    $result = LOG_getDetailData($db, $fromDate, $toDate, $level);

    if (safe_count($result) > 0) {
        foreach ($result as $key => $row) {
            $uName = $row['username'];
            $fName = $row['firstname'];
            $lName = $row['lastname'];
            $mail = $row['email'];
            $ip = $row['loginIP'];
            $time = date('m/d/Y H:i:s', $row['loginTime']);
            $stat = $row['status'];

            $id = $row['id'];
            $userName = '<p id="' . $uName . '" class="ellipsis" title="' . $uName . '">' . $uName . '</p>';
            $emailId = '<p id="' . $mail . '" class="ellipsis" title="' . $mail . '">' . $mail . '</p>';
            $loginTime = '<p id="' . $time . '" class="ellipsis" title="' . $time . '">' . $time . '</p>';
            $loginIp = '<p id="' . $ip . '" class="ellipsis" title="' . $ip . '">' . $ip . '</p>';
            $status = '<p id="' . $stat . '" class="ellipsis" title="' . $stat . '">' . $stat . '</p>';

            $recordList[] = array($userName, $emailId, $loginTime, $loginIp, $status, $id);
        }
    } else {
        $recordList = array();
    }
    return $recordList;
}

function LOG_ExportLogDetails($db, $fromDate, $toDate, $level, $leveltype, $sublistval)
{
    if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
        $userTimeZone = isset($_SESSION['timezone']) ? $_SESSION['timezone'] : date_default_timezone_get();
        $myTimeZone = $userTimeZone;
        $toTimeZone = date_default_timezone_get();

        date_default_timezone_set($myTimeZone);
    }

    $index = 2;
    if ($leveltype == 'User') {
        $result = LOG_getDetailData($db, $fromDate, $toDate, $level, $sublistval);
    } else {
        $result = LOG_getDetailCustomer($db, $fromDate, $toDate, $level, $sublistval);
    }

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);

    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(55);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'User Name');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Email Id');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Login Time');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Login IP');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Login Status');
    $objPHPExcel->getActiveSheet()->setTitle("Login Details");

    if (safe_count($result) > 0) {
        foreach ($result as $key => $value1) {

            if (isset($_SESSION['timezone']) && !empty($_SESSION['timezone']) && !is_null($_SESSION['timezone'])) {
                $userLoggedTime = convertTimeFromTimezone(date_default_timezone_get(), $_SESSION['timezone'], $value1['loginTime']);
            } else {
                $userLoggedTime = date('m/d/Y H:i:s', $value1['loginTime']);
            }

            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value1['username']);
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, $value1['email']);
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $userLoggedTime);

            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $value1['loginIP']);
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $value1['status']);
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }

    (!empty($toTimeZone)) ? date_default_timezone_set($toTimeZone) : date_default_timezone_set('UTC');

    $fn = "logRangeDetails.xlsx";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    ob_end_clean();
    $objWriter->save('php://output');
}

function LOG_getDetailCustomer($db, $fromDateVal, $toDateVal, $level, $sitelist)
{
    $fromDate = strtotime($fromDateVal);
    $toDate = strtotime($toDateVal);
    $where = '';

    if ($level != 'All') {
        $where = "and status = ?";
    }
    $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Customers WHERE customer=? group by username");
    $sql2->execute([$sitelist]);
    $SqlRes = $sql2->fetchAll();
    $usrArray = array();
    foreach ($SqlRes as $key => $val) {
        $username = $val['username'];
        array_push($usrArray, $username);
    }

    $in = str_repeat('?,', safe_count($usrArray) - 1) . '?';

    if ($sitelist == 'All') {
        $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.loginAudit WHERE loginTime >= ? and loginTime <= ? $where");
    } else {
        $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.loginAudit WHERE username in ($in) and loginTime >= ? and loginTime <= ? $where");
    }

    if ($level == 'All' || $level == '') {
        if ($sitelist == 'All') {
            $params = array_merge($usrArray, [$fromDate, $toDate]);
        } else {
            $params = array_merge([$fromDate, $toDate]);
        }
    } else {
        if ($sitelist == 'All') {
            $params = array_merge([$fromDate, $toDate, $level]);
        } else {
            $params = array_merge($usrArray, [$fromDate, $toDate, $level]);
        }
    }
    $sql2->execute($params);
    $result = $sql2->fetchAll();
    return $result;
}

function LOG_getData($db, $limitCount, $curPage, $limitStart, $limitEnd, $notifSearch, $orderStr = '')
{

    $notifSearch = url::postToText('notifSearch');
    if ($notifSearch != '') {
        $notifSearch = strtolower($notifSearch);
        $whereSearch = " and  (username LIKE '%" . $notifSearch . "%'
            OR email LIKE '%" . $notifSearch . "%'  OR status LIKE '%" . $notifSearch . "%')";
    } else {
        $whereSearch = '';
    }

    $retArr = array();
    $userId = $_SESSION['user']['userid'];
    $sql = $db->prepare("SELECT user_email FROM " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
    $sql->execute([$userId]);
    $SqlRes = $sql->fetch();
    $userEmail = $SqlRes['user_email'];
    // $limitStr = 'Limit '.$limitStart.",".$limitEnd;
    // $limitStr = " LIMIT 10 OFFSET ".$limitStart;
    if ($limitStart > 0) {
        $limitStr = " LIMIT " . $limitStart . "," . $limitCount;
    } else {
        $limitStr = " LIMIT " . $limitStart . "," . $limitEnd;
    }
    $usrArr = getChildDetails($userId, 'user_email');
    $usrArrData = array_merge([$userEmail], $usrArr);

    $last24hrs = strtotime('-30 Days');
    $in = str_repeat('?,', safe_count($usrArrData) - 1) . '?';
    $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.loginAudit WHERE email in($in) and loginTime >= ? $whereSearch $orderStr $limitStr");
    $params = array_merge($usrArrData, [$last24hrs]);
    $sql2->execute($params);
    $result = $sql2->fetchAll(PDO::FETCH_ASSOC);
    $count = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.loginAudit WHERE email in($in) and loginTime >= ? $orderStr");
    $count->execute($params);
    $countresult = safe_count($count->fetchAll(PDO::FETCH_ASSOC));

    $retArr['result'] = $result;
    $retArr['count'] = $countresult;
    return json_encode($retArr);
}

function LOG_getDetailData($db, $fromDateVal, $toDateVal, $level, $username)
{
    $fromDate = strtotime($fromDateVal);
    $toDate = strtotime($toDateVal);

    $where = '';

    if ($level != 'All') {
        $where = "and status = ?";
    }

    if ($username == 'All') {
        $userId = $_SESSION['user']['userid'];
        $sql = $db->prepare("SELECT user_email FROM " . $GLOBALS['PREFIX'] . "core.Users where userid=?");
        $sql->execute([$userId]);
        $SqlRes = $sql->fetch();
        $userEmail = $SqlRes['user_email'];
    } else {
        $sql = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE username=?");
        $sql->execute([$username]);
        $SqlRes = $sql->fetch();
        $email = $SqlRes['user_email'];
    }

    $uId = $_SESSION['user']['userid'];
    $usrArr = getChildDetails($uId, 'user_email');
    if(!empty($userEmail)){
        array_push($usrArr, $userEmail);
    }

    if ($level == 'All' || $level == '') {
        if ($username == 'All') {
            $params = array_merge($usrArr, [$fromDate, $toDate]);
        } else {
            $params = array_merge([$email, $fromDate, $toDate]);
        }
    } else {
        if ($username == 'All') {
            $params = array_merge($usrArr, [$fromDate, $toDate, $level]);
        } else {
            $params = array_merge([$email, $fromDate, $toDate, $level]);
        }
    }
    $in = str_repeat('?,', safe_count($usrArr) - 1) . '?';

    if ($username == 'All') {
        $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.loginAudit WHERE email in($in) and loginTime >= ? and loginTime <= ? $where");
    } else {
        $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.loginAudit WHERE email =? and loginTime >= ? and loginTime <= ? $where");
    }

    $sql2->execute($params);
    $result = $sql2->fetchAll();

    return $result;
}

function getUSerDetailList($db)
{
    $username = $_SESSION['user']['logged_username'];
    $userId = $_SESSION['user']['userid'];
    $html = '';

    $sql = $db->prepare("SELECT user_email FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE username=?");
    $sql->execute([$username]);
    $SqlRes = $sql->fetchAll();

    $email = array();
    foreach ($SqlRes as $val) {
        $email[] = $val['user_email'];
    }
    $usrArr = getChildDetails($userId, 'user_email');
    $usrArrData = array_merge($email, $usrArr);
    $in = str_repeat('?,', safe_count($usrArrData) - 1) . '?';

    $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "audit.loginAudit WHERE email in($in)");

    $sql2->execute($usrArrData);
    $result = $sql2->fetchAll();

    $userArray = array();
    foreach ($result as $val) {
        $uname = $val['username'];
        if ($uname != '') {
            array_push($userArray, $uname);
        }
    }
    $finalArray = array_unique($userArray);
    $html .= "<option value='All'>All</option>";
    foreach ($finalArray as $key => $val) {
        $html .= "<option value='$val'>$val</option>";
    }

    echo $html;
}

function getUSerDartDetailList($db)
{
    $username = $_SESSION['user']['logged_username'];
    $userId = $_SESSION['user']['userid'];
    $html = '';
    // $sql = $db->prepare("SELECT user_email FROM " . $GLOBALS['PREFIX'] . "core.Users WHERE username=?");
    // $sql->execute([$username]);
    // $SqlRes = $sql->fetchAll();

    // $email = array();

    // $usrArr = array();
    $sitescommonArr = array();
    // foreach ($SqlRes as $val) {
    //     $email[] = $val['user_email'];
    // }
    $usrArr = getChildDetails($userId, 'username');
    array_push($usrArr, $username);
    $in = str_repeat('?,', safe_count($usrArr) - 1) . '?';
    $siteAccessList = $_SESSION['user']['site_list'];
    foreach ($siteAccessList as $key => $val) {
        $values = $val;
        array_push($sitescommonArr, $values);
    }

    if (empty($sitescommonArr)) {
        echo "<option value='All'>All</option>";
        return;
    }

    $in2 = str_repeat('?,', safe_count($sitescommonArr) - 1) . '?';
    $params = array_merge($usrArr, $sitescommonArr);
    $sql2 = $db->prepare("SELECT * FROM " . $GLOBALS['PREFIX'] . "core.Customers WHERE username in(" . $in . ") and customer in(" . $in2 . ")");
    $sql2->execute($params);
    $result = $sql2->fetchAll();
    $userArray = array();
    foreach ($result as $val) {
        $uname = $val['username'];
        if ($uname != '') {
            array_push($userArray, $uname);
        }
    }
    $finalArray = array_unique($userArray);
    $html .= "<option value='All'>All</option>";
    foreach ($finalArray as $key => $val) {
        $html .= "<option value='$val'>$val</option>";
    }

    echo $html;
}

function getDetailCustomerList($db)
{
    $sitelist = $_SESSION['user']['user_sites'];
    $sitearray = array();
    foreach ($sitelist as $key => $val) {
        $val = str_replace("'", "", $val);
        array_push($sitearray, $val);
    }

    $finalArray = array_unique($sitearray);
    $html = "<option value='All'>All</option>";
    foreach ($finalArray as $key => $val) {
        $html .= "<option value='$val'>$val</option>";
    }

    echo $html;
}
