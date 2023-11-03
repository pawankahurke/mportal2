<?php

$_SESSION['windowtype'] = 'softwaredistribution';
$_SESSION['currentwindow'] = 'softwaredistribution';
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
require_once 'profile_html.php';
require_once '../layout/footer.php';
require_once '../communication/communication.php';
?>

<script type="text/javascript" src="../js/softdist/new.js"></script>
<script src="../js/rightmenu/rightMenu.js"></script>

<script>
    var usrEmail = "<?php echo url::toText($_SESSION['user']['adminEmail']); ?>";
</script>
<link rel="stylesheet" type="text/css" href="../assets/css/fineuploader.css">
<link rel="stylesheet" type="text/css" href="../assets/css/qquploader.css">
<script type="text/javascript" src="../assets/js/all.fine-uploader.js"></script>

<style>
    div.bottom {
        bottom: 39px !important;
    }

    #packageGrid_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }

    .side-nav-it p {
        color: #ffffff
    }

    .swd-ic-it {
        cursor: pointer;
        background-color: #f9f9f9;
        border: 1px solid #ececec;
        padding: 10px 0;
        margin-top: 10px;
        margin-right: 10px
    }

    .swd-ic-it.active,
    .swd-ic-it:hover {
        background-color: #e7e7e7;
        border: 1px solid #cccccc;
    }

    .swd-hide {
        display: none
    }

    .swd-ic-it p {
        font-weight: bold;
        text-align: center
    }

    .swd-ic-it-group .swd-ic-it p {
        font-size: 11px
    }

    .my-btn-group {
        margin-left: 0px;
        margin-bottom: 20px
    }

    .my-btn-group label {
        margin-right: 0px
    }



    .resume-download-wrap .bootstrap-switch-wrapper,
    .propagation-wrap .bootstrap-switch-wrapper {
        position: relative;
        left: 10px;
    }

    #delete-log-file-wrap .bootstrap-switch-wrapper {
        position: relative;
        left: 22px;
    }

    .my-container {
        border: 1px solid #ccc;
        padding-top: 14px;
        padding-bottom: 14px;
        margin-bottom: 14px;
    }
</style>
