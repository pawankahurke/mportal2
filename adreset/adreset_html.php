<!--<script src="http://code.jquery.com/jquery-1.11.1.min.js"></script>-->
<style>
    .alert-info {
        /*background-color:#fd77a4;*/
        color: #fff;
    }

    .listbrdr_btm {
        border-bottom: solid 1px #ddd;
        font-size: 12px;
        color: rgba(34, 42, 66, 0.7) !important;
    }

    .listbrdr_btm a:hover,
    .listbrdr_btm a:active,
    .listbrdr_btm a:focus {
        background: #eee;
    }

    /* #dtLeftList table, th, td {
        padding: 12px 0px 6px 7px !important;
    }
    .dep_details table, th, td {
        padding: 15px 0px 6px 7px !important;
    } */

    table.dataTable tbody>tr.selected,
    table.dataTable tbody>tr>.selected {
        background-color: #eee !important;
    }
</style>
<input type="hidden" value="<?php echo url::toText($_SESSION['searchType']); ?>" id="searchType">
<input type="hidden" value="<?php echo url::toText($_SESSION['searchValue']); ?>" id="searchValue">

<div class="content white-content adreset">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <?php
                    //   nhRole::checkRoleForPage('adpassword');
                    $res = true; // nhRole::checkModulePrivilege('adpassword');
                    if ($res) {
                    ?>
                        <!-- <div class="toolbar">
                        <div class="bullDropdown leftDropdown">
                                        <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>
                        </div>
                        <div class="bullDropdown">
                            <div class="dropdown">

                            </div>
                        </div>
                    </div> -->

                        <div class="row clearfix innerPage pt-0">
                            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 lf-rt-br equalHeight">

                                <h4>Work Groups</h4>
                                <ul>
                                    <li class="listbrdr_btm"><a class="dropdown-item wg_config_que" href="#">Configure Questions</a></li>
                                    <li class="listbrdr_btm"><a class="dropdown-item wg_chng_pwd" href="#">Change Password</a></li>
                                    <li class="listbrdr_btm"><a class="dropdown-item wg_securt_ans" href="#">Reset Security Answers</a></li>
                                </ul>

                                <h4>Domain</h4>
                                <ul>
                                    <li class="listbrdr_btm"><a class="dropdown-item dom_config_que" href="#">Configure Questions</a></li>
                                    <li class="listbrdr_btm"><a class="dropdown-item dom_chng_pwd" href="#">Change Password</a></li>
                                    <li class="listbrdr_btm"><a class="dropdown-item dom_securt_ans" href="#">Reset Security Answers</a></li>
                                    <li class="listbrdr_btm"><a class="dropdown-item dom_unlck_accnt" href="#">Unlock Account</a></li>
                                </ul>
                            </div>

                            <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 rt-lf equalHeight text-center">
                                <!-- right content -->
                                <span>
                                    <img src="../assets/img/4826321.png" alt="" style="width: 8rem;opacity: 0.5;"></span>

                                <span>
                                    <h5>Please select an option from the left hand menu to continue</h5>
                                </span>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                    <!--                        </div>
                                        </div>-->
                    <!-- end content-->
                </div>
                <!--  end card  -->
            </div>
            <!-- end col-md-12 -->
        </div>
        <!-- end row -->
    </div>

    <!-- lg-9 md-6 md-6 -->
    <div id="wg-config-questions" class="rightSidenav leftSidenav wg-conf">
        <div class="card-title border-bottom">
            <div class="head-ttle"></div>

            <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="wg-config-questions">&times;</a>
        </div>
        <div class="btnGroup chckcircle" id="" style="display:none">
            <div class="icon-circle" id="chck">
                <div class="toolTip" id="" name="">
                    <i class="tim-icons icon-check-2"></i>
                    <span class="tooltiptext">Save</span>
                </div>
            </div>
        </div>
        <div class="btnGroup proceedcircle" id="" style="display:none">
            <div class="icon-circle" id="proceed">
                <div class="toolTip" id="" name="">
                    <i class="tim-icons icon-check-2"></i>
                    <span class="tooltiptext">Save</span>
                </div>
            </div>
        </div>
        <div class="btnGroup resetPasscircle" id="" style="display:none">
            <div class="icon-circle" id="resetPass">
                <div class="toolTip" id="" name="">
                    <i class="tim-icons icon-check-2"></i>
                    <span class="tooltiptext">Save</span>
                </div>
            </div>
        </div>
        <input type="hidden" id="selected_ad" value="">
        <div class="form table-responsive white-content">
            <div class="sidebar">

                <div class="form-group has-label username_div">
                    <label style="margin-left: 5%;">
                        Username: <span style="color:red">*</span>
                    </label>
                    <input type="text" id="domain_userName" class="form-control" value="" <?php echo $diables ?> style="width: 90%;margin-left: 5%;" />
                </div>
                <div class="form-group has-label domain_div">
                    <label style="margin-left: 5%;">
                        Domain: <span style="color:red">*</span>
                    </label>
                    <input type="text" id="domain" class="form-control" value="" <?php echo $diables ?> style="width: 90%;margin-left: 5%;" />

                </div>
                <div align="center" class="margin-top100">
                    <div id="loader" class="loader" data-qa="loader">
                        <img src="../assets/img/loader2.gif" />
                    </div>
                </div>
                <div class="button col-md-12 text-center">
                    <span id="wgchngpwd_err" style="color:red;"></span>
                    <span id="unlck_Accnterr" style="color:red;"></span>
                    <span id="unlck_Accntsucc" style="color:green;"></span>
                </div>

                <div class="button col-md-12 text-center submit ">
                    <!-- <button type="button" class="swal2-confirm btn btn-success btn-sm"  aria-label="" id="proceed">Proceed</button> -->
                </div>

                <div class="sec_que">

                    <div class="form-group has-label">
                        <label style="margin-left: 5%; margin-bottom: 20px;">
                            Question 1 <span style="color:red">*</span>
                        </label>
                        <input type="text" id="q1" class="form-control schleedelay" value="" style="width: 90%;margin-left: 5%;" />
                    </div>
                    <div class="form-group has-label">
                        <label style="margin-left: 5%; margin-bottom: 20px;">
                            Question 2 <span style="color:red">*</span>
                        </label>
                        <input type="text" id="q2" class="form-control schleedelay" value="" style="width: 90%;margin-left: 5%;" />
                    </div>
                    <div class="form-group has-label">
                        <label style="margin-left: 5%; margin-bottom: 20px;">
                            Question 3 <span style="color:red">*</span>
                        </label>
                        <input type="text" id="q3" class="form-control schleedelay" value="" style="width: 90%;margin-left: 5%;" />
                    </div>
                    <div class="form-group has-label">
                        <label style="margin-left: 5%; margin-bottom: 20px;">
                            Question 4 <span style="color:red">*</span>
                        </label>
                        <input type="text" id="q4" class="form-control schleedelay" value="" style="width: 90%;margin-left: 5%;" />
                    </div>
                    <div class="form-group has-label">
                        <label style="margin-left: 5%; margin-bottom: 20px;">
                            Question 5 <span style="color:red">*</span>
                        </label>
                        <input type="text" id="q5" class="form-control schleedelay" value="" style="width: 90%;margin-left: 5%;" />
                    </div>
                    <div class="form-group label-floating" id="statuserr" style="display: none;padding-left: 21px !important;padding-right: 4%;padding-top: 8px;">
                        <label>Status</label>
                        <span id="status_perr" style="color: red;"></span>
                    </div>
                    <div class="form-group label-floating" id="statussuc" style="display: none;padding-left: 21px !important;padding-right: 4%;padding-top: 8px;">
                        <label>Status</label>
                        <div id="status_success" style="color: green;"></div>
                    </div>

                    <div class="button col-md-12 text-center">
                        <span id="secure_queserr" style="color:red;"></span>
                        <span id="secure_quessucc" style="color:green;"></span>
                    </div>

                    <div>&nbsp;</div>

                    <div class="button col-md-12 text-center submit">
                        <input type="hidden" name="username" id="username" />
                        <input type="hidden" name="dom" id="dom" />
                        <input type="hidden" name="authenticate" value="authenticate" />
                        <!-- <button type="button" class="swal2-confirm btn btn-success btn-sm"  aria-label="" id="chck">Submit</button> -->
                    </div>
                </div>

                <!-- internal secu quetions div for show hide -->

                <div class="chngpwd_div" style="display:none;">
                    <div class="form-group has-label">
                        <label style="margin-left: 5%;">
                            New password <span style="color:red">*</span>
                        </label>
                        <input type="password" id="newPass" class="form-control" value="" <?php echo $diables ?> style="width: 200%;margin-left: 5%;" />

                    </div>

                    <div class="form-group has-label">
                        <label style="margin-left: 5%;">
                            Confirm Password <span style="color:red">*</span>
                        </label>
                        <input type="password" id="confirmPass" class="form-control" value="" <?php echo $diables ?> style="width: 200%;margin-left: 5%;" />

                    </div>

                    <div class="button col-md-12 text-center">
                        <span id="secure_queserr" style="color:red;"></span>
                        <span id="secure_quessucc" style="color:green;"></span>
                    </div>

                    <div>&nbsp;</div>

                    <div class="button col-md-12 text-center submit">
                        <input type="hidden" name="username" id="username" />
                        <input type="hidden" name="dom" id="dom" />
                        <input type="hidden" name="authenticate" value="authenticate" />
                        <!-- <button type="button" class="swal2-confirm btn btn-success btn-sm"  aria-label="" id="resetPass">Submit</button> -->
                    </div>
                </div>
                <!-- internalchng pwd div for show hide -->
            </div>
        </div>
    </div>
</div>