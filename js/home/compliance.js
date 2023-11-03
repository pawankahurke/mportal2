// Notification JS Functionality.

$(document).ready(function() {
    getComplianceItems(itemType, stattype);
});

var notificationArray = [['', 'Count']];
var filteritemtypes = '';
var searchArray1 = '';

function reloadCompliance(elementName) {
    $("#compHeader").html("<span>Compliance : </span>" + elementName);
    getComplianceItemsReload(itemType, stattype);
}

switch (itemType) {
    case "5":
        $('#complianceViewing').html('<span style="color:#48b2e4;">Viewing : Availability</span>');
        $("#type_Availability").prop("checked", true);
        break;
    case "7":
        $('#complianceViewing').html('<span style="color:#48b2e4;">Viewing : Security</span>');
        $("#type_Security").prop("checked", true);
        break;
    case "8":
        $('#complianceViewing').html('<span style="color:#48b2e4;">Viewing : Resources</span>');
        $("#type_Resources").prop("checked", true);
        break;
    case "9":
        $('#complianceViewing').html('<span style="color:#48b2e4;">Viewing : Events</span>');
        $("#type_Events").prop("checked", true);
        break;
    case "10":
        $('#complianceViewing').html('<span style="color:#48b2e4;">Viewing : Maintenance</span>');
        $("#type_Maintenance").prop("checked", true);
        break;
    default:
        $('#complianceViewing').html('<span style="color:#48b2e4;">Viewing : Availability</span>');
        $("#type_Availability").prop("checked", true);
        break;
}

switch (stattype) {
    case "1":
        $("#status_Ok").prop("checked", true);
        break;
    case "2":
        $("#status_Warning").prop("checked", true);
        break;
    case "3":
        $("#status_Alert").prop("checked", true);
        break;
    default:
        $("#status_Ok").prop("checked", true);
        break;
}

function getComplianceItems(itemtype, status) {

    var itmTpe = "";
    var stts = "";

    $("#filtertype").val(itemtype);
    $("#filterstatus").val(status);

    if (itemtype == 1 || itemtype == "1") {
        $("#showitemtype").html("All");
        itmTpe = "All";
    } else if (itemtype == 5 || itemtype == "5") {
        $("#showitemtype").html("Availability");
        itmTpe = "Availability";
    } else if (itemtype == 7 || itemtype == "7") {
        $("#showitemtype").html("Security");
        itmTpe = "Security";
    } else if (itemtype == 8 || itemtype == "8") {
        $("#showitemtype").html("Resources");
        itmTpe = "Resources";
    } else if (itemtype == 10 || itemtype == "10") {
        $("#showitemtype").html("Maintenance");
        itmTpe = "Maintenance";
    } else if (itemtype == 9 || itemtype == "9") {
        $("#showitemtype").html("Events");
        itmTpe = "Events";
    }

    if (status == 1 || status == "1") {
        $("#showstatus").html("Ok");
        stts = "Ok";
    } else if (status == 2 || status == "2") {
        $("#showstatus").html("Warning");
        stts = "Warning";
    } else if (status == 3 || status == "3") {
        $("#showstatus").html("Alert");
        stts = "Alert";
    }

    $("#detailsShow").html("");
//    $("#detailsShow").html("<span>(Showing compliance items with status "+itmTpe+" and "+stts+" for the past 15 days.)</span>");
    $("#detailsShow").html("<span>(Showing compliance data for the past 15 days.)</span>")

    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    $.ajax({
        url: "../lib/l-ajax.php?function=AJAX_GetComplianceItems",
        type: 'POST',
        //data: 'ptype='+ptype+'&status='+status,
        data: 'searchType=' + searchType + '&searchValue=' + searchValue + '&itemtype=' + itemtype + '&status=' + status,
        success: function(data) {
            var res = data.split('####');
            var critData = res[0];
            var firstid = res[1];
            var itemType = res[2];
            var statusname = res[3];
            $('#rightcompliance').html('<span style="color:#48b2e4;"> Details : '+ statusname +'</span>');
            $('#criteriaList').html('<span>' + critData.trim() + '</span>');
            $('#criteriaList li').first().find("a").addClass('active');
            loadComplianceData(firstid.trim(), itemType.trim());
        },
        error: function(err) {
            console.log(err);
        }
    });

    itemTypeArray = {1: "ALL", 5: "Availability", 7: "Security", 8: "Resources", 10: "Maintenance", 9: "Events"};
    statTypeArray = {'1': "Ok", '2': "Warning", 3: "Alert"};

    var caret = '&nbsp; <span class="caret"></span>';
    $('#itemtype_val').html('<span itemtype="' + itemtype + '">' + itemTypeArray[itemtype] + '</span>' + caret);
    $('#status_val').html('<span stattype="' + status + '">' + statTypeArray[status] + '</span>' + caret);
}

function getComplianceItemsReload(itemtype, stat) {

    var itmTpe = "";
    var stts = "";

    $("#filtertype").val(itemtype);
    $("#filterstatus").val(stat);
    $("#itemtypeExcel").val(itemtype);

    if (itemtype == 1 || itemtype == "1") {
        $("#showitemtype").html("All");
        itmTpe = "All";
    } else if (itemtype == 5 || itemtype == "5") {
        $("#showitemtype").html("Availability");
        itmTpe = "Availability";
    } else if (itemtype == 7 || itemtype == "7") {
        $("#showitemtype").html("Security");
        itmTpe = "Security";
    } else if (itemtype == 8 || itemtype == "8") {
        $("#showitemtype").html("Resources");
        itmTpe = "Resources";
    } else if (itemtype == 10 || itemtype == "10") {
        $("#showitemtype").html("Maintenance");
        itmTpe = "Maintenance";
    } else if (itemtype == 9 || itemtype == "9") {
        $("#showitemtype").html("Events");
        itmTpe = "Events";
    }

    if (stat == 1 || stat == "1") {
        $("#showstatus").html("Ok");
        stts = "Ok";
    } else if (stat == 2 || stat == "2") {
        $("#showstatus").html("Warning");
        stts = "Warning";
    } else if (stat == 3 || stat == "3") {
        $("#showstatus").html("Alert");
        stts = "Alert";
    }

    $("#detailsShow").html("");
//    $("#detailsShow").html("<span>(Showing compliance items with status "+itmTpe+" and "+stts+" for the past 15 days.)</span>");
    $("#detailsShow").html("<span>(Showing compliance data for the past 15 days.)</span>");

    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    $.ajax({
        url: "../lib/l-ajax.php?function=AJAX_GetComplianceFilterItems",
        type: 'POST',
//        data: 'searchType=' + searchType + '&searchValue=' + searchValue + '&itemtype=' + itemtype + '&status=' + stat,
        data: 'searchType=' + searchType + '&searchValue=' + searchValue + '&filteritem=' + itemtype + '&filterstatus=' + stat,
        success: function(data) {
            var res = data.split('####');
            var critData = res[0];
            var firstid = res[1];
            var itemType = res[2];
            $('#criteriaList').html(critData.trim());
            var fthis = $('#criteriaList li').first();
            reloadComplianceData(firstid.trim(), itemType.trim(), stat, fthis);
        },
        error: function(err) {
            console.log(err);
        }
    });
}

function loadComplianceData(id, itemtype) {

    $("#idExcel").val(id);
    $("#itemtypeExcel").val(itemtype);

    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    table1 = $('.order-table').DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        stateSave: true,
        responsive: true,
//        columnDefs: [{
//            targets: "datatable-nosort",
//            orderable: false,
//        }],
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        paging: true,
        searching: true,
        processing: false,
        serverSide: true,
        ajax: {
            url: "../lib/l-ajax.php?function=AJAX_GetComplianceDetails&searchType=" + searchType + "&searchValue=" + searchValue + "&itemid=" + id + '&itemtype=' + itemtype + '&status=' + stattype,
            type: "POST",
            rowId: 'id',
            data: { 'csrfMagicToken': csrfMagicToken }
        },
        columnDefs: [
            {className: "dt-left table-plus checkbox-btn datatable-nosort", "targets": 0},
            {className: "dt-left tdColumn2", "targets": 1},
            {className: "dt-left tdColumn3", "targets": 2}
        ],
        columns: [
            {"data": "checkbox-btn", "orderable": false},
            {"data": "machine"},
            {"data": "servertime"},
            {"data": "eventcount"}
        ],
        select: true,
        drawCallback: function(settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
            $(".se-pre-con").hide();
        }
    });

    $("#compliance_searchbox").keyup(function() {
        table1.search(this.value).draw();
    });
    
    $('.order-table').DataTable().search( '' ).columns().search( '' ).draw();
}

function reloadComplianceData(id, itemtype, stat, obj) {
    $("#idExcel").val(id);
    $("#itemtypeExcel").val(itemtype);
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    $('#criteriaList li a').removeClass('active');
    $(obj).find("a").addClass('active');
    $('#rightcompliance').html('<span style="color:#48b2e4;"> Details : '+ $(obj).text() +'</span>');
    var urlnew = "../lib/l-ajax.php?function=AJAX_GetComplianceDetails&searchType=" + searchType + "&searchValue=" + searchValue + "&itemid=" + id + '&itemtype=' + itemtype + '&status=' + stat;
    table1.ajax.url(urlnew).load();
}

$('#itemtype_drop').find('ul li').click(function() {
    var itemname = $(this).find('a').attr('itemname');
    var itemtype = $(this).find('a').attr('itemtype');
    var caret = '&nbsp; <span class="caret"></span>';
    $('#itemtype_val').html('<span itemtype="' + itemtype + '">' + itemname + '</span>' + caret);

    var status = $('#status_val').find('span').attr('stattype');

    $('#criteriaList').html('<span>Loading Data...</span>');
    getComplianceItemsReload(itemtype, status);
});

$('#status_drop').find('ul li').click(function() {
    var statsel = $(this).find('a').attr('statname');
    var status = $(this).find('a').attr('stattype');
    var caret = '&nbsp; <span class="caret"></span>';
    $('#status_val').html('<span stattype="' + status + '">' + statsel + '</span>' + caret);

    var itemtype = $('#itemtype_val').find('span').attr('itemtype');

    $('#criteriaList').html('<span>Loading Data...</span>');
    itemType = itemtype;
    stattype = status;
    getComplianceItemsReload(itemtype, status);
});

function checkBox(e, obj) {
    e = e || event;/* get IE event ( not passed ) */
    e.stopPropagation ? e.stopPropagation() : e.cancelBubble = true;
    $('.commonClass').prop('checked', obj.checked);
}

$("#topCheckBox").change(function() {

    if (this.checked) {
        $('.user_check').prop('checked', true);
    } else {
        $('.user_check').prop('checked', false);
    }
});

function resetComp() {
    var radioVal = [];
    var itemTypeVal = [];
    var itemIdVal  = [];
    
    $('.user_check').each(function() {
        if ($(this).is(':checked')) {
            var temp = $(this).attr('id').split("_");
              radioVal.push(temp[0]);
              itemTypeVal.push(temp[1]);
              itemIdVal.push(temp[2]);
        }
    });

    if (radioVal.length === 0) {
        $("#warning").modal('show');
        return false;
    }

    var censusId = radioVal.join(",");
    var itemType = itemTypeVal.join(",");
    var itemId   = itemIdVal.join(",");
    $.ajax({
        url: "../lib/l-ajax.php",
        type: 'POST',
        data: 'function=AJAX_ResetComplianceItems&censusId=' + censusId+ '&itemType='+itemType+'&itemId='+itemId,
        success: function(data) {
            if($.trim(data) === 'done') {
            location.reload();
            }
//            location.reload();
        },
        error: function(err) {
            console.log(err);
        }
    });
}

function excelExport() {

    var id = $("#idExcel").val();
    var itemtype = $("#itemtypeExcel").val();
    var itemstatus = $("#filterstatus").val();
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();

    var comp = [];
    var status = [];
    $.each($("input[class='type_check']:checked"), function() {
        comp.push($(this).val());
        comp.join(",");
    });
    $.each($("input[class='status_check']:checked"), function() {
        status.push($(this).val());
        status.join(",");
    });

    if (id == '' || itemtype == '') {
        $('#exportAlert').modal('show');
    } else {
        window.location.href = "../lib/l-ajax.php?function=AJAX_GetComplianceExportDetails&searchType=" + searchType + "&searchValue=" +
                searchValue + "&itemid=" + id + '&itemtype=' + itemtype + '&status=' + itemstatus;
    }

}

function clearCheck() {
    $(".status_check").prop('checked', false);
    $(".type_check").prop('checked', false);
    getComplianceItemsReload(0, 0);
}

$("#type_All").click(function() {
    if ($("#type_All").is(":checked")) {
        $(".type_check").prop("checked", true);
        var filteritemtypes = "5,7,8,9,10";
        $("#filtertype").val(filteritemtypes);
    } else {
        $(".type_check").prop("checked", false);

    }
});

$('.type_check').change(function() {
    if (false == $(this).prop("checked")) {
        $("#type_All").prop('checked', false);
    }
    if ($('.type_check:checked').length < $('.type_check').length - 1) {
        var searchArray = "";
        var searchString = [];
        $('input:checkbox.type_check').each(function() {

            if (this.checked) {
                searchString.push($(this).val());
            }
            searchArray = searchString.join(',');
        });
        $("#filtertype").val(searchArray);
    }

    if ($('.type_check:checked').length == ($('.type_check').length - 1)) {
        $("#type_All").prop('checked', true);
         filteritemtypes = "5,7,8,9,10";
        $("#filtertype").val(filteritemtypes);
    }
    Get_ComplianceFilterSearchData();
});

$('.status_check').change(function() {
//    var searchArray1 = "";
    var searchString1 = [];
    $('input:checkbox.status_check').each(function() {
        if (this.checked) {
            searchString1.push($(this).val());
        }
        searchArray1 = searchString1.join(',');
    });
    $("#filterstatus").val(searchArray1);
    Get_ComplianceFilterSearchData();
});

function Get_ComplianceFilterSearchData() {
    var filtertype = $("#filtertype").val();
    var filterstatus = $("#filterstatus").val();
   
    itemType = filtertype;
    stattype = filterstatus;
    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    $.ajax({
        url: "../lib/l-ajax.php?function=AJAX_GetComplianceFilterItems",
        type: 'POST',
        //data: 'ptype='+ptype+'&status='+status,
        data: 'searchType=' + searchType + '&searchValue=' + searchValue + '&filteritem=' + filtertype + '&filterstatus=' + filterstatus,
        success: function(data) {
            var res = data.split('####');
            var critData = res[0];
            var firstid = res[1];
            var itemType = res[2];
            $('#criteriaList').html('<span>' + critData.trim() + '</span>');
            $('#criteriaList li').first().find("a").addClass('active');
            loadComplianceFilterData(firstid.trim(), itemType.trim());
        },
        error: function(err) {
            console.error(err);
        }
    });
}

function loadComplianceFilterData(id, itemtype) {

    $("#idExcel").val(id);
    $("#itemtypeExcel").val(itemtype);

    var searchType = $('#searchType').val();
    var searchValue = $('#searchValue').val();
    var filtertype = $("#filtertype").val();
    var filterstatus = $("#filterstatus").val();
    $('.order-table').DataTable().destroy();
    table1 = $('.order-table').DataTable({
        scrollY: jQuery('.order-table').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        bAutoWidth: true,
        responsive: true,
//        columnDefs: [{
//            targets: "datatable-nosort",
//            orderable: false,
//        }],
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        paging: true,
        searching: true,
        processing: false,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        serverSide: true,
        ajax: {
            url: "../lib/l-ajax.php?function=AJAX_GetComplianceFilterDetails&searchType=" + searchType + "&searchValue=" + searchValue + "&itemid=" + id + '&filteritem=' + itemtype + '&filterstatus=' + filterstatus,
//            url: "../lib/l-ajax.php?function=AJAX_GetComplianceDetails&searchType=" + searchType + "&searchValue=" + searchValue + "&itemid=" + id + '&itemtype=' + itemtype + '&status=' + stattype,
            type: "POST",
            rowId: 'id',
            data: { 'csrfMagicToken': csrfMagicToken }
        },
        columnDefs: [
            {className: "dt-left table-plus checkbox-btn datatable-nosort", "targets": 0},
            {className: "dt-left tdColumn2", "targets": 1},
            {className: "dt-left tdColumn3", "targets": 2}
        ],
        columns: [
            {"data": "checkbox-btn", "orderable": false},
            {"data": "machine"},
            {"data": "servertime"},
            {"data": "eventcount"}
        ],
        select: true,
        drawCallback: function(settings) {
            $(".dataTables_scrollBody").mCustomScrollbar({
                theme: "minimal-dark"
            });
            $(".se-pre-con").hide();
        }
    });

    $("#compliance_searchbox").keyup(function() {
        table1.search(this.value).draw();
    });
    
    $('.order-table').DataTable().search( '' ).columns().search( '' ).draw();
}

function checkAvailability() {
var temp = $('#type_Availability').is(":checked");
    if (temp == true) {
        $('#complianceViewing').html('<span style="color:#48b2e4;">Viewing : Availability</span>');
    }
//    else {
//        $('#complianceViewing').html('<span>Viewing</span>');
//    }             
}

function checkMaintenance() {
var temp = $('#type_Maintenance').is(":checked");
    if (temp == true) {
        $('#complianceViewing').html('<span style="color:#48b2e4;">Viewing : Maintenance</span>');
    }             
}

function checkEvent() {
var temp = $('#type_Events').is(":checked");
    if (temp == true) {
        $('#complianceViewing').html('<span style="color:#48b2e4;">Viewing : Events</span>');
    }           
}

function checkSecurity() {
var temp = $('#type_Security').is(":checked");
    if (temp == true) {
        $('#complianceViewing').html('<span style="color:#48b2e4;">Viewing : Security</span>');
    }   
}

function checkResources() {
var temp = $('#type_Resources').is(":checked");
    if (temp == true) {
        $('#complianceViewing').html('<span style="color:#48b2e4;">Viewing : Resources</span>');
    }   
}

function checkAll() {
var temp = $('#type_All').is(":checked");
    if (temp == true) {
        $('#complianceViewing').html('<span style="color:#48b2e4;">Viewing : All</span>');
    }   
}
//if ($('input[name=type_Maintenance]:checked')) {
//    alert('1');
//}
