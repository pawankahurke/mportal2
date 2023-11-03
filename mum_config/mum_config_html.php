+<?php
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

    .main-panel>.content .card {
        height: auto !important;
    }

    .main-panel>.content .card {
        margin-bottom: 0px;
    }

    .main-panel>.content {
        padding: 50px 20px 0px 280px;
        min-height: calc(100vh - 34px) !important;
    }

    .NewTable {
        font-weight: 100;
        border: 1px solid grey;
        font-size: 13px;
        font-family: "Montserrat", sans-serif;
        padding: 10px 10px 10px 10px;
    }

    .FirstRow {
        width: 15%;
    }

    .SecondRow {
        width: 15%;
    }

    .ThirdRow {
        width: 15%;
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

    .leftPaneMUM {
        /*margin-left: -131%;*/
        margin-top: 48px;
    }

    .mumliPadding {
        padding: 10px 10px 10px 10px;
    }

    .innerLeft .sidebar .nav li a {
        border-bottom: none;
    }

    .noBreakWord {
        white-space: nowrap;
    }

    .selected {
        background: #f5f6fa;
    }

    .row {
        font-size: 10px;
        color: #000;
    }

    .changeSpanText {
        font-size: 10px;
        color: #000;
    }

    .bodYDiv {
        padding: 10px 10px 10px 0px;
    }

    #MethodSummaryTable {
        padding: 39px 10px 10px 10px;
        width: 95%;
        table-layout: fixed;
    }

    #showConfigurationsTable {
        padding: 39px 10px 10px 10px;
        width: 97%;
        table-layout: fixed;
    }

    #showSummaryTable {
        width: 97%;
        table-layout: fixed;
    }

    #loadermain {
        width: 98%;
        height: 95%;
        position: absolute;
        z-index: 10;
        background-color: #ffffff;
        top: 10px;
        opacity: 0.5;
    }
</style>
<div class="content pt-0 white-content mumPage" style="margin-top: 38px;">
    <div class="row" style="margin-bottom: 22px;height:calc(100vh - 74px);overflow-x: scroll;">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <?php
                    //   nhRole::checkRoleForPage('patchmanagement_new');
                    $res = true; // nhRole::checkModulePrivilege('patchmanagement_new');
                    if ($res) {
                    ?>
                        <div id="loader" class="loader" data-qa="loader">
                            <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                        </div>
                        <div class="toolbar">

                            <input type="hidden" id="config_value" value="" name="">
                            <input type="hidden" id="pageValue" value="" name="">
                            <input type="hidden" id="declinepatchpage" value="" name="">
                            <input type="hidden" id="retryPage" value="" name="">
                            <input type="hidden" id="criticalPage" value="" name="">

                            <input type="hidden" id="selected_siteID">
                            <input type="hidden" id="selected_scope">
                            <input type="hidden" id="selected_scopeVal">
                            <!-- <div id="loadermain" style="display:none"><img src="../assets/img/nanohealLoader.gif" style="margin-top: 20%;width: 71px;"></div> -->

                            <div id="showSiteConfigurations">
                                <div>&nbsp;</div>
                                <div>&nbsp;</div>
                                <div id="SummaryDiv">
                                    <h5><b>Summary</b></h5>
                                    <table id="showSummaryTable">
                                        <thead>
                                            <tr>
                                                <th class="NewTable FirstRow">Configured By</th>
                                                <th class="NewTable" id="configuredBy"></th>
                                            </tr>
                                            <tr>
                                                <th class="NewTable FirstRow">Configured On</th>
                                                <th class="NewTable" id="configuredDate"></th>
                                            </tr>

                                        </thead>
                                    </table>
                                </div>
                                <div>&nbsp;</div>
                                <div id="UpdateSummaryDiv">
                                    <h5><b>Configurations</b></h5>
                                    <span id="ShowConfigMessage"></span>
                                    <table id="showConfigurationsTable" style="display:none">
                                        <thead>
                                            <tr>
                                                <th class="NewTable SecondRow">Source Of Updates</th>
                                                <th class="NewTable" id="SourceUpdateVal"></th>
                                            </tr>
                                            <tr>
                                                <th class="NewTable SecondRow">Management</th>
                                                <th class="NewTable" id="manageVal"></th>
                                            </tr>
                                            <tr>
                                                <th class="NewTable SecondRow">New Updates</th>
                                                <th class="NewTable" id="newUpdateVal"></th>
                                            </tr>
                                            <tr>
                                                <th class="NewTable SecondRow">Downloading Updates</th>
                                                <th class="NewTable" id="downUpdateVal"></th>
                                            </tr>
                                            <tr>
                                                <th class="NewTable SecondRow">Retention Policy</th>
                                                <th class="NewTable" id="RetPolVal"></th>
                                            </tr>
                                            <tr>
                                                <th class="NewTable SecondRow">Restart Policy</th>
                                                <th class="NewTable" id="restartPolVal"></th>
                                            </tr>
                                            <tr>
                                                <th class="NewTable SecondRow">Multiple Installations</th>
                                                <th class="NewTable" id="multipleInsVal"></th>
                                            </tr>
                                        </thead>

                                    </table>
                                </div>
                                <div>&nbsp;</div>
                                <div id="MethodSummaryDiv">
                                    <h5><b>Update Method</b></h5>
                                    <table id="MethodSummaryTable">
                                        <thead>
                                            <tr>
                                                <th class="NewTable ThirdRow">Update Method</th>
                                                <th class="NewTable" id="selUpdateMethodVal"></th>
                                            </tr>
                                            <tr>
                                                <th class="NewTable ThirdRow">Schedule</th>
                                                <th class="NewTable" id="scheduleOption">
                                                    <div>
                                                        <p>Delay Operation by days :<span id="delay1"></span></p><br>
                                                        <p>Schedule to use for scheduled options:</p><br>
                                                        <p class="mt-2">Hour: <span id="sched_hour1"></span></p><br>
                                                        <p class="mt-2">Minutes: <span id="sched_min1"></span></p><br>
                                                        <p class="mt-2">Weekday: <span id="sched_week1"></span></p><br>
                                                        <p class="mt-2">Month: <span id="sched_month1"></span></p><br>
                                                        <p class="mt-2">Day: <span id="sched_day1"></span></p><br>
                                                        <p class="mt-2">Random Delay in minutes: <span id="randomdelay1"></span></p><br>
                                                        <p class="mt-2">Action on Missed Schedule: <span id="actionmissed1"></span></p><br>
                                                    </div>
                                                </th>
                                            </tr>
                                            <tr>
                                                <th class="NewTable ThirdRow">Notification</th>
                                                <th class="NewTable" id="notifOption">
                                                    <div>
                                                        <p>Delay Operation by days :<span id="delay2"></span></p><br>
                                                        <p>Schedule to use for scheduled options:</p><br>
                                                        <p>Hour: <span id="sched_hour2"></span></p><br>
                                                        <p>Minutes: <span id="sched_min2"></span></p><br>
                                                        <p>Weekday: <span id="sched_week2"></span></p><br>
                                                        <p>Month: <span id="sched_month2"></span></p><br>
                                                        <p>Day: <span id="sched_day2"></span></p><br>
                                                        <p>Random Delay in minutes: <span id="randomdelay2"></span></p><br>
                                                        <p>Action on Missed Schedule: <span id="actionmissed2"></span></p><br>
                                                        <p>Notification Text: <span id="notifyText2"></span></p><br>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>

                                    </table>
                                <?php
                            }
                                ?>
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

<!--Configure Update Div Starts-->
<div id="config-update-container" class="rightSidenav leftSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Configure Update</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="config-update-container">&times;</a>
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
                <i class="tim-icons icon-check-2 ConfigUpdateSubmit"></i>
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
                <div id="showConfigUpdateDiv">
                    <div class="patch-manage-page">
                        <div class="row clearfix" style="padding: 10px 10px 10px 23px;">
                            <div class="col-lg-3 col-md-12 col-sm-12 col-xs-12 left equalHeight">
                                <form method="post" name="configurePatch2" id="configurePatch2">
                                    <label class="noBreakWord"><b>Source of Updates:</b></label>
                                    <div id="UpdateSelDiv" class="row">
                                        <div class="col-sm-12" id="checkAddConfigtype_2">
                                            <div class="form-check form-check-radio">
                                                <label class="form-check-label noBreakWord">
                                                    <input class="form-check-input" type="radio" id="mumsel" name="patchSource" value="1">
                                                    <span class="form-check-sign"></span> Microsoft Update Server
                                                </label>
                                            </div>

                                            <div class="form-check form-check-radio">
                                                <label class="form-check-label noBreakWord">
                                                    <input class="form-check-input" type="radio" id="sussel" name="patchSource" value="2">
                                                    <span class="form-check-sign"></span> SUS server:<input style="margin-top: -20px;margin-left: 60px;width: 196%;" class="form-control" type="text" id="susselurl" name="susselurl" size="60">
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div>&nbsp;</div>

                                    <label><b>Management:</b></label>
                                    <div id="ManagementDiv" class="row">
                                        <div class="col-sm-12">
                                            <div class="form-check form-check-radio">
                                                <label class="form-check-label noBreakWord">
                                                    <input class="form-check-input" type="radio" id="management1" name="managementSel" value="1">
                                                    <span class="form-check-sign"></span> Disable
                                                </label>
                                            </div>
                                            <div class="form-check form-check-radio">
                                                <label class="form-check-label noBreakWord">
                                                    <input class="form-check-input" type="radio" id="management2" name="managementSel" value="3">
                                                    <span class="form-check-sign"></span> User controlled download and install
                                                </label>
                                            </div>
                                            <div class="form-check form-check-radio">

                                                <label class="form-check-label noBreakWord">
                                                    <input class="form-check-input" type="radio" id="management3" name="managementSel" value="4">
                                                    <span class="form-check-sign"></span> Automated download, user controlled install
                                                    <span id="showDay"></span>
                                                    at
                                                    <span id="showHour"></span>
                                                </label>
                                            </div>
                                            <div class="form-check form-check-radio">
                                                <label class="form-check-label noBreakWord">
                                                    <input class="form-check-input" type="radio" id="management4" name="managementSel" value="5">
                                                    <span class="form-check-sign"></span> Automated download and install
                                                </label>
                                            </div>
                                            <div class="form-check form-check-radio">
                                                <label class="form-check-label noBreakWord">
                                                    <input class="form-check-input" type="radio" id="management5" name="managementSel" value="2">
                                                    <span class="form-check-sign"></span> Manage from Server
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div>&nbsp;</div>

                                    <div id="Show_manageOptions" style="display:none">

                                        <label><b>New Updates:</b></label>
                                        <div id="NewUpdateDiv" class="row my-form-inline-radio global-value-group">
                                            <div class="col-sm-12">
                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label noBreakWord">
                                                        <input class="form-check-input" type="radio" id="newupdate1" name="selnewUpdate" value="1">
                                                        <span class="form-check-sign"></span> Act based on last settings from server.
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label noBreakWord">
                                                        <input class="form-check-input" type="radio" id="newupdate2" name="selnewUpdate" value="2">
                                                        <span class="form-check-sign"></span> Wait to get current settings from server before taking action.
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div>&nbsp;</div>

                                        <label class="noBreakWord"><b>Downloading updates:</b></label>
                                        <div id="downUpdate" class="row my-form-inline-radio global-value-group">
                                            <div class="col-sm-12">
                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label noBreakWord">
                                                        <input class="form-check-input" type="radio" id="downUpdate1" name="selDownUpdate" value="0">
                                                        <span class="form-check-sign"></span>Only download from vendor
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label noBreakWord">
                                                        <input class="form-check-input" type="radio" id="downUpdate2" name="selDownUpdate" value="1">
                                                        <span class="form-check-sign"></span>Only retrieve from local machines
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label noBreakWord">
                                                        <input class="form-check-input" type="radio" id="downUpdate3" name="selDownUpdate" value="2">
                                                        <span class="form-check-sign"></span>Try to retrieve from local machines, then download from vendor if unsuccessful
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div>&nbsp;</div>

                                        <label><b>Retention policy:</b></label>
                                        <div id="retenSel" class="row my-form-inline-radio global-value-group">
                                            <div class="col-sm-12">
                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label noBreakWord">
                                                        <input class="form-check-input" type="radio" id="retenSel1" name="RetenSel" value="1">
                                                        <span class="form-check-sign"></span>Do not keep updates on this machine for other machines to use
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label noBreakWord">
                                                        <input class="form-check-input" type="radio" id="retenSel2" name="RetenSel" value="2">
                                                        <span class="form-check-sign"></span>Keep updates on this machine for<span id="showDays2"></span>days, for other machines to use
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div>&nbsp;</div>

                                        <label><b>Restart policy:</b></label>
                                        <div id="RestartSelDiv" class="row my-form-inline-radio global-value-group">
                                            <div class="col-sm-12">
                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label noBreakWord">
                                                        <input class="form-check-input" type="radio" id="RestartSel1" name="restartSel" value="1">
                                                        <span class="form-check-sign"></span>Do not automatically restart when a restart is necessary after an installation.
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label noBreakWord">
                                                        <input class="form-check-input" type="radio" id="RestartSel2" name="restartSel" value="2">
                                                        <span class="form-check-sign"></span>Automatically restart when a restart is necessary after an installation
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div>&nbsp;</div>

                                        <label class="noBreakWord"><b>Multiple installations:</b></label>
                                        <div id="mulinstallDiv" class="row my-form-inline-radio global-value-group">
                                            <div class="col-sm-12">
                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label noBreakWord">
                                                        <input class="form-check-input" type="radio" id="multipleInstall1" name="multipleInstallSel" value="1">
                                                        <span class="form-check-sign"></span>Repeat install cycle until machine is up to date, but stop after<span id="showHour2"></span>hours.
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label noBreakWord">
                                                        <input class="form-check-input" type="radio" id="multipleInstall2" name="multipleInstallSel" value="2">
                                                        <span class="form-check-sign"></span>Repeat install cycle until machine is up to date.
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label noBreakWord">
                                                        <input class="form-check-input" type="radio" id="multipleInstall3" name="multipleInstallSel" value="3">
                                                        <span class="form-check-sign"></span>Only do one install cycle.
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </ul>
        </div>
    </div>
</div>
<!--Configure Update Div ends-->

<!--Update Method Div Starts-->
<div id="update-method-container" class="rightSidenav leftSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Update Method</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="update-method-container">&times;</a>
    </div>

    <div class="btnGroup" id="editOption2" style="display: block;">
        <div class="icon-circle">
            <div class="toolTip editOption">
                <i class="tim-icons icon-pencil"></i>
                <span class="tooltiptext">Edit</span>
            </div>
        </div>
    </div>

    <div class="btnGroup" style="display: none;" id="toggleButton2">
        <div class="icon-circle circleGrey">
            <div class="toolTip" id="" name="">
                <i class="tim-icons icon-check-2 UpdateMethodSubmit"></i>
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
                <div id="showUpdateMethodDiv" class="bodYDiv">
                    <div class="form table-responsive white-content">
                        <div class="sidebar">
                            <ul class="nav">
                                <li>
                                    <label><b>Select Update method</b></label>

                                    <div>
                                        <div class="form-check form-check-radio">
                                            <label class="form-check-label noBreakWord">
                                                <input class="form-check-input actionchk automaticupdate" name="updatemethod" type="radio" checked="checked" value="4">All updates approved automatically
                                                <span class="form-check-sign"></span>
                                            </label>
                                        </div>

                                        <div class="form-check form-check-radio">
                                            <label class="form-check-label noBreakWord">
                                                <input class="form-check-input actionchk manualupdate" name="updatemethod" type="radio" value="1">Manually approve updates
                                                <span class="form-check-sign"></span>
                                            </label>
                                        </div>
                                    </div>
                                    <div>
                                        <div>&nbsp;</div>
                                        If you select All updates approved automatically then new updates will be installed automatically unless you decline them using the Decline updates wizard.<br>
                                        <div>&nbsp;</div>
                                        If you select Manually approve updates then new updates will not be installed until you approve them.<br>
                                        <div>&nbsp;</div>
                                    </div>
                                <li id="schOption2" style="display:none">
                                    <label><b>Schedule Options</b></label>
                                    <div>&nbsp;</div>
                                    <div id="tablesExamplesshedule2" style="padding-left: 2px;">
                                        <div class="form-radio">
                                            <div class="row">
                                                <label class="form-check-label noBreakWord" style="margin-left: 13px;">
                                                    Delay Operation by days
                                                </label>

                                                <div class="col-md-4">
                                                    <input type="text" class="form-control schleedelay2" style="margin-left: 10px; margin-top: -2px;" value="">
                                                    <span class="form-check-sign"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div>&nbsp;</div>

                                        <div class="form-radio">
                                            <label class="form-check-label noBreakWord" style="margin-left: 2px;">
                                                <b>Schedule:</b>
                                            </label>
                                            <div>&nbsp;</div>
                                            <div class="row">
                                                <div class="col-md-3">Hour:</div>

                                                <div class="col-md-3">
                                                    <select class="selectpicker schlehour2" data-style="btn btn-info" data-size="7">
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
                                                    <select class="selectpicker schlemin2" data-style="btn btn-info" data-size="7">
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
                                                    <select class="selectpicker schleweek2" data-style="btn btn-info" data-size="7">
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
                                                    <select class="selectpicker schedlemon2" data-style="btn btn-info" data-size="7">
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
                                                    <select class="selectpicker schedleday2" data-style="btn btn-info" data-size="7">
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
                                        <div>&nbsp;</div>
                                        <div class="form-radio">
                                            <div class="row">
                                                <label class="form-check-label noBreakWord" style="margin-left: 15px;">
                                                    Random Delay in minutes
                                                </label>

                                                <div class="col-md-4">
                                                    <input type="text" class="form-control schlerandomdelay2" value="">
                                                    <span class="form-check-sign"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div>&nbsp;</div>
                                        <div class="form-radio">
                                            <label class="form-check-label noBreakWord">
                                                Action on Missed Schedule
                                            </label>

                                            <div class="form-check form-check-radio">
                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label noBreakWord">
                                                        <input class="form-check-input scheduletypeasap2 runasap2" name="schleradio2" type="radio" value="1" checked>Run as soon as Possible
                                                        <span class="form-check-sign"></span>
                                                    </label>
                                                </div>

                                                <div class="form-check form-check-radio">
                                                    <label class="form-check-label noBreakWord">
                                                        <input class="form-check-input scheduletypetiming2 scheduletype2" name="schleradio2" type="radio" value="2">Run at schedule time
                                                        <span class="form-check-sign"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                </li>
                            </ul>

                        </div>
                    </div>
                </div>
            </ul>
        </div>
    </div>
</div>
<!--Configure Update Div ends-->



<!--Configure upload div starts-->
<div id="config-upload-container" class="rightSidenav leftSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Configure Upload & download propagation</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="config-upload-container">&times;</a>
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
                <i class="tim-icons icon-check-2 ConfigUploadSubmit"></i>
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
                <div id="showConfigUploadDiv" class="bodYDiv">
                    <div class="patch-manage-page">
                        <div class="row clearfix">
                            <div class="col-lg-3 col-md-12 col-sm-12 col-xs-12 left equalHeight">
                                <form method="post" name="configurePatch2" id="configurePatch2">

                                    <!--                                            <label class="changeSpanText noBreakWord">How do you want the machines you selected to retrieve updates?</label>
                                            <div>&nbsp;</div>    -->
                                    <label class="changeSpanText noBreakWord"><b>Updates should be:</b></label>
                                    <div id="ConfigUploadDiv1" class="row my-form-inline-radio global-value-group">
                                        <div class="col-sm-12">
                                            <div class="form-check form-check-radio">
                                                <label class="form-check-label noBreakWord">
                                                    <input class="form-check-input" type="radio" id="updateSelected1" name="updateConfigUpload" value="1">
                                                    <span class="form-check-sign"></span>Only downloaded from vendor
                                                </label>
                                            </div>

                                            <div class="form-check form-check-radio">
                                                <label class="form-check-label noBreakWord">
                                                    <input class="form-check-input" type="radio" id="updateSelected2" name="updateConfigUpload" value="2">
                                                    <span class="form-check-sign"></span>Only retrieved from local machines
                                                </label>
                                            </div>

                                            <div class="form-check form-check-radio">
                                                <label class="form-check-label noBreakWord">
                                                    <input class="form-check-input" type="radio" id="updateSelected3" name="updateConfigUpload" value="3">
                                                    <span class="form-check-sign"></span>Try local machines first,then from vendor if unsuccessful
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div>&nbsp;</div>
                                    <label class="changeSpanText noBreakWord"><b>Update retention policy</b></label>
                                    <!--                                            <label class="changeSpanText noBreakWord">How long would you like the machines you selected to download updates to keep copies of the update files?</label>
                                            <div>&nbsp;</div>-->
                                    <div id="ConfigUploadDiv2" class="row my-form-inline-radio global-value-group">
                                        <div class="col-sm-12">
                                            <div class="form-check form-check-radio">
                                                <label class="form-check-label noBreakWord">
                                                    <input class="form-check-input" type="radio" id="uploadRetentionpolicy1" name="retenPolicySel" value="1">
                                                    <span class="form-check-sign"></span>Do not keep updates on the selected machines for other machines to use
                                                </label>
                                            </div>

                                            <div class="form-check form-check-radio">
                                                <label class="form-check-label noBreakWord">
                                                    <input class="form-check-input" type="radio" id="uploadRetentionpolicy1" name="retenPolicySel" value="2">
                                                    <span class="form-check-sign"></span>Keep updates on the selected machines for<span id="showDays3"></span>days,for other machines<br> to use
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>


                    </div>
                </div>
            </ul>
        </div>
    </div>
</div>
<!--Configure upload div ends-->

<!--Patch Settings-->
<div id="settings-add-container" class="rightSidenav leftSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Setting Details</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="settings-add-container">&times;</a>
    </div>

    <div class="btnGroup" id="editOption4" style="display: block;">
        <div class="icon-circle">
            <div class="toolTip editOption">
                <i class="tim-icons icon-pencil"></i>
                <span class="tooltiptext">Edit</span>
            </div>
        </div>
    </div>

    <div class="btnGroup" style="display: none;" id="toggleButton4">
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
                <li>
                    <a data-bs-toggle="collapse" href="#tablesExamples2">
                        <h5>Update method<b class="caret"></b></h5>
                    </a>

                    <div class="collapse" id="tablesExamples2" style="padding-left: 24px;">
                        <div class="form-check form-check-radio">
                            <label class="form-check-label">
                                <input class="form-check-input actionchk automaticupdate" name="updatemethod" type="radio" checked="checked" value="4">All updates approved automatically
                                <span class="form-check-sign"></span>
                            </label>
                        </div>

                        <div class="form-check form-check-radio">
                            <label class="form-check-label">
                                <input class="form-check-input actionchk manualupdate" name="updatemethod" type="radio" value="1">Manually approve updates
                                <span class="form-check-sign"></span>
                            </label>
                        </div>
                    </div>
                </li>
                <li>

                    <a data-bs-toggle="collapse" href="#tablesExamples3">
                        <h5>Advanced Configurations<b class="caret"></b></h5>
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
                                        <input type="text" class="form-control schleedelay" style="margin-left: 10px; margin-top: -2px;">
                                        <span class="form-check-sign"></span>
                                    </div>
                                </div>
                            </div>

                            <div>&nbsp;</div>

                            <div class="form-radio">
                                <div class="row">
                                    <div class="col-md-8">Schedule to use for scheduled options </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-3">Hour:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker schlehour" data-style="btn btn-info" data-size="7">
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

                                <div class="row mt-2">
                                    <div class="col-md-3">Minutes:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker schlemin" data-style="btn btn-info" data-size="7">
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

                                <div class="row mt-2">
                                    <div class="col-md-3">Weekday:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker schleweek" data-style="btn btn-info" data-size="7">
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

                                <div class="row mt-2">
                                    <div class="col-md-3">Month:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker schedlemon" data-style="btn btn-info" data-size="7">
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

                                <div class="row mt-2">
                                    <div class="col-md-3">Day:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker schedleday" data-style="btn btn-info" data-size="7">
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
                                <div class="row mt-2">
                                    <label class="form-check-label" style="margin-left: 15px;">
                                        Random Delay in minutes
                                    </label>

                                    <div class="col-md-4">
                                        <input type="text" class="form-control schlerandomdelay">
                                        <span class="form-check-sign"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-radio mt-2">
                                <label class="form-check-label">
                                    Action on Missed Schedule
                                </label>

                                <div class="form-check form-check-radio">
                                    <div class="form-check form-check-radio">
                                        <label class="form-check-label">
                                            <input class="form-check-input scheduletypeasap runasap" name="schleradio" type="radio" value="1" checked>Run as soon as Possible
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check form-check-radio">
                                        <label class="form-check-label">
                                            <input class="form-check-input scheduletypetiming scheduletype" name="schleradio" type="radio" value="2">Run at schedule time
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
                                            <input class="form-check-input notifymins" name="notifymins" type="checkbox" value="1">Notify &nbsp; &nbsp; &nbsp; &nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; minutes before action
                                            <span class="form-check-sign"></span>
                                        </label>

                                        <input type="text" class="form-control notinumb numb" value="15" maxlength="2">
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input notifremnd" name="notifremnd" type="checkbox" value="1">Reminder user to leave system on
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input notifprevsys" name="notifprevsys" type="checkbox" value="1">Prevent system shutdown
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check">
                                        <label class="form-check-label">
                                            <input class="form-check-input notifschdlsop" name="notifschdlsop" type="checkbox" value="1">Schedule notification
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div>&nbsp;</div>

                            <div class="form-radio">
                                <div class="row">
                                    <div class="col-md-8">Schedule to use for scheduled options</div>
                                </div>
                            </div>

                            <div class="form-check form-check-radio">
                                <div class="row mt-2">
                                    <div class="col-md-3">Hour:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker notifhour" data-style="btn btn-info" data-size="7">
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

                                <div class="row mt-2">
                                    <div class="col-md-3">Minutes:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker notifmin" data-style="btn btn-info" data-size="7">
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

                                <div class="row mt-2">
                                    <div class="col-md-3">Weekday:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker notifwkly" data-style="btn btn-info" data-size="7">
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

                                <div class="row mt-2">
                                    <div class="col-md-3">Month:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker notifmon" data-style="btn btn-info" data-size="7">
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

                                <div class="row mt-2">
                                    <div class="col-md-3">Day:</div>

                                    <div class="col-md-3">
                                        <select class="selectpicker notifday" data-style="btn btn-info" data-size="7">
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
                                <div class="row mt-2">
                                    <label style="margin-left: 15px;">
                                        Random Delay in minutes
                                    </label>

                                    <div class="col-md-4">
                                        <input type="text" class="form-control notifrandomdelay" value="">
                                    </div>
                                </div>
                            </div>

                            <div class="form-radio mt-2">
                                <label class="form-check-label">
                                    Action on Missed Schedule
                                </label>

                                <div class="form-check form-check-radio">
                                    <div class="form-check form-check-radio">
                                        <label class="form-check-label">
                                            <input class="form-check-input runasap notifasap" name="notifradio" type="radio" value="1" checked>Run as soon as Possible
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>

                                    <div class="form-check form-check-radio">
                                        <label class="form-check-label">
                                            <input class="form-check-input notiftiming" name="notifradio" type="radio" value="2">Run at schedule time
                                            <span class="form-check-sign"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div>&nbsp;</div>

                            <div class="form-radio">
                                <label class="form-check-label">
                                    Notification Text
                                    <!--                                    <input type="text" class="form-control notifrandomdelay" value="">
                                    <span class="form-check-sign"></span>-->
                                    <textarea rows="4" cols="57" id="notif_text" style="width: 92%;" class="form-control"></textarea>
                                </label>
                            </div>
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
</div>