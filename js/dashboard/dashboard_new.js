$(document).ready(function () {
    
    var pageValue = $('#pageName').html();
    if(pageValue == 'Manage Dashboard'){
        $('#PreviewIframe').hide();
        $('#PreviewDashTable').show();
        $('#AddDashboard').show();
        $('#deleteDashboard').show();
        $('#backWindowbtn').hide();
        viewdatatableList();
    }else{
        $('#PreviewVizIframe').hide();
        $('#PreviewVizTable').show();
        $('#AddVisual').show();
        $('#deleteVisual').show();
        $('#backWindowbtn2').hide();
        viewVizList();
    }
    getloggedUsersStatus();
    if ($('#dashboard').is(":checked")) {
        $('.defaultCheck').show();
    }
    if ($('#dashboard2').is(":checked")) {
        $('.defaultCheck').show();
    }
})

function VisualIframeOnClose() {
    location.reload();
}

function viewdatatableList() {
    $.ajax({
        url: "../dashboard/dashboardFunctions.php",
        type: "GET",
        data: {'function' : 'getAllDashboards', 'csrfMagicToken': csrfMagicToken},
        dataType: 'json',
        success: function (gridData) {

            $("#dashboardViewList").dataTable().fnDestroy();
            dashboardTable = $('#dashboardViewList').DataTable({
                scrollY: jQuery('#dashboardViewList').data('height'),
                scrollCollapse: true,
                paging: false,
                searching: false,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                stateSave: true,
                responsive: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                    targets: "datatable-nosort",
                    orderable: false
                }],
                initComplete: function (settings, json) {
                },
                drawCallback: function (settings) {
                    $("#dashboardViewList_filter").hide();
                }
            });
            $('.tableloader').hide();

        },
        error: function (msg) {

        }
    });
    
    $('#dashboardViewList').on('click', 'tr', function () {
        var rowID = dashboardTable.row(this).data();
        dashboardTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var dId = rowID[6];
        var name = rowID[5];
        var type = rowID[3];
        $('#dashName').val(name);
        $('#dashId').val(dId);
        $('#dashType').val(type);
    });
    
    $('#dashboardViewList').on('dblclick', 'tr', function () {
        var rowID = dashboardTable.row(this).data();
        dashboardTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var dId = rowID[6];
        var name = rowID[5];
        var type = rowID[3];
        $('#dashName').val(name);
        $('#dashId').val(dId);
        $('#dashType').val(type);
        console.log(rowID[6]);
        console.log(rowID[5]);
        console.log(rowID[3]);
        if (rowID != 'undefined' && rowID !== undefined) {
           $('#selected').val(rowID[6]);
        }
        $.ajax({
            url: '../dashboard/dashboardFunctions.php',
            type:'GET',
            dataType: 'json',
            data:{'function':'getUsersStatus', 'csrfMagicToken': csrfMagicToken},
            success: function (data) {
                if(data.IsAdmin == 'true'){
                    openEditDashSlider();
                }else{
                    $.notify("You don't have the Access to Edit the Dashboard");
                    return false;
                }
            },
            error:function(error){
                console.log("error");
            }
        });  
    });
}

function viewVizList() {
    $.ajax({
        url: "../dashboard/dashboardFunctions.php",
        type: "GET",
        data: {'function' : 'getAllVizs', 'csrfMagicToken': csrfMagicToken},
        dataType: 'json',
        success: function (gridData) {

            $("#vizViewList").dataTable().fnDestroy();
            dashboardTable = $('#vizViewList').DataTable({
                scrollY: jQuery('#vizViewList').data('height'),
                scrollCollapse: true,
                paging: false,
                searching: false,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                stateSave: true,
                responsive: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                    targets: "datatable-nosort",
                    orderable: false
                }],
                initComplete: function (settings, json) {
                },
                drawCallback: function (settings) {
                    $("#vizViewList_filter").hide();
                }
            });
            $('.tableloader').hide();

        },
        error: function (msg) {

        }
    });
    
    $('#vizViewList').on('click', 'tr', function () {
        var rowID = dashboardTable.row(this).data();
        dashboardTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var dId = rowID[6];
        var name = rowID[5];
        var type = rowID[3];
        $('#VizName').val(name);
        $('#VizId').val(dId);
        $('#VizType').val(type);

    });
    
    $('#vizViewList').on('dblclick', 'tr', function () {
        var rowID = dashboardTable.row(this).data();
        dashboardTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var dId = rowID[6];
        var name = rowID[5];
        var type = rowID[3];
        $('#VizName').val(name);
        $('#VizId').val(dId);
        $('#VizType').val(type);
        if (rowID != 'undefined' && rowID !== undefined) {
           $('#selected').val(rowID[6]);
        }
        $.ajax({
            url: '../dashboard/dashboardFunctions.php',
            type:'GET',
            dataType: 'json',
            data:{'function':'getUsersStatus', 'csrfMagicToken': csrfMagicToken},
            success: function (data) {
                if(data.IsAdmin == 'true'){
                    openEditVizSlider();
                }else{
                    $.notify("You don't have the Access to Edit the Dashboard");
                    return false;
                }
            },
            error:function(error){
                console.log("error");
            }
        });  
    });
}

function openEditDashSlider(){
    getEditUsersList('dash');
    rightContainerSlideOn('rsc-editUser-dashboard');
}

function openEditVizSlider(){
    getEditUsersList('viz');
    rightContainerSlideOn('rsc-editUser-visual');
}

function getEditUsersList(dashboardType){
    
    if(dashboardType == 'dash'){
        var dashboardName = $('#dashName').val();
        var did = $('#dashId').val();
        var dashtype = $('#dashType').val();
    }else{
        var dashboardName = $('#VizName').val();
        var did = $('#VizId').val();
        var dashtype = $('#VizType').val();
    }
    
    $.ajax({
        url: '../dashboard/dashboardFunctions.php',
            type:'GET',
            dataType: 'json',
            data:{'function':'fetchDashboardDetails','dashboardName':dashboardName,'did':did,'dashtype':dashtype,'dashboardType':dashboardType, 'csrfMagicToken': csrfMagicToken},
            success: function (data) {
                if(dashboardType == 'dash'){
                    $('#editDashboardName2').val(data.dashname);
                    var home = data.homepage;

                    if(home == 1 || home == '1'){
                        $('#edithomeyes2').prop('checked',true);
                        $('#edithomeno2').prop('checked',false);
                        $(".selectpicker").selectpicker("refresh");
                    }else{
                        $('#edithomeyes2').prop('checked',false);
                        $('#edithomeno2').prop('checked',true);
                        $(".selectpicker").selectpicker("refresh");
                    }
                    console.log(data.userList);
                    var global = data.global;
                    if(global == 1 || global == '1'){
                        $('#editViewAllYes').prop('checked',true);
                        $('#editViewAllNo').prop('checked',false);
                        $('#editViewAllUsersDiv').hide();
                    }else{
                        $('#editViewAllYes').prop('checked',false);
                        $('#editViewAllNo').prop('checked',true);
                        $('#editViewAllUsersDiv').show();
                        $('#editusersList2').html(data.usersList);
                        $(".selectpicker").selectpicker("refresh");
                    }
                }else{
                    $('#editvisualName2').val(data.dashname);
                    var home = data.homepage;

                    if(home == 1 || home == '1'){
                        $('#editVizhomeyes2').prop('checked',true);
                        $('#editVizhomeno2').prop('checked',false);
                    }else{
                        $('#editVizhomeyes2').prop('checked',false);
                        $('#editVizhomeno2').prop('checked',true);
                    }
                    var global = data.global;
                    if(global == 1 || global == '1'){
                        $('#editVizViewAllYes').prop('checked',true);
                        $('#editVizViewAllNo').prop('checked',false);
                        $('#editVizViewAllUsersDiv').hide();
                    }else{
                        $('#editVizViewAllYes').prop('checked',false);
                        $('#editVizViewAllNo').prop('checked',true);
                        $('#editVizViewAllUsersDiv').show();
                        $('#editVizusersList2').html(data.usersList);
                        $(".selectpicker").selectpicker("refresh");
                    }
                }
                
            },
            error:function(error){
                console.log("error");
            }
    });
}

$('#editVizhomeyes2').click(function(){
    $('#editVizhomeyes2').prop('checked',true);
    $('#editVizhomeno2').prop('checked',false);
});

$('#editVizhomeno2').click(function(){
    $('#editVizhomeyes2').prop('checked',false);
    $('#editVizhomeno2').prop('checked',true);
});

$('#editVizViewAllYes').click(function(){
    $('#editVizViewAllYes').prop('checked',true);
    $('#editVizViewAllNo').prop('checked',false);
    $('#editVizViewAllUsersDiv').hide();
});

$('#editVizViewAllNo').click(function(){
    $('#editVizViewAllYes').prop('checked',false);
    $('#editVizViewAllNo').prop('checked',true);
    $('#editVizViewAllUsersDiv').show();
});

$('#editViewAllYes').click(function(){
    $('#editViewAllYes').prop('checked',true);
    $('#editViewAllNo').prop('checked',false);
    $('#editViewAllUsersDiv').hide();
});

$('#editViewAllNo').click(function(){
    $('#editViewAllYes').prop('checked',false);
    $('#editViewAllNo').prop('checked',true);
    $('#editViewAllUsersDiv').show();
});

function openDashboardSlider()
{
    getUsersList();
    rightContainerSlideOn('rsc-add-dashboard');
    return true;  
}

function openUsersVisualSlider()
{
    getUsersList();
    getOriginalVisualList();
    getDefaultVisual();
    if($('#VizreplicateYes').is(':checked')){
        $('#replicateVizDiv').show(); 
        $('#defaultVizDiv').hide();
        $('#VizreplicateYes').prop('checked',true);
    }else{
        $('#replicateVizDiv').hide(); 
        $('#defaultVizDiv').show();
        $('#VizreplicateYes').prop('checked',false);
    }
    
    if($('#VizViewAllYes').is(':checked')){
        $('#VizViewAllUsersDiv').hide(); 
    }else{
        $('#VizViewAllUsersDiv').show(); 
    }
    rightContainerSlideOn('rsc-addUser-visual');
    return true;  
}

$('#VizreplicateYes').click(function(){
    $('#replicateVizDiv').show(); 
    $('#defaultVizDiv').hide();
    $('#VizreplicateNo').prop('checked',false);
});

$('#VizreplicateNo').click(function(){
    $('#replicateVizDiv').hide(); 
    $('#defaultVizDiv').show();
    $('#VizreplicateYes').prop('checked',false);
});

$('#VizViewAllYes').click(function(){
    $('#VizViewAllUsersDiv').hide(); 
    $('#VizViewAllNo').prop('checked',false);
});

$('#VizViewAllNo').click(function(){
    $('#VizViewAllYes').prop('checked',false);
    $('#VizViewAllUsersDiv').show(); 
});

$('#Vizhomeyes2').click(function(){
    $('#Vizhomeno2').prop('checked',false);
});

$('#Vizhomeno2').click(function(){
    $('#Vizhomeyes2').prop('checked',true);
});

function getDefaultVisual(){
    $.ajax({
        url: '../dashboard/dashboardFunctions.php',
        type:'GET',
        data:{'function':'getDefaultViz', 'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            $('#defVizList').html(data);
            $(".selectpicker").selectpicker("refresh");
        }
    });
}

function getOriginalVisualList(){
    $.ajax({
        url: '../dashboard/dashboardFunctions.php',
        type:'GET',
        data:{'function':'get_OrgVizList', 'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            $('#repVizList').html(data);
            $(".selectpicker").selectpicker("refresh");
        }
    });
}

function openUsersDashboardSlider(){
    getUsersList();
    getOriginalDashboardList();
    getDefaultDashboard();
    if($('#replicateYes').is(':checked')){
        $('#replicateDiv').show(); 
        $('#DefaultDashDiv').hide();
    }else{
        $('#replicateDiv').hide(); 
        $('#DefaultDashDiv').show();
    }
    
    if($('#ViewAllYes').is(':checked')){
        $('#ViewAllUsersDiv').hide(); 
    }else{
        $('#ViewAllUsersDiv').show(); 
    }
    rightContainerSlideOn('rsc-addUser-dashboard');
    return true; 
}

$('#replicateYes').click(function(){
   $('#replicateDiv').show(); 
   $('#DefaultDashDiv').hide();
});

$('#replicateNo').click(function(){
   $('#replicateDiv').hide(); 
   $('#DefaultDashDiv').show();
});

$('#ViewAllYes').click(function(){
   $('#ViewAllUsersDiv').hide(); 
   $('#ViewAllNo').prop('checked',false);
});

$('#ViewAllNo').click(function(){
   $('#ViewAllUsersDiv').show(); 
   $('#ViewAllYes').prop('checked',false);
});

function getloggedUsersStatus(){
  $.ajax({
        url: '../dashboard/dashboardFunctions.php',
        type:'GET',
        dataType: 'json',
        data:{'function':'getUsersStatus', 'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            console.log(data.IsAdmin);
            if(data.IsAdmin == 'true'){
                $('#detailViewAudit').show();
            }else{
                $('#detailViewAudit').hide();
            }
        },
        error:function(error){
            console.log("error");
        }
   });  
}


var gulist = false;
function getUsersList() {
    if(gulist){
        return;
    }
    gulist = true;
    $.ajax({
        url: '../dashboard/dashboardFunctions.php',
        type:'GET',
        data:{'function':'get_UsersList', 'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            $('#usersList').html(data);
            $(".selectpicker").selectpicker("refresh");
            $('#VizList').html(data);
            $(".selectpicker").selectpicker("refresh");
            $('#usersList2').html(data);
            $(".selectpicker").selectpicker("refresh");
            $('#VizusersList2').html(data);
            $(".selectpicker").selectpicker("refresh");
        }
    });
}

function getOriginalDashboardList(){
    $.ajax({
        url: '../dashboard/dashboardFunctions.php',
        type:'GET',
        data:{'function':'get_OrgDashList', 'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            $('#repDashList').html(data);
            $(".selectpicker").selectpicker("refresh");
        }
    });
}

function getDefaultDashboard(){
    $.ajax({
        url: '../dashboard/dashboardFunctions.php',
        type:'GET',
        data:{'function':'getDefaultDashboard', 'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            $('#defDashList').html(data);
            $(".selectpicker").selectpicker("refresh");
        }
    });
}

function AddDashboard(type){
    
    if(type == 'dash'){
        var Dashname = $('#DashboardName').val();
        var UsersList = $('#usersList').val();
        
    }else{
        var Dashname = $('#VizualName').val();
        var UsersList = $('#VizList').val();
    }
    
    if(Dashname == ''){
        $.notify("Please Enter the Dashboard Name");
        return false;
    }
    var global = 0;
    var home = 0;
    if($('#globalyes').is('checked')){
        global = 1;
    }else{
        global = 0;
    }
    
    if($('#homeyes').is('checked')){
        home = 1;
    }else{
        home = 0;
    }
    
    var mdata = {
        'function':'AddDashboard',
        'dashboardname': Dashname,
        'users':UsersList,
        'type' : type,
        'global' : global,
        'home' : home, 'csrfMagicToken': csrfMagicToken
    };
    $.ajax({
        url: '../dashboard/dashboardFunctions.php',
        type:'POST',
        data: mdata,
        success: function (data) {
            data = $.trim(data);
            if(data == 'success'){
                if(type == 'dash'){
                    rightContainerSlideClose('rsc-add-dashboard');
                    $.notify("Dashboard successfully added");
                    setTimeout(function(){
                        location.reload();
                    },2000);
                }else{
                    rightContainerSlideClose('rsc-viz-dashboard');
                    $.notify("Visualization successfully added");
                    setTimeout(function(){
                        location.reload();
                    },2000);
                }
                
            }else{
                $.notify("Failed to add");
            }
        },
        error:function(error){
            console.log("error");
        }
    });
    
}

function ViewEditCharts(dashid,dashname){
    var id = dashid;
    var name = dashname;
    previewDashboardById(name,id,'dash');
}

function ViewEditVizCharts(dashid,dashname){
    var id = dashid;
    var name = dashname;
    previewDashboardById(name,id,'viz');
}

function previewDashboardById(dashname,dashid,type) {
    $('#AddDashboard').hide();
    $('#backWindowbtn').show();
    $('#deleteDashboard').hide();
    $('#AddVisual').hide();
    $('#deleteVisual').hide();
    $('#backWindowbtn2').show();
    var dashboardId = dashid;
    var dashName = dashname;

    var level = $('#searchType').val();
    var val = $('#searchValue').val();
    $.ajax({
        type: "POST",
        url: "../dashboard/dashboardFunctions.php",
        data: {
            "function" : 'load_CubePage',
            "dashid": dashboardId,
            "dashName": dashName,
            'type' : type, 
            'csrfMagicToken': csrfMagicToken
        },
        dataType: 'json',
        success: function (data) {
            // Create global var with current visualization service config. 
            // visualization iframe will take from here jwt token for cubjs querys.
            window.visualizationServiceConfig = data
            if(type == 'dash'){
                $('#dashboardTitle').html('');
                $('#PreviewIframe').show();
                $('#PreviewDashTable').hide();
                document.getElementById('Iframe').src = data.url;
                $('#Iframe').show();
                $('#dashboardTitle').html(dashName);
            }else{
                $('#vizTitle').html('');
                $('#PreviewVizIframe').show();
                $('#PreviewVizTable').hide();
                document.getElementById('Iframe2').src = data.url;
                $('#Iframe2').show();
                $('#vizTitle').html(dashName);
            }
            
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {

        }
    });
}

$(".rightslide-container-close").click(function () {
    $('#globalCheck').prop("checked", true);
    $('#quickCheck').prop("checked", true);
    $('#defaultCheck').prop("checked", true);
    $("#dashboardName").val("");
});

function openVizSlider()
{
    getUsersList();
    rightContainerSlideOn('rsc-viz-dashboard');
    return true;  
}

function previewVizById() {
    var vizId = $('#VizId').val();
    var vizName = $('#VizName').val();
    var type = 'vizual';

    $.ajax({
        type: "POST",
        url: "../dashboard/dashboardFunctions.php",
        data: {
            "function" : 'load_CubePage',
            'type' : type,
            "dashid": vizId,
            "dashName": vizName, 
            'csrfMagicToken': csrfMagicToken
        },
        dataType: 'json',
        success: function (data) {
            $('#dashboardTitle2').html('');

            // Create global var with current visualization service config. 
            // visualization iframe will take from here jwt token for cubjs querys.
            window.visualizationServiceConfig = data

            var src = document.getElementById('Iframe2').src = data.url;
            $('#iframeModal2').modal('show');
            $('#dashboardTitle2').html(vizName);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {

        }
    });
}

$('#homeyes').click(function(){
   $('#homeyes').prop('checked',true);
   $('#homeno').prop('checked',false); 
});
        
$('#homeno').click(function(){
   $('#homeno').prop('checked',true);
   $('#homeyes').prop('checked',false); 
});

$('#homeyes2').click(function(){
   $('#homeyes2').prop('checked',true);
   $('#homeno2').prop('checked',false); 
});
        
$('#homeno2').click(function(){
   $('#homeno2').prop('checked',true);
   $('#homeyes2').prop('checked',false); 
});

$('#edithomeyes2').click(function(){
   $('#edithomeyes2').prop('checked',true);
   $('#edithomeno2').prop('checked',false); 
});
        
$('#edithomeno2').click(function(){
   $('#edithomeno2').prop('checked',true);
   $('#edithomeyes2').prop('checked',false); 
});


function AddUsersVisual(){
    var dashName = $('#visualName2').val();
    
    if(dashName == ''){
        $.notify("Please Enter a Dashboard Name");
        return false;
    }
    
    if($('#VizreplicateYes').is(':checked')){
        var replicateID = $('#repVizList').val();
        var defType = '';
    }else{
        var replicate = '';
        var defType = $('#defVizList').val();
    }
    
    if($('#VizViewAllYes').is(':checked')){
        var UserList = 'All';
    }else{
        var UserList = $('#VizusersList2').val();
    }
    
    if($('#Vizhomeyes2').is(':checked')){
        var home = 1;
    }else{
        var home = 0;
    }
    
    var mdata = {
        'function' : 'SaveUsersViz',
        'users' : UserList,
        'dashName' : dashName,
        'home' : home,
        'replicate' : replicateID,
        'defType' : defType, 'csrfMagicToken': csrfMagicToken
    };
    
    $.ajax({
        url: '../dashboard/dashboardFunctions.php',
        type:'POST',
        data: mdata,
        dataType: 'json',
        success: function (data) {
            var Vizname = data.VizName;
            var vizid = data.VizId;
            rightContainerSlideClose('rsc-addUser-visual');
            previewDashboardById(Vizname,vizid,'viz');
        }
    });
    
}

function AddUsersDashboard(){
    var dashName = $('#DashboardName2').val();
    
    if(dashName == ''){
        $.notify("Please Enter a Dashboard Name");
        return false;
    }
    
    if($('#replicateYes').is(':checked')){
        var replicateID = $('#repDashList').val();
        var defType = '';
    }else{
        var replicate = '';
        var defType = $('#defDashList').val();
    }
    
    if($('#ViewAllYes').is(':checked')){
        var UserList = 'All';
    }else{
        var UserList = $('#usersList2').val();
    }
    
    if($('#homeyes2').is(':checked')){
        var home = 1;
    }else{
        var home = 0;
    }
    
    var mdata = {
        'function' : 'SaveUsersDashboard',
        'users' : UserList,
        'dashName' : dashName,
        'home' : home,
        'replicate' : replicateID,
        'defType' : defType, 'csrfMagicToken': csrfMagicToken
    };
    
    $.ajax({
        url: '../dashboard/dashboardFunctions.php',
        type:'POST',
        data: mdata,
        dataType: 'json',
        success: function (data) {
            var dashname = data.DashboardName;
            var dashid = data.DashId;
            rightContainerSlideClose('rsc-addUser-dashboard');
            previewDashboardById(dashname,dashid,'dash');
//            console.log(data);
        }
    });
    
}

function BackToMain(){
    location.reload();
}

function EditUsersDashboard(){
    var dashName = $('#editDashboardName2').val();
    var type = $('#dashType').val();
    var dashid = $('#dashId').val();
    
    if(dashName == ''){
        $.notify("Please Enter a Dashboard Name");
        return false;
    }
    
    if($('#editViewAllYes').is(':checked')){
        var UserList = 'All';
    }else{
        var UserList = $('#editusersList2').val();
    }
    
    if($('#edithomeyes2').is(':checked')){
        var home = 1;
    }else{
        var home = 0;
    }
    
    var mdata = {
        'function' : 'UpdateUsersDashboard',
        'users' : UserList,
        'dashName' : dashName,
        'home' : home,
        'type' : type,
        'dashid' : dashid, 'csrfMagicToken': csrfMagicToken
    };
    
    $.ajax({
        url: '../dashboard/dashboardFunctions.php',
        type:'POST',
        data: mdata,
        success: function (data) {
              data = $.trim(data);
              if(data == 'success'){
                  rightContainerSlideClose('rsc-editUser-dashboard');
                  $.notify("Dashboard Details has been updated successfully");
                  setTimeout(function(){
                        location.reload();
                    },2000);
              }else{
                  rightContainerSlideClose('rsc-editUser-dashboard');
                  $.notify("Failed to update the Dashboard Details");
                  setTimeout(function(){
                        location.reload();
                    },2000);
              }
        }
    });
    
}

function DeleteDashboard(dashType){
    if(dashType == 'dash'){
        var did = $('#dashId').val();
        var type = $('#dashType').val();
    }else{
        var did = $('#VizId').val();
        var type = $('#VizType').val();
    }
    
    if(did == ''){
        $.notify("Please select a record to delete");
        return false;
    }else{
        closePopUp();
        sweetAlert({
            title: 'Are you sure that you want to continue?',
            text: "You wont be able to recover the dashboard once deleted",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#050d30',
            cancelButtonColor: '#fa0f4b',
            cancelButtonText: "No, cancel it!",
            confirmButtonText: 'Yes, delete it!'
        }).then(function (result) {

            $.ajax({
                url: '../dashboard/dashboardFunctions.php',
                type: 'GET',
                data: {'function':'getUsersStatus', 'csrfMagicToken': csrfMagicToken},
                dataType: 'json',
                success: function (data) {
                    if (data.IsAdmin === 'true') {
                        $.ajax({
                            type: "DELETE",
                            url: '../dashboard/dashboardFunctions.php?function=DeleteDashboardFn&did='+did+'&type='+type+'&dashType'+dashType + "&csrfMagicToken=" + csrfMagicToken,
                            dataType: 'json',
                            success: function (data) {
                                if(data.msg == 'success'){
                                    $.notify("Dashboard Deleted Successfully");
                                    setTimeout(function(){
                                        location.reload();
                                    },2000);
                                }else{
                                    $.notify("Failed To Delete Dashboard");
                                    setTimeout(function(){
                                        location.reload();
                                    },2000);
                                }
                            }
                        });
                    } else {

                    }
                }
            });
        }).catch(function (reason) {
            $(".closebtn").trigger("click");
        });

    }
}


function EditUsersVisual(){
    var dashName = $('#editvisualName2').val();
    var type = $('#VizType').val();
    var dashid = $('#VizId').val();
    
    if(dashName == ''){
        $.notify("Please Enter a Dashboard Name");
        return false;
    }
    
    if($('#editVizViewAllYes').is(':checked')){
        var UserList = 'All';
    }else{
        var UserList = $('#editVizusersList2').val();
    }
    
    if($('#editVizhomeyes2').is(':checked')){
        var home = 1;
    }else{
        var home = 0;
    }
    
    var mdata = {
        'function' : 'UpdateUsersViz',
        'users' : UserList,
        'dashName' : dashName,
        'home' : home,
        'type' : type,
        'dashid' : dashid, 'csrfMagicToken': csrfMagicToken
    };
    
    $.ajax({
        url: '../dashboard/dashboardFunctions.php',
        type:'POST',
        data: mdata,
        success: function (data) {
              data = $.trim(data);
              if(data == 'success'){
                  rightContainerSlideClose('rsc-editUser-visual');
                  $.notify("Adhoc Reporting Details has been updated successfully");
                  setTimeout(function(){
                        location.reload();
                    },2000);
              }else{
                  rightContainerSlideClose('rsc-editUser-visual');
                  $.notify("Failed to update the Adhoc Reporting Details");
                  setTimeout(function(){
                        location.reload();
                    },2000);
              }
        }
    });
    
}