serviceLogGrid('preServiceLogGrid.php?status=' + status + '&cat=5' + '&level=' + level);

function serviceLogGrid(url){
    var h = window.innerHeight;
        if (h > 700) {

            $("#predictiveleftList").attr("data-page-length", "10");
        }
        else {
            $("#predictiveleftList").attr("data-page-length", "8");
        }
    $("#predictiveleftList").dataTable().fnDestroy();
    var groupTable = $('#predictiveleftList').DataTable({
        autoWidth: false,
        paging: true,
        searching: false,
        processing: true,
        serverSide: true,
        pagingType: "full_numbers",
        bAutoWidth: false ,
        bLengthChange: false,
        bExpandableGrouping : true,
        ajax: {
            url: url,
            dataType:'json',
            type: "POST"
        },
        columns: [
            {"data": "name"}
        ],
        "columnDefs": [
            { className: "dt-left", "targets": [ 0 ] }
          ],
        initComplete: function( settings, json ) {
            
            var rowId = $('#predictiveleftList tbody tr:eq(0)').attr('id');
            $("#predictiveleftList tbody tr").removeClass("selected");
            $('#'+rowId).addClass("selected");
            if(rowId != undefined ){
            var array = rowId.split("_");
            fixedGrid(rowId,array[0]);
        }
        else{
        }
        }
    });
    $('#predictiveleftList tbody').on( 'mouseover', 'td', function () {
            var rowID = groupTable.row(this).data();
            $("#predictiveleftList tbody tr td").eq(0).attr("data-target","tooltip");
            $("td:nth-child(1)").attr("title",""+rowID.name);
    });
    $("#predictive_searchbox").keyup(function () {
        groupTable.search(this.value).draw();
    });
    $('#predictiveleftList_filter').hide();
    $('#predictiveleftList_info').hide();
    $('#predictiveleftList_paginate').hide();
    $('#predictiveleftList tbody').on('click', 'tr', function () { //row selection code
        if ($(this).hasClass('selected')) {
            var rowdata = groupTable.row(this).data();
            $(this).removeClass('selected');
        } else {
            var rowdata = groupTable.row(this).data();
            $('#proactiveid').val(rowdata.id);
            $('#proactive').val(rowdata.status);
            $('#proactiveitemid').val(rowdata.id);
            $('#proactiveitemtype').val(rowdata.itemtype);
            groupTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
            fixedGrid(rowdata.id,rowdata.status);
        }
    });
}
$(function(){
    $('sorting_asc li').first().css({'background-color':'#48b2e4' , 'color':'white'});
//    $('ul#ul_systemInfoList li:first').addClass('active');
    $('ul#ul_systemInfoList li:first').click();
    
});

function fixedGrid(id,status){
     if (typeof fixGrid_var == 'undefined') {
        fixGrid_var = 0;
    } else {
        fixGrid_var = 1;
    }
   var h = window.innerHeight;
        if (h > 700) {

            $("#predictiverightList").attr("data-page-length", "10");
        }
        else {
            $("#predictiverightList").attr("data-page-length", "8");
        }
    $("#predictiverightList").dataTable().fnDestroy();
    var predRightList = $('#predictiverightList').DataTable({
        autoWidth: false,
        paging: true,
        searching: true,
        processing: true,
        serverSide: true,
        pagingType: "full_numbers",
        bAutoWidth: false ,
        bLengthChange: false,
        bExpandableGrouping : true,
        
        ajax: {
            url: 'preServiceLogDetailGrid.php?eventitemid=' + id + '&status=' + status +'&level=' + level,
            dataType:'json',
            type: "POST"
            
        },
        columns: [
            {"data": "mchchkBx"},
            {"data": "machine"},
            {"data": "count"},
            {"data": "crithtml"}
        ],
        "columnDefs": [
            { className: "dt-left tdColumn1", "targets": 0 },
            { className: "dt-left tdColumn2", "targets": 1 },
            { className: "dt-left tdColumn3", "targets": 2 },
            { className: "dt-left tdColumn4", "targets": 3 }
          ]
    });
    $('#predictiverightList tbody').on( 'mouseover', 'td', function () {
            var rowID = predRightList.row(this).data();
            $("#predictiverightList tbody tr td").eq(1).attr("data-target","tooltip");
            $("#predictiverightList tbody tr td").eq(2).attr("data-target","tooltip");
            $("#predictiverightList tbody tr td").eq(3).attr("data-target","tooltip");
            $("td:nth-child(2)").attr("title",""+rowID.machine);
            $("td:nth-child(3)").attr("title",""+rowID.count);
            $("td:nth-child(4)").attr("title",""+rowID.crithtml);
    });    
    $('#predictiverightList_filter').hide();
}

function proactiveClick(){
    window.location.href = "../support_action/proactive.php";
}