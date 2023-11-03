/*
 * Function for rendering the Chart and Grid Details.
 */

function barChart(tags, columns) {
    var chart = c3.generate({
        bindto: '#barChart',
        data: {
            "x": "entered",
            columns: columns,
            type: 'bar',
            groups: [tags]
        },
        //subchart: {show: true},
        legend: {position: 'right'},
        transition: {duration: 200},
        bar: {
            width: {
                ratio: 0.5 // this makes bar width 50% of length between ticks
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
        axis: {
            x: {
                type: 'timeseries',
                tick: {
                    format: "%b-%d",
                    rotate: 45
                }
            }
        }
    });
}

function mumBarChart(columns) {

    console.log('Bar columns : ' + JSON.stringify(columns));
    var chart = c3.generate({
        bindto: '#mumBarChart',
        data: {
            "x": "Name",
            columns: columns,
            type: 'bar'
        },
        legend: {position: 'right'},
        bar: {
            width: {
                ratio: 0.5 // this makes bar width 50% of length between ticks
            }
            // or
            //width: 100 // this makes bar width 100px
        },
        tooltip: {
            format: {
                value: function (value, ratio, id) {
                    var format = d3.format(' ');
                    return format(value);
                }
            }
        },
        axis: {
            x: {
                type: 'category',
                tick: {
                    rotate: 45
                }
            }
        }
    });
}

function pieChart(tempobj, pieColumns) {
    var chart = c3.generate({
        bindto: '#pieChart',
        data: {
            columns: pieColumns,
            type: 'pie'
        },
        tooltip: {
            format: {
                value: function (value, ratio, id) {
                    var str = "";
                    $.each(tempobj[id], function (k, v) {
                        str += k + ": " + v + " ";
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
}

function mumPieChart(pieColumns) {
    var chart = c3.generate({
        bindto: '#mumPieChart',
        data: {
            columns: pieColumns,
            type: 'pie'
        },
        tooltip: {
            format: {
                value: function (value, ratio, id) {
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

function lineChart(columns) {
    var chart = c3.generate({
        bindto: '#lineChart',
        data: {
            "x": "entered",
            columns: columns
        },
        legend: {
            position: 'right'
        },
        axis: {
            x: {
                type: 'timeseries',
                tick: {
                    format: "%b-%d",
                    rotate: 45
                }
            }
        }
    });
}

function mumLineChart(columns) {
    var chart = c3.generate({
        bindto: '#mumLineChart',
        data: {
            "x": "Name",
            columns: columns
        },
        legend: {
            position: 'right'
        },
        axis: {
            x: {
                type: 'category',
                tick: {
                    rotate: 45
                }
            }
        }
    });
}


function assetPieChart(pieColumns) {

    var chart = c3.generate({
        bindto: '#assetPieChart',
        data: {
            columns: pieColumns,
            type: 'pie'
        },
        tooltip: {
            format: {
                value: function (value, ratio, id) {
                    var format = d3.format(' ');
                    return format(value);
                }
            }
        },
        legend: {
            position: 'left'
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

function assetBarChart(columns) {

    var chart = c3.generate({
        bindto: '#assetBarChart',
        data: {
            columns: columns,
            type: 'bar'
        },
        legend: {position: 'right'},
        bar: {
            width: {
                ratio: 0.5 // this makes bar width 50% of length between ticks
            }
            // or
            //width: 100 // this makes bar width 100px
        },
        tooltip: {
            format: {
                value: function (value, ratio, id) {
                    var format = d3.format(' ');
                    return format(value);
                }
            }
        },
        axis: {
            x: {
                type: 'category',
                tick: {
                    rotate: 45
                }
            }
        }
    });
}

function drawCharts(tags, arr, type) {
    //tags = tags.split(",");
    var tempo = {};
    for (var i = 0; i < arr.length; i++) {
        var o = arr[i]['_source'];
        var tempTag = [];
        for (var j = 0; j < tags.length; j++) {
            if (o.Tags.indexOf(tags[j]) !== -1) {
                tempTag.push(tags[j]);
            }
        }

        var entered = o.entered.split('T')[0];
        if (!tempo[entered]) {
            tempo[entered] = {};
            $.each(tags, function (i, v) {
                tempo[entered][v] = 0;
            });
        }

        $.each(tempTag, function (i, v) {
            tempo[entered][v] = ++tempo[entered][v];
        });
    }

    console.log(JSON.stringify(tempo));

    var columns = [['entered']];
    var pie1stEle = ['entered'];
    $.each(tags, function (i, v) {
        columns.push([v]);
        pie1stEle.push(v);
    });
    var pieCols = [];
    $.each(tempo, function (k, v) {
        columns[0].push(k);
        var a = [];
        a.push(k);
        var pieFlag = false;
        $.each(tags, function (i, val) {
            columns[i + 1].push(v[val]);
            a.push(v[val]);
            if (v[val] !== 0) {
                pieFlag = true;
            }
        });
        if (pieFlag) {
            pieCols.push(a);
        }
    });

    console.log('Bar : ' + JSON.stringify(columns));
    console.log('Pie : ' + JSON.stringify(pieCols));

    G_DATA.COLUMNS = columns;
    G_DATA.PIECOLUMNS = pieCols;
    G_DATA.Tags = tags;
    G_DATA.TEMPOBJ = tempo;

    $('.report-chart').hide();
    if (type == 'bar') {
        $('.chartbar').show();
        barChart(tags, columns);
    } else if (type == 'pie') {
        $('.chartpie').show();
        pieChart(tempo, pieCols);
    } else if (type == 'line') {
        $('.chartline').show();
        lineChart(columns);
    } else {
        $('.chartbar').show();
        barChart(tags, columns);
    }
}

function drawMumCharts(mumdata, type) {
    var mumGraph = [];

    mumGraph.push(["Name", "Status"])
    for (y in mumdata[0].groupedData) {
        //mumdata[0].details[y].detected
        mumGraph.push([mumdata[0].groupedData[y].name, mumdata[0].groupedData[y].count]);
    }
    $('.report-chart').hide();
    if (type == 'bar') {
        //console.log('MUM Bar : ' + JSON.stringify(mumGraph));
        $('.chartbarmum').show();
        mumBarChart(mumGraph);
    } else if (type == 'pie') {
        $('.chartpiemum').show();
        mumPieChart(mumGraph);
    } else if (type == 'line') {
        $('.chartlinemum').show();
        mumLineChart(mumGraph);
    }
}

function drawAssetCharts(assetdata, type) {
    var assetGraph = [];
    for (y in assetdata) {
        assetGraph.push([y, assetdata[y]]);
    }

    $('.report-chart').hide();
    if (type == 'pie') {
        $('.chartpieasset').show();
        assetPieChart(assetGraph);
    } else if (type == 'bar') {
        $('.chartbarasset').show();
        assetBarChart(assetGraph);
    }
}

function renderGridData(res) {
    var eventData = [];
    for (var i = 0; i < res.length; i++) {
        var o = res[i]['_source'];
        eventData.push([o.scrip, '<p class="ellipsis" title="' + o.machine + '">' + o.machine + '</p>', '<p class="ellipsis" title="' + o.customer + '">' + o.customer + '</p>', '<p class="ellipsis" title="' + o.description + '">' + o.description + '</p>', '<p class="ellipsis" title="' + o.text1 + '">' + o.text1 + '</p>']);
    }

    $('#eventDetail').DataTable().destroy();
    $('#eventDetail').DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        responsive: true,
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
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>'
    });
}

function switchToKibana() {
    var fromdate = $("#fromdate").val();
    var tags = $("#tags").val();
    var sitename = $("#sitename").val();
    var columns = $("#cols").val();

    $.each(sitename, function (i, v) {
        sitename[i] = $.trim(v);
    });
    sitename = sitename.toString();

    $.each(tags, function (i, v) {
        tags[i] = $.trim(v);
    });
    tags = tags.toString();

    var dataGridCols = 'scrip,machine,customer,description,text1,Tags,entered';
    columns = dataGridCols.split(",");
    $.each(columns, function (i, v) {
        columns[i] = $.trim(v);
    });
    columns = columns.toString();

    $('.report-chart, .report_table').hide();

    console.log('FD : ' + fromdate + ' -- Tags : ' + tags + ' -- Sitename : ' + sitename + ' -- Columns : ' + columns);
    newTable("table", fromdate, tags, "site", sitename, columns);
}

function dataGridCall(sitename, filtertags, dataGridCols, type, fromdate) {

    if (type == 'kibana') {
        console.log('Tag Length : ' + filtertags.length);
        $('.errMsg').html('');
        if(filtertags.length > 1) {
            $('.errMsg').html('Mutiple Filter Tags not allowed for Kibana Report!');
            return false;
        }
        $(".se-pre-con").hide();
        switchToKibana();
    } else {
        $('.report-chart').show();
        $('#loadIframe').hide();
        var url = "../kibanaGridDataFunc.php";
        var data = {
            "func": "getAllEvents", "sitename": sitename, "columns": dataGridCols, "tags": filtertags, "nofdays": fromdate,
            'csrfMagicToken': csrfMagicToken};
        $.support.cors = true;
        $.ajax({
            url: url,
            type: "POST",
            data: data,
            dataType: "json",
            success: function (res) {
                $(".se-pre-con").hide();
                G_DATA = {
                    Tags: [],
                    COLUMNS: [],
                    PIECOLUMNS: [],
                    TEMPOBJ: {}
                };

                drawCharts(filtertags, res, type);  // Renders Chart Details

                //renderGridData(res);    // Renders DataTable Grid Information

            }, error: function (err) {
                console.error(JSON.stringify(err)).show();
            }
        });
    }


}

var G_DATA = {
    Tags: [],
    COLUMNS: [],
    PIECOLUMNS: [],
    TEMPOBJ: {}
};

function generateURL() {
    var type = $("#type").val();
    var fromdate = $("#fromdate").val();
    var tags = $("#tags").val();
    var sitename = $("#sitename").val();

    //console.log(type + '-' + fromdate + '-' + tags + '--' + sitename);
    $(".se-pre-con").show();

    type = type ? type : "bar";
    fromdate = fromdate ? fromdate : "90d";
    tags = tags ? tags : "avnotinstalled";
    sitename = sitename ? sitename : "testins__201700032";

    if (secType == 1) {
        var dataGridCols = 'scrip,machine,customer,description,text1,Tags,entered';
        dataGridCall(sitename, tags, dataGridCols, type, fromdate);
    } else if (secType == 2) {
        $('.nomum, .notInAsset').hide();
        getAssetDetails(repid);
    } else if (secType == 3) {
        $('.nomum').hide();
        getMumDetails(repid);
    }
}

function getEventFiltersDetails(filter) {

    var eventOptions = '';
    $.ajax({
        type: "POST",
        url: "../lib/l-mngdRprt.php?function=1&functionToCall=getEventFilters",
        dataType: 'json',
        data: { 'csrfMagicToken': csrfMagicToken }
    }).done(function (data) {
        for (var i = 0; i < data.length; i++) {
            if (filter.indexOf(data[i].etag) != -1) {
                eventOptions += '<option value="' + data[i].etag + '" selected>' + data[i].name + '</option>';
            } else {
                eventOptions += '<option value="' + data[i].etag + '">' + data[i].name + '</option>';
            }
        }
        $('#tags').html("");
        $('#tags').html(eventOptions);
        $('.selectpicker').selectpicker('refresh');

        generateURL();
    });
}

function getMumDetails(repid) {

    var type = $("#type").val();
    var sectionid = $("#sections").val();
    $.ajax({
        url: "../lib/l-elasticReport.php?function=1&functionToCall=getMumData" + '&csrfMagicToken=' + csrfMagicToken,
        type: "POST",
        data: "repid=" + repid + "&sectionid=" + sectionid,
        success: function (data) {
            $(".se-pre-con").hide();
            $('.selectpicker').selectpicker('refresh');
            var mumdata = JSON.parse(data);
            var y;
            var mumData = [];
            var status = '';

            for (y in mumdata[0].details) {
                switch (mumdata[0].details[y].status) {
                    case 'Installed':
                        status = mumdata[0].details[y].status;
                        break;
                    case 'Downloaded':
                        status = mumdata[0].details[y].status;
                        break;
                    case 'Detected':
                        status = mumdata[0].details[y].status;
                        break;
                    case 'Superseded':
                        status = mumdata[0].details[y].status;
                        break;
                    case 'Waiting':
                        status = mumdata[0].details[y].status;
                        break;
                    default:
                        status = mumdata[0].details[y].status;
                        break;
                }
                mumData.push(['<p class="ellipsis" title="' + mumdata[0].details[y].host + '">' + mumdata[0].details[y].host + '</p>', '<p class="ellipsis" title="' + mumdata[0].patchname[mumdata[0].details[y].patchid] + '">' + mumdata[0].patchname[mumdata[0].details[y].patchid] + '</p>', status, mumdata[0].patchtype[mumdata[0].details[y].patchid], mumdata[0].details[y].detected]);
            }

            $('.report_table').hide();
            $('.mumtable, .notInAsset').show();

            $('#mumDetail').DataTable().destroy();
            $('#mumDetail').DataTable({
                scrollY: jQuery('.order-table').data('height'),
                scrollCollapse: true,
                autoWidth: false,
                bAutoWidth: true,
                responsive: true,
                data: mumData,
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

            drawMumCharts(mumdata, type);

        },
        error: function (err) {
            console.log(err.toString());
        }
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

function loopdata(assetdata, unique1) {

    var column = 0;
    var temp = [];
    var data = [];
    var i = 0;
    var dataColumns = [];
    var sections = assetdata;

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

                                if (jdata === undefined || jdata === 'undefined') {
                                    var link = '<p class="ellipsis" title="" style="margin-left:25%">-</p>';
                                    final.push(link);
                                } else {
                                    $.each(jdata, function (n, o) {
                                        var link = '<p class="ellipsis" title="' + o + '">' + o + '</p>';
                                        final.push(link);
                                    });
                                }
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
    for (var l in temp) {
        data.push(temp[l]);
    }
    return data;
}

function getAssetDetails(repid) {

    var type = $("#type").val();
    var sectionid = $("#sections").val();
    $.ajax({
        url: "../lib/l-elasticReport.php?function=1&functionToCall=getAssetData",
        type: "POST",
        data: "repid=" + repid + "&sectionid=" + sectionid + '&csrfMagicToken=' + csrfMagicToken,
        success: function (data) {
            $(".se-pre-con").hide();
            $('.selectpicker').selectpicker('refresh');
            //console.log('MUM Data : ' + data);
            var assetdata = JSON.parse(data);

            //Dynamically adding columns to Datatable
            for (k in assetdata[0].details.columns) {
                $("#assetDetail>thead>tr").append("<th>" + k + "</th>");
            }
            var columnNames = get_uniqueColumn(assetdata);
            var assetData = loopdata(assetdata, columnNames);

            $('.report_table').hide();
            $('.assettable').show();

            $('#assetDetail').DataTable().destroy();
            $('#assetDetail').DataTable({
                scrollY: jQuery('.order-table').data('height'),
                scrollCollapse: true,
                autoWidth: false,
                bAutoWidth: true,
                responsive: true,
                data: assetData,
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
            if(assetdata[0].details.showGraph === 0){
                $('.report-chart').hide();
            }else{
                $('.report-chart').hide();
                drawAssetCharts(assetdata.graph, 'bar');
            }

        },
        error: function (err) {
            console.log(err.toString());
        }
    });
}

function newTable(type, from, tags, reporteType, reportVal, columns) {
    hidegraph = true;
    // from = 90d, now = now

    var tagArr = tags.split(",");
    var tag_match_phrase = "";
    var aggs_filter_str = "";
    for (var i = 0; i < tagArr.length; i++) {
        tag_match_phrase += "(match_phrase:(Tags:" + tagArr[i] + ")),";
        aggs_filter_str += "(input:(query:(query_string:(query:'Tags.keyword:" + tagArr[i] + "'))),label:'" + tagArr[i] + "'),";
    }

    tag_match_phrase = tag_match_phrase.replace(/,$/, "");
    aggs_filter_str = aggs_filter_str.replace(/,$/, "");

    var reportArr = reportVal.split(",");
    var report_match_phrase = "";
    for (var i = 0; i < reportArr.length; i++) {
        report_match_phrase += "(match_phrase:(customer:" + reportArr[i] + ")),";
    }

    report_match_phrase = report_match_phrase.replace(/,$/, "");

    var baseurl = "https://msp.nanoheal.com:5601/app/kibana#/discover?embed=true";
    var param1 = "&_g=("
            + "refreshInterval:("
            + "display:Off,"
            + "pause:!f,"
            + "value:0"
            + "),"
            + "time:("
            + "from:now-" + from + ","
            + "interval:'1h',"
            + "mode:quick,"
            + "timezone:Asia%2FKolkata,"
            + "to:now"
            + ")"
            + ")";

    var reportFilter = "";

    if (reporteType) {
        switch (reporteType) {
            case "site":
                reportFilter = "("
                        + "'$state':(store:appState),"
                        + "meta:("
                        + "alias:customer,"
                        + "disabled:!f,"
                        + "index:AV8FPHEbz-ZIQ-fzFD6a,"
                        + "key:customer,"
                        + "negate:!f,"
                        + "params:!(" + reportVal + "),"
                        + "type:phrases,"
                        + "value:'" + reportVal + "'"
                        + "),"
                        + "query:("
                        + "bool:("
                        + "minimum_should_match:1,"
                        + "should:!("
                        + report_match_phrase
                        + ")"
                        + ")"
                        + ")"
                        + ")";

                break;
        }
    }

    var filter = "filters:!("
            + reportFilter + ","
            + "("
            + "'$state':(store:appState),"
            + "meta:("
            + "alias:Tags,"
            + "disabled:!f,"
            + "index:AV8FPHEbz-ZIQ-fzFD6a,"
            + "key:Tags,"
            + "negate:!f,"
            + "params:!(" + tags + "),"
            + "type:phrases,"
            + "value:'" + tags + "'"
            + "),"
            + "query:("
            + "bool:("
            + "minimum_should_match:1,"
            + "should:!("
            + tag_match_phrase
            + ")"
            + ")"
            + ")"
            + ")"
            + ")";

    var aggs = "aggs:!("
            + "("
            + "enabled:!t,"
            + "id:'1',"
            + "params:("
            + "customLabel:count"
            + "),"
            + "schema:metric,"
            + "type:count"
            + "),"
            + "("
            + "enabled:!t,"
            + "id:'2',"
            + "params:("
            + "customInterval:'2h',"
            + "extended_bounds:(),"
            + "field:entered,"
            + "interval:d,"
            + "min_doc_count:1"
            + "),"
            + "schema:segments,"
            + "type:date_histogram"
            + "),"
            + "("
            + "enabled:!t,"
            + "id:'3',"
            + "params:("
            + "filters:!(" + aggs_filter_str + ")"
            + "),"
            + "schema:group,"
            + "type:filters"
            + ")"
            + ")";

    var params = "params:("
            + "addLegend:!t,"
            + "addTimeMarker:!f,"
            + "addTooltip:!t,"
            + "categoryAxes:!("
            + "("
            + "id:CategoryAxis-1,"
            + "labels:("
            + "show:!t,"
            + "truncate:100"
            + "),"
            + "position:bottom,"
            + "scale:(type:linear),"
            + "show:!t,"
            + "style:(),"
            + "title:(text:test2),"
            + "type:category"
            + ")"
            + "),"
            + "grid:("
            + "categoryLines:!f,"
            + "style:(color:%23eee)"
            + "),"
            + "legendPosition:right,"
            + "seriesParams:!("
            + "("
            + "data:("
            + "id:'1',"
            + "label:count"
            + "),"
            + "drawLinesBetweenPoints:!t,"
            + "mode:stacked,"
            + "show:true,"
            + "showCircles:!t,"
            + "type:hetrogram,"
            + "valueAxis:ValueAxis-1"
            + ")"
            + "),"
            + "times:!(),"
            + "type:hetrogram,"
            + "valueAxes:!("
            + "("
            + "id:ValueAxis-1,"
            + "labels:("
            + "filter:!f,"
            + "rotate:0,"
            + "show:!t,"
            + "truncate:100"
            + "),"
            + "name:LeftAxis-1,"
            + "position:left,"
            + "scale:("
            + "mode:normal,"
            + "type:linear"
            + "),"
            + "show:!t,"
            + "style:(),"
            + "title:(text:count),"
            + "type:value"
            + ")"
            + ")"
            + ")";

    var url = baseurl + param1 + "&_a=(columns:!(" + columns + ")," + filter + ",index:AV8FPHEbz-ZIQ-fzFD6a,interval:auto,query:(match_all:()),sort:!(entered,desc),vis:(" + aggs + ",listeners:()," + params + ",title:'New+Visualization',type:" + type + "))";
    $("#loadIframe").html("<iframe src=\"" + url + "\" height=\"400\" width=\"950\"></iframe>").hide();
    $("#loader").show();
    $("#url").val(url);
    console.log("URL: " + url);
}

function showIframe() {
    setTimeout(function () {
        $(".loader").hide();
        $("#loadIframe").show();
    }, 500);
    $("#loadIframe iframe")[0].contentWindow.postMessage("removetag", "*");
    if (hidegraph) {
        //$("#loadIframe iframe")[0].contentWindow.postMessage("removetablegraph","*");
    }
}
