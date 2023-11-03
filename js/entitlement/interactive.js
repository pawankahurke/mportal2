function callHome(pageId) {
    $("#ExceCancelButton").html('');
    $.ajax({
        type: "GET",
        url: base_url+"customer/profile/"+OSDB+"/"+pageId+"/NULL/NULL"+"&csrfMagicToken=" + csrfMagicToken,
        success: function (msgVal) {
            msg = $.trim(msgVal);
            var liMsg = msg.split('##');
            $("#tilesDiv").html(liMsg[0]);
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

function clickl1level(parentId, profile, dart, variable, varValue, description, page, menuitem) {
    var backPageId = $("#backParentId").val();
    $("#ExceCancelButton").html('');
    //var mnItem = decodeURIComponent(menuitem);
    //menuitem = menuitem.replace(/\//g , "##");
   
    $.ajax({
        type: "GET",
        url: base_url+"customer/profile/"+OSDB+"/"+parentId+"/"+backPageId+"/"+menuitem+"&csrfMagicToken=" + csrfMagicToken,
        success: function (msgVal) {
            msg = $.trim(msgVal);
            var liMsg = msg.split('##');
            $("#tilesDiv").html(liMsg[0]);
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

function clickl3level(parentId, profile, dart, variable, varValue, description, page, menuitem) {

    $("#mainParentTitle").html(profile);
    $("#parentDesc").html(description);

    var functionToBeCalled = "StartSequence('" + dart + "','" + variable + "','" + varValue + "','" + menuitem + "')";
    $('#ExceCancelButton').html('<button class="btn" onclick="' + functionToBeCalled + '" id="systemAnalysis">Execute</button>');


}


function callDart(varValue, dart, variable) {

    var machname = "'" + glMachineName + "'";
    $('#ExceCancelButton').html('<button class="btn" onclick="cancelDart(' + machname + ')" id="systemAnalysis">Cancel</button>');
    var dartConf = dart + '-' + varValue;
    $.ajax({
        type: "GET",
        url: "../includes/notification_ajax.php",
        data: "function=doActionOnDetails&selectedRow=" + OSDB + "&dartnum=" + dart + "&dartCofg=" + dartConf + "&machine=" + glMachineName +"&csrfMagicToken=" + csrfMagicToken,
        success: function (msgVal) {
            msg = $.trim(msgVal);
            if (msg == 1) {
                emitDart('1', glMachineName, dart);
            }
        }
    });
}

function callBackButton() {
    var parntId = $("#backParentId").val();
    $("#ExceCancelButton").html('');
    callHome(parntId);
}


function StartSequence(dart, variable, varValue,menuitem)
{
    $("#progressDiv").html('');
    $('#systemAnalysis').hide();

   var selectedRow = $("#censesVal").val();
    
    var selValCount = selectedRow.split("~~~~");
    var selLen = selValCount.length;
    var machineList = '';
    var machineName = '';
    var mchStatus = '';
    var siteName= '';
    var schVal = 0;
    if (selLen == 2) {
	machineList = selValCount[0].split("~~");
        siteName = machineList[1];
	machineName = machineList[2];
	mchStatus = machineList[3];
    }

    if (mchStatus != 'notavail') {

	$.ajax({
	type: "GET",
        url: base_url+"customer/getMachineUnistallStatus/"+machineName,
	success: function (unMsg) {

		if (unMsg == 0 || unMsg == "0") {

		    $.ajax({
			type: "GET",
                        url: base_url+"customer/getScheduleDetails/"+machineName+"/"+menuitem +"&csrfMagicToken=" + csrfMagicToken,
			success: function (msg) {
			    //msg = msg.trim();
			    msg = $.trim(msg)
			    var msgres = msg.split("##");
			    var ststtr = '';
			    if (msgres[1] == 'online') {
				ststtr = '<b style="color:green">Online</b>';
			    } else {
				ststtr = '<b style="color:red">Offline</b>';
			    }
			    $("#toolBxMStatus").html(ststtr);


			    if (msgres[0] != 0 || msgres[0] != '0' || msgres[1] == 'offline' || mchStatus == 'offline') {

				/* this is the new confirmation box ui and cheking check box */
				var user = getCookie("prventNext");

				if (msgres[2] == 0 || msgres[2] == '0') {
				    if (user != "" && user != undefined)
				    {
					
					schVal = 0;
					//this is the call for direct going user checks
					excecuteDartSequence(varValue, machineName, dart, variable, siteName, selLen, schVal, msgres[1],menuitem);
				    } else {

					schVal = 1;

					// example of calling the confirm function
					// you must use a callback function to perform the "yes" action
					confirm("A Resolution is already in progress. Do you want to queue this item?", function () {

					    check = $("#prventNext").is(":checked");
					    if (check) {
						var cname = 'prventNext';
						var cvalue = $("#prventNext").val();
						var exdays = 10;
						setCookie(cname, cvalue, exdays);
					    } else {

					    }
					    excecuteDartSequence(varValue, machineName, dart, variable, siteName, selLen, schVal, msgres[1],menuitem);
					});
				    }
				} else {
				    $("#dialog-form").css({'display': 'block'});
				    $("#dialog-form").dialog({
					resizable: false,
					autoOpen: false,
					height: 120,
					width: 470,
					modal: true
				    });
				    $("#dialog-form").dialog("open");
				    $("#custok")
					    .click(function () {
						$("#dialog-form").dialog("close");
					    });
				}
			    } else {
				schVal = 0;
				excecuteDartSequence(varValue, machineName, dart, variable, siteName, selLen, schVal, msgres[1],menuitem);
			    }
			},
			error: function () {
			    // Action to write when getDartpushedStatus ajax call fails..
			    $(".loadingStage").css({'display': 'none'});
			}
		    });
		} else {
		    $(".statusError").html('Sorry, client is not available. Hence the fix cannot be pushed.');
		    $(".statusError").css({'display': 'block'});
		    // if machine is uninstalled or not reported to node.
		    $(".loadingStage").css({'display': 'none'});
		    /* this is the code for hiding progress - Start */
		    setTimeout(function () {
			$(".statusError").fadeOut("slow", function () {
			});
		    }, 5000);
		    /* this is the code for hiding progress - End */
		}
	    },
	    error: function () {
		// Action to write when findMachineUninstallOrNot ajax call fails..
		$(".loadingStage").css({'display': 'none'});
	    }
	});
    } else {
	$(".statusError").html('Sorry, the C2F+ client is not available on this service tag. Hence the fix cannot be pushed.');
	$(".statusError").css({'display': 'block'});
	/* this is the code for hiding progress - Start */
	setTimeout(function () {
	    $(".statusError").fadeOut("slow", function () {
	    });
	}, 5000);
	/* this is the code for hiding progress - End */
	// if machine is uninstalled or not reported to node.
	$(".loadingStage").css({'display': 'none'});
    }
}

function excecuteDartSequence(varValue, machineName, dart, variable, siteName, selLen, schVal, mstatus,menuitem) {
    
    $.ajax({
	type: "GET",
        url: base_url+"customer/insertScheduleJob/" + siteName + '/' + machineName + '/' + dart + '/' + menuitem + '/'+variable+'/'+agentName+'/'+agentphone+'/'+agentEmail +"&csrfMagicToken=" + csrfMagicToken,
	success: function (msg) {

	    var msgarr = msg.split('##');
	    if (msgarr[1] == '') {

		return;
	    }
	    if (schVal == 1) {
		getSchDet();
	    }

	    if (selLen == 2) {
		$("#rightNavtiles").css({'display': 'block'});
		$("#rightNavtiles").html("<span style='color:#009933; font-size:12px;'>Resolutions initiated for selected PC. Progress can be viewed below.<br />Simultaneous resolution triggers will be queued and executed.</span><br />");
		$("#rightNavtiles").append("<div class='msg-ok-img'><img src='../ui/images/ok.png' /></div>");
	    } else {
		$("#rightNavtiles").css({'display': 'block'});
		$("#rightNavtiles").html("<span style='color:#009933; font-size:12px;'>Resolutions initiated for selected PC's. Status can be viewed under Audit section.<br />Simultaneous resolution triggers will be queued and executed.</span><br />");
		$("#rightNavtiles").append("<div class='msg-ok-img'><img src='../ui/images/ok.png' /></div>");
	    }
	    //$(".loadingStage").css({'display':'none'});
	    if (mstatus != 'offline') {
		emitDart(selLen, machineName, dart);
	    } else {
		$(".loadingStage").css({'display': 'none'});
	    }
	},
	error: function (err) {
	    $(".loadingStage").css({'display': 'none'});
	    $("#rightNavtiles").html("<span style='color:#009933; font-size:12px;'>Not Executed</span><br />");
	}
    });
}


function connectNodeServer(callback) {
    if (socket === '' || socket === undefined) {
        $.getScript(nodeUrl + "/socket.io/socket.io.js").done(function () {
            socket = io.connect(nodeUrl);
            callback();
        });
    }
}


function emitDart(maclen, machineName, dart) {
    if (socket == '' || socket == undefined) {
        // Connect to Socket IO			    
        connectNodeServer(function () {

            showDartStaus(machineName);
            emitJobs(machineName);
        });

    } else {
        emitJobs(machineName);
    }

}

function emitJobs(machineName) {
   
    $.ajax({
        type: "GET",
        url: base_url+"customer/get_ScheduleJob/" +  machineName +"&csrfMagicToken=" + csrfMagicToken,
        success: function (msg) {
            msg = $.trim(msg);
            if (msg != '') {
                var emitMsg = msg.split('##');
                for (var i = 0; i < emitMsg.length - 1; i++) {
                    socket.emit('executeDart', emitMsg[i]);
                }
            }
        }
    });
}

 function showDartStaus(glMachineName) {
     
    socket.on('listenjobs', function (msg) {                           
        console.log('ListenJobs :' + msg);
        var msgDetails = msg.split("---");
        var machineName = $.trim(msgDetails[2]);

        machineName = machineName.toUpperCase();
        glMachineName = glMachineName.toUpperCase();

        if (machineName === glMachineName) {
           
            setfixList(msg, glMachineName);
        }
        
    });
}


function setfixList(textvar, machineName) {

    $("#progressMainDiv").show();
    $("#progressDiv").show();
    if (textvar.indexOf("System Online") !== -1) {
        $("#progressDiv").hide();
        var ststtr1 = '<b style="color:green">Online</b>';
        $("#toolBxMStatus").html(ststtr1);
        return;
    }
    if (textvar.indexOf("System Offline") !== -1) {
        $("#progressDiv").hide();
        var ststtr2 = '<b style="color:red">Offline</b>';
        $("#toolBxMStatus").html(ststtr2);
        return;
    }
    if (textvar.indexOf("Image Uploaded") !== -1) {
        updateScreenShare('/resolutionimages/' + machineName + '.jpg');
        return;
    }
    if (textvar.indexOf("Image Sharing Stopped") !== -1) {
        $('#resImage').hide();
        $('#imgclbtn').hide();
        $('#progressImage').show();
        return;
    }

    $(".loadingStage").css({'display': 'block'});
    $('#showdartStatus').hide();

    var val1 = textvar.split("---");
    var val2 = val1[4];
    var val = val2.split("=");
    console.log(textvar);
    if (val2 !== 'Execution Completed') {
        if (textvar.indexOf("Windows Tweak") !== -1) {
            var jobHeader = 'Windows Tweak';
        } else {
            var obja = getObjects(obj, 'varValue', val[0].trim());
            var jobHeader = obja[0].profile;
        }
    }

    var cancelMachine = "'" + glMachineName + "'";
    var val;
    var i, j, k;
    //var liList = '<div class="steps_content" style="overflow-x: hidden;width:500px;box-shadow: 0px 0px 10px #c4c4c4;padding-bottom: 10px;background-color:#ffffff;"><span style="float:left;color:grey;padding-top:5px;margin-left: 25px;font-weight: bold;width: 93%;">Resolution progress <a onclick="myFunction()"><img src="../images/close-icon.png" style="float:right;width: 24px;"></a></span><div class="progress-div"><div class="fixes" style="height:100px; overflow-x: hidden; width:97%;margin-top:15px;margin-left: 10px;" ><ul>';
    var liList = '<p style="font-size:16px;">' + jobHeader + '</p><div class="fixing-issue-div service-log-fix scroller"  id="progressDiv" style="height:350px;position:relative"><ul class="fixing-issue ">';
    var len = val.length;

    if (textvar.indexOf("Execution Completed") !== -1) {
        k = 4;
    } else {
        for (i = 1; i < val.length - 1; i++) {

            liList += '<li>' + val[i].replace(/[0-9]/g, '');

            var statusVal = val[i + 1].charAt(0);

            if (statusVal === '1' || statusVal === 1) {
                liList += '<span style="float:right"><img src="'+base_url+'assets/images/ajax-loader.gif"/></span>';
            } else if (statusVal === '2' || statusVal === 2) {
                liList += '<span style="float:right"><img src="'+base_url+'assets/images/fixed.png"/></span>';
            } else if (statusVal === '3' || statusVal === 3) {
                liList += '<span style="float:right"><img src="'+base_url+'assets/images/processFailed.png"/></span>';
            } else {
                liList += '<span style="float:right"></span>';
            }

            liList += '</li>';
            laslist = liList;
            if (i == len - 2) {
                k = val[i + 1].charAt(0);
            }
        }
    }
    liList += '</ul>';
    liList += '</div>';

    if (val2 == 'Execution Completed') {
        laslist += '</ul>';
        laslist += '</div>';
        liList = laslist;
        if (cancelVal == 0) {

            liList += '<div style="float:right;padding-right:10px;padding-bottom:5px;"><a style="margin-top:10px;cursor:default;padding:5px;color:grey;">Resolution sequence executed successfully.</a></div>';
        } else {
            liList += '<div style="float:right;padding-right:10px;padding-bottom:5px;"><a style="margin-top:10px;cursor:default;padding:5px;color:grey;">Resolution sequence has been cancelled.</a></div>';
            cancelVal = 0;
        }
        cancelShown = 0;
        $("#ExceCancelButton").html('');
        /* this is the code for hiding progress - Start */
        setTimeout(function() {
            $("#progressDiv").fadeOut("slow", function() {
                $("#progressDiv").html('');
                $("#progressMainDiv").html('');
                $("#progressMainDiv").hide();
                $('#systemAnalysis').show();
            });
            
        }, 5000);
        /* this is the code for hiding progress - End */
    } else {
        if (cancelVal == 0) {
            liList += '<div style="float:right;padding-right:10px;padding-bottom:5px;"><a style="background-color:#5cc1ed;margin-top:10px;cursor:pointer;padding:5px;color:#fff;" onclick="cancelDart(' + cancelMachine + ')" id="cancelbtn">Cancel</a></div>';
        } else {
            liList += '<div style="float:right;padding-right:10px;padding-bottom:5px;"><a style="background-color:#5cc1ed;margin-top:10px;cursor:pointer;padding:5px;color:#fff;" id="cancelbtn">Cancelling...</a></div>';
        }
    }
    liList += '</div></div>';
    $(".loadingStage").css({'display': 'none'});

    $('#progressMainDiv').html(liList);
    scroller();
}

function getObjects(obj, key, val) {
                        var objects = [];
    for (var i in obj) {
        if (!obj.hasOwnProperty(i))
            continue;
        if (typeof obj[i] == 'object') {
            objects = objects.concat(getObjects(obj[i], key, val));
        } else
        //if key matches and value matches or if key matches and value is not passed (eliminating the case where key matches but passed value does not)
        if (i == key && obj[i] == val || i == key && val == '') { //
            objects.push(obj);
        } else if (obj[i] == val && key == '') {
            //only add if the object is not already in the array
            if (objects.lastIndexOf(obj) == -1) {
                objects.push(obj);
            }
        }
    }
    return objects;
}
                    
                    
                     
function scroller() {
    $('.scroller').each(function() {
        var height;
        if ($(this).attr("data-height")) {
            height = $(this).attr("data-height");
        } else {
            height = $(this).css('height');
        }
        $(this).slimScroll({
            size: '7px',
            height: height,
            alwaysVisible: ($(this).attr("data-always-visible") == "1" ? true : false),
            railVisible: ($(this).attr("data-rail-visible") == "1" ? true : false),
            disableFadeOut: true
        });
    });

}


function getProfileData(){
    
    $.ajax({
	type: "GET",
        url: base_url+"customer/get_ProfileName/" +  OSDB +"&csrfMagicToken=" + csrfMagicToken,
	success: function (msg) {
              obj = jQuery.parseJSON(msg);
	}
    });
}


function cancelDart(machineName) {
    if (socket == '' || socket == undefined) {
        connectNodeServer(function () {
            triggerCancel(machineName);
        });
    } else {
        triggerCancel(machineName);
    }
}

function triggerCancel(machineName) {
    $.ajax({
        type: "GET",
        url: base_url+"customer/get_CancelMachineDet/" +  machineName +"&csrfMagicToken=" + csrfMagicToken,
        success: function (msg) {
            $('#cancelbtn').html('Canceling...');
            cancelVal = 1;
            socket.emit('executeDart', 'dboard---cancel---siteid---' + msg.trim());
        },
        error: function () {
            
            $("#rightNavtiles").html("<span style='color:#009933; font-size:12px;'>Not Executed</span><br />");
        }

    });
}

//for audit information
$("#audit").click(function () {

    $(".loadingStage").css({'display': 'none'});
    schdtl = 0;
    $("#schDet").css({'display': 'none'});
    $('#showdartStatus').hide();
    $('.steps_content').hide();
    pb = 2;
    $("#gridContent").hide();
    $("#slidingShowstatus").hide();
    $("#scheduleDiv").hide();
    $("#auditDiv").show();
    $("#mainBack").show();
    $("#rightNavOneright121, #advAct").hide();
    $("#syncDart").hide();
    $("#serverEnabl").hide();
    $("#serverDisabl").hide();
    $("#actTxt").hide();
    $("#rightNavtiles").css({'display': 'none'});
    $("#remoteMaintenanceDiv").hide();

    var midHight = $(window).height() - 400;

   // var address = "auditgrid.php?serviceTag={/literal}{$machine}{literal}&custno={/literal}{$enterdServTag}{literal}&orderno={/literal}{$enterdServReq}{literal}";
    var address = base_url+"customer/get_AuditDetails/" + custmrNo + '/' + ordrNo + '/' + EnSerTag;
    if (scriptloadstataudit == 1) {
        scriptloadstataudit = 0;
    } else {
        FlexiReloadPage(address, 'auditTable');
        return;
    }
    $("#auditTable").flexigrid({
        url: address,
        dataType: "json",
        colModel: [
            {display: "System Serial Number", name: "customerNum", width: 40, sortable: true, align: "left"},
            {display: "Case Number", name: "orderNum", width: 40, sortable: true, align: "left"},
            {display: "System Service Tag", name: "servicetag", width: 40, sortable: true, align: "left"},
            {display: "Triggered By", name: "username", width: 40, sortable: true, align: "left"},
            {display: "Solution pushed", name: "solutionPushed", width: 30, sortable: true, align: "left"},
            {display: "Triggered Time", name: "createdtime", width: 40, sortable: true, align: "left"},
            {display: "Proof", name: "proof", width: 20, sortable: false, align: "right"}
        ],
        sortname: "servicetag",
        sortorder: "asc",
        usepager: true,
        useRp: false,
        rp: 20,
        height: midHight,
        showTableToggleBtn: true
    });
    $(".imgLoad").css({'display': 'none'});
});

//flexi reload
function FlexiReloadPage(urlstringrid, flexigridName)
{
    $("#setGlobalId").val('');
    var urlAction = urlstringrid;
    $.getScript(base_url+"assets/js/flexigrid.js")
            .done(function () {
                $('#' + flexigridName).flexOptions({url: urlAction}).flexReload();
                restrictAjax = 0;
            })
            .fail(function (jqxhr, settings, exception) {
                alert("Fail to relaod flexi grid Please reload the page.");
            });

    $(".loadingStage").css({'display': 'none'});
    $("#rightNavtiles").css({'display': 'none'});
}


//for scheule information.
$("#schedule").click(function () {

    $(".loadingStage").css({'display': 'none'});
    schdtl = 0;
    $("#schDet").css({'display': 'none'});
    $('#showdartStatus').hide();
    $('.steps_content').hide();
    pb = 2;
    $("#gridContent").hide();
    $("#slidingShowstatus").hide();
    $("#auditDiv").hide();
    $("#scheduleDiv").show();
    $("#mainBack").show();
    $("#rightNavOneright121, #advAct").hide();
    $("#syncDart").hide();
    $("#serverEnabl").hide();
    $("#serverDisabl").hide();
    $("#actTxt").hide();
    $("#rightNavtiles").css({'display': 'none'});
    $("#remoteMaintenanceDiv").hide();

    var midHight = $(window).height() - 400;
    var address = base_url+"customer/get_ScheduleDetails/" + custmrNo + '/' + ordrNo + '/' + EnSerTag;
    if (scriptloadstat == 1) {
        scriptloadstat = 0;
    } else {
        FlexiReloadPage(address, 'scheduleTable');
        return;
    }

    $("#scheduleTable").flexigrid({
        url: address,
        dataType: "json",
        colModel: [
           
            {display: "System Serial Number", name: "customerNum", width: 40, sortable: true, align: "left"},
            {display: "Case Number", name: "orderNum", width: 40, sortable: true, align: "left"},
            {display: "System Service Tag", name: "servicetag", width: 40, sortable: true, align: "left"},
            {display: "Triggered By", name: "username", width: 40, sortable: true, align: "left"},
            {display: "Solution pushed", name: "Solution_pushed", width: 30, sortable: true, align: "left"},
            {display: "Triggered Time", name: "scheduleTime", width: 40, sortable: true, align: "left"},
            {display: "Proof", name: "Proof", width: 20, sortable: true, align: "center"}
        ],
        sortname: "servicetag",
        sortorder: "asc",
        usepager: true,
        useRp: false,
        rp: 20,
        height: midHight,
        showTableToggleBtn: true
    });
    $(".imgLoad").css({'display': 'none'});
});

function mainBackButton(){
    $("#auditDiv").hide();
    $("#scheduleDiv").hide();
    $("#gridContent").show();
    $("#mainBack").hide();
}