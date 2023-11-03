<?PHP

include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';

$notifyWindow = 0;
$soln = 0;
$sel_nid = '';
if (isset($_SESSION['notifyWindow'])) {
  $sel_nid = $_SESSION['selectednid'];
  $notifyWindow = 1;
  $soln = 1;
  unset($_SESSION['notifyWindow']);
}

$agentName = $_SESSION["user"]["logged_username"];
$agentUniqId = $_SESSION["user"]["adminEmail"];

if (is_null($agentUniqId) || $agentUniqId === '') {
  $agentUniqId = $_SESSION["user"]["adminid"];
}

function MAKE_CURL_CALL($data)
{
  global $licenseapiurl;

  $data_string = json_encode($data);

  $header = array(
    'Content-Type: application/json',
    "X-Nh-Token: " . nhRole::getNhTokenForHeader(),   'PHPSESSID: ' . session_id(),
    'Content-Length: ' . strlen($data_string),
  );
  try {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $licenseapiurl);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    $result = curl_exec($ch);
    $presdata = safe_json_decode($result, true);
    curl_close($ch);
  } catch (Exception $ex) {
    logs::log(__FILE__, __LINE__, $ex, 0);
    return "Exception : " . $ex;
  }
  return $presdata;
}

$searchType = $_SESSION['searchType'];
$searchValue = $_SESSION['searchValue'];
if ($searchType == 'Sites') {
  $licnsdata['function'] = 'getlicensedetails';
  $licnsdata['data']['sitename'] = $searchValue;
  $licensedetails = MAKE_CURL_CALL($licnsdata);

  $wsurlAbs = $licensedetails['wsurl'];
  $wsurltemp = explode('//', $wsurlAbs);
  //$wsurl = $wsurltemp[1];
} else {
  $db = db_connect();
  $confsql = "select * from " . $GLOBALS['PREFIX'] . "core.Options where name = 'dashboard_config' limit 1";
  $confres = find_one($confsql, $db);
  $confdata = safe_json_decode($confres['value'], true);
  //$wsurl = $confdata['wsurl'];
}
global $wsurl;
global $reportingurl;
?>

<script>
  /* global wNUrl, io, mNUrl, arrAllcheckVal, glMachineName, google, bootbox, jQuery_1_7, wsurl, require */
  var ds = 1;
  var cancelVal = 0;
  var glmachineName = '';
  var watchID;
  var laslist;
  var listening = 0;
  var themeValue = 0;
  var productType = "HP";
  var lang = 'en';
  var ws = '';
  var GlServiceTag = '';
  var CancelDart = 0;
  //var ShowProgress = 0;
  var auto_soln = '<?php echo $soln; ?>';
  var sel_nid = '<?php echo $sel_nid; ?>';
  var wsurl = '<?php echo $wsurl; ?>';
  var reportingurl = '<?php echo $reportingurl; ?>';
  $(document).ready(function() {
    LogToConsole('WS Server : ' + 'https://' + wsurl + ' Reporting Server: ' + reportingurl);
    if (auto_soln !== 0 || auto_soln !== '0') {
      // autoSolnDart(sel_nid);
    }
    // Connect to WebSocket Socket IO
    if (ws === '') {
      LogToConsole('Inside WS Server : ' + 'https://' + wsurl + ' Reporting Server: ' + reportingurl);
      //            if (window.location.protocol !== "https:") {
      //                wsconnect('wss://' + wsurl, reportingurl);
      //                LogToConsole('Connecting to Communication Server : ' + 'http://' + wsurl);
      //            } else {
      wsconnect('wss://' + wsurl, reportingurl);
      LogToConsole('Connecting to Communication Server : ' + 'https://' + wsurl);
      //       }
    } else {
      LogToConsole('Already Connected to Node');
    }
  });

  function wsconnect(wsurl, reportingurl) {
    ws = new WebSocket(wsurl);
    ws.onopen = function() {
      LogToConsole('Connecting to Communication Server Success');
      var ConnectData = {};
      ConnectData['Type'] = 'Dashboard';
      ConnectData['AgentId'] = '<?php echo $agentUniqId; ?>';
      ConnectData['AgentName'] = '<?php echo $agentName; ?>';
      ConnectData['ReportingURL'] = reportingurl;
      ws.send(JSON.stringify(ConnectData));
    };
    ws.onmessage = function(msg) {
      LogToConsole("Inside onmessage");
      LogToConsole("MESSAGE___>" + JSON.stringify(msg));
      LogToConsole("GlServiceTag-->" + GlServiceTag);
      //            ShowJobProgress(msg);
      var JsonMsg = JSON.parse(msg.data);
      LogToConsole("JsonMsg -->" + JSON.stringify(JsonMsg));
      var ServiceTag = JsonMsg.ServiceTag;
      LogToConsole("ServiceTag" + ServiceTag);
      if (GlServiceTag === ServiceTag) {
        var JsonMsg = JSON.parse(msg.data);
        var Status = JsonMsg.Status;
        var val = Status.split("=");
        var CheckValue = val[0];
        var checkVal = $('#selectedPrfoleName').val();
        if ($.trim(CheckValue) !== "Execution Completed" && $.trim(CheckValue) != $.trim(checkVal)) {
          $('#loader_pbar').hide();
          $('#progressMainDiv').html("The previous Job is not completed");
        } else {
          LogToConsole('Message Received : ' + msg.data);
          ShowJobProgress(msg);
        }
      }
    };
    ws.onclose = function() {
      LogToConsole("Connection getting closed frequently");
      setTimeout(function() {
        wsconnect(wsurl);
      }, 2000);
    };
    ws.error = function(msg) {
      LogToConsole('Error in Web socket' + msg.data);
      wsconnect(wsurl, reportingurl);
    };
  }

  // FCM Jobs for Servicetag (Android)

  function FCMJobsForServiceTags() {

  }

  // Emit Jobs for Servicetag (Windows, Linux, Mac)
  function EmitJobsForServiceTags(SupportedMachines, ShowProgressServiceTag) {
    if ($.trim(SupportedMachines) !== '') {
      var ServiceTags = SupportedMachines.split('~~');
      if (ShowProgressServiceTag !== '') {
        GlServiceTag = trimStr(ShowProgressServiceTag);
        LogToConsole('Show Progress for :' + GlServiceTag);
        $('#loader_pbar').show();
      } else {
        GlServiceTag = '';
        LogToConsole('Progress will be hidden');
        $('#progressMainDiv').html('Progress will be displayed only at Machine Level');
      }

      if (ws === '' || ws === undefined) {
        wsconnect('wss://' + wsurl, reportingurl);
        LogToConsole('Connecting to Communication Server : ' + 'https://' + wsurl);
        for (var i = 0; i < ServiceTags.length; i++) {
          var JobData = {};
          JobData['Type'] = 'ExecuteJob';
          JobData['ServiceTag'] = trimStr(ServiceTags[i]);
          ws.send(JSON.stringify(JobData));
        }
        // Pending
        //alert("Node is not Connected");
      } else {
        for (var i = 0; i < ServiceTags.length; i++) {
          var JobData = {};
          JobData['Type'] = 'ExecuteJob';
          JobData['ServiceTag'] = trimStr(ServiceTags[i]);
          ws.send(JSON.stringify(JobData));
        }
      }
    }
  }

  function ExecuteDirectJob(Servicetag, DirectJob) {
    //DirectJob = Math.round(new Date().getTime()/1000) + '###' + DirectJob;
    var JobData = {};
    JobData['Type'] = 'ExecuteDirectJob';
    JobData['ServiceTag'] = trimStr(Servicetag);
    JobData['DirectJob'] = 'InstantExecution---' + DirectJob;
    ws.send(JSON.stringify(JobData));
  }

  // Remove unwanted spaces and tabs
  function trimStr(str) {
    return str.replace(/^\s+|\s+$/g, '');
  }

  function LogToConsole(Msg) {
    console.log(Msg);
  }

  function ShowJobProgress(JobStatus) {
    $(".loadingStage").css({
      'display': 'block'
    });
    $('#showdartStatus').hide();
    var divId = $("#divId").val();
    var JsonMsg = JSON.parse(JobStatus.data);
    var Status = JsonMsg.Status;
    //        alert(Status);
    var ServiceTag = JsonMsg.ServiceTag;
    //        if (trimStr(GlServiceTag) !== trimStr(ServiceTag)) {
    //            console.log("Other Pc");
    //            return;
    //        }

    //        if (Status === 'System Online' || JobStatus === 'System Offline' || JobStatus === '' || JobStatus === 'ADMessage') {
    //            return;
    //        }

    var val = Status.split("=");
    var cancelMachine = "'" + GlServiceTag + "'";
    //var val;
    var i, k;
    var liList = '<ul>';
    var len = val.length;
    if (Status.indexOf("Execution Completed") !== -1 || Status.indexOf("Execution Terminated") !== -1 || Status.indexOf("Execution Failed") !== -1) {
      k = 4;
    } else {
      for (i = 1; i < val.length - 1; i++) {

        liList += '<li id="config_pbar" class="clearfix"><div class="repair-name">' + val[i].replace(/[0-9]/g, '') + '</div>';
        //liList += '<li>' + val[i];
        var statusVal = val[i + 1].charAt(0);
        if (statusVal === '1' || statusVal === 1) {
          liList += '<div class="repair-status"><img id="cncl_progress" src="../vendors/images/loader2.gif" alt="" style="height: 20px;"></div>';
          //                    $(".resolutions-repair-box.customscroll").mCustomScrollbar({
          //                        theme: "minimal-dark"
          //                    });
        } else if (statusVal === '2' || statusVal === 2) {
          liList += '<div class="repair-status"><i class="tim-icons icon-check-2"></i></div>';
          //                    $(".resolutions-repair-box.customscroll").mCustomScrollbar({
          //                        theme: "minimal-dark"
          //                    });
        } else if (statusVal === '3' || statusVal === 3) {
          liList += '<div class="repair-status"><i class="tim-icons icon-alert-circle-exc"></i></div>';
          //                    liList += '<div class="repair-status"><img src="../vendors/images/loader2.gif" alt="" style="height: 20px;"></div>';
          //                    $(".resolutions-repair-box.customscroll").mCustomScrollbar({
          //                        theme: "minimal-dark"
          //                    });
        } else {
          liList += '<span class="repair-status"></span>';
        }

        liList += '</li>';
        laslist = liList;
        if (i === len - 2) {
          k = val[i + 1].charAt(0);
        }
      }
    }
    liList += '</ul>';
    //    liList += '';

    if (Status === 'Execution Completed' || Status === 'Execution Terminated' || Status === 'Execution Failed' || Status === 'System Offline') {
      laslist += '</ul>';
      //        laslist += '</div>';
      liList = laslist;
      if (Status === 'System Offline') {
        $.notify("The selected device is Offline, Please again once the device comes online");
        setTimeout(function() {
          rightContainerSlideClose('config_container');
        }, 1000);
        location.reload();
        CancelDart = 0;
        //                liList += '<span style="float:right;padding-right:10px;padding-bottom:5px;"><a style="margin-top:10px;cursor:default;padding:5px;color:black;">System went Offline.</a></span>';
      } else if (CancelDart === 0) {
        $.notify("The solution has been pushed succesfully");
        //liList += '<span style="float:right;padding-right:10px;padding-bottom:5px;"><a style="margin-top:10px;cursor:default;padding:5px;color:black;">Solution push has been completed successfully.</a></span>';
        setTimeout(function() {
          rightContainerSlideClose('config_container');
        }, 1000);
        location.reload();
      } else {
        $.notify("Solution push has been cancelled");
        //liList += '<span style="float:right;padding-right:10px;padding-bottom:5px;"><a style="margin-top:10px;cursor:default;padding:5px;color:black;">Present solution has been cancelled.</a></span>';
        setTimeout(function() {
          rightContainerSlideClose('config_container');
        }, 1000);
        CancelDart = 0;
      }

      $('#showdartStatus').hide();
      setTimeout(function() {
        $('#' + divId).fadeOut("slow", function() {
          $('#' + divId).html('');
          $('#' + divId).hide();
        });
        $("#showdartStatus").fadeOut("slow", function() {

        });
        //GlServiceTag = '';
      }, 5000);
    } else {
      if (CancelDart === 0) {
        //liList += '<div class="cancel_btn" id="cancelDiv"><a href="javascript:;" id="cancelbtn" onclick="cancelDart(' + cancelMachine + ')">Cancel</a></div>';
        liList += '<div class="button col-md-12 text-left" id="cancelDiv"><button class="swal2-confirm btn btn-success btn-sm cancel_btn" id="cancelbtn" aria-label="" onclick="cancelDart(' + cancelMachine + ')">Cancel</button></div>';
      } else {
        //liList += '<div class="cancel_btn"><a href="javascript:;" id="cancelbtn">Cancelling...<img src="../vendors/images/loader2.gif" alt="" style="height: 20px;"></a></div>';
        liList += '<button class="swal2-confirm btn btn-success btn-sm cancel_btn" id="cancelbtn" aria-label="">Cancelling...<img src="../vendors/images/loader2.gif" alt="" style="height: 20px;"></a></button>';
        $("#cncl_progress").hide();
      }
    }
    //    liList += '</div></div>';

    $("#" + divId).show();
    /*if (ShowProgress === 1) {
     $("#" + divId).show();
     } else {
     $("#" + divId).hide();
     }*/

    //LogToConsole(liList);
    $('.steps_content').css({
      'display': 'block'
    });
    $(".loadingStage").css({
      'display': 'none'
    });
    $('#' + divId).show();
    $('#' + divId).html(liList);
    $('#progressMainDiv').html(liList);
    $("#executeLoader").hide();
    $("#loader_pbar").hide();
    $("#progressMainDiv").show();
    // $(".resolutions-repair-box.customscroll").mCustomScrollbar({
    //     theme: "minimal-dark"
    // });
    // $(".resolutions-repair-box.customscroll").mCustomScrollbar({
    //     theme: "minimal-dark"
    // });
  }

  function cancelDart(hostname) {
    if (GlServiceTag !== '') {
      if (ws === '' || ws === undefined) {
        // Pending
        //                alert("Node is not Connected");
      } else {
        CancelDart = 1;
        var JobCancel = {};
        JobCancel['Type'] = 'CancelCurrentJob';
        JobCancel['ServiceTag'] = trimStr(GlServiceTag);
        ws.send(JSON.stringify(JobCancel));
        $('#cancelbtn').html('Cancelling...');
        $("#cncl_progress").hide();
        LogToConsole("Triggered Job Cancel for " + hostname + " : " + JSON.stringify(JobCancel));
      }
    } else {
      // John Show a message box here
      //            alert('No Jobs are running to cancel');
    }
  }

  function GCMJobs(msg) {
    var res = msg.split('---');
    var job = unescape(res[5]);
    var msgsend = res[4] + '###' + job;
    var gcmid = res[3];
    //msgsend = msgsend.replace(/CID/g, '942763bb6e34e9c3eae2cbd8ccb74526');
    //LogToConsole('Triggering : ' + msgsend);

    msgsend = escape(msgsend);
    //LogToConsole('Encoded : ' + msgsend);
    $.ajax({
      type: "POST",
      url: "../communication/gcm.php",
      data: "gcmid=" + gcmid + "&msg=" + msgsend,
      success: function(msg) {
        $(".loadingStage").css({
          'display': 'none'
        });
      }
    });
  }

  function setfixList(textvar, machineName, dart, pb) {
    $(".loadingStage").css({
      'display': 'block'
    });
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
        liList += '<span style="float:right;padding-right:10px;padding-bottom:5px;"><a style="margin-top:10px;cursor:default;padding:5px;color:grey;">Solution push has been cancelled</a></span>';
        cancelVal = 0;
      }

      $('#showdartStatus').hide();
      /* this is the code for hiding progress - Start */
      setTimeout(function() {
        $("#fixlist").fadeOut("slow", function() {
          $("#fixlist").html('');
          $("#progressMainDiv").hide();
        });
        $("#showdartStatus").fadeOut("slow", function() {

        });
      }, 5000);
      /* this is the code for hiding progress - End */
    } else {
      if (cancelVal === 0) {
        liList += '<div style="float:right;padding-right:10px;padding-bottom:5px;" id="cancelDiv"><a style="background-color:#5c54a4;cursor:pointer;padding:5px;color:#fff;" onclick="cancelDart(' + cancelMachine + ')" id="cancelbtn">Cancel</a></div>';
      } else {
        liList += '<div style="float:right;padding-right:10px;padding-bottom:5px;"><a style="background-color:#5c54a4;cursor:pointer;padding:5px;color:#fff;" id="cancelbtn">Cancelling...<img  src="../vendors/images/loader2.gif" alt="" style="height: 20px;"></a></div>';
        $("#cncl_progress").hide();
      }
    }
    liList += '</div></div>';
    if (pb === 1) {
      $("#fixlist").show();
    } else {
      $("#fixlist").hide();
    }

    $('.steps_content').css({
      'display': 'block'
    });
    $(".loadingStage").css({
      'display': 'none'
    });
    $('#fixlist').html(liList);
    $("#executeLoader").hide();
    $("#loader_pbar").hide();
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
    $(".loadingStage").css({
      'display': 'block'
    });
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

    setTimeout(function() {
      $("#fixlist").fadeOut("slow", function() {
        $("#fixlist").html('');
      });
      $("#showdartStatus").fadeOut("slow", function() {

      });
    }, time);
    $('.steps_content').css({
      'display': 'block'
    });
    $(".loadingStage").css({
      'display': 'none'
    });
    $('#fixlist').html(liList);
  }

  //    function cancelDart(machineName) {
  //
  //        if (wSocket === '' || wSocket === undefined) {
  //            connectToNodeServer(function () {
  //                showDartStaus();
  //            });
  //        }
  //
  //        $.ajax({
  //            type: "POST",
  //            url: "../communication/communication_ajax.php",
  //            data: "function=getCancelMachineDet&machineName=" + machineName,
  //            success: function (msg) {
  //                $('#cancelbtn').html('Cancelling...');
  //                cancelVal = 1;
  //                wSocket.emit('executeDart', 'dboard---cancel---siteid---' + msg.trim());
  //            },
  //            error: function () {
  //                $(".loadingStage").css({'display': 'none'});
  //                $("#rightNavtiles").html("<span style='color:#009933; font-size:12px;'>Not Executed</span><br />");
  //            }
  //        });
  //    }

  //Caption Sliding (Partially Hidden to Visible)
  function slideOpenDiv(id) {
    $('.cover_' + id).stop().animate({
      top: '10px'
    }, {
      queue: false,
      duration: 160
    });
  }

  function slideCloseDiv(id, top) {
    $('.cover_' + id).stop().animate({
      top: top + 'px'
    }, {
      queue: false,
      duration: 160
    });
  }

  function in_array(needle, haystack) {
    for (var key in haystack) {
      if (needle === haystack[key]) {
        return true;
      }
    }

    return false;
  }

  function pad(str, max) {
    str = str.toString();
    return str.length < max ? pad("0" + str, max) : str;
  }

  var configurationDetails = [];

  function executeDynamicConfigretion(sequence) {
    $("#loader").show();
    $.ajax({
      type: "POST",
      url: "../JSONSchema/jsonschema2.php",
      data: {
        data: sequence,
        'csrfMagicToken': csrfMagicToken
      },
      //        dataType:"json",
      async: false,
      //        data:"function=dynamicdata",
      success: function(data) {
        //alert("success");
        //eval(msg);
        rightContainerSlideOn('config-trbl-container');
        //              $(".loader").hide();
        $("#jsonModalDialogDivs").html(data);

      },
      error: function(msg) {
        //            alert(JSON.stringify(msg));
        //                alert("error");
      }
    });

  }


  function CreateJobAndExecute(dart, variable, shortDesc, Jobtype, ProfileName, thisVal, osystem) {
    // 2
    var GroupName = $("#valueSearch").val();
    // var os = $("#osTypeDropVal").val();
    // if (os == '' || os == null) {
    //   os = 'windows';
    // }
    const functionName = `Add_RemoteJobs`
    // if (os.toLowerCase() === 'windows') {
    //   params = {
    //     'function': functionName,
    //     'OS': 'windows',
    //     'Dart': dart,
    //     'variable': variable,
    //     'shortDesc': encodeURIComponent(shortDesc),
    //     'Jobtype': Jobtype,
    //     'ProfileName': encodeURIComponent(ProfileName),
    //     'NotificationWindow': '<?php echo $notifyWindow; ?>',
    //     'GroupName': GroupName,
    //     'ProfileOS': osystem
    //   }
    // } else if (os.toLowerCase() === 'android') {
    //   params = {
    //     'function': functionName,
    //     'OS': 'android',
    //     'Dart': dart,
    //     'variable': variable,
    //     'shortDesc': encodeURIComponent(shortDesc),
    //     'Jobtype': Jobtype,
    //     'ProfileName': encodeURIComponent(ProfileName),
    //     'NotificationWindow': '<?php echo $notifyWindow; ?>',
    //     'GroupName': GroupName,
    //     'ProfileOS': osystem
    //   }
    // } else if (os.toLowerCase() === 'mac') {
    //   params = {
    //     'function': functionName,
    //     'OS': 'os x',
    //     'Dart': dart,
    //     'variable': variable,
    //     'shortDesc': encodeURIComponent(shortDesc),
    //     'Jobtype': Jobtype,
    //     'ProfileName': encodeURIComponent(ProfileName),
    //     'NotificationWindow': '<?php echo $notifyWindow; ?>',
    //     'GroupName': GroupName,
    //     'ProfileOS': osystem
    //   }
    // } else if (os.toLowerCase() === 'linux') {
    //   params = {
    //     'function': functionName,
    //     'OS': 'linux',
    //     'Dart': dart,
    //     'variable': variable,
    //     'shortDesc': encodeURIComponent(shortDesc),
    //     'Jobtype': Jobtype,
    //     'ProfileName': encodeURIComponent(ProfileName),
    //     'NotificationWindow': '<?php echo $notifyWindow; ?>',
    //     'GroupName': GroupName,
    //     'ProfileOS': osystem
    //   }
    // } else if (os.toLowerCase() === 'ios') {
    //   params = {
    //     'function': functionName,
    //     'OS': 'ios',
    //     'Dart': dart,
    //     'variable': variable,
    //     'shortDesc': encodeURIComponent(shortDesc),
    //     'Jobtype': Jobtype,
    //     'ProfileName': encodeURIComponent(ProfileName),
    //     'NotificationWindow': '<?php echo $notifyWindow; ?>',
    //     'GroupName': GroupName,
    //     'ProfileOS': osystem
    //   }
    // } else if (os.toLowerCase() === 'readynet router') {
    //   params = {
    //     'function': functionName,
    //     'OS': 'RLinux',
    //     'Dart': dart,
    //     'variable': variable,
    //     'shortDesc': encodeURIComponent(shortDesc),
    //     'Jobtype': Jobtype,
    //     'ProfileName': encodeURIComponent(ProfileName),
    //     'NotificationWindow': '<?php echo $notifyWindow; ?>',
    //     'GroupName': GroupName,
    //     'ProfileOS': osystem
    //   }
    // } else if (os.toLowerCase() === 'unknow') {
    const name = $('[data-qa=selected-site-name]').attr("title");
    const type = $('#searchType').val();
    params = {
      'function': functionName,
      'OS': 'unknow',
      'type': type,
      'name': name,
      'Dart': dart,
      'variable': variable,
      'shortDesc': encodeURIComponent(shortDesc),
      'Jobtype': Jobtype,
      'ProfileName': encodeURIComponent(ProfileName),
      'NotificationWindow': '<?php echo $notifyWindow; ?>',
      'GroupName': GroupName,
      'ProfileOS': osystem,
      csrfMagicToken
    }
    // }
    // params.csrfMagicToken = csrfMagicToken;
    $("#executeLoader").show();
    $("#loader_pbar").show();
    $("#executeJob").hide();
    $.ajax({
      type: "POST",
      url: "../communication/communication_ajax.php",
      data: params,
      success: function(data) {
        $("#loader_pbar").hide();
        $("#executeLoader").hide();
        if (data !== "error") {
          var result = data.split("##");
          var SupportedMachines = result[0];
          var NonSupportedMachines = result[1];
          var ShowProgressServiceTag = result[3];
          //var TriggerOn = result[4];
          var OnlineOffline = result[5];
          var AuditStatus = result[6];
          debugger;
          if (AuditStatus == 'Duplicates' && SupportedMachines != '') {
            $('#progressMainDiv').html('The solution chosen is already queued for few of the selected machines. Progress will be displayed only at Machine Level');
            return false;
          } else if (AuditStatus == 'Duplicates') {
            $('#progressMainDiv').html('The solution chosen is already queued for the selected machine.');
            return false;
          }
          var StatusMessage = '';
          if (ShowProgressServiceTag !== '') {
            if (OnlineOffline !== 'Online') {
              StatusMessage = "Solution pending";
            } else {
              StatusMessage = "Solution pushed";
            }
          } else if (SupportedMachines === '') {
            StatusMessage = "Solutions pushed";
          } else {
            StatusMessage = "Solutions pushed";
          }
          if ($.trim(SupportedMachines) == 'NotAvaiableForSite') {
            $.notify("The solution chosen, is not compatible for site level");
          }
          if (SupportedMachines !== '') {
            // if (os.toLowerCase() === 'windows' || os.toLowerCase() === 'mac' || os.toLowerCase() === 'linux' || os.toLowerCase() === 'readynet router') {

            if (variable.indexOf("_UserTrigger") !== -1 || variable.indexOf("_UserSurvey") !== -1) {
              $.notify('Solution triggered successfully and is queued for end user action');
              rightContainerSlideClose('config_container');
            }

            EmitJobsForServiceTags(SupportedMachines, ShowProgressServiceTag);

            // } else if (os.toLowerCase() === 'android') {

            // }
            var textVal = $(thisVal).text();
            if (textVal === 'Run the Troubleshooter') {
              $(thisVal).text(StatusMessage).prop('onclick', null).off('click').css('cursor', 'not-allowed');
            }
            /*setTimeout(function () {
             $(thisVal).text('Run now').attr("disabled", false);
             }, 3000);*/
          }
          if (NonSupportedMachines !== '') {
            // NonSupportedMachines (John Add Message Box Here)
            var maclist = NonSupportedMachines.toString();
            maclist = maclist.replace(/~~/g, "<br/>");

            //alert(NonSupportedMachines); 
            $.notify("The solution chosen, <b>" + shortDesc + "</b> is not compatible with the OS of the following devices: <br/><br/>" + maclist);
          }
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
    watchID = navigator.geolocation.watchPosition(function(position) {

        if (loaded === 1) {
          return;
        }
        var path = [];
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
            title: "Location No: " + (i + 1) + "\n " //Date: 22/10/2010
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
      function(positionError) {
        //$("#error").append("Error: " + positionError.message + "<br />");
      }, {
        //enableHighAccuracy: false,
        //timeout: 3000 * 1000 // 10 seconds
      });
  }

  function NoceventDetailsForDartStatA(stat, tid, eventList) {

    $("#rightNavtiles").css({
      'display': 'none'
    });
    if (tid !== '') {
      jQuery_1_7.nyroModalManual({
        debug: false,
        width: 1000, // default Width If null, will be calculate automatically
        height: 1600,
        bgColor: '#333',
        url: '../notification/event_detailsForDartStatAudit.php?eid=' + tid + '&level=&site=&name=&id=&stat=' + stat + '&eventList=' + eventList,
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
      $("#rightNavtiles").css({
        'display': 'block'
      });
      $("#rightNavtiles").html('<div class="panel panel-danger"><div class="panel-heading"><h3 class="panel-title"><i class="fa fa-warning"></i>&nbsp;&nbsp;Alert<i class="fa fa-remove nyroModalClose pull-right closeThis"></i></h3></div><div class="panel-body"><div class="verticalSpace10"></div><span id="errTxt">No eventIdx for this machine.</div></div>');
    }
  }

  function getOsDetails() {
    $.ajax({
      type: "POST",
      url: "../communication/communication_ajax.php",
      data: {
        'function': 'get_MachineOS1',
        'csrfMagicToken': csrfMagicToken,
        searchType: '<?php echo $_SESSION["searchType"]; ?>',
        searchValue: '<?php echo $_SESSION["searchValue"]; ?>',
      },
      success: function(data) {
        var res = JSON.parse(data);
        var OS = res.OperatingSystem;
        var Ititle = res.selectiontype;
        $('#h-title').html('Interactive - ' + Ititle);
        if (OS.toLowerCase().indexOf("win") !== -1) {

          OSName = "Windows";
          OSDB = "Windows";
          OSSUB = 'NA';
          var stype = '<?php echo $_SESSION["searchType"]; ?>';
          if (stype === 'Service Tag' || stype === 'Host Name') {
            if ((OS.indexOf("Windows 10") !== -1) || (OS.indexOf("windows 10") !== -1) || (OS.indexOf("windows10") !== -1) || (OS.indexOf("Windows10") !== -1)) {
              OSSUB = "10";
            } else if ((OS.indexOf("Windows 8.1") !== -1) || (OS.indexOf("windows 8.1") !== -1) || (OS.indexOf("windows8.1") !== -1) || (OS.indexOf("Windows8.1") !== -1)) {
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
          $("#osTypeDropVal").val('Windows');
        } else if (OS.toLowerCase().indexOf("android") !== -1) {
          OSName = "Android";
          OSDB = "Android";
          $("#osTypeDropVal").val('Android');
        } else if (OS.toLowerCase().indexOf("mac") !== -1) {
          OSName = "Mac";
          OSDB = "Mac";
          $("#osTypeDropVal").val('Mac');
        } else if (OS.toLowerCase().indexOf("linux") !== -1) {
          OSName = "Linux";
          OSDB = "Linux";
          $("#osTypeDropVal").val('Linux');
        } else {
          OSName = "unknow";
          OSDB = "7";
          $("#osTypeDropVal").val('unknow');
        }
        //tileHome('1');
      }
    });
  }

  function getOSLevelTilesMachineLvl() {
    $.ajax({
      type: "POST",
      url: "../communication/communication_ajax.php",
      data: {
        'function': 'get_MachineOS1',
        'csrfMagicToken': csrfMagicToken,
        searchType: '<?php echo $_SESSION["searchType"]; ?>',
        searchValue: '<?php echo $_SESSION["searchValue"]; ?>',
      },
      success: function(data) {
        var res = JSON.parse(data);
        var OS = res.OperatingSystem;
        var Ititle = res.selectiontype;
        $('#h-title').html('Troubleshooting - ' + Ititle);
        if (OS.toLowerCase().indexOf("win") !== -1) {
          $("#androidIcon,#macIcon,#iosIcon,#linuxIcon").hide();
          $("#imgDrop").attr('src', '../vendors/images/windows.png');
          $("#windowsIcon").show();
          //                    $("#androidIcon,#macIcon,#iosIcon,#linuxIcon").removeClass("is-selected");
          //                    $("#windowsIcon").css("background-color", "transparent");
          getOSLevelTiles('windows');
        } else if (OS.toLowerCase().indexOf("android") !== -1) {
          $("#windowsIcon,#macIcon,#iosIcon,#linuxIcon").hide();
          $("#imgDrop").attr('src', '../vendors/images/android.png');
          $("#androidIcon").show();
          //                    $("#windowsIcon,#macIcon,#iosIcon,#linuxIcon").removeClass("is-selected");
          //                    $("#androidIcon").css("background-color", "transparent");
          getOSLevelTiles('android');
        } else if (OS.toLowerCase().indexOf("os x") !== -1 || OS === "OS X") {
          $("#windowsIcon,#androidIcon,#iosIcon,#linuxIcon").hide();
          $("#imgDrop").attr('src', '../vendors/images/mac-black.png');
          $("#macIcon").show();
          //                    $("#androidIcon,#windowsIcon,#iosIcon,#linuxIcon").removeClass("is-selected");
          //                    $("#macIcon").css("background-color", "transparent");
          getOSLevelTiles('mac');
        } else if (OS.toLowerCase().indexOf("linux") !== -1) {
          $("#windowsIcon,#androidIcon,#macIcon,#iosIcon").hide();
          $("#imgDrop").attr('src', '../vendors/images/linux-black.png');
          $("#linuxIcon").show();
          //                    $("#androidIcon,#macIcon,#iosIcon,#windowsIcon").removeClass("is-selected");
          //                    $("#linuxIcon").css("background-color", "transparent");
          getOSLevelTiles('linux');
        } else if (OS.toLowerCase().indexOf("ios") !== -1) {
          $("#windowsIcon,#androidIcon,#macIcon,#linuxIcon").hide();
          $("#imgDrop").attr('src', '../vendors/images/ios.png');
          $("#iosIcon").show();
          //                    $("#androidIcon,#macIcon,#windowsIcon,#linuxIcon").removeClass("is-selected");
          //                    $("#iosIcon").css("background-color", "transparent");
          getOSLevelTiles('ios');
        } else {
          $("#windowsIcon,#androidIcon,#macIcon,#iosIcon,#linuxIcon").show();
          $("#imgDrop").attr('src', '../vendors/images/windows.png');
          $("#windowsIcon").show();
          //                    $("#androidIcon,#macIcon,#iosIcon,#linuxIcon").removeClass("is-selected");
          //                    $("#windowsIcon").css("background-color", "transparent");
          getOSLevelTiles('windows');
        }
      }
    });
  }

  var OsLevelTile = false;

  function getOSLevelTiles(OSType) {
    // if(OsLevelTile){
    //     return;
    // }
    OsLevelTile = true;
    $(".tableloader").show();
    $.ajax({
      type: "POST",
      url: "../communication/communication_ajax.php",
      data: {
        'function': 'get_MachineOS1',
        'csrfMagicToken': csrfMagicToken,
        searchType: '<?php echo $_SESSION["searchType"]; ?>',
        searchValue: '<?php echo $_SESSION["searchValue"]; ?>',
      },
      success: function(data) {
        $(".tableloader").hide();
        var res = JSON.parse(data);
        var OS = res.OperatingSystem;
        var Ititle = res.selectiontype;
        $('#h-title').html('Interactive - ' + Ititle);
        if (OS.toLowerCase().indexOf("win") !== -1) {
          OSName = "Windows";
          OSDB = "Windows";
          OSSUB = 'NA';
          var stype = '<?php echo $_SESSION["searchType"]; ?>';
          var fromwindow = '<?php echo $_SESSION["fromwindow"]; ?>';
          if (stype === 'Service Tag' || stype === 'Host Name' || stype === 'ServiceTag') {
            if ((OS.indexOf("Windows 10") !== -1) || (OS.indexOf("windows 10") !== -1) || (OS.indexOf("windows10") !== -1) || (OS.indexOf("Windows10") !== -1)) {
              OSSUB = "10";
            } else if ((OS.indexOf("Windows 8.1") !== -1) || (OS.indexOf("windows 8.1") !== -1) || (OS.indexOf("windows8.1") !== -1) || (OS.indexOf("Windows8.1") !== -1)) {
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
          $("#osTypeDropVal").val('Windows');
        } else if (OS === 'android') {
          OSName = "Android";
          OSDB = "Android";
          $("#osTypeDropVal").val('Android');
        } else if (OS.toLowerCase().indexOf("os x") !== -1 || OS === "OS X") {
          OSName = "Mac";
          OSDB = "Mac";
          $("#osTypeDropVal").val('Mac');
        } else if (OS === 'linux') {
          OSName = "Linux";
          OSDB = "Linux";
          $("#osTypeDropVal").val('Linux');
        } else {
          OSName = "unknow";
          OSDB = "7";
          $("#osTypeDropVal").val('unknow');
        }
        if (fromwindow == 'Notify') {
          tileHome('1', 'notify');
        } else {
          tileHome('1', 'main');
        }

      }
    });
  }

  function tileHomeNotif(pageId) {
    var check = 'true';
    if (pageId === '2' || pageId === 2 || pageId === '1' || pageId === 1) {
      $(".backBtn").hide();
    } else {
      $(".backBtn").show();
    }

    $("#ExceCancelButton").html('');
    $.ajax({
      type: "POST",
      url: "../communication/communication_ajax.php",
      data: {
        'function': 'profile_Data',
        'csrfMagicToken': csrfMagicToken,
        'os': OSDB,
        'pageId': pageId,
        'ossub': OSSUB,
        level: check
      },
      success: function(msgVal) {
        $(".tableloader").hide();
        msg = $.trim(msgVal);
        var liMsg = msg.split('##');
        $("#toolboxList").html(liMsg[0]);
        $("#backParentId").val(liMsg[1]);
        glmenuitem = liMsg[2];
        var ptitle = liMsg[3];
        if (ptitle.length > 22) {
          var parentTitle = ptitle.substring(0, 22);
          ptitle = parentTitle + '..';
        }
        $('#parentTitle').text(ptitle);
        $('#parentTitle').attr("title", liMsg[3]);
        //                $("#mainParentTitle").html(liMsg[3]);
        $("#mainParentTitle").html("Troubleshooting");
        $("#parentDesc").html(liMsg[4]);
        $('#notif_interactive').show();
        $('#notif_details').hide();
        $('.equalHeight').matchHeight('remove');
        $('.equalHeight').matchHeight();
      }
    });
  }

  function tileHome(pageId, check) {

    // $('#showSiteMsg').html('');
    // $("#toolboxList").html('');
    // $("#mainParentTitle").html("");
    // $("#parentDesc").html("");
    if (pageId === '2' || pageId === 2 || pageId === '1' || pageId === 1) {
      $(".backBtn").hide();
    } else {
      $(".backBtn").show();
    }
    var search = $('#passlevel').val();
    $("#ExceCancelButton").html('');
    $.ajax({
      type: "POST",
      url: "../communication/communication_ajax.php",
      data: {
        'function': 'profile_Data',
        'csrfMagicToken': csrfMagicToken,
        'os': OSDB,
        'pageId': pageId,
        'ossub': OSSUB,
        'level': check,
        'search': search
      },
      success: function(msgVal) {
        $('.equalHeight').show();
        $('#absoLoader').hide();
        $(".tableloader").hide();
        msg = $.trim(msgVal);
        var liMsg = msg.split('##');
        if (liMsg[1] === undefined || liMsg[1] === 'undefined') {
          $("#parentDesc").html('');
          $('#showSiteMsg').show();
          $('#listDiv').hide();
          $('#showSiteMsg').html('<img src="../assets/img/click.svg">').css({
            'margin-left': '30%'
          });
          $('#showSiteMsg').append('<div style="margin-left: 5%">' + msg + '</div>');
          $("#toolboxList").html(liMsg[0]).hide();
        } else {
          $("#toolboxList").show();
          $('#showSiteMsg').hide();
          $('#listDiv').hide();
          $("#toolboxList").html(liMsg[0]);
          $("#backParentId").val(liMsg[1]);
          glmenuitem = liMsg[2];
          var ptitle = liMsg[3];
          if (ptitle.length > 22) {
            var parentTitle = ptitle.substring(0, 22);
            ptitle = parentTitle + '..';
          }
          $('#parentTitle').text(ptitle);
          $('#parentTitle').attr("title", liMsg[3]);
          $("#mainParentTitle").html("Troubleshooting");
          $("#parentDesc").html(liMsg[4]);
          $('#toolboxList li a').eq(0).click();
        }
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
      type: "POST",
      url: "../communication/communication_ajax.php",
      data: {
        function: 'advprofileData',
        os: OSDB,
        pageId: parentId,
        backId: backPageId,
        menuitem: menuitem,
        'ossub': OSSUB,
        'csrfMagicToken': csrfMagicToken
      },
      success: function(msgVal) {
        msg = $.trim(msgVal);
        var liMsg = msg.split('##');
        $("#advtoolboxList").html(liMsg[0]);
        $("#backParentId").val(liMsg[1]);
        glmenuitem = liMsg[2];
        var ptitle = liMsg[3];
        if (ptitle.length > 22) {
          var parentTitle = ptitle.substring(0, 22);
          ptitle = parentTitle + '..';
        }
        $('#parentTitle').text(ptitle);
        $('#parentTitle').attr("title", liMsg[3]);
        $("#mainParentTitle").html('Advance Troubleshooters');
        $("#parentDesc").html('Here you will find a variety of tools that are meant for trained technicians. Although you can see the descriptions, only one of our agents can run these tools');
      }
    });
  }

  var isEditProfile = false;
  var isDeleteProfile = false;

  function clickl1level(thisVal, parentId, profile, dart, variable, varValue, description, page, menuitem, mid, level) {
    Tile_type = 'L1';
    $("#listDiv").hide();
    $("#absLoader").show();
    $("#clickList").html("");

    if (page === '1' || page === 1) {
      $(".backBtn").hide();
    } else {
      $(".backBtn").show();
    }
    srchpgid = parentId;
    $('#toolboxList a').css({
      'background-color': 'white',
      'color': '#595959'
    });
    $(thisVal).css({
      'background-color': '#f5f6fa',
      'color': 'white'
    });
    var backPageId = $("#backParentId").val();
    $("#ExceCancelButton").html('');
    $.ajax({
      type: "POST",
      url: "../communication/communication_ajax.php",
      data: {
        'function': 'profile_DataList',
        os: OSDB,
        pageId: parentId,
        backId: backPageId,
        menuitem: menuitem,
        ossub: OSSUB,
        level: level,
        'csrfMagicToken': csrfMagicToken
      },
      success: function(msgVal) {
        $("#absLoader").hide();
        $("#listDiv").fadeIn(100);
        msg = $.trim(msgVal);
        var liMsg = msg.split('##');
        $("#clickList").html(liMsg[0]);
        $("#backParentId").val(liMsg[1]);
        glmenuitem = liMsg[2];
        var ptitle = liMsg[3];
        if (ptitle.length > 22) {
          var parentTitle = ptitle.substring(0, 22);
          ptitle = parentTitle + '..';
        }

        $('#parentTitle').text(ptitle);
        $('#parentTitle').attr("title", liMsg[3]);
        $("#mainParentTitle").html(liMsg[3]);
        $("#mainParentTitle").attr("title", liMsg[3]);
        $("#parentDesc").html(liMsg[4]);
        $("#clickl1level").html(liMsg[4]);
        $("#titleStore").val(liMsg[4]);
        toggelEditButton();
      }
    });
  }

  function onEditTileClick(json, level) {
    //        alert(level);
    $('#config-trbl-container').hide();
    var dataArr = json.split("*****");
    var objArr = {};
    rightContainerSlideOn('edit_profile');
    var editprofile_htmlcontent = "";
    if (level == 'edit') {
      for (var i = 0; i <= dataArr.length - 1; i++) {
        var str = dataArr[i];
        var jsondata = str.split("#:#");
        var key = jsondata[0];
        var value = jsondata[1];
        objArr[key] = value;

        var id_name = key.toLowerCase();
        var profile_name = id_name.replace('/', '');
        editprofile_htmlcontent += '<div class="form-group has-label">' +
          ' <em class="error" id="required_prof_edit_' + profile_name + '">*</em>' +
          ' <label id="title_' + profile_name + '">' + key + '</label>' +
          ' <input type="hidden" id="hidden_id" value="' + profile_name + '">' +
          ' <input key="' + key + '" class="form-control editTroubleshooterTileDetails" name="prof_' + profile_name + '" value="' + value + '" id="prof_edit_' + profile_name + '" />' +
          '</div>';

      }
      $("#edit_profile_page").html(editprofile_htmlcontent);
    } else {
      for (var i = 0; i <= dataArr.length - 1; i++) {
        var str = dataArr[i];
        var jsondata = str.split("#:#");
        var key = jsondata[0];
        var value = jsondata[1];
        objArr[key] = value;

        var id_name = key.toLowerCase();
        var profile_name = id_name.replace('/', '');
        if (profile_name == 'enabledisable') {
          editprofile_htmlcontent += '<div class="form-group has-label">' +
            ' <em class="error" id="required_prof_edit_' + profile_name + '">*</em>' +
            ' <label id="title_' + profile_name + '">' + key + '</label>' +
            ' <input type="hidden" id="hidden_id" value="' + profile_name + '">' +
            ' <input key="' + key + '" class="form-control editTroubleshooterTileDetails" name="prof_' + profile_name + '" value="' + value + '" id="prof_edit_' + profile_name + '" />' +
            '</div>';
        } else {
          editprofile_htmlcontent += '<div class="form-group has-label">' +
            ' <em class="error" id="required_prof_edit_' + profile_name + '">*</em>' +
            ' <label id="title_' + profile_name + '">' + key + '</label>' +
            ' <input type="hidden" id="hidden_id" value="' + profile_name + '">' +
            ' <input key="' + key + '" class="form-control editTroubleshooterTileDetails" name="prof_' + profile_name + '" value="' + value + '" id="prof_edit_' + profile_name + '" readonly/>' +
            '</div>';
        }


      }
      $("#edit_profile_page").html(editprofile_htmlcontent);
    }


  }

  function clickcheck(thisVal, mid, val) {
    Tile_type = 'L3';
    $('.hidden_mid').removeClass('selected');
    $('.hidden_status').removeClass('selected');
    $('.expand a').css({
      'background-color': 'white',
      'color': '#595959'
    });
    $(thisVal).css({
      'background-color': '#f5f6fa',
      'color': 'white'
    });
    if ($('ul').hasClass('expand')) {
      $(':input').filter(function() {
        return this.value == mid
      }).addClass('selected');
      $(':input').filter(function() {
        return this.value == val
      }).addClass('selected');
    }
  }

  function clickl12level(thisVal, parentId, profile, dart, variable, varValue, description, page, menuitem, divId, listId, dynamic, sequence, os, level) {
    Tile_type = 'L2';
    $(".tableloader").show();
    var haveData = $("#" + listId).html();
    $('[id^=morelist]').html('');
    if (haveData != '') {
      return false;
    }
    srchpgid = parentId;
    var backPageId = $("#backParentId").val();
    $("#" + listId).html('<div style="margin-left:35px; padding: 10px;">Loading...</div>');
    $('#clickList a').removeClass('active');
    $(thisVal).addClass('active');

    $.ajax({
      type: "POST",
      url: "../communication/communication_ajax.php",
      data: {
        function: 'profile_DataList',
        os: OSDB,
        pageId: parentId,
        backId: backPageId,
        menuitem: menuitem,
        ossub: OSSUB,
        level: level,
        'csrfMagicToken': csrfMagicToken
      },
      success: function(msgVal) {
        $(".tableloader").hide();
        $("#maximize" + parentId).hide();
        $("#minimize" + parentId).show();
        $("#togglebtn").show();

        msg = $.trim(msgVal);
        var liMsg = msg.split('##');
        $("#" + listId).html(liMsg[0]);
        $("#" + listId).show();

        $("#" + listId).css({
          'height': '5px !important'
        });
        $("#" + listId).addClass("expand");
        toggelEditButton();
      }
    });
  }

  function clickl12levelmin(listID) {
    $("#morelist" + listID).html("");
    $("#maximize" + listID).show();
    $("#minimize" + listID).hide();
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
  //        type: "POST",
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

  // function getOS(type) {

  //   const siteName = $('[data-qa=selected-site-name]').attr("title");

  //   params = {
  //     'function': 'OS_info',
  //     'siteName': siteName,
  //     'type': type,
  //     'csrfMagicToken': csrfMagicToken
  //   }

  //   return Promise.resolve($.ajax({
  //     type: "POST",
  //     url: "../communication/communication_ajax.php",
  //     data: params,
  //   }));
  // }

  function clickl3level(thisVal, parentId, profile, dart, variable, varValue, description, page, menuitem, divId, listId, dynamic, sequence, os) {
    var textVal = $(thisVal).text();
    var Type = $('#searchType').val();

    sweetAlert({
      title: 'Confirm action',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#050d30',
      cancelButtonColor: '#fa0f4b',
      cancelButtonText: 'No, cancel it!',
      confirmButtonText: 'Confirm',
    }).then(() => {

      $('#selectedPrfoleName').val(variable);
      $("#divId").val(divId);

      if (dynamic == 0) {
        if (Type != 'Groups') {
          rightContainerSlideOn('config_container');
          $("#slider_title").html(profile + " " + "Progress Bar");
          $("#progressMainDiv").html('');
          $("#loader_pbar").show();
          CreateJobAndExecute(dart, variable, varValue, 'Interactive', profile, thisVal, os);
        } else {
          CreateJobAndExecute(dart, variable, varValue, 'Interactive', profile, thisVal, os);
          setTimeout(function() {
            $.notify("Solution has been pushed. They would be executed on the devices as they come online");
          }, 2000);
        }
      } else {
        executeDynamicConfigretion(sequence);
        $("#slider-title").html(profile);
      }
      /*if (page === '1' || page === 1) {
       $(".backBtn").hide();
       } else {
       $(".backBtn").show();
       }
       $("#mainParentTitle").html(profile);
       $("#parentDesc").html(decodeURIComponent(description.replace(/\+/g, '%20')));
       var functionToBeCalled = "CreateJobAndExecute('" + dart + "','" + variable + "','" + varValue + "','Interactive','" + profile + "')";
       $('#ExceCancelButton').html('<a href="#" class="button button--inline bkg-primary" onclick="' + functionToBeCalled + '" id="executeJob" style="color:white;">Run</a>');
       */
    });
  }

  function callDart(varValue, dart, variable) {

    var machname = "'" + glMachineName + "'";
    $('#ExceCancelButton').html('<button class="btn" onclick="cancelDart(' + machname + ')" id="systemAnalysis">Cancel</button>');
    var dartConf = dart + '-' + varValue;
    $.ajax({
      type: "POST",
      url: "../includes/notification_ajax.php",
      data: {
        'function': 'doActionOnDetails',
        selectedRow: OSDB,
        dartnum: dart,
        dartCofg: dartConf,
        machine: glMachineName,
        'csrfMagicToken': csrfMagicToken
      },
      success: function(msgVal) {
        msg = $.trim(msgVal);
        if (msg === 1) {
          emitDart('1', glMachineName, dart);
        }
      }
    });
  }

  function callBackButton() {
    var parntId = $("#backParentId").val();
    var fromwindow = '<?php echo $_SESSION["fromwindow"]; ?>';
    //$("#ExceCancelButton").html('');
    if (fromwindow == 'Notify') {
      tileHome('1', 'notify');
    } else {
      tileHome('1', 'main');
    }
    //        tileHome('1');
    $("#clickList").html("");
  }


  function autoSolnDart(nid) {

    $.ajax({
      type: "POST",
      url: "../communication/communication_ajax.php",
      data: {
        function: 'getAutoSolnPush',
        nid: nid,
        csrfMagicToken: csrfMagicToken

      },
      success: function(msg) {
        msg = $.trim(msg);

        if (msg !== '') {
          $("#solnSearch").val(msg);
          searchL3Profile(msg);
        }

      },
      error: function() {

      }
    });
  }
</script>