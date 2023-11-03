<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'eventInfo';
$_SESSION['currentwindow'] = 'eventInfo';

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once 'event_html.php';
require_once '../layout/rightmenu.php';
require_once '../layout/footer.php';
?>
<script type="text/javascript">
    $('#pageName').html('Event Information');
</script>
<script type="text/javascript" src="../js/rightmenu/rightMenu.js"></script>
<script type="text/javascript" src="../js/home/event.js"></script>
<style>
    div.bottom {
        bottom: 39px !important;
    }

    #eventTable_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }

    #eventEventInfo .modal-content {
        position: absolute;
        top: 0px;
    }
</style>