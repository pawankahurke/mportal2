<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'siem';
$_SESSION['currentwindow'] = 'siem';
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'siem_html.php';
require_once '../layout/footer.php';


?>
<script type="text/javascript">
    $('#pageName').html('Siem Configuration');
</script>
<script type="text/javascript" src="../js/rightmenu/rightMenu.js"></script>
<script type="text/javascript" src="../js/customer/siemConfig.js"></script>