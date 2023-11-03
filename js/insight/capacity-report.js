//This function fills the Table grid
//$(document).ready(function(){
//    getCapacityReportInfo();
//});


function getCapacityReportInfo() {
   $(".se-pre-con").show();
    $.ajax({
        type: "GET",
        url: "../lib/l-ajax.php",
        data: "function=AJAX_GetCapacityReportData",
        dataType: 'json',
        success: function(msg) {
            $('#capacityReportGrid').DataTable().destroy();
            deviceInfoTable = $('#capacityReportGrid').DataTable({
                scrollY: jQuery('#capacityReportGrid').data('height'),
                scrollCollapse: true,
                autoWidth: false,
                bAutoWidth: true,
                aaData: msg,
                searching: true,
                order: [[0, "asc"]],
                drawCallback: function(settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',

            });
            $(".se-pre-con").hide();
        },
        error: function(msg) {
            alert("error");
        }

    });
    $('#capacityReportGrid tbody').on('click', 'tr', function() {
        if ($(this).hasClass('selected')) {
            $(this).removeClass('selected');
        }
        else {
            deviceInfoTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });
    
    $("#capacity_searchbox").keyup(function () {//group search code
        deviceInfoTable.search(this.value).draw();
    });
}

function capacityexport(){
    window.location.href = '../lib/l-ajax.php?function=AJAX_GetCapacityReportDataExport';
}