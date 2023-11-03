<!-- content starts here  -->
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
?>
<div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <!--        Here you can write extra buttons/actions for the toolbar              -->
                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <span>Displaying Login Information of Last 24 hours</span>
                            </div>
                        </div>

                        <div class="bullDropdown" >
                            <div class="dropdown" id="explain_login">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a  id="export_login" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('loginexport', 2); ?>" onclick="exportdetails()" data-bs-target="login-range">Export Historical Details</a>
                                    <!--<a class="dropdown-item rightslide-container-hand" id="export_login" onclick="exportLogin();" href="#">Export To Excel</a>-->
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--                <div class="left">
                                        <h3>Login Details (Last 24 hours)</h3>
                                    </div>-->
                    <!--<div class="user-page" id="login_datatable_div">-->
                    <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="login_datatable">
                        <thead>
                            <tr>
                                <th>
                                    User Name
                                </th>
                                <th>
                                    User Email
                                </th>
                                <th>
                                    Login Time
                                </th>
                                <th>
                                    Login Status
                                </th>
                            </tr>
                        </thead>
      <!--                  <tfoot>
                          <tr>
                            <th>User Name</th>
                            <th>User Email</th>
                            <th>Login Time</th>
                            <th>Login Browser IP</th>
                            <th>Login Status</th>
                          </tr>
                        </tfoot>-->
                    </table>
                    <!--</div>-->
                </div>
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>

<div id="login-range" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Export Historical Details</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="login-range">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="exportLogin();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Export</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    
                    <div class="form-group has-label">
                        <!--  <h4 class="card-title">Level</h4> -->
                        <label>
                            Type
                        </label>
                        <select class="form-control selectpicker dropdown-submenu" onchange="checkLevelType(this.value)" disabled="true" data-style="btn btn-info" id="LevelType" data-size="5"> 
                            <!-- <option value="Customer"  >Customer</option> -->
                            <option value="User" selected>User</option> 
                        </select>
                        
                        <div class="CustomerSelection">
                        <select class="form-control selectpicker dropdown-submenu" data-style="btn btn-info"  id="CustomerSelection" data-size="5"> 
                            
                        </select>
                    </div>
                    
                        <div class="UserSelection">
                        <select class="form-control selectpicker dropdown-submenu"  data-style="btn btn-info"  id="UserSelection" data-size="5"> 
                            
                        </select>
                        </div>   
                         
                    </div>
                    
                    <div class="form-group has-label">
                        <label>
                            Start Date
                        </label>
                        <input type="text" class="form-control datetimepicker" id="datefrom" name="datefrom"  autocomplete="off">
                        <span style="color:red;" id="datefrom_err"></span>
                    </div>

                    <div class="form-group has-label">
                        <label>
                            End Date
                        </label>
                        <input type="text" class="form-control datetimepicker" id="dateto" name="dateto"  autocomplete="off">
                        <span style="color:red;" id="dateto_err"></span>
                    </div>
                </div>

                <div class="form-group has-label">
                    <!--  <h4 class="card-title">Level</h4> -->
                    <label>
                        Level
                    </label>
                    <select class="form-control selectpicker dropdown-submenu" data-style="btn btn-info" id="levelId" data-size="5"> 
                        <option value="All">All </option>
                        <option value="Success">Success</option>
                        <option value="Failed">Fail</option>
                    </select>
                </div>
                <div class="button col-md-12 text-center" style="bottom:-38px;">
                    <span id="loadingSuccessMsg" style="color: green;float: left;"></span>

                    <!--                     <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" onclick="exportLogin();"  >Export</button>-->
                </div>
                <div>
                    <span id="errorMsg" style="color:red;"></span>
                </div>
            </div>
        </form>
    </div>
</div>
