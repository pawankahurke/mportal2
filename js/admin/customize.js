// Customize Client UI JS changes
$(document).ready(function () {
    maxTextLength = 49; // Length for welcome message = 50

    welcomeMsgMaxLength = 180;

    var wcMsgLen = $("#welcomeMsg").val().length;
    $("#remChar").html((maxTextLength - wcMsgLen) + 1);

    var landingwcMsgTitleLen = $('#landingwcMsgTitle').val().length;
    $("#landingwcMsgTitleCharRem").html((maxTextLength - landingwcMsgTitleLen) + 1);

    var landingwcMsgLen = $('#landingwcMsgTitle').val().length;
    $("#landingwcMsgCharRem").html((welcomeMsgMaxLength - landingwcMsgLen) + 1);
});

function changeDisplay(hideClass, showClass, pageNameText) {
    $('.' + hideClass).hide();
    $('.' + showClass).show();

    $('#pageName').html(pageNameText);
}

$("#welcomeMsg").keyup(function () {
    if (this.value.length > maxTextLength) {
        return false;
    }
    $("#remChar").html(maxTextLength - this.value.length);
});

// Main function
// Logo image change script
$("#pub_logo").on("change", function () {
    var file_data = $("#pub_logo").prop("files")[0];
    var logo_data = new FormData();
    var logo_name = $("#pub_logo").prop("files")[0]["name"];

    logo_data.append("file", file_data);
    logo_data.append("function", "headerlogo");

    $("#logo_name").html(logo_name).css({color: "black"});
    $(".logo_loader").show();

    commonAjaxFunction(logo_data, "headerlogo");
});

$("#shortcut_icon").on("change", function () {
    var file_data = $("#shortcut_icon").prop("files")[0];
    var shortcut_data = new FormData();
    var shortcut_name = $("#shortcut_icon").prop("files")[0]["name"];

    shortcut_data.append("file", file_data);
    shortcut_data.append("function", "shortcuticon");

    $("#shortcut_logo").html(shortcut_name).css({color: "black"});
    $(".shortcut_loader").show();

    commonAjaxFunction(shortcut_data, "shortcuticon");
});

$("#remove_logo").click(function () {
    $("#pub_logo").val("");
    $("#logo_name").html("");

    $(".logo").attr("src", "../assets/img/logo.png");
});

$("#remove_icon").click(function () {
    $("#shortcut_icon").val("");
    $("#shortcut_logo").html("");

    $(".shorcutIcon").attr("src", "../assets/img/logo.png");
});


// Background image change script
$("#pub_bgimage").on("change", function () {
    var file_data = $("#pub_bgimage").prop("files")[0];
    var bgimage_data = new FormData();
    var bgimg_name = $("#pub_bgimage").prop("files")[0]["name"];

    bgimage_data.append("file", file_data);
    bgimage_data.append("function", "bgimage");

    $("#bgimg_name").html(bgimg_name).css({color: "black"});
    $(".bgimg_loader").show();
    commonAjaxFunction(bgimage_data, "bgimage");
});

$("#remove_bgimg").click(function () {
    $("#pub_bgimage").val("");
    $("#bgimg_name").html("");
    var file_name = $('#fileuploadedname').val();

    deleteImage(file_name);
    $(".bg-setup-img").css({
        background: "url(../assets/img/bask-bg-img.png) no-repeat"
    });
});

$("#remove_landingbgimage").click(function () {
    $("#pub_landingbgimage").val("");
    $("#landingbgimg_name").html("");
    var file_name = $('#fileuploadedname').val();

    deleteImage(file_name);
    $(".bg-setup-img").css({
        background: "url(../assets/img/bask-bg-img.png) no-repeat"
    });
});

function deleteImage(file_name) {
    $.ajax({
        url: '../lib/l-customizeUIFunc.php',
        type: "DELETE",
        data: {'function': 'removeImg', 'filename': file_name, 'csrfMagicToken': csrfMagicToken},
        success: function (response) {
            console.log("success");
        },
        error: function () {
            console.log("error");
        }
    });
}

// Welcome Message script
var welcomeMsgVal = $('#welcomeMsgSavedVal').val();
var supportPhoneVal = $('#supportPhoneVal').val();
//landing page items
var landingwcMsgTitleHidden = $('#landingwcMsgTitleHidden').val();
var landingwcMsgHidden = $('#landingwcMsgHidden').val();
// Email Template Page
var email_titleHidden = $('#email_titleHidden').val();
var email_bodyHidden = $('#email_bodyHidden').val();
var brandingtext1 = $('#branding_text1').val();
var brandingtext2 = $('#branding_text2').val();
var brandingtext3 = $('#branding_text3').val();
var brandingtext4 = $('#branding_text4').val();
var brandingtext5 = $('#branding_text5').val();
var urltext1 = $('#url_text1').val();
var urltext2 = $('#url_text2').val();
var urltext3 = $('#url_text3').val();
var urltext4 = $('#url_text4').val();
var urltext5 = $('#url_text5').val();
var app = angular.module("customizeApp", []);

app.controller("customizeController", function ($scope) {
    $scope.welcomeMsg = welcomeMsgVal; //"Welcome to SelfHeal Client Setup";
    $scope.supportPhNo = supportPhoneVal;
    $scope.landingwcMsgTitle = landingwcMsgTitleHidden;
    $scope.landingwcMsg = landingwcMsgHidden;
    $scope.emailTitle = email_titleHidden;
    $scope.emailBody = email_bodyHidden;
    $scope.branding1_text = brandingtext1;
    $scope.branding2_text = brandingtext2;
    $scope.branding3_text = brandingtext3;
    $scope.branding4_text = brandingtext4;
    $scope.branding5_text = brandingtext5;
    $scope.branding1_url = urltext1;
    $scope.branding2_url = urltext2;
    $scope.branding3_url = urltext3;
    $scope.branding4_url = urltext4;
    $scope.branding5_url = urltext5;
});

$("#welcomeMsg").focusout(function () {
    if ($(this).val().length === 0) {
        $("#welcomeMsg").val("Welcome to SelfHeal Client Setup");
        $("#welcomeMsgText").html("Welcome to SelfHeal Client Setup");
    }
});

// Header color script
$("#headerColor").on("change", function () {
    var headerColor = $(this).val();
    $(".headerWrap").css({"background-color": headerColor});
});

// Header color script
$("#footerColor").on("change", function () {
    var footerColor = $(this).val();
    $(".footerWrap").css({"background-color": footerColor});
});

// Header color script
$("#buttonColor").on("change", function () {
    var buttonColor = $(this).val();
    $(".buttonColor").attr("style", "background-color: " + buttonColor + " !important");
});
termsError = true;
// Terms and Conditions validation script
$("#termsAndConditions").focusout(function () {
    var termsLink = $("#termsAndConditions").val();
    var pattern = /^(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/;
    if (termsLink != "") {
        if (!pattern.test(termsLink)) {
            $("#tcErr").html("* Not a valid url").css({"font-style": "italic"});
            termsError = true;
            return false;
        } else {
            termsError = false;
            $("#tcErr").html("");
            $("#termsAgreeText span a").attr("href", termsLink);
        }
    } else {
        $("#tcErr").html("");
    }
});

// Support phone no validation script
/*$("#supportPhone").focusout(function () {
 var supportPhone = $("#supportPhone").val();
 var phoneno = /^\(?([0-9]{3})\)?[ ]?([0-9]{3})[ ]?([0-9]{4})$/;
 if (supportPhone != "") {
 if (!supportPhone.match(phoneno)) {
 $("#phnErr").html("The Phone Number must contain numbers only eg: (800) 555 5555");
 } else {
 $("#phnErr").html("");
 }
 } else {
 $("#phnErr").html("");
 }
 });*/

function commonAjaxFunction(form_data, type) {

    var custcid = $('#customizeCID').val();
    var custpid = $('#customizePID').val();

    form_data.append('custcid', custcid);
    form_data.append('custpid', custpid);
    form_data.append('csrfMagicToken', csrfMagicToken);

    $.ajax({
        url: "../lib/l-customizeUIFunc.php", // point to server-side PHP script
        //dataType: 'text', // what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,
        type: "post",
        success: function (response) {
            $(".logo_loader, .bgimg_loader, .lpbgimg_loader, .shortcut_loader").hide();
            var data = JSON.parse(response);
            var filename = data['bg_filename'];
            $('#fileuploadedname').val(filename);

            if (data["status"] === "error") {
                if (type === "headerlogo") {
                    $("#logo_name").html(data["message"]).css({color: "red"});
                } else if (type === "bgimage") {
                    $("#bgimg_name").html(data["message"]).css({color: "red"});
                } else if (type === 'landingbgimage') {
                    $("#landingbgimg_name").html(data["message"]).css({color: "red"});
                } else if (type === 'shortcuticon') {
                    $("#shortcut_logo").html(data["message"]).css({color: "red"});
                }
                return;
            } else if (data["status"] === "success") {
                if (type === "headerlogo") {
                    var logo = data["logo_filename"];
                    $(".logo").attr("src", "temp/" + logo);
                    $("#emailLogoPath, #emailLogoPathy").attr("src", "temp/" + logo);
                } else if (type === "bgimage") {
                    var bgimage = data["bg_filename"];
                    $(".bg-setup-img").css({background: "url(temp/" + bgimage + ") no-repeat"});
                } else if (type === "landingbgimage") {
                    var lp_bgimage = data["lp_bg_filename"];
                    $(".lp-bg-setup-img").css({background: "url(temp/" + lp_bgimage + ") no-repeat"});
                } else if (type === "shortcuticon") {
                    var shortcut = data["shortcut_filename"];
                    $(".shorcutIcon").attr("src", "temp/" + shortcut);
                }
            } else {
                //alert("Failed : Some error occurred!");
                if (type === "headerlogo") {
                    $("#logo_name").html("Some error occurred!").css({color: "red"});
                } else if (type === "bgimage") {
                    $("#bgimg_name").html("Some error occurred!").css({color: "red"});
                } else if (type === "landingbgimage") {
                    $("#landingbgimg_name").html("Some error occurred!").css({color: "red"});
                }
                return;
            }
        }
    });
}

function brandingCommonAjaxFunction(form_data, type) {

    var custcid = $('#customizeCID').val();
    var custpid = $('#customizePID').val();

    form_data.append('custcid', custcid);
    form_data.append('custpid', custpid);
    form_data.append('csrfMagicToken', csrfMagicToken);

    $.ajax({
        url: "../lib/l-customizeUIFunc.php", // point to server-side PHP script
        //dataType: 'text', // what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,
        type: "post",
        success: function (response) {
            $("." + type + "_loader").hide();
            var data = JSON.parse(response);

            if (data["status"] === "success") {
                var brandingFile = data["filename"];
                $("." + type + " img").attr('src', 'temp/' + brandingFile);

            } else if (data['status'] === 'error') {
                $("#" + type + "_image").html(data["message"]).css({'color': 'red'});
                //console.log('Error : ' + data["message"]);
            } else {
                $("#" + type + "_image").html('Some error occured!').css({'color': 'red'});
                //console.log('Some error occured!');
            }
        }
    });
}

// Saving the complete customized details
function saveConfiguration() {
    //var uploadLogo = $("#pub_logo").val();
    //var uploadbgimage = $("#pub_bgimage").val();
    var welcomeMsg = $.trim($("#welcomeMsg").val());
    var headerColor = $("#headerColor").val();
    var footerColor = $("#footerColor").val();
    var buttonColor = $("#buttonColor").val();
    var termsLink = $("#termsAndConditions").val();
    var supportPhone = $("#supportPhone").val();
    var chatUrl = $('#chatUrl').val();

    var customizeCID = $('#customizeCID').val();
    var customizePID = $('#customizePID').val();
    /*var qstring = "&custcid=" + customizeCID + "&custpid=" + customizePID;

    var datastr = "&hdrcolor=" + headerColor + "&ftrcolor=" + footerColor + "&btncolor="
            + buttonColor + "&welcomemsg=" + welcomeMsg + "&termslink=" + termsLink
            + "&supportphone=" + supportPhone + "&chaturl=" + chatUrl + qstring;*/
    var dataobj = {
        'function': 'saveUpdateConfiguration',
        'hdrcolor': headerColor,
        'ftrcolor': footerColor,
        'btncolor': buttonColor,
        'welcomemsg': welcomeMsg,
        'termslink': termsLink,
        'supportphone': supportPhone,
        'chaturl': chatUrl,
        'custcid': customizeCID,
        'custpid': customizePID,
        'csrfMagicToken': csrfMagicToken
    };

    $.ajax({
        url: "../lib/l-customizeUIFunc.php",
        type: "POST",
        data: dataobj,
        success: function (data) {
            if ($.trim(data) === "success") {
                goToScreen('brandingUI');
            }
        },
        error: function (err) {
            console.log(err);
        }
    });
}

/**
 * JS funcitonality for the Branding page
 * @param screenui
 */

function goToScreen(screenui) {
    $(".rightCol").hide();
    $("." + screenui).show();


    switch (screenui) {
        case 'brandingUI' :
            $('#pageName').html('Customize the marketing messages');
            break;
        case 'installerUI':
            $('#pageName').html('Upload installer messaging');
            break;
        case 'landingPageUI':
            $('#pageName').html('Customize the software landing page');
            break;
        case 'emailTemplateUI':
            $('#pageName').html('Customize Emails');
            break;
        case 'finishScreenUI':
            $('#pageName').html('Finish Screen');
            break;
        default :
            $('#pageName').html('Customize the marketing messages');
            break;
    }
}

var slideIndex = 0;
var slideIndexx = 0;
rotSpeed = $('#rotationSpeed').val();
showSlides();
showSlidess();
//$(".terms").hide();
function showSlides() {
    var i;
    var slides = document.getElementsByClassName("mySlides");
    var dots = document.getElementsByClassName("dot");
    //var text = document.getElementsByClassName("brand-text");

    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
        //text[i].style.display = "none";
    }
    slideIndex++;
    if (slideIndex > slides.length) {
        slideIndex = 1;
    }
    for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    slides[slideIndex - 1].style.display = "block";
    //text[slideIndex - 1].style.display = "block";
    dots[slideIndex - 1].className += " active";
    setTimeout(showSlides, rotSpeed); // Change image every 2 seconds
}

function showSlidess() {
    var i;
    var slides = document.getElementsByClassName("mySlidess");
    var dots = document.getElementsByClassName("dott");
    //var text = document.getElementsByClassName("brand-text");

    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
        //text[i].style.display = "none";
    }
    slideIndexx++;
    if (slideIndexx > slides.length) {
        slideIndexx = 1;
    }
    for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    slides[slideIndexx - 1].style.display = "block";
    //text[slideIndex - 1].style.display = "block";
    dots[slideIndexx - 1].className += " active";
    setTimeout(showSlidess, rotSpeed); // Change image every 2 seconds
}

// Branding Image change script

function uploadMarketingImages(fileRef, typeVal, jq) {
    var file_data = jq.prop("files")[0];
    var branding_data = new FormData();
    var branding_image = file_data["name"];
    branding_data.append("file", file_data);
    branding_data.append("function", typeVal);

    $("#" + typeVal + "_image").html(branding_image).css({color: "black"});
    $("." + typeVal + "_loader").show();

    brandingCommonAjaxFunction(branding_data, typeVal);
}

$("#remove_branding1").click(function () {
    $("#brandImage1").val("");
    $("#branding1_image").html("");

    $(".branding1 img").attr('src', 'images/group-1.png');
});

$("#remove_branding2").click(function () {
    $("#brandImage2").val("");
    $("#branding2_image").html("");

    $(".branding2 img").attr('src', 'images/group-2.png');
});

$("#remove_branding3").click(function () {
    $("#brandImage3").val("");
    $("#branding3_image").html("");

    $(".branding3 img").attr('src', 'images/group-3.png');
});

$("#remove_branding4").click(function () {
    $("#brandImage4").val("");
    $("#branding4_image").html("");

    $(".branding4 img").attr('src', 'images/group-4.png');
});

$("#remove_branding5").click(function () {
    $("#brandImage5").val("");
    $("#branding5_image").html("");

    $(".branding5 img").attr('src', 'images/group-5.png');
});

function updateRotationSpeed(curRef) {
    rotSpeed = $(curRef).val();
}

function submitMarketingChanges() {
    $('.error').html('');
    var customizeCID = $('#customizeCID').val();
    var customizePID = $('#customizePID').val();
    var rotationSpeed = $('#rotationSpeed').val();

    var url1 = $('#branding1_url').val();
    var url2 = $('#branding2_url').val();
    var url3 = $('#branding3_url').val();
    var url4 = $('#branding4_url').val();
    var url5 = $('#branding5_url').val();
    if (url2 == 'undefined' || url3 == 'undefined' || url4 == 'undefined' || url5 == 'undefined') {
        url2 = '';
        url3 = '';
        url4 = '';
        url5 = '';
    }
    var errorVal = 0;
    errorVal = validateURL();
    if (errorVal === 0) {
        var text1 = $('#branding1_text').val();
        var text2 = $('#branding2_text').val();
        var text3 = $('#branding3_text').val();
        var text4 = $('#branding4_text').val();
        var text5 = $('#branding5_text').val();
        if (text2 == 'undefined' || text3 == 'undefined' || text4 == 'undefined' || text5 == 'undefined') {
            text2 = text3 = text4 = text5 = '';
        }
        /*var qstring = "rotspeed=" + rotationSpeed + "&custcid=" + customizeCID + "&custpid=" + customizePID + "&URLVal1=" + url1 + "&URLVal2=" + url2 + "&URLVal3=" + url3 + "&URLVal4=" + url4
                + "&URLVal5=" + url5 + "&TextVal1=" + text1 + "&TextVal2=" + text2 + "&TextVal3=" + text3 + "&TextVal4=" + text4 + "&TextVal5=" + text5;*/
        var dataobj = {
            'function': 'saveClientBrandingImages',
            'rotspeed': rotationSpeed,
            'custcid': customizeCID,
            'custpid': customizePID,
            'URLVal1': url1,
            'URLVal2': url2,
            'URLVal3': url3,
            'URLVal4': url4,
            'URLVal5': url5,
            'TextVal1': text1,
            'TextVal2': text2,
            'TextVal3': text3,
            'TextVal4': text4,
            'TextVal5': text5,
            'csrfMagicToken': csrfMagicToken
        };

        $.ajax({
            url: "../lib/l-customizeUIFunc.php",
            type: "POST",
            data: dataobj,
            success: function (data) {
                if ($.trim(data) === "success") {
                    //location.href = "../home/index.php";
                    goToScreen('landingPageUI');
                }
            },
            error: function (err) {
                console.log(err);
            }
        });
    }

}

function validateURL() {
    $('.error').html('');
    var errorVal = 0;
    var arr = [];
    $('.URL_branding').each(function (index, currentElement) {
        var field_name = this.name;
        var field_id = this.id;
        var field_value = $("#" + field_id).val();
        arr[field_name] = field_value;
        if (field_value != '') {
            if (!isUrl(field_value)) {
                $('.error').html('Please Enter valid URL');
                errorVal++;
            }
        }

    });
    return errorVal;
}

function isUrl(s) {
    var regexp = /[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/;
    ;
    if (regexp.test(s)) {
        return true;
    } else {
        return false;
    }
}

function goToDashboard() {
    window.location.href = '../home/index.php';
}

function goToBranding() {
    sweetAlert({
        title: 'Are you sure that you want to navigate away from this page',
        text: "You wont be able to recover the profile details once cancelled",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#050d30',
        cancelButtonColor: '#fa0f4b',
        cancelButtonText: "No, Stay on the page",
        confirmButtonText: 'Yes, Leave this page'
    }).then(function (result) {
        window.location.href = 'branding.php';
    }).catch(function () {
        $(".closebtn").trigger("click");
    });
}

// -- Landing page changes.
$("#landingwcMsgTitle").keyup(function () {
    if (this.value.length > maxTextLength) {
        return false;
    }
    $("#landingwcMsgTitleCharRem").html(maxTextLength - this.value.length);
});

$("#landingwcMsg").keyup(function () {
    if (this.value.length > welcomeMsgMaxLength) {
        return false;
    }
    $("#landingwcMsgCharRem").html(welcomeMsgMaxLength - this.value.length);
});

// Landing Page Background image change function
$("#pub_landingbgimage").on("change", function () {
    var file_data = $("#pub_landingbgimage").prop("files")[0];
    console.log(file_data);
    var bgimage_data = new FormData();
    var bgimg_name = $("#pub_landingbgimage").prop("files")[0]["name"];

    bgimage_data.append("file", file_data);
    bgimage_data.append("function", "landingbgimage");

    $("#landingbgimg_name").html(bgimg_name).css({color: "black"});
    $(".lpbgimg_loader").show();

    commonAjaxFunction(bgimage_data, "landingbgimage");
});

// -- Email Page script
// Header color script
$("#lp_bg_color").on("change", function () {
    var lp_bg_color = $(this).val();
    $(".emailBox").css({"background": lp_bg_color});
});

$("#lp_fg_color").on("change", function () {
    var lp_fg_color = $(this).val();
    $("table.innerMailNew tr td").css({"background-color": lp_fg_color});
});

$("#lp_btn_color").on("change", function () {
    var lp_btn_color = $(this).val();
    $(".btn-md").css({"background": lp_btn_color});
});

$("#lp_btn_fnt_color").on("change", function () {
    var lp_btn_fnt_color = $(this).val();
    $(".btn-md").css({"color": lp_btn_fnt_color});
});

$("#lp_txt_color").on("change", function () {
    var lp_txt_color = $(this).val();
    $("table.innerMailNew tr td").css({"color": lp_txt_color});
});

function saveLandingPageDetails() {
    var customizeCID = $('#customizeCID').val();
    var customizePID = $('#customizePID').val();
    var landingwcMsgTitle = $('#landingwcMsgTitle').val();
    var landingwcMsg = $('#landingwcMsg').val();
    //var qstring = "lpwcmsgtitle=" + landingwcMsgTitle + "&lpwcmsg=" + landingwcMsg + "&custcid=" + customizeCID + "&custpid=" + customizePID;

    var dataobj = {
        'function': 'saveClientLandingPageInfo',
        'lpwcmsgtitle': landingwcMsgTitle,
        'lpwcmsg': landingwcMsg,
        'custcid': customizeCID,
        'custpid': customizePID,
        'csrfMagicToken': csrfMagicToken
    };
    
    $.ajax({
        url: "../lib/l-customizeUIFunc.php",
        type: "POST",
        data: dataobj,
        success: function (data) {
            var dwnUrl = decodeURIComponent(data.split('###')[1]);
            $('a').each(function (index) {
                if ($(this).attr('href') == '%url%') {
                    $(this).attr('href', dwnUrl);
                }
            });
            goToScreen('emailTemplateUI');
        },
        error: function (err) {
            console.log(err);
        }
    });
}

$('#email_template').on('change', function() {
    textFromFileLoaded = '';
    var fileToLoad = $("#email_template").prop("files")[0];
    var template_name = $("#email_template").prop("files")[0]["name"];
    $("#template_name").html(template_name).css({color: "black"});
    
    var fileReader = new FileReader();
    fileReader.onload = function (fileLoadedEvent) {
        textFromFileLoaded = fileLoadedEvent.target.result;
        $('#emailContent').html(textFromFileLoaded);
    };

    fileReader.readAsText(fileToLoad, "UTF-8");
});

function saveEmailTemplateDetails() {
    var customizeCID = $('#customizeCID').val();
    var customizePID = $('#customizePID').val();
    var email_subject = $('#email_subject').val();
    //var email_title = $('#email_title').val();
    var email_body =  $('#emailContent').html(); //textFromFileLoaded;
    //var lp_bg_color = $('#lp_bg_color').val();
    //var lp_fg_color = $('#lp_fg_color').val();
    //var lp_btn_color = $('#lp_btn_color').val();
    //var lp_btn_fnt_color = $('#lp_btn_fnt_color').val();
    //var lp_txt_color = $('#lp_txt_color').val();


    /*var qstring = "emailsub=" + email_subject + "&emailtitle=" + email_title + "&emailbody=" + email_body +
            "&lp_bg_color=" + lp_bg_color + "&lp_fg_color=" + lp_fg_color + "&lp_btn_color=" + lp_btn_color +
            "&lp_btn_fnt_color=" + lp_btn_fnt_color + "&lp_txt_color=" + lp_txt_color +
            "&custcid=" + customizeCID + "&custpid=" + customizePID;*/
    var dataobj = {
        'function': 'save_EmailTemplateInfo',
        'emailsub': email_subject,
        //'emailtitle': email_title,
        'emailbody': email_body,
        //'lp_bg_color': lp_bg_color,
        //'lp_fg_color': lp_fg_color,
        //'lp_btn_color': lp_btn_color,
        //'lp_btn_fnt_color': lp_btn_fnt_color,
        //'lp_txt_color': lp_txt_color,
        'custcid': customizeCID,
        'custpid': customizePID,
        'csrfMagicToken': csrfMagicToken
    };
    
    $.ajax({
        url: "../lib/l-customizeUIFunc.php",
        type: "POST",
        data: dataobj,
        success: function (data) {
            if (data) {
                updateBrandingUrl(customizeCID, customizePID);
                goToScreen('finishScreenUI');
            }
        },
        error: function (err) {
            console.log(err);
        }
    });
}

function SaveAllChanges() {
    setTimeout(function () {
        window.location.href = 'branding.php';
    }, 1000);
}

function EditChanges() {
    var currentLocation = window.location.href;
    var url = new URL(currentLocation);
    var selval = url.searchParams.get("show");
    sweetAlert({
        title: 'Are you sure that you want to Edit the changes?',
        text: "If you edit the changes, you will not be able to undo the action.",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#050d30',
        cancelButtonColor: '#fa0f4b',
        cancelButtonText: "Back To Default",
        confirmButtonText: "Edit Again"
    }).then(function (result) {
        if (result == 'true' || result) {
            location.href = 'customize.php?show=' + selval;
        }
    }).catch(function (reason) {
        //Back To Default
        $.ajax({
            url: "../lib/l-customizeUIFunc.php",
            type: "POST",
            data: {'function': 'createDefaultClientBranding', 'selected': selval, 'csrfMagicToken': csrfMagicToken},
            success: function (data) {
                data = data.trim();
                console.log('success');
                if (data == 'success') {
                    location.href = 'customize.php?show=' + selval;
                }
            },
            error: function (data) {
                console.log('error');
            }
        });
    });
    closePopUp();
}

function updateBrandingUrl(sitename, siteid) {
    $.ajax({
        url: "../lib/l-customizeUIFunc.php",
        type: 'POST',
        data: { 'function': 'updateClientBrandingUrl', 'sitename': sitename, 'siteid': siteid, 'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            console.log(JSON.stringify(data));
        },
        error: function (err) {
            console.log(JSON.stringify(err));
        }
    });
}

$('#testMail').click(function () {
    $('#success_msg').html('');
    var customizeCID = $('#customizeCID').val();
    var customizePID = $('#customizePID').val();
    var emailTo = $('#emailList').val();
    var email_subject = $('#email_subject').val();

    var emailContent = $.trim($('#emailContent').html());

    /*var qstring = "emailTo=" + emailTo + "&custcid=" + customizeCID + "&custpid="
            + customizePID + "&emailContent=" + encodeURIComponent(emailContent)
            + "&emailsub=" + email_subject;*/
    var dataobj = {
        'function': 'sendTestUrlEmail',
        'emailTo': emailTo,
        'custcid': customizeCID,
        'custpid': customizePID,
        'emailContent': encodeURIComponent(emailContent),
        'emailsub': email_subject,
        'csrfMagicToken': csrfMagicToken
    };

    if (emailTo == '') {
        $.notify("Please enter the recipient\'s email address");
    } else {
        $.ajax({
            url: "../lib/l-customizeUIFunc.php",
            type: "POST",
            data: dataobj,
            success: function (data) {
                if (data == 'Mailed Successfully') {
                    setTimeout(function () {
//                   $('#success_msg').html("Email has been sent successfully"); 
                        $.notify("Email has been sent successfully");
                    }, 3000);
                }
                console.log(data);
            },
            error: function (err) {
                console.log(err);
            }
        });
    }
});

var count = 1;
$('#AddNewImages').click(function () {
    if (count < 5) {
        count++;
        $("#Marketing_Image" + count).show();
    } else {
        $.notify("Cannot add more than 5 images");
    }
});

$('#RemoveImages').click(function () {
    if (count == 1) {
        $.notify("Upload one image at the least");
    } else {
        $("#Marketing_Image" + count).slideUp('slow', function () {
            $("#Marketing_Image" + count).hide();
            count--;
        });
    }
});


