<!-- content starts here  -->
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?><div class="content white-content commonThree">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <!-- <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                            </div>
                        </div> -->
                        <!-- <div class="bullDropdown">
                            <div class="dropdown">
                                <?php if ($_SESSION["user"]["signuptype"] != 'msp') { ?>
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item rightslide-container-hand <?php echo setRoleForAnchorTag('addcustomer', 2); ?>" data-target="add-customer" onclick="resetFormData();">Add Customer</a>
                                </div>
                                <?php } ?>
                            </div>
                        </div> -->
                    </div>

                    <table class="nhl-datatable table table-striped" id="msp_Customer_Grid">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email ID</th>
                                <!--<th>Created Time</th>-->
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Customer Name</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email ID</th>
                                <!--<th>Created Time</th>-->
                                <th>Status</th>
                            </tr>
                        </tfoot>
                    </table>
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
    <div class="card-title">
        <h4>Add Customer</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="add-customer">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="create_Customer();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content" style="height: calc(100vh - 0px);">
        <form id="RegisterValidation">
            <input type="hidden" id="parent_company_id" value="<?php echo url::toText($_SESSION['user']['entityId']); ?>">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="form-group has-label col-md-6">
                            <h4>
                                Customer Details
                            </h4>

                            <div class="form-group has-label">
                                <em class="error" id="required_cust_companyName">*</em>
                                <label>
                                    Customer name
                                </label>
                                <input class="form-control addCustRequired" name="cust_companyName" id="cust_companyName" />
                                <p id="custcompanyName" name="errMsg" style="color: red;"></p>
                            </div>

                            <div class="form-group has-label">
                                <em class="error" id="required_cust_firstName">*</em>
                                <label>
                                    First Name
                                </label>
                                <input class="form-control addCustRequired " name="cust_firstName" id="cust_firstName" />
                                <p id="custfirstName" name="errMsg" style="color: red;"></p>
                            </div>

                            <div class="form-group has-label">
                                <em class="error" id="required_cust_lastName">*</em>
                                <label>
                                    Last Name
                                </label>
                                <input class="form-control addCustRequired" name="cust_lastName" id="cust_lastName" />
                                <p id="custlastName" name="errMsg" style="color: red;"></p>
                            </div>

                            <div class="form-group has-label">
                                <em class="error" id="required_cust_email">*</em>
                                <label>
                                    Email
                                </label>
                                <input class="form-control addCustRequired" name="cust_email" id="cust_email" />
                                <p id="custemail" name="errMsg" style="color: red;"></p>
                            </div>

                            <div class="form-group has-label">
                                <em class="error" id="required_cust_email">*</em>
                                <label>
                                    User Role
                                </label>
                                <select class="selectpicker" data-style="btn btn-info" title="Choose User Role" data-size="3" id="cust_roleId" name="cust_roleId">
                                </select>
                                <p id="custroleId" name="errMsg" style="color: red;"></p>
                            </div>

                            <div class="form-group has-label">
                                <em class="error" id="required_cust_compWeb">*</em>
                                <label>
                                    Website
                                </label>
                                <input class="form-control addCustRequired" name="cust_compWeb" id="cust_compWeb" />
                                <p id="custcompWeb" name="errMsg" style="color: red;"></p>
                            </div>

                            <!--<div class="form-group has-label">
                                <label>
                                    Sku List<em class="error" id="required_cust_skuList">*</em>
                                </label>
                                <select class="selectpicker" multiple data-style="btn btn-info" title="Choose Sku" data-size="4" name="cust_skuList" id="cust_skuList">
                                </select>
                            </div>-->
                        </div>

                        <div class="form-group has-label col-md-6">
                            <h4>
                                Contact Details
                            </h4>

                            <div class="form-group has-label">
                                <em class="error" id="required_cust_phoneNum">*</em>
                                <label>
                                    Phone Number
                                </label>
                                <input class="form-control addCustRequired" name="cust_phoneNum" id="cust_phoneNum" />
                                <p id="custphoneNum" name="errMsg" style="color: red;"></p>
                            </div>

                            <div class="form-group has-label">
                                <em class="error" id="required_cust_companyAddr">*</em>
                                <label>
                                    Address
                                </label>
                                <input class="form-control addCustRequired" name="cust_companyAddr" id="cust_companyAddr" />
                                <p id="custcompanyAddr" name="errMsg" style="color: red;"></p>
                            </div>

                            <div class="form-group has-label">
                                <em class="error" id="required_cust_companyCity">*</em>
                                <label>
                                    City
                                </label>
                                <input class="form-control addCustRequired" name="cust_companyCity" id="cust_companyCity" />
                                <p id="custcompanyCity" name="errMsg" style="color: red;"></p>
                            </div>

                            <div class="form-group has-label">
                                <em class="error" id="required_cust_companyState">*</em>
                                <label>
                                    State
                                </label>
                                <input class="form-control addCustRequired" name="cust_companyState" id="cust_companyState" />
                                <p id="custcompanyState" name="errMsg" style="color: red;"></p>
                            </div>

                            <div class="form-group has-label">
                                <em class="error" id="required_cust_companyCountry">*</em>
                                <label>
                                    Country
                                </label>
                                <select class="form-control" title="Choose City" data-size="3" name="cust_companyCountry" id="cust_companyCountry" style="height: 26px; padding: 0px 0px 0px 8px;">
                                    <?php require_once '../lib/l-countrylist.php'; ?>
                                </select>
                                <p id="custcompanyCountry" name="errMsg" style="color: red;"></p>
                            </div>

                            <div class="form-group has-label" style="top:5px;">
                                <em class="error" id="required_cust_companyZip">*</em>
                                <label>
                                    Zip Code
                                </label>
                                <input class="form-control addCustRequired" name="cust_companyZip" id="cust_companyZip" />
                                <p id="custcompanyZip" name="errMsg" style="color: red;"></p>
                            </div>
                        </div>
                        <p id="errMsg" name="errMsg" style="font-size: 11px; padding-left: 15px; color: red; font-style: italic;"></p>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!--    <div class="button col-md-12 text-center">
        <p id="errMsg" name="errMsg" style="font-size: 13px;"></p>
    </div>-->
</div>
<!-- Add New  Customer -->
<div id="add-new-customer" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4 id="invite-title">Invite a Customer</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="add-new-customer">&times;</a>
    </div>

    <div class="form table-responsive white-content" id="new-customer">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label col-md-12">
                        <label>
                            Enter the Customers Email ID to invite a customer
                        </label>
                        <input class="form-control addCustRequired" name="invite_customer_email" id="invite_customer_email" />
                        <em class="error" id="required_invite_customer_email" localized="" style="color: red;">*</em>
                        <p class="red text-center" style="font-style: italic;" localized="">Click here to import multiple Customers Email IDs</p>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="button col-md-12 text-center">
        <p id="add_CustomerMsg" style="color: red;font-size: 17px;"></p>
        <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="sendInvitationToCustomersBtn" onclick="sendInvitationToCustomers()">Continue</button>
        <button type="button" class="swal2-confirm btn btn-success btn-sm" id="SB_customerPop" onclick="showCustomerDetailsPopup();" aria-label="">Click Here</button>
    </div>


    <div class="form table-responsive white-content" id="detail-customer" style="display: none;">
        <h4>Please enter your Customers details</h4>
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="form-group has-label col-md-6">
                            <em class="error" id="required_custInfo_fn">*</em>
                            <label>
                                FIRST NAME
                            </label>
                            <input class="form-control addCustRequired" name="custInfo_fn" id="custInfo_fn" required />
                        </div>

                        <div class="form-group has-label col-md-6">
                            <em class="error" id="required_custInfo_ln">*</em>
                            <label>
                                LAST NAME
                            </label>
                            <input class="form-control addCustRequired" name="custInfo_companyname" id="custInfo_ln" required />
                        </div>

                        <div class="form-group has-label col-md-12">
                            <em class="error" id="required_custInfo_companyname">*</em>
                            <label>
                                COMPANY NAME
                            </label>
                            <input class="form-control addCustRequired" name="custInfo_companyname" id="custInfo_companyname" />
                        </div>

                        <div class="form-group has-label col-md-6">

                        </div>

                        <div class="form-group has-label col-md-12">
                            <em class="error" id="required_custInfo_email">*</em>
                            <label>
                                EMAIL
                            </label>
                            <input class="form-control addCustRequired" name="custInfo_email" id="custInfo_email" />
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
    <div class="card-title">
        <h4>Please enter your Customers details</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="detail-customer">&times;</a>


        <div class="form table-responsive white-content">
            <form id="RegisterValidation">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group has-label col-md-6">
                                <em class="error" id="required_custInfo_fn">*</em>
                                <label>
                                    FIRST NAME
                                </label>
                                <input class="form-control addCustRequired" name="custInfo_fn" id="custInfo_fn" required />
                            </div>

                            <div class="form-group has-label col-md-6">
                                <em class="error" id="required_custInfo_ln">*</em>
                                <label>
                                    LAST NAME
                                </label>
                                <div class="col-sm-8">
                                    <input class="form-control addCustRequired" name="custInfo_companyname" id="custInfo_ln" required />
                                </div>
                            </div>

                            <div class="form-group has-label col-md-6">
                                <em class="error" id="required_custInfo_companyname">*</em>
                                <label>
                                    COMPANY NAME
                                </label>
                                <input class="form-control addCustRequired" name="custInfo_companyname" id="custInfo_companyname" />
                            </div>

                            <div class="form-group has-label col-md-6">
                                <em class="error" id="required_custInfo_email">*</em>
                                <label>
                                    EMAIL
                                </label>
                                <input class="form-control addCustRequired" name="custInfo_email" id="custInfo_email" />
                            </div>
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


<!-- Click Here UI ends -->

<div id="msp_CreateCustomerLink" class="rightSidenav" data-class="md-6">
    <div class="card-title">
        <h4>Customer Details</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="msp_CreateCustomerLink">&times;</a>
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
            </div>
        </form>
    </div>

    <div class="button col-md-12 text-center">
        <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="copy_link1">Copy</button>
    </div>
</div>