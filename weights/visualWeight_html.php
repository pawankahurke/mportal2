<?php
    include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
    include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
    ?> <div class="content white-content">
	    <div class="row mt-2">
	        <div class="col-md-12 pl-0 pr-0">
	            <div class="card">
	                <div class="card-body">
                        <!-- loader -->
                        <div id="loader" class="loader"  data-qa="loader" style="position: absolute;bottom: 50%;right:50%;">
                           <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                        </div>
	                <input type="hidden" id="selectedweight">
                    <table id="weightsTable" class="nhl-datatable table table-striped" >
                        <thead>
                            <tr>
                                <th id="key0" headers="MetricName" class="MetricName">
                                    MetricName
                                    <i class="fa fa-caret-down cursorPointer direction" id = "MetricName1" onclick = "addActiveSort('asc', 'MetricName'); getWeightDetails(1,'','MetricName', 'asc');sortingIconColor('MetricName1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "MetricName2" onclick = "addActiveSort('desc', 'MetricName'); getWeightDetails(1,'','MetricName', 'desc');sortingIconColor('MetricName2')" style="font-size:18px"></i>
                                </th>
                                <th id="key1" headers="Category" class="Category">
                                   Category
                                    <i class="fa fa-caret-down cursorPointer direction" id = "Category1" onclick = "addActiveSort('asc', 'Category'); getWeightDetails(1,'','Category', 'asc');sortingIconColor('Category1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "Category2" onclick = "addActiveSort('desc', 'Category'); getWeightDetails(1,'','Category', 'desc');sortingIconColor('Category2')" style="font-size:18px"></i>
                                </th>
								<th id="key2" headers="subcategory" class="subcategory">
                                    Sub-Category
                                    <i class="fa fa-caret-down cursorPointer direction" id = "subcategory1" onclick = "addActiveSort('asc', 'subcategory'); getWeightDetails(1,'','subcategory', 'asc');sortingIconColor('os1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "subcategory2" onclick = "addActiveSort('desc', 'subcategory'); getWeightDetails(1,'','subcategory', 'desc');sortingIconColor('os2')" style="font-size:18px"></i>
                                </th>
                                <th id="key3" headers="MetricDesc" class="MetricDesc">
                                    Description
                                    <i class="fa fa-caret-down cursorPointer direction" id = "MetricDesc1" onclick = "addActiveSort('asc', 'MetricDesc'); getWeightDetails(1,'','MetricDesc', 'asc');sortingIconColor('MetricDesc1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "MetricDesc2" onclick = "addActiveSort('desc', 'MetricDesc'); getWeightDetails(1,'','MetricDesc', 'desc');sortingIconColor('MetricDesc2')" style="font-size:18px"></i>
                                </th>
                                <th id="key4" headers="SpecificInfo" class="SpecificInfo">
                                    Specific Info
                                    <i class="fa fa-caret-down cursorPointer direction" id = "SpecificInfo1" onclick = "addActiveSort('asc', 'SpecificInfo'); getWeightDetails(1,'','SpecificInfo', 'asc');sortingIconColor('SpecificInfo1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "SpecificInfo2" onclick = "addActiveSort('desc', 'SpecificInfo'); getWeightDetails(1,'','SpecificInfo', 'desc');sortingIconColor('SpecificInfo2')" style="font-size:18px"></i>
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


<!-- Add new weight UI starts  -->
<div id="addNew" class="rightSidenav" data-class="lg-9">
    <div class="card-title border-bottom">
        <h4>Add Visualisation Weights</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-target="addNew">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="addWeight">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">
                <div class="form-group has-label">
                    <label for="weight_Type">Type</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="weightType" name="weightType">
                    </select>
                    <span id="err_weight_Type"></span>
                </div>

                <div class="form-group has-label">
                    <label for="weight_Category">Category</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Category" data-size="3" id="weightCategory" name="weightCategory">
                    </select>
                    <span id="err_weight_Category"></span>
                </div>

                <div class="form-group has-label">
                    <label for="weight_scat">Sub-Category</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Sub-Category" data-size="3" id="weightSubCat" name="weightSubCat">
                    </select>
                    <span id="err_weight_scat"></span>
                </div>

                <div class="form-group has-label">
                    <label for="weight_desc">Description</label>
                    <br />
                    <textarea id="weightDescription" name="weightDescription" rows="4" class = "w-100" style = "border-color: rgba(29, 37, 59, 0.2);"></textarea>
                    <span id="err_weight_desc"></span>
                </div>

                <div class="form-group has-label">
                    <label for="sInfo">Tracking Attributes</label>
                    <input class="form-control" name="weightAttr" id="weightAttr" type="text"/>
                    <!-- <select class="selectpicker" data-style="btn btn-info" title="Select Attribute" data-size="3" id="weightAttr" name="weightAttr"> -->
                    <!-- </select> -->
                    <span id="err_sInfo"></span>
                </div>

                <div class="container" >
                    <label for="scores">Score Details</label>
                    <div class='element' id='div_1'>
                    <span class='add'><i class="tim-icons icon-simple-add iconAdd" ></i></span>
                    <table>
                        <tr>
                            <th>From<span id="err_from1"></span></th>
                            <th>To</th>
                            <th>Rank</th>
                            <th>Score</th>
                            <th>Metric Weight</th>
                            <th>Category Weight</th>
                            <th>Sub Category Weight</th>
                        </tr>
                        <tr>
                            <td>
                                <input id='from_1' type = "text" />
                            </td>
                            <td>
                                <input id='to_1' type = "text" />
                            </td>
                            <td>
                                <input id='rank_1' type = "text" />
                            </td>
                            <td>
                                <input id='score_1' type = "text" />
                            </td>
                            <td>
                                <input id='mw_1' type = "text" />
                            </td>
                            <td>
                                <input id='lcw_1' type = "text" />
                            </td>
                            <td>
                                <input id='scw_1' type = "text" />
                            </td>
                        </tr>
                    </table>
                    </div>
                    <span id="err_Scores"></span>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Add new weight UI ends -->

<!-- Edit weight UI starts  -->
<div id="editweight" class="rightSidenav" data-class="lg-9">
    <div class="card-title border-bottom">
        <h4>Update Visualisation Weights</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-target="editweight">&times;</a>
    </div>

    <div class="btnGroup" id="editOption" style="display: block;">
        <div class="icon-circle">
            <div class="toolTip editOption">
                <i class="tim-icons icon-pencil"></i>
                <span class="tooltiptext">Edit</span>
            </div>
        </div>
    </div>

    <div class="btnGroup" style="display: none;" id="toggleButton">
        <div class="icon-circle iconTick circleGrey">
            <div class="toolTip" id="updateWeight" name="updateWeight">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>

        <div class="icon-circle toggleEdit" id="toggleEdit" data-target-container-only="true">
            <div class="toolTip">
                <i class="tim-icons icon-simple-remove"></i>
                <span class="tooltiptext">Toggle edit mode</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">
                <div class="form-group has-label">
                    <label for="editweight_Type">Type</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Type" data-size="3" id="editweightType" name="editweightType">
                    </select>
                    <span id="err_editweight_Type"></span>
                </div>

                <div class="form-group has-label">
                    <label for="editweight_Category">Category</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Category" data-size="3" id="editweightCategory" name="editweightCategory">
                    </select>
                    <span id="err_editweight_Category"></span>
                </div>

                <div class="form-group has-label">
                    <label for="editweight_scat">Sub-Category</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select Sub-Category" data-size="3" id="editweightSubCat" name="editweightSubCat">
                    </select>
                    <span id="err_editweight_scat"></span>
                </div>

                <div class="form-group has-label">
                    <label for="editweight_desc">Description</label>
                    <br />
                    <textarea id="editweightDescription" name="editweightDescription" rows="4" class = "form-control w-100" style = "border-color: rgba(29, 37, 59, 0.2);"></textarea>
                    <span id="err_editweight_desc"></span>
                </div>

                <div class="form-group has-label">
                    <label for="editsInfo">Tracking Attributes</label>
                    <input class="form-control" name="editweightAttr" id="editweightAttr" type="text"/>
                    <!-- <select class="selectpicker" data-style="btn btn-info" title="Select Attribute" data-size="3" id="editweightAttr" name="editweightAttr"> -->
                    <!-- </select> -->
                    <span id="err_editsInfo"></span>
                </div>

                <div class="editcontainer">
                    <label for="editscores1">Score Details</label>
                    <div id="editData" class="form-group has-label"></div>
                    <span id="err_editScores"></span>
                </div>

            </div>
        </div>
    </div>
    <!-- <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">
                <div class="form-group has-label">
                    <label for="deploy_sitename">Type</label><em class="error" id="err_sitename"></em>
                    <select class="selectpicker" data-style="btn btn-info" title="Select SKU" data-size="3" id="deploy_skuid" name="deploy_skuid">
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Category</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select SKU" data-size="3" id="deploy_skuid" name="deploy_skuid">
                    </select>
                </div>

                <div class="form-group has-label">
                    <label for="deploy_delay">Sub-Category</label><em class="error" id="err_delay"></em>
                    <select class="selectpicker" data-style="btn btn-info" title="Select SKU" data-size="3" id="deploy_skuid" name="deploy_skuid">
                    </select>
                </div>

                <div class="form-group has-label">
                    <label for="deploy_delay">Description</label><em class="error" id="err_delay"></em>
                    <br />
                    <textarea id="" name="" rows="4" class = "form-control w-100" style = "border-color: rgba(29, 37, 59, 0.2);"></textarea>
                </div>

                <div class="form-group has-label">
                    <label for="deploy_delay">Tracking Attributes</label><em class="error" id="err_delay"></em>
                    <select class="selectpicker" data-style="btn btn-info" title="Select SKU" data-size="3" id="deploy_skuid" name="deploy_skuid">
                    </select>
                </div>

                <div class="form-group has-label">
                    <table>
                        <tr>
                            <th>From</th>
                            <th>To</th>
                            <th>Rank</th>
                            <th>Score</th>
                            <th>mw</th>
                            <th>cw</th>
                            <th>scw</th>
                        </tr>
                        <tr>
                            <td>
                                <input class = "form-control" type = "text" />
                            </td>
                            <td>
                                <input class = "form-control" type = "text" />
                            </td>
                            <td>
                                <input class = "form-control" type = "text" />
                            </td>
                            <td>
                                <input class = "form-control" type = "text" />
                            </td>
                            <td>
                                <input class = "form-control" type = "text" />
                            </td>
                            <td>
                                <input class = "form-control" type = "text" />
                            </td>
                            <td>
                                <input class = "form-control" type = "text" />
                            </td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    </div> -->
</div>

<style>
    .red {
    outline: 1px solid red;
    }

    .iconAdd{
        width: 20px;
        height: 20px;
        border-radius: 50%;
        cursor: pointer;
        color: #fff;
        background-color: #050d30;
        padding-top: 3px;
        padding-left: 4px;
        font-size: 0.75rem;
        font-weight: bold;
        position: relative;
        left: 95%;
        top: 38px;
    }

    .dataTables_scrollHeadInner{
        width: auto !important;
    }

    .showbtn{
        margin-left: 119px;
    }

    .clearbtn{
        margin-left: 119px;
    }

    .addNewWidth{
        width: 73%;
    }

    .hideAbsoFeed{
        display:none
    }

    .showAbsoFeed{
        display:block
    }

    #absoFeeds {
        /* display: none; */
        width: 0px;
        height: calc(100vh - 50px);
        float: left;
        background: #858d93;
        /* z-index: 99999; */
        z-index: 1111;
        top: 52px;
        position: absolute;
        opacity: 0.5;
    }

    #absoFeed {
        /* display: none; */
        width: 0px;
        height: calc(100vh - 50px);
        float: left;
        background: #858d93;
        /* z-index: 99999; */
        z-index: 1111;
        top: 52px;
        position: absolute;
        opacity: 0.5;
    }

    .form-group {
        font-weight: 600;
    }

    .dropdown-menu.inner li.hidden{
        display:none;
    }

    .dropdown-menu.inner.active li, .dropdown-menu.inner li.active{
        display:block;
    }
</style>
