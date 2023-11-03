<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
$_SESSION['windowtype'] = 'gdprcomp';
$_SESSION['currentwindow'] = 'gdprcomp';

$res = checkModulePrivilege('gdprcomp', 2);
if (!$res) {
    echo 'Permission denied';
    exit();
}

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
require_once 'gdpr_html.php';
require_once '../layout/footer.php';
?>
<style type="text/css">
    .buttonGrey {
        background: rgba(0, 0, 0, 0.20);
    }
</style>
<script type="text/javascript">
    $('#pageName').html('GDPR');
</script>
<!--<script src="../js/rightmenu/rightMenu.js"></script>-->
<script src="../js/admin/gdpr.js"></script>