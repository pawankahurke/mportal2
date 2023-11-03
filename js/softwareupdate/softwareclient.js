$(document).ready(function () {
  $('.main').show();
  $('.version').hide();
  $('.softwareupdategrid').show();
  $('.versiondetailoslist').hide();

  $('input.cuo-cl').on('click', function () {
    var target, hide;

    if (!isNaN($(this).val())) {
      switch (parseInt($(this).val())) {
        case 1:
          target = 'av-durl-w';
          hide = 'av-upc-w';
          break;
        case 2:
          hide = 'av-durl-w';
          target = 'av-upc-w';
          break;
      }
    }

    if (target != undefined && hide != undefined) {
      $(this)
        .parents('.rightSidenav')
        .find('.' + target)
        .show();
      $(this)
        .parents('.rightSidenav')
        .find('.' + hide)
        .hide();
    }
  });

  $(document).on('click', '#open-upload-core-db-wrap', function () {
    //document.querySelector('#upload-core-db-wrap input[name=db-name]').value = '';
    //document.querySelector('#upload-core-db-wrap input[name=db-name]').parentElement.style.display = 'block';
    document.querySelectorAll('#upload-core-db-wrap .av-upc-w')[0].style.display = 'flex';
    document.querySelectorAll('#upload-core-db-wrap .av-import-w')[0].style.display = 'none';
    document.getElementById('dismiss-upload-dbn').click();
    rightContainerSlideOn('upload-core-db-wrap');
    $('.core-dbn-chng').show();
  });

  $(document).on('click', '.import-dbn-data-w', function () {
    importCoreDBN(this);
  });
});
function showCoreDbnUploadCntnr() {
  rightContainerSlideOn('upload-core-db-wrap');
}

function getVersionTable() {
  closePopUp();
  showallversionDetails();
  $('.main').hide();
  $('.version').show();
  $('.softwareupdategrid').hide();
  $('.versiondetailoslist').show();
}

function gotoMain() {
  closePopUp();
  softwareupdategridlist();
  $('.main').show();
  $('.version').hide();
  $('.softwareupdategrid').show();
  $('.versiondetailoslist').hide();
}
function softwareupdategrid() {
  $('.order-table').DataTable({
    scrollY: 'calc(100vh - 240px)',
    scrollCollapse: true,
    autoWidth: false,
    bAutoWidth: true,
    responsive: true,
    stateSave: true,
    stateSaveParams: function (settings, data) {
      data.search.search = '';
    },
    columnDefs: [
      {
        targets: 'datatable-nosort',
        orderable: false,
      },
    ],
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, 'All'],
    ],
    language: {
      search: '_INPUT_',
      searchPlaceholder: 'Search Records',
    },
    dom: '<"top"f>rt<"bottom"lp><"clear">',
  });
  $('.dataTables_filter input').addClass('form-control');
  $('#versiondetailpoup').on('shown.bs.modal', function () {
    $('#versionDetail').show();
    $('#versionDetail').DataTable().columns.adjust().draw();
  });
  $('#versiondetailos').on('shown.bs.modal', function () {
    $('#versiondetailoslist').show();
    $('#versiondetailoslist').DataTable().columns.adjust().draw();
  });
}

function softwareupdategridlist() {
  $('#loader').show();
  var search = $('#softwareupdateSearch').text();
  $('#softwareupdategrid').dataTable().fnDestroy();
  softwareTable = $('#softwareupdategrid').DataTable({
    scrollY: 'calc(100vh - 240px)',
    scrollCollapse: true,
    autoWidth: false,
    searching: false,
    serverSide: false,
    bAutoWidth: true,
    stateSave: true,
    stateSaveParams: function (settings, data) {
      data.search.search = '';
    },
    ajax: {
      url: 'softwarefunctions.php',
      type: 'POST',
      data: {
        function: 'softwareData',
        csrfMagicToken: csrfMagicToken,
      },
    },
    language: {
      info: 'Showing _START_-_END_ <span>of</span> _TOTAL_ <span>entries</span>',
      search: '_INPUT_',
      searchPlaceholder: 'Search Records',
    },
    lengthMenu: [
      [10, 25, 50, 100],
      [10, 25, 50, 100],
    ],
    columns: [{ data: 'sitename' }, { data: 'machine' }, { data: 'os' }, { data: 'version' }, { data: 'action' }],
    ordering: true,
    select: false,
    bInfo: false,
    responsive: true,
    dom: '<"table-wrap"<"top"f>rt<"bottom"lpi><"clear">>',
    initComplete: function (settings, json) {
      softwareTable.$('tr:first').click();
      $('.loader').hide();
    },
    drawCallback: function (settings) {
      $('.loader').hide();
    },
  });
  $('.dataTables_filter input').addClass('form-control');
  $('#softwareupdate_searchbox').keyup(function () {
    softwareTable.search(this.value).draw();
  });

  $('#softwareupdategrid').on('click', 'tr', function () {
    //row selection code
    softwareTable.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
    if ($(this).hasClass('selected')) {
      var rowdata = softwareTable.row(this).data();
      $('#selected').val(rowdata.id);
      $('#sitename').val(rowdata.site);
      $('#Complete_sitename').val(rowdata.completeSiteName);
    } else {
      var rowdata = softwareTable.row(this).data();
      softwareTable.$('tr.selected').removeClass('selected');
      $('#selected').val(rowdata.id);
      $('#sitename').val(rowdata.site);
      $(this).addClass('selected');
    }
    var type = $('#searchType').val();
    $('#detailexport').hide();
    //$('#updateversion').hide();
  });
}

function selectConfirm(data_target_id) {
  var site = $('#sitename').val();
  if (data_target_id == 'version_detail') {
    if (site != '') {
      versiondetailpopup(site);
      rightContainerSlideOn('version_detail');
    } else {
      $.notify('Please select a record');
      closePopUp();
    }
  }
}

function deleteVersion() {
  var versionid = $('#vesiondeailid').val();
  if (versionid == undefined || versionid == 'undefined' || versionid == '') {
    $.notify('Please choose at least one record');
    closePopUp();
  } else {
    sweetAlert({
      title: 'Are you sure that you want to continue?',
      text: 'If you delete the version, you will not be able to undo the action.',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#050d30',
      cancelButtonColor: '#fa0f4b',
      cancelButtonText: 'No, cancel it!',
      confirmButtonText: 'Yes, delete it!',
    })
      .then(function (result) {
        $.ajax({
          url: 'softwarefunctions.php?function=delete_version',
          type: 'post',
          data: 'id=' + versionid + '&csrfMagicToken=' + csrfMagicToken,
          dataType: 'json',
          success: function (data) {
            if (data.msg == 'success') {
              $.notify('Version deleted successfully');
              closePopUp();

              showallversionDetails();
            } else {
              $.notify('Failed to delete the version. Please try again');
              closePopUp();
            }
          },
        });
      })
      .catch(function (reason) {
        $('.closebtn').trigger('click');
      });
    closePopUp();
  }
}

function editversionpopup(id, site) {
  $.ajax({
    url: 'softwarefunctions.php?function=get_editversiondata&idx=' + id + '&sitename=' + site + '&csrfMagicToken=' + csrfMagicToken,
    type: 'post',
    dataType: 'json',
    success: function (data) {
      $('#versionsitename').text('Edit Version : ' + data.sitename);
      $('#windowselect').val(data.windows).change();
      $('#androidselect').val(data.android).change();
      $('#linuxselect').val(data.linux).change();
      $('#macselect').val(data.mac).change();
      $('.selectpicker').selectpicker('refresh');
    },
  });
}

function versionadd() {
  var site = $('#sitename').val();
  var windowval = $('#windowselect').val();
  var androidval = $('#androidselect').val();
  var linuxval = $('#linuxselect').val();
  var macval = $('#macselect').val();
  var iosval = $('#iosselect').val();

  if (site === '') {
    $.notify('Please select the Site');
    return false;
  }

  if (windowval == '' && androidval == '' && linuxval == '' && macval == '' && iosval == '') {
    $.notify('Please select a version');
    return false;
  }

  //return false;
  $.ajax({
    url:
      'softwarefunctions.php?function=get_versionadd&window=' +
      windowval +
      '&android=' +
      androidval +
      '&linux=' +
      linuxval +
      '&mac=' +
      macval +
      '&site=' +
      site +
      '&ios=' +
      iosval +
      '&csrfMagicToken=' +
      csrfMagicToken,
    type: 'post',
    dataType: 'json',
    success: function (data) {
      $.notify('Version updated successfully');
      rightContainerSlideClose('update-version');
      closePopUp();
      softwareupdategridlist();
    },
  });
}

function versiondetailpopup(site) {
  var site = $('#Complete_sitename').val();

  $.ajax({
    type: 'GET',
    url: 'softwarefunctions.php?function=get_versionmachinelist&site=' + site + '&csrfMagicToken=' + csrfMagicToken,
    data: '',
    dataType: 'json',
    success: function (gridData) {
      $('#versionDetail').DataTable().destroy();
      groupTable = $('#versionDetail').DataTable({
        scrollY: 'calc(100vh - 240px)',
        scrollCollapse: false,
        paging: true,
        searching: false,
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
        lengthMenu: [
          [10, 25, 50, 100],
          [10, 25, 50, 100],
        ],
        language: {
          search: '_INPUT_',
          searchPlaceholder: 'Search records',
        },
        dom: '<"top"f>rt<"bottom"lp><"clear">',
        columnDefs: [
          {
            targets: 'datatable-nosort',
            orderable: false,
          },
          {
            className: 'table-plus',
            targets: 0,
          },
        ],
        drawCallback: function (settings) {
          $(".checkbox-btn input[type='checkbox']").change(function () {
            if ($(this).is(':checked')) {
              $(this).parents('tr').addClass('selected');
            }
          });
        },
      });
      $('.tableloader').hide();
    },
    error: function (msg) {},
  });
  $('#versionDetail_filter').hide();
}

/* ===== group view detail grid column onload click function =====*/
function versionpopupclicked() {
  setTimeout(function () {
    $('.event-info-grid-host').click();
  }, 300);
}

function versionlistexport() {
  var site = $('#sitename').val();
  window.location.href = 'softwarefunctions.php?function=get_machinelistexport&site=' + site + '&csrfMagicToken=' + csrfMagicToken;
  closePopUp();
}

function showallversionDetails() {
  $.ajax({
    type: 'post',
    url: 'softwarefunctions.php?function=get_versionlist',
    data: { csrfMagicToken: csrfMagicToken },
    dataType: 'json',
    success: function (gridData) {
      $('#versiondetailoslist').DataTable().destroy();
      groupTable = $('#versiondetailoslist').DataTable({
        scrollY: 'calc(100vh - 240px)',
        scrollCollapse: true,
        paging: true,
        searching: false,
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
        lengthMenu: [
          [10, 25, 50, 100],
          [10, 25, 50, 100],
        ],
        language: {
          search: '_INPUT_',
          searchPlaceholder: 'Search records',
        },
        dom: '<"top"f>rt<"bottom"lp><"clear">',
        initComplete: function (settings, json) {},
        drawCallback: function (settings) {},
      });
    },
    error: function (msg) {},
  });
  $('#versiondetailoslist').on('click', 'tr', function () {
    //row selection code
    groupTable.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
    if ($(this).hasClass('selected')) {
      var rowdata = groupTable.row(this).data();
      $('#vesiondeailid').val(rowdata.id);
    } else {
      var rowdata = groupTable.row(this).data();
      groupTable.$('tr.selected').removeClass('selected');
      $('#vesiondeailid').val(rowdata.id);
      $(this).addClass('selected');
    }
  });
  $('#versiondetailoslist').on('dblclick', 'tr', function () {
    var versionid = $('#vesiondeailid').val();
    if (versionid != '' && versionid != 'undefined' && versionid !== undefined) {
      rightContainerSlideOn('edit-version');
      $('#editOption').show();
      $('#toggleButton').hide();
      disableFields();
      editversiondetail_list(versionid);
    }
  });
}

function addversiondetail() {
  var Vname = $('#versionname').val();
  var Vnumber = $('#versionnumber').val();
  var Os = $('#versionostype').val();
  var check = $('#global').is(':checked') ? 1 : 0;
  var Durl = $('#url').val();
  var Uname = $('#user_name').val();
  var Pass = $('#pass_word').val();
  var command = $('#commandline').val();

  var formData = new FormData();

  var clientOptionRadio = $('#add-version').find('input.cuo-cl:checked').val();
  clientOptionRadio = !isNaN(clientOptionRadio) ? parseInt(clientOptionRadio) : false;

  if (Vname === '') {
    $.notify('Please enter the Version Name');
    return false;
  } else {
    if (/\s/.test(Vname)) {
      $.notify('Please Enter a valid Version Name');
      return false;
    } else {
      if (!validate_alphanumeric_underscore(Vname)) {
        $.notify('Only alphanumeric values and underscore is allowed in the name field');
        return false;
      }
    }
  }

  if (Vnumber === '') {
    $.notify('Please enter version number');
    return false;
  } else {
    $('#required_versionnumber').html('');
    if (!validate_numeric_dot(Vnumber)) {
      $.notify('Please enter valid version number');
      return false;
    }
  }

  if (Os === '') {
    $.notify('Please select the OS');
    return false;
  }
  if (clientOptionRadio && clientOptionRadio == 1 && Durl === '') {
    $.notify('Please enter download url');
    return false;
  } else {
    if (clientOptionRadio && clientOptionRadio == 1 && !validateUrl(Durl)) {
      $.notify('Please enter a valid URL');
      return false;
    }
  }

  if (Uname === '') {
    //$.notify('Please enter username');
    //return false;
  } else {
    if (/\s/.test(Vname)) {
      $.notify('Please Enter a valid username');
      return false;
    } else {
      if (!validate_Name(Uname)) {
        $.notify('Please enter a valid Username');
        return false;
      }
    }
    formData.append('uname', Uname);
  }

  if (Pass === '') {
    //$.notify('Please enter password');
    //return false;
  } else {
    formData.append('pass', Pass);
  }

  if (command === '') {
    //$.notify('Please enter command line');
    //return false;
  } else {
    if (!validate_AlphaSlashSpace(command)) {
      $.notify('Please enter valid command line');
      return false;
    }
    formData.append('command', encodeURIComponent(command));
  }

  var inputFileData = $('#add-version').find('input[name=raw-client]')[0].files[0];

  if (clientOptionRadio && 2 == clientOptionRadio && inputFileData == undefined) {
    errorNotify('Client to be uploaded is required');
    return false;
  }

  if (Vname != '' && Vnumber != '' && Os != '') {
    var rightSlider = new RightSlider('#add-version');
    rightSlider.showLoader();

    formData.append('vname', Vname);
    formData.append('vnumber', Vnumber);
    formData.append('os', Os);
    formData.append('check', check);

    if (clientOptionRadio) {
      if (1 == clientOptionRadio) {
        formData.append('Durl', Durl);
      } else if (2 == clientOptionRadio) {
        formData.append('client', inputFileData);
      }
    }
    formData.append('csrfMagicToken', csrfMagicToken);
    $.ajax({
      url: 'softwarefunctions.php?function=get_versionsubmit',
      type: 'post',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (data) {
        rightSlider.hideLoader();
        if (data.msg == 'success') {
          $.notify('Version added Successfully');
          closePopUp();
          rightContainerSlideClose('add-version');

          showallversionDetails();
        } else if (data.msg == 'invalidmime') {
          $.notify('Invalid Client extension');
          closePopUp();
        } else if (data.msg == 'failed') {
          $.notify('Version name already exists');
          closePopUp();
        } else if (data.msg === 'Failed to upload file') {
          $.notify(data.msg);
          closePopUp();
        }
      },
    });
  }
}

function uploadCoreDB() {
  var wrapper = document.querySelector('#upload-core-db-wrap');
  //var name = wrapper.querySelector('input[name=db-name]');

  /*if (name.value == undefined || name.value == '') {
     errorNotify("The name is required");
     name.focus();
     return false;
     }*/
  if (document.getElementById('core-file').files.length == 0) {
    errorNotify('The file to upload is required');
    name.focus();
    return false;
  }

  var filename = $('#core-file').val();
  filename = filename.substring(filename.lastIndexOf('\\') + 1, filename.length);

  if (!/\.dbn$/.test(filename)) {
    errorNotify('Please select core.dbn file format');
    name.focus();
    return false;
  }

  /*if (document.querySelector('input[name=core-file]').files[0] == undefined) {
     errorNotify("The file to upload is required");
     name.focus();
     return false;
     }*/

  var formData = new FormData(document.querySelector('.upload-core-db-frm'));

  $('.core-dbn-chng').hide();
  $('#core-dbn-ldr').show();
  formData.append('csrfMagicToken', csrfMagicToken);
  formData.append('function', 'uploadCoreDb');
  $.ajax({
    url: 'softwarefunctions.php',
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function (data) {
      if (data.msg == 'success') {
        if (data.import_key != '') {
          $.ajax({
            url: 'softwarefunctions.php',
            type: 'POST',
            data: { function: 'inspectDBNVers', key: data.import_key, csrfMagicToken: csrfMagicToken },
            success: function (data) {
              $('#core-dbn-ldr').hide();
              wrapper.querySelectorAll('.av-upc-w')[0].style.display = 'none';
              wrapper.querySelectorAll('.av-import-w')[0].innerHTML = data;
              wrapper.querySelectorAll('.av-import-w')[0].style.display = 'block';
              //wrapper.querySelector('input[name=db-name]').parentElement.style.display = 'none';
            },
          });
        }

        $('#core-dbn-ldr').hide();
        successNotify('Successfully uploaded file');
      } else if (data.msg == 'failed') {
        $.notify('Failed to upload the file. Please try again');
      } else {
        $.notify(data.msg);
      }
    },
    error: function (e) {
      $.notify(`Error: ${JSON.stringify(e)}`);
    }
  });
}

let importCoreDBN_status = false;
function importCoreDBN(element) {
  var vers = element.getAttribute('data-vers') == undefined ? '' : element.getAttribute('data-vers'),
    type = element.getAttribute('data-type') == undefined ? '' : element.getAttribute('data-type'),
    key = element.getAttribute('data-key') == undefined ? '' : element.getAttribute('data-key');

  if (importCoreDBN_status) {
    successNotify("Let's wait, CoreDBN import in progress");
    return;
  }
  successNotify('Import Started');
  $('#import-dbn-ldr').show();
  importCoreDBN_status = true;
  $.ajax({
    url: 'softwarefunctions.php',
    type: 'POST',
    data: { function: 'importVars', key: key, type: type, vers: vers, csrfMagicToken: csrfMagicToken },
    success: function (data) {
      $('#import-dbn-ldr').hide();
      importCoreDBN_status = false;
      successNotify('Successfully imported dbn<br /><b>' + data + '</b>');
      rightContainerSlideClose('upload-core-db-wrap');
    },
    error: e => {
      importCoreDBN_status = false;
      errorNotify('Error:' + JSON.stringify(e));
    },
  });
}

/*$('#versionname').keydown(function () {
 $('#addversionerror').hide();
 })*/

$('#versionnumber').keydown(function () {
  $('#addversionerror').hide();
});

function editversiondetail_list(vid) {
  $.ajax({
    url: 'softwarefunctions.php?function=get_editversiondetailedlist&id=' + vid + '&csrfMagicToken=' + csrfMagicToken,
    type: 'post',
    dataType: 'json',
    success: function (data) {
      $('input[type=text]').prev().parent().removeClass('is-empty');
      $('input[type=text]').prev().parent().addClass('is-focused');
      $('input[type=password]').prev().parent().removeClass('is-empty');
      $('input[type=password]').prev().parent().addClass('is-focused');
      $('#editversionname').val(data[0].name);
      $('#editversionnumber').val(data[0].version);
      $('#editversionostype').val(data[0].os);
      $('#editurl').val(data[0].url);
      $('#edituser_name').val(data[0].username);
      $('#editpass_word').val(data[0].password);
      $('#editcommandline').val(data[0].cmdline);
      if (data[0].check == '1') {
        $('#edit_global').attr('checked', 'checked');
      }
      $('.selectpicker').selectpicker('refresh');
    },
  });
}

function editversiondetail() {
  var Vname = $('#editversionname').val();
  var Vnumber = $('#editversionnumber').val();
  var Os = $('#editversionostype').val();
  var check = $('#edit_global').is(':checked') ? 1 : 0;
  var Durl = $('#editurl').val();
  var Uname = $('#edituser_name').val();
  var Pass = $('#editpass_word').val();
  var command = $('#editcommandline').val();
  var versionid = $('#vesiondeailid').val();

  var clientOptionRadio = $('#edit-version').find('input.cuo-cl:checked').val();
  clientOptionRadio = !isNaN(clientOptionRadio) ? parseInt(clientOptionRadio) : false;

  if (Vname == '') {
    $.notify('Please enter the Version Name');
    return false;
  } else {
    var regx = /[^a-zA-Z0-9\_\s]/;
    if (regx.test(Vname)) {
      $.notify('Only alphanumeric values and underscore is allowed in the name field');
      return false;
    }
  }

  if (Vnumber == '') {
    $.notify('Please enter version number');
    return false;
  } else {
    $('#required_versionnumber').html('');
    var regx = /[^0-9\.]/;
    if (regx.test(Vnumber)) {
      $.notify('Please enter valid version number');
      return false;
    }
  }

  if (Os == '') {
    $.notify('Please select the OS');
    return false;
  }
  if (clientOptionRadio && 1 == clientOptionRadio && Durl == '') {
    $.notify('Please enter download url');
    return false;
  }
  if (command == '') {
    $.notify('Please enter command line');
    return false;
  } else {
    if (!validate_AlphaSlashSpace(command)) {
      $.notify('Please enter valid command line');
      return false;
    }
  }

  var inputFileData = $('#edit-version').find('input[name=raw-client]')[0].files[0];

  if (clientOptionRadio && 2 == clientOptionRadio && inputFileData == undefined) {
    errorNotify('Client to be uploaded is required');
    return false;
  }

  if (Vname != '' && Vnumber != '' && Os != '' && command != '') {
    var formData = new FormData();
    formData.append('vname', Vname);
    formData.append('vnumber', Vnumber);
    formData.append('os', Os);
    formData.append('check', check);
    formData.append('uname', Uname);
    formData.append('pass', Pass);
    formData.append('command', command);
    formData.append('vid', versionid);

    if (clientOptionRadio) {
      if (1 == clientOptionRadio) {
        formData.append('Durl', Durl);
      } else if (2 == clientOptionRadio) {
        formData.append('client', inputFileData);
      }
    }
    formData.append('csrfMagicToken', csrfMagicToken);
    $.ajax({
      url: 'softwarefunctions.php?function=get_editversionsubmit',
      type: 'post',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function (data) {
        if (data.msg == 'success') {
          $.notify('Updated Successfully');
          closePopUp();
        } else if (data.msg == 'failed') {
          $.notify('Version name already exists');
          closePopUp();
        }

        rightContainerSlideClose('edit-version');
        showallversionDetails();
      },
    });
  }
}

$('#editversionname').keydown(function () {
  $('#editversionerror').hide();
});

$('#editversionnumber').keydown(function () {
  $('#editversionerror').hide();
});

function softwareupdateCancel() {
  location.reload();
}

$('#editversionpopup').on('hidden.bs.modal', function () {
  location.reload();
});

function deleteversionData() {
  var versionid = $('#vesiondeailid').val();
  $.ajax({
    url: 'softwarefunctions.php?function=delete_version' + '&csrfMagicToken=' + csrfMagicToken,
    type: 'post',
    data: 'id=' + versionid,
    dataType: 'json',
    success: function (data) {
      if (data.msg == 'success') {
        location.reload();
      }
    },
  });
}

function get_copyversiondata() {
  var versionid = $('#vesiondeailid').val();
  if (versionid == '') {
    $.notify('Please Select a record');
    $('#copy-version').hide();
    closePopUp();
  } else {
    $('#copy-version').show();
    $.ajax({
      url: 'softwarefunctions.php?function=get_copyversionData' + '&csrfMagicToken=' + csrfMagicToken,
      type: 'post',
      data: 'id=' + versionid,
      dataType: 'json',
      success: function (data) {
        if (data.global == '1') {
          $('#copy_global').attr('checked', 'checked');
        }
        $('#copy_versionname').val(data.name);
        $('#copy_versionnumber').val(data.version);
        $('#copy_url').val(data.url);
        $('#copy_user_name').val(data.username);
        $('#copy_pass_word').val(data.password);
        $('#copy_commandline').val(data.commandline);
        $('#copy_versionostype').val(data.os);
        $('.selectpicker').selectpicker('refresh');
      },
    });
  }
}

function copyversionDetail() {
  var Vname = $('#copy_versionname').val();
  var Vnumber = $('#copy_versionnumber').val();
  var Os = $('#copy_versionostype').val();
  var check = $('#copy_global').is(':checked') ? 1 : 0;
  var Durl = $('#copy_url').val();
  var Uname = $('#copy_user_name').val();
  var Pass = $('#copy_pass_word').val();
  var command = $('#copy_commandline').val();
  $.ajax({
    url: 'softwarefunctions.php?function=get_copydataInsert',
    type: 'post',
    data:
      'vname=' +
      Vname +
      '&vnumber=' +
      Vnumber +
      '&os=' +
      Os +
      '&check=' +
      check +
      '&uname=' +
      Uname +
      '&pass=' +
      Pass +
      '&command=' +
      command +
      '&Durl=' +
      Durl +
      '&csrfMagicToken=' +
      csrfMagicToken,
    dataType: 'json',
    success: function (data) {
      if (data.msg == 'error') {
        $.notify('Version name already exists');
        closePopUp();
      } else if (data.msg == 'success') {
        $.notify('Version copied successfully');
        rightContainerSlideClose('copy-version');
        closePopUp();

        showallversionDetails();
      }
    },
  });
}

$('#copy_versionname').keydown(function () {
  $('#copy_error').hide();
});

function getOsVersionList() {
  var site = $('#sitename').val();

  if (site == '') {
    closePopUp();
    $.notify('Cannot update version for site with no devices');
    return false;
  }
  rightContainerSlideOn('update-version');
  $('#windowselect').html('');
  $('#androidselect').html('');
  $('#linuxselect').html('');
  $('#macselect').html('');
  $('#iosselect').html('');

  $.ajax({
    url: 'softwarefunctions.php?function=get_osversionList',
    type: 'post',
    data: 'site=' + site + '&csrfMagicToken=' + csrfMagicToken,
    dataType: 'json',
    success: function (data) {
      $('#windowselect').append(data.win);
      $('#androidselect').append(data.and);
      $('#linuxselect').append(data.linux);
      $('#macselect').append(data.mac);
      $('#iosselect').append(data.ios);
      $('.selectpicker').selectpicker('refresh');
    },
  });
}

function Versionosremove(id) {
  $('#versiondeleteid').val(id);
  sweetAlert({
    title: 'Are you sure that you want to continue?',
    text: 'You will be able to update the version even after removing it.',
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#050d30',
    cancelButtonColor: '#fa0f4b',
    cancelButtonText: 'No, cancel it!',
    confirmButtonText: 'Yes, remove it!',
  })
    .then(function (result) {
      $.ajax({
        url: 'softwarefunctions.php?function=get_versionosremove',
        type: 'post',
        data: 'id=' + id + '&csrfMagicToken=' + csrfMagicToken,
        dataType: 'json',
        success: function (data) {
          if (data.msg == 'success') {
            $.notify('Version has been removed successfully');
            closePopUp();
            location.reload();
          } else {
            $.notify('Failed to remove the version. Please try again');
            closePopUp();
          }
        },
      });
    })
    .catch(function (reason) {
      $('.closebtn').trigger('click');
    });
  closePopUp();
}

function enableAddFields() {
  enableFields();
}
