<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'sites';
$_SESSION['currentwindow'] = 'sites';

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'sites_html.php';
require_once '../layout/footer.php';
?>
<script type="text/javascript" src="../js/customer/sites.js">
</script>
<script>
    bussinessLevel = '<?php echo $_SESSION['user']['busslevel']; ?>';
    customerType = '<?php echo $_SESSION['user']['customerType']; ?>';
    sel_CustomerEid = '<?php echo $_SESSION['user']['cId']; ?>';
</script>
<script src="../js/customer/form-validation.js"></script>
<script type="text/javascript">
    $('#pageName').html('Sites');
</script>