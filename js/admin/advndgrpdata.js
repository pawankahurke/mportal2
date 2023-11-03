$(function () {
  get_advncdgroupData((nextPage = 1), (notifSearch = ''), (key = ''), (sort = ''));
});

$('body').on('click', '.page-link', function () {
  var nextPage = $(this).data('pgno');
  notifName = $(this).data('name');
  get_advncdgroupData(nextPage, '');
});

$('body').on('change', '#notifyDtl_lengthSel', function () {
  get_advncdgroupData(1, '');
});

// function get_advncdgroupData(nextPage = 1,notifSearch = '',key = '',sort = '') {
//     var formData = new FormData();
//     formData.append('function', 'get_viewadvncdgroups');
//     formData.append('csrfMagicToken', csrfMagicToken);

//     $.ajax({
//         url: "../admin/groupfunctions.php",
//         type: 'POST',
//         data: formData,
//         processData: false,
//         contentType: false,
//         dataType: 'json',
//         success: function (gridData) {
//             $(".se-pre-con").hide();
//             $('#advncdgroupList').DataTable().destroy();
//             $(".loader").hide();
//             groupTable = $('#advncdgroupList').DataTable({
//                 scrollY: 'calc(100vh - 240px)',
//                 scrollCollapse: true,
//                 paging: true,
//                 searching: true,
//                 ordering: true,
//                 aaData: gridData,
//                 bAutoWidth: true,
//                 select: false,
//                 bInfo: false,
//                 responsive: true,
//                 stateSave: true,
//                 "stateSaveParams": function (settings, data) {
//                     data.search.search = "";
//                 },
//                 "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
//                 "language": {
//                     "info": "Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
//                     search: "_INPUT_",
//                     searchPlaceholder: "Search records",
//                 },
//                 "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
//                 columnDefs: [

//                     {className: "checkbox-btn", "targets": [0]},
//                     { "type": "date", "targets": [3]},
//                     {
//                         targets: "datatable-nosort",
//                         orderable: false
//                     }
//                 ],
//                 initComplete: function (settings, json) {
//                     if (gridData.length === 0) {
//                         $('.bottom').hide();
//                         $('#totalmachinecount').val(gridData.length);
//                         $('#view_grpDetails').hide();
//                         $('#delete_group').hide();
//                     } else {
//                         $('.bottom').show();
//                         $('#totalmachinecount').val(gridData.length);
//                         $('#view_grpDetails').show();
//                         $('#delete_group').show();
//                     }
//                 },
//                 drawCallback: function (settings) {
//                     $(".se-pre-con").hide();
//                 }
//             });
//             $('.tableloader').hide();
//         },
//         error: function (msg) {
//             $(".loader").hide();
//         }
//     });

//     $('#advncdgroupList').on('click', 'tr', function () {
//         groupTable.$('tr.selected').removeClass('selected');
//         $(this).addClass('selected');
//         var rowID = groupTable.row(this).data();

//         if (rowID != 'undefined' && rowID !== undefined) {
//             var grpname = rowID[8];
//             var advcndgrpid = rowID[7];
//             $('#groupid').val(advcndgrpid);
//             $('#groupname').val(grpname);
//             $('#selected').val(rowID[5]);
//             $('#hiddengrpid').val(rowID[7]);
//             $('#groupideditcsv').val(rowID[7]);
//             $('#grupnamehidden').val(rowID[8]);
//             $('#groupType').val(rowID[2]);
//         }

//     });

//     $('#advncdgroupList').on('dblclick', 'tr', function () {
//         var GroupType = $('#groupType').val();
//         if(GroupType == 'Dynamic Group'){
//             EditAdvGroup();
//         }else{
//             checkGroupEditAccess();
//         }
//     });

//     $("#groups_searchbox").keyup(function () {//group search code
//         groupTable.search(this.value).draw();
//     });
// }

//Not linked to Groups
function get_advncdgroupData(nextPage = 1, notifSearch = '', key = '', sort = '') {
  var notifSearch = $('#notifSearch').val();
  if (typeof notifSearch === 'undefined') {
    notifSearch = '';
  }
  $('#loader').show();
  $.ajax({
    url: '../lib/l-ajax.php',
    type: 'POST',
    data: {
      function: 'get_viewadvncdgroups',
      csrfMagicToken: csrfMagicToken,
      limitCount: $('#notifyDtl_length :selected').val(),
      nextPage: nextPage,
      notifSearch: notifSearch,
      order: key,
      sort: sort,
    },
    dataType: 'json',
    success: function (gridData) {
      $('#loader').hide();
      $('.se-pre-con').hide();
      $('#advncdgroupList').DataTable().destroy();
      $('#advncdgroupList tbody').empty();
      deviceTable = $('#advncdgroupList').DataTable({
        scrollY: 'calc(100vh - 240px)',
        scrollCollapse: true,
        paging: false,
        searching: false,
        ordering: false,
        aaData: gridData.html,
        bAutoWidth: true,
        select: false,
        bInfo: false,
        responsive: true,
        stateSave: true,
        pagingType: 'full_numbers',
        stateSaveParams: function (settings, data) {
          data.search.search = '';
        },
        order: [[3, 'desc']],
        lengthMenu: [
          [10, 25, 50, 100],
          [10, 25, 50, 100],
        ],
        language: {
          info: 'Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>',
          search: '_INPUT_',
          searchPlaceholder: 'Search records',
        },
        dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        columnDefs: [{ type: 'date', targets: [2, 3] }],
        initComplete: function (settings, json) {
          if (gridData.html.length === 0) {
            $('.bottom').hide();
            $('#totalmachinecount').val(gridData.html.length);
          } else {
            $('.bottom').show();
            $('#totalmachinecount').val(gridData.html.length);
            if (currentSearchtype == 'Groups') {
              $('#detaild_grid tbody tr').removeClass('selected');
              $('#detaild_grid tbody tr').first().addClass('selected');
            }
          }
          $('th').removeClass('sorting_desc');
          $('th').removeClass('sorting_asc');
        },
        drawCallback: function (settings) {
          $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
          // $(".dataTables_filter:first").replaceWith('<div id="notifyDtl_filter" class="dataTables_filter"><label><input type="text" class="form-control form-control-sm" placeholder="Search records" value="' + notifSearch + '" id="notifSearch" aria-controls="notifyDtl"></label></div>');
          $('#se-pre-con-loader').hide();
        },
      });
      $('.dataTables_filter input').addClass('form-control');
    },
    error: function (msg) {
      console.log('Grid Error : ' + msg);
    },
  });

  //     $.ajax({
  //         url: "groupfunctions.php",
  //         type: "POST",
  //         data:"function=get_viewadvncdgroups&csrfMagicToken=" + csrfMagicToken,
  //         dataType: "json",
  //         success: function(gridData) {
  //             $(".loader").hide();
  //             $(".se-pre-con").hide();
  //             $('#advncdgroupList').DataTable().destroy();
  //             groupTable = $('#advncdgroupList').DataTable({
  //                 scrollY: jQuery('#advncdgroupList').data('height'),
  //                 scrollCollapse: true,
  //                 paging: true,
  //                 searching: true,
  //                 ordering: true,
  //                 aaData: gridData,
  //                 bAutoWidth: true,
  //                 select: false,
  //                 bInfo: false,
  //                 responsive: true,
  //                 stateSave: true,
  //                 "stateSaveParams": function (settings, data) {
  //                     data.search.search = "";
  //                 },
  //                 "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
  //                 "language": {
  //                     "info": "_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>",
  //                     searchPlaceholder: "Search"
  //                 },
  //                 "dom": '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
  //                 columnDefs: [{className: "checkbox-btn", "targets": [0]},
  // //                             { "width": "30%", "targets": 1 },
  // //                             { "width": "30%", "targets": 2 },
  //                     {
  //                         targets: "datatable-nosort",
  //                         orderable: false
  //                     }],
  //                 initComplete: function (settings, json) {
  //                 },
  //                 drawCallback: function (settings) {
  //                     $(".dataTables_scrollBody").mCustomScrollbar({
  //                         theme: "minimal-dark"
  //                     });
  //                     $('.equalHeight').matchHeight();
  //                     $(".se-pre-con").hide();
  //                 }
  //             });
  //             $('.tableloader').hide();
  //         },
  //         error: function (msg) {
  //             $(".loader").hide();
  //         }
  //     });

  $('#advncdgroupList').on('click', 'tr', function () {
    var rowID = groupTable.row(this).data();
    var advcndgrpid = rowID[6];
    var defs = rowID[7];
    var eventqry = rowID[8]; //event query
    var asstqry = rowID[9]; //asset filter
    var grpname = rowID[0];
    $('#groupid').val(advcndgrpid);

    if (defs == '3' || defs == '4') {
      $('#adv_update_group').show();
      $('#adv_edit_group').show();
      $('#adv_view_group').show();
    } else {
      $('#adv_update_group').hide();
      $('#adv_edit_group').hide();
      $('#adv_view_group').hide();
    }
    $('#defs').val(defs);
    $('#event').val(eventqry);
    $('#asset').val(asstqry);
    $('#groupname').val(grpname);
    groupTable.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
  });

  $('#groups_searchbox').keyup(function () {
    //group search code
    groupTable.search(this.value).draw();
  });
}

function selectConfirm(value) {
  var grpid = $('#groupid').val(); //advanced group id
  var def = $('#defs').val();
  var eventq = $('#event').val();
  var assetq = $('#asset').val();
  var grpname = $('#groupname').val();

  if (value == 'delete_group') {
    if (grpid != '') {
      $('#delete-group-detail').modal('show');
    } else {
      $('#warningemptygroup').modal('show');
    }
  } else if (value == 'delete_advgrp') {
    deleteadvgrouplist(grpid);
  } else if (value == 'adv_view_group') {
    if (grpid != '') {
      $('#adv_grp_dtl').modal('show');
      advgroupDetail(grpid);
      viewdetailpopupclicked();
    } else {
      $('#warningemptygroup').modal('show');
    }
  } else if (value == 'adv_add_group') {
    $('#adv_add-group').modal('show');
    addadvgroupData();
  } else if (value == 'adv_update_group') {
    if (grpid != '') {
      $('#update-group-detail').modal('show');
    } else {
      $('#warningemptygroup').modal('show');
    }
  } else if (value == 'adv_edit_group') {
    if (grpid != '') {
      $('#adveditgrouppopup').modal('show');

      if (def == '3') {
        //event query

        editadvgroup(eventq, def, grpid);
      } else if (def == '4') {
        //asset query
        editadvgroup(assetq, def, grpid);
      }
    } else {
      $('#warningemptygroup').modal('show');
    }
  } else if (value == 'update_adv_submit') {
    if (def == '3') {
      //event query
      updategroup(eventq, def, grpid);
    } else if (def == '4') {
      //asset query
      updategroup(assetq, def, grpid);
    }
  } else if (value == 'back_to_grp') {
    window.location.href = 'index.php';
  }
}

function deleteadvgrouplist(id) {
  //    var functioncall    = "function=get_groupListDelete&value=" + id;
  //    var encryptedData   = get_RSA_EnrytptedData(functioncall);
  $('#deletefail').html('');
  $('#deletefail').show();

  $.ajax({
    url: 'groupfunctions.php?function=checkEditAccess&csrfMagicToken=' + csrfMagicToken,
    type: 'post',
    data: 'groupid=' + id,
    dataType: 'json',
    success: function (data) {
      if (data.msg === 'success') {
        $.ajax({
          type: 'GET',
          url: 'groupfunctions.php',
          data: 'function=advgroupDelete&value=' + id + '&csrfMagicToken=' + csrfMagicToken,
          dataType: 'json',
          success: function (data) {
            if (data.msg == 'success') {
              $('#delete-group-detail').modal('hide');
              location.reload();
            }
          },
        });
      } else {
        $('#deletefail').html('You do not have access to delete this group');
        setTimeout(function () {
          $('#delete-group-detail').modal('hide');
          location.reload();
        }, 3000);
      }
    },
  });
}

function advgroupDetail(grpid) {
  //    var functioncall    = "function=get_groupviewDetail&grpid=" + grpid;
  //    var encryptedData   = get_RSA_EnrytptedData(functioncall);

  $.ajax({
    url: 'groupfunctions.php',
    type: 'POST',
    data: 'function=adv_groupviewDetail&grpid=' + grpid + '&csrfMagicToken=' + csrfMagicToken,
    dataType: 'json',
    success: function (gridData) {
      $('.information-portal-popup .se-pre-con').hide();
      $('#groupeventDtl').DataTable().destroy();
      groupviewTable = $('#groupeventDtl').DataTable({
        scrollY: jQuery('#groupeventDtl').data('height'),
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
        stateSave: true,
        stateSaveParams: function (settings, data) {
          data.search.search = '';
        },
        lengthMenu: [
          [10, 25, 50, 100],
          [10, 25, 50, 100],
        ],
        language: {
          info: '_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>',
          searchPlaceholder: 'Search',
        },
        dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        columnDefs: [
          { className: 'checkbox-btn', targets: [0] },
          { width: '3%', targets: 1 },
          { width: '17%', targets: 0 },
          {
            targets: 'datatable-nosort',
            orderable: false,
          },
        ],
        initComplete: function (settings, json) {},
        drawCallback: function (settings) {
          $('.dataTables_scrollBody').mCustomScrollbar({
            theme: 'minimal-dark',
          });
          $('.information-portal-popup .se-pre-con').hide();
        },
      });
    },
    error: function (msg) {},
  });
}

/* ======= GROUP VIEW DETAIL GRID COLUMN ONLOAD CLICK FUNCTION ======== */
function viewdetailpopupclicked() {
  setTimeout(function () {
    $('.event-info-grid-host').click();
  }, 300);
}

function addadvgroupData() {
  $.ajax({
    url: 'groupfunctions.php',
    type: 'post',
    data: 'function=get_addadvData&csrfMagicToken=' + csrfMagicToken,
    dataType: 'json',
    success: function (data) {
      $('#eventfilter').html(data.event);
      $('#assetquery').html(data.asset);
      $('.selectpicker').selectpicker('refresh');
    },
  });
}

function advgrpCreate() {
  var gname = $('#advgname').val();
  var global = '0';
  var evntrdo = '0';
  var asstrdo = '0';
  if ($('#global').is(':checked')) {
    global = '1';
  }
  if ($('#evntradio').is(':checked')) {
    evntrdo = '1';
  }
  if ($('#assetradio').is(':checked')) {
    asstrdo = '1';
  }

  var evntid = $('#eventfilter').val();
  var asstid = $('#assetquery').val();
  var days = $('#days').val();
  var hours = $('#hours').val();
  var minutes = $('#minute').val();

  if (gname == '') {
    $('#successmsgadv').show();
    $('#successmsgadv').html('<span style="color:red;">Please enter group name</span>');
    return false;
  }

  if (!validate_AlphaNumeric(gname)) {
    $('#successmsgadv').html('<span style="color:red">Special charecters not allowed in Group name</span>');
    $('#successmsgadv').show();
    return false;
  }

  if (evntrdo == 0 && asstrdo == 0) {
    $('#successmsgadv').show();
    $('#successmsgadv').html('<span style="color:red;">Please select event/asset query</span>');
    return false;
  }

  if (evntrdo == 1 && evntid == '') {
    $('#successmsgadv').show();
    $('#successmsgadv').html('<span style="color:red;">Please select event filter</span>');
    asstid = '';
    return false;
  }

  if (evntrdo == 1 && evntid != '' && days == '') {
    $('#successmsgadv').show();
    $('#successmsgadv').html('<span style="color:red;">Please enter days </span>');
    asstid = '';
    return false;
  }

  if (asstrdo == 1 && asstid == '') {
    $('#successmsgadv').show();
    $('#successmsgadv').html('<span style="color:red;">Please select asset query</span>');
    evntid = '';
    return false;
  }
  $('#successmsgadv').html('');
  $('#loadingadvadd').html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
  $.ajax({
    url: 'groupfunctions.php?function=addadvgroupValues',
    type: 'post',
    data:
      'gname=' +
      gname +
      '&global=' +
      global +
      '&evntid=' +
      evntid +
      '&asstid=' +
      asstid +
      '&evntrdo=' +
      evntrdo +
      '&asstrdo=' +
      asstrdo +
      '&days=' +
      days +
      '&hours=' +
      hours +
      '&min=' +
      minutes +
      '&csrfMagicToken=' +
      csrfMagicToken,
    dataType: 'json',
    success: function (data) {
      $('#loadingadvadd').hide();
      if (data.msg == 'success') {
        $('#successmsgadv').show();
        $('#successmsgadv').html('<span style="color:green;">Advanced Group added successfully</span>');
      } else if (data.msg == 'nomachine') {
        $('#successmsgadv').show();
        $('#successmsgadv').html('<span style="color:red;">No machine updated</span>');
      } else if (data.msg == 'error') {
        $('#successmsgadv').show();
        $('#successmsgadv').html('<span style="color:red;">Group name already exists</span>');
      }

      setTimeout(function () {
        $('#adv_add-group').modal('hide');
        location.href = 'advndgrp.php';
      }, 2000);
    },
  });
}

function updategroup(id, defs, grpid) {
  $('#loadingadvupdate').html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
  $.ajax({
    url: 'groupfunctions.php?function=advgroupUpdate',
    type: 'post',
    data: 'id=' + id + '&def=' + defs + '&grpid=' + grpid + '&csrfMagicToken=' + csrfMagicToken,
    dataType: 'json',
    success: function (data) {
      $('#loadingadvupdate').hide();
      if (data.msg == 'success') {
        $('#successupdatemsg').html('<span style="color:green; margin-left:25%;">Advanced Group updated successfully</span>');
        $('#successupdatemsg').fadeOut(2500);
      } else if (data.msg == 'nomachine') {
        $('#successupdatemsg').html('<span style="color:red; margin-left:25%;">No machine updated</span>');
        $('#successupdatemsg').fadeOut(2500);
      }

      setTimeout(function () {
        $('#update-group-detail').modal('hide');
        location.href = 'advndgrp.php';
      }, 2000);
    },
  });
}

function editadvgroup(id, defs, gid) {
  $.ajax({
    url: 'groupfunctions.php?function=advgroupedit',
    type: 'post',
    data: 'gid=' + gid + '&id=' + id + '&def=' + defs + '&csrfMagicToken=' + csrfMagicToken,
    dataType: 'json',
    success: function (data) {
      $('#editadvgname').val(data.name);

      if (data.global == '1') {
        $('#editglobal').attr('checked', 'checked');
      }
      if (data.defs == '3') {
        $('#editevntradio').prop('checked', true);
        $('#editeventfilter').html(data.event);
        $('#editassetquery').attr('title', 'select asset query');
        $('#editassetradio').prop('checked', false);
        $('#editassetquery').html(data.asset);
        $('#editdays').val(data.day).change();
        $('#edithours').val(data.hour).change();
        $('#editminute').val(data.minute).change();
        $('.selectpicker').selectpicker('refresh');
      } else if (data.defs == '4') {
        $('#editassetradio').prop('checked', true);
        $('#editassetquery').html(data.asset);
        $('#editeventfilter').attr('title', 'select event filter');
        $('#editevntradio').prop('checked', false);
        $('#editeventfilter').html(data.event);
        $('.selectpicker').selectpicker('refresh');
      }
      $('.selectpicker').selectpicker('refresh');
    },
  });
}

function editadvgroupSubmit() {
  var grpid = $('#groupid').val();
  var gname = $('#editadvgname').val();
  var global = '0';
  var evntrdo = '0';
  var asstrdo = '0';
  if ($('#editglobal').is(':checked')) {
    global = '1';
  }
  if ($('#editevntradio').is(':checked')) {
    evntrdo = '1';
  }
  if ($('#editassetradio').is(':checked')) {
    asstrdo = '1';
  }

  var evntid = $('#editeventfilter').val();
  var asstid = $('#editassetquery').val();
  var days = $('#editdays').val();

  if (gname == '') {
    $('#editsuccessmsgadv').show();
    $('#editsuccessmsgadv').html('<span style="color:red;">Please enter group name</span>');
    return false;
  }

  if (!validate_AlphaNumeric(gname)) {
    $('#editsuccessmsgadv').html('<span style="color:red">Special charecters not allowed in Group name</span>');
    $('#editsuccessmsgadv').show();
    return false;
  }

  if (evntrdo == 0 && asstrdo == 0) {
    $('#editsuccessmsgadv').show();
    $('#editsuccessmsgadv').html('<span style="color:red;">Please select event/asset query</span>');
    return false;
  }

  if (evntrdo == 1 && evntid == '') {
    $('#editsuccessmsgadv').show();
    $('#editsuccessmsgadv').html('<span style="color:red;">Please select event filter</span>');
    asstid = '';
    return false;
  }

  if (evntrdo == 1 && evntid != '' && days == '') {
    $('#editsuccessmsgadv').show();
    $('#editsuccessmsgadv').html('<span style="color:red;">Please enter days </span>');
    asstid = '';
    return false;
  }

  if (asstrdo == 1 && asstid == '') {
    $('#editsuccessmsgadv').show();
    $('#editsuccessmsgadv').html('<span style="color:red;">Please select asset query</span>');
    evntid = '';
    return false;
  }
  $('#editsuccessmsgadv').html('');
  $('#loadingadvedit').html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');

  $.ajax({
    url: 'groupfunctions.php?function=checkEditAccess',
    type: 'post',
    data: 'groupid=' + grpid + '&csrfMagicToken=' + csrfMagicToken,
    dataType: 'json',
    success: function (data) {
      if (data.msg == 'success') {
        $.ajax({
          url: 'groupfunctions.php?function=editadvgroupValues',
          type: 'post',
          data:
            'gname=' +
            gname +
            '&global=' +
            global +
            '&evntid=' +
            evntid +
            '&asstid=' +
            asstid +
            '&evntrdo=' +
            evntrdo +
            '&asstrdo=' +
            asstrdo +
            '&days=' +
            days +
            '&groupid=' +
            grpid +
            '&csrfMagicToken=' +
            csrfMagicToken,
          dataType: 'json',
          success: function (data) {
            $('#loadingadvedit').hide();
            if (data.msg == 'success') {
              $('#editsuccessmsgadv').show();
              $('#editsuccessmsgadv').html('<span style="color:green;">Advanced Group edited successfully</span>');
            } else if (data.msg == 'nomachine') {
              $('#editsuccessmsgadv').show();
              $('#editsuccessmsgadv').html('<span style="color:red;">No machine updated</span>');
            } else if (data.msg == 'error') {
              $('#editsuccessmsgadv').show();
              $('#editsuccessmsgadv').html('<span style="color:red;">Group name already exists</span>');
            }

            setTimeout(function () {
              $('#adv_add-group').modal('hide');
              location.href = 'advndgrp.php';
            }, 2000);
          },
        });
      } else {
        $('#loadingadvedit').hide();
        $('#editsuccessmsgadv').html('<span style="color:red;">You do not have access to edit this group</span>');
        setTimeout(function () {
          $('#adv_add-group').modal('hide');
          location.href = 'advndgrp.php';
        }, 2000);
      }
    },
  });
}

$('#advgname').keydown(function () {
  $('#successmsgadv').hide();
});

$('#editadvgname').keydown(function () {
  $('#editsuccessmsgadv').hide();
});

function validate_AlphaNumeric(name) {
  var filter = /^[a-z\d\_\ \s]+$/i;
  if (filter.test(name)) {
    return true;
  } else {
    return false;
  }
}
