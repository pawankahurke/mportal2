var sideBarVizLoadProgress = false;
var fn_getUserDetails = false;
var fn_InsertUpdateTileNames = false;
var fn_getDashboardList = false;
var loadDashboard = false;
var errorCube = 0;
$(document).ready(function () {
  //    $('.toolbar').hide();
  $('.showErrorImage').hide();
  $('#Iframe').hide();
  var page = $('#pageName').html();
  if (page == 'Home Page') {
    if (!fn_getUserDetails) {
      getUserDetails();
    }
  }
});

function InsertUpdateTileNames() {
  fn_InsertUpdateTileNames = true;
  $.ajax({
    type: 'POST',
    url: '../communication/communication_ajax.php',
    data: { function: 'get_TileNames', csrfMagicToken: csrfMagicToken },

    //  dataType: "json",
    success: function (msgVal) {
      console.log(msgVal);
    },
    error: function (error) {
      console.log(error);
    },
  });
}

function getUserDetails() {
  fn_getUserDetails = true;
  $.ajax({
    type: 'POST',
    url: '../home/homeFunction.php',
    data: { function: 'Get_userDetails', csrfMagicToken: csrfMagicToken },
    dataType: 'json',
    success: function (data) {
      $('#user').val(data.adminEmail);
      $('#custtype').val(data.customerType);
      $('.img-circle').html(data.imgPath);
      $('.user-name').html(data.firstName);
      $('.encry_img').html(data.imgprofileencry);
      $('#uname').attr('title', data.firstName);
    },
  });
}

function loadLandingpage() {
  // if(loadDashboard){
  //     return;
  // }
  // loadDashboard = true;
  $('#absoLoader').show();
  var label = $('#CubeDateString').val();
  $('#Iframe').hide();
  var level = $('#searchType').val();
  var val = $('#searchValue').val();
  var kid = $('#dashId').val();
  var name = $('#dashoardname').val();
  if (kid == '') {
    kid = sessionStorage.getItem('dashId');
  }

  $.ajax({
    type: 'POST',
    url: '../dashboard/dashboardFunctions.php',
    data: {
      function: 'load_CubePage',
      csrfMagicToken: csrfMagicToken,
      dashid: kid,
      dashName: name,
      label: label,
    },
    dataType: 'json',
    success: function (data) {
      // Create global var with current visualization service config.
      // visualization iframe will take from here jwt token for cubjs querys.
      errorCube = 0;
      window.visualizationServiceConfig = data;

      if (data.hasData != undefined && data.hasData == false) {
        $('#Iframe,#absoLoader').hide();
        errorNotify('This group has no machines');
        return false;
      }
      $('#timediv').show();
      $('#dashdiv').show();
      $('#absoLoader').hide();
      //            $('#Iframe').show();
      if ($.trim(data.msg) === 'no') {
        $('#homeError').html('No default dashboard for this user').show();
        //                $('#dashdiv').hide();
        //                $('#timediv').hide();
      } else {
        $('#homeError').hide();
        var url = data;
        var selectedVal = data.SelectedVal;

        var checkFlag = iniFrame(data.url);

        //                if(checkFlag){
        $('.toolbar').show();
        $('#selectedFilter').val(selectedVal);
        $('#dashName').html(data.name);
        $('#dashId').val(data.dashId);
        $('.showErrorImage').hide();
        $('#Iframe').show();
        $('#IframeTop').show();
        $('#pageName').html(data.name);
        if (data.chartType === 'metabase') {
          $('#reportrange').hide();
        } else {
          $('#reportrange').show();
        }
        let ifr = document.getElementById('Iframe');
        if (ifr === null) {
          debugger;
          location.href = '/Dashboard/home';
          return;
        }
        document.getElementById('Iframe').src = data.url;

        //                }else{
        /*    $('.toolbar').hide();
                    $('.showErrorImage').show();
                    $('#Iframe').hide();
                    $('#selectedFilter').val('');
                    $('#dashName').html('');
                    $('#dashId').val('');*/
        //                }
      }
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      console.log('error');
      errorCube++;
      $('#absoLoader').hide();
      $('#Iframe').show();
      $('#homeError').show();
      $('#homeError').attr(
        'style',
        `display: block;
      width: calc(100% - 320px);
      text-align: center;
      top: 50%;
      background: #ffffff;
      position: relative;
      padding: 20px;
      margin: 200px;
      border-radius: 10px;
      color: #c85151;
      font-size: 22px;`,
      );
      console.log(errorCube);
      if (errorCube <= 5) {
        loadLandingpage();
      }
      $('#homeError').html(`You do not have role: dashboardview`);
    },
  });
}

// function test() {
//   document
//     .getElementById('Iframe')
//     .contentWindow.postMessage(
//       '5019c6cf49db4d6d912de7eefb142fb0c69e5d1fd558e269df401c7cdda676c3ab87b11b90eb2b47a618e95a25e3a11a59103af9547cf692b040e5d2bc425fcd',
//       '*',
//     );
// }

function iniFrame(url) {
  var gfg = window.frameElement;
  var FlagCheck = false;
  // Checking if webpage is embedded
  if (gfg) {
    FlagCheck = true;
  } else {
    FlagCheck = false;
  }
}

$(function () {
  var start = moment().subtract(29, 'days');
  var end = moment();
  var time = start.format('YYYY-MM-DD') + ' 00:00:00.000,' + end.format('YYYY-MM-DD') + ' 23:59:59.999';
  $('#reportrange span').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
  $('#CubeDateString').val('Last 30 Days');

  if ($('#reportrange') && $('#reportrange').daterangepicker) {
    $('#reportrange').daterangepicker(
      {
        showDropdowns: true,
        maxSpan: {
          days: 30,
        },
        ranges: {
          Today: [moment(), moment()],
          Yesterday: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
          'Last 7 Days': [moment().subtract(6, 'days'), moment()],
          'Last 30 Days': [moment().subtract(29, 'days'), moment()],
          'Last 60 Days': [moment().subtract(59, 'days'), moment()],
          'Last 90 Days': [moment().subtract(89, 'days'), moment()],
          'This Month': [moment().startOf('month'), moment().endOf('month')],
          'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
        },
        startDate: start,
        endDate: end,
        autoApply: false,
      },
      function (start, end, label) {
        if (label == 'Custom Range') {
          label = start.format('YYYY-MM-DD') + ' 00:00:00.000,' + end.format('YYYY-MM-DD') + ' 23:59:59.999';
        }
        $('#CubeDateString').val(label);
        $('#reportrange span').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
        loadLandingpage();
      },
    );
  }
});

$(function () {
  $('#reportrange').on('apply.daterangepicker', function (ev, picker) {
    var label = picker.startDate.format('YYYY-MM-DD') + ' 00:00:00.000,' + picker.endDate.format('YYYY-MM-DD') + ' 23:59:59.999';
    $('#CubeDateString').val(label);
    $('#reportrange span').html(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
    loadLandingpage();
  });
});
