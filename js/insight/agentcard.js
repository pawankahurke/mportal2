$(function (){
   get_agentcardGrid();
})

/* scorecard grid data */
function get_agentcardGrid() {
    $.ajax({
        url: "scorefunction.php?function=get_viewagentData",
        type: "POST",
        dataType: "json",
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#agentcardList').DataTable().destroy();
            scoreTable = $('#agentcardList').DataTable({
                scrollY: jQuery('#agentcardList').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
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
                columnDefs: [{className: "checkbox-btn", "targets": [0]},                               
                    {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                initComplete: function (settings, json) {                    
                },
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }
            });
            $('.tableloader').hide();
        },
        error: function (msg) {

        }
    });  
    
    $('#agentcardList').on('click', 'tr', function () {

        var rowID = scoreTable.row(this).data();
        var selected = rowID[3];
        $('#selected').val(selected);

        scoreTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });
    
    $("#score_searchbox").keyup(function () {//group search code
        scoreTable.search(this.value).draw();
    });
}

function selectConfirm(data_target_id) {
    var id = $('#selected').val();
    
    if (data_target_id == 'add_agent_card') {
        $('#add-agent').modal('show');
        addagentaddsubmit();
        viewdetailpopupclicked();
    } else if (data_target_id == 'delete_agent_card') {
        
        if(id == '') {
            $('#warningemptygroup').modal('show');
        } else {
            $('#delete-agent-detail').modal('show');
        }      
    } else if (data_target_id == 'edit_agent_card') {
         if(id == '') {
            $('#warningemptygroup').modal('show');
        } else {
            $('#edit-agent').modal('show');
            editAgentValue();
        }    
    } 
}

function viewdetailpopupclicked() {
    setTimeout(function () {
        $(".event-info-grid-host").click();
    }, 300);
}

function addagentaddsubmit() {
    $.ajax({
        url: "scorefunction.php?function=get_addagentValue",
        type: "POST",
        dataType: "json",
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function (gridData) {
            $(".information-portal-popup .se-pre-con").hide();
            $('#agentaddDtl').DataTable().destroy();
            groupviewTable = $('#agentaddDtl').DataTable({
                scrollY: jQuery('#agentaddDtl').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
                ordering: true,
                autoWidth: false,
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
                columnDefs: [{className: "checkbox-btn", "targets": [0]}, {
                        targets: "datatable-nosort",
                        orderable: false
                    }],
                initComplete: function (settings, json) {                    
                },
                drawCallback: function (settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $(".information-portal-popup .se-pre-con").hide();
                    $('.bottom').hide();
                    $('#agentaddDtl_paginate').hide();
                }
            });
        },
        error: function (msg) {

        }
    });
}

function addagentCard(){
    var updateType = [];
    var percentage = 0;
    var percent = [];
    
    var checkedid = $('.check:checked').map(function() {
        return $(this).attr('value');
    }).get();
   
    $('.text .scorenametext').each(function() {       
       if ($(this).val() != '') {
            updateType.push($(this).val());
        }
    });
    updateType = updateType.toString();
    
    $('.percent .weightagetext').each(function() {       
        if ($(this).val() != '') {
            percentage += parseInt($(this).val());    
            percent.push($(this).val());
        }
    });   
    percent = percent.toString();
    
    if (percentage != 100 || percentage > 100) {
        $('#successmsg').html('');
        $('#successmsg').html('<span style="color:red;">Sum of Weightage should equal to 100% </span>');
        return false;
    }
     
    if (checkedid == '') {
        $('#successmsg').html('');
        $('#successmsg').html('<span style="color:red;">Please select checkbox </span>');
        return false;
    }
    
    if (updateType == '') {
        $('#successmsg').html('');
        $('#successmsg').html('<span style="color:red;">Please fill values </span>');
        return false;
    }
    
    $("#loadingCSVAdd").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>'); 
    $.ajax({
        url:'scorefunction.php?function=get_agentvalueSubmit',
        type:'post',
        data:'checkid='+checkedid+'&textvalue='+updateType+'&percent='+percent,
        dataType:'json',
        success:function(data) {
            if(data.msg == 'success') {
                $('#loadingCSVAdd').hide();
                $('#successmsg').html('');
                $('#successmsg').html('<span style="color:green;">success </span>');
                
                setTimeout(function () {
                    $("#add-score").modal("hide");
                    location.href = 'agentcard.php';
                }, 3200);
            }
            
        }
    })
    
}

function deleteagentList() {
    var id = $('#selected').val();
    
    $.ajax({
        url:'scorefunction.php?function=get_agentDelete',
        type:'post',
        data:'id='+id, 
        dataType:'json',
        success:function(data) {
            if (data == 'success') {
                $('#delete-agent-detail').modal('hide');
                get_agentcardGrid();
            }
        }
    }) 
}

function editAgentValue() {
    
    $.ajax({
        url:'scorefunction.php?function=get_editagentValue',
        type:'post',
        dataType:'json',
        success:function(data) {
            
        }        
    })
    
}