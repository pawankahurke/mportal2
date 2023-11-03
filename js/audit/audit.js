$(function(){
   $("#auditdataGrid").dataTable().fnDestroy();
    var groupTable = $('#auditdataGrid').DataTable({
        autoWidth: true,
        paging: true,
        searching: true,
        processing: true,
        serverSide: true,
        bLengthChange: false,
        scrollY: 'calc(100vh - 240px)',
        pagingType: "full_numbers",
        ajax: {
            url: "auditGrid.php?level="+level,
            type: "POST"
        },
        columns: [
            {"data": "detail"},
            {"data": "time"},
            {"data": "auditid"},
            {"data": "site"},
            {"data": "machine"},
            {"data": "version"},
            {"data": "details"}
        ],
      columnDefs: [
            {
              targets: 0,
              orderable: false
            },
            {
              targets: 6,
              orderable: false
            },
            { 
                className: "dt-left", "targets": [ 0,1,2,3,4,5,6 ] 
            }
      ],
      "dom": '<"top"f>rt<"bottom"lp><"clear">',
      "language": {
            search: "_INPUT_",
            searchPlaceholder: "Search records",
        }
    });
    $("#audit_searchbox").keyup(function() {
        groupTable.search(this.value).draw();
        });
    $('#auditdataGrid_filter').hide();
//    $('#auditdataGrid tbody').on('click', 'tr', function () { //row selection code
//        if ($(this).hasClass('selected')) {
//            var rowdata = groupTable.row(this).data();
//            $(this).removeClass('selected');
//        } else {
//            var rowdata = groupTable.row(this).data();
//            $('#auditvalue').val(rowdata.auditid);
//            groupTable.$('tr.selected').removeClass('selected');
//            $(this).addClass('selected');
//        }
//    }) 
});

function auditDetail(auditid){
    $("#auditstatusgrid").dataTable().fnDestroy();
    $('#auditstatusgrid').DataTable({
        autoWidth: true,
        paging: true,
        searching: false,
        processing: true,
        serverSide: true,
        pagingType: "full_numbers",
        ajax: {
            url: "ajaxauditDetails.php?auditid=" + auditid +'&level='+level,
            type: "POST"
        },
        columns: [
            {"data": "auditid"},
            {"data": "time"},
            {"data": "site"},
            {"data": "machine"},
            {"data": "details"}
        ],
        columnDefs: [
            {
              targets: 0,
              orderable: false
            },
            {
              targets: 1,
              orderable: false
            },
            {
              targets: 2,
              orderable: false
            },
            {
              targets: 3,
              orderable: false
            },
            {
              targets: 4,
              orderable: false
            },
            { 
                className: "dt-left", "targets": [ 0,1,2,3,4 ] 
            }
        ]
    });
    $('#auditstatusgrid_length').hide();
}


//function confirmrow(data_target_id){
//    var selected = $('#auditvalue').val();
//    if (selected === ''){
//         $('#' + data_target_id).attr('data-bs-target', '#warning');
//    }else{
//        if (data_target_id === 'audit_detail') {
//           $('#' + data_target_id).attr('data-bs-target', '#auditgridpopup');        
//        }
//    }
//    
//}

function auditExport(){
    window.location.href = 'auditexportList.php?level='+level;
}

/*============ audit filter function ===========*/
function auditfilterClick(){
    var sitename = $('#auditsite').val();
    var machine = $('#auditmachine').val();
    var detail = $('#auditDetail').val();
    var type = $('#auditType').val();
    $("#auditdataGrid").dataTable().fnDestroy();
    $('#auditdataGrid').DataTable({
        autoWidth: true,
        paging: true,
        searching: true,
        processing: true,
        serverSide: true,
        bLengthChange: false,
        pagingType: "full_numbers",
        ajax: {
            url: "auditGrid.php?level="+level+'&sitename='+sitename+'&machine='+machine+'&detail='+detail+'&type='+type,
            type: "POST"
        },
        columns: [
            {"data":"detail"},
            {"data": "time"},
            {"data": "auditid"},
            {"data": "site"},
            {"data": "machine"},
            {"data": "version"},
            {"data": "details"}
        ],
      columnDefs: [
            {
              targets: 0,
              orderable: false
            },
            {
              targets: 6,
              orderable: false
            },
            { 
                className: "dt-left", "targets": [ 0,1,2,3,4,5,6 ] 
            }
      ]
    });
}

