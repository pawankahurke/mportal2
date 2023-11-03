$(document).ready(function () {
  changeSiteGroup();
  if (window.location.href.indexOf('device') > -1) {
    // getUsersList();
  } else {
    setTimeout(function () {
      // getSkuList();
      get_deviceDetails();
    }, 100);
  }
});

function changeSiteGroup() {
  var level = $('#searchType').val();

  if (level == 'Sites') {
    $('#name').html('Site Name');
    $('#detaild_grid').show();
    $('.sites').show();
    $('.groups').show();
    $('#errorMsg').hide();
    $('a.dropdown-item[data-bs-target=email-distribution],#exportAllSites').show();
  } else if (level == 'Groups') {
    $('#name').html('Group Name');
    $('#detaild_grid').show();
    $('.sites').hide();
    $('.groups').show();
    $('#errorMsg').hide();
    $('#exportAllSites').show();
    $('a.dropdown-item[data-bs-target=email-distribution],#exportAllSites').hide();
  } else if (level == 'ServiceTag') {
    $('.sites').show();
    $('.groups').show();
    $('#detaild_grid').hide();
    $('#errorMsg').show();
    $('a.dropdown-item[data-bs-target=email-distribution],#exportAllSites').hide();
  }
}

$('body').on('click', '.page-link', function () {
  var nextPage = $(this).data('pgno');
  notifName = $(this).data('name');
  get_deviceDetails(nextPage, '');
});
$('body').on('change', '#notifyDtl_lengthSel', function () {
  get_deviceDetails(1, '');
});
// $(document).on('keypress', function (e) {
//     if (e.which == 13) {
//         var notifSearch = $('#notifSearch').val();
//         if (notifSearch != ''){
//             get_deviceDetails(1,notifSearch);
//         }else{
//             get_deviceDetails(1,'');
//         }

//     }
// });

function get_deviceDetails(nextPage = 1, notifSearch = '', key = '', sort = '') {
  $('#loader').show();
  var notifSearch = $('#notifSearch').val();
  if (typeof notifSearch === 'undefined') {
    notifSearch = '';
  }
  var currentSearchtype = $('#searchType').val();
  var targetFunction = 'AJAX_GetMachineList';
  targetFunction = currentSearchtype != undefined && currentSearchtype == 'Groups' ? 'AJAX_GetGroupMachineList' : 'AJAX_GetMachineList';
  $.ajax({
    url: '../lib/l-ajax.php',
    type: 'POST',
    data: {
      function: targetFunction,
      csrfMagicToken: csrfMagicToken,
      limitCount: $('#notifyDtl_length :selected').val(),
      nextPage: nextPage,
      notifSearch: notifSearch,
      order: key,
      sort: sort,
    },
    dataType: 'json',
    success: function (gridData) {
      if (gridData.error) {
        $.notify(gridData.error);
        return false;
      }
      $('.loader').hide();
      $('.se-pre-con').hide();
      $('#detaild_grid').DataTable().destroy();
      $('#detaild_grid tbody').empty();
      deviceTable = $('#detaild_grid').DataTable({
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
        columnDefs: [
          { type: 'date', targets: [2, 3] },
          {
            render: w => {
              return `${w}`;
              // Hide links from census (AJAX_GetMachineList)
              // return `<a href='https://${window.location.host}/visualization/redirectByNameToId/${w}'>${w}<a/>`;
            },
            targets: [0],
          },
        ],
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
      if (currentSearchtype == 'Groups') {
        var rowID = deviceTable.row('#detaild_grid tbody tr.selected').data();
        if (rowID != 'undefined' && rowID !== undefined) {
          var groupid = $('#hidden_groupid').val();
          var groupname = $('#hidden_groupname').val();
          $('#selected').val(rowID[5]);
          $('#groupideditcsv').val(rowID[5]);
          $('#grupnamehidden').val(rowID[0]);
        }
      }
    },
    error: function (msg) {
      console.log('Grid Error : ' + msg);
    },
  });

  $('#detaild_grid tbody').on('click', 'tr', function () {
    deviceTable.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
    var rowID = deviceTable.row(this).data();
    if (rowID != 'undefined' && rowID !== undefined) {
      $('#selected').val(rowID[5]);
      $('#groupideditcsv').val(rowID[5]);
      $('#grupnamehidden').val(rowID[0]);
    }
  });

  /*$('#detaild_grid tbody').on('dblclick', 'tr', function () {
     var level = $('#searchType').val();
     var rowID = deviceTable.row(this).data();

     if (rowID != 'undefined' && rowID !== undefined) {
     $('#selected').val(rowID[6]);

     if (level == 'Groups') {
     checkGroupEditAccess();
     }
     }

     });*/
}

function getLicenseDetails() {
  //    $('#site-license-container').find('input[type="text"]').each({
  //        $(this).attr("readonly");
  //    });
  var site = $('#searchValue').val();
  var data = {
    function: 'get_LicenseDetails',
    sitename: site,
    csrfMagicToken: csrfMagicToken,
  };
  $.ajax({
    url: '../device/org_api.php',
    type: 'POST',
    data: data,
    success: function (data) {
      var res = JSON.parse(data);
      $('#licSitename').val(site).attr('readonly', true);
      $('#licSkuname').val(res['data']['skuname']).attr('readonly', true);
      var usedTotal = res['data']['numofinstall'] + ' / ' + res['data']['maxinstall'];
      $('#licUsedtotal').val(usedTotal).attr('readonly', true);
      var regcode = res['data']['regcode'];
      var siteemailid = res['data']['siteemailid'];
      var installPath = res['data']['licenseurl'];
      var downloadUrl = installPath + 'Provision/install/d.php?r=' + regcode + '&e=' + siteemailid;
      $('#downloadUrl').val(downloadUrl).attr('readonly', true);
    },
    error: function (error) {
      console.log('Error :: getLicenseDetails : ' + error);
    },
  });
}

function addSitePopup() {
  $('#site_name').val('');
  $('#act_key').val('');
  $('#required_Sitename').html('');
  $('#err_sitename').html();
  $('#err_sitekey').html();
  $('#deploy_sitename').val('');
  $('#deploy_sitekey').val('');

  $('#addDeploymentSiteBtn').css({ 'pointer-events': 'initial', cursor: 'pointer' });
  $('.download_url_div').hide();
  $('#download_url').val('');

  $('.download_url_div').hide();
  $('.create_site_div').show();
  $('#site-add-container em.error').html('');

  var payinfo = $('#payinfo').html();
  if (payinfo == '0') {
    $('.deploy_sitekey_div').show();
  }
}

function customer_link(url) {
  $('#msp_SiteLink').modal('show');
  if (url === 'Url is not available') {
    $('#site_successMsg').val(url);
    $('#site_download_url').hide();
    $('#copy_link1').hide();
  } else {
    $('#site_download_url').val(url);
    $('#site_download_url').show();
    $('#copy_link1').show();
    $('#site_successMsg').val('Please click on copy button to copy download url');
  }
}

function getCustomerDownloadURL() {
  //var businesstype = $("#getbusinessType").text();
  var cid = $('#getchannelid').text();

  //    $('.icon-circle').hide();
  //    $(".download_url_div").show();
  $('#url-pop-container').show();

  $('#selSite').html('');
  $('#site_download_url').val('');
  $('#copy_link1').hide();
  $('#status_emailsent').html('');

  getSitelist({ value: cid });
}

function getSitelist(ob) {
  commonAjaxCall(dashboardAPIURL + 'sites/by/customer_id/' + ob.value + '&method=GET', '', '').then(function (res) {
    var statusObj = JSON.parse(res);
    $('.loader').hide();

    if (statusObj.status == 'success') {
      // var data=statusObj.result;
      if (statusObj.result.length > 0) {
        var data = statusObj.result;

        $('#selSite').append("<option value=''>--select--</option>");
        for (var k in data) {
          var rObj = data[k];
          var sn = rObj.siteName;
          var snArr = sn.split('__');
          var compid = rObj.compid;
          var val = compid + '##' + rObj.siteName;
          $('#selSite').append("<option value='" + val + "'>" + snArr[0] + '</option>');
        }
      } else {
        $('#selSite').append("<option value=''>--Site not found--</option>");
      }
    } else {
      $('#selSite').append("<option value=''>--Site not found--</option>");
    }
    $('.selectpicker').selectpicker('refresh');
  });
}

function getdownloadUrl(thisobj) {
  $('#generateddownloadUrl').val('');
  $('#status_emailsent').html('');
  $('.loader').show();

  var companyid = $('#selCustomer2').val();
  if (thisobj.value != '') {
    $('#copy_link1').show();
    var value = thisobj.value;
    var val = value.split('##');
    var postdata = {
      comp_id: val[0],
      site_name: val[1], //thisobj.value
    };
    commonAjaxCall(dashboardAPIURL + 'download_id/by/site_name/comp_id' + '&method=POST', JSON.stringify(postdata), '').then(function (res) {
      var statusObj = JSON.parse(res);
      $('.loader').hide();
      if (statusObj.status == 'success') {
        var downloadId = statusObj.result;
        var pathArray = window.location.href.split('/');
        var downloadUrl = pathArray[0] + '//' + pathArray[2] + '/' + pathArray[3] + '/eula.php?id=' + downloadId;
        $('#site_download_url').val(downloadUrl);
      } else {
        $('#status_emailsent')
          .css('color', 'red')
          .html('Error : ' + JSON.stringify(statusObj.error.code) + ' - ' + JSON.stringify(statusObj.error.message));
      }
    });
  }
}

function copy_url(selectorId) {
  if (selectorId == '') {
    var urlField = document.querySelector('#site_download_url');
  } else {
    var urlField = document.querySelector('#' + selectorId);
  }
  urlField.select();
  document.execCommand('copy');
  $.notify('Download URL is copied');
}

function copyDownloadUrl() {
  var urlField = document.querySelector('#download_url');
  urlField.select();
  document.execCommand('copy');
}

function create_Site() {
  var sitename = $('#site_name').val();
  var actkey = $('#act_key').val();
  if (sitename == '' && actkey == '') {
    $('#required_Sitename').html('Please enter all the fields');
  } else if (!validate_alphanumeric_underscore(sitename)) {
    $('#required_Sitename').html('Enter only Alphanumeric values(a-z A-Z 0-9 _ -).');
  } else if (sitename != '' && actkey == '') {
    $('#required_Sitename').html('Please enter Ativation Key');
  } else if (sitename == '' && actkey != '') {
    $('#required_Sitename').html('Please enter Site Name');
  } else {
    $('#required_Sitename').html('');
    var m_data = new FormData();
    m_data.append('function', 'MSP_Create_Site');
    m_data.append('sitename', sitename);
    m_data.append('csrfMagicToken', csrfMagicToken);

    $.ajax({
      url: '../lib/l-msp.php',
      data: m_data,
      processData: false,
      contentType: false,
      type: 'POST',
      dataType: 'json',
      success: function (response) {
        if ($.trim(response.status) == 'success') {
          $('#site_successMsg').val(response.msg);
          $('#msp_CreateSite').modal('hide');
          $('#msp_SiteLink').modal('show');
          $('#site_download_url').val(response.link);
          $('copy_link1').show();
          get_CommercialSites();
        } else {
          $('#required_Sitename').html(response.msg);
        }
      },
      error: function (response) {
        $('#required_Sitename').html('Error Occurred');
        console.log('Error In create_Site function : ' + response);
      },
    });
  }
}

function addDeploymentSite() {
  var sitename = $('#deploy_sitename').val();
  var skudata = $('#deploy_skuid').val();
  var skuitem = skudata.split('###')[0];
  var startup = $('#deploy_startup').val();
  var followon = $('#deploy_followon').val();
  var delay = $('#deploy_delay').val();

  if (sitename == '' || skudata == '' || delay == '') {
    $.notify('Please enter the details in all the fields');
    return false;
  }

  var regExp = /^[a-zA-Z0-9_]+$/; // vizualizations donot support sitenames with space on it
  if (!sitename.match(regExp)) {
    $.notify('Enter only AlphaNumeric values for Site name. <br/>Character <b>underscore _</b> can be used.');
    return false;
  }

  var data = {
    function: 'create_OrgInsSite',
    sitename: sitename,
    skuid: skuitem,
    startup: startup,
    followon: followon,
    delay: delay,
    csrfMagicToken: csrfMagicToken,
  };

  $.ajax({
    url: '../device/org_api.php',
    type: 'POST',
    data: data,
    dataType: 'json',
    success: function (data) {
      if (data.status) {
        $.notify(data.msg);
        setTimeout(function () {
          location.reload();
        }, 3000);
      } else {
        $.notify(data.msg);
        setTimeout(function () {
          location.reload();
        }, 3000);
      }
    },
    error: function (errorThrown) {
      console.log(errorThrown);
    },
  });
}

function addDeploymntSiteOld() {
  $('.error').html('');
  $('.loader').show();
  $('#err_sitename').html('');
  $('#err_sitekey').html('');
  $('#required_Sitename').html('');

  var errorVal = 0;
  var field_value = $('#deploy_sitename').val();
  var sitekey_val = $('#deploy_sitekey').val();

  var type = $('#signupType').html();
  if (type === 'msp') {
    if (sitekey_val === '') {
      var deploy_sitekey_hidden = $('#deploy_sitekey_hidden').val();
      if (deploy_sitekey_hidden !== '') {
        $('#deploy_sitekey').val(deploy_sitekey_hidden);
      }
      sitekey_val = deploy_sitekey_hidden;
    }
  }

  if ($.trim(field_value) == '' && $.trim(sitekey_val) == '') {
    $('#err_sitename').css('color', 'red').html(' required');
    $('#err_sitekey').css('color', 'red').html(' required');
  } else if ($.trim(field_value) == '') {
    $('#err_sitename').css('color', 'red').html(' required');
    errorVal++;
  } else if ($.trim(sitekey_val) == '') {
    $('#err_sitekey').css('color', 'red').html(' required');
    errorVal++;
  } else if ($.trim(field_value) != '' && $.trim(sitekey_val) != '') {
    if (!validate_alphanumeric_underscore(field_value)) {
      $('#err_sitename').css('color', 'red').html('Enter only Alphanumeric,Underscore values ');
      errorVal++;
    } else {
      $('#required_sitename').html('*');
      attachSiteKey(field_value, sitekey_val);
    }
  }
}

function attachSiteKey(siteName, siteKey) {
  $.ajax({
    url: '../lib/l-ptsAjax.php',
    type: 'POST',
    data: {
      function: 'attachSiteKey',
      sitename: siteName,
      sitekey: siteKey,
      csrfMagicToken: csrfMagicToken,
    },
    success: function (res) {
      var data = JSON.parse(res);
      $('.loader').hide();
      //rightContainerSlideClose('site-add-container');
      $('#addDeploymentSiteBtn').css({ 'pointer-events': 'none' }).parent().css({ cursor: 'not-allowed' });
      if (data['status'] == 'success') {
        if (data['msg'] == 'DURL') {
          var durl = data['url'];
          var key = data['key'];
          var sid = data['sid'];

          var downloadID = data['downloadID'];

          $('.icon-circle').hide();
          $('.download_url_div').show();
          var pathArray = window.location.href.split('/');
          var downloadUrl = pathArray[0] + '//' + pathArray[2] + '/' + pathArray[3] + '/eula.php';
          if (downloadID === '') {
            $('#download_url')
              .val(downloadUrl + '?key=' + key + '&sid=' + sid)
              .css({ color: 'green' });
          } else {
            $('#download_url')
              .val(downloadUrl + '?id=' + downloadID)
              .css({ color: 'green' });
          }
          setTimeout(function () {
            $('.closebtn').click();
            location.reload();
          }, 2500);
        } else {
          $('#required_Sitename').html(data['msg']).css({ color: 'green' });
          setTimeout(function () {
            $('.closebtn').click();
            location.reload();
          }, 2500);
        }
        $('#msp_Sites_Grid').DataTable().ajax.reload();
      } else {
        $('#required_Sitename').html(data['msg']).css({ color: 'red' });
        $('#addDeploymentSiteBtn').css({ 'pointer-events': 'initial' }).parent().css({ cursor: 'pointer' });
      }
    },
    error: function (err) {
      console.log('Error : ' + err);
    },
  });
}

function getCustomerId(selectedId, index) {
  var custRowId = selectedId.split('---');
  var cust_id = custRowId[index];
  return cust_id;
}

$('#exportAllSites').click(function () {
  var selection = $('[name=searchType]').val();
  if (selection == undefined || selection == 'ServiceTag' || selection == '') {
    errorNotify('Please choose a site or group to export');
    return;
  }

  location.href = '../lib/l-ajax.php?function=AJAX_exportsite';
  closePopUp();
});

/* ====== GROUP DROP MENU FUNCTIONS ====== */

function deleteGroup() {
  var grpType = $('#groupType').val();
  var id = $('#hiddengrpid').val();
  if (grpType == 'Dynamic Group') {
    if (id == '') {
      $.notify('Please select a group to delete');
      closePopUp();
    } else {
      DeleteAdvGroup();
    }
  } else {
    var id = $('#hiddengrpid').val(); //$('#selected').val();
    var totalmachines = $('#totalmachinecount').val();
    if (totalmachines != 0) {
      if (id == undefined || id == 'undefined' || id == '') {
        $.notify('Please select a group to delete');
        closePopUp();
      } else {
        closePopUp();
        deletegrouplist(totalmachines);
      }
    } else {
      if (id == undefined || id == 'undefined' || id == '') {
        $.notify('Please select a group to delete');
        closePopUp();
      } else {
        closePopUp();
        deletegrouplist(totalmachines);
      }
    }
  }
}

function selectConfirm(data_target_id) {
  var editgid = $('#hiddengrpid').val();
  var editcount = $('#totalmachinecount').val();
  $('#succDelMsg').html('');
  $('#succDelMsg').html('');
  if (data_target_id == 'edit_group_drop') {
    if (editgid == '') {
      $.notify('Please choose a record');
      closePopUp();
    } else {
      //
      get_groupnameajax(editgid, editcount);
      rightContainerSlideOn('edit-group');
    }
  } else if (data_target_id == 'viewdetail_group_drop') {
    if (editgid == '') {
      $.notify('Please choose a record');
      // $('#warningemptygroup').modal('show');
      closePopUp();
    } else {
      $('#' + data_target_id).attr('data-bs-target', 'group-view-detail');
      groupviewDetail(editgid);
      viewdetailpopupclicked();
    }
  } else if (data_target_id == 'export_group_list') {
    var id = $('#hiddengrpid').val();
    if (id == '') {
      $.notify('Please choose a record');
      closePopUp();
      return false;
    } else {
      viewgroupdetailExport(id);
      closePopUp();
    }
  } else if (data_target_id == 'export_group_listmain') {
    ExportGroupDetail();
  } else if (data_target_id == 'view_detail_export') {
    viewgroupExport();
    closePopUp();
  } else if (data_target_id == 'group_info_list') {
    get_groupinfoDetails(editgid);
    rightContainerSlideOn('group-info-container');
  }
}

function get_groupinfoDetails(editgid) {
  var currentSearchtype = $('#searchType').val();
  var targetFunction = 'AJAX_GetGroupInfo';

  $.ajax({
    url: '../lib/l-ajax.php',
    type: 'POST',
    data: { function: targetFunction, csrfMagicToken: csrfMagicToken },
    dataType: 'json',
    success: function (gridData) {
      $('#grpcsvgname').val(gridData.groupname);
      $('#grpstyle').val(gridData.style);
      $('#grpcreatedby').val(gridData.username);
      $('#grpcreatedon').val(gridData.addedon);
      $('#grpmodifiedby').val(gridData.Modifiedby);
      $('#grpmodifiedon').val(gridData.ModifyTime);
    },
    error: function (error) {},
  });
}

/* ====== ADD GROUP (CSV UPLOAD) ====== */

function resetCsvGroupMessage() {
  setTimeout(function () {
    $('#successmsg').html('');
    $('#successmsg').show();
  }, 3000);
}

function csvgroupcreate() {
  var Mgroupcsv = $('#csvgname').val();
  var csvq = $('input[name=csv]')[0].files[0];

  var global = 1;

  var userList = $('#groupUsers').val();
  if (csvq == 'undefined' || csvq == undefined) {
  } else {
    var filecsv = csvq.name;
    var fileext = filecsv.substring(filecsv.lastIndexOf('.') + 1);
  }

  if (Mgroupcsv == '') {
    $.notify('Please enter the name of the group ');
    return false;
  } else if (!validate_alphanumeric_nounderscore(Mgroupcsv)) {
    $.notify('Special characters not allowed in the name of the group');
    return false;
  } else if (!$('#csvradio').is(':checked') && !$('#manualradio').is(':checked')) {
    $.notify('Please choose if you would like to create a CSV or a Manual Group');
    return false;
  } else if (csvq == 'undefined' || csvq == undefined) {
    $.notify('Please upload the .CSV file');
    return false;
  } else if (fileext != 'csv') {
    $.notify('Please upload a file with valid .CSV file extension');
    return false;
  } else {
    var m_data = new FormData();
    m_data.append('groupname', Mgroupcsv);
    m_data.append('csvname', csvq);
    $('#loadingCSVAdd').html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
    m_data.append('global', global);
    m_data.append('userlist', userList);
    m_data.append('function', 'get_add_groupcsv');
    m_data.append('csrfMagicToken', csrfMagicToken);

    $.ajax({
      url: '../admin/groupfunctions.php',
      data: m_data,
      processData: false,
      contentType: false,
      type: 'POST',
      dataType: 'json',
      success: function (response) {
        if (response.error == 'Invalid') {
          $.notify('No machines are added to the Group. Please try again ');
        } else if (response.status === 'Failed') {
          $.notify('There is already a group with the same name. Please enter another name');
        } else if (response.error == 'nodata') {
          $.notify('Unable to create a group since the CSV file that you uploaded is empty. Please try this again');
        } else if (response.error == 'no-minimum-machines') {
          $.notify('The CSV must have at least 2 machine names to create a group.');
        } else {
          $.notify('Group Created Successfully.<br/>' + response.msg);
        }

        setTimeout(function () {
          $('#addGroup').modal('hide');
          get_advncdgroupData();
          // location.href = 'device.php';
          refresh();
        }, 2000);
      },
      error: function (data) {},
    });
  }
}

/* ====== SELECTED EDIT GROUP NAME ======  */
function get_groupnameajax(editgid, count) {
  disableContainerFields($('#edit-group'));

  var postData = {
    function: 'get_edit_groupname',
    editgid: editgid,
    count: count,
    csrfMagicToken: csrfMagicToken,
  };

  $('#editmanualmachinebutton,#editcsvuploadbutton,.addButton').hide();
  $('#editmanualradio').removeAttr('data-after-form-check-input-disabled').parents('.form-check-label').removeClass('disabled');
  $('#editcsvradio').removeAttr('data-after-form-check-input-disabled').parents('.form-check-label').removeClass('disabled');
  $('#editcsvuploadbutton').show();

  $.ajax({
    url: '../admin/groupfunctions.php',
    data: postData,
    type: 'post',
    dataType: 'json',
    success: function (data) {
      $('#file_sel_edit').hide();
      $('#editfocused').addClass('is-focused');
      $('#editcsvgname').val(data.gname);
      var machinecount = data.option;
      $('#editmachinecount').html(machinecount.length);
      $('#editcsvradio,#editmanualradio').removeAttr('checked');
      $('#editmanualradio,#editcsvradio').removeAttr('disabled');
      if (data.global == '1') {
        $('#editglobalyes').prop('checked', true);
      } else {
        $('#editglobalno').prop('checked', true);
      }

      $('#editGroupUsers').html('');
      $('#editGroupUsers').html(data.userlist);
      $('.selecpicker').selectpicker('refresh');

      $('#editGroupStyle').html('');
      $('#editGroupStyle').html(data.stylelist);
      $('.selecpicker').selectpicker('refresh');

      if (data.type == 'CSV') {
        //$('#editmanualradio').addClass("not-allowed");
        //$('#editglobalno').addClass("not-allowed");
        $('#editmanualradio').prop('disabled', true);
        $('#editcsvradio').prop('checked', true);
        $('#editglobalyes').prop('disabled', true);
        $('#editglobalno').prop('disabled', true);
        $('#editcsvuploaddata').show();
        $('#editcsvuploadbutton').show();
        $('#editmanualmachinelist').hide();
        $('#editmanualmachinebutton').hide();
        $('#editmanualmachinelist').attr('disabled', true);

        $('#editcsvuploadbutton').show();
        $('#editmanualradio').attr('data-after-form-check-input-disabled', 'true');
      } else if (data.type == 'Manual') {
        $('#loader').show();
        $('#machine_list').val(data.machinelist);
        $.ajax({
          type: 'POST',
          url: '../admin/groupfunctions.php',
          dataType: 'json',
          data: { function: 'get_MachineListEdit', mid: data.machinelist, csrfMagicToken: csrfMagicToken },
        }).done(function (data) {
          $('.loader').hide();
          if ($.trim(data.state) === 'success') {
            $('#include_machine').html(data.option);
          }
        });
        //$('#editcsvradio').addClass("not-allowed");
        //$('#editglobalno').addClass("not-allowed");
        $('#editcsvradio').prop('disabled', true);
        $('#editmanualradio').prop('checked', true);
        $('#editglobalyes').prop('disabled', true);
        $('#editglobalno').prop('disabled', true);
        $('#editmanualmachinelist').attr('disabled', true);
        $('#editmanualmachinelist').show();
        $('#editmanualmachinebutton').hide();
        $('#editcsvuploaddata').hide();
        $('#editcsvuploadbutton').hide();

        $('#editmanualmachinebutton').show();
        $('#editcsvradio').attr('data-after-form-check-input-disabled', 'true');
      }
    },
  });
}

/* ===== EDIT CSV UPLOAD FUNCTION ===== */
function csvgroupedit() {
  if (!$('#editcsvradio').is(':checked') && !$('#editmanualradio').is(':checked')) {
    $.notify('<span style="color:red;">Please choose if you would like to create a CSV or a Manual Group</span>');
    $('#successmsgedit').show();
    return false;
  }

  var grpid = $('#selected').val();
  var grpedit = $('#editcsvgname').val();
  var csvqedit = $('input[name=csvedit]')[0].files[0];
  var globaledit = 1;

  if (grpedit == '') {
    $.notify('Please enter the name of the group ');
    return false;
  }

  if (!validate_alphanumeric_nounderscore(grpedit)) {
    $.notify('Special characters not allowed in the name of the group');
    $('#successmsgedit').show();
    return false;
  }

  if (csvqedit != undefined) {
    var filecsv = csvqedit.name;
    var fileext = filecsv.substring(filecsv.lastIndexOf('.') + 1);

    if (fileext != 'csv') {
      $.notify('Please upload the .CSV file');
      $('#successmsgedit').show();
      return false;
    }
  }

  var userList = $('#editGroupUsers').val();
  var styleList = $('#editGroupStyle').val();

  var m_data = new FormData();
  m_data.append('groupname', grpedit);
  m_data.append('grpid', grpid);
  m_data.append('global', globaledit);
  m_data.append('userlist', userList);
  m_data.append('stylelist', styleList);
  if (csvqedit != undefined) {
    m_data.append('csvname', csvqedit);
  }
  m_data.append('function', 'get_edit_groupcsv');
  m_data.append('csrfMagicToken', csrfMagicToken);

  $('#loadingCSVEdit').html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');

  $.ajax({
    url: '../admin/groupfunctions.php',
    type: 'get',
    data: { function: 'check_GroupEditAccess', groupid: grpid, csrfMagicToken: csrfMagicToken },
    dataType: 'json',
    success: function (data) {
      if (data.msg === 'success') {
        $.ajax({
          type: 'post',
          url: '../admin/groupfunctions.php',
          data: m_data,
          processData: false,
          contentType: false,
          dataType: 'json',
          success: function (response) {
            if (response.status == 'success') {
              $('#loadingCSVEdit').hide();
              $('#successmsgedit').show();
              $.notify('Group updated successfully.<br/>' + response.msg);
              $('#successmsgedit').fadeOut(3000);
              setTimeout(function () {
                $('#editGroup').modal('hide');
                get_advncdgroupData();
                // location.href = 'device.php';
              }, 3200);
            } else if (response.status == 'duplicate') {
              $('#loadingCSVEdit').hide();
              $.notify('There is already a group with the same name. Please enter another name');
              $('#successmsgedit').show();
            }

            if (response.error == 'Invalid') {
              $('#loadingCSVEdit').hide();
              $('#successmsgedit').show();
              $.notify('No machines are added to the Group. Please try again ');
              //                    $('#successmsgedit').fadeOut(3000);
              setTimeout(function () {
                $('#editGroup').modal('hide');
                get_advncdgroupData();
                // location.href = 'device.php';
              }, 3200);
            }

            if (response.status == 'Failed') {
              $('#loadingCSVEdit').hide();
              $('#successmsgedit').show();
              $.notify('There is already a group with the same name. Please enter another name');
              //                    $('#successmsgedit').fadeOut(3000);
              setTimeout(function () {
                $('#editGroup').modal('hide');
                get_advncdgroupData();
                // location.href = 'device.php';
              }, 3200);
            }

            if (response.error == 'nodata') {
              $('#loadingCSVEdit').hide();
              $('#successmsgedit').show();
              $.notify('Unable to update the group since the CSV file that you uploaded is empty. Please try this again');
              setTimeout(function () {
                $('#editGroup').modal('hide');
                get_advncdgroupData();
                // location.href = 'device.php';
              }, 3200);
            }

            if (response.error == 'no-minimum-machines') {
              $('#loadingCSVEdit').hide();
              $('#successmsgedit').show();
              $.notify('The CSV must have at least 2 machine names to update the group.');
              return false;
            }
          },
        });
      } else {
        $('#loadingCSVEdit').hide();
        $('#successmsgedit').show();
        $.notify("You don't have the permission to edit this group");
        setTimeout(function () {
          $('#editGroup').modal('hide');
          get_advncdgroupData();
          // location.href = 'device.php';
        }, 2000);
      }
    },
  });
}

/* ======= SAMPLE FILE EXPORT FOR ADD GROUP ======= */
function samplefileExport() {
  //    var functioncall    = "function=get_editgroupname&editgid=" + editgid;
  //    var encryptedData   = get_RSA_EnrytptedData(functioncall);

  window.location.href = '../admin/groupfunctions.php?function=get_samplefileDownload';
}

/* ======= SAMPLE FILE EXPORT FOR EDIT GROUP ======= */
function samplefileEditExport() {
  window.location.href = '../admin/groupfunctions.php?function=get_samplefileDownload';
}

/* ======= VIEW GROUP DETAIL EXPORT ======== */
function viewgroupdetailExport(grpid) {
  var grpname = $('#grupnamehidden').val();
  window.location.href = '../admin/groupfunctions.php?function=get_viewgroupdetailexportList&grupid=' + grpid + '&grpname=' + grpname;
}

function ExportGroupDetail() {
  window.location.href = '../admin/groupfunctions.php?function=get_view_exportGrpList';
  closePopUp();
}

/* ======= VIEW GROUP FULL EXPORT ======== */

function viewgroupExport() {
  window.location.href = '../admin/groupfunctions.php?function=get_view_groupexportList';
}

/* ======== DELETE GROUP CODE ======== */
function deletegrouplist(totalmachines) {
  closePopUp();
  sweetAlert({
    title: 'Are you sure that you want to continue?',
    text: 'You wont be able to recover the group once deleted',
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#050d30',
    cancelButtonColor: '#fa0f4b',
    cancelButtonText: 'No, cancel it!',
    confirmButtonText: 'Yes, delete it!',
  })
    .then(function (result) {
      var str;
      $('#DelMsg').html('');
      $('#DelMsg').show();

      //    var functioncall    = "function=get_groupListDelete&value=" + str;
      //    var encryptedData   = get_RSA_EnrytptedData(functioncall);
      if (totalmachines != 0) {
        str = $('#grupnamehidden').val();
      } else {
        str = $('#grupnamehidden').val();
      }
      $.ajax({
        url: '../admin/groupfunctions.php',
        type: 'POST',
        data: { function: 'check_EditAccess', groupid: str, csrfMagicToken: csrfMagicToken },
        // data: {'function':'checkGrpAccess', 'csrfMagicToken': csrfMagicToken},
        dataType: 'json',
        success: function (data) {
          if (data.msg === 'success') {
            $.ajax({
              type: 'GET',
              url: '../admin/groupfunctions.php?function=get_groupListDelete&value=' + str + '&csrfMagicToken=' + csrfMagicToken,
              //                        data: {'function': 'get_groupListDelete', 'value': str},
              // dataType: 'json',
              success: function (data) {
                $.notify('Group deleted Successfully');
                // get_deviceDetails();
                get_advncdgroupData();
                $('.site').html('No selection');
                closePopUp();
                // location.reload();
              },
            });
          } else {
            $.notify("You don't have the permission to delete the group");
            get_advncdgroupData();
            // get_deviceDetails();
          }
        },
      });
    })
    .catch(function (reason) {
      $('.closebtn').trigger('click');
    });
}

/* ======= GROUP VIEW DETAIL GRID COLUMN ONLOAD CLICK FUNCTION ======== */
function viewdetailpopupclicked() {
  setTimeout(function () {
    $('.event-info-grid-host').click();
  }, 300);
}

/* ====== ADD AND EDIT GROUP VALIDATION HIDE AND SHOW CODE ===== */
$('#csvgname').keyup(function () {
  $('#successmsg').hide();
  $('#successmsgmanual').hide();
});

$('#editcsvgname').keyup(function () {
  $('#successmsgedit').hide();
  $('#manualsuccessmsgedit').hide();
});

$('#csvS').mousedown(function () {
  $('#successmsg').hide();
});

$('#csvSedit').mousedown(function () {
  $('#successmsgedit').hide();
});

/* ======== CODE FOR EDIT POP-UP(BROWSE FOR FILE) ========  */
jQuery(document).ready(function ($) {
  jQuery('#fileuploader1').change(function (ev) {
    if ($('#fileuploader1').val().split('\\').pop() != '') {
      $('.samplefileEdit').hide();
      $('.browse-fileEdit').hide();
      $('.samplefileEdit2').html($('#fileuploader1').val().split('\\').pop());
      $('.remove-fileEdit').show();
    }
  });
  jQuery('.remove-fileEdit').click(function (event) {
    $('.samplefileEdit').show();
    $('.browse-fileEdit').show();
    $('.samplefileEdit2').html('');
    $('#fileuploader1').val('');
    $('.remove-fileEdit').hide();
  });
});

$('.remove-file').on('click', function () {
  $('#add_file').hide();
});

var filterId = [];
function addDevices() {
  filterId = [];
  $('input:checkbox[name=type]:checked').each(function () {
    filterId.push($(this).val());
  });
  rightContainerSlideClose_Device('rsc-add-container5');
  rightContainerSlideOn('grp-add-container');
  rightContainerSlideOn('grp-addmod-container');

  $('#groupslider').hide();
  $('#machine_count').show();
  $('#machinecount').html(filterId.length);
  $('#groupslider1').hide();
  $('#machine_count1').show();
  $('#machinecount1').html(filterId.length);
}

function editDevices() {
  var machineListIds;
  action();
  filterId = [];
  $.each($('#rsc-add-container5').find('input:checkbox[name=type]:checked'), function () {
    filterId.push($(this).val());
  });
  rightContainerSlideClose_Device('rsc-add-container5');
  rightContainerSlideOn('edit-group');
  machineListIds = filterId.length > 0 ? filterId.join(',') : '';
  $('#machine_list').val(machineListIds);
  $('#editmachinecount').html(filterId.length);
  $('#machine_list1').val(machineListIds);
  $('#editmachinecount1').html(filterId.length);
}

//temp function to get sku list, API call will be added later, By Shamant_G
function getSkuList() {
  $.ajax({
    type: 'POST',
    url: '../device/org_api.php',
    data: { function: 'get_SkuList', csrfMagicToken: csrfMagicToken },
    success: function (response) {
      var result = JSON.parse(response);
      $('#deploy_skuid').html(result.data);
      $('.selectpicker').selectpicker('refresh');
    },
  });
}

var gUList = false;
function getUsersList() {
  if (gUList) {
    return;
  }
  gUList = true;
  $.ajax({
    url: '../admin/groupfunctions.php',
    type: 'POST',
    data: { function: 'get_UsersList', csrfMagicToken: csrfMagicToken },
    success: function (data) {
      $('#groupUsers').html('');
      $('#groupUsers').html(data);
      $('.selectpicker').selectpicker('refresh');
      $('#groupUsers2').html('');
      $('#groupUsers2').html(data);
      $('.selectpicker').selectpicker('refresh');
    },
    error: function (errorThrown) {
      console.log(errorThrown);
    },
  });
}

$('#deploy_skuid').on('change', function () {
  var skudata = $(this).val();
  var trialDays = skudata.split('###')[1];
  $('#deploy_delay').val(trialDays);
});

function manualgroupcreate() {
  var gname = $('#csvgname').val();

  var global = 1;
  var userList = $('#groupUsers').val();

  var machCnt = filterId.length;
  var machinelist = filterId.toString();

  if (gname == '') {
    $.notify('Please enter the name of the group ');
    return false;
  } else if (!validate_alphanumeric_nounderscore(gname)) {
    $.notify('Special characters are not allowed');
    return false;
  } else if (machinelist == '') {
    $.notify('Please select some machines');
    return false;
  } else if (machCnt < 2) {
    $.notify('You must to selected atleast 2 devices to create a group');
    return false;
  } else {
    var m_data = new FormData();
    m_data.append('groupname', gname);
    m_data.append('machinelist', machinelist);
    m_data.append('global', global);
    m_data.append('userlist', userList);

    m_data.append('function', 'get_ManualGroup_Add');
    m_data.append('csrfMagicToken', csrfMagicToken);

    $('#loadingMaualAdd').html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
    $.ajax({
      url: '../admin/groupfunctions.php',
      type: 'POST',
      processData: false,
      contentType: false,
      data: m_data,
      dataType: 'json',
      success: function (data) {
        if (data.status == 'Failed') {
          $('#loadingMaualAdd').hide();
          $.notify('There is already a group with the same name. Please enter another name');
          return false;
        } else if (data.status == 'success') {
          $('#loadingMaualAdd').hide();
          $.notify('Group created successfully');
          //return false;
        }

        setTimeout(function () {
          rightContainerSlideClose('grp-add-container');
          // get_deviceDetails();
          get_advncdgroupData();
          // get_viewadvncdgroups();
          // location.href = 'device.php';
        }, 3200);
      },
    });
  }
}

function resetManualAddGroupMessage() {
  setTimeout(function () {
    $('#successmsgmanual').html('');
    $('#successmsgmanual').show();
  }, 3000);
}

function checkGroupEditAccess() {
  usrstat = '1';
  selectConfirm('edit_group_drop');
  return;
  var grpid = $('#hiddengrpid').val();

  $.ajax({
    url: '../admin/groupfunctions.php',
    type: 'POST',
    data: { function: 'check_GroupEditAccess', groupid: grpid, csrfMagicToken: csrfMagicToken },
    //    dataType: 'json',
    success: function (ret) {
      var data = JSON.parse(ret);
      if (data.msg === 'failed') {
        $('#absoFeed').hide();
        $.notify("You don't have the permission to edit this group!");
        return false;
      } else if (data.msg === 'success' && !data.usrstat) {
        $('#absoFeed').hide();
        $.notify("You don't have the permission to edit this group!");
        return false;
      } else if (data.msg == 'advfailed') {
        $('#absoFeed').hide();
        $.notify('Advance Group can only be modified from the Advance Group section');
        return false;
      } else {
        $('#toggleButton').hide();
        $('#editOption').show();
        $('#csvradio').prop('checked', true);
        $('#editcsvuploaddata').show();
        $('#editcsvuploadbutton').show();
        $('#editmanualmachinelist').hide();
        $('#editmanualmachinebutton').hide();
        $('#successmsgedit').html('');
        $('#successmsgedit').hide();
        selectConfirm('edit_group_drop');
        usrstat = data.usrstat;
      }
    },
    error: function (err) {
      console.log('Error : ' + JSON.stringify(err));
    },
  });
}

$('.closebtn').on('click', function () {
  $('#toggleButton').hide();
  $('#editOption').show();
  $('#editOption2').show();
});

function manualgroupedit_del() {
  var grpid = $('#selected').val();
  var grpedit = $('#editcsvgname').val();
  var gname = $('#editcsvgname').val();
  var global = 1;

  var machinelist = $('#machine_list').val();

  var machinecount = parseInt($('#editmachinecount').html());

  var editUserList = $('#editGroupUsers').val();

  if (gname == '') {
    $.notify('Please enter the name of the group ');
    return false;
  } else if (!validate_alphanumeric_nounderscore(gname)) {
    $.notify('Special characters are not allowed');
    return false;
  } else if (machinelist == '' && machinecount == 0) {
    $.notify('Please select some machines');
    return false;
  } else if (machinecount < 2) {
    $.notify('You must to selected atleast 2 devices to create a group');
    return false;
  } else {
    var postData = {
      function: 'get_ManualGroup_Edit',
      groupname: gname,
      machinelist: machinelist,
      grpid: grpid,
      grpedit: grpedit,
      global: global,
      edituserlist: editUserList,
      csrfMagicToken: csrfMagicToken,
    };

    $.ajax({
      url: '../admin/groupfunctions.php',
      type: 'GET',
      data: { function: 'check_GroupEditAccess', groupid: grpid, csrfMagicToken: csrfMagicToken },
      dataType: 'json',
      success: function (data) {
        if (data.msg === 'success') {
          $('#loadingMaualEdit').html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..."/>');
          $.ajax({
            url: '../admin/groupfunctions.php',
            type: 'POST',
            data: postData,
            dataType: 'json',
            success: function (data) {
              if (data.msg == 'success') {
                $('#loadingMaualEdit').hide();
                $('.site').html(data.newgrpname);
                $.notify('Group Updated Successfully');
                rightContainerSlideClose('edit-group');
                location.reload();
              } else if (data.msg == 'duplicate') {
                $.notify('There is already a group with the same name. Please enter another name');
                return false;
              } else if (data.msg == 'invalid-user') {
                $.notify("You don't have the permission to edit this group");
                return false;
              }
            },
          });
        } else {
          $.notify("You don't have the permission to edit this group");
          setTimeout(function () {
            $('#edit-group').modal('hide');
            get_advncdgroupData();
            // location.href = 'device.php';
          }, 2000);
        }
      },
    });
  }
}

$('#csvradio').change(function () {
  $('#csvuploaddata').show();
  $('#csvuploadbutton').show();
  $('#manualmachinebutton').hide();
  $('#manualmachinelist').hide();
  $('#successmsg').html('');
  $('#successmsg').hide();
});

$('#manualradio').change(function () {
  $('#add-manual').show();
  $('#csvuploaddata').hide();
  $('#csvuploadbutton').hide();
  $('#manualmachinebutton').show();
  $('#manualmachinelist').show();
  $('#include_machine').html('');
  $('#groupslider').show();
  $('#machine_count').hide();
  $('#loader').show();
  $.ajax({
    type: 'GET',
    url: '../admin/groupfunctions.php',
    data: { function: 'get_MachineList', csrfMagicToken: csrfMagicToken },
    dataType: 'json',
  }).done(function (data) {
    $('.loader').hide();
    if ($.trim(data.state) === 'success') {
      $('.loader').hide();
      if (data.option == '') {
        $('#include_machine').html('<span style="margin:20px">No Machine available</span>');
      } else {
        $('#include_machine').html(data.option);
      }
    }
  });

  //    $('#successmsg').html('');
  //    $('#successmsg').hide();
});

//check box selection logic
$('input[type="checkbox"]').change(function (e) {
  var checked = $(this).prop('checked'),
    container = $(this).parent(),
    siblings = container.siblings();

  container.find('input[type="checkbox"]').prop({
    indeterminate: false,
    checked: checked,
  });

  function checkSiblings(el) {
    var parent = el.parent().parent(),
      all = true;

    el.siblings().each(function () {
      return (all = $(this).children('input[type="checkbox"]').prop('checked') === checked);
    });

    if (all && checked) {
      parent.children('input[type="checkbox"]').prop({
        indeterminate: false,
        checked: checked,
      });

      checkSiblings(parent);
    } else if (all && !checked) {
      parent.children('input[type="checkbox"]').prop('checked', checked);
      parent.children('input[type="checkbox"]').prop('indeterminate', parent.find('input[type="checkbox"]:checked').length > 0);
      checkSiblings(parent);
    } else {
      el.parents('li').children('input[type="checkbox"]').prop({
        indeterminate: true,
        checked: false,
      });
    }
  }

  checkSiblings(container);
});

$('#editcsvradio').change(function () {
  $('#editcsvuploaddata').show();
  $('#editcsvuploadbutton').show();
  $('#editmanualmachinelist').hide();
  $('#editmanualmachinebutton').hide();
  $('#successmsgedit').html('');
  $('#successmsgedit').hide();
});

$('#editmanualradio').change(function () {
  $('#add-manual').show();
  $('#editcsvuploaddata').hide();
  $('#editcsvuploadbutton').hide();
  $('#editmanualmachinelist').show();
  $('#editmanualmachinebutton').show();
  $('#successmsgedit').html('');
  $('#successmsgedit').hide();
  $.ajax({
    type: 'GET',
    url: '../admin/groupfunctions.php',
    data: { function: 'get_MachineList', csrfMagicToken: csrfMagicToken },
    dataType: 'json',
  }).done(function (data) {
    $('.loader').hide();
    if ($.trim(data.state) === 'success') {
      $('.loader').hide();
      if (data.option == '') {
        $('#include_machine').html('<span style="margin:20px">No Machine available</span>');
      } else {
        $('#include_machine').html(data.option);
      }
    }
  });
});

function refresh() {
  location.reload();
}

function validate_AlphaNumeric(name) {
  var filter = /^[a-z\d\_\ \s]+$/i;
  if (filter.test(name)) {
    return true;
  } else {
    return false;
  }
}

$('.rightslide-container-close').click(function () {
  $('#csvuploaddata').hide();
  $('#manualmachinelist').hide();
  $('#csvradio').prop('checked', false);
  $('#manualradio').prop('checked', false);
  $('#globalyes').prop('checked', false);
  $('#globalno').prop('checked', false);
  $('#csvgname').val('');
  $('select#exclude_machine').find('option').remove();
});

//expunge machine
function removeMachine(macid, name) {
  machineId = macid;
  machineName = name;
}

function removeMachin(macid, name) {
  closePopUp();
  var isAsset = 0;
  var isEvent = 0;
  var isPatch = 0;
  sweetAlert({
    title: 'Are you sure that you want to continue?',
    //text: "If you expunge the machine, you will not be able to undo the action.",
    html: 'If you expunge the machine, you will not be able to undo the action.Please choose the data that you wish to expunge along with the machine<br/>\n\
               <div><div class="form-check" style="font-size: 13px; float:left; margin-left: 30px;">\n\
                    <label class="form-check-label">Expunge Asset\n\
                        <input class="form-check-input" name="exp-asset" id="exp-asset" checked="" type="checkbox">\n\
                        <span class="form-check-sign"></span>\n\
                    </label>\n\
                </div>\n\
                <div class="form-check" style="font-size: 13px; float:left; margin-left: 15px;">\n\
                    <label class="form-check-label">Expunge Event\n\
                        <input class="form-check-input" name="exp-event" id="exp-event" checked="" type="checkbox">\n\
                        <span class="form-check-sign"></span>\n\
                    </label>\n\
                </div>\n\
                <div class="form-check" style="font-size: 13px; float:left; margin-left: 15px;">\n\
                    <label class="form-check-label">Expunge Patch\n\
                        <input class="form-check-input" name="exp-patch" id="exp-patch" checked="" type="checkbox">\n\
                        <span class="form-check-sign"></span>\n\
                    </label>\n\
                </div></div>',
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#050d30',
    cancelButtonColor: '#fa0f4b',
    cancelButtonText: 'No, cancel it!',
    confirmButtonText: 'Yes, expunge it!',
  })
    .then(function (result) {
      if ($('#exp-asset').is(':checked')) {
        isAsset = 1;
      }
      if ($('#exp-event').is(':checked')) {
        isEvent = 1;
      }
      if ($('#exp-patch').is(':checked')) {
        isPatch = 1;
      }
      $('#loader_' + macid).show();
      $.ajax({
        type: 'POST',
        dataType: 'json',
        url: '../lib/l-ajax.php',
        // url: '../lib/l-ajax.php?function=AJAX_expunge&mId=' + macid + '&asset=' + isAsset + '&event=' + isEvent + '&patch=' + isPatch + "&csrfMagicToken=" + csrfMagicToken,
        data: { function: 'AJAX_expunge', mId: macid, asset: isAsset, event: isEvent, patch: isPatch, csrfMagicToken: csrfMagicToken },
        success: function (result) {
          if (result.status === 'success') {
            $('#loader_' + macid).hide();
            get_deviceDetails();
            $.notify('Device has been expunged successfully');

          } else {
            $.notify('Failed to expunge the device. Please try again');
          }
        },
      });
    })
    .catch(function (reason) {
      $('.closebtn').trigger('click');
    });
}

$('#csv_file').on('change', function () {
  var file_data = $('#csv_file').prop('files')[0];
  var logo_data = new FormData();
  var csv_name = $('#csv_file').prop('files')[0]['name'];

  logo_data.append('file', file_data);
  logo_data.append('type', 'headerlogo');

  $('#csv_name').html(csv_name).css({ color: 'black' });
  $('.logo_loader').show();
  $('#remove_logo').show();
});

$('#remove_logo').click(function () {
  $('#csv_file').val('');
  $('#csv_name').html('');
  $('#remove_logo').hide();
  $('.logo').attr('src', '../assets/img/bask-logo.png');
});

$('#csv_file_edit').on('change', function () {
  var file_data = $('#csv_file_edit').prop('files')[0];
  var logo_data = new FormData();
  var csv_name = $('#csv_file_edit').prop('files')[0]['name'];

  logo_data.append('file', file_data);
  logo_data.append('type', 'headerlogo');

  $('#csv_name_edit').html(csv_name).css({ color: 'black' });
  $('.logo_loader').show();
  $('#remove_logo_edit').show();
  action();
});

$('#remove_logo_edit').click(function () {
  $('#csv_file_edit').val('');
  $('#csv_name_edit').html('');
  $('#remove_logo_edit').hide();
  $('.logo').attr('src', '../assets/img/bask-logo.png');
});

function gobackToAdd() {
  rightContainerSlideClose_Device('rsc-add-container5');
  rightContainerSlideOn('grp-addmod-container');
}

function gobackToEdit() {
  rightContainerSlideClose_Device('rsc-add-container5');
  rightContainerSlideOn('edit-group');
}

/*
 * ORG integration modules
 */

function manageEmailDistribution() {
  $('#emailAddresses').val('');
  $('.emailstat').html('');
}

function emailDistribution() {
  var selection = $('[name=searchType]').val();
  if (selection == undefined || selection == 'ServiceTag' || selection == 'Groups' || selection == '') {
    errorNotify('Please choose a site');
    return;
  }

  // Need to save email in Site email if not exists and send mail
  var emailList = $('#emailAddresses').val();
  var sitename = $.trim($('#searchValue').val());
  if (emailList == '') {
    $.notify('Please enter a valid email address');
    return false;
  }
  if (sitename == '') {
    $.notify('Please select a site');
    rightContainerSlideClose('email-distribution');
    return false;
  }

  if (!validateEmails(emailList)) {
    $.notify('Please enter a valid email address');
  } else {
    $('#emailDistributeLoader').show();
    var Elementresult = emailList.split('\n');
    var NewEmailList = [];
    for (var i = 0; i < Elementresult.length; i++) {
      if (Elementresult[i] != '') {
        NewEmailList.push(Elementresult[i]);
      }
    }
    var postData = {
      function: 'update_SiteEmailData',
      email_list: NewEmailList,
      sitename: sitename,
      csrfMagicToken: csrfMagicToken,
    };

    $.ajax({
      url: '../device/org_api.php',
      type: 'POST',
      data: postData,
      success: function (data) {
        $('#emailDistributeLoader').hide();
        var res = JSON.parse(data);
        if (res.status) {
          sendDownloadLinkMail(res.data, emailList);
        } else {
          $.notify(res.msg);
        }
      },
      error: function (err) {
        $('#emailDistributeLoader').hide();
      },
    });
  }
}

function validateEmails(string) {
  var regex = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
  var result = string.split('\n');
  for (var i = 0; i < result.length; i++) {
    if (!regex.test(result[i])) {
      return false;
    }
  }
  return true;
}

function sendDownloadLinkMail(siteid, emailList) {
  var postData = {
    function: 'send_DownloadLinkMail',
    siteid: siteid,
    emailList: emailList,
    csrfMagicToken: csrfMagicToken,
  };

  $.ajax({
    url: '../device/org_api.php',
    type: 'POST',
    data: postData,
    success: function (data) {
      var res = JSON.parse(data);
      if (res.status) {
        $.notify('Email sent ');
        rightContainerSlideClose('email-distribution');
      } else {
        $.notify('Failed to send the email. Please try again');
        rightContainerSlideClose('email-distribution');
      }
    },
    error: function (err) {
      console.log(err);
    },
  });
}

function clearAddSiteField() {
  $('#deploy_sitename').val();
}
