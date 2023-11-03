<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-db.php';
$pdo = pdo_connect();$grpid = url::getToAny('grpid');
generate_CSV($pdo,$grpid);

function generate_CSV($pdo,$grpid){

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=sampleGroup.csv');
    $output = fopen("php://output", "w");
    $header= array('Site', 'Machine');
        fputcsv($output,$header);

            if($grpid != ""){

        $Censussel = $pdo->prepare("select c.site as Site,c.host as Machine from ".$GLOBALS['PREFIX']."core.Census c,core.MachineGroups mg,core.MachineGroupMap mp where mg.mgroupid = ? and c.censusuniq = mp.censusuniq and mg.mgroupuniq = mp.mgroupuniq ");
        $Censussel->execute([$grpid]);
        $Censusres = $Censussel->fetchAll();
                foreach($Censusres as $cres){
            $dat = array();

            foreach($header as $h){
                if($h == 'Site'){
                    array_push($dat,$cres['Site']);
                }
                else if($h == 'Machine'){
                    array_push($dat,$cres['Machine']);
                }else{
                    array_push($dat,'');
                }

            }

            fputcsv($output,$dat);
                    }
    }

    fclose($output);

}

function getStylesName($pdo,$header){
        $stylesel = $pdo->query('select * from '.$GLOBALS['PREFIX'].'core.group_styles');
        $styleres = $stylesel->fetchAll();
       foreach($styleres as $styler)
    {
           array_push($header,$styler['style_name']);
    }
    return $header;
}
?>
