<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
include_once '../lib/l-dbConnect.php';
include_once '../includes/common_functions.php';
include_once '../lib/l-db.php';
include_once '../lib/l-sql.php';
include_once '../lib/l-gsql.php';
include_once '../lib/l-rcmd.php';
include_once('../lib/l-util.php');

$userName  = url::postToText('name');
$domain    = url::postToText('dom');
$searchVal = url::postToText('searchValue');
$socketId  = url::postToText('socketId');
$userId    = 1;

$wgORd = 1;
$uName = "";
if ($domain == "NULL" || $domain == NULL) {
    $wgORd = 2;
    $uName = "mainuser";
} else {
    $wgORd = 1;
    $uName = "administrator";
}
if ($userName != "") {
    $uName = $userName;
}
$db = pdo_connect();

$confsql = $db->prepare("select * from " . $GLOBALS['PREFIX'] . "core.Options where name = 'dashboard_config' limit 1");
$confsql->execute();
$confres = $confsql->fetch();
$confdata = safe_json_decode($confres['value'], true);
$wsurl = $confdata['wsurl'];
?>

<div class="security-questions">
    <form>
        <h3>Security Questions</h3>
        <span id="perr" style="color: red;"></span>
        <div class="form-group is-empty clearfix row">
            <label for="q1" class="col-sm-3 align-label">Question 1</label>
            <div class="col-sm-9">
                <input class="form-control" type="text" id="q1" name="q1">
            </div>
        </div>
        <div class="form-group is-empty clearfix row">
            <label for="q2" class="col-sm-3 align-label">Question 2</label>
            <div class="col-sm-9">
                <input class="form-control" type="text" id="q2" name="q2">
            </div>
        </div>
        <div class="form-group is-empty clearfix row">
            <label for="q3" class="col-sm-3 align-label">Question 3</label>
            <div class="col-sm-9">
                <input class="form-control" type="text" id="q3" name="q3">
            </div>
        </div>
        <div class="form-group is-empty clearfix row">
            <label for="q4" class="col-sm-3 align-label">Question 4</label>
            <div class="col-sm-9">
                <input class="form-control" type="text" id="q4" name="q4">
            </div>
        </div>
        <div class="form-group is-empty clearfix row">
            <label for="q5" class="col-sm-3 align-label">Question 5</label>
            <div class="col-sm-9">
                <input class="form-control" type="text" id="q5" name="q5">
            </div>
        </div>
        <input type="hidden" name="username" id="username" />
        <input type="hidden" name="dom" id="dom" />
        <input type="hidden" name="authenticate" value="authenticate" />
        <div class="change-profile-footer align-left">
            <button type="button" class="add-user-add-btn" id="chck">Submit</button>
            <button type="button" class="add-user-cancel-btn" data-dismiss="modal" id="cancl">Cancel</button>
            <span class="dataloaderimg2" style="display:none"><img src="../vendors/images/loader2.gif" /></span>
        </div>
    </form>
</div>

<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>-->
<script type="text/javascript">
    /* global wSocket */
    /*socket connection */
    var ws = '';
    $(document).ready(function() {
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
        var wsurl = '<?php echo $wsurl; ?>';
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
            ws.onopen = function() {
                LogToConsole('Connecting to Communication Server Success');
                var ConnectData = {};
                ConnectData['Type'] = 'Dashboard';
                ConnectData['AgentId'] = '<?php echo $agentUniqId; ?>';
                ConnectData['AgentName'] = '<?php echo $agentName . "-AD"; ?>';
                ConnectData['ReportingURL'] = reportingurl;
                ws.send(JSON.stringify(ConnectData));
            };
            ws.onmessage = function(JobStatus) {
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
                                //                                        $('#status_success').fadeIn();
                                $('#status_success').html('Successfully unlocked the account.');
                                //                                        $('#status_success').fadeOut(5000);
                            }
                            if (dartnum === '2') {
                                //                                        $('#status_success').fadeIn();
                                $('#status_success').html('Successfully changed the user password.');
                                //                                        $('#status_success').fadeOut(5000);
                            }
                            if (dartnum === '4') {
                                //                                        $('#status_success').fadeIn();
                                $('#status_success').html('Security answers reset successfully.');
                                //                                        $('#status_success').fadeOut(5000);
                            }
                            if (dartnum === '1') {
                                //                                        $('#status_success').fadeIn();
                                $('#status_success').html('Successfully configured the security questions.');
                                //                                        $('#status_success').fadeOut(5000);
                            }
                        } else {
                            if ($.isNumeric(successstatus)) {
                                if (successstatus === '5') {
                                    $('.rightside').hide();
                                    $('#statuserr').hide();
                                    $('#statussuc').show();
                                    $('#status_success').html('Successfully changed the user password. <br/>User need to change password at next login.');
                                } else if (dartnum === '4' || dartnum === '3') {
                                    var success = successStatusSet(successstatus);
                                    $('.rightside').hide();
                                    $('#statuserr').show();
                                    $('#statussuc').hide();
                                    $('#status_success').html(success);

                                } else {
                                    var success = successStatusSet(successstatus);
                                    $('.rightside').hide();
                                    $('#statuserr').show();
                                    $('#statussuc').hide();
                                    $('.dataloaderimg2').hide();
                                    $('#cancel,#resetPass').show();
                                    $('#status_success').html(success);

                                    //                                            $('#procRow').show();
                                    //                                            $('fieldset').removeAttr("disabled");
                                    //                                            $("#Change_Pass").hide();
                                    //                                            $("#security_ques").hide();
                                    //                                            $("#Default").show();
                                }
                            } else {
                                var success = successStatusSet(successstatus);
                                $('.rightside').hide();
                                $('#statuserr').show();
                                $('#statussuc').hide();
                                $('.dataloaderimg2').hide();
                                $('#cancel,#resetPass').show();
                                //                                        $('#procRow').show();
                                //                                        $('fieldset').removeAttr("disabled");
                                //                                        $("#Change_Pass").hide();
                                //                                        $("#security_ques").hide();
                                //                                        $("#Default").show();
                                $('#status_success').html(success);

                            }
                        }
                    }
                }
            };
            ws.onclose = function() {
                setTimeout(function() {
                    wsconnect(wsurl);
                }, 2000);
            };
        }
    });

    function trimStr(str) {
        return str.replace(/^\s+|\s+$/g, '');
    }

    function ExecuteDirectJob(Servicetag, DirectJob) {
        //DirectJob = Math.round(new Date().getTime()/1000) + '###' + DirectJob;
        var JobData = {};
        JobData['Type'] = 'ExecuteDirectJob';
        JobData['ServiceTag'] = trimStr(Servicetag);
        JobData['DirectJob'] = 'InstantExecution---' + DirectJob;
        LogToConsole('DirectJob : ' + DirectJob);
        ws.send(JSON.stringify(JobData));
    }

    function LogToConsole(Msg) {
      console.log(Msg);
    }
    //-----submit--------------------------------------
    $('#chck').click(function() {
        var userName = $('#userName').val();
        var domain = $('#domain').val();
        var q1 = $('#q1').val();
        var q2 = $('#q2').val();
        var q3 = $('#q3').val();
        var q4 = $('#q4').val();
        var q5 = $('#q5').val();
        sessionStorage.questions = q1 + '#' + q2 + '#' + q3 + '#' + q4 + '#' + q5;
        if (q1 === '' || /^[a-zA-Z0-9 ]*$/.test(q1) === false) {
            $('#perr').text('Please enter the Question1.(Special characters not accepted)');
        } else if (q2 === '' || /^[a-zA-Z0-9 ]*$/.test(q2) === false) {
            $('#perr').text('Please enter the Question2.(Special characters not accepted)');
        } else if (q3 === '' || /^[a-zA-Z0-9 ]*$/.test(q3) === false) {
            $('#perr').text('Please enter the Question3.(Special characters not accepted)');
        } else if (q4 === '' || /^[a-zA-Z0-9 ]*$/.test(q4) === false) {
            $('#perr').text('Please enter the Question4.(Special characters not accepted)');
        } else if (q5 === '' || /^[a-zA-Z0-9 ]*$/.test(q5) === false) {
            $('#perr').text('Please enter the Question5.(Special characters not accepted)');
        } else {
            //$( "#target" ).submit();
            //return true;
            $('.dataloaderimg2').show();
            $('#chck,#cancl').hide();
            var val = "<?php echo url::toText($userId); ?>";
            var searchval = "<?php echo url::toText($searchVal); ?>";

            var msgconf = '252&&&<?php echo $userId; ?>&&&1&&&Request : Configure Security Questions&&&<?php echo $wgORd; ?>&&&<?php echo $uName; ?>&&&<?php echo $domain; ?>&&&Ques1:' + q1 + '$$$Ques2:' + q2 + '$$$Ques3:' + q3 + '$$$Ques4:' + q4 + '$$$Ques5:' + q5 + '&&&';
            var msg = btoa(msgconf) + '252&&&';
            ExecuteDirectJob(searchval, msg);
        }
    });
    //-------cancel------------------
    $('#cancl').click(function() {
        //        $('#procRow').show();
        $('fieldset').removeAttr("disabled");
        //$("#Default").load('rightPaneDetailsAgent.html');
        $("#Default").load('rightPaneDetails.html');
    });

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
</script>
