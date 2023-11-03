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
                        <div class="dropdown">
                            <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="tim-icons icon-bullet-list-67"></i>
                            </button>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item rightslide-container-hand" data-target="addReseller">Add Reseller</a>
                                <a class="dropdown-item rightslide-container-hand" href="javascript:;" id="exportAllResellers">Export</a>
                            </div>
                        </div>
                    </div>
                </div>
                <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="msp_Reseller_Grid">
                  <thead>
                    <tr>
                      <th>Reseller</th>
                      <th>First Name</th>
                      <th>Last Name</th>
                      <th>Reseller Email</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tfoot>
                    <tr>
                      <th>Reseller</th>
                      <th>First Name</th>
                      <th>Last Name</th>
                      <th>Reseller Email</th>
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

      <!-- Add Reseller UI starts -->
      <div id="addReseller" class="rightSidenav" data-class="md-6">
                <div class="card-title">
                    <h3>Add Reseller</h3>
                    <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="addReseller">&times;</a>
                </div>

                <div class="btnGroup">
                    <div class="icon-circle">
                        <div class="toolTip" onclick="create_Reseller();">
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
                                    <h4>
                                        Reseller Details
                                    </h4>
                                </div>
                                <div class="form-group has-label col-md-6">
                                    <h4>
                                        Contact Details
                                    </h4>
                                </div>
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        Reseller Name 
                                    </label>
                                    <input class="form-control addReselRequired" name="reselcompanyName" id="resel_companyName" type="text" />
                                    <input class="form-control" id="trialSite" type="hidden" name="trialSite" value="0">
                                    <em class="error" id="required_resel_companyName">*</em>
                                </div>
                                
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        Address 
                                    </label>
                                    <input class="form-control addReselRequired" name="reselcompanyAddr" id="resel_companyAddr" type="text" />
                                    <em class="error" id="required_resel_companyAddr">*</em>
                                </div>
                                    
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        First name 
                                    </label>
                                    <input class="form-control addReselRequired" name="reselfirstName" id="resel_firstName" type="text" />
                                    <em class="error" id="required_resel_firstName">*</em>
                                </div>
                                    
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        City 
                                    </label>
                                    <input class="form-control addReselRequired" name="reselcompanyCity" id="resel_companyCity" type="text" />
                                    <em class="error" id="required_resel_companyCity">*</em>
                                </div>

                                <div class="form-group has-label col-md-6">
                                    <label>
                                        Last Name 
                                    </label>
                                    <input class="form-control addReselRequired" name="resellastName" id="resel_lastName" type="text"/>
                                    <em class="error" id="required_resel_lastName">*</em>
                                </div>
                                
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        State 
                                    </label>
                                    <input class="form-control addReselRequired" name="reselcompanyState" id="resel_companyState" type="text" />
                                    <em class="error" id="required_resel_companyState">*</em>
                                </div>

                                <div class="form-group has-label col-md-6">
                                    <label>
                                        Email 
                                    </label>
                                    <input class="form-control addReselRequired" name="reselemail" id="resel_email" type="text" />
                                    <em class="error" id="required_resel_email">*</em>
                                </div>
                                
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        Zip Code 
                                    </label>
                                    <input class="form-control addReselRequired" name="reselcompanyZip" id="resel_companyZip" type="text" />
                                    <em class="error" id="required_resel_companyZip">*</em>
                                </div>

                                <div class="form-group has-label col-md-6">
                                    <label>
                                        Website 
                                    </label>
                                    <input class="form-control addReselRequired" name="reselcompWeb" id="resel_compWeb" type="text" />
                                    <em class="error" id="required_resel_compWeb">*</em>
                                </div>
                                
                                

                                    </div>
                                    <div class="form-group has-label col-md-6">
                                    <p id="add_ResellerMsg" style="color: red;font-size: 17px;"></p>
                                </div>
                            </div>  
                        </div>
                    </form>
                </div>
                
<!--                <div class="button col-md-12 text-center">
                    <button type="button" class="swal2-confirm btn btn-success btn-sm"  onclick="create_Reseller();" aria-label="">Add</button>
                </div>-->
                </div>
        <!-- Add Reseller UI ends -->                