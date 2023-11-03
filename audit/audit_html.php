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
                <!-- <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <span>Displaying Last 1 month activity</span>
                            </div>
                        </div>
                <div class="bullDropdown" >
                                        <div class="dropdown" id="explain_auidt">
                                            <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="tim-icons icon-bullet-list-67"></i>
                                            </button>

                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" id="click_first">
                                <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('dartdetailview', 2); ?>" id="detailViewAudit"  data-bs-target="rsc-add-container" >Details View</a>
                                <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('dartexport', 2); ?>"  id="exportDartAudit" data-bs-target="dartaudit-range">Export To Excel</a>
                                            </div>
                                        </div>
                </div>   -->
                <!-- <div id="notifyDtl_filter" class="dataTables_filter">
                                <label class="float-lg-right mr-2">
                                    <input type="text" class="form-control form-control-sm" placeholder="Search records" value="" id="notifSearch" aria-controls="notifyDtl"/>
                                    <button class="bg-white border-0 mr-1 showbtn cursorPointer" onclick="getSearchRecords()"><i class="tim-icons serachIcon icon-zoom-split"></i></button>
                                    <button style="display:none" class="bg-white border-0 mr-1 clearbtn cursorPointer" onclick="clearRecords()" onclick="document.getElementById('notifSearch').value = ''"><i class="tim-icons serachIcon icon-simple-remove"></i></button>
                                </label>
                            </div> -->
                <input type="hidden" id="audit_selected">
                <table class="nhl-datatable table table-striped" id="auditTable" width="100%" data-page-length="25">
                  <thead>
                    <tr>
                      <th id="key0" headers="time" class="">
                          Time
                          <i class="fa fa-caret-down cursorPointer direction" id = "time1"  onclick = "addActiveSort('asc', 'time'); audit_datatablelist(1,'','time', 'asc');sortingIconColor('time1')" style="font-size:18px"></i>
                          <i class="fa fa-caret-up cursorPointer direction" id = "time2" onclick = "addActiveSort('desc', 'time'); audit_datatablelist(1,'','time', 'desc');sortingIconColor('time2')" style="font-size:18px"></i>
                        </th>
                      <th id="key1" headers="scop" class="">
                          Dart
                          <i class="fa fa-caret-down cursorPointer direction" id = "scop1" onclick = "addActiveSort('asc', 'grpaudit'); audit_datatablelist(1,'','grpaudit', 'asc');sortingIconColor('scop1')" style="font-size:18px"></i>
                          <i class="fa fa-caret-up cursorPointer direction" id = "scop2" onclick = "addActiveSort('desc', 'grpaudit'); audit_datatablelist(1,'','grpaudit', 'desc');sortingIconColor('scop2')" style="font-size:18px"></i>
                      </th>
                      <th id="key2" headers="user" class="">
                          User Name
                          <i class="fa fa-caret-down cursorPointer direction" id = "user1" onclick = "addActiveSort('asc', 'user'); audit_datatablelist(1,'','user', 'asc');sortingIconColor('user1')" style="font-size:18px"></i>
                          <i class="fa fa-caret-up cursorPointer direction" id = "user2" onclick = "addActiveSort('desc', 'user'); audit_datatablelist(1,'','user', 'desc');sortingIconColor('user2')" style="font-size:18px"></i>
                        </th>
                      <th id="key3" headers="detail" class="">
                          Details
                          <i class="fa fa-caret-down cursorPointer direction"  id = "detail1" onclick = "addActiveSort('asc', 'detail'); audit_datatablelist(1,'','detail', 'asc');sortingIconColor('detail1')" style="font-size:18px"></i>
                          <i class="fa fa-caret-up cursorPointer direction"  id = "detail2" onclick = "addActiveSort('desc', 'detail'); audit_datatablelist(1,'','detail', 'desc');sortingIconColor('detail2')" style="font-size:18px"></i>
                        </th>
                    </tr>
                  </thead>
                </table>
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


    <!-- audit slider pop-up starts -->
    <div id="rsc-add-container" class="rightSidenav" data-class="md-6">
                <div class="card-title border-bottom">
                    <h4>Audit Details</h4>
                    <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-add-container">&times;</a>
                </div>

                <div class="form table-responsive white-content">
                    <form id="RegisterValidation">
                        <div class="card">
                            <div class="card-body">
                                <label>Time :</label>
                                <div class="form-group has-label" id="auditTime">

                                    <input class="form-control" type="text" />
                                </div>

                                <label>User Name :</label>
                                <div class="form-group has-label"  id="audituserName">
                                    <input class="form-control"  type="text"  />
                                </div>

                                <label>Details</label>
                                <div class="form-group has-label"  id="auditdetails">
                                <input class="form-control" type="text" />
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
    </div>

    <!-- audit slider pop-up ends -->


<div id="dartaudit-range" class="rightSidenav" data-class="sm-3">
    <div class="card-title border-bottom">
        <h4>Export Audit Data</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="dartaudit-range">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="btn-exportDartAudit" onclick="exportDartAudit();">
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
                            Download data for:
                        </label>
                        <!-- <select class="form-control selectpicker dropdown-submenu" disabled="true" onchange="checkLevelType(this.value)" disabled="true" data-style="btn btn-info" id="LevelType" data-size="5">
                            <option value="Customer" selected >Customer</option> -->
                            <!-- <option value="User" selected>User</option>  -->
                        <!--</select> -->

                        <div class="CustomerSelection">
                        <select class="form-control selectpicker dropdown-submenu" data-style="btn btn-info"  id="CustomerSelection" data-size="5">

                        </select>
                        </div>

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

