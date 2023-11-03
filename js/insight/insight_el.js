
$(document).ready(function() {
    getReports();

    $('#sectiondetails').on('shown.bs.modal', function() {
        $('#sectionDetailsList').show();
        $('#sectionDetailsList').DataTable().columns.adjust().draw();
    });
});
var name = '';
var id = '';

var eventData = '';
var assetData = '';
var eventOptions = '';
var assetOptions = '';
var subHeaders = [];
subHeaders[1] = 1;
var subSummary = [];
subSummary[1] = 1;
var patchOptions = '';
var sectionid = 0;
var assetEditData = '';
var eventEditData = '';
var editEvent = '';
var editAsset = '';
var summaryEditData = '';
var summaryAssetData = '';

function getReports() {
    table1 = $('#reportTable').DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
//        paging: false,
        bAutoWidth: true,
        searching: true,
        processing: true,
        serverSide: true,
//        bLengthChange: false,
        responsive: true,
//        "info":     false,
        ajax: {
            url: "reportdata.php?function=1&functionToCall=getReports&reportType=" + type,
            type: "POST",
            rowId: 'id',
            data: { 'csrfMagicToken': csrfMagicToken }
        },
        columns: [
            {"data": "name", "orderable": true},
            {"data": "schedule", "orderable": false},
            {"data": "status", "orderable": true}
        ],
        columnDefs: [
            {className: "table-plus datatable-nosort", "targets": 0, "orderable": true},
            {className: "datatable-nosort", "targets": 1, "orderable": false},
            {className: "datatable-nosort", "targets": 2, "orderable": true}
        ],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function(settings, json) {
            table1.$('tr:first').click();
        },
        drawCallback: function(settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
            $(".se-pre-con").hide();
        }
    });
    $('#reportTable tbody').on('click', 'tr', function() {
        table1.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var rowdata = table1.row(this).data();
        var name = rowdata.name;
        var statusval = rowdata.status;
       
        id = rowdata.DT_RowId;
        if(statusval=== 'Enabled'){
            $("#view_report_option a").attr('style', 'color: #595959 !important');
            $("#view_report_option a").css({"pointer-events": "fill"});
        } 
        if (statusval=== 'Disabled'){
            $("#view_report_option a").css({"pointer-events": "none", "color": "#bfbfbf"});
        }
        $('#selectedrowid').val(id);
        //reloadInformationPortal(name, id);
    });
    $("#servicesinsight_searchbox").keyup(function() {
        table1.search(this.value).draw();
    });
    $("#servicesinsight_searchbox").keyup(function() {
        table1.search(this.value).draw();
    });
}



function loadInformationPortal(name, id) {

    table2 = $('#portalTable').DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        responsive: true,
        bAutoWidth: true,
        //paging: true,
        searching: false,
        processing: true,
        serverSide: true,
        ordering: true,
        //bLengthChange: false,
        //pagingType: "full_numbers",
        ajax: {
            url: "reportdata.php?function=1&functionToCall=get_report_files&name=" + name + "&reportType=" + type + '&id=' + id,
            type: "POST",
            rowId: 'id',
            data: { 'csrfMagicToken': csrfMagicToken }
        },
        columns: [
            {"data": "check_data", "orderable": false},
            {"data": "time"},
            {"data": "expires", "orderable": false},
            {"data": "size", "orderable": false}
        ],
        columnDefs: [
            {className: "table-plus datatable-nosort checkbox-btn", "targets": 0},
            {className: "datatable-nosort", "targets": 2},
            {className: "datatable-nosort", "targets": 3}
        ],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        drawCallback: function(settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
        }
    });

}

function reloadInformationPortal(name, id) {
    var urlnew = "reportdata.php?function=1&functionToCall=get_report_files&name=" + name + "&reportType=" + type + "&id=" + id;
    table2.ajax.url(urlnew).load();
}

function showAlert(id, msg) {
    $("#errMsg").html(msg);
    $('#warning').modal('show');
}

function showSuccess(msg) {
    $("#successMsg").html(msg);
    $('#success').modal('show');
}

function deleteReportFiles() {
    var checkVal = '';
    $("input:checkbox[name*=fileList]:checked").each(function() {
        checkVal += '"' + $(this).val() + '",';
    });
    if (checkVal == '') {
        $("#infoMsg").html("<span>Please select a report file to delete</span>");
        $("#notification").modal("show");
    }
    else {
        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=deleteReportFile&id=" + checkVal,
            dataType: 'json',
            data: { 'csrfMagicToken': csrfMagicToken }
        }).done(function() {
            location.reload();
        });
    }
}

function deleteReport() {
    var checkVal = '';
    $.ajax({
        type: "POST",
        url: "../lib/l-mngdRprt.php?function=1&functionToCall=checkAuth&id=" + id + "&type=" + type,
        dataType: 'json',
        data: { 'csrfMagicToken': csrfMagicToken }
    }).done(function(data) {
        if (data.auth == 'yes') {
            $.ajax({
                type: "POST",
                url: "../lib/l-mngdRprt.php?function=1&functionToCall=deleteReport&id=" + id + "&type=" + type,
                dataType: 'json'
            }).done(function() {
                location.reload();
            });
        }
        else {
            $("#infoMsg").html("<span>You Are not authorised to delete this report</span>");
            $("#notification").modal("show");
        }
    });
}

function viewReport() {
    var checkVal = [];
    $("input:checkbox[name*=fileList]:checked").each(function() {
        checkVal.push($(this).val());
    });
    if (checkVal.length == 0) {
        $("#infoMsg").html("<span>Please select a report to view</span>");
        $("#notification").modal("show");
    }
    else {
        if (checkVal.length > 1) {
            $("#infoMsg").html("<span>Multiple reports cannot be viewed at a time</span>");
            $("#notification").modal("show");
        }
        else {
            window.open("view-reports.php?filename=" + checkVal[0] + "&reportType=" + type, checkVal[0]);
        }
    }
}

function viewReportel() {
    
     var selreportId = $('#selectedrowid').val();
     
     window.open("el-view-reports.php?repid=" + selreportId, selreportId);
}

function downloadReport() {
    var checkVal = [];
    $("input:checkbox[name*=fileList]:checked").each(function() {
        checkVal.push($(this).val());
    });
    if (checkVal.length == 0) {
        $("#infoMsg").html("<span>Please select a report to download</span>");
        $("#notification").modal("show");
    }
    else {
        if (checkVal.length > 1) {
            $("#infoMsg").html("<span>Multiple reports cannot be downloaded at a time</span>");
            $("#notification").modal("show");
        }
        else {
            window.open("files/" + checkVal[0] + ".xls", checkVal[0]);
        }
    }
}

function runReport() {

    if (id == '') {
        $("#infoMsg").html("<span>Please select a report to run</span>");
        $("#notification").modal("show");
        return false;
    }
    else {
        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=runNow&name=" + name + "&reportType=" + type + "&id=" + id,
            dataType: 'json',
            data: { 'csrfMagicToken': csrfMagicToken }
        }).done(function() {
        });
        showSuccess("<span>Report will be published on the portal</span>");
    }
}

function editReport() {

    if (id === '' || id == undefined) {
        $("#infoMsg").html("<span>No report to edit</span>");
        $("#notification").modal("show");
    } else {
        $("#edit_asset_report").modal("show");
        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getReportEditData&name=" + name + "&reportType=" + type + "&id=" + id,
            dataType: 'json',
            data: { 'csrfMagicToken': csrfMagicToken }
        }).done(function(data) {
            $("#editSelect").val(data.id);
            $("#editname").val(name);
            $("#editnamediv").removeClass('is-empty');
            $("#editincuser").html(data.include);
            $('.selectpicker').selectpicker('refresh');
            if (data.global == 1) {
                $("#editglobal").attr("checked", "checked");
            }
            if (data.status == 1) {
                $("#editenable").attr("checked", "checked");
            }
            if (data.envglobal == 1) {
                $("#editenvGlobal").attr("checked", "checked");
            }

            $('#editreportCycle option[value="' + data.schedtype + '"]').attr("selected", "selected");
            $('#editweekDay option[value="' + data.weekday + '"]').attr("selected", "selected");
            $('#editDay option[value="' + data.mnthday + '"]').attr("selected", "selected");
            $('#edithour option[value="' + data.hour + '"]').attr("selected", "selected");
            $('#editmin option[value="' + data.min + '"]').attr("selected", "selected");

            editshowRprtCycOptn(document.getElementById('editreportCycle'));

            if (data.infportal == 1) {
                $("#editdest").val("1").change();
                $('.selectpicker').selectpicker('refresh');
            }
            if (data.emaillist != '') {
                $("#editdest").val("2").change();
                $('.selectpicker').selectpicker('refresh');
                $("#editemailList").show();
                $("#editemailList").val(data.emaillist);
            }

            $("#editsection").html(data.sections);

            edit_include_users();
            edit_section_display();

            $("#editsection").selectpicker("refresh");
        });
    }
}

function edit_exclude_users() {
    $("#editexcusertext").html('');
    $('#editexcuser :selected').each(function(i, selected) {
        $("#editexcusertext").append($(selected).text());
        $("#editexcusertext").append("\n");
    });
}

function edit_include_users() {
    $("#editincusertext").html('');
    $('#editincuser :selected').each(function(i, selected) {
        $("#editincusertext").append($(selected).text());
        $("#editincusertext").append("\n");
    });
}

function edit_section_display() {
    $("#editsectionTxt").html('');
    $('#editsection :selected').each(function(i, selected) {
        $("#editsectionTxt").append($(selected).text());
        $("#editsectionTxt").append("\n");
    });
}

function editshowRprtCycOptn(obj) {
    var cycle = $(obj).val();
    if (cycle == 1) { //immediate cycle
        $("#editweekday").hide();
        $("#editday").hide();
        $("#editHour").hide();
        $("#editMin").hide();
        //alert(cycle);
    }
    else if (cycle == 2) { // daily cycle
        $("#editweekday").hide();
        $("#editday").hide();
        $("#editHour").show();
        $("#editMin").show();
    }
    else if (cycle == 3) { // weekly cycle
        $("#editweekday").show();
        $("#editday").hide();
        $("#editHour").show();
        $("#editMin").show();
    }
    else if (cycle == 4) { // monthly cycle
        $("#editweekday").hide();
        $("#editday").show();
        $("#editHour").show();
        $("#editMin").show();
    }
}

function exclude_users() {
    $("#excusertext").html('');
    $('#excuser :selected').each(function(i, selected) {
        $("#excusertext").append($(selected).text());
        $("#excusertext").append("\n");
    });
}

function include_users() {
    $("#incusertext").html('');
    $('#incuser :selected').each(function(i, selected) {
        $("#incusertext").append($(selected).text());
        $("#incusertext").append("\n");
    });
}

function section_display() {
    $("#sectionTxt").html('');
    $('#section :selected').each(function(i, selected) {
        $("#sectionTxt").append($(selected).text());
        $("#sectionTxt").append("\n");
    });
}

function showEmailCont() {
    var dest = $("#dest").val();
    //console.log(dest);
    if (dest) {
        if (dest.toString().indexOf("2") !== -1) {
            $("#emailListDiv").show();
        }
        else {
            $("#emailListDiv").hide();
        }
    }
    else {
        $("#emailListDiv").hide();
    }
}

function showEmailContEdit() {

    var dest = $("#editdest").val();
    if (dest) {
        if (dest.toString().indexOf("2") !== -1) {
            $("#editemailListDiv").show();
        }
        else {
            $("#editemailListDiv").hide();
        }
    }
    else {
        $("#editemailListDiv").hide();
    }
}

function showRprtCycOptn(obj) {
    var cycle = $(obj).val();
    if (cycle == 1) { //immediate cycle
        $("#weekday").hide();
        $("#day").hide();
        $("#Hour").hide();
        $("#Min").hide();
        //alert(cycle);
    }
    else if (cycle == 2) { // daily cycle
        $("#weekday").hide();
        $("#day").hide();
        $("#Hour").show();
        $("#Min").show();
    }
    else if (cycle == 3) { // weekly cycle
        $("#weekday").show();
        $("#day").hide();
        $("#Hour").show();
        $("#Min").show();
    }
    else if (cycle == 4) { // monthly cycle
        $("#weekday").hide();
        $("#day").show();
        $("#Hour").show();
        $("#Min").show();
    }
}

function submitReport() {
    $("#err1").html('');
    /*validation starts*/
    if ($("#name").val() == '') {
        $("#err1").html("<span>Please enter report name</span>");
        return false;
    }

    if ($("#name").val().length > 25) {
        $("#err1").html("<span>Report name cannot be more than 25 characters</span>");
        return false;
    }

    if ($("#dest").val() == null) {
        $("#err1").html("<span>Please select one of the destinations</span>");
        return false;
    }

    if ($("#incuser").val() == null) {
        $("#err1").html("<span>Please include atleast one site</span>");
        return false;
    }

    if ($("#section").val() == null) {
        $("#err1").html("<span>Please select atleast one section</span>");
        return false;
    }

    if ($("#reportCycle").val() == '') {
        $("#err1").html("<span>Please select schedule</span>");
        return false;
    }

    /*validation ends*/

    var cycType = $("#reportCycle").val();
    var day = 0;
    var weekday = 7;
    var hour = 0;
    var min = 0;

    if (cycType == 2) {
        if ($("#hour").val() == '' || $("#min").val() == '') {
            $("#err1").html("<span>Please select schedule of the reports</span>");
            return false;
        }
        hour = $("#hour").val();
        min = $("#min").val();
    }
    else if (cycType == 3) {
        if ($("#weekDay").val() == '' || $("#hour").val() == '' || $("#min").val() == '') {
            $("#err1").html("<span>Please select schedule of the reports</span>");
            return false;
        }
        weekday = $("#weekDay").val();
        hour = $("#hour").val();
        min = $("#min").val();
    }
    else if (cycType == 4) {
        if ($("#Day").val() == '' || $("#hour").val() == '' || $("#min").val() == '') {
            $("#err1").html("<span>Please select schedule of the reports</span>");
            return false;
        }
        day = $("#Day").val();
        hour = $("#hour").val();
        min = $("#min").val();
    }

    var machGrp = $("#incuser").val();
    machGrp = machGrp.toString();

    var destination = $("#dest").val();
    destination = destination.toString();

    var infportal = 0;
    if (destination.indexOf("1") != -1) {
        infportal = 1;
    }
    var sections = $("#section").val();
    sections = sections.toString();

    var json_data = {};
    json_data.reportName = $("#name").val();
    json_data.reportGlobal = $("#global").is(':checked') ? 1 : 0;
    json_data.envGlobal = $("#envGlobal").is(':checked') ? 1 : 0;
    json_data.includeMachGrp = machGrp;
    json_data.infPortal = infportal;
    json_data.emailList = $("#emailList").val();
    json_data.enabled = $("#enable").is(':checked') ? 1 : 0;
    json_data.type = type
    json_data.defEmail = $("#defEmail").is(':checked') ? 1 : 0;
    json_data.schedData = [cycType, day, weekday, hour, min];
    json_data.sections = sections;
    var sendData = JSON.stringify(json_data);

    $("#err").html('Adding..');
    $.ajax({
        type: "POST",
        url: "../lib/l-mngdRprt.php?function=1&functionToCall=addReport",
        data: sendData,
        dataType: 'json',
        data: { 'csrfMagicToken': csrfMagicToken }
    }).done(function(data) {
        $("#err").html('');
        $("#err").html(data.status);
        setTimeout(function() {
            $(".fclose").click();
            location.reload();
        }, 1500);
    });
}

function submitEditReport() {
    $("#editerr1").html('');
    /*validation starts*/
    if ($("#editname").val() == '') {
        $("#editerr1").html("<span>Please enter report name</span>");
        return false;
    }

    if ($("#editname").val().length > 25) {
        $("#editerr1").html("<span>Report name cannot be more than 25 characters</span>");
        return false;
    }

    if ($("#editdest").val() == null) {
        $("#editerr1").html("<span>Please select one of the destinations</span>");
        return false;
    }

    if ($("#editincuser").val() == null) {
        $("#editerr1").html("<span>Please include atleast one site</span>");
        return false;
    }

    if ($("#editsection").val() == null) {
        $("#editerr1").html("<span>Please select atleast one section</span>");
        return false;
    }

    if ($("#editreportCycle").val() == '') {
        $("#editerr1").html("<span>Please select schedule</span>");
        return false;
    }

    /*validation ends*/

    var cycType = $("#editreportCycle").val();
    var day = 0;
    var weekday = 7;
    var hour = 0;
    var min = 0;

    if (cycType == 2) {
        if ($("#edithour").val() == '' || $("#editmin").val() == '') {
            $("#editerr1").html("<span>Please select schedule of the reports</span>");
            return false;
        }
        hour = $("#edithour").val();
        min = $("#editmin").val();
    }
    else if (cycType == 3) {
        if ($("#editweekDay").val() == '' || $("#edithour").val() == '' || $("#editmin").val() == '') {
            $("#editerr1").html("<span>Please select schedule of the reports</span>");
            return false;
        }
        weekday = $("#editweekDay").val();
        hour = $("#edithour").val();
        min = $("#editmin").val();
    }
    else if (cycType == 4) {
        if ($("#editDay").val() == '' || $("#edithour").val() == '' || $("#editmin").val() == '') {
            $("#editerr1").html("<span>Please select schedule of the reports</span>");
            return false;
        }
        day = $("#editDay").val();
        hour = $("#edithour").val();
        min = $("#editmin").val();
    }

    var machGrp = $("#editincuser").val();
    machGrp = machGrp.toString();

    var destination = $("#editdest").val();
    destination = destination.toString();

    var infportal = 0;
    if (destination.indexOf("1") != -1) {
        infportal = 1;
    }
    var sections = $("#editsection").val();
    sections = sections.toString();

    var reportId = $("#editSelect").val();

    var json_data = {};
    json_data.reportName = $("#editname").val();
    json_data.reportGlobal = $("#editglobal").is(':checked') ? 1 : 0;
    json_data.envGlobal = $("#editenvGlobal").is(':checked') ? 1 : 0;
    json_data.includeMachGrp = machGrp;
    json_data.infPortal = infportal;
    json_data.emailList = $("#editemailList").val();
    json_data.enabled = $("#editenable").is(':checked') ? 1 : 0;
    json_data.type = type
    json_data.defEmail = $("#editdefEmail").is(':checked') ? 1 : 0;
    json_data.schedData = [cycType, day, weekday, hour, min];
    json_data.sections = sections;
    var sendData = JSON.stringify(json_data);
//    console.log(sendData);
    $("#editerr").html('<span>Editing..</span>');
    $.ajax({
        type: "POST",
        url: "../lib/l-mngdRprt.php?function=1&functionToCall=editReport&id=" + reportId,
        data: sendData,
        dataType: 'json',
        data: { 'csrfMagicToken': csrfMagicToken }
    }).done(function(data) {
        $("#editerr").html('');
        $("#editerr").html(data.status);
        setTimeout(function() {
            $(".fclose").click();
            location.reload();
        }, 1500);
    });
}


function refresh() {
    location.reload();
}
// section related code

//for showing and hiding blocks based on type of section
$('#section_id').on('change', function() {

    if ($('#section_id').val() == 1 || $('#section_id').val() == 2) {
        $('.section').mCustomScrollbar('destroy');
        $('.summary_section').hide();
        $('.summarySection').hide();
        $('.SectionName').show();
        $('.subHeader_type').show();
        $('.subHeader_name').show();
        $('.chart_type').show();
        $('.summary_header').hide();
        $('.summarySec').hide();
        $('.mutilpe-summarySection').hide();
    } else if ($('#section_id').val() == 3) {
        $(".section").mCustomScrollbar({theme: "minimal-dark"});
        $('.SectionName').show();
        $('.summary_section').hide();
        $('.summary_header').show();
        $('.chart_type').show();
        $('.subHeader_type').hide();
        $('.subHeader_name').hide();
        $('.summarySection').hide();
        $('.summarySec').hide();
        $('.multiple_subSection').hide();
        $('.mutilpe-summarySection').hide();

    } else if ($('#section_id').val() == 4) {
        $('.section').mCustomScrollbar('destroy');
        $('.subHeader_type').hide();
        $('.chart_type').hide();
        $('.SectionName').hide();
        $('.summarySec').hide();
        $('.summary_header').hide();
        $('.subHeader_name').hide();
        $('.multiple_subSection').hide();
        $('.summary_section').show();
        $('.summarySection').show();

    } else {
        $('.section').mCustomScrollbar('destroy');
        $('.summary_section').hide();
        $('.summarySection').hide();
        $('.summary_header').hide();
        $('.summarySec').hide();
        $('.mutilpe-summarySection').hide();
        $('.subHeader_type').hide();
        $('.subHeader_name').hide();
        $('.summarySection').hide();
        $('.summarySec').hide();
        $('.multiple_subSection').hide();
        $('.mutilpe-summarySection').hide();
        $('.SectionName').show();
        $('.chart_type').show();
    }
    if ($('#section_id').val() == 1) {
        $('.event_duration').show();
    } else {
        $('.event_duration').hide();
    }
});

$('#summary_chart_type').on('change', function() {
    if ($('#summary_chart_type').val() == 6) {
        $('.pivot_chart_type').show();
        } else {
        $('.pivot_chart_type').hide();
    }
});
//to check unique section name
function validateSectionName(obj) {

    var sectionName = $(obj).val();

    $.ajax({
        type: "POST",
        url: "../lib/l-mngdRprt.php?function=1&functionToCall=getSectionName&name=" + sectionName,
        dataType: 'json',
        data: { 'csrfMagicToken': csrfMagicToken }

    }).done(function(data) {
        $("#error1").html('');
        if (data.status != 'No') {
            $("#error1").html(data.status);
            return false;
        }
        else {
            return true;
        }
    });
}

function addSummaryHeader(header, obj) {

    subSummary[header] = subSummary[header] + 1;
    if (subSummary[header] > 3) {
        $(".section").mCustomScrollbar({theme: "minimal-dark"});
    }
    var summarySections = '<div class="row clearfix mutilpe-summarySection" >' +
            '<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">' +
            '<div class="add-sumsecdata">' +
            '<a href="javascript:" onclick="addSummaryHeader(1,this)" id="addNewSummary"><i class="icon-ic_add_24px material-icons"></i></a>' +
            '<div class="form-group label-floating is-empty">' +
            '<input type="hidden" class="summary_count" value="' + subSummary[header] + '">' +
            '<label for="SubSummaryName' + subSummary[header] + '" class="control-label">Enter Sub Summary Name</label>' +
            '<input class="form-control" id="SubSummaryName' + subSummary[header] + '" type="text">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">' +
            '<div class="form-group">' +
            '<select class="form-control selectpicker dropdown-submenu" data-size="5" ' +
            'id="filterType' + subSummary[header] + '" onchange="populateSummaryFilter(' + subSummary[header] + ',this)">' +
            '<option value="0">Filter Type</option>' +
            '<option value="1">Event Filter</option>' +
            '<option value="2">Asset Filter</option>' +
            '</select>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-5 col-md-12 col-sm-12 col-xs-12">' +
            '<div class="row clearfix ">' +
            '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' +
            '<div class="form-group summary_filter" >' +
            '<select class="form-control selectpicker dropdown-submenu summaryFilter" ' +
            'data-size="5" id="summaryFilter' + subSummary[header] + '">' +
            '<option value="0">Categorised By</option>' +
            '</select>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' +
            '<div class="remove-sumsecdata">' +
            '<div class="form-group eventDuration' + subSummary[header] + '" style="display:none">' +
            '<select class="form-control selectpicker dropdown-submenu" data-size="5" id="eventDuration' + subSummary[header] + '">' +
            '<option value="0" selected>Event Duration</option>' +
            '<option value="1" >Last 1 Day</option>' +
            '<option value="3">Last 3 Days</option>' +
            '<option value="7">Last 7 Days</option>' +
            '<option value="15">Last 15 Days</option>' +
            '<option value="60">Last 60 Days</option>' +
            '<option value="4">Latest</option>' +
            '</select>' +
            '</div>' +
            '<a href="javascript:" onclick="removeSubHdr(this)" ><i class="material-icons icon-ic_close_24px"></i></a>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';

    $(summarySections).insertAfter($(obj).parent().parent().parent());
    $('.selectpicker').selectpicker('refresh');
}

//subheader validation
function subheadersAllowed(header, obj) {
    $("#error1").html("");
    $("#error1").show();
    var count = 0;
    var editcount = 0;

    if ($(obj).val() == 1 || $(obj).val() == 0) {
        var subHdr = $('#createSection').find('.addNewSubheader');
        subHdr.each(function() {
            count++;
        });

        var editSubhdr = $('#editSection').find('.addNewSubheader');
        editSubhdr.each(function() {
            editcount++;
        });
        if (count > 1) {
            var msg = 'Single query GroupBy option does not allow adding multiple sub headers';
            $("#error1").html('<span>' + msg + '</span>');
            setTimeout(function() {
                $("#error1").fadeOut(3600);
            }, 3600);
            $(obj).val(2);
        } else if (editcount > 1) {
            var msg = 'Single query GroupBy option does not allow adding multiple sub headers';
            $("#editerror1").html('<span>' + msg + '</span>');
            setTimeout(function() {
                $("#editerror1").fadeOut(3600);
            }, 3600);
            $(obj).val(2);
        } else {
            $('.addNewSubheader').hide();
            $('#subHead_group,#edit_subHead_group').show();
        }
    } else {
        $('#subHead_group,#edit_subHead_group').hide();
        $('.addNewSubheader').show();

    }
}

//add sub header
function addSubheader(header, obj) {

    subHeaders[header] = subHeaders[header] + 1;
    if (subHeaders[header] > 3) {
        $(".section").mCustomScrollbar({theme: "minimal-dark"});
    }

    var htmlSubHdr = '<div class="row clearfix multiple_subSection">' +
            '<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">' +
            '<div class="add-sumsecdata">' +
            '<a href="javascript:" onclick="addSubheader(1,this)" id="addNewSubheader" class="addNewSubheader""><i class="icon-ic_add_24px material-icons"></i></a>' +
            '<div class="form-group label-floating is-empty">' +
            '<input type="hidden" id="head_count" value="' + subHeaders[header] + '">' +
            '<label for="Subheader_' + subHeaders[header] + '" class="control-label">Enter Sub Header Name</label>' +
            '<input class="form-control" id="Subheader_' + subHeaders[header] + '" type="text">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">' +
            '<div class="form-group filter_type">' +
            '<select class="form-control selectpicker dropdown-submenu" data-size="5" id="filterType_' + subHeaders[header] + '">' +
            '</select>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-5 col-md-12 col-sm-12 col-xs-12">' +
            '<div class="row clearfix">' +
            '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' +
            '<div class="remove-sumsecdata">' +
            '<div class="form-group event_duration" style="display:default">' +
            '<select class="form-control selectpicker dropdown-submenu" data-size="5" id="event_duration_' + subHeaders[header] + '">' +
            '<option value="0" selected>Event Duration</option>' +
            '<option value="1" >Last 1 Day</option>' +
            '<option value="3">Last 3 Days</option>' +
            '<option value="7">Last 7 Days</option>' +
            '<option value="15">Last 15 Days</option>' +
            '<option value="60">Last 60 Days</option>' +
            '<option value="4">Latest</option>' +
            '</select>' +
            '</div>' +
            '<a href="javascript:" onclick="removeSubHdr(this)"><i class="material-icons icon-ic_close_24px"></i></a>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';

    $(htmlSubHdr).insertAfter($(obj).parent().parent().parent());
    var obj = $('#section_id').val();

    if ($('#section_id').val() == 1) {
        $('.event_duration').show();
    } else {
        $('.event_duration').hide();
    }
    if (obj == 1) {
        $('#filterType_' + subHeaders[header]).html("");
        $('#filterType_' + subHeaders[header]).html(eventOptions);
        $('#event_duration_' + subHeaders[header]).show();
        $('.selectpicker').selectpicker('refresh');
    }
    else if (obj == 2) {
        $('#filterType_' + subHeaders[header]).html("");
        $('#filterType_' + subHeaders[header]).html(assetOptions);
        $('#event_duration_' + subHeaders[header]).hide();
        $('.selectpicker').selectpicker('refresh');
    }
}

function removeSubHdr(obj) {
    $(obj).parent().parent().parent().parent().parent().remove();
}

//filter for summary section
function populateSummaryFilter(header, obj) {

    if ($('#filterType' + header).val() == 1) {
        $('.eventDuration' + header).show();
        $('#summaryFilter' + header).html('');
        $('#summaryFilter' + header).html(eventOptions);
        $('.selectpicker').selectpicker('refresh');
    } else if ($('#filterType' + header).val() == 2) {
        $('.eventDuration' + header).hide();
        $('#summaryFilter' + header).html('');
        $('#summaryFilter' + header).html(assetOptions);
        $('.selectpicker').selectpicker('refresh');
    }
}


//to fetch filters based on section type
function populateFilters(header, obj) {

    if ($(obj).val() == 1) {
        $('#summary_header').hide();
        $('#subHeaderType').val(0);

        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getEventFilters",
            dataType: 'json',
            data: { 'csrfMagicToken': csrfMagicToken }
        }).done(function(data) {
            eventData = data;
            /*eventOptions = '<option value="0">Choose filter</option>';
            for (var i = 0; i < data.length; i++) {
                eventOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
            }*/
            $('#filterType_' + header).html("");
            $('#filterType_' + header).html(eventData);
            $('.selectpicker').selectpicker('refresh');
        });

    }
    else if ($(obj).val() == 2) {
        $('#summary_header').hide();
        $('#subHeaderType').val(0);

        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getAssetQueries",
            dataType: 'json'
        }).done(function(data) {
            assetData = data.array;
            assetOptions = data.option;
//            assetOptions = '<option value="0">Choose Query</option>';
//            for (var i = 0; i < data.length; i++) {
//                assetOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
//            }
            $('#filterType_' + header).html("");
            $('#filterType_' + header).html(assetOptions);
            $('.selectpicker').selectpicker('refresh');
        });
    }
    else if ($(obj).val() == 3) {
        $('#chart_type').show();
        $('SectionName').show();
        $('#sumHdrAdd').hide();
        $('#subHeaderType').val(1);
        $('#subHeaderType').hide();
    } else {

        $('#sumSection').hide();
        //event options
        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getEventFilters",
            dataType: 'json',
            data: { 'csrfMagicToken': csrfMagicToken }
        }).done(function(data) {
            /*eventOptions = '<option value="0">Choose filter</option>';
            for (var i = 0; i < data.length; i++) {
                eventOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
            }*/
            eventOptions = data;
        });

        //asset option
        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getAssetQueries",
            dataType: 'json',
            data: { 'csrfMagicToken': csrfMagicToken }
        }).done(function(data) {
            /*assetOptions = '<option value="0">Choose filter</option>';
            for (var i = 0; i < data.length; i++) {
                assetOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
            }*/
            assetOptions = data.option;
        });
    }
}

//to fetch grouping option for asset and event section
function populateGroupingOptions(header, obj) {
    var filterid = $('#section_id').val();
    var options = '';
    var filter1 = $('#filterType_' + header).val();

    if (filterid == 1) {
        options += '<option value="Machine">Machine</option>' +
                '<option value="Site">Site</option>' +
                '<option value="User Name">User Name</option>' +
                '<option value="Scrip">Scrip</option>' +
                '<option value="Executable">Executable</option>' +
                '<option value="Windows Title">Windows Title</option>';
    }
    else if (filterid == 2) {
        var grpObj = JSON.parse(assetData[0]['groupby']);
        grpObj[filter1].trim();
        var grpOption = grpObj[filter1].split(":");

        for (var i = 0; i < grpOption.length; i++) {
            if (grpOption[i] !== "" && grpOption[i] !== null && grpOption[i] !== 'undefined') {
                options += '<option value="' + grpOption[i] + '">' + grpOption[i] + '</option>';
            }
        }
    }
    $('#subheader_group').html('');
    $('#subheader_group').html(options);
    $('.selectpicker').selectpicker('refresh');

}

//to fetch the patch option
function populatePatch() {

    month = $('#month').val();
    year = $('#year').val();

    $.ajax({
        type: 'post',
        url: '../lib/l-mngdRprt.php?function=1&functionToCall=getPatchDetails&mnth=' + month + '&year=' + year,
        dataType: 'json'
    }).done(function(data) {

        patchOptions = '<option value="0" >All</option>';
        for (var i = 0; i < data.length; i++) {
            patchOptions += '<option value="' + data[i].id + '" title="' + data[i].name + '">' + data[i].name + '</option>';
        }
        $('#include_patch').html("");
        $('#include_patch').html(patchOptions);
        $('.selectpicker').selectpicker('refresh');
    });
}

function moveOptions(srcList, destList, moveAll) {
    var source = document.getElementById(srcList);
    var destination = document.getElementById(destList);
    var i;

    for (i = 0; i < source.length; i++) {
        if ((source.options[i].selected) || (moveAll)) {
            destination.options[destination.length] = new Option(source.options[i].text,
                    source.options[i].value, source.options[i].title, true, true);
            source.options[i] = null;
            i--;
        }
    }
}
//adding new section
function addSection() {
//    alert('inside');
    $("#error1").html('');
    $("#error1").show();

    if ($('#section_id').val() == 0) {
        $('#error1').html('<span>Please select section type</span>');
        setTimeout(function() {
            $("#error1").fadeOut(3600);
        }, 3600);
        return false;
    }
    if ($('#section_id').val() != 4) {
        if ($('#SectionName').val() == '') {
            $('#error1').html('<span>Please enter section name</span>');
            setTimeout(function() {
                $("#error1").fadeOut(3600);
            }, 3600);
            return false;
        }
        if ($('#SectionName').val().length > 25) {
            $('#error1').html('<span>Section Name cannot be more than 25 characters</span>');
            setTimeout(function() {
                $("#error1").fadeOut(3600);
            }, 3600);
            return false;
        }
        if ($('#chart_type').val() == 0) {
            $('#error1').html('<span>Please select a chart type</span>');
            setTimeout(function() {
                $("#error1").fadeOut(3600);
            }, 3600);
            return false;
        }
    }
    if ($('#section_id').val() == '4') {
        if ($('#SummaryName').val() == '') {
            $('#error1').html('<span>Please enter summary name</span>');
            setTimeout(function() {
                $("#error1").fadeOut(3600);
            }, 3600);
            return false;
        }
        if ($('#SectionName').val().length > 25) {
            $('#error1').html('<span>Summary Name cannot be more than 25 characters</span>');
            setTimeout(function() {
                $("#error1").fadeOut(3600);
            }, 3600);
            return false;
        }
        if ($('#summary_chart_type').val() == 0) {
            $('#error1').html('<span>Please select a chart type</span>');
            setTimeout(function() {
                $("#error1").fadeOut(3600);
            }, 3600);
            return false;
    }
    }

    var sectionData = formatInputData();

    if (typeof sectionData === 'string') {
        $('#error1').html(sectionData);
        setTimeout(function() {
            $("#error1").fadeOut(3600);
        }, 3600);
        return false;
    }
    sectionJson = JSON.stringify(sectionData)
    $.ajax({
        type: 'POST',
        url: '../lib/l-mngdRprt.php?function=1&functionToCall=addSection',
        dataType: 'json',
        data: sectionJson
    }).done(function(data) {
        $("#error").html('');
        $("#error").html(data.msg);
        setTimeout(function() {
            $(".fclose").click();
            location.reload();
        }, 1500);
    });
}

function formatInputData() {

    var formattedData = {}
    var sectionName = '';
    var subHeaderName = '';
    var chartType = 0;
    var sectionType = 0;
    var subHeaderType = 1;
    var filterType = '';
    var filterId = [];
    var groupVal = [];
    var eventDuration = 0;
    var updateType = [];
    var updateSize = '';
    var month = '';
    var year = '';
    var osType = [];
    var header = 1;
    var subSecData = [];
    var temp = [];
    var summaryhead = 1;
    var msg = '';

    sectionType = $('#section_id').val();
    formattedData.sectionName = $('#SectionName').val();
    formattedData.sectionType = sectionType;
    if(sectionType == 4) {
        formattedData.chartType = $('#summary_chart_type').val();
    } else {
    formattedData.chartType = $('#chart_type').val();
    }

    if (sectionType == 1 || sectionType == 2) {

        formattedData.filterType = $('#section_id').val();
        subHeaderType = $('#subHeaderType').val();
        formattedData.subHeaderType = subHeaderType;

        if (formattedData.subHeaderType == 0) {
            msg = 'Please select sub header';
            return msg;
        }
        if (formattedData.subHeaderType == 1) {
            if ($('#subheader_group').val() == '') {
                msg = 'Please select group name';
                return msg;
            }
            formattedData.groupVal = $('#subheader_group').val();
        } else {
            formattedData.groupVal = 0;
            header = $('#head_count').val();
        }

        if (header > 1) {
            for (var i = 1; i <= header; i++) {
                if ($('#Subheader_' + i).val() == '') {
                    msg = 'Please enter sub header name';
                    return msg;
                }
                if ($('#filterType_' + i).val() == 0) {
                    msg = 'Please select a query/filter';
                    return msg;
                }
                subHeaderName = $('#Subheader_' + i).val();
                filterId = $('#filterType_' + i).val();
                if (sectionType == 1) {
                    if ($('#event_duration_' + i).val() == 0) {
                        msg = 'Please select event duration';
                        return msg;
                    }
                    eventDuration = $('#event_duration_' + i).val();
                } else {
                    eventDuration = 0;
                }
                temp[i] = {subheadername: subHeaderName, filterType: formattedData.filterType, filterid: filterId, eventduration: eventDuration};
            }
        } else {

            if ($('#Subheader_' + header).val() == '') {
                msg = 'Please enter sub header name';
                return msg;
            }
            if ($('#filterType_' + header).val() == 0) {
                msg = 'Please select a query/filter';
                return msg;
            }
            formattedData.subHeaderName = $('#Subheader_' + header).val();
            formattedData.filterId = $('#filterType_' + header).val();
            if (sectionType == 1) {
                if ($('#event_duration_' + header).val() == 0) {
                    msg = 'Please select event duration';
                    return msg;
                }
                formattedData.eventDuration = $('#event_duration_' + header).val();
            } else {
                formattedData.eventDuration = 0;
            }
            temp[1] = {subheadername: formattedData.subHeaderName, filterType: formattedData.filterType, filterid: formattedData.filterId, eventduration: formattedData.eventDuration};
        }
        formattedData.subSecData = temp;

        formattedData.updateType = 0;
        formattedData.osType = 0;
        formattedData.updateSize = 0;
        formattedData.month = 0;
        formattedData.year = 0;
    }

    if (sectionType == 3) {
        $('#sum_header').find(':input').each(function() {
            if ($(this).is(':checked')) {
                groupVal += $(this).val() + ',';
            }
        });

        if (groupVal.length < 1) {
            msg = 'Please select atleast one Summary header';
            return msg;
        }
        groupVal = groupVal.toString();
        formattedData.groupVal = groupVal;

        $('.update_type option').each(function() {
            updateType.push($(this).val());
        });

        updateType = updateType.toString();
        formattedData.updateType = updateType;

        osType = $('#OS').val();
        if (osType === null) {
            msg = 'Please select os type';
            return msg;
        }
        osType = osType.toString();
        formattedData.osType = osType;

        $(".include_patch option").each(function() {
            filterId.push($(this).val());
        });
        filterId = filterId.toString();
        formattedData.filterId = filterId;

        if ($('#updateSize').val() == 0) {
            msg = 'Please select update size';
            return msg;
        }

        formattedData.updateSize = $('#updateSize').val();
        formattedData.month = $('#month').val();
        formattedData.year = $('#year').val();

        formattedData.subHeaderName = 'MUM Summary';
        formattedData.filterType = 0;
        formattedData.subHeaderType = 0;
        formattedData.eventDuration = 0;
        temp[1] = {subheadername: formattedData.subHeaderName, filterType: formattedData.filterType, filterid: formattedData.filterId, eventduration: formattedData.eventDuration};
        formattedData.subSecData = temp;
    }

    if (sectionType == 4) {
        summaryhead = $('.summary_count').last().val();
//        alert(summaryhead);
        formattedData.sectionName = $('#SummaryName').val();

        if(formattedData.chartType == 6){
            formattedData.pivotChart = $('#pivot_chart_type').val();
        }
        if (summaryhead > 1) {
            for (var i = 1; i <= summaryhead; i++) {

                if ($('#SubSummaryName' + i).val() == '') {
                    msg = 'Please enter sub summary name';
                    return msg;
                }
                if ($('#filterType' + i).val() == 0) {
                    $('#error1').html('<span>Please select a filter type</span>');
                    return msg;
                }
                if ($('#summaryFilter' + i).val() == 0) {
                    msg = 'Please select a query/filter';
                    return msg;
                }
                subHeaderName = $('#SubSummaryName' + i).val();
                filterType = $('#filterType' + i).val();
                filterId = $('#summaryFilter' + i).val();
                if (filterType == 1) {
                    if ($('#eventDuration' + i).val() == 0) {
                        msg = 'Please select event duration';
                        return msg;
                    }
                    eventDuration = $('#eventDuration' + i).val();
                } else {
                    eventDuration = 0;
                }
                temp[i] = {subheadername: subHeaderName, filterType: filterType, filterid: filterId, eventduration: eventDuration};
            }
        } else {

            if ($('#SubSummaryName' + summaryhead).val() == '') {
                msg = 'Please enter sub summary name';
                return msg;
            }
            if ($('#filterType' + summaryhead).val() == 0) {
                msg = 'Please filter type';
                return msg;
            }
            if ($('#summaryFilter' + summaryhead).val() == 0) {
                msg = 'Please select a query/filter';
                return msg;
            }
            subHeaderName = $('#SubSummaryName' + summaryhead).val();
            filterType = $('#filterType' + summaryhead).val();
            filterId = $('#summaryFilter' + summaryhead).val();
            if (filterType == 1) {
                if ($('#eventDuration' + summaryhead).val() == 0) {
                    msg = 'Please select event duration';
                    return msg;
                }
                eventDuration = $('#eventDuration' + summaryhead).val();
            } else {
                eventDuration = 0;
            }
            temp[1] = {subheadername: subHeaderName, filterType: filterType, filterid: filterId, eventduration: eventDuration};
        }
        formattedData.subSecData = temp;

        formattedData.groupVal = 0;
        formattedData.updateType = 0;
        formattedData.osType = 0;
        formattedData.updateSize = 0;
        formattedData.month = 0;
        formattedData.year = 0;
    }
    return formattedData;
}

// to clear html on cancel
$('#createSection').on('hidden.bs.modal', function() {

//    $('#sectionAdd .form-group').addClass('is-empty');
    $('.form-group input').val('');
    $('.form-group select').val('');
    $('input:checkbox').removeAttr('checked');

    $('.section_type').show();
    $('.SectionName').show();
    $('.chart_type').show();
    $('.subHeader_type').hide();
    $('.subHeader_name').hide();
    $('.summary_section').hide();
    $('.summarySection').hide();
    $('.summary_header').hide();
    $('.multiple_subSection').hide();
    $('.mutilpe-summarySection').hide();
    $('#subHead_group').show();
    $('.addNewSubheader').hide();

    $('.section').mCustomScrollbar('destroy');
    $('.selectpicker').selectpicker('refresh');
//    $('.form-group').find('input,textarea,select').val('');
});

//View section
function viewSection() {

    $('#sectiondetails').modal('show');
    showSectionDetails();

}

function showSectionDetails() {
    $.ajax({
        type: "post",
        url: '../lib/l-mngdRprt.php?function=1&functionToCall=getSectionDetails',
        data: { 'csrfMagicToken': csrfMagicToken },
        dataType: 'json',
        success: function(gridData) {
            $('#sectionDetailsList').DataTable().destroy();
            sectionTable = $('#sectionDetailsList').DataTable({
                scrollY: jQuery('#sectionDetailsList').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: false,
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
                columnDefs: [
                    {
                        targets: "datatable-nosort",
                        orderable: false
                    },
                    {
                        className: "table-plus",
                        targets: 0
                    },
                ],
                drawCallback: function(settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                }
            });
        },
        error: function(msg) {

        }
    });
    $('#sectionDetailsList').on('click', 'tr', function() { //row selection code
        sectionTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        if ($(this).hasClass('selected')) {
            var rowdata = sectionTable.row(this).data();
            $("#sectionId").val(rowdata.id);
        } else {
            var rowdata = sectionTable.row(this).data();
            sectionTable.$('tr.selected').removeClass('selected');
            $("#sectionId").val(rowdata.id);
            $(this).addClass('selected');
        }
    });
}

function editSectionDetails() {
    sectionid = $('#sectionId').val();
    $('#error2').html('');
    $('#error2').show();

    if (sectionid == '') {
        $('#error2').html('<span>Please select one section to edit</span>');
        setTimeout(function() {
            $("#error2").fadeOut(3600);
        }, 3600);
        return false;
    }

    $('#sectiondetails').modal('hide');
    $('#editSection').modal('show');

    $.ajax({
        url: '../lib/l-mngdRprt.php?function=1&functionToCall=editSectionDetails&id=' + sectionid,
        type: 'post',
        dataType: 'json',
    }).done(function(data) {
        $('#edit_sectionid option[value=' + data.sectionType + ']').attr("selected", true);
        $('.selectpicker').selectpicker('refresh');
        $('#edit_sectionid').prop('disabled', true);
        $('.editsection').mCustomScrollbar('destroy');
        if (data.sectionType == 1 || data.sectionType == 2) {
            editEventPopup(data);

        } else if (data.sectionType == 3) {
            editMumPopup(data);

        } else if (data.sectionType == 4) {
            editSummaryPopup(data);

        }
    });
}

function editSection() {

    var editform = formatEditInput();

    if (typeof editform === 'string') {
        $('#editerror1').html(editform);
        setTimeout(function() {
            $("#editerror1").fadeOut(3600);
        }, 3600);
        return false;
    }

    var editData = JSON.stringify(editform);

    $.ajax({
        type: 'POST',
        url: '../lib/l-mngdRprt.php?function=1&functionToCall=editSection',
        dataType: 'json',
        data: editData
    }).done(function(data) {
        $("#editerror").html('');
        $("#editerror").html(data.msg);
        setTimeout(function() {
            $(".fclose").click();
            location.reload();
        }, 1500);
    });

}

function formatEditInput() {

    var formattedData = {}
    var editSectionName = '';
    var editSubHeaderName = '';
    var editChartType = 0;
    var editSectionType = 0;
    var editSubHeaderType = 1;
    var editFilterType = '';
    var editFilterId = [];
    var editGroupVal = [];
    var editEventDuration = 0;
    var editUpdateType = [];
    var editUpdateSize = '';
    var editMonth = '';
    var editYear = '';
    var editOsType = [];
    var editHeader = 1;
    var editSubSecData = [];
    var editTemp = [];
    var editSummaryhead = 1;
    var editErroMsg = '';

    var sectionType = $('#edit_sectionid').val();
    var sectionId = sectionid;
    formattedData.sectionId = sectionId;
    formattedData.editSectionName = $('#edit_SectionName').val();
    formattedData.editSectionType = sectionType;
    if(sectionType == 4) {
        formattedData.editChartType = $('#edit_summary_chart_type').val();
        if($('#edit_summary_chart_type').val() == 6) {
            formattedData.editpivotType = $('#edit_summary_chart_type').val();
        }
    } else {
        formattedData.editChartType = $('#edit_chartType').val();
    }
    if (sectionType == 1 || sectionType == 2) {

        formattedData.editFilterType = $('#edit_sectionid').val();
        editSubHeaderType = $('#edit_subHeaderType').val();
        formattedData.editSubHeaderType = editSubHeaderType;

        if (formattedData.editSubHeaderType == 1) {
            if ($('#edit_subheader_group').val() == '') {
                editErroMsg = 'Please select group name';
                return editErroMsg;
            }
            formattedData.editGroupVal = $('#edit_subheader_group').val();
        } else {
            formattedData.editGroupVal = 0;
            editHeader = $('.edit_head_count').last().val();
        }

        if (editHeader > 1) {
            for (var i = 1; i <= editHeader; i++) {
                if ($('#edit_subHeaderName_' + i).val() == '') {
                    editErroMsg = 'Please enter sub header name';
                    return editErroMsg;
                }
                if ($('#edit_filterType_' + i).val() == 0) {
                    editErroMsg = 'Please select a query/filter';
                    return editErroMsg;
                }
                editSubHeaderName = $('#edit_subHeaderName_' + i).val();
                editFilterId = $('#edit_filterType_' + i).val();
                if (sectionType == 1) {
                    if ($('#edit_event_duration_' + i).val() == 0) {
                        editErroMsg = 'Please select event duration';
                        return editErroMsg;
                    }
                    editEventDuration = $('#edit_event_duration_' + i).val();
                } else {
                    editEventDuration = 0;
                }
                editTemp[i] = {editSubheadername: editSubHeaderName, editFilterType: formattedData.editFilterType, editFilterid: editFilterId, editEventduration: editEventDuration};
                console.log(editTemp[i]);
            }
//            formattedData.subSecData = temp;
        } else {

            if ($('#edit_subHeaderName_' + editHeader).val() == '') {
                editErroMsg = 'Please enter sub header name';
                return editErroMsg;
            }
            if ($('#edit_filterType_' + editHeader).val() == 0) {
                editErroMsg = 'Please select a query/filter';
                return editErroMsg;
            }
            formattedData.editSubHeaderName = $('#edit_subHeaderName_' + editHeader).val();
            formattedData.editFilterId = $('#edit_filterType_' + editHeader).val();
            if (sectionType == 1) {
                if ($('#edit_event_duration_' + editHeader).val() == 0) {
                    editErroMsg = 'Please select event duration';
                    return editErroMsg;
                }
                formattedData.editEventDuration = $('#edit_event_duration_' + editHeader).val();
            } else {
                formattedData.editEventDuration = 0;
            }
            editTemp[1] = {editSubheadername: formattedData.editSubHeaderName, editFilterType: formattedData.editFilterType, editFilterid: formattedData.editFilterId, editEventduration: formattedData.editEventDuration};
        }
        formattedData.editSubSecData = editTemp;

        formattedData.editUpdateType = 0;
        formattedData.editOsType = 0;
        formattedData.editUpdateSize = 0;
        formattedData.editMonth = 0;
        formattedData.editYear = 0;
    }

    if (sectionType == 3) {
        $('#edit_sum_header').find(':input').each(function() {
            if ($(this).is(':checked')) {
                editGroupVal += $(this).val() + ',';
            }
        });

        if (editGroupVal.length < 1) {
            editErroMsg = 'Please select atleast one Summary header';
            return editErroMsg;
        }
        editGroupVal = editGroupVal.toString();
        formattedData.editGroupVal = editGroupVal;

        $('#edit_include_update option').each(function() {
            editUpdateType.push($(this).val());
        });

        editUpdateType = editUpdateType.toString();
        formattedData.editUpdateType = editUpdateType;

        editOsType = $('#edit_OS').val();
        if (editOsType === null) {
            editErroMsg = 'Please select os type';
            return editErroMsg;
        }
        editOsType = editOsType.toString();
        formattedData.editOsType = editOsType;

        $("#edit_include_patch option").each(function() {
            editFilterId.push($(this).val());
        });
        editFilterId = editFilterId.toString();
        formattedData.editFilterId = editFilterId;

        if ($('#edit_updateSize').val() == 0) {
            editErroMsg = 'Please select update size';
            return editErroMsg;
        }

        formattedData.editUpdateSize = $('#edit_updateSize').val();
        formattedData.editMonth = $('#edit_month').val();
        formattedData.editYear = $('#edit_year').val();

        formattedData.editSubHeaderName = 'MUM Summary';
        formattedData.editFilterType = 0;
        formattedData.editSubHeaderType = 0;
        formattedData.editEventDuration = 0;
        editTemp[1] = {editSubheadername: formattedData.editSubHeaderName, editFilterType: formattedData.editFilterType, editFilterid: formattedData.editFilterId, editEventduration: formattedData.editEventDuration};
        formattedData.editSubSecData = editTemp;
    }
    if (sectionType == 4) {

        editSummaryhead = $('.edit_summary_count').last().val();
        formattedData.editSectionName = $('#edit_SummaryName').val();

        if (editSummaryhead > 1) {
            for (var i = 1; i <= editSummaryhead; i++) {

                if ($('#edit_SubSummaryName' + i).val() == '') {
                    editErroMsg = 'Please enter sub summary name';
                    return editErroMsg;
                }
                if ($('#edit_filterType' + i).val() == 0) {
                    $('#error1').html('<span>Please select a filter type</span>');
                    return editErroMsg;
                }
                if ($('#edit_summaryFilter' + i).val() == 0) {
                    editErroMsg = 'Please select a query/filter';
                    return editErroMsg;
                }
                editSubHeaderName = $('#edit_SubSummaryName' + i).val();
                editFilterType = $('#edit_filterType' + i).val();
                editFilterId = $('#edit_summaryFilter' + i).val();
                if (editFilterType == 1) {
                    if ($('#edit_eventDuration' + i).val() == 0) {
                        editErroMsg = 'Please select event duration';
                        return editErroMsg;
                    }
                    editEventDuration = $('#edit_eventDuration' + i).val();
                } else {
                    editEventDuration = 0;
                }
                editTemp[i] = {editSubheadername: editSubHeaderName, editFilterType: editFilterType, editFilterid: editFilterId, editEventduration: editEventDuration};
            }
        } else {

            if ($('#edit_SubSummaryName' + editSummaryhead).val() == '') {
                editErroMsg = 'Please enter sub summary name';
                return editErroMsg;
            }
            if ($('#edit_filterType' + editSummaryhead).val() == 0) {
                editErroMsg = 'Please filter type';
                return editErroMsg;
            }
            if ($('#edit_summaryFilter' + editSummaryhead).val() == 0) {
                editErroMsg = 'Please select a query/filter';
                return editErroMsg;
            }
            editSubHeaderName = $('#edit_SubSummaryName' + editSummaryhead).val();
            editFilterType = $('#edit_filterType' + editSummaryhead).val();
            editFilterId = $('#edit_summaryFilter' + editSummaryhead).val();
            if (editFilterType == 1) {
                if ($('#edit_eventDuration' + editSummaryhead).val() == 0) {
                    editErroMsg = 'Please select event duration';
                    return editErroMsg;
                }
                editEventDuration = $('#edit_eventDuration' + editSummaryhead).val();
            } else {
                editEventDuration = 0;
            }
            editTemp[1] = {editSubheadername: editSubHeaderName, editFilterType: editFilterType, editFilterid: editFilterId, editEventduration: editEventDuration};
        }
        formattedData.editSubSecData = editTemp;

        formattedData.editGroupVal = 0;
        formattedData.editUpdateType = 0;
        formattedData.editOsType = 0;
        formattedData.editUpdateSize = 0;
        formattedData.editMonth = 0;
        formattedData.editYear = 0;
    }
    return formattedData;

}

function editEventPopup(data) {

    $('.edit_summary_name').hide();
    $('.sumSection').hide();
    $('.edit_mum_section').hide();
    $('#edit_Update_type').hide();
    $('#edit_OS').hide();
    $('#edit_date').hide();
    $('#edit_year').hide();
    $('.edit_includePatch').hide();
    $('.edit_summary_section').hide();
    $('.edit_summarySection').hide();

    $('.edit_SectionName').show();
    $('.edit_SectionName').addClass('is-focused');
    $('#edit_SectionName').val(data.sectionName);
    $('.edit_chartType').show();
    $('#edit_chartType option[value=' + data.chartType + ']').attr("selected", true);
    $('.selectpicker').selectpicker('refresh');
    $('.edit_subHeaderType').show();
    $('#edit_subHeaderType').val(data.subHeaders);
    $('.selectpicker').selectpicker('refresh');

    var index = 1;
    var currentPosition = 0; // to skip first index of return array because first index should load default div
    for (var value in data.subData) {

        $('.edit_subHeader').show();
        if (data.subHeaders == 1) {
            $('.addNewSubheader').hide();
            $('#edit_subHeaderName_' + index).val(data.subData[value].subHeaderName);
            $('#edit_subHead_group').show();
            populateEditFilter(1, data.sectionType, data.subData[value].filterId, data.subData[value].groupName, 1);
            $('#edit_subHead_group').show();
            $('.editEventDuration').hide();
            if (data.sectionType == 1) {
                $('.editEventDuration').show();
                $('#edit_event_duration_' + index + ' option[value=' + data.subData[value].eventDuration + ']').attr("selected", true);
                $('.selectpicker').selectpicker('refresh');
            } else {
                $('.editEventDuration').hide();
            }

        } else {
            $('#edit_subHead_group').hide();
            $('.addNewSubheader').show();
            if (currentPosition == 0) {
                $('#edit_subHeaderName_' + index).val(data.subData[value].subHeaderName);
                populateEditFilter(index, data.sectionType, data.subData[value].filterId, data.subData[value].groupName, 2);
                $('.editEventDuration').hide();

                if (data.sectionType == 1) {
                    $('.editEventDuration').show();
                    $('#edit_event_duration_' + index + ' option[value=' + data.subData[value].eventDuration + ']').attr("selected", true);
                    $('.selectpicker').selectpicker('refresh');
                } else {
                    $('.editEventDuration').hide();
                }
            } else {
                index++;
                var multiSubhead = showMultipleSubheader(index, data.subData[value], 1);
                $('#edit_subHeaderName_' + index).val(data.subData[value].subHeaderName);
                populateEditFilter(index, data.sectionType, data.subData[value].filterId, data.subData[value].groupName, 2);
            }
            currentPosition++;
        }
    }
}

function editMumPopup(data) {

    $('.edit_SectionName').show();
    $(".editsection").mCustomScrollbar({theme: "minimal-dark"});
    $('.edit_SectionName').addClass('is-focused');
    $('#edit_SectionName').val(data.sectionName);
    $('.edit_chartType').show();
    $('#edit_chartType option[value=' + data.chartType + ']').attr("selected", true);
    $('.selectpicker').selectpicker('refresh');

    $('.edit_subHeaderType').hide();
    $('.edit_subHeaderName').hide();
    $('#edit_filterType_1').hide();
    $('#edit_subheader_group').hide();
    $('.editEventDuration').hide();
    $('.edit_summary_name').hide();
    $('.sumSection').hide();
    $('.edit_summary_section').hide();
    $('.edit_summarySection').hide();
    $('.edit_subHeader').hide();

    $('.edit_mum_section').show();
    for (var grp in data.subData) {
        var groupname = data.subData[grp].groupName;
        var updateType = data.subData[grp].updateType;
        var updateSize = data.subData[grp].updateSize;
        var os = data.subData[grp].os;
        var month = data.subData[grp].month;
        var year = data.subData[grp].year;
        var filterId = data.subData[grp].filterId;
    }

    groupname = groupname.split(',');
    for (var group in groupname) {
        $('#edit_sum_header input[value=' + groupname[group] + ']').prop("checked", "checked");
    }
    $('#edit_Update_type').show();
    updateType = updateType.split(',');
    for (var index in updateType) {
        $('#edit_include_update option[value=' + updateType[index] + ']').show();
        $('#edit_exclude_update option[value=' + updateType[index] + ']').hide();
    }

    $('#edit_updateSize option[value=' + updateSize + ']').attr("selected", true);
    $('.selectpicker').selectpicker('refresh');

    $('#edit_OS').show();
    os = os.split(',');
    for (var ostype in os) {
        $("#edit_OS").val(os[ostype]).change();
        $('.selectpicker').selectpicker('refresh');
    }

    $('#edit_date').show();
    $('#edit_month option[value=' + month + ']').attr("selected", true);
    $('.selectpicker').selectpicker('refresh');
    $('#edit_year').show();
    $('#edit_year option[value=' + year + ']').attr("selected", true);
    $('.selectpicker').selectpicker('refresh');

    $('.edit_includePatch').show();
    var edit = populateEditPatch(filterId);
    $('.selectpicker').selectpicker('refresh');
}

$('#edit_summary_chart_type').on('change',function() {
   if($('#edit_summary_chart_type').val() == 5) {
       $('.edit_pivot_chart_type').hide();
   } else {
       $('.edit_pivot_chart_type').show();
   }
});

function editSummaryPopup(data) {
//console.log(data);
    $('.edit_SectionName').hide();
    $('.edit_subHeaderType').hide();
    $('#edit_filterType_1').hide();
    $('#edit_subheader_group').hide();
    $('.editEventDuration').hide();
    $('.edit_mum_section').hide();
    $('#edit_Update_type').hide();
    $('.edit_subHeader').hide();
    $('#edit_OS').hide();
    $('#edit_date').hide();
    $('#edit_year').hide();
    $('.edit_chartType').hide();

    $('.edit_summary_section').show();
    $('.editSummary_name').addClass('is-focused');
    $('#edit_SummaryName').val(data.sectionName);

    var index = 1;
    var initialPos = 0;
    $('.edit_summarySection').show();
    for (var val in data.subData) {
        if (initialPos == 0) {

            $('.edit_Subsummaryname').addClass('is-focused');
            $('#edit_SubSummaryName' + index).val(data.subData[val].subHeaderName);
            $('#edit_summary_chart_type option[value=' + data.subData[val].chartType + ']').attr("selected", true);
            $('.selectpicker').selectpicker('refresh');
            $('#edit_pivot_chart_type option[value=' + data.subData[val].pivotType + ']').attr("selected", true);
            $('.selectpicker').selectpicker('refresh');
            $('#edit_filterType' + index + ' option[value=' + data.subData[val].filterType + ']').attr("selected", true);
            $('.selectpicker').selectpicker('refresh');
            populateEditSummary(index, data.subData[val].filterId, data.subData[val].filterType, data.subHeaders, data.subData[val].eventDuration);

        } else {
            editSummaryHeader(index, data.subData[val], 1);
        }
        index++;
        initialPos++;

    }
//    $('#removeSub').hide();
}

function populateEditFilter(header, sectionType, filterId, groupName, subheader) {

    var eventOptions = '';
    var assetOptions = '';

    if (sectionType == 1) {
        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getEventFilters",
            dataType: 'json',
            data: { 'csrfMagicToken': csrfMagicToken }
        }).done(function(data) {
            editEvent = data;
            /*eventOptions = '<option value="0">Choose filter</option>';
            for (var i = 0; i < data.length; i++) {
                if (data[i].id == filterId) {
                    eventOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '" selected>' + data[i].name + '</option>';
                } else {
                    eventOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
                }
            }*/
            eventEditData = editEvent;
//            console.log(eventOptions);
            $('#edit_filterType_' + header).html("");
            $('#edit_filterType_' + header).html(editEvent);
            $('.selectpicker').selectpicker('refresh');
        });

    } else if (sectionType == 2) {
        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getAssetQueries",
            dataType: 'json',
            data: { 'csrfMagicToken': csrfMagicToken }
        }).done(function(data) {
            editAsset = data.array;
            /*assetOptions = '<option value="0">Choose Query</option>';
            for (var i = 0; i < data.length; i++) {
                if (data[i].id == filterId) {
                    assetOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '" selected>' + data[i].name + '</option>';
                } else {
                    assetOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
                }
            }
            assetEditData = assetOptions;*/
            assetEditData = data.option;
//           console.debug(assetOptions); 
            $('#edit_filterType_' + header).html("");
            $('#edit_filterType_' + header).html(data.option);
            $('.selectpicker').selectpicker('refresh');
        });
    }
    if (subheader == 1) {
        populateEditGroupingOptions(1, sectionType, filterId, groupName);
    }
    return true;
}

function populateEditSummary(header, filterId, filterType, subheader, eventDuration) {

    if (filterType == 1) {
        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getEventFilters",
            dataType: 'json',
            data: { 'csrfMagicToken': csrfMagicToken }
        }).done(function(data) {
            /*var eventOptions = '<option value="0">Choose filter</option>';
            for (var i = 0; i < data.length; i++) {
                if (filterId == data[i].id) {
                    eventOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '" selected>' + data[i].name + '</option>';
                } else {
                    eventOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
                }
            }
            summaryEditData = eventOptions;*/
            summaryEditData = data;
            $('#edit_summaryFilter' + header).html("");
            $('#edit_summaryFilter' + header).html(data);
            $('.selectpicker').selectpicker('refresh');
            $('.edit_eventDuration' + header).show();
            $('#edit_eventDuration' + header + ' option[value=' + eventDuration + ']').attr("selected", true);
            $('.selectpicker').selectpicker('refresh');

        });
    } else if (filterType == 2) {
        //asset option
        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getAssetQueries",
            dataType: 'json',
            data: { 'csrfMagicToken': csrfMagicToken }
        }).done(function(data) {
            /*var assetOptions = '<option value="0">Choose filter</option>';
            for (var i = 0; i < data.length; i++) {
                if (filterId == data[i].id) {
                    assetOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '" selected>' + data[i].name + '</option>';
                } else {
                    assetOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
                }
            }
            summaryAssetData = assetOptions;*/
            assetOptions = data.option;
            summaryAssetData = data.array;
            $('#edit_summaryFilter' + header).html("");
            $('#edit_summaryFilter' + header).html(assetOptions);
            $('.selectpicker').selectpicker('refresh');
            $('.edit_eventDuration' + header).hide();

        });
    }
    return true;
}

function populateEditGroupingOptions(header, sectionType, filterId, groupVal) {

    var options = '';

    if (sectionType == 1) {
        options += '<option value="Machine">Machine</option>' +
                '<option value="Site">Site</option>' +
                '<option value="User Name">User Name</option>' +
                '<option value="Scrip">Scrip</option>' +
                '<option value="Executable">Executable</option>' +
                '<option value="Windows Title">Windows Title</option>';
    }
    else if (sectionType == 2) {
        var grpObj = JSON.parse(editAsset[0]['groupby']);
        grpObj[filterId].trim();
        var grpOption = grpObj[filterId].split(":");

        for (var i = 0; i < grpOption.length; i++) {
            if (grpOption[i] !== "" && grpOption[i] !== null && grpOption[i] !== 'undefined') {
                if (grpOption[i] == groupVal) {
                    options += '<option value="' + grpOption[i] + '" selected>' + grpOption[i] + '</option>';
                } else {
                    options += '<option value="' + grpOption[i] + '">' + grpOption[i] + '</option>';
                }
            }
        }
    }
//   console.log(options);
    $('#edit_subheader_group').html('');
    $('#edit_subheader_group').html(options);
    $('.selectpicker').selectpicker('refresh');
    return true;

}

function populateEditPatch(filterId) {

    var month = $('#edit_month').val();
    var year = $('#edit_year').val();
    if (filterId === undefined) {
        var filterid = 0;
    } else {
        var filterid = filterId.split(',');
    }

    $.ajax({
        type: 'post',
        url: '../lib/l-mngdRprt.php?function=1&functionToCall=getPatchDetails&mnth=' + month + '&year=' + year,
        dataType: 'json'
    }).done(function(data) {

        var includePatch = '';
        var excludePatch = '';
        if (filterid == 0) {
            for (var i = 0; i < data.length; i++) {
                includePatch += '<option value="' + data[i].id + '" title="' + data[i].name + '">' + data[i].name + '</option>';
                excludePatch += '';
            }
        } else {
            for (var i = 0; i < data.length; i++) {
                for (var index in filterid) {
                    if (filterid[index] === data[i].id) {
                        includePatch += '<option value="' + data[i].id + '" title="' + data[i].name + '">' + data[i].name + '</option>';
                    } else {
                        excludePatch += '<option value="' + data[i].id + '" title="' + data[i].name + '">' + data[i].name + '</option>';
                    }
                }
            }
        }
        $('#edit_exclude_patch').html("");
        $('#edit_exclude_patch').html(excludePatch);
        $('.selectpicker').selectpicker('refresh');

        $('#edit_include_patch').html("");
        $('#edit_include_patch').html(includePatch);
        $('.selectpicker').selectpicker('refresh');
    });
    return true;
}

function showMultipleSubheader(index, data, header) {

    indexinc = index + 1;
    var subHeaders = [];
    subHeaders[header] = index;
    if (subHeaders[header] > 3) {
        $(".editsection").mCustomScrollbar({theme: "minimal-dark"});
    }
    var htmlSubHdr = '<div class="row clearfix multiple_subSection">' +
            '<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">' +
            '<div class="add-sumsecdata">' +
            '<a href="javascript:" onclick="showMultipleSubheader(' + indexinc + ',this,1)" id="addNewSubheader" class="addNewSubheader""><i class="icon-ic_add_24px material-icons"></i></a>' +
            '<div class="form-group label-floating is-empty is-focused">' +
            '<input type="hidden" class="edit_head_count" value="' + subHeaders[header] + '">' +
            '<label for="edit_subHeaderName_' + subHeaders[header] + '" class="control-label">Enter Sub Header Name</label>' +
            '<input class="form-control" id="edit_subHeaderName_' + subHeaders[header] + '" type="text">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">' +
            '<div class="form-group filter_type">' +
            '<select class="form-control selectpicker dropdown-submenu" data-size="5" id="edit_filterType_' + subHeaders[header] + '">' +
            '</select>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-5 col-md-12 col-sm-12 col-xs-12">' +
            '<div class="row clearfix">' +
            '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' +
            '<div class="remove-sumsecdata">' +
            '<div class="form-group editEventDuration" style="display:none">' +
            '<select class="form-control selectpicker dropdown-submenu" data-size="5" id="edit_event_duration_' + subHeaders[header] + '">' +
            '<option value="0" selected>Event Duration</option>' +
            '<option value="1" >Last 1 Day</option>' +
            '<option value="3">Last 3 Days</option>' +
            '<option value="7">Last 7 Days</option>' +
            '<option value="15">Last 15 Days</option>' +
            '<option value="60">Last 60 Days</option>' +
            '<option value="4">Latest</option>' +
            '</select>' +
            '</div>' +
            '<a href="javascript:" onclick="removeSubHdr(this)"><i class="material-icons icon-ic_close_24px"></i></a>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';

    $('#edit_section').append(htmlSubHdr);
    $('.selectpicker').selectpicker('refresh');
    var obj = $('#edit_sectionid').val();

    if (data.sectionType == 1) {
        $('.editEventDuration').show();
    } else {
        $('.editEventDuration').hide();
    }

    if (obj == 1) {
        $('#edit_filterType_' + subHeaders[header]).html("");
        $('#edit_filterType_' + subHeaders[header]).html(eventEditData);
        $('.editEventDuration').show();
        $('.selectpicker').selectpicker('refresh');
    }
    else if (obj == 2) {
        $('#edit_filterType_' + subHeaders[header]).html("");
        $('#edit_filterType_' + subHeaders[header]).html(assetEditData);
        $('.editEventDuration').hide();
        $('.selectpicker').selectpicker('refresh');
    }
    return true;
}

function editSummaryHeader(index, data, header) {

    var index2 = index + 1;
    var subSummary = [];
    subSummary[header] = index;
    if (index2 > 2) {
//        $(".editsection").mCustomScrollbar({theme: "minimal-dark"});
    }
    $('.edit_Subsummaryname').removeClass('label-floating');
    var summaryHeaders = '<div class="row clearfix mutilpe-summarySection" >' +
            '<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12">' +
            '<div class="add-sumsecdata">' +
            '<a href="javascript:" onclick="editSummaryHeader(' + index2 + ',this)" id="editNewSummary"><i class="icon-ic_add_24px material-icons"></i></a>' +
            '<div class="form-group edit_Subsummaryname label-floating is-empty">' +
            '<input type="hidden" class="edit_summary_count" value="' + subSummary[header] + '">' +
            '<label for="edit_SubSummaryName' + subSummary[header] + '" class="control-label">Enter Sub Summary Name</label>' +
            '<input class="form-control" id="edit_SubSummaryName' + subSummary[header] + '" type="text">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">' +
            '<div class="form-group">' +
            '<select class="form-control selectpicker dropdown-submenu" data-size="5" ' +
            'id="edit_filterType' + subSummary[header] + '" onchange="editpopulateSummaryFilter(' + subSummary[header] + ',this)">' +
            '<option value="0">Filter Type</option>' +
            '<option value="1">Event Filter</option>' +
            '<option value="2">Asset Filter</option>' +
            '</select>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-5 col-md-12 col-sm-12 col-xs-12">' +
            '<div class="row clearfix ">' +
            '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' +
            '<div class="form-group edit_summary_filter" >' +
            '<select class="form-control selectpicker dropdown-submenu summaryFilter" ' +
            'data-size="5" id="edit_summaryFilter' + subSummary[header] + '">' +
            '<option value="0">Categorised By</option>' +
            '</select>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' +
            '<div class="remove-sumsecdata">' +
            '<div class="form-group edit_eventDuration' + subSummary[header] + '" style="display:none">' +
            '<select class="form-control selectpicker dropdown-submenu" data-size="5" id="edit_eventDuration' + subSummary[header] + '">' +
            '<option value="0" selected>Event Duration</option>' +
            '<option value="1" >Last 1 Day</option>' +
            '<option value="3">Last 3 Days</option>' +
            '<option value="7">Last 7 Days</option>' +
            '<option value="15">Last 15 Days</option>' +
            '<option value="60">Last 60 Days</option>' +
            '<option value="4">Latest</option>' +
            '</select>' +
            '</div>' +
            '<a href="javascript:" onclick="removeSubHdr(this)" ><i class="material-icons icon-ic_close_24px"></i></a>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';

    $('#edit_section').append(summaryHeaders);
    $('.selectpicker').selectpicker('refresh');
    if (data !== 'undefined') {
        $('.edit_Subsummaryname').addClass('is-focused');
        $('#edit_SubSummaryName' + subSummary[header]).val(data.subHeaderName);
        $('#edit_filterType' + subSummary[header] + ' option[value=' + data.filterType + ']').attr("selected", true);
        $('.selectpicker').selectpicker('refresh');
        populateEditSummary(index, data.filterId, data.filterType, data.subHeaders, data.eventDuration);
    }
    return true;
}

function editpopulateSummaryFilter(header, obj) {

    if ($('#edit_filterType' + header).val() == 1) {
        $('.edit_eventDuration' + header).show();
        $('#edit_summaryFilter' + header).html('');
        $('#edit_summaryFilter' + header).html(summaryEditData);
        $('.selectpicker').selectpicker('refresh');
    } else if ($('#edit_filterType' + header).val() == 2) {
        $('.edit_eventDuration' + header).hide();
        $('#edit_summaryFilter' + header).html('');
        $('#edit_summaryFilter' + header).html(summaryAssetData);
        $('.selectpicker').selectpicker('refresh');
    }
    return true;
}