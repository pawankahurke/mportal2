<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$_SESSION['windowtype'] = 'troubleshooting';
$_SESSION['currentwindow'] = 'troubleshooting';
require_once '../layout/header.php';
include_once 'selectProfile.php';
require_once '../layout/sidebar.php';
require_once '../layout/rightmenu.php';
require_once 'troubleshooter_pts_html.php';
require_once '../layout/footer.php';
include_once('../communication/communication.php');

$serviceTag = url::requestToAny('mach');
$siteName   = url::requestToAny('site');

?>
<style type="text/css">
    #navigation {
        display: none !important;
    }
</style>
<script type="text/javascript">
    $('#pageName').html('Toolkit');
    $('#currentwindow').val('troubleshooting');
</script>
<script type="text/javascript" src="../config.js"></script>
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
        $('.logo a').prop("href", "javascript:void(0);");
        machClick('ServiceTag##<?php echo $serviceTag; ?>##<?php echo $siteName; ?>##Sites##');
        saveSelectState();
    <?php } else if (url::requestToText('type') === 'myacnt') { ?>
        machClick('Sites', '<?php echo $_SESSION['rparentName']; ?>', '<?php echo $_SESSION['rparentName']; ?>', '<?php echo url::requestToAny('machine'); ?>', '<?php echo url::requestToAny('censusid'); ?>', '');
    <?php } ?>
    $('#selectProfile').val('<?php echo $_SESSION['notifyshowProfile']; ?>');
    $('#rmselectprofile').val('<?php echo $rmselectprofile; ?>');
</script>