<div class="content white-content commonTwo">
    <input type="hidden" id="valueSearch" value="<?php echo url::toText($_SESSION['searchValue']); ?>">
    <input type="hidden" id="selected">
    <input type="hidden" id="selectedPackage">
    <!--                <input type="hidden" id="searchType" name="searchType" value=""/> 
                <input type="hidden" id="searchValue" name="searchValue" value=""/>
                <input type="hidden" id="rparentName" name="rparentName" value=""/>
                <input type="hidden" id="searchLabel" name="searchLabel" value=""/>
                <input type="hidden" id="passlevel" name="passlevel" value=""/>
                <input type="hidden" id="rcensusId" name="rcensusId" value=""/>
                <input type="hidden" id="currentwindow" name="currentwindow" value="softwaredistribution"/>
                
                
                
                <input type="hidden" id="selected">
                <input type="hidden" id="selectedPackage">-->

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <!--        Here you can write extra buttons/actions for the toolbar              -->

                        <div class="bullDropdown leftDropdown">
                            <!--<h5>Selection: <span id="audit_selected_title" class="site"><?php echo $_SESSION['searchValue']; ?></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>-->
                            <div class="dropdown">
                                <h5>Selection: <span id="audit_selected_title" class="site"></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>
                            </div>
                        </div>
                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" href="../softdist/index.php">Back</a>
                                    <a class="dropdown-item" onclick="exportaudit();" href="#">Export Details</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row clearfix two">
                        <div class="col-md-12">
                            <div class="row clearfix">
                                <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12 left equalHeight">
                                    <table id="patchesGrid" class="nhl-datatable table table-striped" data-page-length="25">
                                        <thead>
                                            <tr>
                                                <th>Package Name</th>
                                                <th>Triggered Time</th>
                                                <th>Agent</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                            <div class="row clearfix">
                                <div class="col-lg-7 col-md-7 col-sm-12 col-xs-12 right equalHeight">
                                    <table id="auditGridDetail" class="nhl-datatable table table-striped" data-page-length="25">
                                        <thead>
                                            <tr>
                                                <th>Scope</th>
                                                <th>Machine</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>Scope</th>
                                                <th>Machine</th>
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