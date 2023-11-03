<div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body" id="main_card">
                    <div class="toolbar">
<!--                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>    
                            </div>
                        </div>-->

                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <!--<a class="dropdown-item <?php ?>" id="detailViewAudit" onclick="openVizSlider()">Add Visualization</a>-->
                                    <a class="dropdown-item <?php ?>" id="AddVisual" onclick="openUsersVisualSlider();">Add Insights</a>
                                    <a class="dropdown-item <?php echo setRoleForAnchorTag('backbtn', 2); ?>" id="deleteVisual" onclick="DeleteDashboard('viz');">Delete Insights</a>
                                    <!--<a class="dropdown-item <?php echo setRoleForAnchorTag('addUserDash', 2); ?>" id="EditDashboard" onclick="openUsersDashboardSlider();">Add Dashboard</a>-->
                                    <a class="dropdown-item <?php echo setRoleForAnchorTag('backbtn', 2); ?>" id="backWindowbtn2" onclick="BackToMain();">Back</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="VizId" value="">
                    <input type="hidden" id="VizName" value="">
                    <input type="hidden" id="VizType" value="">
                    <div id="PreviewVizIframe" style="display:none">
                        <div class="modal-header">
                            <h4 class="modal-title" id="vizTitle"></h4>
                        </div>

                        <iframe id="Iframe2" class="kibanahome">
                        </iframe>
                    </div>
                    <div id="PreviewVizTable" style="display:none">
                        <table class="nhl-datatable table table-striped" id="vizViewList">
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

<!--Add Vizualization-->
<div id="rsc-viz-dashboard"class="rightSidenav" data-class="md-6">
    <div class="rsc-loader hide"></div>
    <div class="card-title">
        <h4>Add Vizualization</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="rsc-viz-dashboard">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="AddDashboard('viz')">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <div class="form-group has-label">
                <label for="vizName">
                    Visualization Name
                </label>
                <input class="form-control" type="text" id="VizualName">
            </div>

            <div class="form-group has-label">
                <label for="vislist">
                    Select the user who will have the access for the Visualization
                </label>
                <select data-live-search="true" class="selectpicker" data-style="btn btn-info" multiple title="Select Users" data-size="4" id="VizList">

                </select>
            </div>
            
        </div>
    </div>
</div>

<!--Add Users Dashboard-->
<div id="rsc-addUser-visual"class="rightSidenav" data-class="md-6">
    <div class="rsc-loader hide"></div>
    <div class="card-title">
        <h4>Add Insight</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="rsc-addUser-visual">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="AddUsersVisual()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <div class="form-group has-label">
                <label for="visualName2">
                    Insight Name
                </label>
                <input class="form-control" type="text" id="visualName2" />
            </div>
            
            <div class="form-group has-label">
                <label for="globallabel2">
                    Replicate an existing Insight?
                </label>

                <div class="form-check form-check-radio replicate">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" id="VizreplicateYes" checked value="1">
                        <span class="form-check-sign" style="margin-left: -7px;" >Yes</span>
                    </label>

                    <label class="form-check-label"  style="margin-left: 37px;">
                        <input class="form-check-input" type="radio" id="VizreplicateNo" value="0">
                        <span class="form-check-sign" style="margin-left: -7px;">No</span>
                    </label>
                </div>
            </div>
            
            <div class="form-group has-label" id="replicateVizDiv" style="display:none">
                <label for="globallabel2">
                    Select the Insight you want to replicate
                </label>
                <select data-live-search="true" class="selectpicker" data-style="btn btn-info" title="Select the Insight" data-size="4" id="repVizList">

                </select>
            </div>
            
            <div class="form-group has-label" id="defaultVizDiv" style="display:none">
                <label for="globallabel2">
                    Select the Insight type you want to use
                </label>
                <select data-live-search="true" class="selectpicker" data-style="btn btn-info" title="Select the Insight type" data-size="4" id="defVizList">

                </select>
            </div>
            
            <div class="form-group has-label">
                <label for="globallabel2">
                    Will this Insight be visible to all?
                </label>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" id="VizViewAllYes" checked value="1">
                        <span class="form-check-sign" style="margin-left: -7px;" >Yes</span>
                    </label>

                    <label class="form-check-label"  style="margin-left: 37px;">
                        <input class="form-check-input" type="radio" id="VizViewAllNo" value="0">
                        <span class="form-check-sign" style="margin-left: -7px;">No</span>
                    </label>
                </div>
            </div>
            
            
            <div class="form-group has-label"style="display:none" id="VizViewAllUsersDiv">
                <select data-live-search="true" class="selectpicker" data-style="btn btn-info" multiple title="Select the user who will have the access for the Insight" data-size="4" id="VizusersList2">

                </select>
            </div>
            
            <div class="form-group has-label">
                <label for="homelabel2">
                    Will this Insight be your home screen
                </label>

                <div class="form-check form-check-radio home2">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" id="Vizhomeyes2" value="1">
                        <span class="form-check-sign" style="margin-left: -7px;">Yes</span>
                         
                    </label>

                    <label class="form-check-label" style="margin-left: 37px;">
                        <input class="form-check-input" type="radio" checked id="Vizhomeno2" value="0">
                        <span class="form-check-sign" style="margin-left: -7px;">No</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<!--Edit Users Dashboard-->
<div id="rsc-editUser-visual"class="rightSidenav" data-class="md-6">
    <div class="rsc-loader hide"></div>
    <div class="card-title">
        <h4>Edit Insight</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="rsc-editUser-visual">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="EditUsersVisual()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <div class="form-group has-label">
                <label for="editvisualName2">
                    Insight Name
                </label>
                <input class="form-control" type="text" id="editvisualName2" />
            </div>
            
            <div class="form-group has-label">
                <label for="globallabel2">
                    Will this Insight be visible to all?
                </label>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" id="editVizViewAllYes" checked value="1">
                        <span class="form-check-sign" style="margin-left: -7px;" >Yes</span>
                    </label>

                    <label class="form-check-label"  style="margin-left: 37px;">
                        <input class="form-check-input" type="radio" id="editVizViewAllNo" value="0">
                        <span class="form-check-sign" style="margin-left: -7px;">No</span>
                    </label>
                </div>
            </div>
            
            
            <div class="form-group has-label"style="display:none" id="editVizViewAllUsersDiv">
                <select data-live-search="true" class="selectpicker" data-style="btn btn-info" multiple title="Select the user who will have the access for the Insight" data-size="4" id="editVizusersList2">

                </select>
            </div>
            
            <div class="form-group has-label">
                <label for="homelabel2">
                    Will this Insight be your home screen
                </label>

                <div class="form-check form-check-radio home2">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" id="editVizhomeyes2" value="1">
                        <span class="form-check-sign" style="margin-left: -7px;">Yes</span>
                         
                    </label>

                    <label class="form-check-label" style="margin-left: 37px;">
                        <input class="form-check-input" type="radio" checked id="editVizhomeno2" value="0">
                        <span class="form-check-sign" style="margin-left: -7px;">No</span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal iframeModal" id="iframeModal2">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="dashboardTitle2"></h4>
                <button type="button" onclick="VisualIframeOnClose2();" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div>
                    <iframe id="Iframe2" class="kibanahome">
                    </iframe>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Ok</button>
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