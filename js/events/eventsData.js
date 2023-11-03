$(document).ready(function() {
    $("#eventGrid").dataTable().fnDestroy();
    var table = $('#eventGrid').DataTable({
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
            url: "search.php?host="+machine +"&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        columns: [
            {"data": "description"},
            {"data": "text1"},
            {"data": "executable"},
            {"data": "clienttime"},
            {"data": "servertime"}
        ],
        columnDefs: [{
                targets: 3,
                orderable: false
            },
            {
                targets: 4,
                orderable: false
            },
//            { className: "dt-left", "targets": [ 0,1,2,3,4 ] },
            { className: "dt-left tdColumn1", "targets": 0  },
            { className: "dt-left tdColumn2", "targets": 1 },
            { className: "dt-left tdColumn3", "targets": 2 },
            { className: "dt-left tdColumn4", "targets": 3 },
            { className: "dt-left tdColumn5", "targets": 4 }
        ],
            ordering: true,
            select: false,
            bInfo: false,
            responsive: true,
            dom: '<"top"i>rt<"bottomtable"flp><"clear">',
            
    });
    
    $('#eventGrid tbody').on( 'mouseover', 'td', function () {
            var rowID = table.row(this).data();
            $("#eventGrid tbody tr td").eq(0).attr("data-target","tooltip");
            $("#eventGrid tbody tr td").eq(1).attr("data-target","tooltip");
            $("#eventGrid tbody tr td").eq(2).attr("data-target","tooltip");
            $("#eventGrid tbody tr td").eq(3).attr("data-target","tooltip");
            $("#eventGrid tbody tr td").eq(4).attr("data-target","tooltip");
            $("td:nth-child(1)").attr("title",""+rowID.description);
            $("td:nth-child(2)").attr("title",""+rowID.text1);
            $("td:nth-child(3)").attr("title",""+rowID.executable);
            $("td:nth-child(4)").attr("title",""+rowID.clienttime);
            $("td:nth-child(5)").attr("title",""+rowID.servertime);
    });
    $("#event_searchbox").keyup(function() {
        table.search(this.value).draw();
        });
        $(".bottompager").each(function () {
        $(this).append($(this).find(".bottomtable"));
    });
    $('#eventGrid_filter').hide();
    $('#eventhost').val(machine);
    $('#eventGrid tbody').on('click', 'tr', function() {
        
        table.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        var rowID = table.row(this).data();
        $('#sel_id').val(rowID.id);
        var eid = $('#sel_id').val();
        $("#detailView").on('click',function(){
            $.ajax({
            url: "eventDetailPopup.php?eid="+eid+"&csrfMagicToken=" + csrfMagicToken,
            type: "POST",
            dataType : "json",
            data : '{}',
            async : true,
            success: function (data) {
                    $('#site').html(data.site);
                    $('#machine').html(data.machine);
                    $('#clientversion').html(data.clientversion);
                    $('#cltime').html(data.cltime);
                    $('#setime').html(data.setime);
                    $('#uuid').html(data.uuid);
                    $('#uname').html(data.uname);
                    $('#priority').html(data.priority);
                    $('#desc').html(data.desc);
                    $('#scripno').html(data.scripno);
                    $('#type').html(data.type);
                    $('#exec').html(data.exec);
                    $('#version').html(data.version);
                    $('#size').html(data.size);
                    $('#uid').html(data.uid);
                    $('#string1').html(data.string1);
                    $('#string2').html(data.string2);
                    $('#clsize').html(data.clsize);
                    $('#path').html(data.path);
                    $('#text1').html(data.text1);
                    $('#text2').html(data.text2);
                    $('#text3').html(data.text3);
                    $('#text4').html(data.text4);
                }
        });
        });
    });
});


var text = $("#sel_text").val();
var scrip = $("#sel_scrip").val();
var searchstring = $("#sel_searchstring").val();
var from_date = $("#from_date").val();
var to_date = $("#to_date").val();
var executable = $("#sel_executable").val();
var id = $("#sel_id").val();

$("#sel_textExp").val(text);
$("#sel_scripExp").val(scrip);
$("#sel_searchstringExp").val(searchstring);
$("#from_dateExp").val(from_date);
$("#to_dateExp").val(to_date);
$("#sel_executableExp").val(executable);
$("#sel_idExp").val(id);

var text1 = $("#sel_textExp").val();
var scrip1 = $("#sel_scripExp").val();
var searchstring1 = $("#sel_searchstringExp").val();
var from_date1 = $("#from_dateExp").val();
var to_date1 = $("#to_dateExp").val();
var executable1 = $("#sel_executableExp").val();
var id1 = $("#sel_idExp").val();

$("#sel_textExpData").val(text1);
$("#sel_scripExpData").val(scrip1);
$("#sel_searchstringExpData").val(searchstring1);
$("#from_dateExpData").val(from_date1);
$("#to_dateExpData").val(to_date1);
$("#sel_executableExpData").val(executable1);
$("#sel_idExpData").val(id1);

//Popup validation start
function selectConfirm(data_target_id) {
    var selected = $("#sel_id").val();
    if (selected === '') {
        $('#'+data_target_id).attr('data-bs-target', '#warning');
    } else {
        if (data_target_id === 'detailView') {
            $('#'+data_target_id).attr('data-bs-target', '#event_detail');
        }
    }
    return true;
}

function exportEvents(){
    arrAllcheckVal = '&customer=1';
    $("input:checkbox[name*=eventCheckBox]:checked").each(function() {
        arrAllcheckVal += '&' + $(this).attr('id') + '=' + 1;
    });
    location.href = "ExportExcel.php?host=" + machine + arrAllcheckVal +"&csrfMagicToken=" + csrfMagicToken;
}

/* ========= Event Back code ==============*/
function eventBack(){
    window.location.href = ' ../dashboard/deviceTypes.php' +"&csrfMagicToken=" + csrfMagicToken;
}

/* ===========Event search code=============*/
function eventsearchclick(){
   var searchstring = $('#sel_searchstring').val();
   var eventmon = $('#eventmonth').val();
   var evenday = $('#eventday').val();
   var evenyear = $('#eventyear').val();
   var evenhour = $('#eventhour').val();
   var evenmin  = $('#eventminute').val();
   
   var tomon = $('#Tomonth').val();
   var today = $('#Today').val();
   var toyear = $('#Toyear').val();
   var tohr = $('#Tohour').val();
   var tomin = $('#Tominute').val();
   var host = $('#eventhost').val();
   
   $("#eventGrid").dataTable().fnDestroy();
   var table1 = $('#eventGrid').DataTable({
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
            url: 'search.php?searchstring='+searchstring+'&eventmon='+eventmon+'&evenday='+evenday+'&evenyear='+evenyear+'&evenhour='+evenhour+'&evenmin='+evenmin+'&tomon='+tomon+'&today='+today+'&toyear='+toyear+'&tohr='+tohr+'&tomin='+tomin+'&host='+host+"&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        columns: [
            {"data": "description"},
            {"data": "text1"},
            {"data": "executable"},
            {"data": "clienttime"},
            {"data": "servertime"}
        ],
        columnDefs: [{
                targets: 3,
                orderable: false
            },
            {
                targets: 4,
                orderable: false
        },
//        { 
//            className: "dt-left", "targets": [ 0,1,2,3,4 ] 
//        },
            { className: "dt-left tdColumn1", "targets": 0 },
            { className: "dt-left tdColumn2", "targets": 1 },
            { className: "dt-left tdColumn3", "targets": 2 },
            { className: "dt-left tdColumn4", "targets": 3 },
            { className: "dt-left tdColumn5", "targets": 4 }
    ],
        searching: false,
        ordering: true,
        select: false,
        bLengthChange: false,
        bInfo: false,
        responsive: true,
        pagingType: "full_numbers",
        dom: '<"top"i>rt<"bottomtable"flp><"clear">',
    });
    $('#eventGrid tbody').on( 'mouseover', 'td', function () {
            var rowID = table1.row(this).data();
            $("#eventGrid tbody tr td").eq(0).attr("data-target","tooltip");
            $("#eventGrid tbody tr td").eq(1).attr("data-target","tooltip");
            $("#eventGrid tbody tr td").eq(2).attr("data-target","tooltip");
            $("#eventGrid tbody tr td").eq(3).attr("data-target","tooltip");
            $("#eventGrid tbody tr td").eq(4).attr("data-target","tooltip");
            $("td:nth-child(1)").attr("title",""+rowID.description);
            $("td:nth-child(2)").attr("title",""+rowID.text1);
            $("td:nth-child(3)").attr("title",""+rowID.executable);
            $("td:nth-child(4)").attr("title",""+rowID.clienttime);
            $("td:nth-child(5)").attr("title",""+rowID.servertime);
    });
   
//   $.ajax({
//       url:'search.php?queryid='+id+'&eventmon='+eventmon+'&evenday='+evenday+'&evenyear='+evenyear+'&evenhour='+evenhour+'&evenmin='+evenmin+'&tomon='+tomon+'&today='+today+'&toyear='+toyear+'&tohr='+tohr+'&tomin='+tomin+'&host='+host,
//       type:'post'
//   })
}