<div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body" id="main_card">
                    <div class="toolbar">
<!--                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>    
                            </div>
                        </div>-->

                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item <?php echo setRoleForAnchorTag('addUserDash', 2); ?>" id="AddDashboard" onclick="openUsersDashboardSlider();">Add Dashboard</a>
                                    <a class="dropdown-item <?php echo setRoleForAnchorTag('backbtn', 2); ?>" id="deleteDashboard" onclick="DeleteDashboard('dash');">Delete Dashboard</a>
                                    <a class="dropdown-item <?php echo setRoleForAnchorTag('backbtn', 2); ?>" id="backWindowbtn" onclick="BackToMain();">Back</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="dashId" value="">
                    <input type="hidden" id="dashName" value="">
                    <input type="hidden" id="dashType" value="">
                    <div id="PreviewIframe" style="display:none">
                        <div class="modal-header">
                            <h4 class="modal-title" id="dashboardTitle"></h4>
                        </div>

                        <iframe id="Iframe" class="kibanahome" data-src="Dashboard/dashboard/dashboard_html.php">
                        </iframe>
                    </div>
                    <div id="PreviewDashTable" style="display:none">
                       <table class="nhl-datatable table table-striped" id="dashboardViewList">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Added By</th>
                                <th>Added On</th>
                                <th>Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        </table> 
                    </div>
                    
                </div>
                <!-- end content
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>

<!--Add Dashboard-->
<div id="rsc-add-dashboard"class="rightSidenav" data-class="md-6">
    <div class="rsc-loader hide"></div>
    <div class="card-title">
        <h4>Add Dashboard</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="rsc-add-dashboard">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="AddDashboard('dash')">
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
                <input class="form-control" type="text" id="DashboardName" />
            </div>

            <div class="form-group has-label">
                <label for="vislist">
                    Select the user who will have the access for the dashboard
                </label>
                <select data-live-search="true" class="selectpicker" data-style="btn btn-info" multiple title="Select Users" data-size="4" id="usersList">

                </select>
            </div>

            
            <div class="form-group has-label">
                <label for="globallabel">
                    Do you want the Dashboard to be global?
                </label>

                <div class="form-check form-check-radio global">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" id="globalyes" checked name="exampleRadios" value="1">
                        <span class="form-check-sign" style="margin-left: -7px;" >Yes</span>
                    </label>

                    <label class="form-check-label"  style="margin-left: 37px;">
                        <input class="form-check-input" type="radio" id="globalno" name="exampleRadios" value="0">
                        <span class="form-check-sign" style="margin-left: -7px;">No</span>
                    </label>
                </div>
            </div>
            <div>&nbsp;</div>
            <div class="form-group has-label">
                <label for="homelabel">
                    Do you want the Dashboard to be default?
                </label>

                <div class="form-check form-check-radio home">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" id="homeyes" value="1">
                        <span class="form-check-sign" style="margin-left: -7px;">Yes</span>
                         
                    </label>

                    <label class="form-check-label" style="margin-left: 37px;">
                        <input class="form-check-input" type="radio" checked id="homeno" value="0">
                        <span class="form-check-sign" style="margin-left: -7px;">No</span>
                    </label>
                </div>
            </div>
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


<!--Add Users Dashboard-->
<div id="rsc-addUser-dashboard"class="rightSidenav" data-class="md-6">
    <div class="rsc-loader hide"></div>
    <div class="card-title">
        <h4>Add Dashboard</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="rsc-addUser-dashboard">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="AddUsersDashboard()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <div class="form-group has-label">
                <label for="dashboardName2">
                    Dashboard Name
                </label>
                <input class="form-control" type="text" id="DashboardName2" />
            </div>
            
            <div class="form-group has-label">
                <label for="globallabel2">
                    Replicate an existing dashboard?
                </label>

                <div class="form-check form-check-radio replicate">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" id="replicateYes" checked name="exampleRadios" value="1">
                        <span class="form-check-sign" style="margin-left: -7px;" >Yes</span>
                    </label>

                    <label class="form-check-label"  style="margin-left: 37px;">
                        <input class="form-check-input" type="radio" id="replicateNo" name="exampleRadios" value="0">
                        <span class="form-check-sign" style="margin-left: -7px;">No</span>
                    </label>
                </div>
            </div>
            
            <div class="form-group has-label" id="replicateDiv" style="display:none">
                <label for="globallabel2">
                    Select the Dashboard you want to replicate
                </label>
                <select data-live-search="true" class="selectpicker" data-style="btn btn-info" title="Select the Dashboard" data-size="4" id="repDashList">

                </select>
            </div>
            
            <div class="form-group has-label" id="DefaultDashDiv" style="display:none">
                 <label for="globallabel2">
                    Select the Dashboard Type you want to use
                </label>
                <select data-live-search="true" class="selectpicker" data-style="btn btn-info" title="Select the Dashboard Type" data-size="4" id="defDashList">

                </select>
            </div>
            
            <div class="form-group has-label">
                <label for="globallabel2">
                    Will this dashboard be visible to all?
                </label>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" id="ViewAllYes" checked value="1">
                        <span class="form-check-sign" style="margin-left: -7px;" >Yes</span>
                    </label>

                    <label class="form-check-label"  style="margin-left: 37px;">
                        <input class="form-check-input" type="radio" id="ViewAllNo" value="0">
                        <span class="form-check-sign" style="margin-left: -7px;">No</span>
                    </label>
                </div>
            </div>
            
            
            <div class="form-group has-label"style="display:none" id="ViewAllUsersDiv">
                <select data-live-search="true" class="selectpicker" data-style="btn btn-info" multiple title="Select the user who will have the access for the dashboard" data-size="4" id="usersList2">

                </select>
            </div>
            
            <div class="form-group has-label">
                <label for="homelabel2">
                    Will this Dashboard be your home screen
                </label>

                <div class="form-check form-check-radio home2">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" id="homeyes2" value="1">
                        <span class="form-check-sign" style="margin-left: -7px;">Yes</span>
                         
                    </label>

                    <label class="form-check-label" style="margin-left: 37px;">
                        <input class="form-check-input" type="radio" checked id="homeno2" value="0">
                        <span class="form-check-sign" style="margin-left: -7px;">No</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<!--Edit Users Dashboard-->
<div id="rsc-editUser-dashboard"class="rightSidenav" data-class="md-6">
    <div class="rsc-loader hide"></div>
    <div class="card-title">
        <h4>Edit Dashboard</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="rsc-editUser-dashboard">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="EditUsersDashboard('dash')">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <div class="form-group has-label">
                <label for="editdashboardName2">
                    Dashboard Name
                </label>
                <input class="form-control" type="text" id="editDashboardName2" />
            </div>
            
<!--            <div class="form-group has-label">
                <label for="globallabel2">
                    Replicate an existing dashboard?
                </label>

                <div class="form-check form-check-radio editreplicate">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" id="editreplicateYes" checked name="exampleRadios" value="1">
                        <span class="form-check-sign" style="margin-left: -7px;" >Yes</span>
                    </label>

                    <label class="form-check-label"  style="margin-left: 37px;">
                        <input class="form-check-input" type="radio" id="editreplicateNo" name="exampleRadios" value="0">
                        <span class="form-check-sign" style="margin-left: -7px;">No</span>
                    </label>
                </div>
            </div>-->
            
<!--            <div class="form-group has-label" id="editreplicateDiv" style="display:none">
                <select data-live-search="true" class="selectpicker" data-style="btn btn-info" title="Select the Dashboard you want to replicate" data-size="4" id="editrepDashList">

                </select>
            </div>-->
            
            <div class="form-group has-label">
                <label for="editgloballabel2">
                    Will this dashboard be visible to all?
                </label>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" id="editViewAllYes" value="1">
                        <span class="form-check-sign" style="margin-left: -7px;" >Yes</span>
                    </label>

                    <label class="form-check-label"  style="margin-left: 37px;">
                        <input class="form-check-input" type="radio" id="editViewAllNo" value="0">
                        <span class="form-check-sign" style="margin-left: -7px;">No</span>
                    </label>
                </div>
            </div>
            
            
            <div class="form-group has-label"style="display:none" id="editViewAllUsersDiv">
                <select data-live-search="true" class="selectpicker" data-style="btn btn-info" multiple title="Select the user who will have the access for the dashboard" data-size="4" id="editusersList2">

                </select>
            </div>
            
            <div class="form-group has-label">
                <label for="edithomelabel2">
                    Will this Dashboard be your home screen
                </label>

                <div class="form-check form-check-radio home2">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" id="edithomeyes2" value="1">
                        <span class="form-check-sign" style="margin-left: -7px;">Yes</span>
                         
                    </label>

                    <label class="form-check-label" style="margin-left: 37px;">
                        <input class="form-check-input" type="radio"  id="edithomeno2" value="0">
                        <span class="form-check-sign" style="margin-left: -7px;">No</span>
                    </label>
                </div>
            </div>
            
        </div>
    </div>
</div>

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