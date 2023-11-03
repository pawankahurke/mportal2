<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?>
<style>
    em {
        margin-left: 20px;
        color: red;
    }
</style>

<div id="getchannelid" style="display:none;"><?php echo $_SESSION['user']['cd_eid'] ?></div>
<div id="getctype" style="display:none;"><?php echo $_SESSION['user']['cd_ctype'] ?></div>
<div id="getcompanyName" style="display:none;"><?php echo $_SESSION['user']['companyName'] ?></div>
<div id="getbusinessType" style="display:none;"><?php echo $_SESSION['user']['cd_ctype'] ?></div>
<div id="userloginfo" style="display:none;"><?php echo json_encode($_SESSION['userloginfo']) ?></div>
<div id="remaing_trialdays" style="display:none;"><?php echo $_SESSION["user"]["remaining_trial_days"] ?></div>
<div id="payinfo" style="display:none;"><?php echo $_SESSION["user"]["payinfo"] ?></div>
<div id="subchid" style="display:none;"><?php echo $_SESSION["user"]["subch_id"] ?></div>
<div id="currentwindow" style="display:none;"><?php echo $_SESSION["currentwindow"] ?></div>

<div id="introPopup" style="display:none;"><?php echo  $_SESSION["user"]["showIntroductoryPopup"] ?></div>

<div id="getfirstname" style="display:none;"><?php echo $_SESSION['user']['cfirstname'] ?></div>
<div id="getlastname" style="display:none;"><?php echo $_SESSION['user']['clastname'] ?></div>
<div id="getemail" style="display:none;"><?php echo $_SESSION['user']['cemail'] ?></div>
<div id="signupType" style="display:none;"><?php echo $_SESSION['user']['signuptype'] ?></div>


<div id="buyNowOptionDiv" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4 class="titleCard">Please select a SKU</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="buyNowOptionDiv">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle" id="continuePayButton" name="continuePayButton">
            <div class="toolTip" onclick="continuePayButton();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Continue to Payment</span>
            </div>
        </div>

        <div class="icon-circle" id="completePayButton" name="completePayButton">
            <div class="toolTip">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Complete Order</span>
            </div>
        </div>
        <div class="icon-circle" id="backToPayButton" name="backToPayButton" style="background-color: lightgray;">
            <div class="toolTip">
                <i class="tim-icons icon-double-left"></i>
                <span class="tooltiptext">Back</span>
            </div>
        </div>

        <div class="icon-circle" id="addBuyNowSite" onclick="addBuyNowSite();" style="display:none;">
            <div class="toolTip">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Create Site</span>
            </div>
        </div>

        <div class="icon-circle" id="copyDownloadUrl" onclick="copyDownloadUrl();" style="display:none;">
            <div class="toolTip">
                <i class="tim-icons icon-single-copy-04"></i>
                <span class="tooltiptext">Copy Download Url</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form id="purchaseFormData">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label skuSelectionDiv">
                        <label>
                            Please select the SKU
                        </label>
                        <em class="error">*</em>
                        <select class="selectpicker" data-style="btn btn-info" title="Select Sku" data-size="3" id="buyNowSkuList" name="buyNowSkuList">
                        </select>
                        <!--<span class="purchseLoader" style="float: right; margin-top: -44px; margin-right: -30%; display: none;"><img src="../assets/img/loader2.gif" alt="Loading..."></span>-->
                    </div>
                    <div class="customerInfo">
                        <h4>01 CUSTOMER INFO</h4>
                        <input type="hidden" class="form-control ci_py_carddetails" id="custInfo_skuid" placeholder="" name="custInfo_skuid">
                        <div class="form-group has-label">
                            <label>
                                First Name
                            </label>
                            <em class="error" id="required_custInfo_firstname">*</em>
                            <input class="form-control ci_py_carddetails" name="custInfo_firstname" id="custInfo_firstname" type="text" />
                        </div>
                        <div class="form-group has-label">
                            <label>
                                Last Name
                            </label>
                            <em class="error" id="required_custInfo_lastname">*</em>
                            <input class="form-control ci_py_carddetails" name="custInfo_lastname" id="custInfo_lastname" type="text" />
                        </div>
                        <div class="form-group has-label">
                            <label>
                                Email
                            </label>
                            <em class="error" id="required_custInfo_emailid">*</em>
                            <input class="form-control ci_py_carddetails" name="custInfo_emailid" id="custInfo_emailid" type="text" />
                        </div>
                        <div class="form-group has-label">
                            <label>
                                Address
                            </label>
                            <em class="error" id="required_custInfo_address">*</em>
                            <input class="form-control ci_py_carddetails" name="custInfo_address" id="custInfo_address" type="text" />
                        </div>
                        <div class="form-group has-label">
                            <label>
                                Country
                            </label>
                            <em class="error" id="required_custInfo_country">*</em>
                            <select class="form-control" title="Select Country" data-size="3" id="custInfo_country" name="custInfo_country" style="height: 30px; padding: 0px 0px 0px 8px;">
                                <?php require_once '../lib/l-countrylist.php'; ?>
                            </select>
                        </div>
                        <div class="form-group has-label">
                            <label>
                                City
                            </label>
                            <em class="error" id="required_custInfo_city">*</em>
                            <input class="form-control ci_py_carddetails" name="custInfo_city" id="custInfo_city" placeholder="" type="text" />
                        </div>
                        <div class="form-group has-label">
                            <label>
                                Postal Code
                            </label>
                            <em class="error" id="required_custInfo_postal">*</em>
                            <input class="form-control ci_py_carddetails" name="custInfo_postal" id="custInfo_postal" type="text" />
                        </div>
                        <div class="form-group has-label">
                            <label>
                                Phone
                            </label>
                            <em class="error" id="required_custInfo_phone">*</em>
                            <input class="form-control ci_py_carddetails" name="custInfo_phone" id="custInfo_phone" type="text" />
                        </div>
                    </div>

                    <div class="paymentInfo">
                        <h4>02 PAYMENT INFO</h4>
                        <br />
                        <h3><strong>Credit Card</strong> <span class="cards"><img src="../assets/img/visa.png" alt=""></span> <span class="cards"><img src="../assets/img/maestro.png" alt=""></span> <span class="cards"><img src="../assets/img/discover.png" alt=""></span> <span class="cards"><img src="../assets/img/american-express.png" alt=""></span></h3>
                        <p>Safe money transfer using your bank account. Visa, Maestro, Discover,<br /> American Express.</p>
                        <div class="form-group has-label">
                            <label>
                                Card Number
                            </label>
                            <em class="error" id="required_carddtls_cardno">*</em>
                            <input class="form-control py_carddetails" name="carddtls_cardno" id="carddtls_cardno" type="text" maxlength="16" />
                        </div>
                        <div class="form-group has-label">
                            <label>
                                Name on Card
                            </label>
                            <em class="error" id="required_carddtls_cardname">*</em>
                            <input class="form-control py_carddetails" name="carddtls_cardname" id="carddtls_cardname" type="text" />
                        </div>
                        <div class="form-group has-label">
                            <label>
                                CVV Code
                            </label>
                            <em class="error" id="required_carddtls_cvv">*</em>
                            <input class="form-control py_carddetails" name="carddtls_cvv" id="carddtls_cvv" type="text" maxlength="3" onkeypress="return restictAlphaSC(event)" />
                        </div>
                        <div class="form-group has-label">
                            <label>
                                Expiry Date
                            </label>
                            <em class="error" id="required_carddtls_expiry">*</em>
                            <input class="form-control py_carddetails" name="carddtls_expiry" id="carddtls_expiry" type="text" placeholder="MM/YY" onkeyup="formatString(event)" />
                        </div>
                    </div>

                    <div class="buyNowSiteCreationDiv" style="display:none;">
                        <h4>Create Site</h4>
                        <br />
                        <div class="form-group has-label">
                            <label>
                                Site Name *
                            </label>
                            <input class="form-control" name="custBuyNowSiteName" id="custBuyNowSiteName" type="text" />
                            <input type="hidden" class="form-control custdetails" id="custBuyNowLickey" placeholder="" name="custBuyNowLickey">
                        </div>
                        <div class="form-group has-label download_BuyNowurl_div" style="display:none;">
                            <label>
                                Download URL *
                            </label>
                            <input class="form-control" name="download_buyNowurl" id="download_buyNowurl" type="text" />
                        </div>
                    </div>

                </div>
                <div>
                    <span id="newProvisionErrorMsg" name="newProvisionErrorMsg"></span>
                </div>
            </div>
        </form>
    </div>
</div>


<div id="showTrialFlowDiv" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Trial Flow</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="showTrialFlowDiv">&times;</a>
    </div>

    <div class="form table-responsive white-content">
        <h3><strong>You Have <?php echo $_SESSION["user"]["showNHleftDays"]; ?> Days of Trial Left</strong></h3>
        <h4>Please install Nanoheal on atleast one device to continue.</h4>
        <form id="purchaseFormData">
            <div class="card">
                <div class="card-body">

                    <div class="form-group has-label">
                        <label>
                            Download URL
                        </label>
                        <input class="form-control" name="trial_msp_download_url" id="trial_msp_download_url" type="text" value="<?php echo url::toText($_SESSION["user"]["firstDownloadUrl"]); ?>" readonly="" />
                    </div>

                    <div class="form-group has-label">
                        <h4>Click on the download button to install Nanoheal on the current device</h4>
                        <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="firstDownloadUrlDiv2" name="firstDownloadUrlDiv2">Download</button>
                    </div>

                    <div class="form-group has-label">
                        <h4>Click on the Copy URL to copy and install Nanoheal on the different device</h4>
                        <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="firstCopyUrlDiv2" name="firstCopyUrlDiv2">Copy URL</button>
                    </div>

                </div>
            </div>
        </form>
    </div>
    <div class="button col-md-12 text-center">
        <!-- <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" oncliick="closeTrial();">CONTINUE WITH FREE TRIAL</button> -->
    </div>
</div>