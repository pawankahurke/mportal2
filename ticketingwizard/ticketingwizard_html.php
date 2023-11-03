<div class="content white-content">
    <div class="row" style="margin-top: 9px;">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <?php
                    //   nhRole::checkRoleForPage('customer');
                    $res = true; //nhRole::checkModulePrivilege('customer');
                    if ($res) {
                    ?>
                        <!-- loader -->
                        <div id="loader" class="loader" data-qa="loader" class="w-100" style="position: absolute;bottom: 50%;right:50%;">
                            <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                        </div>
                        <div class="toolbar">
                            <!--        Here you can write extra buttons/actions for the toolbar              -->
                            <!-- <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>
                            </div>
                        </div> -->

                            <!-- <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item rightslide-container-hand <?php echo setRoleForAnchorTag('ticketingconf', 2); ?>" data-bs-target="ticketing-configuration" onclick="crmConfigure();">Configure</a>
                                    <a class="dropdown-item <?php echo setRoleForAnchorTag('actiondetails', 2); ?>" onclick="actionDetails();">Action Details</a>
                                    <a class="dropdown-item <?php echo setRoleForAnchorTag('ticketingexport', 2); ?>" onclick="exportTicketingDetails()" id="ticketingExport">Export</a>
                                </div>
                            </div>
                        </div> -->
                        </div>

                        <table class="nhl-datatable table table-striped" id="ticketingDataGrid">
                            <thead>
                                <tr>
                                    <th>
                                      Ticket Name
                                      <i class="fa fa-caret-down cursorPointer direction" id="ticketSub" onclick="addActiveSort('asc', 'ticketSub'); getTicketingDetails(1, '', 'ticketSub', 'asc');sortingIconColor('ticketSub')" style="font-size:18px"></i>
                                      <i class="fa fa-caret-up cursorPointer direction" id="ticketSub1" onclick="addActiveSort('desc', 'ticketSub'); getTicketingDetails(1, '', 'ticketSub', 'desc');sortingIconColor('ticketSub1')" style="font-size:18px"></i>
                                    </th>
                                    <th>
                                      Client Time
                                      <i class="fa fa-caret-down cursorPointer direction" id="eventDateTime" onclick="addActiveSort('asc', 'eventDateTime'); getTicketingDetails(1, '', 'eventDateTime', 'asc');sortingIconColor('eventDateTime')" style="font-size:18px"></i>
                                      <i class="fa fa-caret-up cursorPointer direction" id="eventDateTime1" onclick="addActiveSort('desc', 'eventDateTime'); getTicketingDetails(1, '', 'eventDateTime', 'desc');sortingIconColor('eventDateTime1')" style="font-size:18px"></i>
                                    </th>
                                    <th>
                                      Created Time
                                      <i class="fa fa-caret-down cursorPointer direction" id="crontime" onclick="addActiveSort('asc', 'crontime'); getTicketingDetails(1, '', 'crontime', 'asc');sortingIconColor('crontime')" style="font-size:18px"></i>
                                      <i class="fa fa-caret-up cursorPointer direction" id="crontime1" onclick="addActiveSort('desc', 'crontime'); getTicketingDetails(1, '', 'crontime', 'desc');sortingIconColor('crontime1')" style="font-size:18px"></i>
                                    </th>
                                    <th>
                                      Device Name
                                      <i class="fa fa-caret-down cursorPointer direction" id="machineName" onclick="addActiveSort('asc', 'machineName'); getTicketingDetails(1, '', 'machineName', 'asc');sortingIconColor('machineName')" style="font-size:18px"></i>
                                      <i class="fa fa-caret-up cursorPointer direction" id="machineName1" onclick="addActiveSort('desc', 'machineName'); getTicketingDetails(1, '', 'machineName', 'desc');sortingIconColor('machineName1')" style="font-size:18px"></i>
                                    </th>
                                    <th>
                                      Ticket ID
                                      <i class="fa fa-caret-down cursorPointer direction" id="ticketId" onclick="addActiveSort('asc', 'ticketId'); getTicketingDetails(1, '', 'ticketId', 'asc');sortingIconColor('ticketId')" style="font-size:18px"></i>
                                      <i class="fa fa-caret-up cursorPointer direction" id="ticketId1" onclick="addActiveSort('desc', 'ticketId'); getTicketingDetails(1, '', 'ticketId', 'desc');sortingIconColor('ticketId1')" style="font-size:18px"></i>
                                    </th>
                                    <th>
                                      Status
                                      <i class="fa fa-caret-down cursorPointer direction" id="ticketType" onclick="addActiveSort('asc', 'ticketType'); getTicketingDetails(1, '', 'ticketType', 'asc');sortingIconColor('ticketType')" style="font-size:18px"></i>
                                      <i class="fa fa-caret-up cursorPointer direction" id="ticketType1" onclick="addActiveSort('desc', 'ticketType'); getTicketingDetails(1, '', 'ticketType', 'desc');sortingIconColor('ticketType1')" style="font-size:18px"></i>
                                    </th>
                                    <th>
                                      Ticket Type
                                      <i class="fa fa-caret-down cursorPointer direction" id="ticketType2" onclick="addActiveSort('asc', 'ticketType'); getTicketingDetails(1, '', 'ticketType', 'asc');sortingIconColor('ticketType2')" style="font-size:18px"></i>
                                      <i class="fa fa-caret-up cursorPointer direction" id="ticketType3" onclick="addActiveSort('desc', 'ticketType'); getTicketingDetails(1, '', 'ticketType', 'desc');sortingIconColor('ticketType3')" style="font-size:18px"></i>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    <?php
                    }
                    ?>
                    <div id="largeDataPagination"></div>
                </div>
                <input type="hidden" id="ticketid" />
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>


<!-- CRM configure UI starts  -->
<div id="ticketing-configuration" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Add/Update Ticketing Wizard</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="ticketing-configuration">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="addDeploymentSiteBtn" onclick="configureCRM()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">
                <div class="form-group has-label">
                    <label>Select CRM</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select CRM" data-size="3" id="tw-crmtype" name="tw-crmtype">
                        <option value="SN" selected="">Service Now</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Customer</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Customer" data-size="3" id="tw-customer" name="tw-customer" onchange="getConfiguredCrmData()">

                    </select>
                </div>

                <div class="form-group has-label">
                    <label for="tw-crmurl">URL</label><em class="error" id="err_crmurl"></em>
                    <input type="text" name="tw-crmurl" id="tw-crmurl" class="form-control" placeholder="Enter crm url">
                </div>


                <div class="form-group has-label">
                    <label for="tw-crmusername">Username</label><em class="error" id="err_username"></em>
                    <input type="text" name="tw-crmusername" id="tw-crmusername" class="form-control" placeholder="Enter crm username" autocomplete="off">
                </div>

                <div class="form-group has-label">
                    <label for="tw-crmpassword">Password</label><em class="error" id="err_username"></em>
                    <input type="password" name="tw-crmpassword" id="tw-crmpassword" class="form-control" placeholder="Enter crm password" autocomplete="off">
                </div>
                <hr />

                <div class="form-check mt-3 distClass" style="">
                    <label class="form-check-label">
                        <input class="form-check-input" type="checkbox" id="tw-tickEnable" name="tw-tickEnable" value="">
                        <span class="form-check-sign"></span> Enable Ticketing Wizard
                    </label>
                </div>
                <hr />

                <h4>
                    Ticket Creation Options
                </h4>

                <div style="display: flex;justify-content: space-between;cursor: pointer; height: 25px;" id="resolutionTitleBlock">
                  <h4>Resolutions</h4>
                  <i class="fa fa-angle-down" aria-hidden="true" id="resolutionTitleBlockI" style="font-size: 18px"></i>
                </div>
                <div style="display: none" id="resolutionBlock" class="mb-2">
                  <div class="form-check mt-3 distClass" style="">
                    <label class="form-check-label">
                      <input class="form-check-input" type="checkbox" id="tw-tickAutoheal" name="tw-tickAutoheal" value="">
                      <span class="form-check-sign"></span> AutoHeal Resolutions
                    </label>
                  </div>
                  <div class="form-check mt-3 distClass" style="">
                    <label class="form-check-label">
                      <input class="form-check-input" type="checkbox" id="tw-tickSelfhelp" name="tw-tickSelfhelp" value="">
                      <span class="form-check-sign"></span> Selfhelp Resolutions
                    </label>
                  </div>
                  <div class="form-check mt-3 distClass" style="">
                    <label class="form-check-label">
                      <input class="form-check-input" type="checkbox" id="tw-tickSchedule" name="tw-tickSchedule" value="">
                      <span class="form-check-sign"></span> Scheduled Resolutions
                    </label>
                  </div>
                </div>

<!--                <div class="form-check mt-3 distClass" style="">-->
<!--                    <label class="form-check-label">-->
<!--                        <input class="form-check-input" type="checkbox" id="tw-tickNotification" name="tw-tickNotification" value="">-->
<!--                        <span class="form-check-sign"></span> Notification-->
<!--                    </label>-->
<!--                </div>-->
                <div style="display: flex;justify-content: space-between;cursor: pointer; height: 25px;" id="notificationTitleBlock">
                  <h4>
                    Notifications
                  </h4>
                  <i class="fa fa-angle-down" aria-hidden="true" id="notificationTitleBlockI" style="font-size: 18px"></i>
                </div>

              <div style="display: none" id="notificationBlock" class="mb-2">
                <div class="form-check mt-3 distClass" style="">
                  <label class="form-check-label">
                    <input class="form-check-input" type="checkbox" id="tw-tickNotificationP1" name="tw-tickNotification" value="">
                    <span class="form-check-sign"></span> P1
                  </label>
                </div>

                <div class="form-check mt-3 distClass" style="">
                  <label class="form-check-label">
                    <input class="form-check-input" type="checkbox" id="tw-tickNotificationP2" name="tw-tickNotification" value="">
                    <span class="form-check-sign"></span> P2
                  </label>
                </div>

                <div class="form-check mt-3 distClass" style="">
                  <label class="form-check-label">
                    <input class="form-check-input" type="checkbox" id="tw-tickNotificationP3" name="tw-tickNotification" value="">
                    <span class="form-check-sign"></span> P3
                  </label>
                </div>
              </div>

                <div class="form-group has-label">
                    <label class="crmConfigureErr"></label>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- CRM configure UI ends -->


<!-- JSON Payload configuration Data UI starts  -->
<div id="ticketing-payload" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>JSON Payload Data</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="ticketing-payload">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="createPayload" onclick="savePayloadInformation();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">

                <div class="form-group">
                    <label>Create JSON Data</label>
                    <textarea id="createJsonPayload" name="createJsonPayload" class="form-control" rows="60" style="border: 1px solid #ccc; min-height: 200px !important;"></textarea>
                    <!--<img id="emailDistributeLoader" src="../assets/img/loader.gif">-->
                </div>

                <div class="form-group">
                    <label>Closed JSON Data</label>
                    <textarea id="closedJsonPayload" name="closedJsonPayload" class="form-control" rows="60" style="border: 1px solid #ccc; min-height: 200px !important;"></textarea>
                    <!--<img id="emailDistributeLoader" src="../assets/img/loader.gif">-->
                </div>

                <input type="hidden" id="crmcustomer" name="crmcustomer" value="" />

                <div class="form-group has-label">
                    <label class="crmCreatePayloadErr"></label>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- JSON Payload configuration Data UI ends -->

<!-- Create JSON Payload Data UI starts  -->
<div id="ticketing-createjson" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Create JSON payload Data</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="ticketing-createjson">&times;</a>
    </div>

    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label>Create JSON Data</label>
                    <textarea readonly="" id="createJsonPayloadData" name="createJsonPayloadData" class="form-control" rows="60" style="border: 1px solid #ccc; min-height: 460px !important;"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Create JSON Payload Data UI ends -->

<!-- Closed JSON Payload Data UI starts  -->
<div id="ticketing-closedjson" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Closed JSON payload Data</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="ticketing-closedjson">&times;</a>
    </div>

    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label>Closed JSON Data</label>
                    <textarea readonly="" id="closedJsonPayloadData" name="closedJsonPayloadData" class="form-control" rows="60" style="border: 1px solid #ccc; min-height: 460px !important;"></textarea>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Closed JSON Payload Data UI ends -->

<!-- Action Details UI starts  -->
<div id="ticketing-actiondetails" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Action Details : <span id="actiontype"></span></h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="ticketing-actiondetails">&times;</a>
    </div>

    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">
                <div class="form-group">

                </div>
            </div>
        </div>
    </div>
</div>
<!-- Action Details UI ends -->

<style>
    #ticketingDataGrid_filter {
        display: none
    }

    .showbtn {}
</style>
