/*
 * History
 * ^^^^^^^
 * 04-09-2019   JHN     File created
 *
 */

$(document).ready(function () {
  fetchAlertList(1, '');

  $('#alertglobal').change(function () {
    cb = $(this);
    cb.val(cb.prop('checked'));
  });

  $('select[name=alerttype]').on('change', function () {
    var val = $(this).val();
    var field;
    if (val == 'Compliance') {
      $.each($('.compl-box'), function () {
        field = $(this).find('input,select');
        field.attr('data-required', 'true');
        $(this).show();
      });
      $('.notif-box').removeAttr('data-required', 'true');
      $('.notif-box').hide();
    } else if (val == 'Notification' || val == 'Notifications') {
      $.each($('.compl-box'), function () {
        field = $(this).find('input,select');
        field.removeAttr('data-required', 'true');
        $(this).hide();
      });
      $('.notif-box').attr('data-required', 'true');
      $('.notif-box').show();
    }
  });

  $('select[name=alerttypeEdit]').on('change', function () {
    var val = $(this).val();
    var field;
    if (val == 'Compliance') {
      $.each($('.compl-boxEdit'), function () {
        field = $(this).find('input,select');
        field.attr('data-required', 'true');
        $(this).show();
      });
      $('.notif-boxEdit').removeAttr('data-required', 'true');
      $('.notif-boxEdit').hide();
    } else if (val == 'Notification' || val == 'Notifications') {
      $.each($('.compl-boxEdit'), function () {
        field = $(this).find('input,select');
        field.removeAttr('data-required', 'true');
        $(this).hide();
      });
      $('.notif-boxEdit').attr('data-required', 'true');
      $('.notif-boxEdit').show();
    }
  });
});

function getProfiles() {
  var name = $('#alertNotifname').val();
  $('#notificationName').attr('readonly', 'readonly');

  if (name == '') {
    $.notify('Please choose at least one record');
  } else {
    $.ajax({
      url: '../notification/notification_func.php',
      type: 'post',
      data: { function: 'notify_getprofile', name: name, csrfMagicToken: csrfMagicToken },
      success: function (data) {
        $('#notificationName').val(name);
        $('#soln').html(data);
        $('.selectpicker').selectpicker('refresh');
        rightContainerSlideOn('rsc-update-sol');
      },
    });
  }
}

$('body').on('click', '.page-link', function () {
  var nextPage = $(this).data('pgno');
  notifName = $(this).data('name');
  const activeElement = window.currentActiveSortElement;
  const key = activeElement ? activeElement.sort : '';
  const sort = activeElement ? activeElement.type : '';
  fetchAlertList(nextPage, '', key, sort);
});

$('body').on('change', '#notifyDtl_lengthSel', function () {
  fetchAlertList(1, '');
});

function fetchAlertList(nextPage = 1, notifSearch = '', key = '', sort = '') {
  $('#enable-alert').addClass('hideAnchorTag').removeClass('enableAnchorTag');
  $('#disable-alert').addClass('hideAnchorTag').removeClass('enableAnchorTag');

  notifSearch = $('#notifSearch').val();

  checkAndUpdateActiveSortElement(key, sort);

  if (typeof notifSearch === 'undefined') {
    notifSearch = '';
  }

  $.ajax({
    url: 'select.php',
    type: 'POST',
    dataType: 'json',
    data: {
      csrfMagicToken: csrfMagicToken,
      limitCount: $('#notifyDtl_length :selected').val(),
      nextPage: nextPage,
      notifSearch: notifSearch,
      order: key,
      sort: sort,
    },
    success: function (gridData) {
      $('#absoLoader').hide();
      $('.se-pre-con').hide();
      $('#AlertListGrid').DataTable().destroy();
      $('#AlertListGrid tbody').empty();
      table3 = $('#AlertListGrid').DataTable({
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
          $(".checkbox-btn input[type='checkbox']").change(function () {
            if ($(this).is(':checked')) {
              $(this).parents('tr').addClass('selected');
            }
          });
          $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
          // $(".dataTables_filter:first").replaceWith('<div id="notifyDtl_filter" class="dataTables_filter"><label><input type="text" class="form-control form-control-sm" placeholder="Search records" value="' + notifSearch + '" id="notifSearch" aria-controls="notifyDtl"></label></div>');
        },
      });
      $('.dataTables_filter input').addClass('form-control');
      $('.tableloader').hide();
    },
  });

  $('.dataTables_filter input').addClass('form-control');

  $('#AlertListGrid').on('click', 'tr', function () {
    table3.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
    var rowID = table3.row(this).data();
    var alertNotifId = rowID[6];
    var watcherId = rowID[7];
    $('#alertNotifId').val(alertNotifId);
    $('#watcherId').val(watcherId);
    $('#watcherIdEdit').val(watcherId);
    $('#alertNotifname').val(rowID[0]);
    var alertstatus = rowID[6];
    StatusOtp_init(alertstatus);
  });

  $('#AlertListGrid').on('dblclick', 'tr', function () {
    if (window.lastRun_dblclick) {
      clearTimeout(window.lastRun_dblclick);
    }
    window.lastRun_dblclick = setTimeout(() => {
      window.lastRun_dblclick = 0;
      rightContainerSlideOn('rsc-edit-alert');
      $('#editAlertForm').hide();

      const alertId = $('#alertNotifId').val();
      get_AlertDetails(alertId);
    }, 300);
  });

  /*$("#users_searchbox").keyup(function () {
     AlertListGridTable.search(this.value).draw();
     $("#AlertListGrid tbody").eq(0).html();
     });*/
  $('#AlertListGrid').DataTable().search('').columns().search('').draw();
}

function StatusOtp_init(alertstatus) {
  if (alertstatus === 'Enabled') {
    $('#enable-alert').addClass('hideAnchorTag').removeClass('enableAnchorTag');
    $('#disable-alert').addClass('enableAnchorTag').removeClass('hideAnchorTag');
  } else if (alertstatus === 'Disabled') {
    $('#enable-alert').addClass('enableAnchorTag').removeClass('hideAnchorTag');
    $('#disable-alert').addClass('hideAnchorTag').removeClass('enableAnchorTag');
  } else {
    $('#enable-alert').addClass('hideAnchorTag');
    $('#disable-alert').addClass('hideAnchorTag');
  }
  return true;
}

function get_AlertDetails(alertId = undefined) {
  //   debugger;

  if (alertId == undefined || alertId == 'undefined' || alertId == '') {
    sweetAlert({
      title: 'Please select a Alert Configuration',
      text: 'Please select a Alert Configuration to modify',
      type: 'error',
      confirmButtonColor: '#050d30',
      confirmButtonText: 'Ok',
    })
      .then(function (reason) {
        $('.closebtn').trigger('click');
      })
      .catch(function (reason) {
        $('.closebtn').trigger('click');
      });
    return false;
  }

  $('#alertnameEdit').attr('readonly', true);

  $.ajax({
    type: 'POST',
    dataType: 'json',
    url: 'getNotificationDetail.php',
    data: { alertId: alertId, csrfMagicToken: csrfMagicToken },
    success: async function (result) {
      if (result.status == 'true' || result.status == true) {
        await assignAlertNewDetails(result.res);
        $('#editAlertForm').show();
      } else {
        $.notify(result.message);
        closePopUp();
      }
    },
  });
}

async function assignAlertNewDetails(res = {}) {
  // alert(JSON.stringify(res));

  $('.FilterCondition').remove();
  $('.MoreMeasure').remove();
  $('#alertnameEdit').val(res.name);
  if (!res.name) {
    $('#editAlertTitle').html('Create alert');
    $('#alertnameEdit').removeAttr('readonly');
  } else {
    $('#editAlertTitle').html('Modify alert');
  }

  $('#alertnameEditPost').val(res.name);
  $('#ntypeedit-item').val(res.ntype);
  $('#ntypeedit-item').val(res.ntype).change();

  $('#notifedit-priority').val(res.priority);
  $('#notifedit-priority').val(res.priority).change();
  await getDartConfig(res.scrip);

  $('#logicaloptedit-item').val(res.logicalopt);
  $('#dartvalueedit-item1').val(res.dartvalue1);
  if (res.dartvalue2 != '' && res.logicalopt == 'range') {
    $('#dartvalueedit-item2').show();
    $('#dartvalueedit-item2').val(res.dartvalue2);
  } else {
    $('#dartvalueedit-item2').hide();
    $('#dartvalueedit-item2').val('');
  }

  let criteria = {};
  if (res && res.criteria) {
    criteria = JSON.parse(res.criteria);
  }
  $('#editmeasure').html('');
  $('#filterTopType').html('');

  if (!criteria || !criteria.length) {
    html_AddMoreMeasure('#editmeasure');
  } else {
    for (let i = 0; i < criteria.length; i++) {
      html_AddMoreMeasure('#editmeasure', criteria[i]);
    }
  }

  if (!res || !res.alertConfig || !res.alertConfig.length) {
    await html_AddAnotherFilterCondition(res.scrip);
  } else {
    for (let i = 0; i < res.alertConfig.length; i++) {
      await html_AddAnotherFilterCondition(res.scrip, res.alertConfig[i]);
    }
  }

  $('.selectpicker').selectpicker('refresh');

  return true;
}

$('#add-alert').click(function () {
  //   getDartConfig('');
  assignAlertNewDetails();
  rightContainerSlideOn('rsc-edit-alert');
  //   rightContainerSlideOn('rsc-add-alert');
});

$('#link-alert').click(function () {
  getAllNotifications('');
  rightContainerSlideOn('rsc-link-alert');
});
$('#export-alert').click(function () {
  rightContainerSlideOn('rsc-export-alert');
});

$('#import-alert').click(function () {
  rightContainerSlideOn('rsc-import-alert');
});

$('#delete-alert').click(function () {
  var alertId = $('#alertNotifId').val();
  var alertName = $('#alertNotifname').val();
  if (alertId == undefined || alertId == 'undefined' || alertId == '') {
    $.notify('Please choose a record');
    closePopUp();
  } else {
    sweetAlert({
      title: ' Are you sure you want to delete the Alert?',
      text: "You won't be able to revert this action!",
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#050d30',
      cancelButtonColor: '#fa0f4b',
      cancelButtonText: 'No, cancel it!',
      confirmButtonText: 'Yes, delete it!',
    })
      .then(function (result) {
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: 'updateAction.php',
          data: { alertName: alertName, alertId: alertId, function: 'deleteAlertFunc', csrfMagicToken: csrfMagicToken },
          success: function (result) {
            if (result.status == 'true' || result.status == true) {
              $('#alertNotifId').val('');
              fetchAlertList();
              $.notify(result.message);
            } else {
              $.notify(result.message);
            }
          },
        });
      })
      .catch(function (reason) {
        $('.closebtn').trigger('click');
      });
    closePopUp();
  }
});

$('#enable-alert').click(function () {
  var alertId = $('#alertNotifId').val();
  if (alertId == undefined || alertId == 'undefined' || alertId == '') {
    $.notify('Please choose a record');
    closePopUp();
  } else {
    sweetAlert({
      title: ' Are you sure you want to enable the Alert?',
      text: 'Please choose your action!',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#050d30',
      cancelButtonColor: '#fa0f4b',
      cancelButtonText: 'No, cancel it!',
      confirmButtonText: 'Yes, enable it!',
    })
      .then(function (result) {
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: 'updateAction.php',
          data: { alertId: alertId, function: 'enableAlertFunc', csrfMagicToken: csrfMagicToken },
          success: function (result) {
            if (result.status == 'true' || result.status == true) {
              $('#alertNotifId').val('');
              fetchAlertList();
              $.notify(result.message);
            } else {
              $.notify(result.message);
            }
          },
        });
      })
      .catch(function (reason) {
        $('.closebtn').trigger('click');
      });
    closePopUp();
  }
});

$('#disable-alert').click(function () {
  var alertId = $('#alertNotifId').val();
  if (alertId == undefined || alertId == 'undefined' || alertId == '') {
    $.notify('Please choose a record');
    closePopUp();
  } else {
    sweetAlert({
      title: ' Are you sure you want to disable the Alert?',
      text: 'Please choose your action!',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#050d30',
      cancelButtonColor: '#fa0f4b',
      cancelButtonText: 'No, cancel it!',
      confirmButtonText: 'Yes, disable it!',
    })
      .then(function (result) {
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: 'updateAction.php',
          data: { alertId: alertId, function: 'disableAlertFunc', csrfMagicToken: csrfMagicToken },
          success: function (result) {
            if (result.status == 'true' || result.status == true) {
              $('#alertNotifId').val('');
              fetchAlertList();
              $.notify(result.message);
            } else {
              $.notify(result.message);
            }
          },
        });
      })
      .catch(function (reason) {
        $('.closebtn').trigger('click');
      });
    closePopUp();
  }
});

function getDartConfig(dart) {
  return $.ajax({
    url: 'getDartConfig.php',
    data: {
      function: 'getDartConfiguration',
      dart: dart,
      csrfMagicToken: csrfMagicToken,
    },
    type: 'POST',
    dataType: 'JSON',
    success: function (data) {
      if (data.status === 'success') {
        $('.dartedit-item').remove();

        $('.dartedit-item-holder').append(
          `<select id="dartedit-item2" name="dartedit-item" class="dartedit-item dartedit-item2 selectpicker" data-size="5" data-width="100%" onchange="getDartConfigurationDet($(this).val() );">
                ${data.dartlist}
            </select>`,
        );

        $('.selectpicker').selectpicker('refresh');
      } else {
        errorNotify(data.message);
      }
    },
    error: function () {
      errorNotify('Some error occurred. Please try again');
    },
  });
}

function getAllNotifications(site) {
  return $.ajax({
    url: 'getNotifications.php?site=' + site,
    data: { csrfMagicToken: csrfMagicToken },
    type: 'POST',
    dataType: 'JSON',
    success: function (data) {
      if (data.status === 'success') {
        $('#notification-item').html('');
        $('#notification-item').html(data.notifications);
        $('.selectpicker').selectpicker('refresh');
      } else {
        errorNotify(data.message);
      }
    },
    error: function () {
      errorNotify('Some error occurred. Please try again');
    },
  });
}

function getDartConfigurationDet(dart, dartId, targetSelector = '.dartconfig-item') {
  return $.ajax({
    url: 'getDartConfig.php',
    data: {
      function: 'getDartConfigurationDet',
      dart: dart,
      dartSel: dartId,
      csrfMagicToken: csrfMagicToken,
    },
    type: 'POST',
    dataType: 'JSON',
    success: function (data) {
      if (data.status === 'success') {
        const targetSelectorParent = $(targetSelector).parent();
        $(targetSelector).remove();
        $(targetSelectorParent).html(
          `<select id="${targetSelector.replace(
            '#',
            '',
          )}" name="dartconfig-item[]" class="dartconfig-item selectpicker dartvalue-item1-class" data-size="5" data-width="100%">
                ${data.dartlistdet}
            </select>`,
        );
        $('.selectpicker').selectpicker('refresh');
      } else {
        errorNotify(data.message);
      }
    },
    error: function () {
      errorNotify('Some error occurred. Please try again');
    },
  });
}

var max_fields = 15; //maximum input boxes allowed
var wrapper = $('#measure'); //Fields wrapper
// var filter = $('#filterTopType');
var editwrapper = $('#editmeasure');
var add_button = $('.add_field_button'); //Add button ID
var editadd_button = $('.editadd_field_button');
// var watcher_box_count = 1; //initlal text box count

function html_AddMoreMeasure(wrapper = '#measure', criteria = {}) {
  const watcherBoxCount = $('.MoreMeasure').length;
  if (watcherBoxCount >= max_fields) {
    errorNotify('You can add only ' + max_fields + ' measures');
    return;
  }

  let status_id = watcherBoxCount + '_status';
  let count_id = watcherBoxCount + '_count';
  let seconds_id = watcherBoxCount + '_seconds';

  $(wrapper).append(
    '<div class="MoreMeasure card-body" \
        style="border: 1px solid;padding: 5px;box-shadow: 1px 1px #f8f8f8;margin-top:3px;border-radius:4px;">\
        <button \
        data-qa="MoreMeasure_' +
      watcherBoxCount +
      'remove_field"  class="remove_field float-right" style="width:25px;height:20px;cursor:pointer;">X</button>\
        <br><div class="form-group"><label>Choose Type</label>\
          <select id="' +
      status_id +
      '" name="criteriaStatus[]"\
        style="height:28px;border:1px solid #e8e8e8;width:100%;border-radius:4px;font-size:12px;"\
        class="selectpicker">\
            <option value="1" selected>Ok</option>\
            <option value="2">Warning</option>\
            <option value="3">Alert</option>\
            <option value="4">Notification</option> \
        </select>\
       </div>\
       <div class="form-group">\
       <label for="">Set Count</label>\
       <input id="' +
      count_id +
      '" name="criteriaCount[]" type="number" class="form-control"></div><div class="form-group"><label for="">Choose Time frame(In seconds)</label><input id="' +
      seconds_id +
      '" name="criteriaSeconds[]"  type="number" class="form-control">\
          </div>\
          </div>\
          </div>',
  ); //add input box

  $('#' + status_id).val(criteria.status);
  $('#' + count_id).val(criteria.count);
  $('#' + seconds_id).val(criteria.seconds);

  $('.selectpicker').selectpicker('refresh');
}

function randStr() {
  return `rnd${Math.floor(Math.random() * 10000)}x${Math.floor(Math.random() * 10000)}`;
}

async function html_AddAnotherFilterCondition(scrip = undefined, alertConfig = {}) {
  if (!scrip) {
    scrip = $('#dartedit-item2').val();
  }
  const filter = $('#filterTopType');

  const FilterConditionCount = $('.FilterCondition').length;
  const FilterConditionOptKey = `logical-group-opt-item${FilterConditionCount}`;

  let addHtnl = '';
  if (FilterConditionCount) {
    addHtnl = `<br><br><select id="${FilterConditionOptKey}" class="selectpicker" data-size="5" data-width="100%"    >
       <option value="AND" ${alertConfig.group == 'AND' ? 'selected' : ''} >AND</option>
       <option value="OR" ${alertConfig.group == 'OR' ? 'selected' : ''} >OR</option>
    </select>`;
  }

  const logicaloptId = `logicalopt-item${FilterConditionCount}`;
  const selectId = randStr();
  $(filter).append(
    `<div class="FilterCondition" data-qa="FilterCondition_${FilterConditionCount}_holder"  >${addHtnl}` +
      '<div class="  card-body filter-card" style="display: flex; flex-direction: column; border: 1px solid;padding: 5px;box-shadow: 1px 2px #f8f8f8;border-radius:4px; margin-top: 20px">\n' +
      '                            <button class="remove_field" \
      data-qa="FilterCondition_' +
      FilterConditionCount +
      'remove_field" \
       onclick="$(this).parent().parent().remove(); return false;"\
       style=" align-self: end; width:25px;height:20px; cursor:pointer;">X</button>\n' +
      '                            <div class="dartconfig-item-holder form-group has-label compl-box" data-required="true">\n' +
      '                              <span class="error">*</span>\n' +
      '                              <label  for="dartconfig-item' +
      FilterConditionCount +
      '" >Which is the criteria that must be searched</label>\n' +
      '                              <select id="' +
      selectId +
      '" name="dartconfig-item[]" class="dartconfig-item selectpicker" data-size="5" data-width="100%"></select>\n' +
      '                            </div>\n' +
      '                            <div class="form-group col-md-5 has-label compl-box" data-required="true" style="margin-left:-15px;width:225px;">\n' +
      '                              <span class="error">*</span>\n' +
      '                              <label for="' +
      logicaloptId +
      '">Logical Operators</label>\n' +
      '                              <select id="' +
      logicaloptId +
      '" name="logicalopt-item[]" class="selectpicker" data-size="5" data-width="100%"\
      onchange="  if(this.value!=\'range\'){$(\'#v2_' +
      selectId +
      "').hide();}else{$('#v2_" +
      selectId +
      '\').show();}"\
      >\n' +
      '                                <option value="&lt;" selected>&lt;</option>\n' +
      '                                <option value="&gt;" selected>&gt;</option>\n' +
      '                                <option value="like" selected>Contains</option>\n' +
      '                                <option value="range" selected>Range</option>\n' +
      '                                <option value="=" selected>=</option>\n' +
      '                              </select>\n' +
      '                            </div>\n' +
      '                            <div class="form-group col-md-12 has-label compl-box" style="margin-left:-15px;">\n' +
      '                              <span class="error">*</span>\n' +
      '                              <label class="label">Enter the Value</label>\n' +
      '                              <div class="form-inline col-md-10" style="margin-left:-15px;">\n' +
      '                                <input id="v1_' +
      selectId +
      '" type="text" data-qa="FilterCondition' +
      FilterConditionCount +
      '_input1" class="form-control col-md-5 dartvalue-item1-class dartvalue-item1" name="dartvalue-item1[]"   />&nbsp;\n' +
      '                                <input id="v2_' +
      selectId +
      '" type="text" data-qa="FilterCondition' +
      FilterConditionCount +
      '_input2" class="form-control col-md-5 dartvalue-item2" name="dartvalue-item2[]"   style="display:none;" />\n' +
      '                              </div>\n' +
      '                            </div>\n' +
      '                          </div>' +
      '</div>',
  );
  console.log('selectId=', selectId);
  await getDartConfigurationDet(scrip, alertConfig.dartconfig, `#${selectId}`);

  $(`#${logicaloptId}`).val(alertConfig.logicalopt || '');
  $(`#v1_${selectId}`).val(alertConfig.dartvalue1 || 0);
  $(`#v2_${selectId}`).val(alertConfig.dartvalue2 || 0);
  if (alertConfig.logicalopt == 'range') {
    $(`#v2_${selectId}`).show();
  }

  $('.selectpicker').selectpicker('refresh');
}

$(editadd_button).click(function (e) {
  //on add input button click
  var Countvalues = $("input[name='criteriaCount[]']")
    .map(function () {
      if ($(this).val() != '') {
        return $(this).val();
      }
    })
    .get();
  // watcher_box_count = Countvalues.length;

  e.preventDefault();

  html_AddMoreMeasure(editwrapper);
  // watcher_box_count++;
});

$(wrapper).on('click', '.remove_field', function (e) {
  //user click on remove text
  e.preventDefault();
  $(this).parent('div').remove();
  // watcher_box_count--;
});

$(editwrapper).on('click', '.remove_field', function (e) {
  //user click on remove text
  e.preventDefault();
  $(this).parent('div').remove();
  // watcher_box_count--;
});

$('#logicalopt-item').on('change', function (e) {
  e.preventDefault();
  if ($(this).val() == 'range') {
    $('.dartvalue-item2').show();
  } else {
    $('.dartvalue-item2').hide();
  }
});

$('#logicaloptedit-item').on('change', function (e) {
  e.preventDefault();
  if ($(this).val() == 'range') {
    $('#dartvalueedit-item2').show();
  } else {
    $('#dartvalueedit-item2').hide();
  }
});

/**
 * On click for save button
 */
$('#Modify-alert1').click(function () {
  var alertname;
  var alerttype;
  var priority = '';

  alertname = $('#alertnameEdit').val();
  //alertsite = $('#alertsite').val();
  alerttype = $('#ntypeedit-item').val();
  var alertId = $('#alertNotifId').val();
  priority = $('#notifedit-priority').val();

  if (alertname === '') {
    $.notify('Please enter the alert configuration name');
    return false;
  }

  if (alerttype === '') {
    $.notify('Please select the type for alert configuration');
    return false;
  }

  var dart = $('#dartedit-item2').val();

  const dartconfigsElm = $('[name="dartconfig-item[]"]');
  const logicaloptsElm = $('[name="logicalopt-item[]"]');
  const dartvaluesElm1 = $('[name="dartvalue-item1[]"]');
  const dartvaluesElm2 = $('[name="dartvalue-item2[]"]');

  const dartconfigs = [];
  for (let i = 0; i < dartconfigsElm.length; i++) {
    let group = $(`#logical-group-opt-item${i}`).val();

    const conf = {
      dartconfig: $(dartconfigsElm[i]).val(),
      logicalopt: $(logicaloptsElm[i]).val(),
      dartvalue1: $(dartvaluesElm1[i]).val(),
      dartvalue2: $(dartvaluesElm2[i]).val(),
      group: group,
    };

    if (conf.dartvalue1 == '') {
      $.notify('Please enter the value');
      return false;
    }

    if (conf.logicalopt == 'range' && !conf.dartvalue2) {
      $.notify('Please enter integer value for comparision');
      return false;
    }
    if (!conf.dartvalue1) {
      $.notify('Please enter integer value for comparision');
      return false;
    }
    if (!conf.dartconfig) {
      $.notify('Please select dart config value');
      return false;
    }

    dartconfigs.push(conf);
  }
  //

  var Statusvalues,
    Countvalues,
    Secondsvalues = [];
  //alert(Statusvalues.toString());
  Statusvalues = $("select[name='criteriaStatus[]']")
    .map(function () {
      if ($(this).val() != '') {
        return $(this).val();
      }
    })
    .get();
  Countvalues = $("input[name='criteriaCount[]']")
    .map(function () {
      if ($(this).val() != '') {
        return $(this).val();
      }
    })
    .get();
  Secondsvalues = $("input[name='criteriaSeconds[]']")
    .map(function () {
      if ($(this).val() != '') {
        return parseInt($(this).val());
      }
    })
    .get();
  const topsec = Math.max.apply(Math, Secondsvalues);

  if (Countvalues.length == 0) {
    $.notify('Please input criteria count');
    return false;
  }

  if (Secondsvalues.length == 0) {
    $.notify('Please input criteria seconds');
    return false;
  }

  var criteria = [];

  var index_id = 1;
  for (var i = 0; i < Secondsvalues.length; i++) {
    var tempcrit = {};
    if (Countvalues[i].length != 0 && Secondsvalues[i].length != 0) {
      tempcrit['cid'] = parseInt(index_id);
      tempcrit['count'] = parseInt(Countvalues[i]);
      tempcrit['seconds'] = parseInt(Secondsvalues[i]);
      tempcrit['status'] = parseInt(Statusvalues[i]);
    } else {
      $.notify('Please fill all criteria count and seconds');
      return false;
    }
    index_id++;
    criteria.push(tempcrit);
  }

  $.ajax({
    url: 'createNotify.php',
    data: {
      alertname: alertname,
      alerttype: alerttype,
      dart: dart,
      priority: priority,
      dartconfigs: JSON.stringify(dartconfigs),
      criteria: JSON.stringify(criteria),
      topSeconds: topsec,
      alertId: alertId,
      action: 'edit',
      csrfMagicToken: csrfMagicToken,
    },
    type: 'POST',
    dataType: 'JSON',
    success: function (data) {
      if (data.status == 'success') {
        var statusMessage = 'Notification configuration updated successfully';
        $.notify(statusMessage);
        rightContainerSlideClose('rsc-edit-alert');
        setTimeout(function () {
          location.reload();
        }, 2000);
        //fetchAlertList();
      } else {
        errorNotify(data.message);
      }
    },
    error: function () {
      errorNotify('Some error occurred. Please try again');
    },
  });
});

$('#alertsiteEdit').change(function () {
  var site = $('#alertsiteEdit').val();
  getAllNotifications(site);
});

$('#link-alert1').click(function () {
  var sitelist = $('#alertsiteEdit').val();
  var notifications = $('#notification-item').val();

  $.ajax({
    url: 'updateAction.php',
    data: { site: sitelist, notifications: notifications.toString(), function: 'linkAlertFunc', csrfMagicToken: csrfMagicToken },
    type: 'POST',
    dataType: 'JSON',
    success: function (data) {
      if (data.status == 'success') {
        var statusMessage = 'Notification linked successfully';
        $.notify(statusMessage);
        rightContainerSlideClose('rsc-link-alert');
        setTimeout(function () {
          location.reload();
        }, 2000);
      } else {
        errorNotify(data.message);
      }
    },
    error: function () {
      errorNotify('Some error occurred. Please try again');
    },
  });
});

$('#notify_file1').on('change', function () {
  var file_data = $('#notify_file1').prop('files')[0];
  var logo_data = new FormData();
  var csv_name = $('#notify_file1').prop('files')[0]['name'];
  $('#remove_logo1').css('display', '');

  logo_data.append('notify_file', file_data);
  logo_data.append('type', 'headerlogo');
  logo_data.append('csrfMagicToken', csrfMagicToken);
  $('#notify_name').html(csv_name).css({
    color: 'black',
  });
  $('.logo_loader').show();
  $.ajax({
    url: '../watcher/importNotification.php',
    type: 'POST',
    data: logo_data,
    success: function (res) {
      console.log(res);
      var result = $.parseJSON(res);

      var i = 0;
      var htmlstr = '';
      if (result.duplicate > 0) {
        htmlstr =
          htmlstr +
          'These are the duplicate notification(s), if you continue then it will replace the existing configuration for these notification(s)<br/><b>' +
          result.dupname +
          '</b> <br/>';
      }
      if (result.insert > 0) {
        htmlstr = htmlstr + ' Going to create ' + result.insert + ' new notification(s)';
      }
      $('#config').html(htmlstr);
    },
    cache: false,
    contentType: false,
    processData: false,
  });
});

$('#remove_logo1').click(function () {
  $('#notify_file1').val('');
  $('#notify_name').html('');
  $('#config').html('');
  $('.logo').attr('src', '../assets/img/bask-logo.png');
});

$('#import-alert1').on('click', function () {
  var file_data = $('#notify_file1').prop('files')[0];
  var logo_data = new FormData();
  var csv_name = $('#notify_file1').prop('files')[0]['name'];
  $('#remove_logo1').css('display', '');
  $('#accesslist').show();
  $('#accesslist').html('<br/><br/><p>Please wait while we are analysing the details of your machine group</p><p>&nbsp;</p>');

  logo_data.append('notify_file', file_data);
  logo_data.append('type', 'headerlogo');
  logo_data.append('csrfMagicToken', csrfMagicToken);
  if (csv_name == '') {
    $.notify('Please upload the CSV file');
    return false;
  }

  $('#notify_name').html(csv_name).css({
    color: 'black',
  });
  $('.logo_loader').show();
  $.ajax({
    url: '../watcher/processcsvNotification.php',
    type: 'POST',
    data: logo_data,
    success: function (res) {
      console.log(res);
      var result = $.parseJSON(res);
      if (result.status == 'success') {
        $.notify('Notification configured successfully');
      } else {
        $.notify('Notification failed to configure');
      }
      setTimeout(function () {
        rightContainerSlideClose('rsc-import-alert');
        location.reload();
      }, 2000);
    },
    cache: false,
    contentType: false,
    processData: false,
  });
});

function updateSol() {
  var name = $('#notificationName').val();
  var value = $('#soln').val();
  var mid = $('#soln option:selected').attr('id');
  if (value == '') {
    $.notify('Please select the solution that must be pushed');
  } else {
    $.ajax({
      url: '../notification/notification_func.php',
      data: { function: 'updateSoln', name: name, val: value, mid: mid, csrfMagicToken: csrfMagicToken },
      type: 'post',
      success: function (data) {
        $.notify('Solution updated successfully');
        rightContainerSlideClose('rsc-update-sol');
        location.reload();
      },
    });
  }
}
