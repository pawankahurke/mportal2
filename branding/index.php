<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
$_SESSION['windowtype'] = 'branding';
$_SESSION['currentwindow'] = 'branding';

$res = checkModulePrivilege('branding', 2);
if (!$res) {
    echo 'Permission denied';
    exit();
}

$Res = checkModulePrivilege('customisebranding', 2);
if (!$Res) {
?>
    <script>
        var CheckAcess;
        CheckAcess = 1; /*If permission is denied*/
    </script>
<?php
} else {
?>
    <script>
        CheckAcess = 0; /*If Permission is not denied*/
    </script>
<?php
}

require_once '../layout/header.php';
require_once 'branding_html.php';
require_once '../layout/footer.php';
?>

<script type="text/javascript" src="../js/admin/branding.js"></script>
<script type="text/javascript">
    $('#pageName').html('Branding');
</script>
<style>
    div.bottom {
        bottom: 39px !important;
    }

    #customizeConfGrid_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }
</style>