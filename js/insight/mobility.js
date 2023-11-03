$(document).ready(function () {

    $('#reporttable, #apptable, #nettable').hide();

});
var rowToDisplay = [];
var map ;
var latlng = '';

$('#row_data').on('click', function () {
    var reportType = $('#repType').val();
    if (reportType == 'tem') {
        $('#reporttable').toggle();
    } else if (reportType == 'dataAnaly') {
        $('#apptable').toggle();
    } else if (reportType == 'netUsage') {
        $('#nettable').toggle();
    }
});

$('#repType').on('change', function () {
    $('#row_data').prop('checked', false);
    $('#reporttable,#apptable,#nettable').hide();
    if ($('#repType').val() == 'locTrack') {
        $('#tblfmt').hide();
    } else { 
        $('#tblfmt').show();
        $('#details').show();
        $('#map').hide();
    }
});

$('#repsubmit').on('click', function () {

    $('#reportMsg').html('');
    $('#reportMsg').show();
    $('#startDateMsg').html('');
    $('#startDateMsg').show();
    $('#endDateMsg').html('');
    $('#endDateMsg').show();
    $('#macError').html('');
    $('#macError').show();
    $('#repError').html('');
    $('#repError').show();
    $('#rawError').html('');
    $('#rawError').show();

    var reportType = $('#repType').val();
    var siteName = $("#rparentName").val();
    var machineName = $('#searchValue').val();
    var dateFrom = $('#datefrom').val();
    var dateTo = $('#dateto').val();
    var tag    = $('#searchType').val();
    var passlevel = $('#passlevel').val();
    var rowToDisplay = [];

    if (reportType == '') {
        $('#reportMsg').html('<span>Please select report type</span>');
        setTimeout(function(){$("#reportMsg").fadeOut(2000)},1000);
        return;
    }
    if (dateFrom == '') {
        $('#startDateMsg').html('<span>Please select date and time</span>');
        setTimeout(function(){$("#startDateMsg").fadeOut(2000)},1000);
        return;
    }
    if (dateTo == '' ) {
        $('#endDateMsg').html('<span>Please select date and time</span>');
        setTimeout(function(){$("#endDateMsg").fadeOut(2000)},1000);
        return;
    }
    
    var start = (new Date(dateFrom).getTime());
    var to = (new Date(dateTo).getTime());
    if(start > to ) {
        $('#macError').html('<span>Start Date should be less than end date</span>');
        setTimeout(function(){
            $("#macError").fadeOut(2000)
        },1000);
        return;
    }

    if(tag === 'Groups' || passlevel === 'Groups') {
        $('#repError').html('<span>Mobility report is not allowed at group level</span>');
        setTimeout(function(){
            $('#repError').fadeOut(2000)
        },1000);
        return;
    }

    var functionCall = '';
    var detailsData = {};

    if ((reportType == 'tem' || reportType == 'dataAnaly' || reportType == 'netUsage') && ($('#row_data').is(':checked') == false)) {
        $('#report').submit();
    } else {
        if ($('#row_data').is(':checked')) {
            functionCall = 'rowReport';
            if (reportType == 'tem') {
                rowToDisplay = $('#temfield').val();
            } else if (reportType == 'dataAnaly') {
                rowToDisplay = $('#appfield').val()
            } else if (reportType == 'netUsage') {
                rowToDisplay = $('#netfield').val();
            }
            if(rowToDisplay == null ) {
                $('#rawError').html('<span>Please select atleast one column</span>');
                setTimeout(function(){
                    $("#rawError").fadeOut(2000)
                },1000);
                return;
            }
        } else if (reportType == 'locTrack') {
            if(tag == 'Sites' || tag == 'All' || tag == 'Groups') {
                $('#macError').html('<span>Please select machine for location tracking</span>');
                setTimeout(function(){$("#macError").fadeOut(2000)},1000);
                return;
            }
            functionCall = 'getMapdetails';
            $('#details').hide();
            $('#map').show();
        }
        $('#repsubmit').attr('disabled','disabled');
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: '../lib/l-ajax.php?function=AJAX_mobility_report',
            data: {startDate: dateFrom, endDate: dateTo, site: siteName, machine: machineName, rowToDisplay: rowToDisplay, reportType: reportType, functionToCall: functionCall}
        }).done(function (msg) {
            $('#repsubmit').removeAttr('disabled');
            if (reportType == 'locTrack') {
                msg = $.trim(msg);
                if (msg) {
                    $('#map_msg').hide();
                    $('#map').css({"display": "block"});
                    $('#locDetails').css({"display": "none"});
                    renderMap(msg);
                } else {
                    $('#map').hide();
                    $('#map_msg').show();
                    $('#locDetails').css({"display": "none"});
                    $('.map').html('<span>Preview not available for this Machine between these dates.</span>');
                }
            } else {
                if ($('#row_data').is(':checked')) {
                    $('#rowData_popup').modal('show');
                    rowData(msg);
                    viewRowDatapopupclicked();
                }
            }
        });
    }
});

var customIcons = {
    start: {
        icon: 'http://maps.google.com/mapfiles/kml/paddle/red-circle.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
    },
    end: {
        icon: 'http://maps.google.com/mapfiles/kml/pal4/icon49.png',
        shadow: 'http://labs.google.com/ridefinder/images/mm_20_shadow.png'
    }
};

function renderMap(msg) {

    if(!msg) {
        $("#error").html("<span>No location to show</span>");
        return;
    }
    
    // If the browser supports the Geolocation API
    if (typeof navigator.geolocation == "undefined") {
        $("#error").html("<span>Your browser doesn't support the Geolocation API</span>");
        return;
    }

    navigator.geolocation.watchPosition(function (position) {
        var path = [];
        var res = msg.split(",");
        var count = Object.keys(res).length;
        // Create the map
        var myOptions = {
            zoom: 16,
            center: path[0],
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }
        map = new google.maps.Map(document.getElementById("map"), myOptions);

        // Create the polyline's points
        for (var i = 0; i < count; i++) {

            latlng = res[i];
            latlng = latlng.replace("|", ",");
            var temp = latlng.split(",");

            // Create a random point using the user current position and a random generated number.
            // The number will be once positive and once negative using based on the parity of i
            // and to reduce the range the number is divided by 10
            path.push(new google.maps.LatLng(temp[0], temp[1]));
        }
        // Create the array that will be used to fit the view to the points range and
        // place the markers to the polyline's points
        var latLngBounds = new google.maps.LatLngBounds();
        for (var i = 0; i < path.length; i++) {
            latLngBounds.extend(path[i]);
            // Place the marker
            new google.maps.Marker({
                map: map,
                position: path[i],
                title: "Location No: " + (i + 1) //+ "\n Date: 22/10/2010"
            });
        }
        // Creates the polyline object
        var polyline = new google.maps.Polyline({
            map: map,
            path: path,
            strokeColor: '#0000FF',
            strokeOpacity: 0.7,
            strokeWeight: 1
        });
        // Fit the bounds of the generated points
        map.fitBounds(latLngBounds);
    },
            function (positionError) {
                $("#error").append("Error: " + positionError.message + "<br />");
            },
            {
                enableHighAccuracy: false,
                timeout: 10 * 1000 // 10 seconds
            });
}

$('#rowData_popup').on('hidden.bs.modal', function () {
        $('.information-portal-popup').html('');
});

function rowData(tableData) {

    var columnNames = [];
    var colHeading = [];
    var data = [];
    var i = 0;
    var columns = [];
    var target = [];
    var order = 0;

    var y, l, x, k;
    var a = 0;

    for (k in tableData[0]) {
        
        if(k == 'siteName') {
            colHeading[i] = 'Site Name';
        } else if(k == 'machine') {
            colHeading[i] = 'Machine';
        } else if(k == 'typeOfCall') {
            colHeading[i] = 'Type';
        } else if(k == 'PhoneNo') {
            colHeading[i] = 'Number';
        } else if(k == 'timespoke') {
            colHeading[i] = 'Duration';
        } else if(k == 'timespokeroaming') {
            colHeading[i] = 'Duration(R)';
        } else if(k == 'clientTime') {
            colHeading[i] = 'Date & Time';
        } else if(k == 'exename') {
            colHeading[i] = 'App Name';
        } else if(k == 'packagename') {
            colHeading[i] = 'Package Name';
        } else if(k == 'timespentonapp') {
            colHeading[i] = 'Duration';
        } else if(k == 'wifiDataUsage') {
            colHeading[i] = 'Wifi Data(in bytes)';
        } else if(k == 'mobileDataUsage') {
            colHeading[i] = 'Mobile Data(in bytes)';
        } 
        columnNames[i] = k;
        i++;
    }
   
    var columnLength = i;
    var maxord = 1;
    var dataTableData = [];

    for (y in tableData) {
        order = columnLength;
        if (order > maxord) {
            maxord = order;
        }

        var m = 0;
        var temp = [];
        for (l in columnNames) {
            var name = columnNames[l];
            var singleVal = tableData[y][name].split("__")[0];
            temp[m] = '<p class="ellipsis" title="' + singleVal + '">' + singleVal + '</p>';
            m++;
        }
        dataTableData[y] = temp;
    }
    for (l in dataTableData) {
        data.push(dataTableData[l]);
    }
    var returnData = {columns: colHeading, rows: data};

    for (x in returnData.columns) {
        columns.push({"sTitle": returnData.columns[x]});
        target.push(parseInt(x));
    }

    $(".information-portal-popup .se-pre-con").hide();

    var htmlTable = '';
        htmlTable = '<div class="information-portal-popup equalHeight">'+
                    '<div class="se-pre-con"></div>' +
                    '<table id="rowDataTable" class="dt-responsive hover order-table nowrap" '+
                        'width="100%" data-page-length="25"> '+
                   '<thead> </thead> </table> </div>';
           
        $(htmlTable).insertAfter('.popup-title-wrap');
    
    $('#rowDataTable').dataTable({
        scrollY: jQuery('#rowDataTable').data('height'),
        scrollCollapse: true,
        searching: false,
        bLengthChange: true,
        paging: true,
        binfo: false,
        ordering: true,
        responsive: true,
        bAutoWidth: true,
        bDestroy: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        data: returnData.rows,
        columns: columns,
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]], 
        "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
        "columnDefs": [
            {className: "table-plus row-data-grid", "targets": 0},
            {targets: "datatable-nosort",
                        orderable: false
            }
        ],
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        drawCallback: function (settings) {
                        $(".dataTables_scrollBody").mCustomScrollbar({
                            theme: "minimal-dark"
                        });
                        $(".information-portal-popup .se-pre-con").hide();                                        
                    }
    });
     $('#repsubmit').removeAttr('disabled');
    
}

function downloadRowData() {

    var reportType = $('#repType').val();
    var fromDate = $('#datefrom').val();
    var endDate = $('#dateto').val();
    var siteName = $("#rparentName").val();
    var machineName = $('#searchValue').val();
    var rowToDisplay = [];
    if (reportType == 'tem') {
        rowToDisplay = $('#temfield').val();
    } else if (reportType == 'dataAnaly') {
        rowToDisplay = $('#appfield').val()
    } else if (reportType == 'netUsage') {
        rowToDisplay = $('#netfield').val();
    }

    window.location.href = '../insights/mobilityExcel.php?function=downloadExcel&repType=' + reportType + '&fromDate=' + fromDate + '&endDate=' + endDate + '&rowToDisplay=' + rowToDisplay + '&reportType=rowData';

}


function viewRowDatapopupclicked() {
    setTimeout(function () {
        $(".row-data-grid").click();
    }, 300);
}

$(".date_format").datetimepicker({
        format: "mm/dd/yyyy hh:ii",
        autoclose: true,
        todayBtn: false,
        pickerPosition: "bottom-left",
        startDate: "2012-01-01 01:00",
        endDate: new Date()
    });

