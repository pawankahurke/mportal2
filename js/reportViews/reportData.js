$(document).ready(function () {
    getAllReports();
});

function getAllReports() {
    $.ajax({
        url: "reportDataFun.php?function=1&functionToCall=getAllViewReports"+"&csrfMagicToken=" + csrfMagicToken,
        type: 'POST',
        data: '',
        dataType: 'json',
        success: function (reports) {
            var reportStr = "";

            if (reports.length > 0) {
                for (var i = 0; i < reports.length; i++) {
                    if (i === 0) {
                        reportStr += '<li id="' + reports[i].id + '" onclick="getDetailReports(' + reports[i].id + ', this);" class="active"><a href="#">' + reports[i].name + '</a></li>';
                    } else {
                        reportStr += '<li id="' + reports[i].id + '" onclick="getDetailReports(' + reports[i].id + ', this);"><a href="#">' + reports[i].name + '</a></li>';
                    }
                }
            } else {
                reportStr = '<li><a href="#">No Views are available</a></li>';
                $('#editRprt').hide();
                $('#excelRprt').hide();
                $('#pdfRprt').hide();
            }
            $("#allReports").html(reportStr);
            //$("ul#allReports > li:first").trigger('click');
        },
        error: function (err) {
            console.log('Error  : ' + err);
        }
    });
    $("#allReports").mCustomScrollbar("update");
}

function getDetailReports(repId, reference) {
    $("#sectionDetailsUL").html("");
    $("#allReports li").removeClass("active");
    $(reference).addClass("active");

    $.ajax({
        url: "reportDataFun.php?function=1&functionToCall=getReportSectionDetails",
        type: 'POST',
        data: 'repId=' + repId +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function (reportSections) {
            $("#reportName").html(reportSections.name);
            for (var i = 0; i < reportSections.sectionData.length; i++) {
                renderSectionData(reportSections.sectionData[i], repId);
            }
        },
        error: function (err) {
            console.log('Error  : ' + err);
        }
    });
    $(".right").mCustomScrollbar("update");

}

function renderSectionData(sectionData, repId) {
    var sectionType = sectionData.secType;
    var chartType = sectionData.chartType;
    var sectionId = sectionData.secId;
    var sectionName = sectionData.sectionName;
    var gridEnalbed = sectionData.gridEnalbed;

    switch (sectionType) {
        case '1':
            renderEventSectionData(sectionData.subSectionData, sectionId, sectionName, chartType, gridEnalbed);
            break;
        case '2':
            renderAssetSectionData(sectionData.subSectionData, sectionId, sectionName, chartType, repId, gridEnalbed);
            break;
        case '3':
            renderMUMSectionData(sectionData.subSectionData, sectionId, sectionName, chartType, repId, gridEnalbed);
            break;
        case '8':
            renderSummarySectionData(sectionData.subSectionData, sectionId, sectionName, chartType, repId, gridEnalbed);
            break;
        case '9':
            renderNotifSectionData(sectionData.subSectionData, sectionId, sectionName, chartType, repId, gridEnalbed);
            break;
        default :
            $('#alert_popup').modal('show');

//            alert("Configured section does not support in this version");
            break;
    }
}

var G_DATA = {
    Tags: [],
    COLUMNS: [],
    PIECOLUMNS: [],
    TEMPOBJ: {}
};

/*** Event Data rendering Start ***/
function renderEventSectionData(sectionData, sectionId, sectionName, chartType, gridEnalbed) {
    var groupBy = "site";
    if (sectionData !== undefined) {
        groupBy = sectionData[0][3];
    }

    var sectionDivId = 'Events_' + sectionId;
    $("#" + sectionDivId).remove();
    var eventDiv = '<li id="Events_' + sectionId + '"><div class="report-wrap customscroll box" style="margin-bottom:3% !important"><div class="left"><h5>' + sectionName + '</h5></div></div></li>';
    if ($('#sectionDetailsUL li').length === 0) {
        $("#sectionDetailsUL").html(eventDiv);
    } else {
        if ($('#' + sectionDivId).length === 0)
        {
            $("#sectionDetailsUL").append(eventDiv);
        }
    }

    var sectionType = 1;
    $.ajax({
        url: "reportDataFun.php?function=1&functionToCall=getEventSectionDetails",
        type: 'POST',
        data: 'sectionId=' + sectionId + '&sectionType=' + sectionType + '&sectionData=' + sectionData +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function (reportSections) {
            var tags = reportSections.tags;
            var reportSectionData = reportSections.data;
            var tagNames = reportSections.tagArray;
            var count = reportSections.count;
            drawEventCharts(tags, reportSectionData, sectionId, tagNames, groupBy, chartType, gridEnalbed,count);
        },
        error: function (err) {
            console.log('Error  : ' + err);
        }
    });
    $('#sectionDetailsUL').mCustomScrollbar('update');
    $('#sectionDetailsUL').css('height', '');
}

function drawEventCharts(tags, arr, sectionId, tagNames, groupBy, graphType, gridEnalbed,count) {
    var tagsArray = tags.split(",");
    $('<div class="graph-div"><div id="Events_Graph_' + sectionId + '" style="max-height: 600px !important; position: relative;" class="graphDivs"></div></div>').appendTo("#Events_" + sectionId + " .left");

    if (graphType === "1") {
        if(count > 10) {
            $('#Events_Graph_'+sectionId).append('<span style="color:red;">Graph cannot be rendered as points exceed the maximum. Please change group by parameter and try again.</span>');
        } else {
        var tempo = createEventBarChartData(tags, arr, sectionId, groupBy);
        var columns = [['entered']];
        $.each(tagsArray, function (i, v) {
            columns.push([v]);
        });

        $.each(tempo, function (k, v) {
            columns[0].push(k.split("__")[0]);
            var a = [];
            a.push(k);
            $.each(tagsArray, function (i, val) {
                columns[i + 1].push(v[val]);
                a.push(v[val]);
            });
        });
            renderEventGraphs("bar", tagsArray, columns, sectionId);
        }
    } else if (graphType === "2") {
        if(count > 10) {
            $('#Events_Graph_'+sectionId).append('<span style="color:red;">Graph cannot be rendered as points exceed the maximum. Please change group by parameter and try again.</span>');
        } else {
        var tempo = createEventPieChartData(tags, arr, sectionId, groupBy);
        var pieCols = [];
        $.each(tempo, function (k, v) {
            var a = [];
            a.push(k);
            var pieFlag = false;
            $.each(tagsArray, function (i, val) {
                a.push(v[val]);
                pieFlag = true;
            });
            if (pieFlag) {
                pieCols.push(a);
            }
        });
        eventPieChart(tempo, pieCols, sectionId, tagNames);
        }
    } else if (graphType === "3") {
        if(count > 10) {
            $('#Events_Graph_'+sectionId).append('<span style="color:red;">Graph cannot be rendered as points exceed the maximum. Please change group by parameter and try again.</span>');
        } else {
        var tempo = createEventBarChartData(tags, arr, sectionId, groupBy);
        var columns = [['entered']];
        $.each(tagsArray, function (i, v) {
            columns.push([v]);
        });

        $.each(tempo, function (k, v) {
            columns[0].push(k.split("__")[0]);
            var a = [];
            a.push(k);
            $.each(tagsArray, function (i, val) {
                columns[i + 1].push(v[val]);
                a.push(v[val]);
            });
        });
        renderEventGraphs("line", tagsArray, columns, sectionId);
    }
    }
    if (gridEnalbed == "0") {

    } else {
        drawEventTable(sectionId, arr);
    }

}

function drawEventTable(sectionId, eventData) {
    var columns = '<th>Machine</th><th>Site</th><th>Date</th><th>Description</th><th>Text</th>';
//    console.log(sectionId);
    $("#Events_" + sectionId + " .report-wrap").append('<div class="report_table"><table class="dt-responsive hover order-table1 nowrap" id="Events_Table_' + sectionId + '" width="100%" data-page-length="25"><thead><tr>' + columns + '</tr></thead></table></div>');
    loadEventsTableData(sectionId, eventData);
}

function loadEventsTableData(sectionId, eventData) {
    var eventGridData = [];
    for (y in eventData) {
        var eventDate = "NA";
        if (eventData[y].enteredDate !== undefined) {
            eventdate = eventData[y].enteredDate.split('T')[0];
            eventDate = eventdate.split("-");
            eventDate = eventDate[1]+'-'+eventDate[2]+'-'+eventDate[0]
        }
        var temp = eventData[y].customer;
        var customer = temp.split("__")[0];
        var text = '-';
        if(eventData[y].text1 === undefined && eventData[y].text2 === undefined && eventData[y].text3 === undefined && eventData[y].text4 === undefined) {
            text = '-';
        } else {
            if(eventData[y].text1 != undefined) {
                text = eventData[y].text1;
            } else if(eventData[y].text2 != undefined) {
                text = eventData[y].text2;
            } else if(eventData[y].text3 != undefined) {
                text = eventData[y].text3;
            } else if(eventData[y].text4 != undefined) {
                text = eventData[y].text4;
            }
        }
//        eventGridData.push(['<p class="ellipsis" title="' + eventData[y].machine + '">' + eventData[y].machine + '</p>', '<p class="ellipsis" title="' + eventData[y].customer + '">' + eventData[y].customer + '</p>', '<p class="ellipsis" title="' + eventDate + '">' + eventDate + '</p>', '<p class="ellipsis" title="' + eventData[y].description + '">' + eventData[y].description + '</p>', '<p class="ellipsis" title="' + eventData[y].text1 + '">' + eventData[y].text1 + '</p>']);
        eventGridData.push(['<p class="ellipsis" title="' + eventData[y].machine + '">' + eventData[y].machine + '</p>', '<p class="ellipsis" title="' + customer + '">' + customer + '</p>', '<p class="ellipsis" title="' + eventDate + '">' + eventDate + '</p>', '<p class="ellipsis" title="' + eventData[y].description + '">' + eventData[y].description + '</p>', '<p class="ellipsis" title="' + text + '">' + text + '</p>']);
    }

    $('#Events_Table_' + sectionId).DataTable().destroy();
    $('#Events_Table_' + sectionId).DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        responsive: true,
        data: eventGridData,
        searching: false,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
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

function renderEventGraphs(type, tags, columns, sectionId) {
    switch (type) {
        case "line":
            eventLineChart(columns, sectionId);
            break;
        case "bar":
            eventBarChart(tags, columns, sectionId);
            break;
        case "pie":
            eventPieChart(tags, columns, sectionId, tagNames);
            break;
    }
    setTimeout(function (){
    createChartImages();
    }, 1000);
}

function createEventBarChartData(tags, arr, sectionId, groupBy) {
    var tagsArray = tags.split(",");
    var tempo = {};
    for (var i = 0; i < arr.length; i++) {
        var o = arr[i];
        var tempTag = [];
        for (var j = 0; j < tagsArray.length; j++) {
            if (o.Tags.indexOf(tagsArray[j]) !== -1) {
                tempTag.push(tagsArray[j]);
            }
        }

        if (groupBy === "machine" || groupBy === "Machine") {
            var entered = o.machine;
        } else if (groupBy === "site" || groupBy === "Site") {
            var entered = o.customer;
        } else if (groupBy === "dart") {
            var entered = o.scrip;
        } else if (groupBy === "date") {
            if (o.enteredDate !== undefined) {
                var entered = o.enteredDate.split('T')[0];
            } else {
                var entered = o.servertime.split('T')[0];
            }

        }

        if (!tempo[entered]) {
            tempo[entered] = {};
            $.each(tagsArray, function (i, v) {
                tempo[entered][v] = 0;
            });
        }

        $.each(tempTag, function (i, v) {
            tempo[entered][v] = ++tempo[entered][v];
        });
    }
    return tempo;
}

function createEventPieChartData(tags, arr, sectionId, groupBy) {
    var tagsArray = tags.split(",");
    var tempo = {};
    for (var i = 0; i < arr.length; i++) {
        var o = arr[i];
        var tempTag = [];
        for (var j = 0; j < tagsArray.length; j++) {
            if (o.Tags.indexOf(tagsArray[j]) !== -1) {
                tempTag.push(tagsArray[j]);
            }
        }

        if (groupBy === "machine" || groupBy === "Machine") {
            var entered = o.machine;
        } else if (groupBy === "site" || groupBy === "Site") {
            var entered = o.customer;
        } else if (groupBy === "dart") {
            var entered = o.scrip;
        } else if (groupBy === "date") {
            var entered = o.enteredDate.split('T')[0];
        }
        entered = entered.split("__")[0];
        if (!tempo[entered]) {
            tempo[entered] = {};
            $.each(tagsArray, function (i, v) {
                tempo[entered][v] = 0;
            });
        }

        $.each(tempTag, function (i, v) {
            tempo[entered][v] = ++tempo[entered][v];
        });
    }
    return tempo;
}

function eventBarChart(tags, columns, sectionId) {
    var lable = true;
    if (tags.length > 0) {
        lable = false;
    }
    var graphWidth = getBarGraphWidth();
    var chart = c3.generate({
        bindto: '#Events_Graph_' + sectionId,
        data: {
            "x": "entered",
            columns: columns,
            type: 'bar',
            groups: [tags],
            labels: lable
        },
        //subchart: {show: true},
        legend: {position: 'right'},
        bar: {
            width: {
                ratio: graphWidth // this makes bar width 50% of length between ticks
            }
        },
        tooltip: {
            format: {
                value: function (value, ratio, id) {
                    var format = d3.format(' ');
                    return format(value);
                }
            }
        },
        grid: {
            focus: {
                show: false
            }
        },
        axis: {
            x: {
                type: 'category'
                        // tick: {
                        //     rotate: 45
                        // }
                }
            }
    });
}

function eventPieChart(tempobj, pieColumns, sectionId, tagNames) {
    var chart = c3.generate({
        bindto: '#Events_Graph_' + sectionId,
        data: {
            columns: pieColumns,
            type: 'pie',
            groups: [pieColumns]
        },
        tooltip: {
            format: {
                value: function (value, ratio, id) {
                    var str = "";
                    $.each(tempobj[id], function (k, v) {
                        str += tagNames[k] + ": " + v + " \n";
                    });
                    return str; //format(value);
                }
            }
        },
        legend: {
            position: 'right'
        },
        pie: {
            label: {
                format: function (value, ratio, id) {
                    return ""; //d3.format(' ')(value);
                }
            }
        }
    });
    setTimeout(function (){
    createChartImages();
    }, 1000);
}

function eventLineChart(columns, sectionId) {
    var chart = c3.generate({
        bindto: '#Events_Graph_' + sectionId,
        data: {
            "x": "entered",
            columns: columns,
            type: 'line',
            labels: true
        },
        //subchart: {show: true},
        legend: {
            position: 'right'
        },
        grid: {
            focus: {
                show: true
            }
        },
        axis: {
            x: {
                type: 'category',
                            tick: {
                                rotate: 27
                }
            }
        }
    });
    d3.selectAll("path").attr("fill", "none");
    d3.selectAll(".tick line, path.domain").attr("stroke", "black");
}

function getBarGraphWidth() {
    var parent = $("#searchType").val();
    var child = $("#searchValue").val();
    var graphWidth = 0.5;
    if (parent === "Sites" && child === "All") {
        graphWidth = 0.20;
    } else if (parent === "Sites" && child !== "All") {
        graphWidth = 0.08;
    } else if (parent === "ServiceTag") {
        graphWidth = 0.5;
    }
    return graphWidth;
}

/*** Event Data rendering End ***/

/*** Asset Data rendering Start ***/

function renderAssetSectionData(sectionData, sectionId, sectionName, chartType, repId, gridEnalbed) {
    var groupBy = ""
    var groupQuery = "";
    if (sectionData !== undefined) {
        var res = sectionData[0][3].split("###");
        groupBy = res[0];
        groupQuery = res[1];
    }

    var sectionDivId = 'Assets_' + sectionId;
    $("#" + sectionDivId).remove();
    var assetDiv = '<li id="Assets_' + sectionId + '" ><div class="report-wrap customscroll box" style="margin-bottom:3% !important"><div class="left"><h5>' + sectionName + '</h5></div></div></li>';
    if ($('#sectionDetailsUL li').length === 0) {
        $("#sectionDetailsUL").html(assetDiv);
    } else {
        if ($('#' + sectionDivId).length === 0)
        {
            $("#sectionDetailsUL").append(assetDiv);
        }
    }

    $.ajax({
        url: "reportDataFun.php?function=1&functionToCall=getAssetSectionDetails",
        type: 'POST',
        data: 'repId=' + repId + '&sectionId=' + sectionId + '&sectionType=2&sectionData=' + sectionData +"&csrfMagicToken=" + csrfMagicToken,
        success: function (reportSections) {
           
            var assetdata = JSON.parse(reportSections);

            if (chartType == "0") {

            } else {
                drawAssetCharts(assetdata.graph, sectionId, assetdata, groupBy, groupQuery, chartType);
            }

            if (gridEnalbed == "0") {

            } else {
                drawAssetTable(sectionId, assetdata);
            }
        },
        error: function (err) {
            console.log('Error  : ' + err);
        }
    });
    $('#sectionDetailsUL').mCustomScrollbar('update');
    $('#sectionDetailsUL').css('height', '');

}

function drawAssetCharts(graphArray, sectionId, assetdata, groupBy, groupQuery, chartType) {
    var tags = [];
    var tagsArray = [];

    $.each(graphArray, function (k, v) {
        tagsArray.push(k.split("__")[0]);
    });
    $('<div class="graph-div"><div id="Assets_Graph_' + sectionId + '" class="graphDivs"></div></div>').appendTo("#Assets_" + sectionId + " .left");
    if (groupQuery === "Machine Name") {
        columns = getSingleQueryData(graphArray, sectionId);
        renderAssetGraphs(chartType, tagsArray, columns, sectionId,assetdata.graph);
    } else {

        var finalColumns = getSiteClubedData(tagsArray, sectionId, assetdata, groupBy);
        var columns = [['entered']];
        $.each(tagsArray, function (i, v) {
            columns.push([v]);
        });

        $.each(finalColumns, function (k, v) {
            columns[0].push(k.split("__")[0]);
            var a = [];
            a.push(k);
            $.each(tagsArray, function (i, val) {
                columns[i + 1].push(v[val]);
                a.push(v[val]);
            });
        });
        renderAssetGraphs(chartType, tagsArray, columns, sectionId,assetdata.graph)
    }


    setTimeout(function () {
        $(".loader-div").hide();
        $(".graph-area, .table-area").show();
    }, 200);

}

function getSingleQueryData(graphArray, sectionId) {
    var columns = ['Machines'];
    var tags = ['entered'];
    var finalColumns = [];
    var graphLength = Object.keys(graphArray).length;
    if (graphLength > 0) {
        for (y in graphArray) {
            columns.push(graphArray[y]);
        }
        $.each(graphArray, function (k, v) {
            tags.push(k);
        });

        finalColumns.push(tags);
        finalColumns.push(columns);
    }
    return finalColumns;
}

function getSiteClubedData(tagsArray, sectionId, assetdata, groupBy) {
    var arr = assetdata.clubbed;
    var compareDataId = assetdata[0].details.dataId;
    var tempo = {};
    for (var i = 0; i < arr.length; i++) {
        var o = arr[i];
        var tempTag = [];
        for (var j = 0; j < tagsArray.length; j++) {
            if(o[compareDataId] === 'undefined' || o[compareDataId] === undefined) {
                
            } else {
            if (o[compareDataId].indexOf(tagsArray[j]) !== -1) {
                tempTag.push(tagsArray[j]);
            }
        }
        }
        var siteNameDataId = getDataId(assetdata, groupBy);
        var entered = o[siteNameDataId];


        if (!tempo[entered]) {
            tempo[entered] = {};
            $.each(tagsArray, function (i, v) {
                tempo[entered][v] = 0;
            });
        }

        $.each(tempTag, function (i, v) {
            tempo[entered][v] = ++tempo[entered][v];
        });
    }
    return tempo;
}
function renderAssetGraphs(type, tags, columns, sectionId,graph) {

    switch (type) {
        case "1":
            assetBarChart(tags, columns, sectionId);
            break;
        case "3":
            assetLineChart(columns, sectionId);
            break;
        case "2":
//            pieChart(tags, columns,sectionId,graph);
            assetPieChart(graph, columns, sectionId, tags);
            break;
        case "4":
            areaChart(columns);
            break;
    }
    setTimeout(function () {
    createChartImages();
    }, 1000);

}

function assetBarChart(tags, columns, sectionId) {
    //alert('hi');
    var lable = true;
    if (tags.length > 0) {
        lable = false;
    }
    var graphWidth = getBarGraphWidth();
    var chart = c3.generate({
        bindto: '#Assets_Graph_' + sectionId,
        data: {
            x: 'entered',
            columns: columns,
            type: 'bar',
            groups: [tags],
            labels: lable

        },
        legend: {position: 'right'},
        bar: {
            width: {
                ratio: graphWidth // this makes bar width 50% of length between ticks
            }
        },
        tooltip: {
            format: {
                value: function (value, ratio, id) {
                    var format = d3.format(' ');
                    return format(value);
                }
            }
        },
        grid: {
            focus: {
                show: false
            }
        },
        axis: {
            x: {
                type: 'category',
                            tick: {
                                rotate: 27
                        }
            },
            y: {
                min: 0,
                tick: {
                    format: d3.format('d')
                },
                padding: {top: 0, bottom: 0}
                }
            }
    });
}

function assetLineChart(columns, sectionId) {
    var chart = c3.generate({
        bindto: '#Assets_Graph_' + sectionId,
        data: {
            "x": "entered",
            columns: columns
        },
        //subchart: {show: true},
        legend: {
            position: 'right'
        },
        grid: {
            focus: {
                show: true
            }
        },
        axis: {
            x: {
                type: 'category',
                         tick: {
                             rotate: 27
                }
            }
        }
    });
}

function assetPieChart(tempobj, pieColumns, sectionId, tagNames) {

    var col = [];
    var tag = {};
    for(ind in tempobj) {
        var val = ind.split("__")[0];
        col.push([val,tempobj[ind]]);
        tag[ind] = val;
    }
    
    var chart = c3.generate({
        bindto: '#Assets_Graph_' + sectionId,
        data: {
            columns: col,
            type: 'pie',
            groups: [col]
        },
        tooltip: {
            format: {
                value: function (value, ratio, id) {
//                    var str = "";
//                    $.each(tempobj[id], function (k, v) {
//                        str += tag[k] + ": " + v + " \n";
//                    });
//                    return str; //format(value);
                    var format = d3.format(' ');
                    return format(value);
                }
            }
        },
        legend: {
            position: 'right'
        },
        pie: {
            label: {
                format: function (value, ratio, id) {
                    return ""; //d3.format(' ')(value);
                }
            }
        }
    });
}

function drawAssetTable(sectionId, assetData) {
    var columns = "";

//    for (k in assetData[0].details.columns) {
//        columns += "<th>" + k + "</th>";
//    }

    $("#Assets_" + sectionId + " .report-wrap").append('<div class="report_table"><table class="dt-responsive hover order-table1 nowrap" id="Assets_Table_' + sectionId + '" width="100%" data-page-length="25"><thead><tr>' + columns + '</tr></thead></table></div>');
    loadAssetTableData(sectionId, assetData);
}

function loadAssetTableData(sectionId, assetData) {
    var columnNames = get_uniqueColumn(assetData);
    var gridassetdata = loopdata(assetData, columnNames, sectionId);

    $('#Assets_Table_' + sectionId).DataTable().destroy();
    $('#Assets_Table_' + sectionId).DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        responsive: true,
        data: gridassetdata,
        searching: false,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
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

function loopdata(assetdata, unique1, sectionId) {

    var column = 0;
    var temp = [];
    var data = [];
    var i = 0;
    var dataColumns = [];
    var sections = assetdata;
    var newcol = [];
    var machineNameDataId = getDataId(sections, "Machine Name");
    var siteNameDataId = getDataId(sections, "Site Name");

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
                                var test = t.replace(/[^a-zA-Z0-9-_\.]/,'');
                                var temp = test.toLowerCase();
                                var jdata = value[temp];
                                var arr = [];

                                //console.log("JOHN : " + jdata);

                                if (jdata === undefined || jdata === 'undefined') {
                                    var link = '<p class="ellipsis" title="" style="">-</p>';
                                    final.push(link);
                                } else {
                                    var link = "";
                                        if (jdata == '-') {
                                            link += '<p class="ellipsis" title="" style="">' + jdata + '</p>';
                                        } else {
                                            var data = jdata.split('__')[0];
                                            link += '<p class="ellipsis" title="' + data + '">' + data + '</p>';
                                        }
                                    if (s == machineNameDataId) {   // To bring the machine name at first
                                        final.unshift(link);
                                    } else if (s == siteNameDataId) {
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
        if (val != "Machine Name") {
            if (val != "Site Name") {
                content += "<th>" + val + "</th>";
            } else {
                siteContent += "<th>" + val + "</th>";
            }
        } else {
            if (val == "Machine Name") {
                macContent = "<th>" + val + "</th>";
            }
        }
    });
    $('#Assets_Table_' + sectionId + '>thead>tr').html(macContent + "" + siteContent + "" + content);
//    $("#assetDetail>thead>tr").html(macContent + "" + siteContent + "" + content);

    for (var l in temp) {
        data.push(temp[l]);
    }
    return data;
}

function getDataId(sections, dataName) {
    var dataId = "";
    var i = 0;
    $.each(sections, function (key, val) {
        $.each(val, function (k, v) {
            if (k === 'details') {
                $.each(v, function (f, e) {
                    if (f === 'columns') {
                        $.each(e, function (g, h) {
                            if (g === dataName) {
                                dataId = h;
                            }
                        });
                    }
                });
            }
        });
        i++;
    });
    return dataId;
}

/*** Asset Data rendering End ***/

function renderMUMSectionData(sectionData, sectionId, sectionName, chartType, gridEnalbed) {
    createSection_HTML(sectionId, sectionName, "MUM");
    var groupBy = "site"
    var cateBy = "type"
    var groupCate = [];
    if (sectionData !== undefined) {
        groupCate = sectionData[0][3].split("###");
        groupBy = groupCate[0];
        cateBy = groupCate[1];
    }

    $.ajax({
        url: "reportDataFun.php?function=1&functionToCall=getMUMSectionDetails",
        type: 'POST',
        data: 'sectionId=' + sectionId + '&sectionType=3&sectionId=' + sectionId +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function (reportSections) {
            var mumData = reportSections.details;
            if (chartType == "0") {

            } else {
                renderMUM_Graphs(reportSections, sectionId, groupBy, cateBy, chartType, gridEnalbed);
            }

            if (gridEnalbed == "0") {

            } else {
                renderMUM_Table(mumData, sectionId);
            }

        },
        error: function (err) {
            console.log('Error  : ' + err);
        }
    });
    $('#sectionDetailsUL').mCustomScrollbar('update');
    $('#sectionDetailsUL').css('height', '');
}

function createSection_HTML(sectionId, sectionName, sectionType) {
    var sectionDivId = sectionType + '_' + sectionId;
    $("#" + sectionDivId).remove();
    var htmlDiv = '<li id="' + sectionDivId + '"><div class="report-wrap customscroll box" style="margin-bottom:3% !important"><div class="left"><h5>' + sectionName + '</h5></div></div></li>';
    if ($('#sectionDetailsUL li').length === 0) {
        $("#sectionDetailsUL").html(htmlDiv);
    } else {
        if ($('#' + sectionDivId).length === 0)
        {
            $("#sectionDetailsUL").append(htmlDiv);
        }
    }
    return true;
}

function renderMUM_Graphs(reportSections, sectionId, groupBy, categorizeBy, chartType, gridEnalbed) {
    loadSectionGraph_HTML(sectionId, "MUM");
    var typeArray = ["Update", "Critical", "Security", "Roll Up", "Service Pack", "Others"];
    var statusArray = ["Installed", "Downloaded", "Detected", "Superseded", "Waiting", "Others"];
    var tagsArray = [];
    var finalColumns = [];
    if (categorizeBy == "host") {
        columns = getSingleCategoryData(reportSections.graph, sectionId, "Machine");
        tagsArray = [];
    } else {
        if (categorizeBy == "type") {
            tagsArray = typeArray;
        } else if (categorizeBy == "status") {
            tagsArray = statusArray;
        }
        finalColumns = mum_ClubedData(sectionId, reportSections.details, groupBy, categorizeBy, tagsArray);
        var columns = [['entered']];
        $.each(tagsArray, function (i, v) {
            columns.push([v]);
        });

        $.each(finalColumns, function (k, v) {
            columns[0].push(k);
            var a = [];
            a.push(k);
            $.each(tagsArray, function (i, val) {
                columns[i + 1].push(v[val]);
                a.push(v[val]);
            });
        });
    }
    if (chartType == "2" && categorizeBy == "host") {   //Data format is different for PIE chart
        var pieCols = [];
        $.each(reportSections.graph, function (k, v) {
            var a = [k, v];
            pieCols.push(a);
        });
        renderSectionGraphs(chartType, tagsArray, pieCols, sectionId, "MUM");
    } else {
        renderSectionGraphs(chartType, tagsArray, columns, sectionId, "MUM");
    }



}

function renderSectionGraphs(type, tags, columns, sectionId, sectionType) {
    switch (type) {
        case "1":
            drawBarChart(tags, columns, sectionId, sectionType);
            break;
        case "2":
            pieChart(columns, sectionId, sectionType);
            break;
        case "3":
            drawLineChart(columns, sectionId, sectionType);
            break;
        case "4":
            areaChart(columns);
            break;
    }
}

function mum_ClubedData(sectionId, mumdata, groupBy, categorizeBy, tagsArray) {
    var tempo = {};
    for (var i = 0; i < mumdata.length; i++) {
        var o = mumdata[i];
        var tempTag = [];

        if (categorizeBy == "site") {
            var rowVal = o.site;
        } else if (categorizeBy == "status") {
            var rowVal = o.status;
        } else if (categorizeBy == "type") {
            var rowVal = o.type;
        } else if (categorizeBy == "size") {
            var rowVal = o.size;
        }

        for (var j = 0; j < tagsArray.length; j++) {
            if (rowVal.indexOf(tagsArray[j]) !== -1) {
                tempTag.push(tagsArray[j]);
            }
        }

        if (groupBy == "site") {
            var entered = o.site;
        } else if (groupBy == "status") {
            var entered = o.status;
        } else if (groupBy == "type") {
            var entered = o.type;
        } else if (groupBy == "size") {
            var entered = o.size;
        }


        if (!tempo[entered]) {
            tempo[entered] = {};
            $.each(tagsArray, function (i, v) {
                tempo[entered][v] = 0;
            });
        }

        $.each(tempTag, function (i, v) {
            tempo[entered][v] = ++tempo[entered][v];
        });
    }

    return tempo;
}

function renderMUM_Table(mumdata, sectionId) {
    var griddata = [];
    var columns = "<th>Machine</th><th>Site Name</th><th>Patch Name</th><th>Detected Date</th><th>Status</th><th>Patch Type</th>";
    loadSectionDataTable_HTML(sectionId, columns, "MUM");

    $.each(mumdata, function (key, val) {
        griddata.push(['<p class="ellipsis" title="' + mumdata[key].host + '">' + mumdata[key].host + '</p>', '<p class="ellipsis" title="' + mumdata[key].site + '">' + mumdata[key].site + '</p>', '<p class="ellipsis" title="' + mumdata[key].patchname + '">' + mumdata[key].patchname + '</p>', '<p class="ellipsis" title="' + mumdata[key].detected + '">' + mumdata[key].detected + '</p>', '<p class="ellipsis" title="' + mumdata[key].status + '">' + mumdata[key].status + '</p>', '<p class="ellipsis" title="' + mumdata[key].type + '">' + mumdata[key].type + '</p>']);
    });
    if (griddata.length === mumdata.length) {
        loadSectionDataTable(sectionId, griddata, "MUM");
    }
}

function renderNotifSectionData(sectionData, sectionId, sectionName, chartType, gridEnalbed) {
    createSection_HTML(sectionId, sectionName, "NOTIF");
    var groupBy = "site";
    var cateBy = "type";
    var groupCate = [];
    if (sectionData !== undefined) {
        groupCate = sectionData[0][3].split("###");
        groupBy = groupCate[0];
        cateBy = groupCate[1];
    }

    $.ajax({
        url: "reportDataFun.php?function=1&functionToCall=getNotifSectionDetails",
        type: 'POST',
        data: 'sectionId=' + sectionId + '&sectionType=9&sectionId=' + sectionId +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function (reportSections) {
            var notifData = reportSections.details;
            if (chartType == "0") {

            } else {
                renderNotif_Graphs(reportSections, sectionId, groupBy, cateBy, chartType, gridEnalbed);
            }

            if (gridEnalbed == "0") {

            } else {
                renderNotif_Table(notifData, sectionId);
            }

        },
        error: function (err) {
            console.log('Error  : ' + err);
        }
    });
    $('#sectionDetailsUL').mCustomScrollbar('update');
    $('#sectionDetailsUL').css('height', '');
}

function renderNotif_Graphs(reportSections, sectionId, groupBy, categorizeBy, chartType, gridEnalbed) {
    var columns = [];
    loadSectionGraph_HTML(sectionId, "NOTIF");
    var statusArray = [];
    var typeArray = ["Critical", "Major", "Minor"];
    var tagArray = "";
    if (categorizeBy == "host") {
        columns = getSingleCategoryData(reportSections.graph, sectionId, "Machine");
        tagArray = [];
    } else {
        if (categorizeBy == "type") {
            tagArray = typeArray;
        } else if (categorizeBy == "status") {
            tagArray = statusArray;
        }

        var finalColumns = notif_ClubedData(sectionId, reportSections.details, groupBy, categorizeBy, tagArray);
        var columns = [['entered']];
        $.each(tagArray, function (i, v) {
            columns.push([v]);
        });

        $.each(finalColumns, function (k, v) {
            columns[0].push(k);
            var a = [];
            a.push(k);
            $.each(tagArray, function (i, val) {
                columns[i + 1].push(v[val]);
                a.push(v[val]);
            });
        });
    }

    renderSectionGraphs(chartType, tagArray, columns, sectionId, "NOTIF");
}

function notif_ClubedData(sectionId, reportSections, groupBy, categorizeBy, tagsArray) {
    var tempo = {};
    var rowVal = "";
    for (var i = 0; i < reportSections.length; i++) {
        var o = reportSections[i];
        var tempTag = [];
        if (categorizeBy == "type") {
            rowVal = o.type;
        } else if (categorizeBy == "status") {
            rowVal = o.status;
        }


        for (var j = 0; j < tagsArray.length; j++) {
            if (rowVal.indexOf(tagsArray[j]) !== -1) {
                tempTag.push(tagsArray[j]);
            }
        }

        var entered = "";

        if (groupBy == "type") {
            entered = o.type;
        } else if (groupBy == "status") {
            entered = o.status;
        } else if (groupBy == "site") {
            entered = o.sitename;
        }

        if (!tempo[entered]) {
            tempo[entered] = {};
            $.each(tagsArray, function (i, v) {
                tempo[entered][v] = 0;
            });
        }

        $.each(tempTag, function (i, v) {
            tempo[entered][v] = ++tempo[entered][v];
        });
    }

    return tempo;
}


function renderSummarySectionData(subSectionData, sectionId, sectionName, chartType, repId, gridEnalbed) {
    createSection_HTML(sectionId, sectionName, "SUMM");
    var html = '<div class="graph-div"><div id="SUMM' + '_Graph_' + sectionId + '" style="max-height: 430px !important; position: relative;" class="graphDivs col-lg-12 col-md-12 col-sm-12 col-xs-12"></div></div>';
    $("#SUMM_" + sectionId + " .left").append(html);

    $.ajax({
        url: "reportDataFun.php?function=1&functionToCall=getSummarySectionDetails",
        type: 'POST',
        data: 'sectionId=' + sectionId + '&sectionType=8' +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json',
        success: function (reportSections) {
            var tableStr = reportSections.tableStr;
            $("#SUMM_" + sectionId + " .report-wrap").append(tableStr);
            var columns = reportSections.data;

            for (var j = 0; j < columns.length; j++) {
                $('#SUMM' + '_Graph_' + sectionId).append('<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12" id="SUMM_Graph_' + sectionId + '_' + j + '"><div>');

                gaugeChart(columns[j], sectionId, "SUMM", j);
            }
        },
        error: function (err) {
            console.log('Error  : ' + err);
        }
    });
    $(".mcs-horizontal-example").mCustomScrollbar({
        axis: "x",
        theme: "dark-3"
    });
    $('#sectionDetailsUL').mCustomScrollbar('update');
    $('#sectionDetailsUL').css('height', '');
}



/**Table Code start**/

function loadSectionDataTable_HTML(sectionId, columns, sectionType) {
    var html = '<div class="report_table"><table class="dt-responsive hover order-table1 nowrap" id="' + sectionType + '_Table_' + sectionId + '" width="100%" data-page-length="25"><thead><tr>' + columns + '</tr></thead></table></div>';
    $("#" + sectionType + "_" + sectionId + " .report-wrap").append(html);
}

function loadSectionDataTable(sectionId, griddata, sectionType) {
    $('#' + sectionType + '_Table_' + sectionId).DataTable().destroy();
    $('#' + sectionType + '_Table_' + sectionId).DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        responsive: true,
        data: griddata,
        searching: false,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
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
    return true;
}

function renderNotif_Table(notifdata, sectionId) {
    var griddata = [];
    var columns = "<th>Machine</th><th>Site Name</th><th>Notification</th><th>Date</th><th>Status</th>";
    loadSectionDataTable_HTML(sectionId, columns, "NOTIF");

    $.each(notifdata, function (key, val) {
        griddata.push(['<p class="ellipsis" title="' + notifdata[key].machine + '">' + notifdata[key].machine + '</p>', '<p class="ellipsis" title="' + notifdata[key].sitename + '">' + notifdata[key].sitename + '</p>', '<p class="ellipsis" title="' + notifdata[key].nocname + '">' + notifdata[key].nocname + '</p>', '<p class="ellipsis" title="' + notifdata[key].date + '">' + notifdata[key].date + '</p>', '<p class="ellipsis" title="' + notifdata[key].status + '">' + notifdata[key].status + '</p>']);
    });
    if (griddata.length === notifdata.length) {
        loadSectionDataTable(sectionId, griddata, "NOTIF");
    }
}

/**Table Code end**/


/**Graph Code start**/

function loadSectionGraph_HTML(sectionId, sectionType) {
    var html = '<div class="graph-div"><div id="' + sectionType + '_Graph_' + sectionId + '" style="max-height: 430px !important; position: relative;" class="graphDivs"></div></div>';
    $("#" + sectionType + "_" + sectionId + " .left").append(html);
    return true;
}

//when catogorized by is Machine or Site, then required format for graph is implemented on backend only
function getSingleCategoryData(graphArray, sectionId, groupName) {
    var columns = [groupName];
    var tags = ['entered'];
    var finalColumns = [];
    var graphLength = Object.keys(graphArray).length;
    if (graphLength > 0) {
        for (y in graphArray) {
            columns.push(graphArray[y]);
        }
        $.each(graphArray, function (k, v) {
            tags.push(k);
        });

        finalColumns.push(tags);
        finalColumns.push(columns);
    }
    return finalColumns;
}

function drawBarChart(tags, columns, sectionId, sectionType) {
    var lable = true;
    if (tags.length > 0) {
        lable = false;
    }
    var graphWidth = getBarGraphWidth();
    var chart = c3.generate({
        bindto: '#' + sectionType + '_Graph_' + sectionId,
        data: {
            x: 'entered',
            columns: columns,
            type: 'bar',
            groups: [tags],
            labels: lable

        },
        legend: {position: 'right'},
        bar: {
            width: {
                ratio: graphWidth // this makes bar width 50% of length between ticks
            }
        },
        tooltip: {
            format: {
                value: function (value, ratio, id) {
                    var format = d3.format(' ');
                    return format(value);
                }
            }
        },
        grid: {
            focus: {
                show: false
            }
        },
        axis: {
            x: {
                type: 'category',
                        tick: {
                            rotate: 27
                }
            }
        }
    });
    setTimeout(function (){
    createChartImages();
    }, 1000);
}

function drawLineChart(columns, sectionId, sectionType) {
    var chart = c3.generate({
        bindto: '#' + sectionType + '_Graph_' + sectionId,
        data: {
            "x": "entered",
            columns: columns
        },
        legend: {
            position: 'right'
        },
        grid: {
            focus: {
                show: true
            }
        },
        axis: {
            x: {
                type: 'category',
                        tick: {
                             rotate: 27
                }
            }
        }
    });
    setTimeout(function (){
    createChartImages();
    }, 1000);
}

function pieChart(pieColumns, sectionId, sectionType) {

    var chart = c3.generate({
        bindto: '#' + sectionType + '_Graph_' + sectionId,
        data: {
            columns: pieColumns,
            type: 'pie'
        },
        tooltip: {
            format: {
                value: function (value, ratio, id) {
                    var str = "";
                    $.each(pieColumns, function (k, v) {
                        str += v[0] + ": " + v[1] + " \n";
                    });
                    return str; //format(value);
                }
            }
        },
        legend: {
            position: 'right'
        },
        pie: {
            label: {
                format: function (value, ratio, id) {
                    return d3.format('')(value);
                    //format(value);
                }
            }
        }
    });
    setTimeout(function (){
    createChartImages();
    }, 1000);
}

function gaugeChart(pieColumns, sectionId, sectionType, gaugeNumber) {
    var chart = c3.generate({
        bindto: '#' + sectionType + '_Graph_' + sectionId + '_' + gaugeNumber,
        data: {
            columns: [
                pieColumns
            ],
            type: 'gauge'
        },
        legend: {
            show: true
        },
        gauge: {
        },
        color: {
            pattern: ['#FF0000', '#F97600', '#F6C600', '#60B044'], // the three color levels for the percentage values.
            threshold: {
                values: [30, 60, 90, 100]
            }
        },
        size: {
            height: 180
        }
    });
    //createChartImages();
}

/**Graph Code ends**/




function exportViewToExcel() {
    var viewId = $("#allReports .active").attr("id");
    window.location.href = "../lib/l-exportViews.php?viewId=" + viewId + "&exportType=1" +"&csrfMagicToken=" + csrfMagicToken;
}

function exportViewToPdf() {
    var viewId = $("#allReports .active").attr("id");
    window.location.href = "../lib/l-exportViews.php?viewId=" + viewId + "&exportType=2" +"&csrfMagicToken=" + csrfMagicToken;
}

function createChartImages() {
    var selector = $('.graphDivs').length;
    var i = 0;
    $(".graphDivs").each(function () {
        var graphDivId = $(this).attr("id");
        var eventDiv = '<li class="canvas_li"><div class="report-wrap customscroll box" style="margin-bottom:3% !important"><canvas id="canvas"></canvas></div></li>';
        $("#sectionDetailsUL").append(eventDiv);

        var svgString = new XMLSerializer().serializeToString(document.querySelector('#' + graphDivId + ' > svg'));
//        var canvas = document.getElementById("canvas");
//        var ctx = canvas.getContext("2d");
        var DOMURL = self.URL || self.webkitURL || self;
        var img = new Image();
        var svg = new Blob([svgString], {type: "image/svg+xml;charset=utf-8"});
        var url = DOMURL.createObjectURL(svg);
        img.onload = function () {
             var canvas = d3.select('body').append('canvas').node();
             canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            var ctx = canvas.getContext('2d');
            
            ctx.drawImage(img, 0, 0);
            var png = canvas.toDataURL("image/png");
//            DOMURL.revokeObjectURL(png);
            prepareImage(png, graphDivId);
//            ctx.setTransform(1, 0, 0, 1, 0, 0);
            //ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.canvas.width = window.innerWidth;
            //ctx.canvas.height = window.innerHeight;
            //ctx.restore();
        };
        img.src = url;
        i += 1;
    });
}

/*function createChartImages() {
    var selector = $('.graphDivs').length;
    console.log(selector);
    var i = 0;
    $(".graphDivs").each(function () {
        var doctype = '<?xml version="1.0" standalone="no"?>'
                + '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">';
        var graphDivId = $(this).attr("id");
        console.log(graphDivId);
        var eventDiv = '<li class="canvas_li"><div class="report-wrap customscroll box" style="margin-bottom:3% !important"><canvas id="canvas"></canvas></div></li>';
        $("#sectionDetailsUL").append(eventDiv);
        var svgString = (new XMLSerializer()).serializeToString(d3.select('svg').node());
        var blob = new Blob([doctype + svgString], {type: "image/svg+xml;charset=utf-8"});
        var url = window.URL.createObjectURL(blob);
        console.log(url);
        var img = new Image();
        img.src = "data:image/svg+xml;base64," + btoa(svgString);
        img.onload = function () {
            var canvas = d3.select('body').append('canvas').node();
            //canvas.width = 1000;
            //canvas.height = 800;
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            var ctx = canvas.getContext('2d');
            //ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.drawImage(img, 0, 0);
            canvasUrl = canvas.toDataURL("image/png").replace("image/png", "image/octet-stream");
            //var a = document.createElement("a");
            //a.download = "sample.png";
            //a.href = canvasUrl;
            //a.click();
            prepareImage(canvasUrl, graphDivId);
            canvas.remove();
        };
        img.src = url;
        i += 1;
        canvasUrl = '';
    });
}*/

function prepareImage(png, graphDivId) {
    $('#sectionDetailsUL .canvas_li').remove();
    $.ajax({
        url: "reportDataFun.php?function=1&functionToCall=prepareImage",
        type: 'POST',
        data: {
            base64: png,
            graphDivId: graphDivId,
            csrfMagicToken: csrfMagicToken
        },
        success: function (reportSections) {

        },
        error: function (err) {
            console.log('Error  : ' + err);
        }
    });
    return true;
}


$('#edit_managed-report').on('hidden.bs.modal', function () {
    $("#edit_managed-report .edit_sumSecData .summarySec").remove();
});

function back() {
    window.history.back();
}
