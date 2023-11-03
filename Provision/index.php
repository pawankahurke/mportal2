<?php
    ob_start();
    $ssl  = (isset($_SERVER['HTTPS']))? 1 : 0;
    $host = $_SERVER['HTTP_HOST'];
    $uri = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $http = ($ssl)? 'https' : 'http';
    $page = 'dashbrd/dnav.php';
    ob_end_clean();
    header("Location: https://$host$uri/$page");
?>
