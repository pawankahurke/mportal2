<!-- content starts here  -->      

<div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <!--     Here you can write extra buttons/actions for the toolbar              -->
                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>    
                            </div>
                        </div>

                        <div class="bullDropdown" >
                            <div class="dropdown" id="explain_login">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a  id="BackToMsgConfig" onclick="backtoMessageConfig()" class="dropdown-item rightslide-container-hand dropHandy <?php ?>" >Back To Message</a>
                                    <a  id="BackTab" onclick="backtoMessageAudit()" class="dropdown-item rightslide-container-hand dropHandy  <?php echo setRoleForAnchorTag('backtab', 2); ?> " >Back</a>
                                    <a  id="ExportAudit" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('exportaudit', 2); ?>" >Export Audit Details</a>
                                    <a  id="ExportAuditDetails" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('exportauditdetail', 2); ?>" >Export Audit</a>
                                    <a  id="msg_add_configurations" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addmsgconfig', 2); ?>" onclick="addmessageConfig()">Add Message</a>
                                    <a  id="msg_edit_configurations" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('editmsgconfig', 2); ?>"  onclick="editmessageConfig()">Edit Message</a>
                                    <a  id="msg_audit_configurations" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('msgauditconfig', 2); ?>"  onclick="showMessageAudit()" >Message Audit</a>
                                    <a  id="msg_trigger_configurations" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('triggermsgconfig', 2); ?>"  onclick="triggerMessage()">Trigger Message</a>
                                    <a  id="msg_clear_configurations" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('clearmsgconfig', 2); ?>"  onclick="clearMessage()">Clear Message</a>
                                    <a  id="msg_delete_configurations" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('deletemsgconfig', 2); ?>"  onclick="deleteMessage()">Delete Message</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id ="messageConfig">
                            <table class="nhl-datatable table table-striped msgconfig" width="100%" data-page-length="25" id="msgconfig_grid" >
                                <thead>
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Title</th>
                                        <th>Message Name</th>
                                        <th>Url</th>
                                        <th>Creation Time</th>
                                    </tr>
                                </thead>
                                 <tfoot>
                                    <tr>
                                        <th>Sr. No.</th>
                                        <th>Title</th>
                                        <th>Message Name</th>
                                        <th>Url</th>
                                        <th>Creation Time</th>
                                    </tr>
                                </tfoot>
                                
                            </table>
                            </div>
                    <input type="hidden" id="selected">
                    <input type="hidden" id="selOsType">
                    <input type="hidden" id="selected">
                    <input type="hidden" id="selectedPackage">
                    <input type="hidden" id="selected">
                        <input type="hidden" id="typeselected">
                        <input type="hidden" id="messageId">
                        <input type="hidden" id="messageName">
                    
                        <div id="leftMsgAudit" style= "display:none">
                            <table id="AuditGrid" class="nhl-datatable table table-striped" width="100%" data-page-length="25">
                                <thead>
                                    <tr>
                                                <th class="ProfileName">Message Name</th>
                                                <th class="JobCreatedTime">Triggered Time</th>
                                                <th class="AgentName">Agent</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        <div id="rightMsgAudit" style= "display:none">
                                    <table id="auditGridDetail" class="nhl-datatable table table-striped" width="100%" data-page-length="25">
                                        <thead>
                                            <tr >
                                                <th class="SelectionType">Scope</th>
                                                <th class="MachineTag">Machine</th>
                                                <th class="Status">Status</th>
                                            </tr>
                                        </thead>
                                    </table>
                        </div>

<!--Add Message-->
<div id="addmsgconfig" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Add Message</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="addmsgconfig">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="addDeploymentSiteBtn" onclick="addMessage();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Add</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">

                
                <div class="form-group has-label">
                    <label for="msgtitle">Title</label><em class="error" id="err_msgtitle"></em>
                    <input type="text" name="msgtitle" id="msgtitle" class="form-control">
                </div>
                
                <div class="form-group has-label">
                    <label for="msgtext">Message</label><em class="error" id="err_msgtext"></em>
                    <textarea type="text" name="msgtext" id="msgtext" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>
                
                <div class="form-group has-label">
                    <label for="msgURL">URL</label><em class="error" id="err_msgURL"></em>
                    <input type="text" name="msgURL" id="msgURL" class="form-control">
                </div>
                
                <div class="form-group has-label">
                    <label for="btntxt">Button Text</label><em class="error" id="err_btntxt"></em>
                    <input type="text" name="btntxt" id="btntxt" class="form-control">
                </div>
                
                 <div class="form-group has-label">
                    <label for="snoozebtntxt">Snooze Button Text</label><em class="error" id="err_snoozebtntxt"></em>
                    <input type="text" name="snoozebtntxt" id="snoozebtntxt" class="form-control">
                </div>
                
                 <div class="form-group has-label">
                    <label for="retrytime">Retry Interval(minutes)</label><em class="error" id="err_retrytime"></em>
                    <input type="text" name="retrytime" id="retrytime" class="form-control">
                </div>
                
                 <div class="form-group has-label">
                    <label for="retryfreq">Retry Frequency</label><em class="error" id="err_retryfreq"></em>
                    <input type="text" name="retryfreq" id="retryfreq" class="form-control">
                </div>
                
                 <div class="form-group has-label">
                    <label for="msglife">Message Lifetime(Hours)</label><em class="error" id="err_msglife"></em>
                    <input type="text" name="msglife" id="msglife" class="form-control">
                </div>
                
            </div>
        </div>
    </div>
</div>
                  
<!--Edit Message-->
<div id="editmsgconfig" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Edit Message</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="editmsgconfig">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="addDeploymentSiteBtn" onclick="editMessage();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Add</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">

                
                <div class="form-group has-label">
                    <label for="editmsgtitle">Title</label><em class="error" id="err_editmsgtitle"></em>
                    <input type="text" name="editmsgtitle" id="editmsgtitle" class="form-control">
                </div>
                
                <div class="form-group has-label">
                    <label for="editmsgtext">Message</label><em class="error" id="err_editmsgtext"></em>
                    <textarea type="text" name="editmsgtext" id="editmsgtext" class="form-control" style="border:1px solid lightgrey;border-radius: 5px;"></textarea>
                </div>
                
                <div class="form-group has-label">
                    <label for="editmsgURL">URL</label><em class="error" id="err_editmsgURL"></em>
                    <input type="text" name="editmsgURL" id="editmsgURL" class="form-control">
                </div>
                
                <div class="form-group has-label">
                    <label for="editbtntxt">Button Text</label><em class="error" id="err_editbtntxt"></em>
                    <input type="edittext" name="editbtntxt" id="editbtntxt" class="form-control">
                </div>
                
                 <div class="form-group has-label">
                    <label for="editsnoozebtntxt">Snooze Button Text</label><em class="error" id="err_editsnoozebtntxt"></em>
                    <input type="text" name="editsnoozebtntxt" id="editsnoozebtntxt" class="form-control">
                </div>
                
                 <div class="form-group has-label">
                    <label for="editretrytime">Retry Interval(minutes)</label><em class="error" id="err_editretrytime"></em>
                    <input type="text" name="editretrytime" id="editretrytime" class="form-control">
                </div>
                
                 <div class="form-group has-label">
                    <label for="editretryfreq">Retry Frequency</label><em class="error" id="err_retryfreq"></em>
                    <input type="text" name="editretryfreq" id="editretryfreq" class="form-control">
                </div>
                
                 <div class="form-group has-label">
                    <label for="editmsglife">Message Lifetime(Hours)</label><em class="error" id="err_editmsglife"></em>
                    <input type="text" name="editmsglife" id="editmsglife" class="form-control">
                </div>
                
            </div>
        </div>
    </div>
</div>

<!--Export Range-->
<div id="rsc-add-container34" class="rightSidenav leftSidenav" data-class="sm-3">
       <div class="card-title">
        <h4>Export </h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="rsc-add-container34">&times;</a>
        </div>
        <div class="btnGroup">
            <div class="icon-circle create_site_div">
                <div class="toolTip" id="submitExport" onclick="exportAlertAuditDetails();">
                    <i class="tim-icons icon-check-2"></i>
                    <span class="tooltiptext">Export</span>
                </div>
            </div>
        </div>
        <div class="form table-responsive white-content">
            <div class="sidebar">
                <ul class="nav">
                    <li>
                        <p style="margin-left: 5%;">Select Date</p>

                            <div class="col-md-6">
                                <input type="text" class="form-control frompatch datetimepicker" id="datefrom" autocomplete="off" value="">
                            </div>

                            <div class="col-md-6">
                                <input type="text" class="form-control topatch datetimepicker" autocomplete="off" id="dateto" value="">
                            </div>
                    </li>
                    
                    <li>
                        <div style="margin-left: 16%;" id='filter_error'></div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>


<!--<script>
           $(document).ready(function () {
                getMessageAudit();
            });
            
</script>-->
