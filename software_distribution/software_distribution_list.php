<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?>
<div class="content white-content profilePage">
    <div class="row mt-2">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body" id="swd-wrapper">
                    <div id="loader" class="loader"  data-qa="loader" style="display:none;width:100%;z-index: 1000;position: relative;left:48%;height:100%"><img src="../assets/img/nanohealLoader.gif" style="margin-top: 20%;width: 71px;"></div>

                    <div class="toolbar">
                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a id="add-swd" class="dropdown-item">Add</a>
                                    <a id="edit-distribute-hand" class="dropdown-item" style="display:none" href="javascript:void(0)">Edit Distribute</a>
                                    <a id="configure-execute-hand" class="dropdown-item" style="display:none" href="javascript:void(0)">Configure Execute</a>
                                    <a id="edit-execute-hand" class="dropdown-item" style="display:none" href="javascript:void(0)">Edit Execute</a>
                                    <a id="rsn-distibute-execute-hand" class="dropdown-item" href="javascript:void(0)">Distribute/Execute</a>
                                    <a id="rsc-ftp-cdn-configuration-hand" class="dropdown-item" href="javascript:void(0)">Configure CDN</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table class="nhl-datatable table table-striped" id="swd-list-dtable">
                        <thead>
                            <tr>
                                <th class="sl-id" style="width:0%">Id</th>
                                <th class="sl-icon" style="width:10%">Logo</th>
                                <th class="sl-name" style="width:20%">Name</th>
                                <th class="sl-platform" style="width:20%">Platform</th>
                                <th class="sl-distribution" style="width:10%">Distribution</th>
                                <th class="sl-execution" style="width:10%">Execution</th>
                                <th class="sl-global" style="width:10%">Global</th>
                                <th class="sl-created" style="width:15%">Created On</th>
                                <th class="sl-updated" style="width:15%">Updated On</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <form name="save-package-form" action="profile.php" method="POST" onsubmit="processForm(event, $(this));return false;">
                    <div class="card-body" style="display: none;" id="widget-container">
                        <div class="toolbar">
                            <div class="bullDropdown leftDropdown">
                                <div class="dropdown">
                                    <span>Software Distribution</span>
                                </div>
                            </div>

                            <div class="bullDropdown">
                                <div class="dropdown">
                                    <div id="close-swd-widget" class="r-ic">
                                        <i class="tim-icons icon-simple-remove"></i>
                                        <img class="swd-progress-loader" src="../assets/img/loader-sm.gif">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row clearfix innerPage">
                            <div class="col-md-12">
                                <div class="col-md-1 col-sm-2 lf-equalHeight" id="dispMenu">
                                    <div class="col-md-12 addBox side-nav-it active" data-disp="1"><span class="arrow-right">&nbsp;</span>
                                        <p>1</p><span>Platform</span>
                                    </div>
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
                                        <div class="row justify-content-center platform-row" style="position: relative;top: 80px;">
                                            <input type="hidden" autocomplete="off" class="swd-ic-it-input" name="platform" />
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
                                            <input type="hidden" autocomplete="off" value="" class="swd-ic-it-input" name="package-type" data-label="Package Type" />
                                            <div class="col-sm-2 swd-ic-it swd-pt" data-indentifier-key="type" data-type="distribute" style="cursor:pointer"><img src="../assets/img/ic-pkg-distribute.png" style="margin: 0 auto;display: block;" alt="Distribute" /><br />
                                                <p>Distribute</p>
                                            </div>
                                            <div class="col-sm-2 swd-ic-it swd-pt" data-indentifier-key="type" data-type="execute" style="cursor:pointer"><img src="../assets/img/ic-pkg-execute.png" style="margin: 0 auto;display: block;" alt="Execute" /><br />
                                                <p>Execute</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group has-label default-hide swd-hide windows-grid">
                                        <span class="error">*</span>
                                        <label class="tgr-title">System Type</label>
                                    </div>
                                    <div class="container default-hide swd-hide windows-grid">
                                        <div class="row justify-content-center swd-ic-it-group" style="position: relative;">
                                            <input type="hidden" autocomplete="off" value="" class="swd-ic-it-input" name="windows-type" data-label="System Type" />
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

                                <div id="source-wrap" class="col-md-11 col-sm-10 table-responsive rt-equalHeight defualt-hide swd-hide eachPWrap" data-wrap-name="source" data-populate="true">
                                    <div class="container">
                                        <div class="mt-z row my-row sec-row">
                                            <div class="col-sm-6 swd-hide my-grid">
                                                <div class="well well-sm default-hide swd-hide"></div>
                                                <div class="row my-btn-group default-hide swd-hide">
                                                    <div class="btn-group btn-group-toggle float-right" data-toggle="buttons">
                                                        <label class="btn btn-md btn-primary btn-simple active wt-hand default-hide swd-hide source-upload" data-target="s-32-1" data-value="1">
                                                            <input type="radio" name="options" checked="">
                                                            <span class="d-none d-sm-block d-md-block d-lg-block d-xl-block">Upload</span>
                                                            <span class="d-block d-sm-none">
                                                                <i class="tim-icons icon-single-02"></i>
                                                            </span>
                                                        </label>
                                                        <label class="btn btn-md btn-primary btn-simple wt-hand default-hide swd-hide source-url" data-target="s-32-2" data-value="2">
                                                            <input type="radio" class="d-none d-sm-none" name="options">
                                                            <span class="d-none d-sm-block d-md-block d-lg-block d-xl-block">URL</span>
                                                            <span class="d-block d-sm-none">
                                                                <i class="tim-icons icon-gift-2"></i>
                                                            </span>
                                                        </label>
                                                        <label class="btn btn-md btn-primary btn-simple wt-hand default-hide swd-hide source-path" data-target="s-32-3" data-value="3">
                                                            <input type="radio" class="d-none d-sm-none" name="options">
                                                            <span class="d-none d-sm-block d-md-block d-lg-block d-xl-block">Path</span>
                                                            <span class="d-block d-sm-none">
                                                                <i class="tim-icons icon-gift-2"></i>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="wt-group">
                                                    <input type="hidden" value="1" name="source-type" class="wt-value" data-required="true" data-label="Source Type" />
                                                    <div id="s-32-1" class="wt-wrap default-hide swd-hide qq-up-wrap-parent" style="margin-bottom:10px">
                                                        <div class="form-group has-label default-hide swd-hide upload-to">
                                                            <span class="error">*</span>
                                                            <label class="tgr-title">Upload To</label>
                                                            <br />
                                                            <input class="my-upload-type" type="checkbox" name="upload-to" data-required="true" data-label="Upload To" checked>
                                                            <span id="name_err"></span>
                                                        </div>
                                                        <div id="upload-widget-1" class="qq-up-wrap"></div>
                                                        <input name="uploaded-file-name" value="" class="uploaded-fn" type="hidden">
                                                    </div>

                                                    <div id="s-32-2" class="wt-wrap default-hide swd-hide">
                                                        <div class="form-group has-label">
                                                            <span class="error">*</span>
                                                            <label class="tgr-title">Url</label>
                                                            <input class="form-control" type="text" name="package-url" data-required="true" data-label="Url" data-max-length="512" data-url="true">
                                                            <span id="name_err"></span>
                                                        </div>
                                                    </div>
                                                    <div id="s-32-3" class="wt-wrap default-hide swd-hide">
                                                        <div class="form-group has-label">
                                                            <span class="error">*</span>
                                                            <label class="tgr-title">Path</label>
                                                            <input class="form-control" type="text" name="package-path" data-required="true" data-label="Path" data-max-length="512">
                                                            <span id="name_err"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group has-label default-hide swd-hide distribution-path-wrap">
                                                    <span class="error">*</span>
                                                    <label class="tgr-title">Distribution Path</label>
                                                    <input class="form-control" type="text" name="distribution-path" data-required="true" data-label="Distribution Path" data-max-length="255">
                                                    <span id="distribution-path-error"></span>
                                                </div>

                                                <div class="form-group has-label default-hide swd-hide command-line-wrap">
                                                    <span class="error">&nbsp;</span>
                                                    <label class="tgr-title">Command Line</label>
                                                    <input class="form-control" type="text" name="command-line" data-label="Command Line" data-max-length="255">
                                                    <span id="distribution-path-error"></span>
                                                </div>

                                                <div class="form-group has-label default-hide swd-hide post-validation-wrap">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Post Validation</label>
                                                    <input class="form-control" type="text" name="post-validation" data-toggle="tooltip" title="The post validation to check" data-max-length="128">
                                                    <span id="name_err"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="container">
                                        <div class="row my-row">
                                            <div class="col-sm-6">
                                                <div id="package_name-wrap" class="form-group has-label">
                                                    <span class="error">*</span>
                                                    <label class="tgr-title">Package/App Name</label>
                                                    <input class="form-control" type="text" name="package-name" data-required="true" data-label="Package/App Name" data-max-length="128">
                                                    <span id="name_err"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="container more-opt-box">
                                        <div class="row">
                                            <div class="col-md-2 my-icon-wrap" id="add-more-section">
                                                <span><i class="tim-icons icon-simple-add my-icon"></i> Add more...</span>
                                            </div>
                                            <div class="col-md-2 my-icon-wrap swd-hide" id="remove-last-section">
                                                <span><i class="tim-icons icon-simple-delete my-icon"></i> Remove last...</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>


                                <!-- pre check Info starts here -->

                                <div id="pre-check-wrap" class="col-md-11 col-sm-10 table-responsive rt-equalHeight defualt-hide swd-hide eachPWrap" data-wrap-name="pre-check" data-populate="true">
                                    <div class="container">
                                        <div class="mt-x row my-row sec-row">
                                            <div class="col-sm-6 swd-hide my-grid">
                                                <div class="well well-sm default-hide swd-hide"></div>
                                                <div class="row my-btn-group default-hide swd-hide">
                                                    <div class="btn-group btn-group-toggle float-right" data-toggle="buttons">
                                                        <label class="btn btn-md btn-primary btn-simple active wt-hand default-hide swd-hide pc-btg-h-reg" data-target="s1-32-1" data-value="3">
                                                            <input type="radio" class="d-none" name="options">
                                                            <span class="d-none d-sm-block d-md-block d-lg-block d-xl-block">Registry</span>
                                                            <span class="d-block d-sm-none">
                                                                <i class="tim-icons icon-tap-02"></i>
                                                            </span>
                                                        </label>
                                                        <label class="btn btn-md btn-primary btn-simple wt-hand default-hide swd-hide pc-btg-h-file" data-target="s1-32-2" data-value="1">
                                                            <input type="radio" name="options" checked="">
                                                            <span class="d-none d-sm-block d-md-block d-lg-block d-xl-block">File</span>
                                                            <span class="d-block d-sm-none">
                                                                <i class="tim-icons icon-single-02"></i>
                                                            </span>
                                                        </label>
                                                        <label class="btn btn-md btn-primary btn-simple wt-hand default-hide swd-hide pc-btg-h-sn" data-target="s1-32-3" data-value="2">
                                                            <input type="radio" class="d-none d-sm-none" name="options">
                                                            <span class="d-none d-sm-block d-md-block d-lg-block d-xl-block">Software Name</span>
                                                            <span class="d-block d-sm-none">
                                                                <i class="tim-icons icon-gift-2"></i>
                                                            </span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="wt-group">
                                                    <input type="hidden" value="3" name="precheck-type" class="wt-value">
                                                    <div id="s1-32-1" class="wt-wrap default-hide swd-hide registry-precheck-wrap">
                                                        <div class="form-group has-label">
                                                            <div class="form-check form-check-radio">
                                                                <label class="form-check-label">
                                                                    <input class="form-check-input" type="radio" value="0" name="registry-precheck" data-required="true" data-label="Registry Precheck If" checked>
                                                                    <span class="form-check-sign"></span> Execute when pre-check value exists
                                                                </label>
                                                            </div>

                                                            <div class="form-check form-check-radio">
                                                                <label class="form-check-label">
                                                                    <input class="form-check-input" type="radio" value="1" name="registry-precheck" data-required="true" data-label="Registry Precheck If">
                                                                    <span class="form-check-sign"></span> Execute when pre-check value doesn't exists
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group has-label">
                                                            <span class="error">&nbsp;</span>
                                                            <label class="tgr-title">Root Key</label>
                                                            <select name="root-key" title="Root Key" class="form-control dropdown-submenu" data-size="5" data-width="100%" data-label="Root Key" data-max-length="1">
                                                                <option value="">Select Root Key</option>
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
                                                            <span class="error">&nbsp;</span>
                                                            <label class="tgr-title">Sub Key</label>
                                                            <input class="form-control" type="text" name="sub-key" data-required="true" data-label="Sub Key" data-max-length="128">
                                                            <span id="name_err"></span>
                                                        </div>
                                                        <div class="form-group has-label">
                                                            <span class="error">&nbsp;</span>
                                                            <label class="tgr-title">Name</label>
                                                            <input class="form-control" type="text" name="sub-key-name" data-required="true" data-label="Name" data-max-length="128">
                                                            <span id="name_err"></span>
                                                        </div>
                                                        <div class="form-group has-label">
                                                            <span class="error">&nbsp;</span>
                                                            <label class="tgr-title">Type</label>
                                                            <select name="type" id="type" title="Type" class="form-control dropdown-submenu" data-size="5" data-width="100%" data-label="Type" data-max-length="16">
                                                                <option value="">Select Type</option>
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
                                                            <span class="error">&nbsp;</span>
                                                            <label class="tgr-title">Value</label>
                                                            <input class="form-control" type="text" name="type-value" data-required="true" data-label="Type Value" data-max-length="128">
                                                            <span id="name_err"></span>
                                                        </div>
                                                    </div>
                                                    <div id="s1-32-2" class="wt-wrap default-hide swd-hide file-precheck-wrap">
                                                        <div class="form-group has-label">
                                                            <div class="form-check form-check-radio">
                                                                <label class="form-check-label">
                                                                    <input class="form-check-input" type="radio" value="0" name="file-precheck" data-required="true" data-label="File Precheck If" checked>
                                                                    <span class="form-check-sign"></span> Execute when pre-check value exists
                                                                </label>
                                                            </div>

                                                            <div class="form-check form-check-radio">
                                                                <label class="form-check-label">
                                                                    <input class="form-check-input" type="radio" value="1" name="file-precheck" data-label="File Precheck If">
                                                                    <span class="form-check-sign"></span> Execute when pre-check value doesn't exists
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="form-group has-label">
                                                            <span class="error">&nbsp;</span>
                                                            <label class="tgr-title">File Path</label>
                                                            <input class="form-control" type="text" name="file-path" data-required="true" data-label="File Path" data-max-length="512">
                                                            <span id="name_err"></span>
                                                        </div>
                                                    </div>
                                                    <div id="s1-32-3" class="default-hide swd-hide wt-wrap software-precheck-wrap">
                                                        <div class="form-group has-label">
                                                            <span class="error">&nbsp;</span>
                                                            <label class="tgr-title">Software Name</label>
                                                            <input class="form-control" type="text" name="software-name" data-required="true" data-label="Software Name" data-max-length="255">
                                                            <span id="name_err"></span>
                                                        </div>
                                                        <div class="form-group has-label">
                                                            <span class="error">&nbsp;</span>
                                                            <label class="tgr-title">Software Version</label>
                                                            <input class="form-control" type="text" name="software-version" data-required="true" data-label="Software Version" data-max-length="32">
                                                            <span id="name_err"></span>
                                                        </div>
                                                        <div class="form-group has-label">
                                                            <span class="error">&nbsp;</span>
                                                            <label class="tgr-title">Knowledge Base</label>
                                                            <input class="form-control" type="text" name="knowledge-base" data-required="true" data-label="Knowledge Base" data-max-length="32">
                                                            <span id="name_err"></span>
                                                        </div>
                                                        <div class="form-group has-label">
                                                            <span class="error">&nbsp;</span>
                                                            <label class="tgr-title">Service Pack</label>
                                                            <input class="form-control" type="text" name="service-pack" data-required="true" data-label="Service Pack" data-max-length="32">
                                                            <span id="name_err"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- Finish starts here -->

                                <div id="finish-wrap" class="col-md-11 col-sm-10 table-responsive rt-equalHeight defualt-hide swd-hide eachPWrap" data-wrap-name="finish" data-populate="true">

                                    <div class="container my-container options-section">
                                        <div class="row sec-row">
                                            <div class="form-group has-label col-sm-6  default-hide swd-hide api-mode-wrap">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Run as</label>
                                                <select class="form-control" name="api-mode[]" data-max-length="1">
                                                    <option value="4">User</option>
                                                    <option value="1">System</option>
                                                    <option value="6">Administrator</option>
                                                </select>
                                                <span id="name_err"></span>
                                            </div>
                                            <div class="form-group has-label col-sm-6  default-hide swd-hide session-wrap" data-max-length="1">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Session</label>
                                                <select class="form-control" name="session[]">
                                                    <option value="1">User</option>
                                                    <option value="0">Interactive</option>
                                                </select>
                                                <span id="name_err"></span>
                                            </div>
                                            <div class="form-group has-label col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Url Credential</label>
                                                <input class="form-control" type="text" name="url-credential[]" data-required="false" data-label="Name of the title" data-toggle="tooltip" title="username:password" data-max-length="128">
                                                <span id="name_err"></span>
                                            </div>
                                            <div class="form-group has-label col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Sleep Time</label>
                                                <input class="form-control" type="text" name="sleep-time[]" data-required="false" data-label="Name of the title" data-toggle="tooltip" title="The sleep time in milliseconds" data-max-length="10">
                                                <span id="name_err"></span>
                                            </div>
                                            <div class="form-group has-label col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Hash Check</label>
                                                <input class="form-control" type="text" name="hash-check[]" data-required="false" data-label="Name of the title" data-toggle="tooltip" title="Hash to validate with" data-max-length="255">
                                                <span id="name_err"></span>
                                            </div>
                                            <div class="form-group has-label col-sm-6">
                                                <div class="form-group has-label resume-download-wrap" data-name="resume-download" style="width: 26%;float: left;">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Resume Download</label>
                                                    <br />
                                                    <input type="checkbox" name="resume-download[0]" class="resume-download-switch" checked>
                                                    <span id="name_err"></span>
                                                </div>
                                                <div class="form-group has-label propagation-wrap" data-name="propagation" style="width: 22%;float: left;" data-toggle="tooltip" title="If propagation is On then it will try to get the file from nearby PC if exist, instead of downloading directly from configured URL">
                                                    <span class="error">&nbsp</span>
                                                    <label class="tgr-title">Propagation</label>
                                                    <br />
                                                    <input type="checkbox" name="propagation[0]" class="propagation-switch" data-max-length="2" checked>
                                                    <span id="name_err"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <i style="position: relative;top:-6px;cursor: pointer" data-toggle="tooltip" title="" data-original-title="The options below will be saved as common for both execution and distribution" class="fa fa-info-circle"></i>
                                    <!--<p style="font-size: 9px;margin-bottom:14px;"><b>Note :</b> The options below will saved as common for both execution and distribution</p>-->
                                    <div class="container my-container">
                                        <div class="row">
                                            <div id="positive-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Positive</label>
                                                <input class="form-control" type="text" name="positive" data-required="false" data-label="Positive" data-max-length="1024" data-toggle="tooltip" title="Here you can configure button names. So that client can auto click on the buttons whenever any window will contain this button. Multiple button names can be configured by comma ',' delimiter and complete configuration should be in the same line of Positive: field. Note: Client is only capable to click the button for which it can capture the windows handler.">
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="negative-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Negative</label>
                                                <input class="form-control" type="text" name="negative" data-required="false" data-label="Negative" data-max-length="1024">
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="special-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Special</label>
                                                <input class="form-control" type="text" name="special" data-required="false" data-label="Special" data-max-length="1024" data-toggle="tooltip" title="Here you can configure special buttons names which required double click on it. So that client can auto double click on the buttons whenever any window will contain this button. Multiple button names can be configured by comma ',' delimiter and complete configure should be in the same line of special: field">
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="process-to-kill-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Process To Kill</label>
                                                <input class="form-control" type="text" name="process-to-kill" data-required="false" data-label="Process To Kill" data-max-length="255">
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="log-file-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Log File</label>
                                                <input class="form-control" type="text" name="log-file" data-required="false" data-label="Log File" data-max-length="1024">
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="default-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Default</label>
                                                <input class="form-control" type="text" name="default" data-required="false" data-label="Name of the title" data-max-length="1024">
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="max-time-per-patch-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">*</span>
                                                <label class="tgr-title">Max Time per Patch</label>
                                                <input class="form-control" type="text" name="max-time-per-patch" data-required="true" data-numeric="true" data-label="Max Time per Patch" data-max-length="1024">
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="status-message-box-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp;</span>
                                                <label class="tgr-title">Status Message Box</label>
                                                <br />
                                                <input type="checkbox" name="status-message-box" data-max-length="2" checked>
                                                <span id="name_err"></span>
                                            </div>
                                            <div id="message-box-text-wrap" class="form-group has-label default-hide swd-hide col-sm-6">
                                                <span class="error">&nbsp</span>
                                                <label class="tgr-title">Message Box Text</label>
                                                <input class="form-control" type="text" name="message-box-text" data-required="false" data-label="Message Box Text" data-max-length="1024" data-toggle="tooltip" title="Example : [1]-Downloading Antivirus.#[2]-Installing Antivirus.#[3]-Updating Antivirus.#[4]-Antivirus scan in progress.#[5]-Uninstalling Antivirus ">
                                                <span id="name_err"></span>
                                            </div>

                                            <div class="col-sm-6">
                                                <div id="global-wrap" class="form-group has-label" style="width: 12%;float: left;">
                                                    <span class="error">&nbsp;</span>
                                                    <label class="tgr-title">Global</label>
                                                    <br />
                                                    <input type="checkbox" name="global" data-max-length="2" checked>
                                                    <span id="name_err"></span>
                                                </div>

                                                <div id="delete-log-file-wrap" class="form-group has-label default-hide swd-hide" style="width: 22%;float: left;" data-toggle="tooltip" title="This parameter when set to On deletes any log file which gets generated by 288 DART">
                                                    <span class="error">&nbsp;</span>
                                                    <label class="tgr-title">Delete Log File</label>
                                                    <br />
                                                    <input type="checkbox" name="delete-log-file" data-max-length="1024" checked>
                                                    <span id="name_err"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="button col-md-12 text-center btBtn btBtnright">
                            <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next defualt-hide swd-hide" id="change-platform-btn">Platform</button>
                            <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next defualt-hide swd-hide" id="swd-prev-btn">Previous</button>
                            <button type="button" class="swal2-confirm btn btn-success btn-sm btn-next defualt-hide swd-hide" id="swd-next-btn">Next</button>
                            <button type="submit" class="swal2-confirm btn btn-success btn-sm btn-next defualt-hide swd-hide" id="swd-save-btn">Save</button>
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

<form style="display:none" name="details-form" method="POST" action="<?php echo $base_url . $moduleName . '/' . $moduleName . '.php?'; ?>function=details">
    <input type="hidden" name="id" value="" autocomplete="off" />
</form>

<form style="display:none" name="configuration-form" method="POST" action="<?php echo $base_url . $moduleName . '/' . $moduleName . '.php?'; ?>function=configuration">
    <input type="hidden" name="id" value="" autocomplete="off" />
    <input type="hidden" name="type" value="" autocomplete="off" />
</form>

<div id="rsn-distibute-execute" class="rightSidenav" data-class="md-6">
    <div class="card-title">
        <h4>Distribute/Execute</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="rsn-distibute-execute">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="push-configuration">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form name="push-configuration-form" method="post" action="<?php echo $base_url . $moduleName . '/' . $moduleName . '.php?'; ?>function=push-configuration" onsubmit="pushConfigurationEvent($(this), event); return false">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <label class="form-check-label"><span class="site indistro-selection-label"><?php echo $_SESSION['searchValue']; ?></span>
                                <button type="button" class="swal2-confirm btn btn-default btn-sm indistro-sitegroup-selection" value="Change" aria-label="">Change</button>
                            </label>
                            <img id="push-status-img" src="<?= $base_url ?>assets/img/loader-sm.gif" style="margin-left: 25px;display:none">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-10">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="ck-conf-hand" value="distribute" type="checkbox" checked>
                                    <span class="form-check-sign"></span>
                                    Distribute
                                </label>
                            </div>
                            <div class="form-check form-check-inline" style="margin-left: 32px;">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="ck-conf-hand" value="execute" type="checkbox" checked>
                                    <span class="form-check-sign"></span>
                                    Execute
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12">
                            <pre id="config-readonly" style="margin-top: 26px;font-size: 14px;"></pre>
                            <input type="submit" name="submit-configuration" value="Push" style="display:none" />
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="button col-md-12 text-center slider-feedwrapper">
        <span id="checkavail" localized="" class="inslider-feed error tm0"></span>
    </div>
</div>


<!-- FTP/CDN Configure start -->

<div id="rsc-ftp-cdn-configuration" class="rightSidenav" data-class="md-6">
    <div class="card-title">
        <h4>CDN Configuration</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="rsc-ftp-cdn-configuration">&times;</a>
    </div>

    <div class="btnGroup">
        <div id="save-credentials-event-hand" class="icon-circle">
            <div class="toolTip">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <div class="card">
            <div class="card-body">

                <div class="form-group has-label">
                    <input type="checkbox" name="cdn-ftp-config-type" data-required="true" data-label="Upload To" checked>
                </div>

                <div id="cdn-config-wrap">
                    <form onsubmit="saveCredentials($(this), event)" method="POST" action="<?php echo $base_url . $moduleName . '/' . $moduleName . '.php?'; ?>function=save-cdn-credentials" name="cdn-configure">
                        <div class="form-group has-label">
                            <span class="error">*</span>
                            <label id="cdnurlPick" for="cdnurl">URL</label>
                            <input class="form-control required" id="cdnurl" name="cdn-url" type="text" data-required="true" data-label="URL" data-url="true">
                            <span id="cdnurlMsg" class="errorMessage"></span>
                        </div>

                        <div class="form-group has-label">
                            <span class="error">*</span>
                            <label id="cdnAkPick" for="cdnAk">Access Key</label>
                            <input class="form-control required" id="cdnAk" name="cdn-ak" type="text" data-required="true" data-label="Access Key">
                            <span id="cdnAkMsg" class="errorMessage"></span>
                        </div>

                        <div class="form-group has-label">
                            <span class="error">*</span>
                            <label id="cdnSkPick" for="cdnSk">Secret Key</label>
                            <input class="form-control required" id="cdnSk" name="cdn-sk" type="text" data-required="true" data-label="Secret Key">
                            <span id="cdnSkMsg" class="errorMessage"></span>
                        </div>

                        <div class="form-group has-label">
                            <span class="error">*</span>
                            <label id="bucketPick" for="bucket">Bucket Name</label>
                            <input class="form-control required" id="bucket" name="cdn-bucket-name" type="text" data-required="true" data-label="Bucket Name">
                            <span id="bucketMsg" class="errorMessage"></span>
                        </div>

                        <div class="form-group has-label">
                            <span class="error">*</span>
                            <label id="regionPick" for="region">CDN Region</label>
                            <input class="form-control required" id="region" name='cdn-region' type="text" data-required="true" data-label="CDN Region">
                            <span id="regionMsg" class="errorMessage"></span>
                            <input type="submit" value="Save" style="display:none" />
                        </div>
                    </form>
                </div>

                <div id="ftp-config-wrap" style="display:none">
                    <form onsubmit="saveCredentials($(this), event)" method="POST" action="<?php echo $base_url . $moduleName . '/' . $moduleName . '.php?'; ?>function=save-ftp-credentials" name="ftp-configure">
                        <input type="hidden" name="ftp" value="1" />
                        <div class="form-group has-label ftpField">
                            <span class="error">*</span>
                            <label id="furlPick" for="furl">URL</label>
                            <input class="form-control" name="ftp-url" type="text" data-required="true" data-label="FTP URL" data-url="true">
                            <span id="furlMsg" class="errorMessage"></span>
                        </div>
                        <div class="form-group has-label">
                            <span class="error">&nbsp;</span>
                            <label id="fuserPick" for="fuser">Username</label>
                            <input class="form-control" name="ftp-username" type="text">
                            <span id="fuserMsg" class="errorMessage"></span>
                        </div>
                        <div class="form-group has-label">
                            <span class="error">&nbsp;</span>
                            <label id="fpwdPick" for="fpwd">Password</label>
                            <input class="form-control" name="ftp-password" type="password">
                            <span id="fpwdMsg" class="errorMessage"></span>
                            <input type="submit" value="Save" style="display:none" />
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


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
