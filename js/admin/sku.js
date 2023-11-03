$(document).ready(function() {
   getSkuList();
    
});


function getSkuList() {
    
    $("#sku_grid").dataTable().fnDestroy();
     
    skuTable = $('#sku_grid').DataTable({
        scrollY: jQuery('#sku_grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        searching: true,
        processing: true,
        serverSide: true,
        bAutoWidth: true,
        ordering: true,
        select: false,
        bInfo: false,
        responsive: true,
        order: [[0, "desc"]],
        ajax: {
            url: "../lib/l-ajax.php?function=AJAX_GetSkuDetails&csrfMagicToken=" + csrfMagicToken,
            type: "POST",
//            rowId : "id"
        },
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        columns: [
            {"data": "skuName"},
            {"data": "description"},
            
        ],
        columnDefs: [
            {className: "table-plus datatable-nosort", "targets": 0}, 
            {className: "datatable-nosort", "targets": 1},
            
        ],
        initComplete: function(settings, json) {
           skuTable.$('tr:first').click();
        },
        drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    
                }
    });
    $('#sku_grid tbody').on('click', 'tr', function() {
        skuTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        id = skuTable.row(this).id();
        $('#skuId').val(id);
      
    });
    
    $("#sku_searchbox").keyup(function() {
        skuTable.search(this.value).draw();
    });
    
}


function selectConfirm(data_target_id) {
    
    if(data_target_id === 'edit') {
       edit_details(); 
    }else if(data_target_id === 'export') {
        export_skulist();
    }
    
}

function add_sku() {
    
    var error = validateSkuDetails();
    
    if(error === 0) {
        var m_data = new FormData();
        
        m_data.append('skuName', $('#sku_name').val());
        m_data.append('skuRef', $('#sku_ref').val());
        m_data.append('skuDesc', $('#sku_desc').val());
        m_data.append('skuType', $('#skuType').val());
        m_data.append('reminder', $('#reminder').val());
        m_data.append('licenseCount', $('#license_count').val());
        
        //Customer's details.
        m_data.append('price', $('#sku_price').val());
        m_data.append('platformPrice', $('#platform_price').val());
        m_data.append('validity', $('#validity').val());
        m_data.append('language', $('#language').val());
        m_data.append('csrfMagicToken', csrfMagicToken);
        
        $("#add_SkuMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        
        $.ajax({
            url: '../lib/l-ajax.php?function=AJAX_createSku',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if($.trim(response.status) == "success"){
                    $('#add_SkuMsg').css("color","green").html($.trim(response.msg));
                    getSkuList();
                    setTimeout(function() {
                        $('#add_sku').modal('hide');
                    }, 3000);
                }else{
                    $('#add_SkuMsg').css("color","red").html($.trim(response.msg));
                }
            },
            error: function(response) {
                $("#add_SkuMsg").html("Error Occurred");
                console.log('Error In create_sku function : '+response);
            }
        });
    }
    
}

function validateSkuDetails() {
    
   $(".error").html(" *");
   var errorVal = 0;
   
    $('.addSkuRequired').each(function() {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();
        
        if ($.trim(field_value) === "") {
            $("#required_" + field_id).css("color", "red").html(" required");
            errorVal++;
        } else if (field_id === "sku_name") {
            if (!validate_Alphanumeric(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
            }

        } else if (field_id === "skuType") {
            if (field_value == '') {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("Please select sku type");
            }

        } else if (field_id === "license_count") {
            if (!validate_numeric(field_value)) {
                errorVal++;
                $("#required_" + field_id).css("color", "red").html("License count can be numeric only");
            }

        } 
       
    });
    return errorVal;
    
}

function edit_details() {
    
    var id = $('#skuId').val();
    
    
    $.ajax({
        url: '../lib/l-ajax.php?function=AJAX_editSkuDetails&id='+id+'&csrfMagicToken=' + csrfMagicToken,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            console.log(response);
            $('#edit_sku').modal('show');
            $('#edit_sku_name').val(response.skuName);
            $('#edit_sku_ref').val(response.skuRef);
            $('#edit_sku_desc').val(response.description);
            $('#edit_skuType').val(response.skuType);
            $('#edit_license_count').val(response.licenseCnt);
            $('#edit_sku_price').val(response.skuPrice);
            $('#edit_platform_price').val(response.platformPrice);
            $('#edit_validity').val(response.licensePeriod);
            $('#edit_language').val(response.localization);
            $(".selectpicker").selectpicker("refresh");
            
            
        },
        error: function(response) {
            $("#add_SkuMsg").html("Error Occurred");
            console.log('Error In update_sku function : '+response);
        }
    });
    
}

function edit_sku() {
    
    var error = validateSkuDetails();
    
    if(error === 0) {
        var m_data = new FormData();
        
        m_data.append('skuName', $('#edit_sku_name').val());
        m_data.append('skuRef', $('#edit_sku_ref').val());
        m_data.append('skuDesc', $('#edit_sku_desc').val());
        m_data.append('skuType', $('#edit_skuType').val());
        m_data.append('reminder', $('#edit_reminder').val());
        m_data.append('licenseCount', $('#edit_license_count').val());
        
        //Customer's details.
        m_data.append('price', $('#edit_sku_price').val());
        m_data.append('platformPrice', $('#edit_platform_price').val());
        m_data.append('validity', $('#edit_validity').val());
        m_data.append('language', $('#edit_language').val());
        m_data.append('id',$('#skuId').val());
        
        $("#edit_SkuMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
        
        $.ajax({
            url: '../lib/l-ajax.php?function=AJAX_updateSku&csrfMagicToken=' + csrfMagicToken,
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if($.trim(response.status) == "success"){
                    $('#edit_SkuMsg').css("color","green").html($.trim(response.msg));
                    getSkuList();
                    setTimeout(function() {
                        $('#edit_sku').modal('hide');
                    }, 3000);
                }else{
                    $('#edit_SkuMsg').css("color","red").html($.trim(response.msg));
                }
            },
            error: function(response) {
                $("#edit_SkuMsg").html("Error Occurred");
                console.log('Error In update_sku function : '+response);
            }
        });
    }
    
}

function export_skulist() {
   
    window.location.href = '../lib/l-ajax.php?function=AJAX_skuDetailsExport&csrfMagicToken=' + csrfMagicToken;
    
}