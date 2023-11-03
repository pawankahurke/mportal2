window.uploadCdnFtpUploadInProgress = false;

$(function () {
    $("#siteName").change(function () {
        var sites = $("#siteName").val();
        $("#siteArray").val(sites);
    });

    var user = $("#logdusr").val();
    getSitesList(user);

    { // closing right-panel from iframe
        const eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
        const eventer = window[eventMethod];
        const messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";
        
        eventer(messageEvent,function(e) {
            const key = e.message ? "message" : "data";
            const data = e[key];
    
            if(data === 'closeSoftDistForm') {
                rightContainerSlideClose('rsc-add-container');
                location.reload();
            }
        },false);
    }
});

function addPackageEvent(element) {
    var targetId = element.attr('data-bs-target');
    enableFields();
    rightContainerSlideOn(targetId);
    addPatch(window.usrEmail);
}

function getSitesList(user) {
    $.ajax({
        url: "../softdist/SWD_Function.php",
        type: "POST",
        dataType: "JSON",
        data: {function: "getSite", user: user, 'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            $("#siteName").html('<option value="All">All</option>' + data.option);
            $('.selectpicker').selectpicker('refresh');
        }
    });
}


var inserteddid;
var fileList = new Array();
var click = 0;
var uploadCount = 0;

function initAddBox() {
    $('#posKeywords,#distTypeDiv,#packExpiryDiv,#policyEnforceDiv,#downloadPathDiv,#andPreCheckDiv,#packageAndVersionDiv,#andPostCheckDiv,#packAndVersDiv,#sourceDestinationDiv,#maxtimeperpatch,#installTypeDiv,#titleDiv,#preDownloadMsgDiv,#postDownloadMsgDiv,#installMsgDiv,#freqIntActMsgDiv,#categoryDiv,#siteDiv,#actionDate,#notify,#uniAction,#manifestDiv,#manifestNameDiv,#repoLinkContainer').hide();
    $('#addPatchValidate').find('#rawUploadContainer,.showaccess,.showSecure,.distributionPath,.distributionTime,.distributionValidPath,.preDisCheckClass,.distributionpreCheck,.distributionpreCheck,.preinstcheckFields,.peerDistribution').hide();
    $('#ap-source-type-container,#nanohealRepository,#otherRepository,#packNameDiv,#packDescDiv,div.version,.configureSource,.distClass,.sourcetypebox').show();
    $('#rsc-add-container').find('#files,#filevalidationtext').html('').hide();
    $('#rsc-add-container').find('#appleplay,#nanohealplay').hide();

    $("#ftpupload").prop('disabled', false).parents('.form-check').removeClass('disabled');
    $("#ftpspan").html("");
    $("#cdnupload").prop('disabled', false).parents('.form-check').removeClass('disabled');
    $("#cdnspan").html("");
}

function addPatch(email) {

    var uploadDiv = $('#fine-uploader-manual-trigger');
    var feed = $('#checkavail');

    $('#addPatchValidate').trigger('reset'); // reset the form

    $.each($('#addPatchValidate').find('select'), function () {
        $(this).prop('selectedIndex', 0);
        if ($(this).hasClass('selectpicker')) {
            $(this).selectpicker("refresh");
        }
    });

    $('#checkavail').text('');
    clearAllField();
    initAddBox();
    uploadDiv.html('').hide();
    feed.removeClass('error').html('');

    var adminEmail = email;

    var formData = new FormData();
    formData.append("function", "addPackageFn");
    formData.append("email", adminEmail);
    formData.append("csrfMagicToken",csrfMagicToken);
    
    $.ajax({
        url: "SWD_Function.php",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (data) {
            var cdn = data.data.cdn;
            var ftp = data.data.ftp;
            var ftpUrl = data.data.ftpUrl;
            var AWSURL = data.data.AWSURL;
            $("#sFtpUrl").val(ftpUrl);
            $("#sCdnUrl").val(AWSURL + "" + data.data.AWSBUCKET + "/");
            $("#AWSACCESS").val(data.data.AWSACCESS);
            $("#AWSSECRET").val(data.data.AWSSECRET);
            $("#AWSBUCKET").val(data.data.AWSBUCKET);
            $("#AWSREGION").val(data.data.AWSREGION);
            $("#policy").val(data.data.policy);
            $("#signature").val(data.data.signature);

            // if ((ftp == '' && cdn == '') || (isNaN(ftp) && isNaN(cdn)) || (parseInt(ftp) == 0 && parseInt(cdn) == 0)) {
            //     feed.addClass('error').html('Please configure FTP or CDN details to add package');
            //     $("#cdnupload").parents('.form-check').attr('data-after-disabled', 'true');
            //     $("#ftpupload").parents('.form-check').attr('data-after-disabled', 'true');
            //     rightContainerSlideClose('rsc-add-container');
            //     errorNotify('Please configure FTP or CDN details to add package');
            //     return;
            // } else if (ftp == "" || isNaN(ftp) || parseInt(ftp) == 0) {
            //     $("#ftpupload").parents('.form-check').attr('data-after-disabled', 'true');
            //     $("#ftpupload").prop('disabled', true).parents('.form-check').addClass('disabled');
            //     $("#ftpspan").html("<span>(Configure FTP to upload)</span>  ");
            //     return;
            // } else if (cdn == "" || isNaN(cdn) || parseInt(cdn) == 0) {
            //     $("#cdnupload").parents('.form-check').attr('data-after-disabled', 'true');
            //     $("#cdnupload").prop('disabled', true).parents('.form-check').addClass('disabled');
            //     $("#cdnspan").html(" <span>(Configure CDN to upload)</span>  ");
            //     return;
            // }
        }
    });
}

function platformSelect() {

    var selectedPlatform = document.getElementById("platform").value;
    if (selectedPlatform == "windows") {
        $("#sfolder_check,#googleplay,#androidMandatory,#nanohealplay,.clearFilesDiv,#appleplay,#siteDiv,#androidIco,#preinstallDiv,#oninstallDiv,#posKeywords,#packExpiryDiv,.CommAndPosKey288,.CommAndPosKey415,.Comm_PosKey_PackExp288,.preDisCheckClass,.distributionPath,.distributionTime,.distributionValidPath,.preinstcheckFields,.distributionpreCheck").hide();
        $("#ap-source-type-container,.FileName,.distClass,#otherRepository,#nanohealRepository,.configureSource,#packNameDiv,#packDescDiv,.version,#fileTypeDiv").show();
        $("#nhrep").click();
        $("#preDisCheck,#distCheck,.preinstcheck").prop("checked", false);
        $("#preDisCheck,#distCheck").val(0);
    } else if (selectedPlatform == "android") {

        $("#sfolder_check,.FileName,.distClass,#appleplay,#siteDiv,#posKeywords,#packExpiryDiv,.CommAndPosKey288,.CommAndPosKey415,.Comm_PosKey_PackExp288,.preDisCheckClass,.distributionPath,.distributionTime,.distributionValidPath,.preinstcheckFields,.distributionpreCheck").hide();
        $("#googleplay,#androidMandatory,#nanohealplay,#androidIco,#posKeywords,#otherRepository,#nanohealRepository,.configureSource,#packNameDiv,#packDescDiv,.version,#fileTypeDiv").show();
        $("#posKey option[value=0]").prop('selected', true);
        $("#gplay").click();
        $(".selectpicker").selectpicker('refresh');
        $("#preDisCheck,#distCheck,.preinstcheck").prop("checked", false);
        $("#preDisCheck,#distCheck").val(0);
    } else if (selectedPlatform == "mac" || selectedPlatform == "linux") {

        $(".FileName,.distClass,#sfolder_check,#androidIco,#googleplay,#androidMandatory,#nanohealplay,.clearFilesDiv,#appleplay,#siteDiv,#preinstallDiv,#oninstallDiv,#posKeywords,#packExpiryDiv,.CommAndPosKey288,.CommAndPosKey415,.Comm_PosKey_PackExp288,.distributionPath,.distributionTime,.distributionValidPath,.preinstcheckFields,.distributionpreCheck,#filenameDiv").hide();
        $("#nhrep").click();
        $("#otherRepository,#nanohealRepository,.configureSource,#packNameDiv,#packDescDiv,.version,#fileTypeDiv,.preDisCheckClass").show();
        $("#preDisCheck,#distCheck,.preinstcheck").prop("checked", false);
        $("#preDisCheck,#distCheck").val(0);
    } else if (selectedPlatform == "ios") {

        $("#sfolder_check,.FileName,.distClass,#googleplay,#androidMandatory,#siteDiv,#preinstallDiv,#oninstallDiv,#posKeywords,#packExpiryDiv,.CommAndPosKey288,.CommAndPosKey415,.Comm_PosKey_PackExp288,.preDisCheckClass,.distributionPath,.distributionTime,.distributionValidPath,.preinstcheckFields,.distributionpreCheck").hide();
        $("#appleplay,#nanohealplay,#nanohealRepository,#otherRepository,.configureSource,#packNameDiv,#packDescDiv,.version,#fileTypeDiv").show();
        $("#preDisCheck,#distCheck,.preinstcheck").prop("checked", false);
        $("#preDisCheck,#distCheck").val(0);

    }
    /*else if (selectedPlatform == "linux") {

        $("#sfolder_check,#googleplay,#androidMandatory,#nanohealplay,.clearFilesDiv,#appleplay,#siteDiv,#androidIco,#preinstallDiv,#oninstallDiv,#posKeywords,#packExpiryDiv,.CommAndPosKey288,.CommAndPosKey415,.Comm_PosKey_PackExp288,.preDisCheckClass,.distributionPath,.distributionTime,.distributionValidPath,.preinstcheckFields,.distributionpreCheck").hide();
        $(".FileName,.distClass,#otherRepository,#nanohealRepository,.configureSource,#packNameDiv,#packDescDiv,.version,#fileTypeDiv").show();
        $("#nhrep").click();
        $("#preDisCheck,#distCheck,.preinstcheck").prop("checked", false);
        $("#preDisCheck,#distCheck").val(0);
    }*/


}

function andPosKeyFields() {
    var posKey = $("#posKey").val();
    $("#manifestDiv").hide();
    switch (posKey) {
        case "415":
            $("#nanohealRepository").show();
            $("#nhrep").click();
            $(".CommAndPosKey288,.CommAndPosKey415,#packageAndVersionDiv,#pathSizeDiv,#downloadTypeDiv,#appIdDiv,#packAndVersDiv,#appleplay,#googleplay,#androidMandatory,#nanohealplay,#androidIco,#otherRepository,#policyEnforceDiv,#titleDiv").fadeOut();
            $("#installTypeDiv,#nanohealRepository,.configureSource,#packNameDiv,#packDescDiv,.version,#fileTypeDiv,#packExpiryDiv").fadeIn();
            break;
        case "288":
            $("#nanohealRepository").show();
            $("#nhrep").click();
            $(".CommAndPosKey415,#packageAndVersionDiv,#pathSizeDiv,#policyEnforceDiv,#googleplay,#androidMandatory,#nanohealplay,#androidIco,#appleplay,#otherRepository,#appIdDiv,#packAndVersDiv,#titleDiv").fadeOut();
            $("#distTypeDiv,#fileTypeDiv,#policyEnforceDiv,#posKeywords,#packExpiryDiv").fadeIn();
            $("#distType option[value='']").prop("selected", true);
            break;
        default:
            $(".CommAndPosKey288,.CommAndPosKey415,#packageAndVersionDiv,#pathSizeDiv,#downloadTypeDiv,#titleDiv,#appIdDiv,#policyEnforceDiv,#packAndVersDiv,#appleplay,#packExpiryDiv").fadeOut();
            $("#googleplay,#androidMandatory,#nanohealplay,#androidIco,#otherRepository,.configureSource,#packNameDiv,#packDescDiv,.version,#fileTypeDiv").fadeIn();
            $("#gplay").click();
            setTimeout(function () {
                $("#nanohealRepository").hide();
            }, 300);
            break;
    }
    $(".selectpicker").selectpicker("refresh");
}

function distTypeFn() {
    var distType = $("#distType").val();
    switch (distType) {
        case "1":
            $("#distTypeDiv,#andPreCheckDiv,#downloadPathDiv,#downloadTypeDiv,#maxtimeperpatch,#nanohealRepository,.configureSource,#packNameDiv,#packDescDiv,.version,#policyEnforceDiv,#preCheckPathDiv").fadeIn();
            $("#andPostCheckDiv,#packageAndVersionDiv,#sourceDestinationDiv,#googleplay,#androidMandatory,#nanohealplay,#androidIco,#appleplay,#otherRepository,#appIdDiv,#titleDiv,#packAndVersDiv").fadeOut();
            $("#andPreCheck option[value=0]").show();
            $("#andPreCheck option[value=1]").hide();
            $("#andPreCheck option[value=2]").hide();
            $("#andPreCheck option[value=0]").prop("selected", true);
            $("#downloadType option[value=0]").show();
            $("#downloadType option[value=1]").hide();
            $("#policyEnforce option[value=0]").show();
            $("#policyEnforce option[value=1]").hide();
            $("#policyEnforce option[value=2]").hide();
            $("#gplay").click();
            $(".selectpicker").selectpicker('refresh');
            $("#andPPackName,#andPVersionCode").val("");
            $("#policyEnforceDiv,#downloadTypeDiv").hide(); //new requirement
//            $(".source-type").show();
            break;
        case "2":
            $("#distTypeDiv,#andPreCheckDiv,#andPostCheckDiv,#downloadTypeDiv,#packageAndVersionDiv,#maxtimeperpatch,#nanohealRepository,.configureSource,#packNameDiv,#packDescDiv,.version,#packAndVersDiv,#policyEnforceDiv").fadeIn();
            $("#downloadPathDiv,#sourceDestinationDiv,#googleplay,#androidMandatory,#nanohealplay,#androidIco,#appleplay,#otherRepository,#appIdDiv,#titleDiv,#preCheckPathDiv").fadeOut();
            $("#andPreCheck option[value=1]").show();
            $("#andPreCheck option[value=0]").show();
            $("#andPreCheck option[value=2]").show();
            $("#andPreCheck option[value=1]").prop("selected", true);
            $("#andPostCheckDiv option[value=1]").show();
            $("#andPostCheckDiv option[value=0]").hide();
            $("#andPostCheckDiv option[value=1]").prop("selected", true);
            $("#downloadType option[value=0]").show();
            $("#downloadType option[value=1]").show();
            $("#downloadType option[value=0]").prop("selected", true);
            $("#policyEnforce option[value=0]").show();
            $("#policyEnforce option[value=1]").show();
            $("#policyEnforce option[value=2]").show();
            $("#gplay").click();
            $(".selectpicker").selectpicker('refresh');
//            $(".source-type").show();
            break;
        case "3":
            $("#distTypeDiv,#maxtimeperpatch,#sourceDestinationDiv,#policyEnforceDiv,#packNameDiv,#downloadTypeDiv").fadeIn();
            $("#andPreCheckDiv,#downloadPathDiv,#andPostCheckDiv,#packageAndVersionDiv,#googleplay,#androidMandatory,#nanohealplay,#androidIco,#appleplay,#otherRepository,#nanohealRepository,.configureSource,#packDescDiv,.version,#appIdDiv,#titleDiv,#packAndVersDiv,#preCheckPathDiv").fadeOut();
            $("#downloadType option[value=0]").show();
            $("#downloadType option[value=1]").hide();
            $("#downloadType option[value=0]").prop("selected", true);
            $("#policyEnforce option[value=0]").show();
            $("#policyEnforce option[value=1]").hide();
            $("#policyEnforce option[value=2]").hide();
            $(".selectpicker").selectpicker('refresh');
            $("#policyEnforceDiv,#downloadTypeDiv").hide(); //new requirement
            $(".source-type").hide();
            break;
        default:
            $("#andPreCheckDiv,#downloadTypeDiv,#andPostCheckDiv,#downloadPathDiv,#packageAndVersionDiv,#maxtimeperpatch,#sourceDestinationDiv,#appIdDiv,#policyEnforceDiv,#packAndVersDiv,#preCheckPathDiv").fadeOut();
            $("#googleplay,#androidMandatory,#nanohealplay,#androidIco,#appleplay,#otherRepository,#nanohealRepository,.configureSource,#packNameDiv,#packDescDiv,.version").fadeIn();
            $("#nhrep").click();
            break;
    }
    $(".selectpicker").selectpicker('refresh');
}

function installTypeChange() {
    var installType = $("#installType").val();
    switch (installType) {
        case "0":
            $("#preDownloadMsgDiv,#postDownloadMsgDiv,#installMsgDiv,#freqIntActMsgDiv,#titleDiv").fadeOut();
            break;
        case "3":
            $("#freqIntActMsgDiv").fadeOut();
            $("#preDownloadMsgDiv,#postDownloadMsgDiv,#installMsgDiv,#titleDiv").fadeIn();
            break;
        case "5":
            $("#preDownloadMsgDiv,#postDownloadMsgDiv,#installMsgDiv,#freqIntActMsgDiv,#titleDiv").fadeIn();
            break;
        default:
            $("#preDownloadMsgDiv,#postDownloadMsgDiv,#installMsgDiv,#freqIntActMsgDiv,#titleDiv").fadeOut();
            break;
    }
}

function policyEnforceActionFnc() {
    var policyEnforceAction = $("#policyEnforceAction").val();
    switch (policyEnforceAction) {
        case "1":
            $(".policyEnforceActionClass").show();
            break;
        case "2":
            $(".policyEnforceActionClass").hide();
            break;
        default:
            $(".policyEnforceActionClass").hide();
            break;
    }
}

function installActionFn() {
    var installAction = $("#installAction").val();
    switch (installAction) {
        case "1":
            $(".installPopupSpan").hide();
            $(".installFinishMsgSpan").show();
            break;
        case "2":
            $(".installFinishMsgSpan").hide();
            $(".installPopupSpan").show();
            break;
        default:
            $(".installFinishMsgSpan").show();
            $(".installPopupSpan").hide();
            break;
    }

}

function andPostCheckFn() {
    var andPostCheck = $("#andPostCheck").val();
    switch (andPostCheck) {
        case "1":
            $("#downloadTypeDiv").show();
            $("#packAndVersDiv").show();
            break;
        case "":
            $("#downloadTypeDiv").show();
            $("#packAndVersDiv").hide();
            break;
        default:
            $("#downloadTypeDiv").show();
            $("#packAndVersDiv").hide();
            break;
    }
}

function andPreCheckFn() {
    var andPreCheck = $("#andPreCheck").val();
    switch (andPreCheck) {
        case "2":
            $("#pathSizeDiv").show();
            $("#packageAndVersionDiv,#preCheckPathDiv").hide();
            break;
        case "1":
            $("#packageAndVersionDiv").show();
            $("#pathSizeDiv,#preCheckPathDiv").hide();
            break;
        case "0":

            $("#packageAndVersionDiv,#pathSizeDiv").hide();
            $("#preCheckPathDiv").show();
            break;
        default:
            $("#preCheckPathDiv").show();
            $("#packageAndVersionDiv,#pathSizeDiv").hide();
            break;
    }
}

function onlyPoskeyShow() {
    var platForm = $("#platform").val();
    if (platForm == "android") {
        setTimeout(function () {
            $("#posKeywords").show();
        }, 500);

    }
}

function sourceTypeFunction(dataSource) {

    var platformS = $("#platform").val();
    $('.shared,.showaccess').hide();
    $('.configureSource,.version').show();
    if (dataSource == "sfolder") {
//        $("#addPackage").prop("disabled",true);
        $('.shared,.showaccess').fadeIn();
        $('.nhrep,#siteDiv').fadeOut();
    } else if (dataSource == "gplay") {
//        $("#addPackage").prop("disabled",false);
//        $(".Path,.version,.nhrep,.showaccess,.configureSource,#siteDiv").hide();
        $(".Path,.nhrep,.showaccess,.configureSource,#siteDiv").hide();
    } else if (dataSource == "nplay") {
//        $("#addPackage").prop("disabled",true);
        if (platformS == 'android') {

            $("#appIdDiv,#manifestDiv,#pathDiv,#filenameDiv,#manifestNameDiv").hide();
            $('#actionDate,#notify,#uniAction,#packNameDiv,#packDescDiv,#androidIco,#siteDiv').show();
        } else if (platformS == 'ios') {

            $("#appIdDiv,#appNameDiv,.distClass,#actionDate,#pathDiv,#filenameDiv,#notify,#uniAction,#manifestDiv,#siteDiv").hide();
            $('#packNameDiv,#packDescDiv,#androidIco,#manifestNameDiv').show();
        }

    } else if (dataSource == "iplay") {
//        $("#addPackage").prop("disabled",false);
        $("#appIdDiv,#appNameDiv,.version").show();
        $('#actionDate,#siteDiv,#notify,#uniAction,#manifestDiv,#androidIco,#packDescDiv,#pathDiv,#filenameDiv,.distClass,.configureSource,.nhrep,#manifestNameDiv').hide();

    } else if (dataSource == "nhrep") {
        $('.showSecure').hide();
//        $("#addPackage").prop("disabled",true);
        if (platformS == 'android') {

            $('#actionDate,#notify,#uniAction,#androidIco,#appIdDiv,#appNameDiv,#manifestDiv,#pathDiv,#filenameDiv,#manifestNameDiv,#siteDiv').hide();
            $('#packNameDiv,#packDescDiv,.version').show();
        } else if (platformS == 'ios') {

            $("#appIdDiv,#appNameDiv,.distClass,#actionDate,#pathDiv,#filenameDiv,#notify,#uniAction,#manifestDiv,#siteDiv").hide();
            $('#packNameDiv,#packDescDiv,#androidIco,#manifestNameDiv').show();
        } else if (platformS == 'windows') {

            $('.distClass,#packNameDiv,#packDescDiv,.version').fadeIn();
            $('#actionDate,#notify,#uniAction,#appIdDiv,#appNameDiv,#androidIco,#manifestDiv,#pathDiv,#filenameDiv,#manifestNameDiv,#siteDiv').hide();
        } else if (platformS == 'mac' || platformS == 'linux') {

            $('.distClass').fadeOut();
            $('#actionDate,#notify,#uniAction,#appIdDiv,#appNameDiv,#manifestNameDiv,#manifestDiv,#siteDiv,#filenameDiv').hide();
            $("#packNameDiv,#packDescDiv,#pathDiv,.version").show();
        }
        /*else if (platformS == 'linux') {

            $('.distClass,#packNameDiv,#packDescDiv,.version,#pathDiv').fadeIn();
            $('#actionDate,#notify,#uniAction,#appIdDiv,#appNameDiv,#androidIco,#manifestDiv,#filenameDiv,#manifestNameDiv,#siteDiv').hide();
        }*/

    } else if (dataSource == "otrep") {
//        $("#addPackage").prop("disabled",false);
//        $("#packName,#packDesc,#version,.showaccess").show();

        $("input[type=radio]:checked.ftprcdn").each(function (i) {
            $(this).removeAttr('checked');
        });
        var pl = $("#platform").val();
        if (pl == 'windows') {

            $('.shared').fadeOut();
        }
        /*if (pl == 'linux') {

            $('.shared').fadeOut();
        }*/
        if (pl == 'android') {

            $('.shared,.version').fadeIn();
            $(".FileName,.nhrep").hide();
        }
        if (pl == 'mac' || pl == 'linux') {

            $('.Path').fadeIn();
            $("#posKeywords,#filenameDiv,.preDisCheckClass,.distributionpreCheck,.preinstcheckFields").hide();
        }

        $('.nhrep,.distClass,.configureSource,#filenameDiv,#pathDiv,#files').hide();
        $('.showaccess').fadeIn();


    }

}

function addTimeStamp(fileName, timeStamp) {

    var uploadingFile = fileName;
    var uploadingFileSplitArr = uploadingFile.split('.');
    var uploadingFileExtension = uploadingFileSplitArr[uploadingFileSplitArr.length - 1];
    var timeStamp = '_' + timeStamp;
    return renamedFile = uploadingFile.replace('.' + uploadingFileExtension, timeStamp + '.' + uploadingFileExtension);
}

function removeExtension(fileName) {

    var uploadingFile = fileName;
    var uploadingFileSplitArr = uploadingFile.split('.');
    var uploadingFileExtension = uploadingFileSplitArr[uploadingFileSplitArr.length - 1];
    return removedextension = uploadingFile.replace('.' + uploadingFileExtension, '');
}


function accessFunction(showsecure) {

    if (showsecure == "anony") {
        $(".showSecure").css('display','none');
        // $(".showSecure").fadeOut();
    } else if (showsecure == "secure") {
        $(".showSecure").css('display','block');
        // $(".showSecureParent").fadeIn();
    }

}

// On selecting of UploadFile radio Button
$('#uploadFile').on("click", function () {

    var feedDiv = $('#checkavail');

    $(".CdnFilesList").hide();
    $("#files,#filevalidationtext_cdn").html('');
    feedDiv.html('');

    var pl = $("#platform").val();
    if (pl == '') {
        //$("#filevalidationtext").html('<span>Please select a platform</span>');
        feedDiv.html('Please select a platform');
        $(this).removeAttr('checked');
        return;
    }

    var ft = $("#types").val();
    if (ft == '') {

        //$("#filevalidationtext").html('<span>Please select a file type</span>');
        feedDiv.html('Please select a file type');
        $(this).removeAttr('checked');
        return;
    }

    $("#filevalidationtext").html('');
    $('.uploadbuttonDiv').show();
});

$('addPackage').click(function () {

    $("#platforms").text('');
    $("#typess").text('');
});
// On change of Platform...
$("#platform").change(function () {

    $('#checkavail').hide();
    $('#upload').unbind('click'); // unbinding all selected files to upload

    $("#files,#filevalidationtext").html(''); // Clearing validation Errors 

    /* For android(E) Start*/
    // Enabling the disabled fileupload select button.			
    $("#fileupload").removeAttr('disabled');
    uploadCount = 0; // making count 0 so that it allows to select files to upload.

    $(".clearFilesDiv").hide(); // Hiding Option to clear files that have been selected. 

    $("input[type=radio]:checked.selectSource").each(function (i) {
        $(this).removeAttr('checked');
    });
    /* For android(E) END*/

    $("#packName").val(''); // Clearing PackageName value.

    // Unchecking selected radio buttons for sourceType or FTP or CDN
    $("input[type=radio]:checked.selectSource").each(function (i) {
        $(this).removeAttr('checked');
    });
    // Unchecking selected radio buttons for FTP or CDN
    $("input[type=radio]:checked.ftprcdn").each(function (i) {
        $(this).removeAttr('checked');
    });
    $('.uploadbuttonDiv,.nhrep').hide(); // Hiding Upload button

    // Checking which sourcetype has been checked earlier and cliking on the same.
    var rID = $("input:radio[name=stype]:checked").attr('id');
    $("#" + rID).click();
    $("#progress1").html("");
});
// On change of File Type...

$("#types").change(function () {

    $('#upload').unbind('click'); // unbinding all selected files to upload

    $("#files,#filevalidationtext").html(''); // Clearing validation Errors

    /* For android(E) Start*/
    // Enabling the disabled fileupload select button.			
    $("#fileupload").removeAttr('disabled');
    uploadCount = 0; // making count 0 so that it allows to select files to upload.

    $(".clearFilesDiv").hide(); // Hiding Option to clear files that have been selected. 

    $("input[type=radio]:checked.selectSource").each(function (i) {
        $(this).removeAttr('checked');
    });
    /* For android(E) END*/

    $('.uploadbuttonDiv').hide();
    // Changing text based on selected file type.
    if ($(this).val() == 'file') {

        $(".selectfile").html('<span>Upload file</span>');
    } else {

        $(".selectfile").html('<span>Upload folder</span>');
    }
});
// Default Click on Nanoheal Repository
$("#nhrep").click();
// On Selecting Distribute Checkbox.
$('#distCheck').change(function () {

    var dpath = $(this).val();
    if (dpath == 1) {
//$("#dPath").fadeIn();
//        if (dpath == "") {/*name validation*/
//            $("#dPathError").css("color", "red")
//            $("#dPathError").html("Please enter the path");
//            $("#dPathError").fadeOut(3000);
//        } else if (!nm.test(dpath)) {  /*check name varification*/
//            $("#dPathError").css("color", "blue")
//            $("#dPathError").html("not a valid path");
//return false;
//}
        $(".distributionPath,.distributionTime,.distributionValidPath,.distributionpreCheck,.preDisCheckClass,.preinstcheckFields").hide();
        $(".preinstcheck,#preDisCheck").removeAttr('checked');
        $(".preinstcheck").val('');
        $("#dPath").val('');
        $("#dTime").val('');
        $("#dvPath").val('');
        $(this).val(0);
        $('#preDisCheck').val(0);
        $(".preinstcheckFields").find('input').val('');
    } else {

        $(this).val(1);
        $(".distributionPath,.distributionTime,.distributionValidPath,.preDisCheckClass").show();
    }

});
// PreDistribution Check
$("#preDisCheck").on("click", function () {

    var preDisVal = $(this).val();
    var platformpd = $("#platform").val();
    if (preDisVal == 0) {

        $(this).val(1);
        $(".distributionpreCheck").show();
        if (platformpd == 'mac' || platformpd == 'linux') {
            $("#pRegistryDiv").hide();
            $("#distributionPreCheckDiv").hide();
        }
        $("#distributionPreCheckDiv").hide();
    } else {

        $(this).val(0);
        $(".distributionpreCheck,.preinstcheckFields").hide();
        $(".preinstcheckFields").find('input').val('');
        $(".preinstcheck").removeAttr('checked');
    }
});
// Preinstcheck is nothing but PreDistribution check in this file.
$('.preinstcheck').on("click", function () {

    var id = $(this).attr('id');
    var platformpi = $("#platform").val();
    if (id == 'pfile') {

        $('#' + id).val('0');
        $("#distributionPreCheckDiv").show();
    } else if (id == 'pSoftware') {

        $('#' + id).val('1');
        $("#distributionPreCheckDiv").hide();

    } else if (id == 'pRegistry') {

        $('#' + id).val('2');
        $("#distributionPreCheckDiv").show();
    } else if (id == 'pPatch') {

        $('#' + id).val('3');

    }

    $('.preinstcheckFields').hide();

    setTimeout(function () {
        $('.' + id).show();
        if (platformpi == 'mac' || platformpi == 'linux') {
            $("#pSoftwareKBDiv,#pSoftwareSPDiv").hide();
            $("#distributionPreCheckDiv").hide();
        }
    }, 100);


});
// On Select of Mandatory CheckBox (Only for Android)
$('#mandatory').change(function () {

    var mv = $(this).val();
    if (mv == 1) {
        $(this).val(0);
    } else {
        $(this).val(1);
    }

});
// Unbiding Selected Files option in (only for ANDROID Nanoheal Play)
$("#clearFiles").on("click", function () {

    $('#fileupload').uploadify('cancel', '*');
    $("#files").html('');
    $("#filevalidationtext").html('');
    $('#upload').unbind('click');
    $("#fileupload").removeAttr('disabled');
    $(".addedfilesList").html("");
    $("#fileUp").fadeIn();
    $(".clearFilesDiv,.addedfilesList").fadeOut();
//    $("#addPackage").prop('disabled', true);
    uploadCount = 0;

});
// CDN REPOSITORY SELECT
$('#sdnSelect').on("click", function () {

    $('.uploadbuttonDiv').hide();
    $(".CdnFilesList").show();
    $("#files").html('');
    var pl = $("#platform").val();
    if (pl == '') {

        $("#filevalidationtext").html('<span>Please select a platform</span>');
        $(this).removeAttr('checked');
        return;
    }

    $("#filevalidationtext").html('');
    $.ajax({
        url: 'SWD_Function.php',
        type: 'POST',
        async: true,
        data: {function: "addFn", platform: pl, csrfMagicToken: csrfMagicToken},
        success: function (data) {

            $("#loaderRepository").hide();
            $("#files").html(data);
        }
    });
});
//
//function addPackageFunction() {
//    if (window.uploadCdnFtpUploadInProgress) {
//        errorNotify('Upload in progress, please wait');
//        return;
//    }
//
//    var uploadCount = window.uploadCount;
//    var cdnuploadCheckbox = $('#cdnupload');
//    var ftpuploadCheckBox = $('#ftpupload');
//    var platformSelectBox = document.getElementById("platform");
//    var SourceTypeNanohealRepoBox = $('#nhrep');
//    var SourceTypeVendorRepoBox = $('#otrep');
//    var sourceTypeAppleStore = $('#iplay');
//    var sourceTypeNHealPlayStore = $('#nplay');
//    var sourceTypeGooglePlayStore = $('#gplay');
//    var statusFeed = $("#checkavail");
//    var selectFromRepoCheckBox = $('#sdnSelect');
//    var repoListBoxes = $('.repositoryList_checkbox');
//    var typeSelectBox = document.getElementById("types");
//    var fileUploadCheckBox = $('#uploadFile');
//    var dartSelectBox = document.getElementById('posKey');
//    var installType = $('#installType').val();
//
//    $("#checkavail").hide().html("");
//    statusFeed.removeClass('success').addClass('error');
//
//    $('.required').not(':hidden').each(function () {
//        var id = $(this).attr('id');
//    });
//
//    $("#packNameMsg").fadeIn();
//    $("#packDescMsg").fadeIn();
//    $("#versionMsg").fadeIn();
//    $("#dPathMsg").fadeIn();
//    $("#dTimeMsg").fadeIn();
//    $("#dvPathMsg").fadeIn();
//    $("#usernameMsg").fadeIn();
//    $("#passwordMsg").fadeIn();
//    $("#domainMsg").fadeIn();
//    $("#sServerMsg").fadeIn();
//    $("#pfilePathMsg").fadeIn();
//    $("#pSoftNameMsg").fadeIn();
//    $("#pSoftVerMsg").fadeIn();
//    $("#pKbMsg").fadeIn();
//    $("#pServicePackMsg").fadeIn();
//    $("#subKeyMsg").fadeIn();
//    $("#preMsg").fadeIn();
//
//
//    $("#packNameMsg").html("");
//    $("#packDescMsg").html("");
//    $("#versionMsg").html("");
//    $("#dPathMsg").html("");
//    $("#dTimeMsg").html("");
//    $("#dvPathMsg").html("");
//    $("#usernameMsg").html("");
//    $("#passwordMsg").html("");
//    $("#domainMsg").html("");
//    $("#sServerMsg").html("");
//    $("#pfilePathMsg").html("");
//    $("#pSoftNameMsg").html("");
//    $("#pSoftVerMsg").html("");
//    $("#pKbMsg").html("");
//    $("#pServicePackMsg").html("");
//    $("#subKeyMsg").html("");
//    $("#preMsg").html("");
//
//    var packName = $("#packName").val();
//    var types = $("#types").val();
//    var version = $("#version").val();
//    var preCheckPathBox = $("#preCheckPath").val();
//    var platform = $("#platform").val();
//    var filename = $("#filename").val();
//    var packDesc = $("#packDesc").val();
//    var ftpupload = $("#ftpupload").val();
//    var cdnupload = $("#cdnupload").val();
//    var actionDate = $("#actionDate").val();
//    var notify = $("#notify").val();
//    var uniAction = $("#uniAction").val();
//    var manifesttypes = $("#manifesttypes").val();
//    var manifestname = $("#manifestname").val();
//    var appId = $("#appId").val();
//    var dPath = $("#dPath").val();
//    var dTime = $("#dTime").val();
//    var dvPath = $("#dvPath").val();
//    var username = $("#username").val();
//    var password = $("#password").val();
//    var domain = $("#domain").val();
//    var pfilePath = $("#pfilePath").val();
//    var pSoftName = $("#pSoftName").val();
//    var pSoftVer = $("#pSoftVer").val();
//    var pKb = $("#pKb").val();
//    var pServicePack = $("#pServicePack").val();
//    var subKey = $("#subKey").val();
//    var posKey = $("#posKey").val();
//    var distType = $("#distType").val();
//    if($('#same32_64config').is(':checked')){
//        var configType = 'same';
//    }else{
//        var configType = 'different';
//    }
//    
//    var isGlobalOptionChecked = false;
//
//
//    for (var gi = 0; gi < $('input.add-pack-global').length; gi++) {
//        if ($('input.add-pack-global').eq(gi).is(':checked')) {
//            isGlobalOptionChecked = true;
//            break;
//        }
//    }
//
//    if (!isGlobalOptionChecked) {
//        errorNotify('Please select the global');
//        return;
//    }
//
//    if ($('#ftpupload').is(':checked')) {
//        if (platform === 'android') {
//            if ($('#installType').val() == '3' || $('#installType').val() == '5') {
//                var count = 0;
//                $('#rsc-add-container .required').not(':hidden').each(function () {
//                    if ($(this).val() == '') {
//                        count++;
//                    }
//                });
//                
//            }
//            if ($("#posKey").val() == "288" || $("#posKey").val() == "415") {
//                $("#val_err_msg").show();
//                $("#val_err_msg").html("");
//                var val_flag = 0;
//                $(".val_spcl_chars").each(function () {
//                    var strn = $(this).val();
//                    var regx = /[#$,]/;
//                    if (strn.match(regx)) {
//                        val_flag++;
//                    }
//                });
//                if (val_flag > 0) {
//                    $("#checkavail").show().html("'#','$' & ',' are not allowed for configuration");
//                    return false;
//                } else {
//                    regular_flow();
//                    return true;
//                }
//            }
//
//            if (($('#siteName').val() == '' || $('#siteName').val() == null || !$('#siteName').val()) && $('#nplay').is(':checked')) {
//                $('#siteErr').show().html("Please select atleast one site");
//                return false;
//            } else {
//                regular_flow();
//                return true;
//            }
//
//        } else {
//            regular_flow();
//            return true;
//        }
//    }
//
//    var disFlag = false;
//    if ($("#sdnSelect").css("display") === "inline-block" || $("#sdnSelect").css("display") === "block") {
//        disFlag = true;
//    }
//    if (disFlag) {
//
//        if ($('#sdnSelect').is(':checked')) {
//            if (!$("input[name='selectfromrepo']:checked").val()) {
//
//                errorNotify("<span>Select any one package from the repository</span>");
//                return;
//                setTimeout(function () {
//                    $("#sServerMsg").fadeOut(7000);
//                }, 3000);
//
//            }
//            if (platform === 'android') {
//                if ($('#installType').val() == '3' || $('#installType').val() == '5') {
//                    var count = 0;
//                    $('.required').not(':hidden').each(function () {
//                        if ($(this).val() == '') {
//                            count++;
//                        }
//                    });
//                    if (count > 0) {
//                        errorNotify('Please enter the mandatory field');
//                        return false;
//                    }
//                }
//                if ($("#posKey").val() == "288" || $("#posKey").val() == "415") {
//                    $("#val_err_msg").show();
//                    $("#val_err_msg").html("");
//                    var val_flag = 0;
//                    $(".val_spcl_chars").each(function () {
//                        var strn = $(this).val();
//                        var regx = /[#$,]/;
//                        if (strn.match(regx)) {
//                            val_flag++;
//                        }
//                    });
//                    if (val_flag > 0) {
//                        errorNotify("'#','$' & ',' are not allowed for configuration");
//                        return false;
//                    } else {
//                        regular_flow();
//                        return true;
//                    }
//                }
//            } else {
//                regular_flow();
//                return true;
//            }
//        } else {
//            regular_flow();
//            return true;
//        }
//    }
//
//    var postData;
//
//    function regular_flow() {
//
//        if (packName != "" && types != "" && version != "" && platform != "" && packDesc != "") {
//
//            if (packName.length > 128) {
//                errorNotify("The package name length should not be more than 128 characters");
//                return false;
//            }
//
//            if (packDesc.length > 256) {
//                errorNotify("The package description length should not be more than 256 characters");
//                return false;
//            }
//
//            if (version.length > 128) {
//                errorNotify("The version length should not be more than 128 characters");
//                return false;
//            }
//
//            postData = {
//                'packName': packName,
//                'version': version,
//                'description': packDesc,
//                function: 'checkAvailabilityFn'
//            };
//
//            $.ajax({
//                url: "SWD_Function.php",
//                type: 'POST',
//                data: postData,
//                success: function (data) {
//                    data = $.parseJSON(data);
//                    if (data.data === "true") {
//                        errorNotify("Pacakge already exists");
//                        return false;
//                    } else if (data.data === "false") {
//                        if (($('#secure').is(':checked'))) {
//                            if (username == "" || password == "" || domain == "") {
//
//                                if (username == "") {
//                                    errorNotify("<span>User name is required</span>");
//                                    return;
//                                    setTimeout(function () {
//                                        $("#usernameMsg").fadeOut(5000);
//                                    }, 3000);
//                                }
//                                if (password == "") {
//                                    errorNotify("<span>Password is required</span>");
//                                    return;
//                                    setTimeout(function () {
//                                        $("#passwordMsg").fadeOut(5000);
//                                    }, 3000);
//                                }
//                                if (domain == "") {
//                                    errorNotify("<span>Domain is required</span>");
//                                    return;
//                                    setTimeout(function () {
//                                        $("#domainMsg").fadeOut(5000);
//                                    }, 3000);
//
//                                    return false;
//                                }
//                            }
//                        }
//
//
//                        if (($('#ftpupload').is(':checked')) || ($('#cdnupload').is(':checked'))) {
//                            alert("vgjghcgc");
//                            if ($('#distCheck').is(':checked')) {
//
//                                if (dPath == "" || dTime == "" || dvPath == "") {
//
//                                    if (dPath == "") {
//                                        errorNotify("<span>Distribution path is required</span>");
//                                        return;
//                                        setTimeout(function () {
//                                            $("#dPathMsg").fadeOut(5000);
//                                        }, 3000);
//
//                                    }
//                                    if (dTime == "") {
//                                        errorNotify("<span>Distribution time is required</span>");
//                                        return;
//                                        setTimeout(function () {
//                                            $("#dTimeMsg").fadeOut(5000);
//                                        }, 3000);
//
//                                    }
//
//                                    if (dTime != "" && dTime.length > 10) {
//                                        errorNotify("<span>Distribution time is too long, specify max 10 digit</span>");
//                                        return;
//                                    }
//
//                                    if (dvPath == "") {
//                                        errorNotify("<span>Distribution validation path is required</span>");
//                                        return;
//                                        setTimeout(function () {
//                                            $("#dvPathMsg").fadeOut(5000);
//                                        }, 3000);
//
//                                        return false;
//                                    }
//
//                                } else {
//                                    if ($('#preDisCheck').is(':checked')) {
//
//                                        if ($('#pfile').is(':checked') || $('#pSoftware').is(':checked') || $('#pRegistry').is(':checked')) {
//
//                                            if ($('#pfile').is(':checked')) {
//
//                                                if (pfilePath == "") {
//
//                                                    errorNotify("<span>File Path is required</span>");
//                                                    return;
//                                                    setTimeout(function () {
//                                                        $("#pfilePathMsg").fadeOut(5000);
//                                                    }, 3000);
//
//                                                } else {
//                                                    var url = 'SavePackageDataInDB.php';
//                                                    var formData = new FormData($("#addPatchValidate")[0]);
//                                                    $.ajax({
//                                                        url: url,
//                                                        type: 'POST',
//                                                        data: formData,
//                                                        async: true,
//                                                        success: function (data) {
//
//                                                            var response = $.trim(data);
//                                                            var splitResponse = response.split(',');
//                                                            if (splitResponse[1] == 'D') {
//
//                                                                id = splitResponse[0];
//                                                            } else {
//
//                                                                id = 0;
//                                                            }
//
//                                                            if (id != 0) {
////                                                                rightContainerSlideClose('rsc-add-container');
//                                                                $("#add_software_distribution").modal("hide");
//                                                                showCofigDetail(id, 'a');
//                                                            } else {
//
//                                                                Get_SoftwareRepositoryData();
//                                                                rightContainerSlideClose('rsc-add-container');
//                                                                location.reload();
//                                                            }
//                                                        },
//                                                        cache: false,
//                                                        contentType: false,
//                                                        processData: false
//                                                    });
//                                                }
//                                            } else if ($('#pSoftware').is(':checked')) {
//
//                                                if (pSoftName == "" || pSoftVer == "" || pKb == "" || pServicePack == "") {
//
//                                                    if (pSoftName == "") {
//                                                        errorNotify("<span>Software name is required</span>");
//                                                        return;
//                                                        setTimeout(function () {
//                                                            $("#pSoftNameMsg").fadeOut(5000);
//                                                        }, 3000);
//
//                                                    }
//                                                    if (pSoftVer == "") {
//                                                        errorNotify("<span>Software Version name is required</span>");
//                                                        return;
//                                                        setTimeout(function () {
//                                                            $("#pSoftVerMsg").fadeOut(5000);
//                                                        }, 3000);
//
//                                                    }
//                                                    if (pKb == "") {
//                                                        errorNotify("<span>Knowledge base is required</span>");
//                                                        return;
//                                                        setTimeout(function () {
//                                                            $("#pKbMsg").fadeOut(5000);
//                                                        }, 3000);
//
//                                                    }
//                                                    if (pServicePack == "") {
//                                                        errorNotify("<span>Service Pack is required</span>");
//                                                        return;
//                                                        setTimeout(function () {
//                                                            $("#pServicePackMsg").fadeOut(5000);
//                                                        }, 3000);
//
//                                                    }
//                                                } else {
//                                                    var url = 'SavePackageDataInDB.php';
//                                                    var formData = new FormData($("#addPatchValidate")[0]);
//                                                    $.ajax({
//                                                        url: url,
//                                                        type: 'POST',
//                                                        data: formData,
//                                                        async: true,
//                                                        success: function (data) {
//
//                                                            var response = $.trim(data);
//                                                            var splitResponse = response.split(',');
//                                                            if (splitResponse[1] == 'D') {
//
//                                                                id = splitResponse[0];
//                                                            } else {
//
//                                                                id = 0;
//                                                            }
//
//                                                            if (id != 0) {
////                                                                rightContainerSlideClose('rsc-add-container');
//                                                                $("#add_software_distribution").modal("hide");
//                                                                showCofigDetail(id, 'a');
//                                                            } else {
//
//                                                                Get_SoftwareRepositoryData();
//                                                                rightContainerSlideClose('rsc-add-container');
//                                                                location.reload();
//                                                            }
//                                                        },
//                                                        cache: false,
//                                                        contentType: false,
//                                                        processData: false
//                                                    });
//                                                }
//                                            } else if ($('#pRegistry').is(':checked')) {
//
//                                                if ($('#preDisCheck').is(':checked') && $('[name=platform]').val() == 'windows') {
//                                                    if ($('#pRegistry').val() != undefined && !isNaN($('#pRegistry').val()) && parseInt($('#pRegistry').val()) == 2) {
//                                                        if ($('[name=rootKey]').val() == undefined || $('[name=rootKey]').val() == '') {
//                                                            errorNotify("Please select a root key");
//                                                            return false;
//                                                        }
//                                                    }
//
//                                                    if (subKey == "") {
//                                                        errorNotify("<span>Sub key is required</span>");
//                                                        return false;
//                                                    }
//
//                                                    if ($('[name=pRegName]').val() == undefined || $('[name=pRegName]').val() == '') {
//                                                        errorNotify("Registry name is required");
//                                                        return false;
//                                                    }
//
//                                                    if ($('[name=pType]').val() == undefined || $('[name=pType]').val() == '') {
//                                                        errorNotify("Type is required");
//                                                        return false;
//                                                    }
//
//                                                    if ($('[name=pValue]').val() == undefined || $('[name=pValue]').val() == '') {
//                                                        errorNotify("Value is required");
//                                                        return false;
//                                                    }
//
//                                                }
//
//                                                if (subKey == "") {
//
//                                                    errorNotify("<span>Sub key is required</span>");
//                                                    return;
//                                                    setTimeout(function () {
//                                                        $("#subKeyMsg").fadeOut(5000);
//                                                    }, 3000);
//
//                                                } else {
//
//                                                    var url = 'SavePackageDataInDB.php';
//                                                    var formData = new FormData($("#addPatchValidate")[0]);
//                                                    $.ajax({
//                                                        url: url,
//                                                        type: 'POST',
//                                                        data: formData,
//                                                        async: true,
//                                                        success: function (data) {
//
//                                                            var response = $.trim(data);
//                                                            var splitResponse = response.split(',');
//                                                            if (splitResponse[1] == 'D') {
//
//                                                                id = splitResponse[0];
//                                                            } else {
//
//                                                                id = 0;
//                                                            }
//
//                                                            if (id != 0) {
////                                                                rightContainerSlideClose('rsc-add-container');
//                                                                $("#add_software_distribution").modal("hide");
//                                                                showCofigDetail(id, 'a');
//                                                            } else {
//
//                                                                Get_SoftwareRepositoryData();
//                                                                rightContainerSlideClose('rsc-add-container');
//                                                                location.reload();
//                                                            }
//                                                        },
//                                                        cache: false,
//                                                        contentType: false,
//                                                        processData: false
//                                                    });
//                                                }
//                                            }
//                                        } else {
//
//                                            if (!$('#pfile').is(':checked') && !$('#pSoftware').is(':checked') && !$('#pRegistry').is(':checked')) {
//                                                errorNotify("<span>Please select any one radio button</span>");
//                                                return;
//                                                setTimeout(function () {
//                                                    $("#preMsg").fadeOut(5000);
//                                                }, 3000);
//
//                                            }
//                                        }
//
//                                    } else {
//                                        var url = 'SavePackageDataInDB.php';
//                                        var formData = new FormData($("#addPatchValidate")[0]);
//                                        $.ajax({
//                                            url: url,
//                                            type: 'POST',
//                                            data: formData,
//                                            async: true,
//                                            success: function (data) {
//
//                                                var response = $.trim(data);
//                                                var splitResponse = response.split(',');
//                                                if (splitResponse[1] == 'D') {
//
//                                                    id = splitResponse[0];
//                                                } else {
//
//                                                    id = 0;
//                                                }
//
//                                                if (id != 0) {
////                                                    rightContainerSlideClose('rsc-add-container');
//                                                    $("#add_software_distribution").modal("hide");
//                                                    showCofigDetail(id, 'a');
//                                                } else {
//
//                                                    Get_SoftwareRepositoryData();
//                                                    rightContainerSlideClose('rsc-add-container');
//                                                    location.reload();
//                                                }
//                                            },
//                                            cache: false,
//                                            contentType: false,
//                                            processData: false
//                                        });
//                                    }
//                                }
//                            } else {
//                                var saveUrl = 'SavePackageDataInDB.php';
//                                var formData = new FormData($("#addPatchValidate")[0]);
//                                $.ajax({
//                                    url: saveUrl,
//                                    type: 'POST',
//                                    data: formData,
//                                    async: true,
//                                    success: function (data) {
//
//                                        var inserteddid;
//                                        var response = $.trim(data);
//                                        var splitResponse = response.split(',');
//                                        if (splitResponse[1] == 'D') {
//
//                                            inserteddid = splitResponse[0];
//                                        } else {
//
//                                            inserteddid = 0;
//                                        }
//
//                                        updateUploadStatus(inserteddid);
//                                    },
//                                    cache: false,
//                                    contentType: false,
//                                    processData: false
//                                });
//                            }
//
//                        } else {
//                            alert("hekkk");
//                            var url = 'SavePackageDataInDB.php';
//                            var formData = new FormData($("#addPatchValidate")[0]);
//                            $.ajax({
//                                url: url,
//                                type: 'POST',
//                                data: formData,
//                                async: true,
//                                success: function (data) {
//
//                                    var response = $.trim(data);
//                                    var splitResponse = response.split(',');
//                                    if (splitResponse[1] == 'D') {
//
//                                        id = splitResponse[0];
//                                    } else {
//
//                                        id = 0;
//                                    }
//
//                                    if (id != 0) {
////                                        rightContainerSlideClose('rsc-add-container');
//                                        $("#add_software_distribution").modal("hide");
//                                        showCofigDetail(id, 'a');
//                                    } else {
//
//                                        Get_SoftwareRepositoryData();
//                                        rightContainerSlideClose('rsc-add-container');
//                                        location.reload();
//                                    }
//                                },
//                                cache: false,
//                                contentType: false,
//                                processData: false
//                            });
//                        }
//
//                    }
//                }
//            });
//        } else {
//
//            var posKey = $("#posKey").val();
//            var distType = $("#distType").val();
//
//            if (posKey == "288" && distType == "3") {
//                var url = 'SavePackageDataInDB.php';
//                var formData = new FormData($("#addPatchValidate")[0]);
//                $.ajax({
//                    url: url,
//                    type: 'POST',
//                    data: formData,
//                    async: true,
//                    success: function (data) {
//
//                        var response = $.trim(data);
//                        var splitResponse = response.split(',');
//                        if (splitResponse[1] == 'D') {
//
//                            id = splitResponse[0];
//                        } else {
//
//                            id = 0;
//                        }
//
//                        if (id != 0) {
////                            rightContainerSlideClose('rsc-add-container');
//                            $("#add_software_distribution").modal("hide");
//                            showCofigDetail(id, 'a');
//                        } else {
//
//                            setTimeout(function () {
//                                location.reload();
//                            }, 1000);
//                        }
//                    },
//                    cache: false,
//                    contentType: false,
//                    processData: false
//                });
//
//                return true;
//            }
//
//            if (packName == "") {
//                errorNotify("<span>Package name is required field</span>");
//                return;
//                setTimeout(function () {
//                    $("#packNameMsg").fadeOut(5000);
//                }, 3000);
//
//            }
//
//            if (!$('#iplay').is(':checked')) {
//                if (packDesc == "") {
//                    errorNotify("<span>Package description is required field</span>");
//                    return;
//                    setTimeout(function () {
//                        $("#packDescMsg").fadeOut(5000);
//                    }, 3000);
//
//                }
//            }
//
//
//            if (version == "") {
//                errorNotify("<span>Software version name is required field</span>");
//                return;
//                setTimeout(function () {
//                    $("#versionMsg").fadeOut(5000);
//                }, 3000);
//
//            }
//            if (dPath == "") {
//                errorNotify("<span>Distribution path is required</span>");
//                return;
//                setTimeout(function () {
//                    $("#dPathMsg").fadeOut(5000);
//                }, 3000);
//
//            }
//            if (dTime == "") {
//                errorNotify("<span>Distrubution time is required</span>");
//                return;
//                setTimeout(function () {
//                    $("#dTimeMsg").fadeOut(5000);
//                }, 3000);
//
//            }
//
//            if (dTime != "" && dTime.length > 10) {
//                errorNotify("<span>Distribution time is too long, specify max 10 digit</span>");
//                return;
//            }
//
//            if (dvPath == "") {
//                errorNotify("<span>Distribution validation path is required</span>");
//                return;
//                setTimeout(function () {
//                    $("#dvPathMsg").fadeOut(5000);
//                }, 3000);
//
//            }
//            if (username == "") {
//                errorNotify("<span>User name is required</span>");
//                return;
//                setTimeout(function () {
//                    $("#usernameMsg").fadeOut(5000);
//                }, 3000);
//
//            }
//            if (password == "") {
//                errorNotify("<span>Password is required</span>");
//                return;
//                setTimeout(function () {
//                    $("#passwordMsg").fadeOut(5000);
//                }, 3000);
//
//            }
//            if (domain == "") {
//                errorNotify("<span>Domain is required</span>");
//                return;
//                setTimeout(function () {
//                    $("#domainMsg").fadeOut(5000);
//                }, 3000);
//
//            }
//
//            if ($('#preDisCheck').is(':checked')) {
//                if ($('#pfile').is(':checked') || $('#pSoftware').is(':checked') || $('#pRegistry').is(':checked')) {
//                    if ($('#pfile').is(':checked')) {
//                        if (pfilePath == "") {
//                            errorNotify("<span>File path is required</span>");
//                            return;
//                            setTimeout(function () {
//                                $("#pfilePathMsg").fadeOut(5000);
//                            }, 3000);
//
//                        }
//                    } else if ($('#pSoftware').is(':checked')) {
//                        if (pSoftName == "" || pSoftVer == "" || pKb == "" || pServicePack == "") {
//
//                            if (pSoftName == "") {
//                                errorNotify("<span>Software name is required</span>");
//                                return;
//                                setTimeout(function () {
//                                    $("#pSoftNameMsg").fadeOut(5000);
//                                }, 3000);
//
//                            }
//                            if (pSoftVer == "") {
//                                errorNotify("<span>Software version name is required</span>");
//                                return;
//                                setTimeout(function () {
//                                    $("#pSoftVerMsg").fadeOut(5000);
//                                }, 3000);
//
//                            }
//                            if (pKb == "") {
//                                errorNotify("<span>Knowledge base is required</span>");
//                                return;
//                                setTimeout(function () {
//                                    $("#pKbMsg").fadeOut(5000);
//                                }, 3000);
//
//                            }
//                            if (pServicePack == "") {
//                                errorNotify("<span>Service Pack is required</span>");
//                                return;
//                                setTimeout(function () {
//                                    $("#pServicePackMsg").fadeOut(5000);
//                                }, 3000);
//
//                            }
//                        }
//                    } else if ($('#pRegistry').is(':checked')) {
//                        if (subKey == "") {
//                            errorNotify("<span>Sub key is required</span>");
//                            return;
//                            setTimeout(function () {
//                                $("#subKeyMsg").fadeOut(5000);
//                            }, 3000);
//
//                        }
//                    }
//                } else {
//                    if (!$('#pfile').is(':checked') && !$('#pSoftware').is(':checked') && !$('#pRegistry').is(':checked')) {
//                        errorNotify("<span>Please select any one radio button</span>");
//                        return;
//                        setTimeout(function () {
//                            $("#preMsg").fadeOut(5000);
//                        }, 3000);
//
//                    }
//                }
//            }
//        }
//    }
//}

function addPackageFunction() {
    if (window.uploadCdnFtpUploadInProgress) {
        errorNotify('Upload in progress, please wait');
        return;
    }
    var uploadCount = window.uploadCount;
    var cdnuploadCheckbox = $('#cdnupload');
    var ftpuploadCheckBox = $('#ftpupload');
    var platformSelectBox = document.getElementById("platform");
    var SourceTypeNanohealRepoBox = $('#nhrep');
    var SourceTypeVendorRepoBox = $('#otrep');
    var sourceTypeAppleStore = $('#iplay');
    var sourceTypeNHealPlayStore = $('#nplay');
    var sourceTypeGooglePlayStore = $('#gplay');
    var statusFeed = $("#checkavail");
    var selectFromRepoCheckBox = $('#sdnSelect');
    var repoListBoxes = $('.repositoryList_checkbox');
    var typeSelectBox = document.getElementById("types");
    var fileUploadCheckBox = $('#uploadFile');
    var dartSelectBox = document.getElementById('posKey');
    var installType = $('#installType').val();

    $("#checkavail").hide().html("");
    statusFeed.removeClass('success').addClass('error');

    $('.required').not(':hidden').each(function () {
        var id = $(this).attr('id');
    });

    $("#packNameMsg").fadeIn();
    $("#packDescMsg").fadeIn();
    $("#versionMsg").fadeIn();
    $("#dPathMsg").fadeIn();
    $("#dTimeMsg").fadeIn();
    $("#dvPathMsg").fadeIn();
    $("#usernameMsg").fadeIn();
    $("#passwordMsg").fadeIn();
    $("#domainMsg").fadeIn();
    $("#sServerMsg").fadeIn();
    $("#pfilePathMsg").fadeIn();
    $("#pSoftNameMsg").fadeIn();
    $("#pSoftVerMsg").fadeIn();
    $("#pKbMsg").fadeIn();
    $("#pServicePackMsg").fadeIn();
    $("#subKeyMsg").fadeIn();
    $("#preMsg").fadeIn();


    $("#packNameMsg").html("");
    $("#packDescMsg").html("");
    $("#versionMsg").html("");
    $("#dPathMsg").html("");
    $("#dTimeMsg").html("");
    $("#dvPathMsg").html("");
    $("#usernameMsg").html("");
    $("#passwordMsg").html("");
    $("#domainMsg").html("");
    $("#sServerMsg").html("");
    $("#pfilePathMsg").html("");
    $("#pSoftNameMsg").html("");
    $("#pSoftVerMsg").html("");
    $("#pKbMsg").html("");
    $("#pServicePackMsg").html("");
    $("#subKeyMsg").html("");
    $("#preMsg").html("");

    var packName = $("#packName").val();
    var types = $("#types").val();
    var version = $("#version").val();
    var preCheckPathBox = $("#preCheckPath").val();
    var platform = $("#platform").val();
    var filename = $("#filename").val();
    var packDesc = $("#packDesc").val();
    var ftpupload = $("#ftpupload").val();
    var cdnupload = $("#cdnupload").val();
    var actionDate = $("#actionDate").val();
    var notify = $("#notify").val();
    var uniAction = $("#uniAction").val();
    var manifesttypes = $("#manifesttypes").val();
    var manifestname = $("#manifestname").val();
    var appId = $("#appId").val();
    var dPath = $("#dPath").val();
    var dTime = $("#dTime").val();
    var dvPath = $("#dvPath").val();
    var username = $("#username").val();
    var password = $("#password").val();
    var domain = $("#domain").val();
    var pfilePath = $("#pfilePath").val();
    var pSoftName = $("#pSoftName").val();
    var pSoftVer = $("#pSoftVer").val();
    var pKb = $("#pKb").val();
    var pServicePack = $("#pServicePack").val();
    var subKey = $("#subKey").val();
    var posKey = $("#posKey").val();
    var distType = $("#distType").val();
    if($('#same32_64config').is(':checked')){
        var configType = 'same';
    }else{
        var configType = 'different';
    }
    var isGlobalOptionChecked = false;

    var disFlag = true;
    var sdnSelect = $('#cdnSelType').val();

//    if(sdnSelect == 'upload'){
//        disFlag = false;
//    }else{
//        disFlag = true;
//    }
//    if ($("#sdnSelect").css("display") === "inline-block" || $("#sdnSelect").css("display") === "block") {
//        disFlag = true;
//    }
    if (disFlag) {

        if (sdnSelect != 'upload') {
            if (platform === 'android') {
                if ($('#installType').val() == '3' || $('#installType').val() == '5') {
                    var count = 0;
                    $('.required').not(':hidden').each(function () {
                        if ($(this).val() == '') {
                            count++;
                        }
                    });
                    if (count > 0) {
                        errorNotify('Please enter the mandatory field');
                        return false;
                    }
                }
                if ($("#posKey").val() == "288" || $("#posKey").val() == "415") {
                    $("#val_err_msg").show();
                    $("#val_err_msg").html("");
                    var val_flag = 0;
                    $(".val_spcl_chars").each(function () {
                        var strn = $(this).val();
                        var regx = /[#$,]/;
                        if (strn.match(regx)) {
                            val_flag++;
                        }
                    });
                    if (val_flag > 0) {
                        errorNotify("'#','$' & ',' are not allowed for configuration");
                        return false;
                    } else {
                        regular_flow();
                        return true;
                    }
                }
            } else {
                regular_flow();
                return true;
            }
        } else {
            regular_flow();
            return true;
        }
    }

    var postData;

    function regular_flow() {

        if (packName != "" && types != "" && version != "" && platform != "" && packDesc != "") {

            if (packName.length > 128) {
                errorNotify("The package name length should not be more than 128 characters");
                return false;
            }

            if (packDesc.length > 256) {
                errorNotify("The package description length should not be more than 256 characters");
                return false;
            }

            if (version.length > 128) {
                errorNotify("The version length should not be more than 128 characters");
                return false;
            }

            postData = {
                'packName': packName,
                'version': version,
                'description': packDesc,
                'function': 'checkAvailabilityFn',
                'csrfMagicToken': csrfMagicToken
            };

            $.ajax({
                url: "SWD_Function.php",
                type: 'POST',
                data: postData,
                success: function (data) {
                    data = $.parseJSON(data);
                    if (data.data === "true") {
                        errorNotify("Pacakge already exists");
                        return false;
                    } else if (data.data === "false") {
                        if (($('#secure').is(':checked'))) {
                            if (username == "" || password == "" || domain == "") {

                                if (username == "") {
                                    errorNotify("<span>User name is required</span>");
                                    return;
                                    setTimeout(function () {
                                        $("#usernameMsg").fadeOut(5000);
                                    }, 3000);
                                }
                                if (password == "") {
                                    errorNotify("<span>Password is required</span>");
                                    return;
                                    setTimeout(function () {
                                        $("#passwordMsg").fadeOut(5000);
                                    }, 3000);
                                }
                                if (domain == "") {
                                    errorNotify("<span>Domain is required</span>");
                                    return;
                                    setTimeout(function () {
                                        $("#domainMsg").fadeOut(5000);
                                    }, 3000);

                                    return false;
                                }
                            }
                        }


                        if (($('#ftpupload').is(':checked')) || ($('#cdnupload').is(':checked'))) {

                            if ($('#distCheck').is(':checked')) {

                                if (dPath == "" || dTime == "" || dvPath == "") {

                                    if (dPath == "") {
                                        errorNotify("<span>Distribution path is required</span>");
                                        return;
                                        setTimeout(function () {
                                            $("#dPathMsg").fadeOut(5000);
                                        }, 3000);

                                    }
                                    if (dTime == "") {
                                        errorNotify("<span>Distribution time is required</span>");
                                        return;
                                        setTimeout(function () {
                                            $("#dTimeMsg").fadeOut(5000);
                                        }, 3000);

                                    }

                                    if (dTime != "" && dTime.length > 10) {
                                        errorNotify("<span>Distribution time is too long, specify max 10 digit</span>");
                                        return;
                                    }

                                    if (dvPath == "") {
                                        errorNotify("<span>Distribution validation path is required</span>");
                                        return;
                                        setTimeout(function () {
                                            $("#dvPathMsg").fadeOut(5000);
                                        }, 3000);

                                        return false;
                                    }

                                } else {
                                    if ($('#preDisCheck').is(':checked')) {

                                        if ($('#pfile').is(':checked') || $('#pSoftware').is(':checked') || $('#pRegistry').is(':checked')) {

                                            if ($('#pfile').is(':checked')) {

                                                if (pfilePath == "") {

                                                    errorNotify("<span>File Path is required</span>");
                                                    return;
                                                    setTimeout(function () {
                                                        $("#pfilePathMsg").fadeOut(5000);
                                                    }, 3000);

                                                } else {
                                                    var url = 'SavePackageDataInDB.php';
                                                    var formData = new FormData($("#addPatchValidate")[0]);
                                                    formData.append("csrfMagicToken",csrfMagicToken);
                                                    $.ajax({
                                                        url: url,
                                                        type: 'POST',
                                                        data: formData,
                                                        async: true,
                                                        success: function (data) {

                                                            var response = $.trim(data);
                                                            var splitResponse = response.split(',');
                                                            if (splitResponse[1] == 'D') {

                                                                id = splitResponse[0];
                                                            } else {

                                                                id = 0;
                                                            }

                                                            if (id != 0) {
//                                                                rightContainerSlideClose('rsc-add-container');
                                                                $("#add_software_distribution").modal("hide");
                                                                showCofigDetail(id, 'a');
                                                            } else {

                                                                Get_SoftwareRepositoryData();
                                                                rightContainerSlideClose('rsc-add-container');
                                                                location.reload();
                                                            }
                                                        },
                                                        cache: false,
                                                        contentType: false,
                                                        processData: false
                                                    });
                                                }
                                            } else if ($('#pSoftware').is(':checked')) {

                                                if (pSoftName == "" || pSoftVer == "" || pKb == "" || pServicePack == "") {

                                                    if (pSoftName == "") {
                                                        errorNotify("<span>Software name is required</span>");
                                                        return;
                                                        setTimeout(function () {
                                                            $("#pSoftNameMsg").fadeOut(5000);
                                                        }, 3000);

                                                    }
                                                    if (pSoftVer == "") {
                                                        errorNotify("<span>Software Version name is required</span>");
                                                        return;
                                                        setTimeout(function () {
                                                            $("#pSoftVerMsg").fadeOut(5000);
                                                        }, 3000);

                                                    }
                                                    if (pKb == "") {
                                                        errorNotify("<span>Knowledge base is required</span>");
                                                        return;
                                                        setTimeout(function () {
                                                            $("#pKbMsg").fadeOut(5000);
                                                        }, 3000);

                                                    }
                                                    if (pServicePack == "") {
                                                        errorNotify("<span>Service Pack is required</span>");
                                                        return;
                                                        setTimeout(function () {
                                                            $("#pServicePackMsg").fadeOut(5000);
                                                        }, 3000);

                                                    }
                                                } else {
                                                    var url = 'SavePackageDataInDB.php';
                                                    var formData = new FormData($("#addPatchValidate")[0]);
                                                    formData.append("csrfMagicToken",csrfMagicToken);
                                                    $.ajax({
                                                        url: url,
                                                        type: 'POST',
                                                        data: formData,
                                                        async: true,
                                                        success: function (data) {

                                                            var response = $.trim(data);
                                                            var splitResponse = response.split(',');
                                                            if (splitResponse[1] == 'D') {

                                                                id = splitResponse[0];
                                                            } else {

                                                                id = 0;
                                                            }

                                                            if (id != 0) {
//                                                                rightContainerSlideClose('rsc-add-container');
                                                                $("#add_software_distribution").modal("hide");
                                                                showCofigDetail(id, 'a');
                                                            } else {

                                                                Get_SoftwareRepositoryData();
                                                                rightContainerSlideClose('rsc-add-container');
                                                                location.reload();
                                                            }
                                                        },
                                                        cache: false,
                                                        contentType: false,
                                                        processData: false
                                                    });
                                                }
                                            } else if ($('#pRegistry').is(':checked')) {

                                                if ($('#preDisCheck').is(':checked') && $('[name=platform]').val() == 'windows') {
                                                    if ($('#pRegistry').val() != undefined && !isNaN($('#pRegistry').val()) && parseInt($('#pRegistry').val()) == 2) {
                                                        if ($('[name=rootKey]').val() == undefined || $('[name=rootKey]').val() == '') {
                                                            errorNotify("Please select a root key");
                                                            return false;
                                                        }
                                                    }

                                                    if (subKey == "") {
                                                        errorNotify("<span>Sub key is required</span>");
                                                        return false;
                                                    }

                                                    if ($('[name=pRegName]').val() == undefined || $('[name=pRegName]').val() == '') {
                                                        errorNotify("Registry name is required");
                                                        return false;
                                                    }

                                                    if ($('[name=pType]').val() == undefined || $('[name=pType]').val() == '') {
                                                        errorNotify("Type is required");
                                                        return false;
                                                    }

                                                    if ($('[name=pValue]').val() == undefined || $('[name=pValue]').val() == '') {
                                                        errorNotify("Value is required");
                                                        return false;
                                                    }

                                                }

                                                if (subKey == "") {

                                                    errorNotify("<span>Sub key is required</span>");
                                                    return;
                                                    setTimeout(function () {
                                                        $("#subKeyMsg").fadeOut(5000);
                                                    }, 3000);

                                                } else {

                                                    var url = 'SavePackageDataInDB.php';
                                                    var formData = new FormData($("#addPatchValidate")[0]);
                                                    formData.append("csrfMagicToken",csrfMagicToken);
                                                    $.ajax({
                                                        url: url,
                                                        type: 'POST',
                                                        data: formData,
                                                        async: true,
                                                        success: function (data) {

                                                            var response = $.trim(data);
                                                            var splitResponse = response.split(',');
                                                            if (splitResponse[1] == 'D') {

                                                                id = splitResponse[0];
                                                            } else {

                                                                id = 0;
                                                            }

                                                            if (id != 0) {
//                                                                rightContainerSlideClose('rsc-add-container');
                                                                $("#add_software_distribution").modal("hide");
                                                                showCofigDetail(id, 'a');
                                                            } else {

                                                                Get_SoftwareRepositoryData();
                                                                rightContainerSlideClose('rsc-add-container');
                                                                location.reload();
                                                            }
                                                        },
                                                        cache: false,
                                                        contentType: false,
                                                        processData: false
                                                    });
                                                }
                                            }
                                        } else {

                                            if (!$('#pfile').is(':checked') && !$('#pSoftware').is(':checked') && !$('#pRegistry').is(':checked')) {
                                                errorNotify("<span>Please select any one radio button</span>");
                                                return;
                                                setTimeout(function () {
                                                    $("#preMsg").fadeOut(5000);
                                                }, 3000);

                                            }
                                        }

                                    } else {
                                        var url = 'SavePackageDataInDB.php';
                                        var formData = new FormData($("#addPatchValidate")[0]);
                                        formData.append("csrfMagicToken",csrfMagicToken);
                                        $.ajax({
                                            url: url,
                                            type: 'POST',
                                            data: formData,
                                            async: true,
                                            success: function (data) {

                                                var response = $.trim(data);
                                                var splitResponse = response.split(',');
                                                if (splitResponse[1] == 'D') {

                                                    id = splitResponse[0];
                                                } else {

                                                    id = 0;
                                                }

                                                if (id != 0) {
//                                                    rightContainerSlideClose('rsc-add-container');
                                                    $("#add_software_distribution").modal("hide");
                                                    showCofigDetail(id, 'a');
                                                } else {

                                                    Get_SoftwareRepositoryData();
                                                    rightContainerSlideClose('rsc-add-container');
                                                    location.reload();
                                                }
                                            },
                                            cache: false,
                                            contentType: false,
                                            processData: false
                                        });
                                    }
                                }
                            } else {
                                var saveUrl = 'SavePackageDataInDB.php';
                                var formData = new FormData($("#addPatchValidate")[0]);
                                formData.append("csrfMagicToken",csrfMagicToken);
                                $.ajax({
                                    url: saveUrl,
                                    type: 'POST',
                                    data: formData,
                                    async: true,
                                    success: function (data) {

                                        var inserteddid;
                                        var response = $.trim(data);
                                        var splitResponse = response.split(',');
                                        if (splitResponse[1] == 'D') {

                                            inserteddid = splitResponse[0];
                                        } else {

                                            inserteddid = 0;
                                        }

                                        updateUploadStatus(inserteddid);
                                    },
                                    cache: false,
                                    contentType: false,
                                    processData: false
                                });
                            }

                        } else {
                            var url = 'SavePackageDataInDB.php';
                            var formData = new FormData($("#addPatchValidate")[0]);
                            formData.append("csrfMagicToken",csrfMagicToken);
                            $.ajax({
                                url: url,
                                type: 'POST',
                                data: formData,
                                async: true,
                                success: function (data) {

                                    var response = $.trim(data);
                                    var splitResponse = response.split(',');
                                    if (splitResponse[1] == 'D') {

                                        id = splitResponse[0];
                                    } else {

                                        id = 0;
                                    }

                                    if (id != 0) {
//                                        rightContainerSlideClose('rsc-add-container');
                                        $("#add_software_distribution").modal("hide");
                                        showCofigDetail(id, 'a');
                                    } else {

                                        Get_SoftwareRepositoryData();
                                        rightContainerSlideClose('rsc-add-container');
                                        location.reload();
                                    }
                                },
                                cache: false,
                                contentType: false,
                                processData: false
                            });
                        }

                    }
                }
            });
        } else {

            var posKey = $("#posKey").val();
            var distType = $("#distType").val();

            if (posKey == "288" && distType == "3") {
                var url = 'SavePackageDataInDB.php';
                var formData = new FormData($("#addPatchValidate")[0]);
                formData.append("csrfMagicToken",csrfMagicToken);
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    async: true,
                    success: function (data) {

                        var response = $.trim(data);
                        var splitResponse = response.split(',');
                        if (splitResponse[1] == 'D') {

                            id = splitResponse[0];
                        } else {

                            id = 0;
                        }

                        if (id != 0) {
//                            rightContainerSlideClose('rsc-add-container');
                            $("#add_software_distribution").modal("hide");
                            showCofigDetail(id, 'a');
                        } else {

                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        }
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });

                return true;
            }

            if (packName == "") {
                errorNotify("<span>Package name is required field</span>");
                return;
                setTimeout(function () {
                    $("#packNameMsg").fadeOut(5000);
                }, 3000);

            }

            if (!$('#iplay').is(':checked')) {
                if (packDesc == "") {
                    errorNotify("<span>Package description is required field</span>");
                    return;
                    setTimeout(function () {
                        $("#packDescMsg").fadeOut(5000);
                    }, 3000);

                }
            }


            if (version == "") {
                errorNotify("<span>Software version name is required field</span>");
                return;
                setTimeout(function () {
                    $("#versionMsg").fadeOut(5000);
                }, 3000);

            }
            if (dPath == "") {
                errorNotify("<span>Distribution path is required</span>");
                return;
                setTimeout(function () {
                    $("#dPathMsg").fadeOut(5000);
                }, 3000);

            }
            if (dTime == "") {
                errorNotify("<span>Distrubution time is required</span>");
                return;
                setTimeout(function () {
                    $("#dTimeMsg").fadeOut(5000);
                }, 3000);

            }

            if (dTime != "" && dTime.length > 10) {
                errorNotify("<span>Distribution time is too long, specify max 10 digit</span>");
                return;
            }

            if (dvPath == "") {
                errorNotify("<span>Distribution validation path is required</span>");
                return;
                setTimeout(function () {
                    $("#dvPathMsg").fadeOut(5000);
                }, 3000);

            }
            if (username == "") {
                errorNotify("<span>User name is required</span>");
                return;
                setTimeout(function () {
                    $("#usernameMsg").fadeOut(5000);
                }, 3000);

            }
            if (password == "") {
                errorNotify("<span>Password is required</span>");
                return;
                setTimeout(function () {
                    $("#passwordMsg").fadeOut(5000);
                }, 3000);

            }
            if (domain == "") {
                errorNotify("<span>Domain is required</span>");
                return;
                setTimeout(function () {
                    $("#domainMsg").fadeOut(5000);
                }, 3000);

            }

            if ($('#preDisCheck').is(':checked')) {
                if ($('#pfile').is(':checked') || $('#pSoftware').is(':checked') || $('#pRegistry').is(':checked')) {
                    if ($('#pfile').is(':checked')) {
                        if (pfilePath == "") {
                            errorNotify("<span>File path is required</span>");
                            return;
                            setTimeout(function () {
                                $("#pfilePathMsg").fadeOut(5000);
                            }, 3000);

                        }
                    } else if ($('#pSoftware').is(':checked')) {
                        if (pSoftName == "" || pSoftVer == "" || pKb == "" || pServicePack == "") {

                            if (pSoftName == "") {
                                errorNotify("<span>Software name is required</span>");
                                return;
                                setTimeout(function () {
                                    $("#pSoftNameMsg").fadeOut(5000);
                                }, 3000);

                            }
                            if (pSoftVer == "") {
                                errorNotify("<span>Software version name is required</span>");
                                return;
                                setTimeout(function () {
                                    $("#pSoftVerMsg").fadeOut(5000);
                                }, 3000);

                            }
                            if (pKb == "") {
                                errorNotify("<span>Knowledge base is required</span>");
                                return;
                                setTimeout(function () {
                                    $("#pKbMsg").fadeOut(5000);
                                }, 3000);

                            }
                            if (pServicePack == "") {
                                errorNotify("<span>Service Pack is required</span>");
                                return;
                                setTimeout(function () {
                                    $("#pServicePackMsg").fadeOut(5000);
                                }, 3000);

                            }
                        }
                    } else if ($('#pRegistry').is(':checked')) {
                        if (subKey == "") {
                            errorNotify("<span>Sub key is required</span>");
                            return;
                            setTimeout(function () {
                                $("#subKeyMsg").fadeOut(5000);
                            }, 3000);

                        }
                    }
                } else {
                    if (!$('#pfile').is(':checked') && !$('#pSoftware').is(':checked') && !$('#pRegistry').is(':checked')) {
                        errorNotify("<span>Please select any one radio button</span>");
                        return;
                        setTimeout(function () {
                            $("#preMsg").fadeOut(5000);
                        }, 3000);

                    }
                }
            }
        }
    }
}

function updateUploadStatus(idInserted) {

    $.ajax({
        url: 'SWD_Function.php',
        type: 'POST',
        data: {function: 'updateUploadStatFn', id: idInserted, csrfMagicToken: csrfMagicToken},
        success: function (data) {

            if (idInserted != 0) {

                rightContainerSlideClose('rsc-add-container');
                $("#add_software_distribution").modal("hide");
                showCofigDetail(idInserted, 'a');
            } else {

                Get_SoftwareRepositoryData();
                rightContainerSlideClose('rsc-add-container');
                location.reload();
            }
        }
    });
}

// Function to fetch uploaded files when selecting CDN Repository radio button.
function selectFilefromRepositoryAdd(obj) {

    $("input[type=checkbox]:checked.repositoryList_checkbox").each(function (i) {
        $(this).removeAttr('checked');
    });

    selectedVal = $(obj).val();

    var removedExtension = removeExtension(selectedVal);
    $("#packName").val(removedExtension);
    $("#packName").attr("title", removedExtension);
    $("#filename").val(selectedVal);
    $(obj).prop('checked', true);
    $('#packName').prev().parent().removeClass('is-empty');

}
function cdnuploadtype(val){
    var value = $('#cdnSelType').val(); 
    $('#repo_name1').hide();
    $('#selectfile_n0me').hide();
    var sameconfig ='';
    if($('#same32_64config').is(':checked')){
        sameconfig = true;
    }else{
        sameconfig = false;
    }
    var repoLinkContainer = $('#repoLinkContainer');
    var repoLinkContainer2 = $('#repoLinkContainer2');
    var rawUploadContainer = $('#rawUploadContainer');
    var rawUploadContainer2 = $('#rawUploadContainer2');
    rawUploadContainer.hide();
    rawUploadContainer2.hide();
    repoLinkContainer.hide();
    repoLinkContainer2.hide();
    $('.nhrepsel1,.nhrepsel2').hide();
    $('.showrepository,.showrepository2,.CdnFilesList,#fine-uploader-s3').hide();
    $('.nhrep,#fine-uploader-manual-trigger').hide();
    $('.nhrep2,#fine-uploader-manual-trigger2').hide();
    $('.CdnFilesList,#fine-uploader-s3,.showrepository').hide();
}

function locationSelect(){
    var value = $('#uploads').val();
    var repoLinkContainer = $('#repoLinkContainer');
    var repoLinkContainer2 = $('#repoLinkContainer2');
    var rawUploadContainer = $('#rawUploadContainer');
    var rawUploadContainer2 = $('#rawUploadContainer2');
    rawUploadContainer.hide();
    rawUploadContainer2.hide();
    repoLinkContainer.hide();
    repoLinkContainer2.hide();
    if(value == '1'){
        $('.cdnoptiontype').hide();
    }else{
        $('.cdnoptiontype').show();
    }
}

$('#cdnupload').on("change",function(){
    $('.cdnoptiontype').show();
});

$('#ftpupload').on("change",function(){
    $('.cdnoptiontype').hide();
});


$("#same32_64config").on("click", function () {
    $('#selectfile_name').show();
    $('#selectfile_name').html('Select the file ');
    $('#uploadFile').trigger('click');
    var repoLinkContainer = $('#repoLinkContainer');
    var repoLinkContainer2 = $('#repoLinkContainer2');
    var rawUploadContainer = $('#rawUploadContainer');
    var rawUploadContainer2 = $('#rawUploadContainer2');
    var uploads = $('#uploads').val();
    if(uploads == '1'){
        $('#selectfile_name').html('Select the file');
        var ftpValue = 'true';
        $('.nhrep,#fine-uploader-manual-trigger').show();
        $('.showrepository,.CdnFilesList,#fine-uploader-s3').hide();
        $("#files,#filevalidationtext_cdn").html('');
        rawUploadContainer.show();
        repoLinkContainer.hide();
        rawUploadContainer2.hide();
        repoLinkContainer2.hide();
    }else{
        var ftpValue = 'false';
        var value = $('#cdnSelType').val(); 
        $('#repo_name1').hide();
        $('#selectfile_name').hide();
        var sameconfig ='';
        if(value === 'upload'){
            $('#selectfile_name').show();
            $('#selectfile_name').html('Select the file');
            $('.nhrep,#fine-uploader-manual-trigger').show();
            $('.CdnFilesList,#fine-uploader-s3,.showrepository').hide();
            $("#files,#filevalidationtext_cdn").html('');
            rawUploadContainer.show();
            rawUploadContainer2.hide();
            repoLinkContainer.hide();
            repoLinkContainer2.hide();
        }else if(value === 'select'){
            $('#repo_name1').show();
            $('#repo_name1').html('Select the file from repository');
            var pl = $("#platform").val();
            $.ajax({
        url: 'SWD_Function.php',
        type: 'POST',
        async: true,
        data: {function: "addFn", platform: pl, csrfMagicToken: csrfMagicToken},
        success: function (data) {

            $("#loaderRepository").hide();
            $("#files").html(data);
        }
    });
            rawUploadContainer.hide();
            rawUploadContainer2.hide();
            repoLinkContainer.show();
            $('#sdnSelect').trigger("click");
            repoLinkContainer2.hide();
            $('.nhrepsel1').show();
            $('.nhrep2,#fine-uploader-manual-trigger2').hide();
            $('.nhrep,#fine-uploader-manual-trigger').hide();
            $('.showrepository').show();
            $('.CdnFilesList,#fine-uploader-s3').show();
            $("#files,#filevalidationtext_cdn").html('');
        }
    }
    
    $("input[type=radio]:checked.selectSource").each(function (i) {
        $(this).removeAttr('checked');
    });
    'use strict';
    var AWSACCESS = $("#AWSACCESS").val();
    var AWSSECRET = $("#AWSSECRET").val();
    var AWSBUCKET = $("#AWSBUCKET").val();
    var AWSREGION = $("#AWSREGION").val();
    
    var FTPURL_Add = $("#sFtpUrl").val();
    var splFTP = FTPURL_Add.split("/");
    var lenFTP = splFTP.length;
    // var url_part = "/" + splFTP[lenFTP - 3] + "/" + splFTP[lenFTP - 2] + "/";
    var url_part = BASE_PATH+'/swd/';
    var pform = $("#platform").val();

    if (pform == undefined || pform == 'undefined' || pform == '') {
        $("#checkavail").text('Please select a platform').show();
        return;
    }

    if (pform == "windows" || pform == "mac" || pform == "linux") {

        if ($('#nhrep').is(':checked')) {

            var manualUploader = new qq.FineUploader({
                element: document.getElementById('fine-uploader-manual-trigger'),
                template: 'qq-template-manual-trigger',
                multiple: false,
                callbacks: {
                    onCancel: function (id, file) {

                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage').css('cursor', 'pointer');

                        setTimeout(function () {
                            $(".qq-upload-button input").prop("disabled", false);
                            $(".qq-upload-button").removeClass("disabled");
                        }, 500);
                        if (uploadCount > 0) {
                            $("#filevalidationtext").hide();
                            this.reset();
                            //                        $('#fileupload').uploadify('cancel', '*');
                            $("#files").html('');
                            $("#filevalidationtext").html('');
                            $('#upload').unbind('click');
                            $("#fileupload").removeAttr('disabled');
                            $(".addedfilesList").html("");
                            $("#fileUp").fadeIn();
                            $(".clearFilesDiv,.addedfilesList").fadeOut();
                            $("#addPackage").prop('disabled', true);
                            uploadCount--;
                        } else {
                            uploadCount = 0;
                        }
                    },
                    onSubmit: function (id, file) {
                        fileName = file;
                        splitFile = fileName.split('.');
                        removeExt = splitFile[splitFile.length - 1];
                        removedFileExtension = fileName.replace('.' + removeExt, '');
                            
                        if(removeExt == 'php' || removeExt == 'html' || removeExt == 'js'){
                            $.notify("File Type restricted to upload");
                            return false;
                        }
                        var newFile = file;
                        this.setName(id, newFile);

                        window.uploadCdnFtpUploadInProgress = true;
                        $('#addPackage').css('cursor', 'wait');
                        $("#addPackage").prop("disabled", true);
                        $(".addedfilesList").fadeIn();
                        $("#filevalidationtext").hide();
                        uploadCount == 0;
                        if ($('#nhrep').is(':checked')) {

                            uploadCount++;
                        }
                        if (uploadCount == 1) {
                            fileName = file;
                            splitFile = fileName.split('.');
                            removeExt = splitFile[splitFile.length - 1];
                            removedFileExtension = fileName.replace('.' + removeExt, '');
                            $("#files").append("<li class='selectedFile'>" + file + "</li>");
                            $('input[type=text]').prev().parent().removeClass('is-empty');
                            $("#packName").val(removedFileExtension);
                            $("#packName").attr("title", removedFileExtension);
                            $('#filename').val(fileName);
                        }

                        if (uploadCount >= 1) {
                            $("#checkavail").hide().html("");
                            setTimeout(function () {
                                $(".qq-upload-button input").prop("disabled", true);
                                $(".qq-upload-button").addClass("disabled");
                            }, 500);
                        }

                        $("#filename").val(newFile);
                    },
                    onProgress: function (id, file, totalBytesUploaded, totalBytesTotal) {
                        $("#filevalidationtext").hide();
                        if (totalBytesUploaded >= totalBytesTotal) {
                            $("#addPackage").prop("disabled", false);
                            $("#upStatus").val("1");
                            $(".clearFilesDiv").fadeIn();
                            if (uploadCount == 2) {
                                $("#progress1").fadeOut();
                            }
                            if (uploadCount == 3) {
                                $("#fileUp").fadeOut();
                            }
                            $(".addedfilesList").append("<label>" + file + "</label>");
                        }
                    },
                    onDelete: function (id) {
                        fileName = file;
                        splitFile = fileName.split('.');
                        removeExt = splitFile[splitFile.length - 1];
                        if(removeExt == 'php' || removeExt == 'html' || removeExt == 'js'){
                            $.notify("File Type restricted to upload");
                            return false;
                        }
                        $("#filevalidationtext").hide();
                        this.reset();
//                        $('#fileupload').uploadify('cancel', '*');
                        $("#files").html('');
                        $("#filevalidationtext").html('');
                        $('#upload').unbind('click');
                        $("#fileupload").removeAttr('disabled');
                        $(".addedfilesList").html("");
                        $("#fileUp").fadeIn();
                        $(".clearFilesDiv,.addedfilesList").fadeOut();
                        $("#addPackage").prop('disabled', true);
                        setTimeout(function () {
                            $(".qq-upload-button input").prop("disabled", false);
                            $(".qq-upload-button").removeClass("disabled");
                        }, 500);
                        uploadCount = 0;
                        
                    },
                    onError: function (event, id, name, errorReason, xhrOrXdr) {
                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage').css('cursor', 'pointer');
                        $("#filevalidationtext").show().html(name).focus();
                    },
                    onComplete: function (id,file) {
                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage').css('cursor', 'pointer');
                        if(uploads == '2'){
                            onUploadAddSubmit(file, AWSACCESS, AWSSECRET, AWSBUCKET, AWSREGION);
                    }
                    }
                },
                messages: {
                    typeError: 'Invalid extension detected in file, {file}.',
                    emptyError: '{file} is Empty, Please upload a valid file'
                },
                request: {
                    endpoint: url_part + "vendor/fineuploader/php-traditional-server/endpoint.php"
                },
                deleteFile: {
                    enabled: true,
                    endpoint: url_part + "vendor/fineuploader/php-traditional-server/endpoint.php"
                },
                chunking: {
                    enabled: true,
                    concurrent: {
                        enabled: true
                    },
                    success: {
                        endpoint: url_part + "vendor/fineuploader/php-traditional-server/endpoint.php?done"
                    }
                },
                resume: {
                    enabled: true
                },
                retry: {
                    enableAuto: false
                },
                thumbnails: {
                    placeholders: {
                        //waitingPath: '/source/placeholders/waiting-generic.png',
                        //notAvailablePath: '/source/placeholders/not_available-generic.png'
                    }
                },
                autoUpload: true,
                debug: false
            });

            qq(document.getElementById("trigger-upload")).attach("click", function () {
                manualUploader.uploadStoredFiles();
            });
        }
    }
});

//For Different 34 and 64 bit
$("#diff32_64config").on("click", function () {
    $('#repo_name1').hide();
    $('#selectfile_name').html('Select the file for 32 bit');
    $('#uploadFile').trigger('click');
    var repoLinkContainer = $('#repoLinkContainer');
    var repoLinkContainer2 = $('#repoLinkContainer2');
    var rawUploadContainer = $('#rawUploadContainer');
    var rawUploadContainer2 = $('#rawUploadContainer2');
    var uploads = $('#uploads').val();
    if(uploads == '1'){
        var ftpValue = 'true';
        $('.nhrep2,#fine-uploader-manual-trigger2').show();
        $('.nhrep,#fine-uploader-manual-trigger').show();
        $('.showrepository,.CdnFilesList,#fine-uploader-s3').hide();
        $("#files,#filevalidationtext_cdn").html('');
        rawUploadContainer.show();
        repoLinkContainer.hide();
        rawUploadContainer2.show();
        repoLinkContainer2.hide();
    }else{
        var ftpValue = 'false';
        var value = $('#cdnSelType').val(); 
        $('#repo_name1').hide();
        $('#selectfile_name').hide();
        if(value === 'upload'){
            $('#selectfile_name').show();
            $('#selectfile_name').html('Select the file for 32 bit');
            rawUploadContainer.show();
            rawUploadContainer2.show();
            repoLinkContainer.hide();
            repoLinkContainer2.hide();
            $('.nhrep,#fine-uploader-manual-trigger').show();
            $('.nhrep2,#fine-uploader-manual-trigger2').show();
            $('.CdnFilesList,#fine-uploader-s3,.showrepository').hide();
            $("#files,#filevalidationtext_cdn").html('');
        }else if(value === 'select'){
            $('#repo_name1').show();
            $('#repo_name1').html('Select file from repository for 32 bit');
            rawUploadContainer.hide();
            rawUploadContainer2.hide();
            $('#sdnSelect').trigger("click");
            $('#sdnSelect2').trigger("click");
            repoLinkContainer.show();
            repoLinkContainer2.show();
            $('.nhrepsel1,.nhrepsel2').show();
            $('.showrepository,.showrepository2,.CdnFilesList,#fine-uploader-s3').show();
            $("#files,#filevalidationtext_cdn").html('');
        }
    }
 
    $("input[type=radio]:checked.selectSource").each(function (i) {
        $(this).removeAttr('checked');
    });
    $("input[type=radio]:checked.selectSource2").each(function (i) {
        $(this).removeAttr('checked');
    });
    'use strict';
    var FTPURL_Add = $("#sFtpUrl").val();
    var splFTP = FTPURL_Add.split("/");
    var lenFTP = splFTP.length;
    
    var AWSACCESS = $("#AWSACCESS").val();
    var AWSSECRET = $("#AWSSECRET").val();
    var AWSBUCKET = $("#AWSBUCKET").val();
    var AWSREGION = $("#AWSREGION").val();
    // var url_part = "/" + splFTP[lenFTP - 3] + "/" + splFTP[lenFTP - 2] + "/";
    var url_part = BASE_PATH+'/swd/';
    var pform = $("#platform").val();

    if (pform == undefined || pform == 'undefined' || pform == '') {
        $("#checkavail").text('Please select a platform').show();
        return;
    }

    if (pform == "windows" || pform == "mac" || pform == "linux") {
        if ($('#nhrep').is(':checked')){
            var manualUploader = new qq.FineUploader({
                element: document.getElementById('fine-uploader-manual-trigger'),
                template: 'qq-template-manual-trigger',
                multiple: false,
                callbacks: {
                    onCancel: function (id, file) {

                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage').css('cursor', 'pointer');

                        setTimeout(function () {
                            $(".qq-upload-button input").prop("disabled", false);
                            $(".qq-upload-button").removeClass("disabled");
                        }, 500);
                        if (uploadCount > 0) {
                            $("#filevalidationtext").hide();
                            this.reset();
                            //                        $('#fileupload').uploadify('cancel', '*');
                            $("#files").html('');
                            $("#filevalidationtext").html('');
                            $('#upload').unbind('click');
                            $("#fileupload").removeAttr('disabled');
                            $(".addedfilesList").html("");
                            $("#fileUp").fadeIn();
                            $(".clearFilesDiv,.addedfilesList").fadeOut();
                            $("#addPackage").prop('disabled', true);
                            uploadCount--;
                        } else {
                            uploadCount = 0;
                        }
                    },
                    onSubmit: function (id, file) {
                        fileName = file;
                        splitFile = fileName.split('.');
                        removeExt = splitFile[splitFile.length - 1];
                        removedFileExtension = fileName.replace('.' + removeExt, '');
                            
                        if(removeExt == 'php' || removeExt == 'html' || removeExt == 'js'){
                            $.notify("File Type restricted to upload");
                            return false;
                        }
                        
                        var newFile = file;
                        this.setName(id, newFile);

                        window.uploadCdnFtpUploadInProgress = true;
                        $('#addPackage').css('cursor', 'wait');
                        $("#addPackage").prop("disabled", true);
                        $(".addedfilesList").fadeIn();
                        $("#filevalidationtext").hide();
                        uploadCount == 0;
                        if ($('#nhrep').is(':checked')) {
                            uploadCount++;
                        }
                        if (uploadCount == 1) {
                            fileName = file;
                            splitFile = fileName.split('.');
                            removeExt = splitFile[splitFile.length - 1];
                            removedFileExtension = fileName.replace('.' + removeExt, '');
                            
                            $("#files").append("<li class='selectedFile'>" + file + "</li>");
                            $('input[type=text]').prev().parent().removeClass('is-empty');
                            $("#packName").val(removedFileExtension);
                            $("#packName").attr("title", removedFileExtension);
//                            $("#filename").val(file);
                            $('#filename').val(fileName);
                            uploadCount++;
                        }
                        if (uploadCount > 1) {
                            $("#checkavail").hide().html("");
                                $(".qq-upload-button input").prop("disabled", true);
//                                $(".qq-upload-button-selector").addClass("disabled");
                        }

                        $("#filename").val(newFile);
                    },
                    onProgress: function (id, file, totalBytesUploaded, totalBytesTotal) {
                        $("#filevalidationtext").hide();
                        if (totalBytesUploaded >= totalBytesTotal) {
                            $("#addPackage").prop("disabled", false);
                            $("#upStatus").val("1");
                            $(".clearFilesDiv").fadeIn();
                            if (uploadCount == 2) {
                                $("#progress1").fadeOut();
                            }
                            if (uploadCount == 3) {
                                $("#fileUp").fadeOut();
                            }
                            $(".addedfilesList").append("<label>" + file + "</label>");
                        }
                    },
                    onDelete: function (id) {
                        fileName = file;
                        splitFile = fileName.split('.');
                        removeExt = splitFile[splitFile.length - 1];
                            
                        if(removeExt == 'php' || removeExt == 'html' || removeExt == 'js'){
                            $.notify("File Type restricted to upload");
                            return false;
                        }
                        $("#filevalidationtext").hide();
                        this.reset();
//                        $('#fileupload').uploadify('cancel', '*');
                        $("#files").html('');
                        $("#filevalidationtext").html('');
                        $('#upload').unbind('click');
                        $("#fileupload").removeAttr('disabled');
                        $(".addedfilesList").html("");
                        $("#fileUp").fadeIn();
                        $(".clearFilesDiv,.addedfilesList").fadeOut();
                        $("#addPackage").prop('disabled', true);
                        setTimeout(function () {
                            $(".qq-upload-button input").prop("disabled", false);
                            $(".qq-upload-button").removeClass("disabled");
                        }, 500);
                        uploadCount = 0;
                        
                        
                    },
                    onError: function (event, id, name, errorReason, xhrOrXdr) {
                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage').css('cursor', 'pointer');
                        $("#filevalidationtext").show().html(name).focus();
                    },
                    onComplete: function (id,file) {
                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage').css('cursor', 'pointer');
                        if(uploads == '2'){
                            onUploadAddSubmit(file, AWSACCESS, AWSSECRET, AWSBUCKET, AWSREGION);
                    }
                    }
                },
                messages: {
                    typeError: 'Invalid extension detected in file, {file}.',
                    emptyError: '{file} is Empty, Please upload a valid file'
                },
                request: {
                    endpoint: url_part + "vendor/fineuploader/php-traditional-server/endpoint.php"
                },
                deleteFile: {
                    enabled: true,
                    endpoint: url_part + "vendor/fineuploader/php-traditional-server/endpoint.php"
                },
                chunking: {
                    enabled: true,
                    concurrent: {
                        enabled: true
                    },
                    success: {
                        endpoint: url_part + "vendor/fineuploader/php-traditional-server/endpoint.php?done"
                    }
                },
                resume: {
                    enabled: true
                },
                retry: {
                    enableAuto: false
                },
                thumbnails: {
                    placeholders: {
                        //waitingPath: '/source/placeholders/waiting-generic.png',
                        //notAvailablePath: '/source/placeholders/not_available-generic.png'
                    }
                },
                autoUpload: true,
                debug: false
            });

            qq(document.getElementById("trigger-upload")).attach("click", function () {
                manualUploader.uploadStoredFiles();
            });
            
            var manualUploader2 = new qq.FineUploader({
                element: document.getElementById('fine-uploader-manual-trigger2'),
                template: 'qq-template-manual-trigger2',
                multiple: false,
                callbacks: {
                    onCancel: function (id, file) {

                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage').css('cursor', 'pointer');

                        setTimeout(function () {
                            $(".qq-upload-button input").prop("disabled", false);
                            $(".qq-upload-button").removeClass("disabled");
                        }, 500);
                        if (uploadCount > 0) {
                            $("#filevalidationtext").hide();
                            this.reset();
                            //                        $('#fileupload').uploadify('cancel', '*');
                            $("#files").html('');
                            $("#filevalidationtext").html('');
                            $('#upload').unbind('click');
                            $("#fileupload").removeAttr('disabled');
                            $(".addedfilesList").html("");
                            $("#fileUp").fadeIn();
                            $(".clearFilesDiv,.addedfilesList").fadeOut();
                            $("#addPackage").prop('disabled', true);
                            uploadCount--;
                        } else {
                            uploadCount = 0;
                        }
                    },
                    onSubmit: function (id, file) {
//                        var newFile = changeUploadFileName(file);
                        var newFile = file;
                        this.setName(id, newFile);
                        fileName = file;
                        splitFile = fileName.split('.');
                        removeExt = splitFile[splitFile.length - 1];
                        removedFileExtension = fileName.replace('.' + removeExt, '');
                        if(removeExt == 'php' || removeExt == 'html' || removeExt == 'js'){
                            $.notify("File Type restricted to upload");
                            return false;
                        }
                        window.uploadCdnFtpUploadInProgress = true;
                        $('#addPackage').css('cursor', 'wait');
                        $("#addPackage").prop("disabled", true);
                        $(".addedfilesList").fadeIn();
                        $("#filevalidationtext").hide();
                        uploadCount == 0;
                        if ($('#nhrep').is(':checked')) {

                            uploadCount++;
                        }
                        if (uploadCount == 1) {

                            fileName = file;
                            splitFile = fileName.split('.');
                            removeExt = splitFile[splitFile.length - 1];
                            removedFileExtension = fileName.replace('.' + removeExt, '');
                            
                            $("#files2").append("<li class='selectedFile'>" + file + "</li>");
                            $('input[type=text]').prev().parent().removeClass('is-empty');
                            $("#packName").val(removedFileExtension);
                            $("#packName").attr("title", removedFileExtension);
//                            $("#filename").val(file);
                            $('#filename2').val(fileName);
                        }

                        if (uploadCount >= 1) {
                            $("#checkavail").hide().html("");
                            setTimeout(function () {
                                $(".qq-upload-button input").prop("disabled", true);
                                $(".qq-upload-button").addClass("disabled");
                            }, 500);
                        }

                        $("#filename2").val(newFile);
                    },
                    onProgress: function (id, file, totalBytesUploaded, totalBytesTotal) {
                        $("#filevalidationtext").hide();
                        if (totalBytesUploaded >= totalBytesTotal) {
                            $("#addPackage").prop("disabled", false);
                            $("#upStatus").val("1");
                            $(".clearFilesDiv").fadeIn();
                            if (uploadCount == 2) {
                                $("#progress1").fadeOut();
                            }
                            if (uploadCount == 3) {
                                $("#fileUp").fadeOut();
                            }
                            $(".addedfilesList").append("<label>" + file + "</label>");
                        }
                    },
                    onDelete: function (id) {
                        
                        fileName = file;
                        splitFile = fileName.split('.');
                        removeExt = splitFile[splitFile.length - 1];
                            
                        if(removeExt == 'php' || removeExt == 'html' || removeExt == 'js'){
                            $.notify("File Type restricted to upload");
                            return false;
                        }
                        
                        $("#filevalidationtext").hide();
                        this.reset();
//                        $('#fileupload').uploadify('cancel', '*');
                        $("#files").html('');
                        $("#filevalidationtext").html('');
                        $('#upload').unbind('click');
                        $("#fileupload").removeAttr('disabled');
                        $(".addedfilesList").html("");
                        $("#fileUp").fadeIn();
                        $(".clearFilesDiv,.addedfilesList").fadeOut();
                        $("#addPackage").prop('disabled', true);
                        setTimeout(function () {
                            $(".qq-upload-button2 input").prop("disabled", false);
                            $(".qq-upload-button2").removeClass("disabled");
                        }, 500);
                        uploadCount = 0;
                        
                    },
                    onError: function (event, id, name, errorReason, xhrOrXdr) {
                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage').css('cursor', 'pointer');
                        $("#filevalidationtext").show().html(name).focus();
                    },
                    onComplete: function (id,file) {
                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage').css('cursor', 'pointer');
                        if(uploads == '2'){
                            onUploadAddSubmit(file, AWSACCESS, AWSSECRET, AWSBUCKET, AWSREGION);
                        }
                    }
                },
                messages: {
                    typeError: 'Invalid extension detected in file, {file}.',
                    emptyError: '{file} is Empty, Please upload a valid file'
                },
                request: {
                    endpoint: url_part + "vendor/fineuploader/php-traditional-server/endpoint.php"
                },
                deleteFile: {
                    enabled: true,
                    endpoint: url_part + "vendor/fineuploader/php-traditional-server/endpoint.php"
                },
                chunking: {
                    enabled: true,
                    concurrent: {
                        enabled: true
                    },
                    success: {
                        endpoint: url_part + "vendor/fineuploader/php-traditional-server/endpoint.php?done"
                    }
                },
                resume: {
                    enabled: true
                },
                retry: {
                    enableAuto: false
                },
                thumbnails: {
                    placeholders: {
                        //waitingPath: '/source/placeholders/waiting-generic.png',
                        //notAvailablePath: '/source/placeholders/not_available-generic.png'
                    }
                },
                autoUpload: true,
                debug: false
            });

            qq(document.getElementById("trigger-upload2")).attach("click", function () {
                manualUploader2.uploadStoredFiles();
            });
            console.log(manualUploader2);
            console.log('manualUploader2');
        }
        
    }
});

function onUploadAddSubmit(file, access, secret, bucket, region) {
    var FILENAME = btoa(file);
    var AWSACCESS = btoa(access);
    var AWSSECRET = btoa(secret);
    var AWSBUCKET = btoa(bucket);
    var AWSREGION = btoa(region);
    var url = "../softdist/upload.php?file=" + FILENAME + '&access=' + AWSACCESS + '&secret=' + AWSSECRET + '&bucket=' + AWSBUCKET + '&region=' + AWSREGION;

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            csrfMagicToken
        },
        async: true,
        success: function (data) {
//            setTimeout(function () {
//                $.ajax({
//                    url: "../swd/upload.php?delFile=" + filename,
//                    type: 'POST',
//                    data: "",
//                    async: true,
//                    success: function (data) {
//
//                    },
//                    cache: false,
//                    contentType: false,
//                    processData: false
//                });
//            }, 5000);
        },
        cache: false,
        // contentType: false,
        // processData: false
    });
}


