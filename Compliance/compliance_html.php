<!--<div class="wrapper">
    <div class="main-panel">-->
<!-- content starts here  -->
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

// $name = $_SESSION['searchValue'];
// $type = $_SESSION['searchType'];

// $nameStr = $type." - ".$name;
?>
<div class="content white-content troubleShooter" style="overflow: hidden;">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">

                        <div class="bullDropdown leftDropdown">
                            <!-- <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5> -->
                            <!-- <h5>Selection: <span class="site" title="<?php echo url::toText($_SESSION['searchValue']); ?>"><?php echo $nameStr; ?></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3" onclick="showRightPane()">Change?</span>)</h5> -->
                            <!-- <div class="filter-div">
                                <p id="statusmsg">Filter Compliances <b>Item </b> - <span id="notifPriority">All</span> | <b>Category </b> - <span id="notifStatus">All</span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="showComplianceFilters()">Change?</span> )
                            </div> -->
                        </div>
                        <!-- <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('notifyexport', 2); ?>" onclick="export_notification()">Export to Excel</a>
                                </div>
                            </div>
                        </div> -->
                    </div>
                    <?php
                    //   nhRole::checkRoleForPage('compliance');
                    ?>
                    <div class="row clearfix innerPage">
                        <div id="absoLoader" style="display:none">&nbsp;<img src="../assets/img/nanohealLoader.gif" style="width: 71px;"></div>
                        <div class="col-md-3 col-sm-12 pl-0 col-xs-12 lf-rt-br equalHeight  notifContainer" style="display:none">
                            <!-- <h5 style="margin-left:-15px;">
                                Compliance : <span id="activeNotif" style="font-size: 13px; font-weight: bold;"></span>
                            </h5> -->
                            <div class="table-responsive innerLeft">

                                <div class="form">
                                    <div class="sidebar">
                                        <div id="notificationList">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-9 col-sm-12 col-xs-12 equalHeight notifContainer  text-center" style="display:none">
                            <!-- <div id="notifyDtl_filter" class="dataTables_filter">
                                <label class="float-lg-right mr-2">
                                    <input type="text" class="form-control form-control-sm" placeholder="Search records" value="" id="notifSearch" aria-controls="notifyDtl"/>
                                    <button class="bg-white border-0 mr-1 showbtn cursorPointer" onclick="getSearchRecords()"><i class="tim-icons serachIcon icon-zoom-split"></i></button>
                                    <button style="display:none" class="bg-white border-0 mr-1 clearbtn cursorPointer" onclick="clearRecords()" onclick="document.getElementById('notifSearch').value = ''"><i class="tim-icons serachIcon icon-simple-remove"></i></button>
                                </label>
                            </div> -->
                            <input type="hidden" id="notiname">
                            <input type="hidden" id="macname">
                            <input type="hidden" id="eventtime">
                            <input type="hidden" id="custname">
                            <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="complDtl">
                                <thead>
                                    <tr>
                                        <th id="key0" headers="machine" class="">
                                            Machine
                                            <i class="fa fa-caret-down cursorPointer direction" id="machine1" onclick="addActiveSort('machine', 'asc'); complianceDtl_datatable(item='',category='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','machine', 'asc');sortingIconColor('machine1')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id="machine2" onclick="addActiveSort('machine', 'desc'); complianceDtl_datatable(item='',category='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','machine', 'desc');sortingIconColor('machine1')" style="font-size:18px"></i>
                                        </th>
                                        <th id="key1" headers="site" class="">
                                            Site
                                            <i class="fa fa-caret-down cursorPointer direction" id="site1" onclick="addActiveSort('site', 'asc'); complianceDtl_datatable(item='',category='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','site', 'asc');sortingIconColor('site1')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id="site2" onclick="addActiveSort('site', 'desc'); complianceDtl_datatable(item='',category='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','site', 'desc');sortingIconColor('site2')" style="font-size:18px"></i>
                                        </th>
                                        <th id="key2" headers="itemtype" class="">
                                            Item
                                            <i class="fa fa-caret-down cursorPointer direction" id="itemtype1" onclick="addActiveSort('itemtype', 'asc'); complianceDtl_datatable(item='',category='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','itemtype', 'asc');sortingIconColor('itemtype1')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id="itemtype2" onclick="addActiveSort('itemtype', 'desc'); complianceDtl_datatable(item='',category='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','itemtype', 'desc');sortingIconColor('itemtype2')" style="font-size:18px"></i>
                                        </th>
                                        <th id="key3" headers="category" class="">
                                            Category
                                            <i class="fa fa-caret-down cursorPointer direction" id="category1" onclick="addActiveSort('category', 'asc'); complianceDtl_datatable(item='',category='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','category', 'asc');sortingIconColor('category1')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id="category2" onclick="addActiveSort('category', 'desc'); complianceDtl_datatable(item='',category='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','category', 'desc');sortingIconColor('category2')" style="font-size:18px"></i>
                                        </th>
                                        <th id="key4" headers="servertime" class="">
                                            Date & Time
                                            <i class="fa fa-caret-down cursorPointer direction" id="servertime1" onclick="addActiveSort('servertime', 'asc'); complianceDtl_datatable(item='',servertime='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','servertime', 'asc');sortingIconColor('servertime1')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id="servertime2" onclick="addActiveSort('servertime', 'desc'); complianceDtl_datatable(item='',category='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','servertime', 'desc');sortingIconColor('servertime2')" style="font-size:18px"></i>
                                        </th>
                                        <th id="key5" headers="count" class="">
                                            Count
                                            <i class="fa fa-caret-down cursorPointer direction" id="count1" onclick="addActiveSort('count', 'asc'); complianceDtl_datatable(item='',category='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','count', 'asc');sortingIconColor('count1')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id="count2" onclick="addActiveSort('count', 'desc'); complianceDtl_datatable(item='',category='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','count', 'desc');sortingIconColor('count2')" style="font-size:18px"></i>
                                        </th>
                                        <th id="key6" headers="reset" class="">
                                            Reset
                                            <i class="fa fa-caret-down cursorPointer direction" id="reset1" onclick="addActiveSort('reset', 'asc'); complianceDtl_datatable(item='',category='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','reset', 'asc');sortingIconColor('reset1')" style="font-size:18px"></i>
                                            <i class="fa fa-caret-up cursorPointer direction" id="reset1" onclick="addActiveSort('reset', 'desc'); complianceDtl_datatable(item='',category='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','reset', 'desc');sortingIconColor('reset2')" style="font-size:18px"></i>
                                        </th>
                                    </tr>
                                </thead>
                            </table>

                            <div id="largeDataPagination"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--  </div>
</div>-->

<div id="rsc-add-fix" data-qa="Dashboard/Compliance/compliance_html.php" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4>Apply Fix</h4>
        <a href="#" onclick=" rightContainerSlideClose('rsc-add-fix'); return false;" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-add-fix">&times;</a>
    </div>

    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="updateSolution()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Submit</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <div class="card-body">

                <div class="text">Please choose the fix you choose to apply</div>
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab">

                        <div class="accordion" id="accordionExample">
                            <div class="card">
                                <div class="card-header" id="headingOne" style="padding: 5px; cursor: pointer;" data-bs-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    <h5 class="mb-0">
                                        Suggested Fixes <i class="fa fa-angle-down rotate-icon" style="float: right;"></i>
                                    </h5>
                                </div>

                                <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample" style="margin-left: 5%">
                                    <div class="card-body" id="notificationfixList">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="accordion" id="accordionExample">
                    <div class="card">
                        <div class="card-header" id="headingTwo" style="padding: 5px; cursor: pointer;" data-bs-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                            <h5 class="mb-0">
                                Other options <i class="fa fa-angle-down rotate-icon" style="float: right;"></i>
                            </h5>
                        </div>

                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample" style="margin-left: 5%">
                            <div class="card-body">
                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="othersSoln" value="Duplicate">
                                        <span class="form-check-sign"></span>
                                        Duplicate
                                    </label>
                                </div>

                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="othersSoln" value="Escalated">
                                        <span class="form-check-sign"></span>
                                        Escalated
                                    </label>
                                </div>
                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="othersSoln" value="No Remote Solution">
                                        <span class="form-check-sign"></span>
                                        No Remote Solution
                                    </label>
                                </div>

                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="othersSoln" value="Dismissed">
                                        <span class="form-check-sign"></span>
                                        Dismissed
                                    </label>
                                </div>
                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="othersSoln" value="Customer has called Inbound queue">
                                        <span class="form-check-sign"></span>
                                        Customer has called Inbound queue
                                    </label>
                                </div>

                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="othersSoln" value="Follow Up already In Progress/Notified">
                                        <span class="form-check-sign"></span>
                                        Follow Up already In Progress/Notified
                                    </label>
                                </div>
                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="othersSoln" value="No valid contract found">
                                        <span class="form-check-sign"></span>
                                        No valid contract found
                                    </label>
                                </div>

                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="othersSoln" value="Remote Solution Pushed">
                                        <span class="form-check-sign"></span>
                                        Remote Solution Pushed
                                    </label>
                                </div>
                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="othersSoln" value="Out Of Scope">
                                        <span class="form-check-sign"></span>
                                        Out Of Scope
                                    </label>
                                </div>

                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="othersSoln" value="Resolved on the call">
                                        <span class="form-check-sign"></span>
                                        Resolved on the call
                                    </label>
                                </div>
                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="othersSoln" value="Customer not reachable">
                                        <span class="form-check-sign"></span>
                                        Customer not reachable
                                    </label>
                                </div>

                                <div class="form-check form-check-radio">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="radio" name="othersSoln" value="Customer Declined Resolution">
                                        <span class="form-check-sign"></span>
                                        Customer Declined Resolution
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
        <!--<div class="card-footer">-->
        <div class="button col-md-12 text-center" style="bottom:-38px;">
            <button type="button" class="swal2-confirm btn btn-success btn-sm" data-qa="interactiveNotifyPush_2" aria-label="" id="interactiveNotifyPush">Go to Troubleshooter</button>
        </div>
        <!--</div>-->
    </div>

</div>

<div id="rsc-details" class="rightSidenav" data-class="lg-9">
    <div class="rsc-loader no-opacity hide"></div>
    <div class="card-title border-bottom">
        <h4>Compliance Details</h4>
        <a href="javascript:void(0)" data-qa="close-tab-Compliance1" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-details">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-class">

        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <table class="nhl-datatable table table-striped" id="notifyeventDtl" style="width:100% !important">
                            <thead>
                                <tr>
                                    <th class="ne-device" style="width:30%">Device</th>
                                    <th class="ne-st" style="width:30%">Server Time</th>
                                    <th class="ne-ed" style="width:30%">Event Details</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="rsc-add-note" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4>Note</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-add-note">&times;</a>
    </div>

    <div class="btnGroup" id="addNote">
        <div class="icon-circle">
            <div class="toolTip" id="notifysolnPush" onclick="addNoteByName('add');">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Submit</span>
            </div>
        </div>
    </div>
    <div class="btnGroup" style="display: none;" id="editOption">
        <div class="icon-circle">
            <div class="toolTip editOption">
                <i class="tim-icons icon-pencil"></i>
                <span class="tooltiptext">Edit</span>
            </div>
        </div>
    </div>

    <div class="btnGroup" style="display: none;" id="toggleButton">
        <div class="icon-circle iconTick circleGrey" onclick="addNoteByName('edit');">
            <div class="toolTip">
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
        <div class="card">
            <div class="card-body">
                <div class="form-group has-label">
                    <label for="editcsvgname">
                        Note data
                    </label>
                    <input type="text" class="form-control" name="notesText" id="notesText">
                </div>


            </div>
        </div>
    </div>

</div>


<div id="rsc-update-sol" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4>Update Solution</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-update-sol">&times;</a>
    </div>

    <div class="btnGroup" id="addNote">
        <div class="icon-circle">
            <div class="toolTip" onclick="updateSol()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Submit</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label>
                            Compliance Name <em class="error">*</em>
                        </label>
                        <input class="form-control" name="notificationName" type="text" id="notificationName" readonly="" />
                        <!--<span id="advusername-err"></span>-->
                    </div>
                    <div class="form-group has-label">
                        <label>
                            Solution to be pushed<em class="error">*</em>
                        </label>
                        <select class="selectpicker" data-style="btn btn-info" title="Solution to be pushed" data-size="3" id="soln" name="soln">

                        </select>
                        <span id="add_advroleId-err" style="color:red"></span>
                    </div>
                </div>

            </div>
        </form>
    </div>

</div>

<!--Filter Options Div Starts-->
<div id="rsc-add-filter-container" class="rightSidenav leftSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4 id="filter_title">Compliance Filters</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-add-filter-container">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="" name="" onclick="loadComplianceUsingFilters();">
                <i class="tim-icons icon-check-2 filterSubmit"></i>
                <span class="tooltiptext">Apply Filter</span>
            </div>
        </div>
    </div>
    <div class="button col-md-12 text-left"></div>

    <div class="form table-responsive white-content">
        <div class="sidebar">
            <ul class="nav">

                <li class="priorityExpand">
                    <a data-bs-toggle="collapse" href="#priorityExpandItems">
                        <h5>Items<b class="caret"></b></h5>
                    </a>

                    <div class="collapse" id="priorityExpandItems">

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Availability
                                <input type="checkbox" class="form-check-input type_check" value="Availability" id="prio_p1" name="prio_p1" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Maintenance
                                <input type="checkbox" class="form-check-input type_check" value="Maintenance" id="prio_p2" name="prio_p2" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Events
                                <input type="checkbox" class="form-check-input type_check" value="Events" id="prio_p3" name="prio_p3" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Security
                                <input type="checkbox" class="form-check-input type_check" value="Security" id="prio_p4" name="prio_p4" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Resources
                                <input type="checkbox" class="form-check-input type_check" value="Resource" id="prio_p5" name="prio_p5" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>
                    </div>
                </li>
                <li class="statusExpand">
                    <a data-bs-toggle="collapse" href="#statusExpandItems">
                        <h5>Categories<b class="caret"></b></h5>
                    </a>


                    <div class="collapse" id="statusExpandItems">
                        <!-- <div class="form-check checkboxMargin">
                            <label class="form-check-label">All
                                <input class="form-check-input" type="checkbox" class="status_check" checked value="all" id="status_All" name="type_All" onclick="toggleStatus(this);">
                                <span class="form-check-sign"></span>
                            </label>
                        </div> -->

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Ok
                                <input type="checkbox" class="form-check-input status_check" value="Ok" id="status_new" name="status_new" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Warning
                                <input type="checkbox" class="form-check-input status_check" value="Warning" id="status_actioned" name="status_actioned" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Alert
                                <input type="checkbox" class="form-check-input status_check" value="Alert" id="status_completed" name="status_completed" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>
                    </div>
                </li>

                <li>&nbsp;</li>

                <li>
                    <div style="margin-left: 16%;" id='filter_error'></div>
                </li>
            </ul>
        </div>
    </div>

    <div class="button col-md-12 text-center submit">
        <button type="button" class="swal2-confirm btn btn-success btn-sm exportsubmit" style="display:none" aria-label="">Export</button>
    </div>
</div>

<style>
    .showbtn {
        margin-left: 46px;
    }

    .clearbtn {
        margin-left: 46px;
    }
</style>

<!--Filter Options Div Ends-->
