$(function () {
assettable = false;
    getDataName();

});
function getDataName() {
    $.ajax({
        url: "macAssetInfoFunc.php",
        type: "POST",
        data: "function=1&functionToCall=getDataNameListNew" + '&csrfMagicToken=' + csrfMagicToken,
        success: function (data) {
            $('#dataNameList').html(data.trim());
            $('#showMsg').show();
        },
        error: function (err) {
            console.log(err);
        }
    });
}

function showSpecList(dhval, dhname, type) {
    if (type == 'old') {
        $('#dataNameList ul').slideUp();
        if ($('.' + dhval).css('display') == 'block') {
            return;
        }
    }
    $('.' + dhval).slideDown();
    $('#headName').val(dhname + ' > ');
}

function getMachineAssetData(dval, dname, refobj) {
    $('.ldr-sm').show();
    $('#dataNameList > ul > li').removeClass('activenew');
    $('*').removeClass('activenew');
    $(refobj).addClass('activenew');
    
    url = "macAssetInfoFunc.php?function=1&functionToCall=getMachineAssetData&dval=" + dval;
    
    /*$.ajax({
        url: "macAssetInfoFunc.php",
        type: "POST",
        data: "function=1&functionToCall=getMachineAssetData&dval=" + dval,
        dataType: 'json',
        success: function (data) {
            $('.ldr-sm').hide();
            $('#showMacMsg').hide();
            var hval = $('#headName').val();
            $('#rightcompliance').html("Asset Information : " + dname);
            if (refresh) {
                $('.assettable').html('<table class="dt-responsive hover order-table nowrap" id="macAssetData_' + dval + '" width="100%" data-page-length="100"><thead><tr></tr></thead></table>');
            }
            renderAssetDataGrid(data, dval);
        },
        error: function (err) {
            console.log(err);
        }
    });*/
     $("#macAssetData>thead>tr").html("");
    $.ajax({
        url: "macAssetInfoFunc.php",
        data: "function=1&functionToCall=getHeader&dval=" + dval + '&csrfMagicToken=' + csrfMagicToken,
        type: 'POST',
        dataType: 'json',
        success: function (data) {
 
            if(assettable) {
                 $("#macAssetData>thead>tr").html("");
                $('#macAssetData').DataTable().destroy();
                $('.assettable').html('<table class="dt-responsive hover order-table nowrap" id="macAssetData" width="100%" data-page-length="100"><thead><tr></tr></thead></table>');
            }
            $("#macAssetData>thead>tr").html(data.header);
            var columns = [];
            $.each(data.column, function (key, val) {
                columns.push({data: val});
    });
            $('.ldr-sm').hide();
            $('#showMacMsg').hide();
            $('.report_table').hide();
            $('.assettable').show();

//            $('#macAssetData').DataTable().destroy();
            table1 = $('#macAssetData').DataTable({
                scrollY: jQuery('.order-table').data('height'),
                scrollCollapse: true,
                autoWidth: true,
                paging: true,
                searching: false,
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
                columns: columns,
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $(".se-pre-con").hide();
                     assettable = true;
}
            });
        }

        });
       
}

$('#srch_scomp').keyup(function () {
    var txt = $(this).val().toUpperCase();
    if(txt != '') {
        $('ul#dataNameList > ul').show();
    } else {
        $('ul#dataNameList > ul').hide();
    }
    $('ul#dataNameList > ul > li').each(function () {
        var currentLiText = $(this).text().toUpperCase();
        var showCurrentLi = currentLiText.indexOf(txt) !== -1;
        $(this).toggle(showCurrentLi);
    });
});

function renderAssetDataGrid(assetdata, dval) {

    var assetData = [];
    var assetDataSet = [];
    var columnNames = get_MachineUniqueColumn(assetdata);

    var assetDataRes = loopMachineData(assetdata, columnNames, dval);    // Table Columns heading added in this function.

    for (var i = 0; i < assetDataRes.length; i++) {
        for (var j = 0; j < assetDataRes[i].length; j++) {
            assetDataSet[j] = assetDataRes[i][j];
        }
        assetData.push(assetDataSet);
        assetDataSet = [];
    }

    $('.report_table').hide();
    $('.assettable').show();
    if (refresh) {
        $('#macAssetData_' + dval).DataTable().destroy();
        $('#macAssetData_' + dval).DataTable({
            scrollY: jQuery('.order-table').data('height'),
            scrollCollapse: true,
            autoWidth: true,
            bAutoWidth: true,
            responsive: true,
            aaData: assetData,
            searching: true,
            serverSide: false,
            stateSave: true,
            "stateSaveParams": function (settings, data) {
                data.search.search = "";
            },
            order: [[0, "desc"]],
            columnDefs: [{
                    targets: "datatable-nosort",
                    orderable: false
                }],
            "lengthMenu": [[100, 500, 1000, 5000], [100, 500, 1000, 5000]],
            "language": {
                "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                searchPlaceholder: "Search"
            },
            "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
            drawCallback: function (settings) {
                $(".dataTables_scrollBody").mCustomScrollbar({
                    theme: "minimal-dark"
                });
            }
        });
    } else {
        $('#macAssetData').DataTable().destroy();
        $('#macAssetData').DataTable({
            scrollY: jQuery('.order-table').data('height'),
            scrollCollapse: true,
            autoWidth: true,
            bAutoWidth: true,
            responsive: true,
            aaData: assetData,
            searching: true,
            serverSide: false,
            stateSave: true,
            "stateSaveParams": function (settings, data) {
                data.search.search = "";
            },
            order: [[0, "desc"]],
            columnDefs: [{
                    targets: "datatable-nosort",
                    orderable: false
                }],
            "lengthMenu": [[100, 500, 1000, 5000], [100, 500, 1000, 5000]],
            "language": {
                "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                searchPlaceholder: "Search"
            },
            "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
            drawCallback: function (settings) {
                $(".dataTables_scrollBody").mCustomScrollbar({
                    theme: "minimal-dark"
                });
            }
        });
    }
    refresh = true;
}

function get_MachineUniqueColumn(assetdata) {

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
                                dataColumns.push({"key": h, "val": g});
                            }
                        });
                    }
                });
            }
        });
        i++;
    });
    //return unique;
    return dataColumns;
}

function loopMachineData(assetdata, unique1, dval) {

    var column = 0;
    var temp = [];
    var data = [];
    var i = 0;
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
                                var jdata = value[t.key];
                                if (jdata === undefined || jdata === 'undefined') {
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

                                    final.push(link);
                                }
                                if ($.inArray(t.val, newcol) === -1) {
                                    newcol.push(t.key[t.val]);
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

    //Dynamically adding columns to Datatable
    var content = "";
    //content = "<th ></th>";
    $.each(unique1, function (k, v) {
        content += "<th class='table-plus'>" + v.val + "</th>";
    });
    if (refresh) {
        $("#macAssetData_" + dval + ">thead>tr").html(content);
    } else {
        $("#macAssetData>thead>tr").html(content);
    }

    for (var l in temp) {
        data.push(temp[l]);
    }
    return data;
}