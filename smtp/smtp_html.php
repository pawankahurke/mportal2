<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
?><div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                 <div class="card-body">
                    <div class="bullDropdown">
                        <div class="dropdown" id="explain_auidt">
                            <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="tim-icons icon-bullet-list-67"></i>
                            </button>

                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addsmtp', 2);?>" id="addsmtpconfig" data-target="rsc-addsmtp-container">Add SMTP</a>
                                <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('testsmtp', 2);?>" id="testsmtp" data-target="rsc-sendmail">Test SMTP</a>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="smtp_selected">
                    <table class="nhl-datatable table table-striped" id="smtpTable" width="100%" data-page-length="25">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Created Time</th>
                                <th>Modified Time</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!--Config smtp starts--> 
 <div id="rsc-addsmtp-container" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Add Config SMTP</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="rsc-addsmtp-container">&times;</a>
    </div>
     <div class="btnGroup" style="display: block;">
        <div class="icon-circle">
            <div class="toolTip" onclick="createsmtp();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>

    </div>
    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label>Name : <em class="error" id="required_name">*</em></label>
                        <input class="form-control" type="text" id="name">
                        <span id="name_err"></span>
                    </div>

                    <div class="form-group has-label">
                        <label>Host : <em class="error" id="required_host">*</em></label>
                        <input class="form-control" type="text" id="host" />
                    </div>

                    <div class="form-group has-label">
                        <label>Port <em class="error" id="required_port">*</em></label>
                        <input class="form-control" type="text" id="port" />
                        <span id="port_err"></span>
                    </div>

                    <div class="form-group has-label">
                        <label>Username </label>
                        <input class="form-control" type="text" id="username" />
                    </div>

                    <div class="form-group has-label">
                        <label>Password </label>
                        <input class="form-control" type="password" id="pwd" />
                    </div>

                    <div class="form-group has-label">
                        <label>From Email <em class="error" id="required_fromemail">*</em></label>
                        <input class="form-control" type="text" id="fromemail" />
                        <span id="fromemail_err"></span>
                    </div>
                    
                    <div class="card-body">
                        <div class="form-group has-label">
                            <label for="security">
                                Security
                            </label>
                        </div>

                        <div class="form-check form-check-radio global">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="exampleRadios1" id="ssl" checked>
                                <span class="form-check-sign"></span> SSL
                            </label>

                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="exampleRadios1" id="tls">
                                <span class="form-check-sign"></span> TLS
                            </label>
                            
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="exampleRadios1" id="none">
                                <span class="form-check-sign"></span> None
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!--config smtp ends-->

<!--test email starts--> 

<div id="rsc-sendmail" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Test Mail</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="rsc-sendmail">&times;</a>
    </div>
     <div class="btnGroup" style="display: block;">
        <div class="icon-circle">
            <div class="toolTip" onclick="sendmail();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Send Mail</span>
            </div>
        </div>

    </div>
    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <label>To Email :</label>
                    <div class="form-group has-label">
                        <input class="form-control" type="text" id="toemail">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!--test email ends-->

<!--Config edit smtp starts--> 
 <div id="rsc-editsmtp-container" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Edit Config SMTP</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="rsc-editsmtp-container">&times;</a>
    </div>
    <div class="btnGroup" id="editOption" style="display: block;">
        <div class="icon-circle">
            <div class="toolTip editOption">
                <i class="tim-icons icon-pencil"></i>
                <span class="tooltiptext">Edit</span>
            </div>
        </div>
    </div>

    <div class="btnGroup" style="display: none;" id="toggleButton">
        <div class="icon-circle iconTick circleGrey">
            <div class="toolTip" onclick="editsmtp();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>

        <div class="icon-circle toggleEdit" id="toggleEdit">
            <div class="toolTip">
                <i class="tim-icons icon-simple-remove"></i>
                <span class="tooltiptext">Toggle edit mode</span>
            </div>
        </div>
    </div>
   
    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <label>Name :</label>
                    <div class="form-group has-label">
                        <input class="form-control" type="text" id="editname">
                        <em class="error" id="required_editname">*</em>
                        <span id="editname_err"></span>
                    </div>

                    <label>Host :</label>
                    <div class="form-group has-label">
                        <input class="form-control" type="text" id="edithost" />
                        <em class="error" id="required_edithost">*</em>
                    </div>

                    <label>Port</label>
                    <div class="form-group has-label">
                        <input class="form-control" type="text" id="editport" />
                        <em class="error" id="required_editport">*</em>
                        <span id="editport_err"></span>
                    </div>
                    
                    <label>Username</label>
                    <div class="form-group has-label">
                        <input class="form-control" type="text" id="editusername" />
                        <em class="error" id="required_editusername">*</em>
                    </div>
                    
                    <label>Password</label>
                    <div class="form-group has-label">
                        <input class="form-control" type="password" id="editpwd" />
                        <em class="error" id="required_editpwd">*</em>
                    </div>
                    
                    <label>From Email</label>
                    <div class="form-group has-label">
                        <input class="form-control" type="text" id="editfromemail" />
                        <em class="error" id="required_editfromemail">*</em>
                        <span id="editfromemail_err"></span>
                    </div>
                    
                    <div class="card-body">
                        <div class="form-group has-label">
                            <label for="security">
                                Security
                            </label>
                        </div>

                        <div class="form-check form-check-radio global">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="exampleRadios2" id="editssl">
                                <span class="form-check-sign"></span> SSL
                            </label>

                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="exampleRadios2" id="edittls">
                                <span class="form-check-sign"></span> TLS
                            </label>

                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="exampleRadios2" id="editnone">
                                <span class="form-check-sign"></span> None
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!--config edit smtp ends-->