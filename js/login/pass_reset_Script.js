function validateSession(passVal) {
    if (vid == '') {
        $('.validateProcess').html('Password cannot be changed');
        $("#successMsg").hide();
        $("#errorMsg").hide();
        $("#submit_button_div").hide();
    } else {
        $.ajax({
            type: "GET",
            url: "customer/addCustomerModel.php",
            data: "function=validatesignupvid&resetSession=" + passVal,
            success: function(msg) {
                msg = $.trim(msg);
                if (msg.indexOf("NOTDONE") !== -1) {
                    $('.validateProcess').html('Invalid session to create password.');
                    $("#successMsg").hide();
                    $("#errorMsg").hide();
                    $("#submit_button_div").hide();
                }else{
                    $("#submit_button_div").show();
                }
            }
        });

    }

}

function uservalidation() {

    var password    = document.getElementById('password').value;
    var repPassword = document.getElementById('repeatpassword').value;
    var email_id    = document.getElementById('email_id').value;
    
    if(password === '' || repPassword === ''){
        document.getElementById('warning_pwd').innerHTML  = "Please enter both password";
        return false;
    } else if(email_id === ''){
        document.getElementById('warning_email').innerHTML = "Invalid email session";
        return false;
    } else if(password != repPassword){
        document.getElementById('warning_pwd').innerHTML  = "Both passwords should match";
        return false;
    }else {
        updatePassword(password);
    }
    
}

function updatePassword(password) {
    var resetSession = document.getElementById('userKey').value;
    $.ajax({
        type: "GET",
        url: "customer/addCustomerModel.php",
        data: "function=pass_reset&password=" + escape(password) + "&resetSession=" + resetSession,
        success: function(msg) {
            msg = $.trim(msg);
            if (msg == '1') {
                location.href = "customer/addResolveChannel.php?type=signup";
            } else {
                document.getElementById("successMsg").style.display = 'none';
                document.getElementById("errorMsg").style.display = 'block';
                return false;
            }
        }
    });
}
 