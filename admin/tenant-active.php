<?php
require_once("../include/common_functions.php");


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'tenantactivation';
$_SESSION['currentwindow'] = 'tenantactivation';
$eid = $_SESSION['user']['cd_eid'];
$username = $_SESSION['user']['username'];

$parts = explode('_', $username);
$last = array_pop($parts);
$parts = array(implode('_', $parts), $last);

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'tenant-active_html.php';
require_once '../layout/footer.php';

?>
<script type="text/javascript" src="../js/common_ajax.js"></script>
<script src="../js/customer/form-validation.js"></script>
<script src="../js/admin/tenantlist.js"></script>
<script type="text/javascript">
    var tenantName = '<?php echo $parts[0] ?>';
    var tenant_id = '<?php echo $eid; ?>';
    $('#pageName').html('Tenant Activation');
    $('#entity_tenant').val(tenantName);
    $('#entity_id').val(tenant_id);
</script>