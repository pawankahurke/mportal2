function getSkuList() {
    $.ajax({
        type: 'GET',
        url: 'addCustomerModel.php',
        data: "function=get_editSkulistChannel&cid="+eid + "&csrfMagicToken=" + csrfMagicToken,
        async: false,
        success: function(data) {
            $("#SKUList").html(data);
        }
    });
}


function getServerList() {

    $.ajax({
        type: 'GET',
        url: 'addCustomerModel.php',
        data: "function=get_editServerlistChannel&cid="+eid + "&csrfMagicToken=" + csrfMagicToken,
        async: false,
        success: function(data) {
            $("#serverList").html(data);
        }
    });
}

//function getOutSourcedList() {
//    $.ajax({
//        type: 'GET',
//        url: 'addCustomerModel.php',
//        data: "function=get_OutSourcedList",
//        async: false,
//        success: function(data) {
//            $("#outsourcedlist").html(data);
//        }
//    });
//}

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

function validateAlphaNumeric(name) {
                var filter = /^[a-z\d\_\s]+$/i;
                if (filter.test(name)) {
                return true;
                }
                else {
                return false;
                }
            }

function getChannelDtl(eid) {

    $.ajax({
        type: 'GET',
        url: 'addCustomerModel.php',
        data: "function=get_channelDtl&eid=" + eid + "&csrfMagicToken=" + csrfMagicToken,
        async: false,
        dataType: 'json',
        success: function(data) {
            var result = eval(data);
            $('#edit_entity_name').html(result.companyName);
            $('#compName').html(result.companyName);
            $('#name').val(result.companyName);
            $('#regnumber').val(result.regNo);
            $('#refnumber').val(result.referenceNo);
            $('#website').val(result.companyName);
            $('#addr').val(result.address);
            $('#city').val(result.city);
            $('#stprov').val(result.province);
            $('#zpcode').val(result.zipCode);
//            $('#country').val(result.country);
            $('#fname').val(result.firstName);
            $('#lname').val(result.lastName);
            $('#email').val(result.emailId);
            $('#phnumber').val(result.phoneNo);
            $('#country option[value='+result.country+']').attr('selected','selected');
            $('#ctype option[value='+result.businessLevel+']').attr('selected','selected');
            $('#orderinfo').val(result.ordergen);
            $('#outsourcedlist').html(result.outsourcedList);
            var logoPath = result.logo;
            var logoname = logoPath.split('/')[5];

            var iconPath = result.iconLogo;
            var iconname = iconPath.split('/')[5];
            $('#channel_uplogo').data("value","logo.png");
//            $('#channel_uplogo').val(logoname);
//            $('#channel_upicon').val(iconname);



        }
    });
}

function updateForm() {
    var isReqFieldsFilled = true;
var isChecked = true;
 var idArray = [];
 $('.req').each(function () {
        var req_id=this.id;
        var field_id=req_id.replace("err_","");
        var field_value=$("#"+field_id).val();

        if($.trim(field_value)===""){
            $("#err_"+field_id).css("color","red").html(" required"); 
            isReqFieldsFilled=false;
            return false;
        }else if(field_id == "phnumber"){
            if(!validatePhoneNumber(field_value)){
                isReqFieldsFilled = false;
               $("#err_"+field_id).css("color","red").html(" enter valid phone number"); 
               return false;
            }else{
                isReqFieldsFilled = true;
                $("#err_"+field_id).css("color","red").html(""); 
                return true;
            }

        }else if(field_id == "zpcode"){
            if(!validateZipCode(field_value)){
                isReqFieldsFilled = false;
               $("#err_"+field_id).css("color","red").html(" enter valid zipcode"); 
               return false;
            }else{
                isReqFieldsFilled = true;
                $("#err_"+field_id).css("color","red").html(""); 
                return true;
            }

        }else{
            isReqFieldsFilled=true;
        }
        //idArray.push(field_value);
    });

   var server = new Array();
    $("input[name='server[]']:checked").each(function() {
      server.push($(this).val());
    });

    var skuVal = new Array();
    $("input[name='skuVal[]']:checked").each(function() {
      skuVal.push($(this).val());
    });

    if(server.length == 0){
        isChecked=false;
        $("#err_server").html("please select server");
        return false;
    }else if(skuVal.length == 0){
        isChecked=false;
        $("#err_skus").html(" please select skus");
        return false;
    }else{
        $("#err_server").html('');
        $("#err_skus").html('');
        isChecked=true;
    }
    
    var ctype       = $("#ctype").val();
    var loginusing  = $("#loginusing").val();
    var orderinfo   = $("#orderinfo").val();
   
    
    $("#err_companyBusiness").html(" ");
    $("#err_companyHireachy").html(" ");
    $("#err_companylogin").html(" ");
    $("#err_companyorder").html(" ");
    
    if(ctype === 0 || ctype === '0'){
        $("#err_companyBusiness").html("Please select Business Type");
        isReqFieldsFilled = false;
        return false;
    }
    
    if(loginusing === 0 || loginusing === '0'){
        $("#err_companyHireachy").html("Please select Entity Hirearchy");
        isReqFieldsFilled = false;
        return false;
    }
    
//    if(orderinfo === 0 || orderinfo === '0'){
//        $("#err_companylogin").html("Please select Login Using");
//        isReqFieldsFilled = false;
//        return false;
//    }
    
    
    
    var m_data = new FormData();  
    m_data.append('editId',$('#editId').val());
    m_data.append( 'name', $('#name').val());
    m_data.append( 'regnumber', $('#regnumber').val());
    m_data.append( 'refnumber', $('#refnumber').val());
    m_data.append( 'website', $('#website').val());
    m_data.append( 'addr', $('#addr').val());
    m_data.append( 'city', $('#city').val());
    m_data.append( 'stprov', $('#stprov').val());
    m_data.append( 'zpcode', $('#zpcode').val());
    m_data.append( 'country', $('#country').val());
    m_data.append( 'ftpurl', $('#ftpurl').val());
    m_data.append( 'wsurl', $('#wsurl').val());
    m_data.append( 'fname', $('#fname').val());
    m_data.append( 'lname', $('#lname').val());
    m_data.append( 'email', $('#email').val());
    m_data.append( 'phnumber', $('#phnumber').val());
    m_data.append( 'ctype', $('#ctype').val());
    m_data.append( 'outsrcpart', $('#outsourcedlist').val());
    m_data.append( 'server', server);
    m_data.append( 'skuVal', skuVal);

    m_data.append( 'channel_uplogo', $('input[name=channel_uplogo]')[0].files[0]);
    m_data.append( 'channel_upicon', $('input[name=channel_upicon]')[0].files[0]);
    m_data.append('csrfMagicToken', csrfMagicToken);

    //instead of $.post() we are using $.ajax()
    //that's because $.ajax() has more options and flexibly.
    if(isReqFieldsFilled == true && isChecked == true){
        $("#edit_channel_success").html('<img src="../images/ajax-login.gif" class="loadhome" alt="loading..." style="width:auto !important;padding-left:22% !important;"/>');
    $.ajax({
          url: 'addCustomerModel.php?function=updateChannel',
          data: m_data,
          processData: false,
          contentType: false,
          type: 'POST',
          dataType:'json',
          success: function(response){
              $("#edit_channel_success").html(response.msg);
              setTimeout(function(){ 
                  window.location.href = "summary.php";
                }, 
              5000);
          },
          error: function(response){
              $("#edit_channel_success").html("Error Occurred");
          }
        });

    }
}


