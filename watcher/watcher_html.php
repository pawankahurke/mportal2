<?php
$userSites = isset($_SESSION['user']['user_sites']) ? $_SESSION['user']['user_sites'] : array();

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?>
<div class="content white-content">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <?php
                    // nhRole::checkRoleForPage('elastwatcher');
                    $res = true; //nhRole::checkModulePrivilege('elastwatcher');
                    if ($res) {
                    ?>
                        <div id="absoLoader" style="width:100%;z-index: 1000;position: absolute;padding-left: 48%;;height:100%;"><img src="../assets/img/nanohealLoader.gif" style="margin-top: 20%;width: 71px;"></div>
                        <div class="toolbar">

                            <input type="hidden" id="alertNotifname" name="alertNotifname" />

                            <table data-qa="tableAlerConf" id="AlertListGrid" class="nhl-datatable table table-striped">
                                <thead>
                                    <tr>
                                        <th id="key0" headers="name">
                                            Name
                                            <i class="fa fa-caret-down cursorPointer direction" id="name1" onclick="addActiveSort('asc', 'name'); fetchAlertList(1,notifSearch='','name', 'asc');sortingIconColor('name')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id="name1" onclick="addActiveSort('desc', 'name'); fetchAlertList(1,notifSearch='','name', 'desc');sortingIconColor('name')" style="font-size:18px"></i>
                                        </th>
                                        <th id="key2" headers="group_include">
                                            Site Name
                                            <i class="fa fa-caret-down cursorPointer direction" id="group_include1" onclick="addActiveSort('asc', 'group_include'); fetchAlertList(1,notifSearch='','group_include', 'asc');sortingIconColor('group_include1')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id="group_include2" onclick="addActiveSort('desc', 'group_include'); fetchAlertList(1,notifSearch='','group_include', 'desc');sortingIconColor('group_include2')" style="font-size:18px"></i>
                                        </th>
                                        <th id="key3" headers="ntype">
                                            Type
                                            <i class="fa fa-caret-down cursorPointer direction" id="ntype1" onclick="addActiveSort('asc', 'ntype'); fetchAlertList(1,notifSearch='','ntype', 'asc');sortingIconColor('ntype1')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id="ntype2" onclick="addActiveSort('desc', 'ntype'); fetchAlertList(1,notifSearch='','ntype', 'desc');sortingIconColor('ntype1')" style="font-size:18px"></i>
                                        </th>
                                        <th id="key4" headers="created">
                                            Created Date
                                            <i class="fa fa-caret-down cursorPointer direction" id="created1" onclick="addActiveSort('asc', 'created'); fetchAlertList(1,notifSearch='','created', 'asc');sortingIconColor('created1')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id="created2" onclick="addActiveSort('desc', 'created'); fetchAlertList(1,notifSearch='','created', 'desc');sortingIconColor('created2')" style="font-size:18px"></i>
                                        </th>
                                        <th id="key4" headers="modified">
                                            Modified Date
                                            <i class="fa fa-caret-down cursorPointer direction" id="modified1" onclick="addActiveSort('asc', 'modified'); fetchAlertList(1,notifSearch='','modified', 'asc');sortingIconColor('modified1')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id="modified2" onclick="addActiveSort('desc', 'modified'); fetchAlertList(1,notifSearch='','modified', 'desc');sortingIconColor('modified2')" style="font-size:18px"></i>
                                        </th>
                                        <th id="key4" headers="enabled">
                                            Status
                                            <i class="fa fa-caret-down cursorPointer direction" id="enabled1" onclick="addActiveSort('asc', 'enabled'); fetchAlertList(1,notifSearch='','enabled', 'asc');sortingIconColor('enabled1')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id="enabled2" onclick="addActiveSort('desc', 'enabled'); fetchAlertList(1,notifSearch='','enabled', 'desc');sortingIconColor('enabled2')" style="font-size:18px"></i>
                                        </th>
                                        <!-- <th class="id">Name</th>
                                    <th class="platfrom">Site Name</th>
                                    <th class="type">Type</th>
                                    <th class="created_date">Created Date</th>
                                    <th class="modified_date">Modified Date</th>
                                    <th class="Status">Status</th> -->
                                    </tr>
                                </thead>

                            </table>
                        <?php
                    }
                        ?>
                        <div id="largeDataPagination"></div>
                        </div>
                </div>
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>

<div id="rsc-add-alert" class="rightSidenav" data-file="<?php echo __FILE__; ?>" data-class="md-6">
    <div class="card-title border-bottom">
        <h4 id="createAlertTitle">Create new Notification</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-add-alert">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="create-alert1">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="button col-md-12 text-center slider-feedwrapper" style="display:none;">
        <span id="checkavail" localized="" class="inslider-feed error tm0"></span>
    </div>

</div>

<!--Edit alert pop starts-->
<div id="rsc-edit-alert" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4 id="editAlertTitle">Modify Notification</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-edit-alert">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" data-qa="save-alert-button" id="Modify-alert1">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form id="editAlertForm" action="post" action="editAlert.php" onsubmit="return editAlertEvent($(this), event)">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label for="alertnameEdit">Name</label>
                        <input class="form-control" class="form-check-input" data-qa="alertnameEdit" id="alertnameEdit" name="alertnameEdit" type="text" localized="" placeholder="Empty" data-required="true" readonly="readonly">
                    </div>

                    <div class="form-group has-label compl-box" data-required="true">
                        <span class="error">*</span>
                        <label for="ntypeedit-item">Type</label>
                        <select id="ntypeedit-item" name="ntype-item" class="selectpicker" data-size="5" data-width="100%">
                            <option value="1" selected>Availability</option>
                            <option value="2">Security</option>
                            <option value="3">Resource</option>
                            <option value="4">Maintenance</option>
                            <option value="5">Events of Interest</option>

                        </select>
                    </div>
                    <div class="form-group has-label notif-box" data-required="true">
                        <span class="error">*</span>
                        <label for="index-name">Priority</label>
                        <div>
                            <select id="notifedit-priority" name="notifedit-priority" class="selectpicker" data-size="5" data-width="100%" data-required="true">
                                <option value="1" selected> P1 </option>
                                <option value="2"> P2 </option>
                                <option value="3"> P3 </option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group has-label compl-box dartedit-item-holder" data-required="true">
                        <span class="error">*</span>
                        <label for="dartedit-item2">Select the darts whose events must be considered</label>
                        <select id="dartedit-item2" name="dartedit-item" class="dartedit-item dartedit-item2 selectpicker" data-size="5" data-width="100%" onchange="getDartConfigurationDet($(this).val(),$('#alertNotifId').val());">

                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label>Set Filter</label>
                            <div id="filterTopType">
                            </div>
                        </div>
                    </div>

                    <div class="form-group" style="margin-left: 0">
                        <a onclick="html_AddAnotherFilterCondition(); return false;" class="add_field_button btn btn-link" style="display: inline-block; font-size:12px; color:#fb1864" data-qa="Add_Filter_Condition_btn">+ Add another filter condition</a>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label>Set Measure</label>
                            <div id="editmeasure"> </div>
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <a onclick="html_AddMoreMeasure(); return false;" class="editadd_field_button btn btn-link float-left" style="font-size:12px;color:#fb1864" data-qa="Add_Measure_btn">+ Add More Measure</a>
                </div>

                <div class="form-group row text-center" localized="">
                    <button class="btn btn-info" type="submit" style="display:none">Update</button>
                </div>
            </div>
    </div>
    <input type="hidden" id="alertNotifId" name="alertNotifId" />
    <input type="hidden" id="watcherIdEdit" name="watcherIdEdit" />
    <input type="hidden" id="alertnameEditPost" name="alertnameEditPost" />
    </form>


    <div class="button col-md-12 text-center slider-feedwrapper">
        <span id="checkavailEdit" localized="" class="inslider-feed error tm0"></span>
    </div>
</div>
<input type="hidden" id="watcherId" name="watcherId" />
<div id="rsc-link-alert" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4 id="createAlertTitle">Link Notification</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-link-alert">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="link-alert1">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <div class="form-group has-label">
            <span class="error">*</span>
            <label for="scopeEdit">Site</label>
            <div>
                <select id="alertsiteEdit" name="alertsiteEdit" class="selectpicker" title="Select Sites" data-size="5" data-width="100%" data-required="true">
                    <option value="All">All</option>
                    <?php
                    foreach ($_SESSION['user']['site_list'] as $value) {
                        if ($value !== "") {
                    ?>
                            <option value="<?php echo url::toText($value); ?>"><?php echo $value; ?></option>
                    <?php }
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="form-group has-label compl-box" data-required="true">
            <span class="error">*</span>
            <label for="notification-item">Select the notifications to assign</label>
            <select id="notification-item" name="notification-item[]" class="selectpicker" title="Select Notifications" data-size="5" data-width="100%" multiple="">

            </select>
        </div>

    </div>
</div>
<div id="rsc-export-alert" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4 id="createAlertTitle">Export Notification</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-export-alert">&times;</a>
    </div>
    <!--<div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="export-alert1">
                <i class="tim-icons icon-simple-remove"></i>
                <span class="tooltiptext">Close</span>
            </div>
        </div>
    </div>-->

    <div class="form table-responsive white-content">
        <form id="exportNotify" name="exportNotify" action="exportNotification.php" method="post">
            <div class="form-group has-label">
                <span class="error">*</span>
                <label for="scopeEdit">Site</label>
                <div>
                    <select id="alertexportsite" name="alertexportsite" class="selectpicker" title="Select Sites" data-size="5" data-width="100%" data-required="true" required>
                        <option value="All">All</option>
                        <?php
                        foreach ($_SESSION['user']['site_list'] as $value) {
                            if ($value !== "") {
                        ?>
                                <option value="<?php echo url::toText($value); ?>"><?php echo $value; ?></option>
                        <?php }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-check form-check-radio global">
                <label class="form-check-label">
                    <input class="form-check-input" type="radio" id="notifyList" name="notifyType" value="list" checked>
                    <span class="form-check-sign"></span>
                    <span style="margin-left:-12px;">Notification List </span>
                </label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                <label class="form-check-label">
                    <input class="form-check-input" type="radio" name="notifyType" id="notifyConfig" value="config">
                    <span class="form-check-sign"></span>
                    <span style="margin-left:-12px;">Notification with configurations</span>
                </label>
            </div>
            <p>&nbsp;</p>


            <div class="form-group has-label">
                <button type="submit" class="btn btn-primary btn-sm" data-action="ExportNotifications">Export Notifications</button>
            </div>

        </form>

        <!--<div class="form-group has-label compl-box" data-required="true">
            <span class="error">*</span>
            <label for="notification-item">Select the notifications to assign</label>
            <select id="notification-item" name="notification-item[]" class="selectpicker" title="Select Notifications" data-size="5" data-width="100%" multiple="">

            </select>
        </div>-->

    </div>
</div>
<div id="rsc-import-alert" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4 id="createAlertTitle">Import Notification</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-import-alert">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="import-alert1">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <form id="importNotify" name="importNotify" action="importNotification.php" method="post" enctype="multipart/form-data">

            <label>Please upload the CSV file</label> <br /><br />


            <p id="file_sel" class="txtsm">File selected : <span id="notify_name"></span></p>

            <p>&nbsp;</p>
            <div class="btnBrowser">
                <span class="btn btn-round btn-rose btn-file btn-sm">
                    <span class="fileinput-new">Browse</span>
                    <input type="file" id="notify_file1" name="notify_file" accept=".csv" />
                </span>

                <span class="btn btn-round btn-success btn-sm" id="remove_logo1" style="display:none">
                    <span class="fileinput-new">Remove</span>
                </span>
            </div>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <div id="config"></div>
            <p>&nbsp;</p>
            <!--  <div class="form-group has-label">
                <button type="submit" class="btn btn-primary btn-sm">Import Notifications</button>
            </div>-->

        </form>

        <!--<div class="form-group has-label compl-box" data-required="true">
            <span class="error">*</span>
            <label for="notification-item">Select the notifications to assign</label>
            <select id="notification-item" name="notification-item[]" class="selectpicker" title="Select Notifications" data-size="5" data-width="100%" multiple="">

            </select>
        </div>-->

    </div>
</div>
<div id="rsc-update-sol" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4>Update Solution</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-update-sol">&times;</a>
    </div>

    <div class="btnGroup" id="addNote">
        <div class="icon-circle">
            <div class="toolTip" onclick="updateSol()" data-qa="save-solution">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Submit</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label>
                            Notification Name <em class="error">*</em>
                        </label>
                        <input class="form-control" name="notificationName" type="text" id="notificationName" readonly="" />
                        <!--<span id="advusername-err"></span>-->
                    </div>
                    <div class="form-group has-label">
                        <label>
                            Solution to be pushed<em class="error">*</em>
                        </label>
                        <select class="selectpicker" data-style="btn btn-info" title="Solution to be pushed" data-size="3" id="soln" name="soln" multiple>

                        </select>
                        <span id="add_advroleId-err" style="color:red"></span>
                    </div>
                </div>

            </div>
        </form>
    </div>

</div>


<style>
    .my-num {
        padding: 0px 18px 0px 18px !important;
    }

    #AlertListGrid_filter {
        display: none;
    }
</style>
