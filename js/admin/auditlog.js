$(document).ready(function () {
  getLogDetails();
  $('.UserSelection').show();
  $.ajax({
    url: "../lib/l-ajax.php",
    type: "POST",
    data: {'function': 'AJAX_get_UserDartDetails', 'csrfMagicToken': csrfMagicToken},
    success: function (data) {
      $('#UserSelection').html(data);
      $(".selectpicker").selectpicker("refresh");
      $(".loader").hide();
    },
    error: function (error) {
      console.log("error");
      $(".loader").hide();
    }
  });
});


$('body').on('click', '.page-link', function () {
    var nextPage = $(this).data('pgno');

  const activeElement = window.currentActiveSortElement;
  const key = (activeElement) ? activeElement.sort : '';
  const sort = (activeElement) ? activeElement.type : '';
  getLogDetails(nextPage, '', key, sort);
})
$('body').on('change', '#notifyDtl_lengthSel', function () {
    getLogDetails(1);
});

// $(document).on('keypress', function (e) {
//     if (e.which == 13) {
//         var notifSearch = $('#notifSearch').val();
//         if (notifSearch != ''){
//             getLogDetails(1);
//         }else{
//             getLogDetails(1);
//         }
//     }
// });

function getLogDetails(nextPage = 1, notifSearch = '', key = '', sort = '') {
    notifSearch = $('#notifSearch').val();
    if (typeof notifSearch === 'undefined') {
        notifSearch = '';
    }
    $("#loader").show();

    checkAndUpdateActiveSortElement(key, sort);

    var dat = {
        "function": 'LOG_getLogDetails',
        'csrfMagicToken': csrfMagicToken,
        'limitCount': $('#notifyDtl_length :selected').val(),
        'nextPage': nextPage,
        'notifSearch': notifSearch,
        'order' : key,
        'sort' :sort
    };
    var gridData = {};
    $.ajax({
        url: "../auditlog/auditLogfunction.php",
        type: "POST",
        dataType: "json",
        data: dat,
        success: function (gridData) {
        //    console.log(gridData.html);
            $('.loader').hide();
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
                responsive: true,
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
                    $("th").removeClass('sorting_desc');
                    $("th").removeClass('sorting_asc');
                },
                "drawCallback": function (settings) {
                    $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
                    // $(".dataTables_filter:first").replaceWith('<div id="notifyDtl_filter" class="dataTables_filter"><label><input type="text" class="form-control form-control-sm" placeholder="Search records" value="' + notifSearch + '" id="notifSearch" aria-controls="notifyDtl"></label></div>');
                }
            });
            $('.dataTables_filter input').addClass('form-control');
            $('.tableloader').hide();
        }
    });



     /*
     * This function is for selecting
     *  row in event filter
     */

    $('#auditlog_datatable').on('click', 'tr', function() {
        var rowID = auditlogTable.row(this).data();
        var selected = rowID[6];

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
            url: "../lib/l-ajax.php?function=AJAX_getUserDetails" + '&csrfMagicToken=' + csrfMagicToken,
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
            url: "../lib/l-ajax.php?function=AJAX_getCustomerDetails" + '&csrfMagicToken=' + csrfMagicToken,
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
            window.location.href = "../auditlog/auditLogExport.php?from="+fromDate+"&to="+toDate+"&type="+type+"&sublist="+sublistval;

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
