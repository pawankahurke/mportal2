<!-- content starts here  -->
<div class="content white-content">

    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <?php
                    //   nhRole::checkRoleForPage('role');
                    $res = true; // nhRole::checkModulePrivilege('role');
                    if ($res) {
                    ?>
                        <!-- loader -->
                        <div id="loader" class="loader" data-qa="loader" style="position: absolute;bottom: 50%;right:50%;">
                            <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                        </div>
                        <!-- <div class="bullDropdown">
                        <div class="dropdown">
                            <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="tim-icons icon-bullet-list-67"></i>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addrole', 2); ?>" id="addRole"  data-bs-target="add-role" onclick=" addNewRole();">Add Role</a>
                                <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('deleterole', 2); ?>" id="deleteRole"  onclick="selectConfirm('delete');">Delete Role</a>
                            </div>
                        </div>
                    </div> -->

                        <div id="RoleList" style="display:block;">
                            <input type="hidden" id="selected" value="">
                            <table class="nhl-datatable table table-striped" id="roleGrid" width="100%" data-page-length="25">
                                <thead>
                                    <tr>
                                        <th id="key0" headers="name" class="">
                                            Role Name
                                            <i class="fa fa-caret-down cursorPointer direction" id = "name1" onclick = "addActiveSort('asc', 'name');sortingIconColor('name1')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id = "name2" onclick = "addActiveSort('desc', 'name');sortingIconColor('name2')" style="font-size:18px"></i>
                                        </th>
                                        <th id="key1" headers="modified" class="">Modified 
                                            <i class="fa fa-caret-down cursorPointer direction" id = "modified1" onclick = "addActiveSort('asc', 'modified');sortingIconColor('modified1')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id = "modified2" onclick = "addActiveSort('desc', 'modified');sortingIconColor('modified2')" style="font-size:18px"></i>    
                                    </th>
                                    </tr>
                                </thead>
                                <!--                        <tfoot>
                                <tr>
                                    <th>Role Name</th>
                                </tr>
                            </tfoot>-->
                            </table>
                        <?php
                    }
                        ?>
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


<div id="add-role" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4>Add Role</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="add-role">&times;</a>
    </div>
    <div class="btnGroup" data-qa="add-role-save-btn">
        <div class="icon-circle">
            <div data-qa="saveRole" class="toolTip" onclick="rolesDataSubmit('rolesdataform');">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <form id="rolesdataform" method="POST" action="">
                <div class="form-group has-label">
                    <label>
                        Role Name
                    </label>
                    <input data-qa="roleNameInput" class="form-control" name="roleName" data-qa="roleName" id="roleName" type="text" autocomplete="off" required maxlength="15" />
                </div>
                <div id="htmlresp">

                </div>
            </form>
        </div>
    </div>
</div>

<div id="edit-role" class="rightSidenav" data-class="md-6">
    <div class="rsc-loader hide"></div>
    <div class="card-title border-bottom">
        <h4>Edit Role</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="edit-role">&times;</a>
    </div>
    <div class="btnGroup" id="editOption" style="display: block;">
        <div class="icon-circle">
            <div class="toolTip editOption">
                <i class="tim-icons icon-pencil"></i>
                <span class="tooltiptext">Edit</span>
            </div>
        </div>
    </div>

    <div class="btnGroup" style="display: none;" id="toggleButton">
        <div class="icon-circle iconTick circleGrey">
            <div class="toolTip" data-qa="save-role-btn" onclick="rolesDataSubmit('rolesEditdataform');">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>

        <div class="icon-circle toggleEdit" id="toggleEdit">
            <div class="toolTip">
                <i class="tim-icons icon-simple-remove"></i>
                <span class="tooltiptext">Toggle edit mode</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <form id="rolesEditdataform" method="POST" action="">
                <div class="form-group has-label">
                    <label>
                        Role Name
                    </label>
                    <input class="form-control" name="editRoleName" id="editRoleName" type="text" autocomplete="off" readonly />
                </div>
                <!--            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="hidden" name="editglobalRole" value="0" />
                                    <input class="form-check-input" type="checkbox" name="editglobalRole" id="editglobalRole">
                                    <span class="form-check-sign"></span><span id="global_EditText"></span>
                                </label>
                            </div>-->
                <input type="hidden" name="roleId" id="roleId">

                <div id="htmlrespEdit"></div>
            </form>
        </div>
    </div>
</div>
<style>
    #edit-role select.selectpicker {
        display: block !important;
    }

    #add-role #rolesdataform #htmlresp div.accordion div.area div:last-child {
        width: 80px;
    }

    #add-role #htmlresp,
    #edit-role #htmlrespEdit {
        margin-top: 16px;
    }

    #roleGrid_filter {
        display: none;
    }
</style>