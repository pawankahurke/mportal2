<div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body" id="main_card">
                    <div class="toolbar">

<!--                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item <?php echo setRoleForAnchorTag('addUserDash', 2); ?>" id="AddDashboard" onclick="openUsersDashboardSlider();">Add Dashboard</a>
                                    <a class="dropdown-item <?php echo setRoleForAnchorTag('backbtn', 2); ?>" id="deleteDashboard" onclick="DeleteDashboard('dash');">Delete Dashboard</a>
                                    <a class="dropdown-item <?php echo setRoleForAnchorTag('backbtn', 2); ?>" id="backWindowbtn" onclick="BackToMain();">Back</a>
                                </div>
                            </div>
                        </div>-->
                    </div>
                    <input type="hidden" id="dashId" value="">
                    <input type="hidden" id="dashName" value="">
                    <input type="hidden" id="dashType" value="">
                    
                    <div id="PreviewDashTable">
                        <div class="row">
                            <div class="col-md-3">Select URL to check status</div>
                            <div class="col-md-3">
                                <select class="selectpicker" id="selectedUrlType" data-style="btn btn-info" onchange="saveSelectedStatus()" data-size="7">
                                    <option value="0">License server</option>
                                    <option value="1">Reporting</option>
                                    <option value="2">Node</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-3">Selected Url</div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" readonly id="insertUrl">
                                <input type="hidden" id="hiddenValueUrl">
                            </div>
                        </div>
                        
                        <div class="row">
                            <button type="button" class="swal2-confirm btn btn-success btn-sm filterSubmit" aria-label="" onclick="checkUrlStatus()">Check Status</button>
                            <span class="errorMsg" id="msgError"><b></b></span>
                        </div>
                    </div>
                    
                </div>
                <!-- end content
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>
<style>
   .filterSubmit{
        margin-top: 17px;
        margin-left: 33%;
    }
    
    .errorMsg{
        margin-left: 16px;
        margin-top: 20px;
    }
</style>