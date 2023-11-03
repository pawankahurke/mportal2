<?php



$zipname = $_SESSION['zipname'] . '.zip';

header('Content-Type: application/zip');
header("Content-Disposition: attachment; filename='$zipname'");
header('Content-Length: ' . filesize($zipname));
header("Location: $zipname");
