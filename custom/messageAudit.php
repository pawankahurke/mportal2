<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
$_SESSION['windowtype'] = 'messageAudit';
$_SESSION['currentwindow'] = 'messageAudit';

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'messageAudit_html.php';
require_once '../layout/rightmenu.php';
require_once '../layout/footer.php';
?>
<script type="text/javascript" src="../js/custom/config_browser.js"></script>
<script type="text/javascript" src="../js/services/addConfig.js"></script>
<script type="text/javascript" src="../js/rightmenu/rightMenu.js"></script>
<script type="text/javascript" src="../config.js"></script>
<script type="text/javascript">
    $('#pageName').html('Message Configurations');
</script>
<style>
    div.bottom {
        bottom: 39px !important;
    }

    #msgconfig_grid_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }

    #AuditGrid_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }

    #auditGridDetail_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }
</style>