//$(function(){        
//    MUMReportAllFunction();     
//})

function MUMReportAllFunction() {
    
    var year = $('#years option:selected').val();
    var mnth = $('#months option:selected').val();  
    
    mumcheckautomatic(year,mnth);    
    mumgridData(year,mnth);
    renderChart(year,mnth);
}

function mumcheckautomatic(year,mnth) {
    $.ajax({
        url: "patchesfunctions.php",
        type: "POST",
        data: "function=get_patchautomaticheck" + '&csrfMagicToken=' + csrfMagicToken,
        dataType: "json",
        success:function(data){
            if (data.status == 'Automatic') {  
                $('#manualgrid').hide(); 
                $('#automaticgrid').show();
                mumautomatictotalData(year,mnth);
            }  else if (data.status == 'Manual') {                
                $('#manualgrid').show(); 
                $('#automaticgrid').hide();                
                mumManualtotalData(year,mnth);             
            }            
        }        
    })
}

function mumautomatictotalData(year,mnth) {
    var val = $('#searchValue').val();
//    $('#headerName').html(val);    
    
    var splitted = val.split("__");
    
    $('#headerName').html(splitted[0]);   
    
    $.ajax({
        url: "patchesfunctions.php",
        type: "POST",
        data: "function=get_patchtotalData&year=" + year + '&month=' + mnth + '&csrfMagicToken=' + csrfMagicToken,
        dataType: "json",
        success: function(gridData) {                                                
            $(".se-pre-con").hide();
            $('#mumtotalautoDetail').DataTable().destroy();
            MUMReport = $('#mumtotalautoDetail').DataTable({
                scrollY: jQuery('#mumtotalautoDetail').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo:false,
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
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                    $('#mumtotalautoDetail_wrapper .bottom').hide();
                    $('#mumtotalDetail_filter').hide();
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });
}


function mumManualtotalData(year,mnth) {
    
    var val = $('#searchValue').val();
//    $('#headerName').html(val);    
    
    var splitted = val.split("__");
    
    $('#headerName').html(splitted[0]);   
    
    $.ajax({
        url: "patchesfunctions.php",
        type: "POST",
        data: "function=get_patchtotalData&year=" + year + '&month=' + mnth + '&csrfMagicToken=' + csrfMagicToken,
        dataType: "json",
        success: function(gridData) {                                                
            $(".se-pre-con").hide();
            $('#mumtotalmanualDetail').DataTable().destroy();
            MUMReport = $('#mumtotalmanualDetail').DataTable({
                scrollY: jQuery('#mumtotalmanualDetail').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo:false,
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
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                    $('#mumtotalmanualDetail_wrapper .bottom').hide();
                    $('#mumtotalDetail_filter').hide();
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });
}

function mumgridData(year,mnth) {
    $.ajax({
        url: "patchesfunctions.php",
        type: "POST",
        data: "function=get_patchgridData&year=" + year + '&month=' + mnth + '&csrfMagicToken=' + csrfMagicToken,
        dataType: "json",
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#mumgridData').DataTable().destroy();
            MUMGridData = $('#mumgridData').DataTable({
                scrollY: jQuery('#mumgridData').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo:false,
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
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                    $('#mumgridData_filter').hide();
//                    $('.bottom').removeAttr("style");
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });
}

function renderChart(year,mnth) {
     
    $.ajax({
        type: "post",
        url: "patchesfunctions.php",
        data: "function=get_patchgraphData&year=" + year + '&month=' + mnth + '&csrfMagicToken=' + csrfMagicToken,
        dataType: "json",
        success: function (data) {            
            graphData = data;
            google.charts.load("current", {packages: ["corechart", 'bar']});
            google.charts.setOnLoadCallback(drawChart1);
        }
    });    
}


function drawChart1() {
    var compliant = graphData;
    var noncompliant = 100 - compliant;
    var data1 = google.visualization.arrayToDataTable([
        ['Task', 'Hours per Day'],
        ['Compliant', compliant],
        ['Non-Compliant', noncompliant],        
    ]);
    var options1 = {
        title: 'PATCH COMPLIANCE STATUS',
        pieHole: 0.9,
        pieSliceText: 'none',
        chartArea: {left: '10px', top: '-20px', width: '94%', height: '90%'},
        legend: {
            position: 'right',
            alignment: 'center'
        },
        slices: {
            0: { color: '#00bcf2' },
            1: { color: '#027496' },
            2: { color: '#0059b2' },
            3: { color: '#bad6da' },
            4: { color: '#3C95B9' }
        }
    };

    var chart1 = new google.visualization.PieChart(document.getElementById('donutchart1'));
    handleChart1 = chart1;
    handleData1 = data1;
    google.visualization.events.addListener(chart1, 'select', selectHandler1);
    chart1.draw(data1, options1);
    var chart3 = new google.visualization.PieChart(document.getElementById('donutchart3'));
    /* function close */
}

function patchDetails(id) {
    $('#patch_status_popup').modal('show');  
    viewdetailpopupclicked();
    $.ajax({
        url: "patchesfunctions.php",
        type: "POST",
        data: "function=get_patchdetailData&pid=" + id + '&csrfMagicToken=' + csrfMagicToken,
        dataType: "json",
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#patchstatusgrid').DataTable().destroy();
            groupTable = $('#patchstatusgrid').DataTable({
                scrollY: jQuery('#patchstatusgrid').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo:false,
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
}

/* ===== group view detail grid column onload click function =====*/
function viewdetailpopupclicked() {
    setTimeout(function () {
        $(".mum_patch_status_gird").click();
    }, 300);
}

function exportpatchData() {
    var year = $('#years option:selected').val();
    var mnth = $('#months option:selected').val();
    window.location.href = 'patchesfunctions.php?function=get_patchdataExport&year='+year+'&month='+mnth;
}

$('#years').change(function(){
    
    MUMReportAllFunction();
});

$('#months').change(function(){
    
    MUMReportAllFunction();
});

function selectHandler1() {
    
    var year = $('#years option:selected').val();
    var mnth = $('#months option:selected').val();
    
    var selectedItem = handleChart1.getSelection()[0];      
    var topping = handleData1.getValue(selectedItem.row, 0);  
     
    if (topping == 'Non-Compliant') {
        
        var data = "function=get_noncompliantpopup&year=" + year + '&month=' + mnth + '&csrfMagicToken=' + csrfMagicToken;
        
    } else if (topping == 'Compliant') {
        
        var data = "function=get_compliantpopup&year=" + year + '&month=' + mnth + '&csrfMagicToken=' + csrfMagicToken;
    }
    $('#patchComplaint').modal('show'); 
    viewdetailpopupclicked();
    $.ajax({
        url: "patchesfunctions.php",
        type: "POST",
        data: data,
        dataType: "json",
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#patchcompliantGrid').DataTable().destroy();
            groupTable = $('#patchcompliantGrid').DataTable({
                scrollY: jQuery('#patchcompliantGrid').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: gridData,
                bAutoWidth: false,
                select: false,
                bInfo:false,
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
}