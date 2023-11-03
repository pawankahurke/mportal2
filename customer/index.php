<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'user';
$_SESSION['currentwindow'] = 'user';

require_once("../include/common_functions.php");
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'user_html.php';
require_once '../layout/footer.php';

// Check authorization
nhUser::redirectIfNotAuth();

?>
<?php
$res = nhRole::checkModulePrivilege('user');
if ($res) {
?>
  <script type="text/javascript" src="../js/customer/users.js"></script>
  <script>
    var ctype = '<?php echo $_SESSION["user"]["customerType"]; ?>';
    var userid = '<?php echo $_SESSION["user"]["userid"]; ?>';
    bussinessLevel = '<?php echo $_SESSION['user']['busslevel']; ?>';
    customerType = '<?php echo $_SESSION['user']['customerType']; ?>';
    aviraInst = '<?php echo $_SESSION["user"]["Avira_Inst"]; ?>';
  </script>
<?php
}
?>
<script type="text/javascript">
  $('#pageName').html('Users');
</script>