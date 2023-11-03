<div class="content white-content">
    <input type="hidden" id="selected">
    <input type="hidden" id="selOsType">
    <input type="hidden" id="selectedPackageName">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <div class="bullDropdown leftDropdown">
                        &nbsp;
                        </div>

                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('distributesoftwaredistribution', 2); ?>" id="distexecPack">Deploy & Install</a>
                                    <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('exportsoftdist', 2); ?>" onclick="exportaudit()">Export Status</a>
                                    
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- <table id="packageGrid2" class="nhl-datatable table table-striped">
                        <thead>
                            <tr>
                                <th class="id" style="width:2%">Id</th>
                                <th class="platfrom" style="width:10%">Platform</th>
                                <th class="packageName" style="width:15%">Package Name</th>
                                <th class="packageDesc" style="width:15%">Description</th>
                                <th class="version" style="width:10%">Version</th>
                                <th class="isDistributed" style="width:5%">Configured?</th>
                            </tr>
                        </thead>
                    </table> -->
                </div>
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>

 <!-- Distribution/Execution Popup start -->

<div id="rsc-distribute-execute-slider" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Distribution/Execution Configuration</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="rsc-distribute-execute-slider">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="saveConfig();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form>
            <div class="card">
                <div class="card-body">
                    <div class="dist-exe-config-popup">
                        <div>
                            <h5>Select the site you want to deploy the software on: 
                                <div style="margin-top: 10px;">
                                    <span class="site indistro-selection-label"><?php echo $_SESSION['searchValue']; ?></span> 
                                    <button type="button" class="swal2-confirm btn btn-default btn-sm indistro-sitegroup-selection" value="Change" aria-label="">Change</button>
                                </div>
                            </h5>
                        </div>
                        
                        
                        <div class="form-group has-label" id="distributeDiv">
                            <label>Do you want to Deploy?</label>
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input style="margin-top: 0px;" class="form-check-input" type="radio" id="distributeNowYes" name="distributeNowYes" value="1">
                                    <span class="form-check-sign">Yes</span>
                                </label>
                            </div>
                            <div class="form-check form-check-radio">
                                <label style="margin-left: -56px;" class="form-check-label">
                                    <input style="margin-top: 0px;" class="form-check-input" type="radio" id="distributeNowNo" name="distributeNowNo" value="0">
                                    <span class="form-check-sign">No</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group has-label" id="executeDiv" style="margin-top: 36px;">
                            <label>Do you want to Execute?</label>
                            <div class="form-check form-check-radio">
                                <label  class="form-check-label">
                                    <input style="margin-top: 0px;" class="form-check-input" type="radio" id="executeNowYes" name="executeNowYes" value="1">
                                    <span class="form-check-sign"></span>Yes
                                </label>
                            </div>


                            <div class="form-check form-check-radio">
                                <label style="margin-left: -56px;" class="form-check-label">
                                    <input style="margin-top: 0px;" class="form-check-input" type="radio" id="executeNowNo" name="executeNowNo" value="0">
                                    <span class="form-check-sign"></span>No
                                </label>
                            </div>
                        </div>
                        
                        <div style="margin-top:80px" class="form-group has-label">
                            <textarea class="form-control" id="edconfig" name="edconfig" style="height:350px;width:850px;display: none;resize:none;max-height:400px" contenteditable='true'></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="button col-md-12 text-center">
        <input type="hidden" id="distId" name="distId">
        <span id="valsub" localized="" class="inslider-feed error"></span>
    </div>
</div>

<!-- Distribution/Execution Popup end -->

 <!-- Export Popup start -->

<div id="rsc-export-slider" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Export the status of Software distribution</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-bs-target="rsc-export-slider">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="ExportAuditConfig();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Export</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form>
            <div class="card">
                <div class="card-body">
                    <div class="dist-exe-config-popup">
                        <div>
                            <h5>Select the site: 
                                <div style="margin-top: 10px;">
                                    <span class="site indistro-selection-label"><?php echo $_SESSION['searchValue']; ?></span> 
                                    <button type="button" class="swal2-confirm btn btn-default btn-sm indistro-sitegroup-selection2" value="Change" aria-label="">Change</button>
                                </div>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="button col-md-12 text-center">
        <input type="hidden" id="distId" name="distId">
        <span id="valsub" localized="" class="inslider-feed error"></span>
    </div>
</div>

<!-- Export Popup end -->

<style>
    .swal2-confirm{
        background-color: #050d30;
    }
    #files.CdnFilesList .repositoryList{
        width:100%;
    }
    
    #files.CdnFilesList .repositoryList span.check{
        margin-left: 10px;
    }
    
    #ftpspan, #cdnspan{
        position: relative;
        top: 11px;
        left: -120px;
        color: red;
        font-size: 12px; 
    }
</style>
<!-- FTP/CDN Configure close -->