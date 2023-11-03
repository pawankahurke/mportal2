<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
$_SESSION['windowtype'] = 'dartaudit';
$_SESSION['currentwindow'] = 'dartaudit';

$res = checkModulePrivilege('dartaudit', 2);
if (!$res) {
    echo 'Permission denied';
    exit();
}

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'audit_html.php';
require_once '../layout/footer.php';
?>

<script type="text/javascript" src="../js/admin/audit.js"></script>
<script type="text/javascript">
    $('#pageName').html('Dart Audit');
</script>
<style>
    div.bottom {
        bottom: 39px !important;
    }

    #auditTable_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }
</style>