<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';


$pdo = pdo_connect();
$styles = array();
$styleOption = '';

$styles = getStylesName($pdo, array());

if (safe_count($styles) > 0) {
    foreach ($styles as $val) {
        if (!empty($val)) {
            $styleOption .= '<option value="' . $val . '">' . $val . '</option>';
        }
    }
}

$jsonArray =  $styleOption;

echo json_encode($jsonArray);


function getStylesName($pdo, $header)
{
    $stylesel = $pdo->query('select * from ' . $GLOBALS['PREFIX'] . 'core.group_styles');
    $styleres = $stylesel->fetchAll();
    foreach ($styleres as $styler) {
        array_push($header, $styler['style_name']);
    }
    return $header;
}
