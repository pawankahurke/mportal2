<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
if (!url::issetInGet('vid') || url::isEmptyInGet('vid')) {
    header('Location: index.php');
}

include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'lib/l-db.php';
include_once 'lib/l-sql.php';
include_once 'lib/l-gsql.php';
include_once 'lib/l-rcmd.php';
include_once 'lib/l-loginAjax.php';

$idVal = (url::issetInRequest('vid')) ? url::getToText('vid') : "";
$langsel = url::issetInRequest('lng') ? url::requestToText('lng') : '';

?>

<!DOCTYPE html>
<html lang="en">
<style>
  .card-footer{
    background-color: #fff!important;
  }
</style>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/apple-icon.ico" ?>" />
    <link rel="icon" type="image/png" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/favicon.ico" ?>" />
    <title>Dashboard :: Reset Password</title>
    <!-- CSS Files -->
    <link href="assets/css/family=Montserrat.css" rel="stylesheet">
    <link href="assets/css/all.css" rel="stylesheet">
    <link href="assets/css/icons.css" rel="stylesheet" />
    <link href="assets/css/styles.css" rel="stylesheet" />
    <link href="assets/css/common.css" rel="stylesheet" />
  <link href="<?php echo $base_url; ?>assets/css/login.css" rel="stylesheet" />
</head>

<body class="reset-page" style='width:100%;height:100%'>
<div class="containerLogin">
  <div class="loginContImg">
    <div class="loginImg"></div>
  </div>
  <form name="form" id="forgetPassForm" class=" loginForm form-validation" action="reset-password.php" method="post">
    <div class="card_login">
      <div class="signed-up-start-block">
        <div>
          <div class="login-titleText" style="text-align: left">Create new Password</div>
          <div style="color: red; background-color:#efefef;width: 160px;cursor: pointer" onclick="showTextValidation()">Show password rules <span class="arrow-pass-bt"> &#9660;</span></div>
          <div class="pass-valid-block" style="background-color: #efefef; padding-top: 10px;font-size: 11px; display: none" data-active="false">
            <div style="padding-bottom: 10px">Your password must contains:</div>
            Atleast 8 Characters
            <br>
            Atleast 1 upper case character (A - Z)
            <br>
            Atleast 1 lower case character (a - z)
            <br>
            Atleast 1 number (0 - 9)
            br
            Atleast 1 special character (~!@#$%^&*_-+=`|\(){})
          </div>
        </div>
        <div style="margin:20px 0px" class="form-group invalidsession">
          <input type="hidden" name="userid" value="<?php echo url::toText($idVal); ?>" id="userid" />
          <input type="password" class="form-control" id="passwordval" placeholder="New Password" style="color: #000">
          <span class="input-group-addon"><a href="#" class="eye-icon" id="view1-eye-icon"></a></span>
          <a href="#" class="eyeIcon"><img id="eyeIcon1" src="assets/img/eye-icon.png" alt="logo" onclick="toggleMask('passwordval', 'eyeIcon1')"></a>
        </div>
        <div class="form-group invalidsession">
          <input type="password" class="form-control" id="repassword" placeholder="Confirm New Password" style="color: #000">
          <span class="input-group-addon"><a href="#" class="eye-icon" id="view2-eye-icon"></a></span>
          <a href="#" class="eyeIcon"><img id="eyeIcon2" src="assets/img/eye-icon.png" alt="logo" onclick="toggleMask('repassword', 'eyeIcon2')"></a>
        </div>
        <h6>
          <div class="form-check">
            <div class="validateProcess error"></div>
          </div>
        </h6>
      </div>

      <div class="card-footer text-center reset-pass-btn"><input type="hidden" name="mainloginform" id="mainloginform" value="1"">
        <button type="button" class="btn btn-success btn-md btn mb-3" onclick="location.href='index.php';">Cancel</button>
        <button type="button" class="btn btn-success btn-md btn mb-3 invalidsession" id='login-btn'>Submit</button>
        <p id="passErroMsg">
        </p>
      </div>
      </div>
    <div class="card_login signed-up-success-block">
      <img class="signed-up-success-img" src="../Dashboard/assets/img/starCheck.png" alt="">
      <div class="signed-up-success-title">Password updated!</div>
      <div class="signed-up-success-text">Your password has been changed successfully Use your new password to login to your account </div>
      <div class="card-footer text-center reset-pass-btn-login" data-qa='gotologinpage'><input type="hidden" name="mainloginform" id="mainloginform" value="1" >
        <button type="button" class="btn btn-success btn-md btn mb-3" onclick="location.href='index.php';">Login now</button>
      </div>
    </div>


    </div>

  </form>
</div>

    <!--   Core JS Files   -->
    <script src="assets/js/core/jquery.min.js"></script>
    <script src="js/login/login.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/tether/1.3.1/js/tether.min.js"></script>
    <script>
        $(document).ready(function() {

            var vid = '<?php echo $idVal; ?>';
            validateSession(vid);

            $('#view1-eye-icon').mousedown(function() {
                $('#passwordval').attr('type', 'text');
            }).bind('mouseup mouseleave', function() {
                $('#passwordval').attr('type', 'password');
            });

            $('#view2-eye-icon').mousedown(function() {
                $('#repassword').attr('type', 'text');
            }).bind('mouseup mouseleave', function() {
                $('#repassword').attr('type', 'password');
            });

        });

        function gotologinpage() {
            location.href = "index.php";
        }

        function showTextValidation() {
          if ($(".pass-valid-block").attr('data-active') == 'false'){
            $(".pass-valid-block").css('display','block');
            $(".pass-valid-block").attr('data-active','true');
            $(".arrow-pass-bt").html(' &#9650;');
          }else{
            $(".pass-valid-block").css('display','none');
            $(".pass-valid-block").attr('data-active','false');
            $(".arrow-pass-bt").html(' &#9660;');
          }

        }
    </script>
</body>

</html>
