<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

nhRole::dieIfnoRoles(['licensedetails']); // roles: licensedetails
?>
<div class="content mt-3 pt-4 white-content" onload="siteDataTable();">
    <div class="row" style="margin-top: 15px;">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <div id="loader" class="loader" data-qa="loader" class="w-100" style="position: absolute;bottom: 50%;right:50%;">
                        <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                    </div>
                    <div class="toolbar">
                        <!--        Here you can write extra buttons/actions for the toolbar
                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>
                            </div>
                        </div>   -->

                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" style="margin-left: -42px;">
                                    <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addsite', 2); ?>" onclick="updateLicense()">Update License Details</a>
                                    <a class="dropdown-item dropHandy id=" site-emailDistribution">Export Details</a>
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
                    <input type="hidden" id="siteId" value="" />
                    <input type="hidden" id="searchValue" value="" />
                    <input type="hidden" id="appendStatus" value="" />
                    <table class="nhl-datatable table table-striped outer" width="100%" data-page-length="25" id="site_grid">
                        <thead>
                            <tr>
                                <th>License</th>
                                <th>Expiry</th>
                                <th>Attached to</th>
                                <th>Total Count</th>
                                <th>Unused Licenses</th>
                                <th>Distribution</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="col-md-12" id="errorMsg" style="display:none;">
                        <span>Please select site or group to view list</span>
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


<!-- Add new site UI starts  -->
<div id="site-add-container" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Update License details</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close pt-2 border-0" data-target="site-add-container" onclick="clearAddSiteField()">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="addDeploymentSiteBtn" onclick="updateLicenseCount()">
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
                        <label>Select Subsription whose count must be updated</label>
                        <select class="selectpicker" data-style="btn btn-info" title="Please select License" data-size="3" id="site_skuid" name="site_skuid" onChange="fillTableDetails()">
                        </select>
                    </div>

                    <!-- <p class = "font-weight-bold mt-4">License details</p> -->
                    <div class="form-group has-label" id="LicenseDetails1" style="display:none">
                        <h5><b>License Details</b></h5>
                        <table class="inner" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="NewTable FirstRow font-weight-400">License Valid Till</th>
                                    <th class="NewTable font-weight-400" id="validLicense1"></th>
                                </tr>
                                <tr>
                                    <th class="NewTable FirstRow font-weight-400">Total Number Of Licenses</th>
                                    <th class="NewTable font-weight-400" id="totalLicense1"></th>
                                </tr>
                                <tr>
                                    <th class="NewTable FirstRow font-weight-400">Licenses Free</th>
                                    <th class="NewTable font-weight-400" id="freeLicense1"></th>
                                </tr>
                                <tr>
                                    <th class="NewTable FirstRow font-weight-400">Licenses Used</th>
                                    <th class="NewTable font-weight-400" id="usedLicense1"></th>
                                </tr>
                                <tr>
                                    <th class="NewTable FirstRow font-weight-400">Distribution Limit</th>
                                    <th class="NewTable font-weight-400" id="distLimit1"></th>
                                </tr>
                            </thead>
                        </table>
                    </div>

            </div>

            <div class="form-group has-label pl-2" id="LicenseDetails2" style="display:none">
                <span class="text-danger">*</span>
                <label for="license_key">Enter License Key</label>
                <input type="text" name="license_key" id="license_key" style="width: 71%;padding: 0;" class="form-control pl-1">
                <button type="button" class="btn btn-success btn-sm" style="top: -31px;margin-left: 76%;" id="verifyKey" onclick="verfiyKey()">Check Key</button>
                <a href="javascript:void(0)" onclick="showPopupMsg()" class="pl-0" id="changeKey" style="cursor: pointer;margin-top:-29px">Where will I find the Key?</a>
                <p id="KeyError" class="text-danger" style="display:none">Enter License Key</p>
                <div class="form-group has-label" id="showDetailsTable" style="display:none">
                    <h5><b>License Details</b></h5>
                    <table class="inner" style="width: 100%;">
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

            </div>

            <div id="checkAppendLicense" style="display:none">
                <p>Are you sure you want to append the License details?</p>
                <!-- <input type="button" id="appendLicense" value="Append" onclick="checkAppendStatus()"> -->
                <label class="switch">
                    <input type="checkbox" onchange="checkAppendStatus()">
                    <span class="slider round"></span>
                </label>
                <p class="text-danger mt-4" id="showErrMsg" style="display:none">
                    You dont have enough licenses free to decrease the license count.
                    Please Contact Nanoheal Support to continue woth this action
                </p>
            </div>
            </form>

            <!-- <div class = "mt-4" id = "license_details" style = "display:none">
                    <p class = "font-weight-bold mt-4">License details</p>
                    <div>
                        <table class = "border" style="width:100%">
                            <tr>
                                <td class = "border border-right-0 pl-3">License valid till</td>
                                <td class = "border border-left-0 pr-3 text-right">27 April 2017</td>
                            </tr>

                            <tr>
                                <td class = "border border-right-0 pl-3">Total Number of License</td>
                                <td class = "border border-left-0 pr-3 text-right">100</td>
                            </tr>

                            <tr>
                                <td class = "border border-right-0 pl-3">License Fee</td>
                                <td class = "border border-left-0 pr-3 text-right">80</td>
                            </tr>
                        </table>
                        <p class = "text-danger mt-4">
                         You dont have enough licenses free to decrease the license count.
                         Please Contact Nanoheal Support to continue woth this action
                        </p>
                    </div>
                </div> -->
        </div>
    </div>
</div>
</div>
<!-- Add new site UI ends -->

<style>
    .dropdown-menu.inner li.hidden {
        display: none;
    }

    .dropdown-menu.inner.active li,
    .dropdown-menu.inner li.active {
        display: block;
    }

    div.bottom {

        bottom: 25px !important;

    }

    .checkKeyBtn {
        background: #F92472;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }

    #changeKey {
        font-size: 11px;
        cursor: pointer;
        color: #fa0f4b
    }

    .inner th,
    td {
        border: 1px solid black;
        border-collapse: collapse;
        font-weight: 400;
        color: #666;
    }

    .outer th,
    td {
        border-left: 0;
        border-right: 0;
        border-bottom: 0;
        color: #666;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 22px;

    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 24px;
        width: 24px;
        /* left: 35px; */
        bottom: -2px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }

    input:checked+.slider {
        background-color: red;
    }

    input:focus+.slider {
        box-shadow: 0 0 1px #red;
    }

    input:checked+.slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
        border-radius: 34px;
    }

    .slider.round:before {
        border-radius: 50%;
    }

    /* table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
  } */

    .main-panel>.content .card {
        min-height: calc(100vh - 67px);
        margin-bottom: 0px;
    }
</style>

<script src="../assets/js/core/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        siteDataTable();
        // getUsersList();
        // getSubscrptionList();
        $('#emailDistributeLoader').hide();
        $('#searchValue').val('');

    });

    function updateLicenseCount() {
        var appendStatus = $('#appendStatus').val();

        if (appendStatus == 0) {
            $.notify("Please check the key and update");
        } else {
            var checkKeyName = $('#site_skuid').val();
            var newSKUName = $('#skuname').val();
            var newSKUDesc = $('#skudescription').val();
            var newSKUCat = $('#skucategory').val();
            var newSKUBType = $('#skubillingtype').val();
            var newSKUQty = $('#skuquantity').val();
            var newSKUAmt = $('#skuamount').val();
            var newSKUTPrd = $('#skutrialperiod').val();
            var newSKUBCycle = $('#skubillingcycle').val();
            var licenseKey = $('#license_key').val();
            $.ajax({
                type: "POST",
                url: "../device/org_api.php",
                data: {
                    "function": 'updateLicenseCount',
                    "oldSkuName": checkKeyName,
                    "newSKUName": newSKUName,
                    "newSKUDesc": newSKUDesc,
                    "newSKUCat": newSKUCat,
                    "newSKUBType": newSKUBType,
                    "newSKUQty": newSKUQty,
                    "newSKUAmt": newSKUAmt,
                    "newSKUTPrd": newSKUTPrd,
                    "newSKUBCycle": newSKUBCycle,
                    'csrfMagicToken': csrfMagicToken
                },
                dataType: 'json',
                success: function(data) {
                    if ($.trim(data) == '1') {
                        $.notify("License Details successfully updated");
                        updateLicenseDetails(checkKeyName, newSKUName, newSKUDesc, newSKUCat, newSKUBType, newSKUQty, newSKUAmt, newSKUTPrd, newSKUBCycle, licenseKey);
                        rightContainerSlideClose('site-add-container');
                    } else {
                        $.notify("Error in updating details");
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    console.log("error");
                }
            });
        }
    }

    function updateLicenseDetails(oldSkuName, skuname, skuDesc, skuCat, skuBillType, skuQty, skuAmt, skuTrial, skuBillCycle, licenseKey) {
        var data = {
            function: "updateLicenseDetails",
            'oldSkuName': oldSkuName,
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
            url: "subFunc.php",
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

    function checkAppendStatus() {
        var licensesUsed = $('#usedLicense1').html();
        var newLicenseCount = $('#totalLicense').html();

        if (licensesUsed > newLicenseCount) {
            $('#showErrMsg').css('display', 'block');
            $('#appendStatus').val(0);
        } else {
            $('#appendStatus').val(1);
            $('#showErrMsg').css('display', 'none');
        }
    }


    function showPopupMsg() {
        $.notify("License Key");
    }

    function getLicenseList() {
        $.ajax({
            url: "subFunc.php",
            type: "POST",
            data: {
                function: 'getLicenseList',
                csrfMagicToken: csrfMagicToken
            },
            success: function(data) {
                $('#site_skuid').html('');
                $('#site_skuid').html(data);
                $(".selectpicker").selectpicker("refresh");
            },
            error: function(errorThrown) {
                console.log(errorThrown);
            }
        });
    }

    function getupdateSite() {
        var id = $("#siteId").val();
        $.ajax({
            url: "../site/getUpdateSite.php?id=" + id,
            type: "GET",
            data: {},
            success: function(data) {

            },
            error: function(errorThrown) {
                console.log(errorThrown);
            }
        });
    }

    function fillTableDetails() {
        var checkKeyName = $('#site_skuid').val();
        $.ajax({
            type: "POST",
            url: "../device/org_api.php",
            data: {
                "function": 'showLicenseDetails',
                "checkKey": checkKeyName,
                'csrfMagicToken': csrfMagicToken
            },
            dataType: 'json',
            success: function(data) {
                var total = data.quantity;
                var used = data.amount;
                var left = total - used;
                $('#LicenseDetails1').css('display', 'block');
                $('#newLicenseKey').css('display', 'block');
                $('#LicenseDetails2').css('display', 'block');
                $('#totalLicense1').html(total);
                $('#freeLicense1').html(left);
                $('#usedLicense1').html(used);
                $('#validLicense1').html(data.billingcycle + " Days");
                $('#distLimit1').html(1);
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("error");
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
                //   $('#license_key').attr('readonly',true);
                $('#skuname').val(data.name);
                $('#skudescription').val(data.description);
                $('#skucategory').val(data.category);
                $('#skubillingtype').val(data.billingtype);
                $('#skuquantity').val(data.quantity);
                $('#skuamount').val(data.amount);
                $('#skutrialperiod').val(data.trialperiod);
                $('#skubillingcycle').val(data.billingcycle);
                $('#checkAppendLicense').css('display', 'block');
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                console.log("error");
            }
        });

    }

    function siteDataTable() {


        $("#site_grid").dataTable().fnDestroy();
        siteTable = $('#site_grid').DataTable({
            scrollY: 'calc(100vh - 240px)',
            scrollCollapse: true,
            autoWidth: false,
            searching: false,
            processing: false,
            serverSide: false,
            ordering: false,
            bAutoWidth: true,
            pagingType: "full_numbers",
            responsive: true,
            ajax: {
                url: "../site/updateSiteFunction.php",
                type: "POST",
                dataType: "json",
                data: {
                    csrfMagicToken: csrfMagicToken
                },
            },
            language: {
                "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                search: "_INPUT_",
                searchPlaceholder: "Search for site"
            },
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            columns: [{
                    "data": "license"
                },
                {
                    "data": "expiry"
                },
                {
                    "data": "attached"
                },
                {
                    "data": "total"
                },
                {
                    "data": "unused"
                },
                {
                    "data": "distribution"
                }

            ],
            "columnDefs": [{
                    className: "dt-left tdColumn1",
                    "targets": 0,
                    "width": "20%"
                },
                {
                    className: "dt-left tdColumn2",
                    "targets": 1,
                    "width": "10%"
                },
                {
                    className: "dt-left tdColumn3",
                    "targets": 2,
                    "width": "15%"
                },
                {
                    "width": "10%",
                    "targets": 3
                },
                {
                    "width": "20%",
                    "targets": 4
                }
            ],
            select: false,
            bInfo: false,
            stateSave: true,
            "stateSaveParams": function(settings, data) {
                data.search.search = "";
            },
            "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',

        });

        $('.dataTables_filter input').addClass('form-control');
        $('#site_grid tbody').on('click', 'tr', function() {
            siteTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var rowID = siteTable.row(this).data();
            if (rowID != 'undefined' && rowID !== undefined) {
                var row = JSON.parse(JSON.stringify(rowID));
                console.log("'" + row.siteName + "'");
                var strele = $(row.siteName);
                $('#searchValue').val(strele.eq(0).attr('id'));
                //console.log(strele.eq(0).attr('id'));
                //alert(JSON.stringify(rowID));
                $("#siteId").val(row.DT_RowId);

            }
        });


        $("#site_searchbox").keyup(function() {
            siteTable.search(this.value).draw();
            $("#site_grid tbody").eq(0).html();
        });
        $('#site_grid').DataTable().search('').columns().search('').draw();
    }

    function getLicenseDetails() {
        //    $('#site-license-container').find('input[type="text"]').each({
        //        $(this).attr("readonly");
        //    });
        var site = $('#searchValue').val();
        if (site == '') {
            $.notify('Please select a site');
            rightContainerSlideClose('site-license-container');
            return false;
        }
        var data = {
            'function': "getLicenseDetails",
            'sitename': site
        };
        $.ajax({
            url: "../customer/org_api.php",
            type: "GET",
            data: data,
            success: function(data) {
                var res = JSON.parse(data);
                var maxinstall = '';
                var downloadUrl = '';
                if (res['data']['maxinstall'] == 0) {
                    maxinstall = 'Unlimited';
                } else {
                    maxinstall = res['data']['maxinstall'].toString();
                }
                $('#licSitename').val(site).attr('readonly', true);
                $('#licSkuname').val(res['data']['skuname']).attr('readonly', true);
                var usedTotal = res['data']['numofinstall'] + ' / ' + maxinstall;
                $('#licUsedtotal').val(usedTotal).attr('readonly', true);
                var regcode = res['data']['regcode'];
                var siteemailid = res['data']['siteemailid'];
                var installPath = res['data']['licenseurl'];
                downloadUrl = installPath + 'Provision/install/d.php?r=' + regcode + '&e=' + siteemailid;
                if (res['data']['isDownViaDash'] === 'YES') {
                    downloadUrl = installPath + 'install-eula.php?r=' + regcode + '&e=' + siteemailid;
                }
                $('#downloadUrl').val(downloadUrl).attr('readonly', true);
            },
            error: function(error) {
                console.log('Error :: getLicenseDetails : ' + error);
            }
        })
    }

    function updateLicense() {
        var LicenseId = $('#siteId').val();
        // if(LicenseId == ''){
        //     $.notify("Please select a license to update");
        // }else{
        rightContainerSlideOn('site-add-container');
        getLicenseList();
        // }
    }
</script>

<style>
    table {
        font-weight: normal;
    }
</style>