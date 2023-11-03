<?php

/*
This file forwards to main/install/download.php.
Used so that a URL sent via email can be shorter

Revision history:

Date        Who     What
----        ---     ----
12-Aug-03   NL      Initial creation.
12-Aug-03   NL      Paste in a copy of get_argument (cant include l-util cause it's encoded).
12-Aug-03   NL      Look for short QS args ($e,$r), not $siteemailid and $regcode.
12-Aug-03   NL      get_argument(): remove references to magic_unquote.
*/

if (isset($_GET['r']) && isset($_GET['e']) && !(isset($_GET['download']))) { ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="apple-touch-icon" sizes="76x76" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/apple-icon.ico"; ?>" />
        <link rel="icon" type="image/png" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/favicon.ico"; ?>" />
        <title>Dowload Nanoheal Client</title>
        <!-- CSS Files -->
        <link href="../../assets/css/family=Montserrat.css" rel="stylesheet">
        <link href="../../assets/css/all.css" rel="stylesheet">
        <link href="../../assets/css/icons.css" rel="stylesheet" />
        <link href="../../assets/css/styles.css" rel="stylesheet" />
        <link href="../../assets/css/common.css" rel="stylesheet" />
    </head>

    <body class="login-page">
        <div class="wrapper wrapper-full-page">
            <div class="full-page login-page">
                <div class="content white-content" style="padding-top: 17%;">
                    <div class="container">
                        <div class="col-lg-4 col-md-6 ml-auto mr-auto">
                            <div class="card card-login card-white">
                                <div class="card-header" style="border-bottom: 2px solid lightgrey;
                    padding-top: 10px;">
                                    <img src="../../assets/img/logo.png" alt=" " style="width: auto; max-width: 100%;" />
                                </div>
                                <div class="card-body" style="padding-left: 25px;">
                                    <p>Thanks for downloading the Nanoheal client.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>

    <?php $host = $_SERVER['HTTP_HOST'];
    $regcode = $_GET['r'];
    $e = $_GET['e'];

    $url = "https://$host/Dashboard/Provision/install/d.php?r=$regcode&e=$e&download=Y";
    ?>

    <script src="../../assets/js/core/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            debugger;
            location.href = '<?php echo $url; ?>';
        });
    </script>

    </html>
<?php }

if (isset($_GET['download']) && $_GET['download'] == 'Y') {
    ob_start();

    function get_argument($name, $default)
    {
        $valu = $default;

        if ((isset($_GET)) || (isset($_POST))) {
            if (isset($_GET[$name]))  $valu = $_GET[$name];
            if (isset($_POST[$name])) $valu = $_POST[$name];
        } else {
            if (isset($GLOBALS['HTTP_GET_VARS'][$name]))
                $valu = $GLOBALS['HTTP_GET_VARS'][$name];
            if (isset($GLOBALS['HTTP_POST_VARS'][$name]))
                $valu = $GLOBALS['HTTP_POST_VARS'][$name];
        }
        return $valu;
    }

    $ssl  = (isset($_SERVER['HTTPS'])) ? 1 : 0;
    $host = $_SERVER['HTTP_HOST'];
    $http = ($ssl) ? 'https' : 'http';
    $page = $_SERVER['PHP_SELF'];
    $folderPath = explode('/', $page)[1];
    // echo $folderPath;exit;
    //New code
    $serverProtocol  = 'https://';
    $servername = $_SERVER['HTTP_HOST'];
    // $page = $folderPath . 'license/install/download.php';
    $page = 'Dashboard/Provision/install/download.php';
    $siteemailid = get_argument('e', '0');
    $regcode    = get_argument('r', '0');
    $url        = "$page?regcode=$regcode&siteemailid=$siteemailid";
    // echo  $http."://".$host."/".$url;exit;
    ob_end_clean();
    // header("Location: $url");
    header("Location: https://$host/$url");
}

