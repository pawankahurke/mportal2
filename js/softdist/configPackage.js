function seePreInstCheck() {
    var n = $("#preInsCheck2:checked").length;
    if (n > 0) {
        $("#preInsCheck2").val("1");
        $(".preinstcheck2").prop("checked", false);
        $(".insPreCheck").show();
        $("#distributionPreCheckDivDE").hide();
        $('#preinstcheck2_div').show();
    } else {
        $('#preinstcheck2_div').hide();
        $("#preInsCheck2").val("0");
        $(".insPreCheck,.preinstcheckFields").hide();
        $(".preinstcheckFields").find('input').val('');
        $(".preinstcheck2").removeAttr('checked');
    }
}

function seeValidityCheck() {
    var n = $("#validityCheck2:checked").length;

    if (n > 0) {
        $('#validityCheck2_div').show();
        $("#validityCheck2").val(1);
        $(".validationRow").show();
        var rID = $("input:radio[class=transferValidation]:checked").attr('id');
        $("#" + rID).click();
    } else {
        $('#validityCheck2_div').hide();
        $("#validityCheck2").val(0);
        $("input:radio[class=transferValidation]:checked").removeAttr('checked');
        $(".validationRow").hide();
        $("#rootKey12").val(0);
        $("#subKey12").val('');
        $(".registryRow").hide();
        $("#filePath12").val('');
        $(".fileRow").hide();
    }
}

function seePreInstCheck_32exec() {
    var n = $("#preInsCheck2_32exec:checked").length;
    if (n > 0) {
        $("#distributionPreCheckDivDE_32exec").show();
        $("#preInsCheck2_32exec").val("1");
        $(".preinstcheck2_32exec").prop("checked", false);  
        $('#preinstcheck2_32exec_div').show(); 
        $('.insPreCheck_32exec').show();
        $(".pfile2_32exec.preinstcheckFields_32exec").show();
    } else {
        $('#preinstcheck2_32exec_div').hide();
        $("#preInsCheck2_32exec").val("0");
        $(".insPreCheck_32exec,.preinstcheckFields_32exec").hide();
        // $(".preinstcheckFields_32exec").find('input').val('');
        $(".preinstcheckFields_32exec").removeAttr('checked');
    }
}

function seeValidityCheck_32exec() {
    var n = $("#validityCheck2_32exec:checked").length;

    if (n > 0) {
        $('#validity_32exec_div').show();
        $("#validityCheck2_32exec").val(1);
        $(".validationRow_32exec").show();
        var rID = $("input:radio[class=transferValidation_32exec]:checked").attr('id');
        $("#" + rID).click();
    } else {
        $('#validity_32exec_div').hide();
        $("#validityCheck2_32exec").val(0);
        $("input:radio[class=transferValidation_32exec]:checked").removeAttr('checked');
        $(".validationRow_32exec").hide();
        $("#rootKey12_32exec").val(0);
        $("#subKey12_32exec").val('');
        $(".registryRow_32exec").hide();
        $("#filePath12_32exec").val('');
        $(".fileRow_32exec").hide();
    }
}

function checkSelType32Depl(type) {
    if(type == 'precheck'){
        var val = $('#preinstcheck2').val();
        if (val == '0') {
            $('.pRegistry2').hide();
            $('.pSoftware2').hide();
            $("#distributionPreCheckDivDE").show();
            $('.pfile2').show();
        } else if (val == '1') {
            $('.pRegistry2').hide();
            $('.pSoftware2').show();
            $("#distributionPreCheckDivDE").hide();
            $('.pfile2').hide();
        } else if (val == '2') {
            $('.pRegistry2').show();
            $('.pSoftware2').hide();
            $("#distributionPreCheckDivDE").show();
            $('.pfile2').hide();
        }
    }else{
        var val = $('#validity').val();
        if (val == '0') {
            $('#rccs-fp1').show();
            $('.registryRow').hide();
        } else if (val == '1') {
            $('#rccs-fp1').hide();
            $('.registryRow').show();
        }
    }
    
}

function checkSelType32Exec(type) {
    if(type == 'precheck'){
        var val = $('#preinstcheck2_32exec').val();
        if (val == '0') {
            $('.pRegistry2_32exec').hide();
            $('.pSoftware2_32exec').hide();
            $("#distributionPreCheckDivDE_32exec").show();
            $('.pfile2_32exec').show();
        } else if (val == '1') {
            $('.pRegistry2_32exec').hide();
            $('.pSoftware2_32exec').show();
            $("#distributionPreCheckDivDE_32exec").hide();
            $('.pfile2_32exec').hide();
        } else if (val == '2') {
            $('.pRegistry2_32exec').show();
            $('.pSoftware2_32exec').hide();
            $("#distributionPreCheckDivDE_32exec").show();
            $('.pfile2_32exec').show();
        }
    }else{
        var val = $('#validity_32exec').val();
        if (val == '0') {
            $('#rccs-fp1_32exec').show();
            $('.registryRow_32exec').hide();
        } else if (val == '1') {
            $('#rccs-fp1_32exec').hide();
            $('.registryRow_32exec').show();
        }
    }
    
}

function seePreInstCheck_64depl() {
    var n = $("#preInsCheck2_64depl:checked").length;
    if (n > 0) {
        $("#preInsCheck2_64depl").val("1");
        $("#preinstcheck2_64depl_div").prop("checked", false);
        $("#preinstcheck2_64depl_div").show();
        $(".insPreCheck_64depl").show();
        $("#distributionPreCheckDivDE_64depl").hide();
    } else {
        $("#preInsCheck2_64depl").val("0");
        $("#preinstcheck_64depl").removeAttr('checked');
        $("#preinstcheck2_64depl_div").hide();
        $(".insPreCheck_64depl,.preinstcheckFields_64depl").hide();
        $(".preinstcheckFields_64depl").find('input').val('');
    }
    
}


function seeValidityCheck_64depl() {
    var n = $("#validityCheck2_64depl:checked").length;
    if (n > 0) {
        $('#validity_64depl_div').show();
        $("#validityCheck2_64depl").val(1);
        $(".validationRow_64depl").show();
        var rID = $("input:radio[class=transferValidation_64depl]:checked").attr('id');
        $("#" + rID).click();
    } else {
        $('#validity_64depl_div').hide();
        $("#validityCheck2_64depl").val(0);
        $("input:radio[class=transferValidation_64depl]:checked").removeAttr('checked');
        $(".validationRow_64depl").hide();
        $("#rootKey12_64depl").val(0);
        $("#subKey12_64depl").val('');
        $(".registryRow_64depl").hide();
        $("#filePath12_64depl").val('');
        $(".fileRow_64depl").hide();
    }
}

function checkSelType64Depl(type) {
    if(type == 'precheck'){
        var val = $('#preinstcheck2_64depl').val();
        if (val == '0') {
            $('.pRegistry2_64depl').hide();
            $('.preinstcheckFields_64depl').hide();
            $("#distributionPreCheckDivDE_64depl").show();
            $('.pfile2_64depl').show();
        } else if (val == '1') {
            $('.pRegistry2_64depl').hide();
            $('.pSoftware2_64depl').show();
            $("#distributionPreCheckDivDE_64depl").hide();
            $('.pfile2_64depl').hide();
        } else if (val == '2') {
            $('.pRegistry2_64depl').show();
            $('.preinstcheckFields_64depl').hide();
            $("#distributionPreCheckDivDE_64depl").show();
            $('.pfile2_64depl').show();
        }
    }else{
        var val = $('#validity_64depl').val();
        if (val == '0') {
            $('#rccs-fp1_64depl').show();
            $('.registryRow_64depl').hide();
        } else if (val == '1') {
            $('#rccs-fp1_64depl').hide();
            $('.registryRow_64depl').show();
        }
    }
}

function seePreInstCheck_64exec() {
    var n = $("#preInsCheck2_64exec:checked").length;

    if (n > 0) {
        $("#preInsCheck2_64exec").val("1");
        $(".preinstcheck2_64exec").prop("checked", false);
        console.log('$(".insPreCheck_64exec").show();')
        $("#preinstcheck2_64exec_div").show(); 
        $("#distributionPreCheckDivDE_64exec").show();
        $(".insPreCheck_64exec").show();
        $('.pfile2_64exec.preinstcheckFields_64exec').show()
    } else {
        $('#preinstcheck2_64exec_div').hide();
        $("#preInsCheck2_64exec").val("0");
        $(".insPreCheck_64exec,.preinstcheckFields_64exec").hide();
        // $(".preinstcheckFields_64exec").find('input').val('');
        $(".preinstcheck_64exec").removeAttr('checked');
    }
    
    
}


function seeValidityCheck_64exec() {
    var n = $("#validityCheck2_64exec:checked").length;
    if (n > 0) {
        $('#validity_64exec_div').show();
        $("#validityCheck2_64exec").val(1);
        $(".validationRow_64exec").show();
        var rID = $("input:radio[class=transferValidation_64exec]:checked").attr('id');
        $("#" + rID).click();
    } else {
        $('#validity_64exec_div').hide();
        $("#validityCheck2_64exec").val(0);
        $("input:radio[class=transferValidation_64exec]:checked").removeAttr('checked');
        $(".validationRow_64exec").hide();
        $("#rootKey12_64exec").val(0);
        $("#subKey12_64exec").val('');
        $(".registryRow_64exec").hide();
        $("#filePath12_64exec").val('');
        $(".fileRow_64exec").hide();
    }
    
}

function checkSelType64exec(type) {
    if(type == 'precheck'){
        var val = $('#preinstcheck2_64exec').val();
        if (val == '0') {
            $('.pRegistry2_64exec').hide();
            $('.preinstcheckFields_64exec').hide();
            $("#distributionPreCheckDivDE_64exec").show();
            $('.pfile2_64exec').show();
        } else if (val == '1') {
            $('.pRegistry2_64exec').hide();
            $('.pSoftware2_64exec').show();
            $("#distributionPreCheckDivDE_64exec").hide();
            $('.pfile2_64exec').hide();
        } else if (val == '2') {
            $('.pRegistry2_64exec').show();
            $('.preinstcheckFields_64exec').hide();
            $("#distributionPreCheckDivDE_64exec").show();
            $('.pfile2_64exec').show();
        }
    }else{
        var val = $('#validity_64exec').val();
        if (val == '0') {
            $('#rccs-fp1_64exec').show();
            $('.registryRow_64exec').hide();
        } else if (val == '1') {
            $('#rccs-fp1_64exec').hide();
            $('.registryRow_64exec').show();
        }
    }
}

function seePreInstCheckReset(id){
    var nid = '#preInsCheck2'+id;
    var n = $(nid+":checked").length;
    if (n > 0) {
        $("#preInsCheck2"+id).val("1");
        $(".preinstcheck2"+id).prop("checked", false);
        $(".insPreCheck"+id).show();
        $("#distributionPreCheckDivDE"+id).hide();
        $('#preinstcheck2'+id).show();
        $('#preInsCheck2_div'+id).show();
    } else {
        $('#preInsCheck2_div'+id).hide();
        $('#preinstcheck2'+id).hide();
        $("#preInsCheck2"+id).val("0");
        $(".insPreCheck"+id).hide();
        $(".preinstcheckFields"+id).hide();
        $(".preinstcheckFields"+id).find('input').val('');
        $(".preinstcheck2"+id).removeAttr('checked');
    }
}

function seeValidityCheckReset(id) {
    var nid = '#validityCheck2'+id;
    var n = $(nid+":checked").length;
    if (n > 0) {
        $('#validityCheck2_div'+id).show();
        $("#validityCheck2"+id).val(1);
        $(".validationRow"+id).show();
    } else {
        $('#validityCheck2_div'+id).hide();
        $("#validityCheck2"+id).val(0);
        $(".validationRow"+id).hide();
        $("#rootKey12"+id).val(0);
        $("#subKey12"+id).val('');
        $(".registryRow"+id).hide();
        $("#filePath12"+id).val('');
        $(".fileRow"+id).hide();
    }
}

function checkSelTypeDepl(type,id) {
    if(type == 'precheck'){
        var val = $('#preinstcheck2'+id).val();
        if (val == '0') {
            $('.pRegistry2'+id).hide();
            $('.preinstcheckFields'+id).hide();
            $("#distributionPreCheckDivDE"+id).show();
            $('.pfile2'+id).show();
        } else if (val == '1') {
            $('.pRegistry2'+id).hide();
            $('.pSoftware2'+id).show();
            $("#distributionPreCheckDivDE"+id).hide();
            $('.pfile2_64exec').hide();
        } else if (val == '2') {
            $('.pRegistry2'+id).show();
            $('.preinstcheckFields'+id).hide();
            $("#distributionPreCheckDivDE"+id).show();
            $('.pfile2'+id).show();
        }
    }else{
        var val = $('#validity'+id).val();
        if (val == '0') {
            $('#rccs-fp1'+id).show();
            $('.registryRow'+id).hide();
        } else if (val == '1') {
            $('#rccs-fp1'+id).hide();
            $('.registryRow'+id).show();
        }
    }
}

function render32DivData(line1,type){
    if(type == 'line1'){
                    $('#enable_32val').val(line1.line1_enable);
                    $('#url_32val').val(line1.line1_url);
                    if(line1.line1_validity){
                        $('#validityCheck2').prop('checked',true);
                        $('#preInsCheck2').prop('checked',false);
                        seeValidityCheck();
                        $("#validityCheck2").val(line1.line1_validity).change();
                        $('#filePath12').val(line1.line1_filepath);
                        ('select[name=rootKey12]').val(line1.line1_rootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=vTypeDE]').val(line1.line1_vTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#vValueDE').val(line1.line1_vValueDE);
                    }else{
                        $('#preInsCheck2').prop('checked',true);
                        $('#validityCheck2').prop('checked',false);
                        seePreInstCheck();
                        $("#preinstcheck2").val(line1.line1_typeoffile).change();
                        $('#pSoftName22').val(line1.line1_psoftname);
                        $('#pSoftVer22').val(line1.line1_pSoftVer);
                        $('#pfilePath22').val(line1.line1_pfilepath);
                        if(line1.line1_pexecprecheck == '0'){
                            $('#pExecPreCheckValDE0').prop('checked',true);
                            $('#pExecPreCheckValDE1').prop('checked',false);
                        }else{
                            $('#pExecPreCheckValDE1').prop('checked',true);
                            $('#pExecPreCheckValDE0').prop('checked',false);
                        }
                        $('select[name=prootKey22]').val(line1.line1_prootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=pTypeDE]').val(line1.line1_pTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#pValueDE').val(line1.line1_pValueDE);
                        
                    }
                    $('select[name=session_32_val]').val(line1.line1_session);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=api_32_val]').val(line1.line1_api);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=preinstcheck2]').val(line1.line1_typeoffile);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=validity]').val(line1.line1_validity);
                    $('.selectpicker').selectpicker('refresh');
                    $('#32cmnd_line').val($.trim(line1.line1_cmndline));
                    $('#32patchDep_val').val(line1.line1_patchDep);
                }else if(type == 'line2'){
                    $('#enable_32valexec').val(line1.line2_enable);
                    $('#url_32valexec').val(line1.line2_url);
                    if(line1.line2_validity){
                        $('#validityCheck2_32valexec').prop('checked',true);
                        $('#preInsCheck2_32valexec').prop('checked',false);
                        seeValidityCheck_32exec();
                        $("#validityCheck2_32exec").val(line1.line2_validity).change();
                        $('#filePath12_32exec').val(line1.line2_filepath);
                        ('select[name=rootKey12_32exec]').val(line1.line2_rootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=vTypeDE_32exec]').val(line1.line2_vTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#vValueDE_32exec').val(line1.line2_vValueDE);
                    }else{
                        $('#preinstcheck2_32exec').prop('checked',true);
                        $('#validityCheck2_32valexec').prop('checked',false);
                        seePreInstCheck_32exec();
                        $("#preinstcheck2_32exec").val(line1.line2_typeoffile).change();
                        $('#pSoftName22_32exec').val(line1.line2_psoftname);
                        $('#pSoftVer22_32exec').val(line1.line2_pSoftVer);
                        $('#pfilePath22_32exec').val(line1.line2_pexecprecheck);
                        if(line1.line2_pfilepath) {
                            $('#preInsCheck2_32exec').prop('checked',true);
                        }
                        if(line1.line2_pexecprecheck == '0'){
                            $('#pExecPreCheckValDE0_32exec').prop('checked',true);
                            $('#pExecPreCheckValDE1_32exec').prop('checked',false);
                        }else{
                            $('#pExecPreCheckValDE1_32exec').prop('checked',true);
                            $('#pExecPreCheckValDE0_32exec').prop('checked',false);
                        }
                        $('select[name=prootKey22_32exec]').val(line1.line2_prootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=pTypeDE_32exec]').val(line1.line2_pTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#pValueDE_32exec').val(line1.line2_pValueDE);
                        
                    }
                    $('select[name=session_32_valexec]').val(line1.line2_session);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=api_32_valexec]').val(line1.line2_api);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=preinstcheck2_32exec]').val(line1.line2_typeoffile);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=validity_32exec]').val(line1.line2_validity);
                    $('.selectpicker').selectpicker('refresh');
                    $('#32cmndline_execute').val(line1.line2_cmndline);
                    $('#32patchExec_val').val(line1.line2_patchDep);
                }
}

function render64DivData(line1,type){
    if(type == 'line1'){
                    $('#enable_64val').val(line1.line1_enable);
                    $('#url_64val').val(line1.line1_url);
                    if(line1.line1_validity){
                        $('#validityCheck2_64depl').prop('checked',true);
                        $('#preinstcheck2_64depl').prop('checked',false);
                        seeValidityCheck_64depl();
                        $("#validityCheck2_64depl").val(line1.line1_validity).change();
                        $('#filePath12_64depl').val(line1.line1_filepath);
                        ('select[name=rootKey12_64depl]').val(line1.line1_rootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=vTypeDE_64depl]').val(line1.line1_vTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#vValueDE_64depl').val(line1.line1_vValueDE);
                    }else{
                        $('#preinstcheck2_64depl').prop('checked',true);
                        $('#validityCheck2_64depl').prop('checked',false);
                        seePreInstCheck_64depl();
                        $("#preinstcheck2_64depl").val(line1.line1_typeoffile).change();
                        $('#pSoftName22_64depl').val(line1.line1_psoftname);
                        $('#pSoftVer22_64depl').val(line1.line1_pSoftVer);
                        $('#pfilePath22_64depl').val(line1.line1_pfilepath);
                        if(line1.line1_pfilepath) {
                            $('#preInsCheck2_64depl').prop('checked',true);
                        }
                        if(line1.line1_pexecprecheck == '0'){
                            $('#pExecPreCheckValDE0_64depl').prop('checked',true);
                            $('#pExecPreCheckValDE1_64depl').prop('checked',false);
                        }else{
                            $('#pExecPreCheckValDE1_64depl').prop('checked',true);
                            $('#pExecPreCheckValDE0_64depl').prop('checked',false);
                        }
                        $('select[name=prootKey22_64depl]').val(line1.line1_prootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=pTypeDE_64depl]').val(line1.line1_pTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#pValueDE_64depl').val(line1.line1_pValueDE);
                        
                    }
                    $('select[name=session_64_val]').val(line1.line1_session);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=api_64_val]').val(line1.line1_api);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=preinstcheck2_64depl]').val(line1.line1_typeoffile);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=validityCheck2_64depl]').val(line1.line1_validity);
                    $('.selectpicker').selectpicker('refresh');
                    $('#cmndline_64').val(line1.line1_cmndline);
                    $('#64patchDepl_val').val(line1.line1_patchDep);
                }else if(type == 'line2'){
                    $('#enable_64valexec').val(line1.line2_enable);
                    $('#url_64valexec').val(line1.line2_url);
                    if(line1.line2_validity){
                        $('#validityCheck2_64exec').prop('checked',true);
                        $('#preinstcheck2_64exec').prop('checked',false);
                        seeValidityCheck_64exec();
                        $("#validityCheck2_64exec").val(line1.line2_validity).change();
                        $('#filePath12_64exec').val(line1.line2_filepath);
                        ('select[name=rootKey12_64exec]').val(line1.line2_rootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=vTypeDE_64exec]').val(line1.line2_vTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#vValueDE_64exec').val(line1.line2_vValueDE);
                    }else{
                        $('#preinstcheck2_64exec').prop('checked',true);
                        $('#validityCheck2_64exec').prop('checked',false);
                        seePreInstCheck_64exec();
                        $("#preinstcheck2_64exec").val(line1.line2_typeoffile).change();
                        $('#pSoftName22_64exec').val(line1.line2_psoftname);
                        $('#pSoftVer22_64exec').val(line1.line2_pSoftVer);
                        if(line1.line2_pfilepath) {
                            $('#preInsCheck2_64exec').prop('checked',true);
                        }
                        $('#pfilePath22_64exec').val(line1.line2_pfilepath);
                        if(line1.line1_pexecprecheck == '0'){
                            $('#pExecPreCheckValDE0_64exec').prop('checked',true);
                            $('#pExecPreCheckValDE1_64exec').prop('checked',false);
                        }else{
                            $('#pExecPreCheckValDE1_64exec').prop('checked',true);
                            $('#pExecPreCheckValDE0_64exec').prop('checked',false);
                        }
                        $('select[name=prootKey22_64exec]').val(line1.line2_prootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=pTypeDE_64exec]').val(line1.line2_pTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#pValueDE_64exec').val(line1.line2_pValueDE);
                        
                    }
                    $('select[name=session_64_valexec]').val(line1.line2_session);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=api_64_valexec]').val(line1.line2_api);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=preinstcheck2_64exec]').val(line1.line2_typeoffile);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=validityCheck2_64exec]').val(line1.line2_validity);
                    $('.selectpicker').selectpicker('refresh');
                    $('#64cmndline_execute_64').val(line1.line2_cmndline);
                    $('#64patchExec_val').val(line1.line2_patchDep);
                }
}

function renderResetDivData(line1,type,linetype){
   if(linetype == 'line5_32' || linetype == 'line5_64'){
       $('#enableval'+type).val(line1.line5_enable);
                    $('#urlval'+type).val(line1.line5_url);
                    if(line1.line1_validity){
                        $('#validityCheck2'+type).prop('checked',true);
                        $('#preinstcheck2'+type).prop('checked',false);
                        seeValidityCheckReset(type);
                        $('#validityCheck2'+type).val(line1.line5_validity).change();
                        $('#filePath12'+type).val(line1.line5_filepath);
                        ('select[name=rootKey12'+type+']').val(line1.line5_rootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=vTypeDE'+type+']').val(line1.line5_vTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#vValueDE'+type).val(line1.line5_vValueDE);
                    }else{
                        $('#preinstcheck2'+type).prop('checked',true);
                        $('#validityCheck2'+type).prop('checked',false);
                        seePreInstCheckReset(type);
                        $("#preinstcheck2"+type).val(line1.line5_typeoffile).change();
                        $('#pSoftName22'+type).val(line1.line5_psoftname);
                        $('#pSoftVer22'+type).val(line1.line5_pSoftVer);
                        $('#pfilePath22'+type).val(line1.line5_pfilepath);
                        if(line1.line5_pexecprecheck == '0'){
                            $('#pExecPreCheckValDE0'+type).prop('checked',true);
                            $('#pExecPreCheckValDE1'+type).prop('checked',false);
                        }else{
                            $('#pExecPreCheckValDE1'+type).prop('checked',true);
                            $('#pExecPreCheckValDE0'+type).prop('checked',false);
                        }
                        $('select[name=prootKey22'+type+']').val(line1.line5_prootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=pTypeDE'+type+']').val(line1.line5_pTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#pValueDE'+type).val(line1.line5_pValueDE);
                        
                    }
                    $('select[name=sessionval'+type+']').val(line1.line5_session);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=apival'+type+']').val(line1.line5_api);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=preinstcheck2'+type+']').val(line1.line5_typeoffile);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=validityCheck2'+type+']').val(line1.line5_validity);
                    $('.selectpicker').selectpicker('refresh');
                    $('#cmndlineval'+type).val(line1.line5_cmndline);
                    $('#patchdepval'+type).val(line1.line5_patchDep);
   }else if(linetype == 'line6_32' || linetype == 'line6_64'){
       $('#enableval'+type).val(line1.line6_enable);
                    $('#urlval'+type).val(line1.line6_url);
                    if(line1.line6_validity){
                        $('#validityCheck2'+type).prop('checked',true);
                        $('#preinstcheck2'+type).prop('checked',false);
                        seeValidityCheckReset(type);
                        $('#validityCheck2'+type).val(line1.line6_validity).change();
                        $('#filePath12'+type).val(line1.line6_filepath);
                        ('select[name=rootKey12'+type+']').val(line1.line6_rootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=vTypeDE'+type+']').val(line1.line6_vTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#vValueDE'+type).val(line1.line6_vValueDE);
                    }else{
                        $('#preinstcheck2'+type).prop('checked',true);
                        $('#validityCheck2'+type).prop('checked',false);
                        seePreInstCheckReset(type);
                        $("#preinstcheck2"+type).val(line1.line6_typeoffile).change();
                        $('#pSoftName22'+type).val(line1.line6_psoftname);
                        $('#pSoftVer22'+type).val(line1.line6_pSoftVer);
                        $('#pfilePath22'+type).val(line1.line6_pfilepath);
                        if(line1.line6_pexecprecheck == '0'){
                            $('#pExecPreCheckValDE0'+type).prop('checked',true);
                            $('#pExecPreCheckValDE1'+type).prop('checked',false);
                        }else{
                            $('#pExecPreCheckValDE1'+type).prop('checked',true);
                            $('#pExecPreCheckValDE0'+type).prop('checked',false);
}
                        $('select[name=prootKey22'+type+']').val(line1.line6_prootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=pTypeDE'+type+']').val(line1.line6_pTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#pValueDE'+type).val(line1.line6_pValueDE);
                        
                    }
                    $('select[name=sessionval'+type+']').val(line1.line6_session);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=apival'+type+']').val(line1.line6_api);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=preinstcheck2'+type+']').val(line1.line6_typeoffile);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=validityCheck2'+type+']').val(line1.line6_validity);
                    $('.selectpicker').selectpicker('refresh');
                    $('#cmndlineval'+type).val(line1.line6_cmndline);
                    $('#patchdepval'+type).val(line1.line6_patchDep);
   }else if(linetype == 'line3_32' || linetype == 'line3_64'){
       $('#enableval'+type).val(line1.line3_enable);
                    $('#urlval'+type).val(line1.line3_url);
                    if(line1.line6_validity){
                        $('#validityCheck2'+type).prop('checked',true);
                        $('#preinstcheck2'+type).prop('checked',false);
                        seeValidityCheckReset(type);
                        $('#validityCheck2'+type).val(line1.line3_validity).change();
                        $('#filePath12'+type).val(line1.line3_filepath);
                        ('select[name=rootKey12'+type+']').val(line1.line3_rootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=vTypeDE'+type+']').val(line1.line3_vTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#vValueDE'+type).val(line1.line3_vValueDE);
                    }else{
                        $('#preinstcheck2'+type).prop('checked',true);
                        $('#validityCheck2'+type).prop('checked',false);
                        seePreInstCheckReset(type);
                        $("#preinstcheck2"+type).val(line1.line3_typeoffile).change();
                        $('#pSoftName22'+type).val(line1.line3_psoftname);
                        $('#pSoftVer22'+type).val(line1.line3_pSoftVer);
                        $('#pfilePath22'+type).val(line1.line3_pfilepath);
                        if(line1.line3_pexecprecheck == '0'){
                            $('#pExecPreCheckValDE0'+type).prop('checked',true);
                            $('#pExecPreCheckValDE1'+type).prop('checked',false);
                        }else{
                            $('#pExecPreCheckValDE1'+type).prop('checked',true);
                            $('#pExecPreCheckValDE0'+type).prop('checked',false);
                        }
                        $('select[name=prootKey22'+type+']').val(line1.line3_prootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=pTypeDE'+type+']').val(line1.line3_pTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#pValueDE'+type).val(line1.line3_pValueDE);
                        
                    }
                    $('select[name=sessionval'+type+']').val(line1.line3_session);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=apival'+type+']').val(line1.line3_api);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=preinstcheck2'+type+']').val(line1.line3_typeoffile);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=validityCheck2'+type+']').val(line1.line3_validity);
                    $('.selectpicker').selectpicker('refresh');
                    $('#cmndlineval'+type).val(line1.line3_cmndline);
                    $('#patchdepval'+type).val(line1.line3_patchDep);
   }else if(linetype == 'line4_32' || linetype == 'line4_64'){
       $('#enableval'+type).val(line1.line4_enable);
                    $('#urlval'+type).val(line1.line4_url);
                    if(line1.line4_validity){
                        $('#validityCheck2'+type).prop('checked',true);
                        $('#preinstcheck2'+type).prop('checked',false);
                        seeValidityCheckReset(type);
                        $('#validityCheck2'+type).val(line1.line4_validity).change();
                        $('#filePath12'+type).val(line1.line4_filepath);
                        ('select[name=rootKey12'+type+']').val(line1.line4_rootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=vTypeDE'+type+']').val(line1.line4_vTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#vValueDE'+type).val(line1.line4_vValueDE);
                    }else{
                        $('#preinstcheck2'+type).prop('checked',true);
                        $('#validityCheck2'+type).prop('checked',false);
                        seePreInstCheckReset(type);
                        $("#preinstcheck2"+type).val(line1.line4_typeoffile).change();
                        $('#pSoftName22'+type).val(line1.line4_psoftname);
                        $('#pSoftVer22'+type).val(line1.line4_pSoftVer);
                        $('#pfilePath22'+type).val(line1.line4_pfilepath);
                        if(line1.line4_pexecprecheck == '0'){
                            $('#pExecPreCheckValDE0'+type).prop('checked',true);
                            $('#pExecPreCheckValDE1'+type).prop('checked',false);
                        }else{
                            $('#pExecPreCheckValDE1'+type).prop('checked',true);
                            $('#pExecPreCheckValDE0'+type).prop('checked',false);
                        }
                        $('select[name=prootKey22'+type+']').val(line1.line4_prootKey);
                        $('.selectpicker').selectpicker('refresh');
                        $('select[name=pTypeDE'+type+']').val(line1.line4_pTypeDE);
                        $('.selectpicker').selectpicker('refresh');
                        $('#pValueDE'+type).val(line1.line4_pValueDE);
                        
                    }
                    $('select[name=sessionval'+type+']').val(line1.line4_session);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=apival'+type+']').val(line1.line4_api);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=preinstcheck2'+type+']').val(line1.line4_typeoffile);
                    $('.selectpicker').selectpicker('refresh');
                    $('select[name=validityCheck2'+type+']').val(line1.line4_validity);
                    $('.selectpicker').selectpicker('refresh');
                    $('#cmndlineval'+type).val(line1.line4_cmndline);
                    $('#patchdepval'+type).val(line1.line4_patchDep);
   }
                    
}
