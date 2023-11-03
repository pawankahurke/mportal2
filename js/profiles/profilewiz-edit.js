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

    /*$('#profile2 input[type=text]').each(function () {
         $(this).val('');
         });
         $('.dart-select').prop('selectedIndex', 0);*/

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

function confirmCancelOperation() {
  sweetAlert({
    title: 'Are you sure that you want to cancel editing profile?',
    text: 'You wont be able to recover the profile details once cancelled',
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#050d30',
    cancelButtonColor: '#fa0f4b',
    cancelButtonText: 'No, continue editing!',
    confirmButtonText: 'Yes, cancel it!',
  })
    .then(function (result) {
      // location.reload();  debugger;
      location.href = 'index.php';
    })
    .catch(function () {
      $('.closebtn').trigger('click');
    });
}

function submitProfileData(form, event) {
  if (event.preventDefault) {
    event.preventDefault();
  } else {
    event.returnValue = false;
  }

  var validationStatus = topValidation(form, false);
}

function openDartConsole(slider, dartId, dartIndx, dartSeqn) {
  $('#dcs-title').html('Dart ' + dartId);
  rightContainerSlideOn('dart-role');
  var rightSlider = new RightSlider('#dart-role');
  rightSlider.showLoader();

  $.ajax({
    url: '../profileJSONSchema/jsonschema.php',
    type: 'GET',
    data: {
      dartno: dartId,
      dartindx: dartIndx,
      dartseqn: dartSeqn,
    },
    dataType: 'text',
    async: true,
    success: function (data) {
      $('#consoleWrapper').html(data);
      rightSlider.hideLoader();
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
  newDartGrid.find('select').prop('selectedIndex', 0);
  newDartGrid.find('input[type=text]').val('');

  return true;
}

function removeDartBox(dartref) {
  var parents = dartref.parents('.row.each-dart-box').parent().find('.row.each-dart-box');
  if (parents.length === 1) {
    return;
  }

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

  $('#tileData' + totalGrid + ' input[type=text]').each(function () {
    $(this).val('');
  });
  $('#tileData' + totalGrid + ' input[type=checkbox]').each(function () {
    $(this).removeAttr('checked');
  });
  $('#tileData' + totalGrid + ' .dart-select').prop('selectedIndex', 0);

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
  var parents = element.parents('.title-grid').parent().find('.title-grid');
  if (parents.length === 1) {
    return;
  }

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
        errorNotify(errorMsg);
        input.focus();
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
  $('.profileList input[type=checkbox]:checked').each(function () {
    profArr.push($(this).val());
    dashProfCnt++;
  });

  if (dashProfCnt === 0) {
    $.notify('Dashboard Profile list cannot be empty!');
    return false;
  }

  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: {
      function: 'e_update_ProfileData',
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
      function: 'e_update_ClientProfileData',
      cliprofdata: cliProfArr,
      csrfMagicToken: csrfMagicToken,
    },
    success: function (data) {
      //console.log('updateClientProfileData : ' + $.trim(data));
    },
    error: function (err) {
      console.log('Error=> ' + err.toLocaleString());
    },
  });

  return true;
}

function renderProfileConfiguration() {
  $('#render-dash').removeClass('btn-simple').addClass('btn-alert');
  $('#render-clnt').removeClass('btn-alert').addClass('btn-simple');
  var formdata = $('form').serialize();
  // formdata += "&function=e_renderProfileDetails"+"&csrfMagicToken=" + csrfMagicToken;
  data = { function: 'e_render_ProfileDetails', csrfMagicToken: csrfMagicToken };

  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: data,
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
  formdata += '&function=renderClientProfileDetails' + '&csrfMagicToken=' + csrfMagicToken;
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
      function: 'e_render_LevelTwoProfile',
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

function updateProfileDetails() {
  var formdata = $('form').serialize();
  formdata += '&function=e_save_ProfileDetails' + '&csrfMagicToken=' + csrfMagicToken;
  $.ajax({
    url: '../lib/l-profilewiz.php',
    type: 'POST',
    data: formdata,
    success: function (data) {
      var res = JSON.parse(data);
      $.notify(res.msg);
      setTimeout(function () {
        debugger;
        location.href = 'index.php';
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
    rightContainerSlideOn('attach-profile');
  }
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

  var isChecked = '';
  if (item['status'] == 3) {
    isChecked = 'checked';
  }

  if (Array.isArray(item['children']) && item['children'].length > 0) {
    clientProfStr +=
      '<label class="form-check-label">\n\
                            <input class="form-check-input" ' +
      isChecked +
      ' type="checkbox" name="global-cli" value="' +
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
                            <input class="form-check-input" ' +
      isChecked +
      ' type="checkbox" name="global-cli" value="' +
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

  var checked = '';
  if (item['status'] == 3) {
    checked = 'checked';
  }

  clientProfStr +=
    '<input class="form-check-input" ' +
    checked +
    ' type="checkbox" name="global-cli" value="' +
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
