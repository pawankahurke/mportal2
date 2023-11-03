<div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <!-- <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                            </div>
                        </div> -->

                        <!-- <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addAdvgrp', 2); ?>"  data-target="advgrp-add-container" id="addNewAdvGroup">Add New Group</a>
                                    <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('editAdvgrp', 2); ?>" onclick="EditAdvGroup()">Edit Group</a>
                                    <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('deleteAdvgrp', 2); ?>" onclick="DeleteAdvGroup()">Delete Group</a>
                                    <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('refreshAdvgrp', 2); ?>"  onclick="RefreshAdvGroup()">Refresh Group</a>
                                </div>
                            </div>
                        </div> -->
                    </div>
                    <input type="hidden" id="groupid">
                    <input type="hidden" id="groupname">
                    <table id="advncdgroupList" class="nhl-datatable table table-striped">
                        <thead>
                            <tr>
                                <th>Group Name</th>
                                <th>Machine Count</th>
                                <th>Created By</th>
                                <th>Created Date Time</th>
                                <th>Modified By</th>
                                <th>Modified Date Time</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!--Add Advanced Group-->
<div id="advgrp-add-container" class="rightSidenav addAdvanceGroup" data-class="md-6">
    <div class="card-title">
        <h4>Add Advance group</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="advgrp-add-container">&times;</a>
    </div>
    <div class="btnGroup">

        <div class="icon-circle">
            <div  class="toolTip" onclick="createAdvanceGrp();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <div class="card-body">
                <div class="form-group has-label">
                    <label for="advgname">
                        Group Name
                    </label>
                    <input class="form-control" type="text" id="advgname" />
                </div>
            </div>

            <div class="card-body">
                <div class="form-group has-label">
                    <label>
                        Assign Group to Users
                    </label>
                    <select class="selectpicker" multiple data-style="btn btn-info" title="Select Group Users" data-size="3" id="groupUsers" name="groupUsers">

                    </select>
                    <span id="add_userLevel-err"></span>
                </div>
            </div>

            <div class="form-group has-label assetfilter">
                <label>
                    Asset Filter
                </label>
                <select data-live-search="true" class="selectpicker" data-style="btn btn-info" title="Select Asset Filter" data-size="3" id="assetFilter" name="assetFilter">

                </select>
                <label>
                    Operator
                </label>
                <select class="selectpicker" data-style="btn btn-info" title="Select Asset Operator" data-size="3" id="assetOperator" name="assetOperator">
                    <option value="1">Equal</option>
                    <option value="2">Not Equal</option>
                    <option value="3">Contains</option>
                </select>

                <label>
                    Value
                </label>
                <input class="form-control" type="text" id="assetVal" />
            </div>

            <div class="card-body">
            <div class="form-group has-label">
                <label>
                    Site
                </label>
                <select class="selectpicker" multiple data-style="btn btn-info" title="Select Site" data-size="3" id="sitelist" name="sitelist">

                </select>
            </div>
            </div>

        </div>
    </div>
</div>

<!--Edit Advanced Group-->
<div id="advgrp-edit-container" class="rightSidenav addAdvanceGroup" data-class="md-6">
    <div class="card-title">
        <h4>Edit Advance group</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="advgrp-edit-container">&times;</a>
    </div>

    <div class="btnGroup" id="toggleButton">
        <div class="icon-circle iconTick" id="editAdvGrpDiv">
            <div class="toolTip" onclick="updateAdvanceGrp();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Update</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <div class="card">
            <div class="card-body">
                <div class="form-group has-label">
                    <label for="advgname">
                        Group Name
                    </label>
                    <input class="form-control" type="text" id="edit-advgname" readonly="" />
                </div>
            </div>

            <div class="card-body">
                <div class="form-group has-label">
                    <label>
                        Assign Group to Users
                    </label>
                    <select class="selectpicker" multiple data-style="btn btn-info" title="Select Group Users" data-size="3" id="edit-groupUsers" name="edit-groupUsers">

                    </select>
                    <span id="add_userLevel-err"></span>
                </div>
            </div>

        </div>
    </div>
</div>

<!--View Group-->
<div id="viewgrp-add-container" class="rightSidenav viewAdvanceGroup" data-class="md-6">
    <div class="card-title">
        <h4>View Advance group</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="viewgrp-add-container">&times;</a>
    </div>

    <div class="form table-responsive white-content">
        <div class="card">
            <table id="versionDetail" class="nhl-datatable table">
                <thead>
                    <tr>
                        <th>Machine</th>
                        <th>Site</th>
                        <th>Last Event</th>
                    </tr>
                </thead>
            </table>
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
</style>
