<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'lib/l-db.php';
include_once 'lib/l-sql.php';
include_once 'lib/l-gsql.php';
include_once 'lib/l-rcmd.php';
include_once 'include/common_functions.php';

require_once 'lib/l-custAjax2.php';


if (isset($_POST) && $_SERVER['REQUEST_METHOD'] == "POST") {
    $user_email = url::postToText('email');

    $resunlSend = CUSTAJX_ResendUserMail($user_email);

    $emailMsg = '<p>Thank you. We will send you a Password reset link if the email id provided matches the one we have on records. Please follow the instructions sent to reset your account password.</p>
        <p>If you have not received a Password reset link in the next few minutes,</br>
        <p>1. Check your Junk/Spam message folder for an email from noreply@nanoheal.com</p>
        <p>2. Make sure that the Email ID that you have entered matches the one used while registering.</p></p>';
}

$langsel = url::issetInRequest('lng') ? url::requestToText('lng') : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/apple-icon.ico" ?>" />
    <link rel="icon" type="image/png" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/favicon.ico" ?>" />
    <title>Dashboard :: Forgot Password</title>
    <!-- CSS Files -->
    <link href="assets/css/family=Montserrat.css" rel="stylesheet">
    <link href="assets/css/all.css" rel="stylesheet">
    <link href="assets/css/icons.css" rel="stylesheet" />
    <link href="<?php echo $base_url; ?>assets/css/login.css" rel="stylesheet" />
</head>

<body>
    <div class="containerLogin">
        <div class="loginContImg">
            <div class="loginImg"></div>
        </div>

        <form name="form" id="forgetPassForm" class="loginForm" action="forgot-password.php" method="post">
            <div class="card_login">
                <div class="login-titleText">Reset password</div>
                <div class="text-center">
                    <p id="frgt-pwd-title" class="loginText">Enter your email address to reset your password</p>
                </div>
                <div style="margin:20px 0px">
                    <input type="text" class="form-control" id="useremail" placeholder="Email" name="email"><input type="hidden" name="mainloginform" id="mainloginform" value="1">
                </div>

                <div style='display:flex; justify-content:space-between;'>
                    <button type="button" class="btnLogin" style='width:45%;' onclick="submitForgetForm();">Submit</button>
                    <button type="button" class="btnLogin" style='width:45%;' onclick="  debugger; location.href = 'index.php';">Cancel</button>
                </div>

                <div style="color:#000000;padding:15px;font-family: OpenSans;">
                    <p id="passErroMsg">
                    <div id="chekEmail"><?php echo $emailMsg; ?></div>
                    </p>
                </div>
            </div>

            <div class="footer-login">
                <?php if (!empty(getenv('PRIVACY_POLICY_URL'))) { ?>
                    <div class='privacy'>
                        <div class="text-center">
                            <h6>
                                &copy; Copyright <?php echo date('Y'); ?> Nanoheal | <a href="https://www.nanoheal.com/privacy-policy/" target="_blank" class="link">Privacy</a>
                            </h6>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </form>
    </div>


    <!--   Core JS Files   -->
    <script src="assets/js/core/jquery.min.js"></script>
    <script src="assets/demo/demo.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/tether/1.3.1/js/tether.min.js"></script>
    <script>
        function submitForgetForm() {

            var username = document.getElementById("useremail").value.toLowerCase();
            if (username != "") {
                document.getElementById('forgetPassForm').submit();
                $("#passErroMsg").html('');
            } else {

                $("#passErroMsg").html('<lable style="color:#ec250d;"><span>Please enter registered email id.</span></lable>');
                $("#chekEmail").html('')
            }
            return false;
        }
    </script>
</body>

</html>