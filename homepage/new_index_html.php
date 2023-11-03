<div class="content white-content">
    <div class="row" style="margin-top: 8px">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body text-center">
                    <img src="../assets/img/WelcomeImage.png" alt="" />
                    <p class="title">
                        Welcome to Nanoheal
                    </p>

                    <p class="para mt-3" data-qa="Dashboard/homepage/new_index_html.php">
                        You dont have any sites linked to your account.
                        Please add a site to <br /> continue using Nanoheal
                    </p>

                    <div>
                        <button style="margin: 10px" class="btn addBtn mt-4 <?php echo setRoleForAnchorTag('addsite', 2); ?>" onclick="showAddSite()" data-qa="site-add-on-home-page" data-bs-target="site-add-container">
                            Add a site
                        </button>
                        <button style="margin: 10px" class="btn addBtn mt-4 " data-qa="logout-on-home-page" data-bs-target="site-add-container">
                            <a href="<?php echo "//" . $_SERVER['HTTP_HOST'] . "/Dashboard/logout.php" ?>" style="color: #fff;font-weight: bold;">Logout</a>
                        </button>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="skuname" value="" />
<input type="hidden" id="skudescription" value="" />
<input type="hidden" id="skucategory" value="" />
<input type="hidden" id="skubillingtype" value="" />
<input type="hidden" id="skuquantity" value="" />
<input type="hidden" id="skuamount" value="" />
<input type="hidden" id="skutrialperiod" value="" />
<input type="hidden" id="skubillingcycle" value="" />
<input type="hidden" id="skuid" value="" />
<!-- Add new site UI starts  -->
<!-- <div id="site-add-container" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Add Site</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="site-add-container">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="addDeploymentSiteBtn" onclick="AddSiteField()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body" style="padding:10px 10px;">
                <form id="siteAddForm" name="siteAddForm">
                    <div class="form-group has-label">
                        <span class="text-danger">*</span>
                        <label for="deploy_sitename">Name of the site</label>
                        <input type="text" name="deploy_sitename" data-qa="deploy_sitename" id="deploy_sitename" class="form-control pl-1">
                        <p id="siteError" class="text-danger" style="display:none">Enter site name</p>
                    </div>

                    <div class="form-group has-label">
                        <label>
                            Assign Site to Users
                        </label>
                        <select data-live-search="true" class="selectpicker" multiple data-style="btn btn-info" title="Select Users" data-size="3" id="sitesUsers" name="sitesUsers">

                        </select>
                        <span id="add_userLevel-err"></span>
                    </div>

                    <div class="form-group has-label">
                        <span class="text-danger">*</span>
                        <label for="license_key">Enter License Key</label>
                        <input type="text" name="license_key" id="license_key" style="width: 71%;padding: 0;" class="form-control pl-1">
                        <button type="button" class="btn btn-success btn-sm" style="top: -50px;margin-left: 76%;" id="verifyKey" onclick="verfiyKey()">Check Key</button>
                        <a href="javascript:void(0)" class = "pl-0" onclick="changeKey()" id="changeKey" style="cursor: pointer;margin-top: -42px;">Change Key</a>
                        <p id="KeyError" class="text-danger" style="display:none">Enter License Key</p>
                    </div>

                    <div class="form-group has-label"  id="showDetailsTable" style="display:none">
                      <h5><b>License Details</b></h5>
                      <table style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th class="NewTable FirstRow">License Valid Till</th>
                                            <th class="NewTable" id="validLicense"></th>
                                        </tr>
                                        <tr>
                                            <th class="NewTable FirstRow">Total Number Of Licenses</th>
                                            <th class="NewTable" id="totalLicense"></th>
                                        </tr>
                                        <tr>
                                            <th class="NewTable FirstRow">Licenses Free</th>
                                            <th class="NewTable" id="freeLicense"></th>
                                        </tr>
                                        <tr>
                                            <th class="NewTable FirstRow">Licenses Used</th>
                                            <th class="NewTable" id="usedLicense"></th>
                                        </tr>
                                        <tr>
                                            <th class="NewTable FirstRow">Distribution Limit</th>
                                            <th class="NewTable" id="distLimit"></th>
                                        </tr>
                                    </thead>
                                </table>
                    </div>

                    <div class="form-group has-label">
                        <label class="siteCreateErr"></label>
                    </div>

                    <div class="button col-md-12 text-left">
                        <p id="required_Sitename" style="color: red;font-size: 14px;"></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> -->

<!-- Add Site Starts -->
<div id="site-addConfig-container" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Add Site</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="site-addConfig-container">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="addDeploymentSiteBtn" onclick="addNewSiteFunc()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body" style="padding:10px 10px;">
                <form id="siteAddForm" name="siteAddForm">
                    <div class="form-group has-label">
                        <span class="text-danger">*</span>
                        <label for="deploy_sitename">Name of the site</label>
                        <input type="text" name="deploy_sitename" data-qa="deploy_sitename" id="deploy_sitename" class="form-control pl-1">
                        <p id="siteError" class="text-danger" style="display:none">Enter site name</p>
                    </div>

                    <div class="form-group has-label">
                        <span class="text-danger">*</span>
                        <label for="deploy_emailsub">Email Subject</label>
                        <input type="text" name="deploy_emailsub" id="deploy_emailsub" class="form-control pl-1">
                        <p id="emailSubError" class="text-danger" style="display:none">Enter Email Subject</p>
                    </div>

                    <div class="form-group has-label">
                        <span class="text-danger">*</span>
                        <label for="deploy_emailsender">Email Sender</label>
                        <input type="text" name="deploy_emailsender" id="deploy_emailsender" class="form-control pl-1">
                        <p id="emailSenderError" class="text-danger" style="display:none">Enter Email Sender</p>
                    </div>

                    <div class="form-group has-label">
                        <label for="client32_name">32-bit Client Name</label>
                        <input type="text" name="client32_name" data-qa="client32_name" id="client32_name" class="form-control pl-1">
                        <!-- <p id="licensenameError" class="text-danger" style="display:none">Enter Client Linux Name</p> -->
                    </div>

                    <div class="form-group has-label">
                        <label for="client64_name">64-bit Client Name</label>
                        <input type="text" name="client64_name" data-qa="client64_name" id="client64_name" class="form-control pl-1">
                        <!-- <p id="licensenameError" class="text-danger" style="display:none">Enter Client Linux Name</p> -->
                    </div>

                    <div class="form-group has-label">
                        <label for="branding_url">Branding URL</label>
                        <input type="text" name="branding_url" id="branding_url" class="form-control pl-1">
                        <!-- <p id="licensenameError" class="text-danger" style="display:none">Enter Client Linux Name</p> -->
                    </div>

                    <div class="form-group has-label">
                        <label for="license_name">License Name</label>
                        <input type="text" name="license_name" disabled id="license_name" class="form-control pl-1">
                        <!-- <p id="licensenameError" class="text-danger" style="display:none">Enter Client Linux Name</p> -->
                    </div>

                    <div class="form-group has-label">
                        <label for="license_details">License Used / Total</label>
                        <input type="text" name="license_details" disabled id="license_details" class="form-control pl-1">
                        <!-- <p id="license_detailsError" class="text-danger" style="display:none">Enter Tenant ID</p> -->
                    </div>

                    <div class="form-group has-label">
                        <label for="license_bill">Billing Cycle</label>
                        <input type="text" name="license_bill" disabled id="license_bill" class="form-control pl-1">
                        <!-- <p id="dIDnameError" class="text-danger" style="display:none">Enter Deployement ID</p> -->
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<!-- Add Site ends -->


<style>
    .addBtn {
        width: 229px;
        height: 51px;
        background: #040D30;
        border-radius: 5px;
    }

    .para {
        font-weight: 600;
        font-size: 14px;
        /* line-height: 30px;
    text-align: center; */
        color: #000000;
    }

    .title {
        font-weight: 600;
        font-size: 24px;
        color: #000000;
    }

    .sidebar {
        display: none;
    }

    table,
    th,
    td {
        border: 1px solid black;
        border-collapse: collapse;
        font-weight: 400;
    }

    #changeKey {
        font-size: 11px;
        cursor: pointer;
        color: #fa0f4b
    }
</style>

<script>
    function changeKey() {
        $('#license_key').val('');
        $('#license_key').attr('readonly', false);
        $('#showDetailsTable').css('display', 'none');
        $('#totalLicense').html('');
        $('#freeLicense').html('');
        $('#usedLicense').html('');
        $('#validLicense').html('');
        $('#distLimit').html('');
    }


    function addSitePopup() {
        rightContainerSlideOn('site-add-container');
        $("#site_name").val("");
        $("#act_key").val("");
        $("#required_Sitename").html("");
        $("#err_sitename").html();
        $("#err_sitekey").html();
        $("#deploy_sitename").val('');
        $("#deploy_sitekey").val('');

        $('#addDeploymentSiteBtn').css({
            'pointer-events': 'initial',
            'cursor': 'pointer'
        });
        $(".download_url_div").hide();
        $("#download_url").val('');

        $('.download_url_div').hide();
        $(".create_site_div").show();
        $('#site-add-container em.error').html('');

        var payinfo = $('#payinfo').html();
        if (payinfo == "0") {
            $('.deploy_sitekey_div').show();
        }
    }

    function AddSiteField() {
        var site = $('#deploy_sitename').val();
        var userList = $('#sitesUsers').val();
        var checkKey = $('#license_key').val();

        if ($.trim(site) === "") {
            $('#siteError').css('display', 'block');
            return false;
        }

        if ($.trim(checkKey) === "") {
            $('#KeyError').css('display', 'block');
            return false;
        }
        var SKU = $('#skuname').val();

        $.ajax({
            type: "POST",
            url: "../device/org_api.php",
            data: {
                'function': 'get_SkuId',
                'SKU': SKU,
                'csrfMagicToken': csrfMagicToken
            },
            dataType: 'json',
            success: function(response) {
                console.log(response, "response");
                if (response.length == 0) {
                    addNewSku();
                } else {
                    $('#skuid').val(response.sid);
                    AddSiteDetails();
                }
            },
            error: function(error) {
                console.log("error");
            }
        });

    }

    function addNewSku() {
        var skuname = $('#skuname').val();
        var skuDesc = $('#skudescription').val();
        var skuCat = $('#skucategory').val();
        var skuBillType = $('#skubillingtype').val();
        var skuQty = $('#skuquantity').val();
        var skuAmt = $('#skuamount').val();
        var skuTrial = $('#skutrialperiod').val();
        var skuBillCycle = $('#skubillingcycle').val();
        var licenseKey = $('#license_key').val();

        var data = {
            function: "addNewSkuList",
            'skuname': skuname,
            'skuDesc': skuDesc,
            'skuCat': skuCat,
            'skuBillType': skuBillType,
            'skuQty': skuQty,
            'skuAmt': skuAmt,
            'skuTrial': skuTrial,
            'skuBillCycle': skuBillCycle,
            'csrfMagicToken': csrfMagicToken
        };

        $.ajax({
            url: "../device/org_api.php",
            type: "POST",
            data: data,
            dataType: "json",
            success: function(data) {
                $('#skuid').val($.trim(data));
                AddSiteDetails();
                AddLicenseDetails(skuname, skuDesc, skuCat, skuBillType, skuQty, skuAmt, skuTrial, skuBillCycle, licenseKey);
            },
            error: function(errorThrown) {
                console.log(errorThrown);
            }
        });
    }

    function AddLicenseDetails(skuname, skuDesc, skuCat, skuBillType, skuQty, skuAmt, skuTrial, skuBillCycle, licenseKey) {
        var data = {
            function: "addLicenseDetails",
            'skuname': skuname,
            'skuDesc': skuDesc,
            'skuCat': skuCat,
            'skuBillType': skuBillType,
            'skuQty': skuQty,
            'skuAmt': skuAmt,
            'skuTrial': skuTrial,
            'skuBillCycle': skuBillCycle,
            'licenseKey': licenseKey,
            'csrfMagicToken': csrfMagicToken
        };

        $.ajax({
            url: "../device/org_api.php",
            type: "POST",
            data: data,
            dataType: "json",
            success: function(data) {
                console.log("success");
            },
            error: function(errorThrown) {
                console.log(errorThrown);
            }
        });
    }

    function AddSiteDetails() {
        var sitename = $("#deploy_sitename").val();
        var skuid = $('#skuid').val();
        // var skuitem = skudata.split('###')[0];
        var startup = 'All'; //$('#deploy_startup').val();
        var followon = 'All'; //$('#deploy_followon').val();
        var delay = $('#skubillingcycle').val();

        // if (sitename == '' || skudata == '' || delay == '') {
        //     $.notify("Please enter the details in all the fields");
        //     return false;
        // }

        var regExp = /^[a-zA-Z0-9_]+$/; // vizualizations donot support sitenames with space on it
        if (!sitename.match(regExp)) {
            $.notify("Enter only AlphaNumeric values for Site name. <br/>Character <b>underscore _</b> can be used.");
            return false;
        }

        var data = {
            function: "create_OrgInsSite",
            sitename: sitename,
            skuid: skuid,
            startup: startup,
            followon: followon,
            delay: delay,
            'csrfMagicToken': csrfMagicToken
        };

        $.ajax({
            url: "../device/org_api.php",
            type: "POST",
            data: data,
            dataType: "json",
            success: function(data) {
                if (data.status) {
                    $.notify(data.msg);
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                } else {
                    $.notify(data.msg);
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                }
            },
            error: function(errorThrown) {
                console.log(errorThrown);
            }
        });
    }

    function verfiyKey() {
        var checkKey = $('#license_key').val();
        $.ajax({
            type: "POST",
            url: "../home/homeFunction.php",
            data: {
                "function": 'verifyLicenseKey',
                "checkKey": checkKey,
                'csrfMagicToken': csrfMagicToken
            },
            dataType: 'json',
            success: function(data) {
                var total = data.quantity;
                var used = data.amount;
                var left = total - used;
                $('#showDetailsTable').css('display', 'block');
                $('#totalLicense').html(total);
                $('#freeLicense').html(left);
                $('#usedLicense').html(used);
                $('#validLicense').html(data.billingcycle + " Days");
                $('#distLimit').html(1);
                $('#license_key').attr('readonly', true);
                $('#skuname').val(data.name);
                $('#skudescription').val(data.description);
                $('#skucategory').val(data.category);
                $('#skubillingtype').val(data.billingtype);
                $('#skuquantity').val(data.quantity);
                $('#skuamount').val(data.amount);
                $('#skutrialperiod').val(data.trialperiod);
                $('#skubillingcycle').val(data.billingcycle);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("error");
            }
        });

    }
</script>