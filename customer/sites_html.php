<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?><div class="content white-content commonTwo">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div id="loader" class="loader"  data-qa="loader" style="display:none">&nbsp;<img src="../assets/img/nanohealLoader.gif" style="width: 71px;"></div>
                    <div class="toolbar">
                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item rightslide-container-hand" data-target="site-add-container" onclick="addSitePopup();" href="javascript:">Add New Site</a>
                                    <a class="dropdown-item rightslide-container-hand" href="javascript:" onclick="getCustomerDownloadURL();" data-target="url-pop-container">Download URL</a>
                                    <a class="dropdown-item" href="javascript:" id="exportAllSites">Export</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row clearfix two">
                        <div class="col-md-12">
                            <div class="row clearfix">
                                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 left equalHeight">
                                    <table class="nhl-datatable table table-striped" id="msp_Sites_Grid">
                                        <thead>
                                            <tr>
                                                <th>Sites</th>
                                                <th>Installed Count</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>Sites</th>
                                                <th>Installed Count</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <div class="row clearfix">
                                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 right equalHeight">
                                    <table class="nhl-datatable table table-striped" id="msp_Device_Grid">
                                        <thead>
                                            <tr>
                                                <th>Device Name</th>
                                                <th>Installed Date</th>
                                                <th>Uninstalled Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>Device Name</th>
                                                <th>Installed Date</th>
                                                <th>Uninstalled Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
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
</div>


<!-- Add new site UI starts  -->
<div id="site-add-container" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Add Site</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="site-add-container">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="btn-addDeploymntSite" onclick="addDeploymntSite()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">
                <div class="form-group has-label">
                    <label for="deploy_sitename">Site name</label>
                    <input type="text" name="deploy_sitename" data-qa="deploy_sitename" id="deploy_sitename" class="form-control pl-2">
                </div>

                <div class="deploy_sitekey_div form-group has-label" style="display:none;">
                    <label for="deploy_sitekey">Enter the Activation key</label>
                    <input type="text" name="deploy_sitekey" id="deploy_sitekey" class="form-control" value="<?php echo  isset($_SESSION['user']['licenseKey']) ? $_SESSION['user']['licenseKey'] : ''  ?>">
                </div>

                <div class="download_url_div form-group has-label" style="display:none;">
                    <label for="download_url">Download URL</label>
                    <input type="text" name="download_url" id="download_url" readonly="" class="form-control">
                </div>

                <div class="button col-md-12 text-left">
                    <p id="required_Sitename" style="color: red;font-size: 14px;"></p>
                    <!-- <button type="button" class="swal2-confirm btn btn-success" aria-label="" onclick="create_Site();">Add</button> -->
                    <!--<button type="button" class="swal2-confirm btn btn-success" id="btn-addDeploymntSite" onclick="addDeploymntSite()">Submit</button>-->
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Add new site UI ends -->

<!-- download url pop up start -->
<div id="url-pop-container" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h3>Download URL</h3>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="url-pop-container">&times;</a>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card-body">
            <form action="#">
                <input type="hidden" class="form-control" id="selCustomer2" placeholder="" name="selCustomer2" value="<?php echo $_SESSION['user']['cd_eid'] ?>">
                <div class="form-group has-label">
                    <label>Select Site</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Site" data-size="3" id="selSite" name="selSite" onchange="getdownloadUrl(this)">
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Download URL</label>
                    <input class="form-control" name="site_download_url" id="site_download_url" readonly="true" type="text" />
                </div>

            </form>

            <div class="button col-md-12 text-left">
                <button type="button" class="swal2-confirm btn btn-success" aria-label="" id="copy_link1">Copy</button>
            </div>
        </div>
    </div>
</div>
<!-- download url pop up end -->
