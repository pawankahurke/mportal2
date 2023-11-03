<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";

include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'home';
$_SESSION['currentwindow'] = 'home';
$bussLevel = $_SESSION['user']['busslevel'];
$custEmail = $_SESSION['user']['adminEmail'];
$custName = $_SESSION['user']['companyName'];
global $kibana_url;
require_once $absDocRoot . 'layout/header.php';
// require_once $absDocRoot . 'layout/sidebar.php';
require_once $absDocRoot . 'layout/rightmenu.php';
require_once $absDocRoot . 'homepage/new_index_html.php';
require_once $absDocRoot . 'layout/footer.php';

?>
<script src="../js/rightmenu/rightMenu.js"></script>
<!-- <script src="../js/dashboard/kibanadashboard.js"></script> -->
<script type="text/javascript">
    var tempid = '<?php echo $kibana_url; ?>';
    $('#pageName').html('Home Page');
</script>

<!-- <script src="../js/home/home.js?_v0.1"></script> -->