<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'weights';
$_SESSION['currentwindow'] = 'weights';
require_once("../include/common_functions.php");


// $res = checkModulePrivilege('weights', 2);
// if(!$res) {
//     echo "Permission Denied";
//     exit();
// }
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'visualWeight_html.php';
require_once '../layout/rightmenu.php';
require_once 'weightsFunction.php';
require_once '../layout/footer.php';

?>
<script type="text/javascript" src="../js/rightmenu/rightMenu.js"></script>
<!-- <script type="text/javascript" src="../js/rightmenu/rightMenu.js"></script> -->
<script src="../js/weights/weights.js"></script>

<!-- <script type="text/javascript" src="../js/customer/form-validation.js"></script> -->
<!-- <script type="text/javascript" src="../js/softwareupdate/softwareclient.js"></script> -->
<script type="text/javascript">
    $('#pageName').html('Visualisation Weights');
</script>

<style>
    div.bottom {
        bottom: 39px !important;
    }

    #softwareupdategrid_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }
</style>