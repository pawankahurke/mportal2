<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?>
<div class="content white-content">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <div id="loader" class="loader"  data-qa="loader" >
                        <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                    </div>
                    <div class="toolbar">
                    <div class="toolbar">
                        <!--        Here you can write extra buttons/actions for the toolbar              -->
                        <!-- <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>
                            </div>
                        </div> -->

                        <!-- <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

                                    <a class="dropdown-item rightslide-container-hand dropHandy main <?php echo setRoleForAnchorTag('softwaredetails', 2); ?>" onclick="selectConfirm('version_detail')">Details</a>
                                    <a class="dropdown-item rightslide-container-hand dropHandy main <?php echo setRoleForAnchorTag('softwareversion', 2); ?>" onclick="getVersionTable();">Versions</a>
                                    <a class="dropdown-item rightslide-container-hand dropHandy main <?php echo setRoleForAnchorTag('updatesoftwareversion', 2); ?>" onclick="getOsVersionList()" id="updateversion">Update Version</a>
                                    <a class="dropdown-item rightslide-container-hand dropHandy main <?php echo setRoleForAnchorTag('softwaredetailsexport', 2); ?>" onclick="versionlistexport()" id="detailexport">Details Export</a>
                                    <a class="dropdown-item dropHandy main" id="open-upload-core-db-wrap <?php echo setRoleForAnchorTag('coredbupload', 2); ?>" onclick="showCoreDbnUploadCntnr();">Core DB Upload</a>

                                    <a class="dropdown-item rightslide-container-hand dropHandy version <?php echo setRoleForAnchorTag('addversion', 2); ?>" data-bs-target="add-version" onclick="enableAddFields()">Add Version</a>
                                    <a class="dropdown-item rightslide-container-hand dropHandy version <?php echo setRoleForAnchorTag('copyversion', 2); ?>" data-bs-target="copy-version" onclick="get_copyversiondata()">Copy Version</a>
                                    <a class="dropdown-item rightslide-container-hand dropHandy version <?php echo setRoleForAnchorTag('deleteversion', 2); ?>" onclick="deleteVersion()">Delete Version</a>
                                    <a class="dropdown-item rightslide-container-hand dropHandy version <?php echo setRoleForAnchorTag('backupdate', 2); ?>" onclick="gotoMain();">Back</a>
                                </div>
                            </div>
                        </div> -->
                    </div>

                    <div class="col-md-12" id="errorMsg" style="display:none;">
                        <span>Please select site</span>
                    </div>
                    <input type="hidden" id="sitename" name="sitename">
                    <input type="hidden" id="Complete_sitename" name="Complete_sitename">
                    <input type="hidden" id='selected' name='selected'>
                    <input type="hidden" id="vesiondeailid" name="vesiondeailid">
                    <input type="hidden" id="versiondeleteid" name="versiondeleteid">

                    <div class="softwareupdategrid">
                        <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="softwareupdategrid">
                            <thead>
                                <tr class="headers">
                                    <th id="key0" class="headers" rowspan="2">Site Name
                                    <i class="fa fa-caret-down cursorPointer direction" id = "sitename1" onclick = "addActiveSort('asc', 'sitename');sortingIconColor('sitename1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "sitename2" onclick = "addActiveSort('desc', 'sitename');sortingIconColor('sitename2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key1" class="headers" rowspan="2">Machine Count
                                    <i class="fa fa-caret-down cursorPointer direction" id = "machinecount1" onclick = "addActiveSort('asc', 'machinecount');sortingIconColor('machinecount1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "machinecount2" onclick = "addActiveSort('desc', 'machinecount');sortingIconColor('machinecount2')" style="font-size:18px"></i>
                                    </th>
                                    <!--<th class="headers" colspan="2" style="text-align: center;">Desired Version</th>-->
                                </tr>

                                <tr>
                                    <th id="key2">OS
                                    <i class="fa fa-caret-down cursorPointer direction" id = "os1" onclick = "addActiveSort('asc', 'os');sortingIconColor('os1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "os2" onclick = "addActiveSort('desc', 'os');sortingIconColor('os2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key3">Version
                                    <i class="fa fa-caret-down cursorPointer direction" id = "version1" onclick = "addActiveSort('asc', 'version');sortingIconColor('version1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "version2" onclick = "addActiveSort('desc', 'version');sortingIconColor('version2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key4">Action
                                    <i class="fa fa-caret-down cursorPointer direction" id = "action1" onclick = "addActiveSort('asc', 'action');sortingIconColor('action1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "action2" onclick = "addActiveSort('desc', 'action');sortingIconColor('action2')" style="font-size:18px"></i>
                                    </th>
                                </tr>
                            </thead>
                            <!--                        <tfoot>
                            <tr>
                                <th class="headers" rowspan="2">Site Name</th>
                                <th class="headers" rowspan="2">Machine Name</th>
                                <th class="headers" colspan="2" style="text-align: center;">Desired Version</th>
                            </tr>
                            <tr>
                                <th>OS</th>
                                <th>Version</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>-->
                        </table>
                    </div>

                    <div class="versiondetailoslist" style="display:none;">
                        <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="versiondetailoslist">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Version</th>
                                    <th>Download URL</th>
                                    <th>Command Line</th>
                                    <th>OS</th>
                                </tr>
                            </thead>

                        </table>
                    </div>
                </div>
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>

<!--Add version starts-->

<div id="add-version" class="rightSidenav" data-class="sm-3">
    <div class="rsc-loader hide"></div>
    <div class="card-title border-bottom">
        <h4>Add Version</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="add-version">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="btn-addversiondetail" onclick="addversiondetail();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label>Version Name<em class="error" id="required_versionname">*</em></label>
                        <input class="form-control" name="versionname" id="versionname" type="text" />
                    </div>

                    <div class="form-group has-label">
                        <label>Version number<em class="error" id="required_versionnumber">*</em></label>
                        <input class="form-control" name="versionnumber" id="versionnumber" type="text" />
                    </div>

                    <div class="form-group has-label">
                        <label>Operating system<em class="error" id="required_os">*</em></label>
                        <select class="selectpicker" data-style="btn btn-info" title="Select OS" data-size="3" id="versionostype" name="versionostype">
                            <option>Windows</option>
                            <option>Mac</option>
                            <option>Android</option>
                            <option>iOS</option>
                            <option>Linux</option>
                        </select>
                    </div>
                    <div class="form-group has-label">
                        <label>Username</label>
                        <input class="form-control" name="user_name" id="user_name" type="text" autocomplete="off" />
                    </div>

                    <div class="form-group has-label">
                        <label>Password</label>
                        <input class="form-control" name="pass_word" id="pass_word" type="password" autocomplete="off" />
                    </div>

                    <div class="form-group has-label">
                        <label>Command Line</label>
                        <input class="form-control" name="commandline" id="commandline" type="text" autocomplete="off" />
                    </div>

                    <div class="row form-group has-label">
                        <div class="col-sm-5 checkbox-radios">
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input cuo-cl" type="radio" name="client-url-opt" value="1" checked="checked">
                                    <span class="form-check-sign"></span>
                                    Download Url
                                </label>
                            </div>
                        </div>

                        <div class="col-sm-5 checkbox-radios">
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input cuo-cl" type="radio" name="client-url-opt" value="2">
                                    <span class="form-check-sign"></span>
                                    Upload Client
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group has-label av-durl-w" style="margin-top: 18px !important;">
                        <label>Download URL<em class="error" id="required_downloadurl">*</em></label>
                        <input class="form-control" name="url" id="url" type="text" />
                    </div>

                    <div class="row av-upc-w" style="margin-top: 28px;display:none">
                        <div class="col-md-16 col-sm-16" style="margin: 0 auto;">
                            <h4 class="card-title" style="background:transparent">&nbsp;</h4>
                            <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                <div class="fileinput-new thumbnail">
                                    <img src="../assets/img/image_placeholder.jpg" alt="...">
                                </div>
                                <div class="fileinput-preview fileinput-exists thumbnail" style=""></div>
                                <div>
                                    <span class="btn btn-rose btn-round btn-file">
                                        <span class="fileinput-new">Upload Client</span>
                                        <span class="fileinput-exists">Change</span>
                                        <input type="hidden"><input type="file" name="raw-client">
                                    </span>
                                    <a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> Remove</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-check pull-left">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" name="global" id="global">
                            <span class="form-check-sign"></span> Would this version be Global?
                        </label>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!--Add version ends-->

<!--edit version starts-->

<div id="edit-version" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Edit Version</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="edit-version">&times;</a>
    </div>

    <div class="btnGroup" id="editOption" style="display: block;">
        <div class="icon-circle">
            <div class="toolTip editOption">
                <i class="tim-icons icon-pencil"></i>
                <span class="tooltiptext">Edit</span>
            </div>
        </div>
    </div>

    <div class="btnGroup" style="display: none;" id="toggleButton">
        <div class="icon-circle iconTick circleGrey">
            <div class="toolTip" onclick="editversiondetail();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>

        <div class="icon-circle toggleEdit" id="toggleEdit">
            <div class="toolTip">
                <i class="tim-icons icon-simple-remove"></i>
                <span class="tooltiptext">Toggle edit mode</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <!--<div class="row">-->
                    <div class="form-group has-label">
                        <label>Version Name<em class="error">*</em></label>
                        <input class="form-control" name="editversionname" id="editversionname" type="text" />
                    </div>

                    <div class="form-group has-label">
                        <label>Version number<em class="error">*</em></label>
                        <input class="form-control" name="editversionnumber" id="editversionnumber" type="text" />
                    </div>

                    <div class="form-group has-label">
                        <label>Operating system<em class="error">*</em></label>
                        <select class="selectpicker" data-style="btn btn-info" title="Select OS" data-size="3" id="editversionostype" name="editversionostype">
                            <option>Windows</option>
                            <option>Mac</option>
                            <option>Android</option>
                            <option>iOS</option>
                            <option>Linux</option>
                        </select>
                    </div>

                    <div class="form-group has-label">
                        <label>Username<em class="error">*</em></label>
                        <input class="form-control" name="edituser_name" id="edituser_name" type="text" />
                    </div>

                    <div class="form-group has-label">
                        <label>Password<em class="error">*</em></label>
                        <input class="form-control" name="editpass_word" id="editpass_word" type="password" />
                    </div>

                    <div class="form-group has-label">
                        <label>Command Line<em class="error">*</em></label>
                        <input class="form-control" name="editcommandline" id="editcommandline" type="text" />
                    </div>

                    <div class="row form-group has-label">
                        <div class="col-sm-5 checkbox-radios">
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input cuo-cl" type="radio" name="client-url-opt" value="1" checked="checked">
                                    <span class="form-check-sign"></span>
                                    Download Url
                                </label>
                            </div>
                        </div>

                        <div class="col-sm-5 checkbox-radios">
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input cuo-cl" type="radio" name="client-url-opt" value="2">
                                    <span class="form-check-sign"></span>
                                    Upload Client
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group has-label av-durl-w">
                        <label>Download URL<em class="error">*</em></label>
                        <input class="form-control" name="editurl" id="editurl" type="text" />
                    </div>

                    <div class="row av-upc-w" style="margin-top: 28px;display:none">
                        <div class="col-md-16 col-sm-16" style="margin: 0 auto;">
                            <h4 class="card-title" style="background:transparent">&nbsp;</h4>
                            <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                <div class="fileinput-new thumbnail">
                                    <img src="../assets/img/image_placeholder.jpg" alt="...">
                                </div>
                                <div class="fileinput-preview fileinput-exists thumbnail" style=""></div>
                                <div>
                                    <span class="btn btn-rose btn-round btn-file">
                                        <span class="fileinput-new">Upload Client</span>
                                        <span class="fileinput-exists">Change</span>
                                        <input type="hidden"><input type="file" name="raw-client">
                                    </span>
                                    <a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> Remove</a>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="form-check pull-left">
                        <label class="form-check-label"><em class="error">*</em>
                            <input class="form-check-input" type="checkbox" name="edit_global" id="edit_global">
                            <span class="form-check-sign"></span> Would this version be Global?
                        </label>
                    </div>
                    <span id="editversionerror"></span>
                </div>
            </div>
        </form>
    </div>
</div>

<!--edit version ends-->

<!--copy version starts-->

<div id="copy-version" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Copy Version</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="copy-version">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="btn-copyversionDetail" onclick="copyversionDetail();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label>Version Name<em class="error">*</em></label>
                        <input class="form-control" name="copy_versionname" id="copy_versionname" type="text" />
                    </div>

                    <div class="form-group has-label">
                        <label>Version number<em class="error">*</em></label>
                        <input class="form-control" name="copy_versionnumber" id="copy_versionnumber" type="text" />
                    </div>

                    <div class="form-group has-label">
                        <label>Operating system<em class="error">*</em></label>
                        <select class="selectpicker" data-style="btn btn-info" title="Select Os" data-size="3" id="copy_versionostype" name="copy_versionostype">
                            <option>Windows</option>
                            <option>Mac</option>
                            <option>Android</option>
                            <option>iOS</option>
                            <option>Linux</option>
                        </select>
                    </div>

                    <div class="form-group has-label">
                        <label>Download URL<em class="error">*</em></label>
                        <input class="form-control" name="copy_url" id="copy_url" type="text" />
                    </div>

                    <div class="form-group has-label">
                        <label>Username<em class="error">*</em></label>
                        <input class="form-control" name="copy_user_name" id="copy_user_name" type="text" />
                    </div>

                    <div class="form-group has-label">
                        <label>Password<em class="error">*</em></label>
                        <input class="form-control" name="copy_pass_word" id="copy_pass_word" type="password" />

                    </div>

                    <div class="form-group has-label">
                        <label>Command Line<em class="error">*</em></label>
                        <input class="form-control" name="copy_commandline" id="copy_commandline" type="text" />
                    </div>

                    <div class="form-check pull-left">
                        <label class="form-check-label"><em class="error">*</em>
                            <input class="form-check-input" type="checkbox" name="copy_global" id="copy_global">
                            <span class="form-check-sign"></span> Would this version be Global?
                        </label>
                    </div>
                    <span id="copy_error"></span>
                </div>
            </div>
        </form>
    </div>
</div>

<!--copy version ends-->

<!--upload client starts-->

<div id="update-version" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Update Version</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="update-version">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="versionadd();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <!--<div class="row">-->
                    <div class="form-group has-label">
                        <label>Windows</label>
                        <select class="selectpicker" data-style="btn btn-info" data-size="3" id="windowselect" name="windowselect">
                        </select>
                    </div>

                    <div class="form-group has-label">
                        <label>Android</label>
                        <select class="selectpicker" data-style="btn btn-info" data-size="3" id="androidselect" name="androidselect">
                        </select>
                    </div>

                    <div class="form-group has-label">
                        <label>Linux</label>
                        <select class="selectpicker" data-style="btn btn-info" data-size="3" id="linuxselect" name="linuxselect">
                        </select>
                    </div>

                    <div class="form-group has-label">
                        <label>Mac</label>
                        <select class="selectpicker" data-style="btn btn-info" data-size="3" id="macselect" name="macselect">
                        </select>
                    </div>

                    <div class="form-group has-label">
                        <label>iOS</label>
                        <select class="selectpicker" data-style="btn btn-info" data-size="3" id="iosselect" name="iosselect">
                        </select>
                    </div>

                    <!--</div>-->
                    <span id="Error_versionadd"></span>
                </div>
            </div>
        </form>
    </div>
</div>

<!--upload client ends-->

<!--details popup-->

<div id="version_detail" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4>Version Details</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="version_detail">&times;</a>
    </div>

    <div class="btnGroup" style="width:100%;">&nbsp;</div>
    <!--                <div class="btnGroup">
                    <div class="icon-circle">
                        <div class="toolTip" onclick="versionlistexport();">
                            <i class="tim-icons icon-send" id="export_icon"></i>
                            <span class="tooltiptext">Export</span>
                        </div>
                    </div>
                </div>-->

    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="versionDetail">
                        <thead>
                            <tr>
                                <th>Machine Name</th>
                                <th>Last Version</th>
                                <th>Old Version</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </form>
    </div>
</div>
<!--details ends-->



<!--upload core db starts-->

<div id="upload-core-db-wrap" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Upload Core DB</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="upload-core-db-wrap">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="btn-uploadCoreDB" onclick="uploadCoreDB();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form name="upload-core-db-frm" class="upload-core-db-frm" method="POST" enctype="multipart/form-data" id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <!--                    <div class="form-group has-label">
                        <label>Name<em class="error" id="required_versionname">*</em></label>
                        <input class="form-control" name="db-name" type="text" />
                    </div>-->

                    <div class="row av-upc-w" style="margin-top: 28px;">
                        <div class="col-md-16 col-sm-16" style="margin: 0 auto;">
                            <h4 class="card-title" style="background:transparent">&nbsp;</h4>
                            <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                                <div class="fileinput-new thumbnail">
                                    <img src="../assets/img/image_placeholder.jpg" alt="...">
                                </div>
                                <div class="fileinput-preview fileinput-exists thumbnail" style=""></div>
                                <div class="core-dbn-chng">
                                    <span class="btn btn-rose btn-round btn-file btn-success">
                                        <span class="fileinput-new">Upload DB</span>
                                        <span class="fileinput-exists">Change</span>
                                        <input type="hidden"><input type="file" name="core-file" id="core-file" accept=".dbn">
                                    </span>
                                    <a id="dismiss-upload-dbn" href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> Remove</a>
                                </div>
                                <div id="core-dbn-ldr" style="display: none;">
                                    <img src="../assets/img/loader.gif"><br /><span>Please wait...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group has-label av-import-w"></div>
                    <div id="import-dbn-ldr" style="display: none; text-align: center;">
                         <img src="../assets/img/loader.gif"><br /><span>Please wait...</span>
                    </div>

                </div>
            </div>
        </form>
    </div>
</div>

<!--upload core db ends-->

<style>
    .import-dbn-data-w {
        cursor: pointer
    }

    #versionDetail_wrapper .bottom {
        margin-bottom: -93px;
    }

    #softwareupdategrid_filter{
        display:none;
    }
</style>
