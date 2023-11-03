auditGrid();

function auditGrid(){
    var h = window.innerHeight;
        if (h > 700) {
            $("#Statisticsmaingrid").attr("data-page-length", "10");
        }
        else {
            $("#Statisticsmaingrid").attr("data-page-length", "8");
        }
    $("#proactiveauditGrid").dataTable().fnDestroy();
    $('#auditContent').show();
    $('#scheduleContent').hide();
//    $('#displayMachine').hide();
    var groupTable = $('#proactiveauditGrid').DataTable({
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
            url: "auditGrid.php&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        columns: [
            {"data": "servicetag"},
            {"data": "username"},
            {"data": "profileName"},
            {"data": "createdtime"},
            {"data": "proof"}
        ],
      columnDefs: [
            {
              targets: 4,
              orderable: false
            },
            { className: "dt-left tdColumn1", "targets": 0 },
            { className: "dt-left tdColumn2", "targets": 1 },
            { className: "dt-left tdColumn3", "targets": 2 },
            { className: "dt-left tdColumn4", "targets": 3 },
            { className: "dt-left tdColumn5", "targets": 4 }
        ]
    });
    $('#proactiveauditGrid tbody').on( 'mouseover', 'td', function () {
            var rowID = groupTable.row(this).data();
            $("#proactiveauditGrid tbody tr td").eq(0).attr("data-target","tooltip");
            $("#proactiveauditGrid tbody tr td").eq(1).attr("data-target","tooltip");
            $("#proactiveauditGrid tbody tr td").eq(2).attr("data-target","tooltip");
            $("#proactiveauditGrid tbody tr td").eq(3).attr("data-target","tooltip");
            $("td:nth-child(1)").attr("title",""+rowID.servicetag);
            $("td:nth-child(2)").attr("title",""+rowID.username);
            $("td:nth-child(3)").attr("title",""+rowID.profileName);
            $("td:nth-child(4)").attr("title",""+rowID.packageName);
    });
    $("#proactive_searchbox").keyup(function() {
        groupTable.search(this.value).draw();
        });
        $('#proactiveauditGrid_filter').hide();
    $('#proactiveauditGrid_length').hide();
    $('#proactiveauditGrid tbody').on('click', 'tr', function () { //row selection code
        if ($(this).hasClass('selected')) {
            var rowdata = groupTable.row(this).data();
            $(this).removeClass('selected');
        } else {
            var rowdata = groupTable.row(this).data();
//            $('#versionid').val(rowdata.id);
            groupTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });  
}

function scheduleData(){
    var h = window.innerHeight;
        if (h > 700) {
            $("#proactivescheduleGrid").attr("data-page-length", "10");
        }
        else {
            $("#proactivescheduleGrid").attr("data-page-length", "8");
        }
    $("#proactivescheduleGrid").dataTable().fnDestroy();
    $('#auditContent').hide();
    $('#scheduleContent').show();
    var groupTable = $('#proactivescheduleGrid').DataTable({
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
            url: "scheduleGrid.php&csrfMagicToken=" + csrfMagicToken,
            type: "POST"
        },
        columns: [
            {"data": "check"},
            {"data": "servicetag"},
            {"data": "username"},
            {"data": "profileName"},
            {"data": "createdtime"},
            {"data": "proof"}
        ],
        "columnDefs": [
            {
              targets: 0,
              orderable: false
            },
            {
              targets: 5,
              orderable: false
            },
            { className: "dt-left", "targets": 0 },
            { className: "dt-left tdColumn1", "targets": 1 },
            { className: "dt-left tdColumn2", "targets": 2 },
            { className: "dt-left tdColumn3", "targets": 3 },
            { className: "dt-left tdColumn4", "targets": 4 },
            { className: "dt-left tdColumn5", "targets": 5 }
          ]
    });
    $('#proactivescheduleGrid tbody').on( 'mouseover', 'td', function () {
            var rowID = groupTable.row(this).data();
            $("#proactivescheduleGrid tbody tr td").eq(1).attr("data-target","tooltip");
            $("#proactivescheduleGrid tbody tr td").eq(2).attr("data-target","tooltip");
            $("#proactivescheduleGrid tbody tr td").eq(3).attr("data-target","tooltip");
            $("#proactivescheduleGrid tbody tr td").eq(4).attr("data-target","tooltip");
            $("td:nth-child(2)").attr("title",""+rowID.servicetag);
            $("td:nth-child(3)").attr("title",""+rowID.username);
            $("td:nth-child(4)").attr("title",""+rowID.profileName);
            $("td:nth-child(5)").attr("title",""+rowID.createdtime);
    });
    $('#proactivescheduleGrid_filter').hide();
    $('#proactivescheduleGrid tbody').on('click', 'tr', function () { //row selection code
        if ($(this).hasClass('selected')) {
            var rowdata = groupTable.row(this).data();
            $(this).removeClass('selected');
        } else {
            var rowdata = groupTable.row(this).data();
            $('#machineid').val(rowdata.id);
            groupTable.$('tr.selected').removeClass('selected');
            $(this).addClass('selected');
        }
    });
}

function auditExcel(){
    window.location.href = "auditdataExcel.php?function=auditExcel";
}

function scheduleEcxel(){
    window.location.href = "scheduledataExcel.php?function=scheduleExcel";
}

function deleteSchedule(){
    var checkedValues = $('.check:checked').map(function () {
        return $(this).attr('id');
    }).get();
    if(checkedValues ==  ''){
        $("#warningdelete").modal("show"); 
    }else{
        $.ajax({
            url:'scheduleGriddelete.php?checkval='+checkedValues + "&csrfMagicToken=" + csrfMagicToken,
            type:'post',
            success:function(data){
               scheduleData(); 
            }
        });  
    }
}

$(function(){
    var some = $("#temp").val();
    if(some == "Service Tag"){
        $("#predictive").attr('href','predictive.php');
    } else{
       $("#predictive").attr('href','predictive.php?cat=5&type=1');
    }
});

function schedulepredictive(){
    window.location.href = '../support_action/predictive.php';
}

function NoceventDetailsForDartStatA(stat, tid, eventList){
    $("#rightNavtiles").css({'display': 'none'});
    if(tid != ''){
        $.ajax({
            url:'event_detailForDartStatAudit.php?stat='+stat+'&eid='+tid+'&eventList='+eventList + "&csrfMagicToken=" + csrfMagicToken,
            type:'post',
            dataType:'json',
            success:function(data){
//                alert(data.eventuser);
                $('input[type=text]').prev().parent().removeClass('is-empty');
                $("#executed").val(data.username);
                $("#servertime").val(data.servertime);
                $("#agenttime").val(data.servertime);
                $("#nodetime").val(data.nodetrigger);
                $("#clienttime").val(data.clienttime);
                
                $("#eventuser").val(data.eventuser);
                $('#eventserver').val(data.eventserver);
                $('#eventcustomer').val(data.eventcustomer);
                $('#eventuuid').val(data.eventuuid);
                $('#eventversion').val(data.eventversion);
                $('#eventdescription').val(data.eventdescription);
                $('#eventsize').val(data.eventsize);
                $('#eventid').val(data.eventid);
                $('#eventstring2').val(data.eventstring2);
                
                $('#eventclient').val(data.eventclient);
                $('#eventscrip').val(data.eventscrip);
                $('#eventmachine').val(data.eventmachine);
                $('#eventusername').val(data.eventusername);
                $('#eventpriority').val(data.eventpriority);
                $('#eventtype').val(data.eventtype);
                $('#eventversion').val(data.eventversion);
                $('#eventid2').val(data.eventid2);
                $('#eventstring1').val(data.eventstring1);
                
                $('#eventpath').val(data.eventpath);
                $('#eventtext2').val(data.eventtext2);
                $('#eventtext3').val(data.eventtext3);
                $('#eventtext4').val(data.eventtext4);
            }
        });
    }
}

$(function() {
    $("#ascrail2008-hr").removeAttr('style');
    $("#ascrail2008-hr div").removeAttr('style');
});

function checkBoxschedule(e, obj) {
    e = e || event;/* get IE event ( not passed ) */
    e.stopPropagation ? e.stopPropagation() : e.cancelBubble = true;
    //$('.commonClass').prop('checked', obj.checked);
    $('.check').prop('checked', obj.checked);
}