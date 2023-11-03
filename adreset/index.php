<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'adpassword';
$_SESSION['currentwindow'] = 'adpassword';

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';

require_once 'adreset_html.php';
require_once '../layout/footer.php';

// Check authorization
nhUser::redirectIfNotAuth();

$userId = $_SESSION["user"]["adminid"];
$username = $_SESSION['user']['username'];

$agentName = $_SESSION["user"]["logged_username"];
$agentUniqId = $_SESSION["user"]["adminEmail"];

if ($_SESSION['searchType'] == 'Service Tag' || $_SESSION['searchType'] == 'Host Name') {
    $searchVal = $_SESSION['searchValue'];
} else {
    $searchVal = $_SESSION['searchValue'];
}
$wgORd = 1;
$uName = "";
if ($domain == "NULL" || $domain == NULL) {
    $wgORd = 2;
    $uName = "mainuser";
} else {
    $wgORd = 1;
    $uName = "administrator";
}
if ($username != "") {
    $uName = $username;
}
?>
<input type="hidden" value="<?php echo url::toText($_SESSION["user"]["adminid"]); ?>" id="userId">
<input type="hidden" value="<?php echo url::toText($_SESSION["user"]["username"]); ?>" id="userName">

<style>
    .hide_hover {
        font-size: 14px;
        color: #000;
        padding: 2px 8px 5px 5px;
    }

    .hide_hover:hover {
        color: #000;
        cursor: pointer;
        padding: 2px 8px 5px 5px;
    }
</style>

?>
<script src="../js/rightmenu/rightMenu.js"></script>
<?php
$res = nhRole::checkModulePrivilege('adpassword');
if ($res) {
?>
    <script src="../js/act_dir/act_dir.js"></script>
<?php
}
?>