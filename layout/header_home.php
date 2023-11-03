<?php
$name = 'usertoken';
$value = $_SESSION['token'];
$expirationTime = 0;    // Session cookie.
$path = '/';
$domain = '';
$isSecure = true;
$isHttpOnly = true;
setcookie($name, $value, $expirationTime, $path, $domain, $isSecure, $isHttpOnly);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="Expires" content="<?php echo gmdate('D, d M Y H:i:s', time() + (3600 * 24 * 365)) . ' GMT' ?>" />
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/apple-icon.ico" ?>" />
    <link rel="icon" type="image/png" href="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/favicon.ico" ?>" />
    <title>Dashboard</title>
    <!-- CSS Files -->
    <!--  <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />-->
    <link href="../assets/css/family=Montserrat.css" rel="stylesheet">
    <link href="../assets/css/all.css" rel="stylesheet">
    <link href="../assets/css/icons.css" rel="stylesheet" />
    <link href="../assets/css/styles.css" rel="stylesheet" />
    <link href="../assets/css/common.css" rel="stylesheet" />
    <link href="../assets/css/bootstrap-colorpicker.css" rel="stylesheet">

    <script src="../assets/js/core/jquery.min.js"></script>
</head>

<body class="sidebar-mini">
    <div id="absoFeed"></div>
    <div id="rsc-blur-loader" style="z-index: 100;" class="hide"></div>
    <div id="fullWrapper" class="wrapper" <?php if ($isLoggedin) { ?>style="display:block;height:0px" <?php } ?>>

        <?php
        require_once 'rolesValues.php';
        ?>

        <style>
            .side-bar-menu {
                background: none !important;
                background-color: #050d30 !important;
            }

            .side-bar-menu ul li:hover {
                /*background-color: #131e4e !important;*/
                background-color: #1E2446 !important;
            }

            .menu-new-dch {
                text-align: center;
                font-size: small;
                border-bottom: 1px solid grey;
                text-transform: none !important;
            }

            .menu-l1-view {
                float: right;
                position: fixed;
                margin-left: 80px;
                background-color: #050d30;
                margin-top: -30px;
                border-left: 1px solid #bbb;
                width: 210px;
                letter-spacing: 1px;
            }

            .menu-l2-view {
                float: right;
                position: fixed;
                margin-left: 209px;
                background-color: #050d30;
                margin-top: -38px;
                border-left: 1px solid #bbb;
                width: 210px;
            }
        </style>

        <div class="sidebar side-bar-menu">
            <div class="sidebar-wrapper">
                <div class="logo">
                    <a id="sdb-main-logo-v8-n" href="javascript:void(0);" class="simple-text logo-mini">
                        <img src="../assets/img/NH.png">;
                    </a>
                    <a href="../home/index.php" class="simple-text logo-normal" style="margin-top:1px">
                        Nanoheal
                    </a>
                </div>
                <ul class="nav">
                    <?php require_once('profile-bar.php')?>
                </ul>
            </div>
        </div>

        <div id="mainPanelContent" class="main-panel" <?php if ($isLoggedin) { ?>style="display:none" <?php } ?>>

            <input type="hidden" id="url_id" value="">
            <input type="hidden" id="hiddenUserID" value="<?php echo $userID; ?> ">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-absolute navbar-transparent">
                <div class="container-fluid">
                    <div class="navbar-wrapper">

                    </div>
                    <div class="collapse navbar-collapse" id="navigation">
                        <ul class="navbar-nav ml-auto">

                            <li class="dropdown nav-item">
                                <a href="#" class="dropdown-toggle nav-link hdr-drop" data-toggle="dropdown">
                                    <div class="user-name" id="uname" title="">

                                    </div>
                                    <b class="caret d-none d-lg-block d-xl-block"></b>
                                    <p class="d-lg-none">
                                        Log out
                                    </p>
                                </a>
                                <ul class="dropdown-menu dropdown-navbar">
                                    <!--<li class="nav-link <?php echo setRoleForAnchorTag('profileEdit', 2); ?>">
                                            <a href="javascript:void(0)" class="nav-item dropdown-item" id="profDispButt">Profile</a>
                                        </li>
                                        <li class="dropdown-divider"></li>-->
                                    <li class="nav-link">
                                        <a href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/logout.php" ?>" class="nav-item dropdown-item">Log out</a>
                                    </li>
                                </ul>
                            </li>
                            <li class="separator d-lg-none"></li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="modal modal-search fade" id="searchModal" tabindex="-1" role="dialog" aria-labelledby="searchModal" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <input type="text" class="form-control" id="inlineFormInputGroup" placeholder="SEARCH">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <i class="tim-icons icon-simple-remove"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="profilepicture-add-container" class="rightSidenav" data-class="sm-3">
                <div class="card-title border-bottom">
                    <h4 class="pt-2">Profile</h4>
                    <a href="javascript:void(0)" class="closebtn profilepicture-container-close border-0" data-target="profilepicture-add-container">&times;</a>
                </div>

                <div class="form table-responsive white-content">
                    <form id="">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group has-label">
                                    <label>
                                        <div class="col-md-3 col-sm-4">
                                            <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <div class="form-group has-label">
                                    <div class="form-group has-label" id="respo_msg" style="display: none;color:green">
                                        Successfully updated profile
                                    </div>
                                    <div class="form-group has-label" id="respo_failmsg" style="display: none;color:red">
                                        Failed to update profile
                                    </div>
                                    <div class="form-group has-label" id="f_Name">
                                        <em class="error" id="required_entity_fn" style="color: red;">*</em>
                                        <label>
                                            First Name :
                                        </label>
                                        <input class="form-control" type="text" id="firstname" name="firstname" required="">
                                    </div>

                                    <div class="form-group has-label" id="L_Name">
                                        <em class="error" id="required_entity_ln" style="color: red;">*</em>
                                        <label>
                                            Last Name :
                                        </label>
                                        <input class="form-control" type="text" id="lastname" name="lastname" required="">
                                    </div>

                                    <div class="form-group has-label">
                                        <em class="error" id="required_entity_email" style="color: red;">*</em>
                                        <label>
                                            User Email :
                                        </label>
                                        <input class="form-control" id="user_email" name="user_email" type="text" readonly="true">
                                    </div>
                                    <div class="form-group has-label">
                                        <em class="error" id="required_entity_companyname" style="color: red;">*</em>
                                        <label>
                                            User Role :
                                        </label>
                                        <input class="form-control" id="dashbaord_user_role" name="dashbaord_user_role" type="text" readonly="true">
                                    </div>

                                    <!--                                        <div class="form-group has-label">
                                                                                    <em class="error" id="required_entity_phone" style="color: red;">*</em>
                                                                                    <label>
                                                                                        Phone No. :
                                                                                    </label>
                                                                                    <input class="form-control" type="text" id="phone_no" name="phone_no">
                                                                                </div>-->
                                    <div class="form-group has-label">
                                        <em class="error" id="required_entity_time" style="color: red;">*</em>
                                        <label>
                                            Time Zone :
                                        </label>
                                        <!--<select class="form-control dropdown-submenu" data-size="5" id="timeZone">-->
                                        <select class="form-control valid" data-size="3" id="timeZone" style="height: 30px; padding: 0px 0px 0px 8px;" aria-invalid="false">

                                        </select>
                                    </div>
                                    <!--                                        <div class="form-group has-label">
                                                                                    <span style="float:right;margin-top:238px;">Version 8.9.0</span>
                                                                                </div>-->

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div id="reset-pass-container" class="rightSidenav" data-class="sm-3">
                <div class="card-title border-bottom">
                    <h4>Reset Password</h4>
                    <a href="javascript:void(0)" class="closebtn profilepicture-container-close border-0" data-target="reset-pass-container">&times;</a>
                </div>
                <div class="btnGroup">
                    <div class="icon-circle ">
                        <div class="toolTip ">
                            <i class="tim-icons icon-check-2" onclick="resetPass()"></i>
                            <span class="tooltiptext " id="">Save</span>
                        </div>
                    </div>
                </div>

                <div class="form table-responsive white-content">
                    <form id="">
                        <div class="card">
                            <div class="card-body">

                                <div class="form-group has-label">
                                    <div class="form-group has-label" id="f_Name">
                                        <em class="error" id="required_entity_fn" style="color: red;">*</em>
                                        <label>
                                            Old Password:
                                        </label>
                                        <input class="form-control" type="text" id="oldpasswordval" name="oldpasswordval" required="">
                                    </div>

                                    <div class="form-group has-label" id="L_Name">
                                        <em class="error" id="required_entity_ln" style="color: red;">*</em>
                                        <label>
                                            New Password:
                                        </label>
                                        <input class="form-control" type="text" id="passwordval" name="passwordval" required="">
                                    </div>

                                    <div class="form-group has-label">
                                        <em class="error" id="required_entity_email" style="color: red;">*</em>
                                        <label>
                                            Confirm Password:
                                        </label>
                                        <input class="form-control" id="repassword" name="repassword" type="text" readonly="true">
                                    </div>
                                    <div class="form-group has-label">
                                        <span class="error_msg" id="required_entity_companyname" style="color: red;"></span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- End Navbar -->
            <script>
                // var token = '<?php echo $_SESSION['token']; ?>';
                // document.cookie = "usertoken=" + token + "; path=/; secure=1; httponly=1";
            </script>
            <style>
                #MUMMessage {
                    font-weight: bold;
                    margin-left: 35%;
                    color: red;
                    font-size: 14px
                }
            </style>