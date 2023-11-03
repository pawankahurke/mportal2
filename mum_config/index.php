<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'patchmanagement_new';
$_SESSION['currentwindow'] = 'patchmanagement_new';

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';

// Check authorization
nhUser::redirectIfNotAuth();

?>

<style>
    .hide_hover{
        font-size: 14px;
         color:#000;
         padding: 2px 8px 5px 5px;
    }
    .hide_hover:hover{
        color:#000;
        cursor: pointer;
        padding: 2px 8px 5px 5px;
    }

    div.bottom{
        bottom:39px !important;
    }

    #patchAllListData_info{
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }

    #retrypatchListData_info{
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }

    #criticalupdatepatchListData_info{
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }

</style>
<span type="hidden" id="patchSearch" name="patchSearch" style="display:none;"></span>
<?php require_once 'mum_config_html.php'; ?>
<?php require_once '../layout/footer.php'; ?>

<script src="../js/rightmenu/rightMenu.js"></script>
<?php
$res = nhRole::checkModulePrivilege('patchmanagement_new');
if ($res){
?>
<script src="../js/mum/patchmanagement.js"></script>
<script src="../js/mum/patchConfigure.js"></script>
  <?php
}
?>
<script type="text/javascript">
$('#pageName').html('Patch Management Configure');
</script>

