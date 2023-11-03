        <?php
        $fromNotificationPage = isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'notification/notification.php') && url::issetInGet('notification') ? true : false;
        $machineName = false;
        if ((url::issetInGet('notification') && url::issetInGet('machine')) && !url::isEmptyInGet('machine')) {
            $machineName = (url::getToText('machine') === 'multi') ? 'Multiple machines' : url::getToText('machine');
        }
        ?>
        <?php
        include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
        include_once $absDocRoot . 'vendors/csrf-magic.php';
        csrf_check_custom();
        ?>
        <!-- content starts here  -->
        <div class="content white-content troubleShooter">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="toolbar">
                                <div class="bullDropdown leftDropdown">
                                    <input type="hidden" id="valueSearch">
                                    <h5>Selection: <?php if ($machineName) { ?><span style="width: 70%;font-weight:bold"><?php echo $machineName; ?></span><?php } else { ?><span class="site" title=""></span><?php } ?></h5>
                                </div>
                            </div>

                            <div class="row clearfix innerPage">
                                <div id="absoLoader" style="display:none;width:100%;z-index: 1000;position: relative;left:48%;height:100%"><img src="../assets/img/loader2.gif" style="margin-top: 20%;"></div>

                                <div class="col-md-3 col-sm-12 col-xs-12 lf-rt-br equalHeight">
                                    <div class="dropdown">

                                    </div>
                                    <input type="hidden" value="" id="seq_id">
                                    <div class="table-responsive innerLeft">
                                        <div class="form">
                                            <input id="troubleshooting_searchbox2" class="form-control" placeholder="Search" aria-controls="DataTables_Table" type="search" style="display:block"><i style="margin-left: 252px;margin-top: -43px;" class="tim-icons icon-zoom-split"></i>
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
                                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="tim-icons icon-bullet-list-67"></i>
                                                </button>

                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                    <a data-qa="ts-addProfile" id="addProfile" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addtrblprofile', 2); ?>" data-target="add_profile" onclick="openAddProfile('<?php echo $_SESSION['new_mid'] ?>','<?php echo $_SESSION['profileKeys'] ?>','<?php echo $_SESSION['new_page'] ?>')">Add Profile</a>
                                                    <a data-qa="ts-editProfile" id="editProfile" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('edittrblprofile', 2); ?>" onclick="openEditProfile();">Edit Profile</a>
                                                    <a data-qa="ts-deleteProfile" id="deleteProfile" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('deletetrblprofile', 2); ?>" onclick="openDeleteProfile();">Delete Profile</a>
                                                    <a data-qa="ts-backoption" id="backoption" class="dropdown-item rightslide-container-hand dropHandy version" onclick="gotoMain();" href="javascript:" style="display:none">Back</a>
                                                    <a data-qa="ts-enbdisprofile" id="enbdisprofile" class="dropdown-item rightslide-container-hand dropHandy  <?php echo setRoleForAnchorTag('enbdisprofile', 2); ?>" onclick="enable_disableprofile();">Enable/Disable Profile</a>
                                                    <?php if ($fromNotificationPage) { ?><a data-qa="ts-backtonotif" id="backtonotif" class="dropdown-item rightslide-container-hand dropHandy" onclick="backToNotif();">Back To Notification</a><?php } ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } else if ($fromNotificationPage) { ?>
                                        <div class="bullDropdown">
                                            <div class="dropdown">
                                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="tim-icons icon-bullet-list-67"></i>
                                                </button>

                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                    <a id="backtonotif" class="dropdown-item rightslide-container-hand dropHandy" onclick="backToNotif();">Back To Notification</a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <div id="troubleshooterDiv" class="troubleInn">
                                        <h4 id="mainParentTitle">Troubleshooting</h4>
                                        <p id="parentDesc"></p>
                                    </div>
                                    <div class="troubleInn" style="display: block;padding: 0px 17px;">
                                        <div align="center" class="margin-top100">
                                            <div id="loader" class="loader" data-qa="loader" style="display: none">
                                                <br>
                                                <img src="../assets/img/loader.gif" />
                                                <br>
                                                <h5>Please wait..!</h5>
                                            </div>
                                        </div>
                                        <p id="parentDesc"></p>
                                        <div id="showSiteMsg" style="margin-left: 17%; display: none;">
                                            <h5 id="config_details"></h5>
                                        </div>

                                        <div class="table-full-width table-responsive" id="listDiv" style="display: block;">
                                            <div class="form">
                                                <div class="sidebar">
                                                    <div id="accordion">
                                                        <!--                                                    <div id="clickList">-->
                                                        <!--                                                            <div class="card" id="clickList">-->
                                                        <ul id="clickList" style="list-style: none;" class="nav">
                                                        </ul>
                                                        <!--                                                            </div>-->
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
            <div class="card-title">
                <h4><span id="slider-title"><span></h4>
                <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="config-trbl-container">&times;</a>
            </div>
            <div align="center" class="margin-top100">
                <div id="loader" class="loader" data-qa="loader">
                    <br>
                    <img src="../assets/img/loader-lg.gif" />
                    <br>
                    <h3>Please wait..!</h3>
                </div>
            </div>
            <div class="form table-responsive white-content" id="jsonModalDialogDivs">
                <!--Data comes dynamically-->
            </div>
        </div>

        <!--For Dynamic 0-->
        <div id="config_container" class="rightSidenav" data-class="sm-3">
            <div class="card-title">
                <h4><span id="slider_title"><span></h4>
                <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="config_container">&times;</a>
            </div>
            <div align="center" class="margin-top100">
                <div id="loader_pbar">
                    <br>
                    <img src="../assets/img/loader-lg.gif" />
                    <br>
                    <h3>Please wait..!</h3>
                </div>
            </div>
            <div class="form table-responsive white-content" id="progressMainDiv">
                <!--Data comes dynamically-->
            </div>
        </div>

        <!--Add New Profile-->
        <div id="add_profile" class="rightSidenav" data-class="sm-3">
            <div class="card-title">
                <h4>Add Profile</h4>
                <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="add_profile">&times;</a>
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
            <div class="card-title">
                <h4>Edit Profile</h4>
                <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="edit_profile">&times;</a>
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