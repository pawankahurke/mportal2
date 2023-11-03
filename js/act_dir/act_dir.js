var ws = '';
$(document).ready(function () {
    $('.loader').hide();
    var userId = $("#userId").val();
    var searchType = $("#searchType").val();

    $("#secure_queserr").text('');
    $("#secure_queserr").html('');

    $('#pageName').html('Active Directory');
    $(".se-pre-con").hide();

    $(".wg_config_que").click(function () {
        var searchType = $("#searchType").val();
        $("#wgchngpwd_err").html('');
        $(".chckcircle").css("display", "block");
        $(".proceedcircle").css("display", "none");
        $(".resetPasscircle").css("display", "none");
        $("#q1").val('');
        $("#q2").val('');
        $("#q3").val('');
        $("#q4").val('');
        $("#q5").val('');
        if (searchType === 'ServiceTag' || searchType === "ServiceTag") {

            $(".head-ttle").html('<h4>Security Questions</h4>');
            $(".wg-conf").attr("data-class", "md-6");
            $(".domain_div").css("display", "none");
            $(".domain_div_sub").css("display", "none");
            $(".chngpwd_div").css("display", "none");
            $(".username_div").css("display", "none");
            $(".sec_que").css("display", "block");
            var userName = $('#userName').val("mainuser");
            userId = $("#userId").val('1');
//            rightContainerSlideOn('wg-config-questions');
            isMachineSelect('configureWG');
        } else if (searchType !== 'Service Tag' || searchType !== 'Host Name' || searchType !== 'ServiceTag') {
            $.notify("Please select a device");
        }
    });

    $(".dom_config_que").click(function () {
        var searchType = $("#searchType").val();
        $("#wgchngpwd_err").html('');
        $(".chckcircle").css("display", "none !important");
        $(".proceedcircle").css("display", "block !important");
        $(".proceedcircle").show();
        $(".chckcircle").hide();
        // $(".resetPasscircle").css("display","none");
        $("#q1").val('');
        $("#q2").val('');
        $("#q3").val('');
        $("#q4").val('');
        $("#q5").val('');
        if (searchType === 'ServiceTag' || searchType === "ServiceTag") {
            $(".head-ttle").html('<h4>Security Questions</h4>');
            $(".wg-conf").attr("data-class", "md-6");
            $(".chngpwd_div").css("display", "none");
            $(".domain_div").css("display", "block");
            $(".username_div").css("display", "none");
            $(".domain_div_sub").css("display", "block");
            $(".sec_que").css("display", "none");
            userId = $("#userId").val('2');
//            rightContainerSlideOn('wg-config-questions');
            isMachineSelect('configure');
        } else if (searchType !== 'Service Tag' || searchType !== 'Host Name' || searchType !== 'ServiceTag') {
            $.notify("Please select a device");
        }
    });


    $(".wg_chng_pwd").click(function () {
        var searchType = $("#searchType").val();
        $("#wgchngpwd_err").html('');
        $(".chckcircle").css("display", "none");
        $(".proceedcircle").css("display", "block");
        $(".resetPasscircle").css("display", "none");
        $("#wgchngpwd_err").html('');
        $("#q1").val('');
        $("#q2").val('');
        $("#q3").val('');
        $("#q4").val('');
        $("#q5").val('');
        if (searchType === 'ServiceTag' || searchType === "ServiceTag") {
            var userName = $('#userName').val("mainuser");
            $(".head-ttle").html('<h4>Change Password</h4>');
            $(".wg-conf").attr("data-class", "sm-3");
            userId = $("#userId").val('1');
//            rightContainerSlideOn('wg-config-questions');
            $(".sec_que").css("display", "none");
            $(".username_div").css("display", "block");
            $(".domain_div_sub").css("display", "block");
            $(".domain_div").css("display", "none");
            $(".chngpwd_div").css("display", "none");
            isMachineSelect('changePassWG');
        } else if (searchType !== 'Service Tag' || searchType !== 'Host Name' || searchType !== 'ServiceTag') {
            $.notify("Please select a device");

        }

    });
    $(".dom_chng_pwd").click(function () {
        var searchType = $("#searchType").val();
        $("#wgchngpwd_err").html('');
        $(".chckcircle").css("display", "none");
        $(".proceedcircle").css("display", "block");
        $(".resetPasscircle").css("display", "none");
        $("#q1").val('');
        $("#q2").val('');
        $("#q3").val('');
        $("#q4").val('');
        $("#q5").val('');
        if (searchType === 'ServiceTag' || searchType === "ServiceTag") {
            userId = $("#userId").val('2');
            $(".head-ttle").html('<h4>Change Password</h4>');
            $(".chngpwd_div").css("display", "none");
            $(".wg-conf").attr("data-class", "sm-3");
//            rightContainerSlideOn('wg-config-questions');
            $(".sec_que").css("display", "none");
            $(".domain_div_sub").css("display", "block");
            $(".username_div").css("display", "block");
            $(".domain_div").css("display", "block");
            isMachineSelect('changePass');
        } else if (searchType !== 'Service Tag' || searchType !== 'Host Name' || searchType !== 'ServiceTag') {
            $.notify("Please select a device");

        }

    });

    $('.closebtn').click(function () {
        $('.loader').hide();
        $('#domain_userName').val('');
        $('#domain').val('');
    });

    $(".wg_securt_ans").click(function () {
        var searchType = $("#searchType").val();
        $("#wgchngpwd_err").html('');
        var userName = $('#userName').val("mainuser");
        $(".chckcircle").css("display", "none");
        $(".proceedcircle").css("display", "block");
        $(".resetPasscircle").css("display", "none");
        $("#q1").val('');
        $("#q2").val('');
        $("#q3").val('');
        $("#q4").val('');
        $("#q5").val('');

        if (searchType === 'ServiceTag' || searchType === "ServiceTag") {
            userId = $("#userId").val('1');
            $(".chngpwd_div").css("display", "none");
            $(".sec_que").css("display", "none");
            $(".wg-conf").attr("data-class", "sm-3");
            $(".head-ttle").html('<h4>Reset Security Answers</h4>');
            $(".domain_div_sub").css("display", "block");
            $(".username_div").css("display", "block");
            $(".domain_div").css("display", "none");

//            rightContainerSlideOn('wg-config-questions');
            isMachineSelect('changeSecqWG');
        } else if (searchType !== 'Service Tag' || searchType !== 'Host Name' || searchType !== 'ServiceTag') {
            $.notify("Please select a device");

        }
    });
    $(".dom_securt_ans").click(function () {
        $('.loader').hide();
        var searchType = $("#searchType").val();
        $("#wgchngpwd_err").html('');
        $(".chckcircle").css("display", "none");
        $(".proceedcircle").css("display", "block");
        $(".resetPasscircle").css("display", "none");
        $("#q1").val('');
        $("#q2").val('');
        $("#q3").val('');
        $("#q4").val('');
        $("#q5").val('');
        if (searchType === 'ServiceTag' || searchType === "ServiceTag") {
            userId = $("#userId").val('2');
            $(".chngpwd_div").css("display", "none");
            $(".sec_que").css("display", "none");
            $(".wg-conf").attr("data-class", "sm-3");
            $(".head-ttle").html('<h4>Reset Security Answers</h4>');
            $(".domain_div_sub").css("display", "block");
            $(".username_div").css("display", "block");
            $(".domain_div").css("display", "block");

//            rightContainerSlideOn('wg-config-questions');
            isMachineSelect('changeSecq');
        } else if (searchType !== 'Service Tag' || searchType !== 'Host Name' || searchType !== 'ServiceTag') {
            $.notify("Please select a device");

        }
    });

    $(".dom_unlck_accnt").click(function () {
        $('.loader').hide();
        var searchType = $("#searchType").val();
        $("#wgchngpwd_err").html('');
        $(".chckcircle").css("display", "none");
        $(".proceedcircle").css("display", "block");
        $(".resetPasscircle").css("display", "none");
        $("#q1").val('');
        $("#q2").val('');
        $("#q3").val('');
        $("#q4").val('');
        $("#q5").val('');
        if (searchType === 'ServiceTag' || searchType === "ServiceTag") {
            userId = $("#userId").val('2');
            $(".chngpwd_div").css("display", "none");
            $(".sec_que").css("display", "none");
            $(".wg-conf").attr("data-class", "sm-3");
            $(".head-ttle").html('<h4>Unlock Account</h4>');
            $(".domain_div_sub").css("display", "block");
            $(".username_div").css("display", "block");
            $(".domain_div").css("display", "block");

//            rightContainerSlideOn('wg-config-questions');
            isMachineSelect('unlockAcc');
        } else if (searchType !== 'Service Tag' || searchType !== 'Host Name' || searchType !== 'ServiceTag') {
            $.notify("Please select a device");

        }
    });


    var errStat = [
        'Success.', 'Unable to access AD server.', 'User is not registered to use Nanoheal Password Reset functionality.', 'Unable to fetch user data from AD Server.', 'Unable to authenticate user.', 'Successfully changed the password. User need to change password at next log in.', 'Unable to reset password.', 'Unable to unlock Account.'
    ];
    errStat[17] = 'Unable to reset security answers.';
    errStat[16] = 'Impersonation credentials are not there in Dart 43.';
    errStat[10] = 'Data sent by server is not proper.';
    errStat[11] = 'Unable to locate user in specified domain.';
    errStat[12] = 'Selected machine is not the specified dedicated machine.';
    errStat[13] = 'Unable to configure security questions.';
    errStat[14] = 'Unable to access AD server IP.';
    errStat[15] = 'Unable to authenticate user.';
    errStat[21] = 'Unable to authenticate user.';
    errStat[24] = 'Security Questions has been changed.';
    errStat[26] = 'Please try again.Unknown error occoured.';
    var wsurl = getwsurl();
    var reportingurl = '<?php echo $reportingurl; ?>';
    if (window.location.protocol !== "https:") {
        wsconnect('ws://' + wsurl, reportingurl);
        LogToConsole('Connecting to Communication Server : ' + 'http://' + wsurl);
    } else {
        wsconnect('wss://' + wsurl, reportingurl);
        LogToConsole('Connecting to Communication Server (AD) : ' + 'https://' + wsurl);
    }

    function wsconnect(wsurl, reportingurl) {
        ws = new WebSocket(wsurl);
        ws.onopen = function () {
            LogToConsole('Connecting to Communication Server Success');
            var ConnectData = {};
            ConnectData['Type'] = 'Dashboard';
            ConnectData['AgentId'] = '<?php echo $agentUniqId; ?>';
            ConnectData['AgentName'] = '<?php echo $agentName . "-AD"; ?>';
            ConnectData['ReportingURL'] = reportingurl;
            ws.send(JSON.stringify(ConnectData));
        };
        ws.onmessage = function (JobStatus) {
            LogToConsole('Message Received : ' + JobStatus.data);
            var JsonMsg = JSON.parse(JobStatus.data);
            var Status = JsonMsg.Status;
            var ServiceTag = JsonMsg.ServiceTag;
            var searchVal = $('#searchValue').val();
            if (Status === 'ADMessage') {
                var AdMessage = JsonMsg.ADResponse;
                if (ServiceTag === searchVal) {
                    var adstatus = AdMessage.split('&&&');
                    var successstatus = adstatus[4];
                    var dartnum = adstatus[1];
                    var questions = sessionStorage.questions; //    sessionStorage don't know where this variable is declared may be in node
                    if (successstatus === '0') {
                        $('.rightside').hide();
                        $('#statuserr').hide();
                        $('#statussuc').show();
                        $('.dataloaderimg2').hide();
                        if (dartnum === '3') {
                            //$('#status_success').fadeIn();
//                            $('#status_success').html('Account unlocked succesfully.');
                            //$('#status_success').fadeOut(5000);
                            $.notify("Account unlocked succesfully");
                        }
                        if (dartnum === '2') {
                            //$('#status_success').fadeIn();
//                            $('#status_success').html('Successfully modified the password for the selected user.');
                            //$('#status_success').fadeOut(5000);
                            $.notify("Successfully modified the password for the selected user");
                        }
                        if (dartnum === '4') {
                            //$('#status_success').fadeIn();
//                            $('#status_success').html('Security answers have been successfully reset.');
                            //$('#status_success').fadeOut(5000);
                            $.notify("Security answers have been successfully reset");
                        }
                        if (dartnum === '1') {
                            //$('#status_success').fadeIn();
//                            $('#status_success').html('The security questions have been configured successfully.');
                            //$('#status_success').fadeOut(5000);
                            $.notify("The security questions have been configured successfully");
                        }
                    } else {
                        if ($.isNumeric(successstatus)) {
                            if (successstatus === '5') {
                                $('.rightside').hide();
                                $('#statuserr').hide();
                                $('#statussuc').show();
//                                $('#status_success').html('Successfully modified the password for the selected user. <br/>User need to change password at next login.');
                                $.notify("The user password has been changed successfully. The user must change the password at the next login.");
                            } else if (dartnum === '4' || dartnum === '3') {
                                var success = successStatusSet(errStat[successstatus]);
                                $('.rightside').hide();
                                $('#statuserr').show();
                                $('#statussuc').hide();
//                                $('#status_perr').html(success);
                                $.notify(success);
                                rightContainerSlideClose('wg-config-questions');
                            } else {
                                var success = successStatusSet(errStat[successstatus]);
                                $('.rightside').hide();
                                $('#statuserr').show();
                                $('#statussuc').hide();
                                $('.dataloaderimg2').hide();
                                $('#cancel,#resetPass').show();
//                                $('#status_perr').text(success);
                                $.notify(success);
                                $('#procRow').show();
                                $('fieldset').removeAttr("disabled");
                                $("#Change_Pass").hide();
                                $("#security_ques").hide();
                                //                                            $("#Default").show();
                            }
                        } else {
                            var success = successStatusSet(successstatus);
                            $('.rightside').hide();
                            $('#statuserr').show();
                            $('#statussuc').hide();
                            $('.dataloaderimg2').hide();
                            $('#cancel,#resetPass').show();
//                            $('#status_perr').text(success);
                            $.notify(success);
                            $('#procRow').show();
                            $('fieldset').removeAttr("disabled");
                            $("#Change_Pass").hide();
                            $("#security_ques").hide();
                            $("#Default").show();
                        }
                    }
                }
            }
        };
        ws.onclose = function () {
            setTimeout(function () {
                wsconnect(wsurl);
            }, 2000);
        };
    }
});


function LogToConsole(Msg) {
  console.log(Msg);
}

function trimStr(str) {
    return str.replace(/^\s+|\s+$/g, '');
}

function ExecuteDirectJob(Servicetag, DirectJob) {
    //alert(Servicetag);
    //return;
    //DirectJob = Math.round(new Date().getTime()/1000) + '###' + DirectJob;
    var JobData = {};
    JobData['Type'] = 'ExecuteDirectJob';
    JobData['ServiceTag'] = trimStr(Servicetag);
    JobData['DirectJob'] = 'InstantExecution---' + DirectJob;
    LogToConsole('DirectJob : ' + DirectJob);
    ws.send(JSON.stringify(JobData));
}

var requestSent = '';
var domain = false;

$('#changePass').click(function () {
    isMachineSelect('changePass');

});
$('#changePassWG').click(function () {
    isMachineSelect('changePassWG');

});
$('#unlockAcc').click(function () {
    isMachineSelect('unlockAcc');

});
$('#changeSecq').click(function () {
    isMachineSelect('changeSecq');

});
$('#changeSecqWG').click(function () {
    isMachineSelect('changeSecqWG');

});
$('#configure').click(function () {
    isMachineSelect('configure');

});
$('#configureWG').click(function () {
    isMachineSelect('configureWG');

});
//            $("#proceed").click(function() {
//
//                $("#userNameErr").text('');
//                $("#domainRowErr").text('');
//                $("#userNameErr").fadeIn();
//                $("#domainRowErr").fadeIn();
//                var uname = $("#userName").val();
//                var dom = $("#domain").val();
//                alert(uname);
//                alert(dom);
//                if(requestSent === 'configure'){
//                    if (dom === "" && domain) {
//                        alert('inside 1');
//                        $("#domainRowErr").text("Please enter the Domain Name");
//                        //$("#domainRowErr").fadeOut('3000');
//                        return false;
//                     }
//                     configure();
//                } else {
//                    alert('else');
//                    if (uname === "") {
//                        $("#userNameErr").text("Please enter the User Name");
////                        $("#userNameErr").fadeOut('3000');
//                        return false;
//                    }
//                    if (dom == "") {
//                        alert('insid');
//                        $("#domainRowErr").text("Please enter the Domain Name");
////                        $("#domainRowErr").fadeOut('3000');
//                        return false;
//                    }
//                    $('fieldset').attr("disabled", "disabled");
//                    $('#procRow').hide();
//                    if (requestSent === 'changeSecQ') {
//                        changeSecAnw();
//                    }
//                    if (requestSent === 'unlockAcnt') {
//                        unclockAcnt();
//                    }
////                    if (requestSent === 'changePass') {
////                        changePass();
////                    }
//                    }
//
//                 /*if( requestSent === 'changeSecQ'){
//                    if (uname === "") {
//                        $("#userNameErr").text("Please enter the User Name");
//                        $("#userNameErr").fadeOut('3000');
//                        return false;
//                    }  if (dom === "" && domain) {
//                        $("#domainRowErr").text("Please enter the Domain Name");
//                        $("#domainRowErr").fadeOut('3000');
//                        return false;
//                    }
//                    changeSecAnw();
//                }
//
//               if (requestSent === 'changeSecQ') {
//
//                        changeSecAnw();
//                }
//                if (uname === "") {
//                    $("#userNameErr").text("Please enter the User Name");
//                    $("#userNameErr").fadeOut('3000');
//                } else if (dom === "" && domain) {
//                    $("#domainRowErr").text("Please enter the Domain Name");
//                    $("#domainRowErr").fadeOut('3000');
//                } else {
//                    $('fieldset').attr("disabled", "disabled");
//                    $('#procRow').hide();
//                    if (requestSent === 'configure') {
//                        configure();
//                    } else if (requestSent === 'changeSecQ') {
//                        changeSecAnw();
//                    } else if (requestSent === 'unlockAcnt') {
//                        unclockAcnt();
//                    } else if (requestSent === 'changePass') {
//                        changePass();
//                    }
//                }*/
//
//            });
$("#cancel").click(function () {
    hideAll();
    $(".btnRow").show();
    domain = false;
});

function hideAll() {
    $(".btnRow").hide();
    $('#userRow').hide();
    $('#domainRow').hide();
    $('#procRow').hide();
}
function changePass() {

    var userName = $('#domain_userName').val();
    var domain = $('#domain').val();
    var host = $('#searchValue').val();
    var socketId = $('#socketId').val();
    var userId = $('#userId').val();

    $('.loader').hide();

    if (userName === '') {
        $('#userNameErr').show();
//        $('#userNameErr').text('Please enter the name.');
        $.notify("Please enter name");
        $('#dmn').empty();
        $('fieldset').removeAttr("disabled");
        $("#procRow").show();
    } else if (domain === '') {
        $('#usr').empty();
        $('#domainRowErr').show();
//        $('#domainRowErr').text('please enter  domain.');
        $.notify("Please enter the domain name");
        $('fieldset').removeAttr("disabled");
        $("#procRow").show();
    } else {
        $('#usr').empty();
        $('#dmn').empty();
        $('fieldset').attr("disabled", "disabled");
        $('#Default').hide();
        $('#Change_Pass').show();
        //                    $('#Default').load('changePassAgent.php', 'id=1&name=' + userName + '&dom=' + domain + '&searchValue=' + host + '&socketId=' + socketId + '&userId=1');

    }
}

function configure() {
    ws_refresh(); // refresh ws connection
    var domain = $('#domain').val();
    var userName = $('#userName').val();
    var host = $('#searchValue').val();
    var socketId = $('#socketId').val();

    if (domain === '') {
        $('.loader').hide();
        $('#usr').empty();
        $('#domainRowErr').show();
//        $('#domainRowErr').text('please enter domain.');
        $.notify("Please enter the domain name");
        $('fieldset').removeAttr("disabled");
        $("#procRow").show();
    } else {
        userName = "";
        $.ajax({
            url: "../admin/configureQuesAgent.php",
            type: 'post',
            data: {name: userName, dom: domain, searchValue: host, socketId: socketId, 'csrfMagicToken': csrfMagicToken}
        })
                .done(function (msg) {
                    $('.loader').hide();
                    if (msg === 1) {
                        $('#usr').empty();
                        $('#dmn').empty();
                        $('.afterAuth').show();
                        $('.beforeAuth').hide();
                    } else {
                        $('#usr').empty();
                        $('#dmn').empty();
                        $('fieldset').attr("disabled", "disabled");
                        $('#Default').empty();
                        $('#Default').html(msg);
                    }
                });
    }
}

function changeSecAnw() {
    var userName = $('#domain_userName').val();
    var host = $('#searchValue').val();
    var domain = $('#domain').val();

    if (userName === '') {
//        $('#userNameErr').show();
//        $('#userNameErr').text('Please enter the name.');
        $('.loader').hide();
        $.notify("Please enter name");
        return false;
        $('#dmn').empty();
        $('fieldset').removeAttr("disabled");
        $("#procRow").show();
    } else if (domain === '') {
        $('.loader').hide();
        $('#usr').empty();
        $('#domainRowErr').show();
//        $('#domainRowErr').text('please enter domain.');
        $.notify("Please enter the domain name");
        return false;
        $('fieldset').removeAttr("disabled");
        $("#procRow").show();
    } else {
        $('#usr').empty();
        $('#dmn').empty();
        $('fieldset').attr("disabled", "disabled");
        var domOrWg = 0;
        if (domain === "NULL" || !domain) {
            domOrWg = 2;
        } else {
            domOrWg = 1;
        }
        //userName = "administrator";
        var msgconf = '252&&&1&&&4&&&Request : Reset Security Answers&&&' + domOrWg + '&&&' + userName + '&&&' + domain + '&&&&&&';
        var msg = btoa(msgconf) + '252&&&';
        console.log(msgconf);
        $('.loader').hide();
        ExecuteDirectJob(host, msg); //  enable before svnup
        //                    var ADREQUEST = {};
        //                    ADREQUEST['Type'] = 'ADREQUEST';
        //                    ADREQUEST['ServiceTag'] = $('#searchValue').val();
        //                    ADREQUEST['ADMessage'] = 'InstantExecution---' + msg;
        //                    LogToConsole(JSON.stringify(ADREQUEST));
        //                    ws.send(JSON.stringify(ADREQUEST));
    }
}

function unclockAcnt() {
    var userName = $('#domain_userName').val();
    var host = $('#searchValue').val();
    var domain = $('#domain').val();
    if (userName === '') {
        $('.loader').hide();
//        $('#unlck_Accnterr').text('Please enter the name.');
//        $("#unlck_Accnterr").fadeOut(3000);
        $.notify("Please enter name");
        return false;
    } else if (domain === '') {
        $('.loader').hide();
//        $('#unlck_Accnterr').text('please enter domain.');
//        $("#unlck_Accnterr").fadeOut(3000);
        $.notify("Please enter the domain name");
        return false;
    } else {
        $('#usr').empty();
        $('#dmn').empty();
        var domOrWg = 0;
        domOrWg = 1;
        //userName = "administrator";
        var msgconf = '252&&&1&&&3&&&Request : Unlock Account&&&' + domOrWg + '&&&' + userName + '&&&' + domain + '&&&&&&';
        var msg = btoa(msgconf) + '252&&&';
        console.log(msgconf);
        $('.loader').hide();

        ExecuteDirectJob(host, msg); // enable before snvup
        //                    var ADREQUEST = {};
        //                    ADREQUEST['Type'] = 'ADREQUEST';
        //                    ADREQUEST['ServiceTag'] = $('#searchValue').val();
        //                    ADREQUEST['ADMessage'] = 'InstantExecution---' + msg;
        //                    LogToConsole(JSON.stringify(ADREQUEST));
        //                    ws.send(JSON.stringify(ADREQUEST));
    }
}

//------submit for change password------------------------------
$('#resetPass').click(function () {

    var userName = $('#domain_userName').val();
    var host = $('#searchValue').val();
    var domain = $('#domain').val();
    var nPass = $('#newPass').val();
    var cPass = $('#confirmPass').val();
    nPass = nPass.trim();
    cPass = cPass.trim();
    if (nPass === cPass && (nPass !== '' || cPass !== '')) {
        $('#perr').empty();
        $('.dataloaderimg2').show();
        $('#resetPass,#cancelCP').hide();

        var domOrWg = 0;
        if (domain === "NULL" || !domain) {
            domOrWg = 2;
        } else {
            domOrWg = 1;
        }
        var userId = $("#userId").val();
        //userName = "administrator";
        var msgconf = '252&&&1&&&2&&&Request : Password Reset&&&' + domOrWg + '&&&' + userName + '&&&' + domain + '&&&' + nPass + '&&&';
        var msg = btoa(msgconf) + '252&&&';
        console.log(msgconf);
        $.notify("Password has been successfully updated ");
        rightContainerSlideClose('wg-config-questions');
        ExecuteDirectJob(host, msg);  // enable before svnup
        //                    var ADREQUEST = {};
        //                    ADREQUEST['Type'] = 'ADREQUEST';
        //                    ADREQUEST['ServiceTag'] = $('#searchValue').val();
        //                    ;
        //                    ADREQUEST['ADMessage'] = 'InstantExecution---' + msg;
        //                    LogToConsole(JSON.stringify(ADREQUEST));
        //                    ws.send(JSON.stringify(ADREQUEST));
    } else {
        $.notify('Your password and confirmation password do not match');
        return false;
    }
});
//-------cancel------------------
$('#cancelCP').click(function () {
    $('#procRow').show();
    $('fieldset').removeAttr("disabled");
    $("#Change_Pass").hide();
    $("#security_ques").hide();
    $("#Default").show();
});
//-----submit for configure questions--------------------------------------
$('#chck').click(function () {

    var host = $('#searchValue').val();
    //userName = $('#userName').val();
    userName = $('#domain_userName').val();
    var domain = $('#domain').val();
    var q1 = $('#q1').val();
    var q2 = $('#q2').val();
    var q3 = $('#q3').val();
    var q4 = $('#q4').val();
    var q5 = $('#q5').val();
    sessionStorage.questions = q1 + '#' + q2 + '#' + q3 + '#' + q4 + '#' + q5;
    if (q1 === '' || /^[a-zA-Z0-9 ]*$/.test(q1) === false) {
        $.notify("Please enter Question1. (Special characters are not accepted)");
        return false;
    } else if (q2 === '' || /^[a-zA-Z0-9 ]*$/.test(q2) === false) {
        $.notify("Please enter Question2. (Special characters are not accepted)");
        return false;
    } else if (q3 === '' || /^[a-zA-Z0-9 ]*$/.test(q3) === false) {
        $.notify("Please enter Question3. (Special characters are not accepted)");
        return false;
    } else if (q4 === '' || /^[a-zA-Z0-9 ]*$/.test(q4) === false) {
        $.notify("Please enter Question4. (Special characters are not accepted)");
        return false;
    } else if (q5 === '' || /^[a-zA-Z0-9 ]*$/.test(q5) === false) {
        $.notify("Please enter Question5. (Special characters are not accepted)");
        return false;
    } else {
        var domOrWg = 0;
        if (domain === "NULL" || !domain) {
            domOrWg = 2;
        } else {
            domOrWg = 1;
        }
        userId = $("#userId").val();

        var msgconf = '252&&&' + domOrWg + '&&&1&&&Request : Configure Security Questions&&&' + domOrWg + '&&&' + userName + '&&&' + domain + '&&&Ques1:' + q1 + '$$$Ques2:' + q2 + '$$$Ques3:' + q3 + '$$$Ques4:' + q4 + '$$$Ques5:' + q5 + '&&&';
        var msg = btoa(msgconf) + '252&&&';
        console.log(msgconf);
        rightContainerSlideClose('wg-config-questions');
        ExecuteDirectJob(host, msg); // enable before svnup

        saveConfiguredQuestion(domain, q1, q2, q3, q4, q5);
    }
});

//-------cancel------------------
$('#cancl').click(function () {
    $('fieldset').removeAttr("disabled");
    $("#Change_Pass").hide();
    $("#security_ques").hide();
    $("#domainRow").hide();
    $(".btnRow").show();
    $("#Default").show();
});

// function to check if machine is selected or not
function isMachineSelect(selectOpt) {

    $('#selected_ad').val(selectOpt);
    var level = $('#searchType').val();
    var machineId = $('#searchValue').val();

    if (level == 'Sites' || machineId == 'Groups') {
        // $("#infoMsg").html("Please choose a device that is online ");
        $.notify("Please choose a device that is online ");
        $("#notification").modal("show");
//        translation.start();
    } else {
        $.ajax({
            type: "post",
            url: '../lib/l-ajax.php',
            dataType: 'json',
            data: {
                'function': 'AJAX_GetMachOnlineStatus',
                'host': machineId,
                'csrfMagicToken': csrfMagicToken
            },
            success: function (data) {
                if (data === 'Offline' || data === 'offline') {
                    $.notify('Please choose a device that is online ');
                    $("#notification").modal("show");
                    rightContainerSlideClose('wg-config-questions');
                } else {
                    hideAll();
                    $('#usr').empty();
                    $('#dmn').empty();
                    $('#userRow').show();
                    $("#procRow").show();

                    if (selectOpt == 'changePass') {
                        $("#domain").val("");
                        $('#domainRow').show();
                        requestSent = 'changePass';
                        domain = false;
                    } else if (selectOpt == 'changePassWG') {
                        $("#domain").val("NULL");
                        requestSent = 'changePass';
                        domain = true;
                    } else if (selectOpt == 'unlockAcc') {
                        $("#domain").val("");
                        $('#domainRow').show();
                        requestSent = 'unlockAcnt';
                        domain = false;
                    } else if (selectOpt == 'changeSecq') {
                        $("#domain").val("");
                        $('#domainRow').show();
                        requestSent = 'changeSecQ';
                        domain = false;
                    } else if (selectOpt == 'changeSecqWG') {
                        $("#domain").val("NULL");
                        requestSent = 'changeSecQ';
                        domain = true;
                    } else if (selectOpt == 'configure') {
                        $('#userRow').hide();
                        $("#domain").val("");
                        $('#domainRow').show();
                        requestSent = 'configure';
                        domain = false;
                        getConfiguredQuestion();
                    } else if (selectOpt == 'configureWG') {
                        $('#userRow').hide();
                        $('#usr').empty();
                        $('#dmn').empty();
                        //$('#domainRowWG').show();
                        $("#userName").val("");
                        $("#domain").val("NULL");
                        requestSent = 'configure';
                        //$("#procRow").show();
                        $("#proceed").click();
                        $('.loader').hide();
                    }
                    rightContainerSlideOn('wg-config-questions');
                }
            }
        });
    }
}

$("#proceed").click(function () {
//    $('#errDomain').html('Please enter the domain name name');
    $('#loader').show();
    var check_domainname = $('#domain').val();
    var seleAD = $('#selected_ad').val();

    if (check_domainname == '') {
        $.notify("Please enter the domain name name");
        return false;
    }

    if (seleAD == "changePassWG") {
        var wgchgpwd = $('#domain_userName').val();
        if (wgchgpwd == '') {
//            $("#wgchngpwd_err").html("Please enter the Username");
            $.notify("Please enter the Username");
            return false;
        }

    }
    if (seleAD == "changeSecqWG") {
        var wgchgpwd = $('#domain_userName').val();
        if (wgchgpwd == '') {
//            $("#wgchngpwd_err").html("Please enter the Username");
            $.notify("Please enter the Username");
            return false;
        }

    }
    if (seleAD == "configure") {
        var wgchgpwd = $('#domain').val();
        if (wgchgpwd == '') {
//            $("#wgchngpwd_err").html("Please enter the domain name");
            $.notify("Please enter the domain name");
            return false;
        }

    }
    if (seleAD == "changePass") {
        var wgchgpwd = $('#domain_userName').val();
        var wgchgdomain = $('#domain').val();
        if (wgchgpwd == '') {
//            $("#wgchngpwd_err").html("Please enter the Username");
            $.notify("Please enter the Username");
            return false;
        }
        if (wgchgdomain == '') {
//            $("#wgchngpwd_err").html("Please enter the domain name");
            $.notify("Please enter the domain name");
            return false;
        }

    }
    if (seleAD == "changeSecq") {
        var wgchgpwd = $('#domain_userName').val();
        var wgchgdomain = $('#domain').val();
        if (wgchgpwd == '') {
//            $("#wgchngpwd_err").html("Please enter the Username");
            $.notify("Please enter the Username");
            return false;
        }
        if (wgchgdomain == '') {
//            $("#wgchngpwd_err").html("Please enter the domain name");
            $.notify("Please enter the domain name");
            return false;
        }

    }
    if (seleAD == "unlockAcc") {
        var wgchgpwd = $('#domain_userName').val();
        var wgchgdomain = $('#domain').val();
        if (wgchgpwd == '') {
//            $("#wgchngpwd_err").html("Please enter the Username");
            $.notify("Please enter the Username");
            return false;
        }
        if (wgchgdomain == '') {
//            $("#wgchngpwd_err").html("Please enter the domain name");
            $.notify("Please enter the domain name");
            return false;
        }

    }


    if (check_domainname == '') {
//        $('#errDomain').html('Please enter the domain name name');
        $.notify("Please enter the domain name name");
        return false;
    } else {
        $('fieldset').attr("disabled", "disabled");
        $('#procRow').hide();
        if (requestSent === 'configure') {
            $(".chckcircle").css("display", "block");
            $(".proceedcircle").css("display", "none");
            $(".sec_que").css("display", "block");
            $(".domain_div_sub").css("display", "none");
            $(".domain_div").css("display", "none");
            configure();
        } else if (requestSent === 'changeSecQ') {
            $(".sec_que").css("display", "none");
            changeSecAnw();
        } else if (requestSent === 'unlockAcnt') {
            unclockAcnt();
        } else if (requestSent === 'changePass') {
            $(".chckcircle").css("display", "none");
            $(".proceedcircle").css("display", "none");
            $(".resetPasscircle").css("display", "block");
            $(".domain_div_sub").css("display", "none");
            $(".domain_div").css("display", "none");
            $(".username_div").css("display", "none");
            $(".sec_que").css("display", "none");
            $(".chngpwd_div").css("display", "block");
            $(".resetPasscircle").css("display", "block");
            changePass();
        }
    }

});

function refreshData() {
    location.reload();
}

function successStatusSet(value) {
    var msg = '';
    if (value == '2') {
        msg = 'User not registered';
    } else if (value == '16') {
        msg = 'Please Set Impersonation';
    } else if (value == '11') {
        msg = 'User Not Found';
    } else if (value == '1') {
        msg = 'AD Not Operational';
    } else if (value == '3') {
        msg = 'No Attributes Found';
    } else if (value == '4') {
        msg = 'Not Authenticate User';
    } else if (value == '6') {
        msg = 'Reset Password Failed';
    } else if (value == '7') {
        msg = 'Unlock Access Failed';
    } else if (value == '10') {
        msg = 'Bad Server Data';
    } else if (value == '12') {
        msg = 'Not Dedicated Machine';
    } else if (value == '13') {
        msg = 'Question Config Failed';
    } else if (value == '14') {
        msg = 'Domain Ip Fetch Failed';
    } else if (value == '25') {
        msg = 'User Authentication Failed';
    } else if (value == '17') {
        msg = 'Reset Answer Failed';
    } else if (value == '24') {
        msg = 'Question Description Changed';
    } else if (value == '26') {
        msg = 'Unknown Error';
    } else {
        msg = value;
    }
    return msg;
}

$('#userName').keydown(function () {
    $('#userNameErr').hide();
})
$('#domain').keydown(function () {
    $('#domainRowErr').hide();
})


function ws_refresh() {
    var errStat = [
        'Success.', 'Unable to access AD server.', 'User is not registered to use Nanoheal Password Reset functionality.', 'Unable to fetch user data from AD Server.', 'Unable to authenticate user.', 'Successfully changed the password. User need to change password at next log in.', 'Unable to reset password.', 'Unable to unlock Account.'
    ];
    errStat[17] = 'Unable to reset security answers.';
    errStat[16] = 'Impersonation credentials are not there in Dart 43.';
    errStat[10] = 'Data sent by server is not proper.';
    errStat[11] = 'Unable to locate user in specified domain.';
    errStat[12] = 'Selected machine is not the specified dedicated machine.';
    errStat[13] = 'Unable to configure security questions.';
    errStat[14] = 'Unable to access AD server IP.';
    errStat[15] = 'Unable to authenticate user.';
    errStat[21] = 'Unable to authenticate user.';
    errStat[24] = 'Security Questions has been changed.';
    errStat[26] = 'Please try again.Unknown error occoured.';
    var wsurl = getwsurl();
    var reportingurl = '<?php echo $reportingurl; ?>';
    if (window.location.protocol !== "https:") {
        wsconnect('ws://' + wsurl, reportingurl);
        LogToConsole('Connecting to Communication Server : ' + 'http://' + wsurl);
    } else {
        wsconnect('wss://' + wsurl, reportingurl);
        LogToConsole('Connecting to Communication Server (AD) : ' + 'https://' + wsurl);
    }

    function wsconnect(wsurl, reportingurl) {
        ws = new WebSocket(wsurl);
        ws.onopen = function () {
            LogToConsole('Connecting to Communication Server Success');
            var ConnectData = {};
            ConnectData['Type'] = 'Dashboard';
            ConnectData['AgentId'] = '<?php echo $agentUniqId; ?>';
            ConnectData['AgentName'] = '<?php echo $agentName . "-AD"; ?>';
            ConnectData['ReportingURL'] = reportingurl;
            ws.send(JSON.stringify(ConnectData));
        };
        ws.onmessage = function (JobStatus) {
            LogToConsole('Message Received : ' + JobStatus.data);
            var JsonMsg = JSON.parse(JobStatus.data);
            var Status = JsonMsg.Status;
            var ServiceTag = JsonMsg.ServiceTag;
            var searchVal = $('#searchValue').val();
            if (Status === 'ADMessage') {
                var AdMessage = JsonMsg.ADResponse;
                if (ServiceTag === searchVal) {
                    var adstatus = AdMessage.split('&&&');
                    var successstatus = adstatus[4];
                    var dartnum = adstatus[1];
                    var questions = sessionStorage.questions; //    sessionStorage don't know where this variable is declared may be in node
                    if (successstatus === '0') {
                        $('.rightside').hide();
                        $('#statuserr').hide();
                        $('#statussuc').show();
                        $('.dataloaderimg2').hide();
                        if (dartnum === '3') {
                            //$('#status_success').fadeIn();
//                            $('#status_success').html('Account unlocked succesfully.');
                            //$('#status_success').fadeOut(5000);
                            $.notify("Account unlocked succesfully");
                        }
                        if (dartnum === '2') {
                            //$('#status_success').fadeIn();
//                            $('#status_success').html('Successfully modified the password for the selected user.');
                            //$('#status_success').fadeOut(5000);
                            $.notify("Successfully modified the password for the selected user");
                        }
                        if (dartnum === '4') {
                            //$('#status_success').fadeIn();
//                            $('#status_success').html('Security answers have been successfully reset.');
                            //$('#status_success').fadeOut(5000);
                            $.notify("Security answers have been successfully reset");
                        }
                        if (dartnum === '1') {
                            //$('#status_success').fadeIn();
//                            $('#status_success').html('The security questions have been configured successfully.');
                            //$('#status_success').fadeOut(5000);
                            $.notify("The security questions have been configured successfully");
                        }
                    } else {
                        if ($.isNumeric(successstatus)) {
                            if (successstatus === '5') {
                                $('.rightside').hide();
                                $('#statuserr').hide();
                                $('#statussuc').show();
//                                $('#status_success').html('Successfully modified the password for the selected user. <br/>User need to change password at next login.');
                                $.notify("The user password has been changed successfully. The user must change the password at the next login.");
                            } else if (dartnum === '4' || dartnum === '3') {
                                var success = successStatusSet(errStat[successstatus]);
                                $('.rightside').hide();
                                $('#statuserr').show();
                                $('#statussuc').hide();
//                                $('#status_perr').html(success);
                                $.notify(success);
                                rightContainerSlideClose('wg-config-questions');
                            } else {
                                var success = successStatusSet(errStat[successstatus]);
                                $('.rightside').hide();
                                $('#statuserr').show();
                                $('#statussuc').hide();
                                $('.dataloaderimg2').hide();
                                $('#cancel,#resetPass').show();
//                                $('#status_perr').text(success);
                                $.notify(success);
                                $('#procRow').show();
                                $('fieldset').removeAttr("disabled");
                                $("#Change_Pass").hide();
                                $("#security_ques").hide();
                                //                                            $("#Default").show();
                            }
                        } else {
                            var success = successStatusSet(successstatus);
                            $('.rightside').hide();
                            $('#statuserr').show();
                            $('#statussuc').hide();
                            $('.dataloaderimg2').hide();
                            $('#cancel,#resetPass').show();
//                            $('#status_perr').text(success);
                            $.notify(success);
                            $('#procRow').show();
                            $('fieldset').removeAttr("disabled");
                            $("#Change_Pass").hide();
                            $("#security_ques").hide();
                            $("#Default").show();
                        }
                    }
                }
            }
        };
        ws.onclose = function () {
            setTimeout(function () {
                wsconnect(wsurl);
            }, 2000);
        };
    }
}

function getConfiguredQuestion() {
    var actdata = {
        'function' : 'getConfiguredDetails', 'csrfMagicToken': csrfMagicToken
    };
    $.ajax({
        url: "../lib/l-activedir.php",
        data: actdata,
        type: 'POST',
        success: function (data) {
            var resdata = JSON.parse(data);
            if(resdata['status'] == 'success') {
                var actdirdata = resdata['rdata'];
                $('#domain').val(actdirdata['domain']);
                $('#domain_userName').val(resdata['impersonateuser']);

                var questdata = actdirdata['questions'].split('###');
                $('#q1').val(questdata[0]);
                $('#q2').val(questdata[1]);
                $('#q3').val(questdata[2]);
                $('#q4').val(questdata[3]);
                $('#q5').val(questdata[4]);

                rightContainerSlideOn('wg-config-questions');
            } else {
                $('#domain_userName').val(resdata['impersonateuser']);
                $.notify('No configuration found')
            }
        },
        error: function (err) {
            console.log('Error : ' + err);
        }
    });
}

function saveConfiguredQuestion(domain, q1, q2, q3, q4, q5) {
    var actdata = {
        'function' : 'saveConfiguredQuestionDetails',
        'domain': domain,
        'q1': q1,
        'q2': q2,
        'q3': q3,
        'q4': q4,
        'q5': q5, 'csrfMagicToken': csrfMagicToken
    };

    $.ajax({
        url: "../lib/l-activedir.php",
        data: actdata,
        type: 'POST',
        success: function (data) {
            if($.trim(data) == 'success') {
                console.log('Configured details has been saved Succesully');
            } else {
                console.log('Failed to saved configured details!');
            }
        },
        error: function (err) {
            console.log('Error : ' + err);
        }
    });
}
