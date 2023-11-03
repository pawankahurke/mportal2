<?php
$fromNotificationPage = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'notification/notification.php') && url::issetInGet('notification') ? true : false;
$machineName = false;
if ((url::issetInGet('notification') && url::issetInGet('machine')) && !url::isEmptyInGet('machine')) {
    $machineName = (url::getToText('machine') === 'multi') ? 'Multiple machines' : url::getToText('machine');
}

// $name = $_SESSION['searchValue'];
// $type = $_SESSION['searchType'];

// $nameStr = $type." - ".$name;
// $fromWindow = $_SESSION['fromwindow'];
?>
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?>
<style>
    .expand {
        height: 5px !important;
    }

    #fullWrapper {
        height: 102vh !important;
    }
</style>
<!-- content starts here  -->
<div class="content white-content troubleShooter" style="padding-top: 33px;">
    <div class="row mt-4">
        <div class="col-md-12 pr-0 pl-0">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <input type="hidden" id="selectedPrfoleName">
                        <!-- <div class="bullDropdown leftDropdown">
                            <input type="hidden" id="valueSearch">
                            <input type="hidden" id="selectedPrfoleName">
                            <h5>Selection: <?php if ($machineName) { ?><span style="width: 70%;font-weight:bold"><?php echo $machineName; ?></span><?php } else { ?><span class="site" title=""></span><?php } ?> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>
                        </div> -->
                    </div>

                    <div class="row clearfix innerPage">
                        <div id="absoLoader" style="display:none;width:100%;z-index: 1000;position: relative;left:48%;height:100%"><img src="../assets/img/nanohealLoader.gif" style="margin-top: 20%;width: 71px;"></div>

                        <div class="col-md-3 col-sm-12 col-xs-12 lf-rt-br equalHeight pl-lg-0">
                            <div class="dropdown">

                            </div>
                            <input type="hidden" value="<?php echo url::toText($fromWindow); ?>" id="fromWindow">
                            <input type="hidden" value="" id="seq_id">
                            <input type="hidden" value="" id="osTypeDropVal">
                            <?php if (!$TS_restricted) { ?>
                                <input id="troubleshooting_searchbox2" class="form-control" placeholder="Search" aria-controls="DataTables_Table" type="search" style="display:block"><i style="margin-left: 252px;margin-top: -43px;" class="tim-icons icon-zoom-split"></i>
                            <?php } ?>
                            <div class="table-responsive innerLeft">
                                <div class="form">
                                    <div class="sidebar" id="main">
                                        <ul class="nav" id="toolboxList">
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-9 col-sm-12 col-xs-12 rt-lf equalHeight">
                            <input type="hidden" id="divId">
                            <input type="hidden" id="titleStore">
                            <input type="hidden" id="backParentId">
                            <?php
                            if (
                                isRoleEnabled('addtrblprofile') ||
                                isRoleEnabled('edittrblprofile') ||
                                isRoleEnabled('deletetrblprofile') ||
                                isRoleEnabled('enbdisprofile')
                            ) {
                            ?>
                                <div class="bullDropdown">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="tim-icons icon-bullet-list-67"></i>
                                        </button>

                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a data-qa="ts2-addProfile" id="addProfile" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addtrblprofile', 2); ?>" data-bs-target="add_profile" onclick="openAddProfile('<?php echo $_SESSION['new_mid'] ?>', '<?php echo $_SESSION['profileKeys'] ?>', '<?php echo $_SESSION['new_page'] ?>')">Add Profile</a>
                                            <a data-qa="ts2-editProfile" id="editProfile" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('edittrblprofile', 2); ?>" onclick="openEditProfile();">Edit Profile</a>
                                            <a data-qa="ts2-deleteProfile" id="deleteProfile" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('deletetrblprofile', 2); ?>" onclick="openDeleteProfile();">Delete Profile</a>
                                            <a data-qa="ts2-backoption" id="backoption" class="dropdown-item rightslide-container-hand dropHandy version" onclick="gotoMain();" href="javascript:" style="display:none">Back</a>
                                            <a data-qa="ts2-enbdisprofile" id="enbdisprofile" class="dropdown-item rightslide-container-hand dropHandy  <?php echo setRoleForAnchorTag('enbdisprofile', 2); ?>" onclick="enable_disableprofile();">Enable/Disable Profile</a>
                                            <?php if ($fromNotificationPage) { ?><a data-qa="ts2-backtonotif" id="backtonotif" class="dropdown-item rightslide-container-hand dropHandy" onclick="backToNotif();">Back To Notification</a><?php } ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } else if ($fromNotificationPage) { ?>
                                <div class="bullDropdown">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="tim-icons icon-bullet-list-67"></i>
                                        </button>

                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <a id="backtonotif" class="dropdown-item rightslide-container-hand dropHandy" onclick="backToNotif();">Back To Notification</a>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <div id="troubleshooterDiv" class="troubleInn">
                                <h4 id="mainParentTitle"></h4>
                                <p id="parentDesc"></p>
                            </div>
                            <div class="troubleInn" style="display: block;padding: 0px 17px;">
                                <div id="absLoader" style="display:none;width:100%;z-index: 1000;position: relative;left:48%;height:100%"><img src="../assets/img/nanohealLoader.gif" style="margin-top: 20%;width: 71px;"></div>

                                <div align="center" class="margin-top100">
                                    <!-- <div id="absLoader" style="display: none">
                                        <img src="../assets/img/loader2.gif" />
                                         <h5>Please wait..!</h5>
                                  </div> -->
                                </div>
                                <p id="parentDesc"></p>
                                <div id="showSiteMsg" style="margin-left: 17%; display: none;">
                                    <h5 id="config_details"></h5>
                                </div>

                                <div class="table-full-width table-responsive" id="listDiv" style="display: block;">
                                    <div class="form">
                                        <div class="sidebar">
                                            <div id="accordion">
                                                <ul id="clickList" style="list-style: none;" class="nav">
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end content-->
        </div>
        <!--  end card  -->
    </div>
    <!-- end col-md-12 -->
</div>
<!-- end row -->


<!--For Dynamic 1-->
<div id="config-trbl-container" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4><span id="slider-title"><span></h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="config-trbl-container">&times;</a>
    </div>
    <!--    <div align="center" class="margin-top100">
                            <div id="loader" class="loader"  data-qa="loader">
                                <br>
                                <img src="../assets/img/loader-lg.gif" />
                                <br>
                                <h3>Please wait..!</h3>
                            </div>
                        </div>-->
    <div class="form table-responsive white-content" id="jsonModalDialogDivs">
        <!--Data comes dynamically-->
    </div>
</div>

<!--For Dynamic 0-->
<div id="config_container" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4><span id="slider_title"><span></h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="config_container">&times;</a>
    </div>
    <div align="center" class="margin-top100">
        <!-- <div id="loader_pbar">
                                                <br>
                                                <img src="../assets/img/loader-lg.gif" />
                                                <br>
                                                <h3>Please wait..!</h3>
                                            </div> -->
    </div>
    <div class="form table-responsive white-content" id="progressMainDiv">
        <!--Data comes dynamically-->
    </div>
</div>

<!--Add New Profile-->
<div id="add_profile" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Add Profile</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="add_profile">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="add_newprofile();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <form id="add_profile_trbl">
            <div class="card">
                <div class="card-body">
                    <div class="row">

                        <div class="form-group has-label col-md-12" id="add_inputfields">
                        </div>

                        <div class="category form-category">* Required fields</div>
                    </div>
                </div>
        </form>
    </div>

    <div class="button col-md-12 text-center">
        <span class="error-txt" id="error_add_entity" localized="" style="color:red;"></span>
    </div>
</div>
</div>

<!--Edit Profile-->
<div id="edit_profile" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Edit Profile</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="edit_profile">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="saveEditedProfile();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form id="add_profile_trbl">
            <div class="card">
                <div class="card-body">
                    <div class="row">

                        <div class="form-group has-label col-md-12" id="edit_profile_page">


                        </div>

                        <div class="category form-category">* Required fields</div>
                    </div>
                </div>
        </form>
    </div>

    <div class="button col-md-12 text-center">
        <span class="error-txt" id="error_add_entity" localized="" style="color:red;"></span>
    </div>
</div>
</div>

<style>
    #clickList h5 {
        font-weight: bold !important;
    }

    .active {
        padding-left: 13px !important;
    }

    .txt {
        color: #666 !important;
        font-weight: 400 !important;
    }
</style>