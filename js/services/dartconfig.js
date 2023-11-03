// iOS Configuration

$(function () {
    var configType = $('#configType').val();
    if (configType === 'basic') {
        loadBasicConfigScrip();
    } else {
        loadiOSConfigScrip();
    }
});

function loadBasicConfigScrip() {
    $.ajax({
        url: "scrip_list.php"+"&csrfMagicToken=" + csrfMagicToken,
        type: "POST",
        dataType: "json",
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#dartListGrid').DataTable().destroy();
            groupTable = $('#dartListGrid').DataTable({
                scrollY: jQuery('#dartListGrid').data('height'),
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
//                    $(".dataTables_scrollBody").mCustomScrollbar({
//                        theme: "minimal-dark"
//                    });
                    //$('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });

    $('#dartListGrid').on('click', 'tr', function () {

        var rowID = groupTable.row(this).data();
        var selected = rowID[6];
        $('#selected').val(selected);

        groupTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });

    $("#dartListGrid_searchbox").keyup(function () {//group search code
        groupTable.search(this.value).draw();
    });
    
    $('#dartListGrid').DataTable().search( '' ).columns().search( '' ).draw();
}

function loadiOSConfigScrip() {
    $.ajax({
        url: "iosScrip.php",
        type: "POST",
        dataType: "json",
        data: {
            osType:2,
            csrfMagicToken: csrfMagicToken
        },
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#iosconfigGrid').DataTable().destroy();
            groupTable = $('#iosconfigGrid').DataTable({
                scrollY: jQuery('#iosconfigGrid').data('height'),
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
                    searchPlaceholder: "Search records"
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
//                    $(".dataTables_scrollBody").mCustomScrollbar({
//                        theme: "minimal-dark"
//                    });
                    //$('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });

    $('#iosconfigGrid').on('click', 'tr', function () {
        var rowID = groupTable.row(this).data();
        var selected = rowID[6];
        $('#selected').val(selected);
        $('#srcpname').val(rowID[3]);
        $('#srcpnum').val(rowID[2]);
        groupTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });

    $("#iosconfig_searchbox").keyup(function () {//group search code
        groupTable.search(this.value).draw();
    });
    
    $('#iosconfigGrid').DataTable().search( '' ).columns().search( '' ).draw();
}

function syncIos() {
    $.ajax({
        url: 'dartFunc.php?function=getDartDetails'+"&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        dataType : "json",
        success: function (result) {
                    console.log(result);
                    var details = JSON.stringify(result);
                    $.notify("Configuration sync has started");
            $.ajax({
                url: syncurl+"&csrfMagicToken=" + csrfMagicToken,
                type: 'POST',
                data: details,
                success: function (data) {

                }
            });
        }
   });
}


function hide_show(scrpnum,scrpname){
    $('#hide_div').hide();
    $('#show_div').show();
    $('#btn_div').hide();
    getHtmlContentios(scrpnum,scrpname);
}