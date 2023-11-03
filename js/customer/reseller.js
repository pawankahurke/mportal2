/**
 * This file belongs to "Commercial/MSP" Resellers only.
 * This file is created for all provisioning functionality for "Commercial/MSP" flow.
 * In this file "msp" indicates "managed service provider" or "Commercial" bussiness flow.
 */

$(document).ready(function () {
    
    get_CommercialResellers();
});

function makeAjaxCall(url, data, responseType) {
    var ajaxResponse;
    $.ajax({
        url: url + "&csrfMagicToken=" + csrfMagicToken,
        data: data,
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
        data: "function=MSP_GetAvailableLicenseCount&csrfMagicToken=" + csrfMagicToken,
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
 * Fetch all resellers list in json format which is required for Datatable.
 */
function get_CommercialResellers(){
    
    $('#msp_Reseller_Grid').dataTable().fnDestroy();
    resellerGrid = $('#msp_Reseller_Grid').DataTable({
//        scrollY: jQuery('#msp_Reseller_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        searching: true,        
        processing: true,
        serverSide: false,
        bAutoWidth: true,       
        stateSave: true,
        scrollY: 'calc(100vh - 240px)',
        "pagingType": "full_numbers",
        ajax: {
            url: "../lib/l-msp.php?function=MSP_GetResellerGrid",
            type: "POST"
        },
        "language": {
            search: "_INPUT_",
            searchPlaceholder: "Search records",
        },
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        columns: [
            {"data": "reseller"},
            {"data": "firstName"},
            {"data": "lastName"},
            {"data": "email"},
            {"data": "status"}
        ],
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false
            },{
                className: "ignore", targets: [0, 1, 2, 3]
            }],
        ordering: true,
        select: false,
        bInfo: false,
        responsive: true,
        
        "dom": '<"top"f>rt<"bottom"lp><"clear">',
        initComplete: function(settings, json) {
           
        },
//        drawCallback: function(settings) {
//                $(".dataTables_scrollBody").mCustomScrollbar({
//                    theme: "minimal-dark"
//                });
//        }
    });
    
    $("#reseller_searchbox").keyup(function () {//search code        
        resellerGrid.search(this.value).draw();
    });
    
    $('#msp_Reseller_Grid').DataTable().search( '' ).columns().search( '' ).draw();
    
    $('#msp_Reseller_Grid tbody').on('click', 'tr', function() {
        resellerGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = resellerGrid.row(this).id();
//        enableOptions(id);
    });
        
}

//function enableOptions(rowId){
//    var sel_resellerStatus = getCustomerId(rowId, 1);
//    if (rowId !== undefined || rowId !== '') {
//        $("#detailsCustomer_Li").removeClass('disableLi');
//        $("#detailsCustomer_Li a").removeClass('disableAnchor');
//    } else {
//        
//        $("#detailsCustomer_Li").addClass('disableLi');
//        $("#detailsCustomer_Li a").addClass('disableAnchor');
//    }
//    
//    if((rowId !== undefined || rowId !== '') && (sel_CustomerStatus == 1)) {
//        $("#editCustomer_Li").removeClass('disableLi');
//        $("#editCustomer_Li a").removeClass('disableAnchor');
//        $("#enableCustomer_Li").addClass('invisible');
//        $("#disableCustomer_Li").removeClass('invisible');
//        $("#renew_Li").removeClass('invisible');
//        $("#configureCustomer_Li").removeClass('invisible');
//    }else{
//        $("#editCustomer_Li").addClass('disableLi');
//        $("#editCustomer_Li a").addClass('disableAnchor');
//        $("#enableCustomer_Li").removeClass('invisible');
//        $("#disableCustomer_Li").addClass('invisible');
//        $("#renew_Li").addClass('invisible');
//        $("#configureCustomer_Li").addClass('invisible');
//    }
//    return true;
//}


function isTrialReseller(){
    $.ajax({
        url: '../lib/l-msp.php?function=MSP_IsTrialReseller&csrfMagicToken=' + csrfMagicToken,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            if ($.trim(response) === "NOT_TRIAL") {
                $('#warning_modal').modal('hide');
                $('#msp_CreateCustomer').modal('show');
            } else {
                $("#warning_msg").html("You are in trial period, please buy nanoheal licenses to create customers");
                $('#warning_modal').modal('show');
                $('#msp_CreateCustomer').modal('hide');
            }
        },
        error: function(response) {
            $("#addNewSite_error").html("Error Occurred");
            console.log('Error In create_Customer function : ' + response);
        }
    });
}


/*
 *----------------------------------------------------------------------------------------------------------------------
 * It will create site for MSP customer.
 *----------------------------------------------------------------------------------------------------------------------
 */
function create_Reseller() {

    var errorVal = 0;
    errorVal = validateAddReselomerForm();
    
    if (errorVal === 0) {
        var m_data = new FormData();
        
        //Customer's company's details.
        m_data.append('reselCompName', $('#resel_companyName').val());
        m_data.append('reselCompAddr', $('#resel_companyAddr').val());
        m_data.append('reselCompCity', $('#resel_companyCity').val());
        m_data.append('reselCompState', $('#resel_companyState').val());
        m_data.append('reselCompZipcode', $('#resel_companyZip').val());
        m_data.append('reselCompWebsite', $('#resel_compWeb').val());
        
        //Customer's details.
        m_data.append('reselFirstName', $('#resel_firstName').val());
        m_data.append('reselLastName', $('#resel_lastName').val());
        m_data.append('reselEmail', $('#resel_email').val());
        m_data.append('csrfMagicToken', csrfMagicToken);
        
        $("#add_ResellerMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        $.ajax({
            url: '../lib/l-msp.php?function=MSP_CreateReseller',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if($.trim(response.status) == "success"){
                    $('#add_ResellerMsg').css("color","green").html($.trim(response.msg));
                    get_CommercialResellers();
                    setTimeout(function() {
                        $('#msp_CreateReseller').modal('hide');
                    }, 3000);
                }else{
                    $('#add_ResellerMsg').css("color","red").html($.trim(response.msg));
                }
            },
            error: function(response) {
                $("#add_ResellerMsg").html("Error Occurred");
                console.log('Error In create_Reseller function : '+response);
            }
        });
    }
}

function validateAddReselomerForm(){
   $(".error").html(" *");
   var errorVal = 0;
   
   $('.addReselRequired').each(function() {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();
        
        if ($.trim(field_value) === "") {
            $("#required_" + field_id).css("color", "red").html(" required");
            errorVal++;
        } else if (field_id === "resel_companyName") {
            if (!validate_Alphanumeric(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
            }

        } else if (field_id === "resel_firstName") {
            if (!validate_Alphanumeric(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
            }

        } else if (field_id === "resel_lastName") {
            if (!validate_Alphanumeric(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
            }

        } else if (field_id === "resel_email") {
            if (!validate_Email(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html(" Enter valid email");
            }
        } else if (field_id === 'resel_companyZip') {
            if(!validate_ZipCode(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Enter only numeric values");
            }
        }
        
       /*else if (field_id == "pcCnt") {
            if (field_value < 0) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html(" Enter valid no of pc");
            }
        }*/
    });
    return errorVal;
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * It will create site for MSP customer.
 *----------------------------------------------------------------------------------------------------------------------
 */
function edit_Reseller() {
    var errorVal = 0;
    errorVal = validateEditResellerForm();
    
    if (errorVal === 0) {
        
        var m_data = new FormData();
        m_data.append('customerId', $('#hidden_customerId').val());
        m_data.append('customerEid', $('#hidden_Eid').val());
        m_data.append('edit_pcCnt', $('#edit_pcCnt').val());
        m_data.append('edit_compWeb', $('#edit_compWeb').val());
        m_data.append('edit_companyAddr', $('#edit_companyAddr').val());
        m_data.append('edit_companyCity', $('#edit_companyCity').val());
        m_data.append('edit_companyState', $('#edit_companyState').val());
        m_data.append('edit_companyZip', $('#edit_companyZip').val());
        m_data.append('edit_firstName', $('#edit_firstName').val());
        m_data.append('edit_lastName', $('#edit_lastName').val());
        m_data.append('edit_email', $('#edit_email').val());
        m_data.append('trialSiteEmail', $('#hidden_trialSiteEmail').val());
        m_data.append('csrfMagicToken', csrfMagicToken);
        
        $("#edit_CustomerMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        $.ajax({
            url: '../lib/l-msp.php?function=MSP_UpdateCustomer',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if(response.status === "success"){
                    $("#edit_CustomerMsg").css('color','green').html(response.msg);
                     setInterval(function() {
                        $("#msp_EditCustomer").modal('hide');
                        location.reload();
                    }, 2000);
                }else{
                    $("#edit_CustomerMsg").html(response.msg);
                }
            },
            error: function(response) {
                $("#edit_CustomerMsg").html("Error Occurred");
                console.log('Error In create_Customer function : '+response);
            }
        });
    }
}

function validateEditResellerForm(){
   $(".error").html(" *");
   var errorVal = 0;
   
   $('.editCustRequired').each(function() {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();
        
        if ($.trim(field_value) === "") {
            $("#required_" + field_id).css("color", "red").html(" required");
            errorVal++;
        } else if (field_id == "edit_firstName") {
            if (!validate_Alphanumeric(field_value)) {
                $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                errorVal++;
            }

        } else if (field_id == "edit_lastName") {
            if (!validate_Alphanumeric(field_value)) {
                $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                errorVal++;
            }

        } else if (field_id == "edit_email") {
            if (!validate_Email(field_value)) {
                $("#required_" + field_id).css("color", "red").html(" Enter valid email");
                errorVal++;
            }
        } else if (field_id == "pcCnt") {
            if (field_value < 0) {
                $("#required_" + field_id).css("color", "red").html(" Enter valid no of pc");
                errorVal++;
            }
        }
    });
    return errorVal;
}

function disable_Customer() {
    var rowId = $('#msp_Customer_Grid tbody tr.selected').attr('id');
    var channelID = getCustomerId(rowId, 2);
    
    if (rowId === undefined || rowId === 'undefined') {
        $("#warning_msg").html("Please select customer");
        $("#warning_modal").modal('show');
    } else {
        $.ajax({
            type: "GET",
            dataType: 'text',
            url: "../lib/l-msp.php",
            data: "function=MSP_DisableCustomer&chId=" + channelID + "&csrfMagicToken=" + csrfMagicToken,
            success: function(result) {
                var msg = $.trim(result);
                if (msg === 'done') {
                    $("#disable_msg").html("Customer account disabled successfully.");
                    setTimeout(function() {
                        $("#disable_site").modal('hide');
                        get_CommercialCustomers();
                    }, 2000);
                } else {
                    $("#disable_msg").html("Fail to disabled customer account.");
                }
            }
        });
    }
    return true;
}

function enable_Customer() {
    var rowId = $('#msp_Customer_Grid tbody tr.selected').attr('id');
    var channelID = getCustomerId(rowId, 2);

    if (channelID === undefined || channelID === 'undefined') {
        $("#warning_msg").html("Please select customer");
        $("#warning_modal").modal('show');
    } else {
        $.ajax({
            type: "GET",
            dataType: 'text',
            url: "../lib/l-msp.php",
            data: "function=MSP_EnableCustomer&chId=" + channelID + "&csrfMagicToken=" + csrfMagicToken,
            success: function(result) {
                var msg = $.trim(result);
                if (msg === 'done') {
                    $("#enable_msg").html("Customer account enabled successfully.");
                    setTimeout(function() {
                        $("#enable_site").modal('hide');
                        get_CommercialCustomers();
                    }, 2000);
                } else {
                    $("#enable_msg").html("Fail to enabled customer account.");
                }
            }
        });
    }

    return true;
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

$("#exportAllResellers").click(function(){
    location.href='../lib/l-msp.php?function=MSP_ExportAllReseller';
    closePopUp();
})

//###################################### Boostrap Modal CLOSE/OPEN Events Start ################################################//

$('#msp_CreateReseller').on('hidden.bs.modal', function() {
    $("#msp_CreateReseller input[type=text]").not("[readonly]").val('');
    $("#msp_CreateReseller .error").html("*");
    $("#add_ResellerMsg").html('');
});

$('#msp_EditReseller').on('hidden.bs.modal', function() {
    $("#msp_EditReseller input[type=text]").not("[readonly]").val('');
    $("#edit_ResellerMsg .error").html("*");
    $("#edit_ResellerMsg").html('<img src="../vendors/images/loader2.gif" class="loading orders" alt="loading..." />');
});

$('#enable_reseller').on('hidden.bs.modal', function() {
     $("#enable_msg").html("");   
});

$('#disable_reseller').on('hidden.bs.modal', function() {
     $("#disable_msg").html("");   
});

$('#warning_modal').on('hidden.bs.modal', function() {
     $("#warning_msg").html("");   
});

function reloadGrid() {
    setTimeout(function () {
        user_datatable();
    }, 2000);
}