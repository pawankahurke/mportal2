<div class="rightCol brandingUI" style="display: none;">
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

                                            <div class="mainBox">

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
                                                            <div class="setupPage clearfix">
                                                                <div class="slideshow-container text-center">
                                                                    <div class="mySlides branding1">
                                                                        <a style="display:none" href="<?php echo url::toText($URLVal1); ?>" id="URLBranding1" ng-bind="branding1_url" target="_blank"></a>
                                                                        <img src="<?php echo $folderPath; ?>images/group-1.png">
                                                                        <h5 class="headtxt" id="TextBranding1" ng-bind="branding1_text"></h5>
                                                                    </div>

                                                                    <div class="mySlides branding2">
                                                                        <a style="display:none" href="<?php echo url::toText($URLVal2); ?>" id="URLBranding2" ng-bind="branding2_url" target="_blank"></a>
                                                                        <img src="<?php echo $folderPath; ?>images/group-2.png">
                                                                        <h5 class="headtxt" id="TextBranding2" ng-bind="branding2_text"></h5>
                                                                    </div>

                                                                    <div class="mySlides branding3">
                                                                        <a style="display:none" href="<?php echo url::toText($URLVal3); ?>" id="URLBranding3" ng-bind="branding3_url" target="_blank"></a>
                                                                        <img src="<?php echo $folderPath; ?>images/group-3.png">
                                                                        <h5 class="headtxt" id="TextBranding3" ng-bind="branding3_text"></h5>
                                                                    </div>

                                                                    <div class="mySlides branding4">
                                                                        <a style="display:none" href="<?php echo url::toText($URLVal4); ?>" id="URLBranding4" ng-bind="branding4_url" target="_blank"></a>
                                                                        <img src="<?php echo $folderPath; ?>images/group-4.png">
                                                                        <h5 class="headtxt" id="TextBranding4" ng-bind="branding4_text"></h5>
                                                                    </div>

                                                                    <div class="mySlides branding5">
                                                                        <a style="display:none" href="<?php echo url::toText($URLVal5); ?>" id="URLBranding5" ng-bind="branding5_url" target="_blank"></a>
                                                                        <img src="<?php echo $folderPath; ?>images/group-5.png">
                                                                        <h5 class="headtxt" id="TextBranding5" ng-bind="branding5_text"></h5>
                                                                    </div>
                                                                </div>

                                                                <div class="text-center">
                                                                    <span class="dot"></span>
                                                                    <span class="dot"></span>
                                                                    <span class="dot"></span>
                                                                    <span class="dot"></span>
                                                                    <span class="dot"></span>
                                                                </div>

                                                                <div class="scanner">
                                                                    <div class="progress">
                                                                        <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="22" aria-valuemin="0" aria-valuemax="100" style="width:22%"></div>
                                                                    </div>
                                                                    <div class="scanner-text">
                                                                        <span>Initializing..</span> <span>42% Complete</span>
                                                                    </div>
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
                                    <!--<input type="hidden" id="rotSpeed" name="rotSpeed" value="2000" />-->
                                    <div class="tabInner">
                                        <div class="table-responsive innerLeft">
                                            <div class="closebtn"><a href="javascript:void(0)" onclick="goToBranding();" data-bs-target="rsc-add-container">&times;</a></div>
                                            <p class="col-md-12 paraTxt">A user would see this page as they are waiting for the either installation, scanning and/or fixing to finish. You can upload custom images or marketing messages for the user as he waits for the process to complete.</p>
                                            <input type="hidden" value="" id="hiddenId">

                                            <!--Marketing image 1-->
                                            <div class="colBox">
                                                <h5 class="card-title"><b>Upload Marketing Image 1<span style="color:red">(only PNG file allowed)</span></b></h5>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <p class="col-md-2 txtsm">File selected : </p>
                                                        <p class="col-md-10 txt"><span id="branding1_image"></span></p>
                                                    </div>
                                                </div>

                                                <span class="btn btn-round btn-rose btn-file btn-sm btn-cursor">
                                                    <span class="fileinput-new">Add Image</span>
                                                    <input type="file" id="brandImage1" name="brandImage1" accept="image/*" onchange="uploadMarketingImages(this, 'branding1', $(this));" />
                                                </span>

                                                <span class="btn btn-success btn-round btn-sm" id="remove_branding1">
                                                    <span class="fileinput-new">Remove</span>
                                                </span>

                                                <span class="branding1_loader" style="display:none;">
                                                    <img class="" alt="loader..." src="../assets/img/loader-sm.gif">
                                                </span>

                                                <div>&nbsp;</div>

                                                <div class="form-group">
                                                    <label>Enter Text</label>
                                                    <!--<input class="form-control" type="text" id="branding1_text"  placeholder="Enter Value">-->
                                                    <input type="text" id="branding1_text" class="form-control" placeholder="Enter Value" ng-model="branding1_text" maxlength="49" autocomplete="off" />
                                                    <input type="hidden" id="branding_text1" value="<?php echo url::toText($TXTVal1); ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label>Enter URL</label>
                                                    <input class="form-control URL_branding" type="url" id="branding1_url" ng-model="branding1_url" name="branding1_url" placeholder="Enter URL">
                                                    <input type="hidden" id="url_text1" value="<?php echo url::toText($URLVal1); ?>">
                                                </div>
                                            </div>
                                            <!--Marketing image 2-->
                                            <div class="colBox" id="Marketing_Image2" style="display:none;">
                                                <h5 class="card-title"><b>Upload Marketing Image 2<span style="color:red">(only PNG file allowed)</span></b></h5>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <p class="col-md-2 txtsm">File selected : </p>
                                                        <p class="col-md-10 txt"><span id="branding2_image"></span></p>
                                                    </div>
                                                </div>

                                                <span class="btn btn-round btn-rose btn-file btn-sm btn-cursor">
                                                    <span class="fileinput-new">Add Image</span>
                                                    <input type="file" id="brandImage2" name="brandImage2" accept="image/*" onchange="uploadMarketingImages(this, 'branding2', $(this));" />
                                                </span>

                                                <span class="btn btn-success btn-round btn-sm" id="remove_branding2">
                                                    <span class="fileinput-new">Remove</span>
                                                </span>

                                                <span class="branding2_loader" style="display:none;">
                                                    <img class="" alt="loader..." src="../assets/img/loader-sm.gif">
                                                </span>

                                                <div>&nbsp;</div>

                                                <div class="form-group">
                                                    <label>Enter Text</label>
                                                    <!--<input class="form-control" type="text" id="branding1_text"  placeholder="Enter Value">-->
                                                    <input type="text" id="branding2_text" class="form-control" placeholder="Enter Value" ng-model="branding2_text" maxlength="49" autocomplete="off" />
                                                    <input type="hidden" id="branding_text2" value="<?php echo url::toText($TXTVal2); ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label>Enter URL</label>
                                                    <input class="form-control URL_branding" type="url" id="branding2_url" name="branding2_url" ng-model="branding2_url" placeholder="Enter URL">
                                                    <input type="hidden" id="url_text2" value="<?php echo url::toText($URLVal2); ?>">
                                                </div>
                                            </div>
                                            <!--Marketing image 3-->
                                            <div class="colBox" id="Marketing_Image3" style="display:none;">
                                                <h5 class="card-title"><b>Upload Marketing Image 3<span style="color:red">(only PNG file allowed)</span></b></h5>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <p class="col-md-2 txtsm">File selected : </p>
                                                        <p class="col-md-10 txt"><span id="branding3_image"></span></p>
                                                    </div>
                                                </div>

                                                <span class="btn btn-round btn-rose btn-file btn-sm btn-cursor">
                                                    <span class="fileinput-new">Add Image</span>
                                                    <input type="file" id="brandImage3" name="brandImage3" accept="image/*" onchange="uploadMarketingImages(this, 'branding3', $(this));" />
                                                </span>

                                                <span class="btn btn-success btn-round btn-sm" id="remove_branding3">
                                                    <span class="fileinput-new">Remove</span>
                                                </span>

                                                <span class="branding3_loader" style="display:none;">
                                                    <img class="" alt="loader..." src="../assets/img/loader-sm.gif">
                                                </span>

                                                <div>&nbsp;</div>

                                                <div class="form-group">
                                                    <label>Enter Text</label>
                                                    <!--<input class="form-control" type="text" id="branding1_text"  placeholder="Enter Value">-->
                                                    <input type="text" id="branding3_text" class="form-control" placeholder="Enter Value" ng-model="branding3_text" maxlength="49" autocomplete="off" />
                                                    <input type="hidden" id="branding_text3" value="<?php echo url::toText($TXTVal3); ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label>Enter URL</label>
                                                    <input class="form-control URL_branding" type="url" id="branding3_url" ng-model="branding3_url" name="branding3_url" placeholder="Enter URL">
                                                    <input type="hidden" id="url_text3" value="<?php echo url::toText($URLVal3); ?>">
                                                </div>
                                            </div>
                                            <!--Marketing image 4-->
                                            <div class="colBox" id="Marketing_Image4" style="display:none;">
                                                <h5 class="card-title"><b>Upload Marketing Image 4<span style="color:red">(only PNG file allowed)</span></b></h5>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <p class="col-md-2 txtsm">File selected : </p>
                                                        <p class="col-md-10 txt"><span id="branding4_image"></span></p>
                                                    </div>
                                                </div>

                                                <span class="btn btn-round btn-rose btn-file btn-sm btn-cursor">
                                                    <span class="fileinput-new">Add Image</span>
                                                    <input type="file" id="brandImage4" name="brandImage4" accept="image/*" onchange="uploadMarketingImages(this, 'branding4', $(this));" />
                                                </span>

                                                <span class="btn btn-success btn-round btn-sm" id="remove_branding4">
                                                    <span class="fileinput-new">Remove</span>
                                                </span>

                                                <span class="branding4_loader" style="display:none;">
                                                    <img class="" alt="loader..." src="../assets/img/loader-sm.gif">
                                                </span>

                                                <div>&nbsp;</div>

                                                <div class="form-group">
                                                    <label>Enter Text</label>
                                                    <!--<input class="form-control" type="text" id="branding1_text"  placeholder="Enter Value">-->
                                                    <input type="text" id="branding4_text" class="form-control" placeholder="Enter Value" ng-model="branding4_text" maxlength="49" autocomplete="off" />
                                                    <input type="hidden" id="branding_text4" value="<?php echo url::toText($TXTVal4); ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label>Enter URL</label>
                                                    <input class="form-control URL_branding" type="url" ng-model="branding4_url" id="branding4_url" name="branding4_url" placeholder="Enter URL">
                                                    <input type="hidden" id="url_text4" value="<?php echo url::toText($URLVal4); ?>">
                                                </div>
                                            </div>
                                            <!--Marketing image 5-->
                                            <div class="colBox" id="Marketing_Image5" style="display:none;">
                                                <h5 class="card-title"><b>Upload Marketing Image 5<span style="color:red">(only PNG file allowed)</span></b></h5>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <p class="col-md-2 txtsm">File selected : </p>
                                                        <p class="col-md-10 txt"><span id="branding5_image"></span></p>
                                                    </div>
                                                </div>

                                                <span class="btn btn-round btn-rose btn-file btn-sm btn-cursor">
                                                    <span class="fileinput-new">Add Image</span>
                                                    <input type="file" id="brandImage5" name="brandImage5" accept="image/*" onchange="uploadMarketingImages(this, 'branding5', $(this));" />
                                                </span>

                                                <span class="btn btn-success btn-round btn-sm" id="remove_branding5">
                                                    <span class="fileinput-new">Remove</span>
                                                </span>

                                                <span class="branding5_loader" style="display:none;">
                                                    <img class="" alt="loader..." src="../assets/img/loader-sm.gif">
                                                </span>

                                                <div>&nbsp;</div>

                                                <div class="form-group">
                                                    <label>Enter Text</label>
                                                    <!--<input class="form-control" type="text" id="branding1_text"  placeholder="Enter Value">-->
                                                    <input type="text" id="branding5_text" class="form-control" placeholder="Enter Value" ng-model="branding5_text" maxlength="49" autocomplete="off" />
                                                    <input type="hidden" id="branding_text5" value="<?php echo url::toText($TXTVal5); ?>">
                                                </div>

                                                <div class="form-group">
                                                    <label>Enter URL</label>
                                                    <input class="form-control URL_branding" type="url" ng-model="branding5_url" id="branding5_url" name="branding5_url" placeholder="Enter URL">
                                                    <input type="hidden" id="url_text5" value="<?php echo url::toText($URLVal5); ?>">
                                                </div>
                                            </div>
                                            <!--                                            <span id="checknewimages">

                                                </span>-->

                                            <!--                                                <div class="row">
                                                    <div class="col-md-12">
                                                <span class="error txt-sm"></span>
                                                    </div>
                                                </div>-->

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <span class="addDelete" id="RemoveImages">Delete</span>
                                                    <span class="addMore" id="AddNewImages">Add More</span>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label>Image rotation speed ( in milliseconds ) : <span id="rotSpdErr" style="color:red;"></span></label>
                                                <input class="form-control" type="number" id="rotationSpeed" name="rotationSpeed" value="<?php echo url::toText($rotationSpeed); ?>" min="1000" max="8000" onkeyup="updateRotationSpeed(this)" required />
                                            </div>

                                            <div class="button col-md-12 text-center btBtn">
                                                <button type="button" class="swal2-confirm btn btn-success btn-sm btn-back" onclick="goToScreen('installerUI');">Prev</button>
                                                <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next" onclick="submitMarketingChanges();">Save and Continue</button>
                                                <span class="txt-sm" onclick="goToScreen('landingPageUI');">Skip and use defaults</span>
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
</div>