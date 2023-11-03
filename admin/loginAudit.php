<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
$_SESSION['windowtype'] = 'loginaudit';
$_SESSION['currentwindow'] = 'loginaudit';

$res = checkModulePrivilege('loginaudit', 2);
if (!$res) {
    echo 'Permission denied';
    exit();
}

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'loginAudit_html.php';
require_once '../layout/footer.php';
?>
<script type="text/javascript" src="../js/admin/loginAudit.js"></script>
<script type="text/javascript">
    $('#pageName').html('Login Information');
</script>
<style>
    div.bottom {
        bottom: 39px !important;
    }

    #login_datatable_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }
</style>