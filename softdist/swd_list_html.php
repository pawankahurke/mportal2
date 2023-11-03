<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?>
<div class="content white-content">
    <input type="hidden" id="selected">
    <input type="hidden" id="selOsType">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <?php
                    //   nhRole::checkRoleForPage('softwaredistribution');
                    $res = true; //nhRole::checkModulePrivilege('softwaredistribution');
                    if ($res) {
                    ?>
                        <div id="absoLoader" style="display:none">
                            <img src="../assets/img/nanohealLoader.gif" style="width: 70px;">
                        </div>
                        <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="packageGrid">
                            <thead>
                                <!-- <th class="id" style="width:2%">Id</th> -->
                                <th id="key0" headers="packageName" class="  packageName " style="width:10%">
                                    Package Name
                                    <i class="fa fa-caret-down cursorPointer direction" id="packageName1" onclick="addActiveSort('asc', 'packageName'); Get_SoftwareRepositoryData(1,notifSearch='','packageName', 'asc');sortingIconColor('packageName1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id="packageName2" onclick="addActiveSort('desc', 'packageName'); Get_SoftwareRepositoryData(1,notifSearch='','packageName', 'desc');sortingIconColor('packageName2')" style="font-size:18px"></i>
                                </th>
                                <th id="key1" headers="packageDesc" class="  packageDesc " style="width:10%">
                                    Description
                                    <i class="fa fa-caret-down cursorPointer direction" id="packageDesc1" onclick="addActiveSort('asc', 'packageDesc'); Get_SoftwareRepositoryData(1,notifSearch='','packageDesc', 'asc');sortingIconColor('packageDesc1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id="packageDesc2" onclick="addActiveSort('desc', 'packageDesc'); Get_SoftwareRepositoryData(1,notifSearch='','packageDesc', 'desc');sortingIconColor('packageDesc2')" style="font-size:18px"></i>
                                </th>
                                <th id="key2" headers="version" class="  version " style="width:10%">
                                    Version
                                    <i class="fa fa-caret-down cursorPointer direction" id="version1" onclick="addActiveSort('asc', 'version'); Get_SoftwareRepositoryData(1,notifSearch='','version', 'asc');sortingIconColor('version1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id="version2" onclick="addActiveSort('desc', 'version'); Get_SoftwareRepositoryData(1,notifSearch='','version', 'desc');sortingIconColor('version2')" style="font-size:18px"></i>
                                </th>
                                <th id="key3" headers="platform" class="  platfrom " style="width:10%">
                                    Platform
                                    <i class="fa fa-caret-down cursorPointer direction" id="platform1" onclick="addActiveSort('asc', 'platform'); Get_SoftwareRepositoryData(1,notifSearch='','platform', 'asc');sortingIconColor('platform1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id="platform2" onclick="addActiveSort('desc', 'platform'); Get_SoftwareRepositoryData(1,notifSearch='','platform', 'desc');sortingIconColor('platform2')" style="font-size:18px"></i>
                                </th>
                                <th id="key4" headers="lastModified" class="  createdDate " style="width:10%">
                                    Date Added
                                    <i class="fa fa-caret-down cursorPointer direction" id="lastModified1" onclick="addActiveSort('asc', 'lastModified'); Get_SoftwareRepositoryData(1,notifSearch='','lastModified', 'asc');sortingIconColor('lastModified1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id="lastModified2" onclick="addActiveSort('desc', 'lastModified'); Get_SoftwareRepositoryData(1,notifSearch='','lastModified', 'desc');sortingIconColor('lastModified2')" style="font-size:18px"></i>
                                </th>
                                <th id="key5" headers="global" class="  global " style="width:5%">
                                    Global
                                    <i class="fa fa-caret-down cursorPointer direction" id="global1" onclick="addActiveSort('asc', 'global'); Get_SoftwareRepositoryData(1,notifSearch='','global', 'asc');sortingIconColor('global1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id="global2" onclick="addActiveSort('desc', 'global'); Get_SoftwareRepositoryData(1,notifSearch='','global', 'desc');sortingIconColor('global2')" style="font-size:18px"></i>
                                </th>
                                <!-- <th id="key5" headers="global" class="  global " style="width:5%">
                                Global
                                <i class="fa fa-caret-down cursorPointer direction" id = "global1" onclick = "Get_SoftwareRepositoryData(1,notifSearch='','global', 'asc');sortingIconColor('global1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "global2" onclick = "Get_SoftwareRepositoryData(1,notifSearch='','global', 'desc');sortingIconColor('global2')" style="font-size:18px"></i>
                                </th> -->
                            </thead>
                        </table>
                    <?php
                    }
                    ?>

                    <!-- <table id="packageGrid" class="nhl-datatable table table-striped">
                        <thead>
                            <tr>
                                <th class="id" style="width:2%">Id</th>
                                <th class="packageName" style="width:10%">Package Name</th>
                                <th class="packageDesc" style="width:10%">Description</th>
                                <th class="version" style="width:10%">Version</th>
                                <th class="platfrom" style="width:10%">Platform</th>
                                <th class="createdDate" style="width:10%">Date Added</th>
                                <th class="global" style="width:5%">Global</th>
                            </tr>
                        </thead>
                    </table> -->
                    <div id="largeDataPagination"></div>
                </div>
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>

<div id="rsc-add-container" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4>Add New Software for Distribution</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-add-container">&times;</a>
    </div>

    <div class="form table-responsive white-content" style="display: flex;">
        <iframe src="<?php echo getenv('VISUALISATION_SERVICE_DASH_URL'); ?>/#/softdist/addPackageForm?csrfMagicToken=<?php echo csrf_get_tokens(); ?>&PHPSESSID=<?php echo session_id(); ?>" style="flex: 1;" id="iFrame1" frameborder="0" scrolling="yes"></iframe>
    </div>

    <div class="button col-md-12 text-center slider-feedwrapper">
        <span id="checkavail" localized="" class="inslider-feed error tm0"></span>
    </div>

    <script type="text/template" id="qq-template-manual-trigger">
        <div class="qq-uploader-selector qq-uploader" qq-drop-area-text="">
        <div class="qq-total-progress-bar-container-selector qq-total-progress-bar-container" style="position: relative;left: -60px !important;">
        <div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-total-progress-bar-selector qq-progress-bar qq-total-progress-bar" style='display:none'></div>
        </div>
        <div class="qq-upload-drop-area-selector qq-upload-drop-area" qq-hide-dropzone>
        <span class="qq-upload-drop-area-text-selector"></span>
        </div>
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
        <div>&nbsp;</div>
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

    <script type="text/template" id="qq-template-manual-trigger2">
        <div class="qq-uploader-selector qq-uploader" qq-drop-area-text="">
        <div class="qq-total-progress-bar-container-selector qq-total-progress-bar-container" style="position: relative;left: -60px !important;">
        <div role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-total-progress-bar-selector qq-progress-bar qq-total-progress-bar" style='display:none'></div>
        </div>
        <div class="qq-upload-drop-area-selector qq-upload-drop-area" qq-hide-dropzone>
        <span class="qq-upload-drop-area-text-selector"></span>
        </div>
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
        <div>&nbsp;</div>
        <div class="buttons">
        <div class="qq-upload-button-selector btn btn-round btn-rose btn-file btn-sm">
        <div>Select files</div>
        </div>
        <button type="button" id="trigger-upload2" class="qq-upload-button" style="display: none;">
        <i class="icon-upload icon-white"></i> Upload
        </button>
        </div>
        <span class="qq-drop-processing-selector qq-drop-processing">
        <span>Processing dropped files...</span>
        <span class="qq-drop-processing-spinner-selector qq-drop-processing-spinner"></span>
        </span>


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
</div>
<!-- Distribute Configuration start -->

<div id="rsc-distribute-package" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4>Configure the Application/Package for Deploy & Execute</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-distribute-package">&times;</a>
    </div>

    <!-- <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip btnSaveSWD" onclick="distributePackage();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div> -->

    <div class="form table-responsive white-content" style="display: flex;">
        <iframe src="<?php echo getenv('VISUALISATION_SERVICE_DASH_URL'); ?>/#/softdist/ConfigurePackageForm?packageId={packageId}" style="flex: 1;" id="iFrameConfigurePackageForm" frameborder="0" scrolling="yes"></iframe>
        <input type="hidden" id="iFrameConfigurePackageFormURLTemplate" value="<?php echo getenv('VISUALISATION_SERVICE_DASH_URL'); ?>/#/softdist/ConfigurePackageForm?packageId=">       
    </div>
</div>

<!--Edit Configuration Start-->

<div id="rsc-edit-configuration" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Edit Configuration</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-edit-configuration">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="saveConfigg();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <div class="card-body">
            <div class="dist-exe-config-popup">
                <form>
                    <div class="form-group has-label">
                        <textarea class="form-control" id="configg" name="configg" style="width:810px;resize:none;" contenteditable='true'></textarea>
                        <span id="configgErr" style="color: red;margin-left: 37%;font-size: 110%;"></span>
                    </div>
                    <input type="hidden" id="ecid" name="ecid">
                    <input type="hidden" id="ecol" name="ecol">
                    <input type="hidden" id="column" name="column">
                </form>
            </div>
        </div>
    </div>
</div>

<!--Edit Configuration End-->

<!-- Distribution/Execution Popup end -->

<!-- FTP/CDN Configure start -->

<div id="rsc-ftp-cdn-configuration" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4>FTP/CDN Server Configuration</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-ftp-cdn-configuration">&times;</a>
    </div>

    <div class="btnGroup">
        <div id="ftpSubmit" class="icon-circle">
            <div class="toolTip" id="ftpconfigPack" onclick="ftpConfig();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>

        <div id="cdnSubmit" class="icon-circle" style="display:none">
            <div class="toolTip" id="cdnconfigPack" onclick="cdnConfig();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <div class="card">
            <div class="card-body">
                <div class="form-group has-label">
                    <span>Viewing :</span>
                    <div class="dropdown dropdown_2">
                        <button class="swal2-confirm btn btn-success btn-sm btn-round" id="inconf-btn-type-ftp" aria-label="" onclick="ftpcdnToggle('FTP');
                                ftpcdnConfig('<?php echo $_SESSION['user']['adminEmail']; ?>');">FTP Config</button>

                        <button class="swal2-confirm btn btn-success btn-sm btn-simple btn-inselected" id="inconf-btn-type-cdn" aria-label="" onclick="ftpcdnToggle('CDN');
                                ftpcdnConfig('<?php echo $_SESSION['user']['adminEmail']; ?>');">CDN Config</button>
                    </div>
                </div>

                <div class="ftp-config">
                    <form method="post" name="ftpConfigure" id="ftpConfigure" enctype="multipart/form-data">
                        <input type="hidden" name="ftp" value="1" />
                        <div class="form-group has-label ftpField">
                            <span class="error">*</span>
                            <label id="furlPick" for="furl">URL</label>
                            <input class="form-control required" id="furl" name="furl" type="text" value="" placeholder="http://YourDomainName/YourDashboard/swd/">
                            <span id="furlMsg" class="errorMessage"></span>
                        </div>

                        <div id="ftpauth" class="form-check mt-3">
                            <label class="form-check-label">
                                <input class="form-check-input" type="checkbox" name="fauth" id="fauth">
                                <span class="form-check-sign"></span> Authentication
                            </label>
                        </div>

                        <div class="row">&nbsp;</div>

                        <div class="form-group has-label ftpauthField" style="display: none;">
                            <span class="error">*</span>
                            <label id="fuserPick" for="fuser">User Name</label>
                            <input class="form-control required" id="fuser" name="fuser" type="text">
                            <span id="fuserMsg" class="errorMessage"></span>
                        </div>

                        <div class="form-group has-label ftpauthField" style="display: none;">
                            <span class="error">*</span>
                            <label id="fpwdPick" for="fpwd">Password</label>
                            <input class="form-control required" id="fpwd" name="fpwd" type="password">
                            <span id="fpwdMsg" class="errorMessage"></span>
                        </div>
                    </form>
                </div>

                <div class="cdn-config" style="display: none;">
                    <form method="post" name="cdnConfigure" id="cdnConfigure" enctype="multipart/form-data">
                        <input type="hidden" name="cdn" value="1" />
                        <div class="form-group has-label">
                            <span class="error">*</span>
                            <label id="cdnurlPick" for="cdnurl">URL</label>
                            <input class="form-control required" id="cdnurl" name="cdnurl" type="text">
                            <span id="cdnurlMsg" class="errorMessage"></span>
                        </div>

                        <div class="form-group has-label">
                            <span class="error">*</span>
                            <label id="cdnAkPick" for="cdnAk">Access Key</label>
                            <input class="form-control required" id="cdnAk" name="cdnAk" type="text">
                            <span id="cdnAkMsg" class="errorMessage"></span>
                        </div>

                        <div class="form-group has-label">
                            <span class="error">*</span>
                            <label id="cdnSkPick" for="cdnSk">Secret Key</label>
                            <input class="form-control required" id="cdnSk" name="cdnSk" type="text">
                            <span id="cdnSkMsg" class="errorMessage"></span>
                        </div>

                        <div class="form-group has-label">
                            <span class="error">*</span>
                            <label id="bucketPick" for="bucket">Bucket Name</label>
                            <input class="form-control required" id="bucket" name="bucket" type="text">
                            <span id="bucketMsg" class="errorMessage"></span>
                        </div>

                        <div class="form-group has-label">
                            <span class="error">*</span>
                            <label id="regionPick" for="region">CDN Region</label>
                            <input class="form-control required" id="region" name='region' type="text">
                            <span id="regionMsg" class="errorMessage"></span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="button col-md-12 text-center">
        <span id="ftpError" class="inslider-feed error"></span>
        <span id="cdnError" class="inslider-feed error"></span>
    </div>
</div>

<!--View Software Details-->

<div id="rsc-view-container" class="rightSidenav myform" data-class="md-6">
    <div class="rsc-loader hide"></div>
    <div class="card-title border-bottom">
        <h4>Software Distribution Details</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-view-container">&times;</a>
    </div>


    <div class="form table-responsive white-content">
        <form action="movetocdn1.php" method="post" name="addPatchValidate1" id="addPatchValidate1" enctype="multipart/form-data">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label id="regionPick" for="region">Platform</label>
                        <input class="form-control" id="platformDetail" name='platformDetail' readonly="" type="text">
                    </div>
                    <div class="form-group has-label">
                        <label id="regionPick" for="region">Type</label>
                        <input class="form-control" id="typeDetail" name='typeDetail' readonly="" type="text">
                    </div>
                    <div class="form-group has-label">
                        <label id="regionPick" for="region">Package Name</label>
                        <input class="form-control" id="packNameDetail" name='packNameDetail' readonly="" type="text">
                    </div>
                    <div class="form-group has-label">
                        <label id="regionPick" for="region">Version</label>
                        <input class="form-control" id="versionDetail" name='versionDetail' readonly="" type="text">
                    </div>
                    <div class="form-group has-label">
                        <label for="pType">Is the Application/Package common for a 32 and 64 bit OS?</label>
                    </div>

                    <div class="row my-form-inline-radio global-value-group">
                        <div class="col-sm-12">
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" id="same32_64config_view" name="optradio" value="yes">
                                    <span class="form-check-sign"></span> Yes
                                </label>
                            </div>

                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" id="diff32_64config_view" name="optradio" value="no">
                                    <span class="form-check-sign"></span> No
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group has-label">
                        <label id="regionPick" for="region">Path</label>
                        <input class="form-control" id="pathDetail" name='pathDetail' readonly="" type="text">
                    </div>
                    <div class="form-group has-label">
                        <label id="regionPick" for="region">Other Path</label>
                        <input class="form-control" id="path2Detail" name='path2Detail' readonly="" type="text">
                    </div>
                    <div class="form-group has-label">
                        <label id="regionPick" for="region">Icon</label>
                        <input class="form-control" id="androidIcon" name='androidIcon' readonly="" type="text">
                    </div>
                    <div class="form-group has-label" id="same_configfile" style="display:none">
                        <label id="regionPick" for="region">File/Folder Name</label>
                        <input class="form-control" id="forfDetail" name='forfDetail' readonly="" type="text">
                    </div>

                    <div class="form-group has-label" id="different_configfile" style="display:none">
                        <label id="regionPick" for="region">File/Folder Name</label>
                        <input class="form-control" id="forfDetail2" name='forfDetail' readonly="" type="text">
                    </div>
                    <div class="form-group has-label">
                        <label id="regionPick" for="region">Upload Status</label>
                        <input class="form-control" id="uploadDetail" name='uploadDetail' readonly="" type="text">
                    </div>
                    <div class="form-group has-label">
                        <label id="regionPick" for="region">Last Modified</label>
                        <input class="form-control" id="modifyDetail" name='modifyDetail' readonly="" type="text">
                    </div>
                    <div class="form-group has-label">
                        <label id="regionPick" for="region">Global</label>
                        <input class="form-control" id="globalDetail" name='globalDetail' readonly="" type="text">
                    </div>

                </div>
            </div>
        </form>
    </div>

</div>

<!--Edit Package Configuration-->
<div id="rsc-edit-container" class="rightSidenav myform" data-class="md-6">
    <div class="rsc-loader hide"></div>
    <div class="card-title border-bottom">
        <h4>Edit Existing Software for Distribution</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-edit-container">&times;</a>
    </div>

    <div class="btnGroup myform-enable-edit" style="display: block;">
        <div class="icon-circle">
            <div class="toolTip editOption">
                <i class="tim-icons icon-pencil"></i>
                <span class="tooltiptext">Edit</span>
            </div>
        </div>
    </div>

    <div class="btnGroup myform-edit-group" id="toggleButton" style="display: none;">
        <div class="icon-circle iconTick circleGrey">
            <div class="toolTip" id="addPackage1" name="addPackage1" onclick="editPackageFunction();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>

        <div class="icon-circle myform-toogle-edit">
            <div class="toolTip">
                <i class="tim-icons icon-simple-remove"></i>
                <span class="tooltiptext">Toggle edit mode</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form action="movetocdn1.php" method="post" name="addPatchValidate1" id="addPatchValidate1" enctype="multipart/form-data">
            <div class="card">
                <div class="card-body">
                    <input type="hidden" id="sFtpUrl1" name="sFtpUrl1" />
                    <input type="hidden" id="sCdnUrl1" name="sCdnUrl1" />
                    <input type="hidden" id="AWSREGION1" name="AWSREGION1" />
                    <input type="hidden" id="AWSBUCKET1" name="AWSBUCKET1" />
                    <input type="hidden" id="AWSSECRET1" name="AWSSECRET1" />
                    <input type="hidden" id="AWSACCESS1" name="AWSACCESS1" />
                    <input type="hidden" id="policy1" name="policy1" />
                    <input type="hidden" id="signature1" name="signature1" />
                    <input type="hidden" id="uts1" name="uts1" value="">

                    <div id="platformwrap1" class="form-group has-label">
                        <label for="platform1">Platform</label>
                        <select id="platform1" name="platform1" class="form-control selectpicker dropdown-submenu" title="Platform &ast;" data-size="3" data-width="100%">
                            <option value="windows">Windows</option>
                            <option value="android">Android</option>
                            <option value="linux">Linux</option>
                            <option value="mac">Mac</option>
                            <option value="ios">iOS</option>
                        </select>
                    </div>

                    <div id="posKeywords1" class="form-group has-label Comm_PosKey_PackExp288 ed-topreset" style="display: none;">
                        <label for="posKey1">Dart</label>
                        <select id="posKey1" name="posKey1" class="form-control selectpicker dropdown-submenu" title="Dart &ast;" data-size="3" data-width="100%" onchange="andPosKeyFields1();">
                            <option value="0" selected>NA</option>
                            <option value="288">288</option>
                            <option value="415">415</option>
                        </select>
                    </div>

                    <div id="distTypeDiv1" class="form-group has-label CommAndPosKey288 ed-topreset" style="display: none;">
                        <label for="distType1">Distribution Type</label>
                        <select id="distType1" name="distType1" class="form-control selectpicker dropdown-submenu" title="Distribution Type &ast;" data-size="4" data-width="100%" onchange="distTypeFn1();">
                            <option value="1">File Distribution</option>
                            <option value="2">App Execution</option>
                            <option value="3">File Move</option>
                        </select>
                    </div>

                    <div id="fileTypeDiv1" class="form-group has-label">
                        <label for="types1">Type</label>
                        <select id="types1" name="types1" class="form-control selectpicker dropdown-submenu" title="Type &ast;" data-size="3" data-width="100%">
                            <option value="file">File</option>
                            <option value="folder">Folder</option>
                        </select>
                    </div>

                    <div id="sourceConfig1" class="form-group has-label source-type sourcetypebox">
                        <h5 class="h5label">Source Type:</h5>
                        <div class="form-check form-check-radio ed-topreset" id="shfold" style="display: none;">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="stype1" id="sfolder1" value="1" checked>
                                <span class="form-check-sign"></span> Shared Folder
                            </label>
                        </div>

                        <div class="form-check form-check-radio ed-topreset" id="nhrepo" style="display: none;">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="stype1" id="nhrep1" value="2" checked>
                                <span class="form-check-sign"></span> Nanoheal Repository
                            </label>
                        </div>

                        <div class="form-check form-check-radio ed-topreset" id="otrepo" style="display: none;">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="stype1" id="otrep0" value="3" checked>
                                <span class="form-check-sign"></span> Vendor Repository
                            </label>
                        </div>

                        <div class="form-check form-check-radio ed-topreset" id="gpstor" style="display: none;">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="stype1" id="gplay1" value="4" checked>
                                <span class="form-check-sign"></span> Google Play Store
                            </label>
                        </div>

                        <div class="form-check form-check-radio ed-topreset" id="npstor" style="display: none;">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="stype1" id="nplay1" value="5" checked>
                                <span class="form-check-sign"></span> Nanoheal Play Store
                            </label>
                        </div>

                        <div class="form-check form-check-radio ed-topreset" id="apstor" style="display: none;">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="stype1" id="iplay1" value="5" checked>
                                <span class="form-check-sign"></span> Apple Store
                            </label>
                        </div>
                    </div>

                    <div id="packExpiryDiv1" class="form-group has-label Comm_PosKey_PackExp288 ed-topreset" style="display: none;">
                        <label for="packExpiry1">Package Expiry</label>
                        <select name="packExpiry1" id="packExpiry1" class="form-control selectpicker dropdown-submenu" title="Package Expiry &ast;" data-size="3" data-width="100%">
                            <option value="432000">5 days</option>
                            <option value="864000" selected>10 days</option>
                            <option value="1296000">15 days</option>
                        </select>
                    </div>
                    <div class="form-group has-label">
                        <label for="pType">Is the Application/Package common for a 32 and 64 bit OS?</label>
                    </div>

                    <div class="row my-form-inline-radio global-value-group">
                        <div class="col-sm-12">
                            <div id="edit_configyes" class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" id="editsame32_64config" name="editsame32_64config" value="yes">
                                    <span class="form-check-sign"></span> Yes
                                </label>
                            </div>

                            <div id="edit_configno" class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input" type="radio" id="editdiff32_64config" name="editsame32_64config" value="no">
                                    <span class="form-check-sign"></span> No
                                </label>
                            </div>
                        </div>
                    </div>
                    <label id="removeDiv_label1" for="domain" style="display:none"></label>
                    <div id="removeDiv" class="remove-existing form-group has-label" style="display:none">
                        <label for="fileonly1" style="font-size: 11px;">Remove existing files and add new files</label>
                        <span id="fileremove1" name="fileremove1" class="material-icons icon-ic_delete_24px" style="margin-left: 20px;position: relative;top: -3px;"><i class="tim-icons tim-icons-lg icon-trash-simple" style="color: #e74c3c;"></i></span>
                        <div id="existingfilename" style="font-size: 10px;font-weight: 400; color: #696969; margin-top: 8px;"></div>
                    </div>
                    <label id="removeDiv_label2" for="domain" style="display:none">64 Bit Configuration File</label>
                    <div id="removeDiv2" class="remove-existing form-group has-label" style="display:none">
                        <label for="fileonly1_2" style="font-size: 11px;">Remove existing files and add new files</label>
                        <span id="fileremove1_2" name="fileremove1_2" class="material-icons icon-ic_delete_24px" style="margin-left: 20px;position: relative;top: -3px;"><i class="tim-icons tim-icons-lg icon-trash-simple" style="color: #e74c3c;"></i></span>
                        <div id="existingfilename2" style="font-size: 10px;font-weight: 400; color: #696969; margin-top: 8px;"></div>
                    </div>

                    <input type="hidden" id="ftp1val">
                    <input type="hidden" id="cdn1val">

                    <div class="select-source configureSource1 ed-topreset" style="display:none">
                        <h5 class="h5label">Select Source(to upload):</h5>
                        <div class="form-group has-label row sstu-wrap">
                            <div class="col-sm-4">
                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input ftprcdn1" type="radio" id="ftpupload1" name="uploads" value="1">
                                        <span class="form-check-sign"></span> FTP server
                                    </label>
                                </div>
                            </div>

                            <div class="col-sm-8">
                                <span id="ftpspan1"></span>
                            </div>
                        </div>

                        <div class="form-group has-label row sstu-wrap">
                            <div class="col-sm-4">
                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input ftprcdn1" type="radio" id="cdnupload1" name="uploads" value="2">
                                        <span class="form-check-sign"></span> CDN server
                                    </label>
                                </div>
                            </div>

                            <div class="col-sm-8">
                                <span id="cdnspan1"></span>
                            </div>
                        </div>

                    </div>


                    <div class="form-group has-label nhrep1 ed-topreset" style="display: none;">
                        <div class="showfile1">
                            <div class="form-group has-label">
                                <label for="fileonly1" class="col-sm-4 align-label">Uploaded File/Folder</label>
                                <div class="col-sm-8">
                                    <input class="form-control" id="fileonly1" name="fileonly1" readonly="" type="text">
                                </div>
                            </div>

                            <div class="form-group has-label" id="iconspace" style="display: none;">
                                <label for="icononly1" class="col-sm-4 align-label">Uploaded Icon</label>
                                <div class="col-sm-8">
                                    <input class="form-control" id="icononly1" name="icononly1" readonly="" type="text">
                                </div>
                            </div>
                        </div>

                        <div class="form-group has-label upload-file chooseFile1 ed-topreset" style="display: none;">
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input ftprcdn1 circle" type="radio" name="selectCretiria" id="uploadFile1" value="">
                                    <span class="form-check-sign"></span> Upload File
                                </label>
                            </div>

                            <div id="fine-uploader-manual-trigger1" class="ed-topreset" style="display: none;"></div>

                            <div id="fine-uploader-s31" class="ed-topreset" style="display: none;"></div>

                            <div class="ed-topreset" style="display:none;">
                                <input type="radio" class="form__radio" value="sUpload" name="selectCretiria1" id="uploadFile1" checked />
                                <label class="selectfile1"></label>
                                <br />
                            </div>

                            <div class="form-check form-check-radio showrepository1">
                                <label class="form-check-label showrepository1">
                                    <input class="form-check-input ftprcdn1 circle" type="radio" name="selectCretiria" id="sdnSelect1" value="sServer">
                                    <span class="form-check-sign"></span> Select From Repository
                                </label>
                            </div>
                        </div>

                        <div id="files1" class="form-group has-label CdnFilesList1 files1 select-source">
                            <div id="filevalidationtext_cdn1" class="error" style="font-size:14px;font-weight:400;"></div>
                        </div>

                        <div id="filevalidationtext1" class="error" style="color: #ff0000;margin-left: 2%;"></div>
                    </div>


                    <div id="packNameDiv1" class="form-group has-label">
                        <label id="packName1Pick" for="packName1" class="align-label">Package/App Name</label>

                        <div>
                            <input class="form-control required packNameclear" id="packName1" name="packName1" type="text" title="">
                            <span id="packName1Msg" class="errorMessage"></span>
                        </div>
                    </div>

                    <div id="appIdDiv1" class="form-group has-label">
                        <label for="appId1" class="align-label">Application ID</label>
                        <div>
                            <input class="form-control" id="appId1" name="appId1" type="text">
                        </div>
                    </div>

                    <div id="editpathDiv" class="form-group has-label ed-topreset" style="display: none;">
                        <label id="editpathPick" for="path1" class="align-label">Path</label>
                        <div>
                            <input class="form-control required" id="path1" name="path1" type="text">
                            <span id="editpathMsg" class="errorMessage"></span>
                        </div>
                    </div>

                    <div id="iconDiv" class="form-group has-label ed-topreset" style="display: none;">
                        <label for="iconName1" class="align-label">Icon URL</label>
                        <div>
                            <input class="form-control" id="iconName1" name="iconName1" type="text">
                        </div>
                    </div>

                    <div id="filenameDiv1" class="form-group has-label">
                        <label for="filename1" class="align-label">File Name</label>
                        <div>
                            <input class="form-control" id="filename1" name="filename1" type="text">
                        </div>
                    </div>

                    <div id="packDescDiv1" class="form-group has-label">
                        <label id="packDesc1Pick" for="packDesc1" class="align-label">Package Description</label>
                        <div>
                            <input class="form-control required" id="packDesc1" name="packDesc1" type="text">
                            <span id="packDesc1Msg" class="errorMessage"></span>
                        </div>
                    </div>

                    <div class="form-group has-label version1">
                        <label id="version1Pick" for="version1" class="align-label">Software Version</label>
                        <div>
                            <input class="form-control required" id="version1" name="version1" type="text">
                            <span id="version1Msg" class="errorMessage"></span>
                        </div>
                    </div>

                    <!--Dart 288 Fields START-->

                    <div id="policyEnforceDiv1" class="form-group has-label CommAndPosKey288 ed-topreset" style="display: none;">
                        <label for="policyEnforce1">Policy Enforcement</label>
                        <select name="policyEnforce1" id="policyEnforce1" class="form-control selectpicker dropdown-submenu" title="Policy Enforcement &ast;" data-size="3" data-width="100%">
                            <option value="0" selected>Silent installation</option>
                            <option value="1">Continuous pop-up for Non SAMSUNG devices</option>
                            <option value="2">Continuous pop-up for SAMSUNG & Non SAMSUNG devices</option>
                        </select>
                    </div>

                    <div id="downloadPathDiv1" class="form-group has-label CommAndPosKey288 ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label for="downloadPath1">Destination Path</label>
                        <input class="form-control required val_spcl_chars1" id="downloadPath1" name="downloadPath1" type="text" placeholder="Example: /sdcard/Nanoheal/Download/" />
                    </div>

                    <div id="andPreCheckDiv1" class="form-group has-label CommAndPosKey288 ed-topreset" style="display: none;">
                        <label for="andPreCheck1">Pre-Check</label>
                        <select name="andPreCheck1" id="andPreCheck1" class="form-control selectpicker dropdown-submenu" title="Pre-Check &ast;" onchange="andPreCheckFn1();" data-size="3" data-width="100%">
                            <option value="0">0</option>
                            <option value="1" selected>1</option>
                            <!--<option value="2">2</option>-->
                        </select>
                    </div>

                    <div id="preCheckPathDiv1" class="form-group has-label CommAndPosKey288 ed-topreset" style="display:none;">
                        <label id="preCheckPath1Pick" for="version1" class="align-label">Pre-Check Path</label>
                        <div>
                            <input class="form-control required val_spcl_chars" id="preCheckPath1" name="preCheckPath1" type="text">
                            <span id="preCheckPath1Msg" class="errorMessage"></span>
                        </div>
                    </div>

                    <div id="packageAndVersionDiv1" class="form-group has-label CommAndPosKey288 ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label for="andPackName1">Package Name</label>
                        <input class="form-control required val_spcl_chars1" id="andPackName1" name="andPackName1" type="text" placeholder="Please enter package name" />

                        <span class="error">*</span>
                        <label for="andVersionCode1">Version Code</label>
                        <input class="form-control required val_spcl_chars1" id="andVersionCode1" name="andVersionCode1" type="text" placeholder="Please enter package version" />
                    </div>

                    <div id="andPostCheckDiv1" class="form-group has-label CommAndPosKey288 ed-topreset" style="display: none;">
                        <label for="andPostCheck1">Post-Check</label>
                        <select name="andPostCheck1" id="andPostCheck1" class="form-control selectpicker dropdown-submenu" title="--" onchange="andPostCheckFn1();" data-size="2" data-width="100%">
                            <option value="">--</option>
                            <option value="1">1</option>
                        </select>
                    </div>

                    <div id="packAndVersDiv1" class="form-group has-label CommAndPosKey288 ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label for="andPPackName1">Package Name</label>
                        <input class="form-control required val_spcl_chars1" id="andPPackName1" name="andPPackName1" type="text" placeholder="Please enter package name" />

                        <span class="error">*</span>
                        <label for="andPVersionCode1">Version Code</label>
                        <input class="form-control required val_spcl_chars1" id="andPVersionCode1" name="andPVersionCode1" type="text" placeholder="Please enter package version" />
                    </div>

                    <div id="sourceDestinationDiv1" class="form-group has-label CommAndPosKey288 ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label for="sourcePath">Source Path of file</label>
                        <input class="form-control required val_spcl_chars1" id="sourcePath1" name="sourcePath1" type="text" placeholder="Please enter source path of file" />

                        <span class="error">*</span>
                        <label for="destinationPath1">Destination Path of file</label>
                        <input class="form-control required val_spcl_chars1" id="destinationPath1" name="destinationPath1" type="text" placeholder="Please enter destination path of file" />
                    </div>

                    <div id="maxtimeperpatch1" class="form-group has-label CommAndPosKey288 ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label for="maxTime1">Max Time Per Patch</label>
                        <input class="form-control required val_spcl_chars1" id="maxTime1" name="maxTime1" type="number" onkeypress="return event.charCode >= 48" min="0" value="0" />
                    </div>

                    <!--Dart 288 Fields END-->

                    <!--Dart 415 Fields START-->

                    <div id="installTypeDiv1" class="form-group has-label CommAndPosKey415 ed-topreset" style="display: none;">
                        <label for="installType1">Installation Type</label>
                        <select name="installType1" id="installType1" class="form-control selectpicker dropdown-submenu" title="Installation Type &ast;" onchange="installTypeChange1();" data-size="3" data-width="100%">
                            <!--<option value="0" selected>Silent Installation</option>-->
                            <option value="3">User pop-up with no enforcement</option>
                            <option value="5">User pop-up with enforcement</option>
                        </select>
                    </div>

                    <div id="titleDiv1" class="form-group has-label CommAndPosKey415 ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label for="title">Title</label>
                        <input class="form-control required val_spcl_chars1" id="title1" name="title1" type="text" placeholder="Please enter Title of the Package/Application" maxlength="20" />
                    </div>

                    <div id="preDownloadMsgDiv1" class="form-group has-label CommAndPosKey415 ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label for="preDownloadMsg1">Pre-download message</label>
                        <input class="form-control required val_spcl_chars1" id="preDownloadMsg1" name="preDownloadMsg1" type="text" placeholder="Example: Do you want to continue?" maxlength="128" />

                        <span class="error">*</span>
                        <label for="preDownloadPosMsg1">Pre-download positive button</label>
                        <input class="form-control required val_spcl_chars1" id="preDownloadPosMsg1" name="preDownloadPosMsg1" type="text" placeholder="Example: Yes" maxlength="20" />

                        <span class="error">*</span>
                        <label for="preDownloadNegMsg1">Pre-download negative button</label>
                        <input class="form-control required val_spcl_chars1" id="preDownloadNegMsg1" name="preDownloadNegMsg1" type="text" placeholder="Example: No" maxlength="20" />
                    </div>

                    <div id="postDownloadMsgDiv1" class="form-group has-label CommAndPosKey415 ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label for="postDownloadMsg1">Post-check</label>
                        <input class="form-control required val_spcl_chars1" id="postDownloadMsg1" name="postDownloadMsg1" type="text" placeholder="Example: Do you want to install?" maxlength="128" />

                        <span class="error">*</span>
                        <label for="postDownloadPosMsg1">Post-download positive button</label>
                        <input class="form-control required val_spcl_chars1" id="postDownloadPosMsg1" name="postDownloadPosMsg1" type="text" placeholder="Example: Yes" maxlength="20" />

                        <span class="error">*</span>
                        <label for="postDownloadNegMsg1">Post-download negative button</label>
                        <input class="form-control required val_spcl_chars1" id="postDownloadNegMsg1" name="postDownloadNegMsg1" type="text" placeholder="Example: No" maxlength="20" />
                    </div>

                    <div id="installMsgDiv1" class="form-group has-label CommAndPosKey415 ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label for="installMsg1">Installation Message</label>
                        <input class="form-control required val_spcl_chars1" id="installMsg1" name="installMsg1" type="text" placeholder="Example: Done" maxlength="128" />

                        <label for="installAction1">Install Action</label>
                        <div class="selectpicker-wrap">
                            <select name="installAction1" id="installAction1" class="form-control selectpicker dropdown-submenu" title="Install Action &ast;" onchange="installActionFn1();" data-size="2" data-width="100%">
                                <option value="1" selected>1</option>
                                <option value="2">2</option>
                            </select>
                        </div>

                        <span class="error">*</span>
                        <label for="installFinishMsg1" class="installFinishMsg1 installFinishMsgSpan1">Install Finish Message</label>
                        <div class="installFinishMsgSpan1">
                            <input class="form-control required val_spcl_chars1 installFinishMsg1" id="installFinishMsg1" name="installFinishMsg1" type="text" placeholder="Example: Ok" maxlength="20" />
                        </div>

                        <span class="error">*</span>
                        <label for="installPopupMsg1" class="installPopupMsg1 installPopupSpan1">Install Pop-up time (seconds)</label>
                        <div class="installPopupSpan1">
                            <input class="form-control required val_spcl_chars1 installPopupMsg1 installPopupSpan1" id="installPopupMsg1" name="installPopupMsg1" type="number" onkeypress="return event.charCode >= 48" min="0" />
                        </div>
                    </div>

                    <div id="freqIntActMsgDiv1" class="form-group has-label CommAndPosKey415 ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label for="installPopupMsg1">Frequency</label>
                        <input class="form-control required val_spcl_chars1" id="frequencySet1" name="frequencySet1" type="number" onkeypress="return event.charCode >= 48" min="1" value="1" />

                        <span class="error">*</span>
                        <label for="installPopupMsg1">Interval</label>
                        <input class="form-control required val_spcl_chars1" id="intervalSet1" name="intervalSet1" type="number" onkeypress="return event.charCode >= 48" min="1" value="1" />

                        <label class="col-sm-4" for="policyEnforceAction1">Enforce Action</label>
                        <div class="col-sm-8 selectpicker-wrap">
                            <select name="policyEnforceAction1" id="policyEnforceAction1" class="form-control selectpicker dropdown-submenu" onchange="policyEnforceActionFnc1();" title="Enforce Action &ast;" data-size="2" data-width="100%">
                                <option value="1" selected>Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>

                        <span class="error">*</span>
                        <label for="installFinishMsg1" class="policyEnforceActionClass1">Message</label>
                        <div class="policyEnforceActionClass1">
                            <input class="form-control required val_spcl_chars1" id="enfMessage1" name="enfMessage1" type="text" placeholder="Example: Application is installed" maxlength="128" />
                        </div>
                    </div>

                    <!--Dart 415 Fields END-->

                    <div id="EditSiteDiv" class="form-group has-label ed-topreset" style="display: none;">
                        <label for="siteArray1" class="col-sm-4 align-label">Site</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="siteArray1" name="siteArray1" style="cursor: no-drop;" readonly="" type="text">
                        </div>
                    </div>

                    <div id="eactionDate" class="form-group has-label ed-topreset" style="display: none;">
                        <label for="actionDate1" class="col-sm-4 align-label">Action Date</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="actionDate1" name="actionDate1" type="text">
                        </div>
                    </div>

                    <div id="enotify" class="form-group has-label ed-topreset" style="display: none;">
                        <label for="notify1" class="col-sm-4 align-label">Notification</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="notify1" name="notify1" type="text">
                        </div>
                    </div>

                    <div id="euniAction" class="form-group has-label ed-topreset" style="display: none;">
                        <label for="uniAction1" class="col-sm-4 align-label">Action</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="uniAction1" name="uniAction1" type="text">
                        </div>
                    </div>

                    <div id="manifestDiv1" class="form-group has-label ed-topreset" style="display: none;">
                        <select name="manifesttypes1" id="manifesttypes1" title="Manifest Type" class="form-control selectpicker dropdown-submenu" data-size="5" data-width="100%">
                            <option value="manifest1"> Manifest 1 </option>
                            <option value="manifest2"> Manifest 2 </option>
                            <option value="manifest3"> Manifest 3 </option>
                            <option value="manifest4"> Manifest 4 </option>
                            <option value="manifest5"> Manifest 5 </option>
                        </select>
                    </div>

                    <div id="manifestNameDiv1" class="form-group has-label ed-topreset" style="display: none">
                        <label for="manifestname1" class="col-sm-4 align-label">Manifest Name (FTP Configuration is mandatory &ast;)</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="manifestname1" name="manifestname1" type="text">
                        </div>
                    </div>

                    <div class="form-group has-label source-type showaccess1 ed-topreset" style="display: none;">
                        <h5>Access:</h5>

                        <div class="form-check form-check-radio">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="access1" id="anony1" value="Anony" onclick="accessFunction1('anony1');">
                                <span class="form-check-sign"></span> Anonymous
                            </label>
                        </div>

                        <div class="form-check form-check-radio">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" value="Secure" name="access1" id="secure1" onclick="accessFunction1('secure1');">
                                <span class="form-check-sign"></span> Secure
                            </label>
                        </div>
                    </div>

                    <div class="form-group has-label showSecure1 ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label id="username1Pick" for="username1">User Name</label>
                        <input class="form-control required" id="username1" name="username1" type="text">
                        <span id="username1Msg" class="errorMessage"></span>
                    </div>

                    <div class="form-group has-label showSecure1 ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label id="password1Pick" for="password1">Password</label>
                        <input class="form-control required" id="password1" name="password1" type="password">
                        <span id="password1Msg" class="errorMessage"></span>
                    </div>

                    <div class="form-group has-label showSecure1 ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label id="domain1Pick" for="domain1">Domain</label>
                        <input class="form-control required" id="domain1" name="domain1" type="text">
                        <span id="domain1Msg" class="errorMessage"></span>
                    </div>

                    <!--<div class="form-check mt-3 distClass1 winOnly">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" id="distCheck1" name="distribute1">
                            <span class="form-check-sign"></span> Distribute
                        </label>
                    </div>

                    <div class="form-group has-label distributionPath1 winOnly ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label id="dPath1Pick" for="dPath1">Distribution Path</label>
                        <input class="form-control required" id="dPath1" name="dPath1" type="text">
                        <span id="dPath1Msg" class="errorMessage"></span>
                    </div>

                    <div class="form-group has-label distributionTime1 winOnly ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label id="dTime1Pick" for="dTime1">Distribution Time</label>
                        <input class="form-control required" id="dTime1" name="dTime1" type="text">
                        <span id="dTime1Msg" class="errorMessage"></span>
                    </div>

                    <div class="form-group has-label distributionValidPath1 winOnly ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label id="dvPath1Pick" for="dvPath1">Distribution Validation Path</label>
                        <input class="form-control required" id="dvPath1" name="dvPath1" type="text">
                        <span id="dvPath1Msg" class="errorMessage"></span>
                    </div>

                    <div class="predischeck-wrap form-check mt-3 preDisCheckClass1 winOnly">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" id="preDisCheck1" name="preDisCheck1" onclick="check();">
                            <span class="form-check-sign"></span> Pre-Distribution Check
                        </label>
                    </div>

                    <div class="form-group has-label source-type select-source distributionpreCheck1 winOnly ed-topreset" style="display: none;">
                        <div class="form-check form-check-radio">
                            <label class="form-check-label">
                                <input class="form-check-input preinstcheck1" type="radio" id="pfile1" name="preinstcheck1" value="0">
                                <span class="form-check-sign"></span> File
                            </label>
                        </div>

                        <div class="form-check form-check-radio">
                            <label class="form-check-label">
                                <input class="form-check-input preinstcheck1" type="radio" id="pSoftware1" name="preinstcheck1" value="1">
                                <span class="form-check-sign"></span> Software Name
                            </label>
                        </div>

                        <div id="pRegistryDiv1" class="form-check form-check-radio">
                            <label class="form-check-label">
                                <input class="form-check-input preinstcheck1" type="radio" id="pRegistry1" name="preinstcheck1" value="2">
                                <span class="form-check-sign"></span> Registry
                            </label>
                        </div>

                        <div>
                            <span id="pre1Msg" style="color:red"></span>
                        </div>
                    </div>

                    <div id="distributionPreCheckDiv1" class="form-group has-label source-type select-source distributionpreCheck1 ed-topreset" style="display: none;">
                        <div class="form-check form-check-radio">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" value="0" name="pExecPreCheckVal2" id="pExecPreCheckVal2">
                                <span class="form-check-sign"></span> Execute when pre-check value exists
                            </label>
                        </div>

                        <div class="form-check form-check-radio">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" value="1" name="pExecPreCheckVal2" id="pExecPreCheckVal3">
                                <span class="form-check-sign"></span> Execute when pre-check value doesn't exists
                            </label>
                        </div>
                    </div>

                    <div class="form-group has-label pfile1 preinstcheckFields1 winOnly ed-topreset" style="display:none;">
                        <span class="error">*</span>
                        <label id="pfilePath1Pick" for="pfilePath1">File Path</label>
                        <input class="form-control required" id="pfilePath1" name="pfilePath1" type="text">
                        <span id="pfilePath1Msg" class="errorMessage"></span>
                    </div>

                    <div class="form-group has-label pSoftware1 preinstcheckFields1 winOnly ed-topreset" style="display:none;">
                        <span class="error">*</span>
                        <label id="pSoftName1Pick" for="pSoftName1">Software Name</label>
                        <input class="form-control required" id="pSoftName1" name="pSoftName1" type="text">
                        <span id="pSoftName1Msg" class="errorMessage"></span>
                    </div>

                    <div class="form-group has-label pSoftware1 preinstcheckFields1 winOnly ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label id="pSoftVer1Pick" for="pSoftVer1">Software Version</label>
                        <input class="form-control required" id="pSoftVer1" name="pSoftVer1" type="text">
                        <span id="pSoftVer1Msg" class="errorMessage"></span>
                    </div>

                    <div id="pSoftwareKBDiv1" class="form-group has-label pSoftware1 preinstcheckFields1 winOnly ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label id="pKb1Pick" for="pKb1">Knowledge base(Values must be comma(,) separated)</label>
                        <input class="form-control required" id="pKb1" name="pKb1" type="text">
                        <span id="pKb1Msg" class="errorMessage"></span>
                    </div>

                    <div id="pSoftwareSPDiv1" class="form-group has-label pSoftware1 preinstcheckFields1 winOnly ed-topreset" style="display: none;">
                        <span class="error">*</span>
                        <label id="pServicePack1Pick" for="pServicePack1">Service Pack</label>
                        <input class="form-control required" id="pServicePack1" name="pServicePack1" type="text">
                        <span id="pServicePack1Msg" class="errorMessage"></span>
                    </div>

                    <div id="edit-rootkey-wrap" class="form-group has-label pRegistry1 preinstcheckFields1 winOnly ed-topreset" style="display:none;">
                        <select name="rootKey1" id="rootKey1" title="Root Key" class="form-control selectpicker dropdown-submenu" data-size="5" data-width="100%">
                            <option id="root" value="3">HKEY_CLASSES_ROOT</option>
                            <option id="current" value="4">HKEY_CURRENT_USER</option>
                            <option id="local" value="1">HKEY_LOCAL_MACHINE</option>
                            <option id="users" value="5">HKEY_USERS</option>
                            <option id="perdata" value="7">HKEY_PERFORMANCE_DATA</option>
                            <option id="pertext" value="8">HKEY_PERFORMANCE_TEXT</option>
                            <option id="pernlstext" value="9">HKEY_PERFORMANCE_NLSTEXT</option>
                            <option id="config" value="2">HKEY_CURRENT_CONFIG</option>
                            <option id="dyndata" value="6">HKEY_DYN_DATA</option>
                        </select>
                    </div>

                    <div class="form-group has-label pRegistry1 preinstcheckFields1 winOnly ed-topreset" style="display:none;">
                        <span class="error">*</span>
                        <label id="subKey1Pick" for="subKey1">Sub Key</label>
                        <input class="form-control required" id="subKey1" name="subKey1" type="text">
                        <span id="subKey1Msg" class="errorMessage"></span>
                    </div>

                    <div class="form-group has-label pRegistry1 preinstcheckFields ed-topreset" style="display:none;">
                        <span class="error">*</span>
                        <label id="pRegName1Pick" for="pRegName1">Name</label>
                        <input class="form-control required" id="pRegName1" name="pRegName1" type="text">
                        <span id="pRegName1Msg" class="errorMessage"></span>
                    </div>

                    <div id="edit-type-wrap" class="form-group has-label pRegistry1 preinstcheckFields ed-topreset" style="display:none;">
                        <label for="pType1">Type</label>
                        <select name="pType1" id="pType1" title="Type" class="form-control selectpicker dropdown-submenu" data-size="5" data-width="100%">
                            <option value="REG_SZ">REG_SZ</option>
                            <option value="REG_DWORD">REG_DWORD</option>
                            <option value="REG_QWORD">REG_QWORD</option>
                            <option value="REG_BINARY">REG_BINARY</option>
                            <option value="REG_MULTI_SZ">REG_MULTI_SZ</option>
                            <option value="REG_EXPAND_SZ">REG_EXPAND_SZ</option>
                        </select>
                    </div>

                    <div class="form-group has-label pRegistry1 preinstcheckFields ed-topreset" style="display:none;">
                        <span class="error">*</span>
                        <label id="pValue1Pick" for="pValue1">Value</label>
                        <input class="form-control required" id="pValue1" name="pValue1" type="text">
                        <span id="pValue1Msg" class="errorMessage"></span>
                    </div>

                    <div class="form-group has-label pPatch1 preinstcheckFields1 winOnly ed-topreset" style="display:none;">
                        <label for="" class="col-sm-4 align-label">Patch Dependency</label>
                        <div class="col-sm-8">
                            <input class="form-control" id="" name="" type="text">
                        </div>
                    </div>-->

                    <div class="form-group has-label edit-global-label-wrap">
                        <label for="pType">Should this patch be available globally ?</label>
                    </div>

                    <div class="row my-form-inline-radio global-value-group">
                        <div class="col-sm-10">
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input select-global-alt-hand" id="global-patch-yes" type="radio" name="optradio" value="yes">
                                    <span class="form-check-sign"></span> Yes
                                </label>
                            </div>

                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input id="global-patch-no" class="form-check-input select-global-alt-hand" type="radio" name="optradio" value="no">
                                    <span class="form-check-sign"></span> No
                                </label>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" class="patch-global" id="global1" name="global1">

                    <input type="hidden" id="upStatus1" name="upStatus1" value="" />
                    <input type="hidden" id="upload1" name="upload1" />
                    <input type="hidden" id="androidI1" name="androidI1" value="" />
                    <input type="hidden" id="cdnRepositorySelect1" name="cdnRepositorySelect1" />
                    <input type="hidden" id="uploadedFilename1" name="filebrowse1" />
                    <input id="selectType1" type="hidden" name="selectType1" value="edit" />
                    <input id="id1" type="hidden" name="id1" value="<?php echo url::toText($edit); ?>" />
                    <input type="hidden" id="sel1" name="sel1" value="<?php echo url::toText($edit); ?>">
                    <input type="hidden" id="uStatus1" name="uStatus1" value="<?php echo url::toText($editSqlRes['status']); ?>" />
                    <input type="hidden" id="siteArray1" name="siteArray1" />
                    <input type="hidden" name="is_distribution_saved" />

                    <!--                    <div class="form-group has-label" style="margin-top:10px">
                        <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="extraButtonConfigure" name="extraButton" value="Configure">Configure</button>
                    </div>-->
                </div>
            </div>
        </form>
    </div>

    <div class="button col-md-12 text-center">
        <span id="checkavail" localized="" class="inslider-feed error"></span>
    </div>
</div>
<style>
    #files.CdnFilesList .repositoryList {
        width: 100%;
    }

    #files.CdnFilesList .repositoryList span.check {
        margin-left: 10px;
    }

    #ftpspan,
    #cdnspan {
        position: relative;
        top: 11px;
        left: -120px;
        color: red;
        font-size: 12px;
    }

    .border_radius {
        padding: 10px 10px 10px 10px;
        border-style: groove;
        border-radius: 10px;
    }

    .selborder {
        /*background-color:#e3e3e3;*/
        border-style: outset;
        border-radius: 6px;
        padding: 5px 5px 5px 5px;
        margin-left: -8px;
        width: 102%;
        border-color: rgba(29, 37, 59, 0.2);
        border-width: thin;
    }

    .active {
        /*background-color:#e3e3e3;*/
    }

    label {
        font-weight: bold;
    }

    #absoLoader {
        width: 100%;
        height: 97%;
        position: absolute;
        z-index: 10;
        background-color: #ffffff;
        opacity: 0.8;
        padding-top: 20%;
        padding-left: 45%;
    }

    .showbtn {
        margin-left: 46px;
    }

    .clearbtn {
        margin-left: 46px;
    }
</style>
<!-- FTP/CDN Configure close -->
