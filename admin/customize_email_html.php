<div class="rightCol emailTemplateUI" style="display: none;">
    <div class="content white-content">
        <div class="row column">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="">
                            <div class="row clearfix innerPage">
                                <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 lf-rt-br left" style="overflow-y: scroll; height: calc(100vh - 105px);">
                                    <div class="row">
                                        <div class="col-md-12" id="emailContent">
                                        <?php if($emailBody == '') { ?>
                                            <img src="../assets/img/mail_icon.svg" style="margin: -10% 0% 0% 30%">
                                            <div style="margin-left: 10%">Upload an email template. This template would be used to send the email containing the client download url</div>
                                        <?php } else { echo $emailBody; } ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 right">
                                    <div class="tabInner">
                                        <div class="colBox">
                                            <div class="closebtn"><a href="javascript:void(0)" onclick="goToBranding();" data-bs-target="rsc-add-container">&times;</a></div>
                                            <p class="col-md-12 paraTxt">Your customers would get this email when you share the download URL with them via Email.</p>
                                        </div>

                                        <div class="form-group has-label">
                                            <label>Email Subject</label>
                                            <input class="form-control" name="email_subject" id="email_subject" type="text" value="Client Download URL" maxlength="50" required />
                                        </div>
                                        <br/>

                                        <div class="colBox">
                                            <h5 class="card-title">Upload the email template here<span style="color:red">(only HTML file allowed)</span></h5>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <p class="col-md-2 txtsm">File selected : </p>
                                                    <p class="col-md-10 txt"><span id="template_name"></span></p>
                                                </div>
                                            </div>

                                            <span class="btn btn-round btn-rose btn-file btn-sm btn-cursor">
                                                <span class="fileinput-new">Upload Email Template</span>
                                                <input type="file" id="email_template" name="email_template" />
                                            </span>

                                            <span class="btn btn-success btn-round btn-sm" id="remove_template">
                                                <span class="fileinput-new">Remove</span>
                                            </span>

                                            <span class="logo_loader" style="display:none;">
                                                <img class="" alt="loader..." src="../assets/img/loader-sm.gif">
                                            </span>
                                        </div>
                                        <br/>

                                        <div class="form-group has-label text-left">
                                            <label>Send Test Mail <span id="emErr"></span></label>
                                            <input class="form-control" name="emailList" id="emailList" type="text" autocomplete="off">
                                            <br/>
                                            <button type="button" class="swal2-confirm btn btn-success btn-sm" id="testMail">Send</button>
                                        </div>
                                        <span class="txt-sm" id="success_msg"></span>
                                    </div>

                                    <div class="button col-md-12 text-center btBtn">
                                        <button type="button" class="swal2-confirm btn btn-success btn-sm btn-back" onclick="goToScreen('landingPageUI');">Prev</button>
                                        <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next" onclick="saveEmailTemplateDetails();">Save and Continue</button>
                                        <span class="txt-sm" onclick="goToScreen('finishScreenUI');">Skip and use defaults</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>