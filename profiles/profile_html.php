<?php
unset($_SESSION['pwvardata']);

include_once '../lib/l-profilewiz.php';
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$baseProfileData = PRWZ_getProfileDetails();

$level_1 = $level_2 = $level_3 = [];
foreach ($baseProfileData as $value) {
    if ($value['type'] == 'L1') {
        $level_1[] = $value;
    } else if ($value['type'] == 'L2') {
        $level_2[] = $value;
    } else if ($value['type'] == 'L3') {
        $level_3[] = $value;
    }
}

$tilesList = PRWZ_getTilesList();
$dartTileToken = uniqid('pwt');

$siteList = $_SESSION['user']['site_list'];
$groupList = PRWZ_getGroupDetails();
?>
<div class="content white-content profilePage">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body" id="profwiz-basic">
                    <?php
                    //   nhRole::checkRoleForPage('profilewizard');
                    $res = true; // nhRole::checkModulePrivilege('profilewizard');
                    if ($res) {
                    ?>
                        <div id="loader" class="loader" data-qa="loader" style="position: absolute;bottom: 50%;right:50%;">
                            <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                        </div>
                        <table id="profileWizardGrid" class="nhl-datatable table table-striped">
                            <thead>
                                <tr>
                                    <th id="key0" headers="profilename" class="profilename">
                                        Profile Name
                                        <i class="fa fa-caret-down cursorPointer direction" id="profilename1" onclick="addActiveSort('asc', 'profilename'); get_ProfileWizardDetails(1,'','profilename', 'asc');sortingIconColor('profilename1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="profilename2" onclick="addActiveSort('desc', 'profilename'); get_ProfileWizardDetails(1,'','profilename', 'desc');sortingIconColor('profilename2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key1" headers="createdby" class="createdby">
                                        Created By
                                        <i class="fa fa-caret-down cursorPointer direction" id="createdby1" onclick="addActiveSort('asc', 'createdby'); get_ProfileWizardDetails(1,'','createdby', 'asc');sortingIconColor('createdby1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="createdby2" onclick="addActiveSort('desc', 'createdby'); get_ProfileWizardDetails(1,'','createdby', 'desc');sortingIconColor('createdby2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key2" headers="createdtime" class="createdtime">
                                        Created On
                                        <i class="fa fa-caret-down cursorPointer direction" id="createdtime1" onclick="addActiveSort('asc', 'createdtime'); get_ProfileWizardDetails(1,'','createdtime', 'asc');sortingIconColor('createdtime1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="createdtime2" onclick="addActiveSort('desc', 'createdtime'); get_ProfileWizardDetails(1,'','createdtime', 'desc');sortingIconColor('createdtime2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key3" headers="modifiedtime" class="modifiedtime">
                                        Modified On
                                        <i class="fa fa-caret-down cursorPointer direction" id="modifiedtime1" onclick="addActiveSort('asc', 'modifiedtime'); get_ProfileWizardDetails(1,'','modifiedtime', 'asc');sortingIconColor('modifiedtime1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="modifiedtime2" onclick="addActiveSort('desc', 'modifiedtime'); get_ProfileWizardDetails(1,'','modifiedtime', 'desc');sortingIconColor('modifiedtime2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key4" headers="sites" class="sites">
                                        Linked Sites
                                        <i class="fa fa-caret-down cursorPointer direction" id="sites1" onclick="addActiveSort('asc', 'sites'); get_ProfileWizardDetails(1,'','sites', 'asc');sortingIconColor('sites1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="sites2" onclick="addActiveSort('desc', 'sites'); get_ProfileWizardDetails(1,'','sites', 'desc');sortingIconColor('sites2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key5" headers="groups" class="groups">
                                        Linked Groups
                                        <i class="fa fa-caret-down cursorPointer direction" id="groups1" onclick="addActiveSort('asc', 'groups'); get_ProfileWizardDetails(1,'','groups', 'asc');sortingIconColor('groups1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="groups2" onclick="addActiveSort('desc', 'groups'); get_ProfileWizardDetails(1,'','groups', 'desc');sortingIconColor('groups2')" style="font-size:18px"></i>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    <?php
                    }
                    ?>
                    <div id="largeDataPagination"></div>
                </div>
                <form action="profile.php" method="POST" onsubmit="return false;">
                    <div class="card-body" style="display: none;" id="profwiz-add">
                        <div class="toolbar">
                            <div class="bullDropdown leftDropdown">
                                <div class="dropdown">
                                    <span>Add a Profile</span>
                                </div>
                            </div>

                            <div class="bullDropdown">
                                <div class="dropdown">
                                    <div id="close-swd-widget" class="r-ic" onclick="confirmCancelOperation();"><i class="tim-icons icon-simple-remove"></i></div>
                                </div>
                            </div>
                        </div>

                        <!--<div class="closebtn" title="Close"><a href="index.php" data-bs-target="rsc-add-container">&times;</a></div>-->

                        <div class="row clearfix innerPage">
                            <div class="col-md-12">
                                <div class="col-md-2 col-sm-2 lf-equalHeight" id="dispMenu" style="padding-right: 20px !important;">
                                    <div class="col-md-12 addBox cbl active" data-disp="1">1 <span>Basic Troubleshooters Dashboard</span></div>
                                    <div class="col-md-12 addBox" data-disp="2">2 <span>Basic Troubleshooters Client</span></div>
                                    <div class="col-md-12 addBox" data-disp="3">3 <span>Custom Troubleshooters</span></div>
                                    <div class="col-md-12 addBox" data-disp="4">4 <span>Configure DARTS for Custom Troubleshooters</span></div>
                                    <div class="col-md-12 addBox" data-disp="5">5 <span>Set Troubleshooters Sequence</span></div>
                                    <div class="col-md-12 addBox" data-disp="6">6 <span>Review and Save</span></div>
                                </div>

                                <!-- hidden fields -->
                                <input type="hidden" name="dartTileToken" id="dartTileToken" value="<?php echo url::toText($dartTileToken); ?>">
                                <input type="hidden" id="selected">
                                <input type="hidden" id="reviewdata" value="client">

                                <!-- profileOne starts here -->

                                <div class="col-md-10 col-sm-10 table-responsive rt-equalHeight profileOne eachPWrap activeNow" id="profile1" style="display: block;">
                                    <div class="form-group has-label">
                                        <span class="error">*</span>
                                        <label>Name of the Profile <span id="err_profile-name"></span></label>
                                        <input data-qa="ph-profile-name" id="profile-name" name="profile-name" class="form-control" type="text" onkeyup="trackInputChange(this.value,this.id,this)" data-required="true" data-label="Profile name">
                                    </div>

                                    <div id="accordion" class="profileList">
                                        <h5>Select the tiles that you want to be part of this Profile for <b>Dashboard</b></h5>

                                        <div id="ck-uck-all" class="form-check mt-3">
                                            <label class="form-check-label">
                                                <input class="form-check-input" type="checkbox" checked="checked">
                                                <span class="form-check-sign"></span>
                                                <p>Check / Uncheck All</p>
                                            </label>
                                        </div>

                                        <?php foreach ($level_2 as $val_2) { ?>
                                            <div class="card">
                                                <div class="card-header" id="headingOne">
                                                    <div class="form-check">

                                                        <label class="form-check-label">
                                                            <input class="form-check-input" checked="" type="checkbox" name="global" value="<?php echo url::toText($val_2['mid']); ?>" onclick="checkBoxUpdate(this, '');">
                                                            <span class="form-check-sign"></span>
                                                            <h5 class="mb-0 d-inline">
                                                                <button class="collapsible btn-link mycollapse">
                                                                    <?php echo $val_2['profile']; ?>
                                                                </button>
                                                            </h5>
                                                        </label>

                                                    </div>
                                                </div>

                                                <div id="collapse<?php echo $val_2['mid']; ?>" class="collapse my-collapse-content" aria-labelledby="headingOne" data-parent="#accordion">
                                                    <div class="card-body child" id="child<?php echo $val_2['mid']; ?>">
                                                        <?php
                                                        foreach ($level_3 as $val_3) {
                                                            if ($val_2['parentId'] == $val_3['page']) {
                                                                $checked_l3 = '';
                                                                if ($val_3['Enable/Disable'] == 1 || $val_3['Enable/Disable'] == 3) {
                                                                    $checked_l3 = 'checked';
                                                                }
                                                        ?>
                                                                <div class="card">
                                                                    <div class="form-check">
                                                                        <label class="form-check-label">
                                                                            <input class="form-check-input" <?php echo $checked_l3; ?> type="checkbox" name="global" value="<?php echo url::toText($val_3['mid']); ?>" onclick="checkBoxUpdate(this, '<?php echo $val_2['mid']; ?>');">
                                                                            <span class="form-check-sign"></span>
                                                                            <div class="card-header">
                                                                                <span class="form-check-sign"></span> <?php echo $val_3['profile'] . ' [ ' . $val_3['OS'] . ' ]'; ?>
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                        <?php
                                                            }
                                                        }
                                                        ?>
                                                    </div>
                                                </div>

                                            </div>
                                        <?php } ?>

                                    </div>



                                </div>

                                <div class="col-md-10 col-sm-10 table-responsive rt-equalHeight profileOne eachPWrap" id="profile2" style="display: none;">
                                    <!-- Client Profile List -->
                                    <div id="accordion" class="clientProfileList" style="display: none;">

                                    </div>
                                </div>

                                <!-- profileTwo starts here -->

                                <div class="col-md-10 col-sm-10 table-responsive rt-equalHeight profileTwo eachPWrap" id="profile3" style="display: none;">
                                    <div class="Box title-grid" id="tileData0">
                                        <span class="bx-cnt-spn">#1</span>
                                        <div class="form-group has-label">
                                            <span class="error">*</span>
                                            <label class="tgr-title">Name of custom troubleshooter <span id="err_tile-name"></span></label>
                                            <input data-qa="ph-Name_of_custom_troubleshooter" class="form-control w1-name" id="tile-name" type="text" name="tile-name[]" onkeyup="trackInputChange(this.value,this.id,this)" data-required="true" data-label="Name of the title">
                                        </div>

                                        <div class="form-group has-label">
                                            <span class="error">*</span>
                                            <label>Description of custom troubleshooter</label>
                                            <input data-qa="ph-Description_of_custom_troubleshooter" class="form-control" type="text" name="tile-description[]" data-required="true" data-label="Description">
                                            <span id="name_err"></span>
                                        </div>

                                        <div class="row">
                                            <label class="col-sm-12 col-form-label">Visibility</label>

                                            <div class="col-sm-6 checkbox-radios">
                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label">
                                                        <input class="form-check-input title-visibiity" type="radio" name="visibility[0]" value="3" checked="checked">
                                                        <span class="form-check-sign"></span> Show only on Dashboard
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-sm-6 checkbox-radios">
                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label">
                                                        <input class="form-check-input title-visibiity" type="radio" name="visibility[0]" value="1">
                                                        <span class="form-check-sign"></span> Show on Dashboard and Client
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div data-required-any-one-parent="this" class="row" data-group-name="OS">
                                            <label class="col-sm-12 col-form-label"><span class="error opsys">*</span>Operating System</label>

                                            <div class="col-sm-2 checkbox-radios">
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input class="form-check-input os-ck-bx" data-required-any-one="true" value="Windows" type="checkbox" name="os-win[0]" data-name="os-win">
                                                        <span class="form-check-sign"></span> Windows
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-sm-2 checkbox-radios">
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input class="form-check-input os-ck-bx" data-required-any-one="true" value="Android" type="checkbox" name="os-android[0]" data-name="os-android">
                                                        <span class="form-check-sign"></span> Android
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-sm-2 checkbox-radios">
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input class="form-check-input os-ck-bx" data-required-any-one="true" value="Mac" type="checkbox" name="os-mac[0]" data-name="os-mac">
                                                        <span class="form-check-sign"></span> Mac
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-sm-2 checkbox-radios">
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input class="form-check-input os-ck-bx" data-required-any-one="true" value="iOS" type="checkbox" name="os-ios[0]" data-name="os-ios">
                                                        <span class="form-check-sign"></span> iOS
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-sm-2 checkbox-radios">
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input class="form-check-input os-ck-bx" data-required-any-one="true" value="Linux" type="checkbox" name="os-linux[0]" data-name="os-linux">
                                                        <span class="form-check-sign"></span> Linux
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="col-sm-2 checkbox-radios">
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input class="form-check-input os-ck-bx" data-required-any-one="true" value="Ubuntu" type="checkbox" name="os-ubuntu[0]" data-name="os-ubuntu">
                                                        <span class="form-check-sign"></span> Ubuntu
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-sm-4">
                                                <label for="vislist">
                                                    Select the Darts that will be part of the tile
                                                </label>
                                            </div>
                                            <div class="col-sm-4">
                                                <label for="vislist">
                                                    Enter Dart description
                                                </label>
                                            </div>
                                        </div>
                                        <div class="row each-dart-box">
                                            <div class="col-sm-4">
                                                <span class="error left">*</span>
                                                <select data-required="true" data-label="Dart" class="form-control dart-select left" name="tile-darts[0][]" data-style="btn btn-info" title="Select Dart" data-size="4">
                                                    <option value="" selected="selected">Select Dart</option>
                                                    <?php foreach ($tilesList as $key => $value) { ?>
                                                        <option value="<?php echo url::toText($key); ?>" title="<?php echo url::toText($value); ?>"><?php echo $value; ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <div class="col-sm-4">
                                                <span class="error left">*</span>
                                                <input type="text" class="form-control dart-input-title" name="tile-dart-name[0][]" data-required="true" data-label="Dart description" placeholder="Dart description" />
                                            </div>
                                            <div class="col-sm-4 rmv-dart-box" style="display:none">
                                                <span><i class="tim-icons icon-simple-delete w2-rmv-dart-box"></i></span>
                                            </div>
                                        </div>

                                        <div class="row addmore">
                                            <div class="col-md-12">
                                                <span><i class="tim-icons icon-simple-add w2-add-dart-box"></i></span>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row remove remove-title-grid" style="display:none">
                                        <div class="col-md-12">
                                            <span class="remove-pwrap" onclick="removeTitleGrid($(this), event)">Remove</span>
                                        </div>
                                    </div>

                                    <div class="row addmore">
                                        <div class="col-md-12" id="addNewTiles">
                                            <span><i class="tim-icons icon-simple-add"></i> Add more...</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- profileThree starts here -->

                                <div class="col-md-10 col-sm-10 table-responsive rt-equalHeight profileThree eachPWrap" id="profile4" style="display: none;">
                                    <h5>Configure the darts that are part of the custom troubleshooters tiles</h5>
                                    <div class="w3-dart-elements-clone" style="display:none">
                                        <button class="collapsible mycollapse">&nbsp</button>
                                        <div style="display:block;" class="my-collapse-content">
                                            <span>
                                                <p><a class="w3-dart-title" href="javascript:void(0)">&nbsp</a> <i class="tim-icons icon-pencil open-dart-console" data-bs-target="dart-role"></i></p>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <!-- profileFour starts here -->

                                <div class="col-md-10 col-sm-10 table-responsive rt-equalHeight profileFour eachPWrap" id="profile5" style="display: none;">
                                    <h5>Configure the sequence that are part of custom troubleshooter tiles</h5>
                                    <div class="w4-dart-elements-clone" style="display:none">
                                        <button class="collapsible mycollapse">&nbsp;</button>
                                        <div style="display:block" class="my-collapse-content">
                                            <div class="row">
                                                <div class="col-md-2 w4-dart-1n-wrap">
                                                    <span>&nbsp;</span>
                                                    <span>
                                                        <p>&nbsp;</p>
                                                    </span>
                                                </div>

                                                <div class="col-md-2 w4-dart-2n-wrap">
                                                    <span>Sequence</span>
                                                    <span><input name="dart-sequence" class="form-control form-control-sm" type="number" min="1" value="1" data-required="true" data-label="dart sequence"></span>
                                                </div>
                                            </div>

                                            <!--<div class="col-md-12">Schedule</div>-->
                                        </div>
                                    </div>
                                </div>

                                <!-- profileFive starts here -->

                                <div class="col-md-10 col-sm-10 rt-equalHeight profileFive eachPWrap" id="profile6" style="display: none;">
                                    <button type="button" class="swal2-confirm btn btn-simple btn-sm btn-next review-tiles" id="render-dash" data-qa="ph-render-dash" onclick="renderProfileConfiguration();" style="width: 170px !important;">Review Dashboard Tiles</button>
                                    <button type="button" class="swal2-confirm btn btn-simple btn-sm btn-next review-tiles" id="render-clnt" data-qa="ph-render-clnt" onclick="renderClientProfileConfiguration();" style="width: 140px !important;">Review Client Tiles</button>
                                    <div class="Box">
                                        <div class="row clearfix innerPage" style="position: relative; z-index: 5;">
                                            <div class="col-md-3 col-sm-12 col-xs-12 lf-rt-br equalHeight">
                                                <div class="table-responsive innerLeft">
                                                    <div class="form">
                                                        <div class="sidebar">
                                                            <ul class="nav" id="levelOneData">
                                                                <!-- Data renders here dynamically -->
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-9 col-sm-12 col-xs-12 rt-lf equalHeight">
                                                <div class="troubleInn">
                                                    <h4 id="tile-header">Troubleshooters</h4>
                                                    <p id="tile-description">Use these troubleshooting and resolution tools to quickly and easily resolve many common device issues. Choose a category on the left and then select the fix that best matches the issue you are experiencing.</p>
                                                </div>

                                                <div class="troubleInn" style="display: block;">
                                                    <div class="table-full-width table-responsive">
                                                        <div class="form">
                                                            <div class="sidebar">
                                                                <div id="child-lvl">
                                                                    <!-- Data renders here dynamically -->
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

                        <div class="button col-md-12 text-center btBtn btBtnright">
                            <button data-qa="ph-skipButton" style="display:none;" type="button" class="swal2-confirm btn-skip" id="skipButton">Skip this step</button>
                            <button data-qa="ph-prevProfBtn" style="display:none;" type="button" class="swal2-confirm btn btn-alert btn-sm btn-next" id="prevProfBtn">Prev</button>
                            <!--<button style="display:none;" type="button" class="swal2-confirm btn btn-success btn-sm btn-next" id="prevProfBtnBasic">Prev</button>-->
                            <button data-qa="ph-nextProfBtn" type="button" class="swal2-confirm btn btn-success btn-sm btn-next" id="nextProfBtn">Next</button>
                            <!--<button type="button" class="swal2-confirm btn btn-success btn-sm btn-next" id="nextProfBtnBasic">Next</button>-->
                            <!--<button style="display:none;" type="button" class="swal2-confirm btn btn-success btn-sm btn-next" id="switchPreview">Review Client</button>-->
                            <button data-qa="ph-saveProfBtn" style="display:none;" type="button" class="swal2-confirm btn btn-success btn-sm btn-next" id="saveProfBtn" onclick="saveProfileDetails()">Save</button>
                        </div>
                    </div>
                    <!-- end content-->
                </form>
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>

<!-- help modalpopup starts here -->

<div class="modal text-center proHelp" id="Help">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-body text-left">
                <h5>How to use the Profile Wizard</h5>

                <span>
                    <p><i class="tim-icons icon-cloud-download-93"></i> &nbsp;Download</p>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- profileThree dart starts here -->

<div id="dart-role" class="rightSidenav" data-class="lg-9">
    <div class="rsc-loader hide"></div>
    <div class="btnGroup" style="z-index: 100000;">
        <div class="icon-circle">
            <div class="toolTip" onclick="onSubmit();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Submit</span>
            </div>
        </div>
    </div>
    <div class="card-title border-bottom">
        <h4 id="dcs-title">Dart 230</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="dart-role">&times;</a>
    </div>

    <!--<div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="profileDartSubmit();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>-->

    <div class="form table-responsive white-content">
        <div class="card" id="consoleWrapper">
            <form id="" method="POST" action="">
                <div class="form-group has-label">
                    <label>Role Name</label>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="attach-profile" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Attach Profile</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="attach-profile">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="attachProfile-save-btn" data-qa="attachProfile-save-btn" onclick="attachProfileTo()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Attach</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <div class="card-body">
                <div class="form-group has-label" id="editfocused">
                    <label for="">
                        Attach profile to
                    </label>
                </div>
            </div>
            <div class="card-body">
                <div>
                    <div class="form-check form-check-radio col-md-3" style="float:left">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" id="proatt-sites" name="profattch" value="sites">
                            <span class="form-check-sign"></span> Sites
                        </label>
                    </div>

                    <div class="form-check form-check-radio col-md-3" style="float:left">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" id="proatt-sites" name="profattch" value="groups">
                            <span class="form-check-sign"></span> Groups
                        </label>
                    </div>
                </div>
                <div>&nbsp;</div>
            </div>

            <div class="card-body site-div" style="display: none;margin-top:20px">
                <div class="form-group has-label">
                    <label>Sites</label>
                </div>
                <select class="selectpicker" multiple data-style="btn btn-info" title="Select Sites" data-size="3" id="siteList" name="siteList">

                </select>
            </div>

            <div class="card-body group-div" style="display: none;margin-top:20px">
                <div class="form-group has-label">
                    <label>Groups</label>
                </div>
                <select class="selectpicker" multiple data-style="btn btn-info" title="Select Groups" data-size="3" id="groupList" name="groupList">

                </select>
            </div>
        </div>
    </div>
</div>

<!-- Duplicate Profile Content Div -->
<div id="duplicate-profile" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Duplicate Profile</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="duplicate-profile">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="duplicateProfile" onclick="duplicateProfileSave()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Duplicate Profile</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <div class="card-body">
                <!--<div class="form-group has-label" id="editfocused">
                    <label for="">
                        Duplicating profile :
                    </label>
                </div>-->
                <div class="form-group has-label col-md-12">
                    <label for="">
                        Enter new profile name
                    </label>
                    <input class="form-control" type="text" name="duplicate-profile-name" id="duplicate-profile-name" placeholder="Enter a new profile name" />
                </div>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
    .open-dart-console {
        cursor: pointer
    }

    a.w3-dart-title,
    a.w3-dart-title:hover {
        color: #1d253b;
        font-size: 12px;
    }

    .w3-dart-elements .my-collapse-content span {
        position: relative;
        left: 15px;
    }

    .bx-cnt-spn {
        border: 1px solid #06113c;
        border-radius: 20px;
        font-size: 10px;
        float: right;
        width: 25px;
        text-align: center;
        color: #000;
        font-weight: bold;
        height: 25px;
        margin: -12px -12px 0px 0px;
        padding: 4px 0px 0px;
    }

    span.error {
        margin-right: 6px !important;
        position: relative;
        top: 3px;
    }

    span.error.left {
        float: left !important;
        top: -3px !important;
    }

    span.error.opsys {
        font-size: 0.875rem !important;
        top: -1px !important;
    }

    select.dart-select.left {
        width: 96%;
        padding: 0.2rem;
    }

    input.dart-input-title {
        width: 96%;
    }

    div#ck-uck-all {
        position: relative;
        top: -12px;
    }

    div#ck-uck-all p {
        position: relative;
        top: 7px;
        color: #1d253b;
        font-weight: 400;
        font-size: 13px;
    }

    div#ck-uck-all-cli {
        position: relative;
        top: -12px;
    }

    div#ck-uck-all-cli p {
        position: relative;
        top: 7px;
        color: #1d253b;
        font-weight: 400;
        font-size: 13px;
    }

    #accordion .card div.card-header {
        width: 98%;
    }

    .r-ic {
        background-color: #ffffff;
        border-radius: 13px;
        padding: 0px 5px 2px 5px;
        border: 1px solid #C1C1C1;
        cursor: pointer;
        box-shadow: 0px 0px 3px 1px #ccc;
        -webkit-box-shadow: 0px 0px 3px 1px #ccc;
    }

    /* Chrome, Safari, Edge, Opera */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Firefox */
    input[type=number] {
        -moz-appearance: textfield;
    }

    .dataTables_filter {
        display: none;
    }
</style>