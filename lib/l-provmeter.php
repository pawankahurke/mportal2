<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-dashboard.php';
include_once '../lib/l-serv.php';

function PROV_GetProductData($db, $key) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select * from ".$GLOBALS['PREFIX']."provision.Products";
        $sqlres = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }

    return $sqlres;
}

function PROV_GetMeterReportData($db, $key) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select id, name, type, username, created, expires from ".$GLOBALS['PREFIX']."provision.ReportFiles";
        $sqlres = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }

    return $sqlres;
}

function PROV_GetMeterAuditData($db, $key, $username) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sqlsites = "select * from ".$GLOBALS['PREFIX']."core.Customers where username='" . $username . "' order by customer";
        $res = find_many($sqlsites, $db);
        foreach ($res as $key => $value) {
            $sites .= "'" . $value['customer'] . "',";
        }
        $sites = rtrim($sites, ",");
        $startDate = strtotime('-15 days');
        $endDate = time();

                $sql = "select * from ".$GLOBALS['PREFIX']."provision.Audit where sitename IN ('', $sites) ";
        $sqlres = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $sqlres;
}

function PROV_ValidateRptName($db, $key, $rname) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select * from ".$GLOBALS['PREFIX']."provision.ReportFiles where name='$rname' limit 1";
        $sqlres = find_one($sql, $db);
    } else {
        echo "Your key has been expired";
    }

    return $sqlres;
}

function PROV_GetMeterReportInsert($db, $key, $username, $name, $created, $expire, $filePath, $startDate, $endDate, $timeToUse, $reportType, $tempTotBy, $withintotal) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "INSERT INTO ".$GLOBALS['PREFIX']."core.Files (username , name , type , created , expires, counted , path, link) VALUES ('$username' , '$name' , 'Meter Report' , $created , $expire , 0 , '$filePath' , '')";
        $sqlres = redcommand($sql, $db);

        $sqlprov = "INSERT INTO ".$GLOBALS['PREFIX']."provision.ReportFiles (username , name , type , created , expires, counted , path, link, reportstart, reportend, usetime, reporttype, totalby, totalbywith, totalbythen) "
                . "VALUES ('$username' , '$name' , 'Meter Report' , $created , $expire , 0 , '$filePath' , '',$startDate,$endDate,'$timeToUse',"
                . "'$reportType','$tempTotBy','$withintotal','')";
        $sqlprovres = redcommand($sqlprov, $db);
        $id = mysql_insert_id();
    } else {
        echo "Your key has been expired";
    }

        return $id;
}

function PROV_GetConfigureReport($db, $key, $pid, $type, $sites) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $checkSql = "select G.* from ".$GLOBALS['PREFIX']."core.MachineGroups as G, ".$GLOBALS['PREFIX']."core.MachineCategories as C where C.category = 'Site' "
                . "and G.mcatuniq = C.mcatuniq and G.global = 1 and G.human = 0 and G.style = 1 and G.name = '$sites' limit 1;";
        $checkSqlRes = find_one($checkSql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $checkSqlRes;
}

function PROV_GetConfigureReportSQL($db, $key, $pid) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $configSql = "SELECT * FROM ".$GLOBALS['PREFIX']."provision.Products WHERE productid = $pid limit 1";
        $configSqlRes = find_one($configSql, $db);
    } else {
        echo "Your key has been expired";
    }

    return $configSqlRes;
}

function PROV_GetConfPrdctUpdate($db, $key, $site, $pname, $Pchk, $Echk, $Mchk, $pid) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $configProSql = "INSERT INTO ".$GLOBALS['PREFIX']."provision.SiteAssignments SET productid=$pid, sitename='$site', "
                . "enabled=$Echk, metered=$Mchk, provisioned=$pname;";
        $configProSqlRes = redcommand($configProSql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $configProSqlRes;
}

function PROV_GetConfPrdDelete($db, $key, $proid, $username) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select prodname from ".$GLOBALS['PREFIX']."provision.Products where productid=$proid";
        $res = find_one($sql, $db);
        $pname = $res['prodname'];

        $deleteSql = "delete from ".$GLOBALS['PREFIX']."provision.Products where productid = '" . $proid . "' ";
        $deleteSqlres = redcommand($deleteSql, $db);

        $deleteMeterSql = "delete from ".$GLOBALS['PREFIX']."provision.MeterFiles where productid = '" . $proid . "' ";
        redcommand($deleteMeterSql, $db);

        $deleteKeySql = "delete from ".$GLOBALS['PREFIX']."provision.KeyFiles where productid = '" . $proid . "' ";
        redcommand($deleteKeySql, $db);

        audit_update($username, $pname, 'delete', $db);

        return $deleteSqlres;
    } else {
        echo "Your key has been expired";
    }
}

function audit_update($username, $pname, $action, $db) {
    $host = server_name($db);
    $mach = safe_addslashes($host);
    $now = time();
    $pname = safe_addslashes($pname);
    $user = safe_addslashes($username);
    $sql = "insert into ".$GLOBALS['PREFIX']."provision.Audit set who=1, servertime=$now, clienttime=$now,"
            . "product='" . $pname . "', machine='" . $mach . "', owner='" . $user . "', username='" . $user . "',"
            . "action='" . $action . "'";
    $res = redcommand($sql, $db);
}

function PROV_GetEditValProduct($db, $key, $pid) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "SELECT * FROM ".$GLOBALS['PREFIX']."provision.Products WHERE productid = $pid";
        $sqlres = find_one($sql, $db);
    } else {
        echo "Your key has been expired";
    }

    return $sqlres;
}

function PROV_GetEditValMFiles($db, $key, $pid) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "SELECT * FROM ".$GLOBALS['PREFIX']."provision.MeterFiles WHERE productid = $pid";
        $sqlres = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $sqlres;
}

function PROV_GetEditValKyFiles($db, $key, $pid) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "SELECT * FROM ".$GLOBALS['PREFIX']."provision.KeyFiles WHERE productid = $pid";
        $sqlres = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $sqlres;
}

function PROV_GetAddProdCheck($db, $key, $pname) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select prodname from Products where prodname='$pname'";
        $sqlres = find_one($sql, $db);
    } else {
        echo "Your key has been expired";
    }
    return $sqlres;
}

function PROV_GetAddValueSubmit($db, $key, $pname, $glob, $enable, $monitor, $created, $modified, $username) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $productSql = "INSERT INTO ".$GLOBALS['PREFIX']."provision.Products (global, username, prodname, defaultenable, defaultmonitor, created, modified) "
                . "VALUES ($glob,'$username', '$pname', $enable, $monitor, $created, $modified)";
        $productSqlRes = redcommand($productSql, $db);         $productid = mysql_insert_id();
    } else {
        echo "Your key has been expired";
    }

    return $productid;
}

function PROV_GetUploadMeterFilesSubmit($db, $key, $metfile1, $metfile2, $metfile3, $metfile4, $metfile5) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $meterSql = "INSERT INTO ".$GLOBALS['PREFIX']."provision.MeterFiles (productid, filename) VALUES $metfile1 $metfile2 $metfile3 $metfile4 $metfile5";
        $meterSql = trim($meterSql);
        $meterSql = rtrim($meterSql, ",");
        $result = redcommand($meterSql, $db);
        $meterid = mysql_insert_id();
    } else {
        echo "Your key has been expired";
    }

    return $meterid;
}

function PROV_GetUploadKeyFilesSubmit($db, $key, $keyfile1, $keyfile2, $keyfile3, $keyfile4, $keyfile5) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $keySql = "INSERT INTO ".$GLOBALS['PREFIX']."provision.KeyFiles (productid, filename) VALUES $keyfile1 $keyfile2 $keyfile3 $keyfile4 $keyfile5";
        $keySql = trim($keySql);
        $keySql = rtrim($keySql, ",");
        $result = redcommand($keySql, $db);
        $keyid = mysql_insert_id();
    } else {
        echo "Your key has been expired";
    }
    return $keyid;
}

function PROV_GetEditValueSubmit($db, $key, $pname, $proid, $glob, $enable, $monitor, $modified, $username) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $updateProSql = "UPDATE ".$GLOBALS['PREFIX']."provision.Products SET global=$glob, username='$username', prodname='$pname', defaultenable=$enable, "
                . "defaultmonitor=$monitor, modified=$modified WHERE productid=$proid";
        $updateProSqlRes = redcommand($updateProSql, $db);
    } else {
        echo "Your key has been expired";
    }
}

function PROV_GetConfMachineUpdate($db, $key, $pid, $local, $provis, $meters, $prolist, $siteName, $searchVal) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select C.host from ".$GLOBALS['PREFIX']."core.Census as C, ".$GLOBALS['PREFIX']."core.ValueMap as M, ".$GLOBALS['PREFIX']."core.VarValues as X, ".$GLOBALS['PREFIX']."core.Variables as V where M.stat=$local
        and V.scop in ($provis, $meters) and V.name = '$prolist' and C.site = '$siteName' and C.host='$searchVal' and M.censusuniq = C.censusuniq
        and M.varuniq = V.varuniq and M.varuniq = X.varuniq and M.mgroupuniq = X.mgroupuniq;";
        $sqlRes = find_many($sql, $db);
    } else {
        echo "Your key has been expired";
    }

    return $sqlRes;
}

function PROV_GetMeterReportShow($db, $key, $id) {
    $key = DASH_ValidateKey($key);
    if ($key) {

        $sql = "select * from ".$GLOBALS['PREFIX']."provision.ReportFiles where id = $id limit 1";
        $sqlres = find_one($sql, $db);
    } else {
        $msg = 'Your key has been expired';
        print_data($msg);
    }
    return $sqlres;
}

function PROV_GetExcelMeterReport($db, $id) {

    $key = '';
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->setActiveSheetIndex(0);

    $result = PROV_GetMeterReportShow($db, $key, $id);

    $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Report Title ' . ' : ' . $result['name']);
    $objPHPExcel->getActiveSheet()->setCellValue('A2', 'Creator ' . ' : ' . $result['username']);
    $objPHPExcel->getActiveSheet()->setCellValue('A3', 'Report Type ' . ' : ' . $result['reporttype']);
    $objPHPExcel->getActiveSheet()->setCellValue('A4', 'Total By ' . ' : ' . $result['totalby']);
    $objPHPExcel->getActiveSheet()->setCellValue('A5', 'Using ' . ' : ' . $result['usetime']);
    $objPHPExcel->getActiveSheet()->setCellValue('A6', 'Start Date ' . ' : ' . date('d-m-Y h:i:sa', $result['created']));
    $objPHPExcel->getActiveSheet()->setCellValue('A7', 'End Date ' . ' : ' . date('d-m-Y h:i:sa', $result['expires']));

    $rname = $result['name'];
    $fn = $rname . "_" . time() . ".xls";

    $objPHPExcel->setActiveSheetIndex(0);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save(str_replace(__FILE__, '../provmeter/files/' . $fn, __FILE__));

    $recordlist = array('filename' => $fn);
    return $recordlist;
}

function MPROV_InsertSiteAssignments($db, $key, $site, $pname, $Pchk, $Echk, $Mchk, $pid) {
    $key = DASH_ValidateKey($key);
    if ($key) {
        $Echk = (int) $Echk;
        $Mchk = (int) $Mchk;
        $pid = (int) $pid;
        $configProSql = "INSERT INTO ".$GLOBALS['PREFIX']."provision.SiteAssignments SET productid='$pid', sitename='$site', "
                . "enabled='$Echk', metered='$Mchk', provisioned='$Pchk'";
        $configProSqlRes = redcommand($configProSql, $db);
        return $configProSqlRes;
    } else {
        echo "Your key has been expired";
    }
}

function MPROV_UpdateSiteAssignmentsById($db, $key, $siteAssignmentId, $site, $pname, $Pchk, $Echk, $Mchk, $pid) {
    $key = DASH_ValidateKey($key);
    if ($key) {
        if ($siteAssignmentId && is_numeric($siteAssignmentId)) {
            $siteAssignmentId = (int) $siteAssignmentId;
            $Echk = (int) $Echk;
            $Mchk = (int) $Mchk;
            $pid = (int) $pid;
            $configProSql = "UPDATE `".$GLOBALS['PREFIX']."provision`.`SiteAssignments` SET `productid`='$pid', `sitename`='$site', `enabled`='$Echk', `metered`='$Mchk', provisioned='$Pchk' WHERE `id`='$siteAssignmentId'";
            $configProSqlRes = redcommand($configProSql, $db);
            return $configProSqlRes;
        }
    } else {
        echo "Your key has been expired";
    }
}

function MPROV_SearchSiteAssignment($db, $key, $siteName, $pid) {
    $key = DASH_ValidateKey($key);
    if ($key) {
        $sql = "SELECT * from `".$GLOBALS['PREFIX']."provision`.`SiteAssignments` WHERE `productid`='$pid' AND `sitename`='$siteName'";
        $oneRow = find_one($sql, $db);
        return $oneRow;
    } else {
        echo "Your key has been expired";
    }
}
