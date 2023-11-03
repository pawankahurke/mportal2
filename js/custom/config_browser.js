$(document).ready(function() {
    var myParam = location.search.split('pageSource=')[1];
    if(myParam == 'Message_audit'){
        messageConfig();
    }
    $('#messageConfig').hide();
    $('#msg_add_configurations').hide();
    $('#msg_edit_configurations').hide();
    $('#msg_delete_configurations').hide();
    $('#msg_trigger_configurations').hide();
    $('#msg_audit_configurations').hide();
//    $('#msgconfig_grid_filter').hide();
    $('#msg_clear_configurations').hide();
    $('#back_configurations').hide();
    loadMessageConfiguration();
    $('#config-browsersel').change(function () {
        rightContainerSlideOn('configbrowshow');
    })
    
    $('#config-kiosksel').change(function () {
        rightContainerSlideOn('configkiosk');
    })
    getAllDetails();
    getSitesDetails();
    
});

$('.closebtn').click(function(){
    $('#config-browsersel').prop("checked", false);
    $('#config-kiosksel').prop("checked", false);
});

function getAllDetails(){
    var obj = {
        function  : "get_AllBrowserDetails", 'csrfMagicToken': csrfMagicToken
    }
     $.ajax({
        url: "config_function.php",
        type: "POST",
        data: obj,
        dataType: 'json',
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#config_grid').DataTable().destroy();
            configTable = $('#config_grid').DataTable({
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
                scrollY: 'calc(100vh - 240px)',
                "pagingType": "full_numbers",
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    search: "_INPUT_",
                    searchPlaceholder: "Search records",
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function(settings, json) {

                },
            });
        },
        error:function(error){
            console.log("error");
        }
    });
    
    $('#config_grid').on('click', 'tr', function() {
        var rowID = configTable.row(this).data();
        var selected = rowID[4];
        var typeselected = rowID[2];
        $("#selected").val(selected);
        $("#typeselected").val(typeselected);
        configTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        
    });
}

function getSitesDetails(){
    var obj = {
        function  : "get_SitesDetails", 'csrfMagicToken': csrfMagicToken
    }
     $.ajax({
        url: "config_function.php",
        type: "POST",
        data: obj,
        success: function (data) {
                $('#browsersitename').html('');
                $('#browsersitename').html(data);
                $(".selectpicker").selectpicker("refresh");
                
                $('#kioskidsitename').html('');
                $('#kioskidsitename').html(data);
                $(".selectpicker").selectpicker("refresh");
                
                $('#editbrowsersitename').html('');
                $('#editbrowsersitename').html(data);
                $(".selectpicker").selectpicker("refresh");
                
                $('#editkioskidsitename').html('');
                $('#editkioskidsitename').html(data);
                $(".selectpicker").selectpicker("refresh");
        },
        error: function(msg) {
            console.log(msg);
                       }
    });
}

//Add Functionalities
function addConfigBrowser(){
    var browserid = $('#browserid').val();
    var browsersitename  = $('#browsersitename').val();
    var scripStatus  = $('#scripStatus').val();
    var appdownlURL  = $('#appdownlURL').val();
    var defaultURL  = $('#defaultURL').val();
    var accessRule  = $('#accessRule').val();
    var keywordfilter  = $('#keywordfilter').val();
    var bookmarks  = $('#bookmarks').val();
    var monitorTimest  = $('#monitorTimest').val();
    var restrictFileDownl  = $('#restrictFileDownl').val();
    var disableCookies  = $('#disableCookies').val();
    var clearCache  = $('#clearCache').val();
    var clearHistory = $('#clearHistory').val();
    var disableBookmark  = $('#disableBookmark').val();
    var clearBookmarks  = $('#clearBookmarks').val();
    var disableCopyPaste  = $('#disableCopyPaste').val();
    var blockedPopUp  = $('#blockedPopUp').val();
    var disableFraudWarning  = $('#disableFraudWarning').val();
    var printPage  = $('#printPage').val();
    var schedTime  = $('#schedTime').val();
    var contentBlocking  = $('#contentBlocking').val();
    
//    Sending object in form of json
    var obj = {
        function : "submit_config_browsercheck",
        browserid :browserid ,
        browsersitename : browsersitename,
        scripStatus : scripStatus,
        appdownlURL : appdownlURL,
        defaultURL : defaultURL,
        accessRule  : accessRule,
        keywordfilter  : keywordfilter,
        bookmarks : bookmarks,
        monitorTimest : monitorTimest,
        restrictFileDownl : restrictFileDownl,
        disableCookies  : disableCookies,
        clearCache  : clearCache,
        clearHistory : clearHistory,
        disableBookmark  : disableBookmark,
        clearBookmarks : clearBookmarks,
        disableCopyPaste  : disableCopyPaste,
        blockedPopUp  : blockedPopUp,
        disableFraudWarning : disableFraudWarning,
        printPage  : printPage,
        schedTime  : schedTime,
        contentBlocking  : contentBlocking, 'csrfMagicToken': csrfMagicToken
    }
    
    $.ajax({
        url: "config_function.php",
        type: "POST",
        data: obj,
//        dataType: "json",
        success: function (data) {
            data = data.trim();
            if(data == '1' || data == 1){
                $.notify("Successful updated the details");
                rightContainerSlideClose('configbrowshow');
                setTimeout(function(){
                    location.reload();
                }, 3000);
            }else{
                $.notify("Some error occurred. Please try again.");
                rightContainerSlideClose('configbrowshow');
                setTimeout(function(){
                    location.reload();
                }, 3000);
            }
        },
        error: function (errorThrown) {
            console.log(errorThrown);
        }
    });

}

function addConfigKiosk(){
    var kioskidsitename = $('#kioskidsitename').val();
    var scripStatuskio = $('#scripStatuskio').val();
    var userlist = $('#userlist').val();
    var kioskProfiles = $('#kioskProfiles').val();
    var userConfig = $('#userConfig').val();
    var kioskconfigProfiles = $('#kioskconfigProfiles').val();
    var lockScreen = $('#lockScreen').val();
    var enableEmergency = $('#enableEmergency').val();
    var emergencyContacts = $('#emergencyContacts').val();
    
    var jsonData = {
        function : "submit_config_kioskcheck",
        kioskidsitename : kioskidsitename,
        scripStatuskio : scripStatuskio,
        userlist : userlist,
        kioskProfiles : kioskProfiles,
        userConfig : userConfig,
        kioskconfigProfiles : kioskconfigProfiles,
        lockScreen : lockScreen,
        enableEmergency : enableEmergency,
        emergencyContacts : emergencyContacts, 'csrfMagicToken': csrfMagicToken
    }
    
    $.ajax({
        url: "config_function.php",
        type: "POST",
        data: jsonData,
//        dataType: "json",
        success: function (data) {
            data = data.trim();
            if(data == '1' || data == 1){
                $.notify("Successful updated the details");
                rightContainerSlideClose('configkiosk');
                setTimeout(function(){
                    location.reload();
                }, 3000);
            }else{
                $.notify("Some error occurred. Please try again.");
                rightContainerSlideClose('configkiosk');
                setTimeout(function(){
                    location.reload();
                }, 3000);
            }
        },
        error: function (errorThrown) {
            console.log(errorThrown);
        }
    });
    
}

//Edit Functionalities
function OnEditClick(){
    var selectedValue = $('#selected').val();
    if(selectedValue === ''){
        $.notify("Please select the record you want to edit");
        closePopUp();
    }else{
        var type = $('#typeselected').val();
        var obj = {
        function  : "fetch_editdetails",
        id: selectedValue,
        type: type, 'csrfMagicToken': csrfMagicToken
        }
        if(type === 'Configuration Browser'){
            $.ajax({
                url:"config_function.php",
                type: "POST",
                data: obj,
                dataType: 'json',
                success:function(data){
                    rightContainerSlideOn('editconfigbrowshow');
                    $('#editbrowsersitename').html("");
                    $('#editbrowsersitename').html(data.sitename);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editscripStatus').val("");
                    $('#editscripStatus').val(data.scripStatus);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editappdownlURL').val(data.appdownlURL);
                    $('#editdefaultURL').val(data.defaultURL);
                    $('#editaccessRule').val(data.accessRule);
                    $('#editkeywordfilter').val(data.keywordfilter);
                    $('#editbookmarks').val(data.bookmarks);
                    $('#editmonitorTimest').val("");
                    $('#editmonitorTimest').val(data.monitorTimest);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editrestrictFileDownl').val("");
                    $('#editrestrictFileDownl').val(data.restrictFileDownl);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editdisableCookies').val("");
                    $('#editdisableCookies').val(data.disableCookies);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editclearCache').val("");
                    $('#editclearCache').val(data.clearCache);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editclearHistory').val("");
                    $('#editclearHistory').val(data.clearHistory);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editdisableBookmark').val("");
                    $('#editdisableBookmark').val(data.disableBookmark);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editclearBookmarks').val("");
                    $('#editclearBookmarks').val(data.clearBookmarks);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editdisableCopyPaste').val("");
                    $('#editdisableCopyPaste').val(data.disableCopyPaste);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editblockedPopUp').val("");
                    $('#editblockedPopUp').val(data.blockedPopUp);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editdisableFraudWarning').val("");
                    $('#editdisableFraudWarning').val(data.disableFraudWarning);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editprintPage').val("");
                    $('#editprintPage').val(data.printPage);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editschedTime').val(data.schedTime);
                    $('#editcontentBlocking').val(data.contentBlocking);
                },
            });
        }else{
            $.ajax({
                url:"config_function.php",
                type: "POST",
                data: obj,
                dataType: 'json',
                success:function(data){
                    enableFields();
                    rightContainerSlideOn('editconfigkiosk');
                    $('#editkioskidsitename').html("");
                    $('#editkioskidsitename').html(data.sitename);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editscripStatuskio').val("");
                    $('#editscripStatuskio').val(data.scripStatus);
                    $(".selectpicker").selectpicker("refresh");
                    $('#edituserlist').val("");
                    $('#edituserlist').val(data.userlist);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editkioskProfiles').val(data.kioskProfiles);
                    $('#edituserConfig').val(data.userConfig);
                    $('#editkioskconfigProfiles').val(data.kioskconfigProfiles);
                    $('#editlockScreen').val(data.lockScreen);
                    $('#editenableEmergency').val("");
                    $('#editenableEmergency').val(data.enableEmergency);
                    $(".selectpicker").selectpicker("refresh");
                    $('#editemergencyContacts').val(data.emergencyContacts);
                    }
            });
            }
    }
}

//Update Functionalities
function updateConfigKiosk(){
    var kioskid = $('#selected').val();
    var kioskidsitename = $('#editkioskidsitename').val();
    var scripStatuskio = $('#editscripStatuskio').val();
    var userlist = $('#edituserlist').val();
    var kioskProfiles = $('#editkioskProfiles').val();
    var userConfig = $('#edituserConfig').val();
    var kioskconfigProfiles = $('#editkioskconfigProfiles').val();
    var lockScreen = $('#editlockScreen').val();
    var enableEmergency = $('#editenableEmergency').val();
    var emergencyContacts = $('#editemergencyContacts').val();
    
    var obj = {
        function : "update_Config",
        type : "kiosk",
        kioskid : kioskid,
        kioskidsitename : kioskidsitename,
        scripStatuskio : scripStatuskio,
        userlist : userlist,
        kioskProfiles : kioskProfiles,
        userConfig : userConfig,
        kioskconfigProfiles : kioskconfigProfiles,
        lockScreen : lockScreen,
        enableEmergency : enableEmergency,
        emergencyContacts : emergencyContacts, 'csrfMagicToken': csrfMagicToken
    }

        $.ajax({
            url:"config_function.php",
            type: "POST",
            data: obj,
            success:function(data){
                if($.trim(data) === 'Success'){
                    $.notify("Successful updated the changes");
                    rightContainerSlideClose('configkiosk');
                    setTimeout(function(){
                        location.reload();
                    }, 3000);
                }else{
                   $.notify("Some error occurred. Please try again.");
                    rightContainerSlideClose('configkiosk');
                    setTimeout(function(){
                        location.reload();
                    }, 3000); 
                }
            },
            error:function(error){
                console.log("error");
            }
        });
}
            
function updateConfigBrowser(){
    var browserid = $('#selected').val();
    var browsersitename  = $('#editbrowsersitename').val();
    var scripStatus  = $('#editscripStatus').val();
    var appdownlURL  = $('#editappdownlURL').val();
    var defaultURL  = $('#editdefaultURL').val();
    var accessRule  = $('#editaccessRule').val();
    var keywordfilter  = $('#editkeywordfilter').val();
    var bookmarks  = $('#editbookmarks').val();
    var monitorTimest  = $('#editmonitorTimest').val();
    var restrictFileDownl  = $('#editrestrictFileDownl').val();
    var disableCookies  = $('#editdisableCookies').val();
    var clearCache  = $('#editclearCache').val();
    var clearHistory = $('#editclearHistory').val();
    var disableBookmark  = $('#editdisableBookmark').val();
    var clearBookmarks  = $('#editclearBookmarks').val();
    var disableCopyPaste  = $('#editdisableCopyPaste').val();
    var blockedPopUp  = $('#editblockedPopUp').val();
    var disableFraudWarning  = $('#editdisableFraudWarning').val();
    var printPage  = $('#editprintPage').val();
    var schedTime  = $('#editschedTime').val();
    var contentBlocking  = $('#editcontentBlocking').val();
    
//    Sending object in form of json
    var obj = {
        function : "update_Config",
        type : "browser",
        browserid :browserid ,
        browsersitename : browsersitename,
        scripStatus : scripStatus,
        appdownlURL : appdownlURL,
        defaultURL : defaultURL,
        accessRule  : accessRule,
        keywordfilter  : keywordfilter,
        bookmarks : bookmarks,
        monitorTimest : monitorTimest,
        restrictFileDownl : restrictFileDownl,
        disableCookies  : disableCookies,
        clearCache  : clearCache,
        clearHistory : clearHistory,
        disableBookmark  : disableBookmark,
        clearBookmarks : clearBookmarks,
        disableCopyPaste  : disableCopyPaste,
        blockedPopUp  : blockedPopUp,
        disableFraudWarning : disableFraudWarning,
        printPage  : printPage,
        schedTime  : schedTime,
        contentBlocking  : contentBlocking, 'csrfMagicToken': csrfMagicToken
    }
    $.ajax({
            url:"config_function.php",
            type: "POST",
            data: obj,
            success:function(data){
                if($.trim(data) === 'Success'){
                    $.notify("Successful updated the changes");
                    rightContainerSlideClose('configbrowser');
                    setTimeout(function(){
                        location.reload();
                    }, 3000);
                }else{
                    $.notify("Some error occurred. Please try again.");
                    rightContainerSlideClose('configbrowser');
                    setTimeout(function(){
                        location.reload();
                    }, 3000); 
                }
            },
            error:function(error){
                console.log("error");
            }
        });
}

function messageConfig(){
    window.location.href = "../custom/messageAudit.php";
}



