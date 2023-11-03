/**
 * This file belongs to "Commercial/MSP" Customers only.
 * This file is created for all provisioning functionality for "Commercial/MSP" flow.
 * In this file "msp" indicates "managed service provider" or "Commercial" bussiness flow.
 */

$(document).ready(function () {
  get_CommercialCustomers();
  getUserRoles();
});

function makeAjaxCall(url, data, responseType) {
  var ajaxResponse;
  $.ajax({
    url: url + '&csrfMagicToken=' + csrfMagicToken,
    data: data,
    async: false,
    dataType: responseType,
    success: function (response) {
      ajaxResponse = response;
    },
    error: function () {},
  });
  return ajaxResponse;
}

function getAvailableLicensesCount() {
  $.ajax({
    url: '../lib/l-msp.php',
    data: 'function=MSP_GetAvailableLicenseCount' + '&csrfMagicToken=' + csrfMagicToken,
    async: false,
    dataType: 'json',
    success: function (response) {
      $('#availableLicenses').val(parseInt($.trim(response.availableCnt)));
    },
    error: function () {},
  });
  return true;
}

/**
 * Fetch all customers list in json format which is required for Datatable.
 */
function get_CommercialCustomers() {
  $('#msp_Customer_Grid').dataTable().fnDestroy();
  customerGrid = $('#msp_Customer_Grid').DataTable({
    scrollY: jQuery('#msp_Customer_Grid').data('height'),
    scrollCollapse: true,
    autoWidth: false,
    serverSide: false,
    searching: true,
    bAutoWidth: true,
    responsive: true,
    ordering: true,
    stateSave: true,
    stateSaveParams: function (settings, data) {
      data.search.search = '';
    },
    columnDefs: [
      {
        targets: 'datatable-nosort',
        orderable: false,
      },
      {
        className: 'ignore',
        targets: [0],
      },
    ],
    ajax: {
      url: '../lib/l-msp.php?function=MSP_GetCustomerGrid',
      type: 'POST',
    },
    columns: [{ data: 'customer' }, { data: 'status' }],
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, 'All'],
    ],
    language: {
      info: '_START_-_END_ of _TOTAL_ entries',
      search: '_INPUT_',
      searchPlaceholder: 'Search records',
    },
    dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
    initComplete: function (settings, json) {
      customerGrid.$('tr:first').click();
    },
    drawCallback: function (settings) {
      //                $(".dataTables_scrollBody").mCustomScrollbar({
      //                    theme: "minimal-dark"
      //                });
    },
  });

  $('#msp_Customer_Grid tbody').on('click', 'tr', function () {
    customerGrid.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
    var id = customerGrid.row(this).id();
    enableOptions(id);
  });

  $('#customer_searchbox').keyup(function () {
    //group search code
    customerGrid.search(this.value).draw();
  });
}

function enableOptions(rowId) {
  var sel_CustomerStatus = getCustomerId(rowId, 0);
  $('#enableCustomer_Li').hide();
  $('#disableCustomer_Li').hide();
  if (sel_CustomerStatus === 0 || sel_CustomerStatus === '0') {
    $('#enableCustomer_Li').show();
    $('#disableCustomer_Li').hide();
  } else if (sel_CustomerStatus === 1 || sel_CustomerStatus === '1') {
    $('#enableCustomer_Li').hide();
    $('#disableCustomer_Li').show();
  }
  var sel_CustomerEid = getCustomerId(rowId, 1);

  $('#msp_Sites_Grid').dataTable().fnDestroy();
  sitesGrid = $('#msp_Sites_Grid').DataTable({
    scrollY: jQuery('#msp_Sites_Grid').data('height'),
    scrollCollapse: true,
    autoWidth: false,
    serverSide: false,
    searching: true,
    bAutoWidth: true,
    responsive: true,
    ordering: true,
    stateSave: true,
    stateSaveParams: function (settings, data) {
      data.search.search = '';
    },
    columnDefs: [
      {
        targets: 'datatable-nosort',
        orderable: false,
      },
      {
        className: 'ignore',
        targets: [0],
      },
    ],
    ajax: {
      url: '../lib/l-msp.php?function=MSP_GetCustomerSitesGrid&custid=' + sel_CustomerEid,
      type: 'POST',
    },
    columns: [{ data: 'sites' }, { data: 'installCount' }],
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, 'All'],
    ],
    language: {
      info: '_START_-_END_ of _TOTAL_ entries',
      searchPlaceholder: 'Search',
    },
    dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
    initComplete: function (settings, json) {
      sitesGrid.$('tr:first').click();
    },
    drawCallback: function (settings) {
      //                $(".dataTables_scrollBody").mCustomScrollbar({
      //                    theme: "minimal-dark"
      //                });
    },
  });
  $('#msp_Sites_Grid tbody').off('click');
  $('#msp_Sites_Grid tbody').on('click', 'tr', function () {
    sitesGrid.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
    var id = sitesGrid.row(this).id();
    getSitesList(id);
  });
}

function getSitesList(rowId) {
  var sel_customerNum = getCustomerId(rowId, 0);
  var sel_orderNum = getCustomerId(rowId, 1);
  var sel_compId = getCustomerId(rowId, 2);
  var sel_procId = getCustomerId(rowId, 3);

  $('#msp_Device_Grid').dataTable().fnDestroy();
  deviceGrid = $('#msp_Device_Grid').DataTable({
    scrollY: jQuery('#msp_Device_Grid').data('height'),
    scrollCollapse: true,
    autoWidth: false,
    serverSide: false,
    searching: true,
    bAutoWidth: true,
    responsive: true,
    ordering: true,
    stateSave: true,
    stateSaveParams: function (settings, data) {
      data.search.search = '';
    },
    columnDefs: [
      {
        targets: 'datatable-nosort',
        orderable: false,
      },
      {
        className: 'ignore',
        targets: [0, 1, 2],
      },
    ],
    ajax: {
      url:
        '../lib/l-msp.php?function=MSP_GetSitesDeviceGrid&custId=' +
        sel_compId +
        '&procId=' +
        sel_procId +
        '&custNum=' +
        sel_customerNum +
        '&ordNum=' +
        sel_orderNum,
      type: 'POST',
    },
    columns: [{ data: 'devicename' }, { data: 'installDt' }, { data: 'status' }],
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, 'All'],
    ],
    language: {
      info: '_START_-_END_ of _TOTAL_ entries',
      searchPlaceholder: 'Search',
    },
    dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
    initComplete: function (settings, json) {},
    drawCallback: function (settings) {
      //                $(".dataTables_scrollBody").mCustomScrollbar({
      //                    theme: "minimal-dark"
      //                });
    },
  });

  $('#msp_Device_Grid tbody').on('click', 'tr', function () {
    deviceGrid.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
    var id = deviceGrid.row(this).id();
    //enableOptions(id);
  });

  //    $("#customer_searchbox").keyup(function () {//group search code
  //        deviceGrid.search(this.value).draw();
  //    });
}

function isTrialReseller() {
  $.ajax({
    url: '../lib/l-msp.php?function=MSP_IsTrialReseller' + '&csrfMagicToken=' + csrfMagicToken,
    processData: false,
    contentType: false,
    type: 'POST',
    dataType: 'text',
    success: function (response) {
      if ($.trim(response) === 'NOT_TRIAL') {
        $('#warning_modal').modal('hide');
        $('#msp_CreateCustomer').modal('show');
      } else {
        $('#warning_msg').html('You are in trial period, please buy nanoheal licenses to create customers');
        $('#warning_modal').modal('show');
        $('#msp_CreateCustomer').modal('hide');
      }
    },
    error: function (response) {
      $('#addNewSite_error').html('Error Occurred');
      console.log('Error In create_Customer function : ' + response);
    },
  });
}

/*
 *----------------------------------------------------------------------------------------------------------------------
 * It will create site for MSP customer.
 *----------------------------------------------------------------------------------------------------------------------
 */
function create_Customer() {
  var errorVal = 0;
  errorVal = validateAddCustomerForm();

  if (errorVal === 0) {
    var m_data = new FormData();

    //Customer's company's details.
    m_data.append('custCompName', $('#cust_companyName').val());
    m_data.append('custCompAddr', $('#cust_companyAddr').val());
    m_data.append('custCompCity', $('#cust_companyCity').val());
    m_data.append('custCompState', $('#cust_companyState').val());
    m_data.append('custCompZipcode', $('#cust_companyZip').val());
    m_data.append('custCompWebsite', $('#cust_compWeb').val());

    //Customer's details.
    m_data.append('custFirstName', $('#cust_firstName').val());
    m_data.append('custLastName', $('#cust_lastName').val());
    m_data.append('custEmail', $('#cust_email').val());
    m_data.append('custLicence', $('#cust_licence').val());
    m_data.append('custRole', $('#cust_roleId option:selected').val());

    //License details.
    m_data.append('orderNumber', $('#orderList').val());
    m_data.append('pcCnt', $('#pcCnt').val());
    m_data.append('csrfMagicToken', csrfMagicToken);
    $('#add_CustomerMsg').html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
    $.ajax({
      url: '../lib/l-msp.php?function=MSP_CreateCustomer',
      data: m_data,
      processData: false,
      contentType: false,
      type: 'POST',
      dataType: 'json',
      success: function (response) {
        if ($.trim(response.status) == 'success') {
          $('#add_successMsg').html($.trim(response.msg));
          $('#new_cust_download_url').val($.trim(response.link));
          $('#msp_CreateCustomer').modal('hide');
          $('#msp_CreateCustomerLink').modal('show');
          get_CommercialCustomers();
          $.notify('Customer has been successfully created');
          rightContainerSlideClose('add-customer');
        } else {
          $('#msp_CreateCustomer').modal('show');
          $('#msp_CreateCustomerLink').modal('hide');
          $('#add_CustomerMsg').html($.trim(response.msg));
        }
      },
      error: function (response) {
        $('#addNewSite_error').html('Error Occurred');
        console.log('Error In create_Customer function : ' + response);
      },
    });
  }
}

function validateAddCustomerForm() {
  $('.error').html(' *');
  var errorVal = 0;

  $('.addCustRequired').each(function () {
    var field_id = this.id;
    var field_value = $('#' + field_id).val();

    if ($.trim(field_value) === '') {
      $('#required_' + field_id)
        .css('color', 'red')
        .html(' required');
      errorVal++;
    } else if (field_id == 'cust_companyName') {
      if (!validate_alphanumeric_underscore(field_value)) {
        errorVal++;
        $('#required_' + field_id)
          .css('color', 'red')
          .html('Enter only Alphanumeric values');
      }
    } else if (field_id == 'cust_firstName') {
      if (!validate_Name(field_value)) {
        errorVal++;
        $('#required_' + field_id)
          .css('color', 'red')
          .html('Enter only Alphabet values');
      }
    } else if (field_id == 'cust_lastName') {
      if (!validate_Name(field_value)) {
        errorVal++;
        $('#required_' + field_id)
          .css('color', 'red')
          .html('Enter only Alphabet values');
      }
    } else if (field_id == 'cust_email') {
      if (!validate_Email(field_value)) {
        errorVal++;
        $('#required_' + field_id)
          .css('color', 'red')
          .html(' Enter valid email');
      }
    } else if (field_id == 'pcCnt') {
      if (field_value < 0) {
        errorVal++;
        $('#required_' + field_id)
          .css('color', 'red')
          .html(' Enter valid no of pc');
      }
    } else if (field_id == 'cust_companyZip') {
      if (!validate_ZipCode(field_value)) {
        errorVal++;
        $('#required_' + field_id)
          .css('color', 'red')
          .html('Enter only numeric values');
      }
    } else if (field_id == 'cust_licence' && (field_value != '' || !empty(field_value))) {
      if (!validate_ZipCode(field_value)) {
        errorVal++;
        $('#required_' + field_id)
          .css('color', 'red')
          .html('Enter only numeric values');
      } else if (field_value < 1 || field_value === 0) {
        errorVal++;
        $('#required_' + field_id)
          .css('color', 'red')
          .html('Enter Minimum Licence Count');
      } else if (field_value > 1000) {
        errorVal++;
        $('#required_' + field_id)
          .css('color', 'red')
          .html('Maximum Of 1000 Licences Allowed,Please Enter Other Licence Count Value');
      }
      //}
    }
  });
  return errorVal;
}

$('#copy_link1').click(function () {
  var urlField = document.querySelector('#new_cust_download_url');
  urlField.select();
  document.execCommand('copy');
});

$('#editCustomer_Li a').click(function () {
  var sel_Customer = $('#msp_Customer_Grid tbody tr.selected').attr('id');
  var sel_CustomerId = getCustomerId(sel_Customer, 0);
  var sel_CustomerEid = getCustomerId(sel_Customer, 2);
  $('#hidden_customerId').val($.trim(sel_CustomerId));
  $('#hidden_Eid').val($.trim(sel_CustomerEid));

  var m_data = new FormData();
  m_data.append('cust_id', sel_CustomerId);
  m_data.append('csrfMagicToken', csrfMagicToken);

  $.ajax({
    url: '../lib/l-msp.php?function=MSP_Get_MSPCustomerDetails',
    data: m_data,
    processData: false,
    contentType: false,
    type: 'POST',
    dataType: 'json',
    success: function (response) {
      $('#edit_companyName').val($.trim(response.companyName));
      $('#edit_pcCnt').val($.trim(response.noOfPc));
      $('#edit_firstName').val($.trim(response.firstName));
      $('#edit_lastName').val($.trim(response.lastName));

      $('#edit_email').val($.trim(response.emailId));
      if (!validate_Email($.trim(response.emailId))) {
        $('#edit_email').attr('readonly', false);
        $('#hidden_trialSiteEmail').val('1');
      } else {
        $('#edit_email').attr('readonly', true);
        $('#hidden_trialSiteEmail').val('0');
      }

      toggleReadonlyAttribute('edit_compWeb', $.trim(response.website));
      toggleReadonlyAttribute('edit_companyAddr', $.trim(response.address));
      toggleReadonlyAttribute('edit_companyCity', $.trim(response.city));
      toggleReadonlyAttribute('edit_companyState', $.trim(response.province));
      toggleReadonlyAttribute('edit_companyZip', $.trim(response.zipCode));

      $('#edit_CustomerMsg').html('');
    },
    error: function (response) {},
  });
});

/*
 *----------------------------------------------------------------------------------------------------------------------
 * It will create site for MSP customer.
 *----------------------------------------------------------------------------------------------------------------------
 */
function edit_Customer() {
  var errorVal = 0;
  errorVal = validateEditCustomerForm();

  if (errorVal === 0) {
    var m_data = new FormData();
    m_data.append('customerId', $('#hidden_customerId').val());
    m_data.append('customerEid', $('#hidden_Eid').val());
    m_data.append('edit_pcCnt', $('#edit_pcCnt').val());
    m_data.append('edit_compWeb', $('#edit_compWeb').val());
    m_data.append('edit_companyAddr', $('#edit_companyAddr').val());
    m_data.append('edit_companyCity', $('#edit_companyCity').val());
    m_data.append('edit_companyState', $('#edit_companyState').val());
    m_data.append('edit_companyZip', $('#edit_companyZip').val());
    m_data.append('edit_firstName', $('#edit_firstName').val());
    m_data.append('edit_lastName', $('#edit_lastName').val());
    m_data.append('edit_email', $('#edit_email').val());
    m_data.append('trialSiteEmail', $('#hidden_trialSiteEmail').val());
    m_data.append('csrfMagicToken', csrfMagicToken);

    $('#edit_CustomerMsg').html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." />');
    $.ajax({
      url: '../lib/l-msp.php?function=MSP_UpdateCustomer',
      data: m_data,
      processData: false,
      contentType: false,
      type: 'POST',
      dataType: 'json',
      success: function (response) {
        if (response.status === 'success') {
          $('#edit_CustomerMsg').css('color', 'green').html(response.msg);
          setInterval(function () {
            $('#msp_EditCustomer').modal('hide');
            location.reload();
          }, 2000);
        } else {
          $('#edit_CustomerMsg').html(response.msg);
        }
      },
      error: function (response) {
        $('#edit_CustomerMsg').html('Error Occurred');
        console.log('Error In create_Customer function : ' + response);
      },
    });
  }
}

function validateEditCustomerForm() {
  $('.error').html(' *');
  var errorVal = 0;

  $('.editCustRequired').each(function () {
    var field_id = this.id;
    var field_value = $('#' + field_id).val();

    if ($.trim(field_value) === '') {
      $('#required_' + field_id)
        .css('color', 'red')
        .html(' required');
      errorVal++;
    } else if (field_id == 'edit_firstName') {
      if (!validate_Name(field_value)) {
        $('#required_' + field_id)
          .css('color', 'red')
          .html('Enter only Alphabet values');
        errorVal++;
      }
    } else if (field_id == 'edit_lastName') {
      if (!validate_Name(field_value)) {
        $('#required_' + field_id)
          .css('color', 'red')
          .html('Enter only Alphabet values');
        errorVal++;
      }
    } else if (field_id == 'edit_email') {
      if (!validate_Email(field_value)) {
        $('#required_' + field_id)
          .css('color', 'red')
          .html(' Enter valid email');
        errorVal++;
      }
    } else if (field_id == 'pcCnt') {
      if (field_value < 0) {
        $('#required_' + field_id)
          .css('color', 'red')
          .html(' Enter valid no of pc');
        errorVal++;
      }
    }
  });
  return errorVal;
}

/**
 * Fetch all customers list in json format which is required for Datatable.
 */
function get_CustomerDetails() {
  var sel_Customer = $('#msp_Customer_Grid tbody tr.selected').attr('id');
  var sel_CustomerId = getCustomerId(sel_Customer, 0);
  $('#customer_detailsGrid').dataTable().fnDestroy();
  customerGrid = $('#customer_detailsGrid').DataTable({
    scrollY: jQuery('#customer_detailsGrid').data('height'),
    scrollCollapse: true,
    autoWidth: false,
    serverSide: false,
    searching: false,
    bAutoWidth: true,
    responsive: true,
    ordering: true,
    stateSave: true,
    stateSaveParams: function (settings, data) {
      data.search.search = '';
    },
    columnDefs: [
      {
        targets: 'datatable-nosort',
        orderable: false,
      },
      {
        className: 'ignore',
        targets: [0, 1, 2, 3],
      },
      { width: '2%', targets: 1 },
    ],
    ajax: {
      url: '../lib/l-msp.php?function=MSP_GetCustomerDetailGrid&customerId=' + sel_CustomerId,
      type: 'POST',
    },
    columns: [{ data: 'order' }, { data: 'email' }, { data: 'endDate' }, { data: 'link' }],
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, 'All'],
    ],
    language: {
      info: '_START_-_END_ of _TOTAL_ entries',
      searchPlaceholder: 'Search',
    },
    dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
    initComplete: function (settings, json) {},
    drawCallback: function (settings) {
      //                $(".dataTables_scrollBody").mCustomScrollbar({
      //                    theme: "minimal-dark"
      //                });
    },
  });

  $('#msp_Customer_Grid tbody').on('click', 'tr', function () {
    customerGrid.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
    var id = customerGrid.row(this).id();
    enableOptions(id);
  });
}

function customer_link(url) {
  var modal = $('#msp_CreateCustomerLink').modal('show');
  modal.css({ zIndex: 10000 });
  $('#add_successMsg').html('Please click on copy button to copy url');
  $('#new_cust_download_url').val($.trim(url));
}

function disable_Customer() {
  var rowId = $('#msp_Customer_Grid tbody tr.selected').attr('id');
  var channelID = getCustomerId(rowId, 1);

  if (rowId === undefined || rowId === 'undefined') {
    $('#warning_msg').html('Please select customer');
    $('#warning_modal').modal('show');
  } else {
    $.ajax({
      type: 'GET',
      dataType: 'text',
      url: '../lib/l-msp.php',
      data: 'function=MSP_DisableCustomer&chId=' + channelID + '&csrfMagicToken=' + csrfMagicToken,
      success: function (result) {
        var msg = $.trim(result);
        if (msg === 'done') {
          $('#disable_msg').html('Customer account disabled successfully.');
          setTimeout(function () {
            $('#disable_site').modal('hide');
            get_CommercialCustomers();
          }, 2000);
        } else {
          $('#disable_msg').html('Fail to disabled customer account.');
        }
      },
    });
  }
  return true;
}

function enable_Customer() {
  var rowId = $('#msp_Customer_Grid tbody tr.selected').attr('id');
  var channelID = getCustomerId(rowId, 1);

  if (channelID === undefined || channelID === 'undefined') {
    $('#warning_msg').html('Please select customer');
    $('#warning_modal').modal('show');
  } else {
    $.ajax({
      type: 'GET',
      dataType: 'text',
      url: '../lib/l-msp.php',
      data: 'function=MSP_EnableCustomer&chId=' + channelID + '&csrfMagicToken=' + csrfMagicToken,
      success: function (result) {
        var msg = $.trim(result);
        if (msg === 'done') {
          $('#enable_msg').html('Customer account enabled successfully.');
          setTimeout(function () {
            $('#enable_site').modal('hide');
            get_CommercialCustomers();
          }, 2000);
        } else {
          $('#enable_msg').html('Fail to enabled customer account.');
        }
      },
    });
  }

  return true;
}

function toggleReadonlyAttribute(fieldId, fieldValue) {
  $('#' + fieldId).val(fieldValue);
  if (fieldValue === '' || fieldValue === undefined || fieldValue === '0' || fieldValue === '-') {
    $('#' + fieldId).attr('readonly', false);
  } else {
    $('#' + fieldId).attr('readonly', true);
  }
  return true;
}

function get_RenewDevices() {
  var rowId = $('#msp_Customer_Grid tbody tr.selected').attr('id');
  var compId = getCustomerId(rowId, 2);
  var customerNumber = getCustomerId(rowId, 3);
  $('#renewDevicesGrid').dataTable().fnDestroy();
  renewGrid = $('#renewDevicesGrid').DataTable({
    scrollY: jQuery('#renewDevicesGrid').data('height'),
    scrollCollapse: true,
    autoWidth: false,
    serverSide: false,
    searching: false,
    bAutoWidth: true,
    responsive: true,
    ordering: true,
    stateSave: true,
    stateSaveParams: function (settings, data) {
      data.search.search = '';
    },
    columnDefs: [
      {
        targets: 'datatable-nosort',
        orderable: false,
      },
      {
        className: 'ignore',
        targets: [1, 2, 3],
      },
      {
        className: 'checkbox-btn',
        targets: [0],
      },
    ],
    ajax: {
      url: '../lib/l-msp.php?function=MSP_RenewDevices&compId=' + compId + '&customerNumber=' + customerNumber,
      type: 'POST',
    },
    columns: [{ data: 'checkBox' }, { data: 'order' }, { data: 'device' }, { data: 'insDate' }, { data: 'uninsDate' }],
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, 'All'],
    ],
    language: {
      info: '_START_-_END_ of _TOTAL_ entries',
      searchPlaceholder: 'Search',
    },
    dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
    initComplete: function (settings, json) {},
    drawCallback: function (settings) {
      //                $(".dataTables_scrollBody").mCustomScrollbar({
      //                    theme: "minimal-dark"
      //                });
      $('#msp_renewDevices').modal('show');
      setRenewCheckEvent();
    },
  });
}

function setRenewCheckEvent() {
  $('.renewCheck').on('change', function () {
    var availableLicense = $('#availableLicenses').val();
    if ($('.renewCheck:checked').length > availableLicense) {
      $('#renew_Msg').html('You have selected more devices than avaialble licenses');
    } else {
      $('#renew_Msg').html('');
    }
  });
}

$('#renewDeviceButton').click(function () {
  var rowId = $('#msp_Customer_Grid tbody tr.selected').attr('id');
  var customerNumber = getCustomerId(rowId, 3);
  var compId = getCustomerId(rowId, 4);
  var procId = getCustomerId(rowId, 5);
  var siteName = getCustomerId(rowId, 6);

  var allDevices = [];
  var allOrders = [];
  $('.renewCheck:checked').each(function () {
    var sid = getCustomerId($(this).val(), 0);
    var orderNum = getCustomerId($(this).val(), 1);

    if ($.inArray(sid, allDevices) == -1) {
      allDevices.push(sid);
    }

    if ($.inArray(orderNum, allOrders) == -1) {
      allOrders.push(orderNum);
    }
  });

  var form_data = new FormData();
  form_data.append('function', 'MSP_RenewSelectedDevices');
  form_data.append('selecDevices', allDevices);
  form_data.append('customerNumber', customerNumber);
  form_data.append('orderNumber', allOrders);
  form_data.append('compId', compId);
  form_data.append('pid', procId);
  form_data.append('siteName', siteName);
  form_data.append('csrfMagicToken', csrfMagicToken);

  $.ajax({
    type: 'POST',
    dataType: 'json',
    url: '../lib/l-msp.php',
    data: form_data,
    processData: false,
    contentType: false,
    success: function (result) {
      if (result.success == 'success') {
        $('#renew_Msg').html('Selected devices renewed successfully');
      } else {
        $('#renew_Msg').css('color', 'green').html(result.msg);
      }
    },
  });
});

function getCustomerId(selectedId, index) {
  var custRowId = selectedId.split('---');
  var cust_id = custRowId[index];
  return cust_id;
}

$('#exportAllCustomers').click(function () {
  location.href = '../lib/l-msp.php?function=MSP_ExportAllCustomers';
  closePopUp();
});

//###################################### Boostrap Modal CLOSE/OPEN Events Start ################################################//
$('#msp_CustomerDetails').on('shown.bs.modal', function () {
  $('#customer_detailsGrid').DataTable().columns.adjust().draw();
});

$('#msp_renewDevices').on('shown.bs.modal', function () {
  getAvailableLicensesCount();
  $('#renewDevicesGrid').DataTable().columns.adjust().draw();
});

$('#msp_CreateCustomer').on('hidden.bs.modal', function () {
  $('#msp_CreateCustomer input[type=text]').not('[readonly]').val('');
  $('#msp_CreateCustomer .error').html('*');
  $('#cust_licence').val(5);
  $('#add_CustomerMsg').html('');
});

$('#msp_EditCustomer').on('hidden.bs.modal', function () {
  $('#msp_EditCustomer input[type=text]').not('[readonly]').val('');
  $('#edit_CustomerMsg .error').html('*');
  $('#edit_CustomerMsg').html('<img src="../vendors/images/loader2.gif" class="loading orders" alt="loading..." />');
});

$('#msp_CustomerDetails').on('hidden.bs.modal', function () {
  location.reload();
});

$('#enable_site').on('hidden.bs.modal', function () {
  $('#enable_msg').html('');
});

$('#disable_site').on('hidden.bs.modal', function () {
  $('#disable_msg').html('');
});

$('#warning_modal').on('hidden.bs.modal', function () {
  $('#warning_msg').html('');
});

//###################################### Boostrap Modal CLOSE/OPEN Events End ################################################//

/* client logo upload code */
function uploadclientLogo() {
  var rowId = $('#msp_Customer_Grid tbody tr.selected').attr('id');
  var eid = getCustomerId(rowId, 2);
  var filename = $('#fileuploader2').val();

  var m_data = new FormData();
  m_data.append('eid', eid);
  m_data.append('upload_logo', $('input[name=fileuploader2]')[0].files[0]); // left pane logo
  m_data.append('csrfMagicToken', csrfMagicToken);

  if (filename != '') {
    $.ajax({
      type: 'post',
      url: '../lib/l-msp.php?function=MSP_UploadClientLogo',
      processData: false, // important
      contentType: false, // important
      data: m_data,
      dataType: 'json',
      success: function (data) {
        if (data.msg == 'valid') {
          $('#logosuccessmsg').html('<span style="color:green;"> Logo uploaded successfully </span>');
          $('#logosuccessmsg').fadeOut(3000);
        } else {
          $('#logosuccessmsg').html('<span style="color:red;"> Logo Not uploaded successfully </span>');
          $('#logosuccessmsg').fadeOut(3000);
        }

        setTimeout(function () {
          debugger;
          location.href = 'customer.php';
        }, 3200);
      },
      error: function (data) {
        alert('error');
      },
    });
  } else {
    $('#logosuccessmsg').html('<span style="color:red;"> Please Select Image To Upload </span>');
    $('#logosuccessmsg').show();
    //        $('#logosuccessmsg').fadeOut(3000);
  }
}

$('#uploadlogo').mousedown(function () {
  $('#logosuccessmsg').hide();
});

function get_custDnlURL() {
  var rowId = $('#msp_Customer_Grid tbody tr.selected').attr('id');
  //    var channelID = getCustomerId(rowId, 1);

  //    if (rowId === undefined || rowId === 'undefined') {
  //        $("#warning_msg").html("Please select customer");
  //        $("#warning_modal").modal('show');
  //    } else {
  var channelID = 4; //getCustomerId(rowId, 1);
  $('#msp_DwnlCustomerURL').modal('show');
  $.ajax({
    type: 'GET',
    dataType: 'json',
    url: '../lib/l-msp.php',
    data: 'function=MSP_GetCustDnlDetails&chId=' + channelID + '&csrfMagicToken=' + csrfMagicToken,
    success: function (result) {
      $('#downloadGrid').DataTable().destroy();
      downloadTable = $('#downloadGrid').DataTable({
        scrollY: jQuery('#downloadGrid').data('height'),
        scrollCollapse: true,
        paging: true,
        searching: false,
        ordering: false,
        aaData: result,
        bAutoWidth: true,
        select: false,
        bInfo: true,
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
        initComplete: function (settings, json) {
          $('#downloadGrid_paginate').hide();
        },
        //                    drawCallback: function(settings) {
        //                        $(".dataTables_scrollBody").mCustomScrollbar({
        //                            theme: "minimal-dark"
        //                        });
        //                        $('.equalHeight').matchHeight();
        //                    }
      });
    },
    error: function (msg) {},
  });
  //    }
  return true;
}
function getUserRoles() {
  $.ajax({
    type: 'POST',
    url: '../lib/l-custAjax.php',
    // data: "function=CUSAJX_GetAllUserRoles" + "&csrfMagicToken=" + csrfMagicToken,
    data: { function: 'CUSAJX_GetAllUser_Roles', csrfMagicToken: csrfMagicToken },

    success: function (response) {
      $('#cust_roleId').html(response);
      $('.selectpicker').selectpicker('refresh');
      $('#edit_advroleId').html(response);
      $('.selectpicker').selectpicker('refresh');
    },
  });
}

function showCustomerDetailsPopup() {
  $('#new-customer').hide();
  $('#button_div').hide();
  $('#detail-customer').show();
}

function inviteCustomer() {
  $('#new-customer').show();
  $('#button_div').show();
  $('#detail-customer').hide();
}

function addcust() {
  $('#cust_companyName').val('');
  $('#cust_compWeb').val('');
  $('#cust_companyAddr').val('');
  $('#cust_companyCity').val('');
  $('#cust_firstName').val('');
  $('#cust_companyState').val('');
  $('#cust_lastName').val('');
  $('#cust_companyZip').val('');
  $('#cust_email').val('');
  $('#cust_roleId').val('');
  $('#cust_licence').val('');
  $('.error').html('*');
}
