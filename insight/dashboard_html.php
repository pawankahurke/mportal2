<div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body" id="main_card">
                    <div class="toolbar">
                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>    
                            </div>
                        </div>

                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item <?php echo setRoleForAnchorTag('adddashboard', 2); ?>" id="detailViewAudit" onclick="openCreateDashboardSlider()">Add Dashboard</a>
                                    <a class="dropdown-item <?php echo setRoleForAnchorTag('previewdashboard', 2); ?>" id="previewDashboard" onclick="previewDashboardById();">Preview</a>
                                    <a class="dropdown-item <?php echo setRoleForAnchorTag('addkibanadashboard', 2); ?>" id="addKibanaDashboard" onclick="addKibanaDashboard();">Add Kibana Dashboard</a>
                                    <a class="dropdown-item rightslide-container-hand <?php echo setRoleForAnchorTag('deletedashboard', 2); ?>" id="deleteDashboard"  onclick="deleteDashboard()">Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="dashId" value="">
                    <input type="hidden" id="dashName" value="">
                    <input type="hidden" id="dashType" value="">
                    <table class="nhl-datatable table table-striped" id="dashboardList">
                        <thead>
                            <tr>
                                <th>Dashboard</th>
                                <th>Default</th>
                                <th>Global</th>
                                <th>Type</th>
                                <th>Username</th>
                                <th>Created Date And Time</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>

<div id="rsc-add-dashboard"class="rightSidenav" data-class="md-6">
    <div class="rsc-loader hide"></div>
    <div class="card-title">
        <h4>Add Dashboard</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="rsc-add-dashboard">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="previewDashboard()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <div class="form-group has-label">
                <label for="dashboardName">
                    Dashboard Name
                </label>
                <input class="form-control" type="text" id="dashboardName" />
            </div>

            <div class="form-group has-label">
                <label for="vislist">
                    Visualization List
                </label>
                <select data-live-search="true" class="selectpicker" data-style="btn btn-info" multiple title="Choose Visualisation" data-size="4" id="visList">

                </select>
            </div>
            <div class="card-body carRadio">
                <div class="form-group has-label">
                    <label for="dashin">
                        Would this Dashboard be Global?
                    </label>
                </div>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="globalCheck" id="globalCheck" class="globalCheck">
                        <span class="form-check-sign"></span>
                        Yes
                    </label>

                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="globalCheck" id="globalCheckNo" class="globalCheck" checked="checked">
                        <span class="form-check-sign"></span>
                        No
                    </label>
                </div>
            </div>

            <div class="card-body carRadio">
                <div class="form-group has-label">
                    <label for="dashin">
                        Would this be Dashboard/Insight?
                    </label>
                </div>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="exampleRadios1" id="dashboard" checked="checked">
                        <span class="form-check-sign"></span>
                        Dashboard
                    </label>

                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="exampleRadios1" id="insight">
                        <span class="form-check-sign"></span>
                        Insight
                    </label>
                </div>
            </div>
            <div class="card-body carRadio defaultCheck" style="display:none;"> 
                <div class="form-group has-label">
                    <label for="dashin">
                        Do you want it to be default?
                    </label>
                </div>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="defaultCheck" id="defaultCheck" >
                        <span class="form-check-sign"></span>
                        Yes
                    </label>

                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="defaultCheck" id="defaultCheckNo" checked="checked">
                        <span class="form-check-sign"></span>
                        No
                    </label>
                </div>
            </div>
            <!--            <div class="card-body carRadio evironmentGlobal">
                            <div class="form-group has-label">
                                <label for="dashin">
                                    Do you want it to be environment global?
                                </label>
                            </div>
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" name="evironmentGlobal" id="evironmentGlobal">
                                    <span class="form-check-sign"></span>
                                    Yes
                                </label>
                                
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" name="evironmentGlobal" id="evironmentGlobalNo">
                                    <span class="form-check-sign"></span>
                                    No
                                </label>
                            </div>
                        </div>-->
        </div>
    </div>
</div>


<!-- Edit Visualisation -->
<div id="rsc-edit-dashboard" class="rightSidenav" data-class="md-6">
    <div class="rsc-loader hide"></div>
    <div class="card-title">
        <h4>Edit Dashboard</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="rsc-edit-dashboard">&times;</a>
    </div>

    <div class="btnGroup" style="display: block;" id="editOption">
        <div class="icon-circle">
            <div class="toolTip editOption" data-target-container-only="true">
                <i class="tim-icons icon-pencil"></i>
                <span class="tooltiptext">Edit</span>
            </div>
        </div>
    </div>
    <div class="btnGroup" style="display: none;" id="toggleButton">
        <div class="icon-circle iconTick circleGrey" id="updateVisualisation">
            <div class="toolTip" onclick="updateVizDashboard();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Update Visualisation</span>
            </div>
        </div>

        <div class="icon-circle toggleEdit" id="toggleEdit" data-target-container-only="true">
            <div class="toolTip">
                <i class="tim-icons icon-simple-remove"></i>
                <span class="tooltiptext">Toggle edit mode</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <div class="card">
            <div class="form-group has-label">
                <label for="dashboardName">
                    Dashboard Name
                </label>
                <input class="form-control" type="text" id="editDashboardName" />
            </div>

            <div id ="edit-normaldash" class="form-group has-label">
                <label for="vislist">
                    Visualization List
                </label>
                <select data-live-search="true" class="selectpicker" data-style="btn btn-info" multiple title="Choose Visualisation" data-size="4" id="editVisList">

                </select>
            </div>
            <div id = "edit-kibana" class="form-group has-label" style="display:none">
                <label for="dashboardName">
                    Dashboard Id
                </label>
                <input class="form-control" type="text" id="kibana-dashboard-id2" />
            </div>
            <div class="card-body carRadio">
                <div class="form-group has-label">
                    <label for="dashin">
                        Would this Visualization be Global?
                    </label>
                </div>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="editglobalCheck" id="editglobalCheck" class="globalCheck" checked="checked">
                        <span class="form-check-sign"></span>
                        Yes
                    </label>

                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="editglobalCheck" id="editglobalCheckNo" class="globalCheck" >
                        <span class="form-check-sign"></span>
                        No
                    </label>
                </div>
            </div>
            <div class="card-body carRadio">
                <div class="form-group has-label">
                    <label for="dashin">
                        Would this be Dashboard/Insight?
                    </label>
                </div>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="editDIRadios" id="editDashboard">
                        <span class="form-check-sign"></span>
                        Dashboard
                    </label>

                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="editDIRadios" id="editInsight">
                        <span class="form-check-sign"></span>
                        Insight
                    </label>
                </div>
            </div>
            <div class="card-body carRadio defaultCheck" style="display:none;"> 
                <div class="form-group has-label">
                    <label for="dashin">
                        Do you want it to be default?
                    </label>
                </div>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="editDefaultCheck" id="editDefaultCheck">
                        <span class="form-check-sign"></span>
                        Yes
                    </label>

                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="editDefaultCheck" id="editDefaultCheckNo">
                        <span class="form-check-sign"></span>
                        No
                    </label>
                </div>
            </div>
            <div class="card-body carRadio evironmentGlobal">
                <div class="form-group has-label">
                    <label for="dashin">
                        Do you want it to be environment global?
                    </label>
                </div>
                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="editEnvironmentGlobal" id="editEvironmentGlobal">
                        <span class="form-check-sign"></span>
                        Yes
                    </label>

                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="editEnvironmentGlobal" id="editEvironmentGlobalNo">
                        <span class="form-check-sign"></span>
                        No
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal iframeModal" id="iframeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="dashboardTitle"></h4>
                <button type="button" onclick="VisualIframeOnClose();" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div>
                    <iframe id="Iframe" class="kibanahome" data-src="Dashboard/insight/dashboard_html.php">
                    </iframe>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Ok</button>
            </div>
        </div>
    </div>
</div> 

<!-- Add Kibana Dashboard::Start  -->
<div id="rsc-add-kibana-dashboard" class="rightSidenav" data-class="md-6">
    <div class="rsc-loader hide"></div>
    <div class="card-title">
        <h4>Add Kibana Dashboard</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="rsc-add-kibana-dashboard">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="createKibanaDashboard();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <div class="form-group has-label">
                <label for="vislist">
                    Dashboard Name
                </label>
                <input class="form-control" type="text" id="kibana-dashboard-name" />
            </div>
            <div class="form-group has-label">
                <label for="dashboardName">
                    Dashboard Id
                </label>
                <input class="form-control" type="text" id="kibana-dashboard-id" />
            </div>
            <div class="card-body carRadio">
                <div class="form-group has-label">
                    <label for="dashin">
                        Would this Dashboard be Global?
                    </label>
                </div>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="globalCheck" id="globalCheck2" class="globalCheck">
                        <span class="form-check-sign"></span>
                        Yes
                    </label>

                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="globalCheck" id="globalCheckNo2" class="globalCheck" checked="checked">
                        <span class="form-check-sign"></span>
                        No
                    </label>
                </div>
            </div>

            <div class="card-body carRadio">
                <div class="form-group has-label">
                    <label for="dashin">
                        Would this be Dashboard/Insight?
                    </label>
                </div>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="exampleRadios1" id="dashboard2" checked="checked">
                        <span class="form-check-sign"></span>
                        Dashboard
                    </label>

                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="exampleRadios1" id="insight2">
                        <span class="form-check-sign"></span>
                        Insight
                    </label>
                </div>
            </div>

            <div class="card-body carRadio defaultCheck"> 
                <div class="form-group has-label">
                    <label for="dashin">
                        Do you want it to be default?
                    </label>
                </div>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="defaultCheck" id="defaultCheck2" checked="checked">
                        <span class="form-check-sign"></span>
                        Yes
                    </label>

                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="defaultCheck" id="defaultCheckNo2">
                        <span class="form-check-sign"></span>
                        No
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Add Kibana Dashboard::End  -->

<style>
    .dropdown-menu.inner li.hidden{
        display:none;
    }

    .dropdown-menu.inner.active li, .dropdown-menu.inner li.active{
        display:block;
    }

    .iframeModal .modal-content {
        height: calc(100vh - 28px) !important;
    }
</style>