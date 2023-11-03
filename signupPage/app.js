var TIMEOUT = 2000;

var interval = setInterval(handleNext, TIMEOUT);

function handleNext() {
  var $radios = $('input[class*="slide-radio"]');
  var $activeRadio = $('input[class*="slide-radio"]:checked');

  var currentIndex = $activeRadio.index();
  var radiosLength = $radios.length;

  $radios.attr('checked', false);

  if (currentIndex >= radiosLength - 1) {
    $radios.first().attr('checked', true);
  } else {
    $activeRadio.next('input[class*="slide-radio"]').attr('checked', true);
  }
}

function skipSlider() {
  //alert("ashish");
  //window.location.href = '../home/index.php';
  debugger;
  window.location.href = '../admin/customize.php';
}
