function getSkuListForCustomer() {
    $.ajax({
        url: 'addCustomerModel.php?function=getSkuListForCustomers&csrfMagicToken=' + csrfMagicToken,
        type: 'GET',
        dataType: 'text',
        success: function(response) {
            $("#skuSelectPicker").html(response.trim());
            $(".selectpicker").selectpicker('refresh');
        },
        error: function(response) {
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

/*@ Created : 06-04-2016
 *@ It will take all values which are filled in customer form & it will generate order with given number */
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
        addSubscription(m_data);
}


function addSubscription(m_data) {
    $("#add_successMsg").html('<img src="../images/ajax-login.gif" class="loadhome" alt="loading..." style="width:auto !important;padding-left:22% !important;"/>');
    $.ajax({
        url: 'addCustomerModel.php?function=createCustomerInfo&csrfMagicToken=' + csrfMagicToken,
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
