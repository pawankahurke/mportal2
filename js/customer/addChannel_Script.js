//getServerList();
//getSkuList();
getOutSourcedList();

if(signUpType === 'signup'){
       
    $("#phnumber").val(signupPhone);
    $("#email").val(signupEmail);
    $("#fname").val(signupUsername);
    $("#lname").val(userLastname);  
    $("#name").val(signUpcompanyName);
}

function changeSkuList(){
    var selBussiLevel = $("#ctype").val();
    changeReportList(selBussiLevel);
    $.ajax({
        type: 'GET',
        url: 'addCustomerModel.php',
        data: "function=get_signupchannelSkuList&selBussiLevel=" + selBussiLevel + "&csrfMagicToken=" + csrfMagicToken,
        async: false,
        success: function(data) {
            $("#SKUList").html(data);
        }
    });
}

function changeReportList(selBussiLevel){
    $.ajax({
        type: 'GET',
        url: 'addCustomerModel.php',
        data: "function=getChSignUpServerList&selBussiLevel=" + selBussiLevel + "&csrfMagicToken=" + csrfMagicToken,
        async: false,
        success: function(data) {
            $("#serverList").html(data);
        }
    });
}

function getSkuList() {
    $.ajax({
        type: 'GET',
        url: 'addCustomerModel.php',
        data: "function=get_channelSkuList&csrfMagicToken=" + csrfMagicToken,
        async: false,
        success: function(data) {
            var str = data.replace('<h2>Customer Sku </h2>', '');
            $("#SKUList").html(str);
        }
    });
}


function getServerList() {

    $.ajax({
        type: 'GET',
        url: 'addCustomerModel.php',
        data: "function=getChServerList&csrfMagicToken=" + csrfMagicToken,
        async: false,
        success: function(data) {
            $("#serverList").html(data);
        }
    });
}

function getOutSourcedList() {
    $.ajax({
        type: 'GET',
        url: 'addCustomerModel.php',
        data: "function=get_OutSourcedList&csrfMagicToken=" + csrfMagicToken,
        async: false,
        success: function(data) {
            $("#outsourcedlist").html(data);
        }
    });
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
    //var regExp = /^\+?([0-9]{2})\)?[-. ]?([0-9]{4})[-. ]?([0-9]{4})$/;
    var regExp = /^[0-9]+$/;
    if (regExp.test(zipcode)) {
        return true;
    } else {
        return false;
    }
}

function submitForm() {
    var isReqFieldsFilled = false;
    var isChecked = false;
    var idArray = [];
    $('.req').each(function() {
        var req_id = this.id;
        var field_id = req_id.replace("err_", "");
        var field_value = $("#" + field_id).val();
        if ($.trim(field_value) === "") {
            $("#err_" + field_id).css("color", "red").html(" *required");
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
        //idArray.push(field_value);
    });

//    var server = new Array();
//    $("input[name='server[]']:checked").each(function() {
//        server.push($(this).val());
//    });

    var skuVal = new Array();
    $("input[name='skuVal[]']:checked").each(function() {
        skuVal.push($(this).val());
    });
    
    var agentVal = new Array();
    $("#agent_list option").each(function()
    {
         agentVal.push($(this).val());
    });
    
    
    
//    if (server.length == 0) {
//        isChecked = false;
//        $("#err_server").html(" please select server");
//        return false;
//    } else 
    if (skuVal.length == 0) {
        isChecked = false;
        $("#err_skus").html(" please select skus");
        return false;
    } else {
        $("#err_server").html('');
        $("#err_skus").html('');
        isChecked = true;
    }


    var m_data = new FormData();
    m_data.append('name', $('#name').val());
    m_data.append('regnumber', $('#regnumber').val());
    m_data.append('companyVatId', $('#refnumber').val());
    m_data.append('website', $('#website').val());
    m_data.append('addr', $('#addr').val());
    m_data.append('city', $('#city').val());
    m_data.append('stprov', $('#stprov').val());
    m_data.append('zpcode', $('#zpcode').val());
    m_data.append('country', $('#country').val());
    m_data.append('fname', $('#fname').val());
    m_data.append('lname', $('#lname').val());
    m_data.append('email', $('#email').val());
    m_data.append('phnumber', $('#phnumber').val());
    m_data.append('ctype', $('#ctype').val());
    m_data.append('outsrcpart', $('#outsourcedlist').val());
    m_data.append('skuVal', skuVal);
    

    m_data.append('channel_uplogo', $('input[name=channel_uplogo]')[0].files[0]);
    m_data.append('channel_upicon', $('input[name=channel_upicon]')[0].files[0]);


    if (isReqFieldsFilled == true && isChecked == true) {
        $("#successMsg").html('<img src="../images/ajax-login.gif" class="loadhome" alt="loading..." style="width:auto !important;padding-left:22% !important;"/>');
        $.ajax({
            url: 'addCustomerModel.php?function=createChannel&csrfMagicToken=' + csrfMagicToken,
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if(addType === 'Submit'){
                    $("#successMsg").html(response.msg);
                    setTimeout(function() {
                        $("#successMsg").html('');
                    },3000);
               } else {
                   if(response.status === 'success'){
                       $("#successMsg").html(response.msg+" Please click on Next button,");
                       $("#addSubmit").hide();
                       $("#nextButton").show();
                   }else{
                       $("#successMsg").html(response.msg);
                       $("#addSubmit").show();
                       $("#nextButton").hide();
                   }
               }
            },
            error: function(response) {
                $("#successMsg").html("Error Occurred");
            }
        });


    }
}

$("#add_agent").click(function (){
    var agent_name = $("#agent_name").val();
    var agent_email = $("#agent_email").val();
    if(agent_name == "" || agent_email == ""){
        $("#successMsg").css("color","red").html("please insert both values");
        return false;
    }else if(!validateEmailAddr(agent_email)){
        $("#successMsg").css("color","red").html("enter valid agent email");
        return false;
    }else{
         $("#successMsg").html("");
        var exist = $("#agent_list option[value='"+agent_email+'--'+agent_name+"']").length;
        if(exist === 0){
            var str = '<option value="'+agent_email+'--'+agent_name+'">'+agent_name+' : '+agent_email+'</option>'
            $("#agent_list").append(str);
        }
    }
});

$("#remove_agent").click(function (){
    var selectedVal = $("#agent_list").val();
    if(selectedVal == "" || selectedVal == undefined){
        $("#successMsg").css("color","red").html("please select agent to remove");
    }else{
      $("#successMsg").html("");
      $("#agent_list option[value='"+selectedVal+"']").remove();  
    }
});
