<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
$_SESSION['windowtype'] = 'configuration';
$_SESSION['currentwindow'] = 'configuration';


require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'Configuration_html.php';
require_once '../layout/footer.php';
?>
<script type="text/javascript" src="../js/Configuration/Configuration.js"></script>
<script type="text/javascript">
    $('#pageName').html('Configuration');
</script>