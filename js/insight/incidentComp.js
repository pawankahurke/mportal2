//This function fills the Table grid
$(document).ready(function(){
    //getSummaryReportInfo();
 });

$("#reportDate").change(function(){
   getSummaryReportInfo();
});

var text = ' ';
var count = 0;
function getSummaryReportInfo() {
    var searchType  = $("#searchType").val();
    var searchValue = $("#searchValue").val();
    var reportDate  = $("#reportDate").val(); 
    $(".se-pre-con").show();
    if(count != 0){
        summaryInfoTable.clear().draw();
    }
    $.ajax({
        type: "POST",
        url: "../lib/l-ajax.php",
        dataType: 'json',
        data: 'function=AJAX_GetSummaryRprtData&searchType=' + searchType + '&searchValue=' + searchValue + '&reportDate=' + reportDate + '&csrfMagicToken=' + csrfMagicToken,
        success: function(gridData) {  
            if(gridData.length == 0){
                text = 'No Data Available';
            }
            else{                
                text = ' ';
            }	
            $(".se-pre-con").hide();
            $("#graphData").val(gridData['graphData']);
            google.charts.load("current", { packages: ["corechart", 'bar'] });
            google.charts.setOnLoadCallback(drawStuff);                        
            $('#summaryReportGrid').DataTable().destroy();
            summaryInfoTable = $('#summaryReportGrid').DataTable({
                scrollY: jQuery('#summaryReportGrid').data('height'),
                scrollCollapse: true,
                autoWidth: false,
                aaData: gridData['gridData'],
                bAutoWidth: true,
                responsive: true,
                
                order: [[0, "asc"]],
                ordering: true,         
                columnDefs: [{
                        targets: "datatable-nosort",
                        orderable: false
                    },
                    {
                        className: "discription", "targets": 0
                    }
                ],
                "lengthMenu":[[10,25,50,75,100],[10,25,50,75,100]],
                drawCallback: function(settings) {
                },
                language: {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    emptyTable: "No data available in table"
                },
                dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                paging : true,
                serverSide: false  

            });
            
        },
        error: function(msg) {

        }

    });
    $('#summaryReportGrid tbody').on('click', 'tr', function() {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        }
        else {
            summaryInfoTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });
    $("#complianceTrend_searchbox").keyup(function() {
        summaryInfoTable.search(this.value).draw();
    });
}

function summaryExport(){
    var reportDate  = $("#reportDate").val(); 
    window.location.href = '../lib/l-ajax.php?function=AJAX_GetSummaryRprtExprtData&reportDate='+reportDate;
}