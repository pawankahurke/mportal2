<div class="rightCol landingPageUI" style="display: none;">
    <div class="content white-content">
        <div class="row column">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="row clearfix innerPage">
                                <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 lf-rt-br left">
                                    <div class="row">
                                        <div class="bullDropdown leftDropdown" style="margin-left: 2%;">
                                            <h5>Selected Customer : <span class="site"> <?php echo $customerName; ?> </span></h5>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mainBox bg-setup-img lp-bg-setup-img">
                                                <div class="headerWrap">
                                                    <div class="left">
                                                        <img class="logo" alt="logo" src="<?php echo url::toText($logoImgPath); ?>">
                                                    </div>

                                                    <div class="right">
                                                        <i class="tim-icons icon-align-left-2"></i>
                                                        <i class="tim-icons icon-simple-delete"></i>
                                                        <i class="tim-icons icon-simple-remove"></i>
                                                    </div>
                                                </div>

                                                <div class="centerWrap clearfix">
                                                    <div class="row">
                                                        <div class="col-md-12 right">
                                                            <div class="welcomePage">
                                                                <h1 class="title" id="landingwcMsgTitleText" ng-bind="landingwcMsgTitle"></h1>
                                                                <h2 class="title" id="landingwcMsgText" ng-bind="landingwcMsg"></h2>
                                                                <h2 class="title">Click the button below to start your system scan.</h2>

                                                                <div class="button text-left">
                                                                    <button type="button" class="btn btn-success with-bg buttonColor" name="scan">Start Your System Scan</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="footerWrap">
                                                    <div class="left"> <span>Need help? {{supportPhNo}}</span> </div>

                                                    <div class="right"> <span>Live Chat</span> </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 right">
                                    <div class="tabInner">
                                        <div class="closebtn"><a href="javascript:void(0)" onclick="goToBranding();" data-bs-target="rsc-add-container">&times;</a></div>
                                        <p class="col-md-12 paraTxt">A user would see this page as soon as the installation is complete. You can enter your own custom message to welcome the user.
                                            <br /> You can also enter your phone number and chat URL (If available, so that your customers can contact you easily).
                                        </p>

                                        <div class="form-group">
                                            <label>Enter the Welcome message title : (<span id="landingwcMsgTitleCharRem">50</span> Characters remaining)</label>
                                            <input class="form-control" type="text" id="landingwcMsgTitle" name="landingwcMsgTitle" ng-model="landingwcMsgTitle" maxlength="49" autocomplete="off" />
                                            <input type="hidden" id="landingwcMsgTitleHidden" value="<?php echo url::toText($landingWelcomeTitle); ?>">
                                        </div>

                                        <div class="form-group length">
                                            <label>Enter the Welcome message : (<span id="landingwcMsgCharRem">50</span> Characters remaining)</label>
                                            <textarea id="landingwcMsg" name="landingwcMsg" class="form-control" rows="60" ng-model="landingwcMsg" maxlength="180" autocomplete="off"></textarea>
                                            <input type="hidden" id="landingwcMsgHidden" value="<?php echo url::toText($landingWelcomeMsg); ?>">
                                        </div>

                                        <div class="colBox">
                                            <h5 class="card-title">Upload a background image<span style="color:red">(only PNG file allowed)</span></h5>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <p class="col-md-2 txtsm">File selected :</p>
                                                    <p class="col-md-10 txt"><span id="landingbgimg_name"></span></p>
                                                </div>
                                            </div>

                                            <span class="btn btn-round btn-rose btn-file btn-sm btn-cursor">
                                                <span class="fileinput-new">Add Photo</span>
                                                <input type="file" id="pub_landingbgimage" name="pub_landingbgimage" accept="image/*" />
                                            </span>

                                            <span class="btn btn-success btn-round btn-sm" id="remove_landingbgimage">
                                                <span class="fileinput-new" data-dismiss="fileinput">Remove</span>
                                            </span>

                                            <span class="lpbgimg_loader" style="display:none;">
                                                <img class="" alt="loader..." src="../assets/img/loader-sm.gif">
                                            </span>
                                        </div>

                                        <div class="button col-md-12 text-center btBtn">
                                            <button type="button" class="swal2-confirm btn btn-success btn-sm btn-back" onclick="goToScreen('brandingUI');/*goToScreen('brandingUI');*/">Prev</button>
                                            <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next" onclick="saveLandingPageDetails();">Save and Continue</button>
                                            <span class="txt-sm" onclick="goToScreen('emailTemplateUI');">Skip and use defaults</span>
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
</div>