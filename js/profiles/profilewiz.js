/**
 * Profile Wizard module JS History
 * ---------------------------------
 * DATE         WHO     WHAT
 * ==========================
 * 05-Jul-19    JHN     Initial implementation.
 *
 */

$(document).ready(function () {
  dispVal = 1;
  tileCount = 0;
  isSkipped = false;
  // get_ProfileWizardDetails();
  $('#profilename1').css('color', '#2b2b2b');
  get_ProfileWizardDetails(1, '');

  //addNewProfile();

  $('input[name="profattch"]').click(function () {
    var selRes = $(this).val();
    if (selRes === 'sites') {
      $('.group-div').hide();
      $('.site-div').show();
    } else {
      $('.site-div').hide();
      $('.group-div').show();
    }
  });

  $('#ck-uck-all input:checkbox').on('click', function () {
    var checkboxes = $('#accordion .card input[type=checkbox]');

    if ($(this).is(':checked')) {
      $.each(checkboxes, function () {
        $(this).prop('checked', true);
      });
    } else {
      $.each(checkboxes, function () {
        $(this).prop('checked', false);
      });
    }
  });

  $('.os-ck-bx').on('click', function () {
    var hand = $(this);
    var parentRow = $(this).parents('.row[data-group-name=OS]');
    var allChecked = parentRow.find('input.os-ck-bx:checked'),
      checked;
    var osArray = [];
    var selectBox = hand.parents('.Box.title-grid').find('select.dart-select[data-label=Dart]'),
      i,
      selectBoxNth,
      selectHtm = '<option value="" selected="selected">Select Dart</option>';

    var getOSName = function (indentifier) {
      var os;

      switch (indentifier) {
        case 'os-win':
          os = 'windows';
          break;
        case 'os-android':
          os = 'android';
          break;
        case 'os-mac':
          os = 'mac';
          break;
        case 'os-ios':
          os = 'ios';
          break;
        case 'os-linux':
          os = 'linux';
          break;
        case 'os-ubuntu':
          os = 'ubuntu';
          break;
      }

      return os;
    };

    for (i = 0; i < allChecked.length; i++) {
      checked = allChecked.eq(i);
      if (getOSName(checked.attr('data-name')) != undefined) osArray.push(getOSName(checked.attr('data-name')));
    }

    if (osArray.length > 0) {
      $.ajax({
        url: '../lib/l-ajax.php',
        type: 'POST',
        data: { function: 'getDartsByPlatform', platform: osArray, csrfMagicToken: csrfMagicToken },
        dataType: 'JSON',
        success: function (data) {
          data = data.data;
          for (i = 0; i < data.length; i++) {
            selectHtm += '<option value="' + data[i] + '">Dart ' + data[i] + '</option>';
          }
          for (i = 0; i < selectBox.length; i++) {
            selectBoxNth = selectBox.eq(i);
            selectBoxNth.html(selectHtm);
          }
        },
        error: function () {},
      });
    }
  });
  //addNewProfile();
});

function chkUnchkAll(ref) {
  var checkboxes = $('.clientProfileList .card input[type=checkbox]');
  if ($(ref).is(':checked')) {
    $.each(checkboxes, function () {
      $(this).prop('checked', true);
    });
  } else {
    $.each(checkboxes, function () {
      $(this).prop('checked', false);
    });
  }
}

function checkBoxUpdate(ref, pmid) {
  var checkVal = $(ref).val();
  if ($(ref).prop('checked') === true) {
    $('#collapse' + checkVal + ' input[type="checkbox"]').each(function () {
      $(this).prop('checked', true);
    });
  } else {
    $('#collapse' + checkVal + ' input[type="checkbox"]').each(function () {
      $(this).prop('checked', false);
    });
  }

  if (pmid != '') {
    $('input[type=checkbox]').each(function () {
      if ($(this).val() == pmid) {
        $(this).prop('checked', true);
      }
    });
  }
}

function checkBoxUpdateClient(ref, pmid) {
  var checkVal = $(ref).val();
  if ($(ref).prop('checked') === true) {
    $('.clientProfileList #collapse' + checkVal + ' input[type="checkbox"]').each(function () {
      $(this).prop('checked', true);
    });
  } else {
    $('.clientProfileList #collapse' + checkVal + ' input[type="checkbox"]').each(function () {
      $(this).prop('checked', false);
    });
  }

  if (pmid != '') {
    $('input[type=checkbox]').each(function () {
      if ($(this).val() == pmid) {
        $(this).prop('checked', true);
      }
    });
  }
}

$('body').on('click', '.page-link', function () {
  var nextPage = $(this).data('pgno');
  notifName = $(this).data('name');
  const activeElement = window.currentActiveSortElement;
  const key = activeElement ? activeElement.sort : '';
  const sort = activeElement ? activeElement.type : '';
  get_ProfileWizardDetails(nextPage, '', key, sort);
});
$('body').on('change', '#notifyDtl_lengthSel', function () {
  get_ProfileWizardDetails(1, '');
});

function get_ProfileWizardDetails(nextPage = 1, notifSearch = '', key = '', sort = '') {
  $('#loader').show();

  notifSearch = $('#notifSearch').val();

  if (typeof notifSearch === 'undefined') {
    notifSearch = '';
  }

  checkAndUpdateActiveSortElement(key, sort);

  var dat = {
    function: 'get_ProfileWizardDetails',
    csrfMagicToken: csrfMagicToken,
    limitCount: $('#notifyDtl_length :selected').val(),
    nextPage: nextPage,
    notifSearch: notifSearch,
    order: key,
    sort: sort,
  };
  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    dataType: 'json',
    data: dat,
    success: function (gridData) {
      $('#profileWizardGrid').DataTable().destroy();
      $('#profileWizardGrid tbody').empty();
      profileWizard = $('#profileWizardGrid').DataTable({
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
          $('#largeDataPagination').html(gridData.largeDataPaginationHtml);
        },
      });
      $('.dataTables_filter input').addClass('form-control');
      $('.tableloader').hide();
    },
  });

  $('#profileWizardGrid').on('click', 'tr', function () {
    profileWizard.$('tr.selected').removeClass('selected');
    $(this).addClass('selected');
    var rowID = profileWizard.row(this).data();
    if (rowID != 'undefined' && rowID !== undefined && selid != '') {
      $('#selected').val(rowID[6]);
    }
  });

  $('#profileWizardGrid').on('dblclick', 'tr', function () {
    var selid = $('#selected').val();
    if (selid != 'undefined' && selid !== undefined && selid != '') {
      editProfile();
    }
    // location.href = 'pw-view.php?id=' + selid;
  });
}

function viewProfiles() {
  var selid = $('#selected').val();
  if (selid == '') {
    $.notify('Please select a record');
  } else {
    location.href = 'pw-view.php?id=' + selid;
  }
}

function confirmCancelOperation() {
  sweetAlert({
    title: 'Are you sure that you want to cancel adding profile?',
    text: 'You wont be able to recover the profile details once cancelled',
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#050d30',
    cancelButtonColor: '#fa0f4b',
    cancelButtonText: 'No, continue adding!',
    confirmButtonText: 'Yes, cancel it!',
  })
    .then(function (result) {
      $('#profwiz-basic').show();
      $('#profwiz-add').hide();
      $('#forProfiles').show();
      $('#notifyDtl_filter').show();
    })
    .catch(function () {
      $('.closebtn').trigger('click');
    });
}

function addNewProfile() {
  $('#forProfiles').hide();
  $('#notifyDtl_filter').hide();
  $('#profwiz-basic').hide();
  $('#profwiz-add').show();
  //createDummyToken();
}
var selid;
function editProfile() {
  selid = $('#selected').val();
  if (selid == '' || typeof selid == 'undefined') {
    $.notify('Please select a record to edit');
    return false;
  } else {
    $('#forProfiles').hide();
    $('#notifyDtl_filter').hide();
    validateProfileAccess(selid, 'edit');
  }
}

function validateProfileAccess(prof_id, act_type) {
  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: {
      function: 'check_ProfileAccess',
      profid: prof_id,
      csrfMagicToken: csrfMagicToken,
    },
    success: function (data) {
      if (data == 'ok') {
        if (act_type == 'edit') {
          location.href = 'pw-edit.php?id=' + prof_id;
        } else if (act_type == 'delete') {
          sweetAlert({
            title: 'Are you sure that you want to continue?',
            text: 'You wont be able to recover the profile once deleted',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#050d30',
            cancelButtonColor: '#fa0f4b',
            cancelButtonText: 'No, cancel it!',
            confirmButtonText: 'Yes, delete it!',
          })
            .then(function (result) {
              $.ajax({
                url: '../lib/l-profilewiz.php',
                type: 'POST',
                data: { function: 'delete_Profile', profileid: prof_id, csrfMagicToken: csrfMagicToken },
                success: function (data) {
                  var res = JSON.parse(data);
                  if (res.status === 'success') {
                    $.notify(res.msg);
                    setTimeout(function () {
                      get_ProfileWizardDetails();
                    }, 500);
                  } else if (res.status === 'failed') {
                    $.notify(res.msg);
                    return false;
                  } else {
                    $.notify('Failed to delete the profile. Please try again');
                    return false;
                  }
                },
                error: function (err) {
                  $.notify('Some error occurred. Please try again.');
                },
              });
            })
            .catch(function () {
              $('.closebtn').trigger('click');
            });
        }
      } else {
        $.notify("You don't have the permission to " + act_type.substr(0, 1).toUpperCase() + act_type.substr(1) + ' this Profile!');
        return false;
      }
    },
    error: function (err) {},
  });
}

//cj start

var previousButton = $('#prevProfBtn');
var previousButtonBasic = $('#prevProfBtnBasic');
var nextButton = $('#nextProfBtn');
var nextButtonBasic = $('#nextProfBtnBasic');
var leftLinks = $('.addBox.cbl');
var activeClassName = 'activeNow';
var currentActiveWrap = $('.' + activeClassName);
var pWrapClassName = 'eachPWrap';
var wraps = $('.' + pWrapClassName);
var leftLinkClickableClassName = 'cbl';
var leftLinkWraps = $('#dispMenu .addBox');
var leftLinkActiveClassName = 'active';
var skipButton = $('#skipButton');
var saveButton = $('#saveProfBtn');
var switchButton = $('#switchPreview');
var addDartHandClassName = 'w2-add-dart-box';
var rmvDartHandClassName = 'w2-rmv-dart-box';
var addDartHand = $('.' + addDartHandClassName);
var titleGridClassName = 'title-grid';
var dartBoxClassName = 'each-dart-box';
var dartBoxAttrName = 'tile-darts';
var dartBoxTextAttrName = 'tile-dart-name';
var dartSelectClassName = 'dart-select';
var dartBoxTextClassName = 'dart-input-title';
var w3DartElementsGridClassName = 'w3-dart-elements';
var w4DartElementsGridClassName = 'w4-dart-elements';

$(document).ready(function () {
  window.nextButton.on('click', function () {
    pWrapNextEvent();
  });

  window.previousButton.on('click', function () {
    pWrapPreviousEvent();
  });

  $('.review-tiles').css({ 'font-size': '12px', 'font-weight': 'normal', 'margin-top': '-20px' });
  /*window.switchButton.on('click', function () {
        switchReview();
    });*/

  $(document).on('click', '.cbl', function () {
    showWrapByIndex($(this).attr('data-disp'));
  });

  $('#addNewTiles').on('click', function () {
    addNewTitleGrid();
  });

  $('#skipButton').on('click', function () {
    resetWrapActiveClass(5);
    resetleftLinkActiveClass(5);

    $('#profile2 input[type=text]').each(function () {
      $(this).val('');
    });
    $('.dart-select').prop('selectedIndex', 0);

    window.skipButton.hide();
    window.nextButton.hide();
    window.saveButton.show();
    //window.switchButton.show().html('Review Client');
    //$('#reviewdata').val('client');
    isSkipped = true;
    renderProfileConfiguration();
  });

  $(document).on('click', '.' + window.addDartHandClassName, function (event) {
    addNewDartBox($(this));
  });

  $(document).on('click', '.' + window.rmvDartHandClassName, function (event) {
    removeDartBox($(this));
  });

  $(document).on('click', '.mycollapse', function (event) {
    var idx = $(this).parents('.eachPWrap').find('.mycollapse').index(this);
    var targetContainer = $(this).parents('.eachPWrap').find('.my-collapse-content').eq(idx);

    if (targetContainer.is(':visible')) {
      $(this).removeClass('active');
      targetContainer.slideUp();
    } else {
      $(this).addClass('active');
      targetContainer.slideDown();
    }

    event.stopImmediatePropagation();
  });

  $(document).on('click', '.open-dart-console', function (event) {
    openDartConsole($(this), $(this).attr('data-dartid'), $(this).attr('data-dart-indx'), $(this).attr('data-dart-seqn'));
  });

  $(document).on('keyup', 'input[data-required=true]', function (event) {
    keyUpValidation($(this), event);
  });

  $(document).on('change', 'select[data-required=true]', function (event) {
    keyUpValidation($(this), event);
  });
});

function keyUpValidation(element, event) {
  var allPWraps = window.wraps;
  var eventPWrap = element.parents('.' + pWrapClassName);
  var currentIndex = allPWraps.index(eventPWrap);

  var isValidated = topValidation(eventPWrap);

  if (!isValidated) {
    for (var i = currentIndex + 1; i < window.leftLinkWraps.length; i++) {
      if (window.leftLinkWraps.eq(i).hasClass(window.leftLinkClickableClassName)) {
        window.leftLinkWraps.eq(i).removeClass(window.leftLinkClickableClassName);
      }
    }
  }

  return true;
}

function submitProfileData(form, event) {
  if (event.preventDefault) {
    event.preventDefault();
  } else {
    event.returnValue = false;
  }

  var validationStatus = topValidation(form, false);
}

function getDartName(dartId) {
  $('#dcs-title').html('');
  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: {
      function: 'getDartName',
      dartId: dartId,
      csrfMagicToken: csrfMagicToken,
    },
    dataType: 'json',
    success: function (data) {
      $('#dcs-title').html('Dart ' + dartId + '-' + data.dartName);
    },
    error: function (err) {
      console.log('Error=> ' + err.toLocaleString());
    },
  });
}

function openDartConsole(slider, dartId, dartIndx, dartSeqn) {
  getDartName(dartId);
  rightContainerSlideOn('dart-role');
  var rightSlider = new RightSlider('#dart-role');
  rightSlider.showLoader();
  $('#consoleWrapper').html('');
  $.ajax({
    url:
      '../profileJSONSchema/jsonschema.php?dartno=' +
      dartId +
      '&dartindx=' +
      dartIndx +
      '&dartseqn=' +
      dartSeqn +
      '&csrfMagicToken=' +
      csrfMagicToken,
    type: 'GET',
    dataType: 'text',
    async: true,
    success: function (data) {
      $('#consoleWrapper').html(data);
      setTimeout(() => {
        rightSlider.hideLoader();
      }, 500);
    },
    error: function (data) {
      rightSlider.hideLoader();
      errorNotify('Something went wrong, retry later');
    },
  });

  return true;
}

function addNewDartBox(hand) {
  var clickedIndex;
  var titleGrids = $('.' + window.titleGridClassName);
  var idx = titleGrids.index(hand.parents('.' + window.titleGridClassName));
  var dartBox = hand
      .parents('.' + window.titleGridClassName)
      .find('.' + window.dartBoxClassName)
      .eq(0),
    dartBoxClone = dartBox.clone();

  var dartBoxLength = hand.parents('.' + window.titleGridClassName).find('.' + window.dartBoxClassName).length;
  var lastDartBox = hand
    .parents('.' + window.titleGridClassName)
    .find('.' + window.dartBoxClassName)
    .eq(dartBoxLength - 1);
  var newdartBox = '<div class="row each-dart-box">' + dartBoxClone.html() + '</div>';
  lastDartBox.after(newdartBox);

  var titleGridsLength = hand.parents('.' + window.titleGridClassName).find('.' + window.dartBoxClassName).length;
  var newDartGrid = hand
    .parents('.' + window.titleGridClassName)
    .find('.' + window.dartBoxClassName)
    .eq(titleGridsLength - 1);
  newDartGrid.find('select').attr('name', window.dartBoxAttrName + '[' + idx + '][]');
  newDartGrid.find('.rmv-dart-box').show();

  return true;
}

function removeDartBox(dartref) {
  dartref.parents('.each-dart-box').remove();
}

function addNewTitleGrid() {
  var maxGrids = 10;
  var titleGrid = $('.title-grid').eq(0),
    titleGridClone = titleGrid.clone(),
    totalGrid = $('.title-grid').length;

  //titleGridClone.find('.tgr-title').text('#' + (totalGrid + 1) + ' Name of the Title');
  titleGridClone.find('.bx-cnt-spn').text('#' + (totalGrid + 1));
  titleGridClone.find('input.title-visibiity').attr('name', 'visibility[' + totalGrid + ']');
  var osBoxes = titleGridClone.find('.os-ck-bx');

  for (var f = 0; f < osBoxes.length; f++) {
    osBoxes.eq(f).attr('name', osBoxes.eq(f).attr('data-name') + '[' + totalGrid + ']');
  }

  var lastTitleGrid = $('.title-grid').eq(totalGrid - 1),
    removeGrid = $('.remove-title-grid').eq(0),
    appendHtml =
      '<div class="Box title-grid" id="tileData' +
      totalGrid +
      '">' +
      titleGridClone.html() +
      '<div class="row remove remove-title-grid">' +
      removeGrid.clone().html() +
      '</div></div>';

  if (totalGrid == maxGrids) {
    errorNotify('You can add maximum 10 containers');
    return false;
  }

  lastTitleGrid.after(appendHtml);

  totalGrid = $('.title-grid').length;

  var newGrid = $('.title-grid').eq(totalGrid - 1),
    newGridDartGrids = newGrid.find('.' + window.dartBoxClassName);

  for (var i = 1; i < newGridDartGrids.length; i++) {
    newGridDartGrids.eq(i).remove();
  }

  newGridDartGrids
    .eq(0)
    .find('select')
    .attr('name', window.dartBoxAttrName + '[' + (totalGrid - 1) + '][]');
  newGridDartGrids
    .eq(0)
    .find('input')
    .attr('name', window.dartBoxTextAttrName + '[' + (totalGrid - 1) + '][]');

  return true;
}

function removeTitleGrid(element, event) {
  element.parents('.title-grid').remove();
  var grid_cnt = 1;
  $('.bx-cnt-spn').each(function () {
    $(this).text('#' + grid_cnt);
    grid_cnt++;
  });
  return true;
}

function showWrapByIndex(idx) {
  var sidx = idx;
  idx = !isNaN(idx) ? parseInt(idx) : false;
  var previousButton = window.previousButton;
  var nextButton = window.nextButton;
  var wraps = window.wraps;

  if (idx) {
    idx--;
    resetWrapActiveClass(idx);
    resetleftLinkActiveClass(idx);

    previousButton.show();
    nextButton.show();
    window.saveButton.hide();
    window.switchButton.hide();

    if (idx === 2) {
      window.skipButton.show();
    } else {
      window.skipButton.hide();
    }

    if (idx <= 0) {
      previousButton.hide();
      nextButton.show();
    }

    if (idx >= wraps.length - 1) {
      nextButton.hide();
      window.saveButton.show();
      window.switchButton.show();
    }
  }

  if (!isNaN(idx) && parseInt(idx) == 0) {
    $('#profile1 #accordion.profileList,#nextProfBtnBasic').show(); //bg#34019,34017
    //$('#nextProfBtn').hide();  //bg#34019,34017
  }

  switch (sidx) {
    case '2':
      $('#nextProfBtnBasic, #prevProfBtnBasic').hide();
      break;
    default:
      break;
  }
  return true;
}

function resetWrapActiveClass(activeIndex) {
  var wraps = window.wraps;
  var activeClassName = window.activeClassName;

  $.each(wraps, function (i, d) {
    $(this).removeClass(activeClassName).hide();
  });

  wraps.eq(activeIndex).addClass(activeClassName).show();

  return true;
}

function resetleftLinkActiveClass(activeIndex) {
  var leftLinkWraps = window.leftLinkWraps;
  var leftLinkActiveClassName = window.leftLinkActiveClassName;

  $.each(leftLinkWraps, function (i, d) {
    $(this).removeClass(leftLinkActiveClassName);
  });

  leftLinkWraps.eq(activeIndex).addClass(leftLinkActiveClassName);

  return true;
}

function pWrapNextEvent() {
  var wraps = window.wraps;
  var activeClassName = window.activeClassName;
  var currentActiveWrap = window.currentActiveWrap;
  var activeIndex;
  var leftLinkWraps = window.leftLinkWraps;
  var leftLinkActiveClassName = window.leftLinkActiveClassName;
  var previousButton = window.previousButton;
  var nextButton = window.nextButton;

  for (var k = 0; k < wraps.length; k++) {
    if (wraps.eq(k).hasClass(activeClassName)) {
      activeIndex = k;
      break;
    }
  }

  var validated = false;

  switch (parseInt(k)) {
    case 0:
      validated = validateBasicDetails();
      break;
    case 1:
      validated = true;
      break;
    case 2:
      validated = validateTileDiv();
      break;
    case 3:
      validated = true;
      break;
    case 4:
      validated = validateSequence();
      break;
  }

  if (validated) {
    var continueFlow = true;

    switch (parseInt(k)) {
      case 0:
        continueFlow = updateProfileConfigured();
        break;
      case 1:
        continueFlow = updateClientProfileConfigured();
        break;
      case 2:
        continueFlow = populateConfigureDartWrap();
        break;
      case 3:
        continueFlow = populateSequenceWrap();
        break;
      case 4:
        continueFlow = populateReviewWrap();
        break;
    }

    if (continueFlow) {
      activeIndex++;

      if (activeIndex == 3) {
        isSkipped = false;
      }

      resetWrapActiveClass(activeIndex);
      resetleftLinkActiveClass(activeIndex);

      for (var j = 0; j < activeIndex; j++) {
        if (!leftLinkWraps.eq(activeIndex).hasClass(window.leftLinkClickableClassName)) {
          leftLinkWraps.eq(activeIndex).addClass(window.leftLinkClickableClassName);
        }
      }

      previousButton.show();
      window.skipButton.hide();
      window.saveButton.hide();
      window.switchButton.hide();

      if (activeIndex >= wraps.length - 1) {
        nextButton.hide();
      }

      if (activeIndex == 2) {
        window.skipButton.show();
      }

      if (activeIndex == wraps.length - 1) {
        window.saveButton.show();
        //window.switchButton.show().html('Review Client');
        //$('#reviewdata').val('client');
      }
    }
  }

  return true;
}

function populateReviewWrap() {
  renderProfileConfiguration();

  var fifthWrap = window.wraps.eq(4);
  var firstWrap = window.wraps.eq(0);

  return true;
}

function populateSequenceWrap() {
  var w4Data = fetchW1SelectedTileAndDart(),
    gridFourTile = w4Data.tiles,
    dartSelectArray = w4Data.darts,
    dartBoxDescArray = w4Data.boxdesc;

  var fourthWrap = window.wraps.eq(4);
  var dartElementsCloneGrid = fourthWrap.find('.' + window.w4DartElementsGridClassName + '-clone'),
    dartElementsGrid = $('.' + window.w4DartElementsGridClassName),
    dartElementsGridLength = dartElementsGrid.length,
    dartElementsGridClone = dartElementsCloneGrid.clone(),
    dartElementsNewGridHtml,
    afterGrid,
    j,
    w4Dart1nClassName = 'w4-dart-1n-wrap',
    w4j1,
    w4Dart1nClone,
    w4Dart1nHtm,
    w4Dart2nClassName = 'w4-dart-2n-wrap',
    w4j2,
    w4Dart2nClone,
    w4Dart2nHtm,
    inc,
    sfn = 'dart-sequence';

  //clear previous populated grids
  if (dartElementsGrid != undefined && dartElementsGrid.length > 0) {
    $.each(dartElementsGrid, function () {
      $(this).remove();
    });
  }

  //populate new grids
  if (gridFourTile.length > 0) {
    for (var i = 0; i < gridFourTile.length; i++) {
      dartElementsGrid = $('.' + window.w4DartElementsGridClassName);
      dartElementsGridLength = dartElementsGrid.length;
      afterGrid = i == 0 ? dartElementsCloneGrid : dartElementsGrid.eq(dartElementsGridLength - 1);
      dartElementsGridClone.find('button').html(gridFourTile[i]);

      if (dartSelectArray.length > 0 && dartSelectArray[i] != undefined && dartSelectArray[i].length > 0) {
        w4Dart1nHtm = w4Dart2nHtm = '';
        w4j1 = dartElementsGridClone.find('.' + w4Dart1nClassName + ' span');
        w4j2 = dartElementsGridClone.find('.' + w4Dart2nClassName + ' span');
        w4Dart1nClone = w4j1.eq(1);
        w4Dart2nClone = w4j2.eq(1);

        //remove previous
        for (j = 1; j < w4j1.length; j++) {
          w4j1.eq(j).remove();
        }
        //remove previous
        for (j = 1; j < w4j2.length; j++) {
          w4j2.eq(j).remove();
        }
        //create new
        inc = 0;
        for (j = 0; j < dartSelectArray[i].length; j++) {
          inc++;
          w4Dart1nClone.find('p').text('Dart ' + dartSelectArray[i][j] + ' - ' + dartBoxDescArray[i][j]);
          w4Dart1nHtm += '<span>' + w4Dart1nClone.html() + '</span>';
          w4Dart2nClone
            .find('input')
            .attr('value', inc)
            .attr('name', sfn + '[' + i + '][]');
          w4Dart2nHtm += '<span>' + w4Dart2nClone.html() + '</span>';
        }

        w4j1.eq(0).after(w4Dart1nHtm);
        w4j2.eq(0).after(w4Dart2nHtm);
      }

      dartElementsNewGridHtml = '<div class="' + window.w4DartElementsGridClassName + '">' + dartElementsGridClone.html() + '</div>';

      afterGrid.after(dartElementsNewGridHtml);
    }
  }

  return true;
}

function populateConfigureDartWrap() {
  var w1Data = fetchW1SelectedTileAndDart(),
    gridThreeTitle = w1Data.tiles,
    dartSelectArray = w1Data.darts,
    dartBoxDescArray = w1Data.boxdesc;

  var thirdWrap = window.wraps.eq(3);
  var dartElementsCloneGrid = thirdWrap.find('.' + window.w3DartElementsGridClassName + '-clone'),
    dartElementsGrid = $('.' + window.w3DartElementsGridClassName),
    dartElementsGridLength = dartElementsGrid.length,
    dartElementsGridClone = dartElementsCloneGrid.clone(),
    dartElementsNewGridHtml,
    afterGrid,
    j,
    dartLink,
    dartLinkHtml;

  //clear previous populated grids
  if (dartElementsGrid != undefined && dartElementsGrid.length > 0) {
    $.each(dartElementsGrid, function () {
      $(this).remove();
    });
  }

  //populate new grids
  if (gridThreeTitle.length > 0) {
    for (var i = 0; i < gridThreeTitle.length; i++) {
      dartElementsGrid = $('.' + window.w3DartElementsGridClassName);
      dartElementsGridLength = dartElementsGrid.length;
      afterGrid = i == 0 ? dartElementsCloneGrid : dartElementsGrid.eq(dartElementsGridLength - 1);
      dartElementsGridClone.find('button').html(gridThreeTitle[i]);
      dartElementsNewGridHtml = '<div class="' + window.w3DartElementsGridClassName + '">' + dartElementsGridClone.html() + '</div>';
      afterGrid.after(dartElementsNewGridHtml);
    }
  }

  //populate dart elements
  dartElementsGrid = $('.' + window.w3DartElementsGridClassName);
  var dartLinkClone;

  for (i = 0; i < dartElementsGrid.length; i++) {
    dartLink = dartElementsGrid.eq(i).find('.my-collapse-content');
    dartLinkHtml = '';
    if (dartSelectArray.length > 0 && dartSelectArray[i] != undefined && dartSelectArray[i].length > 0) {
      for (j = 0; j < dartSelectArray[i].length; j++) {
        dartLinkClone = dartLink.clone();
        dartLinkClone.find('span .w3-dart-title').text('Dart ' + dartSelectArray[i][j] + ' - ' + dartBoxDescArray[i][j]);
        dartLinkClone.find('i.open-dart-console').attr('data-dartid', dartSelectArray[i][j]);
        dartLinkClone.find('i.open-dart-console').attr('data-dart-indx', i);
        dartLinkClone.find('i.open-dart-console').attr('data-dart-seqn', j);
        dartLinkHtml += dartLinkClone.html();
      }
    }
    dartLink.html(dartLinkHtml);
  }

  return true;
}

function fetchW1SelectedTileAndDart() {
  var titleGrids = $('.' + window.titleGridClassName);
  var gridThreeTitle = [],
    titleValue,
    dartSelects,
    dartBoxDesc,
    dartSelectEachArray,
    dartBoxDescEachArray,
    dartSelectArray = [],
    dartBoxDescArray = [];

  for (var i = 0; i < titleGrids.length; i++) {
    titleValue = titleGrids.eq(i).find('.w1-name').val();
    if (titleValue != '') {
      gridThreeTitle.push(titleValue);
    }

    dartSelects = titleGrids.eq(i).find('.' + window.dartSelectClassName);
    dartSelectEachArray = [];
    dartBoxDesc = titleGrids.eq(i).find('.' + window.dartBoxTextClassName);
    dartBoxDescEachArray = [];

    for (var j = 0; j < dartSelects.length; j++) {
      if (dartSelects.eq(j).val() != '') {
        dartSelectEachArray.push(dartSelects.eq(j).val());
      }
    }

    for (var j = 0; j < dartBoxDesc.length; j++) {
      if (dartBoxDesc.eq(j).val() != '') {
        dartBoxDescEachArray.push(dartBoxDesc.eq(j).val());
      }
    }

    dartSelectArray.push(dartSelectEachArray);
    dartBoxDescArray.push(dartBoxDescEachArray);
  }

  return { tiles: gridThreeTitle, darts: dartSelectArray, boxdesc: dartBoxDescArray };
}

function validateBasicDetails() {
  return topValidation($('#profile1'), true);
}

function validateTileDiv() {
  return topValidation($('#profile3'), true);
}

function validateSequence() {
  return topValidation($('#profile5'), true);
}

function topValidation(target, interactive) {
  var wrapperRequireds = target.find('input[data-required=true],select[data-required=true]'),
    input;
  var isValidated = true,
    errorMsg;

  for (var i = 0; i < wrapperRequireds.length; i++) {
    var regex = /^[a-zA-Z0-9_\s]+$/g;
    input = wrapperRequireds.eq(i);
    if (input.val() == undefined || input.val() == '') {
      if (interactive) {
        errorMsg = input.attr('data-label') != undefined ? 'The ' + input.attr('data-label') + ' field is required' : 'This field is required';
        // errorNotify(errorMsg);
        input.attr('placeholder', errorMsg);
        input.focus();
        input.css('border-block-color', 'red');
        input.css('background-color', 'antiquewhite');
      }

      isValidated = false;
      break;
    } else if (!regex.test(input.val())) {
      if (interactive) {
        errorMsg = 'Special characters are not allowed except for underscore ( _ )';
        errorNotify(errorMsg);
        input.focus();
      }
      isValidated = false;
      break;
    }
  }

  if (isValidated) {
    var anyRequiredsParent = target.find('div[data-required-any-one-parent=this]'),
      anyRequireds,
      hasValueCount;

    for (var z = 0; z < anyRequiredsParent.length; z++) {
      anyRequireds = anyRequiredsParent.eq(z).find('input[data-required-any-one=true],select[data-required-any-one=true]');
      hasValueCount = 0;

      for (var zTh = 0; zTh < anyRequireds.length; zTh++) {
        input = anyRequireds.eq(zTh);
        if ('checkbox' == input.attr('type') && input.is(':checked') && input.val() != '') {
          hasValueCount++;
        } else if ('checkbox' != input.attr('type') && input.val() != undefined && input.val() != '') {
          hasValueCount++;
        }
      }

      if (0 == hasValueCount) {
        if (interactive) {
          errorMsg =
            anyRequiredsParent.eq(z).attr('data-group-name') != undefined
              ? 'Atleast one ' + anyRequiredsParent.eq(z).attr('data-group-name') + ' field is required'
              : 'At least one field is required';
          errorNotify(errorMsg);
          anyRequireds.eq(0).focus();
        }

        isValidated = false;
        break;
      }
    }
  }

  return isValidated;
}

function pWrapPreviousEvent() {
  var wraps = window.wraps;
  var activeClassName = window.activeClassName;
  var currentActiveWrap = window.currentActiveWrap;
  var activeIndex;
  var leftLinkWraps = window.leftLinkWraps;
  var leftLinkActiveClassName = window.leftLinkActiveClassName;
  var previousButton = window.previousButton;
  var nextButton = window.nextButton;

  for (var k = 0; k < wraps.length; k++) {
    if (wraps.eq(k).hasClass(activeClassName)) {
      activeIndex = k;
      break;
    }
  }

  activeIndex--;
  if (isSkipped) {
    activeIndex = 2;
    isSkipped = false;
    for (var i = activeIndex + 1; i < window.leftLinkWraps.length; i++) {
      if (window.leftLinkWraps.eq(i).hasClass(window.leftLinkClickableClassName)) {
        window.leftLinkWraps.eq(i).removeClass(window.leftLinkClickableClassName);
      }
    }
  }
  resetWrapActiveClass(activeIndex);
  resetleftLinkActiveClass(activeIndex);

  for (var j = 0; j < activeIndex; j++) {
    if (!leftLinkWraps.eq(activeIndex).hasClass(window.leftLinkClickableClassName)) {
      leftLinkWraps.eq(activeIndex).addClass(window.leftLinkClickableClassName);
    }
  }

  nextButton.show();
  window.skipButton.hide();
  window.saveButton.hide();
  window.switchButton.hide();

  if (activeIndex <= 0) {
    previousButton.hide();
    nextButton.show();
    previousButtonBasic.show();
    $('.clientProfileList').show();
  }

  if (activeIndex == 2) {
    window.skipButton.show();
  }

  return true;
}

//cj end

function updateProfileConfigured() {
  var profArr = [];
  var dashProfCnt = 0;
  var profName = $('input[name=profile-name]').val();
  $('.profileList input[type=checkbox]').each(function () {
    if ($(this).prop('checked') == true) {
      profArr.push($(this).val());
      dashProfCnt++;
    }
  });

  if (dashProfCnt === 0) {
    $.notify('Dashboard Profile list cannot be empty!');
    return false;
  }

  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: {
      function: 'update_ProfileData',
      pname: profName,
      pdata: profArr,
      csrfMagicToken: csrfMagicToken,
    },
    success: function (data) {
      renderClientProfileTiles(data);
    },
    error: function (err) {
      console.log('Error=> ' + err.toLocaleString());
    },
  });

  return true;
}

function updateClientProfileConfigured() {
  var cliProfArr = [];
  var cliProfCnt = 0;
  $('.clientProfileList input[type=checkbox]').each(function () {
    if ($(this).is(':checked')) {
      cliProfArr.push($(this).val());
      cliProfCnt++;
    }
  });

  if (cliProfCnt === 0) {
    $.notify('Client Profile list cannot be empty!');
    return false;
  }

  //$('#prevProfBtnBasic, .clientProfileList').hide();
  $('#prevProfBtn').show();

  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: {
      function: 'update_ClientProfileData',
      cliprofdata: cliProfArr,
      csrfMagicToken: csrfMagicToken,
    },
    success: function (data) {
      console.log('updateClientProfileData : ' + $.trim(data));
    },
    error: function (err) {
      console.log('Error=> ' + err.toLocaleString());
    },
  });

  return true;
}

/*function profileDartSubmit() {
 var dartFormData = $('form.brutusin-form').serialize();
 }*/

function renderProfileConfiguration() {
  $('#render-dash').removeClass('btn-simple').addClass('btn-alert');
  $('#render-clnt').removeClass('btn-alert').addClass('btn-simple');
  var formdata = $('form').serialize();
  formdata += '&function=render_ProfileDetails' + '&csrfMagicToken=' + csrfMagicToken;
  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: formdata,
    dataType: 'json',
    success: function (data) {
      $('#levelOneData').html(data.datalist);
      renderLevelTwoTiles(data.startmid);
    },
    error: function (err) {},
  });
}

function renderClientProfileConfiguration() {
  $('#render-clnt').removeClass('btn-simple').addClass('btn-alert');
  $('#render-dash').removeClass('btn-alert').addClass('btn-simple');
  var formdata = $('form').serialize();
  formdata += '&function=render_ClientProfileDetails' + '&csrfMagicToken=' + csrfMagicToken;
  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: formdata,
    dataType: 'json',
    success: function (data) {
      $('#levelOneData').html(data.datalist);
      renderLevelTwoTiles(data.startmid, 'cli');
    },
    error: function (err) {},
  });
}

function renderLevelTwoTiles(pmid, type = '') {
  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: {
      function: 'render_LevelTwoProfile',
      mid: pmid,
      showtype: type,
      csrfMagicToken: csrfMagicToken,
    },
    success: function (data) {
      var res = JSON.parse(data);
      $('#tile-header').html(res.heading);
      $('#tile-description').html(res.description);
      $('#child-lvl').html(res.datalist);
    },
    error: function (err) {},
  });
}

function saveProfileDetails() {
  var formdata = $('form').serialize();
  formdata += '&function=save_ProfileDetails' + '&csrfMagicToken=' + csrfMagicToken;
  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: formdata,
    success: function (data) {
      var res = JSON.parse(data);
      $.notify(res.msg);
      setTimeout(function () {
        location.reload();
      }, 2000);
    },
    error: function (err) {},
  });
}

function updateVariablesData(finalSubValues, dartno, dartindx, dartseqn) {
  var dartTileToken = $('#dartTileToken').val();
  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: {
      function: 'update_VariablesData',
      vdata: finalSubValues,
      dartno: dartno,
      dartindx: dartindx,
      dartseqn: dartseqn,
      dartToken: dartTileToken,
      csrfMagicToken: csrfMagicToken,
    },
    success: function (data) {
      rightContainerSlideClose('dart-role');
    },
    error: function (err) {},
  });
}

function attachProfile() {
  var selVal = $('#selected').val();
  if (selVal === '' || selVal === undefined) {
    $.notify('Please select a profile to attach');
    return;
  } else {
    viewSelectedData(selVal);
  }
}

function viewSelectedData(selVal) {
  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: {
      function: 'viewSelectedData',
      selVal: selVal,
      csrfMagicToken: csrfMagicToken,
    },
    dataType: 'json',
    success: function (data) {
      console.log(data);
      console.log(data.sites);
      rightContainerSlideOn('attach-profile');
      $('#siteList').html(data.sites);
      $('.selectpicker').selectpicker('refresh');
      $('#groupList').html(data.groups);
      $('.selectpicker').selectpicker('refresh');
    },
    error: function (err) {
      console.log(err);
    },
  });
}

function attachProfileTo() {
  var selType = $('input[name="profattch"]:checked').val();

  if (selType == undefined) {
    errorNotify('Please select an option to attach profile!');
    return false;
  }

  var selValu = $('#selected').val();
  var typeVal = '';
  var dispVal = '';
  if (selType === 'sites') {
    typeVal = $('#siteList').val();
    dispVal = 'site(s)';
  } else {
    typeVal = $('#groupList').val();
    dispVal = 'group(s)';
  }

  if (typeVal == undefined || typeVal == '' || typeVal.length == 0) {
    errorNotify('Please select a Site/Group');
    return false;
  } else {
    validateProfileAttachStatus(selType, typeVal, selValu, dispVal);
  }
}

function validateProfileAttachStatus(selType, typeVal, prof_id, dispVal) {
  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: {
      function: 'check_ProfileAttachStatus',
      profid: prof_id,
      attchType: selType,
      attchVal: typeVal,
      csrfMagicToken: csrfMagicToken,
    },
    success: function (data) {
      rightContainerSlideClose('attach-profile');
      if (data == 'notify') {
        sweetAlert({
          title: 'Are you sure want to continue?',
          text: 'One or more Sites/Groups was attached with this Profile! This will override your existing profile configuration with current one!',
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#050d30',
          cancelButtonColor: '#fa0f4b',
          cancelButtonText: 'No, Cancel it!',
          confirmButtonText: 'Yes, Attach it!',
        })
          .then(function (result) {
            actionAttachProfile(selType, typeVal, prof_id, dispVal);
          })
          .catch(function () {
            $('.closebtn').trigger('click');
          });
      } else {
        actionAttachProfile(selType, typeVal, prof_id, dispVal);
      }
    },
    error: function (err) {},
  });
}

function actionAttachProfile(selType, typeVal, prof_id, dispVal) {
  $('#loader').show();
  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: {
      function: 'attach_ProfileData',
      type: selType,
      typeval: typeVal,
      profval: prof_id,
      csrfMagicToken: csrfMagicToken,
    },
    success: function (data) {
      var res = JSON.parse(data);
      if (res.status === 'success') {
        $('.loader').hide();
        $.notify('Profile has been attached with the ' + dispVal + ' successfully.');
        setTimeout(function () {
          //rightContainerSlideClose('attach-profile');
          get_ProfileWizardDetails();
        }, 500);
      } else {
        $('.loader').hide();
        $.notify('Failed to attach profile with ' + dispVal + '!<br/>Please try again.');
      }
    },
    error: function (err) {
      $.notify('Some error occurred. Please try again.');
    },
  });
}

/* Update Profile */

function renderClientProfileTiles(data) {
  var clientData = JSON.parse(data);
  var clientProfStr = '';
  var dummyval = '';
  var level_2 = [];
  var level_3 = [];

  for (var i = 0; i < clientData.length; i++) {
    if (clientData[i]['Enable/Disable'] == 3) {
      if (clientData[i]['type'] == 'L2') {
        level_2.push(clientData[i]);
      } else if (clientData[i]['type'] == 'L3') {
        level_3.push(clientData[i]);
      }
    }
  }

  for (var l2 = 0; l2 < level_2.length; l2++) {
    clientProfStr += '<div class="card">';
    clientProfStr += '<div class="card-header" id="headingOneClient">';
    clientProfStr += '<div class="form-check">';

    clientProfStr +=
      '<label class="form-check-label">\n\
                            <input class="form-check-input" checked="" type="checkbox" name="global-cli" value="' +
      level_2[l2]['mid'] +
      '" onclick="checkBoxUpdateClient(this, \'' +
      dummyval +
      '\');">\n\
                            <span class="form-check-sign"></span>\n\
                            <h5 class="mb-0 d-inline">\n\
                                <button class="collapsible btn-link mycollapse">' +
      level_2[l2]['profile'] +
      '</button>\n\
                            </h5>\n\
                          </label>';
    clientProfStr += '</div>';
    clientProfStr += '</div>';

    clientProfStr +=
      '<div id="collapse' +
      level_2[l2]['mid'] +
      '" class="collapse my-collapse-content" aria-labelledby="headingOneClient" data-parent="#accordion">';
    clientProfStr += '<div class="card-body child" id="child' + level_2[l2]['mid'] + '">';

    for (var l3 = 0; l3 < level_3.length; l3++) {
      if (level_2[l2]['parentId'] == level_3[l3]['page']) {
        clientProfStr += '<div class="card">';
        clientProfStr += '<div class="form-check">';
        clientProfStr += '<label class="form-check-label">';

        clientProfStr +=
          '<input class="form-check-input" checked="" type="checkbox" name="global-cli" value="' +
          level_3[l3]['mid'] +
          '" onclick="checkBoxUpdateClient(this, \'' +
          level_3[l3]['parentId'] +
          '\');">';
        clientProfStr += '<span class="form-check-sign"></span>';
        clientProfStr +=
          '<div class="card-header"><span class="form-check-sign"></span>' + level_3[l3]['profile'] + ' [' + level_3[l3]['OS'] + ']</div>';
        clientProfStr += '</label>';
        clientProfStr += '</div>';
        clientProfStr += '</div>';
      }
    }
    clientProfStr += '</div>';
    clientProfStr += '</div>';
    clientProfStr += '</div>';
  }

  $('.clientProfileList').html('<h5>Select the tiles that you want to be part of this Profile for <b>Client</b></h5>');
  $('.clientProfileList').append(
    '<div id="ck-uck-all-cli" class="form-check mt-3">' +
      '<label class="form-check-label">' +
      '<input class="form-check-input" type="checkbox" checked="checked" onclick="chkUnchkAll(this)">' +
      '<span class="form-check-sign"></span>' +
      '<p>Check / Uncheck All</p>' +
      '</label>' +
      '</div>',
  );

  //clientData[0]['children'].forEach(clientLevelOne);

  $('.clientProfileList').append(clientProfStr);

  //$('.profileList').hide();
  $('#nextProfBtnBasic').hide();
  $('.clientProfileList, #prevProfBtnBasic, #nextProfBtn').show();
  $('#profile1').animate({ scrollTop: 0 });

  return true;
}

function clientLevelOne(item, index) {
  var dummyval = '';
  clientProfStr += '<div class="card">';
  clientProfStr += '<div class="card-header" id="headingOneClient">';
  clientProfStr += '<div class="form-check">';

  if (Array.isArray(item['children']) && item['children'].length > 0) {
    clientProfStr +=
      '<label class="form-check-label">\n\
                            <input class="form-check-input" checked="" type="checkbox" name="global-cli" value="' +
      item['mid'] +
      '" onclick="checkBoxUpdateClient(this, \'' +
      dummyval +
      '\');">\n\
                            <span class="form-check-sign"></span>\n\
                            <h5 class="mb-0 d-inline">\n\
                                <button class="collapsible btn-link mycollapse">' +
      item['name'] +
      '</button>\n\
                            </h5>\n\
                          </label>';
  } else {
    clientProfStr +=
      '<label class="form-check-label">\n\
                            <input class="form-check-input" checked="" type="checkbox" name="global-cli" value="' +
      item['mid'] +
      '" onclick="checkBoxUpdateClient(this, \'' +
      dummyval +
      '\');">\n\
                            <span class="form-check-sign"></span>\n\
                            <div class="card-header" style="font-size: 12px;">\n\
                                <a class="btn-link" href="javascript:void(0);">' +
      item['name'] +
      '</a>\n\
                            </div>\n\
                          </label>';
  }
  clientProfStr += '</div>';
  clientProfStr += '</div>';

  if (Array.isArray(item['children']) && item['children'].length > 0) {
    clientProfStr +=
      '<div id="collapse' + item['mid'] + '" class="collapse my-collapse-content" aria-labelledby="headingOneClient" data-parent="#accordion">';
    clientProfStr += '<div class="card-body child" id="child' + item['mid'] + '">';
    item['children'].forEach(clientLevelTwo);
    clientProfStr += '</div>';
    clientProfStr += '</div>';
  }

  clientProfStr += '</div>';
}

function clientLevelTwo(item, index, levOneItem) {
  clientProfStr += '<div class="card">';
  clientProfStr += '<div class="form-check">';
  clientProfStr += '<label class="form-check-label">';

  clientProfStr +=
    '<input class="form-check-input" checked="" type="checkbox" name="global-cli" value="' +
    item['mid'] +
    '" onclick="checkBoxUpdateClient(this, \'' +
    item['parentid'] +
    '\');">';
  clientProfStr += '<span class="form-check-sign"></span>';

  if (Array.isArray(item['children']) && item['children'].length > 0) {
    clientProfStr +=
      '<div class="card-header"><a href="#" class="collapsible mycollapse" style="color: #000;">' + item['name'] + ' [' + item['os'] + ']</a></div>';
  } else {
    clientProfStr += '<div class="card-header"><span class="form-check-sign"></span>' + item['name'] + ' [' + item['os'] + ']</div>';
  }

  clientProfStr += '</label>';

  if (Array.isArray(item['children']) && item['children'].length > 0) {
    clientProfStr += '<div id="collapse' + item['mid'] + '" class="card-body collapse my-collapse-content">';
    item['children'].forEach(clientLevelThree);
    clientProfStr += '</div>';
  }

  clientProfStr += '</div>';
  clientProfStr += '</div>';
}

function clientLevelThree(item, index) {
  clientProfStr += '<label class="form-check-label">';
  clientProfStr +=
    '<input class="form-check-input" checked="" type="checkbox" name="global-cli" id="global-cli" value="' +
    item['mid'] +
    '" onclick="checkBoxUpdateClient(this, ' +
    item['parentid'] +
    ');">';
  clientProfStr += '<span class="form-check-sign"></span> ' + item['name'];
  clientProfStr += '</label>';
}

/*$("#nextProfBtnBasic").click(function () {
    updateProfileConfigured();
});

$('#prevProfBtnBasic').click(function () {
    $('.clientProfileList').html('');
    $('.clientProfileList, #prevProfBtnBasic, #nextProfBtn').hide();
    $('.profileList, #nextProfBtnBasic').show();
});*/

function switchReview() {
  var reviewval = $('#reviewdata').val();
  if (reviewval === 'client') {
    $('#switchPreview').html('Review Dashboard');
    $('#reviewdata').val('dash');
    renderClientProfileConfiguration();
  } else {
    $('#switchPreview').html('Review Client');
    $('#reviewdata').val('client');
    renderProfileConfiguration();
  }
}

/* Duplicate Profile */
function duplicateProfile() {
  var selVal = $('#selected').val();
  if (selVal === '' || selVal === undefined) {
    $.notify('Please select a profile to duplicate.');
    return;
  } else {
    rightContainerSlideOn('duplicate-profile');
  }
}

function duplicateProfileSave() {
  var duplicate_profile_name = $('#duplicate-profile-name').val();
  var sel_profile_id = $('#selected').val();
  var regex = /^[a-zA-Z0-9_\s]+$/g;
  if (duplicate_profile_name == '') {
    $.notify('Please enter a profile name');
    return false;
  } else if (!regex.test(duplicate_profile_name)) {
    $.notify('Special characters are not allowed except for underscore ( _ )');
    return false;
  } else {
    $.ajax({
      url: '../lib/l-profilewiz.php',
      type: 'POST',
      data: {
        function: 'duplicate_Profile',
        selprofid: sel_profile_id,
        dprofname: duplicate_profile_name,
        csrfMagicToken: csrfMagicToken,
      },
      success: function (data) {
        var res = JSON.parse(data);
        if (res.status === 'success') {
          $.notify(res.msg);
          setTimeout(function () {
            location.reload();
          }, 2000);
        } else {
          $.notify(res.msg);
          return false;
        }
      },
      error: function (err) {
        $.notify('Some error occurred. Please try again.');
      },
    });
  }
}

/* Delete Profile */

function deleteProfile() {
  var sel_profid = $('#selected').val();
  if (sel_profid === '' || sel_profid === undefined) {
    $.notify('Please select a profile to delete');
    return;
  } else {
    validateProfileAccess(sel_profid, 'delete');
  }
}
