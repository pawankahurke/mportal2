	<?php
	include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
	include_once $absDocRoot . 'vendors/csrf-magic.php';
	csrf_check_custom();
	?> <div class="content white-content">
		<div class="row mt-2">
			<div class="col-md-12 pl-0 pr-0">
				<div class="card">
					<div class="card-body">
						<?php
						// nhRole::checkRoleForPage('device');
						$res = true; // nhRole::checkModulePrivilege('device');
						if ($res) {
						?>
							<!-- loader -->
							<div id="loader" class="loader" data-qa="loader" style="position: absolute;bottom: 50%;right:50%;">
								<img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
							</div>
							<div class="toolbar">
							</div>
							<input type="hidden" id="selected">
							<input type="hidden" id="grupnamehidden">
							<input type="hidden" id="totalmachinecount">
							<input type="hidden" id="hiddengrpid">
							<input type="hidden" id="groupType">
							<input type="hidden" id="groupid">
							<input type="hidden" id="groupname">
							<input type="hidden" id="selectedAssetData">

							<table class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="advncdgroupList">
								<thead>
									<tr>
										<th id="key0" headers="name" class="sortArrow">
											Group Name
											<i class="fa fa-caret-down cursorPointer direction" id="name1" onclick="addActiveSort('asc', 'name'); get_advncdgroupData(1, notifSearch = '', 'name', 'asc'); sortingIconColor('name')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="name1" onclick="addActiveSort('desc', 'name'); get_advncdgroupData(1,notifSearch = '','name', 'desc'); sortingIconColor('name')" style="font-size:18px"></i>
										</th>
										<th id="key1" headers="number" class="sortArrow">
											Machine Count
											<i class="fa fa-caret-down cursorPointer direction" id="number1" onclick="addActiveSort('asc', 'number'); get_advncdgroupData(1, notifSearch = '', 'number', 'asc'); sortingIconColor('number1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="number2" onclick="addActiveSort('desc', 'number'); get_advncdgroupData(1, notifSearch = '', 'number', 'desc'); sortingIconColor('number2')" style="font-size:18px"></i>
										</th>
										<th id="key2" headers="boolstring" class="sortArrow">
											Group Type
											<i class="fa fa-caret-down cursorPointer direction" id="boolstring1" onclick="addActiveSort('asc', 'boolstring'); get_advncdgroupData(1, notifSearch = '','boolstring', 'asc'); sortingIconColor('boolstring1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="boolstring2" onclick="addActiveSort('desc', 'boolstring'); get_advncdgroupData(1, notifSearch = '','boolstring', 'desc'); sortingIconColor('boolstring2')" style="font-size:18px"></i>
										</th>
										<th id="key3" headers="username" class="sortArrow">
											Created By
											<i class="fa fa-caret-down cursorPointer direction" id="username1" onclick="addActiveSort('asc', 'username'); get_advncdgroupData(1, notifSearch = '','username', 'asc'); sortingIconColor('username1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="username2" onclick="addActiveSort('desc', 'username'); get_advncdgroupData(1, notifSearch = '','username', 'desc'); sortingIconColor('username1')" style="font-size:18px"></i>
										</th>
										<th id="key4" headers="created" class="sortArrow">
											Created Date Time
											<i class="fa fa-caret-down cursorPointer direction" id="created1" onclick="addActiveSort('asc', 'created'); get_advncdgroupData(1, notifSearch = '','created', 'asc'); sortingIconColor('created1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="created2" onclick="addActiveSort('desc', 'created'); get_advncdgroupData(1, notifSearch = '','created', 'desc'); sortingIconColor('created2')" style="font-size:18px"></i>
										</th>
										<th id="key4" headers="modifiedby" class="sortArrow">
											Modified By
											<i class="fa fa-caret-down cursorPointer direction" id="modifiedby1" onclick="addActiveSort('asc', 'modifiedby'); get_advncdgroupData(1, notifSearch = '', 'modifiedby', 'asc'); sortingIconColor('modifiedby1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="modifiedby2" onclick="addActiveSort('desc', 'modifiedby'); get_advncdgroupData(1, notifSearch = '','modifiedby', 'desc'); sortingIconColor('modifiedby2')" style="font-size:18px"></i>
										</th>
										<th id="key4" headers="modifiedtime" class="sortArrow">
											Modified Date Time
											<i class="fa fa-caret-down cursorPointer direction" id="modifiedtime1" onclick="addActiveSort('asc', 'modifiedtime'); get_advncdgroupData(1, notifSearch = '','modifiedtime', 'asc'); sortingIconColor('modifiedtime1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="modifiedtime2" onclick="addActiveSort('desc', 'modifiedtime'); get_advncdgroupData(1, notifSearch = '','modifiedtime', 'desc'); sortingIconColor('modifiedtime2')" style="font-size:18px"></i>
										</th>
									</tr>
								</thead>
							</table>
						<?php
						}
						?>
<!--						<div class="col-md-12" id="errorMsg" style="display:none;">-->
<!--							<span>Please select site or group to view list</span>-->
<!--						</div>-->
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


	<!-- Add New Group starts -->
	<div id="grp-addmod-container" class="rightSidenav addGroup" data-class="md-6">
		<div class="card-title border-bottom">
			<h4>Add group</h4>
			<a data-qa="closeBtn" href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="grp-addmod-container">&times;</a>
		</div>
		<div class="btnGroup" style="display: block;">
			<div class="icon-circle" id="csvuploadbutton1" style="display: none;">
				<div class="toolTip" onclick="csvcggroupcreate();">
					<i class="tim-icons icon-check-2"></i>
					<span class="tooltiptext">Save</span>
				</div>
			</div>

			<div class="icon-circle" id="manualmachinebutton1" style="display: none;">
				<div class="toolTip" onclick="manualcggroupcreate1();">
					<i class="tim-icons icon-check-2"></i>
					<span class="tooltiptext">Save</span>
				</div>
			</div>

			<div class="icon-circle" id="dynamicuploadbutton1" style="display: none;">
				<div data-qa="saveBtn" class="toolTip" onclick="dynamicgroupcreate1();">
					<i class="tim-icons icon-check-2"></i>
					<span class="tooltiptext">Save</span>
				</div>
			</div>
		</div>
		<div class="form table-responsive white-content">
			<div class="card" style="padding:15px 15px;">
				<div class="card-body">
					<div class="form-group has-label">
						<label for="csvgname">
							How you would like to add machines to the group?
						</label>
					</div>

					<div class="form-check form-check-radio global">
						<label class="form-check-label">
							<input class="form-check-input" type="radio" id="csvradio1" name="exampleRadios" value="option1">
							<span class="form-check-sign"></span>
							CSV
						</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

						<label class="form-check-label">
							<input class="form-check-input" type="radio" name="exampleRadios" id="manualradio1" value="option2">
							<span class="form-check-sign"></span>
							Manual
						</label>
						</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<!--						<label data-qa="formCheckDynamic" class="form-check-label btn-centus-filter">-->
<!--							<input class="form-check-input" type="radio" name="exampleRadios" id="dynamicradio1" value="option3">-->
<!--							<span class="form-check-sign"></span>Dynamic</label>-->
					</div>
				</div>
				<input type="hidden" id="selectedDynamicFilter">
				<input type="hidden" id="editGrpType">
				<!-- Dynamic Group Data -->
				<div class="card-body" id="DynamicGroup" style="display:none">
					<p>&nbsp;</p>
					<div class="form-group has-label">
						<label for="advgname">
							Group Name
						</label>
						<input data-qa="checkGroupNameDynamic" class="form-control" type="text" id="advgname" />
					</div>

					<div class="form-group has-label">
						<label>
							Select the sites
						</label>
						<span data-qa="selectTSitesDynamic"><select class="selectpicker" multiple data-style="btn btn-info" title="Select Site" data-size="3" id="dynamicsitelist" name="dynamicsitelist" onchange="setsiteValuehidden();">
							</select></span>
					</div>

					<div class="form-group has-label">
						<label>
							Select the Users
						</label>
						<span data-qa="selectTUsersDynamic"> <select class="selectpicker" multiple data-style="btn btn-info" title="Select User" data-size="3" id="dynamicuserlist" name="dynamicuserlist">
							</select></span>
					</div>

					<div class="card-body">
						<div class="form-group has-label">
							<label for="csvgname">
								Select the Dynamic type filter:
							</label>
						</div>

						<div class="form-check form-check-radio">
<!--							<label class="form-check-label">-->
<!--								<input class="form-check-input" type="radio" id="dynamic1" name="exampleRadios" value="optiondynamic1">-->
<!--								<span class="form-check-sign"></span>-->
<!--								Asset Filter-->
<!--							</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-->

<!--							<label data-qa="censusFilter" class="form-check-label">-->
<!--								<input class="form-check-input" type="radio" name="exampleRadios" id="dynamic2" value="optiondynamic2" checked>-->
<!--								<span class="form-check-sign"></span>-->
<!--								Census Filter-->
<!--							</label>-->
<!--							</label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-->
						</div>
					</div>

					<div class="form-group has-label" id="assetfilter" style="display:none">
						<p>&nbsp;</p>
						<label>
							Asset Filter
						</label>
						<select data-live-search="true" class="selectpicker" data-style="btn btn-info" title="Select Asset Filter" onchange="showOtherValues(this)" data-size="3" id="assetFilter" name="assetFilter">
						</select>

						<div id="assetSubFilterDiv" style="display:none">
							<label>
								Asset Sub Filter
							</label>
							<select data-live-search="true" class="selectpicker" data-style="btn btn-info" title="Select Sub Asset Filter" data-size="3" id="assetSubFilter" name="assetSubFilter">
							</select>
						</div>

						<label>
							Operator
						</label>
            <select class="selectpicker" data-style="btn btn-info" title="Select Asset Operator" data-size="3"
                    id="assetOperator" name="assetOperator">
              <option value="1">Equal</option>
              <option value="2">Not Equal</option>
              <option value="3">Contains</option>
            </select>

						<label>
							Value
						</label>
						<input class="form-control" type="text" id="assetVal" />
					</div>

					<div class="form-group has-label" id="censusfilter">
						<p>&nbsp;</p>
						<label>
							Value
						</label>
						<input data-qa="censusFilterForm" class="form-control" type="text" id="censusVal" />
						<span style="color:red">Enter the value to match the Machine Name to create a group</span>
					</div>

				</div>
				<!-- Dynamic Group Data -->
				<!-- CSV File Upload -->
				<div class="card-body" id="csvuploaddata1" style="display:none;">
					<p>&nbsp;</p>
					<div class="form-group has-label">
						<label>
							Select the sites to see the list of configured machine groups
						</label>
						<select class="selectpicker" multiple data-style="btn btn-info" title="Select Site" data-size="3" id="cgsitelist" name="sitelist" onchange="setsiteValuehidden();">

						</select>
					</div>



					<!-- <p class="manual_group">
                    <span class="download_csv" onclick="downloadSample();">Click Here</span> to Download sample CSV
                </p>-->
					<form method="post" name="exportform" action="../cgroup/generate.php" enctype="multipart/form-data" style="margin-top:-15px;margin-left:-10px;">
						<input type="hidden" name="sitelist" value="" id="cgslist" />
						<button type="submit" class="btn btn-link" name="Export" style="font-size:10px;"><u>Click Here</u></button><span style="margin-left:-10px;"> to Download CSV</span>
					</form>

					<p>&nbsp;</p>
					<form name="csvform" id="csvsubmit" method="post" enctype="multipart/form-data">
						<div class="form-group has-label ">
							<!--<input class="form-control" type="text" id="uploadcsv" />-->
							<!-- <div class="col-md-12">
                    <p>
                        <b>Select the CSV file that has details of the machines that must be part of this group</b>
                    </p>
                </div>-->
							<p style="font-size:10px;">
								Upload the CSV with the machine grouping
							</p>
							<!--<p id="title_csv">Upload CSV</p>-->

							<!--<div class="col-md-12">-->
							<p id="file_sel1" class="txtsm" style="font-size:10px;">File selected : <span id="csv_name1"></span></p><br /><br />
							<!--</div>-->

							<div class="btnBrowser">
								<span class="btn btn-round btn-rose btn-file btn-sm">
									<span class="fileinput-new">Browse</span>
									<input type="file" name="csv" id="csv_file1" name="csv_file1" accept=".csv" />
								</span>

								<span class="btn btn-round btn-success btn-sm" id="remove_logo1" style="display:none">
									<span class="fileinput-new">Remove</span>
								</span>
							</div>
							<p>&nbsp;</p>
							<p>&nbsp;</p>

						</div>
						<input type="hidden" name="groups" id="groupsDet" value="" />

						<div class="card-body" id="accesslist">



						</div>



					</form>

				</div>

				<!--   CSV File Upload -->

				<!-- Manual File Upload -->
				<p>&nbsp;</p>
				<p>&nbsp;</p>
				<div id="manualmachinelist1" style="display:none;">
					<form name="csvform" id="manualsubmit" method="post" enctype="multipart/form-data">
						<div class="form-group has-label">
							<label>
								Please select the type of group you would like to create
							</label>
							<select class="selectpicker" data-style="btn btn-info" title="Select Group Type" data-size="3" id="groupcgType" name="groupType">

							</select>
							<span id="add_userLevel-err1"></span>
						</div>

						<div class="form-group has-label">

							<label for="csvgname">
								Group Name
							</label>
							<input class="form-control" type="text" id="manualgname" name="gname" />
						</div>


						<div class="form-group has-label">
							<label>
								Assign Group to Users
							</label>
							<select data-live-search="true" class="selectpicker" multiple data-style="btn btn-info" title="Select Group Users" data-size="3" id="groupcgUsers" name="groupUsers">

							</select>
							<span id="add_userLevel-err1"></span>
						</div>
						<p class="manual_group " id="groupslider1"><span class="add_group">Click here</span> to select the device manually</p>
						<div id="machine_count1" style="display:none;">
							<p><span id="machinecount1"></span> Devices are added to this group
							<p class="manual_group add_group"> Edit</p>
							</p>
						</div>
					</form>
				</div>



			</div>
		</div>
	</div>
	<!-- Add New Group  end-->

	<!-- Edit Group -->
	<div id="edit-group" data-bs-target="edit_group_drop" class="rightSidenav" data-class="md-6">
		<div class="card-title border-bottom">
			<h4>Edit group</h4>
			<a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="edit-group">&times;</a>
		</div>

		<div class="btnGroup" style="display: block;" id="editOption">
			<div class="icon-circle">
				<div class="toolTip editOption" data-target-container-only="true">
					<i class="tim-icons icon-pencil"></i>
					<span class="tooltiptext">Edit</span>
				</div>
			</div>
		</div>

		<div class="btnGroup" id="toggleButton" style="display: none;">
			<div class="icon-circle iconTick" id="editcsvuploadbutton">
				<!-- circleGrey -->
				<div class="toolTip" onclick="csvgroupeditinside();">
					<i class="tim-icons icon-check-2"></i>
					<span class="tooltiptext">Save edit change</span>
				</div>
			</div>


			<div class="icon-circle toggleEdit" id="toggleEdit" data-target-container-only="true">
				<div class="toolTip">
					<i class="tim-icons icon-simple-remove"></i>
					<span class="tooltiptext">Toggle edit mode</span>
				</div>
			</div>
		</div>
		<div class="form table-responsive white-content">
			<form id="RegisterValidation">
				<div class="card">
					<div class="card-body">
						<div class="form-group has-label">
							<label for="csvgname" class="  text-uppercase font-weight-bold">
								Edit Group Users
							</label>
						</div>
						<div class="form-group has-label " id="editfocused">

							<label for="editcsvgname">
								Group Name
							</label>
							<input class="form-control" type="text" id="editcsvgname" data-after-form-text-input-readonly="true" />
						</div>
					</div>
					<div class="card-body">
						<!--<div class="form-group has-label">
                        <label for="csvgname">
                            Would this group be Global?
                        </label>
                    </div>
                    <div class="form-check form-check-radio edit">
                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="editRadios1" id="editglobalyes">
                            <span class="form-check-sign"></span>
                            Yes
                        </label>

                        <label class="form-check-label">
                            <input class="form-check-input" type="radio" name="editRadios1" id="editglobalno">
                            <span class="form-check-sign"></span>
                            No
                        </label>
                    </div>-->

						<div class="form-group has-label">
							<label>
								Assign Group to Users
							</label>
							<select data-live-search="true" class="selectpicker" multiple data-style="btn btn-info" title="Select Group Users" data-size="3" id="editGroupUsers" name="editGroupUsers">

							</select>
							<span id="add_userLevel-err"></span>
						</div>
						<div class="form-group has-label" style="display:none;">
							<label>
								Group Category
							</label>
							<select class="selectpicker" data-style="btn btn-info" title="Select Group Category" data-size="3" id="editGroupStyle" name="editGroupStyle">

							</select>
							<span id="add_styleLevel-err"></span>
						</div>
					</div>
					<!-- <div class="card-body">
                        <div class="form-group has-label">
                            <label for="csvgname">
                                Edit Devices to Groups
                            </label>
                        </div>
                       <div class="form-check form-check-radio edit">
                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" id="editcsvradio" name="editRadios" value="option1">
                                <span class="form-check-sign"></span>
                                CSV
                            </label>

                            <label class="form-check-label">
                                <input class="form-check-input" type="radio" name="editRadios" id="editmanualradio" value="option2">
                                <span class="form-check-sign"></span>
                                Manual
                            </label>
                        </div>
                    </div> -->
				</div>

				<div class="row form-toggle-item">
					<div data-class="md-6" id="editcsvuploaddata">
						<div class="col-md-12">
							<div class="form-group has-label">
								<label for="csvgname" class="  text-uppercase font-weight-bold">
									Add Devices to Groups
								</label>
							</div>
							<p class="manual_group">
								<span class="download_csv" onclick="samplecsvfileExport();">Click Here</span> to Download CSV
							</p>
						</div>

						<div class="fileinput fileinput-new" data-provides="fileinput">
							<div class="form-group has-label ">
								<div class="col-md-12">
									<p style="font-size:10px;">
										Select the CSV file that has details of the machines that must be part of this group
									</p>
								</div>

								<div class="col-md-12" id="file_sel_edit">
									<p class="txtsm">File selected : <span id="csv_name_edit"></span></p>
								</div>

								<div class="col-md-12 btnBrowser">
									<span class="btn btn-round btn-rose btn-file btn-sm">
										<span class="fileinput-new">Browse</span>
										<input type="file" name="csvedit" id="csv_file_edit" accept=".csv" />
									</span>

									<span class="btn btn-success btn-round btn-sm" id="remove_logo_edit" style="display:none">
										<span class="fileinput-new">Remove</span>
									</span>
								</div>

								<!-- <p class="manual_group">
                                        <span class="download_csv" onclick="samplefileExport();">Click Here</span> to Download sample CSV
                                    </p>-->


							</div>
						</div>
					</div>
				</div>
				<!--   CSV Edit File Upload -->

				<!-- Manual Edit File Upload -->

				<div class="form-toggle-item">
					<div id="editmanualmachinelist" style="display:none;">
						<div id="machine_count">
							<p><span id="editmachinecount"></span><input type="hidden" id="machine_list"></p> Devices are added to this group
							<p class="manual_group manual_editgroup"><span class="add_group">Edit</span></p>
							</p>
						</div>

					</div>
				</div>
				<!--  Manual  Edit File Upload -->

			</form>
		</div>
	</div>
	<!--Edit group end-->


	<!--Edit Advanced Group-->
	<div id="advgrp-edit-container" class="rightSidenav addAdvanceGroup" data-class="md-6">
		<div class="card-title border-bottom">
			<h4>Edit Advance group</h4>
			<a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="advgrp-edit-container">&times;</a>
		</div>

		<div class="btnGroup" id="toggleButton">
			<div class="icon-circle iconTick" id="editAdvGrpDiv">
				<div class="toolTip" onclick="updateAdvanceGrp();">
					<i class="tim-icons icon-check-2"></i>
					<span class="tooltiptext">Update</span>
				</div>
			</div>
		</div>

		<div class="form table-responsive white-content">
			<div class="card">
				<div class="card-body">
					<div class="form-group has-label">
						<label for="advgname">
							Group Name
						</label>
						<input class="form-control" type="text" id="edit-advgname" readonly="" />
					</div>
				</div>

				<div class="card-body">
					<div class="form-group has-label">
						<label>
							Assign Group to Users
						</label>
						<select class="selectpicker" multiple data-style="btn btn-info" title="Select Group Users" data-size="3" id="edit-groupUsers2" name="edit-groupUsers2">

						</select>
						<span id="add_userLevel-err"></span>
					</div>
				</div>

				<div class="form-group has-label">
					<label>
						Select the sites
					</label>
					<select class="selectpicker" multiple data-style="btn btn-info" title="Select Site" data-size="3" id="Edynamicsitelist" name="Edynamicsitelist" onchange="setsiteValuehidden();">
					</select>
				</div>

				<div class="form-group has-label" id="editassetfilter" style="display:none">
					<p>&nbsp;</p>
					<label>
						Asset Filter
					</label>
					<select data-live-search="true" class="selectpicker" data-style="btn btn-info" title="Select Asset Filter" onchange="showOtherValues(this)" data-size="3" id="editassetFilter" name="editassetFilter">
					</select>

					<div id="editassetSubFilterDiv" style="display:none">
						<label>
							Asset Sub Filter
						</label>
						<select data-live-search="true" class="selectpicker" data-style="btn btn-info" title="Select Sub Asset Filter" data-size="3" id="editassetSubFilter" name="editassetSubFilter">
						</select>
					</div>

					<label>
						Operator
					</label>
					<select class="selectpicker" data-style="btn btn-info" title="Select Asset Operator" data-size="3" id="editassetOperator" name="editassetOperator">
						<option value="1">Equal</option>
						<option value="2">Not Equal</option>
						<option value="3">Contains</option>
					</select>

					<label>
						Value
					</label>
					<input class="form-control" type="text" id="editassetVal" />
				</div>

				<div class="form-group has-label" id="editcensusfilter" style="display:none">
					<p>&nbsp;</p>
					<label>
						Value
					</label>
					<input class="form-control" type="text" id="editcensusVal" />
					<span style="color:red">Enter the value to match the Machine Name to create a group</span>
				</div>

			</div>
		</div>
	</div>

	<!--View Group-->
	<div id="viewgrp-add-container" class="rightSidenav viewAdvanceGroup" data-class="md-6">
		<div class="card-title border-bottom">
			<h4>Group details</h4>
			<a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="viewgrp-add-container">&times;</a>
		</div>

		<div class="form table-responsive white-content">
			<div class="card">
				<table id="versionDetail" class="nhl-datatable table table-striped">
					<thead>
						<tr>
							<th>Machine</th>
							<th>Site</th>
							<th>Last Event</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>


	<!--Device list slider starts-->
	<div id="rsc-add-container5" class="rightSidenav addGroupSidenav" data-class="sm-3">
		<div class="card-title border-bottom">
			<h4 id="groupName"></h4>
			<a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="rsc-add-container5">&times;</a>
		</div>

		<!--    <div class="col-md-12 text-left">
            Select devices to add to this group
        </div>-->
		<div class="btnGroup addButton">
			<div class="icon-circle">
				<div class="toolTip" onclick="addDevices();">
					<i class="tim-icons icon-check-2"></i>
					<span class="tooltiptext">Add Devices</span>
				</div>
			</div>
		</div>
		<div class="btnGroup editButton" style="display:none;">
			<div class="icon-circle">
				<div class="toolTip" onclick="editDevices();">
					<i class="tim-icons icon-check-2"></i>
					<span class="tooltiptext">Modify</span>
				</div>
			</div>
		</div>

		<div class="form table-responsive white-content">
			<div align="center" class="margin-top100">
				<div id="loader" class="loader" data-qa="loader" style="display:none">
					<br>
					<img src="../assets/img/loader.gif" />
					<br>
					<h5>Please wait..!</h5>
				</div>
			</div>
			<div class="sidebar">
				<div class="rm-search-parent">
					<input type="text" data-bs-target="include_machine" placeholder="Search machines..." class="nhl-htm-search-box form-control" autocomplete="off" role="textbox" aria-label="Search">
				</div>
				<ul class="nav rm-adjust-height" id="include_machine">

				</ul>
			</div>
		</div>

		<div class="col-md-12 text-center submit">
			<button type="button" class="swal2-confirm btn btn-success btn-sm addButton" aria-label="" onclick="gobackToAdd();">Back to Add</button>
			<button type="button" class="swal2-confirm btn btn-primary btn-sm editButton" aria-label="" onclick="gobackToEdit();" style="display:none;">Back to Edit</button>
		</div>
	</div>
	<!--Device list slider end-->

	<!--Group info slider starts-->
	<div id="group-info-container" class="rightSidenav addGroup" data-class="md-6">
		<div class="card-title border-bottom">
			<h4>Group Information</h4>
			<a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="group-info-container">&times;</a>
		</div>

		<div class="form table-responsive white-content">
			<div class="card">
				<div class="card-body">
					<div class="form-group has-label">
						<label for="csvgname">
							Group Name
						</label>
						<input class="form-control" type="text" id="grpcsvgname" readonly />
					</div>
					<div class="form-group has-label">
						<label for="csvgname">
							Group Type
						</label>
						<input class="form-control" type="text" id="grpstyle" />
					</div>
					<div class="form-group has-label">
						<label for="csvgname">
							Created By
						</label>
						<input class="form-control" type="text" id="grpcreatedby" readonly />
					</div>
					<div class="form-group has-label">
						<label for="csvgname">
							Created On
						</label>
						<input class="form-control" type="text" id="grpcreatedon" readonly />
					</div>
					<div class="form-group has-label">
						<label for="csvgname">
							Modified By
						</label>
						<input class="form-control" type="text" id="grpmodifiedby" readonly />
					</div>
					<div class="form-group has-label">
						<label for="csvgname">
							Modified On
						</label>
						<input class="form-control" type="text" id="grpmodifiedon" readonly />
					</div>

				</div>

			</div>
		</div>
		<!--Group info slider closes-->

		<style>
			.dropdown-menu.inner li.hidden {
				display: none;
			}

			.dropdown-menu.inner.active li,
			.dropdown-menu.inner li.active {
				display: block;
			}
		</style>
		<script src="../assets/js/core/jquery.min.js"></script>
		<script>

      $('.btn-centus-filter').click(function () {
        $('#censusfilter').show();
      })
			var users = '';
			$(document).ready(function() {
				$('#assetSubFilterDiv').hide();

				$('#dynamic1').change(function() {
					$('#selectedDynamicFilter').val('Asset');
					$('#assetfilter').show();
					// $('#censusfilter').hide();
				});

				$('#dynamic2').change(function() {
					$('#selectedDynamicFilter').val('Census');
					$('#assetfilter').hide();
					$('#censusfilter').show();
				});

				$('#csvradio1').change(function() {
					$('#assetfilter').hide();
					$('#censusfilter').hide();
					$('#csvuploaddata1').show();
					$('#csvuploadbutton1').show();
					$('#manualcgmachinebutton1').hide();
					$('#manualmachinebutton1').hide();
					$('#dynamicuploadbutton1').hide();
					$('#manualmachinelist1').hide();
					$('#successmsg1').html('');
					$('#successmsg1').hide();
					$('#DynamicGroup').hide();
					$('#assetSubFilterDiv').hide();
				});
				$('#manualradio1').change(function() {
					$('#assetfilter').hide();
					$('#censusfilter').hide();
					$('#DynamicGroup').hide();
					$("#add-manual").show();
					$('#csvuploaddata1').hide();
					$('#csvuploadbutton1').hide();
					$('#manualmachinebutton1').show();
					$('#dynamicuploadbutton1').hide();
					$('#manualmachinelist1').show();
					$('#include_machine').html('');
					$('#groupslider1').show();
					$('#machine_count1').hide();
					$('#loader1').show();
					$('#assetSubFilterDiv').hide();
					// getStylesList();
					$.ajax({
						type: "POST",
						url: "../admin/groupfunctions.php",
						data: {
							'function': 'get_MachineList',
							csrfMagicToken: csrfMagicToken
						},
						dataType: "json"
					}).done(function(data) {
						$('.loader').hide();
						if ($.trim(data.state) === 'success') {
							$('.loader').hide();
							if (data.option == '') {
								$('#include_machine').html('<span style="margin:20px">No Machine available</span>');
							} else {
								$('#include_machine').html(data.option);
							}
						}
					});

					//    $('#successmsg').html('');
					//    $('#successmsg').hide();
				});
				$('#dynamicradio1').change(function() {
					$('#assetfilter').hide();
					// $('#censusfilter').hide();
					$('#dynamicuploadbutton1').show();
					$('#DynamicGroup').show();
					$('#csvuploaddata1').hide();
					$('#csvuploadbutton1').hide();
					$('#manualcgmachinebutton1').hide();
					$('#manualmachinebutton1').hide();
					$('#manualmachinelist1').hide();
					$('#successmsg1').html('');
					$('#successmsg1').hide();
					$("#add-manual").hide();
					$('#csvuploaddata1').hide();
					$('#csvuploadbutton1').hide();
					$('#manualmachinebutton1').hide();
					$('#manualmachinelist1').hide();
					$('#include_machine').html('');
					$('#groupslider1').hide();
					$('#machine_count1').hide();
					$('#loader1').hide();
				});
				$("#csv_file1").on("change", function() {
					var file_data = $("#csv_file1").prop("files")[0];
					var logo_data = new FormData();
					var csv_name = $("#csv_file1").prop("files")[0]["name"];
					$('#remove_logo1').css('display', '');
					$('#accesslist').show();
					$('#accesslist').html('<br/><br/><p>Please wait while we are analysing the details of your machine group</p><p>&nbsp;</p>');

					logo_data.append("file", file_data);
					logo_data.append("type", "headerlogo");
					logo_data.append('csrfMagicToken', csrfMagicToken);

					$("#csv_name1").html(csv_name).css({
						color: "black"
					});
					$(".logo_loader").show();
					$.ajax({
						url: '../cgroup/groupfunc.php',
						type: 'POST',
						data: logo_data,
						success: function(res) {
							var result = JSON.parse(res);
							//alert(r);
							var htmlstr = '';
							$('#accesslist').html('');
							$('#accesslist').show();
							//var str = '{"Department":["HR","Finance","Marketing","IT"],"Segment":["Remote","Laptop"],"GEO Location":["India","Mumbai","US","Bangalore"],"City":["Mumbai","Delhi","Newyork","Bangalore"]}';
							//var srest = $.parseJSON(str);
							var htmls = '';
							var countvalues = 0;
							var groupsdet = [];
							$.each(result.allGroup, function(index, value) {
								//countvalues = countvalues + value.length;
								groupsdet.push(value);
							});
							$.each(result.uniqueGroup, function(index, value) {
								countvalues = countvalues + value.length;
								//groupsdet.push(value);
							});

							$('#groupsDet').val(groupsdet.toString());
							//console.log(users);
							htmls = "<b>" + countvalues + ' unique machine groups found</b> <br/> Kindly check the group names and confirm that everything is exactly the way you need them. If you have made any mistake,Kindly make the changes and upload the CSV with the corrections <br/><br/>';
							$.each(result.uniqueGroup, function(index, value) {
								$.each(value, function(i, val) {
									htmls = htmls + "<b>" + index + " : " + val + " </b><br/> ";
									var sid = index + 'cguserslist' + val;
									sid = sid.replace(/ /g, "_");
									htmls = htmls + '<div class="form-group has-label"> <label> Select the users who will have access to this group</label> <select class="selectpicker" multiple data-style="btn btn-info" title="Select Users" data-size="3" id="' + sid + '" name="userlist-' + index + val + '"> </select> </div>';
									$('#accesslist').html(htmls);
								});


							});
							var formData = new FormData();
							formData.append('function', 'get_UsersList');
							formData.append('csrfMagicToken', csrfMagicToken);

							$.ajax({
								url: "../admin/groupfunctions.php",
								type: 'POST',
								data: formData,
								processData: false,
								contentType: false,
								dataType: 'html',
								success: function(data) {

									$.each(result.uniqueGroup, function(index, value) {
										$.each(value, function(i, val) {
											//console.log(val);
											var id = '#' + index + 'cguserslist' + val;
											id = id.replace(/ /g, "_")
											$(id).html(data);
											$(".selectpicker").selectpicker("refresh");
										});
									});
								},
								error: function(errorThrown) {
									console.log(errorThrown);
								}
							});



						},
						cache: false,
						contentType: false,
						processData: false
					});





				});

				$("#remove_logo1").click(function() {
					$("#csv_file1").val("");
					$("#csv_name1").html("");

					$(".logo").attr("src", "../assets/img/bask-logo.png");
				});


			});

			function getrequiredList() {
				// rightMenuFunctionality();
				getCGUsersList();
				getCGSiteList();
				getStylesList();
				get_FilterList();
				//setaccesslist();
			}

			function samplecsvfileExport() {
				//    var functioncall    = "function=get_editgroupname&editgid=" + editgid;
				//    var encryptedData   = get_RSA_EnrytptedData(functioncall);
				var selected = $('#hiddengrpid').val();
				window.location.href = '../cgroup/generategroup.php?grpid=' + selected;
			}

			function getStylesList() {
				var formData = new FormData();
				formData.append('csrfMagicToken', csrfMagicToken);

				$.ajax({
					url: "../cgroup/getStyles.php",
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					dataType: 'html',
					success: function(data) {

						var d = $.parseJSON(data);

						// alert(data);
						//users = data;
						$('#groupcgType').html(d);
						// $('.cguserlist').html('<option value="JohnSatya">JohnSatya</option><option value="admin">admin</option>');
						$(".selectpicker").selectpicker("refresh");
						//updateResult(data);
					},
					error: function(errorThrown) {
						console.log(errorThrown);
					}
				});
			}

			function getCGUsersList() {
				var formData = new FormData();
				formData.append('function', 'get_UsersList');
				formData.append('csrfMagicToken', csrfMagicToken);

				$.ajax({
					url: "../admin/groupfunctions.php",
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					dataType: 'html',
					success: function(data) {
						$('#groupcgUsers').html(data);
						$(".selectpicker").selectpicker("refresh");
						$('#dynamicuserlist').html(data);
						$(".selectpicker").selectpicker("refresh");
					},
					error: function(errorThrown) {
						console.log(errorThrown);
					}
				});
			}

			function getCGsepUsersList() {
				var users = '';
				var formData = new FormData();
				formData.append('function', 'getUsersList');
				formData.append('csrfMagicToken', csrfMagicToken);

				$.ajax({
					url: "../admin/groupfunctions.php",
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					dataType: 'html',
					success: function(data) {


						var t = data;
					},
					error: function(errorThrown) {
						console.log(errorThrown);
					}
				});

			}

			function updateResult(data) {
				console.log("Users list set");
				users = data;
			}

			function setaccesslist() {

				$('#accesslist').show();
				var str = '{"Department":["HR","Finance","Marketing","IT"],"Segment":["Remote","Laptop"],"GEO Location":["India","Mumbai","US","Bangalore"],"City":["Mumbai","Delhi","Newyork","Bangalore"]}';
				var srest = $.parseJSON(str);
				var htmls = '';
				var countvalues = 0;
				$.each(srest, function(index, value) {
					countvalues = countvalues + value.length;
				});
				//console.log(users);
				console.log(countvalues);
				htmls = "<b>" + countvalues + ' unique machine groups found</b> <br/> Kindly check the group names and confirm that everything is exactly the way you need them. If you have made any mistake, Kindly make the changes and upload the corrected CSV <br/><br/>';
				$.each(srest, function(index, value) {
					$.each(value, function(i, val) {
						htmls = htmls + index + " : " + val + " <br/> ";
						var sid = index + 'cguserslist' + val;
						htmls = htmls + '<div class="form-group has-label"> <label> Select the users who will have access to this group</label> <select class="selectpicker" multiple data-style="btn btn-info" title="Select Users" data-size="3" id="' + sid + '" name="userlist-' + index + val + '"> </select> </div>';
						$('#accesslist').html(htmls);
					});


				});
				var formData = new FormData();
				formData.append('function', 'get_UsersList');
				formData.append('csrfMagicToken', csrfMagicToken);

				$.ajax({
					url: "../admin/groupfunctions.php",
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					dataType: 'html',
					success: function(data) {

						$.each(srest, function(index, value) {
							$.each(value, function(i, val) {
								//console.log(val);
								var id = '#' + index + 'cguserslist' + val;
								$(id).html(data);
								$(".selectpicker").selectpicker("refresh");
							});
						});
					},
					error: function(errorThrown) {
						console.log(errorThrown);
					}
				});
			}


			function setusersDets(id, usrs) {
				//alert(usrs);
				// alert(id);
				$(id).html(usrs);
			}


			function getCGSiteList() {
				var formData = new FormData();
				formData.append('function', 'getSiteList');
				formData.append('csrfMagicToken', csrfMagicToken);
				console.log("Loading site list");
				//$('#cgsitelist').html('<option value="SelfhealCompucom__201900015">SelfhealCompucom__201900015</option><option value="CompucomSelfhealUAT__201900013">CompucomSelfhealUAT__201900013</option><option value="SelfHealPersona__201900014">SelfHealPersona__201900014</option>');
				$.ajax({
					url: "../admin/groupfunctions.php",
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					dataType: 'json',
					success: function(data) {
						$('#cgsitelist').html(data.site);
						$('.selectpicker').selectpicker('refresh');
						$('#dynamicsitelist').html(data.site);
						$('.selectpicker').selectpicker('refresh');
					}
				});
			}

			function downloadSample() {
				var formData = new FormData();
				var sitelist = $("#cgsitelist").val();
				formData.append('sitelist', sitelist);
				formData.append('csrfMagicToken', csrfMagicToken);
				alert(sitelist);
				$.ajax({
					url: '../cgroup/generate.php',
					type: 'POST',
					data: formData,
					success: function(res) {},
					cache: false,
					contentType: false,
					processData: false
				});
			}

			function setsiteValuehidden() {
				$("#cgslist").val($("#cgsitelist").val());
				$("#dynamicsitelist").val($("#dynamicsitelist").val());
			}


			function csvcggroupcreate() {
				var file_data = $("#csv_file1").prop("files")[0];
				// var formData = new FormData();
				var formData = $("#csvsubmit").serializeArray();
				var fData = new FormData();
				var uselist = [];
				$.each(formData, function(i, val) {

					// uselist.push(val.value);
					if (val.name.includes("userlist")) {
						var uslname = val.name;
						var uslarr = [];
						$.each(formData, function(j, valu) {
							if (valu.name.includes(uslname)) {
								uslarr.push(valu.value);

							}
						});

						fData.append(uslname, uslarr.join());

					} else {
						fData.append(val.name, val.value);
					}
					// fData.append(val.name, val.value);
					// console.log(val.name+"-"+val.value);
				});
				console.log("User list" + uselist.join());
				//fData.append('userlist',uselist.join());
				fData.append('file', file_data);
				fData.append('global', '1');
				fData.append('csrfMagicToken', csrfMagicToken);
				console.log(JSON.stringify(fData));
				$("#sloader").show();
				$.ajax({
					url: '../cgroup/processcsv.php',
					type: 'POST',
					data: fData,
					success: function(data) {
						console.log(data);
						var det = $.parseJSON(data);
						if (det.status == 'success') {
							$.notify('Groups created successfully');
							/* var textstr = "Groups created successfully and machines are grouped successfully\n\n" + "Total : " + det.total + "\nInserted record : " + det.inserted + "\nFailed records : " + det.failed + "\n\n";
							 swal({
							     title: "Success",
							     text: textstr,
							     icon: "success",
							     button: "Ok",
							 });*/

						} else {

							/*var textstr = "Groups creation and machines grouping failed\n\n" + "Total : " + det.total + "\nInserted record : " + det.inserted + "\nFailed records : " + det.failed + "\n\n";
							swal({
							    title: "Error",
							    text: textstr,
							    icon: "error",
							    button: "Ok",
							});*/
							$.notify('Group creation failed');


						}
						setTimeout(function() {
							rightContainerSlideClose('grp-addmod-container');
							get_advncdgroupData();
							// location.href = 'device.php';
						}, 2500);


						$("#sloader").hide();
					},
					cache: false,
					contentType: false,
					processData: false

				});




			}

			function manualcggroupcreate1() {

				var gname = $('#manualgname').val();

				var global = 1;
				var style = $("#groupcgType").val();
				var userList = $('#groupcgUsers').val();

				var machCnt = filterId.length;
				var machinelist = filterId.toString();

				if (gname == '') {
					$.notify('Please enter the name of the group ');
					return false;
				} else if (!(/^[a-zA-Z0-9- ]*$/.test(gname))) {
					$.notify('Special characters are not allowed');
					return false;
				} else if (machinelist == '') {
					$.notify('Please select some machines');
					return false;
				} else if (machCnt < 2) {
					$.notify('You must to selected atleast 2 devices to create a group');
					return false;
				} else {
					var m_data = new FormData();
					m_data.append('groupname', gname);
					m_data.append('machinelist', machinelist);
					m_data.append('global', global);
					m_data.append('userlist', userList);
					m_data.append('style', style);

					m_data.append('function', 'get_ManualGroup_Add');
					m_data.append('csrfMagicToken', csrfMagicToken);

					for (var pair of m_data.entries()) {
						console.log(pair[0] + ', ' + pair[1]);
					}

					$("#loadingMaualAdd").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
					$.ajax({
						url: '../cgroup/processmanual.php',
						type: 'POST',
						processData: false,
						contentType: false,
						data: m_data,
						dataType: 'json',
						success: function(data) {

							if (data.status == 'Failed') {
								$('#loadingMaualAdd').hide();
								$.notify('There is already a group with the same name. Please enter another name');
								return false;

							} else if (data.status == 'success') {
								$('#loadingMaualAdd').hide();
								$.notify('Group created successfully');
								//return false;
							}

							setTimeout(function() {
								rightContainerSlideClose('grp-addmod-container');
								rightContainerSlideClose('grp-add-container');
								// get_advncdgroupData();
								get_advncdgroupData();
								// get_viewadvncdgroups();
								// location.href = 'device.php';
							}, 3200);
						}
					});
				}
			}

			function createDynamicAssetGrp(action = '') {
				if (action == 'edit') {
					var gname = $('#edit-advgname').val();
					var sitelist = $('#Edynamicsitelist').val();
					var userlist = $('#edit-groupUsers2').val();
					var asstid = $('#editassetSubFilter').val();
					var searchString = $('#editassetSubFilter').val();
					var searchtype = $('#editassetOperator').val();
					var searchval = $('#editassetVal').val();
				} else {
					var gname = $('#advgname').val();
					var sitelist = $('#dynamicsitelist').val();
					var userlist = $('#dynamicuserlist').val();
					var asstid = $('#assetSubFilter').val();
					var searchString = $('#assetSubFilter').val();
					var searchtype = $('#assetOperator').val();
					var searchval = $('#assetVal').val();
				}


				if (gname == '') {
					$.notify("Please enter the name of the group ");
					return false;
				}

				if (!validate_AlphaNumeric(gname)) {
					$.notify("Special characters not allowed in the name of the group");
					$('#successmsgadv').show();
					return false;
				}

				if (userlist == '') {
					$.notify("Please select the Users");
					return false;
				}

				if (asstid == '') {
					$.notify("Please choose the asset query");
					evntid = '';
					return false;
				}

				if (searchtype == '') {
					$.notify("Please choose asset operator");
					return false;
				}

				if (searchval == '') {
					$.notify("Please choose asset value");
					return false;
				}

				if (sitelist == '') {
					$.notify("Please select site");
					return false;
				}

				var formData = new FormData();
				formData.append('function', 'createAdvanceGrp');
				formData.append('gname', gname);
				formData.append('userlist', userlist);
				formData.append('str', searchString);
				formData.append('condition', searchtype);
				formData.append('strval', searchval);
				formData.append('site', sitelist);
				formData.append('csrfMagicToken', csrfMagicToken);
				formData.append('action', action);

				$.ajax({
					url: '../admin/groupfunctions.php',
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					dataType: 'json',
					success: function(data) {
						if (data.status === 'success') {
							if (action == 'edit') {
								$.notify("Advance Group has been successfully updated");
								setTimeout(function() {
									rightContainerSlideClose('grp-addmod-container');
									get_advncdgroupData();
								}, 2000);
							} else {
								$.notify("Advance Group has been successfully added");
								setTimeout(function() {
									rightContainerSlideClose('grp-addmod-container');
									get_advncdgroupData();
								}, 2000);
							}
						} else if (data.status === 'nomachine') {
							$.notify("No machine has been updated");
							setTimeout(function() {
								rightContainerSlideClose('grp-addmod-container');
								get_advncdgroupData();
							}, 2000);
						} else if (data.status === 'error') {
							$.notify("Group name already exists");
						}
					}
				});
			}

			function createDynamicCensusGrp(action = '') {
				if (action == 'edit') {
					var gname = $('#edit-advgname').val();
					var sitelist = $('#Edynamicsitelist').val();
					var userlist = $('#edit-groupUsers2').val();
					var searchString = $('#editcensusVal').val();
				} else {
					var gname = $('#advgname').val();
					var sitelist = $('#dynamicsitelist').val();
					var userlist = $('#dynamicuserlist').val();
					var searchString = $('#censusVal').val();
				}


				if (gname == '') {
					$.notify("Please enter the name of the group ");
					return false;
				}

				if (!validate_AlphaNumeric(gname)) {
					$.notify("Special characters not allowed in the name of the group");
					$('#successmsgadv').show();
					return false;
				}

				if (userlist == '') {
					$.notify("Please select the Users");
					return false;
				}

				if (searchString == '') {
					$.notify("Please choose the asset query");
					evntid = '';
					return false;
				}

				if (sitelist == '') {
					$.notify("Please select site");
					return false;
				}

				var formData = new FormData();
				formData.append('function', 'createAdvanceGrpCensus');
				formData.append('gname', gname);
				formData.append('userlist', userlist);
				formData.append('str', searchString);
				formData.append('site', sitelist);
				formData.append('csrfMagicToken', csrfMagicToken);
				formData.append('action', action);

				$.ajax({
					url: '../admin/groupfunctions.php',
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					dataType: 'json',
					success: function(data) {
						if (data.status === 'success') {
							if (action == 'edit') {
								$.notify("Advance Group has been successfully updated");
								setTimeout(function() {
									rightContainerSlideClose('grp-addmod-container');
									get_advncdgroupData();
								}, 2000);
							} else {
								$.notify("Advance Group has been successfully added");
								setTimeout(function() {
									rightContainerSlideClose('grp-addmod-container');
									get_advncdgroupData();
								}, 2000);
							}
						} else if (data.status === 'nomachine') {
							$.notify("No machine has been updated");
							setTimeout(function() {
								rightContainerSlideClose('grp-addmod-container');
								get_advncdgroupData();
							}, 2000);
						} else if (data.status === 'error') {
							$.notify("Group name already exists");
						}
					}
				});
			}

			function dynamicgroupcreate1() {

				var levelVal = $('#selectedDynamicFilter').val();
				if (levelVal == 'Asset') {
					createDynamicAssetGrp();
				} else {
					createDynamicCensusGrp();
				}


			}

			function manualgroupedit() {
				var grpid = $('#hiddengrpid').val();
				var userList = $('#editGroupUsers').val();
				global = 1;
				var dat = {
					'function': 'get_ManualGroup_Edit',
					'groupid': grpid,
					"csrfMagicToken": csrfMagicToken,
					'userList': userList,
					'global': '1'
				}
				console.log(dat);
				$("#loadingMaualEdit").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
				$.ajax({
					url: '../admin/groupfunctions.php',
					type: 'post',
					data: dat,
					dataType: 'json',
					success: function(data) {

						console.log(data);
						if (data.msg == 'success') {
							$('#loadingMaualEdit').hide();
							$.notify('Group Updated Successfully');
							//                    $('#manualsuccessmsgedit').fadeOut(3000);
						}
						setTimeout(function() {
							rightContainerSlideClose('edit-group');
							Group_datatablelist();
						}, 2000);
					}
				});
			}








			function csvgroupedit() {


				var grpid = $('#selected').val();
				var grpedit = $('#editcsvgname').val();
				var csvqedit = $('input[name=csvedit]')[0].files[0];
				var globaledit = '0';
				if ($("#editglobal").is(":checked")) {
					globaledit = "1";
				}
				alert(csvqedit);
				if (csvqedit == 'undefined' || csvqedit == undefined) {
					manualgroupedit();
					return;
				} else {
					var filecsv = csvqedit.name;
					var fileext = filecsv.substring(filecsv.lastIndexOf('.') + 1);
				}

				if (grpedit == '') {
					$('#successmsgedit').html('<span style="color:red">please enter group name</span>');
					$('#successmsgedit').show();

				} else if (!validate_AlphaNumeric(grpedit)) {

					$('#successmsgedit').html('<span style="color:red">Special charecters not allowed in Group name</span>');
					$('#successmsgedit').show();

				} else if (fileext != 'csv') {
					$('#successmsgedit').html('<span style="color:red">Please upload .CSV file</span>');
					$('#successmsgedit').show();
				} else {
					var m_data = new FormData();
					//        m_data.append('csvname', $('input[name=csv]')[0].files[0]);
					m_data.append('groupname', grpedit);
					m_data.append('grpid', grpid);
					m_data.append('csvname', csvqedit);
					$("#loadingCSVEdit").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
					m_data.append('global', globaledit);
					m_data.append('csrfMagicToken', csrfMagicToken);

					$.ajax({
						url: 'groupfunctions.php?function=checkEditAccess',
						type: 'post',
						data: 'groupid=' + grpid + "&csrfMagicToken=" + csrfMagicToken,
						dataType: 'json',
						success: function(data) {
							if (data.msg === 'success') {
								$.ajax({
									type: 'post',
									url: 'groupfunctions.php?function=get_editgroupcsv',
									data: m_data,
									processData: false,
									contentType: false,
									dataType: 'json',
									success: function(response) {
										if (response.status == 'success') {
											$('#loadingCSVEdit').hide();
											$('#successmsgedit').show();
											$('#successmsgedit').html('<span style="color:green">' + response.msg + '</span>');
											//                    $('#successmsgedit').fadeOut(3000);
											setTimeout(function() {
												$("#editGroup").modal("hide");
												location.href = 'groups.php';
											}, 3200);

										} else if (response.status == 'duplicate') {
											$('#loadingCSVEdit').hide();
											$('#successmsgedit').html('<span style="color:red">Group Name already exists</span>');
											$('#successmsgedit').show();
										}

										if (response.error == 'Invalid') {
											$('#loadingCSVEdit').hide();
											$('#successmsgedit').show();
											$('#successmsgedit').html('<span style="color:red">0 Machine Updated.</span>');
											//                    $('#successmsgedit').fadeOut(3000);
											setTimeout(function() {
												$("#editGroup").modal("hide");
												location.href = 'groups.php';
											}, 3200);
										}

										if (response.status == 'Failed') {
											$('#loadingCSVEdit').hide();
											$('#successmsgedit').show();
											$('#successmsgedit').html('<span style="color:red">Group Name already exists</span>');
											//                    $('#successmsgedit').fadeOut(3000);
											setTimeout(function() {
												$("#editGroup").modal("hide");
												location.href = 'groups.php';
											}, 3200);
										}
									}
								});
							} else {
								$('#loadingCSVEdit').hide();
								$('#successmsgedit').show();
								$('#successmsgedit').html('<span style="color:red">You do not have access to edit this group</span>')
								setTimeout(function() {
									$("#editGroup").modal("hide");
									location.href = 'groups.php';
								}, 2000);
							}
						}
					});
				}
			}

      function validate_Alphanumeric_special_element(value) {
        const regExp = /^[a-zA-Z0-9#/\s-_,]+$/;
        return !!value.match(regExp);
      }

			function csvgroupeditinside() {
				/*if (!($('#editcsvradio').is(":checked")) && !($('#editmanualradio').is(":checked"))) {
				    $.notify('<span style="color:red;">Please choose if you would like to create a CSV or a Manual Group</span>');
				    $('#successmsgedit').show();
				    return false;
				}*/

				var grpid = $('#hiddengrpid').val();
				var grpedit = $('#editcsvgname').val();
				var csvqedit = $('input[name=csvedit]')[0].files[0];
				var globaledit = 1;
				//alert(csvqedit);
				if (grpedit == '') {
					$.notify('Please enter the name of the group ');
					return false;
				}
				if (csvqedit == undefined) {
					$.notify('Please upload the .CSV file');
					return false;
				}

				if (!validate_Alphanumeric_special_element(grpedit)) {
					$.notify('Special characters not allowed in the name of the group');
					$('#successmsgedit').show();
					return false;
				}

				if (csvqedit != undefined) {
					var filecsv = csvqedit.name;
					var fileext = filecsv.substring(filecsv.lastIndexOf('.') + 1);

					if (fileext != 'csv') {
						$.notify('Please upload the .CSV file');
						$('#successmsgedit').show();
						return false;
					}
				}

				var userList = $('#editGroupUsers').val();
				var styleList = $('#editGroupStyle').val();

				var m_data = new FormData();
				m_data.append('groupname', grpedit);
				m_data.append('grpid', grpid);
				m_data.append('global', globaledit);
				m_data.append('userlist', userList);
				m_data.append('stylelist', styleList);

				if (csvqedit != undefined) {
					m_data.append('csvname', csvqedit);
				}
				m_data.append('function', 'get_editgroupcsv');
				m_data.append('csrfMagicToken', csrfMagicToken);
				$("#loadingCSVEdit").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');

				$.ajax({
					url: '../admin/groupfunctions.php',
					type: 'POST',
					data: {
						'function': 'check_GroupEditAccess',
						'groupid': grpid,
						'csrfMagicToken': csrfMagicToken
					},
					dataType: 'json',
					success: function(data) {
						if (data.msg === 'success') {
							$.ajax({
								type: 'POST',
								url: '../cgroup/processgroupedit.php',
								data: m_data,
								processData: false,
								contentType: false,
								dataType: 'json',
								success: function(response) {
									if (response.status == 'success') {
										$('#loadingCSVEdit').hide();
										$('#successmsgedit').show();
										$.notify("Group updated successfully.<br/>" + response.msg);
										$('#successmsgedit').fadeOut(3000);
										setTimeout(function() {
											$("#edit-group").hide();
                      rightContainerSlideClose('grp-addmod-container');
                      get_advncdgroupData();
										}, 1000);

									} else if (response.status == 'duplicate') {
										$('#loadingCSVEdit').hide();
										$.notify('There is already a group with the same name. Please enter another name');
										$('#successmsgedit').show();
									}

									if (response.error == 'Invalid') {
										$('#loadingCSVEdit').hide();
										$('#successmsgedit').show();
										$.notify('No machines are added to the Group. Please try again ');
										//                    $('#successmsgedit').fadeOut(3000);
										setTimeout(function() {
											$("#editGroup").modal("hide");
											get_advncdgroupData();
										}, 1000);
									}
									if (response.status == 'Invalid') {
										$('#loadingCSVEdit').hide();
										$('#successmsgedit').show();
										$.notify(response.msg);
										//                    $('#successmsgedit').fadeOut(3000);
										setTimeout(function() {
											$("#editGroup").modal("hide");
											get_advncdgroupData();
										}, 1000);
									}

									if (response.status == 'Failed') {
										$('#loadingCSVEdit').hide();
										$('#successmsgedit').show();
										$.notify('There is already a group with the same name. Please enter another name');
										//                    $('#successmsgedit').fadeOut(3000);
										setTimeout(function() {
											$("#editGroup").modal("hide");
											get_advncdgroupData();
										}, 1000);
									}

									if (response.error == 'nodata') {
										$('#loadingCSVEdit').hide();
										$('#successmsgedit').show();
										$.notify('Unable to update the group since the CSV file that you uploaded is empty. Please try this again');
										setTimeout(function() {
											$("#editGroup").modal("hide");
											get_advncdgroupData();
										}, 1000);
									}

									if (response.error == 'no-minimum-machines') {
										$('#loadingCSVEdit').hide();
										$('#successmsgedit').show();
										$.notify('The CSV must have at least 2 machine names to update the group.');
										return false;
									}
								}
							});
						} else {
							$('#loadingCSVEdit').hide();
							$('#successmsgedit').show();
							$.notify("You don't have the permission to edit this group")
							setTimeout(function() {
								$("#editGroup").modal("hide");
								get_advncdgroupData();
							}, 1000);
						}
					}
				});

			}


			function get_FilterList(type = '', dataid = '') {
				var formData = new FormData();
				formData.append('function', 'get_FilterListL1');
				formData.append('csrfMagicToken', csrfMagicToken);
				formData.append('type', type);
				formData.append('dataid', dataid);
				$.ajax({
					url: "../admin/groupfunctions.php",
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					dataType: 'json',
					success: function(data) {
						if (type == 'edit') {
							$('#selectedAssetData').val(data.selectedDataName);
							$('#editassetFilter').html(data.asset);
							$('.selectpicker').selectpicker('refresh');
						} else {
							$('#selectedAssetData').val(data.selectedDataName);
							$('#assetFilter').html(data.asset);
							$('.selectpicker').selectpicker('refresh');
						}
					}
				});
			}

			function showOtherValues(value, type = '', dataStr = '', operator = '', dataid = '') {
				if (type == 'edit') {
					var SelectedValue = $('#selectedAssetData').val();
				} else {
					var SelectedValue = $('#assetFilter').val();
				}

				$('#assetSubFilterDiv').show();
				$('#editassetSubFilterDiv').show();
				var formData = new FormData();
				formData.append('function', 'get_FilterListL2');
				formData.append('csrfMagicToken', csrfMagicToken);
				formData.append('SelectedValue', SelectedValue);
				formData.append('dataStr', dataStr);
				formData.append('operator', operator);
				formData.append('dataid', dataid);
				$.ajax({
					url: "../admin/groupfunctions.php",
					type: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					dataType: 'json',
					success: function(data) {
						if (type == 'edit') {
							$('#editassetSubFilter').html(data.asset);
							$('.selectpicker').selectpicker('refresh');

							$('#editassetOperator').html(data.filter);
							$('.selectpicker').selectpicker('refresh');
						} else {
							$('#assetSubFilter').html(data.asset);
							$('.selectpicker').selectpicker('refresh');

							$('#assetOperator').html(data.filter);
							$('.selectpicker').selectpicker('refresh');
						}
					}
				});
			}
		</script>

		<style>
			.dataTables_filter {
				display: none;
			}

			.bullDropdown {
				padding-top: 10px;
			}

			.rightSidenav {
				height: calc(100vh - 13px);
			}
		</style>
