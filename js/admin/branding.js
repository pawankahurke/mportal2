// Branding page changes.

$(document).ready(function () {
    //$('#customizeBranding').css({'pointer-events': 'none', 'cursor': 'not-allowed'});
    get_CustomizeConfDetails();
});

function get_CustomizeConfDetails() {
    $('#customizeConfGrid').dataTable().fnDestroy();
    customizeGrid = $('#customizeConfGrid').DataTable({
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
        columnDefs: [{ "type": "date", "targets": [4] },
            {
                targets: "datatable-nosort",
                orderable: false
            }, {
                className: "ignore", targets: [0, 1, 2, 3]
            }],
        ajax: {
            url: "../lib/l-customizeUIFunc.php",
            type: "POST",
            data: {'function': 'get_CustomizeConfDetails', 'csrfMagicToken': csrfMagicToken}
        },
        columns: [
            {"data": "customername", "width": "20%"},
            {"data": "emailid", "width": "20%"},
            {"data": "filename", "width": "20%"},
            {"data": "clientuiconf", "width": "20%"},
            {"data": "lastmodified", "width": "20%"}
        ],
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        "language": {
            "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            search: "_INPUT_",
            searchPlaceholder: "Search records"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function (settings, json) {
            //triggers when datatable completely load.
        }
    });
    $('.dataTables_filter input').addClass('form-control');
    $("#myaccount_searchbox").keyup(function () {
        customizeGrid.search(this.value).draw();
    });

    $('#customizeConfGrid tbody').on('click', 'tr', function () {
        customizeGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });

    $('#customizeConfGrid tbody').on('click', 'tr', function () {
        customizeGrid.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        showCustomizePage();
        //$('#customizeBranding').css({'pointer-events': 'initial', 'cursor': 'pointer'});
    });
}

function showCustomizePage() {
    var selval = $('#customizeConfGrid tbody tr.selected').attr('id');

    if (selval == 'undefined' || selval === undefined) {
        $.notify("No customer to create branding");
    } else {
        if (CheckAcess == 1) {
            $.notify("You don't have the permission needed to make the edits");
            return false;
        } else {
            // Set value into session
            $.ajax({
                url: "../lib/l-customizeUIFunc.php",
                type: 'POST',
                data: {'function': 'set_BrandingConfigName', 'brandingconfval': selval, 'csrfMagicToken': csrfMagicToken},
                success: function (data) {
                    if (data) {
                        location.href = 'customize.php';
                    } else {
                        $.notify('Failed to configure Branding. Please try again');
                    }
                },
                error: function (errorThrown) {
                    console.log(errorThrown);
                }
            });
        }
    }
}