<div class="content white-content">
    <input type="hidden" id="selected">
    <input type="hidden" id="selOsType">
    <input type="hidden" id="selectedPackageName">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <?php
                    //   nhRole::checkRoleForPage('softwaredistributionconfig');
                    $res = true; // nhRole::checkModulePrivilege('softwaredistributionconfig');
                    if ($res) {
                    ?>
                        <!-- loader -->
                        <div id="loader" class="loader" data-qa="loader" style="position: absolute;bottom: 50%;right:50%;">
                            <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                        </div>
                        <table class="nhl-datatable table table-striped" id="swdGrid2">
                            <thead>
                                <tr>
                                    <th id="key0" headers="platform" class="platform">
                                        Platform
                                        <i class="fa fa-caret-down cursorPointer direction" id="platform1" onclick="addActiveSort('asc', 'platform'); Get_SoftwareRepositoryData2(1,notifSearch='','platform', 'asc');sortingIconColor('platform1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="platform2" onclick="addActiveSort('desc', 'platform'); Get_SoftwareRepositoryData2(1,notifSearch='','platform', 'desc');sortingIconColor('platform2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key1" headers="packageName" class="packageName">
                                        Package Name
                                        <i class="fa fa-caret-down cursorPointer direction" id="packageName1" onclick="addActiveSort('asc', 'packageName'); Get_SoftwareRepositoryData2(1,notifSearch='','packageName', 'asc');sortingIconColor('packageName1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="packageName2" onclick="addActiveSort('desc', 'packageName'); Get_SoftwareRepositoryData2(1,notifSearch='','packageName', 'desc');sortingIconColor('packageName2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key2" headers="packageDesc" class="packageDesc">
                                        Description
                                        <i class="fa fa-caret-down cursorPointer direction" id="packageDesc1" onclick="addActiveSort('asc', 'packageDesc'); Get_SoftwareRepositoryData2(1,notifSearch='','packageDesc', 'asc');sortingIconColor('packageDesc1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="packageDesc2" onclick="addActiveSort('desc', 'packageDesc'); Get_SoftwareRepositoryData2(1,notifSearch='','packageDesc', 'desc');sortingIconColor('packageDesc2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key3" headers="version" class="version">
                                        Version
                                        <i class="fa fa-caret-down cursorPointer direction" id="version1" onclick="addActiveSort('asc', 'version'); Get_SoftwareRepositoryData2(1,notifSearch='','version', 'asc');sortingIconColor('version1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="version2" onclick="addActiveSort('desc', 'version'); Get_SoftwareRepositoryData2(1,notifSearch='','version', 'desc');sortingIconColor('version2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key4" headers="distrubute" class="isDistributed">
                                        Configured?
                                        <i class="fa fa-caret-down cursorPointer direction" id="distrubute2" onclick="addActiveSort('asc', 'distrubute'); Get_SoftwareRepositoryData2(1,notifSearch='','distrubute', 'asc');sortingIconColor('distrubute1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="distrubute2" onclick="addActiveSort('desc', 'distrubute'); Get_SoftwareRepositoryData2(1,notifSearch='','distrubute', 'desc');sortingIconColor('distrubute2')" style="font-size:18px"></i>
                                    </th>
                                    <!-- <th id="key6" headers="distrubute" class="isDistributed">
                                Configured?
                                <i class="fa fa-caret-down cursorPointer direction" id = "distrubute2" onclick = "Get_SoftwareRepositoryData2(1,notifSearch='','distrubute', 'asc');sortingIconColor('distrubute1')" style="font-size:18px"></i>
                                <i class="fa fa-caret-up cursorPointer direction" id = "distrubute2" onclick = "Get_SoftwareRepositoryData2(1,notifSearch='','distrubute', 'desc');sortingIconColor('distrubute2')" style="font-size:18px"></i>
                                </th> -->
                                </tr>
                            </thead>
                        </table>
                    <?php
                    }
                    ?>
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

<!-- Distribution/Execution Popup start -->

<div id="rsc-distribute-execute-slider" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Distribution/Execution Configuration</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-distribute-execute-slider">&times;</a>
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
                        <div>
                            This package has:
                        </div>
                        <br>
                        <div>
                            <!-- Deploy - <input style="margin-top: 0px; margin-left: 0px;" class="form-check-input" type="radio" disabled> <br>
                            Execute - <input style="margin-top: 0px; margin-left: 0px;" class="form-check-input" type="radio" disabled> -->
                            Deploy - <span id="deploySWDpackage"></span> <br>
                            Execute - <span id="executeSWDpackage"></span>
                        </div>
                        <!-- <div class="form-group has-label" id="distributeDiv">
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
                                <label class="form-check-label">
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
                        </div> -->

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
    <div class="card-title border-bottom">
        <h4>Export the status of Software distribution</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-export-slider">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="btn-exportAuditConfig" onclick="ExportAuditConfig();">
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
    /* .swal2-confirm {
        background-color: #050d30;
    } */

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

    .showbtn {
        margin-left: 119px;
    }

    .clearbtn {
        margin-left: 119px;
    }
</style>
<!-- FTP/CDN Configure close -->