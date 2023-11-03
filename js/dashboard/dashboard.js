
var dashboardCreateInProgress = false;
var currentSelectedVizualizationList = false;

$(document).ready(function () {
    dashboard_datatableList();
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

function dashboard_datatableList() {

    $.ajax({
        url: "dashboardFunction.php",
        type: "GET",
        data: {'function' : 'get_DashboardList', 'csrfMagicToken': csrfMagicToken},
        dataType: "json",
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#dashboardList').DataTable().destroy();
            dashboardTable = $('#dashboardList').DataTable({
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: true,
                select: false,
                bInfo: false,
                responsive: true,
                stateSave: true,
                scrollY: 'calc(100vh - 240px)',
                "pagingType": "full_numbers",
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    search: "_INPUT_",
                    searchPlaceholder: "Search records",
                },
                columnDefs: [{ "type": "date", "targets": [5] }],
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function (settings, json) {
                }
            });
            $('.dataTables_filter input').addClass('form-control');
        },
        error: function (msg) {

        }
    });

    $('#dashboardList').on('click', 'tr', function () {
        var rowID = dashboardTable.row(this).data();

        dashboardTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var dId = rowID[6];
        var name = rowID[0];
        var type = rowID[7];
        $('#dashName').val(name);
        $('#dashId').val(dId);
        $('#dashType').val(type);

    });
    $('#dashboardList').on('dblclick', 'tr', function () {
        //previewDashboardById();
        rightContainerSlideOn("rsc-edit-dashboard");
        $('#editOption').show();
        $('#toggleButton').hide();
        disableContainerFields($('#rsc-edit-dashboard'));
        getVisualisationDetails();
    });

}

function openCreateDashboardSlider()
{
    resetCreateDashboardSlider();
    getVisList();
    rightContainerSlideOn('rsc-add-dashboard');
    return true;  
}

function resetCreateDashboardSlider()
{
    $('#rsc-add-dashboard #dashboardName').val('');
    $('#rsc-add-dashboard #globalCheck').prop('checked', false);
    $('#rsc-add-dashboard #globalCheckNo').prop('checked', true);
    $('#rsc-add-dashboard #dashboard').prop('checked', true);
    $('#rsc-add-dashboard #insight').prop('checked', false);
    $('#rsc-add-dashboard #defaultCheck').prop('checked', false);
    $('#rsc-add-dashboard #defaultCheckNo').prop('checked', true);
    
    return true; 
}

function resetCreateKibanaDashboardSlider(){
    $('#rsc-add-kibana-dashboard #kibana-dashboard-name').val('');
    $('#rsc-add-kibana-dashboard #globalCheck2').prop('checked', false);
    $('#rsc-add-kibana-dashboard #globalCheckNo2').prop('checked', true);
    $('#rsc-add-kibana-dashboard #dashboard2').prop('checked', true);
    $('#rsc-add-kibana-dashboard #insight2').prop('checked', false);
    $('#rsc-add-kibana-dashboard #defaultCheck2').prop('checked', false);
    $('#rsc-add-kibana-dashboard #defaultCheckNo2').prop('checked', true);
    
    return true;
}

function getVisList() 
{
    var rightSlider = new RightSlider('#rsc-add-dashboard');
    rightSlider.showLoader();
    
    $.ajax({
        url: 'dashboardFunction.php',
        type:'GET',
        data:{'function':'get_VisualisationList', 'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            $('#visList').html(data);
            $(".selectpicker").selectpicker("refresh");
            rightSlider.hideLoader();
        },
        error : function(){
            errorNotify("Something went wrong");
            rightSlider.hideLoader();
        }
    });
}

function getEditVisList() 
{
    var vizList = window.currentSelectedVizualizationList;
    
    if(vizList && vizList.indexOf(',') > 0){
        vizList = vizList.split(',');
    } 
    
    $.ajax({
        url: 'dashboardFunction.php',
        type:'GET',
        data:{'function':'get_VisualisationList', 'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            $('#editVisList').html(data);
           
            if(vizList!=undefined || vizList || (Array.isArray(vizList) && vizList.length > 0)){
                $('#editVisList').selectpicker('val', vizList);
            }
            
            $('#editVisList').selectpicker('refresh');
        }
    });
}

function getUserList() {
    $.ajax({
        url: 'dashboardFunction.php',
        type:'GET',
        data:{'function':'get_UserList', 'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            $('#userList').html(data);
            $(".selectpicker").selectpicker("refresh");
        }
    });
}

$('input[name="exampleRadios1"]').click(function () {
    if ($('#dashboard').is(":checked")) {
        $('.defaultCheck').show();
    } else {
        $('.defaultCheck').hide();
    }
    
    if ($('#dashboard2').is(":checked")) {
        $('.defaultCheck').show();
    } else {
        $('.defaultCheck').hide();
    }
});


function previewDashboard() {
    if (window.dashboardCreateInProgress) {
        errorNotify("A dashboard add request is already in progress.<br />Please wait while we complete your request");
        return;
    }

    var dashboard = "0";
    var envGlobal = "0";
    var vid = $('#visList').val();
    var dashboardName = $('#dashboardName').val();
    var dashboardType = 'Normal Dashboard';
    //var dashId = $('#dashboardId').val();
    var global = $('#globalCheck').is(":checked") ? "1" : "0";
    var defaultCheck = $('#defaultCheck').is(":checked") ? "1" : "0";
    if ($('#dashboard').is(":checked")) {
        dashboard = "1";
    } else {
        defaultCheck = "0";
    }
    if ($('#evironmentGlobal').is(":checked")) {
        envGlobal = "1";
    }

    if (dashboardName == '') {
        $.notify("Please provide visualisation name");
        return false;
    }
    if (vid == '') {
        $.notify("Please choose atleast one visualisation");
        return false;
    }

    window.dashboardCreateInProgress = true;

    $.ajax({
        url: 'dashboardFunction.php',
        type: 'POST',
        dataType: 'json',
        data: {'function':'save_Dashboard',visid: vid, dname: dashboardName, global: global, default: defaultCheck, dash: dashboard, envglobal: envGlobal,dashType : dashboardType, 'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            window.dashboardCreateInProgress = false;
            data = $.parseJSON(data);
            if (!data.success) {
                errorNotify(data.message);
                return;
            }

            $.notify("Visualisation added successfully");
            rightContainerSlideClose('rsc-add-dashboard');
            dashboard_datatableList();
            //document.getElementById('Iframe').src = data.data;
            //$('#iframeModal').modal('show');
            //$('#dashboardTitle').html(dashboardName);
        },
        error: function () {
            window.dashboardCreateInProgress = false;
        }
    });
}

function previewDashboardById() {
    var dashboardId = $('#dashId').val();
    var dashName = $('#dashName').val();

    var level = $('#searchType').val();
    var val = $('#searchValue').val();
    $.ajax({
        type: "GET",
        url: "../home/homeFunction.php",
        data: {
            "function" : 'loadHomePage',
            "kid": dashboardId,
            "lev": level,
            "value": val, 'csrfMagicToken': csrfMagicToken
        },
        dataType: 'json',
        success: function (data) {

            $('#dashboardTitle').html('');
            var src = document.getElementById('Iframe').src = data.url;

            $('#iframeModal').modal('show');
            $('#dashboardTitle').html(dashName);

        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {

        }
    });
}

function deleteDashboard() {

    var dashboardId = $('#dashId').val();

    if (dashboardId == undefined || dashboardId == 'undefined' || dashboardId == '') {
        $.notify("Please select a dashboard");
        closePopUp();
    } else {
        sweetAlert({
            title: 'Are you sure?',
            text: "You will not be able to recover this dashboard!",
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#050d30',
            cancelButtonColor: '#fa0f4b',
            confirmButtonText: 'Yes, delete it!'
        }).then(function (result) {
            $.ajax({
                url: "dashboardFunction.php?function=delete_Dashboard&did="+dashboardId + "&csrfMagicToken=" + csrfMagicToken,
                type: 'delete',
                success: function (data) {
                    if ($.trim(data) === 'success') {
                        dashboard_datatableList();
                        sweetAlert(
                                'Deleted!',
                                'Your dashboard has been deleted.',
                                'success'
                                );
                    } else if ($.trim(data) === 'Permission Denied') {
                        sweetAlert(
                                'Permission Denied'
                                );
                    } else {
                        sweetAlert(
                                'Something went wrong.Try again later',
                                'failed'
                                );
                    }

                }
            });
        });
        closePopUp();
    }
}


$(".rightslide-container-close").click(function () {
    $('#globalCheck').prop("checked", true);
    $('#quickCheck').prop("checked", true);
    $('#defaultCheck').prop("checked", true);
    $("#dashboardName").val("");
});

function getVisualisationDetails() 
{
    try{
        
        var dashboardId = $('#dashId').val();
        var dashName = $('#dashName').val();
        var editOption = $('#editOption');
        var DashboardType = $('#dashType').val();
        $('#editDashboardName').val(dashName);
        editOption.hide();

        var rightSlider = new RightSlider('#rsc-edit-dashboard');
        rightSlider.showLoader();

        var data = { 'function' : 'get_DashboardVisData', dashid : dashboardId, 'csrfMagicToken': csrfMagicToken};
        $.ajax({
            url: "dashboardFunction.php",
            type: 'GET',
            data: data,
            success: function (data) {
                var res = JSON.parse(data);
                var loggedUid = window.loggedUserId;

                if(loggedUid!=undefined && !isNaN(loggedUid) && res.uid!=undefined && !isNaN(res.uid) && parseInt(loggedUid) == parseInt(res.uid)){
                    editOption.show();
                }

                if (res.global == 1) {
                    $('#editglobalCheck').prop("checked", true);
                } else {
                    $('#editglobalCheckNo').prop("checked", true);
                }
                if(res.type == 1) {
                    $('#editDashboard').prop("checked", true);
                } else {
                    $('#editInsight').prop("checked", true);
                }

                if(res.defaultPage!=undefined && !isNaN(res.defaultPage) && 1 == parseInt(res.defaultPage)) {
                    $('#editDefaultCheck').prop("checked", true);
                    $('#editDefaultCheckNo').prop("checked", false);
                } else {
                    $('#editDefaultCheck').prop("checked", false);
                    $('#editDefaultCheckNo').prop("checked", true);
                }

                if(res.userList == 'Normal Dashboard'){
                    $('#edit-kibana').hide();
                    $('#edit-normaldash').show();
                window.currentSelectedVizualizationList = res.vizualization_ids;
                getEditVisList();
                }else{
                    $('#edit-normaldash').hide();
                    $('#edit-kibana').show();
                    $('#kibana-dashboard-id2').val(res.dashboardId);
                }
                
                rightSlider.hideLoader();

            },
            error: function (error) {
                errorNotify("Something went wrong");
                rightSlider.hideLoader();
            }
        });
    } catch(e){
        errorNotify("Something went wrong");
        rightSlider.hideLoader();
    }
}

function updateVizDashboard(){
    var editVid = $('#editVisList').val();
    var kibanadashboardId = $('#kibana-dashboard-id2').val();
    var dashboardName = $('#editDashboardName').val();
    var dashId = $('#dashId').val();
    var global = $('#editglobalCheck').is(":checked") ? "1" : "0";
    var defaultCheck = $('#editDefaultCheck').is(":checked") ? "1" : "0";
    var dashtype = $('#dashType').val();
    var dashboard = "0";
    var envGlobal = "0";
    if ($('#editDashboard').is(":checked")) {
        dashboard = "1";
    } else {
        defaultCheck = "0";
    }
    if ($('#editEvironmentGlobal').is(":checked")) {
        envGlobal = "1";
    }

    if (dashboardName == '') {
        $.notify("Please provide Dashboard name");
        return false;
    }
    if(dashtype == 'Normal Dashboard'){
    if (editVid == '') {
        $.notify("Please choose atleast one visualisation");
        return false;
    }
    }else{
        if(kibanadashboardId == ''){
            $.notify("Please enter the Dashboard Id");
            return false;
        }
    }
    
    
    if(window.dashboardCreateInProgress){
        $.notify("A request for a Vizualization Update is already in progress. Please wait until we finish updating your request");
        return false;
    }
    
    window.dashboardCreateInProgress = true;
    
    $.ajax({
        url: 'dashboardFunction.php',
        type: 'POST',
        data: {'function' : 'update_Dashboard', dashid : dashId, visid: editVid, dname: dashboardName, global: global, default: defaultCheck, dash: dashboard, envglobal: envGlobal,dashtype:dashtype,kibanadashboardId:kibanadashboardId, 'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            window.dashboardCreateInProgress = false;
            data = $.parseJSON(data);
            if (!data.success) {
                errorNotify(data.message);
                return;
            }

            $.notify("Dashboard updated successfully");
            rightContainerSlideClose('rsc-edit-dashboard');
            dashboard_datatableList();
        },
        error: function () {
            window.dashboardCreateInProgress = false;
        }
    });
}

function addKibanaDashboard() {
    resetCreateKibanaDashboardSlider();
    $('#rsc-add-kibana-dashboard input[type=text]').val('');
    rightContainerSlideOn('rsc-add-kibana-dashboard');
    return true;   
}

function createKibanaDashboard() {
    var kibdashid = $('#kibana-dashboard-id').val();
    var kibdashname = $('#kibana-dashboard-name').val();
    var dashboard = "0";
    var dashType = "Kibana Dashboard";
    var envGlobal = "0";
    var global = $('#globalCheck2').is(":checked") ? "1" : "0";
    var defaultCheck = $('#defaultCheck2').is(":checked") ? "1" : "0";
    
    if ($('#dashboard2').is(":checked")) {
        dashboard = "1";
    } else {
        defaultCheck = "0";
    }

    if(kibdashid === '') {
        errorNotify('Please enter kibana Dashboard ID.');
        return false;
    } else if(kibdashname === '') {
        errorNotify('Please enter kibana Dashboard Name.');
        return false;
    }

    $.ajax({
        type: 'POST',
        url: 'dashboardFunction.php',
        data: {'function' : 'add_KibanaDashboard', kibid: kibdashid, kibname: kibdashname , global: global, default: defaultCheck, dash: dashboard, dashType: dashType, 'csrfMagicToken': csrfMagicToken},
        success: function (data) {
            if(data == 'done') {
                errorNotify('Kibana dashboard added successfully!');
                setTimeout(function(){
                    rightContainerSlideClose('rsc-add-kibana-dashboard');
                    dashboard_datatableList();
                }, 1500);
            } else if(data == 'exist') {
                errorNotify('Dashboard already exists with same Name!');
                return false;
            } else {
                errorNotify('Failed to add Kibana dashboard!');
                return false;
            }
        }
    });
}