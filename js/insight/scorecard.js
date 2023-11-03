$(function (){
   get_scorecardGrid();
})

/* scorecard grid data */
function get_scorecardGrid() {
    $.ajax({
        url: "scorefunction.php?function=get_viewscoreData",
        type: "POST",
        dataType: "json",
        data: { 'csrfMagicToken': csrfMagicToken },
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#scorecardList').DataTable().destroy();
            scoreTable = $('#scorecardList').DataTable({
                scrollY: jQuery('#scorecardList').data('height'),
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
    
    $('#scorecardList').on('click', 'tr', function () {

        var rowID = scoreTable.row(this).data();
        var selected = rowID[4];
        $('#selected').val(selected);

        scoreTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });
    
    $("#score_searchbox").keyup(function () {//group search code
        scoreTable.search(this.value).draw();
    });
}

/* scorecard all function call */
function selectConfirm(data_target_id) {
    var id = $('#selected').val();
    
    if (data_target_id == 'add_score_card') {
        $('#add-score').modal('show');
        addscoreOption();        
    } else if (data_target_id == 'edit_score_card') {
        
        if (id == '') {
            
           $('#warningemptygroup').modal('show'); 
        } else {
        
           editscoreCard(id);
        }
    } else if (data_target_id == 'delete_score_card') {
        if (id == '') {
            
            $('#warningemptygroup').modal('show'); 
        } else {
        
            $('#delete-score-detail').modal('show'); 
        }
    }
}

/* Add score function */
function addscoreCard() {
    var scrname = $('#scorename').val();
    var filtrid = $('#filtername').val();
    var status   = $('#status').val();
    var dart     = $('#dartvalue').val();

    if (scrname == '') {
        $('#successmsg').html('');
        $('#successmsg').html('<span style="color:red;"> Please Enter Score Name </span>');
        return false;
    }
    
    if (filtrid == '') {
        $('#successmsg').html('');
        $('#successmsg').html('<span style="color:red;"> Please Select Filter </span>');
        return false;
    }
    if (status == '') {
        $('#successmsg').html('');
        $('#successmsg').html('<span style="color:red;"> Please Select Status </span>');
        return false;
    }
    
    if (dart == '') {
        $('#successmsg').html('');
        $('#successmsg').html('<span style="color:red;"> Please Enter Dart Number </span>');
        return false;
    }
    
    $("#loadingCSVAdd").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>'); 
    $.ajax({
        url:'scorefunction.php?function=get_addscoreSubmit',
        type:'post',
        data:'scrname='+scrname+'&filtrid='+filtrid+'&status='+status+'&dart='+dart,
        dataType:'json',        
        success:function(data) {
            if (data.msg == 'success') {
                $('#loadingCSVAdd').hide();
                $('#successmsg').html('');
                $('#successmsg').html('<span style="color:green;">success </span>');
                
                setTimeout(function () {
                    $("#add-score").modal("hide");
                    location.href = 'scorecard.php';
                }, 3200);
                
            } else if (data.msg == 'invalid') {
                
                $('#loadingCSVAdd').hide();
                $('#successmsg').html('');
                $('#successmsg').html('<span style="color:red;"> name is present </span>');
                
            } else {
                $('#loadingCSVAdd').hide();
                $('#successmsg').html('');
                $('#successmsg').html('<span style="color:red;">failed </span>');
                
                setTimeout(function () {
                    $("#add-score").modal("hide");
                    location.href = 'scorecard.php';
                }, 3200);
            }
                        
        },
        error:function(data) {
//            alert('error');
        }
    })    
}

/* event filter list in add pop-up function */
function addscoreOption() {
    
    $.ajax({
        url:'scorefunction.php?function=get_addoptionSubmit',
        type:'post',
        dataType:'json',
        success:function(data) {            
            $('#filtername').html(data);
            $('.selectpicker').selectpicker('refresh');
        }        
    })    
}

/* edit score card popup function */
function editscoreCard(id) {
    
    $.ajax({
        url:'scorefunction.php?function=get_editscoreData',
        type:'post',
        data:'id='+id,
        dataType:'json',
        success:function(data) {            
            $('#edit-Score').modal('show');
            $('#editscorename').val(data.name);            
            $('#editstatus').val(data.status); 
            $('#editdartvalues').val(data.dart);
            $('#editfiltername').html(data.filtername);
            $('.selectpicker').selectpicker('refresh');            
        }
    })    
}

/* score card edited submit function */
function editscoreSubmit() {
    
    var id = $('#selected').val();
    var scrname = $('#editscorename').val();
    var filtrid = $('#editfiltername').val();
    var status  = $('#editstatus').val();
    var dart    = $('#editdartvalues').val(); 

    if (scrname == '') {
        $('#successmsgedit').html('');
        $('#successmsgedit').html('<span style="color:red;"> Please Enter Score Name </span>');
        return false;
    }
    
    if (filtrid == '') {
        $('#successmsgedit').html('');
        $('#successmsgedit').html('<span style="color:red;"> Please Select Filter </span>');
        return false;
    }
    if (status == '') {
        $('#successmsgedit').html('');
        $('#successmsgedit').html('<span style="color:red;"> Please Select Status </span>');
        return false;
    }
    
    if (dart == '') {
        $('#successmsgedit').html('');
        $('#successmsgedit').html('<span style="color:red;"> Please Enter Dart Number </span>');
        return false;
    }
    
    $("#loadingCSVAdd").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>'); 
    $.ajax({
        url:'scorefunction.php?function=get_editscoresubmit',
        type:'post',
        data:'scrname='+scrname+'&filtrid='+filtrid+'&status='+status+'&id='+id+'&dart='+dart,
        dataType:'json',        
        success:function(data) {
            if (data.msg == 'success') {
                $('#loadingCSVAdd').hide();
                $('#successmsgedit').html('');
                $('#successmsgedit').html('<span style="color:green;">success </span>');
                
            } else {
                $('#loadingCSVAdd').hide();
                $('#successmsgedit').html('');
                $('#successmsgedit').html('<span style="color:red;">failed </span>');
            }
            
            setTimeout(function () {
                $("#edit-score").modal("hide");
                location.href = 'scorecard.php';
            }, 3200);
        },
        error:function(data) {
//            alert('error');
        }
    }) 
}

/* delete score card function */
function deletescoreList() {
    var id = $('#selected').val();
    
    $.ajax({
        url:"scorefunction.php?function=get_deleteScore",
        type:'post',
        data:'id='+id,
        dataType:'json',
        success:function(data) {
            if (data.msg == 'success') { 
                $('#delete-score-detail').modal('hide'); 
                get_scorecardGrid();
            }
        }        
    })
}