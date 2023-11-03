<?php
ini_set('max_execution_time', '300');
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../lib/l-db.php';
$pdo = pdo_connect();
$sitelist = url::postToAny('sitelist');
generate_CSV($pdo,$sitelist);

function generate_CSV($pdo, $sitelist)
{
  $user_name = $_SESSION['user']['username'];
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=sampleGroup.csv');
  $output = fopen("php://output", "w");
  $header = array('Site', 'Machine');
  $header = getStylesName($pdo, $header);
  fputcsv($output, $header);

  if ($sitelist != "") {
    $sitearray = explode(',', $sitelist);
    $site_in = str_repeat('?,', safe_count($sitearray) - 1) . '?';
    $Censussel = $pdo->prepare("select Censusuniq,site,host from " . $GLOBALS['PREFIX'] . "core.Census where site in ($site_in)");
    $Censussel->execute($sitearray);
    $Censusres = $Censussel->fetchAll();
    foreach ($Censusres as $cres) {
      $dat = array();

      foreach ($header as $h) {
        if ($h == 'Site') {
          array_push($dat, $cres['site']);
        } else if ($h == 'Machine') {
          array_push($dat, $cres['host']);
        } else if ($h != 'Site' && $h != 'Machine') {
          $groupDet = $pdo->prepare("select mg.name from " . $GLOBALS['PREFIX'] . "core.MachineGroups mg ,core.MachineGroupMap mp  where mg.mgroupuniq = mp.mgroupuniq and mg.username = ?  and mp.censusuniq = ? and mg.boolstring = ? ");
          $groupDet->execute([$user_name, $cres['Censusuniq'], $h]);
          $groupres = $groupDet->fetchAll(PDO::FETCH_COLUMN, 0);

          if (!empty($groupres)) {
            $grps = implode("|", $groupres);
            array_push($dat, $grps);
          } else {
            array_push($dat, '');
          }
        } else {
          array_push($dat, '');
        }

      }
      fputcsv($output, $dat);
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
