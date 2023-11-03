function getScoreTrends(){
    getScoreTrendData();
    getScoreTrendGraph();    
}

function getScoreTrendGraph(){
    
    google.charts.load("current", {packages: ["corechart", 'bar']});
    google.charts.setOnLoadCallback(drawStuff);

}

function drawStuff() {
    var searchType  = $('#searchType').val();
    var searchValue = $('#searchValue').val();     
    var graphArray  = createGraphArray(searchType, searchValue);
//    console.log(graphArray);
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
//    incidentGrid();
//    $('#incidentTable').DataTable().columns.adjust().draw();    //Column names and respective data were distracting. so this line inserted
    
}

function createGraphArray(searchType,searchValue) {    
    var resultObject = [['', 'score']];
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "scorefunction.php",
        data: "function=scoretrendgraphDetails&searchType=" + searchType + "&searchValue=" + searchValue + '&csrfMagicToken=' + csrfMagicToken ,
        async: false,
        success: function(result) {
            console.log(result);
            var i = 1; 
            for (var key in result) {
                if(result.hasOwnProperty(key)) {
                    resultObject[i] = [key,parseInt(result[key].score)];
                    i++;
                }
            }
        }
    });
    return resultObject;
}

function getScoreTrendData() {
    var val = $('#searchValue').val();
    $('#headerName').html(val);
   $.ajax({
       url:'scorefunction.php?function=scoretrenddata',
       type:'post',
       dataType:'json',
       success:function(data) {
           var tableArray = [];
           for(var id in data.table) {
               tableArray.push([data.table[id].scorename,data.table[id].weightage]);
           }
           loadResultTable(tableArray);           
           drawChart(data.total);
       }
   }) 
}

function loadResultTable(tableData) {
        var callTable = $('#scoretrenddetails').DataTable({
                scrollY: '30vh',
                scrollCollapse: true,
                paging: false,
                searching: false,
                ordering: true,
                aaData: tableData,
                bAutoWidth: true,
                select: false,
                bInfo: false,
                bDestroy: true,
                responsive: true,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function (settings, json) {                    
                },
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
            });            
                    $('.bottom').hide();
    }
            });            
    }

function drawChart(graphData) {

    var s1 = [graphData];

    var plot3 = $.jqplot('summaryGraph', [s1], {
        seriesDefaults: {
            renderer: $.jqplot.MeterGaugeRenderer,
            rendererOptions: {
                label: 'OverAll Score : ' + s1 + '%',
                labelPosition: 'bottom',
                min: 0,
                max: 100,                
                intervals: [graphData,100],
                intervalColors: ['#66cc66', '#cc6666']
            }
        },
        grid: {
            drawBorder: false,
            drawGridlines: false,
            background: '#ffffff',
            shadow: false
        }
    });
}