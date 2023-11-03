<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-site.php';

$action = url::requestToAny('action');


$pdo = pdo_connect();
if($action  == 'Upload'){
    $db = db_connect();
    $file = $_FILES['csvfile']['tmp_name'];
    $contents = file_get_contents($_FILES['csvfile']['tmp_name']);
    $mgrpuniq = url::postToAny('mgroupuniq');
    $res = SITE_Import($mgrpuniq, $contents, $db); 
   echo $res; 
}else if($action == 'getSites'){
    $sql = $pdo->prepare("select distinct site from ".$GLOBALS['PREFIX']."core.Census");
    $sql->execute();
    $res = $sql->fetchAll(PDO::FETCH_ASSOC);
    $ListData = '';
    foreach($res as $k=>$v){
        $sitename = $v['site'];
        $sql = $pdo->prepare("select * from ".$GLOBALS['PREFIX']."core.MachineGroups where name = ?");
        $sql->execute([$sitename]);
        $res = $sql->fetch(PDO::FETCH_ASSOC);
        $ListData .= '<option value="' . $res['mgroupuniq'] . '" >' . $sitename . '</option>';
    }
    echo $ListData;
}else if($action == 'export'){
	$db = db_connect();    
	$mgrpuniq = url::requestToAny('mgroupuniq');
	$sql = $pdo->prepare("select * from ".$GLOBALS['PREFIX']."core.MachineGroups where mgroupuniq = ?");
        $sql->execute([$mgrpuniq]);
        $result = $sql->fetch(PDO::FETCH_ASSOC);
	$sitename = $result['name'];

	$res = SITE_Export($mgrpuniq, $db);
	$filename = "SitesData_".$sitename.".txt";
	$handle = fopen($filename, "w");
    	fwrite($handle,$res);
    	fclose($handle);

    	header('Content-Type: application/octet-stream');
    	header('Content-Disposition: attachment; filename='.basename($filename));
    	header("Content-Type: application/force-download");
    	header('Expires: 0');
    	header('Cache-Control: must-revalidate');
    	header('Pragma: public');
    	header('Content-Length: ' . filesize($filename));
    	readfile($filename);
}


