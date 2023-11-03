<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-dbConnect.php';

global $db_host;
global $db_user;
global $db_password;
global $db_port;

if (getenv('ACCESS_NHMYSQL') != 'true'){
  header('Location: ../index.php');
}

$allowedUsers = ['admin@nanoheal.com', 'johnsatya@nanoheal.com', 'manika.sharma@nanoheal.com'];
if (!in_array($_SESSION["user"]["adminEmail"], $allowedUsers)) {
    die('Invalid Session..!');
}

$pdo = NanoDB::connect();
if (!$pdo) {
    die('Failed to connect to MySQL...!');
} else {
    $dataView = '';
    $hdrflag = 0;
    $query_valu = url::rawRequest('query-valu');

    if ($query_valu == '') {
        die('Please enter a query to execute..!');
    } else {
        try {
            $stmt = $pdo->prepare($query_valu);
            $res = $stmt->execute();

            if (
                (stripos($query_valu, 'update') !== false) ||
                (stripos($query_valu, 'insert') !== false) ||
                (stripos($query_valu, 'delete') !== false) ||
                (stripos($query_valu, 'create') !== false)
            ) {
                if ($res) {
                    $dataView = '<table border="1"><tr><td><span>Query Result</span></td></tr><tr><td data-qa="QueryExecutedSuccessfully" >Query executed Successfully..!</td></tr></table>';
                } else {
                    $dataView = '<table border="1"><tr><td><span>Query Result</span></td></tr><tr><td data-qa="QueryExecutedFailed" >Query execution failed..!</td></tr></table>';
                }
            } else {
                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $dataView = '<table border="1">';

                if (safe_count($data) > 0) {
                    foreach ($data as $key => $value) {
                        if ($hdrflag == 0) {
                            $dataView .= 'Query Result : ' . safe_count($data) . ' row(s) X ' . count(safe_array_keys($value)) . ' column(s)';
                            $dataView .= '<tr>';
                            foreach (safe_array_keys($value) as $hdrval) {
                                $dataView .= '<td><span>' . $hdrval . '</span></td>';
                            }
                            $dataView .= '</tr>';
                        }
                        $dataView .= '<tr>';
                        foreach ($value as $dval) {
                            if (strlen($dval) > 500) {
                                $dataView .= '<td style="width: 100%;"><textarea style="width: 100%; height: 110px;">' . $dval . '</textarea></td>';
                            } else {
                                $dataView .= '<td>' . $dval . '</td>';
                            }
                        }
                        $dataView .= '</tr>';
                        $hdrflag = 1;
                    }
                } else {
                    $dataView = '<table border="1"><tr><td><span>Query Result</span></td></tr><tr><td>No Record(s) Found..!</td></tr></table>';
                }
                $dataView .= '</table>';
            }
        } catch (Exception $e) {
            $dataView = '<table border="1"><tr><td><span>Query Result</span></td></tr><tr><td data-qa="QueryExecutedFailed" >' . $e . '</td></tr></table>';
        }
        echo $dataView;
    }
}
