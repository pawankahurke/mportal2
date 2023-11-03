function Network_datatablelist() {    
    var searchVal = $('#searchValue').val();
    var passlevel = $('#passlevel').val();   
    var seachlabel = $('#searchLabel').val();
    
    if (passlevel == 'Sites') {
       $('#heading').html('<span>Network Performance: '+searchVal+'</span>');
    } else if (passlevel == 'Groups') {
        $('#heading').html('<span>Network Performance: '+seachlabel+'</span>');
    }
    $.ajax({
        url: "networkfunction.php?function=getnetworkData",
        type: "POST",
        dataType: "json",
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#networkdataList').DataTable().destroy();
            groupTable = $('#networkdataList').DataTable({
                scrollY: jQuery('#networkdataList').data('height'),
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
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, 
//                             { "width": "30%", "targets": 1 },   
//                             { "width": "30%", "targets": 2 },   
                    {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                initComplete: function (settings, json) {                    
                    var url = $('#networkdataList tbody tr:eq(0) p')[1].id;
                    $('#networkdataList tbody tr:eq(0)').addClass("selected");
                    $("#url").val(url);
//                    console.log($('#networkdataList tbody tr:eq(0)')[0]['nextChild']['innerText']);
//                    $('#process').html('<span>Process: '+url+'</span>');
                    getTrafficResponse();
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

    $('#networkdataList').on('click', 'tr', function () {

        groupTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        if ($(this).hasClass('selected')) {
            var rowdata = groupTable.row(this).data();
            $("#url").val(rowdata.text);
//            $('#process').html('<span>Process: '+rowdata.process+'</span>');
        } else {
            var rowdata = groupTable.row(this).data();
            groupTable.$('tr.selected').removeClass('selected');
            $("#url").val(rowdata.text);
//            $('#process').html('<span>Process: '+rowdata.process+'</span>');
            $(this).addClass('selected');
        }
        getTrafficResponse();


    });

    $("#network_searchbox").keyup(function () {//group search code
        groupTable.search(this.value).draw();
    });
}

function selectConfirm(data_target_id) {
    
    if (data_target_id == 'export_excel') {
        get_excelExport();
    } else if(data_target_id == 'average_response') {
        $('#networkDiv').hide();
        $('#averageDiv').show();
         $('#errorDiv').hide();
        $('#back').show();
        averageResponse_list();
    } else if(data_target_id == 'back') {
        $('#errorDiv').hide();
        $('#networkDiv').show();
        $('#averageDiv').hide();
        $('#back').hide();
        Network_datatablelist();
    } else if(data_target_id == 'error_response') {
        $('#errorDiv').show();
        $('#networkDiv').hide();
        $('#averageDiv').hide();
        $('#back').show();
        network_errorCodeList();
    }
}

/* network excel function */
function get_excelExport(){
    window.location.href = '../network/networkfunction.php?function=exportExcelData';
}

function getTrafficResponse() {
    google.charts.load("current", {packages: ["corechart", 'bar']});
    google.charts.setOnLoadCallback(drawNetworkChart);
}

function drawNetworkChart() {
    
    var searchType  = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var url         = $('#url').val();
    
     var graphArray  = createArray(searchType, searchValue, url);
    
    var data = google.visualization.arrayToDataTable(graphArray);
//    data.addColumn('timeofday', 'Time of Day');
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
        vAxis: {
            title: 'Response time (in seconds)'
        }
    };
    var chart = new google.charts.Bar(document.getElementById('columnchart'));
    chart.draw(data, google.charts.Bar.convertOptions(options));
}

function createArray(searchType, searchValue, url) {
    
    var resultObject;
    
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "networkfunction.php?function=getNetworkTrafficGraph",
        data: {
            searchType: searchType, searchValue: searchValue, url: url,
            'csrfMagicToken': csrfMagicToken},
        async: false,
        success: function(result) {
            resultObject = result; 
        }
    });
    
    var incidentArray  = [['', 'Time']];
    var i = 1;
   for (var key in resultObject) {
        var timeSec = 0;
        var Sec = 0;
        if (resultObject.hasOwnProperty(key)) {
            var time = resultObject[key]['time'];
            if(time === 0) {
               
            } else {
                timeSec= time.replace("msec","");
                Sec = (timeSec/1000) % 60;
            }
            if(Sec > 0) {
              incidentArray[i] = [key,parseFloat(Sec).toFixed(3)];
            } else {
                incidentArray[i] = [key,parseInt(Sec)];
            }
            i++;
        }
    }
    return incidentArray;
}

function averageResponse_list() {
    
    var searchVal = $('#searchValue').val();
    var passlevel = $('#passlevel').val();   
    var seachlabel = $('#searchLabel').val();
    
    if (passlevel == 'Sites') {
       $('#heading').html('<span>Network Average Performance: '+searchVal+'</span>');
    } else if (passlevel == 'Groups') {
        $('#heading').html('<span>Network  Average Performance: '+seachlabel+'</span>');
    }
    $.ajax({
        url: "networkfunction.php?function=getAverageNetworkData",
        type: "POST",
        dataType: "json",
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#averageResponseList').DataTable().destroy();
            networkTable = $('#averageResponseList').DataTable({
                scrollY: jQuery('#averageResponseList').data('height'),
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
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, 
                    {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                initComplete: function (settings, json) {
                    var url = $('#averageResponseList tbody tr:eq(0) p')[1].id; 
                    $('#averageResponseList tbody tr:eq(0)').addClass("selected");
                    $("#avgUrl").val(url);
                    getAverageTrafficResponse();
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

    $('#averageResponseList').on('click', 'tr', function () {

        networkTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        if ($(this).hasClass('selected')) {
            var rowdata = networkTable.row(this).data();
            $("#avgUrl").val(rowdata.text);
        } else {
            var rowdata = networkTable.row(this).data();
            networkTable.$('tr.selected').removeClass('selected');
            $("#avgUrl").val(rowdata.text);
            $(this).addClass('selected');
        }
        getAverageTrafficResponse();
    });

    $("#network_searchbox").keyup(function () {//group search code
        networkTable.search(this.value).draw();
    });
    
}

function getAverageTrafficResponse() {
    google.charts.load("current", {packages: ["corechart", 'bar']});
    google.charts.setOnLoadCallback(drawAverageNetworkChart);
}

function drawAverageNetworkChart() {
    
    var searchType  = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var url         = $('#avgUrl').val();
    
     var graphArray  = createNetworkArray(searchType, searchValue, url);
    
    var data = google.visualization.arrayToDataTable(graphArray);
//    data.addColumn('timeofday', 'Time of Day');
    var options = {
        title: '',
        chartArea: {left: 0, top: 0, width: '50%', height: '100%', backgroundColor: '#ffffff', },
        legend: {position: 'none'},
        backgroundColor: '#ffffff',
        colors: ['#48b2e4', '#62d433'],
        bar: {groupWidth: "10%"},
        series: {
            0: {color: '#48b2e4'},
        },
        isStacked: true,
        vAxis: {
            title: 'Response time (in seconds)'
        }
    };
    var chart = new google.charts.Bar(document.getElementById('averageChart'));
    chart.draw(data, google.charts.Bar.convertOptions(options));
}

function createNetworkArray(searchType, searchValue, url) {
    
    var resultObject;
    
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "networkfunction.php?function=getAverageNetworkGraph",
        data: {
            searchType: searchType, searchValue: searchValue, url: url,
            'csrfMagicToken': csrfMagicToken},
        async: false,
        success: function(result) {
            resultObject = result; 
        }
    });
    
    var incidentArray  = [['', 'Time']];
    var i = 1;
    for (var key in resultObject) {
        var timeSec = 0;
        var Sec = 0;
        if (resultObject.hasOwnProperty(key)) {
            var time = resultObject[key]['time'];
            if(time === 0) {
               
            } else {
                timeSec= time.replace("msec","");
                Sec = (timeSec/1000) % 60;
            }
//            if(Sec > 0) {
//              incidentArray[i] = [key,parseFloat(Sec).toFixed(2)];
//            } else {
                incidentArray[i] = [key,parseInt(Sec)];
//            }
            i++;
        }
    }
    return incidentArray;
}

function network_errorCodeList() {
  
    var searchVal = $('#searchValue').val();
    var passlevel = $('#passlevel').val();   
    var seachlabel = $('#searchLabel').val();
    
    if (passlevel == 'Sites') {
       $('#heading').html('<span>Network Average Performance: '+searchVal+'</span>');
    } else if (passlevel == 'Groups') {
        $('#heading').html('<span>Network  Average Performance: '+seachlabel+'</span>');
    }
    $.ajax({
        url: "networkfunction.php?function=getErrorNetworkData",
        type: "POST",
        dataType: "json",
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#errorResponseList').DataTable().destroy();
            networkErrorTable = $('#errorResponseList').DataTable({
                scrollY: jQuery('#errorResponseList').data('height'),
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
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, 
                    {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                initComplete: function (settings, json) {
                    var url = $('#errorResponseList tbody tr:eq(0) p')[1].id; 
                    $('#errorResponseList tbody tr:eq(0)').addClass("selected");
                    $("#errorurl").val(url);
                    getErrorTrafficResponse();
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

    $('#errorResponseList').on('click', 'tr', function () {

        networkErrorTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        if ($(this).hasClass('selected')) {
            var rowdata = networkErrorTable.row(this).data();
            $("#errorurl").val(rowdata.text);
        } else {
            var rowdata = networkErrorTable.row(this).data();
            networkErrorTable.$('tr.selected').removeClass('selected');
            $("#errorurl").val(rowdata.text);
            $(this).addClass('selected');
        }
        getErrorTrafficResponse();
    });

    $("#network_searchbox").keyup(function () {//group search code
        networkErrorTable.search(this.value).draw();
    });
    
}

function  getErrorTrafficResponse() {
    
    google.charts.load("current", {packages: ["corechart", 'bar']});
    google.charts.setOnLoadCallback(drawErrorNetworkChart);
    
}

function drawErrorNetworkChart() {
    
    var searchType  = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var url         = $('#errorurl').val();
    
     var graphArray  = createErrorArray(searchType, searchValue, url);
    
    var data = google.visualization.arrayToDataTable(graphArray);
//    data.addColumn('timeofday', 'Time of Day');
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
        vAxis: {
            title: 'Response time (in seconds)'
        }
    };
    var chart = new google.charts.Bar(document.getElementById('errorChart'));
    chart.draw(data, google.charts.Bar.convertOptions(options));
}

function createErrorArray(searchType, searchValue, url) {
    
    var resultObject;
    
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "networkfunction.php?function=getNetworkErrorGraph",
        data: {
            searchType: searchType, searchValue: searchValue, url: url,
            'csrfMagicToken': csrfMagicToken},
        async: false,
        success: function(result) {
            resultObject = result; 
        }
    });
    
    var incidentArray  = [['', 'Time']];
    var i = 1;
  
    for (var key in resultObject) {
        var timeSec = 0;
        var Sec = 0;
        if (resultObject.hasOwnProperty(key)) {
            var time = resultObject[key]['time'];
            if(time === 0) {
               
            } else {
                timeSec= time.replace("msec","");
                Sec = (timeSec/1000) % 60;
            }
            if(Sec > 0) {
                incidentArray[i] = [key,parseFloat(Sec).toFixed(3)];
            } else {
                incidentArray[i] = [key,parseInt(Sec)];
            }
              
            i++;
        }
    }
    return incidentArray;
}
