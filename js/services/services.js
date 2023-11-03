
$(document).ready(function () {
  $('#services_search').hide();
  $('#searchloader').hide();
  $('#loader_submit').hide();
  // $('#parent_desc').html('Page Loading...Please Wait');
  // $.ajax({
  //     type: "POST",
  //     url: "../services/wizFunc.php",
  //     data:{
  //     	'function':'parent_Description',
  //     csrfMagicToken:csrfMagicToken
  //     } ,
  //             success: function(msg) {
  //             $('#parent_desc').html('');
  //             $("#parent_desc").append(msg);
  //     },
  //     error: function(msg) {
  //                    }
  // });

  // function childDesc(parent){
  $('.tooltext').html('Others');
  // $(".innTab").hide();
  // $('#tab_1').show();
  $.ajax({
    type: 'POST',
    url: '../services/wizFunc.php',
    data: { function: 'child_Description', name: 'Others', csrfMagicToken: csrfMagicToken },
    success: function (msg) {
      $('.loader').hide();
      $('#parent_desc').html('');
      $('#parent_desc').append(msg);
      $('.nav-link')[0].click();
      // subchildDesc(46);
      // $(".innerTab").show();
      // $("#child_desc").append(msg);
      // slideshow();
    },
    error: function (msg) {},
  });
  // }

  $(document).on('change', '.check_class', function () {
    $('.check_class').prop('checked', false);
    $(this).prop('checked', true);
  });
});

$('.closebtn').click(function () {
  $('#override_precedence').prop('checked', false);
  $('#existing_precedence').prop('checked', true);
});

//Old changes [Donot remove]
// function childDesc(parent){
//     $('.tooltext').html(parent);
//     $(".innTab").hide();
//     $('#tab_1').show();
//     $.ajax({
//         type: "POST",
//         url: "../services/wizFunc.php",
//         data:{'function' :'child_Description',name:parent, csrfMagicToken:csrfMagicToken
//          },
//                 success: function(msg) {
//                 $(".innerTab").show();
//                 $("#child_desc").append(msg);
//                 slideshow();
//         },
//         error: function(msg) {
//                        }
//     });
// }

function subchildDesc(id) {
  $('#selectedsubchild').val(id);
  $('#services_search').show();
  $('.innTab').hide();
  $('#tab_1').show();
  $('#searchloader').show();
  if (id == '42') {
    rightContainerSlideOn('ios_config');
  } else {
    $.ajax({
      type: 'POST',
      url: '../services/wizFunc.php',
      data: {
        function: 'subchild_Description',
        id: id,
        csrfMagicToken: csrfMagicToken,
      },
      success: function (msg) {
        $('#searchloader').hide();
        if (msg === '') {
          closePopUp();
          sweetAlert({
            title: 'There are no devices installed for this site. Please install the device before accessing the page!',
            type: 'warning',
            confirmButtonColor: '#050d30',
            confirmButtonText: 'Return',
          })
            .then(function () {
              subchildDesc(id)
            });
        } else {
          $('.innerTab').show();
          $('#sub_child_desc').html('');
          $('#sub_child_desc').append(msg);
          slideshow();
        }
      },
      error: function (msg) {},
    });
  }
}

function configDesc(wname, config) {
  closePopUp();
  sweetAlert({
    title: 'Variable assignments will be done by:',
    html: '<div class="form-check" style="float:left; margin-left: 30px;">\n\
                    <label class="form-check-label">Existing precedence and mapping rules\n\
                        <input class="form-check-input check_class" name="existing_precedence" id="existing_precedence"  type="checkbox">\n\
                        <span class="form-check-sign"></span>\n\
                    </label>\n\
                </div>\n\
                <div class="form-check" style="float:left; margin-left: 30px;">\n\
                    <label class="form-check-label">Overriding precedence and mapping rules\n\
                        <input class="form-check-input check_class" name="override_precedence" id="override_precedence"  type="checkbox">\n\
                        <span class="form-check-sign"></span>\n\
                    </label>\n\
                </div>',
    type: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#050d30',
    cancelButtonColor: '#fa0f4b',
    cancelButtonText: 'No, cancel it!',
    confirmButtonText: 'Confirm',
  })
    .then(function (result) {
      var precedence;
      if ($('#existing_precedence').is(':checked')) {
        precedence = 'true';
      } else {
        precedence = 'false';
      }

      $('#loader_submit').hide();
      rightContainerSlideOn('configuresideBar');
      var wiz_name_id = config.trim();
      var wiz_name = wname.trim();
      $('#dartname').html(wiz_name);
      $('#dartnoid').html(wiz_name_id);
      $('#jsonModalDialogDivs').html('');
      $('#precedenceValue').val(precedence);
      $('#loader').show();
      $.ajax({
        url: '../JSONSchema/jsonschema.php?dartno=' + wiz_name_id + '&csrfMagicToken=' + csrfMagicToken,
        type: 'GET',
        dataType: 'text',
        async: true,
        success: function (data) {
          $('#loader').fadeOut('slow');
          $('#jsonModalDialogDivs').html(data);
        },
        error: function (data) {},
      });
    })
    .catch(function (reason) {
      $('.closebtn').trigger('click');
    });
}

function backoption() {
  location.reload();
}

//Serach otpion for services module
function searchDarts() {
  $('#sub_child_desc').html('');
  $('#searchloader').show();
  var searchText = $('#UserInput').val();
  var selectedSubId = $('#selectedsubchild').val();
  if (selectedSubId == '42') {
    rightContainerSlideOn('ios_config');
  } else {
    $.ajax({
      type: 'POST',
      url: '../services/wizFunc.php',
      data: { function: 'subchild_Description', id: selectedSubId, search: searchText, csrfMagicToken: csrfMagicToken },
      success: function (msg) {
        $('#searchloader').hide();
        $('.innerTab').show();
        $('#sub_child_desc').html('');
        $('#sub_child_desc').append(msg);
        slideshow();
      },
      error: function (msg) {},
    });
  }
}

function reDirectToMsgConfig() {
  window.location.href = '../custom/messageAudit.php';
}
