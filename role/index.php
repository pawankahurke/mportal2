<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';

require_once 'role_html.php';
require_once '../layout/footer.php';

// Check authorization
nhUser::redirectIfNotAuth();

$userId = $_SESSION["user"]["adminid"];
$username = $_SESSION['user']['username'];

$agentName = $_SESSION["user"]["logged_username"];
$agentUniqId = $_SESSION["user"]["adminEmail"];
?>
<input type="hidden" value="<?php echo url::toText($_SESSION["user"]["adminid"]); ?>" id="userId">
<input type="hidden" value="<?php echo url::toText($_SESSION["user"]["username"]); ?>" id="userName">
<script type="text/javascript">
    $('#pageName').html('Access Right & Permissions');
    var email = '<?php echo $_SESSION['user']['adminEmail']; ?>';
</script>
<style>
    div.bottom {
        bottom: 39px !important;
    }

    #roleGrid_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }
</style>
<script src="../js/rightmenu/rightMenu.js"></script>
<?php
$res = nhRole::checkModulePrivilege('role');
if ($res){
?>
<script type="text/javascript" src="../js/admin/role.js"></script>
  <?php
}
?>
