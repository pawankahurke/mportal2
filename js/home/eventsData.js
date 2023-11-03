function eventGriddatalist() {
//    if (machine != '' && site != '') {
//        eventsiteinformationlevel();
//    } else {
//        eventListAllLevel();
//    }
    eventListAllLevel();
}

$(document).ready(function () {

    var eventOptions = '';
    $.ajax({
        type: "POST",
        url: "../lib/l-dynamicReport.php?function=1&functionToCall=getEventFilters&limit=10",
        dataType: 'json',
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function (data) {
            eventOptions = '<option value="">-- Select Event Filters --</option>';
            for (var i = 0; i < data.length; i++) {
                eventOptions += '<option value="' + data[i].etag + '">' + data[i].name + '</option>';
            }
            $('#el-events').html("");
            $('#el-events').html(eventOptions);
            $('.selectpicker').selectpicker('refresh');
        },
        error: function (err) {
            console.log("Err : " + err.toString());
        }
    });

    var scriplist = '';
    $.ajax({
        url: "../lib/l-dynamicReport.php?function=1&functionToCall=getScripList",
        type: 'POST',
        success: function (data) {
            scriplist = '<option value="">-- Select a Scrip --</option>';
            scriplist += data;
            $('#el-dartno').html("");
            $('#el-dartno').html(scriplist);
            $('.selectpicker').selectpicker('refresh');
        },
        error: function (err) {
            console.log('Err : ' + err.toString());
        }
    });
});

/* ======== Event data for a particular machine ========= */
function eventsiteinformationlevel() {
    var search = $("#eventSearchinfo").text();
    $("#replace").text("Event Details : " + search);
    $('#eventsfiltergrid').hide();
    $.ajax({
        url: "sitefunctions.php?function=eventlistData&host=" + machine + '&cust=' + site,
        type: "POST",
        dataType: "json",
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#eventGrid').DataTable().destroy();
            table = $('#eventGrid').DataTable({
                scrollY: jQuery('#eventGrid').data('height'),
                scrollCollapse: true,
                paging: true,
                serverside: true,
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
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
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

   /* $('#eventGrid').on('click', 'tr', function () { //row selection code
        table.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');

        if ($(this).hasClass('selected')) {
            var rowdata = table.row(this).data();
            $("#sel_id").val(rowdata[6]);
        } else {
            var rowdata = table.row(this).data();
            table.$('tr.selected').removeClass('selected');
            $("#sel_id").val(rowdata[6]);
            $(this).addClass('selected');
        }
    });*/

    $("#eventdetail_searchbox").keyup(function () {
        table.search(this.value).draw();
    });

    $('#eventGrid').DataTable().search('').columns().search('').draw();

    $('#eventGrid_filter').hide();
    $('#eventhost').val(machine);
}

/* ====== Event data fro site/machine/group level ====== */
function eventListAllLevel() {

    var search = $("#eventSearchinfo").text();
    $("#replace").text("Event Details : " + search);
    $('#eventsfiltergrid').hide();
    $("#eventGrid").dataTable().fnDestroy();

    $(".se-pre-con").show();
    $.ajax({
        url: "sitefunctions.php?function=eventlistallData",
        type: "POST",
        dataType: "json",
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#eventGrid').DataTable().destroy();
            eventTable = $('#eventGrid').DataTable({
                scrollY: jQuery('#eventGrid').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo: false,
                responsive: true,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                columns: [
                    {"data": "device"},
                    {"data": "desc"},
                    {"data": "eventInfo"},
                    {"data": "scrip"},
                    {"data": "clientTime"},
                    {"data": "serverTime"}
                ],
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                initComplete: function (settings, json) {
                     $("#eventdetail_searchbox").show();
                    $("#eventdetail_searchbox1").hide();
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

    /*$('#eventGrid').on('click', 'tr', function () { //row selection code            
        eventTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        if ($(this).hasClass('selected')) {
            var rowdata = eventTable.row(this).data();
            $("#sel_id").val(rowdata.id);
        } else {
            var rowdata = eventTable.row(this).data();
            eventTable.$('tr.selected').removeClass('selected');
            $("#sel_id").val(rowdata.id);
            $(this).addClass('selected');
        }
    });*/

    $("#eventdetail_searchbox").keyup(function () {
        eventTable.search(this.value).draw();
    });
}

function backtosites() {
    window.location.href = '../home/site.php';
}

/* ======== event sites (machines) export code ======= */
function exporteventlist() {

    window.location.href = 'sitefunctions.php?function=eventexportlist&host=' + machine + '&cust=' + site;
}

/* ======= Event detail list ======= */
function eventdetils() {
    var eid = $('#sel_id').val();
    if (eid == '') {
        $('#warning').modal('show');
    } else {
        $.ajax({
            url: "sitefunctions.php?function=eventDetailPopup&eid=" + eid,
            type: "POST",
            dataType: "json",
            data: { 'csrfMagicToken': csrfMagicToken },
            async: true,
            success: function (data) {
                $('#eventdetailpopup').modal('show');
                $('#site').html(data.site);
                $('#machine').html(data.machine);
                $('#clientversion').html(data.clientversion);
                $('#cltime').html(data.cltime);
                $('#setime').html(data.setime);
                $('#uuid').html(data.uuid);
                $('#uname').html(data.uname);
                $('#priority').html(data.priority);
                $('#desc').html(data.desc);
                $('#scripno').html(data.scripno);
                $('#type').html(data.type);
                $('#exec').html(data.exec);
                $('#version').html(data.version);
                $('#size').html(data.size);
                $('#uid').html(data.uid);
                $('#string1').html(data.string1);
                $('#string2').html(data.string2);
                $('#clsize').html(data.clsize);
                $('#path').html(data.path);
                $('#text1').html(data.text1);
                $('#text2').html(data.text2);
                $('#text3').html(data.text3);
                $('#text4').html(data.text4);
            }
        });
    }
}

/* ====== Event query filter code ====== */
function EventfilterClick() {
//    $('.main-class-hide').show();
//    $('#eventsearchfilterhide').show();
    var searchstring = $('#sel_searchstring').val();
    var devmon = $('#devicemonth').val();
    var devday = $('#deviceday').val();
    var devyr = $('#deviceyear').val();
    var devhr = $('#devicehour').val();
    var devminute = $('#devicemin').val();
    var tomon = $('#tomonth').val();
    var today = $('#today').val();
    var toyr = $('#toyear').val();
    var tohr = $('#tohour').val();
    var tomin = $('#tominute').val();
    // alert(searchstring);
//    return false;
    if (searchstring != '') {
        $('.parentContainer').removeClass('open');//header class hide
        $('#eventMainGrid').hide();
        $('#eventsfiltergrid').show();
        $.ajax({
            url: "geteventGrid.php?level=" + level + '&searchstring=' + searchstring,
            type: "POST",
            dataType: "json",
            data: { 'csrfMagicToken': csrfMagicToken },
            success: function (gridData) {
                $(".se-pre-con").hide();
                $('#DeviceTypeseventData').DataTable().destroy();
                groupTable = $('#DeviceTypeseventData').DataTable({
                    scrollY: jQuery('#DeviceTypeseventData').data('height'),
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
                    columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
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
                        $('.main-class-hide').modal('hide');
                        $('#eventsearchfilterhide').modal('hide');
                    }
                });
                $('.tableloader').hide();
            },
            error: function (msg) {

            }
        });

        $("#sites_searchbox").keyup(function () {
            groupTable.search(this.value).draw();
        });
    } else if (searchstring == '' && devmon != '' && tomon != '') {
        $('.parentContainer').removeClass('open');//header class hide
        $('#eventMainGrid').hide();
        $('#eventsfiltergrid').show();
        $.ajax({
            url: "geteventGrid.php?level=" + level + '&devmon=' + devmon + '&devday=' + devday + '&devyr=' + devyr + '&devhr=' + devhr + '&devminute=' + devminute + '&tomon=' + tomon + '&today=' + today + '&toyr=' + toyr + '&tohr=' + tohr + '&tomin=' + tomin,
            type: "POST",
            dataType: "json",
            data: { 'csrfMagicToken': csrfMagicToken },
            success: function (gridData) {
                $(".se-pre-con").hide();
                $('#DeviceTypeseventData').DataTable().destroy();
                groupTable = $('#DeviceTypeseventData').DataTable({
                    scrollY: jQuery('#DeviceTypeseventData').data('height'),
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
                    columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
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
                        $('.main-class-hide').modal('hide');
                        $('#eventsearchfilterhide').modal('hide');
                    }
                });
                $('.tableloader').hide();
            },
            error: function (msg) {

            }
        });

        $("#sites_searchbox").keyup(function () {
            groupTable.search(this.value).draw();
        });
    } else if (searchstring == '') {
        $("#warningreventfilter").modal("show");
        return false;
    }

}

function Backtoevents() {
    window.location.href = 'events.php';
}

/* ====== event all level excel code ======= */
function exporteventalllist() {
    var level = $('#searchValue').val();
    if (level === 'All') {
        $('#warningPopup').modal('show');
    } else {
        window.location.href = 'sitefunctions.php?function=exportExcelallData';
    }
}


function eventDetail(idx) {
    if (idx == '') {
        $('#warning').modal('show');
    } else {
        $.ajax({
            url: "sitefunctions.php?function=eventDetailPopup&eid=" + idx,
            type: "POST",
            dataType: "json",
            data: { 'csrfMagicToken': csrfMagicToken },
            async: true,
            success: function (data) {
                $('#eventdetailpopup').modal('show');
                $('#site').html(data.site);
                $('#machine').html(data.machine);
                $('#clientversion').html(data.clientversion);
                $('#cltime').html(data.cltime);
                $('#setime').html(data.setime);
                $('#uuid').html(data.uuid);
                $('#uname').html(data.uname);
                $('#priority').html(data.priority);
                $('#desc').html(data.desc);
                $('#scripno').html(data.scripno);
                $('#type').html(data.type);
                $('#exec').html(data.exec);
                $('#version').html(data.version);
                $('#size').html(data.size);
                $('#uid').html(data.uid);
                $('#string1').html(data.string1);
                $('#string2').html(data.string2);
                $('#clsize').html(data.clsize);
                $('#path').html(data.path);
                $('#text1').html(data.text1);
                $('#text2').html(data.text2);
                $('#text3').html(data.text3);
                $('#text4').html(data.text4);
            }
        });
    }
}

/* Advanced Search Option Functinality */

function searchEventData() {
    var filterValue = $('#el-events').val();
    var dartnoValue = $('#el-dartno').val();

    var exec = $('#executable').val();
    var title = $('#title').val();
    var text1 = $('#text_1').val();
    var text2 = $('#text_2').val();
    var text3 = $('#text_3').val();
    var text4 = $('#text_4').val();
    var idval = $('#idval').val();

    var startDate = $('#datefrom').val();
    var endDate = $('#dateto').val();

    if ((filterValue == '') && (dartnoValue == '')) {
        $('#errmsg').html('Please select a search criteria');
        return false;
    } else if ((startDate == '') || (startDate == 'undefined')) {
        $('#errmsg').html('Please select a start date');
        return false;
    } else if ((endDate == '') || (endDate == 'undefined')) {
        $('#errmsg').html('Please select a end date');
        return false;
    } else if (startDate > endDate) {
        $('#errmsg').html('Start date must be less than end date');
        return false;
    } else {
        $('#errmsg').html('');
    }

    var query_string = "filter=" + filterValue + "&dartno=" + dartnoValue + "&exec=" + exec + "&title=" + title + "&text1=" + text1 + "&text2=" + text2 + "&text3=" + text3 + "&text4=" + text4 + "&idval=" + idval + "&startdate=" + startDate + "&enddate=" + endDate;

    $("#eventGrid").dataTable().fnDestroy();

//    $("#loadingMaualAdd").show();
     
    $(".se-pre-con").show();
    $.ajax({
        url: "sitefunctions.php?function=eventlistallData&" + query_string,
        type: "POST",
        dataType: "json",
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function (gridData) {
            $(".se-pre-con").hide();
            $('#eventGrid').DataTable().destroy();
            eventTable1 = $('#eventGrid').DataTable({
                scrollY: jQuery('#eventGrid').data('height'),
                scrollCollapse: true,
                paging: true,
                serverside: true,
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
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                columns: [
     {"data": "device"},
     {"data": "desc"},
     {"data": "eventInfo"},
     {"data": "scrip"},
     {"data": "clientTime"},
                    {"data": "serverTime"}
     ],
                initComplete: function (settings, json) {
                    $('#eventdetail_searchbox').hide();
                    $('#eventdetail_searchbox1').show();
                },
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }
            });
        },
        error: function (msg) {

        }
    });
    $('#AdvancedSearch').modal("hide");
    $('.tableloader').hide();
    $("#loadingMaualAdd").hide();
    $('#errmsg').html('');
}

$('#eventGrid').on('click', 'tr', function () { //row selection code            
    if(typeof eventTable1 !== 'undefined') {
    eventTable1.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
    if ($(this).hasClass('selected')) {
        var rowdata = eventTable1.row(this).data();

        $("#sel_id").val(rowdata.id);
    } else {
        var rowdata = eventTable1.row(this).data();
        eventTable1.$('tr.selected').removeClass('selected');
        $("#sel_id").val(rowdata.id);
        $(this).addClass('selected');
    }
    } else {
         eventTable.$('tr.selected').removeClass('selected');
         $(this).addClass('selected');
        if ($(this).hasClass('selected')) {
            var rowdata = eventTable.row(this).data();

            $("#sel_id").val(rowdata.id);
        } else {
            var rowdata = eventTable.row(this).data();
            eventTable.$('tr.selected').removeClass('selected');
            $("#sel_id").val(rowdata.id);
            $(this).addClass('selected');
        }
    }
   
    
});

$("#eventdetail_searchbox").keyup(function () {
    eventTable.search(this.value).draw();
});

$("#eventdetail_searchbox1").keyup(function () {
    eventTable1.search(this.value).draw();
});

$('#AdvancedSearch').on('hidden.bs.modal', function () {
    $('.selectpicker').selectpicker('refresh');
    $("#AdvancedSearch .form-control").val("");

});
