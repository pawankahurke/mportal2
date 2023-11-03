$(document).ready(function () {
    var url_value = $("#remote_url").val();
    if ($.trim(url_value) !== '') {
        $('#takeremote').removeAttr('disabled');
        $('#takeremote').removeClass('buttondisable');
        $('#takeremote').addClass('button');
    }
});

var ws = '';
var userName = '';
var loginType = 'email';

$('input:radio[name="login"]').change(
        function () {
            loginType = $(this).val();
            if ($(this).val() === 'login') {
                $("#rmt_loginid").removeAttr('disabled');
            } else {
                $("#rmt_loginid").attr('disabled', 'disabled');
            }
        });

$('#usernameEmail').change(function() {
    userName = $('#usernameEmail').val();
});

function Connectremote() {
    var selectedRemoteConsole = $("input[name=remotename]:checked").val();
    OpenRemoteLogInPage(selectedRemoteConsole);
}

function OpenRemoteLogInPage(remoteType) {

    $.ajax({
        url: "../remote/remote_func.php?function=getUserList&remoteType="+remoteType +"&csrfMagicToken=" + csrfMagicToken,
        type: 'GET',
        dataType: 'json',
        success : function(data) {
            $('#usernameEmail').html('');
            $('#usernameEmail').html(data.option);
            $('.selectpicker').selectpicker('refresh');
            $('#pass_hidden').append(data.pass);
            userName = $('#usernameEmail').val();

    var isAdvancePage = advanceGTA;
    var urlPage = "";
    if ($.trim(isAdvancePage) == '0') {
                urlPage = "../remote/index.php?perform=DEFAULT&showPage=NO&remoteType=" + remoteType+"&userName="+userName;
    } else if ($.trim(isAdvancePage) == '1') {
                urlPage = "../remote/index.php?perform=DEFAULT&showPage=SHOWADVANCE&remoteType=" + remoteType+"&userName="+userName;
    }

    $.ajax({
        url: urlPage+"&csrfMagicToken=" + csrfMagicToken,
        type: 'GET',
        dataType: 'json',
//        data: '{}',
        success: function (data) {
//            alert(data.rmt_result);
            $('#rmt_result').val(data.rmt_result);//url
            $('#rmt_loginidc').val(data.rmt_liginid);//loginid
//            $('#rmt_loginidr').val(data.rmt_liginid);
            $('#rmt_passc').val(data.rmt_pass);//password
            $('#remoteperform').val(data.perform);
            $('#remoteshowpage').val(data.showPage);
                $('#usernameEmail').val(data.option);//email
                $('.selectpicker').selectpicker('refresh'); 
            $('#loginid').val(data.rmt_liginid);//id
            $("#usernameEmail").parent().removeClass("is-empty");
            $("#loginid").parent().removeClass("is-empty");
            $("#remote_url").parent().removeClass("is-empty");
            $('#remoteact').val(data.act);
            $('#remotetype').val(data.remoteTyp);
            $('#emlid').html(data.remoteTyp + " Login ID");
            $('#emd').html(data.remoteTyp + " Login ID");
            $('#emp').html(data.remoteTyp + " Password");
            $('#emr').html(data.remoteTyp + " Login ID");
            $('#emcp').html(data.remoteTyp + " Confirm Password");
//                $('#err').html("Please Set the " + data.remoteTyp + " Credentials");
            $('#emdurl').html(data.remoteTyp + " URL");

            $('#empre').html(data.remoteTyp + " Login ID");
            $('#emppass').html(data.remoteTyp + " Password");
            $('#emcpass').html(data.remoteTyp + " Confirm Password");
            $('#remotewsurl').val(data.wsurl);
            if (data.perform == 'DEFAULT') {
                $('#machineonlineremote').modal('hide');
                $('#warningdefault').modal('show');
            }
            if (data.perform == 'SHOWURL' || (data.perform == 'EXIST' && data.showPage == "NO")) {
                $('#machineonlineremote').modal('hide');
                $('#warningshowurl').modal('show');
            }
            if (data.perform == 'EXIST' && data.showPage == "NO") {
                var rmtloginid = $('#rmt_loginidc').val();
                var rmtpass = $('#rmt_passc').val();
                $('#error').css({'color': 'green'}).html('Fetching URL. Please wait...');
                create_rmt__downloadlink(rmtloginid, rmtpass, "GETURL");
            }
            if (data.perform == 'EXIST' && data.showPage == 'SHOWADVANCE') {
            }
        },
        error: function (request, status, error) {
            alert('Error ' + request.responseText + JSON.stringify(error));
        }
        });
}
        });
}

var emailregex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;

$('#rmt_submit').click(function () {
    var rmttype = $('#remotetype').val();
    $("#err").html('');
    $("#err").show();
    
    if(loginType === 'email') {
       var emailid = $('#usernameEmail').val();
       var password = $('#'+emailid.split('@')[0]).val();
        $('#error').css({'color': 'green'}).html('Fetching URL. Please wait...');
        create_rmt__downloadlink(emailid, password, "GETURL");
        
        
    } else {
    if ($('#rmt_login').prop('checked') == true) {
        if ($('#rmt_loginid').val() == '') {
            $('#err').html('Please enter the email id');
                setTimeout(function() {
                    $("#err").fadeOut(3600);
                }, 3600);
                return false;
        } else if (!emailregex.test($('#rmt_loginid').val())) {
            $('#err').html('Please enter valid email id');
                return false;
        }
    }
    if ($("#rmt_pass").val() == '') {
        $('#err').html('Please enter the password');
        setTimeout(function() {
                    $("#err").fadeOut(3600);
                }, 3600);
                return false;
    } else if ($("#rmt_passconf").val() == '') {
        $('#err').html('Please confirm the password');
        setTimeout(function() {
                    $("#err").fadeOut(3600);
                }, 3600);
                return false;
    } else if ($('#rmt_pass').val() != $('#rmt_passconf').val()) {
        $('#err').html('The passwords do not match');
        setTimeout(function() {
                    $("#err").fadeOut(3600);
                }, 3600);
                return false;
    } else {
        var emailid = '';
        if ($('#rmt_login').prop('checked') == true) {
            emailid = $('#rmt_loginid').val();
        } else if ($('#agentlogin').prop('checked') == true) {
            emailid = $('#usernameEmail').val();
        } else {
            emailid = $('#rmt_user').val();
        }
        var password = $('#rmt_pass').val();
    }
        $.ajax({
            url: "../remote/remote_func.php?function=checkUserExistance&loginid=" + emailid + '&remoteType=' + rmttype +"&csrfMagicToken=" + csrfMagicToken,
            type: 'POST',
            success: function (data) {
                data = data.trim();
                //alert('checkUserExistance: '+data);
                if (data == 'LOGINEXIST') {
                    $('#err').html('<span style="color:green;">Authenticating ' + rmttype + ' Login. Please wait...</span>');
                    create_rmt__downloadlink(emailid, password, "GETURL");
                    /*$('#err').html('Login ID already exists.');
                    setTimeout(function() {
                        $("#err").fadeOut(3600);
                    }, 3600);*/
                } else if (data == 'LOGINADD') {
//                    $('#err').css({'color': 'green'}).html('Authenticating ' + rmttype + ' Login. Please wait...');
                    $('#err').html('<span style="color:green;">Authenticating ' + rmttype + ' Login. Please wait...</span>');
                    create_rmt__downloadlink(emailid, password, 'ADDNEW');
                } else {
                    $('#err').html(data);
                    setTimeout(function() {
                        $("#err").fadeOut(3600);
                    }, 3600);
                }
            },
            error: function (err) {
                console.log(err);
            }
        });
    }
});


$('#rmt_update').click(function () {
    
    var loginid = $('#rmt_loginidr').val();
    var passone = $('#rmt_passr').val();
    var passcon = $('#rmt_passconfr').val();
    $("#errr").html('');
    $("#errr").show();
    
    if (passone == '') {
        $('#errr').css({'color': 'red'}).html('Please enter the password');
        setTimeout(function() {
                    $("#errr").fadeOut(3600);
                }, 3600);
        return false;
    } else if (passcon == '') {
        $('#errr').css({'color': 'red'}).html('Please confirm the password');
        setTimeout(function() {
                    $("#errr").fadeOut(3600);
                }, 3600);
        return false;
    } else if (passone != passcon) {
        $('#errr').css({'color': 'red'}).html('The passwords do not match');
        setTimeout(function() {
                    $("#errr").fadeOut(3600);
                }, 3600);
        return false;
    } else {
        $('#errr').css({'color': 'green'}).html('Authenticating credentials. Please wait..');
        create_rmt__downloadlink(loginid, passone, 'UPDATE');
    }
});

function create_rmt__downloadlink(loginid, password, action) {
    //alert("loginid: "+loginid+", password: "+password+', action: '+action);
    var rmttype = $('#remotetype').val();
    var pass = escape(password);
    $("#errr").html('');
    $("#errr").show();
    $("#err").html('');
    $("#err").show();

    $.ajax({
        url: "../remote/remote_func.php",
        data: "function=create_remotelink&loginid=" + loginid + "&password=" + pass + '&remoteType=' + rmttype +"&csrfMagicToken=" + csrfMagicToken,
        type: "GET",
        success: function (msg) {
            var data = msg.trim();
            //console.log("data--->" + data);
            if (data === 'INVALID') {
                if (action === 'GETURL') {
                    showResetRmtPassword(loginid);
                } else {
                    $('#errr').css({'color': 'red'}).html('Login ID / Password is incorrect');
                        setTimeout(function() {
                        $("#errr").fadeOut(3600);
                    }, 3600);
                    $('#err').css({'color': 'red'}).html('Login ID / Password is incorrect');
                        setTimeout(function() {
                        $("#err").fadeOut(3600);
                    }, 3600);
                }
            } else if (data === 'Password Not Set') {
                $('#resetpass').css({'display': 'block'});
                $('#err').css({'color': 'red'}).html('Incorrect password. Please update password.');
                setTimeout(function() {
                    $("#err").fadeOut(3600);
                }, 3600);
            } else if (data === 'NO_APP_RUNNING') {
                $('#err').css({'color': 'red'}).html('Please Login to ' + rmttype + ' App');
                setTimeout(function() {
                    $("#err").fadeOut(3600);
                }, 3600);
            } else {
                if (action === 'GETURL') {
                    $('#error').html('');
                    $('#takeremote').removeAttr('disabled');
                    $('#takeremote').removeClass('buttondisable');
                    $('#takeremote').addClass('button');
                    $('#remote_url').val($('<div/>').html(data).text());
                    updateRmt_Details(loginid, password, action, data);
                } else {
                    $('#error').html('');
                    $('#takeremote').removeAttr('disabled');
                    $('#takeremote').removeClass('buttondisable');
                    $('#takeremote').addClass('button');
                    $('#remote_url').val($('<div/>').html(data).text());
//                    $('#error').css({'color': 'green'}).html('Fetching URL. Please wait...');
                    updateRmt_Details(loginid, password, action, data);
                }
            }
        }
        /* end - success */
    });
}

function updateRmt_Details(rmt_login, rmt_pass, act, result) {
    var rmttype = $('#remotetype').val();
    var params = "&perform_act=" + act + "&login=" + rmt_login + "&pass=" + rmt_pass;
    $("#err").html('');
    $("#err").show();
    //alert('updateRmt_Details: '+params);
    $.ajax({
        url: "../remote/remote_func.php?function=updateRemote_Details" + params + '&remoteType=' + rmttype +"&csrfMagicToken=" + csrfMagicToken,
        type: 'GET',
        success: function (data) {
            //alert(data);
            if ($.trim(data) === 'success') {
                showRmtUpdate(rmt_login, result);
            } else if ($.trim(data) === 'error') {
                $('#err').css({'color': 'red'}).html("Sorry couldn't Update Remote Details.Please try again");
                setTimeout(function() {
                    $("#err").fadeOut(3600);
                }, 3600);
            }
        },
        error: function (err) {
        }
    });
}

function showRmtUpdate(loginid, dataurl) {
    var rmttype = $('#remotetype').val();
    var rem_URL = encodeURIComponent(dataurl);
    $.ajax({
        url: "../remote/index.php?perform=SHOWURL&rmt_loginid=" + loginid + "&rmt_result=" + rem_URL + '&remoteType=' + rmttype +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        type: 'GET',
        success: function (data) {
            $('#loginid').val(data.rmt_liginid);//email id
            $('#remote_url').val(data.rmt_result);//url
            if (data.perform == 'SHOWURL' || (data.perform == 'EXIST' && data.showPage == "NO")) {
                $('#warningreset').modal('hide');
                $('#warningdefault').modal('hide');
                $('#warningshowurl').modal('show');
            }
        }
    })
}

function showResetRmtPassword(loginid) {
    var rmtid = $('#rmt_loginid').val();
    var rmttype = $('#remotetype').val();
    if (rmtid != "") {
        $.ajax({
            url: "../remote/index.php?perform=RESET&resetloginid=" + rmtid + '&remoteType=' + rmttype +"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json',
            data: '{}',
            type: 'GET',
            success: function (data) {
                if (data.perform == 'RESET') {
                    $('#warningshowurl').modal('hide');
                    $('#warningreset').modal('show');
                }
            }
        });
    } else {
        $.ajax({
            url: "../remote/index.php?perform=RESET&resetloginid=" + loginid + '&remoteType=' + rmttype +"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json',
            data: '{}',
            type: 'GET',
            success: function (data) {
                $("#rmt_loginidr").val(data.resetlogin);
                $("#rmt_loginidr").parent().removeClass("is-empty");
                if (data.perform == 'RESET') {
                    $('#warningshowurl').modal('hide');
                    $('#warningreset').modal('show');
                }
            }
        });
    }
}

function sendremotetoclient() {
    getmachineDetails();
}

function getmachineDetails() {
    var remotewsurl = $('#remotewsurl').val();
    var hostname = $('#selected').val();
    var rmttype = $('#remotetype').val();

    $.ajax({
        type: "GET",
        url: "../communication/communication_ajax.php",
        data: "function=getMachineOS"+"&csrfMagicToken=" + csrfMagicToken,
        success: function (msg) {
            msg = msg.trim();
            var res = msg.split('####');
            var res1 = res[1].split('~~');
            var machineName = res1[0];
            var machineSite = res1[1];
            var machineBuld = res1[2];
            var url = $("#remote_url").val();
            url = $.trim(url);
            var ver;
            var dartVal
            if (machineBuld !== '' || machineBuld === null) {
                var versionNo = machineBuld.split('.');
                ver = parseInt(versionNo[3]);
            } else {
                ver = 0
            }
            if (ver >= 2709) {
                //dartVal = 'VarName=Scrip89Package;VarType=2;VarVal=C:\\Program Files (x86)\\Internet Explorer\\iexplore.exe "' + url + '";Action=SET;DartNum=215;VarScope=1;#;NextConf;#VarName=S00286ProfileName;VarType=2;VarVal=remoteaccess;Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;#;NextConf;##;NextConf;#End';
                dartVal = 'VarName=Scrip89Package;VarType=2;VarVal="C:\\Program Files (x86)\\Internet Explorer\\iexplore.exe" "' + url + '";Action=SET;DartNum=215;VarScope=1;#;NextConf;#VarName=S00089RunNowButton;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=215;VarScope=1;';

            } else {
                //dartVal = 'VarName=Scrip89Package;VarType=2;VarVal=C:\\Program Files (x86)\\Internet Explorer\\iexplore.exe "' + url + '";Action=SET;DartNum=215;VarScope=1;#;NextConf;#VarName=S00289ProfileName1;VarType=2;VarVal=remoteaccess;Action=SET;DartNum=289;VarScope=1;#;NextConf;#VarName=S00289SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=289;VarScope=1;#;NextConf;##;NextConf;#End';
                dartVal = 'VarName=Scrip89Package;VarType=2;VarVal="C:\\Program Files (x86)\\Internet Explorer\\iexplore.exe" "' + url + '";Action=SET;DartNum=215;VarScope=1;#;NextConf;#VarName=S00089RunNowButton;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=215;VarScope=1;';

            }
//            var profileName = "<?php echo $remoteType ?>";
            //var profileName = rmttype;            
            //var dblist = machineSite + '~~' + machineName + '~~' + machineBuld + '~~' + escape(dartVal) + '~~' + profileName;

            //ExecuteDirectJob(hostname, escape(dartVal));
            //addjob(hostname,escape(dartVal));
            var wsurl = remotewsurl;
            if (window.location.protocol !== "https:") {
                wsRemoteconnect('ws://' + wsurl, hostname,dartVal);                
            } else {
                wsRemoteconnect('wss://' + wsurl, hostname,dartVal);                
            }
        }
    });
}

function wsRemoteconnect(wsurl, Servicetag, DirectJob) {
    ws = new WebSocket(wsurl);
    ws.onopen = function () {
        var ConnectData = {};
        ConnectData['Type'] = 'Dashboard';
        ConnectData['AgentId'] = 'GTATRIGGER';
        ConnectData['AgentName'] = 'GTATRIGGER';
        ConnectData['ReportingURL'] = 'GTATRIGGER';
        ws.send(JSON.stringify(ConnectData));
        ExecuteDirectJob(Servicetag, DirectJob);
    };
}

function ExecuteDirectJob(Servicetag, DirectJob) {
    var JobData = {};
    JobData['Type'] = 'ExecuteDirectJob';
    JobData['ServiceTag'] = Servicetag;
    JobData['DirectJob'] = 'InstantExecution---' + DirectJob;
    ws.send(JSON.stringify(JobData));
    openSuccessPage();
}

function emitGTA(serviceTag, OS) {
    $.ajax({
        type: "GET",
        url: "../communication/communication_ajax.php",
        data: "function=getTempAuditdata&os=" + OS +"&csrfMagicToken=" + csrfMagicToken,
        success: function (msg) {
            if (msg !== '') {
                var emitMsg = msg.split('##');
                for (var i = 0; i < emitMsg.length - 1; i++) {
                    RemoteSocket.emit('executeDart', emitMsg[i]);
                    openSuccessPage();
                }
            }
        }
    });
}
/*=====success call =====*/
function openSuccessPage() {
    $.ajax({
        url: "../remote/index.php?perform=SUCCESSPAGE"+"&csrfMagicToken=" + csrfMagicToken,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data.perform == 'SUCCESSPAGE') {
                $('#warningshowurl').modal('hide');
                $('#remotesuccessmsg').modal('show');
            }
        }
    });
}

//fetch stored user name and password

function fetchUserDetails() {
    var name = '';
    $.ajax({
       url: "../remote/remote_func.php?function=getUserList"+"&csrfMagicToken=" + csrfMagicToken,
       type: 'GET',
       dataType: 'text'
        }).done(function (result) {
        //console.log(result);
                    name = result;
        });
//       success: function(data) {
//           console.log(data);
//           $('#usernameEmail').html('');
//           $('#usernameEmail').html(data);
//           $('.selectpicker').selectpicker('refresh');
//           name = data;
//       }
//       
//    });
    return name;
    
}

$('#warningdefault').on('hidden.bs.modal', function() {
//    
    $('#warningdefault input[type=text]').val('');
    $('#warningdefault input[type=password]').val('');
    $('#err').html('');
});