<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?><div class="content white-content">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <?php
                    //   nhRole::checkRoleForPage('user');
                    $res = true; // nhRole::checkModulePrivilege('user');
                    if ($res) {
                    ?>
                        <!-- <div id="loader" class="loader"  data-qa="loader" style="display:none">&nbsp;<img src="../assets/img/nanohealLoader.gif" style = "width: 71px;"></div> -->
                        <!-- <div class="toolbar"> -->
                        <!--        Here you can write extra buttons/actions for the toolbar              -->

                        <!-- <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                            </div>
                        </div> -->
                        <!-- </div> -->
                        <input type="hidden" id="selectedUser" value="">
                        <div id="loader" class="loader" data-qa="loader" style="position: absolute;bottom: 50%;right:50%;">
                            <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                        </div>
                        <table id="user_datatable" class="nhl-datatable table table-striped">
                            <thead>
                                <!-- <tr> -->
                                <tr>
                                    <th id="key0" headers="firstName" class="firstName">
                                        First Name
                                        <i class="fa fa-caret-down cursorPointer direction" id="firstName1" onclick="addActiveSort('asc', 'firstName'); user_datatable(content='',nextPage = 1, notifSearch = '','firstName', 'asc');sortingIconColor('firstName1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="firstName2" onclick="addActiveSort('desc', 'firstName'); user_datatable(content='',nextPage = 1, notifSearch = '','firstName', 'desc');sortingIconColor('firstName2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key1" headers="lastName" class="lastName">
                                        Last Name
                                        <i class="fa fa-caret-down cursorPointer direction" id="lastName1" onclick="addActiveSort('asc', 'lastName'); user_datatable(content='',nextPage = 1, notifSearch = '','lastName', 'asc');sortingIconColor('lastName1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="lastName2" onclick="addActiveSort('desc', 'lastName'); user_datatable(content='',nextPage = 1, notifSearch = '','lastName', 'desc');sortingIconColor('lastName2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key2" headers="user_email" class="">
                                        User Email
                                        <i class="fa fa-caret-down cursorPointer direction" id="user_email1" onclick="addActiveSort('asc', 'user_email'); user_datatable(content='',nextPage = 1, notifSearch = '','user_email', 'asc');sortingIconColor('user_email1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="user_email2" onclick="addActiveSort('desc', 'user_email'); user_datatable(content='',nextPage = 1, notifSearch = '','user_email', 'desc');sortingIconColor('user_email2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key3" headers="role_id" class="">
                                        Role Name
                                        <i class="fa fa-caret-down cursorPointer direction" id="role_id1" onclick="addActiveSort('asc', 'role_id'); user_datatable(content='',nextPage = 1, notifSearch = '','role_id', 'asc');sortingIconColor('role_id1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="role_id2" onclick="addActiveSort('desc', 'role_id'); user_datatable(content='',nextPage = 1, notifSearch = '','role_id', 'desc');sortingIconColor('role_id2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key4" headers="userType" class="">
                                        User Type
                                        <i class="fa fa-caret-down cursorPointer direction" id="userType1" onclick="addActiveSort('asc', 'userType'); user_datatable(content='',nextPage = 1, notifSearch = '','userType', 'asc');sortingIconColor('userType1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="userType2" onclick="addActiveSort('desc', 'userType'); user_datatable(content='',nextPage = 1, notifSearch = '','userType', 'desc');sortingIconColor('userType2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key5" headers="userStatus" class="">
                                        User status
                                        <i class="fa fa-caret-down cursorPointer direction" id="userStatus1" onclick="addActiveSort('asc', 'userStatus'); user_datatable(content='',nextPage = 1, notifSearch = '','userStatus', 'asc');sortingIconColor('userStatus1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="userStatus2" onclick="addActiveSort('desc', 'userStatus'); user_datatable(content='',nextPage = 1, notifSearch = '','userStatus', 'desc');sortingIconColor('userStatus2')" style="font-size:18px"></i>
                                    </th>
                                </tr>
                                <!-- <th>First Name</th>
                                <th>Last Name</th>
                                <th>User Email</th>
                                <th>Role Name</th>
                                <th>User Type</th>
                                <th>User status</th>
                            </tr> -->
                            </thead>
                            <!--                        <tfoot>
                            <tr>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>User Email</th>
                                <th>Role Name</th>
                                <th>User status</th>
                            </tr>
                        </tfoot>-->
                        </table>
                    <?php
                    }
                    ?>
                    <div id="largeDataPagination"></div>
                </div>
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>


<div id="add-new-user" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Add New User</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="add-new-user">&times;</a>
    </div>


    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="addAdvUser" name="addAdvUser">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label>
                            First Name <em class="error">*</em>
                        </label>
                        <input class="form-control" data-qa="advusername" name="advusername" type="text" id="advusername" required />
                        <span id="advusername-err"></span>
                    </div>

                    <div class="form-group has-label">
                        <label>
                            Last Name <em class="error">*</em>
                        </label>
                        <input class="form-control" data-qa="last_name" id="last_name" type="text" name="last_name" required />
                        <span id="last_name-err"></span>
                    </div>
                    <div class="form-group has-label">
                        <label>
                            Email address <em class="error">*</em>
                        </label>
                        <input class="form-control" data-qa="advuser_email" name="advuser_email" id="advuser_email" type="text" />
                        <span id="advuser_email-err"></span>
                    </div>
                    <div class="form-group has-label">
                        <label>
                            Sites <em class="error">*</em>
                        </label>
                        <input type="hidden" id="add_userLevel" value="customer" />
                        <!--This value intentionally kept as customer to insert sites for user-->
                        <span data-qa="add_Customers">
                            <select class="selectpicker" multiple data-style="btn btn-info" title="Select Site" data-size="3" id="add_Customers" name="add_Customers"></select>
                        </span>
                        <span id="add_userLevel-err"></span>
                    </div>
                    <div class="form-group has-label">
                        <label>
                            User Role <em class="error">*</em>
                        </label>
                        <span data-qa="add_advroleId">
                            <select class="selectpicker" data-style="btn btn-info" title="Select User Role" data-size="3" id="add_advroleId" name="add_advroleId"></select>
                        </span>
                        <span id="add_advroleId-err"></span>
                    </div>
                    <div class="form-group has-label">
                        <label>
                            Time Zone <em class="error">*</em>
                        </label>
                        <span data-qa="add_timeZone">
                            <select class="selectpicker" data-style="btn btn-info" title="Select Time Zone" data-size="3" id="add_timeZone" name="add_timeZone">
                            </select></span>
                        <span id="add_timeZone-err"></span>
                    </div>
                    <div class="form-group has-label">
                        <label>
                            Security <em class="error">&nbsp;</em>
                        </label>
                        <span data-qa="add_sectype">
                            <select class="selectpicker" data-style="btn btn-info" title="Select security type" data-size="3" id="add_sectype" name="add_sectype" required>
                                <option value="emailotp">Email OTP</option>
                                <!-- <option value="MFA">MFA</option> -->
                                <option value="none" selected>None</option>
                            </select>
                        </span>
                        <span id="add_sectype-err"></span>
                    </div>
                </div>
                <div>
                    <span id="errMsg" name="errMsg"></span>
                </div>
            </div>
        </form>
    </div>
    <!--    <div class="button col-md-12 text-center">
        <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="addAdvUser" name="addAdvUser">Submit</button>
    </div>-->
</div>

<div id="edit-user" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Modify User</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="edit-user">&times;</a>
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
            <div class="toolTip" id="updateAdvUser" name="updateAdvUser">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
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
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label>
                            First Name
                        </label><em class="error">*</em>
                        <input class="form-control" type="text" id="edit_advusername" name="edit_advusername" />
                        <input type="hidden" name="sel_userid" id="sel_userid">
                        <input type="hidden" name="AdminRoleId" id="AdminRoleId">
                        <input type="hidden" name="ActualRoleId" id="ActualRoleId">
                        <input type="hidden" name="ActualRoleId" id="ActualRoleName">
                    </div>

                    <div class="form-group has-label">
                        <label>
                            Last Name
                        </label><em class="error">*</em>
                        <input class="form-control" name="edit_last_name" id="edit_last_name" type="text" />
                    </div>
                    <div class="form-group has-label">
                        <label>
                            Email address
                        </label><em class="error">*</em>
                        <input class="form-control" name="edit_advuser_email" id="edit_advuser_email" type="text" readonly="" />
                    </div>
                    <div class="form-group has-label">
                        <label>
                            Sites
                        </label><em class="error">*</em>
                        <span data-qa="edit_Sites">
                        <select class="selectpicker" multiple data-style="btn btn-info" title="Select Site" data-size="3" id="edit_Sites" name="edit_Sites">
                        </select>
                        </span>
                    </div>
                    <div class="form-group has-label">
                        <label>
                            User Role
                        </label><em class="error">*</em>
                        <span data-qa="edit_advroleId">
                        <select class="selectpicker" data-style="btn btn-info" title="Select User Role" data-size="3" id="edit_advroleId" name="edit_advroleId">
                        </select>
                        </span>
                    </div>
                    <div class="form-group has-label">
                        <label>
                            Time Zone <em class="error">*</em>
                        </label>
                        <span data-qa="edit_timeZone">
                        <select class="selectpicker" data-style="btn btn-info" title="Select Time Zone" data-size="3" id="edit_timeZone" name="edit_advroleId">
                        </select>
                        </span>
                        <span id="edit_advroleId-err"></span>
                    </div>
                    <div class="form-group has-label">
                        <label>
                            Security <em class="error">&nbsp;</em>
                        </label>
                        <span data-qa="edit_sectype">
                        <select class="selectpicker" data-style="btn btn-info" title="Select security type" data-size="3" id="edit_sectype" name="edit_sectype">
                    </select>
                    </span>
                        <span id="edit_sectype-err"></span>
                        <br> <br> <span onclick="resetMfa();" style="cursor:pointer;"><u>Reset MFA</u></span>
                    </div>
                </div>
                <div>
                    <span id="errMsgEdit" name="errMsgEdit"></span>
                </div>
            </div>
        </form>
    </div>

    <!--    <div class="button col-md-12 text-center">
        <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="updateAdvUser" name="updateAdvUser">Modify</button>
    </div>-->
</div>


<style>
    #user_datatable_filter {
        display: none;
    }
</style>