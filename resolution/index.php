<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../include/common_functions.php';
$_SESSION['windowtype'] = 'troubleshooting';
$_SESSION['currentwindow'] = 'troubleshooting';

$res = checkModulePrivilege('troubleshooting', 2);
if (!$res) {
    echo 'Permission denied';
    exit();
}
if ($troubleshooterMode == 'Off') {
    header('Location: ../index.php');
}

if (!empty(url::getToText('machine'))) {
    /**
     * Select a notification, go to troubleshooters
     * https://nanoheal.atlassian.net/browse/NCP-832
     * 
     * 1. Go to troubleshooters 
     * 2. Select a notification, go to troubleshooters
     * 3. Has site selected, should have machine name selected from notification page.
     */
    $_SESSION["searchType"] = 'ServiceTag';
    $_SESSION["searchValue"] = url::getToText('machine');

    header('Location: ' .  "https://" . $_SERVER['HTTP_HOST'] . "/Dashboard/resolution/index.php?notification");
    exit();
}


require_once '../layout/header.php';
include_once 'selectProfile.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
require_once 'troubleshooter_html.php';
require_once '../layout/footer.php';
include_once '../communication/communication.php';

?>

<script type="text/javascript">
    $('#pageName').html('Manage Devices');
    $('#currentwindow').val('troubleshooting');
</script>

<script type="text/javascript">
    stype = '<?php echo $_SESSION["searchType"]; ?>';
    tsRestricted = '<?php echo $TS_restricted; ?>';
    notifyWindow = '<?php echo $notifyWindow; ?>';
</script>
<script src="../js/rightmenu/rightMenu.js"></script>
<script type="text/javascript" src="../js/resolutions/interactive.js"></script>
<script type="text/javascript">
    <?php
    if ($_SESSION['user']['loginType'] == 'PTS') { ?>
        machClick('Sites', '<?php echo $machineData['siteName']; ?>', '<?php echo $machineData['siteName']; ?>', '<?php echo $_SESSION['pts']['searchValue']; ?>', '<?php echo $machineData['id']; ?>', 'Online');
    <?php } else if (url::requestToText('type') === 'myacnt') { ?>
        machClick('Sites', '<?php echo $_SESSION['rparentName']; ?>', '<?php echo $_SESSION['rparentName']; ?>', '<?php echo url::requestToAny('machine'); ?>', '<?php echo url::requestToAny('censusid'); ?>', '');
    <?php } ?>
    $('#selectProfile').val('<?php if (isset($_SESSION['notifyshowProfile'])) {
                                    echo $_SESSION['notifyshowProfile'];
                                } ?>');
    $('#rmselectprofile').val('<?php echo $rmselectprofile; ?>');
</script>