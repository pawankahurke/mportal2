    <!-- content starts here  -->
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
                                    <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('createorder', 2); ?>" onclick="getSkuList()" data-target="pts_CreateProvision">Create Order</a>
                                    <?php if ($_SESSION["user"]["signuptype"] == 'msp') { ?>
                                        <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('buymore', 2); ?><?php echo setRoleForAnchorTag('tenant', 2); ?>" id="buy_more" onclick="showBuyNowSkuDetails();" data-target="buyNowOptionDiv">Buy More</a>
                                    <?php } ?>
                                    <a id="exportAllOrders" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('exportorder', 2); ?>" >Export</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table id="msp_Order_Grid" class="nhl-datatable table table-striped">
                        <thead>
                            <tr>
                                <th class="table-plus" title="Customer Number">Order Number</th>
                                <th class="table-plus" title="Sku Name">Sku Name</th>
                                <th class="table-plus" title="Created Date">Created Date</th>
                                <th class="table-plus" title="Order key">Order key</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th class="table-plus" title="Customer Number">Order Number</th>
                                <th class="table-plus" title="Sku Name">Sku Name</th>
                                <th class="table-plus" title="Created Date">Created Date</th>
                                <th class="table-plus" title="Order key">Order key</th>
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

<div id="pts_CreateProvision" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Create Order</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="pts_CreateProvision">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="addNewProvisionSubmit" name="addNewProvisionSubmit" onclick="addNewProvision();">
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
                            Select a Sku
                        </label>
                        <em class="error err_addCustSKUSVar">*</em>
                        <select class="selectpicker" data-style="btn btn-info" title="Select Sku" data-size="3" id="addCustSKUSVar" name="addCustSKUSVar">

                        </select>
                    </div>
                    <input type="hidden" id="addPlatform" name="addPlatform" value="Windows">
                    <input type="hidden" id="addCustCountry" name="addCustCountry" value="US">
                    <input type="hidden" id="skuData" name="skuData" value="">
                    <div class="form-group has-label consumer">
                        <label>
                            Customer Number
                        </label>
                        <em class="error err_addCustCustomerNumberSub">*</em>
                        <input class="form-control" name="addCustCustomerNumberSub" type="text" id="addCustCustomerNumberSub"/>
                    </div>

                    <div class="form-group has-label consumer">
                        <label>
                            Order Number
                        </label>
                        <em class="error err_addCustOrderNumberSub">*</em>
                        <input class="form-control" id="addCustOrderNumberSub" type="text" name="addCustOrderNumberSub" />
                    </div>
                    <div class="form-group has-label consumer">
                        <label>
                            Customer First Name
                        </label>
                        <em class="error err_addCustFirstNameSub">*</em>
                        <input class="form-control" name="addCustFirstNameSub" id="addCustFirstNameSub" type="text"/>
                    </div>
                    <div class="form-group has-label consumer">
                        <label>
                            Customer Last Name
                        </label>
                        <em class="error err_addCustLastNameSub">*</em>
                        <input class="form-control" name="addCustLastNameSub" id="addCustLastNameSub" type="text"/>
                    </div>
                    <div class="form-group has-label consumer">
                        <label>
                            Customer Email ID
                        </label>
                        <em class="error err_addCustEmailIdSub">*</em>
                        <input class="form-control" name="addCustEmailIdSub" id="addCustEmailIdSub" type="text"/>
                    </div>
                    <div class="form-group has-label consumer">
                        <label>
                            Customer Phone No
                        </label>
                        <em class="error err_addCustPhoneNoSub">*</em>
                        <input class="form-control" name="addCustPhoneNoSub" id="addCustPhoneNoSub" type="text"/>
                    </div>
                    <div class="form-group has-label consumer">
                        <label>
                            Date Of Order
                        </label>
                        <em class="error err_addCustOrderDateSub">*</em>
                        <input class="form-control datetimepicker" name="addCustOrderDateSub" id="addCustOrderDateSub" type="text"/>
                    </div>

                    <!-- Commercial flow content -->
                    <div class="form-group has-label commercial">
                        <span id="skuTypeVal"></span>
                        <br/>
                        <span class="ComSubType"></span>
                    </div>
                    <div class="form-group has-label commercial">
                        <label>
                            Select Customer
                        </label>
                        <em class="error err_tenantCustomer">*</em>
                        <select class="selectpicker" data-style="btn btn-info" title="Select Customer" data-size="3" id="tenantCustomer" name="tenantCustomer">

                        </select>
                    </div>
                    <div class="form-group has-label" id="successProvisionButtonsVal" style="display: none;">
                        <label>
                            Download URL
                        </label>
                        <em class="error">*</em>
                        <input class="form-control" name="new_prov_download_urlSub" id="new_prov_download_urlSub" type="text" readonly=""/>
                        <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="newProvCopyButton" name="newProvCopyButton">Copy Url</button>
                        <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="newProvSendMailButton" name="newProvSendMailButton" onclick="sendMailForPayment('successProvisionButtonsVal', 'addCustEmailIdSub', 'addCustCustomerNumberSub', 'addCustOrderNumberSub');">Send Mail</button>
                    </div>
                    <div class="form-group has-label" id="successCommercial" style="display: none;">
                        <span id="subsMsg"></span>
                        <label>
                        </label>
                        <input class="form-control" name="site_act_key" id="site_act_key" type="text" readonly=""/>
                        <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="site_act_key_CopyBtn" name="site_act_key_CopyBtn">Copy Key</button>
                    </div>

                </div>
                <div>
                    <span id="newProvisionErrorMsg" name="newProvisionErrorMsg"></span>
                </div>
            </div>
        </form>
    </div>
</div>


