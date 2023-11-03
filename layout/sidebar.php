<?php
include_once   $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/layout/sidebar.php";
?>

<style>
    .side-bar-menu {
        background: none !important;
        background-color: #050d30 !important;
        width: 80px;

    }

    .side-bar-menu ul li:hover {
        /*background-color: #131e4e !important;*/
        background-color: #1E2446 !important;
    }

    .menu-new-dch {
        text-align: center;
        font-size: small;
        border-bottom: 1px solid grey;
        text-transform: none !important;
    }

    .menu-l1-view {
        float: right;
        position: fixed;
        margin-left: 80px;
        background-color: #050d30;
        margin-top: -30px;
        border-left: 1px solid #bbb;
        width: 210px;
        letter-spacing: 1px;
        overflow: auto;
        max-height: calc(100vh - 150px);
    }

    .menu-l2-view {
        float: right;
        position: fixed;
        margin-left: 209px;
        background-color: #050d30;
        margin-top: -38px;
        border-left: 1px solid #bbb;
        width: 210px;
    }
</style>

<div class="sidebar side-bar-menu">
    <div class="sidebar-wrapper">
        <div class="logo">
            <a id="sdb-main-logo-v8-n" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/home/" ?>" class="simple-text logo-mini">
                <img src="<?php echo getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/menu-logo.png" ?>">;
            </a>
            <a href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/home/" ?>" class="simple-text logo-normal" style="margin-top:1px">
                Nanoheal
            </a>
        </div>
        <?php
        if (!isset($_SESSION['user']['loggedUType']) || $_SESSION['user']['loggedUType'] != 'Other') {
        ?>
            <ul class="nav">

                <li class="<?php echo setRoleForAnchorTag('dashboardview', 2); ?> hover-collapse" div-target="dashboardItems">
                    <a data-bs-toggle="collapse" href="#dashboardItems">
                        <span onmouseover="showDashboard()">
                            <i class="tim-icons icon-components"></i>
                            <p> &nbsp; </p>
                        </span>

                    </a>
                    <div class="collapse menu-l1-view" id="dashboardItems">
                        <p class="menu-new-dch"> Dashboard </p>
                        <ul class="nav" id="dashoardList">
                            <li>
                                <a href="javascript:void(0);">
                                    <span class="sidebar-normal">Loading...</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>


                <?php if (getenv('show_groupped_analitics_menu') == 'true') { ?>
                    <!-- Show additional menu fro groupped analitics -->
                    <li class="<?php echo setRoleForAnchorTag('dashboardview', 2); ?> hover-collapse" div-target="dashboardItemsGroupped">
                        <a data-bs-toggle="collapse" href="#dashboardItemsGroupped">
                            <span onmouseover="showDashboardGroupped()">
                                <i class="tim-icons icon-components"></i>
                                <p> &nbsp; </p>
                            </span>
                        </a>
                        <div class="collapse menu-l1-view" id="dashboardItemsGroupped">
                            <p class="menu-new-dch"> Dashboard v2 </p>
                            <ul class="nav" id="dashoardListGroupped">
                                <li>
                                    <a href="javascript:void(0);">
                                        <span class="sidebar-normal">Loading...</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                <?php } ?>

                <?php if (!getenv('HIDE_MENU_FEDERATE_MBAG_DTAG')) { ?>
                    <li class="<?php echo setRoleForAnchorTag('agentworkspace', 2); ?> hover-collapse" div-target="agentWSMenuItems">
                        <a data-bs-toggle="collapse" href="#agentWSMenuItems">
                            <i class="tim-icons icon-vector"></i>
                            <p> &nbsp; </p>
                        </a>
                        <div class="collapse menu-l1-view" id="agentWSMenuItems">
                            <p class="menu-new-dch"> Agent Workspace </p>
                            <ul class="nav">
                                <li class="<?php echo setRoleForAnchorTag('notification', 2); ?> <?php echo setActiveClass('windowtype', 'notification'); ?>">
                                    <a data-qa="menu-notifications" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/notification/" ?>">
                                        <span class="sidebar-normal"> Notifications </span>
                                    </a>
                                </li>
                                <li class="<?php echo setRoleForAnchorTag('compliance', 2); ?> <?php echo setActiveClass('windowtype', 'compliance'); ?>">
                                    <!-- <a href="../compliance"> -->
                                    <a data-qa="compliance" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/Compliance/" ?>">
                                        <span class="sidebar-normal"> Compliance </span>
                                    </a>
                                </li>
                                <?php if ($troubleshooterMode == 'On') { ?>
                                    <li class="<?php echo setRoleForAnchorTag('troubleshooting', 2); ?> <?php echo setActiveClass('windowtype', 'troubleshooting'); ?>">
                                        <a data-qa="manageDevices" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/resolution/" ?>">
                                            <span class="sidebar-normal"> Manage Devices </span>
                                        </a>
                                    </li>
                                <?php } ?>
                                <li class="<?php echo setRoleForAnchorTag('softwaredistributionconfig', 2); ?> <?php echo setActiveClass('windowtype', 'softwaredistributionconfig'); ?>">
                                    <a data-qa="softdistConfig" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/softdist_config/" ?>">
                                        <span class="sidebar-normal"> Software Distribution Configuration</span>
                                    </a>
                                </li>
                                <li class="<?php echo setRoleForAnchorTag('patchmanagement', 2); ?> <?php echo setActiveClass('windowtype', 'patchmanagement'); ?>">
                                    <a data-qa="patchManagement" class='patchManagement' href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/mum/" ?>">
                                        <span class="sidebar-normal"> Patch Management </span>
                                    </a>
                                </li>
<!--                                <li class="--><?php //echo setRoleForAnchorTag('cronMonitoring', 2); ?><!-- --><?php //echo setActiveClass('windowtype', 'cronMonitoring'); ?><!--">-->
<!--                                  <a data-qa="cronMonitoring" class='cronMonitoring' href="--><?php //echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/CronMonitoring/" ?><!--">-->
<!--                                    <span class="sidebar-normal"> Cron Monitoring </span>-->
<!--                                  </a>-->
<!--                                </li>-->
                            </ul>
                        </div>
                    </li>
                <?php } ?>

                <!--            <li class="<?php echo setRoleForAnchorTag('analytics', 2); ?> hover-collapse" div-target="analyticsItems">
                <a data-bs-toggle="collapse" href="#analyticsItems"  onmouseover="getDashboardList($(this), event,'viz'); return true;">
                    <i class="tim-icons icon-chart-bar-32"></i>
                    <p> &nbsp; </p>
                </a>
                <div class="collapse menu-l1-view" id="analyticsItems">
                    <p class="menu-new-dch"> Analytics </p>
                    <ul class="nav" id="analyticsList">

                    </ul>
                </div>
            </li>-->

                <li class="<?php echo setRoleForAnchorTag('management', 2); ?> hover-collapse" div-target="managementMenu">
                    <a data-bs-toggle="collapse" href="#managementMenu">
                        <i class="tim-icons icon-settings-gear-63"></i>
                        <p> &nbsp; </p>
                    </a>
                    <div class="collapse menu-l1-view" id="managementMenu">
                        <p class="menu-new-dch"> Management </p>
                        <ul class="nav">
                            <?php if (!getenv('HIDE_MENU_FEDERATE_MBAG_DTAG')) { ?>
                                <li class="<?php echo setRoleForAnchorTag('census', 2); ?> <?php echo setActiveClass('windowtype', 'census'); ?>">
                                    <a data-qa="census" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/census/" ?>">
                                        <span class="sidebar-normal"> Census </span>
                                    </a>
                                </li>
                                <li class="<?php echo setRoleForAnchorTag('device', 2); ?> <?php echo setActiveClass('windowtype', 'device'); ?>">
                                    <a data-qa="groups" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/device/" ?>">
                                        <span class="sidebar-normal"> Groups </span>
                                    </a>
                                    <!--<div class="collapse menu-l2-view" id="groupsItems">
                                <p class="menu-new-dch">Groups</p>
                                <ul class="nav">
                                    <li class="<?php ?><?php ?>">
                                        <a href="../customer/device.php" style="margin-left: 10px !important;">
                                            <span class="sidebar-normal"> Manual Groups </span>
                                        </a>
                                    </li>
                                    <li class="<?php ?> <?php ?>">
                                        <a href="../customer/advgrp.php" style="margin-left: 10px !important;">
                                            <span class="sidebar-normal"> Dynamic Groups </span>
                                        </a>
                                    </li>
                                </ul>
                            </div>-->
                                </li>

                                <li class="<?php echo setRoleForAnchorTag('automation', 2); ?> hover-collapse" div-target="automationItems">
                                    <a data-bs-toggle="collapse" aria-expanded="false" href="#automationItems">
                                        <span class="sidebar-normal"> Automation </span>
                                    </a>
                                    <div class="collapse menu-l2-view" id="automationItems">
                                        <p class="menu-new-dch"> Automation </p>
                                        <ul class="nav">
                                            <li class="<?php echo setRoleForAnchorTag('services', 2); ?><?php echo setActiveClass('windowtype', 'services'); ?>">
                                                <a data-qa="darts" style="margin-left: 10px !important;" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/services/" ?>">
                                                    <span class="sidebar-normal"> Darts </span>
                                                </a>
                                            </li>
                                            <li class="<?php echo setRoleForAnchorTag('profilewizard', 2); ?> <?php echo setActiveClass('windowtype', 'profilewizard'); ?>">
                                                <a data-qa="profiles" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/profiles/" ?>" style="margin-left: 10px !important;">
                                                    <span class="sidebar-normal"> Profiles </span>
                                                </a>
                                            </li>
                                            <li class="<?php echo setRoleForAnchorTag('adpassword', 2); ?> <?php echo setActiveClass('windowtype', 'adpassword'); ?>">
                                                <a data-qa="passwordReset" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/adreset/" ?>" style=" margin-left: 10px !important;">
                                                    <span class="sidebar-normal"> Password Reset </span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>

                                <li class="<?php echo setRoleForAnchorTag('services', 2); ?> hover-collapse" div-target="servicesItems">
                                    <a data-bs-toggle="collapse" aria-expanded="false" href="#servicesItems">
                                        <span class="sidebar-normal"> Services </span>
                                    </a>
                                    <div class="collapse menu-l2-view" id="servicesItems">
                                        <p class="menu-new-dch"> Services </p>
                                        <ul class="nav">
                                            <li class="<?php echo setRoleForAnchorTag('softwaredistribution', 2); ?><?php echo setActiveClass('windowtype', 'softwaredistribution'); ?>">
                                                <a data-qa="softwareDistribution" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/softdist/" ?>" style="margin-left: 10px !important;">
                                                    <span class="sidebar-normal"> Software Distribution </span>
                                                </a>
                                            </li>
                                            <li class="<?php echo setRoleForAnchorTag('patchmanagementservice', 2); ?> <?php echo setActiveClass('windowtype', 'patchmanagement'); ?>">
                                                <a data-qa="patchManagementConfig" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/mum_config/" ?>" style="margin-left: 10px !important;">
                                                    <span class="sidebar-normal"> Patch Management </span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                                <li class="<?php echo setRoleForAnchorTag('watcherconfig', 2); ?> <?php echo setActiveClass('windowtype', 'elastwatcher'); ?>">
                                    <a data-qa="alertConfiguration" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/watcher/" ?>">
                                        <span class="sidebar-normal"> Alert Configuration </span>
                                    </a>
                                </li>

                            <?php } ?>

                            <li class="<?php echo setRoleForAnchorTag('access', 2); ?> hover-collapse" div-target="configurecollapse">
                                <a data-bs-toggle="collapse" aria-expanded="false" href="#configurecollapse">
                                    <span class="sidebar-normal"> Access </span>
                                </a>
                                <div class="collapse menu-l2-view" id="configurecollapse">
                                    <p class="menu-new-dch"> Access </p>
                                    <ul class="nav">
                                        <li class="<?php echo setRoleForAnchorTag('user', 2); ?><?php echo setActiveClass('windowtype', 'user'); ?>">
                                            <a data-qa="users" style="margin-left: 10px !important;" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/customer/" ?>">
                                                <span class="sidebar-normal"> Users </span>
                                            </a>
                                        </li>
                                        <li class="<?php echo setRoleForAnchorTag('role', 2); ?> <?php echo setActiveClass('windowtype', 'role'); ?>">
                                            <a data-qa="accessRight" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/role/" ?>" style="margin-left: 10px !important;">
                                                <span class="sidebar-normal"> Access Right & permissions </span>
                                            </a>
                                        </li>
                                        <li class="<?php echo setRoleForAnchorTag('role', 2); ?><?php echo setActiveClass('windowtype', 'role'); ?>">
                                            <a data-qa="userApproval" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/userApproval/" ?>" style="margin-left: 10px !important;">
                                                <span class="sidebar-normal"> User Approval </span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <?php if (!getenv('HIDE_MENU_FEDERATE_MBAG_DTAG')) { ?>
                                <li class="<?php echo setRoleForAnchorTag('autoupdate', 2); ?> <?php echo setActiveClass('windowtype', 'autoupdate'); ?>">
                                    <a data-qa="nanohealUpdate" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/softwareupdate/" ?>">
                                        <span class="sidebar-normal"> Nanoheal Update </span>
                                    </a>
                                </li>
                            <?php } ?>

                            <!-- <li class="<?php echo setRoleForAnchorTag('branding', 2); ?> <?php echo setActiveClass('windowtype', 'branding'); ?>">
                            <a href="../branding">
                                <span class="sidebar-normal"> Branding </span>
                            </a>
                        </li> -->
                        </ul>
                    </div>
                </li>

                <li class="<?php echo setRoleForAnchorTag('integrations', 2); ?> hover-collapse" div-target="securityMenu">
                    <a data-bs-toggle="collapse" href="#securityMenu">
                        <i class="tim-icons icon-molecule-40"></i>
                        <p> &nbsp; </p>
                    </a>
                    <div class="collapse menu-l1-view" id="securityMenu">
                        <p class="menu-new-dch"> Integrations </p>
                        <!-- menu-new-dch -->
                        <ul class="nav">
                            <?php if (!getenv('HIDE_MENU_FEDERATE_MBAG_DTAG')) { ?>
                                <li class="<?php echo setRoleForAnchorTag('adpreset', 2); ?> <?php echo setActiveClass('windowtype', 'adpreset'); ?>">
                                    <a data-qa="activeDirectory" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/adreset/" ?>">
                                        <span class="sidebar-normal"> Active Directory </span>
                                    </a>
                                </li>
                            <?php } ?>
                            <li class="<?php echo setRoleForAnchorTag('sso', 2); ?> <?php echo setActiveClass('windowtype', 'sso'); ?>">
                                <a data-qa="singleSignOn" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/sso/" ?>">
                                    <span class="sidebar-normal"> Single Sign On </span>
                                </a>
                            </li>
                            <?php if (!getenv('HIDE_MENU_FEDERATE_MBAG_DTAG')) { ?>
                                <li class="<?php echo setRoleForAnchorTag('ticketingwiz', 2); ?> <?php echo setActiveClass('windowtype', 'ticketingwiz'); ?>">
                                    <a data-qa="serviceNow" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/ticketingwizard/" ?>">
                                        <span class="sidebar-normal"> Service Now </span>
                                    </a>
                                </li>
                            <?php } ?>
                            <!--                        <li class="<?php echo setRoleForAnchorTag('webhook', 2); ?> <?php echo setActiveClass('windowtype', 'webhook'); ?>">
                            <a href="javascript:void(0);">
                                <span class="sidebar-normal"> Webhooks </span>
                            </a>
                        </li>-->
                        </ul>
                    </div>
                </li>
                <?php if (!getenv('HIDE_MENU_FEDERATE_MBAG_DTAG')) { ?>
                    <li class="<?php echo setRoleForAnchorTag('deployments', 2); ?> hover-collapse" div-target="deploymentItems">
                        <a data-bs-toggle="collapse" href="#deploymentItems">
                            <i class="tim-icons icon-world"></i>
                            <p> &nbsp; </p>
                        </a>
                        <div class="collapse menu-l1-view" id="deploymentItems">
                            <p class="menu-new-dch"> Deployments </p>
                            <ul class="nav">
                                <li class="<?php echo setRoleForAnchorTag('site', 2); ?> <?php echo setActiveClass('windowtype', 'site'); ?>">
                                    <a data-qa="sites" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/site/" ?>">
                                        <span class="sidebar-normal"> Sites </span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                <?php } ?>
                <li class="<?php echo setRoleForAnchorTag('security', 2); ?> hover-collapse" div-target="securityItems">
                    <a data-bs-toggle="collapse" href="#securityItems">
                        <i class="tim-icons icon-lock-circle"></i>
                        <p> &nbsp; </p>
                    </a>
                    <div class="collapse menu-l1-view" id="securityItems">
                        <p class="menu-new-dch"> Security </p>
                        <ul class="nav">
                            <li class="<?php echo setRoleForAnchorTag('audit', 2); ?> hover-collapse" div-target="auditItems">
                                <a data-bs-toggle="collapse" aria-expanded="false" href="#auditItems">
                                    <span class="sidebar-normal"> Audit </span>
                                </a>
                                <div class="collapse menu-l2-view" id="auditItems">
                                    <p class="menu-new-dch"> Audit </p>
                                    <ul class="nav">
                                        <li class="<?php echo setRoleForAnchorTag('useraudit', 2); ?><?php echo setActiveClass('windowtype', 'audit'); ?>">
                                            <a data-qa="userActivityAudit" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/auditlog/" ?>" style="margin-left: 10px !important;">
                                                <span class="sidebar-normal"> User Activity Audit </span>
                                            </a>
                                        </li>
                                        <?php if (!getenv('HIDE_MENU_FEDERATE_MBAG_DTAG')) { ?>
                                            <li class="<?php echo setRoleForAnchorTag('dartaudit', 2); ?> <?php echo setActiveClass('windowtype', 'dartaudit'); ?>">
                                                <a data-qa="dartAudit" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/audit/" ?>" style="margin-left: 10px !important;">
                                                    <span class="sidebar-normal"> Dart Audit </span>
                                                </a>
                                            </li>
                                            <li class="<?php echo setRoleForAnchorTag('autoaudit', 2); ?> <?php echo setActiveClass('windowtype', 'autoaudit'); ?>">
                                                <a data-qa="automationAudit" href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/autolog/" ?>" style="margin-left: 10px !important;">
                                                    <span class="sidebar-normal"> Automation Audit </span>
                                                </a>
                                            </li>
                                        <?php } ?>
                                        <li class="<?php echo setRoleForAnchorTag('loginaudit', 2); ?> <?php echo setActiveClass('windowtype', 'loginaudit'); ?>">
                                            <a href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/loginaudit/" ?>" style="margin-left: 10px !important;">
                                                <span class="sidebar-normal"> Login Audit </span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <!--                        <li class="<?php echo setRoleForAnchorTag('gdprcomp', 2); ?> <?php echo setActiveClass('windowtype', 'gdprcomp'); ?>">
                            <a href="../gdpr">
                                <span class="sidebar-normal"> GDPR Compliance </span>
                            </a>
                        </li>-->
                            <!--<li class="<?php echo setRoleForAnchorTag('cofigsmtp', 2); ?> <?php echo setActiveClass('windowtype', 'cofigsmtp'); ?>">
                            <a href="../smtp">
                                <span class="sidebar-normal"> SMTP Config </span>
                            </a>
                        </li>-->
                        </ul>
                    </div>
                </li>

                <?php require_once('profile-bar.php') ?>

            </ul>
        <?php
        }
        ?>
    </div>
</div>
