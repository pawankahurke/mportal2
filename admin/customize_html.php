<!-- content starts here  -->
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
require_once '../lib/l-db.php';

$db = pdo_connect();
$custData = $_SESSION['brandingconfval'];
if ($custData == '') {
    $logged_user = $_SESSION["user"]["logged_username"];
    $sql = "select id, customer, emailbody from " . $GLOBALS['PREFIX'] . "core.Customers where username=? order by id desc limit 1";
    $pdo = $db->prepare($sql);
    $pdo->execute([$logged_user]);
    $res = $pdo->fetch(PDO::FETCH_ASSOC);

    $cid = $res['customer'];
    $pid = $res['id'];
    $customerName = $res['customer'];
    $emailBody = $res['emailbody'];
} else {
    $data = explode('_', $custData);
    $part2 = substr("$custData", (strrpos($custData, '_') + 1));
    $pid = $part2;
    $sql = "select customer, emailbody from " . $GLOBALS['PREFIX'] . "core.Customers where id=? limit 1";
    $pdo = $db->prepare($sql);
    $pdo->execute([$pid]);
    $res = $pdo->fetch(PDO::FETCH_ASSOC);
    $cid = $res['customer'];
    $emailBody = $res['emailbody'];
    $customerName = $cid;
}

$cfname = 'cust_' . $cid . '_' . $pid;

$customerInfo = file_get_contents('../../Branding/' . $cfname . '/' . $cfname . '.json', 'r');
$customerData = safe_json_decode($customerInfo, true);

$folderPath = '';
$logoImgPath = '../assets/img/logo.png';
$shortcutImgPath = getenv('VISUALISATION_SERVICE_API_URL') . "/dashboard-customization/favicon.ico" ?>
$bgimage = '../assets/img/bask-bg-img.png';
$headerColor = 'rgba(255,255,255,0)';
$buttonColor = '#fa0f4b';
$footerColor = '#050d30';
$welcomeMsge = 'Welcome to Nanoheal Client Setup';
$termsLink = '';
$supportPhone = '';
$chatUrl = '';

$rotationSpeed = '2000';

$landingWelcomeTitle = 'Welcome to Nanoheal Client Setup';
$landingWelcomeMsg = "Go beyond seeing a problem and fix it using Nanoheal. Nanoheal includes a variety of troubleshooters designed to quickly diagnose and automatically solve various computer problems.";
$landingBgImagePath = '../assets/img/bask-bg-img.png';

$emailSubject = 'Client Download URL';
$emailTitle = 'Here is your download link';
if ($emailBody == '') {
$emailBody = 'Use the link below to download Nanoheal on your device. This link may be used upto 3 times before it expires.';
}
$landingBGColor = '#ccc';
$landingFGColor = '#fff';
$landingBtnColor = '#fa0f4b';
$landingBtnFontColor = '#fff';
$landingTextColor = '#000';

$URLVal1 = 'www.nanoheal.com';
$URLVal2 = 'www.nanoheal.com';
$URLVal3 = 'www.nanoheal.com';
$URLVal4 = 'www.nanoheal.com';
$URLVal5 = 'www.nanoheal.com';
$TXTVal1 = "Getting your system set-up for the first time will take just a few minutes - it is worth the wait! the very first one";
$TXTVal2 = "Most of ours solutions only take a few minutes - if only everything could be that simple!";
$TXTVal3 = "Did you know that Nanoheal can find and fix issues before you are even aware of them?";
$TXTVal4 = "Most of our fixes take just a second or two - if only everything could be repaired so easily.";
$TXTVal5 = "Did you know that Nanoheal can fix issues before you are even aware of them?";

if (isset($customerData)) {
$folderPath = $customerData['folderPath'] . '/';
$logoImgPath = isset($customerData['logoPath']) ? $customerData['logoPath'] : $logoImgPath;
$shortcutImgPath = isset($customerData['shortccutPath']) ? $customerData['shortccutPath'] : $shortcutImgPath;
$bgimage = isset($customerData['bgimagePath']) ? $customerData['bgimagePath'] : $bgimage;
$headerColor = isset($customerData['headerColor']) ? $customerData['headerColor'] : $headerColor;
$buttonColor = isset($customerData['buttonColor']) ? $customerData['buttonColor'] : $buttonColor;
$footerColor = isset($customerData['footerColor']) ? $customerData['footerColor'] : $footerColor;
$welcomeMsge = isset($customerData['welcomeMsg']) ? $customerData['welcomeMsg'] : $welcomeMsge;
$termsLink = isset($customerData['termsLink']) ? $customerData['termsLink'] : $termsLink;
$supportPhone = $customerData['supportphone'];
$chatUrl = $customerData['chatUrl'];

$rotationSpeed = isset($customerData['rotationSpeed']) ? $customerData['rotationSpeed'] : '2000';
$URLVal1 = isset($customerData['url1']) ? $customerData['url1'] : $URLVal1;
$URLVal2 = isset($customerData['url2']) ? $customerData['url2'] : $URLVal2;
$URLVal3 = isset($customerData['url3']) ? $customerData['url3'] : $URLVal3;
$URLVal4 = isset($customerData['url4']) ? $customerData['url4'] : $URLVal4;
$URLVal5 = isset($customerData['url5']) ? $customerData['url5'] : $URLVal5;
$TXTVal1 = isset($customerData['txt1']) ? $customerData['txt1'] : $TXTVal1;
$TXTVal2 = isset($customerData['txt2']) ? $customerData['txt2'] : $TXTVal2;
$TXTVal3 = isset($customerData['txt3']) ? $customerData['txt3'] : $TXTVal3;
$TXTVal4 = isset($customerData['txt4']) ? $customerData['txt4'] : $TXTVal4;
$TXTVal5 = isset($customerData['txt5']) ? $customerData['txt5'] : $TXTVal5;
$landingWelcomeTitle = isset($customerData['landingWelcomeTitle']) ? $customerData['landingWelcomeTitle'] : $landingWelcomeTitle;
$landingWelcomeMsg = isset($customerData['landingWelcomeMsg']) ? $customerData['landingWelcomeMsg'] : $landingWelcomeMsg;
$landingBgImagePath = isset($customerData['landingBgImagePath']) ? $customerData['landingBgImagePath'] : $landingBgImagePath;

$emailSubject = isset($customerData['emailSubject']) ? $customerData['emailSubject'] : $emailSubject;
$emailTitle = isset($customerData['emailTitle']) ? $customerData['emailTitle'] : $emailTitle;
$emailBody = isset($customerData['emailBody']) ? $customerData['emailBody'] : $emailBody;
$landingBGColor = isset($customerData['LandingBGColor']) ? $customerData['LandingBGColor'] : $landingBGColor;
$landingFGColor = isset($customerData['LandingFGColor']) ? $customerData['LandingFGColor'] : $landingFGColor;
$landingBtnColor = isset($customerData['LandingBtnColor']) ? $customerData['LandingBtnColor'] : $landingBtnColor;
$landingBtnFontColor = isset($customerData['LandingBtnFontColor']) ? $customerData['LandingBtnFontColor'] : $landingBtnFontColor;
$landingTextColor = isset($customerData['LandingTextColor']) ? $customerData['LandingTextColor'] : $landingTextColor;
}
?>

<style type="text/css">
    .bg-setup-img {
        background: url(<?php echo $bgimage; ?>) no-repeat top left #eee;
    }

    .lp-bg-setup-img {
        background: url(<?php echo $landingBgImagePath; ?>) no-repeat top left #eee;
    }

    .ctmz-page2 {
        display: none;
    }

    .closebtn a {
        border: 1px solid #C1C1C1;
        border-radius: 13px;
        padding: 0px 7px 0px 7px;
        display: initial;
        box-shadow: 0px 0px 3px 1px #ccc;
        -webkit-box-shadow: 0px 0px 5px 1px #ccc;
    }
</style>

<div class="rightCol installerUI" style="display: block;">
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
                                            <div class="mainBox bg-setup-img">
                                                <div class="headerWrap">
                                                    <div class="left">
                                                        <img class="logo" alt="logo" src="<?php echo url::toText($logoImgPath); ?>">
                                                    </div>

                                                    <div class="left" style="display: none">
                                                        <img class="shorcutIcon" alt="shortcutlogo" src="<?php echo url::toText($shortcutImgPath); ?>">
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
                                                    <div class="left"> Need help? <span ng-bind="supportPhNo"></span> </div>

                                                    <div class="right"> <span>Live Chat</span> </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 right">
                                    <div class="tabInner">
                                        <input type="hidden" id="customizeCID" value="<?php echo url::toText($cid); ?>" />
                                        <input type="hidden" id="customizePID" value="<?php echo url::toText($pid); ?>" />
                                        <input type="hidden" id="loggedType" value="<?php echo url::toText($_SESSION['user']['dashboardLogin']); ?>" />
                                        <input type="hidden" value="<?php echo url::toText($URLVal2); ?>" id="URL_2">
                                        <input type="hidden" value="<?php echo url::toText($URLVal3); ?>" id="URL_3">
                                        <input type="hidden" value="<?php echo url::toText($URLVal4); ?>" id="URL_4">
                                        <input type="hidden" value="<?php echo url::toText($URLVal5); ?>" id="URL_5">
                                        <input type="hidden" value="<?php echo url::toText($TXTVal2); ?>" id="TXT_2">
                                        <input type="hidden" value="<?php echo url::toText($TXTVal3); ?>" id="TXT_3">
                                        <input type="hidden" value="<?php echo url::toText($TXTVal4); ?>" id="TXT_4">
                                        <input type="hidden" value="<?php echo url::toText($TXTVal5); ?>" id="TXT_5">

                                        <div class="closebtn"><a href="javascript:void(0)" onclick="goToBranding();" data-bs-target="rsc-add-container">&times;</a></div>
                                        <p class="col-md-12 paraTxt ctmz-page1">Choose the color theme that you would want to use on the software, you can change the header, footer and button colors, and upload your custom logo.</p>
                                        <p class="col-md-12 paraTxt ctmz-page2">This is the screen your users would see as soon as they open the installer, you can customise it with custom messaging and imagery.</p>
                                        <div class="colBox ctmz-page1">

                                            <h5 class="card-title">Upload your logo <span style="color:red">(only PNG file allowed)</span></h5>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <p class="col-md-2 txtsm">File selected : </p>
                                                    <p class="col-md-10 txt"><span id="logo_name"></span></p>
                                                </div>
                                            </div>

                                            <span class="btn btn-round btn-rose btn-file btn-sm btn-cursor">
                                                <span class="fileinput-new">Add Photo</span>
                                                <input type="file" id="pub_logo" name="pub_logo" accept="image/png" />
                                            </span>

                                            <span class="btn btn-success btn-round btn-sm" id="remove_logo">
                                                <span class="fileinput-new">Remove</span>
                                            </span>

                                            <span class="logo_loader" style="display:none;">
                                                <img class="" alt="loader..." src="../assets/img/loader-sm.gif">
                                            </span>
                                            <br />

                                            <!--Shortcut Icon-->
                                            <!--<h5 class="card-title">Upload your shortcut icon <span style="color:red">(only ICO file allowed)</span></h5>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <p class="col-md-2 txtsm">File selected : </p>
                                                    <p class="col-md-10 txt"><span id="shortcut_logo"></span></p>
                                                </div>
                                            </div>

                                            <span class="btn btn-round btn-rose btn-file btn-sm btn-cursor">
                                                <span class="fileinput-new">Add Photo</span>
                                                <input type="file" id="shortcut_icon" name="shortcut_icon" accept="ico" />
                                            </span>

                                            <span class="btn btn-success btn-round btn-sm" id="remove_icon">
                                                <span class="fileinput-new">Remove</span>
                                            </span>

                                            <span class="shortcut_loader" style="display:none;">
                                                <img class="" alt="loader..." src="../assets/img/loader-sm.gif">
                                            </span>
                                            <br/><br/>-->
                                        </div>

                                        <div class="colBox ctmz-page2">
                                            <h5 class="card-title">Upload a background image<span style="color:red">(only PNG file allowed)</span></h5>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <p class="col-md-2 txtsm">File selected :</p>
                                                    <p class="col-md-10 txt"><span id="bgimg_name"></span></p>
                                                </div>
                                            </div>

                                            <span class="btn btn-round btn-rose btn-file btn-sm btn-cursor">
                                                <span class="fileinput-new">Add Photo</span>
                                                <input type="file" id="pub_bgimage" name="pub_bgimage" accept="image/*" />
                                            </span>

                                            <span class="btn btn-success btn-round btn-sm" id="remove_bgimg">
                                                <span class="fileinput-new" data-dismiss="fileinput">Remove</span>
                                            </span>

                                            <span class="bgimg_loader" style="display:none;">
                                                <img class="" alt="loader..." src="../assets/img/loader-sm.gif">
                                            </span>
                                        </div>

                                        <br />
                                        <div class="form-group has-label ctmz-page2">
                                            <label>Enter the welcome message (<span id="remChar">50</span> Characters remaining)</label>
                                            <input type="text" id="welcomeMsg" name="welcomeMsg" class="form-control" ng-model="welcomeMsg" maxlength="49" autocomplete="off" />
                                            <input type="hidden" id="welcomeMsgSavedVal" value="<?php echo url::toText($welcomeMsge); ?>" />
                                        </div>

                                        <br />
                                        <div class="row ctmz-page1">
                                            <div class="col-md-12">
                                                <h5>Select Colors</h5>
                                            </div>

                                            <div class="col-md-6 col-sm-12 col-xs-12">
                                                <p>Header</p>
                                                <p>Footer</p>
                                                <p>Button</p>
                                            </div>

                                            <div class="col-md-6 col-sm-12 col-xs-12">
                                                <input type="text" id="headerColor" name="headerColor" class="form-control colorpicker-element simple-color-picker" value="<?php echo url::toText($headerColor); ?>" autocomplete="off">
                                                <input type="text" id="footerColor" name="footerColor" class="form-control colorpicker-element simple-color-picker" value="<?php echo url::toText($footerColor); ?>" autocomplete="off">
                                                <input type="text" id="buttonColor" name="buttonColor" class="form-control colorpicker-element simple-color-picker" value="<?php echo url::toText($buttonColor); ?>" autocomplete="off">
                                            </div>
                                        </div>

                                        <div class="form-group ctmz-page2">
                                            <label>Enter T&C link : <span id="tcErr" style="color:red;"></span></label>
                                            <input class="form-control" name="enter" type="text" id="termsAndConditions" name="termsAndConditions" value="<?php echo url::toText($termsLink); ?>" required />
                                        </div>

                                        <div class="form-group ctmz-page2">
                                            <label>Enter Support Phone Number : <span id="phnErr" style="color:red;"></span></label>
                                            <input class="form-control" type="text" placeholder="Enter Support Phone Number" id="supportPhone" name="supportPhone" maxlength="15" ng-model="supportPhNo" required />
                                            <input type="hidden" id="supportPhoneVal" value="<?php echo url::toText($supportPhone); ?>" />
                                        </div>

                                        <div class="form-group ctmz-page2">
                                            <label>Enter Live Chat URL : <span id="chatErr" style="color:red;"></span></label>
                                            <input class="form-control" type="text" id="chatUrl" name="chatUrl" value="<?php echo url::toText($chatUrl); ?>" required />
                                        </div>

                                    </div>

                                    <div class="button col-md-12 text-center btBtn">
                                        <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next ctmz-page1" onclick="changeDisplay('ctmz-page1', 'ctmz-page2', 'Upload installer messaging');">Next</button>
                                        <span class="txt-sm ctmz-page1" onclick="changeDisplay('ctmz-page1', 'ctmz-page2', 'Upload installer messaging');">Skip and use defaults</span>
                                        <button type="button " class="swal2-confirm btn btn-success btn-sm btn-back ctmz-page2" onclick="changeDisplay('ctmz-page2', 'ctmz-page1', 'Customise the theme and branding');">Prev</button>
                                        <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next ctmz-page2" onclick="saveConfiguration()">Save and Continue</button>
                                        <span class="txt-sm ctmz-page2" onclick="goToScreen('brandingUI');">Skip and use defaults</span>
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