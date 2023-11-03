/*
 * File Info    : Machine Asset History JS Compilation
 * Owner Info   : JHN
 * Created Date : 15-01-2018
 */

$(function () {

    getDataName();

    $('input[name="repType"]').click(function () {
        var repType = $(this).val();
        if (repType == 'change') {
            $('.asst_compare').hide();
            $('.asst_change').show();
        } else if (repType == 'compare') {
            $('.asst_change').hide();
            $('.asst_compare').show();
        }
    });

    /*$(".mcs-horizontal").mCustomScrollbar({
     axis: "x",
     theme: "dark-3",
     advanced:{ autoExpandHorizontalScroll:true }
     });*/
});

$(".date_format").datetimepicker({
    format: "mm/dd/yyyy",
    autoclose: true,
    todayBtn: false,
    pickerPosition: "top-left",
    startDate: "2012-01-01 01:00",
    endDate: new Date(),
    minView: 'month'
});

function getDataName() {
    $.ajax({
        url: 'assetDataNameTree.php',
        type: 'POST',
        data: 'function=1&functionToCall=renderDataName',
        success: function (data) {
            $('#dataNameList').html(data.trim());
        },
        error: function (err) {
            console.log("Fn_getDataNameList : " + err.toString());
        }
    });
}

$('table a').click(function () {
    return false;
});

function backToAssetView() {
    $('#AssetView').show();
    $('#assetHistDiv').hide();
    $('.dataView').hide();
}

$('#submitdata').click(function () {
    var dataid = [];
    $('#seterr').html('');
    $('#seterr').show();

    $("input:checkbox[name^='display']:checked").each(function () {
        dataid.push($(this).attr("name").split('_')[1]);
    });

    var reportType = $('input:radio[name="repType"]:checked').val();
    var changeDuration = $('#asst_change').val();

    var firstDate = $('#datefrom').val();
    var secndDate = $('#dateto').val();

    if (typeof reportType == 'undefined') {
        $('#seterr').html('<span>Please select a report type</span>');
        setTimeout(function () {
            $("#seterr").fadeOut(2000)
        }, 1000);
        return;
    }

    if (reportType == 'compare') {
        if (firstDate == '') {
            $('#seterr').html('<span>Please select first date</span>');
            setTimeout(function () {
                $("#seterr").fadeOut(2000)
            }, 1000);
            return;
        }
        if (secndDate == '') {
            $('#seterr').html('<span>Please select second date</span>');
            setTimeout(function () {
                $("#seterr").fadeOut(2000)
            }, 1000);
            return;
        }

        var start = (new Date(firstDate).getTime());
        var to = (new Date(secndDate).getTime());
        if (start > to) {
            $('#seterr').html('<span>First Date should be less than Second Date</span>');
            setTimeout(function () {
                $("#seterr").fadeOut(2000)
            }, 1000);
            return;
        }
    } else {
        if (changeDuration == '') {
            $('#seterr').html('<span>Please select duration</span>');
            setTimeout(function () {
                $("#seterr").fadeOut(2000)
            }, 1000);
            return;
        }
    }

    if (dataid.length < 1) {
        $('#seterr').html('Please select component');
        return false;
    } else if (reportType == 'undefined' || typeof reportType == 'undefined') {
        $('#seterr').html('Please select a report type');
        return false;
    } else {
        if (reportType == 'change') {
            if (changeDuration == '') {
                $('#seterr').html('Please select Asset change duration');
                return false;
            }
        } else if (reportType == 'compare') {
            if (firstDate == '' || secndDate == '') {
                $('#seterr').html('Please select First and Second Date');
                return false;
            }
        }
    }
    $('#seterr').html('');
    var formData = "&dataids=" + dataid + "&changeval=" + changeDuration + "&firstDate=" + firstDate + "&secndDate=" + secndDate + "&reportType=" + reportType;

    $.ajax({
        url: "assetHistoryFunc.php",
        type: 'POST',
        data: "function=1&functionToCall=setAssetDataListInfo" + formData,
        dataType: 'json',
        success: function (data) {
            //console.log("Data : " + JSON.stringify(data));


            $('#AssetView').hide();
            $('.dataView').show();
            if (reportType == 'compare') {
                //var date1 = new Date(firstDate);
                //var date2 = new Date(secndDate);
                //var diffDays = date2.getDate() - date1.getDate();

                $('.assetHisTable').html('<table class="dt-responsive hover order-table nowrap" id="assetHistDetail_Compare" width="100%" data-page-length="10"><thead><tr></tr></thead></table>');
                loadAssetCompareHistory(data, firstDate, secndDate, 2);
            } else {
                $('.assetHisTable').html('<table class="dt-responsive hover order-table nowrap" id="assetHistDetail_' + changeDuration + '" width="100%" data-page-length="10"><thead><tr></tr></thead></table>');
                loadAssetChangeHistory(data, changeDuration);
            }
        },
        error: function (err) {
            console.log("Error : " + err);
        }
    });
});

function loadAssetChangeHistory(data, showHistoryFor) {

    var assetHistData = [];
    var astData = [];
    var content = "";

    var date = new Date();
    var now = date.getTime() / 1000;

    var sampleStartDate = Math.round(now) - ((showHistoryFor - 1) * 24 * 60 * 60); //'1510808400';  // mm/dd/yyyy
    var columnDateVal = sampleStartDate;
    //var sampleEndDate   = sampleStartDate + (showHistoryFor * 24 * 60 * 60);

    //var columnVal = data['slatestdate'];

    content += "<th>Asset Name</th>";

    for (var i = 1; i <= showHistoryFor; i++) {
        /*if(columnVal[i-1] == 'undefined' || typeof columnVal[i-1] == 'undefined') {
         content += "<th>Day "+ i +"</th>";
         } else {
         content += "<th>" + (columnVal[i-1]).split(' ')[0] + "</th>";
         }*/
        console.log('Col Val : ' + columnDateVal);

        var dt = new Date(columnDateVal * 1000);
        var colDate = (dt.getMonth() + parseInt(1)) + '/' + dt.getDate() + '/' + dt.getFullYear();
        content += "<th>" + colDate + "</th>";

        columnDateVal = parseInt(sampleStartDate) + parseInt((i * 24 * 60 * 60));
    }

    $.each(data, function (key, val) {
        astData = [];
        for (var i = 0; i <= showHistoryFor; i++) {
            if (i === 0) {
                astData.push(['<p class="ellipsis" title="' + key + '">' + key + '</p>']);
            } else {
                if (typeof val[i - 1] == 'undefined') {
                    astData.push(['<p class="ellipsis" title="NA">NA</p>']);
                } else if (val[i - 1] == 'null' || val[i - 1] == null) {
                    astData.push(['<p class="ellipsis" title="No Change">No Change</p>']);
                } else {
                    astData.push(['<p class="ellipsis" title="' + val[i - 1] + '">' + val[i - 1] + '</p>']);
                }
            }
        }
        assetHistData.push(astData);
    });
    $("#assetHistDetail_" + showHistoryFor + ">thead>tr").html(content);

    $('#assetHistDiv').show();

    $('#assetHistDetail_' + showHistoryFor).DataTable().destroy();
    eventDetails = $('#assetHistDetail_' + showHistoryFor).DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: false,
        responsive: true,
        aaData: assetHistData,
        searching: true,
        serverSide: false,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false
            }],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>'
    });
}

function loadAssetCompareHistory(data, firstDate, secndDate, showHistoryFor) {

    var assetHistData = [];
    var astData = [];
    var content = "";

    var columnVal = data['slatestdate'];

    content += "<th>Asset Name</th><th>" + firstDate + "</th><th>" + secndDate + "</th>";

    /*for(var i=1; i <= showHistoryFor; i++) {
     console.log(columnVal[i-1]);
     if(columnVal[i-1] == 'undefined' || typeof columnVal[i-1] == 'undefined') {
     content += "<th>Day "+ i +"</th>";
     } else {
     content += "<th>" + (columnVal[i-1]).split('T')[0] + "</th>";
     }
     }*/

    $.each(data, function (key, val) {
        astData = [];
        for (var i = 0; i <= showHistoryFor; i++) {
            if (i === 0) {
                astData.push(['<p class="ellipsis" title="' + key + '">' + key + '</p>']);
            } else {
                if (val[i - 1] == 'null' || val[i - 1] == null) {
                    astData.push(['<p class="ellipsis" title="No Change">No Change</p>']);
                } else {
                    astData.push(['<p class="ellipsis" title="' + val[i - 1] + '">' + val[i - 1] + '</p>']);
                }
            }
        }
        assetHistData.push(astData);
    });
    $("#assetHistDetail_Compare>thead>tr").html(content);

    $('#assetHistDiv').show();

    $('#assetHistDetail_Compare').DataTable().destroy();
    eventDetails = $('#assetHistDetail_Compare').DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: false,
        responsive: true,
        aaData: assetHistData,
        searching: true,
        serverSide: false,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false
            }],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>'
    });
}