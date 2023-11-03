<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
require_once '../include/common_functions.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'autoaudit';
$_SESSION['currentwindow'] = 'autoaudit';

// $res = checkModulePrivilege('autoaudit', 2);
// if(!$res){
//     echo 'Permission denied';
//     exit();
// }

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
include_once 'autoview_html.php';
include_once 'autoLogFunction.php';
require_once '../layout/footer.php';

// Check authorization
nhUser::redirectIfNotAuth();

?>
<?php
$res = nhRole::checkModulePrivilege('autoaudit');
if ($res) {
?>
  <script src="../js/admin/autolog.js"></script>
<?php
}
?>
<script type="text/javascript">
  $('#pageName').html('Automation Audit');
</script>

<style>
  div.bottom {
    bottom: 39px !important;
  }

  #roleGrid_info {
    margin-left: 14%;
    color: #000;
    font-size: 10px;
  }
</style>