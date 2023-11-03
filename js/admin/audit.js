$(document).ready(function () {
    audit_datatablelist();
    $('.UserSelection').show();
        $('.CustomerSelection').hide();
        $.ajax({
           url: "../lib/l-ajax.php",
           type: "post",
           data:{'function':'AJAX_get_UserDartDetails', 'csrfMagicToken': csrfMagicToken},
           success:function(data){
               $('#UserSelection').html(data);
               $(".selectpicker").selectpicker("refresh");
               $(".loader").hide();
           },
           error:function(error){
               console.log("error");
               $(".loader").hide();
           }
        });

});

/* ******************* audit CODE ******************* */


function checkLevelType(param){
    if(param == 'User'){
        $('.CustomerSelection').hide();
        $('.UserSelection').show();
        $.ajax({
           url: "../lib/l-ajax.php",
           type: "post",
           data:{'function':'AJAX_get_UserDartDetails', 'csrfMagicToken': csrfMagicToken},
           success:function(data){
               $('#UserSelection').html(data);
               $(".selectpicker").selectpicker("refresh");
               $(".loader").hide();
           },
           error:function(error){
               console.log("error");
               $(".loader").hide();
           }
        });
    }else{
        $('.UserSelection').hide();
        $('.CustomerSelection').show();
        $.ajax({
           url: "../lib/l-ajax.php?function=AJAX_getCustomerDetails&csrfMagicToken=" + csrfMagicToken,
           type: "get",
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

$('body').on('click', '.page-link', function () {
    var nextPage = $(this).data('pgno');
    const activeElement = window.currentActiveSortElement;
    const key = (activeElement) ? activeElement.sort : '';
    const sort = (activeElement) ? activeElement.type : '';
    audit_datatablelist(nextPage,'', key, sort);
})
$('body').on('change', '#notifyDtl_lengthSel', function () {

    audit_datatablelist(1,'');
});
// $(document).on('keypress', function (e) {
//     if (e.which == 13) {
//         var notifSearch = $('#notifSearch').val();
//         if (notifSearch != ''){
//             audit_datatablelist(1,'');
//         }else{
//             audit_datatablelist(1,'');
//         }
//     }
// });


/* =========== Audit LIST ============ */
function audit_datatablelist(nextPage = 1, notifSearch = '', key = '', sort = '') {

    notifSearch = $('#notifSearch').val();

    if (typeof notifSearch === 'undefined') {
        notifSearch = '';
    }

    checkAndUpdateActiveSortElement(key, sort);

    var dat = {
        "function": 'AJAX_Audit_GridData',
        'csrfMagicToken': csrfMagicToken,
        'limitCount': $('#notifyDtl_length :selected').val(),
        'nextPage': nextPage,
        'notifSearch': notifSearch,
        'order' : key,
        'sort' :sort
    };

    $.ajax({
        url:  "../lib/l-ajax.php",
        type: "POST",
        dataType: "json",
        data: dat,
        success: function (gridData) {
        $(".se-pre-con").hide();
        $('#auditTable').DataTable().destroy();
            auditTable = $('#auditTable').DataTable({
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

    $('#auditTable').on('click', 'tr', function () {
        var rowID = auditTable.row(this).data();
        var selected = rowID[4];
        $('#audit_selected').val(selected);
        auditTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
    });

    $("#audit_searchbox").keyup(function () {
        auditTable.search(this.value).draw();
    });
}

function exportDartAudit() {
    var fromDate = $('#datefrom').val();
    var toDate   = $('#dateto').val();
    var type = 'User';//$('#LevelType').val();
    // if(type == 'Customer'){
    //     var sublistval = $('#CustomerSelection').val();
    // }else{
        var sublistval = $('#UserSelection').val();
    // }
    $('#errorMsg').html('');
    $('#datefrom_err').html('');
    $('#dateto_err').html('');
    $('#errorMsg').show();

    if(fromDate==''||toDate=='') {
        if (fromDate == '') {
           $.notify("Please choose the Start date");
           return false;
       }
       if (toDate == '' ) {
           $.notify("Please choose the End date");
           return false;
       }
    } else {
        var start = (new Date(fromDate).getTime());
        var to = (new Date(toDate).getTime());

        if(start >= to ) {
            $.notify("Start Date is expected to be before the end date.");
            return false;
        } else {
            window.location.href = "../lib/l-ajax.php?function=AJAX_Export_DartAudit&from="+fromDate+"&to="+toDate+"&type="+type+"&sublist="+sublistval;

            $.notify('Dart Audit Details were successfully exported');
            closePopUp();
            $("#dartaudit-range").modal("hide");
        }
    }
}

$('#detailViewAudit').click(function () {

    var auditId = $('#audit_selected').val();
    $('#auditTime').html('');
    $('#audituserName').html('');
    $('#auditdetails').html('');
    if (auditId == '') {
        $("#rsc-add-container").hide();
        $.notify("Please choose at least one record");
        closePopUp();

    } else {
        $.ajax({
            url: "../lib/l-ajax.php",
            type: "POST",
            data: {"function":"Ajax_audit_Data",
            "auditId" : auditId,
            "csrfMagicToken" : csrfMagicToken},
            success: function (data) {
                data = JSON.parse($.trim(data));
                if (typeof data === 'object') {
                    console.log(data);
                   $('#auditdetailpopup').modal('show');
                    $('#auditTime').html(data.time);
                    $('#audituserName').html(data.user);
                    $('#auditdetails').html(data.detail);
                } else {
                    $('#auditdetailpopup').modal('show');
                    $('#auditTime').html('NA');
                    $('#audituserName').html('NA');
                    $('#auditdetails').html('NA');
                }
            },
            error:function(error){
                console.log("error");
            }
        });
        //$('#auditdetailpopup').modal('show');
    }
});
