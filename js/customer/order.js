/**
 * This file belongs to "Commercial/MSP" Orders only.
 * In this file "msp" indicates "managed service provider" or "Commercial" bussiness flow.
 */

$(document).ready(function () {
    get_CommercialOrders();
    
    //inline css display none property converted
//    $("#successProvisionButtonsVal").hide();
//    $("#successCommercial").hide();
});

/**
 * Fetch all customers list in json format which is required for Datatable.
 */
function get_CommercialOrders(){
    
    $('#msp_Order_Grid').dataTable().fnDestroy();
    customerGrid = $('#msp_Order_Grid').DataTable({
//        scrollY: jQuery('#msp_Order_Grid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        serverSide: false,
        searching: true,
        bAutoWidth: true,
        responsive: true,
        ordering: true,
        stateSave: true,
        scrollY: 'calc(100vh - 240px)',
        "pagingType": "full_numbers",
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false
            },{
                className: "ignore", targets: [0, 1, 2, 3]
            }],
        ajax: {
            url: "../lib/l-msp.php?function=MSP_GetOrdersGrid",
            type: "POST"
        },
        columns: [
            {"data": "order"},
            {"data": "purchaseDate"},
            {"data": "expireDate"},
            {"data": "used_total"},
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        
        "language": {
            search: "_INPUT_",
            searchPlaceholder: "Search records",
        },
        "dom": '<"top"f>rt<"bottom"lp><"clear">',
        initComplete: function(settings, json) {
           //triggers when datatable completely load.
        },
        /* drawCallback: function(settings) {
                $(".dataTables_scrollBody").mCustomScrollbar({
                    theme: "minimal-dark"
                });
        } */
    });
    
    $("#myaccount_searchbox").keyup(function() {
        customerGrid.search(this.value).draw();
    });
    
    $('#msp_Customer_Grid tbody').on('click', 'tr', function() {
        customerGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });

}

$("#exportAllOrders").click(function(){
    location.href='../lib/l-msp.php?function=MSP_ExportAllOrders';
    closePopUp();
})

$("#buy_more").click(function(){
    window.open('https://nanoheal.com/pricing/');
    closePopUp();
})


//###################################### Boostrap Modal CLOSE/OPEN Events Start ################################################//




//###################################### Boostrap Modal CLOSE/OPEN Events End ################################################//