<style>
    #probAuto .nav {
        flex-wrap: nowrap;
    }

    /* #UserInput{
        width: 13%;
        margin-left: 84%;
    } */

    #UserInput {
        width: 96%;
        margin-left: 14px;
        top: 110px;
        position: absolute;
    }
</style>
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?>
<div class="content white-content services">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <!-- loader -->
                    <div id="loader" class="loader"  data-qa="loader" style="position: absolute;bottom: 50%;right:50%;">
                        <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                    </div>
                    <div class="toolbar">
                        <!--        Here you can write extra buttons/actions for the toolbar              -->

                        <!-- <div class="bullDropdown leftDropdown">
                                        <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>
                                        </div> -->
                        <!-- end bullDropdown leftDropdown -->
                        <div class="bullDropdown">

                        </div>
                    </div>

                    <!-- main content -->

                    <div class="row clearfix innerPage">
                        <div class="col-md-12">
                            <div class="card-body" id="divdetail">
                                <ul data-qa="parentDesc" id="parent_desc" class="nav nav-pills nav-pills-primary nav-pills-icons justify-content-left"></ul>

                                <!-- problem automation starts here -->

                                <!-- <div class="tab-content col-lg-11 col-md-10 col-sm-12 col-xs-12 innerTab" id="probAuto">
                                                    <div class="card-body nhl-tab-slider-main-container" id="services_card_body">
                                                        <div class="slideshow">
                                            <a class="prev nhl-tab-slider-previous" onclick="slideshow()"><i class="tim-icons icon-minimal-left"></i></a>
                                            <a class="next nhl-tab-slider-next" onclick="slideshow()"><i class="tim-icons icon-minimal-right"></i></a>

                                                                <ul class="nav nav-pills nav-pills-primary nav-pills-icons justify-content-center slideCont nhl-tab-slider-tab-container" id="child_desc">
                                                                   <!-- <li class="nav-item" type="hidden">
                                                                        <a class="nav-link toolTip" data-bs-toggle="tab" href="#" onclick="">
                                                                            <i class="tim-icons "></i> <span class="tooltext"> </span>
                                                                            <div class="middle">
                                                                                <div class="text"></div>
                                                                            </div>
                                                                        </a>
                                                                    </li>  -->
                                <!-- </ul>
                                                        </div>
                                                    </div>
                                </div> -->

                                <!-- end row clearfix innerPage -->
                                <input type="hidden" value="text" id="precedenceValue">
                                <input type="hidden" id="selectedsubchild">
                                <div id="services_search" class="center-search">
                                    <input type="text" class="form-control form-control-sm" id="UserInput" onchange="searchDarts()" placeholder="Search records">
                                </div>
                                <!-- checkbox contents -->

                                <div class="col-sm-12 tabBox table-responsive" style="display: block;">
                                    <div class="tab-content tab-space tab-subcategories">
                                        <div class="tab-pane" id="link">
                                            <div class="column appMonit">
                                                <div align="center" class="margin-top100">
                                                    <div id="searchloader">
                                                        <br>
                                                        <img src="../assets/img/loader.gif" />
                                                        <br>
                                                        <h5>Please wait..!</h5>
                                                    </div>
                                                </div>
                                                <div class="row" id="sub_child_desc">

                                                    <!-- <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 Box" id="sub_child_desc"> -->

                                                    <!-- </div> -->
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- end checkbox contents -->

                                <!-- configure container -->

                                <div id="configuresideBar" class="rightSidenav configSidenav" data-class="lg-9">
                                    <div class="card-title border-bottom">
                                        <!--<h4><span id="dartname"></span>[DART-<span id="dartnoid"></span>]</h4>-->
                                        <h4><span id="dartname"></span></h4>

                                        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="configuresideBar">&times;</a>
                                    </div>

                                    <div class="btnGroup">
                                        <div class="icon-circle">
                                            <div class="toolTip" onclick="onSubmit();">
                                                <i class="tim-icons icon-check-2"></i>
                                                <span class="tooltiptext">Submit</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form table-responsive white-content">
                                        <div align="center" class="margin-top100">
                                            <div id="loader" class="loader"  data-qa="loader">
                                                <br>
                                                <img src="../assets/img/loader.gif" />
                                                <br>
                                                <h5>Please wait..!</h5>
                                            </div>
                                        </div>

                                        <input type="text" id="precedenceType" style="display: none">
                                        <div class="sidebar" id="jsonModalDialogDivs">
                                        </div>
                                    </div>
                                </div>

                                <!-- end configure container -->
                                <!-- problem automation ends here -->
                            </div>
                            <!-- card body end -->
                        </div>
                        <!-- col-md-12 -->
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

<!-- end content -->

<!--IOS Config Dart-->

<div id="ios_config" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4 data-qa="iosConfig">iOS Configuration</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="ios_config">&times;</a>
    </div>

    <div class="btnGroup" id="btn_div">
        <div class="icon-circle">
            <div class="toolTip" onclick="syncIos();" id="syncnow">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Sync Now</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content" id="hide_div">
        <form id="RegisterValidation" class="configDiv">
            <div class="card">
                <div class="card-body">
                    <div class="group-page">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <table class="dt-responsive hover order-table nowrap" id="iosconfigGrid" width="100%" data-page-length="20">
                                <thead>
                                    <tr>
                                        <th>Scrip Num</th>
                                        <th>Scrip Name</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="button col-md-12 text-center">
            <span class="error-txt" id="error_add_entity" localized="" style="color:red;"></span>
        </div>
    </div>

    <div class="form table-responsive white-content" id="show_div">
        <button type="button" onclick="backToPage();" class="swal2-confirm btn btn-success btn-sm rightBtn" style="width: 13%;">Back</button>
        <form id="script_form">
            <div class="card">
                <div class="card-body">
                    <div class="site-info clearfix">
                        <div class="left">
                            <h3><span id="iosconfigscrip"></span></h3>
                            <h6 id="scripName"></h6>
                        </div>
                    </div>

                    <div class="tools-page" id="scrip_content"></div>
                </div>
            </div>
        </form>

        <div class="button col-md-12 text-center">
            <span class="error-txt" id="error_add_entity" localized="" style="color:red;"></span>
        </div>
    </div>
</div>
