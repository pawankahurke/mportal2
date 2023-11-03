<?php

$regCode = url::requestToText('r');
$siteemailid = url::requestToText('e');

$imageUrl = 'vendors/images/nanologo.png';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">

<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta name="DownloadOptions" content="noopen" />
    <meta http-equiv='cache-control' content='no-cache'>
    <meta http-equiv='expires' content='0'>
    <meta http-equiv='pragma' content='no-cache'>
    <title>Download</title>
    <!--        <link rel="stylesheet" type="text/css" media="all" href="ui/css/jScrollPane.css">-->
    <link rel="stylesheet" type="text/css" media="all" href="vendors/styles/styles.css">
    <link rel="stylesheet" type="text/css" media="all" href="vendors/styles/config.css">
    <link rel="shortcut icon" href="https://license.nanoheal.com/favicon.ico">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/apple-icon.ico" ?>" />
    <link rel="icon" type="image/png" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/favicon.ico" ?>" />
    <!--<script type="text/javascript" src="jquery-1.8.2.min.js"></script>-->
    <script type="text/javascript" src="vendors/scripts/scripts.js"></script>
    <!--        <script type="text/javascript" src="ui/js/jquery.mousewheel.js"></script>
        <script type="text/javascript" src="ui/js/jScrollPane.js"></script>-->

    <style type="text/css">
        .downimag {
            width: auto;
            padding-left: 10px;
        }

        .downimagIe {
            width: 100%;
        }

        #pane1 {
            min-height: 250px;
        }

        @font-face {
            font-family: 'material';
            font-weight: normal;
            font-style: normal;
        }

        .btn_download {
            display: inline-block;
            vertical-align: top;
            line-height: 22px;
            color: white;
            border: 1px solid white;
            border-radius: 3px;
            text-align: center;
            font-size: 16px;
            padding: 7px 29px;
            text-decoration: none;
            font-family: "material";
        }

        .btn_cancel {
            display: inline-block;
            vertical-align: top;
            line-height: 22px;
            color: white;
            margin-left: 0%;
            border-radius: 3px;
            text-align: center;
            font-size: 16px;
            padding: 7px 29px;
            text-decoration: none;
            font-family: "material";
        }

        .progress-bar {
            float: left;
            width: 0;
            height: 100%;
            font-size: 12px;
            line-height: 20px;
            color: #fff;
            text-align: center;
            background-color: #337ab7;
            -webkit-box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .15);
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .15);
            -webkit-transition: width .6s ease;
            -o-transition: width .6s ease;
            transition: width .6s ease;
        }

        .hover {
            background-color: #00A7DE;
            color: #fff;
        }

        body {
            font-family: "material";
        }

        .progress {
            position: relative;
            font-family: "material";
            background-color: transparent;
            box-shadow: none;
        }

        .progress span {
            line-height: 35px;
            font-size: 14px;
            font-family: "material";
            position: absolute;
            left: 0;
            right: 0;
            /* margin: auto; */
            width: 38px;
            font-size: 14px;
            color: #000;
        }

        .progress-bar {
            background-color: #0096d6;
        }

        .action .progress-start .progress {
            height: 35px;
            border: 1px solid white;
            box-shadow: none;
            border-radius: 3px;
        }

        .action .progress-start {
            display: inline-block;
            vertical-align: top;
            height: 35px;
            color: #1ca2db;
            border-radius: 3px;
            text-align: center;
            font-size: 16px;
            margin: 0px;
            width: 41%;
            position: absolute;
            left: 54%;
        }

        .progress-bar {
            width: 0;
            height: 100%;
            font-size: 12px;
            line-height: 20px;
            color: #fff;
            text-align: center;
            -webkit-box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .15);
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .15);
            -webkit-transition: width .6s ease;
            -o-transition: width .6s ease;
            transition: width .6s ease;
        }

        .login-page .login-box .login-left {
            border-right: 0px;
            width: 340px;
        }
    </style>

</head><!-- //margin-left: 55%; -->

<body>
    <header>
        <div class="container-fluid">
            <div class="row clearfix">
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <div class="logo">
                        <a href="javascript:void(0);"><img src="<?php echo url::toText($imageUrl); ?>" alt="Header Logo..."></a>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                </div>
            </div>
        </div>
        <input type="hidden" id="logoPath" value="<?php echo url::toText($imageUrl); ?>">
    </header>
    <div id="downloadDiv" data-copycon="true" class="login-page login-compu">
        <img src="vendors/images/background.jpg" alt="">
        <div class="login-box login-box-left clearfix" style="color : white;font-size: 16px;">
            <div class="login-left" id="successDiv">
                <!--<h2>Selfheal</h2>-->
                <!--<br>-->
                <p>
                    You are just a few steps away from installing <br>
                    Client. If your download does not <br>
                    start in few seconds, click Download Now.
                </p>
                <!--<button>Download Now</button>-->
                <div class="action" id="actionDiv">
                    <div class="input-group clearfix scan-agins" onmousedown="return false">
                        <button disabled="true" type="button" class="btn btn-lg cancel-btn btn_cancel">Cancel</button>
                        <button type="submit" class="btn btn-lg sign-in-btn btn_download" onclick="downloadClient();">Download Now</button>
                        <div style="float: left;">
                            <div class="progress-start scanProgress" style="display:none;">
                                <div class="progress">
                                    <div class="progress-bar progressbarWidth" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                        <span class="percentage" style="padding-left: 24%;">Downloading</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="login-left" id="error_div" style="display: none;">
                <h2>SelfHeal</h2>
                <br>
                <p>
                    This download url has been blocked. <br>
                    Please contact your administer.<br>
                </p>
            </div>

        </div>
    </div>

    <div id="errorDiv" style="display: none;height: 100%;width: 100%;">
        HERE ERROR PAGE WILL COME.
    </div>
    <footer>
        <div class="footer-wrap">
            <!-- &copy; -->
            <img src="<?php echo url::toText($imageUrl); ?>" alt="Footer Logo..." style="height: 25px;">
        </div>
        <input type="hidden" id="langsel" value="">
    </footer>
    <script>
        function downloadClient() {
            debugger;
            location.href = "install-downloadhelper.php?rcode=<?php echo $regCode; ?>&seid=<?php echo $siteemailid; ?>";
        }

        $(document).ready(function() {
            downloadClient();
            $(".btn_download").hover(function() {
                $(".btn_download").addClass('hover');
            });

            $(".btn_download").mouseleave(function() {
                $(".btn_download").removeClass('hover');
            });

            //$('link[rel=icon],link[rel=shortcut icon]').attr('href', 'favicon.ico');
        });
    </script>
</body>

</html>