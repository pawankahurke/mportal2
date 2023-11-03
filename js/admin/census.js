// var siteName = '';
// var machineId = '';
// var machineName = '';

// $(document).ready(function() {
//    getCensusList();
//    $('#back_to_main').hide();

// });

// function getCensusList() {

//     $("#se-pre-con-loader").show();
//     $("#census_grid").dataTable().fnDestroy();

//     censusTable = $('#census_grid').DataTable({
//         scrollY: jQuery('#census_grid').data('height'),
//         scrollCollapse: true,
//         autoWidth: false,
//         searching: true,
//         processing: true,
//         serverSide: true,
//         bAutoWidth: true,
//         ordering: true,
//         select: false,
//         bInfo: false,
//         responsive: true,
//         stateSave: true,
//         "stateSaveParams": function (settings, data) {
//             data.search.search = "";
//         },
//         order: [[0, "desc"]],
//         ajax: {
//             url: "../lib/l-ajax.php?function=AJAX_GetCensusData&csrfMagicToken=" + csrfMagicToken,
//             type: "POST",
// //            rowId : "id"
//         },
//         "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
//         "language": {
//             "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
//             searchPlaceholder: "Search"
//         },
//         "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
//         columns: [
//             {"data": "siteName"},
//             {"data": "count"},

//         ],
//         columnDefs: [
//             {className: "table-plus datatable-nosort", "targets": 0},
//             {className: "datatable-nosort", "targets": 1},

//         ],
//         initComplete: function(settings, json) {
//            censusTable.$('tr:first').click();
//            if ( ! censusTable.data().any() ) {
//                $('#view').addClass("disableAnchorTag");
//                $('#export_option').addClass("disableAnchorTag");
//            }
//         },
//         drawCallback: function (settings) {
//                     $(".dataTables_scrollBody").mCustomScrollbar({
//                         theme: "minimal-dark"
//                     });
//                     $(".census").show();
//                     $(".machine").hide();
// //                    $('.equalHeight').matchHeight();
//                     $("#se-pre-con-loader").hide();
//                 }
//     });

//     $('#census_grid tbody').on('click', 'tr', function() {
//         censusTable.$('tr.selected').removeClass('selected');
//         $(this).addClass('selected');
//         id = censusTable.row(this).id();

//     });

//     $("#census_searchbox").keyup(function() {
//         censusTable.search(this.value).draw();
//     });
// }

// function selectConfirm(data_target_id) {

//     if (data_target_id === 'view') {
//         $('#machine_searchbox').show();
//         $('#census_searchbox').hide();
//         $('#view').hide();
//         $('#export_option').hide();
//         $('#back_to_main').show();
//         viewMachineList(id);

//     } else if (data_target_id === 'exportList') {
//        //show pop up for selection
//        $('#export').modal('show');
//     } else if(data_target_id === 'back') {
//         getCensusList();
//         $('#machine_searchbox').hide();
//         $('#census_searchbox').show();
//         $('#view').show();
//         $('#export_option').show();
//         $('#back_to_main').hide();
//     }
// }

// var viewMachineList = false;
// function viewMachineList(site) {
//     if(viewMachineList){
//         return;
//     }
//     viewMachineList = true;
//     siteName = site;
//     $("#machine_grid").dataTable().fnDestroy();
//     $("#loader").show();

//     machineTable = $('#machine_grid').DataTable({
//         scrollY: jQuery('#machine_grid').data('height'),
//         scrollCollapse: true,
//         autoWidth: false,
//         searching: true,
//         processing: true,
//         serverSide: true,
//         bAutoWidth: true,
//         ordering: true,
//         select: false,
//         bInfo: false,
//         responsive: true,
//         stateSave: true,
//         "stateSaveParams": function (settings, data) {
//             data.search.search = "";
//         },
// //        order: [[1, "desc"]],
//         ajax: {
//             url: "../lib/l-ajax.php?function=AJAX_GetMachineList&csrfMagicToken="+csrfMagicToken,
//             type: "POST",
//             data: {
//                 siteName: siteName,
//                 'csrfMagicToken': csrfMagicToken}
//         },
//         "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
//         "language": {
//             "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
//             searchPlaceholder: "Search"
//         },
//         "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
//         columns: [
//             {"data": "machineName"},
//             {"data": "os"},
//              {"data": "born"},
//              {"data": "last"},
//               {"data":"action"},

//         ],
//         columnDefs: [
//             {className: "checkbox-btn ignore", "targets": [0]},
//             {className: "ignore", "targets": [1]},

//         ],
//         drawCallback: function (settings) {
//             $(".dataTables_scrollBody").mCustomScrollbar({
//                 theme: "minimal-dark"
//             });
//             $(".census").hide();
//             $(".machine").show();
//             $("#se-pre-con-loader").hide();
//             $(".loader").hide();
//         }
//     });
//     $("#machine_searchbox").keyup(function() {
//         machineTable.search(this.value).draw();
//     });
// }

// function exportCensus() {

//     var selVal = '';

//     $('#selectField').find(':input').each(function() {
//         if ($(this).is(':checked')) {
//             selVal += $(this).val() + ',';
//         }
//     });
//     var colName = '';
//     if($('#include_Column').val() === 'null') {
//     } else {
//         colName = $('#include_Column').val();
//     }
//     window.location.href = '../lib/l-ajax.php?function=AJAX_censusExport&colName=' + colName + '&condition=' + selVal+'&cid='+id + "&csrfMagicToken=" + csrfMagicToken;
// }

// $('#export').on('hidden.bs.modal', function() {
//     $('#export .form-control').val('');
//     $(".selectpicker").selectpicker("refresh");
// });

// function removeMachine(macid,name){

//     $('#delete_machine').modal('show');
// 	$('#mainContent').show();
//     $('#confirmContent').hide();
// //    $('#site_name').html(siteName);
//     $('#machine_Id').html(name);
//     machineId = macid;
//     machineName = name;
// }

// function confirm(id) {

//    $('#mainContent').hide();
//    $('#confirmContent').show();
//   if(id === 'machine') {
//       $('#pop_head').html('Delete: ');
//       $('#del_msg').html('Are you sure you want to delete?');
//       $('#confirm_delete').show();
//       $('#confirm_expunge').hide();
//   } else {
//       $('#pop_head').html('Expunge: ');
//       $('#del_msg').html('Are you sure you want to expunge?');
//       $('#confirm_delete').hide();
//       $('#confirm_expunge').show();

//   }
// }

// function cancel() {
// //    $('#mainContent').show();
// //    $('#confirmContent').hide();
//     $('#delete_machine').modal('hide');
// }

// function deleteMachine() {

//     var mId = machineId;

//     $("#updateRevokeotc_errorMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
//     $.ajax({
//         type: 'post',
//         dataType: 'json',
//         url: '../lib/l-ajax.php?function=AJAX_census_delete&csrfMagicToken=' + csrfMagicToken,
//         data : {mId: mId }

//     }).done(function(data) {
//         if (data.status === 'success') {
//             $("#updateRevokeotc_errorMsg").css("color", "green").html(data.msg);
//             setInterval(function () {
//                 location.reload();
//             }, 2000);
//         }
// });
// }

// function expunge() {

//     var mId = machineId;
//     var siteId = siteName;
// //    $('#mainContent').hide();
// //    $('#confirmContent').show();

//     $("#updateRevokeotc_errorMsg").html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
//     $.ajax({
//         type: 'post',
//         dataType: 'json',
//         url: '../lib/l-ajax.php?function=AJAX_expunge&csrfMagicToken=' + csrfMagicToken,
//         data : {mId: mId}

//     }).done(function(data) {
//         if (data.status === 'success') {
//             $("#updateRevokeotc_errorMsg").css("color", "green").html(data.msg);
//             setInterval(function () {
//                 location.reload();
//             }, 2000);

//         }
//     });
// }
