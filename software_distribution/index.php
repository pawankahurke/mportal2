<?php

if (!isset($_SESSION)) {
}

$moduleName = 'software_distribution';
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'softwaredistribution';
$_SESSION['currentwindow'] = 'softwaredistribution';
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
require_once 'software_distribution_list.php';
require_once '../layout/footer.php';
require_once '../communication/communication.php';
?>

<script type="text/javascript" src="../js/software_distribution/software_distribution.js"></script>
<script src="../js/rightmenu/rightMenu.js"></script>

<script>
    var usrEmail = "<?php echo url::toText($_SESSION['user']['adminEmail']); ?>";
</script>
<link rel="stylesheet" type="text/css" href="../assets/css/fineuploader.css">
<link rel="stylesheet" type="text/css" href="../assets/css/qquploader.css">
<link rel="stylesheet" type="text/css" href="../assets/css/software_distribution.css">
<script type="text/javascript" src="../assets/js/all.fine-uploader.js"></script>