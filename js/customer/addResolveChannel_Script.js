

if(signUpType === 'signup'){
       
    $("#phnumber").val(signupPhone);
    $("#email").val(signupEmail);
    $("#fname").val(signupUsername);
    $("#lname").val(userLastname);  
    $("#name").val(signUpcompanyName);
    $("#ctype").val(customerType);
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
            $("#SKUList").html(data);
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
        data: "function=get_OutSourcedList" + "&csrfMagicToken=" + csrfMagicToken,
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
        }else if (field_id == "zpcode") {
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
    
    m_data.append('channel_uplogo', $('input[name=channel_uplogo]')[0].files[0]);
    m_data.append('channel_upicon', $('input[name=channel_upicon]')[0].files[0]);
    m_data.append('csrfMagicToken', csrfMagicToken);

    if (isReqFieldsFilled == true && isImageFieldsFilled == true) {
        $("#successMsg").html('<img src="../images/ajax-login.gif" class="loadhome" alt="loading..." style="width:auto !important;padding-left:22% !important;"/>');
        $.ajax({
            url: 'addCustomerModel.php?function=createResolvChannel',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                   if(response.status === 'success'){
                       $("#successMsg").html(response.msg+" Please click on Next button,");
                       $("#addSubmit").hide();
                       $("#cancel").hide();
                       $("#nextButton").show();
                }
            },
            error: function(response) {
                $("#successMsg").html("Error Occurred");
            }
        });


    }
}

$("#channel_uplogo").change(function(e) {
    var _URL = window.URL || window.webkitURL;
    var file, img,maxwidth=24,maxhieght=21,imgwidth,imgheight;


    if ((file = this.files[0])) {
        img = new Image();
        img.onload = function() {
            imgwidth  = this.width;
            imgheight = this.height;
            if((imgwidth > maxwidth) || (imgwidth < maxwidth)){
                $("#successMsg").css('color','red').html("Please upload image dimension of 24X21");
                isImageFieldsFilled = false;
            } else {
                isImageFieldsFilled = true;
            }
            if((imgheight > maxhieght) || (imgheight < maxhieght)){
                $("#successMsg").css('color','red').html("Please upload image dimension of 24X21");
                isImageFieldsFilled = false;
            }else {
                isImageFieldsFilled = true;
            }
        };
        img.onerror = function() {
             $("#successMsg").css('color','red').html("Not a valid file: " + file.type);
        };
        img.src = _URL.createObjectURL(file);
  }

});


$("#channel_upicon").change(function(e) {
    var _URL = window.URL || window.webkitURL;
    var file, img,maxwidth=225,maxhieght=125,imgwidth,imgheight;

   
    if ((file = this.files[0])) {
        img = new Image();
        img.onload = function() {
            imgwidth  = this.width;
            imgheight = this.height;
            if(imgwidth > maxwidth){
                $("#successMsg").css('color','red').html("Please upload image dimension of 225X125");
                isImageFieldsFilled = false;
            }else {
                isImageFieldsFilled = true;
            }
            if(imgheight > maxhieght){
                $("#successMsg").css('color','red').html("Please upload image dimension of 225X125");
                isImageFieldsFilled = false;
            }else {
                isImageFieldsFilled = true;
            }
        };
        img.onerror = function() {
            $("#successMsg").css('color','red').html("Not a valid file: " + file.type);
        };
        img.src = _URL.createObjectURL(file);
  }

});

