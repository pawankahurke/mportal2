// Buy Now Page Details

var dashboardAPIURL = "../lib/l-dashboardAPI.php?url=";

function showBuyNowSkuDetails() {
    $(".customerInfo").hide();
    $(".paymentInfo").hide();
    $("#backToPayButton").hide();
    $("#completePayButton").hide();
    $("#continuePayButton").hide();

    $(".purchseLoader").show();

    $(".buyNowSiteCreationDiv").hide();
    $("#addBuyNowSite").hide();

    $('#buyNowOptionDiv .titleCard').html('Please select a SKU');

    $.ajax({
        url: "../lib/l-dashboardAPI.php?url=servicetemplate&method=GET" + "&csrfMagicToken=" + csrfMagicToken,
        type: "POST",
        success: function (data) {
            // console.log("Option : " + data);
            $(".purchseLoader").hide();
            var statusObj = JSON.parse(data);
            if (statusObj.status == "success") {
                var data = statusObj.result;
                var buyNowSkuList = "";
                for (var k in data) {
                    var rObj = data[k];
                    var rrobj = JSON.stringify(rObj);
                    // var amount = rObj.amount / 100;

                    if (rObj.id == "40" || rObj.id == "41") {
                        buyNowSkuList +=
                                '<option value="' + rObj.id + '">' + rObj.name + "</option>";
                    }
                }
                // console.log('buyNowSkuList -> ' + buyNowSkuList);
                $("#buyNowSkuList").html(buyNowSkuList);
                $(".selectpicker").selectpicker("refresh");
            } else {
                $("#buyNowSkuList").html(
                        '<option value="0">Sku Not Available</option>'
                        );
                $(".selectpicker").selectpicker("refresh");
            }
        },
        error: function (err) {
            console.log("Error : " + err);
        }
    });
}

$("#buyNowSkuList").change(function () {
    var skuid = $(this).val();
    $(".customerInfo").show();
    $("#continuePayButton").show();

    $(".paymentInfo").hide();
    $("#backToPayButton").hide();
    $("#completePayButton").hide();

    $(".buyNowSiteCreationDiv").hide();
    $("#addBuyNowSite").hide();
    $('#buyNowOptionDiv .titleCard').html('Enter Customer Information');
    showCardDetailsPopup("buynow", skuid);
});

function continuePayButton() {

    var errorVal = 0;
    errorVal = validateCustomerInformation();

    if (errorVal === 0) {
        $(".skuSelectionDiv").hide();
        $(".customerInfo").hide();
        $(".paymentInfo").show();
        $("#continuePayButton").hide();
        $("#backToPayButton").show();
        $("#completePayButton").show();
        $('#buyNowOptionDiv .titleCard').html('Enter Payment Information');
    }
}

$("#backToPayButton").click(function () {
    $(".skuSelectionDiv").show();
    $(".customerInfo").show();
    $(".paymentInfo").hide();
    $("#continuePayButton").show();
    $("#backToPayButton").hide();
    $("#completePayButton").hide();
    $('#buyNowOptionDiv .titleCard').html('Enter Customer Information');
});

function showCardDetailsPopup(managing_from, sel_sku_id = "") {
    var fn = $("#getfirstname").text();
    var ln = $("#getlastname").text();
    var email = $("#getemail").text();
    var ctype = $("#getctype").text();

    $("#custInfo_skuid").val(sel_sku_id);
    $("#custInfo_firstname").val(fn);
    $("#custInfo_lastname").val(ln);
    $("#custInfo_emailid").val(email);
    $("#custInfo_address").val("");
    $("#custInfo_country").val("");
    $("#custInfo_city").val("");
    $("#custInfo_postal").val("");
    $("#carddtls_cardname").val("");
    $("#custInfo_phone").val("");
    $("#carddtls_cardno").val("");
    $("#carddtls_cvv").val("");
    $("#carddtls_expiry").val("");
    $(".selectpicker").selectpicker("refresh");

    $("#required_savedPay").html("");
    $("#required_savedPay").text("");
    $("#required_savedPay").show();

    //alert(sel_sku_id);
    $(".loader").hide();
    // $("#custInfo_country").append('<option selected="">--Select your Country--</option>');
    if (managing_from != "buynow") {
        $("#servicebot_Subscribe").attr("style", "background-color:white");
        if (managing_from == "2") {
            //alert('calling');
            $("#skip_subscribtn").attr(
                    "onClick",
                    "startSubchannelTrial('" + managing_from + "')"
                    );
        } else {
            $("#skip_subscribtn").attr(
                    "onClick",
                    "showIntroductorySlide('" + managing_from + "')"
                    );
        }
    } else {
        $("#skip_subscribtn").attr("onClick", "closePaymentModal()");
        $("#skip_subscribtn").show();
    }

    //alert(managing_from);

    // if(managing_from==="1"){
    //     $("#skip_subscribtn").show();
    // }else{
    //     $("#skip_subscribtn").hide();
    // }

    $("#completePayButton").attr(
            "onClick",
            "saveCustPaymentDetails('" + managing_from + "')"
            );
    //input_credit_card(document.getElementById('carddtls_cardno'));
    $("#servicebot_Subscribe").modal({
        backdrop: "static",
        keyboard: false, //Disables click outside of bootstrap modal area to close modal
        show: true
    });
}

function saveCustPaymentDetails(managing_from) {
    //alert("saveCustPaymentDetails");
    var cid = $("#getchannelid").text();

    $("#required_savedPay").html("");
    $("#required_savedPay").text("");
    $("#required_savedPay").show();

    var errorVal = 0;
    errorVal = validatePaymentSubscribeForm();
    //alert(errorVal);
    var py_data = {};

    if (errorVal === 0) {
        py_data["first_name"] = $("#custInfo_firstname").val();
        py_data["last_name"] = $("#custInfo_lastname").val();
        py_data["email"] = $("#custInfo_emailid").val();

        var cardno = $("#carddtls_cardno").val();
        py_data["card_number"] = cardno.replace(/ /g, "");

        py_data["card_cvv"] = $("#carddtls_cvv").val();

        var expirydate = $("#carddtls_expiry").val();
        var splt_exp = expirydate.split("/");

        py_data["card_exp_month"] = splt_exp[0];
        py_data["card_exp_year"] = splt_exp[1];

        py_data["properties"] = {
            type: "NEW",
            country: $("#custInfo_country").val(),
            quantity: "0"
        };
        // py_data['eid']=cid;

        var postSubscribeObj = {data: py_data};
        var skuamt = $("#select_sku_amt").val();
        var skuInterval = $("#select_sku_interval").val();
        var selSkuid = $("#custInfo_skuid").val();

        $("#continuePayButton").attr("disabled", true);
        $("#loader_cardDetails").show();

        commonAjaxCall(dashboardAPIURL + "servicebot/subscription/42" +
                "&method=POST&selSku=" + selSkuid + "&compId=" + cid + "&funcn=savecard",
                JSON.stringify(postSubscribeObj), "").then(function (res) {
            $("#loader_cardDetails").hide();
            var statusObj = JSON.parse(res);

            if (statusObj.status == "success") {
                $("#servicebot_Subscribe").modal("hide");
                $("#card_popup_desc").text("$" + skuamt + " / " + skuInterval);
                if (managing_from != "buynow") {
                    $("#servicebot_cardDetails").attr("style", "background-color:white");
                }

                $("#custBuyNowLickey").val(statusObj.key);
                updateSession('LicenseKey', statusObj.key);
                // Show create site Div and hide Buy Now btn and trial msg div
                $('#trialMsgDiv, #buyNowBtn').hide();
                $(".buyNowSiteCreationDiv").show();
                $('#buyNowOptionDiv .titleCard').html('Enter Site Details');
                $("#addBuyNowSite").show();
                $(".paymentInfo, #backToPayButton, #completePayButton").hide();
            } else {
                $("#completePayButton").attr("disabled", false);

                $("#continuePayButton").attr("disabled", false);
                $("#continuePayButton").css({
                    backgroundColor: "#fa0f4b",
                    color: "#fff"
                });
                $("#continuePayButton").html("Continue to Payment");
                $("#required_savedPay").css("color", "red").html("ERROR: " +
                        JSON.stringify(statusObj.error.code) + " - " +
                        JSON.stringify(statusObj.error.message));
                $("#required_savedPay").show();
                //$("#paymentError").text("Error in signup : "+JSON.stringify(statusObj.error.code)+" - "+JSON.stringify(statusObj.error.message));
            }
        });
    }
}

function updateSession(sessionType, sessionValue) {
    $.ajax({
        url: "../lib/l-ptsAjax.php",
        type: 'POST',
        data: 'function=updateSessionDy&stype=' + sessionType + '&svalue=' + sessionValue + "&csrfMagicToken=" + csrfMagicToken,
        success: function (data) {
            console.log(sessionType + ' - ' + data);
        },
        error: function (error) {
            console.log('Purchase.JS :: UpdateSession :: Error - ' + error);
        }
    });
}

function validateCustomerInformation() {
    $(".error").html("");
    var errorVal = 0;

    $(".ci_py_carddetails").each(function () {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();

        if ($.trim(field_value) === "") {
            $("#required_" + field_id)
                    .css("color", "red")
                    .html(" required");
            errorVal++;
        } else if (field_id == "custInfo_firstname") {
            if (!validate_Alphanumeric(field_value)) {
                $("#required_" + field_id)
                        .css("color", "red")
                        .html("Enter only Alphanumeric values");
                errorVal++;
            }
        } else if (field_id == "custInfo_lastname") {
            if (!validate_Alphanumeric(field_value)) {
                $("#required_" + field_id)
                        .css("color", "red")
                        .html("Enter only Alphanumeric values");
                errorVal++;
            }
        } else if (field_id == "custInfo_emailid") {
            if (!validate_Email(field_value)) {
                $("#required_" + field_id)
                        .css("color", "red")
                        .html("Enter valid email");
                errorVal++;
            }
        } else if (field_id == "custInfo_address") {
            if (!validate_Alphanumeric(field_value)) {
                $("#required_" + field_id)
                        .css("color", "red")
                        .html("Enter only Alphanumeric values");
                errorVal++;
            }
        } else if (field_id == "custInfo_city") {
            if (!validate_Alphanumeric(field_value)) {
                $("#required_" + field_id)
                        .css("color", "red")
                        .html("Enter only Alphanumeric values");
                errorVal++;
            }
        } else if (field_id == "custInfo_postal") {
            if (!validate_ZipCode(field_value)) {
                $("#required_" + field_id)
                        .css("color", "red")
                        .html("Enter valid postal code");
                errorVal++;
            }
        } else if (field_id == "custInfo_phone") {
            if (!validate_Number(field_value)) {
                $("#required_" + field_id)
                        .css("color", "red")
                        .html("Enter valid Phone number");
                errorVal++;
            }
        } /*else if (field_id == "custInfo_country") {
         if ($.trim(field_value) === "--Select your Country--") {
         $("#required_" + field_id)
         .css("color", "red")
         .html(" required");
         errorVal++;
         }
         }*/
    });

    var custCountry = $('#custInfo_country').val();
    if (custCountry === '') {
        $("#required_custInfo_country").css("color", "red").html(" required");
        errorVal++;
    }

    return errorVal;
}

function validatePaymentSubscribeForm() {
    $(".error").html("");
    var errorVal = 0;

    $(".py_carddetails").each(function () {
        var field_id = this.id;
        //alert('Test -> ' + field_id);
        var field_value = $("#" + field_id).val();
        //alert(field_id+"----"+field_value);

        if ($.trim(field_value) === "") {
            $("#required_" + field_id)
                    .css("color", "red")
                    .html(" required");
            errorVal++;
        } else if (field_id == "carddtls_cardno") {
            var fieldVal = field_value.replace(/ /g, "");
            if (fieldVal.length < 16) {
                $("#required_" + field_id)
                        .css("color", "red")
                        .html("Please enter valid card details");
                errorVal++;
            } else {
                if (!validate_creditcardnumber(fieldVal)) {
                    $("#required_" + field_id)
                            .css("color", "red")
                            .html("Please enter valid card details");
                    errorVal++;
                }
            }
        } else if (field_id == "carddtls_cardname") {
            if (!validate_Alphanumeric(field_value)) {
                $("#required_" + field_id)
                        .css("color", "red")
                        .html("Enter only Alphanumeric values");
                errorVal++;
            }
        } else if (field_id == "carddtls_cvv") {
            if (!validate_Number(field_value)) {
                $("#required_" + field_id)
                        .css("color", "red")
                        .html("Invalid");
                errorVal++;
            }
        } else if (field_id == "carddtls_expiry") {
            // if (!validate_Alphanumeric(field_value)) {
            //  $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
            // errorVal++;
            // }
        }
    });

    return errorVal;
}

function validate_creditcardnumber(inputNum) {
    var digit, digits, flag, sum, _i, _len;
    flag = true;
    sum = 0;
    digits = (inputNum + "").split("").reverse();
    for (_i = 0, _len = digits.length; _i < _len; _i++) {
        digit = digits[_i];
        digit = parseInt(digit, 10);
        if ((flag = !flag)) {
            digit *= 2;
        }
        if (digit > 9) {
            digit -= 9;
        }
        sum += digit;
    }
    return sum % 10 === 0;
}

function formatString(e) {
    var inputChar = String.fromCharCode(event.keyCode);
    var code = event.keyCode;
    var allowedKeys = [8];
    if (allowedKeys.indexOf(code) !== -1) {
        return;
    }

    event.target.value = event.target.value
            .replace(
                    /^([1-9]\/|[2-9])$/g,
                    "0$1/" // 3 > 03/
                    )
            .replace(
                    /^(0[1-9]|1[0-2])$/g,
                    "$1/" // 11 > 11/
                    )
            .replace(
                    /^([0-1])([3-9])$/g,
                    "0$1/$2" // 13 > 01/3
                    )
            .replace(
                    /^(0?[1-9]|1[0-2])([0-9]{2})$/g,
                    "$1/$2" // 141 > 01/41
                    )
            .replace(
                    /^([0]+)\/|[0]+$/g,
                    "0" // 0/ > 0 and 00 > 0
                    )
            .replace(
                    /[^\d\/]|^[\/]*$/g,
                    "" // To allow only digits and `/`
                    )
            .replace(
                    /\/\//g,
                    "/" // Prevent entering more than 1 `/`
                    );
}

// Allow only numbers in TextBox (Restrict Alphabets and Special Characters).
function restictAlphaSC(event) {
    var regex = new RegExp("^[0-9]+$");
    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
    if (!regex.test(key)) {
        event.preventDefault();
        return false;
    }
}

function addBuyNowSite() {
    // var cid=$("#getchannelid").text();
    // alert("addDeploymntSite");

    var errorVal = 0;
    var field_value = $("#custBuyNowSiteName").val();
    var sitekey_val = $("#custBuyNowLickey").val();

    if ($.trim(field_value) == "") {
        $("#newProvisionErrorMsg").css("color", "red").html(" required");
        errorVal++;
    } else if ($.trim(sitekey_val) == "") {
        $("#required_sitekey").css("color", "red").html(" required");
        errorVal++;
    } else if ($.trim(field_value) != "" && $.trim(sitekey_val) != "") {
        if (!validate_alphanumeric_underscore(field_value)) {
            $("#newProvisionErrorMsg").css("color", "red").html("Enter only Alphanumeric,Underscore values ");
            errorVal++;
        } else {
            $("#newProvisionErrorMsg").html("");
        }
    }

    if (errorVal === 0) {
        //generateDownloadURL(selected_cid,field_value);
        attachBuyNowSiteKey(field_value, sitekey_val);
    }
}

function attachBuyNowSiteKey(siteName, siteKey) {
    $.ajax({
        url: "../lib/l-ptsAjax.php",
        type: "POST",
        data: "function=attachSiteKey&sitename=" + siteName + "&sitekey=" + siteKey + "&csrfMagicToken=" + csrfMagicToken,
        success: function (res) {
            console.log("Msg : " + JSON.stringify(res));
            var data = JSON.parse(res);
            $(".loader").hide();

            if (data["status"] == "success") {
                $("#addBuyNowSite").attr("disabled", true).css({cursor: "not-allowed", 'pointer-events': 'none'});

                if (data["msg"] == "DURL") {
                    var durl = data["url"];
                    var key = data["key"];
                    var sid = data["sid"];

                    var downloadID = data['downloadID'];

                    $('.icon-circle').hide();
                    $('#copyDownloadUrl').show();
                    $(".download_BuyNowurl_div").show();
                    var pathArray = window.location.href.split("/");
                    var downloadUrl = pathArray[0] + "//" + pathArray[2] + "/" + pathArray[3] + "/eula.php";
                    if (downloadID === '') {
                        $("#download_buyNowurl").val(downloadUrl + "?key=" + key + "&sid=" + sid).css({color: "green"});
                    } else {
                        $("#download_buyNowurl").val(downloadUrl + "?id=" + downloadID).css({color: "green"});
                    }
                    setTimeout(function () {
                        location.reload();
                    }, 2500);
                } else {
                        $("#newProvisionErrorMsg").html(data["msg"]).css({color: "green"});
                }
            } else {
                $("#newProvisionErrorMsg").html(data["msg"]).css({color: "red"});
            }
        },
        error: function (err) {
            console.log("Error : " + err);
        }
    });
}

function copyDownloadUrl() {
    var urlField = document.querySelector("#download_buyNowurl");
    urlField.select();
    document.execCommand("copy");
}

// Trial Flow JS Code

$(document).ready(function () {
    var trialDays = $('#remaing_trialdays').html();
    var introPops = $('#introPopup').html();
    var currentwindow = $('#currentwindow').html();
    var signupType = $('#signupType').html();

    var pageName = location.href.split('/');
    if(pageName[4] === 'home' && pageName[5] === 'index.php') {
        currentwindow = 'home';
    } else {
        currentwindow = '';
    }
    
    if (currentwindow === 'home' && signupType === "msp") {
        if (trialDays > 0 || introPops === "1") {
            $('#showTrialFlowBtn').click();
        }
    }
});

$("#firstCopyUrlDiv2").click(function () {
    var urlField = document.querySelector("#trial_msp_download_url");
    urlField.select();
    document.execCommand("copy");
});

$("#firstDownloadUrlDiv2").click(function () {
    var urlField = $("#trial_msp_download_url").val();
    var tempUrl = urlField.split("?id=");
    $.ajax({
        type: "GET",
        dataType: "json",
        url: "../lib/l-msp.php",
        data: "function=MSP_GetProcessSetupDetails&urlField=" + tempUrl[1] + "&csrfMagicToken=" + csrfMagicToken,
        success: function (processresult) {
            var sessionId = processresult.sessionid;
            var pid = processresult.pId;
            var companyName = processresult.processName;

            var osVersion = "";
            var osType = navigator.userAgent.toLowerCase();

            if (osType.indexOf("android") !== -1) {
                osVersion = "android";
            } else if (osType.indexOf("mac") !== -1) {
                osVersion = "mac";
            } else if (
                    osType.indexOf("ubuntu") !== -1 ||
                    osType.indexOf("linux") !== -1
                    ) {
                osVersion = "ubuntu";
            } else {
                if (
                        osType.indexOf("WOW64") !== -1 ||
                        osType.indexOf("wow64") !== -1 ||
                        osType.indexOf("Win64") !== -1 ||
                        osType.indexOf("win64") !== -1
                        ) {
                    osVersion = "64bit";
                } else {
                    osVersion = "32bit";
                }
            }

            var downSetup = "";
            if (osVersion === "32bit") {
                downSetup = $.trim(processresult.deployPath32);
            } else if (osVersion === "64bit") {
                downSetup = $.trim(processresult.deployPath64);
            } else if (osVersion === "android") {
                downSetup = $.trim(processresult.androidsetup);
            } else if (osVersion === "mac") {
                downSetup = $.trim(processresult.macsetup);
            } else if (osVersion === "ubuntu") {
                downSetup = $.trim(processresult.linuxsetup);
            }
            if (osVersion === "android") {
                window.location =
                        "https://play.google.com/store/apps/details?id=com.nanoheal.client";
            } else {
                var donwloadUrl =
                        "../download_helper.php?sessionid=" +
                        sessionId +
                        "&downlName=" +
                        downSetup +
                        "&downType=" +
                        companyName +
                        "&proId=" +
                        pid;
                window.location.href = donwloadUrl;
            }
        }
    });
});

$("#exportAllOrders").on('click',function(){
  $.notify("Export successfull");
    location.href='../lib/l-ptsAjax.php?function=get_exportOrder';
    closePopUp();
});
