<?php
    include_once $_SERVER["DOCUMENT_ROOT"] . "/Dashboard/config.php";
    include_once $absDocRoot . 'vendors/csrf-magic.php';csrf_check_custom();
    ?> <div class="content white-content">
	    <div class="row mt-2">
	        <div class="col-md-12 pl-0 pr-0">
	            <div class="card">
	                <div class="card-body">
                        <!-- loader -->
                        <!-- <div id="loader" class="loader"  data-qa="loader" style="position: absolute;bottom: 50%;right:50%;">
                           <img src="../assets/img/nanohealLoader.gif" style="width: 71px;">
                        </div> -->
	                    <div class="toolbar">
	                        <!--        Here you can write extra buttons/actions for the toolbar              -->
	                        <!-- <div class="bullDropdown leftDropdown">
	                            <div class="dropdown">
	                                <h5>Selection: <span class="site" title=""></span> (<span class="red rightslide-container-hand" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-bs-target="rsc-add-container3">Change?</span>)</h5>
	                            </div>
	                        </div> -->
<!--
                        <div class="bullDropdown">
                            <div class="dropdown">
                                <button type="button" class="btn btn-round btn-info dropdown-toggle" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="tim-icons icon-bullet-list-67"></i>
                                </button>

                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    <?php if($_SESSION['user']['licenseuser'] == 1) { ?>
                                        <a class="dropdown-item rightslide-container-hand dropHandy <?php echo setRoleForAnchorTag('addsite', 2); ?>" data-target="site-add-container" onclick="addSitePopup();">Add New Site</a>
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
                    <table class="" width="100%" data-page-length="25">
                        <thead>
                            <tr>
                                <th id="key0" headers="machine" class="sortArrow">
                                    Type
                                    <i class="fa fa-caret-down cursorPointer direction" id = "machine1" onclick = "get_deviceDetails(1,notifSearch='','machine', 'asc');sortingIconColor('machine1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "machine1" onclick = "get_deviceDetails(1,notifSearch='','machine', 'desc');sortingIconColor('machine1')" style="font-size:18px"></i>
                                </th>
                                <th id="key1" headers="host" class="sortArrow">
                                   Category
                                    <i class="fa fa-caret-down cursorPointer direction" id = "host1" onclick = "get_deviceDetails(1,notifSearch='','host', 'asc');sortingIconColor('host1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "host2" onclick = "get_deviceDetails(1,notifSearch='','host', 'desc');sortingIconColor('host2')" style="font-size:18px"></i>
                                </th>
								<th id="key2" headers="os" class="sortArrow">
                                    Sub-Category
                                    <i class="fa fa-caret-down cursorPointer direction" id = "os1" onclick = "get_deviceDetails(1,notifSearch='','os', 'asc');sortingIconColor('os1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "os2" onclick = "get_deviceDetails(1,notifSearch='','os', 'desc');sortingIconColor('os2')" style="font-size:18px"></i>
                                </th>
                                <th id="key3" headers="born" class="sortArrow">
                                    Description
                                    <i class="fa fa-caret-down cursorPointer direction" id = "born1" onclick = "get_deviceDetails(1,notifSearch='','born', 'asc');sortingIconColor('born1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "born2" onclick = "get_deviceDetails(1,notifSearch='','born', 'desc');sortingIconColor('born1')" style="font-size:18px"></i>
                                </th>
                                <th id="key4" headers="last" class="sortArrow">
                                    Tracking Attribute
                                    <i class="fa fa-caret-down cursorPointer direction" id = "last1" onclick = "get_deviceDetails(1,notifSearch='','last', 'asc');sortingIconColor('last1')" style="font-size:18px"></i>
                                    <i class="fa fa-caret-up cursorPointer direction" id = "last2" onclick = "get_deviceDetails(1,notifSearch='','last', 'desc');sortingIconColor('last2')" style="font-size:18px"></i>
                                </th>
                                <!--<th style=" visibility: hidden;">Action</th>-->
                            </tr>
                        </thead>
                    </table>

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
<div id="addNew" class="rightSidenav" data-class="sm-3" style = "">
    <div class="card-title border-bottom">
        <h4>Add</h4>
        <a href="javascript:void(0)" class="closebtn rightslide-container-close border-0" data-target="addNew" oncLick = "hideAddnew()">&times;</a>
    </div>
    <div class="btnGroup">
        <div class="icon-circle create_site_div">
            <div class="toolTip" id="">
                <i class="tim-icons icon-check-2"></i>
                <span class="tooltiptext">Save</span>
            </div>
        </div>
    </div>
    <div class="form table-responsive white-content" style="background: #fff;">
        <div class="card">
            <div class="card-body">
                <div class="form-group has-label">
                    <label for="deploy_sitename">Type</label><em class="error" id="err_sitename"></em>
                    <select class="selectpicker" data-style="btn btn-info" title="Select SKU" data-size="3" id="deploy_skuid" name="deploy_skuid">
                        <option value="0">-- Please select a SKU --</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label>Category</label>
                    <select class="selectpicker" data-style="btn btn-info" title="Select SKU" data-size="3" id="deploy_skuid" name="deploy_skuid">
                        <option value="0">-- Please select a SKU --</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label for="deploy_delay">Sub-Category</label><em class="error" id="err_delay"></em>
                    <select class="selectpicker" data-style="btn btn-info" title="Select SKU" data-size="3" id="deploy_skuid" name="deploy_skuid">
                        <option value="0">-- Please select a SKU --</option>
                    </select>
                </div>

                <div class="form-group has-label">
                    <label for="deploy_delay">Description</label><em class="error" id="err_delay"></em>
                    <br />
                    <textarea id="" name="" rows="4" class = "w-100" style = "border-color: rgba(29, 37, 59, 0.2);"></textarea>
                </div>

                <div class="form-group has-label">
                    <label for="deploy_delay">Tracking Attributes</label><em class="error" id="err_delay"></em>
                    <select class="selectpicker" data-style="btn btn-info" title="Select SKU" data-size="3" id="deploy_skuid" name="deploy_skuid">
                        <option value="0">-- Please select a SKU --</option>
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
                                <input type = "text" />
                            </td>
                            <td>
                                <input type = "text" />
                            </td>
                            <td>
                                <input type = "text" />
                            </td>
                            <td>
                                <input type = "text" />
                            </td>
                            <td>
                                <input type = "text" />
                            </td>
                            <td>
                                <input type = "text" />
                            </td>
                            <td>
                                <input type = "text" />
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="absoFeeds" class = "hideAbsoFeed" style="width: 100%;"></div>
<!-- Add new site UI ends -->

<style>
.dropdown-menu.inner li.hidden{
    display:none;
}

.dropdown-menu.inner.active li, .dropdown-menu.inner li.active{
    display:block;
}
</style>
 <script src="../assets/js/core/jquery.min.js"></script>
    <script>
        var users = '';
        $(document).ready(function() {
            // $('#addNew').modal('show');
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
                     if(val.name.includes("userlist")){
				   var uslname = val.name;
				   var uslarr = [];
				   $.each(formData, function(j, valu) {
					   if(valu.name.includes(uslname)){
						   uslarr.push(valu.value);

					   }
				   });

                   fData.append(uslname,uslarr.join());

                }else{
                       fData.append(val.name, val.value);
                }
               // fData.append(val.name, val.value);
               // console.log(val.name+"-"+val.value);
            });
            console.log("User list"+uselist.join());
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
            m_data.append('function','get_edit_groupcsv');  m_data.append('csrfMagicToken', csrfMagicToken);

            $("#loadingCSVEdit").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');

           $.ajax({
                url: '../admin/groupfunctions.php',
                type: 'POST',
                data: {
                    'function': 'check_GroupEditAccess',                    'groupid': grpid,
                    csrfMagicToken:csrfMagicToken


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

        function showAddnew() {
            // rightContainerSlideOn('addNew');
            $("#addNew").addClass('addNewWidth');
            $("#absoFeeds").addClass('showAbsoFeed');
            $("#absoFeeds").removeClass('hideAbsoFeed');
        }

        function hideAddnew() {
            // rightContainerSlideOn('addNew');
            $("#addNew").removeClass('addNewWidth');
            $("#absoFeeds").removeClass('showAbsoFeed');
            $("#absoFeeds").addClass('hideAbsoFeed');
        }
</script>



<style>
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

    .form-group {
        font-weight: 600;
    }
</style>
