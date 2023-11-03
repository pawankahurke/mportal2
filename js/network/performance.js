$(document).ready(function() {
    Performance_datatablelist();
});

function getPerformanceData() {
    google.charts.load("current", {packages: ["corechart", 'bar']});
    google.charts.setOnLoadCallback(drawProcessChart);
}

function drawProcessChart() {
    var searchType  = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var monthYear   = $('#monthDropDown').val();
    var processName = $("#process_name").val();

    var graphArray  = createProcessArray(searchType, searchValue, monthYear,processName);

    var data = google.visualization.arrayToDataTable(graphArray);
    var options = {
        title: '',
        chartArea: {left: 0, top: 0, width: '100%', height: '100%', backgroundColor: '#ffffff', },
        legend: {position: 'none'},
        backgroundColor: '#ffffff',
        colors: ['#48b2e4', '#62d433'],
        bar: {groupWidth: "10%"},
        series: {
            0: {color: '#48b2e4'},
        },
        isStacked: true,
    };
    var chart = new google.charts.Bar(document.getElementById('processChart'));
    chart.draw(data, google.charts.Bar.convertOptions(options));
    $(".loader").hide();
}

function createProcessArray(searchType, searchValue,month,processName){
    var resultObject;
//    var data = "function=getIncidentReportCounts&searchType=" + searchType + "&searchValue=" + searchValue + "&monthYear=" + monthYear;
//    var encodedData = get_RSA_EnrytptedData(data);

    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "networkfunction.php?function=getPerformanceProcessGraph",
        data: {
            searchType: searchType, searchValue: searchValue, month: month, process: processName,
            'csrfMagicToken': csrfMagicToken},
        async: false,
        success: function(result) {
            resultObject = result;
        }
    });

    var incidentArray  = [['', 'TcpIn','TcpOut', 'UdpIn', 'UdpOut']];
    var i = 1;
    for (var key in resultObject) {
        if (resultObject.hasOwnProperty(key)) {
            incidentArray[i] = [key,parseInt(resultObject[key]['TcpIn']),parseInt(resultObject[key]['TcpOut']),
                        parseInt(resultObject[key]['UdpIn']),parseInt(resultObject[key]['UdpOut'])];
            i++;
        }
    }
    return incidentArray;
}

function Performance_datatablelist() {

    var searchType =  $('#searchType').val();
    var searchVal = $('#searchValue').val();
    var rparent   = $('#rparentName').val();
    var name = rparent.split("__");
    var monthYear   = $('#monthDropDown').val();

    if(searchType == 'Groups') {
        $('#heading').html('<span>Network Report : '+name[0]+'</span>');
    } else if(searchType == 'Sites') {
        var val = searchVal.split("__");
        $('#heading').html('<span>Network Report : '+val[0]+'</span>');
    }else {
        $('#heading').html('<span>Network Report : '+searchVal+'</span>');
    }

    $.ajax({
        url: "networkfunction.php?function=getPerformanceData&month="+monthYear,
        type: "POST",
        dataType: "json",
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function(gridData) {
//            console.log(gridData);
            $(".loader").hide();
            $('#performanceList').DataTable().destroy();
            performanceTable = $('#performanceList').DataTable({
                scrollY: jQuery('#performanceList').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo:false,
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
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                initComplete: function (settings, json) {
                    if($('#performanceList tbody tr:eq(0) p')[0] === undefined || $('#performanceList tbody tr:eq(0) p')[0] ==='undefined') {
                      getPerformanceData();
                    } else {
                    var process = $('#performanceList tbody tr:eq(0) p')[0].id;
                    $('#performanceList tbody tr:eq(0)').addClass("selected");
                    $("#process_name").val(process);
                    $('#process').html('<span>Process: '+process+'</span>');
                    getPerformanceData();
                    }
                },
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });
    $('#performanceList tbody').on('click', 'tr', function() {
        //row selection code
        $("#load").show();
        performanceTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        if ($(this).hasClass('selected')) {
            var rowdata = performanceTable.row(this).data();
            $("#perform_id").val(rowdata.id);
            $("#process_name").val(rowdata.process);
            $('#process').html('<span>Process: '+rowdata.process+'</span>');
        } else {
            var rowdata = performanceTable.row(this).data();
//            performanceTable.$('tr.selected').removeClass('selected');
            $("#perform_id").val(rowdata.id);
            $("#process_name").val(rowdata.process);
            $('#process').html('<span>Process: '+rowdata.process+'</span>');
//            $(this).addClass('selected');
        }
        getPerformanceData();
    });
    $("#performance_searchbox").keyup(function () {//group search code
        performanceTable.search(this.value).draw();
    });
}

function selectConfirm(data_target_id) {

    if (data_target_id == 'export_excel') {
        excelExport();
    } else if (data_target_id == 'details') {
        showPerformanceDetails();
    } else if(data_target_id == 'network_stat') {
        $('#performanceDiv').hide();
         $('#process').hide();
        $('#performanceGraph').show();
        $('.month-dropdown').hide();
        $('#back').show();
        $('#details').hide();
        $('#statistics').hide();
        $('#export').hide();
        $('#export_stat').show();
        getCulmulativeList();
    } else if(data_target_id == 'back') {
        $('#performanceDiv').show();
        $('#performanceGraph').hide();
        $('.month-dropdown').show();
        $('#process').show();
        $('#back').hide();
        $('#details').show();
        $('#statistics').show();
        $('#export').show();
        $('#export_stat').hide();
        Performance_datatablelist();
    } else if(data_target_id == 'export_month_excel') {
        excelMonthlyExport();
    }
}

function showPerformanceDetails() {

    var performVal = $('#perform_id').val();
    var processName = $("#process_name").val();
    $('#process_det').html(processName);
//    $(".se-pre-con").show();
    $('#performanceDetails').modal('show');
    $.ajax({
        url: "networkfunction.php?function=getPerformanceDetail",
        type: "POST",
        dataType: "json",
        data: {
            process: processName,
            'csrfMagicToken': csrfMagicToken},
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#performanceDetailsTable').DataTable().destroy();
            performanceTableDetail = $('#performanceDetailsTable').DataTable({
                scrollY: jQuery('#performanceDetailsTable').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: false,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo:false,
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
                initComplete: function (settings, json) {
                },
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
//                    $(".se-pre-con").hide();
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });

}
function viewdetailpopupclicked() {
    setTimeout(function () {
        $(".event-info-grid-host").click();
    }, 300);
}

function excelExport(){
    var monthYear   = $('#monthDropDown').val();

    window.location.href = '../network/networkfunction.php?function=exportPerformanceData&month='+monthYear;
}

function excelMonthlyExport() {
    window.location.href = '../network/networkfunction.php?function=exportPerformanceMonthlyData';

}

function getCulmulativeList() {

    var searchType =  $('#searchType').val();
    var searchVal = $('#searchValue').val();
    var rparent   = $('#rparentName').val();
    var name = rparent.split("__");

    if(searchType == 'Groups') {
        $('#heading').html('<span>Network Monthly Report : '+name[0]+'</span>');
    } else if(searchType == 'Sites') {
        var name = searchVal.split("__");
        $('#heading').html('<span>Network Monthly Report : '+name[0]+'</span>');
    }else {
        $('#heading').html('<span>Network Monthly Report : '+searchVal+'</span>');
    }

    $.ajax({
        url: "networkfunction.php?function=getCumulativeGraphData",
        type: "POST",
        dataType: "json",
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#cumulativeNetworkList').DataTable().destroy();
            cumulativeTable = $('#cumulativeNetworkList').DataTable({
                scrollY: jQuery('#cumulativeNetworkList').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo:false,
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
                initComplete: function (settings, json) {
                    var month = $('#cumulativeNetworkList tbody tr:eq(0) p')[0].id;
                    $('#cumulativeNetworkList tbody tr:eq(0)').addClass("selected");
                    $("#month_selected").val(month);
                    getPerformanceTrends();
                },
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });
    $('#cumulativeNetworkList').on('click', 'tr', function() {
        //row selection code
        cumulativeTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        if ($(this).hasClass('selected')) {
            var rowdata = cumulativeTable.row(this).data();
            $("#month_selected").val(rowdata.month);
        } else {
            var rowdata = cumulativeTable.row(this).data();
            cumulativeTable.$('tr.selected').removeClass('selected');
            $("#month_selected").val(rowdata.month);
            $(this).addClass('selected');
        }
        getPerformanceTrends();
    });
    $("#performance_searchbox").keyup(function () {//group search code
        cumulativeTable.search(this.value).draw();
    });
}

function getPerformanceTrends() {
    google.charts.load("current", {packages: ["corechart", 'bar']});
    google.charts.setOnLoadCallback(drawPerformanceChart);
}

function drawPerformanceChart() {
    var searchType  = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var month   = $('#month_selected').val();
    var graphArray  = createGraphArray(searchType, searchValue,month);
    var data = google.visualization.arrayToDataTable(graphArray);
    var options = {
        title: '',
        chartArea: {left: 0, top: 0, width: '100%', height: '100%', backgroundColor: '#ffffff', },
        legend: {position: 'none'},
        bar: {groupWidth: "10%"},
        series: {
            0: {color: '#48b2e4'},
            1:{color:'#F7C039'},
        },
        isStacked: true
    };
    var chart = new google.charts.Bar(document.getElementById('columnchart'));
    chart.draw(data, google.charts.Bar.convertOptions(options));
}

function createGraphArray(searchType, searchValue,month){
    var resultObject;
//    var data = "function=getIncidentReportCounts&searchType=" + searchType + "&searchValue=" + searchValue + "&monthYear=" + monthYear;
//    var encodedData = get_RSA_EnrytptedData(data);

    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "networkfunction.php?function=getPerformanceGraphData",
        data: {
            searchType: searchType, searchValue: searchValue, month: month,
            'csrfMagicToken': csrfMagicToken},
        async: false,
        success: function(result) {
            resultObject = result;
        }
    });

    var incidentArray  = [['', 'TcpIn','TcpOut', 'UdpIn', 'UdpOut']];
    var i = 1;
    for (var key in resultObject) {
        if (resultObject.hasOwnProperty(key)) {
            incidentArray[i] = [key,parseInt(resultObject[key]['TcpIn']),parseInt(resultObject[key]['TcpOut']),
                        parseInt(resultObject[key]['UdpIn']),parseInt(resultObject[key]['UdpOut'])];
            i++;
        }
    }
    return incidentArray;
}
