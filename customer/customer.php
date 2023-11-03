<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'customer';
$_SESSION['currentwindow'] = 'customer';

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'customer_html.php';
require_once '../layout/footer.php';
?>
<script>
    bussinessLevel = '<?php echo $_SESSION['user']['busslevel']; ?>';
    customerType = '<?php echo $_SESSION['user']['customerType']; ?>';
</script>
<script src="../js/customer/form-validation.js"></script>
<script src="../js/customer/customer.js"></script>
<script type="text/javascript">
    $('#pageName').html('Customer');
</script>