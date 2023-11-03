var level = '';
$(document).ready(function () {
  user_datatable('all');
  $('.bottom').hide();
  // $('#user_datatable').hide();
  // $('#user_datatable_wrapper').hide();
  $('#user_datatable_info').hide();
  $('#user_datatable_length').hide();
  $('#user_datatable_filter').hide();
  $('#user_datatable_paginate').hide();
  // $(".dropHandy").css('cursor', 'pointer');
  // $(".hideElement").hide();
  // var user_table = '';
  // var h = window.innerHeight;

  // getUserRoles();
  // getAdminRole();
  // getTime_Zones();
  //Call_alert("message", "fail test", "error");
  //No of rows for Datatable need to increase if page is opening on high resolution screen
  // if (h > 700) {
  //     $("#user_datatable").attr("data-page-length", "50");
  // } else {
  //     $("#user_datatable").attr("data-page-length", "25");
  // }
});

function getTime_Zones() {
  $.ajax({
    type: 'POST',
    url: '../lib/l-custAjax.php',
    data: { function: 'CUST_TimeZones', csrfMagicToken: csrfMagicToken },
    success: function (response) {
      $('#add_timeZone').html(response);
      $('.selectpicker').selectpicker('refresh');
      $('#edit_timeZone').html(response);
      $('.selectpicker').selectpicker('refresh');
    },
  });
}

function getUserRoles() {
  $.ajax({
    type: 'POST',
    url: '../lib/l-custAjax.php',
    data: { function: 'CUSAJX_GetAllUser_Roles', csrfMagicToken: csrfMagicToken },
    success: function (response) {
      $('#add_advroleId').html(response);
      $('.selectpicker').selectpicker('refresh');
      $('#edit_advroleId').html(response);
      $('.selectpicker').selectpicker('refresh');
    },
  });
}

function getAdminRole() {
  $.ajax({
    type: 'POST',
    url: '../lib/l-custAjax.php',
    data: { function: 'CUSAJX_GetAdmin_Role', csrfMagicToken: csrfMagicToken },
    success: function (response) {
      response = $.trim(response);
      $('#AdminRoleId').val(response);
    },
  });
}

$('body').on('click', '.page-link', function () {
  var nextPage = $(this).data('pgno');
  notifName = $(this).data('name');
  const activeElement = window.currentActiveSortElement;
  const key = (activeElement) ? activeElement.sort : '';
  const sort = (activeElement) ? activeElement.type : '';
  user_datatable('', nextPage, '', key, sort);
});
$('body').on('change', '#notifyDtl_lengthSel', function () {
  user_datatable('', 1, '');
});

function user_datatable(content , nextPage = 1, notifSearch = '', key = '', sort = '') {
  $('#loader').show();
  var searchType = $('#searchType').val();
  var searchValue = $('#rparentName').val();
  notifSearch = $('#notifSearch').val();
  if (typeof notifSearch === 'undefined') {
    notifSearch = '';
  }

  checkAndUpdateActiveSortElement(key, sort);

  //$("#userSearchValue").htsearchLabelml($("#searchLabel").val());
  var text = '';
  var searchType = 'Sites'; // need to change later
  // if (searchType !== 'Sites') {
  //     text = 'Users are available only at Site level';
  //     $("#usermenu_options").hide();
  // } else {
  //     text = 'No Data Available';
  //     $("#usermenu_options").show();
  // }
  level = content;
  if (content === 'all') {
    $('#userdetails').hide();
    $('#back').show();
  } else {
    $('#userdetails').show();
    $('#back').hide();
  }
  var dat = {
    function: 'CUSAJX_UserGridData',
    type: 'all',
    csrfMagicToken: csrfMagicToken,
    limitCount: $('#notifyDtl_length :selected').val(),
    nextPage: nextPage,
    notifSearch: notifSearch,
    order: key,
    sort: sort,
  };
  $.ajax({
    url: '../lib/l-custAjax.php',
    type: 'POST',
    dataType: 'json',
    data: dat,
    success: function (gridData) {
      $('#absoLoader').hide();
      $('.se-pre-con').hide();
      // $('#user_datatable_info').hide();
      // $('#user_datatable_length').hide();
      // $('#user_datatable_filter').hide();
      // $('#user_datatable_paginate').hide();
      $('#user_datatable').DataTable().destroy();
      $('#user_datatable tbody').empty();
      $('#user_datatable').show();
      $('#user_datatable_wrapper').show();
      userTable = $('#user_datatable').DataTable({
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
          $('.loader').hide();
        },
        drawCallback: function (settings) {
          // $(".checkbox-btn input[type='checkbox']").change(function () {
          //     if ($(this).is(":checked")) {
          //         $(this).parents("tr").addClass("selected");
          //     }
          // });
          $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
          // $(".dataTables_filter:first").replaceWith('<div id="notifyDtl_filter" class="dataTables_filter"><label><input type="text" class="form-control form-control-sm" placeholder="Search records" value="' + notifSearch + '" id="notifSearch" aria-controls="notifyDtl"></label></div>');
        },
      });
      $('.dataTables_filter input').addClass('form-control');
      $('.tableloader').hide();
    },
  });

  // $('.dataTables_filter input').addClass('form-control');
  $('#user_datatable').on('click', 'tr', function () {
    var rowID = userTable.row(this).data();
    userTable.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
    var rowID = userTable.row(this).data();
    var passStatus = rowID[6];
    UserLoginType = rowID[7];
    $('#selectedUser').val(rowID[8]);
    //This is for hide/show resend password option
    if (passStatus === 0) {
      $('#mail_resend_option').show();
      $('#resetPass_resend_option').hide();
    } else {
      $('#mail_resend_option').hide();
      $('#resetPass_resend_option').show();
    }
  });

  $('#user_datatable').on('dblclick', 'tr', function () {
    rightContainerSlideOn('edit-user');
    $('#editOption').show();
    $('#toggleButton').hide();
    getUserRoles();
    getAdminRole();
    getTime_Zones();
    get_UserDetails();
  });

  $('#users_searchbox').keyup(function () {
    userTable.search(this.value).draw();
    $('#user_datatable tbody').eq(0).html();
  });
  $('#user_datatable').DataTable().search('').columns().search('').draw();
}

// $('#editOption').click(function () {
//   if (UserLoginType == 'SSO User') {
//     $('#edit_advroleId').attr('disabled', 'disabled');
//     $('#edit_advroleId').parent().children().css({ cursor: 'not-allowed' });
//     $('#edit_sectype').attr('disabled', 'disabled');
//     $('#edit_sectype').parent().children().css({ cursor: 'not-allowed' });
//   }
// });

$('#addAdvUser').click(function (e) {
  $('#errMsg,#advusername-err,#last_name-err,#advuser_email-err,#add_advroleId-err,#add_userLevel-err').html('');

  var isReqFieldsFilled = true;
  var agentEmail = $('#advuser_email').val().toLowerCase();
  var agentRole = $('#add_advroleId').val();
  var agentRoleName = $('#add_advroleId option:selected').html();
  var add_Customers = $('#add_Customers').val();
  var agentName = $('#advusername').val();
  var agentLastName = $('#last_name').val();
  var usertimezone = $('#add_timeZone').val();
  var selectedUserLevel = '';
  var selectedCustomer = '';
  var language = $('#lang').val();
  var secOpt = $('#add_sectype').val();

  if (agentName.length > 24 || agentLastName.length > 24) {
    $.notify('The first or last name of the user should not be have more than 24 characters');
    return false;
  }
  if (agentName === '') {
    $('#advusername-err').css('color', 'red').html('Please enter first name');
    return false;
  }
  if (!validateName(agentName)) {
    $('#advusername-err').css('color', 'red').html('No special characters or numeric allowed in first name');
    return false;
  }
  if (agentLastName === '') {
    $('#last_name-err').css('color', 'red').html('Please enter last name');
    return false;
  }
  if (!validateName(agentLastName)) {
    $('#last_name-err').css('color', 'red').html('No special characters or numeric allowed in last name');
    return false;
  }
  if (agentEmail === '') {
    $('#advuser_email-err').css('color', 'red').html('Please enter email Id');
    return false;
  }
  if (!validateEmailAddr(agentEmail)) {
    $('#advuser_email-err').css('color', 'red').html('Enter valid email');
    return false;
  }
  if (add_Customers == '' || add_Customers == null || add_Customers == 'null') {
    $('#add_userLevel-err').css('color', 'red').html('One or more sites need to be selected');
    return false;
  }

  if (agentRole === '' || agentRole === undefined) {
    $('#add_advroleId-err').css('color', 'red').html('Please choose a role to be assigned to the user');
    return false;
  }

  if (usertimezone === '' || usertimezone === undefined) {
    $('#add_timeZone-err').css('color', 'red').html('Please select user timezone to create user');
    return false;
  }

  if (secOpt === '' || secOpt === undefined) {
    $('#add_sectype-err').css('color', 'red').html('Please select any security type or select none');
    return false;
  }

  if (bussinessLevel === 'Commercial' && customerType === '2' && aviraInst === '1') {
    var add_userLevel = $('#add_userLevel').val();
    selectedUserLevel = add_userLevel;
    if (add_userLevel === 'customer') {
      var add_Customers = $('#add_Customers').val();
      if (add_Customers === '') {
        $('#errMsg').css('color', 'red').html('Please select customer');
        isReqFieldsFilled = false;
      } else {
        selectedCustomer = add_Customers;
      }
      if (add_Customers === null) {
        $('#errMsg').css('color', 'red').html('Please select customer');
        isReqFieldsFilled = false;
      } else {
        selectedCustomer = add_Customers;
      }
    }
  } else if (bussinessLevel === 'Commercial' && (customerType !== '2' || aviraInst === '0')) {
    var add_Customers = $('#add_Customers').val();
    if (add_Customers === '' || add_Customers === null) {
      $('#errMsg').css('color', 'red').html('Please select Site');
      isReqFieldsFilled = false;
    } else {
      selectedCustomer = add_Customers;
    }
    selectedUserLevel = 'customer';
  }

  if (agentRole == undefined || agentRole == '') {
    $.notify('Please choose a role to be assigned to the user');
    isReqFieldsFilled = false;
  }

  if (agentName == agentLastName) {
    $.notify('First and last name cannot be same.');
    return false;
  }

  /*if(agentName != ""){
     var nameFilter = /^[a-zA-Z]+$/;
     if (nameFilter.test(agentName)) {
     return true;
     } else {
     $("#errMsg").text("No numeric or special characters allowed");
     return false;
     }
     }

     if(agentLastName != ""){
     var nameFilter = /^[a-zA-Z]+$/;
     if (nameFilter.test(agentLastName)) {
     return true;
     } else {
     $("#errMsg").text("No numeric or special characters allowed");
     return false;
     }
     }*/

  if (isReqFieldsFilled == true) {
    $('#loadingSuccessMsg').html(
      '<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." style="margin-left: 23%;width:auto !important; vertical-align: inherit !important;"/>',
    );
    $.ajax({
      type: 'POST',
      url: '../lib/l-custAjax.php',
      //data: "function=CUSAJX_CreateUser&userEmail=" + agentEmail + "&userName=" + agentName + "&lastname=" + agentLastName + "&userRole=" + agentRole + "&userlevel=" + selectedUserLevel + "&userCustomer=" + selectedCustomer + "&language=" + language + "&agentRoleName=" + agentRoleName+"&sectype="+secOpt,
      data: {
        function: 'CUSAJX_CreateUser',
        userEmail: agentEmail,
        userName: agentName,
        lastname: agentLastName,
        userRole: agentRole,
        userlevel: selectedUserLevel,
        userCustomer: selectedCustomer,
        language: language,
        agentRoleName: agentRoleName,
        sectype: secOpt,
        timezone: usertimezone,
        csrfMagicToken: csrfMagicToken,
      },
      success: function (result) {
        console.log(result);
        result = JSON.parse(result);
        if (result.msg === 'DONE') {
          $('#add-new-user').modal('hide');
          $('#user_added').modal('show');
          $('#add-new-user').hide();
          // create install user entry
          if (agentRoleName === 'AdminRole') {
            // create install user entry only for Admin type users
            createInstallUser(agentEmail, agentName, agentLastName, agentRole, selectedCustomer);
          }
          if (!result.pass){
            $.notify('User ' + agentName + ' Added Successfully<br/>Confirmation email has been sent to the created user.');
          }else{
            $.notify('User ' + agentName + ' Added Successfully<br/>Email has not been sent');
          }
          rightContainerSlideClose('add-new-user');
          user_datatable();
          location.reload();
        } else if (result.msg === 'DUPLICATE') {
          $.notify('There is already a user with the same details');
          rightContainerSlideClose('add-new-user');
        } else {
          $.notify('Email has not been sent. Please try again');
          rightContainerSlideClose('add-new-user');
        }
      },
    });
  }
});

function createInstallUser(agentEmail, firstName, lastName, agentRole, siteList) {
  $.ajax({
    url: '../device/org_api.php',
    type: 'POST',
    data: {
      function: 'create_InstallUser',
      fname: firstName,
      lname: lastName,
      emailid: agentEmail,
      role: agentRole,
      sitelist: siteList,
      csrfMagicToken: csrfMagicToken,
    },
    success: function (data) {
      console.log(JSON.stringify(data));
    },
    error: function (err) {
      console.log(JSON.stringify(err));
    },
  });
}

function Call_alert(msg, title, type) {
  sweetAlert(title, msg, type);
}

function reloadGrid() {
  setTimeout(function () {
    user_datatable();
  }, 2000);
}

function get_UserDetails() {
  $('#errMsgEdit').html('');
  var sel_userId = $('#selectedUser').val(); //$('#user_datatable tbody tr.selected').attr('id');
  var editButton = $('#edit-user').find('#editOption');

  editButton.show();

  if (
    window.userid != undefined &&
    !isNaN(window.userid) &&
    window.userid != undefined &&
    !isNaN(sel_userId) &&
    parseInt(window.userid) == parseInt(sel_userId)
  ) {
    editButton.hide();
  }

  if (sel_userId == undefined || sel_userId == 'undefined' || sel_userId == '') {
    sweetAlert({
      title: 'Please select a User',
      text: 'Please select a User to modify',
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

  $('#edit_user_option_trigger').trigger('click');
  $('#sel_userid').val(sel_userId);
  $.ajax({
    type: 'POST',
    url: '../lib/l-custAjax.php',
    data: { function: 'CUSAJX_GetUserDetail', userid: sel_userId, csrfMagicToken: csrfMagicToken },
    dataType: 'json',
    success: function (result) {
      $('#edit-user').find('.form-control').attr('readonly', true);
      $('#edit-user').find('.selectpicker').attr('disabled', true);
      $('#edit-user').find('.selectpicker').selectpicker('refresh');

      $('#editUserForm .form-group').removeClass('is-empty');
      $('#edit_loadingSuccessMsg').html('');
      if (result.firstName == '') {
        $('#edit_advusername').val(result.username);
      } else {
        $('#edit_advusername').val(result.firstName);
      }
      $('#edit_last_name').val(result.lastName);
      $('#edit_advuser_email').val(result.user_email);
      $('#edit_advuser_email').attr('disabled', 'disabled');

      $('#edit_advroleId').val(result.role_id).change();
      $('#edit_advroleId').selectpicker('refresh');

      $('#edit_timeZone').val(result.timezone).change();
      $('#edit_timeZone').selectpicker('refresh');

      if (result.userType === 'customer') {
        $('#edit_userLevel').val('customer').change();
        $('#edit_userCustomersDiv').show();
      } else if (result.userType === 'reseller') {
        $('#edit_userLevel').val('reseller').change();
        $('#edit_userCustomersDiv').hide();
      }
      $('#ActualRoleId').val(result.role_id);
      $('#ActualRoleName').val(result.rolename);

      $('#edit_Customers').html(result.customers);
      $('#edit_Sites').html(result.selectedSites);
      $('#edit_sectype').html(result.secType);
      $('#edit-user').find('.selectpicker').selectpicker('refresh');
    },
    error: function (result) {
      $('#edit_loadingSuccessMsg').css('color', 'red').html('Some error occurred');
    },
  });
}

$('#updateAdvUser').click(function (e) {
  var agentName = $('#edit_advusername').val();
  var agentLastName = $('#edit_last_name').val();
  var agentRole = $('#edit_advroleId').val();
  var timezone = $('#edit_timeZone').val();
  //var actualRole = $('#ActualRoleId').val();
  //var adminRole = $('#AdminRoleId').val();
  //var chkArray = [];
  /*var actualRoleName = $('#ActualRoleName').val();
    if(actualRoleName != 'AdminRole'){
        $.notify('You don't have the permission needed to make the edits');
        return false;
    }*/

  $('#errMsgEdit').html('');

  if (agentName == '') {
    $('#errMsgEdit').html('');
    $('#errMsgEdit').css('color', 'red').html('Please enter first name.');
    //        $('#edit_loadingSuccessMsg').css("color", "red").html('<span>Please insert values for required fields.</span>');
    return false;
  }
  if (agentName != '') {
    var nameFilter = /^[a-zA-Z]+$/;
    if (nameFilter.test(agentName)) {
    } else {
      $('#errMsgEdit').html('');
      $('#errMsgEdit').css('color', 'red').html('No special characters or numeric allowed in first name');
      return false;
    }
  }
  if (agentLastName == '') {
    $('#errMsgEdit').html('');
    $('#errMsgEdit').css('color', 'red').html('Please enter last name.');
    //        $('#edit_loadingSuccessMsg').css("color", "red").html('<span>Please insert values for required fields.</span>');
    return false;
  }
  if (agentLastName != '') {
    var nameFilter = /^[a-zA-Z]+$/;
    if (nameFilter.test(agentLastName)) {
    } else {
      $('#errMsgEdit').html('');
      $('#errMsgEdit').css('color', 'red').html('No special characters or numeric allowed in last name');
      return false;
    }
  }
  var sel_userid = $('#sel_userid').val();
  var sitename = $('#edit_Sites').val();

  if (!sitename[0] ||  sitename[0] == '' ||  sitename[0] == null ||  sitename[0] == 'null') {
    $('#errMsgEdit').css('color', 'red').html(' One or more sites need to be selected');
    return false;
  }
  var securityType = $('#edit_sectype').val();
  if (UserLoginType == 'SSO User') {
    securityType = 'none';
  }
  if (securityType == '' || securityType == null || securityType == 'null') {
    $('#errMsgEdit').css('color', 'red').html(' Select any security type or select none');
    return false;
  }

  $('#edit_loadingSuccessMsg').html(
    '<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." style="margin-left: 23%;width:auto !important; vertical-align: inherit !important;"/>',
  );
  $.ajax({
    type: 'POST',
    url: '../lib/l-custAjax.php',
    dataType: 'json',
    data: {
      function: 'CUSAJX_Update_User',
      userName: agentName,
      lastname: agentLastName,
      sel_userid: sel_userid,
      userrole: agentRole,
      Sitename: sitename,
      sectype: securityType,
      timezone: timezone,
      csrfMagicToken: csrfMagicToken,
    },
    success: function (result) {
      if (result.status === 1) {
        successNotify('Successfully updated user info');
        setTimeout(function () {
          document.location.reload();
        }, 2000);
      } else {
        rightContainerSlideClose('edit-user');
        errorNotify(result.message);
        setTimeout(function () {
          location.reload();
        }, 2000);
      }
    },
  });
});
/* It validates for roles, roles and its respective values are should be selected .*/
function validateAddUserRoles(e) {
  var roleId = $('#advroleId').val();
  var resel_ch_id = '';
  var isRolesChecked = true;
  if (roleId == 'reseller') {
    ch_id = $('input:radio[name=reseller]:checked').val();
    if (ch_id == undefined || ch_id == '' || ch_id == 'undefined') {
      isRolesChecked = false;
      $('#req_reseller_radios').html(' <span>Please select reseller</span>');
      $('#reseller_radios').addClass('border-error');
      $('#req_icon_reseller_radios').show();
    } else {
      isRolesChecked = true;
    }
  } else if (roleId == 'customer') {
    resel_ch_id = $('input:radio[name=reseller]:checked').val();
    ch_id = $('input:radio[name=customers]:checked').val();
    if (resel_ch_id == undefined || resel_ch_id == '' || resel_ch_id == 'undefined') {
      $('#req_reseller_radios').html(' <span>Please select reseller</span>');
      $('#reseller_radios').addClass('border-error');
      $('#req_icon_reseller_radios').show();
      isRolesChecked = false;
    } else if (ch_id == undefined || ch_id == '' || ch_id == 'undefined') {
      $('#req_customer_radios').html(' <span>Please select customer</span>');
      $('#customer_radios').addClass('border-error');
      $('#req_icon_customer_radios').show();
      isRolesChecked = false;
    } else {
      isRolesChecked = true;
    }
  } else {
    isRolesChecked = true;
  }
  return isRolesChecked;
}

/* It validates all fields are filled or not.*/
function validateAddUserForm(e) {
  e.preventDefault();
  var isReqFieldsFilled = true;
  $('.addreq').html(' *');
  $('.addreq').each(function () {
    var req_id = this.id;
    var field_id = req_id.replace('req_', '');
    var field_value = $('#' + field_id).val();
    if ($.trim(field_value) === '') {
      $('#req_' + field_id).html(' <span>required</span>');
      isReqFieldsFilled = false;
    } else if (field_id == 'advusername') {
      if (!validateName(field_value)) {
        isReqFieldsFilled = false;
        $('#req_' + field_id).html(' <span>No special characters or numeric allowed in first name</span>');
      } else {
        $('#req_' + field_id)
          .css('color', 'red')
          .html('');
      }
    } else if (field_id == 'last_name') {
      if (!validateName(field_value)) {
        isReqFieldsFilled = false;
        $('#req_' + field_id)
          .css('color', 'red')
          .html(' <span>No special characters or numeric allowed in last name</span>');
      } else {
        $('#req_' + field_id)
          .css('color', 'red')
          .html('');
      }
    } else if (field_id == 'advuser_email') {
      if (!validateEmailAddr(field_value)) {
        isReqFieldsFilled = false;
        $('#req_' + field_id).html(' <span>enter valid email</span>');
      } else {
        $('#req_' + field_id)
          .css('color', 'red')
          .html('');
      }
    } else {
      isReqFieldsFilled = true;
    }
  });
  return isReqFieldsFilled;
}

//On change of role drop down present in add user pop up following function get called.
function getNextList() {
  var roleValue = $('#advroleId').val();
  if (roleValue == 'reseller') {
    $('#reseller_radios_div').show();
    $('#customer_radios_div').hide();
    getResellerList();
  } else if (roleValue == 'customer') {
    $('#reseller_radios_div').show();
    $('#customer_radios_div').show();
    getResellerList();
  } else {
    $('#reseller_radios_div').hide();
    $('#customer_radios_div').hide();
  }
}

//It brings list of radio buttons of resellers in add user pop up, if role is selected as reseller
function getResellerList() {
  $.ajax({
    type: 'POST',
    url: '../lib/l-custAjax.php',
    data: { function: 'CUSAJX_ResellerList', csrfMagicToken: csrfMagicToken },
    success: function (msg) {
      $('#reseller_radios').html(msg.trim());
    },
  });
}

function changeUserLevels() {
  getUserRoles();
  getAdminRole();
  getTime_Zones();
  enableFields();
  $('#advusername').val('');
  $('#last_name').val('');
  $('#advuser_email').val('');
  $('#errMsg').html('');
  var userLevel = $('#add_userLevel').val();
  var optionStr = '';
  var finalSiteName = '';
  var temp = '';
  if (userLevel === 'customer') {
    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: '../lib/l-custAjax.php',
      data: { function: 'CUSTAJX_ResellerCustomers', csrfMagicToken: csrfMagicToken },
      success: function (result) {
        {
          if (result.length === 0) {
            optionStr = '<option value="">No Customers Available</option>';
          } else {
            for (i = 0; i < result.length; i++) {
              if (result[i].companyName.indexOf('__') >= 0) {
                temp = result[i].companyName.split('__');
                finalSiteName = temp[0];
              } else {
                finalSiteName = result[i].companyName;
              }

              /*Fix for: https://nanoheal.atlassian.net/browse/NCP-152
                            var myregexp = /^[-a-zA-Z0-9_]*$/;
//                            var match = myregexp.test(finalSiteName);
                            if (myregexp.test(finalSiteName)) {
                                var split = finalSiteName.split('_');
                                var num = split[1];
                                var regx = /^[0-9]*$/;
                                if (regx.test(num)) {
                                    finalSiteName = split[0];
                                }
                            }*/

              if (finalSiteName != '') {
                optionStr += '<option value=' + result[i].eid + '>' + finalSiteName + '</option>';
              }
            }
          }
          $('#add_Customers').html('');
          $('#add_Customers').html(optionStr);
          $('.selectpicker').selectpicker('refresh');
        }
      },
    });
    $('#add_userCustomersDiv').show();
  } else {
    $('#add_userCustomersDiv').hide();
  }
}

function changeEditUserLevels() {
  var userLevel = $('#edit_userLevel').val();
  if (userLevel === 'customer') {
    $('#edit_userCustomersDiv').show();
  } else {
    $('#edit_userCustomersDiv').hide();
  }
}

//This is written to resend mail to those users who are not set password
$('#mail_resend_option').click(function () {
  var language = $('#lang').val();
  var sel_userId = $('#selectedUser').val(); //$('#user_datatable tbody tr.selected').attr('id');
  if (sel_userId == undefined || sel_userId == 'undefined' || sel_userId == '') {
    //        sweetAlert({
    //            title: 'Please select a User',
    //            text: "Please select the User to send email to",
    //            type: 'error',
    //            confirmButtonColor: '#3085d6',
    //            confirmButtonText: 'Ok'
    //        }).then(function (reason) {
    //            $(".closebtn").trigger("click");
    //        }
    //        ).catch(function (reason) {
    //            $(".closebtn").trigger("click");
    //        });
    //        return false;
    $.notify('Please select the User to send email to');
    closePopUp();
  } else {
    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: '../lib/l-custAjax.php',
      data: {
        function: 'CUSTAJX_ResendUserMail',
        sel_userId: sel_userId,
        language: language,
        csrfMagicToken: csrfMagicToken,
      },
      success: function (result) {
        if (result === 1) {
          Call_alert('Please ask the user to check their registered email id to proceed.', 'Mail Sent Successfully!', 'success');
          reloadGrid();
        } else {
          Call_alert('Some error occurred.', 'Failed!', 'error');
          reloadGrid();
        }
        $('.closebtn').trigger('click');
      },
    });
  }
});
$('#resetPass_resend_option').click(function () {
  var language = $('#lang').val();
  var sel_userId = $('#selectedUser').val(); //$('#user_datatable tbody tr.selected').attr('id');
  if (sel_userId == undefined || sel_userId == 'undefined' || sel_userId == '') {
    //        sweetAlert({
    //            title: 'Please select a User',
    //            text: "Please select a User to reset the password",
    //            type: 'error',
    //            confirmButtonColor: '#3085d6',
    //            confirmButtonText: 'Ok'
    //        }).then(function (reason) {
    //            $(".closebtn").trigger("click");
    //        }
    //        ).catch(function (reason) {
    //            $(".closebtn").trigger("click");
    //        });
    //        return false;
    $.notify('Please select a User to reset the password');
    closePopUp();
  } else {
    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: '../lib/l-custAjax.php',
      data: {
        function: 'CUSTAJX_ResendUserMail',
        sel_userId: sel_userId,
        language: language,
        mailType: 9,
        csrfMagicToken: csrfMagicToken,
      },
      success: function (result) {
        $('#absoFeed').css('display', 'none');
        if (result === 1) {
          $.notify('Please ask the user to check their registered email id to proceed');
          reloadGrid();
        } else {
          Call_alert('Some error occurred.', 'Failed!', 'error');
          reloadGrid();
        }
        $('.closebtn').trigger('click');
      },
    });
  }
});
/* This will reset user add pop up fields and select lists */
//$('#add-new-user').on('hidden.bs.modal', function (e) {
//    $(".error").html(' *');
//    $("#addUserForm input[name!='advroleId']").val("");
//    $("#addUserForm .form-group").addClass("is-empty");
//    $("#loadingSuccessMsg").html('');
//    $("#reseller_radios").html('');
//    $("#customer_radios").html('');
//    $('.form-group select').val('');
//    $(".selectpicker").selectpicker("refresh");
//    // if (ctype == 1) {
//    //     $("#advroleId").val("agent").change();
//    // }
//
//});

/* This will reset user edit pop up fields and select lists */
$('#edit-user').on('hidden.bs.modal', function (e) {
  $('.error').html(' *');
  $("#editUserForm input[name!='edit_advroleId']").val('');
  $('#editUserForm .form-group').addClass('is-empty');
  $('#edit_loadingSuccessMsg').html('');
  $('#edit_reseller_radios').html('');
  $('#edit_customer_radios').html('');
});
$('#user_added').on('hidden.bs.modal', function (e) {
  $('#user_added_message').html(
    '<h2 class="popup-title">User Added</h2><p> <span>Added successfully</span></p><p><span>Confirmation email has been sent to the created user.</span></p>',
  );
});
$('#delete_user').on('hidden.bs.modal', function (e) {
  $('#delete_confirm')
    .attr('style', 'color: #48b2e4 !important')
    .html('<span>If you delete the user, you will not be able to undo the action. Are you sure that you want to continue?</span>');
  $('#delete_user .modal-footer').show();
});
function validateEmailAddr(email) {
  //    var filter = /^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/;
  var filter = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
  if (filter.test(email)) {
    return true;
  } else {
    return false;
  }
}

function validateName(name) {
  var nameFilter = /^[a-zA-Z0-9\s]+$/;
  if (nameFilter.test(name)) {
    return true;
  } else {
    return false;
  }
}

function validateAlphaNumeric(name) {
  var filter = /^[a-z\d\_\s]+$/i;
  if (filter.test(name)) {
    return true;
  } else {
    return false;
  }
}

function goTOLogin() {
  window.location.href = '../admin/loginAudit.php';
}

function isUserListEmpty() {
  var tr = $('#user_datatable tbody tr');

  if (tr && tr.eq(0) && tr.eq(0).attr('id') != undefined && !isNaN(tr.eq(0).attr('id'))) {
    return false;
  }

  return true;
}

$('#export_user').click(function () {
  // var selID = $('#selectedUser').val();
  // if(isUserListEmpty()){
  //     errorNotify("No data available");
  //     return false;
  // }
  // if (!selID) {
  //   errorNotify('No data available');
  //   return false;
  // } else {
    $.notify('Please check the Download Bar or the Downloads folder');
    reloadGrid();
    window.location.href = '../lib/l-custAjax.php?function=CUSAJX_ExportUser&type=' + level;
  // }
});
//This button is present in confirmation pop up of delete user.
$('#delete_yes').click(function () {
  $('#delete_yes').hide();
  $('#no').hide();
  $('#delete_confirm').html('<img src="../vendors/images/loader2.gif" class="loadhome" alt="loading..." style="margin-left: 40%;"/>');
  var sel_user = $('#user_datatable tbody tr.selected').attr('id');
  if (sel_user != '') {
    $.ajax({
      type: 'POST',
      dataType: 'json',
      url: '../lib/l-custAjax.php',
      data: {
        function: 'CUSAJX_DeleteUser',
        selectedUserid: sel_user,
        csrfMagicToken: csrfMagicToken,
      },
      success: function (result) {
        if (result.status == '1' || result.status == 1) {
          $('#delete_confirm').css('color', 'green').html(result.message);
          $('#delete_user .modal-footer').hide();
          setInterval(function () {
            location.reload();
          }, 5000);
        } else {
          $('#delete_confirm').css('color', 'red').html(result.message);
          $('#delete_user .modal-footer').hide();
        }
      },
    });
  }
});

$('#delete_user_option').click(function () {
  var sel_user = $('#selectedUser').val(); //$('#user_datatable tbody tr.selected').attr('id');
  if (sel_user == undefined || sel_user == 'undefined' || sel_user == '') {
    $.notify('Please choose at least one record');
    closePopUp();
  } else {
    sweetAlert({
      title: ' Are you sure you want to delete the user?',
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
          url: '../lib/l-custAjax.php',
          data: {
            function: 'CUSAJX_DeleteUser',
            selectedUserid: sel_user,
            csrfMagicToken: csrfMagicToken,
          },
          success: function (result) {
            if (result.status == '1' || result.status == 1) {
              user_datatable();
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

function resetMfa() {
  var sel_userId = $('#selectedUser').val(); //$('#user_datatable tbody tr.selected').attr('id');

  $.ajax({
    type: 'GET',
    url: '../multiauth/resetUser.php',
    data: { userid: sel_userId, csrfMagicToken: csrfMagicToken },
    success: function (response) {
      var res = $.parseJSON(response);
      if (res.status == 'success') {
        $.notify(res.msg);
      } else {
        $.notify('Reset MFA action failed');
      }
    },
  });
}
