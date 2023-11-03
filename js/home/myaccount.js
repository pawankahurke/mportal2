/*
 This file is releated to Profile-> My Account.
 */

/*
 Revision history:
 
 Date        Who     What
 ---------   ---     ----
 08-Oct-16   AVI     Created. added functions getLicenseCount, getOrderDetailsGrid.
 02-Dec-16   AVI     Added function edit_AviraCustomerDetails
 15-Feb-17   AVI     MSP Flow changed for Adding new customer. Order number is brought in Add Customer pop up.
 Functions added 
 
 */

$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
    $('#Createsite').tooltip({
        selector: "a[rel=tooltip]"
    })
    isAviraEnabled = $("#avira_enabled").val();
    //Just to initialize Datatable to apply css
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

    //Table in pop up will come up with compact table headings, to adjust table headings following code is responsible
    $('#renew_details').on('shown.bs.modal', function() {
        var rowId = $('#customerDetailsGrid tbody tr.selected').attr('id');
        if (rowId === undefined || rowId === 'undefined') {

        } else {
            var custRowId = rowId.split('##');
            var cust_id = custRowId[0];
            var proc_id = custRowId[1];
            var custNum = custRowId[2];
            var ordNum = custRowId[3];
            getRenewDevices(custNum, ordNum, cust_id, proc_id);

            $('#renewDevicesGrid').show();
            $('#renewDevicesGrid').DataTable().columns.adjust().draw();
        }

    });

    $('#customer_details').on('shown.bs.modal', function() {
        $('#customer_detailsGrid').DataTable().columns.adjust().draw();
    });

    $('#aviraDetails_Popup').on('shown.bs.modal', function() {
        $('#aviraDetails_Grid').DataTable().columns.adjust().draw();
    });

    if (isAviraEnabled == 1) {
        //Do Nothing
    } else {
        getLicenseCount();
    }


    //Top right grid will changed based on SMB customer or PTS customer
    if (bussLevel === 'Commercial') {

        $("#ptsSkuDetails").hide();
        $("#smbOrderDetails").show();

        getOrderDetailsGrid();
        if (isAviraEnabled == 1) {
            //Do nothing
        } else {
            getCustomersGrid();
        }

    } else {
        $("#smbOrderDetails").hide();
        $("#ptsSkuDetails").show();

        getSkuDetailsGrid();
        getLicenseCount();
    }




});


/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function bring the data of Account Details -> Total Lisences Count, Unused Lisences Count, Renew in 30 days
 *----------------------------------------------------------------------------------------------------------------------
 */
function getLicenseCount() {
    $.ajax({
        type: "GET",
        dataType: 'json',
        url: "../lib/l-custAjax.php",
        data: "function=CUSAJX_GetLicenseCount",
        success: function(result) {
            $("#totalLicenses").html(result.totalLicenses);
            $("#unusedLicenses").html(result.unusedLicenses);
            $("#hiddenunusedLicenses").val(result.renewLicenses);
            $("#renewLicenses").html(result.renewLicenses);
        }
    });
    return true;
}

function getOtcDetailsCount(otcCode) {
    $.ajax({
        type: "GET",
        dataType: 'text',
        url: "../lib/l-custAjax.php",
        data: "function=CUSTAJX_getAviraInstallDtl&otcCode=" + otcCode,
        success: function(result) {
            var counts = result.split('---');

            $("#totalLicenses").html(counts[0]);
            $("#unusedLicenses").html(counts[1]);
            $("#hiddenunusedLicenses").val(counts[2]);
            $("#renewLicenses").html(counts[2]);
            
        }
    });
    return true;
}

function getNHDetailsCount(otcCode) {
    $.ajax({
        type: "GET",
        dataType: 'text',
        url: "../lib/l-custAjax.php",
        data: "function=CUSTAJX_getNHInstallDtl&nhId=" + otcCode,
        success: function(result) {
            var counts = result.split('---');

            $("#totalLicenses").html(counts[0]);
            $("#unusedLicenses").html(counts[1]);
            $("#hiddenunusedLicenses").val(counts[2]);
            $("#renewLicenses").html(counts[2]);
        }
    });
    return true;
}

function disableCustAccnt() {

    var rowId = $('#customerDetailsGrid tbody tr.selected').attr('id');
    if (rowId === undefined || rowId === 'undefined') {

    } else {
        var custRowId = rowId.split('##');
        var cust_id = custRowId[0];
        var proc_id = custRowId[1];
        var custNum = custRowId[2];
        var ordNum = custRowId[3];
        var custStatus = custRowId[4];
    }

    $.ajax({
        type: "GET",
        dataType: 'text',
        url: "../lib/l-custAjax.php",
        data: "function=CUSTAJX_disableCust&chId=" + cust_id,
        success: function(result) {
            var msg = $.trim(result);
            if (msg === 'done') {
                $("#disable_msg").html("<span>Customer account disabled successfully.<span>");
                setInterval(function() {
                    $("#disable_site").modal('hide');
                    location.reload();
                }, 2000);
            } else {
                $("#disable_msg").html("<span>Fail to disabled customer account.</span>");
            }
        }
    });
    return true;
}

function enableCustAccnt() {

    var rowId = $('#customerDetailsGrid tbody tr.selected').attr('id');
    if (rowId === undefined || rowId === 'undefined') {

    } else {
        var custRowId = rowId.split('##');
        var cust_id = custRowId[0];
        var proc_id = custRowId[1];
        var custNum = custRowId[2];
        var ordNum = custRowId[3];
        var custStatus = custRowId[4];
    }

    $.ajax({
        type: "GET",
        dataType: 'text',
        url: "../lib/l-custAjax.php",
        data: "function=CUSTAJX_enableCust&chId=" + cust_id,
        success: function(result) {
            var msg = $.trim(result);
            if (msg === 'done') {
                $("#enable_msg").html("<span>Customer account enabled successfully.</span>");
                setInterval(function() {
                    $("#enable_site").modal('hide');
                    location.reload();
                }, 2000);
            } else {
                $("#enable_msg").html("<span>Fail to enabled customer account.</span>");
            }
        }
    });
    return true;
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function bring the table data for Order Details Table, present on top right side. Data is coming from
 * Orderdetails table under agent database.
 *----------------------------------------------------------------------------------------------------------------------
 */
function getOrderDetailsGrid() {
    isAviraEnabled = $("#avira_enabled").val();

    if (isAviraEnabled == 1) {
        $('#orderAVIRA_DetailsGrid').show();
        $('#orderNH_DetailsGrid').hide();
        getAviraOrderDetailsGrid();
    } else {
        $('#orderAVIRA_DetailsGrid').hide();
        $('#orderNH_DetailsGrid').show();
        getRegularOrderDetailsGrid();
    }

}

function changeLicenseType(type) {

    if (type === 'Avira') {
        $('#orderNH_DetailsGrid').dataTable().fnDestroy();
        $('#orderAVIRA_DetailsGrid').show();
        $('#orderNH_DetailsGrid').hide();
        getAviraOrderDetailsGrid();
    } else if (type === 'NH') {
        $('#orderAVIRA_DetailsGrid').dataTable().fnDestroy();
        $('#orderAVIRA_DetailsGrid').hide();
        $('#orderNH_DetailsGrid').show();
        getRegularOrderDetailsGrid();
    }

}

function getAviraOrderDetailsGrid() {

    isAviraEnabled = $("#avira_enabled").val();
    $('#orderAVIRA_DetailsGrid').dataTable().fnDestroy();
    orderDetailsGrid = $('#orderAVIRA_DetailsGrid').DataTable({
        scrollY: jQuery('#orderAVIRA_DetailsGrid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1, 2, 3]
            }],
        ajax: {
            url: "../lib/l-custAjax.php?function=CUSAJX_Avira_GetOrderGridData",
            type: "POST"
        },
        columns: [
            {"data": "OTC_Code"},
            {"data": "email"},
            {"data": "compname"},
            {"data": "desc"},
            {"data": "licenses"},
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function(settings, json) {
            if (isAviraEnabled == 1) {
                orderDetailsGrid.$('tr:first').click();
            }
        },
        drawCallback: function(settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
        }
    });

    if (isAviraEnabled == 1) {

        $('#orderAVIRA_DetailsGrid tbody').on('click', 'tr', function() {
            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
            }
            else {
                orderDetailsGrid.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
                var rowId = $('#orderAVIRA_DetailsGrid tbody tr.selected').attr('id')
                getOtcDetailsGrid(rowId);
                getOtcDetailsCount(rowId);
            }
        });

    }


}


function updateOTCCustomer() {

    var sel_cust = $('#customerDetailsGrid tbody tr.selected').attr('id');
    var cust_info = sel_cust.split('##');
    var compId = cust_info[0];
    var proccessId = cust_info[1];
    var customerNum = cust_info[2];
    var orderNum = cust_info[3];
    var custStatus = cust_info[4];
    var restricted = cust_info[5];

    $.ajax({
        type: "GET",
        dataType: 'json',
        url: "../lib/l-custAjax.php",
        data: "function=CUSTAJX_GetCustOTC&compId=" + compId + "&proId=" + proccessId + "&customerNum=" + customerNum + "&orderNum=" + orderNum,
        success: function(result) {
            if (result.status === 'success') {

                $("#aviraOldotcVal").val(result.currentOtc);
                $("#aviraNewotcVal").html(result.otcList);
                $("#aviraNewotcVal").selectpicker('refresh');
                $("#updateAviraOTCBtn").prop('disabled', true);
                $("#updateAviraOTCBtn").css('cursor', 'not-allowed');
            } else {

            }
        }
    });

}

function updateAviraotcpopUp() {

    var sel_cust = $('#customerDetailsGrid tbody tr.selected').attr('id');
    var cust_info = sel_cust.split('##');
    var compId = cust_info[0];
    var proccessId = cust_info[1];
    var customerNum = cust_info[2];
    var orderNum = cust_info[3];
    var custStatus = cust_info[4];
    var restricted = cust_info[5];

    var aviraOldotc = $('#aviraOldotcVal').val();
    var aviraNewotc = $('#aviraNewotcVal').val();

    var error = 0;
    if (aviraNewotc === '') {

        $("#updatenewotc_errorMsg").css("color", "red").html("<span>Please select OTC code.</span>");
        error++;
    }

    if (error === 0) {
        $("#addotc_errorMsg").html('<img src="../vendors/images/ajax-login.gif" class="loadhome" alt="loading..." />');
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "../lib/l-custAjax.php",
            data: "function=CUSTAJX_UpdateCustOTC&compId=" + compId + "&proId=" + proccessId + "&customerNum=" + customerNum + "&orderNum=" + orderNum + "&newOTC=" + aviraNewotc + "&oldOTC=" + aviraOldotc,
            success: function(response) {
                $("#addotc_errorMsg").html('');
                if (response.status === "success") {
                    $("#updatenewotc_errorMsg").css("color", "green").html(response.msg);
                    setInterval(function() {
                        $("#addotc_Popup").modal('hide');
                        location.reload();
                    }, 3000);

                } else if (response.status === "ERROR") {
                    $("#updatenewotc_errorMsg").css("color", "red").html(response.msg);
                }

            },
            error: function(err) {

            }
        });
    }

}

function onchangeUpdateOTC() {
    $("#updatenewotc_errorMsg").html('');
    var sel_cust = $('#customerDetailsGrid tbody tr.selected').attr('id');
    var cust_info = sel_cust.split('##');
    var compId = cust_info[0];
    var proccessId = cust_info[1];
    var customerNum = cust_info[2];
    var orderNum = cust_info[3];
    var custStatus = cust_info[4];
    var restricted = cust_info[5];

    var aviraOldotcVal = $("#aviraOldotcVal").val();
    var aviraNewotcVal = $("#aviraNewotcVal").val();

    $.ajax({
        type: "GET",
        dataType: 'json',
        url: "../lib/l-custAjax.php",
        data: "function=CUSTAJX_GetSelCustOTC&compId=" + compId + "&proId=" + proccessId + "&customerNum=" + customerNum + "&orderNum=" + orderNum + "&newOTC=" + aviraNewotcVal,
        success: function(result) {
            if (result.status === 'continue') {
                $("#updateAviraOTCBtn").prop('disabled', false);
                $("#updateAviraOTCBtn").css('cursor', 'pointer');
            } else {
                $("#updatenewotc_errorMsg").css("color", "red").html(result.msg);

            }
        }
    });

}



function getRegularOrderDetailsGrid() {


    $('#orderNH_DetailsGrid').dataTable().fnDestroy();
    orderDetailsGrid = $('#orderNH_DetailsGrid').DataTable({
        scrollY: jQuery('#orderNH_DetailsGrid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1, 2, 3]
            }],
        ajax: {
            url: "../lib/l-custAjax.php?function=CUSAJX_GetOrderGridData",
            type: "POST"
        },
        columns: [
            {"data": "orderNum"},
            {"data": "licenseCnt"},
            {"data": "purchaseDate"},
            {"data": "validity"},
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function(settings, json) {
            orderDetailsGrid.$('tr:first').click();
        },
        drawCallback: function(settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
        }
    });

    $('#orderNH_DetailsGrid tbody').on('click', 'tr', function() {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        }
        else {
            orderDetailsGrid.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var rowId = $('#orderNH_DetailsGrid tbody tr.selected').attr('id')
            getNHDetailsGrid(rowId);
            getNHDetailsCount(rowId);
        }
    });

}


/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function bring the table data for Sku details. If user logged in is type commercial/PTS
 * For SMB/MSP customer this grid will not load.
 *----------------------------------------------------------------------------------------------------------------------
 */
function getSkuDetailsGrid() {
    $("#revoke_option").hide();
    $("#regenerate_option").hide();
    $("#aviraDetailsOption").hide();

    $('#ptsSkuDetailsGrid').dataTable().fnDestroy();
    skuDetailsGrid = $('#ptsSkuDetailsGrid').DataTable({
        scrollY: jQuery('#ptsSkuDetailsGrid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1, 2]
            }],
        ajax: {
            url: "../lib/l-custAjax.php?function=CUSAJX_GetSkuGridData",
            type: "POST"
        },
        columns: [
            {"data": "skuName"},
            {"data": "licenseCnt"},
            {"data": "validity"}
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        drawCallback: function(settings) {

        },
        initComplete: function(settings, json) {
            skuDetailsGrid.$('tr:first').click();
        }
    });

    $('#ptsSkuDetailsGrid tbody').on('click', 'tr', function() {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        }
        else {
            skuDetailsGrid.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var rowData = skuDetailsGrid.row(this).data();
            var skuRef = rowData.skuRef;
            var cid = rowData.cid;
            getSkuCustomersGrid(skuRef, cid);
        }
    });

}


function getOtcDetailsGrid(otcId) {

    $("#revoke_option").hide();
    $("#regenerate_option").hide();
    $("#aviraDetailsOption").hide();

    $('#customerDetailsGrid').dataTable().fnDestroy();
    customerDetailsGrid = $('#customerDetailsGrid').DataTable({
        scrollY: jQuery('#customerDetailsGrid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1]
            }],
        ajax: {
            url: "../lib/l-custAjax.php?function=CUSTAJX_getAviraCustDtl&otcCode=" + otcId,
            type: "POST"
        },
        columns: [
            {"data": "customername"},
            {"data": "pccount"},
        ],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        drawCallback: function(settings) {

        },
        initComplete: function(settings, json) {
            customerDetailsGrid.$('tr:first').click();
//            getCustomersDevices();
        }

    });

    $('#customerDetailsGrid tbody').on('click', 'tr', function() {
        $("#customer_details .popup-title").html("<span>Customer Details</span>");
        customerDetailsGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var rowData = customerDetailsGrid.row(this).data();

        getDetailedCustomersGrid();
        getCustomersDevices();

        //If no data in grid, then this option should be hidden
        if (rowData.sitename == '' || rowData.sitename == 'undefined' || rowData.sitename == undefined) {
            $("#cust_details_option").hide();
            $("#aviraSubscriptionOption").hide();
        } else {
            $("#cust_details_option").show();
            $("#aviraSubscriptionOption").show();
        }
    });

    $('#myaccount_searchbox').on('keyup', function() {
        customerDetailsGrid.search(this.value).draw();
        $('#customerDevicesGrid').dataTable().fnClearTable();
        customerDetailsGrid.$('tr:first').click();
        $(".search").addClass('open');
    });
}


function getNHDetailsGrid(otcId) {

    $("#revoke_option").hide();
    $("#regenerate_option").hide();
    $("#aviraDetailsOption").hide();

    $('#customerDetailsGrid').dataTable().fnDestroy();
    customerDetailsGrid = $('#customerDetailsGrid').DataTable({
        scrollY: jQuery('#customerDetailsGrid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1]
            }],
        ajax: {
            url: "../lib/l-custAjax.php?function=CUSTAJX_getNHCustDtl&otcCode=" + otcId,
            type: "POST"
        },
        columns: [
            {"data": "customername"},
            {"data": "pccount"},
        ],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        drawCallback: function(settings) {

        },
        initComplete: function(settings, json) {
            customerDetailsGrid.$('tr:first').click();
//            getCustomersDevices();
        }

    });

    $('#customerDetailsGrid tbody').on('click', 'tr', function() {
        $("#customer_details .popup-title").html("<span>Customer Details</span>");
        customerDetailsGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var rowData = customerDetailsGrid.row(this).data();

        getDetailedCustomersGrid();
        getCustomersDevices();

        //If no data in grid, then this option should be hidden
        if (rowData.sitename == '' || rowData.sitename == 'undefined' || rowData.sitename == undefined) {
            $("#cust_details_option").hide();
        } else {
            $("#cust_details_option").show();
        }
    });

    $('#myaccount_searchbox').on('keyup', function() {
        customerDetailsGrid.search(this.value).draw();
        $('#customerDevicesGrid').dataTable().fnClearTable();
        customerDetailsGrid.$('tr:first').click();
    });

}


/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function get the table data for Customer Details Table, present on bottom left side. Data is coming from
 * joins of customerOrder, channel, servicerequest table under agent database.
 *----------------------------------------------------------------------------------------------------------------------
 */
function getCustomersGrid() {

    $("#revoke_option").hide();
    $("#regenerate_option").hide();
    $("#aviraDetailsOption").hide();

    $('#customerDetailsGrid').dataTable().fnDestroy();
    $('#customerDetailsGrid').dataTable().fnDestroy();
    customerDetailsGrid = $('#customerDetailsGrid').DataTable({
        scrollY: jQuery('#customerDetailsGrid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1]
            }],
        ajax: {
            url: "../lib/l-custAjax.php?function=CUSAJX_GetCustomerGrid",
            type: "POST"
        },
        columns: [
            {"data": "customername"},
            {"data": "pccount"},
        ],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        drawCallback: function(settings) {

        },
        initComplete: function(settings, json) {
            customerDetailsGrid.$('tr:first').click();
//            getCustomersDevices();
        }

    });

    $('#customerDetailsGrid tbody').on('click', 'tr', function() {
        $("#customer_details .popup-title").html("<span>Customer Details</span>");
        customerDetailsGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var rowData = customerDetailsGrid.row(this).data();

        getDetailedCustomersGrid();
        getCustomersDevices();

        //If no data in grid, then this option should be hidden
        if (rowData.sitename == '' || rowData.sitename == 'undefined' || rowData.sitename == undefined) {
            $("#cust_details_option").hide();
        } else {
            $("#cust_details_option").show();
        }
    });
}

function getSearchCustomersGrid(searchVal) {
    $("#revoke_option").hide();
    $("#regenerate_option").hide();
    $("#aviraDetailsOption").hide();

    $('#customerDetailsGrid').dataTable().fnDestroy();
    $('#customerDetailsGrid').dataTable().fnDestroy();
    customerDetailsGrid = $('#customerDetailsGrid').DataTable({
        scrollY: jQuery('#customerDetailsGrid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1]
            }],
        ajax: {
            url: "../lib/l-custAjax.php?function=CUSAJX_GetCustomerGrid&searchVal=" + searchVal,
            type: "POST"
        },
        columns: [
            {"data": "customername"},
            {"data": "pccount"},
        ],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        drawCallback: function(settings) {

        },
        initComplete: function(settings, json) {
            customerDetailsGrid.$('tr:first').click();
            getCustomersDevices();
        }

    });

//    $('#customerDetailsGrid tbody').on('click', 'tr', function() {
//        $("#customer_details .popup-title").html("Customer Details");
//        customerDetailsGrid.$('tr.selected').removeClass('selected');
//        $(this).addClass('selected');
//        var rowData = customerDetailsGrid.row(this).data();
//        
//        getDetailedCustomersGrid();
//        getCustomersDevices();
//        
//        //If no data in grid, then this option should be hidden
//        if (rowData.sitename == '' || rowData.sitename == 'undefined' || rowData.sitename == undefined) {
//            $("#cust_details_option").hide();
//        } else {
//            $("#cust_details_option").show();
//        }
//    });
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function get the table data for Customer Details Table, present on bottom left side. Data is coming from
 * Data will change according to SKU grid row click.
 *----------------------------------------------------------------------------------------------------------------------
 */
function getSkuCustomersGrid(skuRef, cid) {

    $('#customerDetailsGrid').dataTable().fnDestroy();
    $('#customerDetailsGrid').dataTable().fnDestroy();
    customerDetailsGrid = $('#customerDetailsGrid').DataTable({
        scrollY: jQuery('#customerDetailsGrid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1]
            }],
        ajax: {
            url: "../lib/l-custAjax.php?function=CUSAJX_GetSkuCustomerGrid&skuRef=" + skuRef + '&cid=' + cid,
            type: "POST"
        },
        columns: [
            {"data": "customername"},
            {"data": "pccount"},
        ],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        drawCallback: function(settings) {

        },
        initComplete: function(settings, json) {
            customerDetailsGrid.$('tr:first').click();
//            getCustomersDevices();
        }

    });

    $('#customerDetailsGrid tbody').on('click', 'tr', function() {
        $("#customer_details .popup-title").html("<span>Customer Details<span>");
        customerDetailsGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var rowData = customerDetailsGrid.row(this).data();

        if (customerDetailsGrid.data().length === 0) {
            $('#add_new_sub_option').hide();
        } else {
            $('#add_new_sub_option').show();
        }

        getDetailedCustomersGrid();
        getCustomersDevices();

    });
}

function getSearchSkuCustomersGrid(searchVal) {
    var sel_cust = $('#ptsSkuDetailsGrid tbody tr.selected').attr('id');

    var cust_info = sel_cust.split('--');
    var skuRef = cust_info[0];
    var cid = cust_info[1];

    $('#customerDetailsGrid').dataTable().fnDestroy();
    $('#customerDetailsGrid').dataTable().fnDestroy();
    customerDetailsGrid = $('#customerDetailsGrid').DataTable({
        scrollY: jQuery('#customerDetailsGrid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1]
            }],
        ajax: {
            url: "../lib/l-custAjax.php?function=CUSAJX_GetSkuCustomerGrid&skuRef=" + skuRef + '&cid=' + cid + "&searchVal=" + searchVal,
            type: "POST"
        },
        columns: [
            {"data": "customername"},
            {"data": "pccount"},
        ],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        drawCallback: function(settings) {

        },
        initComplete: function(settings, json) {
            customerDetailsGrid.$('tr:first').click();
//            getCustomersDevices();
        }

    });

    $('#customerDetailsGrid tbody').on('click', 'tr', function() {
        $("#customer_details .popup-title").html("<span>Customer Details</span>");
        customerDetailsGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var rowData = customerDetailsGrid.row(this).data();

        if (customerDetailsGrid.data().length === 0) {
            $('#add_new_sub_option').hide();
        } else {
            $('#add_new_sub_option').show();
        }

        getDetailedCustomersGrid();
        getCustomersDevices();

    });
}


function getDetailedCustomersGrid() {

    var sel_cust = $('#customerDetailsGrid tbody tr.selected').attr('id');
    var cust_info = sel_cust.split('##');
    var compId = cust_info[0];
    var proccessId = cust_info[1];
    var customerNum = cust_info[2];

    $('#customer_detailsGrid').dataTable().fnDestroy();
    table = $('#customer_detailsGrid').DataTable({
        scrollY: jQuery('#customer_detailsGrid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        searching: false,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1]
            }],
        ajax: {
            url: "../lib/l-custAjax.php?function=getDetailedCustomers&compId=" + compId + "&processId=" + proccessId + "&customerNum=" + customerNum,
            type: "POST"
        },
        columns: [
            {"data": "ordNum"},
            {"data": "email"},
            {"data": "enddate"},
            {"data": "url"}
        ],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        drawCallback: function(settings) {

        },
        initComplete: function(settings, json) {

        }

    });

}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function get the table data for Customer Devices Table, present on bottom right side.
 *----------------------------------------------------------------------------------------------------------------------
 */
function getCustomersDevices() {
    $("#enableCustomer").hide();
    $("#disableCustomer").hide();
    var sel_cust = $('#customerDetailsGrid tbody tr.selected').attr('id');
    if (sel_cust === undefined) {
        $("#editCustomerOption").hide();
        $('#customerDevicesGrid').DataTable().destroy();
        dtLeftList = $('#customerDevicesGrid').DataTable({
            scrollY: jQuery('#customerDevicesGrid').data('height'),
            scrollCollapse: true,
            paging: true,
            searching: true,
            ordering: true,
            aaData: [],
            bAutoWidth: false,
            select: false,
            bInfo:false,
            responsive: true,
            "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
            "language": {
                "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                searchPlaceholder: "Search"
            },
            "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
            columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                    targets: "datatable-nosort",
                    orderable: false
                }],
            initComplete: function(settings, json) {
                uniqueCheckBox();
//                    $('#customerDevicesGrid tbody tr:eq(0)').addClass("selected");
            },
            drawCallback: function(settings) {
                $(".dataTables_scrollBody").mCustomScrollbar({
                    theme: "minimal-dark"
                });
//                    $('.equalHeight').matchHeight();
                $(".se-pre-con").hide();
            }

        });
    } else {
        $("#editCustomerOption").hide();
        var cust_info = sel_cust.split('##');

        var compId = cust_info[0];
        var proccessId = cust_info[1];
        var customerNum = cust_info[2];
        var orderNum = cust_info[3];
        var custStatus = cust_info[4];
        var restricted = cust_info[5];
        var ctype = cust_info[6];
        if ((custStatus === 0 || custStatus === '0') && (restricted === '0' || restricted === 0)) {
            $("#enableCustomer").show();
            $("#disableCustomer").hide();
            $("#updateotc_Popup").hide();
        } else if ((custStatus === 1 || custStatus === '1') && (restricted === '0' || restricted === 0)) {
            $("#enableCustomer").hide();
            $("#disableCustomer").show();
            $("#editCustomerOption").show();
            $("#updateotc_Popup").show();
        } else {
            $("#enableCustomer").hide();
            $("#disableCustomer").hide();
            $("#updateotc_Popup").hide();
        }


        if ((restricted === '0' && ctype === '2') || (ctype === '5')) {
            $("#refreshOTC a").attr('style', 'color: #595959 !important');
            $("#refreshOTC a").css({"pointer-events": "fill", "cursor":"pointer !important"});
            $("#refreshOTC").css({"cursor":"pointer"});
            
            
            $.ajax({
                type: "GET",
                url: "../lib/l-custAjax.php?function=CUSAJX_GetCustomerDevicesData",
                data: "compId=" + compId + "&processId=" + proccessId + "&customerNum=" + customerNum + "&orderNum=" + orderNum,
                dataType: 'json',
                success: function(gridData) {
                    $('#customerDevicesGrid').DataTable().destroy();
                    dtLeftList = $('#customerDevicesGrid').DataTable({
                        scrollY: jQuery('#customerDevicesGrid').data('height'),
                        scrollCollapse: true,
                        paging: true,
                        searching: true,
                        ordering: true,
                        aaData: gridData,
                        bAutoWidth: false,
                        select: false,
                        bInfo:false,
                        responsive: true,
                        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                        "language": {
                            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                            searchPlaceholder: "Search"
                        },
                        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                        columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                                targets: "datatable-nosort",
                                orderable: false
                            }],
                        initComplete: function(settings, json) {
                            uniqueCheckBox();
//                    $('#customerDevicesGrid tbody tr:eq(0)').addClass("selected");
                        },
                        drawCallback: function(settings) {
                            $(".dataTables_scrollBody").mCustomScrollbar({
                                theme: "minimal-dark"
                            });
//                    $('.equalHeight').matchHeight();
                            $(".se-pre-con").hide();
                        }

                    });
                    //$('.tableloader').hide();
                },
                error: function(msg) {

                }
            });
        } else {
            $("#refreshOTC a").css({"pointer-events": "none", "color": "#bfbfbf"});
            $("#refreshOTC").css({"cursor":"not-allowed"});
            
            $('#customerDevicesGrid').DataTable().destroy();
            dtLeftList = $('#customerDevicesGrid').DataTable({
                scrollY: jQuery('#customerDevicesGrid').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: [],
                bAutoWidth: false,
                select: false,
                bInfo:false,
                responsive: true,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                initComplete: function(settings, json) {
                    uniqueCheckBox();
//                    $('#customerDevicesGrid tbody tr:eq(0)').addClass("selected");
                },
                drawCallback: function(settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
//                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }

            });
        }
//    $('#customerDevicesGrid').dataTable().fnDestroy();
//    $('#customerDevicesGrid').DataTable({
//        scrollY: jQuery('#customerDevicesGrid').data('height'),
//        scrollCollapse: true,
//        autoWidth: false,
//        serverSide: false,
//        bAutoWidth: true,
//        responsive: true,
//        ordering: true,
//        columnDefs: [{
//                className: "checkbox-btn", "targets": [0]
//            },
//            {
//                className: "ignore", targets: [1, 2, 3, 4]
//            },
//            {
//                targets: "datatable-nosort",
//                orderable: false,
//            }],
//        ajax: {
//            url: "../lib/l-custAjax.php?function=CUSAJX_GetCustomerDevicesData&compId=" + compId + "&processId=" + proccessId + "&customerNum=" + customerNum + "&orderNum=" + orderNum,
//            type: "POST"
//        },
//        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, "100"]],
//        "language": {
//            "info": "_START_-_END_ of _TOTAL_ entries",
//            searchPlaceholder: "Search"
//        },
//        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
//        drawCallback: function (settings) {
//            $(".dataTables_scrollBody").mCustomScrollbar({
//                theme: "minimal-dark"
//            });
//            uniqueCheckBox();
//        }
//
//
//    });
        $('#customerDevicesGrid tbody').on('click', 'tr', function() {
            dtLeftList.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var rowId = $('#customerDevicesGrid tbody tr.selected').attr('id');
            removeRevokeServiceTag(rowId);

        });
    }
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function checks how many linceses are remained, we keep remaining licenses in one hidden field ,hidden
 * field value is nothing but the unused lincenses.
 *----------------------------------------------------------------------------------------------------------------------
 */
function checkRemainingLicenses() {
    $("#renew_license_option a").css({"pointer-events": "none", "color": "#bfbfbf"});
    /*var unusedlicenses = $("#hiddenunusedLicenses").val();
     if (unusedlicenses === "0") {
     $("#renew_button").attr("data-target", "#warning_renew");
     } else {
     getRenewDevices();
     $("#renew_button").attr("data-target", "#renew_details");
     }*/

    //getRenewDevices();

    var rowId = $('#customerDetailsGrid tbody tr.selected').attr('id');

    if (rowId === undefined || rowId === 'undefined') {

    } else {
        var custRowId = rowId.split('##');
        var cid = custRowId[0];
        var pid = custRowId[1];
        var custNum = custRowId[2];
        var ordNum = custRowId[3];
        getNHLicDetails(custNum, ordNum, cid, pid);
        $("#renew_button").attr("data-target", "#renew_details");
    }

}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function get the grid data for renew devices, it will show in pop click on renew button.
 *----------------------------------------------------------------------------------------------------------------------
 */
function getRenewDevices(custNum, ordNum, cust_id, proc_id) {

    $.ajax({
        type: "GET",
        url: "../lib/l-custAjax.php",
        data: "function=CUSTAJX_GetRenewDevices&customerNum=" + custNum + "&orderNum=" + ordNum + "&compId=" + cust_id + "&prcId=" + proc_id,
        dataType: 'json',
        success: function(gridData) {
            $('#renewDevicesGrid').DataTable().destroy();
            table3 = $('#renewDevicesGrid').DataTable({
                scrollY: jQuery('#renewDevicesGrid').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo:false,
                responsive: true,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columnDefs: [{className: "checkbox-btn", "targets": [0]},
                    {
                        className: "ignore", targets: [1, 2, 3, 4]
                    }, {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                drawCallback: function(settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $(".checkbox-btn input[type='checkbox']").change(function() {
                        if ($(this).is(":checked")) {
                            $(this).parents("tr").addClass("selected");
                        }
                    });

                    $('.equalHeight').matchHeight();
                }

            });
            $('.tableloader').hide();
            uniqueCheckBox();
            checkAll();

        },
        error: function(msg) {

        }
    });
    // $("#notification_searchbox").keyup(function() {
    //     table3.search(this.value).draw();
    // });
}


/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function is responsible for behaviour of check boxes present in Datatable. All options will get hide/show
 * which are depend on row selection. Top check box checked unchecked is also achieved here.
 *----------------------------------------------------------------------------------------------------------------------
 */
function uniqueCheckBox() {

    $(".renew_check").change(function() {
        if ($('.renew_check:checked').length == $('.renew_check').length) {
            $('#renew_topcheckbox').prop('checked', true);
        } else {
            $('#renew_topcheckbox').prop('checked', false);
        }

        if ($('.renew_check:checked').length > 0) {
            $("#renew_license_option a").attr('style', 'color: #595959 !important');
            $("#renew_license_option a").css({"pointer-events": "fill"});
        } else {
            $("#renew_license_option a").css({"pointer-events": "none", "color": "#bfbfbf", "cursor": "not-allowed"});
        }
    });

    $(".user_check").change(function() {
        $("#aviraDetailsOption").hide();
        $('.user_check').not(this).prop('checked', false);
        var checkedValue = $(this).val();
        populateInstalledOrderDetails(checkedValue);
        regenerateRevokeHideShow(checkedValue);

        if ($('.user_check:checked').length == 1) {
            $("#orde_details_option").show();
        } else {
            $("#orde_details_option").hide();
        }

        checkedValueArray = checkedValue.split('---');

        if ($('.user_check:checked').length === 1) {
            if (checkedValueArray[4]) {
                $("#aviraDetailsOption").show();
            } else {
                $("#aviraDetailsOption").hide();
            }
        } else {
            $("#aviraDetailsOption").hide();
        }

//        if ($('.user_check:checked').length > 0) {
//            var checkedValue = $(this).val();
//            if (checkedValue !== '' && checkedValue !== 'undefined' && checkedValue !== undefined) {
////                $("#revoke_option").show();
//            }
//
//            if ($('.user_check:checked').length == 1) {
//                regenerateRevokeHideShow(checkedValue);
//            } else {
//                $("#regenerate_option").hide();
//            }
//        } else {
//            $("#revoke_option").hide();
//            $("#regenerate_option").hide();
//        }

//        if ($('.user_check:checked').length == $('.user_check').length) {
//            $('#devicetopcheck').prop('checked', true);
//        } else {
//            $('#devicetopcheck').prop('checked', false);
//        }
    });

//    $('#devicetopcheck').click(function() {
//        $('.user_check').not(this).prop('checked', this.checked);
//        if ($('.user_check:checked').length === 1) {
//            var checkedValue = $('.user_check:checked').val();
//            regenerateRevokeHideShow(checkedValue);
//        } else {
//            $("#revoke_option").hide();
//            $("#regenerate_option").hide();
//        }
//    });

}

function checkAll() {
    $('#renew_topcheckbox').click(function() {
        $('.renew_check').not(this).prop('checked', this.checked);
    });
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function is responsible for bringing sku list which is used in add customer/add subscription pop ups
 *----------------------------------------------------------------------------------------------------------------------
 */
function getAddSkuList(id, skutype) {
    $.ajax({
        type: "GET",
        dataType: 'text',
        url: "../lib/l-custAjax.php",
        data: "function=CUSTAJX_SkuList&skutype=" + skutype,
        success: function(result) {
            $("#" + id).html(result);
        }
    });
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function will create customer, form validation is working from one file -> js/customer/form-validation.js
 *----------------------------------------------------------------------------------------------------------------------
 */
function addCustomer() {

    var isReqFieldsFilled = true;
    var isChecked = true;
    $('.addreq').html('');

    $('.addreq').each(function() {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();

        if ($.trim(field_value) === "") {
            $("#addreq_" + field_id).html(" required");
            isReqFieldsFilled = false;
            return false;
        } else if (field_id == "cust_name") {
            if (!validate_AlphaNumeric(field_value)) {
                isReqFieldsFilled = false;
                $("#addreq_" + field_id).html(" <span>No special characters in company name</span>");
                return false;
            } else {
                $("#addreq_" + field_id).css("color", "red").html("*");
            }
        } else if (field_id == "cust_zipcode") {
            if (!validate_Alphanumeric(field_value)) {
                isReqFieldsFilled = false;
                $("#addreq_" + field_id).html(" <span>enter valid postal code</span>");
                return false;
            } else {
                $("#addreq_" + field_id).css("color", "red").html("*");
            }
        } else if (field_id == "cust_fname") {
            if (!validate_AlphaNumeric(field_value)) {
                isReqFieldsFilled = false;
                $("#addreq_" + field_id).css("color", "red").html(" <span>No special characters in first name</span>");
                return false;
            } else {
                $("#addreq_" + field_id).css("color", "red").html("*");
            }
        } else if (field_id == "cust_lname") {
            if (!validate_AlphaNumeric(field_value)) {
                isReqFieldsFilled = false;
                $("#addreq_" + field_id).css("color", "red").html(" <span>No special characters in last name</span>");
                return false;
            } else {
                $("#addreq_" + field_id).css("color", "red").html("*");
            }
        } else if (field_id == "cust_email") {
            if (!validate_Email(field_value)) {
                isReqFieldsFilled = false;
                $("#addreq_" + field_id).html(" enter valid email");
                return false;
            } else {
                $("#addreq_" + field_id).css("color", "red").html("*");
            }
        }
    });

    if (isReqFieldsFilled == true) {
        var m_data = new FormData();
        m_data.append('customername', $('#cust_name').val());
        m_data.append('address', $('#cust_addr').val());
        m_data.append('city', $('#cust_city').val());
        m_data.append('zipcode', $('#cust_zipcode').val());
        m_data.append('country', $('#add_customer_country').val());
        m_data.append('firstname', $('#cust_fname').val());
        m_data.append('lastname', $('#cust_lname').val());
        m_data.append('email', $('#cust_email').val());
        m_data.append('cust_ordergen', $('#cust_ordergen').val());
        var url = '../lib/l-custAjax.php?function=CUSTAJX_CreateNewCustomer';
        response = insertRecords(m_data, url, 'add_loadingSuccessMsg');
    }
}


function addOtc() {
    var url = '../lib/l-custAjax.php?function=CUSTAJX_AddOtc';
    $.ajax({
        url: url,
        data: m_data,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.status == 1) {
                $('#add_subscription_popup').hide();
                $('#subscription_status_msg').html(response.msg);
                $('#subscription_download_url').val(response.link);
                $('#subscription_status').modal('show');
            } else {
                $("#subs_loadingSuccessMsg").css("color", "red").html(response.msg);
            }
            return response;
        },
        error: function(response) {
            $("#subs_loadingSuccessMsg").css("color", "red").html("<span>Error Occurred</span>");
        }
    });
}



/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function will create subscription, form validation is working from one file -> js/customer/form-validation.js
 *----------------------------------------------------------------------------------------------------------------------
 */
function addSubscription() {
    var isReqFieldsFilled = true;
    var isChecked = true;
    $('.subsreq').html('');

    $('.subsreq').each(function() {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();
        if ($.trim(field_value) === "") {
            $("#subsreq_" + field_id).html(" required");
            isReqFieldsFilled = false;
            return false;
        } else if (field_id == "subs_name") {
            if (!validate_AlphaNumeric(field_value)) {
                isReqFieldsFilled = false;
                $("#subsreq_" + field_id).html(" <span>No special characters in name</span>");
                return false;
            } else {
                $("#subsreq_" + field_id).css("color", "red").html("*");
            }
        } else if (field_id == "subs_email") {
            if (!validate_Email(field_value)) {
                isReqFieldsFilled = false;
                $("#subsreq_" + field_id).html(" <span>enter valid email</span>");
                return false;
            } else {
                $("#subsreq_" + field_id).css("color", "red").html("*");
            }
        } else if (field_id == "subs_custnum") {
            if (!validate_Number(field_value)) {
                isReqFieldsFilled = false;
                $("#subsreq_" + field_id).html(" <span>enter valid customer number</span>");
                return false;
            } else if (!validate_OrderNumber(field_value)) {
                isReqFieldsFilled = false;
                $("#subsreq_" + field_id).html(" <span>enter customer number length between 8 to 16</span>");
                return false;
            } else {
                $("#subsreq_" + field_id).css("color", "red").html("*");
            }
        } else if (field_id == "subs_order") {
            if (!validate_Number(field_value)) {
                isReqFieldsFilled = false;
                $("#subsreq_" + field_id).html(" <span>enter valid order number</span>");
                return false;
            } else if (!validate_OrderNumber(field_value)) {
                isReqFieldsFilled = false;
                $("#subsreq_" + field_id).html(" <span>enter order number length between 8 to 16</span>");
                return false;
            } else {
                $("#subsreq_" + field_id).css("color", "red").html("*");
            }
        }
    });

    if (isReqFieldsFilled == true) {
        var m_data = new FormData();
        m_data.append('subsname', $('#subs_name').val());
        m_data.append('customerNo', $('#subs_custnum').val());
        m_data.append('ordernum', $('#subs_order').val());
        m_data.append('email', $('#subs_email').val());
        m_data.append('subscountry', $('#add_subs_country').val());
        m_data.append('subsskus', $('#add_subs_skus').val());

        var url = '../lib/l-custAjax.php?function=CUSTAJX_CreateNewSubscription';
        $("#subs_loadingSuccessMsg").html('<img src="../vendors/images/ajax-login.gif" class="loadhome" alt="loading..." />');
        $.ajax({
            url: url,
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status == 1) {
                    $('#add_subscription_popup').hide();
                    $('#subscription_status_msg').html(response.msg);
                    $('#subscription_download_url').val(response.link);
                    $('#subscription_status').modal('show');
                } else {
                    $("#subs_loadingSuccessMsg").css("color", "red").html(response.msg);
                }
                return response;
            },
            error: function(response) {
                $("#subs_loadingSuccessMsg").css("color", "red").html("<span>Error Occurred</span>");
            }
        });
    }
}


function setCustomerDetails() {
    var custRowId = $('#customerDetailsGrid tbody tr.selected').attr('id');
    custRowId = custRowId.split("##");
    var cid = custRowId[0];
    var pid = custRowId[1];
    var custNum = custRowId[2];
    var ordNum = custRowId[3];
    getAddSkuList('add_new_subs_skus', 3) //It will fill sku list in Add New Subscription Pop Up to given id as parameter
    $.ajax({
        url: '../lib/l-custAjax.php?function=getCustDetails&cid=' + cid + '&pid=' + pid + '&custNum=' + custNum + '&ordNum=' + ordNum,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#newsubs_name").val(response.coustomerFirstName);
            $("#newsubs_email").val(response.emailId);
            $("#newsubs_phone").val(response.phoneNo);
            $("#newsubs_custnum").val(response.customerNum);
            $("#add_new_subs_country").val(response.country).change();
            $('#newsubs_loadingSuccessMsg').html('');
        },
        error: function(response) {

        }
    });
}




function addNewSubscription() {
    var isReqFieldsFilled = true;
    var custRowId = $('#customerDetailsGrid tbody tr.selected').attr('id');
    custRowId = custRowId.split("##");
    var cid = custRowId[0];
    var pid = custRowId[1];
    var custnum = custRowId[2];


    var orderNum = $('#newsubs_order').val();
    var skuNum = $('#add_new_subs_skus').val();

    var isOderExist = isOrderNumberExist(cid, pid, custnum, orderNum);
    if (isOderExist !== 'true') {
        if (orderNum == '') {
            $('#newsubsreq_subs_order').html('<span>please enter order number</span>');
            isReqFieldsFilled = false;
            return false;
        }

        if (skuNum == '' || skuNum == 'Select sku') {
            $('#newsubs_loadingSuccessMsg').css('color', 'red').html('<span>please select sku</span>');
            isReqFieldsFilled = false;
            return false;
        }

        if (isReqFieldsFilled == true) {
            var m_data = new FormData();
            m_data.append('subsname', $('#newsubs_name').val());
            m_data.append('customerNo', $('#newsubs_custnum').val());
            m_data.append('neworder', $('#newsubs_order').val());
            m_data.append('ordernum', $('#newsubs_order').val());
            m_data.append('email', $('#newsubs_email').val());
            m_data.append('subscountry', $('#add_new_subs_country').val());
            m_data.append('subsskus', $('#add_new_subs_skus').val());

            var url = '../lib/l-custAjax.php?function=CUSTAJX_RenewSubscription';
            $("#newsubs_loadingSuccessMsg").html('<img src="../vendors/images/ajax-login.gif" class="loadhome" alt="loading..." />');
            $.ajax({
                url: url,
                data: m_data,
                processData: false,
                contentType: false,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.status == 1) {
                        $('#add_new_subscription_popup').hide();
                        $('#subscription_status_msg').html(response.msg);
                        $('#subscription_download_url').val(response.link);
                        $('#subscription_status').modal('show');
                    } else {
                        $("#newsubs_loadingSuccessMsg").css("color", "red").html(response.msg);
                    }
                    return response;
                },
                error: function(response) {
                    $("#newsubs_loadingSuccessMsg").css("color", "red").html("<span>Error Occurred</span>");
                }
            });
        }
    } else {
        $('#newsubs_loadingSuccessMsg').css('color', 'red').html('<span>order number aleardy exist</span>');
        return false;
    }

}

function isOrderNumberExist(cid, pid, custnum, ordernum) {

    $.ajax({
        url: "../lib/l-custAjax.php?function=isOrderExist&cid=" + cid + "&pid=" + pid + "&custnum=" + custnum + "&ordernum=" + ordernum,
        processData: false,
        async: false,
        contentType: false,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            return response;
        },
        error: function(response) {

        }
    });
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function will make one ajax call to perform insert operation on server side.
 *----------------------------------------------------------------------------------------------------------------------
 */
function insertFormValues(m_data, url, loaderId) {

    $("#" + loaderId).html('<img src="../vendors/images/ajax-login.gif" class="loadhome" alt="loading..." />');
    $.ajax({
        url: url,
        data: m_data,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.status == 1) {
                $("#" + loaderId).html(response.msg);
            } else {
                $("#" + loaderId).css("color", "red").html(response.msg);
            }
            return response;
        },
        error: function(response) {
            $("#" + loaderId).css("color", "red").html("<span>Error Occurred</span>");
        }
    });
}


/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function makes ajax call to renew devices, if devices are already in renew state then it will return failed
 * devices count
 *----------------------------------------------------------------------------------------------------------------------
 */
function renewSelectedDevices() {
    var selNH_ord = $("#NHLic_reseller_License").val();
    var selNH_pcCnt = $("#NHLicpcCnt").val();
    var custNum = $("#NHLicCustno").val();
    var ordNum = $("#NHLicOrdno").val();
    var cId = $("#NHLicCompId").val();
    var pId = $("#NHLicProId").val();
    var allVals = [];
    $('.renew_check:checked').each(function() {
        allVals.push($(this).val());
    });

    var totalSelDevices = allVals.length;
    var availLicenses = $("#NHLicpendingCnt").val();

    if (totalSelDevices > availLicenses) {
        alert("You selected more than avaialable licenses");
        return false;
    }


    $.ajax({
        type: "GET",
        dataType: 'json',
        url: "../lib/l-custAjax.php",
        data: "function=CUSAJX_RenewSelectedLicense&selecDevices=" + allVals + "&custNum=" + custNum + "&ordNum=" + ordNum + "&cId=" + cId + "&pId=" + pId + "&selNHLic=" + selNH_ord + "&selPcCnt=" + selNH_pcCnt,
        success: function(result) {
            $('#renew_details').modal('hide');
            $('#renew_status_msg').html('<span>'+result.msg+'</span>');
            $('#renew_status').modal('show');

        }
    });
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function revoke selected devices,
 *----------------------------------------------------------------------------------------------------------------------
 */
function revokeOrder() {

    var checkedDevices = [];
    $(".user_check").each(function() {
        if ($(this).is(':checked')) {
            checkedDevices.push($(this).attr('id'));
        }
    });

    $("#revoke_errorMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
    $.ajax({
        type: "GET",
        dataType: 'json',
        url: "../lib/l-custAjax.php",
        data: "function=CUSTAJX_RevokeDevice&selecDevices=" + checkedDevices,
        success: function(result) {
            $("#revoke_errorMsg").html();
            if (result.status == 1) {
                $('#revoke_popup').hide();
                $('#subscription_status_msg').html(result.msg);
                $('#subscription_download_url').val(result.link);
                $('#subscription_status').modal('show');
            } else {
                $("#revoke_popup .add-user-add-btn").hide();
                $("#revoke_status_msg").css("color", "red").html(result.msg);
            }
        }
    });
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * Following function regenerate device, return url. It will close current pop up and gives url pop up with copy button.
 *----------------------------------------------------------------------------------------------------------------------
 */
function regenerateOrder() {
    $(".user_check").each(function() {
        if ($(this).is(':checked')) {
            selectedDevice = $(this).attr('id');
        }
    });

    $.ajax({
        type: "GET",
        dataType: 'json',
        url: "../lib/l-custAjax.php",
        data: "function=CUSTAJX_RegenerateDevice&selecDevices=" + selectedDevice,
        success: function(result) {
            if (result.status == 1) {
                $("#rengenerate_download_url").val(result.link);
                $('#regenerate_popup').modal().hide();
                $('#regenerate_url').modal().show();
            } else {
                $("#regenerate_popup .add-user-add-btn").hide();
                $("#regenrate_status_msg").html(result.msg);
            }
        }
    });
}


$("#addNewSite").blur(function() {
    var sitename = $.trim($('#addNewSite').val());
    if (sitename !== '') {
        $.ajax({
            url: "../lib/l-custAjax.php?function=CUSTAJX_IsSiteExist&siteName=" + sitename,
            type: 'POST',
            dataType: 'text',
            success: function(response) {
                if ($.trim(response) === "TRUE") {
                    $("#addNewSite_error").html("<span>Site name already exist</span>");
                } else {
                    $("#addNewSite_error").html("");
                }
            },
            error: function(err) {

            }
        });
    } else {
        $("#addNewSite_error").html("<span>Please enter site name</span>");
    }


});


/*
 *----------------------------------------------------------------------------------------------------------------------
 * It will create site for MSP customer
 *----------------------------------------------------------------------------------------------------------------------
 */
function customerpop() {
    $(".error").html(" *");
    var sitename = $('#addNewSite').val();

    var pendingCnt = $('#aviraPending').text();
    var errorVal = 0;
    var status = 1;
    var compName = '';
    var firstName = '';
    var lastName = '';
    var status_val = $.trim($('input[name=status]:checked').val()); //Customer creation is for myself or customer individual
    var otcType = $('#otcType').val();
    var language = $('#lang').val();
    if (sitename === '') {
        $("#addNewSite_error").html('<span>Please Enter Site Name</span>');
        errorVal++;

    }
    if (sitename.indexOf("__") != -1) {
        $("#addNewSite_error").html('<span>More than one underscore not allowed</span>');
        errorVal++;
    }
    
    if (sitename !== '') {
        if (!customer_validate_Alphanumeric(sitename)) {
            $("#addNewSite_error").html('<span>Special character not allowed in Site Name other than(-_.)</span>');
            errorVal++;
        }
    }
    if (sitename !== '') {
        if (sitename.length > 25) {
            $("#addNewSite_error").html('<span>Max 25 characters are allowed in Customer Name</span>');
            error++;
        }
    }
    if (status_val == 1) {
        var email = $("#email").val();
        compName = $('#compName').val();
        var aviraotc = $('#myself_aviraotc').val();
        var pcCnt = $('#avira_pcno').val();
        var successMessage = '';
        if (aviraotc === "") {
            $("#myself_aviraotc_error").html('<span>Please select OTC code</span>');
            errorVal++;
        }

        if (pcCnt <= 0) {
            $("#avira_pcno_error").html('<span>Please enter valid count</span>');
            errorVal++;
        }

        if (pcCnt > parseInt(pendingCnt)) {
            $("#avira_pcno_error").html('<span>Entered count is greater than available licenses</span>');
            errorVal++;
        }
    } else {
        var email = $("#cust_Email").val();
        compName = $('#addNewSite').val();
        firstName = $('#cust_firstName').val();
        lastName = $('#cust_lastName').val();
        var aviraotc = $('#customer_aviraotc').val();

        if (firstName == '') {
            $("#cust_firstName_error").html('<span>Please Enter First Name</span>');
            errorVal++;

        }
        if (lastName == '') {
            $("#cust_lastName_error").html('<span>Please Enter Last Name</span>');
            errorVal++;

        }
        if (email == '') {
            $("#cust_email_error").html('<span>Please Enter Email</span>');
            errorVal++;

        }

    }

    if (errorVal == 0 || errorVal === 0) {
        $("#addNewSite_error").html('*');
        $("#req_pcCnt").html('*');
        var trialSite = $('#trialSite').val();
        $.ajax({
            url: "../customer/addCustomerModel.php?function=addSitename&sitename=" + sitename + "&trialSite=" + trialSite + "&aviraOtc=" + otcType + "&new_otc=" + aviraotc + "&pcCnt=" + pcCnt + "&new_email=" + email + "&new_compName=" + compName + "&status=" + status_val + "&firstName=" + firstName + "&lastName=" + lastName + "&language=" + language,
            type: 'POST',
            dataType: 'json',
            success: function(response) {

                if (response.msg === 'Success') {
                    $('#createCustomerDiv').hide();
                    $("#clickherelink").val(response.clientUrl);
                    $("#downloadId").val(response.link);
                    //$('#Createcustomer').modal('show');
                    $("#aviraConfigureNext").show();
                    $("#aviraConfigureNextForGateway").hide();
                    $("#aviraConfigurePrevious").hide();
                    $('#avira_configureDiv').show();
                    if (status == 1) {
                        $("#clickHereDownLoad").show();
                        successMessage = sitename + '<span>&nbsp;has been successfully created</span>';
                    } else {
                        $("#clickHereDownLoad").hide();
                        successMessage = sitename + ' <span>&nbsp;has been successfully created.</span> ' + '<span>Email has been sent to given email address</span> ';
                    }
                    $("#custNo_val").html('<b>Customer ID:</b>' + response.link);
                    $("#add_successMsg").html(successMessage);
                    
                    
                   
                     
                    //$('#Createcustomer').modal('show');
                    $('#avira_configure').modal('show');
                    if (status_val == 1) {
                        $("#clickHereDownLoad").show();
                        successMessage = sitename + '<span>&nbsp;has been successfully created</span>';
                    } else {
                        $("#clickHereDownLoad").hide();
                        successMessage = sitename + ' <span>&nbsp;has been successfully created.</span> ' + '<span>Email has been sent to given email address</span> ';
                    }
                    $("#custNo_val").html('<b>Customer ID:</b>' + response.link);
                    $("#add_successMsg").html(successMessage);

                } else {
                    $("#add_errorMsg").html(response.msg);
                }
            },
            error: function(err) {

            }
        });
    }
}



/**
 * This function is written for creating customer/site for MSP(Commercial) bussiness.
 * MSP flow for creating customer has been changed. Order number drop down came in add customer pop up.
 * @returns {undefined}
 */
function msp_CreateCustomer() {
    $(".error").html("*");                                      //Make all error messages empty.
    var errorFlag = 0;                                          //Error 
    var customerName = $("#msp_SiteName").val();               //User entered customer name.
    var orderNumber = $("#msp_OrderDropDown").val();          //Order number selected in order number drop down
    var userEnteredPc = $("#msp_PcCount").val();                //User entered lisence/pc count.
    var pendingCount = parseInt($("#msp_PendingCount").val()); //This is pending licenses for selected order number. Parsing is done for comparision.

    if (customerName === "" || !customer_validate_Alphanumeric(customerName)) {
        $("#msp_SiteName_error").html("<span>Please enter valid customer name</span>");
    } else if (orderNumber === "" || orderNumber === undefined) {
        $("#msp_OrderDropDown_error").html("<span>Please select order number</span>");
    }
    if (userEnteredPc <= 0) {
        $("#msp_PcCount_error").html("<span>Please enter valid count</span>");
    } else if (userEnteredPc > pendingCount) {
        $("#edit_errorMsg").html("<span>Entered licenses are not available</span>");
        errorFlag++;
    }


}

$("#reconfiure_avira").change(function() {
    if (this.value == 1) {
        $("#edit_customer_next").show();
        $("#edit_customer_update").hide();
    }else{
        $("#edit_customer_update").show();
        $("#edit_customer_next").hide();
    }
});

/*
 *----------------------------------------------------------------------------------------------------------------------
 * It will create site for MSP customer
 *----------------------------------------------------------------------------------------------------------------------
 */
function edit_customerpop() {
    $("#edit_errorMsg").html("");
    var errorFlag = 0;
    var reconfiure_avira = $('#reconfiure_avira:checked').val();
    var avira_total = $('#edit_HiddenAviraTotal').val();
    var avira_pending = $('#edit_HiddenAviraPending').val();
    var avira_used = $('#edit_HiddenAviraUsed').val();

    var customer_used = $('#edit_HiddenCustomerUsed').val();
    var customer_install = $('#edit_HiddenCustomerIns').val();
    var user_entered = $('#edit_pcCnt').val();
    var avira_enabled = $("#avira_enabled").val();
    var customer_OTC = $("#edit_aviraotc").val();

    var highest_available = parseInt(avira_pending) + parseInt(customer_used);

    if (user_entered <= 0) {
        $("#edit_errorMsg").html("<span>Please enter valid count</span>");
        errorFlag++;
    } else if (user_entered < parseInt(customer_install)) {
        $("#edit_errorMsg").html("<span>Please enter PC count greater than installed</span>");
        errorFlag++;
    } else if (user_entered > parseInt(highest_available)) {
        $("#edit_errorMsg").html("<span>Entered number of licenses are not available</span>");
        errorFlag++;
    }

    if (errorFlag === 0) {
        var rowId = $('#customerDetailsGrid tbody tr.selected').attr('id');
        var custRowId = rowId.split('##');

        var custNum = custRowId[2];
        var ordNum = custRowId[3];
        $("#edit_errorMsg").html('');
        $.ajax({
            url: "../lib/l-custAjax.php?function=CUSTAJX_EditAviraCustomer&pcCnt=" + user_entered + "&custNum=" + custNum + "&ordNum=" + ordNum + "&OTC=" + customer_OTC,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status == 1) {
                    if (reconfiure_avira == 1) {
                        $("#edit_site").modal('hide');
                        $("#avira_configure").modal('show');
                    } else {
                        $("#edit_errorMsg").css("color", "green").html(response.message);
                        setInterval(function() {
                            $("#edit_site").modal('hide');
                            location.reload();
                        }, 3000);
                    }
                } else {
                    $("#edit_errorMsg").css("color", "red").html(response.message);
                }


            },
            error: function(response) {

            }
        });
    }
}


function otcpopUp() {


    var aviraotc = $('#aviraotcVal').val();
    var aviraEmail = $('#aviraEmail').val();
    var aviraCompName = $('#aviraCompName').val();
    var error = 0;
    if (aviraotc === '') {
        $("#addotc_errorMsg").html("<span>Please enter OTC code.</span>");
        error++;
    } else if (aviraEmail === '') {
        $("#addotc_errorMsg").html("<span>Please enter Email.</span>");
        error++;
    } else if (aviraEmail !== '') {
        if (!myacount_validate_Email(aviraEmail)) {
            $("#addotc_errorMsg").html("<span>Please enter valid email id.</span>");
            error++;
        }
    } else if (aviraCompName === '') {
        $("#addotc_errorMsg").html("<span>Please enter company name.</span>");
        error++;
    }

    if (error === 0) {
        $("#addotc_errorMsg").html('<img src="../vendors/images/ajax-login.gif" class="loadhome" alt="loading..." />');
        $.ajax({
            url: "../customer/addCustomerModel.php?function=addAviraOtc&aviraOtc=" + aviraotc + "&aviraEmail=" + aviraEmail + "&aviraCompName=" + aviraCompName,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $("#addotc_errorMsg").html('');
                if (response.status === "DUPLICATE") {
                    $("#addotc_errorMsg").html("<span>This OTC code already exist</span>");
                } else if (response.status === "SUCCESS") {
                    $("#addotc_errorMsg").css("color", "green").html("<span>OTC Code added successfully</span>");
                    setInterval(function() {
                        $("#addotc_Popup").modal('hide');
                        location.reload();
                    }, 3000);
                } else if (response.status === "ERROR") {
                    $("#addotc_errorMsg").css("color", "red").html("<span>Please enter valid OTC code.</span>");
                } else {
                    $("#addotc_errorMsg").css("color", "red").html('<span>'+response.status+'</span>');
                }

            },
            error: function(err) {

            }
        });
    }
}



//To open download page in new tab when click on "click here to download"
function downloadClientUrl() {
    var url = $("#clickherelink").val();
    window.open(url, '_blank');
}


/*
 *----------------------------------------------------------------------------------------------------------------------
 * This will get called on key entered in customer number text box in renew subscription pop up
 * returns what are all orders are available for that customer number
 *----------------------------------------------------------------------------------------------------------------------
 */
$("#subs_recustnum").keyup(function() {
    var custNumber = $("#subs_recustnum").val();

    if (!validate_Number(custNumber)) {
        $("#renewSubReq_subs_recustnum").html("<span>please enter valid customer number</span>");
        return false;
    } else if (!validate_OrderNumber(custNumber)) {
        $("#renewSubReq_subs_recustnum").html(" <span>enter customer number length between 8 to 16</span>");
        return false;
    } else {
        $("#renewSubReq_subs_recustnum").html(" *");
        $.ajax({
            url: "../lib/l-custAjax.php?function=CUSTAJX_GetOrders&custNumber=" + custNumber,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $("#renew_order_list").html(response.list);
                var orderDtls = response.firstOrderDtls.split("---");
                var customerNumber = orderDtls[0];
                var ordernNmber = orderDtls[1];
                populateRenewFields(customerNumber, ordernNmber);
            },
            error: function(err) {
                $("#subsreq_subs_renewcustnum").html("<span>Something went wrong</span>");
            }
        });
    }

});


/*
 *----------------------------------------------------------------------------------------------------------------------
 * Onchange of orders drop down, this function will get called
 *----------------------------------------------------------------------------------------------------------------------
 */
function repopulateRenewFields() {
    var customerNumber = $("#subs_recustnum").val();
    var ordernNmber = $("#renew_order_list").val();
    populateRenewFields(customerNumber, ordernNmber);
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * It will populate all fields present in renew pop for PTS customer
 *----------------------------------------------------------------------------------------------------------------------
 */
function populateRenewFields(customerNumber, ordernNmber) {
    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_GetCustDetails&custNum=" + customerNumber + '&ordNum=' + ordernNmber,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#renewsubs_name").val(response.coustomerFirstName);
            $("#renewsubs_email").val(response.emailId);
            $("#renewsubs_phone").val('');
            $("#renewsubs_country").val(response.country).change();
            $("#previous_skus").val(response.SKUDesc);
            $("#contractEndDate").val(response.contractEndDate);
            getAddSkuList('renewsubs_skus', 8); //8 is for list of available renew sku
        },
        error: function(err) {
            $("#subsreq_subs_renewcustnum").html("<span>Something went wrong</span>");
        }
    });
}


/*
 *----------------------------------------------------------------------------------------------------------------------
 * It will renew subscription for PTS(consumer) customer.
 * By using which we can renew dataa
 *----------------------------------------------------------------------------------------------------------------------
 */
function renewPTSSubscription() {
    $('.renewSubReq').html(' *');
    var isReqFieldsFilled = true;

    $('.renewSubReq').each(function() {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();
        if ($.trim(field_value) === "") {
            $("#renewSubReq_" + field_id).html(" <span>required</span>");
            isReqFieldsFilled = false;
            return false;
        } else if (field_id == 'renewsubs_name') {
            if (!validate_AlphaNumeric(field_value)) {
                isReqFieldsFilled = false;
                $("#renewSubReq_" + field_id).html(" <span>No special characters in name</span>");
                return false;
            } else {
                $("#renewSubReq_" + field_id).css("color", "red").html("*");
                isReqFieldsFilled = true;
            }
        } else if (field_id == 'renewsubs_email') {
            if (!validate_Email(field_value)) {
                isReqFieldsFilled = false;
                $("#renewSubReq_" + field_id).html(" <span>enter valid email</span>");
                return false;
            } else {
                $("#renewSubReq_" + field_id).css("color", "red").html("*");
                isReqFieldsFilled = true;
            }
        } else if (field_id == 'renewsubs_new_order') {
            if (!validate_Number(field_value)) {
                isReqFieldsFilled = false;
                $("#renewSubReq_" + field_id).html(" <span>enter valid order number</span>");
                return false;
            } else if (!validate_OrderNumber(field_value)) {
                isReqFieldsFilled = false;
                $("#renewSubReq_" + field_id).html(" <span>enter order number length between 8 to 16</span>");
                return false;
            } else {
                $("#renewSubReq_" + field_id).css("color", "red").html("*");
                isReqFieldsFilled = true;
            }
        } else {
            isReqFieldsFilled = true;
        }
    });

    if (isReqFieldsFilled == true) {
        var m_data = new FormData();
        m_data.append('subsname', $('#renewsubs_name').val());
        m_data.append('custnum', $('#subs_recustnum').val());
        m_data.append('ordernum', $('#renew_order_list').val());
        m_data.append('neworder', $('#renewsubs_new_order').val());
        m_data.append('email', $('#renewsubs_email').val());
        m_data.append('subscountry', $('#renewsubs_country').val());
        m_data.append('subsskus', $('#renewsubs_skus').val());
        m_data.append('contractEdate', $('#contractEndDate').val());

        var url = '../lib/l-custAjax.php?function=CUSTAJX_RenewSubscription';
        $("#renewsubs_loadingSuccessMsg").html('<img src="../vendors/images/ajax-login.gif" class="loadhome" alt="loading..." />');
        $.ajax({
            url: url,
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status == 1) {
                    $('#renew_subscription_popup').hide();
                    $('#subscription_status_msg').html(response.msg);
                    $('#subscription_download_url').val(response.link);
                    $('#subscription_status').modal('show');
                } else {
                    $("#renewsubs_loadingSuccessMsg").css("color", "red").html(response.msg);
                }
                return response;
            },
            error: function(response) {
                $("#renewsubs_loadingSuccessMsg").css("color", "red").html("<span>Error Occurred</span>");
            }
        });
    }
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * It will hide or show Revoke/Regenerate options on the basis of Download status and revoke statut of machine.
 * If selectec machine value is empty then it will hide both options
 *----------------------------------------------------------------------------------------------------------------------
 */
function regenerateRevokeHideShow(selectedMachine) {
    if (selectedMachine != '') {
        var machineDetails = selectedMachine.split('---');
        var sid = machineDetails[4];
        $.ajax({
            url: "../lib/l-custAjax.php?function=getMachineDetails&sid=" + sid,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                var downStatus = response.downloadStatus;
                var revokeStatus = response.revokeStatus;
                if (downStatus === 'EXE' && revokeStatus === 'I') {
                    $("#revoke_option").show();
                    $("#regenerate_option").hide();
                } else if ((downStatus === 'D' || downStatus === 'G') && revokeStatus === 'I') {
                    $("#revoke_option").hide();
                    $("#regenerate_option").show();
                } else {
                    $("#revoke_option").hide();
                    $("#regenerate_option").hide();
                }
            },
            error: function(response) {
                $("#renewsubs_loadingSuccessMsg").css("color", "red").html("<span>Error Occurred</span>");
            }
        });
    } else {
        $("#revoke_option").hide();
        $("#regenerate_option").hide();//Do Nothing
        $("#aviraDetailsOption").hide();
    }
}

//This will fetch data for those orders which have installed on machines
function populateInstalledOrderDetails(selectedOrder) {
    $("#cust_details_option").show();

    if (selectedOrder != '') {
        var machineDetails = selectedOrder.split('---');
        var sid = machineDetails[4];
        if (machineDetails[4]) {
            $.ajax({
                url: "../lib/l-custAjax.php?function=getInstalledOrderDetails&sid=" + sid,
                processData: false,
                contentType: false,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $("#site_name").val(response.siteName);
                    $("#customer_num").val(response.customerNum);
                    $("#customer_Id").val(response.downloadId);
                    $("#customer_url").val(response.downloadUrl);
                },
                error: function(response) {

                }
            });
        } else {
            populateNotInstalledOrderDetails(selectedOrder);
        }

    } else {
        $("#customer_details .popup-title").html("<span>Customer Details</span>");
    }
}

//This will fetch data for those orders which is not yet installed on any machine.
function populateNotInstalledOrderDetails(selectedOrder) {
    $("#customer_details .popup-title").html("<span>Order Details</span>");
    var custRowId = selectedOrder.split("---");
    var cid = custRowId[0];
    var pid = custRowId[1];
    var custNum = custRowId[2];
    var ordNum = custRowId[3];
    $.ajax({
        url: '../lib/l-custAjax.php?function=getNotInstalledOrderDetails&cid=' + cid + '&pid=' + pid + '&custNum=' + custNum + '&ordNum=' + ordNum,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#site_name").val(response.siteName);
            $("#customer_num").val(response.customerNum);
            $("#customer_Id").val(response.downloadId);
            $("#customer_url").val(response.downloadUrl);
        },
        error: function(response) {

        }
    });
}


/*
 *----------------------------------------------------------------------------------------------------------------------
 * This function will get called only in case of Avira.
 * We got avira as client and they have some differrent concept regarding details.
 * They want to show show there information in grid format.
 * We have session called $_SESSION['user']['aviraId'] to know that this is Avira.
 *----------------------------------------------------------------------------------------------------------------------
 */
function setAviraDetails() {
    var checkedValue = $('.user_check:checked').val();
    var checkedArray = checkedValue.split('---');
    var sid = checkedArray[4];

    $.ajax({
        type: "GET",
        dataType: 'text',
        url: "../lib/l-custAjax.php",
        data: "function=CUSAJX_IsAviraInstalled&sid=" + sid,
        success: function(result) {

            if (result == 1) {
                console.log(result);
                $("#aviraInstalled").show();
                $("#aviraNotInstalled").hide();

                $('#aviraDetails_Grid').dataTable().fnDestroy();
                $('#aviraDetails_Grid').DataTable({
                    scrollY: jQuery('#aviraDetails_Grid').data('height'),
                    scrollCollapse: true,
                    autoWidth: false,
                    serverSide: false,
                    bAutoWidth: true,
                    responsive: true,
                    ordering: true,
                    searching: false,
                    columnDefs: [{
                            targets: "datatable-nosort",
                            orderable: false,
                        }, {
                            className: "ignore", targets: [0, 1, 2, 3]
                        }],
                    ajax: {
                        url: "../lib/l-custAjax.php?function=CUSTAJX_GetAviraDetailsGrid&sid=" + sid,
                        type: "POST"
                    },
                    columns: [
                        {"data": "serviceTag"},
                        {"data": "prodName"},
                        {"data": "prodDate"},
                        {"data": "licenseExpire"},
                    ],
                    "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    "language": {
                        "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                        searchPlaceholder: "Search"
                    },
                    "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>'
                });

            } else {

                $("#aviraInstalled").hide();
                $("#aviraNotInstalled").show();
            }

        }
    });
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * This function will check that the adding customer user is trial.
 * If it is trial then it ll throw error.
 * If it is not then it ll check available licenses and contract end date.
 *----------------------------------------------------------------------------------------------------------------------
 */
function isValidAddCustomer() {
//    $.ajax({
//        url: '../lib/l-custAjax.php?function=CUSTAJX_IsValidAddCustomer',
//        type: 'POST',
//        dataType: 'json',
//        success: function(response) {
//            if (response.status == '3') {
//                $("#addValidCustomer").show();
//                $("#addInValidCustomer").hide();
//            } else {
//                $("#addInValidCustomer_msg").html(response.msg);
//                $("#addValidCustomer").hide();
//                $("#addInValidCustomer").show();
//            }
//        },
//        error: function(response) {
//            console.log(response);
//            console.log("something went wrong in ajax call in isValidAddCustomer function");
//        }
//    });
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * This will get called when click on add customer Avira otc code
 * It will bring data otc codes
 * It populate drop down
 *----------------------------------------------------------------------------------------------------------------------
 */

function getOtcCodeList() {
    $(".new_otc").hide();
    $(".old_otc").show();
    $.ajax({
        url: '../lib/l-custAjax.php?function=CUSTAJX_getOTClist',
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            $("#myself_aviraotc").html(response);
            $(".selectpicker").selectpicker("refresh");
            triggerConfiureGatewayCheckEvent();
        },
        error: function(response) {
            console.log(response);
            console.log("something went wrong in ajax call in isValidAddCustomer function");
        }
    });
}

function getAviraLicDetails() {
    var selectedOTS = $("#myself_aviraotc").val();
//    if (selectedOTS == "new") {
//        $(".new_otc").show();
//        $(".old_otc").hide();
//    } else {
//        $(".new_otc").hide();
//        $(".old_otc").show();
//    }

    if (selectedOTS !== "") {
        $("#otcType").val('old');
        $.ajax({
            url: '../lib/l-custAjax.php?function=CUSTAJX_getAviraOTCDtl&otcCode=' + selectedOTS,
            type: 'POST',
            dataType: 'text',
            success: function(response) {
                var checkedArray = response.split('---');
                $("#aviraTotal").html(checkedArray[0]);
                $("#aviraUsed").html(checkedArray[3]);
                $("#aviraCntDt").html(checkedArray[2]);
                $("#aviraPending").html(checkedArray[1]);
                $("#avira_pcno").val(checkedArray[1]);
                $("#avira_pcno").attr("max", checkedArray[1]);
                $("#pendingCnt").val(checkedArray[1]);
                triggerConfiureGatewayCheckEvent();
            },
            error: function(response) {
                console.log(response);
                console.log("something went wrong in ajax call in getAviraLicDetails function");
            }
            
        });
    } else {
        $("#otcType").val('');
        $("#aviraTotal").html("");
        $("#aviraUsed").html("");
        $("#aviraPending").html("");
        $("#aviraCntDt").html("");
    }

}


function getNHLicDetails(custNum, ordNum, cid, pid) {
    $("#NHLicDetDiv").show();
    $("#renewDevicesDiv").hide();
    $("#NHdrop_renewOrder").hide();
    $("#NHLicCustno").val('');
    $("#NHLicOrdno").val('');
    $("#NHLicTotal").html('');
    $("#NHLicCompId").val('');
    $("#NHLicProId").val('');
    $("#NHLicUsed").html('');
    $("#NHLicCntDt").html('');
    $("#NHLicPending").html('');
    $("#NHLicpcCnt").attr("max", 1);
    $("#NHLicpendingCnt").val('');
    $("#NHLicCustno").val(custNum);
    $("#NHLicOrdno").val(ordNum);
    $("#NHLicCompId").val(cid);
    $("#NHLicProId").val(pid);
    $.ajax({
        url: '../lib/l-custAjax.php?function=CUSTAJX_getNHLIClist',
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            $("#NHLic_reseller_License").html(response);
            $(".selectpicker").selectpicker("refresh");
        },
        error: function(response) {
            console.log(response);
            console.log("something went wrong in ajax call in isValidAddCustomer function");
        }
    });

}

function validateNHLic() {

    var NHLicpcCnt = $("#NHLicpcCnt").val();
    var NHLicpendingCnt = $("#NHLicpendingCnt").val();
    var custNum = $("#NHLicCustno").val();
    var ordNum = $("#NHLicOrdno").val();
    var comp_id = $("#NHLicCompId").val();
    var pro_id = $("#NHLicProId").val();
    if (NHLicpcCnt <= 0) {
        $("#NH_LicErrorMsg").html('<span>Please enter valid count</span>');
        return false;
    }

    if (NHLicpcCnt > parseInt(NHLicpendingCnt)) {
        $("#NH_LicErrorMsg").html('<span>Entered count is greater than available licenses</span>');
        return false;
    }

    $("#NHLicDetDiv").hide();
    $("#renewDevicesDiv").show();
    $("#NHdrop_renewOrder").show();
    getRenewDevices(custNum, ordNum, comp_id, pro_id);
}


function renewSelectedBack() {

    $("#NHLicDetDiv").show();
    $("#renewDevicesDiv").hide();
    $("#NHdrop_renewOrder").hide();

}


function getNHInsDetails() {
    var selectedOTS = $("#NHLic_reseller_License").val();

    if (selectedOTS !== "") {
        $.ajax({
            url: '../lib/l-custAjax.php?function=CUSTAJX_getNHOTCDtl&otcCode=' + selectedOTS,
            type: 'POST',
            dataType: 'text',
            success: function(response) {
                var checkedArray = response.split('---');
                $("#NHLicTotal").html(checkedArray[0]);
                $("#NHLicUsed").html(checkedArray[3]);
                $("#NHLicCntDt").html(checkedArray[2]);
                $("#NHLicPending").html(checkedArray[1]);
                $("#NHLicpcCnt").attr("max", checkedArray[1]);
                $("#NHLicpendingCnt").val(checkedArray[1]);
            },
            error: function(response) {
                console.log(response);
                console.log("something went wrong in ajax call in getAviraLicDetails function");
            }
        });
    } else {
        $("#aviraTotal").html("");
        $("#aviraUsed").html("");
        $("#aviraPending").html("");
        $("#aviraCntDt").html("");
    }

}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * This will get called when click on search icon in top left search field.
 * It will bring data based on email, order number, customer number
 * It populate Customer grid, bottom left grid on My Account Page
 *----------------------------------------------------------------------------------------------------------------------
 */

$("#myaccount_searchbox_icon").click(function() {
    var searchVal = $("#myaccount_searchbox").val();
    var avira_enabled = $("#avira_enabled").val();
    if (bussLevel === 'Commercial') {
        if (searchVal !== "") {
            getSearchCustomersGrid(searchVal);
        } else {
            if (avira_enabled == '1' || avira_enabled === '1') {
                getOtcDetailsGrid();
            } else {
                getCustomersGrid();
            }
        }
    } else {
        if (searchVal !== "") {
            getSearchSkuCustomersGrid(searchVal);
        } else {

            getSkuDetailsGrid();
        }
    }
});


/*
 *----------------------------------------------------------------------------------------------------------------------
 * This will get called when click on Edit Customer.
 * It will bring data based on selected customer on bottom left grid
 * It populate edit customer pop up.
 *----------------------------------------------------------------------------------------------------------------------
 */
function edit_AviraCustomerDetails() {
    var rowId = $('#customerDetailsGrid tbody tr.selected').attr('id');
    var custRowId = rowId.split('##');
    var cid = custRowId[0];
    var pid = custRowId[1];
    var custNum = custRowId[2];
    var ordNum = custRowId[3];

    if (rowId !== undefined) {
        $.ajax({
            url: '../lib/l-custAjax.php?function=CUSTAJX_GetAviraCustomerDetails&cid=' + cid + "&pid=" + pid + "&custNum=" + custNum + "&ordNum=" + ordNum,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $("#edit_Site").val(response.coustomerFirstName);
                $("#edit_aviraTotal").html(response.otcDetails['licenses']);
                $("#edit_aviraPending").html(response.otcDetails['pending']);
                $("#edit_aviraUsed").html(response.otcDetails['pccount']);
                $("#edit_aviraCntDt").html(response.otcDetails['contractEndDate']);
                $("#edit_aviraotc").val(response.otcDetails['otcCode']);

                $("#edit_HiddenAviraTotal").val(response.otcDetails['licenses']);	//Hidden fields for OTC total counts
                $("#edit_HiddenAviraPending").val(response.otcDetails['pending']);	//Hidden fields for OTC pending counts
                $("#edit_HiddenAviraUsed").val(response.otcDetails['pccount']);	//Hidden fields for OTC used(assigned to customers) counts
                $("#edit_HiddenCustomerUsed").val(response.noOfPc);	//Hidden fields for total count assigned to customer
                $("#edit_HiddenCustomerIns").val(response.used); //Hidden fields for total installed

                $("#edit_pcCnt").val(response.noOfPc); //No Of Pc text field, its input type is number.
                $("#edit_pendingCnt").val(response.otcDetails['pending']);
                var max = parseInt(response.otcDetails['pending']) + parseInt(response.noOfPc);
                $("#edit_pcCnt").attr({"max": max, "min": response.used});
                $("#edit_errorMsg").html("");
                $("#downloadId").val(response.downloadId);
                $("#edit_downloadId").val(response.downloadId);
            },
            error: function(response) {
                console.log(response);
                console.log("something went wrong in ajax call in edit_AviraCustomerDetails function");
            }
        });
    } else {

    }
}


function modifyCustomerDetailPop() {
    $("#customer_details").html("<span>Order Details</span>");
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * Click functionality for copy text from given input field id to clipboard
 *----------------------------------------------------------------------------------------------------------------------
 */
$('#copy_link1').click(function() {
    var urlField = document.querySelector('#rengenerate_download_url');
    urlField.select();
    document.execCommand('copy');
});

$('#copy_link2').click(function() {
    var urlField = document.querySelector('#subscription_download_url');
    urlField.select();
    document.execCommand('copy');
});


$('input[type=radio][name=status]').change(function() {
    if (this.value == 1) {
        $(".myself_avira").show();
        $(".customer_avira").hide();
        $("#myself_aviraOTCDropdown").show();
        $("#customer_aviraOTCText").hide();
        $("#avira_next").show();
        $("#createCustomerNext").hide();
        
    } else if (this.value == 0) {
        $("#aviraTotal").html("0");
        $("#aviraUsed").html("0");
        $("#aviraPending").html("0");
        $("#aviraCntDt").html("YYYY:MM:DD HH:MM:SS");

        $(".myself_avira").hide();
        $(".customer_avira").show();
        $("#myself_aviraOTCDropdown").hide();
        $("#customer_aviraOTCText").show();
        $("#avira_next").hide();
        $("#createCustomerNext").hide();
    }
    $("#myself_aviraotc").val("").change();
    $(".selectpicker").selectpicker('refresh');
});


function verifyOTC() {
    $(".error").html(" *");

    var status_val = $.trim($('input[name=status]:checked').val()); //Customer creation is for myself or customer individual
    var error = 0;

    if (status_val == 0) {                                          //Customer condition
        var sitename = $.trim($('#addNewSite').val());
        var otcCode = $.trim($("#customer_aviraotc").val());
        var cust_email = $.trim($("#cust_Email").val());

        if (sitename == '') {
            $("#addNewSite_error").html('<span>Please Enter Customer Name</span>');
            error++;
        }
        if (otcCode == '') {
            $("#customer_aviraotc_error").html('<span>Please Enter OTC Code</span>');
            error++;
        }
        if (cust_email == '') {
            $("#cust_email_error").html('<span>Please Enter Email</span>');
            error++;
        }
    } else {                                                          //Myself condition
        error === 1;
    }

    if (error === 0) {
        $("#otcType").val("new");
        $("#add_errorMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        $.ajax({
            url: "../lib/l-custAjax.php?function=CUSTAJX_VerifyOTC&otcCode=" + otcCode + "&email=" + cust_email + "&compName=" + sitename + "&status=" + status_val + "&sitename=" + sitename,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status == "SUCCESS") {
                    $("#add_errorMsg").html('');
                    $("#aviraotc").attr('readonly', true);
                    
                    if ($('#confiure_gateway').is(':checked')) {
                        $("#aviraConfigureNextForGateway").show();
                        $("#aviraConfigureNext").hide();
                        $("#avira_next").hide();
                        $("#createCustomerNext").show();
                    } else {
                        $("#aviraConfigureNextForGateway").hide();
                        $("#aviraConfigureNext").show();
                        $("#createCustomerNext").hide();
                    $("#avira_next").show();
                    }
                    triggerConfiureGatewayCheckEvent();
                    $("#avira_verify").hide();
                    $("#avira_total").html(response.licsCnt);
                    $("#avira_used").html(response.used);
                    $("#avira_pending").html(response.pendingCount);
                    $("#avira_pcno").val(response.licsCnt);
                    $("#avira_pcno").attr("max", response.pendingCount);
                    $("#avira_pending_hidden").val(response.licenseCount);
                    if (status_val === 0) {
                        $("#cust_email").prop('disabled', true);
                        $("#cust_firstName").prop('disabled', true);
                        $("#cust_lastName").prop('disabled', true);
                        $("#aviraotc").prop('disabled', true);
                        $("#addNewSite").prop('disabled', true);
                    }

                    $('#Createsite .customscroll').mCustomScrollbar('scrollTo', 'bottom');
                } else if (response.status == "DUPLICATE") {
                    $("#avira_next").hide();
                    $(".verifybutton").show();
                    $("#add_errorMsg").html("<span>This OTC is already in use.</span>");
                } else if (response.status == "ERROR") {
                    $("#avira_next").hide();
                    $("#add_errorMsg").html(response.message);
                } else {
                    $("#avira_next").hide();
                    $("#add_errorMsg").html(response.status);
                }

            },
            error: function(err) {

            }
        });
    } else {
        $("#otcType").val("");
    }
}

function subscription_verifyOTC() {
    $(".error").html(" *");

    var status_val = $.trim($('#avira_subscription_type').val()); //Customer creation is for myself or customer individual
    var error = 0;

    if (status_val === 0 || status_val === '0') {                                          //Customer condition
        var sitename = $.trim($('#addSubscriptionName').val());
        var otcCode = $.trim($("#avira_subscription_customer_otc").val());
        var cust_email = $.trim($("#avira_subscription_cust_email").val());
    } else {                                                          //Myself condition
        error === 1;
    }

    if (error === 0) {
        $("#otcType").val("new");
        $("#add_errorMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        $.ajax({
            url: "../lib/l-custAjax.php?function=CUSTAJX_VerifyOTC&otcCode=" + otcCode + "&email=" + cust_email + "&compName=" + sitename + "&status=" + status_val + "&sitename=" + sitename,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $("#add_errorMsg").html('');
                if (response.status == "SUCCESS") {
                    $("#avira_subscription_next").show();
                    $("#avira_subscription_verify").hide();
                    $("#avira_subscription_total").html(response.licsCnt);
                    $("#avira_subscription_used").html(response.used);
                    $("#avira_subscription_pending").html(response.pendingCount);
                    $("#avira_subscription_date").html(response.contractEnds);
                    $("#avira_subscription_pcno").val(response.licsCnt);
                    $("#avira_subscription_pcno").attr("max", response.pendingCount);

                    $("#avira_subscription_pendingCnt").val(response.licenseCount);
                    $('#aviraSubscription_Popup .customscroll').mCustomScrollbar('scrollTo', 'bottom');
                } else if (response.status == "DUPLICATE") {
                    $("#avira_subscription_next").hide();
                    $(".verifybutton").show();
                    $("#avira_subscription_errorMsg").html("<span>This OTC is already in use.</span>");
                } else if (response.status == "ERROR") {
                    $("#avira_subscription_next").hide();
                    $("#avira_subscription_errorMsg").html(response.message);
                } else {
                    $("#avira_subscription_next").hide();
                    $("#avira_subscription_errorMsg").html(response.status);
                }

            },
            error: function(err) {

            }
        });
    } else {
        $("#otcType").val("");
    }
}

/**
 * Function populate details in add subscription pop.
 */
function setAviraSubscDetails() {
    var rowId = $('#customerDetailsGrid tbody tr.selected').attr('id');
    var cust_name = $('#customerDetailsGrid tbody tr.selected td:first').text();
    $("#addSubscriptionName").val(cust_name);
    if (rowId === undefined || rowId === 'undefined') {

    } else {
        var custRowId = rowId.split('##');
        var cust_id = custRowId[0];
        var proc_id = custRowId[1];
        var custNum = custRowId[2];
        var ordNum = custRowId[3];
        var restrict = custRowId[5];
        $("#avira_subscription_custno").val(custNum);
        $("#avira_subscription_orderno").val(ordNum);
        $("#avira_subscription_cust_id").val(cust_id);
        $("#avira_subscription_proc_id").val(proc_id);

        if (restrict === '1' || restrict === 1) {
            $("#avira_subscription_type").val(0);
        } else if (restrict === '0' || restrict === 0) {
            $("#avira_subscription_type").val(1);
        }


        if (restrict == 1) {
            $("#avira_subscription_next").hide();
            $("#avira_subscription_verify").show();
            $("#avira_subscription_myself").hide();
            $("#avira_subscription_customer").show();
            $.ajax({
                url: '../lib/l-custAjax.php?function=getCustDetails&cid=' + cust_id + '&pid=' + proc_id + '&custNum=' + custNum + '&ordNum=' + ordNum,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $("#avira_subscription_firstname").val(response.firstName);
                    $("#avira_subscription_lastname").val(response.lastName);
                    $("#avira_subscription_cust_email").val(response.emailId);
                    $('#avira_subscription_otctype').val("new");
                },
                error: function(response) {

                }
            });
            $("#avira_subscription_myself_noopc").hide();
            $(".customer_avira").show();
        } else {
            $("#avira_subscription_next").show();
            $("#avira_subscription_verify").hide();
            $.ajax({
                url: '../lib/l-custAjax.php?function=CUSTAJX_getOTClist',
                type: 'POST',
                dataType: 'text',
                success: function(response) {
                    $("#avira_subscription_myself_otc").html(response);
                    $(".selectpicker").selectpicker("refresh");
                    $('#avira_subscription_otctype').val("old");
                },
                error: function(response) {
                    console.log(response);
                    console.log("something went wrong in ajax call in setAviraSubscDetails function");
                }
            });
            $("#avira_subscription_myself").show();
            $("#avira_subscription_customer").hide();
            $("#avira_subscription_myself_noopc").show();
            $(".customer_avira").hide();
        }
    }
}

/**
 * This function will trigger when OTC drop down will change in Add Subscription pop up.
 */
function getSubscAviraLicDetails() {
    var selectedOTS = $("#avira_subscription_myself_otc").val();
    if (selectedOTS !== "") {
        $("#avira_subscription_otctype").val('old');
        $.ajax({
            url: '../lib/l-custAjax.php?function=CUSTAJX_getAviraOTCDtl&otcCode=' + selectedOTS,
            type: 'POST',
            dataType: 'text',
            success: function(response) {
                var checkedArray = response.split('---');
                $("#avira_subscription_total").html(checkedArray[0]);
                $("#avira_subscription_used").html(checkedArray[3]);
                $("#avira_subscription_date").html(checkedArray[2]);
                $("#avira_subscription_pending").html(checkedArray[1]);
                $("#avira_subscription_pcno").attr("max", checkedArray[1]);
                $("#avira_subscription_pendingCnt").val(checkedArray[1]);
            },
            error: function(response) {
                console.log(response);
                console.log("something went wrong in ajax call in getSubscAviraLicDetails function");
            }
        });
    } else {
        $("#avira_subscription_otctype").val('');
        $("#avira_subscription_total").html("");
        $("#avira_subscription_used").html("");
        $("#avira_subscription_pending").html("");
        $("#avira_subscription_date").html("");
    }

}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * It will create one more subcription for selected customer with same customer existing number.
 *----------------------------------------------------------------------------------------------------------------------
 */
function addAviraSubscription() {
    $(".error").html(" *");
    var sitename = $('#addSubscriptionName').val();

    var pendingCnt = $('#avira_subscription_pending').text();
    var errorVal = 0;
    var status = 1;
    var compName = '';
    var firstName = '';
    var lastName = '';
    var status_val = $('#avira_subscription_type').val(); //Customer creation is for myself or customer individual
    var otcType = $('#avira_subscription_otctype').val();
    var custno = $('#avira_subscription_custno').val();
    var orderno = $('#avira_subscription_orderno').val();
    var cust_id = $("#avira_subscription_cust_id").val();
    var proc_id = $("#avira_subscription_proc_id").val();
    if (status_val == 1) {
        var email = $("#avira_subscription_email").val();
        compName = $('#avira_subscription_compName').val();
        var aviraotc = $('#avira_subscription_myself_otc').val();
        var pcCnt = $('#avira_subscription_pcno').val();
        var successMessage = '';
        if (aviraotc === "") {
            $("#avira_subscription_pcno_error").html('<span>Please select OTC code</span>');
            errorVal++;
        }

        if (pcCnt <= 0) {
            $("#avira_subscription_pcno_error").html('<span>Please enter valid count</span>');
            errorVal++;
        }

        if (pcCnt > parseInt(pendingCnt)) {
            $("#avira_subscription_pcno_error").html('<span>Entered count is greater than available licenses</span>');
            errorVal++;
        }
    } else {
        var email = $("#avira_subscription_cust_email").val();
        compName = $('#addSubscriptionName').val();
        firstName = $('#avira_subscription_firstname').val();
        lastName = $('#avira_subscription_lastname').val();
        var aviraotc = $('#avira_subscription_customer_otc').val();
    }

    if (errorVal == 0 || errorVal === 0) {
        $("#avira_subscription_error").html('*');
        $("#req_pcCnt").html('*');
        var trialSite = 0;
        $.ajax({
            url: "../customer/addCustomerModel.php?function=add_aviraSubscription&sitename=" + sitename + "&trialSite=" + trialSite + "&aviraOtc=" + otcType + "&new_otc=" + aviraotc + "&pcCnt=" + pcCnt + "&new_email=" + email + "&new_compName=" + compName + "&status=" + status_val + "&firstName=" + firstName + "&lastName=" + lastName + "&custno=" + custno + "&orderno=" + orderno + "&cust_id=" + cust_id + "&proc_id=" + proc_id,
            type: 'POST',
            dataType: 'json',
            success: function(response) {

                if (response.msg === 'Success') {
                    $('#aviraSubscription_Popup').modal('hide');
                    $("#clickherelink").val(response.clientUrl);
                    $('#Createcustomer').modal('show');
                    if (status_val == 1) {
                        $("#clickHereDownLoad").show();
                        successMessage = sitename + '<span>has been successfully created</span>';
                    } else {
                        $("#clickHereDownLoad").hide();
                        successMessage = sitename + ' <span>has been successfully created.</span> ' + '<span>Email has been sent to given email address</span> ';
                    }
                    $("#custNo_val").html('<b>Customer ID:</b>' + response.link);
                    $("#add_successMsg").html(successMessage);

                } else {
                    $("#avira_subscription_errorMsg").html('<span>'+response.msg+'</span>');
                }
            },
            error: function(err) {

            }
        });
    }
}

function removeRevokeServiceTag(selectedRow) {

    var deviceRowId = selectedRow.split('---');
    var customerNum = deviceRowId[0];
    var orderNum = deviceRowId[1];
    var serviceTag = deviceRowId[2];
    var orderStatus = deviceRowId[3];
    if (orderStatus === 'Inactive') {

        $("#aviraRevoketCustno").val(customerNum);
        $("#aviraRevoketOrdno").val(orderNum);
        $("#aviraRevokeServicetag").val(serviceTag);
        $("#removeServiceTagOption").show();

    } else if (orderStatus === 'Active') {
        $("#removeServiceTagOption").hide();
    }
}

function revokeAviraotcpopUp() {

    var customerNum = $("#aviraRevoketCustno").val();
    var orderNum = $("#aviraRevoketOrdno").val();
    var serviceTag = $("#aviraRevokeServicetag").val();
    $("#updateRevokeotc_errorMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
    $.ajax({
        url: "../lib/l-custAjax.php?function=revoke_aviraSubscription&customerNum=" + customerNum + "&orderNum=" + orderNum + "&servicetag=" + serviceTag,
        type: 'POST',
        dataType: 'json',
        success: function(response) {

            if (response.status === 'Success') {

                $("#updateRevokeotc_errorMsg").css("color", "green").html(response.msg);
                setInterval(function() {
                    $('#removeServiceTagOption').modal('hide');
                    location.reload();
                }, 2000);

            } else {
                $("#updateRevokeotc_errorMsg").css("color", "red").html(response.msg);
            }
        },
        error: function(err) {

        }
    });
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * Export the customer list with devices if present. Export sheet columns will vary based on Avira and normal customer
 *----------------------------------------------------------------------------------------------------------------------
 */
$("#exportCustomerList").click(function() {
    if (bussLevel === 'Commercial') {
        window.location.href = "../lib/l-custAjax.php?function=CUSAJX_ExportCustomerData";
    } else {
        window.location.href = "../lib/l-custAjax.php?function=CUSAJX_ExportPTSCustomerData";
    }
});

function myacount_validate_Email(email) {
    var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
    if (filter.test(email)) {
        return true;
    }
    else {
        return false;
    }
}

$('.confiure_avira').on('change', function() {
    if ($('.confiure_avira:checked').length == $('.confiure_avira').length) {
        $('#confiure_avira_all').prop("checked", true);
    } else {
        $('#confiure_avira_all').prop("checked", false);
    }
});

$('.edit_confiure_avira').on('change', function() {
    if ($('.edit_confiure_avira:checked').length == $('.edit_confiure_avira').length) {
        $('#edit_confiure_avira_all').prop("checked", true);
    } else {
        $('#edit_confiure_avira_all').prop("checked", false);
    }
});

$('#confiure_avira_all').click(function() {
    if ($(this).is(':checked')) {
        $('.confiure_avira').prop("checked", true);
    } else {
        $('.confiure_avira').prop("checked", false);
    }
});

$('#edit_confiure_avira_all').click(function() {
    if ($(this).is(':checked')) {
        $('.edit_confiure_avira').prop("checked", true);
    } else {
        $('.edit_confiure_avira').prop("checked", false);
    }
});

function configureAviraInstall() {
    var downloadId = $("#downloadId").val();
    var unistallModule = $("input[name='avira_unistall']:checked").val()
    var allModules = [];
    $('.confiure_avira:checked').each(function() {
        allModules.push($(this).val());
    });

    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_changeAviraConfiguration&downloadId=" + downloadId + "&unistallModule=" + unistallModule + "&allModules=" + allModules,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            if ($.trim(response) === 'success') {
                
                var clickHere = $("#clickherelink").val();
                if (clickHere !== "") {
                    $('#Createsite').modal('hide');
                    $('#Createcustomer').modal('show');
                }else{
                    $("#aviraConfigure_errorMsg").css("color", "green").html("<span>Customer updated successfully</span>");
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                }
            } else {
                $('#Createcustomer').modal('hide');
            }
        },
        error: function(err) {

        }
    });
}

function reConfigureAviraInstall() {
    var downloadId = $("#edit_downloadId").val();
    var unistallModule = $("input[name='edit_avira_unistall']:checked").val()
    var allModules = [];
    $('.edit_confiure_avira:checked').each(function() {
        allModules.push($(this).val());
    });
    $("#edit_aviraConfigure_errorMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_changeAviraConfiguration&downloadId=" + downloadId + "&unistallModule=" + unistallModule + "&allModules=" + allModules,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            if ($.trim(response) === 'success') {
                $("#edit_aviraConfigure_errorMsg").css("color", "green").html("<span>Customer updated successfully</span>");
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
            } else {
                $('#Createcustomer').modal('hide');
            }
        },
        error: function(err) {

        }
    });
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * What need to be done once pop up get closed, all are here
 *----------------------------------------------------------------------------------------------------------------------
 */
$('#regenerate_popup').on('hidden.bs.modal', function() {
    $("#regenerate_popup .add-user-add-btn").show();
    $("#regenrate_status_msg").html("<span>Do you really want to regenerate order for selected device</span>");
})

$('#revoke_popup').on('hidden.bs.modal', function() {
    $("#revoke_popup .add-user-add-btn").show();
    $("#revoke_status_msg").html("<span>Do you really want to revoke order for selected device</span>");
})

$('#renew_subscription_popup').on('hidden.bs.modal', function() {
    $('#renew_subscription_popup').find('input').val('');
    $("#renewSubReq_subs_recustnum").html("");
    $("#renew_subscription_popup .form-control").val('');
    $("#renew_subscription_popup .error").html("*");
})

$('#add_subscription_popup').on('hidden.bs.modal', function() {
    $("#add_subscription_popup .form-control").val('');
    $("#add_subscription_popup .error").html("*");
})

$('#add_new_subscription_popup').on('hidden.bs.modal', function() {
    $("#add_new_subscription_popup .form-control").val('');
    $("#add_new_subscription_popup .error").html("*");
})

$('#renew_details').on('hidden.bs.modal', function() {
    $("#renew_topcheckbox").prop('checked', false); // Unchecks it
})

$('#Createsite').on('hidden.bs.modal', function() {
    $("#addNewSite_error").html("*");
    $("#customer_aviraotc").val('');
    $("#cust_Email").val('');
    $("#addNewSite").val('');
    $("#customer_aviraotc").prop('disabled', false);
    $("#cust_Email").prop('disabled', false);
    $("#addNewSite").prop('disabled', false);
});

$('#regenerate_url').on('hidden.bs.modal', function() {
    location.reload();
})

$('#subscription_status').on('hidden.bs.modal', function() {
    location.reload();
});

$('#aviraDetails_Popup').on('hidden.bs.modal', function() {
    $("#aviraInstalled").hide();
    $("#aviraNotInstalled").hide();
});

$('#addotc_Popup').on('hidden.bs.modal', function() {
    $("#aviraotcVal").val("");
    $("#addotc_errorMsg").html("");
    $('#addotc_Popup .form-control').val("");
    $("#aviraEmail").val($("#email").val());
    $("#aviraCompName").val(trimmedCompanyName);
//    $('#addotc_Popup .form-group').addClass('is-empty');
});

$('#Createsite').on('hidden.bs.modal', function() {
    $("#aviraTotal").html("");
    $("#aviraUsed").html("");
    $("#aviraPending").html("");
    $("#aviraCntDt").html("");
    $("#aviraotc").html("");
    $("#pcCnt").val("");
});

$("#clickhereokbutton").click(function() {
    location.reload();
});

$('#edit_site').on('hidden.bs.modal', function() {
    $("#edit_errorMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
    $("#edit_pcCnt").attr({"max": 1, "min": 1});
    $("#edit_aviraotc").val("");
    $("#edit_aviraTotal").html("");
    $("#edit_aviraUsed").html("");
    $("#edit_aviraPending").html("");
    $("#edit_aviraCntDt").html("");
    $("#edit_Site").val("");
    $("#edit_pendingCnt").val("");
    $('#reconfiure_avira').prop('checked', false);

});

$('#avira_configure').on('hidden.bs.modal', function() {
    $('.confiure_avira:checked').each(function() {
        $(this).prop("checked", false);
    });
    $('input:radio[name=avira_unistall][value="0"]').attr('checked', true);
    $("#aviraConfigure_errorMsg").html("");
});

$('#Createcustomer').on('hidden.bs.modal', function() {
    $("#clickherelink").val("");
});


function customer_validate_Alphanumeric(value) {
    var regExp = /^[a-zA-Z0-9-._\-\s]+$/;
    if (value.match(regExp)) {

        return true;
    }
    else
    {
        return false;
    }
}


//This function will get called when otc will get verified successfully.
//This function will change button hide show on the basis of gateway configuration checkbox.
function triggerConfiureGatewayCheckEvent() {
    $('#confiure_gateway').change(function() {
        if ($(this).is(":checked")) {
            if ($("#avira_verify").is(":visible") == true)
            {
                $("#avira_next").hide();
                $("#createCustomerNext").hide();
            } else {
                $("#avira_next").hide();
                $("#createCustomerNext").show();
            }
            
        } else {
            if ($("#avira_verify").is(":visible") == true)
            {
                $("#avira_next").hide();
                $("#createCustomerNext").hide();
            } else {
                $("#avira_next").show();
                $("#createCustomerNext").hide();
            }

        }

    });
}

$("#createCustomerNext").click(function() {
   
    $(".error").html(" *");
    var sitename = $.trim($('#addNewSite').val());
    var aviraotc = '';
    var aviraemail = '';
    var compName = '';
    var lastname = '';
    var status = 1;
    var avira_pcno = 5;
    var error = 0;
    var pending = 0;
    if (sitename == '') {
        $("#addNewSite_error").html('<span>Please Enter Customer Name</span>');
        error++;
    }else if (sitename.indexOf("__") != -1) {
        $("#addNewSite_error").html('<span>More than one underscore not allowed</span>');
        error++;
    }else if(!avira_validate_Alphanumeric(sitename)){
        $("#addNewSite_error").html('<span>Special character not allowed in Customer Name other than(-_.)</span>');
        error++;
    }

    status = $('input[name=status]:checked').val();
    if (status === 0 || status === '0') {
        pending = $("#avira_pending_hidden").val();
        aviraotc = $.trim($('#customer_aviraotc').val());
        aviraemail = $.trim($('#cust_Email').val());
        compName = $.trim($('#cust_firstName').val());
        lastname = $.trim($("#cust_lastName").val());
        avira_pcno = $('#avira_pcno').val();
        if (aviraotc === '') {
            
            $("#aviraotc_error").html('<span>Please Enter OTC Code</span>');
            error++;
        }
        if (aviraemail === '') {
           
            $("#cust_email_error").html('<span>Please Enter Email</span>');
            error++;
        }
        if (aviraemail !== '') {
            if (!avira_validate_Email(aviraemail)) {
               
                $("#cust_email_error").html('<span>Please Enter Valid Email Id</span>');
                error++;
            }
        }
        if (compName === '') {
             
            $("#cust_firstName_error").html('<span>Please Enter First Name</span>');
            error++;
        }
        if (lastname === '') {
            
            $("#cust_lastName_error").html('<span>Please Enter Last Name</span>');
            error++;
        }

    } else if (status === 1 || status === '1') {
        pending = $("#avira_pending_hidden").val();
        aviraotc = $.trim($('#myself_aviraotc').val());
        aviraemail = $.trim($('#email').val());
        compName = $.trim($('#compName').val());
        lastname = '';
        avira_pcno = $('#avira_pcno').val();
        if (avira_pcno <= 0) {
             
            $("#avira_pcno_error").html('<span>Please Enter valid number of PC</span>');
            error++;
        } else if (avira_pcno > parseInt(pending)) {
           
            $("#avira_pcno_error").html('<span>Please Enter valid number of PC</span>');
            error++;
        }
    }
   
    if (error === 0) {
        if ($('#confiure_gateway').is(':checked')) {
            $("#aviraConfigureNextForGateway").show();
            $("#aviraConfigureNext").hide();
        }else{
            $("#aviraConfigureNextForGateway").hide();
            $("#aviraConfigureNext").show();
        }
        $('#createCustomerDiv').hide();
        $('#avira_configureDiv').show();
        $('#avira_gatewayDiv').hide();
    }
});


function avira_validate_Email(email) {
    var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
    if (filter.test(email)) {
        return true;
    }
    else {
        return false;
    }
}
function avira_validate_Alphanumeric(value)
{
    var regExp = /^[a-zA-Z0-9-._\-\s]+$/;
    if (value.match(regExp)) {

        return true;
    }
    else
    {
        return false;
    }
}

$("#gatewayInfoPrevious").click(function(){
   $('#createCustomerDiv').hide();
   $('#avira_configureDiv').show();
   $('#avira_gatewayDiv').hide();
});

$("#aviraConfigureNextForGateway").click(function() {
    if ($('#confiure_gateway').is(':checked')) {
        $("#aviraConfigureNext").hide();
        $("#aviraConfigureNextForGateway").show();
    }else{
        $("#aviraConfigureNext").show();
        $("#aviraConfigureNextForGateway").hide();
    }
    $('#createCustomerDiv').hide();
    $('#avira_configureDiv').hide();
    $('#avira_gatewayDiv').show();
})

$("#aviraConfigurePrevious").click(function(){
    $('#createCustomerDiv').show();
    $('#avira_configureDiv').hide();
    $('#avira_gatewayDiv').hide();
});

$("#gatewayInfoPrevious").click(function(){
    $('#createCustomerDiv').hide();
    $('#avira_configureDiv').show();
    $('#avira_gatewayDiv').hide();
});

$("#addCutomerWithGatewayInfo").click(function() {
    var sitename  = $.trim($('#addNewSite').val());
    var trialSite = $('#trialSite').val();
    var otcType   = $('#otcType').val();
    var aviraotc  = '';
    
    var aviraemail= '';
    
    var status      = '';
    var compName    = '';
    var avira_pcno  = 5;
    status = $('input[name=status]:checked').val();
    var validateGatewayInfo1 = validateGatewayInfo();

    if (status === 0 || status === '0') {
        aviraemail = $.trim($('#cust_Email').val());
        compName = $.trim($('#cust_firstName').val());
        avira_pcno = $('#avira_pcno').val();
        aviraotc  = $.trim($('#customer_aviraotc').val());
    } else if (status === 1 || status === '1') {
        aviraemail = $.trim($('#email').val());
        compName = $.trim($('#compName').val());
        avira_pcno = $('#avira_pcno').val();
        aviraotc  = $.trim($('#myself_aviraotc').val());
    }
    
    if (validateGatewayInfo1 == 0) {
        $("#gateModalLoader").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        createCustomerOfGatewayInfo(sitename, trialSite, otcType, aviraotc, aviraemail, compName, status, avira_pcno);
    }
});


function createCustomerOfGatewayInfo(sitename, trialSite, otcType, aviraotc, aviraemail, compName, status, avira_pcno) {
    var successMessage = '';
    var GatewayMachine  = $("#gatewayHostName").val();
    var GatewayHost     = $("#gatewayHostName").val();
    var GatewayIP       = $("#gatewayIPAddress").val();
    var GatewayPort     = $("#gatewayPort").val();
    var GatewayDomain   = $("#gatewayDomain").val();
    var GatewayUN       = $("#gatewayUsername").val();
    var GatewayPassword = $("#gatewayPassword").val();
    var GatewayMachine  = $("#gatewayHostName").val();
    
    var params = "../customer/addCustomerModel.php?function=addSitename&sitename=" + sitename + "&trialSite=" + trialSite 
            + "&aviraOtc=" + otcType + "&new_otc=" + aviraotc + "&new_email=" + aviraemail + "&new_compName=" + compName 
            + "&status=" + status + "&pcCnt=" + avira_pcno + "&defaultGateway=1&GatewayMachine=" + GatewayMachine 
            + "&GatewayHost=" + GatewayHost + "&GatewayIP=" + GatewayIP + "&GatewayPort=" + GatewayPort 
            + "&GatewayDomain=" + GatewayDomain + "&GatewayUN=" + GatewayUN 
            + "&GatewayPassword=" + GatewayPassword + "&GatewayMachine=" + GatewayMachine;
    $.ajax({
        url: params,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#gateModalLoader").html('');
            if (response.msg === 'Success') {
                $("#clickherelink").val(response.clientUrl);
                $("#downloadId").val(response.link);
                $("#custNo_val").html('<b>Customer ID:</b>' + response.link);
                if (status == 1) {
                    $("#clickHereDownLoad").show();
                    successMessage = sitename + '<span>&nbsp;has been successfully created</span>';
                } else {
                    $("#clickHereDownLoad").hide();
                    successMessage = sitename + ' <span>&nbsp;has been successfully created.</span> ' + '<span>Email has been sent to given email address</span> ';
                }
                $("#add_successMsg").html(successMessage);
                
                var downloadId = response.link;
                var unistallModule = $("input[name='avira_unistall']:checked").val()
                var allModules = [];
                $('.confiure_avira:checked').each(function() {
                    allModules.push($(this).val());
                });
                
                var configureAvira = configureAviraForDownloadId(downloadId, allModules, unistallModule);
//                var defaultGateway = configureDefaultGateway(downloadId);
                $('#Createsite').modal('hide');
                 
            } else {
                $("#gatewayReq_gatewayPassword").html(response.msg);
            }
        },
        error: function(err) {

        }
    });
}

function validateGatewayInfo() {
    var isReqFieldsFilled = 0;
    $('.error').html("*");
    $('.gatewayReq').each(function() {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();

        if ($.trim(field_value) === "") {
            $("#gatewayReq_" + field_id).html(" <span>required</span>");
            isReqFieldsFilled++;
        }
    });
    return isReqFieldsFilled;
}

/**
 * This function will get called on successfully creation of customer.
 * This functin will get called when submit button clicked on Gateway module.
 * downloadId is uniq id which will get assigned to every customer after succesfull creation.
 * aviraModules are all checked modules which has been selected on Avira configure pop up.
 * aviraUnistall is value whether previous installed modules need to unistall or not.
 */
function configureAviraForDownloadId(downloadId, aviraModules, aviraUnistall) {
    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_changeAviraConfiguration&downloadId=" + downloadId + "&unistallModule=" + aviraUnistall + "&allModules=" + aviraModules,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            if ($.trim(response) === 'success') {
                $('#avira_configureDiv').show();
                $('#avira_gatewayDiv').hide();
                $('#Createcustomer').modal('show');
            } else {
                $("#gatewayPassword").html('Error occurred, please try after some time');
            }
        },
        error: function(err) {
            console.log("Some error occurred in configureAviraForDownloadId function -->".err);
        }
    });
    return true;
}

function configureDefaultGateway(downloadId) {
    var GatewayMachine  = $("#gatewayHostName").val();
    var GatewayHost     = $("#gatewayHostName").val();
    var GatewayIP       = $("#gatewayIPAddress").val();
    var GatewayPort     = $("#gatewayPort").val();
    var GatewayDomain   = $("#gatewayDomain").val();
    var GatewayUN       = $("#gatewayUsername").val();
    var GatewayPassword = $("#gatewayPassword").val();
    var params = "&GatewayMachine=" + GatewayMachine + "&GatewayHost=" + GatewayHost + "&GatewayIP=" + GatewayIP + "&GatewayPort=" + GatewayPort + "&GatewayDomain=" + GatewayDomain + "&GatewayUN=" + GatewayUN + "&GatewayPassword=" + GatewayPassword + "&downloadId=" + downloadId;
    
    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_SetDefaultGateway" + params,
        type: 'POST',
        dataType: 'text',
        date:params,
        success: function(response) {
            if(response.status == "SUCCESS"){
                $("#gateModalLoader").html('');
                $("#gatewayReq_gatewayPassword").html('*');
            }else{
                $("#gateModalLoader").html('');
                $("#gatewayReq_gatewayPassword").html('some error occurred, please try after some time.');
            }
            
        },
        error: function(err) {
            console.log("Some error occurred in configureAviraForDownloadId function -->".err);
        }
    });
    return true;
}

$("#avira_gatewayDiv .icon-ic_info_outline_24px").mouseover(function(){
    $(this).parent().find('.tooltip-inner').show();
});

$("#avira_gatewayDiv .icon-ic_info_outline_24px").mouseleave(function(){
    $(this).parent().find('.tooltip-inner').hide();
});

$("#createCustomerDiv .icon-ic_info_outline_24px").mouseover(function(){
    $(this).parent().find('.tooltip-inner').show();
});

$("#createCustomerDiv .icon-ic_info_outline_24px").mouseleave(function(){
    $(this).parent().find('.tooltip-inner').hide();
});

/**
 * This function is for refreshing OTP details.
 * By using this function we are just checking OTP details on Nanoheal server & Avira server details are same or not.
 * If both server's details is different then we will sync Nanoheal's details as per Avira server details.
 */
function refreshOTC(){
    var selectedOTC = $('#orderAVIRA_DetailsGrid tbody tr.selected').attr('id');
    $(".regenrate_status_msg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
    $("#invalid_add_customer_popup").modal("show");
    $.ajax({
        url: "../lib/l-custAjax.php?function=CUSTAJX_VerifyOTCDetails&selectedOTC=" + selectedOTC,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $(".regenrate_status_msg").html(response.message);
            $("#invalid_add_customer_popup").modal("show");
            
        },
        error: function(response) {
            console.log("Some error occurred in refreshOTP function --> ".response);
        }
    });
    
    
    
    
}

//$("#gatewayInfo").mouseover(function(){
//   
//});
//
//$("#gatewayInfo").mouseleave(function(){
//   
//});

$("#refreshOTCOkButton").click(function(){
   location.reload(); 
});