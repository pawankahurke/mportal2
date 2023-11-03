
var selectedPlatform, 
    platformClassName='swd-ic-it', 
    swdUIContainerIdName = 'swd-add-container';



$(document).ready(function(){
    
    var sui = new SwdUI('#'+window.swdUIContainerIdName);
    
    $("[data-toggle=tooltip").tooltip();
    $("[name=global]").bootstrapSwitch({'onText' : 'Yes','offText' : 'No'});
    $(".resume-download-switch,.propagation-switch,[name=delete-log-file],[name=status-message-box]").bootstrapSwitch({'onText' : 'No','offText' : 'Yes'});
    
    $('.swd-win-type').click(function(){
        var winType = $(this).attr('data-type');
        sui.resetWindowsSourceWrap(winType);
        sui.resetWindowsPreCheckWrap(winType);
    });
    
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
        $('#profwiz-basic').hide();
        $('#swd-add-container').show();
        
    });
    
    $('#add-more-patch').on('click', function(){
        sui.addMoreEvent();
    });
    
    
    $('.swd-ic-it').click('on', function(){
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
    });
    
    $('.'+sui.platformHandClassName).on('click', function(){
        var sP = $(this).attr('data-platform');
        if(sP!=undefined && sP!=''){
            window.selectedPlatform = sP;
            sui.loadWizard(sP);
            sui.loadWizardMenu(sP);
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
    });
    
    $('.'+sui.typeHandClassName).on('click', function(){
        //if($('[name=type]').val($(this).attr('data-type')));
    });
    
    
    $('#profwiz-basic').hide();
    $('#swd-add-container').show();
    
});


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
            'source-info' : 'Source Info',
            'package-info' : 'Package Info',
            'distribution-info' : 'Distribution Info',
            'finish' :  'Finish'
        },
        'ios' : {
            'source-info' : 'Source Info',
            'package-info' : 'Package Info',
            'distribution-info' : 'Distribution Info',
            'finish' :  'Finish'
        }
    };
    
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
    
    this.sourceTypes = {
        'shared-folder' : 1,
        'google-play-store' : 4,
        'apple-play-store' : 5,
        'nanoheal-play-store' : 5,
        'nanoheal-repository' : 2,
        'vendor-repository' : 3
    };
    
    
    this.gridValidationFunctions = {
        'windows' : ['validateStart','validateSource','validatePrecheck'],
        'linux' : ['validateStart','validateSource','validatePrecheck'],
        'mac' : ['validateStart','validateSource','validatePrecheck'],
        'android' : ['validateStart','validateSource','validatePrecheck'],
        'ios' : ['validateStart','validateSource','validatePrecheck'],
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
            'source' : ['.well','.my-btn-group','.wt-hand','.upload-to','.qq-up-wrap-parent'],
            'pre-check' : ['.well','.my-btn-group','.wt-hand','.registry-precheck-wrap'],
            'finish' :  {
                'distribute' : ['#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap'],
                'execute' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap',    '#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap'],
                'both' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap',    '#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap']
            }
        },
        'linux' : {
            'start' : [],
            'source' : ['.my-btn-group','.wt-hand','.upload-to','.qq-up-wrap-parent'],
            'pre-check' : ['.my-btn-group','.wt-hand','.registry-precheck-wrap'],
            'finish' :  {
                'distribute' : ['#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap'],
                'execute' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap',    '#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap'],
                'both' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap',    '#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap']
            }
        },
        'mac' : {
            'start' : [],
            'source' : ['.my-btn-group','.wt-hand','.upload-to','.qq-up-wrap-parent'],
            'pre-check' : ['.my-btn-group','.wt-hand','.registry-precheck-wrap'],
            'finish' :  {
                'distribute' : ['#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap'],
                'execute' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap',    '#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap'],
                'both' : ['#positive-wrap','#negative-wrap','#special-wrap', '#log-file-wrap','#default-wrap','#delete-log-file-wrap',    '#status-message-box-wrap','#message-box-text-wrap','#max-time-per-patch-wrap','#process-to-kill-wrap','#propagation-wrap']
            }
        },
        'android' : ['#s-si-access','#package_name-wrap','#package_description-wrap','#package_version-wrap','#s-pi-wrap'],
        'ios' : ['#s-si-access','#package_name-wrap','#package_description-wrap','#package_version-wrap','#s-pi-wrap'],
    };   
}

SwdUI.prototype.init = function(platform)
{
    //this.plaform = window.selectedPlatform;
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
    this.loadWindowsWizard();
    
    console.log(allPlatforms);
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
    }
    
    $('#'+this.changePlatformButtonId).removeClass(this.hide);
    $('#'+this.nextButtonId).removeClass(this.hide);
    
    return true;
}

SwdUI.prototype.resetScIc = function(wrap)
{
    var scIcGroups = $(this.container).find('.swd-ic-it-group'), scIcItems, x;
    
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
    $('.windows-grid').addClass(this.hide);
    
    return true;
}

SwdUI.prototype.loadMacWizard = function()
{
    this.loadWindowsWizard();
    $('.windows-grid').addClass(this.hide);
    
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

SwdUI.prototype.getSideBarGridClone = function(){
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

SwdUI.prototype.validateStart = function()
{
    console.log('started validateStart');
    return true;
}

SwdUI.prototype.validateSource = function()
{
    console.log('started validateSource');
    return true;
}

SwdUI.prototype.validatePrecheck = function()
{
    console.log('started validatePrecheck');
    return true;
}

SwdUI.prototype.populateSource = function()
{
    console.log('started populateSource');
    
    
    switch(this.platform){
        case 'windows':
            
            var windowsType = $(this.container).find('input[name=windows-type]').val();
            
            if(windowsType!=undefined && windowsType!=''){
                this.resetWindowsSourceWrap(windowsType);
            }
            
        break;  
        case 'linux':
        case 'mac':
            this.cleanSourceWrapGrids();
            this.cleanPreCheckGrids();
            this.createNewSourceGrid();
            this.initNewSourceGrid();
        break;
    }
    
    this.initVisibility();
    return true;
}

SwdUI.prototype.populatePreCheck = function()
{
    console.log('started populatePreCheck');
    
    switch(this.platform){
        case 'windows':
        break;
        case 'linux':
             this.createNewPreCheckGrid();
        break;
        case 'mac':
             this.createNewPreCheckGrid();
        break;
    }
    
    this.initVisibility();
    return true;
}

SwdUI.prototype.populateFinish = function()
{
    console.log('started populateFinish');
    this.initVisibility();
    return true;
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

SwdUI.prototype.initVisibility = function()
{
    var visibleFilters = this.visibleFieldFilters[this.platform], target, targetWrap,
        idx = this.getActiveIndex();
        idx++;
        
    targetWrap = $(this.container).find('.'+this.wrapClassName).eq(idx);
    
    if(targetWrap.attr('data-wrap-name')!=undefined)
    {   
        visibleFilters = visibleFilters[targetWrap.attr('data-wrap-name')];
        
        switch(targetWrap.attr('data-wrap-name')){
            case 'finish':
                var packageType = this.getSelectedPackageType();
                
                if(packageType!=undefined && packageType!=''){
                    visibleFilters = visibleFilters[packageType];
                } else {
                    visibleFilters =  false;
                }

                this.resetWizardWrapsVisibiity('#finish-wrap');
            break
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
    var defaultHides = $(this.container).find('.'+this.defaultHide);
    console.log(only);
    if(only!=undefined){
        defaultHides = $(this.container).find(only+' .'+this.defaultHide)
    }
    
    console.log('in resetWizardWrapsVisibiity--');
    for(var i=0;i<defaultHides.length;i++){
        if(!defaultHides.eq(i).hasClass(this.hide)){
            defaultHides.eq(i).addClass(this.hide);
        }
    }
}

SwdUI.prototype.next = function()
{
    var activeIndex = this.getActiveIndex(), iX,
        gFx = this.gridValidationFunctions,
        gFx = (gFx[this.platform]!=undefined) ? gFx[this.platform] :  false,
        gPx = this.gridPopulateFunctions,
        gPx = (gPx[this.platform]!=undefined) ? gPx[this.platform] :  false,
        pWd = (this.configurations[this.platform]!=undefined) ?  this.configurations[this.platform] : false;
        
    if(pWd)
    {
        var validated = true;
        
        if(gFx){
            try{
                validated = this[gFx[activeIndex]].call();
            } catch(e){}
        }

        if (validated)
        {
            var continueFlow = true;

            try{
                validated = this[gPx[activeIndex]]();
            } catch(e){}

        }
        
        if(continueFlow){
            iX = activeIndex + 1;
            var oKs = Object.keys(pWd), nextWrap = oKs[iX];
            
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
                $(this.container).find('#'+this.changePlatformButtonId).removeClass(this.hide);
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
    
    var grid = parent.find('.row.mt-z .col-sm-6');
    
    for(var i=0;i<grid.length;i++){
        grid.eq(i).find('.qq-up-wrap').html('');
    }
    
    for(var i=1;i<grid.length;i++){
        grid.eq(i).remove();
    }
    
    return true;
}

SwdUI.prototype.initNewSourceGrid = function(index)
{
    var parent = $(this.container).find('.eachPWrap[data-wrap-name=source]');
    var grid = (index==undefined || index>0) ? parent.find('.row.mt-z .col-sm-6') : parent.find('.row.mt-z').eq(index).find('.col-sm-6'), 
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
    var mtZ = parent.find('.row.mt-z');
    
    if(mtZ.length > 0){
        for(var i=1;i<mtZ.length;i++){
            mtZ.eq(i).remove();
        }
    }
    
    var grid = parent.find('.row.mt-x .col-sm-6');
    
    for(var i=1;i<grid.length;i++){
        grid.eq(i).remove();
    }
    
    return true;
}

SwdUI.prototype.createNewSourceGrid = function(bit)
{
    var parent = $(this.container).find('.eachPWrap[data-wrap-name=source]'), idx;
    var mtZ = parent.find('.row.mt-z'),
        clone = mtZ.eq(0).find('.col-sm-6').eq(0).clone();

    var totalGridRows = mtZ.length;
    var lastGridRow = mtZ.eq((totalGridRows - 1)),
        lastGrid = lastGridRow.find('.col-sm-6'),
        totalGrid = lastGrid.length, idx;

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
    
    var gridHtml = '<div class="col-sm-6">'+clone.html()+'</div>';
    
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


SwdUI.prototype.createNewPreCheckGrid = function(bit)
{
    var parent = $(this.container).find('.eachPWrap[data-wrap-name=pre-check]'),
        mtX = parent.find('.row.mt-x'),
        clone = mtX.eq(0).find('.col-sm-6').eq(0).clone();
    
    var totalGridRows = mtX.length;
    var lastGridRow = mtX.eq((totalGridRows - 1)),
        lastGrid = lastGridRow.find('.col-sm-6'),
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
        bitField = (bit==undefined || bit=='') ? '' :bit ,
        nestify =  (bit==undefined || bit=='') ? '' : '[]';
    
    for(i=0;i<valFields.length;i++){
       valFields.eq(i).attr('name', valFields.eq(i).attr('name')+'-'+bitField+nestify)
    }
    
    var gridHtml = '<div class="col-sm-6">'+clone.html()+'</div>';
    
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

//SwdUI.prototype.createNewPreCheckGrid = function(bit)
//{
//    var parent = $(this.container).find('.eachPWrap[data-wrap-name=pre-check]');
//    var grid = parent.find('.row.mt-x .col-sm-6'), clone = grid.eq(0).clone(), 
//        totalGrid = grid.length, idx;
//    
//    for(var i=0;i<(clone.find('.my-btn-group .wt-hand').length);i++){
//       idx = randomString(18);
//       clone.find('.my-btn-group .wt-hand').eq(i).attr('data-bs-target', idx);
//       clone.find('.wt-group .wt-wrap').eq(i).attr('id', idx);
//    }
//    
//    if(bit!=undefined && bit!=''){
//        clone.find('.well').text(bit+' Bit').removeClass(this.hide);
//    }
//    
//    var valFields = clone.find('input,select'), 
//        bitField = (bit==undefined || bit=='') ? '' :bit ,
//        nestify =  (bit==undefined || bit=='') ? '' : '[]';
//    
//    for(i=0;i<valFields.length;i++){
//       valFields.eq(i).attr('name', valFields.eq(i).attr('name')+'-'+bitField+nestify)
//    }
//    
//    var gridHtml = '<div class="col-sm-6">'+clone.html()+'</div>';
//    grid.eq((totalGrid - 1)).after(gridHtml);
//
//    return true;
//}

SwdUI.prototype.addMoreSectionInSourceWrap = function(bit)
{
    var sourceParent = $(this.container).find('.'+this.wrapClassName+'[data-wrap-name=source]'),
        mtZ = sourceParent.find('.mt-z.row'),
        c = mtZ.length, nHtm;

        nHtm = '<div class="mt-z row"></div>';
        mtZ.eq((c - 1)).after(nHtm);
        
        var type = $(this.container).find('input[name=windows-type]').val();
        
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
        
        this.initNewSourceGrid(c);
    }
    
    return true;
}



SwdUI.prototype.addMoreSectionInPreCheckWrap = function()
{
    var sourceParent = $(this.container).find('.'+this.wrapClassName+'[data-wrap-name=pre-check]'),
        mtX = sourceParent.find('.mt-x.row'),
        c = mtX.length, nHtm;

        nHtm = '<div class="mt-x row"></div>';
        mtX.eq((c - 1)).after(nHtm);
        
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
        
        return true;
}

SwdUI.prototype.addMoreOptionsSection = function()
{
    var optionsSection = $(this.container).find('.eachPWrap[data-wrap-name=finish] .options-section'),
        optionsSectionClone = optionsSection.clone(),
        htm = '<div class="container my-container options-section">'+optionsSectionClone.html()+'</div>';
        
    optionsSection.eq((optionsSection.length - 1)).after(htm);
    
    return true;
}

SwdUI.prototype.addMoreEvent = function()
{
    this.addMoreSectionInSourceWrap();
    this.addMoreSectionInPreCheckWrap();
    this.addMoreOptionsSection();
    
    return true;
}


function loadUploadWidget(id, template)
{
    var manualUploader = new qq.FineUploader({
        element: document.getElementById(id),
        template: template,
        multiple : false,
        callbacks: {
            onCancel: function (id, file) {

            },
            onSubmit: function (id, file) {


            },
            onProgress: function (id, file, totalBytesUploaded, totalBytesTotal) {

            },
            onDelete: function (id) {

            },
            onError: function (event, id, name, errorReason, xhrOrXdr) {

            },
            onComplete : function(){

            }
        },
        messages: {
            typeError: 'Invalid extension detected in file, {file}.',
            emptyError: '{file} is Empty, Please upload a valid file'
        },
        request: {
            endpoint: "https://qastaging.nanoheal.com/dv8/swd/vendor/fineuploader/php-traditional-server/endpoint.php"
        },
        deleteFile: {
            enabled: true,
            endpoint: "https://qastaging.nanoheal.com/dv8/swd/vendor/fineuploader/php-traditional-server/endpoint.php"
        },
        chunking: {
            enabled: true,
            concurrent: {
                enabled: true
            },
            success: {
                endpoint: "https://qastaging.nanoheal.com/dv8/swd/vendor/fineuploader/php-traditional-server/endpoint.php?done"
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
