/**
 * This file belongs to "Consumer/PTS" Customers only.
 * This file is created for all provisioning functionality for "Consumer/PTS" flow.
 * In this file "PTS" indicates "managed service provider" or "Consumer" bussiness flow.
 */

$(document).ready(function () {
    $('.order-table').DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        responsive: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>'
    });

    $('#addCustOrderDate').datetimepicker({
        format: 'yyyy-mm-dd',
        startView: 'month',
        minView: 'month',
        pickerPosition: "top-right",
        startDate: "-1y",
        endDate: '+0d'
    });

    $('#addServiceDate').datetimepicker({
        format: 'yyyy-mm-dd',
        startView: 'month',
        minView: 'month',
        pickerPosition: "top-right",
        startDate: "+1d",
        endDate: '+1y',
    });
    get_PTSCustomers();


    /************ CopyCommand **************/

    $('#newProvCopyButton').click(function () {
        var urlField = document.querySelector('#new_prov_download_url');
        urlField.select();
        document.execCommand('copy');
    });

    $('#renewProvCopyButton').click(function () {
        var urlField = document.querySelector('#renew_prov_download_url');
        urlField.select();
        document.execCommand('copy');
    });

    $('#upgrdProvCopyButton').click(function () {
        var urlField = document.querySelector('#upgrade_prov_download_url');
        urlField.select();
        document.execCommand('copy');
    });

    $('#entitleProvCopyButton').click(function () {
        var urlField = document.querySelector('#entitle_prov_download_url');
        urlField.select();
        document.execCommand('copy', true);
    });

    $('#addDeviceCopyButton').click(function () {
        var urlField = document.querySelector('#addDevicePayURL');
        urlField.select();
        document.execCommand('copy');
    });

    $('#pts_CreateProvision').on('hidden.bs.modal', function () {
        $("#chkPayment-btn").css("display", "block");
        $('#pts_CreateProvision .form-control').val('');
        $('#pts_CreateProvision .error').html('*');
        $('#pts_CreateProvision .form-group').removeClass('is-focused');
        $('#pts_CreateProvision .form-group').addClass('is-empty');
        $("#priceTableContent").html('');
        $("#addCustSKUS").html("");
        $(".selectpicker").selectpicker("refresh");
        $("#newProvisionErrorMsg").html("");
        $("#successProvisionButtons span").html("Please use below link to make payment.");
        $("#successProvisionButtons span").css("color", "#48b2e4");
        $("#successProvisionButtons").hide();
        //$("#addNewProvisionSubmit").hide();
    });

    $("#pts_Entitlement").on('hidden.bs.modal', function () {
        $('#pts_Entitlement .entitleDetails').html('');
        $("#pts_Entitlement #entitleSuccessProvisionButtons").hide();
        $(".entitle-div").hide();
        $("#entitleSuccessProvisionButtons span").html("");
        $("#entitleSuccessProvisionButtons").hide();
    });

    $('#pts_RenewOrder').on('hidden.bs.modal', function () {
        $('#pts_RenewOrder .error').html('*');
        $("#pts_RenewOrder .popupLoader").show();
        $("#pts_RenewOrder .clearfix").hide();
        $("#newOrderNumber").val('');
        $('#pts_RenewOrder #newOrderNumber').parent().removeClass('is-focused');
        $('#pts_RenewOrder #newOrderNumber').parent().addClass('is-empty');
        $('#renewPriceTableContent').html('');
        $("#renewProvisionErrorMsg").html("");
        $("#renewSuccessProvisionButtons span").html("Please use below link to make payment.");
        $("#renewSuccessProvisionButtons span").css("color", "#48b2e4");
        $("#renewSuccessProvisionButtons").hide();
    });

    $('#pts_UpgradeOrder').on('hidden.bs.modal', function () {
        $('#pts_UpgradeOrder .error').html('*');
        $("#pts_UpgradeOrder .popupLoader").show();
        $("#pts_UpgradeOrder .clearfix").hide();
        $("#newUpgradeOrderNumber").val('');
        $('#pts_UpgradeOrder #newUpgradeOrderNumber').parent().removeClass('is-focused');
        $('#pts_UpgradeOrder #newUpgradeOrderNumber').parent().addClass('is-empty');
        $('#upgradePriceTableContent').html('');
        $("#upgradeProvisionErrorMsg").html("");
        $("#upgradeSuccessProvisionButtons span").html("Please use below link to make payment.");
        $("#upgradeSuccessProvisionButtons span").css("color", "#48b2e4");
        $("#upgradeSuccessProvisionButtons").hide();
    });

    $('#pts_addDevices').on('hidden.bs.modal', function () {
        $('#pts_addDevices .error').html('*');
        $('#pts_addDevices .addDeviceSuccessDivs').hide();
        $('#pts_addDevices .form-control').val('');
        $('#pts_addDevices span').html('');
        $('#pts_addDevices #addDeviceErrorMsg').html('<img style="margin-left: 13%;" src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        $("#addDeviceButton").show();
    });

    $('#pts_ExportDetails').on('hidden.bs.modal', function () {
        $("#cust_numbLists").html('');
        $("#cust_OrderLists").html('');
        $(".selectpicker").selectpicker('refresh');
    });

    $("#entitl_PayStatusButton").click(function () {
        $(".ordersLabel").css("margin-top", "3%");
    });

    $("#entitl_PayStatusButton").hide();
    $("#agentlogin").click(function () {
        $(".pass-LMI").hide();
        $("#rmt_loginid").prop("disabled", true);
        $("#rmt_pass").prop("disabled", true);
        $("#rmt_passconf").prop("disabled", true);
        $("#usernameEmail").prop("disabled", false);
    });
    $("#rmt_login").click(function () {
        $(".pass-LMI").show();
        $("#usernameEmail").prop("disabled", true);
        $("#rmt_loginid").prop("disabled", false);
        $("#rmt_pass").prop("disabled", false);
        $("#rmt_passconf").prop("disabled", false);

    });

    var url_value = $("#remote_url").val();
    if ($.trim(url_value) !== '') {
        $('#takeremote').removeAttr('disabled');
        $('#takeremote').removeClass('buttondisable');
        $('#takeremote').addClass('button');
    }

});

var ws = '';
var userName = '';
var loginType = 'email';

$('input:radio[name="login"]').change(
        function () {
            loginType = $(this).val();
            if ($(this).val() === 'login') {
                $("#rmt_loginid").removeAttr('disabled');
            } else {
                $("#rmt_loginid").attr('disabled', 'disabled');
            }
        });

$('#usernameEmail').change(function () {
    userName = $('#usernameEmail').val();
});

$("#addCustCustomerNumber").keyup(function () {
//    $("#addNewProvisionSubmit").hide();
});

$("#addCustOrderNumber").keyup(function () {
    //$("#addNewProvisionSubmit").hide();
});


/**
 * Fetch all customers list in json format.
 */
function get_PTSCustomers() {
    $('#pts_Customer_Grid').dataTable().fnDestroy();	//To avoid reinitialize error, need to destroy existing Datatable
    customerGrid = $('#pts_Customer_Grid').DataTable({
        scrollY: jQuery('#pts_Customer_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        searching: true,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        order: [],
        ajax: {
            url: "../lib/l-ptsAjax.php?function=get_CustomerGrid",
            type: "POST"
        },
        columnDefs: [
            {
                targets: "datatable-nosort",
                orderable: false
            },
            {
                className: "ignore", targets: [0]
            },
            {
                "data": "custName",
                "targets": 2,
                "visible": false
            }
        ],
        columns: [
            {"data": "custNumber"},
            {"data": "custEmail"},
            {"data": "custName"}
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "_START_-_END_ of _TOTAL_ entries",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function (settings, json) {
            $('#pts_Customer_Grid tbody tr:eq(0)').addClass("selected");
            cust_id = $('#pts_Customer_Grid tbody tr:eq(0)')[0].id;
            get_PTSOrders(cust_id);
        },
        drawCallback: function (settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
        }
    });
    $("#pts_Customer_Grid tbody").unbind().click(function () {
        //function body
    });
    $('#pts_Customer_Grid tbody').on('click', 'tr', function () {
        customerGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = customerGrid.row(this).id();
        enableOptions(id);
        get_PTSOrders(id);
    });

    $("#customer_searchbox").keyup(function () {
        customerGrid.search(this.value).draw();
        $('#pts_Order_Grid').dataTable().fnClearTable();
        $('#pts_Device_Grid').dataTable().fnClearTable();
        $('#pts_Customer_Grid tbody tr:eq(0)').click();
    });
}

/* Fetch orders for selected customer number*/
function get_PTSOrders(rowId) {
    var sel_CustomerNumber = getRowIdValue(rowId, 2, '---');
    $('#pts_Order_Grid').dataTable().fnDestroy();
    orderGrid = $('#pts_Order_Grid').DataTable({
        scrollY: jQuery('#pts_Order_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        searching: true,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        order: [],
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0]
            }],
        ajax: {
            url: "../lib/l-ptsAjax.php?function=get_OrderGrid&custNumber=" + sel_CustomerNumber,
            type: "POST"
        },
        columns: [
            {"data": "orderNum"},
            {"data": "installCount"}
            //,{"data": "status"}
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "_START_-_END_ of _TOTAL_ entries",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function (settings, json) {
            $('#pts_Order_Grid tbody tr:eq(0)').addClass("selected");
            order_id = $('#pts_Order_Grid tbody tr:eq(0)')[0].id;
            getOrderDeviceList(order_id);
        },
        drawCallback: function (settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
        }
    });

    $("#pts_Order_Grid tbody").unbind().click(function () {
        //function body
    });
    $('#pts_Order_Grid tbody').on('click', 'tr', function () {
        orderGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = orderGrid.row(this).id();
        getOrderDeviceList(id);
    });
    return true;
}

/* Fetch Device list for selected Sites */
function getOrderDeviceList(rowId) {
    var sel_compId = getRowIdValue(rowId, 0, '---');
    var sel_procId = getRowIdValue(rowId, 1, '---');
    var sel_customerNum = getRowIdValue(rowId, 2, '---');
    var sel_orderNum = getRowIdValue(rowId, 3, '---');
    var sel_isExpiring = getRowIdValue(rowId, 4, '---');

    if (sel_isExpiring == 1) {
        $("#renewOrderOption").show();
    } else {
        $("#renewOrderOption").hide();
    }

    $('#pts_Device_Grid').dataTable().fnDestroy();
    deviceGrid = $('#pts_Device_Grid').DataTable({
        scrollY: jQuery('#pts_Device_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        searching: true,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        order: [],
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1, 2]
            }],
        ajax: {
            url: "../lib/l-ptsAjax.php?function=get_OrdersDeviceGrid&custId=" + sel_compId + "&procId=" + sel_procId + "&custNum=" + sel_customerNum + "&ordNum=" + sel_orderNum,
            type: "POST"
        },
        columns: [
            {"data": "devicename"},
            {"data": "installDt"},
            {"data": "status"},
            {"data": "onlineStatus"}

        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "_START_-_END_ of _TOTAL_ entries",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function (settings, json) {

        },
        drawCallback: function (settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
        }
    });

    $('#pts_Device_Grid tbody').on('click', 'tr', function () {
        deviceGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = deviceGrid.row(this).id();
    });

}

//Hide show option Customer Enable/Disable
function enableOptions(rowId) {
    var sel_CustomerStatus = getRowIdValue(rowId, 4, '---');
    $("#enableCustomer_Li").hide();
    $("#disableCustomer_Li").hide();
    if (sel_CustomerStatus === 0 || sel_CustomerStatus === '0') {
        $("#enableCustomer_Li").show();
        $("#disableCustomer_Li").hide();
    } else if (sel_CustomerStatus === 1 || sel_CustomerStatus === '1') {
        $("#enableCustomer_Li").hide();
        $("#disableCustomer_Li").show();
    }

}

function getRowIdValue(selectedId, index, splittingFlag) {
    var tempRowId = selectedId.split(splittingFlag);
    var rowIdValue = tempRowId[index];
    return rowIdValue;
}

function addSKUList() {
    $("#addCustSKUS_error").html('*');
    $("#addCustCountry_error").html('*');
    $("#addNewProvisionSubmit").hide();
}

/* On change of country list following function will get called. */
function getSkuListForCountry() {
    $("#addNewProvisionSubmit").hide();
//    $("#successProvisionButtons").hide();
    $("#addCustSKUS").html('');
    $("#addCustCountry_error").html('');
    $("#addCustSKUS_error").html('<img style="margin-left: 11%;" src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');

    var platform = $("#addPlatform").val();
    var country = $("#addCustCountry").val();

    if ((platform === "") || platform === "0" || platform === undefined) {
        $("#addPlatform_error").html("Please select platform.");
        $("#addCustSKUS_error").html('*');
        $("#addCustCountry_error").html('');
    } else if ((country === "") || country === "0") {
        $("#addCustCountry_error").html("Please select country.");
        $("#addPlatform_error").html("*");
        $("#addCustSKUS_error").html('*');
        $("#addCustCountry_error").html('');
    } else if (country === undefined) {

    } else {

        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "../lib/l-ptsAjax.php",
            data: "function=get_SKUForCountry&ccode=" + country + "&platform=" + platform,
            success: function (result) {
                $("#addCustSKUS_error").html('*');
                if ($.trim(result.status) === "SUCCESS") {
                    var optionStr = '';
                    for (i = 0; i < result.records.length; i++) {
                        optionStr += '<option value=' + result.records[i].skuRef + '>' + result.records[i].description + '</option>';
                    }

                    $("#addCustSKUS").html(optionStr);
                    $(".selectpicker").selectpicker("refresh");

                } else {
                    $("#addCustSKUS").html('<option value="0">Sku Not Available</option>');
                    $(".selectpicker").selectpicker("refresh");
                }

            },
            error: function (result) {

            }
        });
    }
}

// To populate the provision SKU's
function getSkuList() {
    $("#addCustSKUS").html('');
    $("#addCustCountry_error").html('');
    $("#addCustSKUS_error").html('<img style="margin-left: 11%;" src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');

    $.ajax({
        type: "GET",
        dataType: 'json',
        url: "../lib/l-ptsAjax.php",
        data: "function=get_ProvisionSKU",
        success: function (result) {
            $("#addCustSKUS_error").html('*');
            if ($.trim(result.status) === "SUCCESS") {
                var optionStr = '';
                for (i = 0; i < result.records.length; i++) {
                    optionStr += '<option value=' + result.records[i].skuRef + '>' + result.records[i].description + '</option>';
                }

                $("#addCustSKUS").html(optionStr);
                $(".selectpicker").selectpicker("refresh");

            } else {
                $("#addCustSKUS").html('<option value="0">Sku Not Available</option>');
                $(".selectpicker").selectpicker("refresh");
            }

        },
        error: function (result) {

        }
    });
}

/*  */
function getRenewSkuList(skuRef) {

    $.ajax({
        type: "GET",
        dataType: 'json',
        url: "../lib/l-ptsAjax.php",
        data: "function=get_RenewUpgradeSKUForCountry&skuRef=" + skuRef + "&skuType=2",
        success: function (result) {
            if ($.trim(result.status) === "SUCCESS") {
                var optionStr = '';
                for (i = 0; i < result.records.length; i++) {
                    optionStr += '<option value=' + result.records[i].skuRef + '>' + result.records[i].description + '</option>';
                }
                $("#renewCustSKUS").html(optionStr);
                $(".selectpicker").selectpicker("refresh");
            } else {
                $("#renewCustSKUS").html('<option value="0">Sku Not Available</option>');
                $(".selectpicker").selectpicker("refresh");
            }

        },
        error: function (result) {

        }
    });

}

/*  */
function getUpgradeSkuList(skuRef) {
    $.ajax({
        type: "GET",
        dataType: 'json',
        url: "../lib/l-ptsAjax.php",
        data: "function=get_RenewUpgradeSKUForCountry&skuRef=" + skuRef + "&skuType=3",
        success: function (result) {
            if ($.trim(result.status) === "SUCCESS") {
                var optionStr = '';
                for (i = 0; i < result.records.length; i++) {
                    optionStr += '<option value=' + result.records[i].skuRef + '>' + result.records[i].description + '</option>';
                }
                $("#upgradeCustSKUS").html(optionStr);
                $(".selectpicker").selectpicker("refresh");
            } else {
                $("#upgradeCustSKUS").html('<option value="0">Sku Not Available</option>');
                $(".selectpicker").selectpicker("refresh");
            }

        },
        error: function (result) {

        }
    });

}

function getSkuPrices() {
    $("#priceTableContent").html('<img style="margin-left: 43%;" src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
    var addPlatform = $("#addPlatform").val();
    var selectCountry = $("#addCustCountry").val();
    var selectSku = $("#addCustSKUS").val();
    var skuType = 3;
    $(".error").html("*");
    if (addPlatform == "") {
        $("#addPlatform_error").html("Please select platform.");
        $("#priceTableContent").html('');
    } else if (selectCountry == "") {
        $("#addCustCountry_error").html("Please select Country.");
        $("#priceTableContent").html('');
    } else if (selectSku == "") {
        $("#addCustSKUS_error").html("Please select sku.");
        $("#priceTableContent").html('');
//        setTimeout(function() {
//            $("#addCustSKUS_error").fadeOut(2000)
//        }, 1000);
    } else if (selectSku == undefined) {
        $("#addCustSKUS_error").html("Please select sku.");
        $("#priceTableContent").html('');
//        setTimeout(function() {
//            $("#addCustSKUS_error").fadeOut(2000)
//        }, 1000);
    } else {
        $("#addCustSKUS_error").html("*");
        $.ajax({
            type: "GET",
            dataType: 'text',
            url: "../lib/l-ptsAjax.php",
            data: "function=get_SKUSPrices&sel_skus=" + selectSku + "&skuType=" + skuType,
            success: function (response) {
                $("#priceTableContent").html($.trim(response));
                $("#addNewProvisionSubmit").show();
            },
            error: function (result) {

            }
        });
    }
}

function getUpdatedSkuPrices(id, skuType) {

    var parentid = $("#getupdatedpricebutton").parent().attr('id');

    var item = {};
    var validnoofpc = true;
    $('#' + parentid + ' .newProvisionLicenseRef').each(function () {
        var siblingValue = $(this).siblings('.newProvisionLicenseCount').val();
        if (isNaN(siblingValue) || siblingValue <= 0) {
            $("#getupdatedpricebutton").siblings('span').html("Quantity should be minimum 1.");
            validnoofpc = false;
        } else {
            item [$(this).val()] = siblingValue;
        }

    });
    var jsonString = JSON.stringify(item);

    if (validnoofpc === true) {
        $("#" + parentid).html('<img style="margin-left: 43%;" src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        $.ajax({
            type: "GET",
            dataType: 'text',
            url: "../lib/l-ptsAjax.php",
            data: "function=get_UpdatedSKUSPrices&sel_skus=" + jsonString + "&skuType=" + skuType,
            success: function (response) {

                $("#" + parentid).html($.trim(response));
                if (parentid == "priceTableContent") {
                    $("#addNewProvisionSubmit").show();
                }
            },
            error: function (result) {

            }
        });
    }

}

function getRenewSkuPrices() {
    $("#renewCustSKUS_error").html("*");
    var skuType = 8;

    $("#renewPriceTableContent").html('<img style="margin-left: 43%;" src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
    var selectSku = $("#renewCustSKUS").val();

    if (selectSku == "0") {
        $("#getRenewPricesSkuError").show();
        $("#renewPriceTableContent").html('');
    } else if (selectSku == undefined) {
        $("#getRenewPricesSkuError").show();
        $("#renewPriceTableContent").html('');
    } else {
        $("#getRenewPricesSkuError").hide();
        $.ajax({
            type: "GET",
            dataType: 'text',
            url: "../lib/l-ptsAjax.php",
            data: "function=get_SKUSPrices&sel_skus=" + selectSku + "&skuType=" + skuType,
            success: function (response) {
                $("#renewPriceTableContent").html($.trim(response));
            },
            error: function (result) {

            }
        });
    }
}

function getUpgradeSkuPrices() {
    $("#upgradeCustSKUS_error").html("*");
    var $skuType = 10;
    $("li.third-item").siblings().css("background-color", "red");
    $("#upgradePriceTableContent").html('<img style="margin-left: 43%;" src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
    var selectSku = $("#upgradeCustSKUS").val();

    if (selectSku == "0") {
        $("#getRenewPricesSkuError").show();
        $("#renewPriceTableContent").html('');
    } else if (selectSku == undefined) {
        $("#getRenewPricesSkuError").show();
        $("#renewPriceTableContent").html('');
    } else {
        $("#getRenewPricesSkuError").hide();
        $.ajax({
            type: "GET",
            dataType: 'text',
            url: "../lib/l-ptsAjax.php",
            data: "function=get_SKUSPrices&sel_skus=" + selectSku + "&skuType=" + $skuType,
            success: function (response) {
                $("#upgradePriceTableContent").html($.trim(response));
            },
            error: function (result) {

            }
        });
    }
}

function addNewProvision() {
    //var odate = $("#addCustOrderDate").val();
    //console.log("order date--->"+odate);
    var invalidErrorFlag = '';
    $("#successProvisionButtons").hide();
    invalidErrorFlag = validateNewProvisionForm();
    if (invalidErrorFlag == true) {
        //var vals;
        if ($("#addCustSKUS").val() == "sku_AqZtnF49kysSHu") {
            $("#chkPayment-btn").css("display", "none");
        }
        //var item = {};
        /*$('#priceTableContent .newProvisionLicenseRef').each(function () {
         item [$(this).val()] = $(this).siblings('.newProvisionLicenseCount').val();
         
         if ($(this).siblings('.newProvisionLicenseCount').val() < '0') {
         $("#getupdatedpricebutton").siblings('span').html("Quantity should be minimum 1.");
         vals = '0';
         } else {
         
         vals = '1';
         }
         });*/
        //if (vals === '1') {
        //var jsonString = JSON.stringify(item);
        $("#newProvisionErrorMsg").html('<img style="margin-left: 43%;" src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');

        var provisionData = new FormData();
        provisionData.append('ccode', $("#addCustCountry").val());
        provisionData.append('skuplan', $("#addCustSKUS").val());
        provisionData.append('custNumber', $("#addCustCustomerNumber").val());
        provisionData.append('orderNumber', $("#addCustOrderNumber").val());
        provisionData.append('custFName', $("#addCustFirstName").val());
        provisionData.append('custLName', $("#addCustLastName").val());
        provisionData.append('custEmail', $("#addCustEmailId").val());
        provisionData.append('custPhone', $("#addCustPhoneNo").val());
        //provisionData.append('RemoteURL', $("#addRemoteURL").val());
        provisionData.append('orderDate', $("#addCustOrderDate").val());
        provisionData.append('ServiceDate', $("#addServiceDate").val());
        provisionData.append('osType', $("#addPlatform").val());
        provisionData.append('csrfMagicToken', csrfMagicToken);
        //provisionData.append('skunoofpc', jsonString);

        $.ajax({
            type: "POST",
            dataType: 'json',
            processData: false,
            contentType: false,
            url: "../lib/l-ptsAjax.php?function=create_NewProvision",
            data: provisionData,
            success: function (result) {
                $("#newProvisionErrorMsg").html('');
                if ($.trim(result.status) == "SUCCESS" && $.trim(result.trial) == 1) {
                    get_PTSCustomers();
                    //checkPayment('newProvision', 'successProvisionButtons', 'new_prov_download_url', 'addCustCustomerNumber', 'addCustOrderNumber');
                    $("#new_prov_download_url").val($.trim(result.link));
                    $("#successProvisionButtons").show();
                    $('#successProvisionButtons:gt(1)').find('button').hide();
                    $("#successProvisionButtons span").css("color", "#00e600").html("Please use below link to download nanoheal client.");
                } else if ($.trim(result.status) == "SUCCESS") {
                    get_PTSCustomers();
                    $("#new_prov_download_url").val($.trim(result.link));
                    $("#successProvisionButtons").show();
                } else {
                    $("#getupdatedpricebutton").siblings('span').html('');
                    $("#successProvisionButtons").hide();
                    $("#newProvisionErrorMsg").html($.trim(result.message));
                }
            },
            error: function (result) {

            }
        });
        //}
    } else {
        console.log('Some Error Occured');
    }

}

function validateNewProvisionForm() {
    $('.error').html('*');
    $('.optionalError').html('');
    var invalidErrorFlag = true;
    //var country_value = $("#addCustCountry").val();
    var sku_value = $("#addCustSKUS").val();
    var PhoneNum = $("#addCustPhoneNo").val();
    //var RemoteURL = $("#addRemoteURL").val();
    /*console.log("country_value---->" + country_value);
     if (country_value == "" || country_value == 0 || country_value === "0") {
     console.log("inner level country_value---->" + country_value);
     $("#addCustCountry_error").html("Please select country.");
     invalidErrorFlag = false;
     }*/

    if (sku_value == null || sku_value == "" || sku_value == 0) {
        $("#addCustSKUS_error").html("Please select sku.");
        invalidErrorFlag = false;
    }

    $('#pts_CreateProvision .addCustReq').each(function () {
        var error_id = this.id;
        var field_id = error_id.replace("_error", "");

        var field_value = $("#" + field_id).val();
        if ($.trim(field_value) === "") {
            var label = $('label[for="' + field_id + '"]').html();
            $("#" + field_id + '_error').html("Please enter " + label);
            invalidErrorFlag = false;
        } else {
            if (field_id == 'addCustCustomerNumber' && !validate_alphanumeric_noSpecial(field_value)) {//validate_Number
                $("#" + field_id + '_error').html("Please enter valid Support/Contract No");
                return invalidErrorFlag = false;
            }

            if (field_id == 'addCustCustomerNumber' && !validate_AlphaNumericLenght(field_value)) {
                $("#" + field_id + '_error').html("minimum length should be 8 & maximum 16");
                return invalidErrorFlag = false;
            }

            if (field_id == 'addCustOrderNumber' && !validate_alphanumeric_noSpecial(field_value)) {
                $("#" + field_id + '_error').html("Please enter valid Work order No");
                return invalidErrorFlag = false;
            }

            if (field_id == 'addCustOrderNumber' && !validate_AlphaNumericLenght(field_value)) {
                $("#" + field_id + '_error').html("minimum length should be 8 & maximum 16");
                return invalidErrorFlag = false;
            }

            if (field_id == 'addCustFirstName' && !validate_Alphanumeric(field_value)) {
                $("#" + field_id + '_error').html("Please enter valid first name");
                return invalidErrorFlag = false;
            }

            if (field_id == 'addCustLastName' && !validate_Alphanumeric(field_value)) {
                $("#" + field_id + '_error').html("Please enter valid last name");
                return invalidErrorFlag = false;
            }

            if (field_id == 'addCustEmailId' && !validate_Email(field_value)) {
                $("#" + field_id + '_error').html("Please enter valid customer email");
                return invalidErrorFlag = false;
            }

            /*if (field_id == 'addCustPhoneNo' && !validate_Number(field_value)) {
             $("#" + field_id + '_error').html("Please enter valid phone number");
             return invalidErrorFlag = false;
             }*/
        }

    });

    if (PhoneNum != "") {
        if (!validate_Number(PhoneNum)) {
            $("#addCustPhoneNo_error").html("Please enter valid Customer Phone No");
            invalidErrorFlag = false;
        }

    }

    /*if (RemoteURL != "") {
     var pattern = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
     if (!pattern.test(RemoteURL)) {
     $("#addRemoteURL_error").html("Please enter valid Remote Session URL");
     invalidErrorFlag = false;
     }
     
     }*/
    return invalidErrorFlag;

}

function get_CustomerDetails() {
    var custRowId = $('#pts_Order_Grid tbody tr.selected').attr('id');
    var compId = getRowIdValue(custRowId, 0, '---');
    var pId = getRowIdValue(custRowId, 1, '---');
    var custNumber = getRowIdValue(custRowId, 2, '---');
    var orderNumber = getRowIdValue(custRowId, 3, '---');


    var customerData = new FormData();
    customerData.append('compId', compId);
    customerData.append('proccessId', pId);
    customerData.append('custNumber', custNumber);
    customerData.append('orderNumber', orderNumber);
    customerData.append('csrfMagicToken', csrfMagicToken);

    $.ajax({
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
        url: "../lib/l-ptsAjax.php?function=get_CustomerDetails",
        data: customerData,
        success: function (result) {
            $("#entitle_custNumber").val($.trim(result.details.customerNum));
            $("#entitle_custNumber").attr('title', $.trim(result.details.customerNum));

            $("#entitle_ordNumber").val($.trim(result.details.orderNum));
            $("#entitle_ordNumber").attr('title', $.trim(result.details.orderNum));

            $("#entitle_patmentId").val($.trim(result.details.paymentId));
            $("#entitle_patmentId").attr('title', $.trim(result.details.paymentId));

            $("#entitl_Order").val($.trim(result.details.orderNum));
            $("#entitl_Order").attr('title', $.trim(result.details.orderNum));

            $("#entitl_AgentId").val($.trim(result.details.agentId));
            $("#entitl_AgentId").attr('title', $.trim(result.details.agentId));

            $("#entitl_Statu").val($.trim(result.details.coustomerFirstName));
            $("#entitl_Statu").attr('title', $.trim(result.details.coustomerFirstName));

            //var tempPayRef = result.details.payRefNum;
            //console.log("--result.trial---->" + result.trial);
            /*if ($.trim(result.trial) == '1' || $.trim(result.trial) == 1) {
             $("#entitl_PayStatusButton").show();
             console.log("--if--");
             $("#entitl_PayStatus").css("color", "#00e600").val('Trial');
             $("#entitl_PayStatusButton").css("font-size", "11px").html("Regenerate");
             } else if (tempPayRef == null || tempPayRef == 'null' || tempPayRef == '') {
             $("#entitl_PayStatusButton").hide();
             console.log("--else if--");
             $("#entitl_PayStatus").css("color", "red").val('Pending');
             $("#entitl_PayStatusButton").css("font-size", "16px").html("Payment");
             } else {
             console.log("--else--");
             $("#entitl_PayStatusButton").show();
             $("#entitl_PayStatus").css("color", "#00e600").val('Done');
             $("#entitl_PayStatusButton").css("font-size", "11px").html("Regenerate");
             }*/

            $("#entitl_OrderDate").val($.trim(result.details.orderDate1));
            $("#entitl_OrderDate").attr('title', $.trim(result.details.orderDate1));

            $("#entitl_SkuDesc").val($.trim(result.details.SKUDesc));
            $("#entitl_SkuDesc").attr('title', $.trim(result.details.SKUDesc));

            $("#entitl_ServiceEDate").val($.trim(result.details.contractEndDate));
            $("#entitl_ServiceEDate").attr('title', $.trim(result.details.contractEndDate));

            $("#entitl_CustEmail").val($.trim(result.details.emailId));
            $("#entitl_CustEmail").attr('title', $.trim(result.details.emailId));

            //console.log("result.orderstatus---->"+result.orderstatus);
            if ($.trim(result.orderstatus) == 'Expired' || $.trim(result.orderstatus) == 'CANCEL') {
                $("#entitl_PayStatusButton").hide();
                $("#entitl_PayStatusButton").css("font-size", "11px").html("Regenerate");
                $("#entitl_RevokeButton").hide();
                $("#entitl_RevokeButton").css("font-size", "11px").html("Revoke");
                $("#entitl_LisenseKey").val($.trim(result.orderstatus));
                $("#entitl_LisenseKey").attr('title', $.trim(result.orderstatus));
            } else if ($.trim(result.orderstatus) == 'Unistalled-Active') {
                $("#entitl_PayStatusButton").show();
                $("#entitl_PayStatusButton").css("font-size", "11px").html("Regenerate");
                $("#entitl_RevokeButton").show();
                $("#entitl_RevokeButton").css("font-size", "11px").html("Revoke");
                $("#entitl_LisenseKey").val("Unistalled");
                $("#entitl_LisenseKey").attr('title', "Unistalled");
            } else if ($.trim(result.orderstatus) == 'Install') {
                $("#entitl_PayStatusButton").hide();
                $("#entitl_PayStatusButton").css("font-size", "11px").html("Regenerate");
                $("#entitl_RevokeButton").show();
                $("#entitl_RevokeButton").css("font-size", "11px").html("Revoke");
                $("#entitl_LisenseKey").val("Active");
                $("#entitl_LisenseKey").attr('title', "Active");
            } else if ($.trim(result.orderstatus) == 'NotInstall') {
                $("#entitl_PayStatusButton").show();
                $("#entitl_PayStatusButton").css("font-size", "11px").html("Regenerate");
                $("#entitl_RevokeButton").hide();
                $("#entitl_RevokeButton").css("font-size", "11px").html("Revoke");
                $("#entitl_LisenseKey").val("Active");
                $("#entitl_LisenseKey").attr('title', "Active");
            } else if ($.trim(result.revokeStatus) == 'R') {
                $("#entitl_PayStatusButton").show();
                $("#entitl_PayStatusButton").css("font-size", "11px").html("Regenerate");
                $("#entitl_RevokeButton").hide();
                $("#entitl_RevokeButton").css("font-size", "11px").html("Revoke");
                $("#entitl_LisenseKey").val("Active");
                $("#entitl_LisenseKey").attr('title', "Active");
            } else {
                $("#entitl_PayStatusButton").show();
                $("#entitl_PayStatusButton").css("font-size", "11px").html("Regenerate");
                $("#entitl_RevokeButton").hide();
                $("#entitl_RevokeButton").css("font-size", "11px").html("Revoke");
                $("#entitl_LisenseKey").val($.trim(result.orderstatus));
                $("#entitl_LisenseKey").attr('title', $.trim(result.orderstatus));
            }

            $("#entitl_Quantity").val($.trim(result.details.noOfPc));
            setOrderHistory(result.history);
            $("#pts_Entitlement .popupLoader").hide();
            $("#pts_Entitlement .clearfix").show();

        },
        error: function (result) {

        }
    });
}

function setOrderHistory(history) {
//    alert(history.length);
    var rowStr = '';
    $("#entitlement_OrderHistory tbody").empty();
    rowStr += '<tr><th width="5%">&nbsp;</th><th>SKU Desc:</th><th>Order Date:</th><th>Work order no:</th><th>Agent Id:</th></tr>';
    for (i = 0; i < history.length; i++) {
        rowStr += '<tr><td width="5%">&nbsp;</td><td><span class="ellipsis">' + history[i].SKUDesc + '</span></td><td><span class="ellipsis">' + history[i].orderDate + '</span></td><td><span class="ellipsis">' + history[i].orderNum + '</span></td><td><span class="ellipsis">' + history[i].agentId + '</span></td></tr>';
    }
    $('#entitlement_OrderHistory > tbody:last-child').append(rowStr);
}



function exportDetails() {

    $.ajax({
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
        data: { 'csrfMagicToken': csrfMagicToken },
        url: "../lib/l-ptsAjax.php?function=get_AllCustomerList",
        success: function (result) {
            //console.log(result);
//                console.log(result[0].customerNum);
//                console.log(result[0]);
//                console.log(result.length);
            /*var i, records = '';
             
             for (i = 0; i < result.length; i++) {
             records += '<option value="' + result[i].customerNum + '">' + result[i].customerNum + '</option>';
             //                    console.log(records);
             }*/
            $("#cust_numbLists").html("");
            $("#cust_numbLists").html(result);
            $(".selectpicker").selectpicker("refresh");
        },
        error: function (result) {

        }
    });
}

/*function get_OrderNummbers() {
    var custNum = $("#cust_numbLists").val();
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "../lib/l-ptsAjax.php?function=get_OrderNumber",
        data: {'custNum': custNum},
        success: function (result) {
            var i = '';
 // var records = '<option value="all">All</option>';   //One more option needed to provide as All.
 // for (i = 0; i < result.orders.length; i++) {
 // records += '<option value="' + result.orders[i] + '">' + result.orders[i] + '</option>';
 // }
 $("#cust_OrderLists").html(result);
 $(".selectpicker").selectpicker("refresh");
 
 },
 error: function (result) {
 
 }
 });
             }*/

$("#cust_numbLists").focusout(function () {
//    console.log("inside");
    var custNum = $('#cust_numbLists').val();
    $("#cust_OrderLists").html('<option>Loading...</option>');
    $(".selectpicker").selectpicker("refresh");
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "../lib/l-ptsAjax.php?function=get_OrderNumber",
        data: {
            'custNum': custNum,
            'csrfMagicToken': csrfMagicToken},
        success: function (result) {
            var i = '';
            // var records = '<option value="all">All</option>';   //One more option needed to provide as All.
            // for (i = 0; i < result.orders.length; i++) {
            // records += '<option value="' + result.orders[i] + '">' + result.orders[i] + '</option>';
            // }
            $("#cust_OrderLists").html('');
            $("#cust_OrderLists").html(result);
            $(".selectpicker").selectpicker("refresh");

        },
        error: function (result) {

        }
    });
//    }  
});

function setRenewDetails() {
    var rowId = $('#pts_Order_Grid tbody tr.selected').attr('id');
    var customerData = new FormData();
    var orderId = getRowIdValue(rowId, 5, '---');
    customerData.append('orderId', orderId);
    customerData.append('csrfMagicToken', csrfMagicToken);

    $.ajax({
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
        url: "../lib/l-ptsAjax.php?function=get_RenewDetails",
        data: customerData,
        success: function (response) {
            $("#renewCustomerNumber").val($.trim(response.customerNum));
            $("#renewOldOrder").val($.trim(response.orderNum));
            $("#renewEmail").val($.trim(response.emailId));
            $("#oldOrderDate").val($.trim(response.orderDate));
            $("#renewSkuDesc").val($.trim(response.SKUDesc));
            $("#renewSkuRef").val($.trim(response.SKUNum));
            getRenewSkuList(response.SKUNum);
            $("#pts_RenewOrder .popupLoader").hide();
            $("#pts_RenewOrder .clearfix").show();

        },
        error: function (result) {

        }
    });
}

function setUpgradeDetails() {
    var rowId = $('#pts_Order_Grid tbody tr.selected').attr('id');
    var customerData = new FormData();
    var orderId = getRowIdValue(rowId, 5, '---');
    customerData.append('orderId', orderId);
    customerData.append('csrfMagicToken', csrfMagicToken);

    $.ajax({
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
        url: "../lib/l-ptsAjax.php?function=get_RenewDetails",
        data: customerData,
        success: function (response) {
            $("#upgradeCustomerNumber").val($.trim(response.customerNum));
            $("#upgradeOldOrder").val($.trim(response.orderNum));
            $("#upgradeEmail").val($.trim(response.emailId));
            $("#upgradeOldOrderDate").val($.trim(response.orderDate));
            $("#upgradeSkuDesc").val($.trim(response.SKUDesc));
            $("#upgradeSkuRef").val($.trim(response.SKUNum));
            getUpgradeSkuList(response.SKUNum);
            $("#pts_UpgradeOrder .popupLoader").hide();
            $("#pts_UpgradeOrder .clearfix").show();

        },
        error: function (result) {

        }
    });
}

function renewProvision() {
    var invalidErrorFlag = true;
    $("#renewSuccessProvisionButtons").hide();
    var newOrderNumber = $("#newOrderNumber").val();
    var sku_value = $("#renewCustSKUS").val();

    if (sku_value == null || sku_value == "") {
        $("#renewCustSKUS_error").html("Please select sku.");
        return invalidErrorFlag = false;
    }

    if (newOrderNumber == "") {
        $("#newOrderNumber_error").html("Please enter new order number.");
        return invalidErrorFlag = false;
    }

    if (!validate_Number(newOrderNumber)) {
        $("#newOrderNumber_error").html("Please enter valid order number.");
        return invalidErrorFlag = false;
    }


    if (!validate_OrderNumber(newOrderNumber)) {
        $("#newOrderNumber_error").html("Order Number should be of length minimum 8 & maximum 16");
        return invalidErrorFlag = false;
    }



    if (invalidErrorFlag == true) {
        $("#renewProvisionErrorMsg").html('<img style="margin-left: 43%;" src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        $("#pts_RenewOrder .error").html("*");
        var provisionData = new FormData();
        provisionData.append('custNumber', $("#renewCustomerNumber").val());
        provisionData.append('oldOrderNumber', $("#renewOldOrder").val());
        provisionData.append('oldSkuRef', $("#renewSkuRef").val());
        provisionData.append('newOrderNumber', $("#newOrderNumber").val());
        provisionData.append('newSkuRef', $("#renewCustSKUS").val());
        provisionData.append('csrfMagicToken', csrfMagicToken);

        $.ajax({
            type: "POST",
            dataType: 'json',
            processData: false,
            contentType: false,
            url: "../lib/l-ptsAjax.php?function=renew_Provision",
            data: provisionData,
            success: function (result) {
                $("#renewProvisionErrorMsg").html('');
                if ($.trim(result.status) == "SUCCESS") {
                    get_PTSCustomers();
                    $("#renew_prov_download_url").val($.trim(result.link));
                    $("#renewSuccessProvisionButtons").show();
                } else {
                    $("#renewSuccessProvisionButtons").hide();
                    $("#renewProvisionErrorMsg").html($.trim(result.message));

                }
            },
            error: function (result) {

            }
        });
    }
}

function upgradeProvision() {
    var invalidErrorFlag = true;
    var newOrderNumber = $("#newUpgradeOrderNumber").val();
    var sku_value = $("#upgradeCustSKUS").val();
    $("#upgradeSuccessProvisionButtons").hide();

    if (sku_value == null || sku_value == "") {
        $("#upgradeCustSKUS_error").html("Please select sku.");
        return invalidErrorFlag = false;
    }

    if (newOrderNumber == "") {
        $("#newUpgradeOrderNumber_error").html("Please enter new order number.");
        return invalidErrorFlag = false;
    }

    if (!validate_Number(newOrderNumber)) {
        $("#newUpgradeOrderNumber_error").html("Please enter valid new order number.");
        return invalidErrorFlag = false;
    }

    if (!validate_OrderNumber(newOrderNumber)) {
        $("#newUpgradeOrderNumber_error").html("Order Number should be of length minimum 8 & maximum 16");
        return invalidErrorFlag = false;
    }

    if (invalidErrorFlag == true) {
        $("#upgradeProvisionErrorMsg").html('<img style="margin-left: 43%;" src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        $("#pts_UpgradeOrder .error").html("*");
        var provisionData = new FormData();
        provisionData.append('custNumber', $("#upgradeCustomerNumber").val());
        provisionData.append('oldOrderNumber', $("#upgradeOldOrder").val());
        provisionData.append('oldSkuRef', $("#upgradeSkuRef").val());
        provisionData.append('newOrderNumber', $("#newUpgradeOrderNumber").val());
        provisionData.append('newSkuRef', $("#upgradeCustSKUS").val());
        provisionData.append('csrfMagicToken', csrfMagicToken);

        $.ajax({
            type: "POST",
            dataType: 'json',
            processData: false,
            contentType: false,
            url: "../lib/l-ptsAjax.php?function=upgrade_Provision",
            data: provisionData,
            success: function (result) {
                $("#upgradeProvisionErrorMsg").html('');
                if ($.trim(result.status) == "SUCCESS") {
                    get_PTSCustomers();
                    $("#upgrade_prov_download_url").val($.trim(result.link));
                    $("#upgradeSuccessProvisionButtons").show();
                } else {
                    $("#upgradeSuccessProvisionButtons").hide();
                    $("#upgradeProvisionErrorMsg").html($.trim(result.message));

                }
            },
            error: function (result) {

            }
        });
    }
}

function paymentCheckForEntitlement() {
    $(".ordersLabel").css('marging-top', '19px');
    $("#entitleSuccessProvisionButtons #entitlePaySpan1").html("");
    $("#entitleSuccessProvisionButtons #entitlePaySpan2").html("");
    var checkPaymentData = new FormData();
    checkPaymentData.append('paymentId', $("#entitle_patmentId").val());
    checkPaymentData.append('custNumber', $("#entitle_custNumber").val());
    checkPaymentData.append('orderNumber', $("#entitle_ordNumber").val());
    checkPaymentData.append('csrfMagicToken', csrfMagicToken);
    $.ajax({
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
        url: "../lib/l-ptsAjax.php?function=check_Payment",
        data: checkPaymentData,
        success: function (result) {
            if ($.trim(result.status) == "FAILED") {
                $("#entitleSuccessProvisionButtons #entitlePaySpan1").css("color", "#48b2e4;").html("Please use below link to make payment.");
                $("#entitleSuccessProvisionButtons #entitlePaySpan2").css("color", "red").html("Payment not done. Please check information and try again..");
            } else if ($.trim(result.status) == "SUCCESS") {
                $("#entitleSuccessProvisionButtons #entitlePaySpan1").css("color", "#48b2e4;").html("Please use below link to download client.");
//                $("#entitleSuccessProvisionButtons #entitlePaySpan2").css("color", "#00e600;").html("Payment done.");
                $("#payment_status").hide();
            }
            $("#entitle_prov_download_url").val($.trim(result.link))
            $("#entitleSuccessProvisionButtons").show();
        },
        error: function (result) {

        }
    });
}
function RegenerateForEntitlement() {
    $(".ordersLabel").css('marging-top', '19px');
    $("#entitleSuccessProvisionButtons #entitlePaySpan1").html("");
    var checkPaymentData = new FormData();
    //checkPaymentData.append('paymentId', $("#entitle_patmentId").val());
    checkPaymentData.append('custNumber', $("#entitle_custNumber").val());
    checkPaymentData.append('orderNumber', $("#entitle_ordNumber").val());
    checkPaymentData.append('csrfMagicToken', csrfMagicToken);
    $.ajax({
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
        url: "../lib/l-ptsAjax.php?function=check_Regenerate",
        data: checkPaymentData,
        success: function (result) {
            if ($.trim(result.status) == "FAILED") {
                $("#entitleSuccessProvisionButtons #entitlePaySpan1").css("color", "#48b2e4;").html("Error occured.Please try later!!!");

            } else if ($.trim(result.status) == "SUCCESS") {
                $("#entitleSuccessProvisionButtons #entitlePaySpan1").css("color", "#48b2e4;").html("Copy URL to use in customer machine");
                $("#payment_status").hide();
            }
            $("#entitle_prov_download_url").val($.trim(result.link))
            $("#entitleSuccessProvisionButtons").show();
        },
        error: function (result) {

        }
    });
}

function RevokeForEntitlement() {
    $(".ordersLabel").css('marging-top', '19px');
    $("#entitleSuccessProvisionButtons #entitlePaySpan1").html("");
    var checkPaymentData = new FormData();
    //checkPaymentData.append('paymentId', $("#entitle_patmentId").val());
    checkPaymentData.append('custNumber', $("#entitle_custNumber").val());
    checkPaymentData.append('orderNumber', $("#entitle_ordNumber").val());
    checkPaymentData.append('csrfMagicToken', csrfMagicToken);
    $.ajax({
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
        url: "../lib/l-ptsAjax.php?function=check_Revoke",
        data: checkPaymentData,
        success: function (result) {
            if ($.trim(result.status) == "FAILED") {
                $("#entitleSuccessProvisionButtons #entitlePaySpan1").css("color", "#48b2e4;").html("Error occured.Please try later!!!");
            } else if ($.trim(result.status) == "SUCCESS") {
                $("#entitleSuccessProvisionButtons #entitlePaySpan1").css("color", "#48b2e4;").html("Copy URL to use in customer machine");
                $("#payment_status").hide();
            }
            $("#entitle_prov_download_url").val($.trim(result.link))
            $("#entitleSuccessProvisionButtons").show();
        },
        error: function (result) {

        }
    });
}

function checkPayment(paymentType, divId, urlFieldId, customerNumId, orderNumId) {
    var paymentUrl = $("#" + urlFieldId).val();
    $("#" + divId + " span").html("");
    if (paymentUrl.indexOf("eula") >= 0) {
        $("#" + divId + " span").css("color", "#00e600").html("Payment done, please copy below client download url.");
    } else {
        $("#" + divId + " span").html('<img style="margin-left: 43%;" src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        var checkPaymentData = new FormData();
        var paymentArray = paymentUrl.split("=");
        checkPaymentData.append('paymentId', paymentArray[1]);
        checkPaymentData.append('custNumber', $("#" + customerNumId).val());
        checkPaymentData.append('orderNumber', $("#" + orderNumId).val());
        checkPaymentData.append('paymentType', paymentType);
        checkPaymentData.append('csrfMagicToken', csrfMagicToken);

        $.ajax({
            type: "POST",
            dataType: 'json',
            processData: false,
            contentType: false,
            url: "../lib/l-ptsAjax.php?function=check_Payment",
            data: checkPaymentData,
            success: function (result) {
                if ($.trim(result.status) == "FAILED") {
                    $("#" + divId + " span").html('');
                    $("#" + divId + " span").css("color", "red").html("Please use below link to make payment.");
                    $("#" + divId + " span2").css("color", "red").html("Please use below link to make payment.");
                } else if ($.trim(result.status) == "SUCCESS") {
                    $("#" + divId + " span").css("color", "#00e600").html("Payment done, please copy below client download url.");
                }
                $("#" + urlFieldId).val($.trim(result.link));
            },
            error: function (result) {

            }
        });
    }

}

function checkUpgradePayment(customerNumId, orderNumId, paymentType) {
    var paymentUrl = $("#upgrade_prov_download_url").val();
    $("#upgradeSuccessProvisionSpan").html("");
    if (paymentUrl.indexOf("eula") >= 0) {
        $("#upgradeSuccessProvisionSpan").css("color", "#00e600").html("Payment done, please copy below client download url.");
    } else {
        $("#upgradeSuccessProvisionSpan").html('<img style="margin-left: 43%;" src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        var checkPaymentData = new FormData();
        var paymentArray = paymentUrl.split("=");
        checkPaymentData.append('paymentId', paymentArray[1]);
        checkPaymentData.append('custNumber', $("#" + customerNumId).val());
        checkPaymentData.append('orderNumber', $("#" + orderNumId).val());
        checkPaymentData.append('paymentType', paymentType);
        checkPaymentData.append('csrfMagicToken', csrfMagicToken);

        $.ajax({
            type: "POST",
            dataType: 'json',
            processData: false,
            contentType: false,
            url: "../lib/l-ptsAjax.php?function=check_Payment",
            data: checkPaymentData,
            success: function (result) {
                if ($.trim(result.status) == "FAILED") {
                    $("#upgradeSuccessProvisionSpan").css("color", "red").html("Please use below link to make payment.");
                } else if ($.trim(result.status) == "SUCCESS") {
                    $("#upgradeSuccessProvisionSpan").css("color", "#00e600").html("Payment done, please copy below client download url.");
                }
                $("#upgrade_prov_download_url").val($.trim(result.link));
            },
            error: function (result) {

            }
        });
    }

}

function checkEntitlementPayment(paymentType, divId, urlFieldId, customerNumId, orderNumId) {
    $("#" + divId + " #entitlePaySpan1").html("");
    $("#" + divId + " #entitlePaySpan2").html("");
    var paymentUrl = $("#" + urlFieldId).val();
    $("#" + divId + " span").html("");
    if (paymentUrl.indexOf("eula") >= 0) {
//        $("#" + divId + " span").css("color", "#00e600").html("Payment done, please copy below client download url.");
    } else {
        $("#" + divId + " span").html('<img style="margin-left: 43%;" src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        var checkPaymentData = new FormData();
        var paymentArray = paymentUrl.split("=");
        checkPaymentData.append('paymentId', paymentArray[1]);
        checkPaymentData.append('custNumber', $("#" + customerNumId).val());
        checkPaymentData.append('orderNumber', $("#" + orderNumId).val());
        checkPaymentData.append('paymentType', paymentType);
        checkPaymentData.append('csrfMagicToken', csrfMagicToken);
        $.ajax({
            type: "POST",
            dataType: 'json',
            processData: false,
            contentType: false,
            url: "../lib/l-ptsAjax.php?function=check_Payment",
            data: checkPaymentData,
            success: function (result) {
                if ($.trim(result.status) == "FAILED") {
                    $("#" + divId + " #entitlePaySpan1").css("color", "#48b2e4").html("Please use below link to make payment.");
                    $("#" + divId + " #entitlePaySpan2").css("color", "red").html("Payment not done. Please check information and try again");
                } else if ($.trim(result.status) == "SUCCESS") {
                    $("#" + divId + " span").css("color", "#00e600").html("");
//                    $("#" + divId + " span").css("color", "#00e600").html("Payment done, please copy below client download url.");
                    $("#" + divId + " #entitlePaySpan1").css("color", "#00e600").html("Payment done, please copy below client download url.");
                }
                $("#" + urlFieldId).val($.trim(result.link));
            },
            error: function (result) {

            }
        });
    }

}
function sendMailForEntitlement() {
    var custEmailId = $("#entitl_CustEmail").val();
    var custRowId = $('#pts_Order_Grid tbody tr.selected').attr('id');
    var customerNumber = getRowIdValue(custRowId, 2, '---');
    var orderNumber = getRowIdValue(custRowId, 3, '---');

    var checkPaymentData = new FormData();
    checkPaymentData.append('emailId', custEmailId);
    checkPaymentData.append('custNumber', customerNumber);
    checkPaymentData.append('orderNumber', orderNumber);
    checkPaymentData.append('csrfMagicToken', csrfMagicToken);

    $.ajax({
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
        url: "../lib/l-ptsAjax.php?function=send_PaymentMail",
        data: checkPaymentData,
        success: function (result) {
//            console.log(result);
            if ($.trim(result.status) == "SUCCESS") {
                $("#entitlePaySpan1").css("color", "#00e600").html("Mail Sent successfully.");
            } else if ($.trim(result.status) == "FAILED") {
                $("#entitlePaySpan1").css("color", "red").html("Mail not sent, please try again later.");
            }
        },
        error: function (result) {

        }
    });

}

function sendMailForPayment(divId, emailfieldId, customerNumId, orderNumId) {
    var checkPaymentData = new FormData();
    checkPaymentData.append('emailId', $("#" + emailfieldId).val());
    checkPaymentData.append('custNumber', $("#" + customerNumId).val());
    checkPaymentData.append('orderNumber', $("#" + orderNumId).val());
    checkPaymentData.append('csrfMagicToken', csrfMagicToken);
    $.ajax({
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
        url: "../lib/l-ptsAjax.php?function=send_PaymentMail",
        data: checkPaymentData,
        success: function (result) {
            if ($.trim(result.status) == "SUCCESS") {
                $("#" + divId + " span").css("color", "#00e600").html("Mail Sent successfully.");
            } else if ($.trim(result.status) == "FAILED") {
                $("#" + divId + " span").css("color", "red").html("Mail not sent, please try again later.");
            }
        },
        error: function (result) {

        }
    });
}
function addDeviceIncrese() {

    $("#addDeviceErrorMsg").html("");
    $('#addDevice_noofpc').val(function (i, oldval) {
        var max = $('#addDevice_noofpc').attr("max");
        if (oldval === max) {
            $("#addDeviceErrorMsg").html("<span style='color:red'>Only 5 devices can be added</span>");
            return oldval;
        } else {
            return ++oldval;
        }
    });
}

function addDeviceDecrease() {
    $("#addDeviceErrorMsg").html("");
    $('#addDevice_noofpc').val(function (i, oldval) {
        var min = $('#addDevice_noofpc').attr("min");
        if (oldval === min) {
            $("#addDeviceErrorMsg").html("<span style='color:red'>atleast 1 devices can be added</span>");
            return oldval;
        } else {
            return --oldval;
        }
    });
}
//$("#addDeviceIncrese").on("click", function () {
//
//    $("#addDeviceErrorMsg").html("");
//    $('#addDevice_noofpc').val(function (i, oldval) {
//        var max = $('#addDevice_noofpc').attr("max");
//        if (oldval == max) {
//            $("#addDeviceErrorMsg").html("<span style='color:red'>Only 5 devices can be added</span>");
//            return oldval;
//        } else {
//            return ++oldval;
//        }
//    });
//});
//
//$("#addDeviceDecrease").bind("click", function () {
//    $("#addDeviceErrorMsg").html("");
//    $('#addDevice_noofpc').val(function (i, oldval) {
//        var min = $('#addDevice_noofpc').attr("min");
//        if (oldval == min) {
//            $("#addDeviceErrorMsg").html("<span style='color:red'>atleast 1 devices can be added</span>");
//            return oldval;
//        } else {
//            return --oldval;
//        }
//    });
//});

function setCustomerDeviceDetails() {

    var custRowId = $('#pts_Order_Grid tbody tr.selected').attr('id');
    var compId = getRowIdValue(custRowId, 0, '---');
    var pId = getRowIdValue(custRowId, 1, '---');
    var custNumber = getRowIdValue(custRowId, 2, '---');
    var orderNumber = getRowIdValue(custRowId, 3, '---');

    var customerData = new FormData();
    customerData.append('compId', compId);
    customerData.append('proccessId', pId);
    customerData.append('custNumber', custNumber);
    customerData.append('orderNumber', orderNumber);
    customerData.append('csrfMagicToken', csrfMagicToken);
    $.ajax({
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
        url: "../lib/l-ptsAjax.php?function=get_CustomerDetails",
        data: customerData,
        success: function (result) {
            $("#addDeviceErrorMsg").html("");
            $("#addDevice_CustomerNumber").val($.trim(result.details.customerNum));
            $("#addDevice_OrderNumber").val($.trim(result.details.orderNum));
            $("#addDevice_Email").val($.trim(result.details.emailId));
            $("#addDevice_ContractEDate").val($.trim(result.details.contractEndDate));
            $("#addDevice_noofpc").val($.trim(result.details.noOfPc));
            $("#addDevice_noofpc").attr({"max": 5, "min": result.details.noOfPc});
        },
        error: function (result) {

        }
    });
}

function addDevices() {
    var customerData = new FormData();
    customerData.append('custNumber', $("#addDevice_CustomerNumber").val());
    customerData.append('orderNumber', $("#addDevice_OrderNumber").val());
    customerData.append('noofpc', $("#addDevice_noofpc").val());
    customerData.append('csrfMagicToken', csrfMagicToken);
    $("#addDeviceErrorMsg").html("");
    $.ajax({
        type: "POST",
        dataType: 'json',
        processData: false,
        contentType: false,
        url: "../lib/l-ptsAjax.php?function=addDevices",
        data: customerData,
        success: function (result) {
            if ($.trim(result.status) === "SUCCESS") {
                $("#addDeviceErrorMsg").html("<span style='color:green'>Payment link is generated successfully</span>");
                $("#addDeviceButton").hide();
                $("#addDevicePayURL").val($.trim(result.link));
                $("#pts_addDevices .addDeviceSuccessDivs").show();
                $("#pts_addDevices .customscroll").mCustomScrollbar("scrollTo", "bottom");
            } else {
                $("#addDeviceErrorMsg").html("<span style='color:red'>" + $.trim(result.message) + "</span>");
            }
        },
        error: function (result) {

        }
    });
}

function newProvPlus(idNumber) {
    $("#error_provisionPrice").fadeIn();
    $("#getupdatedpricebutton").show();

    $("#getupdatedpricebutton").siblings('span').html("Please click on 'Update Price' button to update the price");
//    $("#error_provisionPrice").html("Please click on 'Update Price' button to update the price");
//    setTimeout(function() {$("#error_provisionPrice").fadeOut(2000)}, 1000);

    var thisval = $("#newProvisionLicenseCount" + idNumber).val();
    thisval++;
    $("#newProvisionLicenseCount" + idNumber).val(thisval);
}

function newProvMinus(idNumber) {
    $("#error_provisionPrice").fadeIn();
    $("#getupdatedpricebutton").show();

    $("#getupdatedpricebutton").siblings('span').html("Please click on 'Update Price' button to update the price");

    setTimeout(function () {
        $("#error_provisionPrice").fadeOut(2000)
    }, 1000);

    var thisval = $("#newProvisionLicenseCount" + idNumber).val();
    if (thisval !== '0') {
        thisval--;
    } else if (thisval === '0') {
        thisval = '0';
    }
    $("#newProvisionLicenseCount" + idNumber).val(thisval);
}

function exportCustomer() {

    $("#error_numbLists").fadeIn();
    $("#error_OrderLists").fadeIn();

    var customerNumber = $("#cust_numbLists").val();
    var orderNumber = $("#cust_OrderLists").val();

    if (customerNumber != '' && orderNumber != '') {
        $("#error_OrderLists").hide();
        $("#error_numbLists").hide();
        window.location = "../lib/l-ptsAjax.php?function=PTSAJX_ExportCustomerDetails&custNumber=" + customerNumber + "&exportType=" + orderNumber;

    } else if (customerNumber == '' || orderNumber == '') {

        if (customerNumber == '') {
            $("#error_numbLists").html("Please select Customer Number");
            $("#error_OrderLists").hide();
            setTimeout(function () {
                $("#error_numbLists").fadeOut(1000)
            }, 1000);
        } else if (orderNumber == '') {
            $("#error_numbLists").hide();
            $("#error_OrderLists").html("Please select Order Number");
            setTimeout(function () {
                $("#error_OrderLists").fadeOut(1000)
            }, 1000);
        }

    }
}

function redirect(macName, siteName, censusid) {

    $('#searchType').val('ServiceTag');
    $('#searchValue').val(macName);
    $('#rparentName').val(siteName);
    $('#passlevel').val('Sites');
    $('#rcensusId').val(censusid);
//   machClick('Sites', siteName, 'Sites', macName, '', status);
    window.location = "../home/index.php?m=" + macName + "&s=" + siteName + "&c=" + censusid;
}

function OpenRemoteLogInPage(remoteType) {

    $.ajax({
        url: "../remote/remote_func.php?function=getUserList&remoteType=" + remoteType,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            $('#usernameEmail').html('');
            $('#pass_hidden').html('');
            $('#pass_hidden_txt').html('');
            $('#usernameEmail').html(data.option);
            $('.selectpicker').selectpicker('refresh');
            $('#pass_hidden').append(data.pass);
            $('#pass_hidden_txt').append(data.passTxt);
            userName = $('#usernameEmail').val();

            var isAdvancePage = advanceGTA;
            var urlPage = "";
            if ($.trim(isAdvancePage) == '0') {
                urlPage = "../remote/index.php?perform=DEFAULT&showPage=NO&remoteType=" + remoteType + "&userName=" + userName;
            } else if ($.trim(isAdvancePage) == '1') {
                urlPage = "../remote/index.php?perform=DEFAULT&showPage=SHOWADVANCE&remoteType=" + remoteType + "&userName=" + userName;
            }

            $.ajax({
                url: urlPage,
                type: 'GET',
                dataType: 'json',
//        data: '{}',
                success: function (data) {
//            alert(data.rmt_result);
                    $('#rmt_result').val(data.rmt_result);//url
                    $('#rmt_loginidc').val(data.rmt_liginid);//loginid
//            $('#rmt_loginidr').val(data.rmt_liginid);
                    $('#rmt_passc').val(data.rmt_pass);//password
                    $('#remoteperform').val(data.perform);
                    $('#remoteshowpage').val(data.showPage);
                    $('#usernameEmail').val(data.option);//email
                    $('.selectpicker').selectpicker('refresh');
                    $('#loginid').val(data.rmt_liginid);//id
                    $("#usernameEmail").parent().removeClass("is-empty");
                    $("#loginid").parent().removeClass("is-empty");
                    $("#remote_url").parent().removeClass("is-empty");
                    $('#remoteact').val(data.act);
                    $('#remotetype').val(data.remoteTyp);
                    $('#emlid').html(data.remoteTyp + " Login ID");
                    $('#emd').html(data.remoteTyp + " Login ID");
                    $('#emp').html(data.remoteTyp + " Password");
                    $('#emr').html(data.remoteTyp + " Login ID");
                    $('#emcp').html(data.remoteTyp + " Confirm Password");
//                $('#err').html("Please Set the " + data.remoteTyp + " Credentials");
                    $('#emdurl').html(data.remoteTyp + " URL");

                    $('#empre').html(data.remoteTyp + " Login ID");
                    $('#emppass').html(data.remoteTyp + " Password");
                    $('#emcpass').html(data.remoteTyp + " Confirm Password");
                    $('#remotewsurl').val(data.wsurl);
                    if (data.perform == 'DEFAULT') {
                        //$('#machineonlineremote').modal('hide');
                        $('#warningdefault').modal('show');
                    }
                    if (data.perform == 'SHOWURL' || (data.perform == 'EXIST' && data.showPage == "NO")) {
                        //$('#machineonlineremote').modal('hide');
                        $('#warningshowurl').modal('show');
                    }
                    if (data.perform == 'EXIST' && data.showPage == "NO") {
                        var rmtloginid = $('#rmt_loginidc').val();
                        var rmtpass = $('#rmt_passc').val();
                        $('#error').css({'color': 'green'}).html('Fetching URL. Please wait...');
                        create_rmt__downloadlink(rmtloginid, rmtpass, "GETURL");
                    }
                    if (data.perform == 'EXIST' && data.showPage == 'SHOWADVANCE') {
                    }
                },
                error: function (request, status, error) {
                    alert('Error ' + request.responseText + JSON.stringify(error));
                }
            });
        }
    });
}

function getPassword_LMI() {
    var email_id = document.getElementById("usernameEmail").value;
    var rmttype = "LMI";

    $.ajax({
        url: "../remote/remote_func.php?function=GETUserPassword&loginid=" + email_id + '&remoteType=' + rmttype,
        type: 'POST',
        success: function (data) {
            //console.log(data);
            $("#rmt_pass").val(data);
        }
    });
}

var emailregex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;




$('#rmt_submit').click(function () {

    

    var clientVer = "";
    var rmttype = $('#remotetype').val();
    $("#err").html('');
    $("#err").show();
//var clientVer;
    if (loginType === 'email') {


        var emailid = $('#usernameEmail').val();
                
        // Edit by Hareesh - 20-12-2018
        // var password = $('#' + emailid.split('@')[0] + '_Txt').val();
        var finpwd = emailid.split('@')[0] + '_Txt';
        var password = $("input[id='" + finpwd + "']").val();
        // End

        // console.log("emailid test-->"+emailid);
        // console.log("password test-->"+password);

        $('#error').css({'color': 'green'}).html('Fetching URL. Please wait...');

        // TO get client version
        get_ClientVersion(function(callback) {            
            clientVer = callback;
            if ((clientVer > 3057) || (clientVer != 3057)) {
                create_rmt__downloadlinkNewVersion(emailid, password, "GETURL");               
    } else {
                create_rmt__downloadlink(emailid, password, "GETURL");
            }    
        });
    } else {
        if ($('#rmt_login').prop('checked') == true) {
            if ($('#rmt_loginid').val() == '') {
                $('#err').html('Please enter the email id');
                setTimeout(function () {
                    $("#err").fadeOut(3600);
                }, 3600);
                return false;
            } else if (!emailregex.test($('#rmt_loginid').val())) {
                $('#err').html('Please enter valid email id');
                return false;
            }
        }
        if ($("#rmt_pass").val() == '') {
            $('#err').html('Please enter the password');
            setTimeout(function () {
                $("#err").fadeOut(3600);
            }, 3600);
            return false;
        } else if ($("#rmt_passconf").val() == '') {
            $('#err').html('Please confirm the password');
            setTimeout(function () {
                $("#err").fadeOut(3600);
            }, 3600);
            return false;
        } else if ($('#rmt_pass').val() != $('#rmt_passconf').val()) {
            $('#err').html('The passwords do not match');
            setTimeout(function () {
                $("#err").fadeOut(3600);
            }, 3600);
            return false;
        } else {
            var emailid = '';
            if ($('#rmt_login').prop('checked') == true) {
                emailid = $('#rmt_loginid').val();
            } else if ($('#agentlogin').prop('checked') == true) {
                emailid = $('#usernameEmail').val();
            } else {
                emailid = $('#rmt_user').val();
            }
            var password = $('#rmt_pass').val();
        }
        $.ajax({
            url: "../remote/remote_func.php?function=checkUserExistance&loginid=" + emailid + '&remoteType=' + rmttype,
            type: 'POST',
            success: function (data) {
                console.log(data);
                data = data.trim();
                var clientVer;
                if (data == 'LOGINEXIST') {
                    $('#err').html('<span style="color:green;">Authenticating ' + rmttype + ' Login. Please wait...</span>');
                                        
                    get_ClientVersion(function(callback) {            
                        clientVer = callback;
                        if ((clientVer > 3057) || (clientVer != 3057)) {
                            create_rmt__downloadlinkNewVersion(emailid, password, "GETURL");
                            getmachineDetails();
                        } else {
                    create_rmt__downloadlink(emailid, password, "GETURL");
                        }
                    });
                } else if (data == 'LOGINADD') {
//                    $('#err').css({'color': 'green'}).html('Authenticating ' + rmttype + ' Login. Please wait...');
                    $('#err').html('<span style="color:green;">Authenticating ' + rmttype + ' Login. Please wait...</span>');
                    
                    get_ClientVersion(function(callback) {            
                        clientVer = callback;
                        if ((clientVer > 3057) || (clientVer != 3057)) {
                            create_rmt__downloadlinkNewVersion(emailid, password, "GETURL");
                            getmachineDetails();
                        } else {
                    create_rmt__downloadlink(emailid, password, 'ADDNEW');
                        }
                    });                    
                } else {
                    $('#err').html(data);
                    setTimeout(function () {
                        $("#err").fadeOut(3600);
                    }, 3600);
                }
            },
            error: function (err) {
                console.log(err);
            }
        });
    }
});

function create_rmt__downloadlink_EXIST(loginid, password, action) {
    //console.log("action newTest--->" + action);
    //console.log("password newTest--->" + password);
    //alert("loginid: "+loginid+", password: "+password+', action: '+action);
    var rmttype = $('#remotetype').val();
    var pass = escape(password);
    $("#errr").html('');
    $("#errr").show();
    $("#err").html('');
    $("#err").show();

    $.ajax({
        url: "../remote/remote_func.php",
        data: "function=create_remotelink_Exist&loginid=" + loginid + "&password=" + pass + '&remoteType=' + rmttype,
        type: "GET",
        success: function (msg) {
            var data = msg.trim();
            if (data === 'INVALID') {
                //console.log("INVALID--->");
                if (action === 'GETURL') {
                    //console.log("GETURL--->");
                    showResetRmtPassword(loginid);

                } else {
                    //console.log("else--->");
                    $('#errr').css({'color': 'red'}).html('Login ID / Password is incorrect');
                    setTimeout(function () {
                        $("#errr").fadeOut(3600);
                    }, 3600);
                    $('#err').css({'color': 'red'}).html('Login ID / Password is incorrect');
                    setTimeout(function () {
                        $("#err").fadeOut(3600);
                    }, 3600);
                }
            } else if (data === 'Password Not Set') {
                $('#resetpass').css({'display': 'block'});
                $('#err').css({'color': 'red'}).html('Incorrect password. Please update password.');
                setTimeout(function () {
                    $("#err").fadeOut(3600);
                }, 3600);
                $('#errr').css({'color': 'red'}).html('Incorrect password. Please update password.');
                setTimeout(function () {
                    $("#errr").fadeOut(3600);
                }, 3600);
            } else if (data === 'NO_APP_RUNNING') {
                $('#err').css({'color': 'red'}).html('Please Login to ' + rmttype + ' App');
                setTimeout(function () {
                    $("#err").fadeOut(3600);
                }, 3600);
                $('#errr').css({'color': 'red'}).html('Please Login to ' + rmttype + ' App');
                setTimeout(function () {
                    $("#errr").fadeOut(3600);
                }, 3600);
            } else {
                if (action === 'GETURL') {
                    $('#error').html('');
                    $('#takeremote').removeAttr('disabled');
                    $('#takeremote').removeClass('buttondisable');
                    $('#takeremote').addClass('button');
                    $('#remote_url').val($('<div/>').html(data).text());
                    showRmtUpdate(loginid,data);
                    //updateRmt_Details(loginid, password, action, data);
                } else {
                    $('#error').html('');
                    $('#takeremote').removeAttr('disabled');
                    $('#takeremote').removeClass('buttondisable');
                    $('#takeremote').addClass('button');
                    $('#remote_url').val($('<div/>').html(data).text());
//                    $('#error').css({'color': 'green'}).html('Fetching URL. Please wait...');
                    //updateRmt_Details(loginid, password, action, data);
                    showRmtUpdate(loginid,data);
                }
            }
        }
        /* end - success */
    });
}
function create_rmt__downloadlink_EXIST_NEWVERSION(loginid, password, action) {
    //console.log("action newTest--->" + action);
    //console.log("password newTest--->" + password);
    //alert("loginid: "+loginid+", password: "+password+', action: '+action);
    var rmttype = $('#remotetype').val();
    var pass = escape(password);
    $("#errr").html('');
    $("#errr").show();
    $("#err").html('');
    $("#err").show();

    $.ajax({
        url: "../remote/remote_func.php",
        data: "function=create_remotelink_Exist&loginid=" + loginid + "&password=" + pass + '&remoteType=' + rmttype,
        type: "GET",
        success: function (msg) {
            var data = msg.trim();
            if (data === 'INVALID') {
                //console.log("INVALID--->");
                if (action === 'GETURL') {
                    //console.log("GETURL--->");
                    showResetRmtPassword(loginid);

                } else {
                    //console.log("else--->");
                    $('#errr').css({'color': 'red'}).html('Login ID / Password is incorrect');
                    setTimeout(function () {
                        $("#errr").fadeOut(3600);
                    }, 3600);
                    $('#err').css({'color': 'red'}).html('Login ID / Password is incorrect');
                    setTimeout(function () {
                        $("#err").fadeOut(3600);
                    }, 3600);
                }
            } else if (data === 'Password Not Set') {
                $('#resetpass').css({'display': 'block'});
                $('#err').css({'color': 'red'}).html('Incorrect password. Please update password.');
                setTimeout(function () {
                    $("#err").fadeOut(3600);
                }, 3600);
                $('#errr').css({'color': 'red'}).html('Incorrect password. Please update password.');
                setTimeout(function () {
                    $("#errr").fadeOut(3600);
                }, 3600);
            } else if (data === 'NO_APP_RUNNING') {
                $('#err').css({'color': 'red'}).html('Please Login to ' + rmttype + ' App');
                setTimeout(function () {
                    $("#err").fadeOut(3600);
                }, 3600);
                $('#errr').css({'color': 'red'}).html('Please Login to ' + rmttype + ' App');
                setTimeout(function () {
                    $("#errr").fadeOut(3600);
                }, 3600);
            } else {
                if (action === 'GETURL') {
                    $('#error').html('');
                    $('#takeremote').removeAttr('disabled');
                    $('#takeremote').removeClass('buttondisable');
                    $('#takeremote').addClass('button');
                    $('#remote_url').val($('<div/>').html(data).text());
//                    showRmtUpdate(loginid, data);
                    //updateRmt_Details(loginid, password, action, data);
                } else {
                    $('#error').html('');
                    $('#takeremote').removeAttr('disabled');
                    $('#takeremote').removeClass('buttondisable');
                    $('#takeremote').addClass('button');
                    $('#remote_url').val($('<div/>').html(data).text());
//                    $('#error').css({'color': 'green'}).html('Fetching URL. Please wait...');
                    //updateRmt_Details(loginid, password, action, data);
//                    showRmtUpdate(loginid, data);
                }
            }
        }
        /* end - success */
    });
}


function create_rmt__downloadlinkNewVersion(loginid, password, action) {
    //console.log("action newTest--->" + action);
    //console.log("password newTest--->" + password);
    //alert("loginid: "+loginid+", password: "+password+', action: '+action);
//    var rmttype = $('#remotetype').val();
//alert(loginid+"pass"+pass+"act"+action);
    var rmttype = "LMI";
    var pass = escape(password);
    $("#errr").html('');
    $("#errr").show();
    $("#err").html('');
    $("#err").show();

    $.ajax({
        url: "../remote/remote_func.php",
        data: "function=create_remotelink&loginid=" + loginid + "&password=" + pass + '&remoteType=' + rmttype,
        type: "GET",
        success: function (msg) {
//            alert(msg);
            console.log(msg);
            var data = msg.trim();
            if (data === 'INVALID') {
                //console.log("INVALID--->");
                if (action === 'GETURL') {
                    //console.log("GETURL--->");
                    showResetRmtPassword(loginid);

                } else {
                    //console.log("else--->");
                    $('#errr').css({'color': 'red'}).html('Login ID / Password is incorrect');
                    setTimeout(function () {
                        $("#errr").fadeOut(3600);
                    }, 3600);
                    $('#err').css({'color': 'red'}).html('Login ID / Password is incorrect');
                    setTimeout(function () {
                        $("#err").fadeOut(3600);
                    }, 3600);
                }
            } else if (data === 'Password Not Set') {
                $('#resetpass').css({'display': 'block'});
                $('#err').css({'color': 'red'}).html('Incorrect password. Please update password.');
                setTimeout(function () {
                    $("#err").fadeOut(3600);
                }, 3600);
                $('#errr').css({'color': 'red'}).html('Incorrect password. Please update password.');
                setTimeout(function () {
                    $("#errr").fadeOut(3600);
                }, 3600);
            } else if (data === 'NO_APP_RUNNING') {
                $('#err').css({'color': 'red'}).html('Please Login to ' + rmttype + ' App');
                setTimeout(function () {
                    $("#err").fadeOut(3600);
                }, 3600);
                $('#errr').css({'color': 'red'}).html('Please Login to ' + rmttype + ' App');
                setTimeout(function () {
                    $("#errr").fadeOut(3600);
                }, 3600);
            } else {

                if (action === 'GETURL') {
                    $('#error').html('');
                    $('#takeremote').removeAttr('disabled');
                    $('#takeremote').removeClass('buttondisable');
                    $('#takeremote').addClass('button');
                    $('#remote_url').val($('<div/>').html(data).text());
                     getmachineDetails();

                } else {
                    $('#error').html('');
                    $('#takeremote').removeAttr('disabled');
                    $('#takeremote').removeClass('buttondisable');
                    $('#takeremote').addClass('button');
                    $('#remote_url').val($('<div/>').html(data).text());
                }
            }
        }
        /* end - success */
    });
}
function create_rmt__downloadlink(loginid, password, action) {
    //console.log("action newTest--->" + action);
    //console.log("password newTest--->" + password);
    //alert("loginid: "+loginid+", password: "+password+', action: '+action);
    var rmttype = $('#remotetype').val();
    var pass = escape(password);
    $("#errr").html('');
    $("#errr").show();
    $("#err").html('');
    $("#err").show();

    $.ajax({
        url: "../remote/remote_func.php",
        data: "function=create_remotelink&loginid=" + loginid + "&password=" + pass + '&remoteType=' + rmttype,
        type: "GET",
        success: function (msg) {
//            alert(action);
//            return;
            console.log(msg);
            var data = msg.trim();
            if (data === 'INVALID') {
                //console.log("INVALID--->");
                if (action === 'GETURL') {
                    //console.log("GETURL--->");
                    showResetRmtPassword(loginid);

                }else if (action === 'ADDNEW'){
                    $('#err').css({'color': 'red'}).html('Login ID / Password is incorrect');
                    setTimeout(function () {
                        $("#errr").fadeOut(3600);
                    }, 3600);
                    $('#err').css({'color': 'red'}).html('Login ID / Password is incorrect');
                    setTimeout(function () {
                        $("#err").fadeOut(3600);
                    }, 3600);
                    return false;
                } else {
                    //console.log("else--->");
                    $('#errr').css({'color': 'red'}).html('Login ID / Password is incorrect');
                    setTimeout(function () {
                        $("#errr").fadeOut(3600);
                    }, 3600);
                    $('#err').css({'color': 'red'}).html('Login ID / Password is incorrect');
                    setTimeout(function () {
                        $("#err").fadeOut(3600);
                    }, 3600);
                     return;
                }
                return false;
            } else if (data === 'Password Not Set') {
                $('#resetpass').css({'display': 'block'});
                $('#err').css({'color': 'red'}).html('Incorrect password. Please update password.');
                setTimeout(function () {
                    $("#err").fadeOut(3600);
                }, 3600);
                $('#errr').css({'color': 'red'}).html('Incorrect password. Please update password.');
                setTimeout(function () {
                    $("#errr").fadeOut(3600);
                }, 3600);
            } else if (data === 'NO_APP_RUNNING') {
                $('#err').css({'color': 'red'}).html('Please Login to ' + rmttype + ' App');
                setTimeout(function () {
                    $("#err").fadeOut(3600);
                }, 3600);
                $('#errr').css({'color': 'red'}).html('Please Login to ' + rmttype + ' App');
                setTimeout(function () {
                    $("#errr").fadeOut(3600);
                }, 3600);
            } else {

                if (action === 'GETURL') {
                    $('#error').html('');
                    $('#takeremote').removeAttr('disabled');
                    $('#takeremote').removeClass('buttondisable');
                    $('#takeremote').addClass('button');
                    $('#remote_url').val($('<div/>').html(data).text());
                    updateRmt_Details(loginid, password, action, data);
                } else {
                    $('#error').html('');
                    $('#takeremote').removeAttr('disabled');
                    $('#takeremote').removeClass('buttondisable');
                    $('#takeremote').addClass('button');
                    $('#remote_url').val($('<div/>').html(data).text());
//                    $('#error').css({'color': 'green'}).html('Fetching URL. Please wait...');
                    updateRmt_Details(loginid, password, action, data);
                }
            }
        }
        /* end - success */
    });
}

function updateRmt_Details(rmt_login, rmt_pass, act, result) {
    var rmttype = $('#remotetype').val();
    var params = "&perform_act=" + act + "&login=" + rmt_login + "&pass=" + rmt_pass;
    $("#err").html('');
    $("#err").show();
    //alert('updateRmt_Details: '+params);
    $.ajax({
        url: "../remote/remote_func.php?function=updateRemote_Details" + params + '&remoteType=' + rmttype,
        type: 'GET',
        success: function (data) {
            //alert(data);
            if ($.trim(data) === 'success') {
                showRmtUpdate(rmt_login, result);
            } else if ($.trim(data) === 'error') {
                $('#err').css({'color': 'red'}).html("Sorry couldn't Update Remote Details.Please try again");
                setTimeout(function () {
                    $("#err").fadeOut(3600);
                }, 3600);
            }
        },
        error: function (err) {
        }
    });
}

function showRmtUpdate(loginid, dataurl) {
    var rmttype = $('#remotetype').val();
    var rem_URL = encodeURIComponent(dataurl);
    $.ajax({
        url: "../remote/index.php?perform=SHOWURL&rmt_loginid=" + loginid + "&rmt_result=" + rem_URL + '&remoteType=' + rmttype,
        dataType: 'json',
        type: 'GET',
        success: function (data) {
            $('#loginid').val(data.rmt_liginid);//email id
            $('#remote_url').val(data.rmt_result);//url
            if (data.perform == 'SHOWURL' || (data.perform == 'EXIST' && data.showPage == "NO")) {
                $('#warningreset').modal('hide');
                $('#warningdefault').modal('hide');
                $('#warningshowurl').modal('show');
            }
        }
    })
}

function showResetRmtPassword(loginid) {
    var rmtid = $('#rmt_loginid').val();
    var rmttype = $('#remotetype').val();
    if (rmtid != "") {
        //console.log("if");
        $.ajax({
            url: "../remote/index.php?perform=RESET&resetloginid=" + rmtid + '&remoteType=' + rmttype,
            dataType: 'json',
            data: '{}',
            type: 'GET',
            success: function (data) {
                if (data.perform == 'RESET') {
                    $('#warningdefault').modal('hide');
                    $('#warningreset').modal('show');
                }
            }
        });
    } else {
        //console.log("else");
        $.ajax({
            url: "../remote/index.php?perform=RESET&resetloginid=" + loginid + '&remoteType=' + rmttype,
            dataType: 'json',
            data: '{}',
            type: 'GET',
            success: function (data) {
                $("#rmt_loginidr").val(data.resetlogin);
                $("#rmt_loginidr").parent().removeClass("is-empty");
                if (data.perform == 'RESET') {
                    $('#warningdefault').modal('hide');
                    $('#warningreset').modal('show');
                }
            }
        });
    }
}

function sendremotetoclient() {
    getmachineDetails();
}

function getmachineDetails() {
    var remotewsurl = $('#remotewsurl').val();
    var hostname = $('#selected').val();
    var rmttype = $('#remotetype').val();

    $.ajax({
        type: "GET",
        url: "../communication/communication_ajax.php",
        data: "function=getMachineOS",
        success: function (msg) {
            //console.log(msg);
            msg = msg.trim();
            var res = msg.split('####');
            var res1 = res[1].split('~~');
            var hostname = res1[0];
            var machineSite = res1[1];
            var machineBuld = res1[2];
            var url = $("#remote_url").val();
            url = $.trim(url);
            var ver;
            var dartVal;
            if (machineBuld !== '' || machineBuld === null) {
                var versionNo = machineBuld.split('.');
                ver = parseInt(versionNo[3]);
            } else {
                ver = 0;
            }
//            ver = "3058";
            var WidowsType = res[0];
            if (ver > 3057) {
                // New resolution

                if (WidowsType.search("64-bit") !== -1) {
                    var res = url.split("&Code=");
                    //NewVersionURL = res[0]+"&Code=622414";
                    //dartVal = 'VarName=Scrip89Package;VarType=2;VarVal=C:\\Program Files (x86)\\Internet Explorer\\iexplore.exe "' + url + '";Action=SET;DartNum=215;VarScope=1;#;NextConf;#VarName=S00286ProfileName;VarType=2;VarVal=remoteaccess;Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;#;NextConf;##;NextConf;#End';
                    dartVal = 'VarName=Scrip89Package;VarType=2;VarVal="C:\\Program Files (x86)\\Office Depot\\Tech Services\\Tools\\vav7gd\\CallingCard.exe" -accept_eula -pin_code "' + res[1] + '";Action=SET;DartNum=215;VarScope=1;#;NextConf;#VarName=S00089RunNowButton;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=215;VarScope=1;';
                } else {
                    var res = url.split("&Code=");
                    dartVal = 'VarName=Scrip89Package;VarType=2;VarVal="C:\\Program Files\\Office Depot\\Tech Services\\Tools\\vav7gd\\CallingCard.exe" -accept_eula -pin_code "' + res[1] + '";Action=SET;DartNum=215;VarScope=1;#;NextConf;#VarName=S00089RunNowButton;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=215;VarScope=1;';
                }
            } else {
                // Old Resulotion
                if (WidowsType.search("32-bit") !== -1) {
                    //dartVal = 'VarName=Scrip89Package;VarType=2;VarVal=C:\\Program Files (x86)\\Internet Explorer\\iexplore.exe "' + url + '";Action=SET;DartNum=215;VarScope=1;#;NextConf;#VarName=S00289ProfileName1;VarType=2;VarVal=remoteaccess;Action=SET;DartNum=289;VarScope=1;#;NextConf;#VarName=S00289SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=289;VarScope=1;#;NextConf;##;NextConf;#End';
                    dartVal = 'VarName=Scrip89Package;VarType=2;VarVal="C:\\Program Files (x86)\\Internet Explorer\\iexplore.exe" "' + url + '";Action=SET;DartNum=215;VarScope=1;#;NextConf;#VarName=S00089RunNowButton;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=215;VarScope=1;';
                } else {
                    dartVal = 'VarName=Scrip89Package;VarType=2;VarVal="C:\\Program Files\\Internet Explorer\\iexplore.exe "' + url + '";Action=SET;DartNum=215;VarScope=1;#;NextConf;#VarName=S00089RunNowButton;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=215;VarScope=1;';

                }

            }
//            var profileName = "<?php echo $remoteType ?>";
            //var profileName = rmttype;            
            //var dblist = machineSite + '~~' + machineName + '~~' + machineBuld + '~~' + escape(dartVal) + '~~' + profileName;

            //ExecuteDirectJob(hostname, escape(dartVal));
            //addjob(hostname,escape(dartVal));
            var wsurl = remotewsurl;
            if (window.location.protocol !== "https:") {
                wsRemoteconnect('ws://' + wsurl, hostname, dartVal);
            } else {
                wsRemoteconnect('wss://' + wsurl, hostname, dartVal);
            }
        }
    });
}

function wsRemoteconnect(wsurl, Servicetag, DirectJob) {
    ws = new WebSocket(wsurl);
    ws.onopen = function () {
        var ConnectData = {};
        ConnectData['Type'] = 'Dashboard';
        ConnectData['AgentId'] = 'GTATRIGGER';
        ConnectData['AgentName'] = 'GTATRIGGER';
        ConnectData['ReportingURL'] = 'GTATRIGGER';
        ws.send(JSON.stringify(ConnectData));
        ExecuteDirectJob(Servicetag, DirectJob);
    };
}

function ExecuteDirectJob(Servicetag, DirectJob) {


    var JobData = {};
    JobData['Type'] = 'ExecuteDirectJob';
    JobData['ServiceTag'] = Servicetag;
    JobData['DirectJob'] = 'InstantExecution---' + DirectJob;
    //console.log(DirectJob);
    ws.send(JSON.stringify(JobData));
    openSuccessPage();
}

function emitGTA(serviceTag, OS) {
    $.ajax({
        type: "GET",
        url: "../communication/communication_ajax.php",
        data: "function=getTempAuditdata&os=" + OS,
        success: function (msg) {
            if (msg !== '') {
                var emitMsg = msg.split('##');
                for (var i = 0; i < emitMsg.length - 1; i++) {
                    RemoteSocket.emit('executeDart', emitMsg[i]);
                    openSuccessPage();
                }
            }
        }
    });
}
/*=====success call =====*/
function openSuccessPage() {
    $.ajax({
        url: "../remote/index.php?perform=SUCCESSPAGE",
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.perform === 'SUCCESSPAGE') {
                $('#warningshowurl').modal('hide');
                $('#warningdefault').modal('hide');
                $('#warningreset').modal('hide');
                $('#remotesuccessmsg').modal('show');
            }
        }
    });
}

//fetch stored user name and password

function fetchUserDetails() {
    var name = '';
    $.ajax({
        url: "../remote/remote_func.php?function=getUserList",
        type: 'GET',
        dataType: 'text'
    }).done(function (result) {
        //console.log(result);
        name = result;
    });
//       success: function(data) {
//           console.log(data);
//           $('#usernameEmail').html('');
//           $('#usernameEmail').html(data);
//           $('.selectpicker').selectpicker('refresh');
//           name = data;
//       }
//       
//    });
    return name;

}

$('#warningdefault').on('hidden.bs.modal', function () {
//    
    $('#warningdefault input[type=text]').val('');
    $('#warningdefault input[type=password]').val('');
    $('#err').html('');
});


function Machineremote(host) {
    //reset pop up fields
    $("#agentlogin").prop('checked', true);
    $(".pass-LMI").hide();
    $("#rmt_loginid").prop("disabled", true);
    $("#rmt_pass").prop("disabled", true);
    $("#rmt_passconf").prop("disabled", true);
    $("#usernameEmail").prop("disabled", false);

    loginType = 'email'; //reset check box value
    $("#rmt_passr").val('');
    $("#rmt_passconfr").val('');

    $.ajax({
        url: 'sitefunctions.php?function=get_machineremote&hostname=' + host,
        type: 'post',
        dataType: 'json',
        success: function (data) {
            if (data == 'Online') {
                OpenRemoteLogInPage('LMI'); // LMI type connect ,Go to assist is neglected
                //$('#machineonlineremote').modal('show');
            } else {
                $('#remotewarning').modal('show');
            }
        }
    })
}

$('#rmt_update').click(function () {

    var loginid = $('#rmt_loginidr').val();
    var passone = $('#rmt_passr').val();
    var passcon = $('#rmt_passconfr').val();
    $("#errr").html('');
    $("#errr").show();

    if (passone == '') {
        $('#errr').css({'color': 'red'}).html('Please enter the password');
        setTimeout(function () {
            $("#errr").fadeOut(3600);
        }, 3600);
        return false;
    } else if (passcon == '') {
        $('#errr').css({'color': 'red'}).html('Please confirm the password');
        setTimeout(function () {
            $("#errr").fadeOut(3600);
        }, 3600);
        return false;
    } else if (passone != passcon) {
        $('#errr').css({'color': 'red'}).html('The passwords do not match');
        setTimeout(function () {
            $("#errr").fadeOut(3600);
        }, 3600);
        return false;
    } else {
        $('#errr').css({'color': 'green'}).html('Authenticating credentials. Please wait..');
        create_rmt__downloadlink(loginid, passone, 'UPDATE');
    }
});


function get_ClientVersion(callback) {
    var remotewsurl = $('#remotewsurl').val();
    var hostname = $('#selected').val();
    var rmttype = $('#remotetype').val();
    var ver;
    $.ajax({
        type: "GET",
        url: "../communication/communication_ajax.php",
        data: "function=getMachineOS",
        success: function (msg) {                       
            //console.log(msg);
            msg = msg.trim();
            var res = msg.split('####');
            var res1 = res[1].split('~~');
            var hostname = res1[0];
            var machineSite = res1[1];
            var machineBuld = res1[2];
            var url = $("#remote_url").val();
            url = $.trim(url);
            var ver;
            var dartVal;
            if (machineBuld !== '' || machineBuld === null) {
                var versionNo = machineBuld.split('.');
                ver = parseInt(versionNo[3]);
                callback(ver);
            } else {
                ver = 0;
                callback(ver);
            }            
        }
    });
    
   
}
