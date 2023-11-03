
$(document).ready(function () {
    getAllSections();
});

var tempSectionId = '';
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

function getAllSections() {
    $('#sectionsTable').DataTable().destroy();
    table1 = $('#sectionsTable').DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        searching: true,
        processing: true,
        serverSide: false,
        responsive: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        ajax: {
            url: "manageViewsFun.php?function=getAllSections"+"&csrfMagicToken=" + csrfMagicToken,
            type: "POST",
            rowId: 'id'
        },
        columns: [
            {"data": "name"},
            {"data": "type"}
        ],
        columnDefs: [
            {className: "table-plus", "targets": 0}
        ],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function (settings, json) {
            table1.$('tr:first').click();
        },
        drawCallback: function (settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
            $(".se-pre-con").hide();
        }
    });
    $('#sectionsTable tbody').on('click', 'tr', function () {
        table1.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var rowdata = table1.row(this).data();
        id = rowdata.DT_RowId;
        getReports(id);
    });
}

$("#salesinsight_searchbox").keyup(function () {
    table1.search(this.value).draw();
});

function getReports(id) {
    $('#reportSectionTable').DataTable().destroy();
    table2 = $('#reportSectionTable').DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        searching: true,
        processing: true,
        serverSide: false,
        responsive: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        ajax: {
            url: "manageViewsFun.php?function=getSectionReports&sectionId=" + id +"&csrfMagicToken=" + csrfMagicToken,
            type: "POST",
            rowId: 'id'
        },
        columns: [
            {"data": "name"},
            {"data": "created"},
            {"data": "username"}
        ],
        columnDefs: [
            {className: "table-plus", "targets": 0}
        ],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function (settings, json) {
            table2.$('tr:first').click();
        },
        drawCallback: function (settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
            $(".se-pre-con").hide();
        }
    });
    $('#reportSectionTable tbody').on('click', 'tr', function () {

    });
}

function refresh() {
    location.reload();
}
// section related code

//for showing and hiding blocks based on type of section
$('#section_id').on('change', function () {
    if ($('#section_id').val() == 1) {
        $('.SectionName').show();
        $('.subHeader_type').show();
        $('.subHeader_asset_type').hide();
        $('.subHeader_name').show();
        $('.subHeader_name').show();
        $('.subHeader_mum_name').hide();
        $('.add_text').show();
    } else if ($('#section_id').val() == 2) {
        $('.SectionName').show();
        $('.subHeader_asset_type').show();
        $('.subHeader_type').hide();
        $('.subHeader_name').hide();
        $('.subHeader_name').show();
        $('.subHeader_mum_name').hide();
         $('.add_text').hide();
    } else if ($('#section_id').val() == 3) {
        $(".section").mCustomScrollbar({theme: "minimal-dark"});
        $('.SectionName').show();
        $('.subHeader_asset_type').hide();
        $('.subHeader_mum_name').show();
        $('.subHeader_type').hide();
        $('.subHeader_name').hide();
        $('.multiple_subSection').hide();
         $('.add_text').hide();
    } else if ($('#section_id').val() == 4) {
        //$('.section').mCustomScrollbar('destroy');
        $('.subHeader_type').hide();
        $('.SectionName').hide();
        $('.subHeader_name').hide();
        $('.multiple_subSection').hide();
        $('.add_text').hide();
    } else {
        $('.subHeader_type').hide();
        $('.subHeader_name').hide();
        $('.subHeader_mum_name').hide();
        $('.multiple_subSection').hide();
        $('.SectionName').show();
         $('.add_text').hide();
    }
    if ($('#section_id').val() == 1) {
        $('.event_duration').show();
    } else {
        $('.event_duration').hide();
    }
    $(".section").mCustomScrollbar({theme: "minimal-dark"});
    $('.section').mCustomScrollbar('update');
});

//to check unique section name
function validateSectionName(obj) {

    var sectionName = $(obj).val();

    $.ajax({
        type: "POST",
        url: "../lib/l-mngdRprt.php?function=1&functionToCall=getSectionName&name=" + sectionName +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'

    }).done(function (data) {
        $("#error1").html('');
        $("#editerror1").html('');
        if (data.status != 'No') {
            $("#error1").html(data.status);
            $("#editerror1").html(data.status);
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
        subHdr.each(function () {
            count++;
        });

        var editSubhdr = $('#editSection').find('.addNewSubheader');
        editSubhdr.each(function () {
            editcount++;
        });
        if (count > 1) {
            var msg = 'Single query GroupBy option does not allow adding multiple sub headers';
            $("#error1").html('<span>' + msg + '</span>');
            setTimeout(function () {
                $("#error1").fadeOut(3600);
            }, 3600);
            $(obj).val(2);
        } else if (editcount > 1) {
            var msg = 'Single query GroupBy option does not allow adding multiple sub headers';
            $("#editerror1").html('<span>' + msg + '</span>');
            setTimeout(function () {
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

//subheader validation
function subheadersAssetsAllowed(header, obj) {
    $("#error1").html("");
    $("#error1").show();
    var count = 0;
    var editcount = 0;

    if ($(obj).val() == 1 || $(obj).val() == 0) {
        var subHdr = $('#createSection').find('.addNewSubheader');
        subHdr.each(function () {
            count++;
        });

        var editSubhdr = $('#editSection').find('.addNewSubheader');
        editSubhdr.each(function () {
            editcount++;
        });
        if (count > 1) {
            var msg = 'Single query GroupBy option does not allow adding multiple sub headers';
            $("#error1").html('<span>' + msg + '</span>');
            setTimeout(function () {
                $("#error1").fadeOut(3600);
            }, 3600);
            $(obj).val(2);
        } else if (editcount > 1) {
            var msg = 'Single query GroupBy option does not allow adding multiple sub headers';
            $("#editerror1").html('<span>' + msg + '</span>');
            setTimeout(function () {
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
            '<div class="col-lg-1 col-md-6 col-sm-6 col-xs-12">' +
            '<div class="add-sumsecdata">' +
            '<a href="javascript:" onclick="addSubheader(1,this)" id="addNewSubheader" class="addNewSubheader""><i class="icon-ic_add_24px material-icons"></i></a>' +
            '<input type="hidden" id="head_count" value="' + subHeaders[header] + '">' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-10 col-md-6 col-sm-6 col-xs-12">' +
            '<div class="form-group filter_type">' +
            '<select class="form-control dropdown-submenu queryFilterType" data-size="5" id="filterType_' + subHeaders[header] + '">' +
            '</select>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-1 col-md-12 col-sm-12 col-xs-12">' +
            '<div class="row clearfix">' +
            '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' +
            '<div class="remove-sumsecdata">' +
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
    $(".section").mCustomScrollbar({theme: "minimal-dark"});
    $('.section').mCustomScrollbar('update');
}

function removeSubHdr(obj) {
    $(obj).parent().parent().parent().parent().parent().remove();
}

function createVariousSection() {
    var sectionType = $("#section_id").val();

    var validation = addSection();

    if (validation != false && validation !== 'false') {
        if (sectionType == 1) {
            createEventSection();
        } else if (sectionType == 2) {
            createAssetSection();
        } else if (sectionType == 3) {
            createMUMSection();
        }
    }
}

function createEventSection() {
    var sectionName = $("#SectionName").val();
    var groupBy = $("#subHeaderGroupBy").val();
    var startDate = "";
    var endDate = "";
    var latest = false;
    var duration = $("#subHeaderDuration").val();
    var text = $('#textselection').val();

    if (duration === "range") {
        startDate = $("#datefrom").val();
        endDate = $("#dateto").val();
    }

    if(text === '') {
        text = 'text1';
    }
    var sectionData = [];

    $(".queryFilterType").each(function (index) {
        if ($(this).val() === "" || $(this).val() === undefined) {

        } else {
            var item = {};
            item["filterId"] = $(this).val();
            sectionData.push(item);
        }

    });

    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=createEventSection&sectionName=" + sectionName + "&sectionData=" + JSON.stringify(sectionData) + "&groupBy=" + groupBy + "&duration=" + duration + "&startDate=" + startDate + "&endDate=" + endDate+"&txtsel="+text +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'

    }).done(function (data) {
        data = $.trim(data.status);
        if (data === 'SUCCESS') {
            $("#error").html("Section created successfully");
            setTimeout(function () {
                location.reload();
            }, 3000);
        } else {
            $("#error1").html("Section name already exist");
        }

    });
}

function createAssetSection() {
    var sectionName = $("#SectionName").val();
    var sectionData = [];

    var groupBy = $("#subHeaderAssetGroupBy").val();
    var cateBy = $("#subHeaderAssetCatBy").val();

    $(".queryFilterType").each(function (index) {
        if ($(this).val() === "" || $(this).val() === undefined) {

        } else {
            var item = {};
            item["filterId"] = $(this).val();
            sectionData.push(item);
        }
    });

    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=createAssetSection&sectionName=" + sectionName + "&sectionData=" + JSON.stringify(sectionData) + "&groupBy=" + groupBy + "&cateBy=" + cateBy +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'

    }).done(function (data) {
        data = $.trim(data.status);
        if (data === 'SUCCESS') {
            $("#error").html("Section created successfully");
            setTimeout(function () {
                location.reload();
            }, 3000);
        } else {
            $("#error1").html("Section name already exist");
        }
    });
}

$("#mumDuration").change(function () {
    if ($(this).val() == "range") {
        $("#mumStartDate").parent().show();
        $("#mumEndDate").parent().show();
        setPatchesOptions($("#mumStartDate").val(), $("#mumEndDate").val());
    } else {
        var days = $(this).val(); // Days you want to subtract
        var date = new Date();
        var slast = new Date(date.getTime() - (days * 24 * 60 * 60 * 1000));
        var sday = slast.getDate();
        var smonth = slast.getMonth() + 1;
        var syear = slast.getFullYear();

        var eday = date.getDate();
        var emonth = date.getMonth() + 1;
        var eyear = date.getFullYear();
        $("#mumStartDate").parent().hide();
        $("#mumEndDate").parent().hide();
        setPatchesOptions(smonth + '/' + sday + '/' + syear, emonth + '/' + eday + '/' + eyear);
    }
});

$("#include_patch").change(function () {
    if ($(this).val() == "all") {
        $("#include_patch option:selected").removeAttr("selected");
        $('#include_patch').val("all");
    }
});

$("#edit_include_patch").change(function () {
    if ($(this).val() == "all") {
        $("#edit_include_patch option:selected").removeAttr("selected");
        $('#edit_include_patch').val("all");
    }
});

$("#edit_mumDuration").change(function () {
    if ($(this).val() == "range") {
        $("#edit_mumStartDate").parent().show();
        $("#edit_mumEndDate").parent().show();
        setEditPatchesOptions($("#edit_mumStartDate").val(), $("#edit_mumEndDate").val(), "");
    } else {
        var days = $(this).val(); // Days you want to subtract
        var date = new Date();
        var slast = new Date(date.getTime() - (days * 24 * 60 * 60 * 1000));
        var sday = slast.getDate();
        var smonth = slast.getMonth() + 1;
        var syear = slast.getFullYear();

        var eday = date.getDate();
        var emonth = date.getMonth() + 1;
        var eyear = date.getFullYear();
        setEditPatchesOptions(smonth + '/' + sday + '/' + syear, emonth + '/' + eday + '/' + eyear, "");

        $("#edit_mumStartDate").parent().hide();
        $("#edit_mumEndDate").parent().hide();
    }
});

$('input[name=mumStartDate]').change(function () {
    var startDate = $("#mumStartDate").val();
    var endDate = $("#mumEndDate").val();
    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=getPatchesForDateRange&startDate=" + startDate + "&endDate=" + endDate +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'

    }).done(function (data) {
        $("#include_patch").html("");
        var patchOptions = '<option value="all">All</option>';
        if (data.length > 0) {
            for (var i = 0; i < data.length; i++) {
                patchOptions += '<option value="' + data[i].id + '" title="' + data[i].name + '">' + data[i].name + '</option>';
            }
        }

        $("#include_patch").html(patchOptions);
    });
});

$('input[name=mumEndDate]').change(function () {
    var startDate = $("#mumStartDate").val();
    var endDate = $("#mumEndDate").val();
    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=getPatchesForDateRange&startDate=" + startDate + "&endDate=" + endDate +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'

    }).done(function (data) {
        $("#include_patch").html("");
        var patchOptions = '<option value="all">All</option>';
        if (data.length > 0) {
            for (var i = 0; i < data.length; i++) {
                patchOptions += '<option value="' + data[i].id + '" >' + data[i].name + '</option>';
            }
        } else {
            patchOptions += '<option value="" >No Patches Available</option>';
        }

        $("#include_patch").html(patchOptions);

    });
});

$('input[name=edit_mumStartDate]').change(function () {
    var startDate = $("#edit_mumStartDate").val();
    var endDate = $("#edit_mumEndDate").val();
    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=getPatchesForDateRange&startDate=" + startDate + "&endDate=" + endDate +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'

    }).done(function (data) {
        $("#edit_include_patch").html("");
        var patchOptions = '';
        if (data.length > 0) {
            for (var i = 0; i < data.length; i++) {
                patchOptions += '<option value="' + data[i].id + '" >' + data[i].name + '</option>';
            }
        } else {
            patchOptions += '<option value="" >No Patches Available</option>';
        }

        $("#edit_include_patch").html(patchOptions);
    });
});

$('input[name=edit_mumEndDate]').change(function () {
    var startDate = $("#edit_mumStartDate").val();
    var endDate = $("#edit_mumEndDate").val();
    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=getPatchesForDateRange&startDate=" + startDate + "&endDate=" + endDate +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'

    }).done(function (data) {
        $("#edit_include_patch").html("");
        var patchOptions = '';
        if (data.length > 0) {
            for (var i = 0; i < data.length; i++) {
                patchOptions += '<option value="' + data[i].id + '" >' + data[i].name + '</option>';
            }
        } else {
            patchOptions += '<option value="" >No Patches Available</option>';
        }

        $("#edit_include_patch").html(patchOptions);

    });
});

function setPatchesOptions(startDate, endDate) {

    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=getPatchesForDateRange&startDate=" + startDate + "&endDate=" + endDate +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'

    }).done(function (data) {
        $("#include_patch").html("");
        var patchOptions = '<option value="all">All</option>';
        if (data.length > 0) {
            for (var i = 0; i < data.length; i++) {
                patchOptions += '<option value="' + data[i].id + '" >' + data[i].name + '</option>';

            }
        }

        $("#include_patch").html(patchOptions);
    });
}

function setEditPatchesOptions(startDate, endDate, selected) {
    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=getPatchesForDateRange&startDate=" + startDate + "&endDate=" + endDate +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'

    }).done(function (data) {
        $("#edit_include_patch").html("");
        var patchOptions = '<option value="all">All</option>';
        if (data.length > 0) {
            for (var i = 0; i < data.length; i++) {
                if (selected.includes(data[i].id)) {
                    patchOptions += '<option value="' + data[i].id + '" selected>' + data[i].name + '</option>';
                } else {
                    patchOptions += '<option value="' + data[i].id + '" >' + data[i].name + '</option>';
                }
            }
        }

        $("#edit_include_patch").html(patchOptions);
        if (selected == "*") {
            $("#edit_include_patch option:selected").removeAttr("selected");
            $('#edit_include_patch').val("all");
        }
    });
}

function createMUMSection() {
    var sectionName = $("#SectionName").val();
    var groupBy = $("#mumGroupBy").val();
    var cateBy = $("#mumCatBy").val();
    var patch = $("#include_patch").val();
    var duration = $("#mumDuration").val();
    var startDate = "";
    var endDate = "";
    if (duration == "range") {
        startDate = $("#mumStartDate").val();
        endDate = $("#mumEndDate").val();
    }
    if (patch == "all") {
        patch = "*";
    }


    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=createMUMSection&sectionName=" + sectionName + "&groupBy=" + groupBy + "&cateBy=" + cateBy + "&patch=" + patch + "&startDate=" + startDate + "&endDate=" + endDate + "&duration=" + duration +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'

    }).done(function (data) {
        data = $.trim(data.status);
        if (data === 'SUCCESS') {
            $("#error").html("Section created successfully");
            setTimeout(function () {
                location.reload();
            }, 3000);
        } else {
            $("#error1").html("Section name already exist");
        }

    });
}

function updateVariousSection() {
    var sectionType = $("#edit_sectionid").val();
    $('#editerror1').html('');
    $('#editerror1').show();
    
    if($.trim($('#edit_SectionName').val()) == '') {
        $("#editerror1").html("Please provide valid section name");
        setTimeout(function() {
            $("#editerror1").fadeOut(3600);
        }, 3600);
        return false;
    }
    if (sectionType == 1) {
        updateEventSection();
    } else if (sectionType == 2) {
        updateAssetSection();
    } else if (sectionType == 3) {
        updateMUMSection();
    }
}


function updateEventSection() {
    var sectionId = $("#edit_HiddenSectionId").val();
    var sectionName = $("#edit_SectionName").val();
    var groupBy = $("#edit_subHeaderGroupBy").val();
    var startDate = "";
    var endDate = "";
    var duration = $("#edit_subHeaderDuration").val();
    var sectionText = $('#edit_textselection').val();
    $('#editerror1').html('');
    $('#editerror1').show();
//console.log($('#edit_textselection').val());
    if (duration === "range") {
        startDate = $("#edit_datefrom").val();
        endDate = $("#edit_dateto").val();
    }

    if($('#edit_textselection').val() == 0) {
        $('#editerror1').html('<span>Please select text</span>');
        setTimeout(function () {
            $("#editerror1").fadeOut(2000);
        }, 1000);
        return false;
    }
    var sectionData = [];

    $(".edit_queryFilterType").each(function (index) {
        if ($(this).val() === "" || $(this).val() === undefined) {

        } else {
            var item = {};
            item["filterId"] = $(this).val();
            sectionData.push(item);
        }

    });

    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=updateEventSection&sectionId=" + sectionId + "&sectionName=" + sectionName + "&sectionData=" + JSON.stringify(sectionData) + "&groupBy=" + groupBy + "&duration=" + duration + "&startDate=" + startDate + "&endDate=" + endDate+"&text="+sectionText +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'

    }).done(function (data) {
        data = $.trim(data.status);
        if (data === 'SUCCESS') {
            $("#editerror").html("Section updated successfully");
            setTimeout(function () {
                location.reload();
            }, 3000);
        } else {
            $("#editerror1").html("Section name already exist");
        }
    });
}

function updateAssetSection() {
    var sectionId = $("#edit_HiddenSectionId").val();
    var sectionName = $("#edit_SectionName").val();
    var groupBy = $("#edit_subHeaderAssetGroupBy").val();
    var cateBy = $("#edit_subHeaderAssetCatBy").val();

    var sectionData = [];

    $(".edit_queryFilterType").each(function (index) {
        if ($(this).val() === "" || $(this).val() === undefined) {

        } else {
            var item = {};
            item["filterId"] = $(this).val();
            sectionData.push(item);
        }

    });

    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=updateAssetSection&sectionId=" + sectionId + "&sectionName=" + sectionName + "&sectionData=" + JSON.stringify(sectionData) + "&groupBy=" + groupBy + "&cateBy=" + cateBy +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'

    }).done(function (data) {
        data = $.trim(data.status);
        if (data === 'SUCCESS') {
            $("#editerror").html("Section updated successfully");
            setTimeout(function () {
                location.reload();
            }, 3000);
        } else {
            $("#editerror1").html("Section name already exist");
        }

    });
}

function updateMUMSection() {
    var sectionId = $("#edit_HiddenSectionId").val();
    var sectionName = $("#edit_SectionName").val();
    var groupBy = $("#edit_mumGroupBy").val();
    var cateBy = $("#edit_mumCatBy").val();
    var patch = $("#edit_include_patch").val();
    var duration = $("#edit_mumDuration").val();
    var startDate = "";
    var endDate = "";
    if (patch == "all") {
        patch = "*";
    }
    if (duration == "range") {
        startDate = $("#edit_mumStartDate").val();
        endDate = $("#edit_mumEndDate").val();
    }


    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=updateMUMSection&sectionId=" + sectionId + "&sectionName=" + sectionName + "&groupBy=" + groupBy + "&cateBy=" + cateBy + "&patch=" + patch + "&startDate=" + startDate + "&endDate=" + endDate + "&duration=" + duration +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'

    }).done(function (data) {
        data = $.trim(data.status);
        if (data === 'SUCCESS') {
            $("#editerror").html("Section updated successfully");
            setTimeout(function () {
                location.reload();
            }, 3000);
        } else {
            $("#editerror1").html("Section name already exist");
        }
    });
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
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getEventFilters"+"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json'
        }).done(function (data) {
            eventData = data;
            eventOptions = '<option value="0">Choose filter</option>';
            for (var i = 0; i < eventData.length; i++) {
                eventOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
            }
            $('#filterType_' + header).html("");
            $('#filterType_' + header).html(eventOptions);
            $('.selectpicker').selectpicker('refresh');
        });

    }
    else if ($(obj).val() == 2) {
        $('#summary_header').hide();
        $('#subHeaderType').val(0);

        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getAssetQueries"+"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json'
        }).done(function (data) {
            assetData = data;

            assetOptions = '<option value="0">Choose Query</option>';
            for (var i = 0; i < data.length; i++) {
                assetOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
            }
            $('#filterType_' + header).html("");
            $('#filterType_' + header).html(assetOptions);
            $('.selectpicker').selectpicker('refresh');
        });
    }
    else if ($(obj).val() == 3) {
        $('SectionName').show();
    } else {

        $('#sumSection').hide();
        //event options
        /*$.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getEventFilters",
            dataType: 'json'
        }).done(function (data) {
            eventOptions = '<option value="0">Choose filter</option>';
            for (var i = 0; i < data.length; i++) {
                eventOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
            }
        });

        //asset option
        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getAssetQueries",
            dataType: 'json'
        }).done(function (data) {
            assetOptions = '<option value="0">Choose filter</option>';
            for (var i = 0; i < data.length; i++) {
                assetOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
            }
        });*/
    }
    $(".section").mCustomScrollbar({
        theme: "minimal-dark"
    });
    //$('.section').mCustomScrollbar('update');
}

$(".latestEventRange").change(function () {
    if ($(this).is(":checked")) {
        $(".form_datetime").parent().hide();
    } else {
        $(".form_datetime").parent().show();
    }
//    $('.section').mCustomScrollbar('update');
});

$("#subHeaderAssetType").change(function () {
    populateGroupingOptions(1, "");
//    if($(this).val() === "Sites"){
//        $("#subHeaderAssetGroupBy").parent().hide();
//    }else{
//       $("#subHeaderAssetGroupBy").parent().show(); 
//    }
})

$("#edit_subHeaderAssetType").change(function () {
    edit_populateGroupingOptions(1, '', '');
});

function changeDurationCrieteria() {
    var duration = $("#subHeaderDuration").val();
    if (duration === "range") {
        $(".form_datetime").parent().show();
    } else {
        $(".form_datetime").parent().hide();
    }

    $('.section').mCustomScrollbar('update');
}

function changeEditDurationCrieteria() {
    var duration = $("#edit_subHeaderDuration").val();
    if (duration === "range") {
//        $("#edit_datefrom").parent().show();
//        $("#edit_dateto").parent().show();
        $('.subHeader_type_range').show();
    } else {
//        $("#edit_datefrom").parent().hide();
//        $("#edit_dateto").parent().hide();
        $('.subHeader_type_range').hide();
    }

//    $('.section').mCustomScrollbar('update');
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
        var queryType = $("#subHeaderAssetType").val();
        if (filter1 === "" || filter1 === "0") {

        } else {
            $("#subHeaderAssetGroupBy").parent().hide();
            var grpupByOptions = "";
            var cateByOptions = "";
            $.ajax({
                type: "POST",
                url: "manageViewsFun.php?function=getAssetFiltersFields&queryId=" + filter1 +"&csrfMagicToken=" + csrfMagicToken,
                dataType: 'json'
            }).done(function (data) {
                grpupByOptions = "<option>Select group by</option>";
                cateByOptions = "<option>Select categorize by</option>";
                grpupByOptions += "<option value='Site Name' selected>Site Name</option>";
                cateByOptions += "<option value='Machine Name' selected>Machine Name</option>";

                $.each(data, function (key, value) {
                    if (value !== "Site Name") {
                        grpupByOptions += "<option value='" + value + "'>" + value + "</option>";
                    }
                });

                $.each(data, function (key, value) {
                    if (value !== "Machine Name") {
                        cateByOptions += "<option value='" + value + "'>" + value + "</option>";
                    }
                });

                $("#subHeaderAssetGroupBy").parent().show();
                $("#subHeaderAssetCatBy").parent().show();
                $("#subHeaderAssetGroupBy").html($.trim(grpupByOptions));
                $("#subHeaderAssetCatBy").html($.trim(cateByOptions));
            });
        }
    }
    $('#subheader_group').html('');
    $('#subheader_group').html(options);
    $('.selectpicker').selectpicker('refresh');

}

//to fetch grouping option for asset and event section
function edit_populateGroupingOptions(header, obj, groupName) {
    var filterid = $('#edit_sectionid').val();
    var options = '';
    var filter1 = $('#edit_filterType_' + header).val();
    if (filterid == 1) {
        options += '<option value="Machine">Machine</option>' +
                '<option value="Site">Site</option>' +
                '<option value="User Name">User Name</option>' +
                '<option value="Scrip">Scrip</option>' +
                '<option value="Executable">Executable</option>' +
                '<option value="Windows Title">Windows Title</option>';
    } else if (filterid == 2) {
        $("#edit_subHeaderAssetGroupBy").parent().show();
        $("#edit_subHeaderAssetCatBy").parent().show();
        $.ajax({
            type: "POST",
            url: "manageViewsFun.php?function=getAssetFiltersFields&queryId=" + filter1 +"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json'
        }).done(function (data) {
            var grpupByOptions = "<option>Select group by</option><option value='Site Name' selected>Site Name</option>";
            var cateByOptions = "<option>Select categorize by</option><option value='Machine Name' selected>Machine Name</option>";

            $.each(data, function (key, value) {
                if (value !== "Site Name") {
                    grpupByOptions += "<option value='" + value + "'>" + value + "</option>";
                }
            });

            $.each(data, function (key, value) {
                if (value !== "Machine Name") {
                    cateByOptions += "<option value='" + value + "'>" + value + "</option>";
                }
            });

            $("#edit_subHeaderAssetGroupBy").html($.trim(grpupByOptions));
            $("#edit_subHeaderAssetCatBy").html($.trim(cateByOptions));

            if (groupName !== '') {
                var groupValStr = groupName.split("###");
                $("#edit_subHeaderAssetGroupBy option[value='" + groupValStr[0] + "']").prop('selected', true);
                $("#edit_subHeaderAssetCatBy option[value='" + groupValStr[1] + "']").prop('selected', true);
            }

        });
        return true;
    }
    $('.selectpicker').selectpicker('refresh');

}

//to fetch the patch option
function populatePatch() {

    month = $('#month').val();
    year = $('#year').val();

    $.ajax({
        type: 'post',
        url: '../lib/l-mngdRprt.php?function=1&functionToCall=getPatchDetails&mnth=' + month + '&year=' + year +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'
    }).done(function (data) {

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
        setTimeout(function () {
            $("#error1").fadeOut(2000);
        }, 1000);
        return false;
    }
    if ($('#section_id').val() != 4) {
        if ($.trim($('#SectionName').val()) == '') {
            $('#error1').html('<span>Please enter section name</span>');
            setTimeout(function () {
                $("#error1").fadeOut(2000);
            }, 1000);
            return false;
        }
        if ($('#SectionName').val().length > 25) {
            $('#error1').html('<span>Section Name cannot be more than 25 characters</span>');
            setTimeout(function () {
                $("#error1").fadeOut(2000);
            }, 1000);
            return false;
        }
    }
    if ($('#section_id').val() == '4') {
        if ($.trim($('#SummaryName').val()) == '') {
            $('#error1').html('<span>Please enter summary name</span>');
            setTimeout(function () {
                $("#error1").fadeOut(2000);
            }, 1000);
            return false;
        }
        if ($('#SectionName').val().length > 25) {
            $('#error1').html('<span>Summary Name cannot be more than 25 characters</span>');
            setTimeout(function () {
                $("#error1").fadeOut(2000);
            }, 1000);
            return false;
        }
    }

    if ($('#section_id').val() == 1) {
        var subHeaderType = $('#subHeaderType').val();
        if (subHeaderType == 0 && $('#section_id').val() == 1) {
            $('#error1').html('<span>Please select Query Type</span>');
            setTimeout(function () {
                $("#error1").fadeOut(2000);
            }, 1000);
            return false;

        }
        if (subHeaderType == 1) {
            if ($('#subHeaderGroupBy').val() == 0) {
                $('#error1').html('<span>Please select group by</span>');
                setTimeout(function () {
                    $("#error1").fadeOut(2000);
                }, 1000);
                return false;
            }
        }
        if ($('#subHeaderDuration').val() == 0 && $('#subHeaderGroupBy').val() === 'date') {
            $('#error1').html('<span>Please select event duration</span>');
            setTimeout(function () {
                $("#error1").fadeOut(2000);
            }, 1000);
            return false;
        } else if ($('#subHeaderDuration').val() === 'range' && $('#subHeaderGroupBy').val() === 'date') {
            var dateFrom = $('#datefrom').val();
            var dateTo = $('#dateto').val();
            if (dateFrom == '') {
                $('#error1').html('<span>Please select date</span>');
                setTimeout(function () {
                    $("#error1").fadeOut(2000);
                }, 1000);
                return false;
            }
            if (dateTo == '') {
                $('#error1').html('<span>Please select date</span>');
                setTimeout(function () {
                    $("#error1").fadeOut(2000);
                }, 1000);
                return false;
            }
            var start = (new Date(dateFrom).getTime());
            var to = (new Date(dateTo).getTime());
            if (start > to) {
                $('#error1').html('<span>Start Date should be less than end date</span>');
                setTimeout(function () {
                    $("#error1").fadeOut(2000);
                }, 1000);
                return false;
            }
        }
        if ($('.queryFilterType').val() == 0) {
            $('#error1').html('<span>Please select filter</span>');
            setTimeout(function () {
                $("#error1").fadeOut(2000);
            }, 1000);
            return false;
        }
        if($('#textselection').val() == 0) {
            $('#error1').html('<span>Please select text</span>');
            setTimeout(function () {
                $("#error1").fadeOut(2000);
            }, 1000);
            return false;
        }
    } else if ($('#section_id').val() == 2) {
        var query = $('.queryFilterType').val();
        var group = $('#subHeaderAssetGroupBy').val();
        var category = $('#subHeaderAssetCatBy').val();
        if ($('.queryFilterType').val() == 0) {
            $('#error1').html('<span>Please choose query</span>');
            setTimeout(function () {
                $("#error1").fadeOut(2000);
            }, 1000);
            return false;

        }
        if (group == 0) {
            $('#error1').html('<span>Please select group by</span>');
            setTimeout(function () {
                $("#error1").fadeOut(2000);
            }, 1000);
            return false;

        }
        if (category == 0) {
            $('#error1').html('<span>Please select category</span>');
            setTimeout(function () {
                $("#error1").fadeOut(2000);
            }, 1000);
            return false;

        }
    } else if ($('#section_id').val() == 3) {
        var duration = $('#mumDuration').val();
        var mumgroupby = $('#mumGroupBy').val();
        var category = $('#mumCatBy').val();
        if (duration == 0) {
            $('#error1').html('<span>Please choose duration</span>');
            setTimeout(function () {
                $("#error1").fadeOut(2000);
            }, 1000);
            return false;

        }
        if (mumgroupby == 0) {
            $('#error1').html('<span>Please select group by</span>');
            setTimeout(function () {
                $("#error1").fadeOut(2000);
            }, 1000);
            return false;

        }
        if (category == 0) {
            $('#error1').html('<span>Please select category</span>');
            setTimeout(function () {
                $("#error1").fadeOut(2000);
            }, 1000);
            return false;

        }
    }
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
//    formattedData.sectionName = $('#SectionName').val();
//    formattedData.sectionType = sectionType;
//    if(sectionType == 4) {
//        formattedData.chartType = $('#summary_chart_type').val();
//    } else {
//    formattedData.chartType = $('#chart_type').val();
//    }

    if (sectionType == 1 || sectionType == 2) {

//        formattedData.filterType = $('#section_id').val();
        subHeaderType = $('#subHeaderType').val();
//        formattedData.subHeaderType = subHeaderType;

        if (subHeaderType == 0 && sectionType == 1) {
            msg = 'Please select Query';
            return msg;
        }
        if (subHeaderType == 1) {
            if ($('#subheader_group').val() == '') {
                msg = 'Please select group name';
                return msg;
            }
//            formattedData.groupVal = $('#subheader_group').val();
        } else {
//            formattedData.groupVal = 0;
//            header = $('#head_count').val();
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
        $('#sum_header').find(':input').each(function () {
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

        $('.update_type option').each(function () {
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

        $(".include_patch option").each(function () {
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

        if (formattedData.chartType == 6) {
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
$('#createSection').on('hidden.bs.modal', function () {

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
    $('.subHeader_mum_name').hide();

    $('.section').mCustomScrollbar('destroy');
    $('.selectpicker').selectpicker('refresh');
//    $('.form-group').find('input,textarea,select').val('');
    $("#mumStartDate").parent().hide();
    $("#mumEndDate").parent().hide();
});

$('#editSection').on('hidden.bs.modal', function () {
    $('.subHeader_edit_mum_name').hide();
    $("#edit_mumStartDate").parent().hide();
    $("#edit_mumEndDate").parent().hide();
});

//View section
function viewSection() {

    $('#sectiondetails').modal('show');
    showSectionDetails();

}

function showSectionDetails() {
    $.ajax({
        type: "post",
        url: '../lib/l-mngdRprt.php?function=1&functionToCall=getSectionDetails'+"&csrfMagicToken=" + csrfMagicToken,
        data: "",
        dataType: 'json',
        success: function (gridData) {
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
                        className: "table-plus",
                        targets: 0
                    },
                ],
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                }
            });
        },
        error: function (msg) {

        }
    });
    $('#sectionDetailsList').on('click', 'tr', function () { //row selection code
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
    $("#edit_HiddenSectionId").val("");
    $("#editerror1").html('');
    sectionid = $('#sectionsTable tbody tr.selected').attr('id');//$('#sectionId').val();
    $('#error2').html('');
    $('#error2').show();

    if (sectionid == '') {
        $('#error2').html('<span>Please select one section to edit</span>');
        setTimeout(function () {
            $("#error2").fadeOut(3600);
        }, 3600);
        return false;
    }

    $.ajax({
        url: 'manageViewsFun.php?function=editSectionDetails&id=' + sectionid +"&csrfMagicToken=" + csrfMagicToken,
        type: 'post',
        dataType: 'json'
    }).done(function (data) {
        $("#edit_HiddenSectionId").val(sectionid);
        $('#edit_sectionid option[value=' + data.sectionType + ']').attr("selected", true);
        $('.selectpicker').selectpicker('refresh');
        $('#edit_sectionid').prop('disabled', true);
        $('.editsection').mCustomScrollbar('destroy');
        if (data.sectionType == 1) {
            editEventPopup(data);

        } else if (data.sectionType == 2) {
            editAssetPopup(data);

        } else if (data.sectionType == 3) {
            editMumPopup(data);

        } else if (data.sectionType == 4) {
            editSummaryPopup(data);

        }
    });

    $('#sectiondetails').modal('hide');
    $('#editSection').modal('show');

}

function editSection() {

    var editform = formatEditInput();

    if (typeof editform === 'string') {
        $('#editerror1').html(editform);
        setTimeout(function () {
            $("#editerror1").fadeOut(3600);
        }, 3600);
        return false;
    }

    var editData = JSON.stringify(editform);

    $.ajax({
        type: 'POST',
        url: '../lib/l-mngdRprt.php?function=1&functionToCall=editSection',
        dataType: 'json',
        data: editData+"&csrfMagicToken=" + csrfMagicToken
    }).done(function (data) {
        $("#editerror").html('');
        $("#editerror").html(data.msg);
        setTimeout(function () {
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
    if (sectionType == 4) {
        formattedData.editChartType = $('#edit_summary_chart_type').val();
        if ($('#edit_summary_chart_type').val() == 6) {
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
        $('#edit_sum_header').find(':input').each(function () {
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

        $('#edit_include_update option').each(function () {
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

        $("#edit_include_patch option").each(function () {
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

    $(".subHeader_edit_mum_name").hide();
    $(".subHeader_asset_type").hide();
    $('#edit_SectionName').val(data.sectionName);
    $("#edit_sectionType").val(1);

    $("#edit_subHeaderGroupBy").parent().show();

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
    $('.edit_text').show();

    var index = 1;
    var currentPosition = 0; // to skip first index of return array because first index should load default div
    for (var value in data.subData) {

        $('.edit_subHeader').show();
        if (data.subHeaders == 1) {
            $("#edit_subHeaderType").val(1);
            $('.addNewSubheader').hide();
            $('#edit_subHead_group').show();
            populateEditFilter(1, data.sectionType, data.subData[value].filterId, data.subData[value].groupName, 1);
            $('#edit_subHead_group').show();
            $('.editEventDuration').hide();
            $("#edit_subHeaderGroupBy").val(data.subData[value].groupName);

            if (data.sectionType == 1) {
                if (data.subData[value].groupName === 'date') {
                    $('.edit_subHeaderDuration').show();
                    $('#edit_subHeaderDuration').show();
                    $('#edit_event_duration_' + index + ' option[value=' + data.subData[value].eventDuration + ']').attr("selected", true);
                    $('.selectpicker').selectpicker('refresh');

                    if (data.subData[value].reportduration === "7") {
                        $("#edit_datefrom").parent().hide();
                        $("#edit_dateto").parent().hide();
                        $("#edit_subHeaderDuration").val("7");
                    } else if (data.subData[value].month === "0" && data.subData[value].reportduration === "0") {
                        $("#edit_datefrom").parent().hide();
                        $("#edit_dateto").parent().hide();
                        $("#edit_subHeaderDuration").val("latest");
                    } else if (data.subData[value].month !== "0" && data.subData[value].reportduration !== "7") {
                        $("#edit_subHeaderDuration").val("range");
                        $("#edit_datefrom").val(data.subData[value].startDate);
                        $("#edit_dateto").val(data.subData[value].endDate);
                        $("#edit_datefrom").parent().show();
                        $("#edit_dateto").parent().show();
                    }
                    $('.selectpicker').selectpicker('refresh');
                } else {
                    $('.edit_subHeaderDuration').hide();
                    $('#edit_subHeaderDuration').hide();
                }
            } else {
                $('.editEventDuration').hide();
            }

        } else {
            $("#edit_subHeaderType").val(2);
            $('#edit_subHead_group').hide();
            $('.addNewSubheader').show();
            if (currentPosition == 0) {
                $("#edit_subHeaderGroupBy").val(data.subData[value].groupName);
                //$('#edit_subHeaderName_' + index).val(data.subData[value].subHeaderName);
                populateEditFilter(index, data.sectionType, data.subData[value].filterId, data.subData[value].groupName, 2);
                $('.editEventDuration').hide();

                if (data.sectionType == 1) {
                    if (data.subData[value].groupName === 'date') {
                        $('.edit_subHeaderDuration').show();
                        $('#edit_subHeaderDuration').show();
                        $('#edit_event_duration_' + index + ' option[value=' + data.subData[value].eventDuration + ']').attr("selected", true);
                        $('.selectpicker').selectpicker('refresh');

                        if (data.subData[value].reportduration === "7") {
                            $("#edit_datefrom").parent().hide();
                            $("#edit_dateto").parent().hide();
                            $("#edit_subHeaderDuration").val("7");
                        } else if (data.subData[value].month === "0" && data.subData[value].reportduration === "0") {
                            $("#edit_datefrom").parent().hide();
                            $("#edit_dateto").parent().hide();
                            $("#edit_subHeaderDuration").val("latest");
                        } else if (data.subData[value].month !== "0" && data.subData[value].reportduration !== "7") {
                            $("#edit_subHeaderDuration").val("range");
                            $("#edit_datefrom").val(data.subData[value].startDate);
                            $("#edit_dateto").val(data.subData[value].endDate);
                            $("#edit_datefrom").parent().show();
                            $("#edit_dateto").parent().show();
                        }
                        $('.selectpicker').selectpicker('refresh');
                    } else {
                        $('#edit_subHeaderDuration').hide();
                    }
                } else {
                    $('.editEventDuration').hide();
                }
            } else {
                index++;
                var multiSubhead = showMultipleSubheader(index, data.subData[value], 1);
                //$('#edit_subHeaderName_' + index).val(data.subData[value].subHeaderName);
                populateEditFilter(index, data.sectionType, data.subData[value].filterId, data.subData[value].groupName, 2);
            }
            currentPosition++;
        }
        $('#edit_textselection').val(data.subData[value].text);
        $('.selectpicker').selectpicker('refresh');
    }
    $(".editsection").mCustomScrollbar({theme: "minimal-dark"});
    $('.editsection').mCustomScrollbar('update');
}

function editAssetPopup(data) {
    $(".subHeader_edit_mum_name").hide();
    $(".subHeader_asset_type").show();
    $('#edit_SectionName').val(data.sectionName);
    $('.edit_SectionName').addClass('is-focused');
    $("#edit_sectionType").val(2);

    $("#edit_subHeaderGroupBy").parent().hide();

    $('.selectpicker').selectpicker('refresh');
    $('.edit_subHeaderType').parent().hide();
    $('.selectpicker').selectpicker('refresh');

    for (var value in data.subData) {

        $('.edit_subHeader').hide();
        $("#edit_filterType_1").parent().parent().parent().parent().show();
        if (data.subHeaders == 1) {
            populateEditFilter(1, data.sectionType, data.subData[value].filterId, data.subData[value].groupName, 1);
            $("#edit_NewSubheader").hide();
        } else {

        }
        $("#edit_subHeaderAssetGroupBy").parent().show();
        $("#edit_subHeaderAssetCatBy").parent().show();
    }
    $('.selectpicker').selectpicker('refresh');
    $(".editsection").mCustomScrollbar({theme: "minimal-dark"});
    $('.editsection').mCustomScrollbar('update');
}

function editMumPopup(data) {
    $('.edit_subHeader').hide();
    $("#edit_subHeaderGroupBy").parent().hide();
    $('.subHeader_type_range').hide();
    $(".subHeader_edit_mum_name").show();
    $('#edit_SectionName').val(data.sectionName);

    for (var value in data.subData) {
        if (data.subData[value].month == "7" || data.subData[value].month == "30" || data.subData[value].month == "60") {
            $("#edit_mumDuration").val(data.subData[value].month);
            $("#edit_mumStartDate").parent().hide();
            $("#edit_mumEndDate").parent().hide();
            var days = data.subData[value].month;
            var date = new Date();
            var slast = new Date(date.getTime() - (days * 24 * 60 * 60 * 1000));
            var sday = slast.getDate();
            var smonth = slast.getMonth() + 1;
            var syear = slast.getFullYear();

            var eday = date.getDate();
            var emonth = date.getMonth() + 1;
            var eyear = date.getFullYear();
            setEditPatchesOptions(smonth + '/' + sday + '/' + syear, emonth + '/' + eday + '/' + eyear, data.subData[value].filterId);
        } else {
            $("#edit_mumDuration").val("range");
            $("#edit_mumStartDate").val(data.subData[value].startDate).parent().show();
            $("#edit_mumEndDate").val(data.subData[value].endDate).parent().show();
            setEditPatchesOptions(data.subData[value].startDate, data.subData[value].endDate, data.subData[value].filterId);
        }


        var groupValStr = data.subData[value].groupName.split("###");
        $("#edit_mumGroupBy").val(groupValStr[0]).change();
        $("#edit_mumCatBy").val(groupValStr[1]).change();
        $('.selectpicker').selectpicker('refresh');
    }

    $(".editsection").mCustomScrollbar({theme: "minimal-dark"});
    $('.editsection').mCustomScrollbar('update');
}

$('#edit_summary_chart_type').on('change', function () {
    if ($('#edit_summary_chart_type').val() == 5) {
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
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getEventFilters"+"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json'
        }).done(function (data) {
            editEvent = data;
            eventOptions = '<option value="0">Choose filter</option>';
            for (var i = 0; i < data.length; i++) {
                if (data[i].id == filterId) {
                    eventOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '" selected>' + data[i].name + '</option>';
                } else {
                    eventOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
                }
            }
            eventEditData = eventOptions;
//            console.log(eventOptions);
            $('#edit_filterType_' + header).html("");
            $('#edit_filterType_' + header).html(eventOptions);
            $('.selectpicker').selectpicker('refresh');
        });

    } else if (sectionType == 2) {
        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getAssetQueries"+"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json'
        }).done(function (data) {
            editAsset = data;
            assetOptions = '<option value="0">Choose Query</option>';
            for (var i = 0; i < data.length; i++) {
                if (data[i].id == filterId) {
                    assetOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '" selected>' + data[i].name + '</option>';
                } else {
                    assetOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
                }
            }
            assetEditData = assetOptions;
//           console.debug(assetOptions); 
            $('#edit_filterType_' + header).html("");
            $('#edit_filterType_' + header).html(assetOptions);
            $('.selectpicker').selectpicker('refresh');
            var populateGrouping = edit_populateGroupingOptions(header, "", groupName);
            if (populateGrouping) {
                $("#edit_subHeaderAssetGroupBy option[value='Site Name']").prop('selected', true);
                $("#edit_subHeaderAssetCatBy option[value='Chassis Manufacturer']").prop('selected', true);
            }

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
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getEventFilters"+"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json'
        }).done(function (data) {
            var eventOptions = '<option value="0">Choose filter</option>';
            for (var i = 0; i < data.length; i++) {
                if (filterId == data[i].id) {
                    eventOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '" selected>' + data[i].name + '</option>';
                } else {
                    eventOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
                }
            }
            summaryEditData = eventOptions;
            $('#edit_summaryFilter' + header).html("");
            $('#edit_summaryFilter' + header).html(eventOptions);
            $('.selectpicker').selectpicker('refresh');
            $('.edit_eventDuration' + header).show();
            $('#edit_eventDuration' + header + ' option[value=' + eventDuration + ']').attr("selected", true);
            $('.selectpicker').selectpicker('refresh');

        });
    } else if (filterType == 2) {
        //asset option
        $.ajax({
            type: "POST",
            url: "../lib/l-mngdRprt.php?function=1&functionToCall=getAssetQueries"+"&csrfMagicToken=" + csrfMagicToken,
            dataType: 'json'
        }).done(function (data) {
            var assetOptions = '<option value="0">Choose filter</option>';
            for (var i = 0; i < data.length; i++) {
                if (filterId == data[i].id) {
                    assetOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '" selected>' + data[i].name + '</option>';
                } else {
                    assetOptions += '<option value="' + data[i].id + '" grp="' + data[i].groupby + '">' + data[i].name + '</option>';
                }
            }
            summaryAssetData = assetOptions;
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
        url: '../lib/l-mngdRprt.php?function=1&functionToCall=getPatchDetails&mnth=' + month + '&year=' + year +"&csrfMagicToken=" + csrfMagicToken,
        dataType: 'json'
    }).done(function (data) {

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
            '<div class="col-lg-1 col-md-1 col-sm-1 col-xs-12">' +
            '<div class="add-sumsecdata">' +
            '<a href="javascript:" onclick="showMultipleSubheader(' + indexinc + ',this,1)" id="addNewSubheader" class="addNewSubheader""><i class="icon-ic_add_24px material-icons"></i></a>' +
            '<div class="form-group label-floating is-empty is-focused">' +
            '<input type="hidden" class="edit_head_count" value="' + subHeaders[header] + '">' +
            '</div>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">' +
            '<div class="form-group filter_type">' +
            '<select class="form-control selectpicker dropdown-submenu edit_queryFilterType" data-size="5" id="edit_filterType_' + subHeaders[header] + '">' +
            '</select>' +
            '</div>' +
            '</div>' +
            '<div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">' +
            '<div class="row clearfix">' +
            '<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">' +
            '<div class="remove-sumsecdata">' +
            '<div class="form-group editEventDuration" style="display:none">' +
            '</div>' +
            '<a href="javascript:" onclick="removeSubHdr(this)"><i class="material-icons icon-ic_close_24px"></i></a>' +
            '</div>' +
            '</div>' +
            '</div>' +
            '</div>';

    $(htmlSubHdr).insertAfter($(data).parent().parent().parent());
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

function delSecNotView() {
    //alert(123);
    tempSectionId = $('#sectionsTable tbody tr.selected').attr('id');
    var sectionid = tempSectionId;
    //alert(tempSectionId);
    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=getSectionValid&sectionId=" + sectionid +"&csrfMagicToken=" + csrfMagicToken,
        success: function (data) {
            data = $.trim(data);
            if (data == 0 || data == '0') {
                $('#delete_Section').modal('show');
            } else {
//               console.log('could not delete this section');
                $('#delete_SectionView').modal('show');
            }
        }
    });
}

$('#delete_Sec').click(function () {
    var id = tempSectionId;
    //alert(id);
    $.ajax({
        type: "POST",
        url: "manageViewsFun.php?function=deleSectionData&sectionId=" + id,
        success: function (data) {
            data = $.trim(data);
            if (data == 1 || data == '1') {
                $('#delMsg').html('<span style="color:green">Successfully section deleted</span');
                setTimeout(function () {
                    $('#delMsg').html('');
                    $('#delete_Section').modal('hide');
                    getAllSections();
                }, 3000);
            } else {
                $('#delMsg').html('<span style="color:red">Some error occour</span');
                setTimeout(function () {
                    $('#delMsg').html('');
                    $('#delete_Section').modal('hide');
                }, 2000);
            }
        }
    });
});

function subHeaderGroup(header, obj) {

    if ($(obj).val() === 'date') {
//        $('.subHeader_type').show();
        $('.subHeader_type').show();
        $('.subHeaderDuration').show();

        $('.edit_subHeader').show();
        $('.edit_subHeaderDuration').show();
        $('.subHeader_type_range').show();
    } else {
//        $('.subHeader_type').hide();
        $('.subHeader_type').show();
        $('.subHeaderDuration').hide();
        $('.subHeader_type_range').hide();

        $('.edit_subHeader').show();
        $('.edit_subHeaderDuration').hide();
        $('.subHeader_type_range').hide();
    }

}

