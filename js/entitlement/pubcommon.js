$("#homeFootForServiceLog").hide(); //to hide privacy statement
// JavaScript Document

function MM_swapImgRestore() { //v3.0
    var i, x, a = document.MM_sr;
    for (i = 0; a && i < a.length && (x = a[i]) && x.oSrc; i++)
        x.src = x.oSrc;
}
function MM_preloadImages() { //v3.0
    var d = document;
    if (d.images) {
        if (!d.MM_p)
            d.MM_p = new Array();
        var i, j = d.MM_p.length, a = MM_preloadImages.arguments;
        for (i = 0; i < a.length; i++)
            if (a[i].indexOf("#") != 0) {
                d.MM_p[j] = new Image;
                d.MM_p[j++].src = a[i];
            }
    }
}

function MM_findObj(n, d) { //v4.01
    var p, i, x;
    if (!d)
        d = document;
    if ((p = n.indexOf("?")) > 0 && parent.frames.length) {
        d = parent.frames[n.substring(p + 1)].document;
        n = n.substring(0, p);
    }
    if (!(x = d[n]) && d.all)
        x = d.all[n];
    for (i = 0; !x && i < d.forms.length; i++)
        x = d.forms[i][n];
    for (i = 0; !x && d.layers && i < d.layers.length; i++)
        x = MM_findObj(n, d.layers[i].document);
    if (!x && d.getElementById)
        x = d.getElementById(n);
    return x;
}

function MM_swapImage() { //v3.0
    var i, j = 0, x, a = MM_swapImage.arguments;
    document.MM_sr = new Array;
    for (i = 0; i < (a.length - 2); i += 3)
        if ((x = MM_findObj(a[i])) != null) {
            document.MM_sr[j++] = x;
            if (!x.oSrc)
                x.oSrc = x.src;
            x.src = a[i + 2];
        }
}
//-->
function CheckFollow(follow, followList)
{
    var activeFollow = false;
    var data1 = followList.split("/");
    for (var i = 0; i <= data1.length; i++)
    {
        if (data1[i] == follow)
        {
            activeFollow = true;
        }
    }
    return activeFollow;
}

function setBodyCss(theme, text)
{
    var data = text.split('x');
    var value = data[theme].split('~');
    return value[0];
}

function getThemeFontColor(theme, text)
{
    var data = text.split('~');
    var value = data[theme].split('x');
    return value[2];
}

function getThemeColo(theme, text)
{
    var data = text.split('x');
    var value = data[theme].split('~');

    return value[1];
}

function getThemeFont(theme, text)
{
    var data = text.split('~');
    var value = data[theme].split('x');
    var font = value[0].replace(/#/g, ',');
    return font;
}

function getThemeFontSize(theme, text)
{
    var data = text.split('~');
    var value = data[theme].split('x');
    return value[1];
}

function displayDiscription(id, text, theme, text1)
{
    var themeFont = getThemeFont(theme, unescape(text1));
    var themesize = getThemeFontSize(theme, unescape(text1));
    $("#tileDiscrption_" + id).html('');
    $("#tileDiscrption_" + id).html('<p style="font-family: ' + themeFont + ' ; font-size:' + themesize + 'px;">' + unescape(text) + '</p>');
}

function hideDiscription(id)
{
    $("#tileDiscrption_" + id).html('');
}

function cleantextDescription()
{
    $(".tileDiscrption").html('');
}

function GetSlideMakeUp(colbreak)
{
    var themeFont = getThemeFont(themeValue, colbreak[19]);
    var themesize = getThemeFontSize(themeValue, colbreak[19]);
    var divSize = colbreak[11].split("x");
    return '<div style="width:' + divSize[0] + 'px;height:' + divSize[1] + 'px; text-wrap:normal;" ><div style="height:' + (divSize[1] - 2) + 'px; position: relative;"><div class="tileText" style="font-family: ' + themeFont + ' ; font-size:' + themesize + 'px; position: absolute; right:0px;">' + colbreak[4] + '</div></div></div>';

}
function displayProfile(id, page, url)
{
    setCookie("cookieForDefaultSettings", "0", 1);

    $(".centermenulist").css("display", "none");
    $(".sequenceBoxClass").css("display", "none");
    $("#center_" + id).css("display", "block");
    $('div:[class="sequencelist"]').css("display", "block");

    $(".rightMenuItems span").css({'color': '#444444', 'border-bottom': 'none'});

    if (page == 'toolbox')
    {
        $("#homeFootForServiceLog").hide(); //to hide privacy statement

        var tpage = getCookie("toolPage");

        $("#navigation").css({'display': 'none'});
        $("#toolboxmenu").css({'display': 'block'});
        $("#homeIndex").css({'display': 'none'});
        $("#systemInfo").css({'display': 'none'});
        $("#settingsInfo").css({'display': 'none'});
        $("#toolboxFoot").css({'display': 'block'});
        $("#servicelog").css({'display': 'none'});
        $("#homeFoot").css({'display': 'none'});

        if (tpage == 'toolbox') {
            loadToolBox(id);

        } else {
            var iframeObjToll = document.getElementById("toolboxIframe");
            if (iframeObjToll) {
                iframeObjToll.src = url;
                setCookie("toolPage", 'toolbox', 1);
            } else {

            }
        }
        $(".menu" + id).css({'color': '#0085C3'});
    }

    if (page == 'servicelog')
    {
        $("#homeFootForServiceLog").show(); //to show privacy statement

        var servPage = getCookie("servicePage");

        $("#navigation").css({'display': 'none'});
        $("#servicelog").css({'display': 'block'});
        $("#systemInfo").css({'display': 'none'});
        $("#settingsInfo").css({'display': 'none'});
        $("#toolboxmenu").css({'display': 'none'});
        $("#homeIndex").css({'display': 'none'});
        $("#toolboxFoot").css({'display': 'none'});
        $("#homeFoot").css({'display': 'none'});
        $("#navigation").html("service log");

        $(".menu" + id).css({'color': '#0085C3'});

        if (servPage == 'servicelog') {
            loadServiceLog(id);
        } else {
            var iframeObjserv = document.getElementById("servicelogIframe");
            if (iframeObjserv) {
                iframeObjserv.src = url;
                setCookie("servicePage", 'servicelog', 1);
            } else {

            }
        }
    }

    if (id == "1")
    {
        $("#homeFootForServiceLog").hide(); //to hide privacy statement

        $("#toolboxmenu").css({'display': 'none'});
        $("#homeIndex").css({'display': 'block'});
        $("#systemInfo").css({'display': 'none'});
        $("#settingsInfo").css({'display': 'none'});
        $("#navigation").css({'display': 'block'});
        $("#homeFoot").css({'display': 'block'});
        $("#toolboxFoot").css({'display': 'none'});
        $("#servicelog").css({'display': 'none'});
        $("#navigation").css({'display': 'block'});
        $("#navigation").html("");

        $(".menu" + id).css({'color': '#0085C3'});

        var homeReload = getCookie("homeReload");
        if (homeReload == 1) {
            setCookie("homeReload", '0', 1);
            location.href = 'index.html';
        }
        //setCookie("backupType",'0',1);
    }
}

function loadServiceLog(id) {
    $("#navigation").css({'display': 'none'});
    $("#servicelog").css({'display': 'block'});
    $("#systemInfo").css({'display': 'none'});
    $("#settingsInfo").css({'display': 'none'});
    $("#toolboxmenu").css({'display': 'none'});
    $("#homeIndex").css({'display': 'none'});
    $("#toolboxFoot").css({'display': 'none'});
    $("#homeFoot").css({'display': 'none'});
    $("#navigation").html("service log");

    $(".menu" + id).css({'color': '#0085C3'});
}

function loadToolBox(id) {
    $("#navigation").css({'display': 'none'});
    $("#toolboxmenu").css({'display': 'block'});
    $("#homeIndex").css({'display': 'none'});
    $("#systemInfo").css({'display': 'none'});
    $("#settingsInfo").css({'display': 'none'});
    $("#toolboxFoot").css({'display': 'block'});
    $("#servicelog").css({'display': 'none'});
    $("#homeFoot").css({'display': 'none'});

    $(".menu" + id).css({'color': '#0085C3'});

    var iframeObjToll = document.getElementById("toolboxIframe");

    //$("#toolboxIframe").contents().find(".slider-wrapperChg").css("margin-left","0");
    //$("#toolboxIframe").contents().find(".breadval").empty();

    //document.getElementById('toolboxIframe').contentWindow.document.querySelectorAll("slider-wrapperChg").style;
    //window.toolboxIframe.document.getElementById("slider-wrapper-1").style;
    //document.frames['toolboxIframe'].setHomePage();
}

function reloadtoolBox() {
    location.href = 'toolbox.html';
    setCookie("toolPage", 'toolbox', 1);
}

var validateLogin = true;
function beforedisplaySequence(id, slideId, page, menu)
{
    if (validateLogin == true)
    {
        displaySequence(id, slideId, page, menu);
    } else
    {
        beforedisplaySequenceLoad(id, slideId, page, menu);
    }
}

function beforedisplaySequenceLoad(id, slideId, page, menu)
{
    setCookie("Checkid", id, 1);
    setCookie("CheckslideId", slideId, 1);
    setCookie("Checkpage", page, 1);
    setCookie("Checkmenu", menu, 1);
    var urllink = "checkLogin.html"+"&csrfMagicToken=" + csrfMagicToken;
    $.nyroModalManual({
        debug: false,
        width: 600, // default Width If null, will be calculate automatically
        height: 250, // default Height If null, will be calculate automatically
        bgColor: '#333',
        ajax: {url: urllink, data: '', type: 'GET'},
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

function openHardDirveWindow(data1, data2, title)
{
    var url = data1 + data2;
    //soumen add
    var deskWdh = screen.width;
    var deskHgt = screen.height;
    var wdh = 750;
    var hgt = 420;
    var left = parseInt(((deskWdh - wdh) / 2) - 10);
    var top = parseInt((deskHgt - hgt) / 2);
    window.open(url, '1350301521986', 'width=' + wdh + ',height=' + hgt + ',toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,left=' + left + ',top=' + top + '');
    return false;
    //soumen end
    return false;
}

function displaySequence(id, slideId, page, menu)
{
    NextSlideJump(slideId - page, slideId, menu, id, page);
}

/*var flag=0;
 function getcenses(){
 $.ajax({
 type: "GET",
 url :"getcensus.html",
 data :"",
 error:function(msg){
 flag=0;
 setTimeout("getcenses()",3000);
 },
 success:function(msg){
 if(msg == '')
 {
 flag=0;
 setTimeout("getcenses()",3000);
 }else
 {
 output = msg.split("%");
 data = output[1];
 setCookie("censusid",data,1);
 if(flag !=0)
 {
 flag=1;
 }
 }
 }
 });
 }*/

function make_base_auth(user, password) {
    var tok = user + ':' + password;
    var hash = base64_encode(tok);

    return "Basic " + hash;
}

function changeImage(a) {
    document.getElementById("mstatus").src = a;
}

var validateUsername = 'dell';
var validatePssword = 'dell';

function StartSequence(dart, variable, varValue) {
    var schVal =0;
    if (machTagStatus != 'notavail') {
         
          $.ajax({
            type: "GET",
            url: base_url+"customer/getMachineStatus/"+ machineTag+"&csrfMagicToken=" + csrfMagicToken,
            success: function(unMsg) {
                if (unMsg == 0 || unMsg == "0") {

                    $.ajax({
                        type: "GET",
                        url: base_url+"customer/getDartStatus/"+ machineTag+'/'+varValue+"&csrfMagicToken=" + csrfMagicToken,
                        success: function(msg) {
                            
                            msg = $.trim(msg)
                            var msgres = msg.split("##");
                            //changeImage('../ui/images/' + msgres[1] + '.png');
                               
                            if (msgres[0] != 0 || msgres[0] != '0' || msgres[1] == 'offline' || machTagStatus == 'offline' || msgres[2] != 0 || msgres[2] != '0') {
                                
                                /* this is the new confirmation box ui and cheking check box */
                                var user = getCookie("prventNext");
                                
                                if (msgres[2] == 0 || msgres[2] == '0') {
                                    if (user != "" && user != undefined)
                                    {
                                        schVal = 0;
                                        //this is the call for direct going user checks
                                        excecuteDartSequence(varValue, machineTag, dart, variable, schVal, msgres[1]);
                                    } else {

                                        //schVal = 1;

                                        // example of calling the confirm function
                                        // you must use a callback function to perform the "yes" action
                                        confirm("A Resolution is already in progress. Do you want to queue this item?", function() {

                                            check = $("#prventNext").is(":checked");
                                            if (check) {
                                                var cname = 'prventNext';
                                                var cvalue = $("#prventNext").val();
                                                var exdays = 10;
                                                setCookie(cname, cvalue, exdays);
                                            } else {

                                            }
                                            excecuteDartSequence(varValue, machineTag, dart, variable, schVal, msgres[1]);
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
                                            .click(function() {
                                                $("#dialog-form").dialog("close");
                                            });
                                }
                            } else {

                                schVal = 0;
                                excecuteDartSequence(varValue, machineTag, dart, variable, schVal, msgres[1]);
                            }
                        },
                        error: function() {
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
                    setTimeout(function() {
                        $(".statusError").fadeOut("slow", function() {
                        });
                    }, 5000);
                    /* this is the code for hiding progress - End */
                }
            },
            error: function() {
                // Action to write when findMachineUninstallOrNot ajax call fails..
                $(".loadingStage").css({'display': 'none'});
            }
        });
    } else {
        
    }
    
 }


function excecuteDartSequence(varValue, machineName, dart, variable, schVal, mstatus) {

   
    var dartVal = '';
    if (dart == 43) {
        $("#overlay").css({'display': 'block'});
        $("#uninstall").css({'display': 'none'});
        //dartVal = 'ScripNo=43&S00043SilentUninstall=Execute&S00043SilentUninstall_GroupSetting=' + varValue;
        dartVal = 'S00043SilentUninstall_43_RUN_SET_Signaled'
        //deleteNodeData(machineName);
    } else if (dart == 256) {
        //dartVal = 'ScripNo=256&' + variable + '=Execute&' + variable + '_GroupSetting=CENSUSID';
        variable = variable.replace(/_/g, "$");
        dartVal = variable + '_256_RUN_SET_Signaled';
    } else {
        //dartVal = 'ScripNo=' + dart + '&S00' + dart + 'ProfileName1=' + varValue + '&S00' + dart + 'ProfileName1_GroupSetting=CENSUSID&' + variable + '=Execute&' + variable + '_GroupSetting=CENSUSID';
        dartVal = dart + '-' + varValue;
    }

    
    $.ajax({
        type: "GET",
        url: base_url+"customer/addSchedulejob/"+ machineName+'/'+ dartVal+"&csrfMagicToken=" + csrfMagicToken,
        success: function(msg) {
            var msgarr = msg.split('##');
            if (msgarr[1] == '') {

                return;
            }
            /*if (schVal == 1) {
                getSchDet();
            }*/

          
            if (mstatus != 'offline') {
                emitDart(machineName, dart);//selLen,machineName
            } else {
                
            }
        },
        error: function(err) {
           
            $("#rightNavtiles").html("<span style='color:#009933; font-size:12px;'>Not Executed</span><br />");
        }
    });
}

function deleteNodeData(machineName) {
    var returnMsg = '';
    $.ajax({
        type: "GET",
        url: "../includes/notification_ajax.php",
        data: "function=deleteDart&machineName=" + machineName +"&csrfMagicToken=" + csrfMagicToken,
        success: function(msg) {
            returnMsg = msg;
        },
        error: function(msg) {
            returnMsg = msg;
        }
    });
    return returnMsg;
}

function getAuditCount(machineName) {
    var returnMsg = '';
    $.ajax({
        type: "POST",
        url: "../includes/notification_ajax.php",
        data: "function=getDartpushedStatus&machineName=" + machineName +"&csrfMagicToken=" + csrfMagicToken,
        success: function(msg) {
            returnMsg = msg;
        },
        error: function() {
            returnMsg = msg;
        }
    });
    return returnMsg;
}

function openHardDirveWindowMulti(dart, variable, varValue, title) {
    var urllink = "multipleExecution.php";
    $.nyroModalManual({
        modal: false,
        debug: false,
        width: 600, // default Width If null, will be calculate automatically
        height: 260, // default Height If null, will be calculate automatically
        bgColor: '#8699A5',
        ajax: {
            url: urllink,
            data: 'dart=' + variable + '&varValue=' + varValue + '&title=' + title +"&csrfMagicToken=" + csrfMagicToken,
            type: 'GET'
        },
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

function emitDart(machineName, dart) {
      
      if (socket == '' || socket == undefined) {

         $.getScript("https://nodeio.nanoheal.com/socket.io/socket.io.js").done(function (script, textStatus) {
                 socket = io.connect("https://nodeio.nanoheal.com");
                 showDartStaus(machineName, dart, socket);
                 //getJob(machineName);
                 
         });
      }
                        
}


function getJob(machineName){
    
    $.ajax({
             type: "GET",
             url: base_url+"customer/getSchedulejob/"+ machineName +"&csrfMagicToken=" + csrfMagicToken,
             success: function (msg) {
                  
                     if (msg != '') {
                        var emitMsg = msg.split('##');
                        for (var i = 0; i < emitMsg.length - 1; i++) {
                             socket.emit('executeDart', emitMsg[i]);
                        }

               }
           }
    });
}


function showDartStaus(machineName, dart, socket) {
      
        socket.on('listenjobs', function (msg) {

                   if (msg != '') {

                     var val1 = msg.split("---");
                     var machineVal = val1[2];
                     if (machineVal == machineName) {
                       setfixList(msg, machineName, dart);
                     }
                }
           $("#overlay").css({'display': 'none'});
      });
}

function changeImage(a) {
      document.getElementById("mstatus").src = a;
}
                    
function setfixList(textvar, machineName, dart) {
                        
               var cancelVal = 0;
               var pb = 0;
               var laslist = '';
               //$('#showdartStatus').hide();
               var val1 = textvar.split("---");
               var machineVal = val1[2];
               if (machineVal != machineName) {
                   return;
               }
               var val2 = val1[4];
               if (val2 == 'online') {
                   //changeImage('../ui/images/online.png');
                   return;
                }
                var val = val2.split("=");

                var cancelMachine = "'" + machineName + "'";
                var val;
                var i, j, k;
                var liList = '<div class="steps_content" style="width:500px;box-shadow: 0px 0px 10px #c4c4c4;padding-bottom: 10px;background-color:#ffffff;"><span style="float:left;color:grey;padding-top:5px;margin-left: 25px;font-weight: bold;width: 93%;">Resolution progress <a onclick="myFunction()"><img src="../images/close-icon.png" style="float:right;width: 24px;"></a></span><div class="progress-div"><div class="fixes" style="height:100px;overflow-y: auto; overflow-x: hidden; width:100%;margin-top:15px;" ><ul>';
                var len = val.length;

                if (textvar.indexOf("Execution Completed") !== -1 || textvar.indexOf("Execution Terminated") !== -1) {
                     k = 4;
                } else {
                            for (i = 1; i < val.length - 1; i++) {

                                liList += '<li>' + val[i].replace(/[0-9]/g, '');

                                var statusVal = val[i + 1].charAt(0);

                                if (statusVal == '1' || statusVal == 1) {
                                    liList += '<span style="float:right">In Progress..</span>';
                                } else if (statusVal == '2' || statusVal == 2) {
                                    liList += '<span style="float:right"><img src="../images/fixed.jpg"/></span>';
                                } else if (statusVal == '3' || statusVal == 3) {
                                    liList += '<span style="float:right"><img src="../images/processFailed.png"/></span>';
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
                        
              if (val2 == 'Execution Completed' || val2 == 'Execution Terminated' || val2 == 'offline') {
                            laslist += '</ul>';
                            laslist += '</div>';
                            liList = laslist;
                            if (val2 == 'offline') {
                                liList += '<span style="float:right;padding-right:10px;padding-bottom:5px;"><a style="background-color:#ffffff;margin-top:10px;cursor:default;padding:5px;color:grey;">System went Offline.</a></span>';
                                changeImage('../ui/images/offline.png');
                            } else if (cancelVal == 0) {
                                liList += '<span style="float:right;padding-right:10px;padding-bottom:5px;"><a style="background-color:#ffffff;margin-top:10px;cursor:default;padding:5px;color:grey;">Solution pushed have been successfully executed.</a></span>';
                            } else {
                                liList += '<span style="float:right;padding-right:10px;padding-bottom:5px;"><a style="background-color:#ffffff;margin-top:10px;cursor:default;padding:5px;color:grey;">Present solution has been cancelled.</a></span>';
                                cancelVal = 0;
                            }

                            $('#showdartStatus').hide();
                            /* this is the code for hiding progress - Start */
                            setTimeout(function () {
                                $("#fixlist").fadeOut("slow", function () {
                                    $("#fixlist").html('');
                                });
                                $("#showdartStatus").fadeOut("slow", function () {

                                });
                                //getSchDet();
                            }, 5000);
                            /* this is the code for hiding progress - End */
                        } else {
                            if (cancelVal == 0) {
                                liList += '<span style="float:right;padding-right:10px;padding-bottom:5px;"><a style="background-color:#5cc1ed;margin-top:10px;cursor:pointer;padding:5px;color:#fff;" onclick="cancelDart(' + cancelMachine + ')" id="cancelbtn">Cancel</a></span>';
                            } else {
                                liList += '<span style="float:right;padding-right:10px;padding-bottom:5px;"><a style="background-color:#5cc1ed;margin-top:10px;cursor:pointer;padding:5px;color:#fff;" onclick="cancelDart(' + cancelMachine + ')" id="cancelbtn">Cancelling...</a></span>';
                                //console.log(cancelVal);
                            }
                        }
                        liList += '</div></div>';
                        if (pb == 1) {
                            $("#fixlist").show();
                        } else {
                            $("#fixlist").hide();
                        }

                        $('.steps_content').css({'display': 'block'});
                        
                        

                        $('#fixlist').html(liList);
}