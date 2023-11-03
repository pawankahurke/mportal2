var OSName;
var OSDB;
var OSSUB = 'NA';
var mlist;
var glMachineName = '';
var glmenuitem;
var arrAllcheckVal = '';
var srchpgid = '';
var Liclicked = 'false';

var loadRDloaded = false;
var frmPg = '<?php echo $frmPg; ?>';
if (frmPg === '1') {
    $("#scheduleDiv").hide();
    $("#auditDiv").show();
    $('#ToolBox').hide();
    auditGrid();
} else {
    // if (!loadRDloaded)
        loadRD();
}

$(document).ready(function () {
    var OriginWindow = $('#fromWindow').val();

    if(OriginWindow == 'Notify'){
        tileHome('1', 'notify');
    }
    // $.ajax({
    //     type: "POST",
    //     url: "../communication/communication_ajax.php",
    //     data: { 'function': 'get_OriginData', 'csrfMagicToken': csrfMagicToken },
    //     success: function (data) {
    //         data = data.trim();
    //         if (data == 'Notify') {
    //             tileHome('1', 'notify');
    //         } else {
    //         }
    //     },
    //     error: function (data) {
    //         console.log("error");
    //     }
    // });
});

function backToNotif() {
    window.location.href = "../notification/notification.php" + "&csrfMagicToken=" + csrfMagicToken;
}

$("#toolboxList").on('click', 'li', function () {
    $('.hidden_mid').removeClass('midselected');
    $('.hidden_status').removeClass('midselected');
    Liclicked = 'true';
    $('#clickList li').removeClass("tileactive");
    $('#toolboxList li').removeClass("tileactive");
    $('.expand li').removeClass("tileactive");
    $(this).addClass("tileactive");
    var allListElements = $("input");
    $('.tileactive').find(allListElements).addClass('midselected');
});

$("#clickList").on('click', 'li', function () {
    $('.hidden_mid').removeClass('midselected2');
    $('.hidden_status').removeClass('midselected2');
    Liclicked = 'true';
    $('#clickList li').removeClass("tileactive");
    $('#toolboxList li').removeClass("tileactive");
    $('.expand li').removeClass("tileactive");
    $(this).addClass("tileactive");
    $('.tileactive').find('input:first').addClass('midselected2');
    $('.tileactive').find('input:eq(1)').addClass('midselected2');
});


$(function () {
    $("#ascrail2001").removeAttr('style');
    $("#ascrail2009-hr").removeAttr('style');
    $("#ascrail2009-hr div").removeAttr('style');
    $("#ascrail2008-hr").removeAttr('style');
    $("#ascrail2008-hr").removeAttr('class');
    $("#ascrail2008-hr div").removeAttr('style');
    $("#ascrail2008-hr div").removeAttr('class');
    $("#ascrail2007-hr").removeAttr('style');
    $("#ascrail2007-hr div").removeAttr('style');
});

function loadRD() {
    loadRDloaded = true;
    $("#proactive").hide();
    $("#predictive").hide();
    $("#scheduleDiv").hide();
    $("#auditDiv").hide();
    $('#ToolBox').show();
    $('#troubleShoot').show();
    $("#rdBackBtn").hide();
    $("#deleteBtn").hide();
    $("#exportBtn").hide();
    $('#header').html('');

    if (frmPg === 'entl') {
        $("#entBackBtn").show();
    }

    $('.equalHeight').hide();
    $('#absoLoader').show();

    var stype;

    $.ajax({
        url: "../lib/l-ajax.php",
        type: 'POST',
        data: {
            function: 'returnSearchType',
            csrfMagicToken: csrfMagicToken
        },
        success: function (respData) {
            stype = $.trim(respData);
            stype = '';
            if (stype === 'ServiceTag' || stype === 'Service Tag' || stype === 'Host Name') {
                $('#troubleshooting_searchbox').show();
                $.ajax({
                    url: "selectProfile.php?type=1",
                    type: 'POST',
                    success: function (data) {
                        var profres = data.trim();
                        $("#rmselectprofile").val(profres);
                        var rmselectprofile = $("#rmselectprofile").val();
                        $("#osTypeDrop").text(rmselectprofile);
                        $('.equalHeight').show();
                        $('#absoLoader').hide();
                        getOSLevelTilesMachineLvl();
                    },
                    error: function (err) {
                        console.log('Proflle Resp Error : ' + err);
                    }
                });

            } else {
                $("#mainParentTitle").html("Troubleshooting");
                if ((tsRestricted == 1 || tsRestricted == '1') && (notifyWindow == '0' || notifyWindow == 0)) {
                    $('#troubleshooting_searchbox').hide();
                } else {
                    $('#troubleshooting_searchbox').show();
                }

                var sel_pro = $('#selectProfile').val();
                switch (sel_pro) {

                    case "All":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#windowsIcon,#androidIcon,#macIcon,#iosIcon,#linuxIcon").show();
                        break;
                    case "MWAL":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#iosIcon").hide();
                        $("#windowsIcon,#androidIcon,#macIcon,#linuxIcon").show();
                        break;
                    case "MALI":
                        changeDrop('macIcon');
                        getOSLevelTiles('mac');
                        $("#windowsIcon").hide();
                        $("#iosIcon,#androidIcon,#macIcon,#linuxIcon").show();
                        break;
                    case "WALI":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#macIcon").hide();
                        $("#windowsIcon,#androidIcon,#iosIcon,#linuxIcon").show();
                        break;
                    case "MWAI":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#linuxIcon").hide();
                        $("#windowsIcon,#androidIcon,#macIcon,#iosIcon").show();
                        break;
                    case "MWLI":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#androidIcon").hide();
                        $("#windowsIcon,#linuxIcon,#macIcon,#iosIcon").show();
                        break;
                    case "WMA":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#linuxIcon,#iosIcon").hide();
                        $("#windowsIcon,#macIcon,#androidIcon").show();
                        break;
                    case "WLA":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#iosIcon,#macIcon").hide();
                        $("#windowsIcon,#linuxIcon,#androidIcon").show();
                        break;
                    case "WIA":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#linuxIcon,#macIcon").hide();
                        $("#windowsIcon,#iosIcon,#androidIcon").show();
                        break;
                    case "WLM":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#iosIcon,#androidIcon").hide();
                        $("#windowsIcon,#linuxIcon,#macIcon").show();
                        break;
                    case "WLI":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#macIcon,#androidIcon").hide();
                        $("#windowsIcon,#linuxIcon,#iosIcon").show();
                        break;
                    case "WMI":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#linuxIcon,#androidIcon").hide();
                        $("#windowsIcon,#macIcon,#iosIcon").show();
                        break;
                    case "MAL":
                        changeDrop('macIcon');
                        getOSLevelTiles('mac');
                        $("#windowsIcon,#iosIcon").hide();
                        $("#macIcon,#linuxIcon,#androidIcon").show();
                        break;
                    case "MIA":
                        changeDrop('macIcon');
                        getOSLevelTiles('mac');
                        $("#windowsIcon,#linuxIcon").hide();
                        $("#macIcon,#iosIcon,#androidIcon").show();
                        break;
                    case "MIL":
                        changeDrop('macIcon');
                        getOSLevelTiles('mac');
                        $("#windowsIcon,#androidIcon").hide();
                        $("#macIcon,#iosIcon,#linuxIcon").show();
                        break;
                    case "WM":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#linuxIcon,#androidIcon,#iosIcon").hide();
                        $("#macIcon,#windowsIcon").show();
                        break;
                    case "WA":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#linuxIcon,#macIcon,#iosIcon").hide();
                        $("#androidIcon,#windowsIcon").show();
                        break;
                    case "WL":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#androidIcon,#macIcon,#iosIcon").hide();
                        $("#linuxIcon,#windowsIcon").show();
                        break;
                    case "WI":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#androidIcon,#macIcon,#linuxIcon").hide();
                        $("#iosIcon,#windowsIcon").show();
                        break;
                    case "MA":
                        changeDrop('macIcon');
                        getOSLevelTiles('mac');
                        $("#iosIcon,#windowsIcon,#linuxIcon").hide();
                        $("#androidIcon,#macIcon").show();
                        break;
                    case "MI":
                        changeDrop('macIcon');
                        getOSLevelTiles('mac');
                        $("#androidIcon,#windowsIcon,#linuxIcon").hide();
                        $("#iosIcon,#macIcon").show();
                        break;
                    case "ML":
                        changeDrop('macIcon');
                        getOSLevelTiles('mac');
                        $("#androidIcon,#windowsIcon,#iosIcon").hide();
                        $("#linuxIcon,#macIcon").show();
                        break;
                    case "AI":
                        changeDrop('androidIcon');
                        getOSLevelTiles('android');
                        $("#linuxIcon,#windowsIcon,#macIcon").hide();
                        $("#androidIcon,#iosIcon").show();
                        break;
                    case "AL":
                        changeDrop('androidIcon');
                        getOSLevelTiles('android');
                        $("#iosIcon,#windowsIcon,#macIcon").hide();
                        $("#androidIcon,#linuxIcon").show();
                        break;
                    case "Mac":
                        changeDrop('macIcon');
                        getOSLevelTiles('mac');
                        $("#iosIcon,#windowsIcon,#androidIcon,#linuxIcon").hide();
                        $("#macIcon").show();
                        break;
                    case "Windows":
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#iosIcon,#macIcon,#androidIcon,#linuxIcon").hide();
                        $("#windowsIcon").show();
                        break;
                    case "Android":
                        changeDrop('androidIcon');
                        getOSLevelTiles('android');
                        $("#iosIcon,#macIcon,#windowsIcon,#linuxIcon").hide();
                        $("#androidIcon").show();
                        break;
                    case "Linux":
                        changeDrop('linuxIcon');
                        getOSLevelTiles('linux');
                        $("#iosIcon,#macIcon,#windowsIcon,#androidIcon").hide();
                        $("#linuxIcon").show();
                        break;
                    case "iOS":
                        changeDrop('iosIcon');
                        getOSLevelTiles('ios');
                        $("#linuxIcon,#macIcon,#windowsIcon,#androidIcon").hide();
                        $("#iosIcon").show();
                        break;
                    default:
                        changeDrop('windowsIcon');
                        getOSLevelTiles('windows');
                        $("#windowsIcon,#androidIcon,#macIcon,#iosIcon,#linuxIcon").show();
                        break;
                }
                setTimeout(function () {


                }, 2000);
            }
        },
        error: function (err) { }
    });
}
function loadEnt() {
    window.location.href = "../customer/entitlement.php" + "&csrfMagicToken=" + csrfMagicToken;
}
//
function changeDrop(iconType) {

    if (iconType == false) {
        conType = $('#selOsType').val();
        $("#check").html(iconType);
    }
    if (iconType == 'windowsIcon') {
        $("#check").html("Windows");
        $("#imgDrop").attr('src', '../vendors/images/windows.png');
        $("#osTypeDrop").text("Windows");
        $("#clickList").html("");
        //        localStorage.setItem("ostype", "Windows");
    } else if (iconType == 'androidIcon') {
        $("#check").html("Andriod");
        $("#imgDrop").attr('src', '../vendors/images/android.png');
        $("#osTypeDrop").text("Android");
        $("#clickList").html("");
    } else if (iconType == 'macIcon') {
        $("#check").html("Mac");
        $("#imgDrop").attr('src', '../vendors/images/mac-black.png');
        $("#osTypeDrop").text("Mac");
        $("#clickList").html("");
        //        localStorage.setItem("ostype", "Mac");
    } else if (iconType == 'iosIcon') {
        $("#check").html("iOS");
        $("#imgDrop").attr('src', '../vendors/images/ios.png');
        $("#osTypeDrop").text("iOS");
        $("#clickList").html("");
    } else if (iconType == 'linuxIcon') {
        $("#check").html("Linux");
        $("#imgDrop").attr('src', '../vendors/images/linux-black.png');
        $("#osTypeDrop").text("Linux");
        $("#clickList").html("");
    }
}

//    showToolboxinteractive();
//    callHome();

function showServilogSummary() {

    var installationDate = 1234565;
    $.ajax({
        type: "GET",
        url: "supportAjaxFunction.php",
        data: {
            function: 'get_homescreenData',
            machine: glMachineName,
            installationDate: installationDate,
            csrfMagicToken: csrfMagicToken
        },
        success: function (msg) {

            var serSumMsg = msg.split('##');

            $("#pfc_1").html(serSumMsg[0]);
            $("#afc_1").html(serSumMsg[1]);
            $("#tfc_1").html(serSumMsg[2]);
            $("#sfc_1").html(serSumMsg[3]);

        }
    });
}

function showCriticalSummary() {

    $(".loadingStage").css({ 'display': 'block' });
    $.ajax({
        type: "GET",
        url: "supportAjaxFunction.php",
        data: {
            "function": ' getCriticalVal',
            machine: glMachineName,
            csrfMagicToken: csrfMagicToken
        },
        success: function (msg) {

            var serSumMsg = msg.split('##');
            if (serSumMsg[0] === 1 || serSumMsg[0] === '1') {
                $('#criticalCnt').html('<span>1 critical Issue</span>');
            } else {
                var crVal = serSumMsg[0] + ' critical Issues';
                $('#criticalCnt').html('<span>' + crVal + '</span>');
            }



            if (serSumMsg[1] === 1 || serSumMsg[1] === '1') {
                $('#majorCnt').html('<span>1 Major Issue</span>');
            } else {
                var mrVal = serSumMsg[1] + ' Major Issues';
                $('#majorCnt').html('<span>' + mrVal + '</span>');
            }
            if (serSumMsg[2] !== '') {
                var convertedStartDate = serSumMsg[2];

                var today = new Date(convertedStartDate);
                var dd = today.getDate();
                var mm = today.getMonth() + 1;
                var yyyy = today.getFullYear();
                var lastScanned = mm + '/' + dd + '/' + yyyy + ' @ ' + today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
                $('#scanDataTime').html(lastScanned);
            }

        }
    });

}


function getServiceLog(type) {
    $("#progressMainDiv").show();
    $(".loadingStage").css({ 'display': 'block' });
    serviceLogVal = 1;
    $.ajax({
        type: "GET",
        url: "../includes/notification_ajax.php",
        data: {
            "function": getServiceLogDetails,
            type: type,
            machine: glMachineName,
            installationDate: installationDate,
            csrfMagicToken: csrfMagicToken
        },
        success: function (msgVal) {
            $(".loadingStage").css({ 'display': 'none' });
            $("#progressDiv").show();
            $("#progressMainDiv").css("min-height", "350px");
            $("#progressMainDiv").html(msgVal);

            scroller();
        }
    });
}


function goToComplteScanPage() {

    //renderCompleteScanPageButton();
    $('#index').hide();
    $('#fixItPage').hide();
    $('#completeScanScreen').show();
    $('#ToolBox').hide();
    $('#settings').hide();
    $('#sysinfo').hide();

    $('#parentSPTitle').html('<span>View & Fix System Heal..</span>');
    $('#parentSPTitle').attr("title", 'View & Fix System Health Issues');
    $("#mainParentSPTitle").html('<span>View & Fix System Health Issues</span>');
    //$("#parentDesc").html('Description: Nanoheal runs proactive fixes every time it finds issues on your machine.');
    /*$('#issueDiv').hide();
     $("#auditDiv").hide();
     $("#scheduleDiv").hide();
     $("#gridContent").show();
     $("#tilesDiv").html('');
     $(".backBtn").show('');*/

    $.ajax({
        type: "GET",
        url: "supportAjaxFunction.php",
        data: {
            "function": 'getCompleteScanList',
            machine: glMachineName,
            csrfMagicToken: csrfMagicToken
        },
        success: function (msg) {
            var serSumMsg = msg.split('##');
            $("#tilesDiv").html(serSumMsg[0]);
            var disMasg = 'Nanoheal system scan identified ' + serSumMsg[1] + ' issues <br /><br /><br /> The most important once are at the top of the list on left and are marked as.';
            $("#parentDesc").html('<span>' + disMasg + '</span>');
            $("#progressMainDiv").html('');
        }
    });
}

$('.showToolbox').click(function () {

    $('#index').hide();
    $('#fixItPage').hide();
    $('#completeScanScreen').hide();
    $('#ToolBox').show();
    $('#settings').hide();
    $('#sysinfo').hide();
    $('#fixItPage').hide();
    $('#ServiceLogs').hide();
    //tileHome(1);
});

function showToolboxinteractive() {

    $('#index').hide();
    $('#fixItPage').hide();
    $('#completeScanScreen').hide();
    $('#ToolBox').show();
    $('#settings').hide();
    $('#sysinfo').hide();
    $('#fixItPage').hide();
    $('#ServiceLogs').hide();
    //tileHome(1);

}

function callHome() {

    $('#index').hide();
    $('#fixItPage').show();
    $('#completeScanScreen').hide();
    $('#ToolBox').hide();
    $('#settings').hide();
    $('#sysinfo').hide();
    $('#ServiceLogs').hide();
    $('#progressDiv').hide();
    $('#ExceCancelButton').html("");
    showServilogSummary();
    showCriticalSummary();
}


$("#windowsIcon").click(function () {
    $("#windowsIcon").addClass("is-selected");
    $("#androidIcon,#macIcon,#iosIcon,#linuxIcon").removeClass("is-selected");
    $("#windowsIcon").css("background-color", "transparent");
    getOSLevelTiles('windows');
});
$("#androidIcon").click(function () {
    $("#androidIcon").addClass("is-selected");
    $("#windowsIcon,#macIcon,#iosIcon,#linuxIcon").removeClass("is-selected");
    $("#androidIcon").css("background-color", "transparent");
    getOSLevelTiles('android');
});
$("#macIcon").click(function () {
    $("#macIcon").addClass("is-selected");
    $("#androidIcon,#windowsIcon,#iosIcon,#linuxIcon").removeClass("is-selected");
    $("#macIcon").css("background-color", "transparent");
    getOSLevelTiles('mac');
});
$("#iosIcon").click(function () {
    $("#iosIcon").addClass("is-selected");
    $("#androidIcon,#macIcon,#windowsIcon,#linuxIcon").removeClass("is-selected");
    $("#iosIcon").css("background-color", "transparent");
    getOSLevelTiles('ios');
});
$("#linuxIcon").click(function () {
    $("#linuxIcon").addClass("is-selected");
    $("#androidIcon,#macIcon,#iosIcon,#windowsIcon").removeClass("is-selected");
    $("#linuxIcon").css("background-color", "transparent");
    getOSLevelTiles('linux');
});

function checkBox(e, obj) {
    e = e || event;/* get IE event ( not passed ) */
    e.stopPropagation ? e.stopPropagation() : e.cancelBubble = true;
    //$('.commonClass').prop('checked', obj.checked);
    $('.scheduleClass').prop('checked', obj.checked);
}

function exportReports() {

    if ($("#deleteBtn").is(":visible")) {
        var text = $('#scheduleGrid').text();
        if (!text) {
            selctRowConfrim("<span>There is no data to export</span>");
        } else {
            window.location = 'manageExportExcel.php?export=schedule';
        }
    } else {
        var text = $('#auditGrid').text();
        if (!text) {
            selctRowConfrim("<span>There is no data to export</span>");
        } else {
            window.location = 'manageExportExcel.php?export=audit';
        }
    }
}

function deleteAdvGroup() {

    var i = 0;
    var schdIdArray = new Array();
    $('input[name="checkNoc"]:checked').each(function () {
        var schId = $(this).val();
        schdIdArray[i] = [schId];
        i++;
    });

    var selectedRow = schdIdArray;

    if (schdIdArray.length !== 0) {
        confirmdelete('<span>Delete, Do you really  want to delete this Jobs?</span>', selectedRow);
        return;
    } else {
        selctRowConfrim("<span>Please select any Device</span>");
        return;
    }
}

function selctRowConfrim(txt) {
    var url = "../includes/gridOptionsValidation.php?text=" + txt;

    jQuery_1_7.nyroModalManual({
        debug: false,
        width: 400, // default Width If null, will be calculate automatically
        bgColor: '#333',
        url: url,
        ajax: { data: '', type: 'get' },
        closeButton: true,
        css: {// Default CSS option for the nyroModal Div. Some will be overwritten or updated when using IE6
            wrapper: {
                position: 'absolute',
                top: '50%',
                left: '50%'
            }
        }
    });
}

function confirmdelete(txt, id) {
    var url = "delete_schedulejobs.php?text=" + txt + "&id=" + id;

    jQuery_1_7.nyroModalManual({
        debug: false,
        width: 400, // default Width If null, will be calculate automatically
        bgColor: '#333',
        url: url,
        ajax: { data: '', type: 'get' },
        closeButton: true,
        css: {// Default CSS option for the nyroModal Div. Some will be overwritten or updated when using IE6
            wrapper: {
                position: 'absolute',
                top: '50%',
                left: '50%'
            }
        }
    });
}

$('#header_searchbox').keyup(function () {
    var searchText = $(this).val().toUpperCase();
    if (searchText != "") {
        searchL3Profile(searchText);
    } else {
        $("#clickList").html("");
    }
});

$('#troubleshooting_searchbox').keyup(function () {
    var searchText = $(this).val().toUpperCase();
    if (searchText != "") {
        searchL3Profile(searchText);
    } else {
        $("#clickList").html("");
    }
});

$('#troubleshooting_searchbox2').keyup(function () {
    var searchText = $(this).val();
    if (searchText != "") {
        srchpgid = '';
        searchL3Profile(searchText);
    } else {
        $("#clickList").html("");
    }
});

function searchL3Profile(searchText) {
    $('#loader').show();
    $(".tableloader").show();
    $("#clickList").html("");
    $("#listDiv").fadeIn(100);
    $.ajax({
        type: 'POST',
        url: "../communication/communication_ajax.php",
        data: { 'function': 'profile_DataList', 'os': OSDB, 'pageId': srchpgid, 'ossub': OSSUB, 'csrfMagicToken': csrfMagicToken, 'searchProfile': searchText },

        success: function (data) {
            $('.loader').hide();
            var res = $.trim(data);
            var liMsg = res.split('##');
            if (liMsg[0] != "") {
                $("#clickList").html(liMsg[0]);
            } else {
                $("#clickList").html("No results found");
            }

        },
        error: function (err) {
            console.log(err);
        }
    });

}


//New Functionalities for Add/Delete/Edit Profile
function gotoMain() {
    location.reload();
}

var isEditProfile = false;
var isDeleteProfile = false;

function toggelEditButton() {
    if (isEditProfile) {
        $('.troubIcon').attr("style", "display:block"); ///L1 - pencil icon
        $('.troubIconicon').attr("style", "display:block");///L2 & L3 - pencil icon
    } else {
        $('.troubIcon').attr("style", "display:none"); ///L1 - pencil icon
        $('.troubIconicon').attr("style", "display:none");///L2 & L3 - pencil icon
    }
}


function openEditProfile() {
    $('#addProfile').hide();
    $('#editProfile').hide();
    $('#deleteProfile').hide();
    $('#enbdisprofile').hide();
    $('#backoption').show();
    checkLevel = "Edit";
    closePopUp();
    isEditProfile = true;
    var check = 'edit';
    pageId = 1;
    $("#ExceCancelButton").html('');
    $.ajax({
        type: "POST",
        url: "../communication/communication_ajax.php",

        data: { 'function': 'profile_Data', 'os': OSDB, 'pageId': pageId, 'ossub': OSSUB, 'level': check, 'csrfMagicToken': csrfMagicToken },
        success: function (msgVal) {
            $(".tableloader").hide();
            msg = $.trim(msgVal);
            var liMsg = msg.split('##');
            if (liMsg[1] === undefined || liMsg[1] === 'undefined') {
                $("#parentDesc").html('');
                $('#showSiteMsg').show();
                $('#config_details').html(msg);
                $("#toolboxList").html(liMsg[0]).hide();
                toggelEditButton();
            } else {
                $("#toolboxList").show();
                $('#showSiteMsg').hide();
                $('#listDiv').hide();
                $("#toolboxList").html(liMsg[0]);
                $("#backParentId").val(liMsg[1]);
                toggelEditButton();
                glmenuitem = liMsg[2];
                var ptitle = liMsg[3];
                if (ptitle.length > 22) {
                    var parentTitle = ptitle.substring(0, 22);
                    ptitle = parentTitle + '..';
                }
                $('#parentTitle').text(ptitle);
                $('#parentTitle').attr("title", liMsg[3]);
                $("#mainParentTitle").html("Troubleshooting");
                $("#parentDesc").html(liMsg[4]);
            }
        }
    });

    $('#loader_pbar').hide();
}

function openDeleteProfile() {
    $('.troubIcon').attr("style", "display:none");
    $('.troubIconicon').attr("style", "display:none");
    checkLevel = "Delete";
    closePopUp();
    isDeleteProfile = true;
    if (Liclicked == 'false') {
        $.notify("Please select a profile to delete");
    } else {
        $('#backoption').show();
        if (Tile_type == 'L1') {
            var tile_mid = $('.midselected').val();
        } else if (Tile_type == 'L2') {
            var tile_mid = $('.midselected2').val();
        } else if (Tile_type == 'L3') {
            if ($('ul').hasClass('expand')) {
                var tile_mid = $('.selected').val();
            } else {
                var tile_mid = $('.midselected2').val();
            }
        }
        sweetAlert({
            title: 'Are you sure you want to delete the profile?',
            text: "You want be able to revert this action!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#050d30',
            cancelButtonColor: '#fa0f4b',
            cancelButtonText: "No, cancel it!",
            confirmButtonText: 'Yes, delete it!'
        }).then(function (result) {
            $.ajax({
                type: 'POST',
                url: "../communication/communication_ajax.php",
                data: {
                    function: 'delete_profileDataList',
                    mid: tile_mid, csrfMagicToken: csrfMagicToken
                },
                success: function (data) {
                    //                                console.log("success");
                    $.notify('Profile Deleted Successfully');
                    location.reload();
                },
                error: function (err) {
                    console.log("error");
                }
            });
        }).catch(function (reason) {

        });
    }
}

function openAddProfile(newmid, profile_keys, newpage) {
    $('#selectL12').hide();
    $('#show_L1profiles').show();
    $("#prof_type").prop("selectedIndex", 0);
    $('#selectL12').prop("selectedIndex", 0);
    var splitKeys = profile_keys.split("***");
    var html_val = "";
    for (var i = 0; i < splitKeys.length; i++) {
        var id_name = splitKeys[i];
        if (id_name === "mid") {
            html_val += '<div class="form-group has-label">' +
                '<em class="error" id="required_prof_' + id_name + '">*</em>' +
                '<label>' + id_name + '</label>' +
                '<input class="form-control addTroubleshooterTileDetails" name="' + id_name + '" id="prof_' + id_name + '" value=' + newmid + ' readonly/>' +
                '</div>';
        } else if (id_name === "page") {
            html_val += '<div class="form-group has-label">' +
                '<em class="error" id="required_prof_' + id_name + '">*</em>' +
                '<label>' + id_name + '</label>' +
                '<input type="hidden" value=' + newpage + ' id="ParentIdUpdated">' +
                '<input class="form-control addTroubleshooterTileDetails" name="' + id_name + '" id="prof_' + id_name + '" value=' + newpage + ' readonly/>' +
                '</div>';
        } else if (id_name === "type") {
            html_val += '<div class="form-group has-label">' +
                '<label style="display:none" id="L1_title"> L1 profiles</label>' +
                '<select onchange="checklevel(this)"  class="form-control" id="show_L1profiles" data-style="btn btn-info" title="Choose type" data-size="3" name="show_L1profiles" style="padding: 0px 0px 0px 8px; display:none;">' +
                '</select>' +
                '<label style="display:none" id="L2_title">L2  profiles</label>' +
                '<select class="form-control" data-style="btn btn-info" id="L2_title_options" onchange="checkL2(this)" title="Choose type" data-size="3" name="L2_title_options" style="padding: 0px 0px 0px 8px; display:none;">' +
                '</select>' +
                '<em class="error" id="required_prof_' + id_name + '">*</em>' +
                '<label>' + id_name + '</label>' +
                '<select class="form-control addTroubleshooterTileDetails"  onchange="check(this)" data-style="btn btn-info" title="Choose type" data-size="3" id="prof_' + id_name + '" name="' + id_name + '" style="padding: 0px 0px 0px 8px;">' +
                '<option id="L1_profile" selected="" value="Please select L1 Profile" >Please select L1 Profile</option>' +
                '<option id="L2_profile"  value="L2">L2</option>' +
                '<option id="L3_profile" value="L3">L3</option>' +
                '</select>' +
                '<select class="form-control addTroubleshooterTileDetails"  onchange="selectreqprofiles(this)" data-style="btn btn-info" title="Choose type" data-size="3" id="selectL12" name="selectL12" style="padding: 0px 0px 0px 8px;display:none">' +
                '<option id="L1_profile" selected="" value="Please select the profiles" >Please select the profile type</option>' +
                '<option id="L2_profile" selected="" value="L1">L1</option>' +
                '<option id="L3_profile" value="L2">L2</option>' +
                '</select>' +
                '</div>';
        } else {
            html_val += '<div class="form-group has-label">' +
                '<em style="display:block" class="error" id="required_prof_' + id_name + '">*</em>' +
                '<label>' + id_name + '</label>' +
                '<input class="form-control addTroubleshooterTileDetails" name="' + id_name + '" id="prof_' + id_name + '"/>' +
                '<p id="errMsg_' + id_name + '" name="errMsg" style="font-size: 11px; padding-left: 15px; color: red; font-style: italic;"></p>' +
                '</div>';
        }

    }
    $('#add_inputfields').html(html_val);
    $('#prof_parentId').val('1');
}

function selectreqprofiles(object) {
    var value = $('#ParentIdUpdated').val();
    var tiletype = $(object).val();
    if (tiletype == 'L1') {
        $.ajax({
            type: 'POST',
            url: "../communication/communication_ajax.php",
            data: { function: 'getL1tiles_info', type: tiletype, csrfMagicToken: csrfMagicToken },
            success: function (data) {
                $("#show_L1profiles").html(data);
                $("#show_L1profiles").append('<option selected="" id="profile0" value="NewL1profile">NewL1profile</option>');
                $("#show_L1profiles").prepend('<option selected="" id="profile" value="">Please Select the L1 Profile</option>');
                $("#show_L1profiles").prop("selectedIndex", 0);
                $('#L1_title').show();
                $('#L1_title').html("select " + tiletype + "profiles");
                $('#show_L1profiles').show();
                var tilevalue = $('#show_L1profiles').val();
                var check = tilevalue.replace("##", ",");
                var orgtileval = check.replace("##", ",");
                //                    console.log(orgtileval);
                orgtileval = orgtileval.split(",");
                //                    console.log(orgtileval[0]);
                $('#prof_page').val('');
                $('#prof_parentId').val(value);
                $('#prof_dart').val('');
                $('#prof_variable').val('');

            },
            error: function (err) {
                console.log(err);
            }
        });
    } else if (tiletype == 'L2') {
        $.ajax({
            type: 'POST',
            url: "../communication/communication_ajax.php",
            data: { function: 'getL1tiles_info', type: tiletype, csrfMagicToken: csrfMagicToken },
            success: function (data) {
                $("#show_L1profiles").html(data);
                $("#show_L1profiles").prop("selectedIndex", 0);
                $('#L1_title').show();
                $('#L1_title').html("select " + tiletype + "profiles");
                $('#show_L1profiles').show();
                $("#show_L1profiles").attr('onchange', 'checkL1type(this)');
                var tilevalue = $('#show_L1profiles').val();
                var check = tilevalue.replace("##", ",");
                var orgtileval = check.replace("##", ",");
                //                    console.log(orgtileval);
                orgtileval = orgtileval.split(",");
                //                    console.log(orgtileval[0]);
                $('#prof_page').val(orgtileval[1]);
                $('#prof_parentId').val(orgtileval[0]);
                $('#prof_dart').val('');
                $('#prof_variable').val('');

            },
            error: function (err) {
                console.log(err);
            }
        });
    } else {
        $('#L1_title').hide();
        $('#show_L1profiles').hide();
    }

}

function checkL1type(object) {

    var tiletype = $(object).val();
    var check = tiletype.replace("##", ",");
    var orgtileval = check.replace("##", ",");
    orgtileval = orgtileval.split(",");
    $('#prof_page').val(orgtileval[1]);
}

function checklevel(object) {
    var tiletype = $(object).val();
    var check = tiletype.replace("##", ",");
    var orgtileval = check.replace("##", ",");
    orgtileval = orgtileval.split(",");
    $('#prof_page').val(orgtileval[0]);
}

function checkL2(object) {
    var value = $('#ParentIdUpdated').val();
    var tiletype = $(object).val();
    var check = tiletype.replace("##", ",");
    var orgtileval = check.replace("##", ",");
    orgtileval = orgtileval.split(",");
    $('#prof_page').val(orgtileval[1]);
    $('#prof_parentId').val(value);
}

function check(obj) {
    var value = $('#ParentIdUpdated').val();
    var icontype = $(obj).val();
    switch (icontype) {
        case "L2":
            $('#selectL12').hide();
            $('#L1options').hide();
            $('#L2_title').hide();
            $('#L2_title_options').hide();
            $('#prof_page').val('');
            $('#prof_parentId').val('');
            $.ajax({
                type: 'POST',
                url: "../communication/communication_ajax.php",
                data: { function: 'getL1tiles_info', type: "L1", csrfMagicToken: csrfMagicToken },
                success: function (data) {
                    $("#show_L1profiles").html(data);
                    $("#show_L1profiles").append('<option selected="" id="profile0" value="NewL1profile">NewL1profile</option>');
                    $("#show_L1profiles").prepend('<option selected="" id="profile" value="Please Select the L1 Profile">Please Select the L1 Profile</option>');
                    $("#show_L1profiles").prop("selectedIndex", 0);

                    $('#L1_title').show();
                    $('#show_L1profiles').show();
                    var tilevalue = $('#show_L1profiles').val();
                    var check = tilevalue.replace("##", ",");
                    var orgtileval = check.replace("##", ",");
                    //                    console.log(orgtileval);
                    orgtileval = orgtileval.split(",");
                    //                    console.log(orgtileval[0]);
                    $('#prof_page').val('');
                    $('#prof_parentId').val(value);
                    $('#prof_dart').val('');
                    $('#prof_variable').val('');
                },
                error: function (err) {
                    console.log(err);
                }
            });
            break;
        case "L3":
            $('#selectL12').prop("selectedIndex", 0);
            $('#selectL12').show();
            $('#L1options').hide();
            $('#L1_title').hide();
            $('#show_L1profiles').hide();
            $('#prof_page').val('');
            $('#prof_parentId').val('');
            $('#L3_title').show();
            $('#L3_title_options').show();
            var Tile_type = $('#L3_title_options').val();
            break;
    }
}

function newL1tiles() {

}

function add_newprofile() {
    var value = $('#ParentIdUpdated').val();
    var errorVal = 0;
    errorVal = validateprofile();
    var new_data = {};
    var autoL1Profile = {};
    if (errorVal === 0) {
        //        console.log("inside if");
        if ($('select[name=show_L1profiles] option:selected').val() == 'NewL1profile') {
            var newparentId = 1;
            var newpage = value;

            autoL1Profile['enabledisable'] = $('#prof_EnableDisable').val();
            autoL1Profile['mid'] = $('#prof_mid').val();
            autoL1Profile['menuitem'] = $('#prof_menuItem').val();
            autoL1Profile['type'] = 'L1';
            autoL1Profile['parentid'] = "1";
            autoL1Profile['profile'] = $('#prof_profile').val();
            autoL1Profile['dart'] = '';
            autoL1Profile['variable'] = '';
            autoL1Profile['varvalue'] = '';
            autoL1Profile['shortdesc'] = $('#prof_shortDesc').val();
            autoL1Profile['description'] = $('#prof_description').val();
            autoL1Profile['tiledesc'] = $('#prof_tileDesc').val();
            autoL1Profile['os'] = $('#prof_OS').val();
            autoL1Profile['page'] = newpage;
            autoL1Profile['status'] = $('#prof_status').val();
            autoL1Profile['authflag'] = $('#prof_authFalg').val();
            autoL1Profile['usagetype'] = $('#prof_usageType').val();
            //                console.log("autoL1Profile : "+JSON.stringify(autoL1Profile));
            submitProfile(JSON.stringify(autoL1Profile));

            new_data['enabledisable'] = $('#prof_EnableDisable').val();
            new_data['mid'] = $('#prof_mid').val();
            new_data['menuitem'] = $('#prof_menuItem').val();
            new_data['type'] = $('#prof_type').val();
            new_data['parentid'] = newpage;
            new_data['profile'] = $('#prof_profile').val();
            new_data['dart'] = $('#prof_dart').val();
            new_data['variable'] = $('#prof_variable').val();
            new_data['varvalue'] = $('#prof_varValue').val();
            new_data['shortdesc'] = $('#prof_shortDesc').val();
            new_data['description'] = $('#prof_description').val();
            new_data['tiledesc'] = $('#prof_tileDesc').val();
            new_data['os'] = $('#prof_OS').val();
            new_data['page'] = "2";
            new_data['status'] = $('#prof_status').val();
            new_data['authflag'] = $('#prof_authFalg').val();
            new_data['usagetype'] = $('#prof_usageType').val();
        } else {
            new_data['enabledisable'] = $('#prof_EnableDisable').val();
            new_data['mid'] = $('#prof_mid').val();
            new_data['menuitem'] = $('#prof_menuItem').val();
            new_data['type'] = $('#prof_type').val();
            new_data['parentid'] = $('#prof_parentId').val();
            new_data['profile'] = $('#prof_profile').val();
            new_data['dart'] = $('#prof_dart').val();
            new_data['variable'] = $('#prof_variable').val();
            new_data['varvalue'] = $('#prof_varValue').val();
            new_data['shortdesc'] = $('#prof_shortDesc').val();
            new_data['description'] = $('#prof_description').val();
            new_data['tiledesc'] = $('#prof_tileDesc').val();
            new_data['os'] = $('#prof_OS').val();
            new_data['page'] = $('#prof_page').val();
            new_data['status'] = $('#prof_status').val();
            new_data['authflag'] = $('#prof_authFalg').val();
            new_data['usagetype'] = $('#prof_usageType').val();
        }


        var final_data = JSON.stringify(new_data);
        //        console.log("new_data : "+final_data);
        submitProfile(final_data);

    }
}

function submitProfile(final_data) {
    $.ajax({
        url: "../communication/communication_ajax.php",
        type: 'POST',
        data: { function: 'add_newprofileDataList', data: final_data, csrfMagicToken: csrfMagicToken },
        dataType: "text",
        success: function (data) {
            rightContainerSlideClose('add_profile');
            $.notify("profile updated successfully");
            location.reload();
        },
        error: function (err) {
            console.log(err);
        }
    });
}
function saveEditedProfile() {

    var errorVal = 0;
    errorVal = validateEditprofile();
    var new_data = {};
    if (errorVal === 0) {
        //        console.log("inside if");
        new_data['enabledisable'] = $('#prof_edit_enabledisable').val();
        new_data['mid'] = $('#prof_edit_mid').val();
        new_data['menuitem'] = $('#prof_edit_menuitem').val();
        new_data['type'] = $('#prof_edit_type').val();
        new_data['parentid'] = $('#prof_edit_parentid').val();
        new_data['profile'] = $('#prof_edit_profile').val();
        new_data['dart'] = $('#prof_edit_dart').val();
        new_data['variable'] = $('#prof_edit_variable').val();
        new_data['varvalue'] = $('#prof_edit_varvalue').val();
        new_data['shortdesc'] = $('#prof_edit_shortdesc').val();
        new_data['description'] = $('#prof_edit_description').val();
        new_data['tiledesc'] = $('#prof_edit_tiledesc').val();
        new_data['os'] = $('#prof_edit_os').val();
        new_data['page'] = $('#prof_edit_page').val();
        new_data['status'] = $('#prof_edit_status').val();
        new_data['authflag'] = $('#prof_edit_authfalg').val();
        new_data['usagetype'] = $('#prof_edit_usagetype').val();
        var final_data = JSON.stringify(new_data);
        $.ajax({
            type: "POST",
            url: "../communication/communication_ajax.php",
            dataType: "json",
            data: {
                'function': 'edit_profileDataList',
                "edit_profile": final_data,
                csrfMagicToken: csrfMagicToken
            },
            success: function (data) {
                if (data == "success") {
                    $('#loader_pbar').show();
                    rightContainerSlideClose('edit_profile');
                    $.notify("profile updated successfully");
                    location.reload();
                } else {
                    rightContainerSlideClose('edit_profile');
                    $.notify("Some error occurred. Please try again.");
                }
                isEditProfile = false;
                toggelEditButton();
            },
            error: function (err) {
                console.log("error");
            }
        });
    }
}

function reloadUL() {
    tileHomeNotif('2');
    $('#toolboxList').show();
    $('.troubIcon').show();
    $('#run_btn').show();
    $('.troubIconicon').show();

}

function validateEditprofile() {
    $(".error").html("*");
    var errorVal = 0;

    $('.editTroubleshooterTileDetails').each(function () {
        var field_id = this.id;
        var field_value = $("#" + field_id).val();
        if (field_id != "") {
            if ($.trim(field_value) === "") {
                $("#required_" + field_id).css("color", "red").html(" required");
                errorVal++;
            } else if (field_id == "prof_edit_enabledisable") {
                if (!validate_Number(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Numeric values");
                    errorVal++;
                }
            } else if (field_id == "prof_edit_mid") {
                if (!validate_Number(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Numeric values");
                    errorVal++;
                }
            } else if (field_id == "prof_edit_page") {
                if (!validate_Number(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Numeric values");
                    errorVal++;
                }
            } else if (field_id == "prof_edit_usagetype") {
                if (!validate_Number(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Numeric values");
                    errorVal++;
                }
            } else if (field_id == "prof_edit_parentid") {
                if (!validate_Number(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Numeric values");
                    errorVal++;
                }
            } else if (field_id == "prof_edit_variable") {
                if (!validate_Alphanumeric(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                    errorVal++;
                }
            } else if (field_id == "prof_edit_varvalue") {
                if (!validate_Alphanumeric(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                    errorVal++;
                }
            } else if (field_id == "prof_edit_dart") {
                if (!validate_Alphanumeric(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Alphanumeric values");
                    errorVal++;
                }
            }
        }
    });

    return errorVal;
}

function validateprofile() {
    $(".error").html("*");
    var errorVal = 0;
    var arr = [];
    $('.addTroubleshooterTileDetails').each(function (index, currentElement) {
        var field_name = this.name;
        var field_id = this.id;
        var field_value = $("#" + field_id).val();
        arr[field_name] = field_value;
        $("#required_" + field_id).html('*');
        if (field_id != "") {
            //            console.log("FID-> " + field_id + " FVAL-> " + field_value);
            if ($.trim(field_value) === "") {
                $("#required_" + field_id).css("color", "red").html(" required");
                errorVal++;
            } else if (field_id == "prof_EnableDisable") {
                if (!validate_Number(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter either 0 or 1 ");
                    errorVal++;
                }
            } else if (field_id == "prof_mid") {
                if (!validate_Number(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Numeric values");
                    errorVal++;
                }
            } else if (field_id == "prof_parentId") {
                if (!validate_Number(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Numeric values");
                    errorVal++;
                }
            } else if (field_id == "prof_usageType") {
                if (!validate_Number(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only Numeric values");
                    errorVal++;
                }
            } else if (field_id == "prof_variable") {
                if (!validate_AlphaNumeric(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only AlphaNumeric values");
                    errorVal++;
                }
            } else if (field_id == "prof_varValue") {
                if (!validate_AlphaNumeric(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only AlphaNumeric values");
                    errorVal++;
                }
            } else if (field_id == "prof_dart") {
                if (!validate_AlphaNumeric(field_value)) {
                    $("#required_" + field_id).css("color", "red").html("Enter only AlphaNumeric values");
                    errorVal++;
                }
            }
        }
    });
    return errorVal;
}

function enable_disableprofile() {
    $('#addProfile').hide();
    $('#editProfile').hide();
    $('#deleteProfile').hide();
    $('#enbdisprofile').hide();
    $('#backoption').show();
    closePopUp();
    isEditProfile = true;
    var check = 'enabledis';
    pageId = 1;
    $("#ExceCancelButton").html('');
    $.ajax({
        type: "POST",
        url: "../communication/communication_ajax.php",
        data: { 'function': 'profile_Data', 'os': OSDB, 'pageId': pageId, 'ossub': OSSUB, 'level': check, 'csrfMagicToken': csrfMagicToken },

        success: function (msgVal) {
            $(".tableloader").hide();
            msg = $.trim(msgVal);
            var liMsg = msg.split('##');
            if (liMsg[1] === undefined || liMsg[1] === 'undefined') {
                $("#parentDesc").html('');
                $('#showSiteMsg').show();
                $('#config_details').html(msg);
                $("#toolboxList").html(liMsg[0]).hide();
                toggelEditButton();
            } else {
                $("#toolboxList").show();
                $('#showSiteMsg').hide();
                $('#listDiv').hide();
                $("#toolboxList").html(liMsg[0]);
                $("#backParentId").val(liMsg[1]);
                toggelEditButton();
                glmenuitem = liMsg[2];
                var ptitle = liMsg[3];
                if (ptitle.length > 22) {
                    var parentTitle = ptitle.substring(0, 22);
                    ptitle = parentTitle + '..';
                }
                $('#parentTitle').text(ptitle);
                $('#parentTitle').attr("title", liMsg[3]);
                $("#mainParentTitle").html("Troubleshooting");
                $("#parentDesc").html(liMsg[4]);
            }
        }
    });
}
