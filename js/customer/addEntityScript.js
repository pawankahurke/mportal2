
getServerList();
getSkuList();
function showPopUpModels(popurl) {
    jQuery_1_7.nyroModalManual({
        debug: false,
        width: 400, // default Width If null, will be calculate automatically		
        height: 260,
        bgColor: '#333',
        url: popurl,
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

$('#repserver').click(function() {
    var url = 'addRepServer.php';
    showPopUpModels(url);
});

$('#custskus').click(function() {
    var url = 'createSkus.php';
    jQuery_1_7.nyroModalManual({
        debug: false,
        height: 600,
        width: 600, // default Width If null, will be calculate automatically
        bgColor: '#333',
        url: url,
        ajax: {data: {'csrfMagicToken': csrfMagicToken}, type: 'post'},
        closeButton: null,
        css: {// Default CSS option for the nyroModal Div. Some will be overwritten or updated when using IE6
            wrapper: {
                position: 'absolute',
                top: '50%',
                left: '50%'
            }
        }
    });
});

$("#addChnl_upicon").on("change", function() {
    var file = this.files[0],
            fileName = file.name,
            fileSize = file.size;
    $('#nameOfFile_upicon').text(fileName).show();

});

$("#entity_uplogo").on("change", function() {
    var file = this.files[0],
            fileName = file.name,
            fileSize = file.size;

    $('#nameOfFile_uplogo').text(fileName).show();

});

function getSkuList() {

    $.ajax({
        type: 'POST',
        url: 'addCustomerModel.php',
        data: "function=get_EntitySKUList&csrfMagicToken=" + csrfMagicToken,
        async: false,
        success: function(data) {
            $("#SKUList").html(data);
        }
    });
}


function getServerList() {

    $.ajax({
        type: 'GET',
        url: 'addCustomerModel.php',
        data: "function=getEnServerList&csrfMagicToken=" + csrfMagicToken,
        async: false,
        success: function(data) {
            $("#repServerList").html(data);
        }
    });
}

function validateEmailAddr(email) {
    var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
    if (filter.test(email)) {
        return true;
    }
    else {
        return false;
    }
}

function validatePhoneNumber(phoneNumber) {
    var length = phoneNumber.length;
    var regExp = /^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$/;
    if (length > 15) {
        return false;
    } else if (regExp.test(phoneNumber)) {
        return true;
    } else {
        return false;
    }
}


function validateZipCode(zipcode) {
    var regExp = /^[0-9]+$/;
    if (regExp.test(zipcode)) {
        return true;
    } else {
        return false;
    }
}

function submitForm() {
    var isReqFieldsFilled = true;
    var isChecked = true;
    var idArray = [];
    $('.req').each(function() {
        var req_id = this.id;
        var field_id = req_id.replace("err_", "");
        var field_value = $("#" + field_id).val();
        if ($.trim(field_value) === "") {
            $("#err_" + field_id).css("color", "red").html(" *required");
            isReqFieldsFilled = false;
            return false;
        } else if (field_id == "emailId") {
            if (!validateEmailAddr(field_value)) {
                isReqFieldsFilled = false;
                $("#err_" + field_id).css("color", "red").html(" enter valid email");
                return false;
            } else {
                isReqFieldsFilled = true;
                $("#err_" + field_id).css("color", "red").html("");
                return true;
            }
        } else if (field_id == "contactNo") {
            if (!validatePhoneNumber(field_value)) {
                isReqFieldsFilled = false;
                $("#err_" + field_id).css("color", "red").html(" enter valid phone number");
                return false;
            } else {
                isReqFieldsFilled = true;
                $("#err_" + field_id).css("color", "red").html("");
                return true;
            }

        } else if (field_id == "companyZipCode") {
            if (!validateZipCode(field_value)) {
                isReqFieldsFilled = false;
                $("#err_" + field_id).css("color", "red").html(" enter valid zipcode");
                return false;
            } else {
                isReqFieldsFilled = true;
                $("#err_" + field_id).css("color", "red").html("");
                return true;
            }

        } else {
            isReqFieldsFilled = true;
        }
        //idArray.push(field_value);
    });

    var server = new Array();
    $("input[name='server[]']:checked").each(function() {
        server.push($(this).val());
    });

    var skuVal = new Array();
    $("input[name='skuVal[]']:checked").each(function() {
        skuVal.push($(this).val());
    });

    if (server.length == 0) {
        isChecked = false;
        $("#err_server").html(" please select server");
        return false;
    } else if (skuVal.length == 0) {
        isChecked = false;
        $("#err_skus").html(" please select skus");
        return false;
    } else {
        $("#err_server").html('');
        $("#err_skus").html('');
        isChecked = true;
    }


    var m_data = new FormData();
    m_data.append('companyRegName', $('#companyRegName').val());
    m_data.append('companyRegNo', $('#companyRegNo').val());
    m_data.append('companyVatId', $('#companyVatId').val());
    m_data.append('website', $('#website').val());
    m_data.append('companyAddress', $('#companyAddress').val());
    m_data.append('companyCity', $('#companyCity').val());
    m_data.append('province', $('#province').val());
    m_data.append('companyZipCode', $('#companyZipCode').val());
    m_data.append('country', $('#country').val());
    m_data.append('ftpurl', $('#ftpurl').val());
    m_data.append('wsurl', $('#wsurl').val());
    m_data.append('firstName', $('#firstName').val());
    m_data.append('lastName', $('#lastName').val());
    m_data.append('emailId', $('#emailId').val());
    m_data.append('contactNo', $('#contactNo').val());
    m_data.append('ctype', $('#ctype').val());
    m_data.append('loginusing', $('#loginusing').val());
    m_data.append('orderinfo', $('#orderinfo').val());
    m_data.append('hirearchyId', $('#hirearchyId').val());
    m_data.append('server', server);
    m_data.append('skuVal', skuVal);

    m_data.append('entity_uplogo', $('input[name=entity_uplogo]')[0].files[0]);
    m_data.append('entity_upicon', $('input[name=entity_upicon]')[0].files[0]);
    m_data.append('csrfMagicToken', csrfMagicToken);

    //instead of $.post() we are using $.ajax()
    //that's because $.ajax() has more options and flexibly.
    if (isReqFieldsFilled == true && isChecked == true) {
        $("#successMsg").html('<img src="../images/ajax-login.gif" class="loadhome" alt="loading..." style="width:auto !important;padding-left:22% !important;"/>');
        $.ajax({
            url: 'addCustomerModel.php?function=createEntity',
            data: m_data,
            processData: false,
            contentType: false,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                $("#successMsg").html(response.msg);
                setTimeout(function() {
                    $("#successMsg").html('');
                },
                        3000);
            },
            error: function(response) {
                $("#successMsg").html("Error Occurred");
            }
        });
    }
}

function resetForm() {
    document.getElementById("addEntityForm").reset();
}

function createReportServer(){
        var serverName = $("#report_server_name").val();
        var serverUrl = $("#report_server_url").val();
        $.ajax({
            type: 'POST',
            url: 'addCustomerModel.php',
            data: "function=createServer&serverName=" + serverName + "&serURL=" + serverUrl + "&csrfMagicToken=" + csrfMagicToken,
            async: false,
            success: function (data) {
                var msg = data.trim();
                $("#rsuccessMsg").html(msg);
            }
        });
         
 }
 
 function createAdvReport(){
       
        var advserverName = $('#advserverName').val();
        var advserURL = $('#advserURL').val();
        var assetURL = $('#assetURL').val();
        var configURL = $('#configURL').val();
        
        if (!advserverName)
        {
            $('#adverrname').html('* Please enter server Name.');
            document.getElementById('advserverName').focus();
            return;
        }
        if (!advserURL)
        {
            $('#adverrname').html('* Please enter event url.');
            document.getElementById('advserURL').focus();
            return;
        }
        if (!assetURL)
        {
            $('#adverrname').html('* Please enter asset url.');
            document.getElementById('assetURL').focus();
            return;
        }
        if (!configURL)
        {
            $('#adverrname').html('* Please enter config url.');
            document.getElementById('configURL').focus();
            return;
        }
        
        $.ajax({
            type: 'POST',
            url: 'addCustomerModel.php',
            data: "function=createAdvServer&advserverName=" + advserverName + "&advserURL=" + advserURL+"&assetURL="+assetURL+"&configURL="+configURL + "&csrfMagicToken=" + csrfMagicToken,
            async: false,
            success: function (data) {
                $('#rsuccessMsg').html(data);
            }
        });
         
 }
 
function addAdvserver() {
    $('#advreportForm').css({'display':'block'});
    $('#reportForm').css({'display':'none'});
    $('#createAdvReport').show();
    $('#createReport').hide();
    
}

function editrepserver(server) {
            
        var url = 'editRepServer.php?eid='+server;
        //showPopUpModels(url);
        jQuery_1_7.nyroModalManual({
            debug: false,
            height: 400,
            width: 500, // default Width If null, will be calculate automatically
            bgColor: '#333',
            url: url,
            ajax: {data: {'csrfMagicToken': csrfMagicToken}, type: 'post'},
            closeButton: null,
            css: {// Default CSS option for the nyroModal Div. Some will be overwritten or updated when using IE6
                wrapper: {
                    position: 'absolute',
                    top: '50%',
                    left: '50%'
                }
            }
        });
}