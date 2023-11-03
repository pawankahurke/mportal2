$('#loginSubmitId').click(function (e) {
  const opt_code = $('#otp_code').val() || 0;
  const token = $('#auth_token').val();
  $.ajax({
    url: '../lib/l-login.php',
    type: 'POST',
    data: {
      function: 'validateUserDetails',
      allow: true,
      opt_code,
    },
    headers: {
      Authorization: `Basic ${token}`,
    },
    success: function (data) {
      const res = JSON.parse(data);

      console.log(res);
      if (res['msg'] == 'LOGGED') {
        debugger;
        location.href = '/Dashboard/home/';
      } else {
        $('#absoLoader').hide();
        $('#error').html(res['msg']);
        $('#error').show();
        return false;
      }
    },
    error: function (err) {
      $('#absoLoader').hide();
    },
  });
});

$('#resend-opt-code').click(function (e) {
  const token = $('#auth_token').val();

  $.ajax({
    url: '../lib/l-login.php',
    type: 'POST',
    data: {
      function: 'validateUserDetails',
      allow: true,
    },
    headers: {
      Authorization: `Basic ${token}`,
    },
    success: function (data) {
      const res = JSON.parse(data);
      console.log(res);
      if (res['msg'] == 'OPT_SENDED') {
        launchResendTimer();
      } else {
        $('#error').html(res['msg']);
        $('#error').show();
        return false;
      }
    },
    error: function (err) {
      $('#absoLoader').hide();
    },
  });
});

function launchResendTimer(expire = 60) {
  const timer = $('#timer');
  const button = $('#resend-opt-code');

  button.hide();

  const message = `
    <span style="color: green; font-weight: 900;">OPT Resent !</span><br>
    <span>Please wait <b>[TIME]</b> seconds before you can request a new code.</span>
  `;

  const timerInterval = setInterval(() => {
    timer.html(message.replace('[TIME]', expire--));
    if (expire == 0) {
      clearInterval(timerInterval);
      timer.html('');
      button.show();
    }
  }, 1000);
}
if ($('#resend_expire_time').val() > 0) {
  launchResendTimer($('#resend_expire_time').val());
}
