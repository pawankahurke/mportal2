$(document).ready(function () {
    getDefaultData();
    getUserDetails();
});

function getUserDetails() {

    $.ajax({
        type: "GET",
        url: "../Configuration/ConfigurationFunc.php?function=getLoggedUserData&csrfMagicToken=" + csrfMagicToken,
        success: function (data) {
            data = $.trim(data);
            $('#loggedUserDetails').val(data);
        },
        error: function(data){
            console.log("error");
        }
    });
}

function getDefaultData(){
    $.ajax({
        url: "../Configuration/ConfigurationFunc.php?function=getDefaultData" + '&csrfMagicToken=' + csrfMagicToken,
           type: "post",
           success:function(data){
               data = JSON.parse($.trim(data));
                $('#kibana_nspace').val(data.kibananamespace);
                $('#elast_alert').val(data.elastconfig);
                $('#license_url').val(data.licenseurl);
                $('#ws_url').val(data.wsurl);
                $('#security_array').val(data.securityArray);
                $('#global_retry').val(data.globalretry);
                $('#timer').val(data.timer);
                $('#elastic_url').val(data.elasticurl);
                $('#elastic_username').val(data.elasticusername);
                $('#elastic_password').val(data.elasticpassword);
                $('#kibana_url').val(data.kibanaurl);
                $('#kibana_ip_url').val(data.kibanaipurl);
                $('#kibana_username').val(data.kibanausername);
                $('#kibana_password').val(data.kibanapassword);
           },
           error:function(error){
               console.log("error");
           }
        });
}

function editConfigDetails(){
    var LoggedUserRole = $('#loggedUserDetails').val();
    
    if(LoggedUserRole != 'AdminRole'){
        $.notify("You don't have the access to Edit the Details");
    }else{
        $('#kibana_nspace').attr("readonly",false);
        $('#elast_alert').attr("readonly",false);
        $('#license_url').attr("readonly",false);
        $('#ws_url').attr("readonly",false);
        $('#security_array').attr("readonly",false);
        $('#global_retry').attr("readonly",false);
        $('#timer').attr("readonly",false);
        $('#elastic_url').attr("readonly",false);
        $('#elastic_username').attr("readonly",false);
        $('#elastic_password').attr("readonly",false);
        $('#kibana_url').attr("readonly",false);
        $('#kibana_ip_url').attr("readonly",false);
        $('#kibana_username').attr("readonly",false);
        $('#kibana_password').attr("readonly",false);
    }
    
}

function SubmitConfigDetails(){
    var kibananamespace = $('#kibana_nspace').val();
    var elastconfig = $('#elast_alert').val();
    var licenseurl = $('#license_url').val();
    var wsurl = $('#ws_url').val();
    var securityArray = $('#security_array').val();
    var globalretry = $('#global_retry').val();
    var timer = $('#timer').val();
    var elasticurl = $('#elastic_url').val();
    var elasticusername = $('#elastic_username').val();
    var elasticpassword = $('#elastic_password').val();
    var kibanaurl = $('#kibana_url').val();
    var kibanaipurl = $('#kibana_ip_url').val();
    var kibanausername = $('#kibana_username').val();
    var kibanapassword = $('#kibana_password').val();
    
    var FormData = {
      "kibananamespace" : kibananamespace,
      "elastconfig" : elastconfig,
      "licenseurl" : licenseurl,
      "wsurl" : wsurl,
      "securityArray" : securityArray,
      "globalretry" : globalretry,
      "timer" : timer,
      "elasticurl" : elasticurl,
      "elasticusername" : elasticusername,
      "elasticpassword" : elasticpassword,
      "kibanaurl" : kibanaurl,
      "kibanaipurl" : kibanaipurl,
      "kibanausername" : kibanausername,
      "kibanapassword" : kibanapassword
    };
    
    $.ajax({
        url: "../Configuration/ConfigurationFunc.php?function=updateValuesConfig&csrfMagicToken=" + csrfMagicToken,
        data: FormData,
        type: "POST",
        success: function(data){
            data = $.trim(data);
            if(data == "success"){
                $.notify("Details updated successfully");
                setTimeout(function(){
                    location.reload();
                },3000);
            }else{
                $.notify("Some error occured");
            }
            
        },
        error:function(err){
            console.log("error");
        }
    });
}