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
                            </div>
                        </div>

                        <div class="bullDropdown">
                            <div class="dropdown" id="explain_tenant" >
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" id="add_tenant">
                                    <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addtenant', 2); ?>" data-target="add-tenant" onclick="openAddEntityModal();">Add Tenant</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="entity_Grid">
                        <thead>
                            <tr>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Company</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Company</th>
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

<div id="add-tenant" class="rightSidenav" data-class="md-6">
    <div class="card-title">
        <h4>Add Tenant</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="add-tenant">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="create_entity();" id="save_btn">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        
                        <div class="form-group has-label col-md-6">
                            <div class="form-group has-label">
                                <em class="error" id="required_entity_companyname">*</em>
                                <label>
                                    Company Name 
                                </label>
                                <input class="form-control entitydetails" name="entity_companyname" id="entity_companyname" />
                            </div>
                            
                            <div class="form-group has-label" id="f_Name">
                            <em class="error" id="required_entity_fn">*</em>
                            <label>
                                First Name 
                            </label>
                            <input class="form-control entitydetails" name="entity_fn" id="entity_fn" required />
                        </div>

                            <div class="form-group has-label" id="L_Name">
                            <em class="error" id="required_entity_ln">*</em>
                            <label>
                                Last Name 
                            </label>
                            <input class="form-control entitydetails" name="entity_ln" id="entity_ln" required />
                        </div>

                            <div class="form-group has-label">
                            <em class="error" id="required_entity_email">*</em>
                            <label>
                                Email 
                            </label>
                            <input class="form-control entitydetails" name="entity_email" id="entity_email" />
                            </div>

                            <div class="form-group has-label">
                                <em class="error" id="required_entity_skulist">*</em>
                                                            <label>
                                    Please select sku list
                                                            </label>
                                <select class="selectpicker" multiple data-style="btn btn-info" title="Choose SKU" data-size="4" id="entity_skulist" name="entity_skulist">
                                </select>
                            </div>
                            
                            <div class="form-group has-label">
                                <em class="error" id="required_entity_subscrpType">*</em>
                                                            <label>
                                    Please select subscription type
                                                            </label>
                                <select class="selectpicker" data-style="btn btn-info" title="Choose Subscription Type" data-size="2" id="entity_subscrpType" name="entity_subscrpType">
                                    <option value="pts">PTS</option>
                                    <option value="entp">Enterprise</option>
                                </select>
                            </div>
                            
                        </div>
                        
                        <div class="form-group has-label col-md-6">
                            <div class="form-group has-label">
                            <em class="error" id="required_entity_phoneno">*</em>
                            <label>
                                Phone Number 
                            </label>
                            <input class="form-control entitydetails" name="entity_phoneno" id="entity_phoneno"/>
                            </div>

                            <div class="form-group has-label">
                            <em class="error" id="required_entity_address">*</em>
                            <label>
                                Address 
                            </label>
                            <input class="form-control entitydetails" name="entity_address" id="entity_address"/>
                            </div>

                            <div class="form-group has-label">
                            <em class="error" id="required_entity_city">*</em>
                            <label>
                                City 
                            </label>
                            <input class="form-control entitydetails" name="entity_city" id="entity_city" />
                        </div>

                            <div class="form-group has-label">
                            <em class="error" id="required_entity_province">*</em>
                            <label>
                                State Province 
                            </label>
                            <input class="form-control entitydetails" name="entity_province" id="entity_province"/>
                            </div>

                            <div class="form-group has-label">
                                <em class="error" id="required_entity_postal">*</em>
                            <label>
                                    Postal Code 
                            </label>
                                <input class="form-control entitydetails" name="entity_postal" id="entity_postal"/>
                        </div>

                            <div class="form-group has-label">
                                <em class="error" id="required_entity_country">*</em>
                            <label>
                                    Country 
                            </label>
                                <select class="form-control" title="Choose Country" data-size="4" id="entity_country" style="height: 30px; padding: 0px 0px 0px 8px;">
                                    <?php require_once '../lib/l-countrylist.php'; ?>
                                </select>
                                    </div>
                                </div>

                        <div class="category form-category">* Required fields</div>
                    </div>
                </div>
            </div>
        </form>

    <div class="button col-md-12 text-center">
        <span class="error-txt" id="error_add_entity" localized="" style="color:red;"></span>
    </div>
</div>
</div>

<!-- Edit Tenant Content -->
<div id="edit-tenant" class="rightSidenav" data-class="md-6">
    <div class="card-title">
        <h4>Edit Tenant</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="edit-tenant">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="update_entity();" id="update_btn">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <form id="EditTenantInformationForm">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        
                        <div class="form-group has-label col-md-6">
                            <div class="form-group has-label">
                                <em class="error required_edit_entity_companyname">*</em>
                                <label>
                                    Company Name 
                                </label>
                                <input class="form-control" name="edit_entity_companyname" id="edit_entity_companyname" />
                            </div>
                            
                            <div class="form-group has-label" id="f_Name">
                                <em class="error required_edit_entity_fn">*</em>
                                <label>
                                    First Name 
                                </label>
                                <input class="form-control edit_entitydetails" name="edit_entity_fn" id="edit_entity_fn" required />
                            </div>

                            <div class="form-group has-label">
                                <em class="error required_edit_entity_ln">*</em>
                                <label>
                                    Last Name 
                                </label>
                                <input class="form-control edit_entitydetails" name="edit_entity_ln" id="edit_entity_ln" required />
                            </div>
                            
                            <div class="form-group has-label">
                                <em class="error required_edit_entity_email">*</em>
                                <label>
                                    Email 
                                </label>
                                <input class="form-control edit_entitydetails" name="edit_entity_email" id="edit_entity_email" />
                            </div>
                            
                            <div class="form-group has-label">
                                <em class="error required_edit_entity_skulist">*</em>
                                <label>
                                    Please select sku list
                                </label>
                                <select class="selectpicker" multiple data-style="btn btn-info" title="Choose SKU" data-size="4" id="edit_entity_skulist" name="edit_entity_skulist">
                                </select>
                            </div>
                            
                            <div class="form-group has-label">
                                <em class="error required_edit_entity_subscrpType">*</em>
                                <label>
                                    Please select subscription type
                                </label>
                                <select class="selectpicker" data-style="btn btn-info" title="Choose Subscription Type" data-size="2" id="edit_entity_subscrpType" name="edit_entity_subscrpType" disabled="">
                                    <option value="pts">PTS</option>
                                    <option value="entp">Enterprise</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group has-label col-md-6">
                            <div class="form-group has-label">
                                <em class="error required_edit_entity_phoneno">*</em>
                                <label>
                                    Phone Number 
                                </label>
                                <input class="form-control edit_entitydetails" name="edit_entity_phoneno" id="edit_entity_phoneno"/>
                            </div>
                            
                            <div class="form-group has-label">
                                <em class="error required_edit_entity_address">*</em>
                                <label>
                                    Address 
                                </label>
                                <input class="form-control edit_entitydetails" name="edit_entity_address" id="edit_entity_address"/>
                            </div>
                            
                            <div class="form-group has-label">
                                <em class="error required_edit_entity_city">*</em>
                                <label>
                                    City 
                                </label>
                                <input class="form-control edit_entitydetails" name="edit_entity_city" id="edit_entity_city" />
                            </div>
                            
                            <div class="form-group has-label">
                                <em class="error required_edit_entity_province">*</em>
                                <label>
                                    State Province 
                                </label>
                                <input class="form-control edit_entitydetails" name="edit_entity_province" id="edit_entity_province"/>
                            </div>
                            
                            <div class="form-group has-label">
                                <em class="error required_edit_entity_postal">*</em>
                                <label>
                                    Postal Code 
                                </label>
                                <input class="form-control edit_entitydetails" name="edit_entity_postal" id="edit_entity_postal"/>
                            </div>
                            
                            <div class="form-group has-label">
                                <label>
                                    Country 
                                </label>
                                <em class="error required_edit_entity_country">*</em>
                                <select class="form-control" title="Choose Country" data-size="4" id="edit_entity_country" style="padding: 10px 18px 10px 18px;">
                                </select>
                            </div>
                        </div>

                        <div class="category form-category">* Required fields</div>
                    </div>
                </div>
            </div>
        </form>
        
        <div class="button col-md-12 text-center">
            <span class="error-txt" id="error_edit_entity" localized="" style="color:red;"></span>
        </div>
    </div>
</div>
