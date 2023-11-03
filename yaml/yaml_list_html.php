<?php
$userSites = isset($_SESSION['user']['user_sites']) ? $_SESSION['user']['user_sites'] : array();
?><?php
    include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
    include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
    ?>
<div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <div class="bullDropdown leftDropdown">
                            &nbsp;
                        </div>

                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a id="add-yaml" class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('addalert', 2); ?>" href="javascript:void(0)">Create a new Configuration</a>
                                    <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('deletealert', 2); ?>" id="delete-yaml" href="javascript:void(0)">Delete</a>
                                </div>
                            </div>
                        </div>

                        <table id="YamlListGrid" class="nhl-datatable table table-striped">
                            <thead>
                                <tr>
                                    <th class="id" style="width:2%">Id</th>
                                    <th class="platfrom" style="width:10%">Name</th>
                                    <th class="type" style="width:10%">Type</th>
                                    <th class="created_by" style="width:10%">Created by</th>
                                    <th class="modified_by" style="width:10%">Modified by</th>
                                    <th class="created" style="width:10%">Created</th>
                                    <th class="modified" style="width:10%">Modified</th>
                                </tr>
                            </thead>

                        </table>

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

<div id="rsc-add-yaml" class="rightSidenav" data-class="md-6">
    <div class="card-title">
        <h4 id="createYamlTitle"></h4>
        <a href="javascript:void(0)" class="closebtn" data-target="rsc-add-yaml">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="create-yaml">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <form id="addYamlForm" action="post" action="createYaml.php" onsubmit="return createYamlEvent($(this), event)">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <span class="error" id="required_yamlname">*</span>
                        <label for="yamlname">Name</label>
                        <input class="form-control" class="form-check-input" id="yamlname" name="yamlname" type="text" localized="" placeholder="Enter the name" data-required="true">
                    </div>
                    
                    <?php 
                        if(
                            (isset($_SESSION['user']['roleValue']['actypecompliance']) && is_numeric($_SESSION['user']['roleValue']['actypecompliance']) && intval($_SESSION['user']['roleValue']['actypecompliance']) == 2)
                            ||
                            (isset($_SESSION['user']['roleValue']['actypenotification']) && is_numeric($_SESSION['user']['roleValue']['actypenotification']) && intval($_SESSION['user']['roleValue']['actypenotification']) == 2 )
                        ){
                    ?>
                    <div class="form-group has-label">
                        <span class="error">*</span>
                        <label for="nhtype">Type</label>
                        <div>
                            <select id="nhtype" name="nhtype" class="selectpicker" title="Select Type" data-size="5" data-width="100%"  data-required="true">
                                <?php if(isset($_SESSION['user']['roleValue']['actypecompliance']) && is_numeric($_SESSION['user']['roleValue']['actypecompliance']) && intval($_SESSION['user']['roleValue']['actypecompliance']) == 2 ){?><option value="Compliance">Compliance</option> <?php } ?>
                                <?php if(isset($_SESSION['user']['roleValue']['actypenotification']) && is_numeric($_SESSION['user']['roleValue']['actypenotification']) && intval($_SESSION['user']['roleValue']['actypenotification']) == 2 ){?><option value="Notification">Notification</option> <?php } ?>
                            </select>
                        </div>
                    </div>
                    <?php } ?>
                    
                    <div class="form-group has-label">
                        <span class="error">*</span>
                        <label for="scope">Access</label>
                        <div>
                            <select id="scope" name="scope" class="selectpicker" title="Select Scope" data-size="5" data-width="100%" data-required="true">
                                <option value="global">Global</option>
                                <option value="restricted">Only to current user</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group has-label compl-box" style="display: none; ">
                        <span class="error" >*</span>
                        <label for="compliance-name">Compliance Name</label>
                        <input class="form-control" id="compliance-name" name="compliance-name" type="text" localized="" placeholder="Enter compliance name"> 
                    </div>

                    <div class="form-group has-label compl-box" style="display: none;">
                        <span class="error">*</span>
                        <label for="compliance-category">Compliance Category</label>
                        <select id="compliance-category" name="compliance-category" class="selectpicker" title="Select Compliance Category" data-size="5" data-width="100%">
                            <option value="Ok">Ok</option>
                            <option value="Warning">Warning</option>
                            <option value="Alert">Alert</option>
                        </select>
                    </div>

                    <div class="form-group has-label compl-box" style="display: none;"  data-required="true">
                        <span class="error">*</span>
                        <label for="compliance-item">Compliance Item</label>
                        <select id="compliance-item" name="compliance-item" class="selectpicker" title="Select Compliance Item" data-size="5" data-width="100%">
                            <option value="Security">Security</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Resource">Resource</option>
                            <option value="Events">Events</option>
                            <option value="Availability">Availability</option>
                        </select>
                    </div>
                    
                    <div class="form-group has-label">
                        <span class="error" id="required_query-string">*</span>
                        <label for="query-string">Query String</label>
                        <input class="form-control" id="query-string" name="query-string" type="text" localized=""  data-required="true"> 
                    </div>
                    
                    <div class="form-group has-label">
                        <span class="error">*</span>
                        <label for="index-name">Type</label>
                        <div>
                            <select id="index-name" name="index-name" class="selectpicker" title="Select Type" data-size="5" data-width="100%"  data-required="true">
                                <option value="patches_*">Patch</option>
                                <option value="swd_*">Swd</option>
                                <option value="events_*">Events</option>
                                <option value="assets_*">Asset</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group has-label">
                        <span class="error" id="required_number-of-events">*</span>
                        <label for="number-of-events">Number of event</label>
                        <input class="form-control my-num" id="number-of-events"  name="number-of-events" type="text" localized="" placeholder="Enter number or event"  data-required="true" data-numeric="true"> 
                    </div>

                    <div class="form-group has-label">
                        <span class="error" id="required_time-frame-value">*</span>
                        <label for="number-of-events">Time frame</label>
                        <div class="row">
                            <div class="col-sm-12">
                                <select id="time-frame-type" name="time-frame-type" class="selectpicker" title="Select Timeframe Type" data-size="5" data-width="100%"  data-required="true">
                                    <option value="minutes">Minutes</option>
                                    <option value="hours">Hours</option>
                                    <option value="days">Days</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <input class="form-control my-num" id="time-frame-value" name="time-frame-value" type="text" localized="" placeholder="Enter time frame value" data-required="true" data-numeric="true"> 
                            </div>
                        </div>
                    </div>

                    <div class="form-group has-label schdBox">
                        <h5 style="margin-top: 15px;"><strong>Schedule</strong></h5>
                    </div>

                    <div class="form-group has-label">
                        <span class="error" id="required_cornminute">*</span>
                        <label for="cornminute">Minute</label>
                        <input class="form-control my-num" id="cornminute" name="cornminute" type="text" localized="" value="*/5" placeholder="Enter schedule minute"  data-required="true" data-typecorn="true"> 
                    </div>

                    <div class="form-group has-label">
                        <span class="error" id="required_cornhour">*</span>
                        <label for="cornhour">Hour</label>
                        <input class="form-control my-num" id="cornhour" name="cornhour" type="text" localized="" value="*" placeholder="Enter schedule hour"  data-required="true" data-typecorn="true">
                    </div>

                    <div class="form-group has-label">
                        <span class="error" id="required_corndays">*</span>
                        <label for="corndays">Day</label>
                        <input class="form-control my-num" id="corndays" name="corndays" type="text" localized="" value="*" placeholder="Enter schedule day"  data-required="true" data-typecorn="true"> 
                    </div>

                    <div class="form-group has-label ">
                        <span class="error" id="required_cornweekely">*</span>
                        <label for="cornweekly">Weekly</label>
                        <input class="form-control" id="cornweekly" name="cornweekly" type="text" localized="" value="*" placeholder="Enter schedule week"  data-required="true" data-typecorn="true"> 
                    </div>

                    <div class="form-group has-label">
                        <span class="error" id="required_cornmonth">*</span>
                        <label for="cornmonth">Month</label>
                        <input class="form-control" id="cornmonth" name="cornmonth" type="text" localized="" value="*" placeholder="Enter schedule month"  data-required="true" data-typecorn="true"> 
                    </div>
                    
                    <div class="form-group row text-center" localized="">
                        <button class="btn btn-info" type="submit" style="display:none">Create</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="button col-md-12 text-center slider-feedwrapper">
        <span id="checkavail" localized="" class="inslider-feed error tm0"></span>
    </div>
</div>

<style>
    .my-num {padding: 0px 18px 0px 18px !important; }
</style>