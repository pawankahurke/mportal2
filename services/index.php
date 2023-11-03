<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
$_SESSION['windowtype'] = 'services';
$_SESSION['currentwindow'] = 'services';

$res = checkModulePrivilege('services', 2);
if (!$res) {
    echo 'Permission denied';
    exit();
}

require_once '../layout/header.php';
include_once 'selectProfile.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
require_once 'services_html.php';
require_once '../layout/footer.php';
?>

<script type="text/javascript">
    $('#pageName').html('Services');
</script>
<script src="../js/rightmenu/rightMenu.js"></script>
<script type="text/javascript" src="../js/services/services.js"></script>
<!-- <script type="text/javascript" src="../js/services/dartconfig.js"></script> -->
<!-- <script type="text/javascript" src="../js/services/iosScrip.js"></script> -->