//$(document).ready(function(){    
//   SitesInfoChartGridData();       
//});
$(document).ready(function () {

    $("#agentlogin").click(function () {
        $(".pass-LMI").hide();
        $("#rmt_loginid").prop("disabled", true);
        $("#rmt_pass").prop("disabled", true);
        $("#rmt_passconf").prop("disabled", true);
        $("#usernameEmail").prop("disabled", false);
    });
    $("#rmt_login").click(function () {
        $(".pass-LMI").show();
        $("#usernameEmail").prop("disabled", true);
        $("#rmt_loginid").prop("disabled", false);
        $("#rmt_pass").prop("disabled", false);
        $("#rmt_passconf").prop("disabled", false);

    });

});
function SitesInfoChartGridData() {
//   $('#information-portal').modal('show');
    renderChart();      
    renderGrid("Windows", 'first');
//   $('#remotesuccessmsg').modal('show');
}

function renderChart() {
    $.ajax({
        type: "GET",
        url: "sitefunctions.php?function=sitespiechartdata",
        data: "{}",
        async: true,
        dataType: "json",
        success: function (data) {
            $('#windows').html(data.windows);
            $('#android').html(data.android);
            $('#linux').html(data.linux);
            $('#mac').html(data.mac);
            $('#ios').html(data.iOS);
            $('#other').html(data.other);
            var total = parseInt(data.windows) + parseInt(data.android) + parseInt(data.linux) + parseInt(data.mac) + parseInt(data.iOS) + parseInt(data.other);
            $('#Total').html(total);
            graphData = data;
            google.charts.load("current", {packages: ["corechart", 'bar']});
            google.charts.setOnLoadCallback(drawChart1);
        }
    });    
}

function renderGrid(gridType, obj) {
//    $('#eventsearch').show();
//    $('#eventdetail').hide();
    $('#gridtype').val(gridType);
    var search = $("#deviceSearch").text();
    $("#replace").text("Sites Information : " + search);
    if (obj == 'first') {
        $('.device-list li').removeClass('active');
        $('.device-list li').first().addClass('active');
    } else {
        $('.device-list li').first().removeClass('active');
        $('.device-list li').removeClass('active');
        $(obj).addClass('active');
    }
    $(".se-pre-con").show();
    $.ajax({
        url: "sitefunctions.php?function=sitegridlist&gridType=" + gridType,
        type: "POST",
        dataType: 'json',
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function(gridData) {
//            console.log(gridData);
            $(".se-pre-con").hide();
            $('#DeviceTypeData').DataTable().destroy();
            groupTable = $('#DeviceTypeData').DataTable({
        scrollY: jQuery('#DeviceTypeData').data('height'),
        scrollCollapse: true,
                paging: true,
        searching: true,
        ordering: true,
                aaData: gridData,
                bAutoWidth: false,
        select: false,
        bInfo: false,
        responsive: true,
                stateSave: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                drawCallback: function(settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
                    $('.equalHeight').matchHeight();
            $(".se-pre-con").hide();
        }

    });
        },
        error: function(msg) {

        }
        });

    $('#DeviceTypeData').on('click', 'tr', function() {
        var rowID = groupTable.row(this).data();
        var hostname = rowID[0];
        var siteName = rowID[6];
//        $("#selID").val(id1);
        groupTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        $("#selected").val(hostname); 
        $("#sitename").val(siteName);
        $('#rowstatus').val(rowID.rowstatus);
    });
    
    $("#sites_searchbox").keyup(function() {
        groupTable.search(this.value).draw();
    });
    machineremotemenuenable();
}


function machineremotemenuenable() {
    var machinelevel = $('#searchTypedevices').val();
    
    if(machinelevel == 'ServiceTag') {
       $("#machine_remote").css({"pointer-events": "fill", "color": "#333333"});
    } else {
       $("#machine_remote").css({"pointer-events": "none", "color": "#bfbfbf"});
    }
}

function drawChart1() {
    var data1 = google.visualization.arrayToDataTable([
        ['Task', 'Hours per Day'],
        ['Windows', graphData.windows],
        ['Mac', graphData.mac],
        ['Android', graphData.android],
        ['Linux', graphData.linux],
        ['iOS',graphData.iOS],
        ['Others', graphData.other]
    ]);
    var options1 = {
        //title: '',
        pieHole: 0.9,
        pieSliceText: 'none',
        chartArea: {left: '10px', top: '-20px', width: '90%', height: '90%'},
        legend: {
            position: 'right',
            alignment: 'center'
        },
        slices: {
            0: { color: '#00bcf2' },
            1: { color: '#027496' },
            2: { color: '#0059b2' },
            3: { color: '#bad6da' },
            4: { color: '#3C95B9' }
        }
    };

    var chart1 = new google.visualization.PieChart(document.getElementById('donutchart1'));
    chart1.draw(data1, options1);
    // var chart3 = new google.visualization.PieChart(document.getElementById('donutchart3'));
    /* function close */
}

function selectRowConfirm(data_target_id) {
    
    var selected = $("#selected").val();
    var sitename = $("#sitename").val();
    if (selected === '') {
        $('#warning').modal('show');
//        $('#' + data_target_id).attr('data-bs-target', '#warning');
    } else {
        if (data_target_id == 'view_events') {
            $('#' + data_target_id).removeAttr('data-bs-target');
            $("#selected").val("");
            location.href = "events.php?host=" + selected + "&cust=" + sitename;
        } else if (data_target_id == 'view_assets') {
            $('#' + data_target_id).removeAttr('data-bs-target');
            $("#selected").val("");
            location.href = "assets.php?host=" + selected + "&cust=" + sitename;
        } else if (data_target_id == 'machine_remote') {
            Machineremote(selected);
        }
    }
    return true;
}

function Exportsitelist() {
    var grid = $('#gridtype').val();
    window.location.href = 'sitefunctions.php?function=Exportdeviceslist&gridtype=' + grid;
}

function eventDetail(idx){
    $("#eventstatusgrid").dataTable().fnDestroy();
    $('#eventstatusgrid').DataTable({
        scrollY: jQuery('#eventstatusgrid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        searching: true,
        processing: true,
        serverSide: true,
        bAutoWidth: true, 
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        ajax: {
            url: "sitefunctions.php?function=get_eventDeetailpopup&idx=" + idx,
            type: "POST"
        },
          language: {
            "info": "_START_-_END_ <span>of<span> _TOTAL_ <span>entries<span>",
            searchPlaceholder: "Search"
        },
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        columns: [
            {"data": "clienttime"},
            {"data": "servertime"},
            {"data": "text"}
           
        ],
        "columnDefs": [
            { className: "dt-left", "targets": 0 },
            { className: "dt-left", "targets": 1 },
            { className: "dt-left", "targets": 2 }
          ],          
        ordering: true,
        select: false,
        bInfo: false,
        responsive: true,                 
        dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',   
    });
    $('#eventstatusgrid_length').hide();
}

function Machineremote(host) {
    //reset pop up fields
    $("#agentlogin").prop('checked', true);
    $(".pass-LMI").hide();
    $("#rmt_loginid").prop("disabled", true);
    $("#rmt_pass").prop("disabled", true);
    $("#rmt_passconf").prop("disabled", true);
    $("#usernameEmail").prop("disabled", false);
    $.ajax({
        url: 'sitefunctions.php?function=get_machineremote&hostname=' + host,
        type: 'post',
        dataType: 'json',
        success: function (data) {
            if (data == 'Online') {
                $('#machineonlineremote').modal('show');
            } else {
                $('#remotewarning').modal('show');
            }
        }
    });
}