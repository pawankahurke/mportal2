var smSelectedType = 'site';
var smSelectedType = 'site';
var isSelectionSet;

function RightMenu() {
  var target;

  this.search = function (target) {
    this.target = target;
    var domElement = target;
    var tx = domElement.attr('data-bs-target');

    if (tx != undefined) {
      var s = domElement.val();

      if (s == undefined || s == '') {
        this.resetMenu();
        return false;
      }

      var r = new RegExp(s, 'i');
      var d = $('#' + tx);
      var c = d.find('li:not(li div ul li)'),
        a,
        t,
        ith,
        j,
        it,
        ath,
        ithFlag,
        acol,
        topFlag;

      for (var i = 0; i < c.length; i++) {
        t = c.eq(i);
        a = t.find('a p').text();
        if (r.test(a)) {
          topFlag = true;
          t.show();
        } else {
          topFlag = false;
          t.hide();
        }

        ith = t.find('div ul li');
        if (ith.length > 0) {
          ithFlag = false;
          for (var j = 0; j < ith.length; j++) {
            it = ith.eq(j);
            ath = it.find('a span').text();
            if (r.test(ath)) {
              ithFlag = true;
              it.show();
            } else {
              if (!topFlag) {
                it.hide();
              }
            }
          }

          acol = t.find('a[data-toggle=collapse]');

          if (ithFlag) {
            if (acol.hasClass('collapsed')) {
              acol.removeClass('collapsed');
              $(acol.attr('href')).addClass('show');
            }
            t.show();
          } else {
            acol.addClass('collapsed');
            $(acol.attr('href')).removeClass('show');
          }
        }
      }
    }
  };

  this.resetMenu = function () {
    var domElement = this.target;
    var tx = domElement.attr('data-bs-target');
    var t, a, c, r, tD, ith;

    if (tx != undefined) {
      var d = $('#' + tx);
      var c = d.find('li:not(li div ul li)');

      for (var i = 0; i < c.length; i++) {
        t = c.eq(i);
        a = t.find('a[data-toggle=collapse]');
        tD = $(a.attr('href'));

        if (!a.hasClass('collapsed')) {
          a.addClass('collapsed');
        }

        if (tD.hasClass('show')) {
          tD.removeClass('show');
        }

        t.show();

        ith = t.find('div ul li');
        if (ith.length > 0) {
          for (var j = 0; j < ith.length; j++) {
            ith.eq(j).show();
          }
        }
      }
    }
  };
}
var rightMEnu = false;
$(document).ready(function () {
  var page = $('#pageName').html();
  if (page != 'Groups' && page != 'Software Distribution' && page != 'Visualisation Weights' && page != 'Profiles') {
    rightMenuFunctionality();
  }

  // if(page == 'Home Page'){
  //     $("#searchValue").val('All');
  // }

  $('.nhl-htm-search-box').on('keyup', function () {
    /*var rm = new RightMenu;
         rm.search($(this));
         return true;*/
    var searchVal = $(this).val();
    var searchType = $('#searchType').val();
    if (searchVal == '') {
      setTimeout(function () {
        $('#mainul' + searchType).html('');
        rightMenuFunctionality();
      }, 200);
    } else {
      searchMachines(searchVal, searchType);
    }
  });

  $('#sm-search-show').on('click', function () {
    var targetBox;
    var targetContainer;

    if (window.smSelectedType != undefined || window.smSelectedType != '') {
      if (window.smSelectedType == 'site') {
        targetBox = $('input.nhl-htm-search-box[data-bs-target=mainulSites]').parent('.rm-search-parent');
        targetContainer = $('#mainulSites');
      } else if (window.smSelectedType == 'group') {
        targetBox = $('input.nhl-htm-search-box[data-bs-target=mainulGroups]').parent('.rm-search-parent');
        targetContainer = $('#mainulGroups');
      }

      if (!targetBox.is(':visible')) {
        targetBox.fadeIn();
        targetBox.find('input').select().focus();
        targetContainer.addClass('rm-adjust-height');
      } else {
        targetBox.fadeOut();
        targetContainer.removeClass('rm-adjust-height');
      }
    }
  });
});

function searchMachines(searchVal, searchType) {
  if (searchVal.length > 2) {
    var reqdata = {
      function: 'AJAX_SearchMachineDetail',
      searchText: searchVal,
      csrfMagicToken: csrfMagicToken,
    };
    $.ajax({
      url: '../lib/l-ajax.php',
      type: 'POST',
      data: reqdata,
      success: function (data) {
        if (searchType == 'ServiceTag') {
          if (window.smSelectedType == 'site') {
            $('#mainulSites').html(data).css({ height: '100%' });
          } else {
            $('#mainulGroups').html(data).css({ height: '100%' });
          }
        } else {
          $('#mainul' + searchType)
            .html(data)
            .css({ height: '100%' });
        }
      },
      error: function (err) {
        console.log('Error Searching Machine : ' + err.toString());
      },
    });
  } else {
    var reqdata = {
      function: 'AJAX_SearchMachineDetail',
      searchText: '',
      csrfMagicToken: csrfMagicToken,
    };
    $.ajax({
      url: '../lib/l-ajax.php',
      type: 'POST',
      data: reqdata,
      success: function (data) {
        $('#mainul' + searchType)
          .html(data)
          .css({ height: '100%' });
      },
      error: function (err) {
        console.log('Error Searching Machine : ' + err.toString());
      },
    });
  }
}

function rightMenuFunctionality() {
  var PageVal = $('#pageName').html();
  rightMEnu = true;
  $('#loader').show();
  $.ajax({
    url: '../lib/l-ajax.php',
    type: 'POST',
    data: { function: 'AJAX_Get_RightPane', csrfMagicToken: csrfMagicToken, page: PageVal },
    dataType: 'json',
    success: function (data) {
      // $(".loader").hide();
      $('#homeError').show();
      $('#mainulGroups').html('');
      $('#mainulSites').html('');
      var viewsdata = data['views'];
      var userType = data['userType'];
      var elementLabel = '';
      if (viewsdata['Groups'] != '' || viewsdata['Sites'] != '') {
        if (viewsdata['Groups'] == '') {
          $('#groups').html('No Groups Available');
        } else if (viewsdata['Sites'] == '') {
          $('#sites').html('No Sites Available');
        }
        var i = 1;
        for (var x in viewsdata) {
          var j = 1;
          var parentdata = viewsdata[x];
          for (var y in parentdata) {
            //This is done for reusing same site name for different customer, we differentiate by using customer number
            if (y.indexOf('__') != -1) {
              arr = y.split('__');
              elementLabel = arr[0];
            } else {
              elementLabel = y;
            }
            elementLabel = $.trim(elementLabel);
            render_parent(i, x, j, y, parentdata[y]['id'], elementLabel, PageVal);
            var machinedata = parentdata[y]['machines'];
            j++;
          }
          i++;
        }
        li_id != undefined ? li_id.classList.add('activeclass') : '';
      } else {
        $('.site').html('No Sites');
        $('#save_rightMenu').hide();
        $('#groups').html('No Groups Available');
        $('#save_rightMenu').hide();
        $('#sites').html('No Sites Available');
      }
    },
    error: function (err) {
      console.log(err);
    },
  });
}

var li_id;

function render_parent(container, containertype, element, elementname, elementgtId, elementLabel, PageVal) {
  var trimmedElementLabel = '';
  var ele_gateway = elementgtId.split('@@@');
  var elementId = ele_gateway[0];
  var ele = elementId.split('_' + containertype);
  // if(PageVal == 'Home Page'){
  //     var search = $("#searchValue").val('All');
  // }else{
  var search = $('#searchValue').val();
  // }

  if (element === 1 && containertype === 'Sites') {
    if (search === 'All') {
      $('#searchType').val(containertype);
      $('#searchValue').val(elementname);
      $('#rparentName').val(elementname);
      $('#passlevel').val(containertype);
      $('#searchLabel').val(elementLabel);
    } else {
      var searchType = $('#searchType').val();
      var searchVal = $('#searchValue').val();
      var rparent = $('#rparentName').val();
      var passlevel = $('#passlevel').val();
      var searchlabel = $('#searchLabel').val();
      $('#searchType').val(searchType);
      $('#searchValue').val(searchVal);
      $('#rparentName').val(rparent);
      $('#passlevel').val(passlevel);
      $('#searchLabel').val(searchlabel);
    }
    searchType = $('#searchType').val();
    elementname = $('#searchValue').val();
    rparent = $('#rparentName').val();

    var currentwindow = $('#currentwindow').val();
    var selectionData;
    var postData = {
      function: 'AJAX_Update_Session',
      searchType: searchType,
      searchValue: elementname,
      rparentName: rparent,
      csrfMagicToken: csrfMagicToken,
    };
    $.ajax({
      url: '../lib/l-ajax.php',
      type: 'POST',
      data: postData,
      success: function () {
        $('.loader').hide();
        if (searchType == '') {
          $('.site').html('No Selection');
        } else {
          if (searchType === 'Groups') {
            var arr = rparent.split('__');
            var name = arr[0];
            var trmname = '';
            if (name.length > 20) {
              trmname = name.substring(0, 18) + '..';
            } else {
              trmname = name;
            }
            $('.site').html('Group - ' + elementname);
            $('.site').attr('title', name);
          } else {
            var arr = elementname.split('__');
            var name = arr[0];
            var trmname = '';
            if (name.length > 20) {
              trmname = name.substring(0, 18) + '..';
            } else {
              trmname = name;
            }
            if (searchType == 'Sites') {
              selectionData = 'Site';
            } else {
              selectionData = 'Machine';
            }
            $('.site').html(selectionData + ' - ' + trmname);
            $('.site').attr('title', name);
          }
        }
        if (currentwindow === 'softwaredistribution') {
          $('#communicationSearch').html(elementname);
          $('#valueSearch').val(elementname);
        } else if (currentwindow === 'troubleshooting') {
          $('#communicationSearch').html(elementname);
          $('#valueSearch').val(elementname);
          // loadRD();
        } else if (currentwindow === 'home') {
          if (elementname !== '' && typeof loadLandingpage !== 'undefined') {
            loadLandingpage();
          }
        } else if (currentwindow === 'device') {
          // changeSiteGroup();
          // get_deviceDetails();
        } else if (currentwindow === 'notification') {
          // notification_datatable();
        } else if (currentwindow === 'autoupdate') {
          softwareupdategridlist();
        } else if (currentwindow === 'visualisation' || currentwindow === 'service') {
          rightContainerSlideClose('rsc-add-container3');
        }
      },
    });
  }
  if (elementLabel.length > 20) {
    trimmedElementLabel = elementLabel.substring(0, 18) + '..';
  } else {
    trimmedElementLabel = elementLabel;
  }
  var href = containertype + '' + element;
  var html = '';
  var value = containertype + '##' + ele[0] + '##' + elementLabel + '##' + containertype;
  var page = $('#pageName').html();
  if (page === 'Census') {
    hideMachineClick = true;
    html =
      '<li>' +
      '<a class="ranchor" id="' +
      href +
      '" title="' +
      trimmedElementLabel +
      '" onclick="setSiteVal(\'' +
      value +
      "'," +
      href +
      ')">' +
      '<p class="rselect" id="' +
      ele[0] +
      '" title="' +
      elementLabel +
      '" >' +
      trimmedElementLabel +
      '<span class = "float-right mr-4" id= "' +
      ele[0] +
      'p" style = "display:none">Loading..</span>' +
      '</p>' +
      '</a>' +
      '<div class="showValues' +
      href +
      ' hideAll"  style="display:none">' +
      '</div>' +
      '</li>';
    // html = '<li>' +
    //     '<a data-toggle="collapse" class="collapsed" href="#' + href + '" title="' + trimmedElementLabel + '">  ' +
    //     '<p class="activeclass" id="' + ele[0] + '" title="' + elementLabel + '" onclick="getMachines(\'' + ele[0] + '\',\'' + containertype + '\',this.className, \'' + href + '\',\'' + value + '\', \'' + hideMachineClick + '\')">' + trimmedElementLabel + '</p>' +
    //     '</a>' +
    //     '<div class="collapse" id="' + href + '">' +
    //     '</div>' +
    //     '</li>';
  } else {
    hideMachineClick = false;
    // html = '<li>' +
    //     '<a data-toggle="collapse" class="collapsed" href="#' + href + '" title="' + trimmedElementLabel + '">  ' +
    //     '<p class="activeclass" id="' + ele[0] + '" title="' + elementLabel + '" onclick="getMachines(\'' + ele[0] + '\',\'' + containertype + '\',this.className, \'' + href + '\',\'' + value + '\', \'' + hideMachineClick + '\')">' + trimmedElementLabel + '<b class="caret"></b></p>' +
    //     '</a>' +
    //     '<div class="collapse" id="' + href + '">' +
    //     '</div>' +
    //     '</li>';
    html =
      '<li>' +
      '<a class="ranchor" id="' +
      href +
      '" title="' +
      trimmedElementLabel +
      '" onclick="setSiteVal(\'' +
      value +
      "'," +
      href +
      ')">' +
      '<p class="rselect" id="' +
      ele[0] +
      '" title="' +
      elementLabel +
      '" >' +
      trimmedElementLabel +
      '<span class = "float-right mr-4" id= "' +
      ele[0] +
      'p" style = "display:none">Loading..</span>' +
      '<i class="tim-icons iconPlus rmenu' +
      href +
      ' icon-simple-add" style="color: #000;font-size: 0.75rem;font-weight: bold;" onclick="getMachines(this,\'' +
      ele[0] +
      "','" +
      containertype +
      "',this.className, '" +
      href +
      "','" +
      value +
      "', '" +
      hideMachineClick +
      '\')"></i></p>' +
      '</a>' +
      '<div class="showValues' +
      href +
      ' hideAll"  style="display:none">' +
      '</div>' +
      '</li>';
    console.log(li_id);
    li_id != undefined ? li_id.classList.add('activeclass') : '';
  }

  if ($('#mainul' + containertype).is(':empty')) {
    $('#mainul' + containertype).html('');
    $('#mainul' + containertype).html(html);
  } else {
    $('#mainul' + containertype).append(html);
  }
}

function setSiteVal(value, id) {
  li_id = id;
  // console.log(id);
  $('.ranchor').removeClass('activeclass');
  id.classList.add('activeclass');

  // $('.rselect').addClass('activeclass');
  window.isSelectionSet = true;
  $('#selectedtag').val(value);
}

function getMachines(x, selectedSiteName, type, elementClass, elementHref, value, hideMachineClick) {
  // $('.ranchor').removeClass('activeclass');
  // $('.rselect').removeClass('activeclass');
  // $( "li."+elementHref).prev().css( "background-color", "red" );
  // $(".showValues"+elementHref).prev().css( "display", "none" );
  // $(".remu"+elementHref).prev().addClass('icon-simple-delete');
  window.isSelectionSet = true;
  $('#selectedtag').val(value);
  var lastClick = selectedSiteName + '#' + type + '#' + elementClass + '#' + elementHref;
  $('#lastmacClick').val(lastClick);
  var a = selectedSiteName + 'p';
  $('#' + a).show();
  $('.hideAll').hide();
  $('.iconPlus').removeClass('icon-simple-delete');
  $('.iconPlus').addClass('icon-simple-add');
  appendElement = elementHref;
  if (elementClass.includes('icon-simple-add') == true) {
    // $('.ranchor').addClass('activeclass');
    // $('.rselect').addClass('activeclass');
    $('.rmenu' + elementHref).addClass('icon-simple-delete');
    $('.rmenu' + elementHref).removeClass('icon-simple-add');
    if (hideMachineClick === 'false') {
      if (type === 'Sites') {
        getSitesMachines(selectedSiteName, type, elementClass, elementHref);
      } else if (type === 'Groups') {
        getGroupsMachines(selectedSiteName, type, elementClass, elementHref);
      }
    }
  } else {
    // $('.ranchor').removeClass('activeclass');
    $('.rselect').removeClass('activeclass');
    $('.rmenu' + elementHref).removeClass('icon-simple-delete');
    $('.rmenu' + elementHref).addClass('icon-simple-add');
    $('#' + a).hide();
    $('.showValues' + elementHref).hide();
  }
}

function getSitesMachines(selectedSiteName, type, elementClass, elementHref) {
  var searchType = $('#searchType').val();
  var searchValue = $('#searchValue').val();
  var classname = '';
  $('.showValues').hide();
  var a = selectedSiteName + 'p';

  if (elementClass.includes('icon-simple-add') == true) {
    $('.showValues' + elementHref).show();
    var machineLimit = $('#lastmacLimit').val();
    if (machineLimit === '') {
      machineLimit = 100;
    } else if ($('#lastSelectId').val() !== selectedSiteName) {
      machineLimit = 100;
    } else {
      machineLimit = parseInt(machineLimit) + 100;
    }
    $('#lastmacLimit').val(machineLimit);
    machineLimit = 0;

    if (searchType === 'ServiceTag') {
      classname = 'active';
    } else {
      classname = '';
    }

    if (machineLimit == 100) {
      $('#' + elementHref).html('<img src="../vendors/images/ajax-login.gif" class="loadhome" alt="loading..." />');
      $('#lastSelectId').val(selectedSiteName);
    }

    var postData = {
      function: 'AJAX_Get_SelectedSitesMachines',
      parentName: selectedSiteName,
      type: type,
      limit: machineLimit,
      csrfMagicToken: csrfMagicToken,
    };
    $.ajax({
      url: '../lib/l-ajax.php',
      type: 'POST',
      dataType: 'json',
      data: postData,
      success: function (viewsdata) {
        $('#' + a).hide();
        if (viewsdata != '') {
          if (machineLimit == 100) {
            $('.showValues' + elementHref)
              .html(viewsdata)
              .css({ height: '100%' });
            // $("#" + elementHref).html(viewsdata).css({'height': '100%'});
          } else {
            $('.showValues' + elementHref)
              .html(viewsdata)
              .css({ height: '100%' });
            // $("#" + elementHref).html(viewsdata).css({'height': '100%'});
          }
          scrollVal = 0;
        } else {
          $('.loadhome').hide();
        }
      },
      error: function (err) {},
    });
  } else {
    $('.showValues' + elementHref).hide();
    $('#' + elementHref).html(' ');
    $('#lastmacLimit').val('');
  }
}

function getGroupsMachines(parentName, type, elementClass, elementHref, machineLimit) {
  var searchType = $('#searchType').val();
  var searchValue = $('#searchValue').val();
  var classname = '';
  $('.showValues').hide();
  var a = parentName + 'p';

  if (elementClass.includes('icon-simple-add') == true) {
    $('.showValues' + elementHref).show();
    var machineLimit = $('#lastmacLimit').val();
    if (machineLimit === '') {
      machineLimit = 100;
    } else if ($('#lastSelectId').val() !== parentName) {
      machineLimit = 100;
    } else {
      machineLimit = parseInt(machineLimit) + 100;
    }
    $('#lastmacLimit').val(machineLimit);
    machineLimit = 0;

    if (searchType === 'ServiceTag') {
      classname = 'active';
    } else {
      classname = '';
    }

    if (machineLimit == 100) {
      $('#lastSelectId').val(parentName);
      $('#' + elementHref).html('<img src="../vendors/images/ajax-login.gif" class="loadhome" alt="loading..." />');
    }

    var postData = {
      function: 'AJAX_Get_SelectedSitesMachines',
      parentName: parentName,
      type: type,
      limit: machineLimit,
      csrfMagicToken: csrfMagicToken,
    };

    $.ajax({
      url: '../lib/l-ajax.php',
      type: 'POST',
      dataType: 'json',
      data: postData,
      success: function (viewsdata) {
        $('#' + a).hide();
        if (viewsdata != '') {
          if (machineLimit == 100) {
            $('.showValues' + elementHref)
              .html(viewsdata)
              .css({ height: '100%' });
            // $("#" + elementHref).html(viewsdata).css({'height': '100%'});
          } else if ($.trim(viewsdata.msg) === 'Permission denied') {
            // $.notify("You dont have the permission to the groups");
            $('.showValues' + elementHref)
              .html('You dont have the permission to the groups')
              .css({ height: '100%', 'margin-left': '23px', color: 'red' });
          } else {
            $('.showValues' + elementHref)
              .html(viewsdata)
              .css({ height: '100%' });
            // $("#" + elementHref).html(viewsdata).css({'height': '100%'});
          }
          scrollVal = 0;
        } else {
          $('.loadhome').hide();
        }
      },
      error: function (err) {
        console.log('errrooroorroroororororor');
      },
    });
  } else {
    $('.showValues' + elementHref).hide();
    $('#' + elementHref).html(' ');
    // $("#" + elementHref).html(' ');
    $('#lastmacLimit').val('');
  }
}

function machClick(val) {
  window.isSelectionSet = true;
  $('#selectedtag').val(val);
}

function saveSelectState() {
  if (window.isSelectionSet == undefined || !window.isSelectionSet) {
    $.notify('Please select a Site/Group/Machine');
    return false;
  }
  remove_selected_css();
  $('#loader').show();

  window.isSelectionSet = false;
  var val = $('#selectedtag').val(); // 'ServiceTag##IN-BLREPGN581##Capgemini_Testing##Sites##'
  var temp = val.split('##');
  var searchtype = temp[0];
  var searchval = temp[1];
  var rparentval = temp[2];
  var passlevel = temp[3];
  var censusid = temp[4];
  var currentwindow = $('#currentwindow').val();

  // if (searchtype == 'Groups' && Number.isInteger(parseInt(searchval))) {
  //   $.notify('Please select a group from this type');
  //   return false;
  // }

  $('#searchType').val(searchtype);
  $('#searchValue').val(searchval);
  $('#rparentName').val(rparentval);
  $('#passlevel').val(passlevel);
  $('#rcensusId').val(censusid);
  $('#searchLabel').val(rparentval);

  var selectionData;

  var postData = {
    function: 'AJAX_Update_Session',
    searchType: searchtype,
    searchValue: searchval,
    rparentName: rparentval,
    passlevel: passlevel,
    rcensusId: censusid,
    csrfMagicToken: csrfMagicToken,
  };

  $.ajax({
    url: '../lib/l-ajax.php',
    type: 'POST',
    data: postData,
    success: function (data) {
      $('.loader').hide();
      if (searchtype === 'Groups') {
        var arr = rparentval.split('__');
        var name = arr[0];
        var trmname = '';
        if (name.length > 20) {
          trmname = name.substring(0, 18) + '..';
        } else {
          trmname = name;
        }
        $('.site').html('Group - ' + searchval);
        $('.site').attr('title', name);
      } else {
        var arr = searchval.split('__');
        var name = arr[0];
        var trmname = '';
        if (name.length > 20) {
          trmname = name.substring(0, 18) + '..';
        } else {
          trmname = name;
        }
        if (searchtype == 'Sites') {
          selectionData = 'Site';
        } else {
          selectionData = 'Machine';
        }
        $('.site').html(selectionData + ' - ' + trmname);
        $('.site').attr('title', name);
      }

      if (currentwindow === 'softwaredistribution') {
        $('#communicationSearch').html(rparentval);
        $('#valueSearch').val(searchval);
      } else if (currentwindow === 'troubleshooting') {
        rightContainerSlideClose('rsc-add-container3');
        $('#communicationSearch').html(searchval);
        $('#valueSearch').val(searchval);
        loadRD();
        location.reload();
      } else if (currentwindow === 'home') {
        rightContainerSlideClose('rsc-add-container3');
        // loadLandingpage();
        location.reload();
      } else if (currentwindow === 'device') {
        rightContainerSlideClose('rsc-add-container3');
        //changeSiteGroup();
        //get_deviceDetails();
        location.reload();
      } else if (currentwindow === 'census') {
        rightContainerSlideClose('rsc-add-container3');
        get_deviceDetails(1, '');
      } else if (currentwindow === 'notification') {
        rightContainerSlideClose('rsc-add-container3');
        notification_datatable();
      } else if (currentwindow === 'patchmanagement') {
        rightContainerSlideClose('rsc-add-container3');
        // check237dartStatus(searchtype,searchval)
        mum_patchlistData();
      } else if (currentwindow === 'patchmanagement_new') {
        rightContainerSlideClose('rsc-add-container3');
        location.reload();
      } else if (currentwindow === 'autoupdate') {
        rightContainerSlideClose('rsc-add-container3');
        softwareupdategridlist();
      } else if (currentwindow === 'adpassword') {
        rightContainerSlideClose('rsc-add-container3');
      } else if (currentwindow === 'networkdeployment') {
        rightContainerSlideClose('rsc-add-container3');
        Get_DeploymentLeftDT();
      } else if (currentwindow === 'compliance') {
        rightContainerSlideClose('rsc-add-container3');
        compliance_datatable();
        // getComplianceFilters();
      } else if (currentwindow === 'visualisation' || currentwindow === 'service') {
        rightContainerSlideClose('rsc-add-container3');
      } else if (currentwindow === 'eventInfo') {
        rightContainerSlideClose('rsc-add-container3');
        //eventListAllLevel();
        loadInformationPortalData();
      } else if (currentwindow === 'configbrowser') {
        rightContainerSlideClose('rsc-add-container3');
        location.reload();
      } else if (currentwindow === 'messageAudit') {
        rightContainerSlideClose('rsc-add-container3');
        location.reload();
      } else if (currentwindow === 'softwaredistribution_audit') {
        location.reload();
      } else if (currentwindow === 'ticketingwizard') {
        location.reload();
      }
    },
  });

  var jsCallbackStr = $('input[name=jsCallback]').val();

  if (jsCallbackStr != undefined || jsCallbackStr != 'undefined' || jsCallbackStr != '') {
    switch (jsCallbackStr) {
      case 'reopenDistroSliderExport':
        reopenDistroSliderExport();
        break;
      case 'reopenDistroSlider':
        reopenDistroSlider();
        break;
      case 'reopen-cdn-ftp-conf-slider':
        reopenCdnFtpConfSlider();
        break;
    }
  }
}

$('#site_button').on('click', function () {
  $('#site_button').removeClass('btn-simple');
  $('#site_button').addClass('btn-round');
  $('#group_button').addClass('btn-simple');
  $('#group_button').removeClass('btn-round');
  $('#sites').show();
  $('#groups').hide();
  $('.Groups_Search').hide();
  $('.Sites_Search').show();
  window.smSelectedType = 'site';
});

$('#group_button').on('click', function () {
  $('#site_button').addClass('btn-simple');
  $('#site_button').removeClass('btn-round');
  $('#group_button').removeClass('btn-simple');
  $('#group_button').addClass('btn-round');
  $('#sites').hide();
  $('#groups').show();
  $('.Groups_Search').show();
  $('.Sites_Search').hide();
  window.smSelectedType = 'group';
});

function remove_selected_css() {
  var searchType = $('#searchType').val();
  var searchValue = $('#searchValue').val();
  var rparentName = $('#rparentName').val();
  //var passlevel = $("#passlevel").val();
  //alert("searchType--->" + searchType + "searchValue--->" + searchValue + "rparentName--->" + rparentName + "passlevel--->" + passlevel);
  if ((searchType === 'Sites' || searchType === 'Groups') && rparentName !== '') {
    $(`a[title="${rparentName}"]`).css('background-color', '');
  }
  if (searchType === 'ServiceTag' && rparentName !== '' && searchValue !== '') {
    $(`a[title="${rparentName}"]`).css('background-color', '');
    //$("#" + rparentName).trigger("click");
    //$('a[title=' + searchValue + ']').css("background-color", "");
  }
}

function loadMoreMachines(siteName, type, limit) {
  var postData = {
    function: 'AJAX_Get_SelectedSitesMachines',
    parentName: siteName,
    type: type,
    limit: limit,
    csrfMagicToken: csrfMagicToken,
  };
  $.ajax({
    url: '../lib/l-ajax.php',
    type: 'POST',
    dataType: 'json',
    data: postData,
    success: function (viewsdata) {
      $('.limit' + limit).hide();
      if (viewsdata != '') {
        $('.showValues' + appendElement).append(viewsdata);
        // $("#" + appendElement).append(viewsdata);
      } else {
        $('.loadhome').hide();
      }
    },
    error: function (err) {},
  });
}

function showRightMEnu() {
  rightMenuFunctionality();
  // alert('clicked');
}
