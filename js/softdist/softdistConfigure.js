$(document).ready(function () {

    $("#ppreInsCheck").on("click", function () {

        var preDisVal = $(this).val();

        if (preDisVal == 0) {

            $(this).val(1);
            $(".insPreCheck").show();
            setTimeout(function () {
                $("#distributionPreCheckDivCP").hide();
            }, 200);

        } else {

            $(this).val(0);
            $(".insPreCheck,.preinstcheckFields").hide();
            $(".preinstcheckFields").find('input').val('');
            $(".preinstcheck").removeAttr('checked');

        }
    });

    $("#distributeNow").click(function () {
        $('#executeNow').prop('checked',false);
        $("#edconfig").val('');
        var dval = $(this).val();
        var deId = $('#distId').val();

        if (dval == "0") {
            $("#distributeNow").val("1");
            dval = 1;
        } else {
            $("#distributeNow").val("0");
            $("#edconfig").hide();
            $("#executeType").hide();
            $("#retryopt").hide();
            dval = 0;
        }

        var exeval =$("#executeNow").val();
        if (dval == "1") {
            var functioncall = 'getConfigDistribute';
        } else if (dval == "0") {
            var functioncall = 'getConfigexecute';
        } else {
            return;
        }

        $.ajax({
            type: "GET",
            url: '../softdist/SWD_Function.php',
            data: {function: functioncall, id: deId,csrfMagicToken: csrfMagicToken},
            success: function (response) {

                var response = $.trim(response);
//                $("#edconfig").show();
//                $("#executeType").show();
//                $("#retryopt").show();
                $("#edconfig").val(response);
            }
        });
    });


    $("#executeNow").click(function () {
        $('#distributeNow').prop('checked',false);
        $("#edconfig").val('');
        var exeval = $(this).val();
        var deId = $('#distId').val();

        if (exeval == "0") {

            $("#executeNow").val("1");
            exeval = 1;
        } else {

            $("#executeNow").val("0");
            $("#edconfig").hide();
            $("#executeType").hide();
            $("#retryopt").hide();
            exeval = 0;
        }

        var dval = $("#distributeNow").val();
      if (exeval == "0") {
            var functioncall = 'getConfigDistribute';
        } else if (exeval == "1") {

            var functioncall = 'getConfigexecute';
        } else {

            return;
        }

        $.ajax({
            type: "GET",
            url: '../softdist/SWD_Function.php',
            data: {function: functioncall, id: deId,csrfMagicToken: csrfMagicToken},
            success: function (response) {

                var response = $.trim(response);
                $("#edconfig").show();
                $("#executeType").show();
                $("#retryopt").show();
                $("#edconfig").val(response);
            }
        });
    });
});

$('#distributeNowYes').click(function(){
    $('#distributeNowNo').prop('checked',false);
    if($('#executeNowYes').is(':checked')){
        var eValyes = '1';
    }else{
        var eValyes = '0';
    }
    var dval = 1;
    if(dval == '1' && eValyes == '1'){
        var functioncall = 'getConfig';
    }else if(dval == '1' && eValyes == '0'){
        var functioncall = 'getConfigDistribute';
    }

    $("#edconfig").val('');
    var deId = $('#distId').val();
        $.ajax({
            type: "GET",
            url: '../softdist/SWD_Function.php',
            data: {function: functioncall, id: deId,csrfMagicToken: csrfMagicToken},
            success: function (response) {
                var response = $.trim(response);
                $("#edconfig").val(response);
            }
        });

});

$('#distributeNowNo').click(function(){
    $('#distributeNowYes').prop('checked',false);
});

$('#executeNowYes').click(function(){
    if($('#distributeNowYes').is(':checked')){
        var dValyes = '1';
    }else{
        var dValyes = '0';
    }
    var eval = '1';
    $('#executeNowNo').prop('checked',false);
    $("#edconfig").val('');

    if(eval == '1' && dValyes == '1'){
        var functioncall = 'getConfig';
    }else if(eval == '1' && dValyes == '0'){
        var functioncall = 'getConfigexecute';
    }

        var deId = $('#distId').val();
        $.ajax({
            type: "GET",
            url: '../softdist/SWD_Function.php',
            data: {function: functioncall, id: deId,csrfMagicToken: csrfMagicToken},
            success: function (response) {
                var response = $.trim(response);
                $("#edconfig").val(response);
            }
        });
});

$('#executeNowNo').click(function(){
    $('#executeNowYes').prop('checked',false);
});


//**FTP/CDN POPUP Start**
function ftpcdnConfig(userEmail) {
    rightContainerSlideOn('rsc-ftp-cdn-configuration');
    //$("#ftpcdn_Conf").modal('hide');
    var sType = $("#searchType").val();

    if (sType == "ServiceTag") {
        setTimeout(function () {
            $("#ftpcdn_Conf").modal('hide');
        }, 1);
        $('#warning').modal('show');
        $("#normError").show();
        $("#normError").html("<span>FTP or CDN Configuration is not allowed on machine level</span>");
        $("#mainError").hide();
    } else {
        //$("#ftpcdn_Conf").modal('show');
        var formData = new FormData();
        formData.append('function', 'getFtpCdnDataFn');
        formData.append('email', userEmail);
        formData.append("csrfMagicToken",csrfMagicToken);
        $.ajax({
            url: '../softdist/SWD_Function.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (data) {
                $('input[type=text]').prev().parent().removeClass('is-empty');
                $("#furl").val(data.ftpUrl);
                $("#fauth").val(data.ftpauth);
                if (data.ftpauth == '1') {
                    $("#fauth").prop("checked", true);
                    $(".ftpauthField").show();
                    $("#fuser").val(data.ftpUser);
                    $("#fpwd").val(data.ftpPwd);
                } else {
                    $("#fauth").prop("checked", false);
                    $(".ftpauthField").hide();
                    $("#fuser").val("");
                    $("#fpwd").val("");
                }
                $("#cdnurl").val(data.cdnUrl);
                $("#cdnAk").val(data.cdnAccessKey);
                $("#cdnSk").val(data.cdnSecretKey);
                $("#bucket").val(data.cdnBucketName);
                $("#region").val(data.cdnRegion);
            }
        });
    }

}

function ftpConfig() {

    $("#ftpError").html("");
    $("#ftpError").fadeIn(1);

    var formData = new FormData($("#ftpConfigure")[0]);
    var furl = $("#furl").val();
    var checkedValue = $('#fauth:checked').val();
    var fuser = $("#fuser").val();
    var fpwd = $("#fpwd").val();

    formData.append('function', 'saveftpconfig');
    formData.append("csrfMagicToken",csrfMagicToken);
    if (furl === "") {

        $.notify("<span>Please fill the * required fields</span>");
        setTimeout(function () {
            $("#ftpError").fadeOut(3000);
        }, 2000);


    } else if (checkedValue === "1") {
        if (fuser === "" || fpwd === "") {
            $.notify("<span>Please fill the * required fields</span>");
            setTimeout(function () {
                $("#ftpError").fadeOut(3000);
            }, 2000);


        } else {
            $.ajax({
                url: '../softdist/SWD_Function.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (data) {

                    if (data.data === "true") {

                        $("#ftpcdn_Conf").modal('hide');
                        successNotify(data.message);
                        setTimeout(function () {
                            location.reload();
                        }, 2000);
                    } else {

                        errorNotify("Error in submitting Configuration");
                        setTimeout(function () {
                            $("#ftpError").fadeOut(3000);
                        }, 2000);
                    }
                }
            });
        }

    } else {
        $.ajax({
            url: '../softdist/SWD_Function.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (data) {

                if (data.data === "true") {
                    $("#ftpcdn_Conf").modal('hide');
                    successNotify(data.message);
                    setTimeout(function () {
                        location.reload();
                    }, 2000);

                } else {

                    errorNotify("Error in submitting Configuration");
                    setTimeout(function () {
                        $("#ftpError").fadeOut(3000);
                    }, 2000);

                }
            }
        });
    }
}

function cdnConfig() {

    $("#cdnError").html("");
    $("#cdnError").fadeIn(1);

    var formData = new FormData($("#cdnConfigure")[0]);
    var cdnurl = $("#cdnurl").val();
    var cdnAk = $("#cdnAk").val();
    var cdnSk = $("#cdnSk").val();
    var bucket = $("#bucket").val();
    var region = $("#region").val();

    if (cdnurl === "" || cdnAk === "" || cdnSk === "" || bucket === "" || region === "") {

        $.notify("<span>Please fill the * required fields</span>");
        setTimeout(function () {
            $("#cdnError").fadeOut(5000);
        }, 2000);


    } else {
        formData.append('function', 'savecdnconfig');
        formData.append("csrfMagicToken",csrfMagicToken);
        $.ajax({
            url: '../softdist/SWD_Function.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (data) {

                if (data.data === "true") {

                    $("#ftpcdn_Conf").modal('hide');
                    Get_SoftwareRepositoryData();
                    rightContainerSlideClose('rsc-ftp-cdn-configuration');
                    successNotify(data.message);
                } else {

                    errorNotify("<span>Error in submitting Configuration</span>");
                    setTimeout(function () {
                        $("#cdnError").fadeOut(5000);
                    }, 2000);

                }
            }
        });
    }
}

function ftpcdnToggle(getParam) {

    var ftpButton = $('#inconf-btn-type-ftp');
    var cdnButton = $('#inconf-btn-type-cdn');

    if (getParam == 'CDN') {
        $(".ftp-config,#ftpSubmit,#ftpViewing").hide();
        $("#cdnSubmit,#cdnViewing").show();
        $(".cdn-config").fadeIn();

        cdnButton.removeClass('btn-simple btn-inselected').addClass('btn-round');
        ftpButton.removeClass('btn-round').addClass('btn-simple btn-inselected');
    } else {
        $(".cdn-config,#cdnSubmit,#cdnViewing").hide();
        $("#ftpSubmit,#ftpViewing").show();
        $(".ftp-config").fadeIn();
        ftpButton.removeClass('btn-simple btn-inselected').addClass('btn-round');
        cdnButton.removeClass('btn-round').addClass('btn-simple btn-inselected');
    }
}

$("#fauth").on('click', function () {

    var val = $(this).val();

    if (val == '1') {

        $(this).val('0');
        $(".ftpauthField").hide();
        $("#fuser,#fpwd").val('');
        $(this).removeAttr('checked');

    } else {

        $(this).val('1');
        $(".ftpauthField").show();

    }
});


function resetConfigurationBox() {
    $('#rsc-distribute-package').find('.insPreCheck,.preinstcheckFields,.valCheckFields,#rccs-fp1,.registryRow.form-group').hide();
    $('#rsc-distribute-package').find('.preEnabledCheck').show().prop('checked', false);


}

//**FTP/CDN POPUP End**

function configure(id, distribute) { //swd fix update

    rightContainerSlideClose('rsc-edit-container');
    $('#rsc-distribute-package').find('form').eq(0).trigger("reset");

    $("#confPopError").html("");
    $("#distPopError").html("");

    clearAllField();

    var id = id;
    var configPath = '';
    var params = {
        function: 'configPatchFn',
        id: id
    }
    if (distribute === 1) {
        configPath = 'SWD_Function.php';
        params.function = 'distributeConfigurationFn';
    }

    var selected = $('#selected').val();
    var package = $('#package').val();
    var session;

    resetConfigurationBox();

    $.ajax({
        url: configPath,
        type: "POST",
        dataType: "json",
        data: params+"&csrfMagicToken=" + csrfMagicToken,
        async: true,
        success: function (data) {

            session = data.data.sessionD;

            if (distribute == 1 || distribute == '1') {

                rightContainerSlideOn('rsc-distribute-package');
                //$("#distPackagePop").modal("show");
                $('input[type=text]').prev().parent().removeClass('is-empty');
                $("#selD").val(data.data.packageIdD);
                $("#packNameD").val(data.data.packageNameD);
                $("#configStatD").val(data.data.configStatD);
                $("#pathExec2").val(data.data.executePathD);
                $("#cmdLine2").val(data.data.cmdLinesD);

                if (session == undefined || session == null || isNaN(session)) {
                    session = '1';
                }
                $('#session2').val(session).change();
                $('#session2').selectpicker("refresh");

                if (data.data.runasD == "6") {

                    $('#runas2').val(data.data.runasD).change();

                } else if (data.data.runasD == "1") {

                    $('#runas2').val(data.data.runasD).change();

                } else if (data.data.runasD == "4") {

                    $('#runas2').val(data.data.runasD).change();

                } else {

                    $('#runas2').val("1").change();

                }
                if (data.data.validationD == "1" || data.data.validationD == "0") {

                    $('#validityCheck2').prop("checked", true);
                    $(".validationRow").show();

                    if (data.data.validationD == "1") {

                        $(".registryRow").show();
                        $("#registryRow").prop("checked", true);
                        $("#rootKey12").val(data.data.rootKeyD).change();
                        $("#subKey12").val(data.data.subKeyD);
                        $("#vRegNameDE").val(data.data.vRegName);
                        $("#vTypeDE").val(data.data.vType).change();
                        $("#vValueDE").val(data.data.vValue);

                    } else if (data.data.validationD == "0") {

                        $(".fileRow").show();
                        $("#fileRow").prop("checked", true);
                        $("#filePath12").val(data.data.validationFilePathD);

                    }

                } else {

                    $('#validityCheck2').prop("checked", false);
                    $(".validationRow").hide();

                }
                if (data.data.enableMessageD == "1") {

                    $("#enablemsg2").val("1");
                    $("#enablemsg2").prop("checked", true);
                    $('.msgtxt').show();
                    $("#msgtext12").val(data.data.messageTextD);

                } else if (data.data.enableMessageD == "0") {

                    $("#enablemsg2").val("0");
                    $("#enablemsg2").prop("checked", false);
                    $("#msgtext12").val("");
                    $('.msgtxt').hide();

                } else {

                    $("#enablemsg2").val("0");
                    $("#enablemsg2").prop("checked", false);
                    $('.msgtxt').hide();
                    $("#msgtext12").val("");

                }
//                if (data.data.preInstallDD != "") {
//
//                    $("#preInsCheck2").val("1");
//                    $("#preInsCheck2").prop("checked", true);
//
//                } else {
//
//                    $("#preInsCheck2").val("0");
//                    $("#preInsCheck2").prop("checked", false);
//
//                }
                if (data.data.preInstallD == "") {

                    $("#preInsCheck2").val("0");
                    $("#preInsCheck2").prop("checked", false);

                    $(".insPreCheck").hide();

                } else if (data.data.preInstallD == "0" && data.data.filePathD != "") {

                    $("#preInsCheck2").val("1");
                    $("#preInsCheck2").prop("checked", true);

                    $(".insPreCheck,#distributionPreCheckDivDE").show();
                    $("#pfile2").prop("checked", true);
                    $(".pfile2").show();
                    $("#pfilePath22").val(data.data.filePathD);
                    if (data.data.pExecPreCheckVal == "0") {
                        $("#pExecPreCheckValDE0").prop("checked", true);
                    } else if (data.data.pExecPreCheckVal == "1") {
                        $("#pExecPreCheckValDE1").prop("checked", true);
                    }

                } else if (data.data.preInstallD == "1" && data.data.softwareNameD != "" && data.data.softwareVersionD != "" && data.data.knowledgeBaseDD != "" && data.data.servicePackD != "") {

                    $("#preInsCheck2").val("1");
                    $("#preInsCheck2").prop("checked", true);
                    $(".insPreCheck").show();
                    $("#pSoftware2").prop("checked", true);
                    $(".pSoftware2").show();
                    $("#pSoftName22").val(data.data.softwareNameD);
                    $("#pSoftVer22").val(data.data.softwareVersionD);
                    $("#pKb22").val(data.data.knowledgeBaseDD);
                    $("#pServicePack22").val(data.data.servicePackD);
                    $("#distributionPreCheckDivDE").hide();

                } else if (data.data.preInstallD == "2" && data.data.prootKeyD != "" && data.data.psubKeyD != "") {

                    $("#preInsCheck2").val("1");
                    $("#preInsCheck2").prop("checked", true);

                    $(".insPreCheck,#distributionPreCheckDivDE").show();
                    $("#pRegistry2").prop("checked", true);
                    $(".pRegistry2").show();
                    $("#prootKey22").val(data.data.prootKeyD).change();
                    $("#psubKey22").val(data.data.psubKeyD);
                    $("#pRegNameDE").val(data.data.pRegName);
                    $("#pTypeDE").val(data.data.pType).change();
                    $("#pValueDE").val(data.data.pValue);
                    if (data.data.pExecPreCheckVal == "0") {
                        $("#pExecPreCheckValDE0").prop("checked", true);
                    } else if (data.data.pExecPreCheckVal == "1") {
                        $("#pExecPreCheckValDE1").prop("checked", true);
                    }

                }

            } else if (distribute == 0 || distribute == '0') {

                rightContainerSlideOn('rsc-config');
                //$("#configPop").modal("show");
                $('input[type=text]').prev().parent().removeClass('is-empty');
                $("#sele").val(data.data.packageIdC);
                $("#packNamed").val(data.data.packageNameC);
                $("#configStat").val(data.data.configStatC);
                $("#bit32").val(data.data.bit32C);
                $("#bit64").val(data.data.bit64C);

                if (data.data.sourceTypeC != "3") {

                    $("#bit32").attr("readonly");
                    $("#bit64").attr("readonly");

                }
                if (data.data.sessionC == "1") {

                    $('#session').val(data.data.sessionC).change();

                } else if (data.data.sessionC == "0") {

                    $('#session').val(data.data.sessionC).change();

                } else {

                    $('#session').val("1").change();

                }
                if (data.data.runasC == "6") {

                    $('#runas').val(data.data.runasC).change();

                } else if (data.data.runasC == "1") {

                    $('#runas').val(data.data.runasC).change();

                } else if (data.data.runasC == "4") {

                    $('#runas').val(data.data.runasC).change();

                } else {

                    $('#runas').val("1").change();

                }
                if (data.data.cmdSettingC != '1') {

                    $("#cmdLineSetting").prop("checked");
                    $("#cmdLineSetting").val(data.data.cmdSettingC);
                    //$('.cmdLine').show();

                } else if (data.data.cmdSettingC == "0") {

                    $("#cmdLineSetting").prop("checked", false);
                    $("#cmdLineSetting").val(data.data.cmdSettingC);
                    $('.cmdLine').hide();

                } else {

                    $("#cmdLineSetting").attr("checked", "check");
                    $("#cmdLineSetting").val("1");
                    $('.cmdLine').show();

                }
                if (data.data.enableMessageC == "1") {

                    $("#enablemsg").val(data.data.enableMessageC);
                    $("#enablemsg").attr("checked", 'check');
                    $('.msgtxt').show();

                } else if (data.data.enableMessageC == "0") {

                    $("#enablemsg").val(data.data.enableMessageC);
                    $("#enablemsg").removeAttr("checked");
                    $('.msgtxt').hide();

                } else {

                    $("#enablemsg").val("0");
                    $("#enablemsg").removeAttr("checked");
                    $('.msgtxt').hide();

                }

                $("#cmdLine").val(data.data.cmdLinesC);
                $("#pposKey").val(data.data.posKeywordsC);
                $("#pnegKey").val(data.data.negKeywordsC);
                $("#msgtext").val(data.data.messageTextC);
                $("#maxtime").val(data.data.maxTimeC);
                $("#defaultRead").val(data.data.defaultReadC);
                $("#logfiles").val(data.data.logFilesToReadC);
                $("#pprocesskill").val(data.data.processToKillC);

                $("#deletelog").val("0").prop("checked", false).removeAttr("checked"); // reset

                if (data.data.deleteLogFileC == "1") {
                    $("#deletelog").val("1").prop("checked", true).attr("checked", "checked");
                }

                if (data.data.preInstallCC != "") {

                    $("#ppreInsCheck").val("1");
                    $("#ppreInsCheck").attr("checked", "check");
                    if (data.data.preInstallC == "") {

                        $(".insPreCheck").hide();
                        $("#distributionPreCheckDivCP").hide();

                    } else if (data.data.preInstallC == "0") {

                        $(".insPreCheck").show();
                        $("#ppfile").attr("checked", "check");
                        $(".ppfile").show();
                        $("#distributionPreCheckDivCP").show();
                        if (data.data.pExecPreCheckVal == 0) {
                            $("#pExecPreCheckValCP0").prop("checked", true);
                        } else if (data.data.pExecPreCheckVal == 1) {
                            $("#pExecPreCheckValCP1").prop("checked", true);
                        }

                    } else if (data.data.preInstallC == "1") {

                        $(".insPreCheck").show();
                        $("#ppSoftware").attr("checked", "check");
                        $(".ppSoftware").show();
                        $("#distributionPreCheckDivCP").hide();

                    } else if (data.data.preInstallC == "2") {

                        $(".insPreCheck").show();
                        $("#ppRegistry").attr("checked", "check");
                        $(".ppRegistry").show();
                        $("#distributionPreCheckDivCP").show();
                        if (data.data.pExecPreCheckVal == 0) {
                            $("#pExecPreCheckValCP0").prop("checked", true);
                        } else if (data.data.pExecPreCheckVal == 1) {
                            $("#pExecPreCheckValCP1").prop("checked", true);
                        }
                        $("#pRegNameCP").val(data.data.pRegName);
                        $("#pTypeCP").val(data.data.pType).change();
                        $("#pValueCP").val(data.data.pValue);

                    }

                } else {

                    $("#ppreInsCheck").val("0");
                    $("#ppreInsCheck").removeAttr("checked");

                }


                $('#prootKey').val(data.data.rootKeyC).change();
                $("#ppfilePath").val(data.data.filePathC);
                $("#ppSoftName").val(data.data.softwareNameC);
                $("#ppSoftVer").val(data.data.softwareVersionC);
                $("#ppKb").val(data.data.knowledgeBaseCC);
                $("#ppServicePack").val(data.data.servicePackC);
                $("#psubKey").val(data.data.subKeyC);

            } else {

                return false;

            }
            $(".selectpicker").selectpicker("refresh");
        }
    });
}

function MACconfigPatch(id, distribute) {

    rightContainerSlideClose('rsc-edit-container');
    $('#MACconfigForm').trigger("reset");

    $.ajax({
        url: "SWD_Function.php",
        data: {id: id, 'function': 'MACconfigPatchFn', 'csrfMagicToken': csrfMagicToken},
        dataType: "JSON",
        type: "GET",
        success: function (data) {
            rightContainerSlideOn('rsc-mac-config');
            $("#MACconfigPop").modal("show");
            $("#seleMC").val(id);
            $("#packNamedMC").val(data.data.packageName);
            $("#configStatMC").val(data.data.isConfigured);
            if (data.data.sourceType == "2") {
                $("#pathExecMCDiv,#validityCheckMCDiv").show();
                $("#pathUrlMCDiv,#preInsCheckMCDiv").hide();
                $("#pathExecMC").val(data.data.pathorurl);
                if (data.data.validation == "0") {
                    $("#validityCheckMC").prop("checked", true);
                    $("#insPreCheckMCDiv").show();
                    $("#validityMC").prop("checked", true);
                    $("#filePathMC").val(data.data.validationFilePath);
                } else if (data.data.validation == "1") {
                    $("#validityCheckMC").prop("checked", true);
                    $("#insPreCheckMCDiv").show();
                    $("#validityMCSoftware").prop("checked", true);
                    $('#filePathMCDiv').hide();
                    $('.vSoftwareMC').show();
                    $('#vSoftNameMC').val(data.data.validationFilePath);
                } else {
                    $("#insPreCheckMCDiv,#filePathMCDiv").hide();
                    $("#validityCheckMC").prop("checked", false);
                }
                if (data.data.preInstall == "0") {

                }
            } else if (data.data.sourceType == "3") {
                $("#pathUrlMCDiv,#validityCheckMCDiv").show();
                $("#pathExecMCDiv,#preInsCheckMCDiv").hide();
                $("#pathUrlMC").val(data.data.pathorurl);
                if (data.data.validation == "0") {
                    $("#validityCheckMC").prop("checked", true);
                    $("#insPreCheckMCDiv").show();
                    $("#validityMC").prop("checked", true);
                    $("#filePathMC").val(data.data.validationFilePath);
                } else if (data.data.validation == "1") {
                    $("#validityCheckMC").prop("checked", true);
                    $("#insPreCheckMCDiv").show();
                    $("#validityMCSoftware").prop("checked", true);
                    $('#filePathMCDiv').hide();
                    $('.vSoftwareMC').show();
                    $('#vSoftNameMC').val(data.data.validationFilePath);
                } else {
                    $("#insPreCheckMCDiv,#filePathMCDiv").hide();
                    $("#validityCheckMC").prop("checked", false);
                }
            }

            if (data.data.preInstall == "0") {
                $("#preInsCheckMC").prop("checked", true);
                $("#preinstcheckMCDiv,.pfileMC").show();
                $(".pSoftwareMC").hide();
                $("#pfileMC").prop("checked", true);
                $("#pfilePathMC").val(data.data.pValidationFilePath);
            } else if (data.data.preInstall == "1") {
                $("#preInsCheckMC").prop("checked", true);
                $("#preinstcheckMCDiv,.pSoftwareMC").show();
                $(".pfileMC").hide();
                $("#pSoftwareMC").prop("checked", true);
                $("#pSoftNameMC").val(data.data.softwareName);
                $("#pSoftVerMC").val(data.data.softwareVersion);
            } else {
                $("#preInsCheckMC").prop("checked", false);
                $("#preinstcheckMCDiv,.pfileMC,.pSoftwareMC").hide();
            }
            $("#preInsCheckMCDiv").show();
        }
    });
}

$("#validityCheckMC").click(function () {
    if ($(this).is(":checked")) {
        $("#insPreCheckMCDiv").show();
    } else {
        $("#insPreCheckMCDiv,#filePathMCDiv,.vSoftwareMC").hide();
        $("#validityMC").prop("checked", false);
        $("#filePathMC").val("");
    }
});

$("#validityMC").click(function () {
    if ($(this).is(":checked")) {
        $("#filePathMCDiv").show();
        $(".vSoftwareMC").hide();
    } else {
        $("#filePathMCDiv").hide();
        $("#filePathMC").val("");
        $('.vSoftwareMC').show();
    }
});

$("#validityMCSoftware").click(function () {
    if ($(this).is(":checked")) {
        $("#filePathMCDiv").hide();
        $(".vSoftwareMC").show();
    } else {
        $(".vSoftwareMC").hide();
        $('#vSoftNameMC').val("");
        $('#vSoftVerMC').val("");
    }


});

$("#preInsCheckMC").click(function () {
    if ($(this).is(":checked")) {
        $("#preinstcheckMCDiv").show();
    } else {
        $("#preinstcheckMCDiv,.pfileMC,.pSoftwareMC").hide();
        $("#pfileMC,#pSoftwareMC").prop("checked", false);
        $("#pfilePathMC,#pSoftNameMC,#pSoftVerMC").val("");
    }
});

$(".preinstcheckMCC").click(function () {
    if ($(this).val() == "0") {
        $(".pfileMC").show();
        $(".pSoftwareMC").hide();
        $("#pSoftNameMC,#pSoftVerMC").val("");
    } else if ($(this).val() == "1") {
        $(".pfileMC").hide();
        $(".pSoftwareMC").show();
        $("#pfilePathMC").val("");
    }
});

function MACconfigPatchSubmit() {
    var selId = $("#seleMC").val();
    $("#pathExecMCMsg").show();
    $("#pathExecMCMsg").html('');

    if ($('#pathExecMC').val() == '' && $('#pathUrlMC').val() == '') {
        $('#pathExecMCMsg').html('Please enter the mandatory field');
        setTimeout(function () {
            $("#pathExecMCMsg").fadeOut(3000);
        }, 2000);
        return false;
    }

    var formData = $("#MACconfigForm").serialize();
    formData.function = 'MACconfigPatchSubmitFn';
    formData.id = selId;
    formData.csrfMagicToken= csrfMagicToken;

    $.ajax({
        url: "SWD_Function.php",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (data) {
            if ($.trim(data) === 'success') {
                Get_SoftwareRepositoryData(); // swd fix updates
                rightContainerSlideClose('rsc-mac-config');
                successNotify("Successfully updated software configuration");
            }
        }
    });
}

function seeEnableMsgCheck() {
    if ($("#enablemsg2").is(":checked")) {
        $("#enablemsg2").val("1");
        $(".msgtxt").show();
        $("#msgtext12").val("");
    } else {
        $("#enablemsg2").val("0");
        $(".msgtxt").hide();
    }
}

$("#registryRow").on("click", function () {

    $(".fileRow").hide();
    $("#filePath12").val('');
    $(".registryRow").show();

});

$("#fileRow").on("click", function () {

    $(".registryRow").hide();
    $("#subKey12").val('');
    $(".fileRow").show();

});

$("#registryRow_32exec").on("click", function () {

    $(".fileRow_32exec").hide();
    $("#filePath12_32exec").val('');
    $(".registryRow_32exec").show();

});

$("#fileRow_32exec").on("click", function () {
    $(".registryRow_32exec").hide();
    $("#subKey12_32exec").val('');
    $(".fileRow_32exec").show();

});



$('.enablemsg').on("click", function () {

    if ($(this).prop("checked") === true) {

        $('.msgtxt').show();

    } else {

        $("#msgtext").val('');
        $("#msgtext12").val('');
        $('.msgtxt').hide();

    }
});

$('#cmdLineSetting').on("click", function () {

    if ($(this).prop("checked") === true) {

        $('.cmdLine').show();

    } else {

        $("#cmdLine").val('');
        $('.cmdLine').hide();

    }
});

$('#cmdLineSetting').change(function () {

    var cval = $(this).val();

    if (cval == 1) {

        $(this).val(0);

    } else {

        $(this).val(1);

    }
});


$('.preinstcheck').on("click", function () {

    var id = $(this).attr('id');

    if (id == 'ppfile') {

        $('#' + id).val('0');
        $("#distributionPreCheckDivCP").show();

    } else if (id == 'ppSoftware') {

        $('#' + id).val('1');
        $("#distributionPreCheckDivCP").hide();

    } else if (id == 'ppRegistry') {

        $('#' + id).val('2');
        $("#distributionPreCheckDivCP").show();

    } else if (id == 'ppPatch') {

        $('#' + id).val('3');
        $("#distributionPreCheckDivCP").hide();

    }

    $('.preinstcheckFields').hide();
    $('.' + id).show();

});

$('#deletelog').change(function () {

    var deletelog = $(this).val();

    if (deletelog == 1) {

        $(this).val(0);

    } else {

        $(this).val(1);

    }
});

$('#enablemsg').change(function () {

    var enablemsg = $(this).val();

    if (enablemsg == 1) {

        $(this).val(0);

    } else {

        $(this).val(1);

    }
});

$('#ppreinst').change(function () {

    var preinst = $(this).val();

    if (preinst == 1) {

        $(this).val(0);

    } else {

        $(this).val(1);

    }
});


function distributePackage(){
    var ostype = $('#configostypeid').val();
    // if(ostype == 'windows'){
        distributePackageSubmit();
    // }else{
    //     distributePackageSubmit_old();
    // }
}

function distributePackageSubmit_old() {

    $("#distPopError").fadeIn();
    $("#pathExec2Msg").fadeIn();
    $("#filePath12Msg").fadeIn();
    $("#subKey12Msg").fadeIn();
    $("#msgtext12Msg").fadeIn();
    $("#pfilePath22Msg").fadeIn();
    $("#pSoftName22Msg").fadeIn();
    $("#pSoftVer22Msg").fadeIn();
    $("#pKb22Msg").fadeIn();
    $("#pServicePack22Msg").fadeIn();
    $("#psubKey22Msg").fadeIn();
    $("#pre12Msg").fadeIn();
    $("#pre22Msg").fadeIn();


    $("#pathExec2Msg").html("");
    //$("#cmdLine2Msg").html("");
    $("#filePath12Msg").html("");
    $("#subKey12Msg").html("");
    $("#msgtext12Msg").html("");
    $("#pfilePath22Msg").html("");
    $("#pSoftName22Msg").html("");
    $("#pSoftVer22Msg").html("");
    $("#pKb22Msg").html("");
    $("#pServicePack22Msg").html("");
    $("#psubKey22Msg").html("");
    $("#pre12Msg").html("");
    $("#pre22Msg").html("");

    var packageid = $('#id1').val();
    var selected = $("#selD").val();
    var exe = $("#pathExec2").val();
    var cmd = $("#cmdLine2").val();
    var filePath12 = $("#filePath12").val();
    var subKey12 = $("#subKey12").val();
    var msgtext12 = $("#msgtext12").val();
    var pfilePath22 = $("#pfilePath22").val();
    var pSoftName22 = $("#pSoftName22").val();
    var pSoftVer22 = $("#pSoftVer22").val();
    var pKb22 = $("#pKb22").val();
    var pServicePack22 = $("#pServicePack22").val();
    var psubKey22 = $("#psubKey22").val();

    //v8 update start
    if (exe == undefined || exe == "") {
        errorNotify('Executable Path is required');
        return false;
    }

    if ($('#preInsCheck2').is(':checked')) {

        if (!$('#pfile2').is(':checked') && !$('#pSoftware2').is(':checked') && !$('#pRegistry2').is(':checked')) {
            errorNotify('please select any one radio option below pre install check');
            return false;
        }

        if ($('#pfile2').is(':checked')) {
            if (pfilePath22 == "") {
                errorNotify('File Path is required');
                return false;
            }
        }

        if ($('#pSoftware2').is(':checked')) {
            if (pSoftName22 == "") {
                errorNotify('Software Name is required');
                return false;
            }
            if (pSoftVer22 == "") {
                errorNotify('Software Version name is required');
                return false;
            }
            if (pKb22 == "") {
                errorNotify('Knowledge Base is required');
                return false;
            }
            if (pServicePack22 == "") {
                errorNotify('Service Pack is required');
                return false;
            }
        }

        if ($('#pRegistry2').is(':checked')) {
            if (psubKey22 == "") {
                errorNotify('Sub Key is required');
                return false;
            }
        }
    }

    if ($('#validityCheck2').is(':checked')) {

        if (!$('#fileRow').is(':checked') && !$('#registryRow').is(':checked')) {
            errorNotify('please select any one radio option below validation field');
            return false;
        }

        if ($('#fileRow').is(':checked')) {
            if (filePath12 == "") {
                errorNotify('File Path is required');
                return false;
            }
        }

        if ($('#registryRow').is(':checked')) {
            if (subKey12 == "") {
                errorNotify('Sub key is required');
                return false;
            }
        }
    }

    if ($('#enablemsg2').is(':checked')) {
        if (msgtext12 == "") {
            errorNotify('Message Text is required');
            return false;
        }
    }

    //v8 update end
    if (exe == "") {
        if (exe == "") {
            $("#pathExec2Msg").html("<span>Executable Path is required</span>");
            setTimeout(function () {
                $("#pathExec2Msg").fadeOut(5000);
            }, 2000);
            //errorNotify('Executable Path is required');
        }

//        if (cmd == "") { $("#cmdLine2Msg").html("Command Line is  required");    $("#cmdLine2Msg").fadeOut(5000);   }

        if (filePath12 == "") {
            $("#filePath12Msg").html("<span>File Path is required</span>");
            setTimeout(function () {
                $("#filePath12Msg").fadeOut(5000);
            }, 2000);
            //errorNotify('File Path is required');
        }
        if (subKey12 == "") {
            $("#subKey12Msg").html("<span>Sub Key is required</span>");
            setTimeout(function () {
                $("#subKey12Msg").fadeOut(5000);
            }, 2000);
            //errorNotify('Sub Key is required');
        }
        if (msgtext12 == "") {
            $("#msgtext12Msg").html("<span>Message Text is required</span>");
            setTimeout(function () {
                $("#msgtext12Msg").fadeOut(5000);
            }, 2000);
            //errorNotify('Message Text is required');
        }
        if (pfilePath22 == "") {
            $("#pfilePath22Msg").html("<span>File Path is required</span>");
            setTimeout(function () {
                $("#pfilePath22Msg").fadeOut(5000);
            }, 2000);
            //errorNotify('File Path is required');
        }
        if (pSoftName22 == "") {
            $("#pSoftName22Msg").html("<span>Software Name is required</span>");
            setTimeout(function () {
                $("#pSoftName22Msg").fadeOut(5000);
            }, 2000);
            // errorNotify('Software Name is required');
        }
        if (pSoftVer22 == "") {
            $("#pSoftVer22Msg").html("<span>Software Version name is required</span>");
            setTimeout(function () {
                $("#pSoftVer22Msg").fadeOut(5000);
            }, 2000);
            //errorNotify('Software Version name is required');
        }
        if (pKb22 == "") {
            $("#pKb22Msg").html("<span>Knowledge Base is required</span>");
            setTimeout(function () {
                $("#pKb22Msg").fadeOut(5000);
            }, 2000);
            //errorNotify('Knowledge Base is required');
        }
        if (pServicePack22 == "") {
            $("#pServicePack22Msg").html("<span>Service Pack is required</span>");
            setTimeout(function () {
                $("#pServicePack22Msg").fadeOut(5000);
            }, 2000);
            //errorNotify('Service Pack is required');
        }
        if (psubKey22 == "") {
            $("#psubKey22Msg").html("<span>Sub Key is required</span>");
            setTimeout(function () {
                $("#psubKey22Msg").fadeOut(5000);
            }, 2000);
            //errorNotify('Sub Key is required');
        }
        //$("#distPopError").html("Please enter * required fields");
        //$("#distPopError").fadeOut(5000);

    } else {
        if ($('#validityCheck2').is(':checked') || $('#enablemsg2').is(':checked') || $('#preInsCheck2').is(':checked')) {
            if ($('#validityCheck2').is(':checked')) {
                if ($('#fileRow').is(':checked') || $('#registryRow').is(':checked')) {
                    if ($('#fileRow').is(':checked')) {
                        if (filePath12 == "") {
                            $("#filePath12Msg").html("<span>File Path is required</span>");
                            setTimeout(function () {
                                $("#filePath12Msg").fadeOut(5000);
                            }, 2000);
                            //errorNotify('File Path is required');
                        } else {
                            var postFormData = $('#configurePatch2').serialize();
                            postFormData += "&function=saveDistributeConfigFn_old"+"&csrfMagicToken=" + csrfMagicToken;
                            $.ajax({
                                url: '../softdist/SWD_Function.php',
                                type: 'POST',
                                data: postFormData,
                                success: function (data) {
                                    //$("#distPackagePop").modal('hide');
                                    rightContainerSlideClose('rsc-distribute-package');
                                    showCofigDetail(selected, 'c');
                                    //$("#edconfPop").modal('show');
                                    rightContainerSlideOn('rsc-edit-configuration');
                                }
                            });
                        }
                    } else if ($('#registryRow').is(':checked')) {
                        if (subKey12 == "") {
                            $("#subKey12Msg").html("<span>Sub key is required</span>");
                            setTimeout(function () {
                                $("#subKey12Msg").fadeOut(5000);
                            }, 2000);
                            //errorNotify('Sub key is required');
                        } else {
                            var postFormData = $('#configurePatch2').serialize();
                            postFormData += "&function=saveDistributeConfigFn_old"+"&csrfMagicToken=" + csrfMagicToken;
                            $.ajax({
                                url: '../softdist/SWD_Function.php',
                                type: 'POST',
                                data: postFormData,
                                success: function (data) {
                                    //$("#distPackagePop").modal('hide');
                                    rightContainerSlideClose('rsc-distribute-package');
                                    showCofigDetail(selected, 'c');
                                    //$("#edconfPop").modal('show');
                                    rightContainerSlideOn('rsc-edit-configuration');
                                }
                            });
                        }
                    }
                } else {
                    if (!$('#fileRow').is(':checked') && !$('#registryRow').is(':checked')) {
                        $("#pre12Msg").html("<span>please select any one radio button</span>");
                        setTimeout(function () {
                            $("#pre12Msg").fadeOut(5000);
                        }, 2000);
                        //errorNotify('please select any one radio button');
                    }
                }
            } else if ($('#enablemsg2').is(':checked')) {
                if (msgtext12 == "") {
                    $("#msgtext12Msg").html("<span>Message Text is required</span>");
                    setTimeout(function () {
                        $("#msgtext12Msg").fadeOut(5000);
                    }, 2000);
                    //errorNotify('Message Text is required');
                } else {
                    var postFormData = $('#configurePatch2').serialize();
                    postFormData += "&function=saveDistributeConfigFn_old"+"&csrfMagicToken=" + csrfMagicToken;
                    $.ajax({
                        url: '../softdist/SWD_Function.php',
                        type: 'POST',
                        data: postFormData,
                        success: function (data) {
                            //$("#distPackagePop").modal('hide');
                            rightContainerSlideClose('rsc-distribute-package');
                            showCofigDetail(selected, 'c');
                            //$("#edconfPop").modal('show');
                            rightContainerSlideOn('rsc-edit-configuration');
                        }
                    });
                }
            } else if ($('#preInsCheck2').is(':checked')) {
                if ($('#pfile2').is(':checked') || $('#pSoftware2').is(':checked') || $('#pRegistry2').is(':checked')) {
                    if ($('#pfile2').is(':checked')) {
                        if (pfilePath22 == "") {
                            $("#pfilePath22Msg").html("<span>File Path is required</span>");
                            setTimeout(function () {
                                $("#pfilePath22Msg").fadeOut(5000);
                            }, 2000);
                            //errorNotify('File Path is required');

                        } else {
                            var postFormData = $('#configurePatch2').serialize();
                            postFormData += "&function=saveDistributeConfigFn_old"+"&csrfMagicToken=" + csrfMagicToken;
                            $.ajax({
                                url: '../softdist/SWD_Function.php',
                                type: 'POST',
                                data: postFormData,
                                success: function (data) {
                                    rightContainerSlideClose('rsc-distribute-package');
                                    //$("#distPackagePop").modal('hide');
                                    showCofigDetail(selected, 'c');
                                    //$("#edconfPop").modal('show');
                                    rightContainerSlideOn('rsc-edit-configuration');
                                }
                            });
                        }
                    } else if ($('#pSoftware2').is(':checked')) {
                        if (pSoftName22 == "" || pSoftVer22 == "" || pKb22 == "" || pServicePack22 == "") {
                            if (pSoftName22 == "") {
                                $("#pSoftName22Msg").html("<span>Software Name is required</span>");
                                setTimeout(function () {
                                    $("#pSoftName22Msg").fadeOut(5000);
                                }, 2000);
                                //errorNotify('Software Name is required');

                            }
                            if (pSoftVer22 == "") {
                                $("#pSoftVer22Msg").html("<span>Software Version name is required</span>");
                                setTimeout(function () {
                                    $("#pSoftVer22Msg").fadeOut(5000);
                                }, 2000);
                                //errorNotify('Software Version name is required');
                            }
                            if (pKb22 == "") {
                                $("#pKb22Msg").html("<span>Knowledge Base is required</span>");
                                setTimeout(function () {
                                    $("#pKb22Msg").fadeOut(5000);
                                }, 2000);
                                //errorNotify('Knowledge Base is required');
                            }
                            if (pServicePack22 == "") {
                                $("#pServicePack22Msg").html("<span>Service Pack is required</span>");
                                setTimeout(function () {
                                    $("#pServicePack22Msg").fadeOut(5000);
                                }, 2000);
                                //errorNotify('Service Pack is required');
                            }
                        } else {
                            var postFormData = $('#configurePatch2').serialize();
                            postFormData += "&function=saveDistributeConfigFn_old"+"&csrfMagicToken=" + csrfMagicToken;
                            $.ajax({
                                url: '../softdist/SWD_Function.php',
                                type: 'POST',
                                data: postFormData,
                                success: function (data) {
                                    //$("#distPackagePop").modal('hide');
                                    rightContainerSlideClose('rsc-distribute-package');
                                    showCofigDetail(selected, 'c');
                                    //$("#edconfPop").modal('show');
                                    rightContainerSlideOn('rsc-edit-configuration');
                                }
                            });
                        }
                    } else if ($('#pRegistry2').is(':checked')) {
                        if (psubKey22 == "") {
                            $("#psubKey22Msg").html("<span>Sub Key is required</span>");
                            setTimeout(function () {
                                $("#psubKey22Msg").fadeOut(5000);
                            }, 2000);
                            //errorNotify('Sub Key is required');
                        } else {
                            var postFormData = $('#configurePatch2').serialize();
                            postFormData += "&function=saveDistributeConfigFn_old"+"&csrfMagicToken=" + csrfMagicToken;
                            $.ajax({
                                url: '../softdist/SWD_Function.php',
                                type: 'POST',
                                data: postFormData,
                                success: function (data) {
                                    //$("#distPackagePop").modal('hide');
                                    rightContainerSlideClose('rsc-distribute-package');
                                    showCofigDetail(selected, 'c');
                                    //$("#edconfPop").modal('show');
                                    rightContainerSlideOn('rsc-edit-configuration');
                                }
                            });
                        }
                    }
                } else {
                    if (!$('#pfile2').is(':checked') && !$('#pSoftware2').is(':checked') && !$('#pRegistry2').is(':checked')) {
                        $("#pre22Msg").html("<span>please select any one radio button</span>");
                        setTimeout(function () {
                            $("#pre22Msg").fadeOut(5000);
                        }, 2000);
                        //errorNotify('please select any one radio button');
                    }
                }
            }
        } else {
            var postFormData = $('#configurePatch2').serialize();
            postFormData += "&function=saveDistributeConfigFn_old"+"&csrfMagicToken=" + csrfMagicToken;
            $.ajax({
                url: '../softdist/SWD_Function.php',
                type: 'POST',
                data: postFormData,
                success: function (data) {
                    rightContainerSlideClose('rsc-distribute-package');
                    //$("#distPackagePop").modal('hide');
                    showCofigDetail(selected, 'c');
                    //$("#edconfPop").modal('show');
                    rightContainerSlideOn('rsc-edit-configuration');

                }
            });
        }
    }
}

function configPatchSubmit() {


    $("#confPopError").fadeIn();
    $("#bit32Msg").fadeIn();
    $("#bit64Msg").fadeIn();
    $("#msgtextMsg").fadeIn();
    $("#maxtimeMsg").fadeIn();
    $("#ppfilePathMsg").fadeIn();
    $("#psubKeyMsg").fadeIn();
    $("#ppSoftNameMsg").fadeIn();
    $("#ppSoftVerMsg").fadeIn();
    $("#ppKbMsg").fadeIn();
    $("#ppServicePackMsg").fadeIn();
    $("#ppreInsCheckMsg").fadeIn();
    $("#confPopError").html("");
    $("#bit32Msg").html("");
    $("#bit64Msg").html("");
    $("#msgtextMsg").html("");
    $("#maxtimeMsg").html("");
    $("#ppfilePathMsg").html("");
    $("#psubKeyMsg").html("");
    $("#ppSoftNameMsg").html("");
    $("#ppSoftVerMsg").html("");
    $("#ppKbMsg").html("");
    $("#ppServicePackMsg").html("");
    $("#ppreInsCheckMsg").html("");
    var selected = $('#selected').val();
    var b32 = $('#bit32').val();
    var b64 = $('#bit64').val();
    var posk = $('#pposKey').val();
    var defr = $('#defaultRead').val();
    var msgtext = $('#msgtext').val();
    var maxt = $('#maxtime').val();
    var log = $('#logfiles').val();
    var negk = $('#pnegKey').val();
    var pkil = $('#pprocesskill').val();
    var pfilePath = $('#ppfilePath').val();
    var psubKey = $('#psubKey').val();
    var pSoftName = $('#ppSoftName').val();
    var pSoftVer = $('#ppSoftVer').val();
    var pKb = $('#ppKb').val();
    var pServicePack = $('#ppServicePack').val();

    //v8 update start

    var session = $('#session').val();
    var runas = $('#runas').val();

    if (b32 == undefined || b32 === "") {
        errorNotify("32 bit configuration is required");
        return;
    }

    if (b64 == undefined || b64 === "") {
        errorNotify("64 bit configuration is required");
        return;
    }

    if (session == undefined || session === "") {
        errorNotify("Session is required");
        return;
    }

    if (runas == undefined || runas === "") {
        errorNotify("Run as is required");
        return;
    }

    if ($('[name=enablemsg]').is(':checked')) {
        if ($('#msgtext').val() == undefined || $('#msgtext').val() === "") {
            errorNotify("Message text is required");
            return;
        }
    }

    if (maxt == undefined || maxt === "") {
        errorNotify("Max time per patch is required");
        return;
    }


    if ($('#ppreInsCheck').is(':checked')) {
        if ($('#ppfile').is(':checked') || $('#ppSoftware').is(':checked') || $('#ppRegistry').is(':checked')) {
            if ($('#ppfile').is(':checked')) {
                if (pfilePath === "") {
                    errorNotify("File Path is required");
                    return false;
                }
            } else if ($('#ppSoftware').is(':checked')) {
                if (pSoftName == "" || pSoftVer == "" || pKb == "" || pServicePack == "") {
                    if (pSoftName === "") {
                        errorNotify("Software Name is required");
                        return false;
                    }
                    if (pSoftVer === "") {
                        errorNotify("Software Version name is required");
                        return false;
                    }
                    if (pKb === "") {
                        errorNotify("Knowlwdge base is required");
                        return false;
                    }
                    if (pServicePack === "") {
                        errorNotify("Service Pack is required");
                        return false;
                    }
                }
            } else if ($('#ppRegistry').is(':checked')) {
                if (psubKey === "") {
                    errorNotify("Sub Key is required");
                    return false;
                }
            }
        } else {
            if (!$('#ppfile').is(':checked') && !$('#ppSoftware').is(':checked') && !$('#ppRegistry').is(':checked')) {
                $('#ppreInsCheck').focus();
                errorNotify("Please select any one radio button below pre install checkbox");
                return false;
            }
        }
    }

    //v8 update end

    if (b32 === "" || b64 === "" || maxt === "") {

        if (b32 === "") {
            $("#bit32Msg").html("<span>32 bit configuration is required</span>");
            setTimeout(function () {
                $("#bit32Msg").fadeOut(5000);
            }, 2000);
            //errorNotify("32 bit configuration is required");
        }
        if (b64 === "") {
            $("#bit64Msg").html("<span>64 bit configuration is required</span>");
            setTimeout(function () {
                $("#bit64Msg").fadeOut(5000);
            }, 2000);
            //errorNotify("64 bit configuration is required</span>");
        }
// $("#pposKeyError").html("Please enter * required fields");$("#pposKeyError").fadeOut(5000);$("#defaultReadError").html("Please enter * required fields");$("#defaultReadError").fadeOut(5000);
        if (maxt === "") {
            $("#maxtimeMsg").html("<span>Max time per patch is required</span>");
            setTimeout(function () {
                $("#maxtimeMsg").fadeOut(5000);
            }, 2000);
            //errorNotify("Max time per patch is required</span>");
        }
        if ($('#enablemsg').is(':checked')) {
            if (msgtext === "" || maxt === "") {
                if (msgtext === "") {
                    $("#msgtextMsg").html("<span>Message Text is required</span>");
                    setTimeout(function () {
                        $("#msgtextMsg").fadeOut(5000);
                    }, 2000);
                    //errorNotify("Message Text is required");
                }
                if (maxt === "") {
                    $("#maxtimeMsg").html("<span>Max time per patch is required</span>");
                    setTimeout(function () {
                        $("#maxtimeMsg").fadeOut(5000);
                    }, 2000);
                    //errorNotify("Max time per patch is required");
                }
            }
        }
        if ($('#ppreInsCheck').is(':checked')) {
            if ($('#ppfile').is(':checked') || $('#ppSoftware').is(':checked') || $('#ppRegistry').is(':checked')) {
                if ($('#ppfile').is(':checked')) {
                    if (pfilePath === "") {
                        $("#ppfilePathMsg").html("<span>File Path is required</span>");
                        setTimeout(function () {
                            $("#ppfilePathMsg").fadeOut(5000);
                        }, 2000);
                        //errorNotify("File Path is required");
                    }
                } else if ($('#ppSoftware').is(':checked')) {
                    if (pSoftName == "" || pSoftVer == "" || pKb == "" || pServicePack == "") {
                        if (pSoftName === "") {
                            $("#ppSoftNameMsg").html("<span>Software Name is required</span>");
                            setTimeout(function () {
                                $("#ppSoftNameMsg").fadeOut(5000);
                            }, 2000);
                            //errorNotify("Software Name is required");
                        }
                        if (pSoftVer === "") {
                            $("#ppSoftVerMsg").html("<span>Software Version name is required</span>");
                            setTimeout(function () {
                                $("#ppSoftVerMsg").fadeOut(5000);
                            }, 2000);

                        }
                        if (pKb === "") {
                            $("#ppKbMsg").html("<span>Knowlwdge base is required</span>");
                            setTimeout(function () {
                                $("#ppKbMsg").fadeOut(5000);
                            }, 2000);
                            //errorNotify("Knowlwdge base is required");
                        }
                        if (pServicePack === "") {
                            $("#ppServicePackMsg").html("<span>Service Pack is required</span>");
                            setTimeout(function () {
                                $("#ppServicePackMsg").fadeOut(5000);
                            }, 2000);
                            //errorNotify("Service Pack is required");
                        }
                    }
                } else if ($('#ppRegistry').is(':checked')) {
                    if (psubKey === "") {
                        $("#psubKeyMsg").html("<span>Sub Key is required</span>");
                        setTimeout(function () {
                            $("#psubKeyMsg").fadeOut(5000);
                        }, 2000);
                        //errorNotify("Sub Key is required");
                        return false;
                    }
                }
            } else {
                if (!$('#ppfile').is(':checked') && !$('#ppSoftware').is(':checked') && !$('#ppRegistry').is(':checked')) {
                    $("#ppreInsCheckMsg").html("<span>Please select any one radio button</span>");
                    setTimeout(function () {
                        $("#ppreInsCheckMsg").fadeOut(5000);
                    }, 2000);
                    //errorNotify("Please select any one radio button");
                }
            }
        }

    } else {
        if ($('#enablemsg').is(':checked')) {
            if (msgtext === "" || maxt === "") {
                if (msgtext === "") {
                    $("#msgtextMsg").html("<span>Message Text is required</span>");
                    setTimeout(function () {
                        $("#msgtextMsg").fadeOut(5000);
                    }, 2000);
                    //errorNotify("Message Text is required");
                }
                if (maxt === "") {
                    $("#maxtimeMsg").html("<span>Max time per patch is required</span>");
                    setTimeout(function () {
                        $("#maxtimeMsg").fadeOut(5000);
                    }, 2000);
                    //errorNotify("Max time per patch is required");
                }
                return false;
            } else {

                if ($('#ppreInsCheck').is(':checked')) {
                    if ($('#ppfile').is(':checked') || $('#ppSoftware').is(':checked') || $('#ppRegistry').is(':checked')) {
                        if ($('#ppfile').is(':checked')) {
                            if (pfilePath === "") {
                                $("#ppfilePathMsg").html("<span>File Path is required</span>");
                                setTimeout(function () {
                                    $("#ppfilePathMsg").fadeOut(5000);
                                }, 2000);
                                //errorNotify("File Path is required");
                            } else {
                                var postFormData = $('#configurePatch').serialize();
                                postFormData.function = 'getConfigFn';
                                postFormData.csrfMagicToken = csrfMagicToken;
                                $.ajax({
                                    url: '../softdist/SWD_Function.php',
                                    type: 'POST',
                                    data: postFormData,
                                    success: function (data) {
                                        //$("#configPop").modal('hide');
                                        rightContainerSlideClose('rsc-config');
                                        showCofigDetail(selected, 'c');
                                        //$("#edconfPop").modal('show');
                                        rightContainerSlideOn('rsc-edit-configuration');
                                    }
                                });
                            }
                        } else if ($('#ppSoftware').is(':checked')) {
                            if (pSoftName == "" || pSoftVer == "" || pKb == "" || pServicePack == "") {
                                if (pSoftName === "") {
                                    $("#ppSoftNameMsg").html("<span>Software Name is required</span>");
                                    setTimeout(function () {
                                        $("#ppSoftNameMsg").fadeOut(5000);
                                    }, 2000);
                                    //errorNotify("Software Name is required");
                                }
                                if (pSoftVer === "") {
                                    $("#ppSoftVerMsg").html("<span>Software Version name is required</span>");
                                    setTimeout(function () {
                                        $("#ppSoftVerMsg").fadeOut(5000);
                                    }, 2000);
                                    //errorNotify("Software Version name is required");
                                }
                                if (pKb === "") {
                                    $("#ppKbMsg").html("<span>Knowlwdge base is required</span>");
                                    setTimeout(function () {
                                        $("#ppKbMsg").fadeOut(5000);
                                    }, 2000);
                                    //errorNotify("Knowlwdge base is required");
                                }
                                if (pServicePack === "") {
                                    $("#ppServicePackMsg").html("<span>Service Pack is required</span>");
                                    setTimeout(function () {
                                        $("#ppServicePackMsg").fadeOut(5000);
                                    }, 2000);
                                    //errorNotify("Service Pack is required");
                                }
                            } else {
                                var postFormData = $('#configurePatch').serialize();
                                postFormData.function = 'getConfigFn';
                                postFormData.csrfMagicToken = csrfMagicToken;
                                $.ajax({
                                    url: '../softdist/SWD_Function.php',
                                    type: 'POST',
                                    data: postFormData,
                                    success: function (data) {
                                        //$("#configPop").modal('hide');
                                        showCofigDetail(selected, 'c');
                                        //$("#edconfPop").modal('show');
                                        rightContainerSlideOn('rsc-edit-configuration');
                                    }

                                });
                            }
                        } else if ($('#ppRegistry').is(':checked')) {
                            if (psubKey === "") {
                                $("#psubKeyMsg").html("<span>Sub Key is required</span>");
                                setTimeout(function () {
                                    $("#psubKeyMsg").fadeOut(5000);
                                }, 2000);
                                //errorNotify("Sub Key is required");
                                return false;
                            } else {
                                var postFormData = $('#configurePatch').serialize();
                                postFormData.function = 'getConfigFn';
                                postFormData.csrfMagicToken = csrfMagicToken;
                                $.ajax({
                                    url: '../softdist/SWD_Function.php',
                                    type: 'POST',
                                    data: postFormData,
                                    success: function (data) {
                                        rightContainerSlideClose('rsc-config');
                                        //$("#configPop").modal('hide');
                                        showCofigDetail(selected, 'c');
                                        //$("#edconfPop").modal('show');
                                        rightContainerSlideOn('rsc-edit-configuration');
                                    }
                                });
                            }
                        }
                    } else {
                        if (!$('#ppfile').is(':checked') && !$('#ppSoftware').is(':checked') && !$('#ppRegistry').is(':checked')) {
                            $("#ppreInsCheckMsg").html("<span>Please select any one radio button</span>");
                            setTimeout(function () {
                                $("#ppreInsCheckMsg").fadeOut(5000);
                            }, 2000);
                            //errorNotify("Please select any one radio button");
                        }
                    }
                } else {
                    var postFormData = $('#configurePatch').serialize();
                    postFormData.function = 'getConfigFn';
                    postFormData.csrfMagicToken = csrfMagicToken;
                    $.ajax({
                        url: '../softdist/SWD_Function.php',
                        type: 'POST',
                        data: postFormData,
                        success: function (data) {
                            rightContainerSlideClose('rsc-config');
                            //$("#configPop").modal('hide');
                            showCofigDetail(selected, 'c');
                            //$("#edconfPop").modal('show');
                            rightContainerSlideOn('rsc-edit-configuration');
                        }
                    });
                }
            }
        } else {
            if ($('#ppreInsCheck').is(':checked')) {
                if ($('#ppfile').is(':checked') || $('#ppSoftware').is(':checked') || $('#ppRegistry').is(':checked')) {
                    if ($('#ppfile').is(':checked')) {
                        if (pfilePath === "") {
                            $("#ppfilePathMsg").html("<span>File Path is required</span>");
                            setTimeout(function () {
                                $("#ppfilePathMsg").fadeOut(5000);
                            }, 2000);
                            //errorNotify("File Path is required");
                        } else {
                            var postFormData = $('#configurePatch').serialize();
                            postFormData.function = 'getConfigFn';
                            postFormData.csrfMagicToken = csrfMagicToken;
                            $.ajax({
                                url: '../softdist/SWD_Function.php',
                                type: 'POST',
                                data: postFormData,
                                success: function (data) {
                                    rightContainerSlideClose('rsc-config');
                                    //$("#configPop").modal('hide');
                                    showCofigDetail(selected, 'c');
                                    //$("#edconfPop").modal('show');
                                    rightContainerSlideOn('rsc-edit-configuration');
                                }
                            });
                        }
                    } else if ($('#ppSoftware').is(':checked')) {
                        if (pSoftName == "" || pSoftVer == "" || pKb == "" || pServicePack == "") {
                            if (pSoftName === "") {
                                $("#ppSoftNameMsg").html("<span>Software Name is required</span>");
                                setTimeout(function () {
                                    $("#ppSoftNameMsg").fadeOut(5000);
                                }, 2000);
                                //errorNotify("Software Name is required");
                            }
                            if (pSoftVer === "") {
                                $("#ppSoftVerMsg").html("<span>Software Version name is required</span>");
                                setTimeout(function () {
                                    $("#ppSoftVerMsg").fadeOut(5000);
                                }, 2000);
                                //errorNotify("Software Version name is required");
                            }
                            if (pKb === "") {
                                $("#ppKbMsg").html("<span>Knowlwdge base is required</span>");
                                setTimeout(function () {
                                    $("#ppKbMsg").fadeOut(5000);
                                }, 2000);
                                //errorNotify("Knowlwdge base is required");
                            }
                            if (pServicePack === "") {
                                $("#ppServicePackMsg").html("<span>Service Pack is required</span>");
                                setTimeout(function () {
                                    $("#ppServicePackMsg").fadeOut(5000);
                                }, 2000);
                                //errorNotify("Service Pack is required");
                            }
                        } else {
                            var postFormData = $('#configurePatch').serialize();
                            postFormData.function = 'getConfigFn';
                            postFormData.csrfMagicToken = csrfMagicToken;
                            $.ajax({
                                url: '../softdist/SWD_Function.php',
                                type: 'POST',
                                data: postFormData,
                                success: function (data) {
                                    rightContainerSlideClose('rsc-config');
                                    //$("#configPop").modal('hide');
                                    showCofigDetail(selected, 'c');
                                    //$("#edconfPop").modal('show');
                                    rightContainerSlideOn('rsc-edit-configuration');
                                }

                            });
                        }
                    } else if ($('#ppRegistry').is(':checked')) {
                        if (psubKey === "") {
                            $("#psubKeyMsg").html("<span>Sub Key is required</span>");
                            setTimeout(function () {
                                $("#psubKeyMsg").fadeOut(5000);
                            }, 2000);
                            //errorNotify("Sub Key is required");
                            return false;
                        } else {
                            var postFormData = $('#configurePatch').serialize();
                            postFormData.function = 'getConfigFn';
                            postFormData.csrfMagicToken = csrfMagicToken;
                            $.ajax({
                                url: '../softdist/SWD_Function.php',
                                type: 'POST',
                                data: postFormData,
                                success: function (data) {
                                    rightContainerSlideClose('rsc-config');
                                    //$("#configPop").modal('hide');
                                    showCofigDetail(selected, 'c');
                                    //$("#edconfPop").modal('show');
                                    rightContainerSlideOn('rsc-edit-configuration');
                                }
                            });
                        }
                    }
                } else {
                    if (!$('#ppfile').is(':checked') && !$('#ppSoftware').is(':checked') && !$('#ppRegistry').is(':checked')) {
                        $("#ppreInsCheckMsg").html("<span>Please select any one radio button</span>");
                        setTimeout(function () {
                            $("#ppreInsCheckMsg").fadeOut(5000);
                        }, 2000);
                        //errorNotify("Please select any one radio button");
                    }
                }
            } else {
                var postFormData = $('#configurePatch').serialize();
                postFormData.function = 'getConfigFn';
                postFormData.csrfMagicToken = csrfMagicToken;
                $.ajax({
                    url: 'SWD_Function.php?function=getConfigFn',
                    type: 'POST',
                    data: postFormData,
                    success: function (data) {
                        rightContainerSlideClose('rsc-config');
                        //$("#configPop").modal('hide');
                        showCofigDetail(selected, 'c');
                        //$("#edconfPop").modal('show');
                        rightContainerSlideOn('rsc-edit-configuration');
                    }
                });
            }
        }
    }

}

// Edit Configuration Start
function showCofigDetail(id, column) {

    var ecid1 = id;
    var ecol1 = column;
    $('#ecol').val(ecol1);
    $('#ecid').val(ecid1);
    var configUrl = 'SWD_Function.php';
    var postFormData = {
        function: 'showconfigDetailsFn',
        id: id,
        column: column,
        csrfMagicToken: csrfMagicToken
    }
    $.ajax({
        url: configUrl,
        type: "POST",
        dataType: "json",
        data: postFormData,
        async: true,
        success: function (data) {

            //$("#edconfPop").modal("show");
            rightContainerSlideOn('rsc-edit-configuration');
            $('#column').val(data.data.column);
            $('#configg').text(data.data.config);
        }
    });
}

// Edit Configuration End

//    Distribute/Execute
$("#distexecPack").on('click', function () {
    var deId = '';
    deId = $('#distId').val();

    if (deId == undefined || deId == 'undefined' || deId == '') {
        errorNotify('Please select a package to execute/distribute');
        return;
    }
    
    rightMenuFunctionality();
    $("#distributeNow").prop("value", "0");
    $("#executeNow").prop("value", "0");
    $("#edconfig").prop("value", "");
    clearAllField();

    $('#rsc-distribute-execute-slider').find('form').eq(0).trigger("reset");
    var postFormData = {
        function: 'getStatus',
        id: deId,
        csrfMagicToken: csrfMagicToken
    };

    $.ajax({
        type: "POST",
        url: '../softdist/SWD_Function.php',
        dataType: "json",
        data: postFormData,
        async: true,
        success: function (data) {

            var resData = data.data.getStatus;
            var response = $.trim(resData);

            if (response === 'ND') {

                $("#distributeNow").prop("disabled", true);
                $("#nd").html('<span>Configure the distribution details for the Package</span>');
            } else {

                $("#distributeNow").removeAttr("disabled");
                $("#nd").html('');
            }
        }
    });

    var postFormData = {
        function: 'getexecuteStatus',
        id: deId,
        csrfMagicToken: csrfMagicToken
    };

    $.ajax({
        type: "POST",
        url: '../softdist/SWD_Function.php',
        dataType: "json",
        data: postFormData,
        async: true,
        success: function (data) {

            var resData = data.data.getexecuteStatus;
            var response = $.trim(resData);
                $("#executeNow").removeAttr("disabled");
                $("#ne").html('');
            }
    });

    $.ajax({
        type: "GET",
        url: '../softdist/SWD_Function.php',
        data: {function: 'getConfig', id: deId, csrfMagicToken: csrfMagicToken},
        success: function (response) {
            var response = $.trim(response);
            $("#edconfig").val(response);
        }
    });

    $.ajax({
        type: "GET",
        url: '../softdist/SWD_Function.php',
        data: {function: 'getDeployExecuteAvailability', id: deId, csrfMagicToken: csrfMagicToken},
        success: function (response) {
            var response = JSON.parse($.trim(response));
            console.log(response);
            if(response?.deploy) {
                $("#deploySWDpackage").text('Enabled');
            } else {
                $("#deploySWDpackage").text('Disabled');
            }
            if(response?.execute) {
                $("#executeSWDpackage").text('Enabled');
            } else {
                $("#executeSWDpackage").text('Disabled');
            }
        }
    });

    $("#addconfig").click(function () {

        if (!($('#distributeNow').is(':checked')) && !($('#executeNow').is(':checked'))) {

            $("#valsub").show();
        }
    });

    rightContainerSlideOn('rsc-distribute-execute-slider');
});

function saveConfig() {

    $("#valsub").html('');
    // if($('#distributeNowYes').is(':checked')){
    //     var dNo = '1';
    // }else{
    //     var dNo = '0';
    // }

    // if($('#executeNowYes').is(':checked')){
    //     var eNo = '1';
    // }else{
    //     var eNo = '0';
    // }

    // if(dNo == '0' && eNo == '0'){
    //     $.notify('Please Select either Deploy or Execute');
    //     return false;
    // }

    // if(eNo == '1' && dNo == '1'){
        var type = 'edConfig';
    // }else if(eNo == '1' && dNo == '0'){
    //     var type = 'edConfigexecute';
    // }else if(eNo == '0' && dNo == '1'){
    //     var type = 'edConfigdeploy';
    // }

    var deId = $('#distId').val();

     var postFormData = {
        function: 'getSelectedConfigVal',
        id: deId,
        type: type,
        csrfMagicToken: csrfMagicToken
    };
    $.ajax({
        type: "POST",
        url: '../softdist/SWD_Function.php',
//        dataType: "json",
        data: postFormData,
        async: true,
        success: function (data) {
                var config = $.trim(data);
                            if (config === '' || config === null || config === undefined) {
                    return;
                }

                var selectType = $("#executeType").val();
                var retryOpt = $("#retryopt").val();

                $('#absoFeed').hide();
                sweetAlert({
                    title: 'Are you sure that you want to continue?',
                    text: "This will push the configuration to the selected scope",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#050d30',
                    cancelButtonColor: '#fa0f4b',
                    cancelButtonText: "No, Cancel it!",
                    confirmButtonText: 'Yes, Configure it!'
                }).then(function (result) {
                    var postFormData = {
                        function: 'saveConfigFn',
                        id: deId,
                        edconfig: config,
                        column: 'distributionConfigDetail',
                        retryopt: retryOpt,
                        csrfMagicToken: csrfMagicToken
                    }
                    $.ajax({
                        url: '../softdist/SWD_Function.php',
                        type: 'POST',
                        data: postFormData,
                        async: true,
                        success: function (data) {
                            configsubmit("Software Distribution");
                            $("#loaderRepository").hide();
                        }
                    });
                }).catch(function () {
                    $(".closebtn").trigger("click");
                });
            },
            error:function(error){
                console.log("error");
            }
    });

}

function saveConfigg() {

    $("#configgErr").text("");
    $("#configgErr").fadeIn(1);

    var ecolumn = $('#column').val();
    var ecol = $('#ecol').val();
    var ecid = $('#ecid').val();
    var config = $.trim($("#configg").val());

    if (config == "" || config == null || config == "null") {
        $("#configgErr").html("<span>Configuration cannot be empty</span>");
        setTimeout(function () {
            $("#configgErr").fadeOut(3000);
        }, 2000);

    } else {
        var postFormData = {
            function: 'saveConfigFn',
            id: ecid,
            configg: encodeURIComponent(config),
            column: ecolumn,
            csrfMagicToken: csrfMagicToken
        }

        $.ajax({
            url: '../softdist/SWD_Function.php',
            type: 'POST',
            data: postFormData,
            async: true,
            success: function (data) {
                $("#edconfPop").modal("hide");

                setTimeout(function () {
                    Get_SoftwareRepositoryData();
                    rightContainerSlideClose('rsc-edit-configuration');
                    successNotify("Successfully added software configuration");
                }, 1000);
            },
            error: function () {
                errorNotify("Something went wrong, try again later");
            }
        });
    }
}

function configsubmit(JobType) {

    var Dart = $('#distId').val();
    var OS = !!$("#selOsType").val() ? $("#selOsType").val() : 'windows';
    var GroupName = $("#valueSearch").val();
    var dataconfig = '';

    var postFormData = {
        function: 'Add_RemoteJobs',
        Dart: Dart,
        Jobtype: JobType,
        OS: OS,
        type: $('#searchType').val(),
        name: $('#searchValue').val(),
        GroupName: GroupName,
        csrfMagicToken: csrfMagicToken
    }

    if (OS.toLowerCase() === "android") {
        postFormData.function = 'Add_AndroidJobs'
    } else if (OS.toLowerCase() === "ios") {
        $('#distPopup').modal('hide');
        $('#success').modal('show');
        $("#successMessage").show();
        $("#successMessage").html("<span>Action completed successfully</span>");
        Get_SoftwareRepositoryData2();
        rightContainerSlideClose('rsc-distribute-execute-slider');
        successNotify("Action completed successfully");
        return;
    }

    $.ajax({
        type: "POST",
        url: "../communication/communication_ajax.php",
        data: postFormData,
        success: function (msg) {
            msg = $.trim(msg);

            var res = msg.split('##');
            var machines = res[0];
            var batchid = res[2];
            var Showprogress = ''; //res[3];

            if (machines == '' || batchid == '') {
                rightContainerSlideClose('rsc-distribute-execute-slider');
                errorNotify("No Systems/Machines found to trigger this Software Distribution");
                $("#normError").show();
                $("#normError").html("<span>No Systems/Machines found to trigger this Software Distribution</span>");
                $("#mainError").hide();
            } else {

                EmitJobsForServiceTags(machines, Showprogress);
                rightContainerSlideClose('rsc-distribute-execute-slider');
                sweetAlert({
                    title: 'Notification',
                    text: "Reference number for this action is " + batchid,
                    type: 'success',
                    confirmButtonColor: '#050d30',
                    confirmButtonText: 'Ok'
                }).then(function (reason) {
                    $(".closebtn").trigger("click");
                }
                ).catch(function (reason) {
                    $(".closebtn").trigger("click");
                });
//                sweetAlert("Notification", "Reference number for this action is " + batchid, "success"); // not shown as notification , caue the message has a batchid number to be noted by user, but $.notify exists for a few seconds
                $("#successMessage").show();
                $("#successMessage").html("<span>Reference number for this action is </span>" + batchid);
            }
        },
        error: function () {
            rightContainerSlideClose('rsc-distribute-execute-slider');
            errorNotify("Oops !!! Something went wrong, try again later");
        }
    });

    Get_SoftwareRepositoryData2();
}

// Distribute/Execute Popup field close/empty events
$("#distPopup").on("hidden.bs.modal", function () {
    $("#distributeNow,#executeNow").val("0");
    $("#distributeNow,#executeNow").prop("checked", false);
    $("#edconfig,#valsub,#ne,#nd").val("");
});

// SWD Details popup field close/empty events
$("#swd_detail").on("hidden.bs.modal", function () {
    $("#platformDetail,#typeDetail,#packNameDetail,#versionDetail,#pathDetail,#forfDetail,#packDescDetail,#uploadDetail,#modifyDetail,#globalDetail").val("");
});


function distributePackageSubmit(){
    //For 32 bit config

    var restartClient = $('#nhinstallpatch').val();
    var restartPC = $('#nhrestartpc').val();
    var postFormData = $('#configurePatch2').serialize();
    postFormData += "&function=saveDistributeConfigFn&restartClient="+restartClient+"&restartPC="+restartPC+"&csrfMagicToken=" + csrfMagicToken;
    $.ajax({
        url: '../softdist/SWD_Function.php',
        type: 'POST',
        data: postFormData,
        success: function (data) {
        $.notify("Package Configured Successfully");
        rightContainerSlideClose('rsc-distribute-package');
        location.reload();
        }
    });
}

$('.preinstcheck2_64depl').on("click", function () {

    var id = $(this).attr('id');

    if (id == 'pfile2_64depl') {

        $('#' + id).val('0');
        $("#distributionPreCheckDivDE_64depl").show();

    } else if (id == 'pSoftware2_64depl') {

        $('#' + id).val('1');
        $("#distributionPreCheckDivDE_64depl").hide();

    } else if (id == 'pRegistry2_64depl') {

        $('#' + id).val('2');
        $("#distributionPreCheckDivDE_64depl").show();

    }
    $('.preinstcheckFields_64depl').hide();
    $('.' + id).show();

});

$("#registryRow_64depl").on("click", function () {

    $(".fileRow_64depl").hide();
    $("#filePath12_64depl").val('');
    $(".registryRow_64depl").show();

});

$("#fileRow_64depl").on("click", function () {

    $(".registryRow_64depl").hide();
    $("#subKey12_64depl").val('');
    $(".fileRow_64depl").show();

});

$('.preinstcheck2_64exec').on("click", function () {

    var id = $(this).attr('id');

    if (id == 'pfile2_64exec') {

        $('#' + id).val('0');
        $("#distributionPreCheckDivDE_64exec").show();

    } else if (id == 'pSoftware2_64exec') {

        $('#' + id).val('1');
        $("#distributionPreCheckDivDE_64exec").hide();

    } else if (id == 'pRegistry2_64exec') {

        $('#' + id).val('2');
        $("#distributionPreCheckDivDE_64exec").show();

    }
    $('.preinstcheckFields_64exec').hide();
    $('.' + id).show();

});

$("#registryRow_64exec").on("click", function () {

    $(".fileRow_64exec").hide();
    $("#filePath12_64exec").val('');
    $(".registryRow_64exec").show();

});

$("#fileRow_64exec").on("click", function () {

    $(".registryRow_64exec").hide();
    $("#subKey12_64exec").val('');
    $(".fileRow_64exec").show();

});

$('.preinstcheck2_resetClient32depl').on("click", function () {

    var id = $(this).attr('id');

    if (id == 'pfile2_resetClient32depl') {

        $('#' + id).val('0');
        $("#distributionPreCheckDivDE_resetClient32depl").show();

    } else if (id == 'pSoftware2_resetClient32depl') {

        $('#' + id).val('1');
        $("#distributionPreCheckDivDE_resetClient32depl").hide();

    } else if (id == 'pRegistry2_resetClient32depl') {

        $('#' + id).val('2');
        $("#distributionPreCheckDivDE_resetClient32depl").show();

    }
    $('.preinstcheckFields_resetClient32depl').hide();
    $('.' + id).show();

});

function seeValidityCheck_resetClient32depl() {
    var n = $("#validityCheck2_resetClient32depl:checked").length;

    if (n > 0) {
        $("#validityCheck2_resetClient32depl").val(1);
        $(".validationRow_resetClient32depl").show();
        var rID = $("input:radio[class=transferValidation_resetClient32depl]:checked").attr('id');
        $("#" + rID).click();
    } else {
        $("#validityCheck2_resetClient32depl").val(0);
        $("input:radio[class=transferValidation_resetClient32depl]:checked").removeAttr('checked');
        $(".validationRow_resetClient32depl").hide();
        $("#rootKey12_resetClient32depl").val(0);
        $("#subKey12_resetClient32depl").val('');
        $(".registryRow_resetClient32depl").hide();
        $("#filePath12_resetClient32depl").val('');
        $(".fileRow_resetClient32depl").hide();
    }
}

function seePreInstCheck_resetClient32depl() {
    var n = $("#preInsCheck2_resetClient32depl:checked").length;
    if (n > 0) {
        $("#preInsCheck2_resetClient32depl").val("1");
        $(".preinstcheck2_resetClient32depl").prop("checked", false);
        $(".insPreCheck_resetClient32depl").show();
        $("#distributionPreCheckDivDE_resetClient32depl").hide();
    } else {
        $("#preInsCheck2_resetClient32depl").val("0");
        $(".insPreCheck_resetClient32depl,.preinstcheckFields_resetClient32depl").hide();
        $(".preinstcheckFields_resetClient32depl").find('input').val('');
        $(".preinstcheck_resetClient32depl").removeAttr('checked');
    }
}

$("#registryRow_resetClient32depl").on("click", function () {

    $(".fileRow_resetClient32depl").hide();
    $("#filePath12_resetClient32depl").val('');
    $(".registryRow_resetClient32depl").show();

});

$("#fileRow_resetClient32depl").on("click", function () {

    $(".registryRow_resetClient32depl").hide();
    $("#subKey12_resetClient32depl").val('');
    $(".fileRow_resetClient32depl").show();

});

$('.preinstcheck2_resetClient64depl').on("click", function () {

    var id = $(this).attr('id');

    if (id == 'pfile2_resetClient64depl') {

        $('#' + id).val('0');
        $("#distributionPreCheckDivDE_resetClient64depl").show();

    } else if (id == 'pSoftware2_resetClient64depl') {

        $('#' + id).val('1');
        $("#distributionPreCheckDivDE_resetClient64depl").hide();

    } else if (id == 'pRegistry2_resetClient64depl') {

        $('#' + id).val('2');
        $("#distributionPreCheckDivDE_resetClient64depl").show();

    }
    $('.preinstcheckFields_resetClient64depl').hide();
    $('.' + id).show();

});

function seeValidityCheck_resetClient64depl() {
    var n = $("#validityCheck2_resetClient64depl:checked").length;

    if (n > 0) {
        $("#validityCheck2_resetClient64depl").val(1);
        $(".validationRow_resetClient64depl").show();
        var rID = $("input:radio[class=transferValidation_resetClient64depl]:checked").attr('id');
        $("#" + rID).click();
    } else {
        $("#validityCheck2_resetClient64depl").val(0);
        $("input:radio[class=transferValidation_resetClient64depl]:checked").removeAttr('checked');
        $(".validationRow_resetClient64depl").hide();
        $("#rootKey12_resetClient64depl").val(0);
        $("#subKey12_resetClient64depl").val('');
        $(".registryRow_resetClient64depl").hide();
        $("#filePath12_resetClient64depl").val('');
        $(".fileRow_resetClient64depl").hide();
    }
}

function seePreInstCheck_resetClient64depl() {
    var n = $("#preInsCheck2_resetClient64depl:checked").length;
    if (n > 0) {
        $("#preInsCheck2_resetClient64depl").val("1");
        $(".preinstcheck2_resetClient64depl").prop("checked", false);
        $(".insPreCheck_resetClient64depl").show();
        $("#distributionPreCheckDivDE_resetClient64depl").hide();
    } else {
        $("#preInsCheck2_resetClient64depl").val("0");
        $(".insPreCheck_resetClient64depl,.preinstcheckFields_resetClient64depl").hide();
        $(".preinstcheckFields_resetClient64depl").find('input').val('');
        $(".preinstcheck_resetClient64depl").removeAttr('checked');
    }
}

$("#registryRow_resetClient64depl").on("click", function () {

    $(".fileRow_resetClient64depl").hide();
    $("#filePath12_resetClient64depl").val('');
    $(".registryRow_resetClient64depl").show();

});

$("#fileRow_resetClient64depl").on("click", function () {

    $(".registryRow_resetClient64depl").hide();
    $("#subKey12_resetClient64depl").val('');
    $(".fileRow_resetClient64depl").show();

});

$('.preinstcheck2_resetClient32exec').on("click", function () {

    var id = $(this).attr('id');

    if (id == 'pfile2_resetClient32exec') {

        $('#' + id).val('0');
        $("#distributionPreCheckDivDE_resetClient32exec").show();

    } else if (id == 'pSoftware2_resetClient32exec') {

        $('#' + id).val('1');
        $("#distributionPreCheckDivDE_resetClient32exec").hide();

    } else if (id == 'pRegistry2_resetClient32exec') {

        $('#' + id).val('2');
        $("#distributionPreCheckDivDE_resetClient32exec").show();

    }
    $('.preinstcheckFields_resetClient32exec').hide();
    $('.' + id).show();

});

function seeValidityCheck_resetClient32exec() {
    var n = $("#validityCheck2_resetClient32exec:checked").length;

    if (n > 0) {
        $("#validityCheck2_resetClient32exec").val(1);
        $(".validationRow_resetClient32exec").show();
        var rID = $("input:radio[class=transferValidation_resetClient32exec]:checked").attr('id');
        $("#" + rID).click();
    } else {
        $("#validityCheck2_resetClient32exec").val(0);
        $("input:radio[class=transferValidation_resetClient32exec]:checked").removeAttr('checked');
        $(".validationRow_resetClient32exec").hide();
        $("#rootKey12_resetClient32exec").val(0);
        $("#subKey12_resetClient32exec").val('');
        $(".registryRow_resetClient32exec").hide();
        $("#filePath12_resetClient32exec").val('');
        $(".fileRow_resetClient32exec").hide();
    }
}

function seePreInstCheck_resetClient32exec() {
    var n = $("#preInsCheck2_resetClient32exec:checked").length;
    if (n > 0) {
        $("#preInsCheck2_resetClient32exec").val("1");
        $(".preinstcheck2_resetClient32exec").prop("checked", false);
        $(".insPreCheck_resetClient32exec").show();
        $("#distributionPreCheckDivDE_resetClient32exec").hide();
    } else {
        $("#preInsCheck2_resetClient32exec").val("0");
        $(".insPreCheck_resetClient32exec,.preinstcheckFields_resetClient32exec").hide();
        $(".preinstcheckFields_resetClient32exec").find('input').val('');
        $(".preinstcheck_resetClient32exec").removeAttr('checked');
    }
}

$("#registryRow_resetClient32exec").on("click", function () {

    $(".fileRow_resetClient32exec").hide();
    $("#filePath12_resetClient32exec").val('');
    $(".registryRow_resetClient32exec").show();

});

$("#fileRow_resetClient32exec").on("click", function () {

    $(".registryRow_resetClient32exec").hide();
    $("#subKey12_resetClient32exec").val('');
    $(".fileRow_resetClient32exec").show();

});

$('.preinstcheck2_resetClient64exec').on("click", function () {

    var id = $(this).attr('id');

    if (id == 'pfile2_resetClient64exec') {

        $('#' + id).val('0');
        $("#distributionPreCheckDivDE_resetClient64exec").show();

    } else if (id == 'pSoftware2_resetClient64exec') {

        $('#' + id).val('1');
        $("#distributionPreCheckDivDE_resetClient64exec").hide();

    } else if (id == 'pRegistry2_resetClient64exec') {

        $('#' + id).val('2');
        $("#distributionPreCheckDivDE_resetClient64exec").show();

    }
    $('.preinstcheckFields_resetClient64exec').hide();
    $('.' + id).show();

});

function seeValidityCheck_resetClient64exec() {
    var n = $("#validityCheck2_resetClient64exec:checked").length;

    if (n > 0) {
        $("#validityCheck2_resetClient64exec").val(1);
        $(".validationRow_resetClient64exec").show();
        var rID = $("input:radio[class=transferValidation_resetClient64exec]:checked").attr('id');
        $("#" + rID).click();
    } else {
        $("#validityCheck2_resetClient64exec").val(0);
        $("input:radio[class=transferValidation_resetClient64exec]:checked").removeAttr('checked');
        $(".validationRow_resetClient64exec").hide();
        $("#rootKey12_resetClient64exec").val(0);
        $("#subKey12_resetClient64exec").val('');
        $(".registryRow_resetClient64exec").hide();
        $("#filePath12_resetClient64exec").val('');
        $(".fileRow_resetClient64exec").hide();
    }
}

function seePreInstCheck_resetClient64exec() {
    var n = $("#preInsCheck2_resetClient64exec:checked").length;
    if (n > 0) {
        $("#preInsCheck2_resetClient64exec").val("1");
        $(".preinstcheck2_resetClient64exec").prop("checked", false);
        $(".insPreCheck_resetClient64exec").show();
        $("#distributionPreCheckDivDE_resetClient64exec").hide();
    } else {
        $("#preInsCheck2_resetClient64exec").val("0");
        $(".insPreCheck_resetClient64exec,.preinstcheckFields_resetClient64exec").hide();
        $(".preinstcheckFields_resetClient64exec").find('input').val('');
        $(".preinstcheck_resetClient64exec").removeAttr('checked');
    }
}

$("#registryRow_resetClient64exec").on("click", function () {

    $(".fileRow_resetClient64exec").hide();
    $("#filePath12_resetClient64exec").val('');
    $(".registryRow_resetClient64exec").show();

});

$("#fileRow_resetClient64exec").on("click", function () {

    $(".registryRow_resetClient64exec").hide();
    $("#subKey12_resetClient64exec").val('');
    $(".fileRow_resetClient64exec").show();

});


$('.preinstcheck2_resetPC32exec').on("click", function () {

    var id = $(this).attr('id');

    if (id == 'pfile2_resetPC32exec') {

        $('#' + id).val('0');
        $("#distributionPreCheckDivDE_resetPC32exec").show();

    } else if (id == 'pSoftware2_resetPC32exec') {

        $('#' + id).val('1');
        $("#distributionPreCheckDivDE_resetPC32exec").hide();

    } else if (id == 'pRegistry2_resetPC32exec') {

        $('#' + id).val('2');
        $("#distributionPreCheckDivDE_resetPC32exec").show();

    }
    $('.preinstcheckFields_resetPC32exec').hide();
    $('.' + id).show();

});

function seeValidityCheck_resetPC32exec() {
    var n = $("#validityCheck2_resetPC32exec:checked").length;

    if (n > 0) {
        $("#validityCheck2_resetPC32exec").val(1);
        $(".validationRow_resetPC32exec").show();
        var rID = $("input:radio[class=transferValidation_resetPC32exec]:checked").attr('id');
        $("#" + rID).click();
    } else {
        $("#validityCheck2_resetPC32exec").val(0);
        $("input:radio[class=transferValidation_resetPC32exec]:checked").removeAttr('checked');
        $(".validationRow_resetPC32exec").hide();
        $("#rootKey12_resetPC32exec").val(0);
        $("#subKey12_resetPC32exec").val('');
        $(".registryRow_resetPC32exec").hide();
        $("#filePath12_resetPC32exec").val('');
        $(".fileRow_resetPC32exec").hide();
    }
}

function seePreInstCheck_resetPC32exec() {
    var n = $("#preInsCheck2_resetPC32exec:checked").length;
    if (n > 0) {
        $("#preInsCheck2_resetPC32exec").val("1");
        $(".preinstcheck2_resetPC32exec").prop("checked", false);
        $(".insPreCheck_resetPC32exec").show();
        $("#distributionPreCheckDivDE_resetPC32exec").hide();
    } else {
        $("#preInsCheck2_resetPC32exec").val("0");
        $(".insPreCheck_resetPC32exec,.preinstcheckFields_resetPC32exec").hide();
        $(".preinstcheckFields_resetPC32exec").find('input').val('');
        $(".preinstcheck_resetPC32exec").removeAttr('checked');
    }
}

$("#registryRow_resetPC32exec").on("click", function () {

    $(".fileRow_resetPC32exec").hide();
    $("#filePath12_resetPC32exec").val('');
    $(".registryRow_resetPC32exec").show();

});

$("#fileRow_resetPC32exec").on("click", function () {

    $(".registryRow_resetPC32exec").hide();
    $("#subKey12_resetPC32exec").val('');
    $(".fileRow_resetPC32exec").show();

});

$('.preinstcheck2_resetPC64exec').on("click", function () {

    var id = $(this).attr('id');

    if (id == 'pfile2_resetPC64exec') {

        $('#' + id).val('0');
        $("#distributionPreCheckDivDE_resetPC64exec").show();

    } else if (id == 'pSoftware2_resetPC64exec') {

        $('#' + id).val('1');
        $("#distributionPreCheckDivDE_resetPC64exec").hide();

    } else if (id == 'pRegistry2_resetPC64exec') {

        $('#' + id).val('2');
        $("#distributionPreCheckDivDE_resetPC64exec").show();

    }
    $('.preinstcheckFields_resetPC64exec').hide();
    $('.' + id).show();

});

function seeValidityCheck_resetPC64exec() {
    var n = $("#validityCheck2_resetPC64exec:checked").length;

    if (n > 0) {
        $("#validityCheck2_resetPC64exec").val(1);
        $(".validationRow_resetPC64exec").show();
        var rID = $("input:radio[class=transferValidation_resetPC64exec]:checked").attr('id');
        $("#" + rID).click();
    } else {
        $("#validityCheck2_resetPC64exec").val(0);
        $("input:radio[class=transferValidation_resetPC64exec]:checked").removeAttr('checked');
        $(".validationRow_resetPC64exec").hide();
        $("#rootKey12_resetPC64exec").val(0);
        $("#subKey12_resetPC64exec").val('');
        $(".registryRow_resetPC64exec").hide();
        $("#filePath12_resetPC64exec").val('');
        $(".fileRow_resetPC64exec").hide();
    }
}

function seePreInstCheck_resetPC64exec() {
    var n = $("#preInsCheck2_resetPC64exec:checked").length;
    if (n > 0) {
        $("#preInsCheck2_resetPC64exec").val("1");
        $(".preinstcheck2_resetPC64exec").prop("checked", false);
        $(".insPreCheck_resetPC64exec").show();
        $("#distributionPreCheckDivDE_resetPC64exec").hide();
    } else {
        $("#preInsCheck2_resetPC64exec").val("0");
        $(".insPreCheck_resetPC64exec,.preinstcheckFields_resetPC64exec").hide();
        $(".preinstcheckFields_resetPC64exec").find('input').val('');
        $(".preinstcheck_resetPC64exec").removeAttr('checked');
    }
}

$("#registryRow_resetPC64exec").on("click", function () {

    $(".fileRow_resetPC64exec").hide();
    $("#filePath12_resetPC64exec").val('');
    $(".registryRow_resetPC64exec").show();

});

$("#fileRow_resetPC64exec").on("click", function () {

    $(".registryRow_resetPC64exec").hide();
    $("#subKey12_resetPC64exec").val('');
    $(".fileRow_resetPC64exec").show();

});

$('.preinstcheck2_resetPC32depl').on("click", function () {

    var id = $(this).attr('id');

    if (id == 'pfile2_resetPC32depl') {

        $('#' + id).val('0');
        $("#distributionPreCheckDivDE_resetPC32depl").show();

    } else if (id == 'pSoftware2_resetPC32depl') {

        $('#' + id).val('1');
        $("#distributionPreCheckDivDE_resetPC32depl").hide();

    } else if (id == 'pRegistry2_resetPC32depl') {

        $('#' + id).val('2');
        $("#distributionPreCheckDivDE_resetPC32depl").show();

    }
    $('.preinstcheckFields_resetPC32depl').hide();
    $('.' + id).show();

});

function seeValidityCheck_resetPC32depl() {
    var n = $("#validityCheck2_resetPC32depl:checked").length;

    if (n > 0) {
        $("#validityCheck2_resetPC32depl").val(1);
        $(".validationRow_resetPC32depl").show();
        var rID = $("input:radio[class=transferValidation_resetPC32depl]:checked").attr('id');
        $("#" + rID).click();
    } else {
        $("#validityCheck2_resetPC32depl").val(0);
        $("input:radio[class=transferValidation_resetPC32depl]:checked").removeAttr('checked');
        $(".validationRow_resetPC32depl").hide();
        $("#rootKey12_resetPC32depl").val(0);
        $("#subKey12_resetPC32depl").val('');
        $(".registryRow_resetPC32depl").hide();
        $("#filePath12_resetPC32depl").val('');
        $(".fileRow_resetPC32depl").hide();
    }
}

function seePreInstCheck_resetPC32depl() {
    var n = $("#preInsCheck2_resetPC32depl:checked").length;
    if (n > 0) {
        $("#preInsCheck2_resetPC32depl").val("1");
        $(".preinstcheck2_resetPC32depl").prop("checked", false);
        $(".insPreCheck_resetPC32depl").show();
        $("#distributionPreCheckDivDE_resetPC32depl").hide();
    } else {
        $("#preInsCheck2_resetPC32depl").val("0");
        $(".insPreCheck_resetPC32depl,.preinstcheckFields_resetPC32depl").hide();
        $(".preinstcheckFields_resetPC32depl").find('input').val('');
        $(".preinstcheck_resetPC32depl").removeAttr('checked');
    }
}

$("#registryRow_resetPC32depl").on("click", function () {

    $(".fileRow_resetPC32depl").hide();
    $("#filePath12_resetPC32depl").val('');
    $(".registryRow_resetPC32depl").show();

});

$("#fileRow_resetPC32depl").on("click", function () {

    $(".registryRow_resetPC32depl").hide();
    $("#subKey12_resetPC32depl").val('');
    $(".fileRow_resetPC32depl").show();

});

$('.preinstcheck2_resetPC64depl').on("click", function () {

    var id = $(this).attr('id');

    if (id == 'pfile2_resetPC64depl') {

        $('#' + id).val('0');
        $("#distributionPreCheckDivDE_resetPC64depl").show();

    } else if (id == 'pSoftware2_resetPC64depl') {

        $('#' + id).val('1');
        $("#distributionPreCheckDivDE_resetPC64depl").hide();

    } else if (id == 'pRegistry2_resetPC64depl') {

        $('#' + id).val('2');
        $("#distributionPreCheckDivDE_resetPC64depl").show();

    }
    $('.preinstcheckFields_resetPC64depl').hide();
    $('.' + id).show();

});

function seeValidityCheck_resetPC64depl() {
    var n = $("#validityCheck2_resetPC64depl:checked").length;

    if (n > 0) {
        $("#validityCheck2_resetPC64depl").val(1);
        $(".validationRow_resetPC64depl").show();
        var rID = $("input:radio[class=transferValidation_resetPC64depl]:checked").attr('id');
        $("#" + rID).click();
    } else {
        $("#validityCheck2_resetPC64depl").val(0);
        $("input:radio[class=transferValidation_resetPC64depl]:checked").removeAttr('checked');
        $(".validationRow_resetPC64depl").hide();
        $("#rootKey12_resetPC64depl").val(0);
        $("#subKey12_resetPC64depl").val('');
        $(".registryRow_resetPC64depl").hide();
        $("#filePath12_resetPC64depl").val('');
        $(".fileRow_resetPC64depl").hide();
    }
}

function seePreInstCheck_resetPC64depl() {
    var n = $("#preInsCheck2_resetPC64depl:checked").length;
    if (n > 0) {
        $("#preInsCheck2_resetPC64depl").val("1");
        $(".preinstcheck2_resetPC64depl").prop("checked", false);
        $(".insPreCheck_resetPC64depl").show();
        $("#distributionPreCheckDivDE_resetPC64depl").hide();
    } else {
        $("#preInsCheck2_resetPC64depl").val("0");
        $(".insPreCheck_resetPC64depl,.preinstcheckFields_resetPC64depl").hide();
        $(".preinstcheckFields_resetPC64depl").find('input').val('');
        $(".preinstcheck_resetPC64depl").removeAttr('checked');
    }
}

$("#registryRow_resetPC64depl").on("click", function () {

    $(".fileRow_resetPC64depl").hide();
    $("#filePath12_resetPC64depl").val('');
    $(".registryRow_resetPC64depl").show();

});

$("#fileRow_resetPC64depl").on("click", function () {

    $(".registryRow_resetPC64depl").hide();
    $("#subKey12_resetPC64depl").val('');
    $(".fileRow_resetPC64depl").show();

});


function clickl32Deploy(){
    if($('#showConfigDiv').hasClass('active')){
        $('#32deployclick').show();
        $('#showConfigDiv').removeClass('active');
        $('#hideshow1').html('<b>&#45;</b>');
    }else{
        $('#hideshow1').html('<b>&#43;</b>');
        $('#32deployclick').hide();
        $('#showConfigDiv').addClass('active');
    }
}

function clickl32Exec(){
    if($('#showConfigDiv2').hasClass('active')){
        $('#32executeclick').show();
        $('#showConfigDiv2').removeClass('active');
        $('#hideshow2').html('<b>&#45;</b>');
    }else{
        $('#hideshow2').html('<b>&#43;</b>');
        $('#32executeclick').hide();
        $('#showConfigDiv2').addClass('active');
    }
}

function click64Deploy(){
    if($('#showConfigDiv3').hasClass('active')){
        $('#64deployclick_64').show();
        $('#showConfigDiv3').removeClass('active');
        $('#hideshow3').html('<b>&#45;</b>');
    }else{
        $('#hideshow3').html('<b>&#43;</b>');
        $('#64deployclick_64').hide();
        $('#showConfigDiv3').addClass('active');
    }
}

function click64Exec(){
    if($('#showConfigDiv4').hasClass('active')){
        $('#64executeclick_64').show();
        $('#showConfigDiv4').removeClass('active');
        $('#hideshow4').html('<b>&#45;</b>');
    }else{
        $('#hideshow4').html('<b>&#43;</b>');
        $('#64executeclick_64').hide();
        $('#showConfigDiv4').addClass('active');
    }
}

function click32Deploy_reClient(){

    if($('#showConfigDiv5').hasClass('active')){
        $('#32deployclick_resetClient').show();
        $('#showConfigDiv5').removeClass('active');
        $('#hideshow5').html('<b>&#45;</b>');
    }else{
        $('#hideshow5').html('<b>&#43;</b>');
        $('#32deployclick_resetClient').hide();
        $('#showConfigDiv5').addClass('active');
    }
}

function click64Deploy_reClient(){
    if($('#showConfigDiv6').hasClass('active')){
        $('#64deployclick_resetClient').show();
        $('#showConfigDiv6').removeClass('active');
        $('#hideshow6').html('<b>&#45;</b>');
    }else{
        $('#hideshow6').html('<b>&#43;</b>');
        $('#64deployclick_resetClient').hide();
        $('#showConfigDiv6').addClass('active');
    }
}

function click32exec_reClient(){
    if($('#showConfigDiv7').hasClass('active')){
        $('#32executeclick_resetClient').show();
        $('#showConfigDiv7').removeClass('active');
        $('#hideshow7').html('<b>&#45;</b>');
    }else{
        $('#hideshow7').html('<b>&#43;</b>');
        $('#32executeclick_resetClient').hide();
        $('#showConfigDiv7').addClass('active');
    }
}

function click64exec_reClient(){
    if($('#showConfigDiv8').hasClass('active')){
        $('#64executeclick_resetClient').show();
        $('#showConfigDiv8').removeClass('active');
        $('#hideshow8').html('<b>&#45;</b>');
    }else{
        $('#hideshow8').html('<b>&#43;</b>');
        $('#64executeclick_resetClient').hide();
        $('#showConfigDiv8').addClass('active');
    }
}

function click32Depl_rePC(){
    if($('#showConfigDiv9').hasClass('active')){
        $('#32deployclick_resetPC').show();
        $('#showConfigDiv9').removeClass('active');
        $('#hideshow9').html('<b>&#45;</b>');
    }else{
        $('#hideshow9').html('<b>&#43;</b>');
        $('#32deployclick_resetPC').hide();
        $('#showConfigDiv9').addClass('active');
    }
}

function click64Depl_rePC(){
    if($('#showConfigDiv10').hasClass('active')){
        $('#64deployclick_resetPC').show();
        $('#showConfigDiv10').removeClass('active');
        $('#hideshow10').html('<b>&#45;</b>');
    }else{
        $('#hideshow10').html('<b>&#43;</b>');
        $('#64deployclick_resetPC').hide();
        $('#showConfigDiv10').addClass('active');
    }
}

function click32exec_rePC(){
    if($('#showConfigDiv11').hasClass('active')){
        $('#32executeclick_resetPC').show();
        $('#showConfigDiv11').removeClass('active');
        $('#hideshow11').html('<b>&#45;</b>');
    }else{
        $('#hideshow11').html('<b>&#43;</b>');
        $('#32executeclick_resetPC').hide();
        $('#showConfigDiv11').addClass('active');
    }
}

function click64exec_rePC(){
    if($('#showConfigDiv12').hasClass('active')){
        $('#64executeclick_resetPC').show();
        $('#showConfigDiv12').removeClass('active');
        $('#hideshow12').html('<b>&#45;</b>');
    }else{
        $('#hideshow12').html('<b>&#43;</b>');
        $('#64executeclick_resetPC').hide();
        $('#showConfigDiv12').addClass('active');
    }
}