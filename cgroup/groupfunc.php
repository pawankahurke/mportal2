<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
$pdo = pdo_connect();



checkGroupname($pdo);

function checkGroupname($pdo) {
    $file = $_FILES['file']['tmp_name'];
    $handle = fopen($file, "r");

    $minimumTwoMachinesFound = true;
    for ($z = 0; $z <= 2; $z++) {
        $line = fgets($handle);
        if ($z >= 2) {
            if (!isset($line) || !$line || empty($line)) {
                $minimumTwoMachinesFound = false;
                break;
            }
        }
    }

//    if (!$minimumTwoMachinesFound) {
//      $jsonreturn = array('error' => 'no-minimum-machines');
//      return json_encode($jsonreturn);
//    }

  if (!hasCsvContent('file')) {
      $jsonreturn = array('error' => 'nodata');
      return $jsonreturn;
    }

  $styleName = array();
  $handle = fopen($file, "r");
  $styleName = getStylesName($pdo, $styleName);
  $headerName = array('Site', 'Machine');
  $headerName = array_merge($headerName, $styleName);
  $stylearray = array();
  $allstylegroup = array();
  while ($data = fgetcsv($handle, 1000, ",", "'")) {
      if ($data[0] && $data[0] != 'Site') {
            foreach ($headerName as $index => $style) {
                if ($style !== 'Site' && $style != "Machine") {
                    if (!empty($data[$index])) {
                        $det = strpos($data[$index], "|") != false ? array_map('trim', explode("|", $data[$index])) : trim($data[$index]);
                        if (isset($stylearray[$style]) && is_array($stylearray[$style])) {

                            if (is_array($det)) {
                                $stylearray[$style] = array_merge($stylearray[$style], $det);
                            } else {
                                array_push($stylearray[$style], $det);
                            }

                            $stylearray[$style] = array_values(array_unique($stylearray[$style]));
                        } else {
                            if (is_array($det)) {
                                $stylearray[$style] = $det;
                            } else {
                                $stylearray[$style] = array($data[$index]);
                            }
                        }
                    }
                }
            }
        }
    }
    $allstylegroup = $stylearray;
    foreach ($styleName as $style) {
        $compArray = array();
        if (isset($stylearray[$style])) {
            $groupin = "'" . strtolower(implode("','", $stylearray[$style])) . "'";
            $groupsql = $pdo->query("select name from ".$GLOBALS['PREFIX']."core.MachineGroups where lower(name) IN ($groupin)");
            $groupres = $groupsql->fetchAll();
            foreach ($groupres as $gres) {
                array_push($compArray, $gres['name']);
            }
            $stylearray[$style] = array_values(array_diff($stylearray[$style], $compArray));
        }
    }

    echo json_encode(array("uniqueGroup" => $stylearray, 'allGroup' => $allstylegroup));
}

function generate_CSV($pdo) {

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=sampleGroup.csv');
    $output = fopen("php://output", "w");
    $header = array('Site', 'Machine');
    $header = getStylesName($pdo, $header);
    fputcsv($output, $header);

    $censussel = $pdo->query('select site,host from '.$GLOBALS['PREFIX'].'core.Census');
    $censusres = $censussel->fetchAll();
    foreach ($censusres as $cres) {
        $dat = array();
        foreach ($header as $h) {
            if ($h == 'Site') {
                array_push($dat, $cres['site']);
            } else if ($h == 'Machine') {
                array_push($dat, $cres['host']);
            } else {
                array_push($dat, '');
            }
        }

        fputcsv($output, $dat);
    }

    fclose($output);
}

function getStylesName($pdo, $header) {
    $stylesel = $pdo->query('select * from '.$GLOBALS['PREFIX'].'core.group_styles');
    $styleres = $stylesel->fetchAll();
    foreach ($styleres as $styler) {
        array_push($header, $styler['style_name']);
    }
    return $header;
}

function getStylesNumber($pdo, $header) {
    $stylesel = $pdo->query('select * from '.$GLOBALS['PREFIX'].'core.group_styles');
    $styleres = $stylesel->fetchAll();
    foreach ($styleres as $styler) {
        array_push($header, $styler['style_number']);
    }
    return $header;
}

function hasCsvContent($filename) {
    $file = $_FILES[$filename]['tmp_name'];
    $handle = fopen($file, "r");
    $count = 0;

    for ($i = 0; $i < 2; $i++) {
        $line = fgetcsv($handle);
        if ($i == 1) {
            $hasContent = (isset($line) && $line) ? true : false;
            break;
        }
    }

    return $hasContent;
}

?>
