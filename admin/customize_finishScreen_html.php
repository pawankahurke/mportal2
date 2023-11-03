<div class="content white-content finishScreenUI" style="display: none">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <div class="bullDropdown leftDropdown">
                            <h5>You are almost done!</h5>
                            <div>
                                <p>Please preview the designs & click on save the changes.</p>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive text-center">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    <div class="brandBox">
                                        <!--<img id="myImg1" src="../assets/img/bg-img.png" alt="" class="img">-->
                                        <div class="mainBox bg-setup-img">
                                            <div class="headerWrap">
                                                <div class="left">
                                                    <img class="logo" alt="logo" src="<?php echo url::toText($logoImgPath); ?>">
                                                </div>

                                                <div class="left" style="display: none">
                                                    <img class="shorcutIcon" alt="logo" src="<?php echo url::toText($shortcutImgPath); ?>">
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
                                                        <div class="welcomePage text-left">
                                                            <h1 class="title" id="welcomeMsgText" ng-bind="welcomeMsg"></h1>
                                                            <h2 class="title">We keep your connected ecosystem humming along - so you don't have to. Our experts and tools will help ensure your devices are safe, clean and running fast.</h2>

                                                            <div class="form-group">
                                                                <div class="form-check">
                                                                    <label class="form-check-label">
                                                                        <input class="form-check-input" type="checkbox">
                                                                        <span class="form-check-sign"></span>
                                                                    </label>
                                                                    <label><span id="termsAgreeText">I agree to the <span><a href="" target="_blank">Terms and Conditions</a></span> associated with
                                                                            <br>this product</span>
                                                                    </label>
                                                                </div>
                                                            </div>

                                                            <div class="button text-left">
                                                                <button type="button" class="btn btn-success blank" name="cancel">Cancel</button>
                                                                <button type="button" class="btn btn-success with-bg buttonColor" name="next">Next</button>
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
                                    <p>Customize the theme and branding</p>
                                </div>

                                <div class="col-md-6">
                                    <div class="brandBox">
                                        <!--<img id="myImg2" src="../assets/img/bg-img.png" alt="" class="img">-->
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
                                                                <div class="mySlidess branding1">
                                                                    <a href="<?php echo url::toText($URLVal1); ?>" id="URLBranding1" target="_blank"></a>
                                                                    <img src="<?php echo $folderPath; ?>images/group-1.png">
                                                                    <h5 class="headtxt" id="TextBranding1" ng-bind="branding1_text"></h5>
                                                                </div>

                                                                <div class="mySlidess branding2">
                                                                    <a href="<?php echo url::toText($URLVal2); ?>" id="URLBranding2" target="_blank"></a>
                                                                    <img src="<?php echo $folderPath; ?>images/group-2.png">
                                                                    <h5 class="headtxt" id="TextBranding2" ng-bind="branding2_text"></h5>
                                                                </div>

                                                                <div class="mySlidess branding3">
                                                                    <a href="<?php echo url::toText($URLVal3); ?>" id="URLBranding3" target="_blank"></a>
                                                                    <img src="<?php echo $folderPath; ?>images/group-3.png">
                                                                    <h5 class="headtxt" id="TextBranding3" ng-bind="branding3_text"></h5>
                                                                </div>

                                                                <div class="mySlidess branding4">
                                                                    <a href="<?php echo url::toText($URLVal4); ?>" id="URLBranding4" target="_blank"></a>
                                                                    <img src="<?php echo $folderPath; ?>images/group-4.png">
                                                                    <h5 class="headtxt" id="TextBranding4" ng-bind="branding4_text"></h5>
                                                                </div>

                                                                <div class="mySlidess branding5">
                                                                    <a href="<?php echo url::toText($URLVal5); ?>" id="URLBranding5" target="_blank"></a>
                                                                    <img src="<?php echo $folderPath; ?>images/group-5.png">
                                                                    <h5 class="headtxt" id="TextBranding5" ng-bind="branding5_text"></h5>
                                                                </div>
                                                            </div>

                                                            <div class="text-center">
                                                                <span class="dott"></span>
                                                                <span class="dott"></span>
                                                                <span class="dott"></span>
                                                                <span class="dott"></span>
                                                                <span class="dott"></span>
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
                                    <p>Customize the marketing messages</p>
                                </div>

                                <div class="col-md-6">
                                    <div class="brandBox">
                                        <!--<img id="myImg3" src="../assets/img/bg-img.png" alt="" class="img">-->
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
                                                        <div class="welcomePage text-left">
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
                                    <p>Customize the the software landing page</p>
                                </div>

                                <div class="col-md-6">
                                    <div class="brandBox">
                                        <!--<img id="myImg4" src="../assets/img/bg-img.png" alt="" class="img">-->
                                        <div class="col-md-12" id="emailContent">
                                            <div class="table-responsive ps">
                                                <table class="emailBox" cellpadding="0" cellspacing="0" width="100%" style="width: 460px; height: 460px; margin: 5% auto; max-width: 100%; background: #ccc; font-family: Montserrat, 'Open Sans', Helvetica, Arial, sans-serif; text-align: center; display: -webkit-box; padding: 5% 7% 0% 7%;">
                                                    <th></th>
                                                    <tr>
                                                        <td style="height: 23px; width: 333px; float: left; color: #1d253b; font-size: 12px; font-family: Montserrat, 'Open Sans', Helvetica, Arial, sans-serif; text-align: center; display: block;">
                                                            <table cellpadding="0" cellspacing="0" width="100%" align="center">
                                                                <th></th>
                                                                <tr>
                                                                    <td style="height: 23px; width: 333px; float: left; color: #1d253b;">
                                                                        <img src="https://demonew.nanoheal.com/DashboardDev805/assets/img/boxTop.png" alt="" border="0" align="center">
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>

                                                        <td style="height: auto; width: 333px; float: left; color: #1d253b; font-size: 12px; font-family: Montserrat, 'Open Sans', Helvetica, Arial, sans-serif; text-align: center;">
                                                            <table class="innerMailNew text-center" cellpadding="0" cellspacing="0" width="460" align="center" style="height: auto; width: 100%; color: #1d253b; font-size: 12px; font-family: Montserrat, 'Open Sans', Helvetica, Arial, sans-serif; text-align: center;">
                                                                <th></th>
                                                                <tr>
                                                                    <td style="height: 100%; width: 333px; color: #1d253b; background: #fff; padding: 7% 7% 0% 7%; text-align: center;">
                                                                        <img id="emailLogoPathy" src="<?php echo url::toText($logoImgPath); ?>" alt="" border="0" align="center">
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="height: 100%; width: 333px; color: #1d253b; background: #fff; padding: 7% 7% 7% 7%; font-size: 1.0625rem; font-family: Montserrat, Helvetica, Arial, sans-serif;">
                                                                        <span id="emailTitle" ng-bind="emailTitle"></span>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="height: 100%; width: 333px; color: #1d253b; background: #fff; padding: 0% 7% 7% 7%; font-size: 0.7rem; font-family: Montserrat, Helvetica, Arial, sans-serif;">
                                                                        <span ng-bind="emailBody"></span>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="height: 100%; width: 333px; color: #1d253b; background: #fff; padding: 0% 7% 7% 7%; font-size: 0.7rem; font-family: Montserrat, Helvetica, Arial, sans-serif;">
                                                                        <!--<button type="button" class="btn-md" style="font-family: Montserrat, Helvetica, Arial, sans-serif; background-color: #fa0f4b; background-image: linear-gradient(to bottom left, #fd2282, #fa0f4b, #fd2282); color: #ffffff; padding: 9.5px 40px; font-size: 0.875rem; border-radius: 0.4285rem; border: none; cursor: pointer;">Download</button>-->
                                                                        <a href="%url%" class="btn-md" target="_blank" style="text-decoration: none; background: #fa0f4b; color: #fff; padding: 11px 40px; border-radius: 0.4285rem; font-family: Montserrat, 'Open Sans', Helvetica, Arial, sans-serif; font-size: 0.875rem; font-weight: 500;">Download</a>
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="height: 100%; width: 333px; color: #1d253b; background: #fff; padding: 0% 7% 7% 7%; font-size: 0.7rem; font-family: Montserrat, Helvetica, Arial, sans-serif;">
                                                                        Or copy paste the link given below in a browser to start your download.
                                                                    </td>
                                                                </tr>

                                                                <tr>
                                                                    <td style="height: 100%; width: 333px; color: #1d253b; background: #fff; padding: 0% 7% 7% 7%; font-size: 0.7rem; font-family: Montserrat, Helvetica, Arial, sans-serif;">
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </table>


                                            </div>
                                        </div>
                                    </div>
                                    <p>Customize Emails</p>
                                </div>

                                <!-- The Modal -->
                                <!--                                <div id="myModal1" class="myModal">
                                    <span class="clsBtn">&times;</span>
                                    <img class="modalContent" id="img01">
                                </div>-->
                                <!--                                <div id="myModal2" class="myModal">
                                    <span class="clsBtn">&times;</span>
                                    <img class="modalContent" id="img02">
                                </div>-->
                                <!--                                <div id="myModal3" class="myModal">
                                    <span class="clsBtn">&times;</span>
                                    <img class="modalContent" id="img03">
                                </div>-->
                                <!--                                <div id="myModal4" class="myModal">
                                    <span class="clsBtn">&times;</span>
                                    <img class="modalContent" id="img04">
                                </div>-->
                            </div>
                        </div>
                    </div>

                    <div class="button col-md-12 text-center bottomBd">
                        <button type="button" class="swal2-confirm btn btn-success btn-sm btn-back" onclick="EditChanges()">Edit</button>
                        <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next" onclick="SaveAllChanges()">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    //
    //    // Get the modal
    //    var modal1 = document.getElementById('myModal1');
    //    var img1 = document.getElementById('myImg1');
    //    var modalImg1 = document.getElementById("img01");
    //    img1.onclick = function() {
    //        modal1.style.display = "block";
    //        modalImg1.src = this.src;
    //        captionText.innerHTML = this.alt;
    //    }
    //    
    //    var modal2 = document.getElementById('myModal2');
    //    var img2 = document.getElementById('myImg2');
    //    var modalImg2 = document.getElementById("img02");
    //    img2.onclick = function() {
    //        modal2.style.display = "block";
    //        modalImg2.src = this.src;
    //        captionText.innerHTML = this.alt;
    //    }
    //    
    //    var modal3 = document.getElementById('myModal3');
    //    var img3 = document.getElementById('myImg3');
    //    var modalImg3 = document.getElementById("img03");
    //    img3.onclick = function() {
    //        modal3.style.display = "block";
    //        modalImg3.src = this.src;
    //        captionText.innerHTML = this.alt;
    //    }
    //    
    //    var modal4 = document.getElementById('myModal4');
    //    var img4 = document.getElementById('myImg4');
    //    var modalImg4 = document.getElementById("img04");
    //    img4.onclick = function() {
    //        modal4.style.display = "block";
    //        modalImg4.src = this.src;
    //        captionText.innerHTML = this.alt;
    //    }
    //    
    //    // Get the <span> element that closes the modal
    //    var span = document.getElementsByClassName("clsBtn")[0];
    //
    //    // When the user clicks on <span> (x), close the modal
    //    span.onclick = function() {
    //        modal1.style.display = "none";
    //        modal2.style.display = "none";
    //        modal3.style.display = "none";
    //        modal4.style.display = "none";
    //    }
    //
</script>