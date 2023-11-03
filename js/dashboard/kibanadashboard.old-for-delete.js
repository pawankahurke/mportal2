var sideBarVizLoadProgress = false;

function loadLandingpage() {
  $('#absoLoader').show();
  $('#Iframe').hide();
  var level = $('#searchType').val();
  var val = $('#searchValue').val();
  var kid = $('#dashId').val();
  //      console.log(kid);
  if (kid == '') {
    kid = sessionStorage.getItem('dashId');
  }

  $.ajax({
    type: 'GET',
    url: '../dashboard/homeFunction.php?function=loadHomePage',
    data: {
      kid: kid,
      lev: level,
      value: val,
      csrfMagicToken: csrfMagicToken,
    },
    dataType: 'json',
    success: function (data) {
      $('#absoLoader').hide();
      $('#Iframe').show();
      if ($.trim(data) === 'no') {
        $('#homeError').html('No default dashboard for this user').show();
      } else {
        $('#homeError').hide();
        var url = data;
        $('#dashName').html(data.name);
        document.getElementById('Iframe').src = data.url;
      }
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      $('#absoLoader').hide();
      $('#Iframe').show();
    },
  });
}

// function getDashboardList(that, event) {
//   event.stopImmediatePropagation();
// }

function reloadview(id) {
  $('#homeError').hide();
  $('#dashId').val(id);

  $('#absoLoader').show();
  $('#Iframe').hide();

  var window = $('#currentwindow').val();
  if ($.trim(window) === 'kibanaConfig') {
    $.each($('li.sidebar-dashboard-items'), function () {
      if ($(this).hasClass('active')) {
        $(this).removeClass('active');
      }
    });

    var targetList = $('li.sidebar-dashboard-items[data-idx=' + id + ']');
    if (!targetList.hasClass('active')) {
      targetList.addClass('active');
    }
    loadLandingpage();
  } else {
    sessionStorage.setItem('dashId', id);
    location.href = base;
  }
}

function checkTimeSpan(value) {
  switch (value) {
    case 'Last 15 min':
      reloadDashboard('15min');
      break;
    case 'Last 1 hour':
      reloadDashboard('60min');
      break;
    case 'Today':
      reloadDashboard('1day');
      break;
    case 'This Week':
      reloadDashboard('7day');
      break;
    case 'Last 30 days':
      reloadDashboard('30day');
      break;
    case 'Last 90 days':
      reloadDashboard('3mnth');
      break;
    case 'Last 1 year':
      reloadDashboard('1yr');
      break;
    case 'Last 3 year':
      reloadDashboard('3yr');
      break;
    case 'Last 5 year':
      reloadDashboard('5yr');
      break;
    default:
      break;
  }
}

function reloadDashboard(num) {
  $('#absoLoader').show();
  $('#timediv').hide();
  $('#dashdiv').hide();
  $('#Iframe').hide();
  var level = $('#searchType').val();
  var val = $('#searchValue').val();
  var kid = $('#dashId').val();
  var st = num;
  //      console.log(kid);
  if (kid == '') {
    kid = sessionStorage.getItem('dashId');
  }

  /*$.ajax({
        type: 'POST',
        url: "../dashboard/homeFunction.php?function=makecurlLoginCall()",
        success: function(data) {*/

  $.ajax({
    type: 'GET',
    url: 'homeFunction.php',
    data: {
      function: 'loadHomePage',
      kid: kid,
      lev: level,
      value: val,
      st: st,
      csrfMagicToken: csrfMagicToken,
    },
    dataType: 'json',
    success: function (data) {
      closePopUp();
      $('#absoLoader').hide();
      $('#timediv').show();
      $('#dashdiv').show();
      $('#Iframe').show();
      if ($.trim(data) === 'no') {
        $('#homeError').html('No default dashboard for this user').show();
      } else {
        $('#homeError').hide();
        var url = data;
        $('#dashName').html(data.name);
        var selectedVal = data.SelectedVal;
        $('#selectedFilter').val(selectedVal);
        //                $('#selectedFilter').selectpicker('val',data.SelectedVal);
        document.getElementById('Iframe').src = data.url;
      }
    },
    error: function (XMLHttpRequest, textStatus, errorThrown) {
      $('#absoLoader').hide();
      $('#Iframe').show();
    },
  });

  //     }
  //    });
}
