$(document).ready(function(){
    
    $('[data-toggle="tooltip"]').tooltip();
    
     var h = window.innerHeight;
    if(h>700){
        $("#user-table").attr("data-page-length","16");
    }
    else{
        $("#user-table").attr("data-page-length","8");
    }
    
     $('#userto').multiselect({
        includeSelectAllOption: true
    });

    $("#userto").multiselect('selectAll', false);
    $("#userto").multiselect('updateButtonText');
    
});
drawUserTable();

function drawUserTable() {
    selectedCID = $("#selectedCID").val();
    $("#user-table").dataTable().fnDestroy();
    table = $('#user-table').DataTable({
        scrollY: 400,
        autoWidth: true,
        paging : true,
        searching : true,
        processing: false,
        serverSide: true,
        ajax: {
            url: "addCustomerModel.php?function=userListGrid&cId=" + selectedCID,
            type: "POST",
            rowId: 'id',
        },
        columns: [
            {"data": "username"},
            {"data": "user_email"},
            {"data": "user_phone_no"},
            {"data": "role_id"},
            {"data": "userStatus"},
            {"data": "userDetail"},
        ],
        columnDefs: [
            {className: "dt-left", "targets": [0, 1, 2, 3, 4, 5]},
        ],
        bInfo: false,
        responsive: true,
        dom: '<"user-table-list"<"top"f>rt<"bottomtable"lpi><"clear">>',
        scrollCollapse: true,
        bLengthChange: true,
        select: true,
        "lengthMenu": [[25, 50, -1], [25, 50, "All"]],
    });
    $('#user-table tbody').on( 'mouseover', 'td', function () {
            var rowID = table.row(this).data();
//            $("#DeviceTypeData tbody tr td").eq(0).attr("data-target","");
            $("#user-table tbody tr td").eq(0).attr("data-target","tooltip");
            $("#user-table tbody tr td").eq(1).attr("data-target","tooltip");
            $("#user-table tbody tr td").eq(2).attr("data-target","tooltip");
            $("#user-table tbody tr td").eq(3).attr("data-target","tooltip");
            $("#user-table tbody tr td").eq(4).attr("data-target","tooltip");
//            $("#user-table tbody tr td").eq(5).attr("data-target","tooltip");
//            $("td:nth-child(1)").attr("title",""+rowID.status);
            $("td:nth-child(1)").attr("title",""+rowID.username);
            $("td:nth-child(2)").attr("title",""+rowID.user_email);
            $("td:nth-child(3)").attr("title",""+rowID.user_phone_no);
            $("td:nth-child(4)").attr("title",""+rowID.role_id);
            $("td:nth-child(5)").attr("title",""+rowID.userStatus);
//            $("td:nth-child(6)").attr("title",""+rowID.userDetail);
    });
    $("#user-table_length").hide();
    $('#user-table_filter').hide();
    $("#user-list_searchbox").keyup(function() {
        table.search(this.value).draw();
        });

}

$('#user-table tbody').on('click', 'tr', function() {
    table.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
    var rowdata = table.row(this).data();
    //$("#selected").val(rowdata.id);
    username = rowdata.username;
    //$("#selectedUser").val(rowdata.username);
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

function validateName(name) {
    var nameFilter = /^[a-zA-Z]+$/;;
    if (nameFilter.test(name)) {
        return true;
    } else {
        return false;
    }
}



function addAdvUser() {
   
    isReqFieldsFilled = false;
    $("#addsuccessMsg").html("");
    $(".req1").html("*");
    $('.req1').each(function() {
        
        var req_id = this.id;
        var field_id = req_id.replace("err_", "");
        var field_value = $("#" + field_id).val();
        if ($.trim(field_value) === "") {
            $("#err_" + field_id).css("color", "red").html(" *required");
            isReqFieldsFilled = false;
            return false;
        } else if (field_id == "advuser_email") {
            if (!validateEmailAddr(field_value)) {
                $("#addsuccessMsg").css("color", "red").html(" Enter valid email");
                isReqFieldsFilled = false;
                return false;
            }
        } else if (field_id == "advusername") {
            if (field_value.length < 3 || field_value.length > 20) {
                $("#addsuccessMsg").css("color", "red").html(" First name should be minimum 3 and maximum 20 characters");
                isReqFieldsFilled = false;
                return false;
            }else if(!validateName(field_value)){
              $("#addsuccessMsg").css("color", "red").html(" Please enter only letters");
                isReqFieldsFilled = false;
                return false; 
            }
        } else if (field_id == "last_name") {
            if (field_value.length < 1 || field_value.length > 20) {
                $("#addsuccessMsg").css("color", "red").html(" Last name should be minimum 1 and maximum 20 characters");
                isReqFieldsFilled = false;
                return false;
            }else if(!validateName(field_value)){
               $("#addsuccessMsg").css("color", "red").html(" Please enter only letters");
                isReqFieldsFilled = false;
                return false; 
            }
        } else {
            isReqFieldsFilled = true;
        }
    });

    if (isReqFieldsFilled == true) {

        agentEmail      = $("#advuser_email").val();
        agentName       = $("#advusername").val();
        agentLastName   = $("#last_name").val();
        roleId          = $("#advroleId").val();
        agentCorpId     = $("#advuser_corp_id").val();
        userto          = $("#userto").val();
        leveltoid       = $("#leveltoid").val();
        siteVal         = $("#site_list").val();
        entity_id       = $("#entity_list").val();
        channel_id      = $("#channel_list").val();
        subchannel_id   = $("#subchannel_list").val();
        customer_id     = $("#customer_list").val();
        
        var userType = 1;
        
        selVal1 = '';
        selVal2 ='';
        selVal3 ='';
        selVal4 ='';
        selVal5 ='';
        
        if(entity_id == 'all'){
            
            $.each($("#entity_list option"), function(){ 
                if($(this).val() != 'all'){
                    selVal1 +=$(this).val()+ ',';
                }
            });
            entity_id = selVal1.slice(0, -1);
         }
        
        
        
        
        if(channel_id == 'all'){
            
            $.each($("#channel_list option"), function(){ 
            
                if ($(this).val() != 'all') {
                    selVal2 += $(this).val() + ',';
                }
            });
            
            channel_id = selVal2.slice(0, -1);
            
         }
        
        
        
        if(subchannel_id == 'all'){
            $.each($("#subchannel_list option"), function()
            {
                if ($(this).val() != 'all') {
                    selVal3 += $(this).val() + ',';
                }
            });
            subchannel_id = selVal3.slice(0, -1);
       }
        
        
               
        if(customer_id == 'all'){
            $.each($("#customer_list option"), function()
            {
                if ($(this).val() != 'all') {
                    selVal4 += $(this).val() + ',';
                }
            });
            customer_id = selVal4.slice(0, -1);
         }
         
        if(siteVal=== '' || siteVal === 'Not Available' || siteVal === null || siteVal === 'null'){
            $("#addsuccessMsg").css("color", "red").html("Please select site");
            return false;
        } else {
            $("#addsuccessMsg").html("");
            if (siteVal == 'all') {
                userType = 0;
                $.each($("#site_list option"), function()
                {
                    if ($(this).val() != 'all') {
                        selVal5 += $(this).val() + ',';
                    }
                });
                siteVal = selVal5.slice(0, -1);
            }

        }
        
        
        
        $("#addsuccessMsg").html('<img src="../images/ajax-login.gif" class="loadhome" alt="loading..." style="width:auto !important;"/>');
        $.ajax({
            type: "GET",
            url: "addCustomerModel.php",
            data: "function=createAdvUser&userEmail=" + agentEmail + "&userName=" + agentName + "&lastname=" + agentLastName + "&roleId=" + roleId + "&agentCorpId=" + agentCorpId + "&sitelist=" + siteVal+"&entityId="+entity_id+"&channelId="+channel_id+"&subchannelId="+subchannel_id+"&customerId="+customer_id+"&userType="+userType + "&csrfMagicToken=" + csrfMagicToken,
            success: function(msg) {
                msg = $.trim(msg);
                $("#loaderimg").hide();
                if (msg === 'DONE') {
                    $('#addsuccessMsg').css("color","green").html('User created successfully.Confirmation email has been sent to the created user.');
                    setTimeout(function(){
                        location.reload();
                    }, 3000);
                } else if (msg === 'DUPLICATE') {
                    $('#addsuccessMsg').css("color","red").html('Email id already exists.');
                } else {
                    $('#addsuccessMsg').css("color","red").html('Email not Sent please try later.');
                }
                drawUserTable();
            }
        });
    }
}

function editUser(selectedId) {
    $('#edit_user input[type=text]').prev().parent().removeClass('is-empty');
    $("#edit_userid").val(selectedId);
    var m_data = "userid=" + selectedId + "&csrfMagicToken=" + csrfMagicToken;
    $.ajax({
        url: "addCustomerModel.php?function=getUserDetails",
        data: m_data,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            
            var userdetailsArray = response.split("##");
//            console.log(userdetailsArray);
            $("#edit_advusername").val(userdetailsArray[0].trim());
            $("#edit_last_name").val(userdetailsArray[10].trim());
            $("#edit_advuser_email").val(userdetailsArray[1].trim());
            $("#edit_advuser_corp_id").val(userdetailsArray[2].trim());
            $("#edit_advroleId").html(userdetailsArray[3]);
            var selectedUserChId = userdetailsArray[9];
            var selectedUserCtype = userdetailsArray[4];
            switch (selectedUserCtype) {
                case '0':
                    $('#edit_entity_list').html(userdetailsArray[5]);
                    $("#edit_channel_list").html(userdetailsArray[6]);
                    $("#edit_subchannel_list").html(userdetailsArray[7]);
                    $("#edit_customer_list").html(userdetailsArray[8]);
                    removeDuplicate('edit_entity_list');
                    removeDuplicate('edit_channel_list');
                    removeDuplicate('edit_subchannel_list');
                    removeDuplicate('edit_customer_list');
                    $(".selectpicker").selectpicker('refresh');
                    break;
                case '1':
                    $('#edit_entity_list_div').hide();
                    $("#edit_channel_list").html(userdetailsArray[6]);
                    $("#edit_subchannel_list").html(userdetailsArray[7]);
                    $("#edit_customer_list").html(userdetailsArray[8]);
                    removeDuplicate('edit_entity_list');
                    removeDuplicate('edit_channel_list');
                    removeDuplicate('edit_subchannel_list');
                    removeDuplicate('edit_customer_list');
                    $(".selectpicker").selectpicker('refresh');
                    break;
                case '2':
                    $('#edit_entity_list_div').hide();
                    $('#edit_channel_list_div').hide();
                    $("#edit_subchannel_list").html(userdetailsArray[7]);
                    $("#edit_customer_list").html(userdetailsArray[8]);
                    removeDuplicate('edit_subchannel_list');
                    removeDuplicate('edit_customer_list');
                    $(".selectpicker").selectpicker('refresh');
                    break;
                case '3':
                    $('#edit_entity_list_div').hide();
                    $('#edit_channel_list_div').hide();
                    $('#edit_subchannel_list_div').hide();
                    $("#edit_customer_list").html(userdetailsArray[8]);
                    removeDuplicate('edit_customer_list');
                    $(".selectpicker").selectpicker('refresh');
                    break;
                case '4':

                    break;
                case '5':
                    $('#edit_entity_list_div').hide();
                    $('#edit_channel_list_div').hide();
                    $('#edit_subchannel_list_div').hide();
                    $('#edit_customer_list_div').hide();
                    $(".selectpicker").selectpicker('refresh');
                    break;
                default:
                    break;
            }
            edit_getAllSitesForSelUser(userdetailsArray[0].trim(),selectedUserChId);
        },
        error: function(response) {

        }
    });
}

function updateAdvUser() {
    var selectedid = $("#edit_userid").val();
    $("#editsuccessMsg").html("");
    $(".edit_req1").html("*");
    isReqFieldsFilled = false;
    $('.edit_req1').each(function() {
        var req_id = this.id;
        var field_id = req_id.replace("err_", "");
        var field_value = $("#" + field_id).val();
        if ($.trim(field_value) === "") {
            $("#err_" + field_id).css("color", "red").html(" *required");
            isReqFieldsFilled = false;
            return false;
        }  else if (field_id == "edit_advusername") {
            if (field_value.length < 3 || field_value.length > 20) {
                $("#editsuccessMsg").css("color", "red").html(" First name should be minimum 3 and maximum 20 characters");
                isReqFieldsFilled = false;
                return false;
            }else if(!validateName(field_value)){
              $("#editsuccessMsg").css("color", "red").html(" Please enter only letters");
                isReqFieldsFilled = false;
                return false; 
            }
        } else if (field_id == "edit_last_name") {
            if (field_value.length < 1 || field_value.length > 20) {
                $("#editsuccessMsg").css("color", "red").html(" Last name should be minimum 1 and maximum 20 characters");
                isReqFieldsFilled = false;
                return false;
            }else if(!validateName(field_value)){
               $("#editsuccessMsg").css("color", "red").html(" Please enter only letters");
                isReqFieldsFilled = false;
                return false; 
            }
        } else if (field_id == "edit_advuser_email") {
            if (!validateEmailAddr(field_value)) {
                $("#err_" + field_id).css("color", "red").html(" enter valid email");
                isReqFieldsFilled = false;
                return false;
            }
        } else {
            isReqFieldsFilled = true;
        }
    });

    if (isReqFieldsFilled == true) {

        agentEmail      = $("#edit_advuser_email").val();
        agentName       = $("#edit_advusername").val();
        agentLastName   = $("#edit_last_name").val();
        roleId          = $("#edit_advroleId").val();
        agentCorpId     = $("#edit_advuser_corp_id").val();
        userto          = $("#edit_userto").val();
        leveltoid       = $("#edit_leveltoid").val();
        siteVal         = $("#edit_site_list").val();
        entity_id       = $("#edit_entity_list").val();
        channel_id      = $("#edit_channel_list").val();
        subchannel_id   = $("#edit_subchannel_list").val();
        customer_id     = $("#edit_customer_list").val();
        
        var userType = 1;
        
        selVal1 = '';
        selVal2 ='';
        selVal3 ='';
        selVal4 ='';
        selVal5 ='';
        
        if(entity_id == 'all'){
            
            $.each($("#edit_entity_list option"), function(){ 
                if($(this).val() != 'all'){
                    selVal1 +=$(this).val()+ ',';
                }
            });
            entity_id = selVal1.slice(0, -1);
         }
        
        
        
        
        if(channel_id == 'all'){
            
            $.each($("#edit_channel_list option"), function(){ 
            
                if ($(this).val() != 'all') {
                    selVal2 += $(this).val() + ',';
                }
            });
            
            channel_id = selVal2.slice(0, -1);
            
         }
        
        
        
        if(subchannel_id == 'all'){
            $.each($("#edit_subchannel_list option"), function()
            {
                if ($(this).val() != 'all') {
                    selVal3 += $(this).val() + ',';
                }
            });
            subchannel_id = selVal3.slice(0, -1);
       }
        
        
               
        if(customer_id == 'all'){
            $.each($("#edit_customer_list option"), function()
            {
                if ($(this).val() != 'all') {
                    selVal4 += $(this).val() + ',';
                }
            });
            customer_id = selVal4.slice(0, -1);
         }
         
        if(siteVal=== '' || siteVal === 'Not Available' || siteVal === null || siteVal === 'null'){
            $("#editsuccessMsg").css("color", "red").html("Please select site");
            return false;
        } else {
            $("#editsuccessMsg").html("");
            if (siteVal == 'all') {
                userType = 0;
                $.each($("#edit_site_list option"), function()
                {
                    if ($(this).val() != 'all') {
                        selVal5 += $(this).val() + ',';
                    }
                });
                siteVal = selVal5.slice(0, -1);
            }

        }
        
        
        
        $("#editsuccessMsg").html('<img src="../images/ajax-login.gif" class="loadhome" alt="loading..." style="width:auto !important;"/>');
        $.ajax({
            type: "GET",
            url: "addCustomerModel.php",
            data: "function=updateAdvUser&userid="+selectedid+"&userEmail=" + agentEmail + "&userName=" + agentName + "&lastname=" + agentLastName + "&roleId=" + roleId + "&agentCorpId=" + agentCorpId + "&sitelist=" + siteVal+"&entityId="+entity_id+"&channelId="+channel_id+"&subchannelId="+subchannel_id+"&customerId="+customer_id+"&userType="+userType + "&csrfMagicToken=" + csrfMagicToken,
            success: function(msg) {
                msg = $.trim(msg);
                $("#loaderimg").hide();
                if (msg === 'DONE') {
                    $('#editsuccessMsg').html('User updated successfully');
                    setTimeout(function(){
                        location.reload();
                    }, 3000);
                }
                drawUserTable();
            }
        });
    }
}

function updateUser() {
    var userId          = $('#user-table tbody tr.selected').attr('id');
    var siteVal         = $("#esiteId").val();
    var username        = $("#eusername").val();
    var role_id         = $("#eroleId").val();
    var user_email      = $("#euser_email").val();
    var userAgentCorpId = $("#euser_corp_id").val();
    
    m_data = "&uid=" + userId + "&userName=" + username + "&roleId=" + role_id + "&userEmail=" + user_email + "&editagentCorpId=" + userAgentCorpId + "&sitelist=" + siteVal + "&csrfMagicToken=" + csrfMagicToken;
    $.ajax({
        url: "addCustomerModel.php?function=updateUser",
        data: m_data,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            $("#esuccessMsg").html(response);
            setTimeout(function(){
                location.reload();
            }, 2000);
        },
        error: function(response) {
            $("#esuccessMsg").html("Error Occurred");
        }
    });


}

function deleteUser() {
    var userId = $('#user-table tbody tr.selected').attr('id');
    $("#dsuccessMsg").html('<img src="../images/ajax-login.gif" class="loadhome" alt="loading..." style="width:auto !important;"/>');
    var m_data = "";
    $.ajax({
        url: "addCustomerModel.php?function=deleteUser&uid=" + userId + "&csrfMagicToken=" + csrfMagicToken,
        data: m_data,
        type: 'POST',
        dataType: 'text',
        success: function(response) {
            $("#deleteYesButton").hide();
            $("#deleteNoButton").hide();
            $("#dsuccessMsg").html(response);
            setTimeout(function(){
                location.reload();
            }, 3000);
        },
        error: function(response) {
            $("#dsuccessMsg").html("Error Occurred");
        }
    });
    //$(".fclose").click();
    drawUserTable();

}

function selectConfirm(data_target_id) {
    var selected = $('#user-table tbody tr.selected').attr('id');
    if (selected === '' || selected === undefined) {
        $('#' + data_target_id).attr('data-bs-target', '#warning');
    } else {
        if (data_target_id === 'editUserId') {
            $('#' + data_target_id).attr('data-bs-target', '#edit_user');
            editUser(selected);
        } else if (data_target_id === 'deleteUserId') {
            $('#' + data_target_id).attr('data-bs-target', '#delete_user');
        }
    }
    return true;
}

function exportUserDetails() {
    window.location.href = "addCustomerModel.php?function=exportUserDetails";
}

function addadvuser(){
    $('#advdialog-form').css({'display':'block'});
    $('#dialog-form').css({'display':'none'});
    $('#norUserBtn').hide();
    $('#advUserBtn').show();
    
}

function getChannelList(){
    var selVal = $('#entity_list').val();
    var selVal1 = '';
    if (selVal == null) {
        $('#entity_list option').attr("disabled", false);
        $('#channel_list').html("");
    } else if (selVal == "all") {
        $('#entity_list option[value!="all"]').attr("disabled", true);
        $("#entity_list option").each(function()
        {
            if ($(this).val() != "all") {
                selVal1 += $(this).val() + ',';
            }
        });
        selVal = selVal1.slice(0, -1);
    } else if (selVal != "all") {
        $('#entity_list option[value!="all"]').attr("disabled", false);
        $('#entity_list option[value="all"]').attr("disabled", true);
    }
    $(".selectpicker").selectpicker("refresh");
    if (selVal != null) {
        $.ajax({
            type: "GET",
            url: "addCustomerModel.php",
            data: "function=userChannelList&ctype=1&selectedValues=" + selVal + "&csrfMagicToken=" + csrfMagicToken,
            success: function(msg) {
                msg = $.trim(msg);
                msgArray = msg.split("##");
                $('#channel_list').html(msgArray[0]);
                var tempArray = msgArray[1].split("@@");
                if($('#customer_list').text().indexOf('Not Available') != -1){
                    if(tempArray[1] != 0){
                        if(tempArray[0].indexOf('Not Available') != -1){
                            
                        }else{
                            $('#customer_list').html(tempArray[0]);
                        }
                        
                    }
                }else{
                    var str = tempArray[0].replace("<option value='all'>All</option>", "");
                    if(tempArray[1] != 0){
                       $('#customer_list').append(str);
                    }
                 }
                 
                removeDuplicate('customer_list');
                getSubChannelList();
                $(".selectpicker").selectpicker("refresh");
                setEntitySiteOptionsList(selVal);
            }
        });
    }

}

function getSubChannelList() {
    var selVal = $('#channel_list').val();
    var selVal1 = '';
    if (selVal == null) {
        $('#channel_list option').attr("disabled", false);
        $('#subchannel_list').html("");
    } else if (selVal == "all") {
        $('#channel_list option[value!="all"]').attr("disabled", true);
        $("#channel_list option").each(function()
        {
            if ($(this).val() != "all") {
                selVal1 += $(this).val() + ',';
            }
        });
        selVal = selVal1.slice(0, -1);

    } else if (selVal != "all") {
        $('#channel_list option[value!="all"]').attr("disabled", false);
        $('#channel_list option[value="all"]').attr("disabled", true);
    }
    $(".selectpicker").selectpicker("refresh");
    if (selVal != null) {
        $.ajax({
            type: "GET",
            url: "addCustomerModel.php",
            data: "function=userChannelList&ctype=2&&selectedValues=" + selVal + "&csrfMagicToken=" + csrfMagicToken,
            success: function(msg) {
                msgArray = msg.split("##");
                $('#subchannel_list').html(msgArray[0]);
                var tempArray = msgArray[1].split("@@");
                if($('#customer_list').text().indexOf('Not Available') != -1){
                    if(tempArray[1] != 0){
                        $('#customer_list').append(tempArray[0]);
                    }
                }else{
                    var str = tempArray[0].replace("<option value='all'>All</option>", "");
                    if(tempArray[1] != 0){
                       $('#customer_list').append(str);
                    }
                 }
                 
                removeDuplicate('customer_list');
                manipulateSubchannelList()
                $(".selectpicker").selectpicker("refresh");
                setEntitySiteOptionsList(selVal);
                
            }
        });
    }

}

function manipulateSubchannelList() {
    var selVal = $('#subchannel_list').val();
    var selVal1 = '';
    if (selVal == null) {
        $('#subchannel_list option').attr("disabled", false);
    } else if (selVal == "all") {
        $('#subchannel_list option[value!="all"]').attr("disabled", true);
        $("#subchannel_list option").each(function()
        {
            if ($(this).val() != "all") {
                selVal1 += $(this).val() + ',';
            }
        });
        selVal = selVal1.slice(0, -1);
    } else if (selVal != "all") {
        $('#subchannel_list option[value!="all"]').attr("disabled", false);
        $('#subchannel_list option[value="all"]').attr("disabled", true);
    }
    $(".selectpicker").selectpicker("refresh");
    
    if (selVal != null) {
        $.ajax({
            type: "GET",
            url: "addCustomerModel.php",
            data: "function=userChannelList&ctype=3&&selectedValues=" + selVal + "&csrfMagicToken=" + csrfMagicToken,
            success: function(msg) {
                msgArray = msg.split("##");
                var tempArray = msgArray[1].split("@@");
                if ($('#customer_list').text().indexOf('Not Available') != -1) {
                    if (tempArray[1] != 0) {
                        $('#customer_list').html(tempArray[0]);
                    }
                } else {
                    var str = tempArray[0].replace("<option value='all'>All</option>", "");
                    if (tempArray[1] != 0) {
                        $('#customer_list').append(str);
                    }
                }
                 removeDuplicate('customer_list');
                $(".selectpicker").selectpicker("refresh");
                setEntitySiteOptionsList(selVal);
            }
        });
    }
}

function setEntitySiteOptionsList(selVal){
    if (selVal != null) {
        $.ajax({
            type: "GET",
            url: "addCustomerModel.php",
            data: "function=customersSiteList&selectedValues=" + selVal + "&csrfMagicToken=" + csrfMagicToken,
            success: function(msg) {
                msg = $.trim(msg);
                $('#site_list').append(msg);
                removeDuplicate('site_list');
            }
        });
    }
}

function setSiteOptionsList(){
    var selVal = $('#customer_list').val();
    var selVal1 = '';
    if (selVal == null) {
        $('#customer_list option').attr("disabled", false);
    } else if (selVal == "all") {
        $('#customer_list option[value!="all"]').attr("disabled", true);
        $("#customer_list option").each(function()
        {
            if ($(this).val() != "all") {
                selVal1 += $(this).val() + ',';
            }
        });
        selVal = selVal1.slice(0, -1);
    } else if (selVal != "all") {
        $('#customer_list option[value!="all"]').attr("disabled", false);
        $('#customer_list option[value="all"]').attr("disabled", true);
    }
    $(".selectpicker").selectpicker("refresh");
    setEntitySiteOptionsList(selVal);
}

function selectSiteOption(){
    var selVal = $('#site_list').val();
    if (selVal == null) {
        $('#site_list option').attr("disabled", false);
    } else if (selVal == "all") {
        $('#site_list option[value!="all"]').attr("disabled", true);
    } else if (selVal != "all") {
        $('#site_list option[value!="all"]').attr("disabled", false);
        $('#site_list option[value="all"]').attr("disabled", true);
    }
}

function resetSiteListOptions(){
    $('#site_list option').attr("disabled", false);
    $("#site_list > option").attr("selected",false);
}

function setCustomerText() {
    var selVal = $('#customer_list').val();
    var selVal1 = '';
    if (selVal == null) {
        $("#customer_list_text").html('');
        $('#customer_list option').attr("disabled", false);
    } else if (selVal == "all") {
        $('#customer_list option[value!="all"]').attr("disabled", true);
    } else if (selVal != "all") {
        $('#customer_list option[value!="all"]').attr("disabled", false);
        $('#customer_list option[value="all"]').attr("disabled", true);
    }
    $(".selectpicker").selectpicker("refresh");
    if (selVal != null) {
        $("#customer_list_text").html('');
        if (selVal == 'all') {
            $("#customer_list option").each(function()
            {
                if ($(this).val() != "all") {
                    $("#customer_list_text").append($(this).text());
                    $("#customer_list_text").append("\n");
                }
            });
        } else {
            $('#customer_list :selected').each(function(i, selected) {
                $("#customer_list_text").append($(selected).text());
                $("#customer_list_text").append("\n");
            });
        }
    }
}

function removeDuplicate(select_id){
    
    var seen = {};
    $('#'+select_id+' option').each(function() {
        var txt = $(this).text();
        if (seen[txt])
            $(this).remove();
        else
            seen[txt] = true;
    });
}

function edit_getChannelList(){
    var userId = $('#user-table tbody tr.selected').attr('id');
    var selVal = $('#edit_entity_list').val();
    var selVal1 = '';
    if (selVal == null) {
        $('#edit_entity_list option').attr("disabled", false);
        $('#edit_channel_list').html("");
    } else if (selVal == "all") {
        $('#edit_entity_list option[value!="all"]').attr("disabled", true);
        $("#edit_entity_list option").each(function()
        {
            if ($(this).val() != "all") {
                selVal1 += $(this).val() + ',';
            }
        });
        selVal = selVal1.slice(0, -1);
    } else if (selVal != "all") {
        $('#edit_entity_list option[value!="all"]').attr("disabled", false);
        $('#edit_entity_list option[value="all"]').attr("disabled", true);
    }
    $(".selectpicker").selectpicker("refresh");
    if (selVal != null) {
        $.ajax({
            type: "GET",
            url: "addCustomerModel.php",
            data: "function=edit_userChannelList&ctype=1&&selectedValues=" + selVal + "&userid=" + userId + "&csrfMagicToken=" + csrfMagicToken,
            success: function(msg) {
                msg = $.trim(msg);
                msgArray = msg.split("##");
                $('#edit_channel_list').html(msgArray[0]);
                var tempArray = msgArray[1].split("@@");
                if($('#edit_customer_list').text().indexOf('Not Available') != -1){
                    if(tempArray[1] != 0){
                        if(tempArray[0].indexOf('Not Available') != -1){
                            
                        }else{
                            $('#edit_customer_list').html(tempArray[0]);
                        }
                        
                    }
                }else{
                    var str = tempArray[0].replace("<option value='all'>All</option>", "");
                    if(tempArray[1] != 0){
                       $('#edit_customer_list').append(str);
                    }
                 }
                 
                removeDuplicate('edit_customer_list');
                getSubChannelList();
                $(".selectpicker").selectpicker("refresh");
                edit_setEntitySiteOptionsList(selVal);
            }
        });
    }

}

function edit_getSubChannelList() {
    $('#edit_user input[type=text]').prev().parent().removeClass('is-empty');
    var userId = $('#user-table tbody tr.selected').attr('id');
    var selVal = $('#edit_channel_list').val();
    var selVal1 = '';
    if (selVal == null) {
        $('#edit_channel_list option').attr("disabled", false);
        $('#edit_subchannel_list').html("");
    } else if (selVal == "all") {
        $('#edit_channel_list option[value!="all"]').attr("disabled", true);
        $("#edit_channel_list option").each(function()
        {
            if ($(this).val() != "all") {
                selVal1 += $(this).val() + ',';
            }
        });
        selVal = selVal1.slice(0, -1);

    } else if (selVal != "all") {
        $('#edit_channel_list option[value!="all"]').attr("disabled", false);
        $('#edit_channel_list option[value="all"]').attr("disabled", true);
    }
    $(".selectpicker").selectpicker("refresh");
    if (selVal != null) {
        $.ajax({
            type: "GET",
            url: "addCustomerModel.php",
            data: "function=edit_userChannelList&ctype=2&&selectedValues=" + selVal + "&userid=" + userId + "&csrfMagicToken=" + csrfMagicToken,
            success: function(msg) {
                msgArray = msg.split("##");
                $('#edit_subchannel_list').html(msgArray[0]);
                var tempArray = msgArray[1].split("@@");
                if($('#edit_customer_list').text().indexOf('Not Available') != -1){
                    if(tempArray[1] != 0){
                        $('#edit_customer_list').append(tempArray[0]);
                    }
                }else{
                    var str = tempArray[0].replace("<option value='all'>All</option>", "");
                    if(tempArray[1] != 0){
                       $('#edit_customer_list').append(str);
                    }
                 }
                 
                removeDuplicate('edit_customer_list');
                manipulateSubchannelList()
                $(".selectpicker").selectpicker("refresh");
                edit_setEntitySiteOptionsList(selVal);
                
            }
        });
    }

}

function edit_manipulateSubchannelList() {
    var userId = $('#user-table tbody tr.selected').attr('id');
    var selVal = $('#edit_subchannel_list').val();
    var selVal1 = '';
    if (selVal == null) {
        $('#edit_subchannel_list option').attr("disabled", false);
    } else if (selVal == "all") {
        $('#edit_subchannel_list option[value!="all"]').attr("disabled", true);
        $("#edit_subchannel_list option").each(function()
        {
            if ($(this).val() != "all") {
                selVal1 += $(this).val() + ',';
            }
        });
        selVal = selVal1.slice(0, -1);
    } else if (selVal != "all") {
        $('#edit_subchannel_list option[value!="all"]').attr("disabled", false);
        $('#edit_subchannel_list option[value="all"]').attr("disabled", true);
    }
    $(".selectpicker").selectpicker("refresh");
    
    if (selVal != null) {
        $.ajax({
            type: "GET",
            url: "addCustomerModel.php",
            data: "function=edit_userChannelList&ctype=3&&selectedValues=" + selVal + "&userid=" + userId + "&csrfMagicToken=" + csrfMagicToken,
            success: function(msg) {
                msgArray = msg.split("##");
                var tempArray = msgArray[1].split("@@");
                if ($('#edit_customer_list').text().indexOf('Not Available') != -1) {
                    if (tempArray[1] != 0) {
                        $('#edit_customer_list').html(tempArray[0]);
                    }
                } else {
                    var str = tempArray[0].replace("<option value='all'>All</option>", "");
                    if (tempArray[1] != 0) {
                        $('#edit_customer_list').append(str);
                    }
                }
                 removeDuplicate('edit_customer_list');
                $(".selectpicker").selectpicker("refresh");
                edit_setEntitySiteOptionsList(selVal);
            }
        });
    }
}

function edit_getAllSitesForSelUser(username,selectedUserChId){
        $.ajax({
            type: "GET",
            url: "addCustomerModel.php",
            data: "function=edit_customersSiteList&selected_chid=" + selectedUserChId + "&username=" + username + "&csrfMagicToken=" + csrfMagicToken,
            success: function(msg) {
                msg = $.trim(msg);
                $('#edit_site_list').append(msg);
                removeDuplicate('edit_site_list');
            }
        });
}

function edit_setEntitySiteOptionsList(selVal){
    if (selVal != null) {
        $.ajax({
            type: "GET",
            url: "addCustomerModel.php",
            data: "function=edit_customersSiteList&selected_chid=" + selVal + "&csrfMagicToken=" + csrfMagicToken,
            success: function(msg) {
                msg = $.trim(msg);
                $('#edit_site_list').append(msg);
                removeDuplicate('edit_site_list');
            }
        });
    }
}

function edit_setSiteOptionsList(){
    var selVal = $('#edit_customer_list').val();
    var selVal1 = '';
    if (selVal == null) {
        $('#edit_customer_list option').attr("disabled", false);
    } else if (selVal == "all") {
        $('#edit_customer_list option[value!="all"]').attr("disabled", true);
        $("#edit_customer_list option").each(function()
        {
            if ($(this).val() != "all") {
                selVal1 += $(this).val() + ',';
            }
        });
        selVal = selVal1.slice(0, -1);
    } else if (selVal != "all") {
        $('#edit_customer_list option[value!="all"]').attr("disabled", false);
        $('#edit_customer_list option[value="all"]').attr("disabled", true);
    }
    $(".selectpicker").selectpicker("refresh");
    setEntitySiteOptionsList(selVal);
}

function edit_selectSiteOption(){
    var selVal = $('#site_list').val();
    if (selVal == null) {
        $('#edit_site_list option').attr("disabled", false);
    } else if (selVal == "all") {
        $('#edit_site_list option[value!="all"]').attr("disabled", true);
    } else if (selVal != "all") {
        $('#edit_site_list option[value!="all"]').attr("disabled", false);
        $('#edit_site_list option[value="all"]').attr("disabled", true);
    }
}

function resetSiteListOptions(select_id){
    $('#'+select_id+' option').attr("disabled", false);
    $("#"+select_id+" > option").attr("selected",false);
}

function edit_setCustomerText() {
    var selVal = $('#edit_customer_list').val();
    var selVal1 = '';
    if (selVal == null) {
        $("#edit_customer_list_text").html('');
        $('#edit_customer_list option').attr("disabled", false);
    } else if (selVal == "all") {
        $('#edit_customer_list option[value!="all"]').attr("disabled", true);
    } else if (selVal != "all") {
        $('#edit_customer_list option[value!="all"]').attr("disabled", false);
        $('#edit_customer_list option[value="all"]').attr("disabled", true);
    }
    $(".selectpicker").selectpicker("refresh");
    if (selVal != null) {
        $("#edit_customer_list_text").html('');
        if (selVal == 'all') {
            $("#edit_customer_list option").each(function()
            {
                if ($(this).val() != "all") {
                    $("#edit_customer_list_text").append($(this).text());
                    $("#edit_customer_list_text").append("\n");
                }
            });
        } else {
            $('#edit_customer_list :selected').each(function(i, selected) {
                $("#edit_customer_list_text").append($(selected).text());
                $("#edit_customer_list_text").append("\n");
            });
        }
    }
}

function getUserDetail(userid){
    $('#user_detail_list_div').html('<img src="../images/ajax-login.gif" class="loadhome" alt="loading..." style="width:auto !important;"/>');
    $.ajax({
        type: "GET",
        url: "addCustomerModel.php",
        data: "function=user_Hierarchy&userid=" + userid + "&csrfMagicToken=" + csrfMagicToken,
        success: function(msg) {
            msg = $.trim(msg);
            $('#user_detail_list_div').html(msg);
        }
    });
}

function level_to() {
               
    var selVal = $('#leveltoid').val();

    $.ajax({
         type : "GET",
         url : "addCustomerModel.php",
         data : "function=get_SelSiteList&eid=" + selVal + "&csrfMagicToken=" + csrfMagicToken,
         success : function (msg) {
             msg = $.trim(msg);
             $('#advsiteVal').html(msg);
             $(".selectpicker").selectpicker("refresh");
         }
     });
}

$("#userto").change(function(){
    alert($(this).val());
});
