<!-- content starts here  -->
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
?>
<div class="content white-content">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                        <!-- loader -->
                        <div id="loader" class="loader"  data-qa="loader" style="position: absolute;bottom: 50%;right:50%;">
                           <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                        </div>
                    <div class="toolbar">
                    </div>
                    <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="auditlog_datatable">
                        <thead>
                            <tr>
                                <th id="key0" headers="action" class="">
                                    Action
                                    <i class="fa fa-caret-down cursorPointer direction" id = "action1" onclick = "addActiveSort('asc', 'action'); getLogDetails(1,notifSearch='','action', 'asc');sortingIconColor('action1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "action2" onclick = "addActiveSort('desc', 'action'); getLogDetails(1,notifSearch='','action', 'desc');sortingIconColor('action2')" style="font-size:18px"></i>
                                </th>
                                <!-- added -->
								<th id="key9" headers="module" class="">
                                    Module
                                    <i class="fa fa-caret-down cursorPointer direction" id = "action1" onclick = "addActiveSort('asc', 'module'); getLogDetails(1,notifSearch='','module', 'asc');sortingIconColor('module1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "action2" onclick = "addActiveSort('desc', 'module'); getLogDetails(1,notifSearch='','module', 'desc');sortingIconColor('module2')" style="font-size:18px"></i>
                                </th>
                                <th id="key1" headers="ip" class="">
                                    Public IP
                                    <i class="fa fa-caret-down cursorPointer direction" id = "ip1" onclick = "addActiveSort('asc', 'ip'); getLogDetails(1,notifSearch='','ip', 'asc');sortingIconColor('ip1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "ip2" onclick = "addActiveSort('desc', 'ip'); getLogDetails(1,notifSearch='','ip', 'desc');sortingIconColor('ip2')" style="font-size:18px"></i>
                                </th>
                                <th id="key2" headers="agent" class="">
                                    Browse
                                    <i class="fa fa-caret-down cursorPointer direction" id = "agent1" onclick = "addActiveSort('asc', 'agent'); getLogDetails(1,notifSearch='','agent', 'asc');sortingIconColor('agent1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "agent2" onclick = "addActiveSort('desc', 'agent'); getLogDetails(1,notifSearch='','agent', 'desc');sortingIconColor('agent2')" style="font-size:18px"></i>
                                </th>
                                <th id="key3" headers="username" class="">
                                    User Name
                                    <i class="fa fa-caret-down cursorPointer direction" id = "username1" onclick = "addActiveSort('asc', 'username'); getLogDetails(1,notifSearch='','username', 'asc');sortingIconColor('username1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "username2" onclick = "addActiveSort('desc', 'username'); getLogDetails(1,notifSearch='','username', 'desc');sortingIconColor('username2')" style="font-size:18px"></i>
                                </th>
                                <th id="key4" headers="useremail" class="">
                                    User Email
                                    <i class="fa fa-caret-down cursorPointer direction" id = "useremail1" onclick = "addActiveSort('asc', 'useremail'); getLogDetails(1,notifSearch='','useremail', 'asc');sortingIconColor('useremail1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "useremail2" onclick = "addActiveSort('desc', 'useremail'); getLogDetails(1,notifSearch='','useremail', 'desc');sortingIconColor('useremail2')" style="font-size:18px"></i>
                                </th>
                                <th id="key5" headers="created" class="">
                                    Local Time
                                    <i class="fa fa-caret-down cursorPointer direction" id = "created" onclick = "addActiveSort('asc', 'created'); getLogDetails(1,notifSearch='','created', 'asc');sortingIconColor('created')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "created1" onclick = "addActiveSort('desc', 'created'); getLogDetails(1,notifSearch='','created', 'desc');sortingIconColor('created1')" style="font-size:18px"></i>
                                </th>
                                <th id="key6" headers="created" class="">
                                    GMT Time
                                    <i class="fa fa-caret-down cursorPointer direction" id = "created2" onclick = "addActiveSort('asc', 'created'); getLogDetails(1,notifSearch='','created', 'asc');sortingIconColor('created2')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "created3" onclick = "addActiveSort('desc', 'created'); getLogDetails(1,notifSearch='','created', 'desc');sortingIconColor('created3')" style="font-size:18px"></i>
                                </th>
                                <th id="key7" headers="refName" class="">
                                    Details
                                    <i class="fa fa-caret-down cursorPointer direction" id = "refName1" onclick = "addActiveSort('asc', 'refName'); getLogDetails(1, notifSearch = '','refName', 'asc');sortingIconColor('refName1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "refName2" onclick = "addActiveSort('desc', 'refName'); getLogDetails(1, notifSearch='','refName', 'desc');sortingIconColor('refName2')" style="font-size:18px"></i>
                                </th>
                                <th id="key8" headers="status" class="">
                                    Status
                                    <i class="fa fa-caret-down cursorPointer direction" id = "status1" onclick = "addActiveSort('asc', 'status'); getLogDetails(1, notifSearch = '', 'status', 'asc');sortingIconColor('status1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "status2" onclick = "addActiveSort('desc', 'status'); getLogDetails(1, notifSearch = '', 'status', 'desc');sortingIconColor('status1')" style="font-size:18px"></i>
                                </th>
                            </tr>
                        </thead>

                    </table>
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
        <h4>Export Audit Data</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="auditlog-range">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="btn-exportAuditLogData" onclick="exportAuditLog();">
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
                        <div class="UserSelection">
                            <select class="form-control selectpicker dropdown-submenu"  data-style="btn btn-info"  id="UserSelection" data-size="5">

                            </select>
                        </div>

                    </div>

                    <div class="form-group has-label">
                        <label>
                            Start Date
                        </label>
                        <input type="text" class="form-control datetimepicker" id="datefrom" name="datefrom"  >
                        <span style="color:red;" id="datefrom_err"></span>
                    </div>

                    <div class="form-group has-label">
                        <label>
                            End Date
                        </label>
                        <input type="text" class="form-control datetimepicker" id="dateto" name="dateto"  >
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

<style>
    .showbtn{
        margin-left: 119px;
    }

    .clearbtn{
        margin-left: 119px;
    }
</style>



