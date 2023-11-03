$(document).ready(function () {
  console.log('work');
  getLoginDetails();
  $('.UserSelection').show();
  $('.CustomerSelection').hide();
});

function getLoginDetails(nextPage = 1, notifSearch = '', key = '', sort = '') {
  const loader = $('#loader');
  loader.show();

  checkAndUpdateActiveSortElement(key, sort);

  $('#login-range').show();

  notifSearch = $('#notifSearch').val();
  if (typeof notifSearch === 'undefined') {
    notifSearch = '';
  }
  var dat = {
    function: 'AJAX_get_LoginDetails',
    csrfMagicToken: csrfMagicToken,
    limitCount: $('#notifyDtl_length :selected').val(),
    nextPage: nextPage,
    notifSearch: notifSearch,
    order: key,
    sort: sort,
  };
  var gridData = {};
  $.ajax({
    url: '../lib/l-ajax.php',
    type: 'POST',
    dataType: 'json',
    data: dat,
    success: function (gridData) {
      //    console.log(gridData.html);
      $('.se-pre-con').hide();
      $('#login_datatable').DataTable().destroy();
      $('#login_datatable tbody').empty();
      loginTable = $('#login_datatable').DataTable({
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
        pagingType: 'full_numbers',
        stateSaveParams: function (settings, data) {
          data.search.search = '';
        },
        order: [[2, 'asc']],
        lengthMenu: [
          [10, 25, 50, 100],
          [10, 25, 50, 100],
        ],
        //                "lengthChange": false,
        language: {
          info: 'Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>',
          search: '_INPUT_',
          searchPlaceholder: 'Search records',
        },
        columnDefs: [
          {
            targets: 0,
            orderable: false,
          },
        ],
        dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function (settings, json) {
          $('.equalHeight').show();
          $('#absoLoader').hide();
          $('th').removeClass('sorting_desc');
          $('th').removeClass('sorting_asc');
        },
        drawCallback: function (settings) {
          $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
          // $(".dataTables_filter:first").replaceWith('<div id="notifyDtl_filter" class="dataTables_filter"><label><input type="text" class="form-control form-control-sm" placeholder="Search records" value="' + notifSearch + '" id="notifSearch" aria-controls="notifyDtl"></label></div>');
        },
      });
      $('.dataTables_filter input').addClass('form-control');
      $('.tableloader').hide();
      loader.hide();
    },
  });
  /*
   * This function is for selecting
   *  row in event filter
   */

  $('#login_datatable').on('click', 'tr', function () {
    var rowID = loginTable.row(this).data();
    var selected = rowID[1];

    $('#selected').val(selected);
    loginTable.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
  });
}

$('body').on('click', '.page-link', function () {
  var nextPage = $(this).data('pgno');
  notifName = $(this).data('name');
  const activeElement = window.currentActiveSortElement;
  const key = (activeElement) ? activeElement.sort : '';
  const sort = (activeElement) ? activeElement.type : '';
  getLoginDetails(nextPage, '', key, sort);
});
$('body').on('change', '#notifyDtl_lengthSel', function () {
  getLoginDetails(1);
});
// $(document).on('keypress', function (e) {
//     if (e.which == 13) {
//         var notifSearch = $('#notifSearch').val();
//         if (notifSearch != ''){
//             getLoginDetails(1);
//         }else{
//             getLoginDetails(1);
//         }
//     }
// });

function checkLevelType(param) {
  if (param == 'User') {
    $('.CustomerSelection').hide();
    $('.UserSelection').show();
    $.ajax({
      url: '../lib/l-ajax.php?function=AJAX_getUserDetails&csrfMagicToken=' + csrfMagicToken,
      type: 'get',
      success: function (data) {
        $('#UserSelection').html(data);
        $('.selectpicker').selectpicker('refresh');
      },
      error: function (error) {
        console.log('error');
      },
    });
  } else {
    $('.UserSelection').hide();
    $('.CustomerSelection').show();
    $.ajax({
      url: '../lib/l-ajax.php?function=AJAX_getCustomerDetails&csrfMagicToken=' + csrfMagicToken,
      type: 'get',
      success: function (data) {
        $('#CustomerSelection').html(data);
        $('.selectpicker').selectpicker('refresh');
      },
      error: function (error) {
        console.log('error');
      },
    });
  }
}

function exportLogin() {
  var fromDate = $('#datefrom').val();
  var toDate = $('#dateto').val();
  var level = $('#levelId').val();
  var type = $('#LevelType').val();
  if (type == 'Customer') {
    var sublistval = $('#CustomerSelection').val();
  } else {
    var sublistval = $('#UserSelection').val();
  }
  $('#errorMsg').html('');
  $('#datefrom_err').html('');
  $('#dateto_err').html('');
  $('#errorMsg').show();

  if (fromDate == '' || toDate == '' || type == '') {
    if (type == '') {
      $.notify('Please choose a type');
      return false;
    }
    if (fromDate == '') {
      //$('#errorMsg').html('<span>Please select date range</span>');
      //           $('#datefrom_err').html('Please select date range');
      //           setTimeout(function(){
      //               $("#errorMsg").fadeOut(2000)},1000);
      $.notify('Please choose the Start date');
      return false;
    }
    if (toDate == '') {
      $.notify('Please choose the End date');
      return false;
    }
  } else {
    var start = new Date(fromDate).getTime();
    var to = new Date(toDate).getTime();

    if (start >= to) {
      $.notify('Start Date is expected to be before the end date.');
      return false;
    } else {
      window.location.href =
        '../lib/l-ajax.php?function=AJAX_export_LoginRangeDetails&from=' +
        fromDate +
        '&to=' +
        toDate +
        '&level=' +
        level +
        '&leveltype=' +
        type +
        '&sublist=' +
        sublistval +
        '&csrfMagicToken=' +
        csrfMagicToken;
      $.notify('Login details have been successfully exported ');
      closePopUp();
      setTimeout(function () {
        $('#login-range').modal('hide');
        getLoginDetails();
        // location.href = 'loginAudit.php';
      }, 3200);
    }
  }
}

$('#submitRange').on('click', function () {
  var fromDate = $('#datefrom').val();
  var toDate = $('#dateto').val();
  var level = $('#levelId').val();

  var ret = validatePopup();
  if (ret) {
    $.ajax({
      url: '../lib/l-ajax.php?function=AJAX_getLoginRangeDetails',
      type: 'GET',
      data: { from: fromDate, to: toDate, level: level, csrfMagicToken: csrfMagicToken },
      dataType: 'json',
      success: function (gridData) {
        $('.se-pre-con').hide();
        $('#login-range').modal('hide');
        $('#logDetails').modal('show');
        $('#loginDetailsTable').DataTable().destroy();
        loginTable1 = $('#loginDetailsTable').DataTable({
          scrollY: jQuery('#loginDetailsTable').data('height'),
          scrollCollapse: true,
          paging: true,
          searching: false,
          ordering: true,
          aaData: gridData,
          bAutoWidth: false,
          select: false,
          bInfo: false,
          responsive: false,
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
          initComplete: function (settings, json) {},
        });
      },
      error: function (msg) {},
    });
  }
});

$('#exportDetails').on('click', function () {
  var fromDate = $('#datefrom').val();
  var toDate = $('#dateto').val();
  var level = $('#levelId').val();
  var ret = validatePopup();

  if (ret) {
    window.location.href =
      '../lib/l-ajax.php?function=AJAX_exportLoginRangeDetails&from=' +
      fromDate +
      '&to=' +
      toDate +
      '&level=' +
      level +
      '&csrfMagicToken=' +
      csrfMagicToken;
  }
});

$('.closebtn').click(function () {
  $('#levelId').val('');
});

function validatePopup() {
  var fromDate = $('#datefrom').val();
  var toDate = $('#dateto').val();
  var level = $('#levelId').val();
  $('#errorMsg').html('');
  $('#errorMsg').html('');
  $('#datefrom_err').html('');
  $('#dateto_err').html('');
  $('#errorMsg').show();

  if (fromDate == '') {
    $.notify('Please choose the Start date');
    return false;
  }
  if (toDate == '') {
    $.notify('Please choose the End date');
    return false;
  }

  var start = new Date(fromDate).getTime();
  var to = new Date(toDate).getTime();
  if (start >= to) {
    $.notify('Start Date is expected to be before the end date.');
    return false;
  } else {
    return true;
  }
}

$('#login-range').on('hidden.bs.modal', function () {
  $('.form-group input').val('');
  $('.form-group select').val('');
  $('.selectpicker').selectpicker('refresh');
});

$('#logDetails').on('shown.bs.modal', function (e) {
  $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
});

function exportdetails() {
  $('#login-range').show();
  $.ajax({
    url: '../lib/l-ajax.php',
    type: 'POST',
    data: { function: 'AJAX_get_UserDetails', csrfMagicToken: csrfMagicToken },
    success: function (data) {
      $('#UserSelection').html(data);
      $('.selectpicker').selectpicker('refresh');
    },
    error: function (error) {
      console.log('error');
    },
  });
}
