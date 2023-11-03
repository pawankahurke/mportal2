<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

$isLoggedin = true;
require_once $absDocRoot . 'lib/l-db.php';
nhUser::redirectIfNotAuth();
$name = 'usertoken';
$value = $_SESSION['token'];
$expirationTime = 0; // Session cookie.
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
    <script>
        var domUrl = '<?php echo $base_url; ?>';
    </script>
    <!-- CSS Files -->
    <!--  <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,600,700,800" rel="stylesheet" />-->
    <link href="../assets/css/family=Montserrat.css" rel="stylesheet">
    <link href="../assets/css/all.css" rel="stylesheet">
    <link href="../assets/css/icons.css" rel="stylesheet" />
    <link href="../assets/css/styles.css" rel="stylesheet" />
    <link href="../assets/css/common.css" rel="stylesheet" />
    <link href="../assets/css/bootstrap-colorpicker.css" rel="stylesheet">
    <script src="../assets/js/core/jquery.min.js"></script>
    <!-- <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script> -->
    <script type="text/javascript" src="./../assets/js/moment.min.js"></script>
    <script type="text/javascript" src="./../assets/js/daterangepicker.min.js"></script>
    <script type="text/javascript" src="../js/customer/users.js"></script>
    <link rel="stylesheet" type="text/css" href="./../assets/css/daterangepicker.css" />
    <!-- <script type="text/javascript" src="../js/customer/device.js"></script>
 -->
</head>

<body class="sidebar-mini">
    <?php
    if ($isLoggedin) {
    ?>

        <div id="absoBodyLoader" style="display:none">

        </div>
    <?php } ?>
    <div id="absoFeed"></div>
    <div id="rsc-blur-loader" style="z-index: 100;" class="hide"></div>
    <div id="fullWrapper" class="wrapper" <?php if ($isLoggedin) { ?>style="display:block;height:0px" <?php } ?>>
        <div class="navbar-minimize-fixed">
            <button class="minimize-sidebar btn btn-link btn-just-icon">
                <i class="tim-icons icon-align-center visible-on-sidebar-regular text-muted"></i>
                <i class="tim-icons icon-bullet-list-67 visible-on-sidebar-mini text-muted"></i>
            </button>
        </div>

        <?php
        require_once 'rolesValues.php';
        require_once 'sidebar.php';
        $trialEnabled = isset($_SESSION["user"]["trialEnabled"]) ? $_SESSION["user"]["trialEnabled"] : "";
        $trialEndDate = isset($_SESSION["user"]["trialEndDate"]) ? $_SESSION["user"]["trialEndDate"] : "";
        $userID = $_SESSION['user']['userid'];
        $firstName = $_SESSION['user']['fname'];
        $adminMail = $_SESSION['user']['adminEmail'];
        $cType = $_SESSION['user']['customerType'];
        $timeZone = $_SESSION['user']['usertimezone'];

        $windowType = $_SESSION['windowtype'];
        $today = time();
        if ($today > $trialEndDate) {
            $remaingtrialDays = 0;
        } else if ($today < $trialEndDate) {
            $datediff = $trialEndDate - $today;
            $remaingtrialDays = ceil($datediff / (60 * 60 * 24));
        } else {
            $remaingtrialDays = 0;
        }
        ?>

        <div id="mainPanelContent" class="main-panel" <?php if ($isLoggedin) { ?>style="display:none" <?php } ?>>

            <input type="hidden" id="url_id" value="">
            <input type="hidden" id="hiddenUserID" value="<?php echo $userID; ?> ">
            <input type="hidden" id="profileFname" value="<?php echo $firstName; ?> ">
            <input type="hidden" id="profileadminMail" value="<?php echo $adminMail; ?> ">
            <input type="hidden" id="profilecType" value="<?php echo $cType; ?> ">
            <input type="hidden" id="profiletimeZone" value="<?php echo $timeZone; ?> ">

            <!-- Navbar -->
            <nav class="navbar bg-white navbar-expand-lg navbar-absolute" style="background: white !important; height: 52px;">
                <div style="height: 100%;" class="container-fluid">
                    <div style="height: 100%;" class="navbar-wrapper w-100">
                        <div class="navbar-toggle d-inline">
                            <button type="button" class="navbar-toggler">
                                <span class="navbar-toggler-bar bar1"></span>
                                <span class="navbar-toggler-bar bar2"></span>
                                <span class="navbar-toggler-bar bar3"></span>
                            </button>
                        </div>
                        <span class="navbar-brand font-weight-700"><span id="pageName"></span></span>
                        <h5 class="pt-3 w-25" id="siteFilter" style="display:none;align-items: center;">
                            <div style="min-width: 50px;" class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3"><img class="cursorPointer" src="../assets/img/newsite.png" style="width: 30px;border-radius: 0;" /></div>
                            <span style="width: 150px;" class="site pt-1" data-qa="selected-site-name" title=""></span>
                        </h5>
                        <?php
                        if ($windowType == 'home') {
                        ?>
                            <input type="hidden" id="CubeDateString" value="">
                            <input type="hidden" id="InitialLevel" value="">
                            <?php
                            if (!isset($_SESSION['user']['loggedUType']) || $_SESSION['user']['loggedUType'] != 'Other') {
                            ?>
                                <div id="reportrange">
                                    <i class="fa fa-calendar"></i>&nbsp;
                                    <span></span> <i class="fa fa-caret-down"></i>
                                </div>
                            <?php
                            } ?>
                        <?php } ?>
                        <div id="headerFilters" class="w-100">
                            <div class="bullDropdown leftDropdown pt-2">
                                <!-- <h5>Selection: <span class="site" title="<?php echo url::toText($_SESSION['searchValue']); ?>"><?php echo isset($nameStr) ? $nameStr : ""; ?></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3" onclick="showRightPane()">Change?</span>)</h5> -->
                                <!-- <h5 class = "pt-1"> <span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3"><img class = "mr-1 cursorPointer" src="../assets/img/structureh.png"></span> <span class="site" title=""></span></h5> -->

                            </div>


                            <div class="bullDropdown  ml-auto d-flex justify-content-end w-100">
                                <div class="mr-2">
                                    <!-- <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-cog" style="font-size:20px;color:red"></i>
                                    </button> -->
                                    <!-- <i class="fa fa-filter cursorPointer mr-2 pt-1"  style="font-size:20px;color:red"></i> -->
                                    <!-- <i class="fa fa-cog cursorPointer mr-2 " style="font-size:20px;color:red" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>  -->


                                    <div class="dropdown" id="forNotification" style="display:none">
                                        <img class="mr-2 cursorPointer" src="../assets/img/newfunnel.png" onclick="showNotifFilters()" style="width: 20px;margin-top: 6px;height: 20px;">

                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <?php if ($troubleshooterMode == 'On') { ?>
                                                <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('notifyaction', 2); ?>" id="notifyfix" onclick="notifyFix()" data-qa="notification-Action-btn">Action</a>
                                            <?php } ?>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('notifydetails', 2); ?>" id="getdetails" onclick="getDetails()" data-qa="notification-getdetails-btn">Details</a>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('notifyexport', 2); ?>" onclick="export_notification()" data-qa="notification-ExportToExcel-btn">Export to Excel</a>
                                            <!-- <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('updateSolution', 2); ?>" id="update-soln" onclick="getProfiles()">Update Solution</a>-->
                                            <!--<a class="dropdown-item dropHandy" href="#" id="export_allnotification">Export all Notification</a>-->
                                        </div>
                                    </div>

                                    <div class="dropdown" id="forAlert" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a id="add-alert" class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('addalert', 2); ?>">Create Notification</a>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('alertstatus', 2); ?>" id="enable-alert">Enable</a>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('alertstatus', 2); ?>" id="disable-alert">Disable</a>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('deletealert', 2); ?>" id="delete-alert">Delete Notification</a>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('alertlinksite', 2); ?>" id="link-alert">Link to Site</a>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('alertexport', 2); ?>" id="export-alert">Export Notification</a>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('alertimport', 2); ?>" id="import-alert">Import Notification</a>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('updateSolution', 2); ?>" id="updatenew-soln" onclick="getProfiles()">Update Solution</a>
                                        </div>
                                    </div>

                                    <div class="dropdown" id="forComplaince" style="display:none">
                                        <img class="mr-2 cursorPointer" src="../assets/img/newfunnel.png" onclick="showComplianceFilters()" style="width: 20px;margin-top: 6px;height: 20px;">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('notifyexport', 2); ?>" onclick="export_notification()">Export to Excel</a>
                                        </div>
                                    </div>

                                    <div class="dropdown" id="forCensus" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <?php if ($_SESSION['user']['licenseuser'] == 1) { ?>
                                                <!-- <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addsite', 2); ?>" data-bs-target="site-add-container" onclick="addSitePopup();">Add New Site</a> -->
                                            <?php } ?>
                                            <a class="dropdown-item rightslide-container-hand dropHandy sites <?php echo setRoleForAnchorTag('siteexport', 2); ?>" id="exportAllSites">Export</a>
                                            <!-- <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('notifyexport', 2); ?>" onclick="export_notification()">Export to Excel</a> -->
                                        </div>
                                    </div>

                                    <div class="dropdown" id="forProfiles" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a data-qa="h1-addProfile" class="dropdown-item <?php echo setRoleForAnchorTag('addprofile', 2); ?>" id="addProfile" onclick="addNewProfile();">Add Profile</a>
                                            <a data-qa="h1-editprofile" class="dropdown-item <?php echo setRoleForAnchorTag('editprofile', 2); ?>" id="editprofile" onclick="editProfile();">Edit Profile</a>
                                            <a data-qa="h1-viewProfile" class="dropdown-item <?php echo setRoleForAnchorTag('viewprofile', 2); ?>" id="viewProfile" onclick="viewProfiles();">View Profile</a>
                                            <a data-qa="h1-duplicateProfile" class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('duplicateprofile', 2); ?>" id="duplicateProfile" onclick="duplicateProfile();">Duplicate Profile</a>
                                            <a data-qa="h1-attachProfile" class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('attachprofile', 2); ?>" id="attachProfile" onclick="attachProfile();">Attach Profile</a>
                                            <a data-qa="h1-deleteProfile" class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('deleteprofile', 2); ?>" id="deleteProfile" onclick="deleteProfile();">Delete Profile</a>
                                        </div>
                                    </div>

                                    <div class="dropdown" data-qa="forSoftwareDistribution" id="forSoftwareDistribution" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a data-qa="addNewSoftware" class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('addsoftwaredistribution', 2); ?>" onclick="addPackageEvent($(this))" data-bs-target="rsc-add-container">Add a new software</a>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('editsoftwaredistribution', 2); ?>" id="editPopup" data-bs-target="rsc-edit-container">Edit software</a>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('viewsoftwaredistribution', 2); ?>" id="swdDetail" href="#">View Software Details</a>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('configureswdpackage', 2); ?>" id="configurePackage">Configure Package</a>
                                            <!--<a class="dropdown-item dropHandy <?php ?>" href="#" id="distexecPack">Distribute</a>-->
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('deletesoftwaredistribution', 2); ?>" id="deletePackage" onclick="confirmDelete();">Delete</a>
                                            <!--<a class="dropdown-item dropHandy" href="../softdist/index_audit.php">View Status</a>-->
                                        </div>
                                    </div>

                                    <div class="dropdown" id="forUsers" style="display:none">
                                        <img data-qa="settingsUsers" class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <!--<a id="userdetails" class="dropdown-item rightslide-container-hand dropHandy"  onclick="user_datatable('all');">All User</a>-->
                                            <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('adduser', 2); ?>" data-bs-target="add-new-user" onclick="changeUserLevels();" data-qa="Add-New-User">Add New User</a>
                                            <!--<a id="edit_user_option" class="dropdown-item rightslide-container-hand dropHandy"  onclick="get_UserDetails();">Modify User</a>-->
                                            <!--<a id="edit_user_option_trigger" class="dropdown-item rightslide-container-hand dropHandy hideElement " data-bs-target="edit-user">Modify User trigger</a>-->
                                            <a id="delete_user_option" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('deleteuser', 2); ?>" data-qa="Delete-User">Delete User</a>
                                            <!--                                    <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('logininformation', 2); ?>" onclick="goTOLogin();">Log-In Details</a>-->
                                            <a id="mail_resend_option" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('resendmail', 2); ?>">Resend Mail</a>
                                            <a id="resetPass_resend_option" class="dropdown-item rightslide-container-hand dropHandy  <?php echo setRoleForAnchorTag('resetpassword', 2); ?>">Reset Password</a>
                                            <a id="export_user" class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('userexport', 2); ?>">Export To Excel</a>
                                            <!--<a id="back" class="dropdown-item rightslide-container-hand dropHandy"  onclick="user_datatable()">Back</a>-->
                                        </div>

                                    </div>

                                    <div class="dropdown" id="forAccessRight" style="display:none">
                                        <img data-qa="settingsRole" class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a data-qa="addRole" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addrole', 2); ?>" id="addRole" data-bs-target="add-role" onclick=" addNewRole();">Add Role</a>
                                            <a data-qa="deleteRole" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('deleterole', 2); ?>" id="deleteRole" onclick="selectConfirm('delete');">Delete Role</a>
                                        </div>
                                    </div>

                                    <div class="dropdown" id="forSite" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addsite', 2); ?>" data-bs-target="site-add-container" onclick="showAddSite()" data-qa="Add-New-Site">Add New Site</a>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('emaildistribution', 2); ?>" id="site-emailDistribution">Email Distribution</a>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('licensedetails', 2); ?>" id="site-license">License Details</a>
                                        </div>
                                    </div>

                                    <div class="dropdown" id="forUserActivity" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a id="export_audit" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('userauditexport', 2); ?>" data-bs-target="auditlog-range">Export To Excel</a>
                                        </div>
                                    </div>

                                    <div class="dropdown" id="forDartAudit" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" id="click_first">
                                            <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('dartdetailview', 2); ?>" id="detailViewAudit" data-bs-target="rsc-add-container">Details View</a>
                                            <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('dartexport', 2); ?>" id="exportDartAudit" data-bs-target="dartaudit-range">Export To Excel</a>
                                        </div>
                                    </div>

                                    <div class="dropdown" id="forLoginInfo" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" id="click_first">
                                            <a id="export_login" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('loginexport', 2); ?>" onclick="exportdetails()" data-bs-target="login-range">Export Historical Details</a>
                                        </div>
                                    </div>

                                    <div class="dropdown " id="forPatchM" style="display:none">
                                        <img class="mr-2 cursorPointer" src="../assets/img/newfunnel.png" onclick="checkFilter()" style="width: 20px;margin-top: 6px;height: 20px;">
                                        <img class="mr-1 cursorPointer mt-1 btn-setting-patch-management dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <input type="hidden" id="hidden_approvedgrpid">
                                        <input type="hidden" id="selected_appr_patch">
                                        <input type="hidden" id="hiddenStatusValue" style="display:none">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item kbs-container-hand <?php echo setRoleForAnchorTag('mumAdd', 2); ?>" style="display: none;" data-bs-target="kbs-add-container">Adding</a>
                                            <a class="dropdown-item checkUIbtn-approve <?php echo setRoleForAnchorTag('mumAction', 2); ?>" onclick="actionSelection('approve',type)" style="display:none" id="showApprovePatch">Approve Patch</a>
                                            <a class="dropdown-item checkUIbtn-decline declinepatch <?php echo setRoleForAnchorTag('declinePatch', 2); ?>" onclick="actionSelection('decline',type)" style="display:none" id="showDeclinePatch">Decline Patch</a>
                                            <a class="dropdown-item removeselectedpatch checkUIbtn-remove <?php echo setRoleForAnchorTag('removePatch', 2); ?>" style="display:none" onclick="removePatch()" id="showRemovePatch">Remove Patch</a>
                                            <a class="dropdown-item exportPatchDetails <?php echo setRoleForAnchorTag('export', 2); ?>" onclick="exportData()">Export</a>
                                            <a class="dropdown-item exportPatchData <?php echo setRoleForAnchorTag('exportpatchdetails', 2); ?>" style="display: none;" onclick="exportPatchData()">Export Patch Details</a>
                                            <a class="dropdown-item" style="display: none;" id="backbtn">Back</a>
                                        </div>
                                    </div>

                                    <div class="dropdown" id="forNanohealClient" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                            <a class="dropdown-item rightslide-container-hand dropHandy main <?php echo setRoleForAnchorTag('softwaredetails', 2); ?>" onclick="selectConfirm('version_detail')">Details</a>
                                            <a class="dropdown-item rightslide-container-hand dropHandy main <?php echo setRoleForAnchorTag('softwareversion', 2); ?>" onclick="getVersionTable();">Versions</a>
                                            <a class="dropdown-item rightslide-container-hand dropHandy main <?php echo setRoleForAnchorTag('updatesoftwareversion', 2); ?>" onclick="getOsVersionList()" id="updateversion" data-bs-target="update-version">Update Version</a>
                                            <a class="dropdown-item rightslide-container-hand dropHandy main <?php echo setRoleForAnchorTag('softwaredetailsexport', 2); ?>" onclick="versionlistexport()" id="detailexport">Details Export</a>
                                            <a class="dropdown-item dropHandy main" id="open-upload-core-db-wrap <?php echo setRoleForAnchorTag('coredbupload', 2); ?>" onclick="showCoreDbnUploadCntnr();">Core DB Upload</a>

                                            <a class="dropdown-item rightslide-container-hand dropHandy version <?php echo setRoleForAnchorTag('addversion', 2); ?>" data-bs-target="add-version" onclick="enableAddFields()">Add Version</a>
                                            <a class="dropdown-item rightslide-container-hand dropHandy version <?php echo setRoleForAnchorTag('copyversion', 2); ?>" data-bs-target="copy-version" onclick="get_copyversiondata()">Copy Version</a>
                                            <a class="dropdown-item rightslide-container-hand dropHandy version <?php echo setRoleForAnchorTag('deleteversion', 2); ?>" onclick="deleteVersion()">Delete Version</a>
                                            <a class="dropdown-item rightslide-container-hand dropHandy version <?php echo setRoleForAnchorTag('backupdate', 2); ?>" onclick="gotoMain();">Back</a>
                                        </div>
                                    </div>

                                    <div class="dropdown" id="forGroups" data-qa="groupSetting_menu" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;" onClick="callDropDown()" />
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <!-- <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addAdvgrp', 2); ?>" data-bs-target="advgrp-add-container" id="addNewAdvGroup">Add Dynamic Group</a> -->
                                            <a data-qa="addNewGroup" id="newGroupAddition" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addgroup', 2); ?>" onclick="getrequiredList();" data-bs-target="grp-addmod-container">Add New Group</a>
                                            <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('viewAdvgrp', 2); ?>" id="view_grpDetails" onclick="ViewAdvGroup()">View Group Details</a>
                                            <a class="dropdown-item rightslide-container-hand dropHandy groups <?php echo setRoleForAnchorTag('exportgroupdetails', 2); ?>" onclick="selectConfirm('export_group_listmain');" id="export_group_listmain">Export Group List</a>
                                            <a class="dropdown-item rightslide-container-hand dropHandy groups <?php echo setRoleForAnchorTag('exportgroupdetails', 2); ?>" onclick="selectConfirm('export_group_list');" id="export_group_list">Export Group Details</a>
                                            <a data-qa="deleteGroup" class="dropdown-item rightslide-container-hand dropHandy<?php echo setRoleForAnchorTag('deletegroup', 2); ?>" id="delete_group" onclick="deleteGroup()">Delete Group</a>



                                        </div>
                                    </div>

                                    <div class="dropdown" id="forSwDis" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('distributesoftwaredistribution', 2); ?>" id="distexecPack">Deploy & Install</a>
                                            <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('exportsoftdist', 2); ?>" onclick="exportaudit()">Export Status</a>
                                        </div>
                                    </div>

                                    <div class="dropdown" id="forPatchMC" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item selectedli1 <?php echo setRoleForAnchorTag('ConfigUpdate', 2); ?>" onclick="showConfigUpdate()" data-bs-target="config-update-container">Configure Update</a>
                                            <a class="dropdown-item selectedli2 <?php echo setRoleForAnchorTag('UpdateMethod', 2); ?>" onclick="showUpdateMethod()" data-bs-target="update-method-container">Update Method</a>
                                            <!--<a class="dropdown-item selectedli3 <?php echo setRoleForAnchorTag('MachineSetting', 2); ?>" onclick="showMachineSettings()" data-bs-target="machine-setting-container" href="#">Machine Settings</a>-->
                                            <!--<a class="dropdown-item selectedli4 <?php ?>" onclick="showconfigUpload()" data-bs-target="config-upload-container" href="#">Configure Upload</a>-->
                                        </div>

                                    </div>

                                    <div class="dropdown" id="forTW" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item rightslide-container-hand <?php echo setRoleForAnchorTag('ticketingconf', 2); ?>" data-bs-target="ticketing-configuration" data-qa="configurationForTicketing" onclick="crmConfigure();">Configure</a>
                                            <!-- <a class="dropdown-item <?php echo setRoleForAnchorTag('actiondetails', 2); ?>" onclick="actionDetails();">Action Details</a> -->
                                            <!-- <a class="dropdown-item <?php echo setRoleForAnchorTag('ticketingexport', 2); ?>" onclick="exportTicketingDetails()" id="ticketingExport">Export</a> -->
                                        </div>
                                    </div>

                                    <div class="dropdown" id="forNew" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a class="dropdown-item <?php echo setRoleForAnchorTag('addgroup', 2); ?>" data-bs-target="addNew" onClick="showAddnew()">Add</a>
                                            <a class="dropdown-item <?php echo setRoleForAnchorTag('addgroup', 2); ?>" onClick="exportWeights()">Export</a>
                                        </div>
                                    </div>

                                    <div class="dropdown" id="forAutoAudit" style="display:none">
                                        <img class="mr-1 cursorPointer mt-1 dropdown-toggle" src="../assets/img/newSetting.png" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="width: 20px;height: 20px;">
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a id="export_audit" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('userauditexport', 2); ?>" data-bs-target="auditlog-range">Export To Excel</a>
                                        </div>
                                    </div>
                                </div>

                                <div id="notifyDtl_filter" class="dataTables_filter" style="display:none">
                                    <label class="mr-2 w-100">
                                        <input type="text" class="form-control bg-white form-control-sm pb-3 pt-3" placeholder="Search" value="" id="notifSearch" aria-controls="notifyDtl" style="color: black" />
                                        <button class="bg-white border-0 mr-1 showbtn cursorPointer right-0 ml-0" onclick="getSearchRecords()" style="display:block"><i class="tim-icons serachIcon icon-zoom-split"></i></button>
                                        <button style="display:none" class="bg-white border-0 mr-1 clearbtn cursorPointer right-0 ml-0" onclick="clearRecords()" onclick="document.getElementById('notifSearch').value = ''"><i class="tim-icons serachIcon icon-simple-remove"></i></button>
                                    </label>
                                </div>
                            </div>
                        </div>


                        <!-- <span class="navbar-brand ">
                            <p class = "" id="MUMMessage"></p>
                        </span> -->

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
                    <h4>Profile</h4>
                    <a href="javascript:void(0)" class="closebtn profilepicture-container-close border-0" data-bs-target="profilepicture-add-container">&times;</a>
                </div>
                <div class="btnGroup chkbtn">
                    <div class="icon-circle ">
                        <div class="toolTip ">
                            <i class="tim-icons icon-check-2"></i>
                            <span class="tooltiptext " id="">Save</span>
                        </div>
                    </div>
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
                <div class="card-title">
                    <h4>Reset Password</h4>
                    <a href="javascript:void(0)" class="closebtn reset-pass-container-close" data-bs-target="reset-pass-container">&times;</a>
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
                                        <input class="form-control" type="password" id="oldpasswordval" name="oldpasswordval" required="">
                                        <img class="eyeIcons" id="eyeIcon1" src="../assets/img/eye-icon.png" alt="logo" onclick="toggleMask('oldpasswordval', 'eyeIcon1')">
                                    </div>

                                    <div class="form-group has-label" id="L_Name">
                                        <em class="error" id="required_entity_ln" style="color: red;">*</em>
                                        <label>
                                            New Password:
                                        </label>
                                        <input class="form-control" type="password" id="passwordval" name="passwordval" required="">
                                        <img class="eyeIcons" id="eyeIcon2" src="../assets/img/eye-icon.png" alt="logo" onclick="toggleMask('passwordval', 'eyeIcon2')">

                                    </div>

                                    <div class="form-group has-label">
                                        <em class="error" id="required_entity_email" style="color: red;">*</em>
                                        <label>
                                            Confirm Password:
                                        </label>
                                        <input class="form-control" id="repassword" name="repassword" type="password">
                                        <img class="eyeIcons" id="eyeIcon3" src="../assets/img/eye-icon.png" alt="logo" onclick="toggleMask('repassword', 'eyeIcon3')">
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
            <script type="text/javascript">
                // var token = '<?php echo $_SESSION['token']; ?>';
                // document.cookie = "usertoken=" + token + "; path=/; secure=1; httponly=1";

                function toggleMask(id, imgId) {
                    //alert(id);
                    if ($("#" + id).attr("type") == "password") {
                        //alert(id);
                        $("#" + id).attr("type", "text");
                        $("#" + imgId).attr("src", "../assets/img/eye-icon-hide.png");
                    } else {
                        $("#" + id).attr("type", "password");
                        $("#" + imgId).attr("src", "../assets/img/eye-icon.png");
                    }
                }
            </script>
            <style>
                #MUMMessage {
                    font-weight: bold;
                    margin-left: 35%;
                    color: red;
                    font-size: 14px
                }

                .eyeIcons {
                    float: right;
                    margin-top: -18px;
                    margin-right: 12px;
                }

                #absoBodyLoader {
                    width: 100% !important;
                    height: 97%;
                    position: absolute;
                    z-index: 10;
                    background-color: #ffffff;
                    top: 10px;
                    opacity: 0.8;
                    padding-top: 21%;
                    padding-left: 46%;
                    right: 0% !important;
                }

                #notifyDtl_filter {
                    width: 15rem;
                }

                #notifSearch {
                    border: solid 1px rgba(34, 42, 66, 0.2) !important;
                }

                #IframeTop {
                    border: 0px none;
                    margin-left: -185px;
                    height: 859px;
                    margin-top: -533px;
                    width: 926px
                }

                #reportrange {
                    background: #fff;
                    cursor: pointer;
                    padding: 5px 10px;
                    border: 1px solid #ccc;
                    width: 212px;
                    position: fixed;
                    right: 15px;
                }

                .dropdown-menu {
                    margin-top: 8px;
                }
            </style>
