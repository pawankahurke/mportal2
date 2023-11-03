/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {
  ssoStatData = 0;
  getConfiguredSSODetails();

  $('input[name="sso-type"]').click(function () {
    var sso_sel = $(this).val();
    if (sso_sel == 'OAUTH') {
      $('#sso_saml_cnt').hide();
      $('#sso_oauth_cnt').show();
    } else {
      $('#sso_oauth_cnt').hide();
      $('#sso_saml_cnt').show();
    }
  });
});

/*
 * Function to update sso status
 */
function updateSSOGenericCall(ssoStatus) {
  var ssoStatVal = 'disabled';
  if (ssoStatus == '1') {
    ssoStatVal = 'enabled';
  }
  $.ajax({
    url: '../lib/l-sso.php',
    type: 'POST',
    data: { function: 'updateSsoStatusFunc', sso_status: ssoStatus, csrfMagicToken: csrfMagicToken },
    success: function (data) {
      try {
        var resp = JSON.parse(data);
        if (resp['code'] == '200') {
          ssoStatData = parseInt(ssoStatus);
          if (resp['data'] == 'DONE') {
            $.notify('Single Sign-On is ' + ssoStatVal + ' now!');
          }
          if (resp['data'] == 'KEEP_ALIVE') {
            $.notify('Single Sign-On is ' + ssoStatVal + ' now!');
          }
        }
      } catch (error) {
        debugger;
        console.error(error);
      }
    },
    error: function (error) {
      $.notify('Error : ' + error);
    },
  });
}

function updateSSOStatus() {
  if ($('#ssoVal').prop('checked')) {
    updateSSOGenericCall('1');
  } else {
    sweetAlert({
      title: 'Are you sure that you want to disable SSO ?',
      text: 'You have to reenable the module inorder to make your organizational users to use SSO login',
      type: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#050d30',
      cancelButtonColor: '#fa0f4b',
      cancelButtonText: 'No, cancel it!',
      confirmButtonText: 'Yes, disable it!',
    })
      .then(function (result) {
        updateSSOGenericCall('0');
      })
      .catch(function (reason) {
        $('.closebtn').trigger('click');
        $('#ssoVal').prop('checked', true);
      });
  }
}

/*
 * To get configurd SSO details
 */
function getConfiguredSSODetails() {
  $('#absoLoader').show();
  $.ajax({
    url: '../lib/l-sso.php',
    type: 'POST',
    data: { function: 'getConfiguredSSODetailsFunc', csrfMagicToken: csrfMagicToken },
    success: function (data) {
      var ssoData = JSON.parse(data);
      $('#absoLoader').hide();
      if (ssoData['data'] == 'NODATA') {
        $('#ssoVal').prop('checked', false);
        ssoConfType = '';
      } else {
        var ssoStatus = ssoData['data']['sso_status'];
        if (ssoStatus == 0) {
          $('#ssoVal').prop('checked', false);
        } else {
          $('#ssoVal').prop('checked', true);
        }
        ssoConfType = ssoData['data']['sso_type'];
        var companyName = ssoData['data']['company_name'];
        var idpName = ssoData['data']['idp_full_name'];
        var idpMetaUrl = ssoData['data']['idp_metadata_url'];
        var idpMetaData = ssoData['data']['idp_metadata'];
        var idpMetaData = ssoData['data']['idp_metadata'];
        var spEntityID = ssoData['data']['sp_entity_id'];
        var acsUrl = ssoData['data']['acs_url'];
        var samlVstatus = ssoData['data']['saml_vstatus'];

        var authorizeUrl = ssoData['data']['authorize_url'];
        var accessUrl = ssoData['data']['access_url'];
        var clientID = ssoData['data']['client_id'];
        var clientSecret = ssoData['data']['client_secret'];
        var scope = ssoData['data']['scope'];
        var resourceUrl = ssoData['data']['resource_url'];
        var oauthVers = ssoData['data']['oauth_version'];
        var oauthTenantID = ssoData['data']['tenant_id'];
        var oauthVstatus = ssoData['data']['oauth_vstatus'];

        $('input[name="sso-type"][value="' + ssoConfType + '"]').prop('checked', true);
        if (ssoConfType == 'SAML') {
          $('#saml_company_name').val(companyName);
          $('#saml_idp_name').val(idpName);
          $('#saml_idp_metadata_url').val(idpMetaUrl);
          $('#saml_idp_metadata').val(idpMetaData);
          $('#saml_sp_entity_id').val(spEntityID);
          $('#saml_acs_url').val(acsUrl);
          if (samlVstatus == 1) {
            $('#saml_vstatus').html('Verified').css({ color: 'green' });
          } else {
            $('#saml_vstatus').html('Pending').css({ color: 'red' });
          }

          $('#sso_saml_cnt, #saml_verify_stbox').show();
          $('#sso_oauth_cnt, #oauth_verify_stbox').hide();
        } else {
          $('#oauth_company_name').val(companyName);
          $('#oauth_idp_name').val(idpName);
          $('#oauth_auth_url').val(authorizeUrl);
          $('#oauth_access_url').val(accessUrl);
          $('#oauth_client_id').val(clientID);
          $('#oauth_client_secret').val(clientSecret);
          $('#oauth_scope').val(scope);
          $('#oauth_resource_url').val(resourceUrl);
          $('#oauth_version').val(oauthVers);
          $('#oauth_tenant_id').val(oauthTenantID);
          if (oauthVstatus == 1) {
            $('#oauth_vstatus').html('Verified').css({ color: 'green' });
          } else {
            $('#oauth_vstatus').html('Pending').css({ color: 'red' });
          }

          $('#sso_oauth_cnt, #oauth_verify_stbox').show();
          $('#sso_saml_cnt, #saml_verify_stbox').hide();
        }
      }
    },
    error: function (error) {
      console.log('Error : ' + error);
    },
  });
}

/*
 * OAuth form field validation function
 */
function getValidatedOAuthData() {
  var ssoType = $('input[name="sso-type"]:checked').val();
  var companyName = $('#oauth_company_name').val();
  var idpFullName = $('#oauth_idp_name').val();
  var authorizeUrl = $('#oauth_auth_url').val();
  var accessUrl = $('#oauth_access_url').val();
  var clientId = $('#oauth_client_id').val();
  var clientSecret = $('#oauth_client_secret').val();
  var scope = $('#oauth_scope').val();
  var resourceUrl = $('#oauth_resource_url').val();
  var oauthVers = $('#oauth_version').val();
  var tenantId = ''; //$('#oauth_tenant_id').val();

  if (typeof ssoType == 'undefined') {
    $.notify('Please select a single sign on method.');
    return false;
  } else if (companyName == '') {
    $.notify('Please enter the company name.');
    return false;
  } else if (idpFullName == '') {
    $.notify('Please enter the IdP name.');
    return false;
  } else if (authorizeUrl == '') {
    $.notify('Please enter the authorize url.');
    return false;
  } else if (accessUrl == '') {
    $.notify('Please enter the access url.');
    return false;
  } else if (clientId == '') {
    $.notify('Please enter the client ID.');
    return false;
  } else if (clientSecret == '') {
    $.notify('Please enter the client secret.');
    return false;
  } else if (scope == '') {
    $.notify('Please enter the scope.');
    return false;
  } else if (oauthVers == '') {
    $.notify('Please enter the OAuth type.');
    return false;
  } else if (resourceUrl == '') {
    $.notify('Please enter the OAuth Resource URL.');
    return false;
  } else {
    var oAuthDataObj = {
      ssoType: ssoType,
      companyName: companyName,
      idpName: idpFullName,
      authorizeUrl: authorizeUrl,
      accessUrl: accessUrl,
      clientId: clientId,
      clientSecret: clientSecret,
      scope: scope,
      resourceUrl: resourceUrl,
      oauthVers: oauthVers,
      tenantId: tenantId,
      ssoStatData: ssoStatData,
    };

    return oAuthDataObj;
  }
}

/*
 * OAuth Validation & Saving Configuration function
 */
$('#oauth_verify_btn').click(function () {
  $('#oauth_vstatus').text('');
  var oauthData = getValidatedOAuthData();

  if (oauthData) {
    var oAuthVerifyObj = { function: 'verifyOAuthDetailsFunc', csrfMagicToken: csrfMagicToken };

    var samlVerifyReqData = { ...oAuthVerifyObj, ...oauthData };

    $.ajax({
      url: '../lib/l-sso.php',

      type: 'POST',
      data: samlVerifyReqData,
      success: function (data) {
        var resp = JSON.parse(data);
        var reurl = resp['reurl'];
        if (resp['code'] == 200) {
          // make redirect based on the response
          window.open(reurl + '/api/connect/provider', '_blank');
        }
      },
      error: function (error) {
        console.log('Error : ' + error);
      },
    });
  }
});

function saveOAuthDetails(oAuthSaveReqData) {
  $.ajax({
    url: '../lib/l-sso.php',
    type: 'POST',
    data: oAuthSaveReqData,
    success: function (data) {
      var resp = JSON.parse(data);
      $('#absoLoader').hide();
      if (resp['code'] == '200') {
        if (resp['data'] == 'DONE') {
          $.notify('OAUTH configuration created successfully.');
        } else if (resp['data'] == 'UPDATED') {
          $.notify('OAUTH configuration updated successfully.');
        } else {
          $.notify('Failed to create/update OAUTH configuration.');
        }
      } else {
        $.notify('Failed to create/update OAUTH configuration.');
      }
    },
    error: function (error) {
      console.log('Error : ' + error);
    },
  });
}

$('#oauth_save_btn').click(function () {
  var ssoType = $('input[name="sso-type"]:checked').val();
  var oauthData = getValidatedOAuthData();
  var oAuthSaveObj = { function: 'saveOauthDetailsFunc', csrfMagicToken: csrfMagicToken };

  if (oauthData) {
    if (ssoType != ssoConfType && ssoConfType != '') {
      sweetAlert({
        title: 'Are you sure that you want to override SAML Configuration?',
        text: 'You wont be able to recover the SAML configuration after update!',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#050d30',
        cancelButtonColor: '#fa0f4b',
        cancelButtonText: 'No, cancel it!',
        confirmButtonText: 'Yes, save it!',
      })
        .then(function (result) {
          $('#absoLoader').show();
          var oAuthSaveReqData = { ...oAuthSaveObj, ...oauthData };
          saveOAuthDetails(oAuthSaveReqData);
        })
        .catch(function (reason) {
          $('.closebtn').trigger('click');
        });
    } else {
      $('#absoLoader').show();
      var oAuthSaveReqData = { ...oAuthSaveObj, ...oauthData };
      saveOAuthDetails(oAuthSaveReqData);
    }
  }
});

/*
 * SAML - Get metadata url verification
 */
$('#saml_get_metadata').click(function () {
  var metadataUrl = $('#saml_idp_metadata_url').val();
  var pattern = /^(http|https)?:\/\/[a-zA-Z0-9-\.]+\.[a-z]{2,4}/;

  var IsValidUrl = pattern.test(metadataUrl);
  if (metadataUrl == '') {
    $.notify('Please enter the IdP metadata URL to get meta data.');
    return false;
  } else if (!IsValidUrl) {
    $.notify('Please enter valid IdP metadata URL to get meta data.');
    return false;
  } else {
    // get metadata from url
    $('#loader_box').show();
    $.ajax({
      url: '../lib/l-sso.php',
      type: 'POST',
      data: { function: 'getMetaDataDetailsFunc', metadataurl: metadataUrl, csrfMagicToken: csrfMagicToken },
      success: function (data) {
        var metaData = JSON.parse(data);
        if (metaData.code !== 200) {
          $.notify(metaData.data);
          $('#saml_idp_metadata').val('');
        } else {
          $('#saml_idp_metadata').val(metaData['data']);
        }
        $('#loader_box').fadeOut(1000);
      },
      error: function (error) {
        console.log('Error : ' + error);
      },
    });
  }
});

/*
 *
 */
function getValidatedSamlData() {
  var ssoType = $('input[name="sso-type"]:checked').val();
  var companyName = $('#saml_company_name').val();
  var idpName = $('#saml_idp_name').val();
  var idpMetadataUrl = $('#saml_idp_metadata_url').val();
  var idpMetaData = $('#saml_idp_metadata').val();
  var spEntityId = $('#saml_sp_entity_id').val();
  //var acsUrl = $('#saml_acs_url').val();

  if (typeof ssoType == 'undefined') {
    $.notify('Please select a single sign on method.');
    return false;
  } else if (companyName == '') {
    $.notify('Please enter the company name.');
    return false;
  } else if (idpName == '') {
    $.notify('Please enter the IdP name.');
    return false;
  } else if (idpMetadataUrl == '') {
    $.notify('Please enter the IdP metadata URL.');
    return false;
  } else if (idpMetaData == '') {
    $.notify('Please get the metadata details.');
    return false;
  } else if (spEntityId == '') {
    $.notify('Please enter the SP entity ID.');
    return false;
  } else {
    var samlData = {
      ssoType: ssoType,
      companyName: companyName,
      idpName: idpName,
      idpMetadataUrl: idpMetadataUrl,
      idpMetaData: idpMetaData,
      spEntityId: spEntityId,
      //acsUrl: acsUrl,
      ssoStatData: ssoStatData,
    };

    return samlData;
  }
}

/*
 * SAML Validation & Saving Configuration
 */
$('#saml_verify_btn').click(function () {
  var samlData = getValidatedSamlData();

  if (samlData) {
    var samlVerifyObj = { function: 'verifySamlDetailsFunc', csrfMagicToken: csrfMagicToken };
    var samlVerifyReqData = { ...samlVerifyObj, ...samlData };

    $.ajax({
      url: '../lib/l-sso.php',
      type: 'POST',
      data: samlVerifyReqData,
      success: function (data) {
        var resp = JSON.parse(data);
        if (resp['code'] == '200') {
          $.notify(resp['data']);
          // $.notify('Redirecting to the verification portal.');
          // setTimeout(function () {
          //     window.open(resp['data'], '_blank');
          // }, 2000);
        } else {
          $.notify('Code : ' + resp['code'] + ' Error : ' + resp['data']);
        }
      },
      error: function (error) {
        console.log('Error : ' + error);
      },
    });
  }
});

function saveSamlDetails(samlSaveReqData) {
  $.ajax({
    url: '../lib/l-sso.php',
    type: 'POST',
    data: samlSaveReqData,
    success: function (data) {
      var resp = JSON.parse(data);
      $('#absoLoader').hide();
      if (resp['code'] == '200') {
        if (resp['data'] == 'DONE') {
          $.notify('SAML configuration is created successfully.');
        } else if (resp['data'] == 'UPDATED') {
          $.notify('SAML configuration is updated successfully.');
        } else {
          $.notify('Failed to create/update SAML configuration.');
        }
      } else {
        $.notify('Failed to create/update SAML configuration.');
      }
    },
    error: function (error) {
      console.log('Error : ' + error);
    },
  });
}

$('#saml_save_btn').click(function () {
  var ssoType = $('input[name="sso-type"]:checked').val();
  var samlData = getValidatedSamlData();
  var samlSaveObj = { function: 'saveSamlDetailsFunc', csrfMagicToken: csrfMagicToken };

  if (samlData) {
    if (ssoType != ssoConfType && ssoConfType != '') {
      sweetAlert({
        title: 'Are you sure that you want to override OAuth Configuration?',
        text: 'You wont be able to recover the OAuth configuration after update!',
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#050d30',
        cancelButtonColor: '#fa0f4b',
        cancelButtonText: 'No, cancel it!',
        confirmButtonText: 'Yes, save it!',
      })
        .then(function (result) {
          $('#absoLoader').show();
          var samlSaveReqData = { ...samlSaveObj, ...samlData };
          saveSamlDetails(samlSaveReqData);
        })
        .catch(function (reason) {
          $('.closebtn').trigger('click');
        });
    } else {
      $('#absoLoader').show();
      var samlSaveReqData = { ...samlSaveObj, ...samlData };
      saveSamlDetails(samlSaveReqData);
    }
  }
});

/*
 * Formalities to clear SSO Configuration
 */
$('.sso_clear_btn').click(function () {
  var ssoType = $('input[name="sso-type"]:checked').val();
  sweetAlert({
    title: 'Are you sure that you want to clear ' + ssoType + ' Configuration?',
    text: 'You wont be able to recover the configuration once cleared!',
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#050d30',
    cancelButtonColor: '#fa0f4b',
    cancelButtonText: 'No, cancel it!',
    confirmButtonText: 'Yes, clear it!',
  })
    .then(function (result) {
      $('#absoLoader').show();
      $.ajax({
        url: '../lib/l-sso.php',
        type: 'POST',
        data: { function: 'clearSSODetailsFunc', csrfMagicToken: csrfMagicToken },
        success: function (data) {
          var resp = JSON.parse(data);
          $('#absoLoader').hide();
          if (resp['code'] == '200') {
            if (resp['data'] == 'DONE') {
              $.notify(ssoType + ' configuration details cleared successfully!');
            } else {
              $.notify('Failed to clear ' + ssoType + ' details! Please try again.');
            }
          }
        },
        error: function (error) {
          $.notify('Error : ' + error);
        },
      });
    })
    .catch(function (reason) {
      $('.closebtn').trigger('click');
    });
});
