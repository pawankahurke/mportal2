
var selectedPlatform, 
    platformClassName='swd-ic-it', 
    swdUIContainerIdName = 'widget-container', 
    fetchDetailsInProgress = false,
    wizardMode,
    maxSegments = 5,
    debugMode = false, 
    formProgress = false , 
    configureExecute = false, 
    pushProgress = false, 
    selectedConfigurationType;

$(document).ready(function(){
    
    var sui = new SwdUI('#'+window.swdUIContainerIdName);
    sui.fetchList();
    
    $("[data-toggle=tooltip").tooltip();
    $("[name=global]").bootstrapSwitch({'onText' : 'Yes','offText' : 'No'});
    $(".resume-download-switch,.propagation-switch,[name=delete-log-file],[name=status-message-box]").bootstrapSwitch({'onText' : 'No','offText' : 'Yes'});
    
    $(sui.container).on('click', '.wt-hand', function (e) {
        var target = $('#'+$(this).attr('data-bs-target')), 
            group = target.parents('.wt-group'), 
            wraps = group.find('.wt-wrap'), 
            value = $(this).attr('data-value');
        
        $.each(wraps, function(){
            wraps.addClass(sui.hide);
        });
        
        target.removeClass(sui.hide);
        
        var input = group.find('input.wt-value');
        
        if(input!=undefined && input.length > 0 && value!=undefined){
            input.val(value);
        }
    });
    
    $('#add-swd').on('click', function(){
        window.wizardMode = 'create'
        sui.progressLoaderHide();
        sui.toPlatform();
        $('#swd-wrapper').hide();
        $('#widget-container').show();
    });
    
    $('#add-more-section').on('click', function(){
        sui.addMoreEvent();
        sui.activateWrapPopulation();
    });
    
    $('#remove-last-section').on('click', function(){
        
        if(sui.getSegmentsCount()>1){
            sui.removeLastSectionEvent();
            sui.activateWrapPopulation();
        }
        
        if(sui.getSegmentsCount()<=1){
            $(this).addClass(sui.hide);
        }
    });
    
    
    $('.swd-ic-it').click('on', function(){
        if(window.wizardMode=='create')
        {
            var group = $(this).parents('.swd-ic-it-group'), th = $(this), groupInput; 
        
            if(group!=undefined && group.length>0){
                $.each(group.find('.swd-ic-it'), function(){
                    $(this).removeClass('active');
                });
                th.addClass('active');
                groupInput = group.find('.swd-ic-it-input');

                if(groupInput!=undefined && groupInput.length > 0){
                    if(th.attr('data-indentifier-key') != undefined && th.attr('data-'+th.attr('data-indentifier-key')) !=undefined){
                        groupInput.val(th.attr('data-'+th.attr('data-indentifier-key')));
                    }
                }
            }

            sui.activateWrapPopulation();
            sui.resetWizardMenu();
        }
    });
    
    $('.'+sui.platformHandClassName).on('click', function(){
        if(window.wizardMode=='create')
        {
            var sP = $(this).attr('data-platform');
            $(this).parents('.platform-row').eq(0).find('.swd-ic-it-input').val(sP);
            if(sP!=undefined && sP!=''){
                window.selectedPlatform = sP;
                sui.initCreate(sP);
            }
        }
    });
    
//    $('.'+sui.sourceTypeHandClassName).on('click', function(){
//        var sourceType = $(this).attr('data-type'), value = $(this).attr('data-value');
//        if(sourceType!=undefined && sourceType!='' && value!=undefined && value!='' && !isNaN(value)){
//            sui.toggleSourceTypeOptions(sourceType, value);
//        }
//    });
    
    $('.'+sui.accessFieldName).on('click', function(){
        if($(this).is(':checked')){
            var accessValue = $(this).val();
            if(accessValue!=undefined && accessValue!=''){
                sui.toggleAccessOptions(accessValue);
            }
        }
    });
    
    $('#'+sui.nextButtonId).on('click', function(){
        sui.next();
    });
    
    $('#'+sui.previousButtonId).on('click', function(){
        sui.previous();
    });
    
    $('#'+sui.changePlatformButtonId).on('click', function(){
        sui.toPlatform();
    });
    
    $(sui.container).on('click', '.'+sui.sideNavClassName+'.'+sui.wizardMenuClickableClassName, function (e) {
        var target = $(this).attr('data-bs-target');
        if(target!=undefined && target!=''){
            sui.moveWizardItem(target);
        }
        
         var index = $('.'+sui.sideNavClassName).index($(this)),
             totalSideItems = $('.'+sui.sideNavClassName).length;
         
         $('#'+sui.saveButtonId).addClass(sui.hide);
         
         if(index==0){
             $('#'+sui.previousButtonId).addClass(sui.hide);
         } else {
             $('#'+sui.previousButtonId).removeClass(sui.hide);
         }
         
         if(index==(totalSideItems - 1)){ 
             $('#'+sui.nextButtonId).addClass(sui.hide);
             $('#'+sui.saveButtonId).removeClass(sui.hide);
         } else {
             $('#'+sui.nextButtonId).removeClass(sui.hide);
         }
         
    });
    
    $('#'+sui.closeWidgetButtonId).on('click', function(){
        sui.closeWidget();
        $('#swd-wrapper').show();
    });
    
    $('#swd-list-dtable').on('dblclick', 'tbody tr',function(){
        activateRow($(this));
        var type, dS = getActiveRowDistributeConfigureStatus(), eS = getActiveRowExecuteConfigureStatus();
        
        if((dS && eS) || (dS && !eS)){
            type = 'distribute';
        } else if(eS && !dS){
            type = 'execute';
        }
        
        var id = $(this).find('input[name=dt-package-id]').val();
        
        if(id!=undefined && !isNaN(id)){
            sui.progressLoaderHide();
            sui.editEvent($(this).find('input[name=dt-package-id]').val(), 'initEditUI', type);
        }
    });
    
    $('#edit-distribute-hand').on('click' ,function()
    {
        var row = $('#swd-list-dtable tbody tr.active');
        sui.editEvent(row.find('input[name=dt-package-id]').val(), 'initEditUI', 'distribute');
    });
    
    $('#edit-execute-hand').on('click' ,function()
    {
        var row = $('#swd-list-dtable tbody tr.active');
        sui.editEvent(row.find('input[name=dt-package-id]').val(), 'initEditUI', 'execute');
    });
    
    $('#configure-execute-hand').on('click', function(){
        var row = $('#swd-list-dtable tbody tr.active');
        var dS = getActiveRowDistributeConfigureStatus(), eS = getActiveRowExecuteConfigureStatus();
        
        if(dS && !eS){
            sui.editEvent(row.find('input[name=dt-package-id]').val(), 'initConfigureExecuteUI');
            return true;
        }
        
        errorNotify("Something went wrong");
    });
    
    $('#rsn-distibute-execute-hand').on('click', function()
    {
        var row = $('#swd-list-dtable tbody tr.active'), activeId = row.find('input[name=dt-package-id]').val();
        
        if(activeId==undefined){
            errorNotify("Please select a package first");
            return false;
        }
        
        $('#config-readonly').html('');
        
        var field = $('#rsn-distibute-execute input[name=ck-conf-hand]');
        field.eq(0).prop('checked', true).removeAttr('disabled');
        field.eq(0).parents('.form-check').removeClass('disabled');
        field.eq(1).prop('checked', true).removeAttr('disabled');
        field.eq(1).parents('.form-check').removeClass('disabled');
        
        var dS = getActiveRowDistributeConfigureStatus(), 
            eS = getActiveRowExecuteConfigureStatus();
        
        if(!dS){
            field.eq(0).prop('checked', false).attr('disabled', 'disabled');
            field.eq(0).parents('.form-check').addClass('disabled');
        }
        
        if(!eS){
            field.eq(1).prop('checked', false).attr('disabled', 'disabled');
            field.eq(1).parents('.form-check').addClass('disabled');
        }
        
        sui.requestConfiguration(activeId, false, 'sliderUI');   
        rightContainerSlideOn('rsn-distibute-execute');
    });
    
    $('#rsn-distibute-execute input[name=ck-conf-hand]').on('click', function()
    {
        var field = $('#rsn-distibute-execute input[name=ck-conf-hand]');
        var fill = '';
        var anyChecked = true;
        
        if(!field.eq(0).is(':checked') && !field.eq(1).is(':checked')){
            errorNotify("Please either distribute/execute");
            $('#config-readonly').html('');
            anyChecked = false;
        }
        
        if(field.eq(0).is(':checked') && !field.eq(1).is(':checked')){
            fill = 'distribute';
        } else if(!field.eq(0).is(':checked') && field.eq(1).is(':checked')){
            fill = 'execute';
        }
        
        $('form[name=configuration-form] input[name=type]').val(fill);
        
        var row = $('#swd-list-dtable tbody tr.active'), activeId = row.find('input[name=dt-package-id]').val();
        
        if(activeId==undefined){
            errorNotify("Please select a package first");
            return false;
        }
        
        if(anyChecked){
            sui.requestConfiguration(activeId, false, 'sliderUI'); 
        }
    });
    
    $('.indistro-sitegroup-selection').click(function(){
        rightContainerSlideClose('rsn-distibute-execute');
        $('input[name=jsCallback]').val('reopenDistroSlider');
        rightContainerSlideOn('rsc-add-container3');
    });
    
    $('#push-configuration').on('click', function(){
        $('form[name=push-configuration-form]').submit();
    });
    
    $(document).on('keyup', 'input[data-required=true]', function (event) {
        sui.directValidation($(this), event); 
    });

    $(document).on('change', 'select[data-required=true]', function (event) {
        sui.directValidation($(this), event);
    });
    
});


function pushConfigurationEvent(form , event)
{
    var row = $('#swd-list-dtable tbody tr.active'), activeId = row.find('input[name=dt-package-id]').val();
    var sui = new SwdUI('#'+window.swdUIContainerIdName);
    return sui.pushConfiguration(form , event, activeId);
}

function getActiveRowDistributeConfigureStatus()
{
    var row = $('#swd-list-dtable tbody tr.active'), v = row.find('input[name=has_distribution]').val();
    return (v!=undefined && !isNaN(v) && parseInt(v) == 1);
}

function getActiveRowExecuteConfigureStatus()
{
    var row = $('#swd-list-dtable tbody tr.active'), v = row.find('input[name=has_execution]').val();
    return (v!=undefined && !isNaN(v) && parseInt(v) == 1);
}

function saveCredentials(form, event)
{
    var swdUi = new SwdUI(null);
    return swdUi.saveCredentials(form, event);
}

$(document).ready(function()
{
    $('#swd-list-dtable').on('click', 'tbody tr',function()
    {
        activateRow($(this));
        
        var configureExecuteHand = $('#configure-execute-hand'), 
            editDistributeHand = $('#edit-distribute-hand'),
            editExecuteHand = $('#edit-execute-hand'),
            dS = getActiveRowDistributeConfigureStatus(), eS = getActiveRowExecuteConfigureStatus();
        
        try{
            if(dS && !eS){
                configureExecuteHand.show();
            } else{
                configureExecuteHand.hide();
            }
            
            if(dS){
                editDistributeHand.show();
            } else {
                editDistributeHand.hide();
            }
            
            if(eS){
                editExecuteHand.show();
            } else {
                editExecuteHand.hide();
            }
            
        } catch(e){}
        
    });
    
    $('#rsc-ftp-cdn-configuration-hand').on('click', function()
    {
        window.selectedConfigurationType = 'cdn';
        var swdUi = new SwdUI(null);
        return swdUi.editCredentialsUI(true);
    });
    
    $('#fauth').on('click', function(){
        if($(this).is(':checked')){
            $('.ftpauthField').show();
        } else {
            $('.ftpauthField').hide();
        }
    });
    
    $('[name=cdn-ftp-config-type]').bootstrapSwitch(
    {
        'onText' : 'CDN',
        'offText' : 'FTP', 
        'onSwitchChange' : function(event, state){
            var swdUi = new SwdUI(null);
            if(state){
                //cdn
                $('#cdn-config-wrap').show();
                $('#ftp-config-wrap').hide();
                window.selectedConfigurationType = 'cdn';
                swdUi.editCredentialsUI();
            } else {
                //ftp
                $('#ftp-config-wrap').show();
                $('#cdn-config-wrap').hide();
                window.selectedConfigurationType = 'ftp';
                swdUi.editCredentialsUI();
            }
        }
    });
    
    
    $('#save-credentials-event-hand').on('click', function()
    {
        var f;
        
        if(window.selectedConfigurationType!=undefined){
            switch(window.selectedConfigurationType){
                case 'cdn': 
                    f = $('form[name=cdn-configure]');
                break;
                case 'ftp': 
                    f = $('form[name=ftp-configure]');
                break;
            }
        }
        
        if(f!=undefined) f.submit();
    });
    
});

function activateRow(target)
{
    $.each($('#swd-list-dtable tbody tr'), function(){
        $(this).removeClass('active');
    });
    target.addClass('active');
    
    return true;
}

function processForm(event, form)
{
    var swd = new SwdUI('#'+window.swdUIContainerIdName);
    
    if(event.preventDefault){
        event.preventDefault(); 
    } else {
        event.returnValue = false;
    }
    
    if(swd.validateFinish()){
        return swd.savePackage(event, form); 
    }
   
}

function SwdUI(container){
    this.container = container;
    this.sideNavClassName = 'side-nav-it';
    this.sideBarGridClone;
    this.platform;
    this.sideMenuContainerIdName = 'dispMenu';
    this.sourceTypeHandClassName = 'swd-st';
    this.typeHandClassName = 'swd-tp';
    this.hide = 'swd-hide';
    this.defaultHide = 'default-hide';
    this.wizardMenuClickableClassName = 'cbl';
    this.accessFieldName = 'access';
    this.accessOptions = ['secure','anonymous'];
    this.changePlatformButtonId = 'change-platform-btn';
    this.nextButtonId = 'swd-next-btn';
    this.previousButtonId = 'swd-prev-btn';
    this.saveButtonId = 'swd-save-btn';
    this.platformWrapIdName = 'profile';
    this.platformHandClassName = 'swd-plt';
    this.closeWidgetButtonId = 'close-swd-widget';
    this.formName = 'save-package-form';
    this.packageIdFieldName = 'package-id';
    
    this.configurations = {
        'windows' : {
            'start' : 'Start',
            'source' : 'Source',
            'pre-check' : 'Pre Check',
            'finish' :  'Finish'
        },
        'linux' : {
            'start' : 'Start',
            'source' : 'Source',
            'pre-check' : 'Pre Check',
            'finish' :  'Finish'
        },
        'mac' : {
            'start' : 'Start',
            'source' : 'Source',
            'pre-check' : 'Pre Check',
            'finish' :  'Finish'
        },
        'android' : {
            'start' : 'Start',
            'source' : 'Source',
            'pre-check' : 'Pre Check',
            'finish' :  'Finish'
        },
        'ios' : {
            'start' : 'Start',
            'source' : 'Source',
            'pre-check' : 'Pre Check',
            'finish' :  'Finish'
        }
    };
    
    //@dep
    this.platformSourceTypes = {
        'windows' : ['nanoheal-repository','vendor-repository'],
        'linux' : ['nanoheal-repository','vendor-repository'],
        'mac' : ['nanoheal-repository','vendor-repository'],
        'android' : ['google-play-store','nanoheal-play-store','nanoheal-repository','vendor-repository'],
        'ios' : ['apple-play-store','nanoheal-play-store','nanoheal-repository','vendor-repository']
    };
    
    this.wrapClassName = 'eachPWrap';
    this.wrapActiveClassName = 'pActive';
    this.menuWizardItemActiveClassName = 'active';
    
    //@dep
    this.sourceTypes = {
        'shared-folder' : 1,
        'google-play-store' : 4,
        'apple-play-store' : 5,
        'nanoheal-play-store' : 5,
        'nanoheal-repository' : 2,
        'vendor-repository' : 3
    };
    
    
    this.gridValidationFunctions = {
        'windows' : ['validateStart','validateSource','validatePrecheck','validateFinish'],
        'linux' : ['validateStart','validateSource','validatePrecheck','validateFinish'],
        'mac' : ['validateStart','validateSource','validatePrecheck','validateFinish'],
        'android' : ['validateStart','validateSource','validatePrecheck','validateFinish'],
        'ios' : ['validateStart','validateSource','validatePrecheck','validateFinish'],
    };
    
    this.gridPopulateFunctions = {
        'windows' : ['populateSource','populatePreCheck','populateFinish'],
        'linux' : ['populateSource','populatePreCheck','populateFinish'],
        'mac' : ['populateSource','populatePreCheck','populateFinish'],
        'android' : ['populateSource','populatePreCheck','populateFinish'],
        'ios' : ['populateSource','populatePreCheck','populateFinish'],
    };
    
    this.visibleFieldFilters = {
        'windows' : {
            'start' : ['.windows-grid'],
            'source' : {
               'distribute' : ['.well','.my-btn-group','.upload-to','.qq-up-wrap-parent','.distribution-path-wrap','.source-upload','.source-url','.post-validation-wrap'],
               'execute' : ['.well','.my-btn-group','.upload-to','.qq-up-wrap-parent','.exe-path','.source-upload','.source-url','.source-path','.command-line-wrap'],
            },
            'pre-check' : ['.well','.my-btn-group','.wt-hand','.pc-btg-h-reg','.registry-precheck-wrap'],
            'finish' :  {
                'distribute' : ['#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap'],
                'execute' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap', '#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap','.api-mode-wrap','.session-wrap'],
                'both' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap','#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap']
            }
        },
        'linux' : {
            'start' : ['.windows-grid'],
            'source' : {
               'distribute' : ['.well','.my-btn-group','.upload-to','.qq-up-wrap-parent','.distribution-path-wrap','.source-upload','.source-url','.post-validation-wrap'],
               'execute' : ['.well','.my-btn-group','.upload-to','.qq-up-wrap-parent','.exe-path','.source-upload','.source-url','.source-path','.command-line-wrap'],
            },
            'pre-check' : ['.well','.my-btn-group','.pc-btg-h-file','.pc-btg-h-sn','.file-precheck-wrap'],
            'finish' :  {
                'distribute' : ['#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap'],
                'execute' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap', '#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap','.api-mode-wrap','.session-wrap'],
                'both' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap','#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap']
            }
        },
        'mac' : {
            'start' : ['.windows-grid'],
            'source' : {
               'distribute' : ['.well','.my-btn-group','.upload-to','.qq-up-wrap-parent','.distribution-path-wrap','.source-upload','.source-url','.post-validation-wrap'],
               'execute' : ['.well','.my-btn-group','.upload-to','.qq-up-wrap-parent','.exe-path','.source-upload','.source-url','.source-path','.command-line-wrap'],
            },
            'pre-check' : ['.well','.my-btn-group','.pc-btg-h-file','.pc-btg-h-sn','.file-precheck-wrap'],
            'finish' :  {
                'distribute' : ['#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap'],
                'execute' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap', '#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap','.api-mode-wrap','.session-wrap'],
                'both' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap','#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap']
            }
        },
        'android' : {
            'start' : ['.windows-grid'],
            'source' : {
               'distribute' : ['.well','.my-btn-group','.upload-to','.qq-up-wrap-parent','.distribution-path-wrap','.source-upload','.source-url','.post-validation-wrap'],
               'execute' : ['.well','.my-btn-group','.upload-to','.qq-up-wrap-parent','.exe-path','.source-upload','.source-url','.source-path','.command-line-wrap'],
            },
            'pre-check' : ['.well','.my-btn-group','.pc-btg-h-file','.pc-btg-h-sn','.file-precheck-wrap'],
            'finish' :  {
                'distribute' : ['#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap'],
                'execute' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap', '#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap','.api-mode-wrap','.session-wrap'],
                'both' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap','#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap']
            }
        },
        'ios' : {
            'start' : ['.windows-grid'],
            'source' : {
               'distribute' : ['.well','.my-btn-group','.upload-to','.qq-up-wrap-parent','.distribution-path-wrap','.source-upload','.source-url','.post-validation-wrap'],
               'execute' : ['.well','.my-btn-group','.upload-to','.qq-up-wrap-parent','.exe-path','.source-upload','.source-url','.source-path','.command-line-wrap'],
            },
            'pre-check' : ['.well','.my-btn-group','.pc-btg-h-file','.pc-btg-h-sn','.file-precheck-wrap'],
            'finish' :  {
                'distribute' : ['#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap'],
                'execute' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap', '#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap','.api-mode-wrap','.session-wrap'],
                'both' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap','#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap']
            }
        }
    };   
    
}

SwdUI.prototype.init = function(container, platform)
{
    this.plaform = platform;
    this.container = container;
}

SwdUI.prototype.open = function()
{
    $('#swd-wrapper').hide();
    $('#widget-container').show();
    return true;
}

SwdUI.prototype.close = function()
{
    $('#widget-container').hide();
    $('#swd-wrapper').show();
    return true;
}

SwdUI.prototype.initCreate = function(platform)
{
    this.loadWizard(platform);
    this.loadWizardMenu(platform);
    this.showSegmentControlBox();
    this.disableReadOnly();
    this.removePackageIdField();
    window.configureExecute = false;
    return true;
}

SwdUI.prototype.loadWizardMenu = function(platform)
{
    this.platform = platform;
    var sbc = this.getSideBarGridClone();
    var psnd = this.configurations,
        snd = psnd[this.platform],
        sideItemHtm = '',
        allPlatforms = Object.keys(psnd);
    
    if(allPlatforms!=undefined)
    {
        var c = 0, ac;
        for(var i in snd){
            c++;
            sbc.find('p').text(c);
            sbc.find('span').text(snd[i]);
            ac = (c==1) ? ' active cbl' : '';
            sideItemHtm += '<div class="col-md-12 addBox side-nav-it'+ac+'" data-disp="'+c+'" data-target="'+i+'">'+sbc.html()+'</div>';
        }
        
        $('#'+this.sideMenuContainerIdName).html(sideItemHtm);
    }
    
    return true;
}

SwdUI.prototype.loadWizard = function(platform)
{
    this.platform = platform;
    var psnd = this.configurations,
        snd = psnd[this.platform],
        allPlatforms = Object.keys(psnd);
    
    this.resetWizardWrapsVisibiity();
    
    switch(platform){
        case allPlatforms[0]:
            this.loadWindowsWizard();
        break;
        case allPlatforms[1]:
            this.loadLinuxWizard();
        break;
        case allPlatforms[2]:
            this.loadMacWizard();
        break;
        
        case allPlatforms[3]:
            this.loadAndroidWizard();
        break;
        case allPlatforms[4]:
            this.loadIosWizard();
        break;
    }
    
    $('#'+this.changePlatformButtonId).removeClass(this.hide);
    $('#'+this.nextButtonId).removeClass(this.hide);
    $('form[name='+this.formName+']').trigger('reset');
    
    return true;
}

SwdUI.prototype.resetScIc = function(wrap)
{
    var scIcGroups = ((wrap==undefined) ? $(this.container).find('.swd-ic-it-group') : wrap), scIcItems, x;
    
    for(var i=0;i<scIcGroups.length;i++){
        scIcGroups.eq(i).find('.swd-ic-it-input').val('');
        scIcItems = scIcGroups.eq(i).find('.swd-ic-it');
        for(x=0;x<scIcItems.length;x++){
            scIcItems.eq(x).removeClass('active');
        }
    }
    
    return true;
}

SwdUI.prototype.loadWindowsWizard = function()
{
    var wraps = $('.'+this.wrapClassName);
    for(var i=0;i<wraps.length;i++){
        wraps.eq(i).addClass('swd-hide');
    }
    
    this.hidePlatformWrap();
    $('.windows-grid').removeClass(this.hide);
    wraps.eq(0).removeClass(this.hide).addClass(this.wrapActiveClassName);
    this.resetScIc();
    
    return true;
}

SwdUI.prototype.loadLinuxWizard = function()
{
    this.loadWindowsWizard();
    return true;
}

SwdUI.prototype.loadMacWizard = function()
{
    this.loadWindowsWizard();
    return true;
}

SwdUI.prototype.loadAndroidWizard = function()
{
    this.loadWindowsWizard();
    return true;
}

SwdUI.prototype.loadIosWizard = function()
{
    this.loadWindowsWizard();
    return true;
}


SwdUI.prototype.hidePlatformWrap = function(){
    $('#'+this.platformWrapIdName).addClass('swd-hide');
    return true;
}

SwdUI.prototype.loadSourceTypes = function(platform)
{ // @dep
    var st = $('.'+this.sourceTypeHandClassName),stTh;
    
    for(var i=0;i<=st.length;i++){
        stTh = st.eq(i);
        if(!stTh.hasClass(this.hide)){
            stTh.addClass(this.hide)
        }
    }
    
    var sourceTypes = (this.platformSourceTypes[platform]!=undefined) ? this.platformSourceTypes[platform] : false, psT;
    
    if(sourceTypes){
        for(var j in sourceTypes){
            psT = $('.'+this.sourceTypeHandClassName+'[data-type='+sourceTypes[j]+']');
            psT.removeClass(this.hide);
        }
    }
    
    return true;
}

SwdUI.prototype.getSideBarGridClone = function()
{
    this.sideBarGridClone = this.sideBarGridClone==undefined ? $('.'+this.sideNavClassName).eq(0).clone() : this.sideBarGridClone;
    return this.sideBarGridClone;
}

SwdUI.prototype.toggleAccessOptions = function(accessValue)
{
    switch(accessValue){
        case this.accessOptions[0]:
            $('.ac-sec').removeClass('swd-hide'); 
        break;
        case this.accessOptions[1]:
            $('.ac-sec').addClass('swd-hide'); 
        break;
    }
    
    return true;
}

SwdUI.prototype.validateStart = function(interactive)
{
    if(window.debugMode) console.log('@validateStart');
    interactive = (interactive==undefined) ? true : interactive;
    var fields = $(this.container+' .'+this.wrapClassName+'[data-wrap-name=start]').find('[name=package-type], [name=windows-type]');
    
    var noError = true;
    
    for(var i=0;i<fields.length;i++){
        if(fields.eq(i)==undefined || fields.eq(i).val()==''){
            if(interactive){
                errorNotify(fields.eq(i).attr('data-label')+' is required');
            }
            noError = false;
            break;
        }
    }
    
    return noError;
}

SwdUI.prototype.validateSource = function(interactive)
{
    if(window.debugMode) console.log('@validateSource');
    interactive = (interactive==undefined) ? true : interactive;
    var sourceFields = $(this.container+' .'+this.wrapClassName+'[data-wrap-name=source] .my-grid:visible').find('input.wt-value'), uploadValueField;
    var noError = true;
    
    for(var z=0;z<sourceFields.length;z++){
        if(!isNaN(sourceFields.eq(z).val()) && parseInt(sourceFields.eq(z).val())==1){
            uploadValueField = sourceFields.eq(z).parents('.wt-group').find('input.uploaded-fn');
            if(uploadValueField.val()==undefined || uploadValueField.val()==''){
                
                if(interactive){
                    sourceFields.eq(z).parents('.wt-group').find('.qq-up-wrap').css({'box-shadow': '0px 0px 8px 0px red'});
                    setTimeout(function(){ 
                        sourceFields.eq(z).parents('.wt-group').find('.qq-up-wrap').css({'box-shadow': '0px 0px 8px 1px transparent'});
                    }, 1000);
                    errorNotify('Please upload a file'); 
                }
                noError = false;
                break;
            }
        }
        
    }
    
    if(!noError){
        return noError;
    }
    
    if(noError){
        noError = this.validateFields($(this.container+' .'+this.wrapClassName+'[data-wrap-name=source]'), interactive);
    }
    
    return noError;
}


SwdUI.prototype.validateFinish = function(interactive)
{
    if(window.debugMode) console.log('@validateFinish');
    interactive = (interactive==undefined) ? true : interactive;
    var noError = this.validateFields($(this.container+' .'+this.wrapClassName+'[data-wrap-name=finish]'), interactive);
    
    return noError;
}

SwdUI.prototype.validateFields = function(container, interactive)
{
    var fields = $('input:visible,select:visible');
    
    if(container!=undefined && container.length >= 1){
        fields = container.find('input:visible,select:visible');
    }
    
    var noError = true, field, v, urlRegexp;
    
    for(var i=0;i<fields.length;i++){
        field = fields.eq(i);
        
        if(field.attr('data-required') != undefined && field.attr('data-required')=='true'){
            if(field.attr('type')!=undefined && field.attr('type')=='radio'){
                v = field.parents('.form-group').find('input.form-check-input[type=radio]');
                if(!v.is(':checked')){
                    noError = false;
                }
            }
            else if(field==undefined || field.val()==''){
                noError = false;
            }

            if(!noError){
                if(interactive){
                    field.focus();
                    errorNotify(field.attr('data-label')+' is required');
                }
                break;
            }
        }
        
        if(field.attr('data-numeric') != undefined && field.attr('data-numeric')=='true' && isNaN(field.val())){
            noError = false;
            if(!noError){
                if(interactive){
                    field.focus();
                    errorNotify(field.attr('data-label')+' must not be a numeric value');
                }
                break;
            }
        }
        
        if(field.attr('data-max-length') != undefined && field.attr('data-max-length')!='' && !isNaN(field.attr('data-max-length')) && field.val()!=''){
            v = parseInt(field.attr('data-max-length'));
            if(field.val().length > v ){
                noError = false;
            }
            
            if(!noError){
                if(interactive){
                    field.focus();
                    errorNotify(field.attr('data-label')+' must not exceed '+v+' characters');
                }
                break;
            }
        }
        
        if(field.attr('data-url') != undefined && field.attr('data-url')=='true' && field.val()!=''){
            urlRegexp = /^(?:(?:https?|ftp):\/\/)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})))(?::\d{2,5})?(?:\/\S*)?$/;
            
            if (!urlRegexp.test(field.val()))
            {
                noError = false;
            }
            
            if(!noError){
                if(interactive){
                    field.focus();
                    errorNotify(field.attr('data-label')+' must be valid url');
                }
                break;
            }
        }
    } 
    
    return noError;
}


SwdUI.prototype.validatePrecheck = function(interactive)
{
    if(window.debugMode) console.log('@validatePrecheck'); 
    interactive = (interactive==undefined) ? true : interactive;
    return true; //this.validateFields($(this.container+' .'+this.wrapClassName+'[data-wrap-name=pre-check]'), interactive);
}

SwdUI.prototype.resetWtHandActiveIndex = function(wrap, index)
{
    if(window.debugMode) console.log('@resetWtHandActiveIndex');
    
    var wtGroups = wrap.find('.my-btn-group'), wtGroup, hands, activeCount, target;
        index = !isNaN(index) ? parseInt(index) : 0;
    
    for(var i=0;i<wtGroups.length;i++){
        wtGroup = wtGroups.eq(i);
        
        if(!wtGroup.parents('.my-grid').hasClass(this.hide))
        {
            hands = wtGroup.find('.wt-hand:not(.active):not(.'+this.hide+')');
            activeCount = wtGroup.find('.wt-hand.active:not(.'+this.hide+')').length;
            if(window.debugMode) console.log('active count='+activeCount)
            if(activeCount==0){
                wtGroup.find('.wt-hand.active').removeClass('active')
                target = hands.eq(index);
                target.addClass('active');
                wtGroup.parents('.my-grid').find('.wt-group').find('input.wt-value').val(target.attr('data-value'));
            }
        }
        
    }
    
    return true;
}

SwdUI.prototype.populateSource = function()
{
    if(window.debugMode) console.log('@populateSource');
    
    var systemType = $(this.container).find('input[name=windows-type]').val();
    var wrap = $(this.container).find('[data-wrap-name=source]');
    
    if(systemType!=undefined && systemType!='')
    {
        this.resetWindowsSourceWrap(systemType);
    }
    
    
    /*
    switch(this.platform){
        case 'windows':
            
            if(windowsType!=undefined && windowsType!='')
            {
                this.resetWindowsSourceWrap(windowsType);
            }
            
        break;  
        case 'linux':
        case 'mac':
        case 'android':
        case 'ios':
            this.cleanSourceWrapGrids(windowsType);
            this.cleanPreCheckGrids(windowsType);
            this.createNewSourceGrid(windowsType);
            this.initNewSourceGrid(windowsType);
        break;
    }
    */
   
    this.initVisibility();
    this.refreshMutipleGridCountDisplay(); 
    
    wrap.attr('data-populate', 'false');
    
    return true;
}

SwdUI.prototype.populatePreCheck = function(visibilityTarget)
{
    if(window.debugMode) console.log('@populatePreCheck');
    
    var systemType = $(this.container).find('input[name=windows-type]').val();
            
    if(systemType!=undefined && systemType!=''){
        this.resetWindowsPreCheckWrap(systemType);
    }
    /*        
    switch(this.platform){
        case 'windows':
            var windowsType = $(this.container).find('input[name=windows-type]').val();
            
            if(windowsType!=undefined && windowsType!=''){
                this.resetWindowsPreCheckWrap(windowsType);
            }
        break;
        case 'linux':
             this.createNewPreCheckGrid();
        break;
        case 'mac':
             this.createNewPreCheckGrid();
        break;
        case 'android':
             this.createNewPreCheckGrid();
        break;
        case 'ios':
             this.createNewPreCheckGrid();
        break;
    }
    */
    
    var mtZ = $(this.container).find('[data-wrap-name=source]').find('.mt-z'),
    mtZCount = mtZ.length;

    if(mtZCount > 1){
        for(var i=1;i<(mtZCount);i++){
            this.addMoreSectionInPreCheckWrap();
        }
    }
    
    if(visibilityTarget==undefined){
        this.initVisibility();
    } else {
         this.initVisibility(visibilityTarget);
    }
    
    var wrap = $(this.container).find('[data-wrap-name=pre-check]');
     
    this.resetWtHandActiveIndex(wrap);
    this.refreshMutipleGridCountDisplay(); 
    wrap.attr('data-populate', 'false');
    
    return true;
}

SwdUI.prototype.populateFinish = function(visibilityTarget)
{
    if(window.debugMode) console.log('@populateFinish');
    
    var wrap = $(this.container).find('[data-wrap-name=source]');
    var mtZ = wrap.find('.mt-z'),
    mtZCount = mtZ.length;
    this.cleanOptionsGrids();
    
    if(mtZCount > 1){
        for(var i=1;i<(mtZCount);i++){
            this.addMoreOptionsSection();
        }
    }
    
    if(visibilityTarget==undefined){
        this.initVisibility();
    } else {
         this.initVisibility(visibilityTarget);
    }
    
    this.refreshMutipleGridCountDisplay(); 
    wrap.attr('data-populate', 'false');
    
    return true;
}

SwdUI.prototype.cleanOptionsGrids = function()
{
    var optionsContainer = $(this.container).find('[data-wrap-name=finish]').find('.options-section');
    
    for(var i=1;i<optionsContainer.length;i++){
        optionsContainer.eq(i).remove();
    }
    
    return optionsContainer;
}

SwdUI.prototype.hideAllWraps = function(inActivateAll)
{
    var wraps = $(this.container).find('.'+this.wrapClassName);
    
    for(var i=0;i<wraps.length;i++){
        if(!wraps.eq(i).hasClass(this.hide)){
            wraps.eq(i).addClass(this.hide);
        }
        
        if(inActivateAll!=undefined && inActivateAll){
            if(wraps.eq(i).hasClass(this.wrapActiveClassName)){
                wraps.eq(i).removeClass(this.wrapActiveClassName);
            }
        }
    }
    
    return true;
}


SwdUI.prototype.inactivateAllWizardMenuItem = function(item)
{
    var smi = this.sideNavClassName, 
        smie = $(this.container).find('.'+smi), ac = this.menuWizardItemActiveClassName;
    
    for(var i=0;i<smie.length;i++){
        if(smie.eq(i).hasClass(ac)){
            smie.eq(i).removeClass(ac);
        }
    }
}

SwdUI.prototype.changeWizardMenuItem = function(item)
{
    var wmc = this.wizardMenuClickableClassName,
        smi = this.sideNavClassName, smie = $(this.container).find('.'+smi+'[data-target='+item+']'),
        ac = this.menuWizardItemActiveClassName;
    
    this.inactivateAllWizardMenuItem();
    if(!smie.addClass(ac)){
        smie.addClass(ac);
    }
    
    if(!smie.addClass(wmc)){
        smie.addClass(wmc);
    } 
    
    return true;
}

SwdUI.prototype.makeAllWizardMenuItemsClickable = function()
{
    var wmc = this.wizardMenuClickableClassName,
        items = $(this.container).find('#'+this.sideMenuContainerIdName).find('.addBox');
    
    for(var i=0;i<items.length;i++){
        items.eq(i).addClass(wmc);
    }
    
    return true;
}

SwdUI.prototype.getActiveIndex = function()
{
    var wraps = $(this.container).find('.'+this.wrapClassName), i;
    
    for (var k = 0; k < wraps.length; k++) {
        if (wraps.eq(k).hasClass(this.wrapActiveClassName)) {
            i = k;
            break;
        }
    }
    
    return i;
}

SwdUI.prototype.moveWizardItem = function(item)
{
    this.hideAllWraps(true);
    $(this.container).find('.'+this.wrapClassName+'[data-wrap-name='+item+']').removeClass(this.hide).addClass(this.wrapActiveClassName);
    this.changeWizardMenuItem(item);
    
    return true;
}

SwdUI.prototype.getSelectedPackageType = function(item)
{
    return $(this.container).find('input[name=package-type]').val();
}


SwdUI.prototype.inResetInitVisibilityOnly = function(only, visibleFilters)
{
    var packageType = this.getSelectedPackageType();
                
    if(packageType!=undefined && packageType!=''){
        visibleFilters = visibleFilters[packageType];
    } else {
        visibleFilters =  false;
    }

    this.resetWizardWrapsVisibiity(only);

    return visibleFilters;
}

SwdUI.prototype.initVisibility = function(targetWrap)
{
    if(window.debugMode) console.log('@initVisibility');
    
    var visibleFilters = this.visibleFieldFilters[this.platform], target;
    
    if(targetWrap==undefined || targetWrap.length == 0){
        var idx = this.getActiveIndex(), targetWrap;
            idx++;
        targetWrap = $(this.container).find('.'+this.wrapClassName).eq(idx);
    }
    
    if(targetWrap.attr('data-wrap-name')!=undefined)
    {   
        var only;
        visibleFilters = visibleFilters[targetWrap.attr('data-wrap-name')];
        
        switch(targetWrap.attr('data-wrap-name')){
            case 'source':
                only = '#source-wrap';
                visibleFilters = this.inResetInitVisibilityOnly(only, visibleFilters);
            break;
            case 'finish':
                only = '#finish-wrap';
                visibleFilters = this.inResetInitVisibilityOnly(only, visibleFilters);
            break;
        }
        
        if(visibleFilters && visibleFilters!=undefined){
            for(var i=0;i<visibleFilters.length;i++){
                target = targetWrap.find(visibleFilters[i]);
                
                if(target.hasClass(this.hide))
                {
                    target.removeClass(this.hide);
                }
            }
        }
    }
    
    return true;
}

SwdUI.prototype.resetWizardWrapsVisibiity = function(only)
{
    if(window.debugMode) console.log('@resetWizardWrapsVisibiity');
    
    var defaultHides = $(this.container).find('.'+this.defaultHide);
    
    if(only!=undefined){
        defaultHides = $(this.container).find(only+' .'+this.defaultHide)
    }
    
    for(var i=0;i<defaultHides.length;i++){
        if(!defaultHides.eq(i).hasClass(this.hide)){
            defaultHides.eq(i).addClass(this.hide);
        }
    }
}

SwdUI.prototype.next = function()
{ 
    this.platform = ((this.platform == undefined) ? $(this.container+' input[name=platform]').val() : this.platform);
    
    var activeIndex = this.getActiveIndex(), iX, platform = this.platform,
        gFx = this.gridValidationFunctions,
        gFx = (gFx[platform]!=undefined) ? gFx[platform] :  false,
        gPx = this.gridPopulateFunctions,
        gPx = (gPx[platform]!=undefined) ? gPx[platform] :  false,
        pWd = (this.configurations[platform]!=undefined) ?  this.configurations[platform] : false;
    
    iX = activeIndex + 1;
    var oKs = Object.keys(pWd), nextWrap = oKs[iX];
        
    if(pWd)
    {
        var validated = true;
        
        if(gFx){
            try{
                validated = this[gFx[activeIndex]]();
            } catch(e){errorNotify("Something went wrong");}
        }
        
        if (validated)
        {
            var continueFlow = true;
            
            if($(this.container).find('.'+this.wrapClassName+'[data-wrap-name='+oKs[iX]+']').attr('data-populate') == 'true'){
                try{
                    continueFlow = this[gPx[activeIndex]]();
                } catch(e){
                    continueFlow = false;
                    errorNotify("Something went wrong "+e);
                }
            }
        }
       
        if(continueFlow){
            if(nextWrap!=undefined){
                this.moveWizardItem(nextWrap);
                if(iX>=1){
                    $(this.container).find('#'+this.changePlatformButtonId).addClass(this.hide);
                    $(this.container).find('#'+this.previousButtonId).removeClass(this.hide);
                }
                
                if(iX>=(oKs.length - 1)){ 
                    $(this.container).find('#'+this.nextButtonId).addClass(this.hide);
                    $(this.container).find('#'+this.saveButtonId).removeClass(this.hide);
                }
            }
        }
    }
    
    return true;
}

SwdUI.prototype.previous = function()
{ 
    var iTh = this.getActiveIndex(), iX, oKs, previousWrap,
        pWd = (this.configurations[this.platform]!=undefined) ?  this.configurations[this.platform] : false;
    
    if(pWd){
        iX = iTh - 1, oKs = Object.keys(pWd), previousWrap = oKs[iX];
        
        if(previousWrap){
            this.moveWizardItem(previousWrap);
            if(iX<=0){
                $(this.container).find('#'+this.previousButtonId).addClass(this.hide);
                if(window.wizardMode == 'create') $(this.container).find('#'+this.changePlatformButtonId).removeClass(this.hide);
            }
            
            if(iTh<=(oKs.length - 1)){
                $(this.container).find('#'+this.nextButtonId).removeClass(this.hide);
                $(this.container).find('#'+this.saveButtonId).addClass(this.hide);
            }
        }
    }
    
    return true;
}

SwdUI.prototype.truncateWizardMenu = function()
{
    var smi = $(this.container).find('.'+this.sideNavClassName);
    for(var i=0;i<smi.length;i++){
        smi.eq(i).remove();
    }
    
    return true;
}

SwdUI.prototype.hideAllButtons = function()
{
    $(this.container).find('#'+this.changePlatformButtonId+',#'+this.previousButtonId+',#'+this.nextButtonId+',#'+this.saveButtonId).addClass(this.hide);
    return true;
}

SwdUI.prototype.toPlatform = function()
{
    var smiC = this.getSideBarGridClone();
    this.hideAllButtons();
    this.hideAllWraps(true);
    this.truncateWizardMenu();
    $('#'+this.platformWrapIdName).removeClass(this.hide);
    
    smiC.find('p').text('1');
    smiC.find('span').text('Platform');
    smiC = '<div class="col-md-12 addBox side-nav-it active" data-disp="0">'+smiC.html()+'</div>';
            
    $('#'+this.sideMenuContainerIdName).html(smiC);
}

SwdUI.prototype.resetWindowsSourceWrap = function(type)
{
    this.cleanSourceWrapGrids();
    
    if(type!=undefined && type!=''){
        switch(type){
            case '32' :
                this.createNewSourceGrid('32');
            break;
            case '64' :
                this.createNewSourceGrid('64');
            break;
            case 'both' :
                this.createNewSourceGrid('32');
                this.createNewSourceGrid('64');
            break;
        }
        
        this.initNewSourceGrid();
    }
  
    return true;
}

SwdUI.prototype.cleanSourceWrapGrids = function()
{
    var parent = $(this.container).find('.eachPWrap[data-wrap-name=source]');
    var mtZ = parent.find('.row.mt-z');
    
    if(mtZ.length > 0){
        for(var i=1;i<mtZ.length;i++){
            mtZ.eq(i).remove();
        }
    }
    
    var grid = parent.find('.row.mt-z .my-grid');
    
    for(var i=0;i<grid.length;i++){
        grid.eq(i).find('.qq-up-wrap').html('');
    }
    
    for(var i=1;i<grid.length;i++){
        grid.eq(i).remove();
    }
    
    return true;
}


SwdUI.prototype.cleanPreCheckGrids = function()
{
    var parent = $(this.container).find('.eachPWrap[data-wrap-name=pre-check]');
    var mtX = parent.find('.row.mt-x');
    
    if(mtX.length > 0){
        for(var i=1;i<mtX.length;i++){
            mtX.eq(i).remove();
        }
    }
    
    var grid = parent.find('.row.mt-x .my-grid');
    
    for(var i=1;i<grid.length;i++){
        grid.eq(i).remove();
    }
    
    return true;
}


SwdUI.prototype.initNewSourceGrid = function(index)
{
    var parent = $(this.container).find('.eachPWrap[data-wrap-name=source]');
    var grid = (index==undefined || index>0) ? parent.find('.row.mt-z .my-grid') : parent.find('.row.mt-z').eq(index).find('.my-grid'), 
        idx,
        start = (index==undefined || index>0) ?  1 : 0;
    
    for(var i=start;i<(grid.length);i++){
        idx = grid.eq(i).find('.qq-up-wrap').attr('id');
        loadUploadWidget(idx,'qq-template-manual-trigger');
        grid.eq(i).find('.my-upload-type').bootstrapSwitch({'onText' : 'CDN','offText' : 'FTP'});
    }
}


SwdUI.prototype.resetWindowsPreCheckWrap = function(type)
{
    this.cleanPreCheckGrids();
    
    if(type!=undefined && type!=''){
        switch(type){
            case '32' :
                this.createNewPreCheckGrid('32');
            break;
            case '64' :
               this.createNewPreCheckGrid('64');
            break;
            case 'both' :
                this.createNewPreCheckGrid('32');
                this.createNewPreCheckGrid('64');
            break;
        }
    }
  
    return true;
}

SwdUI.prototype.cleanPreCheckGrids = function()
{
    var parent = $(this.container).find('.eachPWrap[data-wrap-name=pre-check]');
    var mtZ = parent.find('.row.mt-x');
    
    if(mtZ.length > 0){
        for(var i=1;i<mtZ.length;i++){
            mtZ.eq(i).remove();
        }
    }
    
    var grid = parent.find('.row.mt-x .my-grid');
    
    for(var i=1;i<grid.length;i++){
        grid.eq(i).remove();
    }
    
    return true;
}

SwdUI.prototype.getGridColSize = function()
{
    var defaultSize = '12';
    var systemType = $(this.container).find('input[name=windows-type]').val();
    if(systemType=='both'){
        defaultSize = '6';
    }
    
    return defaultSize;
}

SwdUI.prototype.setTopGridColSize = function(wrapName, size)
{
    var wrap = $(this.container).find('.eachPWrap[data-wrap-name='+wrapName+']'), rowClassName;
    
    switch(wrapName){
        case 'source':
            rowClassName = 'mt-z';
        break;
        case 'pre-check':
            rowClassName = 'mt-x';
        break;
    }
    
    var grid = wrap.find('.'+rowClassName+' .my-grid').eq(0), oppSize;
    var colsClass = 'col-sm-'+size;
    
    switch(size){
        case '6':
            oppSize = 'col-sm-12';
        break;
        case '12':
            oppSize = 'col-sm-6';
        break;
    }
    
    if(grid.hasClass(oppSize)){
        grid.removeClass(oppSize);
    }
    
    if(!grid.hasClass(colsClass)){
        grid.addClass(colsClass);
    }
}

SwdUI.prototype.createNewSourceGrid = function(bit)
{
    var parent = $(this.container).find('.eachPWrap[data-wrap-name=source]'), idx;
    var gridColSize = this.getGridColSize();
    
    this.setTopGridColSize('source', gridColSize);
    
    var mtZ = parent.find('.row.mt-z'),
        clone = mtZ.eq(0).find('.my-grid').eq(0).clone();
    
    var totalGridRows = mtZ.length;
    var lastGridRow = mtZ.eq((totalGridRows - 1)),
        lastGrid = lastGridRow.find('.my-grid'),
        totalGrid = lastGrid.length, idx, fN;

    for(var i=0;i<(clone.find('.my-btn-group .wt-hand').length);i++){
       idx = randomString(18);
       clone.find('.my-btn-group .wt-hand').eq(i).attr('data-bs-target', idx);
       clone.find('.wt-group .wt-wrap').eq(i).attr('id', idx);
    }
    
    idx = randomString(18);
    clone.find('.qq-up-wrap').attr('id', idx);
    
    if(bit!=undefined && bit!=''){
        clone.find('.well').text(bit+' Bit').removeClass(this.hide);
    }
    
    var valFields = clone.find('input,select'), 
        bitField = (bit==undefined || bit=='') ? '' : bit ,
        nestify = '[]';
    
    for(i=0;i<valFields.length;i++){
       fN = (bitField == '') ? nestify : '-'+bitField+nestify;
       valFields.eq(i).attr('name', valFields.eq(i).attr('name')+fN)
    }
    
    clone.removeClass(this.hide);
    var gridHtml = '<div class="'+clone.attr('class')+'">'+clone.html()+'</div>';
    
    if(totalGridRows==1){
        lastGrid.eq((totalGrid - 1)).after(gridHtml);
    } else if(totalGridRows>1){
        if(totalGrid==0){
            lastGridRow.html(gridHtml);
        } else {
            lastGrid.eq((totalGrid - 1)).after(gridHtml);
        }
    }
    
    return true;
}

/* 
// in grid approach
SwdUI.prototype.createNewSourceGrid = function(bit)
{
    var parent = $(this.container).find('.eachPWrap[data-wrap-name=source]'), idx;
    var mtZ = parent.find('.row.mt-z').eq(0),
        grid = mtZ.find('.my-grid'),
        clone = grid.eq(0).clone();
    
    var totalGridRows = grid.length;

    for(var i=0;i<(clone.find('.my-btn-group .wt-hand').length);i++){
       idx = randomString(18);
       clone.find('.my-btn-group .wt-hand').eq(i).attr('data-bs-target', idx);
       clone.find('.wt-group .wt-wrap').eq(i).attr('id', idx);
    }
    
    idx = randomString(18);
    clone.find('.qq-up-wrap').attr('id', idx);
    
    if(bit!=undefined && bit!=''){
        clone.find('.well').text(bit+' Bit').removeClass(this.hide);
    }
    
    var valFields = clone.find('input,select'), 
        bitField = (bit==undefined || bit=='') ? '' :bit ,
        nestify =  (bit==undefined || bit=='') ? '' : '[]';
    
    for(i=0;i<valFields.length;i++){
       valFields.eq(i).attr('name', valFields.eq(i).attr('name')+'-'+bitField+nestify)
    }
    
    clone.removeClass(this.hide);
    var gridHtml = '<div class="'+clone.attr('class')+'">'+clone.html()+'</div>';
    
    grid.eq((totalGridRows - 1)).after(gridHtml);
    
    return true;
}

*/

SwdUI.prototype.createNewPreCheckGrid = function(bit)
{
    var parent = $(this.container).find('.eachPWrap[data-wrap-name=pre-check]');
    var gridColSize = this.getGridColSize();
    
    this.setTopGridColSize('pre-check', gridColSize);
    
    var mtX = parent.find('.row.mt-x'),
        clone = mtX.eq(0).find('.my-grid').eq(0).clone();
    
    var totalGridRows = mtX.length;
    var lastGridRow = mtX.eq((totalGridRows - 1)),
        lastGrid = lastGridRow.find('.my-grid'),
        totalGrid = lastGrid.length, idx;
    
    for(var i=0;i<(clone.find('.my-btn-group .wt-hand').length);i++){
       idx = randomString(18);
       clone.find('.my-btn-group .wt-hand').eq(i).attr('data-bs-target', idx);
       clone.find('.wt-group .wt-wrap').eq(i).attr('id', idx);
    }
    
    if(bit!=undefined && bit!=''){
        clone.find('.well').text(bit+' Bit').removeClass(this.hide);
    }
    
    var valFields = clone.find('input,select'), 
        bitField = (bit==undefined || bit=='') ? '' : bit ,
        nestify = '[]', fN;
    
    for(i=0;i<valFields.length;i++){
        
        if(valFields.eq(i).attr('type')=='radio'){
            fN = (bitField=='') ? '' : '-'+bitField;
            valFields.eq(i).attr('name', valFields.eq(i).attr('name')+'-'+(totalGridRows - 1)+fN);
        } else {
            fN = (bitField=='') ? nestify : '-'+bitField+nestify;
            valFields.eq(i).attr('name', valFields.eq(i).attr('name')+fN);
        }
    }
    
    clone.removeClass(this.hide);
    var gridHtml = '<div class="'+clone.attr('class')+'">'+clone.html()+'</div>';
    
    if(totalGridRows==1){
        lastGrid.eq((totalGrid - 1)).after(gridHtml);
    } else if(totalGridRows>1){
        if(totalGrid==0){
            lastGridRow.html(gridHtml);
        } else {
            lastGrid.eq((totalGrid - 1)).after(gridHtml);
        }
    }
    
    return true;
}

SwdUI.prototype.addMoreSectionInSourceWrap = function(bit)
{
    var sourceParent = $(this.container).find('.'+this.wrapClassName+'[data-wrap-name=source]'),
        mtZ = sourceParent.find('.mt-z.row'),
        c = mtZ.length, nHtm;
        
        nHtm = '<div class="'+mtZ.attr('class')+'"></div>';
        mtZ.eq((c - 1)).after(nHtm);
        
        var platform = $(this.container).find('[name=platform]').val();
        var type = $(this.container).find('input[name=windows-type]').val();
        
        if(type!=undefined && type!='')
        {
            switch(type){
                case '32' :
                    this.createNewSourceGrid('32');
                break;
                case '64' :
                    this.createNewSourceGrid('64');
                break;
                case 'both' :
                    this.createNewSourceGrid('32');
                    this.createNewSourceGrid('64');
                break;
            }
        }
        /*
        switch(platform)
        {
            case 'windows':
                var type = $(this.container).find('input[name=windows-type]').val();
        
                if(type!=undefined && type!='')
                {
                    switch(type){
                        case '32' :
                            this.createNewSourceGrid('32');
                        break;
                        case '64' :
                            this.createNewSourceGrid('64');
                        break;
                        case 'both' :
                            this.createNewSourceGrid('32');
                            this.createNewSourceGrid('64');
                        break;
                    }
                }
            break;
            case 'linux':
            case 'mac': 
                this.createNewSourceGrid();
            break;
        }
        */
    
    this.initNewSourceGrid(c);
    return true;
}



SwdUI.prototype.addMoreSectionInPreCheckWrap = function()
{
    var sourceParent = $(this.container).find('.'+this.wrapClassName+'[data-wrap-name=pre-check]'),
        mtX = sourceParent.find('.mt-x.row'),
        c = mtX.length, nHtm;

        nHtm = '<div class="'+mtX.attr('class')+'"></div>';
        mtX.eq((c - 1)).after(nHtm);
        
    var platform = $(this.container).find('input[name=platform]').val();
    
    var type = $(this.container).find('input[name=windows-type]').val();
        
    if(type!=undefined && type!=''){
        switch(type){
            case '32' :
                this.createNewPreCheckGrid('32');
            break;
            case '64' :
               this.createNewPreCheckGrid('64');
            break;
            case 'both' :
                this.createNewPreCheckGrid('32');
                this.createNewPreCheckGrid('64');
            break;
        }
    }
            
    /*
    switch(platform){
        case 'windows':
            var type = $(this.container).find('input[name=windows-type]').val();
        
            if(type!=undefined && type!=''){
                switch(type){
                    case '32' :
                        this.createNewPreCheckGrid('32');
                    break;
                    case '64' :
                       this.createNewPreCheckGrid('64');
                    break;
                    case 'both' :
                        this.createNewPreCheckGrid('32');
                        this.createNewPreCheckGrid('64');
                    break;
                }
            }
        break;
        case 'linux' : 
        case 'mac':
            this.createNewPreCheckGrid();
        break;
    }
    */    
        
    return true;
}

SwdUI.prototype.addMoreOptionsSection = function()
{
    var optionsSection = $(this.container).find('.eachPWrap[data-wrap-name=finish] .options-section'),
        optionsSectionClone = optionsSection.clone();
       
    
    var switches = optionsSectionClone.find('.bootstrap-switch-wrapper'), html, label;
    
    for(var i=0;i<switches.length;i++){
        switches.eq(i).find('span').remove();
        html = switches.eq(i).find('.bootstrap-switch-container').html();
        label = switches.eq(i).parent('.form-group.has-label').find('label');
        switches.eq(i).remove();
        label.after(html);
    }
    
    var l = optionsSection.length;
    optionsSectionClone.find('.resume-download-switch').attr('name', optionsSectionClone.find('.resume-download-switch').parents('.resume-download-wrap').attr('data-name')+'['+l+']');
    optionsSectionClone.find('.propagation-switch').attr('name', optionsSectionClone.find('.propagation-switch').parents('.propagation-wrap').attr('data-name')+'['+l+']');
    
    var htm = '<div class="container my-container options-section">'+optionsSectionClone.html()+'</div>';
     
    optionsSection.eq((optionsSection.length - 1)).after(htm);
    optionsSection = $(this.container).find('.eachPWrap[data-wrap-name=finish] .options-section');
    optionsSection.eq((optionsSection.length  - 1)).find(".resume-download-switch,.propagation-switch").bootstrapSwitch({'onText' : 'No','offText' : 'Yes'});
    
    return true;
}

SwdUI.prototype.addMoreEvent = function()
{
    if(this.getSegmentsCount() > window.maxSegments){
        errorNotify('You can add max '+window.maxSegments+' segments');
        return false;
    }
     
    this.addMoreSectionInSourceWrap();
    //this.addMoreSectionInPreCheckWrap();
    //this.addMoreOptionsSection();
    this.refreshMutipleGridCountDisplay(); 
    
    if(this.getSegmentsCount()>=2){
        $(this.container).find('#remove-last-section').removeClass(this.hide);
    }
    
    return true;
}

SwdUI.prototype.getSegmentsCount = function()
{
    return $(this.container).find('#source-wrap .mt-z').length;
    
}

SwdUI.prototype.removeLastSourceSection = function()
{
    var mtZ = $(this.container).find('.eachPWrap[data-wrap-name=source] .mt-z'),
        mtZCount = mtZ.length;

    mtZ.eq((mtZCount - 1)).remove();
    
    return true;
}

SwdUI.prototype.removeLastSectionEvent = function()
{
    this.removeLastSourceSection();
    return true;
}

SwdUI.prototype.refreshMutipleGridCountDisplay = function()
{
    if(window.debugMode) console.log('@refreshMutipleGridCountDisplay');
    
    var wraps = ['source','pre-check','finish'], wrap, rows, count, z;
    
    for(var i=0;i<wraps.length;i++){
        wrap = $(this.container).find('.'+this.wrapClassName+'[data-wrap-name='+wraps[i]+']');
        rows = wrap.find('.sec-row');
        count = rows.length;
        
        for(z=0;z<count;z++){
            rows.eq(z).find('.bx-cnt-spn').remove();
            rows.eq(z).prepend( '<span class="bx-cnt-spn">#'+(z+1)+'</span>' );
        }
    }
}

SwdUI.prototype.activateWrapPopulation = function(wrapName)
{
    if(window.debugMode) console.log('@activateWrapPopulation');
    
    var target = $(this.container).find('.'+this.wrapClassName);
    
    if(wrapName!=undefined && wrapName!=''){
        target = $(this.container).find('.'+this.wrapClassName +'[data-wrap-name='+wrapName+']');
        target.attr('data-populate', 'true');
        return true;
    }
    
    for(var i=0;i<target.length;i++){
        target.eq(i).attr('data-populate', 'true');
    }
    
    return true;
}

SwdUI.prototype.closeWidget = function()
{
    $(this.container).hide();
    return true;
}

SwdUI.prototype.savePackage = function(event, form)
{
    
    var opt = window.wizardMode!=undefined && window.wizardMode == 'edit' ? 'update' : 'create';
    var successMessage = (opt=='create') ? "Successfully added a new software distribution" : "Successfully updated software distribution";
    var url = "../software_distribution/software_distribution.php?function=save";
    var swdUi = this;
    
    if(window.formProgress){
        errorNotify("Request already in progress, please wait while we finish saving the data");
        return false;
    }
    
    if(window.configureExecute) url +='&configure-execute=true';
    
    window.formProgress  = true;
    
    swdUi.progressLoaderShow();
    
    $.ajax({
        url: url,
        type: "POST",
        data: form.serialize()+'&opt='+opt+"&csrfMagicToken=" + csrfMagicToken,
        dataType: "JSON",
        success: function (data) {
            swdUi.progressLoaderHide();
            window.formProgress = false;
            if(data.success!=undefined){ 
                if(data.success==false){
                    if(data.validator!=undefined && data.validator){
                        var responseData = data.data;
                        if(typeof responseData == 'object'){
                            for(var i in responseData){
                                if(typeof responseData[i]=='string'){
                                    errorNotify(responseData[i]);
                                    break;
                                } else {
                                    errorNotify(responseData[i][0]);
                                    break;
                                }
                            }
                            return false;
                        }  else {
                            errorNotify(responseData);
                            return false;
                        }
                        
                    } else {
                        errorNotify(data.message);
                    }
                    
                    return false;
                    
                } else if(data.success){
                    successNotify(successMessage);
                    swdUi.fetchList();
                    swdUi.close();
                    return true;
                }
            }
        },
        error: function(){
            swdUi.progressLoaderHide();
            window.formProgress = false;
            errorNotify("Something went wrong");
        }
    }); 
}

SwdUI.prototype.progressLoaderShow = function()
{
    var closeHand = $('#close-swd-widget');
    closeHand.find('i').hide();
    closeHand.find('img').show();
    return false;
}

SwdUI.prototype.progressLoaderHide = function()
{
    var closeHand = $('#close-swd-widget');
    closeHand.find('i').show();
    closeHand.find('img').hide();
    return false;
}


SwdUI.prototype.fetchList = function()
{
    var tableId = 'swd-list-dtable', table = $('#'+tableId);
    
    table.dataTable().fnDestroy();
    var repoTable = table.DataTable({
        scrollY: 'calc(100vh - 240px)',
        scrollCollapse: true,
        autoWidth: false,
        searching: true,
        processing: true,
        serverSide: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        bAutoWidth: true,
        order: [[0, "desc"]],
        "lengthMenu": [[20, 25, 50, 100], [20, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search Records",
        },
        oLanguage: {sProcessing:  "<img src=\""+window.base+"assets/img/loader2.gif\" style=\"margin-top: 20%;\"/>"},
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        ajax: {
            url: "../software_distribution/software_distribution.php?function=list&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        columns: [
            {"data": "sl-id"},  
            {"data": "sl-icon"}, 
            {"data": "sl-name"},
            {"data": "sl-platform"},
            {"data": "sl-distribution"},
            {"data": "sl-execution"},
            {"data" : "sl-global"},
            {"data" : "sl-created"},
            {"data" : "sl-updated"}
                        
        ],
        "aoColumnDefs": [{ "bVisible": false, "aTargets": [0] }],
        drawCallback: function(settings) {
        },
        ordering: true,
        select: false,
        bInfo: false,
        responsive: true,
        fnInitComplete: function (oSettings, json) {
            $('#swd-list-dtable_processing').css('top', ($('.dataTables_scroll').offset().top - 18)+'px');
        },
        language: { search: "", searchPlaceholder: "Search Records" }
    }); 
}

function resetSelectBox(box){
    
    for(var i=0;i<box.find('option').length;i++){
        box.find('option').eq(i).removeAttr('selected');
    }
    
    return true;
}

SwdUI.prototype.setFieldsValue = function(data, index) 
{
    if(window.debugMode) console.log('@setFieldsValue');
    
    var c = $(this.container), f, pG, iD, reQ;
    
    for(var i in data)
    { 
        f = c.find('input[name=\''+i+'\'], select[name=\''+i+'\']');
        
        if(f!=undefined && f.length >= 1)
        {
            if(index!=undefined) f = f.eq(index);
        
            if(f.attr('type')=='hidden' && f.hasClass('swd-ic-it-input') && data[i]!=undefined && data[i]!=null){
                pG = f.parents('.swd-ic-it-group');
                iD = pG.find('.swd-ic-it').eq(0).attr('data-indentifier-key');
                pG.find('.swd-ic-it[data-'+iD+'='+data[i]+']').addClass('active');
            }

            if(f.is("input")){
                if(f.attr('type')=='checkbox'){
                    if(f.parents(".bootstrap-switch-container").length >= 1){ //if bootsrap switch and value is 0
                       if(data[i]!=undefined && data[i]!=null && !isNaN(data[i]) && parseInt(data[i])==0){
                           if(i == 'upload-to-64[]' || i == 'upload-to-32[]'){ // lets do this now only for upload
                                f.click(); 
                             }
                       }
                    } else { // all other checkboxes
                        if(data[i]!=undefined && data[i]!=null && !isNaN(data[i]) && parseInt(data[i])==1){
                           f.click();
                        } 
                    }
                } else if(f.attr('type')=='radio'){
                    reQ = (data[i]!=undefined && data[i]!=null && !isNaN(data[i]) && parseInt(data[i])==0) ? 0 : 1;
                    f.eq(reQ).prop('checked', true); 
                } else {
                    if(data[i]!=undefined && data[i]!=null){
                        f.val(data[i]);
                    }
                }
            } else if(f.is("select")){
                resetSelectBox(f);
                if(data[i]!=undefined && data[i]!=null){
                    f.find('option[value='+data[i]+']').attr('selected','selected'); 
                }
            }
        }
    }
    
    return true;
}

SwdUI.prototype.setDetailsFields = function(data) 
{
    if(window.debugMode) console.log('@setDetailsFields');
    
    this.resetScIc();
    var c = $(this.container), f, pG, iD,
        detail = data.detail, 
        totalSegment = data.item.length,
        platform = data.detail.platform;
    
    this.setFieldsValue(detail);
    this.sourcePopulateAndFill(totalSegment, data);
    this.populatePreCheck($('#pre-check-wrap'));
    $(this.container).find('[data-wrap-name=pre-check]').attr('data-populate', 'false');
    this.populateFinish($('#finish-wrap'));
    $(this.container).find('[data-wrap-name=finish]').attr('data-populate', 'false');
    
    var cK = [''];
    var windowsType = data.detail['windows-type'];
    
    switch(windowsType){
        case '32':
            cK = ['32'];
        break;
        case '64':
            cK = ['64'];
        break;
        case 'both':
            cK = ['32','64'];
        break;
    }

    var i, x, pUd, pCd, pUdT, ka;
    var btGPu = $(this.container).find('#source-wrap').find('.row.mt-z'), pI, btPuGr;
    var btGPc = $(this.container).find('#pre-check-wrap').find('.row.mt-x'), pCdT, btGPcGr;
     
    for(i=0;i<totalSegment;i++)
    {
        this.setFieldsValue(data.item[i], i);
        for(x in cK)
        {
            pUd = data.path[i][cK[x]];
            pCd = data.pre_check[i][cK[x]];
            ka = '-'+cK[x]+'[]';
            if(pUd!=undefined) pUdT = pUd['source-type'+ka];
            if(pCd!=undefined) pCdT = pCd['precheck-type'+ka];
            pI = x;
            
            if(i==0)
            {
                pI = parseInt(parseInt(x) + parseInt(1)); 
            }
             
            btPuGr = btGPu.eq(i).find('.my-grid').eq(pI);
            btGPcGr = btGPc.eq(i).find('.my-grid').eq(pI);
            
            if(pUdT!=undefined) this.autoSetWt(btPuGr, pUdT); 
            if(pCdT!=undefined) this.autoSetWt(btGPcGr, pCdT);
            
            if(pUd!=undefined) this.setFieldsValue(pUd, i);
            if(pCd!=undefined) this.setFieldsValue(pCd, i);
        }
    }
    
    this.setFieldsValue(data.radios);
    
    return true;
}

SwdUI.prototype.autoSetWt = function(grid, value)
{
    if(window.debugMode) console.log('@autoSetWt');
    
    var wtG = grid.find('.wt-group').eq(0);
    var tI = grid.find('label.wt-hand[data-value='+value+']');
    var tIdx = tI.attr('data-bs-target');
    var btG = grid.find('.my-btn-group .btn-group').eq(0);
    
    this.inActivateWtButtonGroupHead(btG);
    tI.addClass('active');
    this.hideAllWtButtonGroupBody(wtG);
    wtG.find('#'+tIdx).removeClass(this.hide);
    wtG.find('.wt-value').val(value);
    
    return true;
}

SwdUI.prototype.inActivateWtButtonGroupHead = function(group)
{
    var lb = group.find('label.wt-hand'), l = lb.length;
    
    for(var i=0;i<l;i++){
        if(lb.eq(i).hasClass('active')) lb.eq(i).removeClass('active');
    }
    
    return true;
}


SwdUI.prototype.hideAllWtButtonGroupBody = function(group)
{
    var lb = group.find('.wt-wrap'), l = lb.length;
    
    for(var i=0;i<l;i++){
       lb.eq(i).addClass(this.hide);
    }
    
    return true;
}


SwdUI.prototype.sourcePopulateAndFill = function(totalSegments, data)
{
    this.resetWindowsSourceWrap($('[name=windows-type]').val());
    this.initVisibility();
    
    if(totalSegments>1){
        for(var i=1;i<totalSegments;i++){
            this.addMoreSectionInSourceWrap();
        }
        this.refreshMutipleGridCountDisplay();
    }
    
    $(this.container).find('[data-wrap-name=source]').attr('data-populate', 'false');
    
    return true;
}

SwdUI.prototype.initEditUI = function(data, configureExecute)
{
    window.wizardMode = 'edit';
    
    var details = data.detail, platform;
    var itemData = data.item;
    
    if(details==undefined || details==null || itemData==undefined || itemData==null){
        errorNotify("Details not found");
    }
    
    platform = details.platform;
    $('[name=platform]').val(platform);
    this.init('#'+window.swdUIContainerIdName, data.detail.platform);
    this.toggleContainerDisplay();
    this.loadWizard(platform);
    this.loadWizardMenu(platform);
    this.setDetailsFields(data);
    //this.makeAllWizardMenuItemsClickable();
    this.hideSegmentControlBox();
    $(this.container).find('input[name=package-name]').attr('readonly','readonly');
    this.setPackageIdField(data.detail.id);
    $(this.container).find('#'+this.changePlatformButtonId).addClass(this.hide);
    if(configureExecute!=undefined && configureExecute){
       this.enableOnlyPathInPathUrlWt();
    }
    
    return true;
}

SwdUI.prototype.enableOnlyPathInPathUrlWt = function()
{
    if(window.debugMode) console.log('@enableOnlyPathInPathUrlWt');
    
    var mtz = $(this.container).find('.mt-z'), z, grids, gridsSt, group;
    
    try{
        for(var i=0;i<mtz.length;i++){
            grids = mtz.eq(i).find('.my-grid');
            gridsSt = (i==0) ? 1 : 0;
            for(z=gridsSt;z<grids.length;z++){
               group =  grids.eq(z).find('.my-btn-group');
               group.find('.wt-hand.source-upload, .wt-hand.source-url').attr('disabled', 'disabled');
            }
        }
    } catch(e){}
    
    return true;
}

SwdUI.prototype.disableReadOnly = function()
{
    var c = $(this.container), fields = ['package-name'];
    
    for(var i=0;i<fields.length;i++){
        c.find('input[name='+fields[i]+']').removeAttr('readonly');
    }
    
    return true;
}

SwdUI.prototype.setPackageIdField = function(value)
{
    var field = $('form[name='+this.formName+']').find('input[name='+this.packageIdFieldName+']');
    
    if(field==undefined || field.length <= 0)
    {
        var newField = document.createElement('input');
        newField.name = this.packageIdFieldName;
        newField.type = 'hidden';
        newField.value = value;
   
        document.getElementsByName(this.formName)[0].appendChild(newField);
    } else {
        field.val(value);
    }
    
    return true;
}

SwdUI.prototype.removePackageIdField = function()
{
    $('form[name='+this.formName+']').find('input[name='+this.packageIdFieldName+']').remove();
    return true; 
}

SwdUI.prototype.hideSegmentControlBox = function()
{
    $(this.container).find('.more-opt-box').addClass(this.hide);
    return true;
}

SwdUI.prototype.showSegmentControlBox = function()
{
    $(this.container).find('.more-opt-box').removeClass(this.hide);
    return true;
}

SwdUI.prototype.toggleContainerDisplay = function()
{
    var container = $(this.container), listWrapper = $('#swd-wrapper');
    
    if(container.is(':visible')){
        container.hide();
        listWrapper.show();
    } else {
        container.show();
        listWrapper.hide();
    }
}

SwdUI.prototype.editEvent = function(id, callback, type)
{
    var form = $('form[name=details-form]');
    form.find('input[name=id]').val(id);
    var swdui = new SwdUI, url = form.attr('action');
    
    $('#'+swdui.previousButtonId+',#'+swdui.changePlatformButtonId+',#'+swdui.saveButtonId).addClass(swdui.hide);
    $('#'+swdui.nextButtonId).removeClass(swdui.hide);
    
    if(window.fetchDetailsInProgress){
        $.notify("Request in progress, please wait while we process the current request.");
    }
    
    if(callback == 'initConfigureExecuteUI'){
        url +='&make_executable_details=true'; 
    }
    
    if(type!=undefined) url +='&type='+type; 
    
    if(!window.fetchDetailsInProgress)
    {
        window.fetchDetailsInProgress = true;
        
        $.ajax({
            url: url,
            type: form.attr('method'), 
            data: form.serialize()+"&csrfMagicToken=" + csrfMagicToken,
            dataType: "JSON",
            success: function (data) {
                window.fetchDetailsInProgress = false
                
                if(data.success!=undefined){
                    if(data.success==false){
                        errorNotify(data.message);
                        return false;
                    } else if(data.success){
                        
                        switch(callback){
                            case 'initEditUI':
                                swdui.initEditUI(data.data);
                                window.configureExecute = false;
                            break;
                            case 'initConfigureExecuteUI':
                                swdui.initEditUI(data.data, true);
                                window.configureExecute = true;
                            break;
                        }
                        
                    }
                }
            },
            error: function(){
                window.fetchDetailsInProgress = false
                errorNotify("Something went wrong");
            }
        }); 
    }   
}


SwdUI.prototype.requestConfiguration = function(id, type, callback)
{
    var form = $('form[name=configuration-form]');
    form.find('input[name=id]').val(id);
    var swdui = new SwdUI, url = form.attr('action');
    
    $.ajax({
            url: url,
            type: form.attr('method'), 
            data: form.serialize()+"&csrfMagicToken=" + csrfMagicToken,
            dataType: "JSON",
            success: function (data) {
                if(data.success!=undefined){
                    if(data.success==false){
                        errorNotify(data.message);
                        return false;
                    } else if(data.success){
                        $('#config-readonly').html(data.data);
                    }
                }
            },
            error: function(){
                errorNotify("Something went wrong");
            }
        }); 
}

SwdUI.prototype.directValidation = function(element, event)
{
    var allPWraps = $(this.container).find('.eachPWrap');
    var eventPWrap = element.parents('.eachPWrap');
    var currentIndex = allPWraps.index(eventPWrap);
    var gFx = this.gridValidationFunctions;
    var platform = $(this.container).find('input[name=platform]').val();
        gFx = gFx[platform]!=undefined ? gFx[platform] : undefined;
        isValidated = false;
        
    try{
        var isValidated = this[gFx[currentIndex]](false);
    } catch(e){} 
            
    var leftLinkWraps = $(this.container).find('.'+this.sideNavClassName);
    
    if (!isValidated) {
        for (var i = (currentIndex + 1); i < leftLinkWraps.length; i++) {
            if (leftLinkWraps.eq(i).hasClass(this.wizardMenuClickableClassName)) {
                leftLinkWraps.eq(i).removeClass(this.wizardMenuClickableClassName);
            }
        }
    }

    return true;
}

SwdUI.prototype.resetWizardMenu = function()
{
    var leftLinkWraps = $(this.container).find('.'+this.sideNavClassName),
        ac = this.menuWizardItemActiveClassName;
    
    for (var i = 0; i < leftLinkWraps.length; i++) 
    {
        if (leftLinkWraps.eq(i).hasClass(this.wizardMenuClickableClassName)) {
            leftLinkWraps.eq(i).removeClass(this.wizardMenuClickableClassName);
        }
        if (leftLinkWraps.eq(i).hasClass(ac)) {
            leftLinkWraps.eq(i).removeClass(ac);
        }
    }
    
    leftLinkWraps.eq(0).addClass(this.wizardMenuClickableClassName);
    leftLinkWraps.eq(0).addClass(ac);
    
    return true;
}


SwdUI.prototype.pushConfiguration = function(form , event, id)
{
    if(event.preventDefault)
    {
       event.preventDefault(); 
    } else {
        event.returnValue = true;
    }
    
    if(window.pushProgress){
        $.notify("Push Request in progress, please wait while we process the current request.");
    }
    
    if(id==undefined || id==''){
        errorNotify("Package id not found");
        return false;
    }
    
    var data = {'package-id' : id}; 
    
    var distributeField = form.find('[name=ck-conf-hand][value=distribute]');
    var executeField = form.find('[name=ck-conf-hand][value=execute]');
    
    if(!distributeField.is(':checked') && !executeField.is(':checked')){
        errorNotify("Nothing to push");
        return false;
    }
    
    if(distributeField.is(':checked')){
        data.distribute = 1;
    }
    
    if(executeField.is(':checked')){
        data.execute = 1;
    }
    data.csrfMagicToken = csrfMagicToken;
    if(!window.pushProgress)
    {
        window.pushProgress = true;
        var loader = $('#push-status-img');
        loader.show();
    
        $.ajax({
            url: form.attr('action'),
            type: 'POST', 
            data: data,
            dataType: "JSON",
            success: function (data) {
                loader.hide();
                window.pushProgress = false;
                if(data.success!=undefined){
                    if(data.success==false){
                        errorNotify(data.message);
                        return false;
                    } else if(data.success){
                        successNotify(data.message);
                        return false;
                    }
                }
            },
            error: function(){
                window.pushProgress = false;
                loader.hide();
                errorNotify("Something went wrong");
            }
        }); 
    }
    
    
    return false;
}

SwdUI.prototype.saveCredentials = function(form , event)
{
    if(event.preventDefault){
       event.preventDefault(); 
    } else {
        event.returnValue = false;
    }
    
    var isValidationPassed = this.validateFields(form, true);
    
    if(isValidationPassed)
    {
        $.ajax({
            url: form.attr('action'),
            type: 'POST', 
            data: form.serialize()+"&csrfMagicToken=" + csrfMagicToken,
            dataType: "JSON",
            success: function (data) {
                if(data.success!=undefined){
                    if(data.success==false){
                        errorNotify(data.message);
                        return false;
                    } else if(data.success){
                        rightContainerSlideClose('rsc-ftp-cdn-configuration');
                        successNotify(data.message);
                        return false;
                    }
                }
            },
            error: function(){
                errorNotify("Something went wrong");
            }
        }); 
    }
    
    return false;
    
}


SwdUI.prototype.editCredentialsUI = function(openSlider)
{
    $.ajax({
        url: window.base+'software_distribution/software_distribution.php?function=fetch-credentials&type='+window.selectedConfigurationType+"&csrfMagicToken=" + csrfMagicToken,
        type: 'GET',
        dataType: "JSON",
        success: function (data) {
            if(data.success!=undefined){
                if(data.success==false){
                    errorNotify(data.message);
                } else if(data.success){
                    data = data.data;
                    
                    if(window.selectedConfigurationType == 'cdn'){
                        var cdnForm = $('form[name=cdn-configure]');
                        cdnForm.find('[name=cdn-url]').val(data.url);
                        cdnForm.find('[name=cdn-ak]').val(data.access_key);
                        cdnForm.find('[name=cdn-sk]').val(data.secret_key);
                        cdnForm.find('[name=cdn-bucket-name]').val(data.bucket_name);
                        cdnForm.find('[name=cdn-region]').val(data.cdn_region);
                    } 
                    if(window.selectedConfigurationType == 'ftp'){
                        var ftpForm = $('form[name=ftp-configure]'); 
                        ftpForm.find('[name=ftp-url]').val(data.url);
                        ftpForm.find('[name=ftp-username]').val(data.username);
                        ftpForm.find('[name=ftp-password]').val(data.password);
                    }
                    
                    if(openSlider!=undefined && openSlider) rightContainerSlideOn('rsc-ftp-cdn-configuration');
                }
            }
        },
        error: function(){}
    });   
}

function loadUploadWidget(id, template)
{
    var div = $('#'+id), t = '';
    
    if($('[name=platform]').val()=='windows'){
        t = '-'+parseInt(div.parents('.my-grid').find('.well').text());
    }
    
    var existingInput = div.parents('.qq-up-wrap-parent').find('.uploaded-fn');
    
    if(existingInput.length >= 1 && existingInput.val()!=''){
        existingInput.val('');
    }
    
    var uploadPath = window.base+'swd/vendor/fineuploader/php-traditional-server/endpoint.php';
    
    var manualUploader = new qq.FineUploader({
        element: document.getElementById(id),
        template: template,
        multiple : false,
        callbacks: {
            onCancel: function (id, file) {
                try{div.parents('.wt-wrap').find('.uploaded-fn').val('');} catch(e){}
            },
            onSubmit: function (id, file) {
                var newFile = changeUploadFileName(file);
                this.setName(id, newFile);
            },
            onProgress: function (id, file, totalBytesUploaded, totalBytesTotal) {

            },
            onDelete: function (id) {
                try{div.parents('.wt-wrap').find('.uploaded-fn').val('');} catch(e){}
            },
            onError: function (event, id, name, errorReason, xhrOrXdr) {
                try{div.parents('.wt-wrap').find('.uploaded-fn').val('');} catch(e){}
            },
            onComplete : function(id, file){
                try{setUploadedFileField(div, t, file);} catch(e){}
            },
            onAllComplete : function(succeeded, failed){
                if(failed!=undefined && Array.isArray(failed) && failed.length >=1 ){
                   try{div.parents('.wt-wrap').find('.uploaded-fn').val('');} catch(e){}
               }
            }
        },
        messages: {
            typeError: 'Invalid extension detected in file, {file}.',
            emptyError: '{file} is Empty, Please upload a valid file'
        },
        request: {
            endpoint: uploadPath
        },
        deleteFile: {
            enabled: true,
            endpoint: uploadPath
        },
        chunking: {
            enabled: true,
            concurrent: {
                enabled: true
            },
            success: {
                endpoint: uploadPath+"?done"
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
    
    return true;
    
}


function setUploadedFileField(div, pfx, value)
{
    var input = div.parents('.qq-up-wrap-parent').find('.uploaded-fn');
    
    value = (value==undefined) ? '' : window.base+'swd/'+value;
    
    if(input.length == 0){
         div.append('<input name="uploaded-file-name'+pfx+'[]" value="'+value+'" class="uploaded-fn" type="hidden" />');
     } else {
         input.val(value);
     }
     
     return true;
}

