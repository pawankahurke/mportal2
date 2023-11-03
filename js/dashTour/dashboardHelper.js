$(document).ready(function () {
  //alert("hi");
  var str = window.location.search;
  //console.log(str);
  if (str.indexOf('tipAction=true') !== -1) {
    console.log('condition true');
    str = str.split('&');
    str = str[str.length - 1].split('=');
    var id = str[1];
    dbOperations(id);
  } else {
    console.log('condition false');
  }
});

function startTour(path, id) {
  path = decodeURIComponent(path);
  path = path + '&id=' + id;
  debugger;
  window.location.href = path;
}

function hideNshow(id, icon) {
  var id1 = document.getElementById(id);
  $('.panelDiv').each(function (index) {
    var tempId = $(this)[0];
    /*if(!id1.isSameNode(tempId)){
         $(this).slideUp("slow");
         }*/
    if (id1 !== tempId) {
      $(this).slideUp('slow');
    }
  });
  $('#' + id).slideToggle('slow');

  var iconId = document.getElementById(icon);
  $('.addIcon').each(function (index) {
    var tempId = $(this)[0];
    /*if(!iconId.isSameNode(tempId)){
         $(this).addClass("icon-circle-right");
         $(this).removeClass("icon-circle-down");
         }*/
    if (iconId !== tempId) {
      $(this).addClass('icon-ic_keyboard_arrow_right_24px');
      $(this).removeClass('icon-ic_keyboard_arrow_down_24px');
    }
  });

  if ($('#' + icon).attr('class') == 'addIcon icon-ic_keyboard_arrow_right_24px') {
    $('#' + icon).removeClass('icon-ic_keyboard_arrow_right_24px');
    $('#' + icon).addClass('icon-ic_keyboard_arrow_down_24px');
  } else {
    $('#' + icon).addClass('icon-ic_keyboard_arrow_right_24px');
    $('#' + icon).removeClass('icon-ic_keyboard_arrow_down_24px');
  }
}

$('.icon-ic_search_24px').click(function () {
  $('#searchField').toggle();
});

$('#searchField').keyup(function () {
  var data = $('#searchField').val();
  if (data.length == 0 || data == '') {
    var url = '../dashTour/completeHelperGuideDb.php?function=getAllData';
    getData(url);
  }
  if (data.length > 0) {
    var url = '../dashTour/completeHelperGuideDb.php?function=getSearchData&data=' + data;
    getData(url);
  }
});

function startImgTour(fileNames, action, name, id) {
  var divStr = '';
  var files = fileNames.replace(',', ' ');
  var filenamesDb = files.split(' ');
  //console.log(filenamesDb.length);
  var count = filenamesDb.length;
  if (action == 'image') {
    $('#videolink_id').hide();
    $('#prev_btn').show();
    $('#next_btn').show();
    $('#video_id').hide();
    rightContainerSlideClose('help-tour');
    rightContainerSlideOn('image_video_tour');
    for (var i = 0; i < count; i++) {
      if (i == 0) {
        var style = "style='display:block;'";
      } else {
        var style = "style='display:none;'";
      }

      divStr +=
        '<div class="card-body text-center" id="img_id' +
        i +
        '" ' +
        style +
        '>' +
        '<img  src="../imgUploads/' +
        filenamesDb[i] +
        '" style="height: 200px; width: 200px;">' +
        '<span><center><h5>' +
        filenamesDb[i] +
        '</h5></center></span>' +
        '</div>';
    }
    $('#files_data').html(divStr);
    var currId = 0;
    $('#next_btn').on('click', function () {
      //alert("next");
      currId++;
      if (currId < count) {
        $('#img_id' + (currId - 1)).hide();
        $('#img_id' + currId).show();
        //console.log("#img_id"+currId);
      } else {
        currId--;
      }
    });

    $('#prev_btn').on('click', function () {
      currId--;
      //console.log(currId);
      if (currId > -1) {
        //console.log(currId);
        $('#img_id' + currId).show();
        $('#img_id' + (currId + 1)).hide();
        //console.log("#img_id"+currId);
      } else {
        currId++;
      }
    });
  }

  if (action == 'video') {
    $('#videolink_id').hide();
    $('#img_id0').hide();
    $('#img_id1').hide();
    $('#prev_btn').hide();
    $('#next_btn').hide();
    //console.log(fileNames);
    var file = decodeURIComponent(fileNames.trim());
    //console.log(file);
    rightContainerSlideClose('help-tour');
    rightContainerSlideOn('image_video_tour');
    divStr +=
      '<div class="card-body" id="video_id">' +
      '<iframe width="420" height="315" src="../videoUploads/' +
      file +
      '" type="video/mp4"></iframe>' +
      '<span><center><h5>' +
      file +
      '</h5></center></span>' +
      '</div>';
    $('#files_data').html(divStr);
  }

  if (action == 'videoLink') {
    $('#video_id').hide();
    $('#img_id0').hide();
    $('#img_id1').hide();
    $('#prev_btn').hide();
    $('#next_btn').hide();
    rightContainerSlideClose('help-tour');
    rightContainerSlideOn('image_video_tour');
    $.ajax({
      url: '../helptour/completeHelperGuideDb.php?function=getPath&id=' + id + '&csrfMagicToken=' + csrfMagicToken,
      success: function (data) {
        //console.log("success");
        //console.log(data);
        divStr +=
          '<div class="card-body" id="videolink_id">' +
          '<iframe width="420" height="315" src="' +
          data +
          '"></iframe>' +
          '<span><center><h2>' +
          data +
          '</h2></center></span>' +
          '</div>';
        $('#files_data').html(divStr);
      },
      error: function (data) {
        //console.log("error");
      },
    });
    //
  }
}

function dbOperations(parentId) {
  $.ajax({
    url: '../helptour/helperFunction.php?function=getToolTipInfo&id=' + parentId + '&csrfMagicToken=' + csrfMagicToken,
    type: 'post',
    //data: "function=getToolTipInfo",
    dataType: 'json',
    success: function (returnData) {
      //console.log("success");
      //                //console.log(JSON.stringify(returnData));
      var ids = returnData.toolTipEle;
      var isEnable = returnData.toolTipEnable;
      var title = returnData.toolTipTitle;
      var text = returnData.toolTipInfo;
      var positions = returnData.toolTipPos;
      var path = returnData.toolTipPath;
      var popup = returnData.toolTipPopup;
      setTimeout(function () {
        createToolTips(ids, isEnable, title, text, positions, path, parentId, popup);
      }, 200);
    },
    error: function () {
      alert('failure In helperFunction.php');
    },
  });
}

function createToolTips(ids, isEnable, title, text, positions, path, parentId, popup) {
  var steps = [];
  for (var index = 0; index < ids.length; index++) {
    //var i = index;
    (function (i) {
      var obj = {};
      if (path[i] !== '') {
        //console.log("1");
        obj = {
          element: '#' + ids[i],
          content: text[i],
          placement: positions[i],
          onNext: function () {
            debugger;
            window.location.href = decodeURIComponent(path[i]) + '&id=' + parentId;
          },
          onPrev: function (ele) {
            try {
              $(steps[ele._current - 1].element)[0].scrollIntoView(false);
            } catch (e) {
              console.log(e.message);
            }
          },
          next: true,
        };
        if (isEnable[i] === 'true' && isEnable[i] == 'true') {
          //console.log("2");
          obj['title'] = title[i];
        }
      } else if (popup[i] === 'true') {
        //console.log(i);
        //console.log("3");
        obj = {
          element: '#' + ids[i],
          content: text[i],
          placement: positions[i],
          onNext: function (ele) {
            $(steps[ele._current].element).click();
            callNext(ele._current + 1);
          },
          onPrev: function (ele) {
            try {
              $(steps[ele._current - 1].element)[0].scrollIntoView(false);
            } catch (e) {
              console.log(e.message);
            }
          },
          next: true,
        };
        //console.log(obj);
        if (isEnable[i] === 'true' && isEnable[i] == 'true') {
          //console.log("4");
          obj['title'] = title[i];
          //console.log(obj["title"]);
        }
      } else {
        //console.log("5");
        obj = {
          element: '#' + ids[i],
          content: text[i],
          placement: positions[i],
          onNext: function (ele) {
            try {
              $(steps[ele._current + 1].element)[0].scrollIntoView(false);
            } catch (e) {
              console.log(e.message);
            }
          },
          onPrev: function (ele) {
            try {
              $(steps[ele._current - 1].element)[0].scrollIntoView(false);
            } catch (e) {
              console.log(e.message);
            }
          },
        };
        if (isEnable[i] === 'true' && isEnable[i] == 'true') {
          // console.log("6");
          obj['title'] = title[i];
        }
        if (i > 0 && popup[i - 1] === 'true') {
          //console.log("7");
          obj.onPrev = function () {
            $('.fa-remove').click();
          };
        }
      }
      steps.push(obj);
      //console.log(steps);
    })(index);
  }
  //console.log(steps);
  var tour = new Tour({
    steps: steps,
    storage: false,
    autoscroll: true,
  });
  tour.init(true);
  tour.restart(true);
  tour.start(true);
  //console.log(tour);

  function callNext(id) {
    //console.log(id);
    if (id == '1') {
      //alert("1");
      openDropdown();
      tour.goTo(id);
    }
    if (id == '2') {
      //alert("2");
      var r = $('.popover-navigation .text-center').find('[data-role="prev"]');
      r.addClass('disabled');
      //console.log(r);
      r.addClass('disabled');
      closeDropdown();
      //console.log(id);
      $.ajax({
        url: '../helptour/helperFunction.php?function=getContainerId&id=' + parentId + '&csrfMagicToken=' + csrfMagicToken,
        dataType: 'json',
        success: function (data) {
          //console.log("success");
          // console.log(data);
          //console.log(data.length);
          var i = 0;
          var id2 = data[i].id;
          var containerId = data[i].containerid;
          var popovertype = data[i].popovertype;
          if (popovertype == 'slider') {
            rightContainerSlideOn(containerId);
            tour.goTo(id);
          }
        },
        error: function (data) {
          //console.log("failure");
        },
      });
    } else {
      if (id == '3') {
        //alert(id);
        //                r = n.find('[data-role="prev"]');
        //                r.addClass("disabled");

        tour.goTo(id);
      } else {
        //alert(id);
        tour.goTo(id);
      }
    }
  }
}

$('.left').click(function () {
  $('#myCarousel').carousel('prev');
});

$('.right').click(function () {
  $('#myCarousel').carousel('next');
});
