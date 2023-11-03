$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();
    
     var h = window.innerHeight;
    if(h>700){
        $("#summaryTable").attr("data-page-length","13");
    }
    else{
        $("#summaryTable").attr("data-page-length","13");
    }
});

crumb = [];
count = 1;
stack = [];
function fetchSummaryTable(customerType, entityId , entityIdName) {
    
    var entityArray = { 1: 'Entity', 2: 'Channel', 3: 'Subchannel', 4: 'Outsource', 5: 'Customer'};
    $("#selectConfirm").html("");
    $("#detailDiv").hide();
    $("#gridDiv").show();
    $('.bottomtable').remove();
    $("#summaryTable").dataTable().fnDestroy();
    table = $('#summaryTable').DataTable({
        scrollY: 400,
        autoWidth: true,
        paging : true,
        searching : true,
        processing: false,
        serverSide: true,
        ajax: {
            url: "summaryTableFun.php?function=get_EntityGridData&customerType=" + customerType + "&entityId=" + entityId + "&entityIdName=" + entityIdName,
            type: "POST",
            rowId: 'id',
        },
        columns: [
            {"data": "companyName"},
            {"data": "customertype"},
            {"data": "regNo"},
            {"data": "firstName"},
            {"data": "lastName"},
            {"data": "emailId"},
            {"data": "website"},
        ],
        columnDefs: [{
                targets: 5,
                orderable: false
            },
            {
                targets: 1,
                orderable: false
            }, 
           {className: "dt-left tdColumn1", "targets": 0},
           {className: "dt-left tdColumn1", "targets": 1},
           {className: "dt-left tdColumn1", "targets": 2},
           {className: "dt-left tdColumn1", "targets": 3},
           {className: "dt-left tdColumn1", "targets": 4},
           {className: "dt-left tdColumn1", "targets": 5},
           {className: "dt-left tdColumn1", "targets": 6}
           
        ],
        bInfo: false,
        responsive: true,
        dom: '<"user-table-list"<"top"f>rt<"bottomtable"lpi><"clear">>',
        scrollCollapse: true,
        bLengthChange: true,
        select: true,
        "lengthMenu": [[25, 50, -1], [25, 50, "All"]],
    });
//    $('#summaryTable tbody').on( 'mouseover', 'td', function () {
//            var rowID = table.row(this).data();
//            $("#DeviceTypeData tbody tr td").eq(0).attr("data-target","");
//            $("#summaryTable tbody tr td").eq(0).attr("data-target","tooltip");
//            $("#summaryTable tbody tr td").eq(1).attr("data-target","tooltip");
//            $("#summaryTable tbody tr td").eq(2).attr("data-target","tooltip");
//            $("#summaryTable tbody tr td").eq(3).attr("data-target","tooltip");
//            $("#summaryTable tbody tr td").eq(4).attr("data-target","tooltip");
//            $("#user-table tbody tr td").eq(5).attr("data-target","tooltip");
//            $("#user-table tbody tr td").eq(5).attr("data-target","tooltip");
//            $("td:nth-child(1)").attr("title",""+rowID.status);
//            $("td:nth-child(1)").attr("title",""+rowID.companyName);
//            $("td:nth-child(2)").attr("title",""+rowID.customertype);
//            $("td:nth-child(3)").attr("title",""+rowID.regNo);
//            $("td:nth-child(4)").attr("title",""+rowID.firstName);
//            $("td:nth-child(5)").attr("title",""+rowID.lastName);
//            $("td:nth-child(6)").attr("title",""+rowID.emailId);
//            $("td:nth-child(7)").attr("title",""+rowID.website);
//    });
    $("#summaryTable_length").hide();
    $("#summaryTable_filter").hide();
    $('#summaryTable tbody').on('click', 'tr', function() { //row selection code
        $("#selectConfirm").html("");
        var rowdata = table.row(this).data();
        $('#selectedEid').val(rowdata.id);
        table.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');

    });
    
    $('#summaryLi li:last').remove();
    $("#summaryLi").append('<li style="cursor:pointer !important;"><a onclick="fetchSummaryTable(' + customerType + ',' + entityId + ',' + '\'' + entityIdName + '\'' + ')";>'+ entityArray[customerType] +' Summary </a></li>');
    $('#summaryLi1 li:last').remove();
    $("#summaryLi1").append('<li style="cursor:pointer !important;"><a onclick="fetchSummaryTable(' + customerType + ',' + entityId + ',' + '\'' + entityIdName + '\'' + ')";>'+ entityArray[customerType] +' Summary </a></li>');
    
    $("#summary_searchbox").keyup(function() {
        table.search(this.value).draw();
    });
    $(".bottompager").each(function(){
        $(this).append($(this).find(".bottomtable"));
    });
    
}



function editEntity() {
    var selectedEid = $("#selectedEid").val();
    var selectedCtype = '';
    var userCtype = '';
    var URL = '';

    if (selectedEid === '') {
        $("#selectConfirm").html("Please select a record");
    } else {
        $.ajax({
            type: 'POST',
            url: 'summaryTableFun.php?function=get_EntityCtype&eid=' + selectedEid + "&csrfMagicToken=" + csrfMagicToken,
            async: false,
            dataType: "text",
            success: function(data) {
                response = data;
            }
        });
        res = response.split("##");
        selectedCtype = res[0].trim();
        userCtype = res[1].trim();
        
        if (selectedCtype == 1 && userCtype == 0) {
            URL = "editEntity.php?entityId=" + selectedEid;
            window.location.href = URL;
        } else if (selectedCtype == 2 && userCtype == 1) {
            URL = "editChannel.php?entityId=" + selectedEid;
            window.location.href = URL;
        } else if (selectedCtype == 3 && userCtype == 2) {
            URL = "editSubChannel.php?entityId=" + selectedEid;
            window.location.href = URL;
        } else if (selectedCtype == 4 && userCtype == 1) {
            URL = "editOutPartner.php?entityId=" + selectedEid;
            window.location.href = URL;
        } else if (selectedCtype == 5) {
            $.ajax({
                type: 'POST',
                url: 'summaryTableFun.php?function=checkCustomerEdit&eid=' + selectedEid + '&cType=' + userCtype + "&csrfMagicToken=" + csrfMagicToken,
                dataType: "text",
                success: function(data) {
                    var ret = data.trim();
                    if (ret === 1 || ret === '1') {
                        URL = "editCustomer.php?entityId=" + selectedEid;
                        window.location.href = URL;
                    } else {
                        
                        $("#selectConfirm").html('Permission denied to edit');
                    }
                }
            });
        } else if (selectedCtype !== userCtype) {
            $("#selectConfirm").html('Permission denied to edit');
            return false;
        } else {
            URL = "summary.php";
        }
    }
}


function fetchCustomerSummaryTable(customerType, entityId , entityIdName) {
    
    var entityArray = { 1: 'Entity', 2: 'Channel', 3: 'Subchannel', 4: 'Outsource', 5: 'Customer'};
    $("#selectConfirm").html("");
    $("#detailDiv").hide();
    $("#gridDiv").show();
    $('.bottomtable').remove();
    $("#summaryTable").dataTable().fnDestroy();
    table = $('#summaryTable').DataTable({
        paging: true,
        searching: true,
        processing: true,
        serverSide: true,
        pagingType: "full_numbers",
        ajax: {
            url: "summaryTableFun.php?function=get_CustomerGridData&customerType=" + customerType + "&entityId=" + entityId + "&entityIdName=" + entityIdName,
            type: "POST",
            rowId: 'id',
        },
        columns: [
            {"data": "companyName"},
            {"data": "customertype"},
            {"data": "regNo"},
            {"data": "firstName"},
            {"data": "lastName"},
            {"data": "emailId"},
            {"data": "website"},
        ],
        columnDefs: [{
                targets: 5,
                orderable: false
            },
            {
                targets: 1,
                orderable: false
            }, 
           {className: "dt-left tdColumn1", "targets": 0},
           {className: "dt-left tdColumn1", "targets": 1},
           {className: "dt-left tdColumn1", "targets": 2},
           {className: "dt-left tdColumn1", "targets": 3},
           {className: "dt-left tdColumn1", "targets": 4},
           {className: "dt-left tdColumn1", "targets": 5},
           {className: "dt-left tdColumn1", "targets": 6}
           
        ],
        ordering: true,
        select: false,
        bInfo: false,
        responsive: true,
        dom: '<"top"i>rt<"bottomtable"flp><"clear">',
    });
    $("#summaryTable_length").hide();
    $("#summaryTable_filter").hide();
    $('#summaryTable tbody').on('click', 'tr', function() { //row selection code
        $("#selectConfirm").html("");
        var rowdata = table.row(this).data();
        $('#selectedEid').val(rowdata.id);
        table.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');

    })
    
    $('#summaryLi li:last').remove();
    $("#summaryLi").append('<li style="cursor:pointer !important;"><a onclick="fetchSummaryTable(' + customerType + ',' + entityId + ',' + '\'' + entityIdName + '\'' + ')";>'+ entityArray[customerType] +' Summary </a></li>');
    $('#summaryLi1 li:last').remove();
    $("#summaryLi1").append('<li style="cursor:pointer !important;"><a onclick="fetchSummaryTable(' + customerType + ',' + entityId + ',' + '\'' + entityIdName + '\'' + ')";>'+ entityArray[customerType] +' Summary </a></li>');
    
    $("#header_searchbox").keyup(function() {
        table.search(this.value).draw();
    });
    
    $(".bottompager").each(function(){
        $(this).append($(this).find(".bottomtable"));
    });
    
}