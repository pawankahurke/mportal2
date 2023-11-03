$(function () {
    setTimeout(function () {
        $('#submenu_insight').mCustomScrollbar('scrollTo', "#scorereport_Insight_lm");
    }, 1000);

    repDate = $('#reportdate').val();

    scoreReportChart(); // Pie Chart Renderer
    scorereportGrid();
    //scoreDetails();

})

function scoreReportChart() {
    google.charts.load('current', {'packages': ['corechart']});
    google.charts.setOnLoadCallback(drawReportChart);
}

function drawReportChart() {
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var scoreArray = createScoreDetails(searchType, searchValue);

    var data = google.visualization.arrayToDataTable(scoreArray);
    var options = {
        title: '',
        chartArea: {left: 100, top: 0, width: '70%', height: '100%'}
    };
    var chart = new google.visualization.PieChart(document.getElementById('reportChart'));
    chart.draw(data, options);
}

function createScoreDetails(searchType, searchValue) {
    var resultObject = [['parameter', 'score']];
    $.ajax({
        type: "POST",
        url: "scorecardELFunction.php",
        data: "function=scoreReportDetails&searchType=" + searchType + "&searchValue=" + searchValue + "&reportDate=" + repDate+"&csrfMagicToken=" + csrfMagicToken,
        async: false,
        success: function (result) {
            var data = JSON.parse(result);
            for (var i = 0; i < data.length; i++) {
                var paramScore = Math.ceil(parseInt(data[i].paramScore)/parseInt(data[i].cnt));
                resultObject[i + 1] = [data[i].paramName, paramScore];
            }
            
            // Score Details Count
            var score = 0;
            var total = 0;
            for (var i = 0; i < data.length; i++) {
                //var paramScoreVal = Math.round(parseInt(data[i].paramScore) / data[i].cnt);
                //if (paramScoreVal > 0) {
                    console.log('Here is Comes ' + parseInt(data[i].paramScore) + '---' + data[i].cnt);
                    score += Math.ceil(parseInt(data[i].paramScore) / data[i].cnt);
                    total += Math.ceil(parseInt(data[i].paramTotal));
                //}
            }
            if(isNaN(score)) {
                score = 0;
            }
            $('.scorevalue').html(score + ' / ' + total);
            $('#reploader').hide();
        }
    });
    return resultObject;
}

function scorereportGrid() {
    //var search = $('#scoreSearch').text();
    //var searchValue = $('#searchValue').val();
    //var searchValueData = searchValue.split('__')[0];
    //$('#scoredetail').text("Scorecard Report : " + searchValueData);

    $.ajax({
        url: "scorecardELFunction.php?function=get_scorereportData&reportDate=" + repDate+"&csrfMagicToken=" + csrfMagicToken,
        type: "POST",
        dataType: "json",
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#scorecreportTable').DataTable().destroy();
            scoreReport = $('#scorecreportTable').DataTable({
                scrollY: jQuery('#scorecreportTable').data('height'),
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
                }
            });
            $('.tableloader').hide();
        },
        error: function (err) {
            console.log(err);
        }
    });

    $("#scorecard_searchbox").keyup(function () {//group search code
        scoreReport.search(this.value).draw();
    });
}

$(".date_format").datetimepicker({
    format: "mm-dd-yyyy",
    autoclose: true,
    todayBtn: false,
    pickerPosition: "bottom-left",
    startDate: "2018-01-01 00:00:00",
    endDate: new Date(),
    minView: 'month'
});

$('#reportdate').on('change', function (e) {
    repDate = $(this).val();
    $('#reploader').show();

    setTimeout(function () {
        scoreReportChart();
        scorereportGrid();
        //scoreDetails();
    }, 500);
});


function selectConfirm(data_target_id) {
    if (data_target_id == 'scoreReportExport') {
        exportreportData();
    }
}

function exportreportData() {
    window.location.href = 'scorecardELFunction.php?function=get_scoreReportExport&reportDate='+repDate+"&csrfMagicToken=" + csrfMagicToken;
}