$(function(){
    $("#resolution_searchbox").keyup(function() {
       var count = $('#proactiveauditGrid tr').length;
        if(count == 2){
            loadQueryData("ioweurfvbhioebfviovivbrivbiervb");
        } 
    });
});

function Get_ResolutionsAuditGrid_old(){
    $("#se-pre-con-loader").show();
//    var search = $("#searchValue").val();
//    $("#replaceSiteName").text("Resolutions : "+search);
    $("#proactiveauditGrid").dataTable().fnDestroy();
    $('#auditContent').show();
    $('#scheduleContent').hide();
//    $('#displayMachine').hide();
    var groupTable = $('#proactiveauditGrid').DataTable({
        scrollY: jQuery('#proactiveauditGrid').data('height'),
        scrollCollapse: true,
        autoWidth: false,
        searching: true,
        processing: true,
        serverSide: true,
        bAutoWidth: true,
        ordering: true,
        select: false,
        bInfo:false,
        responsive: true,
        stateSave: true,
        "stateSaveParams": function (settings, data) {
            data.search.search = "";
        },
        order: [[3, "desc"]],
        ajax: {
            url: "resolutionsAudit.php?act=getProactiveData",
            type: "POST"
        },
        "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "language": {
            "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
            searchPlaceholder: "Search"
        },
        "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        columns: [
            {"data": "MachineTag"},
            {"data": "AgentName"},
//            {"data": "JobType"},
            {"data": "ProfileName"},
            {"data": "JobCreatedTime"},
//            {"data": "MachineOs"},
            {"data": "JobStatus"}
        ],
        columnDefs: [
            {className: "checkbox-btn ignore", "targets": [0]}, 
            {className: "ignore", "targets": [1]},
            {className: "ignore", "targets": [2]},
            {className: "ignore", "targets": [3]}
        ],
        drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    
                    $(".user-page").show();

//                    $('.equalHeight').matchHeight();
                    
                    $("#se-pre-con-loader").hide();
                }
      
    });
//    $('#proactiveauditGrid tbody').on( 'mouseover', 'td', function () {
//            var rowID = groupTable.row(this).data();
//            $("#proactiveauditGrid tbody tr td").eq(0).attr("data-target","tooltip");
//            $("#proactiveauditGrid tbody tr td").eq(1).attr("data-target","tooltip");
//            $("#proactiveauditGrid tbody tr td").eq(2).attr("data-target","tooltip");
//            $("#proactiveauditGrid tbody tr td").eq(3).attr("data-target","tooltip");
//            $("td:nth-child(1)").attr("title",""+rowID.servicetag);
//            $("td:nth-child(2)").attr("title",""+rowID.username);
//            $("td:nth-child(3)").attr("title",""+rowID.profileName);
//            $("td:nth-child(4)").attr("title",""+rowID.packageName);
//    });
    $("#resolution_searchbox").keyup(function() {
        groupTable.search(this.value).draw();
        });
        $('#proactiveauditGrid_filter').hide();
//    $('#proactiveauditGrid_length').hide();
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


function Get_ResolutionsAuditGrid() {
    
    $.ajax({
        url: "resolutionsAudit.php",
        data: "act=getProactiveData"+"&csrfMagicToken=" + csrfMagicToken,
        type: "GET",
        dataType: 'json',
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#proactiveauditGrid').DataTable().destroy();
            eventTable = $('#proactiveauditGrid').DataTable({
                scrollY: jQuery('#proactiveauditGrid').data('height'),
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
//                order: [[0, "asc"]],
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
        },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function(settings, json) {
                    $('#proactiveauditGrid tbody tr:eq(0)').addClass("selected");
                    
                    if($('#proactiveauditGrid tbody tr:eq(0) p')[0] != 'undefined' && $('#proactiveauditGrid tbody tr:eq(0) p')[0] !== undefined) {
                    var qid = $('#proactiveauditGrid tbody tr:eq(0) p')[0].id;       
                    $("#selected").val(qid);
                         loadQueryData(qid);
                    } else {
                         loadQueryData("ioweurfvbhioebfviovivbrivbiervb")
                    }
                    /*var qid = $('#eventTable tbody tr:eq(0) p')[0].id;       
                    $("#selected").val(qid);
                    if(qid){
                        loadQueryData(qid);
                    } else {
                        loadQueryData("ioweurfvbhioebfviovivbrivbiervb");
                    }*/
            },
                drawCallback: function(settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }
            });
            $("#resolution_searchbox").keyup(function() {
                eventTable.search(this.value).draw();
                eventTable.$('tr.selected').removeClass('selected');
                $('#eventTable tbody tr:eq(0)').addClass("selected");
                var qid = $('#proactiveauditGrid tbody tr:eq(0) p')[0].id;       
                $("#selected").val(qid);
                if(qid){
                    loadQueryData(qid);
                } else {
                    loadQueryData("ioweurfvbhioebfviovivbrivbiervb");
                }
            });
            $('#proactiveauditGrid_filter').hide();
            },
        error: function(msg) {

        }
    });
     /*
     * This function is for selecting
     *  row in event filter
     */

    $('#proactiveauditGrid').on('click', 'tr', function() {
        var rowID = eventTable.row(this).data();
        //console.debug(rowID);
        var selected = rowID[1];
        $("#selected").val(selected);
        loadQueryData(selected);
        eventTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        
    });
    
    $("#resolution_searchbox").keyup(function() {
        eventTable.search(this.value).draw();
        eventTable.$('tr.selected').removeClass('selected');
        $('#eventTable tbody tr:eq(0)').addClass("selected");
        if($('#proactiveauditGrid tbody tr:eq(0) p')[0] != 'undefined' && $('#proactiveauditGrid tbody tr:eq(0) p')[0] !== undefined) {
        var qid = $('#proactiveauditGrid tbody tr:eq(0) p')[0].id;       
        $("#selected").val(qid);
            loadQueryData(qid);
        } else {
             loadQueryData("ioweurfvbhioebfviovivbrivbiervb")
        }
    });
}

function loadQueryData(name) {            
    $.ajax({
        url:'resolutionsAudit.php',
        type:'post',
        data:'act=get_ProactiveDetail&name='+name +"&csrfMagicToken=" + csrfMagicToken,        
        dataType:'json',
        success: function(gridData) {                                   
            $('#RightTableData').DataTable().destroy();
            RightTable = $('#RightTableData').DataTable({
                scrollY: jQuery('#RightTableData').data('height'),
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
                drawCallback: function(settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
}
            });            
        },
        error: function(msg) {

        }
    });      
}


function auditExcel(){
    window.location.href = "resolutionsAudit.php?act=resolutionsAuditExcel"+"&csrfMagicToken=" + csrfMagicToken;
}

//function scheduleEcxel(){
//    window.location.href = "resolutionsAudit.php?act=scheduleExcel";
//}

function deleteSchedule(){
    var checkedValues = $('.check:checked').map(function () {
        return $(this).attr('id');
    }).get();
    if(checkedValues ==  ''){
        $("#warningdelete").modal("show"); 
    }else{
        $.ajax({
            url:'scheduleGriddelete.php?checkval='+checkedValues +"&csrfMagicToken=" + csrfMagicToken,
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
        $(".predictive").attr('href','predictive.php'+"&csrfMagicToken=" + csrfMagicToken);
    } else{
       $(".predictive").attr('href','predictive.php?cat=5&type=1'+"&csrfMagicToken=" + csrfMagicToken);
    }
});

function schedulepredictive(){
    window.location.href = '../resolutions/predictive.php'+"&csrfMagicToken=" + csrfMagicToken;
}

function AuditDetailStatusFn(stat, tid, eventList) {

    $("#rightNavtiles").css({'display': 'none'});

    if (tid != '') {

        $.ajax({
            url: '../softdist/SWD_Function.php?function=AuditDetailStatusFn&stat=' + stat + '&eid=' + tid + '&eventList=' + eventList +"&csrfMagicToken=" + csrfMagicToken,
            type: 'post',
            dataType: 'json',
            success: function(data) {

                $("#eventDetails").modal("show");
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
                $('#eventtext1').val(data.eventtext1).attr('title',data.eventtext1);
                $('#eventtext2').val(data.eventtext2).attr('title',data.eventtext2);
                $('#eventtext3').val(data.eventtext3).attr('title',data.eventtext3);
                $('#eventtext4').val(data.eventtext4).attr('title',data.eventtext4);

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

function exportAll() {
    
    var schedule = '';
    var selfhelp = '';
    var predictives = '';
    var proactive = '';
     $('#successmsg').show();
    $('#successmsg').html('');
    
    if($('#proactive').is(':checked')) {
        proactive = 1;
    }
    if($('#predictive').is(':checked')) {
        predictives = 1;
    }
    if($('#schedule').is(':checked')) {
        schedule = 1;
    }
    if($('#selfhelp').is(':checked')) {
        selfhelp = 1;
    }
    if(schedule == '' && selfhelp == '' && predictives == '' && proactive == '') {
        $('#successmsg').html('Please select atleast one option to export');
        $('#successmsg').fadeOut(3000)
        return false;
    }
    
    if(ResolAPI === 1 || ResolAPI === '1') {
        window.location.href = 'resolutionsAudit_EL.php?act=get_AllExport_new_EL&proactive=' + proactive + '&predictive=' + predictives + '&schedule=' + schedule + '&selfhelp=' + selfhelp +"&csrfMagicToken=" + csrfMagicToken;
    } else {
        window.location.href = 'resolutionsAudit.php?act=get_AllExport&proactive='+proactive+'&predictive='+predictives+'&schedule='+schedule+'&selfhelp='+selfhelp +"&csrfMagicToken=" + csrfMagicToken;
    }
}

$("#exportAll").on('hidden.bs.modal', function() {
    $('.check').prop("checked", false);
});
