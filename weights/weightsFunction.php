<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once $absDocRoot . 'lib/l-db.php';
include_once $absDocRoot . 'lib/l-dbConnect.php';
require_once $absDocRoot . 'lib/l-sql.php';
require_once $absDocRoot . 'lib/l-gsql.php';
require_once $absDocRoot . 'lib/l-rcmd.php';
require_once $absDocRoot . 'lib/l-util.php';
require_once $absDocRoot . 'lib/l-setTimeZone.php';
require_once '../include/common_functions.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';


nhRole::dieIfnoRoles(['dashboardview']);

//Replace $routes['post'] with if else
if (url::postToText('function') === 'getWeightDetails') {  // roles: dashboardview
    get_WeightDetails();
} else if (url::postToText('function') === 'getDetails') { // roles: dashboardview
    get_Details();
} else if (url::postToText('function') === 'getTypeDetails') { // roles: dashboardview
    get_TypeDetails();
} else if (url::postToText('function') === 'addNewWeight') { // roles: dashboardview
    add_NewWeight();
} else if (url::getToText('function') === 'exportWeightDetails') { // roles: dashboardview
    export_WeightDetails();
}

function get_WeightDetails()
{
    $pdo = pdo_connect();

    $limitCount = (url::postToInt('limitCount') == 0) ? 10 : url::postToInt('limitCount');
    $curPage = url::postToInt('nextPage') - 1;

    $orderVal = url::postToStringAz09('order');
    $sortVal = url::postToStringAz09('sort');

    if ($orderVal != '') {
        $orderStr = 'order by ' . $orderVal . ' ' . $sortVal;
    } else {
        $orderStr = 'order by id desc';
    }

    $notifSearch = url::postToText('notifSearch');
    if ($notifSearch != '') {
        $whereSearch = " where (MetricName LIKE '%" . $notifSearch . "%'
        OR Category LIKE '%" . $notifSearch . "%'
        OR subcategory LIKE '%" . $notifSearch . "%'
        OR MetricDesc LIKE '%" . $notifSearch . "%'
        OR SpecificInfo LIKE '%" . $notifSearch . "%')";
    } else {
        $whereSearch = '';
    }

    $limitStart = $limitCount * $curPage;
    $limitEnd = $limitStart + $limitCount;

    if ($limitStart > 0) {
        $limitStr = " LIMIT " . $limitStart . "," . $limitCount;
    } else {
        $limitStr = " LIMIT " . $limitStart . "," . $limitEnd;
    }

    $sql = $pdo->prepare("select MetricName,Category,subcategory,MetricDesc,SpecificInfo,id from " . $GLOBALS['PREFIX'] . "analytics_test.Scores $whereSearch $orderStr $limitStr");
    $sql->execute();
    $result = $sql->fetchAll();

    $csql = $pdo->prepare("select MetricName,Category,subcategory,MetricDesc,SpecificInfo,id from " . $GLOBALS['PREFIX'] . "analytics_test.Scores $whereSearch $orderStr");
    $csql->execute();
    $totCount = safe_count($csql->fetchAll());

    if (safe_sizeof($result) == 0) {
        $dataArr['largeDataPaginationHtml'] =  '';
        $dataArr['html'] =   '';
        echo json_encode($dataArr);
    } else {
        $dataArr['largeDataPaginationHtml'] = largeDataPagination($totCount, $limitCount, $limitEnd, $curPage, $nocName);
        $dataArr['html'] =  Format_WeightsDataMysql($result);
        echo json_encode($dataArr);
    }
}

function Format_WeightsDataMysql($result)
{
    $recordList = [];
    $i = 0;
    foreach ($result as $lastMachine) {
        $recordList[$i][] = $lastMachine['MetricName'];
        $recordList[$i][] =  $lastMachine['Category'];
        $recordList[$i][] =  $lastMachine['subcategory'];
        $recordList[$i][] = $lastMachine['MetricDesc'];
        $recordList[$i][] = $lastMachine['SpecificInfo'];
        $recordList[$i][] = $lastMachine['id'];
        $i++;
    }
    return $recordList;
}

function get_Details()
{
    $pdo = pdo_connect();
    $id = url::postToStringAz09('id');

    $sql = $pdo->prepare("select * from " . $GLOBALS['PREFIX'] . "analytics_test.Scores where id = ?");
    $sql->execute([$id]);
    $result = $sql->fetch();
    // print_R($result);

    echo json_encode($result);
}

function get_TypeDetails()
{
    $pdo = pdo_connect();
    $id = url::postToStringAz09('id');
    $type = url::postToStringAz09('type');

    // if($type == 'add'){
    $sql1 = $pdo->prepare("select distinct MetricName from " . $GLOBALS['PREFIX'] . "analytics_test.Scores");
    $sql1->execute();
    $result1 = $sql1->fetchAll();

    $sql2 = $pdo->prepare("select distinct Category from " . $GLOBALS['PREFIX'] . "analytics_test.Scores");
    $sql2->execute();
    $result2 = $sql2->fetchAll();

    $sql3 = $pdo->prepare("select distinct subcategory from " . $GLOBALS['PREFIX'] . "analytics_test.Scores");
    $sql3->execute();
    $result3 = $sql3->fetchAll();

    $sql4 = $pdo->prepare("select distinct SpecificInfo from " . $GLOBALS['PREFIX'] . "analytics_test.Scores");
    $sql4->execute();
    $result4 = $sql4->fetchAll();

    // }else{
    //selected for Edit


    if ($type == 'edit') {
        $sql = $pdo->prepare("select MetricName,Category,subcategory,SpecificInfo,Scores,MetricDesc from " . $GLOBALS['PREFIX'] . "analytics_test.Scores where id = ?");
        $sql->execute([$id]);
        $resultN = $sql->fetch();

        $Mname = $resultN['MetricName'];
        $wCat = $resultN['Category'];
        $wSCat = $resultN['subcategory'];
        $wSInfo = $resultN['SpecificInfo'];
        $MetricDesc = $resultN['MetricDesc'];

        $Scores = $resultN['Scores'];
        $valScores = safe_json_decode($Scores, true);
        $valScores = json_encode($valScores['range']);
    }

    foreach ($result1 as $value) {
        if ($value['MetricName'] === $Mname) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        $ListData1 .= '<option value="' . $value['MetricName'] . '" ' . $selected . '>' . $value['MetricName'] . '</option>';
    }

    foreach ($result2 as $value) {
        if ($value['Category'] === $wCat) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        $ListData2 .= '<option value="' . $value['Category'] . '" ' . $selected . '>' . $value['Category'] . '</option>';
    }

    foreach ($result3 as $value) {
        if ($value['subcategory'] === $wSCat) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        $ListData3 .= '<option value="' . $value['subcategory'] . '" ' . $selected . '>' . $value['subcategory'] . '</option>';
    }

    foreach ($result4 as $value) {
        if ($value['SpecificInfo'] === $wSInfo) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        $ListData4 .= '<option value="' . $value['SpecificInfo'] . '" ' . $selected . '>' . $value['SpecificInfo'] . '</option>';
    }

    $recordList = array(
        "MerticName" => $ListData1,
        "Category" => $ListData2,
        "subcategory" => $ListData3,
        "SpecificInfo" => $wSInfo,
        "MetricDesc" => $MetricDesc,
        "Scores" => $valScores
    );
    echo json_encode($recordList);
}

function add_NewWeight()
{
    $pdo = pdo_connect();
    $updateType = url::postToText('updateType');

    $name = url::postToText('Type');
    $category = url::postToText('Category');
    $SubCat = url::postToText('SubCat');
    $Description = url::postToText('Description');
    $Attr = url::postToText('Attr');
    $id = url::postToText('selected');

    $to = url::postToText('to');
    $from = url::postToText('from');
    $rank = url::postToText('rank');
    $score = url::postToText('score');
    $mw = url::postToText('mw');
    $cw = url::postToText('cw');
    $scw = url::postToText('scw');

    $toValueArr = explode(',', $to);
    $fromValueArr = explode(',', $from);
    $rankValueArr = explode(',', $rank);
    $scoreValueArr = explode(',', $score);
    $mwValueArr = explode(',', $mw);
    $cwValueArr = explode(',', $cw);
    $scwValueArr = explode(',', $scw);

    $count = safe_count($toValueArr);
    $d = array();

    for ($i = 0; $i < $count; $i++) {
        $d["range"][$i] = array(
            'to' => $toValueArr[$i], 'from' => $fromValueArr[$i], "rank" => $rankValueArr[$i], "score" => $scoreValueArr[$i],
            "mw" => $mwValueArr[$i], "cw" => $cwValueArr[$i], "scw" => $scwValueArr[$i]
        );
    }
    $json = json_encode($d);

    if ($updateType == 'add') {
        $sql = $pdo->prepare("INSERT INTO " . $GLOBALS['PREFIX'] . "analytics_test.Scores (`MetricName`, `Scores`, `Category`, `subcategory`, `MetricDesc`,
        `SpecificInfo`, `CategoryWeightage`, `SCategoryWeightage`, `MetricWeightage`) VALUES(?,?,?,?,?,?,?,?,?)");
        $sql->execute([$name, $json, $category, $SubCat, $Description, $Attr, NULL, NULL, NULL]);
        $res = $pdo->lastInsertId();
    } else {
        $sql = $pdo->prepare("UPDATE " . $GLOBALS['PREFIX'] . "analytics_test.Scores set `MetricName` = ? , `Scores` = ? , `Category` = ?, `subcategory` = ?,
         `MetricDesc` = ?, `SpecificInfo` = ?, `CategoryWeightage` = ?, `SCategoryWeightage` = ?, `MetricWeightage` = ? where id = ?");
        $res = $sql->execute([$name, $json, $category, $SubCat, $Description, $Attr, NULL, NULL, NULL, $id]);
    }

    if ($res) {
        echo "success";
    } else {
        echo "failed";
    }
}


function export_WeightDetails()
{
    $index = 2;

    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
    $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'MetricName');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'from');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'to');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'rank');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'cw');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'mw');
    $objPHPExcel->getActiveSheet()->setCellValue('G1', 'scw');
    $objPHPExcel->getActiveSheet()->setCellValue('H1', 'score');
    $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Category');
    $objPHPExcel->getActiveSheet()->setCellValue('J1', 'subcategory');
    $objPHPExcel->getActiveSheet()->setCellValue('K1', 'MetricDesc');
    $objPHPExcel->getActiveSheet()->setCellValue('L1', 'SpecificInfo');

    $pdo = pdo_connect();
    $sql = $pdo->prepare("select MetricName,Category,subcategory,MetricDesc,SpecificInfo,id,Scores from " . $GLOBALS['PREFIX'] . "analytics_test.Scores order by MetricName asc");
    $sql->execute();
    $result = $sql->fetchAll();
    if (safe_count($result) > 0) {
        foreach ($result as $key => $val) {
            $MetricName = $val['MetricName'];
            $Category = $val['Category'];
            $subcategory = $val['subcategory'];
            $MetricDesc = $val['MetricDesc'];
            $SpecificInfo = $val['SpecificInfo'];
            $Scores = $val['Scores'];
            $Scores = safe_json_decode($Scores, true);
            $ScoresArr = $Scores['range'];
            foreach ($ScoresArr as $k => $v) {
                $from = $v['from'];
                $to = $v['to'];
                $rank = $v['rank'];
                $cw = $v['cw'];
                $mw = $v['mw'];
                $scw = $v['scw'];
                $score = $v['score'];

                $objPHPExcel->setActiveSheetIndex(0);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, '' . $MetricName . '');
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, '' . $from . '');
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, '' . $to  . '');
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, '' . $rank . '');
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, '' . $cw  . '');
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, '' . $mw . '');
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $index, '' . $scw . '');
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $index, '' . $score . '');
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $index, '="' . $Category . '"');
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $index, '' . $subcategory . '');
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $index, '' . $MetricDesc . '');
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $index, '' . $SpecificInfo . '');

                $index++;
            }
        }
    } else {
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No data available');
    }
    $fn = "VisualisationWeightsDetails.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}
