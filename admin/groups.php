<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'groups';
$_SESSION['currentwindow'] = 'groups';

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../admin/groups_html.php';
require_once '../layout/footer.php';
?>
<script type="text/javascript" src="../js/admin/groupdata.js"></script>
<script type="text/javascript">
    $('#pageName').html('Groups');


    $('.add_group').click(function() {
        $('#addButton').show();
        $('#editButton').hide();

        $('#groupName').html('Add Group');
        rightContainerSlideClose('grp-add-container');
        rightContainerSlideOn('rsc-add-container5');
    });

    $('.manual_editgroup').click(function() {
        $('#groupName').html('Modify Group');
        $('#addButton').hide();
        $('#editButton').show();
        rightContainerSlideClose('edit-group');
        rightContainerSlideOn('rsc-add-container5');

    });
</script>