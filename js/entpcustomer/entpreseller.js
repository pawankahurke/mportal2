$(document).ready(function () {
    get_CommercialResellers();
});


function get_CommercialResellers() {
    getResellers();
    getSkuList();
}

/**
 * Fetch all resellers list in json format which is required for Datatable.
 */
function getResellers() {

    $('#reseller_Grid').dataTable().fnDestroy();
    resellerGrid = $('#reseller_Grid').DataTable({
        scrollY: jQuery('#reseller_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        searching: true,
        processing: true,
        serverSide: false,
        bAutoWidth: true,
        ajax: {
            url: "../lib/l-entp.php?function=ENTP_GetResellerGrid"+"&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        "language": {
            "info": "_START_-_END_ of _TOTAL_ entries",
            searchPlaceholder: "Search"
        },
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        columns: [
            {"data": "reseller"},
            {"data": "firstName"},
            {"data": "lastName"},
            {"data": "email"},
            {"data": "status"},
        ],
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1, 2, 3]
            }],
        ordering: true,
        select: false,
        bInfo: false,
        responsive: true,
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function(settings, json) {
            resellerGrid.$('tr:first').click();
        },
        drawCallback: function(settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
        }
    });

    $("#reseller_searchbox").keyup(function() {//search code        
        resellerGrid.search(this.value).draw();
    });

    $('#reseller_Grid tbody').on('click', 'tr', function() {
        resellerGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = resellerGrid.row(this).id();
        getCustomer(id);
    });

}

function getCustomer(id){
    var channelID = id.split('---')[0];
     $('#customer_Grid').dataTable().fnDestroy();
    customerGrid = $('#customer_Grid').DataTable({
        scrollY: jQuery('#customer_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        searching: true,
        processing: true,
        serverSide: false,
        bAutoWidth: true,
        ajax: {
            url: "../lib/l-entp.php?function=ENTP_GetCustomerGrid&channelId=" + channelID +"&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        "language": {
            "info": "_START_-_END_ of _TOTAL_ entries",
            searchPlaceholder: "Search"
        },
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        columns: [
            {"data": "customer"},
            {"data": "firstName"},
            {"data": "lastName"},
            {"data": "email"},
            {"data": "status"},
        ],
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false,
            }, {
                className: "ignore", targets: [0, 1, 2, 3]
            }],
        ordering: true,
        select: false,
        bInfo: false,
        responsive: true,
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function(settings, json) {
            
        },
        drawCallback: function(settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
        }
    });

    $('#customer_Grid tbody').on('click', 'tr', function() {
        customerGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var id = customerGrid.row(this).id();
    });
}

function getSkuList() {

    $.ajax({
        type: 'POST',
        url: '../lib/l-entp.php',
        data: "function=ENTP_GetSkuDtlsByCid"+"&csrfMagicToken=" + csrfMagicToken,
        async: false,
        dataType: 'json',
        success: function(data) {
            var skuStr = "";
            for (var i = 0; i < data.length; i++) {
                skuStr += '<option value="' + data[i].id + '" >' + data[i].skuName + '</option>';
            }
            $("#resel_companySku").html(skuStr);
            $("#resel_editcompanySku").html(skuStr);
            $(".selectpicker").selectpicker("refresh");
        }
    });
}

function create_Reseller() {
    var errorVal = validateAddReselomerForm();

    if (errorVal === 0) {
        var m_data = new FormData();
        m_data.append('reselCompName', $('#resel_companyName').val());
        m_data.append('reselCompAddr', $('#resel_companyAddr').val());
        m_data.append('reselCompCity', $('#resel_companyCity').val());
        m_data.append('reselCompState', $('#resel_companyState').val());
        m_data.append('reselCompZipcode', $('#resel_companyZip').val());
        m_data.append('reselCompWebsite', $('#resel_compWeb').val());

        m_data.append('reselFirstName', $('#resel_firstName').val());
        m_data.append('reselLastName', $('#resel_lastName').val());
        m_data.append('reselEmail', $('#resel_email').val());
        m_data.append('reselSkus', $('#resel_companySku').val());
        m_data.append("csrfMagicToken",csrfMagicToken);
        
        $("#add_ResellerMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        $.ajax({
            url: '../lib/l-entp.php?function=ENTP_AddReseller',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if ($.trim(response.status) == "success") {
                    $('#add_ResellerMsg').css("color", "green").html($.trim(response.msg));
                    get_CommercialResellers();
                    setTimeout(function() {
                        $('#msp_CreateReseller').modal('hide');
                    }, 3000);
                } else {
                    $('#add_ResellerMsg').css("color", "red").html($.trim(response.msg));
                }
            },
            error: function(response) {
                $("#add_ResellerMsg").html("Error Occurred");
                console.log('Error In create_Reseller function : ' + response);
            }
        });
    }
}

function validateAddReselomerForm(){
   $(".error").html(" *");
   var errorVal = 0;
   
   $('.addReselRequired').each(function() {
        var field_id = this.id;
        if(field_id !== '') {
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
            } else if (field_id === "resel_companyZip")  {
                if(!validate_ZipCode(field_value)) {
                    errorVal++;
                     $("#required_" + field_id).css("color", "red").html("Enter valid zipcode");
                }

            }
            /*else if (field_id == "pcCnt") {
            if (field_value < 0) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html(" Enter valid no of pc");
            }
        }*/
        }
    });
    return errorVal;
}

function getResellerDetails() {
    var rowId = $('#reseller_Grid tbody tr.selected').attr('id');
    var selectedId = rowId.split("---")[0];
    $.ajax({
        url: '../lib/l-entp.php?function=ENTP_GetEntityDetails&eid=' + selectedId +"&csrfMagicToken=" + csrfMagicToken,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#resel_editcompanyName").val(response.companyName);
            $("#resel_editfirstName").val(response.firstName);
            $("#resel_editlastName").val(response.lastName);
            $("#resel_editemail").val(response.emailId);
            $("#resel_editcompWeb").val(response.website);
            $("#resel_editcompanyAddr").val(response.address);
            $("#resel_editcompanyCity").val(response.city);
            $("#resel_editcompanyState").val(response.province);
            $("#resel_editcompanyZip").val(response.zipcode);
            
            var skus = response.skulist.split(",");
            
        },
        error: function(response) {
           
        }
    });
    $("#msp_EditReseller").modal("show");
    $('#entity_id').val(selectedId);
}

function edit_Reseller() {
   
    var errorVal = validateEditReselomerForm();
    
    if (errorVal === 0) {
        var m_data = new FormData();

        m_data.append('reselCompAddr', $('#resel_editcompanyAddr').val());
        m_data.append('reselCompCity', $('#resel_editcompanyCity').val());
        m_data.append('reselCompState', $('#resel_editcompanyState').val());
        m_data.append('reselCompZipcode', $('#resel_editcompanyZip').val());
        m_data.append('reselCompWebsite', $('#resel_editcompWeb').val());

        m_data.append('reselFirstName', $('#resel_editfirstName').val());
        m_data.append('reselLastName', $('#resel_editlastName').val());
        m_data.append('reselSkus', $('#resel_companySku').val());
        m_data.append('reselEid', $('#entity_id').val());
        m_data.append("csrfMagicToken",csrfMagicToken);
        $("#edit_ResellerMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        $.ajax({
            url: '../lib/l-entp.php?function=ENTP_editReseller',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if ($.trim(response.status) == "success") {
                    $('#edit_ResellerMsg').css("color", "green").html($.trim(response.msg));
                    get_CommercialResellers();
                    setTimeout(function() {
                        $('#msp_EditReseller').modal('hide');
                    }, 3000);
                } else {
                    $('#edit_ResellerMsg').css("color", "red").html($.trim(response.msg));
                }
            },
            error: function(response) {
                $("#edit_ResellerMsg").html("Error Occurred");
                console.log('Error In update Reseller function : ' + response);
            }
        });
    }
    
    
}

function validateEditReselomerForm(){
   $(".error").html(" *");
   var errorVal = 0;
   
   $('.editReselRequired').each(function() {
        var field_id = this.id;
        if(field_id !== '') {
            var field_value = $("#" + field_id).val();
            if ($.trim(field_value) === "") {
                $("#required_" + field_id).css("color", "red").html(" required");
                errorVal++;
            } else if (field_id === "resel_editcompanyName") {
                if (!validate_Alphanumeric(field_value)) {
                    errorVal++;
                    $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                }

            } else if (field_id === "resel_editfirstName") {
                if (!validate_Alphanumeric(field_value)) {
                    errorVal++;
                    $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                }

            } else if (field_id === "resel_editlastName") {
                if (!validate_Alphanumeric(field_value)) {
                    errorVal++;
                    $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                }

            } else if (field_id === "resel_editemail") {
                if (!validate_Email(field_value)) {
                    errorVal++;
                    $("#required_" + field_id).css("color", "red").html(" Enter valid email");
                }
            } else if (field_id === "resel_editcompanyZip")  {
                if(!validate_ZipCode(field_value)) {
                    errorVal++;
                     $("#required_" + field_id).css("color", "red").html("Enter valid zipcode");
                }

            }
            /*else if (field_id == "pcCnt") {
            if (field_value < 0) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html(" Enter valid no of pc");
            }
        }*/
        }
    });
    return errorVal;
}

$('#msp_CreateReseller').on('hidden.bs.modal', function(e) {
   
    $('.form-group input').val('');
    
});

function exportAllResellers() {
    
    location.href='../lib/l-entp.php?function=ENTP_exportResellerData'+"&csrfMagicToken=" + csrfMagicToken;
    
}