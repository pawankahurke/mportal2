<?php

$_SESSION['internalcurl'] = true;
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
require_once '../include/common_functions.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'auditLog';
$_SESSION['currentwindow'] = 'auditLog';

$res = checkModulePrivilege('useraudit', 2);
if (!$res) {
    echo 'Permission denied';
    exit();
}

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
require_once '../auditlog/auditview_html.php';
require_once '../layout/footer.php';
require_once '../auditlog/auditLogfunction.php';
?>


<script type="text/javascript">
    $('#pageName').html('User Activity Audit');
</script>
<script src="../js/admin/auditlog.js?_v0.1"></script>
<style>
    div.bottom {
        bottom: 39px !important;
    }

    #roleGrid_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }
</style>