<!-- content starts here  -->
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
?>
<div class="content white-content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="toolbar">
                        <!--        Here you can write extra buttons/actions for the toolbar              -->
<!--                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <span>Displaying Login Information of Last 24 hours</span>
                            </div>
                        </div>-->

<!--                        <div class="bullDropdown" >
                            <div class="dropdown" id="explain_login">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a  id="export_login" class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('loginexport', 2); ?>" onclick="exportdetails()" data-bs-target="login-range">Export Historical Details</a>
                                    <a class="dropdown-item rightslide-container-hand" id="export_login" onclick="exportLogin();" href="#">Export To Excel</a>
                                </div>
                            </div>
                        </div>-->
                    </div>
                    <input type="hidden" value="" id="loggedUserDetails">
                    <div class="form table-responsive white-content">
                    <form id="RegisterValidation">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                <div class="form-group has-label">
                                    <h4>Kibana Configuration:</h4>
                                    <!--<div class="form-group">-->
                                        <label>
                                        Kibana Url:
                                        </label>
                                        <input type="text" class="form-control" id="kibana_url" readonly name="kibana_url"  >
                                        <span style="color:red;" id="kibana_url_err"></span>
                                    <!--</div>-->
                                    
                                       <label>
                                        Kibana IP URL:
                                        </label>
                                        <input type="text" class="form-control" id="kibana_ip_url" readonly name="kibana_ip_url"  >
                                        <span style="color:red;" id="kibana_ip_url_err"></span> 
                                        
                                        <label>
                                        Kibana Username:
                                        </label>
                                        <input type="text" class="form-control" id="kibana_username" readonly name="kibana_username"  >
                                        <span style="color:red;" id="kibana_username_err"></span> 
                                        
                                        <label>
                                        Kibana Password:
                                        </label>
                                        <input type="text" class="form-control" id="kibana_password" readonly name="kibana_password"  >
                                        <span style="color:red;" id="kibana_password_err"></span>
                                </div>
                                
                                <div class="form-group has-label">
                                    <h4>Dashboard Configuration:</h4>
                                    <label>
                                        License URL
                                    </label>
                                    <input type="text" class="form-control" id="license_url" readonly name="license_url"  >
                                    <span style="color:red;" id="license_url_err"></span>
                                    
                                    <label>
                                        Web Socket URL
                                    </label>
                                    <input type="text" class="form-control" id="ws_url" readonly name="ws_url"  >
                                    <span style="color:red;" id="ws_url_err"></span>
                                    
                                    <label>
                                        Kibana Namespace Configuration
                                    </label>
                                    <input type="text" class="form-control" id="kibana_nspace" readonly name="kibana_nspace"  >
                                    <span style="color:red;" id="kibana_nspace_err"></span>
                                    
                                    <label>
                                        Elastic Alert Configuration
                                    </label>
                                    <input type="text" class="form-control" id="elast_alert" readonly name="elast_alert"  >
                                    <span style="color:red;" id="elast_alert_err"></span>
                                </div>
 
                                </div>
                                    
                                    <div class="col-lg-6">
                                    <div class="form-group has-label">
                                        <h4>Elastic search configurations</h4>
                                            <label>
                                            Elastic Url:
                                            </label>
                                            <input type="text" class="form-control" id="elastic_url" readonly name="elastic_url"  >
                                            <span style="color:red;" id="elastic_url_err"></span>
                                            
                                            <label>
                                            Elastic Username:
                                            </label>
                                            <input type="text" class="form-control" id="elastic_username" readonly name="elastic_username"  >
                                            <span style="color:red;" id="elastic_username_err"></span>
                                            
                                            <label>
                                            Elastic Password:
                                            </label>
                                            <input type="text" class="form-control" id="elastic_password" readonly name="elastic_password"  >
                                            <span style="color:red;" id="elastic_password_err"></span>
                                            
                                            <h4>Other configurations</h4>    
                                            <label>
                                            Security Array:
                                            </label>
                                            <input type="text" class="form-control" id="security_array" readonly name="security_array"  >
                                            <span style="color:red;" id="security_array_err"></span>
                                            
                                            <label>
                                            Global Retry:
                                            </label>
                                            <input type="text" class="form-control" id="global_retry" readonly name="global_retry"  >
                                            <span style="color:red;" id="global_retry_err"></span>
                                            
                                            <label>
                                            Timer:
                                            </label>
                                            <input type="text" class="form-control" id="timer" readonly name="timer"  >
                                            <span style="color:red;" id="timer_err"></span>
                                    </div>
                                                                        </div>
                                        

                                </div>
                                <div class="button col-md-12 text-center" style="bottom:-38px;">
                                    <span id="loadingSuccessMsg" style="color: green;float: left;"></span>
                                        <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" onclick="editConfigDetails();">Edit</button>
                                        <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" onclick="SubmitConfigDetails();">Submit</button>
                                </div>
                                <div>
                                    <span id="errorMsg" style="color:red;"></span>
                                </div>
                            </div>

                            
                        </div>
                    </form>
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


