<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/apple-icon.ico" ?>" />
    <link rel="icon" type="image/png" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/favicon.ico" ?>" />
    <title>Database is corrupted</title>
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
                    <div class="col-lg-4 col-md-6 ml-auto mr-auto">
                        <form name="form" class="form-validation" action="login.php" method="POST" id="otpForm">
                            <div class="card card-login card-white">
                                <div class="card-header text-center">
                                    <img src="assets/img/logo.png" alt="">
                                </div>
                                <input type="hidden" name="visited" value="" />
                                <div class="text-center">
                                    <p>Database is corrupted</p>
                                    <p><?php echo $_GET['errorText']; ?></p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/core/jquery.min.js"></script>
</body>

</html>