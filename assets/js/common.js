$(document).ready(function () {
  var page = $('#pageName').html();
  console.log('page', page);
  if (page == 'Home Page' || page == 'Manage Devices' || page == '' || page == 'Services') {
    $('#siteFilter').css('display', 'flex');
  } else if (page == 'Notifications' || page == 'Notification' || page == 'Compliance' || page == 'Ticketing Wizard') {
    $('#siteFilter').css('display', 'flex');
    // $('#filterChoice').css('display', 'block');
    // $('#settings').css('display', 'block');
    $('#notifyDtl_filter').css('display', 'block');
  } else if (
    page == 'Software Distribution Configuration' ||
    page == 'Groups' ||
    page == 'Profiles' ||
    page == 'Software Distribution' ||
    page == 'Alert Configuration' ||
    page == 'Users' ||
    page == 'Access Right &amp; Permissions' ||
    page == 'Site' ||
    page == 'User Activity Audit' ||
    page == 'Dart Audit' ||
    page == 'Login Information' ||
    page == 'Visualisation Weights' ||
    page == 'Automation Audit'
  ) {
    // $('#settings').css('display', 'block');
    $('#notifyDtl_filter').css('display', 'block');
  } else if (page == 'Patch Management' || page == 'Patch Management Configure' || page == 'Nanoheal Client Update' || page == 'Census') {
    // $('#settings').css('display', 'block');
    $('#siteFilter').css('display', 'flex');
    $('#notifyDtl_filter').css('display', 'block');
  }

  if (page == 'Alert Configuration') {
    $('#forAlert').css('display', 'block');
  } else if (page == 'Notification' || page == 'Notifications') {
    $('#forNotification').css('display', 'block');
  } else if (page == 'Compliance') {
    $('#forComplaince').css('display', 'block');
  } else if (page == 'Census') {
    $('#forCensus').css('display', 'block');
  } else if (page == 'Profiles') {
    $('#forProfiles').css('display', 'block');
  } else if (page == 'Software Distribution') {
    $('#forSoftwareDistribution').css('display', 'block');
  } else if (page == 'Users') {
    $('#forUsers').css('display', 'block');
  } else if (page == 'Access Right &amp; Permissions') {
    $('#forAccessRight').css('display', 'block');
  } else if (page == 'Site') {
    $('#forSite').css('display', 'block');
  } else if (page == 'User Activity Audit') {
    $('#forUserActivity').css('display', 'block');
  } else if (page == 'Dart Audit') {
    $('#forDartAudit').css('display', 'block');
  } else if (page == 'Login Information') {
    $('#forLoginInfo').css('display', 'block');
  } else if (page == 'Patch Management') {
    $('#forPatchM').css('display', 'block');
  } else if (page == 'Patch Management Configure') {
    $('#forPatchMC').css('display', 'block');
  } else if (page == 'Nanoheal Client Update') {
    $('#forNanohealClient').css('display', 'block');
  } else if (page == 'Groups') {
    $('#forGroups').css('display', 'block');
  } else if (page == 'Software Distribution Configuration') {
    $('#forSwDis').css('display', 'block');
  } else if (page == 'Ticketing Wizard') {
    $('#forTW').css('display', 'block');
  } else if (page == 'Visualisation Weights') {
    $('#forNew').css('display', 'block');
  } else if (page == 'Automation Audit') {
    $('#forAutoAudit').css('display', 'block');
  }

  /*
  From line 82 to line 90 we do a check.
  If we didn't have to show the #siteFilter block then after reloading the page this block was not shown.
  The value is set in the reloadview() function
  */

  const type = sessionStorage.getItem('typeFromDB');
  if (type === '2') {
    $('#siteFilter').hide();
    $('#reportrange').hide();
  }

  if (type === '1') {
    sessionStorage.removeItem('typeFromDB');
  }

  resetAbsoluteLoader();

  $sidebar = $('.sidebar');
  $navbar = $('.navbar');
  $main_panel = $('.main-panel');
  showdiv = '';

  $full_page = $('.full-page');

  $sidebar_responsive = $('body > .navbar-collapse');
  sidebar_mini_active = true;
  white_color = false;

  window_width = $(window).width();

  fixed_plugin_open = $('.sidebar .sidebar-wrapper .nav li.active a p').html();

  $('.fixed-plugin a').click(function (event) {
    if ($(this).hasClass('switch-trigger')) {
      if (event.stopPropagation) {
        event.stopPropagation();
      } else if (window.event) {
        window.event.cancelBubble = true;
      }
    }
  });

  $('.fixed-plugin .background-color span').click(function () {
    $(this).siblings().removeClass('active');
    $(this).addClass('active');

    var new_color = $(this).data('color');

    if ($sidebar.length != 0) {
      $sidebar.attr('data', new_color);
    }

    if ($main_panel.length != 0) {
      $main_panel.attr('data', new_color);
    }

    if ($full_page.length != 0) {
      $full_page.attr('filter-color', new_color);
    }

    if ($sidebar_responsive.length != 0) {
      $sidebar_responsive.attr('data', new_color);
    }
  });

  $('.switch-sidebar-mini input').on('switchChange.bootstrapSwitch', function () {
    var $btn = $(this);

    if (sidebar_mini_active == true) {
      $('body').removeClass('sidebar-mini');
      sidebar_mini_active = false;
      blackDashboard.showSidebarMessage('Sidebar mini deactivated...');
    } else {
      $('body').addClass('sidebar-mini');
      sidebar_mini_active = true;
      blackDashboard.showSidebarMessage('Sidebar mini activated...');
    }

    // we simulate the window Resize so the charts will get updated in realtime.
    var simulateWindowResize = setInterval(function () {
      window.dispatchEvent(new Event('resize'));
    }, 180);

    // we stop the simulation of Window Resize after the animations are completed
    setTimeout(function () {
      clearInterval(simulateWindowResize);
    }, 1000);
  });

  $('.switch-change-color input').on('switchChange.bootstrapSwitch', function () {
    var $btn = $(this);

    if (white_color == true) {
      $('body').addClass('change-background');
      setTimeout(function () {
        $('body').removeClass('change-background');
        $('body').removeClass('white-content');
      }, 900);
      white_color = false;
    } else {
      $('body').addClass('change-background');
      setTimeout(function () {
        $('body').removeClass('change-background');
        $('body').addClass('white-content');
      }, 900);

      white_color = true;
    }
  });

  $('.light-badge').click(function () {
    $('body').addClass('white-content');
  });

  $('.dark-badge').click(function () {
    $('body').removeClass('white-content');
  });

  $('.hover-collapse').hover(
    function () {
      showdiv = $(this).attr('div-target');
      $('#' + showdiv).addClass('show');
    },
    function () {
      showdiv = $(this).attr('div-target');
      $('#' + showdiv).removeClass('show');
    },
  );
});

function resetAbsoluteLoader() {
  $('#mainPanelContent').show();
  $('#fullWrapper').css('height', '100vh').show();
  $('#absoBodyLoader').hide();
}

$(document).ready(function () {
  $('table.nhl-datatable').parent('div.dataTables_scrollBody').css({
    height: 'calc(100vh - 240px)',
  });
});

$(document).ready(function () {
  // initialise Datetimepicker and Sliders
  blackDashboard.initDateTimePicker();
  if ($('.slider').length != 0) {
    demo.initSliders();
  }
});

//right slide container open close logic
$(document).ready(function () {
  $('.rightslide-container-hand').click(function () {
    $('.rightslide-container-close').trigger('click');

    if($('#profilepicture-add-container').css('width').replace('%', '').replace('px','').replace('em','') > 0){
      $('.profilepicture-container-close').trigger('click');
    }

    if($('#reset-pass-container').css('width').replace('%', '').replace('px','').replace('em','') > 0){
      $('.reset-pass-container-close').trigger('click');
    }

    var targetId = $(this).attr('data-bs-target');
    var target = $('#' + targetId);
    var dataClass = target.attr('data-class');

    console.log('t=', targetId);
    console.log('tt=', target);

    $('#absoFeed').css({
      display: 'block',
      width: '100%',
    });
    //target.css({'width': 'auto'});
    target.removeClass('sm-3');
    target.removeClass('md-6');
    target.removeClass('lg-9');
    target.removeClass('rightslide-container-hide');
    target.addClass(dataClass);
  });

  $('.rightslide-container-close').click(function () {
    var targetId = $(this).attr('data-bs-target');
    var target = $('#' + targetId);

    if (window.isSelectionSet != undefined) {
      window.isSelectionSet = false;
    }

    //target.css({'width': '0'});
    target.removeClass('sm-3');
    target.removeClass('md-6');
    target.removeClass('lg-9');
    target.addClass('rightslide-container-hide');
    enableFields();
    $('.form-control input[type=text]').val('');
    $('.selectpicker').val('');
    $('.selectpicker').selectpicker('refresh');

    // const absoFeedWidth = Number($('#absoFeed')[0].style.width.replace('%', '')) - 100 + '%';
    // $('#absoFeed').css({
    //   display: absoFeedWidth === '0%' ? 'none' : 'block',
    //   width: Number($('#absoFeed')[0].style.width.replace('%', '')) - 100 + '%',
    // });
    $('#rsc-blur-loader').addClass('hide');

    var sliderOpenCnt = 0;
    $('.rightSidenav').each(function(i,item){
      if($(this).css('width').replace('%', '').replace('px', '') != '0'){
        sliderOpenCnt++;
      }
    });
    
    if(sliderOpenCnt <= 1){
      //$('#absoFeed').css({ display: 'none', width: '0px' });
      $('#absoFeed').css({
        display: 'none',
        width: Number($('#absoFeed')[0].style.width.replace('%', '')) - 100 + '%',
      });
    }
  });
});

/* form validation starts here */

$(document).ready(function () {
  $sidebar = $('.sidebar');
  $navbar = $('.navbar');
  $main_panel = $('.main-panel');

  $full_page = $('.full-page');

  $sidebar_responsive = $('body > .navbar-collapse');
  sidebar_mini_active = true;
  white_color = false;

  window_width = $(window).width();

  fixed_plugin_open = $('.sidebar .sidebar-wrapper .nav li.active a p').html();

  $('.fixed-plugin a').click(function (event) {
    if ($(this).hasClass('switch-trigger')) {
      if (event.stopPropagation) {
        event.stopPropagation();
      } else if (window.event) {
        window.event.cancelBubble = true;
      }
    }
  });

  $('.fixed-plugin .background-color span').click(function () {
    $(this).siblings().removeClass('active');
    $(this).addClass('active');

    var new_color = $(this).data('color');

    if ($sidebar.length != 0) {
      $sidebar.attr('data', new_color);
    }

    if ($main_panel.length != 0) {
      $main_panel.attr('data', new_color);
    }

    if ($full_page.length != 0) {
      $full_page.attr('filter-color', new_color);
    }

    if ($sidebar_responsive.length != 0) {
      $sidebar_responsive.attr('data', new_color);
    }
  });

  $('.switch-sidebar-mini input').on('switchChange.bootstrapSwitch', function () {
    var $btn = $(this);

    if (sidebar_mini_active == true) {
      $('body').removeClass('sidebar-mini');
      sidebar_mini_active = false;
      blackDashboard.showSidebarMessage('Sidebar mini deactivated...');
    } else {
      $('body').addClass('sidebar-mini');
      sidebar_mini_active = true;
      blackDashboard.showSidebarMessage('Sidebar mini activated...');
    }

    // we simulate the window Resize so the charts will get updated in realtime.
    var simulateWindowResize = setInterval(function () {
      window.dispatchEvent(new Event('resize'));
    }, 180);

    // we stop the simulation of Window Resize after the animations are completed
    setTimeout(function () {
      clearInterval(simulateWindowResize);
    }, 1000);
  });

  $('.switch-change-color input').on('switchChange.bootstrapSwitch', function () {
    var $btn = $(this);

    if (white_color == true) {
      $('body').addClass('change-background');
      setTimeout(function () {
        $('body').removeClass('change-background');
        $('body').removeClass('white-content');
      }, 900);
      white_color = false;
    } else {
      $('body').addClass('change-background');
      setTimeout(function () {
        $('body').removeClass('change-background');
        $('body').addClass('white-content');
      }, 900);

      white_color = true;
    }
  });

  $('.light-badge').click(function () {
    $('body').addClass('white-content');
  });

  $('.dark-badge').click(function () {
    $('body').removeClass('white-content');
  });
});

function setFormValidation(id) {
  $(id).validate({
    highlight: function (element) {
      $(element).closest('.form-group').removeClass('has-success').addClass('has-danger');
      $(element).closest('.form-check').removeClass('has-success').addClass('has-danger');
    },
    success: function (element) {
      $(element).closest('.form-group').removeClass('has-danger').addClass('has-success');
      $(element).closest('.form-check').removeClass('has-danger').addClass('has-success');
    },
    errorPlacement: function (error, element) {
      $(element).closest('.form-group').append(error);
    },
  });
}

$(document).ready(function () {
  setFormValidation('#RegisterValidation');
  setFormValidation('#TypeValidation');
  setFormValidation('#LoginValidation');
  setFormValidation('#RangeValidation');
});

/* services page innerPage hide & show */

$('#tab1').click(function () {
  $('.innTab').hide();
  $('.innerTab').hide();
  $('#tab_1').show();
  $('#probAuto').show();
  $('.tabBox').show();
  //$("#tab_1").trigger("click");
});

$('#tab2').click(function () {
  $('.innTab').hide();
  $('.innerTab').hide();
  $('#tab2').show();
});

$('#tab3').click(function () {
  $('.innTab').hide();
  $('.innerTab').hide();
  $('#tab3').show();
});

$('#tab4').click(function () {
  $('.innTab').hide();
  $('.innerTab').hide();
  $('#tab4').show();
});

$('#tab5').click(function () {
  $('.innTab').hide();
  $('.innerTab').hide();
  $('#tab5').show();
});

$('#tab6').click(function () {
  $('.innTab').hide();
  $('.innerTab').hide();
  $('#tab6').show();
});

$('#tab7').click(function () {
  $('.innTab').hide();
  $('.innerTab').hide();
  $('#tab7').show();
});

$('.backBtn').click(function () {
  location.reload();
});

$('#tab_1').click(function () {
  location.reload();
});

$('.innTab').click(function () {
  $('.backBtn').show();
});

/* services page innerTab hide & show */

//filterSelection("all")
function filterSelection(c) {
  var x, i;
  x = document.getElementsByClassName('column');
  if (c == 'all') c = '';
  for (i = 0; i < x.length; i++) {
    w3RemoveClass(x[i], 'show');
    if (x[i].className.indexOf(c) > -1) w3AddClass(x[i], 'show');
  }
}

function w3AddClass(element, name) {
  var i, arr1, arr2;
  arr1 = element.className.split(' ');
  arr2 = name.split(' ');
  for (i = 0; i < arr2.length; i++) {
    if (arr1.indexOf(arr2[i]) == -1) {
      element.className += ' ' + arr2[i];
    }
  }
}

function w3RemoveClass(element, name) {
  var i, arr1, arr2;
  arr1 = element.className.split(' ');
  arr2 = name.split(' ');
  for (i = 0; i < arr2.length; i++) {
    while (arr1.indexOf(arr2[i]) > -1) {
      arr1.splice(arr1.indexOf(arr2[i]), 1);
    }
  }
  element.className = arr1.join(' ');
}

function slideshow() {
  var mainContainer = $('.nhl-tab-slider-main-container');
  var next = mainContainer.find('.nhl-tab-slider-next').eq(0);
  var previous = mainContainer.find('.nhl-tab-slider-previous').eq(0);
  var tabContainer = mainContainer.find('.nhl-tab-slider-tab-container').eq(0);
  var tab = tabContainer.find('li');
  /* console.log("tab");
     console.log(tab); */
  var totalTabs = tab.length;
  /* console.log("totalTabs");
     console.log(totalTabs); */
  var eachTabWidth = tab.find('a.toolTip').eq(0).innerWidth();
  /* console.log("eachTabWidth");
     console.log(eachTabWidth); */
  var totalTabsWidth = eachTabWidth * totalTabs;

  var mainContainerWidth = mainContainer.parent('div').innerWidth();

  next.attr('data-state', 'true');
  previous.attr('data-state', 'true');

  previous.click(function () {
    /* console.log("previous click");
         console.log(mainContainerWidth); */

    var currentLeftOffset = parseInt(tabContainer.css('left'));
    currentLeftOffset = currentLeftOffset == undefined || currentLeftOffset == 'undefined' ? 0 : currentLeftOffset;
    currentLeftOffset = currentLeftOffset < 0 ? currentLeftOffset * -1 : currentLeftOffset;
    /* console.log(currentLeftOffset); */
    var moveNow = currentLeftOffset + eachTabWidth;
    //console.log(currentLeftOffset);
    //console.log(eachTabWidth);
    //console.log(moveNow);

    if (previous.attr('data-state') == 'true') {
      tabContainer.animate({ left: '-' + moveNow + 'px' });

      if (currentLeftOffset <= eachTabWidth) {
        next.attr('data-state', 'true');
      }

      if (moveNow > tabContainer.innerWidth()) {
        tabContainer.animate({ left: '0px' });
      }
    }

    var updatedLeftOffset = parseInt(tabContainer.css('left'));
    updatedLeftOffset = updatedLeftOffset == undefined || updatedLeftOffset == 'undefined' ? 0 : updatedLeftOffset;
    updatedLeftOffset = updatedLeftOffset < 0 ? updatedLeftOffset * -1 : updatedLeftOffset;

    var calOffset = parseInt(totalTabsWidth) - parseInt(mainContainerWidth);

    if (updatedLeftOffset > calOffset) {
      previous.attr('data-state', 'false');
    }
  });

  next.click(function () {
    console.log('next click');
    var currentLeftOffset = parseInt(tabContainer.css('left'));
    currentLeftOffset = currentLeftOffset == undefined || currentLeftOffset == 'undefined' ? 0 : currentLeftOffset;

    var moveNow = currentLeftOffset + eachTabWidth;

    if (next.attr('data-state') == 'true') {
      tabContainer.animate({ left: moveNow + 'px' });

      if (moveNow > tabContainer.innerWidth()) {
        tabContainer.animate({ left: '0px' });
      }
    }

    if (moveNow > 0) {
      next.attr('data-state', 'false');
      tabContainer.animate({ left: '0px' });
    } else {
      next.attr('data-state', 'true');
    }

    var updatedLeftOffset = parseInt(tabContainer.css('left'));
    updatedLeftOffset = updatedLeftOffset == undefined || updatedLeftOffset == 'undefined' ? 0 : updatedLeftOffset;

    var calOffset = parseInt(totalTabsWidth) - parseInt(mainContainerWidth);

    if (updatedLeftOffset <= calOffset) {
      previous.attr('data-state', 'true');
    }
  });
}

function rightContainerSlideOn(containerId) {
  var target = $('#' + containerId);
  var dataClass = target.attr('data-class');
  $('#absoFeed').css({
    display: 'block',
    width: Number($('#absoFeed')[0].style.width.replace('%', '').replace('px', '').replace('em', '')) + 100 + '%',
  });
  target.removeClass('sm-3');
  target.removeClass('md-6');
  target.removeClass('lg-9');
  target.removeClass('rightslide-container-hide');
  target.addClass(dataClass);
}

function rightContainerSlideClose(containerId) {
  console.log("panel closed");
  var target = $('#' + containerId);

  target.removeClass('sm-3');
  target.removeClass('md-6');
  target.removeClass('lg-9');
  target.addClass('rightslide-container-hide');
  $('.form-control input[type=text]').val('');
  $('.selectpicker').val('');
  $('.selectpicker').selectpicker('refresh');
  $('#rsc-blur-loader').addClass('hide');

  //to get the no of opened side panels
  var sliderOpenCnt = 0;
  $('.rightSidenav').each(function(i,item){
    if($(this).css('width').replace('%', '').replace('px', '') != '0'){
      sliderOpenCnt++;
    }
  });
  
  if(sliderOpenCnt <= 1){
    $('#absoFeed').css({ display: 'none', width: '0px' });
  }
}

function rightContainerSlideClose_Device(containerId) {
  var target = $('#' + containerId);

  target.removeClass('sm-3');
  target.removeClass('md-6');
  target.removeClass('lg-9');
  target.addClass('rightslide-container-hide');
  //$('.form-control input[type=text]').val('');
  //$('.selectpicker').val('');
  //$(".selectpicker").selectpicker("refresh");
  $('#rsc-blur-loader').addClass('hide');

  var sliderOpenCnt = 0;
  $('.rightSidenav').each(function(i,item){
    if($(this).css('width').replace('%', '').replace('px', '') != '0'){
      sliderOpenCnt++;
    }
  });
  
  if(sliderOpenCnt <= 1){
    $('#absoFeed').css({ display: 'none', width: '0px' });
  }
}

/* colorpicker starts here */

$(function () {
  $('.simple-color-picker').colorpicker();
});

//toggle edit option code
$('.editOption').on('click', function () {
  $('#editOption').hide();
  $('#toggleButton').show();
  $('.iconTick').addClass('circleGrey');

  if ($(this).attr('data-target-container-only') != undefined && $(this).attr('data-target-container-only') == 'true') {
    var container = $(this).parents('.rightSidenav');
    enableContainerFields(container);
  } else {
    enableFields();
  }
});

$('.toggleEdit').on('click', function () {
  if ($(this).attr('data-target-container-only') != undefined && $(this).attr('data-target-container-only') == 'true') {
    var container = $(this).parents('.rightSidenav');
    disableContainerFields(container);
  } else {
    disableFields();
  }

  $('#toggleButton').hide();
  $('#editOption').show();
});

function enableFields() {
  $('.form-control').attr('readonly', false);
  $('.selectpicker').attr('disabled', false);
  $('.selectpicker').selectpicker('refresh');
  $('.form-check-input').prop('disabled', false);
  $('.form-check-input').removeClass('not-allowed');
}

function disableFields() {
  $('.form-control').attr('readonly', true);
  $('.selectpicker').attr('disabled', true);
  $('.selectpicker').selectpicker('refresh');
  $('.form-check-input').prop('disabled', true);
  $('.form-check-input').addClass('not-allowed');
}

function disableContainerFields(container) {
  container.find('.form-control').attr('readonly', true);
  container.find('.form-check').addClass('disabled').find('input.form-check-input').prop('disabled', true);
  container.find('.selectpicker').attr('disabled', true);
  container.find('.selectpicker').selectpicker('refresh');
  container.find('.form-toggle-item').hide();
}

function enableContainerFields(container) {
  container.find('.form-control').attr('readonly', false);
  container.find('.form-check').removeClass('disabled').find('input.form-check-input').prop('disabled', false);
  container.find('.selectpicker').attr('disabled', false);
  container.find('.selectpicker').selectpicker('refresh');
  container.find('.form-toggle-item').show();

  $.each($('[data-after-form-check-input-disabled=true]'), function () {
    if ($(this).attr('type') == 'radio' || $(this).attr('type') == 'check') {
      $(this).attr('disabled', 'disabled').parents('.form-check-label').addClass('disabled');
    }
  });

  $.each(container.find('[data-after-form-text-input-readonly=true]'), function () {
    if ($(this).attr('type') == 'text') {
      $(this).attr('readonly', 'readonly');
    }
  });
}

$('.form-control').on('keyup', action);
$('.selectpicker').on('change', action);
$('.form-check-input').on('click', action);

function action() {
  $('.iconTick').removeClass('circleGrey');
}

function closePopUp() {
  setTimeout(function () {
    $('.closebtn').trigger('click');
    $('#absoFeed').css({
      display: 'none',
      width: '0px',
    });
  }, 10);
}

function errorNotify(message) {
  $.notify(message);
}

function successNotify(message) {
  $.notify(message);
}

function randomString(length) {
  var result = '';
  var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  var charactersLength = characters.length;
  for (var i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * charactersLength));
  }
  return result;
}

function changeUploadFileName(file) {
  var fileExtension = '';
  if (file.indexOf('.')) {
    file = file.split('.');
    fileExtension = file[file.length - 1];
  }

  return randomString(64) + '_' + new Date().getTime() + '_' + randomString(6) + '.' + fileExtension;
}

function RightSlider(containerId) {
  this.containerId = containerId;
  this.container = $(containerId);
  this.loader = null;
  this.loaderClass = 'rsc-loader';
  this.hideClass = 'hide';
  this.blurLoaderId = 'rsc-blur-loader';
}

RightSlider.prototype.showLoader = function () {
  this.loader = this.container.find('.' + this.loaderClass);
  if (this.loader.hasClass(this.hideClass)) {
    this.loader.removeClass(this.hideClass);
  }
};

RightSlider.prototype.hideLoader = function () {
  this.loader = this.container.find('.' + this.loaderClass);
  if (!this.loader.hasClass(this.hideClass)) {
    this.loader.addClass(this.hideClass);
  }
};

RightSlider.prototype.showBlurLoader = function () {
  this.loader = $('#' + this.blurLoaderId);
  this.setBlurContainerCss();
  this.container.find('.form.table-responsive').css('-webkit-filter', 'blur(1px)');

  if (this.loader.hasClass(this.hideClass)) {
    this.loader.removeClass(this.hideClass);
  }
};

RightSlider.prototype.hideBlurLoader = function () {
  this.loader = $('#' + this.blurLoaderId);
  this.container.find('.form.table-responsive').css('-webkit-filter', 'blur(0px)');
  if (!this.loader.hasClass(this.hideClass)) {
    this.loader.addClass(this.hideClass);
  }
};

RightSlider.prototype.setBlurContainerCss = function () {
  var offset = this.container.offset();
  $('#' + this.blurLoaderId).css('top', offset.top + this.container.find('.rightslide-container-close').innerHeight() + 'px');
};

$('#dropdownMenuButton').click(function () {
  dropdownMenuButton_click();
});

function dropdownMenuButton_click() {
  //$("#rsc-add-container3 a:has([title])").css("background-color", "");
  var searchType = $('#searchType').val();
  var searchValue = $('#searchValue').val();
  var rparentName = $('#rparentName').val();
  //var passlevel = $("#passlevel").val();
  //console.log("searchType--->" + searchType + "searchValue--->" + searchValue + "rparentName--->" + rparentName + "passlevel--->" + passlevel);
  if ((searchType === 'Sites' || searchType === 'Groups') && rparentName !== '') {
    $('a[title="' + rparentName + '"]').css('background-color', '#f5f6fa');
  }
  if (searchType === 'ServiceTag' && rparentName !== '' && searchValue !== '') {
    $('a[title="' + rparentName + '"]').css('background-color', '#f5f6fa');
    //$('[title="' + searchValue + '"]').css("background-color", "#f5f6fa");
  }
}

var elem = document.getElementById('notifSearch');
elem.onkeyup = function (e) {
  if (e.keyCode == 13) {
    getSearchRecords();
  }
};

function getSearchRecords() {
  var page = $('#pageName').html();
  var notifSearch = $('#notifSearch').val();
  var NocName = $('#notiname').val();
  if (notifSearch == '') {
    return false;
  } else {
    // document.getElementById('notifSearch').disabled = true;
    document.getElementById('notifSearch').style.background = 'white !important';

    $('.clearbtn').css('display', 'block');
    $('.showbtn').css('display', 'none');
    if (page == 'Notification' || page == 'Notifications') {
      notificationDtl_datatable('', NocName, 'mainactive', '', 1, notifSearch);
    } else if (page == 'Compliance') {
      complianceDtl_datatable('', '', NocName, 'mainactive', '', 1, notifSearch);
    } else if (page == 'Census') {
      get_deviceDetails(1, notifSearch);
    } else if (page == 'Software Distribution') {
      Get_SoftwareRepositoryData(1, notifSearch);
    } else if (page == 'Software Distribution Configuration') {
      Get_SoftwareRepositoryData2(1, notifSearch);
    } else if (page == 'Patch Management') {
      mum_patchlistData((wintype = ''), (nextPage = 1), notifSearch);
    } else if (page == 'User Activity Audit') {
      getLogDetails(1, notifSearch);
    } else if (page == 'Dart Audit') {
      audit_datatablelist(1, notifSearch);
    } else if (page == 'Login Information') {
      getLoginDetails(1, notifSearch);
    } else if (page == 'Groups') {
      get_advncdgroupData(1, notifSearch);
    } else if (page == 'Alert Configuration') {
      fetchAlertList(1, notifSearch);
    } else if (page == 'Users') {
      user_datatable('', 1, notifSearch);
    } else if (page == 'Site') {
      siteDataTable(1, notifSearch);
    } else if (page == 'Visualisation Weights') {
      getWeightDetails(1, notifSearch);
    } else if (page == 'Profiles') {
      get_ProfileWizardDetails(1, notifSearch);
    } else if (page == 'Automation Audit') {
      getLogDetails('', 1, notifSearch, '', '');
    } else if (page == 'Ticketing Wizard') {
      getTicketingDetails(1, notifSearch);
    }
  }
}

function clearRecords() {
  var page = $('#pageName').html();
  document.getElementById('notifSearch').value = '';
  var notifSearch = $('#notifSearch').val();
  var NocName = $('#notiname').val();
  if (notifSearch == '') {
    // document.getElementById('notifSearch').disabled = false;
    $('.clearbtn').css('display', 'none');
    $('.showbtn').css('display', 'block');
    if (page == 'Notification' || page == 'Notifications') {
      notificationDtl_datatable('', NocName, 'mainactive', '', 1, notifSearch);
    } else if (page == 'Compliance') {
      complianceDtl_datatable('', '', NocName, 'mainactive', '', 1, notifSearch);
    } else if (page == 'Census') {
      get_deviceDetails(1, notifSearch);
    } else if (page == 'Software Distribution') {
      Get_SoftwareRepositoryData(1, notifSearch);
    } else if (page == 'Software Distribution Configuration') {
      Get_SoftwareRepositoryData2(1, notifSearch);
    } else if (page == 'Patch Management') {
      mum_patchlistData((wintype = ''), (nextPage = 1), notifSearch);
    } else if (page == 'User Activity Audit') {
      getLogDetails(1, notifSearch);
    } else if (page == 'Dart Audit') {
      audit_datatablelist(1, notifSearch);
    } else if (page == 'Login Information') {
      getLoginDetails(1, notifSearch);
    } else if (page == 'Groups') {
      get_advncdgroupData(1, notifSearch);
    } else if (page == 'Alert Configuration') {
      fetchAlertList(1, notifSearch);
    } else if (page == 'Users') {
      user_datatable('', 1, notifSearch);
    } else if (page == 'Site') {
      siteDataTable(1, notifSearch);
    } else if (page == 'Visualisation Weights') {
      getWeightDetails(1, notifSearch);
    } else if (page == 'Profiles') {
      get_ProfileWizardDetails(1, notifSearch);
    } else if (page == 'Automation Audit') {
      getLogDetails('', 1, notifSearch);
    } else if (page == 'Ticketing Wizard') {
      getTicketingDetails(1, notifSearch);
    }
  }
}

$('.sortArrow').click(function () {
  var page = $('#pageName').html();
  if ($('#' + this.id + '').hasClass('headerUp')) {
    var sort = 'desc';
    var key = this.headers;
    $('#' + this.id + '').removeClass('headerUp');
    $('#' + this.id + '').addClass('headerDown');
  } else {
    var sort = 'asc';
    var key = this.headers;
    $('#' + this.id + '').removeClass('headerDown');
    $('#' + this.id + '').addClass('headerUp');
  }
  if (page == 'Notification' || page == 'Notifications') {
    notificationDtl_datatable((priority = ''), (name = ''), (reflag = ''), (status = ''), (nextPage = 1), (notifSearch = ''), key, sort);
  } else if (page == 'Compliance') {
    complianceDtl_datatable((item = ''), (category = ''), (name = ''), (reflag = ''), (status = ''), (nextPage = 1), (notifSearch = ''), key, sort);
  } else if (page == 'Census') {
    get_deviceDetails(1, (notifSearch = ''), key, sort);
  } else if (page == 'Software Distribution Configuration') {
    Get_SoftwareRepositoryData2(1, (notifSearch = ''), key, sort);
  } else if (page == 'Software Distribution') {
    Get_SoftwareRepositoryData(1, (notifSearch = ''), key, sort);
  } else if (page == 'Patch Management') {
    mum_patchlistData((wintype = ''), (nextPage = 1), (notifSearch = ''), key, sort);
  } else if (page == 'User Activity Audit') {
    getLogDetails(1, notifSearch, key, sort);
  } else if (page == 'Dart Audit') {
    audit_datatablelist(1, '', key, sort);
  } else if (page == 'Login Information') {
    getLoginDetails(1, notifSearch, key, sort);
  } else if (page == 'Groups') {
    get_advncdgroupData(1, notifSearch, key, sort);
  } else if (page == 'Alert Configuration') {
    fetchAlertList(1, notifSearch, key, sort);
  } else if (page == 'Users') {
    user_datatable('', 1, notifSearch, key, sort);
  } else if (page == 'Site') {
    siteDataTable((nextPage = 1), (notifSearch = ''), key, sort);
  } else if (page == 'Visualisation Weights') {
    getWeightDetails(1, '', key, sort);
  } else if (page == 'Profiles') {
    get_ProfileWizardDetails(1, '', key, sort);
  } else if (page == 'Automation Audit') {
    getLogDetails('notif', 1, '', key, sort);
  }
});

function sortingIconColor(data) {
  $('.direction').css('color', '#b2b2b2');
  $('#' + data).css('color', '#2b2b2b');
}

function callDropDown() {
  $('#settingGroupDrop').addClass('open');
}

// typeFromDB = 1 for default analitics types
// typeFromDB = 2 for groupped analitics types
function reloadview(id, name, typeFromDB = 1) {
  var name = name.trim();
  name = name.replace(/#/g, ' ');
  $('#homeError').hide();
  $('#dashId').val(id);
  $('#dashName').html(name);
  $('#dashoardname').val(name);
  $('#absoLoader').show();
  $('#Iframe').hide();

  var window = $('#currentwindow').val();
  if ($.trim(window) === 'home' && document.getElementById('Iframe') !== null) {
    $('.menu-l1-view').removeClass('show');
    $('.menu-l1-view').removeClass('collapsing');
    $('.menu-l1-view').addClass('collapse');
    $.each($('li.sidebar-dashboard-items'), function () {
      if ($(this).hasClass('active')) {
        $(this).removeClass('active');
      }
    });

    var targetList = $('li.sidebar-dashboard-items[data-idx=' + id + ']');
    if (!targetList.hasClass('active')) {
      targetList.addClass('active');
    }
    sessionStorage.setItem('dashId', id);
    sessionStorage.setItem('dashName', name);
    loadLandingpage();
  } else {
    sessionStorage.setItem('dashId', id);
    sessionStorage.setItem('dashName', name);
    debugger;
    location.href = '/Dashboard/home';
  }

  $('#siteFilter').show();
  $('#reportrange').show();

  sessionStorage.setItem('typeFromDB', typeFromDB);

  if (typeFromDB == 2) {
    // typeFromDB = 1 for default analitics types
    // typeFromDB = 2 for groupped analitics types
    $('#siteFilter').hide();
    $('#reportrange').hide();
  }
}

//New Site Addition Functionality

function showAddSite() {
  rightContainerSlideOn('site-addConfig-container');
  fetchPrefilledDetails();
}

function fetchPrefilledDetails() {
  $.ajax({
    url: '../admin/groupfunctions.php',
    type: 'POST',
    dataType: 'json',
    data: {
      function: 'get_DefaultSiteDetails',
      csrfMagicToken: csrfMagicToken,
    },
    success: function (data) {
      $('#deploy_emailsub').val(data.emailSubject);
      $('#deploy_emailsender').val(data.emailSender);
      $('#license_name').val(data.skuName);
      $('#license_details').val(data.amount);
      $('#license_bill').val(data.billingcycle);
    },
    error: function (errorThrown) {
      console.log(errorThrown);
    },
  });
}

function addNewSiteFunc() {
  $('#siteError').css('display', 'none');
  $('#emailSubError').css('display', 'none');
  $('#emailSenderError').css('display', 'none');
  $('#client32_nameError').css('display', 'none');
  $('#client64_nameError').css('display', 'none');
  $('#branding_urlError').css('display', 'none');

  var deploy_sitename = $('#deploy_sitename').val();
  var deploy_emailsub = $('#deploy_emailsub').val();
  var deploy_emailsender = $('#deploy_emailsender').val();
  var client32_name = $('#client32_name').val();
  var client64_name = $('#client64_name').val();
  var branding_url = $('#branding_url').val();

  if (deploy_sitename == '') {
    $('#siteError').css('display', 'block');
    return false;
  }

  if (deploy_emailsub == '') {
    $('#emailSubError').css('display', 'block');
    return false;
  }

  if (deploy_emailsender == '') {
    $('#emailSenderError').css('display', 'block');
    return false;
  }

  if (client32_name == '') {
    $('#client32_nameError').css('display', 'block');
    return false;
  }

  if (client64_name == '') {
    $('#client64_nameError').css('display', 'block');
    return false;
  }

  if (branding_url == '') {
    $('#branding_urlError').css('display', 'block');
    return false;
  }

  var currentwindow = $('#currentwindow').val();

  $.ajax({
    url: '../admin/groupfunctions.php',
    type: 'POST',
    dataType: 'json',
    data: {
      function: 'addNewSite',
      csrfMagicToken: csrfMagicToken,
      deploy_sitename: deploy_sitename,
      deploy_emailsub: deploy_emailsub,
      deploy_emailsender: deploy_emailsender,
      client32_name: client32_name,
      client64_name: client64_name,
      branding_url: branding_url,
    },
    success: function (data) {
      if ($.trim(data.msg) == 'success') {
        if (currentwindow === 'home') {
          $.notify('New Site Successfully Added');
          rightContainerSlideClose('site-addConfig-container');
          var postData = {
            function: 'AJAX_Update_Session',
            searchType: 'Sites',
            searchValue: deploy_sitename,
            rparentName: deploy_sitename,
            updateHome: true,
            csrfMagicToken: csrfMagicToken,
          };
          $.ajax({
            url: '../lib/l-ajax.php',
            type: 'POST',
            data: postData,
            success: function () {
              debugger;
              location.reload();
            },
            error: function (error) {},
          });
        } else {
          $.notify('New Site Successfully Added');
          siteDataTable(1, '', '', '');

          rightContainerSlideClose('site-addConfig-container');
        }
      } else if ($.trim(data.msg) == 'failed') {
        $.notify('Error while adding site');
        rightContainerSlideClose('site-addConfig-container');
      } else {
        $.notify($.trim(data.msg));
        // rightContainerSlideClose('site-addConfig-container');
      }
    },
    error: function (errorThrown) {
      console.log(errorThrown);
    },
  });
}

function debounce(func, timeout = 500) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      func.apply(this, args);
    }, timeout);
  };
}

function saveInput(val, id, thisVal) {
  var inputVal = $('#profile-name').val();
  var regex = /^[a-zA-Z0-9_\s]+$/g;

  if (inputVal.length > 50) {
    $.notify('Please limit the profile name to 50 characters');
  }

  if (!regex.test(val)) {
    $(thisVal).css('border-block-color', 'red');
    $(thisVal).css('background-color', 'antiquewhite');
    // $.notify("No special characters allowed expect underscore","error");
    // $("#err_"+id).html("No special characters allowed expect underscore.").css('color','red');
  } else {
    $(thisVal).css('background-color', 'transparent');
    $(thisVal).css('border-color', 'rgba(29, 37, 59, 0.2)');
  }
}

function addActiveSort(type, sort) {
  const activeElement = window.currentActiveSortElement;
  if (type === 'itemtype' || type === 'category') return;
  if (activeElement) {
    if (activeElement.type === type && activeElement.sort === sort) {
      return;
    }
  }

  window.currentActiveSortElement = {
    type,
    sort,
  };
}

function checkAndUpdateActiveSortElement(type, sort) {
  const activeElement = window.currentActiveSortElement;
  if (type === '' || sort === '') {
    if (activeElement) {
      window.currentActiveSortElement = {};
    }
  }
}

const trackInputChange = debounce((val, id, thisVal) => saveInput(val, id, thisVal));
