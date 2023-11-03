$(function () {
  $('#distribution_searchbox').keyup(function () {
    var count = $('#patchesGrid tr').length;
    if (count == 2) {
      auditGridDetail('ioweurfvbhioebfviovivbrivbiervb');
    }
  });
});

var totPatchtable;

$(document).ready(function () {
  $('.bottomtable').remove();
  $('#softrepo').hide();
  $('#softdist').show();

  $('.dropdown-menu li a').css('white-space', 'nowrap');

  $('#softaudit_searchbox').keyup(function () {
    totPatchtable.search(this.value).draw();
  });
  $('.bottompager').each(function () {
    $(this).append($(this).find('.bottomtable'));
  });
  $('#patchesGrid_filter').hide();

  $('#ascrail2008-hr').removeAttr('style');
  $('#ascrail2008-hr div').removeAttr('style');
  $('#ascrail2007-hr').removeAttr('style');
  $('#ascrail2007-hr div').removeAttr('style');
});

function Get_SoftwareDistributionData() {
  var bid = '';
  var pack = '';
  var typecheck = $('#searchType').val();
  if (typecheck == 'Groups') {
    var auditSearch = $('#rparentName').val();
  } else {
    var auditSearch = $('#searchValue').val();
  }

  $('#softrepository').hide();
  $('#softdistribution').show();
  $('#audit_selected_title').text(auditSearch);

  $.ajax({
    type: 'POST',
    url: 'SWD_Function.php',
    data: { function: 'packagesSelectFn', auditSearch: auditSearch, csrfMagicToken: csrfMagicToken },

    dataType: 'json',
    success: function (gridData) {
      console.log(gridData);
      $('.group-page').show();
      $('.se-pre-con').hide();
      $('#patchesGrid').DataTable().destroy();

      var patchGridDTObj = $('#patchesGrid').DataTable({
        //scrollY: $('#patchesGrid').data('height'),
        scrollY: 'calc(100vh - 240px)',
        scrollCollapse: true,
        paging: true,
        searching: true,
        ordering: true,
        aaData: gridData,
        bAutoWidth: false,
        select: false,
        bInfo: false,
        responsive: true,
        stateSave: true,
        stateSaveParams: function (settings, data) {
          data.search.search = '';
        },
        order: [[1, 'desc']],
        //"lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        lengthMenu: [
          [20, 25, 50, 100],
          [20, 25, 50, 100],
        ],
        language: {
          info: '_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>',
          searchPlaceholder: 'Search',
        },
        dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
        initComplete: function (settings, json) {
          $('#patchesGrid tbody tr:eq(0)').addClass('selected');
          if (gridData.length > 0) {
            bid = gridData[0][4];
            pack = gridData[0][3];
          } else {
            bid = '';
            pack = '';
          }
          $('#selected').val(bid);
          $('#selectedPackage').val(pack);
          auditGridDetail(pack);
        },
        //                drawCallback: function (settings) {
        //                    $(".dataTables_scrollBody").mCustomScrollbar({
        //                        theme: "minimal-dark"
        //                    });
        //                    $('.equalHeight').matchHeight();
        //                    $(".se-pre-con").hide();
        //                }
      });

      window.totPatchtable = patchGridDTObj;

      $('#distribution_searchbox').keyup(function () {
        totPatchtable.search(this.value).draw();
        $('#patchesGrid tbody tr:eq(0)').click();
      });
    },
    error: function (msg) {},
  });

  $('#patchesGrid').on('click', 'tr', function () {
    var rowID = totPatchtable.row(this).data();
    var bid = rowID[4];
    var pack = rowID[3];
    $('#selected').val(bid);
    $('#selectedPackage').val(rowID[3]);

    auditGridDetail(pack);

    totPatchtable.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
  });
}

function auditGridDetail(pack) {
  $('#se-pre-con-loader').show();
  $('.move-left-right .bottomtable').remove();
  var bid = $('#selected').val();
  var typecheck = $('#searchType').val();
  if (typecheck == 'Groups') {
    var auditSearch = $('#rparentName').val();
  } else {
    var auditSearch = $('#searchValue').val();
  }

  $('#auditGridDetail').dataTable().fnDestroy();
  var detailTable = $('#auditGridDetail').DataTable({
    //scrollY: $('#auditGridDetail').data('height'),
    scrollY: 'calc(100vh - 240px)',
    scrollCollapse: true,
    autoWidth: true,
    searching: false,
    processing: true,
    serverSide: true,
    stateSave: true,
    stateSaveParams: function (settings, data) {
      data.search.search = '';
    },
    ajax: {
      // url: "SWD_Function.php?function=softwareAuditDetailsFn&pack=" + pack + "&bid=" + bid + '&audit=' + auditSearch+"&csrfMagicToken=" + csrfMagicToken,
      url: 'SWD_Function.php',
      data: { function: softwareAuditDetailsFn, pack: pack, bid: bid, audit: auditSearch, csrfMagicToken: csrfMagicToken },
      type: 'POST',
    },
    drawCallback: function (settings) {
      //            $(".dataTables_scrollBody").mCustomScrollbar({
      //                theme: "minimal-dark"
      //            });
      $('#se-pre-con-loader').hide();
    },
    columns: [{ data: 'SelectionType' }, { data: 'MachineTag' }, { data: 'Status' }],
    language: {
      info: '_START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>',
      searchPlaceholder: 'Search',
    },
    //lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
    lengthMenu: [
      [20, 25, 50, -1],
      [20, 25, 50, 'All'],
    ],
    columnDefs: [
      {
        targets: 2,
        orderable: false,
      },
      { className: 'ignore', targets: 0 },
      { className: 'ignore', targets: 1 },
      { className: 'ignore', targets: 2 },
    ],
    ordering: true,
    select: false,
    bInfo: false,
    responsive: true,
    dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
    searching: true,
  });

  $('#auditGridDetail tbody').on('click', 'tr', function () {
    detailTable.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
  });
  $('.bottompager').each(function () {
    $(this).append($(this).find('.bottomtable'));
  });
}

function allexportPatch() {
  var typecheck = $('#searchType').val();
  if (typecheck == 'Groups') {
    var auditSearch = $('#rparentName').val();
  } else {
    var auditSearch = $('#searchValue').val();
  }
  window.location.href = 'SWD_Function.php?function=allexportPatchFn&auditSearch=' + auditSearch + '&csrfMagicToken=' + csrfMagicToken;
}
function exportaudit() {
  var packageID = $('#selected').val();
  if (packageID != '') {
    rightMenuFunctionality();
    rightContainerSlideOn('rsc-export-slider');
  } else {
    $.notify('Please select the package to Export');
  }
}

function ExportAuditConfig() {
  var typecheck = $('#searchType').val();
  if (typecheck == 'Groups') {
    var auditSearch = $('#rparentName').val();
  } else {
    var auditSearch = $('#searchValue').val();
  }
  var packageID = $('#selected').val();
  var packageName = $('#selectedPackageName').val();
  window.location.href =
    '../softdist/SWD_Function.php?function=exportauditFn' +
    '&bid=' +
    packageID +
    '&auditSearch=' +
    auditSearch +
    '&packageName=' +
    packageName +
    '&csrfMagicToken=' +
    csrfMagicToken;
  $.notify('Data Exported Successfully');
  rightContainerSlideClose('rsc-export-slider');
}

function AuditDetailStatusFn(stat, tid, eventList) {
  $('#rightNavtiles').css({ display: 'none' });

  if (tid != '') {
    $.ajax({
      url:
        '../softdist/SWD_Function.php?function=AuditDetailStatusFn&stat=' +
        stat +
        '&eid=' +
        tid +
        '&eventList=' +
        eventList +
        '&csrfMagicToken=' +
        csrfMagicToken,
      type: 'post',
      dataType: 'json',
      success: function (data) {
        $('#successpop').modal('show');
        $('input[type=text]').prev().parent().removeClass('is-empty');
        $('#executed').val(data.username).attr('title', data.username);
        $('#servertime').val(data.servertime).attr('title', data.servertime);
        $('#agenttime').val(data.servertime).attr('title', data.servertime);
        $('#nodetime').val(data.nodetrigger).attr('title', data.nodetrigger);
        $('#clienttime').val(data.clienttime).attr('title', data.clienttime);

        $('#eventuser').val(data.eventuser).attr('title', data.eventuser);
        $('#eventserver').val(data.eventserver).attr('title', data.eventserver);
        $('#eventcustomer').val(data.eventcustomer).attr('title', data.eventcustomer);
        $('#eventuuid').val(data.eventuuid).attr('title', data.eventuuid);
        $('#eventversion').val(data.eventversion).attr('title', data.eventversion);
        $('#eventdescription').val(data.eventdescription).attr('title', data.eventdescription);
        $('#eventsize').val(data.eventsize).attr('title', data.eventsize);
        $('#eventid').val(data.eventid).attr('title', data.eventid);
        $('#eventstring2').val(data.eventstring2).attr('title', data.eventstring2);

        $('#eventclient').val(data.eventclient).attr('title', data.eventclient);
        $('#eventscrip').val(data.eventscrip).attr('title', data.eventscrip);
        $('#eventmachine').val(data.eventmachine).attr('title', data.eventmachine);
        $('#eventusername').val(data.eventusername).attr('title', data.eventusername);
        $('#eventpriority').val(data.eventpriority).attr('title', data.eventpriority);
        $('#eventtype').val(data.eventtype).attr('title', data.eventtype);
        $('#eventversion').val(data.eventversion).attr('title', data.eventversion);
        $('#eventid2').val(data.eventid2).attr('title', data.eventid2);
        $('#eventstring1').val(data.eventstring1).attr('title', data.eventstring1);

        $('#eventpath').val(data.eventpath).attr('title', data.servertime);
        $('#eventtext1').val(data.eventtext1).attr('title', data.eventtext1);
        $('#eventtext2').val(data.eventtext2).attr('title', data.eventtext2);
        $('#eventtext3').val(data.eventtext3).attr('title', data.eventtext3);
        $('#eventtext4').val(data.eventtext4).attr('title', data.eventtext4);
      },
    });
  }
}
