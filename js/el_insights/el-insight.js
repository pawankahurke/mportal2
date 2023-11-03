
$(document).ready(function () {
    $('.se-pre-con').hide();
});

var G_DATA = {
    Tags: [],
    COLUMNS: [],
    PIECOLUMNS: [],
    TEMPOBJ: {}
};

$(".date_format").datetimepicker({
    format: "mm/dd/yyyy",
    autoclose: true,
    todayBtn: false,
    pickerPosition: "bottom-left",
    startDate: "2012-01-01 01:00",
    endDate: new Date(),
    minView: 'month'
});

$('input[name="filType"]').click(function () {
    var filType = $(this).val();
    if (filType == 'filter') {
        $('.el-eventDartno').hide();
        $('.el-eventFilter').show();
    } else if (filType == 'dartno') {
        $('.el-eventFilter').hide();
        $('.el-eventDartno').show();
    }
});

//To fetch filters based on section type
function populateFilters(header, obj) {

    //$('.se-pre-con').show();
    $('.ldr-sm, .el-btn').show();

    if ($(obj).val() == 1) {

        $(".el-assets").hide();
        if ($('input[name="filType"]:checked').val() == 'filter') {
            $('.el-eventFilter').show();
        } else if ($('input[name="filType"]:checked').val() == 'dartno') {
            $('.el-eventDartno').show();
        }

        $.ajax({
            type: "POST",
            url: "../lib/l-dynamicReport.php?function=1&functionToCall=getEventFilters&limit=10" + "&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json'
        }).done(function (data) {
            $('.ldr-sm').hide();
            $(".el-events").show();

            eventData = data;
            eventOptions = '';
            //eventOptions = '<option value="0">Choose filter</option>';
            for (var i = 0; i < data.length; i++) {
                eventOptions += '<option value="' + data[i].etag + '">' + data[i].name + '</option>';
            }
            $('#el-events').html("");
            $('#el-events').html(eventOptions);
            $('.selectpicker').selectpicker('refresh');
        });

        $.ajax({
            url: "../lib/l-dynamicReport.php?function=1&functionToCall=getScripList" + "&csrfMagicToken=" + csrfMagicToken,
            type: 'POST',
            success: function (data) {
                $('.ldr-sm').hide();
                $('.el-fromDate, .el-toDate').show();

                $('#el-dartno').html("");
                $('#el-dartno').html(data);
                $('.selectpicker').selectpicker('refresh');
            },
            error: function (err) {
                console.log('Err : ' + err.toString());
            }
        });

    } else if ($(obj).val() == 2) {

        $(".el-events, .el-eventFilter, .el-eventDartno").hide();
        $('.el-fromDate, .el-toDate').hide();

        $.ajax({
            type: "POST",
            url: "../lib/l-dynamicReport.php?function=1&functionToCall=getAssetQueries" + "&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json'
        }).done(function (data) {
            $('.ldr-sm').hide();
            $(".el-assets").show();

            assetData = data;
            assetOptions = '';
            //assetOptions = '<option value="0">Choose Query</option>';
            for (var i = 0; i < data.length; i++) {
                assetOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
            }
            $('#el-assets').html("");
            $('#el-assets').html(assetOptions);
            $('.selectpicker').selectpicker('refresh');
        });
    } else {

        $('.se-pre-con, .ldr-sm').hide();
        $(".el-events, .el-assets, .el-btn").hide();

        $(".el-events, .el-eventFilter, .el-eventDartno").hide();
        $('.el-fromDate, .el-toDate').hide();

    }
}

function submitSection() {

    var secType = $('#el-section').val();
    $('#err').html('');
    $('#err').show();

    if (secType === 0 || secType === '0') {
        $('#err').html('Please select section type');
        setTimeout(function () {
            $('#err').fadeOut(2000);
        }, 1000);
        return;
    }

    var secValu = '';
    if (secType == '1') {
        var filtType = $('input[name="filType"]:checked').val();

        if (filtType == '' || filtType === 'undefined' || filtType === undefined) {
            $('#err').html('Please select filter type');
            setTimeout(function () {
                $('#err').fadeOut(2000);
            }, 1000);
            return;
        }

        if (filtType == 'filter') {
            secValu = $('#el-events').val();
        } else if (filtType == 'dartno') {
            secValu = $('#el-dartno').val();
        }
        var fromDate = $('#datefrom').val();
        var toDate = $('#dateto').val();
        //    alert(fromDate);
        //    alert(toDate);
        if (fromDate == '') {
            $('#err').html('Please select start date');
            setTimeout(function () {
                $('#err').fadeOut(2000);
            }, 1000);
            return;
        }

        if (toDate == '') {
            $('#err').html('Please select end date');
            setTimeout(function () {
                $('#err').fadeOut(2000);
            }, 1000);
            return;
        }

        var start = (new Date(fromDate).getTime());
        var to = (new Date(toDate).getTime());
        if (start > to) {
            $('#err').html('<span>Start Date should be less than end date</span>');
            setTimeout(function () {
                $("#err").fadeOut(2000)
            }, 1000);
            return;
        }

    } else if (secType == '2') {
        secValu = $('#el-assets').val();
    }

    var data = {"function": 1, "functionToCall": "loadReportDetails", "section": secType, "EfilType": filtType, "sectVal": secValu, "fromdate": fromDate, "todate": toDate, csrfMagicToken: csrfMagicToken};

    $.ajax({
        url: "../el_insights/el-reportFunction.php",
        type: 'POST',
        data: data,
        success: function (data) {
            var res = data.trim();
            if (res == 'DONE') {
                window.open('el-reports.php', '_blank');
            }
        },
        error: function (err) {
            console.log('Err : ' + err);
        }
    });

}

function renderGridData(res) {
    var eventData = [];
    for (var i = 0; i < res.length; i++) {
        var o = res[i]['_source'];

        var customer = o.customer.split('__')[0];
        var servertime = '';
        var clienttime = '';
        if (o.servertime.length > 12) {
            servertime = o.servertime.replace('T');
            servertime = servertime.replace('Z');
        } else {
            var d = new Date(parseInt(o.servertime + '000'));
            servertime = (d.getMonth() + 1) + '/' + d.getDate() + '/' +  d.getFullYear() + ' ' + d.getHours() + ':' + d.getMinutes();
        }

        if (o.entered.length > 12) {
            clienttime = o.entered.replace('T');
            clienttime = clienttime.replace('Z');
        } else {
            var d = new Date(parseInt(o.entered + '000'));
            clienttime = (d.getMonth() + 1) + '/' + d.getDate() + '/' +  d.getFullYear() + ' ' + d.getHours() + ':' + d.getMinutes();
    }

        eventData.push(['<p class="ellipsis" title="' + o.machine + '">' + o.machine + '</p>', '<p class="ellipsis" title="' + customer + '">' + customer + '</p>', '<p class="ellipsis" title="' + servertime + '">' + servertime + '</p>', o.scrip, '<p class="ellipsis" title="' + o.description + '">' + o.description + '</p>', '<p class="ellipsis" title="' + o.text1 + '">' + o.text1 + '</p>', '<p class="ellipsis" title="' + clienttime + '">' + clienttime + '</p>']);
    }
$('.msgDiv').hide();
    $('.report_table').hide();
    $('.eventtable').show();

    $('#eventDetail').DataTable().destroy();
    eventDetails = $('#eventDetail').DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        responsive: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        data: eventData,
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false
            }],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function(settings, json) {
            $('.ldr-sm').hide();
           $(".se-pre-con").hide();
        },
        drawCallback: function (settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
            $('.equalHeight').matchHeight();
            $(".se-pre-con").hide();
        }
    });
}

$("#adhocEvent_searchbox").keyup(function () {
    eventDetails.search(this.value).draw();
});

function renderAssetGridData(assetdata) {

    var assetData = [];
    var assetDataSet = [];
    var columnNames = get_uniqueColumn(assetdata);
    var assetDataRes = loopdata(assetdata, columnNames);    // Table Columns heading added in this function.

    for (var i = 0; i < assetDataRes.length; i++) {
        for (var j = 0; j < assetDataRes[i].length; j++) {
            assetDataSet[j] = assetDataRes[i][j];
        }
        assetData.push(assetDataSet);
        assetDataSet = [];
    }

    $(".se-pre-con").hide();
    $('.report_table').hide();
    $('.assettable').show();

    $('#assetDetail').DataTable().destroy();
    assetDetails = $('#assetDetail').DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        responsive: true,
        aaData: assetData,
        searching: true,
        serverSide: false,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false
            }],
        "lengthMenu": [[25,100, 500, 1000, 5000], [25,100, 500, 1000, 5000]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        drawCallback: function (settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
            $('.equalHeight').matchHeight();
            $(".se-pre-con").hide();
        }
    });
}

//$("#adhocAsset_searchbox").keyup(function () {
//    assetDetails.search(this.value).draw();
//});

function get_uniqueColumn(assetdata) {

    var sections = assetdata;
    var i = 0;
    var dataColumns = [];
    var unique = {};

    $.each(sections, function (key, val) {
        $.each(val, function (k, v) {
            if (k === 'details') {
                $.each(v, function (f, e) {
                    if (f === 'columns') {
                        $.each(e, function (g, h) {
                            if (!(g in unique)) {
                                unique[h] = g;
                            }
                        });
                    }
                });
            }
        });
        i++;
    });
    return unique;
}

function loopdata(assetdata, unique1) {

    var column = 0;
    var temp = [];
    var data = [];
    var i = 0;
    var dataColumns = [];
    var sections = assetdata;
    var newcol = [];

    $.each(sections, function (key, val) {
        $.each(val, function (k, v) {
            if (k === 'details') {
                $.each(v, function (f, e) {
                    if (f === 'rows') {
                        $.each(e, function (g, h) {
                            var j = 0;
                            var final = [];
                            $.each(unique1, function (s, t) {
                                var value = h;
                                var jdata = value[s];
                                var arr = [];

                                //console.log("JOHN : " + jdata);

                                if (jdata === undefined || jdata === 'undefined') {
                                    //console.log("SATYA : " + jdata + "--" + s + '==' + t);
                                    var link = '<p class="ellipsis" title="" style="">-</p>';
                                    final.push(link);
                                } else {
                                    var link = "";
                                    $.each(jdata, function (n, o) {
                                        if (o == '-') {
                                            link += '<p class="ellipsis" title="" style="">' + o + '</p>';
                                        } else {
                                            var data = o.split('__')[0];
                                            link += '<p class="ellipsis" title="' + data + '">' + data + '</p>';
                                        }
                                    });
                                    /*if (s == '221' || s == 221) {   // To bring the machine name at first
                                     final.unshift(link);
                                     } else if (s == '357' || s == 357) {
                                     final.splice(1, 0, link);
                                     } else {
                                     final.push(link);
                                     }*/
                                    if (t == 'Machine Name') {   // To bring the machine name at first
                                        final.unshift(link);
                                    } else if (t == 'Site Name') {
                                        final.splice(1, 0, link);
                                    } else {
                                        final.push(link);
                                    }
                                }
                                if ($.inArray(t, newcol) === -1) {
                                    newcol.push(s[t]);
                                }
                                //newcol.push(t);
                            });
                            temp[column] = final;
                            column++;
                        });
                    }
                });
            }
        });
        i++;
    });
    //console.log("COLUMNS : " + JSON.stringify(newcol));
    //Dynamically adding columns to Datatable
    var content = "";
    var macContent = "";
    var siteContent = "";
    $.each(unique1, function (key, val) {
        //console.log("SATYA : " + val);
        /*if (key != 221 || key != '221') {
         if (key != 357 || key != '357') {
         content += "<th>" + val + "</th>";
         } else {
         siteContent += "<th>" + val + "</th>";
         }
         } else {
         if (key == 221 || key == '221') {
         macContent = "<th>" + val + "</th>";
         }
         }*/
        if (val != 'Machine Name') {
            if (val != 'Site Name') {
                content += "<th>" + val + "</th>";
            } else {
                siteContent += "<th>" + val + "</th>";
            }
        } else {
            if (val == 'Machine Name') {
                macContent = "<th>" + val + "</th>";
            }
        }
    });
    $("#assetDetail>thead>tr").html(macContent + "" + siteContent + "" + content);

    for (var l in temp) {
        data.push(temp[l]);
    }
    return data;
}

$('#submitDate').click(function () {

    $('.ldr-sm').show();

    var datefrom = $('#datefrom').val();
    var dateto = $('#dateto').val();

    var typeOfSection = $('#typeOfSection').val();

    if (datefrom == '' || dateto == '') {
        $('.errmsg').html('Please select the dates');
    } else {
        $('.errmsg').html('');
        var data = {"function": 1, "functionToCall": "loadReportDetails", "fromdate": datefrom, "todate": dateto, csrfMagicToken: csrfMagicToken};
        $.ajax({
            url: "../el_insights/el-reportFunction.php",
            type: 'POST',
            data: data,
            success: function (data) {
                var res = $.trim(data);
                if (res == 'DONE') {
                    if (typeOfSection == '1' || typeOfSection == 1) {
                        // Event Section
                        eventFunctionCall();
                    } else if (typeOfSection == '2' || typeOfSection == 2) {
                        // Asset Section
                        assetFunctionCall();
                    }
                }
            },
            error: function (err) {
                console.log('Err : ' + err);
            }
        });
    }
});

// Event Function Call Definition
function eventFunctionCall() {
    $('.searchOpt').hide();
    $('#adhocEvent_searchbox').show();
    var data = {"function": 1, "functionToCall": "eventReportFunction", csrfMagicToken: csrfMagicToken};
$(".se-pre-con").show();
    $.ajax({
        url: "../el_insights/el-reportFunction.php",
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function (data) {
//            $('.ldr-sm').hide();
//            $(".se-pre-con").hide();
            renderGridData(data);
        },
        error: function (err) {
            console.log('Err : ' + err);
        }
    });
}

// Asset Function Call Definition
function assetFunctionCall() {
    $('.searchOpt').hide();
    $('#adhocAsset_searchbox').show();
    $(".se-pre-con").show();
    var col = '';

    $.ajax({
        url: "../el_insights/el-reportFunction.php",
        type: 'POST',
        data: 'function=1&functionToCall=assetReportFunctionEL_1&csrfMagicToken=' + csrfMagicToken,
        dataType: 'text',
        success: function (result) {
            console.log(result);
            if(result !== 'NoData') {
//                var filterId = result.split('#')[1];
//                var id = result.split('#')[0];
var id = result;
    if (assetApi) {
                url = "../el_insights/el-reportFunction.php?function=1&functionToCall=assetReportFunctionEL_new&return="+id + "&csrfMagicToken=" + csrfMagicToken;
    } else {
        url = "../el_insights/el-reportFunction.php?function=1&functionToCall=assetReportFunction" + "&csrfMagicToken=" + csrfMagicToken;
    }

    $.ajax({
        url: url,
        type: 'POST',
        dataType: 'json',
        success: function (data) {
            $("#assetDetail>thead>tr").html(data.header);
            var columns = [];
            $.each(data.column, function (key, val) {
                columns.push({data: val});
            });
           
//                    $(".se-pre-con").hide();
                    $('.msgDiv').hide();
            $('.report_table').hide();
            $('.assettable').show();

            $('#assetDetail').DataTable().destroy();
            table1 = $('#assetDetail').DataTable({
                scrollY: jQuery('.order-table').data('height'),
                scrollCollapse: true,
                autoWidth: true,
                paging: true,
                searching: true,
                processing: true,
                serverSide: true,
                ordering: true,
                select: true,
                bInfo: false,
                responsive: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
        },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                ajax: {
                    url: url,
                    type: "POST"
                },
                columnDefs: [{
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                columns: columns,
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $(".se-pre-con").hide();
        }
    });
}
    });
        } else {
            $(".se-pre-con").hide();
            $(".assettable").hide();
            $(".eventtable").hide();
            $('.msgDiv').show();
}
        }
    });
    $("#adhocAsset_searchbox").keyup(function () {
        var searchVal = $('#adhocAsset_searchbox').val();
        var result = [];
        var output = {}
        var i=0;
        var tablerows;
        table1.column( 0 ).data().each( function ( value, index ) {
            //var child = table1.row(this).child;
            //console.log(child);
            var data1 = this.data();
            if(value.toLowerCase().indexOf(searchVal) !== -1) {
               // console.log(data1[index]);
                tablerows = '<tr role="row" class="odd"><td class="sorting_1" tabindex="0">';
              // table1.column(index).draw();
                result.push(data1[index]); 
                output[i] = data1[index];
                i++;
}
        });
//console.log(output);
//        tablerows = '<tr role="row" class="odd"><td class="sorting_1" tabindex="0"><p class="ellipsis" title="2844tmig" style="">2844tmig</p></td><td><p class="ellipsis" title="JSFBBanking" style="">JSFBBanking</p></td><td><p class="ellipsis" title="Android" style="">Android</p></td><td><p class="ellipsis" title="-" style="">-</p></td><td><p class="ellipsis" title="-" style="">-</p></td></tr>';
//
//         $("#assetDetail tbody").empty();
//         $("#assetDetail tbody").append(tablerows);

//        table1 = $("#assetDetail").DataTable();
       // table1.row.add(output).draw(false); // Add new data
//        table1.columns.adjust().draw(); // Redraw th
      // table1.data(output).draw(false);
        table1.search(this.value).draw(false);
    });
}

function assetFunctionCall_old() {
    $('.searchOpt').hide();
    $('#adhocAsset_searchbox').show();
    $(".se-pre-con").show();

    $.ajax({
        url: "../el_insights/el-reportFunction.php",
        type: 'POST',
        data: 'function=1&functionToCall=assetReportFunctionEL_1' + "&csrfMagicToken=" + csrfMagicToken,
        dataType: 'text',
        success: function (result) {
            console.log(result);
    if (assetApi) {
            var data = {"function": 1, "functionToCall": "assetReportFunctionEL_new", "return":result, csrfMagicToken: csrfMagicToken};
    } else {
        var data = {"function": 1, "functionToCall": "assetReportFunction", csrfMagicToken: csrfMagicToken};
    }
    $.ajax({
        url: "../el_insights/el-reportFunction.php",
        type: 'POST',
        data: data,
        dataType: 'json',
        success: function (data) {
            renderAssetGridData(data);
        },
        error: function (err) {
            console.log('Err : ' + err);
        }
    });
}
    });
}

//  Excel Export JS Function
function exportExcelReport() {
    
    var level = $('#searchValue').val();
    if(level === 'All') {
        $('#warningPopup').modal('show');
    } else {
    location.href = "el-exportReport.php";
}
}
