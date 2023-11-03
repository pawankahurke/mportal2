<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once 'lib/l-db.php';
include_once 'lib/l-sql.php';
include_once 'lib/l-gsql.php';
include_once 'lib/l-rcmd.php';
include_once 'include/common_functions.php';


if (!nhUser::isAllowSignUp()) {
    // NCP-671 Unrestricted User Self registration (Allow to block self registration)   
    die('Sign up is not allowed'); // add env var DASHBOARD_AllowSignUp=true to allow sign up
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/apple-icon.ico" ?>" />
    <link rel="icon" type="image/png" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/favicon.ico" ?>" />
    <title>Dashboard :: Sign-Up</title>
    <!-- CSS Files -->
    <link href="assets/css/family=Montserrat.css" rel="stylesheet">
    <link href="assets/css/all.css" rel="stylesheet">
    <link href="assets/css/icons.css" rel="stylesheet" />
    <link href="<?php echo $base_url; ?>assets/css/login.css" rel="stylesheet" />
</head>

<body style='width:100%;height:100%'>
    <div class="containerLogin">
        <div class="loginContImg">
            <div class="loginImg"></div>
        </div>
        <form name="form" id="forgetPassForm" class="loginForm" method="post">
            <div class="card_login">
                <div class="signed-up-start-block">
                    <div>
                        <div class="login-titleText">Create an account</div>
                        <div>
                            <div id="frgt-pwd-title" class="loginText">Please provide the details to Sign-Up</div>
                        </div>
                        <div style="margin:20px 0px">
                            <input type="text" class="form-control" style='margin: 10px 0px' data-cy="newfirstname" id="newfirstname" placeholder="First Name" name="email"><input type="hidden" name="mainloginform" id="mainloginform" value="1">
                            <input type="text" class="form-control" style='margin: 10px 0px' data-cy="newLastName" id="newLastName" placeholder="Last Name" name="email"><input type="hidden" name="mainloginform" id="mainloginform" value="1">
                            <input type="text" class="form-control" style='margin: 10px 0px' data-cy="newuseremail" id="newuseremail" placeholder="Email" name="email"><input type="hidden" name="mainloginform" id="mainloginform" value="1">
                        </div>
                    </div>
                    <div>
                        <div style='display:flex; justify-content:space-between;'>
                            <button type="button" class="btnLogin" style='width:45%;' data-cy="createAccountSubmit" onclick="submitForgetForm();">Submit</button>
                            <button type="button" class="btnLogin" style='width:45%;' onclick="location.href = 'index.php';">Cancel</button>
                        </div>
                        <p id="passErroMsg" style="color:#000000;font-family: OpenSans;">
                            <?php echo $emailMsg; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="card_login signed-up-success-block">
                <img class="signed-up-success-img" src="../Dashboard/assets/img/starCheck.png" alt="">
                <div class="signed-up-success-title">You're all signed up!</div>
                <div class="signed-up-success-text">Please check your email for the confirmation message that we have just send you</div>
            </div>
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
            var fname = document.getElementById("newfirstname").value;
            var lname = document.getElementById("newLastName").value;
            var newemail = document.getElementById("newuseremail").value;
            if (fname != "" && lname != '' && newemail != '') {
                $("#passErroMsg").html('');
                addNewUser(fname, lname, newemail);
            } else {
                if (fname == '') {
                    $("#passErroMsg").html('<lable style="color:#ec250d;"><span>Please enter first name.</span></lable>');
                } else if (lname == '') {
                    $("#passErroMsg").html('<lable style="color:#ec250d;"><span>Please enter last name.</span></lable>');
                } else if (newemail == '') {
                    $("#passErroMsg").html('<lable style="color:#ec250d;"><span>Please enter email id.</span></lable>');
                }
            }
            return false;
        }

        function addNewUser(firstname, lastname, newEmail) {
            $.ajax({
                url: 'signUpFunction.php',
                type: 'POST',
                data: {
                    'function': 'SignupNewUser',
                    'firstname': firstname,
                    'lastname': lastname,
                    'newEmail': newEmail,
                    'csrfMagicToken': csrfMagicToken
                },
                success: function(msg) {
                    if (msg == 'success') {
                        $('.signed-up-start-block').css('display', 'none');
                        $('.signed-up-success-block').css('display', 'block');
                    } else {
                        $('#passErroMsg').html(msg).css('color', '#ec250d');
                    }
                },
                error: function(err) {
                    console.log(err);
                }
            })
            console.log(firstname, lastname, newEmail);
        }
    </script>
</body>

</html>