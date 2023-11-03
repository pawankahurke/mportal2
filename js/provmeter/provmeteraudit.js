$(function() {
    provmeterAudit();
})

function provmeterAudit() {
    $.ajax({
        url: "provmeterfunction.php?function=get_provmeterauditList"+"&csrfMagicToken=" + csrfMagicToken,
        type: "POST",
        dataType: "json",
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#provproductGrid').DataTable().destroy();
            groupTable = $('#provproductGrid').DataTable({
                scrollY: jQuery('#provproductGrid').data('height'),
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

        }
    });
    
    $("#provmeterreport_searchbox").keyup(function () {//group search code
        groupTable.search(this.value).draw();
    });
}