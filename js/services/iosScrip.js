$(document).ready(function () {
    getHtmlContent();
//    window.location.reload();
    var dart = $('#dartNum').val();
    if(dart == '2006') {
        $('#Password_tr').hide(); 
        $('#ProxyServer_tr').hide();
        $('#ProxyUsername_tr').hide();
        $('#ProxyPassword_tr').hide();
        $('#ProxyServerUrl_tr').hide();
    }
    
    if(dart == '2003') {
        fetch_rating();
    }
    if($("#LostModeValue").val() == '0') {
    $('#Message_tr').hide();
    } else {
         $('#Message_tr').show();
    }
   
    $('#applicationId_tr').hide();
    $('#bundleId_tr').show();
//    reload();
});

function reload() {
    
//    window.location.href = 'iosScripGeneration.php?scripnum='+scripNum;
    if(document.URL.indexOf("#")== -1){
        // Set the URL to whatever it was plus "#".
//        var url = document.URL;
//        url.replace('#','');
        url = document.URL+"#";
        location = "#";

        //Reload the page
        location.reload(true);
    }
    
}
//return to main configuration page from dart configuration page
function goToBack() {
                //history.back();
    location.href = 'scrip.php?conf=ios'+"&csrfMagicToken=" + csrfMagicToken;
    return false;
}

//functionality for policy execute 
//$('#command_submit').on('click', function(e) { 
function submit(){
    var dartNum = $('#dartNum').val();
//    var formData = new FormData($('#script_form')[0]);
 var formData = new FormData();
    $("form#script_form :input").each(function(){
        if($(this).attr("name") !== 'scrip_enabled' && $(this).attr("id") !== 'command_submit') {
            formData.append($(this).attr("name"), $(this).val());
        }
    });
    if(dartNum == '2008') {
        formData.append('Icon_profile_5', $('input[name=Icon_profile_5]')[0].files[0]);
    }
    if(dartNum == '2026') {
        formData.append('addProfile_profile_5', $('input[name=addProfile_profile_5]')[0].files[0]);
    }
    
    if(dartNum == '2025') {
        
        var command = $('#notification').val();
        
        $.ajax({
           url: 'notifyDeviceDart.php?function=executeNotifyDart'+"&csrfMagicToken=" + csrfMagicToken,
           type: 'POST',
           data: {data: command,dartNum: dartNum},
           success: function(result) {
               if(result != '') {
                   $.notify("DART executed successfully");
               } else {
                   $.notify("Some error occurred. Please try again");
               }
           }
        });
        
    } else {
        if($('#scrip_enabled').is(':checked') == true || (dartNum == '2023' && $('#LostModeValue').is(':checked') == true) || (dartNum == '2027')) {    
            $.ajax({
                url: 'generateIosXML.php?function=processRequest'+"&csrfMagicToken=" + csrfMagicToken,
                type: 'POST',
                contentType: false,
                processData: false,
                data: formData,
                success: function(result) {
                    if (result != '') {
                         $.notify("DART executed successfully");
                    } else {
                         $.notify("There is some problem in generating mobileConfig XML");
                    }
                }
            });
        } else { 
            //when script is disabled delete the previous policy and add default policy
            $.ajax({
                url: 'generateIosXML.php?function=deleteXml&dart='+dartNum+"&csrfMagicToken=" + csrfMagicToken,
                type: 'POST',
                success: function(result) {
                    $.notify("DART executed successfully");
                }
            });
        }
    }
//});
}

$('#EncryptionType').on('change',function(){
    if($('#EncryptionType').val() == 'WEP' || $('#EncryptionType').val() == 'WPA') {
        $('#Password_tr').show();
    } else {
        $('#Password_tr').hide();
    }
}) ;

$('#ProxyType').on('change',function(){
   if($('#ProxyType').val() == 'Manual') {
        $('#ProxyServer_tr').show();
        $('#ProxyUsername_tr').show();
        $('#ProxyPassword_tr').show();
        $('#ProxyServerUrl_tr').hide();
   }  else if($('#ProxyType').val() == 'Automatic') {
        $('#ProxyServerUrl_tr').show();
        $('#ProxyServer_tr').hide();
        $('#ProxyUsername_tr').hide();
        $('#ProxyPassword_tr').hide();
   } else {
        $('#ProxyServer_tr').hide();
        $('#ProxyUsername_tr').hide();
        $('#ProxyPassword_tr').hide();
        $('#ProxyServerUrl_tr').hide();
   }
});

$('#ratingRegion').on('change',function(){
    fetch_rating();
});

//To fetch the rating based on country for device policy
function fetch_rating() {
    var countryval = $('#ratingRegion').val();
    
    $.ajax({
        url: "country_dropValues.php?function=get_country_drop_down_values",
        cache: false,
        type: "POST",
        data: "countryVal="+countryval+"&csrfMagicToken=" + csrfMagicToken,
        success: function(result) {
            var arr = result.split("##");
            $("#ratingMovies").html(arr[0]);
            $("#ratingTVShows").html(arr[1]);
        }
    });
}

//for command based dart

//$('.command_submit').on('click',function() {
function commandExecute(obj) {
    var dartNum = $('#dartNum').val();
    var values  = $(obj).attr('id');
    $.ajax({
        url: 'generateIosXML.php?function=processRequest',
        type: 'POST',
        data: {dartNum: dartNum,value:values ,csrfMagicToken: csrfMagicToken},
        success: function (result) {
//                    console.log(result);
            if (result != '') {
                $.notify('DART executed successfully');
//                $('#execmsg').html('<span style="color:green;">DART executed successfully</span>');
//                $('#exec_confirm').modal('show');
//                alert('Executed!!!');
            } else {
                $.notify('There is some problem in generating mobileConfig XML');
//                $('#execmsg').html('<span style="color:red;">There is some problem in generating mobileConfig XML</span>');
//                         $('#exec_confirm').modal('show');
//                alert('There is some problem in generating mobileConfig XML');
            }
        }
    });
//});
}

$("#LostModeValue").on('click',function(){
    var checkVal = $("#LostModeValue").val();
    if(checkVal == "0"){
        $('#Message_tr').hide();
    } else {
        $('#Message_tr').show();
    }
});

$("#addApplication").on('click',function() {
   
    var check = $("#addApplication").val();
    if(check == "1") {
        $('#bundleId_tr').show();
        $('#applicationId_tr').hide();
    } else {
        $('#bundleId_tr').hide();
        $('#applicationId_tr').show();
    }
});


function backToPage() {
    
//    window.history.back();
$('#hide_div').show();
$('#btn_div').show();
    
}

function setValue(obj) {
    if($('#'+obj.id).val() == '' || $('#'+obj.id).val() === 0 || $('#'+obj.id).val() == '0') {
        $('#'+obj.id).val(1);
    } else {
        $('#'+obj.id).val(0);
    }
}

function getHtmlContent() {
//     $("#scrip_content").mCustomScrollbar("destroy");
    var scripNum = '';
    $.ajax({
       url: 'scriptGeneration.php',
        type: 'POST',
        data: {
            'function': 'getHtmlContent',
            'scripnum': +scripNum,
            'csrfMagicToken': csrfMagicToken
        },
        success: function (result) {
            var scripName = result.split('##')[1];
            var html = result.split('##')[0];
            $('#scrip_content').html(html);
            $('#scripName').html(scripName);
        },
        complete: function () { 
//            $("#scrip_content").mCustomScrollbar({
//                theme:"light",
//            });               
        }
        
    });
    
}


function getHtmlContentios(scripNum,scripName) {
    $('#scrip_content').html('');
    $.ajax({
       url: 'scriptGeneration.php',
        type: 'POST',
        data: {
            'function': 'getHtmlContent',
            'scripnum': +scripNum,
            'csrfMagicToken': csrfMagicToken
        },
        success: function (result) {
            var scripName = result.split('##')[1];
            var html = result.split('##')[0];
            $('#scrip_content').html(html);
            $('#scripName').html(scripName);
        },
        complete: function () { 
//            $("#scrip_content").mCustomScrollbar({
//                theme:"light",
//            });               
        }
        
    });
    
}
