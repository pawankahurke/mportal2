<?php
require_once("../include/common_functions.php");
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$token = url::issetInRequest('token') ? url::requestToAny('token') : "";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/apple-icon.ico" ?>" />
    <link rel="icon" type="image/png" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/favicon.ico" ?>" />
    <title>Dashboard :: SignUp</title>
    <!-- CSS Files -->
    <!--  <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />-->
    <link href="../assets/css/family=Montserrat.css" rel="stylesheet">
    <link href="../assets/css/all.css" rel="stylesheet">
    <link href="../assets/css/icons.css" rel="stylesheet" />
    <link href="../assets/css/styles.css" rel="stylesheet" />
    <link href="../assets/css/common.css" rel="stylesheet" />
</head>

<body class="register-page">
    <div id="getToken" style="display:none;">
        <?php echo  $token ?>
    </div>
    <div class="wrapper wrapper-full-page">
        <div class="full-page register-page">
            <div class="content white-content">
                <div class="container">
                    <div class="col-lg-5 col-md-7 ml-auto mr-auto">
                        <form class="form">
                            <div class="card card-login card-white">
                                <div class="card-header text-center">
                                    <img src="../assets/img/logo.png" alt="">

                                    <p>Your Email Id is verified. Please enter the following details to continue</p>
                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            <div class="form-group">
                                                <input type="text" class="form-control validatesignupform" name="firstname" id="py_firstname" placeholder="First Name" maxlength="20">
                                                <span id="signupFirstNameErr" class="error"></span>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            <div class="form-group">
                                                <input type="text" class="form-control validatesignupform" name="lastname" id="py_lastname" placeholder="Last Name" maxlength="20">
                                                <span id="signuplastnameErr" class="error"></span>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <input type="text" class="form-control validatesignupform" name="companyname" id="py_companyname" placeholder="Company Name" maxlength="30">
                                        <span id="signupcompanyameErr" class="error"></span>
                                    </div>

                                    <div class="form-group">
                                        <input type="text" class="form-control validatesignupform" name="email" value="" id="py_email" placeholder="Your company Email Id" maxlength="40" readonly>
                                        <span id="validemailError" class="error"></span>
                                    </div>

                                    <div class="row">
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            <div class="form-group">
                                                <input type="password" class="form-control validatesignupform" name="py_setpassword" id="py_setpassword" placeholder="Set Password" maxlength="40" onkeyup="validatePassword()">
                                                <a href="#" class="eyeIcon"><img id="eyeIcon1" src="../assets/img/eye-icon.png" alt="logo" class='handCusror' onclick="toggleMask('py_setpassword', 'eyeIcon1')"></a>
                                                <span id="setpasswordError" class="error"></span>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            <div class="form-group">
                                                <input type="password" class="form-control validatesignupform" name="py_confirmpassword" id="py_confirmpassword" placeholder="Confirm password" maxlength="40" onkeyup="validatePassword()">
                                                <a href="#" class="eyeIcon"><img id="eyeIcon2" src="../assets/img/eye-icon.png" alt="logo" onclick="toggleMask('py_confirmpassword', 'eyeIcon2')" class='handCusror'></a>
                                                <span id="confirmpasswordError" class="error"></span>
                                            </div>
                                        </div>

                                        <div id="message" class="msgTxt" style=" display:none;">
                                            <h5 style="color : black;">Password must contain the following:</h5>
                                            <p id="letter"><a id="lowercaseCheck" style="font-family: wingdings; color: red;">&#x2717;</a> A <b>lowercase </b> letter </p>
                                            <p id="capital"><a id="capitalCheck" style="font-family: wingdings; color: red;">&#x2717;</a> A <b>capital (uppercase)</b> letter</p>
                                            <p id="number"><a id="numberCheck" style="font-family: wingdings; color: red;">&#x2717;</a> A <b>number</b></p>
                                            <p id="length"><a id="minimumNumCheck" style="font-family: wingdings; color: red;">&#x2717;</a> Minimum <b>8 characters</b></p>
                                        </div>
                                    </div>

                                    <div class="form-group agree-tc">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="validatesignupform" name="" id="py_agree">
                                                <span class="form-check-sign"></span>
                                            </label>
                                        </div>
                                        <span class="link footer-link"> I agree to Nanoheal’s <a href="https://nanoheal.com/terms-conditions/" target="_blank">Terms of Use</a> and <a href="https://nanoheal.com/privacy-policy/" target="_blank">Privacy Policy.</a></span>
                                        <div id="required_py_agree"></div>
                                    </div>

                                    <!--                                    <ul id="signup_error_div" style="display: none;">
									<li id="signup_error" style="color:darkred;text-align:center;">An email has been sent to admin1@nanoheal.com to verify your email address.
									</li>
                                    </ul>-->
                                </div>

                                <div class="card-footer text-center">
                                    <div id="passwordMatch"></div>
                                    <button type="button" value="Sign Up" onclick="signup('1')" id="mspsignup_submit" class="btn btn-success btn-md btn mb-3">Sign Up</button>
                                    <div class="loader"><img src="../assets/img/loader-sm.gif" alt="" style="display: none;"></div>
                                </div>

                                <!--                                <div id="success_signup_div" class="thanks pricing-thanks-right" style="padding-bottom:25%;display:none;background:url(../images/login-bg.jpg)">
                                    <h3>Thank you for signing up!</h3>
                                    <ul class="thanks-message">
                                        <li id="thanks_message_text">An email has been sent to admin1@nanoheal.com to verify your email address.</li>
                                    </ul>
                                </div>-->
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--   Core JS Files   -->
    <script src="../assets/js/core/jquery.min.js"></script>
    <script src="../js/signup/signup.js"></script>
    <script src="../js/common_ajax.js"></script>
</body>

</html>