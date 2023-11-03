var selectedItemName;
var formIdName = 'addYamlForm';
var addFormTitle = 'Create a new yaml configuration file';
var editFormTitle = 'Edit yaml configuration file';
var isCreate = true;
var isCreateProgress = false;

$(document).ready(function () {
  $('#pageName').text('Alert Configuration');

  fetchYamlList();

  $('select[name=nhtype]').on('change', function () {
    var val = $(this).val();
    var field;
    if (val == 'Compliance') {
      $.each($('.compl-box'), function () {
        field = $(this).find('input,select');
        field.attr('data-required', 'true');
        $(this).show();
      });
    } else if (val == 'Notification' || val == 'Notifications') {
      $.each($('.compl-box'), function () {
        field = $(this).find('input,select');
        field.removeAttr('data-required', 'true');
        $(this).hide();
      });
    }
  });

  $('#create-yaml').on('click', function () {
    $('#' + window.formIdName + ' button[type=submit]').trigger('click');
  });

  $('#YamlListGrid').on('dblclick', 'tbody tr', function () {
    var clickedTr = $(this);
    $.each($('.dataTables_scrollBody table tbody tr'), function () {
      if ($(this).hasClass('selected')) {
        $(this).removeClass('selected');
      }
    });
    clickedTr.addClass('selected');
    window.selectedItemName = clickedTr.find('td').eq(1).html();
    window.isCreate = false;
    clearFormErrorMessages(window.formIdName);
    openEditSlider();
  });

  $('#add-yaml').on('click', function (event) {
    window.isCreate = false;
    $('#checkavail').html('');
    clearFormErrorMessages(window.formIdName);
    resetForm(window.formIdName);
    var addFormTitle = 'Create a new yaml configuration file';
    $('#createYamlTitle').text(window.addFormTitle);
    window.isCreate = true;
    rightContainerSlideOn('rsc-add-yaml');
    $('[name=compliance-category]').val('Alert').change();
  });

  $('#edit-yaml').on('click', function (event) {
    window.isCreate = false;
    clearFormErrorMessages(window.formIdName);
    openEditSlider();
  });

  $('#delete-yaml').on('click', function (event) {
    var deleteItems = [];

    $.each($('.configuration_list_items:checked'), function () {
      deleteItems.push($(this).val());
    });

    if (deleteItems.length == 0) {
      errorNotify('Please select a record to delete');
      return;
    }

    sweetAlert({
      title: 'Are you sure?',
      text: 'You will not be able to recover this configuration!',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#050d30',
      cancelButtonColor: '#fa0f4b',
      confirmButtonText: 'Yes, delete it!',
    }).then(result => {
      $.ajax({
        url: 'delete.php?action=delete',
        type: 'POST',
        data: { yaml: deleteItems },
        success: function (data) {
          data = $.parseJSON(data);

          if (data.status == true) {
            fetchYamlList();
            rightContainerSlideClose('rsc-add-yaml');
            $.notify(data.message);
          } else {
            $.notify(data.message);
          }
        },
        error: function () {
          $.notify('Something went wrong, please retry again later');
        },
      });
    });
  });

  $('.closebtn').on('click', function () {
    window.isCreateProgress = false;
    rightContainerSlideClose('rsc-add-yaml');
    $.each($('.compl-box'), function () {
      field = $(this).find('input,select');
      field.removeAttr('data-required', 'true');
      $(this).hide();
    });
    $.each($('.notif-box'), function () {
      field = $(this).find('input,select');
      field.removeAttr('data-required', 'true');
      $(this).hide();
    });
  });

  $('#YamlListGrid').on('click', '.configuration_list_items', function () {
    var isSelected = $(this).is(':checked'),
      grid = $(this).parents('tr');

    if (isSelected) {
      if (!grid.hasClass('selected')) {
        grid.addClass('selected');
      }
    } else {
      grid.removeClass('selected');
    }
  });
});

function openEditSlider() {
  var targetElement, field;

  if (window.selectedItemName == undefined) {
    errorNotify('Please select an item to edit');
    return;
  }

  $('#createYamlTitle').text(window.editFormTitle);

  $.ajax({
    url: 'select.php?action=yaml/details&file=' + window.selectedItemName,
    type: 'get',
    success: function (data) {
      data = $.parseJSON(data);

      if (data.status == true) {
        resetForm(window.formIdName);
        $('[name=yamlname]').val(data.data['yamlname']).attr('readonly', 'readonly');
        $('[name=index-name]').val(data.data['index-name']).change();
        $('[name=index-name]').selectpicker('refresh');
        $('[name=site]').val(data.data['site']).change();
        $('[name=site]').selectpicker('refresh');
        $('[name=nhtype]').val(data.data['nhtype']).change();
        $('[name=nhtype]').selectpicker('refresh');

        $('[name=scope]').val(data.data['scope']).change();
        $('[name=scope]').selectpicker('refresh');

        $('[name=compliance-category]').val(data.data['compliance-category']).change();
        $('[name=compliance-category]').selectpicker('refresh');
        $('[name=compliance-item]').val(data.data['compliance-item']).change();
        $('[name=compliance-item]').selectpicker('refresh');

        $.each($('.compl-box'), function () {
          field = $(this).find('input,select');
          $(this).hide();
          if (data.data['nhtype'] == 'Compliance') {
            field.attr('data-required', 'true');
            $(this).show();
          } else {
            field.removeAttr('data-required', 'true');
            $(this).hide();
          }
        });

        $('[name=time-frame-type]').val(data.data['time-frame-type']).change();
        $('[name=time-frame-type]').selectpicker('refresh');
        $('[name=compliance-name]').val(data.data['compliance-name']);
        $('[name=compliance-id]').val(data.data['compliance-id']);
        $('[name=notification-type]').val(data.data['notification-type']);
        $('[name=number-of-events]').val(data.data['number-of-events']);
        $('[name=time-frame-value]').val(data.data['time-frame-value']);
        $('[name=cornminute]').val(data.data['cornminute']);
        $('[name=cornhour]').val(data.data['cornhour']);
        $('[name=corndays]').val(data.data['corndays']);
        $('[name=cornweekly]').val(data.data['cornweekly']);
        $('[name=cornmonth]').val(data.data['cornmonth']);
        $('[name=query-string]').val(data.data['query-string']);

        rightContainerSlideOn('rsc-add-yaml');
      } else {
        errorNotify(data.message);
      }
    },
    error: function () {
      errorNotify('Something went wrong, please retry again later');
    },
  });
}

function resetForm(formId) {
  $('#' + formId).trigger('reset');
  $('[name=yamlname]').removeAttr('readonly');
}

function createYamlEvent(form, event) {
  var action;

  if (event.preventDefault) {
    event.preventDefault();
  } else {
    event.returnValue = false;
  }

  if (window.isCreateProgress) {
    errorNotify('A configuration request is already in progress.<br />Please wait while we save your request');
    return;
  }

  clearFormErrorMessages(form.attr('id'));

  var fields = form.find('input[data-required=true],select[data-required=true]'),
    errorCount = 0;

  $.each(fields, function (ith) {
    if ($(this).val() == '') {
      if (errorCount == 0) {
        $(this).focus();
      }
      $(this).after('<span class="validation-error-msg" style="color: red;">This field is required</span>');
      errorCount++;
    }
  });

  (fields = form.find('input[data-numeric=true],select[data-numeric=true]')),
    $.each(fields, function (ith) {
      if (isNaN($(this).val())) {
        if (errorCount == 0) {
          $(this).focus();
        }
        $(this).after('<span class="validation-error-msg" style="color: red;">This field should be a valid number</span>');
        errorCount++;
      }
    });

  fields = form.find('input[data-typecorn=true]');
  var cornRegexp = /^([\*]?[\/])?([0-9])*(([\*\-\,\/])?([0-9])*)?$/;

  $.each(fields, function (ith) {
    if (!cornRegexp.test($(this).val())) {
      if (errorCount == 0) {
        $(this).focus();
      }
      $(this).after('<span class="validation-error-msg" style="color: red;">The field should take specific value</span>');
      errorCount++;
    }
  });

  var numberOfEvents = $('input[name=number-of-events]');

  if (errorCount == 0 && !isNaN(numberOfEvents.val()) && parseInt(numberOfEvents.val()) == 0) {
    numberOfEvents.after('<span class="validation-error-msg" style="color: red;">This field value should be more than 0</span>');
    numberOfEvents.select().focus();
    errorCount++;
  }

  var cornMinute = $('input[name=cornminute]');

  if (errorCount == 0 && cornMinute.val() == '*') {
    cornMinute.after('<span class="validation-error-msg" style="color: red;">This field should be a valid cron schedule value except *</span>');
    cornMinute.select().focus();
    errorCount++;
  }

  if (errorCount == 0 && !window.isCreateProgress) {
    var allData = form.serialize(),
      statusMessage;
    statusMessage = window.isCreate ? 'Creating yaml please wait...' : 'Updating yaml please wait...';

    var notify = $.notify(statusMessage, {
      z_index: 1000000,
      allow_dismiss: false,
    });

    window.isCreateProgress = true;
    action = window.isCreate ? 'create' : 'update';

    $.ajax({
      url: 'create.php?action=' + action,
      data: allData,
      type: 'POST',
      success: function (data) {
        window.isCreateProgress = false;
        data = $.parseJSON(data);
        notify.close();
        if (data.status == true) {
          //                    fetchYamlList();
          statusMessage = window.isCreate ? 'Successfully created yaml configuration' : 'Successfully edited yaml configuration';
          $.notify(statusMessage);
          rightContainerSlideClose('rsc-add-yaml');
          setTimeout(function () {
            location.reload();
          }, 2000);
        } else {
          errorNotify(data.message);
        }
      },
      error: function () {
        window.isCreateProgress = false;
        notify.close();
        errorNotify('Something went wrong, please retry again later');
      },
    });
  }
}

function fetchYamlList() {
  $('#YamlListGrid').dataTable().fnDestroy();

  var repoTable = $('#YamlListGrid').DataTable({
    scrollY: 'calc(100vh - 240px)',
    dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
    ajax: {
      url: 'select.php',
      type: 'POST',
    },
    columns: [
      { data: 'id' },
      { data: 'name' },
      { data: 'type' },
      { data: 'created_by' },
      { data: 'modified_by' },
      { data: 'created' },
      { data: 'modified' },
    ],
    columnDefs: [{ type: 'date', targets: [5, 6] }],
    ordering: true,
    select: false,
    bInfo: false,
    responsive: true,
    fnInitComplete: function (oSettings, json) {},
    language: {
      info: 'Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>',
      search: '',
      searchPlaceholder: 'Search Records',
    },
  });
  $('.dataTables_filter input').addClass('form-control');
}

function clearFormErrorMessages(formId) {
  var fields = $('#' + formId).find('input[data-required=true],select[data-required=true],input[data-numeric=true]');

  $.each(fields, function (ith) {
    $(this).next().remove();
  });
}
