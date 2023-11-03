<?php

ob_start();


include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
include_once '../lib/l-dbConnect.php';

if (!isset($_SESSION['user']['dashboardLogin'])) {
    header("location:../index.php");
}

$_SESSION['windowtype'] = 'branding';
$_SESSION['currentwindow'] = 'branding';

$res = checkModulePrivilege('customisebranding', 2);
if (!$res) {
    header("location:../index.php");
    exit();
}
?>
<div class="customUI" ng-app="customizeApp" ng-controller="customizeController">
    <div class="row">
        <div class="col-md-12">
            <div class="leftCol">
                <?php
                require_once '../layout/header.php';
                require_once 'customize_html.php';
                require_once 'customize_marketing_html.php';
                require_once 'customize_landing_html.php';
                require_once 'customize_email_html.php';
                require_once 'customize_finishScreen_html.php';
                ?>
            </div>
        </div>
    </div>
</div>
<?php
require_once '../layout/footer.php';

?>
<script type="text/javascript" src="../js/admin/customize.js"></script>
<script type="text/javascript">
    //$('title').html('Dashboard :: Customize Client UI');
    $('#pageName').html('Customise the theme and branding');
</script>