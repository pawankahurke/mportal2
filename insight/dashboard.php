<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../include/common_functions.php';
$_SESSION['windowtype'] = 'visualisation';
$_SESSION['currentwindow'] = 'visualisation';

$res = checkModulePrivilege('visualisation', 2);
if (!$res) {
    echo "Permission Denied";
    exit();
}

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'dashboard_html.php';
require_once '../layout/rightmenu.php';
require_once '../layout/footer.php';

$useremail = $_SESSION['user']['adminEmail'];
global $kibana_url;
?>

<script type="text/javascript" src="../js/rightmenu/rightMenu.js"></script>
<script type="text/javascript" src="../js/dashboard/dashboard.js"></script>
<script type="text/javascript">
    var loggedUserId = '<?php echo $_SESSION['user']['userid']; ?>';
    var tempid = '<?php echo $kibana_url; ?>';
    $('#pageName').html('Create Dashboard');
    var user = '<?php echo $useremail; ?>';
    if (user === 'admin@nanoheal.com') {
        $('.evironmentGlobal').show();
    } else {
        $('.evironmentGlobal').hide();
    }
</script>
<style>
    div.bottom{bottom:39px !important;}
    #dashboardList_info{margin-left: 14%;color: #000;font-size: 10px;}
</style>

