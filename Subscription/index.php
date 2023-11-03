<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'census';
$_SESSION['currentwindow'] = 'census';
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'subscription_html.php';
require_once '../layout/rightmenu.php';
require_once '../layout/footer.php';


?>
<script type="text/javascript" src="../js/rightmenu/rightMenu.js"></script>
<script type="text/javascript" src="../js/subscription/sub.js"></script>
<script type="text/javascript">
    $('#pageName').html('Subscription');
</script>
<style>
    #emailDistributeLoader {
        margin-top: 2%;
        display: none;
    }

    div.bottom {
        bottom: 39px !important;
    }

    #detaild_grid_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }
</style>