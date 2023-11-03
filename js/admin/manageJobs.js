function GET_Comm_Data(type) {
  $('#communicationGrid').dataTable().fnDestroy();
  table1 = $('#communicationGrid').DataTable({
    scrollY: jQuery('#communicationGrid').data('height'),
    scrollCollapse: true,
    autoWidth: true,
    paging: true,
    searching: true,
    processing: true,
    serverSide: true,
    ordering: true,
    select: true,
    bInfo: false,
    responsive: true,
    stateSave: true,
    stateSaveParams: function (settings, data) {
      data.search.search = '';
    },
    order: [[1, 'asc']],
    ajax: {
      url: '../lib/l-ajax.php?function=AJAX_GETManageJobsData&type=' + type + '&csrfMagicToken=' + csrfMagicToken,
      type: 'GET',
    },
    dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
    lengthMenu: [
      [10, 25, 50, 100],
      [10, 25, 50, 100],
    ],
    columns: [
      { data: 'check_data', orderable: false },
      { data: 'ProfileName' },
      { data: 'SelectionType' },
      { data: 'MachineTag' },
      { data: 'AgentName' },
      { data: 'JobCreatedTime' },
    ],
    columnDefs: [
      {
        className: 'checkbox-btn',
        targets: [0],
      },
      {
        targets: 'datatable-nosort',
        orderable: false,
      },
    ],
    language: {
      info: '_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>',
    },
    drawCallback: function (settings) {
      $('.dataTables_scrollBody').mCustomScrollbar({
        theme: 'minimal-dark',
      });
      $(".checkbox-btn input[type='checkbox']").change(function () {
        if ($(this).is(':checked')) {
          $(this).parents('tr').addClass('selected');
        }
      });

      $('.equalHeight').matchHeight();
      $('#se-pre-con-loader').hide();
    },
  });
  $('.tableloader').hide();

  $('#communicationGrid tbody').on('click', 'tr', function () {
    table1.$('tr.selected').removeClass('selected');
  });
  $('#managejobs_searchbox').keyup(function () {
    table1.search(this.value).draw();
  });
}

function GET_GCM_Data(type) {
  $('#gcmGrid').dataTable().fnDestroy();
  table2 = $('#gcmGrid').DataTable({
    scrollY: jQuery('#gcmGrid').data('height'),
    scrollCollapse: true,
    autoWidth: true,
    paging: true,
    searching: true,
    processing: true,
    serverSide: true,
    ordering: true,
    select: true,
    bInfo: false,
    responsive: true,
    stateSave: true,
    stateSaveParams: function (settings, data) {
      data.search.search = '';
    },
    ajax: {
      url: '../lib/l-ajax.php?function=AJAX_GETManageJobsData&type=' + type + '&csrfMagicToken=' + csrfMagicToken,
      type: 'GET',
    },
    dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
    lengthMenu: [
      [10, 25, 50, 100],
      [10, 25, 50, 100],
    ],
    columns: [{ data: 'serviceTag' }, { data: 'MobileID' }, { data: 'machineOS' }, { data: 'action', orderable: false }],
    language: {
      info: '_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>',
    },
    drawCallback: function (settings) {
      $('.dataTables_scrollBody').mCustomScrollbar({
        theme: 'minimal-dark',
      });

      $('.equalHeight').matchHeight();
      $('#se-pre-con-loader').hide();
    },
  });
  $('.tableloader').hide();

  $('#gcmGrid tbody').on('click', 'tr', function () {
    table2.$('tr.selected').removeClass('selected');
  });
  $('#managejobs_searchbox').keyup(function () {
    table2.search(this.value).draw();
  });
}

function solutionTypeChangeFn() {
  var solutionType = $('#solutionType').val();
  if (solutionType == '4') {
    $('#communicationGridDiv,#deleteJobli').hide();
    GET_GCM_Data(solutionType);
    $('#gcmGridDiv,#auditExportli').show();
  } else {
    $('#gcmGridDiv,#auditExportli').hide();
    GET_Comm_Data(solutionType);
    $('#communicationGridDiv,#deleteJobli').show();
  }
}

function editGCM_ID(id) {
  $.ajax({
    url: '../lib/l-ajax.php',
    type: 'GET',
    data: 'function=AJAX_GetGCMIDFn&id=' + id + '&csrfMagicToken=' + csrfMagicToken,
    dataType: 'json',
    success: function (data) {
      $('#gcmPopup').modal('show');
      $('#GCMID,#ID,#ServiceTag,#MachineOS').val('');
      $('#ID').val(data.sid);
      $('#GCMID').val(data.MobileID);
      $('#ServiceTag').val(data.serviceTag);
      $('#MachineOS').val(data.machineOS);
    },
  });
}

function GCM_Submit() {
  var id = $('#ID').val();
  var gcmid = $('#GCMID').val();
  $.ajax({
    url: '../lib/l-ajax.php',
    type: 'POST',
    data: 'function=AJAX_SubmitGCMIDFn&id=' + id + '&gcmid=' + gcmid + '&csrfMagicToken=' + csrfMagicToken,
    dataType: 'text',
    success: function (data) {
      var solutionType = $('#solutionType').val();
      GET_GCM_Data(solutionType);
    },
  });
}

$('#topCheckBox').change(function () {
  if (this.checked) {
    $('.user_check').prop('checked', true);
  } else {
    $('.user_check').prop('checked', false);
  }
});

function uniqueCheckBox() {
  $('.user_check').change(function () {
    if ($('.user_check:checked').length == $('.user_check').length) {
      $('#topCheckBox').prop('checked', true);
    } else {
      $('#topCheckBox').prop('checked', false);
    }
  });
}

function deleteJobsFn() {
  var arrAllcheckVal = '';
  $('input:checkbox[name*=checkNoc]:checked').each(function () {
    arrAllcheckVal += $(this).val() + ',';
  });
  $('#deleteJobIds').val('');
  if (arrAllcheckVal != '') {
    $('#deleteJobsPop').modal('show');
    $('#deleteJobIds').val(arrAllcheckVal);
  } else {
    $('#warnPop').modal('show');
  }
}

function deleteJobsFromAudit() {
  var deleteJobIds = $('#deleteJobIds').val();
  $('#deleteJobsPop').modal('hide');
  $('.se-pre-con').show();

  $.ajax({
    url: '../lib/l-ajax.php',
    type: 'POST',
    data: 'function=AJAX_DeleteJobsFromAuditFn&ids=' + deleteJobIds + '&csrfMagicToken=' + csrfMagicToken,
    dataType: 'text',
    success: function (msg) {
      var splitArr = msg.split('~~');
      if (splitArr[1] === 'AS_DONE') {
        var solutionType = $('#solutionType').val();
        setTimeout(function () {
          $('.se-pre-con').hide();
          GET_Comm_Data(solutionType);
        }, 10000);
        var tempArray = splitArr[2].split('##');

        trrigerJob(tempArray);
      } else {
      }

      //            var solutionType = $("#solutionType").val();
      //            setTimeout(function () {
      //                $(".se-pre-con").hide();
      //                GET_Comm_Data(solutionType);
      //            }, 5000);
    },
  });
}

function auditExcel() {
  window.location.href = '../lib/l-ajax.php?function=AJAX_GCMDetailsFn&type=4&csrfMagicToken=' + csrfMagicToken;
}

var ws = '';

function trrigerJob(tempArray) {
  try {
    $.getScript('../config.js', function () {
      if (ws === '') {
        if (window.location.protocol !== 'https:') {
          wsconnect('ws://' + wsurl, reportingurl, tempArray, function (tempArray) {
            for (var i in tempArray) {
              var JobData = {};
              JobData['Type'] = 'ExecuteJob';
              JobData['ServiceTag'] = trimStr(tempArray[i]);

              ws.send(JSON.stringify(JobData));
            }
          });
          LogToConsole('Connecting to Communication Server : ' + 'http://' + wsurl);
        } else {
          wsconnect('wss://' + wsurl, reportingurl, tempArray, function (tempArray) {
            for (var i in tempArray) {
              var JobData = {};
              JobData['Type'] = 'ExecuteJob';
              JobData['ServiceTag'] = trimStr(tempArray[i]);

              ws.send(JSON.stringify(JobData));
            }
          });
          LogToConsole('Connecting to Communication Server : ' + 'https://' + wsurl);
        }
      } else {
        LogToConsole('Already Connected to Node');
      }
    });
  } catch (e) {
    LogToConsole(e.message);
  }
}

function wsconnect(wsurl, reportingurl, tempArray, callback) {
  ws = new WebSocket(wsurl);
  ws.onopen = function () {
    LogToConsole('Connecting to Communication Server Success');
    var ConnectData = {};
    ConnectData['Type'] = 'Dashboard';
    ConnectData['AgentId'] = '';
    ConnectData['AgentName'] = '';
    ConnectData['ReportingURL'] = reportingurl;
    ws.send(JSON.stringify(ConnectData));

    callback(tempArray);
  };
  ws.onclose = function () {
    setTimeout(function () {
      wsconnect(wsurl, reportingurl, tempArray, callback);
    }, 2000);
  };
}

function LogToConsole(str) {
  console.log(str);
}

// Remove unwanted spaces and tabs
function trimStr(str) {
  return str.replace(/^\s+|\s+$/g, '');
}
