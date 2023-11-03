<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
require_once  'GoogleAuthenticator.php';
$pga = new GoogleAuthenticator();
global $base_url;
$error_message = '';

if (url::issetInPost('btnValidate')) {

    $code = url::postToAny('code');

    if ($code == "") {
        $error_message = 'Please enter authentication code to validate!';
    } else {
        $db = pdo_connect();
        $userId = $_SESSION['user']['userid'];
        $udstmt = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Users where userid = ?");
        $udstmt->execute([$userId]);
        $udstmtres = $udstmt->fetch(PDO::FETCH_ASSOC);

        $ud_rdata = ['result' => (object)$udstmtres];
        $user_details = (object)$ud_rdata;



        if ($pga->verifyCode($user_details->result->google_secret_code, $code, 2)) {
            $_SESSION["user"]["mulverify"] = true;
            header("location:" . $base_url . "home/index.php");
        } else {
            $error_message = 'Invalid Authentication Code!';
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Validate Login</title>
    <!-- Latest compiled and minified CSS -->
    <!--<link rel="stylesheet" href="css/bootstrap.min.css">-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link href="../assets/css/icons.css" rel="stylesheet" />
    <link href="../assets/css/styles.css" rel="stylesheet" />
    <link href="../assets/css/common.css" rel="stylesheet" />
</head>

<body class="login-page">

    <div class="container" style='font-family: "Montserrat", sans-serif;'>
        <!--<div class="row jumbotron">
        <div class="col-md-12">
            <h2>
                Demo: Using Google Two factor authentication in PHP
            </h2>
            <p>
                Note: This is demo version from iTech Empires tutorials. (Multi-factor Authentication)
            </p>
        </div>
    </div>-->
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <p>&nbsp;</p>
        <div class="row">
            <div class="col-md-5 col-md-offset-3">
                <div class="card card-login card-white">
                    <div class="card-header text-center">
                        <img src="<?php echo $base_url; ?>assets/img/logo.png" alt="">
                    </div>
                    <div class="card-body" id="card-body-login">
                        <h4 style="color:#27293d;font-size:16px;font-family: 'Montserrat', sans-serif;">Application Authentication</h4>

                        <p style="color:#27293d;font-size:13px;font-family: 'Montserrat', sans-serif;">
                            Please enter the code which is shown in authentication (Google,Microsoft) app.
                        </p>

                        <form method="post" action="validate.php">
                            <?php
                            if ($error_message != "") {
                                echo '<div class="alert alert-danger"><strong>Error: </strong> ' . $error_message . '</div>';
                            }
                            ?>
                            <div class="form-group" style="font-size:14px;font-family: 'Montserrat', sans-serif;">
                                <input type="text" name="code" placeholder="Enter Authentication Code" class="form-control" style="font-size:12px;">
                            </div>
                            <div class="form-group" align="center">
                                <button type="submit" name="btnValidate" class="btn btn-primary" style="background:#fc1c70;border-color:#fc1c70;font-size:12px;font-family: 'Montserrat', sans-serif;">Validate</button>
                            </div>
                        </form>

                        <!--<div class="form-group">
                Click here to <a href="index.php">Login</a> if you have already registered your account.
            </div>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>
</body>

</html>