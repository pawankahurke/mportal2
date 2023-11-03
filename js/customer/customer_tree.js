$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
    
     var h = window.innerHeight;
    if(h>700){
        $("#subscription_detail").attr("data-page-length","10");
    }
    else{
        $("#subscription_detail").attr("data-page-length","5");
    }
});

function vshow_site_name(){
    var val = $("#vsite_crieteria").val();
    if(val == "friendly_name"){
        $("#vfSiteDiv").show();
    }else{
        $("#vfSiteDiv").hide();
    }
}

function vlshow_site_name(){
    var val = $("#vlsite_crieteria").val();
    if(val == "friendly_name"){
        $("#vlfSiteDiv").show();
    }else{
        $("#vlfSiteDiv").hide();
    }
}

function upshow_site_name(){
    var val = $("#upsite_crieteria").val();
    if(val == "friendly_name"){
        $("#upfSiteDiv").show();
    }else{
        $("#upfSiteDiv").hide();
    }
}

function renshow_site_name(){
    var val = $("#rensite_crieteria").val();
    if(val == "friendly_name"){
        $("#renfSiteDiv").show();
    }else{
        $("#renfSiteDiv").hide();
    }
}

function hideVFriendlySiteOption(){
    var opt = $("#vsiteSelectPicker").val();
    if(opt !== ""){
        $("#vsite_crieteria_div").hide();
        $("#vfSiteDiv").hide();
    }else if(opt === ""){
       $("#vsite_crieteria_div").show();
    }
}

function hideupFriendlySiteOption(){
    var opt = $("#upsiteSelectPicker").val();
    if(opt !== ""){
        $("#upsite_crieteria_div").hide();
        $("#upfSiteDiv").hide();
    }else if(opt === ""){
       $("#upsite_crieteria_div").show();
    }
}

function hiderenFriendlySiteOption(){
    var opt = $("#rensiteSelectPicker").val();
    if(opt !== ""){
        $("#rensite_crieteria_div").hide();
        $("#renfSiteDiv").hide();
    }else if(opt === ""){
       $("#rensite_crieteria_div").show();
    }
}

function hideVLFriendlySiteOption(){
    var opt = $("#vlsiteSelectPicker").val();
    if(opt !== ""){
        $("#vlsite_crieteria_div").hide();
        $("#vlfSiteDiv").hide();
    }else if(opt === ""){
        $("#vlsite_crieteria_div").show();
    }
}

function validateEmailAddr(email) {
    var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
    if (filter.test(email)) {
        return true;
    }
    else {
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

function validateZipCode(zipcode) {
//    var regExp = /^\+?([0-9]{2})\)?[-. ]?([0-9]{4})[-. ]?([0-9]{4})$/;
    var regExp = /^[0-9]+$/;
    if (regExp.test(zipcode)) {
        return true;
    } else {
        return false;
    }
}

function getSkuListForCustomer() {
    $.ajax({
        url: 'addCustomerModel.php?function=getSkuListForCustomers' + "&csrfMagicToken=" + csrfMagicToken,
        type: 'GET',
        dataType: 'text',
        success: function(response) {
            $("#skuSelectPicker").html(response.trim());
            $("#vlskuSelectPicker").html(response.trim());
            $("#vskuSelectPicker").html(response.trim());
            
            $(".selectpicker").selectpicker('refresh');
        },
        error: function(response) {
        }
    });
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

function fetchCustomerList(entityId, customerType) {
   
    $("#downloadurl_option").hide();
    $("#renew_option").hide();
    $("#upgrade_option").hide();
    
    $("#customer_list").html('<img src="../images/ajax-login.gif" class="loadhome" alt="loading..." style="width:auto !important;padding-left:22% !important;"/>');
    $.ajax({
        type: 'POST',
        url: "customer_treeFunction.php?function=get_CustomerList&entityId=" + entityId + "&customerType=" + customerType + "&csrfMagicToken=" + csrfMagicToken,
        async: false,
        dataType: "text",
        success: function(data) {
            var arr = data.split("##");
            $("#customer_list").html(arr[0]);
            fetchDeviceGrid(arr[2],arr[3],arr[1]); /** arr[2]->Order Number, arr[3]->Sku Number, arr[1]->Customer Number  **/
        }
    });
}

function fetchDeviceGrid(orderNum, skuNum, customerNum) {
    $("#remote_option").hide();
    if(orderNum !== ""){
        $('.panel-body .nicescroll li a').attr('style', 'color: #595959 !important;');
        $("#"+orderNum).css("color","#48b2e4");
        $("#selectedOrder").val(orderNum);
        $("#selectedCustomer").val(customerNum);
        $("#downloadurl_option").show();
        $("#renew_option").show();
        $("#upgrade_option").show();
    }
    
    var upgradeShow = getUpgradeOption(skuNum);
    
    if(datatable == false){
        $("#subscription_detail").dataTable().fnDestroy();
    }
    
    var table1 = $('#subscription_detail').DataTable({
        paging: true,
        searching: false,
        processing: true,
        serverSide: true,
        pagingType: "full_numbers",
        ajax: {
            url: "customer_treeFunction.php?function=get_OrderHistory&orderNum=" + orderNum,
            type: "POST",
            rowId: 'id',
        },
        columns: [
            {"data": "device_id"},
            {"data": "install_date"},
            {"data": "valid_till"},
            {"data": "device_info"}
        ],
        columnDefs: [{
                targets: 3,
                orderable: false
            },
        ]
    });
    
    $('#subscription_detail tbody').on('click', 'tr', function() {
        if ($(this).hasClass('selected')) {
            table1.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var rowdata = table1.row(this).data();
            var downstat = rowdata.downStatus;
            var revokstat = rowdata.revokeStatus;
            $("#selectedDevCID").val(rowdata.devCID);
            $("#selectedDevPID").val(rowdata.devPID);
            show_revok_regen_li(downstat, revokstat);
            
        } else {
            table1.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            var rowdata = table1.row(this).data();
            var downstat = rowdata.downStatus;
            var revokstat = rowdata.revokeStatus;
            $("#selectedDevCID").val(rowdata.devCID);
            $("#selectedDevPID").val(rowdata.devPID);
            show_revok_regen_li(downstat, revokstat);
        }
    });
    fetchDetailsOfOrder(orderNum, customerNum)
    $("#subscription_detail_length").hide();
    datatable = false;
}

function getUpgradeOption(skuNum){
    $.ajax({
        type: 'POST',
        url: "customer_treeFunction.php?function=get_upgrade_option&skuNum=" + skuNum + "&csrfMagicToken=" + csrfMagicToken,
        async: false,
        dataType: "text",
        success: function(data) {
            if(data.trim() === "EXIST" || data.trim() === 'EXIST'){
                $("#upgrade_option").show();
            }else if(data.trim() === "NOTEXIST" || data.trim() === 'NOTEXIST'){
                $("#upgrade_option").hide();
            }else{
                $("#upgrade_option").hide();
            }
        }
    });
    return true;
}



/**
 * function fetchDetailsOfOrder();
 * This function for getting sku list with checkboxes.
 * @param -orderNum & customerNum;
 * @return -detail of respective customer number to use in upgrade & renew;
 * @author-
 */
function fetchDetailsOfOrder(orderNum, customerNum){
    $.ajax({
        url: "customer_treeFunction.php?function=get_order_details&orderNum=" + orderNum + "&customerNum=" + customerNum + "&csrfMagicToken=" + csrfMagicToken,
        data: "",
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            var details = $.parseJSON(response);
            $("#upskuSelectPicker").html(details.upgradeSku.trim());
            $("#renskuSelectPicker").html(details.renewSku.trim());
            $(".selectpicker").selectpicker('refresh');
            $("#downloadLinkUrl1").val(details.downloadUrl);
            setDetailsOfOrder(details);
        },
        error: function(response) {
            $("#downloadLinkUrl1").val("Error Occurred");
        }
    });
}

/**
 * function setDetailsOfOrder;
 * This function for setting the details of selected order.
 * @param - details;
 * @return - set details of selected order in upgrade pop up;
 * @author-
 */
function setDetailsOfOrder(details){
    $("#upselectedexpire").val("");
    $("#upgrade_popup .form-group input").val("");
    $("#renew_popup .form-group input").val("");
    $('#upgrade_cust_num').val("");
    $('#upgrade_ref_cust_num').val("");
    $('#renew_cust_num').val("");
    $('#renew_ref_cust_num').val("");
    $('#upgrade_ref_cust_num').val(details.refCustomerOrder);
    $('#renew_ref_cust_num').val(details.refCustomerOrder);
    $("#upname").val(details.coustomerFirstName);
    $("#upname").parent().removeClass("is-empty");
    $("#upcurrentrefnumber").val(details.orderNum);
    $("#upcurrentrefnumber").parent().removeClass("is-empty");
    $("#upemail").val(details.emailId);
    $("#upemail").parent().removeClass("is-empty");
    $("#upcurrentexpire").val(details.currentExpiry);
    $("#upcurrentexpire").parent().removeClass("is-empty");
    $('#upgrade_cust_num').val(details.customerNum);
    $('#upgrade_ref_cust_num').val(details.refCustomerOrder);
    $('#renew_cust_num').val(details.customerNum);
    $('#renew_ref_cust_num').val(details.refCustomerOrder);
    $("#upgrade_compId").val(details.compId);
    $("#upsiteSelectPicker").html(details.order_sites);
    $(".selectpicker").selectpicker("refresh");
    
    $("#renselectedexpire").val("");
    $("#rengrade_popup .form-group input").val("");
    $("#renew_popup .form-group input").val("");
    $('#renew_cust_num').val("");
    $('#renew_ref_cust_num').val("");
    $('#renew_cust_num').val("");
    $('#renew_ref_cust_num').val("");
    $('#renew_ref_cust_num').val(details.refCustomerOrder);
    $('#renew_ref_cust_num').val(details.refCustomerOrder);
    $("#renname").val(details.coustomerFirstName);
    $("#renname").parent().removeClass("is-empty");
    $("#rencurrentrefnumber").val(details.orderNum);
    $("#rencurrentrefnumber").parent().removeClass("is-empty");
    $("#renemail").val(details.emailId);
    $("#renemail").parent().removeClass("is-empty");
    $("#rencurrentexpire").val(details.currentExpiry);
    $("#rencurrentexpire").parent().removeClass("is-empty");
    $('#rengrade_cust_num').val(details.customerNum);
    $('#rengrade_ref_cust_num').val(details.refCustomerOrder);
    $('#renew_cust_num').val(details.customerNum);
    $('#renew_ref_cust_num').val(details.refCustomerOrder);
    $("#renew_compId").val(details.compId);
    $("#rensiteSelectPicker").html(details.order_sites);
    $(".selectpicker").selectpicker("refresh");

}


function upgradeSubmit(){
    var isReqFieldsFilled = false;
    $('.req_up').each(function() {
        var req_id = this.id;
        var field_id = req_id.replace("err_", "");
        var field_value = $("#" + field_id).val();
        if ($.trim(field_value) === "") {
            $("#err_" + field_id).css("color", "red").html(" required");
            isReqFieldsFilled = false;
            return false;
        } else if (field_id == "upnewrefnumber") {
            if (field_value.length < 7 || field_value.length > 16) {
                isReqFieldsFilled = false;
                $("#upgrade_successMsg").css("color", "red").html(" enter new order number between 7 to 16 characters");
                return false
            } else {
                $("#upgrade_successMsg").css("color", "red").html("");
            }
        }else {
            isReqFieldsFilled = true;
        }
    });
    
    if($("#upsiteSelectPicker").val() === ""){
        if ($("#upsite_crieteria").val() === "") {
            $("#err_upsite_crieteria").css("color", "red").html(" required");
            isReqFieldsFilled = false;
            return false;
        } else if ($("#upsite_crieteria").val() === "friendly_name") {
            if ($("#upfsite").val() == "") {
                $("#err_upfsite").css("color", "red").html(" required");
                isReqFieldsFilled = false;
                return false;
            }else if ($("#upfsite").val().length < 3 || $("#upfsite").val().length > 15) {
                $("#upgrade_successMsg").css("color", "red").html(" Enter friendly name between 3 to 15 characters");
                isReqFieldsFilled = false;
            }else {
                $("#upgrade_successMsg").html("");
                isReqFieldsFilled = true;
            }
        }
    }
    
    if (isReqFieldsFilled) {
        var m_data = new FormData();
        var skuval = $("#upskuSelectPicker").val();
        var orderRef = $("#upnewrefnumber").val();
        m_data.append('customerFirstName', $('#upname').val());
        m_data.append('bussiLevel', $('#bussiLevel').val());
        m_data.append('companyId', $('#companyId').val());
        m_data.append('customerEmailId', $('#upemail').val());
        m_data.append('orderRefNumber', $('#upcurrentrefnumber').val());
        m_data.append('orderNewNumber', $('#upnewrefnumber').val());
        m_data.append('customerNum', $('#upgrade_cust_num').val());
        m_data.append('refCustomerNum', $('#upgrade_ref_cust_num').val());
        m_data.append('companyId', $('#upgrade_compId').val())
        m_data.append('fsite_name', $('#upfsite').val());
        m_data.append('site_option', $('#upsite_crieteria').val());
        m_data.append('sel_sitename', $('#upsiteSelectPicker').val());
        
        m_data.append('skuVal', skuval);
        m_data.append('orderRefNumber', orderRef);
        m_data.append('csrfMagicToken', csrfMagicToken);
        
        $.ajax({
            url: 'addCustomerModel.php?function=updateRenew',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $("#upgrade_successMsg").css("color", "green").html(response.msg);
                $("#downloadLinkUrl2").val(response.link);
            },
            error: function(response) {
                $("#upgrade_successMsg").html("Error Occurred");
            }
        });
        
    }
}

function renewSubmit(){
    var isReqFieldsFilled = false;
    $('.req_ren').each(function() {
        var req_id = this.id;
        var field_id = req_id.replace("err_", "");
        var field_value = $("#" + field_id).val();
        if ($.trim(field_value) === "") {
            $("#err_" + field_id).css("color", "red").html(" required");
            isReqFieldsFilled = false;
            return false;
        } else if (field_id == "rennewrefnumber") {
            if (field_value.length < 7 || field_value.length > 16) {
                isReqFieldsFilled = false;
                $("#renew_successMsg").css("color", "red").html(" enter new order number between 7 to 16 characters");
                return false
            } else {
                $("#renew_successMsg").html("");
            }
        }else {
            isReqFieldsFilled = true;
        }
    });
    
    if($("#rensiteSelectPicker").val() === ""){
        if ($("#rensite_crieteria").val() === "") {
            $("#err_rensite_crieteria").css("color", "red").html(" required");
            isReqFieldsFilled = false;
            return false;
        } else if ($("#rensite_crieteria").val() === "friendly_name") {
            if ($("#renfsite").val() == "") {
                $("#err_renfsite").css("color", "red").html(" required");
                isReqFieldsFilled = false;
                return false;
            }else if ($("#renfsite").val().length < 3 || $("#renfsite").val().length > 15) {
                $("#renew_successMsg").css("color", "red").html(" Enter friendly name between 3 to 15 characters");
                isReqFieldsFilled = false;
            }else {
                $("#renew_successMsg").html("");
                isReqFieldsFilled = true;
            }
        }
    }
    
    if (isReqFieldsFilled) {
        var m_data = new FormData();
        var skuval = $("#renskuSelectPicker").val();
        var orderRef = $("#rennewrefnumber").val();
        m_data.append('customerFirstName', $('#renname').val());
        m_data.append('bussiLevel', $('#bussiLevel').val());
        m_data.append('companyId', $('#companyId').val());
        m_data.append('customerEmailId', $('#renemail').val());
        m_data.append('orderRefNumber', $('#rencurrentrefnumber').val());
        m_data.append('orderNewNumber', $('#rennewrefnumber').val());
        m_data.append('customerNum', $('#renew_cust_num').val());
        m_data.append('refCustomerNum', $('#renew_ref_cust_num').val());
        m_data.append('companyId', $('#renew_compId').val())
        m_data.append('fsite_name', $('#renfsite').val());
        m_data.append('site_option', $('#rensite_crieteria').val());
        m_data.append('sel_sitename', $('#rensiteSelectPicker').val());
        
        m_data.append('skuVal', skuval);
        m_data.append('orderRefNumber', orderRef);
        m_data.append('csrfMagicToken', csrfMagicToken);
        
        $.ajax({
            url: 'addCustomerModel.php?function=updateRenew',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $("#renew_successMsg").css("color", "green").html(response.msg);
                $("#downloadLinkUrl5").val(response.link);
            },
            error: function(response) {
                $("#renew_successMsg").html("Error Occurred");
            }
        });
        
    }
}

function show_revok_regen_li(downstat, revokstat) {
    $("#remote_option").show();
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
    var deviceId = $('#subscription_detail tbody tr:eq(0)')[0]['firstChild']['innerText'];
    if (deviceId === '') {
        $('#revoke_option').attr('data-bs-target', '#warning');
        return false;
    } else {
        $('#revoke_option').attr('data-bs-target', '#revoke_popup');
        var orderNum = $("#selectedOrder").val();
        var customerNum = $("#selectedCustomer").val();
        var details = fetchDetailsOfOrder(orderNum , customerNum);
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

function regenerateServicetag() {
    $("#regenerate_successMsg").html("");
    $("#downloadLinkUrl4").val("");
    var deviceId = $('#subscription_detail tbody tr:eq(0)')[0]['firstChild']['innerText'];
    if (deviceId === '') {
        $('#regenerate_option').attr('data-bs-target', '#warning');
        return false;
    } else {
        $('#regenerate_option').attr('data-bs-target', '#regenerate_popup');
        var orderNum = $("#selectedOrder").val();
        var customerNum = $("#selectedCustomer").val();
        var details = fetchDetailsOfOrder(orderNum , customerNum);
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

function remoteDiagnostics() {
    var id = $('#subscription_detail tbody tr.selected').attr('id')
    if (id === '' || id === undefined) {
        $('#remote_option').attr('data-bs-target', '#warning');
        return false;
    }else{
        var selected = $('#subscription_detail tbody tr.selected');
        var serviceTag = selected[0]['firstChild']['innerText'];
        location.href = "../support_action/index.php?servicetag=" + serviceTag + "&frm=entl";
    }
}

function detailSubmit() {
    var isReqFieldsFilled = false;
    
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
                $("#add_successMsg").css("color", "red").html(" enter reference number between 7 to 16 characters");
                return false
            } else {
                $("#add_successMsg" + field_id).html("");
            }
        } else if (field_id == "orderRefNumber") {
            if (field_value.length < 7 || field_value.length > 16) {
                isReqFieldsFilled = false;
                $("#add_successMsg").css("color", "red").html(" enter order number between 7 to 16 characters");
                return false
            } else {
                $("#add_successMsg").html("");
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
        m_data.append('customerFirstName', $('#name').val());
        m_data.append('bussiLevel', $('#bussiLevel').val());
        m_data.append('companyId', $('#companyId').val());
        m_data.append('name', $('#name').val());
        m_data.append('addr', $('#addr').val());
        m_data.append('city', $('#city').val());
        m_data.append('stprov', $('#stprov').val());
        m_data.append('zpcode', $('#zpcode').val());
        m_data.append('country', $('#country').val());
        m_data.append('customerNum', $('#selcustNum').val());
        m_data.append('refCustomerNum', $('#refCustomerNum').val());
        m_data.append('fname', $('#fname').val());
        m_data.append('lname', $('#lname').val());
        m_data.append('customerEmailId', $('#email').val());
        m_data.append('phnumber', $('#phnumber').val());
        m_data.append('selectedCid', $('#selectedCid').val());
        m_data.append('sel_sitename', $('#vsiteSelectPicker').val());

        m_data.append('skuVal', skuval);
        m_data.append('orderRefNumber', orderRef);

        m_data.append('customer_uplogo', $('input[name=customer_uplogo]')[0].files[0]);
        m_data.append('customer_appLogo', $('input[name=customer_appLogo]')[0].files[0]);
        addSubscription(m_data);
        return true;
    }

    
}

function limitedSubmit(){
    var isReqFieldsFilled = false;
    
    $('.vreq').each(function() {
        var req_id = this.id;
        var field_id = req_id.replace("err_", "");
        var field_value = $("#" + field_id).val();
        $("#err_" + field_id).html("");
        if ($.trim(field_value) === "") {
            $("#err_" + field_id).css("color", "red").html(" required");
            isReqFieldsFilled = false;
            return false;
        } else if (field_id == "vemail") {
            if (!validateEmailAddr(field_value)) {
                isReqFieldsFilled = false;
                $("#err_" + field_id).css("color", "red").html(" enter valid email");
                return false
            } else {
                $("#err_" + field_id).css("color", "red").html("");
            }
        } else if (field_id == "vrefnumber") {
            if (field_value.length < 7 || field_value.length > 16) {
                isReqFieldsFilled = false;
                $("#add_successMsg").css("color", "red").html(" enter order number between 7 to 16 characters");
                return false
            } else {
                $("#add_successMsg").html("");
            }
        }else {
            isReqFieldsFilled = true;
        }
    });
    
    if($("#vsiteSelectPicker").val() == ""){
         $("#err_vfsite").html("");
        if ($("#vsite_crieteria").val() == "") {
                $("#err_vsite_crieteria").css("color", "red").html(" required");
                isReqFieldsFilled = false;
        }else if ($("#vsite_crieteria").val() == "friendly_name") {
            if ($("#vfsite").val() == "") {
                $("#err_vfsite").css("color", "red").html(" required");
                isReqFieldsFilled = false;
            }else if ($("#vfsite").val().length < 3 || $("#vfsite").val().length > 15) {
                $("#add_successMsg").css("color", "red").html(" Enter name between 3 to 15 characters");
                isReqFieldsFilled = false;
            }else {
                $("#add_successMsg").html("");
                isReqFieldsFilled = true;
            }
        } else {
            isReqFieldsFilled = true;
        }
    }
    
    
    
    if (isReqFieldsFilled) {
        var m_data = new FormData();
        var skuval = $("#vskuSelectPicker").val();
        var orderRef = $("#vrefnumber").val();
        m_data.append('customerFirstName', $('#vname').val());
        m_data.append('bussiLevel', $('#bussiLevel').val());
        m_data.append('companyId', $('#companyId').val());
        m_data.append('customerNum', $('#selcustNum').val());
        m_data.append('refCustomerNum', $('#refCustomerNum').val());
        m_data.append('customerEmailId', $('#vemail').val());
        m_data.append('sel_sitename', $('#vsiteSelectPicker').val());
        m_data.append('phnumber', $('#vphnumber').val());
        m_data.append('site_option', $('#vsite_crieteria').val());
        m_data.append('fsite_name', $('#vfsite').val());
        m_data.append('skuVal', skuval);
        m_data.append('orderRefNumber', orderRef);
        addSubscription(m_data);
        return true;
    }

    
}

function veryLimitedSubmit(){
    var isReqFieldsFilled = false;
    
    $('.vlreq').each(function() {
        var req_id = this.id;
        var field_id = req_id.replace("err_", "");
        var field_value = $("#" + field_id).val();
        if ($.trim(field_value) === "") {
            $("#err_" + field_id).css("color", "red").html(" required");
            isReqFieldsFilled = false;
            return false;
        } else if (field_id == "vlemail") {
            if (!validateEmailAddr(field_value)) {
                isReqFieldsFilled = false;
                $("#err_" + field_id).css("color", "red").html(" enter valid email");
                return false
            } else {
                $("#err_" + field_id).css("color", "red").html("");
            }
        } else if (field_id == "vlrefnumber") {
            if (field_value.length < 7 || field_value.length > 16) {
                isReqFieldsFilled = false;
                $("#add_successMsg").css("color", "red").html(" enter order number between 7 to 16 characters");
                return false
            } else {
                $("#add_successMsg").css("color", "red").html("");
            }
        }else {
            isReqFieldsFilled = true;
        }
    });
    
    
    
    if($("#vsiteSelectPicker").val() == ""){
        $("#err_vlfsite").html("");
        if ($("#vlsite_crieteria").val() == "friendly_name") {
            if ($("#vlfsite").val() == "") {
                $("#err_vlfsite").css("color", "red").html(" required");
                isReqFieldsFilled = false;
            }else if ($("#vlfsite").val().length < 3 || $("#vlfsite").val().length > 15) {
                $("#add_successMsg").css("color", "red").html(" Enter name between 3 to 15 characters");
                isReqFieldsFilled = false;
                return false
            }else {
                $("#add_successMsg").html("");
                isReqFieldsFilled = true;
            }
        }
    }

    if (isReqFieldsFilled) {
        var m_data = new FormData();
        var skuval = $("#vlskuSelectPicker").val();
        var orderRef = $("#vlrefnumber").val();
        m_data.append('customerFirstName', $('#vlname').val());
        m_data.append('bussiLevel', $('#bussiLevel').val());
        m_data.append('companyId', $('#companyId').val());
        m_data.append('customerNum', $('#selcustNum').val());
        m_data.append('refCustomerNum', $('#refCustomerNum').val());
        m_data.append('customerEmailId', $('#vlemail').val());
        m_data.append('sel_sitename', $('#vlsiteSelectPicker').val());
        m_data.append('site_option', $('#vlfriendly_name').val());
        m_data.append('fsite_name', $('#vlfsite').val());
        m_data.append('skuVal', skuval);
        m_data.append('orderRefNumber', orderRef);
        addSubscription(m_data);
        return true;
    }

    
}

$("#addSubscriber").click(function (){
    $("#subcriber_heading").html("Add Subscriber");
    $("#downloadurl_option").hide();
    $('#downloadLinkUrl').val('');
    $('#selcustNum').val('');
    $('#add_successMsg').html('');
    
    $("#payment_for_sku").html("");
    $("#vpayment_for_sku").html("");
    $("#vlpayment_for_sku").html("");
    
    $('#customer_list .panel .panel-heading h2').attr('style', 'color: #595959 !important;');
    $('#customerForm .form-group').addClass('is-empty');
    $('#customerForm .form-group input').val('');
    
    $("#companyId").val(entityId);
    $("#bussiLevel").val(businessLevel);
    $("#vSite_List_Div").css("display","none");
    
    $("#vsite_crieteria").val("").change();
    $("#vlsite_crieteria").val("").change();
    $(".selectpicker").selectpicker("refresh");
    $(".selectpicker").selectpicker("refresh");
    $("#vskuSelectPicker").val("").change();
    $("#vlskuSelectPicker").val("").change();
    $(".selectpicker").selectpicker("refresh");
    $(".selectpicker").selectpicker("refresh");
    
    $("#vfSiteDiv").css("display","none");
    $("#vlfSiteDiv").css("display","none");
    $("#vlSite_List_Div").css("display","none");    
    $("#vsite_crieteria_div").css("display","block");    
    $("#vlsite_crieteria_div").css("display","block");  
    $("#addSubscription").hide();
    $("#payment_for_sku").html();

})


function addSubscription(m_data) {

    $("#add_successMsg").html('<img src="../images/ajax-login.gif" class="loadhome" alt="loading..." style="width:auto !important;padding-left:22% !important;"/>');
    $.ajax({
        url: 'addCustomerModel.php?function=addSubscriber' + "&csrfMagicToken=" + csrfMagicToken,
        data: m_data,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#add_successMsg").html(response.msg);
            $("#downloadLinkUrl").val(response.link);
            fetchCustomerList(entityId, customerType);
        },
        error: function(response) {
            $("#add_successMsg").html("Error Occurred");
        }
    });
    return true;
}

function setCustomerSubcription(customername, custnum , obj){
    $("#subcriber_heading").html("Add New Subscription");
    $("#downloadurl_option").show();
    $("#vsite_crieteria").val("company_name").change();
    $("#vlsite_crieteria").val("company_name").change();
    $(".selectpicker").selectpicker("refresh");
    $("#vskuSelectPicker").val("").change();
    $("#vlskuSelectPicker").val("").change();
    $(".selectpicker").selectpicker("refresh");
    
    $('#downloadLinkUrl').val('');
    $("#payment_for_sku").html('');
    $("#vpayment_for_sku").html('');
    $("#vlpayment_for_sku").html('');
    $('#add_successMsg').html('');
    
    $('.panel-body .nicescroll li a').attr('style', 'color: #595959 !important;');
    $('#customer_list .panel .panel-heading h2').attr('style', 'color: #595959 !important;');
    $(obj).attr('style', 'color: #48b2e4 !important');
    
    var m_data = "customerNo=" + custnum;
    
    $("#selcustNum").val(custnum); 
    $("#addSubscription").css("display","block");
    $("#detailed").css("display", "none");
    $("#limited").css("display", "none");
    $("#verylimited").css("display", "none");
    
    $.ajax({
        url: 'addCustomerModel.php?function=get_OrderDetails_CustomerNum&customerNo=' + custnum + "&csrfMagicToken=" + csrfMagicToken,
        data: m_data,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function(data) {
            var response = data.detail;
            var siteList = data.siteList;
            $('#customerForm .form-group').removeClass('is-empty');
            if(response.ordergen === 'Detailed'){
                $("#detailed").css("display", "block");
                $("#limited").css("display", "none");
                $("#verylimited").css("display", "none");
                $("#vlSite_List_Div").css("display","none");
                $("#vSite_List_Div").css("display","none");
                setDetailedValues(response);
            } else if (response.ordergen === 'Limited') {
                $("#detailed").css("display", "none");
                $("#limited").css("display", "block");
                $("#verylimited").css("display", "none");
                $("#vSite_List_Div").css("display","block");
                $("#vlSite_List_Div").css("display","none");
                setLimitedValues(response , siteList);
            } else if (response.ordergen === 'VeryLimited') {
                $("#detailed").css("display", "none");
                $("#limited").css("display", "none");
                $("#verylimited").css("display", "block");
                $("#vlSite_List_Div").css("display","block");
                $("#vSite_List_Div").css("display","none");
                setVeryLimitedValues(response , siteList);
            }
        },
        error: function(response) {
            $("#add_successMsg").html("Error Occurred");
        }
    });
}

function setDetailedValues(jsonResult){
    $("#skuSelectPicker").val();
    $("#orderRefNumber").val('');
    $('#name').val(jsonResult.coustomerFirstName);
    $('#bussiLevel').val(jsonResult.businessLevel);
    $('#addr').val(jsonResult.address);
    $('#city').val(jsonResult.city);
    $('#stprov').val(jsonResult.province);
    $('#zpcode').val(jsonResult.zipCode);
    
    if(jsonResult.coustomerCountry === ""){
        $('#country option[value=""]').attr('selected','selected');
    }else{
        $('#country option[value='+jsonResult.coustomerCountry+']').attr('selected','selected');
    }
    
    $(".selectpicker").selectpicker("refresh");
    $('#fname').val(jsonResult.coustomerFirstName);
    $('#lname').val(jsonResult.coustomerLastName);
    $('#email').val(jsonResult.emailId);
    $('#phnumber').val(jsonResult.phoneNo);
    $('#refnumber').val(jsonResult.customerNum);
    $('#companyId').val(jsonResult.compId);
    $("#selcustNum").val(jsonResult.customerNum);
    $("#refCustomerNum").val(jsonResult.refCustomerOrder);
    $('input[name=customer_uplogo]');
    $('input[name=customer_appLogo]');
}

function setLimitedValues(jsonResult , siteList) {
    $("#vsiteSelectPicker").html(siteList);
    $(".selectpicker").selectpicker("refresh");
    $("#vrefnumber").val('');
    $('#vname').val(jsonResult.coustomerFirstName);
    $('#bussiLevel').val(jsonResult.businessLevel);
    $('#companyId').val(jsonResult.compId);
    $('#vrefnumber').val();
    $('#vemail').val(jsonResult.emailId);
    $('#vphnumber').val(jsonResult.phoneNo);
    $("#selcustNum").val(jsonResult.customerNum);
    $("#refCustomerNum").val(jsonResult.refCustomerOrder);
}

function setVeryLimitedValues(jsonResult, siteList) {
    $("#vlsiteSelectPicker").html(siteList);
    $(".selectpicker").selectpicker("refresh");
    $("#selcustNum").val(jsonResult.customerNum);
    $("#refCustomerNum").val(jsonResult.refCustomerOrder);
    $('#companyId').val(jsonResult.compId);
    $("#vlrefnumber").val('');
    $('#vlname').val(jsonResult.coustomerFirstName);
    $('#bussiLevel').val(jsonResult.businessLevel);
    $('#vlrefnumber').val();
    $('#vlemail').val(jsonResult.emailId);
    $('#vlfriendly_name').val();
    $('#vlfsite').val();
}