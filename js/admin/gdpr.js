/**
 * File History
 * Date         Who     What
 * ==========================
 * 18-06-2019   JHN     File created and initial implementation.
 * 21-06-2019   CRN     delete user functionality.
 *
 */

var gdprType = '';

function setRetrieveType(type, ref) {
  gdprType = type;
  if (type === 'server') {
    $('#gdprServer').removeClass('buttonGrey');
    $('#gdprClient').addClass('buttonGrey');
    $('#deviceName').find('input[type]').attr('disabled', true);
  } else if (type === 'client') {
    $('#gdprClient').removeClass('buttonGrey');
    $('#gdprServer').addClass('buttonGrey');
    $('#deviceName').find('input[type]').attr('disabled', false);
  }
}

$('#downloadData').click(function () {
  var redirectUrl = '';
  var retrieveType = $("input[name='dataOption']:checked").val();
  if (gdprType === '') {
    $.notify('Please choose the type of server or client');
    return false;
  } else if (retrieveType === undefined || retrieveType === '') {
    $.notify('Please select a retrieve type!');
    return false;
  } else {
    if (retrieveType === 'user') {
      var retrieveVal = $('#username').val();
      if (retrieveVal === '') {
        $.notify('Please enter the Username');
        return false;
      }
      redirectUrl = 'retrieveUserData.php?username=' + retrieveVal + '&type=user';
    } else if (retrieveType === 'device') {
      var retrieveVal = $('#devicename').val();
      if (retrieveVal === '') {
        $.notify('Please the name of the device');
        return false;
      }
      redirectUrl = 'retrieveUserData.php?username=' + retrieveVal + '&type=device';
    }

    if (window.gdprType != undefined && window.gdprType == 'client') {
      if (retrieveType != undefined && (retrieveType === 'user' || retrieveType === 'device')) {
        var typeValue = retrieveType == 'user' ? $('#username').val() : $('#devicename').val(),
          type = retrieveType == 'user' ? 'user' : 'device';

        document.location.href = '../lib/l-gdpr.php?function=downloadClientData&type=' + type + '&type_value=' + typeValue;
        return true;
      }
    }
  }

  var data = { function: 'get_UserData', type: retrieveType, typeVal: retrieveVal, csrfMagicToken: csrfMagicToken };
  $.ajax({
    url: '../lib/l-gdpr.php',
    type: 'POST',
    data: data,
    success: function (data) {
      var res = $.trim(data);
      if (res === 'success') {
        debugger;
        location.href = redirectUrl;
      } else {
        if (retrieveType === 'user') {
          $.notify('User details are not available');
        } else {
          $.notify('Device details are not available');
        }
      }
    },
    error: function (errorThrown) {},
  });
});

$('#deleteData').click(function () {
  var redirectUrl = '';
  var retrieveType = $("input[name='dataOption']:checked").val();
  if (gdprType === '') {
    $.notify('Please choose the type of server or client');
    return false;
  } else if (retrieveType === undefined || retrieveType === '') {
    $.notify('Please select a retrieve type!');
    return false;
  } else {
    if (gdprType != undefined && (gdprType == 'server' || gdprType == 'client')) {
      var data;
      var url;
      if (gdprType == 'server') {
        if (retrieveType === 'user') {
          var username = $('#username').val();
          if (username === '') {
            $.notify('Please enter the Username');
            return false;
          }

          //                    data = { function: 'deleteUserData', type: retrieveType, username: username};
          url = '../lib/l-gdpr.php&function=deleteUserData&type=' + retrieveType + '&username' + username;
        } else if (retrieveType === 'device') {
        }
      } else if (gdprType == 'client') {
        if (retrieveType === 'user') {
          var username = $('#username').val();
          if (username === '') {
            $.notify('Please enter the Username');
            return false;
          }

          //                    data = { function: 'deleteUserClientData', type: retrieveType, username: username};
          url = '../lib/l-gdpr.php&function=deleteUserClientData&type=' + retrieveType + '&username' + username;
        } else if (retrieveType === 'device') {
          var devicename = $('#devicename').val();
          if (devicename === '') {
            $.notify('Please enter the Username');
            return false;
          }

          //                    data = { function: 'deleteMachineClientData', type: retrieveType, devicename: devicename};
          url =
            '../lib/l-gdpr.php&function=deleteMachineClientData&type=' +
            retrieveType +
            '&devicename' +
            username +
            '&csrfMagicToken=' +
            csrfMagicToken;
        }
      }

      if (data != undefined) {
        $.ajax({
          url: url,
          type: 'get',
          //                    data : data,
          success: function (data) {
            data = $.parseJSON(data);
            if (data.success == false) {
              errorNotify(data.message);
            } else {
              successNotify(data.message);
            }
          },
          error: function (errorThrown) {},
        });
      }
    }
  }
});
