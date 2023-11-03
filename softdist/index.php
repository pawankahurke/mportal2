<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
global $base_url;


$_SESSION['windowtype'] = 'softwaredistribution';
$_SESSION['currentwindow'] = 'softwaredistribution';
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
require_once 'swd_list_html.php';
require_once '../layout/footer.php';
// require_once '../communication/communication.php';

// Check authorization
nhUser::redirectIfNotAuth();

?>

<script>
    var BASE_PATH = "<?php echo url::toText($base_url); ?>";
</script>
<?php
$res = nhRole::checkModulePrivilege('softwaredistribution');
if ($res) {
?>
    <script type="text/javascript" src="../js/softdist/editPackage.js"></script>
    <script type="text/javascript" src="../assets/js/all.fine-uploader.js"></script>
    <script type="text/javascript" src="../js/softdist/softdistData.js"></script>
    <script type="text/javascript" src="../js/softdist/addPackage.js"></script>
    <script type="text/javascript" src="../js/softdist/softdistConfigure.js"></script>
    <script type="text/javascript" src="../js/softdist/softdistAudit.js"></script>
    <script type="text/javascript" src="../js/softdist/configPackage.js"></script>
<?php
}
?>
<script src="../js/rightmenu/rightMenu.js"></script>
<link rel="stylesheet" type="text/css" href="../assets/css/fineuploader.css">
<link rel="stylesheet" type="text/css" href="../assets/css/qquploader.css">
<link rel="stylesheet" type="text/css" href="../assets/css/swd.css">

<script>
    var usrEmail = "<?php echo url::toText($_SESSION['user']['adminEmail']); ?>";
    $('#pageName').html('Software Distribution');
</script>

<style>
    div.bottom {
        bottom: 39px !important;
    }

    #packageGrid_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }

    #packageGrid2_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }
</style>