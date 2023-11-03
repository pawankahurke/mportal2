fetchSubscriptionGrid();
fetchProductList();
var data_table;
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();

    var h = window.innerHeight;
    if (h > 700) {
        $("#add_subcription_table").attr("data-page-length", "14");
    }
    else {
        $("#add_subcription_table").attr("data-page-length", "8");
    }
    
    
});

function edit_site(id){
    $('#site_new').editable({
        type: 'text',
        pk: id,
        name: 'siteVal',
        url: 'addCustomerModel.php?function=change_site_name',
        title: 'Enter sitename'
    });
}


function fetchSubscriptionGrid() {
    $('#add_subcription_table').dataTable().fnDestroy();
    data_table = $('#add_subcription_table').DataTable({
        paging: true,
        searching: false,
        processing: true,
        serverSide: true,
        pagingType: "full_numbers",
        ajax: {
            url: "addCustomerModel.php?function=add_subscription_grid&csrfMagicToken=" + csrfMagicToken,
            type: "POST",
            rowId: 'id',
        },
        columns: [
            {"data": "product"},
            {"data": "key"},
            {"data": "terms"},
            {"data": "seats"},
            {"data": "site_name"},
            {"data": "installed"},
            {"data": "status"},
            {"data": "downloadurl"}
        ],
        columnDefs: [
            {className: "product", "targets": [0]},
            {"width": "20%", "targets": 5}
        ]
    });


    if (bussinessLevel === "Consumer") {
        var column = data_table.column(7);
        column.visible(!column.visible());
    } else if (bussinessLevel === "Commercial") {
        var column = data_table.column(6);
        column.visible(!column.visible());
    }


    $("#add_subcription_table_length").hide();
    edit_site();
//    triggerIncreament();
}

function fetchSubscriptionGrid1(skuRef , compId,license) {
    $('#add_subcription_table').dataTable().fnDestroy();
    data_table = $('#add_subcription_table').DataTable({
        paging: true,
        searching: false,
        processing: true,
        serverSide: true,
        pagingType: "full_numbers",
        ajax: {
            url: "addCustomerModel.php?function=add_subscription_grid_Param&skuRef=" + skuRef + "&compId=" + compId + "&licenseKey=" + license + '&csrfMagicToken=' + csrfMagicToken,
            type: "POST",
            rowId: 'id',
        },
        columns: [
            {"data": "product"},
            {"data": "key"},
            {"data": "terms"},
            {"data": "seats"},
            {"data": "site_name"},
            {"data": "installed"},
            {"data": "status"},
            {"data": "downloadurl"}
        ],
        columnDefs: [
            {className: "product", "targets": [0]},
            {"width": "20%", "targets": 5}
        ]
    });

    if (bussinessLevel === "Consumer") {
        var column = data_table.column(7);
        column.visible(!column.visible());
    } else if (bussinessLevel === "Commercial") {
        var column = data_table.column(6);
        column.visible(!column.visible());
    }
    
    $("#add_subcription_table_length").hide();
    edit_site();
//    triggerIncreament();
}

function fetchProductList() {
    $.ajax({
        url: 'addCustomerModel.php?function=getProduct_List' + '&csrfMagicToken=' + csrfMagicToken,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
           $("#product_list").html(response.trim());
           $(".selectpicker").selectpicker("refresh");
        },
        error: function(response) {
            
        }
    });
}

function addProductProvision() {
    var skuRef = $("#product_list").val();
    var license = $("#licenseKey").val();
    if ((skuRef === "" || skuRef === "0") && license === "") {
        $("#product_successMsg").css("color", "red").html("Please select product or enter license key");
        return false;
    } else if (skuRef !== "" && license !== "") {
        $("#product_successMsg").css("color", "red").html("Please choose any one option");
        return false;
    } else if (license !== ""){
        $.ajax({
            url: 'addCustomerModel.php?function=validateSubscriptionLicenseKey&licenseKey=' + license + '&csrfMagicToken=' + csrfMagicToken,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'text',
            success: function(response) {
                if (response.trim() === 1 || response.trim() === '1') {
                    $("#product_successMsg").css("color", "red").html("License Key already used");
                    return false;
                } else {
                    $.ajax({
                        url: 'addCustomerModel.php?function=confirmTrial&skuVal=' + skuRef + "&companyId=" + compId + "&licenseKey=" + license + "&csrfMagicToken=" + csrfMagicToken,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        dataType: 'json',
                        success: function(response) {

                            if ($.trim(response) === "1" || $.trim(response) === 1) {
                                $("#product_successMsg").css("color", "red").html("Your already used trial license");
                                return false;
                            } else {
                                fetchSubscriptionGrid1(skuRef, compId, license);
                            }
                        },
                        error: function(response) {

                        }
                    });
                }
            },
            error: function(response) {

            }
        });
    } else {
        $.ajax({
            url: 'addCustomerModel.php?function=confirmTrial&skuVal=' + skuRef + "&companyId=" + compId + "&licenseKey=" + license + "&csrfMagicToken=" + csrfMagicToken,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {

                if ($.trim(response) === "1" || $.trim(response) === 1) {
                    $("#product_successMsg").css("color", "red").html("Your already used trial license");
                    return false;
                } else {
                    fetchSubscriptionGrid1(skuRef, compId, license);
                }
            },
            error: function(response) {

            }
        });

    }
}


function validateLicenseKey(licenseKey){
    var result = '';
    $.ajax({
        url: 'addCustomerModel.php?function=validateSubscriptionLicenseKey&licenseKey=' + licenseKey + "&csrfMagicToken=" + csrfMagicToken,
        processData: false,
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

function generateProductProvision(sku,entityId,licenseKey){
    var regExp = /^[a-zA-Z0-9_]*$/;
    var noofpc = $.trim($("#quantity").val());
    var site_name = $.trim($("#new_site").val());
    
    if(site_name === ''){
        $("#product_successMsg").css("color", "red").html("Please enter sitename.");
        return false;
    } else if(!regExp.test(site_name)){
        $("#product_successMsg").css("color", "red").html("site name should be alphanumberic");
        return false;
    } else if(noofpc === 0 || noofpc === ''){
        $("#product_successMsg").css("color", "red").html("Please enter no of seats.");
        return false;
    }
    $.ajax({
        url: 'addCustomerModel.php?function=add_subscriberByProduct&skuVal=' + sku + "&companyId=" + entityId + "&noofpc=" + noofpc + "&site_name=" + site_name+ "&licenseKey="+licenseKey + "&csrfMagicToken=" + csrfMagicToken,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            
            if ($.trim(response.msg) === "EXIST") {
                $("#product_successMsg").css("color", "red").html("Sitename already exist, please enter new site name");
            } else {
                $("#product_successMsg").css("color", "green").html(response.msg);
                fetchSubscriptionGrid();
            }
        },
        error: function(response) {

        }
    });
}

function generateConsumerProvision(sku,entityId,licenseKey){
    var site_name = $.trim($("#new_site").val());
    var regExp = /^[a-zA-Z0-9_]*$/;
    if(site_name === ''){
        $("#product_successMsg").css("color", "red").html("Please enter sitename.");
        return false;
    }else if(!regExp.test(site_name)){
        $("#product_successMsg").css("color", "red").html("site name should be alphanumberic");
        return false;
    }
    $.ajax({
        url: 'addCustomerModel.php?function=add_subscriberByConsumer&skuVal=' + sku + "&companyId=" + entityId + "&site_name=" + site_name + "&licenseKey="+licenseKey + "&csrfMagicToken=" + csrfMagicToken,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
             if ($.trim(response.msg) === "EXIST") {
                $("#product_successMsg").css("color", "red").html("Sitename already exist, please enter new site name");
            } else {
                $("#product_successMsg").css("color", "green").html(response.msg);
                fetchSubscriptionGrid();
            }
        },
        error: function(response) {

        }
    });

}

function getPaymentThenProvision(sku,entityId){
    var regExp = /^[a-zA-Z0-9_]*$/;
    var site_name = $.trim($("#new_site").val());
    $.ajax({
        url: 'addCustomerModel.php?function=paymentModeCheck&skuid=' + sku + "&csrfMagicToken=" + csrfMagicToken,
        type: 'GET',
        dataType: 'text',
        success: function(response) {
            var arr = response.split("##");
            $("#payment_table").html(arr[0]);
            generateConsumerProvision(sku,entityId,'');
        },
        error: function(response) {
        }
    });
    return false;
    if(site_name === ''){
        $("#product_successMsg").css("color", "red").html("Please enter sitename.");
        return false;
    }
}

function copyDownloadUrl(url){
        $('#downloadLinkUrl4').val(url);
        $('#downloadurl_popup').modal('show'); 
        
}



//plugin bootstrap minus and plus
//http://jsfiddle.net/laelitenetwork/puJ6G/
    
    $('.btn-number').click(function(e) {
    e.preventDefault();
    fieldName = $(this).attr('data-field');
    type = $(this).attr('data-type');
    var input = $("input[name='" + fieldName + "']");
    var currentVal = parseInt(input.val());
    if (!isNaN(currentVal)) {
        if (type == 'minus') {

            if (currentVal > input.attr('min')) {
                input.val(currentVal - 1).change();
            }
            if (parseInt(input.val()) == input.attr('min')) {
                $(this).attr('disabled', true);
            }

        } else if (type == 'plus') {

            if (currentVal < input.attr('max')) {
                input.val(currentVal + 1).change();
            }
            if (parseInt(input.val()) == input.attr('max')) {
                $(this).attr('disabled', true);
            }

        }
    } else {
        input.val(0);
    }
});

$('.input-number').focusin(function() {
    $(this).data('oldValue', $(this).val());
});
$('.input-number').change(function() {

    minValue = parseInt($(this).attr('min'));
    maxValue = parseInt($(this).attr('max'));
    valueCurrent = parseInt($(this).val());
    name = $(this).attr('name');
    if (valueCurrent >= minValue) {
        $(".btn-number[data-type='minus'][data-field='" + name + "']").removeAttr('disabled')
    } else {
        alert('Sorry, the minimum value was reached');
        $(this).val($(this).data('oldValue'));
    }
    if (valueCurrent <= maxValue) {
        $(".btn-number[data-type='plus'][data-field='" + name + "']").removeAttr('disabled')
    } else {
        alert('Sorry, the maximum value was reached');
        $(this).val($(this).data('oldValue'));
    }


});
$(".input-number").keydown(function(e) {
    // Allow: backspace, delete, tab, escape, enter and .
    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
            // Allow: Ctrl+A
                    (e.keyCode == 65 && e.ctrlKey === true) ||
                    // Allow: home, end, left, right
                            (e.keyCode >= 35 && e.keyCode <= 39)) {
                // let it happen, don't do anything
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });

