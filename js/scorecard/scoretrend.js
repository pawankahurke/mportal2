$(function () {
    setTimeout(function () {
        $('#submenu_insight').mCustomScrollbar('scrollTo', "#scorereport_Insight_lm");
    }, 1000);
    trendType = 'daily';
    $('#scorereport_Insight_lm').children('a').addClass('active');
    scoretrendfunction();
});

function scoretrendfunction() {
    
    getscorecardIncident();
    scorecardGridData();
}

function getscorecardIncident() {
    google.charts.load("current", {packages: ["corechart", 'bar']});
    google.charts.setOnLoadCallback(drawLineChart);
}

function drawStuff() {
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var graphArray = createGraphArray(searchType, searchValue);

    var data = google.visualization.arrayToDataTable(graphArray);
    var options = {
        title: '',
        chartArea: {left: 0, top: 0, width: '100%', height: '100%', backgroundColor: '#ffffff', },
        legend: {position: 'none'},
        vAxis: {
            minValue: 1
        },
        backgroundColor: '#ffffff',
        colors: ['#48b2e4', '#62d433'],
        bar: {groupWidth: "10%"},
        series: {
            0: {color: '#48b2e4'},
        },
        isStacked: true,
    };
    var chart = new google.charts.Bar(document.getElementById('chart_div'));
    chart.draw(data, google.charts.Bar.convertOptions(options));
}

function drawLineChart() {
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var graphArray = createGraphArray(searchType, searchValue);
    
    var data = google.visualization.arrayToDataTable(graphArray);

    var options = {
        vAxis: {
          title: 'Score',
          maxValue: 4
        },
        pointSize: 3,
        //title: 'Scorecard Trend',
        //curveType: 'function',
        legend: {position: 'right'}
    };

    var chart = new google.visualization.LineChart(document.getElementById('chart_div'));

    chart.draw(data, options);
}

function createGraphArray(searchType, searchValue) {
    var resultObject = [['date', 'score', 'max']];
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "scorecardELFunction.php",
        data: "function=scoretrendgraphDetails&searchType=" + searchType + "&searchValue=" + searchValue + "&trendType=" + trendType +"&csrfMagicToken=" + csrfMagicToken,
        async: false,
        success: function (result) {
            //console.log(result);
            var i = 1;
            for (var key in result) {
                if (result.hasOwnProperty(key)) {
                    resultObject[i] = [key, result[key].score, result[key].max];
                    i++;
                }
            }
        }
    });
    return resultObject;
}

function scorecardGridData() {
    $.ajax({
        url: "scorecardELFunction.php?function=get_scoregridData&trendType=" + trendType +"&csrfMagicToken=" + csrfMagicToken,
        type: "POST",
        dataType: "json",
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#scorecardTable').DataTable().destroy();
            groupTable = $('#scorecardTable').DataTable({
                scrollY: jQuery('#scorecardTable').data('height'),
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
                columnDefs: [{className: "checkbox-btn", "targets": [0]},
                    {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                initComplete: function (settings, json) {
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
            console.log(msg);
        }
    });
}

function selectConfirm(value) {
    if (value == 'scoreTrendExport') {
        exportScoreTrendData();
    }
}

function exportScoreTrendData() {
    window.location.href = 'scorecardELFunction.php?function=get_scoreTrendExport&trendType='+trendType +"&csrfMagicToken=" + csrfMagicToken;
}

function updateTrendDetails(ref) {
    trendType = $(ref).val();
    scoretrendfunction();
}
