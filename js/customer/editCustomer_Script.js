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


function validateCustomerForm() {
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
                $("#err_" + field_id).css("color", "red").html(" enter number between 7 to 16 characters");
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
    });
    
    if(isReqFieldsFilled){
        addCustomerForm();
    }
    return true;
}

function addCustomerForm() {
    var m_data = new FormData();
        $("#add_successMsg").html("");
        m_data.append('name', $('#name').val());
        m_data.append('companyVatId', $('#refnumber').val());
        m_data.append('addr', $('#addr').val());
        m_data.append('city', $('#city').val());
        m_data.append('stprov', $('#stprov').val());
        m_data.append('zpcode', $('#zpcode').val());
        m_data.append('country', $('#country').val());
        m_data.append('fname', $('#fname').val());
        m_data.append('lname', $('#lname').val());
        m_data.append('email', $('#email').val());
        m_data.append('phnumber', $('#phnumber').val());
        m_data.append('selectedCid', $('#selectedCid').val());

        m_data.append('customer_uplogo', $('input[name=customer_uplogo]')[0].files[0]);
        m_data.append('customer_appLogo', $('input[name=customer_appLogo]')[0].files[0]);
        m_data.append('csrfMagicToken', csrfMagicToken);
        addSubscription(m_data);
}

function addSubscription(m_data) {
    $("#add_successMsg").html('<img src="../images/ajax-login.gif" class="loadhome" alt="loading..." style="width:auto !important;padding-left:22% !important;"/>');
    $.ajax({
        url: 'addCustomerModel.php?function=updateCustomerInfo' + "&csrfMagicToken=" + csrfMagicToken,
        data: m_data,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            $("#add_successMsg").html(response.msg);
        },
        error: function(response) {
            $("#add_successMsg").html("Error Occurred");
        }
    });
    return false;
}


function getEntityDtl(eid) {

    $.ajax({
        type: 'GET',
        url: 'addCustomerModel.php',
        data: "function=get_channelDtl&eid=" + eid,
        async: false,
        dataType: 'json',
        success: function(data) {
            var result = eval(data);
            $('#edit_entity_name').html(result.companyName);
            $('#compName').html(result.companyName);
            $('#name').val(result.companyName);
            $('#regnumber').val(result.regNo);
            $('#refnumber').val(result.referenceNo);
            $('#companyVatId').val(result.companyName);
            $('#website').val(result.website);
            $('#addr').val(result.address);
            $('#city').val(result.city);
            $('#stprov').val(result.province);
            $('#zpcode').val(result.zipCode);
            $('#fname').val(result.firstName);
            $('#lname').val(result.lastName);
            $('#email').val(result.emailId);
            $('#phnumber').val(result.phoneNo);
            $('#ctype').val(result.entyHirearchy);
            $('#country option[value='+result.country+']').attr('selected','selected');
            var logoPath = result.logo;
            var logoname = logoPath.split('/')[5];

            var iconPath = result.iconLogo;
            var iconname = iconPath.split('/')[5];

            $("#addChnl_nameOfFile_uplogo").html(logoname);
            $("#addChnl_nameOfFile_upicon").html(logoname);

            //m_data.append( 'server', server);
            //m_data.append( 'skuVal', skuVal);
        }
    });
}


