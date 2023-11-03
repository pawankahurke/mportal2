<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
if(!isset($_SESSION)){
    $_SESSION['currentwindow'] = 'softwaredistribution_audit';
}
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
require_once 'swd_audit_html.php';
require_once '../layout/footer.php';
?>

<script type="text/javascript" src="../js/softdist/editPackage.js"></script>
<script type="text/javascript" src="../assets/js/all.fine-uploader.js"></script>
<script type="text/javascript" src="../js/softdist/softdistData.js"></script>
<script type="text/javascript" src="../js/softdist/addPackage.js"></script>
<script type="text/javascript" src="../js/softdist/softdistAudit.js"></script>
<script src="../js/rightmenu/rightMenu.js"></script>

<script>
$(document).ready(function(){
    //dummyInit();
    Get_SoftwareDistributionData();
    $('table.nhl-datatable').parent('div.dataTables_scrollBody').css({"height": "calc(100vh - 240px)"});
});

function dummyInit(){
    $('#valueSearch').val('HFNQA__201800010');
    $('#selected').val('20');
}
</script>
<style>
div.bottom{bottom:39px !important;}
    #auditGridDetail_info{margin-left: 20%;color: #000;font-size: 10px;}
</style>
