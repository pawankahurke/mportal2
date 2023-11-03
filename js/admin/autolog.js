$(document).ready(function() {
    $('.UserSelection').show();
    if ($('#NotifSel').is(':checked')) {
        getLogDetails('notif');
    }else{
        getLogDetails('trbl');
    }
    //$("#loader").show();
    $('#absoLoader').show();
    $.ajax({
        url: "../lib/l-ajax.php",
        type: "POST",
        data:{'function':'AJAX_get_UserDartDetails', 'csrfMagicToken': csrfMagicToken},
        success:function(data){
            $('#UserSelection').html(data);
            $(".selectpicker").selectpicker("refresh");
            $('[data-toggle=dropdown]').each(function(){
                $(this).removeAttr('data-toggle').attr('data-bs-toggle','dropdown');
            });
            $(".loader").hide();
        },
        error:function(error){
            console.log("error");
            $(".loader").hide();
        }
    });
});

$('#NotifSel').on('click',function(){
    getLogDetails('notif');
});

$('#TroublSel').on('click',function(){
    getLogDetails('trbl');
});

$('#pushSolSel').on('click',function(){
    getLogDetails('solution');
});

$('#distributionSel').on('click',function(){
    getLogDetails('distribution');
});

$('body').on('click', '.page-link', function () {
    var nextPage = $(this).data('pgno');
    notifName = $(this).data('name');
   //  if ($('#NotifSel').is(':checked')) {
   //      type='notif';
   // }else{
   //     type='trbl';
   // }

  const activeElement = window.currentActiveSortElement;
  const key = (activeElement) ? activeElement.sort : '';
  const sort = (activeElement) ? activeElement.type : '';
  let type = '';
    if($('#NotifSel').is(':checked')){
        type = 'notif';
    }else if($('#TroublSel').is(':checked')){
        type = 'trbl';
    }else if($('#pushSolSel').is(':checked')){
        type = 'solution';
    }else if($('#distributionSel').is(':checked')){
        type = 'distribution';
    }

  getLogDetails(type, nextPage, '', key, sort);
  // getLogDetails(type,nextPage);
})
$('body').on('change', '#notifyDtl_lengthSel', function () {
    // if ($('#NotifSel').is(':checked')) {
    //     type='notif';
    // }else{
    //    type='trbl';
    // }
    var type = '';
    if($('#NotifSel').is(':checked')){
        type = 'notif';
    }else if($('#TroublSel').is(':checked')){
        type = 'trbl';
    }else if($('#pushSolSel').is(':checked')){
        type = 'solution';
    }else if($('#distributionSel').is(':checked')){
        type = 'distribution';
    }
    getLogDetails(type,1);
});

function getLogDetails(type = '', nextPage= 1, notifSearch = '', key = '', sort = '') {
    //$('#loader').show();
    $('#absoLoader').show();

    if(type == ''){
        if($('#NotifSel').is(':checked')){
            type = 'notif';
        }else if($('#TroublSel').is(':checked')){
            type = 'trbl';
        }else if($('#pushSolSel').is(':checked')){
            type = 'solution';
        }else if($('#distributionSel').is(':checked')){
            type = 'distribution';
        }
    }

    checkAndUpdateActiveSortElement(key, sort);

    notifSearch = $('#notifSearch').val();

    var dat = {
        'type': type,
        'csrfMagicToken': csrfMagicToken,
        'limitCount': $('#notifyDtl_length :selected').val(),
        'nextPage': nextPage,
        'notifSearch': notifSearch,
        'order' : key,
        'sort' :sort,
    };
    var gridData = {};
    $.ajax({
        url: "../autolog/autoLogfunction.php",
        type: "POST",
        dataType: "json",
        data: dat,
        success: function (gridData) {

            $(".se-pre-con").hide();
            $('#auditlog_datatable').DataTable().destroy();
            $('#auditlog_datatable tbody').empty();
            auditlogTable = $('#auditlog_datatable').DataTable({
                scrollY: 'calc(100vh - 240px)',
                scrollCollapse: true,
                paging: false,
                searching: false,
                bFilter: false,
                ordering: false,
                aaData: gridData.html,
                bAutoWidth: true,
                select: false,
                bInfo: false,
                responsive: false,
                stateSave: true,
                processing: true,
                "pagingType": "full_numbers",
                "stateSaveParams": function (settings, data) {
                    data.search.search = "";
                },
                order: [[2, "asc"]],
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                //                "lengthChange": false,
                "language": {
                    "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
                    search: "_INPUT_",
                    searchPlaceholder: "Search records"
                },
                "columnDefs": [
                    {
                        "targets": 0,
                        "orderable": false
                    }
                ],
                "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
                initComplete: function (settings, json) {
                    $('.equalHeight').show();
                    $('#absoLoader').hide();
                    //$('#loader').hide();
                    $("th").removeClass('sorting_desc');
                    $("th").removeClass('sorting_asc');
                    $(".closebtn").trigger("click");
                    if(type == 'notif'){
                        $('#SiteName').html("Notification Name");
                    }else{
                        $('#SiteName').html("GROUP/Site Name");
                    }

                    $('.dataTables_scrollHead, .dataTables_scrollBody').css('overflow','unset');
                    $('.dataTables_scroll').addClass('table-responsive');
                },
                "drawCallback": function (settings) {
                    $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
                }
            });
            $('.dataTables_filter input').addClass('form-control');
            $('.tableloader').hide();
        },complete: (function(){
            $('#auditlog_datatable tbody td').each(function() {
                var $cell = $(this);
                var cellContent = $cell.text();
            
                if(cellContent.length > 30){
                    $cell.attr('data-bs-title', cellContent);
                    $cell.attr('title', cellContent);
                    $cell.attr('data-bs-toggle', 'tooltip');
                }
            });
            $('[data-bs-toggle="tooltip"]').tooltip();
        })
    });

    


    $('#auditlog_datatable').on('click', 'tr', function() {
        var rowID = auditlogTable.row(this).data();
        var selected = rowID.emailId;

        $("#selected").val(selected);
        auditlogTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');

    });


}

function checkLevelType(param){
    if(param == 'User'){
        $('.CustomerSelection').hide();
        $('.UserSelection').show();
        $.ajax({
           url: "../lib/l-ajax.php?function=AJAX_getUserDetails&csrfMagicToken=" + csrfMagicToken,
           type: "post",
           success:function(data){
               $('#UserSelection').html(data);
               $(".selectpicker").selectpicker("refresh");
           },
           error:function(error){
               console.log("error");
           }
        });
    }else{
        $('.UserSelection').hide();
        $('.CustomerSelection').show();
        $.ajax({
           url: "../lib/l-ajax.php?function=AJAX_getCustomerDetails&csrfMagicToken=" + csrfMagicToken,
           type: "post",
           success:function(data){
               $('#CustomerSelection').html(data);
               $(".selectpicker").selectpicker("refresh");
           },
           error:function(error){
               console.log("error");
           }
        });
    }
}

function exportAuditLog() {
    var fromDate = $('#datefrom').val();
    var toDate   = $('#dateto').val();
    var type = 'User';
    var selectedType = $('#SelectionType').val();
    if(type == 'Customer'){
        var sublistval = $('#CustomerSelection').val();
    }else{
        var sublistval = $('#UserSelection').val();
    }
    $('#errorMsg').html('');
    $('#datefrom_err').html('');
    $('#dateto_err').html('');
    $('#errorMsg').show();

    if(fromDate==''||toDate=='') {
        if (fromDate == '') {
           $.notify("Please select start date");
           return false;
       }
       if (toDate == '' ) {
           $.notify("Please select end date");
           return false;
       }
    } else {
        var start = (new Date(fromDate).getTime());
        var to = (new Date(toDate).getTime());

        if(start >= to ) {
            $.notify("Start Date should be less than end date");
            return false;
        } else {
            window.location.href = "../autolog/autoLogExport.php?from="+fromDate+"&to="+toDate+"&type="+type+"&sublist="+sublistval+"&selectedType="+selectedType;

            $.notify('Audit Details Exported Successfully');
            closePopUp();
            $("#auditlog-range").modal("hide");
        }
    }
}


$('.closebtn').click(function(){
    $('#levelId').val('');
});

function validatePopup() {

    var fromDate = $('#datefrom').val();
    var toDate   = $('#dateto').val();
    var level = $('#levelId').val();
    $('#errorMsg').html('');
    $('#errorMsg').html('');
    $('#datefrom_err').html('');
    $('#dateto_err').html('');
    $('#errorMsg').show();


    if (fromDate == '') {
        $.notify("Please select start date");
        return false;
    }
    if (toDate == '' ) {
        $.notify("Please select end date");
        return false;
    }

    var start = (new Date(fromDate).getTime());
    var to = (new Date(toDate).getTime());
    if(start >= to ) {
        $.notify("Start Date should be less than end date");
        return false;
    } else {
        return true;
    }

}

$('#login-range').on('hidden.bs.modal', function() {

    $('.form-group input').val('');
    $('.form-group select').val('');
    $(".selectpicker").selectpicker("refresh");
});

$('#logDetails').on('shown.bs.modal', function (e) {
        $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

function exportdetails(){
    $("#login-range").show();
}
