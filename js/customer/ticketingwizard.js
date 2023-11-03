/*
 * Ticketing Wizard JS functionality
 * @author SJ
 * @created 11-05-2020
 */

/*
 * Rough Notes
 * ***********
 * rightContainerSlideClose('site-add-container');
 * rightContainerSlideOn('edit-group');
 */

$(document).ready(function () {
  getTicketingDetails();
  getSiteList();

  $('input[type=checkbox]').click(function () {
    if ($(this).prop('checked') === true) {
      console.log('checked');
      $(this).val('1');
    } else {
      console.log('un checked');
      $(this).val('0');
    }
  });
});

function getTicketingDetails(nextPage = 1, notifSearch = '',  order = '', sort = '') {
  $('#absoBodyLoader').show();
  notifSearch = $('#notifSearch').val();

  checkAndUpdateActiveSortElement(order, sort);

  var dat = {
    function: 'getTicketEventDetails',
    csrfMagicToken: csrfMagicToken,
    limitCount: $('#notifyDtl_length :selected').val(),
    nextPage: nextPage,
    notifSearch: notifSearch,
    order: order,
    sort: sort,
  };
  $.ajax({
    url: '../lib/l-ticketingwiz.php',
    data: dat,
    type: 'POST',
    dataType: 'json',
    success: function (gridData) {
      $('#absoBodyLoader').hide();
      //  console.log(gridData)
      $('.se-pre-con').hide();
      $('#ticketingDataGrid').DataTable().destroy();
      ticketingDataGridTable = $('#ticketingDataGrid').DataTable({
        scrollY: 'calc(100vh - 240px)',
        scrollCollapse: true,
        searching: true,
        ordering: true,
        bAutoWidth: true,
        select: false,
        bInfo: false,
        responsive: true,
        stateSave: true,
        paging: false,
        bFilter: false,
        aaData: gridData.html,
        pagingType: 'full_numbers',
        stateSaveParams: function (settings, data) {
          data.search.search = '';
        },
        lengthMenu: [
          [10, 25, 50, 100],
          [10, 25, 50, 100],
        ],
        language: {
          info: 'Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>',
          search: '_INPUT_',
          searchPlaceholder: 'Search Records',
        },
        dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function (settings, json) {
          $('#dataincidentTable_filter').hide();
        },
        drawCallback: function (settings) {
          $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
          // $(".dataTables_filter:first").replaceWith('<div id="notifyDtl_filter" class="dataTables_filter"><label><input type="text" class="form-control form-control-sm" placeholder="Search records" value="' + notifSearch + '" id="notifSearch" aria-controls="notifyDtl"></label></div>');
          // $('.dataTables_scrollBody').mCustomScrollbar({
          //   theme: 'minimal-dark',
          // });
          // $('.equalHeight').matchHeight();
          $('.se-pre-con').hide();
        },
      });
      $('#dataincidentTable').on('click', 'tr', function () {
        var rowID = ticketingDataGridTable.row(this).data();
        console.log(JSON.stringify(rowID));
        var selected = rowID[0];
        $('#ticketid').val(selected);
        ticketingDataGridTable.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
      });

      $('#TicketTable_searchbox').keyup(function () {
        ticketingDataGridTable.search(this.value).draw();
      });
    },
    error: function (response) {
      console.log('Something went wrong with error : ' + response);
    },
  });
}

$('body').on('click', '.page-link', function () {
  var nextPage = $(this).data('pgno');
  const activeElement = window.currentActiveSortElement;
  const order = (activeElement) ? activeElement.sort : '';
  const sort = (activeElement) ? activeElement.type : '';
  getTicketingDetails(nextPage, '', order, sort);
});

$('body').on('change', '#notifyDtl_lengthSel', function () {
  getTicketingDetails();
});

$(document).on('keypress', function (e) {
  if (e.which == 13) {
    var notifSearch = $('#notifSearch').val();
    if (notifSearch != '') getTicketingDetails();
  }
});

function getSiteList() {
  $.ajax({
    url: '../lib/l-ticketingwiz.php',
    data: { function: 'getTicketSiteDetails', csrfMagicToken: csrfMagicToken },
    type: 'POST',
    success: function (data) {
      var sitedata = $.trim(data);
      $('#tw-customer').html(sitedata);
      $('.selectpicker').selectpicker('refresh');
    },
    error: function (err) {
      console.log('Error : ' + err);
    },
  });
}

function crmConfigure() {
  $('#tw-customer').prop('selectedIndex', 0);
  $('#tw-crmurl').val('');
  $('#tw-crmusername').val('');
  $('#tw-crmpassword').val('');
}

function getConfiguredCrmData() {
  var selectedSite = $('#tw-customer').val();

  $.ajax({
    url: '../lib/l-ticketingwiz.php',
    data: { function: 'getConfiguredCrmData', sitename: selectedSite, csrfMagicToken: csrfMagicToken },
    type: 'POST',
    success: function (data) {
      var crmdata = JSON.parse($.trim(data));
      $('#tw-crmurl').val(crmdata['crmUrl']);
      $('#tw-crmusername').val(crmdata['crmUsername']);
      $('#tw-crmpassword').val(atob(crmdata['crmPassword']));

      if (crmdata['tcktcreation'] === 'enabled') {
        $('#tw-tickEnable').prop('checked', true).val('1');
      } else {
        $('#tw-tickEnable').prop('checked', false).val('0');
      }
      if (crmdata['autoheal'] == '1') {
        $('#tw-tickAutoheal').prop('checked', true).val('1');
      } else {
        $('#tw-tickAutoheal').prop('checked', false).val('0');
      }
      if (crmdata['selfhelp'] == '1') {
        $('#tw-tickSelfhelp').prop('checked', true).val('1');
      } else {
        $('#tw-tickSelfhelp').prop('checked', false).val('0');
      }
      if (crmdata['schedule'] == '1') {
        $('#tw-tickSchedule').prop('checked', true).val('1');
      } else {
        $('#tw-tickSchedule').prop('checked', false).val('0');
      }
      // if (crmdata['notification'] == '1') {
      //   $('#tw-tickNotification').prop('checked', true).val('1');
      // } else {
      //   $('#tw-tickNotification').prop('checked', false).val('0');
      // }
      let notification = crmdata['notification'];
      if (notification.indexOf('1') >= 0) {
        $('#tw-tickNotificationP1').prop('checked', true).val('1');
      } else {
        $('#tw-tickNotificationP1').prop('checked', false).val('0');
      }
      if (notification.indexOf('2') >= 0) {
        $('#tw-tickNotificationP2').prop('checked', true).val('1');
      } else {
        $('#tw-tickNotificationP2').prop('checked', false).val('0');
      }
      if (notification.indexOf('3') >= 0) {
        $('#tw-tickNotificationP3').prop('checked', true).val('1');
      } else {
        $('#tw-tickNotificationP3').prop('checked', false).val('0');
      }

      $('#createJsonPayload').val(crmdata['jsonData']);
      $('#closedJsonPayload').val(crmdata['jsonCloseData']);
    },
    error: function (err) {
      console.log('Error : ' + err);
    },
  });
}

function validateCrmDetails(customer, crmurl, crmusername, crmpassword) {
  var errmsg = '';
  if (customer == '') {
    errmsg = 'Please select a customer';
  } else if (crmurl == '') {
    errmsg = 'Please enter the crm url';
  } else if (crmusername == '') {
    errmsg = 'Please enter the crm username';
  } else if (crmpassword == '') {
    errmsg = 'Please enter the crm password';
  }
  if (errmsg != '') {
    $.notify(errmsg);
    return false;
  }
}

function configureCRM() {
  var crmtype = $('#tw-crmtype').val();
  var customer = $('#tw-customer').val();
  var crmurl = $('#tw-crmurl').val();
  var crmusername = $('#tw-crmusername').val();
  var crmpassword = $('#tw-crmpassword').val();
  var tickEnable = $('#tw-tickEnable').val();
  // var tickAutoheal = $('#tw-tickAutoheal').val();
  // var tickSelfhelp = $('#tw-tickSelfhelp').val();
  // var tickSchedule = $('#tw-tickSchedule').val();
  // var tickNotification = $('#tw-tickNotification').val();

  var tickAutoheal = 0;
  if ($('#tw-tickAutoheal').prop('checked')){
    tickAutoheal = 1;
  }
  var tickSelfhelp = 0;
  if ($('#tw-tickSelfhelp').prop('checked')){
    tickSelfhelp = 1;
  }
  var tickSchedule = 0;
  if ($('#tw-tickSchedule').prop('checked')){
    tickSchedule = 1;
  }
  var tickNotification = "";
  if ($('#tw-tickNotificationP1').prop('checked')){
    if (tickNotification == ""){
      tickNotification += "1";
    }
  }
  if ($('#tw-tickNotificationP2').prop('checked')){
    if (tickNotification == ""){
      tickNotification += "2";
    }else{
      tickNotification += ",2";
    }
  }
  if ($('#tw-tickNotificationP3').prop('checked')){
    if (tickNotification == ""){
      tickNotification += "3";
    }else{
      tickNotification += ",3";
    }
  }


  validateCrmDetails(customer, crmurl, crmusername, crmpassword);

  var crmdata = {
    function: 'configureCRMDetails',
    crmtype: crmtype,
    customer: customer,
    crmurl: crmurl,
    crmusername: crmusername,
    crmpassword: btoa(crmpassword),
    tickEnable: tickEnable,
    tickAutoheal: tickAutoheal,
    tickSelfhelp: tickSelfhelp,
    tickSchedule: tickSchedule,
    tickNotification: tickNotification,
    csrfMagicToken: csrfMagicToken,
  };

  $.ajax({
    url: '../lib/l-ticketingwiz.php',
    data: crmdata,
    type: 'POST',
    success: function (data) {
      var crmres = JSON.parse(data);
      if (crmres['rmsg'] == 'success') {
        $('#crmcustomer').val(customer);
        $.notify('CRM details has been configured successfully.');
        rightContainerSlideClose('ticketing-configuration');
        //rightContainerSlideOn('ticketing-payload');
      } else {
        $.notify('Failed to configure CRM details.');
      }
    },
    error: function (err) {},
  });
}

function savePayloadInformation() {
  var customer = $('#crmcustomer').val();
  var createJsonPayload = $('#createJsonPayload').val();
  var closedJsonPayload = $('#closedJsonPayload').val();

  var payloadjsondata = {
    function: 'configureJsonPayload',
    customer: customer,
    createjson: createJsonPayload,
    closedjson: closedJsonPayload,
    csrfMagicToken: csrfMagicToken,
  };

  $.ajax({
    url: '../lib/l-ticketingwiz.php',
    data: payloadjsondata,
    type: 'POST',
    success: function (data) {
      var crmres = JSON.parse(data);
      if (crmres['rmsg'] == 'success') {
        $.notify('Payload details has been configured successfully.');
        rightContainerSlideClose('ticketing-payload');
      } else {
        $.notify('Failed to configure payload details. Please try again...');
      }
    },
    error: function (err) {},
  });
}

function viewJsonPayloadData(jsontype) {
  var payloaddata = {
    function: 'getPayloadData',
    type: jsontype,
    csrfMagicToken: csrfMagicToken,
  };

  $.ajax({
    url: '../lib/l-ticketingwiz.php',
    data: payloaddata,
    type: 'POST',
    success: function (data) {
      var payloadres = JSON.parse(data);
      if (payloadres['rmsg'] == 'success') {
        if (jsontype == 'create') {
          rightContainerSlideOn('ticketing-createjson');
          $('#createJsonPayloadData').html(payloadres['data']['jsonData']);
        } else {
          rightContainerSlideOn('ticketing-closedjson');
          $('#closedJsonPayloadData').html(payloadres['data']['jsonCloseData']);
        }
      } else {
        $.notify('Payload has been not configurd yet.');
      }
    },
    error: function (err) {},
  });
}

// function actionDetails() {
//   var sel_action = $('#selected').val();
//   if (sel_action == '' || typeof sel_action == 'undefined') {
//     $.notify('Please select a record to get action details');
//   } else {
//     // Show Action Details
//   }
// }

// function exportTicketingDetails() {
//   $.notify('No Ticketing data to export!');
// }

$('#resolutionTitleBlock').click(function () {
  if ($('#resolutionTitleBlockI').hasClass('fa-angle-down')){
    $('#resolutionTitleBlockI').removeClass('fa-angle-down');
    $('#resolutionTitleBlockI').addClass('fa-angle-up');
    $('#resolutionBlock').css('display','block');
  }else{
    $('#resolutionTitleBlockI').removeClass('fa-angle-up');
    $('#resolutionTitleBlockI').addClass('fa-angle-down');
    $('#resolutionBlock').css('display','none');
  }
})

$('#notificationTitleBlock').click(function () {
  if ($('#notificationTitleBlockI').hasClass('fa-angle-down')){
    $('#notificationTitleBlockI').removeClass('fa-angle-down');
    $('#notificationTitleBlockI').addClass('fa-angle-up');
    $('#notificationBlock').css('display','block');
  }else{
    $('#notificationTitleBlockI').removeClass('fa-angle-up');
    $('#notificationTitleBlockI').addClass('fa-angle-down');
    $('#notificationBlock').css('display','none');
  }
})
