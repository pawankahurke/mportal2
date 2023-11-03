<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'Advance Group';
$_SESSION['currentwindow'] = 'Advance Group';
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'advgrp_html.php';
require_once '../layout/rightmenu.php';
require_once '../layout/footer.php';
require_once '../include/common_functions.php';


?>
<script type="text/javascript" src="../js/rightmenu/rightMenu.js"></script>
<script type="text/javascript" src="../js/customer/advgrp.js"></script>
<script type="text/javascript">
    $('#pageName').html('Advance Group');
</script>

<style>
    div.bottom {
        bottom: 39px !important;
    }

    #advncdgroupList_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }
</style>