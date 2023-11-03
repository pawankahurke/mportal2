priority = '';
nfstatus = '';
ntype = '';
$(document).ready(function () {
  $('#notifyDtl_wrapper').hide();
  $('#largeDataPagination').hide();
  notification_datatable();
  // getTopnotification();
  // $('#absoLoader').show();
});

$('#topCheckBox').click(function () {
  if ($(this).is(':checked')) {
    $('input:checkbox[name*=checkNoc]').each(function () {
      $(this).prop('checked', true);
    });
  } else {
    $('input:checkbox[name*=checkNoc]').each(function () {
      $(this).prop('checked', false);
    });
  }
});

function showRightPane() {
  rightMenuFunctionality();
}

function getTopnotification() {
  $.ajax({
    type: 'POST',
    url: '../notification/notification_func.php',
    dataType: 'text',
    data: {
      function: 'getTopNotification',
      csrfMagicToken: csrfMagicToken,
    },
    success: function (msg) {
      $('#alert_notify').html('');
      $('#alert_notify').append(msg);
    },
    error: function (msg) {},
  });
}
var notifName;
function notification_datatable(priority = '', nfstatus = '', ntype = '') {
  $('#notifSearch').val('');
  $('.clearbtn').css('display', 'none');
  $('.showbtn').css('display', 'block');
  $('.sortArrow').addClass('headerDown');
  $('.sortArrow').removeClass('headerUp');
  $('#absoLoader').show();
  $.ajax({
    type: 'POST',
    url: 'notification_func.php',
    data: {
      function: 'get_notifications',
      csrfMagicToken: csrfMagicToken,
      priority: priority,
      ntype: ntype,
    },
    success: function (gridData) {
      $('#absoLoader').hide();
      if (gridData == '##') {
        $('#NotificationErr').css('display', 'block');
        $('#notificationList').html('');
        $('#notifyDtl_wrapper').hide();
        $('#largeDataPagination').hide();
        // $("#NotificationErr").html('No Notifications Found');
      } else {
        $('#notifyDtl_wrapper').show();
        $('#largeDataPagination').show();
        $('#NotificationErr').css('display', 'none');
        var dataList = gridData.split('##');
        if (dataList.length > 0) {
          $('#notificationList').html('');
          $('#notificationList').html(dataList[0]);
          notifName = dataList[1];
          notificationDtl_datatable(priority, dataList[1], 'mainactive', nfstatus, 1, '', '', '', ntype);
        }
      }
    },
    error: function (msg) {},
  });
}

function notificationDtl_datatable(
  priority = '',
  name = '',
  reflag = '',
  status = '',
  nextPage = 1,
  notifSearch = '',
  key = '',
  sort = '',
  ntype = '',
) {
  $('#absoLoader').show();
  if (status == '') {
    status = nfstatus;
  }
  if (name == '') {
    name = notifName;
  }

  checkAndUpdateActiveSortElement(key, sort);

  //  alert(notifName);
  //  console.log('ooo'+status);
  $('#notiname').val(name);
  $('#topCheckBox').prop('checked', false);
  if (reflag != 'mainactive') {
    $('.notif-padding').each(function () {
      $(this).removeClass('active');
    });
    $(reflag).addClass('active');
  }
  selNid = name;
  if (name.length > 25) {
    $('#activeNotif')
      .html(name.substring(0, 25) + '...')
      .attr('title', name);
  } else {
    $('#activeNotif').html(name);
  }

  notifSearch = $('#notifSearch').val();
  if (typeof notifSearch === 'undefined') {
    notifSearch = '';
  }
  var dat = {
    function: 'get_notificationDtl',
    name: name,
    csrfMagicToken: csrfMagicToken,
    limitCount: $('#notifyDtl_length :selected').val(),
    nextPage: nextPage,
    notifSearch: notifSearch,
    status: status,
    priority: priority,
    order: key,
    sort: sort,
    ntype: ntype,
  };

  $.ajax({
    url: 'notification_func.php',
    type: 'POST',
    dataType: 'json',
    data: dat,
    success: function (gridData) {
      //    console.log(gridData.html);
      $('#absoLoader').hide();
      $('.se-pre-con').hide();
      $('#notifyDtl').DataTable().destroy();
      console.log(gridData);
      $('#notifyDtl tbody').empty();
      table3 = $('#notifyDtl').DataTable({
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

          $('#sortKey1').html(
            "<i class=\"fa fa-caret-down cursorPointer direction\" id = \"notiMachine1\" onclick = \"addActiveSort('asc', 'machine'); notificationDtl_datatable('','" +
              name +
              "','mainactive', '', 1, '','machine', 'asc');sortingIconColor('notiMachine1')\" style=\"font-size:18px\"></i>" +
              "<i class=\"fa fa-caret-up cursorPointer direction\" id = \"notiMachine2\" onclick = \"addActiveSort('desc', 'machine'); notificationDtl_datatable( '','" +
              name +
              "','mainactive', '', 1, '','machine', 'desc');sortingIconColor('notiMachine2')\" style=\"font-size:18px\"></i>",
          );

          $('#sortKey2').html(
            "<i class=\"fa fa-caret-down cursorPointer direction\" id = \"ndate1\" onclick = \"addActiveSort('asc', 'ndate'); notificationDtl_datatable('','" +
              name +
              "','mainactive', '', 1, '','ndate', 'asc');sortingIconColor('ndate1')\" style=\"font-size:18px\"></i>" +
              "<i class=\"fa fa-caret-up cursorPointer direction\" id = \"ndate2\" onclick = \"addActiveSort('desc', 'ndate'); notificationDtl_datatable( '','" +
              name +
              "','mainactive', '', 1, '','ndate', 'desc');sortingIconColor('ndate1')\" style=\"font-size:18px\"></i>",
          );

          $('#sortKey3').html(
            "<i class=\"fa fa-caret-down cursorPointer direction\" id = \"count1\" onclick = \"addActiveSort('asc', 'count'); notificationDtl_datatable('','" +
              name +
              "','mainactive', '', 1, '','count', 'asc');sortingIconColor('count1')\" style=\"font-size:18px\"></i>" +
              "<i class=\"fa fa-caret-up cursorPointer direction\" id = \"count2\" onclick = \"addActiveSort('desc', 'count'); notificationDtl_datatable( '','" +
              name +
              "','mainactive', '', 1, '','count', 'desc');sortingIconColor('count2')\" style=\"font-size:18px\"></i>",
          );

          $('#sortKey4').html(
            "<i class=\"fa fa-caret-down cursorPointer direction\" id = \"nocStatus1\" onclick = \"addActiveSort('asc', 'nocStatus'); notificationDtl_datatable('','" +
              name +
              "','mainactive', '', 1, '','nocStatus', 'asc');sortingIconColor('nocStatus1')\" style=\"font-size:18px\"></i>" +
              "<i class=\"fa fa-caret-up cursorPointer direction\" id = \"nocStatus2\" onclick = \"addActiveSort('desc', 'nocStatus'); notificationDtl_datatable( '','" +
              name +
              "','mainactive', '', 1, '','nocStatus', 'desc');sortingIconColor('nocStatus2')\" style=\"font-size:18px\"></i>",
          );

          $('#sortKey5').html(
            "<i class=\"fa fa-caret-down cursorPointer direction\" id = \"note1\" onclick = \"addActiveSort('asc', 'note'); notificationDtl_datatable('','" +
              name +
              "','mainactive', '', 1, '','note', 'asc');sortingIconColor('note1')\" style=\"font-size:18px\"></i>" +
              "<i class=\"fa fa-caret-up cursorPointer direction\" id = \"note2\" onclick = \"addActiveSort('desc', 'note'); notificationDtl_datatable( '','" +
              name +
              "','mainactive', '', 1, '','note', 'desc');sortingIconColor('note1')\" style=\"font-size:18px\"></i>",
          );
        },
        drawCallback: function (settings) {
          $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
        },
      });
      console.log(table3);
      $('.dataTables_filter input').addClass('form-control');
      $('.tableloader').hide();
    },
  });

  $('#notifyDtl tbody').on('click', 'tr', function () {
    var rowID = table3.row(this).data();
    table3.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
    $('#selected').val(rowID[7]);
  });
  $('#notification_searchbox').keyup(function () {
    table3.search(this.value).draw();
  });

  $('#notifyDtl').DataTable().search('').columns().search('').draw();
}

$('body').on('click', '.page-link', function () {
  var nextPage = $(this).data('pgno');
  const activeElement = window.currentActiveSortElement;
  const key = activeElement ? activeElement.sort : '';
  const sort = activeElement ? activeElement.type : '';
  notifName = $(this).data('name');
  notificationDtl_datatable('', '', 'mainactive', '', nextPage, '', key, sort, '');
});
$('body').on('change', '#notifyDtl_lengthSel', function () {
  notificationDtl_datatable('', '', 'mainactive', '', 1);
});

function addNotes(tname, cust, machine, entered) {
  $('#notiname').val(tname);
  $('#macname').val(machine);
  $('#eventtime').val(entered);
  $('#custname').val(cust);
  var machine = machine; //$('#macname').val();
  var eventTime = entered; //$('#eventtime').val();
  //    var name = $('#notiname').val();
  var site = cust; //$('#custname').val();
  $.ajax({
    type: 'POST',
    url: 'notification_func.php',
    dataType: 'text',
    data: {
      function: 'getNotes',
      name: tname,
      site: site,
      eventDt: eventTime,
      machine: machine,
      csrfMagicToken: csrfMagicToken,
    },
    success: function (msg) {
      msg = $.trim(msg);
      if (msg === 'add') {
        $('#notesText').val('');
        $('#rsc-add-note').find('.form-control').attr('readonly', false);
        $('#rsc-add-note').find('.selectpicker').attr('disabled', false);
        $('#rsc-add-note').find('.selectpicker').selectpicker('refresh');
        $('#addNote').show();
        $('#editOption').hide();
        $('#toggleButton').hide();
      } else {
        $('#rsc-add-note').find('.form-control').attr('readonly', true);
        $('#addNote').hide();
        $('#editOption').show();
        $('#notesText').val(msg);
      }
      $('#toggleButton').hide();
      rightContainerSlideOn('rsc-add-note');
    },
    error: function (msg) {},
  });
}

function addNoteByName(type) {
  var machine = $('#macname').val();
  var eventTime = $('#eventtime').val();
  var name = $('#notiname').val();
  var site = $('#custname').val();
  var note = $('#notesText').val();

  /*var regExp = /^[a-zA-Z0-9\_ ]*$/;

     if (!regExp.test(note)) {
     errorNotify("The note data should contain only alphanumeric characters, underscore or space");
     return;
     }*/

  var data = {
    function: 'updateNote',
    name: name,
    site: site,
    eventDt: eventTime,
    machine: machine,
    note: note,
    csrfMagicToken: csrfMagicToken,
  };

  $.ajax({
    type: 'POST',
    url: 'notification_func.php',
    data: data,
    dataType: 'text',
    success: function (msg) {
      if ($.trim(msg) === 'success') {
        if (type == 'add') {
          $.notify('Note added successfully');
        } else {
          $.notify('Note updated successfully');
        }

        setTimeout(function () {
          //     location.reload();
          notification_datatable();
        });
      }
      rightContainerSlideClose('rsc-add-note');
    },
    error: function (msg) {},
  });
}

function notifyFix() {
  //    var chk = $('.notifychk').is(':checked');
  //    if($("#notifyDtl tbody tr").hasClass("selected")){
  if ($('.notifychk').is(':checked')) {
    var name = $('#notiname').val();
    if (name == '') {
      $.notify('Please choose at least one record');
    } else {
      $.ajax({
        type: 'POST',
        url: 'notification_func.php',
        data: {
          function: 'get_notificationSoln',
          nid: name,
          csrfMagicToken: csrfMagicToken,
        },
        success: function (gridData) {
          rightContainerSlideOn('rsc-add-fix');
          //            $('#default-fixes').modal('show');
          $('#notify_actionMsg').html('');
          $('#notificationfixList').html(gridData);
        },
        error: function (msg) {},
      });
    }
  } else {
    $.notify('Please select a record');
  }
}

function getDetails() {
  var notifDetList = [];
  var gridSel = $('#selected').val();
  $('input:checkbox[name*=checkNoc]:checked').each(function () {
    // notifDetList.push($(this).attr('id'));
    notifDetList.push($(this).attr('value'));
  });

  if (notifDetList.length <= 0) {
    $.notify('Please select a record!');
    return false;
  } else if (notifDetList.length > 0) {
    notifDetList.push(gridSel);
    var rightSlider = new RightSlider('#rsc-details');
    // rightSlider.showLoader();
    $('#loader').show();
    var postData = {
      function: 'get_notificationsEvents',
      notifdetlist: notifDetList,
      csrfMagicToken: csrfMagicToken,
    };

    $.ajax({
      type: 'POST',
      url: 'notification_func.php',
      data: postData,
      dataType: 'json',
      success: function (gridData) {
        rightContainerSlideOn('rsc-details');
        $('.loader').hide();
        $('#notifyeventDtl').DataTable().destroy();
        table4 = $('#notifyeventDtl').DataTable({
          scrollY: 'calc(100vh - 170px)',
          aaData: gridData,
          autoWidth: false,
          paging: true,
          searching: false,
          processing: false,
          serverSide: false,
          ordering: true,
          select: true,
          bInfo: false,
          responsive: false,
          stateSave: true,
          pagingType: 'full_numbers',
          stateSaveParams: function (settings, data) {
            data.search.search = '';
          },
          lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100],
          ],
          language: {
            info: '_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>',
          },
          dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
          drawCallback: function (settings) {
            $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
          },
          fnInitComplete: function (oSettings, json) {
            setTimeout(function () {
              // rightSlider.hideLoader();
            }, 1000);
          },
        });
      },
      error: function (msg) {
        $('#Loader').hide();
      },
    });

    //        delay_AndSort(); // sort table

    $('#notifyeventDtl tbody').on('click', 'tr', function () {
      if ($(this).hasClass('selected')) {
        $(this).removeClass('selected');
      } else {
        table4.$('tr.selected').removeClass('selected');
        $(this).addClass('selected');
      }
    });
  }
}

function delay_AndSort() {
  setTimeout(function () {
    table4.order([0, 'asc']).draw();
  }, 1500);
}

function export_notification() {
  var name = $('#notiname').val();

  if (name == '') {
    $.notify('Please choose at least one record');
  } else {
    location.href = 'notification_func.php?function=exportNotificationselected&name=' + name;
  }
}

//$("#export_allnotification").click(function() {
//    location.href = "notification_func.php?function=exportNotificationselected";
//});

// for interactiveNotifyPush_1
$('#interactiveNotifyPush').click(function () {
  var name = $('#notiname').val();
  var arrAllcheckVal = '';
  var checkedInput = $('input:checkbox[name*=checkNoc]:checked');

  if (checkedInput.length > 1) {
    $.notify('This action can only be performed for only one device at a time.');
    return false;
  }

  checkedInput.each(function () {
    arrAllcheckVal += $(this).val() + '~~~~';
  });

  var sclid = arrAllcheckVal,
    machineName = '';

  if (checkedInput.length == 1) {
    machineName = checkedInput.parents('tr').find('td').eq(1).text();
  }
  var machineArg = checkedInput.length >= 1 ? (checkedInput.length > 1 ? '&machine=multi' : '&machine=' + machineName) : '';

  $.ajax({
    type: 'POST',
    url: 'notification_func.php',
    data: {
      function: 'get_notificationSolnIntre',
      nid: name,
      sel: sclid,
      csrfMagicToken: csrfMagicToken,
    },
    success: function (gridData) {
      var url = window.location.href; // Returns full URL
      var urlList = url.split('notification');
      location.href = urlList[0] + 'resolution/index.php?notification' + machineArg;
    },
    error: function (msg) {},
  });
});

//$("#notifysolnPush").click(function() {
function updateSolution() {
  var arrAllcheckVal = '';
  $('input:checkbox[name*=checkNoc]:checked').each(function () {
    arrAllcheckVal += $(this).val() + '~~~~';
  });
  var sclid = arrAllcheckVal;
  if (sclid !== '') {
    var othersVal = $('input[name=othersSoln]:checked').val();
    var fixedVal = $('input[name=profilename]:checked').val();
    if (typeof othersVal !== 'undefined') {
      notifyOthersFix();
    }
    if (typeof fixedVal !== 'undefined') {
      notifyVarValue(fixedVal, sclid);
    }
    if (typeof othersVal === 'undefined' && typeof fixedVal === 'undefined') {
      $.notify('Please select an entry to attach to the notification as the recommended fix.');
      //            $("#notify_actionMsg").css("color", "red").html('<span>Please select an entry to attach to the notification as the recommended fix..</span>');
    }
  } else {
    $.notify('Please select an entry to attach to the notification as the recommended fix.');
  }
}
//});

function notifyOthersFix() {
  $('#notify_actionMsg').html('');
  var arrAllcheckVal = '';
  $('input:checkbox[name*=checkNoc]:checked').each(function () {
    arrAllcheckVal += $(this).val() + '~~~~';
  });
  var actionVal = $('input[name=othersSoln]:checked').val();

  var actionStr = '';
  var sclid = arrAllcheckVal;
  if (sclid != '' && actionVal != '' && actionVal != undefined) {
    var selectedRow22 = sclid.split('~~~~');

    var sql = [];

    for (var i = 0; i < selectedRow22.length - 1; i++) {
      var selectedRow1 = selectedRow22[i].split('~~');
      var nid = selectedRow1[0];
      var sitename = selectedRow1[2];
      var machine = selectedRow1[3];
      var cidList = 0;
      var eventTime = selectedRow1[6];
      var eventIdx = 0;

      var status = actionVal;
      var dartnum = selectedRow1[5];
      var ts = Math.round(new Date().getTime() / 1000);

      sql[i] =
        '(' +
        nid +
        ', "' +
        sitename +
        '","' +
        machine +
        '","' +
        status +
        '","' +
        ts +
        '",' +
        dartnum +
        ',' +
        cidList +
        ',"' +
        eventTime +
        '","' +
        eventIdx +
        '","' +
        status +
        '")))';

      actionStr +=
        nid + '~~' + sitename + '~~' + machine + '~~' + status + '~~' + dartnum + '~~' + cidList + '~~' + eventTime + '~~' + actionVal + '~~~~';
    }

    if (actionStr !== '') {
      $.ajax({
        type: 'POST',
        url: 'notification_func.php',
        data: {
          function: 'updateNocStatus',
          machineDet: actionStr,
          name: selNid,
          csrfMagicToken: csrfMagicToken,
        },
        success: function (msg) {
          msg = $.trim(msg);
          if (msg === 'Done') {
            $.notify('Solution pushed successfully');
            notificationDtl_datatable('', selNid, 'mainactive');
            setTimeout(function () {
              rightContainerSlideClose('rsc-add-fix');
              //location.reload();
            }, 1000);
          } else {
            $.notify('Failed to push the solution. Please try again');
            rightContainerSlideClose('rsc-add-fix');
          }
        },
        error: function () {
          //reloadPage(selectedRow22, actionVal);
          //$(".loadingStage").css({'display': 'none'});
        },
      });
    }
  } else {
    $.notify('Please select the solution that you want to pushed for the selected notification');
  }
}

function notifyVarValue(fixedVal, sclid) {
  $('#notify_actionMsg').html('');
  $.ajax({
    type: 'POST',
    url: 'notification_func.php',
    data: {
      function: 'get_notificationSolnDtl',
      notifyArr: sclid,
      csrfMagicToken: csrfMagicToken,
    },
    success: function (gridData) {
      var result = fixedVal.split('##');
      var dart = result[0];
      var variable = result[1];
      var shortDesc = result[2];
      var ProfileName = result[3];
      var profileOS = result[4];

      notifyFixes(dart, variable, shortDesc, ProfileName, profileOS, sclid);
    },
  });
}

function notifyFixes(dart, variable, shortDesc, ProfileName, profileOS, sclid) {
  $('#notify_actionMsg').html('');

  var GroupName = '';
  var params = {
    Dart: dart,
    variable: variable,
    shortDesc: shortDesc,
    Jobtype: 'Notification',
    ProfileName: ProfileName,
    NotificationWindow: '1',
    GroupName: GroupName,
    ProfileOS: profileOS,
    csrfMagicToken: csrfMagicToken,
  };

  // var params = ''; //'function=AddRemoteJobs';
  // params += "&Dart=" + dart;
  // params += "&variable=" + variable;
  // params += "&shortDesc=" + shortDesc;
  // params += "&Jobtype=Notification";
  // params += "&ProfileName=" + ProfileName;
  // params += "&NotificationWindow=1";
  // params += "&GroupName=" + GroupName;
  // params += "&ProfileOS=" + profileOS;

  const functionName = `Add_RemoteJobs`;
  var os = 'windows'; // $("#osTypeDrop").val();
  if (os.toLowerCase() === 'windows') {
    params.OS = 'windows';
    params.function = functionName;
  } else if (os.toLowerCase() === 'android') {
    params.OS = 'android';
    params.function = 'Add_AndroidJobs';
  } else if ($(os.toLowerCase() === 'mac')) {
    params.OS = 'os x';
    params.function = functionName;
  } else if (os.toLowerCase() === 'linux') {
    params.OS = 'linux';
    params.function = functionName;
  } else if (os.toLowerCase() === 'ios') {
    params.OS = 'ios';
    params.function = functionName;
  }
  params.csrfMagicToken = csrfMagicToken;

  $('#executeLoader').show();
  $('#executeJob').hide();
  $.ajax({
    type: 'POST',
    url: '../communication/communication_ajax.php',
    data: params,
    success: function (data) {
      $('#executeLoader').hide();
      if (data !== 'error') {
        var result = data.split('##');
        var SupportedMachines = result[0];
        EmitJobsForServiceTags(SupportedMachines, '');
        var ShowProgressServiceTag = $.trim(result[6]);
        if (ShowProgressServiceTag === 'success') {
          var actionVal = $('input[name=othersSoln]:checked').val();
          var actionStr = '';
          if (sclid != '') {
            var selectedRow22 = sclid.split('~~~~');
            var sql = [];

            for (var i = 0; i < selectedRow22.length - 1; i++) {
              var selectedRow1 = selectedRow22[i].split('~~');
              var nid = selectedRow1[0];
              var sitename = selectedRow1[2];
              var machine = selectedRow1[3];
              var cidList = 0;
              var eventTime = selectedRow1[6];
              var eventIdx = 0;

              var status = actionVal;
              var dartnum = selectedRow1[5];
              var ts = Math.round(new Date().getTime() / 1000);

              //                                sql[i] = '(' + nid + ', "' + sitename + '","' + machine + '","' + status + '","' + ts + '",' + dartnum + ',' + cidList + ',"' + eventTime + '","' + eventIdx + '","' + status + '")';

              actionStr +=
                nid +
                '~~' +
                sitename +
                '~~' +
                machine +
                '~~' +
                status +
                '~~' +
                dartnum +
                '~~' +
                cidList +
                '~~' +
                eventTime +
                '~~' +
                variable +
                '~~~~';
            }

            $.ajax({
              type: 'POST',
              url: 'notification_func.php',
              data: {
                function: 'updateNocStatus',
                name: selNid,
                machineDet: actionStr,
                sugg: 1,
                csrfMagicToken: csrfMagicToken,
              },
              success: function (data) {
                $.notify('Solution pushed successfully');
                rightContainerSlideClose('rsc-add-fix');
                notificationDtl_datatable('', selNid, 'mainactive');
              },
            });
          }
        } else {
          $.notify('Failed to push the solution. Please try again');
          rightContainerSlideClose('rsc-add-fix');
        }
      }
    },
  });
}

function uniqueCheckBox(getStatus) {
  var checked = $('.form-check-input').is(':checked');
  var numberOfChecked = $('.form-check-input:checked').length;
  var getStatusVal = $.trim(getStatus);
  if (checked && numberOfChecked === 1) {
    if (getStatusVal === 'Pending' || getStatusVal === 'New') {
      $('#notifyfix').css({ 'pointer-events': 'fill', color: '#333333' });
      //$("#direct_other_option").css({"pointer-events": "fill", "color": "#333333"});
    } else {
      $('#notifyfix').css({ 'pointer-events': 'none', color: '#bfbfbf' });
      //$("#direct_other_option").css({"pointer-events": "none", "color": "#bfbfbf"});
    }
    $('#view_event_dtl').css({ 'pointer-events': 'fill', color: '#333333' });
  } else if (checked && numberOfChecked > 0) {
    if (getStatusVal === 'Pending' || getStatusVal === 'New') {
      $('#notifyfix').css({ 'pointer-events': 'fill', color: '#333333' });
      //$("#direct_other_option").css({"pointer-events": "fill", "color": "#333333"});
    } else {
      $('#notifyfix').css({ 'pointer-events': 'none', color: '#bfbfbf' });
      //$("#direct_other_option").css({"pointer-events": "none", "color": "#bfbfbf"});
    }
    $('#view_event_dtl').css({ 'pointer-events': 'none', color: '#bfbfbf' });
  } else {
    $('#notifyDtl tbody tr').removeClass('selected');
    $('#notifyfix').css({ 'pointer-events': 'none', color: '#bfbfbf' });
    //$("#direct_other_option").css({"pointer-events": "none", "color": "#bfbfbf"});
    $('#view_event_dtl').css({ 'pointer-events': 'none', color: '#bfbfbf' });
  }

  $('.user_check').change(function () {
    if ($('.user_check:checked').length == $('.user_check').length) {
      $('#notifyDtl tbody tr').removeClass('selected');
      $('#topCheckBox').prop('checked', true);
    } else {
      $('#notifyDtl tbody tr').removeClass('selected');
      $('#topCheckBox').prop('checked', false);
    }
  });
}

function getProfiles() {
  var name = $('#notiname').val();
  $('#notificationName').attr('readonly', 'readonly');

  if (name == '') {
    $.notify('Please choose at least one record');
  } else {
    $.ajax({
      url: 'notification_func.php',
      type: 'post',
      data: {
        function: 'notify_getprofile',
        name: name,
        csrfMagicToken: csrfMagicToken,
      },
      success: function (data) {
        $('#notificationName').val(name);
        $('#soln').html(data);
        $('.selectpicker').selectpicker('refresh');
        rightContainerSlideOn('rsc-update-sol');
      },
    });
  }
}

function updateSol() {
  var name = $('#notificationName').val();
  var value = $('#soln').val();
  var mid = $('#soln option:selected').attr('id');
  if (value == '') {
    $.notify('Please select the solution that must be pushed');
  } else {
    $.ajax({
      url: 'notification_func.php',
      data: {
        function: 'updateSoln',
        name: name,
        val: value,
        mid: mid,
        csrfMagicToken: csrfMagicToken,
      },
      type: 'post',
      success: function (data) {
        $.notify('Solution updated successfully');
        rightContainerSlideClose('rsc-update-sol');
        location.reload();
      },
    });
  }
}

function showNotifFilters() {
  rightContainerSlideOn('rsc-add-filter-container');
}

/*function togglePriority(curRef) {
    if ($(curRef).is(':checked')) {
        $('#prio_p1').prop('checked', true);
        $('#prio_p2').prop('checked', true);
        $('#prio_p3').prop('checked', true);
    } else {
        $('#prio_p1').prop('checked', false);
        $('#prio_p2').prop('checked', false);
        $('#prio_p3').prop('checked', false);
    }
}

function toggleStatus(curRef) {
    if ($(curRef).is(':checked')) {
        $('#status_new').prop('checked', true);
        $('#status_actioned').prop('checked', true);
        $('#status_completed').prop('checked', true);
    } else {
        $('#status_new').prop('checked', false);
        $('#status_actioned').prop('checked', false);
        $('#status_completed').prop('checked', false);
    }
}*/

function loadNotificationUsingFilters() {
  ntype = [];
  priority = [];
  nfstatus = [];
  var prioritySpanData = '';
  var statusSpanData = '';
  $('#notifPriority').html('');
  $('input:checkbox[name*=prio_]:checked').each(function () {
    priority.push($(this).val());
    prioritySpanData += 'P' + $(this).val() + ',';
  });
  $('#notifPriority').html(prioritySpanData.replace(/,\s*$/, ''));

  $('input:checkbox[name*=status_]:checked').each(function () {
    nfstatus.push($(this).val());
    statusSpanData += $(this).val() + ',';
  });
  $('#notifStatus').html(statusSpanData.replace(/,\s*$/, ''));

  $('input:checkbox[name*=ntype_]:checked').each(function () {
    ntype.push($(this).val());
  });

  rightContainerSlideClose('rsc-add-filter-container');

  if (priority.length <= 0) {
    $.notify('Please select atleast one Priority type');
    return;
  }

  if (nfstatus.length <= 0) {
    $.notify('Please select atleast one Status type');
    return;
  }

  if (ntype.length <= 0) {
    $.notify('Please select atleast one Notification type');
    return;
  }
  notification_datatable(priority, nfstatus, ntype);
}

function showMoreDetails(event) {
  $('#showmoreEventDtl').hide();
  $('#EventId').html('');
  $('#MachName').html('');
  $('#SiteName').html('');
  $('#UName').html('');
  $('#ServerTime').html('');
  $('#ClientTime').html('');
  $('#ClientVersion').html('');
  $('#EventDet').html('');
  rightContainerSlideOn('rsc-more-details');
  $('.more-loader').show();
  let gridData = event;

  var text1 = gridData.text1;
  var text2 = gridData.text2;
  var text3 = gridData.text3;
  var text4 = gridData.text4;
  var string1 = gridData.string1;
  var string2 = gridData.string2;

  try {
    if (typeof gridData.text1 === 'string') {
      text1 = JSON.parse(gridData.text1);
    }
  } catch (e) {}
  try {
    if (typeof gridData.text2 === 'string') {
      text2 = JSON.parse(gridData.text2);
    }
  } catch (e) {}
  try {
    if (typeof gridData.text3 === 'string') {
      text3 = JSON.parse(gridData.text3);
    }
  } catch (e) {}
  try {
    if (typeof gridData.text4 === 'string') {
      text4 = JSON.parse(gridData.text4);
    }
  } catch (e) {}
  try {
    if (typeof gridData.string1 === 'string') {
      string1 = JSON.parse(gridData.string1);
    }
  } catch (e) {}
  try {
    if (typeof gridData.string2 === 'string') {
      string2 = JSON.parse(gridData.string2);
    }
  } catch (e) {}

  var eventDetails = Object.assign({}, text1, text2, text3, text4, string1, string2);
  console.log(eventDetails, 'eventDetails');
  var eventDhtml = '';

  for (const [key, value] of Object.entries(eventDetails)) {
    if (typeof value === 'object') {
      for (const [key1, value1] of Object.entries(value)) {
        let vStr = value1;
        if (typeof vStr === 'object') {
          vStr = JSON.stringify(vStr);
        }
        eventDhtml += `<tr>
          <td>${key}->${key1}:</td>
          <td>${vStr}</td>
        </tr>`;
      }
    } else {
      eventDhtml += `<tr>
      <td>${key}:</td>
      <td>${value}</td>
    </tr>`;
    }
  }
  $('.more-loader').hide();
  $('#showmoreEventDtl').show();
  // $('#EventId').html(gridData[0].eventId);
  $('#MachName').html(gridData.machine);
  $('#SiteName').html(gridData.site);
  $('#UName').html(gridData.uname);
  // $('#ServerTime').html(gridData[0].serverTime);
  $('#ClientTime').html(gridData.ctime);
  $('#ClientVersion').html(gridData.cver);
  $('#EventDet').html(eventDhtml);
  $('#DartNumber').html(gridData.scrip);
}

function showNearbyEvents(machineName) {
  $('#deviceName').html(machineName);
  rightContainerSlideOn('rsc-event-details');
  $('#notifyeventAnalyserDtl').hide();
  $('.nearby-loader').show();
  $('#selectedMachineName').val(machineName);
  var startDate = $('#eventStartDate').val() / 1;
  var endDate = $('#eventEndDate').val() / 1;

  startDate += new Date().getTimezoneOffset() * 60;
  endDate += new Date().getTimezoneOffset() * 60;
  $.ajax({
    url: 'notification_func.php',
    data: {
      function: 'showNearbyEvents',
      machine: machineName,
      startDate: startDate,
      endDate: endDate,
      csrfMagicToken: csrfMagicToken,
    },
    dataType: 'json',
    type: 'post',
    success: function (gridData) {
      $('#notifyeventAnalyserDtl').show();
      $('.nearby-loader').hide();
      $('#notifyeventAnalyserDtl').DataTable().destroy();
      table4 = $('#notifyeventAnalyserDtl').DataTable({
        scrollY: 'calc(100vh - 240px)',
        aaData: gridData,
        autoWidth: false,
        paging: true,
        searching: false,
        processing: false,
        serverSide: false,
        ordering: true,
        select: true,
        bInfo: false,
        responsive: false,
        stateSave: true,
        pagingType: 'full_numbers',
        stateSaveParams: function (settings, data) {
          data.search.search = '';
        },
        lengthMenu: [
          [10, 25, 50, 100],
          [10, 25, 50, 100],
        ],
        language: {
          info: '_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>',
        },
        dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        drawCallback: function (settings) {},
        fnInitComplete: function (oSettings, json) {},
      });
    },
    error: function (err) {
      $('.nearby-loader').hide();
      console.log(err);
    },
  });
}

$(function () {
  var start = moment();
  start = moment(start).unix();

  var end = moment().add(30, 'minute');
  end = moment(end).unix();

  $('#eventStartDate').val(start);
  $('#eventEndDate').val(end);
  $('input[name="daterange"]').daterangepicker(
    {
      opens: 'left',
      timePicker: true,
      startDate: moment(),
      endDate: moment().add(30, 'minute'),
      locale: {
        format: 'M/DD hh:mm A',
      },
      maxSpan: {
        days: 1,
      },
      timePickerIncrement: 30,
    },
    function (startDate, endDate, label) {
      debugger;
      startDate = moment(startDate).unix();
      endDate = moment(endDate).unix();
      $('#eventStartDate').val(startDate);
      $('#eventEndDate').val(endDate);
      var mach = $('#selectedMachineName').val();
      showNearbyEvents(mach);
      // console.log("A new date selection was made: " + startDate.format('YYYY-MM-DD') + ' to ' + endDate.format('YYYY-MM-DD'));
    },
  );
});
