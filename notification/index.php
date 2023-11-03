<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'notification';
$_SESSION['currentwindow'] = 'notification';

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
require_once 'notification_html.php';
require_once '../layout/footer.php';
include_once '../communication/communication.php';

// Check authorization
nhUser::redirectIfNotAuth();

?>
<!--<style>
    .highlight {
        background: rgb(71, 178, 227);
    }
</style>-->
<script type="text/javascript">
    $('#pageName').html('Notifications');
</script>
<?php
$res = nhRole::checkModulePrivilege('notification');
if ($res) {
?>
    <script src="../js/notification/notify.js"></script>
<?php
}
?>
<script src="../js/rightmenu/rightMenu.js"></script>
<style>
    /* #notifyDtl_filter label {
        position: relative;
        right: 40px;
        top: -47px;
    } */

    #RegisterValidation .card label {
        width: 100%;
    }

    #absoLoader {
        width: 98%;
        height: 95%;
        position: absolute;
        z-index: 10;
        background-color: #ffffff;
        top: 10px;
        opacity: 0.5;
    }

    #absoLoader img {
        margin-top: 20%;
        margin-left: 47%;
    }

    div.bottom {
        bottom: 52px !important;
    }

    #notifyeventDtl_wrapper .bottom {
        bottom: -16px !important;
    }

    #notifyeventAnalyserDtl_wrapper .bottom {
        bottom: -16px !important;
    }

    #notifyDtl_info {
        margin-left: 18%;
        color: #000;
        font-size: 10px;
    }

    .notif-padding {
        padding: 5px;
        font-size: 12px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }

    .notif-padding.active {
        background-color: #eee;
    }

    .accordion .card .card-header:not(.collapsed) h5 .rotate-icon {
        -webkit-transform: rotate(180deg);
        transform: rotate(180deg);
    }

    .checkboxMargin {
        margin-left: 20px;
    }

    .priorityExpand {
        padding-bottom: 5px;
    }

    .priorityExpand>a>h5,
    .statusExpand>a>h5 {
        margin-top: 15px;
    }

    .priorityExpand>a>h5>b,
    .statusExpand>a>h5>b {
        margin-top: 7px;
    }
</style>
