<?php
//error_reporting(-1);
//ini_set('display_errors', 'On');
unset($_SESSION['pwvardata']);

$pw_id = url::requestToInt('id');

include_once '../lib/l-profilewiz.php';
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
$profileName = "";
$dartTileToken = "";

if ($pw_id) {
    $profileName = PRWZ_getProfileName($pw_id, 'edit');
    $dartTileToken = PRWZ_getProfileSeqToken($pw_id, 'edit');
}
// if ($profileName == '') {
//     header('Location: index.php');
// }

$baseProfileData = PRWZ_getProfileDetails($pw_id);

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

$customTileData = PRWZ_getCustomTileDetails($pw_id);
$newTileCnt = safe_count($customTileData);

?>
<div class="content white-content profilePage">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form action="profile.php" method="POST" onsubmit="return false;">
                    <div class="card-body" id="profwiz-add">
                        <div class="toolbar">
                            <div class="bullDropdown leftDropdown">
                                <div class="dropdown">
                                    <span>Edit Profile</span>
                                </div>
                            </div>

                            <div class="bullDropdown">
                                <div class="dropdown">
                                    <div id="close-swd-widget" class="r-ic" onclick="confirmCancelOperation();"><i class="tim-icons icon-simple-remove"></i></div>
                                </div>
                            </div>
                        </div>

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
                                <input type="hidden" name="profileID" id="profileID" value="<?php echo url::toText($pw_id); ?>">
                                <input type="hidden" id="selected">
                                <input type="hidden" id="reviewdata" value="client">

                                <!-- profileOne starts here -->

                                <div class="col-md-10 col-sm-10 table-responsive rt-equalHeight profileOne eachPWrap activeNow" id="profile1" style="display: block;">
                                    <div class="form-group has-label">
                                        <span class="error">*</span>
                                        <label>Name of the Profile <span id="err_profile-name"></span></label>
                                        <input data-qa="pe-profile-name" id="profile-name" name="profile-name" class="form-control" type="text" data-required="true" onkeyup="trackInputChange(this.value,this.id,this)" data-label="Profile name" value="<?php echo url::toText($profileName); ?>">

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

                                        <?php foreach ($level_2 as $val_2) {
                                            $checked_l2 = '';
                                            if ($val_2['Enable/Disable'] == 1 || $val_2['Enable/Disable'] == 3) {
                                                $checked_l2 = 'checked';
                                            }
                                        ?>
                                            <div class="card">
                                                <div class="card-header" id="headingOne">
                                                    <div class="form-check">

                                                        <label class="form-check-label">
                                                            <input class="form-check-input" <?php echo $checked_l2; ?> type="checkbox" name="global" value="<?php echo url::toText($val_2['mid']); ?>" onclick="checkBoxUpdate(this, '');">
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
                                                        <?php }
                                                        }
                                                        ?>
                                                    </div>
                                                </div>

                                            </div>
                                        <?php } ?>
                                    </div>

                                    <!-- Client Profile List -->
                                    <!--<div id="accordion" class="clientProfileList" style="display: none;">

                                    </div>-->

                                </div>

                                <div class="col-md-10 col-sm-10 table-responsive rt-equalHeight profileOne eachPWrap" id="profile2" style="display: none;">
                                    <!-- Client Profile List -->
                                    <div id="accordion" class="clientProfileList" style="display: none;">

                                    </div>
                                </div>

                                <!-- profileTwo starts here -->

                                <div class="col-md-10 col-sm-10 table-responsive rt-equalHeight profileTwo eachPWrap" id="profile3" style="display: none;">
                                    <?php foreach ($customTileData as $key => $value) { ?>
                                        <input type="hidden" name="custom-tile-id[]" value="<?php echo url::toText($value['ctid']); ?>">
                                        <div class="Box title-grid" id="tileData<?php echo $key; ?>">
                                            <span class="bx-cnt-spn">#<?php echo ($key + 1); ?></span>
                                            <div class="form-group has-label">
                                                <span class="error">*</span>
                                                <label class="tgr-title">Name of custom troubleshooter <span id="err_tile-name<?php echo $key ?>"></span></label>
                                                <input data-qa="pe-Name_of_custom_troubleshooter" class="form-control w1-name" id="tile-name<?php echo $key ?>" type="text" onkeyup="trackInputChange(this.value,this.id,this)" name="tile-name[]" data-required="true" data-label="Name of the title" value="<?php echo url::toText($value['tilename']); ?>">
                                            </div>

                                            <div class="form-group has-label">
                                                <span class="error">*</span>
                                                <label>Description of custom troubleshooter</label>
                                                <input data-qa="pe-Description_of_custom_troubleshooter" class="form-control" type="text" name="tile-description[]" data-required="true" data-label="Description" value="<?php echo url::toText($value['tiledescription']); ?>">
                                                <span id="name_err"></span>
                                            </div>

                                            <div class="row">
                                                <label class="col-sm-12 col-form-label">Visibility</label>
                                                <?php $dashchk = $clichk = '';
                                                if ($value['visibility'] == '3') {
                                                    $dashchk = 'checked="checked"';
                                                } else if ($value['visibility'] == '1') {
                                                    $clichk = 'checked="checked"';
                                                } ?>
                                                <div class="col-sm-6 checkbox-radios">
                                                    <div class="form-check form-check-radio">
                                                        <label class="form-check-label">
                                                            <input class="form-check-input title-visibiity" type="radio" name="visibility[<?php echo $key; ?>]" value="3" <?php echo $dashchk; ?>>
                                                            <span class="form-check-sign"></span> Show only on Dashboard
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-sm-6 checkbox-radios">
                                                    <div class="form-check form-check-radio">
                                                        <label class="form-check-label">
                                                            <input class="form-check-input title-visibiity" type="radio" name="visibility[<?php echo $key; ?>]" value="1" <?php echo $clichk; ?>>
                                                            <span class="form-check-sign"></span> Show on Dashboard and Client
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div data-required-any-one-parent="this" class="row" data-group-name="OS">
                                                <label class="col-sm-12 col-form-label"><span class="error opsys">*</span>Operating System</label>

                                                <?php
                                                $opsyslist = explode(',', $value['ostypes']);
                                                $winchk = $andchk = $macchk = $ioschk = $lnxchk = $ubnchk = '';
                                                foreach ($opsyslist as $osvalue) {
                                                    switch (strtolower($osvalue)) {
                                                        case 'windows':
                                                            $winchk = "checked";
                                                            break;
                                                        case 'android':
                                                            $andchk = "checked";
                                                            break;
                                                        case 'mac':
                                                            $macchk = "checked";
                                                            break;
                                                        case 'ios':
                                                            $ioschk = "checked";
                                                            break;
                                                        case 'linux':
                                                            $lnxchk = "checked";
                                                            break;
                                                        case 'ubuntu':
                                                            $ubnchk = "checked";
                                                            break;
                                                    }
                                                }
                                                ?>

                                                <div class="col-sm-2 checkbox-radios">
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input class="form-check-input os-ck-bx" data-required-any-one="true" value="Windows" type="checkbox" name="os-win[<?php echo $key; ?>]" data-name="os-win" <?php echo $winchk; ?>>
                                                            <span class="form-check-sign"></span> Windows
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-sm-2 checkbox-radios">
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input class="form-check-input os-ck-bx" data-required-any-one="true" value="Android" type="checkbox" name="os-android[<?php echo $key; ?>]" data-name="os-android" <?php echo $andchk; ?>>
                                                            <span class="form-check-sign"></span> Android
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-sm-2 checkbox-radios">
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input class="form-check-input os-ck-bx" data-required-any-one="true" value="Mac" type="checkbox" name="os-mac[<?php echo $key; ?>]" data-name="os-mac" <?php echo $macchk; ?>>
                                                            <span class="form-check-sign"></span> Mac
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-sm-2 checkbox-radios">
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input class="form-check-input os-ck-bx" data-required-any-one="true" value="iOS" type="checkbox" name="os-ios[<?php echo $key; ?>]" data-name="os-ios" <?php echo $ioschk; ?>>
                                                            <span class="form-check-sign"></span> iOS
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-sm-2 checkbox-radios">
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input class="form-check-input os-ck-bx" data-required-any-one="true" value="Linux" type="checkbox" name="os-linux[<?php echo $key; ?>]" data-name="os-linux" <?php echo $lnxchk; ?>>
                                                            <span class="form-check-sign"></span> Linux
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="col-sm-2 checkbox-radios">
                                                    <div class="form-check">
                                                        <label class="form-check-label">
                                                            <input class="form-check-input os-ck-bx" data-required-any-one="true" value="Ubuntu" type="checkbox" name="os-ubuntu[<?php echo $key; ?>]" data-name="os-ubuntu" <?php echo $ubnchk; ?>>
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

                                            <?php foreach (safe_json_decode($value['tiledartseq'], true) as $dtvalue) { ?>
                                                <div class="row each-dart-box">
                                                    <div class="col-sm-4">
                                                        <span class="error left">*</span>
                                                        <select data-required="true" data-label="Dart" class="form-control dart-select left" name="tile-darts[<?php echo $key; ?>][]" data-style="btn btn-info" title="Select Dart" data-size="4">
                                                            <option value="" selected="selected">Select Dart</option>
                                                            <?php
                                                            foreach ($tilesList as $dtdkey => $dtdvalue) {
                                                                $selected = '';
                                                                if ($dtdkey == $dtvalue['dartid']) {
                                                                    $selected = 'selected';
                                                                }
                                                            ?>
                                                                <option value="<?php echo url::toText($dtdkey); ?>" <?php echo $selected; ?>><?php echo $dtdvalue; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <span class="error left">*</span>
                                                        <input type="text" class="form-control dart-input-title" name="tile-dart-name[<?php echo $key; ?>][]" data-required="true" data-label="Dart description" value="<?php echo url::toText($dtvalue['dartname']); ?>" />
                                                    </div>
                                                    <div class="col-sm-4 rmv-dart-box">
                                                        <span><i class="tim-icons icon-simple-delete w2-rmv-dart-box"></i></span>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                            <?php // echo json_encode($customTileData); 
                                            ?>
                                            <?php // echo json_encode($dtvalue); 
                                            ?>
                                            <div class="row remove remove-title-grid">
                                                <div class="col-md-12">
                                                    <span class="remove-pwrap" onclick="removeTitleGrid($(this), event)">Remove</span>
                                                </div>
                                            </div>
                                            <div class="row addmore">
                                                <div class="col-md-12">
                                                    <span><i class="tim-icons icon-simple-add w2-add-dart-box"></i></span>
                                                </div>
                                            </div>

                                        </div>
                                    <?php } ?>

                                    <?php if ($newTileCnt == 0) { ?>
                                        <div class="Box title-grid" id="tileData0">
                                            <span class="bx-cnt-spn">#1</span>
                                            <div class="form-group has-label">
                                                <span class="error">*</span>
                                                <label class="tgr-title">Name of custom troubleshooter <span id="err_tile-name@"></span></label>
                                                <input data-qa="pe-Name_of_custom_troubleshooter2" id="tile-name@" class="form-control w1-name" type="text" name="tile-name[]" onkeyup="trackInputChange(this.value,this.id,this)" data-required="true" data-label="Name of the title">
                                            </div>

                                            <div class="form-group has-label">
                                                <span class="error">*</span>
                                                <label>Description of custom troubleshooter</label>
                                                <input data-qa="pe-Description_of_custom_troubleshooter2" class="form-control" type="text" name="tile-description[]" data-required="true" data-label="Description">
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
                                                            <option value="<?php echo url::toText($key); ?>"><?php echo $value; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <div class="col-sm-4">
                                                    <span class="error left">*</span>
                                                    <input type="text" class="form-control dart-input-title" name="tile-dart-name[0][]" data-required="true" data-label="TileDartName" placeholder="Dart description" />
                                                </div>
                                            </div>

                                            <div class="row addmore">
                                                <div class="col-md-12">
                                                    <span><i class="tim-icons icon-simple-add w2-add-dart-box"></i></span>
                                                </div>
                                            </div>

                                        </div>
                                    <?php } ?>

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
                                    <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next review-tiles" id="render-dash" data-qa="pe-render-dash" onclick="renderProfileConfiguration();" style="width: 170px !important;">Review Dashboard Tiles</button>
                                    <button type="button" class="swal2-confirm btn btn-alert btn-sm btn-next review-tiles" id="render-clnt" data-qa="pe-render-clnt" onclick="renderClientProfileConfiguration();" style="width: 140px !important;">Review Client Tiles</button>
                                    <div class="Box">
                                        <div class="row clearfix innerPage">
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
                            <button data-qa="pe-skipButton" style="display:none;" type="button" class="swal2-confirm btn-skip" id="skipButton">Skip this step</button>
                            <button data-qa="pe-prevProfBtn" style="display:none;" type="button" class="swal2-confirm btn btn-success btn-sm btn-next" id="prevProfBtn">Prev</button>
                            <!--<button style="display:none;" type="button" class="swal2-confirm btn btn-success btn-sm btn-next" id="prevProfBtnBasic">Prev</button>-->
                            <button data-qa="pe-nextProfBtn" type="button" class="swal2-confirm btn btn-success btn-sm btn-next" id="nextProfBtn">Next</button>
                            <!--<button type="button" class="swal2-confirm btn btn-success btn-sm btn-next" id="nextProfBtnBasic">Next</button>-->
                            <!--<button style="display:none;" type="button" class="swal2-confirm btn btn-success btn-sm btn-next" id="switchPreview">Review Client</button>-->
                            <button data-qa="pe-saveProfBtn" type="button" class="swal2-confirm btn btn-success btn-sm btn-next" id="saveProfBtn" onclick="updateProfileDetails()" style="display: none;">Update</button>
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
    <div class="card-title">
        <h4 id="dcs-title">Dart 230</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="dart-role">&times;</a>
    </div>

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

<style>
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
</style>