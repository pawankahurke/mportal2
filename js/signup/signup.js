

var baseURI = "../lib/l-dashboardAPI.php?url=";
var invite_url = "http://development.nanoheal.com/DashboardNew/signupPage/index.php";

var userdetailsObj = {};

$(document).ready(function () {

    var token = $.trim($("#getToken").text());
    if (token != "") {
        var obj = {
            "data": {
                "token": token
            }
        };

        commonAjaxCall(baseURI + "msp/invitation/details&method=POST", JSON.stringify(obj), "").then(function (res) {
            // alert(res);
            var statusObj = JSON.parse(res);
            //Sample response data
            // {
            //     "status": "success",
            //     "code": 200,
            //     "message": "OK",
            //     "result": {
            //       "id": 31,
            //       "parent_eid": 8,
            //       "company_name": null,
            //       "first_name": null,
            //       "last_name": null,
            //       "email": "testR@mailinator.com",
            //       "signup_type": "msp",
            //       "status": 0,
            //       "ctype": null,
            //       "signup_url": null,
            //       "created_at": 1544100713,
            //       "last_update": "2018-12-06 12:51:53"
            //     }
            //   };    


            if (statusObj.status == "success") {
                //alert("hi");
                userdetailsObj = statusObj.result;
                $("#py_email").val(statusObj.result.email);
            }
        });
    }
});

var myInput = document.getElementById("py_setpassword");
var lowerCaseLetters = /[a-z]/g;
var upperCaseLetters = /[A-Z]/g;
var numbers = /[0-9]/g;

function signup(type) {
    var firstName = $.trim($('#py_firstname').val());
    var lastName = $.trim($('#py_lastname').val());
    var companyName = $.trim($('#py_companyname').val());
    var emailid = $.trim($('#py_email').val());
    var password = $('#py_setpassword').val();
    var confirmPassword = $('#py_confirmpassword').val();
    var termscheck = $('#py_agree:checked').val();
    var error = 0;
    var letters = /^[A-Za-z ]+$/;
    //var errorVal = 0;
    if (firstName === '') {
        $('#signupFirstNameErr').html("<span>*Please enter first name</span>");
        error++;
    } else if (!firstName.match(letters)) {
        $('#signupFirstNameErr').html("<span>*Only alphabets and spaces are allowed</span>");
        error++;
    } else {
        $('#signupFirstNameErr').html("");
    }

    if (lastName === '') {
        $('#signuplastnameErr').html("<span>*Please enter first name</span>");
        error++;
    } else if (!lastName.match(letters)) {
        $('#signuplastnameErr').html("<span>*Only alphabets and spaces are allowed</span>");
        error++;
    } else {
        $('#signuplastnameErr').html("");
    }

    if (companyName === '') {
        $("#signupcompanyameErr").html('<span>*Please enter company name</span>');
        error++;
    } else if (companyName.indexOf("__") != -1) {
        $('#signupcompanyameErr').html("<span>More than one underscore not allowed in company name</span>");
        error++;
    } else {
        $('#signupcompanyameErr').html("");
    }

    if (emailid === '') {
        $('#validemailError').html("<span>*Please enter email id</span>");
        error++;
    } else {
        $('#validemailError').html("");
    }
    if (password === '') {
        $('#setpasswordError').html("<span>*Please enter password</span>");
        error++;
    }
    if (confirmPassword === '') {
        $('#confirmpasswordError').html("<span>*Please enter confirm password</span>");
        error++;
    } else {
        $('#confirmpasswordError').html("");
    }

    if (termscheck === undefined) {
        //alert(termscheck);
        error++;
        $('#required_py_agree').html("<span>Please accept terms of use and legal agreements</span>");
    } else {
        $('#required_py_agree').html("");
    }
    if (password !== confirmPassword) {
        error++;
        $('#passwordMatch').html("<span>Passwords do not match. Please re-enter the password again</span>");
    }

    if (password !== '' && confirmPassword !== '' && emailid !== '' && companyName !== '' && password === confirmPassword && myInput.value.match(lowerCaseLetters) && myInput.value.match(upperCaseLetters) && myInput.value.match(numbers) && myInput.value.length >= 8) {
        $('#passwordMatch').html("");
        //alert(errorVal);
        if (error === 0) {
            if (type === "1") {

                var Obj = {
                    "data": {
                        "company_name": companyName,
                        "first_name": firstName,
                        "last_name": lastName,
                        "email": emailid,
                        "password": password,
                        "confirm_password": confirmPassword,
                        "parent_eid": userdetailsObj.parent_eid,
                        "ctype": 5,
                        "type": "msp"
                    }
                };
                var url = "web/invitation/signup" + "&method=POST";
            } else if (type === "3") {
                var Obj = {
                    "data": {
                        "email": emailid,
                        "parent_eid": 4,
                        "invite_url": invite_url
                    }
                };
                var url = "invite/msp" + "&method=POST";
            } else {

                var Obj = {
                    "data": {
                        "company_name": companyName,
                        "first_name": firstName,
                        "last_name": lastName,
                        "email": emailid,
                        "ctype": "5",
                        "type": "msp"
                    }
                };
                var url = "web/signup" + "&method=POST";
            }

            $(".loader").show();

            commonAjaxCall(baseURI + url, JSON.stringify(Obj), "").then(function (res) {
                $(".loader").hide();
                var statusObj = JSON.parse(res);

                if (statusObj.status == "success") {
                    var channelId = statusObj.result;
                    //$("#nano_signup_form").hide();
                    if (type === "1") {
                        //alert("inside type 1");
                        $("#thanks_message_text").text("Your  \""+emailid+"\" signup is successfull");
                        var userinfoObj={"email":emailid,"pwd":password,"authtype":"","timezone":"Asia\/Calcutta"};
                        relogin(userinfoObj);
                        //window.location.href = 'sliderindex.html';
                    } else {
                        //alert("inside type 2");
                        $("#thanks_message_text").text("An email has been sent to \"" + emailid + "\" to set password.");
                    }
                    //alert("inside type 3");    
                    $("#success_signup_div").show();
                } else {
                    //alert("inside type 4");
                    $("#signup_error_div").show();
                    $("#signup_error").text(JSON.stringify(statusObj.error.message));
                }

            });
        }
    }
}

function relogin(userinfoObj) {
    try {
        console.log('Relogin function');
        var pathArray = window.location.href.split('/');
        var loginUrl = pathArray[0] + "//" + pathArray[2] + "/" + pathArray[3] + "/index.php";
        console.log("Login URL : " + loginUrl);
        var form = $('<form action="' + loginUrl + '" method="post" style="display:none">' +
                '<input type="text" name="email" value="' + userinfoObj.email + '" />' +
                '<input type="text" name="password" value="' + userinfoObj.pwd + '" />' +
                '<input type="text" name="authtype" value="' + userinfoObj.authtype + '" />' +
                '<input type="text" name="timezone" value="' + userinfoObj.timezone + '" />' +
                '<input type="hidden" name="signInType" value="SIGNUPFLOW" />' +
                '</form>');

        $('body').append(form);
        form.submit();
    } catch (ex) {
        alert(ex.message);
    }
}

function toggleMask(id, imgId) {
    //alert(id);
    if ($("#" + id).attr("type") == "password") {
        //alert(id);
        $("#" + id).attr("type", "text");
        $("#" + imgId).attr("src", "../assets/img/eye-icon-hide.png");
    } else {
        $("#" + id).attr("type", "password");
        $("#" + imgId).attr("src", "../assets/img/eye-icon.png");
    }
}


myInput.onfocus = function () {
    document.getElementById("message").style.display = "block";
}

function validatePassword() {
    // Validate lowercase letters
    //var lowerCaseLetters = /[a-z]/g;
    if (myInput.value.match(lowerCaseLetters)) {
        $("#lowercaseCheck").html("&#252");
        $("#lowercaseCheck").css("color", "green");
    } else {
        $("#lowercaseCheck").html("&#x2717");
        $("#lowercaseCheck").css("color", "red");
    }

    // Validate upperCaseLetters letters
    //var upperCaseLetters = /[A-Z]/g;
    if (myInput.value.match(upperCaseLetters)) {
        $("#capitalCheck").html("&#252");
        $("#capitalCheck").css("color", "green");
    } else {
        $("#capitalCheck").html("&#x2717");
        $("#capitalCheck").css("color", "red");
    }

    // Validate numbers
    //var numbers = /[0-9]/g;
    if (myInput.value.match(numbers)) {
        $("#numberCheck").html("&#252");
        $("#numberCheck").css("color", "green");
    } else {
        $("#numberCheck").html("&#x2717");
        $("#numberCheck").css("color", "red");
    }

    // Validate length
    if (myInput.value.length >= 8) {
        $("#minimumNumCheck").html("&#252");
        $("#minimumNumCheck").css("color", "green");
    } else {
        $("#minimumNumCheck").html("&#x2717");
        $("#minimumNumCheck").css("color", "red");
    }
}

// function signupmsp(){
//     $("#signup_error").text("");
//     var errorVal = 0;
//     var email=$("#py_email").val();
//     $("#required_py_email").hide();

//     $('.validatesignupform').each(function() {
//         $("#required_" + field_id).hide();
//         var field_id = this.id;
//         var field_value = $("#" + field_id).val();
//         if ($.trim(field_value) === "") {       
//             $("#required_" + field_id).show();
//             errorVal++;
//         } else if (field_id == "py_email") {
//             if (!validate_Email(field_value)) {
//                 $("#validemailError").show();
//                 errorVal++;
//             }
//         }
//     });

//     if(errorVal===0){ 
//             var Obj={
//                 "data" : {
//                     "email" : email,
//                     "invite_url" : invite_url
//                 }
//             };


//             $(".loader").show();

//             commonAjaxCall(baseURI+"invitemsp", JSON.stringify(Obj), "").then(function(res) {  
//                 $(".loader").hide();
//                     var statusObj=JSON.parse(res);

//                     if(statusObj.status=="success"){
//                         var channelId=statusObj.result;
//                         $("#nano_signup_form").hide();

//                         $("#thanks_message_text").text("An email has been sent to \""+email+"\" for signup");

//                         $("#success_signup_div").show();                               
//                     }else{
//                         $("#signup_error_div").show();                        
//                         $("#signup_error").text(JSON.stringify(statusObj.error.message));                          
//                     }
//             });       
//     }  
// }

