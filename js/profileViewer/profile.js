const showDashboard = () => {
  let menuStr = window.sessionStorage.getItem('menu');
  if (!menuStr) {
    getDashboardList();
    return false;
  }

  const menu = JSON.parse(menuStr);
  $('#dashoardList').html(menu);
};

const showDashboardGroupped = () => {
  let menuStr = window.sessionStorage.getItem('menuGroupped');
  if (!menuStr) {
    getDashboardList();
    return false;
  }

  const menu = JSON.parse(menuStr);
  $('#dashoardListGroupped').html(menu);
};

/* ===== edit profile code starts ===== */
$('#profDispButt').click(function () {
  $('.rightslide-container-close').trigger('click');
  if($('#reset-pass-container').css('width').replace('%', '').replace('px','').replace('em','') > 0){
     $('.reset-pass-container-close').trigger('click');
  }
  $('#respo_msg').hide();
  $('#respo_msg').css('display', 'none');
  $('#respo_failmsg').hide();
  $('#respo_failmsg').css('display', 'none');
  $('#user_email').attr('disabled', 'disabled');
  $('#dashbaord_user_role').attr('disabled', 'disabled');
  var useremail = $('#user_email').val();
  var custtype = $('#custtype').val();
  var data = { function: 'profiledata', uemail: useremail, custtype: custtype, csrfMagicToken: csrfMagicToken };
  console.log(data);
  $.ajax({
    url: '../profileViewer/profileData.php',
    type: 'POST',
    dataType: 'json',
    data: data,
    async: true,
    success: function (data) {
      console.log(data);
      $('input[type=text]').prev().parent().removeClass('is-empty');
      $('#firstname').val(data.fname);
      $('#lastname').val(data.lname);
      $('#user_email').val(data.uemail);
      $('#dashbaord_user_role').val(data.role);
      $('#phone_no').val(data.phno);
      $('#hierarchy').val(data.cust);
      $('#timeZone').html(data.timezone);
      $('.selectpicker').selectpicker('refresh');
      $('.cr-image').attr('src', data.imgpath);
      $('.cr-image').css({ transform: 'translate3d(-174.811px, -181.894px, 0px) scale(0.4236)', 'transform-origin': '299.81px 306.895px 0px' });
    },
  });
  rightContainerSlideOn('profilepicture-add-container'); //to open slider
});

$('.profilepicture-container-close').click(function () {
  rightContainerSlideClose('profilepicture-add-container'); //to open slider
});

$('.reset-pass-container-close').click(function () {
  rightContainerSlideClose('reset-pass-container'); //to open slider
});
$('.hdr-drop').click(function () {
  $('.closebtn').click();
});

//function profileeditsubmit() {
//    var fname       = $('#firstname').val();
//    var lname       = $('#lastname').val();
//    var pnumb       = $('#phone_no').val();
//    var logoimg     = $("#fileuploader2").val();
//    var logofootimg = $("#fileuploader3").val();
//    var clientimg   = $("#fileuploader4").val();
////    var uldtext     = $('#uploadtext').val();
//    var radio       = $('input[name=logos]:checked').val();
//    var clientlogo  = $('input[name=clientlogo]:checked').val();
//    var userlogo    = $('#userlogorole').val();
//    var timeZon     = $('#timeZone').val();
//    var timezoneDB  = $('#Timezone').val();
//    var timeZone    = '';
//
//    if (fname == '') {
//        $('#updatesuccessmsg').show();
//        $('#updatesuccessmsg').html('<span style="color:red">Please enter First name</span>');
//        return false;
//    }
//    if (lname == '') {
//        $('#updatesuccessmsg').show();
//        $('#updatesuccessmsg').html('<span style="color:red">Please enter Last name</span>');
//        return false;
//    }
//    if (pnumb == '') {
//        $('#updatesuccessmsg').show();
//        $('#updatesuccessmsg').html('<span style="color:red">Please enter Phone Number</span>');
//        return false;
//    }
//
//    if (timezoneDB == 'default') {
//        timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
//        if(timeZone == 'Asia/Calcutta'){
//            timeZone = 'Asia/Kolkata';
//        }
//    } else {
//        timeZone = timeZon;
//    }
//
//    var m_data = new FormData();
//    $("#add_successMsg").html("");
//    m_data.append('firstname', fname);
//    m_data.append('lastname', lname);
//    m_data.append('phone_no', pnumb);
//    m_data.append('time_zone', timeZone);
//
//    if(userlogo == 1 || userlogo == '1') {
//        m_data.append('upload_logo', $('input[name=fileuploader2]')[0].files[0]);// left pane logo
//        m_data.append('upload_footerlogo', $('input[name=fileuploader3]')[0].files[0]);// footer logo
//        m_data.append('upload_clientlogo',  $('input[name=fileuploader4]')[0].files[0]);// client logo
//    }
//    $("#updatesuccessmsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." style="width:auto !important;padding-left:22% !important;"/>');
//
//    $.ajax({
//        type: 'post',
//        url: '../profileViewer/profileData.php?function=profiledataedit',
//        processData: false, // important
//        contentType: false, // important
//        data: m_data,
//        success: function (data) {
//            if (radio == 'on' || clientlogo == 'on') {
//                if (data == 1 || data == '1') {
//                    $.ajax({
//                        url: '../profileViewer/profileData.php?function=profiledataeditlogoText',
//                        type: 'post',
//                        processData: false, // important
//                        contentType: false, // important
//                        data: m_data,
//                        success: function (data) {
//                            if (data == 1) {
//                                $('#updatesuccessmsg').show();
//                                $('#updatesuccessmsg').html('<span style="color:green"></span>');
//                                setTimeout(function () {
//                                    $('#profileDisplay').modal('hide');
//                                    location.reload();
//                                }, 1500);
//                            } else {
//                                $('#updatesuccessmsg').show();
////                                $('#updatesuccessmsg').html('<span style="color:red">Profile not updated successfully</span>');
//                                $('#updatesuccessmsg').html('<span style="color:red"></span>');
//                                setTimeout(function () {
//                                    $('#profileDisplay').modal('hide');
//                                    location.reload();
//                                }, 1500);
//                            }
//                        }
//                    })
//                } else {
//                    $('#updatesuccessmsg').show();
//                    $('#updatesuccessmsg').html('<span style="color:red">Menu Image size less than 3 KB and Footer Image size less than 6 KB</span>');
//                }
//            } else {
//                $.ajax({
//                    url: '../profileViewer/profileData.php?function=profiledataedituser',
//                    type: 'post',
//                    processData: false, // important
//                    contentType: false, // important
//                    data: m_data,
//                    success: function (data) {
//                        if (data == 1) {
//                            $('#updatesuccessmsg').show();
//                            $('#updatesuccessmsg').html('<span style="color:green"></span>');
//                            setTimeout(function () {
//                                $('#profileDisplay').modal('hide');
//                                location.reload();
//                            }, 1500);
//                        } else {
//                            $('#updatesuccessmsg').show();
//                            $('#updatesuccessmsg').html('<span style="color:red"></span>');
//                            setTimeout(function () {
//                                $('#profileDisplay').modal('hide');
//                                location.reload();
//                            }, 1500);
//                        }
//                    }
//                })
//            }
//        }
//    })
//}

function uploadalllogosupdte() {
  var userlogo = $('#userlogorole').val();
  var custype = $('#customertype').val();
  var uldtext = $('#uploadtext').val();
  var Dlogo = $('input[name=logos]:checked').val();
  var Clogo = $('input[name=clientlogo]:checked').val();
  var uploadLPLogo = $('input[name=fileuploader2]')[0].files[0];
  var uploadFootLogo = $('input[name=fileuploader3]')[0].files[0];
  var uploadClientLogo = $('input[name=fileuploader4]')[0].files[0];
  //    alert($('input[name=fileuploader2]')[0].files[0].size);

  if (Dlogo != 'on' && Clogo != 'on') {
    $('#updatesuccessmsglogo').show();
    $('#updatesuccessmsglogo').html('<span style="color:red">Please Select Checkbox to Upload Logo</span>');
    return false;
  }

  if (uploadLPLogo != undefined) {
    var uploadsize = $('input[name=fileuploader2]')[0].files[0].size;
    if (uploadsize >= 4000) {
      $('#updatesuccessmsglogo').show();
      $('#updatesuccessmsglogo').html('<span style="color:red">Please Upload Left Pane Logo of Size 4KB</span>');
      return false;
    }
  }

  if (uploadFootLogo != undefined) {
    var uploadfooter = $('input[name=fileuploader3]')[0].files[0].size;
    if (uploadfooter >= 4000) {
      $('#updatesuccessmsglogo').show();
      $('#updatesuccessmsglogo').html('<span style="color:red">Please Upload Left Pane Logo of Size 4KB</span>');
      return false;
    }
  }

  if ($('#clientlogo').is(':checked')) {
    if (uploadClientLogo == undefined) {
      $('#updatesuccessmsglogo').show();
      $('#updatesuccessmsglogo').html('<span style="color:red">Please Select Client Logo</span>');
      return false;
    }
  }

  var m_data = new FormData();
  m_data.append('function', 'profiledataedit');
  m_data.append('upload_logo', uploadLPLogo); // left pane logo
  m_data.append('upload_footerlogo', uploadFootLogo); // footer logo
  m_data.append('upload_clientlogo', uploadClientLogo); // client logo
  m_data.append('text', uldtext);
  m_data.append('csrfMagicToken', csrfMagicToken);
  $.ajax({
    url: '../profileViewer/profileData.php',
    type: 'post',
    processData: false, // important
    contentType: false, // important
    data: m_data,
    success: function (data) {
      if (data == 1) {
        $('#updatesuccessmsglogo').show();
        $('#updatesuccessmsglogo').html('<span style="color:green">uploaded successfully</span>');
        setTimeout(function () {
          $('#profileDisplay').modal('hide');
          location.reload();
        }, 1500);
      } else {
        $('#updatesuccessmsglogo').show();
        $('#updatesuccessmsglogo').html('<span style="color:red">not updated successfully</span>');
        //                                $('#updatesuccessmsg').html('<span style="color:red"></span>');
        setTimeout(function () {
          $('#profileDisplay').modal('hide');
          location.reload();
        }, 1500);
      }
    },
    error: function (data) {},
  });
}

//function userlogo(){
//    var fu1 = document.getElementById("fileuploader2");
//    alert("You selected " + fu1.value);
//}

/* ========= image path code starts =========*/
$(function () {
  return;
  var userid = $('#logged_id').val();
  $.ajax({
    url: '../profileViewer/profileData.php',
    type: 'POST',
    dataType: 'json',
    data: { function: 'proimage', userid: userid, csrfMagicToken: csrfMagicToken },
    async: true,
    success: function (data) {
      if (data.img == '') {
        $('#profImage').show();
        return false;
      } else {
        $('#profImage').hide();
        $('#imgshow').html("<img src='" + data.img + "' />");
      }
    },
  });
});

/* ========== change profile picture code starts ======= */
jQuery(document).ready(function ($) {
  var profileFname = $('#profileFname').val();
  var profileadminMail = $('#profileadminMail').val();
  var profilecType = $('#profilecType').val();
  var profiletimeZone = $('#profiletimeZone').val();

  $('#user').val(profileadminMail);
  $('#custtype').val(profilecType);
  $('.user-name').html(profileFname);
  $('#uname').attr('title', profileFname);
  var $uploadCrop;
  function readFile(input) {
    if (input.files && input.files[0]) {
      /* file size in MB */
      if ((input.files[0].size / (1024 * 1024)).toFixed(2) <= 5) {
        var reader = new FileReader();

        (reader.onload = function (e) {
          $('.upload-profile-image').addClass('ready');
          $uploadCrop
            .croppie('bind', {
              url: e.target.result,
            })
            .then(function () {
              //                console.log('jQuery bind complete');
              $('.upload-result').removeAttr('disabled');
            });
        }),
          reader.readAsDataURL(input.files[0]);
      } else {
        $('#successuplaod').show();
        $('#successuplaod').html('<span style="color:red">Please upload image of size less than 5 MB</span>');
      }
    } else {
      //       swal("Sorry - you're browser doesn't support the FileReader API");
    }
  }

  /*     $uploadCrop = $('#upload-profile-image').croppie({

        viewport: {
            width: 200,
            height: 200,
            type: 'circle'
        },
        boundary: {
            width: 250,
            height: 250
        },
        enableExif: true
    }); */

  $('#upload').on('change', function () {
    readFile(this);
  });

  $('.upload-result').on('click', function (ev) {
    var fname = $('#firstname').val();
    var lname = $('#lastname').val();
    var pnumb = $('#phone_no').val();
    var logoimg = $('#fileuploader2').val();
    var logofootimg = $('#fileuploader3').val();
    var clientimg = $('#fileuploader4').val();
    //    var uldtext     = $('#uploadtext').val();
    var radio = $('input[name=logos]:checked').val();
    var clientlogo = $('input[name=clientlogo]:checked').val();
    var userlogo = $('#userlogorole').val();
    var timeZon = $('#timeZone').val();
    var timezoneDB = $('#Timezone').val();
    var timeZone = '';

    var name = $('#upload').val();

    if (name != '') {
      $uploadCrop.croppie('result', 'canvas').then(function (resp) {
        $.ajax({
          url: '../profileViewer/profileData.php',
          type: 'POST',
          dataType: 'json',
          data: { function: 'fileupload', imagebase64: resp, csrfMagicToken: csrfMagicToken },
          success: function (data) {
            if (data.res == 1 || data.res == '1') {
              $('#profImage').hide();
              $('#successuplaod').show();
              $('#imgshow').html("<img src='" + data.src + "' />");
              $('#successuplaod').html('<span style="color:green">Profile Updated Successfully</span>');
              $('#successuplaod').fadeOut(2000);
              setTimeout(function () {
                $('#change-profile').modal('hide');
                location.reload();
              }, 3000);
            } else {
              $('#successuplaod').show();
              $('#successuplaod').html('<span style="color:red">Profile Updated Failed</span>');
              $('#successuplaod').fadeOut(2000);
            }
          },
        });
      });
    } else {
      var nameFilter = /^[a-zA-Z]+$/;
      if (fname == '') {
        $('#updatesuccessmsg').show();
        $('#updatesuccessmsg').html('<span style="color:red">Please enter First name</span>');
        $('#updatesuccessmsg').fadeOut(3600);
        return false;
      }
      if (!nameFilter.test(fname)) {
        $('#updatesuccessmsg').show();
        $('#updatesuccessmsg').html('<span style="color:red">Please enter only alphabet in First name</span>');
        $('#updatesuccessmsg').fadeOut(3600);
        return false;
      }
      if (lname == '') {
        $('#updatesuccessmsg').show();
        $('#updatesuccessmsg').html('<span style="color:red">Please enter Last name</span>');
        $('#updatesuccessmsg').fadeOut(3600);
        return false;
      }
      if (!nameFilter.test(lname)) {
        $('#updatesuccessmsg').show();
        $('#updatesuccessmsg').html('<span style="color:red">Please enter only alphabet in Last name</span>');
        $('#updatesuccessmsg').fadeOut(3600);
        return false;
      }
      if ($.trim(pnumb) == '') {
        $('#updatesuccessmsg').show();
        $('#updatesuccessmsg').html('<span style="color:red">Please enter valid Phone Number</span>');
        $('#updatesuccessmsg').fadeOut(3600);
        return false;
      }
      if ($.trim(pnumb).length < 10) {
        $('#updatesuccessmsg').show();
        $('#updatesuccessmsg').html('<span style="color:red">Phone Number should be minimum 10 digits</span>');
        $('#updatesuccessmsg').fadeOut(3600);
        return false;
      }
      if ($.trim(pnumb).length >= 20) {
        $('#updatesuccessmsg').show();
        $('#updatesuccessmsg').html('<span style="color:red">Phone Number should not be more than 20 digits</span>');
        $('#updatesuccessmsg').fadeOut(3600);
        return false;
      }
      if (!validatePhoneNumber($.trim(pnumb))) {
        $('#updatesuccessmsg').show();
        $('#updatesuccessmsg').html('<span style="color:red">Phone Number can contain +,-,(,),.,space,digits only</span>');
        $('#updatesuccessmsg').fadeOut(3600);
        return false;
      }

      if (timezoneDB == 'default') {
        timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;
        if (timeZone == 'Asia/Calcutta') {
          timeZone = 'Asia/Kolkata';
        }
      } else {
        timeZone = timeZon;
      }

      var m_data = new FormData();
      $('#add_successMsg').html('');
      m_data.append('firstname', fname);
      m_data.append('lastname', lname);
      m_data.append('phone_no', pnumb);
      m_data.append('time_zone', timeZone);
      m_data.append('csrfMagicToken', csrfMagicToken);
      if (userlogo == 1 || userlogo == '1') {
        m_data.append('upload_logo', $('input[name=fileuploader2]')[0].files[0]); // left pane logo
        m_data.append('upload_footerlogo', $('input[name=fileuploader3]')[0].files[0]); // footer logo
        m_data.append('upload_clientlogo', $('input[name=fileuploader4]')[0].files[0]); // client logo
      }
      $('#updatesuccessmsg').html(
        '<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." style="width:auto !important;padding-left:22% !important;"/>',
      );

      $.ajax({
        type: 'post',
        url: '../profileViewer/profileData.php?function=profiledataedit',
        processData: false, // important
        contentType: false, // important
        data: m_data,
        success: function (data) {
          if (radio == 'on' || clientlogo == 'on') {
            if (data == 1 || data == '1') {
              m_data.append('function', 'profiledataeditlogoText');
              $.ajax({
                url: '../profileViewer/profileData.php',
                type: 'post',
                processData: false, // important
                contentType: false, // important
                data: m_data,
                success: function (data) {
                  if (data == 1) {
                    $('#updatesuccessmsg').show();
                    $('#updatesuccessmsg').html('<span style="color:green">Profile updated successfully</span>');
                    setTimeout(function () {
                      $('#profileDisplay').modal('hide');
                      location.reload();
                    }, 1500);
                  } else {
                    $('#updatesuccessmsg').show();
                    //                                $('#updatesuccessmsg').html('<span style="color:red">Profile not updated successfully</span>');
                    $('#updatesuccessmsg').html('<span style="color:red">Profile not updated successfully</span>');
                    setTimeout(function () {
                      $('#profileDisplay').modal('hide');
                      location.reload();
                    }, 1500);
                  }
                },
              });
            } else {
              $('#updatesuccessmsg').show();
              $('#updatesuccessmsg').html('<span style="color:red">Menu Image size less than 3 KB and Footer Image size less than 6 KB</span>');
            }
          } else {
            m_data.append('function', 'profiledataedituser');
            $.ajax({
              url: '../profileViewer/profileData.php',
              type: 'post',
              processData: false, // important
              contentType: false, // important
              data: m_data,
              success: function (data) {
                if (data == 1) {
                  $('#updatesuccessmsg').show();
                  $('#updatesuccessmsg').html('<span style="color:green">Profile updated successfully</span>');
                  setTimeout(function () {
                    $('#profileDisplay').modal('hide');
                    location.reload();
                  }, 1500);
                } else {
                  $('#updatesuccessmsg').show();
                  $('#updatesuccessmsg').html('<span style="color:red">Profile not updated successfully</span>');
                  setTimeout(function () {
                    $('#profileDisplay').modal('hide');
                    location.reload();
                  }, 1500);
                }
              },
            });
          }
        },
      });
    }
  });
});

function imageIsLoaded(e) {
  $('#myImg').html(e.target.result);
}
$('#upload').mousedown(function () {
  $('#successuplaod').hide();
});

$('#cancelPopup').click(function () {
  var image = $('.cr-image').attr('src');
  if (image != 'undefined') {
    $('.cr-image').removeAttr('src');
    $('.upload-result').attr('disabled', 'disabled');
  }
});

$('#firstname,#lastname,#phone_no').keyup(function () {
  $('#updatesuccessmsg').hide();
});

$('#removemenu').mousedown(function () {
  $('#updatesuccessmsg,#updatesuccessmsglogo').hide();
});

$('#removefooter').mousedown(function () {
  $('#updatesuccessmsg,#updatesuccessmsglogo').hide();
});

$('#removeclient').mousedown(function () {
  $('#updatesuccessmsglogo').hide();
});
$('#uploadclientlogo').mousedown(function () {
  $('#updatesuccessmsglogo').hide();
});

$('#uploadtext').mousedown(function () {
  $('#updatesuccessmsg').hide();
});

function Dashlogo() {
  $('#updatesuccessmsglogo').hide();
}
function Clintlogo() {
  $('#updatesuccessmsglogo').hide();
}

function validatePhoneNumber(phnum) {
  //    var filter = /^(\+\d{1,2}\s)?\(?\d{3}\)?[\s.-]?\d{3}[\s.-]?\d{4}$/;
  var filter = /\(?([0-9]{3})\)?([ .-]?)([0-9]{3})\2([0-9]{4})/;
  if (filter.test(phnum)) {
    return true;
  } else {
    return false;
  }
}

// ************** Reset Password Start Here ***************** //

$('#resetPass_header_option').click(function () {
  $('.error_msg').html('');
  $('#passwordval').val('');
  $('#repassword').val('');
  $('#oldpasswordval').val('');
});

function resetPass() {
  $('.error_msg').html('');
  var oldpass = document.getElementById('oldpasswordval').value;
  var password = document.getElementById('passwordval').value;
  var repPassword = document.getElementById('repassword').value;

  if (oldpass === '') {
    $('.error_msg').html("<lable style='color:red;'>Please enter old password</lable>");
    return false;
  } else if (password === '') {
    $('.error_msg').html("<lable style='color:red;'>Please enter new password</lable>");
    return false;
  } else if (repPassword === '') {
    $('.error_msg').html("<lable style='color:red;'>Please enter confirm password</lable>");
    return false;
  } else if (password !== repPassword) {
    $('.error_msg').html("<lable style='color:red;'>Passwords you have entered do not match with each other</lable>");
    return false;
  } else if (password.length < 8 || repPassword.length > 20) {
    $('.error_msg').html("<lable style='color:red;'>Password should be minimum 8 and maximum 20 characters</lable>");
    return false;
  } else if (!validatePassword(password) || !validatePassword(repPassword)) {
    $('.error_msg').html(
      "<lable style='color:red;'>Password should be contain lowercase letters, uppercase letters, numbers, and special characters</lable>",
    );
    return false;
  } else {
    //alert(123);
    updatePassword(password, oldpass);
  }
}

$('.form-control').click(function (e) {
  $('.error_msg').html('');
});

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

function updatePassword(password, oldpass) {
  $('.error_msg').html('');
  //var mainloginform = $.trim(document.getElementById('mainloginform').value).toLowerCase();
  $.ajax({
    type: 'POST',
    url: '../lib/l-custAjax.php',
    data: {
      function: 'CUSTAJX_ResetPassword',
      password: password,
      oldpass: oldpass,
      csrfMagicToken: csrfMagicToken,
      //    'mainloginform': mainloginform
    },
    success: function (result) {
      console.log(result);
      result = $.trim(result);
      if (result === '1' || result === 1) {
        $.notify('Password updated successfully');
        setTimeout(function () {
          rightContainerSlideClose('reset-pass-container');
        }, 3000);
      } else if (result === '2' || result === 2) {
        $('.error_msg').html("<lable style='color:red;'>This password is already in use, please use different password.</lable>");
      } else if (result === '3' || result === 3) {
        $('.error_msg').html("<lable style='color:red;'>Password cannot contain username</lable>");
      } else if (result === '4' || result === 4) {
        $('.error_msg').html("<lable style='color:red;'>Password cannot contain user's first name</lable>");
      } else if (result === '5' || result === 5) {
        $('.error_msg').html("<lable style='color:red;'>Password cannot contain user's last name</lable>");
      } else if (result === '6' || result === 6) {
        $('.error_msg').html(
          "<lable style='color:red;'>Old Password entered is incorrect. If you dont remember your old password, please logout and request for a password reset email using Forgot Password</lable>",
        );
      } else {
        $('.error_msg').html("<lable style='color:red;'>Some error occurred</lable>");
      }
    },
  });
}
$('.chkbtn').on('click', function (eve) {
  //    var file_data = $('#profpictr').prop('files')[0];
  //    alert(typeof file_data);
  //    var resp = JSON.stringify(file_data);
  //    alert(typeof resp);
  var data;

  var fname = $('#firstname').val();
  var lname = $('#lastname').val();
  var pnumb = $('#phone_no').val();
  var user_email = $('#user_email').val();
  var user_role = $('#dashbaord_user_role').val();
  var timezone = $('#timeZone').val();

  data = {
    firstname: fname,
    lastname: lname,
    phone_no: pnumb,
    timezone: timezone,
    csrfMagicToken: csrfMagicToken,
    function: 'uploadProfileDatawithimg',
  };
  console.log(data);
  $.ajax({
    url: '../profileViewer/profileData.php', // point to server-side PHP script
    data: data,
    type: 'post',
    success: function (datas) {
      console.log(datas);
      var data = JSON.parse(datas);
      if (data.res == 1 || data.res == '1') {
        $.notify('Successfully updated Profile');
        //$("#respo_msg").css("display", "block");
        rightContainerSlideClose('profilepicture-add-container');
        //                setTimeout(function () {
        ////                     rightContainerSlideClose('profilepicture-add-container');//to open slider
        //                    location.reload();
        //                }, 1000);
      } else {
        //var msg_val=$("#respo_failmsg").val();
        $.notify('Failed to update the profile. Please try again');
        //$("#respo_failmsg").css("display", "block");
        rightContainerSlideClose('profilepicture-add-container'); //to open slider
      }
    },
  });
});

function GoToResetPassword() {
  $('.rightslide-container-close').trigger('click');
  if($('#profilepicture-add-container').css('width').replace('%', '').replace('px','').replace('em','') > 0){
    $('.profilepicture-container-close').trigger('click');
  }
  
  rightContainerSlideOn('reset-pass-container');
}
// ************** Reset Password Ends Here ***************** //

// ************** Menu list ***************** //

const getDashboardList = () => {
  if (window.sideBarVizLoadProgress) {
    return;
  }
  window.sideBarVizLoadProgress = true;
  $.ajax({
    type: 'POST',
    url: '../insight/dashboardFunction.php',
    data: { function: 'dash_List', type: 'dash', csrfMagicToken: csrfMagicToken },
    dataType: 'json',
    success: function (data) {
      window.sessionStorage.setItem('menu', JSON.stringify(data['1']));
      window.sessionStorage.setItem('menuGroupped', JSON.stringify(data['2']));
      showDashboard();
    },
  });
};

getDashboardList();
