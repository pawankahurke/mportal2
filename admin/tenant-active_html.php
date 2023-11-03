<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
?><div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <!--        Here you can write extra buttons/actions for the toolbar              -->
                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                            </div>
                        </div>

                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('activatetenant', 2);?>"  data-target="activate-tenant" onclick="openActivateEntityModal();">Activate Tenant</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table id="entity_Grid" width="100%" class="nhl-datatable table table-striped" >
                        <thead>
                            <tr>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Company</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Company</th>
                                <th>Status</th>
                            </tr>
                        </tfoot>
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


<div id="activate-tenant" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Activate Tenant</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="activate-tenant">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="activate_tenant();" id="tenantActiveBtn" name="tenantActiveBtn">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Activate</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label>
                            Tenant Name
                        </label>
                        <em class="error err_entity_id">*</em>
                        <input type="hidden" id="entity_id" placeholder="" name="entity_id" value="1">
                        <input class="form-control" name="entity_tenant" type="text" id="entity_tenant" readonly=""/>
                    </div>

                    <div class="form-group has-label">
                        <label>
                            Subscription Key
                        </label>
                        <em class="error err_entity_actvkey">*</em>
                        <input class="form-control" name="entity_actvkey" id="entity_actvkey" type="text"/>
                    </div>
                </div>
                <div>
                    <span id="error_add_entity" style="color:red" name="error_add_entity"></span>
                </div>
            </div>
        </form>
    </div>
<!--    <div class="button col-md-12 text-center">
        <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" onclick="activate_tenant();" id="tenantActiveBtn" name="tenantActiveBtn">Activate</button>
    </div>-->
    </div>

