var OSName;
var OSDB;
var OSSUB = 'NA';
var mlist;
var glMachineName = '';
var glmenuitem;
var arrAllcheckVal = '';
var srchpgid = '';

var frmPg = '<?php echo $frmPg; ?>';
if (frmPg === '1') {
    $("#scheduleDiv").hide();
    $("#auditDiv").show();
    $('#ToolBox').hide();
    auditGrid();
} else {
    loadRD();
}

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
    //$('#completeScanScreen,#asPage').hide();
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
    var stype = '<?php echo $_SESSION["searchType"]; ?>';
    if (stype === 'Service Tag' || stype === 'Host Name') {
        getOSLevelTilesMachineLvl();
    } else {
        //getOsDetails();
        $("#windowsIcon,#androidIcon,#macIcon,#iosIcon,#linuxIcon").show();
        getOSLevelTiles('windows');
    }

}
function loadEnt() {
    window.location.href = "../customer/entitlement.php";
}

//    showToolboxinteractive();
//    callHome();

function showServilogSummary() {

    var installationDate = 1234565;
    $.ajax({
        type: "GET",
        url: "supportAjaxFunction.php",
        data: "function=get_homescreenData&machine=" + glMachineName + "&installationDate=" + installationDate + "&csrfMagicToken=" + csrfMagicToken,
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

    $(".loadingStage").css({'display': 'block'});
    $.ajax({
        type: "GET",
        url: "supportAjaxFunction.php",
        data: "function=getCriticalVal&machine=" + glMachineName + "&csrfMagicToken=" + csrfMagicToken,
        success: function (msg) {

            var serSumMsg = msg.split('##');
            if (serSumMsg[0] === 1 || serSumMsg[0] === '1') {
                $('#criticalCnt').html('1 critical Issue');
            } else {
                var crVal = serSumMsg[0] + ' critical Issues';
                $('#criticalCnt').html(crVal);
            }



            if (serSumMsg[1] === 1 || serSumMsg[1] === '1') {
                $('#majorCnt').html('1 Major Issue');
            } else {
                var mrVal = serSumMsg[1] + ' Major Issues';
                $('#majorCnt').html(mrVal);
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
    $(".loadingStage").css({'display': 'block'});
    serviceLogVal = 1;
    $.ajax({
        type: "GET",
        url: "../includes/notification_ajax.php",
        data: "function=getServiceLogDetails&type=" + type + "&machine=" + glMachineName + "&installationDate=" + installationDate + "&csrfMagicToken=" + csrfMagicToken,
        success: function (msgVal) {
            $(".loadingStage").css({'display': 'none'});
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

    $('#parentSPTitle').text('View & Fix System Heal..');
    $('#parentSPTitle').attr("title", 'View & Fix System Health Issues');
    $("#mainParentSPTitle").html('View & Fix System Health Issues');
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
        data: "function=getCompleteScanList&machine=" + glMachineName + "&csrfMagicToken=" + csrfMagicToken,
        success: function (msg) {
            var serSumMsg = msg.split('##');
            $("#tilesDiv").html(serSumMsg[0]);
            var disMasg = 'Nanoheal system scan identified ' + serSumMsg[1] + ' issues <br /><br /><br /> The most important once are at the top of the list on left and are marked as.';
            $("#parentDesc").html(disMasg);
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

$("#basicTroubleshoot").click(function () {
    $("#clickList").html("");
//    var osTypeDrop = $("#osTypeDrop").text();
    var osTypeDrop = "windows";
    var getOStoLower = osTypeDrop.toLowerCase();
    getOSLevelTiles(getOStoLower);
});

//$('.aside-wrap').hide();

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
        var text = $('#scheduleGrid').text()
        if (!text) {
            selctRowConfrim("There is no data to export");
        } else {
            window.location = 'manageExportExcel.php?export=schedule';
        }
    } else {
        var text = $('#auditGrid').text()
        if (!text) {
            selctRowConfrim("There is no data to export");
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
        confirmdelete('Delete, Do you really  want to delete this Jobs?', selectedRow);
        return;
    } else {
        selctRowConfrim("Please select any Device");
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
        ajax: {
            data: {'csrfMagicToken': csrfMagicToken},
            type: 'get'
        },
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
        ajax: {data: {'csrfMagicToken': csrfMagicToken}, type: 'get'},
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

$('#notification_searchbox').keyup(function () {
    var searchText = $(this).val().toUpperCase();
    if (searchText != "") {
        searchL3Profile(searchText);
    } else {
        $("#clickList").html("");
    }
});

function searchL3Profile(searchText) {
    $.ajax({
        type: 'POST',
        url: "../communication/communication_ajax.php",
        // data: "function=profileDataList&os=" + OSDB + "&pageId=" + srchpgid + "&ossub=" + OSSUB + "&searchProfile=" + searchText + "&csrfMagicToken=" + csrfMagicToken,
        data: {'function':'profile_DataList', 'os':OSDB, 'pageId':srchpgid,'ossub':OSSUB,'csrfMagicToken':csrfMagicToken, 'searchProfile':searchText},

        success: function (data) {
            var res = $.trim(data);
            var liMsg = res.split('##');
            $("#clickList").html(liMsg[0]);
        },
        error: function (err) {
            console.log(err);
        }
    });

}
