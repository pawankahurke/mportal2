<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'autoupdate';
$_SESSION['currentwindow'] = 'autoupdate';
require_once("../include/common_functions.php");


$res = checkModulePrivilege('autoupdate', 2);
if (!$res) {
    echo "Permission Denied";
    exit();
}
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'update_html.php';
require_once '../layout/rightmenu.php';

require_once '../layout/footer.php';

?>
<script type="text/javascript" src="../js/rightmenu/rightMenu.js"></script>
<script type="text/javascript" src="../js/customer/form-validation.js"></script>
<script type="text/javascript" src="../js/softwareupdate/softwareclient.js"></script>
<script type="text/javascript">
    $('#pageName').html('Nanoheal Client Update');
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