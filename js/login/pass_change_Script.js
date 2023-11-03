$(document).ready(function() {
 
    var vid = '<?php echo $idVal; ?>';

    validateSession(vid);

    function validateSession(passVal) {
        if (vid == '') {
            $('.validateProcess').html('Password cannot be changed');
            $("#login-btn").attr("disabled", "disabled");
        } else {
            $.ajax({
                type: "GET",
                url: "customer/addCustomerModel.php",
                data: "function=validatevid&resetSession=" + passVal,
                success: function(msg) {
                    msg = $.trim(msg);
                    if (msg.indexOf("NOTDONE") !== -1) {
                        $('.validateProcess').html('Invalid session to create password.');
                        $("#login-btn").attr("disabled", "disabled");
                        $("#resetbutton").hide();
                    } else {
                        var email = msg.split('##');
                        if (email[1] == 'EXPAIRED') {
                            $("#login-btn").hide();
                            $("#input-password").attr('readonly', 'readonly');
                            $("#re-password").attr('readonly', 'readonly');
                            $("#resetbutton").show();
                            $('.validateProcess').html('Session is expired to reset password.Please Click Reset Button to regenerate the Link');
                            document.getElementById('input-emailid').value = email[2];
                        } else {
                            document.getElementById('input-emailid').value = email[1];
                        }
                    }
                }
            });

        }

    }

});

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
        data: "function=updatepasswrd&password=" + escape(password) + "&resetSession=" + resetSession,
        success: function(msg) {
            msg = $.trim(msg);
            if (msg == '1') {
                document.getElementById("successMsg").style.display = 'block';
                document.getElementById("errorMsg").style.display = 'none';
                document.getElementById('password').value='';
                document.getElementById('repeatpassword').value='';
                document.getElementById('email_id').value='';
                document.getElementById('userKey').value='';
                document.getElementById('warning_email').value='';
                document.getElementById('warning_pwd').value='';
                document.getElementById('warning_pwd').value='';
                
            } else {
                document.getElementById("successMsg").style.display = 'block';
                document.getElementById("errorMsg").style.display = 'none';
            }
        }
    });
}
function regenerate() {
    var useremail = $('#input-emailid').val();
    $.ajax({
        type: "GET",
        url: "customer/addCustomerModel.php",
        data: "function=regeneratePassword&email=" + useremail,
        success: function(msg) {
            msg = $.trim(msg);
            if (msg == 1 || msg == '1') {
                $('.validateProcess').html('Regenerated password confirmation mail has been sent to your registered email.');
            }
        }
    });

    //window.location.href='index.php?q=rst';

}
 