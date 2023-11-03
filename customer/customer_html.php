<!-- content starts here  -->
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
?><div class="content white-content commonThree">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                        <!-- loader -->
						<div id="loader" class="loader"  data-qa="loader" style="position: absolute;bottom: 50%;right:50%;">
                           <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                        </div>
                    <!-- <div class="toolbar">
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
                                    <a class="dropdown-item rightslide-container-hand <?php echo setRoleForAnchorTag('addcustomer', 2);?>" data-target="add-customer" onclick="addcust();">Add Customer</a>
                                    <a class="dropdown-item rightslide-container-hand <?php echo setRoleForAnchorTag('addcustomer', 2);?>" data-target="add-new-customer" onclick="inviteCustomer();">Add New Customer</a>
                                    <a class="dropdown-item rightslide-container-hand <?php echo setRoleForAnchorTag('downloadurl', 2);?>" data-target="msp_CreateCustomerLink" onclick="get_custDnlURL();">Download URL</a>
                                    <a class="dropdown-item rightslide-container-hand <?php echo setRoleForAnchorTag('customerexport', 2);?>" id="exportAllCustomers">Export</a>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <div class="row clearfix three">
                        <div class="col-md-12">
                            <div class="row clearfix">
                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 left equalHeight">
                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 lf-rt">
                                        <table class="nhl-datatable table table-striped" id="msp_Customer_Grid">
                                            <thead>
                                                <tr>
                                                    <th>Customer Name</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr>
                                                    <th>Customer Name</th>
                                                    <th>Status</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 rt-lt">
                                        <table class="nhl-datatable table table-striped" id="msp_Sites_Grid">
                                            <thead>
                                                <tr>
                                                    <th>Sites</th>
                                                    <th>Device Count</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr>
                                                    <th>Sites</th>
                                                    <th>Device Count</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row clearfix">
                                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 right equalHeight">
                                    <table class="nhl-datatable table table-striped" id="msp_Device_Grid">
                                        <thead>
                                            <tr>
                                                <th>Device Name</th>
                                                <th>Installed Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>Device Name</th>
                                                <th>Installed Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
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
</div>

<div id="add-customer" class="rightSidenav" data-class="md-6">
                <div class="card-title border-bottom">
                    <h4>Add Customer</h4>
                    <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-target="add-customer">&times;</a>
                </div>
                <div class="btnGroup">
                    <div class="icon-circle">
                        <div class="toolTip" onclick="create_Customer();">
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
                                        <h3>
                                           Customer Details
                                        </h3>
                                    </div>
                                     <div class="form-group has-label col-md-6">
                                        <h3>
                                           Reseller Details
                                        </h3>
                                    </div>

                                    <div class="form-group has-label col-md-6">
                                        <label>
                                           Customer name
                                        </label>
                                        <input class="form-control addCustRequired" name="cust_companyName" id="cust_companyName" required />
                                        <input class="form-control" id="trialSite" type="hidden" name="trialSite" value="0">
                                        <em class="error" id="required_cust_companyName">*</em>
                                    </div>

                                <div class="form-group has-label col-md-6">
                                    <label>
                                       Reseller Name
                                    </label>
                                    <!-- <input class="form-control addCustRequired" name="reseller_name" id="reseller_name" required /> -->
                                                <input class="form-control" readonly="true"type="text" value="<?php echo $_SESSION['user']['companyName'] ?>">
                                    </div>
                                <div class="form-group has-label col-md-6">
                                    <label>
                                       Website
                                    </label>
                                    <input class="form-control addCustRequired" name="entity_companyname" id="cust_compWeb" />
                                    <em class="error" id="required_cust_compWeb">*</em>
                                </div>
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        Reseller Email
                                    </label>
                                    <!-- <input class="form-control" name="reseller_email" id="reseller_email" /> -->
                                                <input class="form-control" readonly="true"type="text" value="<?php echo $_SESSION['user']['adminEmail'] ?>">
                                    </div>
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        Address
                                    </label>
                                    <input class="form-control addCustRequired" name="cust_companyAddr" id="cust_companyAddr"/>
                                    <em class="error" id="required_cust_companyAddr">*</em>
                                </div>
                                <div class="form-group has-label col-md-6">
                                    <h3>
                                        Contact Details
                                    </h3>
                                    <!--<input class="form-control" name="entity_refno" id="entity_refno"/>-->

                                </div>
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        City
                                    </label>
                                    <input class="form-control addCustRequired" name="cust_companyCity" id="cust_companyCity"/>
                                    <em class="error" id="required_cust_companyCity">*</em>
                                </div>
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        First Name
                                    </label>
                                    <input class="form-control addCustRequired " name="cust_firstName" id="cust_firstName"/>
                                    <em class="error" id="required_cust_firstName">*</em>
                                </div>
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        State
                                    </label>
                                    <input class="form-control addCustRequired" name="cust_companyState" id="cust_companyState"/>
                                    <em class="error" id="required_cust_companyState">*</em>
                                </div>
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        Last Name
                                    </label>
                                    <input class="form-control addCustRequired" name="cust_lastName" id="cust_lastName" />
                                    <em class="error" id="required_cust_lastName">*</em>
                                </div>
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        Zip Code
                                    </label>
                                    <input class="form-control addCustRequired" name="cust_companyZip" id="cust_companyZip"/>
                                    <em class="error" id="required_cust_companyZip">*</em>
                                </div>
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        Email
                                    </label>
                                    <input class="form-control addCustRequired" name="cust_email" id="cust_email"/>
                                    <em class="error" id="required_cust_email">*</em>
                                </div>
                                <div class="form-group has-label col-md-6">

                                </div>
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        User Role
                                    </label>
                                    <select class="selectpicker" multiple data-style="btn btn-info" title="Choose User Role" data-size="3" id="cust_roleId" name="cust_roleId">

                                    </select>
                                    <em class="error" id="required_cust_email">*</em>
                                </div>
                                <div class="form-group has-label col-md-6">

                                </div>
                                <div class="form-group has-label col-md-6">
                                    <label>
                                        Licence Count
                                    </label>
                                    <input class="form-control addCustRequired" type="text" name="cust_licence" id="cust_licence" value="5"/>
                                    <em class="error" id="required_cust_licence">*</em>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="button col-md-12 text-center">
                    <p id="add_CustomerMsg" style="color: red;font-size: 17px;"></p>
                    <!--<button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" onclick="create_Customer();">Submit</button>-->
                </div>
            </div>
</div>
<!-- Add New  Customer -->
<div id="add-new-customer" class="rightSidenav" data-class="sm-3">
                <div class="card-title border-bottom">
                    <h4 id="invite-title">Invite a Customer</h4>
                    <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-target="add-new-customer">&times;</a>
                </div>

                <div class="form table-responsive white-content" id="new-customer">
                    <form id="RegisterValidation">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group has-label col-md-12">
                                    <label>
                                    Enter the Customers Email ID to invite a customer
                                    </label>
                                    <input class="form-control addCustRequired" name="invite_customer_email" id="invite_customer_email"/>
                                    <em class="error" id="required_invite_customer_email" localized="" style="color: red;">*</em>
                                    <p class="red text-center" style="font-style: italic;" localized="">Click here to import multiple Customers Email IDs</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="button col-md-12 text-center" id="button_div">
                    <p id="add_CustomerMsg" style="color: red;font-size: 17px;"></p>
                    <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="sendInvitationToCustomersBtn" onclick="sendInvitationToCustomers()" >Continue</button>
                    <button type="button" class="swal2-confirm btn btn-success btn-sm" id="SB_customerPop" onclick="showCustomerDetailsPopup();" aria-label="" >Click Here</button>
                </div>


               <div class="form table-responsive white-content"  id="detail-customer" style="display: none;">
                <h4>Please enter your Customers details</h4>
                    <form id="RegisterValidation">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                      <div class="form-group has-label col-md-6">
                                        <label>
                                          FIRST NAME
                                        </label>
                                            <input class="form-control addCustRequired" name="custInfo_fn" id="custInfo_fn" required />
                                            <em class="error" id="required_custInfo_fn">*</em>
                                        </div>

                                        <div class="form-group has-label col-md-6">
                                            <label>
                                            LAST NAME
                                            </label>
                                                <input class="form-control addCustRequired" name="custInfo_companyname" id="custInfo_ln" required />
                                                <em class="error" id="required_custInfo_ln">*</em>
                                        </div>
                                        <div class="form-group has-label col-md-12">
                                            <label>
                                            COMPANY NAME
                                            </label>
                                            <input class="form-control addCustRequired" name="custInfo_companyname" id="custInfo_companyname" />
                                            <em class="error" id="required_custInfo_companyname">*</em>
                                        </div>

                                        <div class="form-group has-label col-md-6">

                                        </div>

                                        <div class="form-group has-label col-md-12">
                                            <label>
                                            EMAIL
                                            </label>
                                            <input class="form-control addCustRequired" name="custInfo_email" id="custInfo_email" />
                                            <em class="error" id="required_custInfo_email">*</em>
                                        </div>

                                        <div class="form-group has-label col-md-6">

                                        </div>
                                </div>
                            </div>
                    </form>
                </div>

                            <div class="button col-md-12 text-center">
                                <p id="add_CustomerMsg" style="color: red;font-size: 17px;"></p>
                                <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" onclick="onClickSB_customerPop('customer');">Continue</button>
                                <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" onclick="inviteCustomer();">Send Invite</button>
                            </div>
            </div>

</div>
<!-- Add new customer UI ends -->

<!-- Click Here UI -->
<div id="detail-customer" class="rightSidenav" data-class="md-6">
                <div class="card-title border-bottom">
                    <h4>Please enter your Customers details</h4>
                    <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-target="detail-customer">&times;</a>


                <div class="form table-responsive white-content">
                    <form id="RegisterValidation">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                      <div class="form-group has-label col-md-6">
                                        <label>
                                          FIRST NAME
                                        </label>
                                            <input class="form-control addCustRequired" name="custInfo_fn" id="custInfo_fn" required />
                                            <em class="error" id="required_custInfo_fn">*</em>
                                        </div>

                                        <div class="form-group has-label col-md-6">
                                            <label>
                                            LAST NAME
                                            </label>
                                                <div class="col-sm-8">
                                                <input class="form-control addCustRequired" name="custInfo_companyname" id="custInfo_ln" required />
                                                <em class="error" id="required_custInfo_ln">*</em>
                                                </div>
                                        </div>
                                        <div class="form-group has-label col-md-6">
                                            <label>
                                            COMPANY NAME
                                            </label>
                                            <input class="form-control addCustRequired" name="custInfo_companyname" id="custInfo_companyname" />
                                            <em class="error" id="required_custInfo_companyname">*</em>
                                        </div>

                                        <div class="form-group has-label col-md-6">
                                            <label>
                                                EMAIL
                                            </label>
                                            <input class="form-control addCustRequired" name="custInfo_email" id="custInfo_email"/>
                                            <em class="error" id="required_custInfo_email">*</em>
                                        </div>
                                </div>
                            </div>
                    </form>
                </div>

                            <div class="button col-md-12 text-center">
                                <p id="add_CustomerMsg" style="color: red;font-size: 17px;"></p>
                                <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" onclick="onClickSB_customerPop('customer');">Continue</button>
                                <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" onclick="inviteCustomer();">Send Invite</button>
                            </div>
            </div>
</div>
</div>


<!-- Click Here UI ends -->

<div id="msp_CreateCustomerLink" class="rightSidenav" data-class="md-6">
                <div class="card-title border-bottom">
                    <h4>Customer Details</h4>
                    <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-target="msp_CreateCustomerLink">&times;</a>
                </div>

                <div class="form table-responsive white-content">
                    <form id="RegisterValidation">
                        <div class="card">
                            <div class="card-body">
                                <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="downloadGrid">
                                    <thead>
                                      <tr>
                                        <th>Order Number</th>
                                        <th>Site</th>
                                        <th>Dowload URL</th>
                                      </tr>
                                    </thead>
                              </table>
                        </div>
                    </form>
                </div>

                <div class="button col-md-12 text-center">
                    <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="copy_link1">Copy</button>
                </div>
            </div>
</div>

