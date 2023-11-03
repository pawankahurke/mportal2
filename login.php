<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'lib/l-db.php';
include_once 'lib/l-sql.php';
include_once 'lib/l-gsql.php';
include_once 'lib/l-rcmd.php';
include_once 'lib/l-logAudit.php';
include_once 'include/common_functions.php';

if (isset($_SESSION['user']['dashboardLogin'])) {
    if ($_SESSION["user"]["sso"] || $_SESSION["user"]["mulverify"]) {
        echo '<script type="text/javascript">  debugger; location.href="home/index.php";</script>';
    }
}

global $timer;
$invalid = '';
$azure_valid = 0;
$db = pdo_connect();
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $status_validate = 1;
    $timeZone = url::postToAny('timezone');
    $otp = url::postToText('otp_val');

    $email = $_SESSION['user']['adminEmail'];
    $pwd = $_SESSION['user']['pwd'];

    if ($email != '' && $pwd != '') {
        $loginType = url::postToAny('authtype');
        $signInType = url::issetInPost('signInType') ? url::postToText('signInType') : '';
        $logininfo = array('email' => $email, 'timezone' => $timeZone, 'authtype' => $loginType);
        $_SESSION["userloginfo"] = $logininfo;

        $db = pdo_connect();
        $authentication = CheckAuthentication($db, $email, $pwd, '', $timeZone, $signInType, $otp);
        if ($authentication == 'LOGERR') {
            login_audit($db, $email, 'login', $timeZone, 'Success');
            $logusername = $_SESSION["loguser"];
            $logpwd = $_SESSION["logpass"];
        } else if ($authentication == 'blocked') {
            login_audit($db, $email, 'login', $timeZone, 'Failed');
            $invalid = "<p style='height: 25px; color:#ffffff !important;'>Your account has been blocked.</p>";
        } else if (is_numeric($authentication) || array_key_exists('msg', $authentication)) {
            $invalid = "<p style='height: 30px; color:#f21010 !important;'>Your account has been blocked.Try after 30 minutes</p>";
            header("location:" . $base_url . "index.php");
        } else if (array_key_exists('message', $authentication)) {
            login_audit($db, $email, 'login', $timeZone, 'Failed');
            $msg = $authentication['message'];
            $invalid = "<p style='height: 25px; color:#ff0000 !important;'><span>" . $msg . "</span></p>";
        } else if (is_string($authentication)) {
            login_audit($db, $email, 'login', $timeZone, 'Failed');
            $invalid = "<p style='height: 25px; color:#ff0000 !important;'><span>" . $authentication . "</span></p>";
        } else {
        }
    }
} else {

    if ($_SESSION['loginCount'] > 1 || !isset($_SESSION['loginCount'])) {
        header("location:" . $base_url . "index.php");
    }
    if ($_SESSION['loginCount'] == 1) {
        $_SESSION['loginCount'] = $_SESSION['loginCount'] + 1;
    }
}
if ($_SERVER['REQUEST_METHOD'] === "GET") {
    $host = $_SERVER['HTTP_HOST'];
    $socialCheck = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Socials where provider = ? and status = 1");
    $socialCheck->execute(['Azure']);
    $socialCheckres = $socialCheck->fetch(PDO::FETCH_ASSOC);
    if ($socialCheckres) {
        if ($socialCheckres['provider'] === 'Azure') {
            $azure_valid = 1;
        } else if ($socialCheckres['provider'] === 'SAML') {
            $identity = $socialCheckres['base_dn'];
        }
    }
}
$langsel = url::requestToText('lng');
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
                    <div class="col-lg-4 col-md-6 ml-auto mr-auto">
                        <form name="form" class="form-validation" action="login.php" method="POST" id="otpForm">
                            <div class="card card-login card-white">
                                <div class="card-header text-center">
                                    <img src="assets/img/logo.png" alt="">
                                </div>
                                <input type="hidden" name="visited" value="" />
                                <div class="text-center">
                                    <p>Sign-in to your Account</p>
                                </div>

                                <div class="card-body" id="card-body-login">
                                    <div class="form-group">
                                        <input type="text" class="form-control" placeholder="OTP" id="otp_val" name="otp_val" autocomplete="off" maxlength="6">
                                    </div>
                                    <span id="ReloadMsg" style="color:red;margin-left: 47px;display:none">Please do not refresh the page</span></br>
                                    <?php if (!array_key_exists('msg', $authentication) && !array_key_exists('time', $authentication)) { ?>
                                        <span id="otpmsg" style="color:black"></span>
                                        <input type="hidden" id="time" value="<?php echo url::toText($timer); ?>">
                                        <input type="hidden" id="counter" value="<?php echo url::toText($timer); ?>">
                                    <?php
                                    } else {
                                        $time = $authentication['time'] - time();
                                    ?>
                                        <span id="otpmsg" style="color:black">OTP will expire in <span id="countdown"><?php echo $time; ?></span> seconds</span>
                                        <input type="hidden" id="time" value="<?php echo url::toText($time); ?>">
                                        <input type="hidden" id="counter" value="<?php echo url::toText($timer); ?>">
                                    <?php } ?>

                                    <?php if ($invalid == '') { ?>

                                        <div class="firstotp">
                                            <span id="refreshmsg" style="color:red;margin-left: 47px">Please do not refresh the page</span></br>
                                            <span id="otpmsg" style="color:black">OTP will expire in <span id="countdown"><?php echo $timer; ?></span> seconds</span>
                                            <span style="color:black" id="successmsg">We have sent an OTP to your email. Please enter the OTP to continue.</span>
                                        </div>
                                        <input type="hidden" id="time" value="<?php echo url::toText($timer); ?>">
                                        <input type="hidden" id="counter" value="<?php echo url::toText($timer); ?>">
                                    <?php } ?>

                                    <div class="" id="error" style="color: red;">
                                        <?php echo $invalid; ?>
                                    </div>
                                </div>

                                <div class="card-footer text-center">
                                    <button type="submit" class="btn btn-success btn-md btn mb-3" id="resendotp">Resend</button>
                                    <button type="submit" class="btn btn-success btn-md btn mb-3" id="otpsubmit">Sign In</button>
                                    <div class="text-center">
                                        <h6>
                                            <p id="fpln" class="link footer-link"><u>Forgot Password</u></p>
                                        </h6>
                                    </div>
                                    <?php if ($azure_valid == 1) { ?>
                                        <span style="background:#fff;padding:0 10px;"><span class="text-center" style="font-size:11px;">Or Login with</span></span><br>
                                        <a href="<?php echo url::toText($laravelauth_url . "login/azure"); ?>" style="color:#fc1c70;font-size:13px;">Existing Organization account</a><br><br>
                                    <?php } ?>

                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="langsel" value="<?php echo url::toText($langsel); ?>">
    <input type="hidden" id="name" value="<?php echo url::toText($_SESSION['user']['adminEmail']); ?>">
    <input type="hidden" id="counter" value="<?php echo url::toText($timer); ?>">
    <!--   Core JS Files   -->
    <script src="assets/js/core/jquery.min.js"></script>
    <script src="assets/js/common.js"></script>
    <script src="js/login/login.js"></script>
    <script src="js/common_ajax.js"></script>
    <script>
        otpgen();
    </script>
</body>

</html>