<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?>
<div class="content white-content profilePage">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body" id="profwiz-basic">
                    <div class="toolbar">

                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" id="add-swd">Add Swd</a>
                                    <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('attachprofile', 2); ?>" id="attachProfile" onclick="attachProfile();">Attach Profile</a>
                                    <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('removeprofile', 2); ?>" id="removeProfile" onclick="selectConfirm('delete');">Remove Profile</a>
                                    <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('exportexcel', 2); ?>" id="exportExcel" onclick="selectConfirm('delete');">Export to Excel</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="nhl-datatable table table-striped" id="profileWizardGrid">
                        <thead>
                            <tr>
                                <th>Profile Name</th>
                                <th>Created By</th>
                                <th>Created On</th>
                                <th>Linked Sites</th>
                                <th>Dashboard Only?</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <form action="profile.php" method="POST" onsubmit="return false;">
                    <div class="card-body" style="display: none;" id="swd-add-container">
                        <div class="toolbar">
                            <div class="bullDropdown leftDropdown">
                                <div class="dropdown">
                                    <span>Add a Profile</span>
                                </div>
                            </div>

                            <div class="bullDropdown" style="display:none">
                                <div class="dropdown">
                                    <span class="help" data-bs-toggle="modal" data-target="#Help">Help</span>
                                </div>
                            </div>
                        </div>

                        <div class="row clearfix innerPage">
                            <div class="col-md-12">
                                <div class="col-md-1 col-sm-2 lf-equalHeight" id="dispMenu">
                                    <div class="col-md-12 addBox side-nav-it active" data-disp="1">
                                        <p>1</p><span>Platform</span>
                                    </div>
                                    <!--                                    <div class="col-md-12 addBox" data-disp="2" style="display:none">2 <span>Add New Tiles</span></div>-->
                                </div>

                                <!-- hidden fields -->
                                <input type="hidden" name="dartTileToken" id="dartTileToken" value="<?php echo url::toText($dartTileToken); ?>">
                                <input type="hidden" id="selected">

                                <!-- profileOne starts here -->

                                <div class="col-md-11 col-sm-10 table-responsive rt-equalHeight" id="profile">
                                    <div class="form-group has-label">
                                        <label>
                                            <h3>Select Platform</h3>
                                        </label>
                                    </div>
                                    <div class="container">
                                        <div class="row justify-content-center" style="position: relative;top: 80px;">
                                            <input type="hidden" autocomplete="off" value="" class="swd-ic-it-input" />
                                            <div class="col-sm-2 swd-ic-it swd-plt" data-indentifier-key="platform" data-platform="windows" style="cursor:pointer"><img src="../assets/img/ic-os-win.png" style="margin: 0 auto;display: block;" alt="windows" /><br />
                                                <p>Windows</p>
                                            </div>
                                            <div class="col-sm-2 swd-ic-it swd-plt" data-indentifier-key="platform" data-platform="linux" style="cursor:pointer"><img src="../assets/img/ic-os-linux.png" style="margin: 0 auto;display: block;" alt="Linux" /><br />
                                                <p>Linux</p>
                                            </div>
                                            <div class="col-sm-2 swd-ic-it swd-plt" data-indentifier-key="platform" data-platform="mac" style="cursor:pointer"><img src="../assets/img/ic-os-mac.png" style="margin: 0 auto;display: block;" alt="Macintosh" /><br />
                                                <p>Macintosh</p>
                                            </div>
                                            <div class="col-sm-2 swd-ic-it swd-plt" data-indentifier-key="platform" data-platform="android" style="cursor:pointer"><img src="../assets/img/ic-os-android.png" style="margin: 0 auto;display: block;" alt="Android" /><br />
                                                <p>Android</p>
                                            </div>
                                            <div class="col-sm-2 swd-ic-it swd-plt" data-indentifier-key="platform" data-platform="ios" style="cursor:pointer"><img src="../assets/img/ic-os-iphone.png" style="margin: 0 auto;display: block;" alt="Iphone" /><br />
                                                <p>iOS</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- source info starts here -->

                                <div class="col-md-11 col-sm-10 table-responsive rt-equalHeight defualt-hide swd-hide eachPWrap" data-wrap-name="start">
                                    <div class="form-group has-label">
                                        <span class="error">*</span>
                                        <label class="tgr-title">Package Type</label>
                                    </div>
                                    <div class="container">
                                        <div class="row justify-content-center swd-ic-it-group" style="position: relative;">
                                            <input type="hidden" autocomplete="off" value="" class="swd-ic-it-input" name="package-type" />
                                            <div class="col-sm-2 swd-ic-it swd-pt" data-indentifier-key="type" data-type="distribute" style="cursor:pointer"><img src="../assets/img/ic-pkg-distribute.png" style="margin: 0 auto;display: block;" alt="Distribute" /><br />
                                                <p>Distribute</p>
                                            </div>
                                            <div class="col-sm-2 swd-ic-it swd-pt" data-indentifier-key="type" data-type="execute" style="cursor:pointer"><img src="../assets/img/ic-pkg-execute.png" style="margin: 0 auto;display: block;" alt="Execute" /><br />
                                                <p>Execute</p>
                                            </div>
                                            <div class="col-sm-2 swd-ic-it swd-pt" data-indentifier-key="type" data-type="both" style="cursor:pointer"><img src="../assets/img/ic-pkg-both.png" style="margin: 0 auto;display: block;" alt="Both" /><br />
                                                <p>Both (Distribute/Execute)</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group has-label default-hide swd-hide windows-grid">
                                        <span class="error">*</span>
                                        <label class="tgr-title">Windows Type</label>
                                    </div>
                                    <div class="container default-hide swd-hide windows-grid">
                                        <div class="row justify-content-center swd-ic-it-group" style="position: relative;">
                                            <input type="hidden" autocomplete="off" value="" class="swd-ic-it-input" name="windows-type" />
                                            <div class="col-sm-2 swd-ic-it swd-win-type" data-indentifier-key="type" data-type="32" style="cursor:pointer"><img src="../assets/img/ic-os-win-32.png" style="margin: 0 auto;display: block;" alt="32 Bit" /><br />
                                                <p>32 Bit</p>
                                            </div>
                                            <div class="col-sm-2 swd-ic-it swd-win-type" data-indentifier-key="type" data-type="64" style="cursor:pointer"><img src="../assets/img/ic-os-win-64.png" style="margin: 0 auto;display: block;" alt="64 Bit" /><br />
                                                <p>64 Bit</p>
                                            </div>
                                            <div class="col-sm-2 swd-ic-it swd-win-type" data-indentifier-key="type" data-type="both" style="cursor:pointer"><img src="../assets/img/ic-os-win.png" style="margin: 0 auto;display: block;" alt="Both" /><br />
                                                <p>Both (32 Bit/64 Bit)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Package Info starts here -->

                                <div id="source-wrap" class="col-md-11 col-sm-10 table-responsive rt-equalHeight defualt-hide swd-hide eachPWrap" data-wrap-name="source">

                                    <div class="mt-z row">
                                        <div class="col-sm-6 default-hide swd-hide">
                                            <div class="well well-sm default-hide swd-hide"></div>
                                            <div class="row my-btn-group default-hide swd-hide">
                                                <div class="btn-group btn-group-toggle float-right" data-toggle="buttons">
                                                    <label class="btn btn-md btn-primary btn-simple active wt-hand default-hide swd-hide" data-target="s-32-1" data-value="1">
                                                        <input type="radio" name="options" checked="">
                                                        <span class="d-none d-sm-block d-md-block d-lg-block d-xl-block">Upload</span>
                                                        <span class="d-block d-sm-none">
                                                            <i class="tim-icons icon-single-02"></i>
                                                        </span>
                                                    </label>
                                                    <label class="btn btn-md btn-primary btn-simple wt-hand default-hide swd-hide" data-target="s-32-2" data-value="2">
                                                        <input type="radio" class="d-none d-sm-none" name="options">
                                                        <span class="d-none d-sm-block d-md-block d-lg-block d-xl-block">URL</span>
                                                        <span class="d-block d-sm-none">
                                                            <i class="tim-icons icon-gift-2"></i>
                                                        </span>
                                                    </label>
                                                    <label class="btn btn-md btn-primary btn-simple wt-hand default-hide swd-hide" data-target="s-32-3" data-value="3">
                                                        <input type="radio" class="d-none d-sm-none" name="options">
                                                        <span class="d-none d-sm-block d-md-block d-lg-block d-xl-block">Path</span>
                                                        <span class="d-block d-sm-none">
                                                            <i class="tim-icons icon-gift-2"></i>
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="wt-group">
                                                <input type="hidden" value="1" name="source-type" class="wt-value" />
                                                <div id="s-32-1" class="wt-wrap default-hide swd-hide qq-up-wrap-parent" style="margin-bottom:10px">
                                                    <div class="form-group has-label default-hide swd-hide upload-to">
                                                        <span class="error">*</span>
                                                        <label class="tgr-title">Upload To</label>
                                                        <br />
                                                        <input class="my-upload-type" type="checkbox" name="upload-to" checked>
                                                        <span id="name_err"></span>
                                                    </div>
                                                    <div id="upload-widget-1" class="qq-up-wrap"></div>
                                                </div>

                                                <div id="s-32-2" class="wt-wrap default-hide swd-hide">
                                                    <div class="form-group has-label">
                                                        <span class="error">*</span>
                                                        <label class="tgr-title">Url</label>
                                                        <input class="form-control" type="text" name="package-url" data-required="true" data-label="Url">
                                                        <span id="name_err"></span>
                                                    </div>
                                                </div>
                                                <div id="s-32-3" class="wt-wrap default-hide swd-hide">
                                                    <div class="form-group has-label">
                                                        <span class="error">*</span>
                                                        <label class="tgr-title">Path</label>
                                                        <input class="form-control" type="text" name="package-path" data-required="true" data-label="Url">
                                                        <span id="name_err"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div id="package_name-wrap" class="form-group has-label">
                                                <span class="error">*</span>
                                                <label class="tgr-title">Package/App Name</label>
                                                <input class="form-control" type="text" name="package-name" data-required="true" data-label="Package/App Name">
                                                <span id="name_err"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row addmore" id="add-more-patch" style="cursor:pointer">
                                        <div class="col-md-12" id="addNewTiles">
                                            <span><i class="tim-icons icon-simple-add"></i> Add more...</span>
                                        </div>
                                    </div>
                                </div>


                                <!-- pre check Info starts here -->

                                <div id="pre-check-wrap" class="col-md-11 col-sm-10 table-responsive rt-equalHeight defualt-hide swd-hide eachPWrap" data-wrap-name="pre-check">
                                    <div class="mt-x row">
                                        <div class="col-sm-6 swd-hide">
                                            <div class="well well-sm default-hide swd-hide"></div>
                                            <div class="row my-btn-group default-hide swd-hide">
                                                <div class="btn-group btn-group-toggle float-right" data-toggle="buttons">
                                                    <label class="btn btn-md btn-primary btn-simple active wt-hand default-hide swd-hide" data-target="s1-32-1" data-value="3">
                                                        <input type="radio" class="d-none" name="options">
                                                        <span class="d-none d-sm-block d-md-block d-lg-block d-xl-block">Registry</span>
                                                        <span class="d-block d-sm-none">
                                                            <i class="tim-icons icon-tap-02"></i>
                                                        </span>
                                                    </label>
                                                    <label class="btn btn-md btn-primary btn-simple wt-hand default-hide swd-hide" data-target="s1-32-2" data-value="1">
                                                        <input type="radio" name="options" checked="">
                                                        <span class="d-none d-sm-block d-md-block d-lg-block d-xl-block">File</span>
                                                        <span class="d-block d-sm-none">
                                                            <i class="tim-icons icon-single-02"></i>
                                                        </span>
                                                    </label>
                                                    <label class="btn btn-md btn-primary btn-simple wt-hand default-hide swd-hide" data-target="s1-32-3" data-value="2">
                                                        <input type="radio" class="d-none d-sm-none" name="options">
                                                        <span class="d-none d-sm-block d-md-block d-lg-block d-xl-block">Software Name</span>
                                                        <span class="d-block d-sm-none">
                                                            <i class="tim-icons icon-gift-2"></i>
                                                        </span>
                                                    </label>

                                                </div>
                                            </div>

                                            <div class="wt-group">
                                                <input type="hidden" value="1" name="precheck-type" class="wt-value">
                                                <div id="s1-32-1" class="wt-wrap default-hide swd-hide registry-precheck-wrap">
                                                    <div class="form-group has-label">
                                                        <div class="form-check form-check-radio">
                                                            <label class="form-check-label">
                                                                <input class="form-check-input" type="radio" value="0" name="registry-precheck" checked="checked" data-label="Registry Precheck">
                                                                <span class="form-check-sign"></span> Execute when pre-check value exists
                                                            </label>
                                                        </div>

                                                        <div class="form-check form-check-radio">
                                                            <label class="form-check-label">
                                                                <input class="form-check-input" type="radio" value="1" name="registry-precheck" data-label="Registry Precheck">
                                                                <span class="form-check-sign"></span> Execute when pre-check value doesn't exists
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group has-label">
                                                        <span class="error">*</span>
                                                        <label class="tgr-title">Root Key</label>
                                                        <select name="root-key" title="Root Key" class="form-control dropdown-submenu" data-size="5" data-width="100%" data-label="Root Key">
                                                            <option value="3">HKEY_CLASSES_ROOT</option>
                                                            <option value="4">HKEY_CURRENT_USER</option>
                                                            <option value="1">HKEY_LOCAL_MACHINE</option>
                                                            <option value="5">HKEY_USERS</option>
                                                            <option value="7">HKEY_PERFORMANCE_DATA</option>
                                                            <option value="8">HKEY_PERFORMANCE_TEXT</option>
                                                            <option value="9">HKEY_PERFORMANCE_NLSTEXT</option>
                                                            <option value="2">HKEY_CURRENT_CONFIG</option>
                                                            <option value="6">HKEY_DYN_DATA</option>
                                                        </select>
                                                        <span id="name_err"></span>
                                                    </div>
                                                    <div class="form-group has-label">
                                                        <span class="error">*</span>
                                                        <label class="tgr-title">Sub Key</label>
                                                        <input class="form-control" type="text" name="sub-key" data-required="true" data-label="Sub Key">
                                                        <span id="name_err"></span>
                                                    </div>
                                                    <div class="form-group has-label">
                                                        <span class="error">*</span>
                                                        <label class="tgr-title">Name</label>
                                                        <input class="form-control" type="text" name="sub-key-name" data-required="true" data-label="Name">
                                                        <span id="name_err"></span>
                                                    </div>
                                                    <div class="form-group has-label">
                                                        <span class="error">*</span>
                                                        <label class="tgr-title">Type</label>
                                                        <select name="type" id="type" title="Type" class="form-control dropdown-submenu" data-size="5" data-width="100%" data-label="Type">
                                                            <option value="REG_SZ">REG_SZ</option>
                                                            <option value="REG_DWORD">REG_DWORD</option>
                                                            <option value="REG_QWORD">REG_QWORD</option>
                                                            <option value="REG_BINARY">REG_BINARY</option>
                                                            <option value="REG_MULTI_SZ">REG_MULTI_SZ</option>
                                                            <option value="REG_EXPAND_SZ">REG_EXPAND_SZ</option>
                                                        </select>
                                                        <span id="name_err"></span>
                                                    </div>
                                                    <div class="form-group has-label">
                                                        <span class="error">*</span>
                                                        <label class="tgr-title">Value</label>
                                                        <input class="form-control" type="text" name="type-value" data-required="true" data-label="Type Value">
                                                        <span id="name_err"></span>
                                                    </div>
                                                </div>
                                                <div id="s1-32-2" class="wt-wrap default-hide swd-hide">
                                                    <div class="form-group has-label">
                                                        <div class="form-check form-check-radio">
                                                            <label class="form-check-label">
                                                                <input class="form-check-input" type="radio" value="0" name="file-precheck" checked="checked" data-label="File Precheck">
                                                                <span class="form-check-sign"></span> Execute when pre-check value exists
                                                            </label>
                                                        </div>

                                                        <div class="form-check form-check-radio">
                                                            <label class="form-check-label">
                                                                <input class="form-check-input" type="radio" value="1" name="file-precheck" data-label="File Precheck">
                                                                <span class="form-check-sign"></span> Execute when pre-check value doesn't exists
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="form-group has-label">
                                                        <span class="error">*</span>
                                                        <label class="tgr-title">File Path</label>
                                                        <input class="form-control" type="text" name="file-path" data-required="true" data-label="File Path">
                                                        <span id="name_err"></span>
                                                    </div>
                                                </div>
                                                <div id="s1-32-3" class="default-hide swd-hide wt-wrap">
                                                    <div class="form-group has-label">
                                                        <span class="error">*</span>
                                                        <label class="tgr-title">Software Name</label>
                                                        <input class="form-control" type="text" name="software-name" data-required="true" data-label="Software Name">
                                                        <span id="name_err"></span>
                                                    </div>
                                                    <div class="form-group has-label">
                                                        <span class="error">*</span>
                                                        <label class="tgr-title">Software Version</label>
                                                        <input class="form-control" type="text" name="software-version" data-required="true" data-label="Software Version">
                                                        <span id="name_err"></span>
                                                    </div>
                                                    <div class="form-group has-label">
                                                        <span class="error">*</span>
                                                        <label class="tgr-title">Knowledge Base</label>
                                                        <input class="form-control" type="text" name="knowledge-base" data-required="true" data-label="Knowledge Base">
                                                        <span id="name_err"></span>
                                                    </div>
                                                    <div class="form-group has-label">
                                                        <span class="error">*</span>
                                                        <label class="tgr-title">Service Pack</label>
                                                        <input class="form-control" type="text" name="service-pack" data-required="true" data-label="Service Pack">
                                                        <span id="name_err"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Finish starts here -->

                                <div id="finish-wrap" class="col-md-11 col-sm-10 table-responsive rt-equalHeight defualt-hide swd-hide eachPWrap" data-wrap-name="finish">

                                    <div class="container my-container options-section">
                                        <div class="row">

                                            <div class="form-group has-label col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Run as</label>
                                                <select class="form-control" id="runas" name="api-mode">
                                                    <option value="4">User</option>
                                                    <option value="1">System</option>
                                                    <option value="6">Administrator</option>
                                                </select>
                                                <span id="name_err"></span>
                                            </div>
                                            <div class="form-group has-label col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Session</label>
                                                <select class="form-control" name="session">
                                                    <option value="1">User</option>
                                                    <option value="0">Interactive</option>
                                                </select>
                                                <span id="name_err"></span>
                                            </div>
                                            <div class="form-group has-label col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Post Validation</label>
                                                <input class="form-control" type="text" name="post-validation[]" data-required="true" data-label="Name of the title" data-bs-toggle="tooltip" title="The post validation to check">
                                                <span id="name_err"></span>
                                            </div>

                                            <div class="form-group has-label col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Url Credential</label>
                                                <input class="form-control" type="text" name="url-credential[]" data-required="true" data-label="Name of the title" data-bs-toggle="tooltip" title="username:password">
                                                <span id="name_err"></span>
                                            </div>
                                            <div class="form-group has-label col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Sleep Time</label>
                                                <input class="form-control" type="text" name="sleep-time[]" data-required="true" data-label="Name of the title" data-bs-toggle="tooltip" title="The sleep time in milliseconds">
                                                <span id="name_err"></span>
                                            </div>
                                            <div class="form-group has-label col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Hash Check</label>
                                                <input class="form-control" type="text" name="hash-check[]" data-required="true" data-label="Name of the title" data-bs-toggle="tooltip" title="Hash to validate with">
                                                <span id="name_err"></span>
                                            </div>
                                            <div class="form-group has-label col-sm-6">
                                                <div class="form-group has-label resume-download-wrap" style="width: 26%;float: left;">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Resume Download</label>
                                                    <br />
                                                    <input type="checkbox" name="resume-download[]" class="resume-download-switch" checked>
                                                    <span id="name_err"></span>
                                                </div>
                                                <div class="form-group has-label propagation-wrap" style="width: 22%;float: left;" data-bs-toggle="tooltip" title="If propagation is On then it will try to get the file from nearby PC if exist, instead of downloading directly from configured URL">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Propagation</label>
                                                    <br />
                                                    <input type="checkbox" name="propagation[]" class="propagation-switch" checked>
                                                    <span id="name_err"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="container my-container">
                                        <div class="row">
                                            <div id="positive-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Positive</label>
                                                <input class="form-control" type="text" name="positive" data-required="true" data-label="Positive" data-bs-toggle="tooltip" title="Here you can configure button names. So that client can auto click on the buttons whenever any window will contain this button. Multiple button names can be configured by comma ',' delimiter and complete configuration should be in the same line of Positive: field. Note: Client is only capable to click the button for which it can capture the windows handler.">
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="negative-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Negative</label>
                                                <input class="form-control" type="text" name="negative" data-required="true" data-label="Negative">
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="special-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Special</label>
                                                <input class="form-control" type="text" name="special" data-required="true" data-label="Special" data-bs-toggle="tooltip" title="Here you can configure special buttons names which required double click on it. So that client can auto double click on the buttons whenever any window will contain this button. Multiple button names can be configured by comma ',' delimiter and complete configure should be in the same line of special: field">
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="process-to-kill-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Process To Kill</label>
                                                <input class="form-control" type="text" name="process-to-kill" data-required="true" data-label="Process To Kill">
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="log-file-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Log File</label>
                                                <input class="form-control" type="text" name="log-file" data-required="true" data-label="Log File">
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="default-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Default</label>
                                                <input class="form-control" type="text" name="default" data-required="true" data-label="Name of the title">
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="max-time-per-patch-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Max Time per Patch</label>
                                                <input class="form-control" type="text" name="max-time-per-patch" data-required="true" data-label="Max Time per Patch">
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="status-message-box-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">*</span>
                                                <label class="tgr-title">Status Message Box</label>
                                                <br />
                                                <input type="checkbox" name="status-message-box" checked>
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="message-box-text-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Message Box Text</label>
                                                <input class="form-control" type="text" name="message-box-text" data-required="true" data-label="Message Box Text" data-bs-toggle="tooltip" title="Example : [1]-Downloading Antivirus.#[2]-Installing Antivirus.#[3]-Updating Antivirus.#[4]-Antivirus scan in progress.#[5]-Uninstalling Antivirus ">
                                                <span id="name_err"></span>
                                            </div>

                                            <div class="col-sm-6">
                                                <div id="global-wrap" class="form-group has-label" style="width: 12%;float: left;">
                                                    <span class="error">*</span>
                                                    <label class="tgr-title">Global</label>
                                                    <br />
                                                    <input type="checkbox" name="global" checked>
                                                    <span id="name_err"></span>
                                                </div>

                                                <div id="delete-log-file-wrap" class="form-group has-label default-hide swd-hide" style="width: 22%;float: left;" data-bs-toggle="tooltip" title="This parameter when set to On deletes any log file which gets generated by 288 DART">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Delete Log File</label>
                                                    <br />
                                                    <input type="checkbox" name="delete-log-file" checked>
                                                    <span id="name_err"></span>
                                                </div>
                                            </div>


                                        </div>
                                    </div>
                                    <!--                                     <div class="row">   
                                        <div class="col-sm-6">
                                            <div>
                                                
                                                <div id="positive-wrap" class="form-group has-label default-hide swd-hide">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Positive</label>
                                                    <input class="form-control" type="text" name="positive" data-required="true" data-label="Positive" data-bs-toggle="tooltip" title="Here you can configure button names. So that client can auto click on the buttons whenever any window will contain this button. Multiple button names can be configured by comma ',' delimiter and complete configuration should be in the same line of Positive: field. Note: Client is only capable to click the button for which it can capture the windows handler."> 
                                                    <span id="name_err"></span>
                                                </div>
                                                <div id="negative-wrap" class="form-group has-label default-hide swd-hide">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Negative</label>
                                                    <input class="form-control" type="text" name="negative" data-required="true" data-label="Negative"> 
                                                    <span id="name_err"></span>
                                                </div>
                                                <div id="special-wrap" class="form-group has-label default-hide swd-hide">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Special</label>
                                                    <input class="form-control" type="text" name="special" data-required="true" data-label="Special" data-bs-toggle="tooltip" title="Here you can configure special buttons names which required double click on it. So that client can auto double click on the buttons whenever any window will contain this button. Multiple button names can be configured by comma ',' delimiter and complete configure should be in the same line of special: field"> 
                                                    <span id="name_err"></span>
                                                </div>
                                                <div id="process-to-kill-wrap" class="form-group has-label default-hide swd-hide">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Process To Kill</label>
                                                    <input class="form-control" type="text" name="process-to-kill" data-required="true" data-label="Process To Kill"> 
                                                    <span id="name_err"></span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-sm-6">
                                            <div>
                                                <div id="log-file-wrap" class="form-group has-label default-hide swd-hide">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Log File</label>
                                                    <input class="form-control" type="text" name="log-file" data-required="true" data-label="Log File"> 
                                                    <span id="name_err"></span>
                                                </div>
                                                <div id="default-wrap" class="form-group has-label default-hide swd-hide">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Default</label>
                                                    <input class="form-control" type="text" name="default" data-required="true" data-label="Name of the title"> 
                                                    <span id="name_err"></span>
                                                </div>
                                                <div id="max-time-per-patch-wrap" class="form-group has-label default-hide swd-hide">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Max Time per Patch</label>
                                                    <input class="form-control" type="text" name="max-time-per-patch" data-required="true" data-label="Max Time per Patch"> 
                                                    <span id="name_err"></span>
                                                </div>
                                                <div id="status-message-box-wrap" class="form-group has-label default-hide swd-hide">
                                                    <span class="error">*</span>
                                                    <label class="tgr-title">Status Message Box</label>
                                                    <br />
                                                    <input type="checkbox" name="status-message-box" checked>
                                                    <span id="name_err"></span>
                                                </div>
                                                <div id="message-box-text-wrap" class="form-group has-label default-hide swd-hide">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Message Box Text</label>
                                                    <input class="form-control" type="text" name="message-box-text" data-required="true" data-label="Message Box Text" data-bs-toggle="tooltip" title="Example : [1]-Downloading Antivirus.#[2]-Installing Antivirus.#[3]-Updating Antivirus.#[4]-Antivirus scan in progress.#[5]-Uninstalling Antivirus "> 
                                                    <span id="name_err"></span>
                                                </div>
                                                
                                                <div id="global-wrap" class="form-group has-label" style="width: 14%;float: left;">
                                                    <span class="error">*</span>
                                                    <label class="tgr-title">Global</label>
                                                    <br />
                                                    <input type="checkbox" name="global" checked>
                                                    <span id="name_err"></span>
                                                </div>
                                                
                                                <div id="delete-log-file-wrap" class="form-group has-label default-hide swd-hide" style="width: 22%;float: left;" data-bs-toggle="tooltip" title="This parameter when set to On deletes any log file which gets generated by 288 DART">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Delete Log File</label>
                                                    <br />
                                                    <input type="checkbox" name="delete-log-file" checked>
                                                    <span id="name_err"></span>
                                                </div>
                                                
                                            </div>
                                        </div>
                                        
                                    </div>-->
                                </div>

                            </div>
                        </div>

                        <div class="button col-md-12 text-center btBtn btBtnright">
                            <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next defualt-hide swd-hide" id="change-platform-btn">Platform</button>
                            <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next defualt-hide swd-hide" id="swd-prev-btn">Previous</button>
                            <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next defualt-hide swd-hide" id="swd-next-btn">Next</button>
                            <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next defualt-hide swd-hide" id="swd-save-btn" onclick="saveProfileDetails()">Save</button>
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

    <div class="btnGroup" style="z-index: 100000;" >
        <div class="icon-circle">
            <div class="toolTip" onclick="onSubmit();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Submit</span>
            </div>
        </div>
    </div>
    
    <div class="card-title">
        <h4 id="dcs-title">Dart 230</h4>
        <a href="javascript:void(0)" class="11 closebtn rightslide-container-close" data-bs-target="dart-role">&times;</a>
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
    <div class="card-title">
        <h4>Attach Profile</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="attach-profile">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="emailDistribution" onclick="attachProfileTo()">
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
                <div class="form-check form-check-radio">
                    <label class="form-check-label col-md-3">
                        <input class="form-check-input" type="radio" id="proatt-sites" name="profattch" value="sites">
                        <span class="form-check-sign"></span> Sites
                    </label>

                    <label class="form-check-label col-md-3">
                        <input class="form-check-input" type="radio" id="proatt-sites" name="profattch" value="groups">
                        <span class="form-check-sign"></span> Groups
                    </label>
                </div>
                <div>&nbsp;</div>
            </div>

            <div class="card-body site-div" style="display: none;">
                <div class="form-group has-label">
                    <label>Sites</label>
                </div>
                <select class="selectpicker" multiple data-style="btn btn-info" title="Select Sites" data-size="3" id="siteList" name="siteList">
                    <?php foreach ($siteList as $value) { ?>
                        <option value="<?php echo url::toText($value); ?>"><?php echo $value; ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="card-body group-div" style="display: none;">
                <div class="form-group has-label">
                    <label>Groups</label>
                </div>
                <select class="selectpicker" multiple data-style="btn btn-info" title="Select Groups" data-size="3" id="groupList" name="groupList">
                    <?php foreach ($groupList as $value) { ?>
                        <option value="<?php echo url::toText($value['mgroupid']); ?>"><?php echo $value['name']; ?></option>
                    <?php } ?>
                </select>
            </div>
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

    select.dart-select.left {
        width: 96%;
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

    #accordion .card div.card-header {
        width: 98%;
    }

    .well {
        background-color: #f9f9f9;
        border: 1px solid #ececec;
        padding: 5px 14px;
        margin-bottom: 10px;
    }

    select {
        padding: 0px 14px !important;
    }


    .tooltip-main {
        width: 15px;
        height: 15px;
        border-radius: 50%;
        font-weight: 700;
        background: #f3f3f3;
        border: 1px solid #737373;
        color: #737373;
        margin: 4px 121px 0 5px;
        float: right;
        text-align: left !important;
    }

    .tooltip-qm {
        float: left;
        margin: -2px 0px 3px 4px;
        font-size: 12px;
    }

    .tooltip-inner {
        max-width: 236px !important;
        font-size: 12px;
        padding: 10px 15px 10px 20px;
        background: #000;
        color: #fff;
        border: 1px solid #737373;
        text-align: left;
    }

    .tooltip.show {
        opacity: 1;
    }

    .bs-tooltip-auto[x-placement^=bottom] .arrow::before,
    .bs-tooltip-bottom .arrow::before {
        border-bottom-color: #f00;
        /* Red */
    }

    /*    .mstw*/
</style>

<script type="text/template" id="qq-template-manual-trigger">
    <div class="qq-uploader-selector qq-uploader" qq-drop-area-text="">
<div class="qq-total-progress-bar-container-selector qq-total-progress-bar-container" style="position: relative;left: -60px !important;">
<div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-total-progress-bar-selector qq-progress-bar qq-total-progress-bar"></div>
</div>
<div class="qq-upload-drop-area-selector qq-upload-drop-area" qq-hide-dropzone>
<span class="qq-upload-drop-area-text-selector"></span>
</div>
<div class="buttons">
<div class="qq-upload-button-selector btn btn-round btn-rose btn-file btn-sm">
<div>Select files</div>
</div>
<button type="button" id="trigger-upload" class="qq-upload-button" style="display: none;">
<i class="icon-upload icon-white"></i> Upload
</button>
</div>
<span class="qq-drop-processing-selector qq-drop-processing">
<span>Processing dropped files...</span>
<span class="qq-drop-processing-spinner-selector qq-drop-processing-spinner"></span>
</span>
<ul class="qq-upload-list-selector qq-upload-list" aria-live="polite" aria-relevant="additions removals">
<li>
<div class="qq-progress-bar-container-selector">
<div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-progress-bar-selector qq-progress-bar"></div>
</div>
<span class="qq-upload-spinner-selector qq-upload-spinner"></span>
<img class="qq-thumbnail-selector" qq-max-size="100" qq-server-scale>
<span class="qq-upload-file-selector qq-upload-file"></span>
<span class="qq-edit-filename-icon-selector qq-edit-filename-icon" aria-label="Edit filename"></span>
<input class="qq-edit-filename-selector qq-edit-filename" tabindex="0" type="text">
<span class="qq-upload-size-selector qq-upload-size"></span>
<button type="button" class="qq-btn qq-upload-cancel-selector qq-upload-cancel">Cancel</button>
<button type="button" class="qq-btn qq-upload-retry-selector qq-upload-retry">Retry</button>
<button type="button" class="qq-upload-delete-selector btn btn-success btn-round btn-sm">Delete</button>
<span role="status" class="qq-upload-status-text-selector qq-upload-status-text"></span>
</li>
</ul>

<dialog class="qq-confirm-dialog-selector">
<div class="qq-dialog-message-selector"></div>
<div class="qq-dialog-buttons">
<button type="button" class="qq-cancel-button-selector">No</button>
<button type="button" class="qq-ok-button-selector">Yes</button>
</div>
</dialog>

<dialog class="qq-prompt-dialog-selector">
<div class="qq-dialog-message-selector"></div>
<input type="text">
<div class="qq-dialog-buttons">
<button type="button" class="qq-cancel-button-selector">Cancel</button>
<button type="button" class="qq-ok-button-selector">Ok</button>
</div>
</dialog>
</div>
</script>