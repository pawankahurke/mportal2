<?php
include_once $_SERVER['DOCUMENT_ROOT'] .  '/Dashboard/config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/apple-icon.ico" ?>" />
    <link rel="icon" type="image/png" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/favicon.ico" ?>" />
    <title>Dashboard :: Login</title>
    <script>
        var domUrl = '<?php echo $base_url; ?>';
    </script>
    <link href="../assets/css/family=Montserrat.css" rel="stylesheet">
    <link href="../assets/css/all.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/icons.css" rel="stylesheet" />
    <link href="<?php echo $base_url; ?>assets/css/login.css" rel="stylesheet" />
    <script src="<?php echo $base_url; ?>assets/js/core/jquery.min.js"></script>
</head>

<body>