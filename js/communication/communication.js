/* global wNUrl, io, mNUrl, arrAllcheckVal, glMachineName, google, bootbox, jQuery_1_7 */

var ds = 1;

var cancelVal = 0;
var glmachineName = '';
var watchID;
var laslist;
var listening = 0;

var themeValue = 0;
var productType = "DPMC";
var lang = 'en';

$(document).ready(function() {
    // Connect to Both Socket IO
    //$.getScript("../config.js", function () {
        connectToWindowsNodeServer(function () {
            connectToMacNodeServer(function () {
            });
        });
    //});
});

function connectToWindowsNodeServer(callback) {
    if (wSocket === '' || wSocket === undefined) {
        //$.getScript(wNUrl + "/socket.io/socket.io.js").done(function () {
            require.config({
                paths: {
                    socketio: wNUrl+'/socket.io/socket.io'
                }
            });

            require(['socketio'], function(io) {
                wSocket = io.connect(wNUrl);
                console.log('socket connected');
            });
            //wSocket = io.connect(wNUrl);
            callback();
       // });
    }
}

function connectToMacNodeServer(callback) {
    if (mSocket === '' || mSocket === undefined) {
            require.config({
                paths: {
                    socketio: mNUrl+'/socket.io/socket.io'
                }
            });

            require(['socketio'], function(io) {
                wSocket = io.connect(mNUrl);
                console.log('socket connected');
            });
        //$.getScript(mNUrl + "/socket.io/socket.io.js").done(function () {
            //mSocket = io.connect(mNUrl);
            //callback();
        //});
    }
}

function emitDart(serviceTag, OS) {

    /*var emitMsg = serviceTag.split('##');
    if (wSocket === '' || wSocket === undefined) {
        $.getScript("../config.js", function () {
            connectToWindowsNodeServer(function () {
                //if (glmachineName !== '' && listening === 0) {
                    showWinDartStaus();
                //}
                for (var i = 0; i < emitMsg.length - 1; i++) {
                    wSocket.emit('executeDart', emitMsg[i]);
                }
            });
        });
    } else {
//        if (glmachineName !== '' && listening === 0) {
            showWinDartStaus();
//        }
        for (var i = 0; i < emitMsg.length - 1; i++) {
            wSocket.emit('executeDart', emitMsg[i]);
        }
    }
    $(".loadingStage").css({'display': 'none'});
    return;*/
    glmachineName = serviceTag;

    if (OS.toLowerCase().indexOf("windows") !== -1 || OS.toLowerCase().indexOf("mac") !== -1) {
        var OSName = '';
        if (OS.toLowerCase().indexOf("windows") !== -1) {
            OSName = 'windows';
        } else {
            OSName = 'mac';
        }
        $.ajax({
            type: "GET",
            url: "../communication/communication_ajax.php",
            data: "function=getTempAuditdata&os=" + OSName + "&csrfMagicToken=" + csrfMagicToken,
            success: function (msg) {
                if (msg !== '') {
                    var emitMsg = msg.split('##');
                    if (wSocket === '' || wSocket === undefined) {
                        //$.getScript("../config.js", function () {
                            connectToWindowsNodeServer(function () {
                                if (glmachineName !== '' && listening === 0) {
                                    showWinDartStaus();
                                }
                                for (var i = 0; i < emitMsg.length - 1; i++) {
                                    wSocket.emit('executeDart', emitMsg[i]);
                                }
                            });
                        //});
                    } else {
                        if (glmachineName !== '' && listening === 0) {
                            showWinDartStaus();
                        }
                        for (var i = 0; i < emitMsg.length - 1; i++) {
                            wSocket.emit('executeDart', emitMsg[i]);
                        }
                    }
                    $(".loadingStage").css({'display': 'none'});
                }
            }
        });
    } else if (OS.toLowerCase().indexOf("android") !== -1) {

        $.ajax({
            type: "GET",
            url: "../communication/communication_ajax.php",
            data: "function=getScheduledata&csrfMagicToken=" + csrfMagicToken,
            success: function (msg) {

                var machineList = '';
                var res;

                if (msg !== '') {

                    var emitMsg = msg.split('##');
                    for (var i = 0; i < emitMsg.length - 1; i++) {
                        res = emitMsg[i].split('---');
                        if (machineList === '') {
                            machineList = res[4];
                        } else {
                            machineList += "," + res[4];
                        }
                        GCMJobs(emitMsg[i]);
                    }

                    if (machineList !== '') {
                        $.ajax({
                            type: "GET",
                            url: "../communication/communication_ajax.php",
                            data: "function=updateAuditData&machineList=" + machineList + "&csrfMagicToken=" + csrfMagicToken,
                            success: function (msg) {
                            }
                        });
                    }

                    $(".loadingStage").css({'display': 'none'});
                }
            }
        });
    }
}

function GCMJobs(msg) {
    var res = msg.split('---');
    var job = unescape(res[5]);
    var msgsend = res[4] + '###' + job;
    var gcmid = res[3];
    //msgsend = msgsend.replace(/CID/g, '942763bb6e34e9c3eae2cbd8ccb74526');
    //console.log('Triggering : ' + msgsend);
    console.log(msgsend);
    msgsend = escape(msgsend);
    console.log(msgsend);
    //console.log('Encoded : ' + msgsend);
    $.ajax({
        type: "GET",
        url: "../communication/gcm.php",
        data: "gcmid=" + gcmid + "&msg=" + msgsend + "&csrfMagicToken=" + csrfMagicToken,
        success: function (msg) {
            $(".loadingStage").css({'display': 'none'});
        }
    });
}

function showWinDartStaus() {
    listening = 1;
    $(".loadingStage").css({'display': 'block'});
    ds = 1;
    wSocket.on('listenjobs', function (msg) {
        $(".loadingStage").css({'display': 'block'});
        if (msg !== '') {
            setfixList(msg, glmachineName, '', ds);
        }
        $("#overlay").css({'display': 'none'});
        $(".loadingStage").css({'display': 'none'});
    });
}

function showMacDartStaus() {
    listening = 1;
    $(".loadingStage").css({'display': 'block'});
    ds = 1;
    mSocket.on('listenjobs', function (msg) {
        $(".loadingStage").css({'display': 'block'});
        if (msg !== '') {
            setfixList(msg, glmachineName, '', ds);
        }
        $("#overlay").css({'display': 'none'});
        $(".loadingStage").css({'display': 'none'});
    });
}

function setfixList(textvar, machineName, dart, pb) {
    console.log(textvar);
    $(".loadingStage").css({'display': 'block'});
    $('#showdartStatus').hide();
    var val1 = textvar.split("---");
    var machineVal = val1[2];
    if (machineVal !== glmachineName) {
        return;
    }
    var val2 = val1[4];
    if (val2 === 'online') {
        return;
    }
    var val = val2.split("=");
    var cancelMachine = "'" + machineName + "'";
    var val;
    var i, j, k;
    var liList = ' <div class="verticalSpace10"></div><div class="fixing-issue-div service-log-fix module--scroll" style="height:190px;position:relative"><ul class="fixing-issue">';
    var len = val.length;

    if (textvar.indexOf("Execution Completed") !== -1 || textvar.indexOf("Execution Terminated") !== -1) {
        k = 4;
    } else {
        for (i = 1; i < val.length - 1; i++) {

            liList += '<li>' + val[i].replace(/[0-9]/g, '');
            //liList += '<li>' + val[i];
            var statusVal = val[i + 1].charAt(0);

            if (statusVal === '1' || statusVal === 1) {
                liList += '<img src="images/ajax-loader.gif" class="pull-right" style="height:20px;width:20px"/>';
            } else if (statusVal === '2' || statusVal === 2) {
                liList += '<img src="images/fixed.png" class="pull-right" style="height:20px;width:20px"/>';
            } else if (statusVal === '3' || statusVal === 3) {
                liList += '<img src="images/processFailed.png" class="pull-right" style="height:20px;width:20px"/>';
            } else {
                liList += '<span class="waiting"></span>';
            }

            liList += '</li>';
            laslist = liList;
            if (i === len - 2) {
                k = val[i + 1].charAt(0);
            }
        }
    }
    liList += '</ul>';
    liList += '</div>';

    if (val2 === 'Execution Completed' || val2 === 'Execution Terminated' || val2 === 'offline') {
        laslist += '</ul>';
        laslist += '</div>';
        liList = laslist;
        if (val2 === 'offline') {
            liList += '<span style="float:right;padding-right:10px;padding-bottom:5px;"><a style="margin-top:10px;cursor:default;padding:5px;color:grey;">System went Offline.</a></span>';
        } else if (cancelVal === 0) {
            liList += '<span style="float:right;padding-right:10px;padding-bottom:5px;"><a style="margin-top:10px;cursor:default;padding:5px;color:grey;">Solution push have been successfully executed.</a></span>';
        } else {
            liList += '<span style="float:right;padding-right:10px;padding-bottom:5px;"><a style="margin-top:10px;cursor:default;padding:5px;color:grey;">Present solution has been cancelled.</a></span>';
            cancelVal = 0;
        }

        $('#showdartStatus').hide();
        /* this is the code for hiding progress - Start */
        setTimeout(function () {
            $("#fixlist").fadeOut("slow", function () {
                $("#fixlist").html('');
                $("#progressMainDiv").hide();
            });
            $("#showdartStatus").fadeOut("slow", function () {

            });
        }, 5000);
        /* this is the code for hiding progress - End */
    } else {
        if (cancelVal === 0) {
            liList += '<div style="float:right;padding-right:10px;padding-bottom:5px;" id="cancelDiv"><a style="background-color:#5c54a4;cursor:pointer;padding:5px;color:#fff;" onclick="cancelDart(' + cancelMachine + ')" id="cancelbtn">Cancel</a></div>';
        } else {
            liList += '<div style="float:right;padding-right:10px;padding-bottom:5px;"><a style="background-color:#5c54a4;cursor:pointer;padding:5px;color:#fff;" id="cancelbtn">Cancelling...</a></div>';
        }
    }
    liList += '</div></div>';
    if (pb === 1) {
        $("#fixlist").show();
    } else {
        $("#fixlist").hide();
    }

    $('.steps_content').css({'display': 'block'});
    $(".loadingStage").css({'display': 'none'});
    $('#fixlist').html(liList);
    $("#executeLoader").hide();
    $("#progressMainDiv").show();

    // Show google map
    if (textvar.indexOf("Get Current Location") !== -1) {
        var msg = val[1];
        msg = msg.replace(",", "|");
        //msg = "12.910217712842098|77.5995003455087"; //$.trim(msg);
        msg = msg.replace(/(\r\n|\n|\r)/gm, "");
        msg = msg.split(',');
        msg = JSON.stringify(msg);
        renderMap(msg);
    }
}

function myFunction() {
    $('.steps_content').hide();
    $('#showdartStatus').show();
    ds = 0;
}

function showStatus() {
    $('.steps_content').show();
    $('#showdartStatus').hide();
    ds = 1;
}

function showDartStaus256(machineName, dart) {
    $(".loadingStage").css({'display': 'block'});
    var liList = '<div class="steps_content" style="width:500px;box-shadow: 0px 0px 10px #c4c4c4;padding-bottom: 10px;background-color:#ffffff;">';
    liList += '<span style="color:grey;padding-top:5px;margin-left: 25px;font-weight: bold;width: 93%;">Resolution progress </span>';
    liList += '<div class="progress-div"><div class="fixes" style="height:100px;overflow-y: auto; overflow-x: hidden; width:100%;margin-top:15px;" ><ul>';
    liList += '<span style="float:right;padding-right:10px;padding-bottom:5px;"><a style="background-color:#ffffff;margin-top:10px;cursor:default;padding:5px;color:grey;">Solution pushed have been successfully executed.</a></span>';
    liList += '</ul>';
    liList += '</div>';
    liList += '</div></div>';

    var time = '';

    if (dart === 256) {
        time = 120000;
    } else {
        time = 5000;
    }

    setTimeout(function () {
        $("#fixlist").fadeOut("slow", function () {
            $("#fixlist").html('');
        });
        $("#showdartStatus").fadeOut("slow", function () {

        });
    }, time);
    $('.steps_content').css({'display': 'block'});
    $(".loadingStage").css({'display': 'none'});
    $('#fixlist').html(liList);
}

function cancelDart(machineName) {

    if (wSocket === '' || wSocket === undefined) {
        connectToNodeServer(function () {
            showDartStaus();
        });
    }

    $.ajax({
        type: "POST",
        url: "../communication/communication_ajax.php",
        data: "function=getCancelMachineDet&machineName=" + machineName
            + '&csrfMagicToken=' + csrfMagicToken,
        success: function (msg) {
            $('#cancelbtn').html('Cancelling...');
            cancelVal = 1;
            wSocket.emit('executeDart', 'dboard---cancel---siteid---' + msg.trim());
        },
        error: function () {
            $(".loadingStage").css({'display': 'none'});
            $("#rightNavtiles").html("<span style='color:#009933; font-size:12px;'>Not Executed</span><br />");
        }
    });
}

//Caption Sliding (Partially Hidden to Visible)
function slideOpenDiv(id) {
    $('.cover_' + id).stop().animate({top: '10px'}, {queue: false, duration: 160});
}

function slideCloseDiv(id, top) {
    $('.cover_' + id).stop().animate({top: top + 'px'}, {queue: false, duration: 160});
}

function in_array(needle, haystack) {
    for (var key in haystack)
    {
        if (needle === haystack[key])
        {
            return true;
        }
    }

    return false;
}

function pad(str, max) {
    str = str.toString();
    return str.length < max ? pad("0" + str, max) : str;
}

function CreateJobAndExecute_old(dart, variable, shortDesc, Jobtype, ProfileName) {

    $("#executeLoader").show();
    $("#executeJob").hide();
    $.ajax({
        type: "POST",
        url: "../communication/communication_ajax.php",
        data: "function=getMachineOS"
            + '&csrfMagicToken=' + csrfMagicToken,
        success: function (data) {
            var res = data.split('####');
            var machineOs = res[0];
            var MachinesList = res[1];
            var cType = res[2];
            if (machineOs.toLowerCase().indexOf("windows") !== -1) {
                if (arrAllcheckVal === '' || arrAllcheckVal === undefined) {
                    WindowsJobExecute(dart, variable, shortDesc, Jobtype, ProfileName, MachinesList, cType);
                } else {
                    WindowsNotificationExecute(dart, variable, shortDesc, Jobtype, ProfileName, arrAllcheckVal, cType);
                }
            } else if (machineOs.toLowerCase().indexOf("android") !== -1) {
                AndroidJobExecute(dart, variable, shortDesc, Jobtype, ProfileName, MachinesList, cType);
            } else if (machineOs.toLowerCase().indexOf("mac") !== -1) {
                MacJobExecute(dart, variable, shortDesc, Jobtype, ProfileName, MachinesList, cType);
            } else if (machineOs.toLowerCase().indexOf("linux") !== -1) {

            }
        }
    });
    setTimeout(function () {
        $("#executeLoader").hide();
    }, 2000);
}

function CreateJobAndExecute_old(dart, variable, shortDesc, Jobtype, ProfileName) {

    $("#executeLoader").show();
    $("#executeJob").hide();
    $.ajax({
        type: "POST",
        url: "../communication/communication_ajax.php",
        data: "function=getMachineOS"
            + '&csrfMagicToken=' + csrfMagicToken,
        success: function (data) {
            var res = data.split('####');
            var machineOs = res[0];
            var MachinesList = res[1];
            var cType = res[2];
            if (machineOs.toLowerCase().indexOf("windows") !== -1) {
                if (arrAllcheckVal === '' || arrAllcheckVal === undefined) {
                    WindowsJobExecute(dart, variable, shortDesc, Jobtype, ProfileName, MachinesList, cType);
                } else {
                    WindowsNotificationExecute(dart, variable, shortDesc, Jobtype, ProfileName, arrAllcheckVal, cType);
                }
            } else if (machineOs.toLowerCase().indexOf("android") !== -1) {
                AndroidJobExecute(dart, variable, shortDesc, Jobtype, ProfileName, MachinesList, cType);
            } else if (machineOs.toLowerCase().indexOf("mac") !== -1) {
                MacJobExecute(dart, variable, shortDesc, Jobtype, ProfileName, MachinesList, cType);
            } else if (machineOs.toLowerCase().indexOf("linux") !== -1) {

            }
        }
    });
    setTimeout(function () {
        $("#executeLoader").hide();
    }, 2000);
}

function CreateJobAndExecute(dart, variable, shortDesc, Jobtype, ProfileName) {
    // 1
    $("#executeLoader").show();
    $("#executeJob").hide();
    $.ajax({
        type: "POST",
        url: "../communication/communication_ajax.php",
        data: "function=getMachineOS"
            + '&csrfMagicToken=' + csrfMagicToken,
        success: function (data) {
            var res = data.split('####');
            //var machineOs = res[0];
            var MachinesList = res[1];
            var cType = res[2];
            if (arrAllcheckVal === '' || arrAllcheckVal === undefined) {
                WindowsJobExecute(dart, variable, shortDesc, Jobtype, ProfileName, MachinesList, cType);
                /*if ($("#windowsIcon").hasClass("is-selected")) {
                    WindowsJobExecute(dart, variable, shortDesc, Jobtype, ProfileName, MachinesList, cType);
                } else if ($("#androidIcon").hasClass("is-selected")) {
                    AndroidJobExecute(dart, variable, shortDesc, Jobtype, ProfileName, MachinesList, cType);
                } else if ($("#macIcon").hasClass("is-selected")) {
                    MacJobExecute(dart, variable, shortDesc, Jobtype, ProfileName, MachinesList, cType);
                } else if ($("#linuxIcon").hasClass("is-selected")) {

                } else if ($("#iosIcon").hasClass("is-selected")) {

                }*/
            } else {
                WindowsNotificationExecute(dart, variable, shortDesc, Jobtype, ProfileName, arrAllcheckVal, cType);
            }


        }
    });

    setTimeout(function () {
        $("#executeLoader").hide();
    }, 2000);
}

function AndroidJobExecute(dart, variable, shortDesc, Jobtype, ProfileName, MachinesList) {
    var dblist = '';
    var machineNotSupported = [
    ];

    $.ajax({
        type: "POST",
        url: "../communication/communication_ajax.php",
        data: "function=getAndroidVarValues&shortDesc=" + shortDesc
            + '&csrfMagicToken=' + csrfMagicToken,
        success: function (varValue) {
            varValue = varValue.trim();
            var machines = MachinesList.split("~~~~");
            for (var i = 0; i < machines.length; i++) {

                if (machines[i] === '' || machines[i] === null || machines[i] === undefined) {
                    continue;
                }

                var machineDetails = machines[i].split("~~");
                var machineName = machineDetails[0];
                var machineSite = machineDetails[1];
                var machineBuld = machineDetails[2];
                var OS = machineDetails[3];
                var servicetag;
                var dartVal = '';

                if (OS.toLowerCase().indexOf("android") !== -1) {

                } else {
                    machineNotSupported.push(machineName);
                    continue;
                }

                var uDart = pad(dart, 5);
                var dartVal = 'ScripNo=' + dart + '&S' + uDart + 'ProfileName=' + varValue + '&S' + uDart + 'ProfileName_GroupSetting=CID&' + variable + '=Execute&' + variable + '_GroupSetting=CID@@@@empty';

                if (dblist === '') {
                    dblist = machineSite + '~~' + machineName + '~~' + machineBuld + '~~' + dartVal + '~~' + ProfileName;
                } else {
                    dblist += '~~~~' + machineSite + '~~' + machineName + '~~' + machineBuld + '~~' + dartVal + '~~' + ProfileName;
                }

                servicetag = '';
            }
            if (machineNotSupported.length > 0) {
                alert("Profile will not run on : " + machineNotSupported.length + " Machines" + " " + machineNotSupported.toString());
                //bootbox.alert("<b>Profile will not run on : " + machineNotSupported.length + " Machines</b>" + "<br>" + machineNotSupported.toString(), function () {
                //});
            }

            /* Add Job to Schedule */
            if (dblist !== '') {
                $.ajax({
                    type: "POST",
                    url: "../communication/communication_ajax.php",
                    data: "function=doActionOnInteractiveAndroid&jobRow=" + escape(dblist)
                        + '&csrfMagicToken=' + csrfMagicToken,
                    success: function (msg) {
                        msg = msg.trim();
                        if (msg === 1 || msg === '1') {
                            emitDart(servicetag, 'Android');
                        }
                    },
                    error: function () {
                        $(".loadingStage").css({'display': 'none'});
                        $("#rightNavtiles").html("<span style='color:#009933; font-size:12px;'>Not Executed</span><br />");
                    }
                });
            }
        }
    });

}

function MacJobExecute(dart, variable, shortDesc, Jobtype, ProfileName, MachinesList) {

    /* Get varValue for Specific OS Version */
    var dblist = '';
    var dartVal = '';
    var machineNotSupported = [
    ];

    $.ajax({
        type: "POST",
        url: "../communication/communication_ajax.php",
        data: "function=getMacVarValues&shortDesc=" + shortDesc
            + '&csrfMagicToken=' + csrfMagicToken,
        success: function (varValue) {
            varValue = varValue.trim();
            var machines = MachinesList.split("~~~~");
            for (var i = 0; i < machines.length; i++) {

                if (machines[i] === '' || machines[i] === null || machines[i] === undefined) {
                    continue;
                }

                var machineDetails = machines[i].split("~~");
                var machineName = machineDetails[0];
                var machineSite = machineDetails[1];
                var machineBuld = machineDetails[2];
                var OS = machineDetails[3];
                var servicetag;
                var dartVal = '';

                if (OS.toLowerCase().indexOf("mac") !== -1) {

                } else {
                    machineNotSupported.push(machineName);
                    continue;
                }
                if (parseInt(dart) === 286 || parseInt(dart) === 1014) {
                    dartVal = "VarName=S00286ProfileName;VarType=2;VarVal=" + varValue + ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;#;NextConf;##;NextConf;#End";
                } else if (parseInt(dart) === 289) {
                    dartVal = "289-" + varValue_common;
                }

                if (dblist === '') {
                    dblist = machineSite + '~~' + machineName + '~~' + machineBuld + '~~' + dartVal + '~~' + ProfileName;
                } else {
                    dblist += '~~~~' + machineSite + '~~' + machineName + '~~' + machineBuld + '~~' + dartVal + '~~' + ProfileName;
                }

                servicetag = '';
            }
            if (machineNotSupported.length > 0) {
                alert("Profile will not run on : " + machineNotSupported.length + " Machines" + " " + machineNotSupported.toString());
                //bootbox.alert("<b>Profile will not run on : " + machineNotSupported.length + " Machines</b>" + "<br>" + machineNotSupported.toString(), function () {
                //});
            }

            /* Add Job to Schedule */
            if (dblist !== '') {
                $.ajax({
                    type: "POST",
                    url: "../communication/communication_ajax.php",
                    data: "function=doActionOnInteractiveMac&jobRow=" + escape(dblist)
                        + '&csrfMagicToken=' + csrfMagicToken,
                    success: function (msg) {
                        msg = msg.trim();
                        if (msg === 1 || msg === '1') {
                            emitDart(servicetag, 'Mac');
                        }
                    },
                    error: function () {
                        $(".loadingStage").css({'display': 'none'});
                        $("#rightNavtiles").html("<span style='color:#009933; font-size:12px;'>Not Executed</span><br />");
                    }
                });
            }
        }
    });
}

function WindowsJobExecute(dart, variable, shortDesc, Jobtype, profileName, MachinesList, cType) {

    /* Get varValue for Specific OS Version */
    var dblist = '';
    var servicetag;
    var machineNotSupported = [
    ];

    $.ajax({
        type: "POST",
        url: "../communication/communication_ajax.php",
        data: "function=getOsVarValues&shortDesc=" + shortDesc
            + '&csrfMagicToken=' + csrfMagicToken,
        //data: "function=getOsVarValuesNew&shortDesc=" + shortDesc,
        success: function (msg) {
            msg = $.trim(msg);
            var varValues = msg.split("##");
            var varValue_xp = varValues[0];
            var varValue_vista = varValues[1];
            var varValue_7 = varValues[2];
            var varValue_8 = varValues[3];
            var varValue_10 = varValues[4];
            var varValue_common = varValues[5];
            var CommonProfile = varValues[6];

            var machines = MachinesList.split("~~~~");

            for (var i = 0; i < machines.length; i++) {
                if (machines[i] === '' || machines[i] === null || machines[i] === undefined) {
                    continue;
                }

                var machineDetails = machines[i].split("~~");
                var machineName = machineDetails[0];
                var machineSite = machineDetails[1];
                var machineBuld = machineDetails[2];
                var OS = machineDetails[3];
                var dartVal = '';

                if (OS.toLowerCase().indexOf("win") !== -1) {

                } else {
                    machineNotSupported.push(machineName);
                    continue;
                }

                if (parseInt(dart) === 43) {
                    if (cType === 'IBM') {
                        dartVal = 'S00043SilentUninstall_43_RUN_SET_Signaled';
                    } else if (cType === 'COMMON') {
                        dartVal = "VarName=S00043SilentUninstall;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=43;VarScope=1;#;NextConf;##;NextConf;#End";
                    }
                } else if (parseInt(dart) === 256) {
                    if (cType === 'IBM') {
                        variable = variable.replace(/_/g, "$");
                        dartVal = variable + '_256_RUN_SET_Signaled';
                    } else if (cType === 'COMMON') {
                        dartVal = "VarName=" + variable + ";VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=256;VarScope=1;#;NextConf;##;NextConf;#End";
                    }
                } else {
                    if (CommonProfile === '0' || CommonProfile === 0) {
                        if ((OS.indexOf("Windows 8.1") !== -1) || (OS.indexOf("windows 8.1") !== -1) || (OS.indexOf("windows8.1") !== -1) || (OS.indexOf("Windows8.1") !== -1)) {
                            varValueSpecific = varValue_8;
                        } else if ((OS.indexOf("Windows 8") !== -1) || (OS.indexOf("windows 8") !== -1) || (OS.indexOf("Windows8") !== -1) || (OS.indexOf("windows8") !== -1)) {
                            varValueSpecific = varValue_8;
                        } else if ((OS.indexOf("Windows 7") !== -1) || (OS.indexOf("windows 7") !== -1) || (OS.indexOf("Windows7") !== -1) || (OS.indexOf("windows7") !== -1)) {
                            varValueSpecific = varValue_7;
                        } else if ((OS.indexOf("Windows 10") !== -1) || (OS.indexOf("windows 10") !== -1) || (OS.indexOf("Windows10") !== -1) || (OS.indexOf("windows10") !== -1)) {
                            varValueSpecific = varValue_10;
                        } else if ((OS.indexOf("Windows Vista") !== -1) || (OS.indexOf("WindowsVista") !== -1)) {
                            varValueSpecific = varValue_vista;
                        } else {
                            varValueSpecific = varValue_xp;
                        }

                        if (varValueSpecific !== '') {
                            if (cType === 'IBM') {
                                dartVal = dart + '-' + varValueSpecific; // Pending HareesH
                            } else if (cType === 'COMMON') {
//                                if (new RegExp("([a-zA-Z0-9]+://)?([a-zA-Z0-9_]+:[a-zA-Z0-9_]+@)?([a-zA-Z0-9.-]+\\.[A-Za-z]{2,4})(:[0-9]+)?(/.*)?").test(varValueSpecific)) {
//                                    dartVal = 'VarName=Scrip89Package;VarType=2;VarVal=' + '"c:\\Program Files\\Internet Explorer\\iexplore.exe" "' + varValueSpecific + '";Action=SET;DartNum=151;VarScope=1;#;NextConf;#VarName=S00089RunNowButton;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=151;VarScope=1;';
//                                } else {
                                    //dartVal = "VarName=S00286ProfileName;VarType=2;VarVal=" + varValueSpecific + ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;#;NextConf;##;NextConf;#End";
                                    if (parseInt(dart) === 286) {
                                        dartVal = "VarName=S00286ProfileName;VarType=2;VarVal=" + varValueSpecific + ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;#;NextConf;##;NextConf;#End";
                                        //dartVal = "VarName=S00286RunTimeConfig;VarType=2;VarVal=" + varValueSpecific + ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;#;NextConf;##;NextConf;#End";
                                    } else if (parseInt(dart) === 289) {
                                        //dartVal = "VarName=S00289ProfileName1;VarType=2;VarVal=" + varValueSpecific + ";Action=SET;DartNum=289;VarScope=1;#;NextConf;#VarName=S00289SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=289;VarScope=1;#;NextConf;##;NextConf;#End";
                                        dartVal = "289-" + varValueSpecific;
                                    }
//                                }
                            }
                        } else {
                            machineNotSupported.push(machineName);
                            continue;
                        }
                    } else {
                        if (cType === 'IBM') {
                            dartVal = dart + '-' + varValue_common;
                        } else if (cType === 'COMMON') {
//                            if (new RegExp("([a-zA-Z0-9]+://)?([a-zA-Z0-9_]+:[a-zA-Z0-9_]+@)?([a-zA-Z0-9.-]+\\.[A-Za-z]{2,4})(:[0-9]+)?(/.*)?").test(varValue_common)) {
//                                dartVal = 'VarName=Scrip89Package;VarType=2;VarVal=' + '"c:\\Program Files\\Internet Explorer\\iexplore.exe" "' + varValue_common + '";Action=SET;DartNum=151;VarScope=1;#;NextConf;#VarName=S00089RunNowButton;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=151;VarScope=1;';
//                            } else {
                                if (parseInt(dart) === 286) {
                                    dartVal = "VarName=S00286ProfileName;VarType=2;VarVal=" + varValue_common + ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;#;NextConf;##;NextConf;#End";
                                    //dartVal = "VarName=S00286RunTimeConfig;VarType=2;VarVal=" + varValue_common + ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;#;NextConf;##;NextConf;#End";
                                } else if (parseInt(dart) === 289) {
                                    //dartVal = "VarName=S00289ProfileName1;VarType=2;VarVal=" + varValue_common + ";Action=SET;DartNum=289;VarScope=1;#;NextConf;#VarName=S00289SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=289;VarScope=1;#;NextConf;##;NextConf;#End";
                                    dartVal = "289-" + varValue_common;
                                }
//                            }
                        }
                    }
                }

                if (dblist === '') {
                    dblist = machineSite + '~~' + machineName + '~~' + machineBuld + '~~' + escape(dartVal) + '~~' + escape(profileName);
                } else {
                    dblist += '~~~~' + machineSite + '~~' + machineName + '~~' + machineBuld + '~~' + escape(dartVal) + '~~' + escape(profileName);
                }
                servicetag = machineName;
            }
            if (machineNotSupported.length > 0) {
                alert("Profile will not run on : " + machineNotSupported.length + " Machines" + " " + machineNotSupported.toString());
                //bootbox.alert("<b>Profile will not run on : " + machineNotSupported.length + " Machines</b>" + "<br>" + machineNotSupported.toString(), function () {
                //});
            }

            /* Add Job to Schedule */
            if (dblist !== '') {
                $.ajax({
                    type: "POST",
                    url: "../communication/communication_ajax.php",
                    data: "function=doActionOnInteractive&jobRow=" + dblist
                        + '&csrfMagicToken=' + csrfMagicToken,
                    success: function (msg) {
                        msg = msg.trim();
                        /*if (msg === 1 || msg === '1') {
                         emitDart(servicetag, 'Windows');
                         }*/
                        if (msg !== "") {
                            emitDart(servicetag, 'Windows');
                        }
                    },
                    error: function () {
                        $(".loadingStage").css({'display': 'none'});
                        $("#rightNavtiles").html("<span style='color:#009933; font-size:12px;'>Not Executed</span><br />");
                    }
                });
            }
        }
    });
}

function WindowsNotificationExecute(dart, variable, shortDesc, Jobtype, profileName, MachinesList, cType) {


    /* Get varValue for Specific OS Version */
    var dblist = '';
    var servicetag;
    var machineNotSupported = [
    ];

    $.ajax({
        type: "POST",
        url: "../communication/communication_ajax.php",
        data: "function=getOsVarValues&shortDesc=" + shortDesc
            + '&csrfMagicToken=' + csrfMagicToken,
        success: function (msg) {
            msg = $.trim(msg);
            var varValues = msg.split("##");
            var varValue_xp = varValues[0];
            var varValue_vista = varValues[1];
            var varValue_7 = varValues[2];
            var varValue_8 = varValues[3];
            var varValue_10 = varValues[4];
            var varValue_common = varValues[5];
            var CommonProfile = varValues[6];

            var machines = MachinesList.split("####");

            for (var i = 0; i < machines.length; i++) {
                if (machines[i] === '' || machines[i] === null || machines[i] === undefined) {
                    continue;
                }

                var machineDetails = machines[i].split("~~");
                var machinenid = machineDetails[0];
                var machineSite = machineDetails[2];
                var machineName = machineDetails[3];
                var machineBuld = machineDetails[4];
                var machineConsoleID = machineDetails[5];
                var machineEventIdx = machineDetails[6];
                var machineEventTm = machineDetails[7];
                var machineNotifyID = machineDetails[8];

                var OS = machineDetails[9];

                if (OS.toLowerCase().indexOf("win") !== -1) {

                } else {
                    machineNotSupported.push(machineName);
                    continue;
                }

                var dartVal = '';

                if (parseInt(dart) === 43) {
                    if (cType === 'IBM') {
                        dartVal = 'S00043SilentUninstall_43_RUN_SET_Signaled';
                    } else if (cType === 'COMMON') {
                        dartVal = "VarName=S00043SilentUninstall;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=43;VarScope=1;#;NextConf;##;NextConf;#End";
                    }
                } else if (parseInt(dart) === 256) {
                    if (cType === 'IBM') {
                        variable = variable.replace(/_/g, "$");
                        dartVal = variable + '_256_RUN_SET_Signaled';
                    } else if (cType === 'COMMON') {
                        dartVal = "VarName=" + variable + ";VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=256;VarScope=1;#;NextConf;##;NextConf;#End";
                    }
                } else {
                    if (CommonProfile === '0' || CommonProfile === 0) {
                        if ((OS.indexOf("Windows 8.1") !== -1) || (OS.indexOf("windows 8.1") !== -1) || (OS.indexOf("windows8.1") !== -1) || (OS.indexOf("Windows8.1") !== -1)) {
                            varValueSpecific = varValue_8;
                        } else if ((OS.indexOf("Windows 8") !== -1) || (OS.indexOf("windows 8") !== -1) || (OS.indexOf("Windows8") !== -1) || (OS.indexOf("windows8") !== -1)) {
                            varValueSpecific = varValue_8;
                        } else if ((OS.indexOf("Windows 7") !== -1) || (OS.indexOf("windows 7") !== -1) || (OS.indexOf("Windows7") !== -1) || (OS.indexOf("windows7") !== -1)) {
                            varValueSpecific = varValue_7;
                        }  else if ((OS.indexOf("Windows 10") !== -1) || (OS.indexOf("windows 10") !== -1) || (OS.indexOf("Windows10") !== -1) || (OS.indexOf("windows10") !== -1)) {
                            varValueSpecific = varValue_10;
                        } else if ((OS.indexOf("Windows Vista") !== -1) || (OS.indexOf("WindowsVista") !== -1)) {
                            varValueSpecific = varValue_vista;
                        } else {
                            varValueSpecific = varValue_xp;
                        }

                        if (varValueSpecific !== '') {
                            if (cType === 'IBM') {
                                dartVal = dart + '-' + varValueSpecific; // Pending HareesH
                            } else if (cType === 'COMMON') {
                                if (new RegExp("([a-zA-Z0-9]+://)?([a-zA-Z0-9_]+:[a-zA-Z0-9_]+@)?([a-zA-Z0-9.-]+\\.[A-Za-z]{2,4})(:[0-9]+)?(/.*)?").test(varValueSpecific)) {
                                    dartVal = 'VarName=Scrip89Package;VarType=2;VarVal=' + '"c:\\Program Files\\Internet Explorer\\iexplore.exe" "' + varValueSpecific + '";Action=SET;DartNum=151;VarScope=1;#;NextConf;#VarName=S00089RunNowButton;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=151;VarScope=1;#;NextConf;##;NextConf;#End';
                                } else {
                                    if (parseInt(dart) === 286) {
                                        dartVal = "VarName=S00286ProfileName;VarType=2;VarVal=" + varValueSpecific + ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;#;NextConf;##;NextConf;#End";
                                    } else {
                                        dartVal = dart + '-' + varValueSpecific;
                                    }
                                }
                            }
                        } else {
                            machineNotSupported.push(machineName);
                            continue;
                        }
                    } else {
                        if (cType === 'IBM') {
                            dartVal = dart + '-' + varValue_common;
                        } else if (cType === 'COMMON') {
                            if (new RegExp("([a-zA-Z0-9]+://)?([a-zA-Z0-9_]+:[a-zA-Z0-9_]+@)?([a-zA-Z0-9.-]+\\.[A-Za-z]{2,4})(:[0-9]+)?(/.*)?").test(varValue_common)) {
                                dartVal = 'VarName=Scrip89Package;VarType=2;VarVal=' + '"c:\\Program Files\\Internet Explorer\\iexplore.exe" "' + varValue_common + '";Action=SET;DartNum=151;VarScope=1;#;NextConf;#VarName=S00089RunNowButton;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=151;VarScope=1;#;NextConf;##;NextConf;#End';
                            } else {
                                if (parseInt(dart) === 286) {
                                    dartVal = "VarName=S00286ProfileName;VarType=2;VarVal=" + varValue_common + ";Action=SET;DartNum=286;VarScope=1;#;NextConf;#VarName=S00286SeqRunNow;VarType=5;VarVal=EXECUTING;Action=RUN;DartNum=286;VarScope=1;#;NextConf;##;NextConf;#End";
                                } else {
                                    dartVal = dart + '-' + varValue_common;
                                }
                            }
                        }
                    }
                }

                if (dblist === '') {
                    dblist = machineSite + '~~' + machineName + '~~' + machineBuld + '~~' + escape(dartVal) + '~~' + escape(profileName) + '~~'
                            + machinenid + '~~' + machineConsoleID + '~~' + machineEventIdx + '~~' + machineEventTm + '~~'
                            + machineNotifyID + '~~' + dart;

                } else {
                    dblist += '~~~~' + machineSite + '~~' + machineName + '~~' + machineBuld + '~~' + escape(dartVal) + '~~' + escape(profileName) + '~~'
                            + machinenid + '~~' + machineConsoleID + '~~' + machineEventIdx + '~~' + machineEventTm + '~~'
                            + machineNotifyID + '~~' + dart;
                }

                //servicetag = machineName;
            }
            if (machineNotSupported.length > 0) {
                alert("Profile will not run on : " + machineNotSupported.length + " Machines" + " " + machineNotSupported.toString());
                //bootbox.alert("<b>Profile will not run on : " + machineNotSupported.length + " Machines</b>" + "<br>" + machineNotSupported.toString(), function () {
                //});
            }

            /* Add Job to Schedule */
            if (dblist !== '') {
                $.ajax({
                    type: "POST",
                    url: "../communication/communication_ajax.php",
                    data: "function=doActionOnNotification&jobRow=" + dblist
                        + '&csrfMagicToken=' + csrfMagicToken,
                    success: function (msg) {
                        msg = msg.trim();
                        if (msg === 1 || msg === '1') {
                            $("#cretmsg").html("<span style='color:green'>Resolution <b>" + profileName + "</b> pushed successfully on selected machine's.</span>");
                            emitDart('', 'Windows');
                        } else {
                            $("#cretmsg").html("<span style='color:red'>Resolution <b>" + profileName + "</b> fail to push on selected machine's. Please try again.</span>");
                        }

                    },
                    error: function () {
                        $(".loadingStage").css({'display': 'none'});
                        $("#rightNavtiles").html("<span style='color:#009933; font-size:12px;'>Not Executed</span><br />");
                    }
                });
            }
        }
    });
}

function renderMap(msg) {
    // If the browser supports the Geolocation API
    if (typeof navigator.geolocation === "undefined") {
        $("#error").text("Your browser doesn't support the Geolocation API");
        return;
    }
    loaded = 0;
    navigator.geolocation.clearWatch(watchID);

    watchID = navigator.geolocation.watchPosition(function (position) {

        if (loaded === 1) {
            return;
        }
        var path = [
        ];

        // Save the current position
        //path.push(new google.maps.LatLng(position.coords.latitude, position.coords.longitude));
        var res = $.parseJSON(msg);
        var count = Object.keys(res).length;
        // Create the map
        var myOptions = {
            zoom: 2,
            center: path[0],
            mapTypeId: google.maps.MapTypeId.TERRAIN
        };

        $(".search-bg").show();
        $("#searchbox").show();

        var map = new google.maps.Map(document.getElementById("map"), myOptions);

        //Uncomment this block if you want to set a path

        // Create the polyline's points
        for (var i = 0; i < count; i++) {

            var latlng = res[i];
            var latlng = latlng.replace("|", ",");
            var temp = latlng.split(",");
            // Create a random point using the user current position and a random generated number.
            // The number will be once positive and once negative using based on the parity of i
            // and to reduce the range the number is divided by 10

            path.push(new google.maps.LatLng(temp[0], temp[1]));
        }


        // Create the array that will be used to fit the view to the points range and
        // place the markers to the polyline's points
        var latLngBounds = new google.maps.LatLngBounds();
        for (var i = 0; i < path.length; i++) {
            latLngBounds.extend(path[i]);
            // Place the marker
            new google.maps.Marker({
                map: map,
                position: path[i],
                title: "Location No: " + (i + 1) + "\n "//Date: 22/10/2010
            });
        }
        // Creates the polyline object
        var polyline = new google.maps.Polyline({
            map: map,
            path: path,
            strokeColor: '#0000FF',
            strokeOpacity: 0.7,
            strokeWeight: 1
        });
        // Fit the bounds of the generated points
        map.fitBounds(latLngBounds);
        loaded = 1;
    },
            function (positionError) {
                //$("#error").append("Error: " + positionError.message + "<br />");
            },
            {
                //enableHighAccuracy: false,
                //timeout: 3000 * 1000 // 10 seconds
            });

}

function NoceventDetailsForDartStatA(stat, tid, eventList) {

    $("#rightNavtiles").css({'display': 'none'});
    if (tid !== '')
    {
        jQuery_1_7.nyroModalManual({
            debug: false,
            width: 1000, // default Width If null, will be calculate automatically
            height: 1600,
            bgColor: '#333',
            url: '../notification/event_detailsForDartStatAudit.php?eid=' + tid + '&level=&site=&name=&id=&stat=' + stat + '&eventList=' + eventList + "&csrfMagicToken=" + csrfMagicToken,
            ajax: {
                data: '',
                type: 'post'
            },
            closeButton: false,
            css: { // Default CSS option for the nyroModal Div. Some will be overwritten or updated when using IE6

                wrapper: {
                    position: 'absolute',
                    top: '50%',
                    left: '50%'

                }
            }
        });
    } else {
        $("#rightNavtiles").css({'display': 'block'});
        $("#rightNavtiles").html('<div class="panel panel-danger"><div class="panel-heading"><h3 class="panel-title"><i class="fa fa-warning"></i>&nbsp;&nbsp;Alert<i class="fa fa-remove nyroModalClose pull-right closeThis"></i></h3></div><div class="panel-body"><div class="verticalSpace10"></div><span id="errTxt">No eventIdx for this machine.</div></div>');
    }
}

function getOsDetails() {
    $.ajax({
        type: "GET",
        url: "../communication/communication_ajax.php",
        data: "function=getMachineOS&csrfMagicToken=" + csrfMagicToken,
        success: function (data) {
            var res = data.split('####');
            var OS = res[0];
            var Ititle = res[3];
            $('#h-title').html('Interactive - ' + Ititle);
            if (OS.toLowerCase().indexOf("win") !== -1) {

                OSName = "Windows";
                OSDB = "Windows";
                OSSUB = 'NA';
                var stype = '<?php echo $_SESSION["searchType"]; ?>';

                if (stype === 'Service Tag' || stype === 'Host Name') {
                    if ((OS.indexOf("Windows 8.1") !== -1) || (OS.indexOf("windows 8.1") !== -1) || (OS.indexOf("windows8.1") !== -1) || (OS.indexOf("Windows8.1") !== -1)) {
                        OSSUB = "8";
                    } else if ((OS.indexOf("Windows 8") !== -1) || (OS.indexOf("windows 8") !== -1) || (OS.indexOf("Windows8") !== -1) || (OS.indexOf("windows8") !== -1)) {
                        OSSUB = "8";
                    } else if ((OS.indexOf("Windows 7") !== -1) || (OS.indexOf("windows 7") !== -1) || (OS.indexOf("Windows7") !== -1) || (OS.indexOf("windows7") !== -1)) {
                        OSSUB = "7";
                    } else if ((OS.indexOf("Windows Vista") !== -1) || (OS.indexOf("WindowsVista") !== -1)) {
                        OSSUB = "vista";
                    } else {
                        OSSUB = "xp";
                    }
                } else {
                    OSSUB = 'NA';
                }
            } else if (OS.toLowerCase().indexOf("android") !== -1) {
                OSName = "Android";
                OSDB = "Android";
            } else if (OS.toLowerCase().indexOf("mac") !== -1) {
                OSName = "Mac";
                OSDB = "Mac";
            } else if (OS.toLowerCase().indexOf("linux") !== -1) {
                OSName = "Linux";
                OSDB = "Linux";
            } else {
                OSName = "unknow";
                OSDB = "7";
            }
            //tileHome('1');
        }
    });

}

function getOSLevelTilesMachineLvl() {
    $.ajax({
        type: "GET",
        url: "../communication/communication_ajax.php",
        data: "function=getMachineOS&csrfMagicToken=" + csrfMagicToken,
        success: function (data) {

            var res = data.split('####');
            var OS = res[0];
            var Ititle = res[3];
            $('#h-title').html('Interactive - ' + Ititle);
            if (OS.toLowerCase().indexOf("win") !== -1) {
                $("#androidIcon,#macIcon,#iosIcon,#linuxIcon").hide();
                $("#windowsIcon").addClass("is-selected");
                $("#androidIcon,#macIcon,#iosIcon,#linuxIcon").removeClass("is-selected");
                $("#windowsIcon").css("background-color", "transparent");
                getOSLevelTiles('windows');
            } else if (OS.toLowerCase().indexOf("android") !== -1) {
                $("#windowsIcon,#macIcon,#iosIcon,#linuxIcon").hide();
                $("#androidIcon").addClass("is-selected");
                $("#windowsIcon,#macIcon,#iosIcon,#linuxIcon").removeClass("is-selected");
                $("#androidIcon").css("background-color", "transparent");
                getOSLevelTiles('android');
            } else if (OS.toLowerCase().indexOf("mac") !== -1) {
                $("#windowsIcon,#androidIcon,#iosIcon,#linuxIcon").hide();
                $("#macIcon").addClass("is-selected");
                $("#androidIcon,#windowsIcon,#iosIcon,#linuxIcon").removeClass("is-selected");
                $("#macIcon").css("background-color", "transparent");
                getOSLevelTiles('mac');
            } else if (OS.toLowerCase().indexOf("linux") !== -1) {
                $("#windowsIcon,#androidIcon,#macIcon,#iosIcon,").hide();
                $("#linuxIcon").addClass("is-selected");
                $("#androidIcon,#macIcon,#iosIcon,#windowsIcon").removeClass("is-selected");
                $("#linuxIcon").css("background-color", "transparent");
                getOSLevelTiles('linux');
            } else if (OS.toLowerCase().indexOf("ios") !== -1) {
                $("#windowsIcon,#androidIcon,#macIcon,#linuxIcon").hide();
                $("#iosIcon").addClass("is-selected");
                $("#androidIcon,#macIcon,#windowsIcon,#linuxIcon").removeClass("is-selected");
                $("#iosIcon").css("background-color", "transparent");
                getOSLevelTiles('ios');
            } else {
                $("#windowsIcon,#androidIcon,#macIcon,#iosIcon,#linuxIcon").show();
                $("#windowsIcon").addClass("is-selected");
                $("#androidIcon,#macIcon,#iosIcon,#linuxIcon").removeClass("is-selected");
                $("#windowsIcon").css("background-color", "transparent");
                // getOSLevelTiles('windows');
            }
        }
    });
}

function getOSLevelTiles(OSType) {
    $.ajax({
        type: "GET",
        url: "../communication/communication_ajax.php",
        data: "function=getMachineOS&csrfMagicToken=" + csrfMagicToken,
        success: function (data) {

            var res = data.split('####');
            var OS = res[0];
            var Ititle = res[3];
            $('#h-title').html('Interactive - ' + Ititle);
            if (OSType === 'windows') {

                OSName = "Windows";
                OSDB = "Windows";
                OSSUB = 'NA';
                var stype = '<?php echo $_SESSION["searchType"]; ?>';

                if (stype === 'Service Tag' || stype === 'Host Name') {
                    if ((OS.indexOf("Windows 8.1") !== -1) || (OS.indexOf("windows 8.1") !== -1) || (OS.indexOf("windows8.1") !== -1) || (OS.indexOf("Windows8.1") !== -1)) {
                        OSSUB = "8";
                    } else if ((OS.indexOf("Windows 8") !== -1) || (OS.indexOf("windows 8") !== -1) || (OS.indexOf("Windows8") !== -1) || (OS.indexOf("windows8") !== -1)) {
                        OSSUB = "8";
                    } else if ((OS.indexOf("Windows 7") !== -1) || (OS.indexOf("windows 7") !== -1) || (OS.indexOf("Windows7") !== -1) || (OS.indexOf("windows7") !== -1)) {
                        OSSUB = "7";
                    } else if ((OS.indexOf("Windows Vista") !== -1) || (OS.indexOf("WindowsVista") !== -1)) {
                        OSSUB = "vista";
                    } else {
                        OSSUB = "xp";
                    }
                } else {
                    OSSUB = 'NA';
                }
            } else if (OSType === 'android') {
                OSName = "Android";
                OSDB = "Android";
            } else if (OSType === 'mac') {
                OSName = "Mac";
                OSDB = "Mac";
            } else if (OSType === 'linux') {
                OSName = "Linux";
                OSDB = "Linux";
            } else {
                OSName = "unknow";
                OSDB = "7";
            }
            tileHome('1');
        }
    });
}

function tileHome(pageId) {
    if (pageId === '2' || pageId === 2 || pageId === '1' || pageId === 1) {
        $(".backBtn").hide();
    } else {
        $(".backBtn").show();
    }

    $("#ExceCancelButton").html('');
    $.ajax({
        type: "POST",
        url: "../communication/communication_ajax.php",
        // data: "function=profileData&os=" + OSDB + '&pageId=' + pageId + '&ossub=' + OSSUB + "&csrfMagicToken=" + csrfMagicToken,
        data: {'function':'profile_Data', 'os':OSDB, 'pageId':pageId,'ossub':OSSUB,'level':level, 'csrfMagicToken':csrfMagicToken},
        success: function (msgVal) {
            msg = $.trim(msgVal);
            var liMsg = msg.split('##');
            $("#toolboxList").html(liMsg[0]);
            $("#backParentId").val(liMsg[1]);
            glmenuitem = liMsg[2];
            var ptitle = liMsg[3];
            if (ptitle.length > 22)
            {
                var parentTitle = ptitle.substring(0, 22);
                ptitle = parentTitle + '..';
            }
            $('#parentTitle').text(ptitle);
            $('#parentTitle').attr("title", liMsg[3]);
            $("#mainParentTitle").html(liMsg[3]);
            $("#parentDesc").html(liMsg[4]);
            $('#notif_interactive').show();
            $('#notif_details').hide();
        }
    });
}

function advclickl1level(thisVal, parentId, profile, dart, variable, varValue, description, page, menuitem) {
    $("#listDiv").hide();
    if (page === '1' || page === 1) {
        $(".backBtn").hide();
    } else {
        $(".backBtn").show();
    }
    var backPageId = $("#backParentId").val();
    $("#ExceCancelButton").html('');
    $.ajax({
        type: "GET",
        url: "../communication/communication_ajax.php",
        data: "function=advprofileData&os=" + OSDB + "&pageId=" + parentId + "&backId=" + backPageId + "&menuitem=" + menuitem + '&ossub=' + OSSUB + "&csrfMagicToken=" + csrfMagicToken,
        success: function (msgVal) {
            msg = $.trim(msgVal);
            var liMsg = msg.split('##');
            $("#advtoolboxList").html(liMsg[0]);
            $("#backParentId").val(liMsg[1]);
            glmenuitem = liMsg[2];
            var ptitle = liMsg[3];
            if (ptitle.length > 22)
            {
                var parentTitle = ptitle.substring(0, 22);
                ptitle = parentTitle + '..';
            }
            $('#parentTitle').text(ptitle);
            $('#parentTitle').attr("title", liMsg[3]);
            $("#mainParentTitle").html('ADVANCE TROUBLESHOOTERS');
            $("#parentDesc").html('Here you will find a variety of tools that are meant for trained technicians. Although you can see the descriptions, only one of our agents can run these tools');
        }
    });
}

function clickl1level(thisVal, parentId, profile, dart, variable, varValue, description, page, menuitem) {
    $("#toolboxList").css("padding-top","10px;");
    $("#listDiv").show();
    if (page === '1' || page === 1) {
        $(".backBtn").hide();
    } else {
        $(".backBtn").show();
    }
//    $('.panel-body li').css("");
    $('.panel-body a').css({'background-color':'white' , 'color':'#595959'});
    $(thisVal).css({'background-color':'#48b2e4', 'color':'white'});
    var backPageId = $("#backParentId").val();
    $("#ExceCancelButton").html('');
    $.ajax({
        type: "POST",
        url: "../communication/communication_ajax.php",
        // data: "function=profileDataList&os=" + OSDB + "&pageId=" + parentId + "&backId=" + backPageId + "&menuitem=" + menuitem + '&ossub=' + OSSUB + "&csrfMagicToken=" + csrfMagicToken,
        data: {'function':'profile_DataList', 'os':OSDB, 'pageId':pageId,'ossub':OSSUB,'csrfMagicToken':csrfMagicToken, 'backId':backPageId, 'menuitem':menuitem},

        success: function (msgVal) {
            msg = $.trim(msgVal);
            var liMsg = msg.split('##');
            $("#clickList").html(liMsg[0]);
            $("#backParentId").val(liMsg[1]);
            glmenuitem = liMsg[2];
            var ptitle = liMsg[3];
            if (ptitle.length > 22)
            {
                var parentTitle = ptitle.substring(0, 22);
                ptitle = parentTitle + '..';
            }
            $('#parentTitle').text(ptitle);
            $('#parentTitle').attr("title", liMsg[3]);
            $("#mainParentTitle").html(liMsg[3]);
            $("#parentDesc").html(liMsg[4]);
        }
    });
}

//function clickl2level(thisVal, parentId, profile, dart, variable, varValue, description, page, menuitem, profileId) {
//    $("#listDiv").show();
//    if (page === '1' || page === 1) {
//        $(".backBtn").hide();
//    } else {
//        $(".backBtn").show();
//    }
//    var backPageId = $("#backParentId").val();
//    $("#ExceCancelButton").html('');
//    $.ajax({
//        type: "GET",
//        url: "../communication/communication_ajax.php",
//        data: "function=profileData&os=" + OSDB + "&pageId=" + parentId + "&backId=" + backPageId + "&menuitem=" + menuitem + '&ossub=' + OSSUB,
//        success: function (msgVal) {
//            msg = $.trim(msgVal);
//            var liMsg = msg.split('##');
//            $("#"+profileId).html(liMsg[0]);
//            $("#backParentId").val(liMsg[1]);
//            glmenuitem = liMsg[2];
//            var ptitle = liMsg[3];
//            if (ptitle.length > 22)
//            {
//                var parentTitle = ptitle.substring(0, 22);
//                ptitle = parentTitle + '..';
//            }
//            $('#parentTitle').text(ptitle);
//            $('#parentTitle').attr("title", liMsg[3]);
//            $("#mainParentTitle").html(liMsg[3]);
//            $("#parentDesc").html(liMsg[4]);
//        }
//    });
//}

function clickl3level(thisVal, parentId, profile, dart, variable, varValue, description, page, menuitem) {
    CreateJobAndExecute(dart,variable,varValue,'Interactive',profile);
    /*if (page === '1' || page === 1) {
        $(".backBtn").hide();
    } else {
        $(".backBtn").show();
    }
    $("#mainParentTitle").html(profile);
    $("#parentDesc").html(decodeURIComponent(description.replace(/\+/g, '%20')));
    var functionToBeCalled = "CreateJobAndExecute('" + dart + "','" + variable + "','" + varValue + "','Interactive','" + profile + "')";
    $('#ExceCancelButton').html('<a href="#" class="button button--inline bkg-primary" onclick="' + functionToBeCalled + '" id="executeJob" style="color:white;">Run this Repair</a>');
    */
}

function callDart(varValue, dart, variable) {

    var machname = "'" + glMachineName + "'";
    $('#ExceCancelButton').html('<button class="btn" onclick="cancelDart(' + machname + ')" id="systemAnalysis">Cancel</button>');
    var dartConf = dart + '-' + varValue;
    $.ajax({
        type: "GET",
        url: "../includes/notification_ajax.php",
        data: "function=doActionOnDetails&selectedRow=" + OSDB + "&dartnum=" + dart + "&dartCofg=" + dartConf + "&machine=" + glMachineName + "&csrfMagicToken=" + csrfMagicToken,
        success: function (msgVal) {
            msg = $.trim(msgVal);
            if (msg === 1) {
                emitDart('1', glMachineName, dart);
            }
        }
    });
}

function callBackButton() {
    var parntId = $("#backParentId").val();
    //$("#ExceCancelButton").html('');
    tileHome(parntId);
}
