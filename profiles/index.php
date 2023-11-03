<?php

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'profilewizard';
$_SESSION['currentwindow'] = 'profilewizard';

unset($_SESSION['pwdefdata']);
unset($_SESSION['pwnewdata']);
unset($_SESSION['pwprofdefdata']);
unset($_SESSION['ProfileStateOne']);
unset($_SESSION['pwvardata']);
unset($_SESSION['pwconfprofdata']);

require_once '../layout/header.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
require_once 'profile_html.php';
require_once '../layout/footer.php';

// Check authorization
nhUser::redirectIfNotAuth();

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
<?php
$res = nhRole::checkModulePrivilege('profilewizard');
if ($res) {
?>
    <script src="../js/profiles/profilewiz.js"></script>
<?php
}
?>

<style>
    div.bottom {
        bottom: 39px !important;
    }

    #profileWizardGrid_info {
        margin-left: 14%;
        color: #000;
        font-size: 10px;
    }
</style>