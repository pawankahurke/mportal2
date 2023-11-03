<!-- content starts here  -->
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
?>
<div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <!--        Here you can write extra buttons/actions for the toolbar              -->
                        <input type="hidden" id="selected" name="selected">
                        <input type="hidden" id='grupnamehidden' name='grupnamehidden'>
                        <input type="hidden" id="Count" name="Count">
                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                            </div>
                        </div>

                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" id = "settingGroupDrop" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item rightslide-container-hand <?php echo setRoleForAnchorTag('addgroup', 2);?>" data-bs-target="grp-add-container">Add new group</a>
                                    <!--<a class="dropdown-item rightslide-container-hand" id="edit_group_drop" href="#" onclick="selectConfirm('edit_group_drop')" >Modify group</a>-->
                                    <!-- <a class="dropdown-item" data-bs-target="rsc-add-container3" href="/" onclick="return false;" id="viewdetail_group_drop">View Details</a> -->
                                    <a class="dropdown-item rightslide-container-hand <?php echo setRoleForAnchorTag('exportgroupdetails', 2);?>" data-bs-target="rsc-add-container4" href="#" onclick="selectConfirm('export_group_list');" id="export_group_list">Export group detail</a>
                                    <a class="dropdown-item rightslide-container-hand <?php echo setRoleForAnchorTag('exportgrouplist', 2);?>" data-bs-target="rsc-add-container5" href="#" onclick="selectConfirm('view_detail_export');" id="view_detail_export">Export group list</a>
                                    <!--  <a class="dropdown-item" data-bs-target="rsc-add-container6" href="/" onclick="return false;" id="">Advanced group</a> -->
                                    <a class="dropdown-item rightslide-container-hand <?php echo setRoleForAnchorTag('deletegroup', 2);?>" data-bs-target="rsc-add-container7" href="#" id="delete_group" onclick="selectConfirm('delete_group');">Delete group</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="nhl-datatable table table-striped" id="groupdataList">
                        <thead>
                            <tr>
                                <th class="table-plus">Group</th>
                                <th>Count</th>
                                <th>Type</th>
                                <th>Global</th>
                                <th>Created Time</th>
                                <!-- <th>Owner</th> -->
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>Group</th>
                                <th>Count</th>
                                <th>Type</th>
                                <th>Global</th>
                                <th>Created Time</th>
                                <!-- <th>Owner</th> -->
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>

<!-- Add New Group starts -->
<div id="grp-add-container" class="rightSidenav addGroup" data-class="md-6">
    <div class="card-title border-bottom">
        <h4>Add group</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="grp-add-container">&times;</a>
    </div>
    <div class="btnGroup" style="display: block;">
        <div class="icon-circle" id="csvuploadbutton">
            <div class="toolTip" onclick="csvgroupcreate();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>

        <div class="icon-circle" id="manualmachinebutton" style="display: none;">
            <div class="toolTip" onclick="manualgroupcreate();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <div class="card-body">
                <div class="form-group has-label">
                    <label for="csvgname">
                        Group Name
                    </label>
                    <input class="form-control" type="text" id="csvgname" />
                </div>
            </div>

            <div class="card-body">
                <div class="form-group has-label">
                    <label for="csvgname">
                        Would this group be Global?
                    </label>
                </div>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="exampleRadios1" id="globalyes">
                        <span class="form-check-sign"></span>
                        Yes
                    </label>
                </div>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="exampleRadios1" id="globalno">
                        <span class="form-check-sign"></span>
                        No
                    </label>
                </div>
            </div>
            <div class="card-body">
                <div class="form-group has-label">
                    <label for="csvgname">
                        Add Devices to Groups
                    </label>
                </div>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" id="csvradio" name="exampleRadios" value="option1">
                        <span class="form-check-sign"></span>
                        CSV     
                    </label>
                </div>

                <div class="form-check form-check-radio">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="exampleRadios" id="manualradio" value="option2">
                        <span class="form-check-sign"></span>
                        Manual
                    </label>
                </div>
            </div>


        </div>

        <!-- CSV File Upload -->
        <div class="col-md-3 col-sm-4" data-class="md-6" id="csvuploaddata" style="display:none;">
            <div class="fileinput fileinput-new text-center" data-provides="fileinput">
                <div class="form-group has-label ">
                    <p class="manual_group">
                        Upload CSV
                    </p>
                    <input class="form-control"  type="text"  id="uploadcsv" />
                </div>
                <div class="samplefile">
                    Import CSV file | <a onclick="samplefileExport();" style="cursor: pointer; color: #48b2e4;">sample file</a>
                    <div class="fileinput-preview fileinput-exists thumbnail img-circle"></div>
                    <div>
                        <span class="btn btn-round btn-rose btn-file">
                            <span class="fileinput-new" name="csvS" id="csvS" onclick='document.getElementById("fileuploader").click();
                                    javascript:setTimeout(function () {
                                        $("#add_file").show();
                                    }, 2000);'>Browse for file</span>
                            <span class="fileinput-exists">Change</span>
                            <input name="csv" type='file' id='fileuploader' accept=".csv"/>
                        </span>
                        <br>
                        <a href="#pablo" class="btn btn-danger btn-round fileinput-exists" data-dismiss="fileinput"><i class="fa fa-times"></i> Remove</a>
                    </div>
                </div> 
            </div>
        </div>
        <!--   CSV File Upload -->

        <!-- Manual File Upload -->

        <div id="manualmachinelist" style="display:none;">
            <p class="manual_group add_group" id="groupslider">Click here to select the device manually</p>
            <div id="machine_count" style="display:none;"><p><span id="machinecount"></span> Devices are added to this group <p class="manual_group add_group"> Edit</p></p></div>
        </div>
        <!--                <div class="card-footer">
                        <div class="button col-md-12 text-center" id="csvuploadbutton">
                                <span id="loadingCSVAdd"></span>
                                <span class="error" id="successmsg"></span>
                            <button type="button" class="swal2-confirm btn btn-success btn-sm" onclick="csvgroupcreate();" aria-label="">Submit</button>
                        </div>
                        </div>
        
                        <div class="button col-md-12 text-center" id="manualmachinebutton" style="display: none;">
                                <span id="loadingMaualAdd"></span>
                                <span class="error" id="successmsgmanual"></span>
                                <button type="button" class="swal2-confirm btn btn-success btn-sm" onclick="manualgroupcreate();" aria-label="">Submit</button>
                        </div>-->
    </div>
</div>
<!-- Add New Group  end-->


<!-- Edit Group -->
<div id="edit-group" data-bs-target="edit_group_drop" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4>Edit group</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="edit-group">&times;</a>
    </div>

    <div class="btnGroup" style="display: block;" id="editOption">
        <div class="icon-circle">
            <div class="toolTip editOption">
                <i class="tim-icons icon-pencil"></i>
                <span class="tooltiptext">Edit</span>
            </div>
        </div>
    </div>

    <div class="btnGroup" style="display: none;" id="toggleButton">
        <div class="icon-circle iconTick circleGrey" id="editcsvuploadbutton">
            <div class="toolTip" onclick="csvgroupedit();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save edit change</span>
            </div>
        </div>
        <div class="icon-circle iconTick circleGrey" id="editmanualmachinebutton" style="display: none;">
            <div class="toolTip" onclick="manualgroupedit();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save edit change</span>
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
                    <div class="form-group has-label" id="editfocused">
                        <label for="editcsvgname">
                            Group Name
                        </label>
                        <input class="form-control" type="text" id="editcsvgname" />
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group has-label">
                        <label for="csvgname">
                            Would this group be Global?
                        </label>
                    </div>
                    <div class="form-check form-check-radio">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="editRadios1" id="editglobalyes">
                            <span class="form-check-sign"></span>
                            Yes
                        </label>
                    </div>

                    <div class="form-check form-check-radio">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="editRadios1" id="editglobalno">
                            <span class="form-check-sign"></span>
                            No
                        </label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group has-label">
                        <label for="csvgname">
                            Add Devices to Groups
                        </label>
                    </div>
                    <div class="form-check form-check-radio">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" id="editcsvradio" name="editRadios" value="option1">
                            <span class="form-check-sign"></span>
                            CSV     
                        </label>
                    </div>

                    <div class="form-check form-check-radio">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="editRadios" id="editmanualradio" value="option2">
                            <span class="form-check-sign"></span>
                            Manual
                        </label>
                    </div>
                </div>
            </div>



            <!-- CSV Edit File Upload -->
            <div class="col-md-3 col-sm-4" id="editcsvuploaddata" style="display:none;">
                <label id="edit_file">File</label>
                <div class="file-uploadEdit clearfix">
                    <div class="samplefileEdit">
                        Import CSV file | <a onclick="samplefileEditExport();" style="cursor: pointer; color: #48b2e4;" href="#">sample file</a>
                    </div>
                    <div class="samplefileEdit2"></div>
                    <div class="browse-fileEdit">                           
                        <input name="csvedit" type='file' id='fileuploader1' accept=".csv"/>
                        <span id="csvSedit" name="csvSedit" onclick='document.getElementById("fileuploader1").click()'>Browse for file</span>
                    </div>
                    <span onclick='javascript:;' class="remove-fileEdit">Remove</span>
                </div>
            </div>
            <!--   CSV Edit File Upload -->

            <!-- Manual Edit File Upload -->

            <div id="editmanualmachinelist" style="display:none;">
                <div id="machine_count"><p><span id="editmachinecount"></span> Devices are added to this group <p class="manual_group manual_editgroup"> Edit</p></p></div>

            </div> 
            <!--  Manual  Edit File Upload -->

        </form>
    </div>

<!--    <div class="button col-md-12 text-center" id="editcsvuploadbutton" >
        <span id="loadingCSVEdit" style="color: green;float: center;"></span>
        <span id="successmsgedit"></span>
        <button type="button" class="swal2-confirm btn btn-success btn-sm" onclick="csvgroupedit();" >Edit</button>
    </div>

    <div class="button col-md-12 text-center" id="editmanualmachinebutton" style="display: none;">
        <span id="loadingMaualEdit" style="color: green;float: center;"></span>
        <span id="manualsuccessmsgedit"></span>
        <span id="manualeditfeed"></span>
        <button type="button" class="swal2-confirm btn btn-success btn-sm" onclick="manualgroupedit();">Edit</button>
    </div> -->
</div>
<!--Edit group end-->

<div id="rsc-add-container5" class="rightSidenav addGroupSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4 id="groupName"></h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-add-container5">&times;</a>
    </div>

    <div class="col-md-12 text-left">
        Select devices to add to this group
    </div>

    <div class="form table-responsive white-content">
        <div class="sidebar">
            <ul class="nav" id="include_machine">


            </ul>
        </div>
    </div>

    <div class="col-md-12 text-center submit">
        <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="addButton" onclick="addDevices();">Add Devices</button>
        <button type="button" class="swal2-confirm btn btn-primary btn-sm" aria-label="" onclick="editDevices();" id="editButton" style="display:none;">Modify</button>
    </div>
</div>

<style>
    .bullDropdown{
        padding-top: 10px !important;
    }
</style>