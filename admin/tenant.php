<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'tenant';
$_SESSION['currentwindow'] = 'tenant';

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'tenant_html.php';
require_once '../layout/footer.php';
global $configServer;
?>
<script>
    bussinessLevel = '<?php echo $_SESSION['user']['busslevel']; ?>';
    customerType = '<?php echo $_SESSION['user']['customerType']; ?>';
    sel_CustomerEid = '<?php echo $_SESSION['user']['cId']; ?>';
</script>

<style>
    .error {
        color: red;
        float: right;
        margin-right: 2%;
    }
</style>

<script type="text/javascript" src="../js/common_ajax.js"></script>
<script src="../js/customer/form-validation.js"></script>
<script type="text/javascript">
    $('#pageName').html('Tenant');
    var bburl = '<?php echo $configServer; ?>';
</script>
<script src="../js/admin/entitylist.js"></script>