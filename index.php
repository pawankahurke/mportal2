<?php
include_once "config.php";
include_once 'layout/header-login.php';
?>
<div class="containerLogin">
    <div class="loginContImg">
        <div class="loginImg"></div>
    </div>
    <form class="loginForm" name="form" data-qa="loginForm" id="loginForm">

        <div class="card_login">
            <div>
                <div class="login-titleText">Login to your Account</div>
            </div>
            <div id="sso_option" style="display:none" class="sso_option">
                <div class="sso-btn" id="ssoauthbtn">
                    <span style="cursor: pointer;">Login using SSO</span>
                </div>
                <div style="text-align: center;">

                    <div class="loginText" style=" background: #fff; display: inline-block; position: relative; z-index: 5; padding: 0px 14px; opacity:1 ;">
                        <div style="opacity:0.5">or Sign in with Email</div>
                    </div>

                    <hr style="position: relative; width: 100%; padding: 0px; margin: 0px; top: -30px;">

                </div>
            </div>
            <div id="card-body-login" style="margin-bottom: 30px;">
                <div class="email_Password">Email</div>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Email" data-cy="login" id="email" name="email" autocomplete="off" value="">
                </div>
                <div class="email_Password">Password</div>
                <div class="form-group">
                    <div class="cont-password">
                        <input type="password" class="form-control" data-cy="password" name="password" id="password" value="" placeholder="Password" maxlength="40">
                        <div class="cont-eyeIcon"> <a href="#" class="eyeIcon1"><img id="eyeIcon1" src="assets/img/eye-icon.png" alt="logo" onclick="toggleMask('password', 'eyeIcon1')"></a></div>
                        <input type="hidden" id="input-password" name="authtype" value="" />
                    </div>
                    <div style="margin: 10px 0px;">
                        <span id="fpln" class="link-red">Forgot Password?</span>
                    </div>
                </div>
                <div class="form-check">
                    <div id="error" class="link footer-link text-center" style="color:#000000;font-family: OpenSans;">

                    </div>
                </div>
                <input type="hidden" name="unenc_password" id="unenc_password" value="<?php if (isset($_SESSION['login_success_count'])) {
                                                                                            echo intval($_SESSION['login_success_count']);
                                                                                        } ?>" />
                <input type="hidden" name="mainloginform" id="mainloginform" value="1" />
            </div>
            <div>
                <button type="button" class="btnLogin" data-cy="loginSubmitId" id="loginSubmitId">Login</button>
            </div>
        </div>
        <div class="footer-login">
            <?php if (nhUser::isAllowSignUp()) { ?>
                <div class='signUpLink-footer'>
                    Not Registered Yet?
                    <span id="signUpLink" class="signUpLink">Create an account</span>
                </div>
            <?php } ?>
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