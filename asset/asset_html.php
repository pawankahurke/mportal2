<!-- content starts here  -->
<?php
include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();

$dbo = NanoDB::connect();
$pdo = $dbo->prepare("SELECT filter_name, GROUP_CONCAT(filter) as filter from `".$GLOBALS['PREFIX']."asset`.`assets_filter` GROUP BY filter_name");
$pdo->execute();
$data = $pdo->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="content white-content">

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body" id="adhoc-main">
                    <div class="toolbar">
                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>
                            </div>
                        </div>

                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item rightslide-container-hand" data-target="add-aq-filter-container">Add Filter</a>
                                    <a class="dropdown-item rightslide-container-hand" onclick="editfilter()">Edit Filter</a>
                                    <a class="dropdown-item" onclick="deletefilter()">Delete Filter</a>
                                    <a class="dropdown-item" onclick="adhocInfoPortal();">Information Portal</a>
                                    <!--<a class="dropdown-item rightslide-container-hand" data-target="asset-export-options">Asset Export Options</a>-->
                                    <!--<a class="dropdown-item rightslide-container-hand" data-target="event-export-options">Event Export Options</a>-->
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type ="hidden" id="idselected">
                    <input type ="hidden" id="typeselected">
                    <input type ="hidden" id="hiddenfid">
                    <table id="adhoc-q-list" class="nhl-datatable table table-striped">
                        <thead>
                            <tr>
                                <th class="id">Id</th>
                                <th class="name">Name</th>
                                <th class="source">Source Fields</th>
                                <th class="created">Created On</th>
                                <th class="type">Type</th>
                                <th class="export">Export</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <!-- end content-->

                <!-- portal content -->
                <div class="card-body" id="adhoc-info-portal" style="display: none;">
                    <div class="toolbar">
                        <div class="bullDropdown leftDropdown">
                            <div class="dropdown">
                                <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>
                            </div>
                        </div>

                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <a class="dropdown-item" onclick="adhocMain();">Back</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <table id="adhoc-info-portal-dt" class="nhl-datatable table table-striped">
                        <thead>
                            <tr>
                                <th class="qid">Qid</th>
                                <th class="qname">Query Name</th>
                                <th class="scope">Scope</th>
                                <th class="status">Status</th>
                                <th class="created">Created Time</th>
                                <th class="downloadfile">File Download</th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
            <!--  end card  -->
        </div>
        <!-- end col-md-12 -->
    </div>
    <!-- end row -->
</div>

<div id="asset-export-options" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Export Options</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="asset-export-options">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" id="export-asset">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Export</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content">
        <div class="card">
            <div class="card-body">
                <div id="fileTypeDiv" class="form-group has-label">
                    <span class="error">*</span>
                    <label for="types">Type</label>
                    <select name="types" class="selectpicker dropdown-submenu" title="Type" data-size="2" data-width="100%">
                        <?php
                        if (isset($data) && is_array($data) && safe_sizeof($data) > 0) {
                            foreach ($data as $eachRows) {
                                echo '<option value="' . $eachRows['filter'] . '">' . $eachRows['filter_name'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group has-label ip-r-g">
                    <div class="row" style="position: relative;top: 6px;">
                        <div class="col-sm-3"><input type="radio" value="all" name="query-type" checked /><label for="query-type">All</label></div>
                        <div class="col-sm-3"><input type="radio" value="filter" name="query-type" /><label for="query-type">Filter</label></div>
                    </div>
                </div>

                <div class="form-group has-label" id="filter-wrap" style="display:none">
                    <span class="error" id="required_yamlname">*</span>
                    <label for="yamlname">Filter name</label>
                    <input class="form-control" name="filter-name" type="text" localized="" placeholder="Enter filter name" data-required="true">
                </div>

            </div>
        </div>
    </div>
</div>

<!--Add Filter-->
<div id="add-aq-filter-container" class="rightSidenav addAdvanceGroup" data-class="md-6">
    <form name="create-filter-form" onsubmit="createFilter($(this), event); return false;" method="POST" action="javascript:void(0);">
        <div class="card-title">
            <h4>Add Filter</h4>
            <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="add-aq-filter-container">&times;</a>
        </div>
        <div class="btnGroup">

            <div class="icon-circle">
                <div  id="submitfilterVal" class="toolTip" onclick="$('form[name=create-filter-form]').trigger('submit')">
                    <i class="tim-icons icon-check-2"></i>
                    <span class="tooltiptext">Save</span>
                </div>
            </div>
        </div>

        <div class="form-check form-check-radio browser">
            <label class="form-check-label" style="margin-left: 23px;">
                <input class="form-check-input" type="radio" id="asset_query" name="asset_query" value="asset_query">
                <span class="form-check-sign"></span>
                Asset
            </label>

            <label class="form-check-label" style="position: absolute;margin-top: -21px;margin-left: 136px;">
                <input class="form-check-input"  type="radio" name="event_query" id="event_query" value="event_query">
                <span class="form-check-sign"></span>
                Event
            </label>
        </div>

        <div class="form table-responsive white-content" id="showasset" style="display:none">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label for="advgname">
                            Asset Filter Name
                        </label>
                        <input class="form-control" type="text" name="fname" data-required="true" data-label="Name" />
                    </div>

                    <div class="form-group has-label">
                        <label for="advgname">
                            Source Fields
                        </label>
                        <select  data-live-search="true" class="selectpicker" data-style="btn btn-info" title="Source Fields" data-size="3" name="source-field"  data-required="true" data-label="Source Field" multiple>
                        </select>
                    </div>
                    <div class="row filter-rows">
                        <div class="col-sm-4">
                            <label>
                                Filter name
                            </label>
                            <select data-live-search="true" data-required="true" data-label="Filter name" class="selectpicker" data-style="btn btn-info" title="Select Filter Name" data-size="3" name="filter-name[]">

                            </select>
                        </div>
                        <div class="col-sm-4">
                            <label>
                                Operator
                            </label>
                            <select  data-required="true" data-label="Operator" class="selectpicker" data-style="btn btn-info" title="Select Asset Filter" data-size="3" id="assetOperator" name="filter-operator[]">
                                <option value="1">Equal</option>
                                <option value="2">Not Equal</option>
                                <option value="3">Contains</option>
                                <option value="4">Match Phrase</option>
                            </select>
                        </div>
                        <div class="col-sm-3">
                            <label>
                                Value
                            </label>
                            <input data-required="true" data-label="Value" class="form-control" type="text" name="filter-value[]"/>
                        </div>
                        <div class="col-sm-1">
                            <i id="add-more-filter" class="dt-icons-l tim-icons icon-simple-add r-ic-plain"></i>
                            <i class="dt-icons-l tim-icons icon-simple-remove r-ic-plain" style="display:none" onclick="removeFilterGrid($(this))"></i>
                        </div>
                        <input style="display:none" type="submit" class="button" value="create"/>
                    </div>
                </div>
            </div>
        </div>

        <div class="form table-responsive white-content" id="showevent" style="display:none">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label for="advgname">
                            Event Filter Name
                        </label>
                        <input class="form-control" type="text" name="fname2" data-required="false" data-label="Name" />
                    </div>

                    <div class="form-group has-label">
                        <label for="advgname">
                            Dart No.
                        </label>
                        <select data-live-search="true" class="selectpicker" id="Dartnumbers_event" data-style="btn btn-info" title="Select Dart number" data-size="3" name="dart_no"  data-required="false" data-label="Dart no." >
                        </select>
                    </div>
                    <div id="Events_filterlist" class="form-group has-label" style="display: none">
                        <div class="form-group has-label">
                            <label for="advgname">
                                Source Fields
                            </label>
                            <select data-live-search="true" class="selectpicker" data-style="btn btn-info" title="Source Fields" data-size="3" id="source-field2" name="source-field2"  data-required="false" data-label="Source Field" multiple>
                            </select>
                        </div>
                        <div class="row filter-rows2">
                            <div class="col-sm-4">
                                <label>
                                    Filter name
                                </label>
                                <select  data-live-search="true" data-required="false" data-label="Filter name" class="selectpicker" data-style="btn btn-info" title="Select Filter Name" id="filter-name2[]" data-size="3" name="filter-name2[]">

                                </select>
                            </div>
                            <div class="col-sm-4">
                                <label>
                                    Operator
                                </label>
                                <select  data-required="false" data-label="Operator" class="selectpicker" data-style="btn btn-info" title="Select Event Filter" data-size="3" id="assetOperator2" name="filter-operator2[]">
                                    <option value="1">Equal</option>
                                    <option value="2">Not Equal</option>
                                    <option value="3">Contains</option>
                                    <option value="4">Match Phrase</option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <label>
                                    Value
                                </label>
                                <input data-required="false" data-label="Value" class="form-control" type="text" name="filter-value2[]"/>
                            </div>
                            <div class="col-sm-1">
                                <i id="add-more-filter2" class="dt-icons-l tim-icons icon-simple-add r-ic-plain"></i>
                                <i class="dt-icons-l tim-icons icon-simple-remove r-ic-plain" style="display:none" onclick="removeFilterGrid2($(this))"></i>
                            </div>
                            <input style="display:none" type="submit" class="button" value="create"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="event-export-range" class="rightSidenav" data-class="sm-3">
    <div class="card-title">
        <h4>Export Historical Details</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="event-export-range">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle">
            <div class="toolTip" onclick="exportEventDetails();">
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
                        <label>
                            Start Date
                        </label>
                        <input type="text" class="form-control datetimepicker" id="datefrom" name="datefrom"  autocomplete="off">
                        <span style="color:red;" id="datefrom_err"></span>
                    </div>

                    <div class="form-group has-label">
                        <label>
                            End Date
                        </label>
                        <input type="text" class="form-control datetimepicker" id="dateto" name="dateto"  autocomplete="off">
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

<!--Edit Filter-->
<div id="edit-aq-filter-container" class="rightSidenav editAdvanceGroup" data-class="md-6">
    <form name="update-filter-form" onsubmit="updateFilter($(this), event); return false;" method="POST" action="javascript:void(0);">
        <div class="card-title">
            <h4>Edit Filter</h4>
            <a href="javascript:void(0)" class="closebtn rightslide-container-close" data-target="edit-aq-filter-container">&times;</a>
        </div>

        <div class="btnGroup">

            <div class="icon-circle">
                <div  id="submitfilterVal" class="toolTip" onclick="$('form[name=update-filter-form]').trigger('submit')">
                    <i class="tim-icons icon-check-2"></i>
                    <span class="tooltiptext">Save</span>
                </div>
            </div>
        </div>

        <div class="form-check form-check-radio browser">
            <label class="form-check-label" style="margin-left: 23px;">
                <input class="form-check-input" type="radio" id="edit_asset_query" name="edit_asset_query" value="edit_asset_query">
                <span class="form-check-sign"></span>
                Asset
            </label>

            <label class="form-check-label" style="position: absolute;margin-left: 136px;">
                <input class="form-check-input"  type="radio" name="edit_event_query" id="edit_event_query" value="edit_event_query">
                <span class="form-check-sign"></span>
                Event
            </label>
        </div>

        <div class="form table-responsive white-content" id="showeassetedit" style="display:none">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label for="advgname">
                            Asset Filter Name
                        </label>
                        <input type="hidden" name="selectedValue" id="selectedValue" value="">
                        <input class="form-control" type="text" name="fnameedit" data-required="true" data-label="Name" />
                    </div>

                    <div class="form-group has-label">
                        <label for="advgname">
                            Source Fields
                        </label>
                        <select  data-live-search="true" class="selectpicker" data-style="btn btn-info" title="Source Fields" data-size="3" name="source-fieldedit"  data-required="true" data-label="Source Field" multiple>
                        </select>
                    </div>
                    <div id ="editFilterData">

                    </div>

                </div>
            </div>
        </div>

        <div class="form table-responsive white-content" id="showeventedit" style="display:none">
            <div class="card">
                <div class="card-body">
                    <div class="form-group has-label">
                        <label for="advgname">
                            Event Filter Name
                        </label>
                        <input class="form-control" type="text" name="fname2edit" data-required="false" data-label="Name" />
                    </div>

                    <div class="form-group has-label">
                        <label for="advgname">
                            Dart No.
                        </label>
                        <select data-live-search="true"  class="selectpicker" id="Dartnumbers_eventedit" data-style="btn btn-info" title="Select Dart number" data-size="3" name="dart_no"  data-required="false" data-label="Dart no." >
                        </select>
                    </div>
                    <div id ="Events_filterlistedit">
                        <div class="form-group has-label">
                            <label for="advgname">
                                Source Fields
                            </label>
                            <select data-live-search="true"  class="selectpicker" data-style="btn btn-info" title="Source Fields" data-size="3" id="source-field2edit" name="source-field2edit"  data-required="false" data-label="Source Field" multiple>
                            </select>
                        </div>
                        <div id="editFilterData2">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>

<style>
    .dropdown-menu.inner li.hidden{
        display:none;
    }

    .dropdown-menu.inner.active li, .dropdown-menu.inner li.active{
        display:block;
    }
</style>


