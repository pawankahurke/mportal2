<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

// $name = $_SESSION['searchValue'];
// $type = $_SESSION['searchType'];

// $nameStr = $type." - ".$name;
?><style>
    .circleGrey {
        background-color: rgba(0, 0, 0, 0.20);
    }

    .numb {
        width: 10%;
        position: relative;
        top: -22px;
        left: 77px;
    }

    #tablesExamplesnotification .form-check .text {
        height: 20px;
    }

    .tooltip {
        position: relative;
        display: inline-block;
        border-bottom: 1px dotted black;
    }

    .tooltip .tooltiptext {
        visibility: hidden;
        width: 120px;
        background-color: #555;
        color: #fff;
        text-align: center;
        border-radius: 6px;
        padding: 5px 0;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 50%;
        margin-left: -60px;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .tooltip .tooltiptext::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #555 transparent transparent transparent;
    }

    .tooltip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }

    .leftDropdown {
        left: 0px;
        float: left;
        /* width: 56%; */
    }

    .leftDropdown p {
        font-size: 11px;
        width: 900px;
        height: 20px;
        float: left;
        overflow: inherit;
        text-overflow: ellipsis;
        /*white-space: break-spaces;*/
    }

    .filter-div {
        width: 100%;
    }

    #PATCHSTATUS {
        top: 6px;
        position: relative;
        max-width: 50px;
        display: inline-block;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    #tablesExamples7, #tablesExamples, #tablesExamples2{
        padding-left: 20px;
    }

    #dropdownMenuButton{
        cursor: pointer;
        color: red;
    }

    #statusmsg{
        font-size: 12px;
    }

    .platformExpand>a>h5, .patchTypeExpand>a>h5, .statusExpand>a>h5 {
        margin-top: 15px;
    }

    .platformExpand>a>h5>b, .patchTypeExpand>a>h5, .statusExpand>a>h5>b {
        margin-top: 7px;
    }
</style>
<div class="content white-content mumPage">
    <div class="row mt-2">
        <div class="col-md-12 pr-0 pl-0">
            <div class="card">
                <div class="card-body pt-0" sty>
                    <div class="toolbar">
                        <?php
                        // nhRole::checkRoleForPage('patchmanagement');
                        $res = true; // nhRole::checkModulePrivilege('patchmanagement');
                        if ($res) {
                        ?>
                            <div class="filter-div" style="display:none">
                                <p id="statusmsg"><b>Filter Patches :</b> OS - <span id="OSTYPE">All</span> | ActionType - <span id="ActionType">All</span> | Type - <span id="PATCHTYPE">All</span> | Status - <span title="" id="PATCHSTATUS">All</span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="checkFilter()">Change?</span>)</p>
                            </div>
                            <div class="msg-div" style="display:none">
                                <p>
                                    <b id="msgData" style="color:#fa0f4b">
                                        <!-- Patch Management is not enabled for this site.Enable DART 237 to use Patch Management on this site. -->
                                        <b>
                                </p>
                            </div>
                    </div>
                    <!-- <input type="hidden" id='OSTYPE'>
                        <input type="hidden" id='ActionType'>
                        <input type="hidden" id='PATCHTYPE'>
                        <input type="hidden" id='PATCHSTATUS'> -->
                    <input type="hidden" id="status237">
                    <input type="hidden" id="config_value" value="" name="">
                    <input type="hidden" id="pageValue" value="" name="">
                    <input type="hidden" id="declinepatchpage" value="" name="">
                    <input type="hidden" id="retryPage" value="" name="">
                    <input type="hidden" id="criticalPage" value="" name="">
                    <div id="loader" class="loader" data-qa="loader" style="position: absolute;bottom: 50%;right:50%;">
                        <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                    </div>
                    <table class="nhl-datatable table table-striped" id="patchAllListData">
                        <thead>
                            <tr>
                                <th id="key0" headers="checkbox" class="sorting_desc_disabled sorting_asc_disabled check">
                                    <div class="form-check notifyDtlchkbox">
                                        <label class="form-check-label">
                                            <input class="form-check-input user_check actionchkptch" name="checkboxsel" id="topCheckBox2" type="checkbox" value="value-1">
                                            <span class="form-check-sign">

                                            </span>
                                        </label>
                                    </div>
                                </th>
                                <th id="key1" headers="status" class="">
                                    Patch Status
<!--                                    <i class="fa fa-caret-down cursorPointer direction" id="status1" onclick="addActiveSort('asc', 'status'); mum_patchlistData(wintype = '', nextPage = 1, notifSearch = '', 'status', 'asc'); sortingIconColor('status1')" style="font-size:18px"></i>-->
<!--                                    <i class="fa fa-caret-up cursorPointer direction" id="status2" onclick="addActiveSort('desc', 'status'); mum_patchlistData(wintype = '', nextPage = 1, notifSearch = '', 'status', 'desc'); sortingIconColor('status2')" style="font-size:18px"></i>-->
                                </th>
                                <th id="key2" headers="p.title" class="">
                                    Patch Name
                                    <i class="fa fa-caret-down cursorPointer direction" id="p.title1" onclick="addActiveSort('asc', 'p.title'); mum_patchlistData(wintype = '',nextPage = 1, notifSearch = '', 'p.title', 'asc'); sortingIconColor('p.title1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id="p.title2" onclick="addActiveSort('desc', 'p.title'); mum_patchlistData(wintype = '', nextPage = 1, notifSearch = '', 'p.title', 'desc'); sortingIconColor('p.title2')" style="font-size:18px"></i>
                                </th>
                                <th id="key3" headers="p.type" class="">
                                    Patch Type
                                    <i class="fa fa-caret-down cursorPointer direction" id="p.type1" onclick="addActiveSort('asc', 'p.type'); mum_patchlistData(wintype = '',nextPage = 1, notifSearch = '', 'p.type', 'asc'); sortingIconColor('p.type1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id="p.type2" onclick="addActiveSort('desc', 'p.type'); mum_patchlistData(wintype = '', nextPage = 1, notifSearch = '', 'p.type', 'desc'); sortingIconColor('p.type2')" style="font-size:18px"></i>
                                </th>
                                <th id="key4" headers="p.date" class="">
                                    Release Date
                                    <i class="fa fa-caret-down cursorPointer direction" id="p.date1" onclick="addActiveSort('asc', 'p.date'); mum_patchlistData(wintype = '' ,nextPage = 1, notifSearch = '','p.date', 'asc');sortingIconColor('p.date1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id="p.date2" onclick="addActiveSort('desc', 'p.date'); mum_patchlistData(wintype = '', nextPage = 1, notifSearch = '', 'p.date', 'desc');sortingIconColor('p.date2')" style="font-size:18px"></i>
                                </th>
                                <th id="key5" headers="p.size" class="">
                                    Patch Size
                                    <i class="fa fa-caret-down cursorPointer direction" id="p.size1" onclick="addActiveSort('asc', 'p.size'); mum_patchlistData(wintype = '', nextPage = 1,notifSearch = '', 'p.size', 'asc');sortingIconColor('p.size1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id="p.size2" onclick="addActiveSort('desc', 'p.size'); mum_patchlistData(wintype = '', nextPage = 1, notifSearch = '', 'p.size', 'desc');sortingIconColor('p.size2')" style="font-size:18px"></i>
                                </th>
                                <th id="key6" headers="count" class="">
                                    Machine Count
<!--                                    <i class="fa fa-caret-down cursorPointer direction" id="count1" onclick="addActiveSort('asc', 'count'); mum_patchlistData(wintype = '', nextPage = 1, notifSearch = '', 'count', 'asc');sortingIconColor('count1')" style="font-size:18px"></i>-->
<!--                                    <i class="fa fa-caret-up cursorPointer direction" id="count2" onclick="addActiveSort('desc', 'count'); mum_patchlistData(wintype = '', nextPage = 1, notifSearch = '', 'count', 'desc');sortingIconColor('count2')" style="font-size:18px"></i>-->
                                </th>
                                <th id="key7" headers="kbs" class="">
                                    KBs
                                    <i class="fa fa-caret-down cursorPointer direction" id="kbs1" onclick="addActiveSort('asc', 'p.kbnumber'); mum_patchlistData(wintype = '', nextPage = 1, notifSearch = '', 'p.kbnumber', 'asc');sortingIconColor('kbs1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id="kbs2" onclick="addActiveSort('desc', 'p.kbnumber'); mum_patchlistData(wintype = '', nextPage = 1, notifSearch = '', 'p.kbnumber', 'desc');sortingIconColor('kbs2')" style="font-size:18px"></i>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="tabHeight"></tbody>
                    </table>
                <?php
                        }
                ?>
                <div id="largeDataPagination"></div>
                <!--Retry Patch Data Table-->
                <table class="nhl-datatable table table-striped" id="retrypatchListData" style="display:none">
                    <thead>
                        <tr>
                            <th class="sorting_desc_disabled sorting_asc_disabled check">
                                <div class="form-check notifyDtlchkbox">
                                    <label class="form-check-label">
                                        <input class="form-check-input user_check actionchkptch" name="checkboxsel" id="topCheckBox" type="checkbox" value="value-1">
                                        <span class="form-check-sign">

                                        </span>
                                    </label>
                                </div>
                            </th>
                            <th>Update</th>
                            <th>Machine</th>
                            <th>Status</th>
                            <th>Error</th>
                            <th>Type</th>
                        </tr>
                    </thead>

                    <tbody class="tabHeight"></tbody>
                </table>

                <!--Update Critical Install-->
                <table class="nhl-datatable table table-striped" id="criticalupdatepatchListData" style="display:none">
                    <thead>
                        <tr>
                            <th class="sorting_desc_disabled sorting_asc_disabled check"></th>
                            <th>Current Status</th>
                            <th>Update</th>
                            <th>Status</th>
                            <th>Type</th>
                        </tr>
                    </thead>

                    <tbody class="tabHeight"></tbody>
                </table>

                <!--Remove Patch Data Table-->
                <table class="nhl-datatable table table-striped" id="RemovepatchListData" style="display:none">
                    <thead>
                        <tr>
                            <th class="sorting_desc_disabled sorting_asc_disabled check"></th>
                            <th>Patch Name</th>
                            <th>Patch Status</th>
                            <th>Patch Type</th>
                            <th>Date</th>
                            <th>Patch Size</th>
                            <!--<th>Machine Count</th>-->
                        </tr>
                    </thead>

                    <tbody class="tabHeight"></tbody>
                </table>

                <!--Decline Patch Data Table-->
                <table class="nhl-datatable table table-striped" id="DeclinepatchListData" style="display:none">
                    <thead>
                        <tr>
                            <th class="sorting_desc_disabled sorting_asc_disabled check"></th>
                            <th>Update</th>
                            <!--<th>Machine</th>-->
                            <th>Patch Size</th>
                            <th>Type</th>
                        </tr>
                    </thead>

                    <tbody class="tabHeight"></tbody>
                </table>

                </div>

                <div id="statusDIV"></div>
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>

<div id="kbs-add-container" class="rightSidenav" data-class="lg-9">
    <div class="card-title border-bottom">
        <h4>KBs Details</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="kbs-add-container">&times;</a>
    </div>

    <div class="form table-responsive white-content">
        <form id="">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label>
                            Server Files*
                        </label>
                    </div>

                    <div class="form-group has-label serverfile_data">
                        <label><b>Server File</b></label><br>
                        <span id="serVerFile" name="serVerFile" style="color: #525f7f !important;"></span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="show-status-container" class="rightSidenav" data-class="lg-9">
    <div class="card-title border-bottom">
        <h4>Machine Details</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="show-status-container">&times;</a>
    </div>

    <div class="form table-responsive white-content">
        <form id="">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label>
                            Status
                        </label>
                    </div>

                    <div class="form-group has-label patchstatus_data">
                        <table class='nhl-datatable table table-striped' id='statusData'>
                            <thead>
                                <tr>
                                    <th>Machine Name</th>
                                    <th>Status</th>
                                    <th>Detected Date</th>
                                    <th>Download Date</th>
                                    <th>Install Date</th>
                                    <th>Error</th>
                                </tr>
                            </thead>
                            <tbody class='tabHeight'></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!--Patch Management Filters-->
<div id="rsc-add-container34" class="rightSidenav leftSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4 id="filter_title">Patch Management Filters</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-add-container34">&times;</a>
    </div>
    <!-- <div class="btnGroup">
            <div class="icon-circle">
                <div class="toolTip">
                    <i class="tim-icons icon-check-2 filterSubmit"></i>
                    <span class="tooltiptext">Filter</span>
                </div>
            </div>
        </div> -->
    <div class="form table-responsive white-content">
        <div class="sidebar" id="PatchValidation">
            <form method="post" name="PatchValidationForm" id="PatchValidationForm">
                <ul class="nav">


                    <li class="platformExpand">
                        <a data-bs-toggle="collapse" href="#tablesExamples7">
                            <h5>Platform<b class="caret"></b></h5>
                        </a>

                        <div class="collapse" id="tablesExamples7">

                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input actionchk platform" name="platformCheck" id="windowsCheckAll" type="checkbox" checked value="all">All
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>

                            <div class="form-check">
                                <label class="form-check-label">Windows 10
                                    <input value="Windows 10" class="form-check-input actionchk platform pltmwin10" name="1" type="checkbox">
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>

                            <div class="form-check">
                                <label class="form-check-label">Windows 7
                                    <input value="Windows 7" class="form-check-input actionchk platform pltmwin7" name="1" type="checkbox">
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>

                            <div class="form-check">
                                <label class="form-check-label">Windows 8
                                    <input value="Windows 8" class="form-check-input actionchk platform pltmwin8" name="1" type="checkbox">
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>

                            <div class="form-check">
                                <label class="form-check-label">Windows 8.1
                                    <input value="Windows 8.1" class="form-check-input actionchk platform pltmwin81" name="1" type="checkbox">
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>

                            <div class="form-check">
                                <label class="form-check-label">Others
                                    <input value="Others" class="form-check-input actionchk platform others" name="1" type="checkbox">
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>

                        </div>
                    </li>

                    <li class="patchTypeExpand">
                        <a data-bs-toggle="collapse" href="#tablesExamples">
                            <h5>Patch Type<b class="caret"></b></h5>
                        </a>

                        <div class="collapse" id="tablesExamples">

                            <div class="form-check">
                                <label class="form-check-label">
                                    <input class="form-check-input actionchk patchtype" name="patchtypeCheck" checked id="patchtypeCheck" type="checkbox" value="all">All
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>

                            <div class="form-check">
<!--                                <label class="form-check-label">Undefined-->
                                <label class="form-check-label">Other
                                    <input value="0" class="form-check-input actionchk patchtype ptchty_undef" name="1" type="checkbox">
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>

                            <div class="form-check">
                                <label class="form-check-label">Update
                                    <input value="1" class="form-check-input actionchk patchtype ptchty_update" name="1" type="checkbox">
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>

                            <div class="form-check">
                                <label class="form-check-label">Roll Up
                                    <input value="3" class="form-check-input actionchk patchtype ptchty_rol" name="1" type="checkbox">
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>

                            <div class="form-check">
                                <label class="form-check-label">Security
                                    <input value="4" class="form-check-input actionchk patchtype ptchty_sec" name="1" type="checkbox">
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>

                            <div class="form-check">
                                <label class="form-check-label">Critical
                                    <input value="5" class="form-check-input actionchk patchtype ptchty_criti" name="1" type="checkbox">
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>
                        </div>
                    </li>

<!--                    <li>-->
<!--                        <a data-bs-toggle="collapse" href="#tablesExamples1">-->
<!--                            <p>Action Type<b class="caret"></b></p>-->
<!--                        </a>-->
<!---->
<!--                        <div class="collapse" id="tablesExamples1">-->
<!---->
<!--                            <div class="form-check form-check-radio">-->
<!--                                <label class="form-check-label">-->
<!--                                    <input class="form-check-input actiontype actionchk" name="actiontype" checked id="statusAll" value="All" type="radio">All-->
<!--                                    <span class="form-check-sign"></span>-->
<!--                                </label>-->
<!--                            </div>-->
<!---->
<!--                            <div class="form-check form-check-radio">-->
<!--                                <label class="form-check-label">-->
<!--                                    <input class="form-check-input actiontype actionchk" name="actiontype" id="action1" value="approved" type="radio">Approved-->
<!--                                    <span class="form-check-sign"></span>-->
<!--                                </label>-->
<!--                            </div>-->
<!---->
<!--                            <div class="form-check form-check-radio">-->
<!--                                <label class="form-check-label">-->
<!--                                    <input class="form-check-input actiontype actionchk " name="actiontype" id="action2" value="declined" type="radio">Declined-->
<!--                                    <span class="form-check-sign"></span>-->
<!--                                </label>-->
<!--                            </div>-->
<!---->
<!--                            <div class="form-check form-check-radio">-->
<!--                                <label class="form-check-label">-->
<!--                                    <input class="form-check-input actiontype actionchk" name="actiontype" id="action3" value="critical" type="radio">Critical-->
<!--                                    <span class="form-check-sign"></span>-->
<!--                                </label>-->
<!--                            </div>-->
<!---->
<!--                                                       <div class="form-check form-check-radio">-->
<!--                                <label class="form-check-label">-->
<!--                                    <input class="form-check-input actiontype actionchk " name="actiontype" id="action4" value="removed" type="radio">Removed-->
<!--                                    <span class="form-check-sign"></span>-->
<!--                                </label>-->
<!--                            </div>-->
<!--                        </div>-->
<!--                    </li>-->


                    <li>
                        <a data-bs-toggle="collapse" href="#tablesExamples2">
                            <h5>Status<b class="caret"></b></h5>
                        </a>

                        <div class="collapse" id="tablesExamples2">
                            <div class="row">
                                <div class="col-md-6">

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" checked name="patchstatus" id="statusCheckAll" type="checkbox" value="all">All
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="detected">Detected
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="downloaded">Downloaded
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="installed">Installed
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="pendinginstall">Pending Install
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="declined">Declined
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="alreadyinstalled">Already Installed
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="pendinguninstall">Pending UnInstall
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="scheduledinstall">Scheduled Install
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="scheduledunInstall">Scheduled UnInstall
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="disable">Disable
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="uninstall">unInstall
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="pendingdownload">Pending Download
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="pendingreboot">Pending Reboot
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="potentialinstallfailure">Potential Install Failure
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="superseded">Superseded
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input statuscheck actionchk" name="patchstatus" type="checkbox" value="waiting">Waiting
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>

                    <li>
                        <h5 style="margin-left: 5%;">Release Date</h5>

                        <div class="col-md-6">
                            <input type="text" class="form-control frompatch datetimepicker" id="datefrom">
                        </div>

                        <div class="col-md-6">
                            <input type="text" class="form-control topatch datetimepicker" id="dateto">
                        </div>
                    </li>

                    <li>&nbsp;</li>

                    <li>
                        <div style="margin-left: 16%;" id='filter_error'></div>
                    </li>
                </ul>
            </form>
        </div>
    </div>

    <div class="button col-md-12 text-center submit">
        <button type="button" class="swal2-confirm btn btn-success btn-sm filterSubmit" aria-label="">Filter</button>
        <button type="button" class="swal2-confirm btn btn-success btn-sm exportsubmit" style="display:none" aria-label="">Export</button>
    </div>
</div>
</div>

<!--Patch Settings-->
<div id="settings-add-container" class="rightSidenav leftSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Setting Details</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="settings-add-container">&times;</a>
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
        <div class="icon-circle circleGrey">
            <div class="toolTip" id="" name="">
                <i class="tim-icons icon-check-2 settingsubmit"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>

        <div class="icon-circle closecross" id="toggleEdit">
            <div class="toolTip">
                <i class="tim-icons icon-simple-remove"></i>
                <span class="tooltiptext">Toggle edit mode</span>
            </div>
        </div>
    </div>

    <div class="button col-md-12 text-left"></div>

    <div class="form table-responsive white-content">
        <div class="sidebar">
            <ul class="nav">

            </ul>
        </div>
    </div>
</div>

<!--Approve or Decline Patches-->
<!--<div id="takeaction-add-container" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Take Action for patches</h4>
        <a href="javascript:void(0)" class="closebtn kbs-container-close" data-bs-target="takeaction-add-container">&times;</a>
    </div>

     <div class="btnGroup" id="toggleButton">
        <div class="icon-circle">
            <div class="toolTip" id="" name="">
                <i class="tim-icons icon-check-2 actionTaken"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form id="">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label>
                             <div class="" id="" style="padding-left: 24px;">
                                    <div class="form-check form-check-radio" id="approveupdate">
                                        <label class="form-check-label">
                                            <input class="form-check-input" id="apprvAction" name="tackeacn" type="radio"  value="approve">Approve Update
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div> 

                                    <div class="form-check form-check-radio" id="declineupdate">
                                        <label class="form-check-label">
                                            <input class="form-check-input" id="declAction" name="tackeacn" type="radio"  value="decline">Decline Update
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>
                                </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>-->

<!--Approved Patched Configuration-->
<div id="settings-appr-container" class="rightSidenav leftSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Approved Patches Setting Details</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="settings-appr-container">&times;</a>
    </div>

    <div class="btnGroup" id="editApprOption" style="display: block;">
        <div class="icon-circle">
            <div class="toolTip" id="" name="">
                <i class="tim-icons icon-check-2 apprsettingsubmit"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <!--    <div class="btnGroup" id="toggleApprButton">
        <div class="icon-circle circleGrey">
            <div class="toolTip" id="" name="">
                <i class="tim-icons icon-check-2 apprsettingsubmit"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>-->

    <!--        <div class="icon-circle closecross" id="toggleApprEdit">
            <div class="toolTip">
                <i class="tim-icons icon-simple-remove"></i>
                <span class="tooltiptext">Toggle edit mode</span>
            </div>
        </div>-->
    <!--</div>-->

    <div class="button col-md-12 text-left"></div>

    <div class="form table-responsive white-content">
        <div class="sidebar">
            <div align="center" class="margin-top100">
                <div id="loader" class="loader" data-qa="loader" style="display: none">
                    <img src="../assets/img/loader.gif" />
                    <h5>Please wait..!</h5>
                </div>
            </div>
            <ul class="nav" id="hideContentsField">
                <li>
                    <a data-bs-toggle="collapse" href="#tablesExamples3">
                        <p>Advanced Configurations<b class="caret"></b></p>
                    </a>

                    <div class="collapse" id="tablesExamples3">
                        <a data-bs-toggle="collapse" href="#tablesExamplesshedule">
                            <p style="font-size: 12px;">Schedule Options<b class="caret"></b></p>
                        </a>

                        <div class="collapse" id="tablesExamplesshedule" style="padding-left: 24px;">
                            <div class="form-radio">
                                <div class="row">
                                    <label class="form-check-label" style="margin-left: 13px;">
                                        Delay Operation by days
                                    </label>

                                    <div class="col-md-4">
                                        <input type="text" class="form-control schleedelayappr" style="margin-left: 10px; margin-top: -2px;" value="">
                                        <span class="form-check-sign"></span>
                                    </div>
                                </div>
                            </div>

                            <div>&nbsp;</div>

                            <div class="form-radio">
                                <div class="row">
                                    <div class="col-md-8">Schedule to use for scheduled options </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">Hour:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker schlehourappr" data-style="btn btn-info" data-size="7">
                                            <option value="0">00</option>
                                            <option value="1">01</option>
                                            <option value="2">02</option>
                                            <option value="3">03</option>
                                            <option value="4">04</option>
                                            <option value="5">05</option>
                                            <option value="6">06</option>
                                            <option value="7">07</option>
                                            <option value="8">08</option>
                                            <option value="9">09</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                            <option value="13">13</option>
                                            <option value="14">14</option>
                                            <option value="15">15</option>
                                            <option value="16">16</option>
                                            <option value="17">17</option>
                                            <option value="18">18</option>
                                            <option value="19">19</option>
                                            <option value="20">20</option>
                                            <option value="21">21</option>
                                            <option value="22">22</option>
                                            <option value="23">23</option>
                                            <option value="24" selected>Any</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">Minutes:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker schleminappr" data-style="btn btn-info" data-size="7">
                                            <option value="0">00</option>
                                            <option value="1">01</option>
                                            <option value="2">02</option>
                                            <option value="3">03</option>
                                            <option value="4">04</option>
                                            <option value="5">05</option>
                                            <option value="6">06</option>
                                            <option value="7">07</option>
                                            <option value="8">08</option>
                                            <option value="9">09</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                            <option value="13">13</option>
                                            <option value="14">14</option>
                                            <option value="15">15</option>
                                            <option value="16">16</option>
                                            <option value="17">17</option>
                                            <option value="18">18</option>
                                            <option value="19">19</option>
                                            <option value="20">20</option>
                                            <option value="21">21</option>
                                            <option value="22">22</option>
                                            <option value="23">23</option>
                                            <option value="24">24</option>
                                            <option value="25">25</option>
                                            <option value="26">26</option>
                                            <option value="27">27</option>
                                            <option value="28">28</option>
                                            <option value="29">29</option>
                                            <option value="30">30</option>
                                            <option value="31">31</option>
                                            <option value="32">32</option>
                                            <option value="33">33</option>
                                            <option value="34">34</option>
                                            <option value="35">35</option>
                                            <option value="36">36</option>
                                            <option value="37">37</option>
                                            <option value="38">38</option>
                                            <option value="39">39</option>
                                            <option value="40">40</option>
                                            <option value="41">41</option>
                                            <option value="42">42</option>
                                            <option value="43">43</option>
                                            <option value="44">44</option>
                                            <option value="45">45</option>
                                            <option value="46">46</option>
                                            <option value="47">47</option>
                                            <option value="48">48</option>
                                            <option value="49">49</option>
                                            <option value="50">50</option>
                                            <option value="51">51</option>
                                            <option value="52">52</option>
                                            <option value="53">53</option>
                                            <option value="54">54</option>
                                            <option value="55">55</option>
                                            <option value="56">56</option>
                                            <option value="57">57</option>
                                            <option value="58">58</option>
                                            <option value="59">59</option>
                                            <option value="60" selected>Any</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">Weekday:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker schleweekappr" data-style="btn btn-info" data-size="7">
                                            <option value="0" selected>Any</option>
                                            <option value="1">Monday</option>
                                            <option value="2">Tuesday</option>
                                            <option value="3">Wednesday</option>
                                            <option value="4">Thursday</option>
                                            <option value="5">Friday</option>
                                            <option value="6">Saturday</option>
                                            <option value="7">Sunday</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">Month:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker schedlemonappr" data-style="btn btn-info" data-size="7">
                                            <option value="0" selected>Any</option>
                                            <option value="1">January</option>
                                            <option value="2">February</option>
                                            <option value="3">March</option>
                                            <option value="4">April</option>
                                            <option value="5">May</option>
                                            <option value="6">June</option>
                                            <option value="7">July</option>
                                            <option value="8">August</option>
                                            <option value="9">September</option>
                                            <option value="10">October</option>
                                            <option value="11">November</option>
                                            <option value="12">December</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">Day:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker schedledayappr" data-style="btn btn-info" data-size="7">
                                            <option value="0" selected>Any</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                            <option value="6">6</option>
                                            <option value="7">7</option>
                                            <option value="8">8</option>
                                            <option value="9">9</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                            <option value="13">13</option>
                                            <option value="14">14</option>
                                            <option value="15">15</option>
                                            <option value="16">16</option>
                                            <option value="17">17</option>
                                            <option value="18">18</option>
                                            <option value="19">19</option>
                                            <option value="20">20</option>
                                            <option value="21">21</option>
                                            <option value="22">22</option>
                                            <option value="23">23</option>
                                            <option value="24">24</option>
                                            <option value="25">25</option>
                                            <option value="26">26</option>
                                            <option value="27">27</option>
                                            <option value="28">28</option>
                                            <option value="29">29</option>
                                            <option value="30">30</option>
                                            <option value="31">31</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-radio">
                                <div class="row">
                                    <label class="form-check-label" style="margin-left: 15px;">
                                        Random Delay in minutes
                                    </label>

                                    <div class="col-md-4">
                                        <input type="text" class="form-control schlerandomdelayappr" value="">
                                        <span class="form-check-sign"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-radio">
                                <label class="form-check-label">
                                    Action on Missed Schedule
                                </label>

                                <div class="form-check form-check-radio">
                                    <div class="form-check form-check-radio">
                                        <label class="form-check-label">
                                            <input class="form-check-input scheduletypeasapappr runasapappr" name="schleradioappr" type="radio" value="1" checked>Run as soon as Possible
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check form-check-radio">
                                        <label class="form-check-label">
                                            <input class="form-check-input scheduletypetimingappr scheduletypeappr" name="schleradioappr" type="radio" value="2">Run at schedule time
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a data-bs-toggle="collapse" href="#tablesExamplesnotification">
                            <p style="font-size: 12px;">Notification Options<b class="caret"></b></p>
                        </a>

                        <div class="collapse" id="tablesExamplesnotification" style="padding-left: 24px;">
                            <div class="form-radio">
                                <label class="form-check-label">
                                    Notification Options
                                </label>

                                <div class="form-check">
                                    <div class="form-check text">
                                        <label class="form-check-label">
                                            <input class="form-check-input notifyminsappr" name="notifyminsappr" type="checkbox" value="1">Notify &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; minutes before action
                                            <span class="form-check-sign"></span>
                                        </label>

                                        <input type="text" style="width: 48px; margin-left: 21%; margin-top: -20px;" class="form-control notinumbappr numbappr" value="15" maxlength="2">
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input notifremndappr" name="notifremndappr" type="checkbox" value="1">Reminder user to leave system on
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input notifprevsysappr" name="notifprevsysappr" type="checkbox" value="1">Prevent system shutdown
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input notifschdlsopappr" name="notifschdlsopappr" type="checkbox" value="1">Schedule notification
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div>&nbsp;</div>

                            <div class="form-radio">
                                <div class="row">
                                    <div class="col-md-8">Schedule to use for scheduled options </div>
                                </div>
                            </div>

                            <div class="form-check form-check-radio">
                                <div class="row">
                                    <div class="col-md-3">Hour:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker notifhourappr" data-style="btn btn-info" data-size="7">
                                            <option value="0">00</option>
                                            <option value="1">01</option>
                                            <option value="2">02</option>
                                            <option value="3">03</option>
                                            <option value="4">04</option>
                                            <option value="5">05</option>
                                            <option value="6">06</option>
                                            <option value="7">07</option>
                                            <option value="8">08</option>
                                            <option value="9">09</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                            <option value="13">13</option>
                                            <option value="14">14</option>
                                            <option value="15">15</option>
                                            <option value="16">16</option>
                                            <option value="17">17</option>
                                            <option value="18">18</option>
                                            <option value="19">19</option>
                                            <option value="20">20</option>
                                            <option value="21">21</option>
                                            <option value="22">22</option>
                                            <option value="23">23</option>
                                            <option value="24" selected>Any</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">Minutes:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker notifminappr" data-style="btn btn-info" data-size="7">
                                            <option value="0">00</option>
                                            <option value="1">01</option>
                                            <option value="2">02</option>
                                            <option value="3">03</option>
                                            <option value="4">04</option>
                                            <option value="5">05</option>
                                            <option value="6">06</option>
                                            <option value="7">07</option>
                                            <option value="8">08</option>
                                            <option value="9">09</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                            <option value="13">13</option>
                                            <option value="14">14</option>
                                            <option value="15">15</option>
                                            <option value="16">16</option>
                                            <option value="17">17</option>
                                            <option value="18">18</option>
                                            <option value="19">19</option>
                                            <option value="20">20</option>
                                            <option value="21">21</option>
                                            <option value="22">22</option>
                                            <option value="23">23</option>
                                            <option value="24">24</option>
                                            <option value="25">25</option>
                                            <option value="26">26</option>
                                            <option value="27">27</option>
                                            <option value="28">28</option>
                                            <option value="29">29</option>
                                            <option value="30">30</option>
                                            <option value="31">31</option>
                                            <option value="32">32</option>
                                            <option value="33">33</option>
                                            <option value="34">34</option>
                                            <option value="35">35</option>
                                            <option value="36">36</option>
                                            <option value="37">37</option>
                                            <option value="38">38</option>
                                            <option value="39">39</option>
                                            <option value="40">40</option>
                                            <option value="41">41</option>
                                            <option value="42">42</option>
                                            <option value="43">43</option>
                                            <option value="44">44</option>
                                            <option value="45">45</option>
                                            <option value="46">46</option>
                                            <option value="47">47</option>
                                            <option value="48">48</option>
                                            <option value="49">49</option>
                                            <option value="50">50</option>
                                            <option value="51">51</option>
                                            <option value="52">52</option>
                                            <option value="53">53</option>
                                            <option value="54">54</option>
                                            <option value="55">55</option>
                                            <option value="56">56</option>
                                            <option value="57">57</option>
                                            <option value="58">58</option>
                                            <option value="59">59</option>
                                            <option value="60" selected>Any</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">Weekday:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker notifwklyappr" data-style="btn btn-info" data-size="7">
                                            <option value="0" selected>Any</option>
                                            <option value="1">Monday</option>
                                            <option value="2">Tuesday</option>
                                            <option value="3">Wednesday</option>
                                            <option value="4">Thursday</option>
                                            <option value="5">Friday</option>
                                            <option value="6">Saturday</option>
                                            <option value="7">Sunday</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">Month:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker notifmonappr" data-style="btn btn-info" data-size="7">
                                            <option value="0" selected>Any</option>
                                            <option value="1">January</option>
                                            <option value="2">February</option>
                                            <option value="3">March</option>
                                            <option value="4">April</option>
                                            <option value="5">May</option>
                                            <option value="6">June</option>
                                            <option value="7">July</option>
                                            <option value="8">August</option>
                                            <option value="9">September</option>
                                            <option value="10">October</option>
                                            <option value="11">November</option>
                                            <option value="12">December</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">Day:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker notifdayappr" data-style="btn btn-info" data-size="7">
                                            <option value="0" selected>Any</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                            <option value="6">6</option>
                                            <option value="7">7</option>
                                            <option value="8">8</option>
                                            <option value="9">9</option>
                                            <option value="10">10</option>
                                            <option value="11">11</option>
                                            <option value="12">12</option>
                                            <option value="13">13</option>
                                            <option value="14">14</option>
                                            <option value="15">15</option>
                                            <option value="16">16</option>
                                            <option value="17">17</option>
                                            <option value="18">18</option>
                                            <option value="19">19</option>
                                            <option value="20">20</option>
                                            <option value="21">21</option>
                                            <option value="22">22</option>
                                            <option value="23">23</option>
                                            <option value="24">24</option>
                                            <option value="25">25</option>
                                            <option value="26">26</option>
                                            <option value="27">27</option>
                                            <option value="28">28</option>
                                            <option value="29">29</option>
                                            <option value="30">30</option>
                                            <option value="31">31</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-check">
                                <div class="row">
                                    <label style="margin-left: 15px;">
                                        Random Delay in minutes
                                    </label>

                                    <div class="col-md-4">
                                        <input type="text" class="form-control notifrandomdelayappr" value="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-radio">
                                <label class="form-check-label">
                                    Action on Missed Schedule
                                </label>

                                <div class="form-check form-check-radio">
                                    <div class="form-check form-check-radio">
                                        <label class="form-check-label">
                                            <input class="form-check-input runasapappr notifasapappr" name="notifradioappr" type="radio" value="1" checked>Run as soon as Possible
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check form-check-radio">
                                        <label class="form-check-label">
                                            <input class="form-check-input notiftimingappr" name="notifradioappr" type="radio" value="2">Run at schedule time
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div>&nbsp;</div>

                            <div class="form-radio">
                                <label class="form-check-label">
                                    Notification Text
                                    <textarea rows="4" cols="57" id="notif_textappr" style="width: 92%;" class="form-control"></textarea>
                                </label>
                            </div>
                        </div>
                    </div>
                </li>

                <li>&nbsp;</li>

                <li>
                    <div style="margin-left: 16%;" id='filter_errorappr'></div>
                </li>
            </ul>
        </div>
    </div>
</div>

<!--Machine Configuration-->
<div id="machConfig-add-container" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Machine Configuration</h4>
        <a href="javascript:void(0)" class="closebtn kbs-container-close border-0" data-bs-target="machConfig-add-container">&times;</a>
    </div>

    <div class="btnGroup" id="toggleButton">
        <div class="icon-circle">
            <div class="toolTip" id="" name="">
                <i class="tim-icons icon-check-2 machConfigSave" onclick="MachineConfigsettings()"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form id="">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <div class="" id="" style="padding-left: 24px;">
                            <span>Category:</span>
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="allsel" type="radio" checked="checked" value="1">All
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>

                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="allsel" type="radio" value="2">Site
                                    <span class="form-check-sign"></span>
                                    <input class="form-control" type="text" id="siteselected">
                                </label>
                            </div>
                        </div>


                        <div class="" id="" style="padding-left: 24px;">
                            <span>Source of Updates:</span>
                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="sourceaction" type="radio" checked="checked" value="1"> Microsoft Update Server
                                    <span class="form-check-sign"></span>
                                </label>
                            </div>

                            <div class="form-check form-check-radio">
                                <label class="form-check-label">
                                    <input class="form-check-input" name="sourceaction" type="radio" value="2">SUS server:
                                    <span class="form-check-sign"></span>
                                    <input class="form-control" type="text" id="susServer">
                                </label>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
    td p {
        font-weight: 300;
    }

    td {
        font-weight: 300;
    }

    .showbtn {
        margin-left: 119px;
    }

    .clearbtn {
        margin-left: 119px;
    }

    .leftSidenav .form {
        height: 86%;
    }
</style>
<script>
  $('#notifSearch').attr('placeholder', 'Search patch name');
</script>
