<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'profile';
$_SESSION['currentwindow'] = 'profile';

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
require_once 'profile_edit_html.php';
require_once '../layout/footer.php';
?>
<style type="text/css">
    .buttonGrey {
        background: rgba(0, 0, 0, 0.20);
    }
</style>
<script type="text/javascript">
    $('#pageName').html('Profiles');
</script>
<script src="../js/rightmenu/rightMenu.js"></script>
<script src="../js/profiles/profilewiz-edit.js"></script>