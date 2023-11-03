$(document).ready(function () {
    // getAllSitesDetails();
    getSiteConfigDetails();
});

function getSiteConfigDetails(){
    var ScopeType = $('#searchType').val();
    ScopeType = 'Sites';
    if(ScopeType == 'Sites'){
        $('#showConfigurationsTable').show();
        $('#ShowConfigMessage').html('');
        $.ajax({
            // url: '../mum/mumfunctions.php?function=GetConfigDetails&token='+token,
            url: '../mum/mumfunctions.php',
            data: {'function':"GetConfigDetails", 'csrfMagicToken':csrfMagicToken},
            type: 'POST',
            dataType: 'json',
            success: function (data){
                $('#configuredBy').html(data.configuredBy);
                $('#configuredDate').html(data.configuredOn);
                $('#SourceUpdateVal').html(data.SourceVal);
                $('#manageVal').html(data.management);
                if(data.management == 'Disable'){
                    $('#newUpdateVal').html('Not Configured');
                    $('#downUpdateVal').html('Not Configured');
                    $('#RetPolVal').html('Not Configured');
                    $('#restartPolVal').html('Not Configured');
                    $('#multipleInsVal').html('Not Configured');
                }else{
                $('#newUpdateVal').html(data.newupdates);
                $('#downUpdateVal').html(data.Download);
                $('#RetPolVal').html(data.retention);
                $('#restartPolVal').html(data.restartpolicy);
                $('#multipleInsVal').html(data.multipleinstall);
                }
            },
            error: function(error){
                console.log("no");
            }
        });
    }else{
        $('#showConfigurationsTable').hide();
        $('#ShowConfigMessage').html("Configurations can be updated at Site Level Only");
    }
}

function getAllSitesDetails(){
    var mdata = {
        'function' : 'getAllSitesDetails',
        csrfMagicToken:csrfMagicToken
    }
    $.ajax({
        url: '../mum/mumfunctions.php',
        type: 'POST',
        data: mdata,
        success: function (data) {

        },
        error: function(error){
              console.log(error);
        }
    });
}

function getDateSelection(type){
    var mdata = {
        'function' : 'getDateSelection',
        'type' : type,
        csrfMagicToken:csrfMagicToken
    }
    $.ajax({
        url: '../mum/mumfunctions.php',
        type: 'POST',
        data: mdata,
        success: function (data) {
            if(type =='showDay'){
                $('#showDay').html(data);
            }else if(type =='showDays2'){
                $('#showDays2').html(data);
            }else if(type =='showDays3'){
                $('#showDays3').html(data);
            }
        },
        error: function(error){
              console.log(error);
        }
    });
}

function getHourSelection(type){
    var mdata = {
        'function' : 'getHourSelection',
        'type' : type,
        csrfMagicToken:csrfMagicToken
    }
    $.ajax({
        url: '../mum/mumfunctions.php',
        type: 'POST',
        data: mdata,
        success: function (data) {
            if(type == 'showHour'){
                $('#showHour').html(data);
            }else{
                $('#showHour2').html(data);
            }

        },
        error: function(error){
            console.log(error);
        }
    });
}

function showConfigUpdate(){
    var selectedType = $('#searchType').val();
    getDateSelection('showDay');
    getDateSelection('showDays2');
    getDateSelection('showDays3');
    getHourSelection('showHour');
    getHourSelection('showHour2');
        rightContainerSlideOn('config-update-container');
        if($('#management5').is(':checked')){
            $('#Show_manageOptions').show();

        }else{
            $('#Show_manageOptions').hide();
        }
        getConfigUpdateDetails();
        disableFields();
}

function showUpdateMethod(){
    var selected = $('#selected_siteID').val();
        rightContainerSlideOn('settings-add-container');
        disableFields();

        if($('.automaticupdate').is(':checked')){
            $('#schOption2').show();
        }else{
            $('#schOption2').hide();
        }
}

function showMachineSettings(){
    rightContainerSlideOn('machine-setting-container');
    disableFields();
}

function showconfigUpload(){
   rightContainerSlideOn('config-upload-container');
   disableFields();
}

function showScheduleOptions(){
    if($('#schOption').hasClass('active')){
        $('#tablesExamplesshedule').hide();
        $('#tablesExamplesnotification').hide();
        $('#schOption').removeClass('active');
    }else{
        $('#tablesExamplesshedule').show();
        $('#tablesExamplesnotification').hide();
        $('#schOption').addClass('active');
    }
}

function showNotifOptions(){
    if($('#notiOption').hasClass('active')){
        $('#tablesExamplesshedule').hide();
        $('#tablesExamplesnotification').hide();
        $('#notiOption').removeClass('active');
    }else{
        $('#tablesExamplesshedule').hide();
        $('#tablesExamplesnotification').show();
        $('#notiOption').addClass('active');
    }
}

$('.manualupdate').click(function(){
   $('#schOption2').hide();
});

$('.automaticupdate').click(function(){
    $('#schOption2').show();
})

$('#management5').click(function(){
    $('#Show_manageOptions').show();
    $('#management1').prop('checked',false);
    $('#management2').prop('checked',false);
    $('#management3').prop('checked',false);
    $('#management4').prop('checked',false);
    $('#management5').prop('checked',true);
    $('#Show_manageOptions').show();
    $('#newupdate1').prop('checked',true);
    $('#newupdate2').prop('checked',false);
    $('#downUpdate1').prop('checked',false);
    $('#downUpdate2').prop('checked',false);
    $('#downUpdate3').prop('checked',true);
    $('#retenSel1').prop('checked',false);
    $('#retenSel2').prop('checked',true);
    $('#RestartSel1').prop('checked',false);
    $('#RestartSel2').prop('checked',true);
    $('#multipleInstall1').prop('checked',true);
    $('#multipleInstall2').prop('checked',false);
    $('#multipleInstall3').prop('checked',false);
});

$('#management1,#management2,#management3,#management4').click(function(){
    $('#Show_manageOptions').hide();
});

$('.ConfigUpdateSubmit').click(function(){
    $('#loader').show();
    var mgroupid = $('#selected_siteID').val();
    var postFormData = $("#configurePatch2").serialize();
    postFormData += "&function=configureMUM&mgroupid=" + mgroupid;
    $.ajax({
        url: '../mum/mumfunctions.php',
        type: 'POST',
        data: postFormData,
        success: function (data) {
            $('.loader').hide();
            $.notify("Configurations Updated Successfully");
            rightContainerSlideClose('config-update-container');
            setTimeout(function(){
                // location.reload();
                get_defaultconfiguration();
                mum_firstschceduleset();
                getSiteConfigDetails();
                },3000);
            }
        });
});

function getConfigUpdateDetails(){
    //var selected = $('#').val();
    var mdata = {
        'function' : 'getConfigUpdateDetails',
        csrfMagicToken: csrfMagicToken
      //  'selected' : selected,
    };
    $.ajax({
        url: '../mum/mumfunctions.php',
        type: 'POST',
        data: mdata,
        dataType: 'json',
        success: function (data) {
            console.log(data);
            var sourceUpdate = data.patchsource;
            if(sourceUpdate == '2'){
                $('#sussel').prop('checked',true);
                $('#susselurl').val();
                $('#mumsel').prop('checked',false);
            }else{
                $('#sussel').prop('checked',false);
                $('#susselurl').val('');
                $('#mumsel').prop('checked',true);
            }

            var manageSel = data.management;
            if(manageSel == '1'){
                $('#management1').prop('checked',true);
                $('#management2').prop('checked',false);
                $('#management3').prop('checked',false);
                $('#management4').prop('checked',false);
                $('#management5').prop('checked',false);
            }else if(manageSel == '3'){
                $('#management1').prop('checked',false);
                $('#management2').prop('checked',true);
                $('#management3').prop('checked',false);
                $('#management4').prop('checked',false);
                $('#management5').prop('checked',false);
            }else if(manageSel == '4'){
                $('#management1').prop('checked',false);
                $('#management2').prop('checked',false);
                $('#management3').prop('checked',true);
                $('#management4').prop('checked',false);
                $('#management5').prop('checked',false);
                $('#showDay').html(data.installday);
                $('#showHour').html(data.installhour);
            }else if(manageSel == '5'){
                $('#management1').prop('checked',false);
                $('#management2').prop('checked',false);
                $('#management3').prop('checked',false);
                $('#management4').prop('checked',true);
                $('#management5').prop('checked',false);
            }else if(manageSel == '2'){
                $('#management1').prop('checked',false);
                $('#management2').prop('checked',false);
                $('#management3').prop('checked',false);
                $('#management4').prop('checked',false);
                $('#management5').prop('checked',true);
                $('#Show_manageOptions').show();
                if(data.newpatches == '1'){
                    $('#newupdate1').prop('checked',true);
                    $('#newupdate2').prop('checked',false);
                }else{
                    $('#newupdate1').prop('checked',false);
                    $('#newupdate2').prop('checked',true);
                }

                if(data.propagate == '0'){
                    $('#downUpdate1').prop('checked',true);
                    $('#downUpdate2').prop('checked',false);
                    $('#downUpdate3').prop('checked',false);
                }else if(data.propagate == '1'){
                    $('#downUpdate1').prop('checked',false);
                    $('#downUpdate2').prop('checked',true);
                    $('#downUpdate3').prop('checked',false);
                }else if(data.propagate == '2'){
                    $('#downUpdate1').prop('checked',false);
                    $('#downUpdate2').prop('checked',true);
                    $('#downUpdate3').prop('checked',false);
                }

                if(data.updatecache == '1'){
                    $('#retenSel1').prop('checked',true);
                    $('#retenSel2').prop('checked',false);
                }else{
                    $('#retenSel1').prop('checked',false);
                    $('#retenSel2').prop('checked',true);
                    $('#showDays2').html(data.cacheseconds);
                }

                if(data.restart == '1'){
                    $('#RestartSel1').prop('checked',true);
                    $('#RestartSel2').prop('checked',false);
                }else{
                    $('#RestartSel1').prop('checked',false);
                    $('#RestartSel2').prop('checked',true);
                }

                if(data.chain == '1'){
                    $('#multipleInstall1').prop('checked',true);
                    $('#multipleInstall2').prop('checked',false);
                    $('#multipleInstall3').prop('checked',false);
                    $('#showHour2').html(data.chainseconds);
                }else if(data.chain == '2'){
                    $('#multipleInstall1').prop('checked',false);
                    $('#multipleInstall2').prop('checked',true);
                    $('#multipleInstall3').prop('checked',false);
                }else if(data.chain == '3'){
                    $('#multipleInstall1').prop('checked',false);
                    $('#multipleInstall2').prop('checked',false);
                    $('#multipleInstall3').prop('checked',true);
                }
            }
        }
    });
}

$('.UpdateMethodSubmit').click(function(){
    var selected = $('#selected_siteID').val();
    if(selected == ''){
        $.notify("Please select a record");
    }else{
        updatePatchmethod('selectManualType');
    }
});

$('#MachSettingSubmit').click(function(){
    var selected = $('#selected_siteID').val();
    if(selected == ''){
        $.notify("Please select a record");
    }else{
        updatePatchmethod('updateManualType');
    }
});

/*function updatePatchmethod(type) {
    var updatemethod;
    updatemethod = $("input[name='updatemethod']:checked").val();
    var mgroupid = $('#selected_siteID').val();

    if(updatemethod == '4'){
        var scheduledelay;
        var sdelayoper;
        var scheduleaction;
        var schedlemin;
        var schedlehour;
        var schedlweek;
        var schedlemon;
        var schedleday;
        schedlemin = $(".schlemin2 option:selected").val();
        schedlehour = $(".schlehour2 option:selected").val();
        schedlweek = $(".schleweek2 option:selected").val();
        schedlemon = $(".schedlemon2 option:selected").val();
        schedleday = $(".schedleday2 option:selected").val();
        scheduledelay = $(".schlerandomdelay2").val();
        sdelayoper = $(".schleedelay2").val();
        scheduleaction = $("input[name='schleradio2']:checked").val();

        var mdata = {'function':'mum_updatemethodValue',
            'method':updatemethod,
            'sdelayoper':sdelayoper,
            'schedlemin':schedlemin,
            'schedlehour':schedlehour,
            'schedday':schedleday,
            'scheduleweek':schedlweek,
            'shedmonth':schedlemon,
            'scheduledelay':scheduledelay,
            'scheduleaction':scheduleaction,
            'mgroupid':mgroupid,
            'pgroupid':'1',
            'type': 'autoUpdateType'
            };
    }else{
        if(type == 'selectManualType'){
            var mdata = {'function':'mum_updatemethodValue',
                'method':updatemethod,
                'mgroupid':mgroupid,
                'pgroupid':'1',
                'type' : type,
                'csrfMagicToken': csrfMagicToken
            };
        }else if(type == 'updateManualType'){
            var notifhour;
            var notifmin;
            var notifweek;
            var notifmon;
            var notifday;
            var notifymins;
            var notifremnd;
            var notifprevsys;
            var notifschdlsop;
            var notif_text;
            var notirdelay;
            var notiaction;
            var scheduledelay;
            var sdelayoper;
            var scheduleaction;
            var schedlemin;
            var schedlehour;
            var schedlweek;
            var schedlemon;
            var schedleday;
            schedlemin = $(".schlemin option:selected").val();
            schedlehour = $(".schlehour option:selected").val();
            schedlweek = $(".schleweek option:selected").val();
            schedlemon = $(".schedlemon option:selected").val();
            schedleday = $(".schedleday option:selected").val();
            scheduledelay = $(".schlerandomdelay").val();
            sdelayoper = $(".schleedelay").val();
            scheduleaction = $("input[name='schleradio']:checked").val();
            notifhour = $(".notifhour option:selected").val();
            notifmin = $(".notifmin option:selected").val();
            notifweek = $(".notifwkly option:selected").val();
            notifmon = $(".notifmon option:selected").val();
            notifday = $(".notifday option:selected").val();
            notirdelay = $(".notifrandomdelay").val();
            notif_text = $("#notif_text").val();
            notiaction = $("input[name='notifradio']:checked").val();

            if ($(".notifymins").prop('checked') == true) {
                notifymins = $(".notinumb").val();

            } else {
                notifymins = "0";
            }
            if ($(".notifremnd").prop('checked') == true) {
                notifremnd = $(".notifremnd").val();
            } else {
                notifremnd = "0";
            }
            if ($(".notifprevsys").prop('checked') == true) {
                notifprevsys = $(".notifprevsys").val();
            } else {
                notifprevsys = "0";
            }
            if ($(".notifschdlsop").prop('checked') == true || $(".notifschdlsopappr").prop('checked') == true) {
                notifschdlsop = $(".notifschdlsop").val();
                notifschdlsop = $('.notifschdlsopappr').val();
            } else {
                notifschdlsop = "0";
                notifschdlsop = "0";
            }

            var mdata = {'function':'mum_updatemethodValue',
            'method':updatemethod,
            'sdelayoper':sdelayoper,
            'schedlemin':schedlemin,
            'schedlehour':schedlehour,
            'schedday':schedleday,
            'scheduleweek':schedlweek,
            'shedmonth':schedlemon,
            'scheduledelay':scheduledelay,
            'scheduleaction':scheduleaction,
            'notifmin':notifmin,
            'notifhour':notifhour,
            'notiweek':notifweek,
            'notifmon':notifmon,
            'notifday':notifday,
            'notirdelay':notirdelay,
            'notiaction':notiaction,
            'notif_text':notif_text ,
            'notiopt':notifymins,
            'notiremind':notifremnd,
            'notiprev':notifprevsys,
            'notisched':notifschdlsop,
            'mgroupid':mgroupid,
            'type' : type,
                'csrfMagicToken': csrfMagicToken
            };
        }

    }

    $.ajax({
        url: '../mum/mumfunctions.php',
        type: "POST",
        data: mdata ,
        dataType: "json",
        success: function (data) {
            if(data.msg == "success"){

            }else if(data.msg == "failed"){

            }
        }
    });

}*/


