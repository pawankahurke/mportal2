
$(document).ready(function () {
    getAdocReportInfo();

    $(".asset-radio").click(function () {
        $("#report_name").val('');
        $("#report_email").val('');
        $("#Error_Validation").html('');
        $(".asset_div").css("display", "block");
        $(".event_div").css("display", "none");
    });
    $(".event-radio").click(function () {
        $("#Error_Validation").html('');
        $("#report_name").val('');
        $("#report_email").val('');
        $(".asset_div").css("display", "none");
        $(".event_div").css("display", "block");
    });
});

function edit_reportRdio(ref) {
    var eventType = ($(ref).val());
    if (eventType === "Asset") {
        $(".Editasset_div").css("display", "block");
        $(".Editevent_div").css("display", "none");
    } else if (eventType === "Event") {
        var dartVal = $("#editevent_dart option:selected").val();
        var fltrVal = $("#editevent_filter option:selected").val();
        if (dartVal !== "null") {
            document.getElementById("editevent_filter").disabled = true;
        } else if (fltrVal != "null") {
            document.getElementById("editevent_dart").disabled = true;
        }
        $(".Editevent_div").css("display", "block");
        $(".Editasset_div").css("display", "none");

    }
}

function getAdocReportInfo() {

    $.ajax({
        type: "GET",
        url: "../lib/l-ajax.php",
        data: "function=AJAX_GetAdhocReportData",
        dataType: 'json',
        success: function (msg) {
            $(".se-pre-con").hide();
            $('#adhoc_ReportGrid').DataTable().destroy();
            adhoc_ReportGrid = $('#adhoc_ReportGrid').DataTable({
                scrollY: jQuery('#adhoc_ReportGrid').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                aaData: msg,
                bAutoWidth: false,
                select: false,
                bInfo:false,
                processing: true,
                responsive: true,
                stateSave: true,
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search",
//                    "emptyTable": "No configuration of any Customer found, please Configure Customer from top Menu (bread crumb)",
                    "processing": "<img src='../vendors/images/loader2.gif'> Loading..."
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function (settings, json) {

                    $("#adhoc_ReportGrid_filter").hide();
                },
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }

            });
            $('#adhoc_ReportGrid').on('click', 'tr', function () {
                var rowID = adhoc_ReportGrid.row(this).data();
                var selected = rowID['DT_RowId'];

                $("#selected").val(selected);
                adhoc_ReportGrid.$('tr.selected').removeClass('selected');
                $(this).addClass('selected');
            });

            $("#TicketTable_searchbox").keyup(function () {
                adhoc_ReportGrid.search(this.value).draw();
            });

        },
        error: function (response) {
            console.log("Something went wrong in ajax call of skipFunction function");
            console.log(response);
        }

    });
}

function query_dart() {
    var dartnum = $("#editevent_dart option:selected").val();
    if ((dartnum === "null") || (dartnum === null)) {
        document.getElementById("editevent_filter").disabled = false;
    } else if ((dartnum !== "null") || (dartnum !== null)) {
        document.getElementById("editevent_filter").disabled = true;
    }
}

function query_script() {
    var scrp_num = $("#editevent_filter option:selected").val();
    if ((scrp_num === "null") || (scrp_num === null)) {
        document.getElementById("editevent_dart").disabled = false;
    } else if ((scrp_num !== "null") || (scrp_num !== null)) {
        document.getElementById("editevent_dart").disabled = true;
    }
}

$("#addqueries").click(function () {
    $(".Error_fail").css("display", "none");
    $(".Error_exist").css("display", "none");
    $(".daysDIV").css("display", "none");
    $(".weekDIV").css("display", "none");
    $(".monthDIV").css("display", "none");
    document.getElementById('report_duration').selectedIndex = 0;
    $("#err_email").html('*');
    $("#Error_Validation").html('');
    var report_name = $("#report_name").val('');
    var report_email = $("#report_email").val('');

    $("#event_default").prop('checked', true);
    var selValue = $('input[name=eventType]:checked').val();
    $.ajax({
        url: "../lib/l-ajax.php?function=AJAX_GetSelect_Data&eventType=" + selValue,
        type: 'POST',
        dataType: 'text',
        success: function (response) {
            console.log(response);
            $(".event_SelectDIV").html(response);
            $("#adhoc_query_modal").modal('show');
        }
    });

});

function add_dart() {
    var dartnum = $("#dart_num option:selected").val();
    if ((dartnum === "null") || (dartnum === null)) {
        document.getElementById("scrp_num").disabled = false;
    } else if ((dartnum !== "null") || (dartnum !== null)) {
        document.getElementById("scrp_num").disabled = true;
    }
}
function add_scrip() {
    var scrp_num = $("#scrp_num option:selected").val();
    if ((scrp_num === "null") || (scrp_num === null)) {
        document.getElementById("dart_num").disabled = false;
    } else if ((scrp_num !== "null") || (scrp_num !== null)) {
        document.getElementById("dart_num").disabled = true;
    }
}

$("#edit_queries").click(function () {
    $("#editError_exist").css("display","none");
    $("#editError_fail").css("display","none");
    $("#Edit_reportError").html('');
    var selValue = $('#adhoc_ReportGrid tbody tr.selected').attr('id');
    //alert(selValue);
    if ((selValue === 'undefined') || (selValue === undefined)) {
        $("#select_adhocModal").modal('show');
    } else {
        $.ajax({
            url: "../lib/l-ajax.php?function=AJAX_GetSelectedEdit&selectedDataId=" + selValue,
            type: 'POST',
            dataType: 'text',
            success: function (response) {
                console.log(response);
                $("#edit_report_id").html(response);
                $("#EDIT_adhocquery_modal").modal('show');
            }
        });
    }

});

$("#Delete_queries").click(function () {

    var selValue = $('#adhoc_ReportGrid tbody tr.selected').attr('id');
    if ((selValue === 'undefined') || (selValue === undefined)) {
        $("#select_adhocModal").modal('show');
    } else {
        $("#delete_id").val(selValue);
        $("#select_DeleteModal").modal('show');
    }

});

$("#Delete_report").click(function () {
    var selValue = $('#delete_id').val();
        $.ajax({
            url: "../lib/l-ajax.php?function=AJAX_GetSelectedDel&selectedDataId=" + selValue,
            type: 'POST',
            dataType: 'text',
            success: function (response) {
                console.log(response);
                $("#select_DeleteModal").modal('hide');
                getAdocReportInfo();
            }
        });
});

//$(".cancel_query_btn").click(function () {
//    window.location.href = '../insights/adoc-queries.php';
//});
$("#Back_queries").click(function () {
    window.location.href = '../el_insights/el-home.php';

});


$('#details_queries').click(function () {
    var selectedDataId = $('#adhoc_ReportGrid tbody tr.selected').attr('id');

    if ((selectedDataId === 'undefined') || (selectedDataId === undefined)) {
        $("#select_adhocModal").modal('show');
    } else {
        $("#adhoc_query_Viewmodal").modal('show');
        $.ajax({
            url: "../lib/l-ajax.php?function=AJAX_GetSelectedView&selectedDataId=" + selectedDataId,
            type: 'POST',
            dataType: 'json',
            success: function (response) {
                $("#edit_report_id").html(response);
                $(".se-pre-con").hide();
                $('#adhoc_ViewReportGrid').DataTable().destroy();
                adhoc_ViewReportGrid = $('#adhoc_ViewReportGrid').DataTable({
                    scrollY: jQuery('#adhoc_ViewReportGrid').data('height'),
                    scrollCollapse: true,
                    paging: true,
                    searching: true,
                    ordering: true,
                    aaData: response,
                    bAutoWidth: false,
                    select: false,
                    bInfo:false,
                    processing: true,
                    responsive: true,
                    stateSave: true,
                    "stateSaveParams": function (settings, data) {
                        data.search.search = "";
                    },
                    "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                    "language": {
                        "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                        searchPlaceholder: "Search",
//                        "emptyTable": "No configuration of any Customer found, please Configure Customer from top Menu (bread crumb)",
                        "processing": "<img src='../vendors/images/loader2.gif'> Loading..."
                    },
                    "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                    initComplete: function (settings, json) {

                        $("#adhoc_ViewReportGrid_filter").hide();
                    },
                    drawCallback: function (settings) {
                        $(".dataTables_scrollBody").mCustomScrollbar({
                            theme: "minimal-dark"
                        });
                        $('.equalHeight').matchHeight();
                        $(".se-pre-con").hide();
                    }
                });
                $('#adhoc_ViewReportGrid').on('click', 'tr', function () {
                    var rowID = adhoc_ViewReportGrid.row(this).data();
                    var selected = rowID['DT_RowId'];

                    $("#selected").val(selected);
                    adhoc_ViewReportGrid.$('tr.selected').removeClass('selected');
                    $(this).addClass('selected');
                });

                $("#TicketTable_searchbox").keyup(function () {
                    adhoc_ViewReportGrid.search(this.value).draw();
                });
            }
        });
    }

});



function capacityexport() {
    window.location.href = '../lib/l-ajax.php?function=AJAX_GetCapacityReportDataExport';
}

function _getScheduleView() {

    var durati = $("#report_duration option:selected").val();
    
    if ((durati === "1") || (durati === 1)) {
        document.getElementById('days_hour').selectedIndex = null;
        document.getElementById('days_min').selectedIndex = null;
        $(".weekDIV").css("display", "none");
        $(".monthDIV").css("display", "none");
        $(".daysDIV").css("display", "block");
    } else if ((durati === "7") || (durati === "7")) {
        $(".daysDIV").css("display", "none");
        $(".monthDIV").css("display", "none");
        $(".weekDIV").css("display", "block");
    } else if ((durati === "30") || (durati === "30")) {
        $(".daysDIV").css("display", "none");
        $(".weekDIV").css("display", "none");
        $(".monthDIV").css("display", "block");
    } else if ((durati === '') || (durati === 'undefined')) {
        $(".daysDIV").css("display", "none");
        $(".weekDIV").css("display", "none");
        $(".monthDIV").css("display", "none");
    }
}
function _getEditScheduleView() {

    var durati = $("#Editreport_duration option:selected").val();
    if ((durati === "1") || (durati === 1)) {
        $(".EditweekDIV").css("display", "none");
        $(".EditmonthDIV").css("display", "none");
        $(".EditdaysDIV").css("display", "block");
    } else if ((durati === "7") || (durati === "7")) {
        $(".EditdaysDIV").css("display", "none");
        $(".EditmonthDIV").css("display", "none");
        $(".EditweekDIV").css("display", "block");
    } else if ((durati === "30") || (durati === "30")) {
        $(".EditdaysDIV").css("display", "none");
        $(".EditweekDIV").css("display", "none");
        $(".EditmonthDIV").css("display", "block");
    } else if ((durati === '') || (durati === 'undefined')) {
        $(".EditdaysDIV").css("display", "none");
        $(".EditweekDIV").css("display", "none");
        $(".EditmonthDIV").css("display", "none");
    }
}

function query_submit() {

    var report_type = $('input[name=eventType]:checked').val();
    var report_name = $("#report_name").val();
    var dart_num = $("#dart_num option:selected").val();
    var scrp_num = $("#scrp_num option:selected").val();
    var assSearch_num = $("#assSearch_num option:selected").val();
    var report_duration = $("#report_duration option:selected").val();
    var report_email = $("#report_email").val();

    if ((report_type === '') || (report_type === undefined)) {
        $("#Error_Validation").css("color", "red").html("Please Select Report Type");
        return false;
    }
    if ((report_name === '') || (report_name === undefined)) {
        $("#Error_Validation").css("color", "red").html("Please Enter Report Name");
        return false;
    }
    if ((report_name !== '') || (report_name !== undefined)) {
        var re = /^[ A-Za-z0-9_@./#&+-]*$/
        if (!re.test(report_name)) {
           $("#Error_Validation").css("color", "red").html("Please Enter Report Name with only alphanumericals");
            return false;
        }
    }
    if ((report_type !== '') || (report_type !== undefined)) {
        if (report_type === 'Event') {
            if (((dart_num === '') || (dart_num === undefined) || (dart_num === 'null')) && ((scrp_num === '') || (scrp_num === undefined) || (scrp_num === 'null'))) {
                $("#Error_Validation").css("color", "red").html("Please select either Scrip number or Event filter from drop down");
                return false;
            }
        } else if (report_type === 'Asset') {
            if ((assSearch_num === '') || (assSearch_num === undefined) || (assSearch_num === 'null')) {
                $("#Error_Validation").css("color", "red").html("Please select Asset Queries from drop down");
                return false;
            }
        }
    }

    if ((report_email === '') || (report_email === undefined)) {
        $("#Error_Validation").css("color", "red").html("Please Enter Email Id");
        return false;
    } else if ((report_duration === '') || (report_duration === undefined)) {
        $("#Error_Validation").css("color", "red").html("Please select Report Schedule");
        return false;
    }

    if ((report_duration !== '') || (report_duration !== undefined)) {
        if ((report_duration === "1") || (report_duration === 1)) {
            var dayshour = $("#days_hour option:selected").val();
            var days_min = $("#days_min option:selected").val();
            if ((dayshour === '') || (dayshour === undefined) || (dayshour === 'null')) {
                $("#Error_Validation").css("color", "red").html("Please Select Hour");
                return false;
            } else if ((days_min === '') || (days_min === undefined) || (days_min === 'null')) {
                $("#Error_Validation").css("color", "red").html("Please Select Minutes");
                return false;
            }

        } else if ((report_duration === "7") || (report_duration === 7)) {

            var weekday = $("#weekday option:selected").val();
            var week_hour = $("#week_hour option:selected").val();
            var week_min = $("#week_min option:selected").val();
            if ((weekday === '') || (weekday === undefined) || (weekday === 'null')) {
                $("#Error_Validation").css("color", "red").html("Please Select Weekday");
                return false;
            } else if ((week_hour === '') || (week_hour === undefined) || (week_hour === 'null')) {
                $("#Error_Validation").css("color", "red").html("Please Select Week Hour");
                return false;
            } else if ((week_min === '') || (week_min === undefined) || (week_min === 'null')) {
                $("#Error_Validation").css("color", "red").html("Please Select Week Minute");
                return false;
            }

        } else if ((report_duration === "30") || (report_duration === "30")) {

            var month_day = $("#month_day option:selected").val();
            var month_hour = $("#month_hour option:selected").val();
            var month_min = $("#month_min option:selected").val();
            if ((month_day === '') || (month_day === undefined) || (month_day === 'null')) {
                $("#Error_Validation").css("color", "red").html("Please Select day");
                return false;
            } else if ((month_hour === '') || (month_hour === undefined) || (month_hour === 'null')) {
                $("#Error_Validation").css("color", "red").html("Please Select Hour");
                return false;
            } else if ((month_min === '') || (month_min === undefined) || (month_min === 'null')) {
                $("#Error_Validation").css("color", "red").html("Please Select Minute");
                return false;
            }

        }
    }

    var res = validateEmailId(report_email);

    if ((res === "false") || (res === false)) {
        return false;
    } else if ((res === "undefined") || (res === undefined)) {

        var queryData = {report_type: report_type, report_name: report_name, dart_num: dart_num, scrp_num: scrp_num, assSearch_num: assSearch_num, report_duration: report_duration, report_email: report_email,
            dayshour: dayshour, days_min: days_min, weekday: weekday, week_hour: week_hour, week_min: week_min, month_day: month_day, month_hour: month_hour, month_min: month_min};

        $.ajax({
            type: "GET",
            url: "../lib/l-ajax.php?function=AJAX_Add_adhocQueries",
            data: queryData,
            dataType: 'text',
            success: function (msg) {
                var response = msg.trim();
                if (response === "success") {
                $("#adhoc_query_modal").modal('hide');
                    $(".cancel_query_btn").click();
                getAdocReportInfo();
                } else if (response === "failed") {
                    $("#Error_fail").css("display", "block");
                    
                } else if (response === "exist") {
                    $("#Error_exist").css("display", "block");
                    
            }

            }
        });
    }
}
function query_Editsubmit() {

    var report_type = $('input[name=EditeventType]:checked').val();
    var report_name = $("#Editreport_name").val();
    var dart_num = $(".Editdart_num option:selected").val();
    var scrp_num = $(".Editscrp_num option:selected").val();
    var assSearch_num = $(".EditassSearch_num option:selected").val();
    var report_duration = $("#Editreport_duration option:selected").val();
    var report_email = $("#Editreport_email").val();
    var editReportID = $("#editReportID").val();
    var Editreport_Status = $("#Editreport_Status option:selected").val();

    if ((report_type === '') || (report_type === undefined)) {
        $("#Edit_reportError").css("color", "red").html("Please Select Report Type");
        return false;
    }
    if ((report_name === '') || (report_name === undefined)) {
        $("#Edit_reportError").css("color", "red").html("Please Enter Report Name");
        return false;
    }
    if ((report_name !== '') || (report_name !== undefined)) {
        var re = /^[ A-Za-z0-9_@./#&+-]*$/
        if (!re.test(report_name)) {
           $("#Edit_reportError").css("color", "red").html("Please Enter Report Name with only alphanumericals");
            return false;
        }
    }
    if ((report_type !== '') || (report_type !== undefined)) {
        if (report_type === 'Event') {
            if (((dart_num === '') || (dart_num === undefined) || (dart_num === 'null')) && ((scrp_num === '') || (scrp_num === undefined) || (scrp_num === 'null'))) {
                $("#Edit_reportError").css("color", "red").html("Please select either Scrip number or Event filter from drop down");
                return false;
            }
        } else if (report_type === 'Asset') {
            if ((assSearch_num === '') || (assSearch_num === undefined) || (assSearch_num === 'null')) {
                $("#Edit_reportError").css("color", "red").html("Please select Asset Queries from drop down");
                return false;
            }
        }
    }

    if ((report_email === '') || (report_email === undefined)) {
        $("#Edit_reportError").css("color", "red").html("Please Enter Email Id");
        return false;
    } else if ((report_duration === '') || (report_duration === undefined)) {
        $("#Edit_reportError").css("color", "red").html("Please select Report Schedule");
        return false;
    } else if ((Editreport_Status === '') || (report_email === Editreport_Status)) {
        $("#Edit_reportError").css("color", "red").html("Please Select Status");
        return false;
    }

    if ((report_duration !== '') || (report_duration !== undefined)) {
        if ((report_duration === "1") || (report_duration === 1)) {
            var dayshour = $("#Editdays_hour option:selected").val();
            var days_min = $("#Editdays_min option:selected").val();
            if ((dayshour === '') || (dayshour === undefined) || (dayshour === 'null')) {
                $("#Edit_reportError").css("color", "red").html("Please Select Hour");
                return false;
            } else if ((days_min === '') || (days_min === undefined) || (days_min === 'null')) {
                $("#Edit_reportError").css("color", "red").html("Please Select Minutes");
                return false;
            }

        } else if ((report_duration === "7") || (report_duration === 7)) {

            var weekday = $("#Editweekday option:selected").val();
            var week_hour = $("#Editweek_hour option:selected").val();
            var week_min = $("#Editweek_min option:selected").val();
            if ((weekday === '') || (weekday === undefined) || (weekday === 'null')) {
                $("#Edit_reportError").css("color", "red").html("Please Select Weekday");
                return false;
            } else if ((week_hour === '') || (week_hour === undefined) || (week_hour === 'null')) {
                $("#Edit_reportError").css("color", "red").html("Please Select Week Hour");
                return false;
            } else if ((week_min === '') || (week_min === undefined) || (week_min === 'null')) {
                $("#Edit_reportError").css("color", "red").html("Please Select Week Minute");
                return false;
            }

        } else if ((report_duration === "30") || (report_duration === "30")) {

            var month_day = $("#Editmonth_day option:selected").val();
            var month_hour = $("#Editmonth_hour option:selected").val();
            var month_min = $("#Editmonth_min option:selected").val();
            if ((month_day === '') || (month_day === undefined) || (month_day === 'null')) {
                $("#Edit_reportError").css("color", "red").html("Please Select day");
                return false;
            } else if ((month_hour === '') || (month_hour === undefined) || (month_hour === 'null')) {
                $("#Edit_reportError").css("color", "red").html("Please Select Hour");
                return false;
            } else if ((month_min === '') || (month_min === undefined) || (month_min === 'null')) {
                $("#Edit_reportError").css("color", "red").html("Please Select Minute");
                return false;
            }

        }
    }

    var res = validateEmailId(report_email);

    if ((res === "false") || (res === false)) {
        return false;
    } else if ((res === "undefined") || (res === undefined)) {
        $("#Edit_reportError").html('');
        var queryData = {report_type: report_type, report_name: report_name, dart_num: dart_num, scrp_num: scrp_num, assSearch_num: assSearch_num, report_duration: report_duration, report_email: report_email, editReportID: editReportID, Editreport_Status: Editreport_Status,
        dayshour: dayshour, days_min: days_min, weekday: weekday, week_hour: week_hour, week_min: week_min, month_day: month_day, month_hour: month_hour, month_min: month_min};

        $.ajax({
            type: "GET",
            url: "../lib/l-ajax.php?function=AJAX_Edit_adhocQueries",
            data: queryData,
            dataType: 'text',
            success: function (msg) {
                var response = msg.trim();
                if (response === "success") {
                $("#EDIT_adhocquery_modal").modal('hide');
                    $(".cancel_query_btn").click();
                getAdocReportInfo();
                } else if (response === "failed") {
                    $("#editError_fail").css("display", "block");

                } else if (response === "exist") {
                    $("#editError_exist").css("display", "block");

            }
            }
        });
    }
}

function validateEmailId(report_email) {
    
    var result = report_email.split(',');
    for (var i = 0; i < result.length; i++) {
        if (result[i] !== '') {
            if (!validateEmail(result[i])) {
                $("#err_email").html('Please check, `' + result[i] + '` email addresses not valid!');
                return false;
            } else {
                $("#err_email").html('');

            }
        }
    }
}

function validateEmail(field) {
    var regex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,5}$/;
    return (regex.test(field)) ? true : false;
}