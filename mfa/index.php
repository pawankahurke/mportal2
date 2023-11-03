<?php
include_once "../config.php";
include_once '../layout/header-login.php';
$user = nhUser::getUserInformation($_SESSION['user']['adminEmail'], NanoDB::connect());
if(!$_SESSION['user']['adminEmail']){
    die('You not loggined :(');
}
?>
<div class="containerLogin">
    <div class="loginContImg">
        <div class="loginImg"></div>
    </div>
    <form class="loginForm" name="form" data-qa="loginForm" id="loginForm">
        <input type="hidden" id="auth_token" value="<?php echo base64_encode(url::toText($_SESSION['user']['adminEmail']).":".url::toText($_SESSION['user']['pwd'])); ?>">
        <input type="hidden" id="resend_expire_time" value="<?php echo $user['otp_resend_expiretime'] - time(); ?>">
        <div class="card_login"> 
            <div>
                <div class="login-titleText" style="text-align: inherit;">MFA is enabled on this account</div>
            </div>
            <div id="card-body-login" style="margin-bottom: 30px;">
                <!-- <div class="email_Password">Email</div> -->
                <div class="form-group">
                  <div style="color:#000000;font-family: OpenSans; margin-bottom: 10px;">
                    We have sent an Email OTP to your registered email ID. <br>
                    Please enter it to continue
                  </div>
                    <input type="text" class="form-control" id="otp_code" name="email" autocomplete="off">
                </div>
                <div class="form-group">
                    <div style="margin: 10px 0px;">
                        <span id="resend-opt-code" class="link-red">Resend OPT</span>
                    </div>
                </div>
                <div class="form-check">
                    <div id="timer" class="link footer-link text-center" style="color:#000000;font-family: OpenSans;">

                    </div>
                    <div id="error" class="link footer-link text-center" style="color:#000000;font-family: OpenSans;">
                        
                    </div>
                </div>
            </div>
            <div>
                <button type="button" class="btnLogin" id="loginSubmitId">Login</button>
            </div>
        </div>
        <div class="footer-login" style="margin: 0">
            <div class='signUpLink-footer' style="">
                <span style="color: black;">Not <?php echo url::toText($_SESSION['user']['adminEmail']); ?>?</span> 
                <a href="/Dashboard" id="signUpLink" class="signUpLink">Change login Credentials</a>
            </div>
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
    <div id="absoLoader" class="absoLoaderCont" data-qa="absoLoader" style='display:none;'>
        <img class="absoLoader" src="assets/img/nanohealLoader.gif">
    </div>
</div>
<?php include_once 'layout/footer-login.php'; ?>

<script src="script.js"></script>