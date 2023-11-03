<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
?><div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body" id="main_card">
                    <div class="toolbar">
                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                            </div>
                        </div>

                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item rightslide-container-hand <?php echo setRoleForAnchorTag('addsiem', 2); ?>" data-target="add-siem" href="#">Add Siem </a>
                                    <!--<a class="dropdown-item rightslide-container-hand <?php ?>" data-target="configure-siem" href="#">Configure Siem </a>-->
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="selected" >
                    <table class="nhl-datatable table table-striped" id="siemTable">
                        <thead>
                            <tr>
                                <th>Name of configuration</th>
                                <th>Site</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!--add siem configuration starts-->

<div id="add-siem" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Add SIEM Configuration</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="add-siem">&times;</a>
    </div>


    <div class="btnGroup">
        <div class="icon-circle" onclick="submitsiem();">
            <div class="toolTip">
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
                        <label>
                            SIEM Name
                        </label>
                        <input class="form-control" name="SIEM_Name" type="text" id="SIEM_Name" required />
                    </div>
                    
                    <div class="form-group has-label">
                        <label>
                            Sites 
                        </label>
                        <select class="selectpicker" data-style="btn btn-info" title="Select Site" data-size="3" id="Sites" name="Sites">

                        </select>
                    </div>

                    <div class="form-group has-label">
                        <label>
                            Log Url
                        </label>
                        <input class="form-control" id="logurl" type="text" name="logurl"/>
                        <span id="logurl-err"></span>
                    </div>

                    <div class="form-group has-label">
                        <label>
                            Asset Url
                        </label>
                        <input class="form-control" id="asseturl" type="text" name="asseturl"/>
                        <span id="asseturl-err"></span>
                    </div>

                    <div class="form-group has-label">
                        <label>
                            Notify Url
                        </label>
                        <input class="form-control" id="notifyurl" type="text" name="notifyurl" />
                        <span id="logurl-err"></span>
                    </div>
                    
                    <div class="form-group has-label">
                        <label>
                            Compliance Url
                        </label>
                        <input class="form-control" id="compurl" type="text" name="compurl"/>
                        <span id="compurl-err"></span>
                    </div>
                    
                    <div class="form-group has-label">
                        <label>
                            Dart Url
                        </label>
                        <input class="form-control" id="darturl" type="text" name="darturl"/>
                        <span id="darturl-err"></span>
                    </div>
                    
                    <div class="form-group has-label">
                        <label>
                            Proactive Url
                        </label>
                        <input class="form-control" id="prourl" type="text" name="prourl"/>
                        <span id="prourl-err"></span>
                    </div>
                    
                    <div class="form-group has-label">
                        <label>
                            Patch Url
                        </label>
                        <input class="form-control" id="patchurl" type="text" name="patchurl"/>
                        <span id="patchurl-err"></span>
                    </div>
                    
                    <div class="form-group has-label">
                        <label>
                            Event Url
                        </label>
                        <input class="form-control" id="eventurl" type="text" name="eventurl"/>
                        <span id="eventurl-err"></span>
                    </div>
                </div>
            </div>
            <!-- CSV File Upload -->
            <div class="row">
                <div class="col-md-12 col-sm-12" data-class="md-6" id="csvuploaddata">
                    <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                        <div class="form-group has-label ">
                            <!--<input class="form-control" type="text" id="uploadcsv" />-->

                            <p class="manual_group">
                                <span class="download_csv"  onclick="samplefileExport();">Click Here</span> to Download sample CSV
                            </p>
                            <p id="title_csv">Upload CSV</p>
                            <div class="col-md-12">
                                <p id="file_sel" class="txtsm">File selected : <span id="csv_name"></span></p>

                            </div>

                            <div class="col-md-12 btnBrowser">
                                <span class="btn btn-round btn-rose btn-file btn-sm">
                                    <span class="fileinput-new">Browse</span>
                                    <input type="file" name="csv" id="csv_file" name="csv_file" accept=".csv" />
                                </span>

                                <span class="btn btn-success btn-round btn-sm" id="remove_logo" style="display:none;">
                                    <span class="fileinput-new">Remove</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div> 
            </div>
            <!--   CSV File Upload -->
        </form>
    </div>
</div>

<!--add siem configuration ends-->


<!--edit siem configuration starts-->

<div id="edit-new-siem" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Edit Configuration</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="edit-new-siem">&times;</a>
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
            <div class="toolTip" onclick="editsiem()">
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
                    <div class="form-group has-label">
                        <label>
                            SIEM Name
                        </label>
                        <input class="form-control" name="edit_SIEM_Name" type="text" id="edit_SIEM_Name" readonly=""/>
                    </div>
                    
                    <div class="form-group has-label">
                        <label>
                            Sites 
                        </label>
                        <select class="selectpicker" data-style="btn btn-info" title="Select Site" data-size="3" id="edit_Sites" name="edit_Sites">

                        </select>
                    </div>

                    <div class="form-group has-label">
                        <label>
                            Log Url
                        </label>
                        <input class="form-control" id="edit_logurl" type="text" name="edit_logurl">
                    </div>

                    <div class="form-group has-label">
                        <label>
                            Asset Url
                        </label>
                        <input class="form-control" id="edit_asseturl" type="text" name="edit_asseturl"/>
                    </div>

                    <div class="form-group has-label">
                        <label>
                            Notify Url
                        </label>
                        <input class="form-control" id="edit_notifyurl" type="text" name="edit_notifyurl"/>
                    </div>
                    
                    <div class="form-group has-label">
                        <label>
                            Compliance Url
                        </label>
                        <input class="form-control" id="edit_compurl" type="text" name="edit_compurl"/>
                    </div>
                    
                    <div class="form-group has-label">
                        <label>
                            Dart Url
                        </label>
                        <input class="form-control" id="edit_darturl" type="text" name="edit_darturl"/>
                    </div>
                    
                    <div class="form-group has-label">
                        <label>
                            Proactive Url
                        </label>
                        <input class="form-control" id="edit_prourl" type="text" name="edit_prourl"/>
                    </div>
                    
                    <div class="form-group has-label">
                        <label>
                            Patch Url
                        </label>
                        <input class="form-control" id="edit_patchurl" type="text" name="edit_patchurl"/>
                    </div>
                    
                    <div class="form-group has-label">
                        <label>
                            Event Url
                        </label>
                        <input class="form-control" id="edit_eventurl" type="text" name="edit_eventurl"/>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!--edit siem configuration ends-->


<!--configure siem starts-->

<div id="configure-siem" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Configure SIEM</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="configure-siem">&times;</a>
    </div>


    <div class="btnGroup">
        <div class="icon-circle" onclick="configuresiem();">
            <div class="toolTip">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Send</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label>
                            Send SIEM Configuration to
                        </label>
                        <input class="form-control" name="siem-confemail" type="text" id="siem-confemail" required />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>