
var data_table;
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();

    var h = window.innerHeight;
    if (h > 700) {
        $("#entitlement_table").attr("data-page-length", "14");
    }
    else {
        $("#entitlement_table").attr("data-page-length", "8");
    }


});



function validateEmailAddr(email) {
    var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
    if (filter.test(email)) {
        return true;
    }
    else {
        return false;
    }
}
function validateZipCode(zipcode) {
//    var regExp = /^\+?([0-9]{2})\)?[-. ]?([0-9]{4})[-. ]?([0-9]{4})$/;
    var regExp = /^[0-9]+$/;
    if (regExp.test(zipcode)) {
        return true;
    } else {
        return false;
    }
}

function validatePhoneNumber(phoneNumber) {
    var length = phoneNumber.length;
    var regExp = /^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$/;
    if (length > 15) {
        return false;
    } else if (regExp.test(phoneNumber)) {
        return true;
    } else {
        return false;
    }
}


function fetchEntitlmentGrid(id, licenseKey , cid) {
    $('#entitlement_table').dataTable().fnDestroy();
    data_table = $('#entitlement_table').DataTable({
        paging: true,
        searching: false,
        processing: true,
        serverSide: true,
        pagingType: "full_numbers",
        ajax: {
            url: "addCustomerModel.php?function=entitlement_grid&customerId=" + id + "&cid=" + cid + "&licenseKey=" + licenseKey,
            type: "POST",
            rowId: 'id',
        },
        columns: [
            {"data": "serial_num"},
            {"data": "first_name"},
            {"data": "last_name"},
            {"data": "email"},
            {"data": "product"},
            {"data": "expiry_date"},
            {"data": "machine_os"},
            {"data": "model_no"},
            {"data": "brand"},
            {"data": "activate_key"}
        ],
         columnDefs: [
            {className: "dt-left", "targets": [0, 1, 2, 3, 4, 5, 6]},
        ],
    });

//    if (bussinessLevel === "Consumer") {
//        var column = data_table.column(7);
//        column.visible(!column.visible());
//    } else if (bussinessLevel === "Commercial") {
//        var column = data_table.column(6);
//        column.visible(!column.visible());
//    }

    if (bussinessLevel === "Commercial") {
        data_table.column( 6 ).visible( true );
        data_table.column( 7 ).visible( true );
        data_table.column( 8 ).visible( true );
        data_table.column( 1 ).visible( false );
        data_table.column( 2 ).visible( false );
        data_table.column( 3 ).visible( false );
    } else{
        data_table.column( 6 ).visible( false );
        data_table.column( 7 ).visible( false );
        data_table.column( 8 ).visible( false );
        data_table.column( 1 ).visible( true );
        data_table.column( 2 ).visible( true );
        data_table.column( 3 ).visible( true );
    }


    $('#entitlement_table tbody').on('click', 'tr', function() {
        if ($(this).hasClass('selected')) {
            data_table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var rowdata = data_table.row(this).data();
            var downstat = rowdata.downStatus;
            var revokstat = rowdata.revokeStatus;
            $("#selectedDevCID").val(rowdata.devCID);
            $("#selectedDevPID").val(rowdata.devPID);
            $("#selectedOrder").val(rowdata.orderNum);
            $("#selectedCustomer").val(rowdata.customerNum);
            show_revok_regen_li(downstat, revokstat);
            
        } else {
            data_table.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var rowdata = data_table.row(this).data();
            var downstat = rowdata.downStatus;
            var revokstat = rowdata.revokeStatus;
            $("#selectedDevCID").val(rowdata.devCID);
            $("#selectedDevPID").val(rowdata.devPID);
            $("#selectedOrder").val(rowdata.orderNum);
            $("#selectedCustomer").val(rowdata.customerNum);
            show_revok_regen_li(downstat, revokstat);
        }
    });
    $("#entitlement_table_length").hide();
}

function show_revok_regen_li(downstat, revokstat) {
    $("#remote_option").show();
    $("#downloadurl_option").show();
    if (downstat === "EXE" && revokstat === "I") {
        $("#regenerate_option").hide();
        $("#revoke_option").show();
    } else if ((downstat === "D" || downstat === "G") && revokstat === "I") {
        $("#regenerate_option").show();
        $("#revoke_option").hide();
    } else {
        $("#regenerate_option").hide();
        $("#revoke_option").hide();
    }
    return true;
}

function revokeServicetag() {
    $("#revoke_successMsg").html("");
    $("#downloadLinkUrl3").val("");
    var id = $('#entitlement_table tbody tr.selected').attr('id');
    if (id === '' || id === undefined) {
        $("#warning_msg").html("Please select a row");
        $('#revoke_option').attr('data-bs-target', '#warning');
        return false;
    } else {
        var selected = $('#entitlement_table tbody tr.selected');
        var deviceId = selected[0]['firstChild']['innerText'];
        if (deviceId === '') {
            $('#revoke_option').attr('data-bs-target', '#warning');
            return false;
        } else {
            $('#revoke_option').attr('data-bs-target', '#revoke_popup');
            var orderNum = $("#selectedOrder").val();
            var customerNum = $("#selectedCustomer").val();
            var m_data = new FormData();

            m_data.append('orderNum', $("#selectedOrder").val());
            m_data.append('customerNum', $("#selectedCustomer").val());
            m_data.append('servicetag', deviceId);
            m_data.append('devicecid', $("#selectedDevCID").val());
            m_data.append('devicepid', $("#selectedDevPID").val());
            m_data.append('csrfMagicToken', csrfMagicToken);

            $.ajax({
                url: 'addCustomerModel.php?function=revokeOrder',
                data: m_data,
                processData: false,
                contentType: false,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $("#revoke_successMsg").html(response.msg);
                    $("#downloadLinkUrl3").val(response.link);
                    $('#subscription_detail').DataTable().ajax.reload();
                    $('#revoke_option').hide();
                },
                error: function(response) {
                    $("#revoke_successMsg").html("Error Occurred");
                }
            });
        }
    }

}

function regenerateServicetag() {
    $("#regenerate_successMsg").html("");
    $("#downloadLinkUrl4").val("");
    var id = $('#entitlement_table tbody tr.selected').attr('id');
    if (id === '' || id === undefined) {
        $("#warning_msg").html("Please select a row");
        $('#regenerate_option').attr('data-bs-target', '#warning');
        return false;
    } else {
        var selected = $('#entitlement_table tbody tr.selected');
        var deviceId = selected[0]['firstChild']['innerText'];
        if (deviceId === '') {
            $('#regenerate_option').attr('data-bs-target', '#warning');
            return false;
        } else {
            $('#regenerate_option').attr('data-bs-target', '#regenerate_popup');
            var orderNum = $("#selectedOrder").val();
            var customerNum = $("#selectedCustomer").val();
            var m_data = new FormData();

            m_data.append('orderNum', $("#selectedOrder").val());
            m_data.append('customerNum', $("#selectedCustomer").val());
            m_data.append('servicetag', deviceId);
            m_data.append('devicecid', $("#selectedDevCID").val());
            m_data.append('devicepid', $("#selectedDevPID").val());
            m_data.append('csrfMagicToken', csrfMagicToken);

            $.ajax({
                url: 'addCustomerModel.php?function=regenarate_servicetag',
                data: m_data,
                processData: false,
                contentType: false,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    $("#regenerate_successMsg").html(response.msg);
                    $("#downloadLinkUrl4").val(response.link);
                    $('#subscription_detail').DataTable().ajax.reload();
                    $('#regenerate_option').hide();
                },
                error: function(response) {
                    $("#regenerate_successMsg").html("Error Occurred");
                }
            });
        }
    }

}

function remoteDiagnostics() {
    var id = $('#entitlement_table tbody tr.selected').attr('id')
    if (id === '' || id === undefined) {
        $("#warning_msg").html("Please select a row");
        $('#remote_option').attr('data-bs-target', '#warning');
        return false;
    }else{
        var selected = $('#entitlement_table tbody tr.selected');
        var serviceTag = selected[0]['firstChild']['innerText'];
        location.href = "../support_action/index.php?servicetag=" + serviceTag + "&frm=entl";
    }
}

function downloadUrl(){
    var id = $('#entitlement_table tbody tr.selected').attr('id')
    if (id === '' || id === undefined) {
        $("#warning_msg").html("Please select a row");
        $('#downloadurl_option').attr('data-bs-target', '#warning');
        return false;
    } else {
        $("#warning_msg").html("");
        $.ajax({
            url: 'addCustomerModel.php?function=entitlement_DownloadURL&sid=' + id + "&csrfMagicToken=" + csrfMagicToken,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'text',
            success: function(response) {
                 $("#download_successMsg").html("");
                 $("#downloadLinkUrl12").val(response.trim());
            },
            error: function(response) {

            }
        });
    }
}

function fetchSiteList() {
    $.ajax({
        url: 'addCustomerModel.php?function=getSite_List&csrfMagicToken=' + csrfMagicToken,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            var arr = response.split("$$$");
            $("#site_list").html(arr[0].trim());
            $(".selectpicker").selectpicker("refresh");
            if (bussinessLevel !== "Consumer") {
                $("#downloadLinkUrl1").val($.trim(arr[2]));
                
            }
            fetchEntitlmentGrid(arr[1],'', cid);
        },
        error: function(response) {

        }
    });
}

function refreshGrid() {
//    $("#entitlement_successMsg").html('');
    if (bussinessLevel !== "Consumer") {
        var custId = $("#site_list").val();
        var license = '';
//        var license = $("#licenseKey").val();
        if (custId === "" || custId === "0") {
            $("#entitlement_successMsg").css("color", "red").html("Please enter sitename or license key");
            return false;
        } else if (custId !== "" || custId !== "0") {
            $.ajax({
                url: 'addCustomerModel.php?function=confirmEntitleMentTrial&skuVal=' + custId + "&companyId=" + cid + "&csrfMagicToken=" + csrfMagicToken,
                processData: false,
                contentType: false,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if ($.trim(response) === "1" || $.trim(response) === 1) {
                        $("#trial_used").val('1');
                        $('#add_device_option').attr('data-bs-target', '');
                        return false;
                    } else {
                        $("#trial_used").val('0');
                        $("#add_device_option").attr('data-bs-target', '#add_device');
                        $("#warning_msg").html("");
                        fetchDownloadUrl(custId);
                        fetchEntitlmentGrid(custId, license, cid);
                    }
                },
                error: function(response) {

                }
            });

        }
    } else {
        var custId = $("#site_list").val();
        var license = '';
//        var license = $("#licenseKey").val();
        if (custId === "" || custId === "0") {
            $("#entitlement_successMsg").css("color", "red").html("Please enter sitename or license key");
            return false;
        } else {
            fetchEntitlmentGrid(custId, license, cid);
        }
    }

}

function fetchDownloadUrl(custId){
    $.ajax({
        url: 'addCustomerModel.php?function=fetchDownloadUrl&siteId='+custId + "&csrfMagicToken=" + csrfMagicToken,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
           $("#downloadLinkUrl1").val($.trim(response));
        },
        error: function(response) {

        }
    });
}

function confirmDevice(){
    var custId = $("#site_list").val();
    $("#payment_for_sku").html("");
//    var license = $("#licenseKey").val();
    if (custId === "" || custId === "0") {
        $("#warning_msg").html("Please select a site");
        $("#add_device_option").attr('data-bs-target', '#warning');
        return false;
    } else if (custId !== "") {
        var trial_used = $("#trial_used").val();
        if(trial_used === 1 || trial_used === '1'){
            $("#entitlement_successMsg").css("color","red").html("You already used Trial Lincense");
        } else {
            $("#entitlement_successMsg").html('');
            $.ajax({
                url: 'addCustomerModel.php?function=get_validateSite&custId=' + custId + "&csrfMagicToken=" + csrfMagicToken,
                processData: false,
                contentType: false,
                type: 'POST',
                dataType: 'text',
                success: function(response) {
                    $("#skuSelectPicker").html($.trim(response));
                    $(".selectpicker").selectpicker("refresh");
                    paymentModeCheck("skuSelectPicker", "payment_for_sku")

                },
                error: function(response) {

                }
            });
        }
    }
}


function paymentModeCheck(skuId,divId){
    var selSku = $("#"+skuId).val();
    $.ajax({
        url: 'addCustomerModel.php?function=paymentModeCheck&skuid='+selSku + "&csrfMagicToken=" + csrfMagicToken,
        type: 'GET',
        dataType: 'text',
        success: function(response) {
            var arr = response.split("##");
            $("#"+divId).html(arr[0]);
            if(selSku != ""){
                $("#upselectedexpire").val(arr[1]);
                $("#upselectedexpire").parent().removeClass("is-empty");
            }else{
                $("#upselectedexpire").val("");
                $("#upselectedexpire").parent().addClass("is-empty");
            }
            
        },
        error: function(response) {
        }
    });
}


function add_entitlement_consumer() {
    var isReqFieldsFilled = false;
    var custId = $("#site_list").val();
//    var license = $("#licenseKey").val();
    
    $('.req').each(function() {
        var req_id = this.id;
        var field_id = req_id.replace("err_", "");
        var field_value = $("#" + field_id).val();
        if ($.trim(field_value) === "") {
            $("#err_" + field_id).css("color", "red").html(" required");
            isReqFieldsFilled = false;
            return false;
        } else if (field_id == "email") {
            if (!validateEmailAddr(field_value)) {
                isReqFieldsFilled = false;
                $("#err_" + field_id).css("color", "red").html(" enter valid email");
                return false
            } else {
                $("#err_" + field_id).css("color", "red").html("");
            }
        } else if (field_id == "refnumber") {
            if (field_value.length < 7 || field_value.length > 16) {
                isReqFieldsFilled = false;
                $("#add_device_successMsg").css("color", "red").html(" enter reference number between 7 to 16 characters");
                return false
            } else {
                $("#add_device_successMsg").html("");
            }
        } else if (field_id == "orderRefNumber") {
            if (field_value.length < 7 || field_value.length > 16) {
                isReqFieldsFilled = false;
                $("#add_device_successMsg").css("color", "red").html(" enter order number between 7 to 16 characters");
                return false
            } else {
                $("#add_device_successMsg").html("");
            }
        } else if (field_id == "phnumber") {
            if (!validatePhoneNumber(field_value)) {
                isReqFieldsFilled = false;
                $("#err_" + field_id).css("color", "red").html(" enter valid phone number");
                return false;
            } else {
                $("#err_" + field_id).css("color", "red").html("");
            }

        } else if (field_id == "zpcode") {
            if (!validateZipCode(field_value)) {
                isReqFieldsFilled = false;
                $("#err_" + field_id).css("color", "red").html(" enter valid zipcode");
                return false;
            } else {
                $("#err_" + field_id).css("color", "red").html("");
            }

        } else {
            isReqFieldsFilled = true;
        }
    });
    
    if (isReqFieldsFilled) {
        var m_data = new FormData();
        var skuval = $("#skuSelectPicker").val();
        var orderRef = $("#orderRefNumber").val();
//        m_data.append('customerFirstName', $('#name').val());
        m_data.append('bussiLevel', $('#bussiLevel').val());
        m_data.append('companyId', $('#companyId').val());
        m_data.append('name', $('#name').val());
        m_data.append('addr', $('#addr').val());
        m_data.append('city', $('#city').val());
        m_data.append('stprov', $('#stprov').val());
        m_data.append('zpcode', $('#zpcode').val());
        m_data.append('country', $('#country').val());
        m_data.append('orderRefNumber', $('#orderRefNumber').val());
        m_data.append('refCustomerNum', $('#refnumber').val());
        m_data.append('customerFirstName', $('#fname').val());
        m_data.append('customerLastName', $('#lname').val());
        m_data.append('customerEmailId', $('#email').val());
        m_data.append('phnumber', $('#phnumber').val());
        m_data.append('selectedCid', $('#selectedCid').val());
        m_data.append('custId', custId);
//        m_data.append('licenseKey', license);

        m_data.append('skuVal', skuval);
      

        add_entitlement_consumer_submit(m_data);
        return true;
    }

    
}

function add_entitlement_consumer_submit(m_data) {

    $("#add_device_successMsg").html('<img src="../images/ajax-login.gif" class="loadhome" alt="loading..." style="width:auto !important;padding-left:39% !important;"/>');
    
    $.ajax({
        url: 'addCustomerModel.php?function=addEntitlement_Subscriber' + "&csrfMagicToken=" + csrfMagicToken,
        data: m_data,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#add_device_successMsg").html(response.msg);
            $("#downloadLinkUrl2").val(response.link);
        },
        error: function(response) {
            $("#add_successMsg").html("Error Occurred");
        }
    });
    return true;
}



