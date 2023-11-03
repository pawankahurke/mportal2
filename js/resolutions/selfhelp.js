$(document).ready(function() {
    Selfhelp_datalist();
})

/* ******************* GROUP NEW CODE ******************* */
/* =========== GROUP DETAIL LIST ============ */
function Selfhelp_datalist() {
    $.ajax({
        url: "resolutionsAudit.php",
        data: "act=selfhelpdataList"+"&csrfMagicToken=" + csrfMagicToken,
        type: "GET",
        dataType: 'json',
        success: function(gridData) {
            $(".se-pre-con").hide();
            $('#eventTable').DataTable().destroy();
            eventTable = $('#eventTable').DataTable({
                scrollY: jQuery('#eventTable').data('height'),
                scrollCollapse: true,
                paging: true,
                searching: true,
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
//                order: [[0, "asc"]],
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "language": {
                    "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    searchPlaceholder: "Search"
                },
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function(settings, json) {
                    $('#eventTable tbody tr:eq(0)').addClass("selected");
                    
                    if($('#eventTable tbody tr:eq(0) p')[0] != 'undefined' && $('#eventTable tbody tr:eq(0) p')[0] !== undefined) {
                    var qid = $('#eventTable tbody tr:eq(0) p')[0].id;       
                    $("#selected").val(qid);
                    loadQueryData(qid);
                    } else {
                         loadQueryData("ioweurfvbhioebfviovivbrivbiervb")
                    }
                },
                drawCallback: function(settings) {
                    $(".dataTables_scrollBody").mCustomScrollbar({
                        theme: "minimal-dark"
                    });
                    $('.equalHeight').matchHeight();
                    $(".se-pre-con").hide();
                }
            });
            $("#eventdetail_searchbox").keyup(function() {
                eventTable.search(this.value).draw();
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

    $('#eventTable').on('click', 'tr', function() {
        var rowID = eventTable.row(this).data();
        //console.debug(rowID);
        var selected = rowID[1];
        $("#selected").val(selected);
        loadQueryData(selected);
        eventTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
        
    });
    /*
     * This function is for serching
     *  in event filter
     */

//    $("#eventdetail_searchbox").keyup(function() {
//        eventTable.search(this.value).draw();
//    });
    $("#eventdetail_searchbox").keyup(function() {
        eventTable.search(this.value).draw();
        eventTable.$('tr.selected').removeClass('selected');
        $('#eventTable tbody tr:eq(0)').addClass("selected");
        if($('#eventTable tbody tr:eq(0) p')[0] != 'undefined' && $('#eventTable tbody tr:eq(0) p')[0] !== undefined) {
            var qid = $('#eventTable tbody tr:eq(0) p')[0].id;       
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
        data:'act=get_selfhelpDetail&name='+name+"&csrfMagicToken=" + csrfMagicToken,        
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

function exportReport(){
    window.location.href = 'resolutionsAudit.php?act=get_selfhelpReport'+"&csrfMagicToken=" + csrfMagicToken;
}

function exportAll() {
    
    var schedule = '';
    var selfhelp = '';
    var predictive = '';
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
    
    if(schedule == '' && selfhelp == '' && predictive == '' && proactive == '') {
        $('#successmsg').html('Please select atleast one option to export');
        $('#successmsg').fadeOut(3000)
        return false;
    }
    
    window.location.href = 'resolutionsAudit.php?act=get_AllExport&proactive='+proactive+'&predictive='+predictives+'&schedule='+schedule+'&selfhelp='+selfhelp+"&csrfMagicToken=" + csrfMagicToken;
}