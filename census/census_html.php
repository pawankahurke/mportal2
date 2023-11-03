	<?php
	include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
	include_once $absDocRoot . 'vendors/csrf-magic.php';
	csrf_check_custom();
	?> <div class="content white-content">
		<div class="row mt-2">
			<div class="col-md-12 pl-0 pr-0">
				<div class="card">
					<div class="card-body">
						<!-- loader -->
						<div id="loader" class="loader" data-qa="loader" style="position: absolute;bottom: 50%;right:50%;">
							<img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
						</div>
						<div class="toolbar">
							<?php
							//   nhRole::checkRoleForPage('census');
							$res = true; //nhRole::checkModulePrivilege('census');
							if ($res) {
							?>
								<!--        Here you can write extra buttons/actions for the toolbar              -->
								<!-- <div class="bullDropdown leftDropdown">
	                            <div class="dropdown">
	                                <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>
	                            </div>
	                        </div> -->
								<!--
                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <?php if ($_SESSION['user']['licenseuser'] == 1) { ?>
                                        <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addsite', 2); ?>" data-bs-target="site-add-container" onclick="addSitePopup();">Add New Site</a>
                                    <?php } ?>
                                    <a class="dropdown-item rightslide-container-hand dropHandy sites <?php echo setRoleForAnchorTag('siteexport', 2); ?>" id="exportAllSites">Export</a>
                                </div>
                            </div>
                        </div> -->
						</div>
						<!-- <div id="notifyDtl_filter" class="dataTables_filter">
                                <label class="float-lg-right mr-2">
                                    <input type="text" class="form-control form-control-sm" placeholder="Search records" value="" id="notifSearch" aria-controls="notifyDtl"/>
                                    <button class="bg-white border-0 mr-1 showbtn cursorPointer" onclick="getSearchRecords()"><i class="tim-icons serachIcon icon-zoom-split"></i></button>
                                    <button style="display:none" class="bg-white border-0 mr-1 clearbtn cursorPointer" onclick="clearRecords()" onclick="document.getElementById('notifSearch').value = ''"><i class="tim-icons serachIcon icon-simple-remove"></i></button>
                                </label>
                            </div> -->
						<input type="hidden" id="selected">
						<input type="hidden" id="grupnamehidden">
						<input type="hidden" id="totalmachinecount">
						<input type="hidden" id="hiddengrpid">
						<table data-qa="tableCensus" class="nhl-datatable table table-striped" width="100%" data-page-length="25" id="detaild_grid">
							<thead>
								<tr>
									<?php if ($_SESSION['searchType'] == 'Groups') { ?>
										<th id="key0" headers="machine" class="sortArrow">
											Site Name
											<i class="fa fa-caret-down cursorPointer direction" id="machine1" onclick="addActiveSort('asc', 'machine'); get_deviceDetails(1,notifSearch='','machine', 'asc');sortingIconColor('machine1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="machine1" onclick="addActiveSort('desc', 'machine'); get_deviceDetails(1,notifSearch='','machine', 'desc');sortingIconColor('machine1')" style="font-size:18px"></i>
										</th>
										<th id="key1" headers="host" class="sortArrow">
											Machine Name
											<i class="fa fa-caret-down cursorPointer direction" id="host1" onclick="addActiveSort('asc', 'host'); get_deviceDetails(1,notifSearch='','host', 'asc');sortingIconColor('host1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="host2" onclick="addActiveSort('desc', 'host'); get_deviceDetails(1,notifSearch='','host', 'desc');sortingIconColor('host2')" style="font-size:18px"></i>
										</th>
										<th id="key2" headers="os" class="sortArrow">
											Machine OS
											<i class="fa fa-caret-down cursorPointer direction" id="os1" onclick="addActiveSort('asc', 'os'); get_deviceDetails(1,notifSearch='','os', 'asc');sortingIconColor('os1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="os2" onclick="addActiveSort('desc', 'os'); get_deviceDetails(1,notifSearch='','os', 'desc');sortingIconColor('os2')" style="font-size:18px"></i>
										</th>
										<th id="key3" headers="born" class="sortArrow">
											Date Added
											<i class="fa fa-caret-down cursorPointer direction" id="born1" onclick="addActiveSort('asc', 'born'); get_deviceDetails(1,notifSearch='','born', 'asc');sortingIconColor('born1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="born2" onclick="addActiveSort('desc', 'born'); get_deviceDetails(1,notifSearch='','born', 'desc');sortingIconColor('born1')" style="font-size:18px"></i>
										</th>
										<th id="key4" headers="last" class="sortArrow">
											Last Event
											<i class="fa fa-caret-down cursorPointer direction" id="last1" onclick="addActiveSort('asc', 'last'); get_deviceDetails(1,notifSearch='','last', 'asc');sortingIconColor('last1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="last2" onclick="addActiveSort('desc', 'last'); get_deviceDetails(1,notifSearch='','last', 'desc');sortingIconColor('last2')" style="font-size:18px"></i>
										</th>
										<th id="key5" headers="clientversion" class="sortArrow" title="Client Version">
											Version
											<i class="fa fa-caret-down cursorPointer direction" id="clientversion1" onclick="addActiveSort('asc', 'clientversion'); get_deviceDetails(1,notifSearch='','clientversion', 'asc');sortingIconColor('clientversion1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="clientversion2" onclick="addActiveSort('desc', 'clientversion'); get_deviceDetails(1,notifSearch='','clientversion', 'desc');sortingIconColor('clientversion2')" style="font-size:18px"></i>
										</th>
										<!--<th style=" visibility: hidden;">Action</th>-->
									<?php } ?>
									<?php if ($_SESSION['searchType'] == 'Sites') { ?>
										<th id="key0" headers="host" class="">
											Machine Name
											<i class="fa fa-caret-down cursorPointer direction" id="host1" onclick="addActiveSort('asc', 'host'); get_deviceDetails(1,notifSearch='','host', 'asc');sortingIconColor('host1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="host2" onclick="addActiveSort('desc', 'host'); get_deviceDetails(1,notifSearch='','host', 'desc');sortingIconColor('host2')" style="font-size:18px"></i>
										</th>
										<th id="key1" headers="os" class="">
											Machine OS
											<i class="fa fa-caret-down cursorPointer direction" id="os1" onclick="addActiveSort('asc', 'os'); get_deviceDetails(1,notifSearch='','os', 'asc');sortingIconColor('os1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="os2" onclick="addActiveSort('desc', 'os'); get_deviceDetails(1,notifSearch='','os', 'desc');sortingIconColor('os2')" style="font-size:18px"></i>
										</th>
										<th id="key2" headers="born" class="">
											Date Added
											<i class="fa fa-caret-down cursorPointer direction" id="born1" onclick="addActiveSort('asc', 'born'); get_deviceDetails(1,notifSearch='','born', 'asc');sortingIconColor('born1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="born2" onclick="addActiveSort('desc', 'born'); get_deviceDetails(1,notifSearch='','born', 'desc');sortingIconColor('born2')" style="font-size:18px"></i>
										</th>
										<th id="key3" headers="last" class="">
											Last Event
											<i class="fa fa-caret-down cursorPointer direction" id="last1" onclick="addActiveSort('asc', 'last'); get_deviceDetails(1,notifSearch='','last', 'asc');sortingIconColor('last1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="last2" onclick="addActiveSort('desc', 'last'); get_deviceDetails(1,notifSearch='','last', 'desc');sortingIconColor('last2')" style="font-size:18px"></i>
										</th>
										<th id="key4" headers="clientversion" class="" title="Client Version">
											Version
											<i class="fa fa-caret-down cursorPointer direction" id="clientversion1" onclick="addActiveSort('asc', 'clientversion'); get_deviceDetails(1,notifSearch='','clientversion', 'asc');sortingIconColor('clientversion1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="clientversion2" onclick="addActiveSort('desc', 'clientversion'); get_deviceDetails(1,notifSearch='','clientversion', 'desc');sortingIconColor('clientversion2')" style="font-size:18px"></i>
										</th>
										<th id="key5" headers="action" class="">
											Action
											<i class="fa fa-caret-down cursorPointer direction" id="action1" onclick="addActiveSort('asc', 'action'); get_deviceDetails(1,notifSearch='','action', 'asc');sortingIconColor('action1')" style="font-size:18px"></i>
											<i class="fa fa-caret-up cursorPointer direction" id="action2" onclick="addActiveSort('desc', 'action'); get_deviceDetails(1,notifSearch='','action', 'desc');sortingIconColor('action2')" style="font-size:18px"></i>
										</th>
									<?php } ?>
								</tr>
							</thead>
						</table>
					<?php
							}
					?>
					<div id="errorMsg" style="margin-top: 7%; margin-left: 36%;">
						<!-- <img src="../assets/img/click.svg" alt="" style=""><br/> -->
						<span style="font-size: 13px; margin-left: 30px;">Please select site or group to view list</span>
					</div>
					<!--<div class="col-md-12" id="errorMsg" style="display:none;">
                        <span>Please select site or group to view list</span>
                    </div>-->
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
	<div id="site-add-container" class="rightSidenav" data-class="sm-3">
		<div class="card-title border-bottom">
			<h4>Add Site</h4>
			<a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="site-add-container">&times;</a>
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
				<div class="card-body">
					<div class="form-group has-label">
						<label for="deploy_sitename">Site name</label><em class="error" id="err_sitename"></em>
						<input type="text" name="deploy_sitename" data-qa="deploy_sitename" id="deploy_sitename" class="form-control">
					</div>

					<div class="form-group has-label">
						<label>Select SKU</label>
						<select class="selectpicker" data-style="btn btn-info" title="Select SKU" data-size="3" id="deploy_skuid" name="deploy_skuid">
							<option value="0">-- Please select a SKU --</option>
						</select>
					</div>

					<input type="hidden" id="deploy_startup" value="All" />
					<input type="hidden" id="deploy_followon" value="All" />

					<div class="form-group has-label">
						<label for="deploy_delay">Delay before follow-on</label><em class="error" id="err_delay"></em>
						<input type="text" name="deploy_delay" id="deploy_delay" class="form-control" readonly="">
					</div>

					<div class="form-group has-label">
						<label class="siteCreateErr"></label>
					</div>

					<div class="button col-md-12 text-left">
						<p id="required_Sitename" style="color: red;font-size: 14px;"></p>
					</div>
				</div>
			</div>
		</div>
	</div>
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
						<button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="copy_link1" onclick="copy_url('downloadUrl')"><i class="tim-icons icon-single-copy-04"></i>Copy URL</button>
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
						<label>To get the download url for site <b><span class="site"></span></b>. Please enter the email addresses below.</label>
					</div>
					<div class="form-group">
						<label>Email Addresses</label><em>Enter one email address per line.</em>
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


	<!-- download url pop up start -->
	<div id="url-pop-container" class="rightSidenav" data-class="sm-3">
		<div class="card-title border-bottom">
			<h4>Download URL</h4>
			<a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="url-pop-container">&times;</a>
		</div>

		<div class="form table-responsive white-content" style="background: #fff;">
			<div class="card">
				<div class="card-body">
					<form action="#">
						<input type="hidden" class="form-control" id="selCustomer2" placeholder="" name="selCustomer2" value="<?php echo $_SESSION['user']['cd_eid'] ?>">
						<div class="form-group has-label">
							<label>Select Site</label>
							<select class="selectpicker" data-style="btn btn-info" title="Select Site" data-size="3" id="selSite" name="selSite" onchange="getdownloadUrl(this)">
							</select>
						</div>

						<div class="form-group has-label">
							<label>Download URL</label>
							<input class="form-control" name="site_download_url" id="site_download_url" readonly="true" type="text" />
						</div>

					</form>
					<span id="status_emailsent"></span>
					<div class="button text-left">
						<button type="button" class="swal2-confirm btn btn-success btn-sm" aria-label="" id="copy_link1" onclick="copy_url('site_download_url')" style="display:none"><i class="tim-icons icon-single-copy-04"></i> Copy</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- download url pop up end -->

	<!-- Add New Group starts -->
	<div id="grp-add-container" class="rightSidenav addGroup" data-class="md-6">
		<div class="card-title border-bottom">
			<h4>Add group</h4>
			<a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="grp-add-container">&times;</a>
		</div>
		<div class="btnGroup" style="display: block;">
			<div class="icon-circle" id="csvuploadbutton">
				<div class="toolTip" onclick="csvgroupcreate();">
					<i class="tim-icons icon-check-2"></i>
					<span class="tooltiptext">Save</span>
				</div>
			</div>

			<div class="icon-circle" id="manualmachinebutton" style="display: none;">
				<div class="toolTip" onclick="manualgroupcreate();">
					<i class="tim-icons icon-check-2"></i>
					<span class="tooltiptext">Save</span>
				</div>
			</div>
		</div>
		<div class="form table-responsive white-content">
			<div class="card">
				<div class="card-body">
					<div class="form-group has-label">
						<label for="csvgname">
							Group Name
						</label>
						<input class="form-control" type="text" id="csvgname" />
					</div>
				</div>

				<div class="card-body">
					<!--<div class="form-group has-label">
                    <label for="csvgname">
                        Would this group be Global?
                    </label>
                </div>

                <div class="form-check form-check-radio global">
                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="exampleRadios1" id="globalyes">
                        <span class="form-check-sign"></span>
                        Yes
                    </label>

                    <label class="form-check-label">
                        <input class="form-check-input" type="radio" name="exampleRadios1" id="globalno">
                        <span class="form-check-sign"></span>
                        No
                    </label>
                </div>-->
					<div class="form-group has-label">
						<label>
							Assign Group to Users
						</label>
						<select data-live-search="true" class="selectpicker" multiple data-style="btn btn-info" title="Select Group Users" data-size="3" id="groupUsers" name="groupUsers">

						</select>
						<span id="add_userLevel-err"></span>
					</div>
				</div>
				<div class="card-body">
					<div class="form-group has-label">
						<label for="csvgname">
							Add Devices to Groups
						</label>
					</div>

					<div class="form-check form-check-radio global">
						<label class="form-check-label">
							<input class="form-check-input" type="radio" id="csvradio" name="exampleRadios" value="option1">
							<span class="form-check-sign"></span>
							CSV
						</label>

						<label class="form-check-label">
							<input class="form-check-input" type="radio" name="exampleRadios" id="manualradio" value="option2">
							<span class="form-check-sign"></span>
							Manual
						</label>
					</div>
				</div>


			</div>

			<!-- CSV File Upload -->
			<div class="row">
				<div data-class="md-6" id="csvuploaddata" style="display:none;">
					<div class="fileinput fileinput-new" data-provides="fileinput">
						<div class="form-group has-label ">
							<!--<input class="form-control" type="text" id="uploadcsv" />-->
							<div class="col-md-12">
								<p>
									<b>Select the CSV file that has details of the machines that must be part of this group</b>
								</p>
							</div>
							<!--<p id="title_csv">Upload CSV</p>-->

							<div class="col-md-12">
								<p id="file_sel" class="txtsm">File selected : <span id="csv_name"></span></p>
							</div>

							<div class="col-md-12 btnBrowser">
								<span class="btn btn-round btn-rose btn-file btn-sm">
									<span class="fileinput-new">Browse</span>
									<input type="file" name="csv" id="csv_file" name="csv_file" accept=".csv" />
								</span>

								<span class="btn btn-round btn-success  btn-sm" id="remove_logo" style="display:none">
									<span class="fileinput-new">Remove</span>
								</span>
							</div>
							<div class="col-md-12">
								<p class="manual_group">
									<span class="download_csv" onclick="samplefileExport();">Click Here</span> to Download sample CSV
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!--   CSV File Upload -->

			<!-- Manual File Upload -->

			<div id="manualmachinelist" style="display:none;">
				<p class="manual_group " id="groupslider"><span class="add_group">Click here</span> to select the device manually</p>
				<div id="machine_count" style="display:none;">
					<p><span id="machinecount"></span> Devices are added to this group
					<p class="manual_group add_group"> Edit</p>
					</p>
				</div>
			</div>
		</div>
	</div>
	<!-- Add New Group  end-->
	<!-- Add New Group starts -->
	<div id="grp-addmod-container" class="rightSidenav addGroup" data-class="md-6">
		<div class="card-title border-bottom">
			<h4>Add group</h4>
			<a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-bs-target="grp-addmod-container">&times;</a>
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
					</div>
				</div>





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

				<!--<div class="form-group has-label">
                    <label>
                        Assign Group to Users
                    </label>
                    <select data-live-search="true" class="selectpicker" multiple data-style="btn btn-info" title="Select Group Users" data-size="3" id="groupcgUsers" name="groupUsers">

                    </select>
                    <span id="add_userLevel-err"></span>
                </div>-->



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

		<div class="btnGroup" id="toggleButton">
			<div class="icon-circle iconTick" id="editcsvuploadbutton">
				<!-- circleGrey -->
				<div class="toolTip" onclick="csvgroupeditinside();">
					<i class="tim-icons icon-check-2"></i>
					<span class="tooltiptext">Save edit change</span>
				</div>
			</div>
			<!-- <div class="icon-circle iconTick circleGrey" id="editmanualmachinebutton" style="display: none;">
            <div class="toolTip" onclick="manualgroupedit();">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save edit change</span>
            </div>
        </div>-->

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
						<div class="form-group has-label" id="editfocused">
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
							<p><span id="editmachinecount"></span><input type="hidden" id="machine_list">Devices are added to this group</p>
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
			var users = '';
			$(document).ready(function() {
				$('#csvradio1').change(function() {
					$('#csvuploaddata1').show();
					$('#csvuploadbutton1').show();
					$('#manualcgmachinebutton1').hide();
					$('#manualmachinebutton1').hide();
					$('#manualmachinelist1').hide();
					$('#successmsg1').html('');
					$('#successmsg1').hide();
				});
				$('#manualradio1').change(function() {
					$("#add-manual").show();
					$('#csvuploaddata1').hide();
					$('#csvuploadbutton1').hide();
					$('#manualmachinebutton1').show();
					$('#manualmachinelist1').show();
					$('#include_machine').html('');
					$('#groupslider1').show();
					$('#machine_count1').hide();
					$('#loader1').show();
					$('#loader').show();
					getStylesList();
					$.ajax({
						type: "POST",
						url: "../admin/groupfunctions.php",
						data: {
							'function': 'get_MachineList',
							'csrfMagicToken': csrfMagicToken
						},
						dataType: "json"
					}).done(function(data) {
						$('.loader').hide();
						console.log("ajax one");
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
				$("#csv_file1").on("change", function() {
					var file_data = $("#csv_file1").prop("files")[0];
					var logo_data = new FormData();
					var csv_name = $("#csv_file1").prop("files")[0]["name"];
					$('#remove_logo1').css('display', '');
					$('#accesslist').show();
					$('#accesslist').html('<br/><br/><p>Please wait while we are analysing the details of your machine group</p><p>&nbsp;</p>');

					logo_data.append("file", file_data);
					logo_data.append("type", "headerlogo");

					$("#csv_name1").html(csv_name).css({
						color: "black"
					});
					$(".logo_loader").show();
					$.ajax({
						url: '../cgroup/groupfunc.php',
						type: 'POST',
						data: logo_data,
						success: function(res) {
							$('.loader').hide();
							console.log("ajax one");
							var result = $.parseJSON(res);
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
								console.log(value.toString());
								groupsdet.push(value);
							});
							$.each(result.uniqueGroup, function(index, value) {
								countvalues = countvalues + value.length;
								console.log(value.toString());
								//groupsdet.push(value);
							});

							$('#groupsDet').val(groupsdet.toString());
							//console.log(users);
							console.log(countvalues);
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
							formData.append('function', 'getUsersList');

							$.ajax({
								url: "../admin/groupfunctions.php",
								type: 'POST',
								data: formData,
								processData: false,
								contentType: false,
								dataType: 'html',
								success: function(data) {
									$('.loader').hide();
									console.log("ajax three");
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
									$('.loader').hide();
									console.log("ajax one");
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

				$(".loader").hide();

			});

			function getrequiredList() {
				getCGUsersList();
				getCGSiteList();
				//setaccesslist();
			}

			function samplecsvfileExport() {
				//    var functioncall    = "function=get_editgroupname&editgid=" + editgid;
				//    var encryptedData   = get_RSA_EnrytptedData(functioncall);
				var selected = $('#selected').val();
				window.location.href = '../cgroup/generategroup.php?grpid=' + selected;
			}

			function getStylesList() {
				var formData = new FormData();

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
				$(".loader").hide();

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

						// alert(data);
						//users = data;
						$('#groupcgUsers').html(data);
						// $('.cguserlist').html('<option value="JohnSatya">JohnSatya</option><option value="admin">admin</option>');
						$(".selectpicker").selectpicker("refresh");
						//updateResult(data);
					},
					error: function(errorThrown) {
						console.log(errorThrown);
					}
				});
				$(".loader").hide();

			}

			function getCGsepUsersList() {
				var users = '';
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


						var t = data;
					},
					error: function(errorThrown) {
						console.log(errorThrown);
					}
				});
				$(".loader").hide();


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
				$(".loader").hide();

			}


			function setusersDets(id, usrs) {
				//alert(usrs);
				// alert(id);
				$(id).html(usrs);
			}


			function getCGSiteList() {
				var formData = new FormData();
				formData.append('function', 'getSiteList');
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
					}
				});
				$(".loader").hide();

			}

			function downloadSample() {
				var formData = new FormData();
				var sitelist = $("#cgsitelist").val();
				formData.append('sitelist', sitelist);
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
				$(".loader").hide();

			}

			function setsiteValuehidden() {
				$("#cgslist").val($("#cgsitelist").val());
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
							get_deviceDetails();
							// location.href = 'device.php';
						}, 2500);


						$(".loader").hide();
					},
					cache: false,
					contentType: false,
					processData: false

				});


				$(".loader").hide();


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
								get_deviceDetails();
								// location.href = 'device.php';
							}, 3200);
						}
					});
				}
				$(".loader").hide();

			}


			function csvgroupeditinside() {
				/*if (!($('#editcsvradio').is(":checked")) && !($('#editmanualradio').is(":checked"))) {
				    $.notify('<span style="color:red;">Please choose if you would like to create a CSV or a Manual Group</span>');
				    $('#successmsgedit').show();
				    return false;
				}*/

				var grpid = $('#selected').val();
				var grpedit = $('#editcsvgname').val();
				var csvqedit = $('input[name=csvedit]')[0].files[0];
				var globaledit = 1;
				//alert(csvqedit);
				if (grpedit == '') {
					$.notify('Please enter the name of the group ');
					return false;
				}
				/* 		if (csvqedit == undefined) {
				$.notify('Please upload the .CSV file');
                return false;
			} */

				if (!validate_alphanumeric_nounderscore(grpedit)) {
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
				m_data.append('function', 'get_edit_groupcsv');
				m_data.append('csrfMagicToken', csrfMagicToken);

				$("#loadingCSVEdit").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');

				$.ajax({
					url: '../admin/groupfunctions.php',
					type: 'POST',
					data: {
						'function': 'check_GroupEditAccess',
						'groupid': grpid,
						csrfMagicToken: csrfMagicToken


					},
					dataType: 'json',
					success: function(data) {
						if (data.msg === 'success') {
							$.ajax({
								type: 'post',
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
											$("#editGroup").modal("hide");
											get_deviceDetails();
											// location.href = 'device.php';
										}, 3200);

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
											get_deviceDetails();
											// location.href = 'device.php';
										}, 3200);
									}
									if (response.status == 'Invalid') {
										$('#loadingCSVEdit').hide();
										$('#successmsgedit').show();
										$.notify(response.msg);
										//                    $('#successmsgedit').fadeOut(3000);
										setTimeout(function() {
											$("#editGroup").modal("hide");
											get_deviceDetails();
											// location.href = 'device.php';
										}, 3200);
									}

									if (response.status == 'Failed') {
										$('#loadingCSVEdit').hide();
										$('#successmsgedit').show();
										$.notify('There is already a group with the same name. Please enter another name');
										//                    $('#successmsgedit').fadeOut(3000);
										setTimeout(function() {
											$("#editGroup").modal("hide");
											get_deviceDetails();
											// location.href = 'device.php';
										}, 3200);
									}

									if (response.error == 'nodata') {
										$('#loadingCSVEdit').hide();
										$('#successmsgedit').show();
										$.notify('Unable to update the group since the CSV file that you uploaded is empty. Please try this again');
										setTimeout(function() {
											$("#editGroup").modal("hide");
											get_deviceDetails();
											// location.href = 'device.php';
										}, 3200);
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
								get_deviceDetails();
								// location.href = 'device.php';
							}, 2000);
						}
					}

				});


			}
		</script>



		<style>
			.dataTables_scrollHeadInner {
				width: auto !important;
			}

			.showbtn {
				margin-left: 119px;
			}

			.clearbtn {
				margin-left: 119px;
			}
		</style>
