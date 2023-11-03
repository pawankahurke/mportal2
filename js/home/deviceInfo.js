$(document).ready(function() {
    gridNum = 1;
    // getRespectiveGrid(gridNum);
    $('#information-portal').on('shown.bs.modal', function() {
        $('#assetinformationGrid').show();
        $('#assetinformationGrid').DataTable().columns.adjust().draw();
    });
});

function getRespectiveGrid(gridNum) {
    if (gridNum == 1) {
        getBasicInfo();
    } else if (gridNum == 2) {
        getSoftwareInfo();
    } else if (gridNum == 3) {
        getPatchInfo();
    } else if (gridNum == 4) {
        getResourceInfo();
    } else if (gridNum == 5) {
        getNetworkInfo();
    }
    else if (gridNum == 6) {
        getSystemInfo();
    }
}

//getBasicInfo : Fills basic info grid.
function getBasicInfo() {
    gridNum = 1;
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
//    $("#deviceinfositename").html(searchValue);
    $("#se-pre-con-loader").show();
    var url = "../lib/l-ajax.php?function=AJAX_GetDeviceAssetInfo&gridType=basic&searchType=" + searchType + "&searchValue=" + searchValue;
    createAjaxRequest('GET', url, function(response) {
        basicInfoGrid = createDataTable('basicInfoGrid', response);
        $("#se-pre-con-loader").hide();
        $("#deviceinfo_searchbox").keyup(function() {
            basicInfoGrid.search('"' + this.value + '"').draw();
        });
    });
}

//getSystemInfo(): this fills System Info grid
function getSystemInfo() {
    gridNum = 6;
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
//    $("#deviceinfositename").html(searchValue);
    $("#se-pre-con-loader").show();
    var url = "../lib/l-ajax.php?function=AJAX_GetDeviceAssetInfo&gridType=system&searchType=" + searchType + "&searchValue=" + searchValue;
    createAjaxRequest('GET', url, function(response) {
        createHighChart('operating-system', response.graphdata.operatingSystem);
        createHighChart('processor', response.graphdata.processorVersion);
        createHighChart('chassis-type', response.graphdata.chassisType);
        createHighChart('chassis-manufacturer', response.graphdata.chassisManufacturer);
        systeminfogrid1 = createDataTable('systemInfoOperatingSystemGrid', response.griddata.operatingSystem);
        systeminfogrid2 = createDataTable('systemInfoChassisManufacturerGrid', response.griddata.chassisManufacturer);
        systeminfogrid3 = createDataTable('systemInfoChassisTypeGrid', response.griddata.chassisType);
        systeminfogrid4 = createDataTable('systemInfoProcessorGrid', response.griddata.processorVersion);
        $("#se-pre-con-loader").hide();
        $("#deviceinfo_searchbox").keyup(function() {
            systeminfogrid1.search('"' + this.value + '"').draw();
            systeminfogrid2.search('"' + this.value + '"').draw();
            systeminfogrid3.search('"' + this.value + '"').draw(); 
            systeminfogrid4.search('"' + this.value + '"').draw();
              
        });
    });
}

//This function fills the Table grid
function getSoftwareInfo() {
    gridNum = 2;
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    $("#se-pre-con-loader").show();
    var url = "../lib/l-ajax.php?function=AJAX_GetDeviceAssetInfo&gridType=software&searchType=" + searchType + "&searchValue=" + searchValue + "&softwareNames=" + softwarenames;
    createAjaxRequest('GET', url, function(response) {
        createHighChart('software-chart', response.graphdata, response.drilldowndata);
        $('#softInfoGrid').DataTable().destroy();
        var griddata = [];
        if (response.griddata)
            griddata = response.griddata;
        softInfoGrid = createDataTable('softInfoGrid', griddata);
        $("#se-pre-con-loader").hide();
        $("#deviceinfo_searchbox").keyup(function() {
            softInfoGrid.search('"' + this.value + '"').draw();
        });
    });
}

//This function fills the Table grid
//Function will get called on drill down of software.
//Respective software grid data only visible in grid, which is present in graph.
function getDrilledSoftwareInfo(drilledSoftwareName) {
    gridNum = 2;
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    //$("#se-pre-con-loader").show();
    var url = "../lib/l-ajax.php?function=AJAX_GetDeviceAssetInfo&gridType=software&searchType=" + searchType + "&searchValue=" + searchValue + "&softwareNames=" + drilledSoftwareName;
    createAjaxRequest('GET', url, function(response) {
        //createHighChart('software-chart', response.graphdata, response.drilldowndata);
        $('#softInfoGrid').DataTable().destroy();
        var griddata = [];
        if (response.griddata)
            griddata = response.griddata;
        softInfoGrid = createDataTable('softInfoGrid', griddata);
        //$("#se-pre-con-loader").hide();
        $("#deviceinfo_searchbox").keyup(function() {
            softInfoGrid.search('"' + this.value + '"').draw();
        });
    });
}

//This function fills the Table grid
function getPatchInfo() {
    gridNum = 3;
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
//    $("#deviceinfositename").html(searchValue);
    $("#se-pre-con-loader").show();
    var url = "../lib/l-ajax.php?function=AJAX_GetDeviceAssetInfo&gridType=patch&searchType=" + searchType + "&searchValue=" + searchValue;
    createAjaxRequest('GET', url, function(response) {
        patchInfoGrid = createDataTable('patchInfoGrid', response);
        $("#se-pre-con-loader").hide();
        $("#deviceinfo_searchbox").keyup(function() {
            patchInfoGrid.search('"' + this.value + '"').draw();
        });
    });
}

//This function fills the Table grid
function getResourceInfo() {
    gridNum = 4;
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
//    $("#deviceinfositename").html(searchValue);
    $("#se-pre-con-loader").show();
    var url = "../lib/l-ajax.php?function=AJAX_GetDeviceAssetInfo&gridType=resource&searchType=" + searchType + "&searchValue=" + searchValue;
    createAjaxRequest('GET', url, function(response) {
        resourceGrid = createDataTable('resourceGrid', response);
        $("#se-pre-con-loader").hide();
        $("#deviceinfo_searchbox").keyup(function() {
            resourceGrid.search('"' + this.value + '"').draw();
        });
    });


}

//This function fills the Table grid
function getNetworkInfo() {
    gridNum = 5;
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
//    $("#deviceinfositename").html(searchValue);
    $("#se-pre-con-loader").show();
    var url = "../lib/l-ajax.php?function=AJAX_GetDeviceAssetInfo&gridType=network&searchType=" + searchType + "&searchValue=" + searchValue;
    createAjaxRequest('GET', url, function(response) {
       netwInfoGrid = createDataTable('netwInfoGrid', response);
    });
    $("#se-pre-con-loader").hide();
    $("#deviceinfo_searchbox").keyup(function() {
        netwInfoGrid.search('"' + this.value + '"').draw();
    });
}


//This export functionality is given in sitefunctions.php file.
$("#exportDeviceInfo").click(function() {
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();

    if (gridNum == 1) {
        var exportGridType = "basic";
    } else if (gridNum == 2) {
        var exportGridType = "software";
    } else if (gridNum == 3) {
        var exportGridType = "patch";
    } else if (gridNum == 4) {
        var exportGridType = "resource";
    } else if (gridNum == 5) {
        var exportGridType = "network";
    }
    else if (gridNum == 6) {
        var exportGridType = "system|" + systemsubgrid;
    }
    window.location.href = "../lib/l-ajax.php?function=AJAX_ExportDeviceAssetInfo&gridType=" + exportGridType + "&searchType=" + searchType + "&searchValue=" + searchValue + "&softwareNames=" + softwarenames;
    ;
});


/* ============= Asset filter function =============== */
function assetfilterClick() {
    var id = $('#assetadvdata').val();
    var searchType = $('#searchType').val();
    var grpname = $('#deviceinfositename').text();//groupname

    if (searchType == 'Sites' || searchType == 'ServiceTag') {
        var searchValue = $('#searchValue').val();
    } else if (searchType == 'Groups') {
        var searchValue = grpname;
    }

    var auth = $('#authuser').val();
    if (id != '') {
        var url = 'sitefunctions.php?function=get_assetfilterportal&qid=' + id + '&searchType=' + searchType + '&searchValue=' + searchValue + '&username=' + auth;
        createAjaxRequest('POST', url, function(response) {
            $("#publishPop").modal("show");
            $("#something").html(response);
        });
    } else {

    }
    $(".search").removeClass("open");   /*  close search drop-down box  */
    $("#search_popup").modal("show");
}


function assetinformation() {
    $(".information-portal-popup .se-pre-con").show();
    $.ajax({
        type: "GET",
        url: 'sitefunctions.php?function=get_machinereportlist',
        data: "",
        dataType: 'json',
        success: function(gridData) {
            $('#assetinformationGrid').DataTable().destroy();
            groupTable = $('#assetinformationGrid').DataTable({
                scrollY: jQuery('#assetinformationGrid').data('height'),
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
                columnDefs: [
                    {
                        targets: "datatable-nosort",
                        orderable: false
                    },
                    {
                        className: "table-plus checkbox-btn",
                        targets: 0
                    },
                ],
                drawCallback: function(settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $(".information-portal-popup .se-pre-con").hide();
                    $(".checkbox-btn input[type='checkbox']").change(function() {
                        if ($(this).is(":checked")) {
                            $(this).parents("tr").addClass("selected");
                        }
                    });
                    $('.equalHeight').matchHeight();
                    $('#assetinformationGrid_filter').css({"margin-right": "4%", "margin-top": "-5%"});
                }
            });
        },
        error: function(msg) {

        }
    });
    // $("#notification_searchbox").keyup(function() {
    //     table3.search(this.value).draw();
    // });        
}

/*==== file download ====*/
function Downloadxls(url) {
    window.location.href = '../assets/files/' + url;
}

/*=====asset Delete ======*/
function assetDelete() {
    var checkedValues = $('.commonClass:checked').map(function() {
        return $(this).attr('value');
    }).get();
    if (checkedValues == '') {
        $("#warningreport").modal("show");
    } else {
        var url = 'sitefunctions.php?function=get_deleteportalreport&id=' + checkedValues;
        createAjaxRequest('POST', url, function(response) {
            assetinformation();
        });
    }
}

/*==== report refresh code====*/
function refreshpop() {
    assetinformation();
}

/* ===== Multiple check selection code ===== */
$("#topCheckBox").change(function() {
    if (this.checked) {
        $('.commonClass').prop('checked', true);
    } else {
        $('.commonClass').prop('checked', false);
    }
});

function createHighChart(id, graphdata, drilldowndata) {
//    alert(graphdata);
    switch (id) {
        case 'chassis-manufacturer':
            Highcharts.chart('chassis-manufacturer', {
                chart: {
                    type: 'column'
                },
                colors: ['#5B539E', '#F1574F', '#f4c63d', '#d17905'],
                title: {
                    text: 'Chassis Manufacturer'
                },
                subtitle: {
                    text: ''
                },
                xAxis: {
                    type: 'category',
                    gridLineDashStyle: 'longdash',
                    gridLineWidth: 1,
                    crosshair: true
                },
                yAxis: {
                    gridLineDashStyle: 'longdash',
                    min: 0,
                    title: {
                        text: ''
                    }
                },
                tooltip: {
                    enabled: false
                },
                legend: {
                    enabled: false
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                        type: 'column',
                        maxPointWidth: 20,
                        colorByPoint: true,
                        data: JSON.parse(graphdata)
                    }]
            });
            break;
        case 'chassis-type':
            Highcharts.chart('chassis-type', {
                chart: {
                    type: 'column'
                },
                colors: ['#5B539E', '#F1574F', '#f4c63d', '#d17905'],
                title: {
                    text: 'Chassis Type'
                },
                subtitle: {
                    text: ''
                },
                xAxis: {
                    type: 'category',
                    gridLineDashStyle: 'longdash',
                    gridLineWidth: 1,
                    crosshair: true
                },
                yAxis: {
                    gridLineDashStyle: 'longdash',
                    min: 0,
                    title: {
                        text: ''
                    }
                },
                tooltip: {
                    enabled: false
                },
                legend: {
                    enabled: false
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                        type: 'column',
                        maxPointWidth: 20,
                        colorByPoint: true,
                        data: JSON.parse(graphdata)
                    }]
            });
            break;
        case 'processor':
            Highcharts.chart('processor', {
                chart: {
                    type: 'column'
                },
                colors: ['#5B539E', '#F1574F', '#f4c63d', '#d17905'],
                title: {
                    text: 'Processor'
                },
                subtitle: {
                    text: ''
                },
                xAxis: {
                    type: 'category',
                    gridLineDashStyle: 'longdash',
                    gridLineWidth: 1,
                    crosshair: true
                },
                yAxis: {
                    gridLineDashStyle: 'longdash',
                    min: 0,
                    title: {
                        text: ''
                    }
                },
                tooltip: {
                    enabled: false
                },
                legend: {
                    enabled: false
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                        type: 'column',
                        maxPointWidth: 20,
                        colorByPoint: true,
                        data: JSON.parse(graphdata)
                    }]
            });
            break;
        case 'operating-system':
            Highcharts.chart('operating-system', {
                chart: {
                    type: 'column'
                },
                colors: ['#5B539E', '#F1574F', '#f4c63d', '#d17905'],
                title: {
                    text: 'Operating System'
                },
                subtitle: {
                    text: ''
                },
                xAxis: {
                    type: 'category',
                    gridLineDashStyle: 'longdash',
                    gridLineWidth: 1,
                    crosshair: true
                },
                yAxis: {
                    gridLineDashStyle: 'longdash',
                    min: 0,
                    title: {
                        text: ''
                    }
                },
                tooltip: {
                    enabled: false
                },
                legend: {
                    enabled: false
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                        type: 'column',
                        colorByPoint: true,
                        maxPointWidth: 20,
                        data: JSON.parse(graphdata)
                    }]
            });
            break;
        case 'software-chart':
            var hc = Highcharts.chart(id, {
                chart: {
                    type: 'column',
                    events: {
                        drilldown: function(e) {
                            var drilledSoftwareName = e.point.name;
                            getDrilledSoftwareInfo(drilledSoftwareName);
                },
                        drillup: function(e) {
                            getDrilledSoftwareInfo(softwarenames);
                        }
                    }
                },
                
                title: {
                    text: 'Software Details'
                },
                subtitle: {
                    text: ''
                },
                xAxis: {
                    type: 'category'
                },
                yAxis: {
                    allowDecimals: false,
                    title: {
                        text: 'Total count in a site',
                        min: 0
                    }

                },
                legend: {
                    enabled: false
                },
                plotOptions: {
                    series: {
                        borderWidth: 0,
                        dataLabels: {
                            enabled: true
                        },
                        cursor: 'pointer',
                        point: {
                            events: {
                                click: function(e) {
                                    if(e.point.name !== null){
                                        softInfoGrid.search(e.point.name).draw();
                                    }else{
                                        
                        }
                    }
                        }
                    }
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                    pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y}</b> of total<br/>'
                },
                series: [{
                        name: 'Softwares',
                        colorByPoint: true,
                        maxPointWidth: 10,
                        data: JSON.parse(graphdata)
                    }],
                drilldown: {
                    series: JSON.parse(drilldowndata)
                }
            });
            break;
    }
}

function createDataTable(id, data) {
    if (!$.isArray(data)) {
        data = JSON.parse(data);
    }
    $('#' + id).DataTable().destroy();
    return $('#' + id).DataTable({
        scrollY: jQuery('#' + id).data('height'),
        scrollCollapse: true,
        paging: true,
        searching: true,
        ordering: true,
        aaData: data,
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
        columnDefs: [{
                targets: "datatable-nosort",
                orderable: false
            }],
        drawCallback: function(settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
            $(".checkbox-btn input[type='checkbox']").change(function() {
                if ($(this).is(":checked")) {
                    $(this).parents("tr").addClass("selected");
                }
            });
            $('.equalHeight').matchHeight();
        }
    });
}

$(".chk").on('click', function() {
    softwarenames = [];
    $(".chk:checked").each(function() {
        softwarenames.push($(this).val());
    });
    getSoftwareInfo();
});
var systemsubgrid = 'operatingSystem';
function setsystemsubgrid(type) {
    systemsubgrid = type;
}

function createAjaxRequest(type, url, callback) {
    $.ajax({
        type: type,
        url: url,
        data: "",
        dataType: 'json',
        success: function(response) {
            callback(response);
        }
    });
}

/*$('.deviceslider').on('afterChange', function(event, slick, nextSlide) {
    if (nextSlide == 0) {
        var totalDisplayRecord = $("#basicInfoGrid").DataTable().page.info().recordsDisplay
        if (totalDisplayRecord === 0) {
            getRespectiveGrid(1);
        }
    }
    else if (nextSlide == 1) {
        var totalDisplayRecord = $("#systemInfoOperatingSystemGrid").DataTable().page.info().recordsDisplay;
        if (totalDisplayRecord === 0) {
            getRespectiveGrid(6);
        }

    } else if (nextSlide == 2) {
        var totalDisplayRecord = $("#softInfoGrid").DataTable().page.info().recordsDisplay;
        if (totalDisplayRecord === 0) {
            getRespectiveGrid(2);
        }

    } else if (nextSlide == 3) {
        var totalDisplayRecord = $("#patchInfoGrid").DataTable().page.info().recordsDisplay;
        if (totalDisplayRecord === 0) {
            getRespectiveGrid(3);
        }

    } else if (nextSlide == 4) {
        var totalDisplayRecord = $("#resourceGrid").DataTable().page.info().recordsDisplay;
        if (totalDisplayRecord === 0) {
            getRespectiveGrid(4);
        }
    } else if (nextSlide == 5) {
        var totalDisplayRecord = $("#netwInfoGrid").DataTable().page.info().recordsDisplay;
        if (totalDisplayRecord === 0) {
            getRespectiveGrid(5);
        }

    } else if (nextSlide == 6) {
        var totalDisplayRecord = $("#datInfoGrid").DataTable().page.info().recordsDisplay;
        if (totalDisplayRecord === 0) {
            getRespectiveGrid(7);
        }
    }
});*/
