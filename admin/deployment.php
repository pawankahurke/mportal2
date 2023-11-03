<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
$res = checkModulePrivilege('networkdeployment', 2);
if (!$res) {
    nhUser::redirectIfNotAuth();
    exit();
}
$_SESSION['windowtype'] = 'networkdeployment';
$_SESSION['currentwindow'] = 'networkdeployment';

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
?>

<style>
    .hide_hover {
        font-size: 14px;
        color: #000;
        padding: 2px 8px 5px 5px;
    }

    .hide_hover:hover {
        color: #000;
        cursor: pointer;
        padding: 2px 8px 5px 5px;
    }
</style>

<?php require_once 'deployment_html.php'; ?>

<?php require_once '../layout/footer.php';
require_once '../communication/communication.php'; ?>

<script src="../js/rightmenu/rightMenu.js"></script>
<script src="../js/customer/deployment.js"></script>

<script type="text/javascript">
    $('#pageName').html('Deployment');
</script>

<style>
    div.bottom {
        bottom: 39px !important;
    }

    #dtRightList_info {
        margin-left: 20%;
        color: #000;
        font-size: 10px;
    }

    /*#dtLeftList_info{margin-left: 20%;color: #000;font-size: 10px;}*/
</style>