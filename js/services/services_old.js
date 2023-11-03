$(document).ready(function() {
    $.ajax({
        type: "POST",
        url: "../services/wizFunc.php",
        data:"function=parentDescription",
                success: function(msg) { 
                console.log(msg);
                $("#parent_desc").append(msg);        
        },
        error: function(msg) {
                alert("error");     
                       }
    });
});

function childDesc(id)
{
    alert("inside child description");
    $("#parent_desc").hide();
    $("#child_desc").show();
    $.ajax({
        type: "POST",
        url: "../services/wizFunc.php",
        data:"function=childDescription",
                success: function(msg) { 
                console.log(msg);
                $("#child_desc").append(msg);        
        },
        error: function(msg) {
                alert("error");     
                       }
    });
}

$(function () {
    $("#ascrail2007-hr").removeAttr('style');
    $("#ascrail2007-hr div").removeAttr('style');

    if ($(".chck").is(":checked")) {
        $("#submit").show();
    } else {
        $("#submit").hide();
    }

    if (avira_inst == "1" || avira_inst == 1) {
        aviraWmiFile();
    }

    //script.js -> form_datetime commentf
    $(".form_datetime").datetimepicker({
        format: "mm/dd/yyyy/hh:ii",
        autoclose: true,
        todayBtn: true,
        pickerPosition: "bottom-left",
//        startDate: "2017-02-14 10:00",
//        endDate: "2017-02-20 10:00"
    });

    $(".form_time").datetimepicker({
        format: 'hh:ii',
        startView: 1,
        pickDate: false,
        autoclose: true,
        pickerPosition: "bottom-left"
    }).on("show", function () {
        $(".table-condensed .prev").css('visibility', 'hidden');
        $(".table-condensed .switch").text("Pick Time");
        $(".table-condensed .next").css('visibility', 'hidden');
    });
});


var preventereddata = {};

function keyPressFn(VarID) {
    $("#" + VarID).keyup(function () {
        var EntPass = $("#EntPass").val();
        var ConfPass = $("#ConfPass").val();
        if (EntPass == ConfPass) {
            if (EntPass == "" || ConfPass == "") {
                $("#ControlCenter,#RealTimeProt,#MailProt,#WebProt,#Quarantine,#RestAffObj,#RescanAffObjs,#AffObjProps,#DelAffObj,#SendMail,#AddModJobs,#Configuration,#InstOrUninst").prop("disabled", true);
            } else {
                $("#ControlCenter,#RealTimeProt,#MailProt,#WebProt,#Quarantine,#RestAffObj,#RescanAffObjs,#AffObjProps,#DelAffObj,#SendMail,#AddModJobs,#Configuration,#InstOrUninst").prop("disabled", false);
            }

        } else {
            $("#ControlCenter,#RealTimeProt,#MailProt,#WebProt,#Quarantine,#RestAffObj,#RescanAffObjs,#AffObjProps,#DelAffObj,#SendMail,#AddModJobs,#Configuration,#InstOrUninst").prop("disabled", true);
        }
    });
}


function setDefaultValues(DartNo) {
    if (DartNo == "18000") {
        $("#SelectAll,#APPL,#GAME,#JOKE,#SPR").prop("checked", false);
        $("#SelectAll,#APPL,#GAME,#JOKE,#SPR").val("0");
        $("#ADWARE,#ADSPY,#BDC,#DIAL,#HIDDENEXT,#PFS,#PHISH,#PUA").prop("checked", true);
        $("#ADWARE,#ADSPY,#BDC,#DIAL,#HIDDENEXT,#PFS,#PHISH,#PUA").val("1");
    }
    if (DartNo == "9000") {
        $("#ScanArchiveRecursionDepth").val("20");
        $("#SelectAllArchive,#wim,#iso9660,#msom,#sc,#pm,#em,#nmm,#bsdm").prop("checked", false);
        $("#SelectAllArchive,#wim,#iso9660,#msom,#sc,#pm,#em,#nmm,#bsdm").val("0");
        $("#ScanArchiveScan,#ScanArchivSmartExtensions,#ScanArchiveCutRecursionDepth,#olxml,#sim,#xz,#xar,#is,#sapc,#ahk,#ai,#base64,#7zipsfx,#7zip,#nsis,#rpm,#cpio,#chm,#mb,#pgpsm,#acesfx,#ace,#bz2,#jar,#rarsfx,#rar,#lzhsfx,#lzh,#cabsfx,#cabm,#mscomp,#binhex,#mime,#tnef,#uuencode,#zoo,#gz,#tar,#arjsfx,#arj,#crx,#zipsfx,#zip").prop("checked", true);
        $("#ScanArchiveScan,#ScanArchivSmartExtensions,#ScanArchiveCutRecursionDepth,#olxml,#sim,#xz,#xar,#is,#sapc,#ahk,#ai,#base64,#7zipsfx,#7zip,#nsis,#rpm,#cpio,#chm,#mb,#pgpsm,#acesfx,#ace,#bz2,#jar,#rarsfx,#rar,#lzhsfx,#lzh,#cabsfx,#cabm,#mscomp,#binhex,#mime,#tnef,#uuencode,#zoo,#gz,#tar,#arjsfx,#arj,#crx,#zipsfx,#zip").val("1");
    }
}

function multiCheck(obj) {
    var checkVal = $("#" + obj).is(":checked");

    if (checkVal) {
        $("#" + obj).val("0");
        $("#ADWARE,#ADSPY,#APPL,#BDC,#DIAL,#HIDDENEXT,#PFS,#GAME,#JOKE,#PHISH,#PUA,#SPR").prop("checked", false);
        $("#SelectAll,#ADWARE,#ADSPY,#APPL,#BDC,#DIAL,#HIDDENEXT,#PFS,#GAME,#JOKE,#PHISH,#PUA,#SPR").val("0");
    } else {
        $("#" + obj).val("1");
        $("#ADWARE,#ADSPY,#APPL,#BDC,#DIAL,#HIDDENEXT,#PFS,#GAME,#JOKE,#PHISH,#PUA,#SPR").prop("checked", true);
        $("#SelectAll,#ADWARE,#ADSPY,#APPL,#BDC,#DIAL,#HIDDENEXT,#PFS,#GAME,#JOKE,#PHISH,#PUA,#SPR").val("1");
    }
}

function chck(obj) {

    var checkVal = $("#" + obj).is(":checked");

    if (checkVal) {
        $("#" + obj).val("0");
    } else {
        $("#" + obj).val("1");
    }

    if ($("div").hasClass("ControlCenter")) {
        keyPressFn("");
    }

    if (obj == "SelectAllArchive") {
        if (checkVal) {
            $("#wim,#iso9660,#msom,#sc,#pm,#em,#nmm,#bsdm,#olxml,#sim,#xz,#xar,#is,#sapc,#ahk,#ai,#base64,#7zipsfx,#7zip,#nsis,#rpm,#cpio,#chm,#mb,#pgpsm,#acesfx,#ace,#bz2,#jar,#rarsfx,#rar,#lzhsfx,#lzh,#cabsfx,#cabm,#mscomp,#binhex,#mime,#tnef,#uuencode,#zoo,#gz,#tar,#arjsfx,#arj,#crx,#zipsfx,#zip").prop("checked", false);
            $("#SelectAllArchive,#wim,#iso9660,#msom,#sc,#pm,#em,#nmm,#bsdm,#olxml,#sim,#xz,#xar,#is,#sapc,#ahk,#ai,#base64,#7zipsfx,#7zip,#nsis,#rpm,#cpio,#chm,#mb,#pgpsm,#acesfx,#ace,#bz2,#jar,#rarsfx,#rar,#lzhsfx,#lzh,#cabsfx,#cabm,#mscomp,#binhex,#mime,#tnef,#uuencode,#zoo,#gz,#tar,#arjsfx,#arj,#crx,#zipsfx,#zip").val("0");
        } else {
            $("#wim,#iso9660,#msom,#sc,#pm,#em,#nmm,#bsdm,#olxml,#sim,#xz,#xar,#is,#sapc,#ahk,#ai,#base64,#7zipsfx,#7zip,#nsis,#rpm,#cpio,#chm,#mb,#pgpsm,#acesfx,#ace,#bz2,#jar,#rarsfx,#rar,#lzhsfx,#lzh,#cabsfx,#cabm,#mscomp,#binhex,#mime,#tnef,#uuencode,#zoo,#gz,#tar,#arjsfx,#arj,#crx,#zipsfx,#zip").prop("checked", true);
            $("#SelectAllArchive,#wim,#iso9660,#msom,#sc,#pm,#em,#nmm,#bsdm,#olxml,#sim,#xz,#xar,#is,#sapc,#ahk,#ai,#base64,#7zipsfx,#7zip,#nsis,#rpm,#cpio,#chm,#mb,#pgpsm,#acesfx,#ace,#bz2,#jar,#rarsfx,#rar,#lzhsfx,#lzh,#cabsfx,#cabm,#mscomp,#binhex,#mime,#tnef,#uuencode,#zoo,#gz,#tar,#arjsfx,#arj,#crx,#zipsfx,#zip").val("1");
        }
    }

    var ControlCenter = $("#ControlCenter").val();
    var RealTimeProt = $("#RealTimeProt").val();
    var MailProt = $("#MailProt").val();
    var WebProt = $("#WebProt").val();
    var Quarantine = $("#Quarantine").val();
    var RestAffObj = $("#RestAffObj").val();
    var RescanAffObjs = $("#RescanAffObjs").val();
    var AffObjProps = $("#AffObjProps").val();
    var DelAffObj = $("#DelAffObj").val();
    var SendMail = $("#SendMail").val();
    var AddModJobs = $("#AddModJobs").val();

    if (obj == "ControlCenter") {
        if (checkVal) {
            $("#RealTimeProt,#MailProt,#WebProt,#Quarantine,#RestAffObj,#RescanAffObjs,#AffObjProps,#DelAffObj,#SendMail,#AddModJobs").prop("checked", false);
            $("#RealTimeProt,#MailProt,#WebProt,#Quarantine,#RestAffObj,#RescanAffObjs,#AffObjProps,#DelAffObj,#SendMail,#AddModJobs").val("0");
        } else {
            $("#RealTimeProt,#MailProt,#WebProt,#Quarantine,#RestAffObj,#RescanAffObjs,#AffObjProps,#DelAffObj,#SendMail,#AddModJobs").prop("checked", true);
            $("#RealTimeProt,#MailProt,#WebProt,#Quarantine,#RestAffObj,#RescanAffObjs,#AffObjProps,#DelAffObj,#SendMail,#AddModJobs").val("1");
        }
    }

    if (obj == "Quarantine") {
        if (checkVal) {
            $("#ControlCenter,#RestAffObj,#RescanAffObjs,#AffObjProps,#DelAffObj,#SendMail").prop("checked", false);
            $("#ControlCenter,#RestAffObj,#RescanAffObjs,#AffObjProps,#DelAffObj,#SendMail").val("0");
        } else {
            $("#RestAffObj,#RescanAffObjs,#AffObjProps,#DelAffObj,#SendMail").prop("checked", true);
            $("#RestAffObj,#RescanAffObjs,#AffObjProps,#DelAffObj,#SendMail").val("1");
            if (RealTimeProt == "1" && MailProt == "1" && WebProt == "1" && RestAffObj == "1" && RescanAffObjs == "1" && AffObjProps == "1" && DelAffObj == "1" && SendMail == "1" && AddModJobs == "1") {
                $("#ControlCenter").prop("checked", true);
                $("#ControlCenter").val("1");
            } else {
                $("#ControlCenter").prop("checked", false);
                $("#ControlCenter").val("0");
            }
        }
    }

    if (obj == "RealTimeProt" || obj == "MailProt" || obj == "WebProt" || obj == "SendMail" || obj == "AddModJobs") {
        if (checkVal) {
            $("#ControlCenter").prop("checked", false);
            $("#ControlCenter").val("0");
        } else {
            if (RealTimeProt == "1" && MailProt == "1" && WebProt == "1" && RestAffObj == "1" && RescanAffObjs == "1" && AffObjProps == "1" && DelAffObj == "1" && SendMail == "1" && AddModJobs == "1") {
                $("#ControlCenter").prop("checked", true);
                $("#ControlCenter").val("1");
            } else {
                $("#ControlCenter").prop("checked", false);
                $("#ControlCenter").val("0");
            }
        }
    }
    if (obj == "RestAffObj" || obj == "RescanAffObjs" || obj == "AffObjProps" || obj == "DelAffObj" || obj == "SendMail") {
        if (checkVal) {
            $("#Quarantine").prop("checked", false);
            $("#Quarantine").val("0");
        } else {
            if (RestAffObj == "1" && RescanAffObjs == "1" && AffObjProps == "1" && DelAffObj == "1" && SendMail == "1") {
                $("#Quarantine").prop("checked", true);
                $("#Quarantine").val("1");
            } else {
                $("#Quarantine").prop("checked", false);
                $("#Quarantine ").val("0");
            }
        }
    }

    var countChecked = function () {

        var n = $("input:checked").length;

        if (n > 0) {
            $("#submit").fadeIn();
        } else {
//            $("#submit").fadeOut();
        }

    };
    countChecked();

    $("input[type=checkbox]").on("click", countChecked);

    if ($("div").hasClass("BeforeActionToQuarantine")) {
        var radio_value1 = $('#BeforeActionToQuarantine').val();
        $("#SecondaryActionForInfected option[value='1']").hide();
        $(".SecondaryActionForInfected").selectpicker('refresh');
        if (radio_value1 == "1" || radio_value1 == 1) {
            $("#PrimaryActionForInfected option[value='3']").hide();
            $("#SecondaryActionForInfected option[value='3']").hide();
            $(".PrimaryActionForInfected,.SecondaryActionForInfected").selectpicker('refresh');
        } else if (radio_value1 == "0" || radio_value1 == 0) {
            $("#PrimaryActionForInfected option[value='3']").show();
            $("#SecondaryActionForInfected option[value='3']").show();
            $(".PrimaryActionForInfected,.SecondaryActionForInfected").selectpicker('refresh');
        }
    }

    if ($("div").hasClass("ControlCenter")) {
        var EntPass = $("#EntPass").val();
        var ConfPass = $("#ConfPass").val();

        if (EntPass != ConfPass) {
            $(":checkbox").prop("checked", false);
            $(":checkbox").val("0");
        } else if (EntPass == "" || ConfPass == "") {
            $(":checkbox").prop("checked", false);
            $(":checkbox").val("0");
        }
    }
//    if ($("div").hasClass("ScanInMails")) {
//        var check_value1 = $('input[name=ScanInMails]:checked', '#configure_wizard_detailsAv').val();
//        if (check_value1 == "1" || check_value1 == undefined) {
//            $("#POP3,#IMAP").prop("disabled", false);
//            var check_value2 = $('input[name=POP3]:checked', '#configure_wizard_detailsAv').val();
//            var check_value3 = $('input[name=IMAP]:checked', '#configure_wizard_detailsAv').val();
//            if (check_value2 == "1" || check_value2 == 1) {
//                $(".ImapPortNr1").prop("disabled", false);
//            } else {
//                $(".ImapPortNr1").prop("disabled", true);
//            }
//            if (check_value3 == "1" || check_value3 == 1) {
//                $(".Pop3PortNr1").prop("disabled", false);
//            } else {
//                $(".Pop3PortNr1").prop("disabled", true);
//            }
//        } else {
//            $(".ImapPortNr1,#POP3,.Pop3PortNr1,#IMAP").prop("disabled", true);
//        }
//    }
//    if ($("div").hasClass("POP3")) {
//        var check_value4 = $('input[name=POP3]:checked', '#configure_wizard_detailsAv').val();
//        if (check_value4 == "1" || check_value4 == undefined) {
//            $(".SmtpPortNr1").prop("disabled", false);
//        } else {
//            $(".SmtpPortNr1").prop("disabled", true);
//        }
//    }
//    if ($("div").hasClass("IMAP")) {
//        var check_value5 = $('input[name=IMAP]:checked', '#configure_wizard_detailsAv').val();
//        if (check_value5 == "1" || check_value5 == undefined) {
//            $(".ImapPortNr1").prop("disabled", false);
//        } else {
//            $(".ImapPortNr1").prop("disabled", true);
//        }
//    }
//    if ($("div").hasClass("ScanOutMails")) {
//        var check_value6 = $('input[name=ScanOutMails]:checked', '#configure_wizard_detailsAv').val();
//        if (check_value6 == "1" || check_value6 == undefined) {
//            $(".SmtpPortNr1").prop("disabled", false);
//        } else {
//            $(".SmtpPortNr1").prop("disabled", true);
//        }
//    }
//    if ($("div").hasClass("APCEnabled")) {
//        var check_value7 = $('input[name=APCEnabled]:checked', '#configure_wizard_detailsAv').val();
//        if (check_value7 == "1" || check_value7 == 1) {
//            $("#APCAskForUploadEnabled,#OnAccessEnableCloud,#OnAccessEnableCloudGUI").prop("disabled", false);
//            var check_value8 = $('input[name=OnAccessEnableCloud]:checked', '#configure_wizard_detailsAv').val();
//            if (check_value8 == "1" || check_value8 == 1) {
//                $("#OnAccessEnableCloudGUI").prop("disabled", false);
//            } else if (check_value8 == "0" || check_value8 == 0) {
//                $("#OnAccessEnableCloudGUI").prop("disabled", true);
//            }
//        } else {
//            $("#APCAskForUploadEnabled,#OnAccessEnableCloud,#OnAccessEnableCloudGUI").prop("disabled", true);
//        }
//    }
//    if ($("div").hasClass("OnAccessEnableCloud")) {
//        var check_value9 = $('input[name=OnAccessEnableCloud]:checked', '#configure_wizard_detailsAv').val();
//        if (check_value9 == "1" || check_value9 == 1) {
//            $("#OnAccessEnableCloudGUI").prop("disabled", false);
//        } else if (check_value9 == "0" || check_value9 == 0) {
//            $("#OnAccessEnableCloudGUI").prop("disabled", true);
//        }
//    }
}

$(document).keyup(function (e) {
    if (e.keyCode == 27) { // escape key maps to keycode `27`
        $('.modal').modal('hide');
    }
});

function titleHead(tempHead) {
    $(".titleHead").html("");
    var title = tempHead;
    if (title != "") {
        $(".titleHead").append('<span>' + title + '</span>');
    }
}

function iconHead(tempIcon) {
    $("#iconHead").attr("class", "");
    var icon = tempIcon;
    if (icon != "") {
        $("#iconHead").attr('class', '' + icon);
    }
}

function iconHeadAv(tempIcon) {
    var icon = tempIcon;
    if (icon != "") {
        $(".iconHead").attr('class', '' + icon);
    }
}

function checkClick(checkId, checkClickData, dartnumb) {
    $(".collapseFields,.hideChildClass").hide();
    $(".expandFields").show();
    if (dartnumb == "296") {
        // do nothing
    } else {
        $(".constUsbBrandNameEnabled,.constDeviceGuidEnabled,.constDeviceHardwareIdEnabled,.constUsbLimitFileSizeEnabled").hide();
    }
    if (dartnumb == "232" || dartnumb == "233" || dartnumb == "240") {
        switch (dartnumb) {
            case "232":
                $(".c1scripEnabled_232").hide();
                $(".c2scripEnabled_232").show();
                $('.' + checkClickData).fadeIn();
                $('.c' + checkClickData).fadeIn();
                $(".terminateProcess_Dart232,.deleteTermProcess_Dart232,.quarantineProcess_Dart232,.userInterfaceEnabled_Dart232,.deleteConfigChanges_Dart232,.deleteRiskyActions_Dart232,.disableConfigChanges_Dart232,.disableRiskyActions_Dart232,.Scrip232ECS_GroupExecute").show();
                break;
            case "233":
                $(".c1scripEnabled_233").hide();
                $(".c2scripEnabled_233,.runNow_GroupExecute_Dart233").show();
                $('.' + checkClickData).fadeIn();
                $('.c' + checkClickData).fadeIn();
                break;
            case "240":
                $(".c1scripEnabled_240").hide();
                $(".c2scripEnabled_240,.runNow_GroupExecute_Dart240").show();
                $('.' + checkClickData).fadeIn();
                $('.c' + checkClickData).fadeIn();
                break;

            default:
                break;
        }
    } else {

        var classExists = $("div").hasClass(checkClickData);

        if (classExists == "true" || classExists == true) {

            $('.' + checkClickData).fadeIn();
            $('.c' + checkClickData).fadeIn();
            $('#cc1' + checkId).hide();
            $('#cc2' + checkId).fadeIn();

        } else {

            $('.' + checkClickData).fadeIn();
//            $('#cc1' + checkId).hide();
//            $('#cc2' + checkId).fadeIn();

        }

        switch (dartnumb) {
            case "224":
                $(".Scrip224RealTime_Dart224,#cc2S224ScripRunVarName,.Scrip224Schedule,.S2224UpdateNow_Dart224,.S2224SyncActionsNow_Dart224").show();
                $("#cc1S224ScripRunVarName").hide();
                break;
            case "247":
                $(".S00247UpdateFromOtherClients_Dart247,.S00247UpdateFromServer_Dart247,.S00247UpdateFromVendor_Dart247,.S00247ServerLocations_Dart247,.LatestDefVersion_Dart247,.S00247ReportMachines_Dart247,.S247DaysSinceUpdate_Dart247,.MaxRandomDelay_Dart247,.MaxLocalRetries_Dart247,.LocalRetryDelay_Dart247,.reportNoAVApp_Dart247,.S00247Schedule_Dart247,.S00247RunNow_GroupExecute_Dart247").show();
                break;
            case "296":
                $(".constUsbBrandNameEnabled,.constDeviceGuidEnabled,.constDeviceHardwareIdEnabled,.constUsbLimitFileSizeEnabled,.RunNow_GroupExecute,.constUsbAutorunDisable").show();
                if ($("select#S00296UsbDrive").val() == "2") {
                    $(".constS00296ImpersonateUser,.constS00296ImpersonatePassword,.constS00296ImpersonatePassword_confirmation,.constS00296UsbFileType,.constS00296UsbFileSizeType").show();
                } else {
                    $(".constS00296ImpersonateUser,.constS00296ImpersonatePassword,.constS00296ImpersonatePassword_confirmation,.constS00296UsbFileType,.constS00296UsbFileSizeType").hide();
                }
                break;
            case "6":
                $(".S00006OutputList,.S00006RunNowButton_GroupExecute").show();
                break;
            case "12":
                $(".S00012UpdateFromOtherClients,.S00012UpdateFromServer,.S00012UpdateFromVendor,.reportNoAVApp_Dart12,.S00012RunNow_GroupExecute").show();
                break;
            case "27":
                $(".terminateProcess_Dart27,.deleteTermProcess_Dart27,.quarantineProcess_Dart27,.scrip27UIEnabled_Dart27,.deleteAddedItems_Dart27,.restoreDeletedItems_Dart27,.deleteSysUpdateOps_Dart27,.disableAddedItems_Dart27,.disableSysUpdateOps_Dart27,.Scrip27ECS_GroupExecute").show();
                break;
            case "50":
                $(".S0050ProcessTimeEnable").show();
                break;
            case "84":
                $(".scrip84UIEnabled").show();
                break;
            case "88":
                $(".S00088TrafficControl,.S00088RunNowButton_GroupExecute,.S00088StartMonitor_GroupExecute,.S00088StartControlling_GroupExecute").show();
                break;
            case "90":
                $(".S00090UpdateFromOtherClients,.S00090UpdateFromServer,.S00090UpdateFromVendor,.reportNoAVApp_Dart90,.S00090RunNow_GroupExecute").show();
                break;
            case "161":
                $(".Monitor,.RunNowSemaphore_GroupExecute").show();
                break;
            case "176":
                $(".S176ScripLogOnly,.Scrip177VarTrigger,.S00176DumpInvalid_GroupExecute,.S00176RunNowButton_GroupExecute").show();
                break;
            case "192":
                $(".constS00192EnableExeList,.S00192UseStopListList,.constS00192EnableMsiList,.Scrip192UseMsiRunListScrip").show();
                break;
            case "224":
                $(".Scrip224RealTime").show();
                break;
            case "225":
                $(".Monitor,.RunNowSemaphore_GroupExecute").show();
                break;
            case "96":
                $(".S00096RunNowButton_GroupExecute").show();
                break;
            case "97":
                $(".S00097RunNowButton_GroupExecute").show();
                break;
            case "98":
                $(".S00098RunNowButton_GroupExecute").show();
                break;
            case "177":
                $(".Scrip177VarTrigger").show();
                break;
            case "245":
                $(".S00245RunNow_GroupExecute").show();
                break;
            case "282":
                $(".S00282Execute_GroupExecute").show();
                break;
            case "295":
                $("#cc1EnableDriver").hide();
                $(".InstallUSBDriver_GroupExecute,.UninstallUSBDriver_GroupExecute,.LoadUSBDriver_GroupExecute,.UnloadUSBDriver_GroupExecute,#cc2EnableDriver").show();
                break;
            case "297":
                $(".S00297RunNow_GroupExecute").show();
                break;
            case "95":
                $(".S00095RunNowSemaphore_GroupExecute").show();
                break;
            case "228":
                $(".S00228LogNow_GroupExecute").show();
                break;
            case "267":
                $(".S00060RunNow_GroupExecute").show();
                break;
            case "201":
                $("#cc1Arrow_201").hide();
                $(".InstallDriver_GroupExecute,.UninstDriver_GroupExecute,.LoadDriver_GroupExecute,.UnloadDriver_GroupExecute,#cc2Arrow_201").show();
                break;
            case "248":
                $("#cc1Arrow_248").hide();
                $(".Scrip248EnableDriver_GroupExecute,.Scrip248PreventDriver_GroupExecute,.Scrip248UnloadDriver_GroupExecute,#cc2Arrow_248").show();
                break;
                
            case "1001":
                $("#cc1Arrow_1001").hide();
                $(".Scrip1001DeleteCaches,.Scrip1001WebPreview,.Scrip1001TopSites,.Scrip1001LocalStorage,.Scrip1001DefaultReset,.Scrip1001DeleteAll,.S01001Block,.S0S01001WebName,.S01001RunNowButton,#cc2Arrow_1001").show();
                break;
            case "1002":
//                $("#cc1Arrow_1002").hide();
                $(".S01002ScripRun,.S01002SomeLogFileList").show();
                break;
            case "1003":
//                $("#cc1Arrow_1003").hide();
                $(".S01003Scrip,.S01003System,.S01003Hdiejected,.S01003Install,.S01003Schedule").show();
                break;
            case "1004":
                $("#cc1Arrow_1004").hide();
                $("#cc2Arrow_1004,.S01004NewUser,.S01004UserPswd,.S01004UserPswd_confirmation,.S01004DeleteUser,.S01004CopmuterName,.S01004RunNowButton,.S01004ChangePassOfUser,.S01004UserChPswd,.S01004UserChPswd_confirmation,.S01004ResetPasswdNowButton").show();
                break;
            case "1005":
//                $("#cc1Arrow_1005").hide();
                $(".S01005ScripEnable,.Scrip1005FileVault,.S01005UserName,.S01005Password,.S01005Password_confirmation,.S01005RunNowButton").show();
                break;
            case "1006":
//                $("#cc1Arrow_1006").hide();
                $(".S01006Scrip,.S01006FullScan,.S01006ArchivesFileScan,.S01006SuspiciousFileScan,.S01006CompFile,.S01006Disinfect,.S01006RemoveInfected,.S01006RunNowButton,.S01006Schedule").show();
                break;
            case "1007":
                $("#cc1Arrow_1007").hide();
                $("#cc2Arrow_1007,.S01007RunNowButton,.S01007Disk,.Scrip1007RepPermission,.S01007vPermButton,.S01007VerifyButton,.S01007RepairButton").show();
                break;
            case "1008" :
                $("#cc1Arrow_1008").hide();
                $("#cc2Arrow_1008,.S01008RunUnKSources,.S01008RunStatus,.S01008UnKSourceValue").show();
                break;
            case "1009" :
                $(".S01009Printer,.S01009KeyBoard,.S01009Battery,.S01009WebCam,.S01009CD,.S01009Audio,.S01009Hdn,.S01009Finder,.S01009Safari,.S01009Bth,.S01009Restart").show();
                break;
            case "1010" :
                $("#cc1Arrow_1010").hide();
                $("#cc2Arrow_1010,.S01010AppUnblockList,.S01010AppBlockList,.S01010RunNow").show();
                break;
            case "1011" :
                $(".S01011Scrip,.S01011QuickScan,.S01011FullScan,.S01011StatisticsQuickScan,.S01011StatisticsFullScan,.S01011StopQuickScan,.S01011StopFullScan,.S01011Schedule,.S01011RunNowButton").show();
                break;
            case "1012" :
                $(".S01012Scrip,.S01012FullScan,.S01012StopFullScan,.S01012Schedule,.S01012RunNowButton").show();
                break;
            case "1013" :
                $(".Scrip1013ScripRunEnableA,.Scrip1013Schedule,.Scrip1013RunNowB,.Scrip1013RunNowPatchesB").show();
                break;
            case "801" :
                $(".S00801ScripRun,.S00801SomeLogFileList").show();
                break;
            case "802" :
                $(".S00802ScripEnable").show();
                break;
            case "803" :
                $(".S00803ScripEnable").show();
                break;
            case "804" :
                $(".S00804ScripEnable").show();
                break;
            case "805" :
                $(".S00805ScripRun,.S00805SomeLogFileList").show();
                break;
            case "837" :
                $(".Scrip837ScripRunEnableA,.Scrip837ComRetries,.Scrip837ErrorRetry,.Scrip837PropagateRetryTime,.Scrip237DisableDetect,.Scrip837ClientStartupScanD,.Scrip837Schedule,.Scrip837RunNowB,.Scrip837RunNowPatchesB,.Scrip837RunNowResetB").show();
                break;
            case "411" :
                $(".Scrip411Enabled,.S00411WIFIValue,.S00411HotSpotEnabled,.S00411LongitudeandLatitudeEnable,.S00411TimeFence,.S00411RunNowButton_GroupExecute").show();
                break;
            case "412" : 
                $(".Scrip412Enabled,.S00412WhiteListEnabled,S00412BlackListenabled,.S00412RunNowButton").show();
                break;
            case "414" :
                $("#cc1Arrow_414").hide();
                $("#cc2Arrow_414,.S00414WifiEnabled,.S00414selectiveClean,.S00414sms,.S00414contacts,.S00414Videos,.S00414songs,.S00414images,.S00414DirectorySwipEnabled,.S00414RunNowButton").show();
                break;
            case "421" :
                $(".S00421Enabled,.S00421AppURL,.S00421DefaultURL,.S00421Rule,.s00421KeywordFiltering,.s00421SetBookmark,.S00421AddBookmark,.S00421TimeStamps,.s00421FileDownload,.s00421Cookies,.s00421History,.s00421Search,.s00421Password,.s00421Bookmarks,\n\
                    .s00421Copypaste,.s00421Blockedpopup,.s00421LeaveFraudWarning,.s00421PrintPage,.s421password,.s421password_confirmation,.s421StartUp,.s421TurningOff,.s421CustomToolbar,.S00421ContentBlocking,.S0000421Schedule,.S00421RunNowButton,.S00421SendNowButton").show();
                break;
            case "433" : 
                $(".S433ScripEnable,.S433DetectDateTime,.S433DetectIp,.S433DetectSim,.S433DetectUsbChange,.S433BatteryLogs,.S433DetectAudio,.S433MonitorOS,.S433DeviceOnline,.S433RootStatus,.S433ServiceStatus,.S433UnknownSrc,.S433UsbDebug,.S433Run").show();
                break;
            case "442" : 
                $(".S442Scrip,.S442Prof,.S442Policy,.S442Notification,.S442ForceStopPacks,.S442RunForceStop,.S442Run").show();
                break;
            case "448" :
                $("#cc1Arrow_448").hide();
                $("#cc2Arrow_448,.S448DisableBloatware,.S448EnableBloatware,.S448RenameApplications,.S448DisableUninstall,.S448EnableUninstall,.S448Run").show();
                break;
            case "449" :
                $("#cc1Arrow_449").hide();
                $("#cc2Arrow_449,.S449Wifi,.S449Bluetooth,.S449MobileData,.S449Loc,.S449EnableLoc,.S449Win,.S449MobHot,.S449BtTethering,.S449WifiTethering,.S449DisableTethering,.S449Sync,.S449FlgMod,.S449VideoResct,.S449WallprDisble,.S449SDCardDisable,\n\
                    .S449DisableDebugging,.S449DisableMedia,.S449DisableHostStorage,.S449DisableMicroPhone,.S449DisableHeadPhone,.S449DisableTaskbar,.S449DisableFactoryReset,.S449DisableSafeMode,.S449DisableSettingsChange,.S449DisableUpdate,.S449RoamingData,.S449DisableIncmingCall,.S449DisableOutgoingCall,.S449DisableIncomingSms,.S449DisableOutgoingSms,.S449DisableOutInSms,.S449EmergencyCall,.S449EmergencyCall,.S449RoamingVoiceCall,.S449RoamingSync,.S449RoamingPush,.S449AdminRestrction,.S449EncryptSDCard,.S449Run").show();
                break;
            default:
                break;
        }

    }
}

function collapse(para0, para1, para2, dartnumb) {
    if (dartnumb == "232" || dartnumb == "233" || dartnumb == "240") {
        switch (dartnumb) {
            case "232":
                $(".c1scripEnabled_232").show();
                $(".c2scripEnabled_232").hide();
                $('.' + para1).fadeOut();
                $('.' + para2).fadeOut();
                $(".terminateProcess_Dart232,.deleteTermProcess_Dart232,.quarantineProcess_Dart232,.userInterfaceEnabled_Dart232,.deleteConfigChanges_Dart232,.deleteRiskyActions_Dart232,.disableConfigChanges_Dart232,.disableRiskyActions_Dart232,.Scrip232ECS_GroupExecute").hide();
                break;
            case "233":
                $(".c1scripEnabled_233").show();
                $(".c2scripEnabled_233,.runNow_GroupExecute_Dart233").hide();
                $('.' + para1).fadeOut();
                $('.' + para2).fadeOut();
                break;
            case "240":
                $(".c1scripEnabled_240").show();
                $(".c2scripEnabled_240,.runNow_GroupExecute_Dart240").hide();
                $('.' + para1).fadeOut();
                $('.' + para2).fadeOut();
                break;
            default:
                break;
        }
    } else {
        switch (dartnumb) {
            case "224":
                $(".Scrip224RealTime_Dart224,#cc2S224ScripRunVarName,.Scrip224Schedule,.S2224UpdateNow_Dart224,.S2224SyncActionsNow_Dart224").hide();
                $("#cc1S224ScripRunVarName").show();
                break;
            case "247":
                $(".S00247UpdateFromOtherClients_Dart247,.S00247UpdateFromServer_Dart247,.S00247UpdateFromVendor_Dart247,.S00247ServerLocations_Dart247,.LatestDefVersion_Dart247,.S00247ReportMachines_Dart247,.S247DaysSinceUpdate_Dart247,.MaxRandomDelay_Dart247,.MaxLocalRetries_Dart247,.LocalRetryDelay_Dart247,.reportNoAVApp_Dart247,.S00247Schedule_Dart247,.S00247RunNow_GroupExecute_Dart247").hide();
                break;
            case "267":
                $(".S00060RunNow_GroupExecute").hide();
                break;
            case "297":
                $(".S00297RunNow_GroupExecute").hide();
                break;
            case "296":
                $(".constUsbBrandNameEnabled,.constDeviceGuidEnabled,.constDeviceHardwareIdEnabled,.constUsbLimitFileSizeEnabled,.RunNow_GroupExecute,.constUsbAutorunDisable").hide();
                break;
            case "6":
                $(".S00006OutputList,.S00006RunNowButton_GroupExecute").hide();
                break;
            case "12":
                $(".S00012UpdateFromOtherClients,.S00012UpdateFromServer,.S00012UpdateFromVendor,.reportNoAVApp_Dart12,.S00012RunNow_GroupExecute").hide();
                break;
            case "27":
                $(".terminateProcess_Dart27,.deleteTermProcess_Dart27,.quarantineProcess_Dart27,.scrip27UIEnabled_Dart27,.deleteAddedItems_Dart27,.restoreDeletedItems_Dart27,.deleteSysUpdateOps_Dart27,.disableAddedItems_Dart27,.disableSysUpdateOps_Dart27,.Scrip27ECS_GroupExecute").hide();
                break;
            case "50":
                $(".S0050ProcessTimeEnable").hide();
                break;
            case "84":
                $(".scrip84UIEnabled").hide();
                break;
            case "88":
                $(".S00088TrafficControl,.S00088RunNowButton_GroupExecute,.S00088StartMonitor_GroupExecute,.S00088StartControlling_GroupExecute").hide();
                break;
            case "90":
                $(".S00090UpdateFromOtherClients,.S00090UpdateFromServer,.S00090UpdateFromVendor,.reportNoAVApp_Dart90,.S00090RunNow_GroupExecute").hide();
                break;
            case "161":
                $(".Monitor,.RunNowSemaphore_GroupExecute").hide();
                break;
            case "176":
                $(".S176ScripLogOnly,.Scrip177VarTrigger,.S00176DumpInvalid_GroupExecute,.S00176RunNowButton_GroupExecute").hide();
                break;
            case "192":
                $(".constS00192EnableExeList,.S00192UseStopListList,.constS00192EnableMsiList,.Scrip192UseMsiRunListScrip").hide();
                break;
            case "224":
                $(".Scrip224RealTime").hide();
                break;
            case "225":
                $(".Monitor,.RunNowSemaphore_GroupExecute").hide();
                break;
            case "96":
                $(".S00096RunNowButton_GroupExecute").hide();
                break;
            case "97":
                $(".S00097RunNowButton_GroupExecute").hide();
                break;
            case "98":
                $(".S00098RunNowButton_GroupExecute").hide();
                break;
            case "177":
                $(".Scrip177VarTrigger").hide();
                break;
            case "245":
                $(".S00245RunNow_GroupExecute").hide();
                break;
            case "282":
                $(".S00282Execute_GroupExecute").hide();
                break;
            case "295":
                $("#cc1EnableDriver").show();
                $(".InstallUSBDriver_GroupExecute,.UninstallUSBDriver_GroupExecute,.LoadUSBDriver_GroupExecute,.UnloadUSBDriver_GroupExecute,#cc2EnableDriver").hide();
                break;
            case "95":
                $(".S00095RunNowSemaphore_GroupExecute").hide();
                break;
            case "228":
                $(".S00228LogNow_GroupExecute").hide();
                break;
            case "201":
                $("#cc1Arrow_201").show();
                $(".InstallDriver_GroupExecute,.UninstDriver_GroupExecute,.LoadDriver_GroupExecute,.UnloadDriver_GroupExecute,#cc2Arrow_201").hide();
                break;
            case "248":
                $("#cc1Arrow_248").show();
                $(".Scrip248EnableDriver_GroupExecute,.Scrip248PreventDriver_GroupExecute,.Scrip248UnloadDriver_GroupExecute,#cc2Arrow_248").hide();
                break;
            case "1001":
                $("#cc1Arrow_1001").show();
                $(".Scrip1001DeleteCaches,.Scrip1001WebPreview,.Scrip1001TopSites,.Scrip1001LocalStorage,.Scrip1001DefaultReset,.Scrip1001DeleteAll,.S01001Block,.S0S01001WebName,.S01001RunNowButton,#cc2Arrow_1001").hide();
                break;
            case "1002":
                $(".S01002SomeLogFileList").hide();
                break;
            case "1003":
                $(".S01003System,.S01003Hdiejected,.S01003Install,.S01003Schedule").hide();
                break;
            case "1004":
                $("#cc1Arrow_1004").show();
                $("#cc2Arrow_1004,.S01004NewUser,.S01004UserPswd,.S01004UserPswd_confirmation,.S01004DeleteUser,.S01004CopmuterName,.S01004RunNowButton,.S01004ChangePassOfUser,.S01004UserChPswd,.S01004UserChPswd_confirmation,.S01004ResetPasswdNowButton").hide();
                break;
            case "1005":
                $(".Scrip1005FileVault,.S01005UserName,.S01005Password,.S01005Password_confirmation,.S01005RunNowButton").hide();
                break;
            case "1006":
                $(".S01006FullScan,.S01006ArchivesFileScan,.S01006SuspiciousFileScan,.S01006CompFile,.S01006Disinfect,.S01006RemoveInfected,.S01006RunNowButton,.S01006Schedule").hide();
            break;
            case "1007":
                $("#cc1Arrow_1007").show();
                $("#cc2Arrow_1007,.S01007RunNowButton,.S01007Disk,.Scrip1007RepPermission,.S01007vPermButton,.S01007VerifyButton,.S01007RepairButton").hide();
                break;
            case "1008" :
                $("#cc1Arrow_1008").show();
                $("#cc2Arrow_1008,.S01008RunUnKSources,.S01008RunStatus,.S01008UnKSourceValue").hide();
                break;
            case "1009" :
                $(".S01009Printer,.S01009KeyBoard,.S01009Battery,.S01009WebCam,.S01009CD,.S01009Audio,.S01009Hdn,.S01009Finder,.S01009Safari,.S01009Bth,.S01009Restart").hide();
                break;
            case "1010" :
                $("#cc1Arrow_1010").show();
                $("#cc2Arrow_1010,.S01010AppUnblockList,.S01010AppBlockList,.S01010RunNow").hide();
                break;
            case "1011" :
                $(".S01011QuickScan,.S01011FullScan,.S01011StatisticsQuickScan,.S01011StatisticsFullScan,.S01011StopQuickScan,.S01011StopFullScan,.S01011Schedule,.S01011RunNowButton").hide();
                break;
            case "1012" :
                $(".S01012FullScan,.S01012StopFullScan,.S01012Schedule,.S01012RunNowButton").hide();
                break;
            case "1013" :
                $(".Scrip1013Schedule,.Scrip1013RunNowB,.Scrip1013RunNowPatchesB").hide();
                break;
                case "801" :
                $(".S00801ScripRun,.S00801SomeLogFileList").show();
                break;
            case "802" :
                $(".S00802ScripEnable").show();
                break;
            case "803" :
                $(".S00803ScripEnable").show();
                break;
            case "804" :
                $(".S00804ScripEnable").show();
                break;
            case "805" :
                $(".S00805ScripRun,.S00805SomeLogFileList").show();
            break;
            case "837" :
                $(".Scrip837ScripRunEnableA,.Scrip837ComRetries,.Scrip837ErrorRetry,.Scrip837PropagateRetryTime,.Scrip237DisableDetect,.Scrip837ClientStartupScanD,.Scrip837Schedule,.Scrip837RunNowB,.Scrip837RunNowPatchesB,.Scrip837RunNowResetB").show();
                break;
            case "411" :
                $(".S00411WIFIValue,.S00411HotSpotEnabled,.S00411LongitudeandLatitudeEnable,.S00411TimeFence,.S00411RunNowButton_GroupExecute").hide();
                break;
            case "412" : 
                $(".S00412WhiteListEnabled,S00412BlackListenabled,.S00412RunNowButton").hide();
                break;
            case "414" :
                $('#cc1Arrow_414').show();
                $("#cc2Arrow_414,.S00414WifiEnabled,.S00414selectiveClean,.S00414sms,.S00414contacts,.S00414Videos,.S00414songs,.S00414images,.S00414DirectorySwipEnabled,.S00414RunNowButton").hide();
                break;
            case "421" :
                $(".S00421AppURL,.S00421DefaultURL,.S00421Rule,.s00421KeywordFiltering,.s00421SetBookmark,.S00421AddBookmark,.S00421TimeStamps,.s00421FileDownload,.s00421Cookies,.s00421History,.s00421Search,.s00421Password,.s00421Bookmarks,\n\
                    .s00421Copypaste,.s00421Blockedpopup,.s00421LeaveFraudWarning,.s00421PrintPage,.s421password,.s421password_confirmation,.s421StartUp,.s421TurningOff,.s421CustomToolbar,.S00421ContentBlocking,.S0000421Schedule,.S00421RunNowButton,.S00421SendNowButton").hide();
                break;
            case "433" : 
                $(".S433DetectDateTime,.S433DetectIp,.S433DetectSim,.S433DetectUsbChange,.S433BatteryLogs,.S433DetectAudio,.S433MonitorOS,.S433DeviceOnline,.S433RootStatus,.S433ServiceStatus,.S433UnknownSrc,.S433UsbDebug,.S433Run").hide();
                break;
            case "442" : 
                $(".S442Prof,.S442Policy,.S442Notification,.S442ForceStopPacks,.S442RunForceStop,.S442Run").hide();
                break;
            case "448" :
                $('#cc1Arrow_448').show();
                $("#cc2Arrow_448,.S448DisableBloatware,.S448EnableBloatware,.S448RenameApplications,.S448DisableUninstall,.S448EnableUninstall,.S448Run").hide();
                break;
            case "449" :
                $("#cc1Arrow_449").show();
                $("#cc2Arrow_449,.S449Wifi,.S449Bluetooth,.S449MobileData,.S449Loc,.S449EnableLoc,.S449Win,.S449MobHot,.S449BtTethering,.S449WifiTethering,.S449DisableTethering,.S449Sync,.S449FlgMod,.S449VideoResct,.S449WallprDisble,.S449SDCardDisable,\n\
                    .S449DisableDebugging,.S449DisableMedia,.S449DisableHostStorage,.S449DisableMicroPhone,.S449DisableHeadPhone,.S449DisableTaskbar,.S449DisableFactoryReset,.S449DisableSafeMode,.S449DisableSettingsChange,.S449DisableUpdate,.S449RoamingData,.S449DisableIncmingCall,.S449DisableOutgoingCall,.S449DisableIncomingSms,.S449DisableOutgoingSms,.S449DisableOutInSms,.S449EmergencyCall,.S449EmergencyCall,.S449RoamingVoiceCall,.S449RoamingSync,.S449RoamingPush,.S449AdminRestrction,.S449EncryptSDCard,.S449Run").hide();
                break;
            default:
                break;
        }
        $('#cc1' + para0).fadeIn();
        $('#cc2' + para0).hide();
        $('.' + para1).fadeOut();
        $('.' + para2).fadeOut();
    }
}

function closeConfigFunc(getTab, tabVal, profileName) {
    alert("hi");
    $("#getTabId").val(getTab);
    $("#getTabVal").val(tabVal);
    $("#getprofileName").val(profileName);
    $("#LI_addNewConfiguration").hide();

    $("#nhdesc,#avdesc,#configure_wizard_details,#configure_wizard_detailsAv,#add_avira_scheduler").hide();
    $("#allConfigureContent").fadeIn();

    var tabValue = "tab_" + tabVal;

    $.ajax({
        url: "wizFunc.php",
        data: "act=getLeftListData&name=" + profileName + "&tab=" + tabValue,
        dataType: "text",
        success: function (html) {
//            console.log(html);
            $("#nhdisp").show();
            $("#nhdisp").html("");
            $("#nhdisp").append(html);
        }
    });
}

function closeConfigAvFunc(getTab, tabVal, profileName) {
    $("#getTabId").val(getTab);
    $("#nhdesc,#avdesc,#configure_wizard_detailsAv,#configure_wizard_details").hide();
    $("#allConfigureContent").fadeIn();
    var tabValue = "tabav_" + tabVal;
    $("#profileName").val(profileName);
    if (profileName == "Scheduler") {
        $(".services-table").fadeIn();
        Get_SchedulerData();

        $(".phase1,#nextSc,#goBackToMainSc").show();
        $("#phasecounter").val("0");
        $(".phase2,.phase3,.phase4,.phase5,.frequencybinder,#backSc,#submitSc").hide();
        $(".form-control").val("").change();
        $("input[type=checkbox]").prop("checked", false);
        $(".selectpicker").selectpicker('refresh');

    } else {
        $(".services-table,#add_avira_scheduler").hide();
    }

    $.ajax({
        url: "wizFunc.php",
        data: "act=getLeftListDataAv&name=" + profileName + "&tab=" + tabValue,
        dataType: "text",
        success: function (html) {
//            console.log(html);
            $("#avdisp").show();
            $("#avtabs").html("");
            $("#avtabs").append(html);
        }
    });
}

function accordionClick(type) {
    var Level = $('#valueSearch').val();
    if (Level == "All") {
        $(".avoid").prop("disabled", true);
        showModal("2", "<span>Please select SITE from right pane, to access Configuration Module.</span><br><span>(NOTE: This feature is not available for scope 'ALL')</span>");
    } else {
        $(".avoid").prop("disabled", false);
        if (type == "Avira") {
            $("#avdisp,#nhdesc,#nhdisp,#configure_wizard_details,#configure_wizard_detailsAv").hide();
            $("#avdesc").show();
            $("#nhActive").removeClass("active");
            $("#avActive").addClass("active");
        } else if (type == "Nanoheal") {
            $("#avdisp,#avdesc,#nhdisp,#configure_wizard_details,#configure_wizard_detailsAv").hide();
            $("#nhdesc").show();
            $("#avActive").removeClass("active");
            $("#nhActive").addClass("active");
        }
    }

}

function goBackToMainFn() {
    $("#configure_wizard_details").hide();
    $("#allConfigureContent").fadeIn();
    $(".service-desc").hide();
    $("#LI_addNewConfiguration").hide();
    var tab_id = $("#getTabId").val();
    $("" + tab_id).fadeIn();
}

function ViewProfileDetails(Id, Name) {
//    $(".se-pre-con").show();
    $("#popupLoader").show();
    var wiz_name_id = Id;
    $("#auditDetailsDiv").html("");
    $("#auditTitle").html("<span>Audit Details for </span>" + "<span>" + Name + "</span>");
    $("#detailsPopup").modal("show");
    $.ajax({
        url: "wizFunc.php?act=showWizards&wiz_name_id=" + wiz_name_id,
        type: "POST",
        dataType: "json",
        data: '{}',
        async: true,
        success: function (data) {

            $("#ajaxloaderr").hide();
            $(".se-pre-con").hide();
            $("#popupLoader").hide();

//            var row_restrict = ['631','37','766','707','746','902','747','903','748','904','750',''];
            var html_data = [];
            jQuery.each(data, function (i, val) {
                var ele = "";
                var wizEnteredData = getWizValueEnteredData(val.Ordr, wiz_name_id);
                if (val.GUIType === "checkbox") {
                    var checkData = val.EnteredValues;
                    var str = checkData.split("/");

                    var checkChild = 0;
                    if (val.ShowChild == "yes") {
                        checkChild = " style='display:block;'";
                    } else {
                        checkChild = " style='display:none;'";
                    }

                    var arrowOnly = '';
                    if(val.VarID == 'Arrow_1001' || val.VarID == 'Arrow_1004' || val.VarID == 'Arrow_1008' || val.VarID == 'Arrow_1010' || val.VarID == 'Arrow_1007' || val.VarID == 'Arrow_414' || val.VarID == 'Arrow_448' || val.VarID == 'Arrow_449') {
                        arrowOnly = "";
                    } else {
                        arrowOnly = 'checked="true"';
                    }

                    if (str[0] === $.trim(wizEnteredData)) { //str[0] - only for checked data

                        ele = ''
                                + '<div class="panel-heading" ' + checkChild + '>'
                                + '<div class="panel-title clearfix">'
                                + '<div class="service-left">'
                                + '<div class="form-group">'
                                + '<div class="checkbox">'
                                + '<label>' + val.Name + '<input type="checkbox" class="form-control"' + arrowOnly + 'disabled="true"><span class="checkbox-material"><span class="check" style="border-color: #48b2e4 !important;"></span></span></label>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '';

                    } else {

                        ele = ''
                                + '<div class="panel-heading" ' + checkChild + '>'
                                + '<div class="panel-title clearfix">'
                                + '<div class="service-left">'
                                + '<div class="form-group">'
                                + '<div class="checkbox">'
                                + '<label>' + val.Name + '<input type="checkbox" class="form-control" disabled="true"><span class="checkbox-material"><span class="check" style="border-color: #48b2e4 !important;"></span></span></label>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '';
                    }

                }
                html_data.push(ele);
            });
            var wiz = '<input type="button" id="wizid" value="' + wiz_name_id + '" style="display:none;"/>';
            $("#auditDetailsDiv").append(html_data.join("\n") + wiz);
        }
    });
}

function showWizard(wiznameid) {

    wiznameId = wiznameid;
    var Level = $('#valueSearch').val();
    if (Level == "All") {
        $(".avoid").prop("disabled", true);
        showModal("2", "<span>Please select SITE from right pane, to access Configuration Module.</span><br><span>(NOTE: This feature is not available for scope 'ALL')</span>");
    } else {
        var wiz_name_id = wiznameid.trim();
//    $("#ajaxloaderr").html('<img src="../images/loading.gif" class="loadhome" alt="loading..." style="width:130px !important;margin-left:43% !important;margin-top: 17% !important;"/>');
//        $("#ajaxloaderr").show();
//        $(".se-pre-con").show();
        $("#configure_wizard_details #var_details").html("");
        $("#configure_wizard_details").show();
        $("#ajaxloaderr").show();
        $(".service-desc").show();
        $("#allConfigureContent").hide();
        $("#wizid").val(wiz_name_id);
        $.ajax({
            url: "wizFunc.php?act=showWizards&wiz_name_id=" + wiz_name_id,
            type: "GET",
            dataType: "json",
            data: '{}',
            async: true,
            success: function (data) {
                var RoleValues = "";
                $.ajax({
                    url: 'wizFunc.php',
                    data: 'act=GET_WMRoleData',
                    type: 'GET',
                    dataType: 'text',
                    async: false,
                    success: function (RoleData) {
                        if (RoleData == "empty") {
                            RoleValues = RoleData;
                        } else {
                            RoleValues = JSON.parse(RoleData);
                        }

                    }
                });

                //$("#ajaxloaderr").hide();
//                $(".se-pre-con").hide();
//                $("#LI_addNewConfiguration").show();
                var html_data = [];
                jQuery.each(data, function (i, val) {

                    var role1 = ' display:none;';
                    var role4 = ' disabled="true"';

                    var uniqId = val.VarID + "JR" + val.DartNo;


                    if (RoleValues == "empty") {
                        role1 = ' display:none;';
                        role4 = ' disabled="true"';
                    } else {
                        var RoleVal = RoleValues.darts[0][val.DartNo][0][val.VarID];
                        switch (RoleVal) {
                            case "2":
                                role1 = ' display:block;';
                                role4 = ' ';
                                break;
                            case "1":
                                role1 = ' display:block;';
                                role4 = ' disabled="true"';
                                break;
                            case "0":
                                role1 = ' display:none;';
                                role4 = ' disabled="true"';
                                break;
                            default:
                                role1 = ' display:none;';
                                role4 = ' disabled="true"';
                                break;
                        }
                    }

                    var ele = "";
                    var wizEnteredData = getWizValueEnteredData(val.Ordr, wiz_name_id);
//                    preventereddata[val.VarID] = escape(wizEnteredData.replace(/(?:\r\n|\r|\n)/g, '##'));
                    if (val.GUIType === "date") {
                        var checkVal = val.DartNo;
                        preventereddata[val.VarID] = escape(wizEnteredData);
                        ele = '<li class="collapseFields c' + val.DartNo + ' ' + val.VarID + '" style="display: none;">'
                                + '<div class="panel-heading" style="' + role1 + '">'
                                + '<div class="panel-title clearfix">'
                                + '<div class="service-left">'
                                + '<div class="form-group c' + val.DartNo + '">'
                                + '<label class="control-label" for="' + val.VarID + '">' + val.Name + '</label>'
                                + '<input required="" class="form-control form_datetime date_format ' + uniqId + '" id="' + val.VarID + '" name="' + val.VarID + '" type="text" value="' + $.trim(wizEnteredData) + '" ' + role4 + ' readonly>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</li>';


                    } else if (val.GUIType === "text") {
                        preventereddata[val.VarID] = escape(wizEnteredData);
                        var checkVal = val.DartNo;

                        ele = '<li class="collapseFields c' + val.DartNo + ' ' + val.VarID + '" style="display: none;">'
                                + '<div class="panel-heading" style="' + role1 + '">'
                                + '<div class="panel-title clearfix">'
                                + '<div class="service-left">'
                                + '<div class="form-group c' + val.DartNo + '">'
                                + '<label class="control-label" for="' + val.VarID + '">' + val.Name + '</label>'
                                + '<input required="" placeholder="Please enter ' + val.Name + '" class="form-control ' + uniqId + '" id="' + val.VarID + '" name="' + val.VarID + '" type="text" value="' + $.trim(wizEnteredData) + '" ' + role4 + '>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</li>';


                    } else if (val.GUIType === "checkbox") {
                        var ip1 = "";
                        var ip2 = "";
                        var checkData = val.EnteredValues;
                        var str = checkData.split("/");

                        if (val.VarID == "scripEnabled" && val.DartNo == "232") {
                            ip1 = "c1scripEnabled_232";
                            ip2 = "c2scripEnabled_232";
                        } else if (val.VarID == "scripEnabled" && val.DartNo == "233") {
                            ip1 = "c1scripEnabled_233";
                            ip2 = "c2scripEnabled_233";
                        } else if (val.VarID == "scripEnabled" && val.DartNo == "240") {
                            ip1 = "c1scripEnabled_240";
                            ip2 = "c2scripEnabled_240";
                        } else {
                            ip1 = "";
                            ip2 = "";
                        }
                        var checkParent = "";
                        var checkParentFlag = 0;
                        if (val.CheckParent == "yes") {
                            checkParentFlag = 1;

                        } else {
                            checkParentFlag = 0;
                        }

                        var checkChild = 0;
                        var childClass = "";
                        var arrowOnly = "";
                        if (val.ShowChild == "yes") {
                            checkChild = " style='display:block;'";
                            childClass = "";
                        } else {
                            checkChild = " style='display:none;'";
                            childClass = "hideChildClass";
                        }

                        if (val.DartNo == "201" || val.DartNo == "248") {
                            arrowOnly = " style='display:none;'";
                        } else {
                            arrowOnly = " style='display:block;'";
                        }
                        // for mac configuration
                        if(val.VarID == 'Arrow_1001' || val.VarID == 'Arrow_1004' || val.VarID == 'Arrow_1008' || val.VarID == 'Arrow_1010' || val.VarID == 'Arrow_1007' || val.VarID == 'Arrow_414' || val.VarID == 'Arrow_448' || val.VarID == 'Arrow_449') {
                            arrowOnly = " style='display:none;'";
                        }


//                        var ArrowDarts = ['S00070ScripRun', 'S00017ScripRun', 'S00297FileEnable', 'S00088Enabled', 'S00045ScripRunVar', 'ScripRunVarName', 'S00176ScripRun', 'scrip84UIEnabled', 'S00060ScripRunNew', 'cc1scripEnabled', 'scripEnabled', 'S189ScripRunVarName', 'S188ScripRunVarName', 'S00228ScripEnabled', 'S00296ScripEnabled', 'S00069EnableScrip', 'S00006ScripRun', 'S00012ScripRun', 'S00016ScripRun', 'scrip27Enabled', 'S00050ScripRun', 'S00071ScripRun', 'S00090ScripRun', 'S00060RunNow', 'S00096Run', 'S00097Run', 'S00098Run', 'Scrip177Enabled', 'S00192Enabled', 'S00199ScripRun', 'S00211Enable', 'S00245ScripEnable', 'S00282ScripEnable', 'EnableDriver'];
//                        if (custType == "admin@nanoheal.com") {

                        //alert('str[0]: '+str[0]+' $.trim(wizEnteredData): '+$.trim(wizEnteredData));
                        if (str[0] === $.trim(wizEnteredData)) { //str[0] - only for checked data
                            preventereddata[val.VarID] = escape(wizEnteredData);
                            checkVal = 'c' + val.DartNo;
                            ele = '<li class="' + val.VarID + ' ' + val.VarID + '_Dart' + val.DartNo + ' ' + childClass + '" ' + checkChild + '>'
                                    + '<div class="panel-heading" style="' + role1 + '">'
                                    + '<div class="panel-title clearfix">'
                                    + '<div class="service-left">'
                                    + '<div class="form-group">'
                                    + '<div class="checkbox">'
                                    + '<label>' + val.Name + '<input type="checkbox" class="' + uniqId + '" id="' + val.VarID + '" name="' + val.VarID + '" value="' + $.trim(wizEnteredData) + '" checked ' + role4 + '><span class="checkbox-material" onclick="chck(\'' + val.VarID + '\')" ' + arrowOnly + '><span class="check"></span></span></label>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>';
                            //if (ArrowDarts.indexOf(val.VarID) != -1) {
                            if (checkParentFlag == 1) {
                                ele += '<div id="cc1' + val.VarID + '" class="expandFields service-right ' + ip1 + '" onclick="checkClick(\'' + val.VarID + '\',\'' + checkVal + '\',\'' + val.DartNo + '\');" ' + checkParent + '><a href="javascript:;"></a></div>'
                                        + '<div id="cc2' + val.VarID + '" class="collapseFields material-icons icon-ic_expand_more_24px ' + ip2 + '" onclick="collapse(\'' + val.VarID + '\',\'' + checkVal + '\',\'cc' + val.DartNo + '\',\'' + val.DartNo + '\')" style="display: none;color: black;float: right;cursor: pointer;"><a href="javascript:;"></a></div>';
                            }
                            //}

                            ele += '</div>'
                                    + '</div>'
                                    + '</li>';

                        } else {
                            preventereddata[val.VarID] = "";
                            checkVal = 'c' + val.DartNo;
                            ele = '<li class="' + val.VarID + ' ' + val.VarID + '_Dart' + val.DartNo + ' ' + childClass + '" ' + checkChild + '>'
                                    + '<div class="panel-heading" style="' + role1 + '">'
                                    + '<div class="panel-title clearfix">'
                                    + '<div class="service-left">'
                                    + '<div class="form-group">'
                                    + '<div class="checkbox">'
                                    + '<label>' + val.Name + '<input type="checkbox" class="' + uniqId + '" id="' + val.VarID + '" name="' + val.VarID + '" value="' + $.trim(wizEnteredData) + '" ' + role4 + '><span class="checkbox-material" onclick="chck(\'' + val.VarID + '\')" ' + arrowOnly + '><span class="check"></span></span></label>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>';

                            //if (ArrowDarts.indexOf(val.VarID) != -1) {
                            if (checkParentFlag == 1) {
                                ele += '<div id="cc1' + val.VarID + '" class="expandFields service-right ' + ip1 + '" onclick="checkClick(\'' + val.VarID + '\',\'' + checkVal + '\',\'' + val.DartNo + '\');" ' + checkParent + '><a href="javascript:;"></a></div>'
                                        + '<div id="cc2' + val.VarID + '" class="collapseFields material-icons icon-ic_expand_more_24px ' + ip2 + '" onclick="collapse(\'' + val.VarID + '\',\'' + checkVal + '\',\'cc' + val.DartNo + '\',\'' + val.DartNo + '\')" style="display: none;color: black;float: right;cursor: pointer;"><a href="javascript:;"></a></div>';
                            }
                            //}
                            ele += '</div>'
                                    + '</div>'
                                    + '</li>';
                        }

                    } else if (val.GUIType === "TextArea-A") {
                        preventereddata[val.VarID] = escape(wizEnteredData.replace(/(?:\r\n|\r|\n)/g, '##'));
                        ele = '<li class="collapseFields c' + val.DartNo + ' ' + val.VarID + '" style="display: none;">'
                                + '<div class="panel-heading" style="' + role1 + '">'
                                + '<div class="panel-title clearfix">'
                                + '<div class="service-left">'
                                + '<div class="form-group c' + val.DartNo + '" style="display: none;">'
                                + '<label class="control-label" for="' + val.VarID + '">' + val.Name + '</label>'
                                + '<textarea required="" placeholder="Please enter ' + val.Name + '" class="form-control ' + uniqId + '" rows="3" id="' + val.VarID + '" ' + role4 + ' style="height: 75px !important;">' + $.trim(wizEnteredData) + '</textarea>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</li>';
//                          
                    } else if (val.GUIType === "select") { //dropdown
                        preventereddata[val.VarID] = escape(wizEnteredData.replace(/(?:\r\n|\r|\n)/g, '##'));
                        var str = val.EnteredValues;
                        var optiondata = str.split("/");
                        var selectValues = val.SelectEnteredValues;
                        var selectEnteredValuesData = selectValues.split("/");
                        var optionlist = "";

                        for (var i = 0; i < optiondata.length; i++) {
                            if ($.trim(wizEnteredData) === $.trim(optiondata[i])) {
                                optionlist += '<option id="' + optiondata[i] + '"  value="' + optiondata[i] + '" selected>' + selectEnteredValuesData[i] + '</option>';
                            } else {
                                optionlist += '<option id="' + optiondata[i] + '"  value="' + optiondata[i] + '">' + selectEnteredValuesData[i] + '</option>';
                            }
                        }

                        ele = '<li class="collapseFields c' + val.DartNo + ' ' + val.VarID + '" style="display: none;">'
                                + '<div class="panel-heading" style="' + role1 + '">'
                                + '<div class="panel-title clearfix">'
                                + '<div class="service-left">'
                                + '<div class="form-group c' + val.DartNo + '">'
                                + '<select class="form-control ' + uniqId + ' selectpicker dropdown-submenu ' + val.VarID + '" id="' + val.VarID + '"  title="' + val.Name + '" onchange="HideShowFields(&quot;' + val.VarID + '&quot;)" data-size="5" data-width="100%" ' + role4 + '>'
                                + optionlist
                                + '</select>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</li>';

                    } else if (val.GUIType === "multiselect") { //multiselect dropdown with checkbox
                        preventereddata[val.VarID] = escape(wizEnteredData.replace(/(?:\r\n|\r|\n)/g, '##'));
                        var wiz_mstr_VSF = val.ValueSourceField;

                        var optionlist = getMultiSelection_option(wiz_mstr_VSF, val.Ordr, wiz_name_id);
                        //console.log("multiselect option: "+optionlist);
                        ele = '<li class="collapseFields c' + val.DartNo + ' ' + val.VarID + '" style="display: none;">'
                                + '<div class="panel-heading" style="' + role1 + '">'
                                + '<div class="panel-title clearfix">'
                                + '<div class="service-left">'
                                + '<div class="form-group c' + val.DartNo + '">'
                                + '<label>' + val.Name + '</label>'
                                + '<select class="form-control ' + uniqId + ' multiple-select selectpicker dropdown-submenu ' + val.VarID + '" id="' + val.VarID + '"  title="' + val.Name + '" data-size="5" data-width="100%" multiple="" ' + role4 + '>'
                                + optionlist
                                + '</select>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</li>';

                    } else if (val.GUIType === "textarea") {
                        preventereddata[val.VarID] = escape(wizEnteredData.replace(/(?:\r\n|\r|\n)/g, '##'));
                        var style = "";
                        var A_Tag_Style = " display:none;";

//                            if(wizEnteredData!==""){
                        var ta_data = getTextAreaFieldData(val.Ordr, wiz_name_id, val.VarID, val.DartNo);

                        if (ta_data.match(/<td>/g) == null || ta_data.match(/<td>/g) == "" || ta_data.match(/<td>/g) == "undefined") {
                            style = "display:none;";
                        } else {
                            style = "display:block;";
                        }

                        ele = '<li class="collapseFields c' + val.DartNo + ' ' + val.VarID + '" style="display:none">'
                                + '<div class="panel-heading" style="' + role1 + '">'
                                + '<div class="panel-title clearfix">'
                                + '<div class="service-left">'
                                + '<div class="form-group c' + val.DartNo + '">'
                                + '<label>' + val.Name + '</label>';
                        if (ta_data.includes('<td>') == false) {
                            A_Tag_Style = " display:block;";
                        }
                        if(RoleVal === '2') {
                        ele += '<a href="" class="' + val.VarID + '_' + val.DartNo + '_A" data-toggle="modal" data-target="#subformTextAreaField" style="cursor:pointer; color:#48b2e4;' + A_Tag_Style + '" onclick="createTextAreaForm(\'add\',\'' + val.Ordr + '\',\'' + val.VarID + '\',\'\',\'\',\'' + val.DartNo + '\')" >Click to add a new configuration</a>';
                        } else {
                            ele += '<a href="" class="' + val.VarID + '_' + val.DartNo + '_A" data-toggle="modal" data-target="" style="cursor:default; color:#48b2e4;' + A_Tag_Style + '" onclick="createTextAreaForm(\'add\',\'' + val.Ordr + '\',\'' + val.VarID + '\',\'\',\'\',\'' + val.DartNo + '\')" >Click to add a new configuration</a>';
                        }
//                        ele += '<a href="" class="' + val.VarID + '_' + val.DartNo + '_A" data-toggle="modal" data-target="#subformTextAreaField" style="cursor:pointer; color:#48b2e4;' + A_Tag_Style + '" onclick="createTextAreaForm(\'add\',\'' + val.Ordr + '\',\'' + val.VarID + '\',\'\',\'\',\'' + val.DartNo + '\')" >Click to add a new configuration</a>';
                        ele += '<span name="' + val.VarID + '" id="' + val.VarID + '" style="font-weight: bold;' + style + '">' + ta_data + '</span>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</li>';

                    } else if (val.GUIType === "button") {
                        
                        var ip1 = "";
                        var ip2 = "";
                        var checkData = val.EnteredValues;
                        var str = checkData.split("/");

                        if (val.VarID == "scripEnabled" && val.DartNo == "232") {
                            ip1 = "c1scripEnabled_232";
                            ip2 = "c2scripEnabled_232";
                        } else if (val.VarID == "scripEnabled" && val.DartNo == "233") {
                            ip1 = "c1scripEnabled_233";
                            ip2 = "c2scripEnabled_233";
                        } else if (val.VarID == "scripEnabled" && val.DartNo == "240") {
                            ip1 = "c1scripEnabled_240";
                            ip2 = "c2scripEnabled_240";
                        } else {
                            ip1 = "";
                            ip2 = "";
                        }
                        var checkParent = "";
                        var checkParentFlag = 0;
                        if (val.CheckParent == "yes") {
                            checkParentFlag = 1;

                        } else {
                            checkParentFlag = 0;
                        }

                        var checkChild = 0;
                        var childClass = "";
                        if (val.ShowChild == "yes") {
                            checkChild = " style='display:block;'";
                            childClass = "";
                        } else {
                            checkChild = " style='display:none;'";
                            childClass = "hideChildClass";
                        }
                        if (str[0] === $.trim(wizEnteredData)) {
                            preventereddata[val.VarID] = escape(wizEnteredData);
                            checkVal = 'c' + val.DartNo;
                            ele = '<li class="' + val.VarID + ' ' + val.VarID + '_Dart' + val.DartNo + ' ' + childClass + '" ' + checkChild + '>'
                                    + '<div class="panel-heading" style="' + role1 + '">'
                                    + '<div class="panel-title clearfix">'
                                    + '<div class="service-left">'
                                    + '<div class="form-group">'
                                    + '<div class="checkbox">'
                                    + '<label>' + val.Name + '</label>'
                                    + '<label class="switch"><input type="checkbox" class="' + uniqId + '" id="' + val.VarID + '" name="' + val.VarID + '" value="' + $.trim(wizEnteredData) + '" checked ' + role4 + '><span class="slider round" onclick="chck(\'' + val.VarID + '\')"></span></label>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</li>';
                        } else {
                            preventereddata[val.VarID] = "";
                            checkVal = 'c' + val.DartNo;
                            ele = '<li class="' + val.VarID + ' ' + val.VarID + '_Dart' + val.DartNo + ' ' + childClass + '" ' + checkChild + '>'
                                    + '<div class="panel-heading" style="' + role1 + '">'
                                    + '<div class="panel-title clearfix">'
                                    + '<div class="service-left">'
                                    + '<div class="form-group">'
                                    + '<div class="checkbox">'
                                    + '<label>' + val.Name + '</label>'
                                    + '<label class="switch"><input type="checkbox" class="' + uniqId + '" id="' + val.VarID + '" name="' + val.VarID + '" value="' + $.trim(wizEnteredData) + '" ' + role4 + '><span class="slider round" onclick="chck(\'' + val.VarID + '\')"></span></label>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</li>';
                        }
                    }
//                console.log(ele);
                    html_data.push(ele);

                });
                var wiz = '<input type="button" id="wizid" value="' + wiz_name_id + '" style="display:none;"/>';
                $("#configure_wizard_details #var_details").append(html_data.join("\n") + wiz);
                $(".selectpicker").selectpicker('refresh');
                setTimeout(function () {
                    $(".Scrip248EnableDriver_GroupExecute,.Scrip248PreventDriver_GroupExecute,.Scrip248UnloadDriver_GroupExecute,.InstallDriver_GroupExecute,.UninstDriver_GroupExecute,.LoadDriver_GroupExecute,.UnloadDriver_GroupExecute").hide();
                    $("#submit").show();
//                    if ($('input[type=checkbox]').is(':checked')) {
//                        $("#submit").show();
//                    } else {
//                        $("#submit").hide();
//                    }

                }, 100);
                $("#ajaxloaderr").hide();
            }
        });
    }

}

$("select#S00296UsbDrive").on('change', function () {
    if ($("select#S00296UsbDrive").val() == "2") {
        $(".constS00296ImpersonateUser,.constS00296ImpersonatePassword,.constS00296ImpersonatePassword_confirmation,.constS00296UsbFileType,.constS00296UsbFileSizeType").show();
    } else {
        $(".constS00296ImpersonateUser,.constS00296ImpersonatePassword,.constS00296ImpersonatePassword_confirmation,.constS00296UsbFileType,.constS00296UsbFileSizeType").hide();
    }
});


function showWizardAv(wiznameid) {
    $("#avira-submit").hide();
    var Level = $('#valueSearch').val();
    if (Level == "All") {
        showModal("2", "<span>Please select SITE from right pane, to access Configuration Module.</span><br><span>(NOTE: This feature is not available for scope 'ALL')</span>");
    } else {
        var wiz_name_id = wiznameid.trim();
//    $("#ajaxloaderr").html('<img src="../images/loading.gif" class="loadhome" alt="loading..." style="width:130px !important;margin-left:43% !important;margin-top: 17% !important;"/>');
//        $(".se-pre-con").show();
        $("#ajaxloaderrAv").show();
        $("#configure_wizard_detailsAv #var_detailsAv").html("");
        $("#configure_wizard_detailsAv").fadeIn();
        $(".service-desc").show();
        $("#allConfigureContent").hide();
        $("#wizid").val(wiz_name_id);
        $.ajax({
            url: "wizFunc.php?act=showWizardsAv&wiz_name_id=" + wiz_name_id,
            type: "POST",
            dataType: "json",
            data: '{}',
            async: true,
            success: function (data) {
                setTimeout(function () {
                    $("#avira-submit").show();
                    var html_data = [];
                    var variableid = [];
                    var dartnumber = [];
                    //$("#variable_details_form").html("");
                    //console.log(JSON.stringify(data));
                    jQuery.each(data, function (i, val) {
//                console.log(val.VarID);

//                alert(levelValue+"##"+val.Level);
                        var ele = "";
                        var wizEnteredData = getWizValueEnteredData(val.Ordr, wiz_name_id);
                        if (val.GUIType === "text") {
                            var type = "";
                            var tempevent = "";

                            var heading = "";
                            var style = " style='display:none;'";
                            var style1 = "display:none;";

                            if (val.VarID == "ProxyPwd") {
                                type = "password";
                            } else if (val.VarID == "EntPass") {
                                heading = val.Help;
                                style = " style='display:block;'";
                                type = "password";
                                tempevent = " onfocus='keyPressFn(&quot;" + val.VarID + "&quot;);'";
                            } else if (val.VarID == "ConfPass") {
                                type = "password";
                                tempevent = " onfocus='keyPressFn(&quot;" + val.VarID + "&quot;);'";
                            } else {
                                type = "text";
                            }

                            ele = '<div class="panel-heading ' + val.VarID + '">'
                                    + '<h4 ' + style + '><span>' + heading + '</span></h4>'
                                    + '<div class="panel-title clearfix">'
                                    + '<div class="service-left">'
                                    + '<div class="form-group c' + val.DartNo + '">'
                                    + '<label class="control-label" for="' + val.VarID + '">' + val.Name + '</label>'
                                    + '<input required="" placeholder="Please enter ' + val.Name + '" class="form-control" id="' + val.VarID + '" name="' + val.VarID + '" type="' + type + '" value="' + $.trim(wizEnteredData) + '" ' + tempevent + '>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>';


                        } else if (val.GUIType === "checkbox") {
                            var checkData = val.EnteredValues;
                            var str = checkData.split("/");

                            var wizEnteredData = getWizValueEnteredData(val.Ordr, wiz_name_id);
                            var checkStat = "";
                            var checkValue = "";

                            if (wizEnteredData) {
                                if (wizEnteredData == "1" || wizEnteredData == 1) {
                                    checkStat = " checked";
                                    checkValue = $.trim(wizEnteredData);
                                } else if (wizEnteredData == "0" || wizEnteredData == 0) {
                                    checkStat = "";
                                    checkValue = $.trim(wizEnteredData);
                                }
                            } else {
                                checkStat = val.ValueSourceField;
                                checkValue = $.trim(val.EnteredValues);
                            }

                            var heading = "";
                            var style = "style='display:none;'";
                            var style1 = "display:none;";
                            if (val.VarID == "ScanArchiveScan" || val.VarID == "olxml" || val.VarID == "ControlCenter") {
                                heading = val.Help;
                                style = "style='display:block;'";
                            }
                            if (val.VarID == "zip") {
                                style1 = "display:block";
                            }

                            ele = '<div class="panel-heading ' + val.VarID + '">'
                                    + '<h4 ' + style + '><span>' + heading + '</span></h4>'
                                    + '<div class="panel-title clearfix">'
                                    + '<div class="service-left">'
                                    + '<div class="form-group">'
                                    + '<div class="checkbox">'
                                    + '<label><input type="checkbox" class="" id="' + val.VarID + '" name="' + val.VarID + '" value="' + checkValue + '" ' + checkStat + '><span class="checkbox-material" onclick="chck(\'' + val.VarID + '\')"><span class="check"></span></span>' + val.Name + '</label>'
                                    + '<button type="button" class="btn btn-raised btn-info service-btn ' + val.VarID + '" onclick="setDefaultValues(&quot;' + val.DartNo + '&quot;);" style="margin: 1px 1px !important; ' + style1 + '">Default</button>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>';


                        } else if (val.GUIType === "TextArea-A") {
                            ele = '<div class="panel-heading c' + val.DartNo + '  ' + val.VarID + '">'
                                    + '<div class="panel-title clearfix">'
                                    + '<div class="service-left">'
                                    + '<div class="form-group c' + val.DartNo + '" style="display: none;">'
                                    + '<label class="control-label" for="' + val.VarID + '">' + val.Name + '</label>'
                                    + '<textarea required="" placeholder="Please enter ' + val.Name + '" class="form-control" rows="3" id="' + val.VarID + '" name="' + val.VarID + '">' + $.trim(wizEnteredData) + '</textarea>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>';
//                          
                        } else if (val.GUIType === "select") { //dropdown

                            var wizEnteredData = getWizValueEnteredData(val.Ordr, wiz_name_id);

                            var str = val.EnteredValues;
                            var optiondata = str.split("/");
                            var optionlist = "";
                            var count = "";

                            for (var i = 0; i < optiondata.length; i++) {
                                count = i + 1;
                                if (val.VarID == "Action") {
                                    count = i;
                                } else {
                                    count = i + 1;
                                }
                                if ($.trim(wizEnteredData) == count) {
                                    optionlist += '<option  value="' + count + '" selected>' + optiondata[i] + '</option>';
                                } else {
                                    optionlist += '<option  value="' + count + '">' + optiondata[i] + '</option>';
                                }
                            }

                            ele = '<div class="panel-heading col-lg-11 col-md-11 col-sm-12 col-xs-12 c' + val.DartNo + '  ' + val.VarID + '">'
                                    + '<div class="panel-title clearfix">'
                                    + '<div class="service-left">'
                                    + '<label class="' + val.VarID + '" style="color: #595959;">' + val.Name + ' : </label>'
                                    + '<div class="form-group c' + val.DartNo + '">'
                                    + '<select class="form-control selectpicker dropdown-submenu ' + val.VarID + '" id="' + val.VarID + '"  title="' + val.Name + '" name="' + val.VarID + '" value="' + $.trim(wizEnteredData) + '" data-container="body" onchange="checkSel(&quot;' + val.VarID + '&quot;);" data-size="5" data-width="100%">'
                                    + optionlist
                                    + '</select>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>';

                        } else if (val.GUIType === "multiselect") { //multiselect dropdown with checkbox
                            var wiz_mstr_VSF = val.ValueSourceField;

                            var optionlist = getMultiSelection_option(wiz_mstr_VSF, val.Ordr, wiz_name_id);
                            //console.log("multiselect option: "+optionlist);
                            ele = '<div class="panel-heading col-lg-12 col-md-12 col-sm-12 col-xs-12 c' + val.DartNo + '  ' + val.VarID + '">'
                                    + '<div class="panel-title clearfix">'
                                    + '<div class="service-left">'
                                    + '<div class="form-group c' + val.DartNo + '">'
                                    + '<label>' + val.Name + '</label>'
                                    + '<select class="form-control multiple-select selectpicker dropdown-submenu ' + val.VarID + '" id="' + val.VarID + '"  title="' + val.Name + '" name="' + val.VarID + '" data-size="5" data-width="100%" multiple="" data-container="body">'
                                    + optionlist
                                    + '</select>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>';

                        } else if (val.GUIType === "textarea") {

//                            if(wizEnteredData!==""){
                            var ta_data = getTextAreaFieldData(val.Ordr, wiz_name_id, val.VarID, val.DartNo);
                            ele = '<div class="panel-heading c' + val.DartNo + '  ' + val.VarID + '">'
                                    + '<div class="panel-title clearfix">'
                                    + '<div class="service-left">'
                                    + '<div class="form-group c' + val.DartNo + '">'
                                    + '<label>' + val.Name + '</label>'
                                    + '<span name="' + val.VarID + '" id="' + val.VarID + '" style="font-weight: bold;">' + ta_data + '</span>'
                                    + '<a href="" data-toggle="modal" data-target="#subformTextAreaField" style="cursor:pointer;" onclick="createTextAreaForm(\'add\',\'' + val.Ordr + '\',\'' + val.VarID + '\',\'\',\'\',\'' + val.DartNo + '\')" >Click to add ' + val.Name + '</a>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>';

                        } else if (val.GUIType === "radio") {

                            var wizEnteredData = getWizValueEnteredData(val.Ordr, wiz_name_id);
                            var checkStat = "";

                            if (wizEnteredData) {
                                if (wizEnteredData === val.EnteredValues) {
                                    checkStat = " checked";
                                }
                            } else {
                                checkStat = val.ValueSourceField;
                            }

                            ele += '<div class="' + val.VarID + '"><div class="form-group source-type">'
                                    + '<div class="radio">'
                                    + '<label>'
                                    + '<input type="radio" name="' + val.VarID + '" value="' + $.trim(val.EnteredValues) + '" ' + checkStat + ' onclick="checkRad(&quot;' + val.VarID + '&quot;,this);"> ' + val.Name + '<span class="circle"></span><span class="check"></span>'
                                    + '</label>'
                                    + '</div>'
                                    + '</div></div>';

                        } else if (val.GUIType === "select-transfer") {

                            var optionlist = Get_SelectTransferList(Level, val.Ordr, wiz_name_id);
                            ele += '<h3>' + val.Name + '</h3>'
                                    + '<div class="row clearfix">'
                                    + '<div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">'
                                    + '<div class="form-group label-floating">'
                                    + '<input class="form-control" type="text" id="i' + val.VarID + '">'
                                    + '</div>'
                                    + '</div>'
                                    + '<div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">'
                                    + '<div class="select-transfer">'
                                    + '<ul>'
                                    + '<li><a href="javascript:;" onclick="moveOptions(&quot;i' + val.VarID + '&quot;,&quot;' + val.VarID + '&quot;,&quot;0&quot;);" style="color: white !important;">+</a></li>'
                                    + '<li><a href="javascript:;" onclick="moveOptions(&quot;' + val.VarID + '&quot;,&quot;deleteTransfers&quot;,&quot;1&quot;);" style="color: white !important;" >-</a></li>'
                                    + '</ul>'
                                    + '</div>'
                                    + '</div>'
                                    + '<div class="col-lg-4 col-md-4 col-sm-5 col-xs-12">'
                                    + '<div class="form-group">'
                                    + '<select class="custom-select ' + val.VarID + '" size="10" multiple="" id="' + val.VarID + '" name="' + val.VarID + '">'
                                    + optionlist
                                    + '</select>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>';
                        } else if (val.GUIType === "number") {
                            var EnteredValue = val.EnteredValues;
                            var WizValue = Get_PortValue(Level, val.Ordr, wiz_name_id);
                            ele += '<div class="clearfix ' + val.VarID + '">'
                                    + '<div class="col-sm-8">'
                                    + '<div class="row clearfix">'
                                    + '<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">'
                                    + '<div class="form-group">'
                                    + '<label>' + val.Name + '</label>'
                                    + '<input type="number" class="form-control ' + val.VarID + '" id="' + val.VarID + '" name="' + val.VarID + '" value="' + WizValue + '">'
                                    + '</div>'
                                    + '</div>'
                                    + '<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">'
                                    + '<button type="button" class="btn btn-raised btn-info service-btn ' + val.VarID + '" onclick="setPortDefaultValues(&quot;' + val.VarID + '&quot;,&quot;' + EnteredValue + '&quot;);">Default</button>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>';
                        } else if (val.GUIType === "multi-check") {
                            var checkData = val.EnteredValues;
                            var str = checkData.split("/");

                            var wizEnteredData = getWizValueEnteredData(val.Ordr, wiz_name_id);
                            var checkStat = "";
                            var checkValue = "";

                            if (wizEnteredData) {
                                if (wizEnteredData == "1" || wizEnteredData == 1) {
                                    checkStat = " checked";
                                    checkValue = $.trim(wizEnteredData);
                                } else if (wizEnteredData == "0" || wizEnteredData == 0) {
                                    checkStat = "";
                                    checkValue = $.trim(wizEnteredData);
                                }
                            } else {
                                checkStat = val.ValueSourceField;
                                checkValue = $.trim(val.EnteredValues);
                            }
                            ele += '<div class="clearfix ' + val.VarID + '">'
                                    + '<div class="col-sm-10">'
                                    + '<div class="row clearfix">'
                                    + '<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">'
                                    + '<div class="form-group">'
                                    + '<div class="checkbox">'
                                    + '<label><input type="checkbox" class="" id="' + val.VarID + '" name="' + val.VarID + '" value="' + checkValue + '" ' + checkStat + '><span class="checkbox-material" onclick="multiCheck(\'' + val.VarID + '\')"><span class="check"></span></span>' + val.Name + '</label>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">'
                                    + '<button type="button" class="btn btn-raised btn-info service-btn ' + val.VarID + '" onclick="setDefaultValues(&quot;' + val.DartNo + '&quot;);" style="margin: 1px 1px !important;">Default</button>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>'
                                    + '</div>';
                        }

//                console.log(ele);
                        html_data.push(ele);
                        variableid.push(val.VarID);
                        dartnumber.push(val.DartNo);
                        $("#ajaxloaderrAv").hide();
                        $(".se-pre-con").hide();
                    });

                    var wiz = '<input type="button" id="wizid" name="wn_id" value="' + wiz_name_id + '" style="display:none;"/>';
                    $("#configure_wizard_detailsAv #var_detailsAv").append(html_data.join("\n") + wiz);
                    $("#variableid").val(variableid);
                    $("#dartnumber").val(dartnumber);
                    $(".selectpicker").selectpicker('refresh');
                    $(".ADWARE,.ADSPY,.APPL,.BDC,.DIAL,.HIDDENEXT,.PFS,.GAME,.JOKE,.PHISH,.PUA,.SPR,.ProcessProtectionEx_GUI,.APCAskForUploadEnabled,.OnAccessEnableCloud,.ProxyAddress,.ProxyPort,.ProxyLogin,.ProxyPwd,.POP3,.ImapPortNr1,.IMAP,.Pop3PortNr1,.SmtpPortNr1,.ShowProgress,.DetectionLevel1,.DetectionLevel2,.DetectionLevel3,.CopyQuarantine,.PrimaryAction,.SecondaryAction,.BeforeActionToQuarantine,.ScanArchiveRecursionDepth").css("margin-left", "7%");
                    $(".ScanHeuristicFile,.Win32HeuristicMode,.IsDialog,.DesinfFlags,.DesinfFlags1,.Action,.ShowProgressBar,.AffectedMails,.AffectedAttachments,.PrimaryActionForInfected,.SecondaryActionForInfected,.PrimaryAction12001,.PrimaryAction4001,.SecondaryAction4001").css("margin-left", "3%");
                    $(".OnAccessEnableCloudGUI").css("margin-left", "12%");
                    $("button.ScanArchiveRecursionDepth").hide();
                    $(".ScanArchiveScan,.SelectAllArchive,.ScanArchivSmartExtensions,.ScanArchiveCutRecursionDepth,.SelectAllArchive,.wim,.iso9660,.msom,.sc,.pm,.em,.nmm,.bsdm,.olxml,.sim,.xz,.xar,.is,.sapc,.ahk,.ai,.base64,.7zipsfx,.7zip,.nsis,.rpm,.cpio,.chm,.mb,.pgpsm,.acesfx,.ace,.bz2,.jar,.rarsfx,.rar,.lzhsfx,.lzh,.cabsfx,.cabm,.mscomp,.binhex,.mime,.tnef,.uuencode,.zoo,.gz,.tar,.arjsfx,.arj,.crx,.zipsfx,.zip").css("margin-left", "3%");
                    $(".RealTimeProt,.MailProt,.WebProt,.Quarantine,.AddModJobs").css("margin-left", "6%");
                    $(".RestAffObj,.RescanAffObjs,.AffObjProps,.DelAffObj,.SendMail").css("margin-left", "12%");

                    if ($("div").hasClass("ScanActionMode")) {
                        var radio_value = $('input[name=ScanActionMode]:checked', '#configure_wizard_detailsAv').val();
                        if (radio_value == "1" || radio_value == 1) {
                            $("#BeforeActionToQuarantine,#PrimaryActionForInfected,#SecondaryActionForInfected").prop("disabled", true);
                            $(".PrimaryActionForInfected,.SecondaryActionForInfected").selectpicker('refresh');
                        } else if (radio_value == "0" || radio_value == 0) {
                            $("#BeforeActionToQuarantine,#PrimaryActionForInfected,#SecondaryActionForInfected").prop("disabled", false);
                            $(".PrimaryActionForInfected,.SecondaryActionForInfected").selectpicker('refresh');
                            if ($("div").hasClass("PrimaryActionForInfected")) {
                                var primeVal = $("#PrimaryActionForInfected").val();
                                if (primeVal == "1" || primeVal == 1) {
                                    $("#SecondaryActionForInfected").prop("disabled", false);
                                    $(".SecondaryActionForInfected").selectpicker('refresh');
                                } else {
                                    $("#SecondaryActionForInfected").prop("disabled", true);
                                    $(".SecondaryActionForInfected").selectpicker('refresh');
                                }
                            }
                        }
                    }

                    if ($("div").hasClass("ActionMode")) {
                        var radio_value = $('input[name=ActionMode]:checked', '#configure_wizard_detailsAv').val();
                        if (radio_value == "1" || radio_value == 1) {
                            $("#DesinfFlags,#DesinfFlags1").prop("disabled", true);
                            $("#Action").prop("disabled", false);
                        } else if (radio_value == "0" || radio_value == 0) {
                            $("#DesinfFlags,#DesinfFlags1").prop("disabled", false);
                            $("#Action").prop("disabled", true);
                            if ($("div").hasClass("DesinfFlags")) {
                                var primeVal = $("#DesinfFlags").val();
                                if (primeVal == "3" || primeVal == 3) {
                                    $("#DesinfFlags1").prop("disabled", false);
                                } else {
                                    $("#DesinfFlags1").prop("disabled", true);
                                }
                            }
                        }
                    }

                    if ($("div").hasClass("BeforeActionToQuarantine")) {
                        var check_value = $('input[name=BeforeActionToQuarantine]:checked', '#configure_wizard_detailsAv').val();
                        $("#SecondaryActionForInfected option[value='1']").hide();
                        $(".SecondaryActionForInfected").selectpicker('refresh');
                        if (check_value == "1" || check_value == 1) {
                            $("#PrimaryActionForInfected option[value='3']").hide();
                            $("#SecondaryActionForInfected option[value='3']").hide();
                            $(".PrimaryActionForInfected,.SecondaryActionForInfected").selectpicker('refresh');
                        } else if (check_value == "0" || check_value == 0) {
                            $("#PrimaryActionForInfected option[value='3']").show();
                            $("#SecondaryActionForInfected option[value='3']").show();
                            $(".PrimaryActionForInfected,.SecondaryActionForInfected").selectpicker('refresh');
                        }
                    }

                    if ($("div").hasClass("ProxyEnabled")) {
                        var radio_value = $('input[name=ProxyEnabled]:checked', '#configure_wizard_detailsAv').val();
                        if (radio_value == "1" || radio_value == 1) {
                            $("#ProxyAddress,#ProxyPort,#ProxyLogin,#ProxyPwd").prop("disabled", false);
                        } else if (radio_value == "0" || radio_value == 0) {
                            $("#ProxyAddress,#ProxyPort,#ProxyLogin,#ProxyPwd").prop("disabled", true);
                        }
                    }

                    if ($("div").hasClass("EntPass")) {
                        var field1 = $("#EntPass").val();
                        var field2 = $("#ConfPass").val();

                        if (field1 == "" || field2 == "") {
                            $("#ControlCenter,#RealTimeProt,#MailProt,#WebProt,#Quarantine,#RestAffObj,#RescanAffObjs,#AffObjProps,#DelAffObj,#SendMail,#AddModJobs,#Configuration,#InstOrUninst").prop("disabled", true);
                        } else {
                            $("#ControlCenter,#RealTimeProt,#MailProt,#WebProt,#Quarantine,#RestAffObj,#RescanAffObjs,#AffObjProps,#DelAffObj,#SendMail,#AddModJobs,#Configuration,#InstOrUninst").prop("disabled", false);
                        }
                    }

//            if($("div").hasClass("ScanInMails")){
//                var check_value = $('input[name=ScanInMails]:checked', '#configure_wizard_detailsAv').val();
//                if(check_value == "1" || check_value == 1){
//                    $("#POP3,#IMAP").prop("disabled",false);
//                    var check_value1 = $('input[name=POP3]:checked', '#configure_wizard_detailsAv').val();
//                    var check_value2 = $('input[name=IMAP]:checked', '#configure_wizard_detailsAv').val();
//                    if(check_value1 == "1" || check_value1 == 1){
//                        $(".ImapPortNr1").prop("disabled",false);
//                    } else {
//                        $(".ImapPortNr1").prop("disabled",true);
//                    }
//                    if(check_value2 == "1" || check_value2 == 1){
//                        $(".Pop3PortNr1").prop("disabled",false);
//                    } else {
//                        $(".Pop3PortNr1").prop("disabled",true);
//                    }
//                } else {
//                    $(".ImapPortNr1,#POP3,.Pop3PortNr1,#IMAP").prop("disabled",true);
//                }
//            }
//            if($("div").hasClass("ScanOutMails")){
//                var check_value = $('input[name=ScanOutMails]:checked', '#configure_wizard_detailsAv').val();
//                if(check_value == "1" || check_value == 1){
//                    $(".SmtpPortNr1").prop("disabled",false);
//                } else {
//                    $(".SmtpPortNr1").prop("disabled",true);
//                }
//            }
//            if($("div").hasClass("APCEnabled")){
//                var check_value = $('input[name=APCEnabled]:checked', '#configure_wizard_detailsAv').val();
//                if(check_value == "1" || check_value == 1){
//                    $("#APCAskForUploadEnabled,#OnAccessEnableCloud,#OnAccessEnableCloudGUI").prop("disabled",false);
//                    var check_value1 = $('input[name=OnAccessEnableCloud]:checked', '#configure_wizard_detailsAv').val();
//                        if(check_value1 == "1" || check_value1 == 1){
//                        $("#OnAccessEnableCloudGUI").prop("disabled",false);
//                    } else if(check_value1 == "0" || check_value1 == 0){
//                        $("#OnAccessEnableCloudGUI").prop("disabled",true);
//                    }
//                } else {
//                    $("#APCAskForUploadEnabled,#OnAccessEnableCloud,#OnAccessEnableCloudGUI").prop("disabled",true);
//                }
//            }
                }, 1000);

            }

        });
    }

}

function setPortDefaultValues(VarID, VarValue) {
    $("#" + VarID).val(VarValue);
}

function Get_PortValue(Level, OrderId, WizardId) {
    var numberVal = "";
    $.ajax({
        url: "wizFunc.php?act=getPortValue&Ordr=" + OrderId + '&Level=' + Level + '&WnId=' + WizardId,
        type: "POST",
        dataType: "json",
        data: '{}',
        async: false,
        success: function (num) {
            numberVal = num;
        }
    });

    return $.trim(numberVal);
}

function checkRad(obj, thisVal) {
    $(thisVal).prop("checked", true);
    var radio_value = $('input[name=' + obj + ']:checked', '#configure_wizard_detailsAv').val();
    if (radio_value == "1" || radio_value == 1) {
        $("#BeforeActionToQuarantine,#PrimaryActionForInfected,#SecondaryActionForInfected,#DesinfFlags,#DesinfFlags1").prop("disabled", true);
        $(".PrimaryActionForInfected,.SecondaryActionForInfected").selectpicker('refresh');
        $("#Action").prop("disabled", false);
        $("#ProxyAddress,#ProxyPort,#ProxyLogin,#ProxyPwd").prop("disabled", false);
    } else if (radio_value == "0" || radio_value == 0) {
        $("#BeforeActionToQuarantine,#PrimaryActionForInfected,#SecondaryActionForInfected,#DesinfFlags,#DesinfFlags1").prop("disabled", false);
        $(".PrimaryActionForInfected,.SecondaryActionForInfected").selectpicker('refresh');
        $("#Action").prop("disabled", true);
        $("#ProxyAddress,#ProxyPort,#ProxyLogin,#ProxyPwd").prop("disabled", true);
        var primeVal = $("#PrimaryActionForInfected").val();
        if (primeVal == "1" || primeVal == 1) {
            $("#SecondaryActionForInfected").prop("disabled", false);
            $(".SecondaryActionForInfected").selectpicker('refresh');
        } else {
            $("#SecondaryActionForInfected").prop("disabled", true);
            $(".SecondaryActionForInfected").selectpicker('refresh');
        }
    } else {
        $("#ProxyAddress,#ProxyPort,#ProxyLogin,#ProxyPwd").prop("disabled", true);
    }
}

function checkSel(objid) {
    if (objid == "PrimaryActionForInfected") {
        var primeVal = $("#PrimaryActionForInfected").val();
        if (primeVal == "1" || primeVal == 1) {
            $("#SecondaryActionForInfected").prop("disabled", false);
            $(".SecondaryActionForInfected").selectpicker('refresh');
        } else {
            $("#SecondaryActionForInfected").prop("disabled", true);
            $(".SecondaryActionForInfected").selectpicker('refresh');
        }
    }
    if (objid == "DesinfFlags") {
        var primeVal = $("#DesinfFlags").val();
        if (primeVal == "3" || primeVal == 3) {
            $("#DesinfFlags1").prop("disabled", false);
        } else {
            $("#DesinfFlags1").prop("disabled", true);
        }
    }
}

function moveOptions(srcList, destList, type) {
    if (type === "0" || type === 0) {
        var selected_file = $("#" + srcList).val();
        var optionlist;
        var optionarray = [];
        var i = "0";
        $("#" + destList + " option").each(function () {
            optionlist = $(this).text();
            optionarray.push(optionlist);
            i++;
        });
        if (selected_file != "") {
            if (optionarray.indexOf(selected_file) == -1) {
                $("#" + destList).append("<option value='" + i + "'>" + selected_file + "</option>");
                $("#" + srcList).val("");
            } else {
                showModal("2", "<span>Entry already exists in the list</span>");
            }
        } else {
            showModal("2", "<span>Please enter a value to add in the list</span>");
        }
    } else if (type === "1" || type === 1) {
        var selected_file = $("#" + srcList).val();
        if (selected_file) {
            $.each(selected_file, function (i, val) {
                $("#" + srcList + " option[value='" + val + "']").remove();
            });
        } else {
            showModal("2", "<span>Please select an item to remove from the list</span>");
        }

    }
}

function getTextAreaFieldData(ordr, wiz_name_id, var_id, dart_no) {
    // console.log(obj_val);
    var value = "";
    $.ajax({
        url: "wizFunc.php?act=getTextAreaFieldData" + '&ordr=' + ordr + '&wn_id=' + wiz_name_id + '&var_id=' + var_id + '&dart_no=' + dart_no,
        type: "GET",
        dataType: "text",
        data: '{}',
        async: false,
        success: function (data) {
            value = data;
        }

    });
    if (value != "") {
        return value;
    }


}

function createTextAreaForm(action, ordr_num, varid, editing_id, edit_data, dartnor) {
    var NH_ScheduleVarIds = ['S00006Schedule', 'S00012Schedule', 'S00027SchedECS', 'S00060ScheduleNew', 'S00088Schedule', 'S00090Schedule', 'S00095Schedule', 'S00096Schedule', 'S00097Schedule', 'S00098Schedule', 'S00176Schedule', 'S00177Schedule', 'S00211Schedule', 'S00228Schedule', 'S00232SchedECS', 'S00245Scheduler', 'S00282Scheduler', 'Schedule', 'schedule', 'Scrip224Schedule','S01003Schedule','S01006Schedule','S01011Schedule','S01012Schedule','Scrip1013Schedule','Scrip837Schedule','S0000421Schedule'];
//    $(".form-control").val("");
    var edit_str = "";
    $("#action").val(action);
    if (action == 'edit') {
        if (NH_ScheduleVarIds.indexOf(varid) != -1) {
            //if (varid == "S00060ScheduleNew" || varid == "S00228Schedule" || varid == "Schedule" || varid == "S00088Schedule" || varid == "schedule" || varid == "S00006Schedule" || varid == "S00012Schedule" || varid == "S00027SchedECS" || varid == "S00090Schedule" || varid == "S00095Schedule" || varid == "S00096Schedule" || varid == "S00097Schedule" || varid == "S00098Schedule" || varid == "S00176Schedule" || varid == "S00177Schedule" || varid == "S00211Schedule" || varid == "S00232SchedECS" || varid == "S00245Scheduler" || varid == "S00282Scheduler") {
//            var edit_str1 = edit_data.split(",");
//            if (edit_str1[0].includes('localized=""')) {
            edit_str = edit_data.split('<br localized="">');
//            } else if (edit_str1[0].includes('localized')) {
//                edit_str = edit_str1[0].split('<br localized>');
//            } else {
//                edit_str = edit_str1[0].split('<br>');
//            }
//            console.log(edit_str);
//            return false;
        } else {
            var dart_hardcoded = [];
            var spldata = "";
            switch (dartnor) {
                case 297:
                    spldata = edit_data.split(",");
                    if (spldata[0] == "0") {
                        dart_hardcoded[0] = spldata[0];
                        dart_hardcoded[1] = spldata[1];
                        dart_hardcoded[2] = "";
                        dart_hardcoded[3] = spldata[3];
                    } else if (spldata[0] == "1") {
                        dart_hardcoded[0] = spldata[0];
                        dart_hardcoded[1] = "";
                        dart_hardcoded[2] = spldata[1];
                        dart_hardcoded[3] = "";
                    }
                    edit_str = dart_hardcoded;
                    break;
                case 233:
                    spldata = edit_data.split(",");
                    if (spldata[0] == "RegKey") {
                        dart_hardcoded[0] = spldata[0];
                        dart_hardcoded[1] = spldata[1].trim();
                        dart_hardcoded[2] = spldata[2];
                        dart_hardcoded[3] = spldata[3];
                        dart_hardcoded[4] = spldata[4];
                        dart_hardcoded[5] = "";
                        dart_hardcoded[6] = "";
                    } else if (spldata[0] == "Service") {
                        dart_hardcoded[0] = spldata[0];
                        dart_hardcoded[1] = "";
                        dart_hardcoded[2] = spldata[1];
                        dart_hardcoded[3] = spldata[2];
                        dart_hardcoded[4] = spldata[3];
                        dart_hardcoded[5] = "";
                        dart_hardcoded[6] = "";
                    } else if (spldata[0] == "StartFolder") {
                        dart_hardcoded[0] = spldata[0];
                        dart_hardcoded[1] = spldata[1].trim();
                        dart_hardcoded[2] = "";
                        dart_hardcoded[3] = "";
                        dart_hardcoded[4] = "";
                        dart_hardcoded[5] = spldata[2];
                        dart_hardcoded[6] = spldata[3];
                    }
                    edit_str = dart_hardcoded;
                    break;
                case 240:
                    spldata = edit_data.split(",");
                    if (varid == "settingsToEnforce") {
                        edit_str = spldata;
                    } else {
                        if (spldata[0] == "RegKey") {
                            dart_hardcoded[0] = spldata[0];
                            dart_hardcoded[1] = spldata[1];
                            dart_hardcoded[2] = "";
                            dart_hardcoded[3] = spldata[2];
                            dart_hardcoded[4] = "";
                            dart_hardcoded[5] = spldata[3];
                            dart_hardcoded[6] = spldata[4];
                            dart_hardcoded[7] = "";
                            dart_hardcoded[8] = "";
                        } else if (spldata[0] == "ExtObj") {
                            dart_hardcoded[0] = spldata[0];
                            dart_hardcoded[1] = spldata[1];
                            dart_hardcoded[2] = spldata[2];
                            dart_hardcoded[3] = spldata[3];
                            dart_hardcoded[4] = spldata[4];
                            dart_hardcoded[5] = spldata[5];
                            dart_hardcoded[6] = spldata[6];
                            dart_hardcoded[7] = "";
                            dart_hardcoded[8] = "";
                        } else if (spldata[0] == "TxtFile") {
                            dart_hardcoded[0] = spldata[0];
                            dart_hardcoded[1] = spldata[1];
                            dart_hardcoded[2] = "";
                            dart_hardcoded[3] = "";
                            dart_hardcoded[4] = "";
                            dart_hardcoded[5] = "";
                            dart_hardcoded[6] = "";
                            dart_hardcoded[7] = spldata[2];
                            dart_hardcoded[8] = spldata[3];
                        }
                    }

                    edit_str = dart_hardcoded;
                    break;
                case 228:
                    if (varid == "S00228Rules") {
                        var splrow1 = "";
                        var splrow2 = "";
                        var splrow3 = "";
                        var splrow4 = "";
                        var splrow5 = "";
                        var splrow6 = "";
                        spldata = edit_data.split(",");
                        dart_hardcoded[0] = spldata[0];
                        splrow1 = spldata[1].split("=");
                        dart_hardcoded[1] = splrow1[1];
                        splrow2 = spldata[2].split("=");
                        dart_hardcoded[2] = splrow2[1];
                        splrow3 = spldata[3].split("=");
                        dart_hardcoded[3] = splrow3[1];
                        splrow4 = spldata[4].split("=");
                        dart_hardcoded[4] = splrow4[1];
                        splrow5 = spldata[5].split("=");
                        dart_hardcoded[5] = splrow5[1];
                        splrow6 = spldata[6].split("=");
                        dart_hardcoded[6] = splrow6[1];
                    } else if (varid == "S00228Chains") {
                        spldata = edit_data.split(",");
                        dart_hardcoded[0] = spldata[1];
                        dart_hardcoded[1] = spldata[2];
                    }
                    edit_str = dart_hardcoded;
                    break;
                case 296:
                    var spldata2 = "";
                    if (varid == "constS00296ImpersonatePassword_confirmation" || varid == "constS00296ImpersonatePassword") {
                        spldata = edit_data.split('value="');
                        spldata2 = spldata[1].split('"');
                        edit_str = spldata2[0];
                    } else {
                        edit_str = edit_data.split(",");
                    }
                default:
                    edit_str = edit_data.split(",");
                    break;
            }
        }
    }

    $.ajax({
        url: "wizFunc.php?act=getChildByParent" + '&ordr=' + ordr_num,
        type: "POST",
        dataType: "json",
        data: '{}',
        async: false,
        success: function (data) {
//            $("#subformTextAreaField").modal("show");
            var html_data = [];
            var inc = 0;
            jQuery.each(data, function (i, val) {

                var ele = "";
                var id = varid + '_' + val.Ordr;
                var hideshow = varid + '_' + dartnor + '_' + inc;
                var fieldvar = val.FieldVar;
                var joinchar = val.JoinCharacter;
                var entered_edit_data = "";
                if (action == 'edit') {
                    entered_edit_data = edit_str[i];
                }
                if (val.GUIType === "time") {

                    ele = ''
                            + '<div class="panel-heading TextAreaValidityDiv ' + hideshow + '">'
                            + '<div class="panel-title clearfix">'
                            + '<div class="service-left">'
                            + '<div class="form-group">'
                            + '<label class="control-label" for="' + hideshow + '">' + val.Name + '<em style="color: red;">*</em></label>'
                            + '<input required="" class="form-control form_datetime date_format TextAreaValidity time_field" id="' + hideshow + '" type="text" value="' + entered_edit_data + '" readonly>'
                            + '</div>'
                            + '</div>'
                            + '</div>'
                            + '</div>'
                            + '';
//                            
                } else if (val.GUIType === "date") {

                    ele = ''
                            + '<div class="panel-heading TextAreaValidityDiv ' + hideshow + '">'
                            + '<div class="panel-title clearfix">'
                            + '<div class="service-left">'
                            + '<div class="form-group">'
                            + '<label class="control-label" for="' + hideshow + '">' + val.Name + '<em style="color: red;">*</em></label>'
                            + '<input required="" class="form-control form_datetime date_format TextAreaValidity date_field" id="' + hideshow + '" type="text" value="' + entered_edit_data + '" readonly>'
                            + '</div>'
                            + '</div>'
                            + '</div>'
                            + '</div>'
                            + '';
//                            
                } else if (val.GUIType === "text") {
                    var substring = "228";


                    var TextAreaValidity = "";
                    var emStyle = '';

                    if (val.Name.includes("leave blank")) {
                        TextAreaValidity = "";
                        emStyle = '';
                    } else {
                        TextAreaValidity = "TextAreaValidity";
                        emStyle = '<em style="color: red;">*</em>';
                    }
                    if ((hideshow.indexOf(substring) > -1) && ($.trim(val.Name) != "Please enter the rule name") || ($.trim(val.Name) != "Please enter the rule chain name")) {
                        TextAreaValidity = "";
                        emStyle = '';
                    }
                    var texttype = "";
                    var finalData = "";
                    if (varid == "constS00296ImpersonatePassword_confirmation" || varid == "constS00296ImpersonatePassword" || 
                            varid == "S01004UserPswd" || varid == "S01004UserPswd_confirmation" || varid == "S01004UserChPswd" || 
                            varid == "S01004UserChPswd_confirmation" || varid == "S01005Password" || varid == 's421password_confirmation' || varid == 's421password') {
                        texttype = "password";
                        finalData = entered_edit_data;
                    } else {
                        texttype = "text";
                        finalData = entered_edit_data;
                    }

                    ele = ''
                            + '<div class="panel-heading TextAreaValidityDiv ' + hideshow + '">'
                            + '<div class="panel-title clearfix">'
                            + '<div class="service-left">'
                            + '<div class="form-group">'
                            + '<label class="control-label" for="' + hideshow + '">' + val.Name + ' ' + emStyle + '</label>'
                            + '<input required="" class="form-control ' + TextAreaValidity + '" id="' + hideshow + '" type="'+texttype+'" value="' + finalData + '">'
                            + '</div>'
                            + '</div>'
                            + '</div>'
                            + '</div>'
                            + '';
//                            
                } else if (val.GUIType === "checkbox") {
                    var checkData = val.EnteredValues;
                    var str = checkData.split("/");

                    entered_edit_data ? entered_edit_data : "0";


                    if ($.trim(entered_edit_data) == str[0] || $.trim(entered_edit_data) == 1) {
                        //value="'+$.trim(wizEnteredData)+'" checked 
//                        var checkVal = 'c' + val.DartNo;

                        ele = ''
                                + '<div class="panel-heading TextAreaValidityDiv ' + hideshow + '">'
                                + '<div class="panel-title clearfix">'
                                + '<div class="service-left">'
                                + '<div class="form-group">'
                                + '<div class="checkbox">'
                                + '<label>' + val.Name + '<input type="checkbox" class="TextAreaValidity" id="' + hideshow + '" name="' + val.Name + '" value="' + entered_edit_data + '" checked><span class="checkbox-material"><span class="check"></span></span></label>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '';
                    } else {
//                        var checkVal = 'c' + val.DartNo;

                        ele = ''
                                + '<div class="panel-heading TextAreaValidityDiv ' + hideshow + '">'
                                + '<div class="panel-title clearfix">'
                                + '<div class="service-left">'
                                + '<div class="form-group">'
                                + '<div class="checkbox">'
                                + '<label>' + val.Name + '<input type="checkbox" class="TextAreaValidity" id="' + hideshow + '" name="' + val.Name + '" value="' + entered_edit_data + '"><span class="checkbox-material"><span class="check"></span></span></label>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '</div>'
                                + '';
                    }

                } else if (val.GUIType === "select") { //dropdown                            
                    var str = val.EnteredValues;
                    var optiondata = str.split("/");
                    var selectValues = val.SelectEnteredValues;
                    var selectEnteredValuesData = selectValues.split("/");
                    var optionlist = "";
                    var enterVal = entered_edit_data.split(",");

                    if (val.Name == "Minute" || val.Name == "Hour" || val.Name == "Day" || val.Name == "Month" || val.Name == "Weekday") {
                        var multiClass = "multiple-select";
                        var multiAttr = "multiple";
                        var size = 5;
                    } else {
                        var size = 2;
                    }

                    for (var i = 0; i < optiondata.length; i++) {
                        if (NH_ScheduleVarIds.indexOf(varid) != -1) {
                            var selectData = entered_edit_data;//.split(",");
//                            if (selectData.indexOf(optiondata[i]) != -1) {
                            if($.inArray(optiondata[i],enterVal) != -1) {
                                optionlist += '<option id="' + optiondata[i] + '"  value="' + optiondata[i] + '" selected>' + selectEnteredValuesData[i] + '</option>';
                            } else {
                                if (i == 0 && $.trim(entered_edit_data) == "") {
                                    optionlist += '<option id="' + optiondata[i] + '"  value="' + optiondata[i] + '" selected>' + selectEnteredValuesData[i] + '</option>';
                                } else {
                                    optionlist += '<option id="' + optiondata[i] + '"  value="' + optiondata[i] + '">' + selectEnteredValuesData[i] + '</option>';
                                }
                            }
                        } else {
                            if ($.trim(entered_edit_data) == $.trim(optiondata[i])) {
                                optionlist += '<option id="' + optiondata[i] + '"  value="' + optiondata[i] + '" selected>' + selectEnteredValuesData[i] + '</option>';
                            } else {
                                if (i == 0 && $.trim(entered_edit_data) == "") {
                                    optionlist += '<option id="' + optiondata[i] + '"  value="' + optiondata[i] + '" selected>' + selectEnteredValuesData[i] + '</option>';
                                } else {
                                    optionlist += '<option id="' + optiondata[i] + '"  value="' + optiondata[i] + '">' + selectEnteredValuesData[i] + '</option>';
                                }
                            }
                        }
                    }

                    ele = ''
                            + '<div class="panel-heading TextAreaValidityDiv ' + hideshow + '">'
                            + '<div class="panel-title clearfix">'
                            + '<div class="service-left">'
                            + '<div class="form-group">'
                            + '<label>' + val.Name + '</label>'
                            + '<select class="form-control selectpicker dropdown-submenu ' + hideshow + ' ' + multiClass + '" id="' + hideshow + '"  title="' + val.Name + '" onchange="HideShowFields(&quot;' + hideshow + '&quot;)" data-size="'+size+'" data-width="100%" ' + multiAttr + '>'
                            + optionlist
                            + '</select>'
                            + '</div>'
                            + '</div>'
                            + '</div>'
                            + '</div>'
                            + '';

                } else if (val.GUIType === "TextArea-A") {

                    ele = ''
                            + '<div class="panel-heading TextAreaValidityDiv ' + hideshow + '">'
                            + '<div class="panel-title clearfix">'
                            + '<div class="service-left">'
                            + '<div class="form-group">'
                            + '<label class="control-label" for="' + hideshow + '">' + val.Name + '</label>'
                            + '<textarea required="" class="form-control TextAreaValidity" rows="3" id="' + hideshow + '" value="' + entered_edit_data + '">' + entered_edit_data + '</textarea>'
                            + '</div>'
                            + '</div>'
                            + '</div>'
                            + '</div>'
                            + '';

                }
                html_data.push(ele);
                inc++;
            });
//            console.log(html_data);
            $("#hiddenfields").html("");
            $("#subformTextAreaFieldFill").html(html_data);
            var extra_fields = '<input type="hidden" id="editing_id" value="' + editing_id + '"/>' +
                    '<input type="hidden" id="action" value="' + action + '"/>' +
                    '<input type="hidden" id="ordrnum" value="' + ordr_num + '"/>' +
                    '<input type="hidden" id="varid" value="' + varid + '"/>';
            $("#hiddenfields").html(extra_fields);
            $(".selectpicker").selectpicker('refresh');
            if ($(".panel-heading").hasClass("S00297FileConfig_297_0")) {
                if ($("select#S00297FileConfig_297_0").val() == "1") {
                    $("select#S00297FileConfig_297_0").val("1").change();
                    $(".S00297FileConfig_297_1,.S00297FileConfig_297_3").hide();
                    $(".S00297FileConfig_297_2").show();
                } else {
                    $("select#S00297FileConfig_297_0").val("0").change();
                    $(".S00297FileConfig_297_1,.S00297FileConfig_297_3").show();
                    $(".S00297FileConfig_297_2").hide();
                }
            }
            if ($(".panel-heading").hasClass("itemsToEnable_233_0")) {
                if ($("select#itemsToEnable_233_0").val() == "RegKey") {
                    $(".itemsToEnable_233_5,.itemsToEnable_233_6").hide();
                    $(".itemsToEnable_233_1,.itemsToEnable_233_2,.itemsToEnable_233_3,.itemsToEnable_233_4").show();
                } else if ($("select#itemsToEnable_233_0").val() == "Service") {
                    $(".itemsToEnable_233_1,.itemsToEnable_233_5,.itemsToEnable_233_6").hide();
                    $(".itemsToEnable_233_2,.itemsToEnable_233_3,.itemsToEnable_233_4").show();
                } else if ($("select#itemsToEnable_233_0").val() == "StartFolder") {
                    $(".itemsToEnable_233_1,.itemsToEnable_233_5,.itemsToEnable_233_6").show();
                    $(".itemsToEnable_233_2,.itemsToEnable_233_3,.itemsToEnable_233_4").hide();
                } else {
                    $(".itemsToEnable_233_1,.itemsToEnable_233_2,.itemsToEnable_233_3,.itemsToEnable_233_4,.itemsToEnable_233_5,.itemsToEnable_233_6").hide();
                }
            }
            if ($(".panel-heading").hasClass("itemsToDisable_233_0")) {
                if ($("select#itemsToDisable_233_0").val() == "RegKey") {
                    $(".itemsToDisable_233_5,.itemsToDisable_233_6").hide();
                    $(".itemsToDisable_233_1,.itemsToDisable_233_2,.itemsToDisable_233_3,.itemsToDisable_233_4").show();
                } else if ($("select#itemsToDisable_233_0").val() == "Service") {
                    $(".itemsToDisable_233_1,.itemsToDisable_233_5,.itemsToDisable_233_6").hide();
                    $(".itemsToDisable_233_2,.itemsToDisable_233_3,.itemsToDisable_233_4").show();
                } else if ($("select#itemsToDisable_233_0").val() == "StartFolder") {
                    $(".itemsToDisable_233_1,.itemsToDisable_233_5,.itemsToDisable_233_6").show();
                    $(".itemsToDisable_233_2,.itemsToDisable_233_3,.itemsToDisable_233_4").hide();
                } else {
                    $(".itemsToDisable_233_1,.itemsToDisable_233_2,.itemsToDisable_233_3,.itemsToDisable_233_4,.itemsToDisable_233_5,.itemsToDisable_233_6").hide();
                }
            }
            if ($(".panel-heading").hasClass("itemsToEnable_240_0")) {
                if ($("select#itemsToEnable_240_0").val() == "RegKey") {
                    $(".itemsToEnable_240_2,.itemsToEnable_240_4,.itemsToEnable_240_7,.itemsToEnable_240_8").hide();
                    $(".itemsToEnable_240_1,.itemsToEnable_240_3,.itemsToEnable_240_5,.itemsToEnable_240_6").show();
                } else if ($("select#itemsToEnable_240_0").val() == "ExtObj") {
                    $(".itemsToEnable_240_7,.itemsToEnable_240_8").hide();
                    $(".itemsToEnable_240_1,.itemsToEnable_240_2,.itemsToEnable_240_3,.itemsToEnable_240_4,.itemsToEnable_240_5,.itemsToEnable_240_6").show();
                } else if ($("select#itemsToEnable_240_0").val() == "TxtFile") {
                    $(".itemsToEnable_240_1,.itemsToEnable_240_7,.itemsToEnable_240_8").show();
                    $(".itemsToEnable_240_2,.itemsToEnable_240_3,.itemsToEnable_240_4,.itemsToEnable_240_5,.itemsToEnable_240_6").hide();
                } else {
                    $(".itemsToEnable_240_1,.itemsToEnable_240_2,.itemsToEnable_240_3,.itemsToEnable_240_4,.itemsToEnable_240_5,.itemsToEnable_240_6,.itemsToEnable_240_7,.itemsToEnable_240_8").hide();
                }
            }
            if ($(".panel-heading").hasClass("itemsToDisable_240_0")) {
                if ($("select#itemsToDisable_240_0").val() == "RegKey") {
                    $(".itemsToDisable_240_2,.itemsToDisable_240_4,.itemsToDisable_240_7,.itemsToDisable_240_8").hide();
                    $(".itemsToDisable_240_1,.itemsToDisable_240_3,.itemsToDisable_240_5,.itemsToDisable_240_6").show();
                } else if ($("select#itemsToDisable_240_0").val() == "ExtObj") {
                    $(".itemsToDisable_240_7,.itemsToDisable_240_8").hide();
                    $(".itemsToDisable_240_1,.itemsToDisable_240_2,.itemsToDisable_240_3,.itemsToDisable_240_4,.itemsToDisable_240_5,.itemsToDisable_240_6").show();
                } else if ($("select#itemsToDisable_240_0").val() == "TxtFile") {
                    $(".itemsToDisable_240_1,.itemsToDisable_240_7,.itemsToDisable_240_8").show();
                    $(".itemsToDisable_240_2,.itemsToDisable_240_3,.itemsToDisable_240_4,.itemsToDisable_240_5,.itemsToDisable_240_6").hide();
                } else {
                    $(".itemsToDisable_240_1,.itemsToDisable_240_2,.itemsToDisable_240_3,.itemsToDisable_240_4,.itemsToDisable_240_5,.itemsToDisable_240_6,.itemsToDisable_240_7,.itemsToDisable_240_8").hide();
                }
            }
            if ($(".panel-heading").hasClass("S00088DeviceList_88_2")) {
                if ($("select#S00088DeviceList_88_2").val() == "TCP") {
                    $(".S00088DeviceList_88_6").show();
                } else {
                    $(".S00088DeviceList_88_6").hide();
                }
            }
            $(".selectpicker").selectpicker('refresh');
        }

    });
//    $("#subformTextAreaField").modal("show");
}

function HideShowFields(VarId) {
    setTimeout(function () {
        switch (VarId) {
            case "S00297FileConfig_297_0":
                if ($("#S00297FileConfig_297_0").val() == "1") {
                    $("select#S00297FileConfig_297_0").val("1").change();
                    $(".S00297FileConfig_297_1,.S00297FileConfig_297_3").hide();
                    $(".S00297FileConfig_297_2").show();
                    $("#S00297FileConfig_297_1,#S00297FileConfig_297_3").val("");
                } else {
                    $("select#S00297FileConfig_297_0").val("0").change();
                    $(".S00297FileConfig_297_1,.S00297FileConfig_297_3").show();
                    $(".S00297FileConfig_297_2").hide();
                    $("#S00297FileConfig_297_2").val("");
                }
                break;
            case "itemsToEnable_233_0":

                if ($("select#itemsToEnable_233_0").val() == "RegKey") {
                    $(".itemsToEnable_233_5,.itemsToEnable_233_6").hide();
                    $(".itemsToEnable_233_1,.itemsToEnable_233_2,.itemsToEnable_233_3,.itemsToEnable_233_4").show();
                } else if ($("select#itemsToEnable_233_0").val() == "Service") {
                    $(".itemsToEnable_233_1,.itemsToEnable_233_5,.itemsToEnable_233_6").hide();
                    $(".itemsToEnable_233_2,.itemsToEnable_233_3,.itemsToEnable_233_4").show();
                } else if ($("select#itemsToEnable_233_0").val() == "StartFolder") {
                    $(".itemsToEnable_233_1,.itemsToEnable_233_5,.itemsToEnable_233_6").show();
                    $(".itemsToEnable_233_2,.itemsToEnable_233_3,.itemsToEnable_233_4").hide();
                } else {
                    $(".itemsToEnable_233_1,.itemsToEnable_233_2,.itemsToEnable_233_3,.itemsToEnable_233_4,.itemsToEnable_233_5,.itemsToEnable_233_6").hide();
                }
                $("#itemsToEnable_233_1,#itemsToEnable_233_2,#itemsToEnable_233_3,#itemsToEnable_233_4,#itemsToEnable_233_5,#itemsToEnable_233_6").val("");

                break;
            case "itemsToDisable_233_0":
                if ($("select#itemsToDisable_233_0").val() == "RegKey") {
                    $(".itemsToDisable_233_5,.itemsToDisable_233_6").hide();
                    $(".itemsToDisable_233_1,.itemsToDisable_233_2,.itemsToDisable_233_3,.itemsToDisable_233_4").show();
                } else if ($("select#itemsToDisable_233_0").val() == "Service") {
                    $(".itemsToDisable_233_1,.itemsToDisable_233_5,.itemsToDisable_233_6").hide();
                    $(".itemsToDisable_233_2,.itemsToDisable_233_3,.itemsToDisable_233_4").show();
                } else if ($("select#itemsToDisable_233_0").val() == "StartFolder") {
                    $(".itemsToDisable_233_1,.itemsToDisable_233_5,.itemsToDisable_233_6").show();
                    $(".itemsToDisable_233_2,.itemsToDisable_233_3,.itemsToDisable_233_4").hide();
                } else {
                    $(".itemsToDisable_233_1,.itemsToDisable_233_2,.itemsToDisable_233_3,.itemsToDisable_233_4,.itemsToDisable_233_5,.itemsToDisable_233_6").hide();
                }
                $("#itemsToDisable_233_1,#itemsToDisable_233_2,#itemsToDisable_233_3,#itemsToDisable_233_4,#itemsToDisable_233_5,#itemsToDisable_233_6").val("");
                break;
            case "itemsToEnable_240_0":
                if ($("select#itemsToEnable_240_0").val() == "RegKey") {
                    $(".itemsToEnable_240_2,.itemsToEnable_240_4,.itemsToEnable_240_7,.itemsToEnable_240_8").hide();
                    $(".itemsToEnable_240_1,.itemsToEnable_240_3,.itemsToEnable_240_5,.itemsToEnable_240_6").show();
                } else if ($("select#itemsToEnable_240_0").val() == "ExtObj") {
                    $(".itemsToEnable_240_7,.itemsToEnable_240_8").hide();
                    $(".itemsToEnable_240_1,.itemsToEnable_240_2,.itemsToEnable_240_3,.itemsToEnable_240_4,.itemsToEnable_240_5,.itemsToEnable_240_6").show();
                } else if ($("select#itemsToEnable_240_0").val() == "TxtFile") {
                    $(".itemsToEnable_240_1,.itemsToEnable_240_7,.itemsToEnable_240_8").show();
                    $(".itemsToEnable_240_2,.itemsToEnable_240_3,.itemsToEnable_240_4,.itemsToEnable_240_5,.itemsToEnable_240_6").hide();
                } else {
                    $(".itemsToEnable_240_1,.itemsToEnable_240_2,.itemsToEnable_240_3,.itemsToEnable_240_4,.itemsToEnable_240_5,.itemsToEnable_240_6,.itemsToEnable_240_7,.itemsToEnable_240_8").hide();
                }
                $("#itemsToEnable_240_2,#itemsToEnable_240_3,#itemsToEnable_240_4,#itemsToEnable_240_5,#itemsToEnable_240_6,.itemsToEnable_240_7,.itemsToEnable_240_8").val("");

                break;
            case "itemsToDisable_240_0":
                if ($("select#itemsToDisable_240_0").val() == "RegKey") {
                    $(".itemsToDisable_240_2,.itemsToDisable_240_4,.itemsToDisable_240_7,.itemsToDisable_240_8").hide();
                    $(".itemsToDisable_240_1,.itemsToDisable_240_3,.itemsToDisable_240_5,.itemsToDisable_240_6").show();
                } else if ($("select#itemsToDisable_240_0").val() == "ExtObj") {
                    $(".itemsToDisable_240_7,.itemsToDisable_240_8").hide();
                    $(".itemsToDisable_240_1,.itemsToDisable_240_2,.itemsToDisable_240_3,.itemsToDisable_240_4,.itemsToDisable_240_5,.itemsToDisable_240_6").show();
                } else if ($("select#itemsToDisable_240_0").val() == "TxtFile") {
                    $(".itemsToDisable_240_1,.itemsToDisable_240_7,.itemsToDisable_240_8").show();
                    $(".itemsToDisable_240_2,.itemsToDisable_240_3,.itemsToDisable_240_4,.itemsToDisable_240_5,.itemsToDisable_240_6").hide();
                } else {
                    $(".itemsToDisable_240_1,.itemsToDisable_240_2,.itemsToDisable_240_3,.itemsToDisable_240_4,.itemsToDisable_240_5,.itemsToDisable_240_6,.itemsToDisable_240_7,.itemsToDisable_240_8").hide();
                }
                $("#itemsToDisable_240_2,#itemsToDisable_240_3,#itemsToDisable_240_4,#itemsToDisable_240_5,#itemsToDisable_240_6,.itemsToDisable_240_7,.itemsToDisable_240_8").val("");
                break;
            case "S00296UsbDrive":
                var selectVal = $("select#S00296UsbDrive").val();
                if ($("select#S00296UsbDrive").val() == "2") {
                    $(".constS00296ImpersonateUser,.constS00296ImpersonatePassword,.constS00296ImpersonatePassword_confirmation,.constS00296UsbFileType,.constS00296UsbFileSizeType").show();
                } else {
                    $(".constS00296ImpersonateUser,.constS00296ImpersonatePassword,.constS00296ImpersonatePassword_confirmation,.constS00296UsbFileType,.constS00296UsbFileSizeType").hide();
                }
                $("select#S00296UsbDrive").val(selectVal);
                break;
            case "S00088DeviceList_88_2":
                if ($("select#S00088DeviceList_88_2").val() == "TCP") {
                    $(".S00088DeviceList_88_6").show();
                } else {
                    $(".S00088DeviceList_88_6").hide();
                }
                break;
            default:
                break;
        }
        $(".selectpicker").selectpicker('refresh');
    }, 100);
}

var rowcount = 0;
function saveTextAreaField() {
    var NH_ScheduleVarIds = ['S00006Schedule', 'S00012Schedule', 'S00027SchedECS', 'S00060ScheduleNew', 'S00088Schedule', 'S00090Schedule', 'S00095Schedule', 'S00096Schedule', 'S00097Schedule', 'S00098Schedule', 'S00176Schedule', 'S00177Schedule', 'S00211Schedule', 'S00228Schedule', 'S00232SchedECS', 'S00245Scheduler', 'S00282Scheduler', 'Schedule', 'schedule', 'Scrip224Schedule','S01003Schedule','S01006Schedule','S01011Schedule','S01012Schedule','Scrip1013Schedule','Scrip837Schedule','S0000421Schedule'];
    var NH_NoPlusIcon = ['S0050ProcessMaxTime', 'DialogTimeout', 'S00012ServerLocations', 'LatestDefDateStr', 'S00012DaysOld', 'MaxRandomDelay', 'MaxLocalRetries', 'LocalRetryDelay', 'S00090ServerLocations', 'LatestASaPDefDateStr', 'S00090OlderThan', 'S00095Threshold', 'S00282WearLevel', 'constS00296ImpersonateUser', 'constS00296ImpersonatePassword', 'constS00296ImpersonatePassword_confirmation', 'S00006Schedule', 'S00012Schedule', 'S00027SchedECS', 'S00060ScheduleNew', 'S00088Schedule', 'S00090Schedule', 'S00095Schedule', 'S00096Schedule', 'S00097Schedule', 'S00098Schedule', 'S00176Schedule', 'S00177Schedule', 'S00211Schedule', 'S00228Schedule', 'S00232SchedECS', 'S00245Scheduler', 'S00282Scheduler', 'Schedule', 'schedule', 'S00088ThrottleValue', 'S00247ServerLocations', 'Scrip224Schedule', // windows
                        'S0S01001WebName','S01004NewUser','S01004UserPswd','S01004UserPswd_confirmation','S01004DeleteUser','S01004CopmuterName','S01004ChangePassOfUser','S01004UserChPswd','S01004UserChPswd_confirmation','S01004ResetPasswdNowButton','S01005UserName','S01005Password','S01005Password_confirmation','S01007Disk','S01003Schedule','S01006Schedule','S01011Schedule','S01012Schedule','Scrip1013Schedule','S01002SomeLogFileList' //mac
                        ,'Scrip837ComRetries','Scrip837ErrorRetry','Scrip837PropagateRetryTime','Scrip837Schedule','S0000421Schedule','S00421AppURL','S00421DefaultURL','s421password','s421password_confirmation','S442Notification' ]; //linux
    var ordr_num = $("#ordrnum").val();
    var varid = $("#varid").val();
    var action = $("#action").val();
    var editing_id = $("#editing_id").val();
    var fielddartno = "";
    setTimeout(function () {
        $.ajax({
            url: "wizFunc.php?act=getChildByParent" + '&ordr=' + ordr_num,
            type: "POST",
            dataType: "json",
            data: '{}',
            async: false,
            success: function (data) {
                var params = [];
                var param_with_empty = [];
                var DartNumber = '';
                var i = 0;
                jQuery.each(data, function (i, val) {
                    var fieldvar = val.FieldVar;
                    var joinchar = val.JoinCharacter;
                    DartNumber = val.DartNo;
                    var value = "";
                    var fieldId = varid + '_' + val.DartNo + '_' + i;
                    var fieldValue = $('#' + fieldId).val();
                    var joinCharWNF = $.trim(val.JoinCharacterWithNextField);
                    if (joinCharWNF === "return") {
                        joinCharWNF = "\n";
                    }
                    if (val.GUIType === 'checkbox') {
                        var enteredval = val.EnteredValues;
                        var checkboxData = enteredval.split('/');
                        var isChecked = document.getElementById(fieldId).checked;

                        if (isChecked == "1") {
                            value = $.trim(fieldvar) + $.trim(joinchar) + "1";
                        } else {
                            value = $.trim(fieldvar) + $.trim(joinchar) + "0";
                        }

                    } else if (val.GUIType === 'select') {
                        
                        if($('#' + fieldId).attr("title") == "Minute" || $('#' + fieldId).attr("title") == "Hour" || $('#' + fieldId).attr("title") == "Day" || $('#' + fieldId).attr("title") == "Month" || $('#' + fieldId).attr("title") == "Weekday"){
                            if($('#' + fieldId).val() == "" || $('#' + fieldId).val() == null){
                                value = $.trim(fieldvar) + $.trim(joinchar) + "*";
                    } else {
                                value = $.trim(fieldvar) + $.trim(joinchar) + $('#' + fieldId).val();
                            }
                        } else {
                            value = $.trim(fieldvar) + $.trim(joinchar) + $('#' + fieldId).val();
                        }
                        
                    } else {
                        value = $.trim(fieldvar) + $.trim(joinchar) + $('#' + fieldId).val();//.replace(/,+$/, ''); //Bug 26097
                        if ($('#itemsToEnable_233_1').val() == '' && val.MasterId == '645') {
                            value = ' ';
                        }
                    }

                    params.push(value);

                    i++;
                });
                if (action == 'edit') {
                    id = editing_id;
                    row += '<td>';
                    $.each(params, function (key, val) {
                        if (val != "") {
                            var removeEnd = val.replace(/,+$/, '');
                            if(varid == "constS00296ImpersonatePassword_confirmation" || varid == "constS00296ImpersonatePassword" || 
                                    varid == "S01004UserPswd_confirmation" || varid == "S01004UserPswd" || varid == "S01004UserChPswd" 
                                    || varid == "S01004UserChPswd_confirmation" || varid == "S01005Password" || varid == 's421password_confirmation' || varid == 's421password'){
                                row += '<input id="'+varid+'_pass" type="password" readonly style="border: none;" value="'+removeEnd+'">';
                            } else {
                            row += removeEnd;
                            }
                            
                            if (key < params.length - 1) {
                                if (NH_ScheduleVarIds.indexOf(varid) != -1) {
                                    row += "<br>";
                                } else {
                                    row += ",";
                                }
                            }
                        }
                    });
                    row += '</td>';
                    row += '<td><a class="material-icons icon-ic_edit_24px" href="javascript:;" onclick="editRow(this,' + DartNumber + ')" data-toggle="modal" data-target="#subformTextAreaField" style="cursor:pointer;color:#00cc00;"></a><a class="material-icons icon-ic_delete_24px" href="javascript:;" onclick="deleteRow(this,' + DartNumber + ')" style="cursor:pointer;color:red;"></a>';

                    if (NH_ScheduleVarIds.indexOf(varid) != -1) {
                        row += '</td>';
                    } else {
                        var fielddartno = varid + "_" + DartNumber;
                        var rownumtoedit = $("table." + fielddartno + " tr").length - 1;
                        if (rownumtoedit == editing_id) {
                            if (NH_NoPlusIcon.indexOf(varid) == -1) {
                                row += '<a class="material-icons icon-ic_add_24px" href="javascript:;" onclick="createTextAreaForm(\'add\',\'' + ordr_num + '\',\'' + varid + '\',\'\',\'\',\'' + DartNumber + '\')" style="cursor:pointer;color:#48b2e4;;" data-toggle="modal" data-target="#subformTextAreaField"></a>';
                            }
                            row += '</td>';
                        } else {
                            row += '</td>';
                        }

                    }
                } else {
                    var row = "";
                    fielddartno = varid + "_" + DartNumber;
                    if ($("table." + fielddartno + " tr").length == 0) {
                        row += "<tr></tr>";
                    }
                    row += "<tr>";
                    row += '<td>';
                    $.each(params, function (key, val) {
                        if (val != "") {
                            var removeEnd = val.replace(/,+$/, '');
                            if(varid == "constS00296ImpersonatePassword_confirmation" || varid == "constS00296ImpersonatePassword" || 
                                    varid == "S01004UserPswd_confirmation" || varid == "S01004UserPswd" || varid == "S01004UserChPswd" || 
                                    varid == "S01004UserChPswd_confirmation" || varid == "S01005Password" || varid == 's421password_confirmation' || varid == 's421password'){
                                row += '<input id="'+varid+'_pass" type="password" readonly style="border: none;" value="'+removeEnd+'">';
                            } else {
                            row += removeEnd;
                            }
                            if (key < params.length - 1) {
                                if (NH_ScheduleVarIds.indexOf(varid) != -1) {
                                    row += "<br>";
                                } else {
                                    row += ",";
                                }
                            }
                        }
                    });
                    row += '</td>';
                    row += '<td><a class="material-icons icon-ic_edit_24px" href="javascript:;" onclick="editRow(this,' + DartNumber + ')" data-toggle="modal" data-target="#subformTextAreaField" style="cursor:pointer;color:#00cc00;"></a><a class="material-icons icon-ic_delete_24px" href="javascript:;" onclick="deleteRow(this,' + DartNumber + ')" style="cursor:pointer;color:red;"></a>';
                    if (NH_ScheduleVarIds.indexOf(varid) != -1) {
                        row += '</td></tr>';
                    } else {
                        if (NH_NoPlusIcon.indexOf(varid) == -1) {
                            row += '<a class="material-icons icon-ic_add_24px" href="javascript:;" onclick="createTextAreaForm(\'add\',\'' + ordr_num + '\',\'' + varid + '\',\'\',\'\',\'' + DartNumber + '\')" style="cursor:pointer;color:#48b2e4;;" data-toggle="modal" data-target="#subformTextAreaField"></a>';
                        }
                        row += '</td></tr>';
                    }
                }

                var flag = 0;

                $(".TextAreaValidity").each(function () {
                    var getClass = $(this).attr("id");
                    if ($.trim($(this).val()) == "" && $("." + getClass).css("display") != "none") {
                        flag = 1;
                    }
                });

                if ($("div").hasClass("S00017SomeExeFileList_17_0")) {
                    var ext = $('#S00017SomeExeFileList_17_0').val().split('.'); //.pop().toLowerCase();
                    if (ext[1] != 'exe' || /\s/g.test(ext[0])) {   //$.inArray(ext, ['exe']) == -1
                        $("#errorTextAreaField").html("");
                        $("#errorTextAreaField").html("Invalid File Extension!");
                        setTimeout(function () {
                            $("#errorTextAreaField").html("");
                        }, 3000);
                    }
                }
                if (flag == 1) {
                    $("#errorTextAreaField").html("");
                    $("#errorTextAreaField").html("Please enter all * required fields");
                    setTimeout(function () {
                        $("#errorTextAreaField").html("");
                    }, 3000);
                } else {
                    $("#errorTextAreaField").html("");
                    fielddartno = varid + "_" + DartNumber;
                    var rownumtoedit = $("table." + fielddartno + " tr").length - 1;
                    if (action == 'add') {
                        if ($("table." + fielddartno + " tr").length <= 0) {
                            $("table." + fielddartno + " tbody").append(row);
                            $("span#" + varid).show();
                        } else {
                            if (rownumtoedit > 0) {

                                var tdLast = $('table.' + fielddartno + ' tr:eq(' + rownumtoedit + ')').children('td').length - 1;
                                var tdData = '<td><a class="material-icons icon-ic_edit_24px" href="javascript:;" onclick="editRow(this,' + DartNumber + ')" data-toggle="modal" data-target="#subformTextAreaField" style="cursor:pointer;color:#00cc00;"></a><a class="material-icons icon-ic_delete_24px" href="javascript:;" onclick="deleteRow(this,' + DartNumber + ')" style="cursor:pointer;color:red;"></a></td>';
                                $('table.' + fielddartno + ' tr:eq(' + rownumtoedit + ') td:eq(' + tdLast + ')').remove();
                                $('table.' + fielddartno + ' tr:eq(' + rownumtoedit + ')').append(tdData);
                            }
                            $("table." + fielddartno + " tr:last").after(row);
//                        if($("table." + fielddartno + " tr:eq(0)").is(":empty")){
//                            $('table.' + fielddartno + ' tr:eq(0)').remove();
//                        }
                            $("span#" + varid).show();
                        }

                    } else if (action == 'edit') {
                        var editpass = editing_id - 1;
                        if(varid == "constS00296ImpersonatePassword_confirmation" || varid == "constS00296ImpersonatePassword" || 
                                varid == "S01004UserPswd_confirmation" || varid == "S01004UserPswd" || varid == "S01004UserChPswd" || 
                                varid == "S01004UserChPswd_confirmation" || varid == "S01005Password" || varid == 's421password_confirmation' || varid == 's421password'){
                            $('table.' + fielddartno + ' tr:eq(' + editpass + ')').children('td').remove();
                            $('table.' + fielddartno + ' tr:eq(' + editpass + ')').html(row);
                        } else {
                        $('table.' + fielddartno + ' tr:eq(' + editing_id + ')').children('td').remove();
                        $('table.' + fielddartno + ' tr:eq(' + editing_id + ')').html(row);
                    }

                    }

                    $('#subformTextAreaField').modal('hide');
                    $("." + fielddartno + "_A").hide();
                }


            }
        });
    }, 1200);
}

function deleteRow(element, dartno) {
    var NH_NoPlusIcon = ['S0050ProcessMaxTime', 'DialogTimeout', 'S00012ServerLocations', 'LatestDefDateStr', 'S00012DaysOld', 'MaxRandomDelay', 'MaxLocalRetries', 'LocalRetryDelay', 'S00090ServerLocations', 'LatestASaPDefDateStr', 'S00090OlderThan', 'S00095Threshold', 'S00282WearLevel', 'constS00296ImpersonateUser', 'constS00296ImpersonatePassword', 'constS00296ImpersonatePassword_confirmation', 'S00006Schedule', 'S00012Schedule', 'S00027SchedECS', 'S00060ScheduleNew', 'S00088Schedule', 'S00090Schedule', 'S00095Schedule', 'S00096Schedule', 'S00097Schedule', 'S00098Schedule', 'S00176Schedule', 'S00177Schedule', 'S00211Schedule', 'S00228Schedule', 'S00232SchedECS', 'S00245Scheduler', 'S00282Scheduler', 'Schedule', 'schedule', 'S00088ThrottleValue', 'Scrip224Schedule', // windows
                        'S0S01001WebName','S01004NewUser','S01004UserPswd','S01004UserPswd_confirmation','S01004DeleteUser','S01004CopmuterName','S01004ChangePassOfUser','S01004UserChPswd','S01004UserChPswd_confirmation','S01004ResetPasswdNowButton','S01005UserName','S01005Password','S01005Password_confirmation','S01007Disk','S01003Schedule','S01006Schedule','S01011Schedule','S01012Schedule','Scrip1013Schedule','S01002SomeLogFileList' //mac
                        ,'Scrip837ComRetries','Scrip837ErrorRetry','Scrip837PropagateRetryTime','Scrip837Schedule','S0000421Schedule','S00421AppURL','S00421DefaultURL','s421password','s421password_confirmation','S442Notification' ]; // linux
    var totalTds = element.parentNode.parentNode.getElementsByTagName("td").length;
    var rowId = element.parentNode.parentNode.rowIndex;
    var tableId = $(element).closest('table').attr('id');
    var masterid = "";
    $.ajax({
        url: 'wizFunc.php',
        data: 'act=Get_MasterIdFn&name=' + tableId + '&dart=' + dartno,
        type: 'GET',
        dataType: 'text',
        async: false,
        success: function (mid) {
            masterid = $.trim(mid);
        }
    });
    var fielddartno = tableId + "_" + dartno;
    var rownumtoedit = $("table." + fielddartno + " tr").length - 1;
    if (rownumtoedit == rowId && rowId > 0) {
        var tdLast = $('table.' + fielddartno + ' tr:eq(' + rowId + ')').children('td').length - 1;
        var tdData = '<td><a class="material-icons icon-ic_edit_24px" href="javascript:;" onclick="editRow(this,' + dartno + ')" data-toggle="modal" data-target="#subformTextAreaField" style="cursor:pointer;color:#00cc00;"></a><a class="material-icons icon-ic_delete_24px" href="javascript:;" onclick="deleteRow(this,' + dartno + ')" style="cursor:pointer;color:red;"></a>';
        if (NH_NoPlusIcon.indexOf(tableId) == -1) {
            tdData += '<a class="material-icons icon-ic_add_24px" href="javascript:;" onclick="createTextAreaForm(\'add\',\'' + masterid + '\',\'' + tableId + '\',\'\',\'\',\'' + dartno + '\')" style="cursor:pointer;color:#48b2e4;;" data-toggle="modal" data-target="#subformTextAreaField"></a>';
        }
        tdData += '</td>';
        var rowIdmin = rowId - 1;
        if ($('table.' + fielddartno + ' tr:eq(' + rowIdmin + ') td').length == $('table.' + fielddartno + ' tr:eq(' + rowId + ') td').length) {
            $('table.' + fielddartno + ' tr:eq(' + rowIdmin + ') td:eq(' + tdLast + ')').remove();
            $('table.' + fielddartno + ' tr:eq(' + rowIdmin + ')').append(tdData);
        } else {
            $("." + fielddartno + "_A").show();
        }
    }

    $('table.' + fielddartno + ' tr:eq(' + rowId + ')').remove();
    if (rownumtoedit == 0 || rowId == 0) {
        if ($("table." + fielddartno + " tr").length == 0) {
            $("table#" + tableId + " tr:last").after("<tr></tr>");
        }
        $("." + fielddartno + "_A").show();
    }
}

function editRow(element, dartno) {
    var NH_ScheduleVarIds = ['S00006Schedule', 'S00012Schedule', 'S00027SchedECS', 'S00060ScheduleNew', 'S00088Schedule', 'S00090Schedule', 'S00095Schedule', 'S00096Schedule', 'S00097Schedule', 'S00098Schedule', 'S00176Schedule', 'S00177Schedule', 'S00211Schedule', 'S00228Schedule', 'S00232SchedECS', 'S00245Scheduler', 'S00282Scheduler', 'Schedule', 'schedule', 'Scrip224Schedule','S01003Schedule','S01006Schedule','S01011Schedule','S01012Schedule','Scrip1013Schedule','Scrip837Schedule','S0000421Schedule'];
    var totalTds = element.parentNode.parentNode.getElementsByTagName("td").length;
    var rowId = element.parentNode.parentNode.rowIndex;
    var tableId = $(element).closest('table').attr('id');
    var fieldVals = [];
    var joinFields = "";
    var masterid = "";
    $.ajax({
        url: 'wizFunc.php',
        data: 'act=Get_MasterIdFn&name=' + tableId + '&dart=' + dartno,
        type: 'GET',
        dataType: 'text',
        async: false,
        success: function (mid) {
            masterid = $.trim(mid);
        }
    });
    for (var i = 0; i < totalTds; i++) {
        if (NH_ScheduleVarIds.indexOf(tableId) != -1) {
            //if (tableId == "S00060ScheduleNew" || tableId == "S00228Schedule" || tableId == "Schedule" || tableId == "S00088Schedule" || tableId == "schedule" || tableId == "S00006Schedule" || tableId == "S00012Schedule" || tableId == "S00027SchedECS" || tableId == "S00090Schedule" || tableId == "S00095Schedule" || tableId == "S00096Schedule" || tableId == "S00097Schedule" || tableId == "S00098Schedule" || tableId == "S00176Schedule" || tableId == "S00177Schedule" || tableId == "S00211Schedule" || tableId == "S00232SchedECS" || tableId == "S00245Scheduler" || tableId == "S00282Scheduler") {
            fieldVals.push(element.parentNode.parentNode.getElementsByTagName("td")[i].innerHTML);
        } else {
            fieldVals.push(element.parentNode.parentNode.getElementsByTagName("td")[i].textContent);
        }

        joinFields = fieldVals.join(',');
    }
    createTextAreaForm("edit", masterid, tableId, rowId, joinFields, dartno);
}

function deletetextarea_val(id) {
    $("#" + id).remove();
}

function getWizValueEnteredData(Ordr, wiz_name_id) {
    var value = "";
    $.ajax({
        url: "wizFunc.php?act=getWizValueEnteredData" + '&ordr=' + Ordr + '&wiz_name_id=' + wiz_name_id,
        type: "GET",
        dataType: "text",
        data: '{}',
        async: false,
        success: function (data) {
            value = data;
        }

    });

    return value;
}

function getMultiSelection_option(wiz_mstr_VSF, Ordr, wiz_name_id) {
    var option = "";
    $.ajax({
        url: "wizFunc.php?act=build_multiselct&vsf=" + wiz_mstr_VSF + '&ordr=' + Ordr + '&wiz_name_id=' + wiz_name_id,
        type: "POST",
        dataType: "json",
        data: '{}',
        async: false,
        success: function (data) {
            option = data;
        }

    });

    return option;
}

function getWizardNameDetails(dartno, wiz_name_id) {
    var option = "";
    $.ajax({
        url: "wizFunc.php?act=getWizardNameDetails&dartno=" + dartno + '&wiz_name_id=' + wiz_name_id,
        type: "POST",
        dataType: "text",
        data: '{}',
        async: false,
        success: function (data) {
            option = data;
        }

    });

    return $.trim(option);
}

function Get_SelectTransferList(Level, Ordr, WnId) {
    var option = "";
    $.ajax({
        url: "wizFunc.php?act=getSelectTransferList&Ordr=" + Ordr + '&Level=' + Level + '&WnId=' + WnId,
        type: "POST",
        dataType: "json",
        data: '{}',
        async: false,
        success: function (data) {
            option = data;
        }

    });

    return $.trim(option);
}

function submitEachDartValuesAv(value, dartno, varlist, wn_id) {
    var site = $('#valueSearch').val();
    var selectedview = $('#searchType').val();
    var leveltwoval = $('#leveltwoval').val();
    var configType = $('#configType').val();
    var profileName = $('#profileName').val();
    $.ajax({
        type: 'POST',
        url: 'wizFunc.php?' + 'act=saveSubmitMainDetailsAv&' + value + '&dartno=' + dartno + '&site=' + site + '&confirm=yes&selectedview=' + selectedview + '&varlist=' + varlist + '&wn_id=' + wn_id + '&leveltwoval=' + leveltwoval + '&configType=' + configType + '&profileName=' + profileName,
        async: false,
        success: function (resp) {
            if (resp == "success") {
                var result = "<span>Successfully updated the values</span>";
                showModal("3", result);
            } else {
                var result = "<span>Error submitting the values.</span>";
                showModal("2", result);
            }
        }
    });
}

function submitEachDartValues(value, dartno, varlist, wn_id) {

    var site = $('#valueSearch').val();
    var selectedview = $('#searchType').val();
    var leveltwoval = $('#leveltwoval').val();
    var configType = $('#configType').val();
    var profileName = $('#profileName').val();
//    console.log(value+"#"+dartno+"#"+varlist+"#"+wn_id+"#"+site+"#"+selectedview);
    $.ajax({
        type: 'POST',
        url: 'wizFunc.php?' + 'act=saveSubmitMainDetails&' + value + '&dartno=' + dartno + '&site=' + site + '&confirm=yes&selectedview=' + selectedview + '&varlist=' + varlist + '&wn_id=' + wn_id + '&leveltwoval=' + leveltwoval + '&configType=' + configType + '&profileName=' + profileName,
        async: false,
        success: function (resp) {
            $("#ajaxloaderr").hide();
            var result = "<span>Successfully updated the values.</span>";
            showModal("3", result);
//            if (resp.includes("Success") == true || $.trim(resp) == "") {
//                var result = "<span>Successfully updated the values</span>";
//                showModal("3", result);
//            } else {
//                var result = "<span>Error occurred while submitting the data.</span>";
//                showModal("2", result);
//            }
        }
    });
}

function saveVariableDetailsPass(type) {

    var Level = $('#valueSearch').val();
    var selectedview = $('#searchType').val();
//    var portNumber = $('#ProxyPort').val();
//    var address = $('#ProxyAddress').val();
//    
//    if (address !== undefined) { 
//        if (!ValidateIPaddress(address)) {
//            $("#errormsg").show();
//            $("#errormsg").html('');
//            $("#errormsg").html('<span>Please enter valid ip address</span>');
//            setTimeout(function() {
//                $("#errormsg").fadeOut(3600);
//            }, 3600);
//            return false;
//        }
//    }
//
//    if (portNumber !== undefined) { 
//        if (!$.isNumeric(portNumber) ) {
//            $("#errormsg").show();
//            $("#errormsg").html('');
//            $("#errormsg").html('Please enter only numeric value for port');
//            setTimeout(function() {
//                $("#errormsg").fadeOut(3600);
//            }, 3600);
//            return false;
//        }
//        if(portNumber.length != 4) {
//            $("#errormsg").show();
//            $("#errormsg").html('');
//            $("#errormsg").html('Please enter valid port number');
//            setTimeout(function() {
//                $("#errormsg").fadeOut(3600);
//            }, 3600);
//            return false;
//        }
//    }

    if (Level == "All") {
        showModal("2", "<span>Please select SITE from right pane, to access Configuration Module.</span><br><span><(NOTE: This feature is not available for scope 'ALL')</span>");
    } else {

        $.ajax({
            url: "../lib/l-ajax.php",
            data: "function=AJAX_GetAvailStatus&searchType=" + selectedview + "&searchValue=" + Level,
            type: "POST",
            dataType: "text",
            success: function (resp) {
                if (resp.includes("notfound")) {
                    showModal("2", "<span>There are no machines available in the selected Site/Group</span>");
                } else {
                    $("#configType").val(type);
                    $("#ajaxloaderr").show();
                    if (type == "avira") {
                        $("#ajaxloaderrAv").show();
                        setTimeout(function () {
                            SaveAviraConfigDetails();
                        }, 500);
                    } else if ((type.trim()) === "nanoheal") {
                        $("#ajaxloaderr").show();
                        document.getElementById("submit").value = "Submitting";
                        $("#submit").text("Submitting");
                        setTimeout(function () {
                            saveVariableDetails();
                        }, 500);
                    } else if (type == 'scheduler') {
                        var displaymodeval = $("#displaymode").val();
                        if (displaymodeval != "") {
                            $("#ajaxloaderrAv").show();
                            setTimeout(function () {
                                SetSchedulerDetails();
                            }, 500);
                        } else {
                            $("#phase5error,.phase5").fadeIn();
                            setTimeout(function () {
                                $("#phase5error").fadeOut();
                            }, 3000);
                        }
                    }
                }

            }
        });


    }
}

function SaveAviraConfigDetails() {

    var wizid = $("#wizid").val();
    var site = $('#valueSearch').val();
    var selectedview = $('#searchType').val();
    var ProfileName = $('#profileName').val();

    var excpath = "";
    var excfile = "";
    var excusr = "";
    var excurl = "";
    var SecondaryActionForInfected = "";
    var ExcDifListMime = "";
    var PrimaryActionForInfected = "";

    if ($.trim(site) === '' || $.trim(selectedview) === '') {
        showModal("2", "<span>Please select level (Site/Group/Machine)</span>");
    } else {
        if ($.trim(site) === 'All') {
            showModal("2", "<span>Configuration changes are not allowed in 'All'</span>");
        } else {
            if ($("select").hasClass('PrimaryActionForInfected')) {
                var PAFI = $("#PrimaryActionForInfected").val();
                PrimaryActionForInfected = '&PriACForInfect=' + PAFI;
            }
            if ($("select").hasClass('SecondaryActionForInfected')) {
                var SAFI = $("#SecondaryActionForInfected").val();
                SecondaryActionForInfected = '&SecACForInfect=' + SAFI;
            }
            if ($("select").hasClass('ExclDiffListMime')) {
                var EDLM = $("#ExclDiffListMime").val();
                var encodeEDLM = btoa(EDLM);
                ExcDifListMime = '&ExcDifListMime=' + encodeEDLM;
            }
            if ($("select").hasClass('Path')) {
                var optionlist;
                var optionarray = [];
                $(".Path option").each(function () {
                    optionlist = $(this).text();
                    optionarray.push(optionlist);
                });
                excpath = '&excpath=' + optionarray;
            }
            if ($("select").hasClass('OnAccessExcludedProcess')) {
                var optionlist;
                var optionarray = [];
                $(".OnAccessExcludedProcess option").each(function () {
                    optionlist = $(this).text();
                    optionarray.push(optionlist);
                });
                excfile = '&excfile=' + optionarray;
            }
            if ($("select").hasClass('ExclListUser')) {
                var optionlist;
                var optionarray = [];
                $(".ExclListUser option").each(function () {
                    optionlist = $(this).text();
                    optionarray.push(optionlist);
                });
                excusr = '&excusr=' + optionarray;
            }
            if ($("select").hasClass('ExclListUrl')) {
                var optionlist;
                var optionarray = [];
                $(".ExclListUrl option").each(function () {
                    optionlist = $(this).text();
                    optionarray.push(optionlist);
                });
                excurl = '&excurl=' + optionarray;
            }
            if ($("div").hasClass('EntPass')) {
                var EntPass = $("#EntPass").val();
                var ConfPass = $("#ConfPass").val();

                if (EntPass !== ConfPass) {
                    $("#ajaxloaderrAv").hide();
                    $("#errormsg").show();
                    $("#errormsg").html("The entered password was not correctly confirmed! Please confirm your password again.");
//                    $("#EntPass").val("");
//                    $("#ConfPass").val("");
//                    $(":checkbox").val("0");
//                    $(":checkbox").prop("checked", false);
                    setTimeout(function () {
                        $("#errormsg").fadeOut();
                        $("#errormsg").html("");
                    }, 3000);
                    return false;
                }
            }

            var url = 'wizFunc.php?act=saveSubmitMainDetailsAv&site=' + site + '&profileName=' + ProfileName + '&wn_id=' + wizid + '' + excpath + '' + excfile + '' + excusr + '' + excurl + '' + SecondaryActionForInfected + '' + ExcDifListMime + '' + PrimaryActionForInfected;

            var formData = $("form#configwiz_formAv").serialize();

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                async: false,
                success: function (resp) {
                    if (resp != "failed") {
                        $("#ajaxloaderrAv").hide();
                        var result = "<span>Successfully updated the values</span>";
                        showModal("3", result);

                        //Node Job Call Here -->
                        var VariableString = btoa(resp);
                        CreateJobAndExecute("275", VariableString, "WMI", "Wmi Command", ProfileName, "");
                    } else {
                        var result = "<span>Error submitting the values.</span>";
                        showModal("2", result);
                    }
                }
            });
        }
    }
}

function saveVariableDetailsAv() {
    var site = $('#valueSearch').val();
    var selectedview = $('#searchType').val();
    if ($.trim(site) === '' || $.trim(selectedview) === '') {
        showModal("2", "Please select level (Site/Group/Machine)");
    } else {
        var wiz_name_id = $('#wizid').val();
        $.ajax({
            url: "wizFunc.php?act=showWizards&wiz_name_id=" + wiz_name_id,
            type: "POST",
            dataType: "json",
            data: '{}',
            async: false,
            success: function (data) {
                $("#ajaxloaderr").show();
                var dart = "";
                $.each(data, function (i, field_val) {
//                    console.debug(field_val);
                    var params = [];
                    var varid = $.trim(getWizardNameDetails(field_val.DartNo, wiz_name_id));
//                    console.log("#"+varid+"#");
                    if (dart !== field_val.VarID) {
                        var str = varid.split(",");
                        for (var i = 0; i < str.length; i++) {
                            var fieldvar = field_val.FieldVar;
                            var joinchar = field_val.JoinCharacter;
                            var value = "";

//                            var match = "";
                            if (field_val.GUIType === 'checkbox') {
                                var enteredval = field_val.EnteredValues;
                                var checkboxData = enteredval.split('/');
//                                var isChecked = document.getElementById(str[i]).checked;
                                var isChecked = $("#" + str[i]).val();

                                if (isChecked) {
                                    //alert(isChecked+'-'+checkboxData[0]); 
                                    value = $.trim(fieldvar) + $.trim(joinchar) + checkboxData[0];
                                } else {
                                    //alert(isChecked+'-'+checkboxData[1]); 
                                    value = $.trim(fieldvar) + $.trim(joinchar) + checkboxData[1];
                                }

                            } else if (field_val.GUIType == "textarea") {
                                var textarea_data = $("#" + field_val.VarID + " div[id]").map(function () {
                                    var value = $("#" + this.id).attr('value');
                                    if ($.trim(value) !== "") {
                                        return value;
                                    }
                                }).get().join("\n");
                                value = textarea_data;
                            } else if (field_val.GUIType == "radio") {
                                var radVal = $('input[name=' + field_val.VarID + ']:checked').val();
                                value = $.trim(fieldvar) + $.trim(joinchar) + radVal;
                            } else {
                                var strval = $('#' + str[i]).val();
                                if (strval == "" || strval == null || strval == "null") {
//                                    value = $.trim(fieldvar) + $.trim(joinchar) + "";
                                    var radVal = $('input[type=radio]:checked').val();
                                    value = $.trim(fieldvar) + $.trim(joinchar) + radVal;
                                } else if (strval == "on") {
                                    var enteredval = field_val.EnteredValues;
                                    var checkboxData = enteredval.split('/');
                                    var isChecked = document.getElementById(str[i]).checked;
                                    if (isChecked) {
                                        value = $.trim(fieldvar) + $.trim(joinchar) + checkboxData[0];
                                    } else {
                                        value = $.trim(fieldvar) + $.trim(joinchar) + checkboxData[1];
                                    }
                                } else {
                                    value = $.trim(fieldvar) + $.trim(joinchar) + strval;
                                }

                            }
                            params.push(str[i] + "=" + escape(value));
                        }
//                        console.log("dart no : " + field_val.DartNo + " - " + params.join("&"));
                        submitEachDartValuesAv(params.join("&"), field_val.DartNo, varid, wiz_name_id);
                    }
                    dart = field_val.VarID;
                });
            }
        });
    }
}


function saveVariableDetails() {
    var site = $('#valueSearch').val();
    var selectedview = $('#searchType').val();
    document.getElementById("submit").value = "Submitting";
    $("#submit").text("Submitting");
//    var leveltwoval = $('#leveltwoval').val();
    if ($.trim(site) === '' || $.trim(selectedview) === '') {
        showModal("2", "<span>Please select level (Site/Group/Machine)</span>");
        document.getElementById("submit").value = "Submit";
        $("#submit").text("Submit");
    } else {
        
        var wiz_name_id = $('#wizid').val();

        $.ajax({
            url: "wizFunc.php?act=showWizards&wiz_name_id=" + wiz_name_id,
            type: "POST",
            dataType: "json",
            data: '{}',
            async: false,
            success: function (data) {
                var TA_Dart_Array = [];
                $.ajax({
                    url: 'wizFunc.php',
                    data: 'act=GET_TextAreaDarts',
                    type: 'GET',
                    dataType: 'json',
                    async: false,
                    success: function (jsondata) {
                        $.each(jsondata, function (key, val) {
                            TA_Dart_Array.push(val.DartNo);
                        });
                    }
                });

                var sql = [];
                var dart = "";
                var is288Found = false;
                var mainDataRetrieval = "";
                var variablearray = {};

                var dartlist = '';
                $.each(data, function (j, field_val1) {
                    dartlist += field_val1.DartNo + ",";
                });
                dartlist = dartlist.slice(0, -1);
                var varid = $.trim(getWizardNameDetails(dartlist, wiz_name_id));
                $.each(data, function (i, field_val) {

                    var params = [];

                    var mainarr = [];
//                    if (field_val.DartNo == "228") {
                    if (TA_Dart_Array.indexOf(field_val.DartNo) != -1) {
                        is288Found = true;
                        mainDataRetrieval = data;
                    } else {

                        var str = varid.split(",");

                        for (var i = 0; i < str.length; i++) {
                            var fieldvar = field_val.FieldVar;
                            var joinchar = field_val.JoinCharacter;
                            var value = "";
                            var uniqIdVal = field_val.VarID + "JR" + field_val.DartNo;
                            if ($.trim(str[i]) === $.trim(field_val.VarID)) {

                                if (field_val.GUIType === 'checkbox') {
                                    var enteredval = field_val.EnteredValues;
                                    var checkboxData = enteredval.split('/');

//                                    var isChecked = $("#" + str[i]).val();

//                                    if ($("#" + str[i]).is(":checked")) {
                                    if ($("." + uniqIdVal).is(":checked")) {

                                        value = $.trim(fieldvar) + $.trim(joinchar) + $("." + uniqIdVal).val();
                                    } else {

                                        value = $.trim(fieldvar) + $.trim(joinchar) + $("." + uniqIdVal).val();
                                    }

                                } else if (field_val.GUIType === 'button') {
                                    var enteredval = field_val.EnteredValues;
                                    var checkboxData = enteredval.split('/');

                                    var isChecked = $("." + uniqIdVal).val();

                                    if (isChecked == "1" || isChecked == "4") {

                                        value = $.trim(fieldvar) + $.trim(joinchar) + "4";
                                    } else {

                                        value = $.trim(fieldvar) + $.trim(joinchar) + "";
                                    }

                                } else if (field_val.GUIType == "textarea") {
//                                    var textarea_data = $("#" + field_val.VarID + " div[id]").map(function() {
//                                        var value = $("#" + this.id).attr('value');
//                                        if ($.trim(value) !== "") {
//                                            return value;
//                                        }
//                                    }).get().join("\n");
//                                    value = textarea_data;
//
//                                    var textarea_data_new = $("#" + field_val.VarID + " div[id]").map(function() {
//                                        var value = $("#" + this.id).attr('value');
//                                        if ($.trim(value) !== "") {
//                                            return value;
//                                        }
//                                    }).get().join("##");

//                                    var jsonJoinCharacter = [];
//                                    var jsonFieldVar = [];
//                                    $.ajax({
//                                        url: 'wizFunc.php',
//                                        data: 'act=Get_JoinCharsFn&name=' + field_val.VarID + '&dart=' + field_val.DartNo,
//                                        type: 'GET',
//                                        dataType: 'JSON',
//                                        async: false,
//                                        success: function (jsondata) {
//                                            $.each(jsondata, function (index, element) {
//                                                jsonJoinCharacter.push(element.JoinCharacter);
//                                                jsonFieldVar.push(element.FieldVar);
//                                            });
//                                        }
//                                    });

                                    var textarea_data = $('table.' + field_val.VarID + '_' + field_val.DartNo + ' > tbody > tr').map(function () {
                                        var i = 0;
                                        var entireData = $(this).find("td:not(:last-child)").map(function (i) {
                                            if ($(this).html() != "") {
//                                                var char1 = "";
//                                                var char2 = "";
//                                                if (jsonFieldVar[i] == "" || jsonFieldVar[i] == null || jsonFieldVar[i] == 'undefined') {
//                                                    char1 = "";
//                                                } else {
//                                                    char1 = jsonFieldVar[i];
//                                                }
//                                                if (jsonJoinCharacter[i] == "" || jsonJoinCharacter[i] == null || jsonJoinCharacter[i] == 'undefined') {
//                                                    char2 = "";
//                                                } else {
//                                                    char2 = jsonJoinCharacter[i];
//                                                }
                                                var data = "";
                                                if(field_val.VarID == "constS00296ImpersonatePassword_confirmation" || field_val.VarID == "constS00296ImpersonatePassword" || 
                                                    field_val.VarID == "S01004UserPswd_confirmation" || field_val.VarID == "S01004UserPswd" || field_val.VarID == "S01004UserChPswd" || 
                                                    field_val.VarID == "S01004UserChPswd_confirmation" || field_val.VarID == "S01005Password" || varid == 's421password_confirmation' || varid == 's421password'){
                                                    data = $("#"+field_val.VarID+"_pass").val();
                                                } else {
                                                    data = $(this).html();
                                                }
                                                return data;
                                                i++;
                                            }
                                        }).get().join(",");
                                        return entireData;
                                    }).get().join("\n");
                                    value = textarea_data;


                                } else if (field_val.GUIType == "radio") {
                                    var radVal = $('input[name=' + field_val.VarID + ']:checked').val();
                                    value = $.trim(fieldvar) + $.trim(joinchar) + radVal;

                                } else if (field_val.GUIType == "select") {
                                    var selVal;
                                    if (field_val.DartNo == "296" && field_val.VarID == "S00296UsbDrive") {
                                        selVal = $("select#S00296UsbDrive").val();
                                    } else {
                                        selVal = $("select#" + field_val.VarID).val();
                                    }
                                    value = $.trim(fieldvar) + $.trim(joinchar) + selVal;

                                } else {
                                    var strval = $('.' + uniqIdVal).val();
                                    if (strval == "" || strval == null || strval == "null") {
                                        value = $.trim(fieldvar) + $.trim(joinchar) + "";
                                    } else if (strval == "on") {
                                        var enteredval = field_val.EnteredValues;
                                        var checkboxData = enteredval.split('/');
                                        var isChecked = document.getElementById(str[i]).checked;
                                        if (isChecked == "1") {
                                            value = $.trim(fieldvar) + $.trim(joinchar) + "1";
                                        } else {
                                            value = $.trim(fieldvar) + $.trim(joinchar) + "0";
                                        }
                                    } else {
                                        value = $.trim(fieldvar) + $.trim(joinchar) + strval;
                                    }

                                }

                                params.push(uniqIdVal + "=" + escape(value));
                                variablearray[uniqIdVal] = field_val.DartNo + "india" + escape(value) + "india" + field_val.GUIType;

                            }
                        }
                    }
                    dart = field_val.VarID;

                });

                if (is288Found) {
                    saveVariableDetails228(mainDataRetrieval, wiz_name_id, TA_Dart_Array);
                } else {
                    VARSET_API_CALL(variablearray, wiz_name_id);
//                    saveVariableDetails228(mainDataRetrieval, wiz_name_id, TA_Dart_Array);
                }

            }
        });
    }
}

function limitCalls(value, dartno, varlist, wn_id) {
    var site = $('#valueSearch').val();
    var selectedview = $('#searchType').val();
    var leveltwoval = $('#leveltwoval').val();
    var configType = $('#configType').val();
    var profileName = $('#profileName').val();
    var data = 'saveSubmitMainDetails&' + value + '&dartno=' + dartno + '&site=' + site + '&confirm=yes&selectedview=' + selectedview + '&varlist=' + varlist + '&wn_id=' + wn_id + '&leveltwoval=' + leveltwoval + '&configType=' + configType + '&profileName=' + profileName;
    console.log('dartno=' + dartno + " , value= " + value);

}

function saveVariableDetails228(data, wiz_name_id, TA_Dart_Array) {

//                console.debug(data);

//                $('#configure_wizard_details').modal('hide');
    var dart = "";
    var variablearray = {};
    var mainDataParams = [];

    $.each(data, function (i, field_val) {
//                    console.debug(field_val);
        var params = [];
        var mainarr = [];
        var varid = $.trim(getWizardNameDetails(field_val.DartNo, wiz_name_id));

        if (dart !== field_val.VarID) {
            var str = varid.split(",");
            //console.log("dart1: "+dart+" , field_val.DartNo: "+field_val.DartNo);



            for (var i = 0; i < str.length; i++) {
                var fieldvar = field_val.FieldVar;
                var joinchar = field_val.JoinCharacter;
                var value = "";
                var ta_value = "";
                var uniqIdVal = field_val.VarID + "JR" + field_val.DartNo;
                // console.log("MAIN DATA: "+field_val.VarID+" # "+str[i]+"#"+field_val.GUIType);     
                //if( $.trim(str[i])=="S00228Rules" || $.trim(str[i])=="S00228Rules" || $.trim(str[i])=="S00228Rules") {

                if ($.trim(str[i]) === $.trim(field_val.VarID)) {
//                            var match = "";
                    if (field_val.GUIType === 'checkbox') {
                        var enteredval = field_val.EnteredValues;
                        var checkboxData = enteredval.split('/');
                        var isChecked = $("#" + str[i]).val();

                        if ($("." + uniqIdVal).is(":checked")) {
                            value = $.trim(fieldvar) + $.trim(joinchar) + $("." + uniqIdVal).val();
                        } else {
                            value = $.trim(fieldvar) + $.trim(joinchar) + $("." + uniqIdVal).val();
                        }
                        ta_value = value;
                    } else if (field_val.GUIType === 'button') {
                        var enteredval = field_val.EnteredValues;
                        var checkboxData = enteredval.split('/');
                        var isChecked = $("." + uniqIdVal).val();

                        if (isChecked == "1" || isChecked == "4") {
                            value = $.trim(fieldvar) + $.trim(joinchar) + "4";
                        } else {
                            value = $.trim(fieldvar) + $.trim(joinchar) + "";
                        }
                        ta_value = value;
                    } else if (field_val.GUIType == "textarea") {
                        //console.log("gnghere+1: not gng inside");
//                        var textarea_data = $("#" + field_val.VarID + " div[id]").map(function() {
//                            var value = $("#" + this.id).attr('value');
//                            if ($.trim(value) !== "") {
//                                return value;
//                            }
//                        }).get().join("\n");
//                        value = textarea_data;
//
//                        var textarea_data_new = $("#" + field_val.VarID + " div[id]").map(function() {
//                            var value = $("#" + this.id).attr('value');
//                            if ($.trim(value) !== "") {
//                                return value;
//                            }
//                        }).get().join("##");
//                        ta_value = textarea_data_new;
//                        var jsonJoinCharacter = [];
//                        var jsonFieldVar = [];
//                        $.ajax({
//                            url: 'wizFunc.php',
//                            data: 'act=Get_JoinCharsFn&name=' + field_val.VarID + '&dart=' + field_val.DartNo,
//                            type: 'GET',
//                            dataType: 'JSON',
//                            async: false,
//                            success: function (jsondata) {
//                                $.each(jsondata, function (index, element) {
//                                    jsonJoinCharacter.push(element.JoinCharacter);
//                                    jsonFieldVar.push(element.FieldVar);
//                                });
//                            }
//                        });

                        var textarea_data = $('table.' + field_val.VarID + '_' + field_val.DartNo + ' > tbody > tr:not(:empty) ').map(function () {
                            var i = 0;
                            var entireData = $(this).find("td:not(:last-child)").map(function (i) {
                                if ($(this).html() != "") {
//                                    var char1 = "";
//                                    var char2 = "";
//                                    if (jsonFieldVar[i] == "" || jsonFieldVar[i] == null || jsonFieldVar[i] == 'undefined') {
//                                        char1 = "";
//                                    } else {
//                                        char1 = jsonFieldVar[i];
//                                    }
//                                    if (jsonJoinCharacter[i] == "" || jsonJoinCharacter[i] == null || jsonJoinCharacter[i] == 'undefined') {
//                                        char2 = "";
//                                    } else {
//                                        char2 = jsonJoinCharacter[i];
//                                    }
                                    var data = "";
                                    if(field_val.VarID == "constS00296ImpersonatePassword_confirmation" || field_val.VarID == "constS00296ImpersonatePassword" || 
                                            field_val.VarID == "S01004UserPswd_confirmation" || field_val.VarID == "S01004UserPswd" || field_val.VarID == "S01004UserChPswd" 
                                            || field_val.VarID == "S01004UserChPswd_confirmation" || field_val.VarID == "S01005Password" || varid == 's421password_confirmation' || varid == 's421password'){
                                        data = $("#"+field_val.VarID+"_pass").val();
                                    } else {
                                        data = $(this).html();
                                    }
                                    return data;
                                    i++;
                                }
                            }).get().join(",");
                            return entireData;
                        }).get().join("\n");
                        ta_value = textarea_data;

                    } else if (field_val.GUIType == "radio") {
                        var radVal = $('input[name=' + field_val.VarID + ']:checked').val();
                        value = $.trim(fieldvar) + $.trim(joinchar) + radVal;
                        ta_value = value;
                    } else if (field_val.GUIType == "select") {
                        var selVal;
                        if (field_val.DartNo == "296" && field_val.VarID == "S00296UsbDrive") {
                            selVal = $("select#S00296UsbDrive").val();
                        } else {
                            selVal = $("select#" + field_val.VarID).val();
                        }
                        ta_value = $.trim(fieldvar) + $.trim(joinchar) + selVal;

                    } else {
                        var strval = $('.' + uniqIdVal).val();
                        if (strval == "" || strval == null || strval == "null") {
                            value = $.trim(fieldvar) + $.trim(joinchar) + "";
                        } else if (strval == "on") {
                            var enteredval = field_val.EnteredValues;
                            var checkboxData = enteredval.split('/');
                            var isChecked = document.getElementById(str[i]).checked;
                            if (isChecked == "1") {
                                value = $.trim(fieldvar) + $.trim(joinchar) + "1";
                            } else {
                                value = $.trim(fieldvar) + $.trim(joinchar) + "0";
                            }
                        } else {
                            value = $.trim(fieldvar) + $.trim(joinchar) + strval;
                        }
                        ta_value = value;
                    }
                    params.push(uniqIdVal + "=" + escape(ta_value));
//                    console.log(str[i]+" - "+ta_value);
                    variablearray[uniqIdVal] = field_val.DartNo + "india" + escape(ta_value) + "india" + field_val.GUIType;

                }
                // }
                //}
//                return false;
            }//added while solving 228 changes
//            if (TA_Dart_Array.indexOf(field_val.DartNo) != -1) {
            //console.log("RESULT: "+variablearray);
            mainDataParams.push(params.join("&"));
            //console.log("RESULT: dart no : "+field_val.DartNo+" MAIN PARAMS: "+mainDataParams.join("&")+" , varid: "+varid+" , wiz_name_id: "+wiz_name_id);
            //limitCalls(params.join("&"), field_val.DartNo, varid, wiz_name_id);
            submitEachDartValues(mainDataParams.join("&"), field_val.DartNo, varid, wiz_name_id);
//            }

            //console.log("RESULT: dart no : "+field_val.DartNo+" MAIN PARAMS: "+mainDataParams.join("&"));
            // submitEachDartValues(mainDataParams.join("&"), field_val.DartNo, varid, wiz_name_id);

//                        variablearray.push(params.join("&"), field_val.DartNo, varid, wiz_name_id);
//                        var tempvar = varid.split(',');
//                        $.each(tempvar, function(key,val){
//                            variablearray[val] = field_val.DartNo +","+ escape(value)+","+field_val.GUIType;
//                            
//                        });
            //}////commented while solving 228 changes
        }


        //submitEachDartValues(mainDataParams.join("&"), field_val.DartNo, varid, wiz_name_id);
        dart = field_val.VarID;

    });

    //console.log("RESULT: "+mainDataParams.join("&"));
    //console.log("RESULT: "+variablearray);

    VARSET_API_CALL(variablearray, wiz_name_id);

}


function VARSET_API_CALL(variablearray, wiz_name_id) {
    var encode_variablearray = encodeURI(JSON.stringify(variablearray));

    //console.log("RESULT: "+JSON.stringify(preventereddata));

    var site = $('#valueSearch').val();
    var selectedview = $('#searchType').val();
    var leveltwoval = $('#leveltwoval').val();
    $.ajax({
        url: "wizFunc.php",
        type: "POST",
        dataType: "text",
        data: "act=VARSET_API_CALL&site=" + site + "&leveltwoval=" + leveltwoval + "&selectedview=" + selectedview + "&variables=" + encode_variablearray + "&preventereddata=" + encodeURI(JSON.stringify(preventereddata)) + "&wizId=" + wiz_name_id,
        async: false,
        success: function (resp) {
//            console.log("response--->" + $.trim(resp));
            var result = "";
            if ($.trim(resp) == "Success") {
                var getTab = $("#getTabId").val();
                var tabVal = $("#getTabVal").val();
                var profileName = $("#getprofileName").val();
                closeConfigFunc(getTab, tabVal, profileName);
                result = "Successfully updated the values";
                showModal("3", result);
            } else if ($.trim(resp) == "NoChange") {
                result = "No changes found to update";
                showModal("3", result);
            } else {
                result = "Something went wrong. Please try again later";
                showModal("2", result);
            }
            document.getElementById("submit").value = "Submit";
            $("#submit").text("Submit");
        },
        error: function (req, status, err) {
            var result = "";
            result = "Something went wrong. Status=" + status + ". " + "Error=" + err + ".";
            showModal("2", result);
        }
    });
}

function postMainServerData(sendData, site) {
    //var d=(sendData)+'&act=prmt&mgroupid=959&mcatid=3&snum=60&group_name=Nanoheal_2&cid=0&censusid=0&prev_cid=7&scop=0&prev_scop=3&prev_hid=0&level=0&vers=2.004.032.2622.08&ScripNo=60&button=Next >';
    //alert(sendData);
    var form = $.trim(sendData);

    if ($.trim(sendData) === "noresult") {
        var result = "";
        result = '<span>Sorry no machines found in</span> ' + site + '<span>. Changes failed to update.</span>';
        showModal("2", result);
    } else if (sendData !== "") {
        $.ajax({
            type: 'POST',
            url: '../../main/config/scrpconf.php',
            data: form,
            async: false,
            headers: {
                "Authorization": "Basic " + btoa(username + ":" + password)
            },
            success: function (resp) {

                var result = "";
//                if (resp.indexOf("Please click on \"Continue\"") > -1) {
////                    confirmRequest(formData,policy_id,site);
//                } else if (resp.indexOf("The following variables were changed") > -1) {
//                    result = "Successfully updated the values";
//                    showModal("3", result);
//                } else if (resp.indexOf("An error has occurred") > -1) {
//                    result = "Error occured. Changes failed to update.";
//                    showModal("2", result);
//                } else if (resp.indexOf("Nothing has changed.") > -1) {
//                    result = "Nothing has changed.( values are same )";
//                    showModal("2", result);
//                } else {
                result = "<span>Successfully updated the values</span>";
                showModal("3", result);
//                }
            },
            error: function (req, status, err) {
                var result = "";
                result = "Something went wrong. Status=" + status + ". " + "Error=" + err + ".";
                showModal("2", result);
            }
        });
    }

}

function showModal(perform, text) {

    $("#ajaxloaderr").hide();

    if (perform == 2 || perform == "2") {

        $("#successMsg").hide();
        $("#errorMsg").show();
        $("#statMessage").html("");
        $("#notifyPopup").modal("show");
        $("#statMessage").html(text);

    } else if (perform == 3 || perform == "3") {

        $("#successMsg").show();
        $("#errorMsg").hide();
        $("#statMessage").html("");
        $("#notifyPopup").modal("show");
        $("#statMessage").html(text);

    }
}

function ServicesConfigurationAudit() {
    window.location.href = "../services/configAudit.php";
}
function NanohealConfigurationAudit() {
    window.location.href = "../services/nhConfigAudit.php";
}

//js

function GET_ServicesAuditListData() {
    $.ajax({
        url: "../lib/l-ajax.php",
        data: "function=AJAX_RESOL_ServicesListData",
        dataType: "json",
        success: function (list) {
            $(".se-pre-con").hide();
            $("#servicesList").html(list);
            var ConfigType = $("#servicesList li").first().text();
            var thisVal = '';
            GET_ServicesGridDetailData(ConfigType, thisVal);
        }
    });
}

function GET_ServicesGridDetailData(ConfigType, obj) {
    if (obj == "") {
        $("#servicesList li a").first().addClass("active");
    } else {
        $(".thislist").removeClass("active");
        $(obj).addClass("active");
    }

    $("#AviraConfigType").val(ConfigType);
    var site = $('#valueSearch').val();
    $.ajax({
        type: "GET",
        url: "../lib/l-ajax.php",
        data: "function=AJAX_RESOL_ServicesGridData&ProfileName=" + ConfigType + '&site=' + site,
        dataType: 'json',
        success: function (gridData) {
//            $("#notifyGridDiv").show();
            $("#se-pre-con-loader").hide();
            $('#servicesDtl').DataTable().destroy();
            servicesDtl = $('#servicesDtl').DataTable({
                scrollY: jQuery('#servicesDtl').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                responsive: true,
                stateSave: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                order: [[0, "desc"]],
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });

                    $('.equalHeight').matchHeight();
//                    $("#se-pre-con-loader").hide();
                }

            });
//            $('.tableloader').hide();

        },
        error: function (msg) {

        }
    });
    $("#servicesAudit_searchbox").keyup(function () {
        servicesDtl.search(this.value).draw();
    });
}

function AuditDetailStatusFn(stat, tid, eventList) {

    $("#rightNavtiles").css({'display': 'none'});

    if (tid != '') {

        $.ajax({
            url: '../softdist/SWD_Function.php?function=AuditDetailStatusFn&stat=' + stat + '&eid=' + tid + '&eventList=' + eventList,
            type: 'post',
            dataType: 'json',
            success: function (data) {

                $("#eventDetails").modal("show");
                $('input[type=text]').prev().parent().removeClass('is-empty');
                $("#executed").val(data.username);
                $("#servertime").val(data.servertime);
                $("#agenttime").val(data.servertime);
                $("#nodetime").val(data.nodetrigger);
                $("#clienttime").val(data.clienttime);

                $("#eventuser").val(data.eventuser);
                $('#eventserver').val(data.eventserver);
                $('#eventcustomer').val(data.eventcustomer);
                $('#eventuuid').val(data.eventuuid);
                $('#eventversion').val(data.eventversion);
                $('#eventdescription').val(data.eventdescription);
                $('#eventsize').val(data.eventsize);
                $('#eventid').val(data.eventid);
                $('#eventstring2').val(data.eventstring2);

                $('#eventclient').val(data.eventclient);
                $('#eventscrip').val(data.eventscrip);
                $('#eventmachine').val(data.eventmachine);
                $('#eventusername').val(data.eventusername);
                $('#eventpriority').val(data.eventpriority);
                $('#eventtype').val(data.eventtype);
                $('#eventversion').val(data.eventversion);
                $('#eventid2').val(data.eventid2);
                $('#eventstring1').val(data.eventstring1);

                $('#eventpath').val(data.eventpath);
                $('#eventtext2').val(data.eventtext2);
                $('#eventtext3').val(data.eventtext3);
                $('#eventtext4').val(data.eventtext4);

            }
        });
    }
}

function exportData() {
    var type = $("#AviraConfigType").val();
    var site = $('#valueSearch').val();
    window.location.href = '../lib/l-ajax.php?function=AJAX_RESOL_GetServicesExportDetails&ProfileName=' + type + '&site=' + site;
}

function exportNHData() {
    var type = $("#AviraConfigType").val();
    var site = $('#valueSearch').val();
    window.location.href = '../lib/l-ajax.php?function=AJAX_RESOL_GetNHConfigExportDetails&ProfileName=' + type + '&site=' + site;
}

function GET_ServicesNHAuditListData() {
    $.ajax({
        url: "../lib/l-ajax.php",
        data: "function=AJAX_RESOL_NHConfigListData",
        dataType: "json",
        success: function (list) {
            $(".se-pre-con").hide();
            $("#servicesList").html(list);
            var ConfigType = $("#servicesList li").first().text();
            var thisVal = '';
            GET_NHConfigGridDetailData(ConfigType, thisVal);
        }
    });
}

function GET_NHConfigGridDetailData(ConfigType, obj) {
    if (obj == "") {
        $("#servicesList li a").first().addClass("active");
    } else {
        $(".thislist").removeClass("active");
        $(obj).addClass("active");
    }

    $("#AviraConfigType").val(ConfigType);
    var site = $('#valueSearch').val();
    $.ajax({
        type: "GET",
        url: "../lib/l-ajax.php",
        data: "function=AJAX_RESOL_NHConfigGridData&ProfileName=" + ConfigType + '&site=' + site,
        dataType: 'json',
        success: function (gridData) {
//            $("#notifyGridDiv").show();
            $("#se-pre-con-loader").hide();
            $('#servicesDtl').DataTable().destroy();
            servicesDtl = $('#servicesDtl').DataTable({
                scrollY: jQuery('#servicesDtl').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                responsive: true,
                stateSave: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                order: [[0, "desc"]],
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });

                    $('.equalHeight').matchHeight();
//                    $("#se-pre-con-loader").hide();
                }

            });
//            $('.tableloader').hide();

        },
        error: function (msg) {

        }
    });
    $("#servicesAudit_searchbox").keyup(function () {
        servicesDtl.search(this.value).draw();
    });
    console.log(wiznameId);
    if(wiznameId !== '0') {
        showWizard(wiznameId);
}
}

//function to write config file
function aviraWmiFile() {
    var level = $('#searchType').val();
    var siteName = '';

    if (level == 'ServiceTag') {
        siteName = $("#rparentName").val();
        if ($.isNumeric(siteName)) {
            siteName = '';
        }
    } else if (level == 'Sites' && ($('#searchValue').val() != 'All')) {
        siteName = $('#searchValue').val();
    }

    $.ajax({
        type: 'post',
        dataType: 'json',
        url: 'wizFunc.php?' + 'act=aviraWMIfileWrite&siteName=' + siteName,
    });

}


function AviraScheduleADD() {
    $("#allConfigureContent,#ajaxloaderrSched").hide();
    $("#add_avira_scheduler,.service-desc").show();
    $(".form-control").val("");
    $("#addeditaction").val("add");
    $("#selectedId").val("");
    $("#jobname").val("").prop("disabled", false);
    $("#jobdesc").val("").prop("disabled", false);
    $("#actiontype").val("").change().prop("disabled", false);
}

function frequencyChanger() {
    var frequency = $("#frequency").val();
    switch (frequency) {
        case "0":
            $(".repeatjob,.dateonly,.interval,.weekly,.daily").hide();
            break;
        case "1":
            $(".repeatjob,.daily").fadeIn();
            $(".interval,.weekly,.dateonly").hide();
            break;
        case "2":
            $(".repeatjob,.weekly,.daily").fadeIn();
            $(".interval,.dateonly").hide();
            break;
        case "3":
            $(".repeatjob,.interval").fadeIn();
            $(".weekly,.daily,.dateonly").hide();
            break;
        case "4":
            $(".repeatjob,.dateonly").fadeIn();
            $(".interval,.weekly,.daily").hide();
            break;
        default:
            break;
    }
}

function nextphase() {
    var phasecounter = $("#phasecounter").val();
    switch (phasecounter) {
        case "0":
            var site = $('#valueSearch').val();
            var jobname = $("#jobname").val();
            var jobdesc = $("#jobdesc").val();
            var act = $("#addeditaction").val();
            if (jobname == "" || jobdesc == "") {
                $("#phase1error").fadeIn();
                $("#phase1error").html("<span>Please fill all the required fields.</span>");
                setTimeout(function () {
                    $("#phase1error").fadeOut();
                    $("#phase1error").html("");
                }, 3000);
            } else {
                if (act == "add") {
                    $.ajax({
                        url: "../services/wizFunc.php",
                        data: "act=checkJobExist&site=" + site + "&jobname=" + jobname,
                        type: "POST",
                        success: function (resp) {
                            if (resp == "0" || resp == 0) {
                                $("#backSc,.phase2").fadeIn();
                                $(".phase1,#submitSc,.frequencybinder").hide();
                                $("#phasecounter").val("1");
                            } else {
                                $("#phase1error").fadeIn();
                                $("#phase1error").html('<span>Scheduler with Job Name </span>"' + jobname + '" <span>already exists.</span>');
                                setTimeout(function () {
                                    $("#phase1error").fadeOut();
                                    $("#phase1error").html("");
                                }, 3000);
                            }
                        }
                    });
                } else {
                    $("#backSc,.phase2").fadeIn();
                    $(".phase1,#submitSc,.frequencybinder").hide();
                    $("#phasecounter").val("1");
                }
            }
            break;
        case "1":
            var actiontype = $("#actiontype").val();
            if (actiontype) {
                $(".phase2,#submitSc").hide();
                if (actiontype == "0") {
                    $(".phase3,#backSc,#nextSc").fadeIn();
                    $("#phasecounter").val("2");
                    $(".frequencybinder").hide();
                } else {
                    $(".phase4,#backSc,#nextSc").fadeIn();
                    $("#phasecounter").val("3");
                    var frequency = $("#frequency").val();
                    switch (frequency) {
                        case "0":
                            $(".repeatjob,.dateonly,.interval,.weekly,.daily").hide();
                            break;
                        case "1":
                            $(".repeatjob,.daily").fadeIn();
                            $(".interval,.weekly,.dateonly").hide();
                            break;
                        case "2":
                            $(".repeatjob,.weekly,.daily").fadeIn();
                            $(".interval,.dateonly").hide();
                            break;
                        case "3":
                            $(".repeatjob,.interval").fadeIn();
                            $(".weekly,.daily,.dateonly").hide();
                            break;
                        case "4":
                            $(".repeatjob,.dateonly").fadeIn();
                            $(".interval,.weekly,.daily").hide();
                            break;
                        default:
                            break;
                    }
                }
            } else {
                $("#phase2error").fadeIn();
                setTimeout(function () {
                    $("#phase2error").fadeOut();
                }, 3000);
            }
            break;
        case "2":
            var profiles = $("#profiles").val();
            if (profiles) {
                $(".phase3,#submitSc").hide();
                $(".phase4,#backSc,#nextSc").fadeIn();
                $("#phasecounter").val("3");
                var frequency = $("#frequency").val();
                switch (frequency) {
                    case "0":
                        $(".repeatjob,.dateonly,.interval,.weekly,.daily").hide();
                        break;
                    case "1":
                        $(".repeatjob,.daily").fadeIn();
                        $(".interval,.weekly,.dateonly").hide();
                        break;
                    case "2":
                        $(".repeatjob,.weekly,.daily").fadeIn();
                        $(".interval,.dateonly").hide();
                        break;
                    case "3":
                        $(".repeatjob,.interval").fadeIn();
                        $(".weekly,.daily,.dateonly").hide();
                        break;
                    case "4":
                        $(".repeatjob,.dateonly").fadeIn();
                        $(".interval,.weekly,.daily").hide();
                        break;
                    default:
                        break;
                }
            } else {
                $("#phase3error").fadeIn();
                setTimeout(function () {
                    $("#phase3error").fadeOut();
                }, 3000);
            }
            break;
        case "3":
            var frequency = $("#frequency").val();
            if (frequency) {
                switch (frequency) {
                    case "0":
                        $(".phase3,.phase4,#nextSc,.frequencybinder").hide();
                        $(".phase5,#backSc,#submitSc").fadeIn();
                        $("#phasecounter").val("4");
                        break;
                    case "1":
                        var datetime = $("#datetime").val();
                        if (datetime == "") {
                            $("#phase4error").fadeIn();
                            $("#phase4error").html("<span>Please select time.</span>");
                            setTimeout(function () {
                                $("#phase4error").fadeOut();
                            }, 3000);
                        } else {
                            $(".phase3,.phase4,#nextSc,.frequencybinder").hide();
                            $(".phase5,#backSc,#submitSc").fadeIn();
                            $("#phasecounter").val("4");
                        }
                        break;
                    case "2":
                        var datetime = $("#datetime").val();
                        var weekdays = $("#weekdays").val();
                        if ((datetime == "" || datetime == "null" || datetime == null) || (weekdays == "" || weekdays == null || weekdays == "null")) {
                            $("#phase4error").fadeIn();
                            $("#phase4error").html("<span>Please select both weekday and time.</span>");
                            setTimeout(function () {
                                $("#phase4error").fadeOut();
                            }, 3000);
                        } else {
                            $(".phase3,.phase4,#nextSc,.frequencybinder").hide();
                            $(".phase5,#backSc,#submitSc").fadeIn();
                            $("#phasecounter").val("4");
                        }
                        break;
                    case "3":
                        var intdays = $("#intdays").val();
                        var inthours = $("#inthours").val();
                        var intmins = $("#intmins").val();
                        if (intmins <= 14 && (inthours == "" || inthours == "0") && (intdays == "" || intdays == "0")) {
                            $("#phase4error").fadeIn();
                            $("#phase4error").html("<span>Minimum 15 minutes need to be provided.</span>");
                            setTimeout(function () {
                                $("#phase4error").fadeOut();
                            }, 3000);
                        } else {
                            $(".phase3,.phase4,#nextSc,.frequencybinder").hide();
                            $(".phase5,#backSc,#submitSc").fadeIn();
                            $("#phasecounter").val("4");
                        }
                        break;
                    case "4":
                        var dailytime = $("#dailytime").val();
                        if (dailytime == "") {
                            $("#phase4error").fadeIn();
                            $("#phase4error").html("<span>Please select time.</span>");
                            setTimeout(function () {
                                $("#phase4error").fadeOut();
                            }, 3000);
                        } else {
                            $(".phase3,.phase4,#nextSc,.frequencybinder").hide();
                            $(".phase5,#backSc,#submitSc").fadeIn();
                            $("#phasecounter").val("4");
                        }
                        break;
                    default:
                        break;
                }

            } else {
                $("#phase4error").fadeIn();
                setTimeout(function () {
                    $("#phase4error").fadeOut();
                }, 3000);
            }
            break;
        default:
            break;
    }
}

function prevphase() {
    var phasecounter = $("#phasecounter").val();
    switch (phasecounter) {
        case "4":
            $(".phase5,#submitSc").hide();
            $(".phase4,#backSc,#nextSc").fadeIn();
            $("#phasecounter").val("3");
            var frequency = $("#frequency").val();
            switch (frequency) {
                case "0":
                    $(".repeatjob,.dateonly,.interval,.weekly,.daily").hide();
                    break;
                case "1":
                    $(".repeatjob,.daily").fadeIn();
                    $(".interval,.weekly,.dateonly").hide();
                    break;
                case "2":
                    $(".repeatjob,.weekly,.daily").fadeIn();
                    $(".interval,.dateonly").hide();
                    break;
                case "3":
                    $(".repeatjob,.interval").fadeIn();
                    $(".weekly,.daily,.dateonly").hide();
                    break;
                case "4":
                    $(".repeatjob,.dateonly").fadeIn();
                    $(".interval,.weekly,.daily").hide();
                    break;
                default:
                    break;
            }
            break;
        case "3":
            var actiontype = $("#actiontype").val();
            $(".phase4,.frequencybinder").hide();
            if (actiontype == "0") {
                $(".phase3,#backSc,#nextSc").fadeIn();
                $("#phasecounter").val("2");
            } else {
                $(".phase2,#backSc,#nextSc").fadeIn();
                $("#phasecounter").val("1");
            }
            break;
        case "2":
            $(".phase3,.frequencybinder").hide();
            $(".phase2,#backSc,#nextSc").fadeIn();
            $("#phasecounter").val("1");
            break;
        case "1":
            $(".phase2,#backSc,.frequencybinder").hide();
            $(".phase1,#nextSc").fadeIn();
            $("#phasecounter").val("0");
            break;
        default:
            break;
    }
}


function schedCheck(ID) {
    if ($("#" + ID).is(":checked")) {
        $("#" + ID).val("1");
    } else {
        $("#" + ID).val("0");
    }
}

function SetSchedulerDetails() {
    $(".loader").show();
    var site = $('#valueSearch').val();
    var act = $("#addeditaction").val();
    var selId = $("#selectedId").val();
    var jobname = $("#jobname").val();
    var jobdesc = $("#jobdesc").val();
    var actiontype = $("#actiontype").val();
    var profiles = $("#profiles").val();
    var frequency = $("#frequency").val();
    var weekdays = $("#weekdays").val();
    var dailytime = $("#dailytime").val();
    var datetime = $("#datetime").val();
    var intdays = $("#intdays").val();
    var inthours = $("#inthours").val();
    var intmins = $("#intmins").val();
    var repeatjob = $("#repeatjob").val();
    var displaymode = $("#displaymode").val();
    var shutdown = $("#shutdown").val();
    var enabled = "1";
    var status = "1";

    var myJSONObj = {};

    myJSONObj["Name"] = jobname;
    myJSONObj["Description"] = jobdesc;
    myJSONObj["Action"] = actiontype;
    myJSONObj["Profiles"] = profiles;
    myJSONObj["Frequency"] = frequency;
    myJSONObj["Weekdays"] = weekdays;
    myJSONObj["Dailytime"] = dailytime;
    myJSONObj["Datetime"] = datetime;
    myJSONObj["Days"] = intdays;
    myJSONObj["Hours"] = inthours;
    myJSONObj["Minutes"] = intmins;
    myJSONObj["RepeatJob"] = repeatjob;
    myJSONObj["DisplayMode"] = displaymode;
    myJSONObj["ShutDown"] = shutdown;
    myJSONObj["Enabled"] = enabled;
    myJSONObj["Status"] = status;

    $.ajax({
        url: "wizFunc.php?act=SetSchedulerInfo",
        data: "type=" + act + "&selectId=" + selId + "&site=" + site + "&fielddata=" + JSON.stringify(myJSONObj),
        type: 'POST',
        dataType: 'text',
        async: false,
        success: function (responce) {
            $(".loader").hide();
            if (responce == "failed") {
                var msg = "<span>Error occurred. Please try again later.</span>";
                showModal("2", msg);
            } else {
                $("#submitSc").prop("disabled", true);
                var msg = "<span>Successfully updated the values.</span>";
                showModal("3", msg);
                var VariableString = btoa(responce);
                CreateJobAndExecute("275", VariableString, "WMI", "Wmi Command", jobname, "");
                setTimeout(function () {
                    location.reload();
                }, 2000);
            }
        }
    });
}

function Get_SchedulerData() {
    var site = $('#valueSearch').val();
    setTimeout(function () {
        $('#schedulerTable').DataTable().columns.adjust().draw();
    }, 1500);
    $.ajax({
        type: "GET",
        url: "../lib/l-ajax.php",
        data: "function=AJAX_RESOL_AviraSchedulerData&site=" + site,
        dataType: 'json',
        success: function (gridData) {
            $('#schedulerTable').DataTable().destroy();
            schedulerTable = $('#schedulerTable').DataTable({
                scrollY: jQuery('#schedulerTable').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                responsive: true,
                stateSave: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                order: [[0, "desc"]],
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
//                    $('#schedulerTable').DataTable().columns.adjust().draw();
                    $('.equalHeight').matchHeight();
                }

            });

        },
        error: function (msg) {

        }
    });
    $("#schedulerTable_filter").keyup(function () {
        schedulerTable.search(this.value).draw();
    });
}

function editSchedulerJob(editId) {
    $("#addeditaction").val("edit");
    $("#selectedId").val(editId);
    $.ajax({
        type: "POST",
        url: "../services/wizFunc.php",
        data: "act=editSchedulerJob&editid=" + editId,
        dataType: "json",
        success: function (resp) {
            $("#allConfigureContent,#ajaxloaderrSched").hide();
            $("#add_avira_scheduler,.service-desc").show();

            $("#jobname").val(resp.Name).prop("disabled", true);
            $("#jobdesc").val(resp.Description).prop("disabled", true);
            $("#actiontype").val(resp.Action).change().prop("disabled", true);
            $("#profiles").val(resp.Profiles).change();
            $("#frequency").val(resp.Frequency).change();
            $("#displaymode").val(resp.DisplayMode).change();


            switch (resp.RepeatJob) {
                case "0":
                    $("#repeatjob").val(resp.RepeatJob).prop("checked", false);
                    break;
                case "1":
                    $("#repeatjob").val(resp.RepeatJob).prop("checked", true);
                    break;
            }

            switch (resp.ShutDown) {
                case "0":
                    $("#shutdown").val(resp.ShutDown).prop("checked", false);
                    break;
                case "1":
                    $("#shutdown").val(resp.ShutDown).prop("checked", true);
                    break;
                default:
                    break;
            }

            switch (resp.Frequency) {
                case "0":
                    break;
                case "1":
                    $("#datetime").val(resp.Hours + ":" + resp.Minutes);
                    break;
                case "2":
                    $("#weekdays").val(resp.Day).change();
                    $("#datetime").val(resp.Hours + ":" + resp.Minutes);
                    break;
                case "3":
                    $('#intdays option[value=' + resp.Date + ']').attr("selected", true);
                    $('#inthours option[value=' + resp.Hours + ']').attr("selected", true);
                    $('#intmins option[value=' + resp.Minutes + ']').attr("selected", true);
                    $(".selectpicker").selectpicker('refresh');
                    break;
                case "4":
                    $("#dailytime").val(resp.Month + "/" + resp.Date + "/" + resp.Year + "/" + resp.Hours + ":" + resp.Minutes);
                    break;
                default:
                    break;
            }
            $(".frequencybinder").hide();
        }
    });
}

function deleteSchedulerJob(deleteId) {
    $("#deleteSchedulerJob").modal("show");
    $("#deleteid").val(deleteId);
}

function SubmitDeleteSchedulerJob() {
    var deleteId = $("#deleteid").val();
    $.ajax({
        type: "POST",
        url: "../services/wizFunc.php",
        data: "act=deleteSchedulerJob&deleteid=" + deleteId,
        dataType: "text",
        success: function (resp) {
            $("#deleteSchedulerJob").modal("hide");
            if (resp) {
                var splitvar = resp.split("@@@@");
                var JobName = splitvar[0];
                var VariableString = btoa(splitvar[1]);
                CreateJobAndExecute("275", VariableString, "WMI", "Wmi Command", JobName, "");
                var msg = "<span>Selected schedule is successfully deleted.</span>";
                showModal("3", msg);
                Get_SchedulerData();
            } else {
                var msg = "<span>Error occurred while deleting schedule.</span>";
                showModal("2", msg);
            }
            $("#deleteid").val("");
        }
    });
}

$('.restrict-spcl-chars').keypress(function (e) {
    var regex = new RegExp("^[a-zA-Z0-9\\-\\s]+$");
    var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
    if (regex.test(str)) {
        return true;
    }
    e.preventDefault();
    return false;
});

function ValidateIPaddress(ipaddress)
{
    if (/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ipaddress))
    {
        return true;
    } else {

        return false;
    }
}

function getVariableListFn() {
    var dartno = $("#add_dartNo").val();

    $.ajax({
        url: 'wizFunc.php?act=getVariableList',
        type: 'POST',
        data: 'dartNo=' + dartno,
        async: true,
        success: function (data) {
            $("#add_variable option").remove();
            $("#add_variable").append('<option value="" disabled selected>Select Variable</option> ' + data);
            $(".selectpicker").selectpicker("refresh");
        },
        cache: false,
        processData: false
    });

}

function variableTypeFn(element) {
    var id = $(element).children(":selected").attr("id");

    if (id == '2') {
        $('#var_string').prop('checked', true);
        $('.var_type').val('1');
    } else if (id == '3') {
        $('#var_bool').prop('checked', true);
        $('.var_type').val('2');
    } else if (id == '0') {
        $("#var_int").prop('checked', true);
        $('.var_type').val('3');
    } else if (id == '7') {
        $("#var_execute").prop('checked', true);
        $('.var_type').val('4');
    }
}

function saveNewConfigData() {
    var formDataString = $("#ADD_NEW_CONFIG").serialize() + '&var_type=' + $(".var_type").val();
    var selectedvaliable = $.trim($('#add_variable').val());

    var isValid = true;
    $('.error_add_dartNo').hide();
    $('.error_add_desc').hide();
    $('.error_add_variable').hide();

    if (document.getElementById("add_existing").checked == false) {
        if ($.trim($('#add_dartNo').val()) == '') {
            $('.error_add_dartNo').show();
            isValid = false;
        } else if ($.trim($('#add_desc').val()) == '') {
            $('.error_add_desc').show();
            isValid = false;
        } else if ($.trim($('#add_variable').val()) == '') {
            $('.error_add_variable').show();
            isValid = false;
        } else if ($.trim($('#add_desc').val()) != '') {
            //alert($.trim($('#add_desc').val()));

            var res = "";

            $.ajax({
                url: 'wizFunc.php?act=checkDescriptionUniq',
                type: 'POST',
                data: 'desc=' + $.trim($('#add_desc').val()),
                async: false,
                success: function (data) {
                    res = data;
                },
                cache: false,
                processData: false
            });

            if (res == 'true') {
                $('.error_add_desc').show();
                $('.error_add_desc').text('Already Configuration Name is available . Please provide a different name');
                isValid = false;
            } else {
                var pattern = /^[A-Za-z0-9 \_\+\-\(\)\:,\\\/\'\"]+$/;

                if (pattern.test($.trim($('#add_desc').val()))) {

                } else {
                    $('.error_add_desc').show();
                    $('.error_add_desc').text('Please dont enter any special characters');
                    isValid = false;
                }
            }
        }

    }

    if (document.getElementById("add_existing").checked == true) {
        if (selectedvaliable.length > 0) {

            var res = "";
            var sel_cid = $("#sel_cid").val();
            //alert(sel_cid);

            $.ajax({
                url: 'wizFunc.php?act=checkVariableExists',
                type: 'POST',
                data: 'cid=' + sel_cid + '&variable=' + selectedvaliable,
                async: false,
                success: function (data) {
                    res = data;
                },
                cache: false,
                processData: false
            });

            if (res == 'true') {
                $('.error_add_variable').show();
                $('.error_add_variable').text('Already variable is added / used . Please select a different variable');
                isValid = false;
            }

        } else {
            $('.error_add_variable').show();
            $('.error_add_variable').text('Please select the variable to be added');
            isValid = false;
        }
    }

    if (isValid) {
        $.ajax({
            url: 'wizFunc.php?act=addnewconfig',
            type: 'POST',
            data: formDataString,
            async: true,
            success: function (data) {
                //getCategorizeListOfDarts();
//                                             var dartno=$.trim($('#add_dartNo').val());
//                                             var des=$.trim($('#add_desc').val());
//                                             var cid=data.split("\r\n");                                             
                //updateDartDetails(dartno,cid[0],des);
                jQuery_1_7.nyroModalRemove();
                $("#packagesGrid").trigger("reloadGrid");
            },
            cache: false,
            processData: false
        });
    }
}

$('.modal').on('shown.bs.modal', function () {
    $(".date_field").datetimepicker({
        format: "dd/mm/yyyy",
        autoclose: true,
        todayBtn: false,
        pickerPosition: "bottom-right"
    });
    $(".time_field").datetimepicker({
        format: "hh:ii",
        autoclose: true,
        todayBtn: false,
        pickerPosition: "bottom-right"
    });
});

