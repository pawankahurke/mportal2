<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'device';
$_SESSION['currentwindow'] = 'device';
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'device_html.php';
require_once '../layout/rightmenu.php';
require_once '../layout/footer.php';

// Check authorization
nhUser::redirectIfNotAuth();

?>
<script type="text/javascript" src="../js/rightmenu/rightMenu.js"></script>
<?php
$res = nhRole::checkModulePrivilege('device');
if ($res) {
?>
    <script type="text/javascript" src="../js/customer/advgrp.js"></script>
    <script type="text/javascript" src="../js/customer/device.js"></script>
    <script type="text/javascript">
        $('#pageName').html('Groups');
        $('.leftDropdown').hide();
        $('.add_group').click(function() {
            $('.addButton').show();
            $('.editButton').hide();

            $('#groupName').html('Add Device');
            rightContainerSlideClose_Device('grp-add-container');
            rightContainerSlideClose_Device('grp-addmod-container');
            rightContainerSlideOn('rsc-add-container5');
        });

        $('.manual_editgroup').click(function() {
            $('#groupName').html('Modify Device');
            $('.addButton').hide();
            $('.editButton').show();
            rightContainerSlideClose_Device('edit-group');
            rightContainerSlideOn('rsc-add-container5');

        });
    </script>
<?php
}
?>
<style>
    #emailDistributeLoader {
        margin-top: 2%;
        display: none;
    }

    div.bottom {
        bottom: 39px !important;
    }

    #detaild_grid_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }

    div.bottom {
        bottom: 39px !important;
    }

    #advncdgroupList_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }
</style>