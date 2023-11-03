<?php


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once '../lib/l-util.php';
include_once '../lib/l-dashboard.php';
include_once '../lib/l-elastic.php';
include_once '../lib/l-elasticReport.php';
require_once '../libraries/PHPExcel.php';
require_once '../libraries/PHPExcel/IOFactory.php';
require_once '../libraries/mpdf/mpdf.php';

$viewId = UTIL_GetString("viewId", "0");
$exportType = UTIL_GetString("exportType", "0");

if ($exportType == "1") {
    exportViewToExcel($viewId);
} else if ($exportType == "2") {
    exportViewToPDF($viewId);
}


function exportViewToExcel($viewId)
{
    $conn = db_connect();
    $viewDetails = ELRPT_GetSectionData($conn, $viewId);
    $excelTitle  = $viewDetails['name'];
    $objPHPExcel = new PHPExcel();
    foreach ($viewDetails['sectionData'] as $sheetIndex => $value) {
        $secType = $value['secType'];
        $sectionId = $value['secId'];
        switch ($secType) {
            case "1":
                $objPHPExcel = prepareEventSectionSheet($conn, $objPHPExcel, $sectionId, $viewId, $value['sectionName'], $sheetIndex);
                break;
            case "2":
                $objPHPExcel = prepareAssetSectionSheet($conn, $objPHPExcel, $sectionId, $viewId, $value['sectionName'], $sheetIndex);
                break;
            case "3":
                $objPHPExcel = prepareMUMSectionSheet($conn, $objPHPExcel, $sectionId, $viewId, $value['sectionName'], $sheetIndex, $value);
                break;
            default:
                break;
        }
    }

    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $excelTitle . "_" . date("Y-m-d") . '.xls"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
}

function prepareEventSectionSheet($conn, $objPHPExcel, $sectionId, $viewId, $sectionName, $sheetIndex)
{
    if ($sheetIndex == 0) {
        $objPHPExcel->setActiveSheetIndex(0)->setTitle(substr($sectionName . '...', 0, 28));
        $objPHPExcel = createEventSectionData($conn, $objPHPExcel, $sectionId, $sheetIndex);
    } else {
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex($sheetIndex)->setTitle(substr($sectionName . '...', 0, 28));
        $objPHPExcel = createEventSectionData($conn, $objPHPExcel, $sectionId, $sheetIndex);
    }
    return $objPHPExcel;
}

function createEventSectionData($conn, $objPHPExcel, $sectionId, $sheetIndex)
{
    $assetData = ELRPT_GetEventSectionDetails($conn, $sectionId);
    $index = 2;
    $objPHPExcel = prepareEventSectionHeader($objPHPExcel, $sheetIndex);
    foreach ($assetData['data'] as $key => $value) {
        $text = '';
        if (isset($value['text1'])) {
            $text = $value['text1'];
        } else if (isset($value['text2'])) {
            $text = $value['text2'];
        } else if (isset($value['text3'])) {
            $text = $value['text3'];
        } else if (isset($value['text4'])) {
            $text = $value['text4'];
        }
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['machine']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, UTIL_GetTrimmedGroupName($value['customer']));
        $tempDay = strtotime(explode("T", $value['enteredDate'])[0]);
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, PHPExcel_Shared_Date::PHPToExcel($tempDay));
        $objPHPExcel->getActiveSheet()->getStyle('C' . $index)->getNumberFormat()->setFormatCode("mm/dd/yyyy");
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $value['description']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $text);
        $index++;
    }
    return $objPHPExcel;
}

function prepareEventSectionHeader($objPHPExcel, $sheetIndex)
{
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "Machine");
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "Site");
    $objPHPExcel->getActiveSheet()->setCellValue('C1', "Date");
    $objPHPExcel->getActiveSheet()->setCellValue('D1', "Description");
    $objPHPExcel->getActiveSheet()->setCellValue('E1', "Text");
    return $objPHPExcel;
}

function prepareAssetSectionSheet($conn, $objPHPExcel, $sectionId, $viewId, $sectionName, $sheetIndex)
{
    if ($sheetIndex == 0) {
        $objPHPExcel->setActiveSheetIndex(0)->setTitle(substr($sectionName . '...', 0, 28));
        $objPHPExcel = createAssetSectionData($conn, $objPHPExcel, $sectionId, $viewId, $sheetIndex);
    } else {
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex($sheetIndex)->setTitle(substr($sectionName . '...', 0, 28));
        $objPHPExcel = createAssetSectionData($conn, $objPHPExcel, $sectionId, $viewId, $sheetIndex);
    }
    return $objPHPExcel;
}

function createAssetSectionData($conn, $objPHPExcel, $sectionId, $viewId, $sheetIndex)
{
    $assetData = ELRPT_GetAssetSectionDetails($conn, $sectionId, $viewId);
    $index = 2;

    foreach ($assetData as $assetkey => $asset) {
        $datanames = $asset['details']['columns'];
        $machineDataId = getDynamicDataId("Machine Name", $assetData);
        $siteDataId = getDynamicDataId("Site Name", $assetData);
        $preparedSection = prepareAssetSectionHeader($conn, $objPHPExcel, $datanames, $sheetIndex, $assetData);
        $objPHPExcel = $preparedSection[0];

        foreach ($asset['details']['rows'] as $key => $value) {
            $i = 67;
            foreach ($value as $key1 => $value1) {
                $columnLetter = chr($i);
                if ($key1 == 'machinename') {
                    $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value1);
                } else if ($key1 == 'sitename') {
                    $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, UTIL_GetTrimmedGroupName($value1));
                } else {
                    if ($preparedSection[1][$key1] != '') {
                        $temp = $preparedSection[1][$key1];
                        $objPHPExcel->getActiveSheet()->setCellValue($temp . $index, $value1);
                    }
                    $i++;
                }
            }
            $index++;
        }
    }
    return $objPHPExcel;
}

function prepareAssetSectionHeader($conn, $objPHPExcel, $datanames, $sheetIndex, $assetData)
{
    $i = 67;
    $machineDataId = getDynamicDataId("Machine Name", $assetData);
    $siteDataId = getDynamicDataId("Site Name", $assetData);
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $tempoRory = [];
    foreach ($datanames as $lable => $dataId) {
        $columnLetter = chr($i);
        if ($dataId == $machineDataId) {
            $objPHPExcel->getActiveSheet()->setCellValue('A' . '1', $lable);
        } else if ($dataId == $siteDataId) {
            $objPHPExcel->getActiveSheet()->setCellValue('B' . '1', $lable);
        } else {
            $temp = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/', '', $lable));
            $temp = str_replace('-', '', $temp);
            $tempoRory[$temp] = $columnLetter;
            $objPHPExcel->getActiveSheet()->setCellValue($columnLetter . '1', $lable);
            $i++;
        }
    }
    return array($objPHPExcel, $tempoRory);
}

function prepareMUMSectionSheet($conn, $objPHPExcel, $sectionId, $viewId, $sectionName, $sheetIndex, $sectionData)
{
    if ($sheetIndex == 0) {
        $objPHPExcel->setActiveSheetIndex(0)->setTitle(substr($sectionName . '...', 0, 28));
        $objPHPExcel = createMUMSectionData($conn, $objPHPExcel, $sectionId, $viewId, $sheetIndex, $sectionData);
    } else {
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex($sheetIndex)->setTitle(substr($sectionName . '...', 0, 28));
        $objPHPExcel = createMUMSectionData($conn, $objPHPExcel, $sectionId, $viewId, $sheetIndex, $sectionData);
    }
    return $objPHPExcel;
}

function createMUMSectionData($conn, $objPHPExcel, $sectionId, $viewId, $sheetIndex, $sectionData)
{
    $mumData = ELRPT_GetMUMData($conn, $sectionData);
    $index = 2;
    $objPHPExcel->setActiveSheetIndex($sheetIndex);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->setCellValue('A1', "Machine");
    $objPHPExcel->getActiveSheet()->setCellValue('B1', "Site Name");
    $objPHPExcel->getActiveSheet()->setCellValue('C1', "Detected Date");
    $objPHPExcel->getActiveSheet()->setCellValue('D1', "Status");
    $objPHPExcel->getActiveSheet()->setCellValue('E1', "Patch Type");
    $objPHPExcel->getActiveSheet()->setCellValue('F1', "Patch Name");

    foreach ($mumData['details'] as $key => $value) {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, $value['host']);
        $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, UTIL_GetTrimmedGroupName($value['site']));
        $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, $value['detected']);
        $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, $value['status']);
        $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, $value['type']);
        $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, $value['patchname']);
        $index++;
    }
    return $objPHPExcel;
}

function exportViewToPDFTemp()
{
    $mpdf = new mPDF('c', 'A4-L', '', '', 20, 50, 40, 20, 20, 20);

    $html = '<img src="../Assets_Graph_103.png"/>';
    $html .= '<img src="../Assets_Graph_107.png"/>';
    $html .= '<img src="../Assets_Graph_103.png"/>';
    $html .= '<img src="../Assets_Graph_102.png"/>';
    $html .= '<img src="../Assets_Graph_65.png"/>';

    $mpdf->WriteHTML($html);
    $mpdf->Output("Asset View.pdf", "D");
}

function createPDFObject($pdfTile, $viewDetails)
{

    $mpdf = new mPDF('c', 'A4-L', '', '', '20', '20', '20', '20');
    $mpdf->mirrorMargins = 0;
    $mpdf->defaultheaderfontsize = 14;
    $mpdf->defaultheaderfontstyle = B;
    $mpdf->defaultheaderline = 0;
    $mpdf->defaultfooterfontsize = 12;
    $mpdf->defaultfooterfontstyle = B;
    $mpdf->defaultfooterline = 0;
    $mpdf->SetHeader($pdfTile . '|');
    $mpdf->SetFooter('{DATE j-m-Y}|{PAGENO}|Nanoheal');
    $mpdf->TOCpagebreak();
    $mpdf->SetWatermarkImage('../vendors/images/nanologo.png');
    $mpdf->showWatermarkImage = true;

    $i = 1;
    $mpdf->TOCpagebreak();

    return $mpdf;
}

function exportViewToPDF($viewId)
{
    $conn = db_connect();
    try {
        $viewDetails = ELRPT_GetSectionData($conn, $viewId);
        $pdfTile = $viewDetails['name'];
        $mpdf = createPDFObject($pdfTile, $viewDetails);
        $html = '<!DOCTYPE html>
        <html>
            <head>
                <style>
                @page {
                       sheet-size: 90mm 55mm;
                       margin: 0;
                }
                body {
                    border:4px double black;
                }
                table {
                    font-family: proxima_nova_rgregular;
                    border-collapse: collapse;
                    width: 100%;
                    font-weight: 400;
                }

                td, th{
                    border: 1px solid #dddddd;
                    text-align: left;
                    padding: 8px;
                    
                }
                th{
                   color: #48b2e4;
                }
                </style>
            </head><body></body></html>';
        $mpdf->WriteHTML($html);

        foreach ($viewDetails['sectionData'] as $sheetIndex => $value) {
            $secType = $value['secType'];
            $sectionId = $value['secId'];
            switch ($secType) {
                case "1":
                    if ($sheetIndex > 0) {
                        $mpdf->AddPage();
                    }
                    $mpdf->TOC_Entry('<a style="text-decoration: none !important;font-size: 17px;color: #48b2e4;font-size: 20px !important;">' . ++$sheetIndex . ' . ' . $value['sectionName'] . '</a>', '{PAGENO}');
                    $mpdf = prepareEventSectionPDF($conn, $sectionId, $viewId, $value['sectionName'], $mpdf);
                    break;
                case "2":
                    if ($sheetIndex > 0) {
                        $mpdf->AddPage();
                    }
                    $mpdf->TOC_Entry('<a style="text-decoration: none !important;font-size: 17px;color: #48b2e4;font-size: 20px !important;">' . ++$sheetIndex . ' . ' . $value['sectionName'] . '</a>', '{PAGENO}');
                    $mpdf = prepareAssetSectionPDF($conn, $sectionId, $viewId, $value['sectionName'], $mpdf);
                    break;
                case "3":
                    if ($sheetIndex > 0) {
                        $mpdf->AddPage();
                    }
                    $mpdf->TOC_Entry('<a style="text-decoration: none !important;font-size: 17px;color: #48b2e4;font-size: 20px !important;">' . ++$sheetIndex . ' . ' . $value['sectionName'] . '</a>', '{PAGENO}');
                    $mpdf = prepareMUMSectionPDF($conn, $sectionId, $viewId, $value['sectionName'], $mpdf, $value);
                    break;
                default:
                    break;
            }
        }
        $mpdf->SetDisplayMode('fullwidth');
        $mpdf->Output($pdfTile . ".pdf", "D");
    } catch (\Mpdf\MpdfException $e) {
        echo $e->getMessage();
    }
}

function prepareEventSectionPDF($conn, $sectionId, $viewId, $sectionName, $mpdf)
{
    $eventData = ELRPT_GetEventSectionDetails($conn, $sectionId);
    $html = preparePDFEventSectionHeader($sectionName, $sectionId);

    foreach ($eventData['data'] as $key => $value) {
        $text = '';
        if (isset($value['text1'])) {
            $text = $value['text1'];
        } else if (isset($value['text2'])) {
            $text = $value['text2'];
        } else if (isset($value['text3'])) {
            $text = $value['text3'];
        } else if (isset($value['text4'])) {
            $text = $value['text4'];
        }

        $html .= "<tr>";
        $html .= "<td>" . $value['machine'] . "</td>";
        $html .= "<td>" . UTIL_GetTrimmedGroupName($value['customer']) . "</td>";
        $time = strtotime(explode("T", $value['enteredDate'])[0]);
        $html .= "<td>" . date('m/d/Y', $time) . "</td>";
        $html .= "<td>" . $value['description'] . "</td>";
        $html .= "<td>" . $text . "</td>";
        $html .= "</tr>";
    }
    $html .= "</table>";
    $mpdf->WriteHTML($html);
    return $mpdf;
}

function preparePDFEventSectionHeader($sectionName, $sectionId, $count)
{

    if ($count > 10) {
        $html = "<h3 style='color: #48b2e4;font-family: 'proxima_nova_rgregular''>" . $sectionName . "</h3><div><span style='color:red;'>Graph cannot be rendered as points exceed the maximum. Please change group by parameter and try again.</span></div>";
    } else {
        $html = "<h3 style='color: #48b2e4;font-family: 'proxima_nova_rgregular''>" . $sectionName . "</h3><div><img src='../reportViews/Events_Graph_" . $sectionId . ".png'></div>";
    }
    $html .= "<div id='" . $sectionName . "'><table><tr>";
    $html .= "<th>Machine</th>";
    $html .= "<th>Site</th>";
    $html .= "<th>Date</th>";
    $html .= "<th>Description</th>";
    $html .= "<th>Text</th>";
    $html .= "</tr>";
    return $html;
}

function prepareAssetSectionPDF($conn, $sectionId, $viewId, $sectionName, $mpdf)
{
    $mpdf->Rect(12, 15.3, 273, 177, "S");
    $assetData = ELRPT_GetAssetSectionDetails($conn, $sectionId, $viewId);
    $datanames = $assetData[0]['details']['columns'];
    $headers = preparePDFAssetSectionHeader($datanames, $sectionName, $sectionId, $assetData);
    $html = $headers[0];
    $headerMapping = $headers[1];
    $machineDataId = getDynamicDataId("Machine Name", $assetData);
    $siteDataId = getDynamicDataId("Site Name", $assetData);
    $tempAssets = [];
    $tempAssets1 = [];
    $mappingTemp = [];
    $mappingTemp1 = [];

    foreach ($headerMapping as $temp => $val) {
        $result = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/', '', $datanames));
        $result = str_replace('-', '', $result);
        $mappingTemp[] = $val;
    }

    foreach ($assetData as $assetkey => $asset) {
        foreach ($asset['details']['rows'] as $key => $value) {
            $i = 67;
            foreach ($value as $key1 => $value1) {
                $columnLetter = chr($i);
                if ($key1 == 'machinename') {
                    $tempAssets1["A"] = $value1;
                } else if ($key1 == 'sitename') {
                    $tempAssets1["B"] = UTIL_GetTrimmedGroupName($value1);
                } else if ($headerMapping[$key1] != '') {
                    $tempAssets1[$headerMapping[$key1]] = $value1;
                    $i++;
                }
            }

            foreach ($tempAssets1 as $key => $vale) {
                $mappingTemp1[] = $key;
            }
            $tempArray = (array_diff($mappingTemp, $mappingTemp1));
            foreach ($tempArray as $vals) {
                $tempAssets1[$vals] = '-';
            }
            ksort($tempAssets1);
            array_push($tempAssets, $tempAssets1);
            $tempAssets1 = [];
        }
    }
    if (safe_count($tempAssets) > 0) {
        foreach ($tempAssets as $key => $tempoRory) {
            $html .= "<tr>";
            foreach ($tempoRory as $val) {
                $html .= "<td>" . $val . "</td>";
            }
            $html .= "</tr>";
        }
    } else {
        $html .= "<tr><td>No data available</td></tr>";
    }
    $html .= "</table></div></div>";
    $mpdf->WriteHTML($html);
    return $mpdf;
}

function preparePDFAssetSectionHeader($datanames, $sectionName, $sectionId, $assetData)
{

    $i = 67;
    $machineDataId = getDynamicDataId("Machine Name", $assetData);
    $siteDataId = getDynamicDataId("Site Name", $assetData);
    $tempoRory = [];
    $tempoRory1 = [];
    foreach ($datanames as $lable => $dataId) {
        $columnLetter = chr($i);
        if ($lable == "Machine Name") {
            $tempoRory["A"] = $lable;
            $tempoRory1['machinename'] = "A";
        } else if ($lable == "Site Name") {
            $tempoRory["B"] = $lable;
            $tempoRory1['sitename'] = "B";
        } else {
            $tempoRory[$columnLetter] = $lable;
            $result = strtolower(preg_replace('/[^a-zA-Z0-9-_\.]/', '', $lable));
            $result = str_replace('-', '', $result);
            $tempoRory1[$result] = $columnLetter;
            $i++;
        }
    }

    ksort($tempoRory);

    $html = "<div style=''><div><h2 id='" . str_replace(" ", "", $value['sectionName']) . "' style='color: #48b2e4;font-family: 'proxima_nova_rgregular''>" . $sectionName . "</h2><img src='../reportViews/Assets_Graph_" . $sectionId . ".png'></div>";
    $html .= "<div ><table><tr>";
    foreach ($tempoRory as $key => $value) {
        $html .= "<th>" . $value . "</th>";
    }
    $html .= "</tr>";
    return array($html, $tempoRory1);
}

function getDynamicDataId($dataname, $assetData)
{
    $dataId = 0;
    foreach ($assetData[0] as $key => $value) {
        if ($key == "details") {
            foreach ($value as $key1 => $value1) {
                if ($key1 == "columns") {
                    foreach ($value1 as $key2 => $value2) {
                        if ($key2 == $dataname) {
                            $dataId = $value2;
                        }
                    }
                }
            }
        }
    }
    return $dataId;
}

function prepareMUMSectionPDF($conn, $sectionId, $viewId, $sectionName, $mpdf, $sectionData)
{
    $mumData = ELRPT_GetMUMData($conn, $sectionData);
    $html = preparePDFMUMSectionHeader($sectionName, $sectionId);
    foreach ($mumData['details'] as $key => $value) {
        $html .= "<tr>";
        $html .= "<td>" . $value['host'] . "</td>";
        $html .= "<td>" . UTIL_GetTrimmedGroupName($value['site']) . "</td>";
        $html .= "<td>" . $value['detected'] . "</td>";
        $html .= "<td>" . $value['status'] . "</td>";
        $html .= "<td>" . $value['type'] . "</td>";
        $html .= "<td>" . $value['patchname'] . "</td>";
        $html .= "</tr>";
    }
    $html .= "</table>";
    $mpdf->WriteHTML($html);
    return $mpdf;
}

function preparePDFMUMSectionHeader($sectionName, $sectionId)
{

    $html = "<h3 style='color: #48b2e4;font-family: 'proxima_nova_rgregular''>" . $sectionName . "</h3><div><img src='../reportViews/MUM_Graph_" . $sectionId . ".png'></div>";
    $html .= "<div id='" . $sectionName . "'><table><tr>";
    $html .= "<th>Machine</th>";
    $html .= "<th>Site Name</th>";
    $html .= "<th>Detected Date</th>";
    $html .= "<th>Status</th>";
    $html .= "<th>Patch Type</th>";
    $html .= "<th>Patch Name</th>";
    $html .= "</tr>";
    return $html;
}
