<?php 



function Export_Reseller($data) {
    
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Reseller');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'First Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Last Name');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Reseller Email');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Status');
    
    $index = 2;
    if(count(data) > 0 ) {
        foreach ($data as $key => $val) {
            
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, '' . UTIL_GetTrimmedSiteName($val['companyName']) . '');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, '' . $val['firstName'] . '');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, '' . $val['lastName'] . '');
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, '' . $val['emailId'] . '');
            $status = $val['status'] == 1 ? 'Enabled' : 'Disabled';
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, '' . $status . '');
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }
   
    $fn = "ResellerDetails.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');
    
}


function Export_Customer($data) {
    
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Customer Name');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'First Name');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Last Name');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Customer Email');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Status');
    
    $index = 2;
    if(count(data) > 0 ) {
        foreach ($data as $key => $val) {
            
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, '' . UTIL_GetTrimmedSiteName($val['companyName']) . '');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, '' . $val['firstName'] . '');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, '' . $val['lastName'] . '');
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, '' . $val['emailId'] . '');
            $status = $val['status'] == 1 ? 'Enabled' : 'Disabled';
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, '' . $status . '');
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }
   
    $fn = "CustomerDetails.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');  
    
}

function Export_OrderDetails($data) {
    
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->getStyle("A1:Z1")->getFont()->setBold(true);
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    
    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Customer Name');
    $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Order Number');
    $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Order Date');
    $objPHPExcel->getActiveSheet()->setCellValue('D1', 'License Count');
    $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Expiry Date');
    $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Instal Count');
    
    
    $index = 2;
    if(count(data) > 0 ) {
        foreach ($data as $key => $val) {
            
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, '' . UTIL_GetTrimmedSiteName($val['companyName']) . '');
            $objPHPExcel->getActiveSheet()->setCellValue('B' . $index, '' . $val['orderNum'] . '');
            $objPHPExcel->getActiveSheet()->setCellValue('C' . $index, '' . $val['orderDt'] . '');
            $objPHPExcel->getActiveSheet()->setCellValue('D' . $index, '' . $val['noOfPc'] . '');
            $objPHPExcel->getActiveSheet()->setCellValue('E' . $index, '' . $val['contractEndDt'] . '');
            $objPHPExcel->getActiveSheet()->setCellValue('F' . $index, '' . $val['instalCount'] . '');
            $index++;
        }
    } else {
        $objPHPExcel->getActiveSheet()->setCellValue('A' . $index, 'No Data Available');
    }
   
    $fn = "OrderDetails.xls";
    $objPHPExcel->setActiveSheetIndex(0);
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $fn . '"');
    header('Cache-Control: max-age=0');
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    ob_end_clean();
    $objWriter->save('php://output');  
    
}