var isFileUploadedInEditPage = false;
var isOldImageDeleted = false;
window.uploadCdnFtpUploadInProgress = false;

function openModifyBox(){
    
    var target = $('#rsc-edit-container');
    var dataClass = target.attr('data-class');

    $('#absoFeed').css({'display': 'block', 'width': '100%'});
    
    target.removeClass('sm-3');
    target.removeClass('md-6');
    target.removeClass('lg-9');
    target.removeClass('rightslide-container-hide');
    target.addClass(dataClass);

    selectConfirm('editPopup');

}

/* edit functions start */

function disableEditFormComponents(){
    $('#removeDiv').hide();
}

function enableEditFormComponents(){
    
    if($('#removeDiv').attr('data-style-display') != undefined){
        if($('#removeDiv').attr('data-style-display')!='hidden'){
    $('#removeDiv').show();
        }
    }
    
    var extraButtonConfigure = $('#extraButtonConfigure');
    if(extraButtonConfigure.length > 0){
        if(extraButtonConfigure.attr('data-disabled') != undefined && extraButtonConfigure.attr('data-disabled') == 'true'){
            extraButtonConfigure.attr('disabled', true);
}
    } 
}

function disableMyForm(formId){
    $("#"+formId+" :input").prop("disabled", true);
    
    switch(formId){
        case 'rsc-edit-container' : 
            disableEditFormComponents();
        break;
    }
}

function enableMyForm(formId){
    $("#"+formId+" :input").prop("disabled", false);
    
    switch(formId){
        case 'rsc-edit-container' : 
            enableEditFormComponents();
        break;
    }
}

$('.myform-enable-edit').on('click',function(){
    var parent = $(this).parents('.myform');
        $(this).hide();
        parent.find('.myform-edit-group').show();
    
    var formId = $(this).parents('.myform').attr('id');
        enableMyForm(formId);
        
});

$('.myform-toogle-edit').on('click',function(){
    
    var parent = $(this).parents('.myform');
    
    parent.find('.myform-edit-group').hide();
    parent.find('.myform-enable-edit').show();
    
    var formId = parent.attr('id');
        disableMyForm(formId);
        
});

function initEditComponent(){
    $('#rsc-edit-container').find('.ed-topreset,#filevalidationtext1').hide();
}

/* edit functions end */

function editPackagePrepare(submitButtonId){
   
    var selEdit = $('#sel1').val();
    var extraButton = $('#'+submitButtonId); 
    var uploadDiv = $('#fine-uploader-manual-trigger1');
    
    if (selEdit == undefined || selEdit == 'undefined' || selEdit == '') {
        errorNotify("Please select a package to modify");
        return;
    }
    
    var rightSlider = new RightSlider('#rsc-edit-container');
    rightSlider.showLoader();
    $('.myform-enable-edit').hide();
    extraButton.hide();
    uploadDiv.hide();
    clearAllField();
    initEditComponent();
    $('#removeDiv').hide().attr('data-style-display', 'hidden');
    
    $.ajax({
        url: "SWD_Function.php",
        type: "POST",
        dataType: "json",
        data: {function: "editPackageFn", sel: selEdit},
        async: true,
        success: function (data) {
            
            var cdn = data.data.cdn;
            var ftp = data.data.ftp;
            var ftpUrl = data.data.ftpUrl;
            var AWSURL = data.data.AWSURL;
            var config_check = data.data.same3264config;
            $('.configureSource1').show();
            var protocol = data.data.protocol;
            if(protocol == '1' || protocol ==='1'){
                $('#ftpupload1').prop('checked',true);
                $('#cdnupload1').prop('checked',false);
            }else{
                $('#ftpupload1').prop('checked',false);
                $('#cdnupload1').prop('checked',true);
            }
            
            console.log(config_check);
                if(config_check == 'no'|| config_check === 'no' || config_check == "no"){
                    $('#removeDiv_label1').show();
                    $('#removeDiv_label2').show();
                    $('#removeDiv_label1').html('32 Bit Configuration File');
                    $('#editsame32_64config').prop('checked',false);
                    $('#editsame32_64config').prop('readonly',true);
                    $('#editdiff32_64config').prop('checked',true);
                    $('#removeDiv').show();
                    $('#removeDiv2').show();
                }else{
                    $('#removeDiv_label1').html('32/64 Bit Configurations File');
                    $('#removeDiv_label1').show();
                    $('#removeDiv_label2').hide();
                    $('#editsame32_64config').prop('checked',true);
                    $('#editdiff32_64config').prop('checked',false);
                    $('#editdiff32_64config').prop('readonly',true);
                    $('#removeDiv').show();
                    $('#removeDiv2').hide();
                }
            if((ftp=='' && cdn=='') || (isNaN(ftp) && isNaN(cdn)) || (parseInt(ftp) == 0 && parseInt(cdn) == 0) || (ftp==null && cdn==null)){
                rightContainerSlideClose('rsc-edit-container');
                errorNotify('Please configure FTP or CDN details to edit package');
                return;
            }
            
            disableMyForm('rsc-edit-container');
            $('#rsc-edit-container').find('.configureSource1').hide();
            rightSlider.hideLoader();
            $('.myform-enable-edit').show();
            $("#sFtpUrl1").val(ftpUrl);
            $("#sCdnUrl1").val(AWSURL);
            $("#AWSACCESS1").val(data.data.AWSACCESS);
            $("#AWSSECRET1").val(data.data.AWSSECRET);
            $("#AWSBUCKET1").val(data.data.AWSBUCKET);
            $("#AWSREGION1").val(data.data.AWSREGION);
            $("#policy1").val(data.data.policy);
            $("#signature1").val(data.data.signature);

            if(data.data.sourceType != undefined && parseInt(data.data.sourceType) == 2){
                $('#removeDiv').attr('data-style-display', 'block');
            }
            
            if (ftp != "1" && cdn != "1") {

                $('#edit_software_distribution').modal('hide');
                $('#warning').modal('show');
                $("#normError").show();
                $("#normError").html("<span>Please Configure FTP or CDN Details to Edit Package</span>");
                $("#mainError").hide();

            } else if (ftp != 1 && cdn != 1) {

                $('#edit_software_distribution').modal('hide');
                $('#warning').modal('show');
                $("#normError").show();
                $("#normError").html("<span>Please Configure FTP or CDN Details to Edit Package</span>");
                $("#mainError").hide();

            } else {

                $('#editPopup').attr('data-bs-target', '#edit_software_distribution');

                if (ftp != "1" || ftp != 1) {

                    $("#ftpupload").attr("disabled", "disable");
                    $("#ftpspan").html(" <span>(Configure FTP to upload)</span>  ");
                    $("#ftp1val").val(" <span>(Configure FTP to upload)</span>  ");
                }

                if (cdn != "1" || cdn != 1) {

                    $("#cdnupload").attr("disabled", "disable");
                    $("#cdnspan").html(" <span>(Configure CDN to upload)</span>  ");
                    $("#cdn1val").val(" <span>(Configure CDN to upload)</span>  ");

                }

                $('input[type=text]').prev().parent().removeClass('is-empty');
                $(".CommAndPosKey288,.Comm_PosKey_PackExp288,.CommAndPosKey415").hide();

                if (data.data.platform == "windows") {

                    $('#platform1 option[value=windows]').prop("selected", true).show();
                    $('#platform1 option[value=android]').hide();
                    $('#platform1 option[value=linux]').hide();
                    $('#platform1 option[value=mac]').hide();
                    $('#platform1 option[value=ios]').hide();
                    $('#filenameDiv1,#destaddrDiv,#EditSiteDiv').hide();

                    if (data.data.distrubute == "1") {
                        $('#distCheck1').val('1');
                        $('#distCheck1').prop('checked', true);
                        $('.distributionPath1').css('display', 'block');
                        $('#dPath1').val(data.data.distributionPath);
                        $('.distributionTime1').css('display', 'block');
                        $('#dTime1').val(data.data.distributionTime);
                        $('.distributionValidPath1').css('display', 'block');
                        $('#dvPath1').val(data.data.distributionVpath);
                        $('.preDisCheckClass1').show();
                        if (data.data.trimdcheckPreInstall != "" || data.data.dcheckPreInstall != "") {

                            if (data.data.strdcheckPreInstall == '0') {
                                $('#preDisCheck1').prop('checked', true);
                                $('#preDisCheck1').val('1');
                                $('#pfile1').val('0');
                                $('#pfile1').prop('checked', true);
                                $('.pfile1,.distributionpreCheck1,#distributionPreCheckDiv1').show();
                                $('#pfilePath1').val(data.data.dValidationFilePath);
                                if (data.data.pExecPreCheckVal == 0) {
                                    $("#pExecPreCheckVal2").prop("checked", true);
                                } else if (data.data.pExecPreCheckVal == 1) {
                                    $("#pExecPreCheckVal3").prop("checked", true);
                                }
                            } else if (data.data.strdcheckPreInstall == '1') {
                                $('#preDisCheck1').prop('checked', true);
                                $('#preDisCheck1').val('1');
                                $('#pSoftware1').val('1');
                                $('#pSoftware1').prop('checked', true);
                                $('.pSoftware1,.distributionpreCheck1').show();
                                $('#pSoftName1').val(data.data.dsoftwareName);
                                $('#pSoftVer1').val(data.data.dsoftwareVersion);
                                $('#pKb1').val(data.data.dknowledgeBase);
                                $('#pServicePack1').val(data.data.dservicePack);
                                $("#distributionPreCheckDiv1").hide();
                            } else if (data.data.strdcheckPreInstall == '2') {
                                $('#preDisCheck1').prop('checked', true);
                                $('#preDisCheck1').val('1');
                                $('#pRegistry1').val('2');
                                $('#pRegistry1').prop('checked', true);
                                $('.pRegistry1,.distributionpreCheck1,#distributionPreCheckDiv1').show();
                                $('#rootKey1').val(data.data.dRootKey).change();
                                $('#subKey1').val(data.data.dSubKey);
                                $("#pRegName1").val(data.data.pRegName);
                                $("#pType1").val(data.data.pType).change();
                                $("#pValue1").val(data.data.pValue);
                                if (data.data.pExecPreCheckVal == 0) {
                                    $("#pExecPreCheckVal2").prop("checked", true);
                                } else if (data.data.pExecPreCheckVal == 1) {
                                    $("#pExecPreCheckVal3").prop("checked", true);
                                }

                            }
                        } else {
                            $('#preDisCheck1').prop('checked', false);
                            $('#preDisCheck1').val('0');
                            $('.distributionpreCheck1').hide();

                        }

                    } else if (data.data.distrubute == "0") {

                        $('#distCheck1').val('0');
                        $('#distCheck1').prop('checked', false);
                        $('.distributionPath1').css('display', 'none');
                        $('.distributionTime1').css('display', 'none');
                        $('.distributionValidPath1').css('display', 'none');
                        $('.preDisCheckClass1').show();
                        $('.distributionpreCheck1,.preinstcheckFields1').css('display', 'none');

                    }
                    if (data.data.dRootKey == '0' || data.data.dRootKey == '') {

                        $('#none').prop('selected', true);

                    } else if (data.data.dRootKey == '3') {

                        $('#root').prop('selected', true);

                    } else if (data.data.dRootKey == '4') {

                        $('#current').prop('selected', true);

                    } else if (data.data.dRootKey == '1') {

                        $('#local').prop('selected', true);

                    } else if (data.data.dRootKey == '5') {

                        $('#users').prop('selected', true);

                    } else if (data.data.dRootKey == '7') {

                        $('#perdata').prop('selected', true);

                    } else if (data.data.dRootKey == '8') {

                        $('#pertext').prop('selected', true);

                    } else if (data.data.dRootKey == '9') {

                        $('#pernlstext').prop('selected', true);

                    } else if (data.data.dRootKey == '2') {

                        $('#config').prop('selected', true);

                    } else if (data.data.dRootKey == '6') {

                        $('#dyndata').prop('selected', true);

                    }

                } else if (data.data.platform == "linux") {

                    $('#platform1 option[value=linux]').prop("selected", true).show();
                    $('#platform1 option[value=android]').hide();
                    $('#platform1 option[value=windows]').hide();
                    $('#platform1 option[value=mac]').hide();
                    $('#platform1 option[value=ios]').hide();
                    $('#filenameDiv1,#destaddrDiv,.distributionpreCheck1,.preDisCheckClass1,.preinstcheckFields1,.winOnly,#EditSiteDiv').hide();
                    if (data.data.sourceType == 2) {
                        if (data.data.distrubute == "1") {
                            $('#distCheck1').val('1');
                            $('#distCheck1').prop('checked', true);
                            $('.preDisCheckClass1').show();
                            if (data.data.trimdcheckPreInstall != "" || data.data.dcheckPreInstall != "") {

                                if (data.data.strdcheckPreInstall == '0') {
                                    $('#preDisCheck1').prop('checked', true);
                                    $('#preDisCheck1').val('1');
                                    $('#pfile1').val('0');
                                    $('#pfile1').prop('checked', true);
                                    $('.pfile1,.distributionpreCheck1').show();
                                    $('#pfilePath1').val(data.data.dValidationFilePath);
                                    $("#distributionPreCheckDiv1,#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();

                                } else if (data.data.strdcheckPreInstall == '1') {
                                    $('#preDisCheck1').prop('checked', true);
                                    $('#preDisCheck1').val('1');
                                    $('#pSoftware1').val('1');
                                    $('#pSoftware1').prop('checked', true);
                                    $('.pSoftware1,.distributionpreCheck1').show();
                                    $('#pSoftName1').val(data.data.dsoftwareName);
                                    $('#pSoftVer1').val(data.data.dsoftwareVersion);
                                    $("#distributionPreCheckDiv1,#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();
                                } else {
                                    $('#preDisCheck1').prop('checked', false);
                                    $('#preDisCheck1').val('0');
                                    $('.distributionpreCheck1').hide();
                                    $("#distributionPreCheckDiv1,#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();
                                }
                            } else {
                                $('#preDisCheck1').prop('checked', false);
                                $('#preDisCheck1').val('0');
                                $('.distributionpreCheck1').hide();

                            }

                        } else if (data.data.distrubute == "0") {

                            $('#distCheck1').val('0');
                            $('#distCheck1').prop('checked', false);
                            $('.distributionPath1').css('display', 'none');
                            $('.distributionTime1').css('display', 'none');
                            $('.distributionValidPath1').css('display', 'none');
                            $('.preDisCheckClass1').show();
                            $('.distributionpreCheck1,.preinstcheckFields1').css('display', 'none');

                        }
                    }
                    $("#pRegistryDiv1").hide();
                } else if (data.data.platform == "android") {
                    $('#platform1 option[value=android]').prop("selected", true).show();
                    $('#platform1 option[value=windows]').hide();
                    $('#platform1 option[value=linux]').hide();
                    $('#platform1 option[value=mac]').hide();
                    $('#platform1 option[value=ios]').hide();
                    if (data.data.sourceType == "2") {

                        $("#packExpiryDiv1").show();
                        $("#policyEnforceDiv1").hide();

                        try{
                            $("#packExpiry1 option[value=" + data.data.packExpiry + "]").prop('selected', true);
                            $("#policyEnforce1 option[value=" + data.data.policyEnforce + "]").prop("selected", true).show();
                            $("#andPreCheck1 option[value=" + data.data.andPreCheck + "]").prop("selected", true);
                        } catch (e) {
                        }

                        $("#policyEnforce1 option[value=0]").show();
                        $("#policyEnforce1 option[value=1]").show();
                        $("#policyEnforce1 option[value=2]").show();
                        
                        
                        $("#andPreCheck1 option[value=0]").show();
                        $("#andPreCheck1 option[value=1]").show();
                        $("#andPreCheck1 option[value=2]").show();
                        if (data.data.posKey == "288") {
                            $("#posKeywords1,#distTypeDiv1,#fileTypeDiv1,#andPreCheckDiv1,#downloadTypeDiv1,#maxtimeperpatch1,#packageAndVersionDiv1").show();
                            $("#posKey1 option[value=288]").prop("selected", true);
                            $("#posKey1 option[value=415]").hide();
                            $("#posKey1 option[value=0]").hide();
                            $("#maxTime1").val(data.data.maxTime);
                            if (data.data.distType == "1") {
                                $("#distType1 option[value=1]").prop("selected", true).show();
                                $("#distType1 option[value=3]").hide();
                                $("#distType1 option[value=2]").show();
                                $("#downloadPath1").val(data.data.downloadPath);
                                $("#downloadPathDiv1").show();
                                $("#andPreCheck1 option[value=0]").show();
                                $("#andPreCheck1 option[value=1]").hide();
                                $("#andPreCheck1 option[value=2]").hide();
                                $("#packageAndVersionDiv1,#pathSizeDiv1,#andPostCheckDiv1,#sourceDestinationDiv1").hide();

                                $("#downloadType1 option[value=" + data.data.downloadType + "]").prop("selected", true);
                                $("#andPostCheck1 option[value=1]").hide();
                                $("#andPostCheck1 option[value=0]").show();

                                $('#preCheckPathDiv1').show();
                                $("#preCheckPath1").val(data.data.andPreCheckPath);

                            } else if (data.data.distType == "2") {
                                $("#andPostCheckDiv1").show();
                                $("#distType1 option[value=2]").prop("selected", true).show();
                                $("#distType1 option[value=3]").hide();
                                $("#distType1 option[value=1]").show();
                                $("#downloadPathDiv1,#sourceDestinationDiv1").hide();
                                $("#andPreCheck1 option[value=0]").show();
                                $("#andPreCheck1 option[value=1]").show();
                                $("#andPreCheck1 option[value=2]").hide();
                                if (data.data.andPreCheck == "1") {
                                    $("#packageAndVersionDiv1").show();
                                    $("#pathSizeDiv1").hide();
                                    $("#preCheckPathDiv1").hide();
                                    $("#andPackName1").val(data.data.andPackName);
                                    $("#andVersionCode1").val(data.data.andVersionCode);
                                } else if (data.data.andPreCheck == "2") {
                                    $("#packageAndVersionDiv1").hide();
                                    $("#pathSizeDiv1").show();
                                    $("#preCheckPathDiv1").hide();
                                    $("#apkPath1").val(data.data.apkPath);
                                    $("#apkSize1").val(data.data.apkSize);
                                } else if (data.data.andPreCheck == "0") {
                                    $("#preCheckPathDiv1").show();
                                    $("#packageAndVersionDiv1").hide();
                                    $("#pathSizeDiv1").hide();
                                    $("#preCheckPath1").val(data.data.andPreCheckPath);
                                } else {
                                    $("#packageAndVersionDiv1,#pathSizeDiv1").hide();
                                }

                                if (data.data.andPostCheck) {
                                    $("#andPostCheck1 option[value=1]").prop("selected", true);
                                    $("#packAndVersDiv1").show();
                                    $("#andPPackName1").val(data.data.andPPackName);
                                    $("#andPVersionCode1").val(data.data.andPVersionCode);
                                } else {
                                    $("#andPostCheck1 option[value=1]").prop("selected", false);
                                    $("#packAndVersDiv1").hide();
                                }

                                $("#downloadType1 option[value=" + data.data.downloadType + "]").prop("selected", true);
                                $("#andPostCheck1 option[value=1]").show();
                                $("#andPostCheck1 option[value=0]").show();

                            } else if (data.data.distType == "3") {
                                $("#distType1 option[value=3]").prop("selected", true).show();
                                $("#distType1 option[value=1]").hide();
                                $("#distType1 option[value=2]").hide();
                                $("#fileTypeDiv1,#sourceConfig1,#filenameDiv,#packDescDiv,.version,#downloadPathDiv1,#andPreCheckDiv1,#packageAndVersionDiv1,#pathSizeDiv1,#andPostCheckDiv1,#packageAndVersionDiv1,#packageAndVersionDiv1").hide();
                                $("#policyEnforce1 option[value=1]").hide();
                                $("#policyEnforce1 option[value=2]").hide();
                                $("#sourceDestinationDiv1").show();
                                $("#sourcePath1").val(data.data.sourcePath);
                                $("#destinationPath1").val(data.data.destinationPath);

                                $("#downloadType1 option[value=" + data.data.downloadType + "]").prop("selected", true);
                                $("#andPostCheck1 option[value=1]").hide();
                                $("#andPostCheck1 option[value=0]").show();
                                $("#packDescDiv1,.version1,#policyEnforceDiv1").hide(); // move option hide

                            }
                            $("#packExpiryDiv1").show();
                            $("#packExpiry1").val(data.data.packExpiry).change();
                            $(".selectpicker").selectpicker("refresh");
                        } else if (data.data.posKey == "415") {
                            $("#posKeywords1,#installTypeDiv1").show();
                            $("#distTypeDiv1").hide();
                            $("#posKey1 option[value=415]").prop("selected", true).show();
                            $("#posKey1 option[value=288]").hide();
                            $("#posKey1 option[value=0]").hide();
                            $("#installType1 option[value=" + data.data.installType + "]").prop("selected", true);

                            if (data.data.installType == "0") {
                                $("#titleDiv1,#preDownloadMsgDiv1,#postDownloadMsgDiv1,#installMsgDiv1,#freqIntActMsgDiv1").hide();
                            } else if (data.data.installType == "3") {
                                $("#titleDiv1,#preDownloadMsgDiv1,#postDownloadMsgDiv1,#installMsgDiv1").show();
                                $("#freqIntActMsgDiv1").hide();
                                $("#title1").val(data.data.title);
                                $("#preDownloadMsg1").val(data.data.preDownloadMsg);
                                $("#preDownloadPosMsg1").val(data.data.preDownloadPosMsg);
                                $("#preDownloadNegMsg1").val(data.data.preDownloadNegMsg);
                                $("#postDownloadMsg1").val(data.data.postDownloadMsg);
                                $("#postDownloadPosMsg1").val(data.data.postDownloadPosMsg);
                                $("#postDownloadNegMsg1").val(data.data.postDownloadNegMsg);
                                $("#installMsg1").val(data.data.installMsg);

                                $("#installAction1 option[value=" + data.data.installAction + "]").prop("selected", true);
                                if (data.data.installAction == "1") {
                                    $(".installFinishMsgSpan1").show();
                                    $(".installPopupSpan1").hide();
                                    $('.installPopupMsg1').hide();
                                    $('.installFinishMsg1').show();
                                    $("#installFinishMsg1").val(data.data.installFinishMsg);
                                } else if (data.data.installAction == "2") {
                                    $(".installPopupSpan1").show();
                                    $(".installFinishMsgSpan1").hide();
                                    $('.installPopupMsg1').show();
                                    $('.installFinishMsg1').hide();
                                    $("#installPopupMsg1").val(data.data.installPopupMsg);
                                }

                            } else if (data.data.installType == "5") {
                                $("#titleDiv1,#preDownloadMsgDiv1,#postDownloadMsgDiv1,#installMsgDiv1,#freqIntActMsgDiv1").show();
                                $("#title1").val(data.data.title);
                                $("#preDownloadMsg1").val(data.data.preDownloadMsg);
                                $("#preDownloadPosMsg1").val(data.data.preDownloadPosMsg);
                                $("#preDownloadNegMsg1").val(data.data.preDownloadNegMsg);
                                $("#postDownloadMsg1").val(data.data.postDownloadMsg);
                                $("#postDownloadPosMsg1").val(data.data.postDownloadPosMsg);
                                $("#postDownloadNegMsg1").val(data.data.postDownloadNegMsg);
                                $("#installMsg1").val(data.data.installMsg);

                                $("#installAction1 option[value=" + data.data.installAction + "]").prop("selected", true);
                                if (data.data.installAction == "1") {
                                    $(".installFinishMsgSpan1").show();
                                    $(".installPopupSpan1").hide();
                                    $('.installPopupMsg1').hide();
                                    $('.installFinishMsg1').show();
                                    $("#installFinishMsg1").val(data.data.installFinishMsg);
                                } else if (data.data.installAction == "2") {
                                    $(".installPopupSpan1").show();
                                    $(".installFinishMsgSpan1").hide();
                                    $('.installPopupMsg1').show();
                                    $('.installFinishMsg1').hide();
                                    $("#installPopupMsg1").val(data.data.installPopupMsg);
                                }

                                $("#frequencySet1").val(data.data.frequencySet);
                                $("#intervalSet1").val(data.data.intervalSet);
                                $("#policyEnforceAction1 option[value=" + data.data.policyEnforceAction + "]").prop("selected", true);
                                if (data.data.policyEnforceAction == "1") {
                                    $(".policyEnforceActionClass1").show();
                                    $("#enfMessage1").val(data.data.enfMessage);
                                } else {
                                    $(".policyEnforceActionClass1").hide();
                                    $("#enfMessage1").val("");
                                }
                            }
                            $("#packExpiryDiv1").show();
                            $("#packExpiry1").val(data.data.packExpiry).change();
                            $(".selectpicker").selectpicker("refresh");
                        } else if (data.data.posKey == "0") {
                            $("#posKeywords1").show();
                            $("#distTypeDiv1").hide();
                            $("#posKey1 option[value=0]").prop("selected", true).show();
                            $("#posKey1 option[value=415]").hide();
                            $("#posKey1 option[value=288]").hide();
                            $("#packExpiryDiv1").hide();
                        }
                    }
                    $('.distClass1,#filenameDiv1,#destaddrDiv,.distributionpreCheck1,.preDisCheckClass1,.preinstcheckFields1,.winOnly,#EditSiteDiv,#eactionDate,#enotify,#euniAction').hide();
                } else if (data.data.platform == "mac") {
                    $('#platform1 option[value=mac]').prop("selected", true).show();
                    $('#platform1 option[value=windows]').hide();
                    $('#platform1 option[value=linux]').hide();
                    $('#platform1 option[value=android]').hide();
                    $('#platform1 option[value=ios]').hide();
                    $('#filenameDiv1,#destaddrDiv,.distributionpreCheck1,.preDisCheckClass1,.preinstcheckFields1,.winOnly,#EditSiteDiv').hide();
                    if (data.data.sourceType == 2) {
                        if (data.data.distrubute == "1") {
                            $('#distCheck1').val('1');
                            $('#distCheck1').prop('checked', true);
                            $('.preDisCheckClass1').show();
                            if (data.data.trimdcheckPreInstall != "" || data.data.dcheckPreInstall != "") {

                                if (data.data.strdcheckPreInstall == '0') {
                                    $('#preDisCheck1').prop('checked', true);
                                    $('#preDisCheck1').val('1');
                                    $('#pfile1').val('0');
                                    $('#pfile1').prop('checked', true);
                                    $('.pfile1,.distributionpreCheck1').show();
                                    $('#pfilePath1').val(data.data.dValidationFilePath);
                                    $("#distributionPreCheckDiv1,#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();

                                } else if (data.data.strdcheckPreInstall == '1') {
                                    $('#preDisCheck1').prop('checked', true);
                                    $('#preDisCheck1').val('1');
                                    $('#pSoftware1').val('1');
                                    $('#pSoftware1').prop('checked', true);
                                    $('.pSoftware1,.distributionpreCheck1').show();
                                    $('#pSoftName1').val(data.data.dsoftwareName);
                                    $('#pSoftVer1').val(data.data.dsoftwareVersion);
                                    $("#distributionPreCheckDiv1,#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();
                                } else {
                                    $('#preDisCheck1').prop('checked', false);
                                    $('#preDisCheck1').val('0');
                                    $('.distributionpreCheck1').hide();
                                    $("#distributionPreCheckDiv1,#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();
                                }
                            } else {
                                $('#preDisCheck1').prop('checked', false);
                                $('#preDisCheck1').val('0');
                                $('.distributionpreCheck1').hide();

                            }

                        } else if (data.data.distrubute == "0") {

                            $('#distCheck1').val('0');
                            $('#distCheck1').prop('checked', false);
                            $('.distributionPath1').css('display', 'none');
                            $('.distributionTime1').css('display', 'none');
                            $('.distributionValidPath1').css('display', 'none');
                            $('.preDisCheckClass1').show();
                            $('.distributionpreCheck1,.preinstcheckFields1').css('display', 'none');

                        }
                    }
                    
                    $("#pRegistryDiv1").hide();
                } else if (data.data.platform == "ios") {

                    $('#platform1 option[value=ios]').prop("selected", true).show();
                    $('#platform1 option[value=windows]').hide();
                    $('#platform1 option[value=linux]').hide();
                    $('#platform1 option[value=mac]').hide();
                    $('#platform1 option[value=android]').hide();
                    $('#filenameDiv1,#destaddrDiv,.distributionpreCheck1,.preDisCheckClass1,.preinstcheckFields1,.winOnly,#EditSiteDiv,#manifestDiv1').hide();
                    $('#manifestNameDiv1').show();
                }

                if (data.data.type == "file") {
                    $('#types1 option[value=file]').prop("selected", true).show();
                    $('#types1 option[value=folder]').hide();
                    $('#filefolderType1').val('Upload File');

                } else {
                    $('#types1 option[value=folder]').prop("selected", true).show();
                    $('#types1 option[value=file]').hide();
                    $('#filefolderType1').val('Upload Folder');
                }

                if (data.data.sourceType == "6") {

                    $('#apstor').css('display', 'block');
                    $('#shfold').css('display', 'none');
                    $('#gpstor').css('display', 'none');
                    $('#npstor').css('display', 'none');
                    $('#nhrepo').css('display', 'none');
                    $('#otrepo').css('display', 'none');
                    $('#sourceType1').val("Apple Store");
                    $("#iplay1").prop("checked", true);
                    $("#appId1").val(data.data.appId);
                    $('#filenameDiv1,#destaddrDiv,#eactionDate,#enotify,#euniAction,.nhrep1,#packDescDiv1,.distClass1,#removeDiv').hide();
                    $('#packNameDiv1,#appIdDiv1,.version').show();

                } else if (data.data.sourceType == "1") {

                    $('#shfold').css('display', 'block');
                    $('#apstor').css('display', 'none');
                    $('#gpstor').css('display', 'none');
                    $('#npstor').css('display', 'none');
                    $('#nhrepo').css('display', 'none');
                    $('#otrepo').css('display', 'none');
                    $('#sourceType1').val("Shared Folder");
                    $("#sfolder1").prop("checked", true);
                    $('.shared1,.accessCLass1').fadeIn();
                    $('.nhrep1,#filenameDiv1,#destaddrDiv,#eactionDate,#enotify,#euniAction,#appIdDiv1').hide();

                } else if (data.data.sourceType == "4") {

                    $('#shfold').css('display', 'none');
                    $('#apstor').css('display', 'none');
                    $('#gpstor').css('display', 'block');
                    $('#npstor').css('display', 'none');
                    $('#nhrepo').css('display', 'none');
                    $('#otrepo').css('display', 'none');
                    $('#sourceType1').val("Google Play Store");
                    $("#gplay1").prop("checked", true);
                    $('.Path1,#filenameDiv1,#destaddrDiv,#eactionDate,#enotify,#euniAction,#appIdDiv1').hide();
                    $('.version1').hide();
                    $('.accessCLass1').hide();
                    $('.showSecure1').hide();

                } else if (data.data.sourceType == "5") {

                    $('#shfold').css('display', 'none');
                    $('#apstor').css('display', 'none');
                    $('#gpstor').css('display', 'none');
                    $('#npstor').css('display', 'block');
                    $('#nhrepo').css('display', 'none');
                    $('#otrepo').css('display', 'none');
                    $('#sourceType1').val("Nanoheal Play Store");
                    $("#nplay1").prop("checked", true);
                    $('#filenameDiv1,#destaddrDiv,.distributionpreCheck1,#appIdDiv1').hide();

                } else if (data.data.sourceType == "2") {
                    $('#shfold').css('display', 'none');
                    $('#apstor').css('display', 'none');
                    $('#gpstor').css('display', 'none');
                    $('#npstor').css('display', 'none');
                    $('#nhrepo').css('display', 'block');
                    $('#otrepo').css('display', 'none');
                    $('#sourceType1').val("Nanoheal Repository");
                    $("#nhrep1").prop("checked", true);
                    $('#filenameDiv1,#destaddrDiv,#eactionDate,#enotify,#euniAction,#appIdDiv1').hide();

                } else if (data.data.sourceType == "3") {

                    $('#shfold').css('display', 'none');
                    $('#apstor').css('display', 'none');
                    $('#gpstor').css('display', 'none');
                    $('#npstor').css('display', 'none');
                    $('#nhrepo').css('display', 'none');
                    $('#otrepo').show();
                    $('#sourceType1').val("Vendor Repository");
                    $('#removeDiv').hide();
                    $("#otrep0").prop("checked", true);

                    var pl = $("#platform1").val();

                    if (pl == 'windows') {
                        $('.shared1').fadeOut();
                    }

                    if (pl == 'mac' || pl == 'linux') {
                        $('.FileName1').hide();
                    }

                    $('.nhrep1,#eactionDate,#enotify,#euniAction,.distClass1,#filenameDiv1,#destaddrDiv,.distributionpreCheck1,.preDisCheckClass1,.preinstcheckFields1,#appIdDiv1').hide();
                    $('.accessCLass1').show();

                }
                $("#existingfilename").html(data.data.fileName);
                $("#existingfilename2").html(data.data.fileName2);
                $('#packName1').val(data.data.packageName);
                $("#packName1").attr("title", data.data.packageName);
                $('#path1').val(data.data.path);
                $('#filename1').val(data.data.fileName);
                $('#packDesc1').val(data.data.packageDesc);

                if (data.data.platform == "android" && data.data.sourceType == "5") {

                    $('#iconDiv').css('display', 'none');
                    $('#iconName1').val(data.data.androidIcon);
                    $('#androidI1').val(data.data.androidIcon);
                    $('#eactionDate').css('display', 'block');
                    $('#actionDate1').val(data.data.androiddate);
                    $('#enotify').css('display', 'block');
                    $('#notify1').val(data.data.androidnotify);
                    $('#euniAction').css('display', 'block');
                    $('#uniAction1').val(data.data.androidUninstall);
                    $('#EditSiteDiv').show();
                    $('#siteArray1').val(data.data.androidSite);

                } else {

                    $('#iconDiv').css('display', 'none');

                }
                $('#version1').val(data.data.version);

                if (data.data.access == "Anony" && data.data.sourceType == "3") {

                    $('.showaccess1').css('display', 'block');
                    $('.showSecure1').fadeOut();
                    $('#anony1').prop('checked', true);
                    $('#username1').val(data.data.userName);
                    $('#password1').val(data.data.password);
                    $('#domain1').val(data.data.domain);

                } else if (data.data.access == "Secure" && data.data.sourceType == "3") {

                    $('.showaccess1').css('display', 'block');
                    $('.showSecure1').fadeIn();
                    $('#secure1').prop('checked', true);
                    $('#username1').val(data.data.userName);
                    $('#password1').val(data.data.password);
                    $('#domain1').val(data.data.domain);

                }
                if (data.data.platform == "android" || data.data.platform == "mac" || data.data.platform == "linux") {

                    $('.distClass').css('display', 'none');

                } else if (data.data.sourceType != "3") {

                    if (data.data.distrubute == "1") {

                        $('#distCheck1').prop("checked", true);
                        $('#distCheck1').val('1');

                    } else if (data.data.distrubute == "0") {

                        $('#distCheck1').removeAttr('checked');
                        $('#distCheck1').val('0');

                    }
                }
                if ((data.data.platform == "mac" || data.data.platform == "linux") && data.data.sourceType == "2") {
                    $('#editpathDiv').show();
                    $('#editpath').val(data.data.path);
                }

                if (data.data.global == 'yes') {

                    $('#global1').parents('form').find('#global-patch-yes').prop('checked', true);
                    $('#global1').parents('form').find('#global-patch-no').prop('checked', false);
                    $('#global1').val('yes');
                } else {
                    $('#global1').parents('form').find('#global-patch-yes').prop('checked', false);
                    $('#global1').parents('form').find('#global-patch-no').prop('checked', true);
                    $('#global1').removeAttr('value');
                }

//                $('#stat').html(data.status);
//                $('#text4').html(data.dcheckPreInstall);
//                $('#text4').html(data.strdcheckPreInstall);
//                $('#text4').html(data.trimdcheckPreInstall);

                if (data.data.fileandicon != '' && data.data.sourceType == "5") {

                    $('#fileonly1').val(data.data.fileandicon);
                    $('#icononly1').val(data.data.fileiconUrl);
                    $('#iconspace').css('display', 'block');
                    $('#iconremove1').css('display', 'block');

                } else {

                    $('#fileonly1').val(data.data.fileonly);

                }

                $("#manifesttypes1").val(data.data.distributionPath).change();
                $("#manifestname1").val(data.data.distributionPath).change();

                $(".selectpicker").selectpicker('refresh');

            }

            var dPlatform = data.data.platform;
            var dIsConfigured = data.data.dIsConfigured;
            var dDistribute = data.data.distrubute;
            var dId = data.data.id;


            if (dPlatform == 'windows') {
                if (dIsConfigured == '1') {
                    if (dDistribute == 1) {
                        //$isconfig = "<span style='color:green;cursor: pointer; cursor: hand;' onclick='showCofigDetail(" . $res[$key]['id'] . ",\"a\");'>Edit</span> | <span id='isconfig' style='color:red;cursor: pointer; cursor: hand;' onclick='configure(" . $res[$key]['id'] . "," . $res[$key]['distrubute'] . ");'>Configure</span>";

                    } else {
                        //$isconfig = "<span id='isconfig' style='color:green;' onclick='configure(" . $res[$key]['id'] . "," . $res[$key]['distrubute'] . ");'>Configure</span>";
                    }

                    extraButton.attr('onclick', 'configure(' + dId + ',' + dDistribute + ')').show();
                } else if (dIsConfigured == '2') {
                    if (dDistribute == 1) {
                        //$isconfig = "<span style='color:green;cursor: pointer; cursor: hand;' onclick='showCofigDetail(" . $res[$key]['id'] . ",\"a\");'>Edit</span> | <span id='isconfig' style='color:green;cursor: pointer; cursor: hand;' onclick='configure(" . $res[$key]['id'] . "," . $res[$key]['distrubute'] . ");'>Configure</span>";
                    } else {
                        //$isconfig = "<span id='isconfig' style='color:red;cursor: pointer; cursor: hand;' onclick='configure(" . $res[$key]['id'] . "," . $res[$key]['distrubute'] . ");'>Configure</span>";
                    }
                    extraButton.attr('onclick', 'configure(' + dId + ',' + dDistribute + ')').show();
                } else {
                    if (dDistribute == 1) {
                        //$isconfig = "<span style='color:green;cursor: pointer; cursor: hand;' onclick='showCofigDetail(" . $res[$key]['id'] . ",\"a\");'>Edit</span> | <span id='isconfig' style='color:red;cursor: pointer; cursor: hand;' onclick='configure(" . $res[$key]['id'] . "," . $res[$key]['distrubute'] . ");'>Configure</span>";
                    } else {
                        //$isconfig = "<span id='isconfig' style='color:red;cursor: pointer; cursor: hand;' onclick='configure(" . $res[$key]['id'] . "," . $res[$key]['distrubute'] . ");'>Configure</span>";
                    }
                    extraButton.attr('onclick', 'configure(' + dId + ',' + dDistribute + ')').show();
                }
            } else if (dPlatform == 'mac' || dPlatform == 'linux') {
                if (dIsConfigured == '2') {
                    if (dDistribute == 1) {
                        //$isconfig = "<span id='isconfig' style='color:green;cursor: pointer; cursor: hand;' onclick='MACconfigPatch(" . $res[$key]['id'] . ",2);'>Configure</span>";
                    } else {
                        // $isconfig = "<span id='isconfig' style='color:green;cursor: pointer; cursor: hand;' onclick='MACconfigPatch(" . $res[$key]['id'] . ",2);'>Configure</span>";
                    }

                    extraButton.attr('onclick', 'MACconfigPatch(' + dId + ',2)').show();
                } else {
                    if (dDistribute == 1) {
                        //$isconfig = "<span id='isconfig' style='color:red;cursor: pointer; cursor: hand;' onclick='MACconfigPatch(" . $res[$key]['id'] . ",2);'>Configure</span>";
                    } else {
                        //$isconfig = "<span id='isconfig' style='color:red;cursor: pointer; cursor: hand;' onclick='MACconfigPatch(" . $res[$key]['id'] . ",2);'>Configure</span>";
                    }

                    extraButton.attr('onclick', 'MACconfigPatch(' + dId + ',2)').show();
                }
            } else {
                //$isconfig = "<span style='color:green;cursor: pointer; cursor: hand;'>Configured</span>";
            }
            
            
            var preInstallData = data.data.dcheckPreInstall;
            var sType = data.data.sourceType;
            var isDistributed = data.data.distrubute;
            
            if(isNaN((isDistributed)) || parseInt(isDistributed) == 0){
                $('.preDisCheckClass1').hide();
            } else {
                $('.preDisCheckClass1 ').show();
        }
            
            if(!isNaN(parseInt(sType)) && parseInt(sType) == 2 && !isNaN(parseInt(isDistributed)) && parseInt(isDistributed) == 0){
                $('#extraButtonConfigure').attr('data-disabled','true');
//                var notify = $.notify("Please configure distribute", 
//                { 
//                    placement: {
//                            from: 'top',
//                            align: 'right'
//                    },
//                    z_index : 1000000
//                });
            } else {
                $('#extraButtonConfigure').removeAttr('data-disabled');
            }
            
            if(cdn==undefined || cdn==null || cdn=='' || isNaN(cdn)){ 
                $("#cdnupload1").prop("disabled", true).parents('.form-check.form-check-radio').addClass('disabled');
            } else if(!isNaN(cdn) && parseInt(cdn) == 1){
                $('#cdnspan1').html('');
                $("#cdnupload1").prop("disabled", false).parents('.form-check.form-check-radio').removeClass('disabled');
            }
            
            $('[name=is_distribution_saved]').val(isDistributed);
            
            if (data.data.platform == "android") {
                $('.predischeck-wrap').hide();  
            }
        }
    });
    openModifyBox();
}

$(function () {
    
    $('#rsc-edit-container input[type=radio],#rsc-edit-container input[type=checkbox]').on('click', function(){
        var tickIcon = $('#addPackage1').parent('.iconTick');
        if(tickIcon.hasClass('circleGrey')){
            tickIcon.removeClass('circleGrey');
        }
    });
    
    $('#preDisCheck1').click(function(){
        if(!$(this).is(':checked')){
            var eachPreDistRadios = $('.preinstcheck1');
            $.each(eachPreDistRadios, function(){
                $(this).prop('checked', false);
            });
        } 
    });
    //Edit Popup Start here
    
    $("#editPopup").on('click', function () { // swd fix updates
        var selEdit = $('#sel1').val();
        var extraButton = $('#extraButton'); //
        
        if(selEdit == undefined || selEdit == 'undefined' || selEdit == ''){
           errorNotify("Please select a package to modify"); 
           return;
        }
        
        extraButton.hide();
        clearAllField();
        
        $.ajax({
            url: "SWD_Function.php",
            type: "POST",
            dataType: "json",
            data: {function: "editPackageFn", sel: selEdit,csrfMagicToken: csrfMagicToken},
            async: true,
            success: function (data) {
                disableFields();
                var cdn = data.data.cdn;
                var ftp = data.data.ftp;
                var ftpUrl = data.data.ftpUrl;
                var AWSURL = data.data.AWSURL;
                
                
                $("#sFtpUrl1").val(ftpUrl);
                $("#sCdnUrl1").val(AWSURL);
                $("#AWSACCESS1").val(data.data.AWSACCESS + "" + data.data.AWSBUCKET + "/");
                $("#AWSSECRET1").val(data.data.AWSSECRET);
                $("#AWSBUCKET1").val(data.data.AWSBUCKET);
                $("#AWSREGION1").val(data.data.AWSREGION);
                $("#policy1").val(data.data.policy);
                $("#signature1").val(data.data.signature);
                

                $('#editPopup').attr('data-bs-target', '#edit_software_distribution');

                if (ftp != "1" || ftp != 1) {

                    $("#ftpupload").attr("disabled", "disable");
                    $("#ftpspan").html(" <span>(Configure FTP to upload)</span>  ");
                    $("#ftp1val").val(" <span>(Configure FTP to upload)</span>  ");
                }

                if (cdn != "1" || cdn != 1) {

                    $("#cdnupload").attr("disabled", "disable");
                    $("#cdnspan").html(" <span>(Configure CDN to upload)</span>  ");
                    $("#cdn1val").val(" <span>(Configure CDN to upload)</span>  ");

                }

                $('input[type=text]').prev().parent().removeClass('is-empty');
                $(".CommAndPosKey288,.Comm_PosKey_PackExp288,.CommAndPosKey415").hide();

                if (data.data.platform == "windows") {

                    $('#platform1 option[value=windows]').prop("selected", true).show();
                    $('#platform1 option[value=android]').hide();
                    $('#platform1 option[value=linux]').hide();
                    $('#platform1 option[value=mac]').hide();
                    $('#platform1 option[value=ios]').hide();
                    $('#filenameDiv1,#destaddrDiv,#EditSiteDiv').hide();

                    if (data.data.sourceType != "3") {

                    } else {

                    }

                    if (data.data.distrubute == "1") {
                        $('#distCheck1').val('1');
                        $('#distCheck1').prop('checked', true);
                        $('.distributionPath1').css('display', 'block');
                        $('#dPath1').val(data.data.distributionPath);
                        $('.distributionTime1').css('display', 'block');
                        $('#dTime1').val(data.data.distributionTime);
                        $('.distributionValidPath1').css('display', 'block');
                        $('#dvPath1').val(data.data.distributionVpath);
                        $('.preDisCheckClass1').show();
                        if (data.data.trimdcheckPreInstall != "" || data.data.dcheckPreInstall != "") {

                            if (data.data.strdcheckPreInstall == '0') {
                                $('#preDisCheck1').prop('checked', true);
                                $('#preDisCheck1').val('1');
                                $('#pfile1').val('0');
                                $('#pfile1').prop('checked', true);
                                $('.pfile1,.distributionpreCheck1,#distributionPreCheckDiv1').show();
                                $('#pfilePath1').val(data.data.dValidationFilePath);
                                if (data.data.pExecPreCheckVal == 0) {
                                    $("#pExecPreCheckVal2").prop("checked", true);
                                } else if (data.data.pExecPreCheckVal == 1) {
                                    $("#pExecPreCheckVal3").prop("checked", true);
                                }
                            } else if (data.data.strdcheckPreInstall == '1') {
                                $('#preDisCheck1').prop('checked', true);
                                $('#preDisCheck1').val('1');
                                $('#pSoftware1').val('1');
                                $('#pSoftware1').prop('checked', true);
                                $('.pSoftware1,.distributionpreCheck1').show();
                                $('#pSoftName1').val(data.data.dsoftwareName);
                                $('#pSoftVer1').val(data.data.dsoftwareVersion);
                                $('#pKb1').val(data.data.dknowledgeBase);
                                $('#pServicePack1').val(data.data.dservicePack);
                                $("#distributionPreCheckDiv1").hide();
                            } else if (data.data.strdcheckPreInstall == '2') {
                                $('#preDisCheck1').prop('checked', true);
                                $('#preDisCheck1').val('1');
                                $('#pRegistry1').val('2');
                                $('#pRegistry1').prop('checked', true);
                                $('.pRegistry1,.distributionpreCheck1,#distributionPreCheckDiv1').show();
                                $('#rootKey1').val(data.data.dRootKey).change();
                                $('#subKey1').val(data.data.dSubKey);
                                $("#pRegName1").val(data.data.pRegName);
                                $("#pType1").val(data.data.pType).change();
                                $("#pValue1").val(data.data.pValue);
                                if (data.data.pExecPreCheckVal == 0) {
                                    $("#pExecPreCheckVal2").prop("checked", true);
                                } else if (data.data.pExecPreCheckVal == 1) {
                                    $("#pExecPreCheckVal3").prop("checked", true);
                                }

                            }
//                                else {
//                                    $('.distributionpreCheck1').show();
//                                }
                        } else {
                            $('#preDisCheck1').prop('checked', false);
                            $('#preDisCheck1').val('0');
                            $('.distributionpreCheck1').hide();

                        }

                    } else if (data.data.distrubute == "0") {

                        $('#distCheck1').val('0');
                        $('#distCheck1').prop('checked', false);
                        $('.distributionPath1').css('display', 'none');
                        $('.distributionTime1').css('display', 'none');
                        $('.distributionValidPath1').css('display', 'none');
                        $('.preDisCheckClass1').show();
                        $('.distributionpreCheck1,.preinstcheckFields1').css('display', 'none');

                    }

                    if (data.data.dRootKey == '0' || data.data.dRootKey == '') {

                        $('#none').prop('selected', true);

                    } else if (data.data.dRootKey == '3') {

                        $('#root').prop('selected', true);

                    } else if (data.data.dRootKey == '4') {

                        $('#current').prop('selected', true);

                    } else if (data.data.dRootKey == '1') {

                        $('#local').prop('selected', true);

                    } else if (data.data.dRootKey == '5') {

                        $('#users').prop('selected', true);

                    } else if (data.data.dRootKey == '7') {

                        $('#perdata').prop('selected', true);

                    } else if (data.data.dRootKey == '8') {

                        $('#pertext').prop('selected', true);

                    } else if (data.data.dRootKey == '9') {

                        $('#pernlstext').prop('selected', true);

                    } else if (data.data.dRootKey == '2') {

                        $('#config').prop('selected', true);

                    } else if (data.data.dRootKey == '6') {

                        $('#dyndata').prop('selected', true);

                    }

//                        if (data.data.strdcheckPreInstall == '') {
//                            $('.distributionpreCheck1').css('display', 'none');
//                        } else {
//                            $('.distributionpreCheck1').css('display', 'block');
//                        }

                } else if (data.data.platform == "linux") {

                    $('#platform1 option[value=linux]').prop("selected", true).show();
                    $('#platform1 option[value=android]').hide();
                    $('#platform1 option[value=windows]').hide();
                    $('#platform1 option[value=mac]').hide();
                    $('#platform1 option[value=ios]').hide();
//                        $('#filenameDiv1,#destaddrDiv,.winOnly,#EditSiteDiv').hide();
                    $('#filenameDiv1,#destaddrDiv,.distributionpreCheck1,.preDisCheckClass1,.preinstcheckFields1,.winOnly,#EditSiteDiv').hide();
                    if (data.data.sourceType == 2) {
//                            $(".preDisCheckClass1").show();
                        if (data.data.distrubute == "1") {
                            $('#distCheck1').val('1');
                            $('#distCheck1').prop('checked', true);
                            $('.preDisCheckClass1').show();
                            if (data.data.trimdcheckPreInstall != "" || data.data.dcheckPreInstall != "") {

                                if (data.data.strdcheckPreInstall == '0') {
                                    $('#preDisCheck1').prop('checked', true);
                                    $('#preDisCheck1').val('1');
                                    $('#pfile1').val('0');
                                    $('#pfile1').prop('checked', true);
                                    $('.pfile1,.distributionpreCheck1').show();
                                    $('#pfilePath1').val(data.data.dValidationFilePath);
                                    $("#distributionPreCheckDiv1,#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();

                                } else if (data.data.strdcheckPreInstall == '1') {
                                    $('#preDisCheck1').prop('checked', true);
                                    $('#preDisCheck1').val('1');
                                    $('#pSoftware1').val('1');
                                    $('#pSoftware1').prop('checked', true);
                                    $('.pSoftware1,.distributionpreCheck1').show();
                                    $('#pSoftName1').val(data.data.dsoftwareName);
                                    $('#pSoftVer1').val(data.data.dsoftwareVersion);
                                    $("#distributionPreCheckDiv1,#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();
                                } else {
                                    $('#preDisCheck1').prop('checked', false);
                                    $('#preDisCheck1').val('0');
                                    $('.distributionpreCheck1').hide();
                                    $("#distributionPreCheckDiv1,#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();
                                }
                            } else {
                                $('#preDisCheck1').prop('checked', false);
                                $('#preDisCheck1').val('0');
                                $('.distributionpreCheck1').hide();

                            } 

                        } else if (data.data.distrubute == "0") {

                            $('#distCheck1').val('0');
                            $('#distCheck1').prop('checked', false);
                            $('.distributionPath1').css('display', 'none');
                            $('.distributionTime1').css('display', 'none');
                            $('.distributionValidPath1').css('display', 'none');
                            $('.preDisCheckClass1').show();
                            $('.distributionpreCheck1,.preinstcheckFields1').css('display', 'none');

                        }
                    }
                    $("#pRegistryDiv1").hide();
                } else if (data.data.platform == "android") {
                    $('#platform1 option[value=android]').prop("selected", true).show();
                    $('#platform1 option[value=windows]').hide();
                    $('#platform1 option[value=linux]').hide();
                    $('#platform1 option[value=mac]').hide();
                    $('#platform1 option[value=ios]').hide();
                    if (data.data.sourceType == "2") {

                        $("#packExpiryDiv1").show();
                        $("#policyEnforceDiv1").hide();
                        $("#packExpiry1 option[value=" + data.data.packExpiry + "]").prop('selected', true);
                        $("#policyEnforce1 option[value=" + data.data.policyEnforce + "]").prop("selected", true).show();
                        $("#policyEnforce1 option[value=0]").show();
                        $("#policyEnforce1 option[value=1]").show();
                        $("#policyEnforce1 option[value=2]").show();
                        $("#andPreCheck1 option[value=" + (data.data?.andPreCheck || '0') + "]").prop("selected", true);
                        $("#andPreCheck1 option[value=0]").show();
                        $("#andPreCheck1 option[value=1]").show();
                        $("#andPreCheck1 option[value=2]").show();
                        if (data.data.posKey == "288") {
                            $("#posKeywords1,#distTypeDiv1,#fileTypeDiv1,#andPreCheckDiv1,#downloadTypeDiv1,#maxtimeperpatch1,#packageAndVersionDiv1").show();
                            $("#posKey1 option[value=288]").prop("selected", true);
                            $("#posKey1 option[value=415]").hide();
                            $("#posKey1 option[value=0]").hide();
                            $("#maxTime1").val(data.data.maxTime);
                            if (data.data.distType == "1") {
                                $("#distType1 option[value=1]").prop("selected", true).show();
                                $("#distType1 option[value=3]").hide();
                                $("#distType1 option[value=2]").show();
                                $("#downloadPath1").val(data.data.downloadPath);
                                $("#downloadPathDiv1").show();
                                $("#andPreCheck1 option[value=0]").show();
                                $("#andPreCheck1 option[value=1]").hide();
                                $("#andPreCheck1 option[value=2]").hide();
                                $("#packageAndVersionDiv1,#pathSizeDiv1,#andPostCheckDiv1,#sourceDestinationDiv1").hide();

                                $("#downloadType1 option[value=" + data.data.downloadType + "]").prop("selected", true);
                                $("#andPostCheck1 option[value=1]").hide();
                                $("#andPostCheck1 option[value=0]").show();

                                $('#preCheckPathDiv1').show();
                                $("#preCheckPath1").val(data.data.andPreCheckPath);

                            } else if (data.data.distType == "2") {
                                $("#andPostCheckDiv1").show();
                                $("#distType1 option[value=2]").prop("selected", true).show();
                                $("#distType1 option[value=3]").hide();
                                $("#distType1 option[value=1]").show();
                                $("#downloadPathDiv1,#sourceDestinationDiv1").hide();
                                $("#andPreCheck1 option[value=0]").show();
                                $("#andPreCheck1 option[value=1]").show();
                                $("#andPreCheck1 option[value=2]").hide();
                                if (data.data.andPreCheck == "1") {
                                    $("#packageAndVersionDiv1").show();
                                    $("#pathSizeDiv1").hide();
                                    $("#preCheckPathDiv1").hide();
                                    $("#andPackName1").val(data.data.andPackName);
                                    $("#andVersionCode1").val(data.data.andVersionCode);
                                } else if (data.data.andPreCheck == "2") {
                                    $("#packageAndVersionDiv1").hide();
                                    $("#pathSizeDiv1").show();
                                    $("#preCheckPathDiv1").hide();
                                    $("#apkPath1").val(data.data.apkPath);
                                    $("#apkSize1").val(data.data.apkSize);
                                } else if (data.data.andPreCheck == "0") {
                                    $("#preCheckPathDiv1").show();
                                    $("#packageAndVersionDiv1").hide();
                                    $("#pathSizeDiv1").hide();
                                    $("#preCheckPath1").val(data.data.andPreCheckPath);
                                } else {
                                    $("#packageAndVersionDiv1,#pathSizeDiv1").hide();
                                }

                                if (data.data.andPostCheck) {
                                    $("#andPostCheck1 option[value=1]").prop("selected", true);
                                    $("#packAndVersDiv1").show();
                                    $("#andPPackName1").val(data.data.andPPackName);
                                    $("#andPVersionCode1").val(data.data.andPVersionCode);
                                } else {
                                    $("#andPostCheck1 option[value=1]").prop("selected", false);
                                    $("#packAndVersDiv1").hide();
                                }

                                $("#downloadType1 option[value=" + data.data.downloadType + "]").prop("selected", true);
                                $("#andPostCheck1 option[value=1]").show();
                                $("#andPostCheck1 option[value=0]").show();

                            } else if (data.data.distType == "3") {
                                $("#distType1 option[value=3]").prop("selected", true).show();
                                $("#distType1 option[value=1]").hide();
                                $("#distType1 option[value=2]").hide();
                                $("#fileTypeDiv1,#sourceConfig1,#filenameDiv,#packDescDiv,.version,#downloadPathDiv1,#andPreCheckDiv1,#packageAndVersionDiv1,#pathSizeDiv1,#andPostCheckDiv1,#packageAndVersionDiv1,#packageAndVersionDiv1").hide();
                                $("#policyEnforce1 option[value=1]").hide();
                                $("#policyEnforce1 option[value=2]").hide();
                                $("#sourceDestinationDiv1").show();
                                $("#sourcePath1").val(data.data.sourcePath);
                                $("#destinationPath1").val(data.data.destinationPath);

                                $("#downloadType1 option[value=" + data.data.downloadType + "]").prop("selected", true);
                                $("#andPostCheck1 option[value=1]").hide();
                                $("#andPostCheck1 option[value=0]").show();
                                $("#packDescDiv1,.version1,#policyEnforceDiv1").hide(); // move option hide

                            }
                            $("#packExpiryDiv1").show();
                            $("#packExpiry1").val(data.data.packExpiry).change();
                            $(".selectpicker").selectpicker("refresh");
                        } else if (data.data.posKey == "415") {
                            $("#posKeywords1,#installTypeDiv1").show();
                            $("#distTypeDiv1").hide();
                            $("#posKey1 option[value=415]").prop("selected", true).show();
                            $("#posKey1 option[value=288]").hide();
                            $("#posKey1 option[value=0]").hide();
                            $("#installType1 option[value=" + data.data.installType + "]").prop("selected", true);

                            if (data.data.installType == "0") {
                                $("#titleDiv1,#preDownloadMsgDiv1,#postDownloadMsgDiv1,#installMsgDiv1,#freqIntActMsgDiv1").hide();
                            } else if (data.data.installType == "3") {
                                $("#titleDiv1,#preDownloadMsgDiv1,#postDownloadMsgDiv1,#installMsgDiv1").show();
                                $("#freqIntActMsgDiv1").hide();
                                $("#title1").val(data.data.title);
                                $("#preDownloadMsg1").val(data.data.preDownloadMsg);
                                $("#preDownloadPosMsg1").val(data.data.preDownloadPosMsg);
                                $("#preDownloadNegMsg1").val(data.data.preDownloadNegMsg);
                                $("#postDownloadMsg1").val(data.data.postDownloadMsg);
                                $("#postDownloadPosMsg1").val(data.data.postDownloadPosMsg);
                                $("#postDownloadNegMsg1").val(data.data.postDownloadNegMsg);
                                $("#installMsg1").val(data.data.installMsg);

                                $("#installAction1 option[value=" + data.data.installAction + "]").prop("selected", true);
                                if (data.data.installAction == "1") {
                                    $(".installFinishMsgSpan1").show();
                                    $(".installPopupSpan1").hide();
                                    $('.installPopupMsg1').hide();
                                    $('.installFinishMsg1').show();
                                    $("#installFinishMsg1").val(data.data.installFinishMsg);
                                } else if (data.data.installAction == "2") {
                                    $(".installPopupSpan1").show();
                                    $(".installFinishMsgSpan1").hide();
                                    $('.installPopupMsg1').show();
                                    $('.installFinishMsg1').hide();
                                    $("#installPopupMsg1").val(data.data.installPopupMsg);
                                }

                            } else if (data.data.installType == "5") {
                                $("#titleDiv1,#preDownloadMsgDiv1,#postDownloadMsgDiv1,#installMsgDiv1,#freqIntActMsgDiv1").show();
                                $("#title1").val(data.data.title);
                                $("#preDownloadMsg1").val(data.data.preDownloadMsg);
                                $("#preDownloadPosMsg1").val(data.data.preDownloadPosMsg);
                                $("#preDownloadNegMsg1").val(data.data.preDownloadNegMsg);
                                $("#postDownloadMsg1").val(data.data.postDownloadMsg);
                                $("#postDownloadPosMsg1").val(data.data.postDownloadPosMsg);
                                $("#postDownloadNegMsg1").val(data.data.postDownloadNegMsg);
                                $("#installMsg1").val(data.data.installMsg);

                                $("#installAction1 option[value=" + data.data.installAction + "]").prop("selected", true);
                                if (data.data.installAction == "1") {
                                    $(".installFinishMsgSpan1").show();
                                    $(".installPopupSpan1").hide();
                                    $('.installPopupMsg1').hide();
                                    $('.installFinishMsg1').show();
                                    $("#installFinishMsg1").val(data.data.installFinishMsg);
                                } else if (data.data.installAction == "2") {
                                    $(".installPopupSpan1").show();
                                    $(".installFinishMsgSpan1").hide();
                                        $('.installPopupMsg1').show();
                                        $('.installFinishMsg1').hide();
                                    $("#installPopupMsg1").val(data.data.installPopupMsg);
                                }

                                $("#frequencySet1").val(data.data.frequencySet);
                                $("#intervalSet1").val(data.data.intervalSet);
                                $("#policyEnforceAction1 option[value=" + data.data.policyEnforceAction + "]").prop("selected", true);
                                if (data.data.policyEnforceAction == "1") {
                                    $(".policyEnforceActionClass1").show();
                                    $("#enfMessage1").val(data.data.enfMessage);
                                } else {
                                    $(".policyEnforceActionClass1").hide();
                                    $("#enfMessage1").val("");
                                }


                            }
                            $("#packExpiryDiv1").show();
                            $("#packExpiry1").val(data.data.packExpiry).change();
                            $(".selectpicker").selectpicker("refresh");
                        } else if (data.data.posKey == "0") {
                            $("#posKeywords1").show();
                            $("#distTypeDiv1").hide();
                            $("#posKey1 option[value=0]").prop("selected", true).show();
                            $("#posKey1 option[value=415]").hide();
                            $("#posKey1 option[value=288]").hide();
                            $("#packExpiryDiv1").hide();
                        }
                    }
                    $('.distClass1,#filenameDiv1,#destaddrDiv,.distributionpreCheck1,.preDisCheckClass1,.preinstcheckFields1,.winOnly,#EditSiteDiv,#eactionDate,#enotify,#euniAction').hide();
                } else if (data.data.platform == "mac") {
                    $('#platform1 option[value=mac]').prop("selected", true).show();
                    $('#platform1 option[value=windows]').hide();
                    $('#platform1 option[value=linux]').hide();
                    $('#platform1 option[value=android]').hide();
                    $('#platform1 option[value=ios]').hide();
                    $('#filenameDiv1,#destaddrDiv,.distributionpreCheck1,.preDisCheckClass1,.preinstcheckFields1,.winOnly,#EditSiteDiv').hide();
                    if (data.data.sourceType == 2) {
//                            $(".preDisCheckClass1").show();
                        if (data.data.distrubute == "1") {
                        $('#distCheck1').val('1');
                        $('#distCheck1').prop('checked', true);
                        $('.preDisCheckClass1').show();
                        if (data.data.trimdcheckPreInstall != "" || data.data.dcheckPreInstall != "") {

                            if (data.data.strdcheckPreInstall == '0') {
                                $('#preDisCheck1').prop('checked', true);
                                $('#preDisCheck1').val('1');
                                $('#pfile1').val('0');
                                $('#pfile1').prop('checked', true);
                                $('.pfile1,.distributionpreCheck1').show();
                                $('#pfilePath1').val(data.data.dValidationFilePath);
                                $("#distributionPreCheckDiv1,#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();
                                
                            } else if (data.data.strdcheckPreInstall == '1') {
                                $('#preDisCheck1').prop('checked', true);
                                $('#preDisCheck1').val('1');
                                $('#pSoftware1').val('1');
                                $('#pSoftware1').prop('checked', true);
                                $('.pSoftware1,.distributionpreCheck1').show();
                                $('#pSoftName1').val(data.data.dsoftwareName);
                                $('#pSoftVer1').val(data.data.dsoftwareVersion);
                                $("#distributionPreCheckDiv1,#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();
                            } else {
                                $('#preDisCheck1').prop('checked', false);
                                $('#preDisCheck1').val('0');
                                $('.distributionpreCheck1').hide();
                                $("#distributionPreCheckDiv1,#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();
                    }
                        } else {
                            $('#preDisCheck1').prop('checked', false);
                            $('#preDisCheck1').val('0');
                            $('.distributionpreCheck1').hide();

                        } 

                    } else if (data.data.distrubute == "0") {

                        $('#distCheck1').val('0');
                        $('#distCheck1').prop('checked', false);
                        $('.distributionPath1').css('display', 'none');
                        $('.distributionTime1').css('display', 'none');
                        $('.distributionValidPath1').css('display', 'none');
                        $('.preDisCheckClass1').show();
                        $('.distributionpreCheck1,.preinstcheckFields1').css('display', 'none');

                    }
                    }
//                        $("#preDisCheck1").val("0");
                    /*if (data.data.trimdcheckPreInstall != "" || data.data.dcheckPreInstall != "") {
                        $(".distributionpreCheck1").show();
                        $("#preDisCheck1").val("1");

                        if (data.data.strdcheckPreInstall == '0') {
                            $('#preDisCheck1').prop('checked', true);
                            $('#preDisCheck1').val('1');
                            $('#pfile1').val('0');
                            $('#pfile1').prop('checked', true);
                            $('.pfile1,.distributionpreCheck1,#distributionPreCheckDiv1').show();
                            $('#pfilePath1').val(data.data.dValidationFilePath);
                            $("#distributionPreCheckDiv1").hide();
                        } else if (data.data.strdcheckPreInstall == '1') {
                            $('#preDisCheck1').prop('checked', true);
                            $('#preDisCheck1').val('1');
                            $('#pSoftware1').val('1');
                            $('#pSoftware1').prop('checked', true);
                            $('.pSoftware1,.distributionpreCheck1').show();
                            $('#pSoftName1').val(data.data.dsoftwareName);
                            $('#pSoftVer1').val(data.data.dsoftwareVersion);

                            $("#distributionPreCheckDiv1,#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();
                        }
                    } else {
                        $('#preDisCheck1').prop('checked', false);
                        $('#preDisCheck1').val('0');
                        $('.distributionpreCheck1').hide();
                        $("#distributionPreCheckDiv1").hide();

                    }*/
                    $("#pRegistryDiv1").hide();
                } else if (data.data.platform == "ios") {

                    $('#platform1 option[value=ios]').prop("selected", true).show();
                    $('#platform1 option[value=windows]').hide();
                    $('#platform1 option[value=linux]').hide();
                    $('#platform1 option[value=mac]').hide();
                    $('#platform1 option[value=android]').hide();
                    $('#filenameDiv1,#destaddrDiv,.distributionpreCheck1,.preDisCheckClass1,.preinstcheckFields1,.winOnly,#EditSiteDiv,#manifestDiv1').hide();
                    $('#manifestNameDiv1').show();
                }

                if (data.data.type == "file") {
                    $('#types1 option[value=file]').prop("selected", true).show();
                    $('#types1 option[value=folder]').hide();
                    $('#filefolderType1').val('Upload File');

                } else {
                    $('#types1 option[value=folder]').prop("selected", true).show();
                    $('#types1 option[value=file]').hide();
                    $('#filefolderType1').val('Upload Folder');
                }

                if (data.data.sourceType == "6") {

                    $('#apstor').css('display', 'block');
                    $('#shfold').css('display', 'none');
                    $('#gpstor').css('display', 'none');
                    $('#npstor').css('display', 'none');
                    $('#nhrepo').css('display', 'none');
                    $('#otrepo').css('display', 'none');
                    $('#sourceType1').val("Apple Store");
                    $("#iplay1").prop("checked", true);
                    $("#appId1").val(data.data.appId);
                    $('#filenameDiv1,#destaddrDiv,#eactionDate,#enotify,#euniAction,.nhrep1,#packDescDiv1,.distClass1,#removeDiv').hide();
                    $('#packNameDiv1,#appIdDiv1,.version').show();

                } else if (data.data.sourceType == "1") {

                    $('#shfold').css('display', 'block');
                    $('#apstor').css('display', 'none');
                    $('#gpstor').css('display', 'none');
                    $('#npstor').css('display', 'none');
                    $('#nhrepo').css('display', 'none');
                    $('#otrepo').css('display', 'none');
                    $('#sourceType1').val("Shared Folder");
                    $("#sfolder1").prop("checked", true);
                    $('.shared1,.accessCLass1').fadeIn();
                    $('.nhrep1,#filenameDiv1,#destaddrDiv,#eactionDate,#enotify,#euniAction,#appIdDiv1').hide();

                } else if (data.data.sourceType == "4") {

                    $('#shfold').css('display', 'none');
                    $('#apstor').css('display', 'none');
                    $('#gpstor').css('display', 'block');
                    $('#npstor').css('display', 'none');
                    $('#nhrepo').css('display', 'none');
                    $('#otrepo').css('display', 'none');
                    $('#sourceType1').val("Google Play Store");
                    $("#gplay1").prop("checked", true);
                    $('.Path1,#filenameDiv1,#destaddrDiv,#eactionDate,#enotify,#euniAction,#appIdDiv1').hide();
                    $('.version1').hide();
                    $('.accessCLass1').hide();
                    $('.showSecure1').hide();

                } else if (data.data.sourceType == "5") {

                    $('#shfold').css('display', 'none');
                    $('#apstor').css('display', 'none');
                    $('#gpstor').css('display', 'none');
                    $('#npstor').css('display', 'block');
                    $('#nhrepo').css('display', 'none');
                    $('#otrepo').css('display', 'none');
                    $('#sourceType1').val("Nanoheal Play Store");
                    $("#nplay1").prop("checked", true);
                    $('#filenameDiv1,#destaddrDiv,.distributionpreCheck1,#appIdDiv1').hide();

                } else if (data.data.sourceType == "2") {
                    $('#shfold').css('display', 'none');
                    $('#apstor').css('display', 'none');
                    $('#gpstor').css('display', 'none');
                    $('#npstor').css('display', 'none');
                    $('#nhrepo').css('display', 'block');
                    $('#otrepo').css('display', 'none');
                    $('#sourceType1').val("Nanoheal Repository");
                    $("#nhrep1").prop("checked", true);
                    $('#filenameDiv1,#destaddrDiv,#eactionDate,#enotify,#euniAction,#appIdDiv1').hide();

                } else if (data.data.sourceType == "3") {

                    $('#shfold').css('display', 'none');
                    $('#apstor').css('display', 'none');
                    $('#gpstor').css('display', 'none');
                    $('#npstor').css('display', 'none');
                    $('#nhrepo').css('display', 'none');
                    $('#otrepo').show();
                    $('#sourceType1').val("Vendor Repository");
                    $('#removeDiv').hide();
//                    $('#otrep0').attr('checked');
                    $("#otrep0").prop("checked", true);

                    var pl = $("#platform1").val();

                    if (pl == 'windows') {
                        $('.shared1').fadeOut();
                    }

                    if (pl == 'mac' || pl == 'linux') {
                        $('.FileName1').hide();
                    }

                    $('.nhrep1,#eactionDate,#enotify,#euniAction,.distClass1,#filenameDiv1,#destaddrDiv,.distributionpreCheck1,.preDisCheckClass1,.preinstcheckFields1,#appIdDiv1').hide();
                    $('.accessCLass1').show();

                }
                $("#existingfilename").html(data.data.fileName);
                $("#existingfilename2").html(data.data.fileName2);
                $('#packName1').val(data.data.packageName);
                $("#packName1").attr("title",data.data.packageName);
                $('#path1').val(data.data.path);
                $('#filename1').val(data.data.fileName);
                $('#packDesc1').val(data.data.packageDesc);
                $('input#version1').val(data.data.version);

                if (data.data.platform == "android" && data.data.sourceType == "5") {

                    $('#iconDiv').css('display', 'none');
                    $('#iconName1').val(data.data.androidIcon);
                    $('#androidI1').val(data.data.androidIcon);
                    $('#eactionDate').css('display', 'block');
                    $('#actionDate1').val(data.data.androiddate);
                    $('#enotify').css('display', 'block');
                    $('#notify1').val(data.data.androidnotify);
                    $('#euniAction').css('display', 'block');
                    $('#uniAction1').val(data.data.androidUninstall);
                    $('#EditSiteDiv').show();
                    $('#siteArray1').val(data.data.androidSite);

                } else {

                    $('#iconDiv').css('display', 'none');

                }
                $('#version1').val(data.data.version);

                if (data.data.access == "Anony" && data.data.sourceType == "3") {

                    $('.showaccess1').css('display', 'block');
//                    $('.showSecure1').css('display','block');
                    $('.showSecure1').fadeOut();
                    $('#anony1').prop('checked', true);
                    $('#username1').val(data.data.userName);
                    $('#password1').val(data.data.password);
                    $('#domain1').val(data.data.domain);

                } else if (data.data.access == "Secure" && data.data.sourceType == "3") {

                    $('.showaccess1').css('display', 'block');
//                    $('.showSecure1').css('display','block');
                    $('.showSecure1').fadeIn();
                    $('#secure1').prop('checked', true);
                    $('#username1').val(data.data.userName);
                    $('#password1').val(data.data.password);
                    $('#domain1').val(data.data.domain);

                }
                if (data.data.platform == "android" || data.data.platform == "mac" || data.data.platform == "linux") {

                    $('.distClass').css('display', 'none');

                } else if (data.data.sourceType != "3") {

                    if (data.data.distrubute == "1") {

                        $('#distCheck1').prop("checked", true);
                        $('#distCheck1').val('1');

                    } else if (data.data.distrubute == "0") {

                        $('#distCheck1').removeAttr('checked');
                        $('#distCheck1').val('0');

                    }
                }
                if((data.data.platform == "mac" || data.data.platform == "linux") && data.data.sourceType == "2") {
                    $('#editpathDiv').show();
                    $('#editpath').val(data.data.path);
                }

                if (data.data.global == 'yes') {
                    
                    $('#global1').parents('form').find('#global-patch-yes').prop('checked', true);
                    $('#global1').parents('form').find('#global-patch-no').prop('checked', false);
                    $('#global1').val('yes');
                } else {
                    $('#global1').parents('form').find('#global-patch-yes').prop('checked', false);
                    $('#global1').parents('form').find('#global-patch-no').prop('checked', true);
                    $('#global1').removeAttr('value');
                }

//                $('#stat').html(data.status);
//                $('#text4').html(data.dcheckPreInstall);
//                $('#text4').html(data.strdcheckPreInstall);
//                $('#text4').html(data.trimdcheckPreInstall);

                if (data.data.fileandicon != '' && data.data.sourceType == "5") {

                    $('#fileonly1').val(data.data.fileandicon);
                    $('#icononly1').val(data.data.fileiconUrl);
                    $('#iconspace').css('display', 'block');
                    $('#iconremove1').css('display', 'block');

                } else {

                    $('#fileonly1').val(data.data.fileonly);

                }

                $("#manifesttypes1").val(data.data.distributionPath).change();
                $("#manifestname1").val(data.data.distributionPath).change();

                $(".selectpicker").selectpicker('refresh');

                
                
                var dPlatform = data.data.platform;
                var dIsConfigured = data.data.dIsConfigured;
                var dDistribute = data.data.distrubute;
                var dId = data.data.id;
                
                
                if (dPlatform == 'windows') {
                    if (dIsConfigured == '1') {
                        if (dDistribute == 1) {
                            //$isconfig = "<span style='color:green;cursor: pointer; cursor: hand;' onclick='showCofigDetail(" . $res[$key]['id'] . ",\"a\");'>Edit</span> | <span id='isconfig' style='color:red;cursor: pointer; cursor: hand;' onclick='configure(" . $res[$key]['id'] . "," . $res[$key]['distrubute'] . ");'>Configure</span>";
                            
                        } else {
                            //$isconfig = "<span id='isconfig' style='color:green;' onclick='configure(" . $res[$key]['id'] . "," . $res[$key]['distrubute'] . ");'>Configure</span>";
                        }
                        
                        extraButton.attr('onclick','configure('+dId+','+dDistribute+')').show();
                    } else if (dIsConfigured == '2') {
                        if (dDistribute == 1) {
                            //$isconfig = "<span style='color:green;cursor: pointer; cursor: hand;' onclick='showCofigDetail(" . $res[$key]['id'] . ",\"a\");'>Edit</span> | <span id='isconfig' style='color:green;cursor: pointer; cursor: hand;' onclick='configure(" . $res[$key]['id'] . "," . $res[$key]['distrubute'] . ");'>Configure</span>";
                        } else {
                            //$isconfig = "<span id='isconfig' style='color:red;cursor: pointer; cursor: hand;' onclick='configure(" . $res[$key]['id'] . "," . $res[$key]['distrubute'] . ");'>Configure</span>";
                        }
                        extraButton.attr('onclick','configure('+dId+','+dDistribute+')').show();
                    } else {
                        if (dDistribute == 1) {
                            //$isconfig = "<span style='color:green;cursor: pointer; cursor: hand;' onclick='showCofigDetail(" . $res[$key]['id'] . ",\"a\");'>Edit</span> | <span id='isconfig' style='color:red;cursor: pointer; cursor: hand;' onclick='configure(" . $res[$key]['id'] . "," . $res[$key]['distrubute'] . ");'>Configure</span>";
                        } else {
                            //$isconfig = "<span id='isconfig' style='color:red;cursor: pointer; cursor: hand;' onclick='configure(" . $res[$key]['id'] . "," . $res[$key]['distrubute'] . ");'>Configure</span>";
                        }
                        extraButton.attr('onclick','configure('+dId+','+dDistribute+')').show();
                    }
                } else if (dPlatform == 'mac' || dPlatform == 'linux') {
                    if (dIsConfigured == '2') {
                        if (dDistribute == 1) {
                        //$isconfig = "<span id='isconfig' style='color:green;cursor: pointer; cursor: hand;' onclick='MACconfigPatch(" . $res[$key]['id'] . ",2);'>Configure</span>";
                        } else {
                            // $isconfig = "<span id='isconfig' style='color:green;cursor: pointer; cursor: hand;' onclick='MACconfigPatch(" . $res[$key]['id'] . ",2);'>Configure</span>";
                        }

                        extraButton.attr('onclick','MACconfigPatch('+dId+',2)').show();
                    } else {
                        if (dDistribute == 1) {
                                //$isconfig = "<span id='isconfig' style='color:red;cursor: pointer; cursor: hand;' onclick='MACconfigPatch(" . $res[$key]['id'] . ",2);'>Configure</span>";
                        } else {
                            //$isconfig = "<span id='isconfig' style='color:red;cursor: pointer; cursor: hand;' onclick='MACconfigPatch(" . $res[$key]['id'] . ",2);'>Configure</span>";
                        }
                        
                        extraButton.attr('onclick','MACconfigPatch('+dId+',2)').show();
                    }
                } else {
                    //$isconfig = "<span style='color:green;cursor: pointer; cursor: hand;'>Configured</span>";
                }
            }
        });
        openModifyBox();
    });
    //Edit Popup End here
});

function andPosKeyFields1() {
    var posKey1 = $("#posKey1").val();
    $("#manifestDiv1").hide();
    switch (posKey1) {
        case "415":
            $("#nanohealRepository1").show();
            $("#nhrep1").click();
            $(".CommAndPosKey288,.CommAndPosKey415,#packageAndVersionDiv1,#pathSizeDiv1,#downloadTypeDiv1,#appIdDiv1,#packAndVersDiv1,#appleplay1,#googleplay1,#androidMandatory1,#nanohealplay1,#androidIco1,#otherRepository1,#policyEnforceDiv1,#titleDiv1,#preCheckPathDiv1").fadeOut();
            $("#installTypeDiv1,#nanohealRepository1,.configureSource,#packNameDiv1,#packDescDiv1,.version,#fileTypeDiv1").fadeIn();
            break;
        case "288":
            $("#nanohealRepository1").show();
            $("#nhrep1").click();
            $("#distTypeDiv1,#fileTypeDiv1,#policyEnforceDiv1,#posKeywords1,#preCheckPathDiv1").fadeIn();
            $(".CommAndPosKey415,#packageAndVersionDiv1,#pathSizeDiv1,#policyEnforceDiv1,#googleplay1,#androidMandatory1,#nanohealplay1,#androidIco1,#appleplay1,#otherRepository1,#appIdDiv1,#packAndVersDiv1,#titleDiv1").fadeOut();
            break;
        default:
            $(".CommAndPosKey288,.CommAndPosKey415,#packageAndVersionDiv1,#pathSizeDiv1,#downloadTypeDiv1,#titleDiv1,#appIdDiv1,#policyEnforceDiv1,#packAndVersDiv1,#appleplay1,#preCheckPathDiv1").fadeOut();
            $("#googleplay1,#androidMandatory1,#nanohealplay1,#androidIco1,#otherRepository1,#nanohealRepository1,.configureSource,#packNameDiv1,#packDescDiv1,.version,#fileTypeDiv1").fadeIn();
            $("#gplay1").click();
            break;
    }
}

function distTypeFn1() {
    var distType1 = $("#distType1").val();
    switch (distType1) {
        case "1":
            $("#distTypeDiv1,#andPreCheckDiv1,#downloadPathDiv1,#downloadTypeDiv1,#maxtimeperpatch1,#nanohealRepository1,.configureSource1,#packNameDiv1,#packDescDiv1,.version1,#policyEnforceDiv1,#preCheckPathDiv1").fadeIn();
            $("#andPostCheckDiv1,#packageAndVersionDiv1,#sourceDestinationDiv1,#googleplay,#androidMandatory1,#nanohealplay,#androidIco1,#appleplay,#otherRepository,#appIdDiv1,#titleDiv1,#packAndVersDiv1,.configureSource1,#pathSizeDiv1").fadeOut();
            $("#andPreCheck1 option[value=0]").show();
            $("#andPreCheck1 option[value=1]").hide();
            $("#andPreCheck1 option[value=2]").hide();
            $("#andPreCheck1 option[value=0]").prop("selected", true);
            $("#downloadType1 option[value=0]").show();
            $("#downloadType1 option[value=1]").hide();
            $("#policyEnforce1 option[value=0]").show();
            $("#policyEnforce1 option[value=1]").hide();
            $("#policyEnforce1 option[value=2]").hide();
            $("#nhrep1").click();
            $("#andPPackName1,#andPVersionCode1").val("");
            $("#policyEnforceDiv1,#downloadTypeDiv1").hide(); //new requirement
            $(".selectpicker").selectpicker('refresh');
            break;
        case "2":
            $("#distTypeDiv1,#andPreCheckDiv1,#andPostCheckDiv1,#downloadTypeDiv1,#packageAndVersionDiv1,#maxtimeperpatch1,#nanohealRepository,.configureSource1,#packNameDiv1,#packDescDiv1,.version1,#packAndVersDiv1,#policyEnforceDiv1").fadeIn();
            $("#downloadPathDiv1,#sourceDestinationDiv1,#googleplay,#androidMandatory1,#nanohealplay,#androidIco1,#appleplay,#otherRepository,#appIdDiv1,#titleDiv1,.configureSource1,#preCheckPathDiv1").fadeOut();
            $("#andPreCheck1 option[value=1]").show();
            $("#andPreCheck1 option[value=0]").show();
            $("#andPreCheck1 option[value=2]").show();
            $("#andPreCheck1 option[value=1]").prop("selected", true);
            $("#andPostCheckDiv1 option[value=1]").show();
            $("#andPostCheckDiv1 option[value=0]").hide();
            $("#andPostCheckDiv1 option[value=1]").prop("selected", true);
            $("#downloadType1 option[value=0]").show();
            $("#downloadType1 option[value=1]").show();
            $("#downloadType1 option[value=0]").prop("selected", true);
            $("#policyEnforce1 option[value=0]").show();
            $("#policyEnforce1 option[value=1]").show();
            $("#policyEnforce1 option[value=2]").show();
            $("#googleplay").click();
            $(".selectpicker").selectpicker('refresh');
            break;
        case "3":
            $("#distTypeDiv1,#maxtimeperpatch1,#sourceDestinationDiv1,#policyEnforceDiv1,#packNameDiv1,#downloadTypeDiv1").fadeIn();
            $("#andPreCheckDiv1,#downloadPathDiv1,#andPostCheckDiv1,#packageAndVersionDiv1,#googleplay,#androidMandatory1,#nanohealplay,#androidIco1,#appleplay,#otherRepository,#nanohealRepository,.configureSource1,#packDescDiv1,.version1,#appIdDiv1,#titleDiv1,#packAndVersDiv1,#preCheckPathDiv1").fadeOut();
            $("#downloadType1 option[value=0]").show();
            $("#downloadType1 option[value=1]").hide();
            $("#downloadType1 option[value=0]").prop("selected", true);
            $("#policyEnforce1 option[value=0]").show();
            $("#policyEnforce1 option[value=1]").hide();
            $("#policyEnforce1 option[value=2]").hide();
            $(".selectpicker").selectpicker('refresh');
            break;
        default:
            $("#andPreCheckDiv1,#downloadTypeDiv1,#andPostCheckDiv1,#downloadPathDiv1,#packageAndVersionDiv1,#maxtimeperpatch1,#sourceDestinationDiv1,#appIdDiv1,#policyEnforceDiv1,#packAndVersDiv1,#preCheckPathDiv1").fadeOut();
            $("#googleplay,#androidMandatory1,#nanohealplay,#androidIco1,#appleplay,#otherRepository,#nanohealRepository,.configureSource1,#packNameDiv1,#packDescDiv1,.version1").fadeIn();
            $("#nhrep1").click();
            break;
    }
}

function policyEnforceActionFnc1() {
    var policyEnforceAction1 = $("#policyEnforceAction1").val();
    switch (policyEnforceAction1) {
        case "1":
            $(".policyEnforceActionClass1").show();
            break;
        case "2":
            $(".policyEnforceActionClass1").hide();
            break;
        default:
            $(".policyEnforceActionClass1").hide();
            break;
    }
}

function installTypeChange1() {
    var installType1 = $("#installType1").val();
    switch (installType1) {
        case "0":
            $("#preDownloadMsgDiv1,#postDownloadMsgDiv1,#installMsgDiv1,#freqIntActMsgDiv1,#titleDiv1").fadeOut();
            break;
        case "3":
            $("#freqIntActMsgDiv1,#installPopupSpan1").fadeOut();
            $("#preDownloadMsgDiv1,#postDownloadMsgDiv1,#installMsgDiv1,#titleDiv1").fadeIn();
            $("#installAction1 option[value=1]").prop("selected", true);
            $("#installFinishMsgSpan1").show();
            break;
        case "5":
            $("#installPopupSpan1").fadeOut();
            $("#preDownloadMsgDiv1,#postDownloadMsgDiv1,#installMsgDiv1,#freqIntActMsgDiv1,#titleDiv1").fadeIn();
            $("#installAction1 option[value=1]").prop("selected", true);
            $("#installFinishMsgSpan1").show();
            break;
        default:
            $("#preDownloadMsgDiv1,#postDownloadMsgDiv1,#installMsgDiv1,#freqIntActMsgDiv1,#titleDiv1").fadeOut();
            break;
    }
}

function installActionFn1() {
    var installAction1 = $("#installAction1").val();
    switch (installAction1) {
        case "1":
            $(".installPopupSpan1").hide();
            $(".installFinishMsgSpan1").show();
            break;
        case "2":
            $(".installFinishMsgSpan1").hide();
            $(".installPopupSpan1").show();
            break;
        default:
            $(".installFinishMsgSpan1").show();
            $(".installPopupSpan1").hide();
            break;
    }

}

function andPostCheckFn1() {
    var andPostCheck1 = $("#andPostCheck1").val();
    switch (andPostCheck1) {
        case "1":
            $("#downloadTypeDiv1").show();
            $("#packAndVersDiv1").show();
            break;
        case "":
            $("#downloadTypeDiv1").show();
            $("#packAndVersDiv1").hide();
            break;
        default:
            $("#downloadTypeDiv1").show();
            $("#packAndVersDiv1").hide();
            break;
    }
}

function andPreCheckFn1() {
    var andPreCheck1 = $("#andPreCheck1").val();
    switch (andPreCheck1) {
        case "2":
            $("#pathSizeDiv1").show();
            $("#packageAndVersionDiv1,#preCheckPathDiv1").hide();
            $("#apkPath1,#apkSize1").val("");
            break;
        case "1":
            $("#packageAndVersionDiv1").show();
            $("#pathSizeDiv1,#preCheckPathDiv1").hide();
            $("#andPackName1,#andVersionCode1").val("");
            break;
        case "0":
            $("#preCheckPathDiv1").show();
            $("#packageAndVersionDiv1,#pathSizeDiv1").hide();
            break;
        default:
            $("#preCheckPathDiv1").show();
            $("#packageAndVersionDiv1,#pathSizeDiv1").hide();
            break;
    }
}

// On Selecting Distribute Checkbox.
$('#distCheck1').change(function () {

    var dpath = $(this).val();

    if (dpath == 1) {
        $(".distributionPath1,.distributionTime1,.distributionValidPath1,.distributionpreCheck1,.preDisCheckClass1,.preinstcheckFields1").hide();
        $(".preinstcheck1,#preDisCheck1").removeAttr('checked');
        $(".preinstcheck1").val('');
        $(this).val(0);
        $('#preDisCheck1').val(0);
    } else {
        $(this).val(1);
        $(".distributionPath1,.distributionTime1,.distributionValidPath1,.preDisCheckClass1").show();
    }
    
    $('#extraButtonConfigure').prop('disabled', true);
    if($('#distCheck1').is(':checked')){ 
        var isDistributionSaved = $('[name=is_distribution_saved]');
        if(isDistributionSaved!=undefined && !isNaN(isDistributionSaved) && parseInt(isDistributionSaved)==1){
            $('#extraButtonConfigure').prop('disabled', false);
        }
    }     
});

// PreDistribution Check
function check() {

    var countChecked = function () {

        var preDisVal = $("#preDisCheck1:checked").length;
        var platformpd = $("#platform1").val();
        if (preDisVal > 0) {

            $("#preDisCheck1").val(1);
            $("#preDisCheck1").prop('checked', true);
            $(".distributionpreCheck1").show();
            if (platformpd == 'mac') {
                $("#pRegistryDiv1").hide();
                $("#distributionPreCheckDiv1").hide();
            }
            $("#distributionPreCheckDiv1").hide();
        } else {

            $("#preDisCheck1").val(0);
            $(".distributionpreCheck1,.preinstcheckFields1").hide();
            $(".preinstcheckFields1").find('input').val('');
            $(".preinstcheck1").removeAttr('checked');
        }
    };
    countChecked();
    $("input[type=checkbox]").on("click", countChecked);
    //  });
}


if ($('#pfilePath1').is(':checked')) {
    var platformpi = $("#platform").val();
    $(".pSoftware1").show();
    setTimeout(function () {
        if (platformpi == 'mac' || platformpi == 'linux') {
            $("#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();
            $("#distributionPreCheckDiv1").hide();
        }
    }, 100);
} else {
    $(".pSoftware1").hide();
}

$("#fileremove1").click(function () {
    var fv = $("#ftp1val").val();
    var cv = $("#cdn1val").val();

    $("#ftpspan1").html(fv);
    $("#cdnspan1").html(cv);
    $("#fileonly1").val("");
    $("#icononly1").val("");
    $("#existingfilename").hide();
    $(".packNameclear").val("");
    $(".chooseFile1,.uploadbuttonDiv1,.configureSource1").show();
    $(".showfile1,.showrepository1").hide();
    $(".editedfilesList").html("");
    $("#fileUp1").fadeIn();
    $(".removeDiv,.editedfilesList").fadeOut();
    $("#files1").html('');
    $("#filevalidationtext1").html('');
    $('#upload1').unbind('click');
    $("#fileupload1").removeAttr('disabled');
    $("#addPackage1").prop("disabled", false);
    uploadCount = 0;
    
    $(this).parents('#removeDiv').hide();
    if ($('input#cdnupload1').is(':checked') && !$('input#cdnupload1').is(':disabled')) {
        $('input#cdnupload1').trigger('click');
    } else if ($('input#ftpupload1').is(':checked') && !$('input#ftpupload1').is(':disabled')) {
        $('input#ftpupload1').trigger('click');
    }
    window.isOldImageDeleted = true;
});

$("#fileremove1_2").click(function () {
    var fv = $("#ftp1val").val();
    var cv = $("#cdn1val").val();

    $("#ftpspan1").html(fv);
    $("#cdnspan1").html(cv);
    $("#fileonly1").val("");
    $("#icononly1").val("");
    $("#existingfilename2").hide();
    $(".packNameclear").val("");
    $(".chooseFile1,.uploadbuttonDiv1,.configureSource1").show();
    $(".showfile1,.showrepository1").hide();
    $(".editedfilesList").html("");
    $("#fileUp1").fadeIn();
    $(".removeDiv,.editedfilesList").fadeOut();
    $("#files1").html('');
    $("#filevalidationtext1").html('');
    $('#upload1').unbind('click');
    $("#fileupload1").removeAttr('disabled');
    $("#addPackage1").prop("disabled", false);
    uploadCount = 0;
    
    $(this).parents('#removeDiv').hide();
    if ($('input#cdnupload1').is(':checked') && !$('input#cdnupload1').is(':disabled')) {
        $('input#cdnupload1').trigger('click');
    } else if ($('input#ftpupload1').is(':checked') && !$('input#ftpupload1').is(':disabled')) {
        $('input#ftpupload1').trigger('click');
    }
    window.isOldImageDeleted = true;
});
//Edit Popup Change Start
function editPackageFunction() {
    
    if(window.uploadCdnFtpUploadInProgress){
        errorNotify('Upload in progress, please wait');
        return;
    }
    
    var uploadcheck = $(".files1").text();
    
    $("#fileUpErr").html("").hide();
    
    if (uploadcheck == undefined || uploadcheck == "" || uploadcheck == null || uploadcheck == "undefined") {
        $("#fileUpErr").html("<span>Please upload a file to submit changes</span>").show();
        setTimeout(function () {
            $("#fileUpErr").html("").hide();
        }, 2000);
    }

    $("#checkavail1").fadeIn(1);
    $("#checkavail1").html("");

    $("#packName1Msg").fadeIn();
    $("#packDesc1Msg").fadeIn();
    $("#dPath1Msg").fadeIn();
    $("#dTime1Msg").fadeIn();
    $("#dvPath1Msg").fadeIn();
    $("#version1Msg").fadeIn();
    $("#username1Msg").fadeIn();
    $("#password1Msg").fadeIn();
    $("#domain1Msg").fadeIn();
    $("#pfilePath1Msg").fadeIn();
    $("#pSoftName1Msg").fadeIn();
    $("#pSoftVer1Msg").fadeIn();
    $("#pKb1Msg").fadeIn();
    $("#pServicePack1Msg").fadeIn();
    $("#subKey1Msg").fadeIn();
    $("#pre1Msg").fadeIn();

    $("#packName1Msg").html("");
    $("#packDesc1Msg").html("");
    $("#dPath1Msg").html("");
    $("#dTime1Msg").html("");
    $("#dvPath1Msg").html("");
    $("#version1Msg").html("");
    $("#username1Msg").html("");
    $("#password1Msg").html("");
    $("#domain1Msg").html("");
    $("#pfilePath1Msg").html("");
    $("#pSoftName1Msg").html("");
    $("#pSoftVer1Msg").html("");
    $("#pKb1Msg").html("");
    $("#pServicePack1Msg").html("");
    $("#subKey1Msg").html("");
    $("#pre1Msg").html("");

    var packName = $("#packName1").val();
    var types = $("#types1").val();
    var version = $("#version1").val();
    var platform = $("#platform1").val();
    var filename = $("#filename1").val();
    var packDesc = $("#packDesc1").val();
    var ftpupload = $("#ftpupload1").val();
    var cdnupload = $("#cdnupload1").val();
    var actionDate = $("#actionDate1").val();
    var notify = $("#notify1").val();
    var uniAction = $("#uniAction1").val();
    var manifesttypes = $("#manifesttypes").val();
    var manifestname = $("#manifestname").val();
    var dPath1 = $("#dPath1").val();
    var dTime1 = $("#dTime1").val();
    var dvPath1 = $("#dvPath1").val();
    var username1 = $("#username1").val();
    var password1 = $("#password1").val();
    var domain1 = $("#domain1").val();
    var pfilePath1 = $("#pfilePath1").val();
    var pSoftName1 = $("#pSoftName1").val();
    var pSoftVer1 = $("#pSoftVer1").val();
    var pKb1 = $("#pKb1").val();
    var pServicePack1 = $("#pServicePack1").val();
    var subKey1 = $("#subKey1").val();
    var res = platform.toLowerCase();
    
    
    if(window.isOldImageDeleted && !window.isFileUploadedInEditPage){
        errorNotify("Please upload file(s) to proceed.");
        return false;
    }
    
    if(packName.length > 128){
        errorNotify("The package name length should not be more than 128 characters");
        return false;
    }

    if(packDesc.length > 256){
        errorNotify("The package description length should not be more than 256 characters");
        return false;
    }

    if(version.length > 128){
        errorNotify("The version length should not be more than 128 characters");
        return false;
    }

    var checkData = {
        'packName1': $('[name=packName1]').val(),
        'packDesc1': $('[name=packDesc1]').val(),
        'version1': $('[name=version1]').val(),
        'id1': $('[name=id1]').val(),
        'csrfMagicToken': csrfMagicToken
    };

    $.ajax({
        url: 'SavePackageDataInDB.php?selectType1=edit&check=true',
        type: 'POST',
        data: checkData,
        async: true,
        success: function (data) {
            data = $.parseJSON(data);
            
            if(!data.success){
                errorNotify(data.message);
                return;
            }
            
            editContinue();
        },
        error: function () {
        }
    });

    function editContinue(){
            switch (res) {
            case "windows":
                if (packName != "" && types != "" && version != "" && platform != "" && packDesc != "") {
                            var saveUrl = 'SavePackageDataInDB.php';
                        if ($('#global-patch-yes').is(':checked')){
                            var global = 'yes';
                        }else{
                            var global = 'no';
                                    }
                        var checkData = {
                                'packName1': $('[name=packName1]').val(),
                                'packDesc1': $('[name=packDesc1]').val(),
                                'version1': $('[name=version1]').val(),
                                'id1': $('[name=id1]').val(),
                                'global1' : global,
                                'platform1' : res,
                                'check' : "false",
                                'selectType1' : 'edit',
                                'csrfMagicToken': csrfMagicToken
                            };
                            $.ajax({
                                url: saveUrl,
                                type: 'POST',
                                data: checkData,
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
                                    rightContainerSlideClose('rsc-edit-container');
                                    updateUploadStatusE(inserteddid);
                                },
//                                cache: false,
//                                contentType: false,
//                                processData: false
                            });
                    } else {
                    if (packName == "") {
                        $("#packName1Msg").html("<span>Package Name is required</span>");
                        setTimeout(function () {
                            $("#packName1Msg").fadeOut(3000);
                        }, 2000);

                    }
                    if (packDesc == "") {
                        $("#packDesc1Msg").html("<span>Package Description is required</span>");
                        setTimeout(function () {
                            $("#packDesc1Msg").fadeOut(3000);
                        }, 2000);

                    }
                    if (version == "") {
                        $("#version1Msg").html("<span>Software Version name is required</span>");
                        setTimeout(function () {
                            $("#version1Msg").fadeOut(3000);
                        }, 2000);

                    }

                    }
                break;
            case "android":
                var saveUrl = 'SavePackageDataInDB.php';
                var formData = new FormData($("#addPatchValidate1")[0]);
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

                        updateUploadStatusE(inserteddid);
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
                break;
            case "ios":
                var saveUrl = 'SavePackageDataInDB.php';
                var formData = new FormData($("#addPatchValidate1")[0]);
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

                        updateUploadStatusE(inserteddid);
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
                break;
            case "mac":
                var saveUrl = 'SavePackageDataInDB.php';
                var formData = new FormData($("#addPatchValidate1")[0]);
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

                        updateUploadStatusE(inserteddid);
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
                break;
            case "linux":
                var saveUrl = 'SavePackageDataInDB.php';
                var formData = new FormData($("#addPatchValidate1")[0]);
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

                        updateUploadStatusE(inserteddid);
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
                break;
            default:
                break;

        }
    }

}

function updateUploadStatusE(idInserted) {

    $.ajax({
        url: 'SWD_Function.php',
        type: 'POST',
        data: {function: "updateUploadStatFn", id: idInserted,csrfMagicToken: csrfMagicToken},
        success: function (data) {
            if (idInserted != 0) {
                rightContainerSlideClose('rsc-edit-container');
                $("#edit_software_distribution").modal("hide");
                showCofigDetail(idInserted, 'a');
            } else {
                Get_SoftwareRepositoryData();
                rightContainerSlideClose('rsc-edit-container');
            }
        }
    });
}

function accessFunction1(showsecure) {

    if (showsecure == "anony1") {

        $(".showSecure1").fadeOut();

    } else if (showsecure == "secure1") {

        $(".showSecure1").fadeIn();

    }

}

//FTP FILE UPLOAD FUNCTIONALITY	
$("#ftpupload1").on("click", function () {
    $('#uploadFile1').trigger('click');
    $("#upload1").val("1");
    var repoLinkContainer = $('#repoLinkContainer');
    var rawUploadContainer = $('#rawUploadContainer');
    var rawUploadContainer2 = $('#rawUploadContainer2');
    rawUploadContainer.show();
    repoLinkContainer.hide();
    rawUploadContainer2.hide();
    $('.nhrep1,.chooseFile1,.fileUp1,#fine-uploader-manual-trigger1').show();
    $('.showrepository1,.CdnFilesList1,#fine-uploader-s31').hide();
    $("#files1,#filevalidationtext_cdn1").html('');

    $("input[type=radio]:checked.selectSource").each(function (i) {
        $(this).removeAttr('checked');
    });

    'use strict';

    var pform = $("#platform1").val();
    var FTPURL_Edit = $("#sFtpUrl1").val();
    var splFTP = FTPURL_Edit.split("/");
    var lenFTP = splFTP.length;
    // var url_part = "/" + splFTP[lenFTP - 3] + "/" + splFTP[lenFTP - 2] + "/";
    var url_part = BASE_PATH+'/swd/';
    var pform = $("#platform").val();

    if (pform == "android") {

        if (($('#nhrep1').is(':checked')) || ($('#nplay1').is(':checked'))) {

            var manualUploader = new qq.FineUploader({
                element: document.getElementById('fine-uploader-manual-trigger1'),
                template: 'qq-template-manual-trigger',
                multiple : false,
                callbacks: {
                    onSubmit: function (id, file) {
                        
                        var newFile = changeUploadFileName(file);
                        this.setName(id, newFile);
                        
                        window.uploadCdnFtpUploadInProgress = true;
                        
                        $('#addPackage1').css('cursor','wait'); 
                        
                        $("#filevalidationtext1").hide();
                        $("#addPackage1").prop("disabled", true);
                        $(".editedfilesList").fadeIn();
                        uploadCount == 0;

                        if ($('#nplay1').is(':checked') || $('#nhrep1').is(':checked')) {
                            uploadCount++;
                        }

                        if (uploadCount == 1) {

                            fileName = file;
                            splitFile = fileName.split('.');
                            removeExt = splitFile[splitFile.length - 1];
                            removedFileExtension = fileName.replace('.' + removeExt, '');
                            $("#files1").show();
                            $("#files1").append("<li class='selectedFile1'>" + file + "</li>");
                            $('input[type=text]').prev().parent().removeClass('is-empty');
                            $("#packName1").val(removedFileExtension);
                            $("#packName1").attr("title",removedFileExtension);
                            $("#filename1").val(file);
                            $("#test1").html("<span>Upload Icon</span>");

                        }

                        if (uploadCount == 2) {

                            $("#files1").append("<li class='selectedFile1'>" + file + "</li>");
                            $('input[type=text]').prev().parent().removeClass('is-empty');
                            $("#androidI1").val(file);
                            $("#fileUp1").fadeOut();
                            $("#addPackage1").removeAttr("disabled");

                        }

                        if (uploadCount >= 2) {
                            setTimeout(function () {
                                $(".qq-upload-button input").prop("disabled", true);
                                $(".qq-upload-button").addClass("disabled");
                            }, 500);

                        }
                        
                        $("#filename1").val(newFile);
                    },
                    onProgress: function (id, file, totalBytesUploaded, totalBytesTotal) {
                        $("#filevalidationtext1").hide();
                        if (totalBytesUploaded >= totalBytesTotal) {
                            $("#upStatus1").val("1");
                            $(".removeDiv").fadeIn();
                            if (uploadCount == 2) {
                                $("#progress2").fadeOut();
                            }
                            if (uploadCount == 3) {
                                $("#addPackage1").removeAttr("disabled");
                                $("#fileUp1").fadeOut();
                            }
                            $(".editedfilesList").append("<label>" + file + "</label>");
                        }
                    },
                    onDelete: function (id) {
                        window.isFileUploadedInEditPage = false;
                        $("#addPackage1").prop("disabled", true);
                        $("#filevalidationtext1").hide();
                        this.reset();
                        $("#files1").html('');
                        $("#filevalidationtext1").html('');
                        $('#upload1').unbind('click');
                        $("#fileupload1").removeAttr('disabled');
                        uploadCount = 0;
                        $('#fileupload1').uploadify('cancel', '*');
                        $(".editedfilesList").html("");
                        $("#fileUp1").fadeIn();
                        $(".clearFilesDiv1,.editedfilesList").fadeOut();
                        setTimeout(function () {
                            $(".qq-upload-button input").prop("disabled", false);
                            $(".qq-upload-button").removeClass("disabled");
                        }, 500);
                        uploadCount = 0;
                        window.isFileUploadedInEditPage = false;
                    },
                    onError: function (event, id, name, errorReason, xhrOrXdr) {
                        window.uploadCdnFtpUploadInProgress = false;
                        window.isFileUploadedInEditPage = false;
                        $('#addPackage1').css('cursor','pointer'); 
                        $("#filevalidationtext1").show().html(name).focus();
                        window.isFileUploadedInEditPage = false;
                    },
                    onComplete : function(){
                     window.uploadCdnFtpUploadInProgress = false;
                     window.isFileUploadedInEditPage = true;
                     $('#addPackage1').css('cursor','pointer');  
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

    } else if (pform == "ios") {

        if (($('#nhrep1').is(':checked')) || ($('#nplay1').is(':checked'))) {

            var manualUploader = new qq.FineUploader({
                element: document.getElementById('fine-uploader-manual-trigger1'),
                template: 'qq-template-manual-trigger',
                multiple : false,
                callbacks: {
                    onSubmit: function (id, file) {
                        var newFile = changeUploadFileName(file);
                        this.setName(id, newFile);
                        
                        window.uploadCdnFtpUploadInProgress = true;
                        $('#addPackage1').css('cursor','wait'); 
                        
                        $("#filevalidationtext1").hide();
                        $("#addPackage1").prop("disabled", true);
                        $(".editedfilesList").fadeIn();
                        uploadCount == 0;

                        if ($('#nplay1').is(':checked') || $('#nhrep1').is(':checked')) {

                            uploadCount++;

                        }

                        if (uploadCount == 1) {

                            fileName = file;
                            splitFile = fileName.split('.');
                            removeExt = splitFile[splitFile.length - 1];
                            removedFileExtension = fileName.replace('.' + removeExt, '');
                            $("#files1").append("<li class='selectedFile1'>" + file + "</li>");
                            $('input[type=text]').prev().parent().removeClass('is-empty');
                            $("#packName1").val(removedFileExtension);
                            $("#packName1").attr("title",removedFileExtension);
                            $("#filename1").val(file);
                            $("#test1").text("Upload Icon");

                        }

                        if (uploadCount == 2) {

                            $("#files1").append("<li class='selectedFile1'>" + file + "</li>");
                            $('input[type=text]').prev().parent().removeClass('is-empty');
                            $("#androidI1").val(file);
                            $("#fileUp1").fadeOut();
                        }

                        if (uploadCount >= 2) {

                            setTimeout(function () {
                                $(".qq-upload-button input").prop("disabled", true);
                                $(".qq-upload-button").addClass("disabled");
                            }, 500);

                        }
                        
                        $("#filename1").val(newFile);
                    },
                    onProgress: function (id, file, totalBytesUploaded, totalBytesTotal) {
                        $("#filevalidationtext1").hide();
                        if (totalBytesUploaded >= totalBytesTotal) {
                            $("#upStatus1").val("1");
                            $(".removeDiv").fadeIn();
                            if (uploadCount == 2) {
                                $("#progress2").fadeOut();
                            }
                            if (uploadCount == 3) {
                                $("#addPackage1").removeAttr("disabled");
                                $("#fileUp1").fadeOut();
                            }
                            $(".editedfilesList").append("<label>" + file + "</label>");
                        }
                    },
                    onDelete: function (id) {
                        window.isFileUploadedInEditPage = false;
                        $("#addPackage1").prop("disabled", true);
                        $("#filevalidationtext1").hide();
                        this.reset();
                        $("#files1").html('');
                        $("#filevalidationtext1").html('');
                        $('#upload1').unbind('click');
                        $("#fileupload1").removeAttr('disabled');
                        uploadCount = 0;
                        $('#fileupload1').uploadify('cancel', '*');
                        $(".editedfilesList").html("");
                        $("#fileUp1").fadeIn();
                        $(".clearFilesDiv1,.editedfilesList").fadeOut();
                        setTimeout(function () {
                            $(".qq-upload-button input").prop("disabled", false);
                            $(".qq-upload-button").removeClass("disabled");
                        }, 500);
                        uploadCount = 0;
                        window.isFileUploadedInEditPage = false;
                    },
                    onError: function (event, id, name, errorReason, xhrOrXdr) {
                        window.uploadCdnFtpUploadInProgress = false;
                        window.isFileUploadedInEditPage = false;
                        $('#addPackage1').css('cursor','pointer'); 
                        $("#filevalidationtext1").show().html(name).focus();
                    },
                    onComplete : function(){
                     window.uploadCdnFtpUploadInProgress = false;
                     window.isFileUploadedInEditPage = true;
                     $('#addPackage1').css('cursor','pointer');  
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

    } else if (pform == "windows" || pform == "mac" || pform == "linux") {

        if ($('#nhrep1').is(':checked')) {

            var manualUploader = new qq.FineUploader({
                element: document.getElementById('fine-uploader-manual-trigger1'),
                template: 'qq-template-manual-trigger',
                multiple : false,
                callbacks: {
                    onSubmit: function (id, file) {
                        var newFile = changeUploadFileName(file);
                        this.setName(id, newFile);
                        
                        window.uploadCdnFtpUploadInProgress = true;
                        $('#addPackage1').css('cursor','wait'); 
                        
                        $("#filevalidationtext1").hide();
                        $("#addPackage1").prop("disabled", true);
                        $(".editedfilesList").fadeIn();
                        uploadCount == 0;

                        if ($('#nhrep1').is(':checked')) {

                            uploadCount++;

                        }
                        if (uploadCount == 1) {

                            fileName = file;
                            splitFile = fileName.split('.');
                            removeExt = splitFile[splitFile.length - 1];
                            removedFileExtension = fileName.replace('.' + removeExt, '');
                            $("#files1").append("<li class='selectedFile1'>" + file + "</li>");
                            $('input[type=text]').prev().parent().removeClass('is-empty');
                            $("#packName1").val(removedFileExtension);
                            $("#packName1").attr("title",removedFileExtension);
                            $("#filename1").val(file);

                        }

                        if (uploadCount >= 1) {
                            setTimeout(function () {
                                $(".qq-upload-button input").prop("disabled", true);
                                $(".qq-upload-button").addClass("disabled");
                            }, 500);
                        }
                        
                        $("#filename1").val(newFile);
                    },
                    onProgress: function (id, file, totalBytesUploaded, totalBytesTotal) {
                        $("#filevalidationtext1").hide();
                        if (totalBytesUploaded >= totalBytesTotal) {
                            $("#addPackage1").removeAttr("disabled");
                            $("#upStatus1").val("1");
                            $(".removeDiv").fadeIn();
                            $("#fileUp1").fadeOut();
                            $(".editedfilesList").append("<label>" + file + "</label>");
                        }
                    },
                    onDelete: function (id) {
                        window.isFileUploadedInEditPage = false;
                        $("#addPackage1").prop("disabled", true);
                        $("#filevalidationtext1").hide();
                        this.reset();
                        $("#files1").html('');
                        $("#filevalidationtext1").html('');
                        $('#upload1').unbind('click');
                        $("#fileupload1").removeAttr('disabled');
                        uploadCount = 0;
                        $('#fileupload1').uploadify('cancel', '*');
                        $(".editedfilesList").html("");
                        $("#fileUp1").fadeIn();
                        $(".clearFilesDiv1,.editedfilesList").fadeOut();
                        setTimeout(function () {
                            $(".qq-upload-button input").prop("disabled", false);
                            $(".qq-upload-button").removeClass("disabled");
                        }, 500);
                        uploadCount = 0;
                    },
                    onError: function (event, id, name, errorReason, xhrOrXdr) {
                        window.uploadCdnFtpUploadInProgress = false;
                        window.isFileUploadedInEditPage = false;
                        $('#addPackage1').css('cursor','pointer'); 
                        $("#filevalidationtext1").show().html(name).focus();
                    },
                    onComplete : function(){
                     window.uploadCdnFtpUploadInProgress = false;
                     window.isFileUploadedInEditPage = true;
                     $('#addPackage1').css('cursor','pointer');  
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

$("#cdnupload1").on("click", function () {
    
    if($("#cdnupload1").parents('.form-check.form-check-radio').hasClass('disabled')){
        $("#cdnupload1").prop("checked", false);
        errorNotify('Please configure cdn');
        return;
    }
    
    $('#uploadFile1').trigger('click');

    $("#upload1").val("1");

    $('.nhrep1,.chooseFile1,.fileUp1,#fine-uploader-manual-trigger1,.showrepository1').show();
    $('.CdnFilesList1,#fine-uploader-s31').hide();
    $("#files1,#filevalidationtext_cdn1").html('');

    $("input[type=radio]:checked.selectSource").each(function (i) {
        $(this).removeAttr('checked');
    });

    'use strict';
    var AWSACCESS = $("#AWSACCESS1").val();
    var AWSSECRET = $("#AWSSECRET1").val();
    var AWSBUCKET = $("#AWSBUCKET1").val();
    var AWSREGION = $("#AWSREGION1").val();

    var pform = $("#platform1").val();
    var FTPURL_Edit = $("#sFtpUrl1").val();
    var splFTP = FTPURL_Edit.split("/");
    var lenFTP = splFTP.length;
    var url_part = window.base+'swd/';

    if (pform == "android") {

        if (($('#nhrep1').is(':checked')) || ($('#nplay1').is(':checked'))) {

            var manualUploader = new qq.FineUploader({
                element: document.getElementById('fine-uploader-manual-trigger1'),
                template: 'qq-template-manual-trigger',
                multiple : false,
                callbacks: {
                    onSubmit: function (id, file) {
                        
                        var newFile = changeUploadFileName(file);
                        this.setName(id, newFile);
                        
                        window.uploadCdnFtpUploadInProgress = true;
                        $('#addPackage1').css('cursor','wait'); 
                        
                        $("#filevalidationtext1").hide();
                        $("#addPackage1").prop("disabled", true);
                        $(".editedfilesList").fadeIn();
                        uploadCount == 0;

                        if ($('#nplay1').is(':checked') || $('#nhrep1').is(':checked')) {
                            uploadCount++;
                        }

                        if (uploadCount == 1) {

                            fileName = file;
                            splitFile = fileName.split('.');
                            removeExt = splitFile[splitFile.length - 1];
                            removedFileExtension = fileName.replace('.' + removeExt, '');
                            $("#files1").show();
                            $("#files1").append("<li class='selectedFile1'>" + file + "</li>");
                            $('input[type=text]').prev().parent().removeClass('is-empty');
                            $("#packName1").val(removedFileExtension);
                            $("#packName1").attr("title",removedFileExtension);
                            $("#filename1").val(file);
                            $("#test1").html("<span>Upload Icon</span>");

                        }

                        if (uploadCount == 2) {

                            $("#files1").append("<li class='selectedFile1'>" + file + "</li>");
                            $('input[type=text]').prev().parent().removeClass('is-empty');
                            $("#androidI1").val(file);
                            $("#fileUp1").fadeOut();
                            $("#addPackage1").removeAttr("disabled");

                        }

                        if (uploadCount >= 2) {
                            setTimeout(function () {
                                $(".qq-upload-button input").prop("disabled", true);
                                $(".qq-upload-button").addClass("disabled");
                            }, 500);

                        }
                        
                        $("#filename1").val(newFile);
                    },
                    onProgress: function (id, file, totalBytesUploaded, totalBytesTotal) {
                        $("#filevalidationtext1").hide();
                        if (totalBytesUploaded >= totalBytesTotal) {
                            $("#upStatus1").val("1");
                            $(".removeDiv").fadeIn();
                            if (uploadCount == 2) {
                                $("#progress2").fadeOut();
                            }
                            if (uploadCount == 3) {
                                $("#addPackage1").removeAttr("disabled");
                                $("#fileUp1").fadeOut();
                            }
                            $(".editedfilesList").append("<label>" + file + "</label>");
                        }
                    },
                    onDelete: function (id) {
                        window.isFileUploadedInEditPage = false;
                        $("#addPackage1").prop("disabled", true);
                        $("#filevalidationtext1").hide();
                        this.reset();
                        $("#files1").html('');
                        $("#filevalidationtext1").html('');
                        $('#upload1').unbind('click');
                        $("#fileupload1").removeAttr('disabled');
                        uploadCount = 0;
                        $('#fileupload1').uploadify('cancel', '*');
                        $(".editedfilesList").html("");
                        $("#fileUp1").fadeIn();
                        $(".clearFilesDiv1,.editedfilesList").fadeOut();
                        setTimeout(function () {
                            $(".qq-upload-button input").prop("disabled", false);
                            $(".qq-upload-button").removeClass("disabled");
                        }, 500);
                        uploadCount = 0;
                    },
                    onError: function (event, id, name, errorReason, xhrOrXdr) {
                        window.isFileUploadedInEditPage = false;
                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage1').css('cursor','pointer'); 
                        $("#filevalidationtext1").show().html(name).focus();
                    },
                    onComplete: function (id, file) {
                        window.isFileUploadedInEditPage = true;
                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage1').css('cursor','pointer'); 
                        onUploadEditSubmit(file, AWSACCESS, AWSSECRET, AWSBUCKET, AWSREGION);
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

    } else if (pform == "ios") {

        if (($('#nhrep1').is(':checked')) || ($('#nplay1').is(':checked'))) {

            var manualUploader = new qq.FineUploader({
                element: document.getElementById('fine-uploader-manual-trigger1'),
                template: 'qq-template-manual-trigger',
                multiple : false,
                callbacks: {
                    onSubmit: function (id, file) {
                        
                        var newFile = changeUploadFileName(file);
                        this.setName(id, newFile);
                        
                        window.uploadCdnFtpUploadInProgress = true;
                        $('#addPackage1').css('cursor','wait'); 
                        $("#filevalidationtext1").hide();
                        $("#addPackage1").prop("disabled", true);
                        $(".editedfilesList").fadeIn();
                        uploadCount == 0;

                        if ($('#nplay1').is(':checked') || $('#nhrep1').is(':checked')) {

                            uploadCount++;

                        }

                        if (uploadCount == 1) {

                            fileName = file;
                            splitFile = fileName.split('.');
                            removeExt = splitFile[splitFile.length - 1];
                            removedFileExtension = fileName.replace('.' + removeExt, '');
                            $("#files1").append("<li class='selectedFile1'>" + file + "</li>");
                            $('input[type=text]').prev().parent().removeClass('is-empty');
                            $("#packName1").val(removedFileExtension);
                            $("#packName1").attr("title",removedFileExtension);
                            $("#filename1").val(file);
                            $("#test1").text("Upload Icon");

                        }

                        if (uploadCount == 2) {

                            $("#files1").append("<li class='selectedFile1'>" + file + "</li>");
                            $('input[type=text]').prev().parent().removeClass('is-empty');
                            $("#androidI1").val(file);
                            $("#fileUp1").fadeOut();
                        }

                        if (uploadCount >= 2) {

                            setTimeout(function () {
                                $(".qq-upload-button input").prop("disabled", true);
                                $(".qq-upload-button").addClass("disabled");
                            }, 500);

                        }
                        
                        $("#filename1").val(newFile);
                    },
                    onProgress: function (id, file, totalBytesUploaded, totalBytesTotal) {
                        $("#filevalidationtext1").hide();
                        if (totalBytesUploaded >= totalBytesTotal) {
                            $("#upStatus1").val("1");
                            $(".removeDiv").fadeIn();
                            if (uploadCount == 2) {
                                $("#progress2").fadeOut();
                            }
                            if (uploadCount == 3) {
                                $("#addPackage1").removeAttr("disabled");
                                $("#fileUp1").fadeOut();
                            }
                            $(".editedfilesList").append("<label>" + file + "</label>");
                        }
                    },
                    onDelete: function (id) {
                        window.isFileUploadedInEditPage = false;
                        $("#addPackage1").prop("disabled", true);
                        $("#filevalidationtext1").hide();
                        this.reset();
                        $("#files1").html('');
                        $("#filevalidationtext1").html('');
                        $('#upload1').unbind('click');
                        $("#fileupload1").removeAttr('disabled');
                        uploadCount = 0;
                        $('#fileupload1').uploadify('cancel', '*');
                        $(".editedfilesList").html("");
                        $("#fileUp1").fadeIn();
                        $(".clearFilesDiv1,.editedfilesList").fadeOut();
                        setTimeout(function () {
                            $(".qq-upload-button input").prop("disabled", false);
                            $(".qq-upload-button").removeClass("disabled");
                        }, 500);
                        uploadCount = 0;
                    },
                    onError: function (event, id, name, errorReason, xhrOrXdr) {
                        window.isFileUploadedInEditPage = false;
                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage1').css('cursor','pointer'); 
                        $("#filevalidationtext1").show().html(name).focus();
                    },
                    onComplete: function (id, file) {
                        window.isFileUploadedInEditPage = true;
                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage1').css('cursor','pointer'); 
                        onUploadEditSubmit(file, AWSACCESS, AWSSECRET, AWSBUCKET, AWSREGION);
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

    } else if (pform == "windows" || pform == "mac" || pform == "linux") {

        if ($('#nhrep1').is(':checked')) {

            var manualUploader = new qq.FineUploader({
                element: document.getElementById('fine-uploader-manual-trigger1'),
                template: 'qq-template-manual-trigger',
                multiple : false,
                callbacks: {
                    onSubmit: function (id, file) {
                        
                        var newFile = changeUploadFileName(file);
                        this.setName(id, newFile);
                        
                        window.uploadCdnFtpUploadInProgress = true;
                        $('#addPackage1').css('cursor','wait'); 
                        $("#filevalidationtext1").hide();
                        $("#addPackage1").prop("disabled", true);
                        $(".editedfilesList").fadeIn();
                        uploadCount == 0;

                        if ($('#nhrep1').is(':checked')) {

                            uploadCount++;

                        }
                        if (uploadCount == 1) {

                            fileName = file;
                            splitFile = fileName.split('.');
                            removeExt = splitFile[splitFile.length - 1];
                            removedFileExtension = fileName.replace('.' + removeExt, '');
                            $("#files1").append("<li class='selectedFile1'>" + file + "</li>");
                            $('input[type=text]').prev().parent().removeClass('is-empty');
                            $("#packName1").val(removedFileExtension);
                            $("#packName1").attr("title",removedFileExtension);
                            $("#filename1").val(file);

                        }

                        if (uploadCount >= 1) {
                            setTimeout(function () {
                                $(".qq-upload-button input").prop("disabled", true);
                                $(".qq-upload-button").addClass("disabled");
                            }, 500);
                        }
                        
                        $("#filename1").val(newFile);
                    },
                    onProgress: function (id, file, totalBytesUploaded, totalBytesTotal) {
                        $("#filevalidationtext1").hide();
                        if (totalBytesUploaded >= totalBytesTotal) {
                            $("#addPackage1").removeAttr("disabled");
                            $("#upStatus1").val("1");
                            $(".removeDiv").fadeIn();
                            $("#fileUp1").fadeOut();
                            $(".editedfilesList").append("<label>" + file + "</label>");
                        }
                    },
                    onDelete: function (id) {
                        window.isFileUploadedInEditPage = false;
                        $("#addPackage1").prop("disabled", true);
                        $("#filevalidationtext1").hide();
                        this.reset();
                        $("#files1").html('');
                        $("#filevalidationtext1").html('');
                        $('#upload1').unbind('click');
                        $("#fileupload1").removeAttr('disabled');
                        uploadCount = 0;
                        $('#fileupload1').uploadify('cancel', '*');
                        $(".editedfilesList").html("");
                        $("#fileUp1").fadeIn();
                        $(".clearFilesDiv1,.editedfilesList").fadeOut();
                        setTimeout(function () {
                            $(".qq-upload-button input").prop("disabled", false);
                            $(".qq-upload-button").removeClass("disabled");
                        }, 500);
                        uploadCount = 0;
                    },
                    onError: function (event, id, name, errorReason, xhrOrXdr) {
                        window.isFileUploadedInEditPage = false;
                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage1').css('cursor','pointer'); 
                        $("#filevalidationtext1").show().html(name).focus();
                    },
                    onComplete: function (id, file) {
                        window.isFileUploadedInEditPage = true;
                        window.uploadCdnFtpUploadInProgress = false;
                        $('#addPackage1').css('cursor','pointer'); 
                        onUploadEditSubmit(file, AWSACCESS, AWSSECRET, AWSBUCKET, AWSREGION);
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

// CDN FILE UPLOAD FUNCTIONALITY	

function onUploadEditSubmit(file, access, secret, bucket, region) {
    var FILENAME = btoa(file);
    var AWSACCESS = btoa(access);
    var AWSSECRET = btoa(secret);
    var AWSBUCKET = btoa(bucket);
    var AWSREGION = btoa(region);
    var url = "../softdist/upload.php?file=" + FILENAME + '&access=' + AWSACCESS + '&secret=' + AWSSECRET + '&bucket=' + AWSBUCKET + '&region=' + AWSREGION+"&csrfMagicToken=" + csrfMagicToken;

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            csrfMagicToken
        },
        async: true,
        success: function (data) {
//            setTimeout(function() {
//                $.ajax({
//                    url: "../swd/upload.php?delFile=" + filename,
//                    type: 'POST',
//                    data: "",
//                    async: true,
//                    success: function(data) {
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

$("#uploadFile1").click(function () {

    $("#iconUp1").hide();

});
function changeText(fileval, targetid) {

    var inputval = fileval.value;

}
var fileChanged = 'No';
var uploadCount = 0;

//    function editPatch() {
//        $('#addPackage1').on('click', function() {
//            editsubmitHandle();
//        });
//    }

function selectFilefromRepository(obj) {

    $("input[type=checkbox]:checked.repositoryList_checkbox1").each(function (i) {
        $(this).removeAttr('checked');
    });

    selectedVal = $(obj).val();

    var removedExtension = removeExtension(selectedVal);
    $("#packName1").val(removedExtension);
    $("#packName1").attr("title",removedExtension);
    $("#filename1").val(selectedVal);
    $('#packName1').prev().parent().removeClass('is-empty');
    $(obj).prop('checked', true);

}
$(document).ready(function () {

    var fileList = new Array();
    var click = 0;

    $('#uploadFile1').on("click", function () {

        $("#files1").html('');
        $('.uploadbuttonDiv1').show();
        $(".CdnFilesList1").hide();
        $("#fileUp1").show();

    });

    $('.preinstcheck1').on("click", function () {

        var id = $(this).attr('id');
        var platformpi = $("#platform1").val();
        if (id == 'pfile1') {
            $('#' + id).val('0');
            $("#distributionPreCheckDiv1").show();
            $('#pValue1,#pRegName1').parents('div.form-group').hide();
            $('#edit-type-wrap').hide();
        } else if (id == 'pSoftware1') {
            $('#' + id).val('1');
            $("#distributionPreCheckDiv1").hide();
            $('#pValue1,#pRegName1').parents('div.form-group').hide();
            $('#edit-type-wrap').hide();
        } else if (id == 'pRegistry1') {
            $("#distributionPreCheckDiv1").show();
            $('#' + id).val('2');
        } else if (id == 'pPatch1') {
            $('#' + id).val('3');
        }

        $('.preinstcheckFields1').hide();
        $('.' + id).show();
        setTimeout(function () {
            if (platformpi == 'mac' || platformpi == 'linux') {
                $("#pSoftwareKBDiv1,#pSoftwareSPDiv1").hide();
                $("#distributionPreCheckDiv1").hide();
            }
        }, 100);
    });

    $('#nhrep1').on("click", function () {

        $('.shared1,.accessCLass1').fadeOut();
        $('.nhrep1').fadeIn();

    });

    $('#sfolder1').on("click", function () {

        $('.shared1,.accessCLass1').fadeIn();
        $('.nhrep1').fadeOut();

    });

//        $('#otrep0').on("click", function() {
//            $('.nhrep1').fadeOut();
//            $('.accessCLass1').show();
//        });

    var pl = $("#platform1").val();

    if (pl == 'android') {
        $(".FileName1").hide();
    }

    $("#clearFiles1").on("click", function () {

        $("#files1").html('');
        $("#filevalidationtext1").html('');
        $('#upload1').unbind('click');
        $("#fileupload1").removeAttr('disabled');
        uploadCount = 0;
        $('#fileupload1').uploadify('cancel', '*');
        $(".editedfilesList").html("");
        $("#fileUp1").fadeIn();
        $(".clearFilesDiv1,.editedfilesList").fadeOut();

    });

    $('#distribute1').change(function () {

        var dpath = $(this).val();

        if (dpath == 1) {

            $(".distributionPath1,.distributionTime1,.distributionValidPath1,.distributionPreCheck1,.preDisCheckClass1,.preinstcheckFields1").hide();
            $(".preinstcheck1,#preDisCheck1").removeAttr('checked');
            $(".preinstcheck1").val('');
            $("#dPath1").val('');
            $("#dTime1").val('');
            $("#dvPath1").val('');
            $(this).val(0);
            $('#preDisCheck1').val(0);
            $(".preinstcheckFields1").find('input').val('');

        } else {

            $(this).val(1);
            $(".distributionPath1,.distributionTime1,.distributionValidPath1,.preDisCheckClass1").show();

        }

    });

    $("#preDisCheck1").on("click", function () {

        var preDisVal = $(this).val();

        if (preDisVal == 0) {

            $(this).val(1);
            //  $("#preDisCheck1").prop('checked', true);
            $(".distributionPreCheck1").show();

        } else {

            $(this).val(0);
            $(".distributionPreCheck1,.preinstcheckFields1").hide();
            $(".preinstcheckFields1").find('input').val('');
            $(".preinstcheck1").removeAttr('checked');

        }
    });

    $("#types1").change(function () {

        $("#files1").html('');

        if ($(this).val() == 'file') {

            $(".selectfile1").html('<span>Upload File</span>');

        } else {

            $(".selectfile1").html('<span>Upload Folder</span>');

        }

    });

    $('.remove1').on("click", function () {

        $('.showfile1').hide();
        fileChanged = 'yes';
        $("#files1").html('');
        $('#packName1').val('');
        $('#iconName1').val('');
        $('.configureSource1').show();
        $("#uStatus1").val('Initiated');

    });

});

$('#sdnSelect1').on("click", function () {

    $('.uploadbuttonDiv1').hide();
    $("#iconUp1").hide();
    $(".CdnFilesList1").show();
    $("#files1").html('');
//    $("#fileUp1").hide();

    var pl = $("#platform1").val();

    $("#loaderRepository1").show();

    $.ajax({
        url: 'SWD_Function.php',
        type: 'GET',
        async: true,
        data: {function: "editFn", platform: pl,csrfMagicToken: csrfMagicToken},
        success: function (data) {

            $("#loaderRepository1").hide();
            $("#files1").html(data);

        }
    });

});

