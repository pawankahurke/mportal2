<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/apple-icon.ico" ?>" />
    <link rel="icon" type="image/png" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/favicon.ico" ?>" />
    <title>Dashboard :: Login</title>
    <!-- CSS Files -->
    <link href="assets/css/family=Montserrat.css" rel="stylesheet">
    <link href="assets/css/all.css" rel="stylesheet">
    <link href="assets/css/icons.css" rel="stylesheet" />
    <link href="assets/css/styles.css" rel="stylesheet" />
    <link href="assets/css/common.css" rel="stylesheet" />
</head>

<body class="login-page">
    <div class="wrapper wrapper-full-page">
        <div class="full-page login-page">
            <div class="content white-content">
                <div class="container">


                    <div class="card card-white">
                        <p>&nbsp;</p>
                        <div class="card-header text-center">
                            <img src="assets/img/logo.png" alt="">
                        </div>
                        <div class="card-body text-center">
                            <h3 style="color:#665c5c;">You logged out of your account. It's a good idea to close all browser windows. </h3>

                        </div>
                        <p>&nbsp;</p>
                        <p>&nbsp;</p>

                    </div>

                </div>
            </div>
        </div>
    </div>
</body>

</html>