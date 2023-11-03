<?php



include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom(); 
include_once '../lib/l-dashboard.php';

function SWD_RunSQL_Query($key, $db, $sql, $type) {

    $Result = [];

    $key = DASH_ValidateKey($key);

    if ($key) {

        if ($type == "insup") {

            $Result = redcommand($sql, $db);
        } else if ($type == "one") {

            $Result = find_one($sql, $db);
        } else if ($type == "many") {

            $Result = find_many($sql, $db);
        } else if ($type == 'del') {

            mysqli_query($db, $sql);
        }
    } else {

        echo "Your key has been expired";
    }

    return $Result;
}
