
function getNotificationIncidents(){
    google.charts.load("current", {packages: ["corechart", 'bar']});
    google.charts.setOnLoadCallback(drawStuff);
}


function drawStuff() {
    var searchType  = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var monthYear   = $('#monthDropDown').val();
    var graphArray  = createGraphArray(searchType, searchValue, monthYear);
    
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
    var chart = new google.charts.Bar(document.getElementById('columnchart'));
    chart.draw(data, google.charts.Bar.convertOptions(options));
    incidentGrid();
    $('#incidentTable').DataTable().columns.adjust().draw();    //Column names and respective data were distracting. so this line inserted
    
}

/**
 * Returns array of count in format which is required for google chart.
 * It will fetch data from db and then return formatted array to render data on UI.
 * @param {string} searchType Site level/Group level.
 * @param {string} searchValue  Site name/Group name.
 * @param {string} monthYear    Selected month from drop down on UI.
 * @returns {Array}
 */
function createGraphArray(searchType, searchValue, monthYear){
    var resultObject;
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "../notifications/notification.php",
        data: "function=getIncidentReportCounts&searchType=" + searchType + "&searchValue=" + searchValue + "&monthYear=" + monthYear + '&csrfMagicToken=' + csrfMagicToken,
        async: false,
        success: function(result) {
            resultObject = result; 
        }
    });
    
    var incidentArray  = [['', 'Pending','Actioned', 'Fixed']];
    var i = 1;
    for (var key in resultObject) {
        if (resultObject.hasOwnProperty(key)) {
            incidentArray[i] = [key,parseInt(resultObject[key]['Pending']),parseInt(resultObject[key]['Actioned']),parseInt(resultObject[key]['Fixed'])];
            i++;
        }
    }
    return incidentArray;
}




function incidentGrid() {
    var searchType  = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var monthYear   = $('#monthDropDown').val();
    $('#incidentTable').dataTable().fnDestroy();
    incidentTable = $('#incidentTable').DataTable({
        scrollY: jQuery('#incidentTable').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        paging: true,
        searching: true,
        processing: true,
        serverSide: false,
        ordering: true,
        select: true,
        bInfo: false,
        responsive: true,
        ajax: {
            url: "../notifications/notification.php?function=getIncidentReportGrid&searchType=" + searchType + "&searchValue=" + searchValue + "&monthYear=" + monthYear,
            type: "POST",
            data: { 'csrfMagicToken': csrfMagicToken }
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        columns: [
            {"data": "day"},
            {"data": "type"},
            {"data": "device"},
            {"data": "issue"},
            {"data": "status"}
        ],
        columnDefs: [
            {
                className: "checkbox-btn", "targets": [0]
            },
            {
                targets: "datatable-nosort",
                orderable: false
            }
        ],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
        },
        drawCallback: function(settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
            $(".se-pre-con").hide();
        },
    });
    $("#incidentTrend_searchbox").keyup(function() {
        incidentTable.search(this.value).draw();
    });
}

//On resizing of browser window, this code will triggered
jQuery(window).bind('load resize', function() {
//    drawStuff();
});

$("#notifIncidentExport").click(function(){
    var searchType  = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var monthYear   = $('#monthDropDown').val(); 
    window.location.href = "../notifications/notification.php?function=exportNotifIncidentReport&searchType=" + searchType + "&searchValue=" + searchValue + "&monthYear=" + monthYear;
});