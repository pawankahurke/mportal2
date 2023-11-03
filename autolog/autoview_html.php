<style>
    #absoLoader {
        width: 98%;
        height: 100%;
        position: absolute;
        z-index: 10;
        background-color: #ffffff;
        top: 10px;
        opacity: 0.5;
    }

    #absoLoader img {
        margin-top: 18%;
        margin-left: 44%;
        width: 71px;
    }

    #auditlog_datatable td, #auditlog_datatable th {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 200px;
    }

    /* in common.css */
    .leftDropdown {
        width: 65%;
    }

    .form-check.form-check-radio{
        margin-top: -15px !important;
        margin-bottom: 10px !important;
    }

    .table-responsive::-webkit-scrollbar {
        height: 0.18em;
    }
    
    .table-responsive{
        height: 76vh;
    }

    #notifyDtl_length{
        margin-top: 10px;
    }

    .tooltip-inner {
        max-width: 236px !important;
        font-size: 12px;
        padding: 10px 15px 10px 20px;
        color: white;
        background-color: #0a1b66eb;
        text-align: left;
    }
</style>

<div class="content white-content">
    <div class="row">
        <div class="col-md-12  pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <?php
                    //   nhRole::checkRoleForPage('autoaudit');
                    $res = true; //nhRole::checkModulePrivilege('autoaudit');
                    if ($res) {
                    ?>
                        <div id="absoLoader" class="absoLoaderCont" data-qa="absoLoader" style='display:none;'>
                            <img class="absoLoader" src="../assets/img/nanohealLoader.gif">
                        </div>
                        <div class="toolbar">
                            <div class="bullDropdown leftDropdown">
                                <div class="dropdown">
                                    <div class="form-check form-check-radio">
                                        <span style="font-size: 10px;">Displaying Last 30 days activity for
                                            <label class="form-check-label">
                                            </label>
                                            <label class="form-check-label">
                                                <input class="form-check-input actiontype actionchk" name="actiontype" checked id="NotifSel" value="Notification" type="radio">Notification
                                                <span class="form-check-sign"></span>
                                            </label>
                                            <label class="form-check-label">
                                            </label>
                                            <label class="form-check-label">
                                                <input class="form-check-input actiontype actionchk" name="actiontype" id="TroublSel" value="Troubleshooter" type="radio">Agent Push
                                                <span class="form-check-sign"></span>
                                            </label>
                                            <label class="form-check-label">
                                            </label>
                                            <label class="form-check-label">
                                                <input class="form-check-input actiontype actionchk" name="actiontype" id="pushSolSel" value="Solution" type="radio">Push Solution API
                                                <span class="form-check-sign"></span>
                                            </label> 
                                            <label class="form-check-label">
                                            </label>
                                            <label class="form-check-label">
                                                <input class="form-check-input actiontype actionchk" name="actiontype" id="distributionSel" value="Distribution" type="radio">Software Distribution
                                                <span class="form-check-sign"></span>
                                            </label>
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="auditlog_datatable">
                            <thead>
                                <tr>
                                    <th id="key0" headers="action" class="action">
                                        Action
                                        <i class="fa fa-caret-down cursorPointer direction" id="action1" onclick="addActiveSort('asc', 'action'); getLogDetails('', 1, notifSearch = '', 'action', 'asc');sortingIconColor('action1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="action2" onclick="addActiveSort('desc', 'action'); getLogDetails('', 1, notifSearch = '', 'action', 'desc');sortingIconColor('action2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key1" headers="username" class="username">
                                        User Name
                                        <i class="fa fa-caret-down cursorPointer direction" id="username1" onclick="addActiveSort('asc', 'username'); getLogDetails('', 1, notifSearch = '', 'username', 'asc');sortingIconColor('username1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="username2" onclick="addActiveSort('desc', 'username'); getLogDetails('', 1, notifSearch = '', 'username', 'desc');sortingIconColor('username2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key2" headers="devicename" class="devicename">
                                      Device name
                                      <i class="fa fa-caret-down cursorPointer direction" id="devicename" onclick="addActiveSort('asc', 'deviceName'); getLogDetails('', 1, notifSearch = '', 'deviceName', 'asc');sortingIconColor('devicename')" style="font-size:18px"></i>
                                      <i class="fa fa-caret-up cursorPointer direction" id="devicename1" onclick="addActiveSort('desc', 'deviceName'); getLogDetails('', 1, notifSearch = '', 'deviceName', 'desc');sortingIconColor('devicename1')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key3" headers="useremail" class="useremail">
                                        User Email
                                        <i class="fa fa-caret-down cursorPointer direction" id="useremail1" onclick="addActiveSort('asc', 'useremail'); getLogDetails('', 1, notifSearch = '', 'useremail', 'asc');sortingIconColor('useremail1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="useremail2" onclick="addActiveSort('desc', 'useremail'); getLogDetails('', 1, notifSearch = '', 'useremail', 'desc');sortingIconColor('useremail2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key4" headers="module" class="created">
                                        Local Time
                                        <i class="fa fa-caret-down cursorPointer direction" id="created" onclick="addActiveSort('asc', 'created'); getLogDetails('', 1, notifSearch = '', 'created', 'asc');sortingIconColor('created')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="created1" onclick="addActiveSort('desc', 'created'); getLogDetails('', 1, notifSearch = '', 'created', 'desc');sortingIconColor('created1')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key5" headers="module" class="created">
                                        GMT Time
                                        <i class="fa fa-caret-down cursorPointer direction" id="created2" onclick="addActiveSort('asc', 'created'); getLogDetails('', 1, notifSearch = '', 'created', 'asc');sortingIconColor('created2')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="created3" onclick="addActiveSort('desc', 'created'); getLogDetails('', 1, notifSearch = '', 'created', 'desc');sortingIconColor('created3')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key6" headers="module" class="groupname">
                                        Details
                                        <i class="fa fa-caret-down cursorPointer direction" id="groupname1" onclick="addActiveSort('asc', 'refName'); getLogDetails('', 1, notifSearch = '', 'refName', 'asc');sortingIconColor('groupname1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="groupname2" onclick="addActiveSort('desc', 'refName'); getLogDetails('', 1, notifSearch = '', 'refName', 'desc');sortingIconColor('groupname2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key7" headers="module" class="status">
                                        Status
                                        <i class="fa fa-caret-down cursorPointer direction" id="status1" onclick="addActiveSort('asc', 'status'); getLogDetails('', 1, notifSearch = '','status', 'asc');sortingIconColor('status1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="status2" onclick="addActiveSort('desc', 'status'); getLogDetails('', 1, notifSearch = '', 'status', 'desc');sortingIconColor('status2')" style="font-size:18px"></i>
                                    </th>
                                </tr>
                            </thead>
                        </table> -->

                        <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="auditlog_datatable">
                            <thead>
                                <tr>
                                    <th id="key0" headers="username" class="username">
                                        Device Name
                                        <i class="fa fa-caret-down cursorPointer direction" id="devicename1" onclick="addActiveSort('asc', 'MachineTag'); getLogDetails('', 1, notifSearch = '', 'MachineTag', 'asc');sortingIconColor('devicename1')" style="font-size:18px"></i>

                                        <i class="fa fa-caret-up cursorPointer direction" id="devicename2" onclick="addActiveSort('desc', 'MachineTag'); getLogDetails('', 1, notifSearch = '', 'MachineTag', 'desc');sortingIconColor('devicename2')" style="font-size:18px"></i>
                                    </th>

                                    <th id="key1" headers="createdTime" class="createdTime">
                                        Creation Time
                                        <i class="fa fa-caret-down cursorPointer direction" id="jct1" onclick="addActiveSort('asc', 'JobCreatedTime'); getLogDetails('', 1, notifSearch = '', 'JobCreatedTime', 'asc');sortingIconColor('jct1')" style="font-size:18px"></i>

                                        <i class="fa fa-caret-up cursorPointer direction" id="jct2" onclick="addActiveSort('desc', 'JobCreatedTime'); getLogDetails('', 1, notifSearch = '', 'JobCreatedTime', 'desc');sortingIconColor('jct2')" style="font-size:18px"></i>
                                    </th>

                                    <th id="key2" headers="scope" class="scope">
                                        Scope
                                        <i class="fa fa-caret-down cursorPointer direction" id="scope1" onclick="addActiveSort('asc', 'SelectionType'); getLogDetails('', 1, notifSearch = '', 'SelectionType', 'asc');sortingIconColor('scope1')" style="font-size:18px"></i>

                                        <i class="fa fa-caret-up cursorPointer direction" id="scope2" onclick="addActiveSort('desc', 'SelectionType'); getLogDetails('', 1, notifSearch = '', 'SelectionType', 'desc');sortingIconColor('scope2')" style="font-size:18px"></i>
                                    </th>

                                    <th id="key3" headers="dart" class="dart">
                                        Dart
                                        <i class="fa fa-caret-down cursorPointer direction" id="dart1" onclick="addActiveSort('asc', 'JobCreatedTime'); getLogDetails('', 1, notifSearch = '', 'JobCreatedTime', 'asc');sortingIconColor('dart1')" style="font-size:18px"></i>

                                        <i class="fa fa-caret-up cursorPointer direction" id="dart2" onclick="addActiveSort('desc', 'JobCreatedTime'); getLogDetails('', 1, notifSearch = '', 'JobCreatedTime', 'desc');sortingIconColor('dart2')" style="font-size:18px"></i>
                                    </th>

                                    <th id="key4" headers="username" class="username">
                                        Executed by
                                        <i class="fa fa-caret-down cursorPointer direction" id="username1" onclick="addActiveSort('asc', 'AgentName'); getLogDetails('', 1, notifSearch = '', 'AgentName', 'asc');sortingIconColor('username1')" style="font-size:18px"></i>

                                        <i class="fa fa-caret-up cursorPointer direction" id="username2" onclick="addActiveSort('desc', 'AgentName'); getLogDetails('', 1, notifSearch = '', 'AgentName', 'desc');sortingIconColor('username2')" style="font-size:18px"></i>
                                    </th>

                                    <th id="key5" headers="useremail" class="useremail">
                                        Executed by - Email
                                        <i class="fa fa-caret-down cursorPointer direction" id="useremail1" onclick="addActiveSort('asc', 'AgentUniqId'); getLogDetails('', 1, notifSearch = '', 'AgentUniqId', 'asc');sortingIconColor('useremail1')" style="font-size:18px"></i>

                                        <i class="fa fa-caret-up cursorPointer direction" id="useremail2" onclick="addActiveSort('desc', 'AgentUniqId'); getLogDetails('', 1, notifSearch = '', 'AgentUniqId', 'desc');sortingIconColor('useremail2')" style="font-size:18px"></i>
                                    </th>

                                    <!-- <th id="key7" headers="jobType" class="jobType">
                                        Job Type
                                        <i class="fa fa-caret-down cursorPointer direction" id="jobType1" onclick="addActiveSort('asc', 'JobType'); getLogDetails('', 1, notifSearch = '', 'JobType', 'asc');sortingIconColor('jobType1')" style="font-size:18px"></i>

                                        <i class="fa fa-caret-up cursorPointer direction" id="jobType2" onclick="addActiveSort('desc', 'JobType'); getLogDetails('', 1, notifSearch = '', 'JobType', 'desc');sortingIconColor('jobType2')" style="font-size:18px"></i>
                                    </th> -->

                                    <th id="key8" headers="mos" class="mos">
                                        Machine OS
                                        <i class="fa fa-caret-down cursorPointer direction" id="mos1" onclick="addActiveSort('asc', 'MachineOs'); getLogDetails('', 1, notifSearch = '', 'MachineOs', 'asc');sortingIconColor('mos1')" style="font-size:18px"></i>

                                        <i class="fa fa-caret-up cursorPointer direction" id="mos2" onclick="addActiveSort('desc', 'MachineOs'); getLogDetails('', 1, notifSearch = '', 'MachineOs', 'desc');sortingIconColor('mos2')" style="font-size:18px"></i>
                                    </th>

                                    <th id="key9" headers="pofileName" class="pofileName">
                                        Solution Name
                                        <i class="fa fa-caret-down cursorPointer direction" id="pofileName1" onclick="addActiveSort('asc', 'ProfileName'); getLogDetails('', 1, notifSearch = '', 'ProfileName', 'asc');sortingIconColor('pofileName1')" style="font-size:18px"></i>

                                        <i class="fa fa-caret-up cursorPointer direction" id="pofileName2" onclick="addActiveSort('desc', 'ProfileName'); getLogDetails('', 1, notifSearch = '', 'ProfileName', 'desc');sortingIconColor('pofileName2')" style="font-size:18px"></i>
                                    </th>

                                    <th id="key10" headers="profileSequence" class="profileSequence">
                                        Solution Sequence
                                        <i class="fa fa-caret-down cursorPointer direction" id="profileSequence1" onclick="addActiveSort('asc', 'ProfileSequence'); getLogDetails('', 1, notifSearch = '', 'ProfileSequence', 'asc');sortingIconColor('profileSequence1')" style="font-size:18px"></i>

                                        <i class="fa fa-caret-up cursorPointer direction" id="profileSequence2" onclick="addActiveSort('desc', 'ProfileSequence'); getLogDetails('', 1, notifSearch = '', 'ProfileSequence', 'desc');sortingIconColor('profileSequence2')" style="font-size:18px"></i>
                                    </th>

                                    <th id="key11" headers="clinetTimeZone" class="clinetTimeZone">
                                        Client TimeZone
                                        <i class="fa fa-caret-down cursorPointer direction" id="clinetTimeZone1" onclick="addActiveSort('asc', 'ClientTimeZone'); getLogDetails('', 1, notifSearch = '', 'ClientTimeZone', 'asc');sortingIconColor('clinetTimeZone1')" style="font-size:18px"></i>

                                        <i class="fa fa-caret-up cursorPointer direction" id="clinetTimeZone2" onclick="addActiveSort('desc', 'ClientTimeZone'); getLogDetails('', 1, notifSearch = '', 'ClientTimeZone', 'desc');sortingIconColor('clinetTimeZone2')" style="font-size:18px"></i>
                                    </th>

                                    <th id="key12" headers="clientExecutedTime" class="clientExecutedTime">
                                        Client Execution Time
                                        <i class="fa fa-caret-down cursorPointer direction" id="clientExecutedTime1" onclick="addActiveSort('asc', 'ClientExecutedTime'); getLogDetails('', 1, notifSearch = '', 'ClientExecutedTime', 'asc');sortingIconColor('clientExecutedTime1')" style="font-size:18px"></i>

                                        <i class="fa fa-caret-up cursorPointer direction" id="clientExecutedTime2" onclick="addActiveSort('desc', 'ClientExecutedTime'); getLogDetails('', 1, notifSearch = '', 'ClientExecutedTime', 'desc');sortingIconColor('clientExecutedTime2')" style="font-size:18px"></i>
                                    </th>

                                    <th id="key13" headers="jobStatus" class="jobStatus">
                                        Status
                                        <i class="fa fa-caret-down cursorPointer direction" id="jobStatus1" onclick="addActiveSort('asc', 'JobStatus'); getLogDetails('', 1, notifSearch = '', 'JobStatus', 'asc');sortingIconColor('jobStatus1')" style="font-size:18px"></i>

                                        <i class="fa fa-caret-up cursorPointer direction" id="jobStatus2" onclick="addActiveSort('desc', 'JobStatus'); getLogDetails('', 1, notifSearch = '', 'JobStatus', 'desc');sortingIconColor('jobStatus2')" style="font-size:18px"></i>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    <?php
                    }
                    ?>
                    <div id="largeDataPagination"></div>
                    <!--</div>-->
                </div>
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>
<div id="auditlog-range" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Export Automation Audit Data</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="auditlog-range">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="btn-exportAutomationAuditData" onclick="exportAuditLog();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Export</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <form id="RegisterValidation">
            <div class="card">
                <div class="card-body">

                    <div class="form-group has-label">
                        <!--  <h4 class="card-title">Level</h4> -->
                        <label>
                            Export for the selected user:
                        </label>
                        <div class="UserSelection">
                            <select class="form-control selectpicker dropdown-submenu" data-style="btn btn-info" id="UserSelection" data-size="5">
                            </select>
                        </div>
                        <label>
                            Type
                        </label>
                        <select class="form-control selectpicker dropdown-submenu" data-style="btn btn-info" id="SelectionType" data-size="5">
                            <option value='Notification'>Notification</option>
                            <option value='Interactive'>Agent Push</option>
                            <option value='Solution'>Push Solution API</option>
                            <option value='Distribution'>Software Distribution</option>
                        </select>
                    </div>

                </div>

                <div class="form-group has-label">
                    <label>
                        Start Date
                    </label>
                    <input type="text" class="form-control datetimepicker" id="datefrom" name="datefrom">
                    <span style="color:red;" id="datefrom_err"></span>
                </div>

                <div class="form-group has-label">
                    <label>
                        End Date
                    </label>
                    <input type="text" class="form-control datetimepicker" id="dateto" name="dateto">
                    <span style="color:red;" id="dateto_err"></span>
                </div>
            </div>


            <div class="button col-md-12 text-center" style="bottom:-38px;">
                <span id="loadingSuccessMsg" style="color: green;float: left;"></span>
            </div>
            <div>
                <span id="errorMsg" style="color:red;"></span>
            </div>
    </div>
    </form>
</div>
</div>
