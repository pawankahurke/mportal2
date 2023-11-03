<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'customer';
$_SESSION['currentwindow'] = 'ticketingwizard';
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'ticketingwizard_html.php';
require_once '../layout/rightmenu.php';
require_once '../layout/footer.php';

// Check authorization
nhUser::redirectIfNotAuth();

?>

<script type="text/javascript" src="../js/rightmenu/rightMenu.js"></script>
<?php
$res = nhRole::checkModulePrivilege('customer');
if ($res) {
?>
  <script src="../js/customer/ticketingwizard.js"></script>
<?php
}
?>
<script type="text/javascript">
  $('#pageName').html('Ticketing Wizard');
</script>
<style>
  div.bottom {
    bottom: 39px !important;
  }

  #ticketingDataGrid_info {
    margin-left: 14%;
    color: #000;
    font-size: 10px;
  }
</style>