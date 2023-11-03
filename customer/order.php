<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'order';
$_SESSION['currentwindow'] = 'order';

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'order_html.php';
require_once '../layout/footer.php';
?>
<!-- <script type="text/javascript" src="../js/customer/order.js"></script> -->
<script src="../js/customer/form-validation.js"></script>
<script src="../js/home/subscrption.js"></script>
<script src="../js/home/subscrption_sbot.js"></script>
<script type="text/javascript">
    $('#pageName').html('Subscription');
</script>