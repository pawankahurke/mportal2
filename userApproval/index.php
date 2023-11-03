<?php
/*   error_reporting(-1);
 ini_set('display_errors', 'On'); */

require_once("../include/common_functions.php");

$_SESSION['windowtype'] = 'userapproval';
$_SESSION['currentwindow'] = 'userapproval';

$res = checkModulePrivilege('userapproval', 2);


if (!$res) {
    echo 'Permission denied';
    exit();
}

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'user-action_html.php';
require_once '../layout/footer.php';
?>
<script type="text/javascript" src="../js/admin/user-action.js"></script>

<script type="text/javascript">
    $('#pageName').html('User Approvals');
</script>
<style>
    div.bottom {
        bottom: 39px !important;
    }

    #user_datatable_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }

    .btn-lite {
        font-size: 12px !important;
        font-weight: normal !important;
    }
</style>