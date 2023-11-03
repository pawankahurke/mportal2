$(document).ready(function () {
  $('#fpln').on('click', function () {
    document.location.href = 'forgot-password.php';
  });
  $('#signUpLink').on('click', function () {
    document.location.href = 'userSignUp.php';
  });
  //    sendSecondGet();
  checkSingleSignOn();
});

var ctrlKeyDown = false;
var ResendOtp = 0;
var ssoType = '';
function checkSingleSignOn() {
  $.ajax({
    url: 'lib/l-login.php',
    type: 'GET',
    dataType: 'json',
    data: { function: 'checkSingleSignOnStat' },
    success: function (resp) {
      try {
        if (resp['code'] == '200') {
          if (resp['msg'] == 'SSO_SET') {
            $('#sso_option').show();
            ssoType = resp['data'];
          }
        }
      } catch (error) {
        debugger;
        console.error(error);
      }
    },
    error: function (error) {
      console.log('Error - Fn. checkSingleSignOn : ' + error);
    },
  });
}

function startSSOauth() {
  if (!ssoType) {
    return setTimeout(startSSOauth, 500);
  }
  $.ajax({
    url: 'lib/l-login.php',
    type: 'GET',
    data: { function: 'processSingleSignOn', sso_type: ssoType },
    success: function (data) {
      try {
        var resp = JSON.parse(data);
        console.log(resp, 'resp');
        if (resp['code'] == '200') {
          if (ssoType == 'OAUTH') {
            var reurl = resp['reurl'] + '/api/connect/provider/?nonce=' + resp['nonce'];
            location.href = reurl;
          } else {
            location.href = resp['data'];
          }
        }
      } catch (e) {
        console.error(e);
        setTimeout(startSSOauth, 500);
      }
    },
    error: function (error) {
      console.log('Error - Fn. checkSingleSignOn : ' + error);
    },
  });
}

$('#ssoauthbtn').click(function () {
  setTimeout(startSSOauth, 500);
});

// run sso auth if `startSSOauth=1` in url string
if (window.location.search.indexOf('startSSOauth=1') !== -1) {
  startSSOauth();
}

// $('#ssoauthbtn').click(function () {
//     $.ajax({
//         url: 'lib/l-login.php',
//         type: 'POST',
//         data: { function: 'processSingleSignOn', mainloginform: 1, ssoType: ssoType},
//         success: function (data) {
//             var resp = JSON.parse(data);
//           //  document.write(resp.result)
//             console.log(resp);
//       //    return
//             if (resp['code'] == '200') {
//              //   sendSecondGet(resp['redUrl'], resp['cookie']);
//                 location.href = resp['redUrl']+'?cookie=' + resp['cookie'];
//               //  location.href = 'http://alex.nanoheal.work/Dashboard/redir.php?url=' + resp['redUrl'];
//              //   location.href = resp['redUrl'];

//             }
//             else {
//                 document.write(data);
//             }
//         },
//         error: function (error) {
//             console.log('Error - Fn. checkSingleSignOn : ' + error);
//         }
//     });
// });

// function sendSecondGet(url, cookie) {
//     $.ajax({
//         url: 'https://aad41ed2b8a9.ngrok.io/api/connect/provider',
//         type: 'GET',
// /*         beforeSend: function (request) {
//             request.setRequestHeader("cookie", cookie );
//         },  */
//         success: function (data) {
//             console.log(data);
//             location.href = data;
//         },
//         error: function (error) {
//             console.log('Error - Fn. checkSingleSignOn : ' + error);
//         }
//     });
// }
function keydown(e) {
  if ((e.which || e.keyCode) == 116 || ((e.which || e.keyCode) == 82 && ctrlKeyDown)) {
    // Pressing F5 or Ctrl+R
    e.preventDefault();
    if (ResendOtp == 1) {
      $('#ReloadMsg').show();
      $('#ReloadMsg').html("Do not refresh page while we're emailing you the OTP.");
    } else {
      $('#refreshmsg').html("Do not refresh page while we're emailing you the OTP.");
    }
  } else if ((e.which || e.keyCode) == 17) {
    // Pressing  only Ctrl
    ctrlKeyDown = true;
  }
}

var button = document.getElementById('loginSubmitId');
var email = document.getElementById('email');
var password = document.getElementById('password');

//   email.addEventListener("keyup", function (event) {
//     if (event.keyCode == 13) {
//         button.click();
//     }
// });
//
//   password.addEventListener("keyup", function (event) {
//     if (event.keyCode == 13) {
//         button.click();
//     }
// });

function keyup(e) {
  // Key up Ctrl
  if ((e.which || e.keyCode) == 17) ctrlKeyDown = false;
  if (ResendOtp == 1) {
    $('#ReloadMsg').show();
    $('#ReloadMsg').html("Do not refresh page while we're emailing you the OTP.");
  } else {
    $('#refreshmsg').html("Do not refresh page while we're emailing you the OTP.");
  }
}

function refreshCaptcha() {
  var img = document.images['captchaimg'];
  img.src = img.src.substring(0, img.src.lastIndexOf('?')) + '?val=' + Math.random() * 1000;
}
$('#forgotPass').click(function () {
  var userId = $.trim($('#useremail').val());
  if (userId !== '') {
    $.ajax({
      type: 'GET',
      url: 'dashboard/dashboardAjax.php',
      data: { function: 'sendResetPasswordLink1', user_email: userId },
      success: function (msg) {
        var response = $.trim(msg);
        if (response == 2) {
          $('#error').html('<lable style="color:#ec250d">Your email is not registered, please check with admin.</lable>');
        } else if (response == 1) {
          $('#error').html('<lable style="color:#ec250d;">Email Sent, please check your email to reset password.</lable>');
        } else {
          $('#error').html('<lable style="color:#ec250d">Email Sent failed, please try again.</lable>');
        }
      },
      error: function (msg) {},
    });
  } else {
    $('#error').html('<lable style="color:red">Please enter registered email id.</lable>');
  }
});

function get_UserDetails() {
  var userId = $('#useremail').val();
  $.ajax({
    type: 'GET',
    url: '/lib/l-loginAjax.php',
    data: { function: 'login_sendresetPassLink', userid: userId },
    dataType: 'json',
    success: function (msg) {},
    error: function (msg) {},
  });
}

function validateSession(passVal) {
  if (passVal == '') {
    $('.validateProcess').css('color', '#ec250d').html('<span>Password cannot be changed</span>');
    $('#successMsg').hide();
    $('#errorMsg').hide();
    $('#submit_button_div').hide();
  } else {
    $.ajax({
      type: 'POST',
      url: 'customer/addCustomerModel.php',
      data: {
        function: 'validatevid',
        resetSession: passVal,
        csrfMagicToken: csrfMagicToken,
      },
      success: function (msg) {
        msg = $.trim(msg);
        if (msg.indexOf('NOTDONE') !== -1) {
          $('.invalidsession').hide();
          $('.validateProcess').css({ color: '#ec250d', 'margin-left': '12px' }).html('<span>Invalid session to set / reset password.</span>');
          $('#successMsg').hide();
          $('#errorMsg').hide();
          $('#submit_button_div').hide();
        } else {
          $('.invalidsession').show();
          var userResult = msg.split('##');
          $('#hi_message').html('Welcome ' + userResult[1] + '. ');
          $('#submit_button_div').show();
        }
      },
    });
  }
}

$('#login-btn').click(function () {
  var password = document.getElementById('passwordval').value;
  var repPassword = document.getElementById('repassword').value;
  var user_id = document.getElementById('userid').value;
  var mainloginform = document.getElementById('mainloginform').value;

  if (password === '' || repPassword === '') {
    document.getElementsByClassName('validateProcess')[0].innerHTML = "<lable style='color:#ec250d'>Please enter both password</lable>";
    return false;
  } else if (password != repPassword) {
    document.getElementsByClassName('validateProcess')[0].innerHTML = "<lable style='color:#ec250d'>Both passwords should match</lable>";
    return false;
  } else if (password.length < 8) {
    document.getElementsByClassName('validateProcess')[0].innerHTML = "<lable style='color:#ec250d'>Password should be minimum 8 characters</lable>";
    return false;
  } else if (password.length > 255) {
    document.getElementsByClassName('validateProcess')[0].innerHTML =
      "<lable style='color:#ec250d'>Password should be maximum 255 characters</lable>";
    return false;
  } else if (!validatePassword(password) || !validatePassword(repPassword)) {
    document.getElementsByClassName('validateProcess')[0].innerHTML =
      "<lable style='color:#ec250d'>Password should be contain lowercase letters, uppercase letters, numbers, and special characters</lable>";
    return false;
  } else {
    updatePassword(password, user_id, mainloginform);
  }
});

function updatePassword(password, resetSession, mainloginform) {
  var dat = {
    function: 'updatepasswrd',
    password: escape(password),
    resetSession: resetSession,
    csrfMagicToken: csrfMagicToken,
    mainloginform: mainloginform,
  };
  console.log(dat);
  $.ajax({
    type: 'POST',
    url: 'customer/addCustomerModel.php',
    data: dat,
    success: function (msg) {
      console.log(msg);
      msg = JSON.parse(msg);
      if (msg.success) {
        document.getElementsByClassName('validateProcess')[0].innerHTML =
          '<lable  style=\'color:#007F00\'>Password has been created successfully, please click <span style="color:#008bbc; cursor: pointer;" data-qa=\'gotologinpage\' onclick="gotologinpage();">here</span> to sign-in</lable>';
        $('.signed-up-start-block').css('display', 'none');
        $('.reset-pass-btn').css('display', 'none');
        $('.signed-up-success-block').css('display', 'block');
        $('.reset-pass-btn-login').css('display', 'block');
      } else if (msg.error) {
        document.getElementsByClassName('validateProcess')[0].innerHTML = `<lable style='color:#ec250d'>${msg.error}</lable>`;
      } else {
        document.getElementsByClassName('validateProcess')[0].style.display = 'none';
      }
    },
  });
}
function validatePassword(pwd) {
  //var regex = /^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[^\w\s]).{8,}$/;
  //for digit
  var i = 0;
  var digitpattern = /^.*(?=.*\d).*$/;

  //For lower case
  var lowerpattern = /^.*(?=.*[a-z]).*$/;

  //For upper case
  var upperpattern = /^.*(?=.*[A-Z]).*$/;

  // For symbols
  var symbolpattern = /^.*(?=.*[@#$%&._+-/*]).*$/;

  if (digitpattern.test(pwd)) {
    i++;
  }
  if (lowerpattern.test(pwd)) {
    i++;
  }
  if (upperpattern.test(pwd)) {
    i++;
  }
  if (symbolpattern.test(pwd)) {
    i++;
  }
  if (i >= 3) {
    return true;
  } else {
    return false;
  }
}

function loginCancel() {
  // location.reload();
  window.location.href = 'index.php';
}

function resetCancel() {
  location.reload();
}

function forgotCancel() {
  //When cancel button clicked on forgot page, it should navigate to sign in page.
  window.location = 'index.php';
}

//Onclick of view password icon following functions will get called.
//$('#viewPassword').click(function () {
//    alert("sdfk");
//    $('#password').attr('type', 'text');
//}, function () {
//    $('#password').attr('type', 'password');
//});

$('#showPass')
  .mousedown(function () {
    $('#password').attr('type', 'text');
  })
  .bind('mouseup mouseleave', function () {
    $('#password').attr('type', 'password');
  });

$('#loginSubmitId').click(function (e) {
  var password = $.trim(document.getElementById('password').value);
  var user_id = $.trim(document.getElementById('email').value).toLowerCase();
  var mainloginform = $.trim(document.getElementById('mainloginform').value).toLowerCase();
  if (password === '' && user_id === '') {
    $('#error').html("<lable style='color:#ec250d'><span>Please enter both email and password.</span></lable>");
    return false;
  }
  if (user_id === '') {
    $('#error').html("<lable style='color:#ec250d'><span>Please enter email.</span></lable>");
    $('#error').show();

    return false;
  }
  if (user_id !== '') {
    if (!validateEmaillogin(user_id)) {
      $('#error').html("<lable style='color:#ec250d'><span>Please enter valid email.</span></lable>");
      $('#error').show();
      return false;
    }
  }
  if (password === '') {
    $('#error').html("<lable style='color:#ec250d'><span>Please enter password.</span></lable>");
    $('#error').show();
    return false;
  }
  if (password !== '' && user_id !== '') {
    $('#visited').val('');
    $('#error').hide();

    $('#absoLoader').show();
    var data = {
      function: 'validateUserDetails',
      mainloginform: mainloginform,
    };
    // var data = {'function': 'validateUserDetails', 'email': user_id, 'password': password, 'mainloginform': mainloginform}
    console.log(data);
    $.ajax({
      url: 'lib/l-login.php',
      type: 'POST',
      data: data,
      headers: {
        Authorization: 'Basic ' + btoa(user_id + ':' + password),
      },
      success: function (data) {
        $('#absoLoader').hide();
        var res = JSON.parse(data);
        if (res) {
          if (res['msg'] === 'LOGGED') {
            window.localStorage.setItem('adminAuthToken', res['token']);
            // Hot fix for wrong redirect issue
            res.url = res.url.replace(/\/Dashboard\/home$/, '/Dashboard/home/');
            debugger;
            location.href = res.url;
          } else if (res['msg'] == 'OPT_SENDED') {
            debugger;
            location.href = '/Dashboard/mfa/';
          } else {
            $('#error').html(res['msg']);
            $('#error').show();
            return false;
          }
        }
      },
      error: function (err) {
        $('#absoLoader').hide();
      },
    });
  }
});

function validateEmaillogin(email) {
  //    var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
  var filter = /^\w+([\.\-/+]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
  if (filter.test(email)) {
    return true;
  } else {
    return false;
  }
}

function toggleMask(id, imgId) {
  //alert(id);
  if ($('#' + id).attr('type') == 'password') {
    //alert(id);
    $('#' + id).attr('type', 'text');
    $('#' + imgId).attr('src', 'assets/img/eye-icon-hide.png');
  } else {
    $('#' + id).attr('type', 'password');
    $('#' + imgId).attr('src', 'assets/img/eye-icon.png');
  }
}

$('#otpsubmit').click(function (e) {
  var otp = $.trim(document.getElementById('otp_val').value);

  if (otp === '') {
    $('#resendotp').css('pointer-events', 'auto');
    $('#error').html("<label style='color:#ffffff'><span>Please enter otp.</span></label>");
    return false;
  } else {
    $('#otpmsg').html('');
    $('#otpForm').submit();
    e.preventDefault();
  }
});

function otpgen() {
  var timer = $('#time').val();
  var timeLeft = timer,
    cinterval;
  $('#resendotp').attr('disabled', true);
  $('#otpsubmit').attr('disabled', false);
  var timeDec = function () {
    timeLeft--;
    $('#countdown').text(timeLeft);
    if (timeLeft === 0) {
      clearInterval(cinterval);
      $('#otpmsg').html('Your OTP has expired. Please click on Resend to send a fresh OTP');
      $('#resendotp').attr('disabled', false);
      $('#otpsubmit').attr('disabled', true);
      $('#error').html('');
      $('#successmsg').html('');
      $('.firstotp').hide();
    } else {
      $(document).on('keydown', keydown);
      $(document).on('keyup', keyup);
      //            $('#refreshmsg').html("OTP Page refresh is not allowed");
    }
  };

  cinterval = setInterval(timeDec, 1000);
}

$('#resendotp').click(function (e) {
  e.preventDefault();
  $('#error').html('');
  $('#otpmsg').html('');
  $('#successmsg').hide();
  //    $('.firstotp').hide();
  var email = $('#name').val();
  var timer = $('#counter').val();
  $.ajax({
    type: 'POST',
    url: 'customer/addCustomerModel.php?function=regenerate&email=' + email,
    success: function (data) {
      ResendOtp = 1;
      $('#ReloadMsg').show();
      $(document).on('keydown', keydown);
      $(document).on('keyup', keyup);
      $('#otpmsg').html("<span>OTP expires in <span id='countdown1'>120</span> seconds</span>");
      $('#error').html("<label style='color:black'><span>OTP has been resent. Please check your mail.</span></label>");

      otpgen1();
    },
  });
});

function startTimer() {
  var presentTime = document.getElementById('blocktime').innerHTML;
  var timeArray = presentTime.split(/[:]+/);
  var m = timeArray[0];
  var s = checkSecond(timeArray[1] - 1);
  if (s == 59) {
    m = m - 1;
  }
  if (m < 0) {
    $('#error').html('');
  }

  document.getElementById('blocktime').innerHTML = m + ':' + s;
  setTimeout(startTimer, 1000);
}

function checkSecond(sec) {
  if (sec < 10 && sec >= 0) {
    sec = '0' + sec;
  } // add zero in front of numbers < 10
  if (sec < 0) {
    sec = '59';
  }
  return sec;
}

function otpgen1() {
  var timer = $('#counter').val();
  var timeLeft = timer,
    cinterval;
  $('#otpsubmit').attr('disabled', false);
  $('#resendotp').attr('disabled', true);
  var timeDec = function () {
    timeLeft--;
    $('#countdown1').text(timeLeft);
    if (timeLeft === 0) {
      clearInterval(cinterval);
      $('#otpmsg').html('Your OTP has expired. Please click on Resend to send a fresh OTP');
      $('#resendotp').attr('disabled', false);
      $('#otpsubmit').attr('disabled', true);
      $('#error').html('');
      $('#successmsg').html('');
      $('.firstotp').hide();
      $('#ReloadMsg').hide();
    }
  };

  cinterval = setInterval(timeDec, 1000);
}
