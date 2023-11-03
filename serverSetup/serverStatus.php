<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
include_once '../include/common_functions.php';
$_SESSION['windowtype'] = 'serverstatus';
$_SESSION['currentwindow'] = 'serverstatus';


require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'serverStatus_html.php';
require_once '../layout/rightmenu.php';
require_once '../layout/footer.php';

$useremail = $_SESSION['user']['adminEmail'];
global $reportingurl;
global $kibana_url;
?>
<script type="text/javascript" src="../js/rightmenu/rightMenu.js"></script>
<script type="text/javascript" src="../js/serverStatus.js"></script>
<script type="text/javascript">
    $('#pageName').html('Server Status');
</script>
<style>
    div.bottom{bottom:39px !important;}
    #dashboardList_info{margin-left: 14%;color: #000;font-size: 10px;}
</style>

