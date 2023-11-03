<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
?>
<style>
    /*.alert-info{
        background-color:#fd77a4;
        color:#fff;
    }*/
    .circleGrey {
        background-color: rgba(0,0,0,0.20);
    }

    table.dataTable tbody>tr.selected, table.dataTable tbody>tr>.selected{
        background-color: rgba(0, 0, 0, 0.05);
    }
    /*.dataTables_wrapper .table-striped tbody tr:nth-of-type(odd){
        background-color: rgba(0, 0, 0, 0.05);
    }*/


</style>
<input type="hidden" id="selectedsubnetmask" value="">
<input type="hidden" id="searchValue" value="<?php echo $_SESSION['searchValue'] ?>">
<input type="hidden" id="passlevel" value="<?php echo $_SESSION['passlevel'] ?>">
<input type="hidden" id="rparentName" value="<?php echo $_SESSION['rparentName'] ?>">
<input type="hidden" id="searchType" value="<?php echo $_SESSION['searchType'] ?>">
<input type="hidden" id="timeRemaing" value="0">
<?php
if (($_SESSION['searchType'] == 'Sites') || ($_SESSION['searchType'] == 'Groups')) {
    $diables = "readonly";
} else {
    $diables = "";
}
?>

<div class="content white-content commonTwo">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <div class="bullDropdown leftDropdown">
                            <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>
                        </div>
                        <div class="bullDropdown NDGroupsHide" style="display: none;">
                            <div class="dropdown">

                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item rightslide-container-hand addSubnetIp <?php echo setRoleForAnchorTag('addsubnet', 2); ?>" data-target="subnetip-add-container" href="#">Add subnet IP</a>
                                    <a class="dropdown-item impdetails_Edit <?php echo setRoleForAnchorTag('modifysubnet', 2); ?>" href="#">Modify Impersonation</a>
                                    <a class="dropdown-item impdetails_Delete <?php echo setRoleForAnchorTag('deletesubnet', 2); ?>" href="#">Delete subnet IP</a>
                                    <a class="dropdown-item <?php echo setRoleForAnchorTag('exportsubnet', 2); ?>" onclick="exportDetails();"  href="#">Export</a>
                                    <a class="dropdown-item deployaction <?php echo setRoleForAnchorTag('deploysubnet', 2); ?>"  href="#">Deploy</a>
                                    <a class="dropdown-item refreshpage"  href="#">Refresh</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="NDGroupsShow" style="display: none;">
                        Please select a site/machine to use Deployment
                    </div>
                    <div class="row clearfix two NDGroupsHide" style="display: none;">
                        <div class="col-md-12">
                            <div class="row clearfix">
                                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 left equalHeight" style="top: 32px;">
                                    <table class="nhl-datatable table table-striped" id="dtLeftList">
                                        <thead>
                                            <tr>
                                                <th id="subnet">Subnet IPs</th>
                                                <th id="scaned">Last scaned</th>
                                                <th id="action">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>

                            <div class="row clearfix">
                                <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 right equalHeight" style="top: 32px;">
                                    <img src="../assets/img/loader.gif" class="v8-loader" style="display: none;margin-left: 47%;margin-top: 17%;">
                                    <table class="nhl-datatable table table-striped dep_details" id="dtRightList">
                                        <thead>
                                            <tr>
                                                <th class="sorting_desc_disabled sorting_asc_disabled check" id="sort">
                                                    <!--                                                                                   <div class="form-check">
                                                                                                                                            <label class="form-check-label">
                                                                                                                                                <input class="form-check-input" id="all" type="checkbox">
                                                                                                                                                <span class="form-check-sign"></span>
                                                                                                                                            </label>
                                                                                                                                        </div>-->
                                                </th>
                                                <th id="mac">MAC Address</th>
                                                <th id="ip">IP Address</th>
                                                <th id="host">Host</th>
                                                <th id="client_available">Client Available</th>
                                                <th id="client_version">Client Version</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th class="sorting_desc_disabled sorting_asc_disabled check" id="sort_desc">
                                                </th>
                                                <th id="mac1">MAC Address</th>
                                                <th id="ip1">IP Address</th>
                                                <th id="host1">Host</th>
                                                <th id="client_available1">Client</th>
                                                <th id="client_version1">Client Version</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
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

    <!-- lg-9 md-6 sm-3 -->
    <div id="subnetip-add-container" class="rightSidenav leftSidenav" data-class="sm-3">
        <div class="card-title">
            <h4>Subnet IP and Impersonation Details </h4>
            <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="subnetip-add-container">&times;</a>
        </div>
        <div class="btnGroup" id="">
            <div class="icon-circle addingsubneturl">
                <div class="toolTip" id="" name="">
                    <i class="tim-icons icon-check-2"></i>
                    <span class="tooltiptext">Save</span>
                </div>
            </div>
        </div>


        <div class="form table-responsive white-content">
            <div class="sidebar">
                <div class="form-group has-label">
                    <label style="margin-left: 5%;margin-bottom: 0;">
                        Subnet Details 
                    </label>
                </div>
                <div class="form-group has-label">
                    <label style="margin-left: 5%;">
                        IP <span style="color:red">*</span> 
                    </label>
                    <input class="form-control" id="subnetUrl" type="text" name="subnetUrl" required style="width: 90%;margin-left: 5%;"/>
                </div>

                <div> &nbsp;</div>
                <div class="form-group has-label">
                    <label style="margin-left: 5%;margin-bottom: 0;">
                        Impersonation Details 
                    </label>
                </div>
                <div class="form-group has-label">
                    <label style="margin-left: 5%;">
                        User <span style="color:red"></span> 
                    </label>
                    <input  id="impuser" class="form-control" value="" <?php ?>  type="text" name="impuser" required style="width: 90%;margin-left: 5%;"/>
                </div>
                <div class="form-group has-label">
                    <label style="margin-left: 5%;">
                        Password <span style="color:red"></span> 
                    </label>
                    <input  id="imppwd" class="form-control" value="" <?php ?>  value="" type="password" name="imppwd" required style="width: 90%;margin-left: 5%;"/>
                </div>
                <div class="form-group has-label">
                    <label style="margin-left: 5%;">
                        Domain <span style="color:red"></span> 
                    </label>
                    <input type="text" id="impdomain" class="form-control" value="" <?php ?>  style="width: 90%;margin-left: 5%;"/>
                </div>

                <div>&nbsp;</div>
                <div class="button col-md-12 text-center">
                    <span id="error_disp" style="color:red;"></span>
                    <span id="error_disp_succ" style="color:green;"></span>
                </div> 
                <div>&nbsp;</div>
                <div class="button col-md-12 text-center">
                    <span id="subsuc_disp" style="color:green;"></span>
                </div> 

                <!-- <div class="button col-md-12 text-center submit">
                    <button type="button" class="swal2-confirm btn btn-success btn-sm" onclick="subnetUrl();" aria-label="">Add</button>
                </div>    -->

            </div>
        </div>
    </div>
</div>

<div id="deployimpdetails-add-container" class="rightSidenav leftSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Deploy </h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="deployimpdetails-add-container">&times;</a>
    </div>


    <div class="btnGroup save_deploy" id="toggleButton">
        <div class="icon-circle" id="deploy_main">
            <div class="toolTip"  name="">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>


    </div>

    <div class="form table-responsive white-content">
        <div class="sidebar">
            <div class="form-group has-label">
                <label style="margin-left: 5%;margin-bottom: 0;">
                    Subnet Details 
                </label>
            </div>
            <div class="form-group has-label">
                <label style="margin-left: 5%;">
                    Subnet IP <span style="color:red">*</span> 
                </label>
                <input class="form-control" id="dep_subnetUrl" type="text" name="subnetUrl" readonly required value="" style="width: 90%;margin-left: 5%;"/>
            </div>

            <div> &nbsp;</div>
            <div class="form-group has-label">
                <label style="margin-left: 5%;margin-bottom: 0;">
                    Impersonation Details 
                </label>
            </div>

            <div class="form-group has-label">
                <label style="margin-left: 5%;">
                    User <span style="color:red">*</span>  </label><input type="text" id="dep_impuser" class="form-control" value="" style="width: 90%;margin-left: 5%;">
            </div>

            <div class="form-group has-label">
                <label style="margin-left: 5%;">
                    Password <span style="color:red">*</span> </label> <input type="password" id="dep_imppwd" class="form-control" value="" style="width: 90%;margin-left: 5%;"> 
            </div>

            <div class="form-group has-label">
                <label style="margin-left: 5%;">
                    Domain <span style="color:red">*</span>  </label> <input type="text" id="dep_impdomain" class="form-control" value="" style="width: 90%;margin-left: 5%;"> 
            </div>

            <div>&nbsp;</div>
            <div class="button col-md-12 text-center">
                <span id="error_dispDip" style="color:red;"></span>
            </div> 
            <!--                        <div class="button col-md-12 text-center" id="dipl_updaMsg" style="color:green;display:none;">
                                        <span>Added Impersonation Details please <a href="#" onclick="deployconfirm();" style="    font-size: 11px;color:#5882FA;">click here</a> to Deploy</span>
                                    </div> -->

            <!--<div class="text-center" class="deploymsg" style="display:none;">-->

            <!--</div>--> 
            <div class="form-group has-label" style="padding-left: 25px;">
                <span class="deploymsg">If entered above impersonation details are valid, please <button type="button" class="swal2-confirm btn btn-success btn-sm" onclick="deployconfirm();" aria-label="">click here</button>  to Deploy</span>
            </div> 


            <div class="button col-md-12 text-center" id="dipl_impMsg" style="display:none;">
                <span>Please Add Impersonation Details and click on Save</span>
            </div> 



        </div>
    </div>
</div>
</div>



<div id="modifyimpdetails-add-container" class="rightSidenav leftSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Edit Impersonation Details </h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="modifyimpdetails-add-container">&times;</a>
    </div>
    <div class="btnGroup" id="editOption">
        <div class="icon-circle">
            <div class="toolTip">
                <i class="tim-icons icon-pencil"></i>
                <span class="tooltiptext">Edit</span>
            </div>
        </div>
    </div>

    <div class="btnGroup " id="toggleButton1">
        <div class="icon-circle circleGrey" id="edit_impdetails">
            <div class="toolTip"  name="">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>

        <div class="icon-circle closecross" id="toggleEdit" >
            <div class="toolTip">
                <i class="tim-icons icon-simple-remove"></i>
                <span class="tooltiptext">Toggle edit mode</span>
            </div>
        </div>
    </div>

    <div class="form table-responsive white-content">
        <div class="sidebar">

            <div class="form-group has-label">
                <label style="margin-left: 5%;margin-bottom: 0;">
                    Impersonation Details 
                </label>
            </div>

            <div class="form-group has-label">
                <label style="margin-left: 5%;">
                    User <span style="color:red">*</span>  </label><input type="text" id="edit_impuser" class="form-control" value="" style="width: 90%;margin-left: 5%;">
            </div>

            <div class="form-group has-label">
                <label style="margin-left: 5%;">
                    Password <span style="color:red">*</span> </label> <input type="password" id="edit_imppwd" class="form-control" value="" style="width: 90%;margin-left: 5%;"> 
            </div>

            <div class="form-group has-label">
                <label style="margin-left: 5%;">
                    Domain <span style="color:red">*</span>  </label> <input type="text" id="edit_impdomain" class="form-control" value="" style="width: 90%;margin-left: 5%;"> 
            </div>

            <div>&nbsp;</div>
            <div class="button col-md-12 text-center">
                <span id="error_dis_update" style="color:red;"></span>
                <span id="succ_dis_update" style="color:green;"></span>
            </div> 

            <!-- <div class="button col-md-12 text-center submit">
                <button type="button" class="swal2-confirm btn btn-success btn-sm" onclick="modify_imp();" aria-label="">Update</button>
            </div>    -->

        </div>
    </div>
</div>
<div id="reset-Scan-container" class="rightSidenav leftSidenav" data-class="sm-3">
    <div class="card-title">

        <a href="javascript:void(0)" class="closebtn reset-Scan-container-close" data-target="reset-Scan-container">&times;</a>
    </div>
    <div>&nbsp;</div>

    <div class="form table-responsive white-content">
        <div class="sidebar">


            <h3 id="timer_msg"></h3>
        </div>
    </div>
</div>
</div>





<div id="takeaction-add-container" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Take Action</h4>
        <a href="javascript:void(0)" class="closebtn kbs-container-close" data-target="takeaction-add-container">&times;</a>
    </div>

    <div class="form table-responsive white-content">
        <form id="">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label>
                            <ul role="listbox" aria-expanded="true">
                                <li data-original-index="2">
                                    <a style="padding: 2px 8px 5px 0px;" tabindex="0" class="hide_hover" data-tokens="null" onclick="actionSelection('approve');" role="option" aria-disabled="false" aria-selected="false">
                                        <span class="text hide_hover">Approve Update</span><span class="check-mark"></span>
                                    </a>
                                </li>
                                <li data-original-index="3">
                                    <a style="padding: 2px 8px 5px 0px;" tabindex="0" class="hide_hover" data-tokens="null" onclick="actionSelection('decline');
                                       "role="option" aria-disabled="false" aria-selected="false">
                                        <span class="text hide_hover">Decline Update</span><span class="check-mark"></span>
                                    </a>
                                </li>
                                <li data-original-index="5" class="disabled">
                                    <a style="padding: 2px 8px 5px 0px;" tabindex="-1" class="hide_hover" data-tokens="null" onclick="actionSelection('decline');" role="option" href="#" aria-disabled="true" aria-selected="false">
                                        <span class="text hide_hover">Retry Update</span><span class="check-mark"></span>
                                    </a>
                                </li>
                            </ul>

                        </label>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

</body>
</html>