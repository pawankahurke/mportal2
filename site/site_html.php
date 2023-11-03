<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';
csrf_check_custom();

nhRole::dieIfnoRoles(['site']); // roles: site

?>
<div class="content white-content" onload="siteDataTable();">
    <div class="row mt-2">
        <div class="col-md-12 pl-0 pr-0">
            <div class="card">
                <div class="card-body">
                    <?php
                    //   nhRole::checkRoleForPage('site');
                    $res = true; //nhRole::checkModulePrivilege('site');
                    if ($res) {
                    ?>
                        <!-- loader -->

                        <div class="toolbar">
                            <!--        Here you can write extra buttons/actions for the toolbar
                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>
                            </div>
                        </div>   -->

                            <!-- <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addsite', 2); ?>" data-bs-target="site-newadd-container" onclick="clearFields()">Add New Site</a>
                                    <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('emaildistribution', 2); ?>" id="site-emailDistribution">Email Distribution</a>
                                    <a class="dropdown-item dropHandy <?php echo setRoleForAnchorTag('licensedetails', 2); ?>" id="site-license">License Details</a>
                                </div>
                            </div>
                        </div> -->
                        </div>
                        <input type="hidden" id="siteId" value="" />
                        <input type="hidden" id="searchValue" value="" />
                        <div id="loader" class="loader" data-qa="loader" style="position: absolute;bottom: 50%;right:50%;">
                            <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                        </div>
                        <table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="site_grid">
                            <thead>
                                <tr>
                                    <th id="key0" headers="sitename" class="sitename">
                                        Site Name
                                        <i class="fa fa-caret-down cursorPointer direction" id="sitename1" onclick="addActiveSort('asc', 'Sites.sitename'); siteDataTable( nextPage = 1, notifSearch = '','Sites.sitename', 'asc');sortingIconColor('sitename1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="sitename2" onclick="addActiveSort('desc', 'Sites.sitename'); siteDataTable( nextPage = 1, notifSearch = '','Sites.sitename', 'desc');sortingIconColor('sitename2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key1" headers="username" class="username">
                                        Added by
                                        <i class="fa fa-caret-down cursorPointer direction" id="username1" onclick="addActiveSort('asc', 'Sites.username'); siteDataTable( nextPage = 1, notifSearch = '','Sites.username', 'asc');sortingIconColor('username1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="username2" onclick="addActiveSort('desc', 'Sites.username'); siteDataTable( nextPage = 1, notifSearch = '','Sites.username', 'desc');sortingIconColor('username2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key2" headers="firstcontact" class="">
                                        Added on
                                        <i class="fa fa-caret-down cursorPointer direction" id="firstcontact1" onclick="addActiveSort('asc', 'Sites.firstcontact'); siteDataTable( nextPage = 1, notifSearch = '','Sites.firstcontact', 'asc');sortingIconColor('firstcontact1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="firstcontact2" onclick="addActiveSort('desc', 'Sites.firstcontact'); siteDataTable( nextPage = 1, notifSearch = '','Sites.firstcontact', 'desc');sortingIconColor('firstcontact2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key3" headers="customer_name" class="">
                                        Customer name
                                        <i class="fa fa-caret-down cursorPointer direction" id="customer_name1" onclick="addActiveSort('asc', 'Customers.customer_name'); siteDataTable( nextPage = 1, notifSearch = '','Customers.customer_name', 'asc');sortingIconColor('customer_name1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="customer_name2" onclick="addActiveSort('desc', 'Customers.customer_name'); siteDataTable( nextPage = 1, notifSearch = '','Customers.customer_name', 'desc');sortingIconColor('customer_name2')" style="font-size:18px"></i>
                                    </th>
                                    <th id="key4" headers="skuname" class="">
                                        Subscription
                                        <i class="fa fa-caret-down cursorPointer direction" id="skuname1" onclick="addActiveSort('asc', 'skuname'); siteDataTable( nextPage = 1, notifSearch = '','skuname', 'asc');sortingIconColor('skuname1')" style="font-size:18px"></i>
                                        <i class="fa fa-caret-up cursorPointer direction" id="skuname2" onclick="addActiveSort('desc', 'skuname'); siteDataTable( nextPage = 1, notifSearch = '','skuname', 'desc');sortingIconColor('skuname2')" style="font-size:18px"></i>
                                    </th>

                                    <!-- <th>Site Name</th>
                                <th>Added by</th>
                                <th>Added on</th>
                                <th>Customer name</th>
                                <th>Subscription</th> -->


                                </tr>
                            </thead>
                        </table>
                    <?php
                    }
                    ?>
                    <div class="col-md-12" id="errorMsg" style="display:none;">
                        <span>Please select site or group to view list</span>
                    </div>
                    <div id="largeDataPagination"></div>

                </div>
                <!-- end content-->
            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>


<!-- Add new site UI starts  -->
<!-- <div id="site-newadd-container" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Add Site</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="site-newadd-container" onclick="clearAddSiteField()">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="addDeploymentSiteBtn" onclick="addDeploymentSite()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body" style="padding:10px 10px;">
                <form id="siteAddForm" name="siteAddForm">
                    <div class="form-group has-label">
                        <span class="text-danger">*</span>
                        <label for="deploy_sitename">Name of the site</label>
                        <input type="text" name="deploy_sitename" data-qa="deploy_sitename" id="deploy_sitename" class="form-control pl-1">
                        <p id="siteName" class="text-danger" style="display:none">Enter site name</p>
                    </div>

                    <div class="form-group has-label">
                        <span class="text-danger">*</span>
                        <label>Select Customer</label>
                        <select class="selectpicker" data-style="btn btn-info" title="-- Please select a subscription --" data-size="3" id="site_skuid" name="site_skuid" onchange="getSkuList();">
                            <option value="0" disabled>-- Please select a Customer --</option>


                        </select>
                        <p id="selectCustomer" class="text-danger" style="display:none">Select customer</p>
                    </div>
                    <div class="form-group has-label">
                        <span class="text-danger">*</span>
                        <label>Select the Subscription</label>
                        <select class="selectpicker" data-style="btn btn-info" title="-- Please select a SKU --" data-size="3" id="site_planid" name="site_planid">
                            <option value="0" disabled>-- Please select a subscription --</option>


                        </select>
                        <p id="selectSubscription" class="text-danger" style="display:none">Select subscription</p>
                    </div>


                    <div class="form-check form-check-radio global">
                        <span class="text-danger">*</span>
                        <label for="">Choose the configuration</label><br />
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" checked id="site_config" name="site_configuration" value="default">
                            <span class="form-check-sign"></span><span style="margin-left:-12px;">Default Configuration</span>

                        </label>
                    </div>
                    <br />
                    <div class="form-group has-label">
                        <label>
                            Assign Site to Users
                        </label>
                        <select data-live-search="true" class="selectpicker" multiple data-style="btn btn-info" title="Select Users" data-size="3" id="sitesUsers" name="sitesUsers">

                        </select>
                        <span id="add_userLevel-err"></span>
                    </div>

                    <div class="form-group has-label">
                        <label class="siteCreateErr"></label>
                    </div>

                    <div class="button col-md-12 text-left">
                        <p id="required_Sitename" style="color: red;font-size: 14px;"></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> -->
<!-- Add new site UI ends -->

<!-- license details UI starts  -->
<div id="site-license-container" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>License Details</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="site-license-container">&times;</a>
    </div>
    <div class="btnGroup">
        <!--<div class="icon-circle create_site_div">
            <div class="toolTip" id="">
                <i class="tim-icons icon-simple-remove"></i>
                <span class="tooltiptext">Close</span>
            </div>
        </div>-->
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">
                <div class="form-group has-label">
                    <label for="licSitename">Site name</label>
                    <input type="text" name="licSitename" id="licSitename" class="form-control" readonly="">
                </div>

                <div class="form-group has-label">
                    <label for="licSkuname">SKU Name</label>
                    <input type="text" name="licSkuname" id="licSkuname" class="form-control" readonly="">
                </div>

                <div class="form-group has-label">
                    <label for="licUsedtotal">License Used / Total</label>
                    <input type="text" name="licUsedtotal" id="licUsedtotal" class="form-control" readonly="">
                </div>

                <div class="form-group has-label">
                    <label for="downloadUrl">Download url</label>
                    <input type="text" name="downloadUrl" id="downloadUrl" class="form-control" readonly="">
                </div>

                <div class="button text-left">
                    <button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="copy_link1" onclick="copy_url('downloadUrl')"><i class="tim-icons icon-single-copy-04 mr-1"></i>Copy URL</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- License details UI ends -->


<!-- email-distribution pop-up start -->
<div id="email-distribution" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Email Distribution</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="email-distribution">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="emailDistribution" onclick="emailDistribution()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Send</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">
                <div class="form-group">
                    <label>Enter the email addresses below to get the download URL for the site <b><span class="site"></span></b>. Please enter only one Email ID per line. </label>
                </div>
                <div class="form-group">
                    <label>Email Addresses</label><em class="tpt">Enter one email address per line.</em>
                    <textarea id="emailAddresses" name="emailAddresses" class="form-control" rows="60" style="border: 1px solid #ccc; min-height: 120px !important;"></textarea>
                    <img id="emailDistributeLoader" src="../assets/img/loader.gif">
                </div>
                <div class="form-group has-label">
                    <span class="emailstat"></span>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- email-distribution pop-up end -->


<!-- Add Site Starts -->
<div id="site-addConfig-container" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Add Site</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="site-addConfig-container">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="addDeploymentSiteBtn" onclick="addNewSiteFunc()">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body" style="padding:10px 10px;">
                <form id="siteAddForm" name="siteAddForm">
                    <div class="form-group has-label">
                        <span class="text-danger">*</span>
                        <label for="deploy_sitename">Name of the site</label>
                        <input type="text" name="deploy_sitename" data-qa="deploy_sitename" id="deploy_sitename" class="form-control pl-1">
                        <p id="siteError" class="text-danger" style="display:none">Enter site name</p>
                    </div>

                    <div class="form-group has-label">
                        <span class="text-danger">*</span>
                        <label for="deploy_emailsub">Email Subject</label>
                        <input type="text" name="deploy_emailsub" id="deploy_emailsub" class="form-control pl-1">
                        <p id="emailSubError" class="text-danger" style="display:none">Enter Email Subject</p>
                    </div>

                    <div class="form-group has-label">
                        <span class="text-danger">*</span>
                        <label for="deploy_emailsender">Email Sender</label>
                        <input type="text" name="deploy_emailsender" id="deploy_emailsender" class="form-control pl-1">
                        <p id="emailSenderError" class="text-danger" style="display:none">Enter Email Sender</p>
                    </div>

                    <div class="form-group has-label">
                        <span class="text-danger">*</span>
                        <label for="client32_name">32-bit Client Name</label>
                        <input type="text" name="client32_name" data-qa="client32_name" id="client32_name" class="form-control pl-1">
                        <p id="client32_nameError" class="text-danger" style="display:none">Enter 32-bit Client Name</p>
                    </div>

                    <div class="form-group has-label">
                        <span class="text-danger">*</span>
                        <label for="client64_name">64-bit Client Name</label>
                        <input type="text" name="client64_name" data-qa="client64_name" id="client64_name" class="form-control pl-1">
                        <p id="client64_nameError" class="text-danger" style="display:none">Enter 64-bit Client Name</p>
                    </div>

                    <div class="form-group has-label">
                        <span class="text-danger">*</span>
                        <label for="branding_url">Branding URL</label>
                        <input type="text" name="branding_url" id="branding_url" class="form-control pl-1">
                        <p id="branding_urlError" class="text-danger" style="display:none">Enter Branding URL</p>
                    </div>

                    <div class="form-group has-label">
                        <label for="license_details">License Used / Total</label>
                        <input type="text" name="license_details" disabled id="license_details" class="form-control pl-1">
                        <!-- <p id="license_detailsError" class="text-danger" style="display:none">Enter Tenant ID</p> -->
                    </div>

                    <div class="form-group has-label">
                        <label for="license_name">License Name</label>
                        <input type="text" name="license_name" disabled id="license_name" class="form-control pl-1">
                        <!-- <p id="licensenameError" class="text-danger" style="display:none">Enter Client Linux Name</p> -->
                    </div>

                    <div class="form-group has-label">
                        <label for="license_bill">Billing Cycle</label>
                        <input type="text" name="license_bill" disabled id="license_bill" class="form-control pl-1">
                        <!-- <p id="dIDnameError" class="text-danger" style="display:none">Enter Deployement ID</p> -->
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
<!-- Add Site ends -->
<style>
    .tpt {
        margin-left: 17px;
        color: red;
    }

    .dropdown-menu.inner li.hidden {
        display: none;
    }

    .dropdown-menu.inner.active li,
    .dropdown-menu.inner li.active {
        display: block;
    }

    /*
    div.bottom {

        bottom: 25px !important;

    } */
</style>

<script src="../assets/js/core/jquery.min.js"></script>
<?php
$res = nhRole::checkModulePrivilege('site');
if ($res) {
?>
    <script>
        $(document).ready(function() {
            $('.bottom').hide();
            $('div.dataTables_wrapper thead,div.dataTables_wrapper tbody').hide();
            siteDataTable(1, '');
            $('#emailDistributeLoader').hide();
            $('#searchValue').val('');

        });

        $('#site-license').on('click', function() {
            var site = $('#searchValue').val();
            if (site == '') {
                $.notify('Please select a site');
            } else {
                rightContainerSlideOn('site-license-container');
                getLicenseDetails();
            }
        });
        $("#site-emailDistribution").on('click', function() {
            var site = $('#searchValue').val();
            if (site == '') {

                //rightContainerSlideClose('site-license-container');
                $.notify('Please select a site');

                //return false;
            } else {
                rightContainerSlideOn('email-distribution');
                manageEmailDistribution();
            }
        });

        function addDeploymentSite() {
            var sitename = $('#deploy_sitename').val();
            var site_subsc = $('#site_skuid').val();
            var site_plan = $('#site_planid').val();
            var config = $('#site_config').val();
            var users = $('#sitesUsers').val();
            // alert(config);
            //alert(users);
            if (sitename == '') {
                $.notify('Please enter a site name');
                $("#siteName").css("display", "block");
                return false;
            }
            if ((/\s/.test(sitename))) {
                $.notify('Site name should not have space');
                return false;
            }
            if (site_subsc == '') {
                $.notify('Please select a subscribtion');
                $("#selectCustomer").css("display", "block");
                $("#siteName").css("display", "none");
                return false;
            }
            if (site_plan == '') {
                $.notify('Please select a SKU');
                $("#selectSubscription").css("display", "none");
                $("#selectCustomer").css("display", "none");
                $("#siteName").css("display", "none");
                return false;
            }
            if (config == '') {
                $.notify('Please select a configuration');
                return false;
            }
            if (users == '') {
                $.notify('Please assign this site to users');
                return false;
            }
            var site_data = new FormData();
            site_data.append('sitename', sitename);
            site_data.append('site_sub', site_subsc);
            site_data.append('site_config', config);
            site_data.append('site_plan', site_plan);
            site_data.append('users', users);
            site_data.append('csrfMagicToken', csrfMagicToken);

            $.ajax({
                url: '../site/siteAdd.php',
                type: 'POST',
                processData: false,
                contentType: false,
                data: site_data,
                dataType: 'json',
                success: function(data) {

                    var result = JSON.parse(JSON.stringify(data));

                    if (result.status == "success") {
                        $.notify('Site created successfully');
                        setTimeout(function() {
                            rightContainerSlideClose('site-newadd-container');
                            debugger;
                            location.reload();
                        }, 3200);
                    }

                }
            });
        }

        function getUsersList() {
            $.ajax({
                url: "../admin/groupfunctions.php",
                type: "GET",
                data: {
                    'function': 'get_UsersList'
                },
                success: function(data) {
                    $('#sitesUsers').html('');
                    $('#sitesUsers').html(data);
                    $(".selectpicker").selectpicker("refresh");
                },
                error: function(errorThrown) {
                    console.log(errorThrown);
                }
            });
        }

        function getSubscrptionList() {
            $.ajax({
                url: "../site/getSubscription.php",
                type: "GET",
                data: {},
                success: function(data) {
                    $(".loader").hide();
                    $('#site_skuid').html('');
                    $('#site_skuid').html(data);
                    $(".selectpicker").selectpicker("refresh");
                },
                error: function(errorThrown) {
                    console.log(errorThrown);
                    $(".loader").hide();

                }
            });
        }

        function getSkuList() {
            var custId = $("#site_skuid").val();
            $.ajax({
                url: "../site/getSkulist.php?custId=" + custId,
                type: "GET",
                data: {},
                success: function(data) {
                    $('#site_planid').html('');
                    $('#site_planid').html(data);
                    $(".selectpicker").selectpicker("refresh");
                },
                error: function(errorThrown) {
                    console.log(errorThrown);
                }
            });
        }

        function getupdateSite() {
            var id = $("#siteId").val();
            $.ajax({
                url: "../site/getUpdateSite.php?id=" + id,
                type: "GET",
                data: {},
                success: function(data) {

                },
                error: function(errorThrown) {
                    console.log(errorThrown);
                }
            });
        }

        $('body').on('click', '.page-link', function() {
            var nextPage = $(this).data('pgno');
            notifName = $(this).data('name');
            const activeElement = window.currentActiveSortElement;
            const key = (activeElement) ? activeElement.sort : '';
            const sort = (activeElement) ? activeElement.type : '';
            siteDataTable(nextPage, '', key, sort);
        })
        $('body').on('change', '#notifyDtl_lengthSel', function() {
            siteDataTable(1, '');
        });

        function siteDataTable(nextPage = 1, notifSearch = '', key = '', sort = '') {
            notifSearch = $('#notifSearch').val();
            if (typeof notifSearch === 'undefined') {
                notifSearch = '';
            }

            checkAndUpdateActiveSortElement(key, sort);

            $("#loader").show();
            var dat = {
                'csrfMagicToken': csrfMagicToken,
                'limitCount': $('#notifyDtl_length :selected').val(),
                'nextPage': nextPage,
                'notifSearch': notifSearch,
                'order': key,
                'sort': sort
            }
            // console.log(dat);
            $.ajax({
                url: "../site/siteFunction.php",
                type: "POST",
                dataType: "json",
                data: dat,
                success: function(gridData) {
                    $(".loader").hide();
                    $('#absoLoader').hide();
                    $(".se-pre-con").hide();
                    $('#site_grid').DataTable().destroy();
                    $('#site_grid tbody').empty();
                    siteTable = $('#site_grid').DataTable({
                        scrollY: 'calc(100vh - 240px)',
                        scrollCollapse: true,
                        paging: false,
                        searching: false,
                        bFilter: false,
                        ordering: false,
                        aaData: gridData.html,
                        bAutoWidth: true,
                        select: false,
                        bInfo: false,
                        responsive: true,
                        stateSave: true,
                        processing: true,
                        "pagingType": "full_numbers",
                        "stateSaveParams": function(settings, data) {
                            data.search.search = "";
                        },
                        order: [
                            [2, "asc"]
                        ],
                        "lengthMenu": [
                            [10, 25, 50, 100],
                            [10, 25, 50, 100]
                        ],
                        //                "lengthChange": false,
                        "language": {
                            "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                            search: "_INPUT_",
                            searchPlaceholder: "Search records"
                        },
                        "columnDefs": [{
                            "targets": 0,
                            "orderable": false
                        }],
                        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                        initComplete: function(settings, json) {
                            $('.equalHeight').show();
                            $('#absoLoader').hide();
                            $("th").removeClass('sorting_desc');
                            $("th").removeClass('sorting_asc');
                            $(".loader").hide();
                            $('div.dataTables_wrapper thead,div.dataTables_wrapper tbody').show();
                        },
                        "drawCallback": function(settings) {
                            // $(".checkbox-btn input[type='checkbox']").change(function () {
                            //     if ($(this).is(":checked")) {
                            //         $(this).parents("tr").addClass("selected");
                            //     }
                            // });
                            $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
                            // $(".dataTables_filter:first").replaceWith('<div id="notifyDtl_filter" class="dataTables_filter"><label><input type="text" class="form-control form-control-sm" placeholder="Search records" value="' + notifSearch + '" id="notifSearch" aria-controls="notifyDtl"></label></div>');
                        }
                    });
                    $('.dataTables_filter input').addClass('form-control');
                    $('.tableloader').hide();
                }
            });

            $('#site_grid').on('click', 'tr', function() {
                var rowID = siteTable.row(this).data();
                siteTable.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
                var rowID = siteTable.row(this).data();
                if (rowID != 'undefined' && rowID !== undefined) {
                    $('#searchValue').val(rowID[0]);
                    $("#siteId").val(rowID[5]);

                }

            });

            // $('.dataTables_filter input').addClass('form-control');
            // $('#site_grid tbody').on('click', 'tr', function() {
            //     siteTable.$('tr.selected').removeClass('selected');
            //     $(this).addClass('selected');
            //     var rowID = siteTable.row(this).data();
            //     if (rowID != 'undefined' && rowID !== undefined) {
            //         var row = JSON.parse(JSON.stringify(rowID));
            //         console.log("'" + row.siteName + "'");
            //         var strele = $(row[0]);
            //         $('#searchValue').val(strele.eq(0).attr('id'));
            //         //console.log(strele.eq(0).attr('id'));
            //         //alert(JSON.stringify(rowID));
            //         $("#siteId").val(row[5]);

            //     }
            // });


            $("#site_searchbox").keyup(function() {
                siteTable.search(this.value).draw();
                $("#site_grid tbody").eq(0).html();
            });
            $('#site_grid').DataTable().search('').columns().search('').draw();
        }

        function getLicenseDetails() {
            //    $('#site-license-container').find('input[type="text"]').each({
            //        $(this).attr("readonly");
            //    });
            var site = $('#searchValue').val();
            if (site == '') {
                $.notify('Please select a site');
                rightContainerSlideClose('site-license-container');
                return false;
            }
            var data = {
                'function': "get_LicenseDetails",
                'sitename': site,
                csrfMagicToken: csrfMagicToken
            };
            $.ajax({
                url: "../device/org_api.php",
                type: "POST",
                data: data,
                success: function(data) {
                    var res = JSON.parse(data);
                    var maxinstall = '';
                    var downloadUrl = '';
                    if (res['data']['maxinstall'] == 0) {
                        maxinstall = 'Unlimited';
                    } else {
                        maxinstall = res['data']['maxinstall'].toString();
                    }
                    $('#licSitename').val(site).attr('readonly', true);
                    $('#licSkuname').val(res['data']['skuname']).attr('readonly', true);
                    var usedTotal = res['data']['numofinstall'] + ' / ' + maxinstall;
                    $('#licUsedtotal').val(usedTotal).attr('readonly', true);
                    var regcode = res['data']['regcode'];
                    var siteemailid = res['data']['siteemailid'];
                    var installPath = res['data']['licenseurl'];
                    // downloadUrl = installPath + 'Provision/install/d.php?r=' + regcode + '&e=' + siteemailid;
                    downloadUrl = installPath + 'Dashboard/Provision/install/d.php?r=' + regcode + '&e=' + siteemailid;
                    if (res['data']['isDownViaDash'] === 'YES') {
                        // downloadUrl = installPath + 'install-eula.php?r=' + regcode + '&e=' + siteemailid;
                        downloadUrl = installPath + 'Dashboard/Provision/install-eula.php?r=' + regcode + '&e=' + siteemailid;
                    }
                    $('#downloadUrl').val(downloadUrl).attr('readonly', true);
                },
                error: function(error) {
                    console.log('Error :: getLicenseDetails : ' + error);
                }
            })
        }

        function manageEmailDistribution() {
            $('#emailAddresses').val('');
            $('.emailstat').html('');
        }

        function emailDistribution() {

            /*var selection = $('[name=searchType]').val();
             if (selection == undefined || selection == 'ServiceTag' || selection == 'Groups' || selection == '') {
             errorNotify("Please choose a site");
             return;
             }*/

            // Need to save email in Site email if not exists and send mail
            var emailList = $('#emailAddresses').val();
            var sitename = $.trim($('#searchValue').val());
            if (emailList == '') {
                $.notify('Please enter a valid email address');
                return false;
            }
            if (sitename == '') {
                $.notify('Please select a site');
                rightContainerSlideClose('email-distribution');
                return false;
            }

            if (!validateEmails(emailList)) {
                $.notify('Please enter a valid email address');
            } else {
                $('#emailDistributeLoader').show();
                var Elementresult = emailList.split("\n");
                var NewEmailList = [];
                for (var i = 0; i < Elementresult.length; i++) {
                    if (Elementresult[i] != '') {
                        NewEmailList.push(Elementresult[i]);
                    }
                }
                var postData = {
                    function: 'update_SiteEmailData',
                    email_list: NewEmailList,
                    sitename: sitename,
                    csrfMagicToken: csrfMagicToken
                };

                $.ajax({
                    url: "../device/org_api.php",
                    type: 'POST',
                    data: postData,
                    success: function(data) {
                        $('#emailDistributeLoader').hide();
                        var res = JSON.parse(data);
                        if (res.status) {
                            sendDownloadLinkMail(res.data, emailList);
                        } else {
                            $.notify(res.msg);
                        }
                    },
                    error: function(err) {
                        $('#emailDistributeLoader').hide();
                    }
                });
            }
        }

        function validateEmails(string) {
            var regex = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
            var result = string.split("\n");
            for (var i = 0; i < result.length; i++) {
                if (!regex.test(result[i])) {
                    return false;
                }
            }
            return true;
        }

        function sendDownloadLinkMail(siteid, emailList) {

            var postData = {
                function: 'send_DownloadLinkMail',
                siteid: siteid,
                emailList: emailList,
                csrfMagicToken: csrfMagicToken
            };

            $.ajax({
                url: "../device/org_api.php",
                type: 'POST',
                data: postData,
                success: function(data) {
                    var res = JSON.parse(data);
                    if (res.status) {
                        $.notify('Email sent Succesfully');
                        rightContainerSlideClose('email-distribution');
                    } else {
                        $.notify('Failed to send the email. Please try again');
                        rightContainerSlideClose('email-distribution');
                    }
                },
                error: function(err) {
                    console.log(err);
                }
            });
        }

        function copy_url(selectorId) {
            if (selectorId == '') {
                var urlField = document.querySelector("#site_download_url");
            } else {
                var urlField = document.querySelector("#" + selectorId);
            }
            urlField.select();
            document.execCommand("copy");
            $.notify("Download URL is copied");
        }

        function copyDownloadUrl() {
            var urlField = document.querySelector("#download_url");
            urlField.select();
            document.execCommand("copy");
        }


        function clearFields() {
            $('#deploy_sitename').val('');
        }
    </script>
<?php
}
?>

<style>
    #site_grid_filter {
        display: none;
    }

    /* div.bottom {
        bottom: 34px !important;
    } */

    /* .dataTables_wrapper {
        display:none;
    } */

    .dataTables_filter {
        display: none;
    }
</style>