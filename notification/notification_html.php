<!--<div class="wrapper">
    <div class="main-panel">-->
<!-- content starts here  -->
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();
?>
<div class="content white-content troubleShooter" style="overflow: hidden;">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <!-- <div class="bullDropdown leftDropdown">
                            <div class="filter-div">
                                <p id="statusmsg">Filter Notifications <b>Priority </b> - <span id="notifPriority">All</span> | <b>Status </b> - <span id="notifStatus">All</span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" onclick="showComplianceFilters()">Change?</span> )
                            </div>
                        </div> -->

                    </div>
                    <?php
                    //   nhRole::checkRoleForPage('notification');
                    ?>
                    <div id="NotificationErr" style="display:none">
                        <span>No Notifications Found</span>
                    </div>
                    <div class="row clearfix innerPage">
                        <div id="absoLoader" style="display:none">&nbsp;<img src="../assets/img/nanohealLoader.gif" style="width: 71px;"></div>
                        <div class="col-md-3 pl-0 col-sm-12 col-xs-12 lf-rt-br equalHeight  notifContainer" style="display:none">
                            <!-- <h5 style="margin-left:-15px;">
                                Notification : <span id="activeNotif" style="font-size: 13px; font-weight: bold;"></span>
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
                            <input type="hidden" id="selected">
                            <input type="hidden" id="selectedMachineName">
                            <input type="hidden" id="incrementCount">
                            <input type="hidden" id="decrementCount">
                            <input type="hidden" id="eventStartDate">
                            <input type="hidden" id="eventEndDate">
                            <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="notifyDtl">
                                <thead>
                                    <tr>
                                        <th id="key0" headers="checkbox" class="sorting_desc_disabled sorting_asc_disabled sorting_disabled check">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input class="form-check-input" id="topCheckBox" type="checkbox">
                                                    <span class="form-check-sign"></span>
                                                </label>
                                            </div>
                                        </th>
                                        <th id="key1" headers="machine">
                                            Machine
                                            <div class="SortNotif" id="sortKey1"></div>
                                            <!-- <i class="fa fa-caret-down cursorPointer direction" id = "notiMachine1" onclick = "notificationDtl_datatable(priority='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','machine', 'asc');sortingIconColor('notiMachine1')" style="font-size:18px"></i>
                                                <i class="fa fa-caret-up cursorPointer direction" id = "notiMachine2" onclick = "notificationDtl_datatable(priority='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','machine', 'desc');sortingIconColor('notiMachine2')" style="font-size:18px"></i> -->
                                        </th>
                                        <th id="key2" headers="ndate" class="">
                                            Date
                                            <div class="SortNotif" id="sortKey2"></div>
                                            <!-- <i class="fa fa-caret-down cursorPointer direction" id = "ndate1" onclick = "notificationDtl_datatable(priority='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','ndate', 'asc');sortingIconColor('ndate1')" style="font-size:18px"></i>
                                                <i class="fa fa-caret-up cursorPointer direction" id = "ndate2" onclick = "notificationDtl_datatable(priority='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','ndate', 'desc');sortingIconColor('ndate2')" style="font-size:18px"></i> -->
                                        </th>
                                        <th id="key3" headers="count" class="">
                                            Count
                                            <div class="SortNotif" id="sortKey3"></div>
                                            <!-- <i class="fa fa-caret-down cursorPointer direction" id = "count1" onclick = "notificationDtl_datatable(priority='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','count', 'asc');sortingIconColor('count1')" style="font-size:18px"></i>
                                                <i class="fa fa-caret-up cursorPointer direction" id = "count2" onclick = "notificationDtl_datatable(priority='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','count', 'desc');sortingIconColor('count2')" style="font-size:18px"></i> -->
                                        </th>
                                        <th id="key4" headers="nocStatus" class="">
                                            Status
                                            <div class="SortNotif" id="sortKey4"></div>
                                            <!-- <i class="fa fa-caret-down cursorPointer direction" id = "nocStatus1" onclick = "notificationDtl_datatable(priority='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','nocStatus', 'asc');sortingIconColor('nocStatus1')" style="font-size:18px"></i>
                                                <i class="fa fa-caret-up cursorPointer direction" id = "nocStatus2" onclick = "notificationDtl_datatable(priority='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','nocStatus', 'desc');sortingIconColor('nocStatus2')" style="font-size:18px"></i> -->
                                        </th>
                                        <th id="key5" headers="note" class="">
                                            Notes
                                            <div class="SortNotif" id="sortKey5"></div>
                                            <!-- <i class="fa fa-caret-down cursorPointer direction" id = "note1" onclick = "notificationDtl_datatable(priority='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','note', 'asc');sortingIconColor('note1')" style="font-size:18px"></i>
                                                <i class="fa fa-caret-up cursorPointer direction" id = "note2" onclick = "notificationDtl_datatable(priority='',name = '', reflag = '', status = '', nextPage = 1, notifSearch = '','note', 'desc');sortingIconColor('note2')" style="font-size:18px"></i> -->
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


<div id="rsc-add-fix" class="rightSidenav" data-class="md-6">
    <div class="card-title border-bottom">
        <h4>Apply Fix</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-add-fix">&times;</a>
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
            <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" data-qa="interactiveNotifyPush_1" id="interactiveNotifyPush">Go to Troubleshooter</button>
        </div>
        <!--</div>-->
    </div>

</div>

<div id="rsc-details" class="rightSidenav" data-class="lg-9">
    <div class="rsc-loader no-opacity hide"></div>
    <div class="card-title border-bottom">
        <h4 data-qa="Notification-Details-tabHeader">Notification Details</h4>
        <a href="#" class="closebtn rightslide-container-close border-0" data-qa="close-tab-NotificationDetails" data-bs-target="rsc-details">&times;</a> 
    </div>
    <div class="btnGroup">
        <div class="icon-class">

        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <div id="loader" class="loader" data-qa="loader" style="position: absolute;bottom: 50%;right:50%;">&nbsp;<img src="../assets/img/nanohealLoader.gif" style="width: 71px;"></div>
                    <table class="nhl-datatable  table table-striped w-100" id="notifyeventDtl">
                        <thead>
                            <tr>
                                <th class="ne-device" style="width:10%">Device</th>
                                <th class="ne-st" style="width:10%">Client Time</th>
                                <th class="ne-st" style="width:10%">Server Time</th>
                                <th style="width:30%"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Show Event Analyzer -->
<div id="rsc-event-details" class="rightSidenav" data-class="lg-9">
    <div class="rsc-loader no-opacity hide"></div>
    <div class="card-title border-bottom">
        <h4>Device:<b><span id="deviceName"></span></b><span style="position:absolute;right:27%;"><i class="tim-icons icon-watch-time"></i>Time range:<input type="text" name="daterange" style="position: absolute;z-index: 999999;width: 246%;height: 24px;" /></h4>
        <!-- <h4>Device:<b><span id="deviceName"></span></b><span style="position:absolute;right:0%;"><i class="tim-icons icon-watch-time"></i>Time range:<span><i class="tim-icons icon-minimal-left" onClick="decreaseTime()"></i><span id="eventTimeVal"></span><i class="tim-icons icon-minimal-right" onClick="increaseTime()"></i></span></span></h4> -->
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-qa="Notification-showNearbyEvents-close" data-bs-target="rsc-event-details">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-class">

        </div>
    </div>

    <div class="form table-responsive white-content">
        <div class="row">
            <div class="card">
                <div class="card-body">
                    <div class="loader nearby-loader" style="position: absolute;bottom: 50%;right:50%;">&nbsp;<img src="../assets/img/nanohealLoader.gif" style="width: 71px;"></div>
                    <table class="nhl-datatable  table table-striped w-100" id="notifyeventAnalyserDtl" style="display:none">
                        <thead>
                            <tr>
                                <!-- <th class="ne-device" style="width:10%">Device</th> -->
                                <th class="ne-st" style="width:10%">Client Time</th>
                                <th class="ne-st" style="width:10%">Server Time</th>
                                <th class="ne-st" style="width:10%">Dart</th>
                                <th class="ne-st" style="width:10%">Desc</th>
                                <th style="width:30%"></th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Show More Event Details -->
<div id="rsc-more-details" class="rightSidenav" data-class="lg-9">
    <div class="rsc-loader no-opacity hide"></div>
    <div class="card-title border-bottom">
        <h4 data-qa="Notification-Event-Details-tabHeader">Event Details</h4>
        <a data-qa="Notification-Event-Details-close" href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-more-details">&times;</a>
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
                        <div data-qa="loader" class="loader more-loader" style="position: absolute;bottom: 70%;right:50%;">&nbsp;<img src="../assets/img/nanohealLoader.gif" style="width: 71px;"></div>
                        <table style="display:none" id="showmoreEventDtl">
                            <thead>
                                <!-- <tr>
                                            <th class="NewTable FirstRow">Event Id:</th>
                                            <th class="NewTable" id="EventId"></th>
                                        </tr> -->
                                <tr>
                                    <th class="NewTable FirstRow">Dart:</th>
                                    <th class="NewTable" id="DartNumber"></th>
                                </tr>
                                <tr>
                                    <th class="NewTable FirstRow">Site Name:</th>
                                    <th class="NewTable" id="SiteName"></th>
                                </tr>
                                <tr>
                                    <th class="NewTable FirstRow">Machine Name:</th>
                                    <th class="NewTable" id="MachName"></th>
                                </tr>
                                <tr>
                                    <th class="NewTable FirstRow">User Name:</th>
                                    <th class="NewTable" id="UName"></th>
                                </tr>
                                <!-- <tr>
                                            <th class="NewTable FirstRow">Server Time:</th>
                                            <th class="NewTable" id="ServerTime"></th>
                                        </tr> -->
                                <tr>
                                    <th class="NewTable FirstRow">Client Time:</th>
                                    <th class="NewTable" id="ClientTime"></th>
                                </tr>
                                <tr>
                                    <th class="NewTable FirstRow">Client Version:</th>
                                    <th class="NewTable" id="ClientVersion"></th>
                                </tr>
                                <tr>
                                    <th class="NewTable FirstRow">Event Details:</th>
                                    <th class="NewTable" id="EventDet"></th>
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
                            Notification Name <em class="error">*</em>
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
        <h4 id="filter_title">Notification Filters</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-add-filter-container">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="" name="" onclick="loadNotificationUsingFilters();">
                <i class="tim-icons icon-check-2 filterSubmit"></i>
                <span class="tooltiptext">Apply Filter</span>
            </div>
        </div>
    </div>
    <!-- <div class="button col-md-12 text-left"></div> -->

    <div class="form table-responsive white-content">
        <div class="sidebar">
            <ul class="nav">

                <li class="priorityExpand">
                    <a data-bs-toggle="collapse" href="#priorityExpandItems">
                        <h5>Priority<b class="caret"></b></h5>
                    </a>

                    <div class="collapse" id="priorityExpandItems">
                        <!--<div class="form-check checkboxMargin">
                            <label class="form-check-label">All
                                <input class="form-check-input" type="checkbox" class="type_check" checked value="all" id="prio_All" name="prio_All" onclick="togglePriority(this);">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>-->

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">P1
                                <input type="checkbox" class="form-check-input type_check" value="1" id="prio_p1" name="prio_p1" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">P2
                                <input type="checkbox" class="form-check-input type_check" value="2" id="prio_p2" name="prio_p2" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">P3
                                <input type="checkbox" class="form-check-input type_check" value="3" id="prio_p3" name="prio_p3" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>
                    </div>
                </li>
                <li class="typeExpand">
                    <a data-bs-toggle="collapse" href="#typeExpandItems">
                        <h5>Type<b class="caret"></b></h5>
                    </a>

                    <div class="collapse" id="typeExpandItems">

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Availability
                                <input type="checkbox" class="form-check-input type_check" value="Availability" id="ntype_1" name="ntype_1" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Maintenance
                                <input type="checkbox" class="form-check-input type_check" value="Maintenance" id="ntype_2" name="ntype_2" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Events
                                <input type="checkbox" class="form-check-input type_check" value="Events" id="ntype_3" name="ntype_3" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Security
                                <input type="checkbox" class="form-check-input type_check" value="Security" id="ntype_4" name="ntype_4" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Resources
                                <input type="checkbox" class="form-check-input type_check" value="Resource" id="ntype_5" name="ntype_5" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>
                    </div>
                </li>

                <li class="statusExpand">
                    <a data-bs-toggle="collapse" href="#statusExpandItems">
                        <h5>Status<b class="caret"></b></h5>
                    </a>


                    <div class="collapse" id="statusExpandItems">
                        <!--<div class="form-check checkboxMargin">
                            <label class="form-check-label">All
                                <input class="form-check-input" type="checkbox" class="status_check" checked value="all" id="status_All" name="type_All" onclick="toggleStatus(this);">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>-->

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">New
                                <input type="checkbox" class="form-check-input status_check" value="New" id="status_new" name="status_new" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Actioned
                                <input type="checkbox" class="form-check-input status_check" value="Actioned" id="status_actioned" name="status_actioned" checked="checked">
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check checkboxMargin">
                            <label class="form-check-label">Completed
                                <input type="checkbox" class="form-check-input status_check" value="Completed" id="status_completed" name="status_completed" checked="checked">
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


<!--Filter Options Div Ends-->

<style>
    .dataTables_scrollHeadInner {
        width: auto !important;
    }

    .showbtn {
        margin-left: 119px;
    }

    .clearbtn {
        margin-left: 119px;
    }

    .dropdown-menu {
        margin-left: 32px !important;
    }

    .dataTables_scrollBody {
        height: calc(100vh - 161px) !important;
    }

    #sortKey1,
    #sortKey2,
    #sortKey3,
    #sortKey4,
    #sortKey5 {
        margin-top: -15px;
        margin-left: 49px;
    }

    .dataTables_length {
        margin-top: 10px !important;
    }


    .table-detail {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
    }

    .table-detail td,
    th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
    }


    #showmoreEventDtl {
        width: 100%;
    }

    /* .table-detail tr:nth-child(even) {
    background-color: #dddddd;
    } */

    .daterangepicker {
        position: absolute;
        z-index: 999999;
    }

    .rightSidenav .form {
        height: calc(100vh - 81px);
        padding: 0px 20px 10px;
    }
</style>
