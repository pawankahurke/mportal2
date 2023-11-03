$(document).ready(function () {
    get_CommercialCustomers();
    getSkuList();
});

function makeAjaxCall(url, data, responseType) {
    var ajaxResponse;
    $.ajax({
        url: url,
        data: data+"&csrfMagicToken=" + csrfMagicToken,
        async: false,
        dataType: responseType,
        success: function(response) {
            ajaxResponse = response;
        },
        error: function() {
        }
    });
    return ajaxResponse;
}

function getAvailableLicensesCount(){
    $.ajax({
        url: '../lib/l-msp.php',
        data: "function=MSP_GetAvailableLicenseCount"+"&csrfMagicToken=" + csrfMagicToken,
        async: false,
        dataType: 'json',
        success: function(response) {
            $("#availableLicenses").val(parseInt($.trim(response.availableCnt)));
        },
        error: function() {
        }
    });
    return true;
}

/**
 * Fetch all customers list in json format which is required for Datatable.
 */
function get_CommercialCustomers(){
    $('#msp_Customer_Grid').dataTable().fnDestroy();
    customerGrid = $('#msp_Customer_Grid').DataTable({
        scrollY: jQuery('#msp_Customer_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        searching: true,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            },{
                className: "ignore", targets: [0]
            }],
        ajax: {
            url: "../lib/l-entp.php?function=ENTP_GetCustomerGrid&channelId=" + loggedEid +"&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        columns: [
            {"data": "customer"},
//            {"data": "status"}
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "_START_-_END_ of _TOTAL_ entries",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function(settings, json) {
           customerGrid.$('tr:first').click();
        },
        drawCallback: function(settings) {
                $(".dataTables_scrollBody").mCustomScrollbar({
                    theme: "minimal-dark"
                });
        }
    });
    
    $('#msp_Customer_Grid tbody').on('click', 'tr', function() {
        customerGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = customerGrid.row(this).id();
        enableOptions(id);
        
    });
    
    $("#customer_searchbox").keyup(function () {//group search code
        customerGrid.search(this.value).draw();
    });
}

function getSkuList() {

    $.ajax({
        type: 'POST',
        url: '../lib/l-entp.php',
        data: "function=ENTP_GetSkuDtlsByCid" +"&csrfMagicToken=" + csrfMagicToken,
        async: false,
        dataType: 'json',
        success: function(data) {
            var skuStr = "";
            skuStr = '<option value="0" >Please select SKU</option>';
            for (var i = 0; i < data.length; i++) {
                skuStr += '<option value="' + data[i].id + '" >' + data[i].skuName + '</option>';
            }
            $("#resel_ProvSku").html(skuStr);
            $("#resel_editcompanySku").html(skuStr);
            $(".selectpicker").selectpicker("refresh");
        }
    });
}

function enableOptions(rowId){
    
    var sel_CustomerStatus = getCustomerId(rowId, 0);
    $("#enableCustomer_Li").hide();
    $("#disableCustomer_Li").hide();
    if(sel_CustomerStatus === 0 || sel_CustomerStatus==='0'){
        $("#enableCustomer_Li").show();
        $("#disableCustomer_Li").hide();
    } else if(sel_CustomerStatus === 1 || sel_CustomerStatus==='1'){
        $("#enableCustomer_Li").hide();
        $("#disableCustomer_Li").show();
    }
    var sel_CustomerEid    = getCustomerId(rowId, 0);
    
    
    $('#msp_Sites_Grid').dataTable().fnDestroy();
    sitesGrid = $('#msp_Sites_Grid').DataTable({
        scrollY: jQuery('#msp_Sites_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        searching: true,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            },{
                className: "ignore", targets: [0]
            }],
        ajax: {
            url: "../lib/l-entp.php?function=ENTP_GetCustomerOrderGrid&custid="+sel_CustomerEid+"&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        columns: [
            {"data": "orderNum"},
            {"data":"pcCnt"},
            {"data":"instal"},
            {"data":"orderDt"},
            {"data":"cntDt"},
            
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "_START_-_END_ of _TOTAL_ entries",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function(settings, json) {
           sitesGrid.$('tr:first').click();
        },
        drawCallback: function(settings) {
                $(".dataTables_scrollBody").mCustomScrollbar({
                    theme: "minimal-dark"
                });
        }
    });
    $('#msp_Sites_Grid tbody').off('click');
    $('#msp_Sites_Grid tbody').on('click', 'tr', function() {
        sitesGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = sitesGrid.row(this).id();
        //getSitesList(id);
    });            
    
}


function getSitesList(rowId){
    
    var sel_customerNum = getCustomerId(rowId, 0);
    var sel_orderNum    = getCustomerId(rowId, 1);
    var sel_compId      = getCustomerId(rowId, 2);
    var sel_procId      = getCustomerId(rowId, 3);
   
   
    $('#msp_Device_Grid').dataTable().fnDestroy();
    deviceGrid = $('#msp_Device_Grid').DataTable({
        scrollY: jQuery('#msp_Device_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        searching: true,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            },{
                className: "ignore", targets: [0,1,2]
            }],
        ajax: {
            url: "../lib/l-msp.php?function=MSP_GetSitesDeviceGrid&custId="+sel_compId+"&procId="+sel_procId+"&custNum="+sel_customerNum+"&ordNum="+sel_orderNum+"&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        columns: [
            
             {"data": "devicename"},
             {"data": "installDt"},
             {"data": "status"}
            
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "_START_-_END_ of _TOTAL_ entries",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function(settings, json) {
           
        },
        drawCallback: function(settings) {
                $(".dataTables_scrollBody").mCustomScrollbar({
                    theme: "minimal-dark"
                });
        }
    });
    
    $('#msp_Device_Grid tbody').on('click', 'tr', function() {
        deviceGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = deviceGrid.row(this).id();
        //enableOptions(id);
    });
    
//    $("#customer_searchbox").keyup(function () {//group search code
//        deviceGrid.search(this.value).draw();
//    });
}



/*
 *----------------------------------------------------------------------------------------------------------------------
 * It will create site for MSP customer.
 *----------------------------------------------------------------------------------------------------------------------
 */
function create_Customer() {
    var errorVal = 0;
    errorVal = validateAddCustomerForm();
    
    if (errorVal === 0) {
        var m_data = new FormData();
        
        //Customer's company's details.
        m_data.append('custCompName', $('#cust_companyName').val());
        m_data.append('custCompAddr', $('#cust_companyAddr').val());
        m_data.append('custCompCity', $('#cust_companyCity').val());
        m_data.append('custCompState', $('#cust_companyState').val());
        m_data.append('custCompZipcode', $('#cust_companyZip').val());
        m_data.append('custCompWebsite', $('#cust_compWeb').val());
        
        //Customer's details.
        m_data.append('custFirstName', $('#cust_firstName').val());
        m_data.append('custLastName', $('#cust_lastName').val());
        m_data.append('custEmail', $('#cust_email').val());
        
        //License details.
        m_data.append('orderNumber', $('#orderList').val());
        m_data.append('pcCnt', $('#pcCnt').val());
        m_data.append("csrfMagicToken",csrfMagicToken);
        $("#add_CustomerMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        $.ajax({
            url: '../lib/l-entp.php?function=ENTP_CreateCustomer',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if($.trim(response.status) == "success"){
                    $('#add_CustomerMsg').css("color","green").html($.trim(response.msg));
                    setTimeout(function() {
                        location.reload();
                    }, 3500);
                }else{
                    $('#add_CustomerMsg').css("color","red").html($.trim(response.msg));
                }
                get_CommercialCustomers();
            },
            error: function(response) {
                $("#addNewSite_error").html("Error Occurred");
                console.log('Error In create_Customer function : '+response);
            }
        });
    }
}

function validateAddCustomerForm(){
   $(".error").html(" *");
   var errorVal = 0;
   
   $('.addCustRequired').each(function() {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();
        
        if ($.trim(field_value) === "") {
            $("#required_" + field_id).css("color", "red").html(" required");
            errorVal++;
        } else if (field_id == "cust_companyName") {
            if (!validate_alphanumeric_underscore(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
            }

        } else if (field_id == "cust_firstName") {
            if (!validate_Alphanumeric(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
            }

        } else if (field_id == "cust_lastName") {
            if (!validate_Alphanumeric(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
            }

        } else if (field_id == "cust_email") {
            if (!validate_Email(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html(" Enter valid email");
            }
        } else if (field_id == "pcCnt") {
            if (field_value < 0) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html(" Enter valid no of pc");
            }
        }
    });
    return errorVal;
}

$('#copy_link1').click(function() {
    var urlField = document.querySelector('#new_cust_download_url');
    urlField.select();
    document.execCommand('copy');
});

function getCustomerDetails() {
    var sel_Customer = $('#msp_Customer_Grid tbody tr.selected').attr('id');
    var m_data = new FormData();
    var custId = $.trim(sel_Customer.split("---")[0]);
    m_data.append('eid', custId);
    m_data.append("csrfMagicToken",csrfMagicToken);
    $.ajax({
        url: '../lib/l-entp.php?function=ENTP_GetEntityDetails',
        data: m_data,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#customerEid").val(custId);
            $("#prov_CustName").val($.trim(response.companyName));
            $("#prov_CustEmail").val($.trim(response.emailId));
            $("#edit_companyName").val($.trim(response.companyName));
            $("#edit_pcCnt").val($.trim(response.noOfPc));
            $("#edit_firstName").val($.trim(response.firstName));
            $("#edit_lastName").val($.trim(response.lastName));

            $("#edit_email").val($.trim(response.emailId));
            if (!validate_Email($.trim(response.emailId))) {
                $("#edit_email").attr("readonly", false);
                $("#hidden_trialSiteEmail").val("1");
            } else {
                $("#edit_email").attr("readonly", true);
                $("#hidden_trialSiteEmail").val("0");
            }

            toggleReadonlyAttribute('edit_compWeb', $.trim(response.website));
            toggleReadonlyAttribute('edit_companyAddr', $.trim(response.address));
            toggleReadonlyAttribute('edit_companyCity', $.trim(response.city));
            toggleReadonlyAttribute('edit_companyState', $.trim(response.province));
            toggleReadonlyAttribute('edit_companyZip', $.trim(response.zipCode));

            $("#edit_CustomerMsg").html('');
        },
        error: function(response) {

        }
    });
}

/**
 * Fetch all customers list in json format which is required for Datatable.
 */
function get_CustomerDetails(){
    var sel_Customer = $('#msp_Customer_Grid tbody tr.selected').attr('id');
    var sel_CustomerId = getCustomerId(sel_Customer, 0);
    $('#customer_detailsGrid').dataTable().fnDestroy();
    customerGrid = $('#customer_detailsGrid').DataTable({
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
            },{
                className: "ignore", targets: [0, 1, 2, 3]
            },
            { "width": "2%", "targets": 1 }
        ],
        ajax: {
            url: "../lib/l-msp.php?function=MSP_GetCustomerDetailGrid&customerId="+sel_CustomerId+"&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        columns: [
            {"data": "order"},
            {"data": "email"},
            {"data": "endDate"},
            {"data": "link"},
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "_START_-_END_ of _TOTAL_ entries",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function(settings, json) {
           
        },
        drawCallback: function(settings) {
                $(".dataTables_scrollBody").mCustomScrollbar({
                    theme: "minimal-dark"
                });
        }
    });
    
    $('#msp_Customer_Grid tbody').on('click', 'tr', function() {
        customerGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = customerGrid.row(this).id();
        enableOptions(id);
    });
}

function getSkuDetails(){
    var selectedSku = $("#resel_ProvSku").val();
    var m_data = new FormData();
    
    m_data.append('skuId', selectedSku);
    m_data.append("csrfMagicToken",csrfMagicToken);
    $.ajax({
        url: '../lib/l-entp.php?function=getSkuDetails',
        data: m_data,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            $("#skuEndDate").val(response);
        },
        error: function(response) {

        }
    });
}

function getSkuList() {

    $.ajax({
        type: 'POST',
        url: '../lib/l-entp.php?function=ENTP_GetSkuDtlsByCid'+"&csrfMagicToken=" + csrfMagicToken,
        async: false,
        dataType: 'json',
        success: function(data) {
            var skuStr = "";
            skuStr = '<option value="0" >Please select SKU</option>';
            for (var i = 0; i < data.length; i++) {
                skuStr += '<option value="' + data[i].id + '" >' + data[i].skuName + '</option>';
            }
            $("#resel_ProvSku").html(skuStr);
//            $("#resel_editcompanySku").html(skuStr);
            $(".selectpicker").selectpicker("refresh");
        }
    });
}


function create_Provision() {
    var customerNum = $("#prov_customerNumber").val();
    var orderNum = $("#prov_orderNumber").val();
    var sku = $("#resel_ProvSku").val();
    var cmpId = $("#customerEid").val();
    var errorVal = 0;
    $(".error").html("*");
    $('.provCustRequired').each(function() {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();
        if ($.trim(field_value) === "" && field_id !== "") {
            $("#prov_" + field_id).html(" required");
            errorVal++;
        } else if (field_id === "prov_customerNumber") {
            if (!validate_OrderNumber(field_value)) {
                $("#prov_" + field_id).html(" Enter valid customer number with length 8 to 16");
                errorVal++;
            }

        } else if (field_id === "prov_orderNumber") {
            if (!validate_OrderNumber(field_value)) {
                $("#prov_" + field_id).html(" Enter valid order number with length 8 to 16");
                errorVal++;
            }

        } else if (field_id === "resel_ProvSku") {
            if (field_value === "0") {
                $("#prov_" + field_id).html(" Please select SKU");
                errorVal++;
            }

        }
    });
    if (errorVal === 0) {
        var m_data = new FormData();

        m_data.append('customerNum', customerNum);
        m_data.append('orderNum', orderNum);
        m_data.append('eid', $("#customerEid").val());
        m_data.append('skuVal', sku);
        m_data.append("csrfMagicToken",csrfMagicToken);
        $.ajax({
            url: '../lib/l-entp.php?function=ENTP_NewProvision',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status === "SUCCESS") {
                    $("#new_cust_download_url").val(response.link);
                    $("#add_successMsg").html(response.msg);
                    $("#msp_CreateProvision").modal("hide");
                    $("#msp_CreateCustomerLink").modal("show");
                } else {
                    $("#prov_CustomerMsg").val(response.msg);
                }
                
                enableOptions(cmpId);
            },
            error: function(response) {

            }
        });
    }

}

function customer_link(url) {
    var modal = $('#msp_CreateCustomerLink').modal('show');
    modal.css({zIndex: 10000})
    $('#add_successMsg').html("Please click on copy button to copy url");
    $('#new_cust_download_url').val($.trim(url));
    
}

function toggleReadonlyAttribute(fieldId, fieldValue) {
    $("#" + fieldId).val(fieldValue);
    if (fieldValue === "" || fieldValue === undefined || fieldValue === "0" || fieldValue === "-") {
        $("#" + fieldId).attr("readonly", false);
    } else {
        $("#" + fieldId).attr("readonly", true);
    }
    return true;
}


function getCustomerId(selectedId, index) {
    var custRowId = selectedId.split('---');
    var cust_id = custRowId[index];
    return cust_id;
}

//###################################### Boostrap Modal CLOSE/OPEN Events Start ################################################//
$('#msp_CustomerDetails').on('shown.bs.modal', function() {
    $('#customer_detailsGrid').DataTable().columns.adjust().draw();
});

$('#msp_renewDevices').on('shown.bs.modal', function() {
    getAvailableLicensesCount();
    $('#renewDevicesGrid').DataTable().columns.adjust().draw();
});

$('#msp_CreateCustomer').on('hidden.bs.modal', function() {
    $("#msp_CreateCustomer input[type=text]").not("[readonly]").val('');
    $("#msp_CreateCustomer .error").html("*");
    $("#add_CustomerMsg").html('');
});

$('#msp_EditCustomer').on('hidden.bs.modal', function() {
    $("#msp_EditCustomer input[type=text]").not("[readonly]").val('');
    $("#edit_CustomerMsg .error").html("*");
    $("#edit_CustomerMsg").html('<img src="../vendors/images/loader2.gif" class="loading orders" alt="loading..." />');
});

$('#msp_CustomerDetails').on('hidden.bs.modal', function() {
        location.reload();
});

$('#enable_site').on('hidden.bs.modal', function() {
     $("#enable_msg").html("");   
});

$('#disable_site').on('hidden.bs.modal', function() {
     $("#disable_msg").html("");   
});

$('#warning_modal').on('hidden.bs.modal', function() {
     $("#warning_msg").html("");   
});

$('#msp_CreateProvision').on('hidden.bs.modal', function() {
    $("#msp_CreateProvision input[type=text]").not("[readonly]").val('');
    $("#msp_CreateProvision .error").html("*");
    $("#prov_CustomerMsg").html('');
});

function exportAllOrder() {
    
     location.href='../lib/l-entp.php?function=ENTP_exportOrderData'+"&csrfMagicToken=" + csrfMagicToken;
}

//###################################### Boostrap Modal CLOSE/OPEN Events End ################################################//

